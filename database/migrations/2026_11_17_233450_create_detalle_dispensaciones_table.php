<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detalle_dispensaciones', function (Blueprint $table) {
            $table->id();

            // Dispensación a la que pertenece
            $table->foreignId('dispensacion_id')
                ->constrained('dispensaciones')
                ->restrictOnDelete();

            // Medicamento dispensado
            $table->foreignId('medicamento_id')
                ->constrained('medicamentos')
                ->restrictOnDelete();

            // Cantidad entregada
            $table->unsignedInteger('cantidad');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detalle_dispensaciones');
    }
};
