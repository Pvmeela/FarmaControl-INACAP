<?php

namespace Tests\Feature;

use App\Models\Rol;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class SeguridadCacheTest extends TestCase
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

    private function verificarCache(
        TestResponse $respuesta
    ): void {
        $cache = $respuesta->headers->get(
            'Cache-Control',
            ''
        );

        $this->assertStringContainsString(
            'no-store',
            $cache
        );

        $this->assertStringContainsString(
            'no-cache',
            $cache
        );

        $this->assertStringContainsString(
            'private',
            $cache
        );

        $this->assertStringContainsString(
            'max-age=0',
            $cache
        );

        $this->assertStringContainsString(
            'must-revalidate',
            $cache
        );

        $respuesta->assertHeader(
            'Pragma',
            'no-cache'
        );

        $respuesta->assertHeader(
            'Expires',
            'Thu, 01 Jan 1970 00:00:00 GMT'
        );
    }

    public function test_login_no_se_almacena_en_cache(): void
    {
        $respuesta = $this->get('/login');

        $respuesta->assertOk();

        $this->verificarCache($respuesta);
    }

    public function test_dashboard_no_se_almacena_en_cache(): void
    {
        $respuesta = $this
            ->actingAs($this->crearAdministrador())
            ->withSession(['entorno' => 'privada'])
            ->get('/dashboard');

        $respuesta->assertOk();

        $this->verificarCache($respuesta);
    }

    public function test_medicamentos_no_se_almacenan_en_cache(): void
    {
        $respuesta = $this
            ->actingAs($this->crearAdministrador())
            ->withSession(['entorno' => 'privada'])
            ->get('/medicamentos');

        $respuesta->assertOk();

        $this->verificarCache($respuesta);
    }

    public function test_lotes_no_se_almacenan_en_cache(): void
    {
        $respuesta = $this
            ->actingAs($this->crearAdministrador())
            ->withSession(['entorno' => 'privada'])
            ->get('/lotes');

        $respuesta->assertOk();

        $this->verificarCache($respuesta);
    }

    public function test_movimientos_no_se_almacenan_en_cache(): void
    {
        $respuesta = $this
            ->actingAs($this->crearAdministrador())
            ->withSession(['entorno' => 'privada'])
            ->get('/movimientos');

        $respuesta->assertOk();

        $this->verificarCache($respuesta);
    }

    public function test_ventas_no_se_almacenan_en_cache(): void
    {
        $respuesta = $this
            ->actingAs($this->crearAdministrador())
            ->withSession(['entorno' => 'privada'])
            ->get('/ventas');

        $respuesta->assertOk();

        $this->verificarCache($respuesta);
    }

    public function test_pacientes_no_se_almacenan_en_cache(): void
    {
        $respuesta = $this
            ->actingAs($this->crearAdministrador())
            ->withSession(['entorno' => 'cesfam'])
            ->get('/pacientes');

        $respuesta->assertOk();

        $this->verificarCache($respuesta);
    }

    public function test_recetas_no_se_almacenan_en_cache(): void
    {
        $respuesta = $this
            ->actingAs($this->crearAdministrador())
            ->withSession(['entorno' => 'cesfam'])
            ->get('/recetas');

        $respuesta->assertOk();

        $this->verificarCache($respuesta);
    }

    public function test_dispensaciones_no_se_almacenan_en_cache(): void
    {
        $respuesta = $this
            ->actingAs($this->crearAdministrador())
            ->withSession(['entorno' => 'cesfam'])
            ->get('/dispensaciones');

        $respuesta->assertOk();

        $this->verificarCache($respuesta);
    }

    public function test_usuarios_no_se_almacenan_en_cache(): void
    {
        $respuesta = $this
            ->actingAs($this->crearAdministrador())
            ->withSession(['entorno' => 'privada'])
            ->get('/usuarios');

        $respuesta->assertOk();

        $this->verificarCache($respuesta);
    }

    public function test_cierre_de_sesion_no_se_almacena_en_cache(): void
    {
        $respuesta = $this
            ->actingAs($this->crearAdministrador())
            ->withSession(['entorno' => 'cesfam'])
            ->post('/logout');

        $respuesta->assertRedirect(route('login'));

        $this->assertGuest();

        $this->verificarCache($respuesta);
    }

    public function test_respuesta_inertia_no_se_almacena_en_cache(): void
    {
        $respuesta = $this
            ->actingAs($this->crearAdministrador())
            ->withSession(['entorno' => 'cesfam'])
            ->withHeaders([
                'X-Inertia' => 'true',
                'X-Inertia-Version' => app(
                    \App\Http\Middleware\HandleInertiaRequests::class
                )->version(request()) ?? '',
            ])
            ->get('/pacientes');

        $respuesta->assertOk();

        $respuesta->assertHeader(
            'X-Inertia',
            'true'
        );

        $this->verificarCache($respuesta);
    }
}
