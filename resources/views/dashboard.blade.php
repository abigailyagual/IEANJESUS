
@extends('layouts.system')

@section('title', 'Panel principal')

@section('page-title', 'Panel de Gestión de Informes Mensuales')

@section(
    'eyebrow',
    auth()->user()->canSeeInstitutionalDashboard()
        ? 'Administración y seguimiento institucional'
        : 'Área de trabajo del usuario'
)

@section('page-actions')
    <a href="{{ route('monthly-reports.index') }}" class="btn-primary">
        {{ auth()->user()->canCreateMonthlyReports() ? 'Nuevo informe mensual' : 'Consultar informes mensuales' }}
    </a>
@endsection


@php
    $badgeClass = function (string $status): string {
        return match ($status) {
            'draft' => 'badge-slate',
            'in_review' => 'badge-amber',
            'approved', 'archived' => 'badge-emerald',
            'rejected' => 'badge-rose',
            default => 'badge-blue',
        };
    };

    $statusLabels = [
        'draft' => 'Borrador',
        'in_review' => 'En revisión',
        'approved' => 'Aprobado',
        'rejected' => 'Devuelto para corrección',
        'archived' => 'Archivado',
    ];
@endphp

@section('content')

    <!-- INDICADORES GENERALES -->
    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">

        @foreach ($stats as $stat)

            <div class="panel p-5">

                <div class="text-sm text-slate-500">
                    {{ $stat['label'] }}
                </div>

                <div class="mt-3 text-3xl font-semibold text-slate-900">
                    {{ $stat['value'] }}
                </div>

            </div>

        @endforeach

    </div>

    <!-- RESUMEN DE INFORMES MENSUALES DEPARTAMENTALES -->

<!-- FILTROS DE PERÍODO -->
<form method="GET"
      action="{{ route('dashboard') }}"
      class="mb-5 flex flex-wrap items-end gap-3 rounded-lg border border-slate-200 bg-white p-4">

    <div>
        <label for="report_month"
               class="mb-1 block text-sm font-medium text-slate-700">
            Mes
        </label>

        <select name="report_month"
                id="report_month"
                class="rounded-md border border-slate-300 px-3 py-2 text-sm">

            @foreach (range(1, 12) as $month)
                <option value="{{ $month }}"
                    @selected($monthlyReportMonth === $month)>
                    {{ \Carbon\Carbon::create(2026, $month, 1)
                        ->locale('es')
                        ->translatedFormat('F') }}
                </option>
            @endforeach

        </select>
    </div>

    <div>
        <label for="report_year"
               class="mb-1 block text-sm font-medium text-slate-700">
            Año
        </label>

        <select name="report_year"
                id="report_year"
                class="rounded-md border border-slate-300 px-3 py-2 text-sm">

            @foreach (range((int) now()->year, 2020) as $year)
                <option value="{{ $year }}"
                    @selected($monthlyReportYear === $year)>
                    {{ $year }}
                </option>
            @endforeach

        </select>
    </div>

    <button type="submit" class="btn-primary">
        Consultar período
    </button>

    <a href="{{ route('dashboard') }}" class="btn-secondary">
        Mes actual
    </a>

</form>
<!-- FIN DE FILTROS DE PERÍODO -->

    <section class="mt-8">

        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-lg font-semibold text-slate-900">
                    Informes mensuales departamentales
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    Resumen correspondiente al período
                    {{ \Carbon\Carbon::create(
                        $monthlyReportYear,
                        $monthlyReportMonth,
                        1
                    )->locale('es')->translatedFormat('F Y') }}
                </p>
            </div>

            <a href="{{ route('monthly-reports.index') }}"
               class="btn-secondary">
                Ver informes departamentales
            </a>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">

            @php
                $monthlyCards = [
                    [
                        'label' => 'Total',
                        'value' => $monthlyReportStats['total'],
                        'color' => 'text-slate-900',
                    ],
                    [
                        'label' => 'Borradores',
                        'value' => $monthlyReportStats['draft'],
                        'color' => 'text-slate-600',
                    ],
                    [
                        'label' => 'Enviados',
                        'value' => $monthlyReportStats['submitted'],
                        'color' => 'text-blue-700',
                    ],
                    [
                        'label' => 'En revisión',
                        'value' => $monthlyReportStats['in_review'],
                        'color' => 'text-amber-700',
                    ],
                    [
                        'label' => 'Observados',
                        'value' => $monthlyReportStats['observed'],
                        'color' => 'text-rose-700',
                    ],
                    [
                        'label' => 'Aprobados',
                        'value' => $monthlyReportStats['approved'],
                        'color' => 'text-emerald-700',
                    ],
                ];
            @endphp

            @foreach ($monthlyCards as $card)
                <div class="panel p-5">
                    <div class="text-sm text-slate-500">
                        {{ $card['label'] }}
                    </div>

                    <div class="mt-3 text-3xl font-semibold {{ $card['color'] }}">
                        {{ $card['value'] }}
                    </div>
                </div>
            @endforeach

        </div>

    </section>
    <!-- FIN DEL RESUMEN DE INFORMES MENSUALES -->


<!-- INDICADORES DE CUMPLIMIENTO MENSUAL -->
<section class="mt-6">

    <div class="mb-4">
        <h2 class="text-lg font-semibold text-slate-900">
            Cumplimiento de presentación mensual
        </h2>

        <p class="mt-1 text-sm text-slate-500">
            Seguimiento de las plantillas departamentales activas
            durante el período seleccionado.
        </p>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">

        <div class="panel p-5">
            <div class="text-sm text-slate-500">
                Plantillas activas
            </div>
            <div class="mt-3 text-3xl font-semibold text-slate-900">
                {{ $monthlyComplianceTotal }}
            </div>
        </div>

        <div class="panel p-5">
            <div class="text-sm text-slate-500">
                Con presentación
            </div>
            <div class="mt-3 text-3xl font-semibold text-emerald-700">
                {{ $monthlyCompliancePresented }}
            </div>
        </div>

        <div class="panel p-5">
            <div class="text-sm text-slate-500">
                Sin presentación
            </div>
            <div class="mt-3 text-3xl font-semibold text-rose-700">
                {{ $monthlyCompliancePending }}
            </div>
        </div>

        <div class="panel p-5">
            <div class="text-sm text-slate-500">
                Porcentaje de cumplimiento
            </div>
            <div class="mt-3 text-3xl font-semibold text-blue-700">
                {{ number_format($monthlyCompliancePercentage, 1, ',', '.') }}%
            </div>
        </div>

    </div>

    <!-- BARRA DE PROGRESO -->
    <div class="panel mt-4 p-5">

        <div class="mb-3 flex items-center justify-between gap-3">
            <span class="text-sm font-medium text-slate-700">
                Avance de presentación
            </span>

            <span class="text-sm font-semibold text-slate-900">
                {{ $monthlyCompliancePresented }}
                de
                {{ $monthlyComplianceTotal }}
            </span>
        </div>

       
        
<div style="width: 100%; height: 16px; background-color: #e2e8f0; border-radius: 9999px; overflow: hidden;">
    <div
        style="width: {{ min(100, max(0, $monthlyCompliancePercentage)) }}%; height: 16px; background-color: #16a34a; border-radius: 9999px;">
    </div>
</div>



        <p class="mt-3 text-xs text-slate-500">
            Se considera presentado un informe enviado,
            en revisión, observado o aprobado.
            Los borradores no cuentan como presentación.
        </p>

    </div>

</section>
<!-- FIN DE INDICADORES DE CUMPLIMIENTO -->

<!-- INDICADOR DE APROBACIÓN MENSUAL -->
<section class="panel mt-6">

    <div class="panel-header">
        <div>
            <h2 class="text-base font-semibold text-slate-900">
                Porcentaje de aprobación mensual
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                Informes aprobados respecto al total de informes
                presentados durante el período seleccionado.
            </p>
        </div>
    </div>

    <div class="p-5">

        <div class="flex items-center justify-between mb-4">
            <div>
                <p class="text-sm text-slate-500">
                    Informes aprobados
                </p>

                <p class="text-3xl font-bold text-slate-900">
                    {{ $monthlyReportStats['approved'] }}
                    <span class="text-base font-normal text-slate-500">
                        / {{ $monthlySubmittedTotal }}
                    </span>
                </p>
            </div>

            <div class="text-right">
                <p class="text-3xl font-bold text-emerald-700">
                    {{ number_format($monthlyApprovalPercentage, 1) }}%
                </p>

                <p class="text-xs text-slate-500">
                    Tasa de aprobación
                </p>
            </div>
        </div>

        <div style="width: 100%; height: 16px; background-color: #e2e8f0; border-radius: 9999px; overflow: hidden;">
            <div
                style="width: {{ min(100, max(0, $monthlyApprovalPercentage)) }}%; height: 16px; background-color: #16a34a; border-radius: 9999px;">
            </div>
        </div>

        <p class="mt-3 text-xs text-slate-500">
            Se consideran presentados los informes enviados,
            en revisión, observados y aprobados.
            Los borradores no se incluyen.
        </p>

    </div>
</section>
<!-- FIN DE INDICADOR DE APROBACIÓN MENSUAL -->

<!-- HISTÓRICO MENSUAL DE INFORMES -->

<!-- HISTÓRICO MENSUAL DE INFORMES -->
<section class="panel mt-6">

    <div class="panel-header">
        <div>
            <h2 class="text-base font-semibold text-slate-900">
                Histórico mensual de informes - {{ $monthlyReportYear }}
            </h2>
            <p class="mt-1 text-sm text-slate-500">
                Comparación de informes registrados, presentados,
                aprobados y observados durante el año.
            </p>
        </div>
    </div>

    @php
        $historyMax = max(
            1,
            collect($monthlyHistory)->max('total') ?? 0
        );

        $historyTotal = collect($monthlyHistory)->sum('total');

        $historySeries = [
            ['key' => 'total', 'name' => 'Registrados', 'color' => '#64748b'],
            ['key' => 'presented', 'name' => 'Presentados', 'color' => '#2563eb'],
            ['key' => 'approved', 'name' => 'Aprobados', 'color' => '#16a34a'],
            ['key' => 'observed', 'name' => 'Observados', 'color' => '#e11d48'],
        ];
    @endphp

    <div class="p-5">

        <p class="mb-4 text-sm text-slate-600">
            Total anual:
            <strong>{{ $historyTotal }} informes registrados</strong>
        </p>

        <!-- Leyenda -->
        <div class="mb-6 flex flex-wrap gap-4">
            @foreach ($historySeries as $series)
                <div class="flex items-center gap-2">
                    <span style="
                        display: inline-block;
                        width: 12px;
                        height: 12px;
                        border-radius: 3px;
                        background-color: {{ $series['color'] }};
                    "></span>
                    <span class="text-xs text-slate-600">
                        {{ $series['name'] }}
                    </span>
                </div>
            @endforeach
        </div>

        <!-- Gráfico -->
        <div class="overflow-x-auto">
            <div style="
                min-width: 760px;
                display: grid;
                grid-template-columns: repeat(12, minmax(0, 1fr));
                gap: 8px;
                align-items: end;
            ">

                @foreach ($monthlyHistory as $item)
                    <div style="min-width: 0; text-align: center;">

                        <div style="
                            height: 210px;
                            display: flex;
                            justify-content: center;
                            align-items: flex-end;
                            gap: 3px;
                        ">

                            @foreach ($historySeries as $series)
                                @php
                                    $value = (int) $item[$series['key']];
                                    $barHeight = $value > 0
                                    ? max(4, ($value / $historyMax) * 170)
                                    : 3;
                                @endphp

                                <div style="
                                    width: 11px;
                                    display: flex;
                                    flex-direction: column;
                                    align-items: center;
                                    justify-content: flex-end;
                                ">
                                    <span style="
                                        font-size: 9px;
                                        margin-bottom: 5px;
                                        color: #475569;
                                    ">
                                        {{ $value }}
                                    </span>

                                    <div
                                        title="{{ $item['label'] }} - {{ $series['name'] }}: {{ $value }}"
                                        style="
                                            width: 100%;
                                            height: {{ $barHeight }}px;
                                            background-color: {{ $series['color'] }};
                                            border-radius: 3px 3px 0 0;
                                        ">
                                    </div>
                                </div>
                            @endforeach

                        </div>

                        <div style="
                            border-top: 1px solid #cbd5e1;
                            padding-top: 8px;
                            font-size: 11px;
                            color: #475569;
                        ">
                            {{ $item['label'] }}
                        </div>

                    </div>
                @endforeach

            </div>
        </div>

        <p class="mt-4 text-xs text-slate-500">
            Presentados incluye los estados enviado, en revisión,
            observado y aprobado. Los borradores solamente se
            contabilizan dentro de registrados.
        </p>

    </div>
</section>
<!-- FIN DE HISTÓRICO MENSUAL DE INFORMES -->

<!-- HISTÓRICO DE CUMPLIMIENTO DEPARTAMENTAL -->
<section class="panel mt-6">

    <div class="panel-header">
        <div>
            <h2 class="text-base font-semibold text-slate-900">
                Histórico de cumplimiento departamental - {{ $monthlyReportYear }}
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                Porcentaje de plantillas departamentales con al menos
                un informe presentado durante cada mes.
            </p>
        </div>
    </div>

    <div class="p-5">

        <div class="overflow-x-auto">
            <div style="
                min-width: 720px;
                display: grid;
                grid-template-columns: repeat(12, minmax(0, 1fr));
                gap: 8px;
                align-items: end;
            ">

                @foreach ($monthlyComplianceHistory as $item)

                    @php
                        $percentage = min(100, max(0, $item['percentage']));
                        $barHeight = $percentage > 0
                            ? max(4, ($percentage / 100) * 180)
                            : 3;

                        $monthLabel = \Carbon\Carbon::create(
                            $monthlyReportYear,
                            $item['month'],
                            1
                        )->locale('es')->translatedFormat('M');
                    @endphp

                    <div style="min-width: 0; text-align: center;">

                        <div style="
                            height: 220px;
                            display: flex;
                            flex-direction: column;
                            align-items: center;
                            justify-content: flex-end;
                        ">

                            <span style="
                                font-size: 10px;
                                margin-bottom: 6px;
                                color: #334155;
                            ">
                                {{ number_format($percentage, 1) }}%
                            </span>

                            <div
                                title="{{ $monthLabel }}: {{ $item['presented'] }} de {{ $item['total'] }} plantillas"
                                style="
                                    width: 65%;
                                    max-width: 40px;
                                    height: {{ $barHeight }}px;
                                    background-color: #16a34a;
                                    border-radius: 5px 5px 0 0;
                                ">
                            </div>

                        </div>

                        <div style="
                            border-top: 1px solid #cbd5e1;
                            padding-top: 8px;
                            font-size: 11px;
                            color: #475569;
                        ">
                            {{ $monthLabel }}
                        </div>

                        <div style="
                            font-size: 10px;
                            color: #64748b;
                            margin-top: 4px;
                        ">
                            {{ $item['presented'] }}/{{ $item['total'] }}
                        </div>

                    </div>

                @endforeach

            </div>
        </div>

        <p class="mt-4 text-xs text-slate-500">
            Cada plantilla departamental se contabiliza una sola vez
            por mes. Se consideran presentados los informes enviados,
            en revisión, observados y aprobados. Los borradores no
            cuentan como presentados.
        </p>

    </div>

</section>
<!-- FIN DE HISTÓRICO DE CUMPLIMIENTO DEPARTAMENTAL -->

<!-- FIN DE HISTÓRICO MENSUAL DE INFORMES -->

<!-- DEPARTAMENTOS PENDIENTES DE PRESENTACIÓN -->
<section class="panel mt-6 overflow-hidden">

    <div class="panel-header">
        <div>
            <h2 class="text-base font-semibold text-slate-900">
                Departamentos pendientes de presentación
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                Plantillas sin informes presentados durante
                {{ \Carbon\Carbon::create(
                    $monthlyReportYear,
                    $monthlyReportMonth,
                    1
                )->locale('es')->translatedFormat('F Y') }}.
            </p>
        </div>

        <span class="badge-rose">
            {{ $monthlyCompliancePending }} pendientes
        </span>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200 text-sm">

            <thead class="bg-slate-50 text-left text-slate-600">
                <tr>
                    <th class="px-5 py-3">N.º</th>
                    <th class="px-5 py-3">Informe requerido</th>
                    <th class="px-5 py-3 text-center">Estado</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-slate-200 bg-white">

                @forelse ($monthlyPendingTemplates as $template)

                    <tr class="hover:bg-slate-50">

                        <td class="px-5 py-4 text-slate-600">
                            {{ $loop->iteration }}
                        </td>

                        <td class="px-5 py-4 font-medium text-slate-900">
                            {{ $template->name }}
                        </td>

                        
<td class="px-5 py-4 text-center">

    @if ((int) $template->draft_count > 0)

        <span class="badge-amber">
            En borrador
        </span>

    @else

        <span class="badge-rose">
            Sin registrar
        </span>

    @endif

</td>


                    </tr>

                @empty

                    <tr>
                        <td colspan="3"
                            class="px-5 py-8 text-center text-emerald-700">
                            Todos los informes requeridos
                            han sido presentados.
                        </td>
                    </tr>

                @endforelse

            </tbody>
        </table>
    </div>

</section>
<!-- FIN DE DEPARTAMENTOS PENDIENTES -->

<!-- INFORMES OBSERVADOS PENDIENTES DE CORRECCIÓN -->
<section class="panel mt-6 overflow-hidden">

    <div class="panel-header">
        <div>
            <h2 class="text-base font-semibold text-slate-900">
                Informes observados pendientes de corrección
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                Informes que requieren correcciones durante
                {{ \Carbon\Carbon::create(
                    $monthlyReportYear,
                    $monthlyReportMonth,
                    1
                )->locale('es')->translatedFormat('F Y') }}.
            </p>
        </div>

        <span class="badge-rose">
            {{ $monthlyObservedTotal }} observados
        </span>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200 text-sm">

            <thead class="bg-slate-50 text-left text-slate-600">
                <tr>
                    <th class="px-5 py-3">N.º</th>
                    <th class="px-5 py-3">Informe departamental</th>
                    <th class="px-5 py-3 text-center">Cantidad</th>
                    <th class="px-5 py-3 text-center">Estado</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-slate-200 bg-white">

                @forelse ($monthlyObservedTemplates as $template)

                    <tr class="hover:bg-slate-50">

                        <td class="px-5 py-4 text-slate-600">
                            {{ $loop->iteration }}
                        </td>

                        <td class="px-5 py-4 font-medium text-slate-900">
                            {{ $template->name }}
                        </td>

                        <td class="px-5 py-4 text-center">
                            {{ $template->observed_count }}
                        </td>

                        <td class="px-5 py-4 text-center">
                            <span class="badge-rose">
                                Requiere corrección
                            </span>
                        </td>

                    </tr>

                @empty

                    <tr>
                        <td colspan="4"
                            class="px-5 py-8 text-center text-emerald-700">
                            No existen informes observados
                            pendientes de corrección en este período.
                        </td>
                    </tr>

                @endforelse

            </tbody>
        </table>
    </div>

</section>
<!-- FIN DE INFORMES OBSERVADOS -->

<!-- CONSOLIDADO DE INFORMES POR DEPARTAMENTO -->
<section class="panel mt-6 overflow-hidden">

    <div class="panel-header">
        <div>
            <h2 class="text-base font-semibold text-slate-900">
                Consolidado de informes por departamento
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                Período:
                {{ \Carbon\Carbon::create(
                    $monthlyReportYear,
                    $monthlyReportMonth,
                    1
                )->locale('es')->translatedFormat('F Y') }}
            </p>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200 text-sm">

            <thead class="bg-slate-50 text-left text-slate-600">
                <tr>
                    <th class="px-5 py-3">Departamento</th>
                    <th class="px-5 py-3 text-center">Informes</th>
                    <th class="px-5 py-3 text-center">Presentados</th>
                    <th class="px-5 py-3 text-center">Aprobados</th>
                    <th class="px-5 py-3">Estado</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-slate-200 bg-white">

                @forelse ($monthlyDepartmentSummary as $department)

                    @php
                        $total = (int) $department->monthly_reports_count;
                        $presented = (int) $department->presented_reports_count;
                        $approved = (int) $department->approved_reports_count;

                        $status = match (true) {
                            $total === 0 => 'Sin informes',
                            $approved === $total => 'Todos aprobados',
                            $approved > 0 => 'Aprobación parcial',
                            $presented > 0 => 'Pendientes de aprobación',
                            default => 'En borrador o por corregir',
                        };

                        $statusClass = match ($status) {
                            'Todos aprobados' => 'badge-emerald',
                            'Aprobación parcial' => 'badge-blue',
                            'Pendientes de aprobación' => 'badge-amber',
                            'En borrador o por corregir' => 'badge-rose',
                            default => 'badge-slate',
                        };
                    @endphp

                    <tr class="hover:bg-slate-50">

                        <td class="px-5 py-4 font-medium text-slate-900">
                            {{ $department->name }}
                        </td>

                        <td class="px-5 py-4 text-center">
                            {{ $total }}
                        </td>

                        <td class="px-5 py-4 text-center">
                            {{ $presented }}
                        </td>

                        <td class="px-5 py-4 text-center">
                            {{ $approved }}
                        </td>

                        <td class="px-5 py-4">
                            <span class="{{ $statusClass }}">
                                {{ $status }}
                            </span>
                        </td>

                    </tr>

                @empty

                    <tr>
                        <td colspan="5"
                            class="px-5 py-8 text-center text-slate-500">
                            No hay departamentos disponibles.
                        </td>
                    </tr>

                @endforelse

            </tbody>
        </table>
    </div>

</section>
<!-- FIN DEL CONSOLIDADO -->


    <!-- CONTENIDO DEL PANEL -->
    <div class="mt-6 grid gap-6 xl:grid-cols-[2fr,1fr]">

        <!-- COLUMNA IZQUIERDA -->
        <div class="space-y-6">

            <!-- INFORMES RECIENTES -->
            <section class="panel overflow-hidden">

                <div class="panel-header">

                    <div>
                        <h2 class="text-base font-semibold text-slate-900">
                            Informes mensuales recientes
                        </h2>

                        <p class="text-sm text-slate-500">
                            Últimos informes registrados y estado actual
                            de su proceso de revisión.
                        </p>
                    </div>

                    <a href="{{ route('documents.index') }}"
                       class="btn-secondary">
                        Ver informes
                    </a>

                </div>

                <div class="overflow-x-auto">

                    <table class="min-w-full divide-y divide-slate-200 text-sm">

                        <thead class="bg-slate-50 text-left text-slate-500">

                            <tr>
                                <th class="px-5 py-3 font-medium">
                                    Número
                                </th>

                                <th class="px-5 py-3 font-medium">
                                    Informe mensual
                                </th>

                                <th class="px-5 py-3 font-medium">
                                    Etapa de revisión
                                </th>

                                <th class="px-5 py-3 font-medium">
                                    Estado
                                </th>
                            </tr>

                        </thead>

                        <tbody class="divide-y divide-slate-200 bg-white">

                            @forelse ($documents as $document)

                                <tr class="hover:bg-slate-50">

                                    <td class="px-5 py-4 font-medium text-slate-700">
                                        {{ $document->registration_number }}
                                    </td>

                                    <td class="px-5 py-4">

                                        <a href="{{ route('documents.show', $document) }}"
                                           class="font-medium text-slate-900 hover:text-slate-700">
                                            {{ $document->name }}
                                        </a>

                                        <div class="mt-1 text-xs text-slate-500">
                                            {{ $document->category?->name ?: 'Sin departamento' }}
                                        </div>

                                    </td>

                                    <td class="px-5 py-4 text-slate-600">

                                        <div class="font-medium text-slate-800">
                                            {{ $document->current_step_label }}
                                        </div>

                                        <div class="mt-1 text-xs text-slate-500">
                                            {{ $document->current_step?->assignee_name ?? 'Sin responsable asignado' }}
                                        </div>

                                    </td>

                                    <td class="px-5 py-4">

                                        <span class="{{ $badgeClass($document->status) }}">
                                            {{ $statusLabels[$document->status] ?? $document->status_label }}
                                        </span>

                                    </td>

                                </tr>

                            @empty

                                <tr>
                                    <td colspan="4"
                                        class="px-5 py-12 text-center text-slate-500">
                                        Todavía no se han registrado informes mensuales.
                                    </td>
                                </tr>

                            @endforelse

                        </tbody>

                    </table>

                </div>

            </section>

            <!-- TAREAS PENDIENTES -->
            <section class="panel p-5">

                <div class="flex items-center justify-between">

                    <h2 class="text-base font-semibold text-slate-900">
                        Mis tareas pendientes
                    </h2>

                    <span class="badge-amber">
                        {{ $myTasks->count() }}
                    </span>

                </div>

                <div class="mt-4 space-y-3">

                    @forelse ($myTasks as $document)

                        <a href="{{ route('documents.show', $document) }}"
                           class="block rounded-md border border-slate-200 px-4 py-3 hover:bg-slate-50">

                            <div class="flex items-start justify-between gap-3">

                                <div>

                                    <div class="font-medium text-slate-900">
                                        {{ $document->name }}
                                    </div>

                                    <div class="mt-1 text-xs text-slate-500">
                                        {{ $document->registration_number }}
                                    </div>

                                    <div class="mt-2 text-xs text-slate-600">
                                        {{ $document->current_step?->title }}
                                    </div>

                                </div>

                                <span class="{{ $badgeClass($document->status) }}">
                                    {{ $statusLabels[$document->status] ?? $document->status_label }}
                                </span>

                            </div>

                        </a>

                    @empty

                        <div class="text-sm text-slate-500">
                            No tiene tareas de revisión o aprobación pendientes.
                        </div>

                    @endforelse

                </div>

            </section>

        </div>

        <!-- COLUMNA DERECHA -->
        <div class="space-y-6">

            <!-- RESUMEN DE ESTADOS -->
            <section class="panel p-5">

                <h2 class="text-base font-semibold text-slate-900">
                    Estado de los informes
                </h2>

                <div class="mt-4 space-y-3">

                    @foreach ($statusSummary as $item)

                        <div class="flex items-center justify-between">

                            <span class="text-sm text-slate-600">
                                {{ $statusLabels[$item['status']] ?? $item['label'] }}
                            </span>

                            <span class="{{ $badgeClass($item['status']) }}">
                                {{ $item['count'] }}
                            </span>

                        </div>

                    @endforeach

                </div>

            </section>

            <!-- INFORMES QUE NECESITAN CORRECCIÓN -->
            <section class="panel p-5">

                <h2 class="text-base font-semibold text-slate-900">
                    Informes pendientes de corrección
                </h2>

                <div class="mt-4 space-y-3">

                    @forelse ($needsRevision as $document)

                        <a href="{{ route('documents.show', $document) }}"
                           class="block rounded-md border border-rose-200 bg-rose-50 px-4 py-3 hover:bg-rose-100/70">

                            <div class="font-medium text-slate-900">
                                {{ $document->name }}
                            </div>

                            <div class="mt-1 text-xs text-rose-700">
                                {{ $document->rejection_reason }}
                            </div>

                        </a>

                    @empty

                        <div class="text-sm text-slate-500">
                            No tiene informes pendientes de corrección.
                        </div>

                    @endforelse

                </div>

            </section>

            <!-- RESUMEN INSTITUCIONAL -->
            <section class="panel p-5">

                <h2 class="text-base font-semibold text-slate-900">
                    {{ auth()->user()->canSeeAllDocuments()
                        ? 'Resumen institucional'
                        : 'Mis departamentos' }}
                </h2>

                <div class="mt-4 space-y-3">

                    @if ($adminSummary)

                        <div class="flex items-center justify-between">

                            <span class="text-sm text-slate-600">
                                Usuarios registrados
                            </span>

                            <span class="badge-blue">
                                {{ $adminSummary['users'] }}
                            </span>

                        </div>

                        <div class="flex items-center justify-between">

                            <span class="text-sm text-slate-600">
                                Departamentos
                            </span>

                            <span class="badge-blue">
                                {{ $adminSummary['departments'] }}
                            </span>

                        </div>

                        <div class="flex items-center justify-between">

                            <span class="text-sm text-slate-600">
                                Informes en revisión
                            </span>

                            <span class="badge-amber">
                                {{ $adminSummary['approvalLoad'] }}
                            </span>

                        </div>

                        <div class="flex items-center justify-between">

                            <span class="text-sm text-slate-600">
                                Pendientes en Secretaría
                            </span>

                            <span class="badge-amber">
                                {{ $adminSummary['officeQueue'] }}
                            </span>

                        </div>

                    @else

                        @forelse ($departments as $department)

                            <div class="flex items-center justify-between">

                                <span class="text-sm text-slate-600">
                                    {{ $department->name }}
                                </span>

                                @if ($department->code)

                                    <span class="badge-blue">
                                        {{ $department->code }}
                                    </span>

                                @endif

                            </div>

                        @empty

                            <div class="text-sm text-slate-500">
                                No hay departamentos asignados.
                            </div>

                        @endforelse

                    @endif

                </div>

            </section>

        </div>

    </div>

@endsection
