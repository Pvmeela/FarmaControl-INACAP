<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerificarEntorno
{
    /**
     * Verificar que el usuario esté trabajando
     * en el entorno permitido para el módulo.
     *
     * Entornos disponibles:
     * - privada: Farmacia Privada
     * - cesfam: Farmacia Asistencial
     */
    public function handle(
        Request $request,
        Closure $next,
        string $entornoPermitido
    ): Response {

        // Obtener el entorno seleccionado en la sesión.
        $entornoActual = $request->session()->get('entorno');

        // Verificar que exista un entorno válido.
        if (!in_array($entornoActual, ['privada', 'cesfam'], true)) {
            abort(403, 'Debes seleccionar un entorno de trabajo válido.');
        }

        // Impedir acceso a módulos de otro entorno.
        if ($entornoActual !== $entornoPermitido) {
            abort(403, 'Este módulo no está disponible en el entorno seleccionado.');
        }

        return $next($request);
    }
}
