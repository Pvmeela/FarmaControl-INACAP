<script setup>
import { computed, ref, nextTick } from 'vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import FarmaLayout from '../../Layouts/FarmaLayout.vue'

const props = defineProps({
    lotes: {
        type: Array,
        default: () => [],
    },
    medicamentos: {
        type: Array,
        default: () => [],
    },
})

// ESTADOS DE LA PANTALLA

const mostrarFormulario = ref(false)
const busqueda = ref('')
const filtroEstado = ref('todos')
const filtroExistencias = ref('todos')
const mensajeExito = ref('')
const formularioRef = ref(null)

// FORMULARIO

const form = useForm({
    medicamento_id: '',
    numero_lote: '',
    fecha_recepcion: '',
    fecha_vencimiento: '',
    cantidad_inicial: '',
})

// ICONOS

const iconos = {
    inventario: 'M3 7l9-4 9 4v10l-9 4-9-4z M3 7l9 4 9-4 M12 11v10',
    cajas: 'M3 3h8v8H3z M13 3h8v8h-8z M3 13h8v8H3z M13 13h8v8h-8z',
    check: 'M20 6L9 17l-5-5',
    reloj: 'M22 12a10 10 0 1 1-20 0 10 10 0 0 1 20 0z M12 6v6l4 2',
    alerta: 'M12 3L2 21h20L12 3z M12 9v5 M12 17h.01',
    agregar: 'M12 5v14 M5 12h14',
    cerrar: 'M6 6l12 12 M18 6L6 18',
    buscar: 'M16 16l5 5 M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0z',
    medicamento: 'M8 4a5 5 0 0 1 7 0l5 5a5 5 0 0 1-7 7l-5-5a5 5 0 0 1 0-7z M10 13l7-7',
    informacion: 'M22 12a10 10 0 1 1-20 0 10 10 0 0 1 20 0z M12 11v6 M12 7h.01',
    movimientos: 'M3 7h17 M16 3l4 4-4 4 M21 17H4 M8 13l-4 4 4 4',
    calendario: 'M3 5h18v16H3z M7 3v4 M17 3v4 M3 10h18',
}

// CONVERSIÓN DE FECHAS

function convertirFecha(fecha) {
    if (!fecha) return null

    const valor = String(fecha).substring(0, 10)
    const [anio, mes, dia] = valor.split('-').map(Number)

    if (!anio || !mes || !dia) return null

    const resultado = new Date(anio, mes - 1, dia)

    if (
        resultado.getFullYear() !== anio ||
        resultado.getMonth() !== mes - 1 ||
        resultado.getDate() !== dia
    ) {
        return null
    }

    return resultado
}

function formatearFecha(fecha) {
    const valor = convertirFecha(fecha)

    return valor
        ? valor.toLocaleDateString('es-CL')
        : '—'
}

function formatearNumero(numero) {
    return new Intl.NumberFormat('es-CL').format(
        Number(numero ?? 0)
    )
}

// ESTADO DE VENCIMIENTO
// Se utiliza el estado calculado por Laravel.

function estadoLote(lote) {
    const estados = {
        vencido: {
            texto: 'Vencido',
            clase: 'estado-rojo',
        },
        proximo_vencer: {
            texto: 'Próximo a vencer',
            clase: 'estado-naranja',
        },
        vigente: {
            texto: 'Vigente',
            clase: 'estado-verde',
        },
    }

    return estados[lote.estado_vencimiento] ?? {
        texto: 'Sin fecha',
        clase: 'estado-gris',
    }
}

// INDICADORES

const totalLotes = computed(() =>
    props.lotes.length
)

const unidadesDisponibles = computed(() =>
    props.lotes.reduce(
        (total, lote) =>
            total + Number(lote.cantidad_disponible ?? 0),
        0
    )
)

const unidadesUtilizables = computed(() =>
    props.lotes.reduce((total, lote) => {
        if (
            lote.estado_vencimiento !== 'vigente' &&
            lote.estado_vencimiento !== 'proximo_vencer'
        ) {
            return total
        }

        return total +
            Number(lote.cantidad_disponible ?? 0)
    }, 0)
)

const lotesProximos = computed(() =>
    props.lotes.filter(
        lote =>
            lote.estado_vencimiento === 'proximo_vencer'
    ).length
)

const lotesVencidos = computed(() =>
    props.lotes.filter(
        lote => lote.estado_vencimiento === 'vencido'
    ).length
)

const indicadores = computed(() => [
    {
        titulo: 'Lotes registrados',
        cantidad: totalLotes.value,
        descripcion: 'Total del inventario',
        icono: 'inventario',
        color: 'principal',
        filtro: 'todos',
    },
    {
        titulo: 'Existencias físicas',
        cantidad: unidadesDisponibles.value,
        descripcion: 'Incluye unidades vencidas',
        icono: 'cajas',
        color: 'principal',
        filtro: null,
    },
    {
        titulo: 'Unidades utilizables',
        cantidad: unidadesUtilizables.value,
        descripcion: 'Unidades de lotes vigentes',
        icono: 'check',
        color: 'verde',
        filtro: null,
    },
    {
        titulo: 'Próximos a vencer',
        cantidad: lotesProximos.value,
        descripcion: 'Dentro de 30 días',
        icono: 'reloj',
        color: 'naranja',
        filtro: 'proximo_vencer',
    },
    {
        titulo: 'Lotes vencidos',
        cantidad: lotesVencidos.value,
        descripcion: 'No aptos para su entrega',
        icono: 'alerta',
        color: 'rojo',
        filtro: 'vencido',
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

const lotesFiltrados = computed(() => {
    const texto = normalizar(busqueda.value)

    return props.lotes.filter(lote => {
        const coincideBusqueda = [
            lote.medicamento?.nombre,
            lote.medicamento?.codigo,
            lote.numero_lote,
        ].some(valor =>
            normalizar(valor).includes(texto)
        )

        const coincideEstado =
            filtroEstado.value === 'todos' ||
            lote.estado_vencimiento === filtroEstado.value

        const cantidad = Number(
            lote.cantidad_disponible ?? 0
        )

        const coincideExistencias =
            filtroExistencias.value === 'todos' ||
            (
                filtroExistencias.value === 'con_stock' &&
                cantidad > 0
            ) ||
            (
                filtroExistencias.value === 'sin_stock' &&
                cantidad === 0
            )

        return coincideBusqueda &&
            coincideEstado &&
            coincideExistencias
    })
})

function limpiarFiltros() {
    busqueda.value = ''
    filtroEstado.value = 'todos'
    filtroExistencias.value = 'todos'
}

function filtrarDesdeIndicador(filtro) {
    if (filtro === null) return

    limpiarFiltros()
    filtroEstado.value = filtro
}

// FORMULARIO DE REGISTRO

async function alternarFormulario() {
    mostrarFormulario.value = !mostrarFormulario.value
    mensajeExito.value = ''

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
    mostrarFormulario.value = false
    form.clearErrors()
}

function guardarLote() {
    if (form.processing) return

    mensajeExito.value = ''

    form.post('/lotes', {
        preserveScroll: true,

        onSuccess: () => {
            form.reset()
            mostrarFormulario.value = false
            mensajeExito.value =
                'Lote registrado correctamente.'
        },
    })
}
</script>

<template>
    <Head title="Inventario y lotes | FarmaControl" />

    <FarmaLayout
        titulo="Inventario y lotes"
        subtitulo="Control de existencias, recepción de medicamentos y fechas de vencimiento."
    >
        <template #acciones>
            <button
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
                        ? 'Cerrar formulario'
                        : 'Registrar lote'
                }}
            </button>
        </template>

        <div class="modulo-inventario">

            <!-- INDICADORES -->

            <section class="indicadores">
                <component
                    :is="indicador.filtro !== null
                        ? 'button'
                        : 'article'"
                    v-for="indicador in indicadores"
                    :key="indicador.titulo"
                    class="tarjeta-indicador"
                    :class="{
                        interactivo: indicador.filtro !== null,
                        seleccionado:
                            indicador.filtro !== null &&
                            indicador.filtro !== 'todos' &&
                            filtroEstado === indicador.filtro
                    }"
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
                </component>
            </section>

            <!-- INFORMACIÓN FEFO -->

            <section class="panel-fefo">
                <div class="fefo-icono">
                    <svg viewBox="0 0 24 24">
                        <path :d="iconos.inventario" />
                    </svg>
                </div>

                <div class="fefo-texto">
                    <span class="etiqueta-fefo">
                        CONTROL DE INVENTARIO
                    </span>

                    <h2>Gestión de existencias mediante FEFO</h2>

                    <p>
                        Los medicamentos con vencimiento más
                        próximo tienen prioridad en las salidas
                        de inventario. Los lotes vencidos se
                        excluyen de las unidades utilizables.
                    </p>
                </div>

                <Link
                    href="/movimientos"
                    class="boton-fefo"
                >
                    <svg viewBox="0 0 24 24">
                        <path :d="iconos.movimientos" />
                    </svg>

                    Ver movimientos
                </Link>
            </section>

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

            <!-- FORMULARIO DE REGISTRO -->

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
                                    <path :d="iconos.agregar" />
                                </svg>
                            </div>

                            <div>
                                <h2>Registrar nuevo lote</h2>

                                <p>
                                    Completa la información de
                                    recepción del medicamento.
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
                        @submit.prevent="guardarLote"
                    >
                        <h3 class="titulo-grupo">
                            Información del medicamento
                        </h3>

                        <div class="campos-grid">

                            <!-- MEDICAMENTO -->

                            <div class="campo campo-completo">
                                <label for="medicamento_id">
                                    Medicamento
                                    <span class="obligatorio">*</span>
                                </label>

                                <select
                                    id="medicamento_id"
                                    v-model="form.medicamento_id"
                                    required
                                    :class="{
                                        'input-error':
                                            form.errors.medicamento_id
                                    }"
                                >
                                    <option value="">
                                        Seleccionar medicamento
                                    </option>

                                    <option
                                        v-for="medicamento in props.medicamentos"
                                        :key="medicamento.id"
                                        :value="medicamento.id"
                                    >
                                        {{ medicamento.nombre }}
                                        -
                                        {{ medicamento.codigo }}
                                    </option>
                                </select>

                                <p
                                    v-if="form.errors.medicamento_id"
                                    class="error"
                                >
                                    {{ form.errors.medicamento_id }}
                                </p>
                            </div>

                            <!-- NÚMERO DE LOTE -->

                            <div class="campo">
                                <label for="numero_lote">
                                    Número de lote
                                    <span class="obligatorio">*</span>
                                </label>

                                <input
                                    id="numero_lote"
                                    v-model="form.numero_lote"
                                    type="text"
                                    maxlength="100"
                                    required
                                    placeholder="Ej: PAR-2026-001"
                                    :class="{
                                        'input-error':
                                            form.errors.numero_lote
                                    }"
                                />

                                <small>
                                    Identificador del lote recibido.
                                </small>

                                <p
                                    v-if="form.errors.numero_lote"
                                    class="error"
                                >
                                    {{ form.errors.numero_lote }}
                                </p>
                            </div>

                            <!-- CANTIDAD -->

                            <div class="campo">
                                <label for="cantidad_inicial">
                                    Cantidad recibida
                                    <span class="obligatorio">*</span>
                                </label>

                                <input
                                    id="cantidad_inicial"
                                    v-model="form.cantidad_inicial"
                                    type="number"
                                    min="1"
                                    step="1"
                                    required
                                    placeholder="Ej: 100"
                                    :class="{
                                        'input-error':
                                            form.errors.cantidad_inicial
                                    }"
                                />

                                <small>
                                    Número de unidades recibidas.
                                </small>

                                <p
                                    v-if="form.errors.cantidad_inicial"
                                    class="error"
                                >
                                    {{ form.errors.cantidad_inicial }}
                                </p>
                            </div>

                        </div>

                        <h3 class="titulo-grupo segundo-grupo">
                            Recepción y vencimiento
                        </h3>

                        <div class="campos-grid">

                            <!-- FECHA DE RECEPCIÓN -->

                            <div class="campo">
                                <label for="fecha_recepcion">
                                    Fecha de recepción
                                    <span class="obligatorio">*</span>
                                </label>

                                <input
                                    id="fecha_recepcion"
                                    v-model="form.fecha_recepcion"
                                    type="date"
                                    required
                                    :class="{
                                        'input-error':
                                            form.errors.fecha_recepcion
                                    }"
                                />

                                <p
                                    v-if="form.errors.fecha_recepcion"
                                    class="error"
                                >
                                    {{ form.errors.fecha_recepcion }}
                                </p>
                            </div>

                            <!-- FECHA DE VENCIMIENTO -->

                            <div class="campo">
                                <label for="fecha_vencimiento">
                                    Fecha de vencimiento
                                    <span class="obligatorio">*</span>
                                </label>

                                <input
                                    id="fecha_vencimiento"
                                    v-model="form.fecha_vencimiento"
                                    type="date"
                                    required
                                    :class="{
                                        'input-error':
                                            form.errors.fecha_vencimiento
                                    }"
                                />

                                <p
                                    v-if="form.errors.fecha_vencimiento"
                                    class="error"
                                >
                                    {{ form.errors.fecha_vencimiento }}
                                </p>
                            </div>
                        </div>

                        <div class="aviso-formulario">
                            <svg viewBox="0 0 24 24">
                                <path :d="iconos.informacion" />
                            </svg>

                            <p>
                                Verifica el número de lote,
                                las cantidades y las fechas
                                antes de registrar la recepción.
                            </p>
                        </div>

                        <div class="formulario-inferior">
                            <span>* Campos obligatorios</span>

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
                                    :disabled="form.processing"
                                >
                                    {{
                                        form.processing
                                            ? 'Guardando...'
                                            : 'Guardar lote'
                                    }}
                                </button>
                            </div>
                        </div>
                    </form>
                </section>
            </Transition>

            <!-- TABLA DE INVENTARIO -->

            <section class="tarjeta">

                <div class="tarjeta-encabezado">
                    <div class="titulo-con-icono">
                        <div class="icono-seccion">
                            <svg viewBox="0 0 24 24">
                                <path :d="iconos.inventario" />
                            </svg>
                        </div>

                        <div>
                            <h2>Lotes registrados</h2>

                            <p>
                                Inventario ordenado por fecha
                                de vencimiento (FEFO).
                            </p>
                        </div>
                    </div>

                    <span class="contador">
                        {{ totalLotes }} lotes
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
                            aria-label="Buscar lotes"
                            placeholder="Buscar medicamento o lote..."
                        />
                    </div>

                    <select
                        v-model="filtroEstado"
                        aria-label="Filtrar por vencimiento"
                    >
                        <option value="todos">
                            Todos los vencimientos
                        </option>

                        <option value="vigente">
                            Vigentes
                        </option>

                        <option value="proximo_vencer">
                            Próximos a vencer
                        </option>

                        <option value="vencido">
                            Vencidos
                        </option>
                    </select>

                    <select
                        v-model="filtroExistencias"
                        aria-label="Filtrar por existencias"
                    >
                        <option value="todos">
                            Todas las existencias
                        </option>

                        <option value="con_stock">
                            Con existencias
                        </option>

                        <option value="sin_stock">
                            Sin existencias
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
                                <th>Medicamento</th>
                                <th>Número de lote</th>
                                <th>Recepción</th>
                                <th>Vencimiento</th>
                                <th>Cantidad inicial</th>
                                <th>Existencia física</th>
                                <th>Estado</th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr
                                v-for="lote in lotesFiltrados"
                                :key="lote.id"
                            >
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
                                                    lote.medicamento?.nombre
                                                        ?? 'Sin medicamento'
                                                }}
                                            </strong>

                                            <small>
                                                {{
                                                    lote.medicamento?.codigo
                                                        ?? '—'
                                                }}
                                            </small>
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    <span class="numero-lote">
                                        {{ lote.numero_lote }}
                                    </span>
                                </td>

                                <td>
                                    {{ formatearFecha(lote.fecha_recepcion) }}
                                </td>

                                <td>
                                    <span
                                        class="fecha-vencimiento"
                                        :class="{
                                            'texto-rojo':
                                                lote.estado_vencimiento === 'vencido',
                                            'texto-naranja':
                                                lote.estado_vencimiento === 'proximo_vencer'
                                        }"
                                    >
                                        <svg viewBox="0 0 24 24">
                                            <path :d="iconos.calendario" />
                                        </svg>

                                        {{
                                            formatearFecha(
                                                lote.fecha_vencimiento
                                            )
                                        }}
                                    </span>
                                </td>

                                <td>
                                    {{ formatearNumero(lote.cantidad_inicial) }}

                                    <small class="unidad">
                                        unidades
                                    </small>
                                </td>

                                <td>
                                    <strong class="existencia">
                                        {{
                                            formatearNumero(
                                                lote.cantidad_disponible
                                            )
                                        }}
                                    </strong>

                                    <small class="unidad">
                                        unidades
                                    </small>

                                    <div
                                        v-if="Number(lote.cantidad_disponible) === 0"
                                        class="sin-stock"
                                    >
                                        Sin existencias
                                    </div>
                                </td>

                                <td>
                                    <span
                                        class="estado"
                                        :class="estadoLote(lote).clase"
                                    >
                                        <span class="punto-estado"></span>

                                        {{ estadoLote(lote).texto }}
                                    </span>
                                </td>
                            </tr>

                            <tr v-if="lotesFiltrados.length === 0">
                                <td colspan="7">
                                    <div class="sin-resultados">
                                        <div class="icono-vacio">
                                            <svg viewBox="0 0 24 24">
                                                <path :d="iconos.inventario" />
                                            </svg>
                                        </div>

                                        <strong>
                                            {{
                                                totalLotes === 0
                                                    ? 'No hay lotes registrados'
                                                    : 'No se encontraron resultados'
                                            }}
                                        </strong>

                                        <p>
                                            {{
                                                totalLotes === 0
                                                    ? 'Registra un lote para comenzar.'
                                                    : 'Intenta modificar los filtros.'
                                            }}
                                        </p>

                                        <button
                                            v-if="totalLotes > 0"
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
                    Mostrando {{ lotesFiltrados.length }}
                    de {{ totalLotes }} lotes
                </div>
            </section>

            <!-- INFORMACIÓN COMPLEMENTARIA -->

            <section class="informacion-inventario">
                <div class="icono-seccion">
                    <svg viewBox="0 0 24 24">
                        <path :d="iconos.informacion" />
                    </svg>
                </div>

                <div>
                    <h3>Información sobre el inventario</h3>

                    <p>
                        Las alertas de vencimiento utilizan un
                        umbral de 30 días. Las existencias físicas
                        incluyen unidades vencidas, mientras que
                        las unidades utilizables corresponden a
                        lotes vigentes o próximos a vencer.
                    </p>

                    <p>
                        Las entradas y salidas se registran con
                        trazabilidad. Las salidas aplican el método
                        FEFO y excluyen los lotes vencidos.
                    </p>
                </div>
            </section>

        </div>
    </FarmaLayout>
</template>

<style scoped>
.modulo-inventario {
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
    grid-template-columns: repeat(5, minmax(0, 1fr));
    gap: 14px;
}

.tarjeta-indicador {
    min-width: 0;
    padding: 17px;
    border: 1px solid var(--fc-border);
    border-radius: 14px;
    background: white;
    text-align: left;
    box-shadow: 0 3px 15px rgba(15, 23, 42, 0.03);
    transition: transform 0.2s, box-shadow 0.2s;
}

.tarjeta-indicador.interactivo:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 22px rgba(15, 23, 42, 0.08);
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
    gap: 8px;
}

.indicador-superior > span {
    color: #64748b;
    font-size: 11px;
    font-weight: 600;
    line-height: 1.5;
}

.icono-indicador {
    display: grid;
    place-items: center;
    width: 35px;
    height: 35px;
    flex-shrink: 0;
    border-radius: 9px;
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

.icono-indicador.rojo {
    background: #fef2f2;
    color: #dc2626;
}

.cantidad {
    display: block;
    margin: 13px 0 6px;
    color: var(--fc-primary);
    font-size: 30px;
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

.cantidad.rojo {
    color: #b91c1c;
}

.tarjeta-indicador p {
    color: #64748b;
    font-size: 10px;
    line-height: 1.6;
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
    right: -45px;
    top: -100px;
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
    font-size: 12px;
    line-height: 1.8;
    color: rgba(255, 255, 255, 0.85);
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

/* MENSAJE DE ÉXITO */

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

.mensaje-exito span {
    flex: 1;
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

.campo input:focus,
.campo select:focus,
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
    color: #64748b;
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

.aviso-formulario {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-top: 24px;
    padding: 14px;
    border: 1px solid var(--fc-border);
    border-radius: 9px;
    background: var(--fc-light);
    color: var(--fc-primary);
    font-size: 12px;
    line-height: 1.6;
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
    min-width: 160px;
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
    min-width: 900px;
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

.fecha-vencimiento {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    white-space: nowrap;
}

.fecha-vencimiento svg {
    width: 15px;
    height: 15px;
}

.texto-rojo {
    color: #b91c1c;
    font-weight: 600;
}

.texto-naranja {
    color: #c2410c;
    font-weight: 600;
}

.existencia {
    color: var(--fc-primary);
    font-size: 14px;
    font-weight: 700;
}

.unidad {
    display: block;
    margin-top: 3px;
    color: #64748b;
    font-size: 10px;
}

.sin-stock {
    margin-top: 5px;
    color: #b91c1c;
    font-size: 10px;
    font-weight: 600;
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

.informacion-inventario {
    display: flex;
    align-items: flex-start;
    gap: 15px;
    padding: 20px;
    border: 1px solid var(--fc-border);
    border-radius: 12px;
    background: white;
}

.informacion-inventario h3 {
    margin-bottom: 8px;
    color: var(--fc-primary);
    font-size: 13px;
    font-weight: 700;
}

.informacion-inventario p {
    color: #64748b;
    font-size: 12px;
    line-height: 1.8;
}

.informacion-inventario p + p {
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

@media (max-width: 1450px) {
    .indicadores {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }
}

@media (max-width: 1050px) {
    .indicadores {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .panel-fefo {
        flex-wrap: wrap;
    }

    .boton-fefo {
        margin-left: 85px;
    }
}

@media (max-width: 600px) {
    .indicadores {
        gap: 10px;
    }

    .tarjeta-indicador {
        padding: 13px;
    }

    .indicador-superior > span {
        font-size: 10px;
    }

    .icono-indicador {
        width: 30px;
        height: 30px;
    }

    .cantidad {
        font-size: 25px;
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

    .filtros select {
        flex: 1;
        min-width: 130px;
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