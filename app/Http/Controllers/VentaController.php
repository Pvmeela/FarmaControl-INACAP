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
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class VentaController extends Controller
{
    // Mostrar el listado de ventas.
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
            ->get([
                'id',
                'nombre',
                'codigo',
            ]);

        return Inertia::render('Ventas/Index', [
            'ventas' => $ventas,
            'medicamentos' => $medicamentos,
        ]);
    }

    // Mostrar boleta o factura simulada.
    public function comprobante(Venta $venta)
    {
        $venta->load([
            'usuario',
            'detalles.medicamento',
        ]);

        return Inertia::render('Ventas/Comprobante', [
            'venta' => $venta,
        ]);
    }

    // Registrar una venta con uno o varios medicamentos.
    public function store(Request $request)
    {
        $datos = $request->validate([
            'tipo_comprobante' => [
                'required',
                Rule::in(['boleta', 'factura']),
            ],

            'items' => [
                'required',
                'array',
                'min:1',
            ],

            'items.*.medicamento_id' => [
                'required',
                'integer',
                'distinct',
                'exists:medicamentos,id',
            ],

            'items.*.cantidad' => [
                'required',
                'integer',
                'min:1',
            ],

            'items.*.precio_unitario' => [
                'required',
                'numeric',
                'min:0',
                'decimal:0,2',
                'max:9999999999.99',
            ],
        ]);

        DB::transaction(function () use ($datos, $request) {

            // Ordenar medicamentos para mantener un bloqueo consistente.
            $items = collect($datos['items'])
                ->sortBy('medicamento_id')
                ->values();

            $detalles = [];
            $totalCentavos = 0;

            // Validar medicamentos y calcular subtotales.
            foreach ($items as $item) {

                $medicamento = Medicamento::whereKey(
                    $item['medicamento_id']
                )
                    ->lockForUpdate()
                    ->firstOrFail();

                if (!$medicamento->activo) {
                    throw ValidationException::withMessages([
                        'items' => 'Uno de los medicamentos seleccionados está inactivo.',
                    ]);
                }

                $precioCentavos = $this->precioEnCentavos(
                    (string) $item['precio_unitario']
                );

                $subtotalCentavos =
                    $precioCentavos * $item['cantidad'];

                $totalCentavos += $subtotalCentavos;

                if ($totalCentavos > 999999999999) {
                    throw ValidationException::withMessages([
                        'items' => 'El total de la venta supera el máximo permitido.',
                    ]);
                }

                $detalles[] = [
                    'medicamento' => $medicamento,
                    'cantidad' => $item['cantidad'],
                    'precio_unitario' => $this->formatearImporte(
                        $precioCentavos
                    ),
                    'subtotal' => $this->formatearImporte(
                        $subtotalCentavos
                    ),
                ];
            }

            // Comprobar stock utilizable antes de registrar la venta.
            foreach ($detalles as &$detalle) {

                $lotes = Lote::where(
                    'medicamento_id',
                    $detalle['medicamento']->id
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

                if ($stockDisponible < $detalle['cantidad']) {
                    throw ValidationException::withMessages([
                        'items' => 'Stock insuficiente para '
                            . $detalle['medicamento']->nombre
                            . '. Disponible: '
                            . $stockDisponible
                            . ' unidades.',
                    ]);
                }

                $detalle['lotes'] = $lotes;
            }

            unset($detalle);

            // Crear la venta simulada.
            $venta = Venta::create([
                'user_id' => $request->user()->id,
                'fecha_venta' => now(),
                'tipo_comprobante' => $datos['tipo_comprobante'],
                'convenio' => null,
                'descuento' => 0,
                'total' => $this->formatearImporte(
                    $totalCentavos
                ),
            ]);

            // Guardar detalles y descontar inventario.
            foreach ($detalles as $detalle) {

                DetalleVenta::create([
                    'venta_id' => $venta->id,
                    'medicamento_id' =>
                        $detalle['medicamento']->id,
                    'cantidad' => $detalle['cantidad'],
                    'precio_unitario' =>
                        $detalle['precio_unitario'],
                    'subtotal' => $detalle['subtotal'],
                ]);

                $cantidadPendiente = $detalle['cantidad'];

                // FEFO: descontar los lotes próximos a vencer.
                foreach ($detalle['lotes'] as $lote) {

                    if ($cantidadPendiente <= 0) {
                        break;
                    }

                    $stockAnterior =
                        $lote->cantidad_disponible;

                    $cantidadRetirada = min(
                        $stockAnterior,
                        $cantidadPendiente
                    );

                    $stockPosterior =
                        $stockAnterior - $cantidadRetirada;

                    $lote->cantidad_disponible =
                        $stockPosterior;

                    $lote->save();

                    // Registrar la salida en el historial.
                    MovimientoInventario::create([
                        'lote_id' => $lote->id,
                        'user_id' => $request->user()->id,
                        'tipo' => 'salida',
                        'cantidad' => $cantidadRetirada,
                        'stock_anterior' => $stockAnterior,
                        'stock_posterior' => $stockPosterior,
                        'motivo' => 'Venta simulada #'
                            . $venta->id,
                    ]);

                    $cantidadPendiente -= $cantidadRetirada;
                }
            }
        }, 3);

        return redirect()
            ->route('ventas.index')
            ->with(
                'success',
                'Venta simulada registrada correctamente.'
            );
    }

    // Convertir precios a centavos para calcular con precisión.
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

    // Formatear importes con dos decimales.
    private function formatearImporte(int $centavos): string
    {
        return sprintf(
            '%d.%02d',
            intdiv($centavos, 100),
            $centavos % 100
        );
    }
}
