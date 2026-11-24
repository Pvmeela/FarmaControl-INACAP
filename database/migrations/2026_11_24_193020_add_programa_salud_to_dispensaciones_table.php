<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dispensaciones', function (Blueprint $table) {

            $table->foreignId('programa_id')
                ->nullable()
                ->constrained('programas_salud')
                ->restrictOnDelete();

            $table->string('programa_nombre', 150)
                ->nullable();

            $table->string('programa_tipo', 20)
                ->nullable();

            $table->decimal('cobertura', 5, 2)
                ->nullable();

            $table->unsignedInteger('cuota_maxima')
                ->nullable();

            $table->unsignedInteger('periodo_dias')
                ->nullable();

            $table->index(
                [
                    'paciente_id',
                    'programa_id',
                    'fecha_dispensacion'
                ],
                'dispensaciones_programa_fecha_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::table('dispensaciones', function (Blueprint $table) {

            $table->dropIndex(
                'dispensaciones_programa_fecha_idx'
            );

            $table->dropForeign(['programa_id']);

            $table->dropColumn([
                'programa_id',
                'programa_nombre',
                'programa_tipo',
                'cobertura',
                'cuota_maxima',
                'periodo_dias',
            ]);
        });
    }
};