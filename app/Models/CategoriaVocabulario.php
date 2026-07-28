<?php
namespace App\Models;

use App\Core\Model;
use PDO;

/**
 * CategoriaVocabulario.php
 * Modelo para la gestión de categorías gramaticales (Catálogo para vocabulario - HU18).
 */
class CategoriaVocabulario extends Model {

    public function obtenerTodas(): array {
        $pdo  = self::obtenerConexion();
        $sql = 'SELECT id, nombre FROM categoria_vocabulario ORDER BY nombre';
        $stmt = $pdo->query($sql);
        return $stmt->fetchAll();
    }

    public function obtenerPorId(string $id): ?array {
        $pdo  = self::obtenerConexion();
        $stmt = $pdo->prepare('SELECT id, nombre FROM categoria_vocabulario WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function crear(string $id, string $nombre): bool {
        $pdo  = self::obtenerConexion();
        $stmt = $pdo->prepare('INSERT INTO categoria_vocabulario (id, nombre, activo) VALUES (?, ?, 1)');
        return $stmt->execute([$id, $nombre]);
    }

    public function actualizar(string $id, string $nombre): bool {
        $pdo  = self::obtenerConexion();
        $stmt = $pdo->prepare('UPDATE categoria_vocabulario SET nombre = ? WHERE id = ?');
        return $stmt->execute([$nombre, $id]);
    }

    public function toggleActivo(string $id): bool {
        $pdo  = self::obtenerConexion();
        $stmt = $pdo->prepare('UPDATE categoria_vocabulario SET activo = IF(activo = 1, 0, 1) WHERE id = ?');
        return $stmt->execute([$id]);
    }

    public function existeNombre(string $nombre, string $excluirId = ''): bool {
        $pdo  = self::obtenerConexion();
        $sql  = 'SELECT id FROM categoria_vocabulario WHERE nombre = ?';
        $params = [$nombre];
        if ($excluirId !== '') {
            $sql    .= ' AND id != ?';
            $params[] = $excluirId;
        }
        $stmt = $pdo->prepare($sql . ' LIMIT 1');
        $stmt->execute($params);
        return (bool) $stmt->fetch();
    }
}
