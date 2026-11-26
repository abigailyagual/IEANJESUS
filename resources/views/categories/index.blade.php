
@extends('layouts.system')

@section('title', 'Departamentos')
@section('page-title', 'Gestión de departamentos')
@section('eyebrow', 'Administración institucional')

@section('content')
    <div class="grid gap-6 xl:grid-cols-[360px,1fr]">
        <section class="panel p-5">
            <h2 class="text-base font-semibold text-slate-900">
                Nuevo departamento
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                El código identifica al departamento en los formularios e informes.
            </p>

            <form method="POST" action="{{ route('categories.store') }}" class="mt-5 space-y-4">
                @csrf

                <div>
                    <x-input-label for="department_name" :value="'Nombre del departamento'" />
                    <x-text-input
                        id="department_name"
                        name="name"
                        type="text"
                        class="mt-1 block w-full"
                        required
                    />
                </div>

                <div>
                    <x-input-label for="department_code" :value="'Código del departamento'" />
                    <x-text-input
                        id="department_code"
                        name="code"
                        type="text"
                        class="mt-1 block w-full"
                        placeholder="Por ejemplo, FIN"
                    />
                </div>

                <button type="submit" class="btn-primary w-full">
                    Agregar departamento
                </button>
            </form>
        </section>

        <section class="panel overflow-hidden">
            <div class="panel-header">
                <div>
                    <h2 class="text-base font-semibold text-slate-900">
                        Departamentos registrados
                    </h2>

                    <p class="text-sm text-slate-500">
                        Los departamentos se asignan a los usuarios para gestionar sus informes mensuales.
                    </p>
                </div>

                <form method="POST" action="{{ route('categories.search') }}" class="flex gap-3">
                    @csrf

                    <input
                        type="text"
                        name="search"
                        value="{{ $search }}"
                        class="block rounded-md border-slate-300 shadow-sm focus:border-slate-400 focus:ring-slate-400"
                        placeholder="Buscar departamento"
                    >

                    <button type="submit" class="btn-secondary">
                        Buscar
                    </button>
                </form>
            </div>

            <div class="divide-y divide-slate-200">
                @forelse ($categories as $category)
                    <div class="px-5 py-5">
                        <div class="grid gap-4 lg:grid-cols-[1fr,180px,auto] lg:items-end">

                            <form
                                method="POST"
                                action="{{ route('categories.update', $category) }}"
                                class="contents"
                            >
                                @csrf
                                @method('PUT')

                                <div>
                                    <x-input-label :value="'Nombre'" />
                                    <x-text-input
                                        name="name"
                                        type="text"
                                        class="mt-1 block w-full"
                                        :value="$category->name"
                                    />
                                </div>

                                <div>
                                    <x-input-label :value="'Código'" />
                                    <x-text-input
                                        name="code"
                                        type="text"
                                        class="mt-1 block w-full"
                                        :value="$category->code"
                                    />
                                </div>

                                <button type="submit" class="btn-secondary">
                                    Guardar
                                </button>
                            </form>

                            <form
                                method="POST"
                                action="{{ route('categories.destroy', $category) }}"
                            >
                                @csrf
                                @method('DELETE')

                                <button
                                    type="submit"
                                    class="btn-danger w-full"
                                    onclick="return confirm('¿Está seguro de eliminar este departamento?')"
                                >
                                    Eliminar
                                </button>
                            </form>

                        </div>
                    </div>
                @empty
                    <div class="px-5 py-12 text-center text-slate-500">
                        Todavía no se han registrado departamentos.
                    </div>
                @endforelse
            </div>

            <div class="border-t border-slate-200 px-5 py-4">
                {{ $categories->links() }}
            </div>
        </section>
    </div>
@endsection
