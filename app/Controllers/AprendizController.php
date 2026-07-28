<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;
use App\Models\Progreso;
use PDO;

class AprendizController extends Controller {

    public function __construct() {
        parent::__construct();
        iniciarSesion();
        if (!estaAutenticado() || !in_array(obtenerRolSesion(), ['aprendiz', 'admin', 'instructor'])) {
            $this->redirect('login');
        }
    }

    public function rap(): void {
        $rapId = limpiar($_GET['id'] ?? '');
        if (empty($rapId)) {
            $this->redirect('');
            return;
        }

        $pdo = obtenerConexion();

        // Obtener RAP y nivel
        $stmtRap = $pdo->prepare(
            'SELECT r.id, r.titulo, r.activo AS rap_activo, r.nivel_id, n.nombre AS nivel_nombre, n.orden AS nivel_orden
             FROM rap r
             JOIN nivel n ON n.id = r.nivel_id
             WHERE r.id = ?'
        );
        $stmtRap->execute([$rapId]);
        $rap = $stmtRap->fetch();

        if (!$rap) {
            $this->redirect('');
            return;
        }

        $esPreview = in_array(obtenerRolSesion(), ['admin', 'instructor']);
        if (!$rap['rap_activo'] && !$esPreview) {
            $this->redirect('');
            return;
        }

        // Obtener Vocabulario
        $stmtVoc = $pdo->prepare('SELECT * FROM vocabulario WHERE rap_id = ? AND activo = 1');
        $stmtVoc->execute([$rapId]);
        $vocabulario = $stmtVoc->fetchAll();

        // Obtener vocabulario marcado como difícil por el usuario
        $uid = $_SESSION['usuario_id'];
        $stmtMarc = $pdo->prepare('SELECT vocabulario_id FROM vocabulario_marcado WHERE usuario_id = ?');
        $stmtMarc->execute([$uid]);
        $marcados = $stmtMarc->fetchAll(PDO::FETCH_COLUMN);

        // Obtener Diálogos y sus turnos
        $stmtDia = $pdo->prepare('SELECT * FROM dialogo WHERE rap_id = ? AND activo = 1');
        $stmtDia->execute([$rapId]);
        $dialogos = $stmtDia->fetchAll();

        foreach ($dialogos as &$d) {
            $stmtTur = $pdo->prepare('SELECT * FROM turno_dialogo WHERE dialogo_id = ? ORDER BY orden_turno ASC');
            $stmtTur->execute([$d['id']]);
            $d['turnos'] = $stmtTur->fetchAll();
        }

        // Obtener Ejercicios y sus opciones
        $stmtEj = $pdo->prepare('SELECT * FROM ejercicio WHERE rap_id = ? AND activo = 1');
        $stmtEj->execute([$rapId]);
        $ejercicios = $stmtEj->fetchAll();

        foreach ($ejercicios as &$ej) {
            $stmtOpc = $pdo->prepare('SELECT id, texto, es_correcta, retroalimentacion FROM ejercicio_opcion WHERE ejercicio_id = ?');
            $stmtOpc->execute([$ej['id']]);
            $ej['opciones'] = $stmtOpc->fetchAll();
        }

        // Obtener Quiz y Preguntas
        $stmtQuiz = $pdo->prepare('SELECT * FROM quiz WHERE rap_id = ? AND activo = 1 LIMIT 1');
        $stmtQuiz->execute([$rapId]);
        $quiz = $stmtQuiz->fetch();

        $preguntas = [];
        if ($quiz) {
            $stmtPreg = $pdo->prepare('SELECT id, texto, opciones, respuesta_correcta, retroalimentacion FROM pregunta WHERE quiz_id = ?');
            $stmtPreg->execute([$quiz['id']]);
            $preguntas = $stmtPreg->fetchAll();
            // Decodificar opciones JSON
            foreach ($preguntas as &$preg) {
                $preg['opciones'] = json_decode($preg['opciones'], true);
            }
        }

        // Obtener o inicializar progreso
        $esPreview = in_array(obtenerRolSesion(), ['admin', 'instructor']);
        if ($esPreview) {
            $progreso = ['porcentaje' => 100.00, 'completado' => 1, 'mejor_puntaje_quiz' => 100.00];
        } else {
            $stmtProg = $pdo->prepare('SELECT porcentaje, completado, mejor_puntaje_quiz FROM progreso WHERE usuario_id = ? AND rap_id = ? LIMIT 1');
            $stmtProg->execute([$uid, $rapId]);
            $progreso = $stmtProg->fetch() ?: ['porcentaje' => 0.00, 'completado' => 0, 'mejor_puntaje_quiz' => 0.00];
        }

        $this->render('aprendiz/rap', compact('rap', 'vocabulario', 'marcados', 'dialogos', 'ejercicios', 'quiz', 'preguntas', 'progreso', 'esPreview'));
    }

    public function toggleVocabMarcado(): void {
        header('Content-Type: application/json');
        $uid = $_SESSION['usuario_id'];
        $vocabId = limpiar($_POST['vocabulario_id'] ?? '');

        if (empty($vocabId)) {
            echo json_encode(['exito' => false, 'error' => 'ID de vocabulario no provisto']);
            return;
        }

        $pdo = obtenerConexion();
        $stmt = $pdo->prepare('SELECT 1 FROM vocabulario_marcado WHERE usuario_id = ? AND vocabulario_id = ? LIMIT 1');
        $stmt->execute([$uid, $vocabId]);
        $existe = $stmt->fetchColumn();

        if ($existe) {
            $stmtDel = $pdo->prepare('DELETE FROM vocabulario_marcado WHERE usuario_id = ? AND vocabulario_id = ?');
            $stmtDel->execute([$uid, $vocabId]);
            echo json_encode(['exito' => true, 'marcado' => false]);
        } else {
            $stmtIns = $pdo->prepare('INSERT INTO vocabulario_marcado (usuario_id, vocabulario_id) VALUES (?, ?)');
            $stmtIns->execute([$uid, $vocabId]);
            echo json_encode(['exito' => true, 'marcado' => true]);
        }
    }

    public function guardarProgreso(): void {
        header('Content-Type: application/json');
        $uid = $_SESSION['usuario_id'];
        $rapId = limpiar($_POST['rap_id'] ?? '');
        $porcentaje = (float)($_POST['porcentaje'] ?? 0.00);

        if (empty($rapId)) {
            echo json_encode(['exito' => false, 'error' => 'RAP ID no provisto']);
            return;
        }

        if (in_array(obtenerRolSesion(), ['admin', 'instructor'])) {
            echo json_encode(['exito' => true, 'porcentaje' => $porcentaje, 'preview' => true]);
            return;
        }

        $progresoModel = new Progreso();
        // Progreso se considera completado si es 100%, pero el completado real de la lección
        // se guarda al aprobar el Quiz. Guardamos el porcentaje alcanzado en esta sesión.
        $progresoModel->actualizarProgreso($uid, $rapId, $porcentaje, 0);

        echo json_encode(['exito' => true, 'porcentaje' => $porcentaje]);
    }

    public function guardarIntentoQuiz(): void {
        header('Content-Type: application/json');
        $uid = $_SESSION['usuario_id'];
        $rapId = limpiar($_POST['rap_id'] ?? '');
        $duracionSeg = (int)($_POST['duracion_seg'] ?? 0);
        $respuestas = $_POST['respuestas'] ?? []; // Array de pregunta_id => respuesta_elegida

        if (empty($rapId)) {
            echo json_encode(['exito' => false, 'error' => 'RAP ID no provisto']);
            return;
        }

        if (in_array(obtenerRolSesion(), ['admin', 'instructor'])) {
            echo json_encode([
                'exito' => true,
                'puntaje' => 100.00,
                'aprobado' => true,
                'xp_ganados' => 0,
                'insignia_ganada' => 'Vista Previa',
                'detalles' => []
            ]);
            return;
        }

        $pdo = obtenerConexion();

        // 1. Obtener Quiz
        $stmtQuiz = $pdo->prepare('SELECT * FROM quiz WHERE rap_id = ? AND activo = 1 LIMIT 1');
        $stmtQuiz->execute([$rapId]);
        $quiz = $stmtQuiz->fetch();

        if (!$quiz) {
            echo json_encode(['exito' => false, 'error' => 'Quiz no encontrado para este RAP']);
            return;
        }

        // 2. Obtener Preguntas del Quiz
        $stmtPreg = $pdo->prepare('SELECT id, respuesta_correcta, retroalimentacion FROM pregunta WHERE quiz_id = ?');
        $stmtPreg->execute([$quiz['id']]);
        $preguntas = $stmtPreg->fetchAll();
        $totalPreguntas = count($preguntas);

        if ($totalPreguntas === 0) {
            echo json_encode(['exito' => false, 'error' => 'El quiz no tiene preguntas configuradas']);
            return;
        }

        $correctas = 0;
        $detalles = [];

        // 3. Evaluar respuestas
        foreach ($preguntas as $preg) {
            $elegida = trim($respuestas[$preg['id']] ?? '');
            $esCorrecto = (strcasecmp($elegida, trim($preg['respuesta_correcta'])) === 0) ? 1 : 0;
            if ($esCorrecto) {
                $correctas++;
            }
            $detalles[$preg['id']] = [
                'elegida' => $elegida,
                'correcta' => $preg['respuesta_correcta'],
                'es_correcto' => $esCorrecto,
                'retroalimentacion' => $preg['retroalimentacion']
            ];
        }

        $puntaje = ($correctas / $totalPreguntas) * 100.00;
        $aprobado = ($puntaje >= (float)$quiz['puntaje_minimo']) ? 1 : 0;

        // 4. Guardar Intento de Quiz
        $stmtCount = $pdo->prepare('SELECT COUNT(*) FROM intento_quiz WHERE usuario_id = ? AND quiz_id = ?');
        $stmtCount->execute([$uid, $quiz['id']]);
        $intentosPrevios = (int)$stmtCount->fetchColumn();
        $numeroIntento = $intentosPrevios + 1;

        $intentoId = generarUUID();
        $stmtInsInt = $pdo->prepare('INSERT INTO intento_quiz (id, quiz_id, usuario_id, puntaje, aprobado, numero_intento, duracion_seg) VALUES (?, ?, ?, ?, ?, ?, ?)');
        $stmtInsInt->execute([$intentoId, $quiz['id'], $uid, $puntaje, $aprobado, $numeroIntento, $duracionSeg]);

        // Guardar respuestas individuales
        $stmtInsResp = $pdo->prepare('INSERT INTO respuesta_quiz (id, intento_quiz_id, pregunta_id, respuesta_elegida, es_correcto) VALUES (?, ?, ?, ?, ?)');
        foreach ($detalles as $pregId => $det) {
            $stmtInsResp->execute([generarUUID(), $intentoId, $pregId, $det['elegida'], $det['es_correcto']]);
        }

        // 5. Actualizar Progreso del RAP (mejor puntaje y completado)
        $progresoModel = new Progreso();
        $progresoModel->guardarMejorPuntaje($uid, $rapId, $puntaje);

        $xpGanados = 0;
        $insigniaGanada = null;

        if ($aprobado) {
            // Completado al 100% al aprobar
            $progresoModel->actualizarProgreso($uid, $rapId, 100.00, 1);

            // Recompensas de Gamificación
            $userModel = new User();
            
            // Otorgar XP (+50 XP por aprobación)
            $xpGanados = 50;
            $userModel->actualizarXP($uid, $xpGanados);

            // Verificar e Insignias
            // 1. "Quiz Perfecto" si obtiene 100%
            if ($puntaje === 100.00) {
                $stmtIns = $pdo->prepare('SELECT id, nombre FROM insignia WHERE nombre = "Quiz Perfecto" LIMIT 1');
                $stmtIns->execute();
                $ins = $stmtIns->fetch();
                if ($ins) {
                    $userModel->otorgarInsignia($uid, $ins['id']);
                    $insigniaGanada = $ins['nombre'];
                }
            }

            // 2. Insignia de RAP si obtiene >= 90%
            if ($puntaje >= 90.00) {
                // Busquemos la insignia según el orden de nivel. Nivel 1 -> "Primer Nivel"
                $stmtNiv = $pdo->prepare('SELECT n.orden FROM rap r JOIN nivel n ON n.id = r.nivel_id WHERE r.id = ? LIMIT 1');
                $stmtNiv->execute([$rapId]);
                $nivelOrden = (int)$stmtNiv->fetchColumn();

                if ($nivelOrden === 1) {
                    $stmtIns = $pdo->prepare('SELECT id, nombre FROM insignia WHERE nombre = "Primer Nivel" LIMIT 1');
                    $stmtIns->execute();
                    $ins = $stmtIns->fetch();
                    if ($ins) {
                        $userModel->otorgarInsignia($uid, $ins['id']);
                        $insigniaGanada = $insigniaGanada ? $insigniaGanada . " y " . $ins['nombre'] : $ins['nombre'];
                    }
                }
            }
        }

        echo json_encode([
            'exito' => true,
            'puntaje' => $puntaje,
            'aprobado' => (bool)$aprobado,
            'xp_ganados' => $xpGanados,
            'insignia_ganada' => $insigniaGanada,
            'detalles' => $detalles
        ]);
    }



    public function vocabulario(): void {
        $pdo = obtenerConexion();
        $uid = $_SESSION['usuario_id'];

        // Cargar el vocabulario marcado como difícil para repaso (HU02)
        $stmt = $pdo->prepare(
            'SELECT v.*, c.nombre AS categoria_nombre, a.nombre AS area_nombre, n.nombre AS nivel_nombre,
                    (SELECT 1 FROM vocabulario_marcado vm WHERE vm.vocabulario_id = v.id AND vm.usuario_id = ?) AS es_dificil
             FROM vocabulario v
             LEFT JOIN categoria_vocabulario c ON c.id = v.categoria_id
             LEFT JOIN area_clinica a ON a.id = v.area_clinica_id
             JOIN rap r ON r.id = v.rap_id
             JOIN nivel n ON n.id = r.nivel_id
             WHERE v.activo = 1 AND r.activo = 1
             HAVING es_dificil = 1
             ORDER BY n.orden, v.termino_en'
        );
        $stmt->execute([$uid]);
        $vocabulario = $stmt->fetchAll();

        $this->render('aprendiz/vocabulario', compact('vocabulario'));
    }

    public function glosario(): void {
        $pdo = obtenerConexion();
        
        $areaId = limpiar($_GET['area'] ?? '');
        $categoriaId = limpiar($_GET['categoria'] ?? '');
        $nivelId = limpiar($_GET['nivel'] ?? '');
        $busqueda = limpiar($_GET['q'] ?? '');

        $sql = "SELECT v.*, c.nombre AS categoria_nombre, a.nombre AS area_nombre, n.nombre AS nivel_nombre
                FROM vocabulario v
                LEFT JOIN categoria_vocabulario c ON c.id = v.categoria_id
                LEFT JOIN area_clinica a ON a.id = v.area_clinica_id
                JOIN rap r ON r.id = v.rap_id
                JOIN nivel n ON n.id = r.nivel_id
                WHERE v.activo = 1 AND r.activo = 1";
        
        $params = [];
        if ($areaId) {
            $sql .= " AND v.area_clinica_id = ?";
            $params[] = $areaId;
        }
        if ($categoriaId) {
            $sql .= " AND v.categoria_id = ?";
            $params[] = $categoriaId;
        }
        if ($nivelId) {
            $sql .= " AND r.nivel_id = ?";
            $params[] = $nivelId;
        }
        if ($busqueda) {
            $sql .= " AND (v.termino_en LIKE ? OR v.termino_es LIKE ?)";
            $params[] = "%$busqueda%";
            $params[] = "%$busqueda%";
        }
        
        $sql .= " ORDER BY v.termino_en ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $vocabulario = $stmt->fetchAll();

        // Obtener filtros
        $areas = $pdo->query("SELECT id, nombre FROM area_clinica ORDER BY nombre")->fetchAll();
        $categorias = $pdo->query("SELECT id, nombre FROM categoria_vocabulario ORDER BY nombre")->fetchAll();
        $niveles = $pdo->query("SELECT id, nombre FROM nivel ORDER BY orden")->fetchAll();

        $this->render('aprendiz/glosario', compact('vocabulario', 'areas', 'categorias', 'niveles', 'areaId', 'categoriaId', 'nivelId', 'busqueda'));
    }

    public function perfil(): void {
        $uid = $_SESSION['usuario_id'] ?? null;
        if ($uid) {
            $userModel = new User();
            $usuario = $userModel->obtenerPorId($uid);

            // Cargar datos extra para gamificación
            $historialQuizzes = $userModel->obtenerHistorialQuizzes($uid);
            $insigniasGanadas = $userModel->obtenerInsigniasGanadas($uid);
            $todasInsignias   = $userModel->obtenerTodasInsignias();

            // Resolver programa_id de la BD para el leaderboard
            $pdo = obtenerConexion();
            $stmtProg = $pdo->prepare('SELECT programa_id FROM usuarios WHERE id = ? LIMIT 1');
            $stmtProg->execute([$uid]);
            $programaId = $stmtProg->fetchColumn();

            $leaderboard = $userModel->obtenerLeaderboardSemanal($programaId);
            $heatmapActivo = $userModel->obtenerHeatmapActividad($uid);

            $this->render('aprendiz/perfil', [
                'usuario' => $usuario,
                'historialQuizzes' => $historialQuizzes,
                'insigniasGanadas' => $insigniasGanadas,
                'todasInsignias' => $todasInsignias,
                'leaderboard' => $leaderboard,
                'heatmapActivo' => $heatmapActivo
            ]);
        } else {
            $this->redirect('login');
        }
    }

    public function actualizarPerfil(): void {
        if (!validarTokenCSRF($_POST['csrf_token'] ?? '')) {
            $this->redirect('aprendiz/perfil?error=csrf');
            return;
        }

        $uid    = $_SESSION['usuario_id'] ?? null;
        $accion = limpiar($_POST['accion'] ?? '');

        if (!$uid) {
            $this->redirect('login');
            return;
        }

        $userModel = new User();
        $usuario   = $userModel->obtenerPorId($uid);

        if ($accion === 'nombre') {
            $nombre = trim(limpiar($_POST['nombre_completo'] ?? ''));
            if (empty($nombre)) {
                $this->redirect('aprendiz/perfil?error=nombre');
                return;
            }
            $userModel->actualizarNombre($uid, $nombre);
            $_SESSION['nombre'] = $nombre;
            $this->redirect('aprendiz/perfil?exito=nombre');

        } elseif ($accion === 'clave') {
            $claveActual   = $_POST['clave_actual']    ?? '';
            $claveNueva    = $_POST['clave_nueva']     ?? '';
            $claveConfirma = $_POST['clave_confirmar'] ?? '';

            // Verificar contraseña actual
            $hashActual = $userModel->obtenerHashContrasena($uid);
            if (!password_verify($claveActual, $hashActual)) {
                $this->redirect('aprendiz/perfil?error=clave_actual');
                return;
            }
            if (strlen($claveNueva) < 8) {
                $this->redirect('aprendiz/perfil?error=clave_corta');
                return;
            }
            if ($claveNueva !== $claveConfirma) {
                $this->redirect('aprendiz/perfil?error=clave_no_coincide');
                return;
            }

            $nuevoHash = password_hash($claveNueva, PASSWORD_BCRYPT, ['cost' => 12]);
            $userModel->actualizarContrasena($uid, $nuevoHash);
            $this->redirect('aprendiz/perfil?exito=clave');
        } else {
            $this->redirect('aprendiz/perfil');
        }
    }
}
