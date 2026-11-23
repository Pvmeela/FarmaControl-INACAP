<?php

namespace App\Http\Controllers;

use App\Models\Rol;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class UsuarioController extends Controller
{
    /**
     * Mostrar los usuarios registrados y los roles.
     */
    public function index(): Response
    {
        $usuarios = User::with('rol')
            ->orderBy('name')
            ->get()
            ->map(function ($usuario) {
                return [
                    'id' => $usuario->id,
                    'name' => $usuario->name,
                    'email' => $usuario->email,
                    'rol' => $usuario->rol?->nombre
                        ?? 'Sin rol asignado',
                    'created_at' => $usuario->created_at
                        ?->format('d/m/Y'),
                ];
            });

        $roles = Rol::orderBy('id')
            ->get(['id', 'nombre']);

        return Inertia::render('Usuarios/Index', [
            'usuarios' => $usuarios,
            'roles' => $roles,
        ]);
    }

    /**
     * Registrar un nuevo usuario.
     */
    public function store(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'email' => [
                'required',
                'email',
                'max:255',
                'unique:users,email',
            ],
            'password' => [
                'required',
                'confirmed',
                Password::min(8),
            ],
            'rol_id' => [
                'required',
                'integer',
                'exists:roles,id',
            ],
        ]);

        User::create($datos);

        return redirect()
            ->route('usuarios.index')
            ->with(
                'success',
                'Usuario registrado correctamente.'
            );
    }
}
