<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Dialogo;
use App\Models\TurnoDialogo;
use App\Models\Rap;
use App\Models\ContenidoCurso;

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
        // HU21: anotaciones pedagógicas del escenario
        $anotaciones = trim((string) ($_POST['anotaciones'] ?? ''));
        $anotaciones = $anotaciones === '' ? null : mb_substr($anotaciones, 0, 1000);

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
            $contenido = new ContenidoCurso();
            $contenido->bloquearModulo($rapId);

            $turnos = $_POST['turnos'] ?? [];
            if (!is_array($turnos) || !$turnos) throw new \DomainException('El diálogo necesita al menos un turno.');
            foreach ($turnos as $turno) {
                if (!is_array($turno) || trim((string)($turno['hablante'] ?? '')) === '' || trim((string)($turno['texto_en'] ?? '')) === '') {
                    throw new \DomainException('Cada turno necesita hablante y texto en inglés.');
                }
            }
            if ($dialogoId && ($dialogoModel->obtenerPorId($dialogoId)['rap_id'] ?? null) !== $rapId) throw new \DomainException('El diálogo no pertenece al RAP.');

            if (empty($dialogoId)) {
                $dialogoId = $dialogoModel->crear([
                    'rap_id' => $rapId,
                    'titulo' => $titulo,
                    'contexto' => $contexto,
                    'participantes' => $participantes,
                    'anotaciones' => $anotaciones
                ]);
            } else {
                $dialogoModel->actualizar($dialogoId, [
                    'titulo' => $titulo,
                    'contexto' => $contexto,
                    'participantes' => $participantes,
                    'anotaciones' => $anotaciones
                ]);
            }

            // HU21: los turnos se actualizan por id, los nuevos se agregan y los que el
            // admin quitó se desactivan. Antes se borraban todos y se volvían a crear.
            $turnosActuales = [];
            foreach ($turnoModel->obtenerPorDialogo($dialogoId) as $actual) {
                $turnosActuales[$actual['id']] = $actual;
            }
            $idsEnviados = [];
            $orden = 0;

            foreach ((isset($_POST['turnos']) && is_array($_POST['turnos'])) ? $_POST['turnos'] : [] as $idx => $t) {
                if (empty($t['hablante']) || empty($t['texto_en'])) continue;
                $orden++;

                $turnoId = (string) ($t['id'] ?? '');
                $existe  = $turnoId !== '' && isset($turnosActuales[$turnoId]);

                // El audio se toma de la base (no del formulario): se conserva, se quita o se reemplaza
                $audioUrl = $existe ? $turnosActuales[$turnoId]['audio_url'] : null;
                if (!empty($t['quitar_audio'])) {
                    $audioUrl = null;
                }
                $archivo = $this->archivoDeTurno($idx);
                if ($archivo !== null) {
                    $audioUrl = $this->subirAudioTurno($archivo);
                    if ($audioUrl === null) {
                        throw new \RuntimeException("El audio del turno {$orden} no es válido: usa MP3, OGG o WAV de hasta 2 MB.");
                    }
                }

                $datosTurno = [
                    'dialogo_id' => $dialogoId,
                    'orden_turno' => $orden,
                    'hablante' => $t['hablante'],
                    'texto_en' => $t['texto_en'],
                    'texto_es' => $t['texto_es'] ?? '',
                    'audio_url' => $audioUrl
                ];

                if ($existe) {
                    $turnoModel->actualizar($turnoId, $datosTurno);
                    $idsEnviados[] = $turnoId;
                } else {
                    $idsEnviados[] = $turnoModel->crear($datosTurno);
                }
            }

            foreach (array_diff(array_keys($turnosActuales), $idsEnviados) as $idQuitado) {
                $turnoModel->desactivar($idQuitado);
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
            $dialogoModel = new Dialogo();
            $registro = $dialogoModel->obtenerPorId($id);
            if (!$registro) throw new \DomainException('No se encontró el diálogo.');
            $contenido = new ContenidoCurso();
            $contenido->bloquearModulo($registro['rap_id']);
            $dialogoModel->eliminar($id);
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

    /**
     * Archivo de audio enviado para el turno $idx (campo turnos[idx][audio]), o null si no se
     * envió ninguno. Un archivo que no llegó bien (por ejemplo, demasiado grande) es un error.
     */
    private function archivoDeTurno($idx): ?array {
        $archivos = $_FILES['turnos'] ?? null;
        if (!is_array($archivos) || !isset($archivos['error'][$idx]['audio'])) {
            return null;
        }

        $error = (int) $archivos['error'][$idx]['audio'];
        if ($error === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        if ($error !== UPLOAD_ERR_OK) {
            throw new \RuntimeException('No se pudo recibir el audio de un turno: usa MP3, OGG o WAV de hasta 2 MB.');
        }

        return [
            'name'     => (string) $archivos['name'][$idx]['audio'],
            'tmp_name' => (string) $archivos['tmp_name'][$idx]['audio'],
            'size'     => (int) $archivos['size'][$idx]['audio'],
        ];
    }

    /**
     * Guarda el audio de un turno en assets/uploads/audios (HU21) y devuelve su ruta pública,
     * o null si no es MP3, OGG o WAV de hasta 2 MB. En Docker esa carpeta es un volumen,
     * así que lo subido sobrevive a los despliegues.
     */
    private function subirAudioTurno(array $archivo): ?string {
        $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, ['mp3', 'ogg', 'wav'], true) || $archivo['size'] <= 0 || $archivo['size'] > 2 * 1024 * 1024) {
            return null;
        }

        // Además de la extensión, el contenido debe ser audio
        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime  = $finfo ? (string) finfo_file($finfo, $archivo['tmp_name']) : '';
            if ($finfo) {
                finfo_close($finfo);
            }
            if (!str_starts_with($mime, 'audio/') && !in_array($mime, ['application/ogg', 'video/ogg'], true)) {
                return null;
            }
        }

        $directorio = dirname(__DIR__, 2) . '/assets/uploads/audios/';
        if (!is_dir($directorio) && !mkdir($directorio, 0775, true)) {
            return null;
        }

        $nombre = generarUUID() . '.' . $extension;
        return $this->moverArchivo($archivo['tmp_name'], $directorio . $nombre)
            ? '/assets/uploads/audios/' . $nombre
            : null;
    }

    /**
     * Mueve el archivo recibido por HTTP. Aparte para poder probar la subida desde la terminal.
     */
    protected function moverArchivo(string $origen, string $destino): bool {
        return is_uploaded_file($origen) && move_uploaded_file($origen, $destino);
    }
}
