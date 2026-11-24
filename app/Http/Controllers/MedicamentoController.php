<?php

namespace App\Http\Controllers;

use App\Models\Medicamento;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
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

                $stockFisico = $medicamento->lotes
                    ->sum('cantidad_disponible');

                $stockUtilizable = $medicamento->lotes
                    ->filter(function ($lote) use ($hoy) {
                        return $lote->fecha_vencimiento
                            && Carbon::parse($lote->fecha_vencimiento)
                                ->startOfDay()
                                ->greaterThan($hoy);
                    })
                    ->sum('cantidad_disponible');

                $stockMinimo = (int) $medicamento->stock_minimo;

                if ($stockUtilizable <= 0) {
                    $estadoStock = 'quiebre';
                } elseif ($stockMinimo === 0) {
                    $estadoStock = 'sin_configurar';
                } elseif ($stockUtilizable <= $stockMinimo) {
                    $estadoStock = 'bajo';
                } else {
                    $estadoStock = 'suficiente';
                }

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

                $medicamento->unsetRelation('lotes');

                return $medicamento;
            });

        return Inertia::render('Medicamentos/Index', [
            'medicamentos' => $medicamentos,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Validar información del medicamento
    |--------------------------------------------------------------------------
    */

    private function validarDatos(
        Request $request,
        ?Medicamento $medicamento = null
    ): array {

        $reglaCodigo = Rule::unique(
            'medicamentos',
            'codigo'
        );

        if ($medicamento) {
            $reglaCodigo->ignore($medicamento->id);
        }

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
                $reglaCodigo,
            ],

            'stock_minimo' => [
                'sometimes',
                'required',
                'integer',
                'min:0',
            ],

            'precio_venta' => [
                'nullable',
                'numeric',
                'min:0',
                'decimal:0,2',
                'max:9999999999.99',
            ],

            'es_controlado' => [
                'sometimes',
                'required',
                'boolean',
            ],

            'tipo_control' => [
                'nullable',
                Rule::in([
                    'estupefaciente',
                    'psicotropico',
                ]),
            ],

            'requiere_receta' => [
                'sometimes',
                'required',
                'boolean',
            ],

            'tipo_receta' => [
                'nullable',
                Rule::in([
                    'simple',
                    'retenida',
                    'cheque',
                ]),
            ],

        ], [

            'nombre.required' =>
                'Debes ingresar el nombre del medicamento.',

            'codigo.unique' =>
                'Ya existe un medicamento con este código.',

            'tipo_control.in' =>
                'La clasificación seleccionada no es válida.',

            'tipo_receta.in' =>
                'El tipo de receta seleccionado no es válido.',

            'stock_minimo.min' =>
                'El stock mínimo no puede ser negativo.',

            'precio_venta.min' =>
                'El precio no puede ser negativo.',

        ]);

        /*
        |--------------------------------------------------------------------------
        | Conservar datos existentes durante la edición
        |--------------------------------------------------------------------------
        */

        $esControlado = (bool) (
            $datos['es_controlado']
            ?? $medicamento?->es_controlado
            ?? false
        );

        $requiereReceta = (bool) (
            $datos['requiere_receta']
            ?? $medicamento?->requiere_receta
            ?? false
        );

        $tipoControl = array_key_exists(
            'tipo_control',
            $datos
        )
            ? $datos['tipo_control']
            : $medicamento?->tipo_control;

        $tipoReceta = array_key_exists(
            'tipo_receta',
            $datos
        )
            ? $datos['tipo_receta']
            : $medicamento?->tipo_receta;

        /*
        |--------------------------------------------------------------------------
        | Validar medicamentos controlados
        |--------------------------------------------------------------------------
        */

        if ($esControlado) {

            if (!$tipoControl) {

                throw ValidationException::withMessages([
                    'tipo_control' =>
                        'Debes indicar si corresponde a un estupefaciente o psicotrópico.',
                ]);
            }

            $requiereReceta = true;

            if (!in_array(
                $tipoReceta,
                ['retenida', 'cheque'],
                true
            )) {

                throw ValidationException::withMessages([
                    'tipo_receta' =>
                        'Un medicamento controlado requiere una receta retenida o cheque en esta simulación.',
                ]);
            }

        } else {

            $tipoControl = null;
        }

        /*
        |--------------------------------------------------------------------------
        | Validar condición de receta
        |--------------------------------------------------------------------------
        */

        if ($requiereReceta && !$tipoReceta) {

            throw ValidationException::withMessages([
                'tipo_receta' =>
                    'Debes seleccionar el tipo de receta requerida.',
            ]);
        }

        if (!$requiereReceta) {
            $tipoReceta = null;
        }

        $datos['es_controlado'] = $esControlado;

        $datos['tipo_control'] = $tipoControl;

        $datos['requiere_receta'] = $requiereReceta;

        $datos['tipo_receta'] = $tipoReceta;

        return $datos;
    }

    /*
    |--------------------------------------------------------------------------
    | Registrar medicamento
    |--------------------------------------------------------------------------
    */

    public function store(
        Request $request
    ): RedirectResponse {

        $datos = $this->validarDatos($request);

        $datos['activo'] = true;

        $datos['stock_minimo'] =
            $datos['stock_minimo'] ?? 0;

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

        $datos = $this->validarDatos(
            $request,
            $medicamento
        );

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