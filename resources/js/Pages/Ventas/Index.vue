<script setup>
import { computed, ref } from 'vue'
import { router, useForm } from '@inertiajs/vue3'

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

const form = useForm({
    medicamento_id: '',
    cantidad: 1,
    precio_unitario: '',
})

const busqueda = ref('')

const medicamentoSeleccionado = computed(() =>
    props.medicamentos.find(
        medicamento =>
            String(medicamento.id) === String(form.medicamento_id)
    )
)

const total = computed(() => {
    const cantidad = Number(form.cantidad)
    const precio = Number(form.precio_unitario)

    if (
        !Number.isInteger(cantidad) ||
        cantidad < 1 ||
        !Number.isFinite(precio) ||
        precio < 0
    ) {
        return 0
    }

    return cantidad * precio
})

const totalUnidades = computed(() =>
    props.ventas.reduce(
        (total, venta) =>
            total + (venta.detalles || []).reduce(
                (suma, detalle) => suma + Number(detalle.cantidad),
                0
            ),
        0
    )
)

const montoAcumulado = computed(() =>
    props.ventas.reduce(
        (total, venta) => total + Number(venta.total),
        0
    )
)

const ventasFiltradas = computed(() => {
    const termino = busqueda.value.trim().toLowerCase()

    if (!termino) return props.ventas

    return props.ventas.filter(venta => {
        const medicamentos = (venta.detalles || [])
            .map(detalle => detalle.medicamento?.nombre || '')
            .join(' ')

        const responsable = venta.usuario?.name || ''

        return (
            String(venta.id).includes(termino) ||
            medicamentos.toLowerCase().includes(termino) ||
            responsable.toLowerCase().includes(termino)
        )
    })
})

const dinero = valor =>
    new Intl.NumberFormat('es-CL', {
        style: 'currency',
        currency: 'CLP',
        maximumFractionDigits: 2,
    }).format(Number(valor) || 0)

const fecha = valor => {
    if (!valor) return '—'

    return new Date(valor).toLocaleString('es-CL', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    })
}

function registrarVenta() {
    if (form.processing) return

    form.post('/ventas', {
        preserveScroll: true,
        onSuccess: () => {
            form.reset()
            form.cantidad = 1
        },
    })
}

function volverDashboard() {
    router.visit('/dashboard')
}
</script>

<template>
    <div class="min-h-screen bg-slate-100 text-slate-800">
        <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6">

            <!-- Encabezado -->
            <header
                class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
            >
                <div>
                    <p class="text-sm font-semibold uppercase tracking-widest text-blue-700">
                        FarmaControl
                    </p>

                    <h1 class="mt-1 text-3xl font-bold text-slate-900">
                        Ventas simuladas
                    </h1>

                    <p class="mt-2 text-sm text-slate-500">
                        Registro de ventas y descuento de inventario mediante FEFO.
                    </p>
                </div>

                <button
                    type="button"
                    @click="volverDashboard"
                    class="rounded-xl border border-slate-300 bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                >
                    ← Volver al dashboard
                </button>
            </header>

            <!-- Mensaje de éxito -->
            <div
                v-if="flash.success"
                class="mb-6 rounded-xl border border-green-200 bg-green-50 px-5 py-4 text-sm font-medium text-green-800"
            >
                {{ flash.success }}
            </div>

            <!-- Indicadores -->
            <section class="mb-8 grid gap-4 sm:grid-cols-3">
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <p class="text-sm font-medium text-slate-500">
                        Ventas registradas
                    </p>
                    <p class="mt-3 text-3xl font-bold text-blue-800">
                        {{ ventas.length }}
                    </p>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <p class="text-sm font-medium text-slate-500">
                        Unidades vendidas
                    </p>
                    <p class="mt-3 text-3xl font-bold text-blue-800">
                        {{ totalUnidades }}
                    </p>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <p class="text-sm font-medium text-slate-500">
                        Total simulado acumulado
                    </p>
                    <p class="mt-3 text-3xl font-bold text-blue-800">
                        {{ dinero(montoAcumulado) }}
                    </p>
                </div>
            </section>

            <!-- Formulario -->
            <section class="mb-8 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="mb-6">
                    <h2 class="text-xl font-bold text-slate-900">
                        Registrar nueva venta
                    </h2>
                    <p class="mt-1 text-sm text-slate-500">
                        Esta operación es una simulación académica y descontará stock utilizable.
                    </p>
                </div>

                <form
                    @submit.prevent="registrarVenta"
                    class="grid gap-5 md:grid-cols-3"
                >
                    <!-- Medicamento -->
                    <div>
                        <label
                            for="medicamento"
                            class="mb-2 block text-sm font-semibold text-slate-700"
                        >
                            Medicamento
                        </label>

                        <select
                            id="medicamento"
                            v-model="form.medicamento_id"
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
                            >
                                {{ medicamento.nombre }} ({{ medicamento.codigo }})
                            </option>
                        </select>

                        <p
                            v-if="form.errors.medicamento_id"
                            class="mt-2 text-xs text-red-600"
                        >
                            {{ form.errors.medicamento_id }}
                        </p>
                    </div>

                    <!-- Cantidad -->
                    <div>
                        <label
                            for="cantidad"
                            class="mb-2 block text-sm font-semibold text-slate-700"
                        >
                            Cantidad
                        </label>

                        <input
                            id="cantidad"
                            v-model.number="form.cantidad"
                            type="number"
                            min="1"
                            step="1"
                            required
                            class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100"
                        />

                        <p
                            v-if="form.errors.cantidad"
                            class="mt-2 text-xs text-red-600"
                        >
                            {{ form.errors.cantidad }}
                        </p>
                    </div>

                    <!-- Precio -->
                    <div>
                        <label
                            for="precio"
                            class="mb-2 block text-sm font-semibold text-slate-700"
                        >
                            Precio unitario (CLP)
                        </label>

                        <input
                            id="precio"
                            v-model="form.precio_unitario"
                            type="number"
                            min="0"
                            step="0.01"
                            required
                            placeholder="Ej.: 1500"
                            class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100"
                        />

                        <p
                            v-if="form.errors.precio_unitario"
                            class="mt-2 text-xs text-red-600"
                        >
                            {{ form.errors.precio_unitario }}
                        </p>
                    </div>

                    <!-- Resumen -->
                    <div
                        class="rounded-xl border border-blue-100 bg-blue-50 p-5 md:col-span-3"
                    >
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <p class="text-sm text-blue-700">
                                    Resumen de la venta
                                </p>

                                <p class="mt-1 text-sm text-slate-600">
                                    {{
                                        medicamentoSeleccionado?.nombre ||
                                        'Selecciona un medicamento'
                                    }}
                                    · {{ form.cantidad || 0 }} unidad(es)
                                </p>
                            </div>

                            <div class="sm:text-right">
                                <p class="text-xs font-semibold uppercase text-slate-500">
                                    Total estimado
                                </p>

                                <p class="mt-1 text-3xl font-bold text-blue-900">
                                    {{ dinero(total) }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="md:col-span-3">
                        <button
                            type="submit"
                            :disabled="form.processing || !medicamentos.length"
                            class="rounded-xl bg-blue-800 px-6 py-3 text-sm font-semibold text-white transition hover:bg-blue-900 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            {{
                                form.processing
                                    ? 'Registrando venta...'
                                    : 'Registrar venta simulada'
                            }}
                        </button>
                    </div>
                </form>
            </section>

            <!-- Historial -->
            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="flex flex-col gap-4 border-b border-slate-200 p-6 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-xl font-bold text-slate-900">
                            Historial de ventas
                        </h2>

                        <p class="mt-1 text-sm text-slate-500">
                            Operaciones registradas en FarmaControl.
                        </p>
                    </div>

                    <input
                        v-model="busqueda"
                        type="search"
                        placeholder="Buscar venta, medicamento o usuario"
                        class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm sm:max-w-sm"
                    />
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                        <thead class="bg-slate-50">
                            <tr class="text-xs uppercase tracking-wide text-slate-500">
                                <th class="px-5 py-4">Venta</th>
                                <th class="px-5 py-4">Fecha</th>
                                <th class="px-5 py-4">Medicamento</th>
                                <th class="px-5 py-4">Cantidad</th>
                                <th class="px-5 py-4">Precio unitario</th>
                                <th class="px-5 py-4">Total</th>
                                <th class="px-5 py-4">Responsable</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-100">
                            <template
                                v-for="venta in ventasFiltradas"
                                :key="venta.id"
                            >
                                <tr
                                    v-for="(detalle, indice) in venta.detalles || []"
                                    :key="`${venta.id}-${detalle.id}`"
                                    class="hover:bg-slate-50"
                                >
                                    <td class="whitespace-nowrap px-5 py-4 font-semibold text-blue-800">
                                        #{{ venta.id }}
                                    </td>

                                    <td class="whitespace-nowrap px-5 py-4 text-slate-600">
                                        {{ fecha(venta.fecha_venta) }}
                                    </td>

                                    <td class="px-5 py-4 font-medium text-slate-800">
                                        {{ detalle.medicamento?.nombre || 'No disponible' }}
                                    </td>

                                    <td class="px-5 py-4">
                                        {{ detalle.cantidad }}
                                    </td>

                                    <td class="whitespace-nowrap px-5 py-4">
                                        {{ dinero(detalle.precio_unitario) }}
                                    </td>

                                    <td class="whitespace-nowrap px-5 py-4 font-semibold">
                                        {{ dinero(detalle.subtotal) }}
                                    </td>

                                    <td class="px-5 py-4 text-slate-600">
                                        {{ venta.usuario?.name || 'No disponible' }}
                                    </td>
                                </tr>
                            </template>

                            <tr v-if="!ventasFiltradas.length">
                                <td
                                    colspan="7"
                                    class="px-5 py-12 text-center text-slate-500"
                                >
                                    No hay ventas registradas que coincidan con la búsqueda.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- Marca de agua académica -->
            <div
                class="pointer-events-none fixed bottom-3 right-3 z-50 rounded-lg border border-slate-200 bg-white/85 px-3 py-2 text-right text-[10px] font-semibold text-slate-400 shadow-sm"
            >
                <p>Uso académico · INACAP</p>
                <p>Sistema de simulación · Sin validez legal</p>
            </div>

        </div>
    </div>
</template>
