<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;
use App\Models\Programa;
use App\Models\ValidacionUsuario;
use App\Services\CorreoPlantillas;
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
                    $error = 'Cuenta bloqueada. Usa Recuperar contraseña o contacta al administrador.';
                } elseif (!$usuario['activo']) {
                    $error = 'Cuenta suspendida. Contacta al administrador.';
                } elseif ($usuario['contrasena']===null) {
                    $error='Esta cuenta no tiene una contraseña creada. Elige Continuar con Google o Recuperar contraseña.';
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
                            try {
                                $mensaje=CorreoPlantillas::bloqueo($usuario['nombre_completo'],CorreoPlantillas::urlAplicacion('recuperar'));
                                enviarCorreo($correo,$mensaje['asunto'],$mensaje['html']);
                            } catch (\Throwable $e) {
                                error_log('[Correo] No se pudo preparar el aviso de bloqueo: '.$e->getMessage());
                            }
                        }
                    } else {
                        $error = 'Contraseña incorrecta. Intento ' . $intentos . ' de 5.';
                    }
                } elseif (!$usuario['correo_verificado']) {
                    // RF-01: se avisa solo después de acertar la contraseña, para no
                    // revelarle a un tercero qué correos tienen cuenta sin activar.
                    $this->userModel->resetearIntentosFallidos($usuario['id']);
                    $_SESSION['activacion_pendiente'] = [
                        'id'     => $usuario['id'],
                        'correo' => $correo,
                        'nombre' => $usuario['nombre_completo'],
                        'rol' => $usuario['rol'],
                        'creado' => time(),
                        'huella' => hash('sha256',$usuario['contrasena'])
                    ];
                    $error = 'Tu cuenta todavía no está activada. Revisa el correo que te enviamos y confirma el enlace.';
                } else {
                    // Autenticación exitosa
                    unset($_SESSION['activacion_pendiente']);
                    $this->userModel->resetearIntentosFallidos($usuario['id']);
                    session_regenerate_id(true);
                    $_SESSION['usuario_id'] = $usuario['id'];
                    $_SESSION['nombre'] = $usuario['nombre_completo'];
                    $_SESSION['rol'] = $usuario['rol'];
                    $_SESSION['ultima_actividad'] = time();
                    actualizarHuellaSesion();

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
            $nombre = ValidacionUsuario::entrada($_POST['nombre_completo'] ?? '');
            $correo = ValidacionUsuario::entrada($_POST['correo'] ?? '');
            $ficha = ValidacionUsuario::entrada($_POST['ficha_sena'] ?? '');
            $programa = ValidacionUsuario::entrada($_POST['programa_id'] ?? '');
            $contrasena = is_string($_POST['contrasena'] ?? null) ? $_POST['contrasena'] : '';
            try {
                $errores=ValidacionUsuario::errores($nombre,$correo,$ficha,'aprendiz',$_POST);

                if ($errores) {
                    $error=implode(' ',$errores);
                } elseif (!ValidacionUsuario::programaPermitido($programa)) {
                    $error='Selecciona un programa activo válido.';
                } elseif (empty($contrasena)) {
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
                            // RF-01: la cuenta nace sin verificar y se habilita con el enlace del
                            // correo. Un fallo SMTP conserva la activación pendiente; únicamente
                            // la demostración local con correo desactivado permite omitirla.
                            $enviado = $this->enviarCorreoActivacion($id, $correo, $nombre);
                            $habilitada = !empty($this->userModel->obtenerParaActivacion($id)['correo_verificado']);
                            $exito = $enviado
                                ? '¡Cuenta creada! Te enviamos un correo para activarla. Revisa tu bandeja y confirma el enlace antes de iniciar sesión.'
                                : ($habilitada ? '¡Cuenta creada! Ya puedes iniciar sesión.' : 'Cuenta creada, pendiente de activación. No pudimos enviar el enlace. Inicia sesión con tu contraseña para reenviarlo o contacta al administrador.');
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
            } catch (\Throwable $e) {
                error_log('[Registro] '.$e->getMessage());
                $error='No se pudo registrar la cuenta. Revisa los datos e intenta nuevamente.';
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
     * RF-01: emite el token de activación y manda el enlace. Devuelve si el correo
     * salió; un fallo conserva la cuenta pendiente. Solo local con correo desactivado
     * permite acceder con una cuenta de demostración.
     */
    private function enviarCorreoActivacion(string $usuarioId, string $correo, string $nombre): bool {
        $rutaCorreo = dirname(__DIR__, 2) . '/includes/correo.php';
        if (!file_exists($rutaCorreo)) {
            $this->habilitarCuentaDemoLocal($usuarioId);
            return false;
        }
        require_once $rutaCorreo;

        try {
            $token  = bin2hex(random_bytes(32));
            $expira = date('Y-m-d H:i:s', strtotime('+24 hours'));
            if (!$this->userModel->crearTokenActivacion($usuarioId, $token, $expira)) {
                throw new \RuntimeException('No se pudo registrar el token de activación.');
            }

            $mensaje = CorreoPlantillas::activacion($nombre, CorreoPlantillas::urlAplicacion('activar?token=' . $token));
            if (enviarCorreo($correo, $mensaje['asunto'], $mensaje['html'])) {
                return true;
            }
        } catch (\Throwable $e) {
            error_log('[Activacion] No se pudo preparar el correo: ' . $e->getMessage());
        }

        $this->habilitarCuentaDemoLocal($usuarioId);
        return false;
    }

    /** Solo el entorno local con correo explícitamente desactivado omite la activación. */
    private function habilitarCuentaDemoLocal(string $usuarioId): void {
        if (($_ENV['APP_ENV'] ?? '')==='local' && filter_var($_ENV['MAIL_ENABLED'] ?? 'true',FILTER_VALIDATE_BOOLEAN,FILTER_NULL_ON_FAILURE)===false) {
            $this->userModel->marcarCorreoVerificado($usuarioId);
        }
    }

    /**
     * RF-01: consume el enlace de activación. No revela si el token existió o solo
     * venció, y siempre devuelve al login con un mensaje claro.
     */
    public function activar(): void {
        $token = is_string($_GET['token'] ?? null) ? trim($_GET['token']) : '';

        if ($token !== '' && $this->userModel->activarCuenta($token)) {
            $this->redirect('login?exito=' . urlencode('¡Cuenta activada! Ya puedes iniciar sesión.'));
            return;
        }

        $this->redirect('login?error=' . urlencode('El enlace de activación es inválido o ya venció. Inicia sesión para pedir uno nuevo.'));
    }

    /**
     * RF-01: reenvía el enlace de activación. Solo atiende a quien acaba de acertar
     * su contraseña en esta misma sesión, así que no sirve para averiguar correos
     * ajenos ni para inundar de mensajes a nadie.
     */
    public function reenviarActivacion(): void {
        iniciarSesion();

        if (!validarTokenCSRF($_POST['csrf_token'] ?? '')) {
            $this->redirect('login?error=' . urlencode('Solicitud inválida. Recarga la página.'));
            return;
        }

        $pendiente = $_SESSION['activacion_pendiente'] ?? null;
        if (!is_array($pendiente) || empty($pendiente['id'])) {
            $this->redirect('login?error=' . urlencode('Inicia sesión de nuevo para reenviar el enlace de activación.'));
            return;
        }

        $usuario=$this->userModel->obtenerParaActivacion($pendiente['id']);
        if (!$usuario || !$usuario['activo'] || !empty($usuario['eliminado']) || !empty($usuario['bloqueado']) || !empty($usuario['correo_verificado'])
            || ($usuario['rol'] ?? '')!==($pendiente['rol'] ?? '')
            || strcasecmp((string)$usuario['correo'],(string)$pendiente['correo'])!==0
            || time()-(int)($pendiente['creado'] ?? 0)>900
            || !hash_equals((string)($pendiente['huella'] ?? ''),(string)$usuario['huella_clave'])) {
            unset($_SESSION['activacion_pendiente']);
            $this->redirect('login?error='.urlencode('Tu solicitud venció o la cuenta cambió. Inicia sesión de nuevo.'));
            return;
        }
        if (!$this->userModel->puedeReenviarActivacion($pendiente['id'])) {
            $this->redirect('login?error='.urlencode('Espera un minuto antes de pedir otro enlace de activación.'));
            return;
        }
        $enviado = $this->enviarCorreoActivacion($pendiente['id'], $pendiente['correo'], $pendiente['nombre']);
        $habilitada=!empty($this->userModel->obtenerParaActivacion($pendiente['id'])['correo_verificado']);
        unset($_SESSION['activacion_pendiente']);

        $this->redirect('login?' . ($enviado
            ? 'exito=' . urlencode('Te reenviamos el enlace de activación. Revisa tu correo.')
            : ($habilitada ? 'exito=' . urlencode('Cuenta habilitada para el entorno local. Ya puedes iniciar sesión.') : 'error=' . urlencode('No pudimos enviar el enlace. Tu cuenta sigue pendiente de activación. Inténtalo más tarde o contacta al administrador.'))));
    }

    /**
     * Envía el correo de confirmación de registro (HU16) y devuelve si salió.
     */
    private function enviarCorreoBienvenida(string $correo, string $nombre): bool {
        $rutaCorreo = dirname(__DIR__, 2) . '/includes/correo.php';
        if (!file_exists($rutaCorreo)) {
            return false;
        }
        require_once $rutaCorreo;

        try {
            return enviarCorreo($correo, 'Tu cuenta en SmashCode está lista', $this->cuerpoCorreoBienvenida($nombre));
        } catch (\Throwable $e) {
            error_log('[Correo] No se pudo preparar la bienvenida: '.$e->getMessage());
            return false;
        }
    }

    /**
     * Cuerpo del correo de bienvenida. $nombre llega ya escapado por limpiar().
     * Usa APP_URL o el esquema del servidor y los proxies configurados.
     */
    private function cuerpoCorreoBienvenida(string $nombre): string {
        return CorreoPlantillas::bienvenida($nombre,CorreoPlantillas::urlAplicacion('login'))['html'];
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
                require_once dirname(__DIR__,2).'/includes/correo.php';
                if (!correoDisponible()) {
                    $this->render('auth/recuperar', [
                        'error'=>'La recuperación por correo no está disponible por ahora. Contacta al administrador de tu programa.',
                        'exito'=>'', 'csrf'=>$csrf
                    ]);
                    return;
                }
                $usuario = $this->userModel->obtenerPorCorreo($correo);

                if ($usuario) {
                    // Invalida tokens anteriores
                    $this->userModel->invalidarTokensRecuperacion($usuario['id']);

                    // Genera nuevo token seguro de 64 bytes
                    $token_string = bin2hex(random_bytes(32));
                    $expira = date('Y-m-d H:i:s', strtotime('+24 hours'));
                    
                    $this->userModel->crearTokenRecuperacion($usuario['id'], $token_string, $expira);

                    $enlace=CorreoPlantillas::urlAplicacion('restablecer?token='.rawurlencode($token_string));
                    $mensaje=CorreoPlantillas::recuperacion($usuario['nombre_completo'],$enlace);
                    if (file_exists(dirname(__DIR__,2).'/includes/correo.php')) {
                        require_once dirname(__DIR__,2).'/includes/correo.php';
                        enviarCorreo($correo,$mensaje['asunto'],$mensaje['html']);
                    }
                }
                
                // Siempre mostramos éxito por seguridad para no revelar si el correo existe
                $exito = 'Solicitud recibida. Revisa tu correo y la carpeta de spam. Si no recibes el enlace en unos minutos, solicita otro o contacta al administrador.';
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
        actualizarHuellaSesion();

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
            $this->redirect('login?error=' . urlencode('El acceso con Google no está disponible por ahora. Inicia sesión con tu correo y contraseña.'));
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
            $this->redirect('login?error=' . urlencode('Tu solicitud de Google venció o no se pudo validar. Vuelve a elegir Continuar con Google.'));
            return;
        }

        // 2. Google devuelve ?error=access_denied si el usuario cancela el consentimiento
        if (!empty($_GET['error'])) {
            $this->redirect('login?error=' . urlencode('Cancelaste el acceso con Google. Puedes intentarlo de nuevo o usar tu correo y contraseña.'));
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
            $this->redirect('login?error=' . urlencode('Cuenta bloqueada. Usa Recuperar contraseña o contacta al administrador.'));
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
        actualizarHuellaSesion();

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

    public function csrf(): void {
        header('Content-Type: application/json');
        if (!estaAutenticado()) {
            http_response_code(401);
            echo json_encode(['exito' => false, 'sesion_expirada' => true]);
            return;
        }
        echo json_encode(['exito' => true, 'csrf_token' => generarTokenCSRF(), 'usuario_id' => $_SESSION['usuario_id']]);
    }
}
