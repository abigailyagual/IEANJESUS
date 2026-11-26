<?php

namespace App\Http\Livewire;

use App\Models\Category;
use App\Models\Document;
use App\Models\DocumentWorkflowStep;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class DocumentJournal extends Component
{
    use WithPagination;

    public string $search = '';

    public ?int $categoryId = null;

    public string $status = '';

    public bool $onlyTasks = false;

    protected string $paginationTheme = 'tailwind';

    protected $queryString = [
        'search' => ['except' => ''],
        'categoryId' => ['as' => 'category_id', 'except' => null],
        'status' => ['except' => ''],
        'onlyTasks' => ['as' => 'only_tasks', 'except' => false],
    ];

    public function mount(
        string $search = '',
        ?int $categoryId = null,
        string $status = '',
        bool $onlyTasks = false
    ): void {
        $this->search = trim($search);
        $this->categoryId = $categoryId ?: null;
        $this->status = $status;
        $this->onlyTasks = $onlyTasks;
    }

    /**
     * Reinicia la paginación cuando cambian los filtros.
     */
    public function updated(): void
    {
        $this->resetPage();
    }

    /**
     * Limpia todos los filtros.
     */
    public function clearFilters(): void
    {
        $this->reset([
            'search',
            'categoryId',
            'status',
            'onlyTasks',
        ]);

        $this->resetPage();
    }

    /**
     * Muestra el listado de informes mensuales.
     */
    public function render()
    {
        $user = $this->currentUser();

        $categories = $this->availableCategories($user);

        $activeDepartment = $this->categoryId
            ? $categories->firstWhere('id', $this->categoryId)
            : null;

        return view('livewire.document-journal', [
            'categories' => $categories,

            'documents' => $this->documentsQuery($user)
                ->paginate(12),

            'activeDepartmentName' => $this->categoryId
                ? ($activeDepartment?->name ?? 'Departamento no disponible')
                : 'Todos los departamentos',
        ]);
    }

    /**
     * Construye la consulta respetando los permisos del usuario.
     */
    protected function documentsQuery(User $user): Builder
    {
        $search = trim($this->search);

        return Document::query()
            ->with([
                'category',
                'author',
                'approver',
                'workflowSteps.user',
            ])
            ->visibleTo($user)

            // Filtrar por departamento.
            ->when(
                $this->categoryId,
                fn (Builder $query) =>
                    $query->where('category_id', $this->categoryId)
            )

            // Filtrar por estado.
            ->when(
                $this->status !== '',
                fn (Builder $query) =>
                    $query->where('status', $this->status)
            )

            // Mostrar solamente tareas asignadas al usuario.
            ->when(
                $this->onlyTasks,
                fn (Builder $query) =>
                    $query->whereHas(
                        'workflowSteps',
                        function (Builder $stepQuery) use ($user) {
                            $stepQuery
                                ->where('round', function ($roundQuery) {
                                    $roundQuery
                                        ->select('workflow_round')
                                        ->from('documents')
                                        ->whereColumn(
                                            'documents.id',
                                            'document_workflow_steps.document_id'
                                        );
                                })
                                ->where(
                                    'status',
                                    DocumentWorkflowStep::STATUS_IN_PROGRESS
                                )
                                ->where('user_id', $user->id);
                        }
                    )
            )

            // Búsqueda sin distinguir mayúsculas y minúsculas.
            // ILIKE es compatible con PostgreSQL.
            ->when(
                $search !== '',
                function (Builder $query) use ($search) {
                    $query->where(
                        function (Builder $builder) use ($search) {
                            $term = '%' . $search . '%';

                            $builder
                                ->where('name', 'ILIKE', $term)
                                ->orWhere('registration_number', 'ILIKE', $term)
                                ->orWhere('summary', 'ILIKE', $term)
                                ->orWhere('external_partner', 'ILIKE', $term);
                        }
                    );
                }
            )
            ->latest();
    }

    /**
     * Obtiene los departamentos disponibles según el usuario.
     */
    protected function availableCategories(User $user)
    {
        return $user->canSeeAllDocuments()
            ? Category::orderBy('name')->get()
            : $user->categories()->orderBy('name')->get();
    }

    /**
     * Obtiene el usuario autenticado.
     */
    protected function currentUser(): User
    {
        /** @var User $user */
        $user = Auth::user();

        return $user;
    }
}
