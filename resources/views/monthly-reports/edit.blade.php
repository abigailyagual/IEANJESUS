<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Editar borrador #{{ $report->id }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">

                <h3 class="text-xl font-bold mb-2">
                    {{ $template->name }}
                </h3>

                <p class="text-gray-600 mb-6">
                    Departamento: {{ $report->category->name }}
                    |
                    Período:
                    {{ str_pad($report->report_month, 2, '0', STR_PAD_LEFT) }}
                    /
                    {{ $report->report_year }}
                </p>

                @if ($errors->any())
                    <div class="mb-6 p-4 bg-red-100 text-red-800 rounded">
                        <ul class="list-disc pl-5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST"
                      action="{{ route('monthly-reports.update', $report) }}">
                    @csrf
                    @method('PUT')

                    @foreach ($template->sections as $section)
                        <div class="mb-8">
                            <h4 class="text-lg font-semibold border-b pb-2 mb-4">
                                {{ $section->title }}
                            </h4>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                @foreach ($section->fields as $field)
                                    @php
                                        $value = old(
                                            'values.' . $field->id,
                                            $savedValues[$field->id] ?? null
                                        );
                                    @endphp

                                    <div>
                                        <label class="block text-sm font-medium mb-2">
                                            {{ $field->label }}
                                        </label>

                                        @if ($field->field_type === 'number')
                                            <input type="number"
                                                   min="0"
                                                   step="1"
                                                   name="values[{{ $field->id }}]"
                                                   value="{{ $value }}"
                                                   class="w-full rounded-md border-gray-300">

                                        @elseif ($field->field_type === 'textarea')
                                            <textarea
                                                name="values[{{ $field->id }}]"
                                                rows="4"
                                                class="w-full rounded-md border-gray-300">{{ $value }}</textarea>

                                        @elseif ($field->field_type === 'boolean')
                                            <select
                                                name="values[{{ $field->id }}]"
                                                class="w-full rounded-md border-gray-300">
                                                <option value="">Seleccione</option>
                                                <option value="1"
                                                    @selected((string) $value === '1')>
                                                    Sí
                                                </option>
                                                <option value="0"
                                                    @selected($value !== null && (string) $value === '0')>
                                                    No
                                                </option>
                                            </select>

                                        @else
                                            <input type="text"
                                                   name="values[{{ $field->id }}]"
                                                   value="{{ $value }}"
                                                   class="w-full rounded-md border-gray-300">
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach

                    <div class="flex justify-end gap-3 border-t pt-6">
                        <a href="{{ route('monthly-reports.index') }}"
                           class="px-4 py-2 border rounded-md">
                            Cancelar
                        </a>

                        <button type="submit"
                                class="px-5 py-2 bg-blue-700 text-white rounded-md">
                            Actualizar borrador
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>
