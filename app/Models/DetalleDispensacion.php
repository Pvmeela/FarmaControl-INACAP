<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetalleDispensacion extends Model
{
    protected $table = 'detalle_dispensaciones';

    protected $fillable = [
        'dispensacion_id',
        'medicamento_id',
        'cantidad',
    ];

    protected function casts(): array
    {
        return [
            'cantidad' => 'integer',
        ];
    }

    // Dispensación a la que pertenece
    public function dispensacion()
    {
        return $this->belongsTo(Dispensacion::class);
    }

    // Medicamento dispensado
    public function medicamento()
    {
        return $this->belongsTo(Medicamento::class);
    }
}
