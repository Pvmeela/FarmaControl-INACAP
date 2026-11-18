<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Crear tabla de ventas.
     */
    public function up(): void
    {
        Schema::create('ventas', function (Blueprint $table) {
            $table->id();

            // Usuario que registra la venta.
            $table->foreignId('user_id')
                ->constrained('users')
                ->restrictOnDelete();

            // Fecha y hora de la venta.
            $table->dateTime('fecha_venta');

            // Total de la venta simulada.
            $table->decimal('total', 12, 2)
                ->default(0);

            $table->timestamps();

            // Facilitar consultas por fecha.
            $table->index('fecha_venta');
        });
    }

    /**
     * Eliminar tabla de ventas.
     */
    public function down(): void
    {
        Schema::dropIfExists('ventas');
    }
};
