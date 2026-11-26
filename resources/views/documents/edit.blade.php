@extends('layouts.system')

@section('title', 'Editar documento')
@section('page-title', 'Editar documento')
@section('eyebrow', $document->registration_number)

@section('page-actions')
    <a href="{{ route('documents.show', $document) }}" class="btn-secondary">Volver a la ficha</a>
@endsection

@section('content')
    <section class="panel">
        <div class="panel-header">
            <div>
                <h2 class="text-base font-semibold text-slate-900">{{ $document->name }}</h2>
                <p class="text-sm text-slate-500">Se permite editar borradores y documentos devueltos para corrección.</p>
            </div>
        </div>
        <form method="POST" action="{{ route('documents.update', $document) }}" enctype="multipart/form-data" class="space-y-6 px-5 py-5">
            @csrf
            @method('PUT')
            @include('documents._form', ['document' => $document])

            <div class="flex flex-wrap items-center gap-3 border-t border-slate-200 pt-5">
                <button type="submit" class="btn-primary">Guardar cambios</button>
                <a href="{{ route('documents.show', $document) }}" class="btn-secondary">Cancelar</a>
            </div>
        </form>
    </section>
@endsection
