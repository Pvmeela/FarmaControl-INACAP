<?php

namespace App\Http\Controllers;

use App\Models\Venta;
use App\Models\DetalleVenta;
use App\Models\Medicamento;
use App\Models\Lote;
use App\Models\MovimientoInventario;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class VentaController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Consultar ventas
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        $ventas = Venta::with([
            'usuario',
            'detalles.medicamento',
        ])
            ->orderByDesc('fecha_venta')
            ->orderByDesc('id')
            ->get();

        $medicamentos = Medicamento::where('activo', true)
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'codigo']);

        return Inertia::render('Ventas/Index', [
            'ventas' => $ventas,
            'medicamentos' => $medicamentos,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Registrar una venta simulada
    |--------------------------------------------------------------------------
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
            'precio_unitario' => [
                'required',
                'numeric',
                'min:0',
                'decimal:0,2',
                'max:9999999999.99',
            ],
        ]);

        DB::transaction(function () use ($datos, $request) {

            // Bloquear el medicamento para evitar
            // descuentos simultáneos inconsistentes.
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

            // Seleccionar lotes utilizables en orden FEFO.
            // Los lotes que vencen hoy o antes se excluyen.
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

            $stockDisponible = $lotes->sum(
                'cantidad_disponible'
            );

            if ($stockDisponible < $datos['cantidad']) {
                throw ValidationException::withMessages([
                    'cantidad' =>
                        'Stock insuficiente. Disponible: ' .
                        $stockDisponible . ' unidades.',
                ]);
            }

            // Calcular importes en centavos para evitar
            // errores de precisión con números decimales.
            $precioCentavos = $this->precioEnCentavos(
                (string) $datos['precio_unitario']
            );

            $totalCentavos = $precioCentavos * $datos['cantidad'];

            if ($totalCentavos > 999999999999) {
                throw ValidationException::withMessages([
                    'cantidad' =>
                        'El total de la venta supera el máximo permitido.',
                ]);
            }

            $precioUnitario = $this->formatearImporte(
                $precioCentavos
            );

            $total = $this->formatearImporte(
                $totalCentavos
            );

            // Crear venta simulada.
            $venta = Venta::create([
                'user_id' => $request->user()->id,
                'fecha_venta' => now(),
                'total' => $total,
            ]);

            // Registrar el medicamento vendido.
            DetalleVenta::create([
                'venta_id' => $venta->id,
                'medicamento_id' => $medicamento->id,
                'cantidad' => $datos['cantidad'],
                'precio_unitario' => $precioUnitario,
                'subtotal' => $total,
            ]);

            $cantidadPendiente = $datos['cantidad'];

            // Descontar unidades siguiendo FEFO.
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

                $lote->cantidad_disponible = $stockPosterior;
                $lote->save();

                // Registrar cada descuento para trazabilidad.
                MovimientoInventario::create([
                    'lote_id' => $lote->id,
                    'user_id' => $request->user()->id,
                    'tipo' => 'salida',
                    'cantidad' => $cantidadRetirada,
                    'stock_anterior' => $stockAnterior,
                    'stock_posterior' => $stockPosterior,
                    'motivo' => 'Venta simulada #' . $venta->id,
                ]);

                $cantidadPendiente -= $cantidadRetirada;
            }
        }, 3);

        return redirect()
            ->route('ventas.index')
            ->with(
                'success',
                'Venta simulada registrada correctamente.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Funciones auxiliares para calcular importes
    |--------------------------------------------------------------------------
    */

    private function precioEnCentavos(string $precio): int
    {
        $partes = explode('.', $precio, 2);

        $entero = (int) $partes[0];

        $decimales = str_pad(
            $partes[1] ?? '',
            2,
            '0'
        );

        return ($entero * 100) + (int) $decimales;
    }

    private function formatearImporte(int $centavos): string
    {
        return sprintf(
            '%d.%02d',
            intdiv($centavos, 100),
            $centavos % 100
        );
    }
}
