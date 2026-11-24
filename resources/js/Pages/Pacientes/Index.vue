<script setup>
import { computed, ref, nextTick } from 'vue'
import { Head, Link, useForm, router, usePage } from '@inertiajs/vue3'
import FarmaLayout from '../../Layouts/FarmaLayout.vue'

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

const page = usePage()

const form = useForm({
    codigo_paciente: '',
    rut: '',
    nombres: '',
    apellidos: '',
    fecha_nacimiento: '',
    estado_alergias: 'desconocido',
    alergias: '',
})

const busqueda = ref('')
const filtroEstado = ref('todos')
const filtroAlergias = ref('todos')

const pacienteEditando = ref(null)
const formulario = ref(null)
const mostrarFormulario = ref(false)
const cambiandoEstado = ref(false)

const mensajeExito = computed(
    () => props.flash?.success || page.props.flash?.success
)

// Normalizar texto para facilitar las búsquedas.
function normalizarTexto(valor) {
    return String(valor ?? '')
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .toLowerCase()
        .trim()
}

// Normalizar RUT.
function normalizarRut(valor) {
    return String(valor ?? '')
        .replace(/[.\-\s]/g, '')
        .toUpperCase()
}

// Indicadores.
const pacientesActivos = computed(() =>
    props.pacientes.filter(paciente => paciente.activo).length
)

const pacientesInactivos = computed(() =>
    props.pacientes.filter(paciente => !paciente.activo).length
)

const pacientesConAlergias = computed(() =>
    props.pacientes.filter(
        paciente => paciente.estado_alergias === 'con_alergias'
    ).length
)

const indicadores = computed(() => [
    {
        titulo: 'Pacientes registrados',
        valor: props.pacientes.length,
        descripcion: 'Total de registros',
        icono: 'usuarios',
        color: 'principal',
        filtro: 'todos',
    },
    {
        titulo: 'Pacientes activos',
        valor: pacientesActivos.value,
        descripcion: 'Habilitados en el sistema',
        icono: 'activo',
        color: 'verde',
        filtro: 'activos',
    },
    {
        titulo: 'Pacientes inactivos',
        valor: pacientesInactivos.value,
        descripcion: 'Registros deshabilitados',
        icono: 'inactivo',
        color: 'gris',
        filtro: 'inactivos',
    },
    {
        titulo: 'Pacientes con alergias',
        valor: pacientesConAlergias.value,
        descripcion: 'Requieren revisión',
        icono: 'alerta',
        color: 'rojo',
        filtro: 'alergias',
    },
])

const iconos = {
    usuarios: 'M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75',
    activo: 'M22 11.08V12a10 10 0 1 1-5.93-9.14M22 4 12 14.01l-3-3',
    inactivo: 'M12 22a10 10 0 1 0 0-20 10 10 0 0 0 0 20M8 12h8',
    alerta: 'M10.3 3.9 2.5 17.4A2 2 0 0 0 4.2 20h15.6a2 2 0 0 0 1.7-2.6L13.7 3.9a2 2 0 0 0-3.4 0ZM12 9v4M12 17h.01',
    agregar: 'M12 5v14M5 12h14',
    buscar: 'M11 19a8 8 0 1 0 0-16 8 8 0 0 0 0 16M21 21l-4.35-4.35',
    editar: 'M12 20h9M16.5 3.5a2.12 2.12 0 0 1 3 3L9 17l-4 1 1-4Z',
    cerrar: 'M18 6 6 18M6 6l12 12',
    escudo: 'M12 22s8-4 8-11V5l-8-3-8 3v6c0 7 8 11 8 11ZM9 12l2 2 4-4',
    ficha: 'M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8ZM14 2v6h6M8 13h8M8 17h8',
}

// Filtrar pacientes.
const pacientesFiltrados = computed(() => {
    const termino = normalizarTexto(busqueda.value)
    const rutBuscado = normalizarRut(busqueda.value)

    return props.pacientes.filter(paciente => {

        const contenido = normalizarTexto([
            paciente.codigo_paciente,
            paciente.rut,
            paciente.nombres,
            paciente.apellidos,
        ].join(' '))

        const coincideBusqueda =
            !termino ||
            contenido.includes(termino) ||
            (
                /^[0-9]+[0-9K]?$/.test(rutBuscado) &&
                normalizarRut(paciente.rut).includes(rutBuscado)
            )

        const coincideEstado =
            filtroEstado.value === 'todos' ||
            (
                filtroEstado.value === 'activos' &&
                paciente.activo
            ) ||
            (
                filtroEstado.value === 'inactivos' &&
                !paciente.activo
            )

        const estadoAlergias = [
            'con_alergias',
            'sin_alergias',
        ].includes(paciente.estado_alergias)
            ? paciente.estado_alergias
            : 'desconocido'

        const coincideAlergias =
            filtroAlergias.value === 'todos' ||
            estadoAlergias === filtroAlergias.value

        return coincideBusqueda &&
            coincideEstado &&
            coincideAlergias
    })
})

// Aplicar filtros desde los indicadores.
function aplicarIndicador(filtro) {
    busqueda.value = ''
    filtroEstado.value = 'todos'
    filtroAlergias.value = 'todos'

    if (filtro === 'alergias') {
        filtroAlergias.value = 'con_alergias'
    } else {
        filtroEstado.value = filtro
    }
}

function indicadorSeleccionado(filtro) {
    if (filtro === 'alergias') {
        return filtroAlergias.value === 'con_alergias' &&
            filtroEstado.value === 'todos'
    }

    return filtroEstado.value === filtro &&
        filtroAlergias.value === 'todos'
}

function limpiarFiltros() {
    busqueda.value = ''
    filtroEstado.value = 'todos'
    filtroAlergias.value = 'todos'
}

// Abrir formulario.
async function nuevoPaciente() {
    if (form.processing || cambiandoEstado.value) return

    if (mostrarFormulario.value) {
        if (
            form.isDirty &&
            !window.confirm('¿Deseas descartar los cambios sin guardar?')
        ) {
            return
        }

        cancelarEdicion()
        return
    }

    pacienteEditando.value = null
    form.reset()
    form.clearErrors()

    mostrarFormulario.value = true

    await nextTick()

    formulario.value?.scrollIntoView({
        behavior: 'smooth',
        block: 'start',
    })
}

// Registrar o actualizar paciente.
function registrarPaciente() {
    if (form.processing || cambiandoEstado.value) return

    if (form.estado_alergias !== 'con_alergias') {
        form.alergias = ''
    }

    const opciones = {
        preserveScroll: true,

        onSuccess: () => {
            pacienteEditando.value = null
            form.reset()
            form.clearErrors()
            mostrarFormulario.value = false
        },
    }

    if (pacienteEditando.value !== null) {
        form.put(
            `/pacientes/${pacienteEditando.value}`,
            opciones
        )
    } else {
        form.post('/pacientes', opciones)
    }
}

// Editar paciente.
async function editarPaciente(paciente) {
    if (form.processing || cambiandoEstado.value) return

    if (
        mostrarFormulario.value &&
        form.isDirty &&
        !window.confirm('¿Deseas descartar los cambios sin guardar?')
    ) {
        return
    }

    pacienteEditando.value = paciente.id
    form.clearErrors()

    form.codigo_paciente = paciente.codigo_paciente
    form.rut = paciente.rut ?? ''
    form.nombres = paciente.nombres
    form.apellidos = paciente.apellidos

    form.fecha_nacimiento = paciente.fecha_nacimiento
        ? String(paciente.fecha_nacimiento).slice(0, 10)
        : ''

    form.estado_alergias =
        paciente.estado_alergias ?? 'desconocido'

    form.alergias = paciente.alergias ?? ''

    mostrarFormulario.value = true

    await nextTick()

    formulario.value?.scrollIntoView({
        behavior: 'smooth',
        block: 'start',
    })
}

// Cancelar formulario.
function cancelarEdicion() {
    if (form.processing) return

    pacienteEditando.value = null
    form.reset()
    form.clearErrors()
    mostrarFormulario.value = false
}

// Activar o desactivar paciente.
function cambiarEstadoPaciente(paciente) {
    if (form.processing || cambiandoEstado.value) return

    const accion = paciente.activo
        ? 'desactivar'
        : 'reactivar'

    const confirmacion = window.confirm(
        `¿Estás seguro de ${accion} a ${paciente.nombres} ${paciente.apellidos}?`
    )

    if (!confirmacion) return

    cambiandoEstado.value = true

    router.patch(
        `/pacientes/${paciente.id}/estado`,
        {
            activo: !paciente.activo,
        },
        {
            preserveScroll: true,

            onSuccess: () => {
                if (pacienteEditando.value === paciente.id) {
                    cancelarEdicion()
                }
            },

            onError: () => {
                window.alert(
                    'No se pudo actualizar el estado del paciente.'
                )
            },

            onFinish: () => {
                cambiandoEstado.value = false
            },
        }
    )
}

// Formatear fecha.
function formatearFecha(valor) {
    if (!valor) return 'No registrada'

    const fecha = String(valor).slice(0, 10)
    const partes = fecha.split('-')

    if (partes.length !== 3) return fecha

    return `${partes[2]}/${partes[1]}/${partes[0]}`
}

// Formatear RUT.
function formatearRut(valor) {
    if (!valor) return 'No registrado'

    const rut = normalizarRut(valor)

    if (!/^\d+[0-9K]$/.test(rut)) {
        return String(valor)
    }

    const numero = rut.slice(0, -1)
    const dv = rut.slice(-1)

    return numero.replace(
        /\B(?=(\d{3})+(?!\d))/g,
        '.'
    ) + '-' + dv
}

// Iniciales para identificar al paciente.
function inicialesPaciente(paciente) {
    const nombre = String(paciente.nombres ?? '').trim()
    const apellido = String(paciente.apellidos ?? '').trim()

    return (
        (nombre.charAt(0) || '') +
        (apellido.charAt(0) || '')
    ).toUpperCase() || 'P'
}

const campos = [
    {
        nombre: 'codigo_paciente',
        etiqueta: 'Código del paciente',
        placeholder: 'Ej.: PAC-001',
        maximo: 30,
        requerido: true,
    },
    {
        nombre: 'rut',
        etiqueta: 'RUT del paciente',
        placeholder: 'Ingrese RUT simulado',
        maximo: 12,
        requerido: false,
    },
    {
        nombre: 'nombres',
        etiqueta: 'Nombres',
        placeholder: 'Ej.: Camila',
        maximo: 100,
        requerido: true,
    },
    {
        nombre: 'apellidos',
        etiqueta: 'Apellidos',
        placeholder: 'Ej.: González Pérez',
        maximo: 100,
        requerido: true,
    },
]
</script>

<template>

    <Head title="Pacientes | FarmaControl" />

    <FarmaLayout
        titulo="Gestión de pacientes"
        subtitulo="Administra los registros y antecedentes de pacientes de Farmacia Asistencial."
    >

        <template #acciones>

            <button
                type="button"
                class="boton boton-principal"
                :disabled="form.processing || cambiandoEstado"
                @click="nuevoPaciente"
            >
                <svg viewBox="0 0 24 24" fill="none">
                    <path
                        :d="mostrarFormulario
                            ? iconos.cerrar
                            : iconos.agregar"
                    />
                </svg>

                {{
                    mostrarFormulario
                        ? 'Cerrar formulario'
                        : 'Nuevo paciente'
                }}
            </button>

        </template>

        <div class="modulo-pacientes">

            <!-- INDICADORES -->

            <section class="indicadores">

                <button
                    v-for="indicador in indicadores"
                    :key="indicador.filtro"
                    type="button"
                    class="tarjeta-indicador"
                    :class="[
                        indicador.color,
                        {
                            seleccionado:
                                indicadorSeleccionado(indicador.filtro)
                        }
                    ]"
                    :aria-pressed="indicadorSeleccionado(indicador.filtro)"
                    @click="aplicarIndicador(indicador.filtro)"
                >

                    <div class="indicador-superior">

                        <span>{{ indicador.titulo }}</span>

                        <div class="icono-indicador">
                            <svg viewBox="0 0 24 24" fill="none">
                                <path :d="iconos[indicador.icono]" />
                            </svg>
                        </div>

                    </div>

                    <strong class="valor-indicador">
                        {{ indicador.valor }}
                    </strong>

                    <span class="descripcion-indicador">
                        {{ indicador.descripcion }}
                    </span>

                </button>

            </section>

            <!-- INFORMACION ACADEMICA -->

            <section class="banner-academico">

                <div class="icono-banner">

                    <svg viewBox="0 0 24 24" fill="none">
                        <path :d="iconos.escudo" />
                    </svg>

                </div>

                <div class="texto-banner">

                    <h3>
                        Seguridad y antecedentes del paciente
                    </h3>

                    <p>
                        Registra únicamente datos ficticios y mantén
                        actualizados los antecedentes de alergias
                        antes de realizar una dispensación.
                    </p>

                </div>

                <Link
                    href="/recetas"
                    class="boton-banner"
                >
                    Consultar recetas →
                </Link>

            </section>

            <!-- MENSAJE DE EXITO -->

            <div
                v-if="mensajeExito"
                class="mensaje-exito"
                role="status"
            >

                <svg viewBox="0 0 24 24" fill="none">
                    <path :d="iconos.activo" />
                </svg>

                {{ mensajeExito }}

            </div>

            <!-- FORMULARIO -->

            <Transition name="desplegar">

                <section
                    v-if="mostrarFormulario"
                    ref="formulario"
                    class="tarjeta formulario-paciente"
                >

                    <div class="encabezado-tarjeta">

                        <div class="titulo-con-icono">

                            <div class="icono-seccion">
                                <svg viewBox="0 0 24 24" fill="none">
                                    <path
                                        :d="pacienteEditando !== null
                                            ? iconos.editar
                                            : iconos.usuarios"
                                    />
                                </svg>
                            </div>

                            <div>

                                <h2>
                                    {{
                                        pacienteEditando !== null
                                            ? 'Editar paciente'
                                            : 'Registrar nuevo paciente'
                                    }}
                                </h2>

                                <p>
                                    Completa los datos del paciente
                                    y sus antecedentes de alergias.
                                </p>

                            </div>

                        </div>

                        <button
                            type="button"
                            class="boton-cerrar"
                            aria-label="Cerrar formulario"
                            :disabled="form.processing"
                            @click="nuevoPaciente"
                        >
                            <svg viewBox="0 0 24 24" fill="none">
                                <path :d="iconos.cerrar" />
                            </svg>
                        </button>

                    </div>

                    <div class="contenido-formulario">

                        <div
                            v-if="pacienteEditando !== null"
                            class="aviso-edicion"
                        >
                            Estás editando al paciente

                            <strong>
                                {{ form.nombres }}
                                {{ form.apellidos }}
                            </strong>.
                        </div>

                        <form @submit.prevent="registrarPaciente">

                            <!-- DATOS PERSONALES -->

                            <div class="titulo-subseccion">

                                <span class="numero-seccion">1</span>

                                <div>
                                    <h3>Identificación del paciente</h3>
                                    <p>Información personal simulada.</p>
                                </div>

                            </div>

                            <div class="grilla-formulario">

                                <div
                                    v-for="campo in campos"
                                    :key="campo.nombre"
                                    class="campo"
                                >

                                    <label :for="campo.nombre">

                                        {{ campo.etiqueta }}

                                        <span
                                            v-if="campo.requerido"
                                            class="requerido"
                                        >*</span>

                                        <small v-else>
                                            (Opcional)
                                        </small>

                                    </label>

                                    <input
                                        :id="campo.nombre"
                                        v-model="form[campo.nombre]"
                                        type="text"
                                        :maxlength="campo.maximo"
                                        :placeholder="campo.placeholder"
                                        :required="campo.requerido"
                                        :disabled="form.processing"
                                        class="campo-input"
                                        :class="{
                                            'campo-error':
                                                form.errors[campo.nombre]
                                        }"
                                        :aria-invalid="!!form.errors[campo.nombre]"
                                    />

                                    <p
                                        v-if="form.errors[campo.nombre]"
                                        class="error"
                                    >
                                        {{ form.errors[campo.nombre] }}
                                    </p>

                                </div>

                                <div class="campo">

                                    <label for="fecha_nacimiento">
                                        Fecha de nacimiento
                                        <small>(Opcional)</small>
                                    </label>

                                    <input
                                        id="fecha_nacimiento"
                                        v-model="form.fecha_nacimiento"
                                        type="date"
                                        class="campo-input"
                                        :disabled="form.processing"
                                        :class="{
                                            'campo-error':
                                                form.errors.fecha_nacimiento
                                        }"
                                    />

                                    <p
                                        v-if="form.errors.fecha_nacimiento"
                                        class="error"
                                    >
                                        {{ form.errors.fecha_nacimiento }}
                                    </p>

                                </div>

                            </div>

                            <div class="separador"></div>

                            <!-- ANTECEDENTES DE ALERGIAS -->

                            <div class="titulo-subseccion">

                                <span class="numero-seccion">2</span>

                                <div>
                                    <h3>Antecedentes de alergias</h3>

                                    <p>
                                        Información relevante para
                                        la dispensación simulada.
                                    </p>
                                </div>

                            </div>

                            <div class="opciones-alergias">

                                <label
                                    class="opcion-alergia desconocido"
                                    :class="{
                                        'opcion-seleccionada':
                                            form.estado_alergias === 'desconocido'
                                    }"
                                >

                                    <input
                                        v-model="form.estado_alergias"
                                        type="radio"
                                        name="estado_alergias"
                                        value="desconocido"
                                        required
                                        :disabled="form.processing"
                                    />

                                    <div>
                                        <strong>Información desconocida</strong>
                                        <small>
                                            No existen antecedentes confirmados.
                                        </small>
                                    </div>

                                </label>

                                <label
                                    class="opcion-alergia sin-alergias"
                                    :class="{
                                        'opcion-seleccionada':
                                            form.estado_alergias === 'sin_alergias'
                                    }"
                                >

                                    <input
                                        v-model="form.estado_alergias"
                                        type="radio"
                                        name="estado_alergias"
                                        value="sin_alergias"
                                        :disabled="form.processing"
                                    />

                                    <div>
                                        <strong>Sin alergias conocidas</strong>
                                        <small>
                                            No se han informado alergias.
                                        </small>
                                    </div>

                                </label>

                                <label
                                    class="opcion-alergia con-alergias"
                                    :class="{
                                        'opcion-seleccionada':
                                            form.estado_alergias === 'con_alergias'
                                    }"
                                >

                                    <input
                                        v-model="form.estado_alergias"
                                        type="radio"
                                        name="estado_alergias"
                                        value="con_alergias"
                                        :disabled="form.processing"
                                    />

                                    <div>
                                        <strong>Con alergias conocidas</strong>
                                        <small>
                                            Requiere registrar antecedentes.
                                        </small>
                                    </div>

                                </label>

                            </div>

                            <p
                                v-if="form.errors.estado_alergias"
                                class="error"
                            >
                                {{ form.errors.estado_alergias }}
                            </p>

                            <!-- ALERTA DE ALERGIAS -->

                            <div
                                v-if="form.estado_alergias === 'con_alergias'"
                                class="panel-alergias"
                            >

                                <div class="titulo-alerta">

                                    <svg viewBox="0 0 24 24" fill="none">
                                        <path :d="iconos.alerta" />
                                    </svg>

                                    <strong>
                                        Registrar alergias conocidas
                                    </strong>

                                </div>

                                <label for="alergias">
                                    Especificar antecedentes de alergias *
                                </label>

                                <textarea
                                    id="alergias"
                                    v-model="form.alergias"
                                    rows="4"
                                    maxlength="2000"
                                    required
                                    :disabled="form.processing"
                                    placeholder="Ej.: Alergia a la penicilina."
                                    class="campo-input"
                                    :class="{
                                        'campo-error': form.errors.alergias
                                    }"
                                ></textarea>

                                <div class="contador-caracteres">
                                    {{ form.alergias.length }} / 2000
                                </div>

                                <p
                                    v-if="form.errors.alergias"
                                    class="error"
                                >
                                    {{ form.errors.alergias }}
                                </p>

                                <p class="texto-alerta">
                                    Estos antecedentes deberán revisarse
                                    antes de realizar una dispensación.
                                    El sistema no determina automáticamente
                                    la compatibilidad clínica de los medicamentos.
                                </p>

                            </div>

                            <div
                                v-else-if="form.estado_alergias === 'desconocido'"
                                class="nota-desconocido"
                            >
                                La ausencia de información no significa
                                que el paciente no tenga alergias.
                            </div>

                            <div
                                v-else
                                class="nota-sin-alergias"
                            >
                                Sin alergias conocidas según los
                                antecedentes registrados. Esto no garantiza
                                la ausencia de reacciones alérgicas.
                            </div>

                            <!-- ACCIONES DEL FORMULARIO -->

                            <div class="acciones-formulario">

                                <span class="campos-obligatorios">
                                    * Campos obligatorios
                                </span>

                                <div class="grupo-botones">

                                    <button
                                        type="button"
                                        class="boton boton-secundario"
                                        :disabled="form.processing || cambiandoEstado"
                                        @click="nuevoPaciente"
                                    >
                                        Cancelar
                                    </button>

                                    <button
                                        type="submit"
                                        class="boton boton-principal"
                                        :disabled="form.processing || cambiandoEstado"
                                    >

                                        {{
                                            form.processing
                                                ? 'Guardando...'
                                                : pacienteEditando !== null
                                                    ? 'Guardar cambios'
                                                    : 'Registrar paciente'
                                        }}

                                    </button>

                                </div>

                            </div>

                        </form>

                    </div>

                </section>

            </Transition>

            <!-- LISTADO DE PACIENTES -->

            <section class="tarjeta listado-pacientes">

                <div class="encabezado-tarjeta">

                    <div class="titulo-con-icono">

                        <div class="icono-seccion">
                            <svg viewBox="0 0 24 24" fill="none">
                                <path :d="iconos.ficha" />
                            </svg>
                        </div>

                        <div>

                            <h2>Pacientes registrados</h2>

                            <p>
                                Consulta y administra los registros
                                del entorno asistencial.
                            </p>

                        </div>

                    </div>

                    <span class="contador-registros">
                        {{ pacientes.length }} registros
                    </span>

                </div>

                <!-- BUSCADOR Y FILTROS -->

                <div class="barra-filtros">

                    <div class="buscador">

                        <svg viewBox="0 0 24 24" fill="none">
                            <path :d="iconos.buscar" />
                        </svg>

                        <input
                            v-model="busqueda"
                            type="search"
                            aria-label="Buscar pacientes"
                            placeholder="Buscar por código, RUT o nombre..."
                        />

                    </div>

                    <select
                        v-model="filtroEstado"
                        class="selector-filtro"
                        aria-label="Filtrar por estado"
                    >
                        <option value="todos">
                            Todos los estados
                        </option>

                        <option value="activos">
                            Activos
                        </option>

                        <option value="inactivos">
                            Inactivos
                        </option>
                    </select>

                    <select
                        v-model="filtroAlergias"
                        class="selector-filtro"
                        aria-label="Filtrar por alergias"
                    >
                        <option value="todos">
                            Todas las alergias
                        </option>

                        <option value="con_alergias">
                            Con alergias
                        </option>

                        <option value="sin_alergias">
                            Sin alergias conocidas
                        </option>

                        <option value="desconocido">
                            Información desconocida
                        </option>
                    </select>

                    <button
                        type="button"
                        class="boton-limpiar"
                        @click="limpiarFiltros"
                    >
                        Limpiar filtros
                    </button>

                </div>

                <!-- TABLA -->

                <div class="tabla-contenedor">

                    <table class="tabla-pacientes">

                        <thead>
                            <tr>
                                <th>Paciente</th>
                                <th>Código</th>
                                <th>RUT</th>
                                <th>Nacimiento</th>
                                <th>Antecedentes de alergias</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>

                        <tbody>

                            <tr
                                v-for="paciente in pacientesFiltrados"
                                :key="paciente.id"
                                :class="{
                                    'fila-inactiva': !paciente.activo
                                }"
                            >

                                <!-- IDENTIFICACION -->

                                <td>

                                    <div class="identidad-paciente">

                                        <div class="avatar-paciente">
                                            {{ inicialesPaciente(paciente) }}
                                        </div>

                                        <div>
                                            <strong class="nombre-paciente">
                                                {{ paciente.nombres }}
                                            </strong>

                                            <span class="apellido-paciente">
                                                {{ paciente.apellidos }}
                                            </span>
                                        </div>

                                    </div>

                                </td>

                                <!-- CODIGO -->

                                <td>
                                    <span class="codigo-paciente">
                                        {{ paciente.codigo_paciente }}
                                    </span>
                                </td>

                                <!-- RUT -->

                                <td class="sin-salto">
                                    {{ formatearRut(paciente.rut) }}
                                </td>

                                <!-- NACIMIENTO -->

                                <td class="sin-salto">
                                    {{
                                        formatearFecha(
                                            paciente.fecha_nacimiento
                                        )
                                    }}
                                </td>

                                <!-- ALERGIAS -->

                                <td class="celda-alergias">

                                    <div
                                        v-if="paciente.estado_alergias === 'con_alergias'"
                                    >

                                        <span class="etiqueta etiqueta-roja">

                                            <svg viewBox="0 0 24 24" fill="none">
                                                <path :d="iconos.alerta" />
                                            </svg>

                                            Con alergias

                                        </span>

                                        <p class="descripcion-alergias">
                                            {{
                                                paciente.alergias ||
                                                'Antecedentes sin especificar'
                                            }}
                                        </p>

                                    </div>

                                    <span
                                        v-else-if="paciente.estado_alergias === 'sin_alergias'"
                                        class="etiqueta etiqueta-verde"
                                    >
                                        Sin alergias conocidas
                                    </span>

                                    <span
                                        v-else
                                        class="etiqueta etiqueta-amarilla"
                                    >
                                        Información desconocida
                                    </span>

                                </td>

                                <!-- ESTADO -->

                                <td>

                                    <span
                                        class="etiqueta"
                                        :class="paciente.activo
                                            ? 'etiqueta-verde'
                                            : 'etiqueta-gris'"
                                    >

                                        <span class="punto-estado"></span>

                                        {{
                                            paciente.activo
                                                ? 'Activo'
                                                : 'Inactivo'
                                        }}

                                    </span>

                                </td>

                                <!-- ACCIONES -->

                                <td>

                                    <div class="acciones-paciente">

                                        <button
                                            type="button"
                                            class="boton-editar"
                                            :disabled="form.processing || cambiandoEstado"
                                            @click="editarPaciente(paciente)"
                                        >

                                            <svg viewBox="0 0 24 24" fill="none">
                                                <path :d="iconos.editar" />
                                            </svg>

                                            Editar

                                        </button>

                                        <button
                                            type="button"
                                            class="boton-estado"
                                            :class="paciente.activo
                                                ? 'desactivar'
                                                : 'reactivar'"
                                            :disabled="form.processing || cambiandoEstado"
                                            @click="cambiarEstadoPaciente(paciente)"
                                        >

                                            {{
                                                paciente.activo
                                                    ? 'Desactivar'
                                                    : 'Reactivar'
                                            }}

                                        </button>

                                    </div>

                                </td>

                            </tr>

                            <!-- SIN RESULTADOS -->

                            <tr v-if="!pacientesFiltrados.length">

                                <td colspan="7">

                                    <div class="estado-vacio">

                                        <div class="icono-vacio">
                                            <svg viewBox="0 0 24 24" fill="none">
                                                <path :d="iconos.usuarios" />
                                            </svg>
                                        </div>

                                        <h3>No se encontraron pacientes</h3>

                                        <p>
                                            No existen registros que coincidan
                                            con los filtros seleccionados.
                                        </p>

                                        <button
                                            type="button"
                                            class="boton boton-secundario"
                                            @click="limpiarFiltros"
                                        >
                                            Limpiar filtros
                                        </button>

                                    </div>

                                </td>

                            </tr>

                        </tbody>

                    </table>

                </div>

                <!-- PIE DE TABLA -->

                <div class="pie-tabla">

                    <span>
                        Mostrando
                        <strong>{{ pacientesFiltrados.length }}</strong>
                        de
                        <strong>{{ pacientes.length }}</strong>
                        pacientes
                    </span>

                    <span>
                        Registros exclusivamente académicos
                    </span>

                </div>

            </section>

        </div>

    </FarmaLayout>

</template>

<style scoped>

.modulo-pacientes {
    display: flex;
    flex-direction: column;
    gap: 24px;
    color: #334155;
}

svg {
    width: 20px;
    height: 20px;
    stroke: currentColor;
    stroke-width: 1.7;
    stroke-linecap: round;
    stroke-linejoin: round;
    flex-shrink: 0;
}

/* BOTONES */

.boton {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 9px;
    padding: 12px 18px;
    border-radius: 10px;
    border: 1px solid transparent;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: all .2s ease;
}

.boton-principal {
    background: var(--fc-accent);
    color: white;
    box-shadow: 0 4px 12px rgba(67, 54, 92, .13);
}

.boton-principal:hover {
    background: var(--fc-hover);
    transform: translateY(-1px);
}

.boton-secundario {
    background: white;
    color: var(--fc-primary);
    border-color: var(--fc-border);
}

.boton-secundario:hover {
    background: var(--fc-light);
}

button:disabled {
    opacity: .5;
    cursor: not-allowed;
    transform: none;
}

/* INDICADORES */

.indicadores {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 18px;
}

.tarjeta-indicador {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    text-align: left;
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 15px;
    padding: 20px;
    box-shadow: 0 3px 12px rgba(15, 23, 42, .03);
    cursor: pointer;
    transition: all .2s ease;
}

.tarjeta-indicador:hover {
    transform: translateY(-3px);
    border-color: var(--fc-accent);
    box-shadow: 0 8px 22px rgba(67, 54, 92, .08);
}

.tarjeta-indicador.seleccionado {
    border-color: var(--fc-accent);
    box-shadow: 0 0 0 1px var(--fc-accent);
}

.indicador-superior {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    width: 100%;
}

.indicador-superior > span {
    font-size: 12px;
    font-weight: 600;
    color: #64748b;
}

.icono-indicador {
    width: 42px;
    height: 42px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--fc-light);
    color: var(--fc-primary);
}

.valor-indicador {
    margin-top: 13px;
    font-size: 32px;
    font-weight: 800;
    line-height: 1;
    color: var(--fc-primary);
}

.descripcion-indicador {
    margin-top: 11px;
    font-size: 11px;
    color: #94a3b8;
}

.verde .icono-indicador {
    background: #ecfdf5;
    color: #15803d;
}

.verde .valor-indicador {
    color: #15803d;
}

.gris .icono-indicador {
    background: #f1f5f9;
    color: #64748b;
}

.gris .valor-indicador {
    color: #64748b;
}

.rojo .icono-indicador {
    background: #fef2f2;
    color: #dc2626;
}

.rojo .valor-indicador {
    color: #dc2626;
}

/* BANNER */

.banner-academico {
    display: flex;
    align-items: center;
    gap: 18px;
    padding: 24px;
    border-radius: 15px;
    background: linear-gradient(
        115deg,
        var(--fc-primary),
        var(--fc-accent)
    );
    color: white;
}

.icono-banner {
    width: 54px;
    height: 54px;
    border-radius: 15px;
    background: rgba(255, 255, 255, .14);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.icono-banner svg {
    width: 28px;
    height: 28px;
}

.texto-banner {
    flex: 1;
}

.texto-banner h3 {
    font-size: 15px;
    font-weight: 700;
    margin-bottom: 6px;
}

.texto-banner p {
    font-size: 12px;
    line-height: 1.7;
    color: rgba(255, 255, 255, .85);
    max-width: 650px;
}

.boton-banner {
    padding: 11px 16px;
    border: 1px solid rgba(255, 255, 255, .35);
    border-radius: 9px;
    background: rgba(255, 255, 255, .12);
    color: white;
    font-size: 12px;
    font-weight: 600;
    white-space: nowrap;
    transition: background .2s;
}

.boton-banner:hover {
    background: rgba(255, 255, 255, .22);
}

/* MENSAJE DE EXITO */

.mensaje-exito {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 16px 20px;
    border: 1px solid #bbf7d0;
    border-radius: 12px;
    background: #f0fdf4;
    color: #166534;
    font-size: 13px;
    font-weight: 600;
}

/* TARJETAS */

.tarjeta {
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    box-shadow: 0 4px 18px rgba(15, 23, 42, .03);
    overflow: hidden;
}

.encabezado-tarjeta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 16px;
    padding: 24px;
    border-bottom: 1px solid #edf0f4;
}

.titulo-con-icono {
    display: flex;
    align-items: center;
    gap: 13px;
}

.icono-seccion {
    width: 43px;
    height: 43px;
    border-radius: 12px;
    background: var(--fc-light);
    color: var(--fc-primary);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.encabezado-tarjeta h2 {
    color: var(--fc-primary);
    font-size: 17px;
    font-weight: 750;
}

.encabezado-tarjeta p {
    color: #64748b;
    font-size: 12px;
    margin-top: 5px;
}

.boton-cerrar {
    padding: 8px;
    border-radius: 8px;
    color: #64748b;
    cursor: pointer;
}

.boton-cerrar:hover {
    background: #f1f5f9;
}

/* FORMULARIO */

.formulario-paciente {
    scroll-margin-top: 100px;
}

.contenido-formulario {
    padding: 26px;
}

.aviso-edicion {
    background: var(--fc-light);
    color: var(--fc-primary);
    border: 1px solid var(--fc-border);
    border-radius: 10px;
    padding: 14px;
    font-size: 13px;
    margin-bottom: 24px;
}

.titulo-subseccion {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 22px;
}

.numero-seccion {
    width: 32px;
    height: 32px;
    border-radius: 9px;
    background: var(--fc-light);
    color: var(--fc-primary);
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 13px;
    flex-shrink: 0;
}

.titulo-subseccion h3 {
    font-size: 14px;
    font-weight: 700;
    color: var(--fc-primary);
}

.titulo-subseccion p {
    font-size: 11px;
    color: #64748b;
    margin-top: 4px;
}

.grilla-formulario {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 22px;
}

.campo {
    min-width: 0;
}

.campo label,
.panel-alergias label {
    display: block;
    margin-bottom: 9px;
    font-size: 12px;
    font-weight: 600;
    color: #475569;
}

.campo label small {
    font-size: 10px;
    font-weight: 400;
    color: #94a3b8;
    margin-left: 4px;
}

.requerido {
    color: #dc2626;
}

.campo-input {
    width: 100%;
    min-height: 44px;
    padding: 12px 14px;
    border: 1px solid #dce1e8;
    border-radius: 9px;
    background: white;
    font-size: 13px;
    color: #334155;
    outline: none;
    transition: all .2s;
}

.campo-input:focus {
    border-color: var(--fc-accent);
    box-shadow: 0 0 0 3px var(--fc-light);
}

.campo-input::placeholder {
    color: #94a3b8;
}

.campo-input.campo-error {
    border-color: #ef4444;
}

.error {
    color: #dc2626;
    font-size: 11px;
    margin-top: 7px;
}

.separador {
    border-top: 1px solid #edf0f4;
    margin: 30px 0;
}

/* OPCIONES DE ALERGIAS */

.opciones-alergias {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 14px;
}

.opcion-alergia {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    padding: 17px;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    cursor: pointer;
    transition: all .2s;
}

.opcion-alergia input {
    margin-top: 3px;
    accent-color: var(--fc-accent);
    flex-shrink: 0;
}

.opcion-alergia strong {
    display: block;
    font-size: 12px;
    font-weight: 700;
    color: #475569;
}

.opcion-alergia small {
    display: block;
    font-size: 11px;
    color: #64748b;
    line-height: 1.6;
    margin-top: 6px;
}

.opcion-alergia:hover {
    border-color: var(--fc-accent);
}

.desconocido.opcion-seleccionada {
    border-color: #f59e0b;
    background: #fffbeb;
}

.sin-alergias.opcion-seleccionada {
    border-color: #22c55e;
    background: #f0fdf4;
}

.con-alergias.opcion-seleccionada {
    border-color: #ef4444;
    background: #fef2f2;
}

/* PANEL DE ALERTA */

.panel-alergias {
    margin-top: 20px;
    padding: 20px;
    border: 1px solid #fecaca;
    border-left: 4px solid #dc2626;
    border-radius: 10px;
    background: #fef2f2;
}

.titulo-alerta {
    display: flex;
    align-items: center;
    gap: 9px;
    color: #b91c1c;
    font-size: 13px;
    margin-bottom: 16px;
}

.panel-alergias label {
    color: #991b1b;
}

.panel-alergias textarea {
    resize: vertical;
    min-height: 100px;
    border-color: #fecaca;
}

.contador-caracteres {
    font-size: 10px;
    color: #94a3b8;
    text-align: right;
    margin-top: 6px;
}

.texto-alerta {
    color: #b91c1c;
    font-size: 11px;
    line-height: 1.7;
    margin-top: 12px;
}

.nota-desconocido,
.nota-sin-alergias {
    padding: 13px 16px;
    margin-top: 16px;
    border-radius: 9px;
    font-size: 12px;
    line-height: 1.6;
}

.nota-desconocido {
    background: #fffbeb;
    color: #92400e;
    border: 1px solid #fde68a;
}

.nota-sin-alergias {
    background: #f0fdf4;
    color: #166534;
    border: 1px solid #bbf7d0;
}

.acciones-formulario {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 15px;
    margin-top: 28px;
    padding-top: 22px;
    border-top: 1px solid #edf0f4;
}

.campos-obligatorios {
    font-size: 11px;
    color: #64748b;
}

.grupo-botones {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}

/* LISTADO */

.contador-registros {
    background: var(--fc-light);
    color: var(--fc-primary);
    border-radius: 20px;
    padding: 7px 12px;
    font-size: 11px;
    font-weight: 600;
    white-space: nowrap;
}

/* FILTROS */

.barra-filtros {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 12px;
    padding: 20px 24px;
}

.buscador {
    position: relative;
    flex: 1;
    min-width: 230px;
}

.buscador svg {
    position: absolute;
    left: 13px;
    top: 50%;
    transform: translateY(-50%);
    color: #94a3b8;
    width: 17px;
    height: 17px;
    pointer-events: none;
}

.buscador input {
    width: 100%;
    height: 43px;
    border: 1px solid #dce1e8;
    border-radius: 9px;
    padding: 10px 12px 10px 40px;
    font-size: 12px;
    outline: none;
}

.buscador input:focus,
.selector-filtro:focus {
    border-color: var(--fc-accent);
    box-shadow: 0 0 0 3px var(--fc-light);
}

.selector-filtro {
    height: 43px;
    border: 1px solid #dce1e8;
    border-radius: 9px;
    padding: 10px 13px;
    background: white;
    color: #475569;
    font-size: 12px;
    outline: none;
}

.boton-limpiar {
    font-size: 12px;
    font-weight: 600;
    color: var(--fc-accent);
    padding: 10px;
    cursor: pointer;
}

.boton-limpiar:hover {
    text-decoration: underline;
}

/* TABLA */

.tabla-contenedor {
    overflow-x: auto;
}

.tabla-pacientes {
    width: 100%;
    min-width: 1080px;
    border-collapse: collapse;
    text-align: left;
}

.tabla-pacientes thead {
    background: var(--fc-light);
}

.tabla-pacientes th {
    padding: 15px 18px;
    color: var(--fc-primary);
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .4px;
    white-space: nowrap;
}

.tabla-pacientes td {
    padding: 17px 18px;
    border-bottom: 1px solid #edf0f4;
    vertical-align: middle;
    font-size: 12px;
    color: #475569;
}

.tabla-pacientes tbody tr {
    transition: background .2s;
}

.tabla-pacientes tbody tr:hover {
    background: var(--fc-background);
}

.fila-inactiva {
    background: #f8fafc;
}

.identidad-paciente {
    display: flex;
    align-items: center;
    gap: 11px;
    min-width: 155px;
}

.avatar-paciente {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 39px;
    height: 39px;
    border-radius: 12px;
    background: var(--fc-light);
    color: var(--fc-primary);
    font-size: 12px;
    font-weight: 700;
    flex-shrink: 0;
}

.nombre-paciente {
    display: block;
    font-weight: 700;
    color: #334155;
}

.apellido-paciente {
    display: block;
    color: #64748b;
    font-size: 11px;
    margin-top: 4px;
}

.codigo-paciente {
    font-weight: 700;
    color: var(--fc-primary);
    white-space: nowrap;
}

.sin-salto {
    white-space: nowrap;
}

/* ETIQUETAS */

.etiqueta {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 10px;
    border-radius: 20px;
    font-size: 10px;
    font-weight: 600;
    line-height: 1.4;
}

.etiqueta svg {
    width: 13px;
    height: 13px;
}

.etiqueta-verde {
    color: #15803d;
    background: #dcfce7;
}

.etiqueta-roja {
    color: #b91c1c;
    background: #fee2e2;
}

.etiqueta-amarilla {
    color: #92400e;
    background: #fef3c7;
}

.etiqueta-gris {
    color: #64748b;
    background: #e2e8f0;
}

.punto-estado {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: currentColor;
}

.celda-alergias {
    min-width: 175px;
    max-width: 250px;
}

.descripcion-alergias {
    margin-top: 8px;
    font-size: 11px;
    line-height: 1.6;
    color: #b91c1c;
    overflow-wrap: anywhere;
    white-space: pre-wrap;
}

/* ACCIONES DE PACIENTES */

.acciones-paciente {
    display: flex;
    flex-direction: column;
    gap: 7px;
    min-width: 100px;
}

.boton-editar,
.boton-estado {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    padding: 8px 12px;
    border: 1px solid transparent;
    border-radius: 7px;
    font-size: 11px;
    font-weight: 600;
    cursor: pointer;
    transition: all .2s;
}

.boton-editar {
    background: var(--fc-light);
    color: var(--fc-primary);
    border-color: var(--fc-border);
}

.boton-editar svg {
    width: 13px;
    height: 13px;
}

.boton-editar:hover {
    background: var(--fc-primary);
    color: white;
}

.desactivar {
    color: #b91c1c;
    background: #fef2f2;
    border-color: #fecaca;
}

.desactivar:hover {
    background: #fee2e2;
}

.reactivar {
    color: #15803d;
    background: #f0fdf4;
    border-color: #bbf7d0;
}

.reactivar:hover {
    background: #dcfce7;
}

/* ESTADO VACIO */

.estado-vacio {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 12px;
    padding: 50px 20px;
    text-align: center;
}

.icono-vacio {
    width: 65px;
    height: 65px;
    border-radius: 20px;
    background: var(--fc-light);
    color: var(--fc-accent);
    display: flex;
    align-items: center;
    justify-content: center;
}

.icono-vacio svg {
    width: 30px;
    height: 30px;
}

.estado-vacio h3 {
    font-size: 15px;
    font-weight: 700;
    color: var(--fc-primary);
}

.estado-vacio p {
    font-size: 12px;
    color: #64748b;
    margin-bottom: 5px;
}

/* PIE DE TABLA */

.pie-tabla {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
    padding: 18px 24px;
    font-size: 11px;
    color: #64748b;
}

.pie-tabla strong {
    color: var(--fc-primary);
}

/* ANIMACIONES */

.desplegar-enter-active,
.desplegar-leave-active {
    transition: opacity .25s ease, transform .25s ease;
}

.desplegar-enter-from,
.desplegar-leave-to {
    opacity: 0;
    transform: translateY(-8px);
}

/* RESPONSIVE */

@media (max-width: 1250px) {

    .indicadores {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .opciones-alergias {
        grid-template-columns: 1fr;
    }

}

@media (max-width: 768px) {

    .modulo-pacientes {
        gap: 18px;
    }

    .indicadores {
        gap: 12px;
    }

    .tarjeta-indicador {
        padding: 15px;
    }

    .indicador-superior > span {
        font-size: 11px;
    }

    .icono-indicador {
        width: 34px;
        height: 34px;
    }

    .valor-indicador {
        font-size: 27px;
    }

    .banner-academico {
        flex-wrap: wrap;
        padding: 18px;
    }

    .texto-banner {
        min-width: 180px;
    }

    .boton-banner {
        margin-left: auto;
    }

    .encabezado-tarjeta,
    .contenido-formulario {
        padding: 18px;
    }

    .grilla-formulario {
        grid-template-columns: 1fr;
        gap: 18px;
    }

    .barra-filtros {
        padding: 16px;
    }

    .buscador {
        flex-basis: 100%;
    }

    .selector-filtro {
        flex: 1;
        min-width: 0;
    }

    .acciones-formulario {
        flex-direction: column;
        align-items: flex-start;
    }

    .grupo-botones {
        width: 100%;
        justify-content: flex-end;
    }

    .pie-tabla {
        flex-direction: column;
        align-items: flex-start;
        padding: 16px;
    }

}

@media (max-width: 420px) {

    .indicadores {
        grid-template-columns: 1fr;
    }

    .contador-registros {
        display: none;
    }

    .selector-filtro {
        flex-basis: 100%;
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