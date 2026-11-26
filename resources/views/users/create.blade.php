@extends('layouts.system')

@section('title', 'Agregar usuario')
@section('page-title', 'Registrar nuevo usuario')
@section('eyebrow', 'Administración de usuarios')

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
                    Información del usuario
                </h2>
                <p class="text-sm text-slate-500">
                    Registre los datos del usuario y asigne su rol y los
                    departamentos a los que tendrá acceso.
                </p>
            </div>
        </div>

        <form method="POST" action="{{ route('users.store') }}" class="space-y-6 px-5 py-5">
            @csrf

            @include('users._form')

            <div class="flex flex-wrap items-center gap-3 border-t border-slate-200 pt-5">
                <button type="submit" class="btn-primary">
                    Registrar usuario
                </button>

                <a href="{{ route('users.index') }}" class="btn-secondary">
                    Cancelar
                </a>
            </div>
        </form>
    </section>
@endsection
