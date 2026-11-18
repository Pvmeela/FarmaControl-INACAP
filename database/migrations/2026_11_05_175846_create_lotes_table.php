<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('lotes', function (Blueprint $table) {
            $table->id();

            // Medicamento al que pertenece el lote
            $table->foreignId('medicamento_id')
                ->constrained('medicamentos')
                ->restrictOnDelete();

            // Identificación del lote
            $table->string('numero_lote', 100);

            // Fecha de vencimiento
            $table->date('fecha_vencimiento');

            // Cantidad recibida inicialmente
            $table->unsignedInteger('cantidad_inicial');

            // Cantidad disponible actualmente
            $table->unsignedInteger('cantidad_disponible');

            // Fecha de recepción del lote
            $table->date('fecha_recepcion');

            $table->timestamps();

            // Evitar lotes duplicados para un mismo medicamento
            $table->unique([
                'medicamento_id',
                'numero_lote'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lotes');
    }
};
