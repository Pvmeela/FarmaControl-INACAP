<script setup>
import { computed, ref } from 'vue'
import { Head, Link, router, usePage } from '@inertiajs/vue3'

const page = usePage()

const props = defineProps({
    alertas: {
        type: Object,
        default: () => ({}),
    },
})

const cerrandoSesion = ref(false)
const menuAbierto = ref(false)

const usuario = computed(() => page.props.auth?.user ?? null)

const nombreUsuario = computed(
    () => usuario.value?.name ?? 'Usuario'
)

const rolUsuario = computed(
    () => usuario.value?.rol ?? 'Sin rol asignado'
)

const esAdministrador = computed(
    () => rolUsuario.value === 'Administrador'
)

const puedeConsultarDispensaciones = computed(() =>
    [
        'Administrador',
        'Quimico Farmaceutico',
        'Tecnico en Farmacia',
    ].includes(rolUsuario.value)
)

const entornoActual = computed(() =>
    ['privada', 'cesfam'].includes(page.props.entorno)
        ? page.props.entorno
        : null
)

const nombreEntorno = computed(() => {
    if (entornoActual.value === 'privada') {
        return 'Farmacia Privada'
    }

    if (entornoActual.value === 'cesfam') {
        return 'Farmacia Asistencial (CESFAM)'
    }

    return 'Entorno no seleccionado'
})

const descripcionEntorno = computed(() => {
    if (entornoActual.value === 'privada') {
        return 'Gestiona medicamentos, inventario y ventas simuladas.'
    }

    if (entornoActual.value === 'cesfam') {
        return 'Gestiona medicamentos, pacientes, recetas y dispensaciones.'
    }

    return 'Selecciona un entorno para comenzar.'
})

// ALERTAS DEL INVENTARIO
const indicadores = computed(() => [
    {
        nombre: 'Quiebres de stock',
        cantidad: Number(props.alertas?.quiebres_stock ?? 0),
        descripcion: 'Medicamentos sin stock utilizable',
        icono: '🚨',
        borde: 'border-red-200',
        numero: 'text-red-700',
        fondo: 'bg-red-50',
    },
    {
        nombre: 'Stock bajo',
        cantidad: Number(props.alertas?.stock_bajo ?? 0),
        descripcion: 'Medicamentos bajo el mínimo configurado',
        icono: '📉',
        borde: 'border-orange-200',
        numero: 'text-orange-700',
        fondo: 'bg-orange-50',
    },
    {
        nombre: 'Próximos a vencer',
        cantidad: Number(props.alertas?.proximos_vencer ?? 0),
        descripcion: 'Lotes con vencimiento cercano',
        icono: '⏳',
        borde: 'border-amber-200',
        numero: 'text-amber-700',
        fondo: 'bg-amber-50',
    },
    {
        nombre: 'Lotes vencidos',
        cantidad: Number(props.alertas?.lotes_vencidos ?? 0),
        descripcion: 'Lotes vencidos con existencias',
        icono: '⚠️',
        borde: 'border-red-200',
        numero: 'text-red-700',
        fondo: 'bg-red-50',
    },
])

const totalAlertas = computed(() =>
    indicadores.value.reduce(
        (suma, item) => suma + item.cantidad,
        0
    )
)

// MÓDULOS DEL SISTEMA
const modulos = [
    {
        nombre: 'Medicamentos',
        descripcion: 'Catálogo de medicamentos.',
        enlace: '/medicamentos',
        icono: '💊',
        entornos: ['privada', 'cesfam'],
    },
    {
        nombre: 'Inventario y lotes',
        descripcion: 'Stock y fechas de vencimiento.',
        enlace: '/lotes',
        icono: '📦',
        entornos: ['privada', 'cesfam'],
    },
    {
        nombre: 'Movimientos',
        descripcion: 'Entradas y salidas de inventario.',
        enlace: '/movimientos',
        icono: '🔄',
        entornos: ['privada', 'cesfam'],
    },
    {
        nombre: 'Ventas',
        descripcion: 'Ventas y comprobantes simulados.',
        enlace: '/ventas',
        icono: '🛒',
        entornos: ['privada'],
    },
    {
        nombre: 'Pacientes',
        descripcion: 'Registro de pacientes.',
        enlace: '/pacientes',
        icono: '👤',
        entornos: ['cesfam'],
    },
    {
        nombre: 'Recetas',
        descripcion: 'Registro de recetas médicas.',
        enlace: '/recetas',
        icono: '📋',
        entornos: ['privada', 'cesfam'],
    },
    {
        nombre: 'Dispensaciones',
        descripcion: 'Entrega de medicamentos.',
        enlace: '/dispensaciones',
        icono: '🏥',
        entornos: ['cesfam'],
    },
]

const modulosVisibles = computed(() =>
    modulos.filter((modulo) => {
        if (
            !entornoActual.value ||
            !modulo.entornos.includes(entornoActual.value)
        ) {
            return false
        }

        if (modulo.nombre === 'Dispensaciones') {
            return puedeConsultarDispensaciones.value
        }

        return true
    })
)

// MENÚ DE USUARIO
function cerrarMenu() {
    menuAbierto.value = false
}

function cerrarSesion() {
    if (cerrandoSesion.value) return

    cerrarMenu()
    cerrandoSesion.value = true

    router.post('/logout', {}, {
        onFinish: () => {
            cerrandoSesion.value = false
        },
    })
}
</script>

<template>
    <Head title="Panel principal | FarmaControl" />

    <div class="min-h-screen bg-[#F8FAFC] text-slate-800">

        <!-- ENCABEZADO -->
        <header class="border-b border-blue-100 bg-white shadow-sm">
            <div
                class="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-4 px-5 py-4"
            >
                <Link href="/" class="flex items-center gap-3">
                    <div
                        class="flex h-11 w-11 items-center justify-center rounded-xl bg-[#2563EB] text-2xl font-bold text-white shadow-sm"
                    >
                        +
                    </div>

                    <div>
                        <h1 class="text-xl font-bold text-[#1E3A8A]">
                            FarmaControl
                        </h1>

                        <p class="text-xs text-slate-500">
                            Sistema de Gestión Farmacéutica
                        </p>
                    </div>
                </Link>

                <div class="flex flex-wrap items-center gap-3">
                    <Link
                        href="/"
                        class="rounded-lg border border-blue-200 bg-white px-4 py-2 text-sm font-medium text-[#2563EB] transition hover:bg-blue-50"
                    >
                        Cambiar entorno
                    </Link>

                    <!-- MENÚ DESPLEGABLE -->
                    <div class="relative">
                        <button
                            type="button"
                            :aria-expanded="menuAbierto"
                            aria-haspopup="true"
                            @click="menuAbierto = !menuAbierto"
                            class="flex items-center gap-3 rounded-xl border border-blue-200 bg-[#F8FAFC] px-3 py-2 text-left transition hover:bg-[#DBEAFE]"
                        >
                            <span
                                class="flex h-9 w-9 items-center justify-center rounded-full bg-[#2563EB] text-sm font-bold text-white"
                            >
                                {{ nombreUsuario.charAt(0).toUpperCase() }}
                            </span>

                            <span class="hidden sm:block">
                                <span class="block text-sm font-semibold text-slate-800">
                                    {{ nombreUsuario }}
                                </span>

                                <span class="block text-xs text-slate-500">
                                    {{ rolUsuario }}
                                </span>
                            </span>

                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-4 w-4 text-[#2563EB] transition-transform"
                                :class="{ 'rotate-180': menuAbierto }"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="m6 9 6 6 6-6"
                                />
                            </svg>
                        </button>

                        <button
                            v-if="menuAbierto"
                            type="button"
                            aria-label="Cerrar menú"
                            class="fixed inset-0 z-40 cursor-default"
                            @click="cerrarMenu"
                        ></button>

                        <div
                            v-if="menuAbierto"
                            class="absolute right-0 z-50 mt-2 w-64 overflow-hidden rounded-xl border border-blue-100 bg-white shadow-xl"
                        >
                            <div class="border-b border-blue-100 bg-[#EFF6FF] px-4 py-3">
                                <p class="text-sm font-semibold text-[#1E3A8A]">
                                    {{ nombreUsuario }}
                                </p>

                                <p class="mt-1 text-xs text-slate-500">
                                    {{ rolUsuario }}
                                </p>
                            </div>

                            <!-- ADMINISTRACIÓN -->
                            <div v-if="esAdministrador" class="p-2">
                                <p
                                    class="px-3 pb-2 pt-1 text-[11px] font-semibold uppercase tracking-wider text-slate-400"
                                >
                                    Administración
                                </p>

                                <Link
                                    href="/usuarios"
                                    @click="cerrarMenu"
                                    class="flex items-center gap-3 rounded-lg px-3 py-3 text-sm font-medium text-slate-700 transition hover:bg-[#DBEAFE] hover:text-[#1E3A8A]"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="h-5 w-5 text-[#2563EB]"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2m18 0v-2a4 4 0 0 0-3-3.87M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm8-7.87a4 4 0 0 1 0 7.75"
                                        />
                                    </svg>

                                    Usuarios y roles
                                </Link>
                            </div>

                            <!-- CERRAR SESIÓN -->
                            <div class="border-t border-slate-100 p-2">
                                <button
                                    type="button"
                                    :disabled="cerrandoSesion"
                                    @click="cerrarSesion"
                                    class="flex w-full items-center gap-3 rounded-lg px-3 py-3 text-left text-sm font-medium text-red-600 transition hover:bg-red-50 disabled:opacity-50"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="h-5 w-5"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4m7 14 5-5-5-5m5 5H9"
                                        />
                                    </svg>

                                    {{
                                        cerrandoSesion
                                            ? 'Saliendo...'
                                            : 'Cerrar sesión'
                                    }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- CONTENIDO PRINCIPAL -->
        <main class="mx-auto max-w-6xl px-5 py-8">

            <!-- BIENVENIDA -->
            <section
                class="mb-7 rounded-2xl bg-gradient-to-r from-[#1E3A8A] to-[#2563EB] p-7 text-white shadow-md"
            >
                <p
                    class="mb-2 text-xs font-semibold uppercase tracking-wider text-blue-100"
                >
                    Panel principal
                </p>

                <h2 class="text-2xl font-bold sm:text-3xl">
                    ¡Bienvenido/a, {{ nombreUsuario }}!
                </h2>

                <p class="mt-3 text-sm text-blue-50">
                    {{ descripcionEntorno }}
                </p>

                <div class="mt-5 flex flex-wrap gap-3">
                    <span
                        class="rounded-lg border border-white/30 bg-white/10 px-4 py-2 text-sm"
                    >
                        {{ nombreEntorno }}
                    </span>

                    <span
                        class="rounded-lg border border-white/30 bg-white/10 px-4 py-2 text-sm"
                    >
                        Rol: {{ rolUsuario }}
                    </span>
                </div>
            </section>

            <!-- ALERTAS DEL INVENTARIO -->
            <section
                class="mb-8 rounded-2xl border border-blue-100 bg-white p-5 shadow-sm sm:p-6"
                aria-labelledby="titulo-alertas"
            >
                <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h3
                            id="titulo-alertas"
                            class="text-xl font-bold text-[#1E3A8A]"
                        >
                            Alertas de inventario
                        </h3>

                        <p class="mt-1 text-sm text-slate-500">
                            Indicadores calculados a partir de los registros del sistema.
                        </p>
                    </div>

                    <span
                        v-if="totalAlertas > 0"
                        class="rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-800"
                    >
                        {{ totalAlertas }} alertas
                    </span>

                    <span
                        v-else
                        class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-800"
                    >
                        Sin alertas registradas
                    </span>
                </div>

                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <div
                        v-for="indicador in indicadores"
                        :key="indicador.nombre"
                        :class="indicador.borde"
                        class="rounded-xl border bg-white p-4"
                    >
                        <div class="mb-3 flex items-center justify-between gap-2">
                            <span class="text-sm font-semibold text-slate-700">
                                {{ indicador.nombre }}
                            </span>

                            <span
                                :class="indicador.fondo"
                                class="rounded-lg p-2 text-xl"
                                aria-hidden="true"
                            >
                                {{ indicador.icono }}
                            </span>
                        </div>

                        <p
                            :class="indicador.numero"
                            class="text-3xl font-bold tabular-nums"
                        >
                            {{ indicador.cantidad }}
                        </p>

                        <p class="mt-2 text-xs leading-5 text-slate-500">
                            {{ indicador.descripcion }}
                        </p>
                    </div>
                </div>

                <div
                    class="mt-5 flex flex-wrap items-center justify-between gap-3 border-t border-blue-100 pt-4"
                >
                    <p class="text-xs text-slate-500">
                        Las alertas de vencimiento consideran un plazo de 30 días.
                    </p>

                    <Link
                        href="/lotes"
                        class="inline-flex items-center rounded-lg bg-[#DBEAFE] px-4 py-2 text-sm font-semibold text-[#1E3A8A] transition hover:bg-blue-200"
                    >
                        Ver inventario y lotes →
                    </Link>
                </div>
            </section>

            <!-- INFORMACIÓN DEL ENTORNO -->
            <section
                class="mb-8 flex flex-wrap items-center justify-between gap-4 rounded-xl border border-blue-100 bg-white p-5 shadow-sm"
            >
                <div>
                    <h3 class="font-semibold text-[#1E3A8A]">
                        Entorno de trabajo
                    </h3>

                    <p class="mt-1 text-sm text-slate-600">
                        {{ nombreEntorno }}
                    </p>

                    <p class="mt-1 text-xs text-slate-500">
                        Los módulos se muestran según el entorno y el rol del usuario.
                    </p>
                </div>

                <Link
                    href="/"
                    class="rounded-lg bg-[#DBEAFE] px-4 py-2 text-sm font-medium text-[#1E3A8A] transition hover:bg-blue-200"
                >
                    Seleccionar otro entorno
                </Link>
            </section>

            <!-- MÓDULOS -->
            <section>
                <div class="mb-5">
                    <h3 class="text-xl font-bold text-[#1E3A8A]">
                        Módulos del sistema
                    </h3>

                    <p class="mt-1 text-sm text-slate-500">
                        Selecciona el módulo que deseas utilizar.
                    </p>
                </div>

                <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    <Link
                        v-for="modulo in modulosVisibles"
                        :key="modulo.nombre"
                        :href="modulo.enlace"
                        class="group flex flex-col rounded-xl border border-blue-100 bg-white p-5 shadow-sm transition duration-200 hover:-translate-y-1 hover:border-[#60A5FA] hover:shadow-lg"
                    >
                        <div
                            class="mb-4 flex h-12 w-12 items-center justify-center rounded-xl bg-[#DBEAFE] text-2xl transition group-hover:bg-blue-200"
                        >
                            {{ modulo.icono }}
                        </div>

                        <h4 class="text-lg font-semibold text-[#1E3A8A]">
                            {{ modulo.nombre }}
                        </h4>

                        <p class="mt-2 flex-1 text-sm text-slate-500">
                            {{ modulo.descripcion }}
                        </p>

                        <span class="mt-5 text-sm font-semibold text-[#2563EB]">
                            Ingresar al módulo →
                        </span>
                    </Link>
                </div>

                <div
                    v-if="modulosVisibles.length === 0"
                    class="rounded-xl border border-slate-200 bg-white p-6 text-sm text-slate-500"
                >
                    No hay módulos disponibles para este entorno.
                </div>
            </section>

            <!-- PIE DE PÁGINA -->
            <footer
                class="mt-12 border-t border-blue-100 pt-5 text-center text-sm text-slate-500"
            >
                FarmaControl · Proyecto Integrado INACAP
            </footer>
        </main>

        <!-- MARCA DE AGUA ACADÉMICA -->
        <div
            class="pointer-events-none fixed bottom-3 right-3 z-50 rounded-lg border border-blue-100 bg-white/90 px-3 py-2 text-right text-[10px] text-slate-500 shadow-sm"
        >
            <p class="font-semibold text-[#1E3A8A]">
                Uso académico · INACAP
            </p>

            <p>
                Sistema de simulación · Sin validez legal
            </p>
        </div>
    </div>
</template>
