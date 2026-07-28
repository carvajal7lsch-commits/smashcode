<?php
namespace App\Models;

use App\Core\Model;
use PDO;

class Rap extends Model {
    public function obtenerPorId(string $id): ?array {
        $pdo = self::obtenerConexion();
        $stmt = $pdo->prepare('SELECT * FROM rap WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function obtenerTodos(): array {
        $pdo = self::obtenerConexion();
        $stmt = $pdo->query('SELECT * FROM rap ORDER BY orden ASC');
        return $stmt->fetchAll();
    }
}
