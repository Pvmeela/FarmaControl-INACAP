<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Dispensacion extends Model
{
    protected $table = 'dispensaciones';

    protected $fillable = [

        // Información general
        'user_id',
        'paciente_id',
        'receta_id',
        'fecha_dispensacion',
        'observaciones',

        // Antecedentes de alergias
        'estado_alergias',
        'alergias',
        'alergias_revisadas',

        // Programas GES y crónicos
        'programa_id',
        'programa_nombre',
        'programa_tipo',
        'cobertura',
        'cuota_maxima',
        'periodo_dias',

        // Identificación de quien retira
        'tipo_retiro',
        'retirante_nombre',
        'retirante_rut',
        'retirante_parentesco',
        'autorizacion_verificada',

    ];

    protected $casts = [

        'fecha_dispensacion' => 'datetime',

        'alergias_revisadas' => 'boolean',

        'cobertura' => 'decimal:2',

        'cuota_maxima' => 'integer',

        'periodo_dias' => 'integer',

        'autorizacion_verificada' => 'boolean',

    ];

    /*
    |--------------------------------------------------------------------------
    | Usuario responsable
    |--------------------------------------------------------------------------
    */

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'user_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Paciente asociado
    |--------------------------------------------------------------------------
    */

    public function paciente(): BelongsTo
    {
        return $this->belongsTo(
            Paciente::class,
            'paciente_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Receta asociada
    |--------------------------------------------------------------------------
    */

    public function receta(): BelongsTo
    {
        return $this->belongsTo(
            Receta::class,
            'receta_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Programa de salud
    |--------------------------------------------------------------------------
    */

    public function programa(): BelongsTo
    {
        return $this->belongsTo(
            ProgramaSalud::class,
            'programa_id'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Medicamentos dispensados
    |--------------------------------------------------------------------------
    */

    public function detalles(): HasMany
    {
        return $this->hasMany(
            DetalleDispensacion::class,
            'dispensacion_id'
        );
    }
}