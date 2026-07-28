<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;
use App\Models\Programa;
use Firebase\JWT\JWT;
use Exception;

/**
 * AuthController.php
 * Controlador que gestiona toda la autenticación, registro, cierre de sesión,
 * y recuperación/restablecimiento de contraseñas.
 */
class AuthController extends Controller {

    /* Endpoints de Google para OAuth 2.0 */
    private const GOOGLE_AUTH_URL     = 'https://accounts.google.com/o/oauth2/v2/auth';
    private const GOOGLE_TOKEN_URL    = 'https://oauth2.googleapis.com/token';
    private const GOOGLE_USERINFO_URL = 'https://www.googleapis.com/oauth2/v3/userinfo';
    private const GOOGLE_STATE_COOKIE = 'smashcode_google_state';

    private User $userModel;
    private Programa $programaModel;

    public function __construct() {
        parent::__construct();
        $this->userModel = new User();
        $this->programaModel = new Programa();
        iniciarSesion();
    }

    /**
     * Muestra la pantalla de inicio de sesión o registro.
     */
    public function showLogin(): void {
        if (estaAutenticado()) {
            $this->redirigirPorRol(obtenerRolSesion());
        }

        $accion = limpiar($_GET['accion'] ?? 'ingresar');
        // Flash por query string (lo usa el flujo de Google, que redirige en vez de renderizar)
        $error = limpiar($_GET['error'] ?? '');
        $exito = limpiar($_GET['exito'] ?? '');
        $programas = $this->programaModel->obtenerTodos();
        $csrf = generarTokenCSRF();

        $this->render('auth/login', [
            'accion' => $accion,
            'error' => $error,
            'exito' => $exito,
            'programas' => $programas,
            'csrf' => $csrf
        ]);
    }

    /**
     * Procesa el inicio de sesión.
     */
    public function ingresar(): void {
        if (estaAutenticado()) {
            $this->redirigirPorRol(obtenerRolSesion());
        }

        $error = '';
        $exito = '';
        $programas = $this->programaModel->obtenerTodos();
        $csrf = generarTokenCSRF();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('login');
        }

        if (!validarTokenCSRF($_POST['csrf_token'] ?? '')) {
            $error = 'Solicitud inválida. Recarga la página.';
        } else {
            $correo = limpiar($_POST['correo'] ?? '');
            $contrasena = $_POST['contrasena'] ?? '';

            if (empty($correo) || empty($contrasena)) {
                $error = 'Completa todos los campos.';
            } else {
                $usuario = $this->userModel->obtenerPorCorreo($correo);

                if (!$usuario) {
                    $error = 'Correo o contraseña incorrectos.';
                } elseif ($usuario['bloqueado']) {
                    $error = 'Cuenta bloqueada. Revisa tu correo.';
                } elseif (!$usuario['activo']) {
                    $error = 'Cuenta suspendida. Contacta al administrador.';
                } elseif (!password_verify($contrasena, $usuario['contrasena'])) {
                    // Contraseña incorrecta
                    $intentos = $usuario['intentos_fallidos'] + 1;
                    $bloquear = $intentos >= 5 ? 1 : 0;
                    $this->userModel->actualizarIntentosFallidos($usuario['id'], $intentos, $bloquear);

                    if ($bloquear) {
                        $error = 'Cuenta bloqueada por demasiados intentos.';
                        // Importar la función de envío de correos desde includes
                        if (file_exists(dirname(__DIR__, 2) . '/includes/correo.php')) {
                            require_once dirname(__DIR__, 2) . '/includes/correo.php';
                            enviarCorreo(
                                $correo,
                                'Alerta de Seguridad - Cuenta Bloqueada',
                                '<h1>Cuenta Bloqueada</h1><p>Tu cuenta ha sido bloqueada tras 5 intentos fallidos de inicio de sesión. Por favor, restablece tu contraseña para recuperar el acceso.</p>'
                            );
                        }
                    } else {
                        $error = 'Contraseña incorrecta. Intento ' . $intentos . ' de 5.';
                    }
                } else {
                    // Autenticación exitosa
                    $this->userModel->resetearIntentosFallidos($usuario['id']);
                    session_regenerate_id(true);
                    $_SESSION['usuario_id'] = $usuario['id'];
                    $_SESSION['nombre'] = $usuario['nombre_completo'];
                    $_SESSION['rol'] = $usuario['rol'];
                    $_SESSION['ultima_actividad'] = time();

                    // Generar token JWT para la sesión
                    if (!defined('JWT_SECRET')) {
                        $rutaCredenciales = dirname(__DIR__, 2) . '/config/credenciales.php';
                        if (file_exists($rutaCredenciales)) {
                            require_once $rutaCredenciales;
                        }
                    }
                    
                    $secret_key = defined('JWT_SECRET') ? JWT_SECRET : 'AQUI_COLOCA_UNA_CLAVE_DE_MINIMO_32_CARACTERES';
                    $payload = [
                        'iss' => 'smashcode',
                        'aud' => 'smashcode_users',
                        'iat' => time(),
                        'nbf' => time(),
                        'exp' => time() + 1800, // 30 min
                        'data' => [
                            'id' => $usuario['id'],
                            'rol' => $usuario['rol']
                        ]
                    ];
                    $jwt = JWT::encode($payload, $secret_key, 'HS256');
                    $_SESSION['jwt_token'] = $jwt;

                    // HU09: Si el instructor debe cambiar su clave en el primer login
                    if (!empty($usuario['debe_cambiar_clave'])) {
                        $this->redirect('cambiar-clave');
                        return;
                    }

                    $this->redirigirPorRol($usuario['rol']);
                }
            }
        }

        $this->render('auth/login', [
            'accion' => 'ingresar',
            'error' => $error,
            'exito' => $exito,
            'programas' => $programas,
            'csrf' => $csrf
        ]);
    }

    /**
     * Procesa el registro de un nuevo aprendiz.
     */
    public function registrar(): void {
        if (estaAutenticado()) {
            $this->redirigirPorRol(obtenerRolSesion());
        }

        $error = '';
        $exito = '';
        $programas = $this->programaModel->obtenerTodos();
        $csrf = generarTokenCSRF();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('login');
        }

        if (!validarTokenCSRF($_POST['csrf_token'] ?? '')) {
            $error = 'Solicitud inválida. Recarga la página.';
        } else {
            $nombre = limpiar($_POST['nombre_completo'] ?? '');
            $correo = limpiar($_POST['correo'] ?? '');
            $ficha = limpiar($_POST['ficha_sena'] ?? '');
            $programa = limpiar($_POST['programa_id'] ?? '');
            $contrasena = $_POST['contrasena'] ?? '';

            if (empty($nombre) || empty($correo) || empty($contrasena)) {
                $error = 'Nombre, correo y contraseña son obligatorios.';
            } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
                $error = 'El correo no tiene un formato válido.';
            } elseif (strlen($contrasena) < 8 || !preg_match('/[A-Z]/', $contrasena) || !preg_match('/[0-9]/', $contrasena)) {
                $error = 'La contraseña debe tener mínimo 8 caracteres, 1 mayúscula y 1 número.';
            } else {
                if ($this->userModel->existeCorreo($correo)) {
                    $error = 'Este correo ya está registrado.';
                } else {
                    $hash = password_hash($contrasena, PASSWORD_BCRYPT, ['cost' => 12]);
                    $id = generarUUID();
                    
                    if ($this->userModel->registrar($id, $nombre, $correo, $hash, $ficha ?: null, $programa ?: null)) {
                        $exito = '¡Cuenta creada! Ya puedes iniciar sesión.';
                        $accion = 'ingresar';
                        
                        $this->render('auth/login', [
                            'accion' => 'ingresar',
                            'error' => '',
                            'exito' => $exito,
                            'programas' => $programas,
                            'csrf' => $csrf
                        ]);
                        return;
                    } else {
                        $error = 'Error interno al registrar la cuenta. Intenta más tarde.';
                    }
                }
            }
        }

        $this->render('auth/login', [
            'accion' => 'registrar',
            'error' => $error,
            'exito' => $exito,
            'programas' => $programas,
            'csrf' => $csrf
        ]);
    }

    /**
     * Procesa el cierre seguro de sesión.
     */
    public function logout(): void {
        cerrarSesion();
        $this->redirect('login');
    }

    /**
     * Muestra la pantalla de recuperar contraseña.
     */
    public function showRecuperar(): void {
        if (estaAutenticado()) {
            $this->redirect('');
        }

        $error = '';
        $exito = '';
        $csrf = generarTokenCSRF();

        $this->render('auth/recuperar', [
            'error' => $error,
            'exito' => $exito,
            'csrf' => $csrf
        ]);
    }

    /**
     * Procesa la solicitud y el envío del correo de recuperación.
     */
    public function enviarEnlace(): void {
        if (estaAutenticado()) {
            $this->redirect('');
        }

        $error = '';
        $exito = '';
        $csrf = generarTokenCSRF();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('recuperar');
        }

        if (!validarTokenCSRF($_POST['csrf_token'] ?? '')) {
            $error = 'Solicitud inválida. Recarga la página.';
        } else {
            $correo = limpiar($_POST['correo'] ?? '');
            if (empty($correo) || !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
                $error = 'Ingresa un correo electrónico válido.';
            } else {
                $usuario = $this->userModel->obtenerPorCorreo($correo);

                if ($usuario) {
                    // Invalida tokens anteriores
                    $this->userModel->invalidarTokensRecuperacion($usuario['id']);

                    // Genera nuevo token seguro de 64 bytes
                    $token_string = bin2hex(random_bytes(32));
                    $expira = date('Y-m-d H:i:s', strtotime('+24 hours'));
                    
                    $this->userModel->crearTokenRecuperacion($usuario['id'], $token_string, $expira);

                    // Construcción dinámica de la URI
                    $protocolo = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
                    $enlace = $protocolo . $_SERVER['HTTP_HOST'] . PROYECTO_PATH . "/restablecer?token=" . $token_string;

                    $cuerpo = "<h1>Recuperación de Contraseña</h1>";
                    $cuerpo .= "<p>Hola " . limpiar($usuario['nombre_completo']) . ",</p>";
                    $cuerpo .= "<p>Has solicitado restablecer tu contraseña. Haz clic en el siguiente enlace. Este enlace expira en 24 horas.</p>";
                    $cuerpo .= "<p><a href='$enlace'>$enlace</a></p>";
                    $cuerpo .= "<p>Si no fuiste tú, ignora este mensaje.</p>";

                    if (file_exists(dirname(__DIR__, 2) . '/includes/correo.php')) {
                        require_once dirname(__DIR__, 2) . '/includes/correo.php';
                        enviarCorreo($correo, 'Recupera tu contraseña en SmashCode', $cuerpo);
                    }
                }
                
                // Siempre mostramos éxito por seguridad para no revelar si el correo existe
                $exito = 'Si el correo está registrado, te hemos enviado las instrucciones para restablecer tu contraseña.';
            }
        }

        $this->render('auth/recuperar', [
            'error' => $error,
            'exito' => $exito,
            'csrf' => $csrf
        ]);
    }

    /**
     * Muestra la pantalla para restablecer contraseña.
     */
    public function showRestablecer(): void {
        if (estaAutenticado()) {
            $this->redirect('');
        }

        $error = '';
        $exito = '';
        $csrf = generarTokenCSRF();
        $token = $_GET['token'] ?? ($_POST['token'] ?? '');

        if (empty($token)) {
            $this->redirect('login');
        }

        $tokenRow = $this->userModel->obtenerTokenValido($token);

        if (!$tokenRow) {
            $error = 'El enlace de recuperación es inválido o ha expirado. Por favor, solicita uno nuevo.';
        }

        $this->render('auth/restablecer', [
            'error' => $error,
            'exito' => $exito,
            'csrf' => $csrf,
            'token' => $token,
            'tokenRow' => $tokenRow
        ]);
    }

    /**
     * Procesa la actualización de la nueva contraseña.
     */
    public function guardarClave(): void {
        if (estaAutenticado()) {
            $this->redirect('');
        }

        $error = '';
        $exito = '';
        $csrf = generarTokenCSRF();
        $token = $_POST['token'] ?? '';

        if (empty($token)) {
            $this->redirect('login');
        }

        $tokenRow = $this->userModel->obtenerTokenValido($token);

        if (!$tokenRow) {
            $error = 'El enlace de recuperación es inválido o ha expirado. Por favor, solicita uno nuevo.';
        } else {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                $this->redirect('login');
            }

            if (!validarTokenCSRF($_POST['csrf_token'] ?? '')) {
                $error = 'Solicitud inválida. Recarga la página.';
            } else {
                $clave = $_POST['contrasena'] ?? '';
                
                if (strlen($clave) < 8 || !preg_match('/[A-Z]/', $clave) || !preg_match('/[0-9]/', $clave)) {
                    $error = 'La contraseña debe tener mínimo 8 caracteres, 1 mayúscula y 1 número.';
                } else {
                    $hash = password_hash($clave, PASSWORD_BCRYPT, ['cost' => 12]);
                    
                    if ($this->userModel->restablecerContrasena($tokenRow['usuario_id'], $hash, $token)) {
                        $exito = 'Tu contraseña ha sido actualizada con éxito.';
                        $tokenRow = null; // Oculta el formulario
                    } else {
                        $error = 'Hubo un error al actualizar la contraseña.';
                    }
                }
            }
        }

        $this->render('auth/restablecer', [
            'error' => $error,
            'exito' => $exito,
            'csrf' => $csrf,
            'token' => $token,
            'tokenRow' => $tokenRow
        ]);
    }

    /**
     * Redirige al usuario a su panel de control según su rol.
     */
    private function redirigirPorRol(string $rol): void {
        if ($rol === 'admin') {
            $this->redirect('admin');
        } elseif ($rol === 'instructor') {
            $this->redirect('instructor');
        } else {
            $this->redirect('');
        }
    }

    /* ========================================================
     * HU09 — Cambio de contraseña forzado (primer login)
     * ======================================================== */

    /**
     * Muestra el formulario de cambio de contraseña obligatorio.
     * Solo accesible si se está autenticado y la sesión tiene debe_cambiar_clave.
     */
    public function showCambiarClave(): void {
        if (!estaAutenticado()) {
            $this->redirect('login');
        }

        // Si el usuario no necesita cambiar clave, redirigir a su panel
        $usuario = $this->userModel->obtenerPorId($_SESSION['usuario_id']);
        if (!$usuario || empty($usuario['debe_cambiar_clave'])) {
            $this->redirigirPorRol($_SESSION['rol']);
        }

        $this->render('auth/cambiar_clave', [
            'error' => '',
            'csrf'  => generarTokenCSRF(),
        ]);
    }

    /**
     * Procesa el cambio de contraseña forzado del instructor.
     * Tras guardar exitosamente, limpia el flag debe_cambiar_clave.
     */
    public function guardarCambiarClave(): void {
        if (!estaAutenticado()) {
            $this->redirect('login');
        }

        $csrf = generarTokenCSRF();

        if (!validarTokenCSRF($_POST['csrf_token'] ?? '')) {
            $this->render('auth/cambiar_clave', [
                'error' => 'Solicitud inválida. Recarga la página.',
                'csrf'  => $csrf,
            ]);
            return;
        }

        $claveNueva   = $_POST['contrasena'] ?? '';
        $claveConfirm = $_POST['contrasena_confirmar'] ?? '';

        $errores = [];
        if (strlen($claveNueva) < 8)                      $errores[] = 'Mín. 8 caracteres.';
        if (!preg_match('/[A-Z]/', $claveNueva))          $errores[] = 'Al menos 1 mayúscula.';
        if (!preg_match('/[0-9]/', $claveNueva))          $errores[] = 'Al menos 1 número.';
        if ($claveNueva !== $claveConfirm)                $errores[] = 'Las contraseñas no coinciden.';

        if ($errores) {
            $this->render('auth/cambiar_clave', [
                'error' => implode(' ', $errores),
                'csrf'  => $csrf,
            ]);
            return;
        }

        $hash = password_hash($claveNueva, PASSWORD_BCRYPT, ['cost' => 12]);
        $this->userModel->actualizarContrasenaYLimpiarFlag($_SESSION['usuario_id'], $hash);

        // Redirigir al panel correspondiente con mensaje de éxito
        $this->redirigirPorRol($_SESSION['rol']);
    }

    /* ========================================================
     * Inicio de sesión con Google (OAuth 2.0)
     * No reemplaza al login por contraseña: es una vía alterna que
     * termina en la MISMA sesión PHP que ingresar().
     * ======================================================== */

    /**
     * Arma la URL de consentimiento de Google y envía allí al usuario.
     * Guarda un `state` aleatorio (anti-CSRF) para poder validar el callback.
     */
    public function googleRedirect(): void {
        if (estaAutenticado()) {
            $this->redirigirPorRol(obtenerRolSesion());
        }

        if (empty(GOOGLE_CLIENT_ID) || empty(GOOGLE_CLIENT_SECRET) || empty(GOOGLE_REDIRECT_URI)) {
            $this->redirect('login?error=' . urlencode('El inicio de sesión con Google no está configurado. Revisa el archivo .env'));
            return;
        }

        // Token de un solo uso que viaja hasta Google y vuelve en el callback
        $state = bin2hex(random_bytes(32));
        $this->guardarStateOAuth($state);

        $parametros = [
            'client_id'     => GOOGLE_CLIENT_ID,
            'redirect_uri'  => GOOGLE_REDIRECT_URI,
            'response_type' => 'code',
            'scope'         => 'openid email profile',
            'state'         => $state,
            'access_type'   => 'online',
            'prompt'        => 'select_account'
        ];

        // No se usa $this->redirect() porque ese método antepone PROYECTO_PATH
        // y aquí el destino es un dominio externo.
        header('Location: ' . self::GOOGLE_AUTH_URL . '?' . http_build_query($parametros));
        exit;
    }

    /**
     * Recibe el retorno de Google: valida el `state`, canjea el `code` por un token,
     * lee el perfil y hace find-or-create del usuario antes de iniciar sesión.
     */
    public function googleCallback(): void {
        if (estaAutenticado()) {
            $this->redirigirPorRol(obtenerRolSesion());
        }

        // 1. Validar el state (anti-CSRF). Se consume siempre, haya salido bien o mal.
        // El origen se anota antes de consumirlo, para poder diagnosticar un rechazo.
        $origenState  = (isset($_COOKIE[self::GOOGLE_STATE_COOKIE]) ? 'cookie ' : '')
                      . (isset($_SESSION['google_oauth_state']) ? 'sesion' : '');
        $stateEsperado = $this->consumirStateOAuth();
        $stateRecibido = $_GET['state'] ?? '';

        if (empty($stateEsperado) || !is_string($stateRecibido) || !hash_equals($stateEsperado, $stateRecibido)) {
            // Sin esta traza el rechazo del state era indistinguible de otros rebotes al login
            error_log(sprintf(
                '[AuthController] State de Google rechazado. guardado en: %s | recibido: %s',
                $origenState !== '' ? trim($origenState) : 'ninguno',
                empty($stateRecibido) ? 'vacio' : 'presente'
            ));
            $this->redirect('login?error=' . urlencode('Solicitud inválida. Vuelve a intentarlo desde el botón de Google.'));
            return;
        }

        // 2. Google devuelve ?error=access_denied si el usuario cancela el consentimiento
        if (!empty($_GET['error'])) {
            $this->redirect('login?error=' . urlencode('Cancelaste el inicio de sesión con Google.'));
            return;
        }

        $codigo = $_GET['code'] ?? '';
        if (empty($codigo) || !is_string($codigo)) {
            $this->redirect('login?error=' . urlencode('Google no devolvió el código de autorización.'));
            return;
        }

        // 3. Canjear el code por un access token y leer el perfil
        $accessToken = $this->intercambiarCodigoPorToken($codigo);
        if (!$accessToken) {
            $this->redirect('login?error=' . urlencode('No pudimos validar tu cuenta de Google. Intenta más tarde.'));
            return;
        }

        $perfil = $this->obtenerPerfilGoogle($accessToken);
        $googleId = (string) ($perfil['sub'] ?? '');
        $correo   = limpiar((string) ($perfil['email'] ?? ''));
        $nombre   = limpiar((string) ($perfil['name'] ?? ''));

        if (empty($googleId) || empty($correo) || !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            $this->redirect('login?error=' . urlencode('Google no entregó un perfil válido.'));
            return;
        }

        // Sin correo verificado no se puede vincular por correo sin riesgo de secuestro de cuenta
        if (empty($perfil['email_verified'])) {
            $this->redirect('login?error=' . urlencode('Tu correo de Google no está verificado.'));
            return;
        }

        if (empty($nombre)) {
            $nombre = $correo;
        }

        // 4. Find-or-create: primero por google_id, luego por correo (que ya es UNIQUE)
        $usuario = $this->userModel->obtenerPorGoogleId($googleId);

        if (!$usuario) {
            $usuario = $this->userModel->obtenerPorCorreoParaGoogle($correo);
            if ($usuario) {
                // Cuenta creada antes con contraseña: se vincula en vez de duplicarla
                $this->userModel->vincularGoogleId($usuario['id'], $googleId);
            }
        }

        if ($usuario) {
            if (!empty($usuario['eliminado']) || empty($usuario['activo'])) {
                $this->redirect('login?error=' . urlencode('Cuenta suspendida. Contacta al administrador.'));
                return;
            }
            if (!empty($usuario['bloqueado'])) {
                $this->redirect('login?error=' . urlencode('Cuenta bloqueada. Revisa tu correo.'));
                return;
            }
        } else {
            // 5. Usuario nuevo: aprendiz, correo ya verificado por Google y sin contraseña local
            $id = generarUUID();
            if (!$this->userModel->registrarConGoogle($id, $nombre, $correo, $googleId)) {
                $this->redirect('login?error=' . urlencode('No pudimos crear tu cuenta. Intenta más tarde.'));
                return;
            }

            $usuario = $this->userModel->obtenerPorGoogleId($googleId);
            if (!$usuario) {
                $this->redirect('login?error=' . urlencode('No pudimos crear tu cuenta. Intenta más tarde.'));
                return;
            }
        }

        // 6. Misma sesión que el login por contraseña
        $this->iniciarSesionGoogle($usuario);

        // HU09: instructor con credenciales temporales pendientes de cambio
        if (!empty($usuario['debe_cambiar_clave'])) {
            $this->finalizarLoginGoogle('cambiar-clave');
            return;
        }

        // HU16: un aprendiz creado por Google no trae ficha ni programa
        if ($usuario['rol'] === 'aprendiz' && (empty($usuario['ficha_sena']) || empty($usuario['programa_id']))) {
            $this->finalizarLoginGoogle('aprendiz/perfil?completar=1');
            return;
        }

        $this->finalizarLoginGoogle($this->rutaPanelPorRol($usuario['rol']));
    }

    /**
     * Ruta del panel según el rol, sin redirigir (equivalente a redirigirPorRol()
     * pero devolviendo la ruta, que es lo que necesita finalizarLoginGoogle()).
     */
    private function rutaPanelPorRol(string $rol): string {
        if ($rol === 'admin') {
            return 'admin';
        }
        if ($rol === 'instructor') {
            return 'instructor';
        }
        return '';
    }

    /**
     * Cierra el login con Google navegando desde una página de nuestro propio origen.
     *
     * Aquí NO sirve un redirect 302: la cookie de sesión es SameSite=Strict
     * (config/sesion.php) y el navegador no la envía en la petición que sigue al salto
     * cross-site de vuelta desde accounts.google.com. El usuario llegaba al panel sin
     * sesión y el controlador de destino lo rebotaba a /login sin ningún mensaje, aunque
     * el login hubiera funcionado. Al navegar desde un documento ya servido por nuestro
     * dominio, la petición es same-site y la cookie de sesión sí viaja.
     */
    private function finalizarLoginGoogle(string $ruta): void {
        $destino = PROYECTO_PATH . '/' . ltrim($ruta, '/');

        header('Content-Type: text/html; charset=UTF-8');
        echo '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8">'
           . '<title>Iniciando sesión…</title>'
           . '<meta http-equiv="refresh" content="0;url=' . htmlspecialchars($destino, ENT_QUOTES) . '">'
           . '</head><body>'
           . '<p>Iniciando sesión…</p>'
           . '<script>location.replace(' . json_encode($destino, JSON_UNESCAPED_SLASHES) . ');</script>'
           . '</body></html>';
        exit;
    }

    /**
     * Guarda el `state` del flujo OAuth en sesión y en una cookie propia.
     *
     * La cookie de sesión del proyecto es SameSite=Strict (config/sesion.php), así que
     * el navegador NO la envía cuando Google nos devuelve el control: esa navegación es
     * cross-site y, sin esta cookie auxiliar con SameSite=Lax, el state siempre llegaría
     * vacío y todo inicio de sesión con Google fallaría.
     */
    private function guardarStateOAuth(string $state): void {
        $_SESSION['google_oauth_state'] = $state;

        // Path '/' a propósito: la cookie vive 10 minutos y evita cualquier problema de
        // coincidencia de subruta cuando el proyecto cuelga de /smashcode en localhost.
        setcookie(self::GOOGLE_STATE_COOKIE, $state, [
            'expires'  => time() + 600, // 10 min para completar el consentimiento
            'path'     => '/',
            'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
    }

    /**
     * Devuelve el `state` esperado y lo invalida (es de un solo uso).
     */
    private function consumirStateOAuth(): string {
        $state = $_COOKIE[self::GOOGLE_STATE_COOKIE] ?? ($_SESSION['google_oauth_state'] ?? '');

        unset($_SESSION['google_oauth_state'], $_COOKIE[self::GOOGLE_STATE_COOKIE]);
        // El path debe ser el mismo con el que se creó o el borrado no surte efecto
        setcookie(self::GOOGLE_STATE_COOKIE, '', [
            'expires'  => time() - 3600,
            'path'     => '/',
            'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
            'httponly' => true,
            'samesite' => 'Lax'
        ]);

        return is_string($state) ? $state : '';
    }

    /**
     * Canjea el `code` de Google por un access token vía curl.
     */
    private function intercambiarCodigoPorToken(string $codigo): ?string {
        $data = [
            'code'          => $codigo,
            'client_id'     => GOOGLE_CLIENT_ID,
            'client_secret' => GOOGLE_CLIENT_SECRET,
            'redirect_uri'  => GOOGLE_REDIRECT_URI,
            'grant_type'    => 'authorization_code'
        ];

        $ch = curl_init(self::GOOGLE_TOKEN_URL);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        // Desactivar verificación SSL solo para entornos locales (XAMPP/WAMP)
        if (in_array($_SERVER['SERVER_NAME'] ?? '', ['localhost', '127.0.0.1'], true)) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        }

        $response  = curl_exec($ch);
        $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($httpCode !== 200) {
            // Se registran solo los campos de error de Google (nunca el cuerpo completo,
            // que puede traer datos del cliente OAuth). Sin esto, un 400 era indiagnosticable.
            $detalle = json_decode((string) $response, true);
            error_log(sprintf(
                '[AuthController] Error al canjear el code de Google. HTTP %d | error: %s | descripcion: %s | curl: %s',
                $httpCode,
                $detalle['error'] ?? '(sin campo error)',
                $detalle['error_description'] ?? '(sin descripcion)',
                $curlError !== '' ? $curlError : '(ninguno)'
            ));
            return null;
        }

        $resultado = json_decode((string) $response, true);
        return $resultado['access_token'] ?? null;
    }

    /**
     * Consulta el perfil del usuario (sub, email, email_verified, name) con el access token.
     */
    private function obtenerPerfilGoogle(string $accessToken): array {
        $ch = curl_init(self::GOOGLE_USERINFO_URL);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $accessToken]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        // Desactivar verificación SSL solo para entornos locales (XAMPP/WAMP)
        if (in_array($_SERVER['SERVER_NAME'] ?? '', ['localhost', '127.0.0.1'], true)) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        }

        $response  = curl_exec($ch);
        $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($httpCode !== 200) {
            error_log('[AuthController] Error al leer el perfil de Google. HTTP ' . $httpCode . ' | curl: ' . $curlError);
            return [];
        }

        return json_decode((string) $response, true) ?: [];
    }

    /**
     * Deja la sesión exactamente igual que ingresar(): regenera el ID, guarda
     * usuario_id / nombre / rol / ultima_actividad y emite el JWT de 30 minutos.
     */
    private function iniciarSesionGoogle(array $usuario): void {
        $this->userModel->resetearIntentosFallidos($usuario['id']);
        session_regenerate_id(true);
        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['nombre'] = $usuario['nombre_completo'];
        $_SESSION['rol'] = $usuario['rol'];
        $_SESSION['ultima_actividad'] = time();

        // Generar token JWT para la sesión
        if (!defined('JWT_SECRET')) {
            $rutaCredenciales = dirname(__DIR__, 2) . '/config/credenciales.php';
            if (file_exists($rutaCredenciales)) {
                require_once $rutaCredenciales;
            }
        }

        $secret_key = defined('JWT_SECRET') ? JWT_SECRET : 'AQUI_COLOCA_UNA_CLAVE_DE_MINIMO_32_CARACTERES';
        $payload = [
            'iss' => 'smashcode',
            'aud' => 'smashcode_users',
            'iat' => time(),
            'nbf' => time(),
            'exp' => time() + 1800, // 30 min
            'data' => [
                'id' => $usuario['id'],
                'rol' => $usuario['rol']
            ]
        ];
        $jwt = JWT::encode($payload, $secret_key, 'HS256');
        $_SESSION['jwt_token'] = $jwt;
    }
}
