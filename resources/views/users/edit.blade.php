@extends('layouts.system')

@section('title', 'Editar usuario')
@section('page-title', 'Editar usuario')
@section('eyebrow', $user->email)

@section('page-actions')
    <a href="{{ route('users.index') }}" class="btn-secondary">
        Volver al listado
    </a>
@endsection

@section('content')
    <section class="panel">
        <div class="panel-header">
            <div>
                <h2 class="text-base font-semibold text-slate-900">
                    {{ $user->name }}
                </h2>

                <p class="text-sm text-slate-500">
                    Actualice los datos personales, el cargo, el rol y los
                    departamentos asignados al usuario sin eliminar su cuenta
                    ni afectar su historial de informes.
                </p>
            </div>
        </div>

        <form
            method="POST"
            action="{{ route('users.update', $user) }}"
            class="space-y-6 px-5 py-5"
        >
            @csrf
            @method('PUT')

            @include('users._form', ['user' => $user])

            <div class="flex flex-wrap items-center gap-3 border-t border-slate-200 pt-5">
                <button type="submit" class="btn-primary">
                    Guardar cambios
                </button>

                <a href="{{ route('users.index') }}" class="btn-secondary">
                    Cancelar
                </a>
            </div>
        </form>
    </section>
@endsection
