
<script setup>
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Inicio | FarmaControl" />

    <div class="min-h-screen bg-slate-50 text-slate-800">
        <header class="bg-white border-b border-slate-200 px-8 py-5">
            <h1 class="text-2xl font-bold text-blue-800">
                FarmaControl
            </h1>
            <p class="text-sm text-slate-500">
                Sistema Web Integrado de Gestión Farmacéutica
            </p>
        </header>

        <main class="max-w-5xl mx-auto px-6 py-16">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold">
                    Selecciona tu entorno de trabajo
                </h2>
                <p class="mt-3 text-slate-500">
                    Plataforma de simulación para la formación farmacéutica.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <section class="bg-white rounded-2xl border border-slate-200 p-8 shadow-sm">
                    <div class="text-4xl mb-5">🏪</div>
                    <h3 class="text-xl font-bold text-blue-800">
                        Farmacia privada
                    </h3>
                    <p class="text-slate-600 mt-3">
                        Gestión de medicamentos, inventario,
                        ventas, recetas y compras.
                    </p>
                    <p class="mt-6 text-sm font-medium text-blue-700">
                        Módulo en desarrollo
                    </p>
                </section>

                <section class="bg-white rounded-2xl border border-slate-200 p-8 shadow-sm">
                    <div class="text-4xl mb-5">🏥</div>
                    <h3 class="text-xl font-bold text-emerald-700">
                        Farmacia CESFAM
                    </h3>
                    <p class="text-slate-600 mt-3">
                        Gestión de pacientes, recetas,
                        dispensaciones y programas de salud.
                    </p>
                    <p class="mt-6 text-sm font-medium text-emerald-700">
                        Módulo en desarrollo
                    </p>
                </section>
            </div>
        </main>

        <div class="fixed bottom-4 right-4 rounded-lg border border-slate-200 bg-white/95 px-4 py-2 text-xs text-slate-600 shadow-sm">
            <p class="font-semibold">Uso académico · INACAP</p>
            <p>Sistema de simulación · Sin validez legal</p>
        </div>
    </div>
</template>
