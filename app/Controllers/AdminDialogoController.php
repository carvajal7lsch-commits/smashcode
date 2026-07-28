<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Dialogo;
use App\Models\TurnoDialogo;
use App\Models\Rap;

class AdminDialogoController extends Controller {

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
        $dialogoModel = new Dialogo();
        $turnoModel = new TurnoDialogo();

        $rap = $rapModel->obtenerPorId($rapId);
        if (!$rap) {
            header('Location: ' . PROYECTO_PATH . '/admin/raps');
            exit;
        }

        $dialogos = $dialogoModel->obtenerPorRap($rapId);
        foreach ($dialogos as &$dial) {
            $dial['turnos'] = $turnoModel->obtenerPorDialogo($dial['id']);
        }

        $this->render('admin/dialogos', [
            'rap' => $rap,
            'dialogos' => $dialogos
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

        $dialogoId = $_POST['id'] ?? null;
        $rapId = $_POST['rap_id'] ?? '';
        $titulo = $_POST['titulo'] ?? '';
        $contexto = $_POST['contexto'] ?? '';
        $participantes = $_POST['participantes'] ?? '';

        if (empty($rapId) || empty($titulo)) {
            http_response_code(400);
            echo json_encode(['error' => 'Datos incompletos']);
            exit;
        }

        $dialogoModel = new Dialogo();
        $turnoModel = new TurnoDialogo();

        try {
            $pdo = \App\Core\Model::obtenerConexion();
            $pdo->beginTransaction();

            if (empty($dialogoId)) {
                $dialogoId = $dialogoModel->crear([
                    'rap_id' => $rapId,
                    'titulo' => $titulo,
                    'contexto' => $contexto,
                    'participantes' => $participantes
                ]);
            } else {
                $dialogoModel->actualizar($dialogoId, [
                    'titulo' => $titulo,
                    'contexto' => $contexto,
                    'participantes' => $participantes
                ]);
                $turnoModel->eliminarPorDialogo($dialogoId);
            }

            // Guardar turnos
            if (isset($_POST['turnos']) && is_array($_POST['turnos'])) {
                foreach ($_POST['turnos'] as $idx => $t) {
                    if (empty($t['hablante']) || empty($t['texto_en'])) continue;

                    $turnoModel->crear([
                        'dialogo_id' => $dialogoId,
                        'orden_turno' => $idx + 1,
                        'hablante' => $t['hablante'],
                        'texto_en' => $t['texto_en'],
                        'texto_es' => $t['texto_es'] ?? ''
                    ]);
                }
            }

            $pdo->commit();
            echo json_encode(['success' => true]);

        } catch (\Exception $e) {
            $pdo->rollBack();
            http_response_code(500);
            echo json_encode(['error' => 'Error al guardar: ' . $e->getMessage()]);
        }
    }

    public function delete() {
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

        $id = $_POST['id'] ?? null;
        if (!$id) {
            http_response_code(400);
            echo json_encode(['error' => 'ID no proporcionado']);
            exit;
        }

        $dialogoModel = new Dialogo();
        if ($dialogoModel->eliminar($id)) {
            echo json_encode(['success' => true]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'No se pudo eliminar el diálogo']);
        }
    }
}
