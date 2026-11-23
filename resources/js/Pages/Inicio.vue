<script setup>
import { Head, Link } from '@inertiajs/vue3'
</script>

<template>
    <Head title="Inicio | FarmaControl" />

    <div class="min-h-screen bg-slate-50 text-slate-800">
        <!-- Encabezado -->
        <header class="border-b border-slate-200 bg-white px-8 py-5">
            <h1 class="text-2xl font-bold text-teal-800">
                FarmaControl
            </h1>

            <p class="text-sm text-slate-500">
                Sistema Web Integrado de Gestión Farmacéutica
            </p>
        </header>

        <!-- Contenido principal -->
        <main class="mx-auto max-w-5xl px-6 py-16">
            <div class="mb-12 text-center">
                <h2 class="text-3xl font-bold text-slate-900">
                    Selecciona tu entorno de trabajo
                </h2>

                <p class="mt-3 text-slate-500">
                    Plataforma de simulación académica para la
                    gestión farmacéutica.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-8 md:grid-cols-2">
                <!-- Farmacia privada -->
                <section
                    class="rounded-2xl border border-slate-200 bg-white p-8 shadow-sm"
                >
                    <div class="mb-5 text-4xl">🏪</div>

                    <h3 class="text-xl font-bold text-teal-800">
                        Farmacia Privada
                    </h3>

                    <p class="mt-3 text-slate-600">
                        Gestión comercial de medicamentos,
                        inventario, movimientos y ventas simuladas.
                    </p>

                    <Link
                        href="/seleccionar-entorno/privada"
                        class="mt-6 inline-block rounded-xl bg-teal-700 px-6 py-3 font-semibold text-white transition hover:bg-teal-800"
                    >
                        Ingresar a Farmacia Privada
                    </Link>
                </section>

                <!-- Farmacia CESFAM -->
                <section
                    class="rounded-2xl border border-slate-200 bg-white p-8 shadow-sm"
                >
                    <div class="mb-5 text-4xl">🏥</div>

                    <h3 class="text-xl font-bold text-emerald-700">
                        Farmacia CESFAM
                    </h3>

                    <p class="mt-3 text-slate-600">
                        Gestión asistencial de medicamentos,
                        pacientes, recetas y dispensaciones simuladas.
                    </p>

                    <Link
                        href="/seleccionar-entorno/cesfam"
                        class="mt-6 inline-block rounded-xl bg-emerald-700 px-6 py-3 font-semibold text-white transition hover:bg-emerald-800"
                    >
                        Ingresar a Farmacia CESFAM
                    </Link>
                </section>
            </div>

            <!-- Información académica -->
            <p class="mt-12 text-center text-sm text-slate-500">
                Proyecto académico INACAP · Sistema de simulación
            </p>
        </main>

        <!-- Marca de agua -->
        <div
            class="fixed bottom-4 right-4 z-50 rounded-lg border border-teal-200 bg-white/95 px-4 py-2 text-xs text-teal-800 shadow-sm"
        >
            <p class="font-bold">
                USO ACADÉMICO INACAP
            </p>
            <p>
                SIMULACIÓN · SIN VALIDEZ TRIBUTARIA
            </p>
        </div>
    </div>
</template>
