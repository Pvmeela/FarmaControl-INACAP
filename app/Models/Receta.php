<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Receta extends Model
{
    protected $table = 'recetas';

    protected $fillable = [
        'codigo_receta',
        'paciente_id',
        'fecha_emision',
        'fecha_vencimiento',
        'profesional_prescriptor',
        'estado',
        'observaciones',
    ];

    protected function casts(): array
    {
        return [
            'fecha_emision' => 'date',
            'fecha_vencimiento' => 'date',
        ];
    }

    public function paciente()
    {
        return $this->belongsTo(Paciente::class);
    }

    public function detalles()
    {
        return $this->hasMany(DetalleReceta::class);
    }
}
