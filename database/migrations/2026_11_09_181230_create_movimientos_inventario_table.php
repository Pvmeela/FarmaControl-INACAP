<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Crear la tabla de movimientos de inventario.
     */
    public function up(): void
    {
        Schema::create('movimientos_inventario', function (Blueprint $table) {
            $table->id();

            // Lote al que pertenece el movimiento.
            $table->foreignId('lote_id')
                ->constrained('lotes')
                ->restrictOnDelete();

            // Usuario que realiza la operación.
            $table->foreignId('user_id')
                ->constrained('users')
                ->restrictOnDelete();

            // Tipo de movimiento.
            $table->enum('tipo', [
                'entrada',
                'salida',
            ]);

            // Cantidad de unidades involucradas.
            $table->unsignedInteger('cantidad');

            // Existencias antes y después del movimiento.
            $table->unsignedInteger('stock_anterior');
            $table->unsignedInteger('stock_posterior');

            // Motivo u observación de la operación.
            $table->string('motivo', 255);

            $table->timestamps();

            // Índices para consultar el historial.
            $table->index(['lote_id', 'created_at']);
            $table->index(['tipo', 'created_at']);
        });
    }

    /**
     * Revertir la migración.
     */
    public function down(): void
    {
        Schema::dropIfExists('movimientos_inventario');
    }
};
