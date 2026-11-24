<?php

namespace Tests\Feature;

use App\Models\Rol;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class SeguridadAutenticacionTest extends TestCase
{
    use RefreshDatabase;

    private const PASSWORD = 'PruebaSegura123';

    private function crearUsuario(
        ?string $nombreRol = 'Administrador',
        bool $activo = true
    ): User {
        $rol = null;

        if ($nombreRol !== null) {
            $rol = Rol::firstOrCreate(
                ['nombre' => $nombreRol],
                ['descripcion' => 'Rol de prueba']
            );
        }

        return User::factory()->create([
            'rol_id' => $rol?->id,
            'activo' => $activo,
            'password' => Hash::make(self::PASSWORD),
        ]);
    }

    private function iniciarSesion(User $usuario)
    {
        return $this->from('/login')
            ->withSession(['entorno' => 'privada'])
            ->post('/login', [
                'email' => $usuario->email,
                'password' => self::PASSWORD,
            ]);
    }

    private function verificarAccesoDenegado(
        User $usuario
    ): void {
        $this->iniciarSesion($usuario)
            ->assertRedirect('/login')
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    /*
    |--------------------------------------------------------------------------
    | Inicio de sesion
    |--------------------------------------------------------------------------
    */

    public function test_administrador_puede_iniciar_sesion(): void
    {
        $usuario = $this->crearUsuario('Administrador');

        $this->iniciarSesion($usuario)
            ->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($usuario);
    }

    public function test_quimico_puede_iniciar_sesion(): void
    {
        $usuario = $this->crearUsuario(
            'Quimico Farmaceutico'
        );

        $this->iniciarSesion($usuario)
            ->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($usuario);
    }

    public function test_tecnico_puede_iniciar_sesion(): void
    {
        $usuario = $this->crearUsuario(
            'Tecnico en Farmacia'
        );

        $this->iniciarSesion($usuario)
            ->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($usuario);
    }

    public function test_no_permite_contrasena_incorrecta(): void
    {
        $usuario = $this->crearUsuario();

        $this->from('/login')
            ->post('/login', [
                'email' => $usuario->email,
                'password' => 'PasswordIncorrecta123',
            ])
            ->assertRedirect('/login')
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_no_permite_correo_inexistente(): void
    {
        $this->from('/login')
            ->post('/login', [
                'email' => 'inexistente@farmacontrol.test',
                'password' => self::PASSWORD,
            ])
            ->assertRedirect('/login')
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_login_requiere_campos_obligatorios(): void
    {
        $this->from('/login')
            ->post('/login', [])
            ->assertRedirect('/login')
            ->assertSessionHasErrors([
                'email',
                'password',
            ]);

        $this->assertGuest();
    }

    /*
    |--------------------------------------------------------------------------
    | Control de acceso
    |--------------------------------------------------------------------------
    */

    public function test_usuario_inactivo_no_puede_iniciar_sesion(): void
    {
        $usuario = $this->crearUsuario(
            'Administrador',
            false
        );

        $this->verificarAccesoDenegado($usuario);
    }

    public function test_usuario_sin_rol_no_puede_iniciar_sesion(): void
    {
        $usuario = $this->crearUsuario(null);

        $this->verificarAccesoDenegado($usuario);
    }

    public function test_docente_no_puede_iniciar_sesion(): void
    {
        $usuario = $this->crearUsuario('Docente');

        $this->verificarAccesoDenegado($usuario);
    }

    public function test_estudiante_no_puede_iniciar_sesion(): void
    {
        $usuario = $this->crearUsuario('Estudiante');

        $this->verificarAccesoDenegado($usuario);
    }

    public function test_rol_desconocido_no_puede_iniciar_sesion(): void
    {
        $usuario = $this->crearUsuario(
            'Rol No Autorizado'
        );

        $this->verificarAccesoDenegado($usuario);
    }

    public function test_usuario_desactivado_pierde_su_sesion(): void
    {
        $usuario = $this->crearUsuario();

        $this->iniciarSesion($usuario)
            ->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($usuario);

        User::whereKey($usuario->id)->update([
            'activo' => false,
        ]);

        // Simular una nueva peticion que recarga al usuario.
        Auth::forgetGuards();

        $this->get('/dashboard')
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    /*
    |--------------------------------------------------------------------------
    | Seguridad de sesiones
    |--------------------------------------------------------------------------
    */

    public function test_login_regenera_identificador_de_sesion(): void
    {
        $usuario = $this->crearUsuario();

        $this->withSession([
            'entorno' => 'privada',
        ]);

        $idAnterior = session()->getId();

        $this->iniciarSesion($usuario)
            ->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($usuario);

        $this->assertNotSame(
            $idAnterior,
            session()->getId()
        );
    }

    public function test_usuario_puede_cerrar_sesion(): void
    {
        $usuario = $this->crearUsuario();

        $this->actingAs($usuario)
            ->withSession([
                'entorno' => 'privada',
                'dato_de_prueba' => 'informacion temporal',
            ])
            ->post('/logout')
            ->assertRedirect(route('login'))
            ->assertSessionMissing('dato_de_prueba')
            ->assertSessionMissing('entorno');

        $this->assertGuest();
    }

    public function test_dashboard_no_es_accesible_despues_del_logout(): void
    {
        $usuario = $this->crearUsuario();

        $this->actingAs($usuario)
            ->withSession(['entorno' => 'privada'])
            ->post('/logout')
            ->assertRedirect(route('login'));

        $this->assertGuest();

        $this->get('/dashboard')
            ->assertRedirect(route('login'));
    }

    public function test_logout_regenera_token_csrf(): void
    {
        $usuario = $this->crearUsuario();

        $this->actingAs($usuario)
            ->withSession([
                'entorno' => 'privada',
                '_token' => 'token-anterior-de-prueba',
            ]);

        $tokenAnterior = session()->token();

        $this->post('/logout')
            ->assertRedirect(route('login'));

        $this->assertGuest();

        $this->assertNotEmpty(session()->token());

        $this->assertNotSame(
            $tokenAnterior,
            session()->token()
        );
    }

    public function test_login_limita_intentos_fallidos(): void
    {
        $usuario = $this->crearUsuario();

        for ($intento = 1; $intento <= 5; $intento++) {
            $this->from('/login')
                ->post('/login', [
                    'email' => $usuario->email,
                    'password' => 'PasswordIncorrecta123',
                ])
                ->assertRedirect('/login')
                ->assertSessionHasErrors('email');
        }

        $this->post('/login', [
            'email' => $usuario->email,
            'password' => self::PASSWORD,
        ])->assertStatus(429);

        $this->assertGuest();
    }
}
