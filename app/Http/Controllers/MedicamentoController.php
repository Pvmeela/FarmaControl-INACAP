<?php

namespace App\Http\Controllers;

use App\Models\Medicamento;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class MedicamentoController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Listar medicamentos y calcular stock
    |--------------------------------------------------------------------------
    */

    public function index(): Response
    {
        $hoy = Carbon::today();

        $medicamentos = Medicamento::with('lotes')
            ->orderBy('nombre')
            ->get()
            ->map(function (Medicamento $medicamento) use ($hoy) {

                // Stock físico:
                // Incluye todas las unidades registradas,
                // incluso las que están vencidas.
                $stockFisico = $medicamento->lotes
                    ->sum('cantidad_disponible');

                // Stock utilizable:
                // Solo incluye lotes cuya fecha de
                // vencimiento es posterior a hoy.
                //
                // Los lotes que vencen hoy o antes
                // NO se consideran utilizables.
                $stockUtilizable = $medicamento->lotes
                    ->filter(function ($lote) use ($hoy) {
                        return $lote->fecha_vencimiento
                            && Carbon::parse($lote->fecha_vencimiento)
                                ->startOfDay()
                                ->greaterThan($hoy);
                    })
                    ->sum('cantidad_disponible');

                $stockMinimo = (int) $medicamento->stock_minimo;

                // Determinar el estado del stock.
                if ($stockUtilizable <= 0) {
                    $estadoStock = 'quiebre';
                } elseif ($stockMinimo === 0) {
                    $estadoStock = 'sin_configurar';
                } elseif ($stockUtilizable <= $stockMinimo) {
                    $estadoStock = 'bajo';
                } else {
                    $estadoStock = 'suficiente';
                }

                // Agregar datos calculados para Vue.
                $medicamento->setAttribute(
                    'stock_fisico',
                    (int) $stockFisico
                );

                $medicamento->setAttribute(
                    'stock_utilizable',
                    (int) $stockUtilizable
                );

                $medicamento->setAttribute(
                    'estado_stock',
                    $estadoStock
                );

                // Evitar enviar todos los lotes al catálogo.
                $medicamento->unsetRelation('lotes');

                return $medicamento;
            });

        return Inertia::render('Medicamentos/Index', [
            'medicamentos' => $medicamentos,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Registrar medicamento
    |--------------------------------------------------------------------------
    */

    public function store(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'nombre' => [
                'required',
                'string',
                'max:150',
            ],
            'principio_activo' => [
                'required',
                'string',
                'max:150',
            ],
            'concentracion' => [
                'required',
                'string',
                'max:100',
            ],
            'forma_farmaceutica' => [
                'required',
                'string',
                'max:100',
            ],
            'presentacion' => [
                'required',
                'string',
                'max:150',
            ],
            'codigo' => [
                'required',
                'string',
                'max:50',
                Rule::unique('medicamentos', 'codigo'),
            ],
            'stock_minimo' => [
                'sometimes',
                'required',
                'integer',
                'min:0',
            ],
        ]);

        $datos['activo'] = true;
        $datos['stock_minimo'] = $datos['stock_minimo'] ?? 0;

        Medicamento::create($datos);

        return redirect()
            ->route('medicamentos.index')
            ->with(
                'success',
                'Medicamento registrado correctamente.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Actualizar medicamento
    |--------------------------------------------------------------------------
    */

    public function update(
        Request $request,
        Medicamento $medicamento
    ): RedirectResponse {
        $datos = $request->validate([
            'nombre' => [
                'required',
                'string',
                'max:150',
            ],
            'principio_activo' => [
                'required',
                'string',
                'max:150',
            ],
            'concentracion' => [
                'required',
                'string',
                'max:100',
            ],
            'forma_farmaceutica' => [
                'required',
                'string',
                'max:100',
            ],
            'presentacion' => [
                'required',
                'string',
                'max:150',
            ],
            'codigo' => [
                'required',
                'string',
                'max:50',
                Rule::unique('medicamentos', 'codigo')
                    ->ignore($medicamento->id),
            ],
            'stock_minimo' => [
                'sometimes',
                'required',
                'integer',
                'min:0',
            ],
        ]);

        $medicamento->update($datos);

        return redirect()
            ->route('medicamentos.index')
            ->with(
                'success',
                'Medicamento actualizado correctamente.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Activar o desactivar medicamento
    |--------------------------------------------------------------------------
    */

    public function cambiarEstado(
        Medicamento $medicamento
    ): RedirectResponse {
        $medicamento->update([
            'activo' => !$medicamento->activo,
        ]);

        return redirect()
            ->route('medicamentos.index')
            ->with(
                'success',
                $medicamento->activo
                    ? 'Medicamento activado correctamente.'
                    : 'Medicamento desactivado correctamente.'
            );
    }
}
