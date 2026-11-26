<?php

namespace App\Http\Controllers;

use App\Http\Requests\DocumentStore;
use App\Http\Requests\DocumentUpdate;
use App\Models\Category;
use App\Models\Document;
use App\Models\User;
use App\Services\DocumentWorkflowService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\View\View;

class DocumentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        return view('documents.index', [
            'search' => trim((string) $request->input('search', '')),
            'activeCategory' => $request->integer('category_id') ?: null,
            'activeStatus' => (string) $request->input('status', ''),
            'onlyTasks' => $request->boolean('only_tasks'),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $user = Auth::user();

        return view('documents.create', [
            'categories' => $this->availableCategories($user),
            'approvers' => $this->workflowApprovers($user),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(DocumentStore $request, DocumentWorkflowService $workflowService): RedirectResponse
    {
        $user = Auth::user();
        $validatedData = $request->validated();

        $document = Document::create([
            ...$validatedData,
            'file' => $this->storeUploadedFile($validatedData['file'], $validatedData['name']),
            'registration_number' => $this->generateRegistrationNumber($validatedData['document_type']),
            'author_id' => $user->id,
            'status' => 'draft',
            'submitted_at' => null,
            'approved_at' => null,
            'archived_at' => null,
            'rejection_reason' => null,
            'workflow_round' => 0,
        ]);

        $workflowService->log(
            $document,
            $user,
            'created',
            'Documento creado',
            'Registro creado en el sistema y listo para su envío.'
        );

        return redirect()
            ->route('documents.show', $document)
            ->with('status', 'Documento creado. Puede enviarlo a revisión.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Document $document): View
    {
        $this->authorizeView($document);

        return view('documents.show', [
            'document' => $document->load([
                'category',
                'author',
                'approver',
                'workflowSteps.user',
                'workflowSteps.department',
                'actionLogs.user',
                'actionLogs.workflowStep',
            ]),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Document $document): View
    {
        $user = Auth::user();
        abort_unless($document->isEditableBy($user), 403, 'No se permite editar este documento.');

        return view('documents.edit', [
            'document' => $document,
            'categories' => $this->availableCategories($user),
            'approvers' => $this->workflowApprovers($user),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(DocumentUpdate $request, Document $document, DocumentWorkflowService $workflowService): RedirectResponse
    {
        $user = Auth::user();
        abort_unless($document->isEditableBy($user), 403, 'No se permite editar este documento.');

        $validatedData = $request->validated();

        if ($request->hasFile('file')) {
            $validatedData['file'] = $this->replaceUploadedFile(
                oldFileName: $document->file,
                uploadedFile: $validatedData['file'],
                documentName: $validatedData['name']
            );
        } else {
            unset($validatedData['file']);
        }

        if ($document->status === 'rejected') {
            $validatedData['status'] = 'draft';
            $validatedData['submitted_at'] = null;
            $validatedData['rejection_reason'] = null;
            $validatedData['approved_at'] = null;
            $validatedData['archived_at'] = null;
        }

        $document->update($validatedData);

        $workflowService->log(
            $document,
            $user,
            'updated',
            'Registro del documento actualizado',
            $document->status === 'draft'
                ? 'Cambios guardados, puede reenviar el documento a revisión.'
                : 'Contenido y datos del documento actualizados.'
        );

        return redirect()
            ->route('documents.show', $document)
            ->with('status', 'Documento actualizado.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Document $document, DocumentWorkflowService $workflowService): RedirectResponse
    {
        $user = Auth::user();
        abort_unless($document->isEditableBy($user) || $user->isAdmin(), 403, 'No se permite eliminar.');

        $workflowService->log(
            $document,
            $user,
            'deleted',
            'Documento eliminado',
            'Registro marcado como eliminado.'
        );

        $document->delete();
        $this->deleteUploadedFile($document->file);

        return redirect()
            ->route('documents.index')
            ->with('status', 'Documento eliminado.');
    }

    /**
     * Search resource from storage.
     */
    public function search(Request $request): RedirectResponse
    {
        return redirect()->route('documents.index', [
            'search' => $request->input('search'),
            'category_id' => $request->input('category_id'),
            'status' => $request->input('status'),
            'only_tasks' => $request->boolean('only_tasks'),
        ]);
    }

    /**
     * Submit document for approval.
     */
    public function submit(Document $document, DocumentWorkflowService $workflowService): RedirectResponse
    {
        $user = Auth::user();
        abort_unless($document->canBeSubmittedBy($user), 403, 'No se permite enviar a revisión.');

        $workflowService->submit($document, $user);

        return redirect()
            ->route('documents.show', $document)
            ->with('status', 'Documento enviado al flujo de aprobación.');
    }

    /**
     * Approve document.
     */
    public function approve(Request $request, Document $document, DocumentWorkflowService $workflowService): RedirectResponse
    {
        $user = Auth::user();
        abort_unless($document->canBeApprovedBy($user), 403, 'No se permite aprobar.');

        $validated = $request->validate([
            'comment' => ['nullable', 'string', 'max:1000'],
        ]);

        $workflowService->approve($document, $user, trim((string) ($validated['comment'] ?? '')) ?: null);

        return redirect()
            ->route('documents.show', $document)
            ->with('status', 'Etapa del flujo confirmada.');
    }

    /**
     * Reject document.
     */
    public function reject(Request $request, Document $document, DocumentWorkflowService $workflowService): RedirectResponse
    {
        $user = Auth::user();
        abort_unless($document->canBeApprovedBy($user), 403, 'No se permite devolver el documento.');

        $validated = $request->validate([
            'comment' => ['nullable', 'string', 'max:1000'],
        ]);

        $workflowService->reject(
            $document,
            $user,
            trim((string) ($validated['comment'] ?? '')) ?: 'Realice las correcciones y reenvíe el documento.'
        );

        return redirect()
            ->route('documents.show', $document)
            ->with('status', 'Documento devuelto para corrección.');
    }

    /**
     * Archive document.
     */
    public function archive(Request $request, Document $document, DocumentWorkflowService $workflowService): RedirectResponse
    {
        $user = Auth::user();
        abort_unless($document->canBeArchivedBy($user), 403, 'No se permite archivar.');

        $validated = $request->validate([
            'comment' => ['nullable', 'string', 'max:1000'],
        ]);

        $workflowService->archive($document, $user, trim((string) ($validated['comment'] ?? '')) ?: null);

        return redirect()
            ->route('documents.show', $document)
            ->with('status', 'Documento enviado al archivo electrónico.');
    }

    /**
     * Get the base query for visible documents.
     */
    protected function baseQuery(User $user): Builder
    {
        return Document::query()
            ->with(['category', 'author', 'approver', 'workflowSteps.user'])
            ->visibleTo($user)
            ->latest();
    }

    /**
     * Get categories available to the current user.
     */
    protected function availableCategories(User $user)
    {
        return $user->canSeeAllDocuments()
            ? Category::orderBy('name')->get()
            : $user->categories()->orderBy('name')->get();
    }

    /**
     * Get the list of possible functional approvers.
     */
    protected function workflowApprovers(User $user)
    {
        return User::query()
            ->where('role', '!=', User::ROLE_OFFICE)
            ->whereKeyNot($user->id)
            ->orderBy('role')
            ->orderBy('name')
            ->get();
    }

    /**
     * Ensure document is visible to the current user.
     */
    protected function authorizeView(Document $document): void
    {
        $user = Auth::user();

        $allowed = Document::query()
            ->whereKey($document->id)
            ->visibleTo($user)
            ->exists();

        abort_unless($allowed, 403, 'No se permite consultar.');
    }

    /**
     * Persist uploaded file in the public documents folder.
     */
    protected function storeUploadedFile($uploadedFile, string $documentName): string
    {
        $slug = now()->format('YmdHis') . '-' . Str::slug($documentName) . '.' . $uploadedFile->extension();
        $uploadedFile->move(public_path('files'), $slug);

        return $slug;
    }

    /**
     * Replace an uploaded file and delete the old version.
     */
    protected function replaceUploadedFile(string $oldFileName, $uploadedFile, string $documentName): string
    {
        $this->deleteUploadedFile($oldFileName);

        return $this->storeUploadedFile($uploadedFile, $documentName);
    }

    /**
     * Delete uploaded file if present.
     */
    protected function deleteUploadedFile(?string $fileName): void
    {
        if (!$fileName) {
            return;
        }

        $filePath = public_path('files/' . $fileName);
        if (File::exists($filePath)) {
            File::delete($filePath);
        }
    }

    /**
     * Generate a registration number based on type and date.
     */
    protected function generateRegistrationNumber(string $documentType): string
    {
        $prefixes = [
            'internal' => 'INT',
            'incoming' => 'IN',
            'outgoing' => 'OUT',
            'contract' => 'CTR',
            'order' => 'ORD',
        ];

        $sequence = str_pad((string) ((Document::withTrashed()->max('id') ?? 0) + 1), 4, '0', STR_PAD_LEFT);

        return sprintf(
            '%s-%s-%s',
            $prefixes[$documentType] ?? 'DOC',
            now()->format('Y'),
            $sequence
        );
    }
}
