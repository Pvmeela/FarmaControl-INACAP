<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dispensaciones', function (Blueprint $table) {

            $table->enum('tipo_retiro', [
                'paciente',
                'tercero_autorizado'
            ])->nullable();

            $table->string('retirante_nombre', 200)
                ->nullable();

            $table->string('retirante_rut', 12)
                ->nullable();

            $table->string('retirante_parentesco', 100)
                ->nullable();

            $table->boolean('autorizacion_verificada')
                ->nullable();

        });
    }

    public function down(): void
    {
        Schema::table('dispensaciones', function (Blueprint $table) {

            $table->dropColumn([
                'tipo_retiro',
                'retirante_nombre',
                'retirante_rut',
                'retirante_parentesco',
                'autorizacion_verificada',
            ]);

        });
    }
};