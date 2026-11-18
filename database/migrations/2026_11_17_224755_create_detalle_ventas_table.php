<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Crear tabla de detalle de ventas.
     */
    public function up(): void
    {
        Schema::create('detalle_ventas', function (Blueprint $table) {
            $table->id();

            // Venta a la que pertenece el detalle.
            $table->foreignId('venta_id')
                ->constrained('ventas')
                ->cascadeOnDelete();

            // Medicamento vendido.
            $table->foreignId('medicamento_id')
                ->constrained('medicamentos')
                ->restrictOnDelete();

            // Cantidad vendida.
            $table->unsignedInteger('cantidad');

            // Precio por unidad al momento de la venta.
            $table->decimal('precio_unitario', 12, 2);

            // Total correspondiente al medicamento.
            $table->decimal('subtotal', 12, 2);

            $table->timestamps();

            // Facilitar consultas del historial.
            $table->index('medicamento_id');
        });
    }

    /**
     * Eliminar tabla de detalle de ventas.
     */
    public function down(): void
    {
        Schema::dropIfExists('detalle_ventas');
    }
};
