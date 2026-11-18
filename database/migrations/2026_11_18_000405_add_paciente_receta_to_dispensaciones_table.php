<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dispensaciones', function (Blueprint $table) {

            // Paciente asociado a la dispensación
            $table->foreignId('paciente_id')
                ->nullable()
                ->constrained('pacientes')
                ->restrictOnDelete();

            // Receta que respalda la dispensación
            $table->foreignId('receta_id')
                ->nullable()
                ->constrained('recetas')
                ->restrictOnDelete();

        });
    }

    public function down(): void
    {
        Schema::table('dispensaciones', function (Blueprint $table) {

            $table->dropForeign(['paciente_id']);
            $table->dropForeign(['receta_id']);

            $table->dropColumn([
                'paciente_id',
                'receta_id',
            ]);

        });
    }
};
