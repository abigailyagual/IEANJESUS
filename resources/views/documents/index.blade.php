
@extends('layouts.system')

@section('title', 'Informes mensuales')
@section('page-title', 'Gestión de informes mensuales')
@section('eyebrow', 'IEANJESÚS · Registro y seguimiento institucional')

@section('page-actions')
    <a href="{{ route('documents.create') }}" class="btn-primary">
        Nuevo informe mensual
    </a>
@endsection

@section('content')
    @livewire('document-journal', [
        'search' => $search,
        'categoryId' => $activeCategory,
        'status' => $activeStatus,
        'onlyTasks' => $onlyTasks,
    ])
@endsection

