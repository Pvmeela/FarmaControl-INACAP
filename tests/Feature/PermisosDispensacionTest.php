<?php

namespace Tests\Feature;

use App\Models\Rol;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PermisosDispensacionTest extends TestCase
{
    use RefreshDatabase;

    private function crearUsuarioConRol(string $nombreRol): User
    {
        $rol = Rol::firstOrCreate(
            ['nombre' => $nombreRol],
            ['descripcion' => 'Rol de prueba']
        );

        return User::factory()->create([
            'rol_id' => $rol->id,
        ]);
    }

    public function test_estudiante_no_puede_consultar_dispensaciones(): void
    {
        $usuario = $this->crearUsuarioConRol('Estudiante');

        $this->actingAs($usuario)
            ->get('/dispensaciones')
            ->assertForbidden();
    }

    public function test_estudiante_no_puede_registrar_dispensaciones(): void
    {
        $usuario = $this->crearUsuarioConRol('Estudiante');

        $this->actingAs($usuario)
            ->post('/dispensaciones', [])
            ->assertForbidden();
    }

    public function test_docente_no_puede_consultar_dispensaciones(): void
    {
        $usuario = $this->crearUsuarioConRol('Docente');

        $this->actingAs($usuario)
            ->get('/dispensaciones')
            ->assertForbidden();
    }

    public function test_quimico_farmaceutico_no_puede_registrar_dispensaciones(): void
    {
        $usuario = $this->crearUsuarioConRol('Químico Farmacéutico');

        $this->actingAs($usuario)
            ->post('/dispensaciones', [])
            ->assertForbidden();
    }

    public function test_administrador_puede_consultar_dispensaciones(): void
    {
        $usuario = $this->crearUsuarioConRol('Administrador');

        $this->actingAs($usuario)
            ->get('/dispensaciones')
            ->assertOk();
    }

    public function test_tecnico_puede_consultar_dispensaciones(): void
    {
        $usuario = $this->crearUsuarioConRol('Tecnico en Farmacia');

        $this->actingAs($usuario)
            ->get('/dispensaciones')
            ->assertOk();
    }

    public function test_tecnico_tiene_permiso_para_intentar_registrar(): void
    {
        $usuario = $this->crearUsuarioConRol('Tecnico en Farmacia');

        $this->actingAs($usuario)
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
}
