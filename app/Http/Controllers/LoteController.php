<?php

namespace App\Http\Controllers;

use App\Models\Lote;
use App\Models\Medicamento;
use App\Models\MovimientoInventario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LoteController extends Controller
{
    /**
     * Mostrar lotes y medicamentos disponibles.
     */
    public function index()
    {
        $lotes = Lote::with('medicamento')
            ->orderBy('fecha_vencimiento')
            ->get();

        $medicamentos = Medicamento::where('activo', true)
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'codigo']);

        return inertia('Lotes/Index', [
            'lotes' => $lotes,
            'medicamentos' => $medicamentos,
        ]);
    }

    /**
     * Registrar un nuevo lote y su movimiento de entrada.
     */
    public function store(Request $request)
    {
        $datos = $request->validate([
            'medicamento_id' => [
                'required',
                'integer',
                'exists:medicamentos,id',
            ],
            'numero_lote' => [
                'required',
                'string',
                'max:100',
            ],
            'fecha_vencimiento' => [
                'required',
                'date',
            ],
            'fecha_recepcion' => [
                'required',
                'date',
                'before_or_equal:today',
            ],
            'cantidad_inicial' => [
                'required',
                'integer',
                'min:1',
            ],
        ]);

        DB::transaction(function () use ($datos, $request) {

            // Bloquear el medicamento durante el registro.
            $medicamento = Medicamento::whereKey(
                $datos['medicamento_id']
            )
                ->lockForUpdate()
                ->firstOrFail();

            // Validar que el medicamento esté activo.
            if (!$medicamento->activo) {
                throw ValidationException::withMessages([
                    'medicamento_id' =>
                        'Debes seleccionar un medicamento activo.',
                ]);
            }

            // Evitar duplicados del mismo lote y medicamento.
            $existe = Lote::where(
                'medicamento_id',
                $datos['medicamento_id']
            )
                ->where(
                    'numero_lote',
                    $datos['numero_lote']
                )
                ->exists();

            if ($existe) {
                throw ValidationException::withMessages([
                    'numero_lote' =>
                        'Este número de lote ya está registrado para el medicamento.',
                ]);
            }

            // Crear el lote con sus existencias iniciales.
            $lote = Lote::create([
                'medicamento_id' => $datos['medicamento_id'],
                'numero_lote' => $datos['numero_lote'],
                'fecha_vencimiento' => $datos['fecha_vencimiento'],
                'fecha_recepcion' => $datos['fecha_recepcion'],
                'cantidad_inicial' => $datos['cantidad_inicial'],
                'cantidad_disponible' => $datos['cantidad_inicial'],
            ]);

            // Registrar automáticamente el movimiento de entrada.
            MovimientoInventario::create([
                'lote_id' => $lote->id,
                'user_id' => $request->user()->id,
                'tipo' => 'entrada',
                'cantidad' => $datos['cantidad_inicial'],
                'stock_anterior' => 0,
                'stock_posterior' => $datos['cantidad_inicial'],
                'motivo' => 'Registro inicial del lote ' .
                    $datos['numero_lote'],
            ]);

        }, 3);

        return redirect()
            ->route('lotes.index')
            ->with(
                'success',
                'Lote registrado correctamente y entrada de inventario registrada.'
            );
    }
}
