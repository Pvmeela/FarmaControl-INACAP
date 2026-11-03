
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use Inertia\Inertia;

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

    Route::get('/login', [
        LoginController::class,
        'mostrarLogin'
    ])->name('login');

    Route::post('/login', [
        LoginController::class,
        'iniciarSesion'
    ])->middleware('throttle:5,1')->name('login.attempt');

});

/*
|--------------------------------------------------------------------------
| Rutas protegidas
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    Route::post('/logout', [
        LoginController::class,
        'cerrarSesion'
    ])->name('logout');

});
