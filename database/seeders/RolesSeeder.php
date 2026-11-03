<?php

namespace Database\Seeders;

use App\Models\Rol;
use Illuminate\Database\Seeder;

class RolesSeeder extends Seeder
{
    /**
     * Registrar los roles iniciales de FarmaControl.
     */
    public function run(): void
    {
        $roles = [
            [
                'nombre' => 'Administrador',
                'descripcion' => 'Gestiona usuarios, roles y configuraciones del sistema.',
            ],
            [
                'nombre' => 'Docente',
                'descripcion' => 'Supervisa las actividades académicas y las simulaciones.',
            ],
            [
                'nombre' => 'Estudiante',
                'descripcion' => 'Realiza actividades de simulación farmacéutica.',
            ],
            [
                'nombre' => 'Quimico Farmaceutico',
                'descripcion' => 'Supervisa los procesos farmacéuticos y la dispensación.',
            ],
            [
                'nombre' => 'Tecnico en Farmacia',
                'descripcion' => 'Realiza operaciones de inventario y dispensación autorizadas.',
            ],
        ];

        foreach ($roles as $rol) {
            Rol::updateOrCreate(
                ['nombre' => $rol['nombre']],
                ['descripcion' => $rol['descripcion']]
            );
        }
    }
}
