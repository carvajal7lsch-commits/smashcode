<?php
/**
 * conexion.php
 * Configuración de la conexión a la base de datos usando PDO.
 * @return PDO Instancia de la conexión
 */

require_once __DIR__.'/bootstrap.php';

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


/**
 * Retorna una conexión PDO configurada con consultas preparadas.
 * Se lanza excepción ante cualquier error (modo ERRMODE_EXCEPTION).
 */
function obtenerConexion(): PDO {
    static $conexion = null; // Patrón singleton liviano

    if ($conexion === null) {
        $dsn = 'mysql:host=' . DB_HOST
             . ';port=' . (defined('DB_PUERTO') ? DB_PUERTO : 3306)
             . ';dbname=' . DB_NOMBRE
             . ';charset=' . DB_CHARSET;

        $opciones = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,  // Lanza excepciones ante errores SQL
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,         // Resultados como arrays asociativos
            PDO::ATTR_EMULATE_PREPARES   => false,                    // Consultas preparadas reales
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci', // Forzar codificación UTF-8
        ];

        try {
            $conexion = new PDO($dsn, DB_USUARIO, DB_CLAVE, $opciones);
        } catch (PDOException $e) {
            /* En producción: registrar en log, nunca mostrar al usuario */
            error_log('[SmashCode] Error de conexión: ' . $e->getMessage());
            http_response_code(500);
            die(json_encode(['error' => 'Error interno del servidor. Intenta más tarde.']));
        }
    }

    return $conexion;
}
