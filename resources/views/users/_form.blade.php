@php
    $user = $user ?? null;

    $selectedCategories = collect(
        old('categories', session()->hasOldInput() ? [] : ($user?->categories?->pluck('id')->all() ?? []))
    )->map(fn ($value) => (int) $value)->all();
@endphp

<div class="grid gap-5 lg:grid-cols-2">

    <!-- NOMBRE COMPLETO -->
    <div>
        <x-input-label for="name" :value="'Nombre completo'" />
        <x-text-input
            id="name"
            name="name"
            type="text"
            class="mt-1 block w-full"
            :value="old('name', $user?->name)"
            required
        />
    </div>

    <!-- CORREO ELECTRÓNICO -->
    <div>
        <x-input-label for="email" :value="'Correo electrónico'" />
        <x-text-input
            id="email"
            name="email"
            type="email"
            class="mt-1 block w-full"
            :value="old('email', $user?->email)"
            required
        />
    </div>

    <!-- CARGO -->
    <div>
        <x-input-label for="position" :value="'Cargo o función'" />
        <x-text-input
            id="position"
            name="position"
            type="text"
            class="mt-1 block w-full"
            :value="old('position', $user?->position)"
        />
    </div>

    <!-- TELÉFONO -->
    <div>
        <x-input-label for="phone" :value="'Teléfono'" />
        <x-text-input
            id="phone"
            name="phone"
            type="text"
            class="mt-1 block w-full"
            :value="old('phone', $user?->phone)"
        />
    </div>

    <!-- CONTRASEÑA -->
    <div>
    <x-input-label
        for="password"
        :value="$user ? 'Nueva contraseña (opcional)' : 'Contraseña'"
    />

    <x-text-input
        id="password"
        name="password"
        type="password"
        class="mt-1 block w-full"
        autocomplete="new-password"
        :required="!$user"
    />
</div>

    <!-- CONFIRMAR CONTRASEÑA -->
    <div>
    <x-input-label
        for="password_confirmation"
        :value="'Confirmar contraseña'"
    />

    <x-text-input
        id="password_confirmation"
        name="password_confirmation"
        type="password"
        class="mt-1 block w-full"
        autocomplete="new-password"
        :required="!$user"
    />
</div>

    <!-- ROL -->
    <div>
        <x-input-label for="role" :value="'Rol del usuario'" />

        <select
            id="role"
            name="role"
            class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-slate-400 focus:ring-slate-400"
        >
            @foreach (\App\Models\User::ROLE_LABELS as $value => $label)
                <option
                    value="{{ $value }}"
                    @selected(
                        (string) old('role', $user?->role ?? \App\Models\User::ROLE_EMPLOYEE)
                        === (string) $value
                    )
                >
                    {{ $label }}
                </option>
            @endforeach
        </select>
    </div>

    <!-- DEPARTAMENTOS ASIGNADOS -->
    <div class="lg:col-span-2">
        <x-input-label :value="'Departamentos asignados'" />

        <p class="mt-1 text-sm text-slate-500">
            Obligatorio para secretarios y directores de departamento. Opcional para Administrador, Secretaría de Junta Local y Pastor.
        </p>

        <div class="mt-2 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($categories as $category)
                <label class="inline-flex items-center gap-3 rounded-md border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700">

                    <input
                        type="checkbox"
                        name="categories[]"
                        value="{{ $category->id }}"
                        class="rounded border-slate-300 text-slate-900 shadow-sm focus:ring-slate-400"
                        @checked(in_array((int) $category->id, $selectedCategories, true))
                    >

                    <span>{{ $category->name }}</span>

                    @if ($category->code)
                        <span class="badge-slate">
                            {{ $category->code }}
                        </span>
                    @endif

                </label>
            @endforeach
        </div>
    </div>

</div>
