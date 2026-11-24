<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">

    <title>Comprobante de dispensación - {{ $folio }}</title>

    <style>
        @page {
            margin: 35px 40px 65px;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11px;
            line-height: 1.5;
            color: #333344;
            margin: 0;
        }

        /* MARCA DE AGUA */

        .marca-agua {
            position: fixed;
            top: 36%;
            left: -10%;
            width: 120%;
            text-align: center;
            transform: rotate(-35deg);
            font-size: 39px;
            font-weight: bold;
            color: #665579;
            opacity: 0.13;
            line-height: 1.3;
            z-index: -1000;
        }

        /* ENCABEZADO */

        .encabezado {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 3px solid #bba6d8;
            margin-bottom: 20px;
        }

        .encabezado td {
            padding-bottom: 15px;
            vertical-align: middle;
        }

        .marca {
            font-size: 27px;
            font-weight: bold;
            color: #333344;
            margin: 0;
        }

        .submarca {
            font-size: 10px;
            color: #777181;
            margin-top: 3px;
        }

        .logo {
            width: 110px;
            height: auto;
        }

        .logo-contenedor {
            text-align: right;
        }

        .logo-alternativo {
            color: #e30613;
            font-size: 24px;
            font-weight: bold;
        }

        /* TITULO */

        .titulo-documento {
            text-align: center;
            margin-bottom: 16px;
        }

        .titulo-documento h1 {
            font-size: 19px;
            color: #333344;
            margin: 0 0 7px;
        }

        .folio {
            font-size: 12px;
            font-weight: bold;
            color: #665579;
        }

        .aviso-academico {
            background-color: #f0e8f9;
            border: 1px solid #e6e0ef;
            border-radius: 5px;
            color: #665579;
            padding: 10px;
            text-align: center;
            font-size: 9px;
            font-weight: bold;
            margin-bottom: 18px;
        }

        /* SECCIONES */

        .seccion {
            margin-bottom: 17px;
        }

        .titulo-seccion {
            font-size: 12px;
            font-weight: bold;
            color: #665579;
            border-bottom: 1px solid #e6e0ef;
            padding-bottom: 7px;
            margin: 0 0 10px;
            page-break-after: avoid;
        }

        .tabla-datos {
            width: 100%;
            border-collapse: collapse;
        }

        .tabla-datos td {
            width: 50%;
            padding: 7px 10px 10px 0;
            vertical-align: top;
        }

        .etiqueta {
            font-size: 9px;
            color: #777181;
            margin-bottom: 3px;
        }

        .valor {
            font-size: 11px;
            font-weight: bold;
            color: #333344;
            word-wrap: break-word;
        }

        /* PROGRAMA DE SALUD */

        .programa-panel {
            background-color: #f8f7fc;
            border: 1px solid #d2bfea;
            border-radius: 6px;
            padding: 14px;
            page-break-inside: avoid;
        }

        .programa-etiqueta {
            display: inline-block;
            padding: 5px 10px;
            background-color: #e3d5f5;
            color: #665579;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
            margin-bottom: 8px;
        }

        .programa-nombre {
            font-size: 14px;
            font-weight: bold;
            color: #333344;
            margin-bottom: 8px;
        }

        .programa-indicadores {
            width: 100%;
            border-collapse: separate;
            border-spacing: 5px;
            margin-top: 8px;
        }

        .programa-indicadores td {
            width: 33.33%;
            background-color: #ffffff;
            border: 1px solid #e6e0ef;
            border-radius: 5px;
            padding: 10px 6px;
            text-align: center;
            vertical-align: top;
        }

        .indicador-titulo {
            font-size: 9px;
            color: #777181;
            margin-bottom: 6px;
        }

        .indicador-valor {
            font-size: 18px;
            font-weight: bold;
            color: #665579;
        }

        .indicador-descripcion {
            font-size: 8px;
            color: #777181;
            margin-top: 4px;
        }

        .entrega-gratuita {
            background-color: #ecfdf3;
            border: 1px solid #bbf7d0;
            border-radius: 5px;
            padding: 12px;
            margin-top: 10px;
            color: #166534;
            page-break-inside: avoid;
        }

        .entrega-gratuita table {
            width: 100%;
            border-collapse: collapse;
        }

        .costo {
            text-align: right;
            font-size: 21px;
            font-weight: bold;
        }

        /* ALERTAS DE ALERGIAS */

        .alerta {
            padding: 13px;
            border-radius: 5px;
            margin-bottom: 12px;
            page-break-inside: avoid;
            word-wrap: break-word;
        }

        .alerta-roja {
            background-color: #fef2f2;
            border: 1px solid #fecaca;
            color: #991b1b;
        }

        .alerta-verde {
            background-color: #ecfdf3;
            border: 1px solid #bbf7d0;
            color: #166534;
        }

        .alerta-ambar {
            background-color: #fffbeb;
            border: 1px solid #fde68a;
            color: #92400e;
        }

        .alerta-gris {
            background-color: #f1f5f9;
            border: 1px solid #cbd5e1;
            color: #475569;
        }

        .alerta-titulo {
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 7px;
        }

        .alerta p {
            margin: 5px 0;
        }

        .descripcion-alergias {
            padding: 8px;
            background-color: #ffffff;
            border-radius: 3px;
            margin: 8px 0;
            font-weight: bold;
            white-space: pre-wrap;
            word-wrap: break-word;
        }

        .revision {
            padding: 9px 12px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
            margin-bottom: 8px;
        }

        .revision-confirmada {
            background-color: #ecfdf3;
            color: #166534;
            border: 1px solid #bbf7d0;
        }

        .revision-no-confirmada {
            background-color: #fffbeb;
            color: #92400e;
            border: 1px solid #fde68a;
        }

        .nota {
            font-size: 9px;
            color: #777181;
            line-height: 1.6;
        }

        /* TABLA DE MEDICAMENTOS */

        .tabla-medicamentos {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }

        .tabla-medicamentos thead {
            display: table-header-group;
        }

        .tabla-medicamentos th {
            background-color: #e3d5f5;
            color: #333344;
            font-size: 9px;
            padding: 11px 8px;
            text-align: left;
            border-bottom: 1px solid #d2bfea;
        }

        .tabla-medicamentos td {
            border-bottom: 1px solid #e6e0ef;
            padding: 12px 8px;
            font-size: 10px;
            vertical-align: middle;
            word-wrap: break-word;
        }

        .tabla-medicamentos tr {
            page-break-inside: avoid;
        }

        .tabla-medicamentos tbody tr:nth-child(even) {
            background-color: #f8f7fc;
        }

        .nombre-medicamento {
            font-weight: bold;
            color: #333344;
        }

        .codigo-medicamento {
            color: #777181;
            font-size: 9px;
            margin-top: 3px;
        }

        .tabla-medicamentos .centrado {
            text-align: center;
        }

        .cantidad {
            font-size: 12px;
            font-weight: bold;
            color: #665579;
        }

        /* TOTAL */

        .tabla-total {
            width: 100%;
            border-collapse: collapse;
            background-color: #f0e8f9;
            border: 1px solid #e6e0ef;
            page-break-inside: avoid;
        }

        .tabla-total td {
            padding: 12px;
            font-weight: bold;
            color: #665579;
        }

        .total-valor {
            text-align: right;
            font-size: 16px;
        }

        /* OBSERVACIONES */

        .observaciones {
            padding: 12px;
            background-color: #f8f7fc;
            border: 1px solid #e6e0ef;
            border-radius: 4px;
            white-space: pre-wrap;
            word-wrap: break-word;
        }

        /* RESPONSABLE */

        .responsable {
            background-color: #f8f7fc;
            border: 1px solid #e6e0ef;
            padding: 14px;
            border-radius: 5px;
            page-break-inside: avoid;
        }

        .responsable-nombre {
            font-size: 13px;
            font-weight: bold;
            color: #333344;
        }

        /* PIE DE PAGINA */

        .pie-pagina {
            position: fixed;
            bottom: -48px;
            left: 0;
            right: 0;
            border-top: 1px solid #e6e0ef;
            padding-top: 9px;
            text-align: center;
            font-size: 8px;
            color: #777181;
        }

        .pie-pagina strong {
            color: #665579;
        }

        .pie-pagina p {
            margin: 3px 0;
        }
    </style>
</head>

<body>

    @php
        $rut = $paciente?->rut;

        if ($rut) {
            $rutLimpio = strtoupper(
                preg_replace('/[^0-9kK]/', '', $rut)
            );

            if (strlen($rutLimpio) >= 2) {
                $cuerpoRut = substr($rutLimpio, 0, -1);
                $digitoVerificador = substr($rutLimpio, -1);

                $rut = number_format(
                    (int) $cuerpoRut,
                    0,
                    ',',
                    '.'
                ) . '-' . $digitoVerificador;
            }
        }

        $nombrePaciente = trim(
            ($paciente?->nombres ?? '') . ' ' .
            ($paciente?->apellidos ?? '')
        );

        $claseAlerta = match ($estadoAlergias) {
            'con_alergias' => 'alerta-roja',
            'sin_alergias' => 'alerta-verde',
            'desconocido' => 'alerta-ambar',
            default => 'alerta-gris',
        };

        $esPrograma = !empty($dispensacion->programa_id);

        $tipoPrograma = match ($programaTipo ?? null) {
            'GES' => 'GES/AUGE',
            'Cronico' => 'Programa de pacientes crónicos',
            default => 'Programa de salud',
        };

        $modalidad = $esPrograma
            ? $tipoPrograma
            : 'Dispensación general';

        $coberturaFormateada = isset($cobertura)
            ? rtrim(
                rtrim(
                    number_format((float) $cobertura, 2, ',', '.'),
                    '0'
                ),
                ','
            ) . '%'
            : 'No registrada';

    @endphp

    <!-- MARCA DE AGUA -->

    <div class="marca-agua">
        DOCUMENTO SIMULADO<br>
        USO ACADÉMICO<br>
    </div>

    <!-- PIE DE PAGINA -->

    <div class="pie-pagina">

        <strong>
            FarmaControl | Proyecto Integrado INACAP
        </strong>

        <p>
            Uso exclusivamente académico.
            Este documento no acredita una dispensación real.
        </p>

        <p>
            Documento generado el
            {{ $fechaGeneracion->format('d/m/Y H:i') }}
        </p>

    </div>

    <!-- ENCABEZADO -->

    <table class="encabezado">

        <tr>

            <td>

                <div class="marca">
                    FarmaControl
                </div>

                <div class="submarca">
                    Sistema integrado de gestión farmacéutica
                </div>

                <div class="submarca">
                    Farmacia Asistencial - CESFAM
                </div>

            </td>

            <td class="logo-contenedor">

                @if ($logoInacap)

                    <img
                        src="{{ $logoInacap }}"
                        class="logo"
                        alt="INACAP"
                    >

                @else

                    <span class="logo-alternativo">
                        INACAP
                    </span>

                @endif

            </td>

        </tr>

    </table>

    <!-- TITULO -->

    <div class="titulo-documento">

        <h1>COMPROBANTE DE DISPENSACIÓN</h1>

        <div class="folio">
            Folio: {{ $folio }}
        </div>

    </div>

    <div class="aviso-academico">

        DOCUMENTO DE SIMULACIÓN ACADÉMICA
        - SIN VALIDEZ LEGAL NI CLÍNICA

    </div>

    <!-- INFORMACION DE LA DISPENSACION -->

    <div class="seccion">

        <h2 class="titulo-seccion">
            01. Información de la dispensación
        </h2>

        <table class="tabla-datos">

            <tr>

                <td>

                    <div class="etiqueta">
                        Número de comprobante
                    </div>

                    <div class="valor">
                        {{ $folio }}
                    </div>

                </td>

                <td>

                    <div class="etiqueta">
                        Fecha y hora de dispensación
                    </div>

                    <div class="valor">

                        {{ $dispensacion->fecha_dispensacion
                            ? \Carbon\Carbon::parse(
                                $dispensacion->fecha_dispensacion
                              )->format('d/m/Y H:i')
                            : 'No registrada' }}

                    </div>

                </td>

            </tr>

            <tr>

                <td>

                    <div class="etiqueta">
                        Modalidad de dispensación
                    </div>

                    <div class="valor">
                        {{ $modalidad }}
                    </div>

                </td>

                <td>

                    <div class="etiqueta">
                        Entorno
                    </div>

                    <div class="valor">
                        Farmacia Asistencial (CESFAM)
                    </div>

                </td>

            </tr>

        </table>

    </div>

    <!-- INFORMACION DEL PACIENTE -->

    <div class="seccion">

        <h2 class="titulo-seccion">
            02. Identificación del paciente
        </h2>

        <table class="tabla-datos">

            <tr>

                <td>

                    <div class="etiqueta">
                        Nombre completo
                    </div>

                    <div class="valor">
                        {{ $nombrePaciente ?: 'No disponible' }}
                    </div>

                </td>

                <td>

                    <div class="etiqueta">
                        RUT
                    </div>

                    <div class="valor">
                        {{ $rut ?: 'No registrado' }}
                    </div>

                </td>

            </tr>

            <tr>

                <td>

                    <div class="etiqueta">
                        Código del paciente
                    </div>

                    <div class="valor">

                        {{ $paciente?->codigo_paciente
                            ?? 'No disponible' }}

                    </div>

                </td>

                <td>

                    <div class="etiqueta">
                        Fecha de nacimiento
                    </div>

                    <div class="valor">

                        {{ $paciente?->fecha_nacimiento
                            ? \Carbon\Carbon::parse(
                                $paciente->fecha_nacimiento
                              )->format('d/m/Y')
                            : 'No registrada' }}

                    </div>

                </td>

            </tr>

        </table>

    </div>
<!-- IDENTIFICACION DEL RETIRANTE -->

<div class="seccion">

    <h2 class="titulo-seccion">
        03. Identificación del retirante
    </h2>

    <div class="programa-panel">

        @if (in_array($dispensacion->tipo_retiro, ['paciente', 'tercero_autorizado']))

            <div class="programa-etiqueta">
                {{ $dispensacion->tipo_retiro === 'paciente'
                    ? 'Retiro personal'
                    : 'Tercero autorizado' }}
            </div>

            <table class="tabla-datos">

                <tr>

                    <td>
                        <div class="etiqueta">
                            Nombre completo del retirante
                        </div>

                        <div class="valor">
                            {{ $dispensacion->retirante_nombre
                                ?: 'No registrado' }}
                        </div>
                    </td>

                    <td>
                        <div class="etiqueta">
                            RUT del retirante
                        </div>

                        <div class="valor">
                            {{ $dispensacion->retirante_rut
                                ?: 'No registrado' }}
                        </div>
                    </td>

                </tr>

                <tr>

                    <td>
                        <div class="etiqueta">
                            Relación con el paciente
                        </div>

                        <div class="valor">
                            {{ $dispensacion->tipo_retiro === 'paciente'
                                ? 'Titular de la receta'
                                : ($dispensacion->retirante_parentesco
                                    ?: 'No registrada') }}
                        </div>
                    </td>

                    <td>
                        <div class="etiqueta">
                            Fecha y hora del retiro
                        </div>

                        <div class="valor">
                            {{ $dispensacion->fecha_dispensacion
                                ? \Carbon\Carbon::parse(
                                    $dispensacion->fecha_dispensacion
                                )->format('d/m/Y H:i')
                                : 'No registrada' }}
                        </div>
                    </td>

                </tr>

            </table>

            @if ($dispensacion->tipo_retiro === 'tercero_autorizado')

                @if ($dispensacion->autorizacion_verificada)

                    <div class="revision revision-confirmada">
                        Autorización del tercero confirmada
                        por el responsable de la dispensación.
                    </div>

                @else

                    <div class="revision revision-no-confirmada">
                        No consta confirmación de la autorización
                        del tercero.
                    </div>

                @endif

            @else

                <div class="revision revision-confirmada">
                    Entrega registrada directamente al paciente.
                </div>

            @endif

            <p class="nota">
                Los datos del retirante corresponden a la
                información histórica almacenada al momento
                de registrar la dispensación.
                La confirmación de autorización corresponde
                a una simulación académica y no constituye
                una acreditación legal.
            </p>

        @else

            <div class="alerta alerta-gris">

                <div class="alerta-titulo">
                    Sin registro histórico del retirante
                </div>

                <p>
                    Esta dispensación no dispone de información
                    sobre la persona que recibió los medicamentos.
                </p>

            </div>

        @endif

    </div>

</div>
    <!-- PROGRAMA DE SALUD -->

    <div class="seccion">

        <h2 class="titulo-seccion">
            04. Modalidad y cobertura asistencial
        </h2>

        <div class="programa-panel">

            @if ($esPrograma)

                <div class="programa-etiqueta">
                    {{ $tipoPrograma }}
                </div>

                <div class="programa-nombre">

                    {{ $programaNombre
                        ?? 'Nombre del programa no registrado' }}

                </div>

                <table class="tabla-datos">

                    <tr>

                        <td>

                            <div class="etiqueta">
                                Modalidad registrada
                            </div>

                            <div class="valor">
                                Entrega mediante programa de salud
                            </div>

                        </td>

                        <td>

                            <div class="etiqueta">
                                Validación del beneficio
                            </div>

                            <div class="valor">
                                Beneficiario validado al registrar
                            </div>

                        </td>

                    </tr>

                </table>

                <table class="programa-indicadores">

                    <tr>

                        <td>

                            <div class="indicador-titulo">
                                Cuota máxima
                            </div>

                            <div class="indicador-valor">
                                {{ $cuotaMaxima ?? '—' }}
                            </div>

                            <div class="indicador-descripcion">
                                Unidades autorizadas
                            </div>

                        </td>

                        <td>

                            <div class="indicador-titulo">
                                Período autorizado
                            </div>

                            <div class="indicador-valor">
                                {{ $periodoDias ?? '—' }}
                            </div>

                            <div class="indicador-descripcion">
                                Días del período móvil
                            </div>

                        </td>

                        <td>

                            <div class="indicador-titulo">
                                Cobertura
                            </div>

                            <div class="indicador-valor">
                                {{ $coberturaFormateada }}
                            </div>

                            <div class="indicador-descripcion">
                                Cobertura registrada
                            </div>

                        </td>

                    </tr>

                </table>

                <p class="nota">

                    Los datos del programa, cuota, período y cobertura
                    corresponden a los valores históricos almacenados
                    al momento de registrar la dispensación.

                    La cuota máxima no representa el saldo disponible
                    actual del beneficiario.

                </p>

            @else

                <div class="programa-etiqueta">
                    Dispensación general
                </div>

                <div class="programa-nombre">
                    Atención farmacéutica asistencial
                </div>

                <p class="nota">

                    Esta entrega no se encuentra asociada a un
                    programa GES/AUGE ni a un programa de
                    pacientes crónicos.

                </p>

            @endif

            <div class="entrega-gratuita">

                <table>

                    <tr>

                        <td>

                            <strong>
                                Entrega asistencial gratuita
                            </strong>

                            <div style="font-size: 9px;">

                                Operación simulada sin cobro
                                al paciente.

                            </div>

                        </td>

                        <td class="costo">

                            ${{ number_format(
                                (float) ($costoEntrega ?? 0),
                                0,
                                ',',
                                '.'
                            ) }}

                        </td>

                    </tr>

                </table>

            </div>

        </div>

    </div>

    <!-- ANTECEDENTES DE ALERGIAS -->

    <div class="seccion">

        <h2 class="titulo-seccion">
            05. Antecedentes de alergias
        </h2>

        <div class="alerta {{ $claseAlerta }}">

            <div class="alerta-titulo">
                {{ $textoEstadoAlergias }}
            </div>

            @if ($estadoAlergias === 'con_alergias')

                <p>
                    Alergias registradas al momento de la entrega:
                </p>

                <div class="descripcion-alergias">{{ $alergias ?: 'Sin descripción registrada.' }}</div>

                <p>

                    Ante posibles incompatibilidades,
                    corresponde consultar al químico farmacéutico.

                </p>

            @elseif ($estadoAlergias === 'sin_alergias')

                <p>

                    Al momento de la dispensación no existían
                    alergias conocidas registradas.

                </p>

                <p>

                    Esto no garantiza la ausencia de alergias.

                </p>

            @elseif ($estadoAlergias === 'desconocido')

                <p>

                    Los antecedentes de alergias del paciente
                    eran desconocidos al momento de la entrega.

                </p>

            @else

                <p>

                    Este registro no dispone de antecedentes
                    históricos de alergias.

                </p>

                <p>

                    No es posible determinar el estado de
                    alergias al momento de la dispensación.

                </p>

            @endif

        </div>

        @if ($alergiasRevisadas)

            <div class="revision revision-confirmada">

                Revisión de antecedentes de alergias confirmada
                por el responsable de la dispensación.

            </div>

        @else

            <div class="revision revision-no-confirmada">

                No consta confirmación de revisión
                de antecedentes de alergias.

            </div>

        @endif

        <p class="nota">

            Los antecedentes de alergias corresponden a los datos
            históricos almacenados en la dispensación.

            El sistema no realiza una evaluación automática de
            compatibilidad clínica entre medicamentos y alergias.

        </p>

    </div>

    <!-- RECETA ASOCIADA -->

    <div class="seccion">

        <h2 class="titulo-seccion">
            06. Información de la receta asociada
        </h2>

        <table class="tabla-datos">

            <tr>

                <td>

                    <div class="etiqueta">
                        Código de receta
                    </div>

                    <div class="valor">

                        {{ $receta?->codigo_receta
                            ?? 'No disponible' }}

                    </div>

                </td>

                <td>

                    <div class="etiqueta">
                        Profesional prescriptor
                    </div>

                    <div class="valor">

                        {{ $receta?->profesional_prescriptor
                            ?? 'No registrado' }}

                    </div>

                </td>

            </tr>

            <tr>

                <td>

                    <div class="etiqueta">
                        Fecha de emisión
                    </div>

                    <div class="valor">

                        {{ $receta?->fecha_emision
                            ? \Carbon\Carbon::parse(
                                $receta->fecha_emision
                              )->format('d/m/Y')
                            : 'No registrada' }}

                    </div>

                </td>

                <td>

                    <div class="etiqueta">
                        Fecha de vencimiento
                    </div>

                    <div class="valor">

                        {{ $receta?->fecha_vencimiento
                            ? \Carbon\Carbon::parse(
                                $receta->fecha_vencimiento
                              )->format('d/m/Y')
                            : 'No registrada' }}

                    </div>

                </td>

            </tr>

        </table>

    </div>

    <!-- MEDICAMENTOS DISPENSADOS -->

    <div class="seccion">

        <h2 class="titulo-seccion">
            07. Medicamentos dispensados
        </h2>

        <table class="tabla-medicamentos">

            <thead>

                <tr>

                    <th style="width: 6%;">
                        N.º
                    </th>

                    <th style="width: 36%;">
                        Medicamento
                    </th>

                    <th style="width: 22%;">
                        Concentración
                    </th>

                    <th style="width: 22%;">
                        Forma farmacéutica
                    </th>

                    <th class="centrado" style="width: 14%;">
                        Cantidad
                    </th>

                </tr>

            </thead>

            <tbody>

                @forelse ($dispensacion->detalles as $detalle)

                    <tr>

                        <td>
                            {{ $loop->iteration }}
                        </td>

                        <td>

                            <div class="nombre-medicamento">

                                {{ $detalle->medicamento?->nombre
                                    ?? 'Medicamento no disponible' }}

                            </div>

                            <div class="codigo-medicamento">

                                Código:

                                {{ $detalle->medicamento?->codigo
                                    ?? 'No registrado' }}

                            </div>

                        </td>

                        <td>

                            {{ $detalle->medicamento?->concentracion
                                ?? 'No registrada' }}

                        </td>

                        <td>

                            {{ $detalle->medicamento?->forma_farmaceutica
                                ?? 'No registrada' }}

                        </td>

                        <td class="centrado">

                            <span class="cantidad">
                                {{ $detalle->cantidad }}
                            </span>

                        </td>

                    </tr>

                @empty

                    <tr>

                        <td colspan="5" class="centrado">

                            No existen medicamentos registrados.

                        </td>

                    </tr>

                @endforelse

            </tbody>

        </table>

        <table class="tabla-total">

            <tr>

                <td>
                    Total de unidades dispensadas
                </td>

                <td class="total-valor">
                    {{ $totalUnidades }}
                </td>

            </tr>

        </table>

    </div>

    <!-- OBSERVACIONES -->

    <div class="seccion">

        <h2 class="titulo-seccion">
            08. Observaciones
        </h2>

        <div class="observaciones">{{ $dispensacion->observaciones ?: 'Sin observaciones registradas.' }}</div>

    </div>

    <!-- RESPONSABLE -->

    <div class="seccion">

        <h2 class="titulo-seccion">
            09. Responsable de la dispensación
        </h2>

        <div class="responsable">

            <div class="etiqueta">
                Usuario responsable del registro
            </div>

            <div class="responsable-nombre">
                {{ $responsable?->name ?? 'No disponible' }}
            </div>

            <p class="nota">

                Registro asociado al usuario que realizó
                la dispensación dentro de FarmaControl.

                No constituye una firma profesional.

            </p>

        </div>

    </div>

    <!-- NOTAS FINALES -->

    <p class="nota">

        Los datos de identificación del paciente, la receta,
        el responsable y el catálogo de medicamentos se consultan
        desde los registros relacionados disponibles al momento
        de generar este documento.

    </p>

    <p class="nota">

        Este comprobante fue generado para fines educativos
        en el contexto del Proyecto Integrado INACAP.

        No tiene validez clínica, comercial ni legal.

    </p>

</body>
</html>