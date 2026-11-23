
<script setup>
import { computed, ref } from 'vue'
import { Head, Link, router, usePage } from '@inertiajs/vue3'

const props = defineProps({
    alertas: {
        type: Object,
        default: () => ({
            stock_bajo: null,
            quiebres_stock: null,
            proximos_vencer: null,
            lotes_vencidos: null,
        }),
    },
})

const page = usePage()
const busqueda = ref('')
const cerrandoSesion = ref(false)

const usuario = computed(() => page.props.auth?.user ?? null)
const nombreUsuario = computed(() => usuario.value?.name ?? 'Usuario')
const rolUsuario = computed(() => usuario.value?.rol ?? 'Sin rol asignado')

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

const entornoActual = computed(() =>
    ['privada', 'cesfam'].includes(page.props.entorno)
        ? page.props.entorno
        : null
)

const nombreEntorno = computed(() => {
    if (entornoActual.value === 'privada') return 'Farmacia privada'
    if (entornoActual.value === 'cesfam') return 'Farmacia CESFAM'
    return 'Panel general'
})

const subtituloEntorno = computed(() => {
    if (entornoActual.value === 'privada') return 'Gestión comercial'
    if (entornoActual.value === 'cesfam') return 'Gestión asistencial'
    return 'Gestión farmacéutica'
})

const descripcionEntorno = computed(() => {
    if (entornoActual.value === 'privada') {
        return 'Administra medicamentos, inventario, movimientos y ventas desde un espacio organizado y moderno.'
    }

    if (entornoActual.value === 'cesfam') {
        return 'Gestiona medicamentos, pacientes, recetas y dispensaciones desde un entorno asistencial de simulación.'
    }

    return 'Gestiona los procesos farmacéuticos desde una plataforma centralizada.'
})

// Íconos SVG vectoriales, sin emojis.
const iconos = {
    medicamentos: [
        '<rect x="3" y="8" width="18" height="8" rx="4" transform="rotate(-45 12 12)"/>',
        '<path d="m8.5 8.5 7 7"/>',
    ],
    inventario: [
        '<path d="m12 2 9 5-9 5-9-5 9-5Z"/>',
        '<path d="M3 7v10l9 5 9-5V7"/>',
        '<path d="M12 12v10"/>',
    ],
    movimientos: [
        '<path d="M3 7h17m-4-4 4 4-4 4"/>',
        '<path d="M21 17H4m4-4-4 4 4 4"/>',
    ],
    ventas: [
        '<rect x="3" y="4" width="18" height="16" rx="2"/>',
        '<path d="M7 8h10M7 12h10M7 16h5"/>',
    ],
    pacientes: [
        '<circle cx="12" cy="8" r="4"/>',
        '<path d="M4 21v-2a8 8 0 0 1 16 0v2"/>',
    ],
    recetas: [
        '<rect x="5" y="3" width="14" height="18" rx="2"/>',
        '<path d="M9 8h6M9 12h6M9 16h3"/>',
    ],
    dispensaciones: [
        '<path d="M3 10 12 3l9 7v11H3V10Z"/>',
        '<path d="M12 9v8M8 13h8"/>',
    ],
    usuarios: [
        '<circle cx="9" cy="8" r="3"/>',
        '<path d="M3 20v-2a6 6 0 0 1 12 0v2"/>',
        '<path d="M17 6a3 3 0 0 1 0 6M18 15a5 5 0 0 1 3 5"/>',
    ],
    alerta: [
        '<path d="M12 3 2 21h20L12 3Z"/>',
        '<path d="M12 9v5M12 18h.01"/>',
    ],
    calendario: [
        '<rect x="3" y="5" width="18" height="16" rx="2"/>',
        '<path d="M7 3v4M17 3v4M3 10h18"/>',
    ],
    escudo: [
        '<path d="M12 2 4 5v6c0 5 3.5 8.5 8 11 4.5-2.5 8-6 8-11V5l-8-3Z"/>',
        '<path d="m9 12 2 2 4-4"/>',
    ],
    grafico: [
        '<path d="M3 3v18h18"/>',
        '<path d="m7 15 4-4 3 3 5-7"/>',
    ],
    tienda: [
        '<path d="M3 10V5h18v5"/>',
        '<path d="M4 10v11h16V10"/>',
        '<path d="M2 10h20M9 21v-7h6v7"/>',
    ],
    hospital: [
        '<rect x="3" y="5" width="18" height="16" rx="2"/>',
        '<path d="M12 8v8M8 12h8M8 5V3h8v2"/>',
    ],
    flecha: [
        '<path d="M5 12h14M13 6l6 6-6 6"/>',
    ],
    buscar: [
        '<circle cx="11" cy="11" r="7"/>',
        '<path d="m16 16 5 5"/>',
    ],
    salir: [
        '<path d="M9 4H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h4"/>',
        '<path d="M14 8l4 4-4 4M18 12H9"/>',
    ],
    cambiar: [
        '<path d="M4 7h16m-4-4 4 4-4 4"/>',
        '<path d="M20 17H4m4-4-4 4 4 4"/>',
    ],
    cruz: [
        '<path d="M12 4v16M4 12h16"/>',
    ],
}

const indicadores = computed(() => [
    {
        nombre: 'Stock bajo',
        valor: props.alertas?.stock_bajo ?? '—',
        descripcion: 'Medicamentos bajo el mínimo',
        icono: 'grafico',
        color: 'amber',
    },
    {
        nombre: 'Quiebres de stock',
        valor: props.alertas?.quiebres_stock ?? '—',
        descripcion: 'Sin existencias utilizables',
        icono: 'alerta',
        color: 'red',
    },
    {
        nombre: 'Próximos a vencer',
        valor: props.alertas?.proximos_vencer ?? '—',
        descripcion: 'Lotes próximos a vencer en 30 días',
        icono: 'calendario',
        color: 'blue',
    },
    {
        nombre: 'Lotes vencidos',
        valor: props.alertas?.lotes_vencidos ?? '—',
        descripcion: 'Lotes fuera de vigencia',
        icono: 'escudo',
        color: 'rose',
    },
])

const modulos = [
    {
        nombre: 'Medicamentos',
        icono: 'medicamentos',
        descripcion: 'Administración del catálogo farmacéutico y la información de medicamentos.',
        enlace: '/medicamentos',
        categoria: 'Inventario',
        color: 'blue',
        entornos: ['privada', 'cesfam'],
        disponible: true,
    },
    {
        nombre: 'Inventario y lotes',
        icono: 'inventario',
        descripcion: 'Control de existencias, lotes, fechas de vencimiento y disponibilidad.',
        enlace: '/lotes',
        categoria: 'Inventario',
        color: 'indigo',
        entornos: ['privada', 'cesfam'],
        disponible: true,
    },
    {
        nombre: 'Movimientos de inventario',
        icono: 'movimientos',
        descripcion: 'Registro de entradas, salidas y trazabilidad del inventario mediante FEFO.',
        enlace: '/movimientos',
        categoria: 'Inventario',
        color: 'cyan',
        entornos: ['privada', 'cesfam'],
        disponible: true,
    },
    {
        nombre: 'Ventas',
        icono: 'ventas',
        descripcion: 'Registro y consulta de operaciones de venta con actualización de inventario.',
        enlace: '/ventas',
        categoria: 'Operaciones',
        color: 'emerald',
        entornos: ['privada'],
        disponible: true,
    },
    {
        nombre: 'Pacientes',
        icono: 'pacientes',
        descripcion: 'Gestión y consulta de registros de pacientes del área asistencial.',
        enlace: '/pacientes',
        categoria: 'Asistencial',
        color: 'violet',
        entornos: ['cesfam'],
        disponible: true,
    },
    {
        nombre: 'Recetas',
        icono: 'recetas',
        descripcion: 'Registro, consulta y control de vigencia de prescripciones farmacéuticas.',
        enlace: '/recetas',
        categoria: 'Asistencial',
        color: 'blue',
        entornos: ['privada', 'cesfam'],
        disponible: true,
    },
    {
        nombre: 'Dispensaciones',
        icono: 'dispensaciones',
        descripcion: 'Registro de entregas de medicamentos vinculadas a recetas e inventario.',
        enlace: '/dispensaciones',
        categoria: 'Asistencial',
        color: 'teal',
        entornos: ['cesfam'],
        disponible: true,
    },
    {
        nombre: 'Usuarios y roles',
        icono: 'usuarios',
        descripcion: 'Administración de cuentas de usuario y permisos de acceso.',
        enlace: '/usuarios',
        categoria: 'Administración',
        color: 'slate',
        entornos: ['privada', 'cesfam'],
        disponible: true,
    },
]

const modulosVisibles = computed(() =>
    modulos.filter(modulo => {
        if (
            entornoActual.value &&
            !modulo.entornos.includes(entornoActual.value)
        ) {
            return false
        }

        if (modulo.nombre === 'Dispensaciones') {
            return puedeConsultarDispensaciones.value
        }

        if (modulo.nombre === 'Usuarios y roles') {
            return esAdministrador.value
        }

        return true
    })
)

const modulosFiltrados = computed(() => {
    const termino = busqueda.value.trim().toLowerCase()

    if (!termino) return modulosVisibles.value

    return modulosVisibles.value.filter(modulo =>
        [modulo.nombre, modulo.descripcion, modulo.categoria]
            .join(' ')
            .toLowerCase()
            .includes(termino)
    )
})

const cantidadModulosActivos = computed(
    () => modulosVisibles.value.filter(modulo => modulo.disponible).length
)

const accesosRapidos = computed(() => {
    const nombres = entornoActual.value === 'cesfam'
        ? ['Pacientes', 'Recetas', 'Dispensaciones']
        : ['Ventas', 'Recetas', 'Dispensaciones']

    return nombres
        .map(nombre =>
            modulosVisibles.value.find(modulo => modulo.nombre === nombre)
        )
        .filter(Boolean)
})

function cerrarSesion() {
    if (cerrandoSesion.value) return

    cerrandoSesion.value = true

    router.post('/logout', {}, {
        onFinish: () => {
            cerrandoSesion.value = false
        },
    })
}
</script>

<template>
    <Head title="Panel de control | FarmaControl" />

    <div class="pagina-dashboard">
        <!-- FIGURAS DECORATIVAS -->
        <div class="fondo-figuras" aria-hidden="true">
            <span class="figura figura-uno"></span>
            <span class="figura figura-dos"></span>
            <span class="figura figura-tres"></span>
            <span class="figura figura-cuatro"></span>
        </div>

        <!-- ENCABEZADO -->
        <header class="encabezado">
            <div class="encabezado-contenido">
                <Link href="/" class="marca">
                    <span class="marca-icono">
                        <svg viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2.5"
                            stroke-linecap="round">
                            <path d="M12 4v16M4 12h16"/>
                        </svg>
                    </span>

                    <span class="marca-texto">
                        <strong>Farma<span>Control</span></strong>
                        <small>Plataforma de gestión farmacéutica</small>
                    </span>
                </Link>

                <div class="acciones-encabezado">
                    <span class="estado-panel">
                        <span class="punto-estado"></span>
                        Panel disponible
                    </span>

                    <Link href="/" class="boton-cambiar">
                        <svg viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="1.8"
                            stroke-linecap="round" stroke-linejoin="round">
                            <g v-html="iconos.cambiar.join('')"></g>
                        </svg>
                        <span>Cambiar entorno</span>
                    </Link>

                    <button
                        type="button"
                        class="boton-salir"
                        :disabled="cerrandoSesion"
                        @click="cerrarSesion"
                    >
                        <svg viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="1.8"
                            stroke-linecap="round" stroke-linejoin="round">
                            <g v-html="iconos.salir.join('')"></g>
                        </svg>
                        <span>{{ cerrandoSesion ? 'Saliendo...' : 'Cerrar sesión' }}</span>
                    </button>
                </div>
            </div>
        </header>

        <main class="contenido-principal">

            <!-- PRESENTACIÓN -->
            <section class="presentacion">
                <div class="presentacion-luz"></div>
                <div class="presentacion-circulo"></div>

                <div class="presentacion-contenido">
                    <div class="etiqueta-superior">
                        <span class="etiqueta-punto"></span>
                        CENTRO DE OPERACIONES
                    </div>

                    <div class="datos-usuario">
                        <div class="dato">
                            <span>Usuario conectado</span>
                            <strong>{{ nombreUsuario }}</strong>
                        </div>

                        <div class="dato">
                            <span>Rol asignado</span>
                            <strong>{{ rolUsuario }}</strong>
                        </div>
                    </div>

                    <p class="presentacion-subtitulo">
                        {{ subtituloEntorno }}
                    </p>

                    <h1>{{ nombreEntorno }}</h1>

                    <p class="presentacion-descripcion">
                        {{ descripcionEntorno }}
                    </p>

                    <div class="presentacion-acciones">
                        <Link href="/medicamentos" class="accion-principal">
                            Ver medicamentos
                            <svg viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2"
                                stroke-linecap="round">
                                <g v-html="iconos.flecha.join('')"></g>
                            </svg>
                        </Link>

                        <Link href="/movimientos" class="accion-secundaria">
                            Consultar inventario
                        </Link>
                    </div>
                </div>

                <div class="presentacion-simbolo" aria-hidden="true">
                    <svg viewBox="0 0 240 240" fill="none"
                        stroke="currentColor" stroke-width="1.5">
                        <circle cx="120" cy="120" r="95"/>
                        <circle cx="120" cy="120" r="67"/>
                        <circle cx="120" cy="120" r="38"/>
                        <path d="M120 25v190M25 120h190"/>
                    </svg>
                </div>
            </section>

            <!-- IDENTIFICACIÓN DEL ENTORNO -->
            <section class="entorno-activo">
                <div class="entorno-icono">
                    <svg viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="1.7"
                        stroke-linecap="round" stroke-linejoin="round">
                        <g v-html="iconos[
                            entornoActual === 'cesfam' ? 'hospital' : 'tienda'
                        ].join('')"></g>
                    </svg>
                </div>

                <div class="entorno-informacion">
                    <span>ENTORNO DE TRABAJO ACTIVO</span>
                    <h2>{{ nombreEntorno }}</h2>
                    <p>
                        Los módulos se organizan según el entorno
                        seleccionado y los permisos de tu usuario.
                    </p>
                </div>

                <Link href="/" class="entorno-enlace">
                    Seleccionar otro entorno
                    <svg viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2"
                        stroke-linecap="round">
                        <g v-html="iconos.flecha.join('')"></g>
                    </svg>
                </Link>
            </section>

            <!-- INDICADORES -->
            <section class="seccion">
                <div class="seccion-encabezado">
                    <div>
                        <span class="seccion-etiqueta">SUPERVISIÓN</span>
                        <h2>Estado del inventario</h2>
                        <p>Indicadores actualizados desde los registros del sistema.</p>
                    </div>

                    <Link href="/lotes" class="enlace-texto">
                        Revisar lotes
                        <svg viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2"
                            stroke-linecap="round">
                            <g v-html="iconos.flecha.join('')"></g>
                        </svg>
                    </Link>
                </div>

                <div class="indicadores-grid">
                    <article
                        v-for="indicador in indicadores"
                        :key="indicador.nombre"
                        class="indicador-tarjeta"
                        :class="'indicador-' + indicador.color"
                    >
                        <div class="indicador-superior">
                            <span>{{ indicador.nombre }}</span>

                            <div class="indicador-icono">
                                <svg viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="1.8"
                                    stroke-linecap="round" stroke-linejoin="round">
                                    <g v-html="iconos[indicador.icono].join('')"></g>
                                </svg>
                            </div>
                        </div>

                        <strong class="indicador-valor">
                            {{ indicador.valor }}
                        </strong>

                        <p>{{ indicador.descripcion }}</p>
                    </article>
                </div>
            </section>

            <!-- ACCESOS RÁPIDOS -->
            <section v-if="accesosRapidos.length" class="seccion">
                <div class="seccion-encabezado">
                    <div>
                        <span class="seccion-etiqueta">ACCESOS RÁPIDOS</span>
                        <h2>Operaciones frecuentes</h2>
                        <p>Accede directamente a las funciones más utilizadas.</p>
                    </div>
                </div>

                <div class="accesos-grid">
                    <Link
                        v-for="acceso in accesosRapidos"
                        :key="acceso.nombre"
                        :href="acceso.enlace"
                        class="acceso-tarjeta"
                    >
                        <div class="acceso-icono">
                            <svg viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="1.8"
                                stroke-linecap="round" stroke-linejoin="round">
                                <g v-html="iconos[acceso.icono].join('')"></g>
                            </svg>
                        </div>

                        <div class="acceso-texto">
                            <strong>{{ acceso.nombre }}</strong>
                            <span>{{ acceso.categoria }}</span>
                        </div>

                        <svg class="acceso-flecha" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round">
                            <g v-html="iconos.flecha.join('')"></g>
                        </svg>
                    </Link>
                </div>
            </section>

            <!-- MÓDULOS -->
            <section class="seccion">
                <div class="seccion-encabezado seccion-modulos">
                    <div>
                        <span class="seccion-etiqueta">PLATAFORMA</span>
                        <h2>Módulos del sistema</h2>
                        <p>
                            {{ cantidadModulosActivos }}
                            módulos disponibles para tu entorno y rol.
                        </p>
                    </div>

                    <div class="buscador">
                        <svg viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="1.8"
                            stroke-linecap="round">
                            <g v-html="iconos.buscar.join('')"></g>
                        </svg>

                        <input
                            v-model="busqueda"
                            type="search"
                            placeholder="Buscar un módulo..."
                            aria-label="Buscar módulos"
                        />
                    </div>
                </div>

                <div class="modulos-grid">
                    <article
                        v-for="modulo in modulosFiltrados"
                        :key="modulo.nombre"
                        class="modulo-tarjeta"
                    >
                        <div class="modulo-superior">
                            <div
                                class="modulo-icono"
                                :class="'modulo-color-' + modulo.color"
                            >
                                <svg viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="1.7"
                                    stroke-linecap="round" stroke-linejoin="round">
                                    <g v-html="iconos[modulo.icono].join('')"></g>
                                </svg>
                            </div>

                            <span class="modulo-estado">
                                Disponible
                            </span>
                        </div>

                        <span class="modulo-categoria">
                            {{ modulo.categoria }}
                        </span>

                        <h3>{{ modulo.nombre }}</h3>
                        <p>{{ modulo.descripcion }}</p>

                        <Link
                            :href="modulo.enlace"
                            class="modulo-boton"
                        >
                            Acceder al módulo

                            <svg viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2"
                                stroke-linecap="round">
                                <g v-html="iconos.flecha.join('')"></g>
                            </svg>
                        </Link>
                    </article>
                </div>

                <div v-if="modulosFiltrados.length === 0" class="sin-resultados">
                    <h3>No se encontraron módulos</h3>
                    <p>Intenta buscar con otro término.</p>
                </div>
            </section>

            <!-- PIE DE PÁGINA -->
            <footer class="pie-pagina">
                <strong>FarmaControl · Gestión farmacéutica</strong>
                <span>
                    Plataforma académica para farmacia privada y asistencial.
                </span>
            </footer>
        </main>

        <!-- MARCA DE AGUA -->
        <div class="marca-agua">
            <strong>FARMA<span>CONTROL</span></strong>
            <p>Uso académico · INACAP</p>
            <small>Sistema de simulación · Sin validez legal</small>
        </div>
    </div>
</template>

<style scoped>
/* BASE GENERAL */
.pagina-dashboard {
    --azul: #174b86;
    --azul-oscuro: #102f59;
    --turquesa: #45a9b1;
    --texto: #17334d;
    --texto-suave: #718397;

    position: relative;
    min-height: 100vh;
    overflow: hidden;
    background:
        radial-gradient(circle at 8% 9%, rgba(69,169,177,.13), transparent 27%),
        radial-gradient(circle at 92% 30%, rgba(63,126,198,.12), transparent 27%),
        linear-gradient(145deg, #f5fbfa 0%, #f2f8fc 48%, #edf5fb 100%);
    color: var(--texto);
    font-family: Inter, ui-sans-serif, system-ui, sans-serif;
}

.fondo-figuras {
    position: absolute;
    inset: 0;
    overflow: hidden;
    pointer-events: none;
}

.figura {
    position: absolute;
    border-radius: 50%;
    border: 1px solid rgba(69,169,177,.12);
    background: rgba(255,255,255,.18);
    animation: flotar 18s ease-in-out infinite alternate;
}

.figura-uno {
    width: 230px;
    height: 230px;
    top: 160px;
    left: -120px;
}

.figura-dos {
    width: 360px;
    height: 360px;
    top: 520px;
    right: -230px;
    animation-delay: -6s;
}

.figura-tres {
    width: 130px;
    height: 130px;
    top: 1200px;
    left: 7%;
    animation-delay: -9s;
}

.figura-cuatro {
    width: 200px;
    height: 200px;
    bottom: 300px;
    right: 8%;
    animation-delay: -4s;
}

@keyframes flotar {
    from { transform: translateY(0); }
    to { transform: translateY(16px); }
}

/* ENCABEZADO */
.encabezado {
    position: relative;
    z-index: 30;
    background: rgba(255,255,255,.85);
    border-bottom: 1px solid rgba(191,216,228,.65);
    backdrop-filter: blur(18px);
}

.encabezado-contenido {
    max-width: 1240px;
    margin: auto;
    padding: 18px 26px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
    flex-wrap: wrap;
}

.marca {
    display: flex;
    align-items: center;
    gap: 12px;
    text-decoration: none;
}

.marca-icono {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 48px;
    height: 48px;
    border-radius: 16px;
    color: white;
    background: linear-gradient(135deg, #146a76, #287fa8);
    box-shadow: 0 8px 20px rgba(20,106,118,.16);
}

.marca-icono svg {
    width: 27px;
    height: 27px;
}

.marca-texto {
    display: flex;
    flex-direction: column;
}

.marca-texto strong {
    font-size: 21px;
    color: #17334d;
    letter-spacing: -.7px;
}

.marca-texto strong span {
    color: #238a9b;
}

.marca-texto small {
    font-size: 11px;
    color: #7c91a2;
}

.acciones-encabezado {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
}

.estado-panel {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-size: 12px;
    font-weight: 700;
    color: #238274;
    background: #eaf8f2;
    padding: 11px 15px;
    border: 1px solid #c9ecdf;
    border-radius: 30px;
}

.punto-estado,
.etiqueta-punto {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #4cc5b6;
}

.boton-cambiar,
.boton-salir {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 9px;
    padding: 11px 15px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 700;
    transition: .25s;
}

.boton-cambiar {
    background: #e8f5f7;
    color: #146a76;
    border: 1px solid #cce9ed;
}

.boton-cambiar:hover {
    background: #d7eef0;
}

.boton-salir {
    background: white;
    color: #64748b;
    border: 1px solid #dbe5eb;
    cursor: pointer;
}

.boton-salir:hover {
    background: #fff2f2;
    color: #bd4545;
    border-color: #f3d1d1;
}

.boton-salir:disabled {
    opacity: .5;
}

.boton-cambiar svg,
.boton-salir svg {
    width: 17px;
    height: 17px;
}

/* CONTENIDO */
.contenido-principal {
    position: relative;
    z-index: 1;
    max-width: 1240px;
    margin: auto;
    padding: 35px 26px 90px;
}

/* PRESENTACIÓN */
.presentacion {
    position: relative;
    overflow: hidden;
    min-height: 330px;
    padding: 43px 46px;
    border-radius: 28px;
    background:
        linear-gradient(125deg, #123b6b 0%, #176c89 62%, #2799a7 100%);
    box-shadow: 0 20px 45px rgba(23,75,134,.14);
    color: white;
}

.presentacion-luz {
    position: absolute;
    width: 440px;
    height: 440px;
    right: -90px;
    top: -190px;
    background: rgba(147,235,233,.15);
    border-radius: 50%;
    filter: blur(45px);
}

.presentacion-circulo {
    position: absolute;
    width: 350px;
    height: 350px;
    right: -180px;
    bottom: -250px;
    border: 55px solid rgba(255,255,255,.06);
    border-radius: 50%;
}

.presentacion-contenido {
    position: relative;
    z-index: 2;
    max-width: 750px;
}

.etiqueta-superior {
    display: inline-flex;
    align-items: center;
    gap: 9px;
    padding: 9px 15px;
    border: 1px solid rgba(255,255,255,.2);
    border-radius: 30px;
    background: rgba(255,255,255,.1);
    color: #e3fbff;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 1px;
}

.datos-usuario {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    margin: 23px 0;
}

.dato {
    display: flex;
    flex-direction: column;
    gap: 5px;
    min-width: 135px;
    padding: 12px 17px;
    border-radius: 13px;
    background: rgba(255,255,255,.1);
    border: 1px solid rgba(255,255,255,.17);
}

.dato span {
    font-size: 11px;
    color: #cde9f1;
}

.dato strong {
    font-size: 13px;
    color: white;
}

.presentacion-subtitulo {
    margin-bottom: 8px;
    color: #9ce5e8;
    font-size: 13px;
    font-weight: 700;
    letter-spacing: .8px;
    text-transform: uppercase;
}

.presentacion h1 {
    font-size: clamp(30px, 4vw, 44px);
    font-weight: 800;
    line-height: 1.15;
    letter-spacing: -1.4px;
}

.presentacion-descripcion {
    max-width: 590px;
    margin-top: 16px;
    color: #d5edf5;
    font-size: 14px;
    line-height: 1.85;
}

.presentacion-acciones {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
    margin-top: 26px;
}

.accion-principal,
.accion-secundaria {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 11px;
    padding: 13px 19px;
    border-radius: 12px;
    font-size: 13px;
    font-weight: 700;
    transition: .25s;
}

.accion-principal {
    color: #12526b;
    background: white;
}

.accion-principal:hover {
    background: #e8f8f8;
    transform: translateY(-2px);
}

.accion-secundaria {
    color: white;
    background: rgba(255,255,255,.1);
    border: 1px solid rgba(255,255,255,.24);
}

.accion-secundaria:hover {
    background: rgba(255,255,255,.19);
}

.accion-principal svg {
    width: 17px;
    height: 17px;
}

.presentacion-simbolo {
    position: absolute;
    right: 15px;
    top: 45px;
    width: 230px;
    height: 230px;
    color: rgba(255,255,255,.13);
    pointer-events: none;
}

.presentacion-simbolo svg {
    width: 100%;
    height: 100%;
}

/* ENTORNO ACTIVO */
.entorno-activo {
    display: flex;
    align-items: center;
    gap: 20px;
    flex-wrap: wrap;
    margin-top: 22px;
    padding: 24px 27px;
    border-radius: 22px;
    background: rgba(255,255,255,.75);
    border: 1px solid rgba(178,217,226,.65);
    backdrop-filter: blur(16px);
    box-shadow: 0 8px 25px rgba(29,91,125,.045);
}

.entorno-icono {
    width: 60px;
    height: 60px;
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 17px;
    color: #177c8e;
    background: linear-gradient(135deg, #dff5f2, #e4f1ff);
}

.entorno-icono svg {
    width: 29px;
    height: 29px;
}

.entorno-informacion {
    flex: 1;
    min-width: 200px;
}

.entorno-informacion > span {
    font-size: 10px;
    font-weight: 800;
    letter-spacing: 1.3px;
    color: #2494a1;
}

.entorno-informacion h2 {
    margin-top: 5px;
    font-size: 20px;
    font-weight: 800;
    color: #173b5d;
}

.entorno-informacion p {
    margin-top: 5px;
    font-size: 12px;
    line-height: 1.7;
    color: #718397;
}

.entorno-enlace {
    display: inline-flex;
    align-items: center;
    gap: 9px;
    color: #18788b;
    font-size: 12px;
    font-weight: 700;
    transition: .2s;
}

.entorno-enlace:hover {
    color: #0b4c69;
}

.entorno-enlace svg {
    width: 17px;
    height: 17px;
}

/* SECCIONES */
.seccion {
    margin-top: 46px;
}

.seccion-encabezado {
    display: flex;
    align-items: end;
    justify-content: space-between;
    gap: 20px;
    flex-wrap: wrap;
    margin-bottom: 22px;
}

.seccion-etiqueta {
    display: block;
    margin-bottom: 9px;
    color: #18889a;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 2px;
}

.seccion-encabezado h2 {
    font-size: clamp(22px, 3vw, 27px);
    font-weight: 800;
    letter-spacing: -.7px;
    color: #183a55;
}

.seccion-encabezado p {
    margin-top: 7px;
    font-size: 13px;
    color: #788b9b;
}

.enlace-texto {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    font-weight: 700;
    color: #167c91;
}

.enlace-texto svg {
    width: 16px;
    height: 16px;
}

/* INDICADORES */
.indicadores-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 17px;
}

.indicador-tarjeta {
    position: relative;
    overflow: hidden;
    padding: 23px;
    border-radius: 21px;
    background: rgba(255,255,255,.82);
    border: 1px solid rgba(204,222,233,.8);
    box-shadow: 0 7px 23px rgba(29,91,125,.045);
    transition: transform .25s, box-shadow .25s, border-color .25s;
}

.indicador-tarjeta::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: #45a9b1;
}

.indicador-tarjeta:hover {
    transform: translateY(-4px);
    border-color: #acdce1;
    box-shadow: 0 13px 30px rgba(29,91,125,.09);
}

.indicador-superior {
    display: flex;
    align-items: start;
    justify-content: space-between;
    gap: 12px;
}

.indicador-superior > span {
    font-size: 13px;
    font-weight: 700;
    color: #637a8b;
}

.indicador-icono {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 42px;
    height: 42px;
    flex-shrink: 0;
    border-radius: 13px;
    background: #e9f7f7;
    color: #188a99;
}

.indicador-icono svg {
    width: 22px;
    height: 22px;
}

.indicador-valor {
    display: block;
    margin-top: 18px;
    font-size: 35px;
    font-weight: 800;
    letter-spacing: -1px;
    color: #175d7b;
}

.indicador-tarjeta > p {
    margin-top: 8px;
    font-size: 12px;
    line-height: 1.6;
    color: #8192a1;
}

.indicador-amber::before { background: #e5b34f; }
.indicador-red::before { background: #df7777; }
.indicador-blue::before { background: #5c9ed2; }
.indicador-rose::before { background: #d58aa0; }

.indicador-amber .indicador-valor { color: #b4832a; }
.indicador-red .indicador-valor { color: #c45b5b; }
.indicador-blue .indicador-valor { color: #397cba; }
.indicador-rose .indicador-valor { color: #bb6683; }

/* ACCESOS RÁPIDOS */
.accesos-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 17px;
}

.acceso-tarjeta {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 20px;
    border-radius: 20px;
    background: rgba(255,255,255,.8);
    border: 1px solid rgba(204,222,233,.8);
    box-shadow: 0 7px 23px rgba(29,91,125,.04);
    transition: .25s;
}

.acceso-tarjeta:hover {
    transform: translateY(-4px);
    border-color: #9ed4df;
    box-shadow: 0 14px 30px rgba(29,91,125,.09);
}

.acceso-icono {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 54px;
    height: 54px;
    flex-shrink: 0;
    border-radius: 16px;
    background: #e6f5f7;
    color: #187b91;
}

.acceso-icono svg {
    width: 27px;
    height: 27px;
}

.acceso-texto {
    display: flex;
    flex-direction: column;
    gap: 5px;
    flex: 1;
    min-width: 0;
}

.acceso-texto strong {
    color: #24435c;
    font-size: 14px;
}

.acceso-texto span {
    color: #8799a7;
    font-size: 11px;
}

.acceso-flecha {
    width: 19px;
    height: 19px;
    color: #92a6b4;
    transition: transform .25s;
}

.acceso-tarjeta:hover .acceso-flecha {
    transform: translateX(4px);
    color: #168c9b;
}

/* BUSCADOR */
.buscador {
    position: relative;
    width: 100%;
    max-width: 330px;
}

.buscador > svg {
    position: absolute;
    left: 15px;
    top: 50%;
    transform: translateY(-50%);
    width: 19px;
    height: 19px;
    color: #94a8b5;
    pointer-events: none;
}

.buscador input {
    width: 100%;
    padding: 13px 15px 13px 44px;
    border: 1px solid #d4e4eb;
    border-radius: 13px;
    background: rgba(255,255,255,.86);
    outline: none;
    color: #24435c;
    font-size: 13px;
    transition: .2s;
}

.buscador input:focus {
    border-color: #45a9b1;
    box-shadow: 0 0 0 3px rgba(69,169,177,.13);
}

/* MÓDULOS */
.modulos-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 20px;
}

.modulo-tarjeta {
    position: relative;
    display: flex;
    flex-direction: column;
    min-height: 300px;
    padding: 27px;
    border-radius: 23px;
    background: rgba(255,255,255,.8);
    border: 1px solid rgba(190,219,229,.75);
    box-shadow: 0 8px 25px rgba(29,91,125,.05);
    backdrop-filter: blur(14px);
    transition:
        transform .28s ease,
        box-shadow .28s ease,
        border-color .28s ease;
}

.modulo-tarjeta:hover {
    transform: translateY(-5px);
    border-color: #90cbd5;
    box-shadow: 0 18px 38px rgba(29,91,125,.11);
}

.modulo-superior {
    display: flex;
    align-items: start;
    justify-content: space-between;
    gap: 15px;
    margin-bottom: 24px;
}

.modulo-icono {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 58px;
    height: 58px;
    border-radius: 18px;
    background: #e7f4fa;
    color: #217a9a;
}

.modulo-icono svg {
    width: 29px;
    height: 29px;
}

.modulo-color-indigo { background: #edf0ff; color: #6376b6; }
.modulo-color-cyan { background: #e2f6fa; color: #168c9d; }
.modulo-color-emerald { background: #e4f6ef; color: #278c73; }
.modulo-color-violet { background: #f1edfc; color: #8570b7; }
.modulo-color-teal { background: #e1f6f3; color: #218b84; }
.modulo-color-slate { background: #edf1f5; color: #60758a; }

.modulo-estado {
    padding: 7px 11px;
    border-radius: 30px;
    background: #e8f7f1;
    color: #27866f;
    font-size: 10px;
    font-weight: 800;
}

.modulo-categoria {
    font-size: 10px;
    font-weight: 800;
    letter-spacing: 1.3px;
    color: #2594a1;
    text-transform: uppercase;
}

.modulo-tarjeta h3 {
    margin-top: 10px;
    color: #1e3c55;
    font-size: 19px;
    font-weight: 800;
}

.modulo-tarjeta > p {
    flex: 1;
    margin-top: 13px;
    margin-bottom: 25px;
    font-size: 13px;
    line-height: 1.8;
    color: #788b9c;
}

.modulo-boton {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 11px;
    width: 100%;
    padding: 13px 17px;
    border-radius: 12px;
    background: linear-gradient(110deg, #176c89, #238da2);
    color: white;
    font-size: 12px;
    font-weight: 800;
    transition: .25s;
}

.modulo-boton:hover {
    background: linear-gradient(110deg, #125777, #187d91);
    box-shadow: 0 8px 18px rgba(23,108,137,.15);
}

.modulo-boton svg {
    width: 16px;
    height: 16px;
}

.sin-resultados {
    padding: 45px;
    margin-top: 15px;
    border: 1px solid #d9e6eb;
    border-radius: 20px;
    background: white;
    text-align: center;
}

.sin-resultados h3 {
    font-weight: 800;
    color: #24435c;
}

.sin-resultados p {
    margin-top: 8px;
    font-size: 13px;
    color: #8294a2;
}

/* PIE DE PÁGINA */
.pie-pagina {
    display: flex;
    justify-content: space-between;
    gap: 15px;
    flex-wrap: wrap;
    margin-top: 55px;
    padding-top: 25px;
    border-top: 1px solid #d8e6ed;
    color: #8294a2;
    font-size: 12px;
}

.pie-pagina strong {
    color: #456276;
}

/* MARCA DE AGUA */
.marca-agua {
    position: fixed;
    z-index: 50;
    right: 20px;
    bottom: 20px;
    padding: 12px 18px;
    border: 1px solid rgba(210,225,232,.8);
    border-radius: 15px;
    background: rgba(255,255,255,.85);
    backdrop-filter: blur(14px);
    box-shadow: 0 6px 20px rgba(24,72,100,.06);
    text-align: right;
    pointer-events: none;
}

.marca-agua strong {
    font-size: 11px;
    letter-spacing: .5px;
    color: #607b8d;
}

.marca-agua strong span {
    color: #238a9b;
}

.marca-agua p {
    margin-top: 3px;
    font-size: 10px;
    font-weight: 600;
    color: #8193a2;
}

.marca-agua small {
    display: block;
    margin-top: 2px;
    font-size: 9px;
    color: #a0aeba;
}

/* RESPONSIVE */
@media (max-width: 1050px) {
    .indicadores-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .modulos-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .accesos-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (max-width: 700px) {
    .encabezado-contenido {
        padding: 16px;
    }

    .acciones-encabezado {
        width: 100%;
        justify-content: space-between;
    }

    .estado-panel {
        display: none;
    }

    .contenido-principal {
        padding: 22px 16px 95px;
    }

    .presentacion {
        padding: 30px 24px;
        border-radius: 22px;
    }

    .presentacion-simbolo {
        display: none;
    }

    .entorno-activo {
        padding: 21px;
    }

    .indicadores-grid,
    .accesos-grid,
    .modulos-grid {
        grid-template-columns: 1fr;
    }

    .buscador {
        max-width: 100%;
    }

    .seccion-modulos {
        align-items: stretch;
    }

    .modulo-tarjeta {
        min-height: auto;
    }

    .marca-agua {
        right: 10px;
        bottom: 10px;
        padding: 9px 12px;
    }

    .pie-pagina {
        padding-bottom: 20px;
    }
}

@media (prefers-reduced-motion: reduce) {
    .figura {
        animation: none;
    }

    .modulo-tarjeta,
    .indicador-tarjeta,
    .acceso-tarjeta {
        transition: none;
    }

    .modulo-tarjeta:hover,
    .indicador-tarjeta:hover,
    .acceso-tarjeta:hover {
        transform: none;
    }
}
</style>
