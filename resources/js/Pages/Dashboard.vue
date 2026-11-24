
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
const menuContraido = ref(false)
const menuMovil = ref(false)
const logoDisponible = ref(true)

// USUARIO Y PERMISOS

const usuario = computed(() => page.props.auth?.user ?? null)

const nombreUsuario = computed(
    () => usuario.value?.name ?? 'Usuario'
)

const rolUsuario = computed(
    () => usuario.value?.rol ?? 'Sin rol asignado'
)

const iniciales = computed(() =>
    nombreUsuario.value
        .trim()
        .split(/\s+/)
        .slice(0, 2)
        .map(nombre => nombre.charAt(0).toUpperCase())
        .join('')
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

// ENTORNO DE TRABAJO

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
        return 'Gestiona medicamentos, inventario y ventas simuladas desde un solo lugar.'
    }

    if (entornoActual.value === 'cesfam') {
        return 'Gestiona medicamentos, pacientes, recetas y dispensaciones asistenciales.'
    }

    return 'Selecciona un entorno de trabajo para comenzar.'
})

const fechaActual = new Intl.DateTimeFormat('es-CL', {
    weekday: 'long',
    day: 'numeric',
    month: 'long',
    year: 'numeric',
    timeZone: 'America/Santiago',
}).format(new Date())

// ICONOS SVG

const iconos = {
    dashboard: 'M3 3h7v7H3z M14 3h7v7h-7z M3 14h7v7H3z M14 14h7v7h-7z',

    medicamentos: 'M8 4a5 5 0 0 1 7 0l5 5a5 5 0 0 1-7 7l-5-5a5 5 0 0 1 0-7z M10 13l7-7',

    inventario: 'M3 7l9-4 9 4-9 4-9-4z M3 7v10l9 4 9-4V7 M12 11v10 M7.5 5l9 4',

    movimientos: 'M3 7h17 M16 3l4 4-4 4 M21 17H4 M8 13l-4 4 4 4',

    ventas: 'M2 3h3l3 13h11l3-9H6 M9 21h.01 M18 21h.01',

    pacientes: 'M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2 M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8 M22 21v-2a4 4 0 0 0-3-3.87 M16 3.13a4 4 0 0 1 0 7.75',

    recetas: 'M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z M14 2v6h6 M8 13h8 M8 17h6',

    dispensaciones: 'M8 4h8v4h4v13H4V8h4z M8 4V2h8v2 M12 11v7 M8.5 14.5h7',

    usuarios: 'M12 22s8-4 8-11V5l-8-3-8 3v6c0 7 8 11 8 11z M9 12l2 2 4-4',

    cambiar: 'M4 7h16 M16 3l4 4-4 4 M20 17H4 M8 13l-4 4 4 4',

    salir: 'M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4 M16 17l5-5-5-5 M21 12H9',

    menu: 'M3 6h18 M3 12h18 M3 18h18',

    flecha: 'M5 12h14 M13 6l6 6-6 6',

    derecha: 'M9 6l6 6-6 6',

    abajo: 'M6 9l6 6 6-6',

    cerrar: 'M6 6l12 12 M18 6L6 18',

    alerta: 'M12 22a10 10 0 1 0 0-20 10 10 0 0 0 0 20 M12 7v6 M12 17h.01',

    stock: 'M3 4v16h18 M6 8l5 5 4-4 6 6 M16 15h5v-5',

    reloj: 'M12 22a10 10 0 1 0 0-20 10 10 0 0 0 0 20 M12 6v6l4 2',

    vencido: 'M3 5h18v16H3z M7 2v6 M17 2v6 M3 10h18 M9 13l6 5 M15 13l-6 5',

    calendario: 'M3 5h18v16H3z M7 2v6 M17 2v6 M3 10h18',

    informacion: 'M12 22a10 10 0 1 0 0-20 10 10 0 0 0 0 20 M12 11v6 M12 7h.01',

    farmacia: 'M3 10l9-7 9 7v11H3z M9 14h6 M12 11v6',

    panel: 'M3 3h18v18H3z M9 3v18',

    check: 'M5 12l4 4L19 6',
}

// INDICADORES REALES DEL INVENTARIO

const indicadores = computed(() => [
    {
        nombre: 'Quiebres de stock',
        cantidad: Number(props.alertas?.quiebres_stock ?? 0),
        descripcion: 'Medicamentos sin stock utilizable',
        icono: 'alerta',
        color: '#B91C1C',
        fondo: '#FEF2F2',
    },
    {
        nombre: 'Stock bajo',
        cantidad: Number(props.alertas?.stock_bajo ?? 0),
        descripcion: 'Medicamentos bajo el mínimo configurado',
        icono: 'stock',
        color: '#C2410C',
        fondo: '#FFF7ED',
    },
    {
        nombre: 'Próximos a vencer',
        cantidad: Number(props.alertas?.proximos_vencer ?? 0),
        descripcion: 'Lotes con vencimiento cercano',
        icono: 'reloj',
        color: '#A16207',
        fondo: '#FFFBEB',
    },
    {
        nombre: 'Lotes vencidos',
        cantidad: Number(props.alertas?.lotes_vencidos ?? 0),
        descripcion: 'Lotes vencidos con existencias',
        icono: 'vencido',
        color: '#B91C1C',
        fondo: '#FEF2F2',
    },
])

const totalAlertas = computed(() =>
    indicadores.value.reduce(
        (suma, item) => suma + item.cantidad,
        0
    )
)

// MÓDULOS EXISTENTES

const modulos = [
    {
        nombre: 'Medicamentos',
        descripcion: 'Consulta y gestión del catálogo de medicamentos.',
        enlace: '/medicamentos',
        icono: 'medicamentos',
        entornos: ['privada', 'cesfam'],
    },
    {
        nombre: 'Inventario y lotes',
        descripcion: 'Control de existencias, lotes y vencimientos.',
        enlace: '/lotes',
        icono: 'inventario',
        entornos: ['privada', 'cesfam'],
    },
    {
        nombre: 'Movimientos',
        descripcion: 'Registro de entradas y salidas de inventario.',
        enlace: '/movimientos',
        icono: 'movimientos',
        entornos: ['privada', 'cesfam'],
    },
    {
        nombre: 'Ventas',
        descripcion: 'Ventas y comprobantes de compra simulados.',
        enlace: '/ventas',
        icono: 'ventas',
        entornos: ['privada'],
    },
    {
        nombre: 'Pacientes',
        descripcion: 'Registro de pacientes y antecedentes.',
        enlace: '/pacientes',
        icono: 'pacientes',
        entornos: ['cesfam'],
    },
    {
        nombre: 'Programas de salud',
        descripcion: 'Gestion de programas GES/AUGE y pacientes cronicos.',
        enlace: '/programas-salud',
        icono: 'pacientes',
        entornos: ['cesfam'],
    },
    {
        nombre: 'Recetas',
        descripcion: 'Registro y consulta de recetas médicas simuladas.',
        enlace: '/recetas',
        icono: 'recetas',
        entornos: ['privada', 'cesfam'],
    },
    {
        nombre: 'Dispensaciones',
        descripcion: 'Registro de entrega asistencial de medicamentos.',
        enlace: '/dispensaciones',
        icono: 'dispensaciones',
        entornos: ['cesfam'],
    },
]

const modulosVisibles = computed(() =>
    modulos.filter(modulo => {
        if (
            !entornoActual.value ||
            !modulo.entornos.includes(entornoActual.value)
        ) {
            return false
        }

        if (modulo.nombre === 'Programas de salud') {
            return [
                'Administrador',
                'Quimico Farmaceutico',
            ].includes(rolUsuario.value)
        }

        if (modulo.nombre === 'Dispensaciones') {
            return puedeConsultarDispensaciones.value
        }

        return true
    })
)

// NAVEGACIÓN Y SESIÓN

function alternarMenu() {
    if (window.innerWidth <= 768) {
        menuMovil.value = !menuMovil.value
    } else {
        menuContraido.value = !menuContraido.value
    }
}

function cerrarMenu() {
    menuAbierto.value = false
    menuMovil.value = false
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

    <div
        class="dashboard"
        :data-entorno="entornoActual || 'privada'"
        :class="{
            'menu-contraido': menuContraido,
            'menu-movil-abierto': menuMovil
        }"
        @keydown.esc="cerrarMenu"
    >

        <!-- FONDO DEL MENÚ MÓVIL -->

        <button
            v-if="menuMovil"
            class="fondo-movil"
            aria-label="Cerrar menú lateral"
            @click="menuMovil = false"
        ></button>

        <!-- MENÚ LATERAL -->

        <aside class="sidebar">

            <!-- LOGO FARMACONTROL -->

            <Link href="/dashboard" class="marca">

                <div class="marca-icono">
                    <svg viewBox="0 0 24 24" fill="currentColor">
                        <path d="M9 3H15V9H21V15H15V21H9V15H3V9H9Z"/>
                    </svg>
                </div>

                <div class="texto-marca">
                    <strong>FarmaControl</strong>
                    <small>Gestión farmacéutica</small>
                </div>

            </Link>

            <!-- ENTORNO ACTIVO -->

            <div class="entorno-sidebar">

                <svg class="icono" viewBox="0 0 24 24">
                    <path :d="iconos.farmacia"/>
                </svg>

                <div class="texto-entorno">
                    <small>Entorno de trabajo</small>
                    <strong>{{ nombreEntorno }}</strong>
                </div>

            </div>

            <!-- NAVEGACIÓN PRINCIPAL -->

            <nav class="navegacion" aria-label="Menú principal">

                <p class="grupo-titulo">GENERAL</p>

                <Link
                    href="/dashboard"
                    class="nav-item activo"
                    title="Panel principal"
                    aria-current="page"
                    @click="cerrarMenu"
                >
                    <svg class="icono" viewBox="0 0 24 24">
                        <path :d="iconos.dashboard"/>
                    </svg>

                    <span>Panel principal</span>
                </Link>

                <p class="grupo-titulo">
                    GESTIÓN FARMACÉUTICA
                </p>

                <Link
                    v-for="modulo in modulosVisibles"
                    :key="modulo.enlace"
                    :href="modulo.enlace"
                    :title="modulo.nombre"
                    class="nav-item"
                    @click="cerrarMenu"
                >
                    <svg class="icono" viewBox="0 0 24 24">
                        <path :d="iconos[modulo.icono]"/>
                    </svg>

                    <span>{{ modulo.nombre }}</span>
                </Link>

                <!-- ADMINISTRACIÓN -->

                <template v-if="esAdministrador">

                    <p class="grupo-titulo">
                        ADMINISTRACIÓN
                    </p>

                    <Link
                        href="/usuarios"
                        class="nav-item"
                        title="Usuarios y roles"
                        @click="cerrarMenu"
                    >
                        <svg class="icono" viewBox="0 0 24 24">
                            <path :d="iconos.usuarios"/>
                        </svg>

                        <span>Usuarios y roles</span>
                    </Link>

                </template>

            </nav>

            <!-- OPCIONES INFERIORES -->

            <div class="sidebar-inferior">

                <Link
                    href="/"
                    class="nav-item"
                    title="Cambiar entorno"
                >
                    <svg class="icono" viewBox="0 0 24 24">
                        <path :d="iconos.cambiar"/>
                    </svg>

                    <span>Cambiar entorno</span>
                </Link>

                <button
                    type="button"
                    class="nav-item boton-salir"
                    title="Cerrar sesión"
                    :disabled="cerrandoSesion"
                    @click="cerrarSesion"
                >
                    <svg class="icono" viewBox="0 0 24 24">
                        <path :d="iconos.salir"/>
                    </svg>

                    <span>
                        {{ cerrandoSesion
                            ? 'Cerrando sesión...'
                            : 'Cerrar sesión' }}
                    </span>
                </button>

                <div class="sidebar-academico">
                    <span class="punto-academico"></span>
                    <span>Proyecto Integrado · INACAP</span>
                </div>

            </div>

        </aside>

        <!-- ÁREA PRINCIPAL -->

        <div class="area-principal">

            <!-- ENCABEZADO SUPERIOR -->

            <header class="barra-superior">

                <div class="barra-izquierda">

                    <button
                        type="button"
                        class="boton-menu"
                        aria-label="Alternar menú lateral"
                        @click="alternarMenu"
                    >
                        <svg class="icono" viewBox="0 0 24 24">
                            <path :d="iconos.panel"/>
                        </svg>
                    </button>

                    <div class="separador-vertical"></div>

                    <div class="ruta-actual">
                        <span>FarmaControl</span>

                        <svg viewBox="0 0 24 24" class="icono">
                            <path :d="iconos.derecha"/>
                        </svg>

                        <strong>Panel principal</strong>
                    </div>

                </div>

                <div class="barra-derecha">

                    <!-- LOGO INACAP -->

                    <div class="logo-inacap">

                        <img
                            v-if="logoDisponible"
                            :src="'/images/logo-inacap.png'"
                            alt="INACAP"
                            @error="logoDisponible = false"
                        />

                        <span v-else class="logo-alternativo">
                            INACAP
                        </span>

                    </div>

                    <div class="separador-vertical"></div>

                    <!-- PERFIL DEL USUARIO -->

                    <div class="perfil-contenedor">

                        <button
                            type="button"
                            class="perfil"
                            :aria-expanded="menuAbierto"
                            @click="menuAbierto = !menuAbierto"
                        >

                            <div class="avatar">
                                {{ iniciales }}
                            </div>

                            <div class="perfil-texto">
                                <strong>{{ nombreUsuario }}</strong>
                                <small>{{ rolUsuario }}</small>
                            </div>

                            <svg
                                class="icono perfil-flecha"
                                :class="{ girado: menuAbierto }"
                                viewBox="0 0 24 24"
                            >
                                <path :d="iconos.abajo"/>
                            </svg>

                        </button>

                        <button
                            v-if="menuAbierto"
                            class="fondo-menu"
                            aria-label="Cerrar opciones de usuario"
                            @click="menuAbierto = false"
                        ></button>

                        <Transition name="desplegar">

                            <div
                                v-if="menuAbierto"
                                class="menu-perfil"
                            >

                                <div class="menu-perfil-datos">
                                    <strong>{{ nombreUsuario }}</strong>
                                    <small>{{ rolUsuario }}</small>
                                    <small v-if="usuario?.email">
                                        {{ usuario.email }}
                                    </small>
                                </div>

                                <Link
                                    v-if="esAdministrador"
                                    href="/usuarios"
                                    class="opcion-perfil"
                                    @click="cerrarMenu"
                                >
                                    <svg class="icono" viewBox="0 0 24 24">
                                        <path :d="iconos.usuarios"/>
                                    </svg>

                                    Usuarios y roles
                                </Link>

                                <Link
                                    href="/"
                                    class="opcion-perfil"
                                    @click="cerrarMenu"
                                >
                                    <svg class="icono" viewBox="0 0 24 24">
                                        <path :d="iconos.cambiar"/>
                                    </svg>

                                    Cambiar entorno
                                </Link>

                                <button
                                    type="button"
                                    class="opcion-perfil opcion-salir"
                                    :disabled="cerrandoSesion"
                                    @click="cerrarSesion"
                                >
                                    <svg class="icono" viewBox="0 0 24 24">
                                        <path :d="iconos.salir"/>
                                    </svg>

                                    Cerrar sesión
                                </button>

                            </div>

                        </Transition>

                    </div>

                </div>

            </header>

            <!-- CONTENIDO DEL DASHBOARD -->

            <main class="contenido">

                <!-- TÍTULO Y FECHA -->

                <div class="encabezado-pagina">

                    <div>
                        <p class="etiqueta-pagina">
                            ESPACIO DE TRABAJO
                        </p>

                        <h1>Panel principal</h1>

                        <p class="subtitulo">
                            Resumen general de la gestión farmacéutica.
                        </p>
                    </div>

                    <div class="fecha">

                        <svg class="icono" viewBox="0 0 24 24">
                            <path :d="iconos.calendario"/>
                        </svg>

                        {{ fechaActual }}

                    </div>

                </div>

                <!-- BIENVENIDA -->

                <section class="bienvenida">

                    <div class="bienvenida-decoracion" aria-hidden="true">

                        <svg viewBox="0 0 280 220">

                            <circle
                                cx="200" cy="100" r="95"
                                fill="none"
                                stroke="white"
                                stroke-opacity=".12"
                            />

                            <circle
                                cx="200" cy="100" r="125"
                                fill="none"
                                stroke="white"
                                stroke-opacity=".08"
                            />

                            <g transform="rotate(35 185 110)">

                                <rect
                                    x="155" y="35"
                                    width="60" height="150"
                                    rx="30"
                                    fill="white"
                                    fill-opacity=".10"
                                    stroke="white"
                                    stroke-opacity=".35"
                                    stroke-width="2"
                                />

                                <path
                                    d="M155 110H215V155A30 30 0 0 1 155 155Z"
                                    fill="white"
                                    fill-opacity=".25"
                                />

                                <path
                                    d="M155 110H215"
                                    stroke="white"
                                    stroke-opacity=".4"
                                />

                            </g>

                            <path
                                d="M60 30V60M45 45H75"
                                stroke="white"
                                stroke-opacity=".2"
                                stroke-width="8"
                            />

                            <path
                                d="M245 160V180M235 170H255"
                                stroke="white"
                                stroke-opacity=".2"
                                stroke-width="5"
                            />

                        </svg>

                    </div>

                    <div class="bienvenida-contenido">

                        <div class="entorno-badge">
                            <span></span>
                            {{ nombreEntorno }}
                        </div>

                        <h2>
                            ¡Bienvenido/a, {{ nombreUsuario }}!
                        </h2>

                        <p>{{ descripcionEntorno }}</p>

                        <div class="bienvenida-inferior">

                            <span>
                                <svg class="icono" viewBox="0 0 24 24">
                                    <path :d="iconos.usuarios"/>
                                </svg>

                                {{ rolUsuario }}
                            </span>

                            <span>
                                <svg class="icono" viewBox="0 0 24 24">
                                    <path :d="iconos.informacion"/>
                                </svg>

                                Simulación académica
                            </span>

                        </div>

                    </div>

                </section>

                <!-- ALERTAS DE INVENTARIO -->

                <section
                    class="seccion"
                    aria-labelledby="titulo-alertas"
                >

                    <div class="seccion-encabezado">

                        <div>
                            <h2 id="titulo-alertas">
                                Alertas de inventario
                            </h2>

                            <p>
                                Indicadores obtenidos de los registros del sistema.
                            </p>
                        </div>

                        <div
                            class="estado-alertas"
                            :class="totalAlertas > 0
                                ? 'estado-pendiente'
                                : 'estado-correcto'"
                        >
                            <span class="estado-punto"></span>

                            {{ totalAlertas > 0
                                ? totalAlertas + ' alertas registradas'
                                : 'Sin alertas registradas' }}
                        </div>

                    </div>

                    <div class="indicadores-grid">

                        <article
                            v-for="indicador in indicadores"
                            :key="indicador.nombre"
                            class="indicador"
                            :style="{
                                '--indicador-color': indicador.color,
                                '--indicador-fondo': indicador.fondo
                            }"
                        >

                            <div class="indicador-superior">

                                <div class="indicador-icono">

                                    <svg class="icono" viewBox="0 0 24 24">
                                        <path :d="iconos[indicador.icono]"/>
                                    </svg>

                                </div>

                                <span class="indicador-estado">
                                    {{ indicador.cantidad > 0
                                        ? 'Revisar'
                                        : 'Sin alertas' }}
                                </span>

                            </div>

                            <p class="indicador-nombre">
                                {{ indicador.nombre }}
                            </p>

                            <strong class="indicador-numero">
                                {{ indicador.cantidad }}
                            </strong>

                            <p class="indicador-descripcion">
                                {{ indicador.descripcion }}
                            </p>

                        </article>

                    </div>

                    <div class="nota-inventario">

                        <div>

                            <svg class="icono" viewBox="0 0 24 24">
                                <path :d="iconos.informacion"/>
                            </svg>

                            <p>
                                Las alertas de vencimiento consideran
                                un plazo de 30 días.
                            </p>

                        </div>

                        <Link
                            href="/lotes"
                            class="enlace-inventario"
                        >
                            Ver inventario y lotes

                            <svg class="icono" viewBox="0 0 24 24">
                                <path :d="iconos.flecha"/>
                            </svg>
                        </Link>

                    </div>

                </section>

                <!-- ACCESOS RÁPIDOS -->

                <section
                    class="seccion"
                    aria-labelledby="titulo-modulos"
                >

                    <div class="seccion-encabezado">

                        <div>
                            <h2 id="titulo-modulos">
                                Accesos rápidos
                            </h2>

                            <p>
                                Módulos disponibles según tu entorno y rol.
                            </p>
                        </div>

                        <span class="cantidad-modulos">
                            {{ modulosVisibles.length }} módulos
                        </span>

                    </div>

                    <div class="modulos-grid">

                        <Link
                            v-for="modulo in modulosVisibles"
                            :key="modulo.enlace"
                            :href="modulo.enlace"
                            class="modulo"
                        >

                            <div class="modulo-icono">

                                <svg class="icono" viewBox="0 0 24 24">
                                    <path :d="iconos[modulo.icono]"/>
                                </svg>

                            </div>

                            <div class="modulo-texto">
                                <h3>{{ modulo.nombre }}</h3>
                                <p>{{ modulo.descripcion }}</p>
                            </div>

                            <svg
                                class="icono modulo-flecha"
                                viewBox="0 0 24 24"
                            >
                                <path :d="iconos.derecha"/>
                            </svg>

                        </Link>

                    </div>

                    <div
                        v-if="modulosVisibles.length === 0"
                        class="sin-modulos"
                    >
                        <svg class="icono" viewBox="0 0 24 24">
                            <path :d="iconos.informacion"/>
                        </svg>

                        <p>
                            No hay módulos disponibles para este entorno.
                        </p>

                        <Link href="/">
                            Seleccionar entorno
                        </Link>
                    </div>

                </section>

                <!-- INFORMACIÓN DEL ENTORNO -->

                <section class="tarjeta-entorno">

                    <div class="tarjeta-entorno-icono">

                        <svg class="icono" viewBox="0 0 24 24">
                            <path :d="iconos.farmacia"/>
                        </svg>

                    </div>

                    <div class="tarjeta-entorno-texto">

                        <h3>Entorno de trabajo actual</h3>

                        <strong>{{ nombreEntorno }}</strong>

                        <p>
                            Los módulos disponibles dependen del
                            entorno seleccionado y del rol del usuario.
                        </p>

                    </div>

                    <Link href="/" class="boton-cambiar">

                        <svg class="icono" viewBox="0 0 24 24">
                            <path :d="iconos.cambiar"/>
                        </svg>

                        Cambiar entorno

                    </Link>

                </section>

                <!-- PIE DE PÁGINA -->

                <footer class="pie-pagina">

                    <p>
                        FarmaControl · Proyecto Integrado INACAP
                    </p>

                    <p>Sistema de gestión farmacéutica académica</p>

                </footer>

            </main>

        </div>

        <!-- MARCA DE AGUA PERMANENTE -->

        <div class="marca-agua">

            <strong>Uso académico · INACAP</strong>

            <span>
                Sistema de simulación · Sin validez legal
            </span>

        </div>

    </div>

</template>

<style scoped>

/* =========================================
   CONFIGURACIÓN GENERAL
   ========================================= */

.dashboard {
    --ancho-sidebar: 252px;
    --alto-header: 72px;

    min-height: 100vh;
    background: var(--fc-background);
    color: #1E293B;
    font-family: 'Inter', sans-serif;
}

.dashboard * {
    box-sizing: border-box;
}

.dashboard[data-entorno="privada"] {
    --fc-primary: #172554;
    --fc-accent: #1D4ED8;
    --fc-hover: #1E40AF;
    --fc-light: #EFF6FF;
    --fc-background: #F8FAFC;
    --fc-border: #DBEAFE;
}

.dashboard[data-entorno="cesfam"] {
    --fc-primary: #333344;
    --fc-accent: #665579;
    --fc-hover: #51435F;
    --fc-light: #E3D5F5;
    --fc-background: #F8F7FC;
    --fc-border: #E6E0EF;
}

.icono {
    width: 21px;
    height: 21px;
    flex-shrink: 0;
    fill: none;
    stroke: currentColor;
    stroke-width: 1.7;
    stroke-linecap: round;
    stroke-linejoin: round;
}

.dashboard button {
    cursor: pointer;
}

.dashboard button:disabled {
    cursor: wait;
    opacity: .6;
}

/* =========================================
   MENÚ LATERAL
   ========================================= */

.sidebar {
    position: fixed;
    inset: 0 auto 0 0;
    z-index: 60;

    display: flex;
    flex-direction: column;

    width: var(--ancho-sidebar);
    height: 100dvh;
    padding: 24px 14px 15px;

    background: var(--fc-primary);
    color: white;

    overflow: hidden;
    transition: width .25s ease;
}

.marca {
    display: flex;
    align-items: center;
    gap: 11px;
    padding: 0 7px 25px;
    text-decoration: none;
    color: white;
    flex-shrink: 0;
}

.marca-icono {
    display: flex;
    align-items: center;
    justify-content: center;

    width: 39px;
    height: 39px;
    flex-shrink: 0;

    border-radius: 11px;
    background: rgba(255, 255, 255, .14);
}

.marca-icono svg {
    width: 23px;
    height: 23px;
}

.texto-marca {
    display: flex;
    flex-direction: column;
    white-space: nowrap;
}

.texto-marca strong {
    font-size: 20px;
    font-weight: 800;
    letter-spacing: -.6px;
}

.texto-marca small {
    margin-top: 2px;
    color: rgba(255, 255, 255, .60);
    font-size: 10px;
}

/* ENTORNO EN EL MENÚ */

.entorno-sidebar {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 20px;
    padding: 12px;

    border: 1px solid rgba(255, 255, 255, .12);
    border-radius: 10px;
    background: rgba(255, 255, 255, .07);
}

.entorno-sidebar > .icono {
    color: rgba(255, 255, 255, .85);
}

.texto-entorno {
    display: flex;
    flex-direction: column;
    gap: 5px;
    min-width: 0;
}

.texto-entorno small {
    color: rgba(255, 255, 255, .60);
    font-size: 10px;
}

.texto-entorno strong {
    color: white;
    font-size: 11px;
    font-weight: 600;
    line-height: 1.5;
}

/* NAVEGACIÓN */

.navegacion {
    flex: 1;
    min-height: 0;
    overflow-y: auto;
    overflow-x: hidden;
    scrollbar-width: thin;
    scrollbar-color: #ffffff33 transparent;
}

.grupo-titulo {
    margin: 20px 10px 9px;
    color: rgba(255, 255, 255, .48);
    font-size: 9px;
    font-weight: 600;
    letter-spacing: 1.2px;
    white-space: nowrap;
}

.grupo-titulo:first-child {
    margin-top: 0;
}

.nav-item {
    position: relative;
    display: flex;
    align-items: center;
    gap: 12px;

    width: 100%;
    min-height: 43px;
    margin-bottom: 4px;
    padding: 11px 12px;

    border: none;
    border-radius: 8px;

    background: transparent;
    color: rgba(255, 255, 255, .78);

    font-size: 12px;
    font-weight: 500;
    text-align: left;
    text-decoration: none;
    white-space: nowrap;

    transition: background .2s, color .2s;
}

.nav-item .icono {
    width: 19px;
    height: 19px;
}

.nav-item:hover {
    background: rgba(255, 255, 255, .10);
    color: white;
}

.nav-item.activo {
    background: rgba(255, 255, 255, .16);
    color: white;
    font-weight: 600;
}

.nav-item.activo::before {
    position: absolute;
    left: 0;
    top: 10px;
    bottom: 10px;

    width: 3px;
    border-radius: 5px;
    background: white;

    content: '';
}

/* MENÚ INFERIOR */

.sidebar-inferior {
    flex-shrink: 0;
    padding-top: 12px;
    border-top: 1px solid rgba(255, 255, 255, .12);
}

.boton-salir {
    color: #FECACA;
}

.boton-salir:hover {
    background: rgba(239, 68, 68, .15);
    color: white;
}

.sidebar-academico {
    display: flex;
    align-items: center;
    gap: 7px;
    padding: 15px 9px 3px;

    color: rgba(255, 255, 255, .48);
    font-size: 9px;
    white-space: nowrap;
}

.punto-academico {
    width: 6px;
    height: 6px;
    flex-shrink: 0;
    border-radius: 50%;
    background: #FCA5A5;
}

/* =========================================
   MENÚ CONTRAÍDO
   ========================================= */

.menu-contraido {
    --ancho-sidebar: 76px;
}

.menu-contraido .texto-marca,
.menu-contraido .texto-entorno,
.menu-contraido .grupo-titulo,
.menu-contraido .nav-item span,
.menu-contraido .sidebar-academico {
    display: none;
}

.menu-contraido .marca {
    justify-content: center;
    padding-left: 0;
    padding-right: 0;
}

.menu-contraido .entorno-sidebar {
    justify-content: center;
    padding: 10px;
}

.menu-contraido .nav-item {
    justify-content: center;
    padding: 12px 8px;
}

/* =========================================
   ÁREA PRINCIPAL
   ========================================= */

.area-principal {
    min-height: 100vh;
    margin-left: var(--ancho-sidebar);
    transition: margin-left .25s ease;
}

/* =========================================
   BARRA SUPERIOR
   ========================================= */

.barra-superior {
    position: sticky;
    top: 0;
    z-index: 40;

    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;

    min-height: var(--alto-header);
    padding: 12px 28px;

    border-bottom: 1px solid #E2E8F0;
    background: white;
}

.barra-izquierda,
.barra-derecha {
    display: flex;
    align-items: center;
    gap: 17px;
    min-width: 0;
}

.boton-menu {
    display: flex;
    align-items: center;
    justify-content: center;

    width: 37px;
    height: 37px;

    border: 1px solid #E2E8F0;
    border-radius: 8px;
    background: white;
    color: var(--fc-primary);
}

.boton-menu:hover {
    background: var(--fc-light);
}

.separador-vertical {
    width: 1px;
    height: 30px;
    flex-shrink: 0;
    background: #E2E8F0;
}

.ruta-actual {
    display: flex;
    align-items: center;
    gap: 8px;
    color: #94A3B8;
    font-size: 12px;
}

.ruta-actual strong {
    color: #334155;
    font-weight: 600;
}

.ruta-actual .icono {
    width: 13px;
    height: 13px;
}

/* LOGO INSTITUCIONAL */

.logo-inacap img {
    display: block;
    width: 105px;
    max-height: 40px;
    object-fit: contain;
}

.logo-alternativo {
    color: #222222;
    font-size: 22px;
    font-weight: 800;
    font-style: italic;
    border-bottom: 3px solid #E30613;
}

/* =========================================
   PERFIL DEL USUARIO
   ========================================= */

.perfil-contenedor {
    position: relative;
}

.perfil {
    display: flex;
    align-items: center;
    gap: 10px;

    padding: 5px;
    border-radius: 9px;
    background: transparent;
    text-align: left;
}

.perfil:hover {
    background: var(--fc-light);
}

.avatar {
    display: flex;
    align-items: center;
    justify-content: center;

    width: 38px;
    height: 38px;
    flex-shrink: 0;

    border-radius: 50%;
    background: var(--fc-light);
    color: var(--fc-primary);

    font-size: 13px;
    font-weight: 700;
}

.perfil-texto {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.perfil-texto strong {
    max-width: 155px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;

    color: #1E293B;
    font-size: 12px;
    font-weight: 600;
}

.perfil-texto small {
    color: #64748B;
    font-size: 10px;
}

.perfil-flecha {
    width: 15px;
    height: 15px;
    color: #64748B;
    transition: transform .2s;
}

.perfil-flecha.girado {
    transform: rotate(180deg);
}

.fondo-menu {
    position: fixed;
    inset: 0;
    z-index: 41;
    background: transparent;
    border: none;
}

.menu-perfil {
    position: absolute;
    right: 0;
    top: calc(100% + 12px);
    z-index: 42;

    width: 250px;
    padding: 7px;

    border: 1px solid #E2E8F0;
    border-radius: 12px;
    background: white;
    box-shadow: 0 12px 35px rgba(15, 23, 42, .12);
}

.menu-perfil-datos {
    display: flex;
    flex-direction: column;
    gap: 6px;

    margin-bottom: 5px;
    padding: 13px;
    border-radius: 8px;
    background: var(--fc-light);
}

.menu-perfil-datos strong {
    color: var(--fc-primary);
    font-size: 13px;
}

.menu-perfil-datos small {
    color: #64748B;
    font-size: 11px;
    overflow-wrap: anywhere;
}

.opcion-perfil {
    display: flex;
    align-items: center;
    gap: 10px;

    width: 100%;
    padding: 12px;

    border-radius: 7px;
    background: transparent;
    color: #475569;

    font-size: 12px;
    text-decoration: none;
    text-align: left;
}

.opcion-perfil .icono {
    width: 18px;
    height: 18px;
}

.opcion-perfil:hover {
    background: var(--fc-light);
    color: var(--fc-primary);
}

.opcion-salir {
    color: #B91C1C;
}

.opcion-salir:hover {
    background: #FEF2F2;
    color: #B91C1C;
}

/* =========================================
   CONTENIDO PRINCIPAL
   ========================================= */

.contenido {
    width: 100%;
    max-width: 1600px;
    margin: 0 auto;
    padding: 30px 28px 95px;
}

.encabezado-pagina {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 15px;
    margin-bottom: 24px;
}

.etiqueta-pagina {
    margin-bottom: 8px;
    color: var(--fc-accent);
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 1.5px;
}

.encabezado-pagina h1 {
    color: #172033;
    font-size: 29px;
    font-weight: 750;
    line-height: 1.2;
    letter-spacing: -.8px;
}

.subtitulo {
    margin-top: 8px;
    color: #64748B;
    font-size: 13px;
}

.fecha {
    display: flex;
    align-items: center;
    gap: 8px;

    padding: 10px 13px;
    border: 1px solid #E2E8F0;
    border-radius: 8px;
    background: white;

    color: #64748B;
    font-size: 11px;
    text-transform: capitalize;
}

.fecha .icono {
    width: 16px;
    height: 16px;
    color: var(--fc-accent);
}

/* =========================================
   BIENVENIDA
   ========================================= */

.bienvenida {
    position: relative;
    isolation: isolate;
    overflow: hidden;

    padding: 28px 30px;
    border-radius: 15px;

    background: linear-gradient(
        115deg,
        var(--fc-primary),
        var(--fc-accent)
    );

    color: white;
}

.bienvenida-contenido {
    position: relative;
    z-index: 1;
    max-width: 75%;
}

.bienvenida-decoracion {
    position: absolute;
    right: 0;
    top: 50%;
    width: 280px;
    transform: translateY(-50%);
    pointer-events: none;
}

.bienvenida-decoracion svg {
    width: 100%;
}

.entorno-badge {
    display: inline-flex;
    align-items: center;
    gap: 7px;

    padding: 6px 10px;
    border: 1px solid rgba(255, 255, 255, .20);
    border-radius: 6px;
    background: rgba(255, 255, 255, .10);

    color: white;
    font-size: 10px;
    font-weight: 500;
}

.entorno-badge span {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: #FFFFFF;
}

.bienvenida h2 {
    margin-top: 17px;
    color: white;
    font-size: clamp(23px, 2.5vw, 34px);
    font-weight: 700;
    line-height: 1.3;
    letter-spacing: -.7px;
    overflow-wrap: anywhere;
}

.bienvenida-contenido > p {
    margin-top: 10px;
    color: rgba(255, 255, 255, .85);
    font-size: 13px;
    line-height: 1.8;
}

.bienvenida-inferior {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    margin-top: 22px;
}

.bienvenida-inferior span {
    display: flex;
    align-items: center;
    gap: 7px;
    color: rgba(255, 255, 255, .85);
    font-size: 11px;
}

.bienvenida-inferior .icono {
    width: 16px;
    height: 16px;
}

/* =========================================
   SECCIONES
   ========================================= */

.seccion {
    margin-top: 30px;
}

.seccion-encabezado {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 12px;
    margin-bottom: 17px;
}

.seccion-encabezado h2 {
    color: #1E293B;
    font-size: 18px;
    font-weight: 700;
    letter-spacing: -.3px;
}

.seccion-encabezado p {
    margin-top: 6px;
    color: #64748B;
    font-size: 12px;
}

/* ESTADO DE ALERTAS */

.estado-alertas {
    display: flex;
    align-items: center;
    gap: 7px;

    padding: 7px 11px;
    border-radius: 20px;
    font-size: 10px;
    font-weight: 600;
}

.estado-pendiente {
    background: #FFFBEB;
    color: #92400E;
    border: 1px solid #FDE68A;
}

.estado-correcto {
    background: #F0FDF4;
    color: #166534;
    border: 1px solid #BBF7D0;
}

.estado-punto {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: currentColor;
}

/* =========================================
   TARJETAS DE INDICADORES
   ========================================= */

.indicadores-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 15px;
}

.indicador {
    min-width: 0;
    padding: 18px;

    border: 1px solid #E2E8F0;
    border-radius: 12px;
    background: white;

    box-shadow: 0 3px 8px rgba(15, 23, 42, .025);
    transition: box-shadow .2s, transform .2s;
}

.indicador:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(15, 23, 42, .06);
}

.indicador-superior {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 7px;
    margin-bottom: 16px;
}

.indicador-icono {
    display: flex;
    align-items: center;
    justify-content: center;

    width: 39px;
    height: 39px;

    border-radius: 10px;
    background: var(--indicador-fondo);
    color: var(--indicador-color);
}

.indicador-estado {
    padding: 4px 7px;
    border-radius: 5px;

    background: #F8FAFC;
    color: #64748B;

    font-size: 9px;
    font-weight: 500;
}

.indicador-nombre {
    color: #475569;
    font-size: 12px;
    font-weight: 600;
}

.indicador-numero {
    display: block;
    margin-top: 7px;

    color: var(--indicador-color);
    font-size: 34px;
    font-weight: 750;
    font-variant-numeric: tabular-nums;
    line-height: 1.2;
}

.indicador-descripcion {
    margin-top: 9px;
    color: #64748B;
    font-size: 10px;
    line-height: 1.7;
}

/* NOTA DE INVENTARIO */

.nota-inventario {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 12px;

    margin-top: 13px;
    padding: 12px 15px;

    border: 1px solid var(--fc-border);
    border-radius: 9px;
    background: var(--fc-light);
}

.nota-inventario > div {
    display: flex;
    align-items: center;
    gap: 9px;
}

.nota-inventario p {
    color: #475569;
    font-size: 11px;
    line-height: 1.6;
}

.nota-inventario .icono {
    width: 16px;
    height: 16px;
    color: var(--fc-accent);
}

.enlace-inventario {
    display: flex;
    align-items: center;
    gap: 7px;
    color: var(--fc-primary);
    font-size: 11px;
    font-weight: 600;
    text-decoration: none;
}

.enlace-inventario:hover {
    text-decoration: underline;
}

/* =========================================
   ACCESOS RÁPIDOS
   ========================================= */

.cantidad-modulos {
    color: #64748B;
    font-size: 11px;
}

.modulos-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 13px;
}

.modulo {
    display: flex;
    align-items: center;
    gap: 14px;

    min-width: 0;
    min-height: 92px;
    padding: 17px;

    border: 1px solid #E2E8F0;
    border-radius: 11px;
    background: white;

    text-decoration: none;
    transition: border-color .2s, box-shadow .2s, transform .2s;
}

.modulo:hover {
    transform: translateY(-2px);
    border-color: var(--fc-accent);
    box-shadow: 0 6px 20px rgba(15, 23, 42, .05);
}

.modulo-icono {
    display: flex;
    align-items: center;
    justify-content: center;

    width: 46px;
    height: 46px;
    flex-shrink: 0;

    border-radius: 11px;
    background: var(--fc-light);
    color: var(--fc-accent);

    transition: background .2s, color .2s;
}

.modulo:hover .modulo-icono {
    background: var(--fc-primary);
    color: white;
}

.modulo-icono .icono {
    width: 23px;
    height: 23px;
}

.modulo-texto {
    flex: 1;
    min-width: 0;
}

.modulo-texto h3 {
    color: #1E293B;
    font-size: 13px;
    font-weight: 650;
}

.modulo-texto p {
    margin-top: 5px;
    color: #64748B;
    font-size: 11px;
    line-height: 1.6;
}

.modulo-flecha {
    width: 16px;
    height: 16px;
    color: #94A3B8;
    transition: transform .2s;
}

.modulo:hover .modulo-flecha {
    transform: translateX(3px);
    color: var(--fc-accent);
}

.sin-modulos {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 13px;

    padding: 30px;
    border: 1px solid #E2E8F0;
    border-radius: 12px;
    background: white;

    color: #64748B;
    font-size: 13px;
    text-align: center;
}

.sin-modulos a {
    color: var(--fc-accent);
    font-weight: 600;
}

/* =========================================
   ENTORNO DE TRABAJO
   ========================================= */

.tarjeta-entorno {
    display: flex;
    align-items: center;
    gap: 15px;

    margin-top: 30px;
    padding: 20px;

    border: 1px solid #E2E8F0;
    border-radius: 12px;
    background: white;
}

.tarjeta-entorno-icono {
    display: flex;
    align-items: center;
    justify-content: center;

    width: 49px;
    height: 49px;
    flex-shrink: 0;

    border-radius: 12px;
    background: var(--fc-light);
    color: var(--fc-accent);
}

.tarjeta-entorno-texto {
    flex: 1;
}

.tarjeta-entorno-texto h3 {
    margin-bottom: 5px;
    color: #64748B;
    font-size: 11px;
    font-weight: 500;
}

.tarjeta-entorno-texto strong {
    color: var(--fc-primary);
    font-size: 14px;
    font-weight: 700;
}

.tarjeta-entorno-texto p {
    margin-top: 6px;
    color: #64748B;
    font-size: 11px;
    line-height: 1.6;
}

.boton-cambiar {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;

    padding: 10px 14px;
    border: 1px solid var(--fc-border);
    border-radius: 8px;

    background: var(--fc-light);
    color: var(--fc-primary);

    font-size: 11px;
    font-weight: 600;
    text-decoration: none;
    white-space: nowrap;

    transition: background .2s, color .2s;
}

.boton-cambiar .icono {
    width: 16px;
    height: 16px;
}

.boton-cambiar:hover {
    background: var(--fc-primary);
    color: white;
}

/* =========================================
   PIE DE PÁGINA Y MARCA DE AGUA
   ========================================= */

.pie-pagina {
    display: flex;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 10px;

    margin-top: 35px;
    padding-top: 18px;

    border-top: 1px solid #E2E8F0;
    color: #64748B;
    font-size: 10px;
}

.marca-agua {
    position: fixed;
    right: 14px;
    bottom: 12px;
    z-index: 45;

    display: flex;
    flex-direction: column;
    gap: 3px;

    padding: 8px 12px;

    border: 1px solid #E2E8F0;
    border-left: 3px solid #E30613;
    border-radius: 7px;

    background: rgba(255, 255, 255, .96);
    box-shadow: 0 3px 12px rgba(15, 23, 42, .05);

    color: #64748B;
    font-size: 9px;
    text-align: right;

    pointer-events: none;
}

.marca-agua strong {
    color: var(--fc-primary);
    font-weight: 650;
}

/* =========================================
   ANIMACIONES
   ========================================= */

.desplegar-enter-active,
.desplegar-leave-active {
    transition: opacity .18s, transform .18s;
}

.desplegar-enter-from,
.desplegar-leave-to {
    opacity: 0;
    transform: translateY(-7px);
}

.fondo-movil {
    display: none;
}

/* =========================================
   PANTALLAS MEDIANAS
   ========================================= */

@media (max-width: 1200px) {

    .dashboard:not(.menu-contraido) {
        --ancho-sidebar: 230px;
    }

    .indicadores-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .contenido {
        padding-left: 22px;
        padding-right: 22px;
    }

    .barra-superior {
        padding-left: 22px;
        padding-right: 22px;
    }

    .ruta-actual > span,
    .ruta-actual > .icono {
        display: none;
    }

}

@media (max-width: 950px) {

    .perfil-texto {
        display: none;
    }

    .modulos-grid {
        grid-template-columns: 1fr;
    }

    .tarjeta-entorno {
        flex-wrap: wrap;
    }

    .bienvenida-contenido {
        max-width: 100%;
    }

    .bienvenida-decoracion {
        opacity: .55;
    }

}

/* =========================================
   TABLETS Y CELULARES
   ========================================= */

@media (max-width: 768px) {

    .dashboard,
    .dashboard:not(.menu-contraido),
    .dashboard.menu-contraido {
        --ancho-sidebar: 252px;
    }

    .sidebar {
        transform: translateX(-100%);
        transition: transform .25s ease;
    }

    .menu-movil-abierto .sidebar {
        transform: translateX(0);
    }

    .area-principal {
        margin-left: 0;
    }

    .fondo-movil {
        display: block;
        position: fixed;
        inset: 0;
        z-index: 55;
        border: none;
        background: rgba(15, 23, 42, .45);
    }

    .menu-contraido .texto-marca,
    .menu-contraido .texto-entorno {
        display: flex;
    }

    .menu-contraido .grupo-titulo,
    .menu-contraido .nav-item span {
        display: block;
    }

    .menu-contraido .sidebar-academico {
        display: flex;
    }

    .menu-contraido .nav-item,
    .menu-contraido .marca,
    .menu-contraido .entorno-sidebar {
        justify-content: flex-start;
    }

    .barra-superior {
        min-height: 64px;
        padding: 10px 15px;
    }

    .barra-izquierda,
    .barra-derecha {
        gap: 10px;
    }

    .barra-izquierda .separador-vertical {
        display: none;
    }

    .ruta-actual {
        font-size: 11px;
    }

    .logo-inacap img {
        width: 80px;
    }

    .contenido {
        padding: 22px 16px 90px;
    }

    .encabezado-pagina h1 {
        font-size: 25px;
    }

    .bienvenida {
        padding: 23px;
    }

    .bienvenida h2 {
        font-size: 24px;
    }

    .indicadores-grid {
        gap: 10px;
    }

    .indicador {
        padding: 14px;
    }

    .indicador-numero {
        font-size: 29px;
    }

    .modulo {
        min-height: 85px;
    }

}

@media (max-width: 400px) {

    .ruta-actual {
        display: none;
    }

    .indicadores-grid {
        grid-template-columns: 1fr;
    }

    .tarjeta-entorno-texto {
        flex-basis: 65%;
    }

    .boton-cambiar {
        width: 100%;
    }

    .marca-agua {
        font-size: 8px;
    }

}

/* =========================================
   ACCESIBILIDAD
   ========================================= */

.dashboard a:focus-visible,
.dashboard button:focus-visible {
    outline: 2px solid var(--fc-accent);
    outline-offset: 3px;
}

.sidebar a:focus-visible,
.sidebar button:focus-visible {
    outline-color: white;
}

@media (prefers-reduced-motion: reduce) {

    .dashboard *,
    .dashboard *::before,
    .dashboard *::after {
        transition: none !important;
        animation: none !important;
    }

    .indicador:hover,
    .modulo:hover {
        transform: none;
    }

}


/* ==========================================
   DASHBOARD CESFAM - LAVANDA CLARO
   ========================================== */

.dashboard[data-entorno="cesfam"] {
    background: #F8F7FC;
    color: #333344;
}

/* MENU LATERAL */

[data-entorno="cesfam"] .sidebar {
    background: #E3D5F5;
    color: #333344;
    border-right: 1px solid #D2BFEA;
}

[data-entorno="cesfam"] .marca {
    color: #333344;
}

[data-entorno="cesfam"] .marca-icono {
    background: #FFFFFF;
    color: #665579;
}

[data-entorno="cesfam"] .texto-marca strong {
    color: #333344;
}

[data-entorno="cesfam"] .texto-marca small {
    color: #665579;
}

[data-entorno="cesfam"] .entorno-sidebar {
    background: #F8F7FC;
    border-color: #D2BFEA;
}

[data-entorno="cesfam"] .entorno-sidebar > .icono {
    color: #665579;
}

[data-entorno="cesfam"] .texto-entorno strong {
    color: #333344;
}

[data-entorno="cesfam"] .texto-entorno small {
    color: #665579;
}

[data-entorno="cesfam"] .grupo-titulo {
    color: #665579;
}

[data-entorno="cesfam"] .nav-item {
    color: #333344;
}

[data-entorno="cesfam"] .nav-item:hover {
    background: #D2BFEA;
    color: #333344;
}

[data-entorno="cesfam"] .nav-item.activo {
    background: #FFFFFF;
    color: #333344;
    font-weight: 650;
}

[data-entorno="cesfam"] .nav-item.activo::before {
    background: #665579;
}

[data-entorno="cesfam"] .sidebar-inferior {
    border-top-color: #D2BFEA;
}

[data-entorno="cesfam"] .boton-salir {
    color: #333344;
}

[data-entorno="cesfam"] .boton-salir:hover {
    background: #FEE2E2;
    color: #B91C1C;
}

[data-entorno="cesfam"] .sidebar-academico {
    color: #665579;
}

[data-entorno="cesfam"] .navegacion {
    scrollbar-color: #BDAAD5 transparent;
}

[data-entorno="cesfam"] .sidebar a:focus-visible,
[data-entorno="cesfam"] .sidebar button:focus-visible {
    outline-color: #665579;
}

/* BARRA SUPERIOR */

[data-entorno="cesfam"] .barra-superior {
    background: #FFFFFF;
    border-bottom-color: #E6E0EF;
}

[data-entorno="cesfam"] .boton-menu {
    background: #FFFFFF;
    color: #333344;
    border-color: #E6E0EF;
}

[data-entorno="cesfam"] .boton-menu:hover {
    background: #E3D5F5;
}

[data-entorno="cesfam"] .avatar {
    background: #E3D5F5;
    color: #333344;
}

[data-entorno="cesfam"] .perfil:hover {
    background: #F8F7FC;
}

[data-entorno="cesfam"] .menu-perfil {
    border-color: #E6E0EF;
}

/* CONTENIDO PRINCIPAL */

[data-entorno="cesfam"] .area-principal {
    background: #F8F7FC;
}

[data-entorno="cesfam"] .encabezado-pagina h1 {
    color: #333344;
}

/* BANNER DE BIENVENIDA */

[data-entorno="cesfam"] .bienvenida {
    background: linear-gradient(
        115deg,
        #E3D5F5 0%,
        #F1EAF9 100%
    );

    border: 1px solid #DCCCEB;
    color: #333344;
}

[data-entorno="cesfam"] .bienvenida h2 {
    color: #333344;
}

[data-entorno="cesfam"] .bienvenida-contenido > p {
    color: #51475F;
}

[data-entorno="cesfam"] .bienvenida-inferior span {
    color: #51475F;
}

[data-entorno="cesfam"] .bienvenida-decoracion {
    opacity: 0.65;
}

[data-entorno="cesfam"] .bienvenida-decoracion svg {
    filter: brightness(0) saturate(100%);
    opacity: 0.18;
}

[data-entorno="cesfam"] .entorno-badge {
    background: #F8F7FC;
    border-color: #D2BFEA;
    color: #333344;
}

[data-entorno="cesfam"] .entorno-badge span {
    background: #665579;
}

/* ACCESOS RAPIDOS */

[data-entorno="cesfam"] .modulo {
    background: #FFFFFF;
    border-color: #E6E0EF;
}

[data-entorno="cesfam"] .modulo:hover {
    border-color: #BDAAD5;
    box-shadow: 0 6px 20px rgba(102, 85, 121, 0.10);
}

[data-entorno="cesfam"] .modulo-icono {
    background: #E3D5F5;
    color: #665579;
}

[data-entorno="cesfam"] .modulo:hover .modulo-icono {
    background: #D2BFEA;
    color: #333344;
}

[data-entorno="cesfam"] .modulo-texto h3 {
    color: #333344;
}

/* TARJETA DE ENTORNO */

[data-entorno="cesfam"] .tarjeta-entorno {
    background: #FFFFFF;
    border-color: #E6E0EF;
}

[data-entorno="cesfam"] .tarjeta-entorno-icono {
    background: #E3D5F5;
    color: #665579;
}

[data-entorno="cesfam"] .boton-cambiar {
    background: #E3D5F5;
    border-color: #D2BFEA;
    color: #333344;
}

[data-entorno="cesfam"] .boton-cambiar:hover {
    background: #D2BFEA;
    color: #333344;
}

[data-entorno="cesfam"] .pie-pagina {
    border-top-color: #E6E0EF;
}
</style>
