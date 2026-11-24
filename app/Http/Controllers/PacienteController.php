<?php

namespace App\Http\Controllers;

use App\Models\Paciente;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class PacienteController extends Controller
{
    // Consultar pacientes.
    public function index()
    {
        $pacientes = Paciente::orderBy('apellidos')
            ->orderBy('nombres')
            ->get();

        return Inertia::render('Pacientes/Index', [
            'pacientes' => $pacientes,
        ]);
    }

    // Registrar un nuevo paciente.
    public function store(Request $request)
    {
        $datos = $this->validarPaciente($request);

        Paciente::create([
            ...$datos,
            'activo' => true,
        ]);

        return redirect()
            ->route('pacientes.index')
            ->with(
                'success',
                'Paciente simulado registrado correctamente.'
            );
    }

    // Actualizar los datos de un paciente.
    public function update(Request $request, Paciente $paciente)
    {
        $datos = $this->validarPaciente($request, $paciente);

        $paciente->update($datos);

        return redirect()
            ->route('pacientes.index')
            ->with(
                'success',
                'Paciente actualizado correctamente.'
            );
    }

    // Activar o desactivar un paciente.
    public function cambiarEstado(Request $request, Paciente $paciente)
    {
        $datos = $request->validate([
            'activo' => ['required', 'boolean'],
        ]);

        $paciente->update([
            'activo' => $datos['activo'],
        ]);

        return redirect()
            ->route('pacientes.index')
            ->with(
                'success',
                $paciente->activo
                    ? 'Paciente reactivado correctamente.'
                    : 'Paciente desactivado correctamente.'
            );
    }

    // Validación para registrar y editar pacientes.
    private function validarPaciente(
        Request $request,
        ?Paciente $paciente = null
    ): array {
        if (is_string($request->input('rut'))) {
            $rut = strtoupper(
                preg_replace('/[\.\-\s]/', '', $request->input('rut'))
            );

            if (preg_match('/^(\d{1,8})([\dK])$/', $rut, $partes)) {
                $numero = ltrim($partes[1], '0');
                $rut = ($numero ?: '0') . '-' . $partes[2];
            }

            $request->merge([
                'rut' => $rut === '' ? null : $rut,
            ]);
        }

        $codigoUnico = Rule::unique(
            'pacientes',
            'codigo_paciente'
        );

        $rutUnico = Rule::unique('pacientes', 'rut');

        // Al editar, excluir al paciente actual
        // de la validación de registros duplicados.
        if ($paciente) {
            $codigoUnico->ignore($paciente->id);
            $rutUnico->ignore($paciente->id);
        }

        $datos = $request->validate([
            'codigo_paciente' => [
                'required',
                'string',
                'max:30',
                $codigoUnico,
            ],

            'rut' => [
                'bail',
                'nullable',
                'string',
                'max:12',
                $rutUnico,
                function ($attribute, $value, $fail) {
                    if (!$this->rutValido($value)) {
                        $fail('El RUT ingresado no es válido.');
                    }
                },
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

            'estado_alergias' => [
                'required',
                Rule::in([
                    'desconocido',
                    'sin_alergias',
                    'con_alergias',
                ]),
            ],

            'alergias' => [
                'nullable',
                'required_if:estado_alergias,con_alergias',
                'string',
                'max:2000',
            ],

        ], [
            'codigo_paciente.required' =>
                'Debes ingresar un código de paciente.',

            'codigo_paciente.unique' =>
                'Este código de paciente ya está registrado.',

            'rut.unique' =>
                'Este RUT ya está registrado.',

            'nombres.required' =>
                'Debes ingresar los nombres del paciente.',

            'apellidos.required' =>
                'Debes ingresar los apellidos del paciente.',

            'fecha_nacimiento.before_or_equal' =>
                'La fecha de nacimiento no puede ser futura.',

            'estado_alergias.required' =>
                'Debes indicar el estado de alergias.',

            'estado_alergias.in' =>
                'El estado de alergias seleccionado no es válido.',

            'alergias.required_if' =>
                'Debes especificar las alergias conocidas.',
        ]);

        $datos['rut'] = $datos['rut'] ?? null;

        $datos['fecha_nacimiento'] =
            $datos['fecha_nacimiento'] ?? null;

        $datos['alergias'] =
            $datos['estado_alergias'] === 'con_alergias'
                ? ($datos['alergias'] ?? null)
                : null;

        return $datos;
    }

    // Validar el dígito verificador del RUT chileno.
    private function rutValido(string $rut): bool
    {
        $rut = strtoupper(
            preg_replace('/[\.\-\s]/', '', $rut)
        );

        if (!preg_match('/^(\d{1,8})([\dK])$/', $rut, $partes)) {
            return false;
        }

        $numero = $partes[1];
        $verificador = $partes[2];

        if ((int) $numero === 0) {
            return false;
        }

        $suma = 0;
        $multiplicador = 2;

        for ($i = strlen($numero) - 1; $i >= 0; $i--) {
            $suma += (int) $numero[$i] * $multiplicador;

            $multiplicador =
                $multiplicador === 7
                    ? 2
                    : $multiplicador + 1;
        }

        $resultado = 11 - ($suma % 11);

        $esperado = match ($resultado) {
            11 => '0',
            10 => 'K',
            default => (string) $resultado,
        };

        return $verificador === $esperado;
    }
}
