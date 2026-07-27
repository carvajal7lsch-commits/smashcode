<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Vocabulario;
use App\Models\AreaClinica;
use App\Models\CategoriaVocabulario;
use App\Models\Admin;

/**
 * VocabularioController.php
 * Controlador para la gestión de vocabulario médico dentro de cada RAP (HU19).
 */
class VocabularioController extends Controller {

    private Vocabulario $vocabularioModel;
    private AreaClinica $areaModel;
    private CategoriaVocabulario $categoriaModel;
    private Admin $adminModel;

    public function __construct() {
        parent::__construct();
        $this->vocabularioModel = new Vocabulario();
        $this->areaModel        = new AreaClinica();
        $this->categoriaModel   = new CategoriaVocabulario();
        $this->adminModel       = new Admin();
        iniciarSesion();

        if (!estaAutenticado() || obtenerRolSesion() !== 'admin') {
            $this->redirect('login');
        }
    }

    /**
     * Muestra la lista de vocabulario para un RAP específico.
     */
    public function index(): void {
        $rapId = limpiar($_GET['rap_id'] ?? '');
        if (empty($rapId)) {
            $this->redirect('admin/raps');
            return;
        }

        $vocabulario   = $this->vocabularioModel->obtenerPorRap($rapId);
        $totalUsuarios = $this->adminModel->obtenerTotalUsuarios();
        $areas         = $this->areaModel->obtenerTodas(true);
        $categorias    = $this->categoriaModel->obtenerTodas(true);
        
        // Obtener el título del RAP para mostrar en la vista
        $rapTitulo = $this->vocabularioModel->obtenerTituloRap($rapId);
        $rap = ['titulo' => $rapTitulo];
        
        $exito = limpiar($_GET['exito'] ?? '');
        $error = limpiar($_GET['error'] ?? '');

        $this->render('admin/vocabulario/index', compact('vocabulario', 'rapId', 'rap', 'totalUsuarios', 'areas', 'categorias', 'exito', 'error'));
    }

    /**
     * Procesa la creación de un nuevo término.
     */
    public function guardar(): void {
        if (!validarTokenCSRF($_POST['csrf_token'] ?? '')) {
            $this->redirect('admin/raps');
        }

        $rapId = limpiar($_POST['rap_id'] ?? '');
        
        $datos = [
            'id'                => generarUUID(),
            'rap_id'            => $rapId,
            'termino_en'        => limpiar($_POST['termino_en'] ?? ''),
            'termino_es'        => limpiar($_POST['termino_es'] ?? ''),
            'categoria_id'      => limpiar($_POST['categoria_id'] ?? ''),
            'area_clinica_id'   => limpiar($_POST['area_clinica_id'] ?? ''),
            'transcripcion_ipa' => limpiar($_POST['transcripcion_ipa'] ?? ''),
            'oracion_ejemplo'   => limpiar($_POST['oracion_ejemplo'] ?? ''),
            'nivel_dificultad'  => limpiar($_POST['nivel_dificultad'] ?? ''),
            'audio_url'         => null,
            'imagen_url'        => null
        ];

        if (empty($datos['termino_en']) || empty($datos['termino_es'])) {
            $this->redirect("admin/vocabulario?rap_id={$rapId}&error=" . urlencode('Los términos en inglés y español son obligatorios.'));
            return;
        }

        // Procesar subida de archivos si existen
        if (isset($_FILES['audio']) && $_FILES['audio']['error'] === UPLOAD_ERR_OK) {
            $datos['audio_url'] = $this->subirArchivo($_FILES['audio'], 'audios');
        }
        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
            $datos['imagen_url'] = $this->subirArchivo($_FILES['imagen'], 'imagenes');
        }

        $this->vocabularioModel->crear($datos);
        $this->redirect("admin/vocabulario?rap_id={$rapId}&exito=creado");
    }

    /**
     * Procesa la actualización de un término.
     */
    public function actualizar(): void {
        if (!validarTokenCSRF($_POST['csrf_token'] ?? '')) {
            $this->redirect('admin/raps');
        }

        $id    = limpiar($_POST['id'] ?? '');
        $rapId = limpiar($_POST['rap_id'] ?? '');
        
        $datos = [
            'termino_en'        => limpiar($_POST['termino_en'] ?? ''),
            'termino_es'        => limpiar($_POST['termino_es'] ?? ''),
            'categoria_id'      => limpiar($_POST['categoria_id'] ?? ''),
            'area_clinica_id'   => limpiar($_POST['area_clinica_id'] ?? ''),
            'transcripcion_ipa' => limpiar($_POST['transcripcion_ipa'] ?? ''),
            'oracion_ejemplo'   => limpiar($_POST['oracion_ejemplo'] ?? ''),
            'nivel_dificultad'  => limpiar($_POST['nivel_dificultad'] ?? ''),
            'audio_url'         => $_POST['audio_url_actual'] ?? null,
            'imagen_url'        => $_POST['imagen_url_actual'] ?? null
        ];

        // Si suben un nuevo archivo, reemplazamos el existente
        if (isset($_FILES['audio']) && $_FILES['audio']['error'] === UPLOAD_ERR_OK) {
            $datos['audio_url'] = $this->subirArchivo($_FILES['audio'], 'audios');
        }
        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
            $datos['imagen_url'] = $this->subirArchivo($_FILES['imagen'], 'imagenes');
        }

        $this->vocabularioModel->actualizar($id, $datos);
        $this->redirect("admin/vocabulario?rap_id={$rapId}&exito=actualizado");
    }

    /**
     * Alterna el estado activo/inactivo del término.
     */
    public function toggle(): void {
        if (!validarTokenCSRF($_POST['csrf_token'] ?? '')) {
            $this->redirect('admin/raps');
        }

        $id    = limpiar($_POST['id'] ?? '');
        $rapId = limpiar($_POST['rap_id'] ?? '');

        if (!empty($id)) {
            $this->vocabularioModel->toggleActivo($id);
        }

        $this->redirect("admin/vocabulario?rap_id={$rapId}&exito=estado");
    }

    /**
     * Sube un archivo a la carpeta especificada en assets/uploads.
     */
    private function subirArchivo(array $archivo, string $carpeta): ?string {
        $directorioBase = dirname(__DIR__, 2) . '/assets/uploads/' . $carpeta . '/';
        if (!is_dir($directorioBase)) {
            mkdir($directorioBase, 0777, true);
        }

        // Validación de tamaño (Límite 2MB según HU19)
        $maxSize = 2 * 1024 * 1024; // 2MB
        if ($archivo['size'] > $maxSize) {
            return null; // En una mejora real se lanzaría una excepción o se pasaría el error
        }

        $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
        // Simple validación de seguridad
        $extensionesPermitidas = ['jpg', 'jpeg', 'png', 'svg', 'mp3', 'ogg', 'wav'];
        if (!in_array($extension, $extensionesPermitidas)) {
            return null;
        }

        $nombreArchivo = generarUUID() . '.' . $extension;
        $rutaDestino   = $directorioBase . $nombreArchivo;

        if (move_uploaded_file($archivo['tmp_name'], $rutaDestino)) {
            return '/assets/uploads/' . $carpeta . '/' . $nombreArchivo;
        }
        return null;
    }

    /**
     * Llama a la API de Inteligencia Artificial para sugerir la traducción, IPA y oración.
     */
    public function sugerir(): void {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['error' => 'Método no permitido']);
            return;
        }

        $termino = limpiar($_POST['termino'] ?? '');
        if (empty($termino)) {
            echo json_encode(['error' => 'El término es obligatorio']);
            return;
        }

        if (empty(GEMINI_API_KEY)) {
            echo json_encode(['error' => 'Falta configurar la API de IA (GEMINI_API_KEY) en el archivo .env']);
            return;
        }

        $prompt = "Actúa como un experto lingüista en inglés médico y general. "
                . "Para el término en inglés '{$termino}', proporciona exactamente la siguiente información en formato JSON estricto sin backticks ni markdown extra, con estas 3 claves: "
                . "1. 'traduccion': La traducción más natural al español. "
                . "2. 'ipa': La transcripción fonética IPA (ej. hɑːrt). "
                . "3. 'oracion': Una oración corta de ejemplo en inglés usando el término, preferiblemente en contexto médico si aplica, o contexto diario si no.";

        $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent?key=' . GEMINI_API_KEY;
        $data = [
            'contents' => [
                ['parts' => [['text' => $prompt]]]
            ]
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        // Desactivar verificación SSL solo para entornos locales (XAMPP/WAMP)
        if ($_SERVER['SERVER_NAME'] === 'localhost' || $_SERVER['SERVER_NAME'] === '127.0.0.1') {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($httpCode !== 200) {
            echo json_encode([
                'error' => 'Error al contactar con la IA', 
                'http_code' => $httpCode,
                'curl_error' => $curlError,
                'detalles' => $response
            ]);
            return;
        }

        $resultado = json_decode($response, true);
        $textoExtraido = $resultado['candidates'][0]['content']['parts'][0]['text'] ?? '';

        // Limpiar el JSON si la IA responde con bloques de código markdown
        $textoExtraido = preg_replace('/```json/i', '', $textoExtraido);
        $textoExtraido = preg_replace('/```/', '', $textoExtraido);
        $textoExtraido = trim($textoExtraido);

        echo $textoExtraido;
    }
}
