<?php

namespace App\Http\Controllers;

use App\Models\Medicamento;
use App\Models\Lote;
use Carbon\Carbon;
use Inertia\Inertia;

class DashboardController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Dashboard principal - Alertas de inventario
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        // Fecha actual del sistema.
        $hoy = Carbon::today()->toDateString();

        // Límite para alertas de vencimiento: 30 días.
        $limite = Carbon::today()
            ->addDays(30)
            ->toDateString();

        // Obtener medicamentos activos con sus lotes.
        $medicamentos = Medicamento::with('lotes')
            ->where('activo', true)
            ->get();

        $stockBajo = 0;
        $quiebresStock = 0;

        foreach ($medicamentos as $medicamento) {

            /*
            |--------------------------------------------------------------------------
            | Calcular stock utilizable
            |--------------------------------------------------------------------------
            |
            | Solo se consideran lotes cuya fecha de vencimiento
            | sea posterior a hoy.
            |
            | Los lotes que vencen hoy o antes quedan excluidos.
            |
            */

            $stockUtilizable = $medicamento->lotes
                ->filter(function ($lote) use ($hoy) {
                    return $lote->fecha_vencimiento
                        && Carbon::parse($lote->fecha_vencimiento)
                            ->toDateString() > $hoy;
                })
                ->sum('cantidad_disponible');

            $stockMinimo = (int) $medicamento->stock_minimo;

            /*
            |--------------------------------------------------------------------------
            | Quiebres de stock
            |--------------------------------------------------------------------------
            */

            if ($stockUtilizable <= 0) {
                $quiebresStock++;
            }

            /*
            |--------------------------------------------------------------------------
            | Stock bajo
            |--------------------------------------------------------------------------
            |
            | Debe existir stock utilizable.
            | El mínimo debe estar configurado.
            |
            */

            elseif (
                $stockMinimo > 0 &&
                $stockUtilizable <= $stockMinimo
            ) {
                $stockBajo++;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Lotes próximos a vencer
        |--------------------------------------------------------------------------
        |
        | Vencen después de hoy y dentro de 30 días.
        | Solo se cuentan lotes con existencias.
        |
        */

        $proximosVencer = Lote::whereDate(
                'fecha_vencimiento',
                '>',
                $hoy
            )
            ->whereDate(
                'fecha_vencimiento',
                '<=',
                $limite
            )
            ->where('cantidad_disponible', '>', 0)
            ->count();

        /*
        |--------------------------------------------------------------------------
        | Lotes vencidos
        |--------------------------------------------------------------------------
        |
        | Incluye lotes que vencen hoy o antes.
        | Solo se cuentan lotes con existencias físicas.
        |
        */

        $lotesVencidos = Lote::whereDate(
                'fecha_vencimiento',
                '<=',
                $hoy
            )
            ->where('cantidad_disponible', '>', 0)
            ->count();

        /*
        |--------------------------------------------------------------------------
        | Enviar indicadores al dashboard de Vue
        |--------------------------------------------------------------------------
        */

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
