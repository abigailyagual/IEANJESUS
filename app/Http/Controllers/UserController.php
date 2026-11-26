<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserStore;
use App\Http\Requests\UserUpdate;
use App\Models\Category;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    /**
     * Display users list.
     */
    public function index(Request $request): View
    {
        abort_unless(auth()->user()->isAdmin(), 403);

        $search = trim((string) $request->input('search', ''));
        $users = User::query()
            ->with('categories')
            ->when($search !== '', fn ($query) => $query
                ->where('name', 'like', '%' . $search . '%')
                ->orWhere('email', 'like', '%' . $search . '%')
                ->orWhere('position', 'like', '%' . $search . '%'))
            ->orderBy('role')
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        return view('users.index', [
            'users' => $users,
            'categories' => Category::orderBy('name')->get(),
            'search' => $search,
        ]);

    }

    /**
     * Show the form for creating a new user.
     */
    public function create(): View
    {
        abort_unless(auth()->user()->isAdmin(), 403);

        return view('users.create', [
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    /**
     * Store a newly created user.
     */
    public function store(UserStore $request): RedirectResponse
    {
        abort_unless(auth()->user()->isAdmin(), 403);

        $validated = $request->validated();
        $categories = $validated['categories'];
        unset($validated['categories']);
        $validated['role'] = (int) ($validated['role'] ?? User::ROLE_EMPLOYEE);

        $user = User::create($validated);
        $user->categories()->sync($categories);

        event(new Registered($user));

        return redirect()
            ->route('users.index')
            ->with('status', 'Usuario registrado correctamente.');
    }

    /**
     * Show the form for editing a user.
     */
    public function edit(User $user): View
    {
        abort_unless(auth()->user()->isAdmin(), 403);

        return view('users.edit', [
            'user' => $user->load('categories'),
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    /**
     * Update user.
     */
    
/**
 * Actualizar los datos de un usuario.
 */
public function update(UserUpdate $request, User $user): RedirectResponse
{
    abort_unless(auth()->user()->isAdmin(), 403);

    $validated = $request->validated();

    $categories = $validated['categories'] ?? [];
    unset($validated['categories']);

    if (empty($validated['password'])) {
        unset($validated['password']);
    }

    DB::transaction(function () use ($user, $validated, $categories) {

        // Bloqueo común con el método deactivate().
        $this->lockAdminManagement();

        // Obtener los datos actualizados del usuario y bloquear su fila.
        $target = User::whereKey($user->id)
            ->lockForUpdate()
            ->firstOrFail();

        $newRole = (int) ($validated['role'] ?? $target->role);

        // Impedir cambiar el rol del último Administrador activo.
        if (
            $target->isAdmin() &&
            $target->isActive() &&
            $newRole !== User::ROLE_ADMIN
        ) {
            $activeAdmins = User::where('role', User::ROLE_ADMIN)
                ->where('is_active', true)
                ->count();

            abort_if(
                $activeAdmins <= 1,
                403,
                'No puede cambiar el rol del último Administrador activo.'
            );
        }

        $data = $validated;
        $data['role'] = $newRole;

        $target->update($data);

        // Conservar las asignaciones de departamentos.
        $target->categories()->sync($categories);
    });

    return redirect()
        ->route('users.index')
        ->with('status', 'Datos del usuario actualizados correctamente.');
}

    /**
     * Delete user.
     */
    
/**
 * La eliminación permanente de usuarios está deshabilitada
 * para proteger el historial institucional.
 */
public function destroy(User $user): RedirectResponse
{
    abort_unless(auth()->user()->isAdmin(), 403);

    abort(403, 'No se permite eliminar usuarios permanentemente. Utilice la opción de desactivar cuenta.');
}


    /**
     * Redirect search to the index page.
     */
    public function search(Request $request): RedirectResponse
    {
        return redirect()->route('users.index', [
            'search' => $request->input('search'),
        ]);
    }

/**
 * Activar una cuenta de usuario.
 */
public function activate(User $user): RedirectResponse
{
    abort_unless(auth()->user()->isAdmin(), 403);

    $user->update([
        'is_active' => true,
    ]);

    return redirect()
        ->route('users.index')
        ->with('status', 'Usuario activado correctamente.');
}

/**
 * Desactivar una cuenta de usuario.
 */

/**
 * Desactivar una cuenta sin eliminar su historial.
 */

/**
 * Desactivar una cuenta sin eliminar su historial.
 */
public function deactivate(User $user): RedirectResponse
{
    abort_unless(auth()->user()->isAdmin(), 403);

    DB::transaction(function () use ($user) {

        // Evitar modificaciones administrativas simultáneas.
        $this->lockAdminManagement();

        $target = User::whereKey($user->id)
            ->lockForUpdate()
            ->firstOrFail();

        abort_if(
            $target->id === auth()->id(),
            403,
            'No puede desactivar su propia cuenta.'
        );

        if ($target->isAdmin() && $target->isActive()) {
            $activeAdmins = User::where('role', User::ROLE_ADMIN)
                ->where('is_active', true)
                ->count();

            abort_if(
                $activeAdmins <= 1,
                403,
                'No puede desactivar la última cuenta de Administrador activa.'
            );
        }

        $target->update([
            'is_active' => false,
        ]);
    });

    return redirect()
        ->route('users.index')
        ->with('status', 'Usuario desactivado correctamente.');
}
/**
 * Bloqueo común para operaciones que modifican
 * las cuentas de Administrador.
 *
 * PostgreSQL libera el bloqueo automáticamente
 * al finalizar la transacción.
 */
private function lockAdminManagement(): void
{
    DB::select('SELECT pg_advisory_xact_lock(20261110, 1)');
}

}
