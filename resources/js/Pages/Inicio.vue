<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { ref, onMounted, onUnmounted } from 'vue'

const logoDisponible = ref(true)
const desplazamiento = ref(0)
const menuAbierto = ref(false)
const pagina = ref(null)

let observador = null

const entornos = [
    {
        id: 'privada',
        nombre: 'Farmacia Privada',
        categoria: 'GESTIÓN COMERCIAL',
        descripcion: 'Simula las operaciones de una farmacia comercial, gestionando medicamentos, inventario y ventas.',
        ruta: '/seleccionar-entorno/privada',
    },
    {
        id: 'cesfam',
        nombre: 'Farmacia Asistencial CESFAM',
        categoria: 'ATENCIÓN ASISTENCIAL',
        descripcion: 'Desarrolla competencias en atención farmacéutica, gestión de pacientes, recetas y dispensaciones.',
        ruta: '/seleccionar-entorno/cesfam',
    },
]

const actualizarScroll = () => {
    desplazamiento.value = window.scrollY
}

const irASeccion = (id) => {
    menuAbierto.value = false

    const reducirMovimiento = window.matchMedia(
        '(prefers-reduced-motion: reduce)'
    ).matches

    document.getElementById(id)?.scrollIntoView({
        behavior: reducirMovimiento ? 'instant' : 'smooth',
        block: 'start',
    })
}

onMounted(() => {
    actualizarScroll()

    window.addEventListener(
        'scroll',
        actualizarScroll,
        { passive: true }
    )

    if (
        'IntersectionObserver' in window &&
        !window.matchMedia(
            '(prefers-reduced-motion: reduce)'
        ).matches
    ) {
        observador = new IntersectionObserver(
            (entradas) => {
                entradas.forEach((entrada) => {
                    if (entrada.isIntersecting) {
                        entrada.target.classList.add('visible')
                        observador.unobserve(entrada.target)
                    }
                })
            },
            { threshold: 0.12 }
        )

        pagina.value?.querySelectorAll('.revelar').forEach(
            (elemento) => {
                elemento.classList.add('animar-entrada')
                observador.observe(elemento)
            }
        )
    }
})

onUnmounted(() => {
    window.removeEventListener('scroll', actualizarScroll)
    observador?.disconnect()
})
</script>

<template>

    <Head title="Inicio | FarmaControl" />

    <div ref="pagina" class="inicio-page">

        <!-- ENCABEZADO PRINCIPAL -->

        <header
            class="encabezado"
            :class="{ 'encabezado-scroll': desplazamiento > 20 }"
        >

            <div class="encabezado-contenido">

                <button
                    type="button"
                    class="identidad"
                    @click="irASeccion('inicio')"
                >

                    <div class="logo-farma">

                        <svg
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                            stroke-linejoin="round"
                            aria-hidden="true"
                        >
                            <path d="M9 3H15V9H21V15H15V21H9V15H3V9H9Z"/>
                        </svg>

                    </div>

                    <div class="identidad-texto">

                        <h1>FarmaControl</h1>

                        <p>
                            Gestión farmacéutica integrada
                        </p>

                    </div>

                </button>

                <!-- NAVEGACIÓN -->

                <nav
                    class="navegacion"
                    :class="{ 'menu-abierto': menuAbierto }"
                    aria-label="Navegación principal"
                >

                    <button
                        type="button"
                        @click="irASeccion('inicio')"
                    >
                        Inicio
                    </button>

                    <button
                        type="button"
                        @click="irASeccion('entornos')"
                    >
                        Entornos
                    </button>

                    <button
                        type="button"
                        @click="irASeccion('nosotros')"
                    >
                        Sobre la plataforma
                    </button>

                </nav>

                <!-- LOGO INACAP -->

                <div class="institucion">

                    <img
                        v-if="logoDisponible"
                        :src="'/images/logo-inacap.png'"
                        alt="INACAP"
                        class="fc-logo-inacap"
                        @error="logoDisponible = false"
                    />

                    <span
                        v-else
                        class="logo-alternativo"
                    >
                        INACAP
                    </span>

                    <button
                        type="button"
                        class="boton-menu"
                        aria-label="Abrir o cerrar navegación"
                        :aria-expanded="menuAbierto"
                        @click="menuAbierto = !menuAbierto"
                    >

                        <svg
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                        >
                            <path
                                v-if="!menuAbierto"
                                d="M4 6H20M4 12H20M4 18H20"
                            />
                            <path
                                v-else
                                d="M6 6L18 18M18 6L6 18"
                            />
                        </svg>

                    </button>

                </div>

            </div>

        </header>

        <main>

            <!-- =====================================
                 SECCIÓN 1: BIENVENIDA
                 ===================================== -->

            <section id="inicio" class="hero">

                <div class="patron-fondo"></div>

                <div class="fc-glow brillo-uno"></div>
                <div class="fc-glow brillo-dos"></div>

                <div
                    class="hero-contenido"
                >

                    <!-- TEXTO PRINCIPAL -->

                    <div class="hero-texto fc-slide-up">

                        <div class="etiqueta-hero">

                            <span class="punto-activo"></span>

                            INNOVACIÓN · TECNOLOGÍA · SALUD

                        </div>

                        <h2>

                            El futuro de la gestión

                            <span>
                                farmacéutica
                            </span>

                            comienza aquí.

                        </h2>

                        <p class="hero-descripcion">

                            Una plataforma de simulación académica
                            que conecta el aprendizaje, la tecnología
                            y la práctica profesional en un mismo lugar.

                        </p>

                        <div class="hero-acciones">

                            <button
                                type="button"
                                class="boton-explorar"
                                @click="irASeccion('entornos')"
                            >

                                Explorar plataforma

                                <svg
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="2"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                >
                                    <path d="M5 12H19M12 5L19 12L12 19"/>
                                </svg>

                            </button>

                            <button
                                type="button"
                                class="boton-conocer"
                                @click="irASeccion('nosotros')"
                            >
                                Conocer más
                            </button>

                        </div>

                        <div class="hero-institucional">

                            <svg
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.7"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                aria-hidden="true"
                            >
                                <path d="M2 10L12 4L22 10L12 16Z"/>
                                <path d="M6 13V18C9 21 15 21 18 18V13"/>
                                <path d="M22 10V17"/>
                            </svg>

                            Proyecto Integrado · INACAP

                        </div>

                    </div>

                    <!-- ILUSTRACIÓN FARMACÉUTICA -->

                    <div
                        class="hero-ilustracion"
                        :style="{
                            '--parallax': Math.min(
                                desplazamiento * 0.08,
                                60
                            ) + 'px'
                        }"
                        aria-hidden="true"
                    >

                        <svg
                            viewBox="0 0 500 500"
                            fill="none"
                            class="ilustracion-principal"
                        >

                            <defs>

                                <linearGradient
                                    id="capsula-clara"
                                    x1="0"
                                    y1="0"
                                    x2="1"
                                    y2="1"
                                >
                                    <stop offset="0%" stop-color="#FFFFFF"/>
                                    <stop offset="100%" stop-color="#B9C8ED"/>
                                </linearGradient>

                                <linearGradient
                                    id="capsula-oscura"
                                    x1="0"
                                    y1="0"
                                    x2="1"
                                    y2="1"
                                >
                                    <stop offset="0%" stop-color="#B9ACCF"/>
                                    <stop offset="100%" stop-color="#75628F"/>
                                </linearGradient>

                                <radialGradient id="luz-central">
                                    <stop offset="0%" stop-color="#FFFFFF" stop-opacity=".09"/>
                                    <stop offset="100%" stop-color="#FFFFFF" stop-opacity="0"/>
                                </radialGradient>

                            </defs>

                            <!-- ÓRBITAS -->

                            <circle
                                cx="250"
                                cy="250"
                                r="225"
                                fill="url(#luz-central)"
                            />

                            <circle
                                cx="250"
                                cy="250"
                                r="210"
                                stroke="white"
                                stroke-opacity=".07"
                            />

                            <circle
                                cx="250"
                                cy="250"
                                r="175"
                                stroke="white"
                                stroke-opacity=".12"
                                stroke-dasharray="5 12"
                                class="orbita"
                            />

                            <circle
                                cx="250"
                                cy="250"
                                r="140"
                                fill="white"
                                fill-opacity=".025"
                                stroke="white"
                                stroke-opacity=".1"
                            />

                            <!-- CÁPSULA PRINCIPAL -->

                            <g class="capsula-principal">

                                <g transform="rotate(38 250 250)">

                                    <rect
                                        x="190"
                                        y="95"
                                        width="120"
                                        height="310"
                                        rx="60"
                                        fill="url(#capsula-clara)"
                                    />

                                    <path
                                        d="M190 250H310V345A60 60 0 0 1 190 345Z"
                                        fill="url(#capsula-oscura)"
                                    />

                                    <rect
                                        x="190"
                                        y="95"
                                        width="120"
                                        height="310"
                                        rx="60"
                                        stroke="white"
                                        stroke-opacity=".7"
                                        stroke-width="2"
                                    />

                                    <path
                                        d="M190 250H310"
                                        stroke="white"
                                        stroke-opacity=".8"
                                        stroke-width="2"
                                    />

                                    <path
                                        d="M210 140V210"
                                        stroke="white"
                                        stroke-width="7"
                                        stroke-linecap="round"
                                        stroke-opacity=".8"
                                    />

                                </g>

                            </g>

                            <!-- FRASCO DE MEDICAMENTOS -->

                            <g class="elemento-flotante demora-uno">

                                <g transform="rotate(-12 80 290)">

                                    <rect
                                        x="45"
                                        y="250"
                                        width="70"
                                        height="100"
                                        rx="13"
                                        fill="white"
                                        fill-opacity=".07"
                                        stroke="white"
                                        stroke-opacity=".35"
                                        stroke-width="1.5"
                                    />

                                    <rect
                                        x="53"
                                        y="230"
                                        width="54"
                                        height="22"
                                        rx="5"
                                        fill="white"
                                        fill-opacity=".13"
                                        stroke="white"
                                        stroke-opacity=".35"
                                    />

                                    <rect
                                        x="45"
                                        y="278"
                                        width="70"
                                        height="45"
                                        fill="white"
                                        fill-opacity=".08"
                                    />

                                    <path
                                        d="M80 288V312M68 300H92"
                                        stroke="white"
                                        stroke-opacity=".55"
                                        stroke-width="2.5"
                                        stroke-linecap="round"
                                    />

                                </g>

                            </g>

                            <!-- CÁPSULA PEQUEÑA -->

                            <g class="elemento-flotante demora-dos">

                                <g transform="rotate(-35 410 115)">

                                    <rect
                                        x="391"
                                        y="75"
                                        width="38"
                                        height="80"
                                        rx="19"
                                        fill="white"
                                        fill-opacity=".07"
                                        stroke="white"
                                        stroke-opacity=".4"
                                        stroke-width="1.5"
                                    />

                                    <path
                                        d="M391 115H429"
                                        stroke="white"
                                        stroke-opacity=".5"
                                        stroke-width="1.5"
                                    />

                                </g>

                            </g>

                            <!-- COMPRIMIDO -->

                            <g class="elemento-flotante demora-tres">

                                <circle
                                    cx="405"
                                    cy="365"
                                    r="32"
                                    fill="white"
                                    fill-opacity=".06"
                                    stroke="white"
                                    stroke-opacity=".4"
                                    stroke-width="1.5"
                                />

                                <path
                                    d="M383 387L427 343"
                                    stroke="white"
                                    stroke-opacity=".4"
                                    stroke-width="2"
                                />

                            </g>

                            <!-- CRUCES SANITARIAS -->

                            <g class="elemento-flotante demora-cuatro">

                                <path
                                    d="M95 75V115M75 95H115"
                                    stroke="white"
                                    stroke-opacity=".5"
                                    stroke-width="3"
                                    stroke-linecap="round"
                                />

                            </g>

                            <g class="elemento-flotante demora-dos">

                                <path
                                    d="M300 420V450M285 435H315"
                                    stroke="white"
                                    stroke-opacity=".35"
                                    stroke-width="2"
                                    stroke-linecap="round"
                                />

                            </g>

                            <!-- PUNTOS DECORATIVOS -->

                            <g fill="#B9ACCF">

                                <circle cx="175" cy="45" r="4" opacity=".6"/>
                                <circle cx="450" cy="240" r="4" opacity=".5"/>
                                <circle cx="145" cy="420" r="3" opacity=".6"/>
                                <circle cx="45" cy="175" r="3" opacity=".5"/>
                                <circle cx="345" cy="45" r="3" opacity=".4"/>

                            </g>

                        </svg>

                        <div class="etiqueta-ilustracion">

                            <span></span>

                            FORMACIÓN Y SIMULACIÓN

                        </div>

                    </div>

                </div>

                <!-- INDICADOR DE DESPLAZAMIENTO -->

                <button
                    type="button"
                    class="indicador-scroll"
                    @click="irASeccion('entornos')"
                >

                    <span>Desliza para continuar</span>

                    <svg
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.7"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    >
                        <path d="M6 8L12 14L18 8M6 14L12 20L18 14"/>
                    </svg>

                </button>

            </section>

            <!-- =====================================
                 SECCIÓN 2: ENTORNOS DE TRABAJO
                 ===================================== -->

            <section id="entornos" class="seccion-entornos">

                <div class="contenedor">

                    <div class="titulo-seccion revelar">

                        <p class="sobre-titulo">
                            TU ESPACIO DE TRABAJO
                        </p>

                        <h2>
                            Selecciona tu entorno
                        </h2>

                        <p class="descripcion-seccion">
                            Dos entornos de simulación,
                            una plataforma integrada.
                        </p>

                    </div>

                    <div class="lista-entornos">

                        <Link
                            v-for="entorno in entornos"
                            :key="entorno.id"
                            :href="entorno.ruta"
                            :data-entorno="entorno.id"
                            class="acceso-entorno revelar"
                        >

                            <div class="icono-entorno">

                                <!-- FARMACIA PRIVADA -->

                                <svg
                                    v-if="entorno.id === 'privada'"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.6"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    aria-hidden="true"
                                >
                                    <path d="M3 9L5 3H19L21 9"/>
                                    <path d="M3 9V12A3 3 0 0 0 9 12A3 3 0 0 0 15 12A3 3 0 0 0 21 12V9Z"/>
                                    <path d="M5 15V21H19V15"/>
                                    <path d="M10 21V16H14V21"/>
                                </svg>

                                <!-- FARMACIA CESFAM -->

                                <svg
                                    v-else
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.6"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    aria-hidden="true"
                                >
                                    <path d="M20.8 4.6A5.5 5.5 0 0 0 13 4.6L12 5.7L11 4.6A5.5 5.5 0 0 0 3.2 12.4L12 21L20.8 12.4A5.5 5.5 0 0 0 20.8 4.6Z"/>
                                    <path d="M3 12H8L10 8L13 16L15 12H21"/>
                                </svg>

                            </div>

                            <div class="entorno-informacion">

                                <p class="entorno-categoria">
                                    {{ entorno.categoria }}
                                </p>

                                <h3>
                                    {{ entorno.nombre }}
                                </h3>

                                <p class="entorno-descripcion">
                                    {{ entorno.descripcion }}
                                </p>

                            </div>

                            <div class="entorno-accion">

                                <span>Ingresar</span>

                                <div class="flecha">

                                    <svg
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.8"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        aria-hidden="true"
                                    >
                                        <path d="M5 12H19M12 5L19 12L12 19"/>
                                    </svg>

                                </div>

                            </div>

                        </Link>

                    </div>

                    <p class="nota-entornos revelar">

                        Selecciona el entorno correspondiente
                        a tu actividad académica.

                    </p>

                </div>

            </section>

            <!-- =====================================
                 SECCIÓN 3: SOBRE FARMACONTROL
                 ===================================== -->

            <section id="nosotros" class="seccion-nosotros">

                <div class="contenedor nosotros-grid">

                    <div class="nosotros-texto revelar">

                        <p class="sobre-titulo">
                            SOBRE FARMACONTROL
                        </p>

                        <h2>
                            Tecnología al servicio
                            del aprendizaje.
                        </h2>

                        <p>

                            FarmaControl es un sistema web integrado
                            de gestión farmacéutica desarrollado
                            como proyecto académico.

                        </p>

                        <p>

                            Su propósito es acercar a los estudiantes
                            a procesos propios de una farmacia comercial
                            y asistencial, mediante un entorno de
                            simulación digital.

                        </p>

                        <button
                            type="button"
                            class="boton-volver-entornos"
                            @click="irASeccion('entornos')"
                        >

                            Comenzar a explorar

                            <svg
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            >
                                <path d="M5 12H19M12 5L19 12L12 19"/>
                            </svg>

                        </button>

                    </div>

                    <div class="caracteristicas revelar">

                        <!-- FORMACIÓN -->

                        <article class="caracteristica">

                            <div class="caracteristica-icono">

                                <svg
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.7"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    aria-hidden="true"
                                >
                                    <path d="M2 10L12 4L22 10L12 16Z"/>
                                    <path d="M6 13V18C9 21 15 21 18 18V13"/>
                                    <path d="M22 10V17"/>
                                </svg>

                            </div>

                            <div>

                                <h3>Formación académica</h3>

                                <p>
                                    Desarrollo de competencias mediante
                                    experiencias de simulación.
                                </p>

                            </div>

                        </article>

                        <!-- GESTIÓN -->

                        <article class="caracteristica">

                            <div class="caracteristica-icono">

                                <svg
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.7"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    aria-hidden="true"
                                >
                                    <rect
                                        x="7"
                                        y="2"
                                        width="10"
                                        height="20"
                                        rx="5"
                                        transform="rotate(40 12 12)"
                                    />
                                    <path d="M8 9L16 15"/>
                                </svg>

                            </div>

                            <div>

                                <h3>Gestión farmacéutica</h3>

                                <p>
                                    Procesos de inventario, medicamentos
                                    y atención farmacéutica simulada.
                                </p>

                            </div>

                        </article>

                        <!-- TECNOLOGÍA -->

                        <article class="caracteristica">

                            <div class="caracteristica-icono">

                                <svg
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.7"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    aria-hidden="true"
                                >
                                    <rect x="3" y="3" width="18" height="13" rx="2"/>
                                    <path d="M12 16V21M8 21H16"/>
                                    <path d="M7 10L10 13L16 7"/>
                                </svg>

                            </div>

                            <div>

                                <h3>Transformación digital</h3>

                                <p>
                                    Integración de herramientas
                                    tecnológicas en la formación profesional.
                                </p>

                            </div>

                        </article>

                    </div>

                </div>

            </section>

            <!-- =====================================
                 SECCIÓN 4: COMPROMISO ACADÉMICO
                 ===================================== -->

            <section class="seccion-academica">

                <div class="contenedor academico-contenido revelar">

                    <div class="escudo-academico">

                        <svg
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.5"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            aria-hidden="true"
                        >
                            <path d="M12 22S20 18 20 11V5L12 2L4 5V11C4 18 12 22 12 22Z"/>
                            <path d="M9 12L11 14L15 10"/>
                        </svg>

                    </div>

                    <div>

                        <p class="academico-etiqueta">
                            COMPROMISO ACADÉMICO
                        </p>

                        <h2>
                            Aprendizaje en un entorno de simulación.
                        </h2>

                        <p>

                            FarmaControl está destinado exclusivamente
                            a fines educativos. Utiliza únicamente
                            datos ficticios y sus documentos no poseen
                            validez legal, clínica ni tributaria.

                        </p>

                    </div>

                </div>

            </section>

        </main>

        <!-- =====================================
             PIE DE PÁGINA
             ===================================== -->

        <footer class="pie-pagina">

            <div class="contenedor">

                <div class="footer-principal">

                    <div>

                        <h2>FarmaControl</h2>

                        <p>
                            Sistema Web Integrado de Gestión Farmacéutica
                        </p>

                        <p class="footer-secundario">
                            Proyecto Integrado · INACAP
                        </p>

                    </div>

                    <div class="footer-enlaces">

                        <button
                            type="button"
                            @click="irASeccion('inicio')"
                        >
                            Inicio
                        </button>

                        <button
                            type="button"
                            @click="irASeccion('entornos')"
                        >
                            Entornos de trabajo
                        </button>

                        <button
                            type="button"
                            @click="irASeccion('nosotros')"
                        >
                            Sobre FarmaControl
                        </button>

                    </div>

                    <div class="footer-logo">

                        <img
                            v-if="logoDisponible"
                            :src="'/images/logo-inacap.png'"
                            alt="INACAP"
                            class="fc-logo-inacap"
                            @error="logoDisponible = false"
                        />

                        <span v-else class="logo-alternativo">
                            INACAP
                        </span>

                    </div>

                </div>

                <div class="footer-inferior">

                    <p>
                        © {{ new Date().getFullYear() }}
                        FarmaControl · Proyecto académico.
                    </p>

                    <p>
                        Tecnología al servicio del aprendizaje.
                    </p>

                </div>

            </div>

        </footer>

        <!-- BOTÓN VOLVER ARRIBA -->

        <Transition name="aparecer">

            <button
                v-if="desplazamiento > 500"
                type="button"
                class="volver-arriba"
                aria-label="Volver al inicio"
                @click="irASeccion('inicio')"
            >

                <svg
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                >
                    <path d="M12 19V5M5 12L12 5L19 12"/>
                </svg>

            </button>

        </Transition>

        <!-- MARCA DE AGUA -->

        <div class="fc-watermark">

            <p class="font-bold">
                Uso académico · INACAP
            </p>

            <p>
                Sistema de simulación · Sin validez legal
            </p>

        </div>

    </div>

</template>

<style scoped>

/* ==========================================
   CONFIGURACIÓN GENERAL
   ========================================== */

.inicio-page {
    min-height: 100vh;
    background: #F8FAFC;
    color: #1E293B;
}

.contenedor {
    width: 100%;
    max-width: 1240px;
    margin: 0 auto;
    padding: 0 40px;
}

section[id] {
    scroll-margin-top: 85px;
}

/* ==========================================
   ENCABEZADO
   ========================================== */

.encabezado {
    position: sticky;
    top: 0;
    z-index: 100;
    background: rgba(255, 255, 255, .97);
    border-bottom: 1px solid #E2E8F0;
    backdrop-filter: blur(18px);
    transition: box-shadow .3s ease;
}

.encabezado-scroll {
    box-shadow: 0 5px 25px rgba(15, 23, 42, .07);
}

.encabezado-contenido {
    max-width: 1440px;
    min-height: 80px;
    margin: auto;
    padding: 15px 40px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 25px;
}

.identidad {
    display: flex;
    align-items: center;
    gap: 12px;
    text-align: left;
}

.logo-farma {
    width: 44px;
    height: 44px;
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 12px;
    background: #172554;
    color: white;
}

.logo-farma svg {
    width: 25px;
    height: 25px;
}

.identidad h1 {
    color: #172554;
    font-size: 23px;
    font-weight: 800;
    letter-spacing: -.9px;
    line-height: 1.2;
}

.identidad p {
    margin-top: 4px;
    color: #64748B;
    font-size: 10px;
}

.navegacion {
    display: flex;
    align-items: center;
    gap: 30px;
}

.navegacion button {
    position: relative;
    padding: 8px 0;
    color: #475569;
    font-size: 13px;
    font-weight: 500;
}

.navegacion button::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 0;
    height: 2px;
    background: #75628F;
    transition: width .25s ease;
}

.navegacion button:hover {
    color: #43365C;
}

.navegacion button:hover::after {
    width: 100%;
}

.institucion {
    display: flex;
    align-items: center;
    gap: 15px;
}

.logo-alternativo {
    color: #222222;
    font-size: 22px;
    font-weight: 800;
    border-bottom: 3px solid #E30613;
}

.boton-menu {
    display: none;
    color: #172554;
}

.boton-menu svg {
    width: 26px;
    height: 26px;
}

/* ==========================================
   SECCIÓN PRINCIPAL
   ========================================== */

.hero {
    position: relative;
    isolation: isolate;
    min-height: calc(100svh - 80px);
    display: flex;
    flex-direction: column;
    justify-content: center;
    overflow: hidden;
    background: linear-gradient(
        125deg,
        #101A3B 0%,
        #172554 45%,
        #43365C 100%
    );
    color: white;
}

.patron-fondo {
    position: absolute;
    inset: 0;
    z-index: -1;
    pointer-events: none;
    background-image: radial-gradient(
        rgba(255, 255, 255, .15) 1px,
        transparent 1px
    );
    background-size: 35px 35px;
    mask-image: linear-gradient(
        to right,
        transparent,
        black
    );
    opacity: .5;
}

.brillo-uno {
    top: -100px;
    right: -100px;
    width: 500px;
    height: 500px;
    background: rgba(185, 172, 207, .12);
}

.brillo-dos {
    bottom: -180px;
    left: -100px;
    width: 500px;
    height: 500px;
    animation-delay: -6s;
}

.hero-contenido {
    position: relative;
    z-index: 1;
    width: 100%;
    max-width: 1320px;
    margin: auto;
    padding: 65px 60px 100px;
    display: grid;
    grid-template-columns: 1.15fr 1fr;
    align-items: center;
    gap: 45px;
}

.etiqueta-hero {
    display: inline-flex;
    align-items: center;
    gap: 9px;
    padding: 9px 14px;
    border: 1px solid rgba(255, 255, 255, .15);
    border-radius: 30px;
    background: rgba(255, 255, 255, .05);
    color: #D7DDF0;
    font-size: 10px;
    font-weight: 600;
    letter-spacing: 1.5px;
}

.punto-activo {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: #B9ACCF;
    animation: pulsar 3s infinite;
}

.hero-texto h2 {
    max-width: 650px;
    margin-top: 28px;
    font-size: clamp(40px, 4.8vw, 70px);
    font-weight: 800;
    line-height: 1.12;
    letter-spacing: -2.5px;
}

.hero-texto h2 span {
    display: block;
    color: #C6B8E0;
}

.hero-descripcion {
    max-width: 510px;
    margin-top: 25px;
    color: #D1D8EB;
    font-size: 16px;
    line-height: 1.9;
}

.hero-acciones {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 15px;
    margin-top: 35px;
}

.boton-explorar,
.boton-conocer {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 15px;
    min-height: 52px;
    padding: 15px 24px;
    border-radius: 10px;
    font-size: 13px;
    font-weight: 600;
    transition:
        transform .25s ease,
        background-color .25s ease,
        box-shadow .25s ease;
}

.boton-explorar {
    background: white;
    color: #172554;
}

.boton-explorar:hover {
    transform: translateY(-3px);
    background: #F0EBF7;
    box-shadow: 0 8px 25px rgba(0, 0, 0, .15);
}

.boton-explorar svg {
    width: 19px;
    height: 19px;
}

.boton-conocer {
    color: white;
    border: 1px solid rgba(255, 255, 255, .25);
    background: rgba(255, 255, 255, .04);
}

.boton-conocer:hover {
    background: rgba(255, 255, 255, .12);
}

.hero-institucional {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-top: 35px;
    color: #B9C8ED;
    font-size: 12px;
}

.hero-institucional svg {
    width: 21px;
    height: 21px;
}

/* ==========================================
   ILUSTRACIÓN Y ANIMACIONES
   ========================================== */

.hero-ilustracion {
    position: relative;
    transform: translateY(var(--parallax, 0px));
}

.ilustracion-principal {
    width: 100%;
    max-width: 520px;
    margin: auto;
}

.capsula-principal {
    transform-origin: center;
    transform-box: fill-box;
    animation: capsula-flotante 7s ease-in-out infinite;
    filter: drop-shadow(
        0 20px 20px rgba(0, 0, 0, .18)
    );
}

.elemento-flotante {
    animation: flotar 6s ease-in-out infinite;
}

.demora-uno {
    animation-delay: -1.5s;
}

.demora-dos {
    animation-delay: -3s;
}

.demora-tres {
    animation-delay: -4.5s;
}

.demora-cuatro {
    animation-delay: -2s;
}

.orbita {
    transform-origin: center;
    transform-box: fill-box;
    animation: girar 60s linear infinite;
}

.etiqueta-ilustracion {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 10px;
    color: #C6CDE3;
    font-size: 10px;
    letter-spacing: 2px;
}

.etiqueta-ilustracion span {
    width: 20px;
    height: 1px;
    background: #B9ACCF;
}

/* INDICADOR DE SCROLL */

.indicador-scroll {
    position: absolute;
    bottom: 25px;
    left: 50%;
    transform: translateX(-50%);
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 7px;
    color: #C6CDE3;
    font-size: 10px;
}

.indicador-scroll svg {
    width: 23px;
    height: 23px;
    animation: bajar 2s ease-in-out infinite;
}

/* ==========================================
   ENTORNOS DE TRABAJO
   ========================================== */

.seccion-entornos {
    padding: 100px 0;
    background: #F8FAFC;
}

.titulo-seccion {
    margin-bottom: 45px;
    text-align: center;
}

.sobre-titulo {
    color: #75628F;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 2px;
}

.titulo-seccion h2,
.nosotros-texto h2 {
    margin-top: 15px;
    color: #172554;
    font-size: clamp(30px, 3.5vw, 43px);
    font-weight: 800;
    line-height: 1.2;
    letter-spacing: -1.5px;
}

.descripcion-seccion {
    margin-top: 15px;
    color: #64748B;
    font-size: 15px;
    line-height: 1.8;
}

.lista-entornos {
    display: flex;
    flex-direction: column;
    gap: 22px;
    max-width: 950px;
    margin: auto;
}

.acceso-entorno {
    position: relative;
    display: flex;
    align-items: center;
    gap: 25px;
    padding: 28px 32px;
    border: 1px solid var(--fc-border);
    border-left: 5px solid var(--fc-primary);
    border-radius: 16px;
    background: var(--fc-light);
    color: var(--fc-primary);
    text-decoration: none;
    transition:
        transform .3s ease,
        box-shadow .3s ease,
        border-color .3s ease;
}

.acceso-entorno:hover {
    transform: translateY(-4px);
    border-color: var(--fc-accent);
    box-shadow: 0 15px 35px rgba(15, 23, 42, .08);
}

.icono-entorno {
    width: 76px;
    height: 76px;
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 17px;
    background: white;
    color: var(--fc-primary);
    transition:
        background-color .3s ease,
        color .3s ease;
}

.icono-entorno svg {
    width: 35px;
    height: 35px;
}

.acceso-entorno:hover .icono-entorno {
    background: var(--fc-primary);
    color: white;
}

.entorno-informacion {
    flex: 1;
    min-width: 0;
}

.entorno-categoria {
    color: var(--fc-accent);
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 1.4px;
}

.entorno-informacion h3 {
    margin-top: 7px;
    font-size: 23px;
    font-weight: 750;
    letter-spacing: -.6px;
}

.entorno-descripcion {
    max-width: 540px;
    margin-top: 8px;
    color: #64748B;
    font-size: 13px;
    line-height: 1.8;
}

.entorno-accion {
    display: flex;
    align-items: center;
    gap: 15px;
    font-size: 13px;
    font-weight: 600;
}

.flecha {
    width: 43px;
    height: 43px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    background: var(--fc-primary);
    color: white;
    transition: transform .3s ease;
}

.flecha svg {
    width: 20px;
    height: 20px;
}

.acceso-entorno:hover .flecha {
    transform: translateX(5px);
}

.nota-entornos {
    margin-top: 30px;
    text-align: center;
    color: #64748B;
    font-size: 12px;
}

/* ==========================================
   SOBRE FARMACONTROL
   ========================================== */

.seccion-nosotros {
    padding: 100px 0;
    background: white;
    border-top: 1px solid #E2E8F0;
}

.nosotros-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    align-items: center;
    gap: 80px;
}

.nosotros-texto > p:not(.sobre-titulo) {
    margin-top: 20px;
    color: #64748B;
    font-size: 14px;
    line-height: 1.9;
}

.boton-volver-entornos {
    display: inline-flex;
    align-items: center;
    gap: 12px;
    margin-top: 28px;
    padding: 14px 20px;
    border-radius: 10px;
    background: #172554;
    color: white;
    font-size: 13px;
    font-weight: 600;
}

.boton-volver-entornos:hover {
    background: #43365C;
}

.boton-volver-entornos svg {
    width: 18px;
    height: 18px;
}

.caracteristicas {
    display: flex;
    flex-direction: column;
}

.caracteristica {
    display: flex;
    align-items: center;
    gap: 20px;
    padding: 25px 0;
    border-bottom: 1px solid #E2E8F0;
}

.caracteristica:last-child {
    border-bottom: none;
}

.caracteristica-icono {
    width: 60px;
    height: 60px;
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 15px;
    background: #F0EBF7;
    color: #43365C;
}

.caracteristica-icono svg {
    width: 29px;
    height: 29px;
}

.caracteristica h3 {
    color: #172554;
    font-size: 17px;
    font-weight: 700;
}

.caracteristica p {
    margin-top: 7px;
    color: #64748B;
    font-size: 13px;
    line-height: 1.8;
}

/* ==========================================
   COMPROMISO ACADÉMICO
   ========================================== */

.seccion-academica {
    padding: 65px 0;
    background: linear-gradient(
        110deg,
        #43365C,
        #59466F
    );
    color: white;
}

.academico-contenido {
    display: flex;
    align-items: center;
    gap: 35px;
}

.escudo-academico {
    width: 90px;
    height: 90px;
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 1px solid rgba(255, 255, 255, .15);
    border-radius: 22px;
    background: rgba(255, 255, 255, .08);
}

.escudo-academico svg {
    width: 45px;
    height: 45px;
}

.academico-etiqueta {
    color: #DDD4E9;
    font-size: 10px;
    font-weight: 600;
    letter-spacing: 2px;
}

.academico-contenido h2 {
    margin-top: 10px;
    font-size: clamp(23px, 3vw, 32px);
    font-weight: 700;
    letter-spacing: -.7px;
}

.academico-contenido p:last-child {
    max-width: 850px;
    margin-top: 12px;
    color: #E3DDED;
    font-size: 13px;
    line-height: 1.9;
}

/* ==========================================
   PIE DE PÁGINA
   ========================================== */

.pie-pagina {
    padding: 55px 0 75px;
    background: #101A3B;
    color: white;
}

.footer-principal {
    display: grid;
    grid-template-columns: 1.5fr 1fr auto;
    align-items: center;
    gap: 40px;
}

.footer-principal h2 {
    font-size: 26px;
    font-weight: 800;
    letter-spacing: -.7px;
}

.footer-principal p {
    margin-top: 10px;
    color: #B9C8ED;
    font-size: 12px;
    line-height: 1.7;
}

.footer-principal .footer-secundario {
    color: #A5B4CF;
    font-size: 11px;
}

.footer-enlaces {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 13px;
}

.footer-enlaces button {
    color: #D1D8EB;
    font-size: 12px;
}

.footer-enlaces button:hover {
    color: white;
    text-decoration: underline;
}

.footer-logo {
    padding: 15px 20px;
    border-radius: 10px;
    background: white;
}

.footer-inferior {
    display: flex;
    justify-content: space-between;
    gap: 20px;
    margin-top: 35px;
    padding-top: 25px;
    border-top: 1px solid rgba(255, 255, 255, .12);
    color: #A5B4CF;
    font-size: 11px;
}

/* ==========================================
   BOTÓN VOLVER ARRIBA
   ========================================== */

.volver-arriba {
    position: fixed;
    bottom: 80px;
    right: 20px;
    z-index: 60;
    width: 43px;
    height: 43px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 12px;
    background: #172554;
    color: white;
    box-shadow: 0 5px 20px rgba(15, 23, 42, .2);
}

.volver-arriba:hover {
    background: #43365C;
}

.volver-arriba svg {
    width: 22px;
    height: 22px;
}

.aparecer-enter-active,
.aparecer-leave-active {
    transition:
        opacity .25s ease,
        transform .25s ease;
}

.aparecer-enter-from,
.aparecer-leave-to {
    opacity: 0;
    transform: translateY(10px);
}

/* ==========================================
   ANIMACIONES AL DESPLAZARSE
   ========================================== */

.animar-entrada {
    opacity: 0;
    translate: 0 25px;
    transition:
        opacity .7s ease,
        translate .7s ease,
        transform .3s ease,
        box-shadow .3s ease,
        border-color .3s ease;
}

.animar-entrada.visible {
    opacity: 1;
    translate: 0 0;
}

@keyframes capsula-flotante {
    0%, 100% {
        transform: translateY(0) rotate(-4deg);
    }

    50% {
        transform: translateY(-18px) rotate(4deg);
    }
}

@keyframes flotar {
    0%, 100% {
        transform: translateY(0);
    }

    50% {
        transform: translateY(-15px);
    }
}

@keyframes girar {
    from {
        transform: rotate(0);
    }

    to {
        transform: rotate(360deg);
    }
}

@keyframes bajar {
    0%, 100% {
        transform: translateY(0);
    }

    50% {
        transform: translateY(6px);
    }
}

@keyframes pulsar {
    0%, 100% {
        box-shadow: 0 0 0 0 rgba(185, 172, 207, .25);
    }

    50% {
        box-shadow: 0 0 0 6px rgba(185, 172, 207, .08);
    }
}

/* ==========================================
   PANTALLAS MEDIANAS
   ========================================== */

@media (max-width: 1000px) {

    .hero-contenido {
        padding: 60px 40px 100px;
        gap: 25px;
    }

    .hero-texto h2 {
        font-size: 48px;
    }

    .navegacion {
        gap: 18px;
    }

    .nosotros-grid {
        gap: 40px;
    }

}

/* ==========================================
   TABLETS Y CELULARES
   ========================================== */

@media (max-width: 768px) {

    .contenedor {
        padding: 0 22px;
    }

    .encabezado-contenido {
        min-height: 70px;
        padding: 12px 20px;
        gap: 12px;
    }

    .identidad h1 {
        font-size: 19px;
    }

    .identidad p {
        font-size: 9px;
    }

    .logo-farma {
        width: 38px;
        height: 38px;
    }

    .fc-logo-inacap {
        width: 85px;
    }

    .boton-menu {
        display: block;
    }

    .navegacion {
        display: none;
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        padding: 20px;
        background: white;
        border-bottom: 1px solid #E2E8F0;
        box-shadow: 0 10px 20px rgba(15, 23, 42, .06);
    }

    .navegacion.menu-abierto {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
    }

    .hero {
        min-height: calc(100svh - 70px);
    }

    .hero-contenido {
        grid-template-columns: 1fr;
        padding: 50px 25px 90px;
        gap: 25px;
        text-align: center;
    }

    .hero-texto h2 {
        margin-top: 23px;
        font-size: clamp(35px, 8vw, 50px);
        letter-spacing: -1.5px;
    }

    .hero-descripcion {
        margin: 20px auto 0;
        font-size: 14px;
    }

    .hero-acciones,
    .hero-institucional {
        justify-content: center;
    }

    .hero-ilustracion {
        transform: none;
    }

    .ilustracion-principal {
        max-width: 280px;
    }

    .seccion-entornos,
    .seccion-nosotros {
        padding: 65px 0;
    }

    .acceso-entorno {
        gap: 15px;
        padding: 22px 18px;
    }

    .icono-entorno {
        width: 55px;
        height: 55px;
        border-radius: 12px;
    }

    .icono-entorno svg {
        width: 27px;
        height: 27px;
    }

    .entorno-informacion h3 {
        font-size: 18px;
    }

    .entorno-descripcion {
        font-size: 12px;
    }

    .entorno-accion span {
        display: none;
    }

    .flecha {
        width: 35px;
        height: 35px;
    }

    .nosotros-grid {
        grid-template-columns: 1fr;
        gap: 25px;
    }

    .academico-contenido {
        align-items: flex-start;
        gap: 20px;
    }

    .escudo-academico {
        width: 60px;
        height: 60px;
        border-radius: 15px;
    }

    .escudo-academico svg {
        width: 30px;
        height: 30px;
    }

    .footer-principal {
        grid-template-columns: 1fr;
        gap: 25px;
    }

    .footer-logo {
        width: fit-content;
    }

    .footer-inferior {
        flex-direction: column;
        gap: 10px;
    }

}

/* CELULARES PEQUEÑOS */

@media (max-width: 400px) {

    .encabezado-contenido {
        padding: 12px;
    }

    .identidad {
        gap: 7px;
    }

    .identidad h1 {
        font-size: 16px;
    }

    .identidad p {
        display: none;
    }

    .institucion {
        gap: 8px;
    }

    .fc-logo-inacap {
        width: 70px;
    }

    .etiqueta-hero {
        font-size: 8px;
        letter-spacing: .8px;
    }

    .acceso-entorno {
        gap: 10px;
        padding: 18px 12px;
    }

    .icono-entorno {
        width: 43px;
        height: 43px;
    }

    .entorno-informacion h3 {
        font-size: 15px;
    }

    .entorno-categoria {
        font-size: 8px;
    }

    .flecha {
        width: 28px;
        height: 28px;
    }

}

/* ==========================================
   ACCESIBILIDAD
   ========================================== */

@media (prefers-reduced-motion: reduce) {

    .capsula-principal,
    .elemento-flotante,
    .orbita,
    .punto-activo,
    .indicador-scroll svg {
        animation: none;
    }

    .hero-ilustracion {
        transform: none;
    }

    .animar-entrada {
        opacity: 1;
        translate: none;
    }

    .acceso-entorno,
    .flecha,
    .boton-explorar,
    .boton-conocer {
        transition: none;
    }

    .acceso-entorno:hover,
    .acceso-entorno:hover .flecha,
    .boton-explorar:hover {
        transform: none;
    }

}

</style>
