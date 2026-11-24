<script setup>
import { computed, ref, nextTick } from 'vue'
import { Head, useForm, usePage, router } from '@inertiajs/vue3'
import FarmaLayout from '../../Layouts/FarmaLayout.vue'

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

const page = usePage()

const busqueda = ref('')
const filtroRol = ref('')
const filtroEstado = ref('')
const mostrarFormulario = ref(false)
const mostrarContrasena = ref(false)
const usuarioEnEdicion = ref(null)
const formularioRef = ref(null)
const mensajeExito = ref('')
const mensajeError = ref('')
const cambiandoEstadoId = ref(null)

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    rol_id: '',
})

/*
|--------------------------------------------------------------------------
| Funciones auxiliares
|--------------------------------------------------------------------------
*/

function normalizar(valor) {
    return String(valor ?? '')
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .toLowerCase()
        .trim()
}

function nombreRol(usuario) {
    const rol = typeof usuario.rol === 'object'
        ? usuario.rol?.nombre
        : usuario.rol

    if (!rol || normalizar(rol).startsWith('sin rol')) {
        return 'Sin rol asignado'
    }

    return rol
}

function etiquetaRol(rol) {
    switch (normalizar(rol)) {
        case 'administrador':
            return 'Administrador'

        case 'quimico farmaceutico':
            return 'Químico Farmacéutico'

        case 'tecnico en farmacia':
            return 'Técnico en Farmacia'

        default:
            return rol || 'Sin rol asignado'
    }
}

function claseRol(rol) {
    switch (normalizar(rol)) {
        case 'administrador':
            return 'rol-administrador'

        case 'quimico farmaceutico':
            return 'rol-quimico'

        case 'tecnico en farmacia':
            return 'rol-tecnico'

        default:
            return 'rol-default'
    }
}

function estaActivo(usuario) {
    return usuario.activo === true ||
        usuario.activo === 1 ||
        usuario.activo === '1'
}

function tieneRolValido(usuario) {
    return props.roles.some(rol =>
        String(rol.id) === String(usuario.rol_id)
    )
}

function iniciales(nombre) {
    return String(nombre || 'Usuario')
        .trim()
        .split(/\s+/)
        .slice(0, 2)
        .map(palabra => palabra.charAt(0).toUpperCase())
        .join('')
}

function formatearFecha(fecha) {
    if (!fecha) {
        return 'No registrada'
    }

    const texto = String(fecha)

    const coincidencia = texto.match(
        /^(\d{4})-(\d{2})-(\d{2})/
    )

    if (coincidencia) {
        return `${coincidencia[3]}/${coincidencia[2]}/${coincidencia[1]}`
    }

    return texto
}

const usuarioActual = computed(() =>
    page.props.auth?.user || null
)

function esMiCuenta(usuario) {
    return usuarioActual.value !== null &&
        String(usuarioActual.value.id) === String(usuario.id)
}

const editandoMiCuenta = computed(() =>
    usuarioEnEdicion.value !== null &&
    esMiCuenta(usuarioEnEdicion.value)
)

const modoEdicion = computed(() =>
    usuarioEnEdicion.value !== null
)

const ocupado = computed(() =>
    form.processing || cambiandoEstadoId.value !== null
)

/*
|--------------------------------------------------------------------------
| Indicadores
|--------------------------------------------------------------------------
*/

const totalUsuarios = computed(() =>
    props.usuarios.length
)

const totalActivos = computed(() =>
    props.usuarios.filter(estaActivo).length
)

const totalInactivos = computed(() =>
    props.usuarios.filter(usuario =>
        !estaActivo(usuario)
    ).length
)

function contarRol(rol) {
    return props.usuarios.filter(usuario =>
        normalizar(nombreRol(usuario)) === normalizar(rol)
    ).length
}

const totalAdministradores = computed(() =>
    contarRol('Administrador')
)

const totalQuimicos = computed(() =>
    contarRol('Quimico Farmaceutico')
)

const totalTecnicos = computed(() =>
    contarRol('Tecnico en Farmacia')
)

const indicadores = computed(() => [
    {
        titulo: 'Usuarios registrados',
        cantidad: totalUsuarios.value,
        descripcion: 'Total de cuentas del sistema',
        icono: 'usuarios',
        color: 'principal',
        rol: '',
        estado: '',
    },
    {
        titulo: 'Usuarios activos',
        cantidad: totalActivos.value,
        descripcion: 'Cuentas habilitadas',
        icono: 'usuarioCheck',
        color: 'verde',
        rol: '',
        estado: 'activo',
    },
    {
        titulo: 'Usuarios inactivos',
        cantidad: totalInactivos.value,
        descripcion: 'Cuentas deshabilitadas',
        icono: 'usuarioX',
        color: 'rojo',
        rol: '',
        estado: 'inactivo',
    },
    {
        titulo: 'Administradores',
        cantidad: totalAdministradores.value,
        descripcion: 'Administración del sistema',
        icono: 'escudo',
        color: 'morado',
        rol: 'Administrador',
        estado: '',
    },
    {
        titulo: 'Químicos farmacéuticos',
        cantidad: totalQuimicos.value,
        descripcion: 'Supervisión farmacéutica',
        icono: 'capsula',
        color: 'turquesa',
        rol: 'Quimico Farmaceutico',
        estado: '',
    },
    {
        titulo: 'Técnicos en farmacia',
        cantidad: totalTecnicos.value,
        descripcion: 'Operaciones farmacéuticas',
        icono: 'maletin',
        color: 'naranjo',
        rol: 'Tecnico en Farmacia',
        estado: '',
    },
])

/*
|--------------------------------------------------------------------------
| Buscador y filtros
|--------------------------------------------------------------------------
*/

const usuariosFiltrados = computed(() => {
    const texto = normalizar(busqueda.value)

    return props.usuarios.filter(usuario => {
        const rol = nombreRol(usuario)

        const contenido = normalizar([
            usuario.id,
            usuario.name,
            usuario.email,
            rol,
            estaActivo(usuario) ? 'Activo' : 'Inactivo',
        ].join(' '))

        const coincideBusqueda =
            !texto || contenido.includes(texto)

        const coincideRol =
            !filtroRol.value ||
            normalizar(rol) === normalizar(filtroRol.value)

        const coincideEstado =
            !filtroEstado.value ||
            (
                filtroEstado.value === 'activo' &&
                estaActivo(usuario)
            ) ||
            (
                filtroEstado.value === 'inactivo' &&
                !estaActivo(usuario)
            )

        return coincideBusqueda &&
            coincideRol &&
            coincideEstado
    })
})

const hayFiltros = computed(() =>
    Boolean(
        busqueda.value ||
        filtroRol.value ||
        filtroEstado.value
    )
)

function aplicarFiltro(indicador) {
    busqueda.value = ''
    filtroRol.value = indicador.rol
    filtroEstado.value = indicador.estado
}

function indicadorSeleccionado(indicador) {
    return !busqueda.value &&
        filtroRol.value === indicador.rol &&
        filtroEstado.value === indicador.estado
}

function limpiarFiltros() {
    busqueda.value = ''
    filtroRol.value = ''
    filtroEstado.value = ''
}

/*
|--------------------------------------------------------------------------
| Validaciones
|--------------------------------------------------------------------------
*/

const contrasenasCoinciden = computed(() =>
    form.password === form.password_confirmation
)

const rolValido = computed(() =>
    props.roles.some(rol =>
        String(rol.id) === String(form.rol_id)
    )
)

const contrasenaValida = computed(() => {
    if (
        modoEdicion.value &&
        !form.password &&
        !form.password_confirmation
    ) {
        return true
    }

    return form.password.length >= 8 &&
        contrasenasCoinciden.value
})

const puedeGuardar = computed(() =>
    form.name.trim().length > 0 &&
    form.email.trim().length > 0 &&
    contrasenaValida.value &&
    rolValido.value &&
    !ocupado.value
)

const rolSeleccionado = computed(() =>
    props.roles.find(rol =>
        String(rol.id) === String(form.rol_id)
    )
)

const mensajeServidor = computed(() =>
    page.props.flash?.success || ''
)

const ocultarMensajeServidor = ref(false)

const mensajeVisible = computed(() =>
    mensajeExito.value ||
    (
        !ocultarMensajeServidor.value
            ? mensajeServidor.value
            : ''
    )
)

function limpiarMensajes() {
    mensajeExito.value = ''
    mensajeError.value = ''
    ocultarMensajeServidor.value = true
}

function obtenerError(errores) {
    const error = Object.values(errores || {})[0]

    if (Array.isArray(error)) {
        return error[0]
    }

    return error ||
        'No fue posible completar la operación.'
}

/*
|--------------------------------------------------------------------------
| Gestión del formulario
|--------------------------------------------------------------------------
*/

function confirmarCambios() {
    if (ocupado.value) {
        return false
    }

    if (mostrarFormulario.value && form.isDirty) {
        return window.confirm(
            'Hay cambios sin guardar. ¿Deseas descartarlos?'
        )
    }

    return true
}

function prepararFormulario(datos = {}) {
    const valores = {
        name: datos.name || '',
        email: datos.email || '',
        password: '',
        password_confirmation: '',
        rol_id: datos.rol_id ?? '',
    }

    form.defaults(valores)
    form.reset()
    form.clearErrors()

    mostrarContrasena.value = false
}

async function desplazarFormulario() {
    await nextTick()

    formularioRef.value?.scrollIntoView({
        behavior: 'smooth',
        block: 'start',
    })
}

async function abrirFormulario() {
    if (!confirmarCambios()) {
        return
    }

    limpiarMensajes()

    usuarioEnEdicion.value = null

    prepararFormulario()

    mostrarFormulario.value = true

    await desplazarFormulario()
}

async function editarUsuario(usuario) {
    if (!confirmarCambios()) {
        return
    }

    limpiarMensajes()

    usuarioEnEdicion.value = usuario

    prepararFormulario(usuario)

    mostrarFormulario.value = true

    await desplazarFormulario()
}

function cerrarFormulario() {
    prepararFormulario()

    mostrarFormulario.value = false
    usuarioEnEdicion.value = null
}

function cancelar() {
    if (!confirmarCambios()) {
        return
    }

    cerrarFormulario()
}

function alternarFormulario() {
    if (mostrarFormulario.value) {
        cancelar()
    } else {
        abrirFormulario()
    }
}

function guardarUsuario() {
    if (!puedeGuardar.value) {
        return
    }

    limpiarMensajes()

    const editando = modoEdicion.value

    const opciones = {
        preserveScroll: true,

        onSuccess: () => {
            cerrarFormulario()

            mensajeExito.value = editando
                ? 'Usuario actualizado correctamente.'
                : 'Usuario registrado correctamente.'
        },

        onError: () => {
            mensajeError.value =
                'Revisa los campos del formulario.'
        },

        onFinish: () => {
            mostrarContrasena.value = false
        },
    }

    if (editando) {
        form.put(
            `/usuarios/${usuarioEnEdicion.value.id}`,
            opciones
        )
    } else {
        form.post('/usuarios', opciones)
    }
}

/*
|--------------------------------------------------------------------------
| Activación y desactivación
|--------------------------------------------------------------------------
*/

function cambiarEstado(usuario) {
    if (ocupado.value || mostrarFormulario.value) {
        return
    }

    limpiarMensajes()

    const nuevoEstado = !estaActivo(usuario)

    if (esMiCuenta(usuario) && !nuevoEstado) {
        mensajeError.value =
            'No puedes desactivar tu propia cuenta.'

        return
    }

    if (nuevoEstado && !tieneRolValido(usuario)) {
        mensajeError.value =
            'Debes asignar un rol válido antes de activar esta cuenta.'

        return
    }

    const confirmar = window.confirm(
        nuevoEstado
            ? `¿Deseas activar la cuenta de ${usuario.name}?`
            : `¿Deseas desactivar la cuenta de ${usuario.name}? Se bloqueará su acceso, pero se conservarán sus registros históricos.`
    )

    if (!confirmar) {
        return
    }

    cambiandoEstadoId.value = usuario.id

    router.patch(
        `/usuarios/${usuario.id}/estado`,
        {
            activo: nuevoEstado,
        },
        {
            preserveScroll: true,

            onSuccess: () => {
                mensajeExito.value = nuevoEstado
                    ? 'Usuario activado correctamente.'
                    : 'Usuario desactivado correctamente.'
            },

            onError: errores => {
                mensajeError.value =
                    obtenerError(errores)
            },

            onFinish: () => {
                cambiandoEstadoId.value = null
            },
        }
    )
}

/*
|--------------------------------------------------------------------------
| Iconos
|--------------------------------------------------------------------------
*/

const iconos = {
    usuarios: `
        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
        <circle cx="9" cy="7" r="4"/>
        <path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>
    `,

    usuarioCheck: `
        <circle cx="9" cy="7" r="4"/>
        <path d="M2 21v-2a4 4 0 0 1 4-4h6a4 4 0 0 1 4 4v2"/>
        <path d="m16 11 2 2 4-4"/>
    `,

    usuarioX: `
        <circle cx="9" cy="7" r="4"/>
        <path d="M2 21v-2a4 4 0 0 1 4-4h6a4 4 0 0 1 4 4v2"/>
        <path d="m17 9 5 5m0-5-5 5"/>
    `,

    escudo: `
        <path d="M12 22s8-4 8-11V5l-8-3-8 3v6c0 7 8 11 8 11z"/>
        <path d="m9 12 2 2 4-4"/>
    `,

    capsula: `
        <path d="M5 19a5 5 0 0 1 0-7L12 5a5 5 0 0 1 7 7l-7 7a5 5 0 0 1-7 0Z"/>
        <path d="m8.5 8.5 7 7"/>
    `,

    maletin: `
        <rect x="3" y="7" width="18" height="14" rx="2"/>
        <path d="M8 7V4h8v3M12 11v6M9 14h6"/>
    `,

    agregar: `
        <circle cx="9" cy="7" r="4"/>
        <path d="M2 21v-2a4 4 0 0 1 4-4h6a4 4 0 0 1 4 4v2M19 8v6M16 11h6"/>
    `,

    buscar: `
        <circle cx="11" cy="11" r="7"/>
        <path d="m20 20-4-4"/>
    `,

    cerrar: `
        <path d="m6 6 12 12M18 6 6 18"/>
    `,

    ojo: `
        <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7z"/>
        <circle cx="12" cy="12" r="3"/>
    `,

    ojoCerrado: `
        <path d="m3 3 18 18M10 10a3 3 0 0 0 4 4M6 6C3 8 2 12 2 12s3.5 7 10 7c2 0 4-.6 5-1.5M10 5h2c6.5 0 10 7 10 7s-.8 1.5-2.5 3.5"/>
    `,

    candado: `
        <rect x="4" y="10" width="16" height="12" rx="2"/>
        <path d="M8 10V6a4 4 0 0 1 8 0v4M12 15v3"/>
    `,

    check: `
        <circle cx="12" cy="12" r="9"/>
        <path d="m8 12 3 3 5-6"/>
    `,

    correo: `
        <rect x="3" y="5" width="18" height="14" rx="2"/>
        <path d="m3 7 9 6 9-6"/>
    `,

    calendario: `
        <rect x="3" y="5" width="18" height="16" rx="2"/>
        <path d="M16 3v4M8 3v4M3 10h18"/>
    `,

    informacion: `
        <circle cx="12" cy="12" r="9"/>
        <path d="M12 11v5M12 7h.01"/>
    `,

    editar: `
        <path d="m16 3 5 5-12 12-6 1 1-6L16 3z"/>
        <path d="m13 6 5 5"/>
    `,

    encendido: `
        <path d="M12 2v10M6 5a9 9 0 1 0 12 0"/>
    `,
}
</script>

<template>

    <Head title="Usuarios y roles | FarmaControl" />

    <FarmaLayout
        titulo="Administración de usuarios"
        subtitulo="Gestiona las cuentas, los roles y los permisos de acceso de FarmaControl."
    >

        <!-- ACCIONES DEL ENCABEZADO -->

        <template #acciones>

            <button
                type="button"
                class="boton-principal"
                :disabled="ocupado"
                @click="alternarFormulario"
            >
                <svg
                    viewBox="0 0 24 24"
                    aria-hidden="true"
                    v-html="mostrarFormulario
                        ? iconos.cerrar
                        : iconos.agregar"
                />

                {{
                    mostrarFormulario
                        ? 'Cerrar formulario'
                        : 'Nuevo usuario'
                }}
            </button>

        </template>

        <div class="usuarios-page">

            <!-- INDICADORES -->

            <section class="indicadores-grid">

                <button
                    v-for="indicador in indicadores"
                    :key="indicador.titulo"
                    type="button"
                    class="indicador-card"
                    :class="{
                        'indicador-activo':
                            indicadorSeleccionado(indicador)
                    }"
                    :aria-pressed="
                        indicadorSeleccionado(indicador)
                    "
                    @click="aplicarFiltro(indicador)"
                >

                    <div class="indicador-superior">

                        <div
                            class="indicador-icono"
                            :class="`icono-${indicador.color}`"
                        >
                            <svg
                                viewBox="0 0 24 24"
                                aria-hidden="true"
                                v-html="iconos[indicador.icono]"
                            />
                        </div>

                        <span class="indicador-etiqueta">
                            Usuarios
                        </span>

                    </div>

                    <p class="indicador-titulo">
                        {{ indicador.titulo }}
                    </p>

                    <p class="indicador-numero">
                        {{ indicador.cantidad }}
                    </p>

                    <p class="indicador-descripcion">
                        {{ indicador.descripcion }}
                    </p>

                </button>

            </section>

            <!-- BANNER DE SEGURIDAD -->

            <section class="banner-seguridad">

                <div class="banner-contenido">

                    <div class="banner-icono">
                        <svg
                            viewBox="0 0 24 24"
                            aria-hidden="true"
                            v-html="iconos.escudo"
                        />
                    </div>

                    <div>

                        <span class="banner-etiqueta">
                            SEGURIDAD Y ADMINISTRACIÓN
                        </span>

                        <h2>
                            Control de acceso al sistema
                        </h2>

                        <p>
                            Gestiona los perfiles autorizados,
                            administra las cuentas y protege
                            el acceso a los módulos de
                            FarmaControl.
                        </p>

                    </div>

                </div>

                <div class="banner-roles">

                    <strong>
                        {{ roles.length }}
                    </strong>

                    <span>
                        Roles disponibles
                    </span>

                </div>

            </section>

            <!-- MENSAJES -->

            <div
                v-if="mensajeVisible"
                class="mensaje mensaje-exito"
                role="status"
            >

                <svg
                    viewBox="0 0 24 24"
                    aria-hidden="true"
                    v-html="iconos.check"
                />

                <span>
                    {{ mensajeVisible }}
                </span>

                <button
                    type="button"
                    class="cerrar-mensaje"
                    aria-label="Cerrar mensaje"
                    @click="
                        mensajeExito = '';
                        ocultarMensajeServidor = true
                    "
                >
                    <svg
                        viewBox="0 0 24 24"
                        v-html="iconos.cerrar"
                    />
                </button>

            </div>

            <div
                v-if="mensajeError"
                class="mensaje mensaje-error"
                role="alert"
            >

                <svg
                    viewBox="0 0 24 24"
                    aria-hidden="true"
                    v-html="iconos.informacion"
                />

                <span>
                    {{ mensajeError }}
                </span>

                <button
                    type="button"
                    class="cerrar-mensaje"
                    aria-label="Cerrar mensaje de error"
                    @click="mensajeError = ''"
                >
                    <svg
                        viewBox="0 0 24 24"
                        v-html="iconos.cerrar"
                    />
                </button>

            </div>

            <!-- FORMULARIO -->

            <Transition name="formulario">

                <section
                    v-if="mostrarFormulario"
                    ref="formularioRef"
                    class="panel formulario-panel"
                >

                    <div class="panel-encabezado">

                        <div class="panel-titulo-grupo">

                            <div class="panel-icono">
                                <svg
                                    viewBox="0 0 24 24"
                                    aria-hidden="true"
                                    v-html="modoEdicion
                                        ? iconos.editar
                                        : iconos.agregar"
                                />
                            </div>

                            <div>

                                <h2>
                                    {{
                                        modoEdicion
                                            ? 'Editar usuario'
                                            : 'Registrar nuevo usuario'
                                    }}
                                </h2>

                                <p>
                                    {{
                                        modoEdicion
                                            ? 'Actualiza la información y las credenciales de la cuenta.'
                                            : 'Completa la información de la nueva cuenta.'
                                    }}
                                </p>

                            </div>

                        </div>

                        <button
                            type="button"
                            class="boton-cerrar"
                            aria-label="Cerrar formulario"
                            :disabled="ocupado"
                            @click="cancelar"
                        >
                            <svg
                                viewBox="0 0 24 24"
                                v-html="iconos.cerrar"
                            />
                        </button>

                    </div>

                    <form @submit.prevent="guardarUsuario">

                        <div class="formulario-contenido">

                            <!-- INFORMACIÓN PERSONAL -->

                            <div class="subtitulo-formulario">

                                <span class="numero-seccion">
                                    01
                                </span>

                                <div>

                                    <h3>
                                        Información personal
                                    </h3>

                                    <p>
                                        Datos de identificación
                                        de la cuenta.
                                    </p>

                                </div>

                            </div>

                            <div class="campos-grid">

                                <div class="campo">

                                    <label for="nombre">
                                        Nombre completo
                                        <span>*</span>
                                    </label>

                                    <input
                                        id="nombre"
                                        v-model="form.name"
                                        type="text"
                                        required
                                        maxlength="255"
                                        autocomplete="name"
                                        placeholder="Nombre del usuario"
                                        :disabled="ocupado"
                                        :class="{
                                            'campo-error':
                                                form.errors.name
                                        }"
                                    />

                                    <p
                                        v-if="form.errors.name"
                                        class="error-texto"
                                    >
                                        {{ form.errors.name }}
                                    </p>

                                </div>

                                <div class="campo">

                                    <label for="email">
                                        Correo electrónico
                                        <span>*</span>
                                    </label>

                                    <input
                                        id="email"
                                        v-model="form.email"
                                        type="email"
                                        required
                                        maxlength="255"
                                        autocomplete="email"
                                        placeholder="usuario@ejemplo.cl"
                                        :disabled="ocupado"
                                        :class="{
                                            'campo-error':
                                                form.errors.email
                                        }"
                                    />

                                    <p
                                        v-if="form.errors.email"
                                        class="error-texto"
                                    >
                                        {{ form.errors.email }}
                                    </p>

                                </div>

                                <div class="campo campo-completo">

                                    <label for="rol">
                                        Rol del usuario
                                        <span>*</span>
                                    </label>

                                    <select
                                        id="rol"
                                        v-model="form.rol_id"
                                        required
                                        :disabled="
                                            ocupado ||
                                            editandoMiCuenta
                                        "
                                        :class="{
                                            'campo-error':
                                                form.errors.rol_id
                                        }"
                                    >

                                        <option
                                            value=""
                                            disabled
                                        >
                                            Seleccionar rol
                                        </option>

                                        <option
                                            v-for="rol in roles"
                                            :key="rol.id"
                                            :value="rol.id"
                                        >
                                            {{
                                                etiquetaRol(
                                                    rol.nombre
                                                )
                                            }}
                                        </option>

                                    </select>

                                    <p
                                        v-if="form.errors.rol_id"
                                        class="error-texto"
                                    >
                                        {{ form.errors.rol_id }}
                                    </p>

                                    <div
                                        v-if="editandoMiCuenta"
                                        class="aviso-proteccion"
                                    >

                                        <svg
                                            viewBox="0 0 24 24"
                                            v-html="iconos.candado"
                                        />

                                        No puedes modificar
                                        tu propio rol.

                                    </div>

                                    <div
                                        v-else-if="rolSeleccionado"
                                        class="rol-seleccionado"
                                    >

                                        <svg
                                            viewBox="0 0 24 24"
                                            v-html="iconos.informacion"
                                        />

                                        <span>
                                            Rol asignado:

                                            <strong>
                                                {{
                                                    etiquetaRol(
                                                        rolSeleccionado.nombre
                                                    )
                                                }}
                                            </strong>
                                        </span>

                                    </div>

                                </div>

                            </div>

                            <div class="separador"></div>

                            <!-- CREDENCIALES -->

                            <div class="subtitulo-formulario">

                                <span class="numero-seccion">
                                    02
                                </span>

                                <div>

                                    <h3>
                                        Credenciales de acceso
                                    </h3>

                                    <p>
                                        {{
                                            modoEdicion
                                                ? 'Deja ambos campos vacíos para conservar la contraseña actual.'
                                                : 'Configura una contraseña segura para la cuenta.'
                                        }}
                                    </p>

                                </div>

                            </div>

                            <div class="campos-grid">

                                <div class="campo">

                                    <label for="password">

                                        {{
                                            modoEdicion
                                                ? 'Nueva contraseña'
                                                : 'Contraseña'
                                        }}

                                        <span v-if="!modoEdicion">
                                            *
                                        </span>

                                    </label>

                                    <div class="password-contenedor">

                                        <input
                                            id="password"
                                            v-model="form.password"
                                            :type="mostrarContrasena
                                                ? 'text'
                                                : 'password'"
                                            :required="!modoEdicion"
                                            minlength="8"
                                            autocomplete="new-password"
                                            placeholder="Mínimo 8 caracteres"
                                            :disabled="ocupado"
                                            :class="{
                                                'campo-error':
                                                    form.errors.password
                                            }"
                                        />

                                        <button
                                            type="button"
                                            class="boton-password"
                                            :aria-label="
                                                mostrarContrasena
                                                    ? 'Ocultar contraseñas'
                                                    : 'Mostrar contraseñas'
                                            "
                                            @click="
                                                mostrarContrasena =
                                                    !mostrarContrasena
                                            "
                                        >

                                            <svg
                                                viewBox="0 0 24 24"
                                                v-html="
                                                    mostrarContrasena
                                                        ? iconos.ojoCerrado
                                                        : iconos.ojo
                                                "
                                            />

                                        </button>

                                    </div>

                                    <p
                                        v-if="form.errors.password"
                                        class="error-texto"
                                    >
                                        {{ form.errors.password }}
                                    </p>

                                    <p
                                        v-if="form.password"
                                        class="requisito-password"
                                        :class="{
                                            'requisito-cumplido':
                                                form.password.length >= 8
                                        }"
                                    >
                                        {{
                                            form.password.length >= 8
                                                ? '✓ Mínimo de caracteres cumplido'
                                                : 'Debe contener al menos 8 caracteres'
                                        }}
                                    </p>

                                </div>

                                <div class="campo">

                                    <label for="confirmar">
                                        Confirmar contraseña

                                        <span
                                            v-if="
                                                !modoEdicion ||
                                                form.password
                                            "
                                        >
                                            *
                                        </span>
                                    </label>

                                    <input
                                        id="confirmar"
                                        v-model="form.password_confirmation"
                                        :type="mostrarContrasena
                                            ? 'text'
                                            : 'password'"
                                        :required="
                                            !modoEdicion ||
                                            Boolean(form.password)
                                        "
                                        minlength="8"
                                        autocomplete="new-password"
                                        placeholder="Repetir contraseña"
                                        :disabled="ocupado"
                                        :class="{
                                            'campo-error':
                                                form.errors.password_confirmation ||
                                                (
                                                    form.password_confirmation &&
                                                    !contrasenasCoinciden
                                                )
                                        }"
                                    />

                                    <p
                                        v-if="form.errors.password_confirmation"
                                        class="error-texto"
                                    >
                                        {{
                                            form.errors.password_confirmation
                                        }}
                                    </p>

                                    <p
                                        v-else-if="
                                            form.password_confirmation &&
                                            !contrasenasCoinciden
                                        "
                                        class="error-texto"
                                    >
                                        Las contraseñas no coinciden.
                                    </p>

                                    <p
                                        v-else-if="
                                            form.password_confirmation &&
                                            contrasenasCoinciden &&
                                            form.password.length >= 8
                                        "
                                        class="confirmacion-password"
                                    >
                                        ✓ Las contraseñas coinciden.
                                    </p>

                                </div>

                            </div>

                            <!-- ESTADO DURANTE EDICIÓN -->

                            <div
                                v-if="modoEdicion"
                                class="estado-edicion"
                            >

                                <div>

                                    <strong>
                                        Estado actual de la cuenta
                                    </strong>

                                    <p>
                                        La edición no modifica
                                        automáticamente el estado
                                        de acceso.
                                    </p>

                                </div>

                                <span
                                    class="estado-badge"
                                    :class="
                                        estaActivo(usuarioEnEdicion)
                                            ? 'estado-activo'
                                            : 'estado-inactivo'
                                    "
                                >

                                    <span class="estado-punto"></span>

                                    {{
                                        estaActivo(usuarioEnEdicion)
                                            ? 'Activo'
                                            : 'Inactivo'
                                    }}

                                </span>

                            </div>

                            <!-- AVISO ACADÉMICO -->

                            <div class="aviso-academico">

                                <svg
                                    viewBox="0 0 24 24"
                                    v-html="iconos.candado"
                                />

                                <div>

                                    <strong>
                                        Uso exclusivamente académico
                                    </strong>

                                    <p>
                                        Utiliza cuentas y datos
                                        ficticios para las actividades
                                        educativas de FarmaControl.
                                    </p>

                                </div>

                            </div>

                        </div>

                        <!-- ACCIONES -->

                        <div class="formulario-acciones">

                            <p>
                                Los campos marcados con *
                                son obligatorios.
                            </p>

                            <div class="acciones-grupo">

                                <button
                                    type="button"
                                    class="boton-secundario"
                                    :disabled="ocupado"
                                    @click="cancelar"
                                >
                                    Cancelar
                                </button>

                                <button
                                    type="submit"
                                    class="boton-principal"
                                    :disabled="!puedeGuardar"
                                >

                                    <span
                                        v-if="form.processing"
                                        class="spinner"
                                    ></span>

                                    <svg
                                        v-else
                                        viewBox="0 0 24 24"
                                        v-html="iconos.check"
                                    />

                                    {{
                                        form.processing
                                            ? 'Guardando...'
                                            : modoEdicion
                                                ? 'Guardar cambios'
                                                : 'Guardar usuario'
                                    }}

                                </button>

                            </div>

                        </div>

                    </form>

                </section>

            </Transition>

            <!-- LISTADO -->

            <section class="panel listado-panel">

                <div class="panel-encabezado">

                    <div class="panel-titulo-grupo">

                        <div class="panel-icono">
                            <svg
                                viewBox="0 0 24 24"
                                v-html="iconos.usuarios"
                            />
                        </div>

                        <div>

                            <h2>
                                Usuarios registrados
                            </h2>

                            <p>
                                Consulta y administra las
                                cuentas del sistema.
                            </p>

                        </div>

                    </div>

                    <span class="contador-registros">
                        {{ usuarios.length }} registros
                    </span>

                </div>

                <!-- FILTROS -->

                <div class="filtros-contenedor">

                    <div class="filtro-busqueda">

                        <label for="buscar-usuarios">
                            Buscar usuarios
                        </label>

                        <div class="buscador">

                            <svg
                                viewBox="0 0 24 24"
                                v-html="iconos.buscar"
                            />

                            <input
                                id="buscar-usuarios"
                                v-model="busqueda"
                                type="search"
                                placeholder="Nombre, correo o rol..."
                            />

                        </div>

                    </div>

                    <div class="filtro-rol">

                        <label for="filtro-rol">
                            Rol
                        </label>

                        <select
                            id="filtro-rol"
                            v-model="filtroRol"
                        >

                            <option value="">
                                Todos los roles
                            </option>

                            <option
                                v-for="rol in roles"
                                :key="rol.id"
                                :value="rol.nombre"
                            >
                                {{ etiquetaRol(rol.nombre) }}
                            </option>

                            <option value="Sin rol asignado">
                                Sin rol asignado
                            </option>

                        </select>

                    </div>

                    <div class="filtro-estado">

                        <label for="filtro-estado">
                            Estado
                        </label>

                        <select
                            id="filtro-estado"
                            v-model="filtroEstado"
                        >

                            <option value="">
                                Todos
                            </option>

                            <option value="activo">
                                Activos
                            </option>

                            <option value="inactivo">
                                Inactivos
                            </option>

                        </select>

                    </div>

                    <button
                        v-if="hayFiltros"
                        type="button"
                        class="boton-limpiar"
                        @click="limpiarFiltros"
                    >
                        Limpiar filtros
                    </button>

                </div>

                <!-- TABLA -->

                <div class="tabla-contenedor">

                    <table class="tabla-usuarios">

                        <thead>

                            <tr>
                                <th>Usuario</th>
                                <th>Correo electrónico</th>
                                <th>Rol asignado</th>
                                <th>Estado</th>
                                <th>Fecha de registro</th>
                                <th>Acciones</th>
                            </tr>

                        </thead>

                        <tbody>

                            <tr
                                v-for="usuario in usuariosFiltrados"
                                :key="usuario.id"
                                :class="{
                                    'fila-inactiva':
                                        !estaActivo(usuario)
                                }"
                            >

                                <!-- USUARIO -->

                                <td>

                                    <div class="usuario-celda">

                                        <div
                                            class="usuario-avatar"
                                            :class="
                                                claseRol(
                                                    nombreRol(usuario)
                                                )
                                            "
                                        >
                                            {{
                                                iniciales(
                                                    usuario.name
                                                )
                                            }}
                                        </div>

                                        <div class="usuario-informacion">

                                            <strong>
                                                {{ usuario.name }}
                                            </strong>

                                            <span
                                                v-if="esMiCuenta(usuario)"
                                                class="usuario-actual"
                                            >
                                                Tu cuenta
                                            </span>

                                            <span v-else>
                                                Usuario #{{ usuario.id }}
                                            </span>

                                        </div>

                                    </div>

                                </td>

                                <!-- CORREO -->

                                <td>

                                    <div class="correo-celda">

                                        <svg
                                            viewBox="0 0 24 24"
                                            v-html="iconos.correo"
                                        />

                                        <span>
                                            {{ usuario.email }}
                                        </span>

                                    </div>

                                </td>

                                <!-- ROL -->

                                <td>

                                    <span
                                        class="rol-badge"
                                        :class="
                                            claseRol(
                                                nombreRol(usuario)
                                            )
                                        "
                                    >

                                        <span class="rol-punto"></span>

                                        {{
                                            etiquetaRol(
                                                nombreRol(usuario)
                                            )
                                        }}

                                    </span>

                                </td>

                                <!-- ESTADO -->

                                <td>

                                    <span
                                        class="estado-badge"
                                        :class="
                                            estaActivo(usuario)
                                                ? 'estado-activo'
                                                : 'estado-inactivo'
                                        "
                                    >

                                        <span class="estado-punto"></span>

                                        {{
                                            estaActivo(usuario)
                                                ? 'Activo'
                                                : 'Inactivo'
                                        }}

                                    </span>

                                </td>

                                <!-- FECHA -->

                                <td>

                                    <div class="fecha-celda">

                                        <svg
                                            viewBox="0 0 24 24"
                                            v-html="iconos.calendario"
                                        />

                                        <span>
                                            {{
                                                formatearFecha(
                                                    usuario.created_at
                                                )
                                            }}
                                        </span>

                                    </div>

                                </td>

                                <!-- ACCIONES -->

                                <td>

                                    <div class="acciones-tabla">

                                        <button
                                            type="button"
                                            class="boton-tabla boton-editar"
                                            :disabled="ocupado"
                                            @click="editarUsuario(usuario)"
                                        >

                                            <svg
                                                viewBox="0 0 24 24"
                                                v-html="iconos.editar"
                                            />

                                            Editar

                                        </button>

                                        <button
                                            v-if="!esMiCuenta(usuario)"
                                            type="button"
                                            class="boton-tabla"
                                            :class="
                                                estaActivo(usuario)
                                                    ? 'boton-desactivar'
                                                    : 'boton-activar'
                                            "
                                            :disabled="
                                                ocupado ||
                                                mostrarFormulario ||
                                                (
                                                    !estaActivo(usuario) &&
                                                    !tieneRolValido(usuario)
                                                )
                                            "
                                            :title="
                                                !estaActivo(usuario) &&
                                                !tieneRolValido(usuario)
                                                    ? 'Asigna un rol válido antes de activar'
                                                    : estaActivo(usuario)
                                                        ? 'Desactivar cuenta'
                                                        : 'Activar cuenta'
                                            "
                                            @click="cambiarEstado(usuario)"
                                        >

                                            <svg
                                                viewBox="0 0 24 24"
                                                v-html="iconos.encendido"
                                            />

                                            {{
                                                cambiandoEstadoId === usuario.id
                                                    ? 'Procesando...'
                                                    : estaActivo(usuario)
                                                        ? 'Desactivar'
                                                        : 'Activar'
                                            }}

                                        </button>

                                        <span
                                            v-if="esMiCuenta(usuario)"
                                            class="cuenta-protegida"
                                        >

                                            <svg
                                                viewBox="0 0 24 24"
                                                v-html="iconos.candado"
                                            />

                                            Cuenta propia

                                        </span>

                                        <span
                                            v-else-if="
                                                !estaActivo(usuario) &&
                                                !tieneRolValido(usuario)
                                            "
                                            class="sin-rol-aviso"
                                        >
                                            Requiere asignar rol
                                        </span>

                                    </div>

                                </td>

                            </tr>

                            <!-- ESTADO VACÍO -->

                            <tr v-if="usuariosFiltrados.length === 0">

                                <td
                                    colspan="6"
                                    class="tabla-vacia"
                                >

                                    <div class="vacio-icono">

                                        <svg
                                            viewBox="0 0 24 24"
                                            v-html="iconos.usuarios"
                                        />

                                    </div>

                                    <h3>
                                        No se encontraron usuarios
                                    </h3>

                                    <p>
                                        {{
                                            hayFiltros
                                                ? 'Prueba con otros términos de búsqueda.'
                                                : 'Todavía no hay usuarios registrados.'
                                        }}
                                    </p>

                                    <button
                                        v-if="hayFiltros"
                                        type="button"
                                        class="boton-secundario"
                                        @click="limpiarFiltros"
                                    >
                                        Limpiar filtros
                                    </button>

                                </td>

                            </tr>

                        </tbody>

                    </table>

                </div>

                <!-- PIE DE TABLA -->

                <div class="tabla-footer">

                    <span>

                        Mostrando

                        <strong>
                            {{ usuariosFiltrados.length }}
                        </strong>

                        de

                        <strong>
                            {{ usuarios.length }}
                        </strong>

                        usuarios

                    </span>

                    <span class="tabla-footer-seguridad">

                        <svg
                            viewBox="0 0 24 24"
                            v-html="iconos.candado"
                        />

                        Administración de acceso restringido

                    </span>

                </div>

            </section>

            <!-- NOTA DE SEGURIDAD -->

            <div class="nota-seguridad">

                <svg
                    viewBox="0 0 24 24"
                    v-html="iconos.informacion"
                />

                <div>

                    <strong>
                        Seguridad y trazabilidad
                    </strong>

                    <p>
                        La administración de usuarios está
                        reservada al perfil Administrador.
                        Las cuentas desactivadas no pueden
                        acceder al sistema, pero sus registros
                        históricos se conservan.
                    </p>

                </div>

            </div>

        </div>

    </FarmaLayout>

</template>

<style scoped>

/* =========================================================
   CONFIGURACIÓN GENERAL
   ========================================================= */

.usuarios-page {
    --primary: var(--fc-primary, #172554);
    --accent: var(--fc-accent, #1d4ed8);
    --hover: var(--fc-hover, #1e40af);
    --light: var(--fc-light, #eff6ff);
    --border: var(--fc-border, #dbeafe);
    --background: var(--fc-background, #f8fafc);

    display: flex;
    flex-direction: column;
    gap: 23px;
    color: #334155;
    min-width: 0;
}

.usuarios-page * {
    box-sizing: border-box;
}

button,
input,
select {
    font-family: inherit;
}

button {
    cursor: pointer;
}

button:disabled {
    cursor: not-allowed;
    opacity: 0.5;
}

input:disabled,
select:disabled {
    background: #f1f5f9;
    cursor: not-allowed;
}

button:focus-visible,
input:focus-visible,
select:focus-visible {
    outline: 2px solid var(--fc-accent, #1d4ed8);
    outline-offset: 3px;
}

svg {
    width: 20px;
    height: 20px;
    fill: none;
    stroke: currentColor;
    stroke-width: 1.8;
    stroke-linecap: round;
    stroke-linejoin: round;
    flex-shrink: 0;
}

/* =========================================================
   BOTONES
   ========================================================= */

.boton-principal {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 9px;
    padding: 12px 19px;
    border: 1px solid transparent;
    border-radius: 10px;
    background: var(--fc-accent, #1d4ed8);
    color: #ffffff;
    font-size: 13px;
    font-weight: 650;
    white-space: nowrap;
    transition: background 0.2s, transform 0.2s;
}

.boton-principal:hover:not(:disabled) {
    background: var(--fc-hover, #1e40af);
    transform: translateY(-1px);
}

.boton-principal svg {
    width: 18px;
    height: 18px;
}

.boton-secundario {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 11px 18px;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    background: #ffffff;
    color: #475569;
    font-size: 13px;
    font-weight: 600;
}

.boton-secundario:hover:not(:disabled) {
    background: var(--light);
    border-color: var(--border);
    color: var(--primary);
}

/* =========================================================
   INDICADORES
   ========================================================= */

.indicadores-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 16px;
}

.indicador-card {
    min-width: 0;
    padding: 19px;
    border: 1px solid #e7ebf0;
    border-radius: 15px;
    background: #ffffff;
    text-align: left;
    box-shadow: 0 3px 15px rgba(15, 23, 42, 0.035);
    transition:
        transform 0.2s,
        border-color 0.2s,
        box-shadow 0.2s;
}

.indicador-card:hover {
    transform: translateY(-3px);
    border-color: var(--accent);
    box-shadow: 0 8px 22px rgba(15, 23, 42, 0.07);
}

.indicador-activo {
    border-color: var(--accent);
    box-shadow: 0 0 0 1px var(--accent);
}

.indicador-superior {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    margin-bottom: 14px;
}

.indicador-icono {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 43px;
    height: 43px;
    border-radius: 12px;
}

.indicador-icono svg {
    width: 23px;
    height: 23px;
}

.icono-principal {
    background: var(--light);
    color: var(--accent);
}

.icono-verde {
    background: #ecfdf5;
    color: #047857;
}

.icono-rojo {
    background: #fef2f2;
    color: #dc2626;
}

.icono-morado {
    background: #f3e8ff;
    color: #7e22ce;
}

.icono-turquesa {
    background: #f0fdfa;
    color: #0f766e;
}

.icono-naranjo {
    background: #fff7ed;
    color: #c2410c;
}

.indicador-etiqueta {
    padding: 4px 8px;
    border-radius: 6px;
    background: #f8fafc;
    color: #64748b;
    font-size: 10px;
    font-weight: 600;
}

.indicador-titulo {
    margin: 0;
    color: #64748b;
    font-size: 12px;
    font-weight: 600;
}

.indicador-numero {
    margin: 8px 0;
    color: var(--primary);
    font-size: 32px;
    font-weight: 750;
    line-height: 1;
    letter-spacing: -1px;
}

.indicador-descripcion {
    margin: 0;
    color: #64748b;
    font-size: 11px;
    line-height: 1.5;
}

/* =========================================================
   BANNER DE SEGURIDAD
   ========================================================= */

.banner-seguridad {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 25px;
    overflow: hidden;
    padding: 26px;
    border-radius: 17px;
    background: linear-gradient(
        115deg,
        var(--primary),
        var(--accent)
    );
    color: #ffffff;
    box-shadow: 0 6px 20px rgba(15, 23, 42, 0.08);
}

.banner-seguridad::before {
    position: absolute;
    top: -100px;
    right: -40px;
    width: 260px;
    height: 260px;
    border: 1px solid rgba(255, 255, 255, 0.14);
    border-radius: 50%;
    content: '';
    pointer-events: none;
}

.banner-seguridad::after {
    position: absolute;
    right: 80px;
    bottom: -130px;
    width: 240px;
    height: 240px;
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 50%;
    content: '';
    pointer-events: none;
}

.banner-contenido {
    position: relative;
    z-index: 1;
    display: flex;
    align-items: center;
    gap: 20px;
}

.banner-icono {
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    width: 64px;
    height: 64px;
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 17px;
    background: rgba(255, 255, 255, 0.12);
}

.banner-icono svg {
    width: 33px;
    height: 33px;
}

.banner-etiqueta {
    font-size: 10px;
    font-weight: 650;
    letter-spacing: 1.4px;
    opacity: 0.8;
}

.banner-contenido h2 {
    margin: 6px 0 8px;
    font-size: 21px;
    font-weight: 700;
    letter-spacing: -0.4px;
}

.banner-contenido p {
    max-width: 630px;
    margin: 0;
    color: rgba(255, 255, 255, 0.85);
    font-size: 12px;
    line-height: 1.8;
}

.banner-roles {
    position: relative;
    z-index: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    flex-shrink: 0;
    min-width: 145px;
    gap: 6px;
    padding: 15px 20px;
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 12px;
    background: rgba(255, 255, 255, 0.1);
    font-size: 11px;
}

.banner-roles strong {
    font-size: 29px;
    font-weight: 750;
}

/* =========================================================
   MENSAJES
   ========================================================= */

.mensaje {
    display: flex;
    align-items: center;
    gap: 11px;
    padding: 15px 18px;
    border: 1px solid;
    border-radius: 12px;
    font-size: 13px;
    font-weight: 550;
}

.mensaje > span {
    flex: 1;
}

.mensaje-exito {
    border-color: #bbf7d0;
    background: #f0fdf4;
    color: #166534;
}

.mensaje-error {
    border-color: #fecaca;
    background: #fef2f2;
    color: #b91c1c;
}

.cerrar-mensaje {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 4px;
    border: none;
    background: transparent;
    color: inherit;
}

.cerrar-mensaje svg {
    width: 17px;
    height: 17px;
}

/* =========================================================
   PANELES
   ========================================================= */

.panel {
    min-width: 0;
    overflow: hidden;
    border: 1px solid #e7ebf0;
    border-radius: 16px;
    background: #ffffff;
    box-shadow: 0 3px 18px rgba(15, 23, 42, 0.035);
}

.panel-encabezado {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 15px;
    padding: 23px 25px;
    border-bottom: 1px solid #edf0f4;
}

.panel-titulo-grupo {
    display: flex;
    align-items: center;
    gap: 13px;
}

.panel-icono {
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    width: 44px;
    height: 44px;
    border-radius: 11px;
    background: var(--light);
    color: var(--accent);
}

.panel-icono svg {
    width: 23px;
    height: 23px;
}

.panel-encabezado h2 {
    margin: 0 0 4px;
    color: var(--primary);
    font-size: 17px;
    font-weight: 700;
}

.panel-encabezado p {
    margin: 0;
    color: #64748b;
    font-size: 12px;
    line-height: 1.5;
}

.contador-registros {
    flex-shrink: 0;
    padding: 7px 11px;
    border-radius: 7px;
    background: var(--light);
    color: var(--accent);
    font-size: 11px;
    font-weight: 650;
}

.boton-cerrar {
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    width: 34px;
    height: 34px;
    border: 1px solid #e2e8f0;
    border-radius: 9px;
    background: #ffffff;
    color: #64748b;
}

.boton-cerrar:hover {
    background: #f1f5f9;
}

/* =========================================================
   FORMULARIO
   ========================================================= */

.formulario-panel {
    scroll-margin-top: 100px;
}

.formulario-contenido {
    padding: 25px;
}

.subtitulo-formulario {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 23px;
}

.numero-seccion {
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    width: 34px;
    height: 34px;
    border-radius: 9px;
    background: var(--light);
    color: var(--accent);
    font-size: 12px;
    font-weight: 750;
}

.subtitulo-formulario h3 {
    margin: 0 0 4px;
    color: var(--primary);
    font-size: 14px;
    font-weight: 650;
}

.subtitulo-formulario p {
    margin: 0;
    color: #64748b;
    font-size: 11px;
    line-height: 1.5;
}

.campos-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 22px;
}

.campo {
    display: flex;
    flex-direction: column;
    min-width: 0;
    gap: 8px;
}

.campo-completo {
    grid-column: 1 / -1;
}

.campo label,
.filtros-contenedor label {
    display: block;
    color: #475569;
    font-size: 12px;
    font-weight: 650;
}

.campo label span {
    color: #dc2626;
}

.campo input,
.campo select,
.filtros-contenedor select,
.buscador input {
    width: 100%;
    min-height: 44px;
    padding: 11px 13px;
    border: 1px solid #dfe5ed;
    border-radius: 9px;
    background: #ffffff;
    color: #334155;
    font-size: 13px;
    outline: none;
    transition:
        border-color 0.2s,
        box-shadow 0.2s;
}

.campo input:disabled,
.campo select:disabled {
    background: #f1f5f9;
}

.campo input::placeholder,
.buscador input::placeholder {
    color: #94a3b8;
    font-size: 12px;
}

.campo input:focus,
.campo select:focus,
.filtros-contenedor select:focus,
.buscador input:focus {
    border-color: var(--accent);
    box-shadow: 0 0 0 3px var(--light);
}

.campo input.campo-error,
.campo select.campo-error {
    border-color: #ef4444;
}

.error-texto {
    margin: 0;
    color: #dc2626;
    font-size: 11px;
    line-height: 1.5;
}

.rol-seleccionado,
.aviso-proteccion {
    display: flex;
    align-items: center;
    gap: 9px;
    padding: 11px 13px;
    border: 1px solid var(--border);
    border-radius: 8px;
    background: var(--light);
    color: var(--primary);
    font-size: 12px;
}

.aviso-proteccion {
    border-color: #fde68a;
    background: #fffbeb;
    color: #92400e;
}

.rol-seleccionado svg,
.aviso-proteccion svg {
    width: 17px;
    height: 17px;
}

.separador {
    height: 1px;
    margin: 28px 0;
    background: #edf0f4;
}

/* =========================================================
   CONTRASEÑAS
   ========================================================= */

.password-contenedor {
    position: relative;
}

.password-contenedor input {
    padding-right: 48px;
}

.boton-password {
    position: absolute;
    top: 0;
    right: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 44px;
    height: 100%;
    border: none;
    background: transparent;
    color: #64748b;
}

.boton-password:hover {
    color: var(--accent);
}

.requisito-password {
    margin: 0;
    color: #64748b;
    font-size: 11px;
}

.requisito-cumplido,
.confirmacion-password {
    color: #15803d;
}

.confirmacion-password {
    margin: 0;
    font-size: 11px;
}

/* =========================================================
   ESTADO DURANTE EDICIÓN
   ========================================================= */

.estado-edicion {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 15px;
    margin-top: 25px;
    padding: 16px;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    background: #f8fafc;
}

.estado-edicion strong {
    color: #334155;
    font-size: 12px;
}

.estado-edicion p {
    margin: 5px 0 0;
    color: #64748b;
    font-size: 11px;
    line-height: 1.6;
}

/* =========================================================
   AVISO ACADÉMICO
   ========================================================= */

.aviso-academico {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    margin-top: 25px;
    padding: 15px;
    border: 1px solid var(--border);
    border-radius: 10px;
    background: var(--light);
    color: var(--primary);
}

.aviso-academico strong {
    font-size: 12px;
}

.aviso-academico p {
    margin: 4px 0 0;
    font-size: 11px;
    line-height: 1.7;
    opacity: 0.85;
}

/* =========================================================
   ACCIONES DEL FORMULARIO
   ========================================================= */

.formulario-acciones {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 15px;
    padding: 18px 25px;
    border-top: 1px solid #edf0f4;
    background: #fafbfd;
}

.formulario-acciones > p {
    margin: 0;
    color: #64748b;
    font-size: 11px;
}

.acciones-grupo {
    display: flex;
    align-items: center;
    gap: 10px;
}

.spinner {
    width: 17px;
    height: 17px;
    border: 2px solid rgba(255, 255, 255, 0.4);
    border-top-color: #ffffff;
    border-radius: 50%;
    animation: girar 0.8s linear infinite;
}

@keyframes girar {
    to {
        transform: rotate(360deg);
    }
}

/* =========================================================
   FILTROS
   ========================================================= */

.filtros-contenedor {
    display: flex;
    align-items: flex-end;
    flex-wrap: wrap;
    gap: 14px;
    padding: 22px 25px;
}

.filtro-busqueda {
    flex: 1;
    min-width: 200px;
}

.filtro-rol {
    width: 220px;
}

.filtro-estado {
    width: 145px;
}

.filtros-contenedor label {
    margin-bottom: 8px;
}

.buscador {
    position: relative;
}

.buscador svg {
    position: absolute;
    top: 50%;
    left: 13px;
    width: 18px;
    height: 18px;
    color: #94a3b8;
    transform: translateY(-50%);
    pointer-events: none;
}

.buscador input {
    padding-left: 40px;
}

.boton-limpiar {
    min-height: 44px;
    padding: 10px 13px;
    border: 1px solid var(--border);
    border-radius: 9px;
    background: var(--light);
    color: var(--accent);
    font-size: 12px;
    font-weight: 600;
}

.boton-limpiar:hover {
    border-color: var(--accent);
}

/* =========================================================
   TABLA
   ========================================================= */

.tabla-contenedor {
    overflow-x: auto;
    margin: 0 25px;
    border: 1px solid #e7ebf0;
    border-radius: 11px;
}

.tabla-usuarios {
    width: 100%;
    min-width: 1080px;
    border-collapse: collapse;
    text-align: left;
}

.tabla-usuarios thead {
    background: var(--light);
}

.tabla-usuarios th {
    padding: 15px 14px;
    color: var(--primary);
    font-size: 11px;
    font-weight: 700;
    white-space: nowrap;
}

.tabla-usuarios td {
    padding: 17px 14px;
    border-top: 1px solid #edf0f4;
    color: #475569;
    font-size: 12px;
    vertical-align: middle;
}

.tabla-usuarios tbody tr {
    transition: background 0.18s;
}

.tabla-usuarios tbody tr:hover {
    background: var(--background);
}

.fila-inactiva {
    background: #fafbfc;
}

/* =========================================================
   INFORMACIÓN DEL USUARIO
   ========================================================= */

.usuario-celda {
    display: flex;
    align-items: center;
    gap: 12px;
}

.usuario-avatar {
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    width: 41px;
    height: 41px;
    border-radius: 12px;
    font-size: 13px;
    font-weight: 750;
}

.usuario-informacion {
    display: flex;
    flex-direction: column;
    gap: 5px;
    min-width: 120px;
}

.usuario-informacion strong {
    color: #334155;
    font-size: 12px;
    font-weight: 650;
    overflow-wrap: anywhere;
}

.usuario-informacion > span {
    color: #64748b;
    font-size: 10px;
}

.usuario-informacion .usuario-actual {
    color: var(--accent);
    font-weight: 650;
}

.correo-celda,
.fecha-celda {
    display: flex;
    align-items: center;
    gap: 8px;
}

.correo-celda svg,
.fecha-celda svg {
    width: 15px;
    height: 15px;
    color: #94a3b8;
}

.correo-celda span {
    overflow-wrap: anywhere;
}

.fecha-celda {
    white-space: nowrap;
}

/* =========================================================
   ROLES
   ========================================================= */

.rol-badge,
.estado-badge {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 6px 10px;
    border-radius: 7px;
    font-size: 10px;
    font-weight: 650;
    white-space: nowrap;
}

.rol-punto,
.estado-punto {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: currentColor;
}

.rol-administrador {
    background: #f3e8ff;
    color: #7e22ce;
}

.rol-quimico {
    background: #f0fdfa;
    color: #0f766e;
}

.rol-tecnico {
    background: #fff7ed;
    color: #c2410c;
}

.rol-default {
    background: #f1f5f9;
    color: #64748b;
}

/* =========================================================
   ESTADOS
   ========================================================= */

.estado-activo {
    background: #ecfdf5;
    color: #047857;
    border: 1px solid #a7f3d0;
}

.estado-inactivo {
    background: #fef2f2;
    color: #b91c1c;
    border: 1px solid #fecaca;
}

/* =========================================================
   ACCIONES DE LA TABLA
   ========================================================= */

.acciones-tabla {
    display: flex;
    flex-direction: column;
    align-items: stretch;
    gap: 7px;
    min-width: 115px;
}

.boton-tabla {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 7px;
    min-height: 33px;
    padding: 7px 10px;
    border: 1px solid transparent;
    border-radius: 7px;
    font-size: 11px;
    font-weight: 650;
    white-space: nowrap;
    transition: background 0.2s;
}

.boton-tabla svg {
    width: 14px;
    height: 14px;
}

.boton-editar {
    background: var(--light);
    border-color: var(--border);
    color: var(--accent);
}

.boton-editar:hover:not(:disabled) {
    background: var(--accent);
    color: #ffffff;
}

.boton-desactivar {
    background: #fef2f2;
    border-color: #fecaca;
    color: #b91c1c;
}

.boton-desactivar:hover:not(:disabled) {
    background: #dc2626;
    color: #ffffff;
}

.boton-activar {
    background: #ecfdf5;
    border-color: #a7f3d0;
    color: #047857;
}

.boton-activar:hover:not(:disabled) {
    background: #047857;
    color: #ffffff;
}

.cuenta-protegida {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 5px;
    padding: 5px;
    color: #64748b;
    font-size: 10px;
    white-space: nowrap;
}

.cuenta-protegida svg {
    width: 13px;
    height: 13px;
}

.sin-rol-aviso {
    color: #92400e;
    font-size: 10px;
    text-align: center;
}

/* =========================================================
   ESTADO VACÍO
   ========================================================= */

.tabla-usuarios .tabla-vacia {
    padding: 45px 20px;
    text-align: center;
}

.vacio-icono {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 57px;
    height: 57px;
    margin: 0 auto 15px;
    border-radius: 15px;
    background: var(--light);
    color: var(--accent);
}

.vacio-icono svg {
    width: 29px;
    height: 29px;
}

.tabla-vacia h3 {
    margin: 0 0 7px;
    color: var(--primary);
    font-size: 15px;
    font-weight: 650;
}

.tabla-vacia p {
    margin: 0 0 15px;
    color: #64748b;
    font-size: 12px;
}

/* =========================================================
   PIE DE TABLA
   ========================================================= */

.tabla-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 12px;
    padding: 19px 25px;
    color: #64748b;
    font-size: 11px;
}

.tabla-footer strong {
    color: #475569;
    font-weight: 650;
}

.tabla-footer-seguridad {
    display: flex;
    align-items: center;
    gap: 6px;
}

.tabla-footer-seguridad svg {
    width: 14px;
    height: 14px;
}

/* =========================================================
   NOTA DE SEGURIDAD
   ========================================================= */

.nota-seguridad {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 17px;
    border: 1px solid var(--border);
    border-radius: 11px;
    background: var(--light);
    color: var(--primary);
}

.nota-seguridad strong {
    font-size: 12px;
    font-weight: 650;
}

.nota-seguridad p {
    margin: 5px 0 0;
    font-size: 11px;
    line-height: 1.8;
}

/* =========================================================
   TRANSICIONES
   ========================================================= */

.formulario-enter-active,
.formulario-leave-active {
    transition:
        opacity 0.25s ease,
        transform 0.25s ease;
}

.formulario-enter-from,
.formulario-leave-to {
    opacity: 0;
    transform: translateY(-10px);
}

/* =========================================================
   DISEÑO RESPONSIVO
   ========================================================= */

@media (max-width: 1100px) {

    .indicadores-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

}

@media (max-width: 900px) {

    .banner-seguridad {
        padding: 22px;
    }

    .banner-contenido {
        gap: 15px;
    }

    .banner-icono {
        width: 51px;
        height: 51px;
    }

    .banner-contenido h2 {
        font-size: 18px;
    }

    .banner-roles {
        min-width: 110px;
        padding: 13px;
    }

    .filtro-rol {
        width: 200px;
    }

}

@media (max-width: 640px) {

    .usuarios-page {
        gap: 17px;
    }

    .indicadores-grid {
        gap: 10px;
    }

    .indicador-card {
        padding: 15px;
    }

    .indicador-icono {
        width: 37px;
        height: 37px;
    }

    .indicador-icono svg {
        width: 19px;
        height: 19px;
    }

    .indicador-etiqueta {
        display: none;
    }

    .indicador-numero {
        font-size: 27px;
    }

    .indicador-titulo {
        font-size: 11px;
    }

    .indicador-descripcion {
        font-size: 10px;
    }

    .banner-seguridad {
        align-items: flex-start;
        flex-direction: column;
        gap: 18px;
        padding: 19px;
    }

    .banner-contenido {
        align-items: flex-start;
    }

    .banner-icono {
        width: 43px;
        height: 43px;
        border-radius: 11px;
    }

    .banner-icono svg {
        width: 25px;
        height: 25px;
    }

    .banner-etiqueta {
        font-size: 9px;
        letter-spacing: 0.7px;
    }

    .banner-roles {
        align-self: stretch;
        flex-direction: row;
        justify-content: center;
        gap: 12px;
    }

    .banner-roles strong {
        font-size: 23px;
    }

    .panel-encabezado {
        padding: 18px;
        flex-wrap: wrap;
    }

    .panel-encabezado h2 {
        font-size: 15px;
    }

    .panel-encabezado p {
        font-size: 11px;
    }

    .formulario-contenido {
        padding: 18px;
    }

    .campos-grid {
        grid-template-columns: 1fr;
        gap: 18px;
    }

    .formulario-acciones {
        align-items: stretch;
        flex-direction: column;
        padding: 18px;
    }

    .acciones-grupo {
        justify-content: flex-end;
        flex-wrap: wrap;
    }

    .filtros-contenedor {
        align-items: stretch;
        flex-direction: column;
        padding: 18px;
    }

    .filtro-busqueda {
        min-width: 0;
    }

    .filtro-rol,
    .filtro-estado {
        width: 100%;
    }

    .tabla-contenedor {
        margin: 0 18px;
    }

    .tabla-footer {
        padding: 17px 18px;
    }

    .tabla-footer-seguridad {
        font-size: 10px;
    }

    .estado-edicion {
        align-items: flex-start;
        flex-direction: column;
    }

}

@media (prefers-reduced-motion: reduce) {

    *,
    *::before,
    *::after {
        animation-duration: 0.01ms !important;
        transition-duration: 0.01ms !important;
        scroll-behavior: auto !important;
    }

}

</style>