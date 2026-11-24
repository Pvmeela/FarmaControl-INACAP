<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">

    <title>
        {{ $tituloComprobante }} - FarmaControl
    </title>

    <style>
        @page {
            size: A4 portrait;
            margin: 35px 40px 65px 40px;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11px;
            color: #334155;
            margin: 0;
            line-height: 1.5;
        }

        /* MARCA DE AGUA */

        .marca-agua {
            position: fixed;
            top: 36%;
            left: -10%;
            width: 120%;
            text-align: center;
            transform: rotate(-35deg);
            opacity: 0.075;
            color: #172554;
            font-size: 58px;
            font-weight: bold;
            line-height: 1.15;
            z-index: -1000;
        }

        .marca-agua-institucion {
            font-size: 32px;
            letter-spacing: 8px;
            margin-top: 12px;
        }

        /* ENCABEZADO */

        .encabezado {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 3px solid #172554;
            margin-bottom: 22px;
        }

        .encabezado td {
            vertical-align: middle;
            padding-bottom: 16px;
        }

        .marca {
            font-size: 27px;
            font-weight: bold;
            color: #172554;
            letter-spacing: -1px;
        }

        .descripcion {
            font-size: 9px;
            color: #64748b;
            margin-top: 3px;
        }

        .entorno {
            font-size: 10px;
            font-weight: bold;
            color: #1d4ed8;
            margin-top: 5px;
        }

        .logo-inacap {
            width: 115px;
            height: auto;
        }

        .logo-alternativo {
            color: #e30613;
            font-size: 24px;
            font-weight: bold;
        }

        .logo-contenedor {
            width: 135px;
            text-align: right;
        }

        /* TITULO DEL DOCUMENTO */

        .titulo-documento {
            text-align: center;
            margin: 20px 0;
        }

        .titulo-documento h1 {
            margin: 0;
            font-size: 23px;
            color: #172554;
            letter-spacing: 1px;
        }

        .folio {
            font-size: 12px;
            color: #64748b;
            margin-top: 7px;
        }

        /* AVISO ACADEMICO */

        .aviso-academico {
            border: 1px solid #fca5a5;
            border-left: 4px solid #dc2626;
            background-color: #fef2f2;
            padding: 12px;
            text-align: center;
            margin-bottom: 24px;
        }

        .aviso-titulo {
            color: #b91c1c;
            font-weight: bold;
            font-size: 12px;
            letter-spacing: 0.5px;
        }

        .aviso-texto {
            color: #991b1b;
            font-size: 9px;
            margin-top: 5px;
        }

        /* SECCIONES */

        .titulo-seccion {
            color: #172554;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            border-bottom: 1px solid #cbd5e1;
            padding-bottom: 8px;
            margin-top: 23px;
            margin-bottom: 13px;
        }

        /* INFORMACION DE LA VENTA */

        .informacion {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .informacion td {
            width: 50%;
            padding: 9px 12px 12px 0;
            vertical-align: top;
        }

        .etiqueta {
            font-size: 9px;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }

        .valor {
            font-size: 11px;
            color: #172554;
            font-weight: bold;
            margin-top: 4px;
        }

        /* TABLA DE MEDICAMENTOS */

        .tabla-medicamentos {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 22px;
        }

        .tabla-medicamentos thead {
            display: table-header-group;
        }

        .tabla-medicamentos th {
            background-color: #172554;
            color: #ffffff;
            font-size: 9px;
            text-transform: uppercase;
            padding: 12px 9px;
            text-align: left;
        }

        .tabla-medicamentos td {
            padding: 13px 9px;
            border-bottom: 1px solid #e2e8f0;
            vertical-align: middle;
            font-size: 10px;
        }

        .tabla-medicamentos tr {
            page-break-inside: avoid;
        }

        .nombre-medicamento {
            font-weight: bold;
            color: #334155;
        }

        .codigo-medicamento {
            font-size: 8px;
            color: #64748b;
            margin-top: 4px;
        }

        .tabla-medicamentos .centrado {
            text-align: center;
        }

        .tabla-medicamentos .derecha {
            text-align: right;
        }

        .importe {
            color: #172554;
            font-weight: bold;
        }

        .sin-registros {
            text-align: center;
            color: #64748b;
            padding: 20px;
        }

        /* TOTALES E IVA */

        .bloque-totales {
            width: 100%;
            page-break-inside: avoid;
        }

        .tabla-totales {
            width: 55%;
            margin-left: auto;
            border-collapse: collapse;
        }

        .tabla-totales td {
            padding: 8px 10px;
            font-size: 11px;
        }

        .concepto {
            color: #475569;
        }

        .monto {
            text-align: right;
            font-weight: bold;
            color: #172554;
        }

        .separacion-iva td {
            border-top: 1px solid #cbd5e1;
            padding-top: 12px;
        }

        .fila-iva td {
            color: #1d4ed8;
        }

        .total-final td {
            background-color: #eff6ff;
            border-top: 2px solid #172554;
            border-bottom: 2px solid #172554;
            padding: 15px 10px;
            color: #172554;
            font-weight: bold;
            font-size: 14px;
        }

        .total-final .monto {
            font-size: 21px;
        }

        .nota-iva {
            text-align: right;
            color: #64748b;
            font-size: 8px;
            margin-top: 9px;
        }

        /* INFORMACION ACADEMICA */

        .nota-academica {
            border: 1px solid #cbd5e1;
            border-left: 4px solid #1d4ed8;
            padding: 13px;
            margin-top: 30px;
            page-break-inside: avoid;
        }

        .nota-academica strong {
            color: #172554;
            font-size: 10px;
        }

        .nota-academica p {
            color: #64748b;
            font-size: 9px;
            margin: 5px 0 0;
            line-height: 1.7;
        }

        /* PIE DE PAGINA */

        .pie-pagina {
            position: fixed;
            bottom: -42px;
            left: 0;
            right: 0;
            border-top: 1px solid #cbd5e1;
            padding-top: 9px;
            text-align: center;
        }

        .pie-titulo {
            font-size: 9px;
            color: #172554;
            font-weight: bold;
        }

        .pie-texto {
            color: #64748b;
            font-size: 8px;
            margin-top: 3px;
        }

        .pie-advertencia {
            color: #b91c1c;
            font-size: 8px;
            font-weight: bold;
            margin-top: 3px;
        }
    </style>
</head>

<body>

    @php
        $dinero = function ($valor) {
            $importe = (float) ($valor ?? 0);

            $decimales = abs(
                $importe - round($importe)
            ) > 0.00001 ? 2 : 0;

            return '$' . number_format(
                $importe,
                $decimales,
                ',',
                '.'
            );
        };

        $folio = str_pad(
            (string) $venta->id,
            6,
            '0',
            STR_PAD_LEFT
        );

        $fechaVenta = $venta->fecha_venta
            ? \Carbon\Carbon::parse(
                $venta->fecha_venta
            )->format('d/m/Y H:i')
            : 'No disponible';
    @endphp

    <!-- MARCA DE AGUA -->

    <div class="marca-agua">
        SIMULACIÓN
        <br>
        ACADÉMICA

        <div class="marca-agua-institucion">
            INACAP
        </div>
    </div>

    <!-- PIE DE PAGINA -->

    <footer class="pie-pagina">

        <div class="pie-titulo">
            FarmaControl — Proyecto Integrado INACAP
        </div>

        <div class="pie-texto">
            Documento generado el
            {{ $fechaGeneracion->format('d/m/Y H:i') }}
        </div>

        <div class="pie-advertencia">
            USO ACADÉMICO — SIN VALIDEZ LEGAL NI TRIBUTARIA
        </div>

    </footer>

    <!-- ENCABEZADO -->

    <table class="encabezado">
        <tr>
            <td>
                <div class="marca">
                    FarmaControl
                </div>

                <div class="descripcion">
                    Sistema Web Integrado de Gestión Farmacéutica
                </div>

                <div class="entorno">
                    Farmacia Privada
                </div>
            </td>

            <td class="logo-contenedor">

                @if ($logoInacap)

                    <img
                        src="{{ $logoInacap }}"
                        class="logo-inacap"
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

    <!-- IDENTIFICACION DEL DOCUMENTO -->

    <div class="titulo-documento">

        <h1>
            {{ $tituloComprobante }}
        </h1>

        <div class="folio">
            Comprobante N.º {{ $folio }}
        </div>

    </div>

    <!-- ADVERTENCIA OBLIGATORIA -->

    <div class="aviso-academico">

        <div class="aviso-titulo">
            USO ACADÉMICO INACAP
        </div>

        <div class="aviso-titulo">
            SIN VALIDEZ TRIBUTARIA
        </div>

        <div class="aviso-texto">
            Documento de simulación académica.
            No corresponde a un documento tributario
            electrónico autorizado por el SII.
        </div>

    </div>

    <!-- INFORMACION DE LA VENTA -->

    <div class="titulo-seccion">
        Información de la venta
    </div>

    <table class="informacion">

        <tr>
            <td>
                <div class="etiqueta">
                    Número de operación
                </div>

                <div class="valor">
                    #{{ $folio }}
                </div>
            </td>

            <td>
                <div class="etiqueta">
                    Fecha de emisión simulada
                </div>

                <div class="valor">
                    {{ $fechaVenta }}
                </div>
            </td>
        </tr>

        <tr>
            <td>
                <div class="etiqueta">
                    Responsable de la venta
                </div>

                <div class="valor">
                    {{ $venta->usuario?->name ?? 'No disponible' }}
                </div>
            </td>

            <td>
                <div class="etiqueta">
                    Tipo de comprobante
                </div>

                <div class="valor">
                    {{ $tituloComprobante }}
                </div>
            </td>
        </tr>

    </table>

    <!-- DETALLE DE MEDICAMENTOS -->

    <div class="titulo-seccion">
        Detalle de medicamentos
    </div>

    <table class="tabla-medicamentos">

        <thead>
            <tr>
                <th style="width: 43%;">
                    Medicamento
                </th>

                <th
                    class="centrado"
                    style="width: 12%;"
                >
                    Cant.
                </th>

                <th
                    class="derecha"
                    style="width: 23%;"
                >
                    Precio unitario
                </th>

                <th
                    class="derecha"
                    style="width: 22%;"
                >
                    Importe
                </th>
            </tr>
        </thead>

        <tbody>

            @forelse ($venta->detalles as $detalle)

                <tr>
                    <td>
                        <div class="nombre-medicamento">
                            {{
                                $detalle->medicamento?->nombre
                                ?? 'Medicamento no disponible'
                            }}
                        </div>

                        @if ($detalle->medicamento?->codigo)

                            <div class="codigo-medicamento">
                                Código:
                                {{ $detalle->medicamento->codigo }}
                            </div>

                        @endif
                    </td>

                    <td class="centrado">
                        {{ $detalle->cantidad }}
                    </td>

                    <td class="derecha">
                        {{ $dinero($detalle->precio_unitario) }}
                    </td>

                    <td class="derecha importe">
                        {{ $dinero($detalle->subtotal) }}
                    </td>
                </tr>

            @empty

                <tr>
                    <td
                        colspan="4"
                        class="sin-registros"
                    >
                        No hay medicamentos asociados
                        a esta venta.
                    </td>
                </tr>

            @endforelse

        </tbody>

    </table>

    <!-- RESUMEN DE IMPORTES -->

    <div class="bloque-totales">

        <table class="tabla-totales">

            <tr>
                <td class="concepto">
                    Subtotal (IVA incluido)
                </td>

                <td class="monto">
                    {{ $dinero($totales['subtotal']) }}
                </td>
            </tr>

            <tr>
                <td class="concepto">
                    Descuento
                </td>

                <td class="monto">
                    {{ $dinero($totales['descuento']) }}
                </td>
            </tr>

            <tr class="separacion-iva">
                <td class="concepto">
                    Monto neto
                </td>

                <td class="monto">
                    {{ $dinero($totales['neto']) }}
                </td>
            </tr>

            <tr class="fila-iva">
                <td class="concepto">
                    IVA ({{ $totales['tasa_iva'] }}%)
                </td>

                <td class="monto">
                    {{ $dinero($totales['iva']) }}
                </td>
            </tr>

            <tr class="total-final">
                <td>
                    TOTAL
                </td>

                <td class="monto">
                    {{ $dinero($totales['total']) }}
                </td>
            </tr>

        </table>

        <div class="nota-iva">
            Importes expresados en pesos chilenos (CLP).
            <br>
            IVA incluido en los precios de venta.
        </div>

    </div>

    <!-- NOTA ACADEMICA -->

    <div class="nota-academica">

        <strong>
            Información sobre la simulación
        </strong>

        <p>
            Este comprobante ha sido generado por FarmaControl
            exclusivamente para fines de aprendizaje y
            demostración académica en INACAP.
        </p>

        <p>
            El desglose del IVA del 19% corresponde al supuesto
            tributario utilizado en la simulación. Los precios
            incluyen el impuesto, por lo que este no se agrega
            nuevamente al total registrado.
        </p>

        <p>
            Este documento no acredita una transacción comercial
            real ni posee validez legal o tributaria.
        </p>

    </div>

</body>
</html>