<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Ejercicio;
use App\Models\EjercicioOpcion;
use App\Models\Rap;
use App\Models\ContenidoCurso;

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
            'ejercicios' => $ejercicios,
            // RF-34: opciones para enlazar un recurso de ayuda a cada ejercicio
            'vocabularioAyuda' => $ejercicioModel->vocabularioDelModulo($rapId)
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

        // HU20: instrucciones, máximo de intentos y puntaje de cada ejercicio
        $instrucciones = trim((string) ($_POST['instrucciones'] ?? ''));
        $instrucciones = $instrucciones === '' ? null : mb_substr($instrucciones, 0, 500);
        $maxIntentos   = max(1, min(10, (int) ($_POST['max_intentos'] ?? 3)));
        $puntos        = max(1, min(100, (int) ($_POST['puntos'] ?? 10)));

        // RF-34: palabra de vocabulario que se ofrece como ayuda al fallar. Opcional.
        $vocabAyuda = trim((string) ($_POST['vocab_ayuda_id'] ?? ''));

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
            $contenido = new ContenidoCurso();
            $contenido->bloquearModulo($rapId);

            $opciones = $_POST['opciones'] ?? [];
            if (!is_array($opciones)) throw new \DomainException('Opciones inválidas.');
            ContenidoCurso::validarEjercicio($tipo,$opciones,$enunciado);
            if ($ejercicioId && ($ejercicioModel->obtenerPorId($ejercicioId)['rap_id'] ?? null) !== $rapId) throw new \DomainException('El ejercicio no pertenece al RAP.');

            // Se descarta en silencio la palabra que no exista o sea de otro módulo:
            // el ejercicio se guarda igual, solo se queda sin recurso de ayuda.
            $vocabAyudaId = $ejercicioModel->ayudaValida($vocabAyuda, $rapId);

            if (empty($ejercicioId)) {
                // Crear
                $ejercicioId = $ejercicioModel->crear([
                    'rap_id' => $rapId,
                    'tipo' => $tipo,
                    'enunciado' => $enunciado,
                    'instrucciones' => $instrucciones,
                    'max_intentos' => $maxIntentos,
                    'puntos' => $puntos,
                    'vocab_ayuda_id' => $vocabAyudaId
                ]);
            } else {
                // Actualizar
                $ejercicioModel->actualizar($ejercicioId, [
                    'tipo' => $tipo,
                    'enunciado' => $enunciado,
                    'instrucciones' => $instrucciones,
                    'max_intentos' => $maxIntentos,
                    'puntos' => $puntos,
                    'vocab_ayuda_id' => $vocabAyudaId
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

            $contenido->validarPublicado($rapId);
            $pdo->commit();
            echo json_encode(['success' => true]);

        } catch (\DomainException $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            http_response_code(422);
            echo json_encode(['error' => $e->getMessage()]);
        } catch (\Throwable $e) {
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

        $pdo = \App\Core\Model::obtenerConexion();
        try {
            $pdo->beginTransaction();
            $ejercicioModel = new Ejercicio();
            $registro = $ejercicioModel->obtenerPorId($id);
            if (!$registro) throw new \DomainException('No se encontró el ejercicio.');
            $contenido = new ContenidoCurso();
            $contenido->bloquearModulo($registro['rap_id']);
            $ejercicioModel->eliminar($id);
            $contenido->validarPublicado($registro['rap_id']);
            $pdo->commit();
            echo json_encode(['success' => true]);
        } catch (\DomainException $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            http_response_code(422); echo json_encode(['error' => $e->getMessage()]);
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            error_log($e->getMessage());
            http_response_code(500); echo json_encode(['error' => 'No se pudo eliminar el contenido.']);
        }
    }
}
