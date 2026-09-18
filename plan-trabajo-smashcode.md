# Plan de trabajo de SmashCode antes de comenzar las correcciones

Fecha: 18 de septiembre de 2026.

Este archivo reúne la preparación, las decisiones pendientes y el orden de las correcciones conocidas. Su objetivo es mejorar la plataforma conservando cuentas, roles, contenidos, intentos y progreso. La creación de este documento no modifica la aplicación.

## 1. Punto de partida

- Proyecto local: `C:\Users\crist\Music\smashcode`.
- Rama de trabajo: `fix/local-auth-learning`.
- Versión local de referencia: `29312b8`.
- Aplicación local: `http://127.0.0.1:8097`.
- Base de datos local utilizada en la revisión anterior: `smash_code`, puerto `3308`.
- La revisión anterior registró 343 comprobaciones aprobadas. Este resultado corresponde a aquella entrega; las nuevas tareas de WhatsApp todavía requieren implementación y verificación.
- La selección manual de aprendiz, instructor y administrador se retiró del acceso. Debemos conservar la identificación del rol por el servidor y comprobar que ningún formulario permita elevar permisos.

Ya se corrigieron la advertencia del rango en el mapa, la racha que mostraba un valor fijo y el enlace del ranking. Esos puntos se comprobarán nuevamente al revisar la pantalla, sin considerarlos fallos nuevos pendientes de implementación.

## 2. Preparación obligatoria antes del primer cambio

- [ ] Comprobar la rama, los cambios locales y las actualizaciones remotas. Revisar cualquier aporte nuevo antes de incorporarlo; conservar el trabajo de otras personas.
- [ ] Registrar la versión que servirá como punto de retorno y guardar una copia privada de la base de datos, la configuración y los archivos subidos que puedan verse afectados.
- [ ] Confirmar que la aplicación y las pruebas apuntan a la base local correcta. Las pruebas que crean usuarios, consumen enlaces o cambian contraseñas deben ejecutarse en una base aislada.
- [ ] Preparar cuentas locales de prueba para aprendiz, instructor y administrador, con programas y asignaciones suficientes para recorrer sus funciones.
- [ ] Revisar el comportamiento actual de las pantallas afectadas en tema claro, tema oscuro, escritorio y móvil; guardar evidencia inicial cuando ayude a comparar.
- [ ] Aislar el correo de las pruebas automáticas. El envío SMTP está habilitado en el entorno local: no deben salir mensajes a cuentas ajenas ni a direcciones ficticias usadas por las pruebas.
- [ ] Mantener contraseñas, claves de Google, configuración privada y copias de datos fuera de los archivos entregables y de Git.
- [ ] Revisar los requisitos del curso antes de cambiar ejercicios o respuestas, especialmente `contenidos.md` para listening.

Las migraciones deben conservar los datos y poder ejecutarse nuevamente sin efectos inesperados. No ejecutar los seeds sobre la base existente para corregir un ejercicio.

## 3. Decisiones que necesitamos resolver

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

## 4. Orden de implementación y criterios de aceptación

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

## 5. Comprobaciones que deben acompañar los cambios

- **Acceso y roles:** inicio y cierre de sesión, registro y rechazo del acceso a funciones de otro rol, incluso mediante peticiones directas.
- **Formularios:** datos válidos, vacíos, letras en ficha, longitudes excedidas, valores fuera del catálogo y desacuerdo entre ficha y programa. Verificar también el rechazo cuando se omite la validación del navegador.
- **Aprendiz:** mapa, perfil, prácticas, quiz, glosario, listening y conservación de intentos, experiencia y progreso.
- **Instructor:** alumnos y programas asignados, consulta de resultados, exportaciones y rechazo del acceso a datos fuera de su alcance.
- **Administración:** usuarios, niveles, edición, vista previa y clave temporal, incluyendo cancelar, doble clic y errores de validación.
- **Interfaz:** tema claro y oscuro, móvil, teclado, foco de los diálogos y legibilidad de mensajes. Los cambios visuales simples se comprobarán directamente en pantalla.
- **Correo:** entrega, claridad del mensaje, destino del enlace, consumo, vencimiento y protección frente a reutilización. Comprobar que los relojes y la vigencia real sean coherentes.
- **Datos y migraciones:** comparar los datos relevantes antes y después y repetir las migraciones que se introduzcan para verificar que no alteran registros nuevamente.

Ejecutar las pruebas existentes que correspondan a cada cambio y añadir casos cuando haya comportamiento nuevo que proteger. Antes de entregar el conjunto, ejecutar las comprobaciones de regresión relevantes. No presentar las 343 comprobaciones anteriores como evidencia de tareas aún sin verificar.

## 6. Situación actual de los correos

El usuario confirmó que los tres mensajes de diseño llegaron a Gmail. Esos mensajes indicaban datos ficticios y sus claves y enlaces no sirven para entrar o recuperar una cuenta.

Después se solicitó una recuperación real desde el flujo local. Se comprobó que el enlace era válido y que la contraseña existente seguía intacta en ese momento. Falta confirmar la recepción de ese mensaje y completar el cambio de contraseña y el inicio de sesión. Si el enlace ya venció, debe solicitarse uno nuevo desde la aplicación.

El acceso con Google sigue pendiente de configuración y prueba con credenciales reales. Que el correo SMTP funcione no demuestra que el acceso con Google esté configurado.

## 7. Pendientes para una etapa posterior

Estos puntos deben quedar registrados, pero no son requisitos para comenzar los ajustes de formularios e interfaz:

- Completar y clasificar el vocabulario de las áreas clínicas sin términos, con revisión del responsable del contenido.
- Preparar los audios nativos que falten y verificar su reproducción. La prioridad del audio cargado y el respaldo mediante síntesis ya se ajustaron; la disponibilidad del material sigue siendo una tarea de contenido.
- Definir el alcance de pretest, postest y roleplay grabado antes de desarrollar esas funciones.
- Comprobar compatibilidad con el entorno de despliegue real, incluyendo MariaDB/XAMPP si se utiliza, Apache, HTTPS y configuración de sesiones.
- Revisar en producción las migraciones, las copias de seguridad y la configuración antes de publicar. La integración con `main` y el despliegue serán pasos posteriores acordados con el usuario.

## 8. Forma de cerrar cada bloque

1. Implementar un cambio acotado y revisar su efecto en las pantallas y datos relacionados.
2. Ejecutar sus comprobaciones y registrar el resultado, incluyendo cualquier limitación pendiente.
3. Revisar el contenido del commit para excluir datos privados y cambios ajenos al bloque.
4. Guardar y subir el avance a `fix/local-auth-learning` después de comprobarlo.
5. Actualizar la lista de pendientes con evidencia de lo terminado.

**Primer paso práctico:** preparar el entorno y acordar el catálogo y las reglas de ficha. Con esas decisiones, comenzar el bloque de formularios; los ajustes independientes de contraste y botones pueden avanzar mientras se define el catálogo.

## 9. Documentos de referencia

- `tareas-whatsapp-smashcode.md`: tareas escritas y capturas revisadas.
- `revision-actualizacion-smashcode.md`: revisión de aportes recientes y correcciones incorporadas.
- `informe-fallas-pendientes-smashcode.md` e `informe-fallas-adicionales-smashcode.md`: antecedentes de las auditorías; contrastar sus pendientes con los informes de arreglos posteriores.
- `guia-local-smashcode.md`: ejecución del entorno local.
- `configurar-gmail-google-local.md`: configuración y estado de las integraciones.
- `resultado-pruebas-actualizacion.json` y `prueba-recuperacion-real.json`: evidencia de verificaciones anteriores, con el alcance y el momento de cada comprobación.

Este inventario recoge lo identificado hasta ahora. El recorrido completo y las pruebas pueden revelar otros fallos; cada hallazgo debe añadirse con su evidencia y prioridad.
