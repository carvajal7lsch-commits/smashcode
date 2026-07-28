<?php
namespace App\Core;

use PDO;
use PDOException;

/**
 * Model.php
 * Modelo base del framework MVC.
 * Se encarga de instanciar y compartir la conexión PDO a la base de datos de manera segura y centralizada.
 */
abstract class Model {
    protected static ?PDO $conexion = null;

    /**
     * Retorna una conexión PDO singleton configurada con consultas preparadas reales.
     */
    public static function obtenerConexion(): PDO {
        if (self::$conexion === null) {
            // Cargar credenciales si no han sido definidas aún
            if (!defined('DB_HOST')) {
                $rutaCredenciales = dirname(__DIR__, 2) . '/config/credenciales.php';
                $rutaPlantilla = dirname(__DIR__, 2) . '/config/credenciales.example.php';
                
                if (file_exists($rutaCredenciales)) {
                    require_once $rutaCredenciales;
                } else {
                    require_once $rutaPlantilla;
                }
            }

            $dsn = 'mysql:host=' . DB_HOST
                 . ';dbname=' . DB_NOMBRE
                 . ';charset=' . DB_CHARSET;

            $opciones = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,  // Lanza excepciones en errores SQL
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,         // Resultados como arrays asociativos
                PDO::ATTR_EMULATE_PREPARES   => false,                    // Consultas preparadas reales nativas
            ];

            try {
                self::$conexion = new PDO($dsn, DB_USUARIO, DB_CLAVE, $opciones);
            } catch (PDOException $e) {
                // Registrar error de conexión
                error_log('[SmashCode MVC] Error de conexión: ' . $e->getMessage());
                http_response_code(500);
                die(json_encode(['error' => 'Error de conexión interno del servidor. Intenta más tarde.']));
            }
        }

        return self::$conexion;
    }
}
