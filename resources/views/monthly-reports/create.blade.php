<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ $template->name }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">

                <form method="POST"
                      action="{{ route('monthly-reports.store', $template) }}">
                    @csrf

                    @if ($errors->any())
                        <div class="mb-6 p-4 bg-red-100 text-red-800 rounded">
                            <ul class="list-disc pl-5">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">

                        <div>
                            <label class="block font-medium mb-2">
                                Departamento
                            </label>
                            <select name="category_id"
                                    required
                                    class="w-full rounded-md border-gray-300">
                                <option value="">Seleccione</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}"
                                        @selected(old('category_id', $template->category_id) == $category->id)>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block font-medium mb-2">
                                Mes
                            </label>
                            <select name="report_month"
                                    required
                                    class="w-full rounded-md border-gray-300">
                                @foreach ([
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
                                    12 => 'Diciembre'
                                ] as $number => $month)
                                    <option value="{{ $number }}"
                                        @selected(old('report_month', now()->month) == $number)>
                                        {{ $month }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block font-medium mb-2">
                                Año
                            </label>
                            <input type="number"
                                   name="report_year"
                                   min="2020"
                                   max="2100"
                                   required
                                   value="{{ old('report_year', now()->year) }}"
                                   class="w-full rounded-md border-gray-300">
                        </div>

                    </div>

                    @foreach ($template->sections as $section)
                        <div class="mb-8">
                            <h3 class="text-lg font-semibold border-b pb-2 mb-4">
                                {{ $section->title }}
                            </h3>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                                @foreach ($section->fields as $field)
                                    <div>
                                        <label class="block text-sm font-medium mb-2">
                                            {{ $field->label }}
                                        </label>

                                        @if ($field->field_type === 'number')
                                            <input type="number"
                                                   min="0"
                                                   step="1"
                                                   name="values[{{ $field->id }}]"
                                                   value="{{ old('values.' . $field->id) }}"
                                                   class="w-full rounded-md border-gray-300">

                                        @elseif ($field->field_type === 'textarea')
                                            <textarea
                                                name="values[{{ $field->id }}]"
                                                rows="4"
                                                class="w-full rounded-md border-gray-300">{{ old('values.' . $field->id) }}</textarea>

                                        @elseif ($field->field_type === 'boolean')
                                            <select
                                                name="values[{{ $field->id }}]"
                                                class="w-full rounded-md border-gray-300">
                                                <option value="">Seleccione</option>
                                                <option value="1"
                                                    @selected(old('values.' . $field->id) === '1')>
                                                    Sí
                                                </option>
                                                <option value="0"
                                                    @selected(old('values.' . $field->id) === '0')>
                                                    No
                                                </option>
                                            </select>

                                        @else
                                            <input type="text"
                                                   name="values[{{ $field->id }}]"
                                                   value="{{ old('values.' . $field->id) }}"
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
                            Guardar borrador
                        </button>
                    </div>

                </form>

            </div>
        </div>
    </div>
</x-app-layout>

