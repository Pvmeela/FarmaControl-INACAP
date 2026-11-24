<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('paciente_programa', function (Blueprint $table) {
            $table->id();

            $table->foreignId('paciente_id')
                ->constrained('pacientes')
                ->restrictOnDelete();

            $table->foreignId('programa_id')
                ->constrained('programas_salud')
                ->restrictOnDelete();

            $table->date('fecha_ingreso');
            $table->enum('estado', ['Activo', 'Inactivo'])
                ->default('Activo');

            $table->timestamps();

            $table->unique(['paciente_id', 'programa_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paciente_programa');
    }
};
