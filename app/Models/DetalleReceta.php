<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetalleReceta extends Model
{
    protected $table = 'detalle_recetas';

    protected $fillable = [
        'receta_id',
        'medicamento_id',
        'cantidad_prescrita',
        'indicaciones',
    ];

    protected function casts(): array
    {
        return [
            'cantidad_prescrita' => 'integer',
        ];
    }

    public function receta()
    {
        return $this->belongsTo(Receta::class);
    }

    public function medicamento()
    {
        return $this->belongsTo(Medicamento::class);
    }
}
