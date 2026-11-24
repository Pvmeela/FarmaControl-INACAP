<?php

namespace App\Http\Controllers;

use App\Models\Rol;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class UsuarioController extends Controller
{
    /**
     * Roles autorizados en FarmaControl.
     */
    private const ROLES_PERMITIDOS = [
        'Administrador',
        'Quimico Farmaceutico',
        'Tecnico en Farmacia',
    ];

    /**
     * Mostrar los usuarios registrados y los roles disponibles.
     */
    public function index(): Response
    {
        $usuarios = User::with('rol')
            ->orderByDesc('activo')
            ->orderBy('name')
            ->get()
            ->map(function ($usuario) {
                return [
                    'id' => $usuario->id,
                    'name' => $usuario->name,
                    'email' => $usuario->email,
                    'rol_id' => $usuario->rol_id,
                    'rol' => $usuario->rol?->nombre
                        ?? 'Sin rol asignado',
                    'activo' => $usuario->activo,
                    'created_at' => $usuario->created_at
                        ?->format('d/m/Y'),
                ];
            });

        $roles = Rol::whereIn(
                'nombre',
                self::ROLES_PERMITIDOS
            )
            ->orderBy('id')
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
                Rule::exists('roles', 'id')
                    ->whereIn(
                        'nombre',
                        self::ROLES_PERMITIDOS
                    ),
            ],
        ]);

        // Los usuarios nuevos se crean activos.
        $datos['activo'] = true;

        User::create($datos);

        return redirect()
            ->route('usuarios.index')
            ->with(
                'success',
                'Usuario registrado correctamente.'
            );
    }

    /**
     * Actualizar los datos de un usuario.
     */
    public function update(
        Request $request,
        User $usuario
    ): RedirectResponse
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
                Rule::unique('users', 'email')
                    ->ignore($usuario->id),
            ],
            'password' => [
                'nullable',
                'confirmed',
                Password::min(8),
            ],
            'rol_id' => [
                'required',
                'integer',
                Rule::exists('roles', 'id')
                    ->whereIn(
                        'nombre',
                        self::ROLES_PERMITIDOS
                    ),
            ],
        ]);

        // Impedir que el administrador cambie su propio rol.
        if (
            (int) $request->user()->id === (int) $usuario->id
            && (int) $datos['rol_id'] !== (int) $usuario->rol_id
        ) {
            throw ValidationException::withMessages([
                'rol_id' => 'No puedes modificar tu propio rol.',
            ]);
        }

        // Mantener la contraseña actual si no se ingresa otra.
        if (empty($datos['password'])) {
            unset($datos['password']);
        } else {
            // Invalidar el token de recordatorio anterior.
            $usuario->remember_token = null;
        }

        $usuario->fill($datos);
        $usuario->save();

        return redirect()
            ->route('usuarios.index')
            ->with(
                'success',
                'Usuario actualizado correctamente.'
            );
    }

    /**
     * Activar o desactivar una cuenta.
     */
    public function cambiarEstado(
        Request $request,
        User $usuario
    ): RedirectResponse
    {
        $datos = $request->validate([
            'activo' => [
                'required',
                'boolean',
            ],
        ]);

        $nuevoEstado = (bool) $datos['activo'];

        // Impedir que un administrador desactive su propia cuenta.
        if (
            (int) $request->user()->id === (int) $usuario->id
            && !$nuevoEstado
        ) {
            throw ValidationException::withMessages([
                'activo' => 'No puedes desactivar tu propia cuenta.',
            ]);
        }

        // Para reactivar una cuenta debe tener un rol autorizado.
        if ($nuevoEstado) {
            $rolValido = Rol::where('id', $usuario->rol_id)
                ->whereIn(
                    'nombre',
                    self::ROLES_PERMITIDOS
                )
                ->exists();

            if (!$rolValido) {
                throw ValidationException::withMessages([
                    'activo' => 'Debes asignar un rol válido antes de activar esta cuenta.',
                ]);
            }
        }

        $usuario->activo = $nuevoEstado;

        // Invalidar el token de recordatorio al desactivar.
        if (!$nuevoEstado) {
            $usuario->remember_token = null;
        }

        $usuario->save();

        return redirect()
            ->route('usuarios.index')
            ->with(
                'success',
                $nuevoEstado
                    ? 'Usuario activado correctamente.'
                    : 'Usuario desactivado correctamente.'
            );
    }
}