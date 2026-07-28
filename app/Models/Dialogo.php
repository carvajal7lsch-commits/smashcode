<?php
namespace App\Models;

use App\Core\Model;
use PDO;

class Dialogo extends Model {
    public function obtenerPorRap(string $rapId): array {
        $pdo = self::obtenerConexion();
        $stmt = $pdo->prepare('SELECT * FROM dialogo WHERE rap_id = ? ORDER BY id ASC');
        $stmt->execute([$rapId]);
        return $stmt->fetchAll();
    }

    public function obtenerPorId(string $id): ?array {
        $pdo = self::obtenerConexion();
        $stmt = $pdo->prepare('SELECT * FROM dialogo WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function crear(array $datos): string {
        $pdo = self::obtenerConexion();
        $id = $datos['id'] ?? generarUUID();
        $stmt = $pdo->prepare('INSERT INTO dialogo (id, rap_id, titulo, contexto, participantes) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([
            $id,
            $datos['rap_id'],
            $datos['titulo'],
            $datos['contexto'] ?? null,
            $datos['participantes'] ?? null
        ]);
        return $id;
    }

    public function actualizar(string $id, array $datos): bool {
        $pdo = self::obtenerConexion();
        $stmt = $pdo->prepare('UPDATE dialogo SET titulo = ?, contexto = ?, participantes = ? WHERE id = ?');
        return $stmt->execute([
            $datos['titulo'],
            $datos['contexto'] ?? null,
            $datos['participantes'] ?? null,
            $id
        ]);
    }

    public function eliminar(string $id): bool {
        $pdo = self::obtenerConexion();
        $stmt = $pdo->prepare('DELETE FROM dialogo WHERE id = ?');
        return $stmt->execute([$id]);
    }
}
