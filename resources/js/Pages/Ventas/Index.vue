<script setup>
import { computed, ref, nextTick } from 'vue'
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3'
import FarmaLayout from '../../Layouts/FarmaLayout.vue'

const props = defineProps({
    ventas: {
        type: Array,
        default: () => [],
    },
    medicamentos: {
        type: Array,
        default: () => [],
    },
    flash: {
        type: Object,
        default: () => ({}),
    },
})

const page = usePage()

const busqueda = ref('')
const filtroTipo = ref('todos')
const formularioRef = ref(null)
const historialRef = ref(null)

// FORMULARIO

const nuevoItem = () => ({
    medicamento_id: '',
    cantidad: 1,
})

const form = useForm({
    tipo_comprobante: 'boleta',
    items: [nuevoItem()],
})

const mensajeExito = computed(() =>
    props.flash?.success ||
    page.props.flash?.success ||
    ''
)

// ICONOS

const iconos = {
    venta: 'M3 3h2l3 12h12l3-9H6 M9 20h.01 M19 20h.01',
    dinero: 'M3 5h18v14H3z M12 9a3 3 0 1 0 0 6a3 3 0 1 0 0-6 M6 12h.01 M18 12h.01',
    medicamento: 'M8 4a5 5 0 0 1 7 0l5 5a5 5 0 0 1-7 7l-5-5a5 5 0 0 1 0-7z M10 13l7-7',
    agregar: 'M12 5v14 M5 12h14',
    eliminar: 'M3 6h18 M9 6V3h6v3 M5 6l1 15h12l1-15 M10 10v7 M14 10v7',
    documento: 'M5 3h10l4 4v14H5z M14 3v5h5 M8 12h8 M8 16h8',
    buscar: 'M16 16l5 5 M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0z',
    check: 'M20 6L9 17l-5-5',
    historial: 'M3 4v6h6 M3 10a9 9 0 1 1 1 8 M12 7v5l4 2',
    escudo: 'M12 3l8 3v6c0 5-8 9-8 9s-8-4-8-9V6z M8 12l3 3 5-6',
    usuario: 'M16 7a4 4 0 1 1-8 0 4 4 0 0 1 8 0z M4 21v-2a8 8 0 0 1 16 0v2',
    informacion: 'M22 12a10 10 0 1 1-20 0 10 10 0 0 1 20 0z M12 11v6 M12 7h.01',
    flecha: 'M4 12h16 M14 6l6 6-6 6',
}

// FORMATO DE DATOS

const dinero = valor =>
    new Intl.NumberFormat('es-CL', {
        style: 'currency',
        currency: 'CLP',
        minimumFractionDigits: 0,
        maximumFractionDigits: 2,
    }).format(Number(valor) || 0)

const numero = valor =>
    Number(valor || 0).toLocaleString('es-CL')

const fecha = valor => {
    if (!valor) return '—'

    const resultado = new Date(valor)

    if (Number.isNaN(resultado.getTime())) {
        return '—'
    }

    return resultado.toLocaleString('es-CL', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    })
}

// MEDICAMENTOS Y PRECIOS

function medicamentoSeleccionado(id) {
    return props.medicamentos.find(
        medicamento =>
            String(medicamento.id) === String(id)
    )
}

function precioCentavos(item) {
    const medicamento = medicamentoSeleccionado(
        item.medicamento_id
    )

    if (
        !medicamento ||
        medicamento.precio_venta === null ||
        medicamento.precio_venta === undefined ||
        medicamento.precio_venta === ''
    ) {
        return null
    }

    const precio = Number(medicamento.precio_venta)

    if (!Number.isFinite(precio) || precio <= 0) {
        return null
    }

    return Math.round(precio * 100)
}

function subtotalCentavos(item) {
    const cantidad = Number(item.cantidad)
    const precio = precioCentavos(item)

    if (
        precio === null ||
        !Number.isInteger(cantidad) ||
        cantidad < 1
    ) {
        return 0
    }

    return cantidad * precio
}

function subtotal(item) {
    return subtotalCentavos(item) / 100
}

const totalCentavos = computed(() =>
    form.items.reduce(
        (suma, item) => suma + subtotalCentavos(item),
        0
    )
)

const total = computed(() =>
    totalCentavos.value / 100
)

const itemsSeleccionados = computed(() =>
    form.items.filter(
        item => item.medicamento_id !== ''
    )
)

const unidadesSeleccionadas = computed(() =>
    itemsSeleccionados.value.reduce(
        (suma, item) => {
            const cantidad = Number(item.cantidad)

            return suma + (
                Number.isInteger(cantidad) &&
                cantidad > 0
                    ? cantidad
                    : 0
            )
        },
        0
    )
)

// VALIDACIÓN DE VENTA

const ventaValida = computed(() =>
    form.items.length > 0 &&

    ['boleta', 'factura'].includes(
        form.tipo_comprobante
    ) &&

    form.items.every(item =>
        item.medicamento_id !== '' &&
        Number.isInteger(Number(item.cantidad)) &&
        Number(item.cantidad) > 0 &&
        precioCentavos(item) !== null
    ) &&

    new Set(
        form.items.map(
            item => String(item.medicamento_id)
        )
    ).size === form.items.length &&

    Number.isSafeInteger(totalCentavos.value) &&
    totalCentavos.value > 0 &&
    totalCentavos.value <= 999999999999
)

// INDICADORES

const totalUnidades = computed(() =>
    props.ventas.reduce(
        (suma, venta) =>
            suma + (venta.detalles || []).reduce(
                (cantidad, detalle) =>
                    cantidad + Number(detalle.cantidad || 0),
                0
            ),
        0
    )
)

const montoAcumulado = computed(() =>
    props.ventas.reduce(
        (suma, venta) =>
            suma + Number(venta.total || 0),
        0
    )
)

const indicadores = computed(() => [
    {
        titulo: 'Ventas registradas',
        valor: numero(props.ventas.length),
        descripcion: 'Operaciones de venta simuladas',
        icono: 'venta',
        color: 'principal',
    },
    {
        titulo: 'Unidades vendidas',
        valor: numero(totalUnidades.value),
        descripcion: 'Medicamentos retirados del inventario',
        icono: 'medicamento',
        color: 'azul',
    },
    {
        titulo: 'Monto acumulado',
        valor: dinero(montoAcumulado.value),
        descripcion: 'Total de ventas simuladas',
        icono: 'dinero',
        color: 'verde',
    },
])

// HISTORIAL

function tipoComprobante(venta) {
    return venta.tipo_comprobante === 'factura'
        ? 'Factura simulada'
        : 'Boleta simulada'
}

function resumenMedicamentos(venta) {
    const detalles = venta.detalles || []

    if (!detalles.length) return 'Sin detalles'

    return detalles.map(detalle => {
        const nombre =
            detalle.medicamento?.nombre || 'Medicamento'

        return `${nombre} (${detalle.cantidad})`
    }).join(', ')
}

function cantidadVenta(venta) {
    return (venta.detalles || []).reduce(
        (suma, detalle) =>
            suma + Number(detalle.cantidad || 0),
        0
    )
}

function normalizar(texto) {
    return String(texto ?? '')
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .toLowerCase()
        .trim()
}

const ventasFiltradas = computed(() => {
    const termino = normalizar(busqueda.value)

    return props.ventas.filter(venta => {
        const coincideBusqueda = [
            venta.id,
            `#${venta.id}`,
            resumenMedicamentos(venta),
            venta.usuario?.name,
            tipoComprobante(venta),
            fecha(venta.fecha_venta),
        ].some(valor =>
            normalizar(valor).includes(termino)
        )

        const coincideTipo =
            filtroTipo.value === 'todos' ||
            venta.tipo_comprobante === filtroTipo.value

        return coincideBusqueda && coincideTipo
    })
})

function limpiarFiltros() {
    busqueda.value = ''
    filtroTipo.value = 'todos'
}

// ACCIONES DEL FORMULARIO

function medicamentoOcupado(id, indiceActual) {
    return form.items.some(
        (item, indice) =>
            indice !== indiceActual &&
            String(item.medicamento_id) === String(id)
    )
}

async function agregarMedicamento() {
    if (
        form.processing ||
        form.items.length >= props.medicamentos.length
    ) {
        return
    }

    form.items.push(nuevoItem())
    form.clearErrors()

    await nextTick()

    document.getElementById(
        `medicamento-${form.items.length - 1}`
    )?.focus()
}

function quitarMedicamento(indice) {
    if (
        form.processing ||
        form.items.length <= 1
    ) return

    form.items.splice(indice, 1)
    form.clearErrors()
}

function limpiarVenta() {
    if (form.processing) return

    if (
        itemsSeleccionados.value.length > 0 &&
        !window.confirm(
            '¿Deseas limpiar los medicamentos de esta venta?'
        )
    ) {
        return
    }

    form.reset()
    form.items = [nuevoItem()]
    form.tipo_comprobante = 'boleta'
    form.clearErrors()
}

function registrarVenta() {
    if (form.processing || !ventaValida.value) {
        return
    }

    form.clearErrors()

    form.post('/ventas', {
        preserveScroll: true,

        onSuccess: () => {
            form.reset()
            form.items = [nuevoItem()]
            form.tipo_comprobante = 'boleta'
            form.clearErrors()
        },
    })
}

function verComprobante(venta) {
    router.visit(`/ventas/${venta.id}/comprobante`)
}

function irFormulario() {
    formularioRef.value?.scrollIntoView({
        behavior: 'smooth',
        block: 'start',
    })
}

function irHistorial() {
    historialRef.value?.scrollIntoView({
        behavior: 'smooth',
        block: 'start',
    })
}
</script>

<template>
    <Head title="Ventas | FarmaControl" />

    <FarmaLayout
        titulo="Ventas simuladas"
        subtitulo="Punto de venta, comprobantes académicos y control de inventario."
    >
        <template #acciones>
            <div class="acciones-encabezado">
                <button
                    type="button"
                    class="boton-secundario"
                    @click="irHistorial"
                >
                    <svg viewBox="0 0 24 24">
                        <path :d="iconos.historial" />
                    </svg>

                    Historial
                </button>

                <button
                    type="button"
                    class="boton-principal"
                    @click="irFormulario"
                >
                    <svg viewBox="0 0 24 24">
                        <path :d="iconos.agregar" />
                    </svg>

                    Nueva venta
                </button>
            </div>
        </template>

        <div class="modulo-ventas">

            <!-- MENSAJE DE ÉXITO -->

            <Transition>
                <div
                    v-if="mensajeExito"
                    class="mensaje-exito"
                    role="status"
                >
                    <div class="icono-exito">
                        <svg viewBox="0 0 24 24">
                            <path :d="iconos.check" />
                        </svg>
                    </div>

                    <div>
                        <strong>Operación completada</strong>
                        <p>{{ mensajeExito }}</p>
                    </div>
                </div>
            </Transition>

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

                    <strong class="valor-indicador">
                        {{ indicador.valor }}
                    </strong>

                    <p>{{ indicador.descripcion }}</p>
                </article>
            </section>

            <!-- PUNTO DE VENTA -->

            <form
                ref="formularioRef"
                class="punto-venta"
                @submit.prevent="registrarVenta"
            >

                <!-- PANEL DE MEDICAMENTOS -->

                <section class="tarjeta panel-productos">

                    <div class="tarjeta-encabezado">
                        <div class="titulo-con-icono">
                            <div class="icono-seccion">
                                <svg viewBox="0 0 24 24">
                                    <path :d="iconos.venta" />
                                </svg>
                            </div>

                            <div>
                                <h2>Registrar nueva venta</h2>

                                <p>
                                    Selecciona los medicamentos
                                    de la operación.
                                </p>
                            </div>
                        </div>

                        <button
                            type="button"
                            class="boton-limpiar"
                            :disabled="form.processing"
                            @click="limpiarVenta"
                        >
                            Limpiar venta
                        </button>
                    </div>

                    <div class="contenido-productos">

                        <!-- COMPROBANTE -->

                        <div class="campo">
                            <label>
                                Tipo de comprobante
                            </label>

                            <div class="tipos-comprobante">
                                <button
                                    v-for="tipo in ['boleta', 'factura']"
                                    :key="tipo"
                                    type="button"
                                    class="opcion-comprobante"
                                    :class="{
                                        seleccionado:
                                            form.tipo_comprobante === tipo
                                    }"
                                    :aria-pressed="
                                        form.tipo_comprobante === tipo
                                    "
                                    :disabled="form.processing"
                                    @click="form.tipo_comprobante = tipo"
                                >
                                    <svg viewBox="0 0 24 24">
                                        <path :d="iconos.documento" />
                                    </svg>

                                    <div>
                                        <strong>
                                            {{
                                                tipo === 'boleta'
                                                    ? 'Boleta'
                                                    : 'Factura'
                                            }}
                                        </strong>

                                        <small>
                                            Documento simulado
                                        </small>
                                    </div>

                                    <span class="radio-visual"></span>
                                </button>
                            </div>

                            <p
                                v-if="form.errors.tipo_comprobante"
                                class="error"
                            >
                                {{ form.errors.tipo_comprobante }}
                            </p>
                        </div>

                        <div class="separador">
                            <h3>Medicamentos de la venta</h3>

                            <span>
                                {{ form.items.length }}
                                producto(s)
                            </span>
                        </div>

                        <!-- LISTADO DE PRODUCTOS -->

                        <div
                            v-for="(item, indice) in form.items"
                            :key="indice"
                            class="producto"
                        >
                            <div class="producto-encabezado">
                                <div class="producto-titulo">
                                    <span class="numero-producto">
                                        {{ indice + 1 }}
                                    </span>

                                    <strong>
                                        Medicamento {{ indice + 1 }}
                                    </strong>
                                </div>

                                <button
                                    v-if="form.items.length > 1"
                                    type="button"
                                    class="boton-eliminar"
                                    :disabled="form.processing"
                                    @click="quitarMedicamento(indice)"
                                >
                                    <svg viewBox="0 0 24 24">
                                        <path :d="iconos.eliminar" />
                                    </svg>

                                    Quitar
                                </button>
                            </div>

                            <div class="campos-producto">

                                <!-- MEDICAMENTO -->

                                <div class="campo campo-medicamento">
                                    <label
                                        :for="`medicamento-${indice}`"
                                    >
                                        Medicamento
                                        <span class="obligatorio">*</span>
                                    </label>

                                    <select
                                        :id="`medicamento-${indice}`"
                                        v-model="item.medicamento_id"
                                        :disabled="form.processing"
                                        required
                                        :class="{
                                            'input-error':
                                                form.errors[
                                                    `items.${indice}.medicamento_id`
                                                ]
                                        }"
                                    >
                                        <option value="">
                                            Seleccionar medicamento
                                        </option>

                                        <option
                                            v-for="medicamento in medicamentos"
                                            :key="medicamento.id"
                                            :value="medicamento.id"
                                            :disabled="
                                                medicamentoOcupado(
                                                    medicamento.id,
                                                    indice
                                                )
                                            "
                                        >
                                            {{ medicamento.nombre }}
                                            ({{ medicamento.codigo }})
                                        </option>
                                    </select>

                                    <p
                                        v-if="
                                            form.errors[
                                                `items.${indice}.medicamento_id`
                                            ]
                                        "
                                        class="error"
                                    >
                                        {{
                                            form.errors[
                                                `items.${indice}.medicamento_id`
                                            ]
                                        }}
                                    </p>
                                </div>

                                <!-- CANTIDAD -->

                                <div class="campo">
                                    <label
                                        :for="`cantidad-${indice}`"
                                    >
                                        Cantidad
                                        <span class="obligatorio">*</span>
                                    </label>

                                    <input
                                        :id="`cantidad-${indice}`"
                                        v-model.number="item.cantidad"
                                        :disabled="form.processing"
                                        type="number"
                                        min="1"
                                        step="1"
                                        required
                                        :class="{
                                            'input-error':
                                                form.errors[
                                                    `items.${indice}.cantidad`
                                                ]
                                        }"
                                    />

                                    <p
                                        v-if="
                                            form.errors[
                                                `items.${indice}.cantidad`
                                            ]
                                        "
                                        class="error"
                                    >
                                        {{
                                            form.errors[
                                                `items.${indice}.cantidad`
                                            ]
                                        }}
                                    </p>
                                </div>

                                <!-- PRECIO AUTOMÁTICO -->

                                <div class="campo">
                                    <label>Precio unitario</label>

                                    <div class="precio-unitario">
                                        {{
                                            !item.medicamento_id
                                                ? '—'
                                                : precioCentavos(item) === null
                                                    ? 'Sin precio'
                                                    : dinero(
                                                        precioCentavos(item) / 100
                                                    )
                                        }}
                                    </div>

                                    <small class="texto-ayuda">
                                        Precio del catálogo
                                    </small>
                                </div>
                            </div>

                            <!-- ALERTA DE PRECIO -->

                            <div
                                v-if="
                                    item.medicamento_id &&
                                    precioCentavos(item) === null
                                "
                                class="alerta-precio"
                            >
                                <svg viewBox="0 0 24 24">
                                    <path :d="iconos.informacion" />
                                </svg>

                                <div>
                                    Este medicamento no tiene un
                                    precio de venta válido.

                                    <Link
                                        href="/medicamentos"
                                        class="enlace"
                                    >
                                        Consultar catálogo
                                    </Link>
                                </div>
                            </div>

                            <!-- SUBTOTAL -->

                            <div class="producto-inferior">
                                <span>
                                    {{
                                        medicamentoSeleccionado(
                                            item.medicamento_id
                                        )?.nombre ||
                                        'Medicamento no seleccionado'
                                    }}
                                </span>

                                <div>
                                    <small>Subtotal</small>

                                    <strong>
                                        {{ dinero(subtotal(item)) }}
                                    </strong>
                                </div>
                            </div>
                        </div>

                        <!-- ERRORES GENERALES -->

                        <div
                            v-if="form.errors.items"
                            class="alerta-error"
                            role="alert"
                        >
                            {{ form.errors.items }}
                        </div>

                        <div
                            v-if="medicamentos.length === 0"
                            class="alerta-precio"
                        >
                            No hay medicamentos disponibles
                            para registrar una venta.
                        </div>

                        <!-- AGREGAR PRODUCTO -->

                        <button
                            type="button"
                            class="boton-agregar"
                            :disabled="
                                form.processing ||
                                form.items.length >= medicamentos.length
                            "
                            @click="agregarMedicamento"
                        >
                            <svg viewBox="0 0 24 24">
                                <path :d="iconos.agregar" />
                            </svg>

                            Agregar otro medicamento
                        </button>

                        <p class="texto-ayuda">
                            Los precios se obtienen automáticamente
                            del catálogo y no pueden modificarse
                            durante la venta.
                        </p>
                    </div>
                </section>

                <!-- RESUMEN DE VENTA -->

                <aside class="panel-resumen">

                    <section class="tarjeta resumen">

                        <div class="resumen-encabezado">
                            <div class="icono-resumen">
                                <svg viewBox="0 0 24 24">
                                    <path :d="iconos.documento" />
                                </svg>
                            </div>

                            <div>
                                <h2>Resumen de venta</h2>

                                <p>
                                    {{
                                        form.tipo_comprobante === 'factura'
                                            ? 'Factura simulada'
                                            : 'Boleta simulada'
                                    }}
                                </p>
                            </div>
                        </div>

                        <div class="resumen-contenido">

                            <div
                                v-if="itemsSeleccionados.length === 0"
                                class="carrito-vacio"
                            >
                                <div class="icono-vacio">
                                    <svg viewBox="0 0 24 24">
                                        <path :d="iconos.venta" />
                                    </svg>
                                </div>

                                <strong>Tu venta está vacía</strong>

                                <p>
                                    Selecciona medicamentos
                                    para comenzar.
                                </p>
                            </div>

                            <div
                                v-else
                                class="resumen-productos"
                            >
                                <div
                                    v-for="(item, indice) in itemsSeleccionados"
                                    :key="indice"
                                    class="resumen-producto"
                                >
                                    <div>
                                        <strong>
                                            {{
                                                medicamentoSeleccionado(
                                                    item.medicamento_id
                                                )?.nombre ||
                                                'Medicamento'
                                            }}
                                        </strong>

                                        <small>
                                            {{ item.cantidad }} unidad(es)

                                            <template
                                                v-if="precioCentavos(item) !== null"
                                            >
                                                ×
                                                {{
                                                    dinero(
                                                        precioCentavos(item) / 100
                                                    )
                                                }}
                                            </template>
                                        </small>
                                    </div>

                                    <strong class="importe">
                                        {{ dinero(subtotal(item)) }}
                                    </strong>
                                </div>
                            </div>

                            <div class="resumen-detalles">
                                <div>
                                    <span>Medicamentos</span>

                                    <strong>
                                        {{ itemsSeleccionados.length }}
                                    </strong>
                                </div>

                                <div>
                                    <span>Total de unidades</span>

                                    <strong>
                                        {{ unidadesSeleccionadas }}
                                    </strong>
                                </div>
                            </div>

                            <div
                                class="total-venta"
                                aria-live="polite"
                            >
                                <span>Total estimado</span>

                                <strong>{{ dinero(total) }}</strong>

                                <small>Pesos chilenos (CLP)</small>
                            </div>

                            <button
                                type="submit"
                                class="boton-confirmar"
                                :disabled="
                                    form.processing ||
                                    !ventaValida
                                "
                            >
                                <span
                                    v-if="form.processing"
                                    class="spinner"
                                ></span>

                                <svg
                                    v-else
                                    viewBox="0 0 24 24"
                                >
                                    <path :d="iconos.check" />
                                </svg>

                                {{
                                    form.processing
                                        ? 'Registrando venta...'
                                        : 'Registrar venta simulada'
                                }}
                            </button>

                            <p
                                v-if="!ventaValida"
                                class="ayuda-resumen"
                            >
                                Selecciona medicamentos con precio
                                válido e indica cantidades correctas.
                            </p>

                            <div class="aviso-academico">
                                <svg viewBox="0 0 24 24">
                                    <path :d="iconos.escudo" />
                                </svg>

                                <div>
                                    <strong>Simulación académica</strong>

                                    <p>
                                        Documento sin validez
                                        tributaria.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- INFORMACIÓN FEFO -->

                    <section class="panel-fefo">
                        <div class="icono-seccion">
                            <svg viewBox="0 0 24 24">
                                <path :d="iconos.escudo" />
                            </svg>
                        </div>

                        <div>
                            <h3>Control de inventario FEFO</h3>

                            <p>
                                El sistema validará el stock
                                disponible y priorizará los lotes
                                vigentes con vencimiento más próximo.
                            </p>

                            <Link
                                href="/lotes"
                                class="enlace"
                            >
                                Consultar inventario

                                <svg viewBox="0 0 24 24">
                                    <path :d="iconos.flecha" />
                                </svg>
                            </Link>
                        </div>
                    </section>
                </aside>
            </form>

            <!-- HISTORIAL DE VENTAS -->

            <section
                ref="historialRef"
                class="tarjeta historial"
            >
                <div class="tarjeta-encabezado">
                    <div class="titulo-con-icono">
                        <div class="icono-seccion">
                            <svg viewBox="0 0 24 24">
                                <path :d="iconos.historial" />
                            </svg>
                        </div>

                        <div>
                            <h2>Historial de ventas</h2>

                            <p>
                                Consulta las operaciones
                                y sus comprobantes simulados.
                            </p>
                        </div>
                    </div>

                    <span class="contador">
                        {{ ventas.length }} registros
                    </span>
                </div>

                <!-- FILTROS -->

                <div class="filtros">
                    <div class="buscador">
                        <svg viewBox="0 0 24 24">
                            <path :d="iconos.buscar" />
                        </svg>

                        <input
                            v-model="busqueda"
                            type="search"
                            aria-label="Buscar ventas"
                            placeholder="Buscar venta, medicamento, usuario..."
                        />
                    </div>

                    <select
                        v-model="filtroTipo"
                        aria-label="Tipo de comprobante"
                    >
                        <option value="todos">
                            Todos los comprobantes
                        </option>

                        <option value="boleta">
                            Boletas simuladas
                        </option>

                        <option value="factura">
                            Facturas simuladas
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
                                <th>Venta</th>
                                <th>Fecha y hora</th>
                                <th>Comprobante</th>
                                <th>Medicamentos</th>
                                <th>Unidades</th>
                                <th>Total</th>
                                <th>Responsable</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr
                                v-for="venta in ventasFiltradas"
                                :key="venta.id"
                            >
                                <td>
                                    <strong class="numero-venta">
                                        #{{ venta.id }}
                                    </strong>
                                </td>

                                <td class="fecha">
                                    {{ fecha(venta.fecha_venta) }}
                                </td>

                                <td>
                                    <span
                                        class="tipo-documento"
                                        :class="
                                            venta.tipo_comprobante === 'factura'
                                                ? 'factura'
                                                : 'boleta'
                                        "
                                    >
                                        <svg viewBox="0 0 24 24">
                                            <path :d="iconos.documento" />
                                        </svg>

                                        {{ tipoComprobante(venta) }}
                                    </span>
                                </td>

                                <td class="medicamentos-tabla">
                                    <div
                                        v-for="(detalle, indice) in (venta.detalles || [])"
                                        :key="indice"
                                        class="detalle-medicamento"
                                    >
                                        <span>
                                            {{
                                                detalle.medicamento?.nombre ||
                                                'Medicamento'
                                            }}
                                        </span>

                                        <small>
                                            × {{ detalle.cantidad }}
                                        </small>
                                    </div>

                                    <span
                                        v-if="!(venta.detalles || []).length"
                                    >
                                        Sin detalles
                                    </span>
                                </td>

                                <td>
                                    <span class="unidades-tabla">
                                        {{ cantidadVenta(venta) }}
                                    </span>
                                </td>

                                <td>
                                    <strong class="total-tabla">
                                        {{ dinero(venta.total) }}
                                    </strong>
                                </td>

                                <td>
                                    <div class="responsable">
                                        <div class="usuario-icono">
                                            <svg viewBox="0 0 24 24">
                                                <path :d="iconos.usuario" />
                                            </svg>
                                        </div>

                                        <span>
                                            {{
                                                venta.usuario?.name ||
                                                'No disponible'
                                            }}
                                        </span>
                                    </div>
                                </td>

                                <td>
                                    <button
                                        type="button"
                                        class="boton-comprobante"
                                        @click="verComprobante(venta)"
                                    >
                                        <svg viewBox="0 0 24 24">
                                            <path :d="iconos.documento" />
                                        </svg>

                                        Ver comprobante
                                    </button>
                                </td>
                            </tr>

                            <!-- SIN RESULTADOS -->

                            <tr v-if="ventasFiltradas.length === 0">
                                <td colspan="8">
                                    <div class="sin-resultados">
                                        <div class="icono-vacio">
                                            <svg viewBox="0 0 24 24">
                                                <path :d="iconos.historial" />
                                            </svg>
                                        </div>

                                        <strong>
                                            {{
                                                ventas.length === 0
                                                    ? 'No hay ventas registradas'
                                                    : 'No se encontraron resultados'
                                            }}
                                        </strong>

                                        <p>
                                            {{
                                                ventas.length === 0
                                                    ? 'Registra una venta para comenzar.'
                                                    : 'Modifica los filtros de búsqueda.'
                                            }}
                                        </p>

                                        <button
                                            v-if="ventas.length > 0"
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
                    Mostrando {{ ventasFiltradas.length }}
                    de {{ ventas.length }} ventas
                </div>
            </section>
        </div>
    </FarmaLayout>
</template>

<style scoped>
.modulo-ventas {
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

.acciones-encabezado {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}

/* BOTONES */

.boton-principal,
.boton-secundario {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 11px 17px;
    border-radius: 9px;
    font-size: 12px;
    font-weight: 600;
}

.boton-principal {
    background: var(--fc-accent);
    color: white;
}

.boton-principal:hover:not(:disabled) {
    background: var(--fc-hover);
    box-shadow: 0 4px 12px rgba(15, 23, 42, 0.12);
}

.boton-secundario {
    border: 1px solid #e2e8f0;
    background: white;
    color: #475569;
}

.boton-secundario:hover:not(:disabled) {
    background: var(--fc-light);
    border-color: var(--fc-border);
    color: var(--fc-primary);
}

.boton-principal svg,
.boton-secundario svg {
    width: 17px;
    height: 17px;
}

.boton-limpiar {
    color: var(--fc-accent);
    font-size: 12px;
    font-weight: 600;
}

.boton-limpiar:hover:not(:disabled) {
    text-decoration: underline;
}

/* MENSAJE DE ÉXITO */

.mensaje-exito {
    display: flex;
    align-items: center;
    gap: 13px;
    padding: 17px;
    border: 1px solid #bbf7d0;
    border-radius: 11px;
    background: #ecfdf5;
    color: #166534;
}

.icono-exito {
    display: grid;
    place-items: center;
    width: 36px;
    height: 36px;
    flex-shrink: 0;
    border-radius: 50%;
    background: #d1fae5;
}

.mensaje-exito strong {
    font-size: 13px;
}

.mensaje-exito p {
    margin-top: 3px;
    font-size: 12px;
}

/* INDICADORES */

.indicadores {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 17px;
}

.tarjeta-indicador {
    min-width: 0;
    padding: 22px;
    border: 1px solid var(--fc-border);
    border-radius: 14px;
    background: white;
    box-shadow: 0 3px 15px rgba(15, 23, 42, 0.03);
    transition: transform 0.2s, box-shadow 0.2s;
}

.tarjeta-indicador:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(15, 23, 42, 0.07);
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
}

.icono-indicador {
    display: grid;
    place-items: center;
    width: 42px;
    height: 42px;
    flex-shrink: 0;
    border-radius: 11px;
}

.icono-indicador.principal {
    background: var(--fc-light);
    color: var(--fc-accent);
}

.icono-indicador.azul {
    background: #eff6ff;
    color: #2563eb;
}

.icono-indicador.verde {
    background: #ecfdf5;
    color: #059669;
}

.valor-indicador {
    display: block;
    margin: 14px 0 6px;
    color: var(--fc-primary);
    font-size: clamp(24px, 2.5vw, 34px);
    font-weight: 750;
    letter-spacing: -1px;
    overflow-wrap: anywhere;
}

.tarjeta-indicador p {
    color: #64748b;
    font-size: 11px;
    line-height: 1.6;
}

/* PUNTO DE VENTA */

.punto-venta {
    display: grid;
    grid-template-columns: minmax(0, 1fr) minmax(270px, 330px);
    align-items: start;
    gap: 22px;
    scroll-margin-top: 95px;
}

.tarjeta {
    min-width: 0;
    overflow: hidden;
    border: 1px solid var(--fc-border);
    border-radius: 14px;
    background: white;
    box-shadow: 0 3px 15px rgba(15, 23, 42, 0.03);
}

.tarjeta-encabezado {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 15px;
    padding: 22px;
    border-bottom: 1px solid #edf0f5;
}

.titulo-con-icono {
    display: flex;
    align-items: center;
    gap: 12px;
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
    font-size: 11px;
    line-height: 1.6;
}

.contenido-productos {
    padding: 22px;
}

/* CAMPOS */

.campo {
    min-width: 0;
}

.campo label {
    display: block;
    margin-bottom: 9px;
    color: #475569;
    font-size: 12px;
    font-weight: 600;
}

.obligatorio {
    color: #dc2626;
}

.campo select,
.campo input,
.filtros select {
    width: 100%;
    min-height: 43px;
    padding: 10px 12px;
    border: 1px solid #dbe2ea;
    border-radius: 8px;
    background: white;
    color: #334155;
    font-size: 12px;
    outline: none;
    transition: border-color 0.2s, box-shadow 0.2s;
}

.campo select:focus,
.campo input:focus,
.filtros select:focus {
    border-color: var(--fc-accent);
    box-shadow: 0 0 0 3px var(--fc-light);
}

.campo input:disabled,
.campo select:disabled {
    opacity: 0.65;
}

.campo .input-error {
    border-color: #ef4444;
}

.texto-ayuda {
    display: block;
    margin-top: 8px;
    color: #64748b;
    font-size: 10px;
    line-height: 1.7;
}

.error {
    margin-top: 7px;
    color: #dc2626;
    font-size: 11px;
    line-height: 1.6;
}

/* TIPO DE COMPROBANTE */

.tipos-comprobante {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 12px;
}

.opcion-comprobante {
    display: flex;
    align-items: center;
    gap: 10px;
    min-width: 0;
    padding: 15px 12px;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    background: white;
    color: #64748b;
    text-align: left;
}

.opcion-comprobante:hover:not(:disabled) {
    border-color: var(--fc-accent);
}

.opcion-comprobante.seleccionado {
    border-color: var(--fc-accent);
    background: var(--fc-light);
    color: var(--fc-accent);
    box-shadow: 0 0 0 1px var(--fc-accent);
}

.opcion-comprobante strong {
    display: block;
    color: var(--fc-primary);
    font-size: 13px;
    font-weight: 650;
}

.opcion-comprobante small {
    display: block;
    margin-top: 3px;
    color: #64748b;
    font-size: 9px;
}

.radio-visual {
    width: 15px;
    height: 15px;
    margin-left: auto;
    flex-shrink: 0;
    border: 1px solid #cbd5e1;
    border-radius: 50%;
    background: white;
}

.seleccionado .radio-visual {
    border: 4px solid var(--fc-accent);
}

/* MEDICAMENTOS */

.separador {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    margin: 25px 0 14px;
}

.separador h3 {
    color: var(--fc-primary);
    font-size: 13px;
    font-weight: 700;
}

.separador span {
    color: #64748b;
    font-size: 11px;
}

.producto {
    margin-bottom: 14px;
    padding: 17px;
    border: 1px solid #e2e8f0;
    border-radius: 11px;
    background: #f8fafc;
}

.producto-encabezado {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    margin-bottom: 17px;
}

.producto-titulo {
    display: flex;
    align-items: center;
    gap: 9px;
    color: var(--fc-primary);
    font-size: 12px;
}

.numero-producto {
    display: grid;
    place-items: center;
    width: 25px;
    height: 25px;
    border-radius: 7px;
    background: var(--fc-light);
    color: var(--fc-accent);
    font-size: 11px;
    font-weight: 700;
}

.boton-eliminar {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 5px 8px;
    border-radius: 6px;
    color: #dc2626;
    font-size: 11px;
    font-weight: 600;
}

.boton-eliminar svg {
    width: 15px;
    height: 15px;
}

.boton-eliminar:hover:not(:disabled) {
    background: #fee2e2;
}

.campos-producto {
    display: grid;
    grid-template-columns:
        minmax(0, 2fr)
        minmax(70px, 0.8fr)
        minmax(95px, 1fr);
    align-items: start;
    gap: 12px;
}

.precio-unitario {
    display: flex;
    align-items: center;
    min-height: 43px;
    padding: 10px 12px;
    border: 1px solid var(--fc-border);
    border-radius: 8px;
    background: var(--fc-light);
    color: var(--fc-primary);
    font-size: 13px;
    font-weight: 700;
    overflow-wrap: anywhere;
}

.producto-inferior {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 15px;
    margin-top: 15px;
    padding-top: 14px;
    border-top: 1px solid #e2e8f0;
}

.producto-inferior > span {
    color: #64748b;
    font-size: 11px;
}

.producto-inferior > div {
    flex-shrink: 0;
    text-align: right;
}

.producto-inferior small {
    display: block;
    color: #64748b;
    font-size: 10px;
}

.producto-inferior strong {
    display: block;
    margin-top: 3px;
    color: var(--fc-primary);
    font-size: 17px;
    font-weight: 700;
}

.boton-agregar {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    width: 100%;
    padding: 13px;
    border: 1px dashed var(--fc-accent);
    border-radius: 9px;
    background: var(--fc-light);
    color: var(--fc-accent);
    font-size: 12px;
    font-weight: 600;
}

.boton-agregar:hover:not(:disabled) {
    background: white;
    box-shadow: 0 3px 12px rgba(15, 23, 42, 0.06);
}

.boton-agregar svg {
    width: 17px;
    height: 17px;
}

/* ALERTAS */

.alerta-precio {
    display: flex;
    align-items: flex-start;
    gap: 9px;
    margin: 13px 0;
    padding: 12px;
    border: 1px solid #fed7aa;
    border-radius: 8px;
    background: #fff7ed;
    color: #9a3412;
    font-size: 11px;
    line-height: 1.7;
}

.alerta-precio svg {
    width: 17px;
    height: 17px;
}

.alerta-error {
    margin: 15px 0;
    padding: 13px;
    border: 1px solid #fecaca;
    border-radius: 8px;
    background: #fef2f2;
    color: #b91c1c;
    font-size: 12px;
}

/* RESUMEN DE VENTA */

.panel-resumen {
    position: sticky;
    top: 90px;
    display: flex;
    flex-direction: column;
    gap: 17px;
    min-width: 0;
}

.resumen-encabezado {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 21px;
    background: linear-gradient(
        125deg,
        var(--fc-primary),
        var(--fc-accent)
    );
    color: white;
}

.icono-resumen {
    display: grid;
    place-items: center;
    width: 40px;
    height: 40px;
    flex-shrink: 0;
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 10px;
    background: rgba(255, 255, 255, 0.1);
}

.resumen-encabezado h2 {
    font-size: 15px;
    font-weight: 700;
}

.resumen-encabezado p {
    margin-top: 4px;
    color: rgba(255, 255, 255, 0.8);
    font-size: 11px;
}

.resumen-contenido {
    padding: 20px;
}

.carrito-vacio {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 9px;
    padding: 17px 8px 23px;
    text-align: center;
}

.icono-vacio {
    display: grid;
    place-items: center;
    width: 53px;
    height: 53px;
    margin-bottom: 4px;
    border-radius: 15px;
    background: var(--fc-light);
    color: var(--fc-accent);
}

.icono-vacio svg {
    width: 25px;
    height: 25px;
}

.carrito-vacio strong {
    color: var(--fc-primary);
    font-size: 13px;
}

.carrito-vacio p {
    color: #64748b;
    font-size: 11px;
    line-height: 1.7;
}

.resumen-productos {
    max-height: 270px;
    overflow-y: auto;
}

.resumen-producto {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    padding: 13px 0;
    border-bottom: 1px solid #edf0f5;
}

.resumen-producto > div {
    min-width: 0;
}

.resumen-producto strong {
    display: block;
    color: #334155;
    font-size: 12px;
    overflow-wrap: anywhere;
}

.resumen-producto small {
    display: block;
    margin-top: 4px;
    color: #64748b;
    font-size: 10px;
}

.resumen-producto .importe {
    flex-shrink: 0;
    color: var(--fc-primary);
    font-weight: 700;
}

.resumen-detalles {
    display: flex;
    flex-direction: column;
    gap: 12px;
    padding: 17px 0;
    border-top: 1px solid #edf0f5;
}

.resumen-detalles > div {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    color: #64748b;
    font-size: 12px;
}

.resumen-detalles strong {
    color: var(--fc-primary);
}

.total-venta {
    padding: 20px 16px;
    border: 1px solid var(--fc-border);
    border-radius: 11px;
    background: var(--fc-light);
}

.total-venta > span {
    display: block;
    color: var(--fc-primary);
    font-size: 12px;
    font-weight: 600;
}

.total-venta strong {
    display: block;
    margin: 7px 0;
    color: var(--fc-primary);
    font-size: 33px;
    font-weight: 800;
    letter-spacing: -1px;
    overflow-wrap: anywhere;
}

.total-venta small {
    color: #64748b;
    font-size: 10px;
}

.boton-confirmar {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 9px;
    width: 100%;
    margin-top: 17px;
    padding: 15px 12px;
    border-radius: 9px;
    background: var(--fc-accent);
    color: white;
    font-size: 12px;
    font-weight: 650;
}

.boton-confirmar:hover:not(:disabled) {
    background: var(--fc-hover);
    box-shadow: 0 5px 15px rgba(15, 23, 42, 0.15);
}

.ayuda-resumen {
    margin-top: 10px;
    color: #64748b;
    font-size: 10px;
    line-height: 1.7;
    text-align: center;
}

.aviso-academico {
    display: flex;
    align-items: flex-start;
    gap: 9px;
    margin-top: 20px;
    padding-top: 16px;
    border-top: 1px solid #edf0f5;
    color: #64748b;
}

.aviso-academico svg {
    width: 18px;
    height: 18px;
    color: var(--fc-accent);
}

.aviso-academico strong {
    color: #475569;
    font-size: 11px;
}

.aviso-academico p {
    margin-top: 3px;
    font-size: 10px;
}

/* PANEL FEFO */

.panel-fefo {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 18px;
    border: 1px solid var(--fc-border);
    border-radius: 12px;
    background: white;
}

.panel-fefo .icono-seccion {
    width: 35px;
    height: 35px;
}

.panel-fefo h3 {
    color: var(--fc-primary);
    font-size: 12px;
    font-weight: 700;
}

.panel-fefo p {
    margin: 6px 0 10px;
    color: #64748b;
    font-size: 11px;
    line-height: 1.8;
}

.enlace {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    color: var(--fc-accent);
    font-size: 11px;
    font-weight: 600;
}

.enlace:hover {
    text-decoration: underline;
}

.enlace svg {
    width: 14px;
    height: 14px;
}

/* HISTORIAL */

.historial {
    scroll-margin-top: 95px;
}

.contador {
    padding: 7px 12px;
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
    gap: 13px;
    padding: 18px 22px;
}

.buscador {
    display: flex;
    align-items: center;
    flex: 1;
    min-width: 220px;
    gap: 9px;
    padding: 0 12px;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
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
    min-width: 190px;
}

/* TABLA */

.tabla-contenedor {
    width: 100%;
    overflow-x: auto;
}

.tabla {
    width: 100%;
    min-width: 1050px;
    border-collapse: collapse;
    text-align: left;
    font-size: 12px;
}

.tabla thead {
    background: var(--fc-light);
    color: var(--fc-primary);
}

.tabla th {
    padding: 15px 16px;
    font-size: 11px;
    font-weight: 700;
    white-space: nowrap;
}

.tabla td {
    padding: 17px 16px;
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

.numero-venta {
    color: var(--fc-accent);
    font-size: 13px;
    font-weight: 700;
}

.fecha {
    white-space: nowrap;
    font-size: 11px;
}

.tipo-documento {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 10px;
    border-radius: 7px;
    font-size: 10px;
    font-weight: 600;
    white-space: nowrap;
}

.tipo-documento svg {
    width: 14px;
    height: 14px;
}

.tipo-documento.boleta {
    background: #eff6ff;
    color: #1d4ed8;
}

.tipo-documento.factura {
    background: #f3e8ff;
    color: #7e22ce;
}

.medicamentos-tabla {
    min-width: 180px;
    max-width: 280px;
}

.detalle-medicamento {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 9px;
    padding: 3px 0;
    font-size: 11px;
}

.detalle-medicamento small {
    flex-shrink: 0;
    color: #64748b;
    font-size: 10px;
}

.unidades-tabla {
    display: inline-block;
    min-width: 28px;
    padding: 5px 8px;
    border-radius: 6px;
    background: #f1f5f9;
    color: #475569;
    text-align: center;
    font-weight: 600;
}

.total-tabla {
    color: var(--fc-primary);
    font-size: 13px;
    font-weight: 750;
    white-space: nowrap;
}

.responsable {
    display: flex;
    align-items: center;
    gap: 8px;
    min-width: 120px;
    font-size: 11px;
}

.usuario-icono {
    display: grid;
    place-items: center;
    width: 28px;
    height: 28px;
    flex-shrink: 0;
    border-radius: 50%;
    background: #f1f5f9;
    color: #64748b;
}

.usuario-icono svg {
    width: 15px;
    height: 15px;
}

.boton-comprobante {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 11px;
    border: 1px solid var(--fc-border);
    border-radius: 7px;
    background: var(--fc-light);
    color: var(--fc-accent);
    font-size: 10px;
    font-weight: 650;
    white-space: nowrap;
}

.boton-comprobante svg {
    width: 15px;
    height: 15px;
}

.boton-comprobante:hover {
    border-color: var(--fc-accent);
    background: var(--fc-accent);
    color: white;
}

.tabla-inferior {
    padding: 16px 22px;
    color: #64748b;
    font-size: 11px;
}

/* ESTADO VACÍO */

.sin-resultados {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 12px;
    padding: 40px 20px;
    text-align: center;
}

.sin-resultados strong {
    color: var(--fc-primary);
    font-size: 15px;
}

.sin-resultados p {
    color: #64748b;
    font-size: 12px;
}

/* ANIMACIONES */

.spinner {
    width: 17px;
    height: 17px;
    flex-shrink: 0;
    border: 2px solid rgba(255, 255, 255, 0.4);
    border-top-color: white;
    border-radius: 50%;
    animation: girar 0.8s linear infinite;
}

@keyframes girar {
    to {
        transform: rotate(360deg);
    }
}

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

@media (max-width: 1200px) {
    .punto-venta {
        grid-template-columns:
            minmax(0, 1fr)
            minmax(245px, 285px);
        gap: 16px;
    }

    .campos-producto {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .campo-medicamento {
        grid-column: 1 / -1;
    }

    .tarjeta-indicador {
        padding: 17px;
    }
}

@media (max-width: 1000px) {
    .punto-venta {
        grid-template-columns: minmax(0, 1fr);
    }

    .panel-resumen {
        position: static;
    }

    .campos-producto {
        grid-template-columns:
            minmax(0, 2fr)
            minmax(80px, 1fr)
            minmax(100px, 1fr);
    }

    .campo-medicamento {
        grid-column: auto;
    }
}

@media (max-width: 650px) {
    .indicadores {
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
    }

    .tarjeta-indicador:last-child {
        grid-column: 1 / -1;
    }

    .indicador-superior > span {
        font-size: 11px;
    }

    .icono-indicador {
        width: 33px;
        height: 33px;
    }

    .tarjeta-encabezado,
    .contenido-productos,
    .filtros {
        padding: 16px;
    }

    .campos-producto {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .campo-medicamento {
        grid-column: 1 / -1;
    }

    .opcion-comprobante {
        padding: 12px 9px;
        gap: 7px;
    }

    .opcion-comprobante svg {
        width: 16px;
        height: 16px;
    }

    .opcion-comprobante small {
        font-size: 8px;
    }

    .buscador {
        flex-basis: 100%;
        min-width: 0;
    }

    .filtros select {
        flex: 1;
        min-width: 150px;
    }

    .acciones-encabezado {
        width: 100%;
    }
}

@media (prefers-reduced-motion: reduce) {
    *,
    *::before,
    *::after {
        animation: none !important;
        transition: none !important;
    }
}
</style>