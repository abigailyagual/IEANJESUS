<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Gestión de informes mensuales
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('success'))
                <div class="p-4 bg-green-100 text-green-800 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="p-4 bg-red-100 text-red-800 rounded-lg">
                    <ul class="list-disc pl-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Plantillas --}}
            @if (auth()->user()->canCreateMonthlyReports())
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold mb-4">
                    Crear nuevo informe mensual
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach ($templates as $template)
                        @continue(!auth()->user()->canCreateMonthlyReportFor((int) $template->category_id))
                        <div class="border rounded-lg p-4">
                            <h4 class="font-semibold">
                                {{ $template->name }}
                            </h4>

                            <p class="text-sm text-gray-500 mt-2">
                                {{ $template->description }}
                            </p>

                            <a href="{{ route('monthly-reports.create', $template) }}"
                               class="inline-block mt-4 text-blue-700 font-medium hover:underline">
                                Abrir formulario
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>

            @endif

            {{-- Informes registrados --}}
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold mb-4">
                    Informes registrados
                </h3>

                {{-- Filtros de búsqueda de informes --}}
                <form method="GET" action="{{ route('monthly-reports.index') }}"
                      class="mb-6 rounded-lg border border-gray-200 bg-gray-50 p-4">
                    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
                        <div>
                            <label for="category_id" class="mb-1 block text-sm font-medium text-gray-700">Departamento</label>
                            <select id="category_id" name="category_id" class="w-full rounded-md border-gray-300">
                                <option value="">Todos los departamentos</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}" @selected(request('category_id') == $category->id)>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="report_month" class="mb-1 block text-sm font-medium text-gray-700">Mes</label>
                            <select id="report_month" name="report_month" class="w-full rounded-md border-gray-300">
                                <option value="">Todos los meses</option>
                                @foreach ([1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril', 5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto', 9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'] as $number => $month)
                                    <option value="{{ $number }}" @selected(request('report_month') == $number)>{{ $month }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="report_year" class="mb-1 block text-sm font-medium text-gray-700">Año</label>
                            <select id="report_year" name="report_year" class="w-full rounded-md border-gray-300">
                                <option value="">Todos los años</option>
                                @for ($year = now()->year; $year >= 2020; $year--)
                                    <option value="{{ $year }}" @selected(request('report_year') == $year)>{{ $year }}</option>
                                @endfor
                            </select>
                        </div>
                        <div>
                            <label for="status" class="mb-1 block text-sm font-medium text-gray-700">Estado</label>
                            <select id="status" name="status" class="w-full rounded-md border-gray-300">
                                <option value="">Todos los estados</option>
                                @foreach (['draft' => 'Borrador', 'submitted' => 'Enviado', 'in_review' => 'En revisión', 'observed' => 'Observado', 'approved' => 'Aprobado'] as $value => $label)
                                    <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="mt-4">
                        <label for="search" class="mb-1 block text-sm font-medium text-gray-700">Buscar informe</label>
                        <input id="search" name="search" type="text" maxlength="100"
                               value="{{ request('search') }}" placeholder="Nombre del informe o número de ID"
                               class="w-full rounded-md border-gray-300">
                    </div>
                    <div class="mt-4 flex flex-wrap items-center gap-3">
                        <button type="submit" class="rounded-md bg-blue-600 px-5 py-2 text-white hover:bg-blue-700">Buscar informes</button>
                        <a href="{{ route('monthly-reports.index') }}"
                           class="rounded-md border border-gray-300 bg-white px-5 py-2 text-gray-700 hover:bg-gray-100">Limpiar filtros</a>
                        <span class="text-sm text-gray-600">{{ $reports->total() }} informes encontrados</span>
                    </div>
                </form>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="p-3 text-left">ID</th>
                                <th class="p-3 text-left">Informe</th>
                                <th class="p-3 text-left">Departamento</th>
                                <th class="p-3 text-left">Período</th>
                                <th class="p-3 text-left">Elaborado por</th>
                                <th class="p-3 text-left">Estado</th>
                                <th class="p-3 text-left">Acciones</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-200">
                            @forelse ($reports as $report)
                                <tr>
                                    <td class="p-3">
                                        {{ $report->id }}
                                    </td>

                                    <td class="p-3">
                                        {{ $report->template?->name }}
                                    </td>

                                    <td class="p-3">
                                        {{ $report->category?->name }}
                                    </td>

                                    <td class="p-3">
                                        {{ str_pad($report->report_month, 2, '0', STR_PAD_LEFT) }}
                                        /
                                        {{ $report->report_year }}
                                    </td>

                                    <td class="p-3">
                                        {{ $report->user?->name }}
                                    </td>

                                    <td class="p-3">
                                        @switch($report->status)
                                            @case('draft')
                                                <span class="text-yellow-700 font-semibold">
                                                    Borrador
                                                </span>
                                                @break

                                            @case('submitted')
                                                <span class="text-blue-700 font-semibold">
                                                    Enviado
                                                </span>
                                                @break

                                            @case('in_review')
                                                <span class="text-indigo-700 font-semibold">
                                                    En revisión
                                                </span>
                                                @break

                                            @case('observed')
                                                <span class="text-orange-700 font-semibold">
                                                    Observado
                                                </span>
                                                @break

                                            @case('approved')
                                                <span class="text-green-700 font-semibold">
                                                    Aprobado
                                                </span>
                                                @break
                                        @endswitch
                                    </td>

                                    <td class="p-3">

    <div class="mb-2">
        <a href="{{ route('monthly-reports.show', $report) }}"
            class="text-blue-700 font-semibold hover:underline">
            Ver informe
        </a>
    </div>

                                        @if ($report->status === 'draft' && auth()->user()->canEditMonthlyReport($report))
                                            <div class="flex items-center gap-3">
                                                <a href="{{ route('monthly-reports.edit', $report) }}"
                                                   class="text-blue-700 font-medium hover:underline">
                                                    Editar
                                                </a>

                                                <form method="POST"
                                                      action="{{ route('monthly-reports.submit', $report) }}"
                                                      onsubmit="return confirm('¿Enviar este informe a revisión?');">
                                                    @csrf

                                                    <button type="submit"
                                                            class="text-green-700 font-medium hover:underline">
                                                        Enviar a revisión
                                                    </button>
                                                </form>
                                            </div>

                                        @elseif ($report->status === 'submitted')
                                            @if (auth()->user()->canReviewMonthlyReports())
                                                <form method="POST"
                                                      action="{{ route('monthly-reports.review', $report) }}">
                                                    @csrf

                                                    <button type="submit"
                                                            class="text-indigo-700 font-medium hover:underline">
                                                        Iniciar revisión
                                                    </button>
                                                </form>
                                            @else
                                                <span class="text-gray-500">
                                                    Pendiente de revisión
                                                </span>
                                            @endif

                                        @elseif ($report->status === 'in_review')
                                            @if (auth()->user()->canApproveMonthlyReports())
                                                <div class="space-y-3">

                                                    <form method="POST"
                                                          action="{{ route('monthly-reports.approve', $report) }}"
                                                          onsubmit="return confirm('¿Aprobar definitivamente este informe?');">
                                                        @csrf

                                                        <button type="submit"
                                                                class="text-green-700 font-semibold hover:underline">
                                                            Aprobar informe
                                                        </button>
                                                    </form>

                                                    <form method="POST"
                                                          action="{{ route('monthly-reports.observe', $report) }}">
                                                        @csrf

                                                        <textarea
                                                            name="observations"
                                                            rows="3"
                                                            required
                                                            maxlength="5000"
                                                            placeholder="Escriba las observaciones..."
                                                            class="w-full rounded-md border-gray-300 text-sm"></textarea>

                                                        <button type="submit"
                                                                class="mt-2 text-orange-700 font-semibold hover:underline">
                                                            Devolver con observaciones
                                                        </button>
                                                    </form>
                                                </div>
                                            @else
                                                <span class="text-gray-500">
                                                    En revisión
                                                </span>
                                            @endif

                                        

@elseif ($report->status === 'observed' && auth()->user()->canEditMonthlyReport($report))

    <div class="space-y-3">

        <p class="font-semibold text-orange-700">
            Requiere correcciones
        </p>

        @if ($report->observations)
            <div class="bg-orange-50 border border-orange-200 rounded p-3">
                <p class="text-sm font-semibold">
                    Observaciones:
                </p>

                <p class="text-sm mt-1 whitespace-pre-line">
                    {{ $report->observations }}
                </p>
            </div>
        @endif

        <div class="flex flex-col gap-2">

            <a href="{{ route('monthly-reports.edit', $report) }}"
               class="text-blue-700 font-semibold hover:underline">
                Corregir informe
            </a>

            <form method="POST"
                  action="{{ route('monthly-reports.submit', $report) }}"
                  onsubmit="return confirm('¿Desea reenviar el informe corregido?');">
                @csrf

                <button type="submit"
                        class="text-green-700 font-semibold hover:underline">
                    Reenviar a revisión
                </button>
            </form>

        </div>

    </div>



                                        @elseif ($report->status === 'approved')
                                            <div>
                                                <span class="text-green-700 font-semibold">
                                                    Informe aprobado
                                                </span>

                                                @if ($report->approved_at)
                                                    <p class="text-xs text-gray-500 mt-1">
                                                        {{ $report->approved_at->format('d/m/Y H:i') }}
                                                    </p>
                                                @endif
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7"
                                        class="p-6 text-center text-gray-500">
                                        No existen informes registrados.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $reports->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
