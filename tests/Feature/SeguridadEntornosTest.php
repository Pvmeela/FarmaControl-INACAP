<?php

namespace Tests\Feature;

use App\Models\Rol;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeguridadEntornosTest extends TestCase
{
    use RefreshDatabase;

    private function crearAdministrador(): User
    {
        $rol = Rol::firstOrCreate(
            ['nombre' => 'Administrador'],
            ['descripcion' => 'Administrador de prueba']
        );

        return User::factory()->create([
            'rol_id' => $rol->id,
            'activo' => true,
        ]);
    }

    public function test_no_permite_consultar_ventas_desde_cesfam(): void
    {
        $this->actingAs($this->crearAdministrador())
            ->withSession(['entorno' => 'cesfam'])
            ->get('/ventas')
            ->assertForbidden();
    }

    public function test_no_permite_registrar_ventas_desde_cesfam(): void
    {
        $this->actingAs($this->crearAdministrador())
            ->withSession(['entorno' => 'cesfam'])
            ->post('/ventas', [])
            ->assertForbidden();
    }

    public function test_permite_consultar_ventas_desde_privada(): void
    {
        $this->actingAs($this->crearAdministrador())
            ->withSession(['entorno' => 'privada'])
            ->get('/ventas')
            ->assertOk();
    }

    public function test_no_permite_consultar_pacientes_desde_privada(): void
    {
        $this->actingAs($this->crearAdministrador())
            ->withSession(['entorno' => 'privada'])
            ->get('/pacientes')
            ->assertForbidden();
    }

    public function test_no_permite_registrar_pacientes_desde_privada(): void
    {
        $this->actingAs($this->crearAdministrador())
            ->withSession(['entorno' => 'privada'])
            ->post('/pacientes', [])
            ->assertForbidden();
    }

    public function test_permite_consultar_pacientes_desde_cesfam(): void
    {
        $this->actingAs($this->crearAdministrador())
            ->withSession(['entorno' => 'cesfam'])
            ->get('/pacientes')
            ->assertOk();
    }

    public function test_no_permite_consultar_dispensaciones_desde_privada(): void
    {
        $this->actingAs($this->crearAdministrador())
            ->withSession(['entorno' => 'privada'])
            ->get('/dispensaciones')
            ->assertForbidden();
    }

    public function test_no_permite_registrar_dispensaciones_desde_privada(): void
    {
        $this->actingAs($this->crearAdministrador())
            ->withSession(['entorno' => 'privada'])
            ->post('/dispensaciones', [])
            ->assertForbidden();
    }

    public function test_permite_consultar_dispensaciones_desde_cesfam(): void
    {
        $this->actingAs($this->crearAdministrador())
            ->withSession(['entorno' => 'cesfam'])
            ->get('/dispensaciones')
            ->assertOk();
    }

    public function test_no_permite_consultar_ventas_sin_entorno(): void
    {
        $this->actingAs($this->crearAdministrador())
            ->get('/ventas')
            ->assertForbidden();
    }

    public function test_no_permite_consultar_pacientes_sin_entorno(): void
    {
        $this->actingAs($this->crearAdministrador())
            ->get('/pacientes')
            ->assertForbidden();
    }

    public function test_no_permite_consultar_dispensaciones_sin_entorno(): void
    {
        $this->actingAs($this->crearAdministrador())
            ->get('/dispensaciones')
            ->assertForbidden();
    }

    public function test_no_permite_acceso_con_entorno_invalido(): void
    {
        $this->actingAs($this->crearAdministrador())
            ->withSession(['entorno' => 'entorno_invalido'])
            ->get('/dispensaciones')
            ->assertForbidden();
    }

    public function test_medicamentos_disponibles_en_privada(): void
    {
        $this->actingAs($this->crearAdministrador())
            ->withSession(['entorno' => 'privada'])
            ->get('/medicamentos')
            ->assertOk();
    }

    public function test_medicamentos_disponibles_en_cesfam(): void
    {
        $this->actingAs($this->crearAdministrador())
            ->withSession(['entorno' => 'cesfam'])
            ->get('/medicamentos')
            ->assertOk();
    }

    public function test_inventario_disponible_en_privada(): void
    {
        $this->actingAs($this->crearAdministrador())
            ->withSession(['entorno' => 'privada'])
            ->get('/lotes')
            ->assertOk();
    }

    public function test_inventario_disponible_en_cesfam(): void
    {
        $this->actingAs($this->crearAdministrador())
            ->withSession(['entorno' => 'cesfam'])
            ->get('/lotes')
            ->assertOk();
    }

    public function test_movimientos_disponibles_en_privada(): void
    {
        $this->actingAs($this->crearAdministrador())
            ->withSession(['entorno' => 'privada'])
            ->get('/movimientos')
            ->assertOk();
    }

    public function test_movimientos_disponibles_en_cesfam(): void
    {
        $this->actingAs($this->crearAdministrador())
            ->withSession(['entorno' => 'cesfam'])
            ->get('/movimientos')
            ->assertOk();
    }

    public function test_recetas_disponibles_en_privada(): void
    {
        $this->actingAs($this->crearAdministrador())
            ->withSession(['entorno' => 'privada'])
            ->get('/recetas')
            ->assertOk();
    }

    public function test_recetas_disponibles_en_cesfam(): void
    {
        $this->actingAs($this->crearAdministrador())
            ->withSession(['entorno' => 'cesfam'])
            ->get('/recetas')
            ->assertOk();
    }
}
