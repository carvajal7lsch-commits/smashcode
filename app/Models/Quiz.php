<?php
namespace App\Models;

use App\Core\Model;
use PDO;

class Quiz extends Model {
    public function obtenerPorRap(string $rapId): ?array {
        $pdo = self::obtenerConexion();
        $stmt = $pdo->prepare('SELECT * FROM quiz WHERE rap_id = ? LIMIT 1');
        $stmt->execute([$rapId]);
        return $stmt->fetch() ?: null;
    }

    public function crear(array $datos): string {
        $pdo = self::obtenerConexion();
        $id = $datos['id'] ?? generarUUID();
        $stmt = $pdo->prepare('INSERT INTO quiz (id, rap_id, puntaje_minimo, limite_tiempo_seg, max_intentos) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([
            $id,
            $datos['rap_id'],
            $datos['puntaje_minimo'] ?? 60.00,
            $datos['limite_tiempo_seg'] ?? 300,
            $datos['max_intentos'] ?? 3
        ]);
        return $id;
    }

    public function actualizar(string $id, array $datos): bool {
        $pdo = self::obtenerConexion();
        $stmt = $pdo->prepare('UPDATE quiz SET puntaje_minimo = ?, limite_tiempo_seg = ?, max_intentos = ? WHERE id = ?');
        return $stmt->execute([
            $datos['puntaje_minimo'] ?? 60.00,
            $datos['limite_tiempo_seg'] ?? 300,
            $datos['max_intentos'] ?? 3,
            $id
        ]);
    }
}
