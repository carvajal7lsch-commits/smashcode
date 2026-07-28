<?php
namespace App\Models;

use App\Core\Model;

/**
 * Nivel.php
 * Modelo de negocio para la gestión de Niveles de Aprendizaje.
 * Centraliza las consultas relacionadas con las etapas del mapa gamificado de SmashCode.
 */
class Nivel extends Model {

    /**
     * Obtiene todos los niveles activos junto con sus RAPs (Resultados de Aprendizaje) asociados.
     * Usado por la vista del aprendiz (HU01).
     */
    public function obtenerNivelesConRaps(): array {
        $pdo = self::obtenerConexion();
        $stmt = $pdo->query(
            'SELECT n.id, n.nombre, n.descripcion, n.orden, n.activo, n.umbral_desbloqueo,
                    r.id AS rap_id, r.titulo AS rap_titulo, r.orden AS rap_orden
             FROM nivel n
             JOIN rap r ON r.nivel_id = n.id AND r.activo = 1
             WHERE n.activo = 1
             ORDER BY n.orden, r.orden'
        );
        return $stmt->fetchAll();
    }

    /**
     * Obtiene todos los niveles (activos e inactivos) con conteo de RAPs.
     * Usado por el panel de administración / instructor (HU10).
     */
    public function obtenerTodos(): array {
        $pdo = self::obtenerConexion();
        $stmt = $pdo->query(
            'SELECT n.id, n.nombre, n.descripcion, n.imagen_url, n.orden,
                    n.activo, n.umbral_desbloqueo,
                    MAX(r.id) AS rap_id,
                    COUNT(r.id) AS total_raps,
                    SUM(CASE WHEN r.activo = 1 THEN 1 ELSE 0 END) AS raps_activos
             FROM nivel n
             LEFT JOIN rap r ON r.nivel_id = n.id
             GROUP BY n.id, n.nombre, n.descripcion, n.imagen_url, n.orden,
                      n.activo, n.umbral_desbloqueo
             ORDER BY n.orden'
        );
        return $stmt->fetchAll();
    }

    /**
     * Obtiene un nivel por su ID.
     * @param string $id UUID del nivel
     */
    public function obtenerPorId(string $id): ?array {
        $pdo  = self::obtenerConexion();
        $stmt = $pdo->prepare(
            'SELECT n.id, n.nombre, n.descripcion, n.imagen_url, n.orden,
                    n.activo, n.umbral_desbloqueo,
                    COUNT(r.id) AS total_raps
             FROM nivel n
             LEFT JOIN rap r ON r.nivel_id = n.id
             WHERE n.id = :id
             GROUP BY n.id, n.nombre, n.descripcion, n.imagen_url, n.orden,
                      n.activo, n.umbral_desbloqueo'
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Actualiza los atributos editables de un nivel (HU10).
     * No permite crear ni eliminar niveles.
     *
     * @param string $id UUID del nivel
     * @param string $nombre Nombre del nivel
     * @param string $descripcion Descripción del nivel
     * @param string|null $imagenUrl URL de imagen de portada
     * @param float $umbral Porcentaje mínimo de desbloqueo (0.00 para Nivel 1)
     * @param int $activo 1 = activo, 0 = inactivo
     */
    public function actualizar(
        string $id,
        string $nombre,
        string $descripcion,
        ?string $imagenUrl,
        float $umbral,
        int $activo
    ): bool {
        $pdo  = self::obtenerConexion();
        $stmt = $pdo->prepare(
            'UPDATE nivel
             SET nombre = :nombre,
                 descripcion = :descripcion,
                 imagen_url = :imagen_url,
                 umbral_desbloqueo = :umbral,
                 activo = :activo
             WHERE id = :id'
        );
        return $stmt->execute([
            ':nombre'      => $nombre,
            ':descripcion' => $descripcion,
            ':imagen_url'  => $imagenUrl,
            ':umbral'      => $umbral,
            ':activo'      => $activo,
            ':id'          => $id,
        ]);
    }

    /**
     * Alterna el estado activo/inactivo de un nivel (toggle rápido).
     * @param string $id UUID del nivel
     */
    public function toggleActivo(string $id): bool {
        $pdo  = self::obtenerConexion();
        $stmt = $pdo->prepare(
            'UPDATE nivel SET activo = IF(activo = 1, 0, 1) WHERE id = :id'
        );
        return $stmt->execute([':id' => $id]);
    }
}
