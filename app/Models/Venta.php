<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Venta extends Model
{
    protected $table = 'ventas';

    protected $fillable = [
        'user_id',
        'fecha_venta',
        'total',
    ];

    protected function casts(): array
    {
        return [
            'fecha_venta' => 'datetime',
            'total' => 'decimal:2',
        ];
    }

    // Usuario responsable de la venta.
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Medicamentos incluidos en la venta.
    public function detalles(): HasMany
    {
        return $this->hasMany(DetalleVenta::class, 'venta_id');
    }
}
