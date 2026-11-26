
@extends('layouts.system')

@section('title', 'Nuevo informe mensual')
@section('page-title', 'Registrar nuevo informe mensual')
@section('eyebrow', 'IEANJESÚS · Registro de informes institucionales')

@section('page-actions')
    <a href="{{ route('documents.index') }}" class="btn-secondary">
        Volver a informes mensuales
    </a>
@endsection

@section('content')

    <section class="panel">

        <div class="panel-header">
            <div>
                <h2 class="text-base font-semibold text-slate-900">
                    Datos del informe mensual
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    Complete la información del informe y adjunte
                    el archivo correspondiente. Después de guardarlo,
                    podrá enviarlo al proceso de revisión y aprobación.
                </p>
            </div>
        </div>

        <form
            method="POST"
            action="{{ route('documents.store') }}"
            enctype="multipart/form-data"
            class="space-y-6 px-5 py-5"
        >
            @csrf

            {{-- Campos del formulario --}}
            @include('documents._form')

            {{-- Acciones --}}
            <div class="flex flex-wrap items-center gap-3 border-t border-slate-200 pt-5">

                <button type="submit" class="btn-primary">
                    Guardar informe mensual
                </button>

                <a
                    href="{{ route('documents.index') }}"
                    class="btn-secondary"
                >
                    Cancelar
                </a>

            </div>

        </form>

    </section>

@endsection
