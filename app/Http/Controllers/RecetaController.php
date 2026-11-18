<?php

namespace App\Http\Controllers;

use App\Models\DetalleReceta;
use App\Models\Medicamento;
use App\Models\Paciente;
use App\Models\Receta;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class RecetaController extends Controller
{
    public function index()
    {
        $recetas = Receta::with([
            'paciente',
            'detalles.medicamento',
        ])
            ->orderByDesc('fecha_emision')
            ->orderByDesc('id')
            ->get()
            ->map(function ($receta) {
                $receta->vigencia_actual = $this->obtenerVigencia($receta);

                return $receta;
            });

        $pacientes = Paciente::where('activo', true)
            ->orderBy('apellidos')
            ->orderBy('nombres')
            ->get();

        $medicamentos = Medicamento::where('activo', true)
            ->orderBy('nombre')
            ->get();

        return Inertia::render('Recetas/Index', [
            'recetas' => $recetas,
            'pacientes' => $pacientes,
            'medicamentos' => $medicamentos,
        ]);
    }

    public function store(Request $request)
    {
        $datos = $request->validate([
            'codigo_receta' => [
                'required',
                'string',
                'max:30',
                Rule::unique('recetas', 'codigo_receta'),
            ],
            'paciente_id' => [
                'required',
                Rule::exists('pacientes', 'id')
                    ->where('activo', true),
            ],
            'fecha_emision' => [
                'required',
                'date',
                'before_or_equal:today',
            ],
            'fecha_vencimiento' => [
                'required',
                'date',
                'after_or_equal:fecha_emision',
                'after_or_equal:today',
            ],
            'profesional_prescriptor' => [
                'required',
                'string',
                'max:150',
            ],
            'observaciones' => [
                'nullable',
                'string',
                'max:255',
            ],
            'detalles' => [
                'required',
                'array',
                'min:1',
            ],
            'detalles.*.medicamento_id' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('medicamentos', 'id')
                    ->where('activo', true),
            ],
            'detalles.*.cantidad_prescrita' => [
                'required',
                'integer',
                'min:1',
            ],
            'detalles.*.indicaciones' => [
                'nullable',
                'string',
                'max:255',
            ],
        ], [
            'codigo_receta.required' =>
                'Debes ingresar el código de la receta.',
            'codigo_receta.unique' =>
                'Ya existe una receta con ese código.',
            'paciente_id.required' =>
                'Debes seleccionar un paciente.',
            'paciente_id.exists' =>
                'El paciente seleccionado no está disponible.',
            'fecha_emision.required' =>
                'Debes ingresar la fecha de emisión.',
            'fecha_emision.before_or_equal' =>
                'La fecha de emisión no puede ser futura.',
            'fecha_vencimiento.required' =>
                'Debes ingresar la fecha de vencimiento.',
            'fecha_vencimiento.after_or_equal' =>
                'La receta debe tener una fecha de vencimiento válida.',
            'profesional_prescriptor.required' =>
                'Debes ingresar el nombre del profesional prescriptor simulado.',
            'detalles.required' =>
                'Debes agregar al menos un medicamento.',
            'detalles.min' =>
                'Debes agregar al menos un medicamento.',
            'detalles.*.medicamento_id.distinct' =>
                'No puedes repetir el mismo medicamento en una receta.',
            'detalles.*.medicamento_id.exists' =>
                'Uno de los medicamentos no está disponible.',
            'detalles.*.cantidad_prescrita.min' =>
                'La cantidad prescrita debe ser mayor que cero.',
        ]);

        DB::transaction(function () use ($datos) {
            $paciente = Paciente::whereKey($datos['paciente_id'])
                ->lockForUpdate()
                ->first();

            if (!$paciente || !$paciente->activo) {
                throw ValidationException::withMessages([
                    'paciente_id' =>
                        'El paciente ya no está disponible.',
                ]);
            }

            $idsMedicamentos = collect($datos['detalles'])
                ->pluck('medicamento_id')
                ->sort()
                ->values();

            $medicamentosActivos = Medicamento::whereIn(
                'id',
                $idsMedicamentos
            )
                ->where('activo', true)
                ->lockForUpdate()
                ->count();

            if ($medicamentosActivos !== $idsMedicamentos->count()) {
                throw ValidationException::withMessages([
                    'detalles' =>
                        'Uno o más medicamentos ya no están disponibles.',
                ]);
            }

            $receta = Receta::create([
                'codigo_receta' => $datos['codigo_receta'],
                'paciente_id' => $datos['paciente_id'],
                'fecha_emision' => $datos['fecha_emision'],
                'fecha_vencimiento' => $datos['fecha_vencimiento'],
                'profesional_prescriptor' =>
                    $datos['profesional_prescriptor'],
                'estado' => 'vigente',
                'observaciones' =>
                    $datos['observaciones'] ?? null,
            ]);

            foreach ($datos['detalles'] as $detalle) {
                DetalleReceta::create([
                    'receta_id' => $receta->id,
                    'medicamento_id' =>
                        $detalle['medicamento_id'],
                    'cantidad_prescrita' =>
                        $detalle['cantidad_prescrita'],
                    'indicaciones' =>
                        $detalle['indicaciones'] ?? null,
                ]);
            }
        });

        return redirect()
            ->route('recetas.index')
            ->with(
                'success',
                'Receta simulada registrada correctamente.'
            );
    }

    private function obtenerVigencia(Receta $receta): string
    {
        if ($receta->estado === 'anulada') {
            return 'anulada';
        }

        if ($receta->estado === 'utilizada') {
            return 'utilizada';
        }

        $hoy = Carbon::today();

        if ($receta->fecha_emision->gt($hoy)) {
            return 'no_vigente';
        }

        if ($receta->fecha_vencimiento->lt($hoy)) {
            return 'vencida';
        }

        return 'vigente';
    }
}
