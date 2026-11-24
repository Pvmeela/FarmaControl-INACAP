<script setup>
import { computed, ref, nextTick } from 'vue'
import { Head, router, useForm, usePage } from '@inertiajs/vue3'
import FarmaLayout from '../../Layouts/FarmaLayout.vue'

const props = defineProps({
    medicamentos: {
        type: Array,
        default: () => [],
    },
})

const page = usePage()

// PERMISOS

const rolUsuario = computed(() =>
    page.props.auth?.user?.rol ?? ''
)

const puedeAdministrarMedicamentos = computed(() =>
    [
        'Administrador',
        'Quimico Farmaceutico',
    ].includes(rolUsuario.value)
)

// ESTADOS

const mostrarFormulario = ref(false)
const mostrarEdicion = ref(false)
const medicamentoEnEdicion = ref(null)
const mensajeExito = ref('')
const cambiandoEstadoId = ref(null)
const formularioRef = ref(null)

const busqueda = ref('')
const filtroEstado = ref('todos')
const filtroStock = ref('todos')
const filtroClasificacion = ref('todos')

// FORMULARIOS

const datosIniciales = {
    nombre: '',
    principio_activo: '',
    concentracion: '',
    forma_farmaceutica: '',
    presentacion: '',
    codigo: '',
    stock_minimo: 0,
    precio_venta: '',
    es_controlado: false,
    tipo_control: null,
    requiere_receta: false,
    tipo_receta: null,
}

const form = useForm({ ...datosIniciales })
const formEditar = useForm({ ...datosIniciales })

const formularioActual = computed(() =>
    mostrarEdicion.value ? formEditar : form
)

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
]

// INDICADORES

const totalMedicamentos = computed(() =>
    props.medicamentos.length
)

const totalActivos = computed(() =>
    props.medicamentos.filter(m => m.activo).length
)

const totalInactivos = computed(() =>
    props.medicamentos.filter(m => !m.activo).length
)

const totalAlertas = computed(() =>
    props.medicamentos.filter(m =>
        m.activo &&
        ['quiebre', 'bajo'].includes(m.estado_stock)
    ).length
)

const indicadores = computed(() => [
    {
        titulo: 'Medicamentos registrados',
        cantidad: totalMedicamentos.value,
        descripcion: 'Total del catálogo',
        color: 'principal',
        icono: 'medicamento',
    },
    {
        titulo: 'Medicamentos activos',
        cantidad: totalActivos.value,
        descripcion: 'Habilitados en el sistema',
        color: 'verde',
        icono: 'check',
    },
    {
        titulo: 'Medicamentos inactivos',
        cantidad: totalInactivos.value,
        descripcion: 'Deshabilitados',
        color: 'gris',
        icono: 'inactivo',
    },
    {
        titulo: 'Alertas de stock',
        cantidad: totalAlertas.value,
        descripcion: 'Quiebres y stock bajo',
        color: 'naranja',
        icono: 'alerta',
    },
])

// ICONOS

const iconos = {
    medicamento: 'M8 4a5 5 0 0 1 7 0l5 5a5 5 0 0 1-7 7l-5-5a5 5 0 0 1 0-7z M10 13l7-7',
    check: 'M20 6L9 17l-5-5',
    inactivo: 'M12 8v8 M8 12h8 M22 12a10 10 0 1 1-20 0 10 10 0 0 1 20 0z',
    alerta: 'M12 3L2 21h20L12 3z M12 9v5 M12 17h.01',
    buscar: 'M16 16l5 5 M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0z',
    agregar: 'M12 5v14 M5 12h14',
    editar: 'M13 6l5 5 M4 20l5-1L21 7l-5-5L4 14z',
    cerrar: 'M6 6l12 12 M18 6L6 18',
    lista: 'M8 6h13 M8 12h13 M8 18h13 M3 6h.01 M3 12h.01 M3 18h.01',
}

// BÚSQUEDA Y FILTROS

function normalizar(texto) {
    return String(texto ?? '')
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .toLowerCase()
        .trim()
}

function textoClasificacion(medicamento) {
    if (medicamento.es_controlado) {
        return medicamento.tipo_control === 'estupefaciente'
            ? 'Estupefaciente'
            : medicamento.tipo_control === 'psicotropico'
                ? 'Psicotrópico'
                : 'Controlado sin clasificación'
    }

    if (medicamento.requiere_receta) {
        return 'No controlado con receta'
    }

    return 'Sin restricción registrada'
}

function textoTipoReceta(tipo) {
    const tipos = {
        simple: 'Receta simple',
        retenida: 'Receta retenida',
        cheque: 'Receta cheque',
    }

    return tipos[tipo] ?? 'Sin tipo registrado'
}

const medicamentosFiltrados = computed(() => {
    const termino = normalizar(busqueda.value)

    return props.medicamentos.filter(medicamento => {

        const coincideBusqueda = [
            medicamento.nombre,
            medicamento.codigo,
            medicamento.principio_activo,
            medicamento.concentracion,
            medicamento.forma_farmaceutica,
            medicamento.presentacion,
            textoClasificacion(medicamento),
            textoTipoReceta(medicamento.tipo_receta),
        ].some(valor =>
            normalizar(valor).includes(termino)
        )

        const coincideEstado =
            filtroEstado.value === 'todos' ||
            (
                filtroEstado.value === 'activos' &&
                medicamento.activo
            ) ||
            (
                filtroEstado.value === 'inactivos' &&
                !medicamento.activo
            )

        const coincideStock =
            filtroStock.value === 'todos' ||
            medicamento.estado_stock === filtroStock.value

        const coincideClasificacion =
            filtroClasificacion.value === 'todos' ||
            (
                filtroClasificacion.value === 'controlados' &&
                medicamento.es_controlado
            ) ||
            (
                filtroClasificacion.value === 'estupefaciente' &&
                medicamento.es_controlado &&
                medicamento.tipo_control === 'estupefaciente'
            ) ||
            (
                filtroClasificacion.value === 'psicotropico' &&
                medicamento.es_controlado &&
                medicamento.tipo_control === 'psicotropico'
            ) ||
            (
                filtroClasificacion.value === 'con_receta' &&
                medicamento.requiere_receta
            ) ||
            (
                filtroClasificacion.value === 'sin_restriccion' &&
                !medicamento.es_controlado &&
                !medicamento.requiere_receta
            )

        return coincideBusqueda &&
            coincideEstado &&
            coincideStock &&
            coincideClasificacion
    })
})

function limpiarFiltros() {
    busqueda.value = ''
    filtroEstado.value = 'todos'
    filtroStock.value = 'todos'
    filtroClasificacion.value = 'todos'
}

// CLASIFICACIÓN FARMACOLÓGICA

function cambiarClasificacion() {
    const actual = formularioActual.value

    if (actual.es_controlado) {
        actual.requiere_receta = true

        if (!['retenida', 'cheque'].includes(actual.tipo_receta)) {
            actual.tipo_receta = null
        }
    } else {
        actual.tipo_control = null
    }

    actual.clearErrors(
        'es_controlado',
        'tipo_control',
        'requiere_receta',
        'tipo_receta'
    )
}

function cambiarCondicionReceta() {
    const actual = formularioActual.value

    if (actual.es_controlado) {
        actual.requiere_receta = true
    }

    if (!actual.requiere_receta) {
        actual.tipo_receta = null
    }

    actual.clearErrors(
        'requiere_receta',
        'tipo_receta'
    )
}
// FORMATO DE PRECIOS

function formatearPrecio(precio) {
    if (
        precio === null ||
        precio === undefined ||
        precio === ''
    ) {
        return 'Sin precio'
    }

    const valor = Number(precio)

    if (!Number.isFinite(valor)) {
        return 'Sin precio'
    }

    return new Intl.NumberFormat('es-CL', {
        style: 'currency',
        currency: 'CLP',
        maximumFractionDigits: 0,
    }).format(valor)
}

// ALERTAS DE STOCK

function textoStock(estado) {
    const estados = {
        quiebre: 'Quiebre de stock',
        bajo: 'Stock bajo',
        suficiente: 'Stock suficiente',
    }

    return estados[estado] ?? 'Umbral sin configurar'
}

function claseStock(estado) {
    const clases = {
        quiebre: 'estado-rojo',
        bajo: 'estado-naranja',
        suficiente: 'estado-verde',
    }

    return clases[estado] ?? 'estado-gris'
}

// FORMULARIO DE REGISTRO

async function alternarFormulario() {
    if (!puedeAdministrarMedicamentos.value) return

    if (mostrarEdicion.value) {
        cancelarEdicion()
    }

    mostrarFormulario.value = !mostrarFormulario.value
    mensajeExito.value = ''

    if (!mostrarFormulario.value) {
        form.clearErrors()
    } else {
        await nextTick()

        formularioRef.value?.scrollIntoView({
            behavior: 'smooth',
            block: 'start',
        })
    }
}

function guardarMedicamento() {
    if (!puedeAdministrarMedicamentos.value) return
    if (form.processing) return

    mensajeExito.value = ''

    form.post('/medicamentos', {
        preserveScroll: true,

        onSuccess: () => {
            form.reset()
            mostrarFormulario.value = false
            mensajeExito.value =
                'Medicamento registrado correctamente.'
        },
    })
}

// EDICIÓN

async function editarMedicamento(medicamento) {

    if (!puedeAdministrarMedicamentos.value) return

    mostrarFormulario.value = false
    mostrarEdicion.value = true
    medicamentoEnEdicion.value = medicamento.id
    mensajeExito.value = ''

    for (const campo of campos) {
        formEditar[campo.nombre] =
            medicamento[campo.nombre] ?? ''
    }

    formEditar.stock_minimo =
        medicamento.stock_minimo ?? 0

    formEditar.precio_venta =
        medicamento.precio_venta ?? ''

    formEditar.es_controlado =
        Boolean(medicamento.es_controlado)

    formEditar.tipo_control =
        medicamento.tipo_control ?? null

    formEditar.requiere_receta =
        Boolean(medicamento.requiere_receta)

    formEditar.tipo_receta =
        medicamento.tipo_receta ?? null

    formEditar.clearErrors()

    await nextTick()

    formularioRef.value?.scrollIntoView({
        behavior: 'smooth',
        block: 'start',
    })
}

function cancelarEdicion() {
    mostrarEdicion.value = false
    medicamentoEnEdicion.value = null
    formEditar.reset()
    formEditar.clearErrors()
}

function cerrarFormulario() {
    if (mostrarEdicion.value) {
        cancelarEdicion()
    } else {
        mostrarFormulario.value = false
        form.clearErrors()
    }
}

function actualizarMedicamento() {
    if (!puedeAdministrarMedicamentos.value) return
    if (medicamentoEnEdicion.value === null) return
    if (formEditar.processing) return

    mensajeExito.value = ''

    formEditar.put(
        `/medicamentos/${medicamentoEnEdicion.value}`,
        {
            preserveScroll: true,

            onSuccess: () => {
                cancelarEdicion()

                mensajeExito.value =
                    'Medicamento actualizado correctamente.'
            },
        }
    )
}

function enviarFormulario() {
    if (mostrarEdicion.value) {
        actualizarMedicamento()
    } else {
        guardarMedicamento()
    }
}

// ACTIVAR Y DESACTIVAR

function cambiarEstadoMedicamento(medicamento) {
    if (!puedeAdministrarMedicamentos.value) return
    if (cambiandoEstadoId.value !== null) return

    const estabaActivo = medicamento.activo
    const accion = estabaActivo ? 'desactivar' : 'activar'

    const confirmado = window.confirm(
        `¿Estás seguro de que deseas ${accion} el medicamento "${medicamento.nombre}"?`
    )

    if (!confirmado) return

    mensajeExito.value = ''
    cambiandoEstadoId.value = medicamento.id

    router.patch(
        `/medicamentos/${medicamento.id}/estado`,
        {},
        {
            preserveScroll: true,

            onSuccess: () => {
                mensajeExito.value = estabaActivo
                    ? 'Medicamento desactivado correctamente.'
                    : 'Medicamento activado correctamente.'
            },

            onFinish: () => {
                cambiandoEstadoId.value = null
            },
        }
    )
}
</script>

<template>
    <Head title="Medicamentos | FarmaControl" />

    <FarmaLayout
        titulo="Medicamentos"
        subtitulo="Consulta y administra el catálogo de medicamentos del sistema."
    >
        <template #acciones>
            <button
                v-if="puedeAdministrarMedicamentos"
                type="button"
                class="boton-principal"
                @click="alternarFormulario"
            >
                <svg viewBox="0 0 24 24">
                    <path
                        :d="mostrarFormulario
                            ? iconos.cerrar
                            : iconos.agregar"
                    />
                </svg>

                {{
                    mostrarFormulario
                        ? 'Cancelar registro'
                        : 'Nuevo medicamento'
                }}
            </button>
        </template>

        <div class="modulo-medicamentos">

            <!-- INDICADORES -->

            <section class="indicadores">
                <article
                    v-for="indicador in indicadores"
                    :key="indicador.titulo"
                    class="tarjeta-indicador"
                >
                    <div class="indicador-superior">
                        <span>{{ indicador.titulo }}</span>

                        <div
                            class="icono-indicador"
                            :class="indicador.color"
                        >
                            <svg viewBox="0 0 24 24">
                                <path :d="iconos[indicador.icono]" />
                            </svg>
                        </div>
                    </div>

                    <strong class="cantidad">
                        {{ indicador.cantidad }}
                    </strong>

                    <p>{{ indicador.descripcion }}</p>
                </article>
            </section>

            <!-- MENSAJES -->

            <div
                v-if="!puedeAdministrarMedicamentos"
                class="aviso-consulta"
            >
                <svg viewBox="0 0 24 24">
                    <path :d="iconos.alerta" />
                </svg>

                <div>
                    <strong>Modo de consulta</strong>

                    <p>
                        Tu rol permite consultar el catálogo,
                        pero no registrar, editar o cambiar
                        el estado de los medicamentos.
                    </p>
                </div>
            </div>

            <Transition>
                <div
                    v-if="mensajeExito"
                    class="mensaje-exito"
                    role="status"
                >
                    <svg viewBox="0 0 24 24">
                        <path :d="iconos.check" />
                    </svg>

                    <span>{{ mensajeExito }}</span>

                    <button
                        type="button"
                        aria-label="Cerrar mensaje"
                        @click="mensajeExito = ''"
                    >
                        <svg viewBox="0 0 24 24">
                            <path :d="iconos.cerrar" />
                        </svg>
                    </button>
                </div>
            </Transition>

            <!-- FORMULARIO -->

            <Transition>
                <section
                    v-if="
                        puedeAdministrarMedicamentos &&
                        (mostrarFormulario || mostrarEdicion)
                    "
                    ref="formularioRef"
                    class="tarjeta formulario-tarjeta"
                >
                    <div class="tarjeta-encabezado">
                        <div class="titulo-con-icono">
                            <div class="icono-seccion">
                                <svg viewBox="0 0 24 24">
                                    <path
                                        :d="mostrarEdicion
                                            ? iconos.editar
                                            : iconos.agregar"
                                    />
                                </svg>
                            </div>

                            <div>
                                <h2>
                                    {{
                                        mostrarEdicion
                                            ? 'Editar medicamento'
                                            : 'Registrar medicamento'
                                    }}
                                </h2>

                                <p>
                                    Completa la información
                                    farmacéutica del medicamento.
                                </p>
                            </div>
                        </div>

                        <button
                            type="button"
                            class="boton-icono"
                            aria-label="Cerrar formulario"
                            @click="cerrarFormulario"
                        >
                            <svg viewBox="0 0 24 24">
                                <path :d="iconos.cerrar" />
                            </svg>
                        </button>
                    </div>

                    <form
                        class="formulario"
                        @submit.prevent="enviarFormulario"
                    >
                        <div class="titulo-grupo">
                            Información general
                        </div>

                        <div class="campos-grid">
                            <div
                                v-for="campo in campos"
                                :key="campo.nombre"
                                class="campo"
                            >
                                <label :for="campo.nombre">
                                    {{ campo.etiqueta }}
                                    <span class="obligatorio">*</span>
                                </label>

                                <input
                                    :id="campo.nombre"
                                    v-model="formularioActual[campo.nombre]"
                                    type="text"
                                    :maxlength="campo.maximo"
                                    :placeholder="campo.ejemplo"
                                    :class="{
                                        'input-error':
                                            formularioActual.errors[campo.nombre]
                                    }"
                                    required
                                />

                                <p
                                    v-if="formularioActual.errors[campo.nombre]"
                                    class="error"
                                >
                                    {{ formularioActual.errors[campo.nombre] }}
                                </p>
                            </div>
                        </div>

                        <div class="titulo-grupo segundo-grupo">
                            Configuración de inventario y precio
                        </div>

                        <div class="campos-grid configuracion-grid">
                            <div class="campo">
                                <label for="stock-minimo">
                                    Stock mínimo
                                    <span class="obligatorio">*</span>
                                </label>

                                <input
                                    id="stock-minimo"
                                    v-model.number="formularioActual.stock_minimo"
                                    type="number"
                                    min="0"
                                    step="1"
                                    required
                                    placeholder="Ej: 20"
                                />

                                <small>
                                    Cantidad mínima deseada.
                                    Utiliza 0 si no has definido un umbral.
                                </small>

                                <p
                                    v-if="formularioActual.errors.stock_minimo"
                                    class="error"
                                >
                                    {{ formularioActual.errors.stock_minimo }}
                                </p>
                            </div>

                            <div class="campo">
                                <label for="precio-venta">
                                    Precio de venta ($ CLP)
                                </label>

                                <input
                                    id="precio-venta"
                                    v-model="formularioActual.precio_venta"
                                    type="number"
                                    min="0"
                                    max="9999999999.99"
                                    step="0.01"
                                    placeholder="Ej: 2990"
                                />

                                <small>
                                    Precio para ventas simuladas de
                                    Farmacia Privada. Campo opcional.
                                </small>

                                <p
                                    v-if="formularioActual.errors.precio_venta"
                                    class="error"
                                >
                                    {{ formularioActual.errors.precio_venta }}
                                </p>
                            </div>
                        </div>
                         <!-- CLASIFICACIÓN FARMACOLÓGICA -->

<div class="titulo-grupo segundo-grupo">
    Clasificación farmacológica y control de recetas
</div>

<div class="panel-clasificacion">

    <div class="clasificacion-encabezado">

        <div class="icono-seccion">
            <svg viewBox="0 0 24 24">
                <path :d="iconos.alerta" />
            </svg>
        </div>

        <div>
            <h3>Condición de dispensación</h3>

            <p>
                Define las restricciones aplicables al medicamento
                dentro de la simulación académica.
            </p>
        </div>

    </div>

    <div class="campos-grid configuracion-grid">

        <div class="campo">

            <label for="es-controlado">
                ¿Es un medicamento controlado?
            </label>

            <select
                id="es-controlado"
                v-model="formularioActual.es_controlado"
                @change="cambiarClasificacion"
            >
                <option :value="false">
                    No
                </option>

                <option :value="true">
                    Sí, medicamento controlado
                </option>
            </select>

            <p
                v-if="formularioActual.errors.es_controlado"
                class="error"
            >
                {{ formularioActual.errors.es_controlado }}
            </p>

        </div>

        <div
            v-if="formularioActual.es_controlado"
            class="campo"
        >

            <label for="tipo-control">
                Tipo de control
                <span class="obligatorio">*</span>
            </label>

            <select
                id="tipo-control"
                v-model="formularioActual.tipo_control"
                required
            >
                <option :value="null" disabled>
                    Seleccionar clasificación
                </option>

                <option value="estupefaciente">
                    Estupefaciente
                </option>

                <option value="psicotropico">
                    Psicotrópico
                </option>
            </select>

            <p
                v-if="formularioActual.errors.tipo_control"
                class="error"
            >
                {{ formularioActual.errors.tipo_control }}
            </p>

        </div>

        <div class="campo">

            <label for="requiere-receta">
                ¿Requiere receta médica?
            </label>

            <select
                id="requiere-receta"
                v-model="formularioActual.requiere_receta"
                :disabled="formularioActual.es_controlado"
                @change="cambiarCondicionReceta"
            >
                <option :value="false">
                    No
                </option>

                <option :value="true">
                    Sí, requiere receta
                </option>
            </select>

            <small v-if="formularioActual.es_controlado">
                Obligatoria para medicamentos controlados.
            </small>

            <p
                v-if="formularioActual.errors.requiere_receta"
                class="error"
            >
                {{ formularioActual.errors.requiere_receta }}
            </p>

        </div>

        <div
            v-if="formularioActual.requiere_receta"
            class="campo"
        >

            <label for="tipo-receta">
                Tipo de receta requerida
                <span class="obligatorio">*</span>
            </label>

            <select
                id="tipo-receta"
                v-model="formularioActual.tipo_receta"
                required
            >

                <option :value="null" disabled>
                    Seleccionar tipo de receta
                </option>

                <option
                    v-if="!formularioActual.es_controlado"
                    value="simple"
                >
                    Receta simple
                </option>

                <option value="retenida">
                    Receta retenida
                </option>

                <option value="cheque">
                    Receta cheque
                </option>

            </select>

            <p
                v-if="formularioActual.errors.tipo_receta"
                class="error"
            >
                {{ formularioActual.errors.tipo_receta }}
            </p>

        </div>

    </div>

    <div
        v-if="formularioActual.es_controlado"
        class="aviso-controlado"
    >

        <svg viewBox="0 0 24 24">
            <path :d="iconos.alerta" />
        </svg>

        <div>

            <strong>
                Medicamento sujeto a control especial
            </strong>

            <p>
                Su entrega deberá contar con una receta compatible,
                autorización del profesional correspondiente
                y registro de trazabilidad.
            </p>

        </div>

    </div>

    <div class="nota-clasificacion">
        La clasificación debe verificarse antes de habilitar
        el medicamento para operaciones. Información destinada
        exclusivamente a la simulación académica INACAP.
    </div>

 </div>
   
                        <div class="formulario-inferior">
                            <span>
                                * Campos obligatorios
                            </span>

                            <div class="acciones-formulario">
                                <button
                                    type="button"
                                    class="boton-secundario"
                                    @click="cerrarFormulario"
                                >
                                    Cancelar
                                </button>

                                <button
                                    type="submit"
                                    class="boton-principal"
                                    :disabled="formularioActual.processing"
                                >
                                    {{
                                        formularioActual.processing
                                            ? 'Guardando...'
                                            : mostrarEdicion
                                                ? 'Guardar cambios'
                                                : 'Guardar medicamento'
                                    }}
                                </button>
                            </div>
                        </div>
                    </form>
                </section>
            </Transition>

            <!-- CATÁLOGO -->

            <section class="tarjeta catalogo">
                <div class="tarjeta-encabezado">
                    <div class="titulo-con-icono">
                        <div class="icono-seccion">
                            <svg viewBox="0 0 24 24">
                                <path :d="iconos.lista" />
                            </svg>
                        </div>

                        <div>
                            <h2>Catálogo de medicamentos</h2>

                            <p>
                                Información farmacéutica,
                                existencias y disponibilidad.
                            </p>
                        </div>
                    </div>

                    <span class="contador">
                        {{ totalMedicamentos }} registros
                    </span>
                </div>

                <!-- FILTROS -->
<!-- FILTROS -->

<div class="filtros">

    <div class="buscador">
        <svg viewBox="0 0 24 24">
            <path :d="iconos.buscar" />
        </svg>

        <input
            v-model="busqueda"
            type="search"
            aria-label="Buscar medicamento"
            placeholder="Buscar medicamento, código..."
        />
    </div>

    <select
        v-model="filtroEstado"
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
        v-model="filtroStock"
        aria-label="Filtrar por stock"
    >
        <option value="todos">
            Todo el stock
        </option>

        <option value="quiebre">
            Quiebre de stock
        </option>

        <option value="bajo">
            Stock bajo
        </option>

        <option value="suficiente">
            Stock suficiente
        </option>
    </select>

    <select
        v-model="filtroClasificacion"
        aria-label="Filtrar por clasificación"
    >
        <option value="todos">
            Todas las clasificaciones
        </option>

        <option value="controlados">
            Medicamentos controlados
        </option>

        <option value="estupefaciente">
            Estupefacientes
        </option>

        <option value="psicotropico">
            Psicotrópicos
        </option>

        <option value="con_receta">
            Requieren receta
        </option>

        <option value="sin_restriccion">
            Sin restricción registrada
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

    <table class="tabla">

        <thead>
            <tr>
                <th>Código</th>
                <th>Medicamento</th>
                <th>Principio activo</th>
                <th>Concentración</th>
                <th>Clasificación</th>
                <th>Condición de receta</th>
                <th>Precio de venta</th>
                <th>Stock físico</th>
                <th>Stock utilizable</th>
                <th>Stock mínimo</th>
                <th>Alerta de stock</th>
                <th>Estado</th>

                <th v-if="puedeAdministrarMedicamentos">
                    Acciones
                </th>
            </tr>
        </thead>

        <tbody>

            <tr
                v-for="medicamento in medicamentosFiltrados"
                :key="medicamento.id"
            >

                <td>
                    <span class="codigo">
                        {{ medicamento.codigo }}
                    </span>
                </td>

                <td>
                    <div class="medicamento-nombre">

                        <div class="medicamento-icono">
                            <svg viewBox="0 0 24 24">
                                <path :d="iconos.medicamento" />
                            </svg>
                        </div>

                        <div>
                            <strong>
                                {{ medicamento.nombre }}
                            </strong>

                            <small>
                                {{ medicamento.forma_farmaceutica }}
                            </small>

                            <small>
                                {{ medicamento.presentacion }}
                            </small>
                        </div>

                    </div>
                </td>

                <td>
                    {{ medicamento.principio_activo }}
                </td>

                <td>
                    {{ medicamento.concentracion }}
                </td>

                <!-- CLASIFICACIÓN -->

                <td>
                    <span
                        class="estado"
                        :class="medicamento.es_controlado
                            ? 'estado-naranja'
                            : 'estado-gris'"
                    >
                        <span class="punto-estado"></span>

                        {{ textoClasificacion(medicamento) }}
                    </span>
                </td>

                <!-- CONDICIÓN DE RECETA -->

                <td>
                    <span
                        class="estado"
                        :class="medicamento.requiere_receta
                            ? 'estado-receta'
                            : 'estado-gris'"
                    >
                        <span class="punto-estado"></span>

                        {{
                            medicamento.requiere_receta
                                ? textoTipoReceta(medicamento.tipo_receta)
                                : 'Sin exigencia registrada'
                        }}
                    </span>
                </td>

                <!-- PRECIO -->

                <td>
                    <span
                        :class="
                            formatearPrecio(medicamento.precio_venta)
                                === 'Sin precio'
                                ? 'sin-precio'
                                : 'precio'
                        "
                    >
                        {{
                            formatearPrecio(
                                medicamento.precio_venta
                            )
                        }}
                    </span>
                </td>

                <!-- STOCK -->

                <td>
                    {{ medicamento.stock_fisico ?? '—' }}
                </td>

                <td>
                    <strong>
                        {{ medicamento.stock_utilizable ?? '—' }}
                    </strong>
                </td>

                <td>
                    {{
                        Number(medicamento.stock_minimo) > 0
                            ? medicamento.stock_minimo
                            : 'Sin configurar'
                    }}
                </td>

                <!-- ALERTA DE STOCK -->

                <td>
                    <span
                        class="estado"
                        :class="claseStock(medicamento.estado_stock)"
                    >
                        <span class="punto-estado"></span>

                        {{ textoStock(medicamento.estado_stock) }}
                    </span>
                </td>

                <!-- ESTADO -->

                <td>
                    <span
                        class="estado"
                        :class="medicamento.activo
                            ? 'estado-verde'
                            : 'estado-rojo'"
                    >
                        <span class="punto-estado"></span>

                        {{
                            medicamento.activo
                                ? 'Activo'
                                : 'Inactivo'
                        }}
                    </span>
                </td>

                <!-- ACCIONES -->

                <td v-if="puedeAdministrarMedicamentos">

                    <div class="acciones-tabla">

                        <button
                            type="button"
                            class="boton-editar"
                            @click="editarMedicamento(medicamento)"
                        >
                            <svg viewBox="0 0 24 24">
                                <path :d="iconos.editar" />
                            </svg>

                            Editar
                        </button>

                        <button
                            type="button"
                            class="boton-estado"
                            :class="medicamento.activo
                                ? 'desactivar'
                                : 'activar'"
                            :disabled="cambiandoEstadoId !== null"
                            @click="cambiarEstadoMedicamento(medicamento)"
                        >
                            {{
                                cambiandoEstadoId === medicamento.id
                                    ? 'Procesando...'
                                    : medicamento.activo
                                        ? 'Desactivar'
                                        : 'Activar'
                            }}
                        </button>

                    </div>

                </td>

            </tr>

            <!-- SIN RESULTADOS -->

            <tr v-if="medicamentosFiltrados.length === 0">

                <td
                    :colspan="puedeAdministrarMedicamentos ? 13 : 12"
                >

                    <div class="sin-resultados">

                        <div class="icono-vacio">
                            <svg viewBox="0 0 24 24">
                                <path :d="iconos.buscar" />
                            </svg>
                        </div>

                        <strong>
                            {{
                                totalMedicamentos === 0
                                    ? 'No hay medicamentos registrados'
                                    : 'No se encontraron resultados'
                            }}
                        </strong>

                        <p>
                            {{
                                totalMedicamentos === 0
                                    ? 'El catálogo todavía está vacío.'
                                    : 'Intenta modificar los filtros de búsqueda.'
                            }}
                        </p>

                        <button
                            v-if="totalMedicamentos > 0"
                            type="button"
                            class="boton-secundario"
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
                
                <div class="tabla-inferior">
                    Mostrando {{ medicamentosFiltrados.length }}
                    de {{ totalMedicamentos }} medicamentos
                </div>
            </section>

        </div>
    </FarmaLayout>
</template>

<style scoped>
.modulo-medicamentos {
    display: flex;
    flex-direction: column;
    gap: 24px;
    min-width: 0;
}

svg {
    width: 20px;
    height: 20px;
    flex-shrink: 0;
    fill: none;
    stroke: currentColor;
    stroke-width: 1.7;
    stroke-linecap: round;
    stroke-linejoin: round;
}

button {
    cursor: pointer;
    transition: all 0.2s ease;
}

button:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

/* BOTONES */

.boton-principal {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 9px;
    padding: 12px 18px;
    border-radius: 9px;
    background: var(--fc-accent);
    color: white;
    font-size: 13px;
    font-weight: 600;
}

.boton-principal:hover:not(:disabled) {
    background: var(--fc-hover);
    box-shadow: 0 4px 12px rgba(15, 23, 42, 0.12);
}

.boton-secundario {
    padding: 11px 18px;
    border: 1px solid #e2e8f0;
    border-radius: 9px;
    background: white;
    color: #475569;
    font-size: 13px;
    font-weight: 600;
}

.boton-secundario:hover {
    background: var(--fc-light);
    border-color: var(--fc-border);
}

/* INDICADORES */

.indicadores {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 16px;
}

.tarjeta-indicador {
    min-width: 0;
    padding: 19px;
    border: 1px solid var(--fc-border);
    border-radius: 14px;
    background: white;
    box-shadow: 0 3px 15px rgba(15, 23, 42, 0.03);
    transition: transform 0.2s, box-shadow 0.2s;
}

.tarjeta-indicador:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 22px rgba(15, 23, 42, 0.06);
}

.indicador-superior {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
}

.indicador-superior > span {
    color: #64748b;
    font-size: 12px;
    font-weight: 600;
    line-height: 1.5;
}

.icono-indicador {
    display: grid;
    place-items: center;
    width: 38px;
    height: 38px;
    flex-shrink: 0;
    border-radius: 10px;
}

.icono-indicador.principal {
    background: var(--fc-light);
    color: var(--fc-accent);
}

.icono-indicador.verde {
    background: #ecfdf5;
    color: #059669;
}

.icono-indicador.gris {
    background: #f1f5f9;
    color: #64748b;
}

.icono-indicador.naranja {
    background: #fff7ed;
    color: #ea580c;
}

.cantidad {
    display: block;
    margin: 12px 0 5px;
    color: var(--fc-primary);
    font-size: 32px;
    font-weight: 750;
    letter-spacing: -1px;
}

.tarjeta-indicador p {
    color: #94a3b8;
    font-size: 11px;
}

/* MENSAJES */

.aviso-consulta,
.mensaje-exito {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 16px;
    border-radius: 10px;
    font-size: 13px;
}

.aviso-consulta {
    background: var(--fc-light);
    border: 1px solid var(--fc-border);
    color: var(--fc-primary);
}

.aviso-consulta p {
    margin-top: 5px;
    font-size: 12px;
    line-height: 1.6;
}

.mensaje-exito {
    background: #ecfdf5;
    border: 1px solid #bbf7d0;
    color: #166534;
}

.mensaje-exito span {
    flex: 1;
}

/* TARJETAS */

.tarjeta {
    min-width: 0;
    border: 1px solid var(--fc-border);
    border-radius: 14px;
    background: white;
    box-shadow: 0 3px 15px rgba(15, 23, 42, 0.03);
    overflow: hidden;
}

.tarjeta-encabezado {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 16px;
    padding: 22px;
    border-bottom: 1px solid #edf0f5;
}

.titulo-con-icono {
    display: flex;
    align-items: center;
    gap: 13px;
}

.icono-seccion {
    display: grid;
    place-items: center;
    width: 42px;
    height: 42px;
    flex-shrink: 0;
    border-radius: 11px;
    background: var(--fc-light);
    color: var(--fc-accent);
}

.tarjeta-encabezado h2 {
    color: var(--fc-primary);
    font-size: 16px;
    font-weight: 700;
}

.tarjeta-encabezado p {
    margin-top: 4px;
    color: #64748b;
    font-size: 12px;
}

.contador {
    padding: 7px 12px;
    border-radius: 20px;
    background: var(--fc-light);
    color: var(--fc-primary);
    font-size: 11px;
    font-weight: 600;
}

.boton-icono {
    display: grid;
    place-items: center;
    width: 34px;
    height: 34px;
    border-radius: 8px;
    color: #64748b;
}

.boton-icono:hover {
    background: #f1f5f9;
}

/* FORMULARIO */

.formulario-tarjeta {
    scroll-margin-top: 95px;
}

.formulario {
    padding: 24px;
}

.titulo-grupo {
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 1px solid #edf0f5;
    color: var(--fc-primary);
    font-size: 13px;
    font-weight: 700;
}

.segundo-grupo {
    margin-top: 28px;
}

.campos-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 20px;
}

.configuracion-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr));
}

.campo {
    min-width: 0;
}

.campo label {
    display: block;
    margin-bottom: 8px;
    color: #475569;
    font-size: 12px;
    font-weight: 600;
}

.obligatorio {
    color: #dc2626;
}

.campo input,
.filtros select {
    width: 100%;
    min-height: 43px;
    padding: 11px 13px;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    background: #fff;
    color: #334155;
    font-size: 13px;
    outline: none;
    transition: border-color 0.2s, box-shadow 0.2s;
}

.campo input:focus,
.filtros select:focus {
    border-color: var(--fc-accent);
    box-shadow: 0 0 0 3px var(--fc-light);
}

.campo input::placeholder {
    color: #94a3b8;
}

.campo small {
    display: block;
    margin-top: 7px;
    color: #94a3b8;
    font-size: 11px;
    line-height: 1.6;
}

.campo .input-error {
    border-color: #ef4444;
}

.error {
    margin-top: 6px;
    color: #dc2626;
    font-size: 12px;
}

.formulario-inferior {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 15px;
    margin-top: 28px;
    padding-top: 22px;
    border-top: 1px solid #edf0f5;
}

.formulario-inferior > span {
    color: #94a3b8;
    font-size: 11px;
}

.acciones-formulario {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}

/* FILTROS */

.filtros {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 12px;
    padding: 18px 22px;
}

.buscador {
    display: flex;
    align-items: center;
    flex: 1;
    min-width: 220px;
    gap: 10px;
    padding: 0 13px;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    background: #fff;
    color: #94a3b8;
}

.buscador:focus-within {
    border-color: var(--fc-accent);
    box-shadow: 0 0 0 3px var(--fc-light);
}

.buscador svg {
    width: 17px;
    height: 17px;
}

.buscador input {
    width: 100%;
    min-width: 0;
    min-height: 43px;
    border: none;
    outline: none;
    color: #334155;
    font-size: 12px;
}

.buscador input::placeholder {
    color: #94a3b8;
}

.filtros select {
    width: auto;
    min-width: 145px;
    font-size: 12px;
    cursor: pointer;
}

.boton-limpiar {
    padding: 10px;
    color: var(--fc-accent);
    font-size: 12px;
    font-weight: 600;
}

.boton-limpiar:hover {
    text-decoration: underline;
}

/* TABLA */

.tabla-contenedor {
    width: 100%;
    overflow-x: auto;
}

.tabla {
    width: 100%;
    min-width: 1250px;
    border-collapse: collapse;
    text-align: left;
    font-size: 12px;
}

.tabla thead {
    background: var(--fc-light);
    color: var(--fc-primary);
}

.tabla th {
    padding: 15px 17px;
    font-size: 11px;
    font-weight: 700;
    white-space: nowrap;
}

.tabla td {
    padding: 17px;
    border-bottom: 1px solid #edf0f5;
    color: #475569;
    vertical-align: middle;
}

.tabla tbody tr {
    transition: background 0.2s;
}

.tabla tbody tr:hover {
    background: var(--fc-background);
}

.codigo {
    color: #64748b;
    font-size: 11px;
    font-weight: 600;
    white-space: nowrap;
}

.medicamento-nombre {
    display: flex;
    align-items: center;
    gap: 10px;
    min-width: 165px;
}

.medicamento-icono {
    display: grid;
    place-items: center;
    width: 34px;
    height: 34px;
    flex-shrink: 0;
    border-radius: 9px;
    background: var(--fc-light);
    color: var(--fc-accent);
}

.medicamento-icono svg {
    width: 18px;
    height: 18px;
}

.medicamento-nombre strong {
    display: block;
    color: var(--fc-primary);
    font-size: 12px;
    font-weight: 650;
}

.medicamento-nombre small {
    display: block;
    margin-top: 4px;
    color: #94a3b8;
    font-size: 10px;
}

.precio {
    color: var(--fc-primary);
    font-weight: 700;
    white-space: nowrap;
}

.sin-precio {
    color: #b45309;
    font-size: 11px;
}

/* ESTADOS */

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

.estado-verde {
    background: #ecfdf5;
    color: #047857;
}

.estado-rojo {
    background: #fef2f2;
    color: #b91c1c;
}

.estado-naranja {
    background: #fff7ed;
    color: #c2410c;
}

.estado-gris {
    background: #f1f5f9;
    color: #64748b;
}

/* ACCIONES */

.acciones-tabla {
    display: flex;
    flex-wrap: wrap;
    gap: 7px;
    min-width: 105px;
}

.boton-editar,
.boton-estado {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 5px;
    padding: 7px 10px;
    border-radius: 7px;
    font-size: 11px;
    font-weight: 600;
}

.boton-editar {
    background: var(--fc-light);
    color: var(--fc-accent);
    border: 1px solid var(--fc-border);
}

.boton-editar svg {
    width: 13px;
    height: 13px;
}

.boton-editar:hover {
    background: var(--fc-accent);
    color: white;
}

.boton-estado.desactivar {
    background: #fff;
    border: 1px solid #fecaca;
    color: #b91c1c;
}

.boton-estado.desactivar:hover:not(:disabled) {
    background: #fef2f2;
}

.boton-estado.activar {
    background: #fff;
    border: 1px solid #bbf7d0;
    color: #047857;
}

.boton-estado.activar:hover:not(:disabled) {
    background: #ecfdf5;
}

.tabla-inferior {
    padding: 16px 22px;
    color: #94a3b8;
    font-size: 11px;
}

/* SIN RESULTADOS */

.sin-resultados {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 12px;
    padding: 40px 20px;
    text-align: center;
}

.icono-vacio {
    display: grid;
    place-items: center;
    width: 55px;
    height: 55px;
    border-radius: 15px;
    background: var(--fc-light);
    color: var(--fc-accent);
}

.sin-resultados strong {
    color: var(--fc-primary);
    font-size: 15px;
}

.sin-resultados p {
    color: #94a3b8;
    font-size: 12px;
}

/* ANIMACIONES */

.v-enter-active,
.v-leave-active {
    transition: opacity 0.25s, transform 0.25s;
}

.v-enter-from,
.v-leave-to {
    opacity: 0;
    transform: translateY(-8px);
}

/* RESPONSIVE */

@media (max-width: 1250px) {
    .indicadores {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .campos-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (max-width: 600px) {
    .indicadores {
        gap: 10px;
    }

    .tarjeta-indicador {
        padding: 13px;
    }

    .indicador-superior {
        align-items: flex-start;
    }

    .indicador-superior > span {
        font-size: 10px;
    }

    .icono-indicador {
        width: 30px;
        height: 30px;
    }

    .cantidad {
        font-size: 26px;
    }

    .campos-grid {
        grid-template-columns: 1fr;
    }

    .tarjeta-encabezado,
    .formulario,
    .filtros {
        padding: 16px;
    }

    .filtros select {
        flex: 1;
        min-width: 120px;
    }

    .buscador {
        flex-basis: 100%;
    }

    .acciones-formulario {
        width: 100%;
    }

    .acciones-formulario button {
        flex: 1;
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