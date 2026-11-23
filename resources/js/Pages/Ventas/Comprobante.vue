<script setup>
import { computed } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
    venta: {
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

const subtotal = computed(() => {
    return detalles.value.reduce(
        (suma, detalle) =>
            suma + Number(detalle.subtotal || 0),
        0
    )
})

const descuento = computed(
    () => Number(props.venta.descuento || 0)
)

const total = computed(
    () => Number(props.venta.total || 0)
)

function imprimir() {
    window.print()
}

function volverVentas() {
    router.visit('/ventas')
}
</script>

<template>
    <div class="min-h-screen bg-slate-100 px-4 py-8 text-slate-800 print:bg-white print:p-0">

        <div class="mx-auto max-w-3xl">

            <!-- BOTONES SUPERIORES -->
            <div
                class="mb-6 flex flex-wrap items-center justify-between gap-3 print:hidden"
            >
                <button
                    type="button"
                    @click="volverVentas"
                    class="rounded-xl border border-slate-300 bg-white px-5 py-3 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50"
                >
                    ← Volver a ventas
                </button>

                <button
                    type="button"
                    @click="imprimir"
                    class="inline-flex items-center gap-2 rounded-xl bg-blue-800 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-900"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        width="18"
                        height="18"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        aria-hidden="true"
                    >
                        <path d="M6 9V2h12v7" />
                        <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2" />
                        <path d="M6 14h12v8H6z" />
                    </svg>

                    Imprimir / Guardar PDF
                </button>
            </div>

            <!-- DOCUMENTO -->
            <article
                class="rounded-2xl border border-slate-200 bg-white p-6 shadow-lg sm:p-10 print:rounded-none print:border-0 print:p-0 print:shadow-none"
            >

                <!-- ENCABEZADO -->
                <header class="border-b border-slate-200 pb-7 text-center">

                    <h1 class="text-3xl font-extrabold text-blue-900">
                        FarmaControl
                    </h1>

                    <p class="mt-2 text-sm text-slate-500">
                        Sistema Web Integrado de Gestión Farmacéutica
                    </p>

                    <h2 class="mt-6 text-xl font-bold text-slate-900">
                        {{ tituloComprobante }}
                    </h2>

                    <p class="mt-2 text-sm text-slate-500">
                        Comprobante N.º {{ venta.id }}
                    </p>

                    <!-- ADVERTENCIA OBLIGATORIA -->
                    <div
                        class="mt-6 rounded-xl border-2 border-red-500 bg-red-50 px-4 py-4 text-center print:border-red-500"
                    >
                        <p class="text-base font-extrabold uppercase text-red-700">
                            USO ACADÉMICO INACAP
                        </p>

                        <p class="mt-1 text-sm font-bold uppercase text-red-700">
                            SIN VALIDEZ TRIBUTARIA
                        </p>

                        <p class="mt-2 text-xs text-red-600">
                            Documento de simulación.
                            No corresponde a un documento tributario
                            electrónico emitido por el SII.
                        </p>
                    </div>

                </header>

                <!-- INFORMACIÓN DE LA VENTA -->
                <section class="grid gap-5 border-b border-slate-200 py-7 sm:grid-cols-2">

                    <div>
                        <p
                            class="text-xs font-semibold uppercase tracking-wide text-slate-500"
                        >
                            Número de operación
                        </p>

                        <p class="mt-1 font-semibold text-slate-900">
                            #{{ venta.id }}
                        </p>
                    </div>

                    <div>
                        <p
                            class="text-xs font-semibold uppercase tracking-wide text-slate-500"
                        >
                            Fecha de emisión simulada
                        </p>

                        <p class="mt-1 font-semibold text-slate-900">
                            {{ fecha(venta.fecha_venta) }}
                        </p>
                    </div>

                    <div>
                        <p
                            class="text-xs font-semibold uppercase tracking-wide text-slate-500"
                        >
                            Responsable
                        </p>

                        <p class="mt-1 font-semibold text-slate-900">
                            {{ venta.usuario?.name || 'No disponible' }}
                        </p>
                    </div>

                    <div>
                        <p
                            class="text-xs font-semibold uppercase tracking-wide text-slate-500"
                        >
                            Tipo de operación
                        </p>

                        <p class="mt-1 font-semibold text-slate-900">
                            {{ tituloComprobante }}
                        </p>
                    </div>

                </section>

                <!-- DETALLE DE MEDICAMENTOS -->
                <section class="py-7">

                    <h3 class="mb-4 text-lg font-bold text-slate-900">
                        Detalle de medicamentos
                    </h3>

                    <div class="overflow-x-auto">

                        <table class="w-full text-left text-sm">

                            <thead>
                                <tr class="border-b-2 border-slate-200 text-slate-600">
                                    <th class="px-2 py-3">
                                        Medicamento
                                    </th>

                                    <th class="px-2 py-3 text-center">
                                        Cant.
                                    </th>

                                    <th class="px-2 py-3 text-right">
                                        Precio
                                    </th>

                                    <th class="px-2 py-3 text-right">
                                        Subtotal
                                    </th>
                                </tr>
                            </thead>

                            <tbody>
                                <tr
                                    v-for="detalle in detalles"
                                    :key="detalle.id"
                                    class="border-b border-slate-100"
                                >
                                    <td class="px-2 py-4 font-medium">
                                        {{
                                            detalle.medicamento?.nombre ||
                                            'Medicamento no disponible'
                                        }}
                                    </td>

                                    <td class="px-2 py-4 text-center">
                                        {{ detalle.cantidad }}
                                    </td>

                                    <td class="px-2 py-4 text-right">
                                        {{ dinero(detalle.precio_unitario) }}
                                    </td>

                                    <td class="px-2 py-4 text-right font-semibold">
                                        {{ dinero(detalle.subtotal) }}
                                    </td>
                                </tr>

                                <tr v-if="!detalles.length">
                                    <td
                                        colspan="4"
                                        class="px-2 py-8 text-center text-slate-500"
                                    >
                                        No hay medicamentos asociados
                                        a esta venta.
                                    </td>
                                </tr>
                            </tbody>

                        </table>
                    </div>

                </section>

                <!-- TOTALES -->
                <section class="border-t border-slate-200 pt-6">

                    <div class="ml-auto max-w-sm space-y-4">

                        <div class="flex justify-between text-sm">
                            <span class="text-slate-600">
                                Subtotal
                            </span>

                            <span class="font-semibold">
                                {{ dinero(subtotal) }}
                            </span>
                        </div>

                        <div class="flex justify-between text-sm">
                            <span class="text-slate-600">
                                Descuento
                            </span>

                            <span class="font-semibold">
                                {{ dinero(descuento) }}
                            </span>
                        </div>

                        <div
                            class="flex items-center justify-between border-t-2 border-slate-200 pt-4"
                        >
                            <span class="text-lg font-bold text-slate-900">
                                TOTAL
                            </span>

                            <span class="text-2xl font-extrabold text-blue-900">
                                {{ dinero(total) }}
                            </span>
                        </div>

                    </div>

                </section>

                <!-- PIE DEL DOCUMENTO -->
                <footer class="mt-10 border-t border-slate-200 pt-6 text-center">

                    <p class="text-sm font-semibold text-slate-700">
                        FarmaControl — Proyecto Integrado INACAP
                    </p>

                    <p class="mt-2 text-xs text-slate-500">
                        Documento generado exclusivamente para
                        demostración académica.
                    </p>

                    <p class="mt-2 text-xs font-bold uppercase text-red-700">
                        USO ACADÉMICO INACAP — SIN VALIDEZ TRIBUTARIA
                    </p>

                </footer>

            </article>

            <!-- NOTA INFERIOR -->
            <p class="mt-5 text-center text-xs text-slate-500 print:hidden">
                Puedes imprimir este comprobante o guardarlo
                como PDF desde tu navegador.
            </p>

        </div>

    </div>
</template>

<style>
@media print {
    @page {
        size: A4;
        margin: 15mm;
    }

    body {
        background: white !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }

    article {
        break-inside: auto;
    }

    tr {
        break-inside: avoid;
    }
}
</style>
