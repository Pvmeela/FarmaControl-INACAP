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
        'tipo_receta',
        'formato_receta',
        'rut_prescriptor',
        'fecha_retencion',
        'retenida_por',
    ];

    protected function casts(): array
    {
        return [
            'fecha_emision' => 'date',
            'fecha_vencimiento' => 'date',
            'fecha_retencion' => 'datetime',
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

    public function responsableRetencion()
    {
        return $this->belongsTo(
            User::class,
            'retenida_por'
        );
    }
}