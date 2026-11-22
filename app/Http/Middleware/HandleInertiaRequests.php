<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * Plantilla principal de Inertia.
     */
    protected $rootView = 'app';

    /**
     * Versión de los recursos.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Compartir información con las páginas Vue.
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),

            'auth' => [
                'user' => $request->user()
                    ? [
                        'id' => $request->user()->id,
                        'name' => $request->user()->name,
                        'email' => $request->user()->email,
                        'rol' => $request->user()->rol?->nombre,
                    ]
                    : null,
            ],
        ];
    }
}
