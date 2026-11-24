<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('programa_medicamento', function (Blueprint $table) {
            $table->id();

            $table->foreignId('programa_id')
                ->constrained('programas_salud')
                ->restrictOnDelete();

            $table->foreignId('medicamento_id')
                ->constrained('medicamentos')
                ->restrictOnDelete();

            $table->unsignedInteger('cantidad_maxima');

            $table->unsignedInteger('periodo_dias')
                ->default(30);

            $table->decimal('cobertura', 5, 2)
                ->default(100);

            $table->boolean('activo')
                ->default(true);

            $table->timestamps();

            $table->unique([
                'programa_id',
                'medicamento_id'
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('programa_medicamento');
    }
};