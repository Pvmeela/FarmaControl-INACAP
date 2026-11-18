<?php

namespace App\Http\Controllers;

use App\Models\Lote;
use App\Models\Medicamento;
use App\Models\MovimientoInventario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class MovimientoInventarioController extends Controller
{
    /**
     * Mostrar el historial de movimientos.
     */
    public function index()
    {
        $movimientos = MovimientoInventario::with([
            'lote.medicamento',
            'usuario',
        ])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();

        $medicamentos = Medicamento::where('activo', true)
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'codigo']);

        return inertia('Movimientos/Index', [
            'movimientos' => $movimientos,
            'medicamentos' => $medicamentos,
        ]);
    }

    /**
     * Registrar una salida de inventario aplicando FEFO.
     */
    public function store(Request $request)
    {
        $datos = $request->validate([
            'medicamento_id' => [
                'required',
                'integer',
                'exists:medicamentos,id',
            ],
            'cantidad' => [
                'required',
                'integer',
                'min:1',
            ],
            'motivo' => [
                'required',
                'string',
                'max:255',
            ],
        ]);

        DB::transaction(function () use ($datos, $request) {

            // Bloquear el medicamento para evitar
            // salidas simultáneas inconsistentes.
            $medicamento = Medicamento::whereKey(
                $datos['medicamento_id']
            )
                ->lockForUpdate()
                ->firstOrFail();

            if (!$medicamento->activo) {
                throw ValidationException::withMessages([
                    'medicamento_id' =>
                        'El medicamento seleccionado está inactivo.',
                ]);
            }

            // FEFO:
            // Solo utilizar lotes con vencimiento
            // posterior a la fecha actual.
            //
            // Los lotes que vencen hoy o antes
            // quedan excluidos de las salidas.
            $lotes = Lote::where(
                'medicamento_id',
                $medicamento->id
            )
                ->whereDate(
                    'fecha_vencimiento',
                    '>',
                    Carbon::today()
                )
                ->where('cantidad_disponible', '>', 0)
                ->orderBy('fecha_vencimiento')
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            // Calcular el stock realmente utilizable.
            $stockDisponible = $lotes->sum(
                'cantidad_disponible'
            );

            // Impedir salidas superiores al stock.
            if ($stockDisponible < $datos['cantidad']) {
                throw ValidationException::withMessages([
                    'cantidad' =>
                        'Stock insuficiente. Disponible: ' .
                        $stockDisponible . ' unidades.',
                ]);
            }

            $cantidadPendiente = $datos['cantidad'];

            // Descontar primero los lotes que
            // tienen el vencimiento más cercano.
            foreach ($lotes as $lote) {

                if ($cantidadPendiente <= 0) {
                    break;
                }

                $stockAnterior = $lote->cantidad_disponible;

                $cantidadRetirada = min(
                    $stockAnterior,
                    $cantidadPendiente
                );

                $stockPosterior = (
                    $stockAnterior - $cantidadRetirada
                );

                // Actualizar existencias del lote.
                $lote->cantidad_disponible = $stockPosterior;
                $lote->save();

                // Registrar trazabilidad del movimiento.
                MovimientoInventario::create([
                    'lote_id' => $lote->id,
                    'user_id' => $request->user()->id,
                    'tipo' => 'salida',
                    'cantidad' => $cantidadRetirada,
                    'stock_anterior' => $stockAnterior,
                    'stock_posterior' => $stockPosterior,
                    'motivo' => $datos['motivo'],
                ]);

                $cantidadPendiente -= $cantidadRetirada;
            }
        }, 3);

        return redirect()
            ->route('movimientos.index')
            ->with(
                'success',
                'Salida de inventario registrada correctamente.'
            );
    }
}
