<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Inicializar los datos principales de FarmaControl.
     */
    public function run(): void
    {
        $this->call([
            RolesSeeder::class,
            AdministradorSeeder::class,
        ]);
    }
}