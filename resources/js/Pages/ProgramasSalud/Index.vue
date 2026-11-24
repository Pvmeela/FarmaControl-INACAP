<script setup>
import FarmaLayout from '../../Layouts/FarmaLayout.vue';
import { computed, ref } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';

const props = defineProps({
    programas: {
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
});

// ICONOS

const iconos = {
    corazon: 'M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1.1-1.1a5.5 5.5 0 0 0-7.8 7.8L12 21l8.8-8.6a5.5 5.5 0 0 0 0-7.8z M3 12h5l2-4 4 8 2-4h5',
    pacientes: 'M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2 M13 7a4 4 0 1 1-8 0 4 4 0 0 1 8 0 M22 21v-2a4 4 0 0 0-3-3.9 M16 3.1a4 4 0 0 1 0 7.8',
    documento: 'M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z M14 2v6h6 M8 13h8 M8 17h8',
    medicamento: 'M10.5 3.5a5.7 5.7 0 0 1 8 8l-7 7a5.7 5.7 0 0 1-8-8z M7 7l10 10',
    buscar: 'M21 21l-5-5 M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0',
    agregar: 'M12 5v14 M5 12h14',
    volver: 'M19 12H5 M12 19l-7-7 7-7',
    editar: 'M12 20h9 M16 3a2.8 2.8 0 0 1 4 4L9 18l-5 1 1-5z',
    cerrar: 'M6 6l12 12 M18 6L6 18',
    estado: 'M12 2v10 M6.3 5.7a9 9 0 1 0 11.4 0',
    check: 'M5 12l4 4L19 6',
    alerta: 'M12 9v4 M12 17h.01 M10.3 3.8L2.5 18a2 2 0 0 0 1.8 3h15.4a2 2 0 0 0 1.8-3L13.7 3.8a2 2 0 0 0-3.4 0',
    calendario: 'M8 2v4 M16 2v4 M3 10h18 M5 4h14a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2',
};

// ESTADOS

const busqueda = ref('');
const filtroTipo = ref('');
const filtroEstado = ref('');

const modal = ref(null);
const pestanas = ref({});

const mensaje = ref('');
const mensajeError = ref('');
const cambiandoEstado = ref(false);

const programaSeleccionado = ref(null);
const medicamentoSeleccionado = ref(null);
const modoEdicion = ref(false);

const fechaActual = new Date();

const fechaLocal = [
    fechaActual.getFullYear(),
    String(fechaActual.getMonth() + 1).padStart(2, '0'),
    String(fechaActual.getDate()).padStart(2, '0'),
].join('-');

const fechaFormateada = fechaActual.toLocaleDateString(
    'es-CL',
    {
        weekday: 'long',
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    }
);

// FORMULARIOS

const formularioPrograma = useForm({
    nombre: '',
    tipo: 'GES',
    descripcion: '',
});

const formularioAsignacion = useForm({
    paciente_id: '',
    programa_id: '',
    fecha_ingreso: fechaLocal,
});

const formularioCanasta = useForm({
    medicamento_id: '',
    cantidad_maxima: '',
    periodo_dias: 30,
    cobertura: 100,
});

// INDICADORES

const programasActivos = computed(() =>
    props.programas.filter(programa => programa.activo)
);

const totalGES = computed(() =>
    programasActivos.value.filter(
        programa => programa.tipo === 'GES'
    ).length
);

const totalCronicos = computed(() =>
    programasActivos.value.filter(
        programa => programa.tipo === 'Cronico'
    ).length
);

const totalBeneficiarios = computed(() => {
    const ids = new Set();

    programasActivos.value.forEach(programa => {
        (programa.pacientes || []).forEach(paciente => {
            if (
                paciente.activo &&
                paciente.pivot?.estado === 'Activo'
            ) {
                ids.add(paciente.id);
            }
        });
    });

    return ids.size;
});

const totalMedicamentos = computed(() => {
    const ids = new Set();

    programasActivos.value.forEach(programa => {
        (programa.medicamentos || []).forEach(medicamento => {
            if (
                medicamento.activo &&
                medicamento.pivot?.activo
            ) {
                ids.add(medicamento.id);
            }
        });
    });

    return ids.size;
});

const indicadores = computed(() => [
    {
        titulo: 'Programas GES/AUGE',
        valor: totalGES.value,
        descripcion: 'Programas activos',
        icono: 'corazon',
        color: 'lavanda',
    },
    {
        titulo: 'Programas crónicos',
        valor: totalCronicos.value,
        descripcion: 'Programas activos',
        icono: 'documento',
        color: 'azul',
    },
    {
        titulo: 'Beneficiarios',
        valor: totalBeneficiarios.value,
        descripcion: 'Pacientes activos',
        icono: 'pacientes',
        color: 'verde',
    },
    {
        titulo: 'Medicamentos',
        valor: totalMedicamentos.value,
        descripcion: 'En canastas activas',
        icono: 'medicamento',
        color: 'ambar',
    },
]);

// FILTROS

const programasFiltrados = computed(() => {
    const texto = busqueda.value.toLowerCase().trim();

    return props.programas.filter(programa => {
        const coincideBusqueda =
            programa.nombre.toLowerCase().includes(texto) ||
            (programa.descripcion || '')
                .toLowerCase()
                .includes(texto) ||
            (programa.pacientes || []).some(paciente =>
                `${paciente.nombres} ${paciente.apellidos} ${paciente.rut || ''}`
                    .toLowerCase()
                    .includes(texto)
            ) ||
            (programa.medicamentos || []).some(medicamento =>
                `${medicamento.nombre} ${medicamento.concentracion || ''} ${medicamento.codigo || ''}`
                    .toLowerCase()
                    .includes(texto)
            );

        const coincideTipo =
            !filtroTipo.value ||
            programa.tipo === filtroTipo.value;

        const coincideEstado =
            !filtroEstado.value ||
            (
                filtroEstado.value === 'activo'
                    ? Boolean(programa.activo)
                    : !programa.activo
            );

        return (
            coincideBusqueda &&
            coincideTipo &&
            coincideEstado
        );
    });
});

const medicamentosDisponibles = computed(() => {
    if (!programaSeleccionado.value) {
        return [];
    }

    const programa = props.programas.find(
        p => p.id === programaSeleccionado.value.id
    );

    const asociados = new Set(
        (programa?.medicamentos || []).map(m => m.id)
    );

    return props.medicamentos.filter(
        medicamento =>
            medicamento.activo &&
            !asociados.has(medicamento.id)
    );
});

function limpiarFiltros() {
    busqueda.value = '';
    filtroTipo.value = '';
    filtroEstado.value = '';
}

// FUNCIONES AUXILIARES

function beneficiariosActivos(programa) {
    return (programa.pacientes || []).filter(
        paciente =>
            paciente.activo &&
            paciente.pivot?.estado === 'Activo' &&
            programa.activo
    ).length;
}

function medicamentosActivos(programa) {
    return (programa.medicamentos || []).filter(
        medicamento =>
            medicamento.activo &&
            medicamento.pivot?.activo &&
            programa.activo
    ).length;
}

function formatearFecha(fecha) {
    if (!fecha) return 'Sin fecha';

    const partes = fecha.substring(0, 10).split('-');

    return `${partes[2]}/${partes[1]}/${partes[0]}`;
}

function formatearCobertura(cobertura) {
    return `${Number(cobertura).toLocaleString('es-CL')} %`;
}

function inicialesPaciente(paciente) {
    return (
        (paciente.nombres || '').charAt(0) +
        (paciente.apellidos || '').charAt(0)
    ).toUpperCase();
}

function pestanaActual(programaId) {
    return pestanas.value[programaId] || 'canasta';
}

function cambiarPestana(programaId, pestana) {
    pestanas.value[programaId] = pestana;
}

function limpiarMensajes() {
    mensaje.value = '';
    mensajeError.value = '';
}

function mostrarError(errors) {
    mensajeError.value =
        Object.values(errors || {}).flat().join(' ') ||
        'No fue posible completar la operación.';
}

// MODALES

function cerrarModal() {
    modal.value = null;

    formularioPrograma.reset();
    formularioPrograma.clearErrors();

    formularioAsignacion.reset();
    formularioAsignacion.clearErrors();

    formularioCanasta.reset();
    formularioCanasta.clearErrors();

    programaSeleccionado.value = null;
    medicamentoSeleccionado.value = null;
    modoEdicion.value = false;
}

function solicitarCierre() {
    if (
        formularioPrograma.processing ||
        formularioAsignacion.processing ||
        formularioCanasta.processing
    ) {
        return;
    }

    cerrarModal();
}

// PROGRAMAS

function abrirRegistro() {
    limpiarMensajes();

    formularioPrograma.reset();
    formularioPrograma.clearErrors();

    modal.value = 'programa';
}

function registrarPrograma() {
    limpiarMensajes();

    formularioPrograma.post('/programas-salud', {
        preserveScroll: true,

        onSuccess: () => {
            cerrarModal();

            mensaje.value =
                'Programa de salud registrado correctamente.';
        },
    });
}

// BENEFICIARIOS

function abrirAsignacion(programaId = '') {
    limpiarMensajes();

    formularioAsignacion.reset();
    formularioAsignacion.clearErrors();

    formularioAsignacion.programa_id = programaId;

    modal.value = 'paciente';
}

function asignarPaciente() {
    limpiarMensajes();

    const programaId = formularioAsignacion.programa_id;

    formularioAsignacion.post('/programas-salud/asignar', {
        preserveScroll: true,

        onSuccess: () => {
            cambiarPestana(programaId, 'pacientes');

            cerrarModal();

            mensaje.value =
                'Paciente incorporado correctamente al programa.';
        },
    });
}

function cambiarEstadoPaciente(programa, paciente) {
    const nuevoEstado =
        paciente.pivot.estado === 'Activo'
            ? 'Inactivo'
            : 'Activo';

    if (
        !confirm(
            `¿Deseas cambiar la participación a ${nuevoEstado}?`
        )
    ) {
        return;
    }

    limpiarMensajes();

    cambiandoEstado.value = true;

    router.patch(
        `/programas-salud/${programa.id}/pacientes/${paciente.id}/estado`,
        {
            estado: nuevoEstado,
        },
        {
            preserveScroll: true,

            onSuccess: () => {
                mensaje.value =
                    'Estado del beneficiario actualizado.';
            },

            onError: mostrarError,

            onFinish: () => {
                cambiandoEstado.value = false;
            },
        }
    );
}

// CANASTAS

function abrirCanasta(programa) {
    limpiarMensajes();

    formularioCanasta.reset();
    formularioCanasta.clearErrors();

    programaSeleccionado.value = programa;
    medicamentoSeleccionado.value = null;
    modoEdicion.value = false;

    modal.value = 'canasta';
}

function editarCanasta(programa, medicamento) {
    limpiarMensajes();

    formularioCanasta.reset();
    formularioCanasta.clearErrors();

    programaSeleccionado.value = programa;
    medicamentoSeleccionado.value = medicamento;

    modoEdicion.value = true;

    formularioCanasta.medicamento_id = medicamento.id;

    formularioCanasta.cantidad_maxima =
        medicamento.pivot.cantidad_maxima;

    formularioCanasta.periodo_dias =
        medicamento.pivot.periodo_dias;

    formularioCanasta.cobertura =
        Number(medicamento.pivot.cobertura);

    modal.value = 'canasta';
}

function guardarCanasta() {
    if (!programaSeleccionado.value) return;

    limpiarMensajes();

    const programaId = programaSeleccionado.value.id;

    const opciones = {
        preserveScroll: true,

        onSuccess: () => {
            cambiarPestana(programaId, 'canasta');

            mensaje.value = modoEdicion.value
                ? 'Canasta actualizada correctamente.'
                : 'Medicamento incorporado correctamente.';

            cerrarModal();
        },
    };

    if (modoEdicion.value) {
        formularioCanasta.put(
            `/programas-salud/${programaId}/medicamentos/${medicamentoSeleccionado.value.id}`,
            opciones
        );

        return;
    }

    formularioCanasta.post(
        `/programas-salud/${programaId}/medicamentos`,
        opciones
    );
}

function cambiarEstadoMedicamento(programa, medicamento) {
    const nuevoEstado = !medicamento.pivot.activo;

    const accion = nuevoEstado
        ? 'activar'
        : 'desactivar';

    if (
        !confirm(
            `¿Deseas ${accion} ${medicamento.nombre} en esta canasta?`
        )
    ) {
        return;
    }

    limpiarMensajes();

    cambiandoEstado.value = true;

    router.patch(
        `/programas-salud/${programa.id}/medicamentos/${medicamento.id}/estado`,
        {
            activo: nuevoEstado,
        },
        {
            preserveScroll: true,

            onSuccess: () => {
                mensaje.value = nuevoEstado
                    ? 'Medicamento habilitado en la canasta.'
                    : 'Medicamento deshabilitado en la canasta.';
            },

            onError: mostrarError,

            onFinish: () => {
                cambiandoEstado.value = false;
            },
        }
    );
}
</script>

<template>
    <Head title="Programas de salud | FarmaControl" />

    <FarmaLayout
        titulo="Programas de salud"
        subtitulo="Gestión de programas GES/AUGE y pacientes crónicos."
    >
        <div class="pagina-programas">

            <!-- NAVEGACIÓN SUPERIOR -->

            <div class="navegacion-superior">

                <Link
                    href="/dashboard"
                    class="boton-volver"
                >
                    <svg
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    >
                        <path :d="iconos.volver" />
                    </svg>

                    Volver al inicio
                </Link>

                <div class="fecha-actual">
                    <svg
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.7"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    >
                        <path :d="iconos.calendario" />
                    </svg>

                    {{ fechaFormateada }}
                </div>

            </div>

            <!-- BANNER PRINCIPAL -->

            <section class="banner">

                <div class="circulo-decorativo circulo-uno"></div>
                <div class="circulo-decorativo circulo-dos"></div>

                <div class="banner-contenido">

                    <span class="banner-etiqueta">
                        <span class="punto"></span>
                        FARMACIA ASISTENCIAL · CESFAM
                    </span>

                    <h1>Programas de salud</h1>

                    <p>
                        Administra los programas GES/AUGE y crónicos,
                        sus beneficiarios y las canastas de medicamentos
                        desde un solo lugar.
                    </p>

                    <div class="banner-acciones">

                        <button
                            class="boton principal"
                            @click="abrirRegistro"
                        >
                            <svg
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                stroke-linecap="round"
                            >
                                <path :d="iconos.agregar" />
                            </svg>

                            Nuevo programa
                        </button>

                        <button
                            class="boton blanco"
                            @click="abrirAsignacion()"
                        >
                            <svg
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.7"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            >
                                <path :d="iconos.pacientes" />
                            </svg>

                            Incorporar paciente
                        </button>

                    </div>

                </div>

                <!-- Ilustración vectorial -->

                <div class="banner-ilustracion" aria-hidden="true">

                    <svg
                        viewBox="0 0 250 220"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    >
                        <circle
                            cx="125"
                            cy="110"
                            r="95"
                            opacity=".18"
                        />

                        <circle
                            cx="125"
                            cy="110"
                            r="78"
                            opacity=".15"
                        />

                        <rect
                            x="65"
                            y="34"
                            width="118"
                            height="158"
                            rx="15"
                            fill="#ffffff"
                            fill-opacity=".2"
                        />

                        <rect
                            x="99"
                            y="23"
                            width="50"
                            height="26"
                            rx="7"
                            fill="#e3d5f5"
                        />

                        <path
                            d="M124 69v37 M105.5 87.5h37"
                            stroke-width="5"
                        />

                        <path d="M89 126h70" />
                        <path d="M89 139h58" />
                        <path d="M89 152h45" />

                        <circle
                            cx="183"
                            cy="172"
                            r="30"
                            fill="#f8f7fc"
                        />

                        <path
                            d="M170 172l9 9 17-19"
                            stroke-width="3.5"
                        />

                        <path
                            d="M30 62v18 M21 71h18"
                            opacity=".7"
                        />

                        <path
                            d="M210 43v18 M201 52h18"
                            opacity=".7"
                        />

                        <circle
                            cx="42"
                            cy="155"
                            r="5"
                            opacity=".5"
                        />

                        <circle
                            cx="216"
                            cy="112"
                            r="4"
                            opacity=".5"
                        />

                    </svg>

                </div>

            </section>

            <!-- INDICADORES -->

            <div class="titulo-bloque">
                <div>
                    <h2>Resumen general</h2>
                    <p>Estado actual de los programas asistenciales.</p>
                </div>

                <span class="etiqueta-resumen">
                    <span class="punto-verde"></span>
                    Información del sistema
                </span>
            </div>

            <section class="indicadores">

                <article
                    v-for="indicador in indicadores"
                    :key="indicador.titulo"
                    class="tarjeta-indicador"
                >
                    <div
                        class="indicador-icono"
                        :class="indicador.color"
                    >
                        <svg
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.7"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        >
                            <path :d="iconos[indicador.icono]" />
                        </svg>
                    </div>

                    <div class="indicador-informacion">

                        <span class="indicador-titulo">
                            {{ indicador.titulo }}
                        </span>

                        <strong>{{ indicador.valor }}</strong>

                        <span class="indicador-descripcion">
                            {{ indicador.descripcion }}
                        </span>

                    </div>
                </article>

            </section>

            <!-- MENSAJES -->

            <div
                v-if="mensaje"
                class="notificacion exito"
                role="status"
            >
                <svg
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                >
                    <path :d="iconos.check" />
                </svg>

                <span>{{ mensaje }}</span>

                <button
                    class="cerrar-notificacion"
                    aria-label="Cerrar mensaje"
                    @click="mensaje = ''"
                >
                    ×
                </button>
            </div>

            <div
                v-if="mensajeError"
                class="notificacion error-notificacion"
                role="alert"
            >
                <svg
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                >
                    <path :d="iconos.alerta" />
                </svg>

                <span>{{ mensajeError }}</span>

                <button
                    class="cerrar-notificacion"
                    aria-label="Cerrar mensaje"
                    @click="mensajeError = ''"
                >
                    ×
                </button>
            </div>

            <!-- DIRECTORIO DE PROGRAMAS -->

            <div class="titulo-bloque directorio-titulo">

                <div>
                    <h2>Directorio de programas</h2>

                    <p>
                        Consulta y administra las canastas
                        y los pacientes beneficiarios.
                    </p>
                </div>

                <span class="contador">
                    {{ programasFiltrados.length }}
                    {{ programasFiltrados.length === 1
                        ? 'programa'
                        : 'programas' }}
                </span>

            </div>

            <!-- BUSCADOR Y FILTROS -->

            <section class="panel-filtros">

                <div class="campo campo-busqueda">

                    <label for="buscar-programa">
                        Buscar
                    </label>

                    <div class="input-icono">

                        <svg
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                            stroke-linecap="round"
                        >
                            <path :d="iconos.buscar" />
                        </svg>

                        <input
                            id="buscar-programa"
                            v-model="busqueda"
                            type="search"
                            placeholder="Programa, paciente, RUT o medicamento..."
                        />

                    </div>

                </div>

                <div class="campo">

                    <label for="filtro-tipo">
                        Tipo de programa
                    </label>

                    <select
                        id="filtro-tipo"
                        v-model="filtroTipo"
                    >
                        <option value="">
                            Todos los tipos
                        </option>

                        <option value="GES">
                            GES/AUGE
                        </option>

                        <option value="Cronico">
                            Pacientes crónicos
                        </option>
                    </select>

                </div>

                <div class="campo">

                    <label for="filtro-estado">
                        Estado
                    </label>

                    <select
                        id="filtro-estado"
                        v-model="filtroEstado"
                    >
                        <option value="">Todos</option>
                        <option value="activo">Activos</option>
                        <option value="inactivo">Inactivos</option>
                    </select>

                </div>

                <button
                    class="boton limpiar"
                    @click="limpiarFiltros"
                >
                    Limpiar filtros
                </button>

            </section>

            <!-- ESTADO VACÍO -->

            <section
                v-if="programasFiltrados.length === 0"
                class="estado-vacio"
            >

                <div class="vacio-icono">
                    <svg
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.5"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    >
                        <path :d="iconos.documento" />
                    </svg>
                </div>

                <h3>
                    {{
                        programas.length === 0
                            ? 'Comienza registrando un programa'
                            : 'No se encontraron resultados'
                    }}
                </h3>

                <p v-if="programas.length === 0">
                    Registra tu primer programa de salud para
                    administrar sus medicamentos y beneficiarios.
                </p>

                <p v-else>
                    Prueba con otros términos de búsqueda
                    o modifica los filtros seleccionados.
                </p>

                <button
                    v-if="programas.length === 0"
                    class="boton principal"
                    @click="abrirRegistro"
                >
                    + Registrar programa
                </button>

                <button
                    v-else
                    class="boton secundario"
                    @click="limpiarFiltros"
                >
                    Limpiar filtros
                </button>

            </section>

            <!-- TARJETAS DE PROGRAMAS -->

            <section class="listado-programas">

                <article
                    v-for="programa in programasFiltrados"
                    :key="programa.id"
                    class="tarjeta-programa"
                >

                    <div class="programa-superior">

                        <div class="programa-identidad">

                            <div class="programa-icono">
                                <svg
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.6"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                >
                                    <path
                                        :d="programa.tipo === 'GES'
                                            ? iconos.corazon
                                            : iconos.documento"
                                    />
                                </svg>
                            </div>

                            <div class="programa-texto">

                                <div class="insignias">

                                    <span class="insignia tipo">
                                        {{
                                            programa.tipo === 'GES'
                                                ? 'GES/AUGE'
                                                : 'Programa crónico'
                                        }}
                                    </span>

                                    <span
                                        class="insignia"
                                        :class="programa.activo
                                            ? 'activo'
                                            : 'inactivo'"
                                    >
                                        <span class="estado-punto"></span>

                                        {{
                                            programa.activo
                                                ? 'Activo'
                                                : 'Inactivo'
                                        }}
                                    </span>

                                </div>

                                <h3>{{ programa.nombre }}</h3>

                                <p>
                                    {{
                                        programa.descripcion ||
                                        'Sin descripción registrada.'
                                    }}
                                </p>

                            </div>

                        </div>

                        <button
                            v-if="programa.activo"
                            class="boton secundario"
                            @click="abrirAsignacion(programa.id)"
                        >
                            <svg
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.7"
                                stroke-linecap="round"
                            >
                                <path :d="iconos.pacientes" />
                            </svg>

                            Incorporar paciente
                        </button>

                    </div>

                    <!-- RESUMEN DEL PROGRAMA -->

                    <div class="resumen-programa">

                        <div class="resumen-item">

                            <div class="resumen-icono verde">
                                <svg
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.7"
                                    stroke-linecap="round"
                                >
                                    <path :d="iconos.pacientes" />
                                </svg>
                            </div>

                            <div>
                                <span>Beneficiarios activos</span>

                                <strong>
                                    {{ beneficiariosActivos(programa) }}
                                </strong>
                            </div>

                        </div>

                        <div class="resumen-item">

                            <div class="resumen-icono lavanda">
                                <svg
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.7"
                                    stroke-linecap="round"
                                >
                                    <path :d="iconos.medicamento" />
                                </svg>
                            </div>

                            <div>
                                <span>Medicamentos habilitados</span>

                                <strong>
                                    {{ medicamentosActivos(programa) }}
                                </strong>
                            </div>

                        </div>

                    </div>

                    <!-- PESTAÑAS -->

                    <div class="pestanas">

                        <button
                            class="pestana"
                            :class="{
                                seleccionada:
                                    pestanaActual(programa.id) === 'canasta'
                            }"
                            :aria-pressed="pestanaActual(programa.id) === 'canasta'"
                            @click="cambiarPestana(programa.id, 'canasta')"
                        >
                            <svg
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.7"
                                stroke-linecap="round"
                            >
                                <path :d="iconos.medicamento" />
                            </svg>

                            Canasta de medicamentos

                            <span class="numero-pestana">
                                {{ programa.medicamentos?.length || 0 }}
                            </span>
                        </button>

                        <button
                            class="pestana"
                            :class="{
                                seleccionada:
                                    pestanaActual(programa.id) === 'pacientes'
                            }"
                            :aria-pressed="pestanaActual(programa.id) === 'pacientes'"
                            @click="cambiarPestana(programa.id, 'pacientes')"
                        >
                            <svg
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.7"
                                stroke-linecap="round"
                            >
                                <path :d="iconos.pacientes" />
                            </svg>

                            Beneficiarios

                            <span class="numero-pestana">
                                {{ programa.pacientes?.length || 0 }}
                            </span>
                        </button>

                    </div>

                    <!-- CONTENIDO CANASTAS -->

                    <div
                        v-if="pestanaActual(programa.id) === 'canasta'"
                        class="contenido-pestana"
                    >

                        <div class="seccion-encabezado">

                            <div>
                                <h4>Canasta de medicamentos</h4>

                                <p>
                                    Configuración de cuotas,
                                    períodos y cobertura.
                                </p>
                            </div>

                            <button
                                v-if="programa.activo"
                                class="boton principal"
                                @click="abrirCanasta(programa)"
                            >
                                <svg
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    stroke-linecap="round"
                                >
                                    <path :d="iconos.agregar" />
                                </svg>

                                Agregar medicamento
                            </button>

                        </div>

                        <div
                            v-if="!programa.medicamentos?.length"
                            class="vacio-interno"
                        >
                            <div class="vacio-icono pequeno-icono">
                                <svg
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.5"
                                >
                                    <path :d="iconos.medicamento" />
                                </svg>
                            </div>

                            <strong>Canasta sin medicamentos</strong>

                            <p>
                                Todavía no existen medicamentos
                                asociados a este programa.
                            </p>
                        </div>

                        <div v-else class="tabla-contenedor">

                            <table>

                                <thead>
                                    <tr>
                                        <th>Medicamento</th>
                                        <th>Cuota máxima</th>
                                        <th>Período</th>
                                        <th>Cobertura</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>

                                <tbody>

                                    <tr
                                        v-for="medicamento in programa.medicamentos"
                                        :key="medicamento.id"
                                    >

                                        <td>
                                            <div class="celda-identidad">

                                                <div class="celda-icono">
                                                    <svg
                                                        viewBox="0 0 24 24"
                                                        fill="none"
                                                        stroke="currentColor"
                                                        stroke-width="1.6"
                                                    >
                                                        <path :d="iconos.medicamento" />
                                                    </svg>
                                                </div>

                                                <div>
                                                    <strong>
                                                        {{ medicamento.nombre }}
                                                    </strong>

                                                    <small>
                                                        {{ medicamento.concentracion }}
                                                        ·
                                                        {{ medicamento.forma_farmaceutica }}
                                                    </small>

                                                    <small>
                                                        {{ medicamento.codigo }}
                                                    </small>

                                                    <small
                                                        v-if="!medicamento.activo"
                                                        class="texto-error"
                                                    >
                                                        Desactivado en catálogo
                                                    </small>
                                                </div>

                                            </div>
                                        </td>

                                        <td>
                                            <strong>
                                                {{ medicamento.pivot.cantidad_maxima }}
                                            </strong>

                                            <small>Unidades</small>
                                        </td>

                                        <td>
                                            {{ medicamento.pivot.periodo_dias }}
                                            días
                                        </td>

                                        <td>
                                            <span class="cobertura">
                                                {{
                                                    formatearCobertura(
                                                        medicamento.pivot.cobertura
                                                    )
                                                }}
                                            </span>
                                        </td>

                                        <td>
                                            <span
                                                class="insignia"
                                                :class="medicamento.pivot.activo &&
                                                    medicamento.activo &&
                                                    programa.activo
                                                    ? 'activo'
                                                    : 'inactivo'"
                                            >
                                                <span class="estado-punto"></span>

                                                {{
                                                    medicamento.pivot.activo &&
                                                    medicamento.activo &&
                                                    programa.activo
                                                        ? 'Activo'
                                                        : 'Inactivo'
                                                }}
                                            </span>
                                        </td>

                                        <td>

                                            <div class="acciones-tabla">

                                                <button
                                                    class="boton-icono editar"
                                                    title="Editar medicamento"
                                                    aria-label="Editar medicamento"
                                                    :disabled="!programa.activo"
                                                    @click="editarCanasta(
                                                        programa,
                                                        medicamento
                                                    )"
                                                >
                                                    <svg
                                                        viewBox="0 0 24 24"
                                                        fill="none"
                                                        stroke="currentColor"
                                                        stroke-width="1.8"
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                    >
                                                        <path :d="iconos.editar" />
                                                    </svg>
                                                </button>

                                                <button
                                                    class="boton-icono"
                                                    :class="medicamento.pivot.activo
                                                        ? 'desactivar'
                                                        : 'activar'"
                                                    :title="medicamento.pivot.activo
                                                        ? 'Desactivar'
                                                        : 'Activar'"
                                                    :aria-label="medicamento.pivot.activo
                                                        ? 'Desactivar medicamento'
                                                        : 'Activar medicamento'"
                                                    :disabled="cambiandoEstado ||
                                                        (!medicamento.pivot.activo &&
                                                        (!medicamento.activo ||
                                                        !programa.activo))"
                                                    @click="cambiarEstadoMedicamento(
                                                        programa,
                                                        medicamento
                                                    )"
                                                >
                                                    <svg
                                                        viewBox="0 0 24 24"
                                                        fill="none"
                                                        stroke="currentColor"
                                                        stroke-width="1.8"
                                                        stroke-linecap="round"
                                                    >
                                                        <path :d="iconos.estado" />
                                                    </svg>
                                                </button>

                                            </div>

                                        </td>

                                    </tr>

                                </tbody>

                            </table>

                        </div>

                    </div>

                    <!-- CONTENIDO BENEFICIARIOS -->

                    <div v-else class="contenido-pestana">

                        <div class="seccion-encabezado">

                            <div>
                                <h4>Pacientes beneficiarios</h4>

                                <p>
                                    Participación de pacientes
                                    en el programa de salud.
                                </p>
                            </div>

                            <button
                                v-if="programa.activo"
                                class="boton principal"
                                @click="abrirAsignacion(programa.id)"
                            >
                                + Incorporar paciente
                            </button>

                        </div>

                        <div
                            v-if="!programa.pacientes?.length"
                            class="vacio-interno"
                        >
                            <div class="vacio-icono pequeno-icono">
                                <svg
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.5"
                                    stroke-linecap="round"
                                >
                                    <path :d="iconos.pacientes" />
                                </svg>
                            </div>

                            <strong>Sin beneficiarios registrados</strong>

                            <p>
                                Incorpora pacientes para asociarlos
                                a este programa de salud.
                            </p>
                        </div>

                        <div v-else class="tabla-contenedor">

                            <table>

                                <thead>
                                    <tr>
                                        <th>Paciente</th>
                                        <th>RUT</th>
                                        <th>Fecha de ingreso</th>
                                        <th>Participación</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>

                                <tbody>

                                    <tr
                                        v-for="paciente in programa.pacientes"
                                        :key="paciente.id"
                                    >

                                        <td>
                                            <div class="celda-identidad">

                                                <div class="avatar-paciente">
                                                    {{ inicialesPaciente(paciente) }}
                                                </div>

                                                <div>
                                                    <strong>
                                                        {{ paciente.nombres }}
                                                        {{ paciente.apellidos }}
                                                    </strong>

                                                    <small
                                                        v-if="!paciente.activo"
                                                        class="texto-error"
                                                    >
                                                        Paciente desactivado
                                                    </small>
                                                </div>

                                            </div>
                                        </td>

                                        <td>
                                            {{ paciente.rut || 'Sin RUT' }}
                                        </td>

                                        <td>
                                            {{
                                                formatearFecha(
                                                    paciente.pivot.fecha_ingreso
                                                )
                                            }}
                                        </td>

                                        <td>
                                            <span
                                                class="insignia"
                                                :class="paciente.pivot.estado === 'Activo' &&
                                                    paciente.activo &&
                                                    programa.activo
                                                    ? 'activo'
                                                    : 'inactivo'"
                                            >
                                                <span class="estado-punto"></span>

                                                {{
                                                    paciente.pivot.estado === 'Activo' &&
                                                    paciente.activo &&
                                                    programa.activo
                                                        ? 'Activo'
                                                        : 'Inactivo'
                                                }}
                                            </span>
                                        </td>

                                        <td>
                                            <button
                                                class="boton pequeno"
                                                :class="paciente.pivot.estado === 'Activo'
                                                    ? 'desactivar'
                                                    : 'activar'"
                                                :disabled="cambiandoEstado ||
                                                    (paciente.pivot.estado !== 'Activo' &&
                                                    (!paciente.activo ||
                                                    !programa.activo))"
                                                @click="cambiarEstadoPaciente(
                                                    programa,
                                                    paciente
                                                )"
                                            >
                                                {{
                                                    paciente.pivot.estado === 'Activo'
                                                        ? 'Desactivar'
                                                        : 'Activar'
                                                }}
                                            </button>
                                        </td>

                                    </tr>

                                </tbody>

                            </table>

                        </div>

                    </div>

                </article>

            </section>

            <!-- MODALES -->

            <Teleport to="body">

                <Transition name="modal">

                    <div
                        v-if="modal"
                        class="modal-fondo"
                        @click.self="solicitarCierre"
                        @keydown.esc="solicitarCierre"
                    >

                        <section
                            class="modal-panel"
                            role="dialog"
                            aria-modal="true"
                            aria-labelledby="modal-titulo"
                        >

                            <!-- Encabezado -->

                            <div class="modal-encabezado">

                                <div class="modal-identidad">

                                    <div class="modal-icono">

                                        <svg
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="1.7"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                        >
                                            <path
                                                :d="modal === 'programa'
                                                    ? iconos.documento
                                                    : modal === 'paciente'
                                                        ? iconos.pacientes
                                                        : iconos.medicamento"
                                            />
                                        </svg>

                                    </div>

                                    <div>

                                        <h2 id="modal-titulo">
                                            {{
                                                modal === 'programa'
                                                    ? 'Nuevo programa'
                                                    : modal === 'paciente'
                                                        ? 'Incorporar paciente'
                                                        : modoEdicion
                                                            ? 'Editar medicamento'
                                                            : 'Agregar medicamento'
                                            }}
                                        </h2>

                                        <p>Farmacia Asistencial · CESFAM</p>

                                    </div>

                                </div>

                                <button
                                    class="cerrar-modal"
                                    aria-label="Cerrar"
                                    @click="solicitarCierre"
                                >
                                    <svg
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                        stroke-linecap="round"
                                    >
                                        <path :d="iconos.cerrar" />
                                    </svg>
                                </button>

                            </div>

                            <!-- FORMULARIO PROGRAMA -->

                            <form
                                v-if="modal === 'programa'"
                                @submit.prevent="registrarPrograma"
                            >

                                <div class="modal-cuerpo">

                                    <div class="campo">

                                        <label for="nombre-programa">
                                            Nombre del programa *
                                        </label>

                                        <input
                                            id="nombre-programa"
                                            v-model="formularioPrograma.nombre"
                                            maxlength="150"
                                            placeholder="Ej. Diabetes mellitus tipo 2"
                                            required
                                        />

                                        <span class="error">
                                            {{ formularioPrograma.errors.nombre }}
                                        </span>

                                    </div>

                                    <div class="campo">

                                        <label for="tipo-programa">
                                            Tipo de programa *
                                        </label>

                                        <select
                                            id="tipo-programa"
                                            v-model="formularioPrograma.tipo"
                                            required
                                        >
                                            <option value="GES">
                                                GES/AUGE
                                            </option>

                                            <option value="Cronico">
                                                Pacientes crónicos
                                            </option>
                                        </select>

                                        <span class="error">
                                            {{ formularioPrograma.errors.tipo }}
                                        </span>

                                    </div>

                                    <div class="campo">

                                        <label for="descripcion-programa">
                                            Descripción
                                        </label>

                                        <textarea
                                            id="descripcion-programa"
                                            v-model="formularioPrograma.descripcion"
                                            maxlength="1000"
                                            rows="4"
                                            placeholder="Describe el programa de salud..."
                                        ></textarea>

                                        <span class="error">
                                            {{ formularioPrograma.errors.descripcion }}
                                        </span>

                                    </div>

                                </div>

                                <div class="modal-pie">

                                    <button
                                        type="button"
                                        class="boton secundario"
                                        @click="solicitarCierre"
                                    >
                                        Cancelar
                                    </button>

                                    <button
                                        type="submit"
                                        class="boton principal"
                                        :disabled="formularioPrograma.processing"
                                    >
                                        {{
                                            formularioPrograma.processing
                                                ? 'Guardando...'
                                                : 'Guardar programa'
                                        }}
                                    </button>

                                </div>

                            </form>

                            <!-- FORMULARIO PACIENTE -->

                            <form
                                v-else-if="modal === 'paciente'"
                                @submit.prevent="asignarPaciente"
                            >

                                <div class="modal-cuerpo">

                                    <div class="campo">

                                        <label for="paciente">
                                            Paciente *
                                        </label>

                                        <select
                                            id="paciente"
                                            v-model="formularioAsignacion.paciente_id"
                                            required
                                        >
                                            <option value="" disabled>
                                                Selecciona un paciente
                                            </option>

                                            <option
                                                v-for="paciente in pacientes"
                                                :key="paciente.id"
                                                :value="paciente.id"
                                            >
                                                {{ paciente.nombres }}
                                                {{ paciente.apellidos }}
                                                — {{ paciente.rut }}
                                            </option>
                                        </select>

                                        <span class="error">
                                            {{ formularioAsignacion.errors.paciente_id }}
                                        </span>

                                    </div>

                                    <div class="campo">

                                        <label for="programa-asignacion">
                                            Programa de salud *
                                        </label>

                                        <select
                                            id="programa-asignacion"
                                            v-model="formularioAsignacion.programa_id"
                                            required
                                        >
                                            <option value="" disabled>
                                                Selecciona un programa
                                            </option>

                                            <option
                                                v-for="programa in programasActivos"
                                                :key="programa.id"
                                                :value="programa.id"
                                            >
                                                {{ programa.nombre }}
                                                — {{ programa.tipo }}
                                            </option>
                                        </select>

                                        <span class="error">
                                            {{ formularioAsignacion.errors.programa_id }}
                                        </span>

                                    </div>

                                    <div class="campo">

                                        <label for="fecha-ingreso">
                                            Fecha de ingreso *
                                        </label>

                                        <input
                                            id="fecha-ingreso"
                                            v-model="formularioAsignacion.fecha_ingreso"
                                            type="date"
                                            :max="fechaLocal"
                                            required
                                        />

                                        <span class="error">
                                            {{ formularioAsignacion.errors.fecha_ingreso }}
                                        </span>

                                    </div>

                                    <div
                                        v-if="!pacientes.length ||
                                            !programasActivos.length"
                                        class="aviso"
                                    >
                                        Debes contar con pacientes
                                        y programas activos para
                                        registrar una incorporación.
                                    </div>

                                </div>

                                <div class="modal-pie">

                                    <button
                                        type="button"
                                        class="boton secundario"
                                        @click="solicitarCierre"
                                    >
                                        Cancelar
                                    </button>

                                    <button
                                        type="submit"
                                        class="boton principal"
                                        :disabled="formularioAsignacion.processing ||
                                            !pacientes.length ||
                                            !programasActivos.length"
                                    >
                                        {{
                                            formularioAsignacion.processing
                                                ? 'Guardando...'
                                                : 'Incorporar paciente'
                                        }}
                                    </button>

                                </div>

                            </form>

                            <!-- FORMULARIO CANASTA -->

                            <form
                                v-else
                                @submit.prevent="guardarCanasta"
                            >

                                <div class="modal-cuerpo">

                                    <div class="programa-seleccionado">

                                        <span>Programa de salud</span>

                                        <strong>
                                            {{ programaSeleccionado?.nombre }}
                                        </strong>

                                    </div>

                                    <div class="campo">

                                        <label for="medicamento-canasta">
                                            Medicamento *
                                        </label>

                                        <input
                                            v-if="modoEdicion"
                                            id="medicamento-canasta"
                                            :value="`${medicamentoSeleccionado?.nombre} ${medicamentoSeleccionado?.concentracion}`"
                                            readonly
                                        />

                                        <select
                                            v-else
                                            id="medicamento-canasta"
                                            v-model="formularioCanasta.medicamento_id"
                                            required
                                        >
                                            <option value="" disabled>
                                                Selecciona un medicamento
                                            </option>

                                            <option
                                                v-for="medicamento in medicamentosDisponibles"
                                                :key="medicamento.id"
                                                :value="medicamento.id"
                                            >
                                                {{ medicamento.nombre }}
                                                {{ medicamento.concentracion }}
                                                — {{ medicamento.codigo }}
                                            </option>
                                        </select>

                                        <span
                                            v-if="!modoEdicion &&
                                                !medicamentosDisponibles.length"
                                            class="ayuda"
                                        >
                                            No hay medicamentos activos
                                            disponibles para incorporar.
                                        </span>

                                        <span class="error">
                                            {{ formularioCanasta.errors.medicamento_id }}
                                        </span>

                                    </div>

                                    <div class="campos-dobles">

                                        <div class="campo">

                                            <label for="cuota">
                                                Cuota máxima *
                                            </label>

                                            <input
                                                id="cuota"
                                                v-model.number="formularioCanasta.cantidad_maxima"
                                                type="number"
                                                min="1"
                                                step="1"
                                                placeholder="Unidades"
                                                required
                                            />

                                            <span class="error">
                                                {{ formularioCanasta.errors.cantidad_maxima }}
                                            </span>

                                        </div>

                                        <div class="campo">

                                            <label for="periodo">
                                                Período en días *
                                            </label>

                                            <input
                                                id="periodo"
                                                v-model.number="formularioCanasta.periodo_dias"
                                                type="number"
                                                min="1"
                                                step="1"
                                                required
                                            />

                                            <span class="error">
                                                {{ formularioCanasta.errors.periodo_dias }}
                                            </span>

                                        </div>

                                    </div>

                                    <div class="campo">

                                        <label for="cobertura">
                                            Cobertura (%) *
                                        </label>

                                        <input
                                            id="cobertura"
                                            v-model.number="formularioCanasta.cobertura"
                                            type="number"
                                            min="0"
                                            max="100"
                                            step="0.01"
                                            required
                                        />

                                        <span class="ayuda">
                                            Parámetro de cobertura académica.
                                            No genera cobros al paciente.
                                        </span>

                                        <span class="error">
                                            {{ formularioCanasta.errors.cobertura }}
                                        </span>

                                    </div>

                                    <div class="aviso">

                                        <strong>
                                            Información académica
                                        </strong>

                                        <p>
                                            Las cuotas y coberturas
                                            corresponden a parámetros de
                                            simulación y no constituyen
                                            indicaciones médicas.
                                        </p>

                                    </div>

                                </div>

                                <div class="modal-pie">

                                    <button
                                        type="button"
                                        class="boton secundario"
                                        @click="solicitarCierre"
                                    >
                                        Cancelar
                                    </button>

                                    <button
                                        type="submit"
                                        class="boton principal"
                                        :disabled="formularioCanasta.processing ||
                                            (!modoEdicion &&
                                            !medicamentosDisponibles.length)"
                                    >
                                        {{
                                            formularioCanasta.processing
                                                ? 'Guardando...'
                                                : modoEdicion
                                                    ? 'Guardar cambios'
                                                    : 'Agregar medicamento'
                                        }}
                                    </button>

                                </div>

                            </form>

                            <div class="modal-nota">
                                Uso académico · INACAP
                                <br />
                                Sistema de simulación · Sin validez legal
                            </div>

                        </section>

                    </div>

                </Transition>

            </Teleport>

        </div>
    </FarmaLayout>
</template>

<style scoped>
* {
    box-sizing: border-box;
}

.pagina-programas,
.modal-fondo {
    --salud-primary: #665579;
    --salud-hover: #51435f;
    --salud-light: #e3d5f5;
    --salud-background: #f8f7fc;
    --salud-border: #e6e0ef;
    --salud-text: #333344;
    --salud-muted: #777181;

    font-family: 'Inter', Arial, sans-serif;
    color: var(--salud-text);
}

.pagina-programas {
    width: 100%;
    min-width: 0;
    padding-bottom: 90px;
}

button,
input,
select,
textarea {
    font-family: inherit;
}

button:disabled {
    opacity: .55;
    cursor: not-allowed;
}

button:focus-visible,
a:focus-visible {
    outline: 3px solid #b9a2d9;
    outline-offset: 3px;
}

/* NAVEGACIÓN */

.navegacion-superior {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 20px;
    flex-wrap: wrap;
}

.boton-volver {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 8px 12px;
    border: 1px solid var(--salud-border);
    border-radius: 8px;
    background: #ffffff;
    color: var(--salud-primary);
    text-decoration: none;
    font-size: 12px;
    font-weight: 600;
    transition: all .2s ease;
}

.boton-volver:hover {
    background: var(--salud-light);
    border-color: #d2bfea;
    transform: translateX(-2px);
}

.boton-volver svg,
.fecha-actual svg {
    width: 15px;
    height: 15px;
}

.fecha-actual {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 12px;
    color: var(--salud-muted);
    text-transform: capitalize;
}

/* BANNER */

.banner {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: space-between;
    min-height: 245px;
    padding: 34px 38px;
    background: linear-gradient(
        115deg,
        #e3d5f5 0%,
        #ede4f8 60%,
        #f4effb 100%
    );
    border: 1px solid #ddd0ed;
    border-radius: 20px;
    overflow: hidden;
    margin-bottom: 30px;
    box-shadow: 0 6px 24px #6655790a;
}

.banner-contenido {
    position: relative;
    z-index: 2;
    max-width: 680px;
}

.banner-etiqueta {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: #ffffff80;
    color: #665579;
    padding: 7px 11px;
    border: 1px solid #ffffff90;
    border-radius: 7px;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: .9px;
}

.punto {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: #8e75ae;
}

.banner h1 {
    font-size: clamp(26px, 3vw, 36px);
    font-weight: 800;
    color: #333344;
    letter-spacing: -1.1px;
    line-height: 1.2;
    margin: 17px 0 12px;
}

.banner p {
    max-width: 570px;
    font-size: 14px;
    line-height: 1.8;
    color: #665579;
    margin: 0;
}

.banner-acciones {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-top: 24px;
}

.banner-ilustracion {
    position: relative;
    flex: 0 0 230px;
    color: #a18bbd;
    z-index: 1;
}

.banner-ilustracion svg {
    width: 100%;
    display: block;
}

.circulo-decorativo {
    position: absolute;
    border: 1px solid #ffffff65;
    border-radius: 50%;
    pointer-events: none;
}

.circulo-uno {
    width: 360px;
    height: 360px;
    right: -70px;
    top: -180px;
}

.circulo-dos {
    width: 290px;
    height: 290px;
    right: 80px;
    bottom: -240px;
}

/* BOTONES */

.boton {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 11px 16px;
    border: 1px solid transparent;
    border-radius: 9px;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    white-space: nowrap;
    transition: all .2s ease;
}

.boton svg {
    width: 17px;
    height: 17px;
    flex-shrink: 0;
}

.principal {
    background: var(--salud-primary);
    color: #ffffff;
    box-shadow: 0 3px 8px #66557918;
}

.principal:hover:not(:disabled) {
    background: var(--salud-hover);
    transform: translateY(-1px);
}

.blanco {
    background: #ffffff;
    color: var(--salud-primary);
    border-color: #ddd0ed;
}

.blanco:hover:not(:disabled) {
    background: #f8f7fc;
}

.secundario {
    background: #f0e8f9;
    color: var(--salud-primary);
    border-color: #e0d3ef;
}

.secundario:hover:not(:disabled) {
    background: var(--salud-light);
}

.limpiar {
    background: #ffffff;
    color: var(--salud-muted);
    border-color: var(--salud-border);
}

.limpiar:hover {
    background: var(--salud-background);
    color: var(--salud-primary);
}

/* TÍTULOS */

.titulo-bloque {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 15px;
    margin-bottom: 17px;
}

.titulo-bloque h2 {
    font-size: 19px;
    font-weight: 750;
    color: var(--salud-text);
    letter-spacing: -.4px;
    margin: 0 0 5px;
}

.titulo-bloque p {
    font-size: 12px;
    color: var(--salud-muted);
    margin: 0;
    line-height: 1.6;
}

.etiqueta-resumen {
    display: flex;
    align-items: center;
    gap: 7px;
    color: var(--salud-muted);
    font-size: 11px;
}

.punto-verde {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: #22a06b;
}

/* INDICADORES */

.indicadores {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 16px;
    margin-bottom: 32px;
}

.tarjeta-indicador {
    display: flex;
    align-items: flex-start;
    gap: 15px;
    min-width: 0;
    background: #ffffff;
    padding: 22px 18px;
    border: 1px solid var(--salud-border);
    border-radius: 15px;
    box-shadow: 0 3px 15px #33334406;
    transition: all .2s ease;
}

.tarjeta-indicador:hover {
    transform: translateY(-3px);
    border-color: #d2bfea;
    box-shadow: 0 8px 22px #6655790c;
}

.indicador-icono {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: grid;
    place-items: center;
    flex-shrink: 0;
}

.indicador-icono svg {
    width: 25px;
    height: 25px;
}

.lavanda {
    background: #eee4fa;
    color: #8062a5;
}

.azul {
    background: #e4efff;
    color: #3970bc;
}

.verde {
    background: #e0f5eb;
    color: #20835a;
}

.ambar {
    background: #fff3dc;
    color: #b47a26;
}

.indicador-informacion {
    display: flex;
    flex-direction: column;
    gap: 6px;
    min-width: 0;
}

.indicador-titulo {
    font-size: 12px;
    color: #6e6879;
    font-weight: 600;
}

.indicador-informacion strong {
    font-size: 30px;
    font-weight: 750;
    color: var(--salud-text);
    line-height: 1.2;
}

.indicador-descripcion {
    font-size: 11px;
    color: #918b9b;
}

/* FILTROS */

.contador {
    padding: 7px 12px;
    border-radius: 8px;
    background: var(--salud-light);
    color: var(--salud-primary);
    font-size: 11px;
    font-weight: 600;
    white-space: nowrap;
}

.panel-filtros {
    display: grid;
    grid-template-columns: 2fr 1fr 1fr auto;
    gap: 16px;
    align-items: end;
    background: #ffffff;
    border: 1px solid var(--salud-border);
    border-radius: 14px;
    padding: 20px;
    margin-bottom: 24px;
    box-shadow: 0 3px 15px #33334404;
}

.campo {
    display: flex;
    flex-direction: column;
    gap: 8px;
    min-width: 0;
}

.campo label {
    color: var(--salud-text);
    font-size: 12px;
    font-weight: 650;
}

.campo input,
.campo select,
.campo textarea {
    width: 100%;
    min-width: 0;
    padding: 11px 12px;
    background: #ffffff;
    border: 1px solid var(--salud-border);
    border-radius: 9px;
    color: var(--salud-text);
    font-size: 13px;
    transition: all .2s ease;
}

.campo input,
.campo select {
    min-height: 42px;
}

.campo textarea {
    resize: vertical;
}

.campo input::placeholder,
.campo textarea::placeholder {
    color: #aaa3b3;
}

.campo input:focus,
.campo select:focus,
.campo textarea:focus {
    outline: none;
    border-color: #a58abb;
    box-shadow: 0 0 0 3px #e3d5f580;
}

.campo input[readonly] {
    background: #f8f7fc;
}

.input-icono {
    position: relative;
}

.input-icono svg {
    position: absolute;
    left: 13px;
    top: 50%;
    transform: translateY(-50%);
    width: 17px;
    height: 17px;
    color: #9b8bad;
    pointer-events: none;
}

.input-icono input {
    padding-left: 39px;
}

/* TARJETAS DE PROGRAMAS */

.listado-programas {
    display: flex;
    flex-direction: column;
    gap: 24px;
}

.tarjeta-programa {
    min-width: 0;
    background: #ffffff;
    border: 1px solid var(--salud-border);
    border-radius: 17px;
    overflow: hidden;
    box-shadow: 0 4px 20px #33334406;
}

.programa-superior {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
    padding: 25px;
}

.programa-identidad {
    display: flex;
    align-items: flex-start;
    gap: 16px;
    min-width: 0;
}

.programa-icono {
    width: 58px;
    height: 58px;
    flex-shrink: 0;
    display: grid;
    place-items: center;
    background: #eee4fa;
    color: #8062a5;
    border-radius: 15px;
}

.programa-icono svg {
    width: 29px;
    height: 29px;
}

.programa-texto {
    min-width: 0;
    overflow-wrap: anywhere;
}

.programa-texto h3 {
    font-size: 20px;
    font-weight: 750;
    color: var(--salud-text);
    margin: 9px 0 5px;
    letter-spacing: -.4px;
}

.programa-texto p {
    font-size: 13px;
    color: var(--salud-muted);
    line-height: 1.6;
    margin: 0;
}

.insignias {
    display: flex;
    flex-wrap: wrap;
    gap: 7px;
}

.insignia {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 5px 9px;
    border-radius: 6px;
    font-size: 10px;
    font-weight: 650;
    white-space: nowrap;
}

.tipo {
    background: #eee4fa;
    color: #795c9b;
}

.activo {
    background: #e1f5e9;
    color: #228252;
}

.inactivo {
    background: #fce7e9;
    color: #b74c58;
}

.estado-punto {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: currentColor;
}

/* RESUMEN */

.resumen-programa {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 15px;
    padding: 0 25px 24px;
}

.resumen-item {
    display: flex;
    align-items: center;
    gap: 13px;
    padding: 16px;
    background: #f8f7fc;
    border: 1px solid #eee9f4;
    border-radius: 12px;
}

.resumen-icono {
    display: grid;
    place-items: center;
    width: 42px;
    height: 42px;
    border-radius: 10px;
    flex-shrink: 0;
}

.resumen-icono svg {
    width: 22px;
    height: 22px;
}

.resumen-item > div:last-child {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.resumen-item span {
    font-size: 12px;
    color: var(--salud-muted);
}

.resumen-item strong {
    font-size: 23px;
    color: var(--salud-text);
    font-weight: 750;
}

/* PESTAÑAS */

.pestanas {
    display: flex;
    align-items: center;
    gap: 22px;
    padding: 0 25px;
    border-bottom: 1px solid var(--salud-border);
    overflow-x: auto;
}

.pestana {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    background: transparent;
    border: none;
    border-bottom: 3px solid transparent;
    padding: 15px 4px;
    color: var(--salud-muted);
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    white-space: nowrap;
    transition: all .2s ease;
}

.pestana svg {
    width: 18px;
    height: 18px;
}

.pestana:hover {
    color: var(--salud-primary);
}

.pestana.seleccionada {
    color: var(--salud-primary);
    border-bottom-color: #8e75ae;
}

.numero-pestana {
    padding: 3px 7px;
    background: #f0edf4;
    color: var(--salud-muted);
    border-radius: 6px;
    font-size: 10px;
}

.seleccionada .numero-pestana {
    background: var(--salud-light);
    color: var(--salud-primary);
}

.contenido-pestana {
    padding: 24px 25px;
}

.seccion-encabezado {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    margin-bottom: 20px;
}

.seccion-encabezado h4 {
    font-size: 15px;
    font-weight: 700;
    margin: 0 0 5px;
    color: var(--salud-text);
}

.seccion-encabezado p {
    font-size: 12px;
    color: var(--salud-muted);
    margin: 0;
}

/* TABLAS */

.tabla-contenedor {
    width: 100%;
    overflow-x: auto;
    border: 1px solid var(--salud-border);
    border-radius: 11px;
}

table {
    width: 100%;
    border-collapse: collapse;
    text-align: left;
    font-size: 12px;
}

th {
    padding: 13px 15px;
    background: #f4f0f9;
    color: #756587;
    font-size: 11px;
    font-weight: 650;
    white-space: nowrap;
    border-bottom: 1px solid var(--salud-border);
}

td {
    padding: 16px 15px;
    color: #625c6c;
    border-bottom: 1px solid #f0edf4;
    vertical-align: middle;
}

tbody tr:last-child td {
    border-bottom: none;
}

tbody tr:hover {
    background: #fbf9fd;
}

td strong {
    color: var(--salud-text);
    font-size: 12px;
    font-weight: 650;
}

td small {
    display: block;
    font-size: 11px;
    color: var(--salud-muted);
    margin-top: 4px;
}

.celda-identidad {
    display: flex;
    align-items: center;
    gap: 10px;
    min-width: 175px;
}

.celda-icono,
.avatar-paciente {
    width: 36px;
    height: 36px;
    border-radius: 9px;
    display: grid;
    place-items: center;
    background: #f0e8fa;
    color: var(--salud-primary);
    flex-shrink: 0;
}

.celda-icono svg {
    width: 19px;
    height: 19px;
}

.avatar-paciente {
    font-size: 11px;
    font-weight: 700;
    border-radius: 50%;
}

.cobertura {
    display: inline-block;
    padding: 6px 9px;
    background: #eee4fa;
    color: #665579;
    border-radius: 6px;
    font-weight: 650;
    white-space: nowrap;
}

.acciones-tabla {
    display: flex;
    align-items: center;
    gap: 7px;
}

.boton-icono {
    display: grid;
    place-items: center;
    width: 33px;
    height: 33px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    transition: all .2s ease;
}

.boton-icono svg {
    width: 16px;
    height: 16px;
}

.boton-icono:hover:not(:disabled) {
    transform: translateY(-2px);
    filter: brightness(.96);
}

.editar {
    background: #eee4fa;
    color: #795c9b;
}

.desactivar {
    background: #fce8eb;
    color: #bd5363;
}

.activar {
    background: #e1f5e9;
    color: #228252;
}

.pequeno {
    padding: 7px 11px;
    font-size: 11px;
}

/* ESTADOS VACÍOS */

.estado-vacio {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    padding: 55px 25px;
    background: #ffffff;
    border: 1px solid var(--salud-border);
    border-radius: 16px;
}

.vacio-icono {
    display: grid;
    place-items: center;
    width: 70px;
    height: 70px;
    border-radius: 20px;
    background: #eee4fa;
    color: #9b81b9;
    margin-bottom: 18px;
}

.vacio-icono svg {
    width: 34px;
    height: 34px;
}

.estado-vacio h3 {
    color: var(--salud-text);
    font-size: 17px;
    font-weight: 700;
    margin: 0 0 8px;
}

.estado-vacio p {
    max-width: 410px;
    font-size: 13px;
    line-height: 1.7;
    color: var(--salud-muted);
    margin: 0 0 22px;
}

.vacio-interno {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    padding: 32px 20px;
    border: 1px dashed #dcd2e8;
    border-radius: 12px;
    background: #fcfbfe;
}

.pequeno-icono {
    width: 47px;
    height: 47px;
    border-radius: 12px;
    margin-bottom: 12px;
}

.pequeno-icono svg {
    width: 24px;
    height: 24px;
}

.vacio-interno strong {
    font-size: 13px;
    color: var(--salud-text);
    font-weight: 650;
}

.vacio-interno p {
    font-size: 12px;
    color: var(--salud-muted);
    line-height: 1.6;
    margin: 7px 0 0;
}

/* NOTIFICACIONES */

.notificacion {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 15px 18px;
    border-radius: 11px;
    margin-bottom: 22px;
    font-size: 13px;
}

.notificacion > svg {
    width: 20px;
    height: 20px;
    flex-shrink: 0;
}

.notificacion > span {
    flex: 1;
}

.exito {
    background: #e9f8ef;
    color: #217a4e;
    border: 1px solid #ccebd9;
}

.error-notificacion {
    background: #fef0f1;
    color: #b74c58;
    border: 1px solid #f6d0d5;
}

.cerrar-notificacion {
    border: none;
    background: transparent;
    color: inherit;
    font-size: 22px;
    cursor: pointer;
}

/* MODALES */

.modal-fondo {
    position: fixed;
    inset: 0;
    z-index: 2000;
    background: #29243e85;
    backdrop-filter: blur(4px);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
}

.modal-panel {
    width: 100%;
    max-width: 580px;
    max-height: 92vh;
    overflow-y: auto;
    background: #ffffff;
    border: 1px solid #e6e0ef;
    border-radius: 20px;
    box-shadow: 0 25px 80px #29243e30;
}

.modal-encabezado {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 15px;
    padding: 24px;
    background: linear-gradient(
        120deg,
        #e3d5f5,
        #f5effb
    );
    border-bottom: 1px solid #e6e0ef;
}

.modal-identidad {
    display: flex;
    align-items: center;
    gap: 13px;
}

.modal-icono {
    display: grid;
    place-items: center;
    width: 45px;
    height: 45px;
    background: #ffffff85;
    color: #8062a5;
    border-radius: 12px;
    flex-shrink: 0;
}

.modal-icono svg {
    width: 24px;
    height: 24px;
}

.modal-encabezado h2 {
    font-size: 19px;
    font-weight: 750;
    color: #333344;
    margin: 0 0 5px;
}

.modal-encabezado p {
    font-size: 11px;
    color: #806d94;
    margin: 0;
}

.cerrar-modal {
    width: 33px;
    height: 33px;
    display: grid;
    place-items: center;
    background: #ffffff80;
    color: #665579;
    border: 1px solid #ffffff;
    border-radius: 8px;
    cursor: pointer;
}

.cerrar-modal svg {
    width: 18px;
    height: 18px;
}

.modal-cuerpo {
    display: flex;
    flex-direction: column;
    gap: 20px;
    padding: 26px;
}

.modal-pie {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    padding: 18px 26px;
    border-top: 1px solid #eee9f4;
    background: #fcfbfe;
}

.modal-nota {
    padding: 0 26px 16px;
    text-align: right;
    background: #fcfbfe;
    color: #918b9b;
    font-size: 10px;
    line-height: 1.6;
}

.campos-dobles {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 16px;
}

.programa-seleccionado {
    display: flex;
    flex-direction: column;
    gap: 7px;
    background: #f1eafa;
    border: 1px solid #e3d5f5;
    border-radius: 10px;
    padding: 15px;
}

.programa-seleccionado span {
    font-size: 11px;
    color: #806d94;
}

.programa-seleccionado strong {
    font-size: 14px;
    color: #333344;
}

.aviso {
    background: #f8f7fc;
    border: 1px solid #e6e0ef;
    border-left: 3px solid #a18bbd;
    border-radius: 8px;
    padding: 14px;
    font-size: 12px;
    color: #777181;
    line-height: 1.7;
}

.aviso strong {
    color: #665579;
    font-size: 12px;
}

.aviso p {
    margin: 5px 0 0;
}

.ayuda {
    font-size: 11px;
    color: #918b9b;
    line-height: 1.6;
}

.error,
.texto-error {
    color: #bd5363 !important;
    font-size: 11px;
}

.error:empty {
    display: none;
}

/* ANIMACIONES */

.modal-enter-active,
.modal-leave-active {
    transition: opacity .2s ease;
}

.modal-enter-active .modal-panel,
.modal-leave-active .modal-panel {
    transition: transform .2s ease;
}

.modal-enter-from,
.modal-leave-to {
    opacity: 0;
}

.modal-enter-from .modal-panel,
.modal-leave-to .modal-panel {
    transform: translateY(15px) scale(.98);
}

/* RESPONSIVE */

@media (max-width: 1300px) {
    .indicadores {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .panel-filtros {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (max-width: 950px) {
    .banner {
        padding: 28px;
    }

    .banner-ilustracion {
        flex-basis: 170px;
    }

    .programa-superior {
        flex-wrap: wrap;
    }

    .seccion-encabezado {
        flex-wrap: wrap;
    }
}

@media (max-width: 700px) {
    .banner {
        min-height: auto;
        padding: 25px;
    }

    .banner-ilustracion {
        display: none;
    }

    .indicadores {
        gap: 12px;
    }

    .tarjeta-indicador {
        padding: 17px;
    }

    .etiqueta-resumen {
        display: none;
    }

    .panel-filtros {
        grid-template-columns: 1fr;
    }

    .programa-superior {
        padding: 18px;
    }

    .resumen-programa {
        padding: 0 18px 18px;
    }

    .pestanas {
        padding: 0 18px;
        gap: 15px;
    }

    .contenido-pestana {
        padding: 18px;
    }
}

@media (max-width: 480px) {
    .indicadores,
    .resumen-programa,
    .campos-dobles {
        grid-template-columns: 1fr;
    }

    .banner-acciones {
        flex-direction: column;
    }

    .banner-acciones .boton {
        width: 100%;
    }

    .titulo-bloque {
        align-items: flex-start;
    }

    .programa-icono {
        width: 44px;
        height: 44px;
    }

    .programa-icono svg {
        width: 23px;
        height: 23px;
    }

    .programa-texto h3 {
        font-size: 17px;
    }

    .programa-superior > .boton {
        width: 100%;
    }

    .modal-fondo {
        padding: 12px;
    }

    .modal-cuerpo,
    .modal-encabezado,
    .modal-pie {
        padding: 18px;
    }

    .modal-pie .boton {
        flex: 1;
    }

    .modal-encabezado h2 {
        font-size: 16px;
    }
}

@media (prefers-reduced-motion: reduce) {
    *,
    *::before,
    *::after {
        transition: none !important;
        animation: none !important;
    }
}
</style>