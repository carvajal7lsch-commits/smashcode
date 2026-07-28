<?php
namespace App\Models;

use App\Core\Model;
use PDO;

class EjercicioOpcion extends Model {
    public function obtenerPorEjercicio(string $ejercicioId): array {
        $pdo = self::obtenerConexion();
        $stmt = $pdo->prepare('SELECT * FROM ejercicio_opcion WHERE ejercicio_id = ? ORDER BY id ASC');
        $stmt->execute([$ejercicioId]);
        return $stmt->fetchAll();
    }

    public function crear(array $datos): string {
        $pdo = self::obtenerConexion();
        $id = generarUUID();
        $stmt = $pdo->prepare('INSERT INTO ejercicio_opcion (id, ejercicio_id, texto, es_correcta, retroalimentacion) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([
            $id,
            $datos['ejercicio_id'],
            $datos['texto'],
            $datos['es_correcta'] ?? 0,
            $datos['retroalimentacion'] ?? null
        ]);
        return $id;
    }

    public function eliminarPorEjercicio(string $ejercicioId): bool {
        $pdo = self::obtenerConexion();
        $stmt = $pdo->prepare('DELETE FROM ejercicio_opcion WHERE ejercicio_id = ?');
        return $stmt->execute([$ejercicioId]);
    }
}
