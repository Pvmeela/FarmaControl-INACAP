<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

class VerificarUsuarioActivo
{
    private const ROLES_PERMITIDOS = [
        'Administrador',
        'Quimico Farmaceutico',
        'Tecnico en Farmacia',
    ];

    /**
     * Verificar que el usuario esté activo
     * y tenga un rol autorizado.
     */
    public function handle(
        Request $request,
        Closure $next
    ): Response {
        $usuario = $request->user();

        if (
            $usuario &&
            (
                !$usuario->activo ||
                !$usuario->rol ||
                !in_array(
                    $usuario->rol->nombre,
                    self::ROLES_PERMITIDOS,
                    true
                )
            )
        ) {
            Auth::logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            // Invalidar el historial de navegación anterior.
            Inertia::clearHistory();

            return redirect()
                ->route('login')
                ->withErrors([
                    'email' => 'Tu cuenta no está habilitada para acceder al sistema.',
                ]);
        }

        return $next($request);
    }
}
