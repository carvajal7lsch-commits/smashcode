<?php
namespace App\Models;

use App\Core\Model;
use DomainException;
use PDO;

class ContenidoCurso extends Model {
    public static function validarEjercicio(string $tipo, array $opciones, string $enunciado): void {
        if (!in_array($tipo,['seleccion_multiple','role_play','completar_frase','escucha_escribe','arrastrar_soltar','ordenar_dialogo'],true)) {
            throw new DomainException('Tipo de ejercicio no válido.');
        }
        if (trim(strip_tags($enunciado)) === '' || !$opciones) throw new DomainException('El ejercicio necesita enunciado y opciones.');
        $textos=[]; $correctas=[];
        foreach ($opciones as $opcion) {
            if (!is_array($opcion) || !is_string($opcion['texto'] ?? null) || trim($opcion['texto']) === '') {
                throw new DomainException('Cada opción necesita texto.');
            }
            $texto=trim($opcion['texto']); $textos[]=$texto;
            if ((int)($opcion['es_correcta'] ?? 0)===1) $correctas[]=$texto;
        }
        if (count(array_unique(array_map('mb_strtolower',$textos))) !== count($textos)) throw new DomainException('Las opciones no pueden repetirse.');
        if (in_array($tipo,['seleccion_multiple','role_play'],true) && count($textos)<2) throw new DomainException('Este ejercicio necesita al menos dos opciones.');
        if (in_array($tipo,['seleccion_multiple','completar_frase','escucha_escribe'],true) && count($correctas)!==1) {
            throw new DomainException('Marca exactamente una opción correcta.');
        }
        if ($tipo==='role_play' && !$correctas) throw new DomainException('El roleplay necesita al menos una respuesta o criterio válido.');
        if ($tipo==='completar_frase' && !str_contains($enunciado,'___')
            && !preg_match('/\b'.preg_quote($correctas[0],'/').'\b/iu',$enunciado)) {
            throw new DomainException('La frase necesita ___ o la palabra que se completará.');
        }
        if ($tipo==='arrastrar_soltar') {
            $ingles=[];$espanol=[];
            foreach($textos as $texto) {
                $partes=array_map('trim',explode('=',$texto,2));
                if(count($partes)!==2 || $partes[0]==='' || $partes[1]==='') throw new DomainException('Cada par debe tener el formato Inglés = Español.');
                $ingles[]=mb_strtolower($partes[0]);$espanol[]=mb_strtolower($partes[1]);
            }
            if(count(array_unique($ingles))!==count($ingles) || count(array_unique($espanol))!==count($espanol)) throw new DomainException('Los pares deben tener textos únicos en cada columna.');
        }
        if ($tipo==='ordenar_dialogo') {
            $lineas=array_map('trim',explode('|',$textos[0]));
            if(count($textos)!==1 || count($lineas)<2 || in_array('',$lineas,true)) throw new DomainException('Guarda la secuencia completa en una opción, con al menos dos líneas separadas por |.');
        }
    }

    public function bloquearModulo(string $rapId): void {
        $stmt=self::obtenerConexion()->prepare('SELECT n.id FROM nivel n JOIN rap r ON r.nivel_id=n.id WHERE r.id=? FOR UPDATE');
        $stmt->execute([$rapId]);
        if(!$stmt->fetchColumn()) throw new DomainException('RAP no encontrado.');
    }

    public function validarPublicado(string $rapId): void {
        $stmt=self::obtenerConexion()->prepare('SELECT activo FROM rap WHERE id=?');$stmt->execute([$rapId]);
        if((int)$stmt->fetchColumn()===1) $this->validarPublicacion($rapId);
    }

    public function validarPublicacion(string $rapId): void {
        $pdo=self::obtenerConexion();
        $stmt=$pdo->prepare('SELECT id FROM rap WHERE nivel_id=(SELECT nivel_id FROM rap WHERE id=?) AND (activo=1 OR id=?)');
        $stmt->execute([$rapId,$rapId]);$raps=$stmt->fetchAll(PDO::FETCH_COLUMN);
        if(!$raps) throw new DomainException('RAP no encontrado.');
        $in=implode(',',array_fill(0,count($raps),'?'));
        $stmt=$pdo->prepare("SELECT COUNT(*) FROM vocabulario WHERE rap_id IN ($in) AND activo=1 AND TRIM(termino_en)<>'' AND TRIM(termino_es)<>''");
        $stmt->execute($raps);
        if((int)$stmt->fetchColumn()<3) throw new DomainException('El módulo necesita al menos tres palabras válidas para Warm-Up.');
        $stmt=$pdo->prepare("SELECT e.* FROM ejercicio e WHERE e.rap_id IN ($in) AND e.activo=1");$stmt->execute($raps);$ejercicios=$stmt->fetchAll();
        if(!$ejercicios) throw new DomainException('El módulo necesita ejercicios antes de publicarse.');
        $opciones=$pdo->prepare('SELECT * FROM ejercicio_opcion WHERE ejercicio_id=?');
        foreach($ejercicios as $ejercicio) {
            $opciones->execute([$ejercicio['id']]);self::validarEjercicio($ejercicio['tipo'],$opciones->fetchAll(),$ejercicio['enunciado']);
        }
        $stmt=$pdo->prepare("SELECT d.id FROM dialogo d WHERE d.rap_id IN ($in) AND d.activo=1");$stmt->execute($raps);$dialogos=$stmt->fetchAll(PDO::FETCH_COLUMN);
        if(!$dialogos) throw new DomainException('El módulo necesita un diálogo antes de publicarse.');
        $turnos=$pdo->prepare("SELECT COUNT(*) FROM turno_dialogo WHERE dialogo_id=? AND activo=1 AND TRIM(hablante)<>'' AND TRIM(texto_en)<>''");
        foreach($dialogos as $id) { $turnos->execute([$id]);if(!(int)$turnos->fetchColumn()) throw new DomainException('Cada diálogo activo necesita turnos válidos.'); }
        // El RAP que se publica debe aportar su quiz; la página combina los del módulo.
        $stmt=$pdo->prepare('SELECT p.opciones,p.respuesta_correcta,p.texto FROM pregunta p JOIN quiz q ON q.id=p.quiz_id WHERE q.rap_id=? AND q.activo=1 AND p.activo=1');
        $stmt->execute([$rapId]);$preguntas=$stmt->fetchAll();
        if(!$preguntas) throw new DomainException('Completa el quiz con preguntas activas antes de publicar el RAP.');
        foreach($preguntas as $pregunta) {
            $ops=json_decode($pregunta['opciones'],true);
            if(trim($pregunta['texto'])==='' || !is_array($ops) || count($ops)<2 || !in_array($pregunta['respuesta_correcta'],$ops,true)) {
                throw new DomainException('El quiz contiene preguntas incompletas.');
            }
        }
    }
}
