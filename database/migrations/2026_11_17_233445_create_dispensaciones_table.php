<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dispensaciones', function (Blueprint $table) {
            $table->id();

            // Usuario responsable de la dispensación
            $table->foreignId('user_id')
                ->constrained('users')
                ->restrictOnDelete();

            // Fecha y hora de la dispensación
            $table->dateTime('fecha_dispensacion');

            // Observaciones de la operación
            $table->string('observaciones', 255)->nullable();

            $table->timestamps();

            $table->index('fecha_dispensacion');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dispensaciones');
    }
};
