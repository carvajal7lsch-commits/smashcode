<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;
use App\Models\Progreso;
use App\Models\GamificacionConfig;
use App\Models\Programa;
use App\Models\AccesoCurso;
use PDO;

class AprendizController extends Controller {

    public function __construct() {
        parent::__construct();
        iniciarSesion();
        if (!estaAutenticado() || !in_array(obtenerRolSesion(), ['aprendiz', 'admin', 'instructor'])) {
            // Las peticiones en segundo plano (avance, ejercicios, quiz) no pueden seguir
            // una redirección: el navegador tomaría la página de login como respuesta y
            // el avance se perdería sin aviso. Un 401 le permite a la vista avisar.
            if ($this->esPeticionAjax()) {
                http_response_code(401);
                header('Content-Type: application/json');
                echo json_encode([
                    'exito'           => false,
                    'sesion_expirada' => true,
                    'error'           => 'Tu sesión expiró. Vuelve a iniciar sesión para seguir guardando tu avance.'
                ]);
                exit;
            }
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
        $esPreview = in_array(obtenerRolSesion(), ['admin', 'instructor']);
        try {
            (new AccesoCurso())->rap($rapId, $esPreview);
        } catch (\DomainException $e) {
            http_response_code(403);
            exit(htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
        }

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
            // Solo turnos activos: los quitados desde el panel se conservan desactivados (HU21)
            $stmtTur = $pdo->prepare('SELECT * FROM turno_dialogo WHERE dialogo_id = ? AND activo = 1 ORDER BY orden_turno ASC');
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
            $ej['ayuda'] = null;
        }
        unset($ej);

        // RF-34: recurso de ayuda de cada ejercicio. Se resuelve en una sola consulta
        // para no pegarle a la base una vez por ejercicio.
        $idsAyuda = array_values(array_filter(array_column($ejercicios, 'vocab_ayuda_id')));
        if ($idsAyuda) {
            $inAyuda = implode(',', array_fill(0, count($idsAyuda), '?'));
            $stmtAyuda = $pdo->prepare(
                "SELECT id, termino_en, termino_es, transcripcion_ipa, oracion_ejemplo, traduccion_ejemplo
                 FROM vocabulario WHERE id IN ($inAyuda) AND activo = 1"
            );
            $stmtAyuda->execute($idsAyuda);
            $ayudas = [];
            foreach ($stmtAyuda->fetchAll() as $fila) {
                $ayudas[$fila['id']] = $fila;
            }
            foreach ($ejercicios as &$ej) {
                $ej['ayuda'] = $ayudas[$ej['vocab_ayuda_id'] ?? ''] ?? null;
            }
            unset($ej);
        }

        // Obtener Quizzes y Preguntas unificados. El quiz de cierre reúne las preguntas
        // de todos los RAPs del módulo; el del RAP abierto va primero porque es donde
        // guardarIntentoQuiz() registra el intento.
        $quizzes = $this->obtenerQuizzesDelModulo($pdo, $allRapIds, $rapId);

        $quiz = !empty($quizzes) ? $quizzes[0] : null;
        $preguntas = [];

        if (!empty($quizzes)) {
            $preguntas = $this->obtenerPreguntasDeQuizzes($pdo, array_column($quizzes, 'id'));
            foreach ($preguntas as &$preg) {
                $preg['opciones'] = json_decode($preg['opciones'], true);
            }
            unset($preg);

            // HU22: si el quiz del RAP abierto está configurado para aleatorizar, las
            // preguntas cambian de orden en cada visita (se califican por id, no por posición)
            if (!empty($quiz['aleatorizar'])) {
                shuffle($preguntas);
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

        // Ejercicios del módulo ya respondidos, para retomar el Momento 3 donde quedó
        // el aprendiz. Solo mientras no lo haya terminado: quien ya pasó del 75% está
        // repitiendo el RAP (HU14) y empieza la práctica de cero.
        $ejerciciosRespondidos = [];
        if (!$esPreview && !empty($ejercicios) && (float) $progreso['porcentaje'] < 75 && !(int) $progreso['completado']) {
            $idsEjercicios = array_column($ejercicios, 'id');
            $inEjercicios  = implode(',', array_fill(0, count($idsEjercicios), '?'));
            $stmtResp = $pdo->prepare(
                "SELECT ejercicio_id, es_correcto
                 FROM intento_ejercicio
                 WHERE usuario_id = ? AND ejercicio_id IN ($inEjercicios)
                 ORDER BY creado_en, numero_intento"
            );
            $stmtResp->execute([$uid, ...$idsEjercicios]);

            // Queda el último intento de cada ejercicio
            foreach ($stmtResp->fetchAll() as $intento) {
                $ejerciciosRespondidos[$intento['ejercicio_id']] = (int) $intento['es_correcto'] === 1;
            }
        }

        // HU14: un RAP completado se puede repetir. El repaso reinicia los momentos solo en
        // esta visita; en la base el avance nunca baja y el mejor puntaje se conserva
        // (actualizarProgreso y guardarMejorPuntaje guardan siempre el máximo).
        $rapCompletado = !$esPreview && (int) $progreso['completado'] === 1;

        // HU22: intentos del quiz en la ronda actual. Con el quiz bloqueado también se
        // puede entrar en modo repaso: al terminar la práctica empieza una ronda nueva.
        $intentosQuiz  = (!$esPreview && !empty($quizzes)) ? $this->estadoIntentosQuiz($pdo, $uid, $quizzes, $rapId) : null;
        $quizBloqueado = !empty($intentosQuiz['bloqueado']);
        $modoRepaso    = ($rapCompletado || $quizBloqueado) && ($_GET['repetir'] ?? '') === '1';

        $practica = $esPreview ? null : (new AccesoCurso())->practica($rap, (float) $progreso['porcentaje'], $modoRepaso);
        if ($practica && (int) $practica['etapa'] < 75) {
            $stmtResp = $pdo->prepare("SELECT i.ejercicio_id, i.es_correcto FROM intento_ejercicio i JOIN ejercicio e ON e.id=i.ejercicio_id JOIN rap r ON r.id=e.rap_id WHERE i.usuario_id=? AND r.nivel_id=? AND i.creado_en>=? ORDER BY i.creado_en, i.numero_intento");
            $stmtResp->execute([$uid, $rap['nivel_id'], $practica['iniciada_en']]);
            $ejerciciosRespondidos = [];
            foreach ($stmtResp->fetchAll() as $intento) $ejerciciosRespondidos[$intento['ejercicio_id']] = (bool) $intento['es_correcto'];
        }

        $this->render('aprendiz/rap', compact('rap', 'vocabulario', 'marcados', 'dialogos', 'ejercicios', 'quiz', 'preguntas', 'progreso', 'esPreview', 'ejerciciosRespondidos', 'rapCompletado', 'modoRepaso', 'intentosQuiz', 'practica'));
    }

    public function toggleVocabMarcado(): void {
        if (!$this->validarEscritura()) return;
        header('Content-Type: application/json');
        $uid = $_SESSION['usuario_id'];
        $vocabId = limpiar($_POST['vocabulario_id'] ?? '');

        if (empty($vocabId)) {
            echo json_encode(['exito' => false, 'error' => 'ID de vocabulario no provisto']);
            return;
        }

        $pdo = obtenerConexion();
        $stmt = $pdo->prepare('SELECT rap_id FROM vocabulario WHERE id=? AND activo=1');
        $stmt->execute([$vocabId]);
        $rapId = $stmt->fetchColumn();
        // El glosario permite repasar palabras de cualquier módulo publicado.
        if (!$rapId) { $this->errorJson('Vocabulario inexistente o inactivo.'); return; }
        try { (new AccesoCurso())->rap((string)$rapId, true); }
        catch (\DomainException $e) { $this->errorJson($e->getMessage()); return; }
        if (obtenerRolSesion()==='aprendiz') {
            $stmt=$pdo->prepare('SELECT r.activo AND n.activo FROM rap r JOIN nivel n ON n.id=r.nivel_id WHERE r.id=?');
            $stmt->execute([$rapId]);
            if (!$stmt->fetchColumn()) { $this->errorJson('Este vocabulario no está publicado.'); return; }
        }
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
        if (!$this->validarEscritura()) return;
        header('Content-Type: application/json');
        $uid = $_SESSION['usuario_id'];
        $rapId = (string) ($_POST['rap_id'] ?? '');
        $pct = (int) ($_POST['porcentaje'] ?? 0);
        $rap = $this->validarAccesoRap($rapId);
        if (!$rap) return;
        if (obtenerRolSesion() !== 'aprendiz') {
            echo json_encode(['exito'=>true, 'preview'=>true, 'porcentaje'=>$pct]);
            return;
        }
        $pdo = obtenerConexion();
        try {
            $pdo->beginTransaction();
            $this->bloquearUsuario($pdo);
            $practica = (new AccesoCurso())->sesion((string) ($_POST['practica_id'] ?? ''), $rap['nivel_id']);
            $actual = (int) $practica['etapa'];
            if (!in_array($pct, [25,50,75], true)) throw new \DomainException('Momento inválido.');
            if ($pct > $actual) {
                if ($pct === 25) {
                    $stmt = $pdo->prepare('SELECT v.id FROM vocabulario v JOIN rap r ON r.id=v.rap_id WHERE r.nivel_id=? AND r.activo=1 AND v.activo=1 ORDER BY v.rap_id,v.id LIMIT 3');
                    $stmt->execute([$rap['nivel_id']]);
                    $ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
                    $pares = json_decode((string) ($_POST['pares'] ?? '[]'), true);
                    $recibidos = [];
                    foreach (is_array($pares) ? $pares : [] as $par) {
                        if (!is_array($par) || ($par['en'] ?? '') !== ($par['es'] ?? '')) throw new \DomainException('Completa el emparejamiento del Warm-Up.');
                        $recibidos[] = $par['en'];
                    }
                    sort($ids); sort($recibidos);
                    if (!$ids || $ids !== $recibidos) throw new \DomainException('Completa el emparejamiento del Warm-Up.');
                } elseif ($actual < $pct - 25) {
                    throw new \DomainException('Completa el momento anterior primero.');
                }
                if ($pct === 75) {
                    $stmt = $pdo->prepare('SELECT COUNT(*) FROM ejercicio e JOIN rap r ON r.id=e.rap_id WHERE r.nivel_id=? AND r.activo=1 AND e.activo=1 AND NOT EXISTS (SELECT 1 FROM intento_ejercicio i WHERE i.ejercicio_id=e.id AND i.usuario_id=? AND i.creado_en>=?)');
                    $stmt->execute([$rap['nivel_id'], $uid, $practica['iniciada_en']]);
                    if ((int) $stmt->fetchColumn() > 0) throw new \DomainException('Responde todos los ejercicios de esta práctica antes del quiz.');
                }
                $stmt = $pdo->prepare('UPDATE sesion_practica SET etapa=? WHERE id=?');
                $stmt->execute([$pct,$practica['id']]);
                $model = new Progreso();
                foreach ($this->obtenerRapsDelModulo($pdo,$rapId) as $id) {
                    $model->actualizarProgreso($uid,$id,$pct,0);
                    if ($pct === 75) $model->iniciarRondaQuiz($uid,$id);
                }
            }
            $pdo->commit();
            echo json_encode(['exito'=>true,'porcentaje'=>max($actual,$pct)]);
        } catch (\DomainException $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $this->errorJson($e->getMessage());
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            error_log($e->getMessage());
            $this->errorJson('No se pudo guardar el progreso.',500);
        }
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
        if (!$this->validarEscritura()) return;
        header('Content-Type: application/json');
        if (obtenerRolSesion() !== 'aprendiz') {
            echo json_encode(['exito'=>true,'preview'=>true,'xp_ganados'=>0]);
            return;
        }
        $pdo = obtenerConexion();
        $uid = $_SESSION['usuario_id'];
        $id = (string) ($_POST['ejercicio_id'] ?? '');
        $solicitud = (string) ($_POST['solicitud_id'] ?? '');
        if (!preg_match('/^[0-9a-f-]{36}$/i',$solicitud)) { $this->errorJson('Solicitud de ejercicio inválida.'); return; }
        try {
            $pdo->beginTransaction();
            $this->bloquearUsuario($pdo);
            $stmt = $pdo->prepare('SELECT resultado_json FROM intento_ejercicio WHERE usuario_id=? AND solicitud_id=? AND ejercicio_id=?');
            $stmt->execute([$uid,$solicitud,$id]);
            if ($guardado = $stmt->fetchColumn()) {
                $pdo->commit(); echo $guardado; return;
            }
            $stmt = $pdo->prepare('SELECT * FROM ejercicio WHERE id=? AND activo=1');
            $stmt->execute([$id]);
            $ej = $stmt->fetch();
            if (!$ej) throw new \DomainException('Ejercicio inexistente o inactivo.');
            $rap = (new AccesoCurso())->rap($ej['rap_id']);
            $practica = (new AccesoCurso())->sesion((string) ($_POST['practica_id'] ?? ''),$rap['nivel_id']);
            if ((int) $practica['etapa'] !== 50) throw new \DomainException('Completa los momentos anteriores o inicia un repaso para practicar.');
            $stmt = $pdo->prepare('SELECT COUNT(*) FROM intento_ejercicio WHERE usuario_id=? AND ejercicio_id=? AND creado_en>=?');
            $stmt->execute([$uid,$id,$practica['iniciada_en']]);
            $intentosRonda = (int) $stmt->fetchColumn();
            if ($intentosRonda >= max(1,(int) $ej['max_intentos'])) throw new \DomainException('Se agotaron los intentos de este ejercicio en la práctica actual.');
            $respuesta = mb_substr(trim((string) ($_POST['respuesta'] ?? '')),0,10000);
            $correcto = $this->resolverAciertoEjercicio($pdo,$id,(string) ($_POST['opcion_id'] ?? ''),$respuesta,0);
            $stmt = $pdo->prepare('SELECT COUNT(*), COALESCE(MAX(es_correcto),0) FROM intento_ejercicio WHERE usuario_id=? AND ejercicio_id=?');
            $stmt->execute([$uid,$id]);
            $historia = $stmt->fetch(PDO::FETCH_NUM);
            $xp = $correcto && !(int) $historia[1] ? (new GamificacionConfig())->obtenerValor('xp_ejercicio_correcto',10) : 0;
            $result = ['exito'=>true,'es_correcto'=>(bool)$correcto,'numero_intento'=>(int)$historia[0]+1,'xp_ganados'=>$xp,'intentos_restantes'=>max(0,(int)$ej['max_intentos']-$intentosRonda-1)];
            $stmt = $pdo->prepare('INSERT INTO intento_ejercicio (id,ejercicio_id,usuario_id,respuesta_elegida,es_correcto,numero_intento,tiempo_respuesta_ms,solicitud_id,resultado_json) VALUES (?,?,?,?,?,?,?,?,?)');
            $ms = max(0,min(3600000,(int) ($_POST['tiempo_respuesta_ms'] ?? 0)));
            $stmt->execute([generarUUID(),$id,$uid,mb_substr($respuesta,0,500),$correcto,$result['numero_intento'],$ms,$solicitud,json_encode($result)]);
            if ($xp > 0 && !(new User())->actualizarXP($uid,$xp)) throw new \RuntimeException('No se pudo guardar XP.');
            if ($ms > 0) (new Progreso())->sumarTiempo($uid,$ej['rap_id'],(int) round($ms/1000));
            $pdo->commit();
            echo json_encode($result);
        } catch (\DomainException $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $this->errorJson($e->getMessage());
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            error_log($e->getMessage());
            $this->errorJson('No se pudo guardar el ejercicio.',500);
        }
    }

    private function resolverAciertoEjercicio(PDO $pdo, string $id, string $opcionId, string $respuesta, int $ignorado): int {
        $stmt = $pdo->prepare('SELECT tipo FROM ejercicio WHERE id=?');
        $stmt->execute([$id]);
        $tipo = $stmt->fetchColumn();
        $stmt = $pdo->prepare('SELECT id,texto,es_correcta FROM ejercicio_opcion WHERE ejercicio_id=? ORDER BY id');
        $stmt->execute([$id]);
        $opciones = $stmt->fetchAll();
        $normalizar = static fn($texto) => mb_strtolower(trim(html_entity_decode((string)$texto,ENT_QUOTES | ENT_HTML5,'UTF-8')));
        if (in_array($tipo,['seleccion_multiple','role_play'],true)) {
            foreach ($opciones as $op) if ($op['id']===$opcionId) return (int) $op['es_correcta'] === 1 ? 1 : 0;
            return 0;
        }
        if ($tipo === 'arrastrar_soltar') {
            $pares = json_decode($respuesta,true);
            if (!is_array($pares) || count($pares)!==count($opciones) || !$opciones) return 0;
            $esperados=[]; $recibidos=[];
            foreach ($opciones as $op) {
                $partes=explode('=',$op['texto'],2);
                if(count($partes)!==2) return 0;
                $esperados[]=[$normalizar($partes[0]),$normalizar($partes[1])];
            }
            foreach($pares as $par) {
                if(!is_array($par) || !isset($par['en'],$par['es'])) return 0;
                $recibidos[]=[$normalizar($par['en']),$normalizar($par['es'])];
            }
            sort($esperados); sort($recibidos);
            return $esperados===$recibidos ? 1 : 0;
        }
        if ($tipo === 'ordenar_dialogo') {
            $esperada=[];
            foreach($opciones as $op) foreach(explode('|',$op['texto']) as $linea) $esperada[]=$normalizar($linea);
            return $respuesta!=='' && $esperada && $esperada===array_map($normalizar,explode('|',$respuesta)) ? 1 : 0;
        }
        if ($respuesta==='') return 0;
        if ($tipo === 'escucha_escribe') {
            $correctas = array_values(array_filter($opciones,static fn($opcion)=>(int)$opcion['es_correcta']===1));
            return count($correctas)===1 && $normalizar($correctas[0]['texto'])===$normalizar($respuesta) ? 1 : 0;
        }
        foreach($opciones as $op) if((int)$op['es_correcta']===1 && $normalizar($op['texto'])===$normalizar($respuesta)) return 1;
        return 0;
    }

    private function bloquearUsuario(PDO $pdo): void {
        $stmt=$pdo->prepare('SELECT id FROM usuarios WHERE id=? FOR UPDATE');
        $stmt->execute([$_SESSION['usuario_id']]); $stmt->fetch();
    }

    private function errorJson(string $error,int $codigo=403): void {
        http_response_code($codigo); header('Content-Type: application/json');
        echo json_encode(['exito'=>false,'error'=>$error]);
    }

    private function validarEscritura(): bool {
        if(!validarTokenCSRF((string) ($_POST['csrf_token'] ?? ''))) {
            $this->errorJson('La sesión del formulario cambió. Recarga la página.',419); return false;
        }
        return true;
    }

    private function validarAccesoRap(string $id): ?array {
        try { return (new AccesoCurso())->rap($id,obtenerRolSesion()!=='aprendiz'); }
        catch (\DomainException $e) { $this->errorJson($e->getMessage()); return null; }
    }

    public function iniciarQuiz(): void {
        if (!$this->validarEscritura()) return;
        header('Content-Type: application/json');
        $rapId=(string) ($_POST['rap_id'] ?? '');
        $rap=$this->validarAccesoRap($rapId);
        if(!$rap) return;
        if(obtenerRolSesion()!=='aprendiz') {
            echo json_encode(['exito'=>true,'preview'=>true]); return;
        }
        $pdo=obtenerConexion();
        try {
            $pdo->beginTransaction(); $this->bloquearUsuario($pdo);
            $sesion=(new AccesoCurso())->sesion((string) ($_POST['practica_id'] ?? ''),$rap['nivel_id']);
            if((int)$sesion['etapa']<75) throw new \DomainException('Termina la práctica antes de comenzar el quiz.');
            $raps=$this->obtenerRapsDelModulo($pdo,$rapId);
            $quizzes=$this->obtenerQuizzesDelModulo($pdo,$raps,$rapId);
            if(!$quizzes) throw new \DomainException('Quiz no disponible.');
            $estado=$this->estadoIntentosQuiz($pdo,$_SESSION['usuario_id'],$quizzes,$rapId);
            if($estado['bloqueado']) throw new \DomainException('Se agotaron los intentos. Repasa el RAP antes de continuar.');
            $preguntas=$this->obtenerPreguntasDeQuizzes($pdo,array_column($quizzes,'id'));
            if(!$preguntas) throw new \DomainException('El quiz no tiene preguntas.');
            $ids=json_decode((string)($_POST['preguntas_ids'] ?? '[]'),true);
            $esperados=array_column($preguntas,'id'); sort($esperados);
            if(!is_array($ids)) throw new \DomainException('Recarga el quiz.');
            sort($ids);
            if($ids!==$esperados) throw new \DomainException('El contenido del quiz cambió. Recarga el RAP antes de comenzar.');
            $stmt=$pdo->prepare('SELECT id, GREATEST(0,TIMESTAMPDIFF(SECOND,NOW(6),vence_en)) AS segundos_restantes FROM sesion_quiz WHERE usuario_id=? AND rap_id=? AND resultado_json IS NULL AND vence_en>NOW(6) ORDER BY iniciada_en DESC LIMIT 1');
            $stmt->execute([$_SESSION['usuario_id'],$rapId]);
            $activo=$stmt->fetch();
            if(!$activo) {
                $id=generarUUID();
                $segundos=max(1,(int)($quizzes[0]['limite_tiempo_seg'] ?: 300));
                $stmt=$pdo->prepare('INSERT INTO sesion_quiz (id,usuario_id,rap_id,quiz_id,vence_en,preguntas_json,puntaje_minimo) VALUES (?,?,?,?,DATE_ADD(NOW(6),INTERVAL ? SECOND),?,?)');
                $stmt->execute([$id,$_SESSION['usuario_id'],$rapId,$quizzes[0]['id'],$segundos,json_encode($preguntas),$quizzes[0]['puntaje_minimo']]);
                $activo=['id'=>$id,'segundos_restantes'=>$segundos];
            }
            $pdo->commit();
            echo json_encode(['exito'=>true,'sesion_quiz_id'=>$activo['id'],'segundos_restantes'=>(int)$activo['segundos_restantes']]);
        } catch(\DomainException $e) {
            if($pdo->inTransaction())$pdo->rollBack(); $this->errorJson($e->getMessage());
        } catch(\Throwable $e) {
            if($pdo->inTransaction())$pdo->rollBack(); error_log($e->getMessage()); $this->errorJson('No se pudo iniciar el quiz.',500);
        }
    }

    public function guardarIntentoQuiz(): void {
        if (!$this->validarEscritura()) return;
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
                'subio_nivel' => 0,
                'modulo_desbloqueado' => null,
                'resumen' => null
            ]);
            return;
        }

        if (!$this->validarAccesoRap($rapId)) return;
        $pdo = obtenerConexion();
        try {
        $pdo->beginTransaction();
        $this->bloquearUsuario($pdo);
        $stmt = $pdo->prepare('SELECT *, TIMESTAMPDIFF(SECOND,iniciada_en,NOW(6)) AS duracion_real, NOW(6)>DATE_ADD(vence_en,INTERVAL 3 SECOND) AS vencido FROM sesion_quiz WHERE id=? AND usuario_id=? AND rap_id=? FOR UPDATE');
        $stmt->execute([(string)($_POST['sesion_quiz_id'] ?? ''),$uid,$rapId]);
        $sesionQuiz = $stmt->fetch();
        if (!$sesionQuiz) throw new \DomainException('Comienza el quiz antes de enviar respuestas.');
        if ($sesionQuiz['resultado_json']) {
            $pdo->commit(); echo $sesionQuiz['resultado_json']; return;
        }
        if ($sesionQuiz['vencido']) throw new \DomainException('El tiempo de este quiz terminó. Vuelve a comenzar un intento.');
        $duracionSeg = max(0,(int)$sesionQuiz['duracion_real']);
        if (!is_array($respuestas)) throw new \DomainException('Respuestas inválidas.');

        // 1. Obtener el quiz del módulo: se califican las mismas preguntas que la
        //    página le mostró al aprendiz, las de todos los RAPs del módulo
        $rapsModulo = $this->obtenerRapsDelModulo($pdo, $rapId);
        $quizzes    = $this->obtenerQuizzesDelModulo($pdo, $rapsModulo, $rapId);

        if (empty($quizzes)) {
            throw new \DomainException('Quiz no encontrado para este RAP');
        }

        // El intento queda registrado en el primero, que es el quiz del RAP abierto
        $quiz = $quizzes[0];
        $quiz['id'] = $sesionQuiz['quiz_id'];
        $quiz['puntaje_minimo'] = $sesionQuiz['puntaje_minimo'];

        // HU22: con los intentos de la ronda agotados no se califica ni se registra
        $intentos = $this->estadoIntentosQuiz($pdo, $uid, $quizzes, $rapId);
        if ($intentos['bloqueado']) {
            $pdo->rollBack();
            echo json_encode([
                'exito'     => false,
                'bloqueado' => true,
                'intentos'  => $intentos,
                'error'     => 'Usaste tus ' . $intentos['limite'] . ' intentos de esta ronda. Repasa el RAP y termina la práctica para recibir intentos nuevos.',
            ]);
            return;
        }

        // 2. Obtener Preguntas del Quiz
        $preguntas = json_decode($sesionQuiz['preguntas_json'], true);
        $totalPreguntas = count($preguntas);

        if ($totalPreguntas === 0) {
            throw new \DomainException('El quiz no tiene preguntas configuradas');
        }

        $correctas = 0;
        $detalles = [];

        // 3. Evaluar respuestas
        foreach ($preguntas as $preg) {
            $elegida = $respuestas[$preg['id']] ?? '';
            if (!is_string($elegida) || mb_strlen($elegida)>500) throw new \DomainException('Respuesta inválida.');
            $elegida = trim($elegida);
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
        $stmtInsInt = $pdo->prepare('INSERT INTO intento_quiz (id, quiz_id, usuario_id, puntaje, aprobado, numero_intento, duracion_seg, puntaje_minimo_original) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
        $stmtInsInt->execute([$intentoId, $quiz['id'], $uid, $puntaje, $aprobado, $numeroIntento, $duracionSeg, $quiz['puntaje_minimo']]);

        // Guardar respuestas individuales
        $stmtInsResp = $pdo->prepare('INSERT INTO respuesta_quiz (id, intento_quiz_id, pregunta_id, respuesta_elegida, es_correcto, texto_pregunta_original) VALUES (?, ?, ?, ?, ?, ?)');
        foreach ($detalles as $pregId => $det) {
            $stmtInsResp->execute([generarUUID(), $intentoId, $pregId, $det['elegida'], $det['es_correcto'], $det['texto']]);
        }

        // 5. Actualizar Progreso del módulo (mejor puntaje y completado)
        $progresoModel = new Progreso();
        foreach ($rapsModulo as $idRap) {
            $progresoModel->guardarMejorPuntaje($uid, $idRap, $puntaje);
        }
        // HU05: el tiempo del quiz cuenta como tiempo invertido en el RAP
        $progresoModel->sumarTiempo($uid, $rapId, $duracionSeg);

        $xpGanados = 0;
        $insigniaGanada = null;
        $subioNivelPerfil = 0;
        $moduloDesbloqueado = null;

        if ($aprobado) {
            // HU05: se mide el avance del modulo antes y despues para avisar solo
            // en el momento exacto en que se cruza el umbral que abre el siguiente
            $promedioModuloAntes = $this->obtenerPromedioModuloDelRap($pdo, $uid, $rapId);

            // Completado al 100% al aprobar, en todos los RAPs del módulo
            foreach ($rapsModulo as $idRap) {
                $progresoModel->actualizarProgreso($uid, $idRap, 100.00, 1);
            }

            $promedioModuloDespues = $this->obtenerPromedioModuloDelRap($pdo, $uid, $rapId);
            $umbralSiguiente = $this->obtenerUmbralSiguienteModulo($pdo,$rapId);
            if ($umbralSiguiente !== null && !AccesoCurso::alcanzaUmbral($promedioModuloAntes,$umbralSiguiente)
                && AccesoCurso::alcanzaUmbral($promedioModuloDespues,$umbralSiguiente)) {
                $moduloDesbloqueado = $this->obtenerNombreSiguienteModulo($pdo, $rapId);
            }

            // Recompensas de Gamificación dinámicas
            $userModel = new User();
            $gamConfig = new GamificacionConfig();
            
            $xpQuizAprobado = $gamConfig->obtenerValor('xp_quiz_aprobado', 50);
            $xpQuizPerfecto = $gamConfig->obtenerValor('xp_quiz_perfecto', 100);

            $xpGanados = $xpQuizAprobado;
            if ($puntaje === 100.00) {
                $xpGanados += $xpQuizPerfecto;
            }
            if (!$userModel->actualizarXP($uid, $xpGanados)) throw new \RuntimeException('No se pudo guardar XP.');
            // HU15: actualizarXP() deja anotado si el aprendiz subio de rango
            $subioNivelPerfil = $userModel->obtenerUltimoAscensoNivel();

            // HU07: insignias. Cada una se identifica por su criterio y solo se anuncian
            // las que el aprendiz gana en este intento.
            $nuevas = [];

            // "Quiz Perfecto" con 100%
            if ($puntaje === 100.00) {
                $nuevas[] = $this->otorgarInsigniaNueva($pdo, $userModel, $uid, 'puntaje_quiz = 100');
            }

            // Una insignia por módulo al aprobar con 90% o más (M1 Primer Nivel,
            // M2 Handover Specialist, M3 Clinical Communicator, M4 Care Evaluator)
            if ($puntaje >= 90.00) {
                $stmtNiv = $pdo->prepare('SELECT n.orden FROM rap r JOIN nivel n ON n.id = r.nivel_id WHERE r.id = ? LIMIT 1');
                $stmtNiv->execute([$rapId]);
                $nivelOrden = (int) $stmtNiv->fetchColumn();
                $nuevas[] = $this->otorgarInsigniaNueva($pdo, $userModel, $uid, 'quiz_modulo_' . $nivelOrden . ' >= 90');
            }

            // "Vocabulario Pro": 30 palabras en RAPs completados
            if ($this->palabrasAprendidas($pdo, $uid) >= 30) {
                $nuevas[] = $this->otorgarInsigniaNueva($pdo, $userModel, $uid, 'vocabulario_aprendido >= 30');
            }

            // "Estudiante Élite": todos los RAPs activos completados
            if ($this->completoTodosLosModulos($pdo, $uid)) {
                $nuevas[] = $this->otorgarInsigniaNueva($pdo, $userModel, $uid, 'modulos_completados = todos');
            }

            $nuevas = array_values(array_filter($nuevas));
            $insigniaGanada = $nuevas ? implode(' y ', $nuevas) : null;
        }

        $resultado = [
            'exito' => true,
            'puntaje' => $puntaje,
            'aprobado' => (bool)$aprobado,
            'xp_ganados' => $xpGanados,
            'insignia_ganada' => $insigniaGanada,
            'detalles' => $detalles,
            'intentos' => $this->estadoIntentosQuiz($pdo, $uid, $quizzes, $rapId),
            'subio_nivel' => $subioNivelPerfil,
            'modulo_desbloqueado' => $moduloDesbloqueado,
            'resumen' => $this->construirResumenRap($pdo, $uid, $rapId, $detalles, $correctas, $totalPreguntas, (bool)$aprobado)
        ];
        $stmt=$pdo->prepare('UPDATE sesion_quiz SET resultado_json=? WHERE id=?');
        $stmt->execute([json_encode($resultado),$sesionQuiz['id']]);
        $pdo->commit();
        echo json_encode($resultado);
        } catch (\DomainException $e) {
            if($pdo->inTransaction()) $pdo->rollBack();
            $this->errorJson($e->getMessage());
        } catch (\Throwable $e) {
            if($pdo->inTransaction()) $pdo->rollBack();
            error_log($e->getMessage());
            $this->errorJson('No se pudo guardar el quiz.',500);
        }
    }



    /**
     * RAPs activos del módulo al que pertenece $rapId, en orden.
     *
     * El mapa muestra un solo camino por módulo y la página del RAP une el
     * contenido de todos sus RAPs, así que el avance y el quiz se registran en
     * todos a la vez. Si solo avanzara el RAP abierto, un módulo de dos RAPs se
     * quedaría en 50% y el siguiente no se desbloquearía nunca (umbral 80%).
     */
    private function obtenerRapsDelModulo(\PDO $pdo, string $rapId): array {
        $stmt = $pdo->prepare(
            'SELECT r.id
             FROM rap r
             WHERE r.activo = 1
               AND r.nivel_id = (SELECT nivel_id FROM rap WHERE id = ? LIMIT 1)
             ORDER BY r.orden, r.id'
        );
        $stmt->execute([$rapId]);
        $ids = $stmt->fetchAll(PDO::FETCH_COLUMN);

        return $ids ?: [$rapId];
    }

    /**
     * Quizzes activos de los RAPs indicados: el del RAP abierto primero y el resto
     * en el orden de sus RAPs. La página y la calificación usan esta misma consulta,
     * para que el aprendiz responda exactamente lo que después se califica.
     */
    /**
     * HU07: otorga la insignia de ese criterio si el aprendiz aún no la tiene.
     * Devuelve su nombre solo cuando es nueva, para no anunciar de nuevo una ya ganada.
     */
    private function otorgarInsigniaNueva(\PDO $pdo, User $userModel, string $uid, string $criterio): ?string {
        $stmt = $pdo->prepare('SELECT id, nombre FROM insignia WHERE criterio = ? LIMIT 1');
        $stmt->execute([$criterio]);
        $insignia = $stmt->fetch();
        if (!$insignia) {
            return null;
        }

        $stmt = $pdo->prepare('SELECT 1 FROM insignia_usuario WHERE usuario_id = ? AND insignia_id = ? LIMIT 1');
        $stmt->execute([$uid, $insignia['id']]);
        if ($stmt->fetchColumn()) {
            return null;
        }

        return $userModel->otorgarInsignia($uid, $insignia['id']) ? $insignia['nombre'] : null;
    }

    /**
     * HU07: palabras aprendidas = vocabulario activo de los RAPs que el aprendiz completó.
     */
    private function palabrasAprendidas(\PDO $pdo, string $uid): int {
        $stmt = $pdo->prepare(
            'SELECT COUNT(v.id)
             FROM progreso p
             JOIN rap r ON r.id = p.rap_id AND r.activo = 1
             JOIN nivel n ON n.id = r.nivel_id AND n.activo = 1
             JOIN vocabulario v ON v.rap_id = r.id AND v.activo = 1
             WHERE p.usuario_id = ? AND p.completado = 1'
        );
        $stmt->execute([$uid]);
        return (int) $stmt->fetchColumn();
    }

    /**
     * HU07: completó todos los RAPs activos de los módulos activos.
     */
    private function completoTodosLosModulos(\PDO $pdo, string $uid): bool {
        $total = (int) $pdo->query(
            'SELECT COUNT(*) FROM rap r JOIN nivel n ON n.id = r.nivel_id AND n.activo = 1 WHERE r.activo = 1'
        )->fetchColumn();

        $stmt = $pdo->prepare(
            'SELECT COUNT(DISTINCT p.rap_id)
             FROM progreso p
             JOIN rap r ON r.id = p.rap_id AND r.activo = 1
             JOIN nivel n ON n.id = r.nivel_id AND n.activo = 1
             WHERE p.usuario_id = ? AND p.completado = 1'
        );
        $stmt->execute([$uid]);

        return $total > 0 && (int) $stmt->fetchColumn() >= $total;
    }

    /**
     * HU22: intentos del quiz en la ronda actual.
     *
     * Una ronda empieza al terminar la práctica (Momento 3, ver guardarProgreso). Se
     * cuentan los intentos reprobados del módulo desde entonces; al llegar a
     * max_intentos el quiz queda bloqueado hasta repasar el RAP. Quien ya aprobó no
     * tiene límite (HU14). Sin ronda registrada cuentan todos los intentos reprobados.
     */
    private function estadoIntentosQuiz(\PDO $pdo, string $uid, array $quizzes, string $rapId): array {
        $limite = (int) ($quizzes[0]['max_intentos'] ?? 0);

        $stmt = $pdo->prepare('SELECT completado, ronda_quiz_desde FROM progreso WHERE usuario_id = ? AND rap_id = ? LIMIT 1');
        $stmt->execute([$uid, $rapId]);
        $prog = $stmt->fetch() ?: ['completado' => 0, 'ronda_quiz_desde' => null];

        if ((int) $prog['completado'] === 1 || $limite <= 0) {
            return ['sin_limite' => true, 'limite' => $limite, 'usados' => 0, 'restantes' => null, 'bloqueado' => false];
        }

        $ids    = array_column($quizzes, 'id');
        $in     = implode(',', array_fill(0, count($ids), '?'));
        $sql    = "SELECT COUNT(*) FROM intento_quiz WHERE usuario_id = ? AND quiz_id IN ($in) AND aprobado = 0";
        $params = [$uid, ...$ids];
        if (!empty($prog['ronda_quiz_desde'])) {
            $sql     .= ' AND creado_en >= ?';
            $params[] = $prog['ronda_quiz_desde'];
        }
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $usados = (int) $stmt->fetchColumn();

        return [
            'sin_limite' => false,
            'limite'     => $limite,
            'usados'     => min($usados, $limite),
            'restantes'  => max(0, $limite - $usados),
            'bloqueado'  => $usados >= $limite,
        ];
    }

    private function obtenerQuizzesDelModulo(\PDO $pdo, array $rapIds, string $rapAbierto): array {
        $in = implode(',', array_fill(0, count($rapIds), '?'));
        $stmt = $pdo->prepare(
            "SELECT q.*
             FROM quiz q
             JOIN rap r ON r.id = q.rap_id
             WHERE q.rap_id IN ($in) AND q.activo = 1
             ORDER BY (q.rap_id = ?) DESC, r.orden"
        );
        $stmt->execute([...$rapIds, $rapAbierto]);

        return $stmt->fetchAll();
    }

    /**
     * Preguntas de los quizzes indicados, agrupadas por quiz en ese mismo orden.
     */
    private function obtenerPreguntasDeQuizzes(\PDO $pdo, array $quizIds): array {
        $in = implode(',', array_fill(0, count($quizIds), '?'));
        $stmt = $pdo->prepare(
            "SELECT id, quiz_id, texto, opciones, respuesta_correcta, retroalimentacion
             FROM pregunta
             WHERE quiz_id IN ($in) AND activo = 1
             ORDER BY FIELD(quiz_id, $in), id"
        );
        $stmt->execute([...$quizIds, ...$quizIds]);

        return $stmt->fetchAll();
    }

    /**
     * Avance promedio del aprendiz en el módulo al que pertenece un RAP (HU05).
     *
     * Recorre todos los RAPs activos del módulo, contando en 0 los que aún no
     * tienen fila de progreso: es el mismo cálculo que usa el mapa de
     * aprendizaje para decidir qué módulo está desbloqueado.
     */
    private function obtenerPromedioModuloDelRap(\PDO $pdo, string $uid, string $rapId): float {
        $stmt = $pdo->prepare(
            'SELECT COALESCE(AVG(COALESCE(p.porcentaje, 0)), 0)
             FROM rap r
             LEFT JOIN progreso p ON p.rap_id = r.id AND p.usuario_id = ?
             WHERE r.activo = 1
               AND r.nivel_id = (SELECT nivel_id FROM rap WHERE id = ? LIMIT 1)'
        );
        $stmt->execute([$uid, $rapId]);
        return (float) $stmt->fetchColumn();
    }

    /**
     * Nombre del módulo siguiente al que contiene el RAP indicado, o null si
     * este ya era el último módulo activo del curso (HU05).
     */
    private function obtenerUmbralSiguienteModulo(\PDO $pdo, string $rapId): ?float {
        $stmt=$pdo->prepare('SELECT n.umbral_desbloqueo FROM nivel n WHERE n.activo=1 AND n.orden>(SELECT n2.orden FROM nivel n2 JOIN rap r ON r.nivel_id=n2.id WHERE r.id=?) ORDER BY n.orden LIMIT 1');
        $stmt->execute([$rapId]);$umbral=$stmt->fetchColumn();
        return $umbral===false ? null : (float)$umbral;
    }

    private function obtenerNombreSiguienteModulo(\PDO $pdo, string $rapId): ?string {
        $stmt = $pdo->prepare(
            'SELECT n.nombre
             FROM nivel n
             WHERE n.activo = 1
               AND n.orden > (SELECT n2.orden
                              FROM rap r
                              JOIN nivel n2 ON n2.id = r.nivel_id
                              WHERE r.id = ? LIMIT 1)
             ORDER BY n.orden
             LIMIT 1'
        );
        $stmt->execute([$rapId]);
        $nombre = $stmt->fetchColumn();
        return $nombre !== false ? (string) $nombre : null;
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

        // La página del RAP muestra el contenido de todo el módulo; las recomendaciones también
        $rapsModulo = $this->obtenerRapsDelModulo($pdo, $rapId);
        $inRaps     = implode(',', array_fill(0, count($rapsModulo), '?'));

        // Ejercicios del Momento 3 de este módulo que el aprendiz aún no ha acertado nunca
        $stmtEj = $pdo->prepare(
            "SELECT e.enunciado
             FROM ejercicio e
             JOIN intento_ejercicio ie ON ie.ejercicio_id = e.id AND ie.usuario_id = ?
             WHERE e.rap_id IN ($inRaps) AND e.activo = 1
             GROUP BY e.id
             HAVING MAX(ie.es_correcto) = 0
             ORDER BY COUNT(ie.id) DESC
             LIMIT 3"
        );
        $stmtEj->execute([$uid, ...$rapsModulo]);
        $ejerciciosFallados = $stmtEj->fetchAll(PDO::FETCH_COLUMN);

        // Vocabulario que el propio aprendiz marcó como difícil en este módulo (HU02)
        $stmtVoc = $pdo->prepare(
            "SELECT v.termino_en
             FROM vocabulario_marcado vm
             JOIN vocabulario v ON v.id = vm.vocabulario_id
             WHERE vm.usuario_id = ? AND v.rap_id IN ($inRaps)
             LIMIT 5"
        );
        $stmtVoc->execute([$uid, ...$rapsModulo]);
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
            // RF-16: las etiquetas existen para encontrar la palabra, así que entran
            // en la búsqueda junto al término en inglés y su traducción.
            $sql .= " AND (v.termino_en LIKE ? OR v.termino_es LIKE ? OR v.etiquetas LIKE ?)";
            $params[] = "%$busqueda%";
            $params[] = "%$busqueda%";
            $params[] = "%$busqueda%";
        }
        
        $sql .= " ORDER BY v.termino_en ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $vocabulario = $stmt->fetchAll();

        // Obtener filtros
        // Solo catálogos activos: desactivar un área o categoría la quita de los filtros (HU18)
        //
        // RF-20: cada opción trae cuántos términos consultables tiene. Sin ese número,
        // filtrar por un área todavía sin vocabulario devuelve una lista vacía sin
        // explicación y parece un error de la plataforma.
        $areas = $pdo->query(
            "SELECT a.id, a.nombre, COUNT(v.id) AS total
             FROM area_clinica a
             LEFT JOIN vocabulario v ON v.area_clinica_id = a.id AND v.activo = 1
             LEFT JOIN rap r ON r.id = v.rap_id AND r.activo = 1
             WHERE a.activo = 1
             GROUP BY a.id, a.nombre ORDER BY a.nombre"
        )->fetchAll();
        $categorias = $pdo->query(
            "SELECT c.id, c.nombre, COUNT(v.id) AS total
             FROM categoria_vocabulario c
             LEFT JOIN vocabulario v ON v.categoria_id = c.id AND v.activo = 1
             LEFT JOIN rap r ON r.id = v.rap_id AND r.activo = 1
             WHERE c.activo = 1
             GROUP BY c.id, c.nombre ORDER BY c.nombre"
        )->fetchAll();
        $niveles = $pdo->query(
            "SELECT n.id, n.nombre, COUNT(v.id) AS total
             FROM nivel n
             LEFT JOIN rap r ON r.nivel_id = n.id AND r.activo = 1
             LEFT JOIN vocabulario v ON v.rap_id = r.id AND v.activo = 1
             GROUP BY n.id, n.nombre, n.orden ORDER BY n.orden"
        )->fetchAll();

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

            // HU05: avance modulo a modulo / RAP a RAP y tiempo total de estudio
            $progresoModel  = new Progreso();
            $avanceModulos  = $progresoModel->obtenerAvancePorModulo($uid);
            $tiempoTotalSeg = $progresoModel->obtenerTiempoTotal($uid);

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
                'programaId' => $programaId,
                'avanceModulos' => $avanceModulos,
                'tiempoTotalSeg' => $tiempoTotalSeg,
                'inicioSemana' => $userModel->obtenerInicioSemana()
            ]);
        } else {
            $this->redirect('login');
        }
    }

    /**
     * Marcador semanal en JSON para que el perfil lo refresque sin recargar (HU15).
     *
     * Reutiliza la misma consulta que pinta la vista al cargar la página, para
     * que el ranking en vivo y el renderizado inicial no puedan divergir.
     */
    public function leaderboard(): void {
        header('Content-Type: application/json');
        $uid = $_SESSION['usuario_id'] ?? null;

        if (!$uid) {
            http_response_code(401);
            echo json_encode(['exito' => false, 'error' => 'Sesión expirada']);
            return;
        }

        $userModel  = new User();
        $usuario    = $userModel->obtenerPorId($uid);
        $programaId = $usuario['programa_id'] ?? null;

        $ranking    = [];
        $miPosicion = 0;

        foreach ($userModel->obtenerLeaderboardSemanal($programaId) as $i => $fila) {
            $soyYo = ($fila['id'] === $uid);
            if ($soyYo) {
                $miPosicion = $i + 1;
            }
            $ranking[] = [
                'posicion'     => $i + 1,
                'nombre'       => $fila['nombre_completo'],
                'nivel_perfil' => (int) $fila['nivel_perfil'],
                'xp_semana'    => (int) ($fila['xp_semana'] ?? 0),
                'xp_puntos'    => (int) $fila['xp_puntos'],
                'soy_yo'       => $soyYo
            ];
        }

        echo json_encode([
            'exito'       => true,
            'mi_posicion' => $miPosicion,
            'ranking'     => $ranking
        ]);
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
            actualizarHuellaSesion();
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
