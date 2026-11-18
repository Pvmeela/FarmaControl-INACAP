<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerificarRol
{
    /**
     * Verifica que el usuario tenga uno
     * de los roles autorizados.
     */
    public function handle(
        Request $request,
        Closure $next,
        string ...$rolesPermitidos
    ): Response
    {
        // Obtener el usuario autenticado
        $usuario = $request->user();

        // Comprobar que haya iniciado sesión
        if (!$usuario) {
            abort(401, 'Debes iniciar sesión.');
        }

        // Obtener el nombre de su rol
        $nombreRol = $usuario->rol?->nombre;

        // Verificar que el rol esté autorizado
        if (
            !$nombreRol ||
            !in_array($nombreRol, $rolesPermitidos, true)
        ) {
            abort(
                403,
                'No tienes permisos para realizar esta operación.'
            );
        }

        // Permitir continuar con la solicitud
        return $next($request);
    }
}
