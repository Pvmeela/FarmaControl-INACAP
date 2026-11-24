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
use Barryvdh\DomPDF\Facade\Pdf;

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
                'precio_venta',
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
            'totales' => $this->calcularTotalesComprobante(
                $venta
            ),
        ]);
    }

    // Visualizar comprobante PDF.
    public function verPdf(Venta $venta)
    {
        return $this->generarPdf($venta)
            ->stream(
                'comprobante-simulado-' . $venta->id . '.pdf'
            )
            ->header('Cache-Control', 'private, no-store');
    }

    // Descargar comprobante PDF.
    public function descargarPdf(Venta $venta)
    {
        return $this->generarPdf($venta)
            ->download(
                'comprobante-simulado-' . $venta->id . '.pdf'
            )
            ->header('Cache-Control', 'private, no-store');
    }

    // Calcular el desglose del IVA.
    private function calcularTotalesComprobante(
        Venta $venta
    ): array {
        $subtotalCentavos = $venta->detalles->sum(
            fn ($detalle) => $this->precioEnCentavos(
                (string) $detalle->subtotal
            )
        );

        $descuentoCentavos = $this->precioEnCentavos(
            (string) ($venta->descuento ?? 0)
        );

        $totalCentavos = $this->precioEnCentavos(
            (string) $venta->total
        );

        // Para la simulación académica, los precios
        // del catálogo incluyen IVA del 19%.
        //
        // El impuesto se desglosa del total registrado,
        // sin modificar los montos históricos.

        $netoCentavos = intdiv(
            ($totalCentavos * 100) + 59,
            119
        );

        $ivaCentavos = $totalCentavos - $netoCentavos;

        return [
            'subtotal' => $this->formatearImporte(
                $subtotalCentavos
            ),

            'descuento' => $this->formatearImporte(
                $descuentoCentavos
            ),

            'neto' => $this->formatearImporte(
                $netoCentavos
            ),

            'iva' => $this->formatearImporte(
                $ivaCentavos
            ),

            'tasa_iva' => 19,

            'total' => $this->formatearImporte(
                $totalCentavos
            ),
        ];
    }

    // Generar documento PDF académico.
    private function generarPdf(Venta $venta)
    {
        $venta->load([
            'usuario',
            'detalles.medicamento',
        ]);

        $totales = $this->calcularTotalesComprobante(
            $venta
        );

        $tituloComprobante =
            $venta->tipo_comprobante === 'factura'
                ? 'FACTURA SIMULADA'
                : 'BOLETA SIMULADA';

        // Obtener logo institucional de INACAP.
        $rutaLogo = public_path(
            'images/logo-inacap.png'
        );

        $logoInacap = null;

        if (
            is_file($rutaLogo) &&
            is_readable($rutaLogo)
        ) {
            $contenidoLogo = file_get_contents($rutaLogo);

            if ($contenidoLogo !== false) {
                $logoInacap = 'data:image/png;base64,'
                    . base64_encode($contenidoLogo);
            }
        }

        return Pdf::loadView('pdf.venta', [
            'venta' => $venta,
            'totales' => $totales,
            'tituloComprobante' => $tituloComprobante,
            'logoInacap' => $logoInacap,
            'fechaGeneracion' => now(),
        ])
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'defaultFont' => 'DejaVu Sans',
                'isRemoteEnabled' => false,
                'isPhpEnabled' => false,
                'isJavascriptEnabled' => false,
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
        ]);

        DB::transaction(function () use ($datos, $request) {

            // Ordenar medicamentos para mantener
            // un bloqueo consistente.
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

                // No permitir medicamentos inactivos.
                if (!$medicamento->activo) {
                    throw ValidationException::withMessages([
                        'items' =>
                            'Uno de los medicamentos seleccionados está inactivo.',
                    ]);
                }

                // Obtener el precio directamente desde MySQL.
                $precio = $medicamento->precio_venta;

                // Validar que exista un precio mayor que cero.
                if (
                    $precio === null ||
                    $this->precioEnCentavos(
                        (string) $precio
                    ) <= 0
                ) {
                    throw ValidationException::withMessages([
                        'items' =>
                            'El medicamento "'
                            . $medicamento->nombre
                            . '" no tiene un precio de venta válido configurado.',
                    ]);
                }

                $precioCentavos = $this->precioEnCentavos(
                    (string) $precio
                );

                // Evitar importes superiores al máximo permitido.
                if (
                    $item['cantidad'] >
                    intdiv(
                        999999999999 - $totalCentavos,
                        $precioCentavos
                    )
                ) {
                    throw ValidationException::withMessages([
                        'items' =>
                            'El total de la venta supera el máximo permitido.',
                    ]);
                }

                $subtotalCentavos =
                    $precioCentavos * $item['cantidad'];

                $totalCentavos += $subtotalCentavos;

                $detalles[] = [
                    'medicamento' => $medicamento,

                    'cantidad' => $item['cantidad'],

                    'precio_unitario' =>
                        $this->formatearImporte(
                            $precioCentavos
                        ),

                    'subtotal' =>
                        $this->formatearImporte(
                            $subtotalCentavos
                        ),
                ];
            }

            // Comprobar stock utilizable antes
            // de registrar la venta.
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
                    ->where(
                        'cantidad_disponible',
                        '>',
                        0
                    )
                    ->orderBy('fecha_vencimiento')
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->get();

                $stockDisponible = $lotes->sum(
                    'cantidad_disponible'
                );

                if (
                    $stockDisponible <
                    $detalle['cantidad']
                ) {
                    throw ValidationException::withMessages([
                        'items' =>
                            'Stock insuficiente para '
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
            // El total registrado incluye IVA bajo
            // el supuesto tributario de la simulación.
            $venta = Venta::create([
                'user_id' => $request->user()->id,

                'fecha_venta' => now(),

                'tipo_comprobante' =>
                    $datos['tipo_comprobante'],

                'convenio' => null,

                'descuento' => 0,

                'total' => $this->formatearImporte(
                    $totalCentavos
                ),
            ]);

            // Guardar detalles y descontar inventario.
            foreach ($detalles as $detalle) {

                // Conservar el precio histórico de la venta.
                DetalleVenta::create([
                    'venta_id' => $venta->id,

                    'medicamento_id' =>
                        $detalle['medicamento']->id,

                    'cantidad' => $detalle['cantidad'],

                    'precio_unitario' =>
                        $detalle['precio_unitario'],

                    'subtotal' => $detalle['subtotal'],
                ]);

                $cantidadPendiente =
                    $detalle['cantidad'];

                // FEFO: descontar primero los lotes
                // vigentes próximos a vencer.
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
                        $stockAnterior -
                        $cantidadRetirada;

                    $lote->cantidad_disponible =
                        $stockPosterior;

                    $lote->save();

                    // Registrar salida en el historial.
                    MovimientoInventario::create([
                        'lote_id' => $lote->id,

                        'user_id' =>
                            $request->user()->id,

                        'tipo' => 'salida',

                        'cantidad' =>
                            $cantidadRetirada,

                        'stock_anterior' =>
                            $stockAnterior,

                        'stock_posterior' =>
                            $stockPosterior,

                        'motivo' =>
                            'Venta simulada #'
                            . $venta->id,
                    ]);

                    $cantidadPendiente -=
                        $cantidadRetirada;
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

    // Convertir importes a unidades de cálculo
    // con dos decimales para mantener precisión.
    private function precioEnCentavos(
        string $precio
    ): int {
        $precio = trim($precio);

        $esNegativo = str_starts_with($precio, '-');

        $precio = ltrim($precio, '-');

        $partes = explode('.', $precio, 2);

        $entero = (int) $partes[0];

        $decimales = substr(
            str_pad(
                $partes[1] ?? '',
                2,
                '0'
            ),
            0,
            2
        );

        $importe =
            ($entero * 100) + (int) $decimales;

        return $esNegativo
            ? -$importe
            : $importe;
    }

    // Formatear importes con dos decimales.
    private function formatearImporte(
        int $centavos
    ): string {
        $signo = $centavos < 0 ? '-' : '';

        $centavos = abs($centavos);

        return $signo . sprintf(
            '%d.%02d',
            intdiv($centavos, 100),
            $centavos % 100
        );
    }
}