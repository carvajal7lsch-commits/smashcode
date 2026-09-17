<?php
namespace App\Core;

/**
 * App.php
 * El Front Controller y enrutador principal del framework MVC.
 * Registra las rutas de la aplicación, limpia las peticiones URI y las despacha al controlador correspondiente.
 */
class App {
    protected array $rutas = [];

    /**
     * Registra una ruta GET.
     * @param string $ruta Ruta amigable (ej: '/login')
     * @param string $controladorMetodo Controlador y método en formato 'Controlador@metodo'
     */
    public function get(string $ruta, string $controladorMetodo): void {
        $this->rutas['GET'][$ruta] = $controladorMetodo;
    }

    /**
     * Registra una ruta POST.
     * @param string $ruta Ruta amigable (ej: '/login')
     * @param string $controladorMetodo Controlador y método en formato 'Controlador@metodo'
     */
    public function post(string $ruta, string $controladorMetodo): void {
        $this->rutas['POST'][$ruta] = $controladorMetodo;
    }

    /**
     * Ejecuta el enrutador y despacha la petición actual.
     */
    public function run(): void {
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

        // Obtener la URI actual de la petición
        $uri = $_SERVER['REQUEST_URI'] ?? '/';
        
        // Quitar la ruta del proyecto si está presente (ej: /SistemaSmashCode/login -> /login)
        if (PROYECTO_PATH !== '' && strpos($uri, PROYECTO_PATH) === 0) {
            $uri = substr($uri, strlen(PROYECTO_PATH));
        }

        // Limpiar los parámetros de consulta GET (?token=...)
        $partes = explode('?', $uri);
        $rutaLimpia = '/' . trim($partes[0], '/');

        // Si la ruta es directamente el Front Controller, resolver a la raíz
        if ($rutaLimpia === '/index.php') {
            $rutaLimpia = '/';
        }

        $metodoHttp = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        // Buscar coincidencia en las rutas registradas
        if (isset($this->rutas[$metodoHttp][$rutaLimpia])) {
            $controladorMetodo = $this->rutas[$metodoHttp][$rutaLimpia];
            $this->despachar($controladorMetodo);
        } else {
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

            // Iniciar sesión para identificar el rol del usuario en la vista 404
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }

            http_response_code(404);
            $archivoVista = dirname(__DIR__) . '/Views/errors/404.php';
            if (file_exists($archivoVista)) {
                require_once $archivoVista;
            } else {
                echo "<div style='font-family:sans-serif;text-align:center;margin-top:10%;color:#fff;background:#131F24;min-height:100vh;padding-top:40px;'>";
                echo "  <h1 style='font-size:4rem;color:#FF4B4B;'>404</h1>";
                echo "  <h2>Página No Encontrada</h2>";
                echo "</div>";
            }
            exit;
        }
    }

    /**
     * Despacha la petición instanciando el controlador e invocando su método correspondiente.
     * @param string $controladorMetodo Controlador y método
     */
    protected function despachar(string $controladorMetodo): void {
        $autenticado = estaAutenticado();
        if ($autenticado && !empty($_SESSION['debe_cambiar_clave'])
            && !in_array($controladorMetodo, ['AuthController@showCambiarClave', 'AuthController@guardarCambiarClave', 'AuthController@logout', 'AuthController@csrf'], true)) {
            if (stripos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false
                || strcasecmp($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '', 'XMLHttpRequest') === 0) {
                http_response_code(403);
                header('Content-Type: application/json');
                echo json_encode(['exito' => false, 'error' => 'Debes cambiar tu contraseña antes de continuar.', 'redirigir' => PROYECTO_PATH . '/cambiar-clave']);
            } else {
                header('Location: ' . PROYECTO_PATH . '/cambiar-clave');
            }
            exit;
        }
        list($nombreControlador, $metodo) = explode('@', $controladorMetodo);
        
        // Agregar namespace completo de controladores
        $claseControlador = "App\\Controllers\\" . $nombreControlador;

        if (class_exists($claseControlador)) {
            $instanciaControlador = new $claseControlador();
            if (method_exists($instanciaControlador, $metodo)) {
                $instanciaControlador->$metodo();
            } else {
                die("El método '{$metodo}' no existe en el controlador '{$nombreControlador}'.");
            }
        } else {
            die("El controlador '{$nombreControlador}' no existe en la carpeta app/Controllers/.");
        }
    }
}
