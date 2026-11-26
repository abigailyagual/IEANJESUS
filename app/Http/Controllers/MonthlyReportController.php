<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\MonthlyReport;
use App\Models\ReportTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class MonthlyReportController extends Controller
{

public function index(Request $request): View
{
    $user = $request->user();

    // Validar los filtros recibidos.
    $filters = $request->validate([
        'category_id' => ['nullable', 'integer', 'exists:categories,id'],
        'report_month' => ['nullable', 'integer', 'between:1,12'],
        'report_year' => ['nullable', 'integer', 'between:2020,2100'],
        'status' => [
            'nullable',
            'in:draft,submitted,in_review,observed,approved'
        ],
        'search' => ['nullable', 'string', 'max:100'],
    ]);

    
    // Plantillas disponibles según los permisos del usuario.
    $templatesQuery = ReportTemplate::query()
        ->where('is_active', true);

    $templatesQuery->whereHas('category.users', fn ($query) => $query->whereKey($user->id));
    if (!$user->canCreateMonthlyReports()) {
        $templatesQuery->whereRaw('1 = 0');
    }

    $templates = $templatesQuery
        ->orderBy('name')
        ->get();


    // Departamentos disponibles para filtrar.
    $categories = Category::query()
        ->when(
            !$user->canSeeAllMonthlyReports(),
            function ($query) use ($user) {
                $query->whereHas('users', function ($userQuery) use ($user) {
                    $userQuery->whereKey($user->id);
                });
            }
        )
        ->orderBy('name')
        ->get();

    // Consulta de informes.
    $reports = MonthlyReport::query()
        ->with(['template', 'category', 'user'])

        ->visibleTo($user)

        // Filtrar por departamento.
        ->when(
            !empty($filters['category_id']),
            function ($query) use ($filters) {
                $query->where(
                    'category_id',
                    $filters['category_id']
                );
            }
        )

        // Filtrar por mes.
        ->when(
            !empty($filters['report_month']),
            function ($query) use ($filters) {
                $query->where(
                    'report_month',
                    $filters['report_month']
                );
            }
        )

        // Filtrar por año.
        ->when(
            !empty($filters['report_year']),
            function ($query) use ($filters) {
                $query->where(
                    'report_year',
                    $filters['report_year']
                );
            }
        )

        // Filtrar por estado.
        ->when(
            !empty($filters['status']),
            function ($query) use ($filters) {
                $query->where(
                    'status',
                    $filters['status']
                );
            }
        )

        // Buscar por nombre de informe o ID.
        ->when(
            !empty($filters['search']),
            function ($query) use ($filters) {
                $search = trim($filters['search']);

                $query->where(function ($subQuery) use ($search) {
                    $subQuery->whereHas(
                        'template',
                        function ($templateQuery) use ($search) {
                            $templateQuery->where(
                                'name',
                                'ILIKE',
                                '%' . $search . '%'
                            );
                        }
                    );

                    if (ctype_digit($search)) {
                        $subQuery->orWhere('id', (int) $search);
                    }
                });
            }
        )

        // Ordenar del más reciente al más antiguo.
        ->orderByDesc('report_year')
        ->orderByDesc('report_month')
        ->orderByDesc('id')

        // Mostrar 15 informes por página.
        // Conservar los filtros al cambiar de página.
        ->paginate(15)
        ->withQueryString();

    return view('monthly-reports.index', compact(
        'templates',
        'reports',
        'categories',
        'filters'
    ));
}
    
public function create(
    Request $request,
    ReportTemplate $template
): View {
    abort_unless($template->is_active, 404);

    $template->load(['sections.fields', 'category']);

    abort_unless(
        $template->category_id && $template->category,
        422,
        'Esta plantilla no tiene un departamento asignado.'
    );

    $user = $request->user();

    // Solo el secretario asignado puede elaborar informes.
    abort_unless($user->canCreateMonthlyReportFor((int) $template->category_id), 403, 'No tiene permiso para elaborar informes de este departamento.');

    // Conservamos la variable categories para que
    // la vista actual siga funcionando.
    $categories = Category::query()
        ->whereKey($template->category_id)
        ->get();

    return view('monthly-reports.create', compact(
        'template',
        'categories'
    ));
}


    
public function store(
    Request $request,
    ReportTemplate $template
) {
    abort_unless($template->is_active, 404);

    $template->load(['sections.fields', 'category']);

    abort_unless(
        $template->category_id && $template->category,
        422,
        'Esta plantilla no tiene un departamento asignado.'
    );

    abort_if(
        $template->sections->flatMap->fields->isEmpty(),
        422,
        'Esta plantilla todavía no tiene campos configurados.'
    );

    $user = $request->user();

    // El departamento siempre proviene de la plantilla,
    // nunca del valor enviado desde el navegador.
    $categoryId = (int) $template->category_id;

    abort_unless($user->canCreateMonthlyReportFor($categoryId), 403, 'No tiene permiso para elaborar informes de este departamento.');

    $data = $request->validate([
        'report_month' => ['required', 'integer', 'between:1,12'],
        'report_year' => ['required', 'integer', 'between:2020,2100'],
        'values' => ['nullable', 'array'],
    ]);

    $fields = $template->sections->flatMap->fields;

    $allowedIds = $fields->pluck('id')
        ->map(fn ($id) => (string) $id)
        ->all();

    foreach (array_keys($data['values'] ?? []) as $fieldId) {
        if (!in_array((string) $fieldId, $allowedIds, true)) {
            throw ValidationException::withMessages([
                'values' => 'Se recibieron campos ajenos a la plantilla.',
            ]);
        }
    }

    $validatedValues = [];

    foreach ($fields as $field) {
        $raw = $request->input('values.' . $field->id);

        $rules = match ($field->field_type) {
            'number' => ['nullable', 'integer', 'min:0'],
            'boolean' => ['nullable', 'boolean'],
            'textarea' => ['nullable', 'string', 'max:10000'],
            'text' => ['nullable', 'string', 'max:1000'],
            default => null,
        };

        if ($rules === null) {
            throw ValidationException::withMessages([
                'values' => 'Tipo de campo no admitido: ' . $field->field_type,
            ]);
        }

        $value = validator(
            ['value' => $raw],
            ['value' => $rules]
        )->validate()['value'] ?? null;

        if ($value !== null && $value !== '') {
            $validatedValues[$field->id] = match ($field->field_type) {
                'number' => (int) $value,
                'boolean' => (bool) $value,
                default => (string) $value,
            };
        }
    }

    $report = DB::transaction(function () use (
        $template,
        $categoryId,
        $data,
        $user,
        $validatedValues
    ) {
        $report = MonthlyReport::create([
            'report_template_id' => $template->id,
            'category_id' => $categoryId,
            'user_id' => $user->id,
            'report_month' => $data['report_month'],
            'report_year' => $data['report_year'],
            'status' => MonthlyReport::STATUS_DRAFT,
        ]);

        foreach ($validatedValues as $fieldId => $value) {
            $report->values()->create([
                'report_field_id' => $fieldId,
                'value' => ['value' => $value],
            ]);
        }

        return $report;
    });

    return redirect()
        ->route('monthly-reports.index')
        ->with(
            'success',
            'Borrador #' . $report->id . ' guardado correctamente.'
        );
}


public function edit(Request $request, MonthlyReport $report): View
{
    $user = $request->user();

    abort_unless($user->canEditMonthlyReport($report), 403, 'No tiene permiso para editar este informe.');

    abort_unless(
        in_array($report->status, [MonthlyReport::STATUS_DRAFT, MonthlyReport::STATUS_OBSERVED], true),
        403,
        'Solo se pueden editar informes en borrador u observados.'
    );

    $report->load([
        'template.sections.fields',
        'values',
        'category',
    ]);

    $template = $report->template;

    $savedValues = $report->values
        ->mapWithKeys(function ($item) {
            return [
                $item->report_field_id =>
                    $item->value['value'] ?? null
            ];
        })
        ->all();

    return view('monthly-reports.edit', compact(
        'report',
        'template',
        'savedValues'
    ));
}

public function update(
    Request $request,
    MonthlyReport $report
) {
    $user = $request->user();

    abort_unless($user->canEditMonthlyReport($report), 403, 'No tiene permiso para actualizar este informe.');

    abort_unless(
        in_array($report->status, [MonthlyReport::STATUS_DRAFT, MonthlyReport::STATUS_OBSERVED], true),
        403,
        'Solo se pueden actualizar informes en borrador u observados.'
    );

    $report->load('template.sections.fields');

    $fields = $report->template->sections->flatMap->fields;

    $data = $request->validate([
        'values' => ['nullable', 'array'],
    ]);

    $allowedIds = $fields->pluck('id')
        ->map(fn ($id) => (string) $id)
        ->all();

    foreach (array_keys($data['values'] ?? []) as $fieldId) {
        if (!in_array((string) $fieldId, $allowedIds, true)) {
            throw ValidationException::withMessages([
                'values' => 'Se recibieron campos ajenos a la plantilla.'
            ]);
        }
    }

    $validatedValues = [];

    foreach ($fields as $field) {
        $raw = $request->input('values.' . $field->id);

        $rules = match ($field->field_type) {
            'number' => ['nullable', 'integer', 'min:0'],
            'boolean' => ['nullable', 'boolean'],
            'textarea' => ['nullable', 'string', 'max:10000'],
            'text' => ['nullable', 'string', 'max:1000'],
            default => null,
        };

        if ($rules === null) {
            throw ValidationException::withMessages([
                'values' => 'Tipo de campo no admitido.'
            ]);
        }

        $value = validator(
            ['value' => $raw],
            ['value' => $rules]
        )->validate()['value'] ?? null;

        if ($value !== null && $value !== '') {
            $validatedValues[$field->id] = match ($field->field_type) {
                'number' => (int) $value,
                'boolean' => (bool) $value,
                default => (string) $value,
            };
        }
    }

    DB::transaction(function () use ($report, $validatedValues, $user) {

        // Bloqueamos el informe para evitar cambios simultáneos
        $lockedReport = MonthlyReport::query()
            ->lockForUpdate()
            ->findOrFail($report->id);

        abort_unless($user->canEditMonthlyReport($lockedReport), 403);

        abort_unless(
            in_array($lockedReport->status, [MonthlyReport::STATUS_DRAFT, MonthlyReport::STATUS_OBSERVED], true),
            403,
            'El informe ya no permite correcciones.'
        );

        // Reemplazamos únicamente las respuestas del borrador
        $lockedReport->values()->delete();

        foreach ($validatedValues as $fieldId => $value) {
            $lockedReport->values()->create([
                'report_field_id' => $fieldId,
                'value' => ['value' => $value],
            ]);
        }

        $lockedReport->touch();
    });

    return redirect()
        ->route('monthly-reports.index')
        ->with(
            'success',
            'El informe #' . $report->id . ' se actualizó correctamente.'
        );
}

public function submit(Request $request, MonthlyReport $report)
{
    $user = $request->user();

    abort_unless($user->canSubmitMonthlyReport($report), 403, 'No tiene permiso para enviar este informe.');

    DB::transaction(function () use ($report, $user) {
        $lockedReport = MonthlyReport::query()
            ->lockForUpdate()
            ->findOrFail($report->id);

        abort_unless($user->canSubmitMonthlyReport($lockedReport), 403);

        abort_unless(
            in_array($lockedReport->status, [MonthlyReport::STATUS_DRAFT, MonthlyReport::STATUS_OBSERVED], true),
            403,
            'Solo se pueden enviar informes en borrador u observados.'
        );

        abort_unless(
            $lockedReport->values()->exists(),
            422,
            'Debe completar al menos un campo antes de enviar el informe.'
        );

        $lockedReport->update([
            'status' => MonthlyReport::STATUS_SUBMITTED,
            'submitted_at' => now(),
            'approved_at' => null,
        ]);
    });

    return redirect()
        ->route('monthly-reports.index')
        ->with('success', 'Informe #' . $report->id . ' enviado a revisión.');
}

public function startReview(Request $request, MonthlyReport $report)
{
    abort_unless(
        $request->user()->canApproveMonthlyReports(),
        403,
        'No tiene permiso para revisar informes.'
    );

    DB::transaction(function () use ($report) {
        $lockedReport = MonthlyReport::query()
            ->lockForUpdate()
            ->findOrFail($report->id);

        abort_unless(
            $lockedReport->status === MonthlyReport::STATUS_SUBMITTED,
            422,
            'Solo se pueden revisar informes enviados.'
        );

        $lockedReport->update([
            'status' => MonthlyReport::STATUS_IN_REVIEW,
        ]);
    });

    return redirect()
        ->route('monthly-reports.index')
        ->with('success', 'Informe #' . $report->id . ' en revisión.');
}

public function approve(Request $request, MonthlyReport $report)
{
    abort_unless(
        $request->user()->canApproveMonthlyReports(),
        403,
        'No tiene permiso para aprobar informes.'
    );

    DB::transaction(function () use ($report) {
        $lockedReport = MonthlyReport::query()
            ->lockForUpdate()
            ->findOrFail($report->id);

        abort_unless(
            $lockedReport->status === MonthlyReport::STATUS_IN_REVIEW,
            422,
            'Solo se pueden aprobar informes en revisión.'
        );

        $lockedReport->update([
            'status' => MonthlyReport::STATUS_APPROVED,
            'approved_at' => now(),
            'observations' => null,
        ]);
    });

    return redirect()
        ->route('monthly-reports.index')
        ->with('success', 'Informe #' . $report->id . ' aprobado correctamente.');
}

public function observe(Request $request, MonthlyReport $report)
{
    abort_unless(
        $request->user()->canApproveMonthlyReports(),
        403,
        'No tiene permiso para observar informes.'
    );

    $data = $request->validate([
        'observations' => ['required', 'string', 'max:5000'],
    ]);

    DB::transaction(function () use ($report, $data) {
        $lockedReport = MonthlyReport::query()
            ->lockForUpdate()
            ->findOrFail($report->id);

        abort_unless(
            $lockedReport->status === MonthlyReport::STATUS_IN_REVIEW,
            422,
            'Solo se pueden observar informes en revisión.'
        );

        $lockedReport->update([
            'status' => MonthlyReport::STATUS_OBSERVED,
            'observations' => $data['observations'],
            'approved_at' => null,
        ]);
    });

    return redirect()
        ->route('monthly-reports.index')
        ->with('success', 'Informe #' . $report->id . ' devuelto con observaciones.');
}

public function show(Request $request, MonthlyReport $report)
{
    $user = $request->user();

    // Aplicar el permiso de consulta según rol y departamento.
    abort_unless($user->canViewMonthlyReport($report), 403, 'No tiene permiso para consultar este informe.');

    $report->load([
        'template.sections.fields',
        'category',
        'user',
        'values',
    ]);

    $answers = $report->values
        ->pluck('value', 'report_field_id');

    return view('monthly-reports.show', compact(
        'report',
        'answers'
    ));
}


}
