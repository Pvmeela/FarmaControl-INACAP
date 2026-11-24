<?php

namespace Tests\Feature;

use App\Models\Rol;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PermisosDispensacionTest extends TestCase
{
    use RefreshDatabase;

    private function crearUsuarioConRol(
        string $nombreRol,
        bool $activo = true
    ): User {
        $rol = Rol::firstOrCreate(
            ['nombre' => $nombreRol],
            ['descripcion' => 'Rol de prueba']
        );

        return User::factory()->create([
            'rol_id' => $rol->id,
            'activo' => $activo,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Permisos del Administrador
    |--------------------------------------------------------------------------
    */

    public function test_administrador_puede_consultar_dispensaciones(): void
    {
        $usuario = $this->crearUsuarioConRol('Administrador');

        $this->actingAs($usuario)
            ->withSession(['entorno' => 'cesfam'])
            ->get('/dispensaciones')
            ->assertOk();
    }

    public function test_administrador_puede_intentar_registrar_dispensaciones(): void
    {
        $usuario = $this->crearUsuarioConRol('Administrador');

        $this->actingAs($usuario)
            ->withSession(['entorno' => 'cesfam'])
            ->from('/dispensaciones')
            ->post('/dispensaciones', [])
            ->assertRedirect('/dispensaciones')
            ->assertSessionHasErrors([
                'paciente_id',
                'receta_id',
                'medicamento_id',
                'cantidad',
            ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Permisos del Químico Farmacéutico
    |--------------------------------------------------------------------------
    */

    public function test_quimico_puede_consultar_dispensaciones(): void
    {
        $usuario = $this->crearUsuarioConRol(
            'Quimico Farmaceutico'
        );

        $this->actingAs($usuario)
            ->withSession(['entorno' => 'cesfam'])
            ->get('/dispensaciones')
            ->assertOk();
    }

    public function test_quimico_puede_intentar_registrar_dispensaciones(): void
    {
        $usuario = $this->crearUsuarioConRol(
            'Quimico Farmaceutico'
        );

        $this->actingAs($usuario)
            ->withSession(['entorno' => 'cesfam'])
            ->from('/dispensaciones')
            ->post('/dispensaciones', [])
            ->assertRedirect('/dispensaciones')
            ->assertSessionHasErrors([
                'paciente_id',
                'receta_id',
                'medicamento_id',
                'cantidad',
            ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Permisos del Técnico en Farmacia
    |--------------------------------------------------------------------------
    */

    public function test_tecnico_puede_consultar_dispensaciones(): void
    {
        $usuario = $this->crearUsuarioConRol(
            'Tecnico en Farmacia'
        );

        $this->actingAs($usuario)
            ->withSession(['entorno' => 'cesfam'])
            ->get('/dispensaciones')
            ->assertOk();
    }

    public function test_tecnico_puede_intentar_registrar_dispensaciones(): void
    {
        $usuario = $this->crearUsuarioConRol(
            'Tecnico en Farmacia'
        );

        $this->actingAs($usuario)
            ->withSession(['entorno' => 'cesfam'])
            ->from('/dispensaciones')
            ->post('/dispensaciones', [])
            ->assertRedirect('/dispensaciones')
            ->assertSessionHasErrors([
                'paciente_id',
                'receta_id',
                'medicamento_id',
                'cantidad',
            ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Seguridad de usuarios inactivos
    |--------------------------------------------------------------------------
    */

    public function test_usuario_inactivo_no_puede_consultar_dispensaciones(): void
    {
        $usuario = $this->crearUsuarioConRol(
            'Administrador',
            false
        );

        $this->actingAs($usuario)
            ->withSession(['entorno' => 'cesfam'])
            ->get('/dispensaciones')
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_usuario_inactivo_no_puede_registrar_dispensaciones(): void
    {
        $usuario = $this->crearUsuarioConRol(
            'Tecnico en Farmacia',
            false
        );

        $this->actingAs($usuario)
            ->withSession(['entorno' => 'cesfam'])
            ->post('/dispensaciones', [])
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    /*
    |--------------------------------------------------------------------------
    | Seguridad de usuarios sin rol
    |--------------------------------------------------------------------------
    */

    public function test_usuario_sin_rol_no_puede_consultar_dispensaciones(): void
    {
        $usuario = User::factory()->create([
            'rol_id' => null,
            'activo' => true,
        ]);

        $this->actingAs($usuario)
            ->withSession(['entorno' => 'cesfam'])
            ->get('/dispensaciones')
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_usuario_sin_rol_no_puede_registrar_dispensaciones(): void
    {
        $usuario = User::factory()->create([
            'rol_id' => null,
            'activo' => true,
        ]);

        $this->actingAs($usuario)
            ->withSession(['entorno' => 'cesfam'])
            ->post('/dispensaciones', [])
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    /*
    |--------------------------------------------------------------------------
    | Seguridad de roles no autorizados
    |--------------------------------------------------------------------------
    */

    public function test_rol_no_autorizado_no_puede_consultar_dispensaciones(): void
    {
        $usuario = $this->crearUsuarioConRol(
            'Rol No Autorizado'
        );

        $this->actingAs($usuario)
            ->withSession(['entorno' => 'cesfam'])
            ->get('/dispensaciones')
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_rol_no_autorizado_no_puede_registrar_dispensaciones(): void
    {
        $usuario = $this->crearUsuarioConRol(
            'Rol No Autorizado'
        );

        $this->actingAs($usuario)
            ->withSession(['entorno' => 'cesfam'])
            ->post('/dispensaciones', [])
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    /*
    |--------------------------------------------------------------------------
    | Seguridad de usuarios no autenticados
    |--------------------------------------------------------------------------
    */

    public function test_invitado_no_puede_consultar_dispensaciones(): void
    {
        $this->withSession(['entorno' => 'cesfam'])
            ->get('/dispensaciones')
            ->assertRedirect(route('login'));
    }

    public function test_invitado_no_puede_registrar_dispensaciones(): void
    {
        $this->withSession(['entorno' => 'cesfam'])
            ->post('/dispensaciones', [])
            ->assertRedirect(route('login'));
    }
}