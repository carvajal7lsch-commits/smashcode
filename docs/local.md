# Desarrollo local en Windows

Requisitos: Git, Composer, PHP 8.2 o posterior en PATH y MySQL de Wamp instalado. El lanzador usa una instancia aislada en 127.0.0.1:3308; no cambia los servicios de Wamp. No usarlo contra producción.

1. Ejecutar `composer install` desde la raíz del repositorio.
2. Crear `.env` (ignorado por Git) con estos valores:

```dotenv
APP_ENV=local
DB_HOST=127.0.0.1
DB_PUERTO=3308
DB_NOMBRE=smash_code
DB_USUARIO=root
DB_CLAVE=
MAIL_ENABLED=false
SESSION_COOKIE_SECURE=false
JWT_SECRET=reemplazar_por_un_secreto_aleatorio_de_al_menos_32_caracteres
GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
GOOGLE_REDIRECT_URI=http://127.0.0.1:8097/login/google/callback
```

3. Ejecutar `powershell -ExecutionPolicy Bypass -File tools/start-local.ps1`.
4. Abrir http://127.0.0.1:8097. Las credenciales de aprendiz, instructor y admin se generan en `.local/local-users.json`. El sistema identifica el rol automáticamente con el correo y la contraseña.

El primer arranque crea la base, ejecuta migraciones, carga contenido y genera cuentas de prueba. Los siguientes arranques conservan los datos y solo ejecutan migraciones idempotentes. Los seeds borran contenido: no ejecutarlos de nuevo sobre una base con progreso. Los logs y las sesiones están en `.local/` y no se versionan. Para usar un directorio de datos existente, definir `LOCAL_MYSQL_DATADIR` en `.env`. El lanzador verifica `@@datadir` antes de ejecutar mantenimiento.

El correo y Google OAuth requieren credenciales propias; no están habilitados en este entorno. En HTTPS configurar `SESSION_COOKIE_SECURE=true`; si hay proxy, configurar únicamente sus IPs reales en `TRUSTED_PROXIES`. Para Docker servido por HTTP, definir `SESSION_COOKIE_SECURE=false` explícitamente.

## Verificación

Con el servidor local arrancado, Python y Node disponibles:

```powershell
python tests/regression.py --base-url http://127.0.0.1:8097
```

Se pueden indicar `--php`, `--node` y `--artifact-root`. Las pruebas crean y eliminan sus propios datos de prueba en la base local. Cubren perfiles, revocación de sesiones, clave temporal, CSRF, prerrequisitos, seis tipos de ejercicios, intentos, XP, reloj del quiz, peticiones repetidas y los cuatro módulos. Guardan el resultado en `.local/test-results/results.json` por defecto.

## Cambios para desplegar esta rama

Antes de activar el código en otro entorno, respaldar la base y ejecutar `php database/migrar.php`. La nueva migración añade sesiones de práctica/quiz y resultados idempotentes; conserva el contenido y progreso existente. El despliegue invalida sesiones antiguas: los usuarios deben volver a entrar. No ejecutar seeds durante el despliegue.

La política existente del instructor sin programa asignado se conserva. El login no solicita seleccionar un perfil; la autorización y el panel de destino usan el rol guardado en la base de datos.

## Integridad del contenido y reportes

Publicar exige tres palabras para Warm-Up, ejercicios válidos por tipo, diálogos con turnos y un quiz evaluable. Las actividades se reúnen por módulo. Para dejar temporalmente incompleto un RAP, despublicarlo primero. Las ediciones de contenido publicado se validan dentro de la transacción y se revierten si dejan actividades inutilizables.

Los intentos nuevos guardan el mínimo de aprobación y los enunciados originales. Los intentos anteriores a esta migración conservan puntaje, aprobación y respuestas; el criterio original queda vacío en CSV y el enunciado se indica como no disponible, porque no puede recuperarse con certeza. No se inventan valores históricos a partir del quiz actual.

## Archivos y entrega de claves temporales

Las subidas de vocabulario validan el contenido y el formato según el campo: audio MP3/OGG/WAV e imagen JPG/PNG/SVG estático, hasta 2 MB. Una subida fallida conserva el registro y los archivos anteriores. Los SVG nuevos no admiten scripts, eventos ni recursos externos; los archivos ya existentes se conservan.

Las comprobaciones de estas correcciones se ejecutan con `python tests/additional-regression.py --artifact-root .local/additional-test-results`. Admiten `--php` para elegir el intérprete y crean y eliminan únicamente sus cuentas, registros y archivos propios.

Si no se entrega la clave temporal por correo, el administrador recibe un aviso y ve la clave una sola vez en la lista de usuarios. Puede generar otra con el botón de clave temporal para un aprendiz o instructor activo. Esta acción reemplaza la clave, invalida las sesiones existentes y exige cambiarla al ingresar. La clave no se incluye en URLs ni logs. En local se conserva `MAIL_ENABLED=false`; esta alternativa permite probar las altas sin enviar correos reales.

## Correos e integración con Google

Los correos de bienvenida, recuperación, clave temporal y bloqueo comparten una plantilla HTML con versión de texto plano, botón y enlace alternativo. Configurar `APP_URL` con la URL pública de la aplicación para sus enlaces; en local, `http://127.0.0.1:8097`. El remitente predeterminado es `SMTP_USER`; `SMTP_FROM_EMAIL` permite usar otro remitente autorizado por el proveedor. Puerto 587 usa STARTTLS y 465 usa TLS implícito.

Para Gmail: configurar `SMTP_HOST=smtp.gmail.com`, `SMTP_PORT=587`, `SMTP_USER` y `SMTP_PASS` con una contraseña de aplicación; después activar `MAIL_ENABLED=true`. No guardar secretos en Git. La recuperación muestra un aviso global cuando faltan configuración o credenciales, sin revelar si una cuenta existe.

`php tools/preview-mails.php --output=.local/mail-previews` genera cuatro ejemplos ficticios. Añadir `--send-to=tu-cuenta-de-prueba@gmail.com` envía únicamente las tres pruebas de bienvenida, recuperación y clave temporal. Los ejemplos no crean usuarios ni modifican contraseñas; el enlace de recuperación y la clave de ejemplo no son válidos. Un resultado `enviado=true` confirma aceptación SMTP, no llegada a la bandeja de entrada.

`python tests/mail-regression.py --artifact-root .local/mail-test-results` comprueba PHPMailer contra SMTP de loopback sin enviar mensajes a Internet. El transporte `MAIL_TRANSPORT=local` se permite exclusivamente con `APP_ENV=local` y SMTP en loopback. La aplicación usa `MAIL_TRANSPORT=smtp` para Gmail o cualquier proveedor real.

El acceso con Google necesita su cliente OAuth web, `GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET` y `GOOGLE_REDIRECT_URI=http://127.0.0.1:8097/login/google/callback`, registrado exactamente en Google Cloud. SMTP y OAuth son configuraciones independientes. Las cuentas creadas con Google que todavía no tienen contraseña reciben instrucciones específicas al intentar acceder por contraseña, sin acumular intentos fallidos por esa situación.

`python tests/integrations-http-regression.py --artifact-root .local/integration-test-results` comprueba estos mensajes y el rechazo de callbacks inválidos. Exige correo deshabilitado y Google sin configurar; crea y retira una cuenta ficticia propia. No sustituye una prueba real con el proveedor.

## Iconografía

Los iconos que antes usaban emojis se sirven desde `assets/icons/smashcode.svg`, mediante `icono_svg()`, y un SVG independiente para el indicador de progreso. Se conserva la mascota vectorial y los iconos de Font Awesome existentes. Los SVG decorativos se ocultan a los lectores de pantalla y mantienen el texto de cada acción.
