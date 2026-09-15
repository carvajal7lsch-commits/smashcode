# SmashCode

Plataforma web para aprender inglés médico, pensada para los aprendices del programa **Técnico en Enfermería del SENA**. El aprendiz avanza por 4 módulos y 6 RAPs (Resultados de Aprendizaje) con vocabulario clínico, pronunciación, diálogos, ejercicios interactivos y un quiz de cierre; el instructor sigue el progreso de su grupo y el administrador gestiona contenido y usuarios.

Equipo: Manuel Cárdenas, Santiago Lizcano y Juan Sebastián Carvajal.

- Producción: https://smashcode.secarvajal.com
- Requisitos: [`input.md`](input.md) (especificación) · [`hu.md`](hu.md) (historias de usuario) · [`contenidos.md`](contenidos.md) (guion pedagógico de los 4 módulos)

---

## Contenido

1. [Tecnología](#tecnología)
2. [Estructura del proyecto](#estructura-del-proyecto)
3. [Puesta en marcha local (XAMPP)](#puesta-en-marcha-local-xampp)
4. [Docker](#docker)
5. [Despliegue](#despliegue)
6. [Base de datos](#base-de-datos)
7. [Progreso del aprendiz](#progreso-del-aprendiz)
8. [Roles](#roles)
9. [Seguimiento pedagógico del instructor (HU06 / HU23)](#seguimiento-pedagógico-del-instructor-hu06--hu23)
10. [Estado de las historias de usuario](#estado-de-las-historias-de-usuario)
11. [Pendientes para cerrar el proyecto](#pendientes-para-cerrar-el-proyecto)
12. [Cómo trabajamos](#cómo-trabajamos)
13. [Cambios recientes](#cambios-recientes)

---

## Tecnología

| Capa | Herramienta |
|---|---|
| Backend | PHP 8.2 con un MVC propio (`app/Core`) |
| Base de datos | MySQL 8.0 en Docker · MariaDB en XAMPP |
| Servidor | Apache con `mod_rewrite` (todo entra por `index.php`) |
| Dependencias (Composer) | `phpmailer/phpmailer`, `firebase/php-jwt`, `vlucas/phpdotenv` |
| Frontend | HTML, CSS y JavaScript sin framework · Font Awesome |
| Audio | Síntesis de voz del navegador (`speechSynthesis`) |
| Despliegue | Docker Compose sobre Dokploy |

## Estructura del proyecto

```
app/
  Core/          App (router), Controller, Model (conexión PDO), Autoloader
  Controllers/   Un controlador por área: Auth, Aprendiz, Instructor, Admin*, Catalogos, Vocabulario
  Models/        Acceso a datos; un modelo por tabla o por dominio (Instructor, Progreso, User…)
  Views/         Plantillas PHP por rol: aprendiz/, instructor/, admin/, auth/
assets/          css/ y js/
config/          conexion.php, sesion.php, credenciales.php (lee variables de entorno)
database/
  smash_code.sql         Esquema base completo
  migrar.php             Aplica el esquema y las migraciones vigentes
  migraciones/           Cambios de esquema versionados
  seeds/                 Contenido pedagógico de los RAP 1 a 5
  diagnostico_raps.php   Diagnóstico de solo lectura de la tabla rap
includes/        funciones.php (helpers globales), correo.php
index.php        Front controller y definición de todas las rutas
```

Todas las rutas están declaradas en [`index.php`](index.php). Cada controlador valida el rol en su constructor, antes de ejecutar cualquier acción.

## Puesta en marcha local (XAMPP)

1. Clona el repositorio dentro de `C:\xampp\htdocs\smashcode`.
2. Instala las dependencias:
   ```bash
   composer install
   ```
3. Copia `.env.example` a `.env` y completa las credenciales de base de datos, SMTP, JWT y Google OAuth. El archivo `.env` no se sube al repositorio.
4. Crea la base con el esquema. **Importa siempre con `utf8mb4`**, o se pierden las tildes del contenido:
   ```bash
   mysql -u root --default-character-set=utf8mb4 < database/smash_code.sql
   ```
5. Aplica las migraciones:
   ```bash
   php database/migrar.php
   ```
6. Carga el contenido de los RAP 1 a 5:
   ```bash
   php database/seeds/run_seeds.php
   ```
7. Abre http://localhost/smashcode

## Docker

```bash
docker compose up -d --build
```

El contenedor `web` ejecuta `docker-entrypoint.sh`, que corre `database/migrar.php` antes de arrancar Apache. Las migraciones son idempotentes, así que reiniciar el contenedor nunca duplica cambios. Las credenciales llegan como variables de entorno desde `docker-compose.yml`.

## Despliegue

Producción corre en **Dokploy** con **Autodeploy activado sobre la rama `main`** del repositorio `carvajal7lsch-commits/smashcode`.

> **Un push a `main` despliega directo a producción.** No hay rama de staging. Prueba en local antes de hacer push.

- **Desplegar a mano:** botón *Deploy* en Dokploy, por si el webhook no dispara.
- **Revertir:** pestaña *Deployments* de Dokploy (volver al despliegue anterior) o `git revert` + push.
- **Terminal del servidor:** botón *Open Terminal* en Dokploy.
- La conexión con GitHub la administra Sebastián: solo él puede cambiar el repositorio o la rama que sigue Dokploy.

## Base de datos

### Migraciones

[`database/migrar.php`](database/migrar.php) aplica una **lista explícita** de migraciones, no todo lo que haya en la carpeta. Tres archivos de `database/migraciones/` están excluidos a propósito, y el motivo de cada uno está documentado en el propio script. **Al crear una migración nueva, agrégala a esa lista** o nunca se ejecutará.

### Seeds

Los seeds **borran y recrean** el contenido de su RAP, incluidos los intentos de quiz asociados. Sirven para una base nueva o local. **No los ejecutes en producción**: se perdería el historial de los aprendices.

### RAPs duplicados en producción

En la base del VPS hay RAPs repetidos: varias filas ocupando el mismo módulo y la misma posición. La base local está sana (6 RAPs, uno por posición).

El diagnóstico del 14 de septiembre encontró dos filas sobrantes, las dos **inactivas y sin progreso ni intentos de quiz**:

| Posición | Fila sobrante | Detalle |
|---|---|---|
| Módulo 2 · 2 (RAP 3) | `dc9c7dea` | Copia con 5 ejercicios duplicados |
| Módulo 3 · 2 (RAP 5) | `0da2ae35` | Es la fila original, vacía; la activa es la que creó el seed (`e3f72737`) |

- **Causa:** la tabla `rap` no tiene restricción única por módulo y posición, y la migración que reubicó los 6 RAPs de los 6 niveles originales en 4 módulos movió filas a posiciones que los seeds ya habían ocupado.
- **Por qué parecían copias exactas:** los paneles de RAPs muestran un título canónico según la posición, no el título guardado. Ahora cada tarjeta repetida lleva la etiqueta **Duplicado** junto con su título real y su identificador.
- **Diagnóstico** (solo lectura, no modifica nada):
  ```bash
  php database/diagnostico_raps.php
  ```
  Muestra cuánto contenido y cuánta actividad de aprendices depende de cada fila.

> **No borres filas de `rap` a mano.** El progreso, los intentos de quiz y los de ejercicios de los aprendices cuelgan de ellas. La limpieza debe ser una migración escrita a partir de la salida del diagnóstico, que traslade esa actividad a la fila que se conserve.

## Progreso del aprendiz

El **módulo es la unidad de progreso**. El mapa muestra un solo camino de cuatro momentos por módulo y la página del RAP reúne el vocabulario, los diálogos, los ejercicios y el quiz de todos los RAPs del módulo; por eso el avance se guarda en todos esos RAPs a la vez. En los módulos de dos RAPs, ambos tienen siempre el mismo avance.

| Momento | Avance guardado | Cuándo se guarda |
|---|---|---|
| 1. Warm-Up | 25% | Al emparejar las tres tarjetas |
| 2. Absorption | 50% | Al pasar a la práctica |
| 3. Practice | 75% | Al responder el último ejercicio |
| 4. Quiz | 100% y completado | Solo al aprobar el quiz |

- El avance nunca retrocede, y desde el navegador no se puede guardar más de 75%.
- Si el aprendiz sale a mitad de la práctica, al volver sigue en el primer ejercicio que no ha respondido, con los puntos que ya había ganado. Si el RAP ya estaba terminado, la práctica empieza de cero (repetición, HU14).
- El quiz del módulo reúne las preguntas de todos sus RAPs y las califica todas.
- Un módulo se desbloquea cuando el anterior llega al 80%.
- Si la sesión expira mientras estudia (30 minutos sin actividad), lo que intente guardar queda en espera y un aviso le pide volver a iniciar sesión en otra pestaña. Al volver y pulsar *"Ya inicié sesión: guardar"* se envía todo lo pendiente, incluido un quiz completo.

> Antes del 14 de septiembre el avance se guardaba solo en el primer RAP de cada módulo: los módulos de dos RAPs se quedaban en 50% y nadie podía pasar del Módulo 2. La migración `2026_09_14_progreso_por_modulo.sql` sincroniza a los aprendices que quedaron atascados.

## Roles

| Rol | Qué puede hacer |
|---|---|
| **Aprendiz** | Se registra solo; cursa los RAPs; ve su perfil, su progreso, el glosario y su vocabulario difícil |
| **Instructor** | Lo crea el administrador. Consulta el progreso y los resultados de sus aprendices, exporta CSV. **No modifica contenido ni cuentas** |
| **Administrador** | Gestiona usuarios, programas, niveles, RAPs, vocabulario, ejercicios, diálogos, quizzes, catálogos y gamificación |

Admin e instructor pueden abrir un RAP como aprendiz en **modo vista previa**: lo recorren completo sin que se guarde progreso ni intentos.

## Seguimiento pedagógico del instructor (HU06 / HU23)

### "Mis aprendices"

El instructor ve a los aprendices **del programa de formación que tiene asignado** (el administrador lo asigna al crear o editar la cuenta). Si no tiene programa, ve a todos los aprendices y la pantalla le avisa que pida que se lo asignen. Esto aplica al dashboard, a *Mis Aprendices*, a *Resultados Quiz* y al CSV.

### Filtros

Nivel, RAP y estado se pueden combinar libremente. El estado se calcula sobre los RAPs activos del alcance filtrado (el curso completo, un módulo o un RAP):

| Estado | Significa |
|---|---|
| Completado | Terminó **todos** los RAPs del alcance |
| En progreso | Tiene actividad en alguno, pero no los terminó todos |
| Sin iniciar | No tiene actividad en ninguno |

El **avance promedio** cuenta en 0% los RAPs que el aprendiz no ha empezado, igual que en su propio perfil.

### Avance por módulo y por RAP

Sin filtros, la tabla muestra una columna por módulo (M1 a M4), con el detalle de cada RAP al pasar el cursor. Al filtrar por un módulo o un RAP, muestra una columna por cada RAP del alcance.

### Resultados de quiz y CSV

*Resultados Quiz* lista todos los intentos y los ejercicios con mayor tasa de error del grupo. El CSV (separado por `;`, UTF-8 con BOM para Excel) incluye:

ID del aprendiz · nombre · correo · ficha SENA · módulo · quiz · puntaje · puntaje mínimo · aprobado · fecha · duración · número de intento · detalle de respuestas

El CSV exporta exactamente los filtros de la pantalla. Los textos que empiezan por `=`, `+`, `-` o `@` se escriben con un apóstrofo delante, para que Excel no los ejecute como fórmula: nombres y respuestas los escriben los aprendices.

## Estado de las historias de usuario

Revisión del 14 de septiembre de 2026 contra el código de `main`.

| HU | Historia | Estado | Qué falta |
|---|---|---|---|
| HU01 | Módulos por nivel de dificultad | ✅ Completa | Corregida y desplegada el 14 de septiembre: el avance se guarda en cada momento, la práctica se retoma y los módulos de dos RAPs ya desbloquean el siguiente |
| HU02 | Pronunciación con IPA y palabras difíciles | ✅ Completa | El audio usa síntesis de voz, no archivos MP3. El repaso de palabras marcadas está en *Mi Vocabulario* |
| HU03 | Previsualizar y publicar RAPs | ✅ Completa | — |
| HU04 | Gestión de usuarios | ✅ Completa | — |
| HU05 | Panel de progreso personal | ✅ Completa | — |
| HU06 | Seguimiento pedagógico y reportes | ✅ Completa | — |
| HU07 | Retroalimentación inmediata e insignias | ✅ Completa | — |
| HU08 | Login seguro y recuperación de contraseña | ✅ Completa | Confirmar que la cookie de sesión viaja con `Secure` detrás del proxy del VPS |
| HU09 | Cuentas de instructor con clave temporal | ✅ Completa | — |
| HU10 | Editar los niveles precargados | ✅ Completa | Adaptada a 4 módulos según `contenidos.md` (la HU original habla de 6 niveles) |
| HU11 | Glosario con filtros | ✅ Completa | Audio con síntesis de voz |
| HU12 | Los 6 tipos de ejercicios | ✅ Completa | — |
| HU13 | Diálogos con audio sincronizado | 🟡 Parcial | Hay reproducción completa, por turno y detener; faltan pausa y retroceso |
| HU14 | Repetir RAPs conservando el mejor puntaje | 🟡 Parcial | El mejor puntaje se conserva, pero no existe el botón "Repetir RAP" |
| HU15 | Nivel de perfil, leaderboard y heatmap | ✅ Completa | — |
| HU16 | Registro público de aprendices | 🟡 Parcial | No se envía correo de confirmación al registrarse |
| HU17 | Programas de formación | ✅ Completa | — |
| HU18 | Catálogos de áreas clínicas y categorías | 🟡 Parcial | Las áreas y categorías desactivadas siguen saliendo como opción al crear vocabulario: el controlador pide solo las activas, pero el modelo ignora ese parámetro |
| HU19 | Vocabulario médico por RAP | 🟡 Parcial | Falta el campo "traducción del ejemplo"; audio e imagen no se exigen al publicar |
| HU20 | Crear ejercicios (admin) | 🟡 Parcial | Faltan instrucciones, configurar máximo de intentos y puntaje, y previsualización |
| HU21 | Crear diálogos (admin) | 🟡 Parcial | Faltan anotaciones pedagógicas y audio individual por turno |
| HU22 | Configurar quizzes (admin) | 🟡 Parcial | Aleatorizar no se configura ni se aplica; el temporizador ignora el límite configurado; el máximo de intentos no se valida; no hay borrado lógico de quiz ni de preguntas |
| HU23 | Progreso de aprendices y CSV | ✅ Completa | — |

**Resumen:** 15 completas y 8 parciales, de 23 historias. Ninguna está sin empezar.

## Pendientes para cerrar el proyecto

Ordenados por impacto en los aprendices.

1. **Limpiar los RAPs duplicados del VPS.** Ya están diagnosticados (ver [RAPs duplicados en producción](#raps-duplicados-en-producción)); falta la migración que retire las dos filas sobrantes y añada la restricción única.
2. **Arreglos rápidos:** las áreas y categorías desactivadas siguen como opción al crear vocabulario (HU18), el temporizador del quiz está fijo en 5 minutos (HU22) y el registro no envía correo de confirmación (HU16).
3. **Corregir las tildes perdidas en la base del VPS.** Hoy se parchean palabra por palabra con `normalizarTextoEspanol()` en `includes/funciones.php`. Conviene hacerlo antes de cargar el RAP 6.
4. **Cargar el contenido del RAP 6** (Módulo 4). No tiene seed; el guion completo está en `contenidos.md`. Incluye la Grammar Pill del Módulo 4.
5. **Criterios parciales** de HU13, HU14, HU16, HU18, HU19, HU20, HU21 y HU22 (tabla anterior).
6. **Alcance de `contenidos.md` que no está en `hu.md`:** PRE-TEST inicial, POS-TEST global y "El Desafío" (grabación de audio del aprendiz en cada módulo).

## Cómo trabajamos

- **`main` es producción.** Prueba en local antes de hacer push y confirma en el VPS después.
- Haz pull antes de empezar para no pisar el trabajo de los demás.
- Commits pequeños, con mensaje en el formato `tipo(área): descripción` (`feat`, `fix`, `chore`…).
- Los scripts `.sh` deben quedar con saltos de línea LF (lo fuerza `.gitattributes`); si no, el contenedor no arranca.
- Nunca subas `.env` ni ejecutes seeds contra la base de producción.

## Cambios recientes

**14 de septiembre de 2026**
- **Progreso por módulo:** nadie podía pasar del Módulo 2, porque el avance se guardaba solo en el primer RAP de cada módulo y el siguiente exige 80%. Ahora el avance y la aprobación del quiz cuentan para todos los RAPs del módulo, el quiz califica todas las preguntas que muestra, cada momento guarda su avance al terminarlo y la práctica se retoma donde quedó. La migración `2026_09_14_progreso_por_modulo.sql` desatasca a quienes ya estaban bloqueados.
- **Sesión expirada:** si la sesión caducaba, el avance, los ejercicios y el quiz se perdían sin aviso, porque las peticiones de la página recibían la página de login como respuesta. Ahora el servidor responde `401` a esas peticiones y el RAP muestra un aviso: lo pendiente se guarda al volver a iniciar sesión. En *Mi Vocabulario* y el *Glosario* la estrella avisa y lleva al login.
- HU23 completa: el instructor ve solo a los aprendices de su programa; los filtros de nivel, RAP y estado se pueden combinar sin errores y ya no esconden a quien no ha empezado; avance por RAP visible al filtrar; selectores sin módulos repetidos; CSV protegido contra fórmulas.
- Paneles de RAPs: aviso **Duplicado** con el título y el identificador reales, y la fila activa primero.
- Nuevo `database/diagnostico_raps.php` para revisar la tabla `rap` sin modificarla.

**31 de agosto de 2026**
- HU15: el leaderboard semanal del perfil se actualiza solo cada 20 segundos.
