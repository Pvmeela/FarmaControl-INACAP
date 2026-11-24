<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('programas_salud', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 150);
            $table->enum('tipo', ['GES', 'Cronico']);
            $table->text('descripcion')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->unique(['nombre', 'tipo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('programas_salud');
    }
};
