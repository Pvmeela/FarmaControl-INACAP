<script setup>
import { computed } from 'vue'
import { Head, Link } from '@inertiajs/vue3'

const props = defineProps({
    venta: {
        type: Object,
        required: true,
    },

    totales: {
        type: Object,
        required: true,
    },
})

const esFactura = computed(
    () => props.venta.tipo_comprobante === 'factura'
)

const tituloComprobante = computed(
    () => esFactura.value
        ? 'FACTURA SIMULADA'
        : 'BOLETA SIMULADA'
)

const detalles = computed(
    () => props.venta.detalles || []
)

const folio = computed(
    () => String(props.venta.id).padStart(6, '0')
)

const urlPdf = computed(
    () => `/ventas/${props.venta.id}/pdf`
)

const urlDescarga = computed(
    () => `/ventas/${props.venta.id}/pdf/descargar`
)

const dinero = valor => {
    return new Intl.NumberFormat('es-CL', {
        style: 'currency',
        currency: 'CLP',
        minimumFractionDigits: 0,
        maximumFractionDigits: 2,
    }).format(Number(valor) || 0)
}

const fecha = valor => {
    if (!valor) return 'No disponible'

    const resultado = new Date(valor)

    if (Number.isNaN(resultado.getTime())) {
        return 'No disponible'
    }

    return resultado.toLocaleString('es-CL', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    })
}

function imprimir() {
    window.print()
}
</script>

<template>
    <Head :title="`${tituloComprobante} | FarmaControl`" />

    <div class="pagina-comprobante" data-entorno="privada">

        <div class="contenedor">

            <!-- BARRA SUPERIOR -->

            <div class="barra-acciones">

                <Link href="/ventas" class="boton volver">

                    <svg viewBox="0 0 24 24" fill="none">
                        <path d="M19 12H5M12 19l-7-7 7-7" />
                    </svg>

                    Volver a ventas

                </Link>

                <div class="acciones-documento">

                    <a
                        :href="urlPdf"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="boton secundario"
                    >
                        <svg viewBox="0 0 24 24" fill="none">
                            <path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12Z" />
                            <circle cx="12" cy="12" r="3" />
                        </svg>

                        Ver PDF
                    </a>

                    <button
                        type="button"
                        class="boton secundario"
                        @click="imprimir"
                    >
                        <svg viewBox="0 0 24 24" fill="none">
                            <path d="M6 9V3h12v6M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2" />
                            <path d="M6 14h12v8H6z" />
                        </svg>

                        Imprimir
                    </button>

                    <a
                        :href="urlDescarga"
                        class="boton principal"
                    >
                        <svg viewBox="0 0 24 24" fill="none">
                            <path d="M12 3v12m-5-5 5 5 5-5M4 16v5h16v-5" />
                        </svg>

                        Descargar PDF
                    </a>

                </div>

            </div>

            <!-- INFORMACIÓN SUPERIOR -->

            <div class="informacion-pagina">

                <div>
                    <span class="etiqueta-superior">
                        DOCUMENTACIÓN DE VENTAS
                    </span>

                    <h1>Comprobante de venta</h1>

                    <p>
                        Consulta, imprime o descarga el documento
                        asociado a esta operación.
                    </p>
                </div>

                <span class="estado-documento">
                    <span class="punto-estado"></span>
                    Venta registrada
                </span>

            </div>

            <!-- DOCUMENTO -->

            <article class="documento">

                <!-- MARCA DE AGUA -->

                <div class="marca-agua" aria-hidden="true">

                    <span>SIMULACIÓN</span>

                    <span>ACADÉMICA</span>

                    <small>INACAP</small>

                </div>

                <div class="contenido-documento">

                    <!-- ENCABEZADO INSTITUCIONAL -->

                    <header class="encabezado">

                        <div class="identidad">

                            <div class="marca-farmacontrol">

                                <div class="icono-marca">
                                    <svg
                                        viewBox="0 0 24 24"
                                        fill="none"
                                    >
                                        <path d="M9 3h6v6h6v6h-6v6H9v-6H3V9h6z" />
                                    </svg>
                                </div>

                                <span>FarmaControl</span>

                            </div>

                            <p class="descripcion-marca">
                                Sistema Web Integrado de
                                Gestión Farmacéutica
                            </p>

                            <p class="entorno">
                                Farmacia Privada
                            </p>

                        </div>

                        <img
                            :src="'/images/logo-inacap.png'"
                            alt="Logo INACAP"
                            class="logo-inacap"
                        />

                    </header>

                    <!-- TITULO -->

                    <div class="titulo-documento">

                        <span class="tipo-documento">
                            DOCUMENTO ACADÉMICO
                        </span>

                        <h2>{{ tituloComprobante }}</h2>

                        <p>
                            Comprobante N.º {{ folio }}
                        </p>

                    </div>

                    <!-- ADVERTENCIA -->

                    <div class="aviso-academico">

                        <svg viewBox="0 0 24 24" fill="none">
                            <circle cx="12" cy="12" r="9" />
                            <path d="M12 8v5m0 3h.01" />
                        </svg>

                        <div>

                            <strong>
                                USO ACADÉMICO INACAP
                            </strong>

                            <span>
                                SIN VALIDEZ TRIBUTARIA
                            </span>

                            <p>
                                Documento de simulación.
                                No corresponde a un documento
                                tributario electrónico autorizado
                                por el SII.
                            </p>

                        </div>

                    </div>

                    <!-- DATOS DE LA VENTA -->

                    <section class="seccion">

                        <h3 class="titulo-seccion">
                            Información de la venta
                        </h3>

                        <div class="datos-venta">

                            <div class="dato">
                                <span>Número de operación</span>

                                <strong>
                                    #{{ folio }}
                                </strong>
                            </div>

                            <div class="dato">
                                <span>Fecha de emisión simulada</span>

                                <strong>
                                    {{ fecha(venta.fecha_venta) }}
                                </strong>
                            </div>

                            <div class="dato">
                                <span>Responsable de la venta</span>

                                <strong>
                                    {{
                                        venta.usuario?.name
                                        || 'No disponible'
                                    }}
                                </strong>
                            </div>

                            <div class="dato">
                                <span>Tipo de comprobante</span>

                                <strong>
                                    {{
                                        esFactura
                                            ? 'Factura simulada'
                                            : 'Boleta simulada'
                                    }}
                                </strong>
                            </div>

                        </div>

                    </section>

                    <!-- MEDICAMENTOS -->

                    <section class="seccion">

                        <h3 class="titulo-seccion">
                            Detalle de medicamentos
                        </h3>

                        <div class="tabla-contenedor">

                            <table class="tabla-medicamentos">

                                <thead>
                                    <tr>
                                        <th>Medicamento</th>

                                        <th class="centrado">
                                            Cant.
                                        </th>

                                        <th class="derecha">
                                            Precio unitario
                                        </th>

                                        <th class="derecha">
                                            Importe
                                        </th>
                                    </tr>
                                </thead>

                                <tbody>

                                    <tr
                                        v-for="detalle in detalles"
                                        :key="detalle.id"
                                    >

                                        <td>
                                            <strong>
                                                {{
                                                    detalle.medicamento?.nombre
                                                    || 'Medicamento no disponible'
                                                }}
                                            </strong>

                                            <small
                                                v-if="detalle.medicamento?.codigo"
                                                class="codigo-medicamento"
                                            >
                                                Código:
                                                {{ detalle.medicamento.codigo }}
                                            </small>
                                        </td>

                                        <td class="centrado">
                                            {{ detalle.cantidad }}
                                        </td>

                                        <td class="derecha">
                                            {{
                                                dinero(
                                                    detalle.precio_unitario
                                                )
                                            }}
                                        </td>

                                        <td class="derecha importe">
                                            {{
                                                dinero(detalle.subtotal)
                                            }}
                                        </td>

                                    </tr>

                                    <tr v-if="!detalles.length">
                                        <td
                                            colspan="4"
                                            class="sin-registros"
                                        >
                                            No hay medicamentos
                                            asociados a esta venta.
                                        </td>
                                    </tr>

                                </tbody>

                            </table>

                        </div>

                    </section>

                    <!-- RESUMEN E IVA -->

                    <section class="seccion resumen-venta">

                        <div class="resumen-importes">

                            <div class="fila-total">

                                <span>
                                    Subtotal (IVA incluido)
                                </span>

                                <strong>
                                    {{ dinero(totales.subtotal) }}
                                </strong>

                            </div>

                            <div class="fila-total">

                                <span>Descuento</span>

                                <strong>
                                    {{ dinero(totales.descuento) }}
                                </strong>

                            </div>

                            <div class="separador"></div>

                            <div class="fila-total">

                                <span>Monto neto</span>

                                <strong>
                                    {{ dinero(totales.neto) }}
                                </strong>

                            </div>

                            <div class="fila-total fila-iva">

                                <span>
                                    IVA ({{ totales.tasa_iva }} %)
                                </span>

                                <strong>
                                    {{ dinero(totales.iva) }}
                                </strong>

                            </div>

                            <div class="total-final">

                                <div>
                                    <span>TOTAL A PAGAR</span>

                                    <small>IVA incluido</small>
                                </div>

                                <strong>
                                    {{ dinero(totales.total) }}
                                </strong>

                            </div>

                            <p class="nota-moneda">
                                Importes expresados en pesos
                                chilenos (CLP).
                            </p>

                        </div>

                    </section>

                    <!-- NOTA INFORMATIVA -->

                    <div class="nota-academica">

                        <strong>
                            Información sobre la simulación
                        </strong>

                        <p>
                            Este comprobante ha sido generado
                            exclusivamente para fines de aprendizaje
                            y demostración académica en INACAP.
                        </p>

                        <p>
                            El desglose del IVA del 19 % corresponde
                            al supuesto tributario utilizado en la
                            simulación. Los precios incluyen el
                            impuesto, por lo que no se agrega
                            nuevamente al total registrado.
                        </p>

                        <p>
                            Este documento no acredita una
                            transacción comercial real.
                        </p>

                    </div>

                    <!-- PIE DEL DOCUMENTO -->

                    <footer class="pie-documento">

                        <strong>
                            FarmaControl — Proyecto Integrado INACAP
                        </strong>

                        <p>
                            Sistema de simulación académica
                            de gestión farmacéutica.
                        </p>

                        <span>
                            SIN VALIDEZ LEGAL NI TRIBUTARIA
                        </span>

                    </footer>

                </div>

            </article>

            <!-- NOTA EXTERIOR -->

            <div class="nota-exterior">

                <svg viewBox="0 0 24 24" fill="none">
                    <circle cx="12" cy="12" r="9" />
                    <path d="M12 11v5m0-8h.01" />
                </svg>

                El comprobante PDF conserva el diseño institucional,
                el desglose del IVA y la marca de agua académica.

            </div>

        </div>

    </div>
</template>

<style scoped>

.pagina-comprobante {
    min-height: 100vh;
    padding: 32px 24px 60px;
    background: #f1f5f9;
    color: #334155;
    font-family: 'Inter', sans-serif;
}

.contenedor {
    max-width: 960px;
    margin: 0 auto;
}

/* BARRA DE ACCIONES */

.barra-acciones {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 14px;
    flex-wrap: wrap;
    margin-bottom: 30px;
}

.acciones-documento {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.boton {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 9px;
    padding: 12px 17px;
    border-radius: 10px;
    font-size: 13px;
    font-weight: 600;
    text-decoration: none;
    cursor: pointer;
    transition: all 0.2s ease;
    border: 1px solid transparent;
}

.boton svg,
.aviso-academico svg,
.nota-exterior svg {
    width: 18px;
    height: 18px;
    stroke: currentColor;
    stroke-width: 1.7;
    stroke-linecap: round;
    stroke-linejoin: round;
    flex-shrink: 0;
}

.volver {
    background: white;
    border-color: #cbd5e1;
    color: #475569;
}

.volver:hover {
    background: #f8fafc;
    color: #172554;
}

.secundario {
    background: white;
    border-color: #cbd5e1;
    color: #172554;
}

.secundario:hover {
    background: #eff6ff;
    border-color: #93c5fd;
}

.principal {
    background: #172554;
    color: white;
}

.principal:hover {
    background: #1e40af;
    transform: translateY(-1px);
}

/* INFORMACIÓN DE PÁGINA */

.informacion-pagina {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
    margin-bottom: 24px;
}

.etiqueta-superior {
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 1.5px;
    color: #1d4ed8;
}

.informacion-pagina h1 {
    font-size: 27px;
    font-weight: 800;
    color: #172554;
    margin: 7px 0;
}

.informacion-pagina p {
    font-size: 13px;
    color: #64748b;
}

.estado-documento {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 13px;
    border-radius: 30px;
    background: #ecfdf5;
    border: 1px solid #bbf7d0;
    color: #15803d;
    font-size: 11px;
    font-weight: 600;
    white-space: nowrap;
}

.punto-estado {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: #22c55e;
}

/* DOCUMENTO PRINCIPAL */

.documento {
    position: relative;
    isolation: isolate;
    overflow: hidden;
    min-height: 1050px;
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 5px;
    box-shadow: 0 12px 45px rgba(15, 23, 42, 0.08);
}

.contenido-documento {
    position: relative;
    z-index: 1;
    padding: 48px;
}

/* MARCA DE AGUA TRANSPARENTE */

.marca-agua {
    position: absolute;
    top: 50%;
    left: 50%;
    width: 110%;
    transform: translate(-50%, -50%) rotate(-35deg);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    color: #172554;
    opacity: 0.075;
    font-size: clamp(38px, 7.5vw, 84px);
    font-weight: 900;
    line-height: 1.1;
    letter-spacing: 3px;
    pointer-events: none;
    user-select: none;
    z-index: 0;
}

.marca-agua small {
    font-size: 0.45em;
    letter-spacing: 12px;
    margin-top: 15px;
}

/* ENCABEZADO */

.encabezado {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
    padding-bottom: 24px;
    border-bottom: 3px solid #172554;
}

.marca-farmacontrol {
    display: flex;
    align-items: center;
    gap: 10px;
    color: #172554;
    font-size: 29px;
    font-weight: 800;
    letter-spacing: -1px;
}

.icono-marca {
    width: 42px;
    height: 42px;
    border-radius: 11px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #172554;
    color: white;
}

.icono-marca svg {
    width: 23px;
    height: 23px;
    stroke: currentColor;
    stroke-width: 1.5;
    stroke-linejoin: round;
}

.descripcion-marca {
    margin-top: 7px;
    font-size: 11px;
    color: #64748b;
}

.entorno {
    margin-top: 5px;
    font-size: 12px;
    font-weight: 700;
    color: #1d4ed8;
}

.logo-inacap {
    width: 135px;
    max-width: 30%;
    height: auto;
    object-fit: contain;
}

/* TITULO DEL DOCUMENTO */

.titulo-documento {
    text-align: center;
    padding: 30px 0 25px;
}

.tipo-documento {
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 2px;
    color: #64748b;
}

.titulo-documento h2 {
    margin: 9px 0;
    font-size: 27px;
    font-weight: 800;
    color: #172554;
    letter-spacing: 0.7px;
}

.titulo-documento p {
    font-size: 13px;
    color: #64748b;
}

/* ADVERTENCIA */

.aviso-academico {
    display: flex;
    align-items: flex-start;
    gap: 13px;
    padding: 17px 20px;
    border: 1px solid #fecaca;
    border-left: 4px solid #dc2626;
    border-radius: 6px;
    background: rgba(254, 242, 242, 0.85);
    color: #b91c1c;
    margin-bottom: 30px;
}

.aviso-academico svg {
    width: 23px;
    height: 23px;
    margin-top: 2px;
}

.aviso-academico strong {
    display: block;
    font-size: 12px;
    font-weight: 800;
    letter-spacing: 0.5px;
}

.aviso-academico span {
    display: block;
    margin-top: 3px;
    font-size: 11px;
    font-weight: 700;
}

.aviso-academico p {
    margin-top: 7px;
    font-size: 11px;
    line-height: 1.6;
}

/* SECCIONES */

.seccion {
    margin-top: 27px;
}

.titulo-seccion {
    font-size: 13px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.7px;
    color: #172554;
    padding-bottom: 12px;
    border-bottom: 1px solid #cbd5e1;
    margin-bottom: 18px;
}

.datos-venta {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 24px;
}

.dato {
    display: flex;
    flex-direction: column;
    gap: 7px;
}

.dato span {
    font-size: 10px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #64748b;
}

.dato strong {
    font-size: 13px;
    color: #172554;
    font-weight: 600;
}

/* TABLA */

.tabla-contenedor {
    overflow-x: auto;
}

.tabla-medicamentos {
    width: 100%;
    border-collapse: collapse;
    font-size: 12px;
}

.tabla-medicamentos th {
    background: #172554;
    color: white;
    padding: 13px 12px;
    text-align: left;
    font-size: 10px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

.tabla-medicamentos th:first-child {
    border-radius: 5px 0 0 0;
}

.tabla-medicamentos th:last-child {
    border-radius: 0 5px 0 0;
}

.tabla-medicamentos td {
    padding: 17px 12px;
    border-bottom: 1px solid #e2e8f0;
    vertical-align: middle;
}

.tabla-medicamentos td strong {
    font-weight: 600;
}

.tabla-medicamentos .centrado {
    text-align: center;
}

.tabla-medicamentos .derecha {
    text-align: right;
}

.codigo-medicamento {
    display: block;
    margin-top: 5px;
    font-size: 10px;
    color: #64748b;
}

.importe {
    color: #172554;
    font-weight: 700;
}

.sin-registros {
    text-align: center;
    color: #64748b;
}

/* TOTALES */

.resumen-venta {
    display: flex;
    justify-content: flex-end;
}

.resumen-importes {
    width: 100%;
    max-width: 365px;
}

.fila-total {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 15px;
    padding: 10px 5px;
    font-size: 12px;
    color: #475569;
}

.fila-total strong {
    font-weight: 700;
    color: #172554;
}

.separador {
    border-top: 1px solid #cbd5e1;
    margin: 7px 0;
}

.fila-iva,
.fila-iva strong {
    color: #1d4ed8;
}

.total-final {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 15px;
    margin-top: 12px;
    padding: 18px 16px;
    border-top: 2px solid #172554;
    border-bottom: 2px solid #172554;
    background: rgba(239, 246, 255, 0.85);
    color: #172554;
}

.total-final span {
    display: block;
    font-size: 12px;
    font-weight: 800;
}

.total-final small {
    display: block;
    margin-top: 5px;
    font-size: 10px;
    color: #64748b;
}

.total-final strong {
    font-size: 26px;
    font-weight: 800;
    white-space: nowrap;
}

.nota-moneda {
    margin-top: 10px;
    text-align: right;
    font-size: 10px;
    color: #64748b;
}

/* NOTA ACADÉMICA */

.nota-academica {
    margin-top: 35px;
    padding: 17px 20px;
    border: 1px solid #cbd5e1;
    border-left: 4px solid #1d4ed8;
    border-radius: 5px;
    background: rgba(248, 250, 252, 0.75);
}

.nota-academica strong {
    font-size: 12px;
    color: #172554;
    font-weight: 700;
}

.nota-academica p {
    margin-top: 8px;
    font-size: 11px;
    color: #64748b;
    line-height: 1.7;
}

/* PIE DEL DOCUMENTO */

.pie-documento {
    margin-top: 35px;
    padding-top: 20px;
    border-top: 1px solid #cbd5e1;
    text-align: center;
}

.pie-documento strong {
    font-size: 11px;
    color: #172554;
    font-weight: 700;
}

.pie-documento p {
    font-size: 10px;
    color: #64748b;
    margin-top: 6px;
}

.pie-documento span {
    display: block;
    margin-top: 8px;
    font-size: 10px;
    color: #b91c1c;
    font-weight: 700;
}

.nota-exterior {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 9px;
    padding: 20px 10px;
    color: #64748b;
    font-size: 11px;
    text-align: center;
}

/* RESPONSIVE */

@media (max-width: 768px) {

    .pagina-comprobante {
        padding: 20px 12px 35px;
    }

    .contenido-documento {
        padding: 28px 22px;
    }

    .informacion-pagina {
        align-items: flex-start;
        flex-direction: column;
    }

    .informacion-pagina h1 {
        font-size: 23px;
    }

    .acciones-documento {
        width: 100%;
    }

    .boton {
        font-size: 12px;
        padding: 11px 13px;
    }

    .marca-farmacontrol {
        font-size: 22px;
    }

    .icono-marca {
        width: 34px;
        height: 34px;
    }

    .logo-inacap {
        width: 105px;
    }

    .titulo-documento h2 {
        font-size: 22px;
    }

    .tabla-medicamentos {
        min-width: 470px;
    }

    .marca-agua {
        font-size: clamp(32px, 8vw, 65px);
    }

}

@media (max-width: 480px) {

    .datos-venta {
        grid-template-columns: 1fr;
        gap: 18px;
    }

    .contenido-documento {
        padding: 22px 16px;
    }

    .marca-farmacontrol {
        font-size: 19px;
    }

    .descripcion-marca {
        font-size: 9px;
    }

    .total-final strong {
        font-size: 21px;
    }

}

/* IMPRESIÓN */

@media print {

    .pagina-comprobante {
        padding: 0;
        background: white;
        min-height: auto;
    }

    .barra-acciones,
    .informacion-pagina,
    .nota-exterior {
        display: none !important;
    }

    .contenedor {
        max-width: none;
        width: 100%;
        margin: 0;
    }

    .documento {
        border: none;
        border-radius: 0;
        box-shadow: none;
        min-height: auto;
        overflow: visible;
    }

    .contenido-documento {
        padding: 0;
    }

    .marca-agua {
        position: fixed;
        top: 50%;
        left: 50%;
        font-size: 70px;
        opacity: 0.075;
    }

    .tabla-contenedor {
        overflow: visible;
    }

    .tabla-medicamentos {
        min-width: 0;
    }

    .tabla-medicamentos thead {
        display: table-header-group;
    }

    .tabla-medicamentos tr,
    .resumen-venta,
    .nota-academica,
    .pie-documento {
        break-inside: avoid;
    }

    .datos-venta {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .pagina-comprobante {
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }

}

@media (prefers-reduced-motion: reduce) {
    .boton {
        transition: none;
    }
}

</style>

<style>
@media print {
    @page {
        size: A4 portrait;
        margin: 15mm;
    }

    body {
        background: white !important;
        margin: 0 !important;
    }
}
</style>