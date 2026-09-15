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

Los audios e imágenes que se suben desde el panel se guardan en `assets/uploads/`, que en Docker es el volumen `uploads`. Sin ese volumen se perdían en cada despliegue. El entrypoint crea la carpeta y se la asigna a `www-data` antes de arrancar Apache. Localmente la carpeta está en `.gitignore`.

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

`smash_code.sql` crea las tablas con `CREATE TABLE IF NOT EXISTS`, así que **añadir una columna ahí no la crea en las bases que ya existen** (la local de cada uno y la del VPS). Todo cambio de esquema necesita también su migración. Así se perdió la columna `activo` de los catálogos (ver HU18).

### Probar una migración de datos antes de desplegarla

```bash
php database/probar_migracion.php database/migraciones/<archivo>.sql
```

Ejecuta la migración dentro de una transacción, muestra cuántas filas cambiaría por columna (con ejemplos de antes y después) o insertaría por tabla, y la revierte. Solo acepta `UPDATE`, `INSERT`, `SET @variable` y `SELECT`: una sentencia de estructura (`ALTER`, `CREATE`, `PREPARE`…) confirmaría la transacción y la prueba se niega a correr. Cada sentencia debe terminar con `;` al final de una línea. Para probarla en el VPS, súbela **sin agregarla todavía a `migrar.php`**, córrela desde *Open Terminal* y regístrala en un segundo push.

### Tildes y caracteres especiales

El contenido del VPS se cargó por un camino que **borró los caracteres no ASCII**: "Módulo" quedó "Mdulo", la IPA `/θərˈmɒmɪtər/` quedó `/rmmtr/` y se perdieron los `¡¿`, las rayas y los emojis. Algunas insignias quedaron además mal codificadas ("Estudiante Ã‰lite"). La base del VPS sí guarda tildes (MySQL 8, `utf8mb4`).

La migración `2026_09_15_restaurar_tildes.sql` restauró 124 filas. Solo reemplaza un texto si coincide byte a byte con la forma dañada de un texto de los seeds, así que lo editado a mano no se toca. El archivo está en ASCII con los textos en hexadecimal.

- **No pegues contenido con tildes en la terminal de Dokploy.** Súbelo como archivo (seed o migración) o créalo desde el panel de administración.
- `normalizarTextoEspanol()` en `includes/funciones.php` parchaba el texto dañado al mostrarlo. Con la base restaurada ya no hace falta, pero sigue activa dentro de `limpiar()`. Retirarla es una decisión pendiente del equipo, porque también modifica lo que escriben los usuarios (un nombre "Ramirez" se guarda como "Ramírez").

### Seeds

Los seeds **borran y recrean** el contenido de su RAP, incluidos los intentos de quiz asociados. Sirven para una base nueva o local. **No los ejecutes en producción**: se perdería el historial de los aprendices.

El **RAP 6** no tiene seed: su contenido lo carga la migración `2026_09_15_contenido_rap6.sql`, que solo inserta si el RAP 6 existe y está vacío, y nunca borra. En una base nueva, si corres los seeds después de `migrar.php`, vuelve a correr `migrar.php` para que cargue el RAP 6. Para cargar contenido nuevo en producción, usa una migración así, nunca un seed.

### RAPs duplicados en producción

En la base del VPS quedaron RAPs repetidos: varias filas ocupando el mismo módulo y la misma posición. La base local está sana (6 RAPs, uno por posición).

El diagnóstico del 14 de septiembre encontró dos filas sobrantes, las dos **inactivas y sin progreso ni intentos de quiz**:

| Posición | Fila sobrante | Detalle |
|---|---|---|
| Módulo 2 · 2 (RAP 3) | `dc9c7dea` | Copia con 5 ejercicios duplicados |
| Módulo 3 · 2 (RAP 5) | `0da2ae35` | Es la fila original, vacía; la activa es la que creó el seed (`e3f72737`) |

La migración `2026_09_14_retirar_raps_duplicados.sql` las retira junto con su contenido. Solo retira una fila si está inactiva, tiene una fila activa en su misma posición y ningún aprendiz depende de ella (sin progreso, intentos de ejercicio o de quiz, respuestas ni vocabulario marcado). Cuando ya no quedan posiciones repetidas, añade la restricción única `uk_rap_posicion (nivel_id, orden)`: desde entonces la base rechaza un segundo RAP en una posición ocupada.

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
- Si el aprendiz sale a mitad de la práctica, al volver sigue en el primer ejercicio que no ha respondido, con los puntos que ya había ganado. Si el RAP ya estaba terminado, la práctica empieza de cero.
- **Repetir un RAP (HU14):** un módulo completado muestra el botón *Repetir RAP* en el mapa, en la página del RAP y en los resultados del quiz. Abre la página con `repetir=1`, y en esa visita se trabaja como la primera vez:
  - se empieza en el Momento 1 con los demás bloqueados y la barra en 0%;
  - la práctica arranca de cero;
  - cada quiz queda como un intento nuevo.

  En la base no baja nada: el avance y el completado se quedan y el mejor puntaje solo sube. En un RAP sin completar o en vista previa, `repetir=1` no hace nada.
- **Intentos del quiz por ronda (HU22):** el administrador define el máximo de intentos de cada quiz.
  - **Cuándo empieza una ronda:** al terminar la práctica (Momento 3), que guarda `progreso.ronda_quiz_desde`. Cuentan los intentos reprobados desde entonces.
  - **Al agotarlos:** el servidor rechaza el quiz y la página ofrece *Repasar el RAP*. Al terminar otra vez la práctica empieza una ronda nueva.
  - **Quien ya aprobó:** no tiene límite.
  - **Orden de las preguntas:** si el quiz está marcado para aleatorizar, cambia en cada visita. La calificación va por el id de cada pregunta.
- **Preguntas borradas:** borrar una pregunta del quiz la desactiva (`pregunta.activo = 0`). Deja de verse y de calificarse, pero las respuestas históricas se conservan.

### Insignias (HU07)

Cada insignia se identifica por su `criterio`, y el código busca ese valor, no el nombre. Al aprobar un quiz solo se anuncian las insignias **nuevas**.

| Insignia | Se gana | `criterio` |
|---|---|---|
| Primer Nivel | Aprobar el Módulo 1 con 90% o más | `quiz_modulo_1 >= 90` |
| Handover Specialist | Aprobar el Módulo 2 con 90% o más | `quiz_modulo_2 >= 90` |
| Clinical Communicator | Aprobar el Módulo 3 con 90% o más | `quiz_modulo_3 >= 90` |
| Care Evaluator | Aprobar el Módulo 4 con 90% o más | `quiz_modulo_4 >= 90` |
| Quiz Perfecto | Sacar 100% en un quiz | `puntaje_quiz = 100` |
| Vocabulario Pro | Sumar 30 palabras en RAPs completados (se llega al aprobar el Módulo 2) | `vocabulario_aprendido >= 30` |
| Estudiante Élite | Completar todos los RAPs activos | `modulos_completados = todos` |
| Racha 7 Días | Estudiar 7 días seguidos (se revisa al abrir el perfil) | `racha_dias >= 7` |

La migración `2026_09_15_insignias_por_modulo.sql` creó las tres insignias de módulo, corrigió los criterios y entregó las insignias que los aprendices ya se habían ganado.
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
| HU07 | Retroalimentación inmediata e insignias | ✅ Completa | Cada módulo da su insignia al aprobarlo con 90% o más; "Vocabulario Pro" y "Estudiante Élite" ya se otorgan |
| HU08 | Login seguro y recuperación de contraseña | ✅ Completa | Confirmar que la cookie de sesión viaja con `Secure` detrás del proxy del VPS |
| HU09 | Cuentas de instructor con clave temporal | ✅ Completa | — |
| HU10 | Editar los niveles precargados | ✅ Completa | Adaptada a 4 módulos según `contenidos.md` (la HU original habla de 6 niveles) |
| HU11 | Glosario con filtros | ✅ Completa | Audio con síntesis de voz |
| HU12 | Los 6 tipos de ejercicios | ✅ Completa | — |
| HU13 | Diálogos con audio sincronizado | ✅ Completa | Pausa, línea anterior y reanudar. Reanudar repite la línea en pausa desde el principio, porque la pausa nativa de la síntesis de voz no es confiable en todos los navegadores |
| HU14 | Repetir RAPs conservando el mejor puntaje | ✅ Completa | Botón "Repetir RAP" en el mapa, en la página del RAP y en los resultados del quiz |
| HU15 | Nivel de perfil, leaderboard y heatmap | ✅ Completa | — |
| HU16 | Registro público de aprendices | ✅ Completa | Si el correo de confirmación no sale, la cuenta igual queda activa y el aviso no lo promete |
| HU17 | Programas de formación | ✅ Completa | — |
| HU18 | Catálogos de áreas clínicas y categorías | ✅ Completa | Al editar una palabra se sigue mostrando el área o categoría desactivada que ya tenía asignada, para no perderla al guardar |
| HU19 | Vocabulario médico por RAP | 🟡 Parcial | Falta el campo "traducción del ejemplo"; audio e imagen no se exigen al publicar |
| HU20 | Crear ejercicios (admin) | ✅ Completa | Instrucciones, intentos y puntaje configurables, botón *Previsualizar* (abre la práctica en vista previa) y borrado lógico. El puntaje queda configurado, pero el XP de cada acierto sigue viniendo de la configuración de gamificación |
| HU21 | Crear diálogos (admin) | ✅ Completa | Anotaciones pedagógicas, audio subido por turno (si no hay, suena la voz sintetizada) y borrado lógico de diálogos y turnos |
| HU22 | Configurar quizzes (admin) | ✅ Completa | La publicación es por RAP (HU03): el panel marca *Incompleto* si el quiz no tiene preguntas y hay vista previa, pero no impide publicarlo |
| HU23 | Progreso de aprendices y CSV | ✅ Completa | — |

**Resumen:** 22 completas y 1 parcial, de 23 historias. Ninguna está sin empezar.

## Pendientes para cerrar el proyecto

Ordenados por impacto en los aprendices.

1. **Criterios parciales** de HU19 (tabla anterior).
2. **Decidir si se retira `normalizarTextoEspanol()` de `limpiar()`** (ver *Tildes y caracteres especiales*).
3. **Alcance de `contenidos.md` que no está en `hu.md`:** PRE-TEST inicial, POS-TEST global y "El Desafío" (grabación de audio del aprendiz en cada módulo).

## Cómo trabajamos

- **`main` es producción.** Prueba en local antes de hacer push y confirma en el VPS después.
- Haz pull antes de empezar para no pisar el trabajo de los demás.
- Commits pequeños, con mensaje en el formato `tipo(área): descripción` (`feat`, `fix`, `chore`…).
- Los scripts `.sh` deben quedar con saltos de línea LF (lo fuerza `.gitattributes`); si no, el contenedor no arranca.
- Nunca subas `.env` ni ejecutes seeds contra la base de producción.

## Cambios recientes

**15 de septiembre de 2026**
- **HU21 · Diálogos:** el formulario del panel agrega anotaciones pedagógicas y audio por turno (MP3, OGG o WAV de hasta 2 MB).
  - **En la página del RAP:** si el turno tiene audio se reproduce el archivo; si no, o si falla, suena la voz sintetizada.
  - **Al editar:** los turnos conservan su id y los que se quitan quedan desactivados.
  - **Al eliminar un diálogo:** se desactiva.
  - **Error corregido:** el panel de diálogos fallaba si un diálogo no tenía contexto.
  - **Migración:** `2026_09_15_dialogos_anotaciones_y_turnos.sql`.
  - **Volumen nuevo `uploads` en `docker-compose.yml`:** lo subido ya no se pierde al desplegar.
- **HU20 · Ejercicios:** el formulario del panel agrega instrucciones (se muestran al aprendiz), máximo de intentos y puntaje, y el botón *Previsualizar*. Eliminar un ejercicio ahora lo desactiva. Se corrigen dos errores:
  - eliminar un ejercicio que algún aprendiz ya había respondido fallaba, porque sus intentos lo impedían;
  - una opción sin retroalimentación rompía la página del RAP para los aprendices.

  La migración `2026_09_15_ejercicio_instrucciones.sql` agrega la columna; después del pull, corre `php database/migrar.php`.
- **HU13 · Diálogos:** botones *Pause*, *Resume* y *Previous Line* mientras suena un diálogo. Corregido además un error: al pulsar *Stop* o tocar un turno suelto, el diálogo completo podía seguir sonando, porque el navegador dispara el fin de la línea al cancelar la voz.
- **HU07 · Insignias:** cada módulo da su insignia al aprobarlo con 90% o más (antes solo el Módulo 1). "Vocabulario Pro" se gana con 30 palabras y "Estudiante Élite" al completar todos los módulos; antes ningún código las otorgaba. Ya no se repite el anuncio de una insignia que el aprendiz tenía. La migración `2026_09_15_insignias_por_modulo.sql` entrega las insignias ya ganadas.
- **HU22 · Quizzes:** el máximo de intentos se valida por ronda, se puede aleatorizar el orden de las preguntas y borrar una pregunta es lógico. La migración `2026_09_15_quiz_rondas_y_preguntas_activas.sql` agrega `progreso.ronda_quiz_desde` y `pregunta.activo`, y da una ronda nueva a quien ya podía presentar el quiz. **Después de hacer pull, corre `php database/migrar.php`**: sin esas columnas la página del RAP falla.
- **HU14 · Repetir RAP:** botón en los módulos completados (mapa, página del RAP y resultados del quiz). El repaso reinicia los momentos solo en esa visita y en la base conserva el avance y el mejor puntaje.
- **RAP 6 (Módulo 4):** tenía la fila pero ningún contenido. La migración `2026_09_15_contenido_rap6.sql` carga lo que pide `contenidos.md`, y la página del Módulo 4 tiene su propia Grammar Pill (*Medical Advice* frente a *Reporting Results*). Se probó en seco en el VPS: 68 filas nuevas. Contenido cargado:
  - 16 palabras con IPA; las del Warm-Up van primero;
  - el diálogo de alta de Mr. Thomas;
  - 9 ejercicios: dictados del *Discharge Summary*, lectura de la *Nursing Checklist*, modales, emparejar, ordenar y El Desafío;
  - un quiz de 6 preguntas.
- **Tildes del VPS:** la migración `2026_09_15_restaurar_tildes.sql` restaura las tildes, signos, emojis y la pronunciación IPA que se borraron al cargar el contenido (124 filas; las 49 IPA vuelven a leerse). Se probó antes en el VPS con el nuevo `database/probar_migracion.php`.
- **HU18 · catálogos:** las áreas clínicas y categorías desactivadas ya no salen al crear vocabulario ni en los filtros del glosario. Las bases creadas antes no tenían la columna `activo` (por eso el panel de Catálogos escondía el botón de desactivar); la migración `2026_09_14_activo_en_catalogos.sql` la añade con todo activo.
- **HU22 · quiz:** el temporizador usa el límite configurado por el administrador (antes siempre 5 minutos) y la bienvenida muestra el número real de preguntas.
- **HU16 · registro:** el aprendiz recibe un correo de confirmación. Los correos ahora van en UTF-8 (las tildes llegaban dañadas, también en la recuperación de contraseña) y esperan como máximo 10 segundos al servidor de correo.

**14 de septiembre de 2026**
- **Progreso por módulo:** nadie podía pasar del Módulo 2, porque el avance se guardaba solo en el primer RAP de cada módulo y el siguiente exige 80%. Ahora el avance y la aprobación del quiz cuentan para todos los RAPs del módulo, el quiz califica todas las preguntas que muestra, cada momento guarda su avance al terminarlo y la práctica se retoma donde quedó. La migración `2026_09_14_progreso_por_modulo.sql` desatasca a quienes ya estaban bloqueados.
- **Sesión expirada:** si la sesión caducaba, el avance, los ejercicios y el quiz se perdían sin aviso, porque las peticiones de la página recibían la página de login como respuesta. Ahora el servidor responde `401` a esas peticiones y el RAP muestra un aviso: lo pendiente se guarda al volver a iniciar sesión. En *Mi Vocabulario* y el *Glosario* la estrella avisa y lleva al login.
- **RAPs duplicados:** la migración `2026_09_14_retirar_raps_duplicados.sql` retira las dos filas sobrantes del VPS, inactivas y sin actividad de aprendices, y añade una restricción única para que no vuelva a pasar.
- HU23 completa: el instructor ve solo a los aprendices de su programa; los filtros de nivel, RAP y estado se pueden combinar sin errores y ya no esconden a quien no ha empezado; avance por RAP visible al filtrar; selectores sin módulos repetidos; CSV protegido contra fórmulas.
- Paneles de RAPs: aviso **Duplicado** con el título y el identificador reales, y la fila activa primero.
- Nuevo `database/diagnostico_raps.php` para revisar la tabla `rap` sin modificarla.

**31 de agosto de 2026**
- HU15: el leaderboard semanal del perfil se actualiza solo cada 20 segundos.
