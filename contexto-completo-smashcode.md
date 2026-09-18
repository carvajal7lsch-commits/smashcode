# SmashCode: contexto completo, correcciones y trabajo pendiente

Actualizado el 18 de septiembre de 2026. Documento consolidado de las auditorías de la web, los arreglos entregados, la revisión de aportes recientes, las pruebas de correo y las tareas recibidas por WhatsApp.

Este documento permite continuar el proyecto sin tener que reconstruir la conversación ni consultar cada informe anterior. Incluye todos los hallazgos documentados hasta esta fecha, su estado y el plan para abordarlos. No equivale a una certificación de que la plataforma no tenga otros errores.

## Cómo interpretar los estados

- **Corregido:** hay un cambio implementado y comprobaciones registradas en la rama de trabajo. Su funcionamiento en producción debe verificarse cuando corresponda.
- **Pendiente:** falta implementar el cambio o completar el contenido.
- **Por verificar:** existe una comprobación pendiente; no significa que se haya demostrado una falla.
- **Por definir:** hace falta acordar una regla de negocio o el alcance antes de construirla.

Prioridades: **alta**, para integridad de datos, acceso, evaluación y validaciones; **media**, para usabilidad, presentación y recorridos pendientes; **planificada**, para contenido o funcionalidades que requieren una etapa propia. La prioridad de un fallo ya corregido describe su importancia histórica, no una urgencia abierta.

## 1. Situación del proyecto y recorrido de lo realizado

Proyecto local: `C:\Users\crist\Music\smashcode`. Rama: `fix/local-auth-learning`. Versión local comprobada al redactar este documento: `29312b8`. Dirección del entorno trabajado: `http://127.0.0.1:8097`. La base local habitual utiliza MySQL en el puerto `3308`; durante la revisión de actualizaciones se utilizó una copia aislada en `3309`.

La revisión de código inicial se realizó sobre `b163c91`, entonces referencia de `main`. Los informes que dicen que main sigue en esa versión son históricos. En la última consulta remota registrada, main ya estaba en `0e97024` y había recibido las publicaciones mediante PR #2 y #3. El commit correctivo `29312b8` quedó publicado en nuestra rama y pendiente de incorporar a main. Al redactar este archivo se comprobaron las referencias guardadas localmente; no se hizo una consulta nueva a GitHub ni se inspeccionó producción.

Hay un archivo de planificación sin seguimiento en el proyecto local, `plan-trabajo-smashcode.md`. Se conserva; no se atribuye su creación a otra persona ni se incorpora automáticamente. Este documento se entrega en la carpeta de resultados y no cambia el código.

| Etapa | Referencia | Resultado |
|---|---|---|
| Auditoría inicial | `b163c91` | 14 hallazgos H01–H14 y puntos de configuración/política que requerían confirmación. |
| Seguridad y aprendizaje local | `a6e8d5b` | Controles del servidor para cuentas, roles, actividades, evaluación, tiempo, XP, CSRF y mantenimiento; entorno local verificable. |
| Login único | `7c3d47e` | Retirada de la selección de perfiles; el servidor identifica el rol desde la cuenta. |
| Contenido y reportes | `16bb62b` | Corrección de N01–N07: publicación, edición, diálogos, histórico de quizzes, umbrales y dictado. 209 comprobaciones. |
| Usuarios y archivos | `7f9b1a0` | Corrección de N08–N10: entrega de claves, validación de usuarios y conservación de archivos. 253 comprobaciones. |
| Interfaz y mensajes | `317cb6c` | Sustitución de emojis por vectores, métricas y textos corregidos, plantillas y mensajes de integración. 287 comprobaciones. |
| Aportes recibidos | `2b17cbc`, `e336293` | Activación por correo, ayudas, etiquetas, contadores, arranque, indicadores, avisos, mapa y favicon. Se revisaron antes de incorporarlos en local. |
| Corrección de los aportes | `29312b8` | Nueve grupos de correcciones adicionales, incorporación local conservando datos y 343 comprobaciones. |
| Correos y WhatsApp | 18/09/2026 | Recepción confirmada de tres correos ficticios; recuperación real solicitada y enlace comprobado; tareas escritas y nueve capturas de WhatsApp revisadas. |

Las cantidades de comprobaciones son resultados de cada entrega; no se suman entre sí porque contienen pruebas repetidas. No hicimos un despliegue ni un merge a main durante esta revisión.

## 2. Inventario de la auditoría inicial: H01–H14

| ID | Módulo / prioridad histórica | Problema identificado | Estado y solución realizada | Límite o seguimiento |
|---|---|---|---|---|
| H01 | Login / media | Las pestañas de rol no tenían efecto funcional y confundían el acceso. | **Corregido.** Login sin selector; rol obtenido desde la base. | No se encontró elevación de privilegios causada por las pestañas originales. Conservar las comprobaciones del servidor. |
| H02 | Sesiones y usuarios / alta | Suspender, eliminar o cambiar el rol no revocaba una sesión abierta. | **Corregido.** Se controla el estado vigente de la cuenta y se revocan sesiones ante cambios relevantes, incluida contraseña. | Mantener la matriz de permisos en pruebas futuras. |
| H03 | Contraseña temporal / alta | Se podía entrar directamente al panel antes de cambiar la clave obligatoria. | **Corregido.** Restricción central de rutas y operaciones mientras exista la obligación. | Falta completar una prueba real de correo y acceso temporal con una cuenta de prueba adecuada. |
| H04 | Módulos y RAPs / alta | Los prerrequisitos se bloqueaban visualmente, pero podían omitirse mediante URL o petición directa. | **Corregido.** Regla compartida de acceso para lectura y escritura, publicación y estado del nivel. | El umbral configurable se completó con N06. |
| H05 | Progreso y rondas / alta | Enviar 75% permitía saltar actividades y reiniciar intentos del quiz. | **Corregido.** Hitos y rondas verificados por el servidor; repetir el porcentaje no abre otra ronda. | Conservar progreso anterior al cambiar contenidos. |
| H06 | Evaluación de ejercicios / alta | El servidor aceptaba el acierto declarado por el navegador sin una respuesta comprobable. | **Corregido.** Evaluación de los tipos de ejercicio en servidor; tratamiento explícito de la autoevaluación de roleplay. | La autoevaluación no demuestra desempeño ni sustituye una grabación revisada. |
| H07 | Intentos de ejercicios / media | El máximo configurado no se aplicaba. | **Corregido.** Límites vinculados a la ronda de práctica y comportamiento coherente en pantalla. | Mantener los límites al ajustar botones y ejercicios. |
| H08 | Contenido dinámico / media | Apóstrofos y comillas rompían manejadores de ejercicios y quiz. | **Corregido.** Manejo seguro del contenido dinámico; pruebas con comillas. | No volver a incrustar texto sin el escape correspondiente dentro de JavaScript. |
| H09 | XP y perfil / media | El XP mostrado en ejercicios no se persistía y se ignoraba su configuración. | **Corregido.** Recompensa verificada, persistida e idempotente desde el servidor. | Ajustes visuales no deben duplicar premios. |
| H10 | Tiempo del quiz / media | El servidor no controlaba el inicio y vencimiento del intento. | **Corregido.** Inicio y plazo propios del servidor; envío repetido no renueva el reloj ni duplica intentos. | Falta comprobar concurrencia real en el entorno de despliegue. |
| H11 | Publicación / media | Se publicaban RAPs sin evaluación utilizable. | **Corregido.** Validación de publicación ampliada posteriormente con N01–N04. | Revalidar el contenido que se modifique. |
| H12 | Escrituras del aprendiz / media | Faltaba validar CSRF en cuatro operaciones. | **Corregido.** Token requerido en escrituras del aprendiz, incluido inicio de quiz. | Las vistas previas de instructor/admin no escriben progreso ni métricas. |
| H13 | Datos personales / media | El filtro general cambiaba nombres legítimos, por ejemplo añadiendo tildes. | **Corregido.** Se separó el tratamiento de entradas de la reparación de textos pedagógicos. | Las nuevas reglas de ficha deben preservar los datos anteriores. |
| H14 | Mantenimiento / crítica potencial | Scripts de migración y seeds podían ejecutarse desde rutas bajo la raíz pública. | **Corregido en código/configuración versionada y comprobado en local.** Bloqueo de archivos internos y ejecución de mantenimiento solo por CLI. | **Por verificar en despliegue:** configuración efectiva de Apache. No probar una ruta destructiva ejecutándola en producción. Una raíz pública mínima queda como mejora de arquitectura. |

## 3. Fallos adicionales de la web: N01–N10

| ID | Módulo / prioridad histórica | Problema identificado | Estado y resultado |
|---|---|---|---|
| N01 | Quiz / alta | Editar un quiz publicado podía dejarlo sin preguntas. | **Corregido, `16bb62b`.** Edición inválida rechazada y revertida; vacío permitido únicamente en borrador. |
| N02 | Ejercicios / alta | Se guardaban ejercicios sin opciones o respuesta válida. | **Corregido, `16bb62b`.** Validación por tipo antes de guardar; una edición inválida conserva el ejercicio. |
| N03 | Publicación y progreso / alta | Se publicaba contenido sin vocabulario suficiente, ejercicios utilizables o conversaciones completas. | **Corregido, `16bb62b`.** Validación integral y protección frente a eliminaciones que dejarían incompleto un RAP publicado. |
| N04 | Diálogos / media | Se guardaban conversaciones activas sin turnos. | **Corregido, `16bb62b`.** Se exigen turnos válidos; una edición vacía no borra la conversación anterior. |
| N05 | Reportes del instructor / media | Cambiar preguntas o mínimo de aprobación alteraba el contexto mostrado de intentos antiguos. | **Corregido, `16bb62b`.** Los intentos nuevos guardan el criterio y enunciado originales. En registros antiguos sin evidencia, se informa que no está disponible; no se inventa el contexto perdido. |
| N06 | Desbloqueo de niveles / media | Se imponía 80% fijo aunque el siguiente nivel configurara otro umbral. | **Corregido, `16bb62b`.** Servidor, mapa y avisos usan el umbral configurado. |
| N07 | Dictado / media | Se usaba la primera opción, aunque otra estuviera marcada como correcta. | **Corregido, `16bb62b`.** Pantalla y servidor toman la respuesta marcada. La revisión de main precisó que la calificación errónea del distractor se reprodujo en la rama de arreglos; la discrepancia visual también existía en main. |
| N08 | Alta y clave temporal / media | Se anunciaba un alta exitosa sin informar que no se habían entregado las credenciales. | **Corregido, `7f9b1a0`.** Se distingue alta y entrega; alternativa protegida de un solo uso y generación de otra clave temporal, con revocación y cambio obligatorio. |
| N09 | Usuarios y referencias / media | Rol o programa inválidos producían excepciones SQL visibles. | **Corregido, `7f9b1a0`.** Roles permitidos, existencia/estado del programa, límites y errores de persistencia controlados. La regla numérica y catálogo de ficha siguen pendientes como tarea nueva. |
| N10 | Vocabulario y archivos / media | Una subida rechazada se anunciaba como guardada y podía perder la referencia al archivo anterior. | **Corregido, `7f9b1a0`.** Validación de tamaño, extensión y contenido; conservación de datos anteriores y limpieza de archivos nuevos si falla la persistencia. |

## 4. Mejoras entregadas y fallos de las actualizaciones recibidas

### Interfaz, correos y funcionamiento local ya trabajados

- **Corregido:** 26 usos de emojis encontrados se sustituyeron por SVG/vectores; colección local de 13 iconos, medallas y marca de Google vectoriales. Se conservaron la mascota SVG y Font Awesome. Este conteo corresponde a la revisión realizada, no garantiza que cualquier pantalla futura use siempre vectores.
- **Corregido:** métricas del dashboard administrativo excluyen cuentas eliminadas cuando corresponde; se retiraron incrementos ficticios y etiquetas incorrectas. El panel de instructor describe sus cuatro niveles y permisos reales.
- **Implementado:** plantillas coherentes de bienvenida, recuperación, clave temporal y bloqueo, con HTML/texto, instrucciones, botón y enlace alternativo. Se añadió posteriormente la activación por correo. Falta completar sus recorridos reales según la sección 6.
- **Corregido:** mensajes de Google sin configurar, cancelación, estado inválido y cuentas Google sin contraseña; recuperación sin revelar si una cuenta existe.
- **Implementado y probado en local:** arranque con puerto de base configurable, MySQL aislado y configuración de cookies/proxy explícita. Falta comprobar su configuración efectiva con HTTPS en producción.
- **Contexto del acceso local:** la base local y producción son entornos distintos. Que una cuenta entre en producción no demuestra que exista con la misma clave y estado en local. No se trasladó ni modificó la base de producción. No es posible mostrar una contraseña original a partir de su hash; la vía de acceso es comprobar credenciales o recuperar la clave.

### Correcciones de `29312b8`

Los aportes de `2b17cbc` y `e336293` añadieron activación, ayudas, etiquetas, avisos e indicadores. Se incorporaron después de revisar y corregir los siguientes grupos:

| ID de seguimiento | Módulo | Falla | Estado y solución |
|---|---|---|---|
| U01 | Migración de activación | Repetir migraciones verificaba todas las cuentas pendientes. | **Corregido.** Habilitación de cuentas anteriores una sola vez y protección de instalaciones que ya tenían activación. |
| U02 | Registro y SMTP | Una caída de correo verificaba automáticamente la cuenta, incluso en producción. | **Corregido.** Cuenta pendiente ante fallo; excepción limitada a demostración local con correo explícitamente desactivado. |
| U03 | Reenvío de activación | Una sesión pendiente podía seguir utilizándose tras cambiar el estado o datos de la cuenta. | **Corregido.** Validación de estado, rol, correo, huella de contraseña y antigüedad; CSRF y espera mínima de un minuto. |
| U04 | Tokens de activación | Tokens en texto y emisión que podía invalidar el anterior antes de completar el nuevo. | **Corregido.** SHA-256, transacciones, bloqueo de filas, consumo único y compatibilidad con enlaces antiguos vigentes. |
| U05 | Configuración | Herramientas requerían un archivo de credenciales que se había retirado del repositorio. | **Corregido.** Carga común de configuración para web, correo y herramientas, con entorno como alternativa. |
| U06 | Mapa y gamificación | Clave de rango inexistente, racha fija y ranking dirigido a una API JSON. | **Corregido.** Rango/icono adecuados, XP coherente con perfil, racha real y enlace al ranking del perfil. |
| U07 | Glosario | Contadores incluían vocabulario de RAPs inactivos ausente en la lista. | **Corregido.** Contadores y resultados utilizan el mismo criterio de actividad. |
| U08 | Ayudas y audio | Ayuda inválida descartada silenciosamente; no se reproducía el audio propio. | **Corregido.** Rechazo conservando el ejercicio; prioridad de archivo y síntesis como alternativa controlada. |
| U09 | Avisos | Fondo incorrecto en tema claro y foco que podía salir del diálogo. | **Corregido.** Variables de tema, contención de foco y retorno al control de origen. No sustituye las confirmaciones nativas todavía pendientes. |

También se reparó el formato de la configuración SMTP, cuyas comillas faltantes impedían cargar el entorno. Se conservó el valor de la contraseña; no se publicó la configuración privada.

## 5. Tareas de WhatsApp: inventario completo revisado

Fuente: mensajes escritos de Sebastián Carvajal entre las 00:24 y 00:39 y nueve capturas. Los identificadores W01–W13 se usan aquí para seguimiento y no son códigos del repositorio.

| ID | Módulo / prioridad | Solicitud o fallo | Estado actual | Trabajo y comprobación necesaria |
|---|---|---|---|---|
| W01 | Mapa / media | Revisar tarjetas de la barra derecha; captura con advertencia de rango. | **Parcialmente resuelto.** Advertencia, racha y ranking corregidos con U06; revisión visual restante pendiente. | Comprobar las tarjetas completas en ambos temas y móvil, conservando los indicadores reales. |
| W02 | Perfil aprendiz / media | Exceso de espacio vertical y scroll. | **Pendiente.** Mejora de presentación. | Compactar resumen, métricas y progreso sin ocultar información ni perder adaptación móvil. |
| W03 | Quiz / media | Botón inferior Continuar con estilo incompleto. | **Pendiente; confirmado en código.** | Añadir el estilo base compartido; conservar deshabilitado, avance, finalización, tiempo e intentos. |
| W04 | Completar frase / media | Opciones poco legibles en tema oscuro. | **Pendiente; reportado en captura.** | Contraste de texto y fondo en estados normal, seleccionado, correcto e incorrecto. |
| W05 | Listening / alta para la calidad de evaluación | El ejercicio con RAMIREZ revela la respuesta y reproduce la palabra en lugar del deletreo. | **Pendiente; confirmado en seed y vista.** | Revisar contenido de la base actual, retirar la respuesta visible y ofrecer el deletreo requerido. Conservar intentos y progreso mediante un cambio acotado. |
| W06 | Glosario / media | Opciones de selectores invisibles o poco legibles en oscuro. | **Pendiente; reportado en captura.** | Corregir control/opciones nativas en ambos temas y comprobar filtros, etiquetas y conteos. |
| W07 | Admin: usuarios / media | Bordes y textos de tarjetas en tema claro; estilo de clave temporal. | **Pendiente.** | Unificar presentación en tarjetas y tabla; comprobar filtros, controles y legibilidad. |
| W08 | Admin: clave temporal / media | Confirmación mediante ventana nativa del navegador. | **Pendiente; confirmado en ambas vistas.** | Diálogo propio con Cancelar/Confirmar, envío único, permisos, CSRF y foco. Cancelar no cambia contraseñas ni envía correo. |
| W09 | Admin: niveles / media | Sombras verdes excesivas y estilo incompleto de Prever. | **Pendiente; botón confirmado en código.** | Ajustar las sombras indicadas y clase base del botón; mantener foco visible y preview sin escritura de progreso. |
| W10 | Admin: editar nivel / media | Editar abre otra página; se solicita modal. | **Pendiente.** Mejora de flujo. | Reutilizar validación y permisos existentes, errores dentro del modal y cierre/cancelación sin cambios. |
| W11 | Registro / alta | Ficha admite letras; faltan límites coherentes y errores durante escritura. | **Pendiente; confirmado.** | Definir catálogo y regla; validación numérica/coherente en servidor y navegador, mensajes junto al campo y prevención de envío inválido. |
| W12 | Alta/edición y perfil Google / alta | Reglas de ficha distintas entre formularios. | **Pendiente; confirmado.** | Aplicar la misma regla acordada según rol y programa en registro, administración, alta de instructor y perfil posterior a Google. Preservar datos anteriores. |
| W13 | Panel instructor / media | No se pudo recorrer por falta de cuenta de instructor. | **Por verificar visualmente.** Existen pruebas funcionales de rutas y permisos. | Recorrer con cuenta local asignada: aprendices, niveles/RAPs, resultados, exportaciones y alcance de datos. |

**Nota de voz pendiente:** mensaje de las 00:17 sin transcripción disponible durante la revisión. No se afirma haberlo escuchado ni conocer sus requisitos. La lectura no incluyó mensajes antiguos que WhatsApp todavía no había sincronizado. El inventario cubre el bloque escrito disponible, sin enviar respuestas por WhatsApp.

## 6. Integraciones, contenido, políticas y límites pendientes

| ID | Área / prioridad | Lo comprobado o realizado | Lo que falta y estado |
|---|---|---|---|
| P01 | Gmail / media | SMTP configurado; tres correos de diseño aceptados y recepción confirmada por el usuario. | **Por verificar:** aspecto completo, claridad y uso desde Gmail. Las claves y enlaces ficticios nunca fueron accesos válidos. |
| P02 | Recuperación / alta para cerrar el flujo | Solicitud real local emitida; enlace válido comprobado entonces, sin cambiar la contraseña; sin error SMTP registrado. | **Por verificar:** recepción del mensaje real y recorrido hasta nueva clave e inicio de sesión. Solicitar otro si vence; comprobar vigencia y coherencia de relojes. |
| P03 | Activación y clave temporal / alta para cerrar el flujo | Plantillas, controles, pruebas aisladas y alternativa protegida de entrega implementados. | **Por verificar:** bienvenida/activación y clave temporal reales con cuenta de prueba, entrega, consumo y cambio obligatorio. No reemplazar la clave personal para una demostración. |
| P04 | Google / media | Flujo y errores locales revisados, sin credenciales del proveedor. | **Pendiente de configuración y prueba real:** cliente, secreto y callback exacto `http://127.0.0.1:8097/login/google/callback`. SMTP y OAuth son independientes. |
| P05 | Alcance instructor / por definir, alta si contradice la regla del negocio | Instructor sin programa conserva acceso global según la política documentada; con programa el alcance es por programa. | **Por definir:** acceso por programa, ficha o grupo y comportamiento sin asignación. No se presenta la política existente como vulnerabilidad nueva demostrada. |
| P06 | Cookies, Apache y despliegue / alta antes de publicar | Controles versionados y entorno local revisados. | **Por verificar:** bloqueo efectivo de archivos internos, cookie Secure con HTTPS/proxy de confianza y configuración real. No se inspeccionó el servidor de producción. |
| P07 | Compatibilidad y concurrencia / media, necesaria antes de publicar según entorno | Wamp/MySQL local probado; PHP 8.2 revisado. | **Por verificar:** MariaDB/XAMPP si se usa, Apache/HTTPS y concurrencia real. No afirmar compatibilidad completa por pasar sintaxis. |
| P08 | Vocabulario clínico / planificada | En la revisión había 65 términos: General 57 y Urgencias 8. | **Pendiente de contenido:** Cardiología, Cirugía, Farmacología, Obstetricia, Pediatría, Traumatología y UCI sin términos. Clasificar/ampliar con revisión del instructor. |
| P09 | Audios nativos / planificada | Audio propio tiene prioridad en ayudas y existe síntesis alternativa; subidas válidas y fallidas tienen pruebas locales. | **Pendiente de contenido y recorrido:** producir/cargar grabaciones y revisar audio real en los ejercicios afectados. |
| P10 | Pretest y postest / planificada | Identificados como alcance pendiente en el informe original. | **Por definir e implementar:** requisitos, contenido, calificación y seguimiento. Son funciones nuevas, no una regresión comprobada. |
| P11 | Roleplay grabado / planificada | La práctica existente contempla autoevaluación. | **Por definir e implementar:** grabación real, almacenamiento, revisión y permisos. La autoevaluación no cubre esta función. |
| P12 | Datos de producción / por verificar | No se consultó ni modificó la base del VPS. | **Por verificar:** posibles duplicidades mencionadas en el informe de traspaso y configuración/datos reales. No se confirman solo leyendo el repositorio. |
| P13 | Integración a main / posterior a validación | Nuestra corrección `29312b8` publicada en la rama; publicaciones recibidas ya estaban en main remoto en la última revisión. | **Pendiente:** actualizar referencias, revisar diferencias finales, preparar PR y acordar integración/despliegue. No asumir que producción coincide con main. |

### Evidencia de pruebas y conservación de datos

La última batería registrada contiene **343 comprobaciones aprobadas y ninguna fallida**:

| Grupo | Comprobaciones |
|---|---:|
| Plataforma y cuatro módulos | 209 |
| Usuarios y archivos | 44 |
| Plantillas y SMTP local | 25 |
| Google y recuperación sin integración real | 9 |
| Activación y migraciones | 20 |
| Reenvío HTTP | 16 |
| Mapa, etiquetas y ayudas | 15 |
| Audio de ayuda | 5 |
| Total | 343 |

También se registraron 92 archivos PHP con sintaxis válida en PHP 8.2.29, JavaScript generado revisado, 19 migraciones aplicadas y repetidas, y un recorrido visual del mapa. Las pruebas no demuestran recepción por Gmail ni ingreso por Google con un proveedor real.

En la incorporación de actualizaciones se conservaron 10 cuentas, sus contraseñas, 2 registros de progreso, 7 intentos de ejercicios, 1 intento de quiz y 65 términos. La habilitación inicial de activación verificó una cuenta antigua y cambió su indicador y fecha automática; no se reiniciaron datos ni se ejecutaron seeds. Estas cifras son una fotografía de esa revisión, no un conteo nuevo realizado para este documento.

Las tareas nuevas W02–W12 no quedan aprobadas por esa batería anterior. W01 tiene correcciones previas que requieren conservarse y W13 necesita el recorrido específico señalado.

## 7. Preparación obligatoria antes del primer cambio

- [ ] Comprobar la rama, los cambios locales y las actualizaciones remotas. Revisar cualquier aporte nuevo antes de incorporarlo; conservar el trabajo de otras personas.
- [ ] Registrar la versión que servirá como punto de retorno y guardar una copia privada de la base de datos, la configuración y los archivos subidos que puedan verse afectados.
- [ ] Confirmar que la aplicación y las pruebas apuntan a la base local correcta. Las pruebas que crean usuarios, consumen enlaces o cambian contraseñas deben ejecutarse en una base aislada.
- [ ] Preparar cuentas locales de prueba para aprendiz, instructor y administrador, con programas y asignaciones suficientes para recorrer sus funciones.
- [ ] Revisar el comportamiento actual de las pantallas afectadas en tema claro, tema oscuro, escritorio y móvil; guardar evidencia inicial cuando ayude a comparar.
- [ ] Aislar el correo de las pruebas automáticas. El envío SMTP está habilitado en el entorno local: no deben salir mensajes a cuentas ajenas ni a direcciones ficticias usadas por las pruebas.
- [ ] Mantener contraseñas, claves de Google, configuración privada y copias de datos fuera de los archivos entregables y de Git.
- [ ] Revisar los requisitos del curso antes de cambiar ejercicios o respuestas, especialmente `contenidos.md` para listening.

Las migraciones deben conservar los datos y poder ejecutarse nuevamente sin efectos inesperados. No ejecutar los seeds sobre la base existente para corregir un ejercicio.

## 8. Decisiones que necesitamos resolver

### Fichas y formularios: antes del primer bloque

1. **Origen de las fichas válidas:** definir qué catálogo utiliza el selector, quién lo administra y cómo se relaciona cada ficha con el programa. Que una ficha tenga solo números no demuestra que esté registrada o sea válida en el SENA.
2. **Obligatoriedad por caso:** precisar cuándo la ficha es requerida para aprendiz, instructor, creación administrativa y perfil completado después del acceso con Google.
3. **Longitud y almacenamiento:** definir un límite común. Guardar la ficha como texto numérico para conservar posibles ceros iniciales; rechazar letras, signos, decimales y notación científica.
4. **Datos existentes:** revisar fichas antiguas que no cumplan la nueva regla y acordar su regularización. No borrarlas ni cambiarlas silenciosamente.
5. **Límites de los demás campos:** alinear cada formulario con las reglas del servidor y el almacenamiento. Un nombre, un correo y una contraseña necesitan reglas distintas.

El catálogo es necesario antes de construir un selector de fichas registradas. Mientras se define, pueden avanzar los ajustes de contraste y botones que no dependen de esa decisión.

### Antes de los bloques correspondientes

- **Listening:** confirmar el contenido que debe escuchar el aprendiz y la respuesta aceptada. La documentación pide deletreo de nombre y correo; no obliga a utilizar el apellido Ramírez.
- **Correo funcional:** definir la cuenta local de prueba y qué flujo se va a ejecutar. No reemplazar la contraseña personal del usuario para demostrar una clave temporal.
- **Instructor:** confirmar las asignaciones de la cuenta de prueba y conservar la política vigente para instructores sin programa asignado hasta que se acuerde otro comportamiento.
- **WhatsApp:** falta conocer el contenido de la nota de voz de las 00:17. No se ha transcrito y podría contener requisitos adicionales; no impide trabajar en las tareas escritas ya revisadas.

## 9. Orden de implementación y criterios de aceptación

| Bloque | Trabajo | Cuándo se considera terminado |
|---|---|---|
| 1. Fichas y formularios | Compartir las reglas entre registro, alta y edición administrativa, alta de instructor y perfil posterior a Google. Implementar el catálogo acordado, límites coherentes y errores junto al campo durante la escritura. | Una ficha válida se acepta; una inválida se rechaza también al enviar una petición directamente al servidor. Los datos anteriores se conservan. Los mensajes son claros y los formularios se pueden usar con teclado y autocompletado. |
| 2. Contraste y botones | Corregir las opciones de completar frase, los selectores del glosario, las tarjetas de usuarios en tema claro, los botones Continuar y Prever, el estilo de clave temporal y las sombras verdes de niveles. | Texto y controles legibles en ambos temas y móvil. Se distinguen los estados seleccionado, correcto, incorrecto y deshabilitado. Continuar y Prever conservan su función. |
| 3. Confirmaciones y edición | Sustituir las confirmaciones nativas de clave temporal por un diálogo de la plataforma. Abrir la edición de nivel en un modal reutilizando las validaciones existentes. | Cancelar no envía solicitudes ni cambia datos. Confirmar envía una sola solicitud. Se mantienen los permisos y la protección CSRF. Los errores se muestran dentro del flujo y el foco vuelve al control de origen al cerrar. |
| 4. Perfil, mapa e instructor | Compactar el perfil conservando sus métricas y progreso; revisar las tarjetas del mapa; recorrer el panel de instructor y sus consultas, resultados y exportaciones. | La información sigue disponible con menos espacio desperdiciado. No aparecen advertencias en el mapa. El instructor ve únicamente los datos autorizados y completa sus funciones con las asignaciones de prueba. |
| 5. Listening | Quitar la respuesta visible del ejercicio señalado y reproducir el deletreo que corresponda al objetivo del curso. Revisar primero el contenido realmente cargado en la base. | El enunciado no revela la solución; el audio permite resolverla; la evaluación acepta las respuestas acordadas. El cambio conserva intentos y progreso y no requiere recargar todos los contenidos. |
| 6. Correos y Google | Probar bienvenida, activación, recuperación y clave temporal con enlaces o credenciales reales de prueba. Configurar y comprobar Google cuando estén disponibles sus credenciales. | Cada flujo se completa desde el correo hasta la aplicación; se prueban vencimiento y reutilización de enlaces. Google devuelve al entorno correcto y conserva las reglas de roles y perfil. |
| 7. Entrega de la rama | Revisar el conjunto, actualizar el informe, guardar los cambios en commits claros y subirlos a la rama de trabajo. Preparar el pull request cuando se solicite. | La rama contiene únicamente los cambios previstos y evidencia de las comprobaciones relevantes. Las migraciones y los pasos de actualización están documentados. |

Los bloques 1 a 5 corresponden a las tareas escritas de WhatsApp. El bloque 6 completa pendientes de las revisiones anteriores y depende, en parte, de cuentas y configuración externas.

## 10. Comprobaciones que deben acompañar los cambios

- **Acceso y roles:** inicio y cierre de sesión, registro y rechazo del acceso a funciones de otro rol, incluso mediante peticiones directas.
- **Formularios:** datos válidos, vacíos, letras en ficha, longitudes excedidas, valores fuera del catálogo y desacuerdo entre ficha y programa. Verificar también el rechazo cuando se omite la validación del navegador.
- **Aprendiz:** mapa, perfil, prácticas, quiz, glosario, listening y conservación de intentos, experiencia y progreso.
- **Instructor:** alumnos y programas asignados, consulta de resultados, exportaciones y rechazo del acceso a datos fuera de su alcance.
- **Administración:** usuarios, niveles, edición, vista previa y clave temporal, incluyendo cancelar, doble clic y errores de validación.
- **Interfaz:** tema claro y oscuro, móvil, teclado, foco de los diálogos y legibilidad de mensajes. Los cambios visuales simples se comprobarán directamente en pantalla.
- **Correo:** entrega, claridad del mensaje, destino del enlace, consumo, vencimiento y protección frente a reutilización. Comprobar que los relojes y la vigencia real sean coherentes.
- **Datos y migraciones:** comparar los datos relevantes antes y después y repetir las migraciones que se introduzcan para verificar que no alteran registros nuevamente.

Ejecutar las pruebas existentes que correspondan a cada cambio y añadir casos cuando haya comportamiento nuevo que proteger. Antes de entregar el conjunto, ejecutar las comprobaciones de regresión relevantes. No presentar las 343 comprobaciones anteriores como evidencia de tareas aún sin verificar.

## 11. Situación actual de los correos

El usuario confirmó que los tres mensajes de diseño llegaron a Gmail. Esos mensajes indicaban datos ficticios y sus claves y enlaces no sirven para entrar o recuperar una cuenta.

Después se solicitó una recuperación real desde el flujo local. Se comprobó que el enlace era válido y que la contraseña existente seguía intacta en ese momento. Falta confirmar la recepción de ese mensaje y completar el cambio de contraseña y el inicio de sesión. Si el enlace ya venció, debe solicitarse uno nuevo desde la aplicación.

El acceso con Google sigue pendiente de configuración y prueba con credenciales reales. Que el correo SMTP funcione no demuestra que el acceso con Google esté configurado.

## 12. Pendientes para una etapa posterior

Estos puntos deben quedar registrados, pero no son requisitos para comenzar los ajustes de formularios e interfaz:

- Completar y clasificar el vocabulario de las áreas clínicas sin términos, con revisión del responsable del contenido.
- Preparar los audios nativos que falten y verificar su reproducción. La prioridad del audio cargado y el respaldo mediante síntesis ya se ajustaron; la disponibilidad del material sigue siendo una tarea de contenido.
- Definir el alcance de pretest, postest y roleplay grabado antes de desarrollar esas funciones.
- Comprobar compatibilidad con el entorno de despliegue real, incluyendo MariaDB/XAMPP si se utiliza, Apache, HTTPS y configuración de sesiones.
- Revisar en producción las migraciones, las copias de seguridad y la configuración antes de publicar. La integración con `main` y el despliegue serán pasos posteriores acordados con el usuario.

## 13. Forma de cerrar cada bloque

1. Implementar un cambio acotado y revisar su efecto en las pantallas y datos relacionados.
2. Ejecutar sus comprobaciones y registrar el resultado, incluyendo cualquier limitación pendiente.
3. Revisar el contenido del commit para excluir datos privados y cambios ajenos al bloque.
4. Guardar y subir el avance a `fix/local-auth-learning` después de comprobarlo.
5. Actualizar la lista de pendientes con evidencia de lo terminado.

**Primer paso práctico:** preparar el entorno y acordar el catálogo y las reglas de ficha. Con esas decisiones, comenzar el bloque de formularios; los ajustes independientes de contraste y botones pueden avanzar mientras se define el catálogo.


## 14. Fuentes, alcance y mantenimiento de este documento

Se consolidaron la auditoría inicial, la revisión histórica de main, los informes N01–N10 y sus entregas de arreglos, la guía local, las mejoras de interfaz/correo, la revisión de actualizaciones, el estado de Gmail/Google, la evidencia JSON y el inventario escrito de WhatsApp. Se contrastaron con el historial y las referencias locales de Git. No se realizó una nueva auditoría de producción ni una nueva batería de pruebas al redactar este archivo.

Las siguientes actualizaciones prevalecen sobre textos históricos de los informes anteriores:

1. Las fallas H01–H14 y N01–N10 tienen correcciones entregadas con los límites indicados en sus tablas; no deben contarse automáticamente como fallas todavía abiertas.
2. Main ya había avanzado respecto a `b163c91` en la última revisión remota registrada. Falta consultar nuevamente GitHub antes de comenzar cambios o preparar el PR.
3. Los tres mensajes de diseño sí llegaron a Gmail, según confirmación del usuario. Esa recepción no convierte sus credenciales ficticias en accesos funcionales.
4. La activación por correo se añadió después de la entrega inicial de plantillas. La descripción antigua de un registro sin confirmación no representa el flujo posterior con correo habilitado.
5. El enlace de recuperación real se comprobó sin cambiar la contraseña. Su recepción y finalización no están confirmadas en la evidencia disponible.
6. Las correcciones ya entregadas no sustituyen los ajustes visuales ni las reglas nuevas solicitadas por WhatsApp.

Este será el documento de consulta general. Al completar un bloque, actualizar la fila correspondiente con su estado, commit, prueba y fecha; conservar los identificadores H, N, U, W y P para no perder contexto. Si aparece un fallo nuevo, añadir evidencia, módulo y prioridad antes de considerarlo confirmado.

No contiene contraseñas, tokens de acceso, credenciales de Google ni copias de datos. Las evidencias privadas y los respaldos deben seguir separados de los entregables y del repositorio.
