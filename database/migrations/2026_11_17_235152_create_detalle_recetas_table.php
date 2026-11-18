<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detalle_recetas', function (Blueprint $table) {
            $table->id();

            // Receta asociada
            $table->foreignId('receta_id')
                ->constrained('recetas')
                ->restrictOnDelete();

            // Medicamento prescrito
            $table->foreignId('medicamento_id')
                ->constrained('medicamentos')
                ->restrictOnDelete();

            // Cantidad indicada en la receta
            $table->unsignedInteger('cantidad_prescrita');

            // Indicaciones simuladas
            $table->string('indicaciones', 255)->nullable();

            $table->timestamps();

            // Evita repetir el mismo medicamento en una receta
            $table->unique([
                'receta_id',
                'medicamento_id',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detalle_recetas');
    }
};
