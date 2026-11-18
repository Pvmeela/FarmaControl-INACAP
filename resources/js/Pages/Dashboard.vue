<script setup>
import { Head, Link, router } from '@inertiajs/vue3';

defineProps({
    alertas: {
        type: Object,
        default: () => ({
            stock_bajo: null,
            quiebres_stock: null,
            proximos_vencer: null,
            lotes_vencidos: null,
        }),
    },
});

const modulos = [
    {
        nombre: 'Medicamentos',
        icono: '💊',
        descripcion: 'Administración del catálogo de medicamentos.',
        enlace: '/medicamentos',
        disponible: true,
    },
    {
        nombre: 'Inventario y lotes',
        icono: '📦',
        descripcion: 'Control de existencias, lotes y vencimientos.',
        enlace: '/lotes',
        disponible: true,
    },
    {
        nombre: 'Movimientos de inventario',
        icono: '🔄',
        descripcion: 'Registro de entradas, salidas y trazabilidad FEFO.',
        enlace: '/movimientos',
        disponible: true,
    },
    {
        nombre: 'Ventas',
        icono: '🧾',
        descripcion: 'Registro de ventas simuladas y descuento de inventario mediante FEFO.',
        enlace: '/ventas',
        disponible: true,
    },
    {
        nombre: 'Pacientes',
        icono: '👤',
        descripcion: 'Registro y consulta de pacientes simulados.',
        enlace: '/pacientes',
        disponible: true,
    },
    {
        nombre: 'Dispensaciones',
        icono: '🏥',
        descripcion: 'Entrega simulada de medicamentos.',
        enlace: null,
        disponible: false,
    },
    {
        nombre: 'Recetas',
        icono: '📋',
        descripcion: 'Registro, consulta y validación de vigencia de recetas simuladas.',
        enlace: '/recetas',
        disponible: true,
    },
    {
        nombre: 'Usuarios y roles',
        icono: '👥',
        descripcion: 'Administración de usuarios y permisos.',
        enlace: null,
        disponible: false,
    },
];

function cerrarSesion() {
    router.post('/logout');
}
</script>

<template>
    <Head title="Panel principal | FarmaControl" />

    <div class="min-h-screen bg-slate-100 text-slate-800">

        <!-- ENCABEZADO -->
        <header class="border-b border-slate-200 bg-white">
            <div
                class="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-4 px-6 py-5"
            >
                <div>
                    <h1 class="text-2xl font-bold text-blue-800">
                        FarmaControl
                    </h1>

                    <p class="text-sm text-slate-500">
                        Sistema Web Integrado de Gestión Farmacéutica
                    </p>
                </div>

                <button
                    type="button"
                    @click="cerrarSesion"
                    class="rounded-lg bg-red-600 px-4 py-2 text-white transition hover:bg-red-700"
                >
                    Cerrar sesión
                </button>
            </div>
        </header>

        <!-- CONTENIDO -->
        <main class="mx-auto max-w-6xl px-6 py-10">

            <h2 class="mb-2 text-3xl font-bold">
                Panel principal
            </h2>

            <p class="mb-8 text-slate-500">
                Bienvenido al sistema de gestión FarmaControl.
            </p>

            <!-- ALERTAS DE INVENTARIO -->
            <section class="mb-10">
                <div class="mb-5">
                    <h3 class="text-xl font-bold text-slate-900">
                        Alertas de inventario
                    </h3>

                    <p class="mt-1 text-sm text-slate-500">
                        Resumen del estado de las existencias
                        y los vencimientos.
                    </p>
                </div>

                <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">

                    <!-- STOCK BAJO -->
                    <div
                        class="rounded-xl border border-amber-200 bg-white p-5 shadow-sm"
                    >
                        <p class="text-sm font-medium text-slate-500">
                            Medicamentos con stock bajo
                        </p>

                        <p class="mt-3 text-3xl font-bold text-amber-700">
                            {{ alertas.stock_bajo ?? '—' }}
                        </p>

                        <p class="mt-2 text-xs text-slate-500">
                            Existencias bajo el nivel mínimo configurado.
                        </p>
                    </div>

                    <!-- QUIEBRES -->
                    <div
                        class="rounded-xl border border-red-200 bg-white p-5 shadow-sm"
                    >
                        <p class="text-sm font-medium text-slate-500">
                            Quiebres de stock
                        </p>

                        <p class="mt-3 text-3xl font-bold text-red-700">
                            {{ alertas.quiebres_stock ?? '—' }}
                        </p>

                        <p class="mt-2 text-xs text-slate-500">
                            Medicamentos sin unidades utilizables.
                        </p>
                    </div>

                    <!-- PRÓXIMOS A VENCER -->
                    <div
                        class="rounded-xl border border-orange-200 bg-white p-5 shadow-sm"
                    >
                        <p class="text-sm font-medium text-slate-500">
                            Lotes próximos a vencer
                        </p>

                        <p class="mt-3 text-3xl font-bold text-orange-700">
                            {{ alertas.proximos_vencer ?? '—' }}
                        </p>

                        <p class="mt-2 text-xs text-slate-500">
                            Vencimiento dentro de los próximos 30 días.
                        </p>
                    </div>

                    <!-- VENCIDOS -->
                    <div
                        class="rounded-xl border border-rose-200 bg-white p-5 shadow-sm"
                    >
                        <p class="text-sm font-medium text-slate-500">
                            Lotes vencidos
                        </p>

                        <p class="mt-3 text-3xl font-bold text-rose-700">
                            {{ alertas.lotes_vencidos ?? '—' }}
                        </p>

                        <p class="mt-2 text-xs text-slate-500">
                            Lotes que no pueden utilizarse.
                        </p>
                    </div>

                </div>
            </section>

            <!-- MÓDULOS -->
            <section>
                <h3 class="mb-5 text-xl font-bold text-slate-900">
                    Módulos del sistema
                </h3>

                <div
                    class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3"
                >
                    <div
                        v-for="modulo in modulos"
                        :key="modulo.nombre"
                        class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm"
                    >
                        <div class="mb-3 text-3xl">
                            {{ modulo.icono }}
                        </div>

                        <h4 class="text-lg font-bold">
                            {{ modulo.nombre }}
                        </h4>

                        <p class="mt-2 text-sm text-slate-500">
                            {{ modulo.descripcion }}
                        </p>

                        <Link
                            v-if="modulo.disponible && modulo.enlace"
                            :href="modulo.enlace"
                            class="mt-4 inline-flex rounded-lg bg-blue-800 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-900"
                        >
                            Acceder al módulo
                        </Link>

                        <p
                            v-else
                            class="mt-4 text-sm font-medium text-slate-400"
                        >
                            Próximamente
                        </p>
                    </div>
                </div>
            </section>
        </main>

        <!-- MARCA DE AGUA ACADÉMICA -->
        <div
            class="pointer-events-none fixed bottom-3 right-3 z-50 rounded-lg border border-slate-200 bg-white/95 px-3 py-2 text-right text-[10px] leading-4 text-slate-600 shadow-sm"
        >
            <p class="font-semibold">
                Uso académico · INACAP
            </p>

            <p>
                Sistema de simulación · Sin validez legal
            </p>
        </div>
    </div>
</template>
