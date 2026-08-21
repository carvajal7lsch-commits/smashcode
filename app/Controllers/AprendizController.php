<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;
use App\Models\Progreso;
use App\Models\GamificacionConfig;
use App\Models\Programa;
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
            'SELECT r.id, r.titulo, r.activo AS rap_activo, r.nivel_id, n.nombre AS nivel_nombre, n.orden AS nivel_orden, n.descripcion AS nivel_descripcion
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

        // Obtener TODOS los RAPs activos asociados a este mismo nivel para unificar la experiencia del módulo
        $stmtAllRaps = $pdo->prepare('SELECT id FROM rap WHERE nivel_id = ? AND activo = 1 ORDER BY orden ASC');
        $stmtAllRaps->execute([$rap['nivel_id']]);
        $allRapIds = $stmtAllRaps->fetchAll(PDO::FETCH_COLUMN);
        if (empty($allRapIds)) {
            $allRapIds = [$rapId];
        }
        $inRaps = implode(',', array_fill(0, count($allRapIds), '?'));

        // Obtener Vocabulario unificado de todos los RAPs del módulo
        $stmtVoc = $pdo->prepare("SELECT * FROM vocabulario WHERE rap_id IN ($inRaps) AND activo = 1 ORDER BY rap_id, id");
        $stmtVoc->execute($allRapIds);
        $vocabulario = $stmtVoc->fetchAll();

        // Obtener vocabulario marcado como difícil por el usuario
        $uid = $_SESSION['usuario_id'];
        $stmtMarc = $pdo->prepare('SELECT vocabulario_id FROM vocabulario_marcado WHERE usuario_id = ?');
        $stmtMarc->execute([$uid]);
        $marcados = $stmtMarc->fetchAll(PDO::FETCH_COLUMN);

        // Obtener Diálogos y sus turnos unificados
        $stmtDia = $pdo->prepare("SELECT * FROM dialogo WHERE rap_id IN ($inRaps) AND activo = 1 ORDER BY rap_id, id");
        $stmtDia->execute($allRapIds);
        $dialogos = $stmtDia->fetchAll();

        foreach ($dialogos as &$d) {
            $stmtTur = $pdo->prepare('SELECT * FROM turno_dialogo WHERE dialogo_id = ? ORDER BY orden_turno ASC');
            $stmtTur->execute([$d['id']]);
            $d['turnos'] = $stmtTur->fetchAll();
        }

        // Obtener Ejercicios y sus opciones unificados
        $stmtEj = $pdo->prepare("SELECT * FROM ejercicio WHERE rap_id IN ($inRaps) AND activo = 1 ORDER BY rap_id, id");
        $stmtEj->execute($allRapIds);
        $ejercicios = $stmtEj->fetchAll();

        foreach ($ejercicios as &$ej) {
            $stmtOpc = $pdo->prepare('SELECT id, texto, es_correcta, retroalimentacion FROM ejercicio_opcion WHERE ejercicio_id = ?');
            $stmtOpc->execute([$ej['id']]);
            $ej['opciones'] = $stmtOpc->fetchAll();
        }

        // Obtener Quizzes y Preguntas unificados
        $stmtQuiz = $pdo->prepare("SELECT * FROM quiz WHERE rap_id IN ($inRaps) AND activo = 1");
        $stmtQuiz->execute($allRapIds);
        $quizzes = $stmtQuiz->fetchAll();

        $quiz = !empty($quizzes) ? $quizzes[0] : null;
        $preguntas = [];

        if (!empty($quizzes)) {
            $quizIds = array_column($quizzes, 'id');
            $inQuiz = implode(',', array_fill(0, count($quizIds), '?'));
            $stmtPreg = $pdo->prepare("SELECT id, texto, opciones, respuesta_correcta, retroalimentacion FROM pregunta WHERE quiz_id IN ($inQuiz)");
            $stmtPreg->execute($quizIds);
            $preguntas = $stmtPreg->fetchAll();
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

    /**
     * Registra el intento de un ejercicio del Momento 3 (HU07).
     *
     * Hasta ahora los ejercicios se validaban solo en el navegador y no dejaban
     * rastro: la tabla intento_ejercicio quedaba siempre vacía, así que ni el
     * heatmap de actividad (HU05/HU15) ni el informe de ejercicios con mayor
     * tasa de error del instructor (HU06/HU23) tenían de dónde leer.
     */
    public function guardarIntentoEjercicio(): void {
        header('Content-Type: application/json');
        $uid = $_SESSION['usuario_id'];

        $ejercicioId = limpiar($_POST['ejercicio_id'] ?? '');
        $opcionId    = limpiar($_POST['opcion_id'] ?? '');
        $respuesta   = mb_substr(trim((string)($_POST['respuesta'] ?? '')), 0, 500);
        $tiempoMs    = (int)($_POST['tiempo_respuesta_ms'] ?? 0);
        $esCorrectoCliente = ((int)($_POST['es_correcto'] ?? 0) === 1) ? 1 : 0;

        if (empty($ejercicioId)) {
            echo json_encode(['exito' => false, 'error' => 'Ejercicio no provisto']);
            return;
        }

        // Admin e instructor recorren el RAP en modo vista previa: no ensucian las métricas
        if (in_array(obtenerRolSesion(), ['admin', 'instructor'])) {
            echo json_encode(['exito' => true, 'preview' => true]);
            return;
        }

        $pdo = obtenerConexion();

        $stmtEj = $pdo->prepare('SELECT id FROM ejercicio WHERE id = ? AND activo = 1 LIMIT 1');
        $stmtEj->execute([$ejercicioId]);
        if (!$stmtEj->fetchColumn()) {
            echo json_encode(['exito' => false, 'error' => 'Ejercicio inexistente o inactivo']);
            return;
        }

        $esCorrecto = $this->resolverAciertoEjercicio($pdo, $ejercicioId, $opcionId, $respuesta, $esCorrectoCliente);

        $stmtCount = $pdo->prepare('SELECT COUNT(*) FROM intento_ejercicio WHERE usuario_id = ? AND ejercicio_id = ?');
        $stmtCount->execute([$uid, $ejercicioId]);
        $numeroIntento = (int)$stmtCount->fetchColumn() + 1;

        $stmtIns = $pdo->prepare(
            'INSERT INTO intento_ejercicio (id, ejercicio_id, usuario_id, respuesta_elegida, es_correcto, numero_intento, tiempo_respuesta_ms)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        $stmtIns->execute([
            generarUUID(),
            $ejercicioId,
            $uid,
            $respuesta !== '' ? $respuesta : null,
            $esCorrecto,
            $numeroIntento,
            $tiempoMs > 0 ? $tiempoMs : null
        ]);

        echo json_encode([
            'exito'          => true,
            'es_correcto'    => (bool)$esCorrecto,
            'numero_intento' => $numeroIntento
        ]);
    }

    /**
     * Decide si un intento de ejercicio fue correcto sin fiarse del navegador
     * cuando la base de datos permite comprobarlo.
     *
     * - Si llega el id de la opción elegida, manda `es_correcta` de esa fila.
     * - Si no, y el ejercicio tiene una única opción correcta, se compara el
     *   texto (pasado por normalizarTextoEspanol(), que es la misma forma en
     *   que la vista lo pintó, para que las tildes no generen falsos fallos).
     * - Los tipos compuestos (emparejar, ordenar diálogo, role play) no tienen
     *   una única respuesta almacenada: ahí se conserva el resultado del cliente.
     */
    private function resolverAciertoEjercicio(\PDO $pdo, string $ejercicioId, string $opcionId, string $respuesta, int $esCorrectoCliente): int {
        if ($opcionId !== '') {
            $stmt = $pdo->prepare('SELECT es_correcta FROM ejercicio_opcion WHERE id = ? AND ejercicio_id = ? LIMIT 1');
            $stmt->execute([$opcionId, $ejercicioId]);
            $fila = $stmt->fetch();
            if ($fila !== false) {
                return ((int)$fila['es_correcta'] === 1) ? 1 : 0;
            }
        }

        if ($respuesta !== '') {
            $stmt = $pdo->prepare('SELECT texto FROM ejercicio_opcion WHERE ejercicio_id = ? AND es_correcta = 1');
            $stmt->execute([$ejercicioId]);
            $correctas = $stmt->fetchAll(PDO::FETCH_COLUMN);

            if (count($correctas) === 1) {
                $esperada = mb_strtolower(trim(normalizarTextoEspanol((string)$correctas[0])));
                $recibida = mb_strtolower(trim(normalizarTextoEspanol($respuesta)));
                return ($esperada === $recibida) ? 1 : 0;
            }
        }

        return $esCorrectoCliente;
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
                'detalles' => [],
                'resumen' => null
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
        $stmtPreg = $pdo->prepare('SELECT id, texto, respuesta_correcta, retroalimentacion FROM pregunta WHERE quiz_id = ?');
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
                'retroalimentacion' => $preg['retroalimentacion'],
                'texto' => $preg['texto']
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

            // Recompensas de Gamificación dinámicas
            $userModel = new User();
            $gamConfig = new GamificacionConfig();
            
            $xpQuizAprobado = $gamConfig->obtenerValor('xp_quiz_aprobado', 50);
            $xpQuizPerfecto = $gamConfig->obtenerValor('xp_quiz_perfecto', 100);

            $xpGanados = $xpQuizAprobado;
            if ($puntaje === 100.00) {
                $xpGanados += $xpQuizPerfecto;
            }
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
            'detalles' => $detalles,
            'resumen' => $this->construirResumenRap($pdo, $uid, $rapId, $detalles, $correctas, $totalPreguntas, (bool)$aprobado)
        ]);
    }



    /**
     * Arma el resumen de cierre del RAP (HU07): puntaje, fortalezas, áreas de
     * mejora y recomendaciones de actividades.
     *
     * Las fortalezas y las mejoras salen de las preguntas del quiz recién
     * presentado; las recomendaciones se derivan del estado real del aprendiz
     * en este RAP (ejercicios fallados y vocabulario marcado como difícil),
     * no de frases genéricas.
     */
    private function construirResumenRap(\PDO $pdo, string $uid, string $rapId, array $detalles, int $correctas, int $totalPreguntas, bool $aprobado): array {
        $fortalezas = [];
        $mejoras    = [];

        foreach ($detalles as $det) {
            $enunciado = trim((string)($det['texto'] ?? ''));
            if ($enunciado === '') {
                continue;
            }

            if ((int)$det['es_correcto'] === 1) {
                $fortalezas[] = $enunciado;
            } else {
                $mejoras[] = [
                    'pregunta'          => $enunciado,
                    'tu_respuesta'      => $det['elegida'] !== '' ? $det['elegida'] : '(sin responder)',
                    'correcta'          => $det['correcta'],
                    'retroalimentacion' => $det['retroalimentacion'] ?? ''
                ];
            }
        }

        // Ejercicios del Momento 3 de este RAP que el aprendiz aún no ha acertado nunca
        $stmtEj = $pdo->prepare(
            'SELECT e.enunciado
             FROM ejercicio e
             JOIN intento_ejercicio ie ON ie.ejercicio_id = e.id AND ie.usuario_id = ?
             WHERE e.rap_id = ? AND e.activo = 1
             GROUP BY e.id
             HAVING MAX(ie.es_correcto) = 0
             ORDER BY COUNT(ie.id) DESC
             LIMIT 3'
        );
        $stmtEj->execute([$uid, $rapId]);
        $ejerciciosFallados = $stmtEj->fetchAll(PDO::FETCH_COLUMN);

        // Vocabulario que el propio aprendiz marcó como difícil en este RAP (HU02)
        $stmtVoc = $pdo->prepare(
            'SELECT v.termino_en
             FROM vocabulario_marcado vm
             JOIN vocabulario v ON v.id = vm.vocabulario_id
             WHERE vm.usuario_id = ? AND v.rap_id = ?
             LIMIT 5'
        );
        $stmtVoc->execute([$uid, $rapId]);
        $vocabularioDificil = $stmtVoc->fetchAll(PDO::FETCH_COLUMN);

        $recomendaciones = [];

        if (!empty($mejoras)) {
            $recomendaciones[] = [
                'icono' => 'fa-rotate-left',
                'texto' => 'Repasa el Momento 1 (vocabulario) antes de volver a presentar el quiz: fallaste '
                           . count($mejoras) . ' de ' . $totalPreguntas . ' preguntas.'
            ];
        }

        if (!empty($ejerciciosFallados)) {
            $recomendaciones[] = [
                'icono' => 'fa-dumbbell',
                'texto' => 'Vuelve al Momento 3 y repite estos ejercicios: '
                           . implode(' · ', array_map(fn($e) => mb_strimwidth($e, 0, 60, '…'), $ejerciciosFallados))
            ];
        }

        if (!empty($vocabularioDificil)) {
            $recomendaciones[] = [
                'icono' => 'fa-bookmark',
                'texto' => 'Practica la pronunciación del vocabulario que marcaste como difícil: '
                           . implode(', ', $vocabularioDificil)
            ];
        }

        if (!$aprobado) {
            $recomendaciones[] = [
                'icono' => 'fa-arrows-rotate',
                'texto' => 'Puedes repetir este RAP las veces que necesites: siempre se conserva tu mejor puntaje.'
            ];
        } elseif (empty($recomendaciones)) {
            $recomendaciones[] = [
                'icono' => 'fa-forward',
                'texto' => '¡Dominaste este RAP! Continúa con el siguiente en tu mapa de aprendizaje.'
            ];
        }

        return [
            'correctas'       => $correctas,
            'total'           => $totalPreguntas,
            'fortalezas'      => array_slice($fortalezas, 0, 4),
            'mejoras'         => array_slice($mejoras, 0, 4),
            'recomendaciones' => $recomendaciones
        ];
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

        // Obtener vocabulario marcado por el usuario para las estrellas
        $uid = $_SESSION['usuario_id'] ?? null;
        $marcados = [];
        if ($uid) {
            $stmtMarc = $pdo->prepare('SELECT vocabulario_id FROM vocabulario_marcado WHERE usuario_id = ?');
            $stmtMarc->execute([$uid]);
            $marcados = $stmtMarc->fetchAll(PDO::FETCH_COLUMN);
        }

        $this->render('aprendiz/glosario', compact('vocabulario', 'areas', 'categorias', 'niveles', 'areaId', 'categoriaId', 'nivelId', 'busqueda', 'marcados'));
    }

    public function perfil(): void {
        $uid = $_SESSION['usuario_id'] ?? null;
        if ($uid) {
            $userModel = new User();
            $usuario = $userModel->obtenerPorId($uid);

            // Cargar configuración de gamificación
            $gamConfig = new GamificacionConfig();
            $configGam = $gamConfig->obtenerTodas();
            $xpPorNivel = $configGam['xp_por_nivel'] ?? 500;
            $diasRachaMeta = $configGam['dias_racha_insignia'] ?? 7;

            // Rango clínico y Racha actual
            $rangoClinico = $userModel->calcularRangoClinico((int)($usuario['xp_puntos'] ?? 0), $xpPorNivel);
            $rachaDias = $userModel->calcularRachaDias($uid);

            $pdo = obtenerConexion();

            // Verificar e insignia de racha automática
            if ($rachaDias >= $diasRachaMeta) {
                $stmtIns = $pdo->prepare('SELECT id FROM insignia WHERE nombre LIKE "%Racha%" LIMIT 1');
                $stmtIns->execute();
                $insRachaId = $stmtIns->fetchColumn();
                if ($insRachaId) {
                    $userModel->otorgarInsignia($uid, $insRachaId);
                }
            }

            // Cargar datos extra para gamificación
            $historialQuizzes = $userModel->obtenerHistorialQuizzes($uid);
            $insigniasGanadas = $userModel->obtenerInsigniasGanadas($uid);
            $todasInsignias   = $userModel->obtenerTodasInsignias();

            $programaId = $usuario['programa_id'] ?? null;

            // Ficha y programa también pueblan el formulario de datos de formación (HU16),
            // que necesitan las cuentas creadas con Google: nacen sin estos datos.
            $fichaSena = $usuario['ficha_sena'] ?? '';

            // Programas activos para el selector (mismo modelo que usa el registro)
            $programaModel = new Programa();
            $programas = $programaModel->obtenerTodos();

            $leaderboard = $userModel->obtenerLeaderboardSemanal($programaId);
            $heatmapActivo = $userModel->obtenerHeatmapActividad($uid);

            $this->render('aprendiz/perfil', [
                'usuario' => $usuario,
                'rangoClinico' => $rangoClinico,
                'rachaDias' => $rachaDias,
                'configGam' => $configGam,
                'historialQuizzes' => $historialQuizzes,
                'insigniasGanadas' => $insigniasGanadas,
                'todasInsignias' => $todasInsignias,
                'leaderboard' => $leaderboard,
                'heatmapActivo' => $heatmapActivo,
                'programas' => $programas,
                'fichaSena' => $fichaSena,
                'programaId' => $programaId
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

        } elseif ($accion === 'ficha') {
            // HU16: datos de formación obligatorios para el aprendiz.
            // Los usuarios creados con Google llegan aquí desde el callback (?completar=1).
            $ficha       = trim(limpiar($_POST['ficha_sena'] ?? ''));
            $programaId  = limpiar($_POST['programa_id'] ?? '');
            $completando = limpiar($_POST['completar'] ?? '') === '1';
            // Conservar el modo "completar" en los redirects de error para no perder el aviso
            $sufijo      = $completando ? '&completar=1' : '';

            if (empty($ficha)) {
                $this->redirect('aprendiz/perfil?error=ficha' . $sufijo);
                return;
            }

            // El programa debe existir y estar activo (mismo modelo que puebla el selector)
            $programaModel = new Programa();
            $programa = empty($programaId) ? null : $programaModel->obtenerPorId($programaId);
            if (!$programa || empty($programa['activo'])) {
                $this->redirect('aprendiz/perfil?error=programa' . $sufijo);
                return;
            }

            if (!$userModel->actualizarFichaYPrograma($uid, $ficha, $programaId)) {
                $this->redirect('aprendiz/perfil?error=ficha_guardar' . $sufijo);
                return;
            }

            // Perfil ya completo: si venía del flujo de Google, seguir al dashboard
            if ($completando) {
                $this->redirect('');
                return;
            }
            $this->redirect('aprendiz/perfil?exito=ficha');

        } else {
            $this->redirect('aprendiz/perfil');
        }
    }
}
