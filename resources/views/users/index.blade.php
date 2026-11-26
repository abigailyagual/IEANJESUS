@extends('layouts.system')

@section('title', 'Usuarios')
@section('page-title', 'Administración de usuarios')
@section('eyebrow', 'Cuentas de usuario y permisos de acceso')

@section('page-actions')
    <a href="{{ route('users.create') }}" class="btn-primary">
        Agregar usuario
    </a>
@endsection

@section('content')
    <section class="panel overflow-hidden">
        <div class="panel-header">
            <div>
                <h2 class="text-base font-semibold text-slate-900">
                    Listado de usuarios
                </h2>
                <p class="text-sm text-slate-500">
                    Administre los usuarios, asigne roles y departamentos
                    para controlar el acceso a los informes institucionales.
                </p>
            </div>

            <form method="POST" action="{{ route('users.search') }}" class="flex gap-3">
                @csrf

                <input
                    type="text"
                    name="search"
                    value="{{ $search }}"
                    class="block rounded-md border-slate-300 shadow-sm focus:border-slate-400 focus:ring-slate-400"
                    placeholder="Buscar por nombre, correo o cargo"
                >

                <button type="submit" class="btn-secondary">
                    Buscar
                </button>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-slate-500">
                    <tr>
                        <th class="px-5 py-3 font-medium">Usuario</th>
                        <th class="px-5 py-3 font-medium">Cargo</th>
                        <th class="px-5 py-3 font-medium">Departamentos</th>
                        <th class="px-5 py-3 font-medium">Rol</th>
                        <th class="px-5 py-3 font-medium">Estado</th>
                        <th class="px-5 py-3 font-medium">Acciones</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-200 bg-white">
                    @forelse ($users as $user)
                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-4">
                                <div class="font-medium text-slate-900">
                                    {{ $user->name }}
                                </div>

                                <div class="mt-1 text-xs text-slate-500">
                                    {{ $user->email }}
                                </div>
                            </td>

                            <td class="px-5 py-4 text-slate-600">
                                {{ $user->position ?: 'No especificado' }}
                            </td>

                            <td class="px-5 py-4">
                                <div class="flex flex-wrap gap-2">
                                    @forelse ($user->categories as $category)
                                        <span class="badge-slate">
                                            {{ $category->name }}
                                        </span>
                                    @empty
                                        <span class="text-slate-400">
                                            Sin departamentos asignados
                                        </span>
                                    @endforelse
                                </div>
                            </td>

                            <td class="px-5 py-4">
                                <span class="{{ $user->isAdmin() ? 'badge-blue' : ($user->isOffice() ? 'badge-amber' : 'badge-slate') }}">
                                    {{ $user->role_label }}
                                </span>
                            </td>
                            <td class="px-5 py-4">
                                @if ($user->isActive())
                                    <span class="badge-blue">Activo</span>
                                @else
                                    <span class="badge-slate">Inactivo</span>
                                @endif
                            </td>  
                            
<td class="px-5 py-4">
    <div class="flex flex-wrap items-center gap-2">

        <a href="{{ route('users.edit', $user) }}"
           class="btn-secondary">
            Editar
        </a>

        @if ($user->id !== auth()->id())

            @if ($user->isActive())
                <form method="POST"
                      action="{{ route('users.deactivate', $user) }}">
                    @csrf
                    @method('PATCH')

                    <button
                        type="submit"
                        class="btn-danger"
                        onclick="return confirm('¿Está seguro de desactivar esta cuenta? El usuario no podrá ingresar al sistema.')"
                    >
                        Desactivar
                    </button>
                </form>
            @else
                <form method="POST"
                      action="{{ route('users.activate', $user) }}">
                    @csrf
                    @method('PATCH')

                    <button
                        type="submit"
                        class="btn-primary"
                        onclick="return confirm('¿Desea reactivar esta cuenta?')"
                    >
                        Activar
                    </button>
                </form>
            @endif

        @else
            <span class="text-xs text-slate-400">
                Mi cuenta
            </span>
        @endif

    </div>
</td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-14 text-center text-slate-500">
                                No se encontraron usuarios.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-slate-200 px-5 py-4">
            {{ $users->links() }}
        </div>
    </section>
@endsection
