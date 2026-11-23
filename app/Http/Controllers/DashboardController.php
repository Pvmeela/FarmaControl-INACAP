<?php

namespace App\Http\Controllers;

use App\Models\Medicamento;
use App\Models\Lote;
use Carbon\Carbon;
use Inertia\Inertia;

class DashboardController extends Controller
{
    /**
     * Dashboard principal de FarmaControl.
     * Indicadores y alertas de inventario.
     */
    public function index()
    {
        // Fecha actual y límite de alerta de 30 días.
        $hoy = Carbon::today();
        $limite = $hoy->copy()->addDays(30);

        // Medicamentos activos con sus lotes.
        $medicamentos = Medicamento::with('lotes')
            ->where('activo', true)
            ->get();

        $stockBajo = 0;
        $quiebresStock = 0;

        foreach ($medicamentos as $medicamento) {

            /*
             * Calcular stock utilizable.
             *
             * Se incluyen los lotes que vencen hoy
             * o en una fecha posterior.
             *
             * Se excluyen los lotes ya vencidos.
             */
            $stockUtilizable = $medicamento->lotes
                ->filter(function ($lote) use ($hoy) {
                    return $lote->fecha_vencimiento
                        && Carbon::parse(
                            $lote->fecha_vencimiento
                        )->startOfDay()->greaterThanOrEqualTo($hoy);
                })
                ->sum('cantidad_disponible');

            $stockMinimo = (int) $medicamento->stock_minimo;

            // Medicamentos sin existencias utilizables.
            if ($stockUtilizable <= 0) {
                $quiebresStock++;
            }

            // Medicamentos con stock bajo.
            elseif (
                $stockMinimo > 0 &&
                $stockUtilizable <= $stockMinimo
            ) {
                $stockBajo++;
            }
        }

        /*
         * Lotes próximos a vencer.
         *
         * Incluye los que vencen hoy o durante
         * los próximos 30 días.
         *
         * Solo se cuentan lotes con existencias.
         */
        $proximosVencer = Lote::whereDate(
                'fecha_vencimiento',
                '>=',
                $hoy->toDateString()
            )
            ->whereDate(
                'fecha_vencimiento',
                '<=',
                $limite->toDateString()
            )
            ->where('cantidad_disponible', '>', 0)
            ->count();

        /*
         * Lotes vencidos.
         *
         * Solo se consideran fechas anteriores a hoy
         * y lotes que todavía tienen existencias.
         */
        $lotesVencidos = Lote::whereDate(
                'fecha_vencimiento',
                '<',
                $hoy->toDateString()
            )
            ->where('cantidad_disponible', '>', 0)
            ->count();

        // Enviar alertas al Dashboard de Vue.
        return Inertia::render('Dashboard', [
            'alertas' => [
                'stock_bajo' => $stockBajo,
                'quiebres_stock' => $quiebresStock,
                'proximos_vencer' => $proximosVencer,
                'lotes_vencidos' => $lotesVencidos,
            ],
        ]);
    }
}
