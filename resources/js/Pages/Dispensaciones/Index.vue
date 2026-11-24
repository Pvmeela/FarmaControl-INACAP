<script setup>
import { computed, ref, watch, nextTick } from 'vue'
import { Head, Link, useForm, usePage } from '@inertiajs/vue3'
import FarmaLayout from '../../Layouts/FarmaLayout.vue'

const props = defineProps({
    pacientes: { type: Array, default: () => [] },
    recetas: { type: Array, default: () => [] },
    medicamentos: { type: Array, default: () => [] },
    dispensaciones: { type: Array, default: () => [] },
})

const page = usePage()

const busqueda = ref('')
const buscarPaciente = ref('')
const filtroModalidad = ref('todos')
const filtroAlergias = ref('todos')
const mostrarFormulario = ref(false)
const formularioRef = ref(null)
const modalidad = ref('general')

const iconos = {
    volver: 'M19 12H5 M12 19l-7-7 7-7',
    agregar: 'M12 5v14 M5 12h14',
    medicamento: 'M10.5 3.5a5.7 5.7 0 0 1 8 8l-7 7a5.7 5.7 0 0 1-8-8z M7 7l10 10',
    documento: 'M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z M14 2v6h6 M8 13h8 M8 17h8',
    corazon: 'M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1.1-1.1a5.5 5.5 0 0 0-7.8 7.8L12 21l8.8-8.6a5.5 5.5 0 0 0 0-7.8z',
    alerta: 'M12 9v4 M12 17h.01 M10.3 3.8L2.5 18a2 2 0 0 0 1.8 3h15.4a2 2 0 0 0 1.8-3L13.7 3.8a2 2 0 0 0-3.4 0',
    buscar: 'M21 21l-5-5 M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0',
    descargar: 'M12 3v12 M7 10l5 5 5-5 M4 17v4h16v-4',
    ver: 'M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7z M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0',
    check: 'M5 12l4 4L19 6',
}

const rolUsuario = computed(() => {
    const rol = page.props.auth?.user?.rol

    return typeof rol === 'string'
        ? rol
        : rol?.nombre || page.props.auth?.user?.rol_nombre || ''
})

const puedeDispensar = computed(() =>
    [
        'Administrador',
        'Quimico Farmaceutico',
        'Tecnico en Farmacia',
    ].includes(rolUsuario.value)
)

const form = useForm({
    paciente_id: '',
    receta_id: '',
    medicamento_id: '',
    programa_id: '',
    cantidad: 1,
    observaciones: '',
    alergias_revisadas: false,
    tipo_retiro: 'paciente',
    retirante_nombre: '',
    retirante_rut: '',
    retirante_parentesco: '',
    autorizacion_verificada: false,
})

const mensajeExito = computed(() =>
    page.props.flash?.success || ''
)

function activo(valor) {
    return valor === true || valor === 1 || valor === '1'
}

function mismoId(a, b) {
    return Number(a) === Number(b)
}

function normalizar(valor) {
    return String(valor ?? '')
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .toLowerCase()
        .trim()
}

function normalizarRut(valor) {
    return String(valor || '')
        .replace(/[^0-9kK]/g, '')
        .toUpperCase()
}

function formatearRut(valor) {
    if (!valor) return 'No registrado'

    const rut = normalizarRut(valor)

    if (rut.length < 2) return valor

    return rut.slice(0, -1)
        .replace(/\B(?=(\d{3})+(?!\d))/g, '.')
        + '-' + rut.slice(-1)
}

function fechaISO(fecha) {
    return [
        fecha.getFullYear(),
        String(fecha.getMonth() + 1).padStart(2, '0'),
        String(fecha.getDate()).padStart(2, '0'),
    ].join('-')
}

function fechaLocal(valor) {
    return valor ? String(valor).slice(0, 10) : ''
}

const hoy = computed(() => fechaISO(new Date()))

const fechaActual = new Date().toLocaleDateString(
    'es-CL',
    {
        weekday: 'long',
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    }
)

function formatearFecha(valor) {
    if (!valor) return 'Sin registro'

    const partes = fechaLocal(valor).split('-')

    if (partes.length !== 3) return valor

    return `${partes[2]}/${partes[1]}/${partes[0]}`
}

function nombrePaciente(paciente) {
    if (!paciente) return 'Sin paciente registrado'

    return `${paciente.nombres || ''} ${paciente.apellidos || ''}`.trim()
}

function nombreMedicamento(medicamento) {
    if (!medicamento) return 'Medicamento no disponible'

    return `${medicamento.nombre} ${medicamento.concentracion || ''}`.trim()
}

function obtenerMedicamento(id) {
    return props.medicamentos.find(
        medicamento => mismoId(medicamento.id, id)
    )
}

function textoAlergias(estado) {
    return {
        con_alergias: 'Con alergias',
        sin_alergias: 'Sin alergias conocidas',
        desconocido: 'Antecedentes desconocidos',
    }[estado] || 'Sin registro histórico'
}

function claseAlergias(estado) {
    return {
        con_alergias: 'rojo',
        sin_alergias: 'verde',
        desconocido: 'ambar',
    }[estado] || 'gris'
}

function textoPrograma(tipo) {
    if (tipo === 'GES') return 'GES/AUGE'
    if (tipo === 'Cronico') return 'Crónico'

    return tipo || 'Programa de salud'
}

// PACIENTES

const pacientesFiltrados = computed(() => {
    const texto = normalizar(buscarPaciente.value)
    const rut = normalizarRut(buscarPaciente.value)

    return props.pacientes.filter(paciente =>
        activo(paciente.activo) &&
        (
            !texto ||
            normalizar(nombrePaciente(paciente)).includes(texto) ||
            (
                rut &&
                normalizarRut(paciente.rut).includes(rut)
            )
        )
    )
})

const pacienteSeleccionado = computed(() =>
    props.pacientes.find(paciente =>
        mismoId(paciente.id, form.paciente_id)
    )
)

const estadoAlergias = computed(() =>
    pacienteSeleccionado.value?.estado_alergias || 'desconocido'
)

// PROGRAMAS DE SALUD

const programasPaciente = computed(() =>
    (pacienteSeleccionado.value?.programas || []).filter(
        programa =>
            activo(programa.activo) &&
            programa.pivot?.estado === 'Activo' &&
            Boolean(programa.pivot?.fecha_ingreso) &&
            fechaLocal(programa.pivot.fecha_ingreso) <= hoy.value
    )
)

const programaSeleccionado = computed(() =>
    programasPaciente.value.find(programa =>
        mismoId(programa.id, form.programa_id)
    )
)

const medicamentosCanasta = computed(() =>
    (programaSeleccionado.value?.medicamentos || []).filter(
        medicamento =>
            activo(medicamento.activo) &&
            activo(medicamento.pivot?.activo)
    )
)

const medicamentoCanasta = computed(() =>
    medicamentosCanasta.value.find(medicamento =>
        mismoId(medicamento.id, form.medicamento_id)
    )
)

const cuotaMaxima = computed(() =>
    Number(medicamentoCanasta.value?.pivot?.cantidad_maxima || 0)
)

const periodoDias = computed(() =>
    Number(medicamentoCanasta.value?.pivot?.periodo_dias || 0)
)

const cobertura = computed(() =>
    Number(medicamentoCanasta.value?.pivot?.cobertura || 0)
)

const inicioPeriodo = computed(() => {
    if (periodoDias.value < 1) return ''

    const fecha = new Date()

    fecha.setDate(
        fecha.getDate() - (periodoDias.value - 1)
    )

    return fechaISO(fecha)
})

const unidadesEntregadasPrograma = computed(() => {
    if (
        !programaSeleccionado.value ||
        !medicamentoCanasta.value ||
        !inicioPeriodo.value
    ) {
        return 0
    }

    return props.dispensaciones
        .filter(dispensacion =>
            mismoId(dispensacion.paciente_id, form.paciente_id) &&
            mismoId(dispensacion.programa_id, form.programa_id) &&
            fechaLocal(dispensacion.fecha_dispensacion) >= inicioPeriodo.value &&
            fechaLocal(dispensacion.fecha_dispensacion) <= hoy.value
        )
        .flatMap(dispensacion =>
            dispensacion.detalles || []
        )
        .filter(detalle =>
            mismoId(detalle.medicamento_id, form.medicamento_id)
        )
        .reduce(
            (total, detalle) =>
                total + Number(detalle.cantidad || 0),
            0
        )
})

const cuotaDisponible = computed(() =>
    Math.max(
        0,
        cuotaMaxima.value - unidadesEntregadasPrograma.value
    )
)

const porcentajeUtilizado = computed(() => {
    if (!cuotaMaxima.value) return 0

    return Math.min(
        100,
        unidadesEntregadasPrograma.value /
            cuotaMaxima.value * 100
    )
})

// RECETAS

function recetaEstaVigente(receta) {
    if (!receta || receta.estado !== 'vigente') {
        return false
    }

    const emision = fechaLocal(receta.fecha_emision)
    const vencimiento = fechaLocal(receta.fecha_vencimiento)

    return Boolean(
        emision &&
        vencimiento &&
        emision <= hoy.value &&
        vencimiento >= hoy.value
    )
}

const recetasDisponibles = computed(() =>
    props.recetas.filter(receta =>
        mismoId(receta.paciente_id, form.paciente_id) &&
        recetaEstaVigente(receta)
    )
)

const recetaSeleccionada = computed(() =>
    recetasDisponibles.value.find(receta =>
        mismoId(receta.id, form.receta_id)
    )
)

function cantidadEntregadaReceta(recetaId, medicamentoId) {
    return props.dispensaciones
        .filter(dispensacion =>
            mismoId(dispensacion.receta_id, recetaId)
        )
        .flatMap(dispensacion =>
            dispensacion.detalles || []
        )
        .filter(detalle =>
            mismoId(detalle.medicamento_id, medicamentoId)
        )
        .reduce(
            (total, detalle) =>
                total + Number(detalle.cantidad || 0),
            0
        )
}

function cantidadPendiente(medicamentoId) {
    if (!recetaSeleccionada.value) return 0

    const cantidadPrescrita = (
        recetaSeleccionada.value.detalles || []
    )
        .filter(detalle =>
            mismoId(detalle.medicamento_id, medicamentoId)
        )
        .reduce(
            (total, detalle) =>
                total + Number(detalle.cantidad_prescrita || 0),
            0
        )

    return Math.max(
        0,
        cantidadPrescrita - cantidadEntregadaReceta(
            recetaSeleccionada.value.id,
            medicamentoId
        )
    )
}

const medicamentosDisponibles = computed(() => {
    const ids = new Set(
        (recetaSeleccionada.value?.detalles || [])
            .map(detalle => Number(detalle.medicamento_id))
    )

    return props.medicamentos.filter(medicamento => {
        if (
            !activo(medicamento.activo) ||
            !ids.has(Number(medicamento.id)) ||
            cantidadPendiente(medicamento.id) <= 0
        ) {
            return false
        }

        if (modalidad.value === 'programa') {
            return medicamentosCanasta.value.some(
                item => mismoId(item.id, medicamento.id)
            )
        }

        return true
    })
})

const medicamentoSeleccionado = computed(() =>
    medicamentosDisponibles.value.find(medicamento =>
        mismoId(medicamento.id, form.medicamento_id)
    )
)

const pendienteReceta = computed(() =>
    medicamentoSeleccionado.value
        ? cantidadPendiente(form.medicamento_id)
        : 0
)

const maximoEntregable = computed(() =>
    modalidad.value === 'programa'
        ? Math.min(pendienteReceta.value, cuotaDisponible.value)
        : pendienteReceta.value
)

// VALIDACIONES
function rutValido(valor) {
    const entrada = String(valor || '').trim()

    if (!/^[0-9kK.\-\s]+$/.test(entrada)) return false

    const rut = normalizarRut(entrada)

    if (!/^[0-9]{1,8}[0-9K]$/.test(rut)) return false

    const cuerpo = rut.slice(0, -1)
    const digito = rut.slice(-1)

    if (Number(cuerpo) <= 0) return false

    let suma = 0
    let multiplicador = 2

    for (let i = cuerpo.length - 1; i >= 0; i--) {
        suma += Number(cuerpo[i]) * multiplicador
        multiplicador = multiplicador === 7
            ? 2
            : multiplicador + 1
    }

    const resultado = 11 - (suma % 11)

    const esperado = resultado === 11
        ? '0'
        : resultado === 10
            ? 'K'
            : String(resultado)

    return digito === esperado
}

function rutCanonico(valor) {
    return normalizarRut(valor).replace(/^0+/, '')
}

const retiranteValido = computed(() => {
    if (!pacienteSeleccionado.value) return false

    if (form.tipo_retiro === 'paciente') {
        return rutValido(pacienteSeleccionado.value.rut)
    }

    return Boolean(
        form.tipo_retiro === 'tercero_autorizado' &&
        form.retirante_nombre.trim().length >= 3 &&
        rutValido(form.retirante_rut) &&
        rutCanonico(form.retirante_rut) !==
            rutCanonico(pacienteSeleccionado.value.rut) &&
        form.retirante_parentesco.trim() &&
        form.autorizacion_verificada === true
    )
})

function limpiarRetirante() {
    form.retirante_nombre = ''
    form.retirante_rut = ''
    form.retirante_parentesco = ''
    form.autorizacion_verificada = false
}

watch(() => form.tipo_retiro, limpiarRetirante)

watch(() => form.paciente_id, () => {
    form.tipo_retiro = 'paciente'
    limpiarRetirante()
})

watch(
    () => [
        form.retirante_nombre,
        form.retirante_rut,
        form.retirante_parentesco,
    ],
    () => {
        form.autorizacion_verificada = false
    }
)

watch(() => form.paciente_id, () => {
    form.receta_id = ''
    form.programa_id = ''
    form.medicamento_id = ''
    form.cantidad = 1
    form.alergias_revisadas = false
    modalidad.value = 'general'
    form.clearErrors()
})

watch(modalidad, () => {
    form.programa_id = ''
    form.medicamento_id = ''
    form.cantidad = 1
    form.alergias_revisadas = false
    form.clearErrors()
})

watch(() => form.programa_id, () => {
    form.medicamento_id = ''
    form.cantidad = 1
    form.alergias_revisadas = false
    form.clearErrors()
})

watch(() => form.receta_id, () => {
    form.medicamento_id = ''
    form.cantidad = 1
    form.alergias_revisadas = false
    form.clearErrors()
})

watch(() => form.medicamento_id, () => {
    form.cantidad = 1
    form.alergias_revisadas = false
    form.clearErrors('cantidad', 'alergias_revisadas')
})

watch(
    () => [
        pacienteSeleccionado.value?.estado_alergias,
        pacienteSeleccionado.value?.alergias,
    ],
    () => {
        form.alergias_revisadas = false
    }
)

const programaValido = computed(() =>
    modalidad.value === 'general' ||
    Boolean(
        programaSeleccionado.value &&
        medicamentoCanasta.value &&
        cuotaMaxima.value > 0 &&
        periodoDias.value > 0 &&
        cobertura.value > 0 &&
        cobertura.value <= 100
    )
)

const puedeRegistrar = computed(() =>
    Boolean(
        puedeDispensar.value &&
        pacienteSeleccionado.value &&
        activo(pacienteSeleccionado.value.activo) &&
        recetaSeleccionada.value &&
        medicamentoSeleccionado.value &&
        programaValido.value &&
        retiranteValido.value &&
        form.alergias_revisadas === true &&
        Number.isInteger(Number(form.cantidad)) &&
        Number(form.cantidad) >= 1 &&
        Number(form.cantidad) <= maximoEntregable.value &&
        !form.processing
    )
)

// ACCIONES

async function abrirFormulario() {
    if (!puedeDispensar.value || form.processing) return

    mostrarFormulario.value = true

    await nextTick()

    formularioRef.value?.scrollIntoView({
        behavior: 'smooth',
        block: 'start',
    })
}

function limpiarFormulario() {
    form.reset()
    form.clearErrors()

    modalidad.value = 'general'
    buscarPaciente.value = ''
    mostrarFormulario.value = false
}

function cancelarFormulario() {
    if (form.processing) return

    if (
        form.isDirty &&
        !window.confirm('¿Deseas descartar los cambios?')
    ) {
        return
    }

    limpiarFormulario()
}

function registrarDispensacion() {
    if (!puedeRegistrar.value) return

    form.transform(datos => ({
        ...datos,
        programa_id: modalidad.value === 'programa'
            ? datos.programa_id
            : null,
    })).post('/dispensaciones', {
        preserveScroll: true,
        onSuccess: limpiarFormulario,
    })
}

// INDICADORES

const indicadores = computed(() => [
    {
        titulo: 'Dispensaciones',
        valor: props.dispensaciones.length,
        descripcion: 'Entregas registradas',
        icono: 'documento',
        color: 'lavanda',
    },
    {
        titulo: 'Entregas de hoy',
        valor: props.dispensaciones.filter(
            d => fechaLocal(d.fecha_dispensacion) === hoy.value
        ).length,
        descripcion: 'Actividad diaria',
        icono: 'medicamento',
        color: 'azul',
    },
    {
        titulo: 'GES y crónicos',
        valor: props.dispensaciones.filter(
            d => Boolean(d.programa_id)
        ).length,
        descripcion: 'Entregas de programas',
        icono: 'corazon',
        color: 'verde',
    },
    {
        titulo: 'Con alergias',
        valor: props.dispensaciones.filter(
            d => d.estado_alergias === 'con_alergias'
        ).length,
        descripcion: 'Registros con antecedentes',
        icono: 'alerta',
        color: 'ambar',
    },
])

// HISTORIAL

const dispensacionesFiltradas = computed(() => {
    const texto = normalizar(busqueda.value)

    return props.dispensaciones.filter(dispensacion => {
        const contenido = normalizar([
            dispensacion.id,
            nombrePaciente(dispensacion.paciente),
            dispensacion.paciente?.rut,
            formatearRut(dispensacion.paciente?.rut),
            dispensacion.receta_id,
            dispensacion.programa_nombre,
            dispensacion.programa_tipo,
            dispensacion.usuario?.name,
            dispensacion.observaciones,
            formatearFecha(dispensacion.fecha_dispensacion),
            ...(dispensacion.detalles || []).map(
                detalle => nombreMedicamento(detalle.medicamento)
            ),
        ].join(' '))

        const coincideModalidad =
            filtroModalidad.value === 'todos' ||
            (
                filtroModalidad.value === 'programa'
                    ? Boolean(dispensacion.programa_id)
                    : !dispensacion.programa_id
            )

        const coincideAlergias =
            filtroAlergias.value === 'todos' ||
            (
                filtroAlergias.value === 'sin_registro'
                    ? !dispensacion.estado_alergias
                    : dispensacion.estado_alergias === filtroAlergias.value
            )

        return contenido.includes(texto) &&
            coincideModalidad &&
            coincideAlergias
    })
})

function limpiarFiltros() {
    busqueda.value = ''
    filtroModalidad.value = 'todos'
    filtroAlergias.value = 'todos'
}
</script>

<template>
    <Head title="Dispensaciones | FarmaControl" />

    <FarmaLayout
        titulo="Dispensaciones"
        subtitulo="Gestión de entregas de medicamentos asistenciales."
    >
        <div class="pagina">

            <!-- NAVEGACIÓN -->

            <div class="navegacion">
                <Link href="/dashboard" class="volver">
                    <svg viewBox="0 0 24 24">
                        <path :d="iconos.volver" />
                    </svg>
                    Volver al inicio
                </Link>

                <span class="fecha-actual">
                    {{ fechaActual }}
                </span>
            </div>

            <!-- BANNER PRINCIPAL -->

            <section class="banner">
                <div class="banner-contenido">
                    <span class="banner-etiqueta">
                        <span class="punto"></span>
                        FARMACIA ASISTENCIAL · CESFAM
                    </span>

                    <h1>Dispensación de medicamentos</h1>

                    <p>
                        Gestiona las entregas asistenciales,
                        las recetas médicas y los beneficios
                        de los programas GES/AUGE y crónicos.
                    </p>

                    <button
                        v-if="puedeDispensar"
                        type="button"
                        class="boton principal"
                        :disabled="form.processing"
                        @click="mostrarFormulario
                            ? cancelarFormulario()
                            : abrirFormulario()"
                    >
                        <svg viewBox="0 0 24 24">
                            <path :d="iconos.agregar" />
                        </svg>

                        {{
                            mostrarFormulario
                                ? 'Cerrar registro'
                                : 'Nueva dispensación'
                        }}
                    </button>
                </div>

                <div class="banner-ilustracion">
                    <svg viewBox="0 0 250 220">
                        <circle cx="125" cy="110" r="95" opacity=".2" />
                        <circle cx="125" cy="110" r="78" opacity=".15" />

                        <rect
                            x="65" y="55"
                            width="115" height="125"
                            rx="16"
                            fill="#ffffff"
                            fill-opacity=".25"
                        />

                        <rect
                            x="83" y="35"
                            width="80" height="25"
                            rx="7"
                            fill="#e3d5f5"
                        />

                        <rect
                            x="79" y="87"
                            width="87" height="62"
                            rx="8"
                            fill="#ffffff"
                            fill-opacity=".3"
                        />

                        <path
                            d="M122 99v37 M104 117h36"
                            stroke-width="5"
                        />

                        <g transform="rotate(-35 183 170)">
                            <rect
                                x="164" y="135"
                                width="38" height="70"
                                rx="19"
                                fill="#f8f7fc"
                            />
                            <path d="M164 170h38" />
                        </g>

                        <path d="M35 55v20 M25 65h20" />
                        <path d="M210 50v20 M200 60h20" />
                    </svg>
                </div>
            </section>

            <!-- INDICADORES -->

            <div class="titulo-bloque">
                <div>
                    <h2>Resumen general</h2>
                    <p>Actividad de farmacia asistencial.</p>
                </div>
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
                        <svg viewBox="0 0 24 24">
                            <path :d="iconos[indicador.icono]" />
                        </svg>
                    </div>

                    <div class="indicador-texto">
                        <span>{{ indicador.titulo }}</span>
                        <strong>{{ indicador.valor }}</strong>
                        <small>{{ indicador.descripcion }}</small>
                    </div>
                </article>
            </section>

            <div
                v-if="mensajeExito"
                class="notificacion verde"
                role="status"
            >
                <svg viewBox="0 0 24 24">
                    <path :d="iconos.check" />
                </svg>

                {{ mensajeExito }}
            </div>

            <div
                v-if="!puedeDispensar"
                class="aviso aviso-consulta"
            >
                Tu perfil dispone de acceso de consulta.
                No tiene permisos para registrar dispensaciones.
            </div>

            <!-- FORMULARIO -->

            <section
                v-if="mostrarFormulario && puedeDispensar"
                ref="formularioRef"
                class="panel formulario-panel"
            >
                <div class="panel-encabezado">
                    <div class="encabezado-identidad">
                        <div class="indicador-icono lavanda">
                            <svg viewBox="0 0 24 24">
                                <path :d="iconos.medicamento" />
                            </svg>
                        </div>

                        <div>
                            <h2>Nueva dispensación</h2>
                            <p>Completa los antecedentes de la entrega.</p>
                        </div>
                    </div>

                    <button
                        type="button"
                        class="boton secundario"
                        :disabled="form.processing"
                        @click="cancelarFormulario"
                    >
                        Cancelar
                    </button>
                </div>

                <form @submit.prevent="registrarDispensacion">

                    <div class="formulario-cuerpo">

                        <!-- IDENTIFICACIÓN DEL PACIENTE -->

                        <div class="paso-titulo">
                            <span>01</span>

                            <div>
                                <h3>Identificación del paciente</h3>
                                <p>Selecciona al beneficiario de la entrega.</p>
                            </div>
                        </div>

                        <div class="campos-dobles">
                            <div class="campo">
                                <label for="buscar-paciente">
                                    Buscar paciente
                                </label>

                                <input
                                    id="buscar-paciente"
                                    v-model="buscarPaciente"
                                    placeholder="Nombre, apellido o RUT"
                                    :disabled="form.processing"
                                />
                            </div>

                            <div class="campo">
                                <label for="paciente">
                                    Paciente *
                                </label>

                                <select
                                    id="paciente"
                                    v-model="form.paciente_id"
                                    :disabled="form.processing"
                                    required
                                >
                                    <option value="">
                                        Selecciona un paciente
                                    </option>

                                    <option
                                        v-if="pacienteSeleccionado &&
                                            !pacientesFiltrados.some(
                                                p => mismoId(
                                                    p.id,
                                                    pacienteSeleccionado.id
                                                )
                                            )"
                                        :value="pacienteSeleccionado.id"
                                    >
                                        {{ nombrePaciente(pacienteSeleccionado) }}
                                    </option>

                                    <option
                                        v-for="paciente in pacientesFiltrados"
                                        :key="paciente.id"
                                        :value="paciente.id"
                                    >
                                        {{ nombrePaciente(paciente) }}
                                        — {{ formatearRut(paciente.rut) }}
                                    </option>
                                </select>

                                <small class="error">
                                    {{ form.errors.paciente_id }}
                                </small>
                            </div>
                        </div>

                        <div
                            v-if="pacienteSeleccionado"
                            class="paciente-ficha"
                        >
                            <div class="avatar">
                                {{
                                    pacienteSeleccionado.nombres?.charAt(0)
                                }}{{
                                    pacienteSeleccionado.apellidos?.charAt(0)
                                }}
                            </div>

                            <div class="paciente-identidad">
                                <strong>
                                    {{ nombrePaciente(pacienteSeleccionado) }}
                                </strong>

                                <span>
                                    RUT:
                                    {{ formatearRut(pacienteSeleccionado.rut) }}
                                </span>

                                <span>
                                    Nacimiento:
                                    {{
                                        formatearFecha(
                                            pacienteSeleccionado.fecha_nacimiento
                                        )
                                    }}
                                </span>
                            </div>

                            <div class="paciente-etiquetas">
                                <span class="estado verde">
                                    Paciente activo
                                </span>

                                <span
                                    v-if="programasPaciente.length"
                                    class="estado lavanda"
                                >
                                    Beneficiario de programas
                                </span>
                            </div>
                        </div>

                        <!-- ALERGIAS -->

                        <div
                            v-if="pacienteSeleccionado"
                            class="alerta-alergias"
                            :class="claseAlergias(estadoAlergias)"
                        >
                            <svg viewBox="0 0 24 24">
                                <path :d="iconos.alerta" />
                            </svg>

                            <div>
                                <strong>
                                    {{ textoAlergias(estadoAlergias) }}
                                </strong>

                                <p v-if="estadoAlergias === 'con_alergias'">
                                    {{
                                        pacienteSeleccionado.alergias ||
                                        'Sin detalle registrado.'
                                    }}
                                </p>

                                <p v-else-if="estadoAlergias === 'desconocido'">
                                    Verifica los antecedentes antes
                                    de realizar la entrega.
                                </p>

                                <p v-else>
                                    No existen alergias conocidas
                                    registradas en la ficha.
                                </p>
                            </div>
                        </div>

                        <hr />

                        <!-- MODALIDAD -->

                        <div class="paso-titulo">
                            <span>02</span>

                            <div>
                                <h3>Modalidad de dispensación</h3>
                                <p>Selecciona el tipo de entrega asistencial.</p>
                            </div>
                        </div>

                        <div class="modalidades">

                            <button
                                type="button"
                                class="modalidad"
                                :class="{ seleccionada: modalidad === 'general' }"
                                :disabled="!pacienteSeleccionado || form.processing"
                                :aria-pressed="modalidad === 'general'"
                                @click="modalidad = 'general'"
                            >
                                <div class="indicador-icono lavanda">
                                    <svg viewBox="0 0 24 24">
                                        <path :d="iconos.documento" />
                                    </svg>
                                </div>

                                <div class="modalidad-texto">
                                    <strong>Dispensación general</strong>
                                    <small>Entrega asistencial habitual.</small>
                                </div>

                                <span class="radio"></span>
                            </button>

                            <button
                                type="button"
                                class="modalidad"
                                :class="{ seleccionada: modalidad === 'programa' }"
                                :disabled="!pacienteSeleccionado || form.processing"
                                :aria-pressed="modalidad === 'programa'"
                                @click="modalidad = 'programa'"
                            >
                                <div class="indicador-icono lavanda">
                                    <svg viewBox="0 0 24 24">
                                        <path :d="iconos.corazon" />
                                    </svg>
                                </div>

                                <div class="modalidad-texto">
                                    <strong>GES/AUGE y crónicos</strong>
                                    <small>Entrega con control de cuotas.</small>
                                </div>

                                <span class="radio"></span>
                            </button>

                        </div>

                        <div
                            v-if="modalidad === 'programa'"
                            class="programa-panel"
                        >
                            <div class="campo">
                                <label for="programa">
                                    Programa de salud *
                                </label>

                                <select
                                    id="programa"
                                    v-model="form.programa_id"
                                    :disabled="form.processing"
                                    required
                                >
                                    <option value="">
                                        Selecciona un programa
                                    </option>

                                    <option
                                        v-for="programa in programasPaciente"
                                        :key="programa.id"
                                        :value="programa.id"
                                    >
                                        {{ programa.nombre }}
                                        — {{ textoPrograma(programa.tipo) }}
                                    </option>
                                </select>

                                <small class="error">
                                    {{ form.errors.programa_id }}
                                </small>
                            </div>

                            <div
                                v-if="!programasPaciente.length"
                                class="aviso ambar"
                            >
                                El paciente no pertenece a ningún
                                programa de salud activo.
                            </div>

                            <div
                                v-if="programaSeleccionado"
                                class="programa-informacion"
                            >
                                <span class="estado verde">
                                    Beneficiario activo
                                </span>

                                <strong>
                                    {{ programaSeleccionado.nombre }}
                                </strong>

                                <p>
                                    {{ medicamentosCanasta.length }}
                                    medicamentos habilitados en la canasta.
                                </p>

                                <div
                                    v-if="medicamentosCanasta.length"
                                    class="canasta-lista"
                                >
                                    <span
                                        v-for="medicamento in medicamentosCanasta"
                                        :key="medicamento.id"
                                        class="canasta-item"
                                    >
                                        {{ nombreMedicamento(medicamento) }}
                                    </span>
                                </div>

                                <div v-else class="aviso ambar">
                                    Este programa no tiene medicamentos
                                    activos en su canasta.
                                </div>
                            </div>
                        </div>

                        <hr />

                        <!-- RECETA Y MEDICAMENTO -->

                        <div class="paso-titulo">
                            <span>03</span>

                            <div>
                                <h3>Receta y medicamento</h3>
                                <p>
                                    Selecciona una receta vigente
                                    y un medicamento autorizado.
                                </p>
                            </div>
                        </div>

                        <div class="campos-dobles">

                            <div class="campo">
                                <label for="receta">
                                    Receta médica *
                                </label>

                                <select
                                    id="receta"
                                    v-model="form.receta_id"
                                    :disabled="!pacienteSeleccionado ||
                                        form.processing"
                                    required
                                >
                                    <option value="">
                                        Selecciona una receta
                                    </option>

                                    <option
                                        v-for="receta in recetasDisponibles"
                                        :key="receta.id"
                                        :value="receta.id"
                                    >
                                        Receta #{{ receta.id }}
                                        — {{ formatearFecha(receta.fecha_emision) }}
                                    </option>
                                </select>

                                <small class="error">
                                    {{ form.errors.receta_id }}
                                </small>

                                <small
                                    v-if="pacienteSeleccionado &&
                                        !recetasDisponibles.length"
                                    class="ayuda"
                                >
                                    El paciente no tiene recetas vigentes.
                                </small>
                            </div>

                            <div class="campo">
                                <label for="medicamento">
                                    Medicamento *
                                </label>

                                <select
                                    id="medicamento"
                                    v-model="form.medicamento_id"
                                    :disabled="!recetaSeleccionada ||
                                        (modalidad === 'programa' &&
                                        !programaSeleccionado) ||
                                        form.processing"
                                    required
                                >
                                    <option value="">
                                        Selecciona un medicamento
                                    </option>

                                    <option
                                        v-for="medicamento in medicamentosDisponibles"
                                        :key="medicamento.id"
                                        :value="medicamento.id"
                                    >
                                        {{ nombreMedicamento(medicamento) }}
                                        — Pendiente:
                                        {{ cantidadPendiente(medicamento.id) }}
                                    </option>
                                </select>

                                <small class="error">
                                    {{ form.errors.medicamento_id }}
                                </small>

                                <small
                                    v-if="recetaSeleccionada &&
                                        !medicamentosDisponibles.length"
                                    class="ayuda"
                                >
                                    No existen medicamentos pendientes
                                    que cumplan los requisitos seleccionados.
                                </small>
                            </div>

                        </div>

                        <!-- INFORMACIÓN DE RECETA -->

                        <div
                            v-if="recetaSeleccionada"
                            class="receta-ficha"
                        >
                            <div class="receta-titulo">
                                <strong>
                                    Receta #{{ recetaSeleccionada.id }}
                                </strong>

                                <span class="estado verde">
                                    Vigente
                                </span>
                            </div>

                            <div class="receta-fechas">
                                <span>
                                    Emisión:
                                    {{
                                        formatearFecha(
                                            recetaSeleccionada.fecha_emision
                                        )
                                    }}
                                </span>

                                <span>
                                    Vencimiento:
                                    {{
                                        formatearFecha(
                                            recetaSeleccionada.fecha_vencimiento
                                        )
                                    }}
                                </span>
                            </div>

                            <div class="tabla-contenedor">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Medicamento prescrito</th>
                                            <th>Cantidad</th>
                                            <th>Pendiente total</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        <tr
                                            v-for="detalle in recetaSeleccionada.detalles"
                                            :key="detalle.id"
                                        >
                                            <td>
                                                {{
                                                    nombreMedicamento(
                                                        detalle.medicamento ||
                                                        obtenerMedicamento(
                                                            detalle.medicamento_id
                                                        )
                                                    )
                                                }}
                                            </td>

                                            <td>
                                                {{ detalle.cantidad_prescrita }}
                                            </td>

                                            <td>
                                                {{
                                                    cantidadPendiente(
                                                        detalle.medicamento_id
                                                    )
                                                }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- CONTROL DE CUOTAS -->

                        <section
                            v-if="modalidad === 'programa' && medicamentoCanasta"
                            class="cuota-panel"
                        >
                            <div class="cuota-encabezado">
                                <div>
                                    <h3>Control de cuota del programa</h3>
                                    <p>
                                        Período móvil de {{ periodoDias }} días.
                                    </p>
                                </div>

                                <span class="estado lavanda">
                                    Cobertura {{ cobertura }}%
                                </span>
                            </div>

                            <div class="cuota-indicadores">

                                <div>
                                    <span>Cuota máxima</span>
                                    <strong>{{ cuotaMaxima }}</strong>
                                    <small>Unidades autorizadas</small>
                                </div>

                                <div>
                                    <span>Ya entregadas</span>
                                    <strong>{{ unidadesEntregadasPrograma }}</strong>
                                    <small>Unidades del período</small>
                                </div>

                                <div>
                                    <span>Disponibles</span>

                                    <strong
                                        :class="cuotaDisponible > 0
                                            ? 'texto-verde'
                                            : 'texto-rojo'"
                                    >
                                        {{ cuotaDisponible }}
                                    </strong>

                                    <small>Unidades restantes</small>
                                </div>

                            </div>

                            <div
                                class="progreso"
                                role="progressbar"
                                aria-label="Cuota utilizada"
                                :aria-valuenow="Math.round(porcentajeUtilizado)"
                                aria-valuemin="0"
                                aria-valuemax="100"
                            >
                                <div
                                    class="progreso-barra"
                                    :class="{ agotado: cuotaDisponible === 0 }"
                                    :style="{
                                        width: porcentajeUtilizado + '%'
                                    }"
                                ></div>
                            </div>

                            <div class="progreso-texto">
                                <span>
                                    Desde {{ formatearFecha(inicioPeriodo) }}
                                </span>

                                <span>
                                    {{ Math.round(porcentajeUtilizado) }}%
                                    utilizado
                                </span>
                            </div>

                            <div
                                v-if="cuotaDisponible === 0"
                                class="aviso rojo"
                            >
                                Cuota agotada. No es posible registrar
                                otra entrega de este medicamento
                                dentro del programa durante el período vigente.
                            </div>

                            <div
                                v-if="!programaValido"
                                class="aviso rojo"
                            >
                                La configuración de la canasta no
                                permite registrar esta entrega.
                            </div>

                            <small class="ayuda">
                                El cálculo es informativo.
                                El servidor verificará nuevamente
                                la cuota al registrar la entrega.
                            </small>
                        </section>

                        <hr />
<!-- IDENTIFICACIÓN DEL RETIRANTE -->

<div class="paso-titulo">
    <span>04</span>

    <div>
        <h3>Identificación del retirante</h3>
        <p>Selecciona quién recibe los medicamentos.</p>
    </div>
</div>

<div class="modalidades">

    <button
        type="button"
        class="modalidad"
        :class="{
            seleccionada: form.tipo_retiro === 'paciente'
        }"
        :disabled="!pacienteSeleccionado || form.processing"
        @click="form.tipo_retiro = 'paciente'"
    >
        <div class="indicador-icono lavanda">
            <svg viewBox="0 0 24 24">
                <circle cx="12" cy="7" r="4" />
                <path d="M4 21v-2a8 8 0 0 1 16 0v2" />
            </svg>
        </div>

        <div class="modalidad-texto">
            <strong>Retiro personal</strong>
            <small>El paciente recibe sus medicamentos.</small>
        </div>

        <span class="radio"></span>
    </button>

    <button
        type="button"
        class="modalidad"
        :class="{
            seleccionada:
                form.tipo_retiro === 'tercero_autorizado'
        }"
        :disabled="!pacienteSeleccionado || form.processing"
        @click="form.tipo_retiro = 'tercero_autorizado'"
    >
        <div class="indicador-icono lavanda">
            <svg viewBox="0 0 24 24">
                <path :d="iconos.documento" />
            </svg>
        </div>

        <div class="modalidad-texto">
            <strong>Tercero autorizado</strong>
            <small>Otra persona realiza el retiro.</small>
        </div>

        <span class="radio"></span>
    </button>

</div>

<small class="error">
    {{ form.errors.tipo_retiro }}
</small>

<div
    v-if="form.tipo_retiro === 'paciente' && pacienteSeleccionado"
    class="programa-panel"
>
    <div class="programa-informacion">
        <span class="estado verde">
            Retiro personal
        </span>

        <strong>
            {{ nombrePaciente(pacienteSeleccionado) }}
        </strong>

        <p>
            RUT: {{ formatearRut(pacienteSeleccionado.rut) }}
        </p>

        <p>
            Se utilizarán los datos de la ficha del paciente.
        </p>
    </div>

    <div
        v-if="!rutValido(pacienteSeleccionado.rut)"
        class="aviso rojo"
    >
        El paciente no tiene un RUT válido registrado.

        <Link href="/pacientes">
            Revisar ficha del paciente
        </Link>
    </div>

    <small class="error">
        {{ form.errors.retirante_rut }}
    </small>
</div>

<div
    v-if="form.tipo_retiro === 'tercero_autorizado'"
    class="programa-panel"
>
    <div class="programa-informacion">
        <strong>Datos del tercero autorizado</strong>

        <p>
            Identificación de la persona que recibe
            los medicamentos.
        </p>
    </div>

    <div class="campo">
        <label for="retirante-nombre">
            Nombre completo *
        </label>

        <input
            id="retirante-nombre"
            v-model="form.retirante_nombre"
            placeholder="Nombre y apellidos"
            maxlength="200"
            minlength="3"
            :disabled="form.processing"
            required
        />

        <small class="error">
            {{ form.errors.retirante_nombre }}
        </small>
    </div>

    <div class="campos-dobles">

        <div class="campo">
            <label for="retirante-rut">
                RUT *
            </label>

            <input
                id="retirante-rut"
                v-model="form.retirante_rut"
                placeholder="12.345.678-5"
                maxlength="12"
                :disabled="form.processing"
                @blur="
                    rutValido(form.retirante_rut) &&
                    (form.retirante_rut =
                        formatearRut(form.retirante_rut))
                "
                required
            />

            <small
                v-if="
                    form.retirante_rut &&
                    !rutValido(form.retirante_rut)
                "
                class="error"
            >
                Ingresa un RUT válido.
            </small>

            <small
                v-else-if="
                    form.retirante_rut &&
                    rutCanonico(form.retirante_rut) ===
                    rutCanonico(pacienteSeleccionado?.rut)
                "
                class="error"
            >
                El RUT debe ser distinto al del paciente.
            </small>

            <small class="error">
                {{ form.errors.retirante_rut }}
            </small>
        </div>

        <div class="campo">
            <label for="retirante-parentesco">
                Relación con el paciente *
            </label>

            <input
                id="retirante-parentesco"
                v-model="form.retirante_parentesco"
                placeholder="Ej. Madre, padre o cuidador"
                maxlength="100"
                :disabled="form.processing"
                required
            />

            <small class="error">
                {{ form.errors.retirante_parentesco }}
            </small>
        </div>

    </div>

    <label class="confirmacion">
        <input
            v-model="form.autorizacion_verificada"
            type="checkbox"
            :disabled="form.processing"
        />

        <span>
            <strong>
                Confirmo la autorización del tercero
            </strong>

            <small>
                He verificado que la persona está
                autorizada para retirar los medicamentos.
            </small>
        </span>
    </label>

    <small class="error">
        {{ form.errors.autorizacion_verificada }}
    </small>

    <div class="aviso">
        Los datos quedarán registrados en el historial
        para mantener la trazabilidad de la entrega.
    </div>
</div>

<hr />
                        <!-- CONFIRMACIÓN -->

                        <div class="paso-titulo">
                            <span>04</span>

                            <div>
                                <h3>Confirmación de entrega</h3>
                                <p>Verifica las unidades y los antecedentes.</p>
                            </div>
                        </div>

                        <div class="campos-dobles">

                            <div class="campo">
                                <label for="cantidad">
                                    Cantidad a dispensar *
                                </label>

                                <input
                                    id="cantidad"
                                    v-model.number="form.cantidad"
                                    type="number"
                                    min="1"
                                    step="1"
                                    :max="maximoEntregable"
                                    :disabled="!medicamentoSeleccionado ||
                                        maximoEntregable < 1 ||
                                        form.processing"
                                    required
                                />

                                <small class="ayuda">
                                    Máximo autorizado:
                                    {{ maximoEntregable }} unidades.
                                    Sujeto a disponibilidad de inventario.
                                </small>

                                <small
                                    v-if="Number(form.cantidad) > maximoEntregable &&
                                        medicamentoSeleccionado"
                                    class="error"
                                >
                                    La cantidad supera el máximo disponible.
                                </small>

                                <small class="error">
                                    {{ form.errors.cantidad }}
                                </small>
                            </div>

                            <div class="costo-entrega">
                                <span>Costo para el paciente</span>
                                <strong>$0</strong>
                                <small>Entrega asistencial gratuita</small>
                            </div>

                        </div>

                        <div class="campo">
                            <label for="observaciones">
                                Observaciones
                            </label>

                            <textarea
                                id="observaciones"
                                v-model="form.observaciones"
                                maxlength="255"
                                rows="3"
                                placeholder="Observaciones de la entrega..."
                                :disabled="form.processing"
                            ></textarea>

                            <small class="ayuda">
                                {{ form.observaciones.length }}/255 caracteres
                            </small>

                            <small class="error">
                                {{ form.errors.observaciones }}
                            </small>
                        </div>

                        <label class="confirmacion">
                            <input
                                v-model="form.alergias_revisadas"
                                type="checkbox"
                                :disabled="!medicamentoSeleccionado ||
                                    form.processing"
                            />

                            <span>
                                <strong>
                                    Confirmo la revisión de antecedentes
                                </strong>

                                <small>
                                    He revisado las alergias registradas
                                    y los antecedentes del paciente
                                    antes de efectuar la dispensación.
                                </small>
                            </span>
                        </label>

                        <small class="error">
                            {{ form.errors.alergias_revisadas }}
                        </small>

                        <div class="aviso">
                            El inventario será descontado automáticamente
                            mediante FEFO. Esta entrega corresponde
                            exclusivamente a una simulación académica
                            sin validez clínica.
                        </div>

                        <div
                            v-if="form.hasErrors"
                            class="notificacion rojo"
                            role="alert"
                        >
                            <div>
                                <strong>
                                    No fue posible registrar la entrega.
                                </strong>

                                <p
                                    v-for="(error, campo) in form.errors"
                                    :key="campo"
                                >
                                    {{ error }}
                                </p>
                            </div>
                        </div>

                    </div>

                    <div class="formulario-pie">

                        <button
                            type="button"
                            class="boton secundario"
                            :disabled="form.processing"
                            @click="cancelarFormulario"
                        >
                            Cancelar
                        </button>

                        <button
                            type="submit"
                            class="boton principal"
                            :disabled="!puedeRegistrar"
                        >
                            <svg viewBox="0 0 24 24">
                                <path :d="iconos.check" />
                            </svg>

                            {{
                                form.processing
                                    ? 'Registrando...'
                                    : 'Registrar dispensación'
                            }}
                        </button>

                    </div>

                </form>
            </section>

            <!-- HISTORIAL -->

            <div class="titulo-bloque">
                <div>
                    <h2>Historial de dispensaciones</h2>
                    <p>
                        Consulta las entregas realizadas
                        y sus comprobantes académicos.
                    </p>
                </div>

                <span class="contador">
                    {{ dispensacionesFiltradas.length }} registros
                </span>
            </div>

            <section class="panel">

                <!-- FILTROS -->

                <div class="filtros">

                    <div class="campo">
                        <label for="busqueda">
                            Buscar registro
                        </label>

                        <div class="input-icono">
                            <svg viewBox="0 0 24 24">
                                <path :d="iconos.buscar" />
                            </svg>

                            <input
                                id="busqueda"
                                v-model="busqueda"
                                placeholder="Paciente, RUT, programa..."
                            />
                        </div>
                    </div>

                    <div class="campo">
                        <label for="modalidad-filtro">
                            Modalidad
                        </label>

                        <select
                            id="modalidad-filtro"
                            v-model="filtroModalidad"
                        >
                            <option value="todos">Todas</option>
                            <option value="general">General</option>
                            <option value="programa">GES y crónicos</option>
                        </select>
                    </div>

                    <div class="campo">
                        <label for="alergias-filtro">
                            Alergias
                        </label>

                        <select
                            id="alergias-filtro"
                            v-model="filtroAlergias"
                        >
                            <option value="todos">Todas</option>

                            <option value="con_alergias">
                                Con alergias
                            </option>

                            <option value="sin_alergias">
                                Sin alergias
                            </option>

                            <option value="desconocido">
                                Desconocidas
                            </option>

                            <option value="sin_registro">
                                Sin registro
                            </option>
                        </select>
                    </div>

                    <button
                        type="button"
                        class="boton secundario"
                        @click="limpiarFiltros"
                    >
                        Limpiar filtros
                    </button>

                </div>

                <!-- TABLA -->

                <div
                    v-if="dispensacionesFiltradas.length"
                    class="tabla-contenedor historial-tabla"
                >
                    <table>
                        <thead>
                            <tr>
                                <th>Registro</th>
                                <th>Paciente</th>
                                <th>Modalidad</th>
                                <th>Medicamentos</th>
                                <th>Alergias</th>
                                <th>Retirante</th>
                                <th>Comprobante</th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr
                                v-for="dispensacion in dispensacionesFiltradas"
                                :key="dispensacion.id"
                            >
                                <td>
                                    <strong>
                                        DIS-{{
                                            String(dispensacion.id)
                                                .padStart(6, '0')
                                        }}
                                    </strong>

                                    <small>
                                        {{
                                            formatearFecha(
                                                dispensacion.fecha_dispensacion
                                            )
                                        }}
                                    </small>

                                    <small>
                                        Receta #{{ dispensacion.receta_id || '—' }}
                                    </small>
                                </td>

                                <td>
                                    <strong>
                                        {{ nombrePaciente(dispensacion.paciente) }}
                                    </strong>

                                    <small>
                                        {{
                                            formatearRut(
                                                dispensacion.paciente?.rut
                                            )
                                        }}
                                    </small>
                                </td>

                                <td>
                                    <span
                                        class="estado"
                                        :class="dispensacion.programa_id
                                            ? 'lavanda'
                                            : 'gris'"
                                    >
                                        {{
                                            dispensacion.programa_id
                                                ? textoPrograma(
                                                    dispensacion.programa_tipo
                                                )
                                                : 'General'
                                        }}
                                    </span>

                                    <small v-if="dispensacion.programa_id">
                                        {{
                                            dispensacion.programa_nombre ||
                                            dispensacion.programa?.nombre
                                        }}
                                    </small>

                                    <small
                                        v-if="dispensacion.cobertura !== null &&
                                            dispensacion.cobertura !== undefined"
                                    >
                                        Cobertura:
                                        {{ dispensacion.cobertura }}%
                                    </small>

                                    <small class="texto-verde">
                                        Entrega gratuita
                                    </small>
                                </td>

                                <td>
                                    <div
                                        v-for="detalle in dispensacion.detalles"
                                        :key="detalle.id"
                                        class="detalle-historial"
                                    >
                                        <strong>
                                            {{
                                                nombreMedicamento(
                                                    detalle.medicamento
                                                )
                                            }}
                                        </strong>

                                        <small>
                                            {{ detalle.cantidad }} unidades
                                        </small>
                                    </div>

                                    <small v-if="dispensacion.observaciones">
                                        Obs: {{ dispensacion.observaciones }}
                                    </small>
                                </td>

                                <td>
                                    <span
                                        class="estado"
                                        :class="claseAlergias(
                                            dispensacion.estado_alergias
                                        )"
                                    >
                                        {{
                                            textoAlergias(
                                                dispensacion.estado_alergias
                                            )
                                        }}
                                    </span>

                                    <small
                                        v-if="dispensacion.estado_alergias ===
                                            'con_alergias'"
                                    >
                                        {{ dispensacion.alergias }}
                                    </small>

                                    <small v-if="dispensacion.alergias_revisadas">
                                        Antecedentes revisados
                                    </small>
                                </td>

                                <td>
    <template v-if="dispensacion.tipo_retiro">

        <span
            class="estado"
            :class="
                dispensacion.tipo_retiro === 'paciente'
                    ? 'verde'
                    : 'lavanda'
            "
        >
            {{
                dispensacion.tipo_retiro === 'paciente'
                    ? 'Retiro personal'
                    : 'Tercero autorizado'
            }}
        </span>

        <small>
            <strong>
                {{ dispensacion.retirante_nombre }}
            </strong>
        </small>

        <small>
            {{ formatearRut(dispensacion.retirante_rut) }}
        </small>

        <small v-if="dispensacion.retirante_parentesco">
            {{ dispensacion.retirante_parentesco }}
        </small>

        <small
            v-if="dispensacion.autorizacion_verificada"
            class="texto-verde"
        >
            Autorización verificada
        </small>

    </template>

    <span v-else class="estado gris">
        Sin registro histórico
    </span>
</td>

<td>
    {{
        dispensacion.usuario?.name ||
        'Sin registro'
    }}
</td>

                                <td>
                                    <div class="acciones-tabla">

                                        <a
                                            :href="`/dispensaciones/${dispensacion.id}/pdf`"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="boton-icono lavanda"
                                            title="Ver comprobante PDF"
                                            aria-label="Ver comprobante PDF"
                                        >
                                            <svg viewBox="0 0 24 24">
                                                <path :d="iconos.ver" />
                                            </svg>
                                        </a>

                                        <a
                                            :href="`/dispensaciones/${dispensacion.id}/pdf/descargar`"
                                            class="boton-icono azul"
                                            title="Descargar comprobante PDF"
                                            aria-label="Descargar comprobante PDF"
                                        >
                                            <svg viewBox="0 0 24 24">
                                                <path :d="iconos.descargar" />
                                            </svg>
                                        </a>

                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- ESTADO VACÍO -->

                <div v-else class="estado-vacio">
                    <div class="vacio-icono">
                        <svg viewBox="0 0 24 24">
                            <path :d="iconos.documento" />
                        </svg>
                    </div>

                    <h3>No hay dispensaciones para mostrar</h3>

                    <p>
                        {{
                            dispensaciones.length
                                ? 'Modifica los filtros de búsqueda.'
                                : 'Las entregas registradas aparecerán aquí.'
                        }}
                    </p>

                    <button
                        v-if="dispensaciones.length"
                        type="button"
                        class="boton secundario"
                        @click="limpiarFiltros"
                    >
                        Limpiar filtros
                    </button>

                    <button
                        v-else-if="puedeDispensar && !mostrarFormulario"
                        type="button"
                        class="boton principal"
                        @click="abrirFormulario"
                    >
                        Nueva dispensación
                    </button>
                </div>

            </section>

        </div>
    </FarmaLayout>
</template>

<style scoped>
* {
    box-sizing: border-box;
}

.pagina {
    --principal: #665579;
    --hover: #51435f;
    --lavanda: #e3d5f5;
    --fondo: #f8f7fc;
    --borde: #e6e0ef;
    --texto: #333344;
    --secundario: #777181;

    width: 100%;
    min-width: 0;
    padding-bottom: 90px;
    color: var(--texto);
    font-family: 'Inter', Arial, sans-serif;
}

svg {
    fill: none;
    stroke: currentColor;
    stroke-width: 1.7;
    stroke-linecap: round;
    stroke-linejoin: round;
}

button,
input,
select,
textarea {
    font-family: inherit;
}

button:disabled {
    opacity: .5;
    cursor: not-allowed;
}

button:focus-visible,
a:focus-visible {
    outline: 3px solid #b9a2d9;
    outline-offset: 3px;
}

/* NAVEGACIÓN */

.navegacion {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 12px;
    margin-bottom: 20px;
}

.volver {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 8px 12px;
    border: 1px solid var(--borde);
    border-radius: 8px;
    background: white;
    color: var(--principal);
    text-decoration: none;
    font-size: 12px;
    font-weight: 600;
    transition: .2s;
}

.volver:hover {
    background: var(--lavanda);
    transform: translateX(-2px);
}

.volver svg {
    width: 15px;
    height: 15px;
}

.fecha-actual {
    color: var(--secundario);
    font-size: 12px;
    text-transform: capitalize;
}

/* BANNER */

.banner {
    display: flex;
    align-items: center;
    justify-content: space-between;
    min-height: 245px;
    padding: 34px 38px;
    margin-bottom: 30px;
    background: linear-gradient(
        115deg,
        #e3d5f5,
        #ede4f8 60%,
        #f4effb
    );
    border: 1px solid #ddd0ed;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 6px 24px #6655790a;
}

.banner-contenido {
    max-width: 680px;
}

.banner-etiqueta {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 7px 11px;
    background: #ffffff80;
    border: 1px solid #ffffff90;
    border-radius: 7px;
    color: var(--principal);
    font-size: 10px;
    font-weight: 700;
    letter-spacing: .9px;
}

.punto {
    width: 7px;
    height: 7px;
    background: #8e75ae;
    border-radius: 50%;
}

.banner h1 {
    margin: 17px 0 12px;
    color: var(--texto);
    font-size: clamp(26px, 3vw, 36px);
    font-weight: 800;
    letter-spacing: -1.1px;
    line-height: 1.2;
}

.banner p {
    max-width: 570px;
    margin: 0 0 24px;
    color: var(--principal);
    font-size: 14px;
    line-height: 1.8;
}

.banner-ilustracion {
    flex: 0 0 230px;
    color: #a18bbd;
}

.banner-ilustracion svg {
    width: 100%;
    display: block;
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
    white-space: nowrap;
    cursor: pointer;
    transition: .2s;
}

.boton svg {
    width: 17px;
    height: 17px;
}

.principal {
    background: var(--principal);
    color: white;
    box-shadow: 0 3px 8px #66557918;
}

.principal:hover:not(:disabled) {
    background: var(--hover);
    transform: translateY(-1px);
}

.secundario {
    background: #f0e8f9;
    color: var(--principal);
    border-color: #e0d3ef;
}

.secundario:hover:not(:disabled) {
    background: var(--lavanda);
}

/* INDICADORES */

.titulo-bloque {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 15px;
    margin-bottom: 17px;
}

.titulo-bloque h2 {
    margin: 0 0 5px;
    font-size: 19px;
    font-weight: 750;
    letter-spacing: -.4px;
}

.titulo-bloque p {
    margin: 0;
    color: var(--secundario);
    font-size: 12px;
    line-height: 1.6;
}

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
    padding: 22px 18px;
    background: white;
    border: 1px solid var(--borde);
    border-radius: 15px;
    box-shadow: 0 3px 15px #33334406;
    transition: .2s;
}

.tarjeta-indicador:hover {
    transform: translateY(-3px);
    border-color: #d2bfea;
}

.indicador-icono {
    display: grid;
    place-items: center;
    width: 48px;
    height: 48px;
    border-radius: 12px;
    flex-shrink: 0;
}

.indicador-icono svg {
    width: 25px;
    height: 25px;
}

.indicador-texto {
    display: flex;
    flex-direction: column;
    gap: 6px;
    min-width: 0;
}

.indicador-texto span {
    color: #6e6879;
    font-size: 12px;
    font-weight: 600;
}

.indicador-texto strong {
    font-size: 30px;
    font-weight: 750;
    line-height: 1.2;
}

.indicador-texto small {
    color: var(--secundario);
    font-size: 11px;
}

/* COLORES SEMÁNTICOS */

.lavanda {
    background: #eee4fa;
    color: #795c9b;
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
    color: #94601c;
}

.rojo {
    background: #fce7e9;
    color: #a53e4b;
}

.gris {
    background: #f0eef3;
    color: #6e6879;
}

.texto-verde {
    color: #20835a !important;
}

.texto-rojo {
    color: #b74c58 !important;
}

/* PANELES */

.panel {
    min-width: 0;
    background: white;
    border: 1px solid var(--borde);
    border-radius: 17px;
    overflow: hidden;
    box-shadow: 0 4px 20px #33334406;
}

.formulario-panel {
    margin-bottom: 32px;
    scroll-margin-top: 90px;
}

.panel-encabezado {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 16px;
    padding: 24px;
    background: linear-gradient(
        120deg,
        #f0e8f9,
        #faf8fd
    );
    border-bottom: 1px solid var(--borde);
}

.encabezado-identidad {
    display: flex;
    align-items: center;
    gap: 14px;
}

.panel-encabezado h2 {
    margin: 0 0 5px;
    font-size: 19px;
    font-weight: 750;
}

.panel-encabezado p {
    margin: 0;
    color: var(--secundario);
    font-size: 12px;
}

.formulario-cuerpo {
    display: flex;
    flex-direction: column;
    gap: 22px;
    padding: 28px;
}

.formulario-pie {
    display: flex;
    justify-content: flex-end;
    flex-wrap: wrap;
    gap: 10px;
    padding: 20px 28px;
    background: #fcfbfe;
    border-top: 1px solid var(--borde);
}

.paso-titulo {
    display: flex;
    align-items: center;
    gap: 13px;
}

.paso-titulo > span {
    display: grid;
    place-items: center;
    width: 37px;
    height: 37px;
    border-radius: 10px;
    background: var(--lavanda);
    color: var(--principal);
    font-size: 12px;
    font-weight: 700;
    flex-shrink: 0;
}

.paso-titulo h3 {
    margin: 0 0 4px;
    font-size: 15px;
    font-weight: 700;
}

.paso-titulo p {
    margin: 0;
    color: var(--secundario);
    font-size: 12px;
}

hr {
    border: 0;
    border-top: 1px solid var(--borde);
    margin: 5px 0;
}

/* CAMPOS */

.campos-dobles {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 18px;
}

.campo {
    display: flex;
    flex-direction: column;
    gap: 8px;
    min-width: 0;
}

.campo label {
    font-size: 12px;
    font-weight: 650;
}

.campo input,
.campo select,
.campo textarea {
    width: 100%;
    min-width: 0;
    min-height: 43px;
    padding: 11px 12px;
    background: white;
    color: var(--texto);
    border: 1px solid var(--borde);
    border-radius: 9px;
    font-size: 13px;
    transition: .2s;
}

.campo textarea {
    resize: vertical;
}

.campo input:focus,
.campo select:focus,
.campo textarea:focus {
    outline: none;
    border-color: #a58abb;
    box-shadow: 0 0 0 3px #e3d5f580;
}

.campo input:disabled,
.campo select:disabled,
.campo textarea:disabled {
    background: #f4f2f7;
    cursor: not-allowed;
}

.ayuda {
    color: var(--secundario);
    font-size: 11px;
    line-height: 1.6;
}

.error {
    color: #b74c58;
    font-size: 11px;
}

.error:empty {
    display: none;
}

/* PACIENTE */

.paciente-ficha {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 18px;
    background: var(--fondo);
    border: 1px solid var(--borde);
    border-radius: 12px;
}

.avatar {
    display: grid;
    place-items: center;
    width: 48px;
    height: 48px;
    flex-shrink: 0;
    border-radius: 50%;
    background: var(--lavanda);
    color: var(--principal);
    font-size: 16px;
    font-weight: 700;
}

.paciente-identidad {
    display: flex;
    flex-direction: column;
    gap: 5px;
    flex: 1;
}

.paciente-identidad strong {
    font-size: 14px;
}

.paciente-identidad span {
    color: var(--secundario);
    font-size: 12px;
}

.paciente-etiquetas {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
}

.alerta-alergias {
    display: flex;
    align-items: flex-start;
    gap: 13px;
    padding: 16px;
    border-radius: 11px;
    border-left: 3px solid currentColor;
}

.alerta-alergias svg {
    width: 22px;
    height: 22px;
    flex-shrink: 0;
}

.alerta-alergias strong {
    font-size: 13px;
}

.alerta-alergias p {
    margin: 5px 0 0;
    font-size: 12px;
    line-height: 1.7;
}

/* MODALIDADES */

.modalidades {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 16px;
}

.modalidad {
    display: flex;
    align-items: center;
    gap: 13px;
    padding: 18px;
    background: white;
    border: 1px solid var(--borde);
    border-radius: 12px;
    color: var(--texto);
    text-align: left;
    cursor: pointer;
    transition: .2s;
}

.modalidad.seleccionada {
    background: #f2ebfa;
    border-color: #bba6d8;
    box-shadow: 0 0 0 1px #bba6d8;
}

.modalidad:hover:not(:disabled) {
    border-color: #bba6d8;
}

.modalidad-texto {
    display: flex;
    flex-direction: column;
    gap: 6px;
    flex: 1;
}

.modalidad strong {
    font-size: 13px;
    font-weight: 650;
}

.modalidad small {
    color: var(--secundario);
    font-size: 11px;
    line-height: 1.5;
}

.radio {
    width: 17px;
    height: 17px;
    flex-shrink: 0;
    border: 2px solid #d0c5dc;
    border-radius: 50%;
}

.seleccionada .radio {
    border: 5px solid #8062a5;
    background: white;
}

/* PROGRAMAS */

.programa-panel {
    display: flex;
    flex-direction: column;
    gap: 15px;
    padding: 20px;
    background: var(--fondo);
    border: 1px solid var(--borde);
    border-radius: 12px;
}

.programa-informacion {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 8px;
}

.programa-informacion strong {
    font-size: 15px;
}

.programa-informacion p {
    margin: 0;
    color: var(--secundario);
    font-size: 12px;
}

.canasta-lista {
    display: flex;
    flex-wrap: wrap;
    gap: 7px;
    margin-top: 6px;
}

.canasta-item {
    padding: 7px 10px;
    background: white;
    border: 1px solid var(--borde);
    border-radius: 7px;
    color: var(--principal);
    font-size: 11px;
}

/* RECETA */

.receta-ficha {
    display: flex;
    flex-direction: column;
    gap: 15px;
    padding: 18px;
    background: var(--fondo);
    border: 1px solid var(--borde);
    border-radius: 12px;
}

.receta-titulo {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 14px;
}

.receta-fechas {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    color: var(--secundario);
    font-size: 12px;
}

/* CONTROL DE CUOTAS */

.cuota-panel {
    display: flex;
    flex-direction: column;
    gap: 17px;
    padding: 22px;
    background: #f8f5fc;
    border: 1px solid #ddd0ed;
    border-radius: 14px;
}

.cuota-encabezado {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 12px;
}

.cuota-encabezado h3 {
    margin: 0 0 6px;
    font-size: 15px;
    font-weight: 700;
}

.cuota-encabezado p {
    margin: 0;
    color: var(--secundario);
    font-size: 12px;
}

.cuota-indicadores {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 12px;
}

.cuota-indicadores > div {
    display: flex;
    flex-direction: column;
    gap: 7px;
    padding: 18px;
    background: white;
    border: 1px solid var(--borde);
    border-radius: 11px;
}

.cuota-indicadores span {
    font-size: 12px;
    color: var(--secundario);
}

.cuota-indicadores strong {
    font-size: 28px;
    font-weight: 750;
}

.cuota-indicadores small {
    font-size: 11px;
    color: var(--secundario);
}

.progreso {
    height: 9px;
    background: #e3d5f5;
    border-radius: 20px;
    overflow: hidden;
}

.progreso-barra {
    height: 100%;
    background: #8062a5;
    border-radius: 20px;
    transition: width .3s ease;
}

.progreso-barra.agotado {
    background: #b74c58;
}

.progreso-texto {
    display: flex;
    justify-content: space-between;
    gap: 10px;
    font-size: 11px;
    color: var(--secundario);
}

/* CONFIRMACIÓN */

.costo-entrega {
    display: flex;
    flex-direction: column;
    gap: 5px;
    padding: 16px;
    background: #edf8f1;
    border: 1px solid #d5eddf;
    border-radius: 11px;
    color: #20835a;
}

.costo-entrega span {
    font-size: 12px;
    font-weight: 600;
}

.costo-entrega strong {
    font-size: 28px;
    font-weight: 750;
}

.costo-entrega small {
    font-size: 11px;
}

.confirmacion {
    display: flex;
    align-items: flex-start;
    gap: 13px;
    padding: 18px;
    background: var(--fondo);
    border: 1px solid var(--borde);
    border-radius: 11px;
    cursor: pointer;
}

.confirmacion input {
    width: 18px;
    height: 18px;
    margin-top: 2px;
    accent-color: var(--principal);
    flex-shrink: 0;
}

.confirmacion > span {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.confirmacion strong {
    font-size: 13px;
}

.confirmacion small {
    color: var(--secundario);
    font-size: 12px;
    line-height: 1.6;
}

.aviso {
    padding: 14px;
    border-radius: 8px;
    border: 1px solid var(--borde);
    border-left: 3px solid #a18bbd;
    font-size: 12px;
    line-height: 1.7;
}

.aviso:not(.rojo):not(.ambar) {
    background: var(--fondo);
    color: var(--secundario);
}

.aviso-consulta {
    margin-bottom: 24px;
}

.notificacion {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 16px;
    border-radius: 11px;
    margin-bottom: 22px;
    font-size: 13px;
}

.notificacion svg {
    width: 21px;
    height: 21px;
}

.notificacion p {
    margin: 6px 0;
    font-size: 12px;
}

/* HISTORIAL */

.contador {
    padding: 7px 12px;
    background: var(--lavanda);
    color: var(--principal);
    border-radius: 8px;
    font-size: 11px;
    font-weight: 600;
    white-space: nowrap;
}

.filtros {
    display: grid;
    grid-template-columns: 2fr 1fr 1fr auto;
    align-items: end;
    gap: 16px;
    padding: 22px;
    border-bottom: 1px solid var(--borde);
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

.tabla-contenedor {
    width: 100%;
    overflow-x: auto;
}

.receta-ficha .tabla-contenedor {
    border: 1px solid var(--borde);
    border-radius: 9px;
    background: white;
}

table {
    width: 100%;
    border-collapse: collapse;
    text-align: left;
    font-size: 12px;
}

th {
    padding: 15px;
    background: #f4f0f9;
    color: #756587;
    font-size: 11px;
    font-weight: 650;
    white-space: nowrap;
    border-bottom: 1px solid var(--borde);
}

td {
    padding: 17px 15px;
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
    color: var(--texto);
    font-size: 12px;
    font-weight: 650;
}

td small {
    display: block;
    margin-top: 5px;
    font-size: 11px;
    line-height: 1.6;
    color: var(--secundario);
}

.historial-tabla table {
    min-width: 1000px;
}

.detalle-historial {
    margin-bottom: 8px;
}

.estado {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 5px 9px;
    border-radius: 6px;
    font-size: 10px;
    font-weight: 650;
    white-space: nowrap;
}

.acciones-tabla {
    display: flex;
    align-items: center;
    gap: 8px;
}

.boton-icono {
    display: grid;
    place-items: center;
    width: 34px;
    height: 34px;
    border-radius: 8px;
    text-decoration: none;
    transition: .2s;
}

.boton-icono svg {
    width: 17px;
    height: 17px;
}

.boton-icono:hover {
    transform: translateY(-2px);
    filter: brightness(.96);
}

/* ESTADO VACÍO */

.estado-vacio {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 55px 25px;
    text-align: center;
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
    margin: 0 0 8px;
    font-size: 17px;
    font-weight: 700;
}

.estado-vacio p {
    margin: 0 0 22px;
    font-size: 13px;
    color: var(--secundario);
}

/* DISEÑO RESPONSIVE */

@media (max-width: 1300px) {
    .indicadores {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .filtros {
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

    .modalidades {
        grid-template-columns: 1fr;
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

    .campos-dobles,
    .filtros {
        grid-template-columns: 1fr;
    }

    .formulario-cuerpo,
    .panel-encabezado,
    .formulario-pie {
        padding: 18px;
    }

    .paciente-ficha {
        flex-wrap: wrap;
    }

    .cuota-indicadores > div {
        padding: 12px;
    }
}

@media (max-width: 480px) {
    .indicadores,
    .cuota-indicadores {
        grid-template-columns: 1fr;
    }

    .formulario-pie .boton {
        flex: 1;
    }

    .banner .boton {
        width: 100%;
    }

    .titulo-bloque {
        align-items: flex-start;
    }

    .modalidad {
        padding: 13px;
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