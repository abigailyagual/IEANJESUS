
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

    $priorityLabels = [
        'low' => 'Baja',
        'normal' => 'Normal',
        'medium' => 'Media',
        'high' => 'Alta',
        'urgent' => 'Urgente',
        'critical' => 'Crítica',
    ];

    $typeLabels = [
        'internal' => 'Interno',
        'external' => 'Externo',
        'incoming' => 'Recibido',
        'outgoing' => 'Enviado',
    ];
@endphp

<section class="panel overflow-hidden">

    <div class="panel-header">
        <div>
            <h2 class="text-base font-semibold text-slate-900">
                Búsqueda y filtros
            </h2>

            <p class="text-sm text-slate-500">
                {{ $activeDepartmentName === 'Todos los departamentos'
                    ? 'Todos los departamentos'
                    : $activeDepartmentName }}
                · Los resultados se actualizan sin recargar la página.
            </p>
        </div>

        <div class="grid w-full gap-3 sm:max-w-5xl sm:grid-cols-2 xl:grid-cols-5">

            {{-- Filtro por departamento --}}
            <select
                wire:model="categoryId"
                class="block rounded-md border-slate-300 shadow-sm focus:border-slate-400 focus:ring-slate-400"
            >
                <option value="">Todos los departamentos</option>

                @foreach ($categories as $category)
                    <option value="{{ $category->id }}">
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>

            {{-- Filtro por estado --}}
            <select
                wire:model="status"
                class="block rounded-md border-slate-300 shadow-sm focus:border-slate-400 focus:ring-slate-400"
            >
                <option value="">Todos los estados</option>

                @foreach (\App\Models\Document::STATUS_LABELS as $status => $label)
                    <option value="{{ $status }}">
                        {{ $statusLabels[$status] ?? $label }}
                    </option>
                @endforeach
            </select>

            {{-- Búsqueda --}}
            <input
                type="text"
                wire:model.debounce.350ms="search"
                class="block rounded-md border-slate-300 shadow-sm focus:border-slate-400 focus:ring-slate-400 xl:col-span-2"
                placeholder="Código, nombre del informe o referencia"
            >

            {{-- Tareas asignadas y limpieza de filtros --}}
            <div class="flex items-center gap-3">
                <label class="inline-flex flex-1 items-center gap-2 rounded-md border border-slate-200 px-3 py-2 text-sm text-slate-600">

                    <input
                        type="checkbox"
                        wire:model="onlyTasks"
                        class="rounded border-slate-300 text-slate-900 shadow-sm focus:ring-slate-400"
                    >

                    Solo mis tareas
                </label>

                <button
                    type="button"
                    wire:click="clearFilters"
                    class="btn-secondary whitespace-nowrap"
                >
                    Limpiar filtros
                </button>
            </div>
        </div>
    </div>

    {{-- Indicador de carga --}}
    <div class="relative">

        <div
            wire:loading.delay
            class="absolute inset-0 z-10 bg-white/70 text-sm font-medium text-slate-600 backdrop-blur-sm"
        >
            <div class="flex h-full items-center justify-center">
                Actualizando informes...
            </div>
        </div>

        <div class="overflow-x-auto">

            <table class="min-w-full divide-y divide-slate-200 text-sm">

                <thead class="bg-slate-50 text-left text-slate-500">
                    <tr>
                        <th class="px-5 py-3 font-medium">Código</th>
                        <th class="px-5 py-3 font-medium">Informe mensual</th>
                        <th class="px-5 py-3 font-medium">Departamento</th>
                        <th class="px-5 py-3 font-medium">Responsable</th>
                        <th class="px-5 py-3 font-medium">Etapa actual</th>
                        <th class="px-5 py-3 font-medium">Estado</th>
                        <th class="px-5 py-3 font-medium">Fecha límite</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-200 bg-white">

                    @forelse ($documents as $document)

                        <tr class="hover:bg-slate-50">

                            {{-- Código del informe --}}
                            <td class="px-5 py-4 font-medium text-slate-700">
                                {{ $document->registration_number ?: 'Sin código' }}
                            </td>

                            {{-- Nombre y características --}}
                            <td class="px-5 py-4">

                                <a
                                    href="{{ route('documents.show', $document) }}"
                                    class="font-medium text-slate-900 hover:text-slate-700"
                                >
                                    {{ $document->name }}
                                </a>

                                <div class="mt-1 flex flex-wrap gap-2">

                                    <span class="badge-blue">
                                        {{ $typeLabels[$document->document_type] ?? $document->type_label }}
                                    </span>

                                    <span class="badge-slate">
                                        {{ $priorityLabels[$document->priority] ?? $document->priority_label }}
                                    </span>

                                    @if ($document->visibility)
                                        <span class="badge-slate">
                                            Visible para el departamento
                                        </span>
                                    @endif

                                </div>
                            </td>

                            {{-- Departamento --}}
                            <td class="px-5 py-4 text-slate-600">
                                {{ $document->category?->name ?: 'Sin departamento' }}
                            </td>

                            {{-- Responsable --}}
                            <td class="px-5 py-4 text-slate-600">
                                {{ $document->author?->name ?: 'No asignado' }}
                            </td>

                            {{-- Etapa de revisión --}}
                            <td class="px-5 py-4">

                                <div class="font-medium text-slate-800">
                                    {{ $document->current_step_label }}
                                </div>

                                <div class="mt-1 text-xs text-slate-500">
                                    {{ $document->current_step?->assignee_name
                                        ?? 'Sin responsable activo' }}
                                </div>

                            </td>

                            {{-- Estado --}}
                            <td class="px-5 py-4">

                                <span class="{{ $badgeClass($document->status) }}">
                                    {{ $statusLabels[$document->status] ?? $document->status_label }}
                                </span>

                            </td>

                            {{-- Fecha límite --}}
                            <td class="px-5 py-4 text-slate-600">
                                {{ optional($document->due_at)->format('d/m/Y')
                                    ?: 'Sin fecha límite' }}
                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td
                                colspan="7"
                                class="px-5 py-14 text-center text-slate-500"
                            >
                                No se encontraron informes mensuales
                                con los filtros seleccionados.
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>
        </div>
    </div>

    {{-- Paginación --}}
    <div class="border-t border-slate-200 px-5 py-4">
        {{ $documents->links() }}
    </div>

</section>
