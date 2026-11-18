<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Lote extends Model
{
    protected $table = 'lotes';

    protected $fillable = [
        'medicamento_id',
        'numero_lote',
        'fecha_vencimiento',
        'cantidad_inicial',
        'cantidad_disponible',
        'fecha_recepcion',
    ];

    protected function casts(): array
    {
        return [
            'fecha_vencimiento' => 'date',
            'fecha_recepcion' => 'date',
            'cantidad_inicial' => 'integer',
            'cantidad_disponible' => 'integer',
        ];
    }

    // Cada lote pertenece a un medicamento
    public function medicamento(): BelongsTo
    {
        return $this->belongsTo(
            Medicamento::class,
            'medicamento_id'
        );
    }
}
