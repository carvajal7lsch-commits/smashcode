<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Ejercicio;
use App\Models\EjercicioOpcion;
use App\Models\Rap;

class AdminEjercicioController extends Controller {

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
        $ejercicioModel = new Ejercicio();
        $opcionModel = new EjercicioOpcion();

        $rap = $rapModel->obtenerPorId($rapId);
        if (!$rap) {
            header('Location: ' . PROYECTO_PATH . '/admin/raps');
            exit;
        }

        $ejercicios = $ejercicioModel->obtenerPorRap($rapId);
        foreach ($ejercicios as &$ej) {
            $ej['opciones'] = $opcionModel->obtenerPorEjercicio($ej['id']);
        }

        $this->render('admin/ejercicios', [
            'rap' => $rap,
            'ejercicios' => $ejercicios
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

        $ejercicioId = $_POST['id'] ?? null;
        $rapId = $_POST['rap_id'] ?? '';
        $tipo = $_POST['tipo'] ?? '';
        $enunciado = $_POST['enunciado'] ?? '';
        $orden = $_POST['orden'] ?? 1;

        if (empty($rapId) || empty($tipo) || empty($enunciado)) {
            http_response_code(400);
            echo json_encode(['error' => 'Datos incompletos']);
            exit;
        }

        $ejercicioModel = new Ejercicio();
        $opcionModel = new EjercicioOpcion();

        try {
            $pdo = \App\Core\Model::obtenerConexion();
            $pdo->beginTransaction();

            if (empty($ejercicioId)) {
                // Crear
                $ejercicioId = $ejercicioModel->crear([
                    'rap_id' => $rapId,
                    'tipo' => $tipo,
                    'enunciado' => $enunciado
                ]);
            } else {
                // Actualizar
                $ejercicioModel->actualizar($ejercicioId, [
                    'tipo' => $tipo,
                    'enunciado' => $enunciado
                ]);
                // Eliminar opciones antiguas para recrearlas
                $opcionModel->eliminarPorEjercicio($ejercicioId);
            }

            // Guardar opciones
            if (isset($_POST['opciones']) && is_array($_POST['opciones'])) {
                foreach ($_POST['opciones'] as $opc) {
                    // Validar formato de la opción
                    if (empty($opc['texto'])) continue;

                    $opcionModel->crear([
                        'ejercicio_id' => $ejercicioId,
                        'texto' => $opc['texto'],
                        'es_correcta' => isset($opc['es_correcta']) && $opc['es_correcta'] == '1' ? 1 : 0,
                        'retroalimentacion' => $opc['retroalimentacion'] ?? null
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

        $ejercicioModel = new Ejercicio();
        if ($ejercicioModel->eliminar($id)) {
            echo json_encode(['success' => true]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'No se pudo eliminar el ejercicio']);
        }
    }
}
