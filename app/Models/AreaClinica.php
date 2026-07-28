<?php
namespace App\Models;

use App\Core\Model;
use PDO;

/**
 * AreaClinica.php
 * Modelo para la gestión de áreas clínicas (Catálogo para vocabulario - HU18).
 */
class AreaClinica extends Model {

    public function obtenerTodas(): array {
        $pdo  = self::obtenerConexion();
        $sql = 'SELECT id, nombre FROM area_clinica ORDER BY nombre';
        $stmt = $pdo->query($sql);
        return $stmt->fetchAll();
    }

    public function obtenerPorId(string $id): ?array {
        $pdo  = self::obtenerConexion();
        $stmt = $pdo->prepare('SELECT id, nombre FROM area_clinica WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function crear(string $id, string $nombre): bool {
        $pdo  = self::obtenerConexion();
        $stmt = $pdo->prepare('INSERT INTO area_clinica (id, nombre, activo) VALUES (?, ?, 1)');
        return $stmt->execute([$id, $nombre]);
    }

    public function actualizar(string $id, string $nombre): bool {
        $pdo  = self::obtenerConexion();
        $stmt = $pdo->prepare('UPDATE area_clinica SET nombre = ? WHERE id = ?');
        return $stmt->execute([$nombre, $id]);
    }

    public function toggleActivo(string $id): bool {
        $pdo  = self::obtenerConexion();
        $stmt = $pdo->prepare('UPDATE area_clinica SET activo = IF(activo = 1, 0, 1) WHERE id = ?');
        return $stmt->execute([$id]);
    }

    public function existeNombre(string $nombre, string $excluirId = ''): bool {
        $pdo  = self::obtenerConexion();
        $sql  = 'SELECT id FROM area_clinica WHERE nombre = ?';
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
