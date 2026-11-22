<script setup>
import { computed, ref } from 'vue'
import { Head, Link, useForm } from '@inertiajs/vue3'

const props = defineProps({
    pacientes: {
        type: Array,
        default: () => [],
    },
    flash: {
        type: Object,
        default: () => ({}),
    },
})

const form = useForm({
    codigo_paciente: '',
    nombres: '',
    apellidos: '',
    fecha_nacimiento: '',
})

const busqueda = ref('')

const pacientesFiltrados = computed(() => {
    const termino = busqueda.value.trim().toLowerCase()

    if (!termino) return props.pacientes

    return props.pacientes.filter(paciente => {
        const contenido = [
            paciente.codigo_paciente,
            paciente.nombres,
            paciente.apellidos,
        ].join(' ').toLowerCase()

        return contenido.includes(termino)
    })
})

const pacientesActivos = computed(() =>
    props.pacientes.filter(paciente => paciente.activo).length
)

const pacientesInactivos = computed(() =>
    props.pacientes.filter(paciente => !paciente.activo).length
)

function registrarPaciente() {
    if (form.processing) return

    form.post('/pacientes', {
        preserveScroll: true,
        onSuccess: () => {
            form.reset()
        },
    })
}

function formatearFecha(valor) {
    if (!valor) return 'No registrada'

    const fecha = String(valor).slice(0, 10)
    const partes = fecha.split('-')

    if (partes.length !== 3) return fecha

    return `${partes[2]}/${partes[1]}/${partes[0]}`
}
</script>

<template>
    <Head title="Pacientes | FarmaControl" />

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
                        Gestión de pacientes
                    </h1>

                    <p class="mt-2 text-sm text-slate-500">
                        Registro y consulta de pacientes ficticios
                        para simulaciones académicas.
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
                v-if="flash.success"
                class="mb-6 rounded-xl border border-green-200 bg-green-50 px-5 py-4 text-sm font-medium text-green-800"
            >
                {{ flash.success }}
            </div>

            <!-- INDICADORES -->
            <section class="mb-8 grid gap-4 sm:grid-cols-3">

                <div
                    class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"
                >
                    <p class="text-sm font-medium text-slate-500">
                        Pacientes registrados
                    </p>

                    <p class="mt-3 text-3xl font-bold text-blue-800">
                        {{ pacientes.length }}
                    </p>
                </div>

                <div
                    class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"
                >
                    <p class="text-sm font-medium text-slate-500">
                        Pacientes activos
                    </p>

                    <p class="mt-3 text-3xl font-bold text-green-700">
                        {{ pacientesActivos }}
                    </p>
                </div>

                <div
                    class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"
                >
                    <p class="text-sm font-medium text-slate-500">
                        Pacientes inactivos
                    </p>

                    <p class="mt-3 text-3xl font-bold text-slate-600">
                        {{ pacientesInactivos }}
                    </p>
                </div>

            </section>

            <!-- FORMULARIO -->
            <section
                class="mb-8 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"
            >
                <div class="mb-6">
                    <h2 class="text-xl font-bold text-slate-900">
                        Registrar nuevo paciente
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        Utiliza únicamente datos ficticios.
                        No ingreses información de pacientes reales.
                    </p>
                </div>

                <form
                    @submit.prevent="registrarPaciente"
                    class="grid gap-5 md:grid-cols-2"
                >
                    <!-- CÓDIGO -->
                    <div>
                        <label
                            for="codigo_paciente"
                            class="mb-2 block text-sm font-semibold text-slate-700"
                        >
                            Código del paciente
                        </label>

                        <input
                            id="codigo_paciente"
                            v-model="form.codigo_paciente"
                            type="text"
                            maxlength="30"
                            required
                            placeholder="Ej.: PAC-001"
                            class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100"
                        />

                        <p
                            v-if="form.errors.codigo_paciente"
                            class="mt-2 text-xs text-red-600"
                        >
                            {{ form.errors.codigo_paciente }}
                        </p>
                    </div>

                    <!-- NOMBRES -->
                    <div>
                        <label
                            for="nombres"
                            class="mb-2 block text-sm font-semibold text-slate-700"
                        >
                            Nombres
                        </label>

                        <input
                            id="nombres"
                            v-model="form.nombres"
                            type="text"
                            maxlength="100"
                            required
                            placeholder="Ej.: Camila"
                            class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100"
                        />

                        <p
                            v-if="form.errors.nombres"
                            class="mt-2 text-xs text-red-600"
                        >
                            {{ form.errors.nombres }}
                        </p>
                    </div>

                    <!-- APELLIDOS -->
                    <div>
                        <label
                            for="apellidos"
                            class="mb-2 block text-sm font-semibold text-slate-700"
                        >
                            Apellidos
                        </label>

                        <input
                            id="apellidos"
                            v-model="form.apellidos"
                            type="text"
                            maxlength="100"
                            required
                            placeholder="Ej.: González Pérez"
                            class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100"
                        />

                        <p
                            v-if="form.errors.apellidos"
                            class="mt-2 text-xs text-red-600"
                        >
                            {{ form.errors.apellidos }}
                        </p>
                    </div>

                    <!-- FECHA DE NACIMIENTO -->
                    <div>
                        <label
                            for="fecha_nacimiento"
                            class="mb-2 block text-sm font-semibold text-slate-700"
                        >
                            Fecha de nacimiento (opcional)
                        </label>

                        <input
                            id="fecha_nacimiento"
                            v-model="form.fecha_nacimiento"
                            type="date"
                            class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100"
                        />

                        <p
                            v-if="form.errors.fecha_nacimiento"
                            class="mt-2 text-xs text-red-600"
                        >
                            {{ form.errors.fecha_nacimiento }}
                        </p>
                    </div>

                    <!-- BOTÓN -->
                    <div class="md:col-span-2">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="rounded-xl bg-blue-800 px-6 py-3 text-sm font-semibold text-white transition hover:bg-blue-900 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            {{
                                form.processing
                                    ? 'Registrando paciente...'
                                    : 'Registrar paciente'
                            }}
                        </button>
                    </div>
                </form>
            </section>

            <!-- TABLA -->
            <section
                class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"
            >
                <div
                    class="flex flex-col gap-4 border-b border-slate-200 p-6 sm:flex-row sm:items-center sm:justify-between"
                >
                    <div>
                        <h2 class="text-xl font-bold text-slate-900">
                            Pacientes registrados
                        </h2>

                        <p class="mt-1 text-sm text-slate-500">
                            Consulta de registros ficticios.
                        </p>
                    </div>

                    <input
                        v-model="busqueda"
                        type="search"
                        placeholder="Buscar por código o nombre"
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
                                <th class="px-5 py-4">Nombres</th>
                                <th class="px-5 py-4">Apellidos</th>
                                <th class="px-5 py-4">Nacimiento</th>
                                <th class="px-5 py-4">Estado</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-100">
                            <tr
                                v-for="paciente in pacientesFiltrados"
                                :key="paciente.id"
                                class="hover:bg-slate-50"
                            >
                                <td class="px-5 py-4 font-semibold text-blue-800">
                                    {{ paciente.codigo_paciente }}
                                </td>

                                <td class="px-5 py-4">
                                    {{ paciente.nombres }}
                                </td>

                                <td class="px-5 py-4">
                                    {{ paciente.apellidos }}
                                </td>

                                <td class="px-5 py-4 text-slate-600">
                                    {{ formatearFecha(paciente.fecha_nacimiento) }}
                                </td>

                                <td class="px-5 py-4">
                                    <span
                                        :class="paciente.activo
                                            ? 'bg-green-100 text-green-700'
                                            : 'bg-slate-100 text-slate-600'"
                                        class="inline-flex rounded-full px-3 py-1 text-xs font-semibold"
                                    >
                                        {{ paciente.activo ? 'Activo' : 'Inactivo' }}
                                    </span>
                                </td>
                            </tr>

                            <tr v-if="pacientesFiltrados.length === 0">
                                <td
                                    colspan="5"
                                    class="px-5 py-12 text-center text-slate-500"
                                >
                                    No hay pacientes registrados
                                    que coincidan con la búsqueda.
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

        </div>
    </div>
</template>
