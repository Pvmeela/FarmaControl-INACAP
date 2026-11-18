<script setup>
import { computed, ref } from 'vue'
import { Head, Link, useForm, usePage } from '@inertiajs/vue3'

const props = defineProps({
    movimientos: {
        type: Array,
        default: () => [],
    },
    medicamentos: {
        type: Array,
        default: () => [],
    },
})

const page = usePage()

const form = useForm({
    medicamento_id: '',
    cantidad: '',
    motivo: '',
})

const busqueda = ref('')
const enviando = ref(false)

const mensajeExito = computed(() => page.props.flash?.success ?? '')

const medicamentosDisponibles = computed(() =>
    props.medicamentos.filter((medicamento) =>
        medicamento.nombre && medicamento.codigo
    )
)

const movimientosFiltrados = computed(() => {
    const texto = busqueda.value.trim().toLowerCase()

    if (!texto) {
        return props.movimientos
    }

    return props.movimientos.filter((movimiento) => {
        const medicamento = movimiento.lote?.medicamento
        const usuario = movimiento.usuario

        return [
            medicamento?.nombre,
            medicamento?.codigo,
            movimiento.lote?.numero_lote,
            movimiento.tipo,
            movimiento.motivo,
            usuario?.name,
        ]
            .filter(Boolean)
            .some((valor) =>
                String(valor).toLowerCase().includes(texto)
            )
    })
})

const totalMovimientos = computed(() => props.movimientos.length)

const totalSalidas = computed(() =>
    props.movimientos
        .filter((movimiento) => movimiento.tipo === 'salida')
        .reduce(
            (total, movimiento) => total + Number(movimiento.cantidad || 0),
            0
        )
)

const totalEntradas = computed(() =>
    props.movimientos
        .filter((movimiento) => movimiento.tipo === 'entrada')
        .reduce(
            (total, movimiento) => total + Number(movimiento.cantidad || 0),
            0
        )
)

function registrarSalida() {
    if (enviando.value) return

    enviando.value = true

    form.post('/movimientos', {
        preserveScroll: true,
        onSuccess: () => {
            form.reset()
        },
        onFinish: () => {
            enviando.value = false
        },
    })
}

function formatearFecha(fecha) {
    if (!fecha) return 'Sin registro'

    const fechaObj = new Date(fecha)

    if (Number.isNaN(fechaObj.getTime())) {
        return fecha
    }

    return fechaObj.toLocaleString('es-CL', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    })
}

function formatearNumero(numero) {
    return Number(numero || 0).toLocaleString('es-CL')
}
</script>

<template>
    <Head title="Movimientos de inventario - FarmaControl" />

    <div class="min-h-screen bg-slate-100 text-slate-800">
        <header class="border-b border-slate-200 bg-white shadow-sm">
            <div
                class="mx-auto flex max-w-7xl flex-col gap-4 px-5 py-5 sm:flex-row sm:items-center sm:justify-between"
            >
                <div>
                    <p class="text-sm font-semibold tracking-wide text-blue-700">
                        FARMA<span class="text-slate-800">CONTROL</span>
                    </p>
                    <h1 class="mt-1 text-2xl font-bold">
                        Movimientos de inventario
                    </h1>
                    <p class="mt-1 text-sm text-slate-500">
                        Control de salidas y trazabilidad por lote.
                    </p>
                </div>

                <nav class="flex flex-wrap gap-2">
                    <Link
                        href="/dashboard"
                        class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium hover:bg-slate-50"
                    >
                        Panel principal
                    </Link>

                    <Link
                        href="/medicamentos"
                        class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium hover:bg-slate-50"
                    >
                        Medicamentos
                    </Link>

                    <Link
                        href="/lotes"
                        class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium hover:bg-slate-50"
                    >
                        Lotes
                    </Link>
                </nav>
            </div>
        </header>

        <main class="mx-auto max-w-7xl space-y-6 px-5 py-8">
            <div
                v-if="mensajeExito"
                class="rounded-xl border border-green-200 bg-green-50 px-5 py-4 text-sm font-medium text-green-800"
                role="status"
            >
                {{ mensajeExito }}
            </div>

            <section class="grid gap-4 sm:grid-cols-3">
                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-sm text-slate-500">
                        Movimientos registrados
                    </p>
                    <p class="mt-2 text-3xl font-bold text-slate-900">
                        {{ formatearNumero(totalMovimientos) }}
                    </p>
                </div>

                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-sm text-slate-500">
                        Unidades retiradas
                    </p>
                    <p class="mt-2 text-3xl font-bold text-blue-700">
                        {{ formatearNumero(totalSalidas) }}
                    </p>
                </div>

                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-sm text-slate-500">
                        Unidades ingresadas en historial
                    </p>
                    <p class="mt-2 text-3xl font-bold text-slate-900">
                        {{ formatearNumero(totalEntradas) }}
                    </p>
                    <p class="mt-1 text-xs text-slate-500">
                        No incluye los lotes registrados anteriormente.
                    </p>
                </div>
            </section>

            <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="mb-6">
                    <h2 class="text-lg font-bold text-slate-900">
                        Registrar salida de inventario
                    </h2>
                    <p class="mt-1 text-sm text-slate-500">
                        El sistema seleccionará automáticamente los lotes
                        vigentes según FEFO: primero los que vencen antes.
                    </p>
                </div>

                <form
                    class="grid gap-5 md:grid-cols-2"
                    @submit.prevent="registrarSalida"
                >
                    <div>
                        <label
                            for="medicamento"
                            class="mb-2 block text-sm font-semibold"
                        >
                            Medicamento
                        </label>

                        <select
                            id="medicamento"
                            v-model="form.medicamento_id"
                            required
                            class="w-full rounded-lg border border-slate-300 bg-white px-4 py-3 text-sm focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-100"
                        >
                            <option value="">
                                Selecciona un medicamento
                            </option>

                            <option
                                v-for="medicamento in medicamentosDisponibles"
                                :key="medicamento.id"
                                :value="medicamento.id"
                            >
                                {{ medicamento.nombre }} — {{ medicamento.codigo }}
                            </option>
                        </select>

                        <p
                            v-if="form.errors.medicamento_id"
                            class="mt-2 text-sm text-red-600"
                        >
                            {{ form.errors.medicamento_id }}
                        </p>
                    </div>

                    <div>
                        <label
                            for="cantidad"
                            class="mb-2 block text-sm font-semibold"
                        >
                            Cantidad a retirar
                        </label>

                        <input
                            id="cantidad"
                            v-model="form.cantidad"
                            type="number"
                            min="1"
                            step="1"
                            required
                            placeholder="Ejemplo: 5"
                            class="w-full rounded-lg border border-slate-300 px-4 py-3 text-sm focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-100"
                        />

                        <p
                            v-if="form.errors.cantidad"
                            class="mt-2 text-sm text-red-600"
                        >
                            {{ form.errors.cantidad }}
                        </p>
                    </div>

                    <div class="md:col-span-2">
                        <label
                            for="motivo"
                            class="mb-2 block text-sm font-semibold"
                        >
                            Motivo de la salida
                        </label>

                        <input
                            id="motivo"
                            v-model="form.motivo"
                            type="text"
                            maxlength="255"
                            required
                            placeholder="Ejemplo: Salida de prueba académica"
                            class="w-full rounded-lg border border-slate-300 px-4 py-3 text-sm focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-100"
                        />

                        <p
                            v-if="form.errors.motivo"
                            class="mt-2 text-sm text-red-600"
                        >
                            {{ form.errors.motivo }}
                        </p>
                    </div>

                    <div class="md:col-span-2">
                        <div class="rounded-lg border border-blue-100 bg-blue-50 p-4">
                            <p class="text-sm text-blue-900">
                                <strong>Control FEFO:</strong>
                                no se retirarán unidades de lotes vencidos.
                                Si la cantidad solicitada supera el stock
                                utilizable, la operación será rechazada.
                            </p>
                        </div>
                    </div>

                    <div class="md:col-span-2">
                        <button
                            type="submit"
                            :disabled="form.processing || enviando || medicamentosDisponibles.length === 0"
                            class="rounded-lg bg-blue-700 px-6 py-3 text-sm font-semibold text-white transition hover:bg-blue-800 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            {{
                                form.processing || enviando
                                    ? 'Registrando salida...'
                                    : 'Registrar salida'
                            }}
                        </button>
                    </div>
                </form>
            </section>

            <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                <div class="flex flex-col gap-4 border-b border-slate-200 p-6 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-lg font-bold text-slate-900">
                            Historial de movimientos
                        </h2>
                        <p class="mt-1 text-sm text-slate-500">
                            Registro de las operaciones realizadas en inventario.
                        </p>
                    </div>

                    <input
                        v-model="busqueda"
                        type="search"
                        placeholder="Buscar medicamento, lote, usuario..."
                        aria-label="Buscar movimientos"
                        class="w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-100 sm:w-80"
                    />
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                        <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-600">
                            <tr>
                                <th class="px-5 py-4">Fecha</th>
                                <th class="px-5 py-4">Medicamento</th>
                                <th class="px-5 py-4">Lote</th>
                                <th class="px-5 py-4">Tipo</th>
                                <th class="px-5 py-4">Cantidad</th>
                                <th class="px-5 py-4">Stock anterior</th>
                                <th class="px-5 py-4">Stock posterior</th>
                                <th class="px-5 py-4">Responsable</th>
                                <th class="px-5 py-4">Motivo</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-100">
                            <tr
                                v-for="movimiento in movimientosFiltrados"
                                :key="movimiento.id"
                                class="hover:bg-slate-50"
                            >
                                <td class="whitespace-nowrap px-5 py-4 text-slate-600">
                                    {{ formatearFecha(movimiento.created_at) }}
                                </td>

                                <td class="px-5 py-4">
                                    <p class="font-semibold text-slate-900">
                                        {{ movimiento.lote?.medicamento?.nombre ?? 'Sin medicamento' }}
                                    </p>
                                    <p class="text-xs text-slate-500">
                                        {{ movimiento.lote?.medicamento?.codigo ?? '—' }}
                                    </p>
                                </td>

                                <td class="whitespace-nowrap px-5 py-4">
                                    {{ movimiento.lote?.numero_lote ?? '—' }}
                                </td>

                                <td class="px-5 py-4">
                                    <span
                                        class="inline-flex rounded-full px-3 py-1 text-xs font-semibold"
                                        :class="
                                            movimiento.tipo === 'salida'
                                                ? 'bg-orange-100 text-orange-800'
                                                : 'bg-green-100 text-green-800'
                                        "
                                    >
                                        {{
                                            movimiento.tipo === 'salida'
                                                ? 'Salida'
                                                : 'Entrada'
                                        }}
                                    </span>
                                </td>

                                <td class="px-5 py-4 font-semibold">
                                    {{ formatearNumero(movimiento.cantidad) }}
                                </td>

                                <td class="px-5 py-4">
                                    {{ formatearNumero(movimiento.stock_anterior) }}
                                </td>

                                <td class="px-5 py-4 font-semibold">
                                    {{ formatearNumero(movimiento.stock_posterior) }}
                                </td>

                                <td class="px-5 py-4">
                                    {{ movimiento.usuario?.name ?? 'Sin usuario' }}
                                </td>

                                <td class="min-w-48 px-5 py-4 text-slate-600">
                                    {{ movimiento.motivo }}
                                </td>
                            </tr>

                            <tr v-if="movimientosFiltrados.length === 0">
                                <td
                                    colspan="9"
                                    class="px-5 py-12 text-center text-slate-500"
                                >
                                    {{
                                        busqueda
                                            ? 'No se encontraron movimientos con esa búsqueda.'
                                            : 'Todavía no hay movimientos registrados.'
                                    }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </main>

        <div
            class="pointer-events-none fixed bottom-3 right-3 z-50 rounded-md border border-slate-200 bg-white/90 px-3 py-2 text-right text-[10px] leading-4 text-slate-500 shadow-sm"
        >
            <p class="font-semibold">Uso académico · INACAP</p>
            <p>Sistema de simulación · Sin validez legal</p>
        </div>
    </div>
</template>
