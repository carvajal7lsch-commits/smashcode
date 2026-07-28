<?php
namespace App\Models;

use App\Core\Model;
use PDO;

class TurnoDialogo extends Model {
    public function obtenerPorDialogo(string $dialogoId): array {
        $pdo = self::obtenerConexion();
        $stmt = $pdo->prepare('SELECT * FROM turno_dialogo WHERE dialogo_id = ? ORDER BY orden_turno ASC');
        $stmt->execute([$dialogoId]);
        return $stmt->fetchAll();
    }

    public function crear(array $datos): string {
        $pdo = self::obtenerConexion();
        $id = generarUUID();
        $stmt = $pdo->prepare('INSERT INTO turno_dialogo (id, dialogo_id, orden_turno, hablante, texto_en, texto_es) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->execute([
            $id,
            $datos['dialogo_id'],
            $datos['orden_turno'],
            $datos['hablante'],
            $datos['texto_en'],
            $datos['texto_es']
        ]);
        return $id;
    }

    public function eliminarPorDialogo(string $dialogoId): bool {
        $pdo = self::obtenerConexion();
        $stmt = $pdo->prepare('DELETE FROM turno_dialogo WHERE dialogo_id = ?');
        return $stmt->execute([$dialogoId]);
    }
}
