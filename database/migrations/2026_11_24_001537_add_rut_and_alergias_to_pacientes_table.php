
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pacientes', function (Blueprint $table) {
            $table->string('rut', 12)
                ->nullable()
                ->unique();

            $table->string('estado_alergias', 20)
                ->default('desconocido');

            $table->text('alergias')
                ->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('pacientes', function (Blueprint $table) {
            $table->dropUnique(['rut']);
            $table->dropColumn([
                'rut',
                'estado_alergias',
                'alergias',
            ]);
        });
    }
};
