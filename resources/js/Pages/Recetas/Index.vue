<script setup>
import { computed, ref } from 'vue'
import { Head, Link, useForm, usePage } from '@inertiajs/vue3'

const props = defineProps({
    recetas: {
        type: Array,
        default: () => [],
    },
    pacientes: {
        type: Array,
        default: () => [],
    },
    medicamentos: {
        type: Array,
        default: () => [],
    },
})

const page = usePage()
const busqueda = ref('')

const fechaHoy = new Date()
const hoy = [
    fechaHoy.getFullYear(),
    String(fechaHoy.getMonth() + 1).padStart(2, '0'),
    String(fechaHoy.getDate()).padStart(2, '0'),
].join('-')

const form = useForm({
    codigo_receta: '',
    paciente_id: '',
    fecha_emision: hoy,
    fecha_vencimiento: '',
    profesional_prescriptor: '',
    observaciones: '',
    detalles: [
        {
            medicamento_id: '',
            cantidad_prescrita: 1,
            indicaciones: '',
        },
    ],
})

const recetasFiltradas = computed(() => {
    const termino = busqueda.value.trim().toLowerCase()

    if (!termino) return props.recetas

    return props.recetas.filter((receta) => {
        const paciente = receta.paciente
        const contenido = [
            receta.codigo_receta,
            paciente?.codigo_paciente,
            paciente?.nombres,
            paciente?.apellidos,
            receta.profesional_prescriptor,
        ].join(' ').toLowerCase()

        return contenido.includes(termino)
    })
})

const recetasVigentes = computed(() =>
    props.recetas.filter(
        (receta) => receta.vigencia_actual === 'vigente'
    ).length
)

const recetasVencidas = computed(() =>
    props.recetas.filter(
        (receta) => receta.vigencia_actual === 'vencida'
    ).length
)

function agregarMedicamento() {
    form.detalles.push({
        medicamento_id: '',
        cantidad_prescrita: 1,
        indicaciones: '',
    })
}

function quitarMedicamento(indice) {
    if (form.detalles.length > 1) {
        form.detalles.splice(indice, 1)
    }
}

function registrarReceta() {
    if (form.processing) return

    form.post('/recetas', {
        preserveScroll: true,
        onSuccess: () => {
            form.reset()
            form.fecha_emision = hoy
            form.detalles = [
                {
                    medicamento_id: '',
                    cantidad_prescrita: 1,
                    indicaciones: '',
                },
            ]
        },
    })
}

function formatearFecha(valor) {
    if (!valor) return '—'

    const partes = String(valor).slice(0, 10).split('-')

    if (partes.length !== 3) return String(valor)

    return `${partes[2]}/${partes[1]}/${partes[0]}`
}

function nombrePaciente(paciente) {
    if (!paciente) return 'Paciente no disponible'

    return `${paciente.nombres} ${paciente.apellidos}`
}

function nombreMedicamento(medicamento) {
    if (!medicamento) return 'Medicamento no disponible'

    return medicamento.nombre
}

function etiquetaVigencia(vigencia) {
    const etiquetas = {
        vigente: 'Vigente',
        vencida: 'Vencida',
        utilizada: 'Utilizada',
        anulada: 'Anulada',
        no_vigente: 'No vigente',
    }

    return etiquetas[vigencia] || vigencia || 'Sin estado'
}

function claseVigencia(vigencia) {
    const clases = {
        vigente: 'bg-green-100 text-green-700',
        vencida: 'bg-red-100 text-red-700',
        utilizada: 'bg-blue-100 text-blue-700',
        anulada: 'bg-slate-200 text-slate-700',
        no_vigente: 'bg-amber-100 text-amber-700',
    }

    return clases[vigencia] || 'bg-slate-100 text-slate-600'
}

const mensajeExito = computed(
    () => page.props.flash?.success || ''
)
</script>

<template>
    <Head title="Recetas farmacéuticas | FarmaControl" />

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
                        Recetas farmacéuticas
                    </h1>

                    <p class="mt-2 text-sm text-slate-500">
                        Registro y consulta de prescripciones simuladas.
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
                        Recetas registradas
                    </p>

                    <p class="mt-3 text-3xl font-bold text-blue-800">
                        {{ recetas.length }}
                    </p>
                </div>

                <div
                    class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"
                >
                    <p class="text-sm font-medium text-slate-500">
                        Recetas vigentes
                    </p>

                    <p class="mt-3 text-3xl font-bold text-green-700">
                        {{ recetasVigentes }}
                    </p>
                </div>

                <div
                    class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"
                >
                    <p class="text-sm font-medium text-slate-500">
                        Recetas vencidas
                    </p>

                    <p class="mt-3 text-3xl font-bold text-red-700">
                        {{ recetasVencidas }}
                    </p>
                </div>

            </section>

            <!-- FORMULARIO -->
            <section
                class="mb-8 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"
            >
                <h2 class="text-xl font-bold text-slate-900">
                    Registrar receta simulada
                </h2>

                <p class="mt-2 text-sm text-slate-500">
                    Utiliza únicamente pacientes, profesionales y
                    prescripciones ficticias.
                </p>

                <form
                    @submit.prevent="registrarReceta"
                    class="mt-6 space-y-6"
                >
                    <div class="grid gap-5 md:grid-cols-2">

                        <!-- CÓDIGO -->
                        <div>
                            <label
                                for="codigo_receta"
                                class="mb-2 block text-sm font-semibold"
                            >
                                Código de receta
                            </label>

                            <input
                                id="codigo_receta"
                                v-model="form.codigo_receta"
                                type="text"
                                maxlength="30"
                                required
                                placeholder="Ej.: REC-001"
                                class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100"
                            />

                            <p
                                v-if="form.errors.codigo_receta"
                                class="mt-2 text-xs text-red-600"
                            >
                                {{ form.errors.codigo_receta }}
                            </p>
                        </div>

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
                                class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm"
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

                        <!-- EMISIÓN -->
                        <div>
                            <label
                                for="fecha_emision"
                                class="mb-2 block text-sm font-semibold"
                            >
                                Fecha de emisión
                            </label>

                            <input
                                id="fecha_emision"
                                v-model="form.fecha_emision"
                                type="date"
                                :max="hoy"
                                required
                                class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm"
                            />

                            <p
                                v-if="form.errors.fecha_emision"
                                class="mt-2 text-xs text-red-600"
                            >
                                {{ form.errors.fecha_emision }}
                            </p>
                        </div>

                        <!-- VENCIMIENTO -->
                        <div>
                            <label
                                for="fecha_vencimiento"
                                class="mb-2 block text-sm font-semibold"
                            >
                                Fecha de vencimiento
                            </label>

                            <input
                                id="fecha_vencimiento"
                                v-model="form.fecha_vencimiento"
                                type="date"
                                :min="form.fecha_emision || hoy"
                                required
                                class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm"
                            />

                            <p
                                v-if="form.errors.fecha_vencimiento"
                                class="mt-2 text-xs text-red-600"
                            >
                                {{ form.errors.fecha_vencimiento }}
                            </p>
                        </div>

                        <!-- PROFESIONAL -->
                        <div>
                            <label
                                for="profesional_prescriptor"
                                class="mb-2 block text-sm font-semibold"
                            >
                                Profesional prescriptor simulado
                            </label>

                            <input
                                id="profesional_prescriptor"
                                v-model="form.profesional_prescriptor"
                                type="text"
                                maxlength="150"
                                required
                                placeholder="Ej.: Dra. Andrea López (ficticia)"
                                class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm"
                            />

                            <p
                                v-if="form.errors.profesional_prescriptor"
                                class="mt-2 text-xs text-red-600"
                            >
                                {{ form.errors.profesional_prescriptor }}
                            </p>
                        </div>

                        <!-- OBSERVACIONES -->
                        <div>
                            <label
                                for="observaciones"
                                class="mb-2 block text-sm font-semibold"
                            >
                                Observaciones (opcional)
                            </label>

                            <input
                                id="observaciones"
                                v-model="form.observaciones"
                                type="text"
                                maxlength="255"
                                placeholder="Observaciones académicas"
                                class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm"
                            />

                            <p
                                v-if="form.errors.observaciones"
                                class="mt-2 text-xs text-red-600"
                            >
                                {{ form.errors.observaciones }}
                            </p>
                        </div>
                    </div>

                    <!-- MEDICAMENTOS PRESCRITOS -->
                    <div class="border-t border-slate-200 pt-6">

                        <div
                            class="mb-5 flex flex-wrap items-center justify-between gap-3"
                        >
                            <div>
                                <h3 class="text-lg font-bold">
                                    Medicamentos prescritos
                                </h3>

                                <p class="mt-1 text-sm text-slate-500">
                                    Agrega los medicamentos de la receta.
                                </p>
                            </div>

                            <button
                                type="button"
                                @click="agregarMedicamento"
                                class="rounded-xl border border-blue-200 bg-blue-50 px-4 py-2 text-sm font-semibold text-blue-800 hover:bg-blue-100"
                            >
                                + Agregar medicamento
                            </button>
                        </div>

                        <p
                            v-if="form.errors.detalles"
                            class="mb-4 text-sm text-red-600"
                        >
                            {{ form.errors.detalles }}
                        </p>

                        <div
                            v-for="(detalle, indice) in form.detalles"
                            :key="indice"
                            class="mb-4 rounded-xl border border-slate-200 bg-slate-50 p-4"
                        >
                            <div
                                class="mb-4 flex items-center justify-between"
                            >
                                <h4 class="font-semibold text-slate-800">
                                    Medicamento {{ indice + 1 }}
                                </h4>

                                <button
                                    v-if="form.detalles.length > 1"
                                    type="button"
                                    @click="quitarMedicamento(indice)"
                                    class="text-sm font-semibold text-red-600 hover:text-red-800"
                                >
                                    Quitar
                                </button>
                            </div>

                            <div class="grid gap-4 md:grid-cols-3">

                                <div>
                                    <label
                                        class="mb-2 block text-sm font-semibold"
                                    >
                                        Medicamento
                                    </label>

                                    <select
                                        v-model="detalle.medicamento_id"
                                        required
                                        class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm"
                                    >
                                        <option value="">
                                            Seleccionar
                                        </option>

                                        <option
                                            v-for="medicamento in medicamentos"
                                            :key="medicamento.id"
                                            :value="medicamento.id"
                                        >
                                            {{ medicamento.nombre }}
                                            - {{ medicamento.concentracion }}
                                        </option>
                                    </select>

                                    <p
                                        v-if="form.errors[`detalles.${indice}.medicamento_id`]"
                                        class="mt-2 text-xs text-red-600"
                                    >
                                        {{
                                            form.errors[
                                                `detalles.${indice}.medicamento_id`
                                            ]
                                        }}
                                    </p>
                                </div>

                                <div>
                                    <label
                                        class="mb-2 block text-sm font-semibold"
                                    >
                                        Cantidad prescrita
                                    </label>

                                    <input
                                        v-model.number="detalle.cantidad_prescrita"
                                        type="number"
                                        min="1"
                                        step="1"
                                        required
                                        class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm"
                                    />

                                    <p
                                        v-if="form.errors[`detalles.${indice}.cantidad_prescrita`]"
                                        class="mt-2 text-xs text-red-600"
                                    >
                                        {{
                                            form.errors[
                                                `detalles.${indice}.cantidad_prescrita`
                                            ]
                                        }}
                                    </p>
                                </div>

                                <div>
                                    <label
                                        class="mb-2 block text-sm font-semibold"
                                    >
                                        Indicaciones (opcional)
                                    </label>

                                    <input
                                        v-model="detalle.indicaciones"
                                        type="text"
                                        maxlength="255"
                                        placeholder="Indicaciones ficticias"
                                        class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm"
                                    />

                                    <p
                                        v-if="form.errors[`detalles.${indice}.indicaciones`]"
                                        class="mt-2 text-xs text-red-600"
                                    >
                                        {{
                                            form.errors[
                                                `detalles.${indice}.indicaciones`
                                            ]
                                        }}
                                    </p>
                                </div>

                            </div>
                        </div>
                    </div>

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="rounded-xl bg-blue-800 px-6 py-3 text-sm font-semibold text-white transition hover:bg-blue-900 disabled:opacity-50"
                    >
                        {{
                            form.processing
                                ? 'Registrando receta...'
                                : 'Registrar receta simulada'
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
                            Recetas registradas
                        </h2>

                        <p class="mt-1 text-sm text-slate-500">
                            Historial de recetas ficticias.
                        </p>
                    </div>

                    <input
                        v-model="busqueda"
                        type="search"
                        placeholder="Buscar receta o paciente"
                        class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm sm:max-w-sm"
                    />
                </div>

                <div class="overflow-x-auto">
                    <table
                        class="min-w-full divide-y divide-slate-200 text-left text-sm"
                    >
                        <thead class="bg-slate-50">
                            <tr class="text-xs uppercase tracking-wide text-slate-500">
                                <th class="px-5 py-4">Código</th>
                                <th class="px-5 py-4">Paciente</th>
                                <th class="px-5 py-4">Emisión</th>
                                <th class="px-5 py-4">Vencimiento</th>
                                <th class="px-5 py-4">Medicamentos</th>
                                <th class="px-5 py-4">Vigencia</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-100">
                            <tr
                                v-for="receta in recetasFiltradas"
                                :key="receta.id"
                                class="align-top hover:bg-slate-50"
                            >
                                <td class="px-5 py-4 font-semibold text-blue-800">
                                    {{ receta.codigo_receta }}
                                </td>

                                <td class="px-5 py-4">
                                    {{ nombrePaciente(receta.paciente) }}
                                </td>

                                <td class="px-5 py-4">
                                    {{ formatearFecha(receta.fecha_emision) }}
                                </td>

                                <td class="px-5 py-4">
                                    {{ formatearFecha(receta.fecha_vencimiento) }}
                                </td>

                                <td class="px-5 py-4">
                                    <div
                                        v-for="detalle in receta.detalles || []"
                                        :key="detalle.id"
                                        class="mb-2 last:mb-0"
                                    >
                                        <p class="font-medium">
                                            {{ nombreMedicamento(detalle.medicamento) }}
                                        </p>

                                        <p class="text-xs text-slate-500">
                                            Cantidad:
                                            {{ detalle.cantidad_prescrita }}
                                        </p>

                                        <p
                                            v-if="detalle.indicaciones"
                                            class="text-xs text-slate-500"
                                        >
                                            {{ detalle.indicaciones }}
                                        </p>
                                    </div>
                                </td>

                                <td class="px-5 py-4">
                                    <span
                                        :class="claseVigencia(receta.vigencia_actual)"
                                        class="inline-flex rounded-full px-3 py-1 text-xs font-semibold"
                                    >
                                        {{ etiquetaVigencia(receta.vigencia_actual) }}
                                    </span>
                                </td>
                            </tr>

                            <tr v-if="recetasFiltradas.length === 0">
                                <td
                                    colspan="6"
                                    class="px-5 py-12 text-center text-slate-500"
                                >
                                    No hay recetas que coincidan
                                    con la búsqueda.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- MARCA DE AGUA -->
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
