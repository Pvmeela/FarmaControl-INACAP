<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Dispensacion extends Model
{
    protected $table = 'dispensaciones';

    protected $fillable = [
        'user_id',
        'paciente_id',
        'receta_id',
        'fecha_dispensacion',
        'observaciones',
    ];

    protected function casts(): array
    {
        return [
            'fecha_dispensacion' => 'datetime',
        ];
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function paciente(): BelongsTo
    {
        return $this->belongsTo(Paciente::class, 'paciente_id');
    }

    public function receta(): BelongsTo
    {
        return $this->belongsTo(Receta::class, 'receta_id');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(DetalleDispensacion::class, 'dispensacion_id');
    }
}
