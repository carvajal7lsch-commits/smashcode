<?php
namespace App\Models;

use App\Core\Model;
use PDO;
use DomainException;

class AccesoCurso extends Model {
    public function rap(string $rapId, bool $preview = false): array {
        $pdo = self::obtenerConexion();
        $stmt = $pdo->prepare('SELECT r.*, n.orden AS nivel_orden, n.activo AS nivel_activo FROM rap r JOIN nivel n ON n.id = r.nivel_id WHERE r.id = ?');
        $stmt->execute([$rapId]);
        $rap = $stmt->fetch();
        if (!$rap || (!$preview && (!$rap['activo'] || !$rap['nivel_activo']))) {
            throw new DomainException('Este RAP no está disponible.');
        }
        if (!$preview) {
            $stmt = $pdo->prepare('SELECT n.id FROM nivel n WHERE n.activo = 1 AND n.orden < ? ORDER BY n.orden DESC LIMIT 1');
            $stmt->execute([$rap['nivel_orden']]);
            $anterior = $stmt->fetchColumn();
            if ($anterior) {
                $stmt = $pdo->prepare('SELECT COALESCE(AVG(COALESCE(p.porcentaje, 0)), 0) FROM rap r LEFT JOIN progreso p ON p.rap_id=r.id AND p.usuario_id=? WHERE r.nivel_id=? AND r.activo=1');
                $stmt->execute([$_SESSION['usuario_id'], $anterior]);
                if ((float) $stmt->fetchColumn() < 80) throw new DomainException('Completa el módulo anterior antes de acceder a este RAP.');
            }
        }
        return $rap;
    }

    public function practica(array $rap, float $avance, bool $repaso): array {
        $pdo = self::obtenerConexion();
        $stmt = $pdo->prepare('SELECT * FROM sesion_practica WHERE usuario_id=? AND nivel_id=? ORDER BY iniciada_en DESC, id DESC LIMIT 1');
        $stmt->execute([$_SESSION['usuario_id'], $rap['nivel_id']]);
        $sesion = $stmt->fetch();
        if (!$sesion || ($repaso && (int) $sesion['etapa'] >= 75)) {
            $id = generarUUID();
            // Una primera sesión adopta el avance y los intentos históricos. Un repaso empieza de cero.
            $etapa = $repaso ? 0 : (int) min(75, $avance);
            $inicio = !$sesion && !$repaso ? '1970-01-01 00:00:00.000000' : null;
            $stmt = $pdo->prepare('INSERT INTO sesion_practica (id,usuario_id,nivel_id,etapa,iniciada_en) VALUES (?,?,?,?,COALESCE(?,CURRENT_TIMESTAMP(6)))');
            $stmt->execute([$id, $_SESSION['usuario_id'], $rap['nivel_id'], $etapa, $inicio]);
            return $this->sesion($id, $rap['nivel_id']);
        }
        return $sesion;
    }

    public function sesion(string $id, string $nivelId): array {
        $stmt = self::obtenerConexion()->prepare('SELECT * FROM sesion_practica WHERE id=? AND usuario_id=? AND nivel_id=?');
        $stmt->execute([$id, $_SESSION['usuario_id'], $nivelId]);
        $sesion = $stmt->fetch();
        if (!$sesion) throw new DomainException('Recarga el RAP para iniciar una práctica válida.');
        return $sesion;
    }
}
