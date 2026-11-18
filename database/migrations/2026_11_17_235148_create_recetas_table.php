<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recetas', function (Blueprint $table) {
            $table->id();

            // Identificador ficticio de la receta
            $table->string('codigo_receta', 30)->unique();

            // Paciente asociado
            $table->foreignId('paciente_id')
                ->constrained('pacientes')
                ->restrictOnDelete();

            // Fechas de emisión y vencimiento
            $table->date('fecha_emision');
            $table->date('fecha_vencimiento');

            // Profesional prescriptor simulado
            $table->string('profesional_prescriptor', 150);

            // Estado administrativo de la receta
            $table->enum('estado', [
                'vigente',
                'utilizada',
                'anulada',
            ])->default('vigente');

            // Observaciones opcionales
            $table->string('observaciones', 255)->nullable();

            $table->timestamps();

            $table->index('fecha_vencimiento');
            $table->index('estado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recetas');
    }
};
