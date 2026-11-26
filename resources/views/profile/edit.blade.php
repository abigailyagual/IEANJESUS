@extends('layouts.system')

@section('title', 'Mi perfil')
@section('page-title', 'Mi perfil')
@section('eyebrow', 'Configuración personal')

@section('content')
    <div class="space-y-6">
        <section class="panel p-5">
            @include('profile.partials.update-profile-information-form')
        </section>

        <section class="panel p-5">
            @include('profile.partials.update-password-form')
        </section>

        
    </div>
@endsection
