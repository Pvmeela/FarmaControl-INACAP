<script setup>
import { computed, ref, nextTick } from 'vue'
import { Head, Link, useForm, usePage } from '@inertiajs/vue3'
import FarmaLayout from '../../Layouts/FarmaLayout.vue'

const props = defineProps({
    movimientos: {
        type: Array,
        default: () => [],
    },
    medicamentos: {
        type: Array,
        default: () => [],
    },
})

const page = usePage()

// ESTADOS

const busqueda = ref('')
const filtroTipo = ref('todos')
const mostrarFormulario = ref(false)
const enviando = ref(false)
const formularioRef = ref(null)

// FORMULARIO

const form = useForm({
    medicamento_id: '',
    cantidad: '',
    motivo: '',
})

const mensajeExito = computed(() =>
    page.props.flash?.success ?? ''
)

const medicamentosDisponibles = computed(() =>
    props.medicamentos.filter(medicamento =>
        medicamento.nombre && medicamento.codigo
    )
)

// ICONOS

const iconos = {
    movimientos: 'M3 7h17 M16 3l4 4-4 4 M21 17H4 M8 13l-4 4 4 4',
    entrada: 'M12 3v14 M6 11l6 6 6-6 M4 17v4h16v-4',
    salida: 'M12 17V3 M6 9l6-6 6 6 M4 17v4h16v-4',
    agregar: 'M12 5v14 M5 12h14',
    cerrar: 'M6 6l12 12 M18 6L6 18',
    buscar: 'M16 16l5 5 M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0z',
    check: 'M20 6L9 17l-5-5',
    inventario: 'M3 7l9-4 9 4v10l-9 4-9-4z M3 7l9 4 9-4 M12 11v10',
    medicamento: 'M8 4a5 5 0 0 1 7 0l5 5a5 5 0 0 1-7 7l-5-5a5 5 0 0 1 0-7z M10 13l7-7',
    historial: 'M3 4v6h6 M3 10a9 9 0 1 1 1 8 M12 7v5l4 2',
    usuario: 'M16 7a4 4 0 1 1-8 0 4 4 0 0 1 8 0z M4 21v-2a8 8 0 0 1 16 0v2',
    informacion: 'M22 12a10 10 0 1 1-20 0 10 10 0 0 1 20 0z M12 11v6 M12 7h.01',
    calendario: 'M3 5h18v16H3z M7 3v4 M17 3v4 M3 10h18',
    flecha: 'M4 12h16 M14 6l6 6-6 6',
}

// INDICADORES

const totalMovimientos = computed(() =>
    props.movimientos.length
)

const totalSalidas = computed(() =>
    props.movimientos
        .filter(movimiento =>
            movimiento.tipo === 'salida'
        )
        .reduce(
            (total, movimiento) =>
                total + Number(movimiento.cantidad || 0),
            0
        )
)

const totalEntradas = computed(() =>
    props.movimientos
        .filter(movimiento =>
            movimiento.tipo === 'entrada'
        )
        .reduce(
            (total, movimiento) =>
                total + Number(movimiento.cantidad || 0),
            0
        )
)

const indicadores = computed(() => [
    {
        titulo: 'Movimientos registrados',
        cantidad: totalMovimientos.value,
        descripcion: 'Total de operaciones registradas',
        icono: 'movimientos',
        color: 'principal',
        filtro: 'todos',
    },
    {
        titulo: 'Unidades retiradas',
        cantidad: totalSalidas.value,
        descripcion: 'Salidas de inventario registradas',
        icono: 'salida',
        color: 'naranja',
        filtro: 'salida',
    },
    {
        titulo: 'Unidades ingresadas',
        cantidad: totalEntradas.value,
        descripcion: 'Según el historial de movimientos',
        icono: 'entrada',
        color: 'verde',
        filtro: 'entrada',
    },
])

// BÚSQUEDA Y FILTROS

function normalizar(texto) {
    return String(texto ?? '')
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .toLowerCase()
        .trim()
}

const movimientosFiltrados = computed(() => {
    const texto = normalizar(busqueda.value)

    return props.movimientos.filter(movimiento => {
        const medicamento = movimiento.lote?.medicamento
        const usuario = movimiento.usuario

        const coincideBusqueda = [
            medicamento?.nombre,
            medicamento?.codigo,
            movimiento.lote?.numero_lote,
            movimiento.tipo,
            movimiento.motivo,
            usuario?.name,
        ].some(valor =>
            normalizar(valor).includes(texto)
        )

        const coincideTipo =
            filtroTipo.value === 'todos' ||
            movimiento.tipo === filtroTipo.value

        return coincideBusqueda && coincideTipo
    })
})

function limpiarFiltros() {
    busqueda.value = ''
    filtroTipo.value = 'todos'
}

function filtrarDesdeIndicador(tipo) {
    busqueda.value = ''
    filtroTipo.value = tipo
}

// FORMATO DE DATOS

function formatearFecha(fecha) {
    if (!fecha) return 'Sin registro'

    const fechaObj = new Date(fecha)

    if (Number.isNaN(fechaObj.getTime())) {
        return fecha
    }

    return fechaObj.toLocaleString('es-CL', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    })
}

function formatearNumero(numero) {
    return Number(numero || 0).toLocaleString('es-CL')
}

function textoTipo(tipo) {
    if (tipo === 'entrada') return 'Entrada'
    if (tipo === 'salida') return 'Salida'

    return tipo || 'Sin tipo'
}

function claseTipo(tipo) {
    if (tipo === 'entrada') return 'estado-verde'
    if (tipo === 'salida') return 'estado-naranja'

    return 'estado-gris'
}

// FORMULARIO DESPLEGABLE

async function alternarFormulario() {
    if (enviando.value || form.processing) return

    mostrarFormulario.value = !mostrarFormulario.value

    if (mostrarFormulario.value) {
        await nextTick()

        formularioRef.value?.scrollIntoView({
            behavior: 'smooth',
            block: 'start',
        })
    } else {
        form.clearErrors()
    }
}

function cerrarFormulario() {
    if (enviando.value || form.processing) return

    mostrarFormulario.value = false
    form.clearErrors()
}

// REGISTRAR SALIDA

function registrarSalida() {
    if (enviando.value || form.processing) return
    if (medicamentosDisponibles.value.length === 0) return

    enviando.value = true

    form.post('/movimientos', {
        preserveScroll: true,

        onSuccess: () => {
            form.reset()
            mostrarFormulario.value = false
        },

        onFinish: () => {
            enviando.value = false
        },
    })
}
</script>

<template>
    <Head title="Movimientos de inventario | FarmaControl" />

    <FarmaLayout
        titulo="Movimientos de inventario"
        subtitulo="Control de entradas, salidas y trazabilidad de medicamentos por lote."
    >
        <template #acciones>
            <button
                type="button"
                class="boton-principal"
                :disabled="enviando || form.processing"
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
                        ? 'Cerrar formulario'
                        : 'Registrar salida'
                }}
            </button>
        </template>

        <div class="modulo-movimientos">

            <!-- MENSAJE DE ÉXITO -->

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
                </div>
            </Transition>

            <!-- INDICADORES -->

            <section class="indicadores">
                <button
                    v-for="indicador in indicadores"
                    :key="indicador.titulo"
                    type="button"
                    class="tarjeta-indicador"
                    :class="{
                        seleccionado:
                            filtroTipo === indicador.filtro &&
                            indicador.filtro !== 'todos'
                    }"
                    :aria-pressed="filtroTipo === indicador.filtro"
                    @click="filtrarDesdeIndicador(indicador.filtro)"
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

                    <strong
                        class="cantidad"
                        :class="indicador.color"
                    >
                        {{ formatearNumero(indicador.cantidad) }}
                    </strong>

                    <p>{{ indicador.descripcion }}</p>

                    <div class="indicador-inferior">
                        <span>Consultar movimientos</span>

                        <svg viewBox="0 0 24 24">
                            <path :d="iconos.flecha" />
                        </svg>
                    </div>
                </button>
            </section>

            <!-- PANEL FEFO -->

            <section class="panel-fefo">
                <div class="fefo-icono">
                    <svg viewBox="0 0 24 24">
                        <path :d="iconos.inventario" />
                    </svg>
                </div>

                <div class="fefo-texto">
                    <span class="etiqueta-fefo">
                        TRAZABILIDAD DE INVENTARIO
                    </span>

                    <h2>Control de salidas mediante FEFO</h2>

                    <p>
                        El sistema selecciona automáticamente
                        los lotes vigentes con fecha de vencimiento
                        más próxima, evitando retirar unidades
                        de lotes vencidos.
                    </p>
                </div>

                <Link
                    href="/lotes"
                    class="boton-fefo"
                >
                    <svg viewBox="0 0 24 24">
                        <path :d="iconos.inventario" />
                    </svg>

                    Consultar inventario
                </Link>
            </section>

            <!-- FORMULARIO -->

            <Transition>
                <section
                    v-if="mostrarFormulario"
                    ref="formularioRef"
                    class="tarjeta formulario-tarjeta"
                >
                    <div class="tarjeta-encabezado">
                        <div class="titulo-con-icono">
                            <div class="icono-seccion">
                                <svg viewBox="0 0 24 24">
                                    <path :d="iconos.salida" />
                                </svg>
                            </div>

                            <div>
                                <h2>Registrar salida de inventario</h2>

                                <p>
                                    Selecciona el medicamento y
                                    especifica las unidades a retirar.
                                </p>
                            </div>
                        </div>

                        <button
                            type="button"
                            class="boton-icono"
                            aria-label="Cerrar formulario"
                            :disabled="enviando || form.processing"
                            @click="cerrarFormulario"
                        >
                            <svg viewBox="0 0 24 24">
                                <path :d="iconos.cerrar" />
                            </svg>
                        </button>
                    </div>

                    <form
                        class="formulario"
                        @submit.prevent="registrarSalida"
                    >
                        <h3 class="titulo-grupo">
                            Información de la salida
                        </h3>

                        <div class="campos-grid">

                            <!-- MEDICAMENTO -->

                            <div class="campo">
                                <label for="medicamento">
                                    Medicamento
                                    <span class="obligatorio">*</span>
                                </label>

                                <select
                                    id="medicamento"
                                    v-model="form.medicamento_id"
                                    required
                                    :class="{
                                        'input-error':
                                            form.errors.medicamento_id
                                    }"
                                >
                                    <option value="">
                                        Selecciona un medicamento
                                    </option>

                                    <option
                                        v-for="medicamento in medicamentosDisponibles"
                                        :key="medicamento.id"
                                        :value="medicamento.id"
                                    >
                                        {{ medicamento.nombre }}
                                        —
                                        {{ medicamento.codigo }}
                                    </option>
                                </select>

                                <small>
                                    Medicamento que será retirado
                                    del inventario.
                                </small>

                                <p
                                    v-if="form.errors.medicamento_id"
                                    class="error"
                                >
                                    {{ form.errors.medicamento_id }}
                                </p>
                            </div>

                            <!-- CANTIDAD -->

                            <div class="campo">
                                <label for="cantidad">
                                    Cantidad a retirar
                                    <span class="obligatorio">*</span>
                                </label>

                                <input
                                    id="cantidad"
                                    v-model="form.cantidad"
                                    type="number"
                                    min="1"
                                    step="1"
                                    required
                                    placeholder="Ej: 5"
                                    :class="{
                                        'input-error':
                                            form.errors.cantidad
                                    }"
                                />

                                <small>
                                    Ingresa una cantidad entera
                                    mayor que cero.
                                </small>

                                <p
                                    v-if="form.errors.cantidad"
                                    class="error"
                                >
                                    {{ form.errors.cantidad }}
                                </p>
                            </div>

                            <!-- MOTIVO -->

                            <div class="campo campo-completo">
                                <label for="motivo">
                                    Motivo de la salida
                                    <span class="obligatorio">*</span>
                                </label>

                                <textarea
                                    id="motivo"
                                    v-model="form.motivo"
                                    maxlength="255"
                                    rows="3"
                                    required
                                    placeholder="Ej: Salida de prueba académica"
                                    :class="{
                                        'input-error':
                                            form.errors.motivo
                                    }"
                                ></textarea>

                                <div class="detalle-campo">
                                    <small>
                                        Describe el motivo del retiro
                                        para mantener la trazabilidad.
                                    </small>

                                    <small>
                                        {{ form.motivo.length }}/255
                                    </small>
                                </div>

                                <p
                                    v-if="form.errors.motivo"
                                    class="error"
                                >
                                    {{ form.errors.motivo }}
                                </p>
                            </div>
                        </div>

                        <!-- INFORMACIÓN FEFO -->

                        <div class="aviso-formulario">
                            <svg viewBox="0 0 24 24">
                                <path :d="iconos.informacion" />
                            </svg>

                            <div>
                                <strong>Validación automática de stock</strong>

                                <p>
                                    No se retirarán unidades de lotes
                                    vencidos. Si la cantidad solicitada
                                    supera el stock utilizable,
                                    la operación será rechazada.
                                </p>
                            </div>
                        </div>

                        <div
                            v-if="medicamentosDisponibles.length === 0"
                            class="aviso-sin-medicamentos"
                        >
                            No hay medicamentos disponibles
                            para registrar una salida.
                        </div>

                        <div class="formulario-inferior">
                            <span>* Campos obligatorios</span>

                            <div class="acciones-formulario">
                                <button
                                    type="button"
                                    class="boton-secundario"
                                    :disabled="enviando || form.processing"
                                    @click="cerrarFormulario"
                                >
                                    Cancelar
                                </button>

                                <button
                                    type="submit"
                                    class="boton-principal"
                                    :disabled="
                                        form.processing ||
                                        enviando ||
                                        medicamentosDisponibles.length === 0
                                    "
                                >
                                    <svg viewBox="0 0 24 24">
                                        <path :d="iconos.salida" />
                                    </svg>

                                    {{
                                        form.processing || enviando
                                            ? 'Registrando salida...'
                                            : 'Registrar salida'
                                    }}
                                </button>
                            </div>
                        </div>
                    </form>
                </section>
            </Transition>

            <!-- HISTORIAL -->

            <section class="tarjeta">
                <div class="tarjeta-encabezado">
                    <div class="titulo-con-icono">
                        <div class="icono-seccion">
                            <svg viewBox="0 0 24 24">
                                <path :d="iconos.historial" />
                            </svg>
                        </div>

                        <div>
                            <h2>Historial de movimientos</h2>

                            <p>
                                Registro de las operaciones
                                realizadas en inventario.
                            </p>
                        </div>
                    </div>

                    <span class="contador">
                        {{ totalMovimientos }} registros
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
                            aria-label="Buscar movimientos"
                            placeholder="Buscar medicamento, lote, usuario..."
                        />
                    </div>

                    <select
                        v-model="filtroTipo"
                        aria-label="Filtrar tipo de movimiento"
                    >
                        <option value="todos">
                            Todos los movimientos
                        </option>

                        <option value="entrada">
                            Entradas de inventario
                        </option>

                        <option value="salida">
                            Salidas de inventario
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
                                <th>Fecha y hora</th>
                                <th>Medicamento</th>
                                <th>Número de lote</th>
                                <th>Tipo</th>
                                <th>Cantidad</th>
                                <th>Stock anterior</th>
                                <th>Stock posterior</th>
                                <th>Responsable</th>
                                <th>Motivo</th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr
                                v-for="movimiento in movimientosFiltrados"
                                :key="movimiento.id"
                            >
                                <!-- FECHA -->

                                <td>
                                    <div class="fecha">
                                        <svg viewBox="0 0 24 24">
                                            <path :d="iconos.calendario" />
                                        </svg>

                                        {{
                                            formatearFecha(
                                                movimiento.created_at
                                            )
                                        }}
                                    </div>
                                </td>

                                <!-- MEDICAMENTO -->

                                <td>
                                    <div class="medicamento-nombre">
                                        <div class="medicamento-icono">
                                            <svg viewBox="0 0 24 24">
                                                <path :d="iconos.medicamento" />
                                            </svg>
                                        </div>

                                        <div>
                                            <strong>
                                                {{
                                                    movimiento.lote
                                                        ?.medicamento
                                                        ?.nombre
                                                        ?? 'Sin medicamento'
                                                }}
                                            </strong>

                                            <small>
                                                {{
                                                    movimiento.lote
                                                        ?.medicamento
                                                        ?.codigo
                                                        ?? '—'
                                                }}
                                            </small>
                                        </div>
                                    </div>
                                </td>

                                <!-- LOTE -->

                                <td>
                                    <span class="numero-lote">
                                        {{
                                            movimiento.lote?.numero_lote
                                                ?? '—'
                                        }}
                                    </span>
                                </td>

                                <!-- TIPO -->

                                <td>
                                    <span
                                        class="estado"
                                        :class="claseTipo(movimiento.tipo)"
                                    >
                                        <svg
                                            v-if="
                                                movimiento.tipo === 'entrada' ||
                                                movimiento.tipo === 'salida'
                                            "
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                :d="movimiento.tipo === 'entrada'
                                                    ? iconos.entrada
                                                    : iconos.salida"
                                            />
                                        </svg>

                                        {{ textoTipo(movimiento.tipo) }}
                                    </span>
                                </td>

                                <!-- CANTIDAD -->

                                <td>
                                    <strong
                                        class="cantidad-movimiento"
                                        :class="{
                                            'texto-verde':
                                                movimiento.tipo === 'entrada',
                                            'texto-naranja':
                                                movimiento.tipo === 'salida'
                                        }"
                                    >
                                        {{
                                            movimiento.tipo === 'entrada'
                                                ? '+'
                                                : movimiento.tipo === 'salida'
                                                    ? '−'
                                                    : ''
                                        }}{{
                                            formatearNumero(
                                                movimiento.cantidad
                                            )
                                        }}
                                    </strong>

                                    <small class="unidad">
                                        unidades
                                    </small>
                                </td>

                                <!-- STOCK ANTERIOR -->

                                <td>
                                    <span class="stock-anterior">
                                        {{
                                            formatearNumero(
                                                movimiento.stock_anterior
                                            )
                                        }}
                                    </span>
                                </td>

                                <!-- STOCK POSTERIOR -->

                                <td>
                                    <strong class="stock-posterior">
                                        {{
                                            formatearNumero(
                                                movimiento.stock_posterior
                                            )
                                        }}
                                    </strong>
                                </td>

                                <!-- RESPONSABLE -->

                                <td>
                                    <div class="responsable">
                                        <div class="usuario-icono">
                                            <svg viewBox="0 0 24 24">
                                                <path :d="iconos.usuario" />
                                            </svg>
                                        </div>

                                        <span>
                                            {{
                                                movimiento.usuario?.name
                                                    ?? 'Sin usuario'
                                            }}
                                        </span>
                                    </div>
                                </td>

                                <!-- MOTIVO -->

                                <td class="motivo">
                                    {{ movimiento.motivo }}
                                </td>
                            </tr>

                            <!-- SIN RESULTADOS -->

                            <tr v-if="movimientosFiltrados.length === 0">
                                <td colspan="9">
                                    <div class="sin-resultados">
                                        <div class="icono-vacio">
                                            <svg viewBox="0 0 24 24">
                                                <path :d="iconos.historial" />
                                            </svg>
                                        </div>

                                        <strong>
                                            {{
                                                totalMovimientos === 0
                                                    ? 'No hay movimientos registrados'
                                                    : 'No se encontraron resultados'
                                            }}
                                        </strong>

                                        <p>
                                            {{
                                                totalMovimientos === 0
                                                    ? 'El historial todavía está vacío.'
                                                    : 'Intenta modificar los filtros de búsqueda.'
                                            }}
                                        </p>

                                        <button
                                            v-if="totalMovimientos > 0"
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
                    Mostrando {{ movimientosFiltrados.length }}
                    de {{ totalMovimientos }} movimientos
                </div>
            </section>

            <!-- INFORMACIÓN COMPLEMENTARIA -->

            <section class="informacion-movimientos">
                <div class="icono-seccion">
                    <svg viewBox="0 0 24 24">
                        <path :d="iconos.informacion" />
                    </svg>
                </div>

                <div>
                    <h3>Información sobre la trazabilidad</h3>

                    <p>
                        Cada movimiento permite identificar el
                        medicamento, el lote, la cantidad,
                        el responsable y la variación del stock.
                    </p>

                    <p>
                        El indicador de unidades ingresadas
                        considera únicamente las entradas
                        registradas en el historial. No incluye
                        los lotes registrados anteriormente
                        que no tengan un movimiento asociado.
                    </p>
                </div>
            </section>

        </div>
    </FarmaLayout>
</template>

<style scoped>
.modulo-movimientos {
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

.boton-secundario:hover:not(:disabled) {
    background: var(--fc-light);
    border-color: var(--fc-border);
}

/* MENSAJES */

.mensaje-exito {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 16px;
    border: 1px solid #bbf7d0;
    border-radius: 10px;
    background: #ecfdf5;
    color: #166534;
    font-size: 13px;
}

/* INDICADORES */

.indicadores {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 16px;
}

.tarjeta-indicador {
    min-width: 0;
    padding: 21px;
    border: 1px solid var(--fc-border);
    border-radius: 14px;
    background: white;
    text-align: left;
    box-shadow: 0 3px 15px rgba(15, 23, 42, 0.03);
}

.tarjeta-indicador:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 22px rgba(15, 23, 42, 0.07);
    border-color: var(--fc-accent);
}

.tarjeta-indicador.seleccionado {
    border-color: var(--fc-accent);
    box-shadow: 0 0 0 2px var(--fc-light);
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
    width: 42px;
    height: 42px;
    flex-shrink: 0;
    border-radius: 11px;
}

.icono-indicador.principal {
    background: var(--fc-light);
    color: var(--fc-accent);
}

.icono-indicador.verde {
    background: #ecfdf5;
    color: #059669;
}

.icono-indicador.naranja {
    background: #fff7ed;
    color: #ea580c;
}

.cantidad {
    display: block;
    margin: 14px 0 5px;
    color: var(--fc-primary);
    font-size: 34px;
    font-weight: 750;
    letter-spacing: -1px;
    overflow-wrap: anywhere;
}

.cantidad.verde {
    color: #047857;
}

.cantidad.naranja {
    color: #c2410c;
}

.tarjeta-indicador p {
    color: #64748b;
    font-size: 11px;
    line-height: 1.6;
}

.indicador-inferior {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    margin-top: 18px;
    padding-top: 12px;
    border-top: 1px solid #edf0f5;
    color: var(--fc-accent);
    font-size: 11px;
    font-weight: 600;
}

.indicador-inferior svg {
    width: 15px;
    height: 15px;
}

/* PANEL FEFO */

.panel-fefo {
    position: relative;
    display: flex;
    align-items: center;
    gap: 20px;
    padding: 25px;
    overflow: hidden;
    border-radius: 15px;
    background: linear-gradient(
        115deg,
        var(--fc-primary),
        var(--fc-accent)
    );
    color: white;
}

.panel-fefo::after {
    position: absolute;
    top: -100px;
    right: -45px;
    width: 260px;
    height: 260px;
    border: 35px solid rgba(255, 255, 255, 0.06);
    border-radius: 50%;
    content: '';
    pointer-events: none;
}

.fefo-icono {
    display: grid;
    place-items: center;
    width: 65px;
    height: 65px;
    flex-shrink: 0;
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 16px;
    background: rgba(255, 255, 255, 0.1);
}

.fefo-icono svg {
    width: 32px;
    height: 32px;
}

.fefo-texto {
    position: relative;
    z-index: 1;
    flex: 1;
}

.etiqueta-fefo {
    font-size: 9px;
    font-weight: 600;
    letter-spacing: 1.5px;
    opacity: 0.8;
}

.fefo-texto h2 {
    margin: 6px 0;
    font-size: 19px;
    font-weight: 700;
}

.fefo-texto p {
    max-width: 650px;
    color: rgba(255, 255, 255, 0.85);
    font-size: 12px;
    line-height: 1.8;
}

.boton-fefo {
    position: relative;
    z-index: 1;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 11px 15px;
    flex-shrink: 0;
    border: 1px solid rgba(255, 255, 255, 0.3);
    border-radius: 9px;
    background: rgba(255, 255, 255, 0.1);
    color: white;
    font-size: 12px;
    font-weight: 600;
    transition: background 0.2s;
}

.boton-fefo:hover {
    background: rgba(255, 255, 255, 0.2);
}

/* TARJETAS */

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

.boton-icono:hover:not(:disabled) {
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

.campos-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 20px;
}

.campo {
    min-width: 0;
}

.campo-completo {
    grid-column: 1 / -1;
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
.campo select,
.campo textarea,
.filtros select {
    width: 100%;
    min-height: 43px;
    padding: 11px 13px;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    background: white;
    color: #334155;
    font-size: 13px;
    outline: none;
    transition: border-color 0.2s, box-shadow 0.2s;
}

.campo textarea {
    min-height: 95px;
    resize: vertical;
}

.campo input:focus,
.campo select:focus,
.campo textarea:focus,
.filtros select:focus {
    border-color: var(--fc-accent);
    box-shadow: 0 0 0 3px var(--fc-light);
}

.campo input::placeholder,
.campo textarea::placeholder {
    color: #94a3b8;
}

.campo small {
    display: block;
    margin-top: 7px;
    color: #64748b;
    font-size: 11px;
    line-height: 1.6;
}

.detalle-campo {
    display: flex;
    justify-content: space-between;
    gap: 12px;
}

.campo .input-error {
    border-color: #ef4444;
}

.error {
    margin-top: 6px;
    color: #dc2626;
    font-size: 12px;
}

.aviso-formulario {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    margin-top: 24px;
    padding: 16px;
    border: 1px solid var(--fc-border);
    border-radius: 9px;
    background: var(--fc-light);
    color: var(--fc-primary);
    font-size: 12px;
    line-height: 1.7;
}

.aviso-formulario strong {
    display: block;
    margin-bottom: 4px;
}

.aviso-sin-medicamentos {
    margin-top: 15px;
    padding: 14px;
    border: 1px solid #fed7aa;
    border-radius: 9px;
    background: #fff7ed;
    color: #9a3412;
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
    color: #64748b;
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
    background: white;
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
    min-width: 185px;
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
    min-width: 1150px;
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

.fecha {
    display: flex;
    align-items: center;
    gap: 7px;
    color: #64748b;
    font-size: 11px;
    white-space: nowrap;
}

.fecha svg {
    width: 15px;
    height: 15px;
}

.medicamento-nombre {
    display: flex;
    align-items: center;
    gap: 10px;
    min-width: 155px;
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
    color: #64748b;
    font-size: 10px;
}

.numero-lote {
    display: inline-block;
    padding: 5px 8px;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    background: #f8fafc;
    color: #475569;
    font-size: 11px;
    font-weight: 600;
    white-space: nowrap;
}

/* TIPOS DE MOVIMIENTO */

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

.estado svg {
    width: 13px;
    height: 13px;
}

.estado-verde {
    background: #ecfdf5;
    color: #047857;
}

.estado-naranja {
    background: #fff7ed;
    color: #c2410c;
}

.estado-gris {
    background: #f1f5f9;
    color: #64748b;
}

.cantidad-movimiento {
    font-size: 15px;
    font-weight: 700;
    white-space: nowrap;
}

.texto-verde {
    color: #047857;
}

.texto-naranja {
    color: #c2410c;
}

.unidad {
    display: block;
    margin-top: 3px;
    color: #64748b;
    font-size: 10px;
}

.stock-anterior {
    color: #64748b;
    font-size: 13px;
}

.stock-posterior {
    display: inline-block;
    min-width: 35px;
    padding: 5px 9px;
    border-radius: 7px;
    background: var(--fc-light);
    color: var(--fc-primary);
    font-size: 13px;
    font-weight: 700;
    text-align: center;
}

/* RESPONSABLE */

.responsable {
    display: flex;
    align-items: center;
    gap: 8px;
    min-width: 125px;
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

.motivo {
    min-width: 190px;
    max-width: 300px;
    line-height: 1.7;
    overflow-wrap: anywhere;
}

.tabla-inferior {
    padding: 16px 22px;
    color: #64748b;
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
    color: #64748b;
    font-size: 12px;
}

/* INFORMACIÓN COMPLEMENTARIA */

.informacion-movimientos {
    display: flex;
    align-items: flex-start;
    gap: 15px;
    padding: 20px;
    border: 1px solid var(--fc-border);
    border-radius: 12px;
    background: white;
}

.informacion-movimientos h3 {
    margin-bottom: 8px;
    color: var(--fc-primary);
    font-size: 13px;
    font-weight: 700;
}

.informacion-movimientos p {
    color: #64748b;
    font-size: 12px;
    line-height: 1.8;
}

.informacion-movimientos p + p {
    margin-top: 6px;
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

@media (max-width: 1050px) {
    .panel-fefo {
        flex-wrap: wrap;
    }

    .boton-fefo {
        margin-left: 85px;
    }

    .tarjeta-indicador {
        padding: 16px;
    }

    .indicador-superior > span {
        font-size: 11px;
    }

    .icono-indicador {
        width: 34px;
        height: 34px;
    }
}

@media (max-width: 650px) {
    .indicadores {
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
    }

    .tarjeta-indicador:first-child {
        grid-column: 1 / -1;
    }

    .cantidad {
        font-size: 28px;
    }

    .panel-fefo {
        padding: 18px;
        gap: 13px;
    }

    .fefo-icono {
        width: 45px;
        height: 45px;
    }

    .fefo-icono svg {
        width: 24px;
        height: 24px;
    }

    .fefo-texto {
        min-width: 180px;
    }

    .fefo-texto h2 {
        font-size: 16px;
    }

    .boton-fefo {
        width: 100%;
        margin-left: 0;
    }

    .campos-grid {
        grid-template-columns: 1fr;
    }

    .tarjeta-encabezado,
    .formulario,
    .filtros {
        padding: 16px;
    }

    .buscador {
        flex-basis: 100%;
    }

    .filtros select {
        flex: 1;
        min-width: 150px;
    }

    .acciones-formulario {
        width: 100%;
    }

    .acciones-formulario button {
        flex: 1;
    }

    .detalle-campo {
        flex-wrap: wrap;
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