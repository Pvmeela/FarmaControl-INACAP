<?php

use Illuminate\Http\Request;
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
use App\Http\Controllers\UsuarioController;

/*
|--------------------------------------------------------------------------
| Página principal de FarmaControl
|--------------------------------------------------------------------------
|
| Pantalla de bienvenida y selección de entorno.
|
*/

Route::get('/', function () {
    return Inertia::render('Inicio');
})->name('inicio');


/*
|--------------------------------------------------------------------------
| Selección del entorno de trabajo
|--------------------------------------------------------------------------
|
| Permite seleccionar:
| - Farmacia privada
| - Farmacia CESFAM
|
| El entorno se guarda en la sesión.
| La selección no modifica roles ni permisos.
|
*/

Route::get('/seleccionar-entorno/{entorno}', function (
    Request $request,
    string $entorno
) {

    // Validar el entorno seleccionado.
    if (!in_array($entorno, ['privada', 'cesfam'], true)) {
        abort(404);
    }

    // Guardar el entorno en la sesión.
    $request->session()->put('entorno', $entorno);

    // Si el usuario ya inició sesión, ir al Dashboard.
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }

    // Si no ha iniciado sesión, ir al Login.
    return redirect()->route('login');

})->name('seleccionar.entorno');


/*
|--------------------------------------------------------------------------
| Autenticación
|--------------------------------------------------------------------------
|
| Rutas disponibles para usuarios no autenticados.
|
*/

Route::middleware('guest')->group(function () {

    // Mostrar formulario de inicio de sesión.
    Route::get('/login', [
        LoginController::class,
        'mostrarLogin'
    ])->name('login');

    // Procesar inicio de sesión.
    Route::post('/login', [
        LoginController::class,
        'iniciarSesion'
    ])
        ->middleware('throttle:5,1')
        ->name('login.attempt');

});


/*
|--------------------------------------------------------------------------
| Rutas protegidas
|--------------------------------------------------------------------------
|
| Solo pueden acceder usuarios autenticados.
|
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

    // Consultar medicamentos.
    Route::get('/medicamentos', [
        MedicamentoController::class,
        'index'
    ])->name('medicamentos.index');

    // Registrar medicamentos.
    Route::post('/medicamentos', [
        MedicamentoController::class,
        'store'
    ])->name('medicamentos.store');

    // Actualizar medicamentos.
    Route::put('/medicamentos/{medicamento}', [
        MedicamentoController::class,
        'update'
    ])->name('medicamentos.update');

    // Activar o desactivar medicamentos.
    Route::patch('/medicamentos/{medicamento}/estado', [
        MedicamentoController::class,
        'cambiarEstado'
    ])->name('medicamentos.estado');


    /*
    |--------------------------------------------------------------------------
    | Gestión de lotes - RF-07 y RF-08
    |--------------------------------------------------------------------------
    */

    // Consultar lotes.
    Route::get('/lotes', [
        LoteController::class,
        'index'
    ])->name('lotes.index');

    // Registrar lotes.
    Route::post('/lotes', [
        LoteController::class,
        'store'
    ])->name('lotes.store');


    /*
    |--------------------------------------------------------------------------
    | Movimientos de inventario y FEFO
    |--------------------------------------------------------------------------
    */

    // Consultar movimientos.
    Route::get('/movimientos', [
        MovimientoInventarioController::class,
        'index'
    ])->name('movimientos.index');

    // Registrar movimientos.
    Route::post('/movimientos', [
        MovimientoInventarioController::class,
        'store'
    ])->name('movimientos.store');


    /*
    |--------------------------------------------------------------------------
    | Ventas simuladas - RF-10
    |--------------------------------------------------------------------------
    |
    | Permite registrar ventas con varios medicamentos,
    | consultar el historial y visualizar comprobantes.
    |
    | Los comprobantes son exclusivamente académicos.
    | No tienen validez tributaria ni conexión con el SII.
    |
    */

    // Consultar historial de ventas.
    Route::get('/ventas', [
        VentaController::class,
        'index'
    ])->name('ventas.index');

    // Registrar una nueva venta simulada.
    Route::post('/ventas', [
        VentaController::class,
        'store'
    ])->name('ventas.store');

    // Mostrar boleta o factura simulada.
    Route::get('/ventas/{venta}/comprobante', [
        VentaController::class,
        'comprobante'
    ])->name('ventas.comprobante');


    /*
    |--------------------------------------------------------------------------
    | Pacientes simulados - RF-15
    |--------------------------------------------------------------------------
    */

    // Consultar pacientes.
    Route::get('/pacientes', [
        PacienteController::class,
        'index'
    ])->name('pacientes.index');

    // Registrar pacientes.
    Route::post('/pacientes', [
        PacienteController::class,
        'store'
    ])->name('pacientes.store');


    /*
    |--------------------------------------------------------------------------
    | Recetas farmacéuticas - RF-13
    |--------------------------------------------------------------------------
    */

    // Consultar recetas.
    Route::get('/recetas', [
        RecetaController::class,
        'index'
    ])->name('recetas.index');

    // Registrar recetas.
    Route::post('/recetas', [
        RecetaController::class,
        'store'
    ])->name('recetas.store');


    /*
    |--------------------------------------------------------------------------
    | Dispensaciones asistenciales - RF-16
    |--------------------------------------------------------------------------
    |
    | Consultar dispensaciones:
    | Administrador, Químico Farmacéutico y Técnico.
    |
    | Registrar dispensaciones:
    | Administrador y Técnico en Farmacia.
    |
    */

    // Consultar dispensaciones.
    Route::get('/dispensaciones', [
        DispensacionController::class,
        'index'
    ])
        ->middleware(
            'rol:Administrador,Quimico Farmaceutico,Tecnico en Farmacia'
        )
        ->name('dispensaciones.index');

    // Registrar dispensaciones.
    Route::post('/dispensaciones', [
        DispensacionController::class,
        'store'
    ])
        ->middleware(
            'rol:Administrador,Tecnico en Farmacia'
        )
        ->name('dispensaciones.store');


    /*
    |--------------------------------------------------------------------------
    | Administración de usuarios y roles
    |--------------------------------------------------------------------------
    |
    | Acceso exclusivo para el Administrador.
    |
    */

    Route::middleware('rol:Administrador')->group(function () {

        // Consultar usuarios y roles.
        Route::get('/usuarios', [
            UsuarioController::class,
            'index'
        ])->name('usuarios.index');

        // Registrar nuevos usuarios.
        Route::post('/usuarios', [
            UsuarioController::class,
            'store'
        ])->name('usuarios.store');

    });


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
