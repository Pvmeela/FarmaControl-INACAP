
<script setup>
import { Head, useForm } from '@inertiajs/vue3';

const form = useForm({
    email: '',
    password: '',
});

const iniciarSesion = () => {
    form.post('/login', {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Iniciar sesión | FarmaControl" />

    <div class="min-h-screen bg-slate-100 flex items-center justify-center p-6">

        <div class="w-full max-w-md bg-white rounded-2xl shadow-lg p-8">

            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-blue-800">
                    FarmaControl
                </h1>

                <p class="text-slate-500 mt-2">
                    Sistema de Gestión Farmacéutica
                </p>

                <h2 class="text-xl font-semibold mt-6">
                    Iniciar sesión
                </h2>
            </div>

            <form @submit.prevent="iniciarSesion">

                <div class="mb-5">
                    <label class="block text-sm font-medium mb-2">
                        Correo electrónico
                    </label>

                    <input
                        v-model="form.email"
                        type="email"
                        autocomplete="email"
                        required
                        class="w-full border border-slate-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="correo@ejemplo.cl"
                    />

                    <p v-if="form.errors.email"
                       class="text-red-600 text-sm mt-1">
                        {{ form.errors.email }}
                    </p>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium mb-2">
                        Contraseña
                    </label>

                    <input
                        v-model="form.password"
                        type="password"
                        autocomplete="current-password"
                        required
                        class="w-full border border-slate-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Ingresa tu contraseña"
                    />

                    <p v-if="form.errors.password"
                       class="text-red-600 text-sm mt-1">
                        {{ form.errors.password }}
                    </p>
                </div>

                <button
                    type="submit"
                    :disabled="form.processing"
                    class="w-full bg-blue-700 text-white py-3 rounded-lg font-semibold hover:bg-blue-800 disabled:opacity-50"
                >
                    {{ form.processing ? 'Ingresando...' : 'Ingresar' }}
                </button>

            </form>

            <p class="text-center text-xs text-slate-500 mt-8">
                Uso académico · INACAP
                Sistema de simulación · Sin validez legal
            </p>

        </div>
    </div>
</template>
