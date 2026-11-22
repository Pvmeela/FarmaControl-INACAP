<?php

namespace App\Http\Controllers;

use App\Models\DetalleDispensacion;
use App\Models\Dispensacion;
use App\Models\Lote;
use App\Models\Medicamento;
use App\Models\MovimientoInventario;
use App\Models\Paciente;
use App\Models\Receta;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class DispensacionController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Consultar dispensaciones
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        $pacientes = Paciente::where('activo', true)
            ->orderBy('nombres')
            ->orderBy('apellidos')
            ->get();

        $recetas = Receta::with([
            'paciente',
            'detalles.medicamento',
        ])
            ->orderByDesc('fecha_emision')
            ->orderByDesc('id')
            ->get();

        $medicamentos = Medicamento::where('activo', true)
            ->orderBy('nombre')
            ->get();

        $dispensaciones = Dispensacion::with([
            'paciente',
            'receta',
            'usuario',
            'detalles.medicamento',
        ])
            ->orderByDesc('fecha_dispensacion')
            ->orderByDesc('id')
            ->get();

        return Inertia::render('Dispensaciones/Index', [
            'pacientes' => $pacientes,
            'recetas' => $recetas,
            'medicamentos' => $medicamentos,
            'dispensaciones' => $dispensaciones,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Registrar dispensación asistencial simulada
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        $datos = $request->validate([
            'paciente_id' => [
                'required',
                'integer',
                'exists:pacientes,id',
            ],
            'receta_id' => [
                'required',
                'integer',
                'exists:recetas,id',
            ],
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
            'observaciones' => [
                'nullable',
                'string',
                'max:255',
            ],
        ]);

        DB::transaction(function () use ($datos, $request) {

            // Bloquear la receta para evitar dispensaciones
            // simultáneas que superen lo prescrito.
            $receta = Receta::whereKey($datos['receta_id'])
                ->lockForUpdate()
                ->firstOrFail();

            if ($receta->paciente_id != $datos['paciente_id']) {
                throw ValidationException::withMessages([
                    'receta_id' =>
                        'La receta no corresponde al paciente seleccionado.',
                ]);
            }

            if ($receta->estado !== 'vigente') {
                throw ValidationException::withMessages([
                    'receta_id' =>
                        'La receta no está vigente para dispensación.',
                ]);
            }

            $hoy = Carbon::today();

            if (
                Carbon::parse($receta->fecha_emision)->startOfDay()->gt($hoy)
                || Carbon::parse($receta->fecha_vencimiento)->startOfDay()->lt($hoy)
            ) {
                throw ValidationException::withMessages([
                    'receta_id' =>
                        'La receta está fuera de su período de vigencia.',
                ]);
            }

            $paciente = Paciente::whereKey($datos['paciente_id'])
                ->firstOrFail();

            if (!$paciente->activo) {
                throw ValidationException::withMessages([
                    'paciente_id' =>
                        'El paciente seleccionado está inactivo.',
                ]);
            }

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

            // Verificar que el medicamento esté prescrito.
            $detalleReceta = $receta->detalles()
                ->where('medicamento_id', $medicamento->id)
                ->first();

            if (!$detalleReceta) {
                throw ValidationException::withMessages([
                    'medicamento_id' =>
                        'Este medicamento no está incluido en la receta.',
                ]);
            }

            // Calcular lo que ya se entregó de esta receta.
            $cantidadEntregada = DetalleDispensacion::query()
                ->where('medicamento_id', $medicamento->id)
                ->whereHas('dispensacion', function ($consulta) use ($receta) {
                    $consulta->where('receta_id', $receta->id);
                })
                ->sum('cantidad');

            $cantidadPendienteReceta =
                $detalleReceta->cantidad_prescrita - $cantidadEntregada;

            if (
                $cantidadPendienteReceta <= 0
                || $datos['cantidad'] > $cantidadPendienteReceta
            ) {
                throw ValidationException::withMessages([
                    'cantidad' =>
                        'La cantidad solicitada supera lo pendiente en la receta. '
                        . 'Pendiente: '
                        . max(0, $cantidadPendienteReceta)
                        . ' unidades.',
                ]);
            }

            // Seleccionar lotes utilizables siguiendo FEFO.
            $lotes = Lote::where(
                'medicamento_id',
                $medicamento->id
            )
                ->whereDate(
                    'fecha_vencimiento',
                    '>',
                    $hoy
                )
                ->where('cantidad_disponible', '>', 0)
                ->orderBy('fecha_vencimiento')
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            $stockDisponible = $lotes->sum('cantidad_disponible');

            if ($stockDisponible < $datos['cantidad']) {
                throw ValidationException::withMessages([
                    'cantidad' =>
                        'Stock insuficiente. Disponible: '
                        . $stockDisponible
                        . ' unidades.',
                ]);
            }

            // Registrar la dispensación.
            $dispensacion = Dispensacion::create([
                'user_id' => $request->user()->id,
                'paciente_id' => $paciente->id,
                'receta_id' => $receta->id,
                'fecha_dispensacion' => now(),
                'observaciones' => $datos['observaciones'] ?? null,
            ]);

            // Registrar el medicamento entregado.
            DetalleDispensacion::create([
                'dispensacion_id' => $dispensacion->id,
                'medicamento_id' => $medicamento->id,
                'cantidad' => $datos['cantidad'],
            ]);

            $cantidadPendiente = $datos['cantidad'];

            // Descontar existencias en orden FEFO.
            foreach ($lotes as $lote) {

                if ($cantidadPendiente <= 0) {
                    break;
                }

                $stockAnterior = $lote->cantidad_disponible;

                $cantidadRetirada = min(
                    $stockAnterior,
                    $cantidadPendiente
                );

                $stockPosterior =
                    $stockAnterior - $cantidadRetirada;

                $lote->cantidad_disponible = $stockPosterior;
                $lote->save();

                MovimientoInventario::create([
                    'lote_id' => $lote->id,
                    'user_id' => $request->user()->id,
                    'tipo' => 'salida',
                    'cantidad' => $cantidadRetirada,
                    'stock_anterior' => $stockAnterior,
                    'stock_posterior' => $stockPosterior,
                    'motivo' =>
                        'Dispensación asistencial simulada #'
                        . $dispensacion->id,
                ]);

                $cantidadPendiente -= $cantidadRetirada;
            }

            // Comprobar si ya se entregaron todos los
            // medicamentos y cantidades de la receta.
            $recetaCompleta = true;

            foreach ($receta->detalles as $detalle) {

                $totalEntregado = DetalleDispensacion::query()
                    ->where('medicamento_id', $detalle->medicamento_id)
                    ->whereHas('dispensacion', function ($consulta) use ($receta) {
                        $consulta->where('receta_id', $receta->id);
                    })
                    ->sum('cantidad');

                if ($totalEntregado < $detalle->cantidad_prescrita) {
                    $recetaCompleta = false;
                    break;
                }
            }

            if ($recetaCompleta) {
                $receta->estado = 'utilizada';
                $receta->save();
            }

        }, 3);

        return redirect()
            ->route('dispensaciones.index')
            ->with(
                'success',
                'Dispensación asistencial simulada registrada correctamente.'
            );
    }
}
