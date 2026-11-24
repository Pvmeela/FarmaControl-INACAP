<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PrevenirCacheSensible
{
    /**
     * Evitar el almacenamiento en caché HTTP
     * de respuestas con información sensible.
     */
    public function handle(
        Request $request,
        Closure $next
    ): Response {
        $respuesta = $next($request);

        $respuesta->headers->set(
            'Cache-Control',
            'no-store, no-cache, must-revalidate, private, max-age=0'
        );

        $respuesta->headers->set(
            'Pragma',
            'no-cache'
        );

        $respuesta->headers->set(
            'Expires',
            'Thu, 01 Jan 1970 00:00:00 GMT'
        );

        return $respuesta;
    }
}
