<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class LoginController extends Controller
{
    private const ROLES_PERMITIDOS = [
        'Administrador',
        'Quimico Farmaceutico',
        'Tecnico en Farmacia',
    ];

    /**
     * Mostrar el formulario de inicio de sesión.
     */
    public function mostrarLogin(Request $request): Response
    {
        $entorno = $request->session()->get(
            'entorno',
            'privada'
        );

        if (!in_array($entorno, ['privada', 'cesfam'], true)) {
            $entorno = 'privada';
        }

        return Inertia::render('Auth/Login', [
            'entorno' => $entorno,
        ]);
    }

    /**
     * Validar credenciales e iniciar sesión.
     */
    public function iniciarSesion(Request $request)
    {
        $credenciales = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $credenciales['activo'] = true;

        if (!Auth::attempt($credenciales)) {
            throw ValidationException::withMessages([
                'email' => 'Las credenciales son incorrectas o la cuenta no está habilitada.',
            ]);
        }

        $usuario = Auth::user();

        if (
            !$usuario->rol ||
            !in_array(
                $usuario->rol->nombre,
                self::ROLES_PERMITIDOS,
                true
            )
        ) {
            Auth::logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            Inertia::clearHistory();

            throw ValidationException::withMessages([
                'email' => 'Las credenciales son incorrectas o la cuenta no está habilitada.',
            ]);
        }

        $request->session()->regenerate();

        // Invalidar el historial de navegación anterior.
        Inertia::clearHistory();

        return redirect()->route('dashboard');
    }

    /**
     * Cerrar sesión de forma segura.
     */
    public function cerrarSesion(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Proteger la información de la sesión anterior.
        Inertia::clearHistory();

        return redirect()->route('login');
    }
}
