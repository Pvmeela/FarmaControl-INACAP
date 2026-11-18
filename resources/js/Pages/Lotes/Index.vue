<script setup>
import { computed, ref } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';

const props = defineProps({
    lotes: {
        type: Array,
        default: () => [],
    },
    medicamentos: {
        type: Array,
        default: () => [],
    },
});

const mostrarFormulario = ref(false);
const busqueda = ref('');

const form = useForm({
    medicamento_id: '',
    numero_lote: '',
    fecha_recepcion: '',
    fecha_vencimiento: '',
    cantidad_inicial: '',
});

// Convertir una fecha de MySQL a una fecha local.
function convertirFecha(fecha) {
    if (!fecha) return null;

    const valor = String(fecha).substring(0, 10);
    const [anio, mes, dia] = valor.split('-').map(Number);

    if (!anio || !mes || !dia) return null;

    const resultado = new Date(anio, mes - 1, dia);

    if (
        resultado.getFullYear() !== anio ||
        resultado.getMonth() !== mes - 1 ||
        resultado.getDate() !== dia
    ) {
        return null;
    }

    return resultado;
}

// Calcular los días restantes para el vencimiento.
function diasHastaVencimiento(fecha) {
    const vencimiento = convertirFecha(fecha);

    if (!vencimiento) return null;

    const hoy = new Date();

    const actual = new Date(
        hoy.getFullYear(),
        hoy.getMonth(),
        hoy.getDate()
    );

    return Math.round(
        (vencimiento - actual) / 86400000
    );
}

// Determinar el estado de cada lote.
function estadoLote(lote) {
    const dias = diasHastaVencimiento(
        lote.fecha_vencimiento
    );

    if (dias === null) {
        return {
            texto: 'Sin fecha',
            clase: 'bg-slate-100 text-slate-600',
        };
    }

    if (dias < 0) {
        return {
            texto: 'Vencido',
            clase: 'bg-red-100 text-red-700',
        };
    }

    if (dias <= 30) {
        return {
            texto: 'Próximo a vencer',
            clase: 'bg-amber-100 text-amber-800',
        };
    }

    return {
        texto: 'Vigente',
        clase: 'bg-emerald-100 text-emerald-700',
    };
}

// Formatear fechas para Chile.
function formatearFecha(fecha) {
    const valor = convertirFecha(fecha);

    return valor
        ? valor.toLocaleDateString('es-CL')
        : '—';
}

// Buscar medicamentos o números de lote.
const lotesFiltrados = computed(() => {
    const texto = busqueda.value
        .trim()
        .toLowerCase();

    return props.lotes.filter((lote) => {
        const nombre = lote.medicamento?.nombre ?? '';
        const codigo = lote.medicamento?.codigo ?? '';
        const numeroLote = String(
            lote.numero_lote ?? ''
        );

        return (
            nombre.toLowerCase().includes(texto) ||
            codigo.toLowerCase().includes(texto) ||
            numeroLote.toLowerCase().includes(texto)
        );
    });
});

// Total de lotes registrados.
const totalLotes = computed(() =>
    props.lotes.length
);

// Existencias físicas:
// incluye unidades de lotes vencidos.
const unidadesDisponibles = computed(() =>
    props.lotes.reduce(
        (total, lote) =>
            total + Number(
                lote.cantidad_disponible ?? 0
            ),
        0
    )
);

// Existencias utilizables:
// excluye lotes vencidos o sin fecha válida.
const unidadesUtilizables = computed(() =>
    props.lotes.reduce((total, lote) => {
        const dias = diasHastaVencimiento(
            lote.fecha_vencimiento
        );

        if (dias === null || dias < 0) {
            return total;
        }

        return total + Number(
            lote.cantidad_disponible ?? 0
        );
    }, 0)
);

// Lotes próximos a vencer en 30 días.
const lotesProximos = computed(() =>
    props.lotes.filter((lote) => {
        const dias = diasHastaVencimiento(
            lote.fecha_vencimiento
        );

        return (
            dias !== null &&
            dias >= 0 &&
            dias <= 30
        );
    }).length
);

// Lotes vencidos.
const lotesVencidos = computed(() =>
    props.lotes.filter((lote) => {
        const dias = diasHastaVencimiento(
            lote.fecha_vencimiento
        );

        return dias !== null && dias < 0;
    }).length
);

// Registrar un lote nuevo.
function guardarLote() {
    if (form.processing) return;

    form.post('/lotes', {
        preserveScroll: true,

        onSuccess: () => {
            form.reset();
            mostrarFormulario.value = false;
        },
    });
}

// Cerrar sesión.
function cerrarSesion() {
    router.post('/logout');
}
</script>

<template>
    <Head title="Inventario y lotes - FarmaControl" />

    <div class="min-h-screen bg-slate-50 text-slate-800">

        <!-- ENCABEZADO -->
        <header class="border-b border-slate-200 bg-white">
            <div
                class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-4 px-6 py-5"
            >
                <div>
                    <h1 class="text-2xl font-bold text-blue-900">
                        FarmaControl
                    </h1>

                    <p class="text-sm text-slate-500">
                        Sistema de gestión farmacéutica
                    </p>
                </div>

                <nav class="flex flex-wrap items-center gap-3">
                    <Link
                        href="/dashboard"
                        class="rounded-lg px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100"
                    >
                        Panel principal
                    </Link>

                    <Link
                        href="/medicamentos"
                        class="rounded-lg px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100"
                    >
                        Medicamentos
                    </Link>

                    <Link
                        href="/movimientos"
                        class="rounded-lg px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100"
                    >
                        Movimientos
                    </Link>

                    <button
                        type="button"
                        @click="cerrarSesion"
                        class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100"
                    >
                        Cerrar sesión
                    </button>
                </nav>
            </div>
        </header>

        <!-- CONTENIDO PRINCIPAL -->
        <main class="mx-auto max-w-7xl px-6 py-8">

            <!-- TÍTULO -->
            <div
                class="mb-8 flex flex-wrap items-center justify-between gap-4"
            >
                <div>
                    <h2 class="text-2xl font-bold text-slate-900">
                        Inventario y gestión de lotes
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        Control de existencias, recepción y vencimientos.
                    </p>
                </div>

                <button
                    type="button"
                    @click="mostrarFormulario = !mostrarFormulario"
                    class="rounded-xl bg-blue-800 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-900"
                >
                    {{
                        mostrarFormulario
                            ? 'Cerrar formulario'
                            : '+ Registrar lote'
                    }}
                </button>
            </div>

            <!-- INDICADORES -->
            <div
                class="mb-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-5"
            >

                <!-- TOTAL DE LOTES -->
                <div
                    class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"
                >
                    <p class="text-sm text-slate-500">
                        Lotes registrados
                    </p>

                    <p class="mt-2 text-3xl font-bold text-blue-900">
                        {{ totalLotes }}
                    </p>
                </div>

                <!-- EXISTENCIAS FÍSICAS -->
                <div
                    class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"
                >
                    <p class="text-sm text-slate-500">
                        Existencias físicas
                    </p>

                    <p class="mt-2 text-3xl font-bold text-blue-900">
                        {{ unidadesDisponibles }}
                    </p>
                </div>

                <!-- UNIDADES UTILIZABLES -->
                <div
                    class="rounded-2xl border border-emerald-200 bg-white p-5 shadow-sm"
                >
                    <p class="text-sm text-slate-500">
                        Unidades utilizables
                    </p>

                    <p class="mt-2 text-3xl font-bold text-emerald-700">
                        {{ unidadesUtilizables }}
                    </p>
                </div>

                <!-- LOTES PRÓXIMOS A VENCER -->
                <div
                    class="rounded-2xl border border-amber-200 bg-white p-5 shadow-sm"
                >
                    <p class="text-sm text-slate-500">
                        Lotes próximos a vencer
                    </p>

                    <p class="mt-2 text-3xl font-bold text-amber-700">
                        {{ lotesProximos }}
                    </p>
                </div>

                <!-- LOTES VENCIDOS -->
                <div
                    class="rounded-2xl border border-red-200 bg-white p-5 shadow-sm"
                >
                    <p class="text-sm text-slate-500">
                        Lotes vencidos
                    </p>

                    <p class="mt-2 text-3xl font-bold text-red-700">
                        {{ lotesVencidos }}
                    </p>
                </div>
            </div>

            <!-- FORMULARIO PARA REGISTRAR LOTES -->
            <section
                v-if="mostrarFormulario"
                class="mb-8 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"
            >
                <div class="mb-6">
                    <h3 class="text-lg font-bold text-blue-900">
                        Registrar nuevo lote
                    </h3>

                    <p class="mt-1 text-sm text-slate-500">
                        Completa la información de recepción del medicamento.
                    </p>
                </div>

                <form
                    @submit.prevent="guardarLote"
                    class="grid gap-5 md:grid-cols-2"
                >

                    <!-- MEDICAMENTO -->
                    <div class="md:col-span-2">
                        <label
                            for="medicamento_id"
                            class="mb-2 block text-sm font-semibold"
                        >
                            Medicamento
                        </label>

                        <select
                            id="medicamento_id"
                            v-model="form.medicamento_id"
                            required
                            class="w-full rounded-lg border border-slate-300 bg-white px-4 py-3 focus:border-blue-600 focus:outline-none"
                        >
                            <option value="">
                                Seleccionar medicamento
                            </option>

                            <option
                                v-for="medicamento in medicamentos"
                                :key="medicamento.id"
                                :value="medicamento.id"
                            >
                                {{ medicamento.nombre }} -
                                {{ medicamento.codigo }}
                            </option>
                        </select>

                        <p
                            v-if="form.errors.medicamento_id"
                            class="mt-1 text-sm text-red-600"
                        >
                            {{ form.errors.medicamento_id }}
                        </p>
                    </div>

                    <!-- NÚMERO DE LOTE -->
                    <div>
                        <label
                            for="numero_lote"
                            class="mb-2 block text-sm font-semibold"
                        >
                            Número de lote
                        </label>

                        <input
                            id="numero_lote"
                            v-model="form.numero_lote"
                            type="text"
                            maxlength="100"
                            required
                            placeholder="Ej.: PAR-2026-001"
                            class="w-full rounded-lg border border-slate-300 px-4 py-3 focus:border-blue-600 focus:outline-none"
                        />

                        <p
                            v-if="form.errors.numero_lote"
                            class="mt-1 text-sm text-red-600"
                        >
                            {{ form.errors.numero_lote }}
                        </p>
                    </div>

                    <!-- CANTIDAD RECIBIDA -->
                    <div>
                        <label
                            for="cantidad_inicial"
                            class="mb-2 block text-sm font-semibold"
                        >
                            Cantidad recibida
                        </label>

                        <input
                            id="cantidad_inicial"
                            v-model="form.cantidad_inicial"
                            type="number"
                            min="1"
                            step="1"
                            required
                            placeholder="Ej.: 100"
                            class="w-full rounded-lg border border-slate-300 px-4 py-3 focus:border-blue-600 focus:outline-none"
                        />

                        <p
                            v-if="form.errors.cantidad_inicial"
                            class="mt-1 text-sm text-red-600"
                        >
                            {{ form.errors.cantidad_inicial }}
                        </p>
                    </div>

                    <!-- FECHA DE RECEPCIÓN -->
                    <div>
                        <label
                            for="fecha_recepcion"
                            class="mb-2 block text-sm font-semibold"
                        >
                            Fecha de recepción
                        </label>

                        <input
                            id="fecha_recepcion"
                            v-model="form.fecha_recepcion"
                            type="date"
                            required
                            class="w-full rounded-lg border border-slate-300 px-4 py-3 focus:border-blue-600 focus:outline-none"
                        />

                        <p
                            v-if="form.errors.fecha_recepcion"
                            class="mt-1 text-sm text-red-600"
                        >
                            {{ form.errors.fecha_recepcion }}
                        </p>
                    </div>

                    <!-- FECHA DE VENCIMIENTO -->
                    <div>
                        <label
                            for="fecha_vencimiento"
                            class="mb-2 block text-sm font-semibold"
                        >
                            Fecha de vencimiento
                        </label>

                        <input
                            id="fecha_vencimiento"
                            v-model="form.fecha_vencimiento"
                            type="date"
                            required
                            class="w-full rounded-lg border border-slate-300 px-4 py-3 focus:border-blue-600 focus:outline-none"
                        />

                        <p
                            v-if="form.errors.fecha_vencimiento"
                            class="mt-1 text-sm text-red-600"
                        >
                            {{ form.errors.fecha_vencimiento }}
                        </p>
                    </div>

                    <!-- BOTONES -->
                    <div
                        class="flex justify-end gap-3 md:col-span-2"
                    >
                        <button
                            type="button"
                            @click="mostrarFormulario = false"
                            class="rounded-lg border border-slate-300 px-5 py-3 text-sm font-medium hover:bg-slate-50"
                        >
                            Cancelar
                        </button>

                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="rounded-lg bg-blue-800 px-6 py-3 text-sm font-semibold text-white hover:bg-blue-900 disabled:opacity-50"
                        >
                            {{
                                form.processing
                                    ? 'Guardando...'
                                    : 'Guardar lote'
                            }}
                        </button>
                    </div>
                </form>
            </section>

            <!-- TABLA DE LOTES -->
            <section
                class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"
            >
                <div
                    class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-200 p-6"
                >
                    <div>
                        <h3 class="text-lg font-bold text-blue-900">
                            Lotes registrados
                        </h3>

                        <p class="text-sm text-slate-500">
                            Ordenados por fecha de vencimiento (FEFO).
                        </p>
                    </div>

                    <input
                        v-model="busqueda"
                        type="search"
                        placeholder="Buscar medicamento o lote..."
                        aria-label="Buscar lotes"
                        class="w-full rounded-lg border border-slate-300 px-4 py-3 text-sm focus:border-blue-600 focus:outline-none sm:w-72"
                    />
                </div>

                <div class="overflow-x-auto">
                    <table
                        class="w-full min-w-[900px] text-left text-sm"
                    >
                        <thead class="bg-slate-100 text-slate-600">
                            <tr>
                                <th class="px-5 py-4 font-semibold">
                                    Medicamento
                                </th>

                                <th class="px-5 py-4 font-semibold">
                                    Número de lote
                                </th>

                                <th class="px-5 py-4 font-semibold">
                                    Recepción
                                </th>

                                <th class="px-5 py-4 font-semibold">
                                    Vencimiento
                                </th>

                                <th class="px-5 py-4 font-semibold">
                                    Cantidad inicial
                                </th>

                                <th class="px-5 py-4 font-semibold">
                                    Existencia física
                                </th>

                                <th class="px-5 py-4 font-semibold">
                                    Estado
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-100">
                            <tr
                                v-for="lote in lotesFiltrados"
                                :key="lote.id"
                                class="hover:bg-slate-50"
                            >
                                <!-- MEDICAMENTO -->
                                <td class="px-5 py-4">
                                    <p class="font-semibold text-slate-800">
                                        {{
                                            lote.medicamento?.nombre ??
                                            'Sin medicamento'
                                        }}
                                    </p>

                                    <p class="text-xs text-slate-500">
                                        {{
                                            lote.medicamento?.codigo ??
                                            '—'
                                        }}
                                    </p>
                                </td>

                                <!-- NÚMERO DE LOTE -->
                                <td class="px-5 py-4 font-medium">
                                    {{ lote.numero_lote }}
                                </td>

                                <!-- RECEPCIÓN -->
                                <td class="px-5 py-4">
                                    {{
                                        formatearFecha(
                                            lote.fecha_recepcion
                                        )
                                    }}
                                </td>

                                <!-- VENCIMIENTO -->
                                <td class="px-5 py-4">
                                    {{
                                        formatearFecha(
                                            lote.fecha_vencimiento
                                        )
                                    }}
                                </td>

                                <!-- CANTIDAD INICIAL -->
                                <td class="px-5 py-4">
                                    {{ lote.cantidad_inicial }}
                                </td>

                                <!-- EXISTENCIA FÍSICA -->
                                <td
                                    class="px-5 py-4 font-semibold text-blue-900"
                                >
                                    {{ lote.cantidad_disponible }}
                                </td>

                                <!-- ESTADO -->
                                <td class="px-5 py-4">
                                    <span
                                        :class="estadoLote(lote).clase"
                                        class="inline-flex rounded-full px-3 py-1 text-xs font-semibold"
                                    >
                                        {{ estadoLote(lote).texto }}
                                    </span>
                                </td>
                            </tr>

                            <tr v-if="lotesFiltrados.length === 0">
                                <td
                                    colspan="7"
                                    class="px-6 py-12 text-center text-slate-500"
                                >
                                    No se encontraron lotes registrados.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- INFORMACIÓN DE INVENTARIO -->
            <p class="mt-5 text-xs text-slate-500">
                Las alertas de vencimiento utilizan un umbral de
                30 días. Las existencias físicas incluyen lotes
                vencidos; las unidades utilizables excluyen lotes
                vencidos o sin fecha válida. Las entradas y salidas
                de inventario se registran con trazabilidad.
                Las salidas aplican el método FEFO y excluyen
                los lotes vencidos.
            </p>
        </main>

        <!-- MARCA DE AGUA ACADÉMICA -->
        <div
            class="pointer-events-none fixed bottom-3 right-3 z-50 rounded-lg border border-slate-200 bg-white/90 px-3 py-2 text-right text-[10px] leading-4 text-slate-500 shadow-sm"
        >
            Uso académico · INACAP
            <br />
            Sistema de simulación · Sin validez legal
        </div>
    </div>
</template>
