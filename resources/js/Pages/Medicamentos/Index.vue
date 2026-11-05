
<script setup>
import { ref } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';

defineProps({
    medicamentos: {
        type: Array,
        default: () => [],
    },
});

const mostrarFormulario = ref(false);
const mostrarEdicion = ref(false);
const medicamentoEnEdicion = ref(null);
const mensajeExito = ref('');
const cambiandoEstadoId = ref(null);

const form = useForm({
    nombre: '',
    principio_activo: '',
    concentracion: '',
    forma_farmaceutica: '',
    presentacion: '',
    codigo: '',
});

const formEditar = useForm({
    nombre: '',
    principio_activo: '',
    concentracion: '',
    forma_farmaceutica: '',
    presentacion: '',
    codigo: '',
});

const campos = [
    {
        nombre: 'nombre',
        etiqueta: 'Nombre del medicamento',
        ejemplo: 'Ej: Paracetamol',
        maximo: 150,
    },
    {
        nombre: 'principio_activo',
        etiqueta: 'Principio activo',
        ejemplo: 'Ej: Paracetamol',
        maximo: 150,
    },
    {
        nombre: 'concentracion',
        etiqueta: 'Concentración',
        ejemplo: 'Ej: 500 mg',
        maximo: 100,
    },
    {
        nombre: 'forma_farmaceutica',
        etiqueta: 'Forma farmacéutica',
        ejemplo: 'Ej: Comprimido',
        maximo: 100,
    },
    {
        nombre: 'presentacion',
        etiqueta: 'Presentación',
        ejemplo: 'Ej: Caja de 30 comprimidos',
        maximo: 150,
    },
    {
        nombre: 'codigo',
        etiqueta: 'Código del medicamento',
        ejemplo: 'Ej: MED-001',
        maximo: 50,
    },
];

function alternarFormulario() {
    if (mostrarEdicion.value) {
        cancelarEdicion();
    }

    mostrarFormulario.value = !mostrarFormulario.value;
    mensajeExito.value = '';

    if (!mostrarFormulario.value) {
        form.clearErrors();
    }
}

function guardarMedicamento() {
    mensajeExito.value = '';

    form.post('/medicamentos', {
        preserveScroll: true,

        onSuccess: () => {
            form.reset();
            mostrarFormulario.value = false;
            mensajeExito.value =
                'Medicamento registrado correctamente.';
        },
    });
}

function editarMedicamento(medicamento) {
    mostrarFormulario.value = false;
    mostrarEdicion.value = true;
    medicamentoEnEdicion.value = medicamento.id;
    mensajeExito.value = '';

    for (const campo of campos) {
        formEditar[campo.nombre] =
            medicamento[campo.nombre] ?? '';
    }

    formEditar.clearErrors();
}

function cancelarEdicion() {
    mostrarEdicion.value = false;
    medicamentoEnEdicion.value = null;
    formEditar.reset();
    formEditar.clearErrors();
}

function actualizarMedicamento() {
    if (medicamentoEnEdicion.value === null) {
        return;
    }

    mensajeExito.value = '';

    formEditar.put(
        `/medicamentos/${medicamentoEnEdicion.value}`,
        {
            preserveScroll: true,

            onSuccess: () => {
                cancelarEdicion();

                mensajeExito.value =
                    'Medicamento actualizado correctamente.';
            },
        }
    );
}

function cambiarEstadoMedicamento(medicamento) {
    if (cambiandoEstadoId.value !== null) {
        return;
    }

    const estabaActivo = medicamento.activo;
    const accion = estabaActivo
        ? 'desactivar'
        : 'activar';

    const confirmado = window.confirm(
        `¿Estás seguro de que deseas ${accion} el medicamento "${medicamento.nombre}"?`
    );

    if (!confirmado) {
        return;
    }

    mensajeExito.value = '';
    cambiandoEstadoId.value = medicamento.id;

    router.patch(
        `/medicamentos/${medicamento.id}/estado`,
        {},
        {
            preserveScroll: true,

            onSuccess: () => {
                mensajeExito.value = estabaActivo
                    ? 'Medicamento desactivado correctamente.'
                    : 'Medicamento activado correctamente.';
            },

            onFinish: () => {
                cambiandoEstadoId.value = null;
            },
        }
    );
}
</script>

<template>
    <Head title="Medicamentos | FarmaControl" />

    <div class="min-h-screen bg-slate-100 text-slate-800">

        <!-- Encabezado original -->
        <header class="bg-white border-b border-slate-200">
            <div class="max-w-6xl mx-auto px-6 py-5">
                <h1 class="text-2xl font-bold text-blue-800">
                    FarmaControl
                </h1>

                <p class="text-sm text-slate-500">
                    Gestión de medicamentos
                </p>
            </div>
        </header>

        <main class="max-w-6xl mx-auto px-6 py-10">

            <!-- Navegación -->
            <Link
                href="/dashboard"
                class="inline-flex items-center rounded-lg
                       bg-white px-4 py-2.5 text-sm
                       font-semibold text-blue-800
                       shadow-sm border border-slate-200
                       hover:bg-blue-50 transition"
            >
                Panel principal
            </Link>

            <!-- Título y botón -->
            <div
                class="flex flex-wrap items-center
                       justify-between gap-4 mt-8 mb-6"
            >
                <div>
                    <h2 class="text-3xl font-bold">
                        Catálogo de medicamentos
                    </h2>

                    <p class="text-slate-500 mt-2">
                        Administración de medicamentos
                        registrados en FarmaControl.
                    </p>
                </div>

                <button
                    type="button"
                    @click="alternarFormulario"
                    class="bg-blue-700 hover:bg-blue-800
                           text-white font-semibold
                           px-5 py-3 rounded-lg transition"
                >
                    {{ mostrarFormulario
                        ? 'Cancelar registro'
                        : '+ Nuevo medicamento' }}
                </button>
            </div>

            <!-- Mensaje de éxito -->
            <div
                v-if="mensajeExito"
                class="mb-6 rounded-lg border border-green-200
                       bg-green-50 p-4 text-green-800"
                role="status"
            >
                {{ mensajeExito }}
            </div>

            <!-- Formulario para registrar -->
            <section
                v-if="mostrarFormulario"
                class="bg-white rounded-xl shadow-sm
                       border border-slate-200 p-6 mb-8"
            >
                <h3 class="text-xl font-bold mb-6">
                    Registrar medicamento
                </h3>

                <form
                    @submit.prevent="guardarMedicamento"
                    class="grid grid-cols-1 md:grid-cols-2 gap-5"
                >
                    <div
                        v-for="campo in campos"
                        :key="campo.nombre"
                    >
                        <label
                            :for="`crear-${campo.nombre}`"
                            class="block font-medium mb-2"
                        >
                            {{ campo.etiqueta }} *
                        </label>

                        <input
                            :id="`crear-${campo.nombre}`"
                            v-model="form[campo.nombre]"
                            type="text"
                            :maxlength="campo.maximo"
                            required
                            class="w-full rounded-lg border
                                   border-slate-300 p-3"
                            :placeholder="campo.ejemplo"
                        />

                        <p
                            v-if="form.errors[campo.nombre]"
                            class="text-red-600 text-sm mt-1"
                        >
                            {{ form.errors[campo.nombre] }}
                        </p>
                    </div>

                    <div
                        class="md:col-span-2 flex flex-wrap
                               justify-end gap-3 mt-4"
                    >
                        <button
                            type="button"
                            @click="mostrarFormulario = false"
                            class="px-5 py-3 rounded-lg
                                   border border-slate-300
                                   hover:bg-slate-100"
                        >
                            Cancelar
                        </button>

                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-6 py-3 rounded-lg
                                   bg-blue-700 text-white
                                   font-semibold hover:bg-blue-800
                                   disabled:opacity-50"
                        >
                            {{ form.processing
                                ? 'Guardando...'
                                : 'Guardar medicamento' }}
                        </button>
                    </div>
                </form>
            </section>

            <!-- Formulario para editar -->
            <section
                v-if="mostrarEdicion"
                class="bg-white rounded-xl shadow-sm
                       border border-slate-200 p-6 mb-8"
            >
                <h3 class="text-xl font-bold mb-2">
                    Editar medicamento
                </h3>

                <p class="text-sm text-slate-500 mb-6">
                    Modifica los datos del medicamento seleccionado.
                </p>

                <form
                    @submit.prevent="actualizarMedicamento"
                    class="grid grid-cols-1 md:grid-cols-2 gap-5"
                >
                    <div
                        v-for="campo in campos"
                        :key="campo.nombre"
                    >
                        <label
                            :for="`editar-${campo.nombre}`"
                            class="block font-medium mb-2"
                        >
                            {{ campo.etiqueta }} *
                        </label>

                        <input
                            :id="`editar-${campo.nombre}`"
                            v-model="formEditar[campo.nombre]"
                            type="text"
                            :maxlength="campo.maximo"
                            required
                            class="w-full rounded-lg border
                                   border-slate-300 p-3"
                            :placeholder="campo.ejemplo"
                        />

                        <p
                            v-if="formEditar.errors[campo.nombre]"
                            class="text-red-600 text-sm mt-1"
                        >
                            {{ formEditar.errors[campo.nombre] }}
                        </p>
                    </div>

                    <div
                        class="md:col-span-2 flex flex-wrap
                               justify-end gap-3 mt-4"
                    >
                        <button
                            type="button"
                            @click="cancelarEdicion"
                            class="px-5 py-3 rounded-lg
                                   border border-slate-300
                                   hover:bg-slate-100"
                        >
                            Cancelar
                        </button>

                        <button
                            type="submit"
                            :disabled="formEditar.processing"
                            class="px-6 py-3 rounded-lg
                                   bg-blue-700 text-white
                                   font-semibold hover:bg-blue-800
                                   disabled:opacity-50"
                        >
                            {{ formEditar.processing
                                ? 'Actualizando...'
                                : 'Guardar cambios' }}
                        </button>
                    </div>
                </form>
            </section>

            <!-- Tabla de medicamentos -->
            <div
                class="bg-white rounded-xl shadow-sm
                       border border-slate-200 overflow-x-auto"
            >
                <table class="w-full text-left">
                    <thead class="bg-slate-50 border-b">
                        <tr>
                            <th class="p-4">Código</th>
                            <th class="p-4">Nombre</th>
                            <th class="p-4">Principio activo</th>
                            <th class="p-4">Concentración</th>
                            <th class="p-4">Estado</th>
                            <th class="p-4">Acciones</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr
                            v-for="medicamento in medicamentos"
                            :key="medicamento.id"
                            class="border-b hover:bg-slate-50"
                        >
                            <td class="p-4">
                                {{ medicamento.codigo }}
                            </td>

                            <td class="p-4">
                                {{ medicamento.nombre }}
                            </td>

                            <td class="p-4">
                                {{ medicamento.principio_activo }}
                            </td>

                            <td class="p-4">
                                {{ medicamento.concentracion }}
                            </td>

                            <!-- Estado actual -->
                            <td class="p-4">
                                <span
                                    :class="medicamento.activo
                                        ? 'text-green-700'
                                        : 'text-red-600'"
                                    class="font-medium"
                                >
                                    {{ medicamento.activo
                                        ? 'Activo'
                                        : 'Inactivo' }}
                                </span>
                            </td>

                            <!-- Botones de acciones -->
                            <td class="p-4">
                                <div class="flex flex-wrap gap-2">

                                    <!-- Editar -->
                                    <button
                                        type="button"
                                        @click="editarMedicamento(medicamento)"
                                        class="rounded-lg border
                                               border-blue-200
                                               bg-blue-50 px-4 py-2
                                               text-sm font-semibold
                                               text-blue-800
                                               hover:bg-blue-100
                                               transition"
                                    >
                                        Editar
                                    </button>

                                    <!-- Activar / Desactivar -->
                                    <button
                                        type="button"
                                        @click="cambiarEstadoMedicamento(medicamento)"
                                        :disabled="cambiandoEstadoId !== null"
                                        :class="medicamento.activo
                                            ? 'border-red-200 bg-red-50 text-red-700 hover:bg-red-100'
                                            : 'border-green-200 bg-green-50 text-green-700 hover:bg-green-100'"
                                        class="rounded-lg border
                                               px-4 py-2 text-sm
                                               font-semibold transition
                                               disabled:opacity-50
                                               disabled:cursor-not-allowed"
                                    >
                                        {{ cambiandoEstadoId === medicamento.id
                                            ? 'Procesando...'
                                            : medicamento.activo
                                                ? 'Desactivar'
                                                : 'Activar' }}
                                    </button>

                                </div>
                            </td>
                        </tr>

                        <tr v-if="medicamentos.length === 0">
                            <td
                                colspan="6"
                                class="p-8 text-center text-slate-500"
                            >
                                Todavía no hay medicamentos registrados.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </main>

        <!-- Marca de agua académica -->
        <div
            class="fixed bottom-4 right-4
                   bg-white/95 border border-slate-200
                   rounded-lg shadow-sm px-4 py-2
                   text-xs text-slate-600"
        >
            <p class="font-semibold">
                Uso académico · INACAP
            </p>

            <p>
                Sistema de simulación · Sin validez legal
            </p>
        </div>
    </div>
</template>
