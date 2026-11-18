<?php

namespace App\Http\Controllers;

use App\Models\Paciente;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class PacienteController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Consultar pacientes registrados
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        $pacientes = Paciente::orderBy('apellidos')
            ->orderBy('nombres')
            ->get();

        return Inertia::render('Pacientes/Index', [
            'pacientes' => $pacientes,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Registrar un paciente simulado
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        $datos = $request->validate([
            'codigo_paciente' => [
                'required',
                'string',
                'max:30',
                Rule::unique('pacientes', 'codigo_paciente'),
            ],

            'nombres' => [
                'required',
                'string',
                'max:100',
            ],

            'apellidos' => [
                'required',
                'string',
                'max:100',
            ],

            'fecha_nacimiento' => [
                'nullable',
                'date',
                'before_or_equal:today',
            ],
        ], [
            'codigo_paciente.required' =>
                'Debes ingresar un código de paciente.',

            'codigo_paciente.unique' =>
                'Este código de paciente ya está registrado.',

            'nombres.required' =>
                'Debes ingresar los nombres del paciente.',

            'apellidos.required' =>
                'Debes ingresar los apellidos del paciente.',

            'fecha_nacimiento.before_or_equal' =>
                'La fecha de nacimiento no puede ser futura.',
        ]);

        Paciente::create([
            'codigo_paciente' => $datos['codigo_paciente'],
            'nombres' => $datos['nombres'],
            'apellidos' => $datos['apellidos'],
            'fecha_nacimiento' =>
                $datos['fecha_nacimiento'] ?? null,
            'activo' => true,
        ]);

        return redirect()
            ->route('pacientes.index')
            ->with(
                'success',
                'Paciente simulado registrado correctamente.'
            );
    }
}
