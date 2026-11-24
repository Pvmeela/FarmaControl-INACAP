<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Medicamento extends Model
{
    protected $fillable = [
        'nombre',
        'principio_activo',
        'concentracion',
        'forma_farmaceutica',
        'presentacion',
        'codigo',
        'activo',
        'stock_minimo',
        'precio_venta',
        'es_controlado',
        'tipo_control',
        'requiere_receta',
        'tipo_receta',
    ];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
            'stock_minimo' => 'integer',
            'precio_venta' => 'decimal:2',
            'es_controlado' => 'boolean',
            'requiere_receta' => 'boolean',
        ];
    }

    public function lotes(): HasMany
    {
        return $this->hasMany(Lote::class);
    }

    public function programas(): BelongsToMany
    {
        return $this->belongsToMany(
            ProgramaSalud::class,
            'programa_medicamento',
            'medicamento_id',
            'programa_id'
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