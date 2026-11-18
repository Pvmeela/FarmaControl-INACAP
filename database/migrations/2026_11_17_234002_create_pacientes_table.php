<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pacientes', function (Blueprint $table) {
            $table->id();

            // Identificador ficticio para uso académico
            $table->string('codigo_paciente', 30)->unique();

            // Datos básicos del paciente simulado
            $table->string('nombres', 100);
            $table->string('apellidos', 100);

            $table->date('fecha_nacimiento')->nullable();

            // Estado del registro
            $table->boolean('activo')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pacientes');
    }
};
