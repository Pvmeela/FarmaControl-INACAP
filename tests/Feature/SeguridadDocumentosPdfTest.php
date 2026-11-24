<?php

namespace Tests\Feature;

use App\Models\Rol;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SeguridadDocumentosPdfTest extends TestCase
{
    use RefreshDatabase;

    private function crearUsuario(
        string $nombreRol = 'Administrador',
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

    private function insertar(
        string $tabla,
        array $datos
    ): int {
        return DB::table($tabla)->insertGetId(
            array_merge($datos, [
                'created_at' => now(),
                'updated_at' => now(),
            ])
        );
    }

    private function crearDocumentos(
        User $usuario,
        string $tipo = 'boleta'
    ): array {
        $medicamento = $this->insertar('medicamentos', [
            'nombre' => 'Paracetamol',
            'principio_activo' => 'Paracetamol',
            'concentracion' => '500 mg',
            'forma_farmaceutica' => 'Comprimido',
            'presentacion' => 'Caja de prueba',
            'codigo' => 'MED-PDF-001',
            'activo' => true,
            'stock_minimo' => 5,
            'precio_venta' => 5950,
        ]);

        $paciente = $this->insertar('pacientes', [
            'codigo_paciente' => 'PAC-PDF-001',
            'nombres' => 'Paciente',
            'apellidos' => 'Simulado',
            'rut' => '12345678-5',
            'fecha_nacimiento' => '1990-01-01',
            'activo' => true,
            'estado_alergias' => 'sin_alergias',
            'alergias' => null,
        ]);

        $receta = $this->insertar('recetas', [
            'codigo_receta' => 'REC-PDF-001',
            'paciente_id' => $paciente,
            'fecha_emision' => now()->toDateString(),
            'fecha_vencimiento' => now()
                ->addDays(30)->toDateString(),
            'profesional_prescriptor' => 'Profesional simulado',
            'estado' => 'vigente',
            'observaciones' => 'Prueba academica',
        ]);

        $this->insertar('detalle_recetas', [
            'receta_id' => $receta,
            'medicamento_id' => $medicamento,
            'cantidad_prescrita' => 2,
            'indicaciones' => 'Indicacion simulada',
        ]);

        $venta = $this->insertar('ventas', [
            'user_id' => $usuario->id,
            'fecha_venta' => now(),
            'tipo_comprobante' => $tipo,
            'convenio' => null,
            'descuento' => 0,
            'total' => 5950,
        ]);

        $this->insertar('detalle_ventas', [
            'venta_id' => $venta,
            'medicamento_id' => $medicamento,
            'cantidad' => 1,
            'precio_unitario' => 5950,
            'subtotal' => 5950,
        ]);

        $dispensacion = $this->insertar('dispensaciones', [
            'user_id' => $usuario->id,
            'paciente_id' => $paciente,
            'receta_id' => $receta,
            'fecha_dispensacion' => now(),
            'observaciones' => 'Dispensacion simulada',
            'estado_alergias' => 'con_alergias',
            'alergias' => 'Antecedente historico simulado',
            'alergias_revisadas' => true,
        ]);

        $this->insertar('detalle_dispensaciones', [
            'dispensacion_id' => $dispensacion,
            'medicamento_id' => $medicamento,
            'cantidad' => 1,
        ]);

        return [
            'venta' => $venta,
            'receta' => $receta,
            'dispensacion' => $dispensacion,
        ];
    }

    private function verificarPdf(
        $respuesta,
        string $disposicion,
        string $nombreArchivo
    ): void {
        $respuesta->assertOk();

        $respuesta->assertHeader(
            'Content-Type',
            'application/pdf'
        );

        $this->assertStringStartsWith(
            '%PDF-',
            $respuesta->getContent()
        );

        $cabecera = $respuesta->headers->get(
            'Content-Disposition',
            ''
        );

        $this->assertStringStartsWith(
            $disposicion,
            $cabecera
        );

        $this->assertStringContainsString(
            $nombreArchivo,
            $cabecera
        );

        $this->assertTrue(
            $respuesta->headers->hasCacheControlDirective(
                'private'
            )
        );

        $this->assertTrue(
            $respuesta->headers->hasCacheControlDirective(
                'no-store'
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Generacion y descarga de documentos
    |--------------------------------------------------------------------------
    */

    public function test_puede_visualizar_boleta_pdf(): void
    {
        $usuario = $this->crearUsuario();
        $ids = $this->crearDocumentos($usuario);

        $respuesta = $this->actingAs($usuario)
            ->withSession(['entorno' => 'privada'])
            ->get("/ventas/{$ids['venta']}/pdf");

        $this->verificarPdf(
            $respuesta,
            'inline',
            "comprobante-simulado-{$ids['venta']}.pdf"
        );
    }

    public function test_puede_descargar_boleta_pdf(): void
    {
        $usuario = $this->crearUsuario();
        $ids = $this->crearDocumentos($usuario);

        $respuesta = $this->actingAs($usuario)
            ->withSession(['entorno' => 'privada'])
            ->get("/ventas/{$ids['venta']}/pdf/descargar");

        $this->verificarPdf(
            $respuesta,
            'attachment',
            "comprobante-simulado-{$ids['venta']}.pdf"
        );
    }

    public function test_puede_visualizar_factura_pdf(): void
    {
        $usuario = $this->crearUsuario();
        $ids = $this->crearDocumentos($usuario, 'factura');

        $respuesta = $this->actingAs($usuario)
            ->withSession(['entorno' => 'privada'])
            ->get("/ventas/{$ids['venta']}/pdf");

        $this->verificarPdf(
            $respuesta,
            'inline',
            "comprobante-simulado-{$ids['venta']}.pdf"
        );
    }

    public function test_puede_descargar_factura_pdf(): void
    {
        $usuario = $this->crearUsuario();
        $ids = $this->crearDocumentos($usuario, 'factura');

        $respuesta = $this->actingAs($usuario)
            ->withSession(['entorno' => 'privada'])
            ->get("/ventas/{$ids['venta']}/pdf/descargar");

        $this->verificarPdf(
            $respuesta,
            'attachment',
            "comprobante-simulado-{$ids['venta']}.pdf"
        );
    }

    public function test_puede_visualizar_receta_pdf_en_privada(): void
    {
        $usuario = $this->crearUsuario();
        $ids = $this->crearDocumentos($usuario);

        $respuesta = $this->actingAs($usuario)
            ->withSession(['entorno' => 'privada'])
            ->get("/recetas/{$ids['receta']}/pdf");

        $this->verificarPdf(
            $respuesta,
            'inline',
            "receta-simulada-{$ids['receta']}.pdf"
        );
    }

    public function test_puede_visualizar_receta_pdf_en_cesfam(): void
    {
        $usuario = $this->crearUsuario();
        $ids = $this->crearDocumentos($usuario);

        $respuesta = $this->actingAs($usuario)
            ->withSession(['entorno' => 'cesfam'])
            ->get("/recetas/{$ids['receta']}/pdf");

        $this->verificarPdf(
            $respuesta,
            'inline',
            "receta-simulada-{$ids['receta']}.pdf"
        );
    }

    public function test_puede_descargar_receta_pdf(): void
    {
        $usuario = $this->crearUsuario();
        $ids = $this->crearDocumentos($usuario);

        $respuesta = $this->actingAs($usuario)
            ->withSession(['entorno' => 'cesfam'])
            ->get("/recetas/{$ids['receta']}/pdf/descargar");

        $this->verificarPdf(
            $respuesta,
            'attachment',
            "receta-simulada-{$ids['receta']}.pdf"
        );
    }

    public function test_puede_visualizar_dispensacion_pdf(): void
    {
        $usuario = $this->crearUsuario();
        $ids = $this->crearDocumentos($usuario);

        $respuesta = $this->actingAs($usuario)
            ->withSession(['entorno' => 'cesfam'])
            ->get("/dispensaciones/{$ids['dispensacion']}/pdf");

        $this->verificarPdf(
            $respuesta,
            'inline',
            "dispensacion-simulada-{$ids['dispensacion']}.pdf"
        );
    }

    public function test_puede_descargar_dispensacion_pdf(): void
    {
        $usuario = $this->crearUsuario();
        $ids = $this->crearDocumentos($usuario);

        $respuesta = $this->actingAs($usuario)
            ->withSession(['entorno' => 'cesfam'])
            ->get("/dispensaciones/{$ids['dispensacion']}/pdf/descargar");

        $this->verificarPdf(
            $respuesta,
            'attachment',
            "dispensacion-simulada-{$ids['dispensacion']}.pdf"
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Restricciones entre entornos
    |--------------------------------------------------------------------------
    */

    public function test_no_permite_visualizar_venta_pdf_en_cesfam(): void
    {
        $usuario = $this->crearUsuario();
        $ids = $this->crearDocumentos($usuario);

        $this->actingAs($usuario)
            ->withSession(['entorno' => 'cesfam'])
            ->get("/ventas/{$ids['venta']}/pdf")
            ->assertForbidden();
    }

    public function test_no_permite_descargar_venta_pdf_en_cesfam(): void
    {
        $usuario = $this->crearUsuario();
        $ids = $this->crearDocumentos($usuario);

        $this->actingAs($usuario)
            ->withSession(['entorno' => 'cesfam'])
            ->get("/ventas/{$ids['venta']}/pdf/descargar")
            ->assertForbidden();
    }

    public function test_no_permite_visualizar_dispensacion_pdf_en_privada(): void
    {
        $usuario = $this->crearUsuario();
        $ids = $this->crearDocumentos($usuario);

        $this->actingAs($usuario)
            ->withSession(['entorno' => 'privada'])
            ->get("/dispensaciones/{$ids['dispensacion']}/pdf")
            ->assertForbidden();
    }

    public function test_no_permite_descargar_dispensacion_pdf_en_privada(): void
    {
        $usuario = $this->crearUsuario();
        $ids = $this->crearDocumentos($usuario);

        $this->actingAs($usuario)
            ->withSession(['entorno' => 'privada'])
            ->get("/dispensaciones/{$ids['dispensacion']}/pdf/descargar")
            ->assertForbidden();
    }

    public function test_no_permite_venta_pdf_sin_entorno(): void
    {
        $usuario = $this->crearUsuario();
        $ids = $this->crearDocumentos($usuario);

        $this->actingAs($usuario)
            ->get("/ventas/{$ids['venta']}/pdf")
            ->assertForbidden();
    }

    public function test_no_permite_dispensacion_pdf_sin_entorno(): void
    {
        $usuario = $this->crearUsuario();
        $ids = $this->crearDocumentos($usuario);

        $this->actingAs($usuario)
            ->get("/dispensaciones/{$ids['dispensacion']}/pdf")
            ->assertForbidden();
    }

    /*
    |--------------------------------------------------------------------------
    | Autenticacion y permisos
    |--------------------------------------------------------------------------
    */

    public function test_invitado_no_puede_acceder_a_documentos_pdf(): void
    {
        $usuario = $this->crearUsuario();
        $ids = $this->crearDocumentos($usuario);

        $rutas = [
            "/ventas/{$ids['venta']}/pdf",
            "/ventas/{$ids['venta']}/pdf/descargar",
            "/recetas/{$ids['receta']}/pdf",
            "/recetas/{$ids['receta']}/pdf/descargar",
            "/dispensaciones/{$ids['dispensacion']}/pdf",
            "/dispensaciones/{$ids['dispensacion']}/pdf/descargar",
        ];

        foreach ($rutas as $ruta) {
            $this->get($ruta)
                ->assertRedirect(route('login'));
        }
    }

    public function test_usuario_inactivo_no_puede_acceder_a_documentos_pdf(): void
    {
        $usuario = $this->crearUsuario(
            'Administrador',
            false
        );

        $ids = $this->crearDocumentos($usuario);

        $rutas = [
            'privada' => [
                "/ventas/{$ids['venta']}/pdf",
                "/ventas/{$ids['venta']}/pdf/descargar",
            ],
            'cesfam' => [
                "/recetas/{$ids['receta']}/pdf",
                "/recetas/{$ids['receta']}/pdf/descargar",
                "/dispensaciones/{$ids['dispensacion']}/pdf",
                "/dispensaciones/{$ids['dispensacion']}/pdf/descargar",
            ],
        ];

        foreach ($rutas as $entorno => $documentos) {
            foreach ($documentos as $ruta) {
                $this->actingAs($usuario)
                    ->withSession(['entorno' => $entorno])
                    ->get($ruta)
                    ->assertRedirect(route('login'));

                $this->assertGuest();
            }
        }
    }

    public function test_usuario_sin_rol_no_puede_acceder_a_documentos_pdf(): void
    {
        $usuario = User::factory()->create([
            'rol_id' => null,
            'activo' => true,
        ]);

        $ids = $this->crearDocumentos($usuario);

        $rutas = [
            'privada' => [
                "/ventas/{$ids['venta']}/pdf",
                "/ventas/{$ids['venta']}/pdf/descargar",
            ],
            'cesfam' => [
                "/recetas/{$ids['receta']}/pdf",
                "/recetas/{$ids['receta']}/pdf/descargar",
                "/dispensaciones/{$ids['dispensacion']}/pdf",
                "/dispensaciones/{$ids['dispensacion']}/pdf/descargar",
            ],
        ];

        foreach ($rutas as $entorno => $documentos) {
            foreach ($documentos as $ruta) {
                $this->actingAs($usuario)
                    ->withSession(['entorno' => $entorno])
                    ->get($ruta)
                    ->assertRedirect(route('login'));

                $this->assertGuest();
            }
        }
    }

    public function test_rol_no_autorizado_no_puede_acceder_a_dispensacion_pdf(): void
    {
        $usuario = $this->crearUsuario('Rol No Autorizado');
        $ids = $this->crearDocumentos($usuario);

        $this->actingAs($usuario)
            ->withSession(['entorno' => 'cesfam'])
            ->get("/dispensaciones/{$ids['dispensacion']}/pdf")
            ->assertRedirect(route('login'));

        $this->assertGuest();

        $this->actingAs($usuario)
            ->withSession(['entorno' => 'cesfam'])
            ->get("/dispensaciones/{$ids['dispensacion']}/pdf/descargar")
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_quimico_puede_visualizar_dispensacion_pdf(): void
    {
        $usuario = $this->crearUsuario('Quimico Farmaceutico');
        $ids = $this->crearDocumentos($usuario);

        $respuesta = $this->actingAs($usuario)
            ->withSession(['entorno' => 'cesfam'])
            ->get("/dispensaciones/{$ids['dispensacion']}/pdf");

        $this->verificarPdf(
            $respuesta,
            'inline',
            "dispensacion-simulada-{$ids['dispensacion']}.pdf"
        );
    }

    public function test_tecnico_puede_visualizar_dispensacion_pdf(): void
    {
        $usuario = $this->crearUsuario('Tecnico en Farmacia');
        $ids = $this->crearDocumentos($usuario);

        $respuesta = $this->actingAs($usuario)
            ->withSession(['entorno' => 'cesfam'])
            ->get("/dispensaciones/{$ids['dispensacion']}/pdf");

        $this->verificarPdf(
            $respuesta,
            'inline',
            "dispensacion-simulada-{$ids['dispensacion']}.pdf"
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Documentos inexistentes
    |--------------------------------------------------------------------------
    */

    public function test_venta_pdf_inexistente_devuelve_404(): void
    {
        $this->actingAs($this->crearUsuario())
            ->withSession(['entorno' => 'privada'])
            ->get('/ventas/999999/pdf')
            ->assertNotFound();
    }

    public function test_receta_pdf_inexistente_devuelve_404(): void
    {
        $this->actingAs($this->crearUsuario())
            ->withSession(['entorno' => 'cesfam'])
            ->get('/recetas/999999/pdf')
            ->assertNotFound();
    }

    public function test_dispensacion_pdf_inexistente_devuelve_404(): void
    {
        $this->actingAs($this->crearUsuario())
            ->withSession(['entorno' => 'cesfam'])
            ->get('/dispensaciones/999999/pdf')
            ->assertNotFound();
    }

    /*
    |--------------------------------------------------------------------------
    | Calculo de importes del comprobante
    |--------------------------------------------------------------------------
    */

    public function test_comprobante_desglosa_correctamente_el_iva(): void
    {
        $usuario = $this->crearUsuario();
        $ids = $this->crearDocumentos($usuario);

        $this->actingAs($usuario)
            ->withSession(['entorno' => 'privada'])
            ->get("/ventas/{$ids['venta']}/comprobante")
            ->assertOk()
            ->assertInertia(fn ($pagina) => $pagina
                ->component('Ventas/Comprobante')
                ->where('totales.subtotal', '5950.00')
                ->where('totales.descuento', '0.00')
                ->where('totales.neto', '5000.00')
                ->where('totales.iva', '950.00')
                ->where('totales.tasa_iva', 19)
                ->where('totales.total', '5950.00')
            );
    }
}
