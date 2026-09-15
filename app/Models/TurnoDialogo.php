<?php
namespace App\Models;

use App\Core\Model;
use PDO;

class TurnoDialogo extends Model {
    /**
     * Turnos activos del diálogo, en orden.
     */
    public function obtenerPorDialogo(string $dialogoId): array {
        $pdo = self::obtenerConexion();
        $stmt = $pdo->prepare('SELECT * FROM turno_dialogo WHERE dialogo_id = ? AND activo = 1 ORDER BY orden_turno ASC');
        $stmt->execute([$dialogoId]);
        return $stmt->fetchAll();
    }

    public function crear(array $datos): string {
        $pdo = self::obtenerConexion();
        $id = generarUUID();
        $stmt = $pdo->prepare('INSERT INTO turno_dialogo (id, dialogo_id, orden_turno, hablante, texto_en, texto_es, audio_url) VALUES (?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([
            $id,
            $datos['dialogo_id'],
            $datos['orden_turno'],
            $datos['hablante'],
            $datos['texto_en'],
            $datos['texto_es'],
            $datos['audio_url'] ?? null
        ]);
        return $id;
    }

    /**
     * Actualiza un turno existente conservando su id (HU21).
     */
    public function actualizar(string $id, array $datos): bool {
        $pdo = self::obtenerConexion();
        $stmt = $pdo->prepare('UPDATE turno_dialogo SET orden_turno = ?, hablante = ?, texto_en = ?, texto_es = ?, audio_url = ?, activo = 1 WHERE id = ?');
        return $stmt->execute([
            $datos['orden_turno'],
            $datos['hablante'],
            $datos['texto_en'],
            $datos['texto_es'],
            $datos['audio_url'] ?? null,
            $id
        ]);
    }

    /**
     * Borrado lógico de un turno quitado desde el panel (HU21).
     */
    public function desactivar(string $id): bool {
        $pdo = self::obtenerConexion();
        $stmt = $pdo->prepare('UPDATE turno_dialogo SET activo = 0 WHERE id = ?');
        return $stmt->execute([$id]);
    }

    public function eliminarPorDialogo(string $dialogoId): bool {
        $pdo = self::obtenerConexion();
        $stmt = $pdo->prepare('UPDATE turno_dialogo SET activo = 0 WHERE dialogo_id = ?');
        return $stmt->execute([$dialogoId]);
    }
}
