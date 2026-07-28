<?php
/**
 * credenciales.php
 * Configuración de entorno y credenciales para Base de Datos, SMTP y JWT.
 * Lee las configuraciones de seguridad de manera dinámica desde el archivo .env
 */

// Cargar variables de entorno si existe la librería
if (class_exists(\Dotenv\Dotenv::class)) {
    // Buscar .env en la raíz del proyecto
    $dotenv = \Dotenv\Dotenv::createImmutable(dirname(__DIR__));
    try {
        $dotenv->load();
    } catch (\Exception $e) {
        // Fallback silencioso si no hay .env (podría estar en un entorno que las inyecta directo en $_ENV)
    }
}

// Base de Datos
define('DB_HOST', $_ENV['DB_HOST'] ?? $_SERVER['DB_HOST'] ?? getenv('DB_HOST') ?: 'localhost');
define('DB_NOMBRE', $_ENV['DB_NOMBRE'] ?? $_SERVER['DB_NOMBRE'] ?? getenv('DB_NOMBRE') ?: 'smash_code');
define('DB_USUARIO', $_ENV['DB_USUARIO'] ?? $_SERVER['DB_USUARIO'] ?? getenv('DB_USUARIO') ?: 'root');
define('DB_CLAVE', $_ENV['DB_CLAVE'] ?? $_SERVER['DB_CLAVE'] ?? getenv('DB_CLAVE') ?: '');
define('DB_CHARSET', $_ENV['DB_CHARSET'] ?? $_SERVER['DB_CHARSET'] ?? getenv('DB_CHARSET') ?: 'utf8mb4');

// Configuración de SMTP
define('SMTP_HOST', $_ENV['SMTP_HOST'] ?? $_SERVER['SMTP_HOST'] ?? getenv('SMTP_HOST') ?: 'smtp.gmail.com');
define('SMTP_USER', $_ENV['SMTP_USER'] ?? $_SERVER['SMTP_USER'] ?? getenv('SMTP_USER') ?: ''); 
define('SMTP_PASS', $_ENV['SMTP_PASS'] ?? $_SERVER['SMTP_PASS'] ?? getenv('SMTP_PASS') ?: ''); 
define('SMTP_PORT', $_ENV['SMTP_PORT'] ?? $_SERVER['SMTP_PORT'] ?? getenv('SMTP_PORT') ?: 587);

// Configuración de JWT
define('JWT_SECRET', $_ENV['JWT_SECRET'] ?? $_SERVER['JWT_SECRET'] ?? getenv('JWT_SECRET') ?: 'CAMBIAME_EN_EL_ARCHIVO_ENV');

// Configuración de Inteligencia Artificial (Gemini API)
define('GEMINI_API_KEY', $_ENV['GEMINI_API_KEY'] ?? $_SERVER['GEMINI_API_KEY'] ?? getenv('GEMINI_API_KEY') ?: '');

// Configuración de inicio de sesión con Google (OAuth 2.0)
define('GOOGLE_CLIENT_ID', $_ENV['GOOGLE_CLIENT_ID'] ?? $_SERVER['GOOGLE_CLIENT_ID'] ?? getenv('GOOGLE_CLIENT_ID') ?: '');
define('GOOGLE_CLIENT_SECRET', $_ENV['GOOGLE_CLIENT_SECRET'] ?? $_SERVER['GOOGLE_CLIENT_SECRET'] ?? getenv('GOOGLE_CLIENT_SECRET') ?: '');
define('GOOGLE_REDIRECT_URI', $_ENV['GOOGLE_REDIRECT_URI'] ?? $_SERVER['GOOGLE_REDIRECT_URI'] ?? getenv('GOOGLE_REDIRECT_URI') ?: '');
