<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">

    <title>Receta simulada - FarmaControl</title>

    <style>
        @page {
            margin: 35px 40px 65px 40px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #334155;
            line-height: 1.5;
        }

        .encabezado {
            width: 100%;
            border-bottom: 3px solid #1e40af;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }

        .marca {
            font-size: 25px;
            font-weight: bold;
            color: #1e40af;
        }

        .subtitulo {
            font-size: 10px;
            color: #64748b;
        }

        .logo {
            width: 105px;
            height: auto;
        }

        .titulo-documento {
            text-align: center;
            font-size: 19px;
            font-weight: bold;
            color: #1e293b;
            margin-top: 20px;
            margin-bottom: 5px;
        }

        .codigo-receta {
            text-align: center;
            color: #64748b;
            font-size: 12px;
            margin-bottom: 20px;
        }

        .seccion {
            margin-top: 20px;
            margin-bottom: 12px;
        }

        .titulo-seccion {
            background-color: #eff6ff;
            color: #1e40af;
            font-size: 12px;
            font-weight: bold;
            padding: 9px 12px;
            border-left: 4px solid #1e40af;
            margin-bottom: 10px;
        }

        .datos {
            width: 100%;
            border-collapse: collapse;
        }

        .datos td {
            width: 50%;
            padding: 7px 10px;
            vertical-align: top;
            overflow-wrap: break-word;
        }

        .etiqueta {
            font-size: 9px;
            color: #64748b;
            text-transform: uppercase;
        }

        .valor {
            font-size: 11px;
            font-weight: bold;
            color: #1e293b;
        }

        .alerta {
            padding: 12px;
            margin-top: 15px;
            margin-bottom: 15px;
            border-radius: 5px;
            page-break-inside: avoid;
        }

        .alerta-roja {
            background-color: #fef2f2;
            border: 1px solid #fca5a5;
            color: #991b1b;
        }

        .alerta-verde {
            background-color: #f0fdf4;
            border: 1px solid #86efac;
            color: #166534;
        }

        .alerta-amarilla {
            background-color: #fffbeb;
            border: 1px solid #fcd34d;
            color: #92400e;
        }

        .alerta-titulo {
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .tabla-medicamentos {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            table-layout: fixed;
        }

        .tabla-medicamentos thead {
            display: table-header-group;
        }

        .tabla-medicamentos th {
            background-color: #1e40af;
            color: #ffffff;
            padding: 10px 7px;
            font-size: 10px;
            text-align: left;
        }

        .tabla-medicamentos td {
            padding: 10px 7px;
            border-bottom: 1px solid #e2e8f0;
            vertical-align: top;
            overflow-wrap: break-word;
        }

        .tabla-medicamentos tr {
            page-break-inside: avoid;
        }

        .fila-alternativa {
            background-color: #f8fafc;
        }

        .observaciones {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            padding: 12px;
            min-height: 30px;
            overflow-wrap: break-word;
        }

        .estado {
            display: inline-block;
            padding: 4px 10px;
            font-size: 10px;
            font-weight: bold;
            border-radius: 4px;
        }

        .estado-vigente {
            background-color: #dcfce7;
            color: #166534;
        }

        .estado-vencida {
            background-color: #fee2e2;
            color: #991b1b;
        }

        .estado-utilizada {
            background-color: #dbeafe;
            color: #1e40af;
        }

        .estado-anulada {
            background-color: #e2e8f0;
            color: #475569;
        }

        .estado-no-vigente {
            background-color: #fef3c7;
            color: #92400e;
        }

        .marca-agua {
            position: fixed;
            top: 43%;
            left: 0;
            width: 100%;
            text-align: center;
            font-size: 40px;
            font-weight: bold;
            color: #cbd5e1;
            opacity: 0.22;
            transform: rotate(-35deg);
            z-index: -1000;
        }

        .aviso-academico {
            margin-top: 25px;
            padding: 12px;
            border: 1px dashed #94a3b8;
            background-color: #f8fafc;
            text-align: center;
            font-size: 9px;
            color: #475569;
            page-break-inside: avoid;
        }

        .pie-pagina {
            position: fixed;
            bottom: -45px;
            left: 0;
            right: 0;
            border-top: 1px solid #cbd5e1;
            padding-top: 8px;
            text-align: center;
            font-size: 8px;
            color: #64748b;
        }

        .centrado {
            text-align: center;
        }
    </style>
</head>

<body>

    {{-- Marca de agua académica --}}
    <div class="marca-agua">
        SIMULACIÓN ACADÉMICA
    </div>

    {{-- Pie de página --}}
    <div class="pie-pagina">
        <strong>FarmaControl | Uso académico INACAP</strong>
        <br>
        Sistema de simulación · Sin validez legal ni clínica
        <br>
        Documento generado:
        {{ $fechaGeneracion->format('d/m/Y H:i') }}
    </div>

    {{-- Encabezado institucional --}}
    <table class="encabezado">
        <tr>
            <td style="width: 75%;">
                <div class="marca">FarmaControl</div>

                <div class="subtitulo">
                    Sistema Web Integrado de Gestión Farmacéutica
                </div>

                <div class="subtitulo">
                    Proyecto Integrado · INACAP
                </div>
            </td>

            <td style="width: 25%; text-align: right;">
                @if ($logoInacap)
                    <img
                        src="{{ $logoInacap }}"
                        class="logo"
                        alt="Logo INACAP"
                    >
                @else
                    <strong>INACAP</strong>
                @endif
            </td>
        </tr>
    </table>

    {{-- Título del documento --}}
    <div class="titulo-documento">
        RECETA MÉDICA SIMULADA
    </div>

    <div class="codigo-receta">
        Código: {{ $receta->codigo_receta }}
    </div>

    {{-- Datos del paciente --}}
    <div class="seccion">

        <div class="titulo-seccion">
            01. Datos del paciente
        </div>

        <table class="datos">
            <tr>
                <td>
                    <div class="etiqueta">
                        Nombre completo
                    </div>

                    <div class="valor">
                        {{ $paciente?->nombres ?? '' }}
                        {{ $paciente?->apellidos ?? '' }}
                    </div>
                </td>

                <td>
                    <div class="etiqueta">RUT</div>

                    <div class="valor">
                        {{ $paciente?->rut ?? 'No registrado' }}
                    </div>
                </td>
            </tr>

            <tr>
                <td>
                    <div class="etiqueta">
                        Código del paciente
                    </div>

                    <div class="valor">
                        {{ $paciente?->codigo_paciente ?? 'No registrado' }}
                    </div>
                </td>

                <td>
                    <div class="etiqueta">
                        Fecha de nacimiento
                    </div>

                    <div class="valor">
                        @if ($paciente?->fecha_nacimiento)
                            {{ \Carbon\Carbon::parse(
                                $paciente->fecha_nacimiento
                            )->format('d/m/Y') }}
                        @else
                            No registrada
                        @endif
                    </div>
                </td>
            </tr>
        </table>
    </div>

    {{-- Alertas de alergias --}}
    <div class="seccion">

        <div class="titulo-seccion">
            02. Antecedentes actuales de alergias
        </div>

        @if ($estadoAlergias === 'con_alergias')

            <div class="alerta alerta-roja">

                <div class="alerta-titulo">
                    ALERTA: PACIENTE CON ALERGIAS CONOCIDAS
                </div>

                <strong>Antecedentes registrados:</strong>

                <p>
                    {{ $alergias ?? 'Descripción no registrada.' }}
                </p>

                Verificar los antecedentes con el profesional
                responsable antes de simular la dispensación.

            </div>

        @elseif ($estadoAlergias === 'sin_alergias')

            <div class="alerta alerta-verde">

                <div class="alerta-titulo">
                    SIN ALERGIAS CONOCIDAS REGISTRADAS
                </div>

                No se registran alergias conocidas en la ficha
                actual del paciente. Esto no garantiza la
                ausencia de alergias.

            </div>

        @else

            <div class="alerta alerta-amarilla">

                <div class="alerta-titulo">
                    PRECAUCIÓN: ESTADO DE ALERGIAS DESCONOCIDO
                </div>

                No existe información suficiente sobre los
                antecedentes de alergias del paciente.

                Se recomienda revisar y actualizar su ficha.

            </div>

        @endif

        <div style="font-size: 9px; color: #64748b;">
            Información consultada al generar este documento.
            El sistema no realiza una evaluación automática
            de compatibilidad clínica.
        </div>

    </div>

    {{-- Información de la receta --}}
    <div class="seccion">

        <div class="titulo-seccion">
            03. Información de la receta
        </div>

        <table class="datos">
            <tr>
                <td>
                    <div class="etiqueta">
                        Profesional prescriptor simulado
                    </div>

                    <div class="valor">
                        {{ $receta->profesional_prescriptor }}
                    </div>
                </td>

                <td>
                    <div class="etiqueta">
                        Estado actual
                    </div>

                    @if ($vigencia === 'vigente')

                        <span class="estado estado-vigente">
                            VIGENTE
                        </span>

                    @elseif ($vigencia === 'vencida')

                        <span class="estado estado-vencida">
                            VENCIDA
                        </span>

                    @elseif ($vigencia === 'utilizada')

                        <span class="estado estado-utilizada">
                            UTILIZADA
                        </span>

                    @elseif ($vigencia === 'anulada')

                        <span class="estado estado-anulada">
                            ANULADA
                        </span>

                    @else

                        <span class="estado estado-no-vigente">
                            NO VIGENTE
                        </span>

                    @endif
                </td>
            </tr>

            <tr>
                <td>
                    <div class="etiqueta">
                        Fecha de emisión
                    </div>

                    <div class="valor">
                        {{ \Carbon\Carbon::parse(
                            $receta->fecha_emision
                        )->format('d/m/Y') }}
                    </div>
                </td>

                <td>
                    <div class="etiqueta">
                        Fecha de vencimiento
                    </div>

                    <div class="valor">
                        {{ \Carbon\Carbon::parse(
                            $receta->fecha_vencimiento
                        )->format('d/m/Y') }}
                    </div>
                </td>
            </tr>
        </table>

    </div>

    {{-- Medicamentos prescritos --}}
    <div class="seccion">

        <div class="titulo-seccion">
            04. Medicamentos prescritos
        </div>

        <table class="tabla-medicamentos">

            <thead>
                <tr>
                    <th style="width: 5%;">N.º</th>

                    <th style="width: 28%;">
                        Medicamento
                    </th>

                    <th style="width: 18%;">
                        Concentración
                    </th>

                    <th style="width: 12%;">
                        Cantidad
                    </th>

                    <th style="width: 37%;">
                        Indicaciones
                    </th>
                </tr>
            </thead>

            <tbody>

                @forelse ($receta->detalles as $detalle)

                    <tr class="{{ $loop->even
                        ? 'fila-alternativa'
                        : '' }}">

                        <td>
                            {{ $loop->iteration }}
                        </td>

                        <td>
                            <strong>
                                {{ $detalle->medicamento?->nombre
                                    ?? 'No disponible' }}
                            </strong>

                            <br>

                            <span style="font-size: 9px; color: #64748b;">
                                {{ $detalle->medicamento?->forma_farmaceutica
                                    ?? '' }}
                            </span>
                        </td>

                        <td>
                            {{ $detalle->medicamento?->concentracion
                                ?? 'No registrada' }}
                        </td>

                        <td class="centrado">
                            {{ $detalle->cantidad_prescrita }}
                        </td>

                        <td>
                            {{ $detalle->indicaciones
                                ?? 'Sin indicaciones registradas' }}
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

    </div>

    {{-- Observaciones --}}
    <div class="seccion">

        <div class="titulo-seccion">
            05. Observaciones
        </div>

        <div class="observaciones">
            {{ $receta->observaciones
                ?? 'Sin observaciones adicionales.' }}
        </div>

    </div>

    {{-- Aviso obligatorio de uso académico --}}
    <div class="aviso-academico">

        <strong>
            DOCUMENTO EXCLUSIVAMENTE DE USO ACADÉMICO
        </strong>

        <br><br>

        Este documento ha sido generado mediante FarmaControl,
        sistema desarrollado para fines educativos en INACAP.

        <br>

        Corresponde a una simulación académica y no constituye
        una receta médica real.

        <br>

        <strong>
            Sin validez legal, clínica ni comercial.
        </strong>

    </div>

</body>
</html>
