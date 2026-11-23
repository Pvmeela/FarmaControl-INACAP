
<script setup>
import { computed, ref } from 'vue'
import { router, useForm, usePage } from '@inertiajs/vue3'

const props = defineProps({
    ventas: {
        type: Array,
        default: () => [],
    },
    medicamentos: {
        type: Array,
        default: () => [],
    },
    flash: {
        type: Object,
        default: () => ({}),
    },
})

const page = usePage()
const busqueda = ref('')

const nuevoItem = () => ({
    medicamento_id: '',
    cantidad: 1,
    precio_unitario: '',
})

const form = useForm({
    tipo_comprobante: 'boleta',
    items: [nuevoItem()],
})

const mensajeExito = computed(
    () => props.flash?.success || page.props.flash?.success || ''
)

const dinero = valor =>
    new Intl.NumberFormat('es-CL', {
        style: 'currency',
        currency: 'CLP',
        minimumFractionDigits: 0,
        maximumFractionDigits: 2,
    }).format(Number(valor) || 0)

const fecha = valor => {
    if (!valor) return '—'

    const resultado = new Date(valor)

    if (Number.isNaN(resultado.getTime())) return '—'

    return resultado.toLocaleString('es-CL', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    })
}

const totalUnidades = computed(() =>
    props.ventas.reduce(
        (suma, venta) =>
            suma + (venta.detalles || []).reduce(
                (cantidad, detalle) =>
                    cantidad + Number(detalle.cantidad || 0),
                0
            ),
        0
    )
)

const montoAcumulado = computed(() =>
    props.ventas.reduce(
        (suma, venta) => suma + Number(venta.total || 0),
        0
    )
)

const totalCentavos = computed(() =>
    form.items.reduce((suma, item) => {
        return suma + subtotalCentavos(item)
    }, 0)
)

const total = computed(() => totalCentavos.value / 100)

const ventasFiltradas = computed(() => {
    const termino = busqueda.value.trim().toLowerCase()

    if (!termino) return props.ventas

    return props.ventas.filter(venta => {
        const nombres = (venta.detalles || [])
            .map(detalle => detalle.medicamento?.nombre || '')
            .join(' ')
            .toLowerCase()

        const responsable = String(
            venta.usuario?.name || ''
        ).toLowerCase()

        const comprobante = tipoComprobante(venta).toLowerCase()

        return (
            String(venta.id).includes(termino) ||
            nombres.includes(termino) ||
            responsable.includes(termino) ||
            comprobante.includes(termino)
        )
    })
})

function subtotalCentavos(item) {
    const cantidad = Number(item.cantidad)
    const precio = Number(item.precio_unitario)

    if (
        item.precio_unitario === '' ||
        !Number.isInteger(cantidad) ||
        cantidad < 1 ||
        !Number.isFinite(precio) ||
        precio < 0
    ) {
        return 0
    }

    return cantidad * Math.round(precio * 100)
}

function subtotal(item) {
    return subtotalCentavos(item) / 100
}

function agregarMedicamento() {
    if (
        form.processing ||
        form.items.length >= props.medicamentos.length
    ) {
        return
    }

    form.items.push(nuevoItem())
    form.clearErrors()
}

function quitarMedicamento(indice) {
    if (form.processing || form.items.length <= 1) return

    form.items.splice(indice, 1)
    form.clearErrors()
}

function medicamentoOcupado(id, indiceActual) {
    return form.items.some(
        (item, indice) =>
            indice !== indiceActual &&
            String(item.medicamento_id) === String(id)
    )
}

function medicamentoSeleccionado(id) {
    return props.medicamentos.find(
        medicamento => String(medicamento.id) === String(id)
    )
}

function tipoComprobante(venta) {
    return venta.tipo_comprobante === 'factura'
        ? 'Factura simulada'
        : 'Boleta simulada'
}

function resumenMedicamentos(venta) {
    const detalles = venta.detalles || []

    if (!detalles.length) return 'Sin detalles'

    return detalles
        .map(detalle => {
            const nombre =
                detalle.medicamento?.nombre || 'Medicamento'

            return `${nombre} (${detalle.cantidad})`
        })
        .join(', ')
}

function cantidadVenta(venta) {
    return (venta.detalles || []).reduce(
        (suma, detalle) => suma + Number(detalle.cantidad || 0),
        0
    )
}

function registrarVenta() {
    if (form.processing) return

    form.clearErrors()

    form.post('/ventas', {
        preserveScroll: true,
        onSuccess: () => {
            form.reset()
            form.items = [nuevoItem()]
            form.tipo_comprobante = 'boleta'
            form.clearErrors()
        },
    })
}

function verComprobante(venta) {
    router.visit(`/ventas/${venta.id}/comprobante`)
}

function volverDashboard() {
    router.visit('/dashboard')
}
</script>

<template>
    <div class="min-h-screen bg-slate-100 text-slate-800">
        <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6">

            <!-- ENCABEZADO -->
            <header
                class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
            >
                <div>
                    <p
                        class="text-sm font-semibold uppercase tracking-widest text-blue-700"
                    >
                        FarmaControl
                    </p>

                    <h1 class="mt-1 text-3xl font-bold text-slate-900">
                        Ventas simuladas
                    </h1>

                    <p class="mt-2 text-sm text-slate-500">
                        Registro de ventas, comprobantes académicos
                        y control de inventario mediante FEFO.
                    </p>
                </div>

                <button
                    type="button"
                    @click="volverDashboard"
                    class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-300 bg-white px-5 py-3 text-sm font-semibold text-slate-700 shadow-sm transition hover:border-blue-300 hover:bg-blue-50 hover:text-blue-800"
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
                        <rect x="3" y="3" width="7" height="7" rx="1" />
                        <rect x="14" y="3" width="7" height="7" rx="1" />
                        <rect x="3" y="14" width="7" height="7" rx="1" />
                        <rect x="14" y="14" width="7" height="7" rx="1" />
                    </svg>

                    Panel principal
                </button>
            </header>

            <!-- MENSAJE DE ÉXITO -->
            <div
                v-if="mensajeExito"
                class="mb-6 rounded-xl border border-green-200 bg-green-50 px-5 py-4 text-sm font-medium text-green-800"
            >
                {{ mensajeExito }}
            </div>

            <!-- INDICADORES -->
            <section class="mb-8 grid gap-4 sm:grid-cols-3">

                <div
                    class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"
                >
                    <p class="text-sm font-medium text-slate-500">
                        Ventas registradas
                    </p>

                    <p class="mt-3 text-3xl font-bold text-blue-800">
                        {{ ventas.length }}
                    </p>
                </div>

                <div
                    class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"
                >
                    <p class="text-sm font-medium text-slate-500">
                        Unidades vendidas
                    </p>

                    <p class="mt-3 text-3xl font-bold text-blue-800">
                        {{ totalUnidades }}
                    </p>
                </div>

                <div
                    class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"
                >
                    <p class="text-sm font-medium text-slate-500">
                        Total simulado acumulado
                    </p>

                    <p class="mt-3 text-3xl font-bold text-blue-800">
                        {{ dinero(montoAcumulado) }}
                    </p>
                </div>

            </section>

            <!-- FORMULARIO -->
            <section
                class="mb-8 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"
            >
                <div class="mb-6">
                    <h2 class="text-xl font-bold text-slate-900">
                        Registrar nueva venta
                    </h2>

                    <p class="mt-2 text-sm text-slate-500">
                        Selecciona uno o varios medicamentos.
                        El sistema validará el stock disponible
                        y aplicará el método FEFO.
                    </p>
                </div>

                <form @submit.prevent="registrarVenta">

                    <!-- TIPO DE COMPROBANTE -->
                    <div class="mb-6 max-w-sm">
                        <label
                            for="tipo_comprobante"
                            class="mb-2 block text-sm font-semibold text-slate-700"
                        >
                            Tipo de comprobante
                        </label>

                        <select
                            id="tipo_comprobante"
                            v-model="form.tipo_comprobante"
                            :disabled="form.processing"
                            required
                            class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100"
                        >
                            <option value="boleta">
                                Boleta simulada
                            </option>

                            <option value="factura">
                                Factura simulada
                            </option>
                        </select>

                        <p
                            v-if="form.errors.tipo_comprobante"
                            class="mt-2 text-xs text-red-600"
                        >
                            {{ form.errors.tipo_comprobante }}
                        </p>
                    </div>

                    <!-- MEDICAMENTOS -->
                    <div
                        v-for="(item, indice) in form.items"
                        :key="indice"
                        class="mb-4 rounded-xl border border-slate-200 bg-slate-50 p-5"
                    >
                        <div
                            class="mb-4 flex items-center justify-between gap-3"
                        >
                            <h3 class="font-semibold text-slate-800">
                                Medicamento {{ indice + 1 }}
                            </h3>

                            <button
                                v-if="form.items.length > 1"
                                type="button"
                                :disabled="form.processing"
                                @click="quitarMedicamento(indice)"
                                class="text-sm font-semibold text-red-600 hover:text-red-800 disabled:opacity-50"
                            >
                                Quitar medicamento
                            </button>
                        </div>

                        <div class="grid gap-4 md:grid-cols-3">

                            <!-- MEDICAMENTO -->
                            <div>
                                <label
                                    :for="`medicamento-${indice}`"
                                    class="mb-2 block text-sm font-semibold text-slate-700"
                                >
                                    Medicamento
                                </label>

                                <select
                                    :id="`medicamento-${indice}`"
                                    v-model="item.medicamento_id"
                                    :disabled="form.processing"
                                    required
                                    class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100"
                                >
                                    <option value="">
                                        Seleccionar medicamento
                                    </option>

                                    <option
                                        v-for="medicamento in medicamentos"
                                        :key="medicamento.id"
                                        :value="medicamento.id"
                                        :disabled="medicamentoOcupado(medicamento.id, indice)"
                                    >
                                        {{ medicamento.nombre }}
                                        ({{ medicamento.codigo }})
                                    </option>
                                </select>

                                <p
                                    v-if="form.errors[`items.${indice}.medicamento_id`]"
                                    class="mt-2 text-xs text-red-600"
                                >
                                    {{
                                        form.errors[
                                            `items.${indice}.medicamento_id`
                                        ]
                                    }}
                                </p>
                            </div>

                            <!-- CANTIDAD -->
                            <div>
                                <label
                                    :for="`cantidad-${indice}`"
                                    class="mb-2 block text-sm font-semibold text-slate-700"
                                >
                                    Cantidad
                                </label>

                                <input
                                    :id="`cantidad-${indice}`"
                                    v-model.number="item.cantidad"
                                    :disabled="form.processing"
                                    type="number"
                                    min="1"
                                    step="1"
                                    required
                                    class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100"
                                />

                                <p
                                    v-if="form.errors[`items.${indice}.cantidad`]"
                                    class="mt-2 text-xs text-red-600"
                                >
                                    {{
                                        form.errors[
                                            `items.${indice}.cantidad`
                                        ]
                                    }}
                                </p>
                            </div>

                            <!-- PRECIO -->
                            <div>
                                <label
                                    :for="`precio-${indice}`"
                                    class="mb-2 block text-sm font-semibold text-slate-700"
                                >
                                    Precio unitario (CLP)
                                </label>

                                <input
                                    :id="`precio-${indice}`"
                                    v-model="item.precio_unitario"
                                    :disabled="form.processing"
                                    type="number"
                                    min="0"
                                    max="9999999999.99"
                                    step="0.01"
                                    required
                                    placeholder="Ej.: 1500"
                                    class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100"
                                />

                                <p
                                    v-if="form.errors[`items.${indice}.precio_unitario`]"
                                    class="mt-2 text-xs text-red-600"
                                >
                                    {{
                                        form.errors[
                                            `items.${indice}.precio_unitario`
                                        ]
                                    }}
                                </p>
                            </div>

                        </div>

                        <!-- SUBTOTAL -->
                        <div
                            class="mt-4 flex flex-col gap-1 border-t border-slate-200 pt-4 sm:flex-row sm:items-center sm:justify-between"
                        >
                            <p class="text-sm text-slate-500">
                                {{
                                    medicamentoSeleccionado(
                                        item.medicamento_id
                                    )?.nombre || 'Selecciona un medicamento'
                                }}
                            </p>

                            <p class="text-sm font-bold text-slate-800">
                                Subtotal: {{ dinero(subtotal(item)) }}
                            </p>
                        </div>
                    </div>

                    <!-- ERRORES GENERALES -->
                    <p
                        v-if="form.errors.items"
                        class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-700"
                    >
                        {{ form.errors.items }}
                    </p>

                    <!-- AGREGAR OTRO -->
                    <button
                        type="button"
                        :disabled="form.processing || form.items.length >= medicamentos.length"
                        @click="agregarMedicamento"
                        class="inline-flex items-center gap-2 rounded-xl border border-blue-300 bg-white px-5 py-3 text-sm font-semibold text-blue-800 transition hover:bg-blue-50 disabled:cursor-not-allowed disabled:opacity-50"
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
                            <path d="M12 5v14" />
                            <path d="M5 12h14" />
                        </svg>

                        Agregar otro medicamento
                    </button>

                    <!-- RESUMEN -->
                    <div
                        class="mt-6 rounded-xl border border-blue-100 bg-blue-50 p-5"
                    >
                        <div
                            class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
                        >
                            <div>
                                <h3 class="font-semibold text-blue-800">
                                    Resumen de la venta
                                </h3>

                                <p class="mt-2 text-sm text-slate-600">
                                    {{
                                        form.items.filter(
                                            item => item.medicamento_id !== ''
                                        ).length
                                    }}
                                    medicamento(s) seleccionado(s)
                                </p>

                                <p class="mt-1 text-sm text-slate-600">
                                    {{
                                        form.tipo_comprobante === 'factura'
                                            ? 'Factura simulada'
                                            : 'Boleta simulada'
                                    }}
                                </p>

                                <p class="mt-2 text-xs text-slate-500">
                                    Uso académico INACAP.
                                    Documento sin validez tributaria.
                                </p>
                            </div>

                            <div class="sm:text-right">
                                <p
                                    class="text-xs font-semibold uppercase text-slate-500"
                                >
                                    Total estimado
                                </p>

                                <p class="mt-1 text-3xl font-bold text-blue-900">
                                    {{ dinero(total) }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- GUARDAR VENTA -->
                    <div class="mt-6">
                        <button
                            type="submit"
                            :disabled="form.processing || !medicamentos.length"
                            class="inline-flex items-center justify-center gap-2 rounded-xl bg-blue-800 px-6 py-3 text-sm font-semibold text-white transition hover:bg-blue-900 disabled:cursor-not-allowed disabled:opacity-50"
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
                                <path d="M20 6L9 17l-5-5" />
                            </svg>

                            {{
                                form.processing
                                    ? 'Registrando venta...'
                                    : 'Registrar venta simulada'
                            }}
                        </button>
                    </div>
                </form>
            </section>

            <!-- HISTORIAL DE VENTAS -->
            <section
                class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"
            >
                <div
                    class="flex flex-col gap-4 border-b border-slate-200 p-6 sm:flex-row sm:items-center sm:justify-between"
                >
                    <div>
                        <h2 class="text-xl font-bold text-slate-900">
                            Historial de ventas
                        </h2>

                        <p class="mt-1 text-sm text-slate-500">
                            Consulta las ventas y sus comprobantes simulados.
                        </p>
                    </div>

                    <input
                        v-model="busqueda"
                        type="search"
                        placeholder="Buscar venta, medicamento o usuario"
                        class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100 sm:max-w-sm"
                    />
                </div>

                <div class="overflow-x-auto">
                    <table
                        class="min-w-full divide-y divide-slate-200 text-left text-sm"
                    >
                        <thead class="bg-slate-50">
                            <tr
                                class="text-xs uppercase tracking-wide text-slate-500"
                            >
                                <th class="px-5 py-4">Venta</th>
                                <th class="px-5 py-4">Fecha</th>
                                <th class="px-5 py-4">Comprobante</th>
                                <th class="px-5 py-4">Medicamentos</th>
                                <th class="px-5 py-4">Unidades</th>
                                <th class="px-5 py-4">Total</th>
                                <th class="px-5 py-4">Responsable</th>
                                <th class="px-5 py-4">Acciones</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-100">

                            <tr
                                v-for="venta in ventasFiltradas"
                                :key="venta.id"
                                class="hover:bg-slate-50"
                            >
                                <!-- ID -->
                                <td
                                    class="whitespace-nowrap px-5 py-4 font-semibold text-blue-800"
                                >
                                    #{{ venta.id }}
                                </td>

                                <!-- FECHA -->
                                <td
                                    class="whitespace-nowrap px-5 py-4 text-slate-600"
                                >
                                    {{ fecha(venta.fecha_venta) }}
                                </td>

                                <!-- COMPROBANTE -->
                                <td class="px-5 py-4">
                                    <span
                                        class="inline-block whitespace-nowrap rounded-lg bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-800"
                                    >
                                        {{ tipoComprobante(venta) }}
                                    </span>
                                </td>

                                <!-- MEDICAMENTOS -->
                                <td
                                    class="min-w-48 px-5 py-4 text-slate-700"
                                >
                                    {{ resumenMedicamentos(venta) }}
                                </td>

                                <!-- CANTIDAD TOTAL -->
                                <td class="px-5 py-4">
                                    {{ cantidadVenta(venta) }}
                                </td>

                                <!-- TOTAL -->
                                <td
                                    class="whitespace-nowrap px-5 py-4 font-bold text-slate-900"
                                >
                                    {{ dinero(venta.total) }}
                                </td>

                                <!-- RESPONSABLE -->
                                <td class="px-5 py-4 text-slate-600">
                                    {{
                                        venta.usuario?.name ||
                                        'No disponible'
                                    }}
                                </td>

                                <!-- ACCIONES -->
                                <td class="px-5 py-4">
                                    <button
                                        type="button"
                                        @click="verComprobante(venta)"
                                        class="inline-flex items-center gap-2 whitespace-nowrap rounded-lg border border-blue-200 bg-blue-50 px-3 py-2 text-xs font-semibold text-blue-800 transition hover:bg-blue-100"
                                    >
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            width="16"
                                            height="16"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="2"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            aria-hidden="true"
                                        >
                                            <path d="M8 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-3" />
                                            <path d="M16 3h5v5" />
                                            <path d="M10 14 21 3" />
                                        </svg>

                                        Ver comprobante
                                    </button>
                                </td>
                            </tr>

                            <!-- SIN RESULTADOS -->
                            <tr v-if="!ventasFiltradas.length">
                                <td
                                    colspan="8"
                                    class="px-5 py-12 text-center text-slate-500"
                                >
                                    No hay ventas registradas que coincidan
                                    con la búsqueda.
                                </td>
                            </tr>

                        </tbody>
                    </table>
                </div>
            </section>

            <!-- MARCA DE AGUA -->
            <div
                class="pointer-events-none fixed bottom-3 right-3 z-50 rounded-lg border border-slate-200 bg-white/85 px-3 py-2 text-right text-[10px] font-semibold text-slate-400 shadow-sm"
            >
                <p>Uso académico · INACAP</p>
                <p>Sistema de simulación · Sin validez tributaria</p>
            </div>

        </div>
    </div>
</template>
