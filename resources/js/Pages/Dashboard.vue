<script setup>
import { Head, router } from '@inertiajs/vue3';

const cerrarSesion = () => {
    router.post('/logout');
};
</script>

<template>
    <Head title="Panel principal | FarmaControl" />

    <div class="min-h-screen bg-slate-100 text-slate-800">

        <header class="bg-white border-b border-slate-200">
            <div class="max-w-6xl mx-auto px-6 py-5 flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-blue-800">
                        FarmaControl
                    </h1>
                    <p class="text-sm text-slate-500">
                        Sistema Web Integrado de Gestión Farmacéutica
                    </p>
                </div>

                <button
                    @click="cerrarSesion"
                    class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg"
                >
                    Cerrar sesión
                </button>
            </div>
        </header>

        <main class="max-w-6xl mx-auto px-6 py-10">

            <h2 class="text-3xl font-bold mb-2">
                Panel principal
            </h2>

            <p class="text-slate-500 mb-8">
                Bienvenido al sistema de gestión FarmaControl.
            </p>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                <div class="bg-white rounded-xl p-6 shadow-sm border">
                    <div class="text-3xl mb-3">💊</div>
                    <h3 class="text-lg font-bold">Medicamentos</h3>
                    <p class="text-slate-500 text-sm mt-2">
                        Administración del catálogo de medicamentos.
                    </p>
                    <p class="text-blue-700 text-sm mt-4">
                        Próximamente
                    </p>
                </div>

                <div class="bg-white rounded-xl p-6 shadow-sm border">
                    <div class="text-3xl mb-3">📦</div>
                    <h3 class="text-lg font-bold">Inventario</h3>
                    <p class="text-slate-500 text-sm mt-2">
                        Control de existencias, lotes y vencimientos.
                    </p>
                    <p class="text-blue-700 text-sm mt-4">
                        Próximamente
                    </p>
                </div>

                <div class="bg-white rounded-xl p-6 shadow-sm border">
                    <div class="text-3xl mb-3">🧾</div>
                    <h3 class="text-lg font-bold">Ventas</h3>
                    <p class="text-slate-500 text-sm mt-2">
                        Operaciones de farmacia privada.
                    </p>
                    <p class="text-blue-700 text-sm mt-4">
                        Próximamente
                    </p>
                </div>

                <div class="bg-white rounded-xl p-6 shadow-sm border">
                    <div class="text-3xl mb-3">🏥</div>
                    <h3 class="text-lg font-bold">Dispensaciones CESFAM</h3>
                    <p class="text-slate-500 text-sm mt-2">
                        Entrega simulada de medicamentos a pacientes.
                    </p>
                    <p class="text-blue-700 text-sm mt-4">
                        Próximamente
                    </p>
                </div>

                <div class="bg-white rounded-xl p-6 shadow-sm border">
                    <div class="text-3xl mb-3">📋</div>
                    <h3 class="text-lg font-bold">Recetas</h3>
                    <p class="text-slate-500 text-sm mt-2">
                        Registro y consulta de recetas farmacéuticas.
                    </p>
                    <p class="text-blue-700 text-sm mt-4">
                        Próximamente
                    </p>
                </div>

                <div class="bg-white rounded-xl p-6 shadow-sm border">
                    <div class="text-3xl mb-3">👥</div>
                    <h3 class="text-lg font-bold">Usuarios y roles</h3>
                    <p class="text-slate-500 text-sm mt-2">
                        Administración de usuarios y permisos.
                    </p>
                    <p class="text-blue-700 text-sm mt-4">
                        Próximamente
                    </p>
                </div>

            </div>
        </main>

        <div class="fixed bottom-4 right-4 bg-white/95 border border-slate-200 rounded-lg shadow-sm px-4 py-2 text-xs text-slate-600">
            <p class="font-semibold">Uso académico · INACAP</p>
            <p>Sistema de simulación · Sin validez legal</p>
        </div>
    </div>
</template>
