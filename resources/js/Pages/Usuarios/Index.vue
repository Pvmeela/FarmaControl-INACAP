<script setup>
import { computed, ref } from 'vue'
import { Head, Link, useForm } from '@inertiajs/vue3'

const props = defineProps({
    usuarios: {
        type: Array,
        default: () => [],
    },
    roles: {
        type: Array,
        default: () => [],
    },
})

const busqueda = ref('')
const mostrarFormulario = ref(false)

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    rol_id: '',
})

const usuariosFiltrados = computed(() => {
    const texto = busqueda.value.trim().toLowerCase()

    if (!texto) {
        return props.usuarios
    }

    return props.usuarios.filter(usuario =>
        [
            usuario.name,
            usuario.email,
            usuario.rol,
        ]
            .join(' ')
            .toLowerCase()
            .includes(texto)
    )
})

function guardarUsuario() {
    form.post('/usuarios', {
        preserveScroll: true,
        onSuccess: () => {
            form.reset()
            mostrarFormulario.value = false
        },
    })
}

function cancelar() {
    form.reset()
    form.clearErrors()
    mostrarFormulario.value = false
}
</script>

<template>
    <Head title="Usuarios y roles | FarmaControl" />

    <div class="min-h-screen bg-[#f3f6fb] text-slate-800">

        <!-- ENCABEZADO -->
        <header class="border-b border-slate-200 bg-white shadow-sm">
            <div
                class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-4 px-5 py-5 sm:px-8"
            >
                <div>
                    <h1 class="text-2xl font-extrabold text-slate-900">
                        Farma<span class="text-blue-700">Control</span>
                    </h1>

                    <p class="mt-1 text-xs text-slate-500">
                        Administración del sistema
                    </p>
                </div>

                <Link
                    href="/dashboard"
                    class="rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50"
                >
                    Volver al Dashboard
                </Link>
            </div>
        </header>

        <main class="mx-auto max-w-7xl px-5 py-8 sm:px-8">

            <!-- PRESENTACIÓN -->
            <section
                class="mb-8 rounded-3xl bg-gradient-to-br from-[#102b65] via-[#17418f] to-[#2563b9] p-8 text-white shadow-lg"
            >
                <p
                    class="text-xs font-bold uppercase tracking-widest text-blue-200"
                >
                    Administración
                </p>

                <h2 class="mt-3 text-3xl font-extrabold">
                    Usuarios y roles
                </h2>

                <p class="mt-3 max-w-2xl text-sm leading-7 text-blue-100">
                    Consulta las cuentas registradas, crea nuevos
                    usuarios y asigna los roles disponibles
                    en FarmaControl.
                </p>

                <div class="mt-6 flex flex-wrap gap-3">
                    <div
                        class="rounded-xl border border-white/20 bg-white/10 px-5 py-3"
                    >
                        <p class="text-xs text-blue-100">
                            Usuarios registrados
                        </p>

                        <p class="mt-1 text-2xl font-bold">
                            {{ usuarios.length }}
                        </p>
                    </div>

                    <div
                        class="rounded-xl border border-white/20 bg-white/10 px-5 py-3"
                    >
                        <p class="text-xs text-blue-100">
                            Roles disponibles
                        </p>

                        <p class="mt-1 text-2xl font-bold">
                            {{ roles.length }}
                        </p>
                    </div>
                </div>
            </section>

            <!-- USUARIOS -->
            <section
                class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"
            >
                <div
                    class="mb-6 flex flex-wrap items-center justify-between gap-4"
                >
                    <div>
                        <h3 class="text-xl font-extrabold text-slate-900">
                            Cuentas registradas
                        </h3>

                        <p class="mt-1 text-sm text-slate-500">
                            Listado de usuarios y sus roles asignados.
                        </p>
                    </div>

                    <button
                        type="button"
                        @click="mostrarFormulario = !mostrarFormulario"
                        class="rounded-xl bg-blue-800 px-5 py-3 text-sm font-bold text-white hover:bg-blue-900"
                    >
                        {{
                            mostrarFormulario
                                ? 'Cerrar formulario'
                                : '+ Nuevo usuario'
                        }}
                    </button>
                </div>

                <!-- FORMULARIO -->
                <form
                    v-if="mostrarFormulario"
                    @submit.prevent="guardarUsuario"
                    class="mb-8 rounded-2xl border border-blue-100 bg-blue-50/50 p-6"
                >
                    <h4 class="mb-5 text-lg font-bold text-slate-900">
                        Registrar nuevo usuario
                    </h4>

                    <div class="grid gap-5 md:grid-cols-2">

                        <div>
                            <label
                                for="nombre"
                                class="mb-2 block text-sm font-semibold"
                            >
                                Nombre completo
                            </label>

                            <input
                                id="nombre"
                                v-model="form.name"
                                type="text"
                                required
                                maxlength="255"
                                class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 outline-none focus:border-blue-500"
                                placeholder="Nombre del usuario"
                            />

                            <p
                                v-if="form.errors.name"
                                class="mt-1 text-xs text-red-600"
                            >
                                {{ form.errors.name }}
                            </p>
                        </div>

                        <div>
                            <label
                                for="email"
                                class="mb-2 block text-sm font-semibold"
                            >
                                Correo electrónico
                            </label>

                            <input
                                id="email"
                                v-model="form.email"
                                type="email"
                                required
                                maxlength="255"
                                class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 outline-none focus:border-blue-500"
                                placeholder="usuario@ejemplo.cl"
                            />

                            <p
                                v-if="form.errors.email"
                                class="mt-1 text-xs text-red-600"
                            >
                                {{ form.errors.email }}
                            </p>
                        </div>

                        <div>
                            <label
                                for="rol"
                                class="mb-2 block text-sm font-semibold"
                            >
                                Rol del usuario
                            </label>

                            <select
                                id="rol"
                                v-model="form.rol_id"
                                required
                                class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 outline-none focus:border-blue-500"
                            >
                                <option value="" disabled>
                                    Seleccionar rol
                                </option>

                                <option
                                    v-for="rol in roles"
                                    :key="rol.id"
                                    :value="rol.id"
                                >
                                    {{ rol.nombre }}
                                </option>
                            </select>

                            <p
                                v-if="form.errors.rol_id"
                                class="mt-1 text-xs text-red-600"
                            >
                                {{ form.errors.rol_id }}
                            </p>
                        </div>

                        <div>
                            <label
                                for="password"
                                class="mb-2 block text-sm font-semibold"
                            >
                                Contraseña
                            </label>

                            <input
                                id="password"
                                v-model="form.password"
                                type="password"
                                required
                                minlength="8"
                                autocomplete="new-password"
                                class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 outline-none focus:border-blue-500"
                                placeholder="Mínimo 8 caracteres"
                            />

                            <p
                                v-if="form.errors.password"
                                class="mt-1 text-xs text-red-600"
                            >
                                {{ form.errors.password }}
                            </p>
                        </div>

                        <div>
                            <label
                                for="confirmar"
                                class="mb-2 block text-sm font-semibold"
                            >
                                Confirmar contraseña
                            </label>

                            <input
                                id="confirmar"
                                v-model="form.password_confirmation"
                                type="password"
                                required
                                minlength="8"
                                autocomplete="new-password"
                                class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 outline-none focus:border-blue-500"
                                placeholder="Repetir contraseña"
                            />
                        </div>
                    </div>

                    <div class="mt-6 flex flex-wrap gap-3">
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="rounded-xl bg-blue-800 px-6 py-3 text-sm font-bold text-white hover:bg-blue-900 disabled:opacity-50"
                        >
                            {{
                                form.processing
                                    ? 'Guardando...'
                                    : 'Guardar usuario'
                            }}
                        </button>

                        <button
                            type="button"
                            @click="cancelar"
                            class="rounded-xl border border-slate-200 bg-white px-6 py-3 text-sm font-semibold"
                        >
                            Cancelar
                        </button>
                    </div>
                </form>

                <!-- BUSCADOR -->
                <div class="mb-5">
                    <input
                        v-model="busqueda"
                        type="search"
                        placeholder="Buscar por nombre, correo o rol..."
                        aria-label="Buscar usuarios"
                        class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm outline-none focus:border-blue-500"
                    />
                </div>

                <!-- TABLA -->
                <div
                    class="overflow-x-auto rounded-xl border border-slate-200"
                >
                    <table class="w-full min-w-[650px] text-left text-sm">
                        <thead class="bg-slate-50 text-slate-600">
                            <tr>
                                <th class="px-5 py-4 font-bold">
                                    Nombre
                                </th>

                                <th class="px-5 py-4 font-bold">
                                    Correo
                                </th>

                                <th class="px-5 py-4 font-bold">
                                    Rol
                                </th>

                                <th class="px-5 py-4 font-bold">
                                    Registro
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-100">
                            <tr
                                v-for="usuario in usuariosFiltrados"
                                :key="usuario.id"
                                class="hover:bg-blue-50/50"
                            >
                                <td
                                    class="px-5 py-4 font-semibold text-slate-900"
                                >
                                    {{ usuario.name }}
                                </td>

                                <td class="px-5 py-4 text-slate-600">
                                    {{ usuario.email }}
                                </td>

                                <td class="px-5 py-4">
                                    <span
                                        class="inline-flex rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-800"
                                    >
                                        {{ usuario.rol }}
                                    </span>
                                </td>

                                <td class="px-5 py-4 text-slate-500">
                                    {{ usuario.created_at }}
                                </td>
                            </tr>

                            <tr v-if="usuariosFiltrados.length === 0">
                                <td
                                    colspan="4"
                                    class="px-5 py-10 text-center text-slate-500"
                                >
                                    No se encontraron usuarios.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <footer
                class="mt-8 border-t border-slate-200 py-6 text-xs text-slate-500"
            >
                FarmaControl · Plataforma académica INACAP
            </footer>
        </main>

        <!-- MARCA DE AGUA ACADÉMICA -->
        <div
            class="pointer-events-none fixed bottom-5 right-5 z-50 select-none rounded-2xl border border-slate-200/70 bg-white/80 px-5 py-3 text-right shadow-sm backdrop-blur-sm"
        >
            <p class="text-xs font-extrabold text-slate-600">
                FARMA<span class="text-blue-700">CONTROL</span>
            </p>

            <p class="mt-1 text-[11px] text-slate-500">
                Uso académico · INACAP
            </p>

            <p class="text-[10px] text-slate-400">
                Sistema de simulación · Sin validez legal
            </p>
        </div>
    </div>
</template>
