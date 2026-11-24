<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('medicamentos', function (Blueprint $table) {
            $table->boolean('es_controlado')->default(false);

            $table->enum('tipo_control', [
                'estupefaciente',
                'psicotropico',
            ])->nullable();

            $table->boolean('requiere_receta')->default(false);

            $table->enum('tipo_receta', [
                'simple',
                'retenida',
                'cheque',
            ])->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('medicamentos', function (Blueprint $table) {
            $table->dropColumn([
                'es_controlado',
                'tipo_control',
                'requiere_receta',
                'tipo_receta',
            ]);
        });
    }
};