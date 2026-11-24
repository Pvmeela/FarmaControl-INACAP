<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('recetas', function (Blueprint $table) {

            $table->enum('tipo_receta', [
                'simple',
                'retenida',
                'cheque',
            ])->nullable();

            $table->enum('formato_receta', [
                'fisica',
                'digital',
            ])->nullable();

            $table->string('rut_prescriptor', 12)
                ->nullable();

            $table->timestamp('fecha_retencion')
                ->nullable();

            $table->foreignId('retenida_por')
                ->nullable()
                ->constrained('users')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('recetas', function (Blueprint $table) {

            $table->dropConstrainedForeignId('retenida_por');

            $table->dropColumn([
                'tipo_receta',
                'formato_receta',
                'rut_prescriptor',
                'fecha_retencion',
            ]);
        });
    }
};