<?php

namespace Database\Seeders;

use App\Models\Rol;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdministradorSeeder extends Seeder
{
    public function run(): void
    {
        $rol = Rol::where('nombre', 'Administrador')->firstOrFail();

        $email = 'admin@farmacontrol.test';

        if (User::where('email', $email)->exists()) {
            $this->command->info(
                'El administrador de prueba ya existe.'
            );

            return;
        }

        $password = Str::random(20);

        User::create([
            'name' => 'Administrador FarmaControl',
            'email' => $email,
            'password' => Hash::make($password),
            'rol_id' => $rol->id,
        ]);

        $this->command->info(
            'Administrador de prueba creado correctamente.'
        );

        $this->command->info(
            'Correo: ' . $email
        );

        $this->command->info(
            'Contraseña temporal: ' . $password
        );
    }
}
