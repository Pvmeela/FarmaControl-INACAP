<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ejecutar la migración.
     */
    public function up(): void
    {
        Schema::table('dispensaciones', function (Blueprint $table) {

            // Estado de alergias al momento de dispensar.
            $table->string('estado_alergias', 20)
                ->nullable();

            // Antecedentes de alergias del paciente.
            $table->text('alergias')
                ->nullable();

            // Confirmación de revisión de alergias.
            $table->boolean('alergias_revisadas')
                ->default(false);

        });
    }

    /**
     * Revertir la migración.
     */
    public function down(): void
    {
        Schema::table('dispensaciones', function (Blueprint $table) {

            $table->dropColumn([
                'estado_alergias',
                'alergias',
                'alergias_revisadas',
            ]);

        });
    }
};
