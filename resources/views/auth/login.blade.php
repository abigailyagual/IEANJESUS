
@section('title', 'Iniciar sesión')

<x-guest-layout>
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        <div>
            <x-input-label for="email" :value="'Correo electrónico'" />
            <x-text-input
                id="email"
                class="mt-1 block w-full"
                type="email"
                name="email"
                :value="old('email')"
                required
                autofocus
                autocomplete="username"
            />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password" :value="'Contraseña'" />
            <x-text-input
                id="password"
                class="mt-1 block w-full"
                type="password"
                name="password"
                required
                autocomplete="current-password"
            />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <label for="remember_me"
               class="inline-flex items-center gap-2 text-sm text-slate-600">
            <input
                id="remember_me"
                type="checkbox"
                class="rounded border-slate-300 text-slate-900 shadow-sm focus:ring-slate-400"
                name="remember"
            >
            <span>Recordar sesión</span>
        </label>

        <button type="submit" class="btn-primary w-full">
            Iniciar sesión
        </button>
    </form>
</x-guest-layout>
