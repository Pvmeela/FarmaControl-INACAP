<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Paciente extends Model
{
    protected $table = 'pacientes';

    protected $fillable = [
        'codigo_paciente',
        'rut',
        'nombres',
        'apellidos',
        'fecha_nacimiento',
        'estado_alergias',
        'alergias',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'fecha_nacimiento' => 'date',
            'activo' => 'boolean',
        ];
    }

    public function programas()
    {
        return $this->belongsToMany(
            ProgramaSalud::class,
            'paciente_programa',
            'paciente_id',
            'programa_id'
        )
        ->withPivot('fecha_ingreso', 'estado')
        ->withTimestamps();
    }
}
