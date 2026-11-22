<?php

namespace Tests\Feature;

use App\Models\Rol;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DispensacionTest extends TestCase
{
    use RefreshDatabase;

    private function crearAdministrador(): User
    {
        $rol = Rol::firstOrCreate(
            ['nombre' => 'Administrador'],
            ['descripcion' => 'Administrador de FarmaControl']
        );

        return User::factory()->create([
            'rol_id' => $rol->id,
        ]);
    }

    public function test_no_permite_dispensar_con_receta_vencida(): void
    {
        $usuario = $this->crearAdministrador();

        $pacienteId = DB::table('pacientes')->insertGetId([
            'codigo_paciente' => 'PAC-TEST-001',
            'nombres' => 'Paciente',
            'apellidos' => 'Prueba',
            'activo' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $medicamentoId = DB::table('medicamentos')->insertGetId([
            'nombre' => 'Paracetamol de prueba',
            'principio_activo' => 'Paracetamol',
            'concentracion' => '500 mg',
            'forma_farmaceutica' => 'Comprimido',
            'presentacion' => 'Caja',
            'codigo' => 'MED-TEST-001',
            'activo' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $recetaId = DB::table('recetas')->insertGetId([
            'codigo_receta' => 'REC-TEST-001',
            'paciente_id' => $pacienteId,
            'fecha_emision' => now()->subDays(15)->toDateString(),
            'fecha_vencimiento' => now()->subDay()->toDateString(),
            'profesional_prescriptor' => 'Profesional de prueba',
            'estado' => 'vigente',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('detalle_recetas')->insert([
            'receta_id' => $recetaId,
            'medicamento_id' => $medicamentoId,
            'cantidad_prescrita' => 5,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $loteId = DB::table('lotes')->insertGetId([
            'medicamento_id' => $medicamentoId,
            'numero_lote' => 'LOTE-TEST-001',
            'fecha_vencimiento' => now()->addYear()->toDateString(),
            'cantidad_inicial' => 10,
            'cantidad_disponible' => 10,
            'fecha_recepcion' => now()->subDay()->toDateString(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $respuesta = $this->actingAs($usuario)
            ->from('/dispensaciones')
            ->post('/dispensaciones', [
                'paciente_id' => $pacienteId,
                'receta_id' => $recetaId,
                'medicamento_id' => $medicamentoId,
                'cantidad' => 1,
            ]);

        $respuesta->assertRedirect('/dispensaciones');
        $respuesta->assertSessionHasErrors('receta_id');

        $this->assertDatabaseCount('dispensaciones', 0);
        $this->assertDatabaseCount('detalle_dispensaciones', 0);
        $this->assertDatabaseCount('movimientos_inventario', 0);

        $this->assertDatabaseHas('lotes', [
            'id' => $loteId,
            'cantidad_disponible' => 10,
        ]);
    }

    public function test_no_permite_cantidad_superior_a_la_prescrita(): void
    {
        $usuario = $this->crearAdministrador();

        $pacienteId = DB::table('pacientes')->insertGetId([
            'codigo_paciente' => 'PAC-TEST-002',
            'nombres' => 'Paciente',
            'apellidos' => 'Cantidad',
            'activo' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $medicamentoId = DB::table('medicamentos')->insertGetId([
            'nombre' => 'Paracetamol de prueba',
            'principio_activo' => 'Paracetamol',
            'concentracion' => '500 mg',
            'forma_farmaceutica' => 'Comprimido',
            'presentacion' => 'Caja',
            'codigo' => 'MED-TEST-002',
            'activo' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $recetaId = DB::table('recetas')->insertGetId([
            'codigo_receta' => 'REC-TEST-002',
            'paciente_id' => $pacienteId,
            'fecha_emision' => now()->subDay()->toDateString(),
            'fecha_vencimiento' => now()->addDays(30)->toDateString(),
            'profesional_prescriptor' => 'Profesional de prueba',
            'estado' => 'vigente',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('detalle_recetas')->insert([
            'receta_id' => $recetaId,
            'medicamento_id' => $medicamentoId,
            'cantidad_prescrita' => 5,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $loteId = DB::table('lotes')->insertGetId([
            'medicamento_id' => $medicamentoId,
            'numero_lote' => 'LOTE-TEST-002',
            'fecha_vencimiento' => now()->addYear()->toDateString(),
            'cantidad_inicial' => 20,
            'cantidad_disponible' => 20,
            'fecha_recepcion' => now()->subDay()->toDateString(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // La receta permite 5 unidades, pero se solicitan 8.
        $respuesta = $this->actingAs($usuario)
            ->from('/dispensaciones')
            ->post('/dispensaciones', [
                'paciente_id' => $pacienteId,
                'receta_id' => $recetaId,
                'medicamento_id' => $medicamentoId,
                'cantidad' => 8,
            ]);

        $respuesta->assertRedirect('/dispensaciones');
        $respuesta->assertSessionHasErrors('cantidad');

        $this->assertDatabaseCount('dispensaciones', 0);
        $this->assertDatabaseCount('detalle_dispensaciones', 0);
        $this->assertDatabaseCount('movimientos_inventario', 0);

        $this->assertDatabaseHas('lotes', [
            'id' => $loteId,
            'cantidad_disponible' => 20,
        ]);
    }

    public function test_no_permite_dispensar_sin_stock_suficiente(): void
    {
        $usuario = $this->crearAdministrador();

        $pacienteId = DB::table('pacientes')->insertGetId([
            'codigo_paciente' => 'PAC-TEST-003',
            'nombres' => 'Paciente',
            'apellidos' => 'Stock',
            'activo' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $medicamentoId = DB::table('medicamentos')->insertGetId([
            'nombre' => 'Ibuprofeno de prueba',
            'principio_activo' => 'Ibuprofeno',
            'concentracion' => '400 mg',
            'forma_farmaceutica' => 'Comprimido',
            'presentacion' => 'Caja',
            'codigo' => 'MED-TEST-003',
            'activo' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $recetaId = DB::table('recetas')->insertGetId([
            'codigo_receta' => 'REC-TEST-003',
            'paciente_id' => $pacienteId,
            'fecha_emision' => now()->subDay()->toDateString(),
            'fecha_vencimiento' => now()->addDays(30)->toDateString(),
            'profesional_prescriptor' => 'Profesional de prueba',
            'estado' => 'vigente',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('detalle_recetas')->insert([
            'receta_id' => $recetaId,
            'medicamento_id' => $medicamentoId,
            'cantidad_prescrita' => 10,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $loteId = DB::table('lotes')->insertGetId([
            'medicamento_id' => $medicamentoId,
            'numero_lote' => 'LOTE-TEST-003',
            'fecha_vencimiento' => now()->addYear()->toDateString(),
            'cantidad_inicial' => 3,
            'cantidad_disponible' => 3,
            'fecha_recepcion' => now()->subDay()->toDateString(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Se solicitan 5 unidades, pero solo hay 3 disponibles.
        $respuesta = $this->actingAs($usuario)
            ->from('/dispensaciones')
            ->post('/dispensaciones', [
                'paciente_id' => $pacienteId,
                'receta_id' => $recetaId,
                'medicamento_id' => $medicamentoId,
                'cantidad' => 5,
            ]);

        $respuesta->assertRedirect('/dispensaciones');
        $respuesta->assertSessionHasErrors('cantidad');

        $this->assertDatabaseCount('dispensaciones', 0);
        $this->assertDatabaseCount('detalle_dispensaciones', 0);
        $this->assertDatabaseCount('movimientos_inventario', 0);

        $this->assertDatabaseHas('lotes', [
            'id' => $loteId,
            'cantidad_disponible' => 3,
        ]);
    }

    public function test_dispensacion_descuenta_lotes_en_orden_fefo(): void
    {
        $usuario = $this->crearAdministrador();

        // Crear paciente de prueba.
        $pacienteId = DB::table('pacientes')->insertGetId([
            'codigo_paciente' => 'PAC-TEST-004',
            'nombres' => 'Paciente',
            'apellidos' => 'FEFO',
            'activo' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Crear medicamento de prueba.
        $medicamentoId = DB::table('medicamentos')->insertGetId([
            'nombre' => 'Paracetamol FEFO',
            'principio_activo' => 'Paracetamol',
            'concentracion' => '500 mg',
            'forma_farmaceutica' => 'Comprimido',
            'presentacion' => 'Caja de comprimidos',
            'codigo' => 'MED-TEST-004',
            'activo' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Crear receta vigente para 7 unidades.
        $recetaId = DB::table('recetas')->insertGetId([
            'codigo_receta' => 'REC-TEST-004',
            'paciente_id' => $pacienteId,
            'fecha_emision' => now()->subDay()->toDateString(),
            'fecha_vencimiento' => now()->addDays(30)->toDateString(),
            'profesional_prescriptor' => 'Profesional de prueba',
            'estado' => 'vigente',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('detalle_recetas')->insert([
            'receta_id' => $recetaId,
            'medicamento_id' => $medicamentoId,
            'cantidad_prescrita' => 7,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Lote A: vence primero y tiene 5 unidades.
        $loteA = DB::table('lotes')->insertGetId([
            'medicamento_id' => $medicamentoId,
            'numero_lote' => 'LOTE-FEFO-A',
            'fecha_vencimiento' => now()->addDays(20)->toDateString(),
            'cantidad_inicial' => 5,
            'cantidad_disponible' => 5,
            'fecha_recepcion' => now()->subDays(10)->toDateString(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Lote B: vence después y tiene 10 unidades.
        $loteB = DB::table('lotes')->insertGetId([
            'medicamento_id' => $medicamentoId,
            'numero_lote' => 'LOTE-FEFO-B',
            'fecha_vencimiento' => now()->addDays(180)->toDateString(),
            'cantidad_inicial' => 10,
            'cantidad_disponible' => 10,
            'fecha_recepcion' => now()->subDays(5)->toDateString(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Dispensar 7 unidades.
        $respuesta = $this->actingAs($usuario)
            ->post('/dispensaciones', [
                'paciente_id' => $pacienteId,
                'receta_id' => $recetaId,
                'medicamento_id' => $medicamentoId,
                'cantidad' => 7,
            ]);

        $respuesta->assertRedirect('/dispensaciones');
        $respuesta->assertSessionHasNoErrors();

        // Verificar que se registró una dispensación.
        $this->assertDatabaseCount('dispensaciones', 1);

        $this->assertDatabaseHas('detalle_dispensaciones', [
            'medicamento_id' => $medicamentoId,
            'cantidad' => 7,
        ]);

        // FEFO: el lote A debe agotarse primero.
        $this->assertDatabaseHas('lotes', [
            'id' => $loteA,
            'cantidad_disponible' => 0,
        ]);

        // El lote B debe conservar 8 unidades.
        $this->assertDatabaseHas('lotes', [
            'id' => $loteB,
            'cantidad_disponible' => 8,
        ]);

        // Deben existir dos movimientos de salida.
        $this->assertDatabaseCount('movimientos_inventario', 2);

        $this->assertDatabaseHas('movimientos_inventario', [
            'lote_id' => $loteA,
            'tipo' => 'salida',
            'cantidad' => 5,
            'stock_anterior' => 5,
            'stock_posterior' => 0,
        ]);

        $this->assertDatabaseHas('movimientos_inventario', [
            'lote_id' => $loteB,
            'tipo' => 'salida',
            'cantidad' => 2,
            'stock_anterior' => 10,
            'stock_posterior' => 8,
        ]);

        // La receta debe quedar utilizada.
        $this->assertDatabaseHas('recetas', [
            'id' => $recetaId,
            'estado' => 'utilizada',
        ]);
    }
}
