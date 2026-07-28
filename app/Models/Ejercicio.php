<?php
namespace App\Models;

use App\Core\Model;
use PDO;

class Ejercicio extends Model {
    public function obtenerPorRap(string $rapId): array {
        $pdo = self::obtenerConexion();
        $stmt = $pdo->prepare('SELECT * FROM ejercicio WHERE rap_id = ? ORDER BY id ASC');
        $stmt->execute([$rapId]);
        return $stmt->fetchAll();
    }

    public function obtenerPorId(string $id): ?array {
        $pdo = self::obtenerConexion();
        $stmt = $pdo->prepare('SELECT * FROM ejercicio WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function crear(array $datos): string {
        $pdo = self::obtenerConexion();
        // El id se genera automáticamente por uuid() en BD si no se envía, pero es mejor generarlo en PHP para usarlo
        $id = isset($datos['id']) ? $datos['id'] : generarUUID();
        $stmt = $pdo->prepare('INSERT INTO ejercicio (id, rap_id, tipo, enunciado) VALUES (?, ?, ?, ?)');
        $stmt->execute([
            $id,
            $datos['rap_id'],
            $datos['tipo'],
            $datos['enunciado']
        ]);
        return $id;
    }

    public function actualizar(string $id, array $datos): bool {
        $pdo = self::obtenerConexion();
        $stmt = $pdo->prepare('UPDATE ejercicio SET tipo = ?, enunciado = ? WHERE id = ?');
        return $stmt->execute([
            $datos['tipo'],
            $datos['enunciado'],
            $id
        ]);
    }

    public function eliminar(string $id): bool {
        $pdo = self::obtenerConexion();
        $stmt = $pdo->prepare('DELETE FROM ejercicio WHERE id = ?');
        return $stmt->execute([$id]);
    }
}
