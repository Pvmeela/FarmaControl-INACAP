<?php

namespace App\Http\Controllers;

use App\Models\DetalleReceta;
use App\Models\Medicamento;
use App\Models\Paciente;
use App\Models\Receta;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class RecetaController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Consultar recetas
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        $recetas = Receta::with([
            'paciente',
            'detalles.medicamento',
            'responsableRetencion:id,name',
        ])
            ->orderByDesc('fecha_emision')
            ->orderByDesc('id')
            ->get()
            ->map(function ($receta) {

                $receta->vigencia_actual =
                    $this->obtenerVigencia($receta);

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

    /*
    |--------------------------------------------------------------------------
    | Validar y normalizar RUT del prescriptor
    |--------------------------------------------------------------------------
    */

    private function normalizarRut(string $rut): string
    {
        $rut = strtoupper(
            preg_replace('/[.\s-]/', '', $rut)
        );

        if (!preg_match('/^[0-9]{1,8}[0-9K]$/', $rut)) {

            throw ValidationException::withMessages([
                'rut_prescriptor' =>
                    'El formato del RUT del prescriptor no es válido.',
            ]);
        }

        $cuerpo = ltrim(substr($rut, 0, -1), '0');

        $dv = substr($rut, -1);

        if ($cuerpo === '') {

            throw ValidationException::withMessages([
                'rut_prescriptor' =>
                    'El RUT del prescriptor no es válido.',
            ]);
        }

        $suma = 0;

        $multiplicador = 2;

        for ($i = strlen($cuerpo) - 1; $i >= 0; $i--) {

            $suma += (int) $cuerpo[$i] * $multiplicador;

            $multiplicador++;

            if ($multiplicador > 7) {
                $multiplicador = 2;
            }
        }

        $resultado = 11 - ($suma % 11);

        $dvEsperado = match ($resultado) {
            11 => '0',
            10 => 'K',
            default => (string) $resultado,
        };

        if ($dv !== $dvEsperado) {

            throw ValidationException::withMessages([
                'rut_prescriptor' =>
                    'El dígito verificador del RUT no es válido.',
            ]);
        }

        return number_format(
            (int) $cuerpo,
            0,
            ',',
            '.'
        ) . '-' . $dv;
    }

    /*
    |--------------------------------------------------------------------------
    | Registrar receta médica simulada
    |--------------------------------------------------------------------------
    */

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

            'rut_prescriptor' => [
                'nullable',
                'string',
                'max:12',
            ],

            'tipo_receta' => [
                'nullable',
                Rule::in([
                    'simple',
                    'retenida',
                    'cheque',
                ]),
            ],

            'formato_receta' => [
                'nullable',
                Rule::in([
                    'fisica',
                    'digital',
                ]),
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

            'tipo_receta.in' =>
                'El tipo de receta seleccionado no es válido.',

            'formato_receta.in' =>
                'El formato de receta seleccionado no es válido.',

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

            /*
            |--------------------------------------------------------------------------
            | Validar paciente activo
            |--------------------------------------------------------------------------
            */

            $paciente = Paciente::whereKey(
                $datos['paciente_id']
            )
                ->lockForUpdate()
                ->first();

            if (!$paciente || !$paciente->activo) {

                throw ValidationException::withMessages([
                    'paciente_id' =>
                        'El paciente ya no está disponible.',
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Validar medicamentos activos
            |--------------------------------------------------------------------------
            */

            $idsMedicamentos = collect($datos['detalles'])
                ->pluck('medicamento_id')
                ->sort()
                ->values();

            $medicamentosActivos = Medicamento::whereIn(
                'id',
                $idsMedicamentos
            )
                ->where('activo', true)
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            if (
                $medicamentosActivos->count()
                !== $idsMedicamentos->count()
            ) {

                throw ValidationException::withMessages([
                    'detalles' =>
                        'Uno o más medicamentos ya no están disponibles.',
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Determinar tipo y formato de receta
            |--------------------------------------------------------------------------
            */

            $tipoReceta = $datos['tipo_receta'] ?? 'simple';

            $formatoReceta = $datos['formato_receta'] ?? null;

            $rutPrescriptor = !empty($datos['rut_prescriptor'])
                ? $this->normalizarRut($datos['rut_prescriptor'])
                : null;

            /*
            |--------------------------------------------------------------------------
            | Validar clasificación de medicamentos
            |--------------------------------------------------------------------------
            */

            foreach ($medicamentosActivos as $medicamento) {

                if ($medicamento->es_controlado) {

                    if (
                        !$medicamento->tipo_control
                        || !$medicamento->requiere_receta
                        || !in_array(
                            $medicamento->tipo_receta,
                            ['retenida', 'cheque'],
                            true
                        )
                    ) {

                        throw ValidationException::withMessages([
                            'detalles' =>
                                'El medicamento '
                                . $medicamento->nombre
                                . ' presenta una clasificación incompleta.',
                        ]);
                    }
                }

                if ($medicamento->requiere_receta) {

                    if (!$medicamento->tipo_receta) {

                        throw ValidationException::withMessages([
                            'detalles' =>
                                'El medicamento '
                                . $medicamento->nombre
                                . ' no tiene configurado su tipo de receta.',
                        ]);
                    }

                    if ($medicamento->tipo_receta !== $tipoReceta) {

                        $tipoRequerido = match (
                            $medicamento->tipo_receta
                        ) {
                            'retenida' => 'retenida',
                            'cheque' => 'cheque',
                            default => 'simple',
                        };

                        throw ValidationException::withMessages([
                            'tipo_receta' =>
                                'El medicamento '
                                . $medicamento->nombre
                                . ' requiere una receta '
                                . $tipoRequerido
                                . '.',
                        ]);
                    }
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Validaciones especiales
            |--------------------------------------------------------------------------
            */

            if (in_array(
                $tipoReceta,
                ['retenida', 'cheque'],
                true
            )) {

                if (!$rutPrescriptor) {

                    throw ValidationException::withMessages([
                        'rut_prescriptor' =>
                            'Debes ingresar el RUT del profesional prescriptor.',
                    ]);
                }

                if (!$formatoReceta) {

                    throw ValidationException::withMessages([
                        'formato_receta' =>
                            'Debes indicar si la receta es física o digital.',
                    ]);
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Crear receta simulada
            |--------------------------------------------------------------------------
            */

            $receta = Receta::create([

                'codigo_receta' =>
                    $datos['codigo_receta'],

                'paciente_id' =>
                    $datos['paciente_id'],

                'fecha_emision' =>
                    $datos['fecha_emision'],

                'fecha_vencimiento' =>
                    $datos['fecha_vencimiento'],

                'profesional_prescriptor' =>
                    $datos['profesional_prescriptor'],

                'rut_prescriptor' =>
                    $rutPrescriptor,

                'tipo_receta' =>
                    $tipoReceta,

                'formato_receta' =>
                    $formatoReceta,

                'estado' => 'vigente',

                'observaciones' =>
                    $datos['observaciones'] ?? null,

                'fecha_retencion' => null,

                'retenida_por' => null,

            ]);

            /*
            |--------------------------------------------------------------------------
            | Registrar medicamentos prescritos
            |--------------------------------------------------------------------------
            */

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

        }, 3);

        return redirect()
            ->route('recetas.index')
            ->with(
                'success',
                'Receta simulada registrada correctamente.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Visualizar receta en PDF
    |--------------------------------------------------------------------------
    */

    public function verPdf(Receta $receta)
    {
        $pdf = $this->generarPdf($receta);

        return $pdf->stream(
            'receta-simulada-' . $receta->id . '.pdf',
            [
                'Attachment' => false,
            ]
        )->header(
            'Cache-Control',
            'private, no-store'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Descargar receta en PDF
    |--------------------------------------------------------------------------
    */

    public function descargarPdf(Receta $receta)
    {
        $pdf = $this->generarPdf($receta);

        return $pdf->download(
            'receta-simulada-' . $receta->id . '.pdf'
        )->header(
            'Cache-Control',
            'private, no-store'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Generar documento PDF
    |--------------------------------------------------------------------------
    */

    private function generarPdf(Receta $receta)
    {
        $receta->load([
            'paciente',
            'detalles.medicamento',
            'responsableRetencion:id,name',
        ]);

        $vigencia = $this->obtenerVigencia($receta);

        $estadoAlergias =
            $receta->paciente?->estado_alergias
            ?? 'desconocido';

        $alergias =
            $estadoAlergias === 'con_alergias'
                ? $receta->paciente?->alergias
                : null;

        /*
        |--------------------------------------------------------------------------
        | Configurar logo institucional
        |--------------------------------------------------------------------------
        */

        $rutaLogo = public_path(
            'images/logo-inacap.png'
        );

        $logoInacap = null;

        if (is_file($rutaLogo) && is_readable($rutaLogo)) {

            $contenidoLogo = file_get_contents($rutaLogo);

            if ($contenidoLogo !== false) {

                $logoInacap =
                    'data:image/png;base64,'
                    . base64_encode($contenidoLogo);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Información de clasificación y retención
        |--------------------------------------------------------------------------
        */

        $tipoReceta = match ($receta->tipo_receta) {
            'simple' => 'Receta simple',
            'retenida' => 'Receta retenida',
            'cheque' => 'Receta cheque',
            default => 'Sin clasificación histórica',
        };

        $formatoReceta = match ($receta->formato_receta) {
            'fisica' => 'Física',
            'digital' => 'Digital',
            default => 'No registrado',
        };

        $estadoRetencion = match (true) {

            $receta->fecha_retencion !== null =>
                'Retenida',

            in_array(
                $receta->tipo_receta,
                ['retenida', 'cheque'],
                true
            ) => 'Pendiente de retención',

            $receta->tipo_receta === 'simple' =>
                'No requiere retención',

            default => 'Sin registro histórico',
        };

        /*
        |--------------------------------------------------------------------------
        | Cargar plantilla del documento
        |--------------------------------------------------------------------------
        */

        return Pdf::loadView('pdf.receta', [

            'receta' => $receta,

            'paciente' => $receta->paciente,

            'vigencia' => $vigencia,

            'estadoAlergias' => $estadoAlergias,

            'alergias' => $alergias,

            'logoInacap' => $logoInacap,

            'fechaGeneracion' => now(),

            'tipoReceta' => $tipoReceta,

            'formatoReceta' => $formatoReceta,

            'estadoRetencion' => $estadoRetencion,

            'rutPrescriptor' => $receta->rut_prescriptor,

            'fechaRetencion' => $receta->fecha_retencion,

            'responsableRetencion' =>
                $receta->responsableRetencion?->name,

        ])
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'defaultFont' => 'DejaVu Sans',
                'isRemoteEnabled' => false,
                'isPhpEnabled' => false,
                'isJavascriptEnabled' => false,
            ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Determinar vigencia actual de la receta
    |--------------------------------------------------------------------------
    */

    private function obtenerVigencia(Receta $receta): string
    {
        if ($receta->estado === 'anulada') {

            return 'anulada';
        }

        if ($receta->estado === 'utilizada') {

            return 'utilizada';
        }

        $hoy = Carbon::today();

        if (
            Carbon::parse($receta->fecha_emision)
                ->startOfDay()
                ->gt($hoy)
        ) {

            return 'no_vigente';
        }

        if (
            Carbon::parse($receta->fecha_vencimiento)
                ->startOfDay()
                ->lt($hoy)
        ) {

            return 'vencida';
        }

        return 'vigente';
    }
}