<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Admin;
use App\Models\GestionUsuarios;
use App\Models\Nivel;
use App\Models\Programa;
use App\Models\User;

/**
 * AdminController.php
 * Controlador para las vistas y lógica administrativa del sistema SmashCode.
 * Incluye la gestión completa de usuarios (HU04).
 */
class AdminController extends Controller {

    private Admin $adminModel;
    private GestionUsuarios $usuarioModel;
    private Nivel $nivelModel;
    private Programa $programaModel;

    public function __construct() {
        parent::__construct();
        $this->adminModel   = new Admin();
        $this->usuarioModel = new GestionUsuarios();
        $this->nivelModel   = new Nivel();
        $this->programaModel = new Programa();
        iniciarSesion();

        // Verificar rol administrador obligatoriamente
        if (!estaAutenticado() || obtenerRolSesion() !== 'admin') {
            $this->redirect('login');
        }
    }

    /**
     * Muestra el panel de control administrativo general.
     */
    public function index(): void {
        $totalUsuarios     = $this->adminModel->obtenerTotalUsuarios();
        $aprendicesActivos = $this->adminModel->obtenerAprendicesActivos();
        $totalXP           = $this->adminModel->obtenerTotalXP();
        $quizzesCompletos  = $this->adminModel->obtenerQuizzesCompletados();
        $actividad         = $this->adminModel->obtenerActividadReciente();
        $datosSemana       = $this->adminModel->obtenerRendimientoSemanal();

        $this->render('admin/dashboard', [
            'totalUsuarios'     => $totalUsuarios,
            'aprendicesActivos' => $aprendicesActivos,
            'totalXP'           => $totalXP,
            'quizzesCompletos'  => $quizzesCompletos,
            'actividad'         => $actividad,
            'datosSemana'       => $datosSemana
        ]);
    }

    /* ========================================================
     * HU04 — Gestión de Usuarios
     * ======================================================== */

    /**
     * Lista paginada con búsqueda de usuarios.
     */
    public function usuarios(): void {
        $busqueda = limpiar($_GET['busqueda'] ?? '');
        $rol      = limpiar($_GET['rol'] ?? '');
        $pagina   = max(1, (int)($_GET['pagina'] ?? 1));
        $porPagina = 15;

        $lista  = $this->usuarioModel->listar($busqueda, $rol, $pagina, $porPagina);
        $total  = $this->usuarioModel->contarTotal($busqueda, $rol);
        $paginas = (int) ceil($total / $porPagina);
        $totalUsuarios = $this->adminModel->obtenerTotalUsuarios();
        $programas     = $this->programaModel->obtenerTodos();

        $this->render('admin/usuarios', compact(
            'lista', 'total', 'paginas', 'pagina', 'busqueda', 'rol', 'totalUsuarios', 'programas'
        ));
    }

    /**
     * Muestra el formulario de creación de usuario.
     */
    public function crearUsuario(): void {
        $totalUsuarios = $this->adminModel->obtenerTotalUsuarios();
        $programas     = $this->programaModel->obtenerTodos();
        $this->render('admin/usuario_form', [
            'usuario'       => null,
            'totalUsuarios' => $totalUsuarios,
            'programas'     => $programas,
            'modoEditar'    => false,
            'error'         => '',
            'exito'         => ''
        ]);
    }

    /**
     * Procesa el guardado de un nuevo usuario.
     */
    public function guardarUsuario(): void {
        if (!validarTokenCSRF($_POST['csrf_token'] ?? '')) {
            $this->redirect('admin/usuarios');
        }

        $nombre     = limpiar($_POST['nombre_completo'] ?? '');
        $correo     = limpiar($_POST['correo'] ?? '');
        $rol        = limpiar($_POST['rol'] ?? 'aprendiz');
        $ficha      = limpiar($_POST['ficha_sena'] ?? '');
        $programaId = limpiar($_POST['programa_id'] ?? '');
        $contrasena = $_POST['contrasena'] ?? '';

        // Validaciones comunes
        $errores = [];
        if (empty($nombre))                                $errores[] = 'El nombre es obligatorio.';
        if (!filter_var($correo, FILTER_VALIDATE_EMAIL))   $errores[] = 'Correo inválido.';

        // La contraseña solo es obligatoria para el administrador
        if ($rol === 'admin') {
            if (strlen($contrasena) < 8)                   $errores[] = 'La contraseña debe tener mínimo 8 caracteres.';
            if (!preg_match('/[A-Z]/', $contrasena))       $errores[] = 'Incluye al menos 1 mayúscula.';
            if (!preg_match('/[0-9]/', $contrasena))       $errores[] = 'Incluye al menos 1 número.';
        }

        if ($errores) {
            $this->redirect('admin/usuarios?error=' . urlencode(implode(' ', $errores)));
            return;
        }

        if ($this->usuarioModel->existeCorreo($correo)) {
            $this->redirect('admin/usuarios?error=' . urlencode('Ese correo ya está registrado en el sistema.'));
            return;
        }

        $id = generarUUID();

        // ── FLUJO APRENDIZ / INSTRUCTOR: clave temporal + correo automático ──
        if ($rol === 'instructor' || $rol === 'aprendiz') {
            $claveTemp = $this->generarClaveTemp();
            $hash = password_hash($claveTemp, PASSWORD_BCRYPT, ['cost' => 12]);

            $this->usuarioModel->crearConClaveTemporal($id, $nombre, $correo, $hash, $rol, $programaId ?: null, $ficha ?: null);

            // Resolver nombre del programa para el correo
            $nombrePrograma = '';
            if ($programaId) {
                foreach ($this->programaModel->obtenerTodos() as $p) {
                    if ($p['id'] === $programaId) { $nombrePrograma = $p['nombre']; break; }
                }
            }

            $protocolo = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
            $urlLogin  = $protocolo . $_SERVER['HTTP_HOST'] . PROYECTO_PATH . '/login';
            
            $rolTexto = ucfirst($rol);

            $asunto = '¡Bienvenido a SmashCode! Tus credenciales de acceso';
            $cuerpo  = "<h2 style='color:#58CC02;'>¡Bienvenido(a) al equipo SmashCode!</h2>";
            $cuerpo .= "<p>Hola <strong>" . htmlspecialchars($nombre) . "</strong>,</p>";
            $cuerpo .= "<p>El administrador ha creado tu cuenta como <strong>{$rolTexto}</strong> en la plataforma SmashCode SENA.</p>";
            $cuerpo .= "<p>Para acceder, utiliza las siguientes credenciales temporales. Por tu seguridad, el sistema te forzará a cambiarlas en tu primer inicio de sesión.</p>";
            $cuerpo .= "<table style='border-collapse:collapse; font-size:1rem; margin:16px 0; background:#f4f4f4; padding:12px; border-radius:8px;'>";
            $cuerpo .= "<tr><td style='padding:8px 16px 8px 0; font-weight:600; color:#555;'>Correo:</td><td style='padding:8px 0; font-family:monospace; font-weight:bold;'>" . htmlspecialchars($correo) . "</td></tr>";
            $cuerpo .= "<tr><td style='padding:8px 16px 8px 0; font-weight:600; color:#555;'>Contraseña Temporal:</td><td style='padding:8px 0; font-family:monospace; font-weight:bold;'>" . htmlspecialchars($claveTemp) . "</td></tr>";
            if ($ficha)         $cuerpo .= "<tr><td style='padding:8px 16px 8px 0; font-weight:600; color:#555;'>Ficha SENA:</td><td style='padding:8px 0;'>" . htmlspecialchars($ficha) . "</td></tr>";
            if ($nombrePrograma) $cuerpo .= "<tr><td style='padding:8px 16px 8px 0; font-weight:600; color:#555;'>Programa asignado:</td><td style='padding:8px 0;'>" . htmlspecialchars($nombrePrograma) . "</td></tr>";
            $cuerpo .= "</table>";
            $cuerpo .= "<p style='margin-top:24px;'><a href='{$urlLogin}' style='display:inline-block; background:#58CC02; color:#fff; padding:14px 28px; border-radius:8px; text-decoration:none; font-weight:700;'>Ir a Iniciar Sesión</a></p>";
            $cuerpo .= "<hr><p style='font-size:0.8rem; color:#aaa; margin-top:24px;'>Si tienes algún problema para acceder, contacta al administrador del sistema.</p>";

            if (file_exists(dirname(__DIR__, 2) . '/includes/correo.php')) {
                require_once dirname(__DIR__, 2) . '/includes/correo.php';
                enviarCorreo($correo, $asunto, $cuerpo);
            }

            $this->redirect('admin/usuarios?exito=creado');
            return;
        }

        // ── FLUJO ADMIN: contraseña ingresada por el admin ──
        $hash = password_hash($contrasena, PASSWORD_BCRYPT, ['cost' => 12]);
        // Admins no llevan ficha ni programa
        $fichaFinal     = ($rol === 'admin') ? null : ($ficha ?: null);
        $programaFinal  = ($rol === 'admin') ? null : ($programaId ?: null);

        $this->usuarioModel->crear($id, $nombre, $correo, $hash, $rol, $fichaFinal, $programaFinal);
        $this->redirect('admin/usuarios?exito=creado');
    }

    /**
     * Muestra el formulario de edición de un usuario existente.
     */
    public function editarUsuario(): void {
        $id = limpiar($_GET['id'] ?? '');
        $usuario = $this->usuarioModel->obtenerPorId($id);

        if (!$usuario) {
            $this->redirect('admin/usuarios');
        }

        $totalUsuarios = $this->adminModel->obtenerTotalUsuarios();
        $programas     = $this->programaModel->obtenerTodos();
        $this->render('admin/usuario_form', [
            'usuario'       => $usuario,
            'totalUsuarios' => $totalUsuarios,
            'programas'     => $programas,
            'modoEditar'    => true,
            'error'         => '',
            'exito'         => ''
        ]);
    }

    /**
     * Procesa la actualización de datos de un usuario (sin cambiar contraseña).
     */
    public function actualizarUsuario(): void {
        if (!validarTokenCSRF($_POST['csrf_token'] ?? '')) {
            $this->redirect('admin/usuarios');
        }

        $id         = limpiar($_POST['id'] ?? '');
        $nombre     = limpiar($_POST['nombre_completo'] ?? '');
        $correo     = limpiar($_POST['correo'] ?? '');
        $rol        = limpiar($_POST['rol'] ?? 'aprendiz');
        $ficha      = limpiar($_POST['ficha_sena'] ?? '');
        $programaId = limpiar($_POST['programa_id'] ?? '');

        if (empty($id) || empty($nombre) || !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            $this->redirect('admin/usuarios?error=' . urlencode('El nombre es obligatorio y el correo electrónico debe tener un formato válido.'));
            return;
        }

        $this->usuarioModel->actualizar($id, $nombre, $correo, $rol, $ficha ?: null, $programaId ?: null);
        $this->redirect('admin/usuarios?exito=actualizado');
    }

    /**
     * Suspende o reactiva (soft-disable) a un usuario de forma inmediata.
     */
    public function suspenderUsuario(): void {
        if (!validarTokenCSRF($_POST['csrf_token'] ?? '')) {
            $this->redirect('admin/usuarios');
        }

        $id     = limpiar($_POST['id'] ?? '');
        $activo = (int)($_POST['activo'] ?? 0);

        if (!empty($id)) {
            $this->usuarioModel->cambiarEstado($id, $activo === 1 ? 0 : 1);
        }

        $this->redirect('admin/usuarios?exito=estado');
    }

    /**
     * Soft-delete de un usuario: marca la cuenta como eliminada sin borrarla físicamente.
     */
    public function eliminarUsuario(): void {
        if (!validarTokenCSRF($_POST['csrf_token'] ?? '')) {
            $this->redirect('admin/usuarios');
        }

        $id = limpiar($_POST['id'] ?? '');
        if (!empty($id)) {
            $this->usuarioModel->softDelete($id);
        }

        $this->redirect('admin/usuarios?exito=eliminado');
    }

    /**
     * Muestra el log de actividad de un usuario específico.
     */
    public function actividadUsuario(): void {
        $id      = limpiar($_GET['id'] ?? '');
        $usuario = $this->usuarioModel->obtenerPorId($id);

        if (!$usuario) {
            if (isset($_GET['ajax'])) {
                header('Content-Type: application/json');
                echo json_encode(['error' => 'Usuario no encontrado']);
                return;
            }
            $this->redirect('admin/usuarios');
        }

        $log = $this->usuarioModel->obtenerActividad($id);

        if (isset($_GET['ajax'])) {
            header('Content-Type: application/json');
            echo json_encode([
                'usuario' => $usuario,
                'logs'    => $log
            ]);
            return;
        }

        $totalUsuarios = $this->adminModel->obtenerTotalUsuarios();
        $this->render('admin/usuario_actividad', compact('usuario', 'log', 'totalUsuarios'));
    }

    /* ========================================================
     * HU09 — Crear Cuentas de Instructor con Credenciales Temporales
     * ======================================================== */

    /**
     * Muestra el formulario de alta de instructor (redirige al flujo unificado).
     * Mantenido por compatibilidad de rutas.
     */
    public function crearInstructor(): void {
        $this->redirect('admin/usuarios');
    }

    /**
     * Procesa la creación de un instructor desde la ruta legada.
     * Redirige al flujo unificado en guardarUsuario().
     * Mantenido por compatibilidad de rutas.
     */
    public function guardarInstructor(): void {
        // Inyectar rol instructor para que guardarUsuario() lo detecte
        $_POST['rol'] = 'instructor';
        $this->guardarUsuario();
    }

    /**
     * Genera una contraseña temporal segura de 12 caracteres:
     * garantiza al menos 1 mayúscula, 1 número y 1 símbolo.
     */
    private function generarClaveTemp(): string {
        $mayus   = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
        $minuscu = 'abcdefghjkmnpqrstuvwxyz';
        $numeros = '23456789';
        $simbol  = '!@#$%&*?';

        $clave  = $mayus[random_int(0, strlen($mayus) - 1)];
        $clave .= $numeros[random_int(0, strlen($numeros) - 1)];
        $clave .= $simbol[random_int(0, strlen($simbol) - 1)];

        $todos = $mayus . $minuscu . $numeros;
        for ($i = 0; $i < 9; $i++) {
            $clave .= $todos[random_int(0, strlen($todos) - 1)];
        }

        // Mezclar caracteres aleatoriamente
        return str_shuffle($clave);
    }

    /* ========================================================
     * HU10 — Gestión de Niveles (6 niveles fijos MCER)
     * ======================================================== */

    /**
     * Lista los 6 niveles con sus estadísticas de RAPs.
     */
    public function niveles(): void {
        $niveles       = $this->nivelModel->obtenerTodos();
        $totalUsuarios = $this->adminModel->obtenerTotalUsuarios();
        $exito  = limpiar($_GET['exito'] ?? '');
        $error  = limpiar($_GET['error']  ?? '');

        $this->render('admin/niveles', compact('niveles', 'totalUsuarios', 'exito', 'error'));
    }

    /**
     * Muestra el formulario de edición de un nivel.
     */
    public function editarNivel(): void {
        $id    = limpiar($_GET['id'] ?? '');
        $nivel = $this->nivelModel->obtenerPorId($id);

        if (!$nivel) {
            $this->redirect('admin/niveles?error=' . urlencode('Nivel no encontrado.'));
        }

        $totalUsuarios = $this->adminModel->obtenerTotalUsuarios();
        $this->render('admin/nivel_form', compact('nivel', 'totalUsuarios'));
    }

    /**
     * Procesa la actualización de un nivel (nombre, descripción, imagen, umbral, estado).
     */
    public function actualizarNivel(): void {
        if (!validarTokenCSRF($_POST['csrf_token'] ?? '')) {
            $this->redirect('admin/niveles');
        }

        $id          = limpiar($_POST['id'] ?? '');
        $nombre      = limpiar($_POST['nombre'] ?? '');
        $descripcion = limpiar($_POST['descripcion'] ?? '');
        $imagenUrl   = limpiar($_POST['imagen_url'] ?? '') ?: null;
        $umbral      = (float)($_POST['umbral_desbloqueo'] ?? 80);
        $activo      = isset($_POST['activo']) ? 1 : 0;

        if (empty($id) || empty($nombre)) {
            $this->redirect('admin/niveles?error=' . urlencode('El nombre del nivel es obligatorio.'));
            return;
        }

        $nivel = $this->nivelModel->obtenerPorId($id);
        if (!$nivel) {
            $this->redirect('admin/niveles?error=' . urlencode('Nivel no encontrado.'));
            return;
        }

        // El nivel 1 (orden=1) siempre tiene umbral 0 y siempre está activo
        if ((int)$nivel['orden'] === 1) {
            $umbral = 0.00;
            $activo = 1;
        }

        $this->nivelModel->actualizar($id, $nombre, $descripcion, $imagenUrl, $umbral, $activo);
        $this->redirect('admin/niveles?exito=actualizado');
    }

    /**
     * Alterna el estado activo/inactivo de un nivel (toggle rápido).
     */
    public function toggleNivel(): void {
        if (!validarTokenCSRF($_POST['csrf_token'] ?? '')) {
            $this->redirect('admin/niveles');
        }

        $id = limpiar($_POST['id'] ?? '');
        if (!empty($id)) {
            $this->nivelModel->toggleActivo($id);
        }

        $this->redirect('admin/niveles?exito=estado');
    }

    /* ======================================================== */

    /**
     * Valida campos básicos de usuario. Retorna array de errores.
     */
    private function validarCamposUsuario(string $nombre, string $correo, string $contrasena): array {
        $errores = [];
        if (empty($nombre))                               $errores[] = 'El nombre es obligatorio.';
        if (!filter_var($correo, FILTER_VALIDATE_EMAIL))  $errores[] = 'Correo inválido.';
        if (strlen($contrasena) < 8)                      $errores[] = 'La contraseña debe tener mínimo 8 caracteres.';
        if (!preg_match('/[A-Z]/', $contrasena))          $errores[] = 'Incluye al menos 1 mayúscula.';
        if (!preg_match('/[0-9]/', $contrasena))          $errores[] = 'Incluye al menos 1 número.';
        return $errores;
    }

    /* ========================================================
     * HU17 — Gestión de Programas de Formación
     * ======================================================== */

    /**
     * Lista todos los programas de formación para el panel admin.
     */
    public function programas(): void {
        $programas     = $this->programaModel->listarAdmin();
        $totalUsuarios = $this->adminModel->obtenerTotalUsuarios();
        $exito = limpiar($_GET['exito'] ?? '');
        $error = limpiar($_GET['error']  ?? '');

        $this->render('admin/programas', compact('programas', 'totalUsuarios', 'exito', 'error'));
    }

    /**
     * Muestra el formulario de creación de un programa.
     */
    public function crearPrograma(): void {
        $totalUsuarios = $this->adminModel->obtenerTotalUsuarios();
        $this->render('admin/programa_form', [
            'programa'      => null,
            'totalUsuarios' => $totalUsuarios,
            'modoEditar'    => false,
            'error'         => '',
        ]);
    }

    /**
     * Procesa la creación de un nuevo programa de formación.
     */
    public function guardarPrograma(): void {
        if (!validarTokenCSRF($_POST['csrf_token'] ?? '')) {
            $this->redirect('admin/programas');
        }

        $nombre      = limpiar($_POST['nombre'] ?? '');
        $descripcion = limpiar($_POST['descripcion'] ?? '');

        if (empty($nombre)) {
            $this->redirect('admin/programas?error=' . urlencode('El nombre del programa es obligatorio.'));
            return;
        }

        if ($this->programaModel->existeNombre($nombre)) {
            $this->redirect('admin/programas?error=' . urlencode('Ya existe un programa con ese nombre.'));
            return;
        }

        $this->programaModel->crear(generarUUID(), $nombre, $descripcion ?: null);
        $this->redirect('admin/programas?exito=creado');
    }

    /**
     * Muestra el formulario de edición de un programa existente.
     */
    public function editarPrograma(): void {
        $id      = limpiar($_GET['id'] ?? '');
        $programa = $this->programaModel->obtenerPorId($id);

        if (!$programa) {
            $this->redirect('admin/programas?error=' . urlencode('Programa no encontrado.'));
        }

        $totalUsuarios = $this->adminModel->obtenerTotalUsuarios();
        $this->render('admin/programa_form', [
            'programa'      => $programa,
            'totalUsuarios' => $totalUsuarios,
            'modoEditar'    => true,
            'error'         => '',
        ]);
    }

    /**
     * Procesa la actualización de nombre y descripción de un programa.
     */
    public function actualizarPrograma(): void {
        if (!validarTokenCSRF($_POST['csrf_token'] ?? '')) {
            $this->redirect('admin/programas');
        }

        $id          = limpiar($_POST['id'] ?? '');
        $nombre      = limpiar($_POST['nombre'] ?? '');
        $descripcion = limpiar($_POST['descripcion'] ?? '');

        if (empty($id) || empty($nombre)) {
            $this->redirect('admin/programas?error=' . urlencode('El nombre del programa es obligatorio.'));
            return;
        }

        if ($this->programaModel->existeNombre($nombre, $id)) {
            $this->redirect('admin/programas?error=' . urlencode('Ya existe otro programa con ese nombre.'));
            return;
        }

        $this->programaModel->actualizar($id, $nombre, $descripcion ?: null);
        $this->redirect('admin/programas?exito=actualizado');
    }

    /**
     * Alterna el estado activo/inactivo de un programa (toggle).
     * Un programa inactivo no aparece en selectores de nuevas asignaciones.
     */
    public function togglePrograma(): void {
        if (!validarTokenCSRF($_POST['csrf_token'] ?? '')) {
            $this->redirect('admin/programas');
        }

        $id = limpiar($_POST['id'] ?? '');
        if (!empty($id)) {
            $this->programaModel->desactivar($id);
        }

        $this->redirect('admin/programas?exito=estado');
    }

    /**
     * Soft-delete de un programa. Bloqueado si tiene usuarios activos vinculados.
     */
    public function eliminarPrograma(): void {
        if (!validarTokenCSRF($_POST['csrf_token'] ?? '')) {
            $this->redirect('admin/programas');
        }

        $id = limpiar($_POST['id'] ?? '');

        if (empty($id)) {
            $this->redirect('admin/programas');
            return;
        }

        if ($this->programaModel->tieneUsuarios($id)) {
            $this->redirect('admin/programas?error=' . urlencode('No se puede eliminar: el programa tiene usuarios activos vinculados.'));
            return;
        }

        $this->programaModel->softDelete($id);
        $this->redirect('admin/programas?exito=eliminado');
    }

    /**
     * Muestra el panel de RAPs con el estado de sus 5 componentes (HU03).
     */
    public function raps(): void {
        $pdo = obtenerConexion();
        $stmt = $pdo->query(
            'SELECT r.id, r.titulo, r.activo AS rap_activo, n.nombre AS nivel_nombre, n.orden AS nivel_orden,
                    (SELECT COUNT(*) FROM vocabulario v WHERE v.rap_id = r.id AND v.activo = 1) AS total_vocabulario,
                    (SELECT COUNT(*) FROM vocabulario v WHERE v.rap_id = r.id AND v.activo = 1 AND (v.transcripcion_ipa IS NOT NULL AND v.transcripcion_ipa <> "")) AS total_pronunciacion,
                    (SELECT COUNT(*) FROM ejercicio e WHERE e.rap_id = r.id AND e.activo = 1) AS total_ejercicios,
                    (SELECT COUNT(*) FROM dialogo d WHERE d.rap_id = r.id AND d.activo = 1) AS total_dialogos,
                    (SELECT COUNT(*) FROM quiz q WHERE q.rap_id = r.id AND q.activo = 1) AS tiene_quiz,
                    (SELECT COUNT(p.id) FROM quiz q JOIN pregunta p ON p.quiz_id = q.id WHERE q.rap_id = r.id AND q.activo = 1) AS total_preguntas_quiz
             FROM rap r
             JOIN nivel n ON n.id = r.nivel_id
             ORDER BY n.orden, r.orden'
        );
        $raps = $stmt->fetchAll();
        $totalUsuarios = $this->adminModel->obtenerTotalUsuarios();
        $exito = limpiar($_GET['exito'] ?? '');
        $error = limpiar($_GET['error']  ?? '');

        $this->render('admin/raps', compact('raps', 'totalUsuarios', 'exito', 'error'));
    }

    /**
     * Alterna el estado activo/inactivo de un RAP (HU03).
     */
    public function toggleRap(): void {
        if (!validarTokenCSRF($_POST['csrf_token'] ?? '')) {
            $this->redirect('admin/raps');
        }

        $id = limpiar($_POST['id'] ?? '');
        if (!empty($id)) {
            $pdo = obtenerConexion();
            $stmt = $pdo->prepare('UPDATE rap SET activo = IF(activo = 1, 0, 1) WHERE id = ?');
            $stmt->execute([$id]);
        }

        $this->redirect('admin/raps?exito=estado');
    }
}
