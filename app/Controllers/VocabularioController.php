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
        // Todas, con su estado: la vista oculta las desactivadas para vocabulario nuevo,
        // pero muestra la que ya tenga asignada la palabra que se edita (HU18)
        $areas         = $this->areaModel->obtenerTodas();
        $categorias    = $this->categoriaModel->obtenerTodas();
        
        // Obtener el registro del RAP para mostrar en la vista
        $rapModel = new \App\Models\Rap();
        $rap = $rapModel->obtenerPorId($rapId);
        
        $exito = limpiar($_GET['exito'] ?? '');
        $error = limpiar($_GET['error'] ?? '');

        $this->render('admin/vocabulario/index', compact('vocabulario', 'rapId', 'rap', 'totalUsuarios', 'areas', 'categorias', 'exito', 'error'));
    }

    /**
     * Procesa la creación de un nuevo término.
     */
    public function guardar(): void {
        $this->guardarTermino(false);
    }

    public function actualizar(): void {
        $this->guardarTermino(true);
    }

    private function guardarTermino(bool $editar): void {
        if (!validarTokenCSRF($_POST['csrf_token'] ?? '')) $this->redirect('admin/raps');
        $rapId=\App\Models\ValidacionUsuario::entrada($_POST['rap_id'] ?? '');
        $id=$editar ? \App\Models\ValidacionUsuario::entrada($_POST['id'] ?? '') : generarUUID();
        $pdo=\App\Core\Model::obtenerConexion();$nuevos=[];
        try {
            $pdo->beginTransaction();
            $contenido=new \App\Models\ContenidoCurso();$contenido->bloquearModulo($rapId);
            $actual=$editar ? $this->vocabularioModel->obtenerPorId($id) : null;
            if ($editar && (!$actual || $actual['rap_id']!==$rapId)) throw new \DomainException('La palabra no pertenece al RAP.');
            $datos=['id'=>$id,'rap_id'=>$rapId,'audio_url'=>$actual['audio_url'] ?? null,'imagen_url'=>$actual['imagen_url'] ?? null];
            foreach (['termino_en','termino_es','categoria_id','area_clinica_id','transcripcion_ipa','oracion_ejemplo','traduccion_ejemplo','nivel_dificultad'] as $campo) {
                $datos[$campo]=\App\Models\ValidacionUsuario::entrada($_POST[$campo] ?? '');
            }
            $faltantes=$this->camposFaltantes($datos);
            if ($faltantes) throw new \DomainException('Completa los campos obligatorios: '.implode(', ',$faltantes).'.');
            foreach (['categoria_id'=>'categoria_vocabulario','area_clinica_id'=>'area_clinica'] as $campo=>$tabla) {
                $stmt=$pdo->prepare("SELECT activo FROM $tabla WHERE id=?");$stmt->execute([$datos[$campo]]);$activo=$stmt->fetchColumn();
                if ($activo===false || (!(int)$activo && $datos[$campo]!==($actual[$campo] ?? null))) throw new \DomainException('Selecciona una categoría y un área clínica válidas y activas.');
            }
            // Validar ambos archivos antes de mover ninguno; las URLs previas vienen de la base.
            $archivos=['audio_url'=>$this->validarArchivo('audio','audios'),'imagen_url'=>$this->validarArchivo('imagen','imagenes')];
            foreach ($archivos as $campo=>$archivo) {
                if ($archivo) $datos[$campo]=$this->guardarArchivo($archivo,$nuevos);
            }
            $guardado=$editar ? $this->vocabularioModel->actualizar($id,$datos) : $this->vocabularioModel->crear($datos);
            if (!$guardado) throw new \RuntimeException('No se pudo persistir el vocabulario.');
            $contenido->validarPublicado($rapId);$pdo->commit();
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            foreach ($nuevos as $archivo) { if (is_file($archivo)) unlink($archivo); }
            if ($e instanceof \DomainException) $mensaje=$e->getMessage();
            else { error_log('[Vocabulario] '.$e->getMessage());$mensaje='No se pudo guardar la palabra. Revisa los datos y los archivos.'; }
            $this->redirect('admin/vocabulario?rap_id='.urlencode($rapId).'&error='.urlencode($mensaje));
            return;
        }
        $this->redirect('admin/vocabulario?rap_id='.urlencode($rapId).'&exito='.($editar?'actualizado':'creado'));
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
            $pdo = \App\Core\Model::obtenerConexion();
            try {
                $pdo->beginTransaction();
                $palabra = $this->vocabularioModel->obtenerPorId($id);
                if (!$palabra || $palabra['rap_id'] !== $rapId) throw new \DomainException('La palabra no pertenece al RAP.');
                $contenido = new \App\Models\ContenidoCurso();
                $contenido->bloquearModulo($rapId);
                $this->vocabularioModel->toggleActivo($id);
                $contenido->validarPublicado($rapId);
                $pdo->commit();
            } catch (\DomainException $e) {
                if ($pdo->inTransaction()) $pdo->rollBack();
                $this->redirect('admin/vocabulario?rap_id=' . urlencode($rapId) . '&error=' . urlencode($e->getMessage()));
                return;
            } catch (\Throwable $e) {
                if ($pdo->inTransaction()) $pdo->rollBack();
                throw $e;
            }
        }

        $this->redirect("admin/vocabulario?rap_id={$rapId}&exito=estado");
    }

    /**
     * HU19: campos obligatorios de una entrada de vocabulario que llegaron vacíos.
     * Audio e imagen son opcionales: sin audio suena la voz sintetizada.
     */
    private function camposFaltantes(array $datos): array {
        $obligatorios = [
            'termino_en'         => 'término en inglés',
            'termino_es'         => 'traducción al español',
            'categoria_id'       => 'categoría',
            'area_clinica_id'    => 'área clínica',
            'nivel_dificultad'   => 'nivel de dificultad',
            'transcripcion_ipa'  => 'transcripción IPA',
            'oracion_ejemplo'    => 'oración de ejemplo',
            'traduccion_ejemplo' => 'traducción del ejemplo',
        ];

        $faltantes = [];
        foreach ($obligatorios as $campo => $nombre) {
            if (trim((string) ($datos[$campo] ?? '')) === '') {
                $faltantes[] = $nombre;
            }
        }
        return $faltantes;
    }

    private function validarArchivo(string $campo,string $carpeta): ?array {
        $archivo=$_FILES[$campo] ?? null;
        if ($archivo===null) return null;
        if (!is_array($archivo) || !isset($archivo['error']) || is_array($archivo['error'])) throw new \DomainException('Archivo no válido.');
        if ((int)$archivo['error']===UPLOAD_ERR_NO_FILE) return null;
        if ((int)$archivo['error']!==UPLOAD_ERR_OK) throw new \DomainException('No se pudo recibir el archivo. El límite es 2 MB.');
        if (!is_string($archivo['name'] ?? null) || !is_string($archivo['tmp_name'] ?? null) || !is_uploaded_file($archivo['tmp_name'])) throw new \DomainException('Archivo no válido.');
        $tamano=filesize($archivo['tmp_name']);
        if ($tamano===false || $tamano<=0 || $tamano>2*1024*1024) throw new \DomainException('El archivo debe tener contenido y no superar 2 MB.');
        $extension=strtolower(pathinfo($archivo['name'],PATHINFO_EXTENSION));
        $tipos=$carpeta==='audios'
            ? ['mp3'=>['audio/mpeg','audio/mp3'],'ogg'=>['audio/ogg','application/ogg'],'wav'=>['audio/wav','audio/x-wav','audio/vnd.wave']]
            : ['jpg'=>['image/jpeg'],'jpeg'=>['image/jpeg'],'png'=>['image/png'],'svg'=>['image/svg+xml','text/xml','application/xml','text/plain']];
        if (!isset($tipos[$extension])) throw new \DomainException($carpeta==='audios'?'El audio debe ser MP3, OGG o WAV.':'La imagen debe ser JPG, PNG o SVG estático.');
        $mime=(new \finfo(FILEINFO_MIME_TYPE))->file($archivo['tmp_name']);
        if (!in_array($mime,$tipos[$extension],true)) throw new \DomainException('El contenido del archivo no coincide con su formato.');
        if ($carpeta==='imagenes') {
            if ($extension==='svg') $this->validarSvg($archivo['tmp_name']);
            elseif (getimagesize($archivo['tmp_name'])===false) throw new \DomainException('La imagen no es válida.');
        }
        return ['tmp_name'=>$archivo['tmp_name'],'extension'=>$extension,'carpeta'=>$carpeta];
    }

    private function validarSvg(string $ruta): void {
        $xml=file_get_contents($ruta);
        if (preg_match('/<!DOCTYPE|<!ENTITY/i',$xml)) throw new \DomainException('El SVG no puede incluir entidades externas.');
        $previo=libxml_use_internal_errors(true);
        try {
            $dom=new \DOMDocument();
            if (!$dom->loadXML($xml,LIBXML_NONET) || $dom->documentElement->localName!=='svg' || $dom->documentElement->namespaceURI!=='http://www.w3.org/2000/svg') throw new \DomainException('El SVG no es válido.');
            if ((new \DOMXPath($dom))->query('//processing-instruction()')->length) throw new \DomainException('El SVG no puede incluir hojas de estilo externas.');
            foreach ($dom->getElementsByTagName('*') as $elemento) {
                if (in_array(strtolower($elemento->localName),['script','foreignobject','iframe','object','embed','audio','video','animate','set','animatemotion','animatetransform'],true)) throw new \DomainException('Usa un SVG estático sin scripts ni contenido incrustado.');
                foreach ($elemento->attributes as $atributo) {
                    $nombre=strtolower($atributo->localName);$valor=trim($atributo->value);
                    if (str_starts_with($nombre,'on') || ($nombre==='href' && $valor!=='' && !str_starts_with($valor,'#')) || $this->referenciaExternaSvg($valor)) throw new \DomainException('El SVG no puede incluir eventos ni enlaces externos.');
                }
                if (strtolower($elemento->localName)==='style' && $this->referenciaExternaSvg($elemento->textContent)) throw new \DomainException('El SVG no puede cargar recursos externos.');
            }
        } finally { libxml_clear_errors();libxml_use_internal_errors($previo); }
    }

    private function referenciaExternaSvg(string $texto): bool {
        if (str_contains($texto,'\\') || preg_match('/javascript\s*:|@import/i',$texto)) return true;
        preg_match_all('/url\s*\(([^)]*)\)/i',$texto,$referencias);
        foreach ($referencias[1] as $referencia) {
            if (!str_starts_with(trim($referencia," \t\r\n\"'"),'#')) return true;
        }
        return false;
    }

    private function guardarArchivo(array $archivo,array &$nuevos): string {
        $directorio=dirname(__DIR__,2).'/assets/uploads/'.$archivo['carpeta'].'/';
        if (!is_dir($directorio) && !mkdir($directorio,0775,true)) throw new \RuntimeException('No se pudo crear el directorio.');
        $nombre=generarUUID().'.'.$archivo['extension'];$ruta=$directorio.$nombre;
        if (!move_uploaded_file($archivo['tmp_name'],$ruta)) throw new \RuntimeException('No se pudo guardar el archivo.');
        $nuevos[]=$ruta;
        return '/assets/uploads/'.$archivo['carpeta'].'/'.$nombre;
    }
}
