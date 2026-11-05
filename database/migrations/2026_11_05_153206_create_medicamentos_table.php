<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medicamentos', function (Blueprint $table) {
            $table->id();

            $table->string('nombre', 150);
            $table->string('principio_activo', 150);
            $table->string('concentracion', 100);
            $table->string('forma_farmaceutica', 100);
            $table->string('presentacion', 150);

            $table->string('codigo', 50)->unique();

            $table->boolean('activo')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medicamentos');
    }
};
