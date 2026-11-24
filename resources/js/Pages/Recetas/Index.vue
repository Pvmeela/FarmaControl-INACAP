<script setup>
import { computed, ref, nextTick } from 'vue'
import { Head, Link, useForm, usePage } from '@inertiajs/vue3'
import FarmaLayout from '../../Layouts/FarmaLayout.vue'

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
const filtroVigencia = ref('todos')
const mostrarFormulario = ref(false)
const formularioRef = ref(null)

function obtenerFechaHoy() {
    const fecha = new Date()

    return [
        fecha.getFullYear(),
        String(fecha.getMonth() + 1).padStart(2, '0'),
        String(fecha.getDate()).padStart(2, '0'),
    ].join('-')
}

const hoy = obtenerFechaHoy()

function nuevoDetalle() {
    return {
        medicamento_id: '',
        cantidad_prescrita: 1,
        indicaciones: '',
    }
}

const form = useForm({
    codigo_receta: '',
    paciente_id: '',
    fecha_emision: hoy,
    fecha_vencimiento: '',
    profesional_prescriptor: '',
    rut_prescriptor: '',
    tipo_receta: 'simple',
    formato_receta: 'fisica',
    observaciones: '',
    detalles: [nuevoDetalle()],
})

// ==========================================
// MENSAJES
// ==========================================

const mensajeExito = computed(() =>
    page.props.flash?.success || ''
)

// ==========================================
// INFORMACIÓN DEL PACIENTE
// ==========================================

const pacienteSeleccionado = computed(() =>
    props.pacientes.find(
        paciente =>
            String(paciente.id) === String(form.paciente_id)
    ) || null
)

const estadoAlergias = computed(() =>
    pacienteSeleccionado.value?.estado_alergias
    || 'desconocido'
)

function nombrePaciente(paciente) {
    if (!paciente) return 'Paciente no disponible'

    return `${paciente.nombres} ${paciente.apellidos}`
}

function inicialesPaciente(paciente) {
    if (!paciente) return '?'

    return (
        (paciente.nombres?.charAt(0) || '') +
        (paciente.apellidos?.charAt(0) || '')
    ).toUpperCase()
}

function normalizarRut(valor) {
    return String(valor || '')
        .replace(/[.\-\s]/g, '')
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

function normalizarTexto(valor) {
    return String(valor ?? '')
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .toLowerCase()
        .trim()
}

// ==========================================
// INDICADORES
// ==========================================

const recetasVigentes = computed(() =>
    props.recetas.filter(
        receta => receta.vigencia_actual === 'vigente'
    ).length
)

const recetasVencidas = computed(() =>
    props.recetas.filter(
        receta => receta.vigencia_actual === 'vencida'
    ).length
)

const recetasUtilizadas = computed(() =>
    props.recetas.filter(
        receta => receta.vigencia_actual === 'utilizada'
    ).length
)

const indicadores = computed(() => [
    {
        titulo: 'Recetas registradas',
        valor: props.recetas.length,
        descripcion: 'Total de prescripciones',
        filtro: 'todos',
        color: 'principal',
        icono: 'documento',
    },
    {
        titulo: 'Recetas vigentes',
        valor: recetasVigentes.value,
        descripcion: 'Dentro de su vigencia',
        filtro: 'vigente',
        color: 'verde',
        icono: 'check',
    },
    {
        titulo: 'Recetas vencidas',
        valor: recetasVencidas.value,
        descripcion: 'Fuera de su vigencia',
        filtro: 'vencida',
        color: 'rojo',
        icono: 'reloj',
    },
    {
        titulo: 'Recetas utilizadas',
        valor: recetasUtilizadas.value,
        descripcion: 'Prescripciones completadas',
        filtro: 'utilizada',
        color: 'azul',
        icono: 'archivo',
    },
])

const iconos = {
    documento: 'M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z M14 2v6h6 M8 13h8 M8 17h8',
    check: 'M22 11.08V12a10 10 0 1 1-5.93-9.14 M22 4 12 14.01 9 11.01',
    reloj: 'M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20 M12 6v6l4 2',
    archivo: 'M4 7h16v14H4z M3 3h18v4H3z M10 11h4',
    buscar: 'M21 21l-4.35-4.35 M19 11a8 8 0 1 1-16 0 8 8 0 0 1 16 0',
    mas: 'M12 5v14 M5 12h14',
    cerrar: 'M18 6 6 18 M6 6l12 12',
    alerta: 'M10.3 3.9 2 18a2 2 0 0 0 1.7 3h16.6a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0 M12 9v4 M12 17h.01',
    ojo: 'M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7z M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0',
    descargar: 'M12 3v12 M7 10l5 5 5-5 M4 17v4h16v-4',
    usuario: 'M20 21v-2a7 7 0 0 0-7-7h-2a7 7 0 0 0-7 7v2 M16 6a4 4 0 1 1-8 0 4 4 0 0 1 8 0',
    flecha: 'M5 12h14 M12 5l7 7-7 7',
    escudo: 'M12 22s8-4 8-11V5l-8-3-8 3v6c0 7 8 11 8 11 M9 12l2 2 4-4',
}

function seleccionarFiltro(filtro) {
    filtroVigencia.value = filtro
    busqueda.value = ''
}

function limpiarFiltros() {
    filtroVigencia.value = 'todos'
    busqueda.value = ''
}

// ==========================================
// BÚSQUEDA Y FILTROS
// ==========================================

const recetasFiltradas = computed(() => {
    const termino = normalizarTexto(busqueda.value)
    const rutBusqueda = normalizarRut(busqueda.value)

    return props.recetas.filter(receta => {
        const paciente = receta.paciente

        const contenido = normalizarTexto([
            receta.codigo_receta,
            paciente?.codigo_paciente,
            paciente?.nombres,
            paciente?.apellidos,
            paciente?.rut,
            receta.profesional_prescriptor,
            ...(receta.detalles || []).map(
                detalle => detalle.medicamento?.nombre
            ),
        ].join(' '))

        const coincideBusqueda =
            !termino ||
            contenido.includes(termino) ||
            (
                rutBusqueda.length > 0 &&
                normalizarRut(paciente?.rut)
                    .includes(rutBusqueda)
            )

        const coincideVigencia =
            filtroVigencia.value === 'todos' ||
            receta.vigencia_actual === filtroVigencia.value

        return coincideBusqueda && coincideVigencia
    })
})

// ==========================================
// FORMULARIO
// ==========================================

async function alternarFormulario() {
    if (form.processing) return

    if (mostrarFormulario.value) {
        cancelarFormulario()
        return
    }

    form.clearErrors()
    mostrarFormulario.value = true

    await nextTick()

    formularioRef.value?.scrollIntoView({
        behavior: 'smooth',
        block: 'start',
    })
}

function reiniciarFormulario() {
    form.reset()
    form.clearErrors()
    form.fecha_emision = obtenerFechaHoy()
    form.detalles = [nuevoDetalle()]
}

function cancelarFormulario() {
    if (form.processing) return

    if (
        form.isDirty &&
        !window.confirm(
            '¿Deseas cancelar el registro? Los datos ingresados se perderán.'
        )
    ) {
        return
    }

    reiniciarFormulario()
    mostrarFormulario.value = false
}

function agregarMedicamento() {
    if (form.processing) return

    if (form.detalles.length >= props.medicamentos.length) {
        return
    }

    form.detalles.push(nuevoDetalle())
}

function quitarMedicamento(indice) {
    if (form.processing || form.detalles.length <= 1) {
        return
    }

    form.detalles.splice(indice, 1)

    Object.keys(form.errors)
        .filter(campo => campo.startsWith('detalles.'))
        .forEach(campo => form.clearErrors(campo))
}

function medicamentoSeleccionadoEnOtraFila(id, indice) {
    return form.detalles.some(
        (detalle, posicion) =>
            posicion !== indice &&
            String(detalle.medicamento_id) === String(id)
    )
}
function rutValido(valor) {
    const rut = normalizarRut(valor)

    if (!/^[0-9]{1,8}[0-9K]$/.test(rut)) {
        return false
    }

    const cuerpo = rut.slice(0, -1)
    const dv = rut.slice(-1)

    if (Number(cuerpo) === 0) return false

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

    return dv === esperado
}

function textoTipoReceta(tipo) {
    return {
        simple: 'Receta simple',
        retenida: 'Receta retenida',
        cheque: 'Receta cheque',
    }[tipo] || 'Sin clasificación registrada'
}

function textoFormatoReceta(formato) {
    return {
        fisica: 'Física',
        digital: 'Digital',
    }[formato] || 'Sin formato registrado'
}

function obtenerMedicamento(id) {
    return props.medicamentos.find(
        medicamento =>
            String(medicamento.id) === String(id)
    ) || null
}

function medicamentoCompatible(id) {
    const medicamento = obtenerMedicamento(id)

    if (!medicamento) return false

    if (
        !medicamento.requiere_receta &&
        !medicamento.es_controlado
    ) {
        return true
    }

    return medicamento.tipo_receta === form.tipo_receta
}

const medicamentosIncompatibles = computed(() =>
    form.detalles.filter(detalle =>
        detalle.medicamento_id &&
        !medicamentoCompatible(detalle.medicamento_id)
    )
)

const contieneControlados = computed(() =>
    form.detalles.some(detalle =>
        obtenerMedicamento(detalle.medicamento_id)?.es_controlado
    )
)

const puedeRegistrar = computed(() => {
    if (form.processing) return false

    if (
        !form.codigo_receta.trim() ||
        !form.paciente_id ||
        !form.fecha_emision ||
        !form.fecha_vencimiento ||
        !form.profesional_prescriptor.trim() ||
        !form.tipo_receta ||
        !form.formato_receta ||
        !rutValido(form.rut_prescriptor)
    ) {
        return false
    }

    if (
        form.fecha_emision > hoy ||
        form.fecha_vencimiento < hoy ||
        form.fecha_vencimiento < form.fecha_emision
    ) {
        return false
    }

    if (!form.detalles.length) return false

    if (medicamentosIncompatibles.value.length > 0) {
        return false
    }

    const ids = form.detalles.map(
        detalle => String(detalle.medicamento_id)
    )

    if (new Set(ids).size !== ids.length) {
        return false
    }

    return form.detalles.every(detalle =>
        detalle.medicamento_id &&
        Number.isInteger(Number(detalle.cantidad_prescrita)) &&
        Number(detalle.cantidad_prescrita) >= 1
    )
})

function registrarReceta() {
    if (!puedeRegistrar.value) return

    form.post('/recetas', {
        preserveScroll: true,

        onSuccess: () => {
            reiniciarFormulario()
            mostrarFormulario.value = false
            limpiarFiltros()
        },
    })
}

// ==========================================
// FORMATOS Y ESTADOS
// ==========================================

function formatearFecha(valor) {
    if (!valor) return '—'

    const partes = String(valor)
        .slice(0, 10)
        .split('-')

    if (partes.length !== 3) return String(valor)

    return `${partes[2]}/${partes[1]}/${partes[0]}`
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
        vigente: 'estado-verde',
        vencida: 'estado-rojo',
        utilizada: 'estado-azul',
        anulada: 'estado-gris',
        no_vigente: 'estado-amarillo',
    }

    return clases[vigencia] || 'estado-gris'
}

function etiquetaAlergias(paciente) {
    if (paciente?.estado_alergias === 'con_alergias') {
        return 'Con alergias'
    }

    if (paciente?.estado_alergias === 'sin_alergias') {
        return 'Sin alergias conocidas'
    }

    return 'Alergias desconocidas'
}

function claseAlergias(paciente) {
    if (paciente?.estado_alergias === 'con_alergias') {
        return 'estado-rojo'
    }

    if (paciente?.estado_alergias === 'sin_alergias') {
        return 'estado-verde'
    }

    return 'estado-amarillo'
}
</script>

<template>
    <Head title="Recetas médicas | FarmaControl" />

    <FarmaLayout
        titulo="Recetas médicas"
        subtitulo="Registra, consulta y genera prescripciones farmacéuticas simuladas."
    >

        <template #acciones>
            <button
                type="button"
                class="boton-primario"
                :disabled="form.processing"
                @click="alternarFormulario"
            >
                <svg
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    stroke-linecap="round"
                >
                    <path
                        :d="mostrarFormulario
                            ? iconos.cerrar
                            : iconos.mas"
                    />
                </svg>

                {{ mostrarFormulario
                    ? 'Cerrar formulario'
                    : 'Nueva receta' }}
            </button>
        </template>

        <div class="modulo-recetas">

            <!-- MENSAJE DE ÉXITO -->

            <div
                v-if="mensajeExito"
                class="mensaje-exito"
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

                {{ mensajeExito }}
            </div>

            <!-- INDICADORES INTERACTIVOS -->

            <section class="indicadores">
                <button
                    v-for="indicador in indicadores"
                    :key="indicador.filtro"
                    type="button"
                    class="tarjeta-indicador"
                    :class="{
                        'indicador-activo':
                            filtroVigencia === indicador.filtro
                    }"
                    :aria-pressed="
                        filtroVigencia === indicador.filtro
                    "
                    @click="seleccionarFiltro(indicador.filtro)"
                >
                    <div class="indicador-superior">
                        <span>{{ indicador.titulo }}</span>

                        <div
                            class="icono-indicador"
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
                    </div>

                    <strong>{{ indicador.valor }}</strong>

                    <small>{{ indicador.descripcion }}</small>
                </button>
            </section>

            <!-- BANNER INFORMATIVO -->

            <section class="banner-recetas">
                <div class="banner-icono">
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

                <div class="banner-contenido">
                    <span>GESTIÓN FARMACÉUTICA</span>

                    <h2>Prescripciones seguras y organizadas</h2>

                    <p>
                        Consulta los antecedentes de alergias,
                        registra recetas ficticias y genera
                        documentos PDF de uso académico.
                    </p>
                </div>

                <div class="banner-decoracion" aria-hidden="true">
                    <svg
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width=".7"
                    >
                        <path :d="iconos.documento" />
                    </svg>
                </div>
            </section>

            <!-- FORMULARIO DE REGISTRO -->

            <section
                v-if="mostrarFormulario"
                ref="formularioRef"
                class="tarjeta formulario-tarjeta"
            >
                <div class="encabezado-tarjeta">
                    <div class="titulo-con-icono">
                        <div class="icono-seccion">
                            <svg
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                            >
                                <path :d="iconos.documento" />
                            </svg>
                        </div>

                        <div>
                            <h2>Registrar receta simulada</h2>

                            <p>
                                Completa los datos de la prescripción.
                            </p>
                        </div>
                    </div>

                    <button
                        type="button"
                        class="boton-icono"
                        aria-label="Cerrar formulario"
                        :disabled="form.processing"
                        @click="cancelarFormulario"
                    >
                        <svg
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                        >
                            <path :d="iconos.cerrar" />
                        </svg>
                    </button>
                </div>

                <form
                    class="formulario"
                    @submit.prevent="registrarReceta"
                >
                    <fieldset :disabled="form.processing">

                        <!-- DATOS GENERALES -->

                        <div class="subtitulo-formulario">
                            <span class="numero-paso">1</span>

                            <div>
                                <h3>Información general</h3>
                                <p>
                                    Identificación del paciente y
                                    datos de la receta.
                                </p>
                            </div>
                        </div>

                        <div class="grilla-formulario">

                            <div class="campo">
                                <label for="codigo_receta">
                                    Código de receta *
                                </label>

                                <input
                                    id="codigo_receta"
                                    v-model="form.codigo_receta"
                                    type="text"
                                    maxlength="30"
                                    placeholder="Ej.: REC-001"
                                    required
                                    :class="{
                                        'campo-error':
                                            form.errors.codigo_receta
                                    }"
                                />

                                <p
                                    v-if="form.errors.codigo_receta"
                                    class="error"
                                >
                                    {{ form.errors.codigo_receta }}
                                </p>
                            </div>

                            <div class="campo">
                                <label for="paciente_id">
                                    Paciente *
                                </label>

                                <select
                                    id="paciente_id"
                                    v-model="form.paciente_id"
                                    required
                                >
                                    <option value="">
                                        Seleccionar paciente
                                    </option>

                                    <option
                                        v-for="paciente in pacientes"
                                        :key="paciente.id"
                                        :value="paciente.id"
                                    >
                                        {{ paciente.codigo_paciente }}
                                        —
                                        {{ nombrePaciente(paciente) }}
                                        —
                                        {{ formatearRut(paciente.rut) }}
                                    </option>
                                </select>

                                <p
                                    v-if="form.errors.paciente_id"
                                    class="error"
                                >
                                    {{ form.errors.paciente_id }}
                                </p>
                            </div>

                            <!-- INFORMACIÓN DEL PACIENTE -->

                            <div
                                v-if="pacienteSeleccionado"
                                class="ancho-completo paciente-panel"
                            >
                                <div class="paciente-encabezado">
                                    <div class="avatar avatar-grande">
                                        {{
                                            inicialesPaciente(
                                                pacienteSeleccionado
                                            )
                                        }}
                                    </div>

                                    <div>
                                        <small>Paciente seleccionado</small>

                                        <h3>
                                            {{
                                                nombrePaciente(
                                                    pacienteSeleccionado
                                                )
                                            }}
                                        </h3>
                                    </div>
                                </div>

                                <div class="datos-paciente">
                                    <div>
                                        <small>RUT</small>

                                        <strong>
                                            {{
                                                formatearRut(
                                                    pacienteSeleccionado.rut
                                                )
                                            }}
                                        </strong>
                                    </div>

                                    <div>
                                        <small>Código del paciente</small>

                                        <strong>
                                            {{
                                                pacienteSeleccionado
                                                    .codigo_paciente
                                            }}
                                        </strong>
                                    </div>

                                    <div>
                                        <small>Fecha de nacimiento</small>

                                        <strong>
                                            {{
                                                formatearFecha(
                                                    pacienteSeleccionado
                                                        .fecha_nacimiento
                                                )
                                            }}
                                        </strong>
                                    </div>
                                </div>

                                <!-- ALERTA DE ALERGIAS -->

                                <div
                                    v-if="estadoAlergias === 'con_alergias'"
                                    class="alerta alergia-roja"
                                    role="alert"
                                >
                                    <svg
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    >
                                        <path :d="iconos.alerta" />
                                    </svg>

                                    <div>
                                        <h4>
                                            Paciente con alergias conocidas
                                        </h4>

                                        <p class="texto-alergias">
                                            {{
                                                pacienteSeleccionado.alergias
                                                || 'Descripción no registrada.'
                                            }}
                                        </p>

                                        <small>
                                            Revisa estos antecedentes con
                                            el profesional responsable
                                            antes de continuar.
                                        </small>
                                    </div>
                                </div>

                                <div
                                    v-else-if="
                                        estadoAlergias === 'sin_alergias'
                                    "
                                    class="alerta alergia-verde"
                                >
                                    <svg
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                    >
                                        <path :d="iconos.check" />
                                    </svg>

                                    <div>
                                        <h4>
                                            Sin alergias conocidas registradas
                                        </h4>

                                        <p>
                                            Esto no garantiza la ausencia
                                            de alergias.
                                        </p>
                                    </div>
                                </div>

                                <div
                                    v-else
                                    class="alerta alergia-amarilla"
                                >
                                    <svg
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                    >
                                        <path :d="iconos.alerta" />
                                    </svg>

                                    <div>
                                        <h4>
                                            Estado de alergias desconocido
                                        </h4>

                                        <p>
                                            No existe información suficiente
                                            sobre los antecedentes de alergias.
                                        </p>
                                    </div>
                                </div>

                                <p class="nota-clinica">
                                    FarmaControl muestra los antecedentes
                                    registrados y no realiza evaluaciones
                                    automáticas de compatibilidad clínica.
                                </p>
                            </div>

                            <!-- FECHAS -->

                            <div class="campo">
                                <label for="fecha_emision">
                                    Fecha de emisión *
                                </label>

                                <input
                                    id="fecha_emision"
                                    v-model="form.fecha_emision"
                                    type="date"
                                    :max="hoy"
                                    required
                                />

                                <p
                                    v-if="form.errors.fecha_emision"
                                    class="error"
                                >
                                    {{ form.errors.fecha_emision }}
                                </p>
                            </div>

                            <div class="campo">
                                <label for="fecha_vencimiento">
                                    Fecha de vencimiento *
                                </label>

                                <input
                                    id="fecha_vencimiento"
                                    v-model="form.fecha_vencimiento"
                                    type="date"
                                    :min="hoy"
                                    required
                                />

                                <p
                                    v-if="form.errors.fecha_vencimiento"
                                    class="error"
                                >
                                    {{ form.errors.fecha_vencimiento }}
                                </p>
                            </div>

                            <!-- PROFESIONAL -->

                            <div class="campo ancho-completo">
                                <label for="profesional_prescriptor">
                                    Profesional prescriptor simulado *
                                </label>

                                <input
                                    id="profesional_prescriptor"
                                    v-model="form.profesional_prescriptor"
                                    type="text"
                                    maxlength="150"
                                    placeholder="Ej.: Dra. Andrea López (ficticia)"
                                    required
                                />

                                <p
                                    v-if="form.errors.profesional_prescriptor"
                                    class="error"
                                >
                                    {{
                                        form.errors.profesional_prescriptor
                                    }}
                                </p>
                            </div>
<!-- CLASIFICACIÓN DE LA RECETA -->

<div class="campo">
    <label for="tipo_receta">
        Tipo de receta *
    </label>

    <select
        id="tipo_receta"
        v-model="form.tipo_receta"
        required
    >
        <option value="simple">
            Receta simple
        </option>

        <option value="retenida">
            Receta retenida
        </option>

        <option value="cheque">
            Receta cheque
        </option>
    </select>

    <p v-if="form.errors.tipo_receta" class="error">
        {{ form.errors.tipo_receta }}
    </p>
</div>

<div class="campo">
    <label for="formato_receta">
        Formato de receta *
    </label>

    <select
        id="formato_receta"
        v-model="form.formato_receta"
        required
    >
        <option value="fisica">
            Receta física
        </option>

        <option value="digital">
            Receta digital
        </option>
    </select>

    <p v-if="form.errors.formato_receta" class="error">
        {{ form.errors.formato_receta }}
    </p>
</div>

<div class="campo ancho-completo">
    <label for="rut_prescriptor">
        RUT del profesional prescriptor simulado *
    </label>

    <input
        id="rut_prescriptor"
        v-model="form.rut_prescriptor"
        type="text"
        maxlength="12"
        placeholder="Ej.: 12.345.678-5"
        required
        @blur="
            rutValido(form.rut_prescriptor)
                && (form.rut_prescriptor =
                    formatearRut(form.rut_prescriptor))
        "
    />

    <p
        v-if="form.errors.rut_prescriptor"
        class="error"
    >
        {{ form.errors.rut_prescriptor }}
    </p>

    <p
        v-else-if="
            form.rut_prescriptor &&
            !rutValido(form.rut_prescriptor)
        "
        class="error"
    >
        El RUT ingresado no es válido.
    </p>

    <small
        v-else-if="rutValido(form.rut_prescriptor)"
        class="rut-correcto"
    >
        RUT válido
    </small>
</div>

<div
    v-if="form.tipo_receta !== 'simple'"
    class="ancho-completo alerta alergia-amarilla"
>
    <svg
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        stroke-width="1.8"
    >
        <path :d="iconos.escudo" />
    </svg>

    <div>
        <h4>Prescripción con control especial</h4>

        <p>
            Esta receta requiere controles adicionales
            durante la entrega de medicamentos.
            Su registro no implica que haya sido retenida.
        </p>
    </div>
</div>
                            <!-- OBSERVACIONES -->

                            <div class="campo ancho-completo">
                                <label for="observaciones">
                                    Observaciones
                                    <span class="opcional">
                                        (opcional)
                                    </span>
                                </label>

                                <textarea
                                    id="observaciones"
                                    v-model="form.observaciones"
                                    rows="3"
                                    maxlength="255"
                                    placeholder="Agrega observaciones académicas..."
                                ></textarea>

                                <div class="contador">
                                    {{ form.observaciones.length }}/255
                                </div>

                                <p
                                    v-if="form.errors.observaciones"
                                    class="error"
                                >
                                    {{ form.errors.observaciones }}
                                </p>
                            </div>
                        </div>

                        <!-- MEDICAMENTOS -->

                        <div class="seccion-medicamentos">

                            <div class="medicamentos-encabezado">
                                <div class="subtitulo-formulario">
                                    <span class="numero-paso">2</span>

                                    <div>
                                        <h3>Medicamentos prescritos</h3>

                                        <p>
                                            Agrega los medicamentos
                                            correspondientes a la receta.
                                        </p>
                                    </div>
                                </div>

                                <button
                                    type="button"
                                    class="boton-secundario"
                                    :disabled="
                                        form.processing ||
                                        form.detalles.length >=
                                            medicamentos.length
                                    "
                                    @click="agregarMedicamento"
                                >
                                    <svg
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                    >
                                        <path :d="iconos.mas" />
                                    </svg>

                                    Agregar medicamento
                                </button>
                            </div>

                            <p
                                v-if="form.errors.detalles"
                                class="error"
                            >
                                {{ form.errors.detalles }}
                            </p>

                            <div
                                v-for="(detalle, indice) in form.detalles"
                                :key="indice"
                                class="medicamento-tarjeta"
                            >
                                <div class="medicamento-encabezado">
                                    <span class="medicamento-numero">
                                        Medicamento {{ indice + 1 }}
                                    </span>

                                    <button
                                        v-if="form.detalles.length > 1"
                                        type="button"
                                        class="boton-quitar"
                                        @click="quitarMedicamento(indice)"
                                    >
                                        <svg
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="2"
                                        >
                                            <path :d="iconos.cerrar" />
                                        </svg>

                                        Quitar
                                    </button>
                                </div>

                                <div class="grilla-medicamento">

                                    <div class="campo">
                                        <label
                                            :for="`medicamento-${indice}`"
                                        >
                                            Medicamento *
                                        </label>

                                        <select
                                            :id="`medicamento-${indice}`"
                                            v-model="detalle.medicamento_id"
                                            required
                                        >
                                            <option value="">
                                                Seleccionar medicamento
                                            </option>

                                            <option
                                                v-for="medicamento in medicamentos"
                                                :key="medicamento.id"
                                                :value="medicamento.id"
                                                :disabled="
                                                    medicamentoSeleccionadoEnOtraFila(
                                                        medicamento.id,
                                                        indice
                                                    )
                                                "
                                            >
                                                {{ medicamento.nombre }}
                                                —
                                                {{ medicamento.concentracion }}

                                                {{ medicamento.es_controlado
    ? ' — CONTROLADO'
    : '' }}

{{ medicamento.requiere_receta
    ? ' — ' + textoTipoReceta(medicamento.tipo_receta)
    : '' }}
                                            </option>
                                        </select>

                                        <p
                                            v-if="form.errors[
                                                `detalles.${indice}.medicamento_id`
                                            ]"
                                            class="error"
                                        >
                                            {{
                                                form.errors[
                                                    `detalles.${indice}.medicamento_id`
                                                ]
                                            }}
                                        </p>
                                    </div>

                                    <div class="campo">
                                        <label
                                            :for="`cantidad-${indice}`"
                                        >
                                            Cantidad prescrita *
                                        </label>

                                        <input
                                            :id="`cantidad-${indice}`"
                                            v-model.number="
                                                detalle.cantidad_prescrita
                                            "
                                            type="number"
                                            min="1"
                                            step="1"
                                            required
                                        />

                                        <p
                                            v-if="form.errors[
                                                `detalles.${indice}.cantidad_prescrita`
                                            ]"
                                            class="error"
                                        >
                                            {{
                                                form.errors[
                                                    `detalles.${indice}.cantidad_prescrita`
                                                ]
                                            }}
                                        </p>
                                    </div>

                                    <div class="campo">
                                        <label
                                            :for="`indicaciones-${indice}`"
                                        >
                                            Indicaciones
                                            <span class="opcional">
                                                (opcional)
                                            </span>
                                        </label>

                                        <input
                                            :id="`indicaciones-${indice}`"
                                            v-model="detalle.indicaciones"
                                            type="text"
                                            maxlength="255"
                                            placeholder="Indicaciones ficticias"
                                        />

                                        <p
                                            v-if="form.errors[
                                                `detalles.${indice}.indicaciones`
                                            ]"
                                            class="error"
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

                            <div class="resumen-medicamentos">
                                <span>
                                    Medicamentos incluidos
                                </span>

                                <strong>
                                    {{ form.detalles.length }}
                                </strong>
                            </div>
                        </div>
<!-- VALIDACIÓN FARMACÉUTICA -->

<div
    v-if="medicamentosIncompatibles.length"
    class="alerta alergia-roja"
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

    <div>
        <h4>Tipo de receta incompatible</h4>

        <p>
            Los siguientes medicamentos requieren
            otro tipo de receta:
        </p>

        <p
            v-for="detalle in medicamentosIncompatibles"
            :key="detalle.medicamento_id"
        >
            <strong>
                {{
                    obtenerMedicamento(
                        detalle.medicamento_id
                    )?.nombre
                }}
            </strong>

            —

            {{
                textoTipoReceta(
                    obtenerMedicamento(
                        detalle.medicamento_id
                    )?.tipo_receta
                )
            }}
        </p>

        <small>
            Corrige el tipo de receta o los medicamentos
            seleccionados para continuar.
        </small>
    </div>
</div>

<div
    v-else-if="contieneControlados"
    class="alerta alergia-amarilla"
>
    <svg
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        stroke-width="1.8"
    >
        <path :d="iconos.escudo" />
    </svg>

    <div>
        <h4>Medicamentos sujetos a control</h4>

        <p>
            Esta prescripción contiene medicamentos
            controlados. Su entrega estará sujeta
            a las validaciones correspondientes.
        </p>
    </div>
</div>

                        <!-- AVISO ACADÉMICO -->

                        <div class="aviso-academico">
                            <svg
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.7"
                            >
                                <path :d="iconos.escudo" />
                            </svg>

                            <div>
                                <strong>Uso exclusivamente académico</strong>

                                <p>
                                    Utiliza únicamente datos ficticios.
                                    Las recetas generadas no tienen
                                    validez legal ni clínica.
                                </p>
                            </div>
                        </div>

                        <!-- BOTONES DEL FORMULARIO -->

                        <div class="acciones-formulario">
                            <button
                                type="button"
                                class="boton-cancelar"
                                @click="cancelarFormulario"
                            >
                                Cancelar
                            </button>

                            <button
                                type="submit"
                                class="boton-primario"
                                :disabled="!puedeRegistrar"
                            >
                                <svg
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                >
                                    <path :d="iconos.check" />
                                </svg>

                                {{ form.processing
                                    ? 'Registrando...'
                                    : 'Registrar receta' }}
                            </button>
                        </div>

                    </fieldset>
                </form>
            </section>

            <!-- HISTORIAL DE RECETAS -->

            <section class="tarjeta historial">

                <div class="encabezado-tarjeta">
                    <div class="titulo-con-icono">
                        <div class="icono-seccion">
                            <svg
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                            >
                                <path :d="iconos.archivo" />
                            </svg>
                        </div>

                        <div>
                            <h2>Historial de recetas</h2>

                            <p>
                                Consulta las prescripciones
                                y sus documentos asociados.
                            </p>
                        </div>
                    </div>

                    <span class="contador-registros">
                        {{ recetas.length }} registros
                    </span>
                </div>

                <!-- BUSCADOR Y FILTROS -->

                <div class="filtros">

                    <div class="buscador">
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
                            v-model="busqueda"
                            type="search"
                            aria-label="Buscar recetas"
                            placeholder="Buscar receta, paciente, RUT o medicamento..."
                        />
                    </div>

                    <select
                        v-model="filtroVigencia"
                        class="selector-filtro"
                        aria-label="Filtrar por vigencia"
                    >
                        <option value="todos">
                            Todos los estados
                        </option>

                        <option value="vigente">Vigentes</option>
                        <option value="vencida">Vencidas</option>
                        <option value="utilizada">Utilizadas</option>
                        <option value="anulada">Anuladas</option>
                        <option value="no_vigente">No vigentes</option>
                    </select>

                    <button
                        v-if="
                            busqueda ||
                            filtroVigencia !== 'todos'
                        "
                        type="button"
                        class="boton-limpiar"
                        @click="limpiarFiltros"
                    >
                        Limpiar filtros
                    </button>

                </div>

                <!-- TABLA -->

                <div class="tabla-contenedor">

                    <table class="tabla-recetas">

                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Paciente</th>
                                <th>RUT</th>
                                <th>Emisión</th>
                                <th>Vencimiento</th>
                                <th>Medicamentos</th>
                                <th>Clasificación</th>
                                <th>Vigencia</th>
                                <th>Documentos</th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr
                                v-for="receta in recetasFiltradas"
                                :key="receta.id"
                            >
                                <!-- CÓDIGO -->

                                <td>
                                    <span class="codigo-receta">
                                        {{ receta.codigo_receta }}
                                    </span>

                                    <small class="texto-secundario">
                                        {{
                                            receta.profesional_prescriptor
                                            || 'Sin profesional'
                                        }}
                                    </small>
                                </td>

                                <!-- PACIENTE -->

                                <td>
                                    <div class="paciente-tabla">
                                        <div class="avatar">
                                            {{
                                                inicialesPaciente(
                                                    receta.paciente
                                                )
                                            }}
                                        </div>

                                        <div>
                                            <strong>
                                                {{
                                                    nombrePaciente(
                                                        receta.paciente
                                                    )
                                                }}
                                            </strong>

                                            <small class="texto-secundario">
                                                {{
                                                    receta.paciente
                                                        ?.codigo_paciente
                                                    || '—'
                                                }}
                                            </small>
                                        </div>
                                    </div>

                                    <span
                                        class="estado estado-alergia"
                                        :class="
                                            claseAlergias(receta.paciente)
                                        "
                                    >
                                        {{
                                            etiquetaAlergias(
                                                receta.paciente
                                            )
                                        }}
                                    </span>
                                </td>

                                <!-- RUT -->

                                <td class="sin-salto">
                                    {{
                                        formatearRut(
                                            receta.paciente?.rut
                                        )
                                    }}
                                </td>

                                <!-- FECHAS -->

                                <td class="sin-salto">
                                    {{
                                        formatearFecha(
                                            receta.fecha_emision
                                        )
                                    }}
                                </td>

                                <td class="sin-salto">
                                    {{
                                        formatearFecha(
                                            receta.fecha_vencimiento
                                        )
                                    }}
                                </td>

                                <!-- MEDICAMENTOS -->

                                <td>
                                    <div
                                        v-for="detalle in receta.detalles || []"
                                        :key="detalle.id"
                                        class="medicamento-tabla"
                                    >
                                        <strong>
                                            {{
                                                detalle.medicamento?.nombre
                                                || 'Medicamento no disponible'
                                            }}
                                        </strong>

                                        <small class="texto-secundario">
                                            Cantidad:
                                            {{ detalle.cantidad_prescrita }}
                                        </small>

                                        <small
                                            v-if="detalle.indicaciones"
                                            class="indicaciones"
                                        >
                                            {{ detalle.indicaciones }}
                                        </small>
                                    </div>
                                </td>
                                <!-- CLASIFICACIÓN Y RETENCIÓN -->

<td>
    <span
        class="estado"
        :class="
            receta.tipo_receta === 'retenida' ||
            receta.tipo_receta === 'cheque'
                ? 'estado-amarillo'
                : 'estado-gris'
        "
    >
        {{ textoTipoReceta(receta.tipo_receta) }}
    </span>

    <small class="texto-secundario">
        Formato:
        {{ textoFormatoReceta(receta.formato_receta) }}
    </small>

    <small class="texto-secundario">
        RUT prescriptor:
        {{ formatearRut(receta.rut_prescriptor) }}
    </small>

    <div class="retencion-receta">

        <span
            v-if="receta.fecha_retencion"
            class="estado estado-verde"
        >
            Retenida
        </span>

        <span
            v-else-if="
                receta.tipo_receta === 'retenida' ||
                receta.tipo_receta === 'cheque'
            "
            class="estado estado-amarillo"
        >
            Pendiente de retención
        </span>

        <span
            v-else-if="receta.tipo_receta === 'simple'"
            class="estado estado-gris"
        >
            No aplica retención
        </span>

        <span
            v-else
            class="estado estado-gris"
        >
            Sin registro histórico
        </span>

        <small
            v-if="receta.fecha_retencion"
            class="texto-secundario"
        >
            {{ formatearFecha(receta.fecha_retencion) }}
        </small>

    </div>
</td>

                                <!-- VIGENCIA -->

                                <td>
                                    <span
                                        class="estado"
                                        :class="
                                            claseVigencia(
                                                receta.vigencia_actual
                                            )
                                        "
                                    >
                                        <span class="punto-estado"></span>

                                        {{
                                            etiquetaVigencia(
                                                receta.vigencia_actual
                                            )
                                        }}
                                    </span>
                                </td>

                                <!-- DOCUMENTOS PDF -->

                                <td>
                                    <div class="acciones-documentos">

                                        <a
                                            :href="`/recetas/${receta.id}/pdf`"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="boton-pdf"
                                        >
                                            <svg
                                                viewBox="0 0 24 24"
                                                fill="none"
                                                stroke="currentColor"
                                                stroke-width="1.8"
                                            >
                                                <path :d="iconos.ojo" />
                                            </svg>

                                            Ver PDF
                                        </a>

                                        <a
                                            :href="`/recetas/${receta.id}/pdf/descargar`"
                                            class="boton-descargar"
                                        >
                                            <svg
                                                viewBox="0 0 24 24"
                                                fill="none"
                                                stroke="currentColor"
                                                stroke-width="1.8"
                                            >
                                                <path :d="iconos.descargar" />
                                            </svg>

                                            Descargar
                                        </a>

                                    </div>
                                </td>
                            </tr>

                            <!-- SIN RESULTADOS -->

                            <tr v-if="recetasFiltradas.length === 0">
                                <td colspan="9">
                                    <div class="estado-vacio">
                                        <div class="icono-vacio">
                                            <svg
                                                viewBox="0 0 24 24"
                                                fill="none"
                                                stroke="currentColor"
                                                stroke-width="1.5"
                                            >
                                                <path :d="iconos.documento" />
                                            </svg>
                                        </div>

                                        <h3>
                                            {{
                                                recetas.length
                                                    ? 'No se encontraron resultados'
                                                    : 'Aún no hay recetas registradas'
                                            }}
                                        </h3>

                                        <p>
                                            {{
                                                recetas.length
                                                    ? 'Intenta modificar los filtros de búsqueda.'
                                                    : 'Registra una receta simulada para comenzar.'
                                            }}
                                        </p>

                                        <button
                                            v-if="recetas.length"
                                            type="button"
                                            class="boton-secundario"
                                            @click="limpiarFiltros"
                                        >
                                            Limpiar filtros
                                        </button>

                                        <button
                                            v-else-if="!mostrarFormulario"
                                            type="button"
                                            class="boton-secundario"
                                            @click="alternarFormulario"
                                        >
                                            Nueva receta
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
                        Mostrando {{ recetasFiltradas.length }}
                        de {{ recetas.length }} recetas
                    </span>

                    <span>
                        Documentos de simulación académica
                    </span>
                </div>

            </section>

            <!-- INFORMACIÓN INFERIOR -->

            <div class="nota-final">
                <svg
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.7"
                >
                    <path :d="iconos.escudo" />
                </svg>

                <p>
                    Los documentos generados por FarmaControl
                    son exclusivamente de uso académico.
                    Los antecedentes de alergias mostrados
                    corresponden a la información actual
                    registrada del paciente.
                </p>
            </div>

        </div>

    </FarmaLayout>
</template>

<style scoped>

/* ==========================================
   CONFIGURACIÓN GENERAL
   ========================================== */

.modulo-recetas {
    display: flex;
    flex-direction: column;
    gap: 24px;
    min-width: 0;
    color: var(--fc-text, #1E293B);
}

.modulo-recetas *,
.boton-primario {
    box-sizing: border-box;
}

button,
a,
input,
select,
textarea {
    -webkit-tap-highlight-color: transparent;
}

button:disabled {
    cursor: not-allowed;
    opacity: .5;
}

button:focus-visible,
a:focus-visible {
    outline: 3px solid var(--fc-accent);
    outline-offset: 3px;
}

/* ==========================================
   BOTONES
   ========================================== */

.boton-primario,
.boton-secundario,
.boton-cancelar {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    min-height: 42px;
    padding: 11px 18px;
    border-radius: 9px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: .2s ease;
}

.boton-primario {
    border: 1px solid transparent;
    background: var(--fc-primary, #172554);
    color: #FFFFFF;
}

.boton-primario:hover:not(:disabled) {
    background: var(--fc-hover);
    box-shadow: 0 4px 12px rgba(15, 23, 42, .12);
}

.boton-secundario {
    border: 1px solid var(--fc-border);
    background: var(--fc-light);
    color: var(--fc-primary);
}

.boton-secundario:hover:not(:disabled) {
    border-color: var(--fc-accent);
}

.boton-cancelar {
    border: 1px solid #CBD5E1;
    background: #FFFFFF;
    color: #475569;
}

.boton-cancelar:hover {
    background: #F8FAFC;
}

.boton-primario svg,
.boton-secundario svg {
    width: 17px;
    height: 17px;
    flex-shrink: 0;
}

.boton-icono {
    display: grid;
    place-items: center;
    width: 34px;
    height: 34px;
    border: 1px solid #E2E8F0;
    border-radius: 8px;
    background: #FFFFFF;
    color: #64748B;
    cursor: pointer;
}

.boton-icono:hover {
    background: #F1F5F9;
}

.boton-icono svg {
    width: 17px;
    height: 17px;
}

/* ==========================================
   MENSAJE DE ÉXITO
   ========================================== */

.mensaje-exito {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 15px 18px;
    border: 1px solid #BBF7D0;
    border-radius: 12px;
    background: #F0FDF4;
    color: #166534;
    font-size: 13px;
    font-weight: 600;
}

.mensaje-exito svg {
    width: 21px;
    height: 21px;
    flex-shrink: 0;
}

/* ==========================================
   INDICADORES
   ========================================== */

.indicadores {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 16px;
}

.tarjeta-indicador {
    display: flex;
    flex-direction: column;
    gap: 8px;
    min-width: 0;
    padding: 20px;
    border: 1px solid #E2E8F0;
    border-radius: 14px;
    background: #FFFFFF;
    text-align: left;
    cursor: pointer;
    box-shadow: 0 3px 12px rgba(15, 23, 42, .025);
    transition: .2s ease;
}

.tarjeta-indicador:hover {
    transform: translateY(-3px);
    border-color: var(--fc-accent);
    box-shadow: 0 7px 20px rgba(15, 23, 42, .07);
}

.indicador-activo {
    border-color: var(--fc-accent);
    box-shadow: 0 0 0 2px var(--fc-light);
}

.indicador-superior {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
}

.indicador-superior > span {
    font-size: 12px;
    font-weight: 600;
    color: #64748B;
}

.icono-indicador {
    display: grid;
    place-items: center;
    width: 40px;
    height: 40px;
    flex-shrink: 0;
    border-radius: 11px;
}

.icono-indicador svg {
    width: 21px;
    height: 21px;
}

.principal {
    background: var(--fc-light);
    color: var(--fc-primary);
}

.verde {
    background: #DCFCE7;
    color: #15803D;
}

.rojo {
    background: #FEE2E2;
    color: #B91C1C;
}

.azul {
    background: #DBEAFE;
    color: #1D4ED8;
}

.tarjeta-indicador strong {
    font-size: 32px;
    line-height: 1.2;
    font-weight: 750;
    color: var(--fc-primary);
    letter-spacing: -1px;
}

.tarjeta-indicador small {
    font-size: 11px;
    color: #64748B;
}

/* ==========================================
   BANNER
   ========================================== */

.banner-recetas {
    position: relative;
    isolation: isolate;
    display: flex;
    align-items: center;
    gap: 20px;
    min-height: 145px;
    padding: 26px 30px;
    border-radius: 16px;
    overflow: hidden;
    background: linear-gradient(
        115deg,
        var(--fc-primary),
        var(--fc-accent)
    );
    color: #FFFFFF;
}

.banner-icono {
    display: grid;
    place-items: center;
    width: 66px;
    height: 66px;
    flex-shrink: 0;
    border: 1px solid rgba(255, 255, 255, .2);
    border-radius: 18px;
    background: rgba(255, 255, 255, .12);
}

.banner-icono svg {
    width: 34px;
    height: 34px;
}

.banner-contenido {
    position: relative;
    z-index: 1;
}

.banner-contenido > span {
    font-size: 10px;
    font-weight: 600;
    letter-spacing: 1.5px;
    color: rgba(255, 255, 255, .8);
}

.banner-contenido h2 {
    margin: 6px 0;
    font-size: 23px;
    font-weight: 700;
    letter-spacing: -.5px;
}

.banner-contenido p {
    max-width: 650px;
    margin: 0;
    font-size: 13px;
    line-height: 1.7;
    color: rgba(255, 255, 255, .88);
}

.banner-decoracion {
    position: absolute;
    right: 15px;
    top: -35px;
    width: 220px;
    height: 220px;
    opacity: .09;
    transform: rotate(-15deg);
    pointer-events: none;
    z-index: -1;
}

.banner-decoracion svg {
    width: 100%;
    height: 100%;
}

/* ==========================================
   TARJETAS GENERALES
   ========================================== */

.tarjeta {
    min-width: 0;
    border: 1px solid #E2E8F0;
    border-radius: 16px;
    background: #FFFFFF;
    box-shadow: 0 4px 18px rgba(15, 23, 42, .035);
}

.encabezado-tarjeta {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 15px;
    padding: 22px 24px;
    border-bottom: 1px solid #E2E8F0;
}

.titulo-con-icono {
    display: flex;
    align-items: center;
    gap: 13px;
}

.icono-seccion {
    display: grid;
    place-items: center;
    width: 43px;
    height: 43px;
    flex-shrink: 0;
    border-radius: 11px;
    background: var(--fc-light);
    color: var(--fc-primary);
}

.icono-seccion svg {
    width: 23px;
    height: 23px;
}

.encabezado-tarjeta h2 {
    margin: 0;
    font-size: 17px;
    font-weight: 700;
    color: #1E293B;
}

.encabezado-tarjeta p {
    margin: 4px 0 0;
    font-size: 12px;
    color: #64748B;
    line-height: 1.6;
}

/* ==========================================
   FORMULARIO
   ========================================== */

.formulario-tarjeta {
    scroll-margin-top: 90px;
    animation: aparecer .25s ease;
}

.formulario {
    padding: 26px;
}

.formulario fieldset {
    min-width: 0;
    padding: 0;
    margin: 0;
    border: 0;
}

.subtitulo-formulario {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 22px;
}

.numero-paso {
    display: grid;
    place-items: center;
    width: 30px;
    height: 30px;
    flex-shrink: 0;
    border-radius: 9px;
    background: var(--fc-light);
    color: var(--fc-primary);
    font-size: 13px;
    font-weight: 700;
}

.subtitulo-formulario h3 {
    margin: 0;
    font-size: 15px;
    font-weight: 700;
    color: #1E293B;
}

.subtitulo-formulario p {
    margin: 4px 0 0;
    font-size: 12px;
    color: #64748B;
}

.grilla-formulario {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 22px;
}

.ancho-completo {
    grid-column: 1 / -1;
}

.campo {
    min-width: 0;
}

.campo label {
    display: block;
    margin-bottom: 8px;
    font-size: 13px;
    font-weight: 600;
    color: #334155;
}

.opcional {
    color: #64748B;
    font-size: 11px;
    font-weight: 400;
}

.campo input,
.campo select,
.campo textarea {
    display: block;
    width: 100%;
    min-width: 0;
    min-height: 43px;
    padding: 11px 13px;
    border: 1px solid #CBD5E1;
    border-radius: 9px;
    background: #FFFFFF;
    color: #334155;
    font-size: 13px;
    outline: none;
    transition: .2s ease;
}

.campo textarea {
    resize: vertical;
}

.campo input:focus,
.campo select:focus,
.campo textarea:focus {
    border-color: var(--fc-accent);
    box-shadow: 0 0 0 3px var(--fc-light);
}

.campo input::placeholder,
.campo textarea::placeholder {
    color: #94A3B8;
}

.campo .campo-error {
    border-color: #DC2626;
}

.error {
    margin: 7px 0 0;
    font-size: 12px;
    color: #B91C1C;
    line-height: 1.5;
}

.contador {
    margin-top: 5px;
    text-align: right;
    color: #64748B;
    font-size: 11px;
}

/* ==========================================
   INFORMACIÓN DEL PACIENTE
   ========================================== */

.paciente-panel {
    padding: 20px;
    border: 1px solid var(--fc-border);
    border-radius: 13px;
    background: var(--fc-background);
}

.paciente-encabezado {
    display: flex;
    align-items: center;
    gap: 13px;
    margin-bottom: 18px;
}

.avatar {
    display: grid;
    place-items: center;
    width: 35px;
    height: 35px;
    flex-shrink: 0;
    border-radius: 10px;
    background: var(--fc-light);
    color: var(--fc-primary);
    font-size: 12px;
    font-weight: 700;
}

.avatar-grande {
    width: 47px;
    height: 47px;
    border-radius: 13px;
    font-size: 17px;
}

.paciente-encabezado small {
    font-size: 11px;
    color: #64748B;
}

.paciente-encabezado h3 {
    margin: 3px 0 0;
    font-size: 17px;
    font-weight: 700;
    color: var(--fc-primary);
}

.datos-paciente {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 15px;
    padding: 16px;
    border: 1px solid var(--fc-border);
    border-radius: 10px;
    background: #FFFFFF;
}

.datos-paciente small {
    display: block;
    margin-bottom: 5px;
    font-size: 11px;
    color: #64748B;
}

.datos-paciente strong {
    font-size: 13px;
    font-weight: 600;
    color: #334155;
    overflow-wrap: anywhere;
}

/* ==========================================
   ALERTAS DE ALERGIAS
   ========================================== */

.alerta {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    margin-top: 15px;
    padding: 16px;
    border: 1px solid;
    border-radius: 10px;
}

.alerta svg {
    width: 22px;
    height: 22px;
    flex-shrink: 0;
}

.alerta h4 {
    margin: 0;
    font-size: 13px;
    font-weight: 700;
}

.alerta p {
    margin: 6px 0 0;
    font-size: 12px;
    line-height: 1.7;
}

.alerta small {
    display: block;
    margin-top: 8px;
    font-size: 11px;
    line-height: 1.6;
}

.texto-alergias {
    white-space: pre-wrap;
    overflow-wrap: anywhere;
    font-weight: 600;
}

.alergia-roja {
    background: #FEF2F2;
    border-color: #FECACA;
    color: #991B1B;
}

.alergia-verde {
    background: #F0FDF4;
    border-color: #BBF7D0;
    color: #166534;
}

.alergia-amarilla {
    background: #FFFBEB;
    border-color: #FDE68A;
    color: #92400E;
}

.nota-clinica {
    margin: 12px 0 0;
    font-size: 11px;
    line-height: 1.6;
    color: #64748B;
}

/* ==========================================
   MEDICAMENTOS PRESCRITOS
   ========================================== */

.seccion-medicamentos {
    margin-top: 28px;
    padding-top: 26px;
    border-top: 1px solid #E2E8F0;
}

.medicamentos-encabezado {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 15px;
    margin-bottom: 20px;
}

.medicamentos-encabezado .subtitulo-formulario {
    margin: 0;
}

.medicamento-tarjeta {
    margin-bottom: 15px;
    padding: 19px;
    border: 1px solid #E2E8F0;
    border-radius: 12px;
    background: #F8FAFC;
}

.medicamento-encabezado {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 18px;
}

.medicamento-numero {
    display: inline-flex;
    padding: 6px 10px;
    border-radius: 6px;
    background: var(--fc-light);
    color: var(--fc-primary);
    font-size: 12px;
    font-weight: 700;
}

.boton-quitar {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 5px 8px;
    border: 0;
    border-radius: 6px;
    background: transparent;
    color: #B91C1C;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
}

.boton-quitar:hover {
    background: #FEE2E2;
}

.boton-quitar svg {
    width: 14px;
    height: 14px;
}

.grilla-medicamento {
    display: grid;
    grid-template-columns:
        minmax(0, 1.5fr)
        minmax(0, .8fr)
        minmax(0, 1.5fr);
    gap: 15px;
}

.resumen-medicamentos {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 14px 18px;
    border-radius: 10px;
    background: var(--fc-light);
    color: var(--fc-primary);
    font-size: 13px;
    font-weight: 600;
}

.resumen-medicamentos strong {
    font-size: 20px;
}

/* ==========================================
   AVISO Y ACCIONES
   ========================================== */

.aviso-academico {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    margin-top: 24px;
    padding: 16px;
    border: 1px solid #E2E8F0;
    border-radius: 10px;
    background: #F8FAFC;
    color: #475569;
}

.aviso-academico svg {
    width: 23px;
    height: 23px;
    flex-shrink: 0;
    color: var(--fc-primary);
}

.aviso-academico strong {
    font-size: 12px;
}

.aviso-academico p {
    margin: 5px 0 0;
    font-size: 12px;
    line-height: 1.7;
}

.acciones-formulario {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    margin-top: 24px;
    padding-top: 20px;
    border-top: 1px solid #E2E8F0;
}

/* ==========================================
   HISTORIAL Y FILTROS
   ========================================== */

.historial {
    overflow: hidden;
}

.contador-registros {
    flex-shrink: 0;
    padding: 7px 12px;
    border: 1px solid var(--fc-border);
    border-radius: 20px;
    background: var(--fc-light);
    color: var(--fc-primary);
    font-size: 11px;
    font-weight: 600;
}

.filtros {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 12px;
    padding: 20px 24px;
}

.buscador {
    display: flex;
    align-items: center;
    gap: 10px;
    flex: 1;
    min-width: 200px;
    min-height: 42px;
    padding: 0 13px;
    border: 1px solid #CBD5E1;
    border-radius: 9px;
    background: #FFFFFF;
}

.buscador:focus-within {
    border-color: var(--fc-accent);
    box-shadow: 0 0 0 3px var(--fc-light);
}

.buscador svg {
    width: 18px;
    height: 18px;
    flex-shrink: 0;
    color: #94A3B8;
}

.buscador input {
    width: 100%;
    min-width: 0;
    padding: 11px 0;
    border: 0;
    outline: none;
    background: transparent;
    font-size: 12px;
    color: #334155;
}

.buscador input::placeholder {
    color: #94A3B8;
}

.selector-filtro {
    min-height: 42px;
    padding: 10px 14px;
    border: 1px solid #CBD5E1;
    border-radius: 9px;
    background: #FFFFFF;
    color: #334155;
    font-size: 12px;
    outline: none;
}

.selector-filtro:focus {
    border-color: var(--fc-accent);
}

.boton-limpiar {
    padding: 10px 12px;
    border: 0;
    background: transparent;
    color: var(--fc-primary);
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
}

.boton-limpiar:hover {
    text-decoration: underline;
}

/* ==========================================
   TABLA
   ========================================== */

.tabla-contenedor {
    width: 100%;
    overflow-x: auto;
}

.tabla-recetas {
    width: 100%;
    min-width: 1150px;
    border-collapse: collapse;
    text-align: left;
}

.tabla-recetas thead {
    background: #F8FAFC;
}

.tabla-recetas th {
    padding: 14px 17px;
    border-top: 1px solid #E2E8F0;
    border-bottom: 1px solid #E2E8F0;
    color: #64748B;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: .6px;
    text-transform: uppercase;
    white-space: nowrap;
}

.tabla-recetas td {
    padding: 17px;
    border-bottom: 1px solid #F1F5F9;
    color: #475569;
    font-size: 12px;
    vertical-align: top;
}

.tabla-recetas tbody tr:hover {
    background: var(--fc-background);
}

.tabla-recetas tbody tr:last-child td {
    border-bottom: 0;
}

.codigo-receta {
    display: inline-block;
    color: var(--fc-primary);
    font-size: 13px;
    font-weight: 700;
    white-space: nowrap;
}

.texto-secundario {
    display: block;
    margin-top: 5px;
    font-size: 11px;
    color: #64748B;
    line-height: 1.5;
}

.sin-salto {
    white-space: nowrap;
}

.paciente-tabla {
    display: flex;
    align-items: center;
    gap: 9px;
    min-width: 160px;
}

.paciente-tabla strong {
    color: #334155;
    font-size: 12px;
    font-weight: 600;
}

.medicamento-tabla {
    min-width: 145px;
    max-width: 240px;
    margin-bottom: 12px;
    padding-bottom: 10px;
    border-bottom: 1px dashed #E2E8F0;
}

.medicamento-tabla:last-child {
    margin-bottom: 0;
    padding-bottom: 0;
    border-bottom: 0;
}

.medicamento-tabla strong {
    color: #334155;
    font-size: 12px;
    font-weight: 600;
}

.indicaciones {
    display: block;
    margin-top: 5px;
    color: #64748B;
    font-size: 11px;
    line-height: 1.5;
    overflow-wrap: anywhere;
}

/* ==========================================
   ETIQUETAS DE ESTADO
   ========================================== */

.estado {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 10px;
    border-radius: 20px;
    font-size: 10px;
    font-weight: 600;
    white-space: nowrap;
}

.punto-estado {
    width: 6px;
    height: 6px;
    flex-shrink: 0;
    border-radius: 50%;
    background: currentColor;
}

.estado-alergia {
    margin-top: 10px;
    font-size: 10px;
}

.estado-verde {
    background: #DCFCE7;
    color: #166534;
}

.estado-rojo {
    background: #FEE2E2;
    color: #B91C1C;
}

.estado-azul {
    background: #DBEAFE;
    color: #1D4ED8;
}

.estado-amarillo {
    background: #FEF3C7;
    color: #92400E;
}

.estado-gris {
    background: #E2E8F0;
    color: #475569;
}

/* ==========================================
   DOCUMENTOS PDF
   ========================================== */

.acciones-documentos {
    display: flex;
    flex-direction: column;
    gap: 7px;
    min-width: 115px;
}

.boton-pdf,
.boton-descargar {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 7px;
    min-height: 33px;
    padding: 8px 11px;
    border-radius: 7px;
    font-size: 11px;
    font-weight: 600;
    text-decoration: none;
    white-space: nowrap;
    transition: .2s ease;
}

.boton-pdf {
    border: 1px solid transparent;
    background: var(--fc-primary);
    color: #FFFFFF;
}

.boton-pdf:hover {
    background: var(--fc-hover);
}

.boton-descargar {
    border: 1px solid var(--fc-border);
    background: var(--fc-light);
    color: var(--fc-primary);
}

.boton-descargar:hover {
    border-color: var(--fc-accent);
}

.boton-pdf svg,
.boton-descargar svg {
    width: 15px;
    height: 15px;
    flex-shrink: 0;
}

/* ==========================================
   ESTADO VACÍO
   ========================================== */

.estado-vacio {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 10px;
    padding: 40px 20px;
    text-align: center;
}

.icono-vacio {
    display: grid;
    place-items: center;
    width: 62px;
    height: 62px;
    margin-bottom: 5px;
    border-radius: 17px;
    background: var(--fc-light);
    color: var(--fc-primary);
}

.icono-vacio svg {
    width: 30px;
    height: 30px;
}

.estado-vacio h3 {
    margin: 0;
    color: #334155;
    font-size: 16px;
    font-weight: 700;
}

.estado-vacio p {
    margin: 0 0 5px;
    color: #64748B;
    font-size: 12px;
}

/* ==========================================
   PIE DE TABLA
   ========================================== */

.pie-tabla {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 10px;
    padding: 16px 24px;
    border-top: 1px solid #E2E8F0;
    background: #FAFBFC;
    color: #64748B;
    font-size: 11px;
}

.nota-final {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    padding: 0 4px;
    color: #64748B;
}

.nota-final svg {
    width: 18px;
    height: 18px;
    flex-shrink: 0;
}

.nota-final p {
    margin: 0;
    font-size: 11px;
    line-height: 1.7;
}

/* ==========================================
   ANIMACIONES
   ========================================== */

@keyframes aparecer {
    from {
        opacity: 0;
        transform: translateY(-8px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* ==========================================
   DISEÑO RESPONSIVO
   ========================================== */

@media (max-width: 1250px) {
    .indicadores {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .grilla-medicamento {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .grilla-medicamento > :last-child {
        grid-column: 1 / -1;
    }
}

@media (max-width: 768px) {
    .modulo-recetas {
        gap: 18px;
    }

    .indicadores {
        gap: 12px;
    }

    .tarjeta-indicador {
        padding: 15px;
    }

    .tarjeta-indicador strong {
        font-size: 27px;
    }

    .icono-indicador {
        width: 34px;
        height: 34px;
    }

    .banner-recetas {
        padding: 20px;
        gap: 15px;
    }

    .banner-icono {
        width: 48px;
        height: 48px;
    }

    .banner-icono svg {
        width: 26px;
        height: 26px;
    }

    .banner-contenido h2 {
        font-size: 18px;
    }

    .banner-contenido p {
        font-size: 12px;
    }

    .encabezado-tarjeta {
        padding: 18px;
        flex-wrap: wrap;
    }

    .formulario {
        padding: 18px;
    }

    .grilla-formulario,
    .grilla-medicamento {
        grid-template-columns: minmax(0, 1fr);
        gap: 18px;
    }

    .datos-paciente {
        grid-template-columns: minmax(0, 1fr);
    }

    .medicamentos-encabezado {
        flex-direction: column;
        align-items: flex-start;
    }

    .filtros {
        padding: 16px;
    }

    .buscador,
    .selector-filtro {
        width: 100%;
    }

    .pie-tabla {
        padding: 15px 18px;
    }
}

@media (max-width: 480px) {
    .indicadores {
        grid-template-columns: minmax(0, 1fr);
    }

    .banner-icono {
        display: none;
    }

    .acciones-formulario {
        flex-direction: column-reverse;
    }

    .acciones-formulario button {
        width: 100%;
    }

    .paciente-panel {
        padding: 14px;
    }
}

@media (prefers-reduced-motion: reduce) {
    .formulario-tarjeta {
        animation: none;
    }

    .tarjeta-indicador,
    .boton-primario,
    .boton-secundario,
    .boton-pdf,
    .boton-descargar {
        transition: none;
    }

    .tarjeta-indicador:hover {
        transform: none;
    }
}
/* CONTROL DE RECETAS */

.rut-correcto {
    display: block;
    margin-top: 7px;
    color: #15803D;
    font-size: 12px;
    font-weight: 600;
}

.retencion-receta {
    margin-top: 10px;
}

.tabla-recetas {
    min-width: 1380px;
}

.retencion-receta .estado {
    margin-bottom: 4px;
}
</style>
