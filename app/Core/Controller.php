<?php
namespace App\Core;

/**
 * Controller.php
 * Controlador base del framework MVC.
 * Proporciona métodos para renderizar vistas, redirigir de forma segura y gestionar sesiones.
 */
abstract class Controller {

    public function __construct() {
        // Asegurar que la ruta base esté definida de forma dinámica
        if (!defined('PROYECTO_PATH')) {
            $folder_name = basename(dirname(__DIR__, 2));
            $script_name = $_SERVER['SCRIPT_NAME'] ?? '';
            if (strpos($script_name, '/' . $folder_name) === 0) {
                define('PROYECTO_PATH', '/' . $folder_name);
            } else {
                define('PROYECTO_PATH', '');
            }
        }
    }

    /**
     * Renderiza una vista y le inyecta un array de datos asociativo de forma limpia.
     * @param string $vista Nombre de la vista (ej: 'auth/login')
     * @param array $datos Datos pasados a la vista
     */
    protected function render(string $vista, array $datos = []): void {
        // Extraer los datos a variables locales en el ámbito de la vista
        extract($datos);

        $archivoVista = dirname(__DIR__) . '/Views/' . $vista . '.php';

        if (file_exists($archivoVista)) {
            // Cargar cabecera, vista y pie de página si corresponde
            // Nota: Si la vista no es de layout completo (como peticiones AJAX), se puede cargar directo
            require_once $archivoVista;
        } else {
            http_response_code(404);
            die("La vista '{$vista}' no existe en el sistema.");
        }
    }

    /**
     * Redirige de forma segura a una URL relativa respetando la ruta dinámica del proyecto.
     * @param string $ruta Ruta de destino
     */
    protected function redirect(string $ruta): void {
        $destino = PROYECTO_PATH . '/' . ltrim($ruta, '/');
        header('Location: ' . $destino);
        exit;
    }

    /**
     * Indica si la petición la hizo el JavaScript de la página y espera JSON,
     * en lugar de ser una navegación normal del navegador.
     */
    protected function esPeticionAjax(): bool {
        $conXhr   = strcasecmp($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '', 'XMLHttpRequest') === 0;
        $pideJson = stripos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false;

        return $conXhr || $pideJson;
    }
}
