<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Medicamento extends Model
{
    protected $table = 'medicamentos';

    protected $fillable = [
        'nombre',
        'principio_activo',
        'concentracion',
        'forma_farmaceutica',
        'presentacion',
        'codigo',
        'activo',
        'stock_minimo',
    ];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
            'stock_minimo' => 'integer',
        ];
    }

    /**
     * Un medicamento puede tener varios lotes.
     */
    public function lotes(): HasMany
    {
        return $this->hasMany(
            Lote::class,
            'medicamento_id'
        );
    }
}
