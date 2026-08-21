<?php
/**
 * credenciales.php
 * Configuración del entorno local (Ignorado por Git por motivos de seguridad).
 */

// Base de Datos
define('DB_HOST', 'localhost');
define('DB_NOMBRE', 'smash_code');
define('DB_USUARIO', 'root');
define('DB_CLAVE', '');
define('DB_CHARSET', 'utf8mb4');

// Configuración de SMTP (Gmail)
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_USER', 'smascode@gmail.com');
define('SMTP_PASS', ''); // Tu Contraseña de Aplicación de Gmail
define('SMTP_PORT', 587);

// Configuración de JWT (Genera una clave segura con: php -r "echo bin2hex(random_bytes(48));")
define('JWT_SECRET', 'TU_CLAVE_JWT_SECRETA_AQUI');

// Inicio de sesión con Google (OAuth 2.0). Se obtienen en Google Cloud Console.
define('GOOGLE_CLIENT_ID', '');
define('GOOGLE_CLIENT_SECRET', '');
define('GOOGLE_REDIRECT_URI', 'http://localhost/smashcode/login/google/callback');
