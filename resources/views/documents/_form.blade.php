@php
    $document = $document ?? null;
@endphp

<div class="grid gap-5 lg:grid-cols-2">

{{-- Período del informe mensual --}}
<div>
    <x-input-label for="report_month" :value="'Mes del informe'" />

    <select
        id="report_month"
        name="report_month"
        required
        class="mt-1 block w-full rounded-md border-slate-300 shadow-sm"
    >
        <option value="">Seleccione un mes</option>

        @php
            $months = [
                1 => 'Enero',
                2 => 'Febrero',
                3 => 'Marzo',
                4 => 'Abril',
                5 => 'Mayo',
                6 => 'Junio',
                7 => 'Julio',
                8 => 'Agosto',
                9 => 'Septiembre',
                10 => 'Octubre',
                11 => 'Noviembre',
                12 => 'Diciembre',
            ];
        @endphp

        @foreach ($months as $number => $month)
            <option
                value="{{ $number }}"
                @selected((int) old('report_month', $document?->report_month) === $number)
            >
                {{ $month }}
            </option>
        @endforeach
    </select>

    @error('report_month')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>

<div>
    <x-input-label for="report_year" :value="'Año del informe'" />

    <select
        id="report_year"
        name="report_year"
        required
        class="mt-1 block w-full rounded-md border-slate-300 shadow-sm"
    >
        <option value="">Seleccione un año</option>

        @for ($year = now()->year; $year >= 2020; $year--)
            <option
                value="{{ $year }}"
                @selected((int) old('report_year', $document?->report_year) === $year)
            >
                {{ $year }}
            </option>
        @endfor
    </select>

    @error('report_year')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>

    <div class="lg:col-span-2">
        <x-input-label for="name" :value="'Nombre del documento'" />
        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $document?->name)" required />
    </div>

    <div>
        <x-input-label for="document_type" :value="'Tipo de documento'" />
        <select id="document_type" name="document_type" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-slate-400 focus:ring-slate-400">
            @foreach (\App\Models\Document::TYPE_LABELS as $value => $label)
                <option value="{{ $value }}" @selected(old('document_type', $document?->document_type ?? 'internal') === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <x-input-label for="priority" :value="'Prioridad'" />
        <select id="priority" name="priority" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-slate-400 focus:ring-slate-400">
            @foreach (\App\Models\Document::PRIORITY_LABELS as $value => $label)
                <option value="{{ $value }}" @selected(old('priority', $document?->priority ?? 'normal') === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <x-input-label for="category_id" :value="'Departamento'" />
        <select id="category_id" name="category_id" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-slate-400 focus:ring-slate-400">
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected((int) old('category_id', $document?->category_id) === $category->id)>{{ $category->name }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <x-input-label for="approver_id" :value="'Revisor adicional'" />
        <select id="approver_id" name="approver_id" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-slate-400 focus:ring-slate-400">
            <option value="">Sin asignar</option>
            @foreach ($approvers as $approver)
                <option value="{{ $approver->id }}" @selected((int) old('approver_id', $document?->approver_id) === $approver->id)>
                    {{ $approver->name }}{{ $approver->position ? ' - ' . $approver->position : '' }}
                </option>
            @endforeach
        </select>
        <p class="mt-2 text-xs text-slate-500">El director de departamento y Secretaría de Junta Local se añadirán automáticamente al flujo.</p>
    </div>

    <div>
        <x-input-label for="external_partner" :value="'Contraparte / destinatario'" />
        <x-text-input id="external_partner" name="external_partner" type="text" class="mt-1 block w-full" :value="old('external_partner', $document?->external_partner)" />
    </div>

    <div>
        <x-input-label for="due_at" :value="'Fecha límite'" />
        <x-text-input id="due_at" name="due_at" type="date" class="mt-1 block w-full" :value="old('due_at', optional($document?->due_at)->format('Y-m-d'))" />
    </div>

    <div class="lg:col-span-2">
        <x-input-label for="summary" :value="'Resumen'" />
        <textarea id="summary" name="summary" rows="4" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-slate-400 focus:ring-slate-400">{{ old('summary', $document?->summary) }}</textarea>
    </div>

    <div class="lg:col-span-2">
        <label for="dropzone-file" class="flex cursor-pointer flex-col items-center justify-center rounded-lg border border-dashed border-slate-300 bg-slate-50 px-6 py-8 text-center transition hover:border-slate-400 hover:bg-slate-100">
            <div id="upload-icon" class="mb-3 text-slate-400">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-10 w-10">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 16.5V8.25m0 0l-3 3m3-3l3 3M3.75 15a4.5 4.5 0 004.5 4.5h7.5a4.5 4.5 0 001.102-8.863 3.75 3.75 0 00-7.42-1.989A4.501 4.501 0 003.75 15z" />
                </svg>
            </div>
            <div class="text-sm font-medium text-slate-700">
                {{ $document ? 'Reemplazar archivo del documento' : 'Cargar archivo del documento' }}
            </div>
            <div id="file-name" class="mt-2 text-sm text-slate-500">
                @if ($document?->file)
                    Archivo actual: <span class="font-medium text-slate-800">{{ $document->file }}</span>
                @else
                    PDF, DOCX, XLSX, CSV, PPTX o TXT
                @endif
            </div>
            <input id="dropzone-file" name="file" type="file" class="hidden" {{ $document ? '' : 'required' }}>
        </label>
    </div>

    <div class="lg:col-span-2">
        <label class="inline-flex items-center gap-3 rounded-md border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700">
            <input type="checkbox" name="visibility" value="1" class="rounded border-slate-300 text-slate-900 shadow-sm focus:ring-slate-400" @checked(old('visibility', $document?->visibility ?? true))>
            Mostrar el documento a los usuarios del departamento asignado
        </label>
    </div>
</div>
