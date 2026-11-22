<script setup>
import { computed, ref, watch } from 'vue'
import { Head, Link, useForm, usePage } from '@inertiajs/vue3'

const props = defineProps({
    pacientes: {
        type: Array,
        default: () => [],
    },
    recetas: {
        type: Array,
        default: () => [],
    },
    medicamentos: {
        type: Array,
        default: () => [],
    },
    dispensaciones: {
        type: Array,
        default: () => [],
    },
})

const page = usePage()
const busqueda = ref('')

const form = useForm({
    paciente_id: '',
    receta_id: '',
    medicamento_id: '',
    cantidad: 1,
    observaciones: '',
})

const mensajeExito = computed(
    () => page.props.flash?.success || ''
)

function fechaLocal(valor) {
    if (!valor) return ''

    return String(valor).slice(0, 10)
}

const hoy = computed(() => {
    const fecha = new Date()

    return [
        fecha.getFullYear(),
        String(fecha.getMonth() + 1).padStart(2, '0'),
        String(fecha.getDate()).padStart(2, '0'),
    ].join('-')
})

function recetaEstaVigente(receta) {
    if (receta.estado !== 'vigente') return false

    const emision = fechaLocal(receta.fecha_emision)
    const vencimiento = fechaLocal(receta.fecha_vencimiento)

    return emision <= hoy.value && vencimiento >= hoy.value
}

const recetasDisponibles = computed(() => {
    if (!form.paciente_id) return []

    return props.recetas.filter(
        receta =>
            Number(receta.paciente_id) === Number(form.paciente_id)
            && recetaEstaVigente(receta)
    )
})

const recetaSeleccionada = computed(() =>
    props.recetas.find(
        receta => Number(receta.id) === Number(form.receta_id)
    )
)

const medicamentoSeleccionado = computed(() =>
    props.medicamentos.find(
        medicamento =>
            Number(medicamento.id) === Number(form.medicamento_id)
    )
)

const medicamentosReceta = computed(() => {
    if (!recetaSeleccionada.value) return []

    return (recetaSeleccionada.value.detalles || [])
        .filter(detalle =>
            props.medicamentos.some(
                medicamento =>
                    Number(medicamento.id) === Number(detalle.medicamento_id)
            )
        )
})

function cantidadEntregada(recetaId, medicamentoId) {
    return props.dispensaciones
        .filter(
            dispensacion =>
                Number(dispensacion.receta_id) === Number(recetaId)
        )
        .flatMap(dispensacion => dispensacion.detalles || [])
        .filter(
            detalle =>
                Number(detalle.medicamento_id) === Number(medicamentoId)
        )
        .reduce(
            (total, detalle) => total + Number(detalle.cantidad || 0),
            0
        )
}

function cantidadPendiente(detalle) {
    if (!recetaSeleccionada.value) return 0

    const totalPrescrito = (recetaSeleccionada.value.detalles || [])
        .filter(
            item =>
                Number(item.medicamento_id) ===
                Number(detalle.medicamento_id)
        )
        .reduce(
            (total, item) =>
                total + Number(item.cantidad_prescrita || 0),
            0
        )

    return Math.max(
        0,
        totalPrescrito - cantidadEntregada(
            recetaSeleccionada.value.id,
            detalle.medicamento_id
        )
    )
}

const detallesPendientes = computed(() =>
    medicamentosReceta.value.filter(
        detalle => cantidadPendiente(detalle) > 0
    )
)

const detalleSeleccionado = computed(() =>
    medicamentosReceta.value.find(
        detalle =>
            Number(detalle.medicamento_id) ===
            Number(form.medicamento_id)
    )
)

const maximoEntregable = computed(() =>
    detalleSeleccionado.value
        ? cantidadPendiente(detalleSeleccionado.value)
        : 0
)

watch(
    () => form.paciente_id,
    () => {
        form.receta_id = ''
        form.medicamento_id = ''
        form.cantidad = 1
        form.clearErrors()
    }
)

watch(
    () => form.receta_id,
    () => {
        form.medicamento_id = ''
        form.cantidad = 1
        form.clearErrors()
    }
)

watch(
    () => form.medicamento_id,
    () => {
        form.cantidad = 1
        form.clearErrors('cantidad')
    }
)

const puedeRegistrar = computed(() =>
    Boolean(
        form.paciente_id &&
        form.receta_id &&
        form.medicamento_id &&
        Number.isInteger(Number(form.cantidad)) &&
        Number(form.cantidad) >= 1 &&
        Number(form.cantidad) <= maximoEntregable.value &&
        !form.processing
    )
)

function registrarDispensacion() {
    if (!puedeRegistrar.value) return

    form.post('/dispensaciones', {
        preserveScroll: true,
        onSuccess: () => {
            form.reset()
        },
    })
}

function nombrePaciente(paciente) {
    if (!paciente) return 'Paciente no disponible'

    return `${paciente.nombres} ${paciente.apellidos}`
}

function nombreMedicamento(medicamento) {
    if (!medicamento) return 'Medicamento no disponible'

    return medicamento.nombre
}

function formatearFecha(valor) {
    if (!valor) return '—'

    const partes = fechaLocal(valor).split('-')

    if (partes.length !== 3) return String(valor)

    return `${partes[2]}/${partes[1]}/${partes[0]}`
}

const dispensacionesFiltradas = computed(() => {
    const termino = busqueda.value.trim().toLowerCase()

    if (!termino) return props.dispensaciones

    return props.dispensaciones.filter(dispensacion => {
        const contenido = [
            dispensacion.id,
            dispensacion.paciente?.codigo_paciente,
            nombrePaciente(dispensacion.paciente),
            dispensacion.receta?.codigo_receta,
            ...(dispensacion.detalles || []).map(
                detalle => detalle.medicamento?.nombre
            ),
        ].join(' ').toLowerCase()

        return contenido.includes(termino)
    })
})

const totalUnidades = computed(() =>
    props.dispensaciones.reduce(
        (total, dispensacion) =>
            total + (dispensacion.detalles || []).reduce(
                (subtotal, detalle) =>
                    subtotal + Number(detalle.cantidad || 0),
                0
            ),
        0
    )
)
</script>

<template>
    <Head title="Dispensaciones asistenciales | FarmaControl" />

    <div class="min-h-screen bg-slate-100 text-slate-800">
        <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6">

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
                        Dispensaciones asistenciales
                    </h1>

                    <p class="mt-2 text-sm text-slate-500">
                        Registro y seguimiento de entregas simuladas
                        de medicamentos con receta.
                    </p>
                </div>

                <Link
                    href="/dashboard"
                    class="rounded-xl border border-slate-300 bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                >
                    ← Volver al dashboard
                </Link>
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
                        Dispensaciones registradas
                    </p>

                    <p class="mt-3 text-3xl font-bold text-blue-800">
                        {{ dispensaciones.length }}
                    </p>
                </div>

                <div
                    class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"
                >
                    <p class="text-sm font-medium text-slate-500">
                        Unidades entregadas
                    </p>

                    <p class="mt-3 text-3xl font-bold text-blue-800">
                        {{ totalUnidades }}
                    </p>
                </div>

                <div
                    class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"
                >
                    <p class="text-sm font-medium text-slate-500">
                        Recetas vigentes
                    </p>

                    <p class="mt-3 text-3xl font-bold text-green-700">
                        {{ recetas.filter(recetaEstaVigente).length }}
                    </p>
                </div>
            </section>

            <!-- FORMULARIO -->
            <section
                class="mb-8 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"
            >
                <h2 class="text-xl font-bold text-slate-900">
                    Registrar dispensación simulada
                </h2>

                <p class="mt-2 text-sm text-slate-500">
                    Selecciona un paciente y una receta vigente.
                    El sistema validará la cantidad prescrita y
                    descontará el inventario utilizando FEFO.
                    Utiliza únicamente datos ficticios.
                </p>

                <form
                    class="mt-6 space-y-6"
                    @submit.prevent="registrarDispensacion"
                >
                    <div class="grid gap-5 md:grid-cols-2">

                        <!-- PACIENTE -->
                        <div>
                            <label
                                for="paciente_id"
                                class="mb-2 block text-sm font-semibold"
                            >
                                Paciente
                            </label>

                            <select
                                id="paciente_id"
                                v-model="form.paciente_id"
                                required
                                class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm"
                            >
                                <option value="">
                                    Seleccionar paciente
                                </option>

                                <option
                                    v-for="paciente in pacientes"
                                    :key="paciente.id"
                                    :value="paciente.id"
                                >
                                    {{ paciente.codigo_paciente }} -
                                    {{ nombrePaciente(paciente) }}
                                </option>
                            </select>

                            <p
                                v-if="form.errors.paciente_id"
                                class="mt-2 text-xs text-red-600"
                            >
                                {{ form.errors.paciente_id }}
                            </p>
                        </div>

                        <!-- RECETA -->
                        <div>
                            <label
                                for="receta_id"
                                class="mb-2 block text-sm font-semibold"
                            >
                                Receta vigente
                            </label>

                            <select
                                id="receta_id"
                                v-model="form.receta_id"
                                :disabled="!form.paciente_id"
                                required
                                class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm disabled:bg-slate-100"
                            >
                                <option value="">
                                    Seleccionar receta
                                </option>

                                <option
                                    v-for="receta in recetasDisponibles"
                                    :key="receta.id"
                                    :value="receta.id"
                                >
                                    {{ receta.codigo_receta }}
                                    - {{ receta.profesional_prescriptor }}
                                </option>
                            </select>

                            <p
                                v-if="form.paciente_id && recetasDisponibles.length === 0"
                                class="mt-2 text-xs text-amber-700"
                            >
                                Este paciente no tiene recetas vigentes disponibles.
                            </p>

                            <p
                                v-if="form.errors.receta_id"
                                class="mt-2 text-xs text-red-600"
                            >
                                {{ form.errors.receta_id }}
                            </p>
                        </div>

                        <!-- MEDICAMENTO -->
                        <div>
                            <label
                                for="medicamento_id"
                                class="mb-2 block text-sm font-semibold"
                            >
                                Medicamento prescrito
                            </label>

                            <select
                                id="medicamento_id"
                                v-model="form.medicamento_id"
                                :disabled="!form.receta_id"
                                required
                                class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm disabled:bg-slate-100"
                            >
                                <option value="">
                                    Seleccionar medicamento
                                </option>

                                <option
                                    v-for="detalle in detallesPendientes"
                                    :key="detalle.id"
                                    :value="detalle.medicamento_id"
                                >
                                    {{ nombreMedicamento(detalle.medicamento) }}
                                    - Pendiente: {{ cantidadPendiente(detalle) }}
                                </option>
                            </select>

                            <p
                                v-if="form.receta_id && detallesPendientes.length === 0"
                                class="mt-2 text-xs text-amber-700"
                            >
                                No hay medicamentos pendientes disponibles
                                para esta receta.
                            </p>

                            <p
                                v-if="form.errors.medicamento_id"
                                class="mt-2 text-xs text-red-600"
                            >
                                {{ form.errors.medicamento_id }}
                            </p>
                        </div>

                        <!-- CANTIDAD -->
                        <div>
                            <label
                                for="cantidad"
                                class="mb-2 block text-sm font-semibold"
                            >
                                Cantidad a entregar
                            </label>

                            <input
                                id="cantidad"
                                v-model.number="form.cantidad"
                                type="number"
                                min="1"
                                step="1"
                                :max="maximoEntregable || undefined"
                                :disabled="!form.medicamento_id"
                                required
                                class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm disabled:bg-slate-100"
                            />

                            <p
                                v-if="detalleSeleccionado"
                                class="mt-2 text-xs text-slate-500"
                            >
                                Máximo pendiente según receta:
                                {{ maximoEntregable }} unidades.
                            </p>

                            <p
                                v-if="form.errors.cantidad"
                                class="mt-2 text-xs text-red-600"
                            >
                                {{ form.errors.cantidad }}
                            </p>
                        </div>
                    </div>

                    <!-- INFORMACIÓN DE LA RECETA -->
                    <div
                        v-if="recetaSeleccionada"
                        class="rounded-xl border border-blue-100 bg-blue-50 p-5"
                    >
                        <h3 class="font-bold text-blue-900">
                            Información de la receta seleccionada
                        </h3>

                        <p class="mt-2 text-sm text-blue-800">
                            Código:
                            {{ recetaSeleccionada.codigo_receta }}
                        </p>

                        <p class="mt-1 text-sm text-blue-800">
                            Vencimiento:
                            {{ formatearFecha(recetaSeleccionada.fecha_vencimiento) }}
                        </p>

                        <div class="mt-4 space-y-2">
                            <div
                                v-for="detalle in medicamentosReceta"
                                :key="detalle.id"
                                class="flex flex-wrap justify-between gap-2 rounded-lg bg-white p-3 text-sm"
                            >
                                <span class="font-medium">
                                    {{ nombreMedicamento(detalle.medicamento) }}
                                </span>

                                <span class="text-slate-600">
                                    Prescrito: {{ detalle.cantidad_prescrita }}
                                    · Pendiente: {{ cantidadPendiente(detalle) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- OBSERVACIONES -->
                    <div>
                        <label
                            for="observaciones"
                            class="mb-2 block text-sm font-semibold"
                        >
                            Observaciones (opcional)
                        </label>

                        <textarea
                            id="observaciones"
                            v-model="form.observaciones"
                            rows="3"
                            maxlength="255"
                            placeholder="Observaciones académicas de la dispensación"
                            class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm"
                        ></textarea>

                        <p
                            v-if="form.errors.observaciones"
                            class="mt-2 text-xs text-red-600"
                        >
                            {{ form.errors.observaciones }}
                        </p>
                    </div>

                    <button
                        type="submit"
                        :disabled="!puedeRegistrar"
                        class="rounded-xl bg-blue-800 px-6 py-3 text-sm font-semibold text-white transition hover:bg-blue-900 disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        {{
                            form.processing
                                ? 'Registrando dispensación...'
                                : 'Registrar dispensación simulada'
                        }}
                    </button>
                </form>
            </section>

            <!-- HISTORIAL -->
            <section
                class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"
            >
                <div
                    class="flex flex-col gap-4 border-b border-slate-200 p-6 sm:flex-row sm:items-center sm:justify-between"
                >
                    <div>
                        <h2 class="text-xl font-bold">
                            Historial de dispensaciones
                        </h2>

                        <p class="mt-1 text-sm text-slate-500">
                            Registro de entregas ficticias de medicamentos.
                        </p>
                    </div>

                    <input
                        v-model="busqueda"
                        type="search"
                        placeholder="Buscar paciente, receta o medicamento"
                        class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm sm:max-w-sm"
                    />
                </div>

                <div class="overflow-x-auto">
                    <table
                        class="min-w-full divide-y divide-slate-200 text-left text-sm"
                    >
                        <thead class="bg-slate-50">
                            <tr class="text-xs uppercase tracking-wide text-slate-500">
                                <th class="px-5 py-4">N.º</th>
                                <th class="px-5 py-4">Fecha</th>
                                <th class="px-5 py-4">Paciente</th>
                                <th class="px-5 py-4">Receta</th>
                                <th class="px-5 py-4">Medicamentos</th>
                                <th class="px-5 py-4">Responsable</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-100">
                            <tr
                                v-for="dispensacion in dispensacionesFiltradas"
                                :key="dispensacion.id"
                                class="align-top hover:bg-slate-50"
                            >
                                <td class="px-5 py-4 font-semibold text-blue-800">
                                    #{{ dispensacion.id }}
                                </td>

                                <td class="px-5 py-4">
                                    {{ formatearFecha(dispensacion.fecha_dispensacion) }}
                                </td>

                                <td class="px-5 py-4">
                                    {{ nombrePaciente(dispensacion.paciente) }}
                                </td>

                                <td class="px-5 py-4">
                                    {{ dispensacion.receta?.codigo_receta || '—' }}
                                </td>

                                <td class="px-5 py-4">
                                    <div
                                        v-for="detalle in dispensacion.detalles || []"
                                        :key="detalle.id"
                                        class="mb-2 last:mb-0"
                                    >
                                        <p class="font-medium">
                                            {{ nombreMedicamento(detalle.medicamento) }}
                                        </p>

                                        <p class="text-xs text-slate-500">
                                            Cantidad: {{ detalle.cantidad }}
                                        </p>
                                    </div>
                                </td>

                                <td class="px-5 py-4">
                                    {{ dispensacion.usuario?.name || '—' }}
                                </td>
                            </tr>

                            <tr v-if="dispensacionesFiltradas.length === 0">
                                <td
                                    colspan="6"
                                    class="px-5 py-12 text-center text-slate-500"
                                >
                                    No hay dispensaciones registradas
                                    que coincidan con la búsqueda.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- MARCA DE AGUA ACADÉMICA -->
            <div
                class="pointer-events-none fixed bottom-3 right-3 z-50 rounded-lg border border-slate-200 bg-white/95 px-3 py-2 text-right text-[10px] leading-4 text-slate-600 shadow-sm"
            >
                <p class="font-semibold">
                    Uso académico · INACAP
                </p>

                <p>
                    Sistema de simulación · Sin validez legal
                </p>
            </div>
        </main>
    </div>
</template>
