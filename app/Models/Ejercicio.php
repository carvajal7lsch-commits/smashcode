<?php
namespace App\Models;

use App\Core\Model;
use PDO;

class Ejercicio extends Model {
    /**
     * Ejercicios activos del RAP. Los eliminados desde el panel se conservan
     * desactivados para no perder el historial de intentos (HU20).
     */
    public function obtenerPorRap(string $rapId): array {
        $pdo = self::obtenerConexion();
        $stmt = $pdo->prepare('SELECT * FROM ejercicio WHERE rap_id = ? AND activo = 1 ORDER BY id ASC');
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
        $stmt = $pdo->prepare('INSERT INTO ejercicio (id, rap_id, tipo, enunciado, instrucciones, max_intentos, puntos) VALUES (?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([
            $id,
            $datos['rap_id'],
            $datos['tipo'],
            $datos['enunciado'],
            $datos['instrucciones'] ?? null,
            $datos['max_intentos'] ?? 3,
            $datos['puntos'] ?? 10
        ]);
        return $id;
    }

    public function actualizar(string $id, array $datos): bool {
        $pdo = self::obtenerConexion();
        $stmt = $pdo->prepare('UPDATE ejercicio SET tipo = ?, enunciado = ?, instrucciones = ?, max_intentos = ?, puntos = ? WHERE id = ?');
        return $stmt->execute([
            $datos['tipo'],
            $datos['enunciado'],
            $datos['instrucciones'] ?? null,
            $datos['max_intentos'] ?? 3,
            $datos['puntos'] ?? 10,
            $id
        ]);
    }

    /**
     * Borrado lógico (HU20): intento_ejercicio apunta al ejercicio con RESTRICT, así
     * que un DELETE fallaba en cuanto un aprendiz lo había respondido.
     */
    public function eliminar(string $id): bool {
        $pdo = self::obtenerConexion();
        $stmt = $pdo->prepare('UPDATE ejercicio SET activo = 0 WHERE id = ?');
        return $stmt->execute([$id]);
    }
}
