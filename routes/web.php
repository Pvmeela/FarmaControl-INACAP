<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MedicamentoController;
use App\Http\Controllers\LoteController;
use App\Http\Controllers\MovimientoInventarioController;
use App\Http\Controllers\VentaController;
use App\Http\Controllers\PacienteController;
use App\Http\Controllers\RecetaController;
use App\Http\Controllers\DispensacionController;

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
    | Dashboard y alertas de inventario
    |--------------------------------------------------------------------------
    */

    Route::get('/dashboard', [
        DashboardController::class,
        'index'
    ])->name('dashboard');

    /*
    |--------------------------------------------------------------------------
    | Gestión de medicamentos - RF-05
    |--------------------------------------------------------------------------
    */

    Route::get('/medicamentos', [
        MedicamentoController::class,
        'index'
    ])->name('medicamentos.index');

    Route::post('/medicamentos', [
        MedicamentoController::class,
        'store'
    ])->name('medicamentos.store');

    Route::put('/medicamentos/{medicamento}', [
        MedicamentoController::class,
        'update'
    ])->name('medicamentos.update');

    Route::patch('/medicamentos/{medicamento}/estado', [
        MedicamentoController::class,
        'cambiarEstado'
    ])->name('medicamentos.estado');

    /*
    |--------------------------------------------------------------------------
    | Gestión de lotes - RF-07 y RF-08
    |--------------------------------------------------------------------------
    */

    Route::get('/lotes', [
        LoteController::class,
        'index'
    ])->name('lotes.index');

    Route::post('/lotes', [
        LoteController::class,
        'store'
    ])->name('lotes.store');

    /*
    |--------------------------------------------------------------------------
    | Movimientos de inventario y FEFO
    |--------------------------------------------------------------------------
    */

    Route::get('/movimientos', [
        MovimientoInventarioController::class,
        'index'
    ])->name('movimientos.index');

    Route::post('/movimientos', [
        MovimientoInventarioController::class,
        'store'
    ])->name('movimientos.store');

    /*
    |--------------------------------------------------------------------------
    | Ventas simuladas - RF-10
    |--------------------------------------------------------------------------
    */

    Route::get('/ventas', [
        VentaController::class,
        'index'
    ])->name('ventas.index');

    Route::post('/ventas', [
        VentaController::class,
        'store'
    ])->name('ventas.store');

    /*
    |--------------------------------------------------------------------------
    | Pacientes simulados - RF-15
    |--------------------------------------------------------------------------
    */

    Route::get('/pacientes', [
        PacienteController::class,
        'index'
    ])->name('pacientes.index');

    Route::post('/pacientes', [
        PacienteController::class,
        'store'
    ])->name('pacientes.store');

    /*
    |--------------------------------------------------------------------------
    | Recetas farmacéuticas - RF-13
    |--------------------------------------------------------------------------
    */

    Route::get('/recetas', [
        RecetaController::class,
        'index'
    ])->name('recetas.index');

    Route::post('/recetas', [
        RecetaController::class,
        'store'
    ])->name('recetas.store');

    /*
    |--------------------------------------------------------------------------
    | Dispensaciones asistenciales - RF-16
    |--------------------------------------------------------------------------
    */

    // Consultar dispensaciones
    Route::get('/dispensaciones', [
        DispensacionController::class,
        'index'
    ])->middleware(
        'rol:Administrador,Quimico Farmaceutico,Tecnico en Farmacia'
    )->name('dispensaciones.index');

    // Registrar dispensaciones
    Route::post('/dispensaciones', [
        DispensacionController::class,
        'store'
    ])->middleware(
        'rol:Administrador,Tecnico en Farmacia'
    )->name('dispensaciones.store');

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
