<?php

namespace App\Http\Controllers;

use App\Models\ProgramaSalud;
use App\Models\Paciente;
use App\Models\Medicamento;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class ProgramaSaludController extends Controller
{
    public function index()
    {
        $programas = ProgramaSalud::with([
            'pacientes',
            'medicamentos'
        ])
            ->orderBy('nombre')
            ->get();

        $pacientes = Paciente::where('activo', true)
            ->orderBy('nombres')
            ->get();

        $medicamentos = Medicamento::where('activo', true)
            ->orderBy('nombre')
            ->get();

        return Inertia::render('ProgramasSalud/Index', [
            'programas' => $programas,
            'pacientes' => $pacientes,
            'medicamentos' => $medicamentos,
        ]);
    }

    public function store(Request $request)
    {
        $datos = $request->validate([
            'nombre' => [
                'required',
                'string',
                'max:150',
                Rule::unique('programas_salud', 'nombre')
                    ->where('tipo', $request->tipo),
            ],
            'tipo' => [
                'required',
                Rule::in(['GES', 'Cronico']),
            ],
            'descripcion' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ]);

        ProgramaSalud::create([
            'nombre' => $datos['nombre'],
            'tipo' => $datos['tipo'],
            'descripcion' => $datos['descripcion'] ?? null,
            'activo' => true,
        ]);

        return back()->with(
            'success',
            'Programa de salud registrado correctamente.'
        );
    }

    public function asignar(Request $request)
    {
        $datos = $request->validate([
            'paciente_id' => [
                'required',
                'integer',
                Rule::exists('pacientes', 'id')
                    ->where('activo', true),
            ],
            'programa_id' => [
                'required',
                'integer',
                Rule::exists('programas_salud', 'id')
                    ->where('activo', true),
            ],
            'fecha_ingreso' => [
                'required',
                'date',
                'before_or_equal:today',
            ],
        ]);

        $programa = ProgramaSalud::findOrFail(
            $datos['programa_id']
        );

        if ($programa->pacientes()
            ->where('pacientes.id', $datos['paciente_id'])
            ->exists()) {

            return back()->withErrors([
                'paciente_id' =>
                    'El paciente ya pertenece a este programa.',
            ]);
        }

        $programa->pacientes()->attach(
            $datos['paciente_id'],
            [
                'fecha_ingreso' => $datos['fecha_ingreso'],
                'estado' => 'Activo',
            ]
        );

        return back()->with(
            'success',
            'Paciente incorporado correctamente al programa.'
        );
    }

    public function cambiarEstado(
        Request $request,
        ProgramaSalud $programa,
        Paciente $paciente
    ) {
        $datos = $request->validate([
            'estado' => [
                'required',
                Rule::in(['Activo', 'Inactivo']),
            ],
        ]);

        abort_unless(
            $programa->pacientes()
                ->where('pacientes.id', $paciente->id)
                ->exists(),
            404
        );

        if (
            $datos['estado'] === 'Activo' &&
            (!$paciente->activo || !$programa->activo)
        ) {
            return back()->withErrors([
                'estado' =>
                    'El paciente y el programa deben estar activos.',
            ]);
        }

        $programa->pacientes()->updateExistingPivot(
            $paciente->id,
            [
                'estado' => $datos['estado'],
            ]
        );

        return back()->with(
            'success',
            'Estado del beneficiario actualizado correctamente.'
        );
    }
}