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
