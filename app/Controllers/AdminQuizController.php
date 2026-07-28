<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Quiz;
use App\Models\Pregunta;
use App\Models\Rap;

class AdminQuizController extends Controller {

    public function index() {
        iniciarSesion();
        if (!estaAutenticado() || obtenerRolSesion() !== 'admin') {
            header('Location: ' . PROYECTO_PATH . '/login');
            exit;
        }

        $rapId = $_GET['rap_id'] ?? null;
        if (!$rapId) {
            header('Location: ' . PROYECTO_PATH . '/admin/raps');
            exit;
        }

        $rapModel = new Rap();
        $quizModel = new Quiz();
        $preguntaModel = new Pregunta();

        $rap = $rapModel->obtenerPorId($rapId);
        if (!$rap) {
            header('Location: ' . PROYECTO_PATH . '/admin/raps');
            exit;
        }

        $quiz = $quizModel->obtenerPorRap($rapId);
        $preguntas = [];
        if ($quiz) {
            $preguntas = $preguntaModel->obtenerPorQuiz($quiz['id']);
            foreach ($preguntas as &$p) {
                if (is_string($p['opciones'])) {
                    $p['opciones_arr'] = json_decode($p['opciones'], true) ?? [];
                } else {
                    $p['opciones_arr'] = $p['opciones'] ?? [];
                }
            }
        }

        $this->render('admin/quizzes', [
            'rap' => $rap,
            'quiz' => $quiz,
            'preguntas' => $preguntas
        ]);
    }

    public function save() {
        iniciarSesion();
        if (!estaAutenticado() || obtenerRolSesion() !== 'admin') {
            http_response_code(403);
            echo json_encode(['error' => 'No autorizado']);
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
            exit;
        }

        if (!validarTokenCSRF($_POST['csrf_token'] ?? '')) {
            http_response_code(403);
            echo json_encode(['error' => 'Token CSRF inválido']);
            exit;
        }

        $rapId = $_POST['rap_id'] ?? '';
        $puntajeMinimo = $_POST['puntaje_minimo'] ?? 60.00;
        $limiteTiempoSeg = $_POST['limite_tiempo_seg'] ?? 300;
        $maxIntentos = $_POST['max_intentos'] ?? 3;

        if (empty($rapId)) {
            http_response_code(400);
            echo json_encode(['error' => 'RAP no especificado']);
            exit;
        }

        $quizModel = new Quiz();
        $preguntaModel = new Pregunta();

        try {
            $pdo = \App\Core\Model::obtenerConexion();
            $pdo->beginTransaction();

            $quiz = $quizModel->obtenerPorRap($rapId);
            if (!$quiz) {
                $quizId = $quizModel->crear([
                    'rap_id' => $rapId,
                    'puntaje_minimo' => $puntajeMinimo,
                    'limite_tiempo_seg' => $limiteTiempoSeg,
                    'max_intentos' => $maxIntentos
                ]);
            } else {
                $quizId = $quiz['id'];
                $quizModel->actualizar($quizId, [
                    'puntaje_minimo' => $puntajeMinimo,
                    'limite_tiempo_seg' => $limiteTiempoSeg,
                    'max_intentos' => $maxIntentos
                ]);
            }

            // Obtener preguntas actuales para comparar si alguna fue removida
            $preguntasActuales = $preguntaModel->obtenerPorQuiz($quizId);
            $idsActuales = array_column($preguntasActuales, 'id');
            $idsEnviados = [];

            // Guardar o actualizar preguntas
            if (isset($_POST['preguntas']) && is_array($_POST['preguntas'])) {
                foreach ($_POST['preguntas'] as $p) {
                    if (empty($p['texto']) || empty($p['respuesta_correcta'])) continue;

                    $opciones = $p['opciones'] ?? [];
                    if (is_array($opciones)) {
                        $opcionesClean = array_values(array_filter($opciones, fn($v) => !empty(trim($v))));
                    } else {
                        $opcionesClean = [$p['respuesta_correcta']];
                    }

                    $preguntaId = $p['id'] ?? null;
                    if (!empty($preguntaId) && in_array($preguntaId, $idsActuales)) {
                        $idsEnviados[] = $preguntaId;
                        $preguntaModel->actualizar($preguntaId, [
                            'texto' => $p['texto'],
                            'opciones' => $opcionesClean,
                            'respuesta_correcta' => $p['respuesta_correcta'],
                            'retroalimentacion' => $p['retroalimentacion'] ?? null
                        ]);
                    } else {
                        $newId = $preguntaModel->crear([
                            'quiz_id' => $quizId,
                            'texto' => $p['texto'],
                            'opciones' => $opcionesClean,
                            'respuesta_correcta' => $p['respuesta_correcta'],
                            'retroalimentacion' => $p['retroalimentacion'] ?? null
                        ]);
                        $idsEnviados[] = $newId;
                    }
                }
            }

            // Intentar eliminar solo las preguntas que el usuario borró expresamente en la UI
            foreach ($idsActuales as $idViejo) {
                if (!in_array($idViejo, $idsEnviados)) {
                    try {
                        $preguntaModel->eliminar($idViejo);
                    } catch (\Exception $eEx) {
                        // Si falla la restricción FK (porque aprendices ya la respondieron), se ignora la eliminación para no romper historial
                    }
                }
            }

            $pdo->commit();
            echo json_encode(['success' => true]);

        } catch (\Exception $e) {
            $pdo->rollBack();
            http_response_code(500);
            echo json_encode(['error' => 'Error al guardar el quiz: ' . $e->getMessage()]);
        }
    }
}
