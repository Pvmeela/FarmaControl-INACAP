<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

use App\Http\Middleware\VerificarRol;
use App\Http\Middleware\VerificarEntorno;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\VerificarUsuarioActivo;
use App\Http\Middleware\PrevenirCacheSensible;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {

        // Mantener el funcionamiento de Inertia.js.
        $middleware->web(append: [
            HandleInertiaRequests::class,
        ]);

        // Registrar middleware de seguridad.
        $middleware->alias([
            'rol' => VerificarRol::class,
            'entorno' => VerificarEntorno::class,
            'usuario.activo' => VerificarUsuarioActivo::class,
            'cache.sensible' => PrevenirCacheSensible::class,
        ]);

    })
    ->withExceptions(function (Exceptions $exceptions): void {

        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) =>
                $request->is('api/*') ||
                $request->expectsJson(),
        );

    })
    ->create();
