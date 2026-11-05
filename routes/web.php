
<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\MedicamentoController;

/*
|--------------------------------------------------------------------------
| Página principal de FarmaControl
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return Inertia::render('Inicio');
})->name('inicio');

/*
|--------------------------------------------------------------------------
| Autenticación
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {

    // Mostrar formulario de inicio de sesión
    Route::get('/login', [
        LoginController::class,
        'mostrarLogin'
    ])->name('login');

    // Procesar inicio de sesión
    Route::post('/login', [
        LoginController::class,
        'iniciarSesion'
    ])->middleware('throttle:5,1')
      ->name('login.attempt');

});

/*
|--------------------------------------------------------------------------
| Rutas protegidas
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Panel principal
    |--------------------------------------------------------------------------
    */

    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    /*
    |--------------------------------------------------------------------------
    | Gestión de medicamentos - RF-05
    |--------------------------------------------------------------------------
    */

    // Consultar medicamentos registrados
    Route::get('/medicamentos', [
        MedicamentoController::class,
        'index'
    ])->name('medicamentos.index');

    // Registrar nuevos medicamentos
    Route::post('/medicamentos', [
        MedicamentoController::class,
        'store'
    ])->name('medicamentos.store');

    // Actualizar medicamentos existentes
    Route::put('/medicamentos/{medicamento}', [
        MedicamentoController::class,
        'update'
    ])->name('medicamentos.update');

    // Activar o desactivar medicamentos
    Route::patch('/medicamentos/{medicamento}/estado', [
        MedicamentoController::class,
        'cambiarEstado'
    ])->name('medicamentos.estado');

    /*
    |--------------------------------------------------------------------------
    | Cierre de sesión
    |--------------------------------------------------------------------------
    */

    Route::post('/logout', [
        LoginController::class,
        'cerrarSesion'
    ])->name('logout');

});
