<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->string('tipo_comprobante', 20)
                ->default('boleta');

            $table->string('convenio', 100)
                ->nullable();

            $table->decimal('descuento', 12, 2)
                ->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->dropColumn([
                'tipo_comprobante',
                'convenio',
                'descuento',
            ]);
        });
    }
};
