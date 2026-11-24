<?php

namespace Tests\Feature;

use App\Models\Rol;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SeguridadUsuariosTest extends TestCase
{
    use RefreshDatabase;

    private function crearRol(string $nombre): Rol
    {
        return Rol::firstOrCreate(
            ['nombre' => $nombre],
            ['descripcion' => 'Rol de prueba']
        );
    }

    private function crearUsuario(
        string $nombreRol = 'Administrador',
        bool $activo = true
    ): User {
        $rol = $this->crearRol($nombreRol);

        return User::factory()->create([
            'rol_id' => $rol->id,
            'activo' => $activo,
        ]);
    }

    private function datosUsuario(int $rolId): array
    {
        return [
            'name' => 'Usuario de prueba',
            'email' => 'nuevo@farmacontrol.test',
            'password' => 'PruebaSegura123',
            'password_confirmation' => 'PruebaSegura123',
            'rol_id' => $rolId,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Operaciones autorizadas del administrador
    |--------------------------------------------------------------------------
    */

    public function test_administrador_puede_consultar_usuarios(): void
    {
        $this->actingAs($this->crearUsuario())
            ->withSession(['entorno' => 'privada'])
            ->get('/usuarios')
            ->assertOk();
    }

    public function test_administrador_puede_crear_usuarios(): void
    {
        $administrador = $this->crearUsuario();
        $rol = $this->crearRol('Tecnico en Farmacia');

        $datos = $this->datosUsuario($rol->id);

        $this->actingAs($administrador)
            ->withSession(['entorno' => 'privada'])
            ->post('/usuarios', $datos)
            ->assertRedirect('/usuarios')
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('users', [
            'name' => $datos['name'],
            'email' => $datos['email'],
            'rol_id' => $rol->id,
            'activo' => true,
        ]);

        $usuario = User::where('email', $datos['email'])
            ->firstOrFail();

        $this->assertTrue(
            Hash::check($datos['password'], $usuario->password)
        );

        $this->assertNotSame(
            $datos['password'],
            $usuario->password
        );
    }

    public function test_administrador_puede_editar_usuarios(): void
    {
        $administrador = $this->crearUsuario();
        $usuario = $this->crearUsuario('Tecnico en Farmacia');
        $rol = $this->crearRol('Quimico Farmaceutico');

        $this->actingAs($administrador)
            ->withSession(['entorno' => 'privada'])
            ->put("/usuarios/{$usuario->id}", [
                'name' => 'Usuario actualizado',
                'email' => 'actualizado@farmacontrol.test',
                'rol_id' => $rol->id,
            ])
            ->assertRedirect('/usuarios')
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('users', [
            'id' => $usuario->id,
            'name' => 'Usuario actualizado',
            'email' => 'actualizado@farmacontrol.test',
            'rol_id' => $rol->id,
        ]);
    }

    public function test_administrador_puede_desactivar_usuarios(): void
    {
        $administrador = $this->crearUsuario();
        $usuario = $this->crearUsuario('Tecnico en Farmacia');

        $this->actingAs($administrador)
            ->withSession(['entorno' => 'privada'])
            ->patch("/usuarios/{$usuario->id}/estado", [
                'activo' => false,
            ])
            ->assertRedirect('/usuarios')
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('users', [
            'id' => $usuario->id,
            'activo' => false,
        ]);
    }

    public function test_administrador_puede_reactivar_usuarios(): void
    {
        $administrador = $this->crearUsuario();

        $usuario = $this->crearUsuario(
            'Tecnico en Farmacia',
            false
        );

        $this->actingAs($administrador)
            ->withSession(['entorno' => 'privada'])
            ->patch("/usuarios/{$usuario->id}/estado", [
                'activo' => true,
            ])
            ->assertRedirect('/usuarios')
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('users', [
            'id' => $usuario->id,
            'activo' => true,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Proteccion de la cuenta del administrador
    |--------------------------------------------------------------------------
    */

    public function test_administrador_no_puede_desactivarse(): void
    {
        $administrador = $this->crearUsuario();

        $this->actingAs($administrador)
            ->withSession(['entorno' => 'privada'])
            ->from('/usuarios')
            ->patch("/usuarios/{$administrador->id}/estado", [
                'activo' => false,
            ])
            ->assertRedirect('/usuarios')
            ->assertSessionHasErrors('activo');

        $this->assertTrue($administrador->fresh()->activo);
    }

    public function test_administrador_no_puede_cambiar_su_propio_rol(): void
    {
        $administrador = $this->crearUsuario();
        $rol = $this->crearRol('Tecnico en Farmacia');

        $rolOriginal = $administrador->rol_id;

        $this->actingAs($administrador)
            ->withSession(['entorno' => 'privada'])
            ->from('/usuarios')
            ->put("/usuarios/{$administrador->id}", [
                'name' => $administrador->name,
                'email' => $administrador->email,
                'rol_id' => $rol->id,
            ])
            ->assertRedirect('/usuarios')
            ->assertSessionHasErrors('rol_id');

        $this->assertEquals(
            $rolOriginal,
            $administrador->fresh()->rol_id
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Restricciones por rol
    |--------------------------------------------------------------------------
    */

    public function test_quimico_no_puede_consultar_usuarios(): void
    {
        $this->actingAs(
            $this->crearUsuario('Quimico Farmaceutico')
        )
            ->withSession(['entorno' => 'cesfam'])
            ->get('/usuarios')
            ->assertForbidden();
    }

    public function test_quimico_no_puede_crear_usuarios(): void
    {
        $quimico = $this->crearUsuario('Quimico Farmaceutico');
        $rol = $this->crearRol('Tecnico en Farmacia');

        $this->actingAs($quimico)
            ->withSession(['entorno' => 'cesfam'])
            ->post('/usuarios', $this->datosUsuario($rol->id))
            ->assertForbidden();

        $this->assertDatabaseCount('users', 1);
    }

    public function test_tecnico_no_puede_consultar_usuarios(): void
    {
        $this->actingAs(
            $this->crearUsuario('Tecnico en Farmacia')
        )
            ->withSession(['entorno' => 'privada'])
            ->get('/usuarios')
            ->assertForbidden();
    }

    public function test_tecnico_no_puede_crear_usuarios(): void
    {
        $tecnico = $this->crearUsuario('Tecnico en Farmacia');
        $rol = $this->crearRol('Administrador');

        $this->actingAs($tecnico)
            ->withSession(['entorno' => 'privada'])
            ->post('/usuarios', $this->datosUsuario($rol->id))
            ->assertForbidden();

        $this->assertDatabaseCount('users', 1);
    }

    public function test_quimico_no_puede_editar_usuarios(): void
    {
        $quimico = $this->crearUsuario('Quimico Farmaceutico');
        $usuario = $this->crearUsuario('Tecnico en Farmacia');

        $this->actingAs($quimico)
            ->withSession(['entorno' => 'cesfam'])
            ->put("/usuarios/{$usuario->id}", [
                'name' => 'Cambio no autorizado',
                'email' => $usuario->email,
                'rol_id' => $usuario->rol_id,
            ])
            ->assertForbidden();

        $this->assertEquals(
            $usuario->name,
            $usuario->fresh()->name
        );
    }

    public function test_tecnico_no_puede_desactivar_usuarios(): void
    {
        $tecnico = $this->crearUsuario('Tecnico en Farmacia');
        $administrador = $this->crearUsuario();

        $this->actingAs($tecnico)
            ->withSession(['entorno' => 'privada'])
            ->patch("/usuarios/{$administrador->id}/estado", [
                'activo' => false,
            ])
            ->assertForbidden();

        $this->assertTrue($administrador->fresh()->activo);
    }

    /*
    |--------------------------------------------------------------------------
    | Usuarios no autenticados
    |--------------------------------------------------------------------------
    */

    public function test_invitado_no_puede_consultar_usuarios(): void
    {
        $this->get('/usuarios')
            ->assertRedirect(route('login'));
    }

    public function test_invitado_no_puede_crear_usuarios(): void
    {
        $rol = $this->crearRol('Administrador');

        $this->post('/usuarios', $this->datosUsuario($rol->id))
            ->assertRedirect(route('login'));

        $this->assertDatabaseCount('users', 0);
    }

    /*
    |--------------------------------------------------------------------------
    | Validaciones de datos
    |--------------------------------------------------------------------------
    */

    public function test_no_permite_crear_usuario_con_rol_no_autorizado(): void
    {
        $administrador = $this->crearUsuario();
        $rol = $this->crearRol('Rol No Autorizado');

        $this->actingAs($administrador)
            ->withSession(['entorno' => 'privada'])
            ->from('/usuarios')
            ->post('/usuarios', $this->datosUsuario($rol->id))
            ->assertRedirect('/usuarios')
            ->assertSessionHasErrors('rol_id');

        $this->assertDatabaseCount('users', 1);
    }

    public function test_no_permite_crear_usuario_con_correo_duplicado(): void
    {
        $administrador = $this->crearUsuario();
        $rol = $this->crearRol('Tecnico en Farmacia');

        $datos = $this->datosUsuario($rol->id);
        $datos['email'] = $administrador->email;

        $this->actingAs($administrador)
            ->withSession(['entorno' => 'privada'])
            ->from('/usuarios')
            ->post('/usuarios', $datos)
            ->assertRedirect('/usuarios')
            ->assertSessionHasErrors('email');

        $this->assertDatabaseCount('users', 1);
    }

    public function test_no_permite_reactivar_usuario_sin_rol(): void
    {
        $administrador = $this->crearUsuario();

        $usuario = User::factory()->create([
            'rol_id' => null,
            'activo' => false,
        ]);

        $this->actingAs($administrador)
            ->withSession(['entorno' => 'privada'])
            ->from('/usuarios')
            ->patch("/usuarios/{$usuario->id}/estado", [
                'activo' => true,
            ])
            ->assertRedirect('/usuarios')
            ->assertSessionHasErrors('activo');

        $this->assertFalse($usuario->fresh()->activo);
    }

    public function test_edicion_sin_contrasena_conserva_la_actual(): void
    {
        $administrador = $this->crearUsuario();
        $usuario = $this->crearUsuario('Tecnico en Farmacia');

        $passwordOriginal = $usuario->password;

        $this->actingAs($administrador)
            ->withSession(['entorno' => 'privada'])
            ->put("/usuarios/{$usuario->id}", [
                'name' => 'Nombre modificado',
                'email' => $usuario->email,
                'rol_id' => $usuario->rol_id,
                'password' => null,
            ])
            ->assertRedirect('/usuarios')
            ->assertSessionHasNoErrors();

        $this->assertSame(
            $passwordOriginal,
            $usuario->fresh()->password
        );

        $this->assertEquals(
            'Nombre modificado',
            $usuario->fresh()->name
        );
    }

    public function test_no_permite_contrasena_menor_a_ocho_caracteres(): void
    {
        $administrador = $this->crearUsuario();
        $rol = $this->crearRol('Tecnico en Farmacia');

        $datos = $this->datosUsuario($rol->id);
        $datos['password'] = '1234567';
        $datos['password_confirmation'] = '1234567';

        $this->actingAs($administrador)
            ->withSession(['entorno' => 'privada'])
            ->from('/usuarios')
            ->post('/usuarios', $datos)
            ->assertRedirect('/usuarios')
            ->assertSessionHasErrors('password');

        $this->assertDatabaseCount('users', 1);
    }

    public function test_no_permite_contrasenas_que_no_coinciden(): void
    {
        $administrador = $this->crearUsuario();
        $rol = $this->crearRol('Tecnico en Farmacia');

        $datos = $this->datosUsuario($rol->id);
        $datos['password_confirmation'] = 'OtraContrasena123';

        $this->actingAs($administrador)
            ->withSession(['entorno' => 'privada'])
            ->from('/usuarios')
            ->post('/usuarios', $datos)
            ->assertRedirect('/usuarios')
            ->assertSessionHasErrors('password');

        $this->assertDatabaseCount('users', 1);
    }
}
