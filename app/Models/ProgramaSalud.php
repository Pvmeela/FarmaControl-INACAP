<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ProgramaSalud extends Model
{
    protected $table = 'programas_salud';

    protected $fillable = [
        'nombre',
        'tipo',
        'descripcion',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
        ];
    }

    public function pacientes(): BelongsToMany
    {
        return $this->belongsToMany(
            Paciente::class,
            'paciente_programa',
            'programa_id',
            'paciente_id'
        )
        ->withPivot('fecha_ingreso', 'estado')
        ->withTimestamps();
    }

    public function medicamentos(): BelongsToMany
    {
        return $this->belongsToMany(
            Medicamento::class,
            'programa_medicamento',
            'programa_id',
            'medicamento_id'
        )
        ->withPivot([
            'cantidad_maxima',
            'periodo_dias',
            'cobertura',
            'activo',
        ])
        ->withTimestamps();
    }
}