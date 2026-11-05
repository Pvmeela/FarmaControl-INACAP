
#  FarmaControl
### Sistema Web Integrado de Gestión Farmacéutica

**Proyecto Integrado | Analista Programador | INACAP 2026**

---

## Descripción del proyecto

**FarmaControl** es un sistema web desarrollado como parte de la asignatura Proyecto Integrado de INACAP.

Su objetivo es facilitar la gestión de información farmacéutica mediante una plataforma que permita administrar medicamentos, controlar inventarios y apoyar los procesos relacionados con el funcionamiento de una farmacia.

El sistema está diseñado considerando dos contextos:

- **Farmacia privada:** gestión comercial y administración de medicamentos.
- **Farmacia asistencial (CESFAM):** gestión de medicamentos y apoyo a procesos asistenciales.

Además, FarmaControl contempla su utilización como herramienta de simulación académica para estudiantes del área de Química y Farmacia de INACAP.

El proyecto surge a partir del levantamiento de necesidades realizado con un docente del área farmacéutica, quien participó como representante del cliente académico.

> **Aviso:** FarmaControl es un software de simulación desarrollado exclusivamente con fines educativos. No está autorizado para operaciones farmacéuticas reales.

---

## Objetivo del sistema

Desarrollar una plataforma web que permita centralizar y organizar información farmacéutica, facilitando el registro de medicamentos y la implementación progresiva de funcionalidades relacionadas con inventario, lotes, vencimientos y control de acceso.

Se busca construir una aplicación funcional, intuitiva y adecuada para representar procesos farmacéuticos en un entorno académico.

---

## Tecnologías utilizadas

| Tecnología | Función |
|---|---|
| Laravel 11 | Desarrollo del backend |
| PHP | Lógica del servidor |
| Vue.js | Interfaz de usuario |
| Inertia.js | Integración entre Laravel y Vue |
| Tailwind CSS | Diseño visual |
| MySQL | Almacenamiento de información |
| Docker y Laravel Sail | Entorno de desarrollo |
| WSL2 | Entorno Linux |
| Git y GitHub | Control de versiones |
| Visual Studio Code | Desarrollo del código |

La aplicación utiliza Laravel para procesar las solicitudes, Vue.js para presentar las interfaces e Inertia.js para comunicar ambos componentes.

MySQL permite almacenar los datos de forma persistente.

---

##  Avances implementados

Actualmente, FarmaControl cuenta con las siguientes funcionalidades:

### 1. Configuración del proyecto
- Instalación y configuración de Laravel.
- Configuración de Docker y Laravel Sail.
- Integración de Vue.js e Inertia.js.
- Conexión funcional con MySQL.
- Configuración del repositorio en GitHub.

### 2. Autenticación
- Formulario de inicio de sesión.
- Validación de credenciales.
- Protección de rutas para usuarios autenticados.
- Cierre de sesión.
- Acceso al panel principal.

### 3. Gestión de medicamentos
Se desarrolló un módulo que permite:

- Registrar nuevos medicamentos.
- Consultar medicamentos almacenados.
- Editar información existente.
- Activar y desactivar medicamentos sin eliminarlos.
- Validar campos obligatorios y códigos únicos.
- Guardar los cambios en MySQL.

Cada medicamento contiene información como nombre, principio activo, concentración, forma farmacéutica, presentación y código.

### 4. Interfaz del sistema
- Página de inicio.
- Panel principal.
- Catálogo de medicamentos.
- Formularios de registro y edición.
- Mensajes de confirmación.
- Marca de agua que identifica el uso académico.

### 5. Base de datos
Se han configurado las tablas iniciales para usuarios, roles y medicamentos.

Las operaciones del catálogo se realizan mediante Laravel y Eloquent, utilizando MySQL como base de datos.

---

## Funcionalidades en desarrollo

| Funcionalidad | Estado |
|---|---|
| Configuración del entorno |  Implementado |
| Conexión a MySQL |  Implementado |
| Inicio y cierre de sesión |  Implementado |
| Panel principal |  Versión inicial |
| Registro de medicamentos |  Implementado |
| Consulta y edición de medicamentos |  Implementado |
| Activación y desactivación |  Implementado |
| Permisos según roles |  En desarrollo |
| Gestión de inventario |  Pendiente |
| Gestión de lotes y vencimientos |  Pendiente |
| Alertas de stock y vencimiento |  Pendiente |
| Pruebas integrales |  Pendiente |
| Despliegue en AWS |  Pendiente |

Los avances corresponden a funcionalidades desarrolladas en el entorno local. El sistema continúa en proceso de implementación y pruebas.

---

##  Roles del sistema

FarmaControl contempla diferentes perfiles de usuario, de acuerdo con las responsabilidades definidas para el proyecto.

Entre ellos se encuentran:

- Docente.
- Estudiante.
- Químico Farmacéutico.
- Técnico en Farmacia.

Los roles ya se encuentran registrados en la base de datos.

Actualmente se trabaja en implementar los permisos correspondientes para restringir las operaciones según el perfil del usuario.

---

##  Pruebas realizadas

Durante el desarrollo se han realizado verificaciones para comprobar el funcionamiento de los componentes implementados.

Entre las pruebas realizadas se encuentran:

- Ejecución del proyecto mediante Docker.
- Verificación de conexión con MySQL.
- Comprobación de rutas de Laravel.
- Registro de medicamentos.
- Edición de información almacenada.
- Consulta de registros mediante Laravel Tinker.
- Pruebas de las operaciones de activación y desactivación.

Estas verificaciones permiten comprobar el funcionamiento inicial de los módulos, aunque todavía se requieren pruebas integrales.

---

##  Acceso al sistema

### Dirección local

**http://localhost:8080**

Esta es la dirección utilizada actualmente para ejecutar FarmaControl en el entorno de desarrollo.

**Importante para la revisión docente:** esta dirección funciona únicamente cuando el proyecto está ejecutándose en el equipo desde el que se accede. No corresponde todavía a un enlace público.

### Ejecución local

Con Docker Desktop iniciado y el proyecto configurado, ejecutar desde la carpeta principal:

```bash
docker compose up -d
```

Para compilar el frontend:

```bash
docker compose exec laravel.test npm run build
```

Posteriormente, abrir en el navegador:

```text
http://localhost:8080
```

Para una instalación nueva también es necesario configurar las dependencias, las variables de entorno y la base de datos.

El despliegue público en AWS está contemplado para una etapa posterior del proyecto.

---

##  Seguimiento del desarrollo

Este repositorio permite revisar el código fuente y los avances realizados durante la construcción de FarmaControl.

A través del historial de **commits de GitHub** es posible identificar las modificaciones incorporadas al proyecto.

El archivo README se actualizará progresivamente para reflejar las funcionalidades implementadas y las actividades pendientes.

---

##  Contexto académico

**Institución:** INACAP  
**Carrera:** Analista Programador  
**Asignatura:** Proyecto Integrado  
**Año:** 2026  
**Proyecto:** FarmaControl

El desarrollo se basa en los requerimientos levantados y documentados para el Sistema Web Integrado de Gestión para Farmacia Privada y Asistencial (CESFAM).

---

**FarmaControl | Proyecto Integrado INACAP 2026**

*Software de simulación académica. Sin validez legal para operaciones farmacéuticas reales.*
