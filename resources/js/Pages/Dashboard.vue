<script setup>
import { computed, ref } from 'vue'
import { Head, Link, router, usePage } from '@inertiajs/vue3'

const props = defineProps({
    alertas: {
        type: Object,
        default: () => ({
            stock_bajo: null,
            quiebres_stock: null,
            proximos_vencer: null,
            lotes_vencidos: null,
        }),
    },
})

const busqueda = ref('')
const cerrandoSesion = ref(false)

const page = usePage()

const usuario = computed(() => page.props.auth?.user ?? null)

const nombreUsuario = computed(
    () => usuario.value?.name ?? 'Usuario'
)

const rolUsuario = computed(
    () => usuario.value?.rol ?? 'Sin rol asignado'
)

const puedeConsultarDispensaciones = computed(() =>
    [
        'Administrador',
        'Quimico Farmaceutico',
        'Tecnico en Farmacia',
    ].includes(rolUsuario.value)
)

const iconos = {
    farmacia: [
        '<path d="M12 2v20M2 12h20"/>',
    ],
    medicamentos: [
        '<path d="m10.5 20.5 10-10a6 6 0 0 0-8.5-8.5l-10 10a6 6 0 0 0 8.5 8.5Z"/>',
        '<path d="m8.5 8.5 7 7"/>',
    ],
    inventario: [
        '<path d="m12 2 9 5-9 5-9-5 9-5Z"/>',
        '<path d="M3 7v10l9 5 9-5V7"/>',
        '<path d="M12 12v10"/>',
    ],
    movimientos: [
        '<path d="M3 7h17"/>',
        '<path d="m16 3 4 4-4 4"/>',
        '<path d="M21 17H4"/>',
        '<path d="m8 13-4 4 4 4"/>',
    ],
    ventas: [
        '<rect x="3" y="4" width="18" height="16" rx="2"/>',
        '<path d="M7 8h10M7 12h10M7 16h5"/>',
    ],
    pacientes: [
        '<circle cx="12" cy="8" r="4"/>',
        '<path d="M4 21v-2a8 8 0 0 1 16 0v2"/>',
    ],
    recetas: [
        '<rect x="5" y="3" width="14" height="18" rx="2"/>',
        '<path d="M9 8h6M9 12h6M9 16h3"/>',
    ],
    dispensaciones: [
        '<path d="M3 10 12 3l9 7v11H3V10Z"/>',
        '<path d="M12 9v8M8 13h8"/>',
    ],
    usuarios: [
        '<circle cx="9" cy="8" r="3"/>',
        '<path d="M3 20v-2a6 6 0 0 1 12 0v2"/>',
        '<path d="M17 6a3 3 0 0 1 0 6M18 15a5 5 0 0 1 3 5"/>',
    ],
    alerta: [
        '<path d="M12 3 2 21h20L12 3Z"/>',
        '<path d="M12 9v5M12 18h.01"/>',
    ],
    calendario: [
        '<rect x="3" y="5" width="18" height="16" rx="2"/>',
        '<path d="M7 3v4M17 3v4M3 10h18"/>',
    ],
    escudo: [
        '<path d="M12 2 4 5v6c0 5 3.5 8.5 8 11 4.5-2.5 8-6 8-11V5l-8-3Z"/>',
        '<path d="m9 12 2 2 4-4"/>',
    ],
    grafico: [
        '<path d="M3 3v18h18"/>',
        '<path d="m7 15 4-4 3 3 5-7"/>',
    ],
    buscar: [
        '<circle cx="11" cy="11" r="7"/>',
        '<path d="m16 16 5 5"/>',
    ],
    flecha: [
        '<path d="M5 12h14M13 6l6 6-6 6"/>',
    ],
    salir: [
        '<path d="M9 4H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h4"/>',
        '<path d="M14 8l4 4-4 4M18 12H9"/>',
    ],
}

const indicadores = computed(() => [
    {
        nombre: 'Stock bajo',
        valor: props.alertas?.stock_bajo ?? '—',
        descripcion: 'Medicamentos bajo el mínimo',
        icono: 'grafico',
        color: 'amber',
    },
    {
        nombre: 'Quiebres de stock',
        valor: props.alertas?.quiebres_stock ?? '—',
        descripcion: 'Sin existencias utilizables',
        icono: 'alerta',
        color: 'red',
    },
    {
        nombre: 'Próximos a vencer',
        valor: props.alertas?.proximos_vencer ?? '—',
        descripcion: 'Lotes próximos a vencer en 30 días',
        icono: 'calendario',
        color: 'blue',
    },
    {
        nombre: 'Lotes vencidos',
        valor: props.alertas?.lotes_vencidos ?? '—',
        descripcion: 'Lotes fuera de vigencia',
        icono: 'escudo',
        color: 'rose',
    },
])

const modulos = [
    {
        nombre: 'Medicamentos',
        icono: 'medicamentos',
        descripcion: 'Administración del catálogo farmacéutico y la información de medicamentos.',
        enlace: '/medicamentos',
        disponible: true,
        categoria: 'Inventario',
        color: 'blue',
    },
    {
        nombre: 'Inventario y lotes',
        icono: 'inventario',
        descripcion: 'Control de existencias, lotes, fechas de vencimiento y disponibilidad.',
        enlace: '/lotes',
        disponible: true,
        categoria: 'Inventario',
        color: 'indigo',
    },
    {
        nombre: 'Movimientos de inventario',
        icono: 'movimientos',
        descripcion: 'Registro de entradas, salidas y trazabilidad del inventario mediante FEFO.',
        enlace: '/movimientos',
        disponible: true,
        categoria: 'Inventario',
        color: 'cyan',
    },
    {
        nombre: 'Ventas',
        icono: 'ventas',
        descripcion: 'Registro y consulta de operaciones de venta con actualización de inventario.',
        enlace: '/ventas',
        disponible: true,
        categoria: 'Operaciones',
        color: 'emerald',
    },
    {
        nombre: 'Pacientes',
        icono: 'pacientes',
        descripcion: 'Gestión y consulta de registros de pacientes del área asistencial.',
        enlace: '/pacientes',
        disponible: true,
        categoria: 'Asistencial',
        color: 'violet',
    },
    {
        nombre: 'Recetas',
        icono: 'recetas',
        descripcion: 'Registro, consulta y control de vigencia de prescripciones farmacéuticas.',
        enlace: '/recetas',
        disponible: true,
        categoria: 'Asistencial',
        color: 'blue',
    },
    {
        nombre: 'Dispensaciones',
        icono: 'dispensaciones',
        descripcion: 'Registro de entregas de medicamentos vinculadas a recetas e inventario.',
        enlace: '/dispensaciones',
        disponible: true,
        categoria: 'Asistencial',
        color: 'teal',
    },
    {
        nombre: 'Usuarios y roles',
        icono: 'usuarios',
        descripcion: 'Administración de cuentas de usuario y permisos de acceso.',
        enlace: null,
        disponible: false,
        categoria: 'Administración',
        color: 'slate',
    },
]

const modulosVisibles = computed(() =>
    modulos.filter(modulo =>
        modulo.nombre !== 'Dispensaciones'
        || puedeConsultarDispensaciones.value
    )
)

const modulosFiltrados = computed(() => {
    const termino = busqueda.value.trim().toLowerCase()

    if (!termino) return modulosVisibles.value

    return modulosVisibles.value.filter(modulo =>
        [modulo.nombre, modulo.descripcion, modulo.categoria]
            .join(' ')
            .toLowerCase()
            .includes(termino)
    )
})

const cantidadModulosActivos = computed(
    () => modulosVisibles.value.filter(modulo => modulo.disponible).length
)

const accesosRapidos = computed(() =>
    ['Ventas', 'Recetas', 'Dispensaciones']
        .map(nombre => modulosVisibles.value.find(m => m.nombre === nombre))
        .filter(Boolean)
)

function cerrarSesion() {
    if (cerrandoSesion.value) return

    cerrandoSesion.value = true

    router.post('/logout', {}, {
        onFinish: () => {
            cerrandoSesion.value = false
        },
    })
}
</script>

<template>
    <Head title="Panel de control | FarmaControl" />

    <div class="min-h-screen bg-[#f3f6fb] text-slate-800">

        <!-- ENCABEZADO -->
        <header
            class="sticky top-0 z-40 border-b border-slate-200 bg-white/95 shadow-sm backdrop-blur"
        >
            <div
                class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-4 px-5 py-4 sm:px-8"
            >
                <div class="flex items-center gap-3">
                    <div
                        class="flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-800 text-white shadow-md shadow-blue-100"
                    >
                        <svg
                            viewBox="0 0 24 24"
                            class="h-7 w-7"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2.5"
                            stroke-linecap="round"
                        >
                            <path d="M12 4v16M4 12h16"/>
                        </svg>
                    </div>

                    <div>
                        <h1 class="text-xl font-extrabold tracking-tight text-slate-900">
                            Farma<span class="text-blue-700">Control</span>
                        </h1>

                        <p class="text-xs text-slate-500">
                            Plataforma de gestión farmacéutica
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <div
                        class="hidden items-center gap-2 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs font-semibold text-emerald-700 sm:flex"
                    >
                        <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                        Panel disponible
                    </div>

                    <button
                        type="button"
                        :disabled="cerrandoSesion"
                        @click="cerrarSesion"
                        class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:border-red-200 hover:bg-red-50 hover:text-red-700 disabled:opacity-50"
                    >
                        <svg
                            viewBox="0 0 24 24"
                            class="h-4 w-4"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        >
                            <path d="M9 4H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h4"/>
                            <path d="M14 8l4 4-4 4M18 12H9"/>
                        </svg>

                        {{ cerrandoSesion ? 'Saliendo...' : 'Cerrar sesión' }}
                    </button>
                </div>
            </div>
        </header>

        <main class="mx-auto max-w-7xl px-5 py-8 sm:px-8">

            <!-- SECCIÓN PRINCIPAL -->
            <section
                class="relative mb-8 overflow-hidden rounded-3xl bg-gradient-to-br from-[#102b65] via-[#17418f] to-[#2563b9] px-7 py-9 text-white shadow-xl shadow-blue-900/10 sm:px-10 sm:py-11"
            >
                <!-- FIGURAS GEOMÉTRICAS -->
                <div
                    class="pointer-events-none absolute -right-20 -top-24 h-72 w-72 rounded-full border-[45px] border-white/5"
                ></div>

                <div
                    class="pointer-events-none absolute bottom-0 right-28 h-32 w-32 rounded-full bg-cyan-300/10 blur-2xl"
                ></div>

                <div
                    class="pointer-events-none absolute right-10 top-10 hidden opacity-15 lg:block"
                >
                    <svg
                        viewBox="0 0 240 240"
                        class="h-56 w-56"
                        fill="none"
                        stroke="white"
                        stroke-width="2"
                    >
                        <circle cx="120" cy="120" r="95"/>
                        <circle cx="120" cy="120" r="65"/>
                        <circle cx="120" cy="120" r="35"/>
                        <path d="M120 35v170M35 120h170"/>
                        <path d="M65 65l110 110M175 65 65 175"/>
                    </svg>
                </div>

                <div class="relative z-10 max-w-2xl">
                    <div
                        class="mb-4 inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-4 py-2 text-xs font-semibold tracking-wide text-blue-100"
                    >
                        <span class="h-2 w-2 rounded-full bg-cyan-300"></span>
                        CENTRO DE OPERACIONES
                    </div>

                    <div class="mb-5 flex flex-wrap gap-3">
                        <div class="rounded-xl border border-white/20 bg-white/10 px-4 py-3">
                            <p class="text-xs text-blue-100">
                                Usuario conectado
                            </p>
                            <p class="mt-1 text-sm font-bold text-white">
                                {{ nombreUsuario }}
                            </p>
                        </div>

                        <div class="rounded-xl border border-white/20 bg-white/10 px-4 py-3">
                            <p class="text-xs text-blue-100">
                                Rol asignado
                            </p>
                            <p class="mt-1 text-sm font-bold text-white">
                                {{ rolUsuario }}
                            </p>
                        </div>
                    </div>

                    <h2 class="text-3xl font-extrabold tracking-tight sm:text-4xl">
                        Bienvenido a FarmaControl
                    </h2>

                    <p class="mt-4 max-w-xl text-sm leading-7 text-blue-100 sm:text-base">
                        Gestiona medicamentos, inventario, ventas y procesos
                        asistenciales desde una plataforma centralizada,
                        organizada y fácil de utilizar.
                    </p>

                    <div class="mt-7 flex flex-wrap gap-3">
                        <Link
                            href="/medicamentos"
                            class="inline-flex items-center gap-2 rounded-xl bg-white px-5 py-3 text-sm font-bold text-blue-900 shadow-sm transition hover:bg-blue-50"
                        >
                            Ver medicamentos

                            <svg
                                viewBox="0 0 24 24"
                                class="h-4 w-4"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <path d="M5 12h14M13 6l6 6-6 6"/>
                            </svg>
                        </Link>

                        <Link
                            href="/movimientos"
                            class="inline-flex items-center gap-2 rounded-xl border border-white/25 bg-white/10 px-5 py-3 text-sm font-semibold text-white transition hover:bg-white/20"
                        >
                            Consultar inventario
                        </Link>
                    </div>
                </div>
            </section>

            <!-- INDICADORES -->
            <section class="mb-10">
                <div class="mb-5 flex flex-wrap items-end justify-between gap-3">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.2em] text-blue-700">
                            Supervisión
                        </p>

                        <h3 class="mt-2 text-2xl font-extrabold tracking-tight text-slate-900">
                            Estado del inventario
                        </h3>

                        <p class="mt-1 text-sm text-slate-500">
                            Indicadores actualizados desde los registros del sistema.
                        </p>
                    </div>

                    <Link
                        href="/lotes"
                        class="text-sm font-semibold text-blue-700 transition hover:text-blue-900"
                    >
                        Revisar lotes
                    </Link>
                </div>

                <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    <div
                        v-for="indicador in indicadores"
                        :key="indicador.nombre"
                        class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition duration-200 hover:-translate-y-1 hover:border-blue-200 hover:shadow-lg"
                    >
                        <div
                            class="absolute inset-x-0 top-0 h-1"
                            :class="{
                                'bg-amber-400': indicador.color === 'amber',
                                'bg-red-500': indicador.color === 'red',
                                'bg-blue-500': indicador.color === 'blue',
                                'bg-rose-500': indicador.color === 'rose',
                            }"
                        ></div>

                        <div class="flex items-start justify-between gap-3">
                            <p class="text-sm font-semibold text-slate-600">
                                {{ indicador.nombre }}
                            </p>

                            <div
                                class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl"
                                :class="{
                                    'bg-amber-50 text-amber-700': indicador.color === 'amber',
                                    'bg-red-50 text-red-700': indicador.color === 'red',
                                    'bg-blue-50 text-blue-700': indicador.color === 'blue',
                                    'bg-rose-50 text-rose-700': indicador.color === 'rose',
                                }"
                            >
                                <svg
                                    viewBox="0 0 24 24"
                                    class="h-6 w-6"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                >
                                    <g v-html="iconos[indicador.icono]?.join('')"></g>
                                </svg>
                            </div>
                        </div>

                        <p
                            class="mt-5 text-4xl font-extrabold tracking-tight"
                            :class="{
                                'text-amber-600': indicador.color === 'amber',
                                'text-red-600': indicador.color === 'red',
                                'text-blue-700': indicador.color === 'blue',
                                'text-rose-600': indicador.color === 'rose',
                            }"
                        >
                            {{ indicador.valor }}
                        </p>

                        <p class="mt-3 text-xs leading-5 text-slate-500">
                            {{ indicador.descripcion }}
                        </p>
                    </div>
                </div>
            </section>

            <!-- ACCESOS RÁPIDOS -->
            <section class="mb-10">
                <div class="mb-5">
                    <p class="text-xs font-bold uppercase tracking-[0.2em] text-blue-700">
                        Accesos rápidos
                    </p>

                    <h3 class="mt-2 text-2xl font-extrabold tracking-tight text-slate-900">
                        Operaciones frecuentes
                    </h3>
                </div>

                <div class="grid gap-4 md:grid-cols-3">
                    <Link
                        v-for="acceso in accesosRapidos"
                        :key="acceso.nombre"
                        :href="acceso.enlace"
                        class="group flex items-center gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-blue-200 hover:shadow-md"
                    >
                        <div
                            class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-blue-50 text-blue-800"
                        >
                            <svg
                                viewBox="0 0 24 24"
                                class="h-7 w-7"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.7"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            >
                                <g v-html="iconos[acceso.icono]?.join('')"></g>
                            </svg>
                        </div>

                        <div class="min-w-0 flex-1">
                            <p class="font-bold text-slate-900">
                                {{ acceso.nombre }}
                            </p>

                            <p class="mt-1 text-xs text-slate-500">
                                {{ acceso.categoria }}
                            </p>
                        </div>

                        <svg
                            viewBox="0 0 24 24"
                            class="h-5 w-5 text-slate-400 transition group-hover:translate-x-1 group-hover:text-blue-700"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                        >
                            <path d="M5 12h14M13 6l6 6-6 6"/>
                        </svg>
                    </Link>
                </div>
            </section>

            <!-- MÓDULOS -->
            <section class="mb-10">
                <div
                    class="mb-6 flex flex-col justify-between gap-5 md:flex-row md:items-end"
                >
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.2em] text-blue-700">
                            Plataforma
                        </p>

                        <h3 class="mt-2 text-2xl font-extrabold tracking-tight text-slate-900">
                            Módulos del sistema
                        </h3>

                        <p class="mt-2 text-sm text-slate-500">
                            {{ cantidadModulosActivos }}
                            módulos disponibles para su utilización.
                        </p>
                    </div>

                    <div class="relative w-full md:max-w-sm">
                        <svg
                            viewBox="0 0 24 24"
                            class="pointer-events-none absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-slate-400"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                        >
                            <circle cx="11" cy="11" r="7"/>
                            <path d="m16 16 5 5"/>
                        </svg>

                        <input
                            v-model="busqueda"
                            type="search"
                            placeholder="Buscar un módulo..."
                            aria-label="Buscar módulos"
                            class="w-full rounded-xl border border-slate-200 bg-white py-3 pl-11 pr-4 text-sm shadow-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                        />
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-3">
                    <div
                        v-for="modulo in modulosFiltrados"
                        :key="modulo.nombre"
                        class="group flex flex-col rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition duration-200 hover:-translate-y-1 hover:border-blue-200 hover:shadow-lg"
                    >
                        <div class="mb-5 flex items-start justify-between">
                            <div
                                class="flex h-14 w-14 items-center justify-center rounded-2xl"
                                :class="{
                                    'bg-blue-50 text-blue-700': modulo.color === 'blue',
                                    'bg-indigo-50 text-indigo-700': modulo.color === 'indigo',
                                    'bg-cyan-50 text-cyan-700': modulo.color === 'cyan',
                                    'bg-emerald-50 text-emerald-700': modulo.color === 'emerald',
                                    'bg-violet-50 text-violet-700': modulo.color === 'violet',
                                    'bg-teal-50 text-teal-700': modulo.color === 'teal',
                                    'bg-slate-100 text-slate-600': modulo.color === 'slate',
                                }"
                            >
                                <svg
                                    viewBox="0 0 24 24"
                                    class="h-7 w-7"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.7"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                >
                                    <g v-html="iconos[modulo.icono]?.join('')"></g>
                                </svg>
                            </div>

                            <span
                                v-if="modulo.disponible"
                                class="rounded-full bg-emerald-50 px-3 py-1 text-[11px] font-bold text-emerald-700"
                            >
                                Disponible
                            </span>

                            <span
                                v-else
                                class="rounded-full bg-slate-100 px-3 py-1 text-[11px] font-semibold text-slate-500"
                            >
                                Próximamente
                            </span>
                        </div>

                        <p class="text-xs font-bold uppercase tracking-wider text-blue-600">
                            {{ modulo.categoria }}
                        </p>

                        <h4 class="mt-2 text-lg font-extrabold text-slate-900">
                            {{ modulo.nombre }}
                        </h4>

                        <p class="mt-3 min-h-[60px] flex-1 text-sm leading-6 text-slate-500">
                            {{ modulo.descripcion }}
                        </p>

                        <Link
                            v-if="modulo.disponible && modulo.enlace"
                            :href="modulo.enlace"
                            class="mt-6 inline-flex items-center justify-center gap-2 rounded-xl bg-blue-800 px-5 py-3 text-sm font-bold text-white transition hover:bg-blue-900"
                        >
                            Acceder al módulo

                            <svg
                                viewBox="0 0 24 24"
                                class="h-4 w-4"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <path d="M5 12h14M13 6l6 6-6 6"/>
                            </svg>
                        </Link>

                        <div
                            v-else
                            class="mt-6 flex items-center justify-center rounded-xl border border-slate-200 bg-slate-50 px-5 py-3 text-sm font-semibold text-slate-400"
                        >
                            En desarrollo
                        </div>
                    </div>
                </div>

                <div
                    v-if="modulosFiltrados.length === 0"
                    class="rounded-2xl border border-slate-200 bg-white px-6 py-12 text-center"
                >
                    <p class="text-lg font-bold text-slate-800">
                        No se encontraron módulos
                    </p>

                    <p class="mt-2 text-sm text-slate-500">
                        Intenta buscar con otro término.
                    </p>
                </div>
            </section>

            <!-- PIE DE PÁGINA -->
            <footer
                class="flex flex-col justify-between gap-3 border-t border-slate-200 py-7 text-xs text-slate-500 sm:flex-row sm:items-center"
            >
                <p class="font-semibold text-slate-600">
                    FarmaControl · Gestión farmacéutica
                </p>

                <p>
                    Plataforma de gestión de medicamentos,
                    inventario y procesos asistenciales.
                </p>
            </footer>
        </main>

        <!-- MARCA DE AGUA -->
        <div
            class="pointer-events-none fixed bottom-5 right-5 z-50 select-none rounded-2xl border border-slate-200/70 bg-white/80 px-5 py-3 text-right shadow-sm backdrop-blur-sm"
        >
            <div class="flex items-center justify-end gap-2">
                <svg
                    viewBox="0 0 24 24"
                    class="h-5 w-5 text-blue-700/70"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2.5"
                    stroke-linecap="round"
                >
                    <path d="M12 4v16M4 12h16"/>
                </svg>

                <p class="text-xs font-extrabold tracking-wide text-slate-600">
                    FARMA<span class="text-blue-700/80">CONTROL</span>
                </p>
            </div>

            <p class="mt-1 text-[11px] font-medium text-slate-500">
                Uso académico · INACAP
            </p>

            <p class="mt-0.5 text-[10px] text-slate-400">
                Sistema de simulación · Sin validez legal
            </p>
        </div>
    </div>
</template>
