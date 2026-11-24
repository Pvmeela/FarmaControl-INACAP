<?php

namespace App\Http\Controllers;

use App\Models\ProgramaSalud;
use App\Models\Medicamento;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CanastaMedicamentoController extends Controller
{
    public function store(
        Request $request,
        ProgramaSalud $programa
    ) {
        abort_unless($programa->activo, 422,
            'El programa de salud está inactivo.');

        $datos = $request->validate([
            'medicamento_id' => [
                'required',
                'integer',
                Rule::exists('medicamentos', 'id')
                    ->where('activo', true),
            ],
            'cantidad_maxima' => [
                'required',
                'integer',
                'min:1',
            ],
            'periodo_dias' => [
                'required',
                'integer',
                'min:1',
            ],
            'cobertura' => [
                'required',
                'numeric',
                'min:0',
                'max:100',
            ],
        ]);

        if ($programa->medicamentos()
            ->where('medicamentos.id', $datos['medicamento_id'])
            ->exists()) {

            return back()->withErrors([
                'medicamento_id' =>
                    'El medicamento ya pertenece a este programa.',
            ]);
        }

        $programa->medicamentos()->attach(
            $datos['medicamento_id'],
            [
                'cantidad_maxima' => $datos['cantidad_maxima'],
                'periodo_dias' => $datos['periodo_dias'],
                'cobertura' => $datos['cobertura'],
                'activo' => true,
            ]
        );

        return back()->with(
            'success',
            'Medicamento incorporado correctamente a la canasta.'
        );
    }

    public function update(
        Request $request,
        ProgramaSalud $programa,
        Medicamento $medicamento
    ) {
        abort_unless(
            $programa->medicamentos()
                ->where('medicamentos.id', $medicamento->id)
                ->exists(),
            404
        );

        $datos = $request->validate([
            'cantidad_maxima' => [
                'required',
                'integer',
                'min:1',
            ],
            'periodo_dias' => [
                'required',
                'integer',
                'min:1',
            ],
            'cobertura' => [
                'required',
                'numeric',
                'min:0',
                'max:100',
            ],
        ]);

        $programa->medicamentos()->updateExistingPivot(
            $medicamento->id,
            $datos
        );

        return back()->with(
            'success',
            'Canasta de medicamentos actualizada correctamente.'
        );
    }

    public function cambiarEstado(
        Request $request,
        ProgramaSalud $programa,
        Medicamento $medicamento
    ) {
        abort_unless(
            $programa->medicamentos()
                ->where('medicamentos.id', $medicamento->id)
                ->exists(),
            404
        );

        $datos = $request->validate([
            'activo' => ['required', 'boolean'],
        ]);

        if ($datos['activo'] &&
            (!$programa->activo || !$medicamento->activo)) {

            return back()->withErrors([
                'activo' =>
                    'El programa y el medicamento deben estar activos.',
            ]);
        }

        $programa->medicamentos()->updateExistingPivot(
            $medicamento->id,
            [
                'activo' => $datos['activo'],
            ]
        );

        return back()->with(
            'success',
            $datos['activo']
                ? 'Medicamento habilitado en la canasta.'
                : 'Medicamento deshabilitado en la canasta.'
        );
    }
}