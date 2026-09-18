<?php
/**
 * sesion.php
 * Configuración y funciones de gestión de sesión.
 * Seguridad: HttpOnly, SameSite=Strict, expiración por inactividad (RF02).
 */

// Definir la ruta base del proyecto de manera dinámica (funciona en raíz del dominio/puerto o en subcarpetas)
if (!defined('PROYECTO_PATH')) {
    $folder_name = basename(dirname(__DIR__));
    $script_name = $_SERVER['SCRIPT_NAME'] ?? '';
    if (strpos($script_name, '/' . $folder_name) === 0) {
        define('PROYECTO_PATH', '/' . $folder_name);
    } else {
        define('PROYECTO_PATH', '');
    }
}


/* --- Configuración de cookies seguras --- */
ini_set('session.cookie_httponly', 1);       // Evita acceso JS a la cookie
ini_set('session.cookie_samesite', 'Strict'); // Protección CSRF
ini_set('session.use_strict_mode', 1);        // Solo IDs de sesión generados por el servidor
ini_set('session.gc_maxlifetime', 1800);      // 30 minutos de inactividad

// Obligar HTTPS para cookies si estamos sobre protocolo seguro
// Solo confiar en el proxy declarado por el despliegue, nunca en un header arbitrario.
$proxies = array_filter(array_map('trim', explode(',', $_ENV['TRUSTED_PROXIES'] ?? (getenv('TRUSTED_PROXIES') ?: ''))));
$httpsProxy = in_array($_SERVER['REMOTE_ADDR'] ?? '', $proxies, true)
    && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https';
if ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $httpsProxy
    || filter_var($_ENV['SESSION_COOKIE_SECURE'] ?? (getenv('SESSION_COOKIE_SECURE') ?: 'false'), FILTER_VALIDATE_BOOLEAN)) {
    ini_set('session.cookie_secure', 1);
}

define('TIEMPO_INACTIVIDAD', 1800); // 30 minutos en segundos

/**
 * Inicia la sesión de forma segura.
 */
function iniciarSesion(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Verificar expiración por inactividad
    if (isset($_SESSION['ultima_actividad'])) {
        if (time() - $_SESSION['ultima_actividad'] > TIEMPO_INACTIVIDAD) {
            cerrarSesion();
            return;
        }
    }
    $_SESSION['ultima_actividad'] = time();

    // Prevenir caché del navegador en páginas con sesión
    if (!headers_sent()) {
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Cache-Control: post-check=0, pre-check=0', false);
        header('Pragma: no-cache');
    }
}

/**
 * Cierra la sesión y destruye todos los datos.
 */
function cerrarSesion(): void {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params['path'], $params['domain'],
            $params['secure'], $params['httponly']
        );
    }
    if (session_status() === PHP_SESSION_ACTIVE) session_destroy();
}

/**
 * Verifica si el usuario está autenticado.
 */
function estaAutenticado(): bool {
    iniciarSesion();
    if (empty($_SESSION['usuario_id'])) return false;
    static $identidadVerificada = null;
    $identidad = $_SESSION['usuario_id'] . ':' . ($_SESSION['huella_clave'] ?? '');
    if ($identidadVerificada === $identidad) return true;
    $stmt = obtenerConexion()->prepare('SELECT rol, activo, eliminado, bloqueado, contrasena, debe_cambiar_clave FROM usuarios WHERE id = ?');
    $stmt->execute([$_SESSION['usuario_id']]);
    $usuario = $stmt->fetch();
    if (!$usuario || !$usuario['activo'] || $usuario['eliminado'] || $usuario['bloqueado']
        || $usuario['rol'] !== ($_SESSION['rol'] ?? '')
        || !hash_equals(hash('sha256', (string) $usuario['contrasena']), $_SESSION['huella_clave'] ?? '')) {
        cerrarSesion();
        return false;
    }
    $_SESSION['debe_cambiar_clave'] = (bool) $usuario['debe_cambiar_clave'];
    $identidadVerificada = $identidad;
    return true;
}

// Llamar tras autenticar o cambiar la clave propia; otras sesiones se revocan.
function actualizarHuellaSesion(): void {
    $stmt = obtenerConexion()->prepare('SELECT contrasena, debe_cambiar_clave FROM usuarios WHERE id = ?');
    $stmt->execute([$_SESSION['usuario_id']]);
    $usuario = $stmt->fetch();
    $_SESSION['huella_clave'] = hash('sha256', (string) ($usuario['contrasena'] ?? ''));
    $_SESSION['debe_cambiar_clave'] = (bool) ($usuario['debe_cambiar_clave'] ?? false);
}

/**
 * Redirige al login si el usuario no está autenticado.
 */
function requerirAutenticacion(): void {
    if (!estaAutenticado()) {
        header('Location: ' . PROYECTO_PATH . '/login');
        exit;
    }
}

/**
 * Verifica si el usuario tiene el rol requerido.
 * @param string|array $roles Rol o arreglo de roles permitidos
 */
function requerirRol($roles): void {
    requerirAutenticacion();
    $roles = (array) $roles;
    if (!in_array($_SESSION['rol'] ?? '', $roles, true)) {
        http_response_code(403);
        header('Location: ' . PROYECTO_PATH . '/index.php?error=acceso_denegado');
        exit;
    }
}

/**
 * Obtiene el rol del usuario en sesión.
 */
function obtenerRolSesion(): string {
    return $_SESSION['rol'] ?? '';
}
