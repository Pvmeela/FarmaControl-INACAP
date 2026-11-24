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
use App\Http\Controllers\ProgramaSaludController;
use App\Http\Controllers\CanastaMedicamentoController;

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
| Selección del entorno de trabajo
|--------------------------------------------------------------------------
|
| Farmacia Privada y Farmacia Asistencial (CESFAM).
| La selección no modifica roles ni permisos.
|
*/

Route::get('/seleccionar-entorno/{entorno}', function (
    Request $request,
    string $entorno
) {
    if (!in_array($entorno, ['privada', 'cesfam'], true)) {
        abort(404);
    }

    $request->session()->put('entorno', $entorno);

    if (auth()->check()) {
        return redirect()->route('dashboard');
    }

    return redirect()->route('login');

})->name('seleccionar.entorno');

/*
|--------------------------------------------------------------------------
| Autenticación
|--------------------------------------------------------------------------
*/

Route::middleware(['cache.sensible', 'guest'])->group(function () {

    Route::get('/login', [
        LoginController::class,
        'mostrarLogin'
    ])->name('login');

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
| Solo pueden acceder usuarios autenticados,
| con una cuenta activa y un rol asignado.
|
*/

Route::middleware([
    'cache.sensible',
    'auth',
    'usuario.activo'
])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Dashboard
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
    |
    | Disponible en ambos entornos.
    |
    */

    Route::get('/medicamentos', [
        MedicamentoController::class,
        'index'
    ])->name('medicamentos.index');

    /*
    |--------------------------------------------------------------------------
    | Administración de medicamentos
    |--------------------------------------------------------------------------
    |
    | Acceso exclusivo para Administrador y Químico Farmacéutico.
    |
    */

    Route::middleware(
        'rol:Administrador,Quimico Farmaceutico'
    )->group(function () {

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

    });

    /*
    |--------------------------------------------------------------------------
    | Gestión de lotes - RF-07 y RF-08
    |--------------------------------------------------------------------------
    |
    | Disponible en Farmacia Privada y CESFAM.
    |
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
    |
    | Exclusivo de Farmacia Privada.
    | Comprobantes académicos sin validez tributaria.
    | Sin conexión con el SII.
    |
    */

    Route::middleware('entorno:privada')->group(function () {

        // Consultar historial de ventas.

        Route::get('/ventas', [
            VentaController::class,
            'index'
        ])->name('ventas.index');

        // Registrar venta simulada.

        Route::post('/ventas', [
            VentaController::class,
            'store'
        ])->name('ventas.store');

        // Mostrar boleta o factura simulada.

        Route::get('/ventas/{venta}/comprobante', [
            VentaController::class,
            'comprobante'
        ])->name('ventas.comprobante');

        // Visualizar e imprimir comprobante PDF.

        Route::get('/ventas/{venta}/pdf', [
            VentaController::class,
            'verPdf'
        ])->name('ventas.pdf');

        // Descargar comprobante PDF.

        Route::get('/ventas/{venta}/pdf/descargar', [
            VentaController::class,
            'descargarPdf'
        ])->name('ventas.pdf.descargar');

    });

    /*
    |--------------------------------------------------------------------------
    | Pacientes simulados - RF-15
    |--------------------------------------------------------------------------
    |
    | Exclusivo de Farmacia Asistencial (CESFAM).
    |
    */

    Route::middleware('entorno:cesfam')->group(function () {

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

        // Actualizar datos y antecedentes de alergias.

        Route::put('/pacientes/{paciente}', [
            PacienteController::class,
            'update'
        ])->name('pacientes.update');

        // Activar o desactivar pacientes.

        Route::patch('/pacientes/{paciente}/estado', [
            PacienteController::class,
            'cambiarEstado'
        ])->name('pacientes.estado');

    });

    /*
    |--------------------------------------------------------------------------
    | Programas de salud GES/AUGE y pacientes crónicos
    |--------------------------------------------------------------------------
    |
    | Exclusivo de Farmacia Asistencial (CESFAM).
    | Acceso para Administrador y Químico Farmacéutico.
    | Programas y beneficiarios de simulación académica.
    |
    */

    Route::middleware([
        'entorno:cesfam',
        'rol:Administrador,Quimico Farmaceutico'
    ])->group(function () {

        // Consultar programas de salud y beneficiarios.

        Route::get('/programas-salud', [
            ProgramaSaludController::class,
            'index'
        ])->name('programas-salud.index');

        // Registrar programas de salud.

        Route::post('/programas-salud', [
            ProgramaSaludController::class,
            'store'
        ])->name('programas-salud.store');

        // Incorporar pacientes a un programa.

        Route::post('/programas-salud/asignar', [
            ProgramaSaludController::class,
            'asignar'
        ])->name('programas-salud.asignar');

        // Activar o desactivar la participación de un paciente.

        Route::patch(
            '/programas-salud/{programa}/pacientes/{paciente}/estado',
            [
                ProgramaSaludController::class,
                'cambiarEstado'
            ]
        )->name('programas-salud.estado');

        /*
        |--------------------------------------------------------------------------
        | Canastas de medicamentos GES/AUGE y crónicos
        |--------------------------------------------------------------------------
        |
        | Administración de medicamentos, cobertura,
        | cuotas y períodos de entrega.
        |
        */

        // Incorporar medicamentos a una canasta.

        Route::post(
            '/programas-salud/{programa}/medicamentos',
            [
                CanastaMedicamentoController::class,
                'store'
            ]
        )->name('canastas.store');

        // Actualizar cuotas, períodos y cobertura.

        Route::put(
            '/programas-salud/{programa}/medicamentos/{medicamento}',
            [
                CanastaMedicamentoController::class,
                'update'
            ]
        )->name('canastas.update');

        // Activar o desactivar medicamentos de una canasta.

        Route::patch(
            '/programas-salud/{programa}/medicamentos/{medicamento}/estado',
            [
                CanastaMedicamentoController::class,
                'cambiarEstado'
            ]
        )->name('canastas.estado');

    });

    /*
    |--------------------------------------------------------------------------
    | Recetas farmacéuticas - RF-13
    |--------------------------------------------------------------------------
    |
    | Disponible en ambos entornos.
    | Documentos académicos sin validez legal ni clínica.
    |
    */

    // Consultar recetas.

    Route::get('/recetas', [
        RecetaController::class,
        'index'
    ])->name('recetas.index');

    // Registrar recetas simuladas.

    Route::post('/recetas', [
        RecetaController::class,
        'store'
    ])->name('recetas.store');

    // Visualizar e imprimir receta PDF.

    Route::get('/recetas/{receta}/pdf', [
        RecetaController::class,
        'verPdf'
    ])->name('recetas.pdf');

    // Descargar receta PDF.

    Route::get('/recetas/{receta}/pdf/descargar', [
        RecetaController::class,
        'descargarPdf'
    ])->name('recetas.pdf.descargar');

    /*
    |--------------------------------------------------------------------------
    | Dispensaciones asistenciales - RF-16
    |--------------------------------------------------------------------------
    |
    | Exclusivo de Farmacia Asistencial (CESFAM).
    |
    | Roles autorizados:
    | - Administrador.
    | - Químico Farmacéutico.
    | - Técnico en Farmacia.
    |
    */

    Route::middleware('entorno:cesfam')->group(function () {

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
                'rol:Administrador,Quimico Farmaceutico,Tecnico en Farmacia'
            )
            ->name('dispensaciones.store');

        // Visualizar e imprimir comprobante PDF.

        Route::get('/dispensaciones/{dispensacion}/pdf', [
            DispensacionController::class,
            'verPdf'
        ])
            ->middleware(
                'rol:Administrador,Quimico Farmaceutico,Tecnico en Farmacia'
            )
            ->name('dispensaciones.pdf');

        // Descargar comprobante PDF.

        Route::get('/dispensaciones/{dispensacion}/pdf/descargar', [
            DispensacionController::class,
            'descargarPdf'
        ])
            ->middleware(
                'rol:Administrador,Quimico Farmaceutico,Tecnico en Farmacia'
            )
            ->name('dispensaciones.pdf.descargar');

    });

    /*
    |--------------------------------------------------------------------------
    | Administración de usuarios y roles
    |--------------------------------------------------------------------------
    |
    | Disponible en ambos entornos.
    | Acceso exclusivo para el Administrador.
    |
    | Permite:
    | - Consultar usuarios.
    | - Registrar usuarios.
    | - Editar datos y roles.
    | - Actualizar contraseñas.
    | - Activar y desactivar cuentas.
    |
    | No se eliminan usuarios para conservar la trazabilidad.
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

        // Actualizar datos, contraseña y rol.

        Route::put('/usuarios/{usuario}', [
            UsuarioController::class,
            'update'
        ])->name('usuarios.update');

        // Activar o desactivar una cuenta.

        Route::patch('/usuarios/{usuario}/estado', [
            UsuarioController::class,
            'cambiarEstado'
        ])->name('usuarios.estado');

    });

});

/*
|--------------------------------------------------------------------------
| Cierre de sesión
|--------------------------------------------------------------------------
|
| Disponible para cualquier usuario autenticado,
| incluso si su cuenta fue desactivada.
|
*/

Route::middleware(['cache.sensible', 'auth'])->group(function () {

    Route::post('/logout', [
        LoginController::class,
        'cerrarSesion'
    ])->name('logout');

});