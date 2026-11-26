<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Detalle del informe mensual #{{ $report->id }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Información general --}}
            <div class="bg-white shadow-sm rounded-lg p-6">

                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-xl font-bold">
                        {{ $report->template?->name }}
                    </h3>

                    @php
                        $statusLabels = [
                            'draft' => 'Borrador',
                            'submitted' => 'Enviado',
                            'in_review' => 'En revisión',
                            'observed' => 'Observado',
                            'approved' => 'Aprobado',
                        ];
                    @endphp

                    <span class="px-3 py-1 bg-gray-100 rounded-full text-sm font-semibold">
                        {{ $statusLabels[$report->status] ?? $report->status }}
                    </span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                    <div>
                        <p class="text-sm text-gray-500">Departamento</p>
                        <p class="font-medium">
                            {{ $report->category?->name }}
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500">Responsable</p>
                        <p class="font-medium">
                            {{ $report->user?->name }}
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500">Período</p>
                        <p class="font-medium">
                            {{ str_pad($report->report_month, 2, '0', STR_PAD_LEFT) }}
                            /
                            {{ $report->report_year }}
                        </p>
                    </div>

                    <div>
                        <p class="text-sm text-gray-500">Fecha de envío</p>
                        <p class="font-medium">
                            {{ $report->submitted_at?->format('d/m/Y H:i') ?? 'No enviado' }}
                        </p>
                    </div>

                    @if ($report->approved_at)
                        <div>
                            <p class="text-sm text-gray-500">Fecha de aprobación</p>
                            <p class="font-medium text-green-700">
                                {{ $report->approved_at->format('d/m/Y H:i') }}
                            </p>
                        </div>
                    @endif

                </div>
            </div>

            {{-- Secciones y respuestas --}}
            @foreach ($report->template->sections->sortBy('sort_order') as $section)

                <div class="bg-white shadow-sm rounded-lg p-6">

                    <h3 class="text-lg font-semibold border-b pb-3 mb-5">
                        {{ $section->title }}
                    </h3>

                    <div class="space-y-4">

                        @foreach ($section->fields->sortBy('sort_order') as $field)

                            @php
                                $stored = $answers->get($field->id);

                                // Los valores se guardan como JSON:
                                // {"value": dato}
                                $value = is_array($stored)
                                    ? ($stored['value'] ?? null)
                                    : $stored;

                                if (is_array($value)) {
                                    $displayValue = implode(', ', $value);
                                } elseif (is_bool($value)) {
                                    $displayValue = $value ? 'Sí' : 'No';
                                } elseif ($value === null || $value === '') {
                                    $displayValue = 'Sin respuesta';
                                } else {
                                    $displayValue = (string) $value;
                                }
                            @endphp

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 border-b pb-3">

                                <div class="text-gray-700 font-medium">
                                    {{ $field->label }}
                                </div>

                                <div class="text-gray-900 whitespace-pre-line">
                                    {{ $displayValue }}
                                </div>

                            </div>

                        @endforeach

                    </div>

                </div>

            @endforeach

            {{-- Observaciones --}}
            @if ($report->observations)
                <div class="bg-orange-50 border border-orange-200 rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-orange-800 mb-3">
                        Observaciones del revisor
                    </h3>

                    <p class="text-gray-800 whitespace-pre-line">
                        {{ $report->observations }}
                    </p>
                </div>
            @endif

            {{-- Volver --}}
            <div class="flex justify-end">
                <a href="{{ route('monthly-reports.index') }}"
                   class="px-5 py-2 bg-gray-800 text-white rounded-lg hover:bg-gray-700">
                    Volver al listado
                </a>
            </div>

        </div>
    </div>
</x-app-layout>
