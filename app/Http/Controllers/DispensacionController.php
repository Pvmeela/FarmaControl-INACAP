<?php

namespace App\Http\Controllers;

use App\Models\DetalleDispensacion;
use App\Models\Dispensacion;
use App\Models\Lote;
use App\Models\Medicamento;
use App\Models\MovimientoInventario;
use App\Models\Paciente;
use App\Models\ProgramaSalud;
use App\Models\Receta;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class DispensacionController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Consultar dispensaciones
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        $pacientes = Paciente::where('activo', true)
            ->with([
                'programas' => function ($consulta) {
                    $consulta
                        ->where('programas_salud.activo', true)
                        ->wherePivot('estado', 'Activo')
                        ->wherePivot(
                            'fecha_ingreso',
                            '<=',
                            Carbon::today()->toDateString()
                        );
                },

                'programas.medicamentos' => function ($consulta) {
                    $consulta
                        ->where('medicamentos.activo', true)
                        ->wherePivot('activo', true);
                },
            ])
            ->orderBy('nombres')
            ->orderBy('apellidos')
            ->get();

        $recetas = Receta::with([
            'paciente',
            'detalles.medicamento',
        ])
            ->orderByDesc('fecha_emision')
            ->orderByDesc('id')
            ->get();

        $medicamentos = Medicamento::where('activo', true)
            ->orderBy('nombre')
            ->get();

        $dispensaciones = Dispensacion::with([
            'paciente',
            'receta',
            'usuario',
            'programa',
            'detalles.medicamento',
        ])
            ->orderByDesc('fecha_dispensacion')
            ->orderByDesc('id')
            ->get();

        return Inertia::render('Dispensaciones/Index', [
            'pacientes' => $pacientes,
            'recetas' => $recetas,
            'medicamentos' => $medicamentos,
            'dispensaciones' => $dispensaciones,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Visualizar comprobante PDF
    |--------------------------------------------------------------------------
    */

    public function verPdf(Dispensacion $dispensacion)
    {
        return $this->generarPdf($dispensacion)
            ->stream(
                'dispensacion-simulada-' . $dispensacion->id . '.pdf',
                ['Attachment' => false]
            )
            ->header('Cache-Control', 'private, no-store');
    }

    /*
    |--------------------------------------------------------------------------
    | Descargar comprobante PDF
    |--------------------------------------------------------------------------
    */

    public function descargarPdf(Dispensacion $dispensacion)
    {
        return $this->generarPdf($dispensacion)
            ->download(
                'dispensacion-simulada-' . $dispensacion->id . '.pdf'
            )
            ->header('Cache-Control', 'private, no-store');
    }

    /*
    |--------------------------------------------------------------------------
    | Generar comprobante académico
    |--------------------------------------------------------------------------
    */

    private function generarPdf(Dispensacion $dispensacion)
    {
        $dispensacion->load([
            'paciente',
            'receta',
            'usuario',
            'programa',
            'detalles.medicamento',
        ]);

        $estadoAlergias = $dispensacion->estado_alergias;

        $alergias = $dispensacion->alergias;

        $alergiasRevisadas =
            (bool) $dispensacion->alergias_revisadas;

        $textoEstadoAlergias = match ($estadoAlergias) {
            'con_alergias' => 'Paciente con alergias conocidas',
            'sin_alergias' => 'Sin alergias conocidas registradas',
            'desconocido' => 'Antecedentes de alergias desconocidos',
            default => 'Sin antecedentes históricos registrados',
        };

        $totalUnidades = $dispensacion->detalles->sum('cantidad');

        $folio = 'DIS-' . str_pad(
            (string) $dispensacion->id,
            6,
            '0',
            STR_PAD_LEFT
        );

        $rutaLogo = public_path('images/logo-inacap.png');

        $logoInacap = null;

        if (is_file($rutaLogo) && is_readable($rutaLogo)) {

            $contenidoLogo = file_get_contents($rutaLogo);

            if ($contenidoLogo !== false) {

                $logoInacap = 'data:image/png;base64,'
                    . base64_encode($contenidoLogo);
            }
        }

        $tipoRetiroTexto = match ($dispensacion->tipo_retiro) {
            'paciente' => 'Retiro por el propio paciente',
            'tercero_autorizado' => 'Retiro por tercero autorizado',
            default => 'Información histórica no registrada',
        };

        return Pdf::loadView('pdf.dispensacion', [

            'dispensacion' => $dispensacion,

            'paciente' => $dispensacion->paciente,

            'receta' => $dispensacion->receta,

            'responsable' => $dispensacion->usuario,

            'folio' => $folio,

            'estadoAlergias' => $estadoAlergias,

            'textoEstadoAlergias' => $textoEstadoAlergias,

            'alergias' => $alergias,

            'alergiasRevisadas' => $alergiasRevisadas,

            'totalUnidades' => $totalUnidades,

            'logoInacap' => $logoInacap,

            'fechaGeneracion' => now(),

            // Información del programa de salud

            'programaNombre' => $dispensacion->programa_nombre,

            'programaTipo' => $dispensacion->programa_tipo,

            'cobertura' => $dispensacion->cobertura,

            'cuotaMaxima' => $dispensacion->cuota_maxima,

            'periodoDias' => $dispensacion->periodo_dias,

            'costoEntrega' => 0,

            // Información histórica del retirante

            'tipoRetiro' => $dispensacion->tipo_retiro,

            'tipoRetiroTexto' => $tipoRetiroTexto,

            'retiranteNombre' => $dispensacion->retirante_nombre,

            'retiranteRut' => $dispensacion->retirante_rut,

            'retiranteParentesco' =>
                $dispensacion->retirante_parentesco,

            'autorizacionVerificada' =>
                $dispensacion->autorizacion_verificada,

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
    | Validar y normalizar RUT chileno
    |--------------------------------------------------------------------------
    */

    private function normalizarRutRetirante(
        string $rut,
        string $campo = 'retirante_rut'
    ): string {

        $rut = strtoupper(
            preg_replace('/[.\s-]/', '', $rut)
        );

        if (!preg_match('/^[0-9]{1,8}[0-9K]$/', $rut)) {

            throw ValidationException::withMessages([
                $campo => 'El formato del RUT no es válido.',
            ]);
        }

        $cuerpo = substr($rut, 0, -1);

        $dv = substr($rut, -1);

        $cuerpo = ltrim($cuerpo, '0');

        if ($cuerpo === '') {

            throw ValidationException::withMessages([
                $campo => 'El RUT ingresado no es válido.',
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
                $campo =>
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
    | Validar identificación de quien retira
    |--------------------------------------------------------------------------
    */

    private function validarRetirante(
        array $datos,
        Paciente $paciente
    ): array {

        /*
        |--------------------------------------------------------------------------
        | Retiro por el propio paciente
        |--------------------------------------------------------------------------
        */

        if ($datos['tipo_retiro'] === 'paciente') {

            if (empty($paciente->rut)) {

                throw ValidationException::withMessages([
                    'paciente_id' =>
                        'El paciente debe tener un RUT registrado.',
                ]);
            }

            $nombrePaciente = trim(
                $paciente->nombres . ' ' .
                $paciente->apellidos
            );

            if ($nombrePaciente === '') {

                throw ValidationException::withMessages([
                    'paciente_id' =>
                        'El paciente debe tener un nombre registrado.',
                ]);
            }

            return [

                'tipo_retiro' => 'paciente',

                'retirante_nombre' => $nombrePaciente,

                'retirante_rut' =>
                    $this->normalizarRutRetirante(
                        $paciente->rut,
                        'paciente_id'
                    ),

                'retirante_parentesco' => null,

                'autorizacion_verificada' => null,

            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Retiro por tercero autorizado
        |--------------------------------------------------------------------------
        */

        $nombreTercero = trim(
            $datos['retirante_nombre']
        );

        $parentesco = trim(
            $datos['retirante_parentesco']
        );

        if ($nombreTercero === '') {

            throw ValidationException::withMessages([
                'retirante_nombre' =>
                    'Debe ingresar el nombre del tercero.',
            ]);
        }

        if ($parentesco === '') {

            throw ValidationException::withMessages([
                'retirante_parentesco' =>
                    'Debe indicar la relación con el paciente.',
            ]);
        }

        $rutTercero = $this->normalizarRutRetirante(
            $datos['retirante_rut']
        );

        /*
        |--------------------------------------------------------------------------
        | Evitar registrar al paciente como tercero
        |--------------------------------------------------------------------------
        */

        if (!empty($paciente->rut)) {

            $rutPacienteLimpio = strtoupper(
                preg_replace(
                    '/[^0-9kK]/',
                    '',
                    $paciente->rut
                )
            );

            $rutTerceroLimpio = strtoupper(
                preg_replace(
                    '/[^0-9kK]/',
                    '',
                    $rutTercero
                )
            );

            if (
                ltrim($rutPacienteLimpio, '0') ===
                ltrim($rutTerceroLimpio, '0')
            ) {

                throw ValidationException::withMessages([
                    'retirante_rut' =>
                        'El RUT del tercero debe ser diferente al del paciente.',
                ]);
            }
        }

        return [

            'tipo_retiro' => 'tercero_autorizado',

            'retirante_nombre' => $nombreTercero,

            'retirante_rut' => $rutTercero,

            'retirante_parentesco' => $parentesco,

            'autorizacion_verificada' => true,

        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Validar programa, beneficiario, canasta y cuota
    |--------------------------------------------------------------------------
    */

    private function validarPrograma(
        array $datos,
        Paciente $paciente,
        Medicamento $medicamento
    ): array {

        $informacion = [

            'programa_id' => null,

            'programa_nombre' => null,

            'programa_tipo' => null,

            'cobertura' => null,

            'cuota_maxima' => null,

            'periodo_dias' => null,

        ];

        // Permitir dispensaciones asistenciales generales.

        if (empty($datos['programa_id'])) {

            return $informacion;
        }

        /*
        |--------------------------------------------------------------------------
        | Validar programa activo
        |--------------------------------------------------------------------------
        */

        $programa = ProgramaSalud::whereKey(
            $datos['programa_id']
        )
            ->lockForUpdate()
            ->first();

        if (!$programa || !$programa->activo) {

            throw ValidationException::withMessages([
                'programa_id' =>
                    'El programa seleccionado no está disponible.',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Validar beneficiario activo
        |--------------------------------------------------------------------------
        */

        $beneficiario = DB::table('paciente_programa')
            ->where('paciente_id', $paciente->id)
            ->where('programa_id', $programa->id)
            ->lockForUpdate()
            ->first();

        if (
            !$beneficiario
            || $beneficiario->estado !== 'Activo'
            || Carbon::parse($beneficiario->fecha_ingreso)
                ->startOfDay()
                ->gt(Carbon::today())
        ) {

            throw ValidationException::withMessages([
                'programa_id' =>
                    'El paciente no es beneficiario activo de este programa.',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Validar medicamento incluido en la canasta
        |--------------------------------------------------------------------------
        */

        $canasta = DB::table('programa_medicamento')
            ->where('programa_id', $programa->id)
            ->where('medicamento_id', $medicamento->id)
            ->lockForUpdate()
            ->first();

        if (!$canasta || !$canasta->activo) {

            throw ValidationException::withMessages([
                'medicamento_id' =>
                    'El medicamento no pertenece a la canasta activa del programa.',
            ]);
        }

        $cuotaMaxima = (int) $canasta->cantidad_maxima;

        $periodoDias = (int) $canasta->periodo_dias;

        $cobertura = (float) $canasta->cobertura;

        /*
        |--------------------------------------------------------------------------
        | Validar configuración de la canasta
        |--------------------------------------------------------------------------
        */

        if ($cuotaMaxima < 1 || $periodoDias < 1) {

            throw ValidationException::withMessages([
                'programa_id' =>
                    'La cuota o el período del programa no están configurados correctamente.',
            ]);
        }

        if ($cobertura <= 0 || $cobertura > 100) {

            throw ValidationException::withMessages([
                'programa_id' =>
                    'El medicamento no dispone de una cobertura válida.',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Calcular período móvil de entrega
        |--------------------------------------------------------------------------
        */

        $inicioPeriodo = Carbon::today()
            ->subDays($periodoDias - 1);

        $finPeriodo = Carbon::today()
            ->endOfDay();

        /*
        |--------------------------------------------------------------------------
        | Consultar entregas anteriores del programa
        |--------------------------------------------------------------------------
        */

        $cantidadEntregada = DB::table(
            'detalle_dispensaciones as detalle'
        )
            ->join(
                'dispensaciones as dispensacion',
                'detalle.dispensacion_id',
                '=',
                'dispensacion.id'
            )
            ->where(
                'dispensacion.paciente_id',
                $paciente->id
            )
            ->where(
                'dispensacion.programa_id',
                $programa->id
            )
            ->where(
                'detalle.medicamento_id',
                $medicamento->id
            )
            ->whereBetween(
                'dispensacion.fecha_dispensacion',
                [
                    $inicioPeriodo,
                    $finPeriodo,
                ]
            )
            ->sum('detalle.cantidad');

        $cantidadDisponible = max(
            0,
            $cuotaMaxima - $cantidadEntregada
        );

        if ($datos['cantidad'] > $cantidadDisponible) {

            throw ValidationException::withMessages([
                'cantidad' =>
                    'La cantidad solicitada supera la cuota disponible del programa. '
                    . 'Cuota máxima: '
                    . $cuotaMaxima
                    . ' unidades. Entregadas: '
                    . $cantidadEntregada
                    . '. Disponibles: '
                    . $cantidadDisponible
                    . '.',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Conservar información histórica del programa
        |--------------------------------------------------------------------------
        */

        return [

            'programa_id' => $programa->id,

            'programa_nombre' => $programa->nombre,

            'programa_tipo' => $programa->tipo,

            'cobertura' => $cobertura,

            'cuota_maxima' => $cuotaMaxima,

            'periodo_dias' => $periodoDias,

        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Registrar dispensación asistencial
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | Validar datos del formulario
        |--------------------------------------------------------------------------
        */

        $datos = $request->validate([

            'paciente_id' => [
                'required',
                'integer',
                'exists:pacientes,id',
            ],

            'receta_id' => [
                'required',
                'integer',
                'exists:recetas,id',
            ],

            'medicamento_id' => [
                'required',
                'integer',
                'exists:medicamentos,id',
            ],

            'programa_id' => [
                'nullable',
                'integer',
                'exists:programas_salud,id',
            ],

            'cantidad' => [
                'required',
                'integer',
                'min:1',
            ],

            'observaciones' => [
                'nullable',
                'string',
                'max:255',
            ],

            'alergias_revisadas' => [
                'required',
                'accepted',
            ],

            /*
            |--------------------------------------------------------------------------
            | Validaciones del retirante
            |--------------------------------------------------------------------------
            */

            'tipo_retiro' => [
                'required',
                'in:paciente,tercero_autorizado',
            ],

            'retirante_nombre' => [
                'exclude_unless:tipo_retiro,tercero_autorizado',
                'required',
                'string',
                'min:3',
                'max:200',
            ],

            'retirante_rut' => [
                'exclude_unless:tipo_retiro,tercero_autorizado',
                'required',
                'string',
                'max:12',
            ],

            'retirante_parentesco' => [
                'exclude_unless:tipo_retiro,tercero_autorizado',
                'required',
                'string',
                'max:100',
            ],

            'autorizacion_verificada' => [
                'exclude_unless:tipo_retiro,tercero_autorizado',
                'required',
                'accepted',
            ],

        ], [

            'paciente_id.required' =>
                'Debe seleccionar un paciente.',

            'receta_id.required' =>
                'Debe seleccionar una receta.',

            'medicamento_id.required' =>
                'Debe seleccionar un medicamento.',

            'cantidad.required' =>
                'Debe ingresar la cantidad a dispensar.',

            'cantidad.integer' =>
                'La cantidad debe ser un número entero.',

            'cantidad.min' =>
                'La cantidad debe ser mayor que cero.',

            'alergias_revisadas.accepted' =>
                'Debe confirmar la revisión de antecedentes de alergias.',

            'tipo_retiro.required' =>
                'Debe indicar quién retira los medicamentos.',

            'tipo_retiro.in' =>
                'La modalidad de retiro seleccionada no es válida.',

            'retirante_nombre.required' =>
                'Debe ingresar el nombre completo del tercero.',

            'retirante_nombre.min' =>
                'El nombre debe contener al menos tres caracteres.',

            'retirante_rut.required' =>
                'Debe ingresar el RUT del tercero autorizado.',

            'retirante_parentesco.required' =>
                'Debe indicar la relación del tercero con el paciente.',

            'autorizacion_verificada.required' =>
                'Debe confirmar la autorización del tercero.',

            'autorizacion_verificada.accepted' =>
                'Debe verificar la autorización antes de registrar la entrega.',

        ]);

        /*
        |--------------------------------------------------------------------------
        | Transacción de base de datos
        |--------------------------------------------------------------------------
        */

        DB::transaction(function () use ($datos, $request) {

            $hoy = Carbon::today()->toDateString();

            /*
            |--------------------------------------------------------------------------
            | Validar paciente activo
            |--------------------------------------------------------------------------
            */

            $paciente = Paciente::whereKey(
                $datos['paciente_id']
            )
                ->lockForUpdate()
                ->firstOrFail();

            if (!$paciente->activo) {

                throw ValidationException::withMessages([
                    'paciente_id' =>
                        'El paciente seleccionado está inactivo.',
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Validar receta
            |--------------------------------------------------------------------------
            */

            $receta = Receta::whereKey(
                $datos['receta_id']
            )
                ->lockForUpdate()
                ->firstOrFail();

            if (
                (int) $receta->paciente_id !==
                (int) $paciente->id
            ) {

                throw ValidationException::withMessages([
                    'receta_id' =>
                        'La receta seleccionada no pertenece al paciente.',
                ]);
            }

            if ($receta->estado !== 'vigente') {

                throw ValidationException::withMessages([
                    'receta_id' =>
                        'La receta seleccionada no se encuentra vigente.',
                ]);
            }

            if (
                !$receta->fecha_emision
                || !$receta->fecha_vencimiento
                || Carbon::parse($receta->fecha_emision)
                    ->startOfDay()
                    ->gt(Carbon::today())
                || Carbon::parse($receta->fecha_vencimiento)
                    ->startOfDay()
                    ->lt(Carbon::today())
            ) {

                throw ValidationException::withMessages([
                    'receta_id' =>
                        'La receta se encuentra fuera de su período de vigencia.',
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Conservar antecedentes históricos de alergias
            |--------------------------------------------------------------------------
            */

            $estadoAlergias = $paciente->estado_alergias;

            $antecedentesAlergias =
                $estadoAlergias === 'con_alergias'
                    ? $paciente->alergias
                    : null;

            /*
            |--------------------------------------------------------------------------
            | Validar identificación del retirante
            |--------------------------------------------------------------------------
            */

            $informacionRetirante = $this->validarRetirante(
                $datos,
                $paciente
            );

            /*
            |--------------------------------------------------------------------------
            | Validar medicamento activo
            |--------------------------------------------------------------------------
            */

            $medicamento = Medicamento::whereKey(
                $datos['medicamento_id']
            )
                ->lockForUpdate()
                ->firstOrFail();

            if (!$medicamento->activo) {

                throw ValidationException::withMessages([
                    'medicamento_id' =>
                        'El medicamento seleccionado está inactivo.',
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Validar medicamento incluido en la receta
            |--------------------------------------------------------------------------
            */

            $detallesReceta = $receta->detalles()
                ->where(
                    'medicamento_id',
                    $medicamento->id
                )
                ->get();

            if ($detallesReceta->isEmpty()) {

                throw ValidationException::withMessages([
                    'medicamento_id' =>
                        'Este medicamento no está incluido en la receta.',
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Validar programa de salud y cuota
            |--------------------------------------------------------------------------
            */

            $informacionPrograma = $this->validarPrograma(
                $datos,
                $paciente,
                $medicamento
            );

            /*
            |--------------------------------------------------------------------------
            | Validar cantidad pendiente de la receta
            |--------------------------------------------------------------------------
            */

            $cantidadPrescrita = $detallesReceta->sum(
                'cantidad_prescrita'
            );

            $cantidadEntregada = DetalleDispensacion::query()
                ->where(
                    'medicamento_id',
                    $medicamento->id
                )
                ->whereHas(
                    'dispensacion',
                    function ($consulta) use ($receta) {

                        $consulta->where(
                            'receta_id',
                            $receta->id
                        );
                    }
                )
                ->sum('cantidad');

            $cantidadPendienteReceta =
                $cantidadPrescrita - $cantidadEntregada;

            if (
                $cantidadPendienteReceta <= 0
                || $datos['cantidad'] > $cantidadPendienteReceta
            ) {

                throw ValidationException::withMessages([
                    'cantidad' =>
                        'La cantidad solicitada supera lo pendiente en la receta. '
                        . 'Pendiente: '
                        . max(0, $cantidadPendienteReceta)
                        . ' unidades.',
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Validar inventario mediante FEFO
            |--------------------------------------------------------------------------
            */

            $lotes = Lote::where(
                'medicamento_id',
                $medicamento->id
            )
                ->whereDate(
                    'fecha_vencimiento',
                    '>',
                    $hoy
                )
                ->where(
                    'cantidad_disponible',
                    '>',
                    0
                )
                ->orderBy('fecha_vencimiento')
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            $stockDisponible = $lotes->sum(
                'cantidad_disponible'
            );

            if ($stockDisponible < $datos['cantidad']) {

                throw ValidationException::withMessages([
                    'cantidad' =>
                        'Stock insuficiente. Disponible: '
                        . $stockDisponible
                        . ' unidades.',
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Registrar dispensación
            |--------------------------------------------------------------------------
            */

            $dispensacion = Dispensacion::create(
                array_merge([

                    'user_id' => $request->user()->id,

                    'paciente_id' => $paciente->id,

                    'receta_id' => $receta->id,

                    'fecha_dispensacion' => now(),

                    'observaciones' =>
                        $datos['observaciones'] ?? null,

                    'estado_alergias' => $estadoAlergias,

                    'alergias' => $antecedentesAlergias,

                    'alergias_revisadas' => true,

                ],
                    $informacionPrograma,
                    $informacionRetirante
                )
            );

            /*
            |--------------------------------------------------------------------------
            | Registrar detalle de dispensación
            |--------------------------------------------------------------------------
            */

            DetalleDispensacion::create([

                'dispensacion_id' => $dispensacion->id,

                'medicamento_id' => $medicamento->id,

                'cantidad' => $datos['cantidad'],

            ]);

            /*
            |--------------------------------------------------------------------------
            | Descontar inventario mediante FEFO
            |--------------------------------------------------------------------------
            */

            $cantidadPendiente = $datos['cantidad'];

            foreach ($lotes as $lote) {

                if ($cantidadPendiente <= 0) {

                    break;
                }

                $stockAnterior =
                    $lote->cantidad_disponible;

                $cantidadRetirada = min(
                    $stockAnterior,
                    $cantidadPendiente
                );

                $stockPosterior =
                    $stockAnterior - $cantidadRetirada;

                $lote->cantidad_disponible =
                    $stockPosterior;

                $lote->save();

                /*
                |--------------------------------------------------------------------------
                | Registrar movimiento de inventario
                |--------------------------------------------------------------------------
                */

                $motivo =
                    'Dispensación asistencial simulada #'
                    . $dispensacion->id;

                if ($dispensacion->programa_id) {

                    $motivo .= ' - Programa '
                        . $dispensacion->programa_tipo;
                }

                if (
                    $dispensacion->tipo_retiro ===
                    'tercero_autorizado'
                ) {

                    $motivo .= ' - Retiro por tercero autorizado';

                } else {

                    $motivo .= ' - Retiro por paciente';
                }

                MovimientoInventario::create([

                    'lote_id' => $lote->id,

                    'user_id' => $request->user()->id,

                    'tipo' => 'salida',

                    'cantidad' => $cantidadRetirada,

                    'stock_anterior' => $stockAnterior,

                    'stock_posterior' => $stockPosterior,

                    'motivo' => $motivo,

                ]);

                $cantidadPendiente -= $cantidadRetirada;
            }

            /*
            |--------------------------------------------------------------------------
            | Verificar descuento completo de inventario
            |--------------------------------------------------------------------------
            */

            if ($cantidadPendiente > 0) {

                throw ValidationException::withMessages([
                    'cantidad' =>
                        'No fue posible completar el descuento de inventario.',
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Verificar si la receta fue utilizada completamente
            |--------------------------------------------------------------------------
            */

            $recetaCompleta = true;

            $medicamentosPrescritos = $receta->detalles
                ->groupBy('medicamento_id');

            foreach (
                $medicamentosPrescritos
                as $medicamentoId => $detalles
            ) {

                $totalPrescrito = $detalles->sum(
                    'cantidad_prescrita'
                );

                $totalEntregado = DetalleDispensacion::query()
                    ->where(
                        'medicamento_id',
                        $medicamentoId
                    )
                    ->whereHas(
                        'dispensacion',
                        function ($consulta) use ($receta) {

                            $consulta->where(
                                'receta_id',
                                $receta->id
                            );
                        }
                    )
                    ->sum('cantidad');

                if ($totalEntregado < $totalPrescrito) {

                    $recetaCompleta = false;

                    break;
                }
            }

            if ($recetaCompleta) {

                $receta->estado = 'utilizada';

                $receta->save();
            }

        }, 3);

        /*
        |--------------------------------------------------------------------------
        | Confirmar registro exitoso
        |--------------------------------------------------------------------------
        */

        $mensaje = !empty($datos['programa_id'])

            ? 'Dispensación del programa de salud registrada correctamente. Entrega gratuita simulada.'

            : 'Dispensación asistencial simulada registrada correctamente.';

        if (
            $datos['tipo_retiro'] ===
            'tercero_autorizado'
        ) {

            $mensaje .= ' Retiro por tercero autorizado registrado.';

        } else {

            $mensaje .= ' Retiro por paciente registrado.';
        }

        return redirect()
            ->route('dispensaciones.index')
            ->with('success', $mensaje);
    }
}