<?php
namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Document;
use App\Models\MonthlyReport;
use App\Models\DocumentWorkflowStep;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Muestra el panel principal de gestión de informes mensuales.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();

        // Informes visibles según los permisos del usuario.
        $documentsQuery = Document::query()
            ->with(['category', 'author', 'approver', 'workflowSteps.user'])
            ->visibleTo($user);

        // Últimos ocho informes registrados.
        $documents = (clone $documentsQuery)
            ->latest()
            ->take(8)
            ->get();

        // Tareas de revisión o aprobación asignadas al usuario.
        $myTasks = Document::query()
            ->with(['category', 'author', 'workflowSteps.user'])
            ->visibleTo($user)
            ->whereHas('workflowSteps', function (Builder $query) use ($user) {
                $query
                    ->where('round', function ($roundQuery) {
                        $roundQuery->select('workflow_round')
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
            })
            ->latest('submitted_at')
            ->take(6)
            ->get();

        // Informes devueltos al autor para corrección.
        $needsRevision = (clone $documentsQuery)
            ->where('author_id', $user->id)
            ->where('status', 'rejected')
            ->latest('updated_at')
            ->take(4)
            ->get();

        // Indicadores del panel principal.
        $stats = [
            [
                'label' => 'Total de informes',
                'value' => (clone $documentsQuery)->count(),
            ],
            [
                'label' => 'En proceso de revisión',
                'value' => (clone $documentsQuery)
                    ->where('status', 'in_review')
                    ->count(),
            ],
            [
                'label' => 'Mis tareas pendientes',
                'value' => $myTasks->count(),
            ],
            [
                'label' => 'Informes atrasados',
                'value' => (clone $documentsQuery)
                    ->whereNotNull('due_at')
                    ->whereDate('due_at', '<', now()->toDateString())
                    ->whereIn('status', [
                        'draft',
                        'in_review',
                        'rejected',
                    ])
                    ->count(),
            ],
        ];

        // Resumen de informes agrupados por estado.
        $statusSummary = collect(Document::STATUS_LABELS)
            ->map(function (string $label, string $status) use ($documentsQuery) {
                return [
                    'status' => $status,
                    'label' => $label,
                    'count' => (clone $documentsQuery)
                        ->where('status', $status)
                        ->count(),
                ];
            })
            ->values();

        // Departamentos visibles para el usuario.
        $departments = $user->canSeeAllDocuments()
            ? Category::orderBy('name')->get()
            : $user->categories()->orderBy('name')->get();

        // Resumen institucional para usuarios autorizados.
        $adminSummary = null;

        if ($user->canSeeAllDocuments()) {
            $adminSummary = [
                'users' => User::count(),

                'departments' => Category::count(),

                'approvalLoad' => Document::where(
                    'status',
                    'in_review'
                )->count(),

                'officeQueue' => DocumentWorkflowStep::where(
                    'status',
                    DocumentWorkflowStep::STATUS_IN_PROGRESS
                )
                    ->where(
                        'role_code',
                        DocumentWorkflowStep::ROLE_OFFICE
                    )
                    ->count(),
            ];
        }
        // Estadísticas de informes mensuales del período actual.
$period = $request->validate([
    'report_month' => ['nullable', 'integer', 'between:1,12'],
    'report_year' => ['nullable', 'integer', 'between:2020,2100'],
]);

$monthlyReportMonth = (int) ($period['report_month'] ?? now()->month);
$monthlyReportYear = (int) ($period['report_year'] ?? now()->year);

$monthlyReportsQuery = MonthlyReport::query()
    ->where('report_month', $monthlyReportMonth)
    ->where('report_year', $monthlyReportYear);

// Aplicar los mismos permisos del módulo de informes mensuales.
$monthlyReportsQuery->visibleTo($user);

// Obtener todos los conteos en una consulta.
$monthlyStatusCounts = (clone $monthlyReportsQuery)
    ->selectRaw('status, COUNT(*) as total')
    ->groupBy('status')
    ->pluck('total', 'status');

$monthlyReportStats = [
    'total' => (int) $monthlyStatusCounts->sum(),
    'draft' => (int) $monthlyStatusCounts->get(MonthlyReport::STATUS_DRAFT, 0),
    'submitted' => (int) $monthlyStatusCounts->get(MonthlyReport::STATUS_SUBMITTED, 0),
    'in_review' => (int) $monthlyStatusCounts->get(MonthlyReport::STATUS_IN_REVIEW, 0),
    'observed' => (int) $monthlyStatusCounts->get(MonthlyReport::STATUS_OBSERVED, 0),
    'approved' => (int) $monthlyStatusCounts->get(MonthlyReport::STATUS_APPROVED, 0),
];

        // Porcentaje de aprobación de informes mensuales.
        $monthlySubmittedTotal =
            $monthlyReportStats['submitted']
            + $monthlyReportStats['in_review']
            + $monthlyReportStats['observed']
            + $monthlyReportStats['approved'];

        $monthlyApprovalPercentage = $monthlySubmittedTotal > 0
            ? round(
                ($monthlyReportStats['approved'] / $monthlySubmittedTotal) * 100,
                1
            )
            : 0;

        // Histórico de informes mensuales del año seleccionado.
        $monthlyHistoryQuery = MonthlyReport::query()
            ->where('report_year', $monthlyReportYear);

        // Respetar los permisos de visualización.
        $monthlyHistoryQuery->visibleTo($user);

        
        $monthlyHistoryCounts = $monthlyHistoryQuery
            ->selectRaw("
                report_month,
                COUNT(*) AS total,
                COUNT(*) FILTER (
                    WHERE status IN ('submitted', 'in_review', 'observed', 'approved')
                ) AS presented,
                COUNT(*) FILTER (
                    WHERE status = 'approved'
                ) AS approved,
                COUNT(*) FILTER (
                    WHERE status = 'observed'
                ) AS observed
            ")
            ->groupBy('report_month')
            ->get()
            ->keyBy('report_month');


        
        $monthlyHistory = collect(range(1, 12))
            ->map(function ($month) use ($monthlyHistoryCounts) {

                $data = $monthlyHistoryCounts->get($month);

                return [
                    'month' => $month,
                    'label' => \Carbon\Carbon::create(2026, $month, 1)
                        ->locale('es')
                        ->translatedFormat('M'),
                    'total' => (int) ($data->total ?? 0),
                    'presented' => (int) ($data->presented ?? 0),
                    'approved' => (int) ($data->approved ?? 0),
                    'observed' => (int) ($data->observed ?? 0),
                ];
            })
            ->values()
            ->all();


        // Histórico de cumplimiento mensual por plantilla departamental.
        $monthlyComplianceHistoryQuery = MonthlyReport::query()
            ->where('report_year', $monthlyReportYear)
            ->whereIn('status', [
                MonthlyReport::STATUS_SUBMITTED,
                MonthlyReport::STATUS_IN_REVIEW,
                MonthlyReport::STATUS_OBSERVED,
                MonthlyReport::STATUS_APPROVED,
            ])
            ->whereHas('template', function ($query) use ($user) {
                $query->where('is_active', true)
                    ->whereNotNull('category_id');

                if (!$user->canSeeAllMonthlyReports()) {
                    $query->whereHas('category.users', function ($userQuery) use ($user) {
                        $userQuery->whereKey($user->id);
                    });
                }
            });

        $monthlyComplianceHistoryQuery->visibleTo($user);

        $monthlyComplianceHistoryCounts = $monthlyComplianceHistoryQuery
            ->selectRaw('report_month, COUNT(DISTINCT report_template_id) AS total')
            ->groupBy('report_month')
            ->pluck('total', 'report_month');

        // Total de plantillas activas accesibles para el usuario.
       
        
        // Total de plantillas activas para el histórico.
        
        // Calcular las plantillas activas para el histórico.
        // Esta consulta es independiente de otras variables del controlador.
        $monthlyComplianceTotalForHistoryQuery =
            \App\Models\ReportTemplate::query()
                ->where('is_active', true)
                ->whereNotNull('category_id');

        // Respetar los permisos del usuario.
        if (!$user->canSeeAllMonthlyReports()) {
            $monthlyComplianceTotalForHistoryQuery
                ->whereHas('category.users', function ($query) use ($user) {
                    $query->whereKey($user->id);
                });
        }

        $monthlyComplianceTotalForHistory =
            $monthlyComplianceTotalForHistoryQuery->count();


        // Construcción del histórico de cumplimiento.
        $monthlyComplianceHistory = collect(range(1, 12))
            ->map(function ($month) use (
                $monthlyComplianceHistoryCounts,
                $monthlyComplianceTotalForHistory
            ) {
                $presented = (int) $monthlyComplianceHistoryCounts->get($month, 0);

                return [
                    'month' => $month,
                    'presented' => $presented,
                    'total' => $monthlyComplianceTotalForHistory,
                    'percentage' => $monthlyComplianceTotalForHistory > 0
                        ? round(
                            ($presented / $monthlyComplianceTotalForHistory) * 100,
                            1
                        )
                        : 0,
                ];
            })
            ->values()
            ->all();



        // Consolidado departamental del período seleccionado.
        $monthlyDepartmentSummary = Category::query()
            ->when(
                !$user->canSeeAllMonthlyReports(),
                function ($query) use ($user) {
                    $query->whereHas('users', function ($userQuery) use ($user) {
                        $userQuery->whereKey($user->id);
                    });
                }
            )
            ->withCount([

'monthlyReports as presented_reports_count' => function ($query) use (
    $user,
    $monthlyReportMonth,
    $monthlyReportYear
) {
    $query->where('report_month', $monthlyReportMonth)
        ->where('report_year', $monthlyReportYear)
        ->whereIn('status', [
            MonthlyReport::STATUS_SUBMITTED,
            MonthlyReport::STATUS_IN_REVIEW,
            MonthlyReport::STATUS_OBSERVED,
            MonthlyReport::STATUS_APPROVED,
        ]);

    $query->visibleTo($user);
},

                'monthlyReports as monthly_reports_count' => function ($query) use (
                    $user,
                    $monthlyReportMonth,
                    $monthlyReportYear
                ) {
                    $query->where('report_month', $monthlyReportMonth)
                        ->where('report_year', $monthlyReportYear);

                    $query->visibleTo($user);
                },

                'monthlyReports as approved_reports_count' => function ($query) use (
                    $user,
                    $monthlyReportMonth,
                    $monthlyReportYear
                ) {
                    $query->where('report_month', $monthlyReportMonth)
                        ->where('report_year', $monthlyReportYear)
                        ->where('status', MonthlyReport::STATUS_APPROVED);

                    $query->visibleTo($user);
                },
            ])
            ->orderBy('name')
            ->get();


        // Cumplimiento mensual por plantilla activa.
        // Respetar los departamentos asignados al usuario.
        $monthlyComplianceQuery = \App\Models\ReportTemplate::query()
            ->where('is_active', true)
            ->whereNotNull('category_id');

        if (!$user->canSeeAllMonthlyReports()) {
            $monthlyComplianceQuery->whereHas(
                'category.users',
                function ($query) use ($user) {
                    $query->whereKey($user->id);
                }
            );
        }

        
        $monthlyComplianceTemplates = $monthlyComplianceQuery
            ->withCount([
                // Informes presentados durante el período.
                'monthlyReports as presented_count' => function ($query) use (
                    $user,
                    $monthlyReportMonth,
                    $monthlyReportYear
                ) {
                    $query
                        ->where('report_month', $monthlyReportMonth)
                        ->where('report_year', $monthlyReportYear)
                        ->whereIn('status', [
                            MonthlyReport::STATUS_SUBMITTED,
                            MonthlyReport::STATUS_IN_REVIEW,
                            MonthlyReport::STATUS_OBSERVED,
                            MonthlyReport::STATUS_APPROVED,
                        ]);

                    $query->visibleTo($user);
                },

                // Informes que permanecen como borrador.
                'monthlyReports as draft_count' => function ($query) use (
                    $user,
                    $monthlyReportMonth,
                    $monthlyReportYear
                ) {
                    $query
                        ->where('report_month', $monthlyReportMonth)
                        ->where('report_year', $monthlyReportYear)
                        ->where('status', MonthlyReport::STATUS_DRAFT);

                    $query->visibleTo($user);
                },
                 // Informes observados que requieren corrección.
                'monthlyReports as observed_count' => function ($query) use (
                    $user,
                    $monthlyReportMonth,
                    $monthlyReportYear
                ) {
                    $query
                        ->where('report_month', $monthlyReportMonth)
                        ->where('report_year', $monthlyReportYear)
                        ->where('status', MonthlyReport::STATUS_OBSERVED);

                    $query->visibleTo($user);
                },
            ])
            ->orderBy('name')
            ->get();


        $monthlyComplianceTotal = $monthlyComplianceTemplates->count();

        $monthlyCompliancePresented = $monthlyComplianceTemplates
            ->filter(fn ($template) => $template->presented_count > 0)
            ->count();

        $monthlyCompliancePending = $monthlyComplianceTotal
            - $monthlyCompliancePresented;

        // Plantillas que todavía no tienen informes presentados.
        $monthlyPendingTemplates = $monthlyComplianceTemplates
            ->filter(function ($template) {
                return (int) $template->presented_count === 0;
            })
            ->values();


        // Plantillas con informes observados que requieren corrección.
        $monthlyObservedTemplates = $monthlyComplianceTemplates
            ->filter(function ($template) {
                return (int) $template->observed_count > 0;
            })
            ->values();

        // Total de informes observados en el período.
        $monthlyObservedTotal = $monthlyObservedTemplates
            ->sum('observed_count');

        $monthlyCompliancePercentage = $monthlyComplianceTotal > 0
            ? round(
                ($monthlyCompliancePresented / $monthlyComplianceTotal) * 100,
                1
            )
            : 0;

        // Enviar los datos a la vista del panel principal.
        return view('dashboard', [
            'documents' => $documents,
            'myTasks' => $myTasks,
            'needsRevision' => $needsRevision,
            'stats' => $stats,
            'statusSummary' => $statusSummary,
            'departments' => $departments,
            'adminSummary' => $adminSummary,
            'monthlyReportStats' => $monthlyReportStats,
            'monthlyReportMonth' => $monthlyReportMonth,
            'monthlyReportYear' => $monthlyReportYear,
            'monthlyDepartmentSummary' => $monthlyDepartmentSummary,
            'monthlyComplianceTotal' => $monthlyComplianceTotal,
            'monthlyCompliancePresented' => $monthlyCompliancePresented,
            'monthlyCompliancePending' => $monthlyCompliancePending,
            'monthlyCompliancePercentage' => $monthlyCompliancePercentage,
            'monthlyPendingTemplates' => $monthlyPendingTemplates,
            'monthlyObservedTemplates' => $monthlyObservedTemplates,
            'monthlyObservedTotal' => $monthlyObservedTotal,
            'monthlySubmittedTotal' => $monthlySubmittedTotal,
            'monthlyApprovalPercentage' => $monthlyApprovalPercentage,
            'monthlyHistory' => $monthlyHistory,
            'monthlyComplianceHistory' => $monthlyComplianceHistory,
        ]);
    }
}
