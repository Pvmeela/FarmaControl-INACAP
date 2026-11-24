
<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import { ref, computed } from 'vue'

const props = defineProps({
    entorno: {
        type: String,
        default: 'privada',
    },
})

const mostrarPassword = ref(false)
const logoDisponible = ref(true)
const animacionesActivas = ref(true)

const entornoActual = computed(() =>
    props.entorno === 'cesfam' ? 'cesfam' : 'privada'
)

const nombreEntorno = computed(() =>
    entornoActual.value === 'cesfam'
        ? 'Farmacia Asistencial CESFAM'
        : 'Farmacia Privada'
)

const form = useForm({
    email: '',
    password: '',
})

const iniciarSesion = () => {
    if (form.processing) return

    form.post('/login', {
        onFinish: () => {
            form.reset('password')
            mostrarPassword.value = false
        },
    })
}
</script>

<template>

    <Head title="Iniciar sesión | FarmaControl" />

    <main
        class="login-page"
        :data-entorno="entornoActual"
        :class="{ 'sin-animaciones': !animacionesActivas }"
    >

        <div class="tarjeta-login">

            <!-- =====================================
                 PANEL IZQUIERDO ANIMADO
                 ===================================== -->

            <section class="panel-visual">

                <div class="circulo circulo-uno"></div>
                <div class="circulo circulo-dos"></div>
                <div class="brillo brillo-uno"></div>
                <div class="brillo brillo-dos"></div>

                <!-- DECORACIONES FARMACÉUTICAS -->

                <div class="decoraciones" aria-hidden="true">

                    <!-- FRASCO -->

                    <svg
                        class="elemento frasco"
                        viewBox="0 0 100 140"
                        fill="none"
                    >
                        <rect
                            x="20" y="28"
                            width="60" height="100"
                            rx="14"
                            fill="white"
                            fill-opacity=".10"
                            stroke="white"
                            stroke-opacity=".3"
                            stroke-width="2"
                        />

                        <rect
                            x="26" y="10"
                            width="48" height="23"
                            rx="5"
                            fill="white"
                            fill-opacity=".22"
                        />

                        <rect
                            x="20" y="58"
                            width="60" height="40"
                            fill="white"
                            fill-opacity=".08"
                        />

                        <path
                            d="M50 66V90M38 78H62"
                            stroke="white"
                            stroke-opacity=".4"
                            stroke-width="6"
                            stroke-linecap="round"
                        />
                    </svg>

                    <!-- CÁPSULA GRANDE -->

                    <svg
                        class="elemento capsula"
                        viewBox="0 0 80 150"
                        fill="none"
                    >
                        <rect
                            x="15" y="10"
                            width="50" height="130"
                            rx="25"
                            fill="white"
                            fill-opacity=".13"
                            stroke="white"
                            stroke-opacity=".5"
                            stroke-width="3"
                        />

                        <path
                            d="M15 75H65V115A25 25 0 0 1 15 115Z"
                            fill="white"
                            fill-opacity=".35"
                        />

                        <path
                            d="M15 75H65"
                            stroke="white"
                            stroke-opacity=".5"
                            stroke-width="2"
                        />
                    </svg>

                    <!-- CÁPSULA PEQUEÑA -->

                    <svg
                        class="elemento capsula-pequena"
                        viewBox="0 0 80 150"
                        fill="none"
                    >
                        <rect
                            x="15" y="10"
                            width="50" height="130"
                            rx="25"
                            fill="white"
                            fill-opacity=".06"
                            stroke="white"
                            stroke-opacity=".2"
                            stroke-width="3"
                        />

                        <path
                            d="M15 75H65"
                            stroke="white"
                            stroke-opacity=".3"
                            stroke-width="2"
                        />
                    </svg>

                    <!-- CRUCES FARMACÉUTICAS -->

                    <svg
                        class="elemento cruz cruz-uno"
                        viewBox="0 0 40 40"
                    >
                        <path
                            d="M15 3H25V15H37V25H25V37H15V25H3V15H15Z"
                            fill="white"
                            fill-opacity=".18"
                        />
                    </svg>

                    <svg
                        class="elemento cruz cruz-dos"
                        viewBox="0 0 40 40"
                    >
                        <path
                            d="M15 3H25V15H37V25H25V37H15V25H3V15H15Z"
                            fill="white"
                            fill-opacity=".15"
                        />
                    </svg>

                    <svg
                        class="elemento cruz cruz-tres"
                        viewBox="0 0 40 40"
                    >
                        <path
                            d="M15 3H25V15H37V25H25V37H15V25H3V15H15Z"
                            fill="white"
                            fill-opacity=".10"
                        />
                    </svg>

                    <!-- COMPRIMIDO -->

                    <svg
                        class="elemento comprimido"
                        viewBox="0 0 80 80"
                        fill="none"
                    >
                        <circle
                            cx="40" cy="40" r="33"
                            fill="white"
                            fill-opacity=".06"
                            stroke="white"
                            stroke-opacity=".18"
                            stroke-width="2"
                        />

                        <path
                            d="M17 63L63 17"
                            stroke="white"
                            stroke-opacity=".2"
                            stroke-width="3"
                        />
                    </svg>

                </div>

                <!-- IDENTIDAD FARMACONTROL -->

                <Link href="/" class="marca-farma">

                    <div class="marca-icono">

                        <svg
                            viewBox="0 0 24 24"
                            fill="currentColor"
                            aria-hidden="true"
                        >
                            <path
                                d="M9 3H15V9H21V15H15V21H9V15H3V9H9Z"
                            />
                        </svg>

                    </div>

                    <span>FarmaControl</span>

                </Link>

                <!-- MENSAJE PRINCIPAL -->

                <div class="contenido-visual">

                    <p class="sobre-titulo">
                        SISTEMA INTEGRADO DE GESTIÓN
                    </p>

                    <h1>
                        La farmacia del futuro
                        <span>comienza aquí.</span>
                    </h1>

                    <p class="descripcion-visual">
                        Aprende, gestiona y transforma la atención
                        farmacéutica en un entorno de simulación
                        profesional.
                    </p>

                    <div class="etiqueta">

                        <svg
                            viewBox="0 0 24 24"
                            fill="currentColor"
                            aria-hidden="true"
                        >
                            <path
                                d="M12 2L14.8 9.2L22 12L14.8 14.8L12 22L9.2 14.8L2 12L9.2 9.2Z"
                            />
                        </svg>

                        Innovación y aprendizaje

                    </div>

                </div>

                <!-- INFORMACIÓN INFERIOR -->

                <div class="inferior-visual">

                    <p>Proyecto Integrado · INACAP</p>

                    <button
                        type="button"
                        class="control-animacion"
                        :aria-label="animacionesActivas
                            ? 'Pausar animaciones'
                            : 'Activar animaciones'"
                        :title="animacionesActivas
                            ? 'Pausar animaciones'
                            : 'Activar animaciones'"
                        :aria-pressed="!animacionesActivas"
                        @click="animacionesActivas = !animacionesActivas"
                    >

                        <svg
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            aria-hidden="true"
                        >
                            <path
                                v-if="animacionesActivas"
                                d="M9 5V19M15 5V19"
                            />

                            <path
                                v-else
                                d="M8 5L19 12L8 19Z"
                            />
                        </svg>

                    </button>

                </div>

            </section>

            <!-- =====================================
                 PANEL DERECHO: INICIO DE SESIÓN
                 ===================================== -->

            <section class="panel-login">

                <div class="contenido-login">

                    <!-- LOGO INACAP -->

                    <div class="logo-institucional">

                        <img
                            v-if="logoDisponible"
                            :src="'/images/logo-inacap.png'"
                            alt="INACAP"
                            @error="logoDisponible = false"
                        />

                        <span
                            v-else
                            class="logo-alternativo"
                        >
                            INACAP
                        </span>

                    </div>

                    <!-- ENTORNO SELECCIONADO -->

                    <div class="entorno">

                        <span class="punto-entorno"></span>

                        {{ nombreEntorno }}

                    </div>

                    <!-- BIENVENIDA -->

                    <div class="bienvenida">

                        <h2>¡Bienvenido!</h2>

                        <p>
                            Ingresa tus credenciales para continuar.
                        </p>

                    </div>

                    <!-- FORMULARIO -->

                    <form
                        class="formulario"
                        @submit.prevent="iniciarSesion"
                    >

                        <!-- CORREO ELECTRÓNICO -->

                        <div class="campo">

                            <label for="email">
                                Correo electrónico
                            </label>

                            <div
                                class="input-contenedor"
                                :class="{ 'input-error': form.errors.email }"
                            >

                                <svg
                                    class="input-icono"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.7"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    aria-hidden="true"
                                >
                                    <rect
                                        x="3" y="5"
                                        width="18" height="14"
                                        rx="2"
                                    />

                                    <path d="M3 7L12 13L21 7"/>
                                </svg>

                                <input
                                    id="email"
                                    v-model="form.email"
                                    name="email"
                                    type="email"
                                    autocomplete="email"
                                    placeholder="usuario@farmacontrol.test"
                                    :disabled="form.processing"
                                    :aria-invalid="!!form.errors.email"
                                    :aria-describedby="form.errors.email
                                        ? 'error-email'
                                        : undefined"
                                    required
                                />

                            </div>

                            <p
                                v-if="form.errors.email"
                                id="error-email"
                                class="mensaje-error"
                                role="alert"
                            >
                                {{ form.errors.email }}
                            </p>

                        </div>

                        <!-- CONTRASEÑA -->

                        <div class="campo">

                            <label for="password">
                                Contraseña
                            </label>

                            <div
                                class="input-contenedor"
                                :class="{ 'input-error': form.errors.password }"
                            >

                                <svg
                                    class="input-icono"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.7"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    aria-hidden="true"
                                >
                                    <rect
                                        x="5" y="10"
                                        width="14" height="11"
                                        rx="2"
                                    />

                                    <path d="M8 10V7A4 4 0 0 1 16 7V10"/>
                                    <path d="M12 14V17"/>
                                </svg>

                                <input
                                    id="password"
                                    v-model="form.password"
                                    name="password"
                                    :type="mostrarPassword ? 'text' : 'password'"
                                    autocomplete="current-password"
                                    placeholder="Ingresa tu contraseña"
                                    :disabled="form.processing"
                                    :aria-invalid="!!form.errors.password"
                                    :aria-describedby="form.errors.password
                                        ? 'error-password'
                                        : undefined"
                                    required
                                />

                                <button
                                    type="button"
                                    class="boton-password"
                                    :aria-label="mostrarPassword
                                        ? 'Ocultar contraseña'
                                        : 'Mostrar contraseña'"
                                    :aria-pressed="mostrarPassword"
                                    :disabled="form.processing"
                                    @click="mostrarPassword = !mostrarPassword"
                                >

                                    <svg
                                        v-if="!mostrarPassword"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.7"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        aria-hidden="true"
                                    >
                                        <path
                                            d="M2 12S6 5 12 5S22 12 22 12S18 19 12 19S2 12 2 12Z"
                                        />

                                        <circle cx="12" cy="12" r="3"/>
                                    </svg>

                                    <svg
                                        v-else
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.7"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        aria-hidden="true"
                                    >
                                        <path d="M3 3L21 21"/>

                                        <path
                                            d="M10 5.2A11 11 0 0 1 12 5C18 5 22 12 22 12A18 18 0 0 1 19 15.5"
                                        />

                                        <path
                                            d="M6.5 6.5C3.5 9 2 12 2 12S6 19 12 19A10 10 0 0 0 16.5 18"
                                        />
                                    </svg>

                                </button>

                            </div>

                            <p
                                v-if="form.errors.password"
                                id="error-password"
                                class="mensaje-error"
                                role="alert"
                            >
                                {{ form.errors.password }}
                            </p>

                        </div>

                        <!-- BOTÓN INICIAR SESIÓN -->

                        <button
                            type="submit"
                            class="boton-ingresar"
                            :disabled="form.processing"
                            :aria-busy="form.processing"
                        >

                            <span
                                v-if="form.processing"
                                class="spinner"
                                aria-hidden="true"
                            ></span>

                            <span aria-live="polite">
                                {{ form.processing
                                    ? 'Iniciando sesión...'
                                    : 'Iniciar sesión' }}
                            </span>

                            <svg
                                v-if="!form.processing"
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

                        </button>

                    </form>

                    <!-- CAMBIAR ENTORNO -->

                    <Link href="/" class="cambiar-entorno">

                        <svg
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            aria-hidden="true"
                        >
                            <path d="M19 12H5M12 5L5 12L12 19"/>
                        </svg>

                        Cambiar entorno de trabajo

                    </Link>

                    <!-- MARCA ACADÉMICA -->

                    <div class="marca-academica">

                        <p>Uso académico · INACAP</p>

                        <p>
                            Sistema de simulación · Sin validez legal
                        </p>

                    </div>

                </div>

            </section>

        </div>

        <!-- MARCA DE AGUA EXTERIOR -->

        <div class="marca-agua">

            <span>FarmaControl</span>

            <span>Uso académico · INACAP</span>

        </div>

    </main>

</template>

<style scoped>

/* ==========================================
   PALETA FARMACIA PRIVADA
   ========================================== */

.login-page[data-entorno="privada"] {
    --fc-primary: #172554;
    --fc-accent: #1D4ED8;
    --fc-hover: #1E40AF;
    --fc-light: #EFF6FF;
    --fc-border: #DBEAFE;
    --fc-background: #F1F5F9;
}

/* ==========================================
   PALETA CESFAM
   ========================================== */

.login-page[data-entorno="cesfam"] {
    --fc-primary: #333344;
    --fc-accent: #665579;
    --fc-hover: #51435F;
    --fc-light: #E3D5F5;
    --fc-border: #E6E0EF;
    --fc-background: #F8F7FC;
}

/* ==========================================
   PANTALLA FIJA Y FONDO EXTERIOR
   ========================================== */

.login-page {
    position: fixed;
    inset: 0;

    display: flex;
    align-items: center;
    justify-content: center;

    width: 100%;
    height: 100vh;
    height: 100dvh;

    padding: 32px;

    overflow: hidden;

    background:
        radial-gradient(
            ellipse at 10% 10%,
            var(--fc-light),
            transparent 55%
        ),
        var(--fc-background);

    font-family: 'Inter', sans-serif;
}

.login-page * {
    box-sizing: border-box;
}

/* ==========================================
   TARJETA CENTRAL
   ========================================== */

.tarjeta-login {
    position: relative;

    display: grid;

    grid-template-columns:
        minmax(0, 1fr)
        minmax(0, 1fr);

    grid-template-rows: minmax(0, 1fr);

    width: 100%;
    max-width: 1050px;

    height: 620px;
    max-height: calc(100dvh - 80px);

    border: 1px solid rgba(148, 163, 184, .16);
    border-radius: 24px;

    background: #FFFFFF;

    overflow: hidden;

    box-shadow:
        0 25px 65px rgba(15, 23, 42, .13),
        0 5px 15px rgba(15, 23, 42, .04);
}

/* ==========================================
   PANEL IZQUIERDO
   ========================================== */

.panel-visual {
    position: relative;
    isolation: isolate;

    display: flex;
    flex-direction: column;
    justify-content: space-between;

    min-width: 0;
    min-height: 0;

    padding: 36px 42px 28px;

    overflow-x: hidden;
    overflow-y: auto;

    background: linear-gradient(
        155deg,
        var(--fc-primary) 0%,
        var(--fc-primary) 20%,
        var(--fc-accent) 100%
    );

    color: #FFFFFF;
}

/* ==========================================
   CÍRCULOS DECORATIVOS
   ========================================== */

.circulo {
    position: absolute;

    border: 1px solid rgba(255, 255, 255, .10);
    border-radius: 50%;

    pointer-events: none;
    z-index: -1;
}

.circulo-uno {
    width: 400px;
    height: 400px;

    top: -130px;
    right: -160px;
}

.circulo-dos {
    width: 470px;
    height: 470px;

    bottom: -260px;
    left: -190px;
}

.brillo {
    position: absolute;

    width: 280px;
    height: 280px;

    border-radius: 50%;

    background: #FFFFFF;

    filter: blur(90px);
    opacity: .06;

    pointer-events: none;
    z-index: -1;
}

.brillo-uno {
    top: -80px;
    right: -80px;
}

.brillo-dos {
    bottom: -80px;
    left: -80px;
}

/* ==========================================
   ELEMENTOS FARMACÉUTICOS ANIMADOS
   ========================================== */

.decoraciones {
    position: absolute;
    inset: 0;

    overflow: hidden;
    pointer-events: none;

    z-index: -1;
}

.elemento {
    position: absolute;

    animation: flotar 8s ease-in-out infinite;
}

.frasco {
    width: 90px;

    top: 12%;
    right: 9%;

    --rotacion: -9deg;

    animation-delay: -1s;
}

.capsula {
    width: 82px;

    bottom: 9%;
    right: 8%;

    --rotacion: 35deg;

    animation-delay: -3s;
}

.capsula-pequena {
    width: 42px;

    top: 40%;
    right: 5%;

    --rotacion: -35deg;

    animation-delay: -5s;
}

.cruz-uno {
    width: 38px;

    top: 29%;
    right: 18%;

    --rotacion: 10deg;

    animation-delay: -2s;
}

.cruz-dos {
    width: 28px;

    bottom: 18%;
    left: 7%;

    --rotacion: 15deg;

    animation-delay: -4s;
}

.cruz-tres {
    width: 20px;

    top: 19%;
    left: 43%;

    --rotacion: -15deg;

    animation-delay: -6s;
}

.comprimido {
    width: 60px;

    bottom: 25%;
    right: 27%;

    --rotacion: 0deg;

    animation-delay: -2.5s;
}

/* ==========================================
   IDENTIDAD FARMACONTROL
   ========================================== */

.marca-farma {
    display: flex;
    align-items: center;

    gap: 10px;

    width: fit-content;

    color: #FFFFFF;
    text-decoration: none;

    flex-shrink: 0;
}

.marca-icono {
    display: flex;
    align-items: center;
    justify-content: center;

    width: 40px;
    height: 40px;

    flex-shrink: 0;

    border-radius: 11px;

    background: rgba(255, 255, 255, .13);
}

.marca-icono svg {
    width: 23px;
    height: 23px;
}

.marca-farma span {
    font-size: 23px;
    font-weight: 800;
    letter-spacing: -.7px;
}

/* ==========================================
   CONTENIDO PRINCIPAL IZQUIERDO
   ========================================== */

.contenido-visual {
    width: 100%;
    max-width: 450px;

    margin: 25px 0;
}

.sobre-titulo {
    margin-bottom: 20px;

    color: rgba(255, 255, 255, .78);

    font-size: 10px;
    font-weight: 600;

    letter-spacing: 1.8px;
}

.contenido-visual h1 {
    margin: 0;

    color: #FFFFFF;

    font-size: clamp(30px, 3.5vw, 46px);
    font-weight: 700;

    line-height: 1.15;
    letter-spacing: -1.5px;
}

.contenido-visual h1 span {
    display: block;

    color: #FFFFFF;
}

.descripcion-visual {
    max-width: 370px;

    margin-top: 20px;

    color: rgba(255, 255, 255, .85);

    font-size: 14px;
    line-height: 1.8;
}

.etiqueta {
    display: inline-flex;
    align-items: center;

    gap: 8px;

    margin-top: 20px;
    padding: 9px 13px;

    border: 1px solid rgba(255, 255, 255, .25);
    border-radius: 30px;

    background: rgba(255, 255, 255, .07);

    color: #FFFFFF;

    font-size: 11px;
    font-weight: 500;
}

.etiqueta svg {
    width: 13px;
    height: 13px;

    flex-shrink: 0;
}

/* ==========================================
   INFORMACIÓN INFERIOR IZQUIERDA
   ========================================== */

.inferior-visual {
    display: flex;
    align-items: center;
    justify-content: space-between;

    gap: 12px;

    flex-shrink: 0;
}

.inferior-visual p {
    color: rgba(255, 255, 255, .75);

    font-size: 11px;
    font-weight: 500;
}

.control-animacion {
    display: flex;
    align-items: center;
    justify-content: center;

    width: 32px;
    height: 32px;

    flex-shrink: 0;

    border: 1px solid rgba(255, 255, 255, .2);
    border-radius: 8px;

    color: rgba(255, 255, 255, .85);

    background: rgba(255, 255, 255, .05);

    cursor: pointer;
}

.control-animacion:hover {
    background: rgba(255, 255, 255, .15);
}

.control-animacion svg {
    width: 15px;
    height: 15px;
}

/* ==========================================
   PANEL DERECHO
   ========================================== */

.panel-login {
    display: flex;
    flex-direction: column;
    align-items: center;

    min-width: 0;
    min-height: 0;

    padding: 28px 42px;

    overflow-x: hidden;
    overflow-y: auto;

    background: #FFFFFF;
}

.contenido-login {
    width: 100%;
    max-width: 380px;

    flex-shrink: 0;

    margin: auto 0;
}

/* ==========================================
   LOGO INACAP
   ========================================== */

.logo-institucional {
    display: flex;
    align-items: center;
    justify-content: flex-end;

    min-height: 40px;

    margin-bottom: 16px;
}

.logo-institucional img {
    display: block;

    width: 140px;
    max-width: 100%;
    max-height: 48px;

    object-fit: contain;
}

.logo-alternativo {
    color: #222222;

    font-size: 28px;
    font-weight: 800;
    font-style: italic;

    border-bottom: 4px solid #E30613;
}

/* ==========================================
   IDENTIFICACIÓN DEL ENTORNO
   ========================================== */

.entorno {
    display: inline-flex;
    align-items: center;

    gap: 7px;

    padding: 8px 11px;

    border: 1px solid var(--fc-border);
    border-radius: 7px;

    background: var(--fc-light);

    color: var(--fc-accent);

    font-size: 10px;
    font-weight: 700;

    text-transform: uppercase;
    letter-spacing: .7px;
}

.punto-entorno {
    width: 6px;
    height: 6px;

    flex-shrink: 0;

    border-radius: 50%;

    background: var(--fc-accent);
}

/* ==========================================
   BIENVENIDA
   ========================================== */

.bienvenida {
    margin-top: 18px;
}

.bienvenida h2 {
    margin: 0;

    color: #172033;

    font-size: 36px;
    font-weight: 700;

    line-height: 1.2;
    letter-spacing: -1.3px;
}

.bienvenida p {
    margin-top: 9px;

    color: #64748B;

    font-size: 13px;
    line-height: 1.6;
}

/* ==========================================
   FORMULARIO
   ========================================== */

.formulario {
    display: flex;
    flex-direction: column;

    gap: 17px;

    margin-top: 25px;
}

.campo {
    min-width: 0;
}

.campo label {
    display: block;

    margin-bottom: 8px;

    color: #1E293B;

    font-size: 13px;
    font-weight: 600;
}

.input-contenedor {
    display: flex;
    align-items: center;

    gap: 10px;

    min-height: 49px;

    padding: 0 13px;

    border: 1px solid #CBD5E1;
    border-radius: 9px;

    background: #FAFBFC;

    transition:
        border-color .2s ease,
        box-shadow .2s ease,
        background-color .2s ease;
}

.input-contenedor:focus-within {
    border-color: var(--fc-accent);

    background: #FFFFFF;

    box-shadow: 0 0 0 3px var(--fc-light);
}

.input-icono {
    width: 17px;
    height: 17px;

    flex-shrink: 0;

    color: #94A3B8;
}

.input-contenedor input {
    width: 100%;
    min-width: 0;

    padding: 12px 0;

    border: none;
    outline: none;

    background: transparent;

    color: #334155;

    font-size: 13px;
}

.input-contenedor input:focus {
    outline: none;
    box-shadow: none;
}

.input-contenedor input::placeholder {
    color: #94A3B8;
}

.input-contenedor input:disabled {
    opacity: .6;
}

/* ==========================================
   MOSTRAR Y OCULTAR CONTRASEÑA
   ========================================== */

.boton-password {
    display: flex;
    align-items: center;
    justify-content: center;

    width: 29px;
    height: 29px;

    flex-shrink: 0;

    border: none;
    border-radius: 6px;

    background: transparent;

    color: #64748B;

    cursor: pointer;
}

.boton-password:hover:not(:disabled) {
    background: var(--fc-light);
    color: var(--fc-primary);
}

.boton-password:disabled {
    cursor: not-allowed;
    opacity: .6;
}

.boton-password svg {
    width: 18px;
    height: 18px;
}

/* ==========================================
   ERRORES DE AUTENTICACIÓN
   ========================================== */

.input-contenedor.input-error {
    border-color: #DC2626;
    background: #FFFAFA;
}

.input-contenedor.input-error:focus-within {
    box-shadow: 0 0 0 3px #FEE2E2;
}

.mensaje-error {
    margin-top: 7px;

    color: #B91C1C;

    font-size: 12px;
    line-height: 1.5;

    overflow-wrap: anywhere;
}

/* ==========================================
   BOTÓN INICIAR SESIÓN
   ========================================== */

.boton-ingresar {
    display: flex;
    align-items: center;
    justify-content: center;

    gap: 10px;

    width: 100%;
    min-height: 49px;

    margin-top: 3px;
    padding: 13px 18px;

    border: none;
    border-radius: 9px;

    background: var(--fc-primary);

    color: #FFFFFF;

    font-size: 14px;
    font-weight: 600;

    cursor: pointer;

    transition:
        background-color .25s ease,
        box-shadow .25s ease;
}

.boton-ingresar:hover:not(:disabled) {
    background: var(--fc-hover);

    box-shadow: 0 5px 18px rgba(15, 23, 42, .12);
}

.boton-ingresar:disabled {
    opacity: .7;
    cursor: wait;
}

.boton-ingresar svg {
    width: 17px;
    height: 17px;

    flex-shrink: 0;

    transition: transform .2s ease;
}

.boton-ingresar:hover:not(:disabled) svg {
    transform: translateX(4px);
}

.spinner {
    width: 17px;
    height: 17px;

    flex-shrink: 0;

    border: 2px solid rgba(255, 255, 255, .3);
    border-top-color: #FFFFFF;
    border-radius: 50%;

    animation: girar .8s linear infinite;
}

/* ==========================================
   CAMBIAR ENTORNO
   ========================================== */

.cambiar-entorno {
    display: flex;
    align-items: center;
    justify-content: center;

    gap: 7px;

    width: fit-content;

    margin: 19px auto 0;

    color: var(--fc-accent);

    font-size: 12px;
    font-weight: 500;

    text-decoration: none;
}

.cambiar-entorno svg {
    width: 15px;
    height: 15px;

    flex-shrink: 0;

    transition: transform .2s ease;
}

.cambiar-entorno:hover {
    color: var(--fc-primary);
}

.cambiar-entorno:hover svg {
    transform: translateX(-4px);
}

/* ==========================================
   MARCA ACADÉMICA INTERIOR
   ========================================== */

.marca-academica {
    margin-top: 23px;
    padding-top: 13px;

    border-top: 1px solid #E2E8F0;

    text-align: center;

    color: #64748B;

    font-size: 10px;
    line-height: 1.8;
}

.marca-academica p {
    margin: 0;
}

.marca-academica p:first-child {
    font-weight: 600;
}

/* ==========================================
   MARCA DE AGUA EXTERIOR
   ========================================== */

.marca-agua {
    position: fixed;

    right: 22px;
    bottom: 10px;

    display: flex;
    align-items: flex-end;
    flex-direction: column;

    color: #64748B;

    font-size: 10px;
    line-height: 1.4;

    pointer-events: none;
    user-select: none;
}

.marca-agua span:first-child {
    font-size: 11px;
    font-weight: 700;
}

/* ==========================================
   ANIMACIONES
   ========================================== */

@keyframes flotar {

    0%, 100% {
        transform:
            translateY(0)
            rotate(var(--rotacion, 0deg));
    }

    50% {
        transform:
            translateY(-14px)
            rotate(calc(var(--rotacion, 0deg) + 5deg));
    }

}

@keyframes girar {

    from {
        transform: rotate(0deg);
    }

    to {
        transform: rotate(360deg);
    }

}

.sin-animaciones .elemento {
    animation-play-state: paused;
}

/* ==========================================
   ACCESIBILIDAD DEL TECLADO
   ========================================== */

button:focus-visible,
a:focus-visible {
    outline: 3px solid var(--fc-accent);
    outline-offset: 4px;
}

.panel-visual button:focus-visible,
.panel-visual a:focus-visible {
    outline-color: #FFFFFF;
}

/* ==========================================
   COMPUTADORES PORTÁTILES
   ========================================== */

@media (max-height: 760px) {

    .tarjeta-login {
        max-height: calc(100dvh - 70px);
    }

    .panel-visual {
        padding: 27px 34px 23px;
    }

    .panel-login {
        padding: 22px 34px;
    }

    .contenido-visual {
        margin: 18px 0;
    }

    .contenido-visual h1 {
        font-size: clamp(28px, 3.3vw, 40px);
    }

    .sobre-titulo {
        margin-bottom: 15px;
    }

    .descripcion-visual {
        margin-top: 15px;
        font-size: 13px;
    }

    .etiqueta {
        margin-top: 15px;
    }

    .logo-institucional {
        min-height: 35px;
        margin-bottom: 12px;
    }

    .logo-institucional img {
        width: 125px;
        max-height: 40px;
    }

    .bienvenida {
        margin-top: 14px;
    }

    .bienvenida h2 {
        font-size: 32px;
    }

    .formulario {
        margin-top: 20px;
        gap: 14px;
    }

    .input-contenedor {
        min-height: 45px;
    }

    .input-contenedor input {
        padding: 10px 0;
    }

    .boton-ingresar {
        min-height: 46px;
        padding: 11px 15px;
    }

    .cambiar-entorno {
        margin-top: 15px;
    }

    .marca-academica {
        margin-top: 17px;
        padding-top: 10px;
    }
}

/* ==========================================
   PANTALLAS MEDIANAS
   ========================================== */

@media (max-width: 1000px) {

    .login-page {
        padding: 24px;
    }

    .panel-visual {
        padding-left: 28px;
        padding-right: 28px;
    }

    .panel-login {
        padding-left: 28px;
        padding-right: 28px;
    }

    .contenido-visual h1 {
        font-size: clamp(26px, 3.8vw, 40px);
    }
}

/* ==========================================
   PANTALLAS DE POCA ALTURA
   ========================================== */

@media (max-height: 580px) {

    .login-page {
        padding: 15px;
    }

    .tarjeta-login {
        max-height: calc(100dvh - 45px);
        border-radius: 16px;
    }

    .panel-visual {
        padding-top: 18px;
        padding-bottom: 18px;
    }

    .panel-login {
        padding-top: 18px;
        padding-bottom: 18px;
    }

    .contenido-visual h1 {
        font-size: 30px;
    }

    .descripcion-visual {
        font-size: 12px;
    }

    .bienvenida h2 {
        font-size: 28px;
    }

    .formulario {
        gap: 12px;
        margin-top: 16px;
    }

    .marca-agua {
        right: 12px;
        bottom: 3px;
        font-size: 9px;
    }

    .marca-agua span:first-child {
        display: none;
    }
}

/* ==========================================
   TABLETS Y PANTALLAS ESTRECHAS
   CONSERVAR LOS DOS PANELES LATERALES
   ========================================== */

@media (max-width: 768px) {

    .login-page {
        padding: 12px;
    }

    .tarjeta-login {
        grid-template-columns:
            minmax(0, 1fr)
            minmax(0, 1fr);

        max-height: calc(100dvh - 60px);

        border-radius: 16px;
    }

    .panel-visual {
        padding: 22px 16px;
    }

    .panel-login {
        padding: 22px 16px;
    }

    .marca-farma {
        gap: 6px;
    }

    .marca-farma span {
        font-size: clamp(12px, 2.8vw, 20px);
    }

    .marca-icono {
        width: 29px;
        height: 29px;
    }

    .marca-icono svg {
        width: 18px;
        height: 18px;
    }

    .contenido-visual h1 {
        font-size: clamp(20px, 4vw, 32px);

        letter-spacing: -.8px;
        overflow-wrap: anywhere;
    }

    .sobre-titulo {
        font-size: 9px;
        letter-spacing: .8px;
    }

    .descripcion-visual {
        font-size: 11px;
        line-height: 1.6;
    }

    .etiqueta {
        padding: 7px 9px;
        font-size: 9px;
    }

    .inferior-visual p {
        font-size: 9px;
    }

    .logo-institucional img {
        width: 110px;
    }

    .entorno {
        font-size: 9px;
        letter-spacing: .3px;
        padding: 7px 8px;
    }

    .bienvenida h2 {
        font-size: clamp(21px, 4vw, 30px);
    }

    .bienvenida p {
        font-size: 12px;
    }

    .campo label {
        font-size: 12px;
    }

    .input-contenedor {
        padding: 0 8px;
        gap: 5px;
    }

    .input-contenedor input {
        font-size: 16px;
    }

    .input-icono {
        display: none;
    }

    .boton-ingresar {
        padding: 10px 8px;
        font-size: 12px;
    }

    .boton-ingresar svg {
        display: none;
    }

    .cambiar-entorno {
        font-size: 11px;
    }

    .marca-academica {
        font-size: 9px;
    }

    .marca-agua {
        right: 12px;
        bottom: 8px;
        font-size: 9px;
    }
}

/* ==========================================
   PANTALLAS MUY ESTRECHAS
   ========================================== */

@media (max-width: 480px) {

    .tarjeta-login {
        grid-template-columns:
            minmax(0, 36fr)
            minmax(0, 64fr);
    }

    .panel-visual {
        padding: 16px 10px;
    }

    .panel-login {
        padding: 18px 12px;
    }

    .marca-farma {
        flex-direction: column;
        align-items: flex-start;
    }

    .marca-farma span {
        font-size: 12px;
    }

    .contenido-visual h1 {
        font-size: 20px;
    }

    .sobre-titulo {
        font-size: 8px;
    }

    .descripcion-visual {
        font-size: 11px;
    }

    .etiqueta {
        display: none;
    }

    .inferior-visual {
        flex-wrap: wrap;
    }

    .frasco {
        width: 65px;
    }

    .capsula {
        width: 55px;
    }

    .boton-password {
        width: 23px;
    }

    .logo-alternativo {
        font-size: 22px;
    }
}

/* ==========================================
   MOVIMIENTO REDUCIDO
   ========================================== */

@media (prefers-reduced-motion: reduce) {

    .elemento,
    .spinner {
        animation: none;
    }

    .control-animacion {
        display: none;
    }

    .boton-ingresar,
    .boton-ingresar svg,
    .cambiar-entorno svg,
    .input-contenedor {
        transition: none;
    }

    .boton-ingresar:hover svg,
    .cambiar-entorno:hover svg {
        transform: none;
    }
}


/* ==========================================
   LOGIN CESFAM - LAVANDA CLARO
   ========================================== */

.login-page[data-entorno="cesfam"] {
    background:
        radial-gradient(
            ellipse at 10% 10%,
            #E3D5F5,
            transparent 55%
        ),
        #F8F7FC;
}

.login-page[data-entorno="cesfam"] .tarjeta-login {
    border-color: #E6E0EF;
    box-shadow:
        0 25px 65px rgba(102, 85, 121, .12),
        0 5px 15px rgba(51, 51, 68, .04);
}

/* PANEL VISUAL IZQUIERDO */

.login-page[data-entorno="cesfam"] .panel-visual {
    background: linear-gradient(
        155deg,
        #E3D5F5 0%,
        #E9DDF7 55%,
        #F1EAF9 100%
    );

    color: #333344;
}

.login-page[data-entorno="cesfam"] .marca-farma {
    color: #333344;
}

.login-page[data-entorno="cesfam"] .marca-icono {
    background: #FFFFFF;
    color: #665579;
}

.login-page[data-entorno="cesfam"] .sobre-titulo {
    color: #665579;
}

.login-page[data-entorno="cesfam"] .contenido-visual h1 {
    color: #333344;
}

.login-page[data-entorno="cesfam"] .contenido-visual h1 span {
    color: #665579;
}

.login-page[data-entorno="cesfam"] .descripcion-visual {
    color: #51475F;
}

.login-page[data-entorno="cesfam"] .etiqueta {
    background: rgba(255, 255, 255, .65);
    border-color: #D2BFEA;
    color: #333344;
}

/* DECORACIONES Y ANIMACIONES */

.login-page[data-entorno="cesfam"] .circulo {
    border-color: rgba(102, 85, 121, .15);
}

.login-page[data-entorno="cesfam"] .brillo {
    background: #FFFFFF;
    opacity: .30;
}

.login-page[data-entorno="cesfam"] .decoraciones [fill="white"] {
    fill: #665579;
}

.login-page[data-entorno="cesfam"] .decoraciones [stroke="white"] {
    stroke: #665579;
}

.login-page[data-entorno="cesfam"] .inferior-visual p {
    color: #665579;
}

.login-page[data-entorno="cesfam"] .control-animacion {
    background: rgba(255, 255, 255, .60);
    border-color: #D2BFEA;
    color: #665579;
}

.login-page[data-entorno="cesfam"] .control-animacion:hover {
    background: #FFFFFF;
}

/* PANEL DERECHO */

.login-page[data-entorno="cesfam"] .panel-login {
    background: #FFFFFF;
}

.login-page[data-entorno="cesfam"] .entorno {
    background: #E3D5F5;
    border-color: #D2BFEA;
    color: #333344;
}

.login-page[data-entorno="cesfam"] .punto-entorno {
    background: #665579;
}

.login-page[data-entorno="cesfam"] .bienvenida h2 {
    color: #333344;
}

.login-page[data-entorno="cesfam"] .bienvenida p {
    color: #64748B;
}

/* FORMULARIO */

.login-page[data-entorno="cesfam"] .campo label {
    color: #333344;
}

.login-page[data-entorno="cesfam"] .input-contenedor {
    background: #F8F7FC;
    border-color: #D2BFEA;
}

.login-page[data-entorno="cesfam"] .input-contenedor:focus-within {
    background: #FFFFFF;
    border-color: #665579;
    box-shadow: 0 0 0 3px #E3D5F5;
}

.login-page[data-entorno="cesfam"] .input-contenedor input {
    color: #333344;
}

.login-page[data-entorno="cesfam"] .input-icono {
    color: #665579;
}

.login-page[data-entorno="cesfam"] .boton-password {
    color: #665579;
}

/* BOTON DE INGRESO */

.login-page[data-entorno="cesfam"] .boton-ingresar {
    background: #665579;
    color: #FFFFFF;
}

.login-page[data-entorno="cesfam"] .boton-ingresar:hover:not(:disabled) {
    background: #51435F;
    box-shadow: 0 6px 18px rgba(102, 85, 121, .20);
}

.login-page[data-entorno="cesfam"] .cambiar-entorno {
    color: #665579;
}

.login-page[data-entorno="cesfam"] .cambiar-entorno:hover {
    color: #333344;
}
</style>
