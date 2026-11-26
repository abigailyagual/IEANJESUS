<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\MonthlyReportController;


//---------------- DASHBOARD ----------------

Route::get('/', [DashboardController::class, 'index'])
    ->middleware(['auth'])
    ->name('dashboard');


//---------------- ADMINISTRADOR ----------------


    // Usuarios
    Route::middleware(['auth', 'role:0'])->group(function () {

        Route::resource('users', UserController::class)
            ->except(['show']);

        Route::post('/users/search',
            [UserController::class, 'search'])
            ->name('users.search');

        // Activar y desactivar cuentas de usuario
        Route::patch('/users/{user}/activate',
            [UserController::class, 'activate'])
            ->name('users.activate');

        Route::patch('/users/{user}/deactivate',
            [UserController::class, 'deactivate'])
            ->name('users.deactivate');
    });


// Categorías
Route::middleware(['auth', 'role:0'])->group(function () {

    Route::resource('categories', CategoryController::class)
        ->only(['index', 'store', 'update', 'destroy']);

    Route::post('/categories/search',
        [CategoryController::class, 'search'])
        ->name('categories.search');
});


//---------------- USUARIOS AUTENTICADOS ----------------

Route::middleware(['auth'])->group(function () {

    // Perfil
    Route::get('/profile',
        [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/profile',
        [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::delete('/profile',
        [ProfileController::class, 'destroy'])
        ->name('profile.destroy');


    // Documentos
    Route::resource('documents', DocumentController::class);

    Route::post('documents/search',
        [DocumentController::class, 'search'])
        ->name('documents.search');

    Route::post('documents/{document}/submit',
        [DocumentController::class, 'submit'])
        ->name('documents.submit');

    Route::post('documents/{document}/approve',
        [DocumentController::class, 'approve'])
        ->name('documents.approve');

    Route::post('documents/{document}/reject',
        [DocumentController::class, 'reject'])
        ->name('documents.reject');

    Route::post('documents/{document}/archive',
        [DocumentController::class, 'archive'])
        ->name('documents.archive');
});


//---------------- CONSULTA DE CATEGORÍAS ----------------

Route::middleware(['auth'])->group(function () {

    Route::resource('categories', CategoryController::class)
        ->only('show');
});


//---------------- INFORMES MENSUALES ----------------

Route::middleware(['auth'])->group(function () {

    // 1. Listado de informes
    Route::get('/informes-mensuales',
        [MonthlyReportController::class, 'index'])
        ->name('monthly-reports.index');


    // 2. Formulario para crear informe
    Route::get('/informes-mensuales/crear/{template}',
        [MonthlyReportController::class, 'create'])
        ->name('monthly-reports.create');


    // 3. Guardar nuevo borrador
    Route::post('/informes-mensuales/crear/{template}',
        [MonthlyReportController::class, 'store'])
        ->name('monthly-reports.store');


    // 4. Formulario para editar borrador
    Route::get('/informes-mensuales/{report}/editar',
        [MonthlyReportController::class, 'edit'])
        ->name('monthly-reports.edit');


    // 5. Actualizar borrador existente
    Route::put('/informes-mensuales/{report}',
        [MonthlyReportController::class, 'update'])
        ->name('monthly-reports.update');


    // 6. Enviar informe a revisión
    Route::post('/informes-mensuales/{report}/enviar',
        [MonthlyReportController::class, 'submit'])
        ->name('monthly-reports.submit');


    // 7. NUEVO: Iniciar revisión
    Route::post('/informes-mensuales/{report}/revisar',
        [MonthlyReportController::class, 'startReview'])
        ->name('monthly-reports.review');


    // 8. NUEVO: Aprobar informe
    Route::post('/informes-mensuales/{report}/aprobar',
        [MonthlyReportController::class, 'approve'])
        ->name('monthly-reports.approve');


    // 9. NUEVO: Devolver informe con observaciones
    Route::post('/informes-mensuales/{report}/observar',
        [MonthlyReportController::class, 'observe'])
        ->name('monthly-reports.observe');
    
    // 10. Consultar informe completo
    Route::get('/informes-mensuales/{report}/ver',
        [MonthlyReportController::class, 'show'])
        ->name('monthly-reports.show');

    


});


//---------------- AUTENTICACIÓN ----------------

require __DIR__.'/auth.php';
