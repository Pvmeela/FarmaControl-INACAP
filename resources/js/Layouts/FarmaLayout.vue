<script setup>
import { computed, ref } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'

const page = usePage()

defineProps({
    titulo: {
        type: String,
        default: 'Panel principal',
    },
    subtitulo: {
        type: String,
        default: '',
    },
})

const menuContraido = ref(false)
const menuMovil = ref(false)
const menuUsuario = ref(false)
const cerrandoSesion = ref(false)
const logoDisponible = ref(true)

const usuario = computed(() => page.props.auth?.user ?? null)
const nombreUsuario = computed(() => usuario.value?.name ?? 'Usuario')
const rolUsuario = computed(() => usuario.value?.rol ?? 'Sin rol asignado')

const entorno = computed(() =>
    ['privada', 'cesfam'].includes(page.props.entorno)
        ? page.props.entorno
        : null
)

const nombreEntorno = computed(() => {
    if (entorno.value === 'cesfam') return 'Farmacia Asistencial'
    if (entorno.value === 'privada') return 'Farmacia Privada'
    return 'Entorno no seleccionado'
})

const iniciales = computed(() =>
    nombreUsuario.value
        .trim()
        .split(/\s+/)
        .slice(0, 2)
        .map(nombre => nombre.charAt(0))
        .join('')
        .toUpperCase()
)

const esAdministrador = computed(() =>
    rolUsuario.value === 'Administrador'
)

const puedeConsultarDispensaciones = computed(() =>
    [
        'Administrador',
        'Quimico Farmaceutico',
        'Tecnico en Farmacia',
    ].includes(rolUsuario.value)
)

const modulos = [
    {
        nombre: 'Medicamentos',
        ruta: '/medicamentos',
        icono: 'medicamentos',
        entornos: ['privada', 'cesfam'],
    },
    {
        nombre: 'Inventario y lotes',
        ruta: '/lotes',
        icono: 'inventario',
        entornos: ['privada', 'cesfam'],
    },
    {
        nombre: 'Movimientos',
        ruta: '/movimientos',
        icono: 'movimientos',
        entornos: ['privada', 'cesfam'],
    },
    {
        nombre: 'Ventas',
        ruta: '/ventas',
        icono: 'ventas',
        entornos: ['privada'],
    },
    {
        nombre: 'Pacientes',
        ruta: '/pacientes',
        icono: 'pacientes',
        entornos: ['cesfam'],
    },
    {
        nombre: 'Recetas',
        ruta: '/recetas',
        icono: 'recetas',
        entornos: ['privada', 'cesfam'],
    },
    {
        nombre: 'Dispensaciones',
        ruta: '/dispensaciones',
        icono: 'dispensaciones',
        entornos: ['cesfam'],
    },
]

const modulosVisibles = computed(() =>
    modulos.filter(modulo => {
        if (!modulo.entornos.includes(entorno.value)) {
            return false
        }

        if (modulo.nombre === 'Dispensaciones') {
            return puedeConsultarDispensaciones.value
        }

        return true
    })
)

const iconos = {
    dashboard: 'M3 3h7v7H3z M14 3h7v7h-7z M3 14h7v7H3z M14 14h7v7h-7z',
    medicamentos: 'M8 4a5 5 0 0 1 7 0l5 5a5 5 0 0 1-7 7l-5-5a5 5 0 0 1 0-7z M10 13l7-7',
    inventario: 'M3 7l9-4 9 4v10l-9 4-9-4z M3 7l9 4 9-4 M12 11v10',
    movimientos: 'M3 7h17 M16 3l4 4-4 4 M21 17H4 M8 13l-4 4 4 4',
    ventas: 'M3 3h2l3 12h11l2-9H6 M9 20h.01 M18 20h.01',
    pacientes: 'M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2 M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8 M19 8v6 M16 11h6',
    recetas: 'M6 3h9l5 5v13H6z M14 3v6h6 M9 13h8 M9 17h8',
    dispensaciones: 'M4 8h16v13H4z M8 8V4h8v4 M12 11v7 M9 14h6',
    usuarios: 'M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2 M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8 M22 21v-2a4 4 0 0 0-3-4 M16 3a4 4 0 0 1 0 8',
    cambiar: 'M3 7h18 M17 3l4 4-4 4 M21 17H3 M7 13l-4 4 4 4',
    salir: 'M10 17l5-5-5-5 M15 12H3 M14 3h6v18h-6',
    menu: 'M4 6h16 M4 12h16 M4 18h16',
    flecha: 'M6 9l6 6 6-6',
    cruz: 'M9 3h6v6h6v6h-6v6H9v-6H3V9h6z',
}

function rutaActiva(ruta) {
    const actual = page.url.split('?')[0]

    return actual === ruta || actual.startsWith(ruta + '/')
}

function alternarMenu() {
    if (window.innerWidth <= 768) {
        menuMovil.value = !menuMovil.value
    } else {
        menuContraido.value = !menuContraido.value
    }
}

function cerrarMenus() {
    menuMovil.value = false
    menuUsuario.value = false
}

function cerrarSesion() {
    if (cerrandoSesion.value) return

    cerrarMenus()
    cerrandoSesion.value = true

    router.post('/logout', {}, {
        onFinish: () => {
            cerrandoSesion.value = false
        },
    })
}
</script>

<template>
    <div
        class="farma-layout"
        :data-entorno="entorno || 'privada'"
        :class="{
            'menu-contraido': menuContraido,
            'menu-movil-abierto': menuMovil,
        }"
        @keydown.esc="cerrarMenus"
    >
        <button
            v-if="menuMovil"
            class="fondo-movil"
            aria-label="Cerrar menú"
            @click="cerrarMenus"
        ></button>

        <!-- MENÚ LATERAL -->

        <aside class="sidebar">

            <Link
                href="/dashboard"
                class="marca"
                @click="cerrarMenus"
            >
                <div class="marca-icono">
                    <svg viewBox="0 0 24 24" fill="currentColor">
                        <path :d="iconos.cruz" />
                    </svg>
                </div>

                <div class="texto-marca">
                    <strong>FarmaControl</strong>
                    <span>Gestión farmacéutica</span>
                </div>
            </Link>

            <div class="entorno-sidebar">
                <span class="punto-entorno"></span>

                <div class="texto-entorno">
                    <small>ENTORNO ACTUAL</small>
                    <strong>{{ nombreEntorno }}</strong>
                    <span v-if="entorno === 'cesfam'">CESFAM</span>
                </div>
            </div>

            <nav class="navegacion">

                <p class="etiqueta-menu">GENERAL</p>

                <Link
                    href="/dashboard"
                    class="enlace-menu"
                    :class="{ activo: rutaActiva('/dashboard') }"
                    title="Panel principal"
                    @click="cerrarMenus"
                >
                    <svg viewBox="0 0 24 24">
                        <path :d="iconos.dashboard" />
                    </svg>

                    <span>Panel principal</span>
                </Link>

                <p class="etiqueta-menu">GESTIÓN FARMACÉUTICA</p>

                <Link
                    v-for="modulo in modulosVisibles"
                    :key="modulo.ruta"
                    :href="modulo.ruta"
                    :title="modulo.nombre"
                    class="enlace-menu"
                    :class="{ activo: rutaActiva(modulo.ruta) }"
                    @click="cerrarMenus"
                >
                    <svg viewBox="0 0 24 24">
                        <path :d="iconos[modulo.icono]" />
                    </svg>

                    <span>{{ modulo.nombre }}</span>
                </Link>

                <template v-if="esAdministrador">
                    <p class="etiqueta-menu">ADMINISTRACIÓN</p>

                    <Link
                        href="/usuarios"
                        class="enlace-menu"
                        :class="{ activo: rutaActiva('/usuarios') }"
                        title="Usuarios"
                        @click="cerrarMenus"
                    >
                        <svg viewBox="0 0 24 24">
                            <path :d="iconos.usuarios" />
                        </svg>

                        <span>Usuarios</span>
                    </Link>
                </template>

            </nav>

            <div class="sidebar-inferior">

                <Link
                    href="/"
                    class="enlace-menu"
                    title="Cambiar entorno"
                >
                    <svg viewBox="0 0 24 24">
                        <path :d="iconos.cambiar" />
                    </svg>

                    <span>Cambiar entorno</span>
                </Link>

                <button
                    class="enlace-menu boton-salir"
                    title="Cerrar sesión"
                    :disabled="cerrandoSesion"
                    @click="cerrarSesion"
                >
                    <svg viewBox="0 0 24 24">
                        <path :d="iconos.salir" />
                    </svg>

                    <span>
                        {{ cerrandoSesion ? 'Cerrando...' : 'Cerrar sesión' }}
                    </span>
                </button>

                <div class="nota-academica">
                    <strong>Proyecto académico</strong>
                    <span>INACAP · FarmaControl</span>
                </div>

            </div>
        </aside>

        <!-- ÁREA PRINCIPAL -->

        <div class="area-principal">

            <header class="barra-superior">

                <div class="barra-izquierda">

                    <button
                        class="boton-menu"
                        aria-label="Alternar menú lateral"
                        @click="alternarMenu"
                    >
                        <svg viewBox="0 0 24 24">
                            <path :d="iconos.menu" />
                        </svg>
                    </button>

                    <div class="ruta-navegacion">
                        <span>FarmaControl</span>
                        <span class="separador">/</span>
                        <strong>{{ titulo }}</strong>
                    </div>

                </div>

                <div class="barra-derecha">

                    <div class="logo-institucional">
                        <img
                            v-if="logoDisponible"
                            :src="'/images/logo-inacap.png'"
                            alt="INACAP"
                            @error="logoDisponible = false"
                        />

                        <strong v-else class="logo-alternativo">
                            INACAP
                        </strong>
                    </div>

                    <div class="perfil">

                        <button
                            class="boton-perfil"
                            :aria-expanded="menuUsuario"
                            @click="menuUsuario = !menuUsuario"
                        >
                            <div class="avatar">
                                {{ iniciales }}
                            </div>

                            <div class="datos-perfil">
                                <strong>{{ nombreUsuario }}</strong>
                                <span>{{ rolUsuario }}</span>
                            </div>

                            <svg viewBox="0 0 24 24" class="flecha-perfil">
                                <path :d="iconos.flecha" />
                            </svg>
                        </button>

                        <Transition>
                            <div v-if="menuUsuario" class="menu-perfil">

                                <div class="informacion-perfil">
                                    <strong>{{ nombreUsuario }}</strong>
                                    <span>{{ usuario?.email }}</span>
                                </div>

                                <Link
                                    v-if="esAdministrador"
                                    href="/usuarios"
                                    @click="cerrarMenus"
                                >
                                    Administración de usuarios
                                </Link>

                                <Link href="/" @click="cerrarMenus">
                                    Cambiar entorno
                                </Link>

                                <button
                                    :disabled="cerrandoSesion"
                                    @click="cerrarSesion"
                                >
                                    Cerrar sesión
                                </button>

                            </div>
                        </Transition>

                    </div>
                </div>

            </header>

            <!-- CONTENIDO DE CADA MÓDULO -->

            <main class="contenido-principal">

                <div class="encabezado-pagina">
                    <div>
                        <h1>{{ titulo }}</h1>
                        <p v-if="subtitulo">{{ subtitulo }}</p>
                    </div>

                    <slot name="acciones" />
                </div>

                <slot />

            </main>

            <footer class="pie-pagina">
                <span>FarmaControl · Proyecto Integrado INACAP</span>
                <span>Sistema de simulación académica</span>
            </footer>

        </div>

        <!-- MARCA DE AGUA -->

        <div class="fc-watermark">
            <strong>Uso académico · INACAP</strong>
            <br />
            Sistema de simulación · Sin validez legal
        </div>

    </div>
</template>

<style scoped>
.farma-layout {
    --ancho-sidebar: 252px;
    min-height: 100vh;
    background: var(--fc-background);
    color: #1e293b;
    font-family: 'Inter', sans-serif;
}

.farma-layout.menu-contraido {
    --ancho-sidebar: 76px;
}

/* MENÚ LATERAL */

.sidebar {
    position: fixed;
    inset: 0 auto 0 0;
    z-index: 40;
    width: var(--ancho-sidebar);
    display: flex;
    flex-direction: column;
    background: var(--fc-primary);
    color: white;
    transition: width 0.25s ease;
    overflow: hidden;
}

.marca {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 28px 20px;
    text-decoration: none;
    color: white;
}

.marca-icono {
    width: 40px;
    height: 40px;
    flex-shrink: 0;
    display: grid;
    place-items: center;
    border-radius: 12px;
    background: rgba(255, 255, 255, 0.14);
}

.marca-icono svg {
    width: 24px;
    height: 24px;
}

.texto-marca {
    display: flex;
    flex-direction: column;
    white-space: nowrap;
}

.texto-marca strong {
    font-size: 19px;
    font-weight: 750;
    letter-spacing: -0.6px;
}

.texto-marca span {
    font-size: 10px;
    opacity: 0.65;
}

.entorno-sidebar {
    display: flex;
    align-items: center;
    gap: 12px;
    margin: 0 15px 20px;
    padding: 14px;
    border: 1px solid rgba(255, 255, 255, 0.12);
    border-radius: 12px;
    background: rgba(255, 255, 255, 0.07);
}

.punto-entorno {
    width: 9px;
    height: 9px;
    flex-shrink: 0;
    border-radius: 50%;
    background: #c4b5fd;
    box-shadow: 0 0 0 4px rgba(255, 255, 255, 0.08);
}

[data-entorno="privada"] .punto-entorno {
    background: #93c5fd;
}

.texto-entorno {
    display: flex;
    flex-direction: column;
    gap: 3px;
    white-space: nowrap;
}

.texto-entorno small {
    font-size: 9px;
    letter-spacing: 0.8px;
    opacity: 0.6;
}

.texto-entorno strong {
    font-size: 12px;
    font-weight: 600;
}

.texto-entorno span {
    font-size: 10px;
    opacity: 0.7;
}

.navegacion {
    flex: 1;
    min-height: 0;
    overflow-y: auto;
    overflow-x: hidden;
    padding: 0 12px;
}

.etiqueta-menu {
    margin: 20px 10px 9px;
    color: rgba(255, 255, 255, 0.5);
    font-size: 9px;
    font-weight: 700;
    letter-spacing: 0.8px;
    white-space: nowrap;
}

.enlace-menu {
    display: flex;
    align-items: center;
    gap: 12px;
    width: 100%;
    min-height: 43px;
    margin-bottom: 4px;
    padding: 11px 13px;
    border-radius: 9px;
    color: rgba(255, 255, 255, 0.75);
    font-size: 13px;
    font-weight: 500;
    text-decoration: none;
    text-align: left;
    white-space: nowrap;
    cursor: pointer;
    transition: background 0.2s, color 0.2s;
}

.enlace-menu svg,
.boton-menu svg,
.flecha-perfil {
    width: 19px;
    height: 19px;
    flex-shrink: 0;
    fill: none;
    stroke: currentColor;
    stroke-width: 1.7;
    stroke-linecap: round;
    stroke-linejoin: round;
}

.enlace-menu:hover {
    color: white;
    background: rgba(255, 255, 255, 0.08);
}

.enlace-menu.activo {
    background: rgba(255, 255, 255, 0.15);
    color: white;
    font-weight: 650;
    box-shadow: inset 3px 0 0 #dbeafe;
}

.sidebar-inferior {
    padding: 14px 12px;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
}

.boton-salir:hover {
    color: #fecaca;
    background: rgba(239, 68, 68, 0.12);
}

.nota-academica {
    display: flex;
    flex-direction: column;
    gap: 4px;
    margin: 12px 10px 4px;
    white-space: nowrap;
}

.nota-academica strong {
    font-size: 10px;
    font-weight: 600;
    opacity: 0.7;
}

.nota-academica span {
    font-size: 9px;
    opacity: 0.45;
}

/* BARRA SUPERIOR */

.area-principal {
    min-height: 100vh;
    margin-left: var(--ancho-sidebar);
    display: flex;
    flex-direction: column;
    transition: margin-left 0.25s ease;
}

.barra-superior {
    position: sticky;
    top: 0;
    z-index: 30;
    min-height: 76px;
    padding: 12px 28px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
    background: white;
    border-bottom: 1px solid #e8edf3;
}

.barra-izquierda,
.barra-derecha {
    display: flex;
    align-items: center;
    gap: 20px;
}

.boton-menu {
    width: 38px;
    height: 38px;
    display: grid;
    place-items: center;
    border: 1px solid #e2e8f0;
    border-radius: 9px;
    color: #64748b;
    cursor: pointer;
}

.boton-menu:hover {
    background: var(--fc-light);
    color: var(--fc-primary);
}

.ruta-navegacion {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 12px;
    color: #94a3b8;
}

.ruta-navegacion strong {
    color: #334155;
    font-weight: 600;
}

.separador {
    color: #cbd5e1;
}

.logo-institucional {
    padding-right: 20px;
    border-right: 1px solid #e2e8f0;
}

.logo-institucional img {
    width: 105px;
    height: auto;
    object-fit: contain;
}

.logo-alternativo {
    color: #e30613;
    font-size: 20px;
    font-weight: 800;
}

/* PERFIL */

.perfil {
    position: relative;
}

.boton-perfil {
    display: flex;
    align-items: center;
    gap: 10px;
    cursor: pointer;
    text-align: left;
}

.avatar {
    width: 39px;
    height: 39px;
    flex-shrink: 0;
    display: grid;
    place-items: center;
    border-radius: 11px;
    background: var(--fc-light);
    color: var(--fc-primary);
    font-size: 13px;
    font-weight: 750;
}

.datos-perfil {
    display: flex;
    flex-direction: column;
    gap: 3px;
}

.datos-perfil strong {
    font-size: 12px;
    font-weight: 650;
    color: #1e293b;
}

.datos-perfil span {
    font-size: 10px;
    color: #64748b;
}

.flecha-perfil {
    width: 15px;
    height: 15px;
    color: #94a3b8;
}

.menu-perfil {
    position: absolute;
    right: 0;
    top: calc(100% + 18px);
    width: 245px;
    padding: 7px;
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    box-shadow: 0 12px 35px rgba(15, 23, 42, 0.12);
}

.informacion-perfil {
    display: flex;
    flex-direction: column;
    gap: 5px;
    padding: 13px;
    border-bottom: 1px solid #e2e8f0;
    margin-bottom: 5px;
    overflow-wrap: anywhere;
}

.informacion-perfil strong {
    font-size: 13px;
}

.informacion-perfil span {
    font-size: 11px;
    color: #64748b;
}

.menu-perfil a,
.menu-perfil button {
    display: block;
    width: 100%;
    padding: 11px 13px;
    border-radius: 7px;
    font-size: 12px;
    text-align: left;
    cursor: pointer;
}

.menu-perfil a:hover,
.menu-perfil button:hover {
    background: var(--fc-light);
}

.menu-perfil button {
    color: #dc2626;
}

/* CONTENIDO */

.contenido-principal {
    width: 100%;
    max-width: 1600px;
    flex: 1;
    margin: 0 auto;
    padding: 30px;
    min-width: 0;
}

.encabezado-pagina {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
    flex-wrap: wrap;
    margin-bottom: 26px;
}

.encabezado-pagina h1 {
    color: var(--fc-primary);
    font-size: 27px;
    font-weight: 750;
    letter-spacing: -0.8px;
}

.encabezado-pagina p {
    margin-top: 6px;
    color: #64748b;
    font-size: 13px;
    line-height: 1.6;
}

.pie-pagina {
    display: flex;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 10px;
    padding: 20px 30px 65px;
    color: #94a3b8;
    font-size: 10px;
}

/* MENÚ CONTRAÍDO */

.menu-contraido .texto-marca,
.menu-contraido .texto-entorno,
.menu-contraido .enlace-menu span,
.menu-contraido .etiqueta-menu,
.menu-contraido .nota-academica {
    display: none;
}

.menu-contraido .marca {
    justify-content: center;
    padding: 28px 10px;
}

.menu-contraido .entorno-sidebar {
    justify-content: center;
    padding: 14px 5px;
}

.menu-contraido .enlace-menu {
    justify-content: center;
    padding: 13px 5px;
}

.fondo-movil {
    display: none;
}

/* ANIMACIONES */

.v-enter-active,
.v-leave-active {
    transition: opacity 0.18s, transform 0.18s;
}

.v-enter-from,
.v-leave-to {
    opacity: 0;
    transform: translateY(-6px);
}

/* DISEÑO RESPONSIVO */

@media (max-width: 1050px) {
    .ruta-navegacion {
        display: none;
    }

    .contenido-principal {
        padding: 24px;
    }
}

@media (max-width: 768px) {
    .sidebar {
        width: 252px;
        transform: translateX(-100%);
        transition: transform 0.25s ease;
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
        z-index: 35;
        background: rgba(15, 23, 42, 0.5);
    }

    .barra-superior {
        padding: 12px 16px;
        gap: 10px;
    }

    .barra-derecha {
        gap: 12px;
    }

    .datos-perfil,
    .flecha-perfil {
        display: none;
    }

    .logo-institucional {
        padding-right: 12px;
    }

    .logo-institucional img {
        width: 85px;
    }

    .contenido-principal {
        padding: 20px 16px;
    }

    .encabezado-pagina h1 {
        font-size: 23px;
    }

    .menu-contraido .texto-marca,
    .menu-contraido .texto-entorno {
        display: flex;
    }

    .menu-contraido .enlace-menu span,
    .menu-contraido .etiqueta-menu {
        display: block;
    }

    .menu-contraido .enlace-menu {
        justify-content: flex-start;
        padding: 11px 13px;
    }
}

@media (prefers-reduced-motion: reduce) {
    *,
    *::before,
    *::after {
        transition: none !important;
        animation: none !important;
    }
}
</style>