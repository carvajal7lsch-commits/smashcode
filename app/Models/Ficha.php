<?php
namespace App\Models;

use App\Core\Model;
use PDO;

/**
 * Ficha.php
 * Modelo de negocio para la gestión del catálogo de Fichas SENA (W11 / W12).
 * Soporta validación contra programa, catalogación y asignación a instructores.
 */
class Ficha extends Model {

    /**
     * Obtiene todas las fichas del catálogo.
     */
    public function obtenerTodas(bool $soloActivas = true): array {
        $pdo = self::obtenerConexion();
        $sql = 'SELECT f.id, f.codigo, f.programa_id, f.activo, f.creado_en, p.nombre AS programa_nombre
                FROM fichas f
                INNER JOIN programa_formacion p ON f.programa_id = p.id
                WHERE p.eliminado = 0';
        if ($soloActivas) {
            $sql .= ' AND f.activo = 1 AND p.activo = 1';
        }
        $sql .= ' ORDER BY f.codigo ASC';
        return $pdo->query($sql)->fetchAll();
    }

    /**
     * Obtiene las fichas asociadas a un programa de formación específico.
     */
    public function obtenerPorPrograma(string $programaId, bool $soloActivas = true): array {
        $pdo = self::obtenerConexion();
        $sql = 'SELECT id, codigo, programa_id, activo, creado_en
                FROM fichas
                WHERE programa_id = ?';
        if ($soloActivas) {
            $sql .= ' AND activo = 1';
        }
        $sql .= ' ORDER BY codigo ASC';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$programaId]);
        return $stmt->fetchAll();
    }

    /**
     * Busca una ficha por su código numérico exacto.
     */
    public function obtenerPorCodigo(string $codigo): ?array {
        $pdo = self::obtenerConexion();
        $stmt = $pdo->prepare(
            'SELECT f.id, f.codigo, f.programa_id, f.activo, p.nombre AS programa_nombre, p.activo AS programa_activo
             FROM fichas f
             INNER JOIN programa_formacion p ON f.programa_id = p.id
             WHERE f.codigo = ? AND p.eliminado = 0
             LIMIT 1'
        );
        $stmt->execute([$codigo]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Valida si un código de ficha existe y es válido para un programa.
     * Si no se proporciona programaId, valida si existe activa en cualquier programa activo.
     */
    public function esValida(string $codigo, ?string $programaId = null): bool {
        $ficha = $this->obtenerPorCodigo($codigo);
        if (!$ficha || (int)$ficha['activo'] !== 1 || (int)$ficha['programa_activo'] !== 1) {
            return false;
        }
        if (!empty($programaId) && $ficha['programa_id'] !== $programaId) {
            return false;
        }
        return true;
    }

    /**
     * Obtiene las fichas asignadas a un instructor mediante instructor_ficha.
     */
    public function obtenerFichasInstructor(string $instructorId): array {
        $pdo = self::obtenerConexion();
        $stmt = $pdo->prepare(
            'SELECT f.id, f.codigo, f.programa_id, p.nombre AS programa_nombre, ifi.asignado_en
             FROM instructor_ficha ifi
             INNER JOIN fichas f ON ifi.ficha_id = f.id
             INNER JOIN programa_formacion p ON f.programa_id = p.id
             WHERE ifi.instructor_id = ?
             ORDER BY f.codigo ASC'
        );
        $stmt->execute([$instructorId]);
        return $stmt->fetchAll();
    }

    /**
     * Sincroniza las fichas asignadas a un instructor.
     */
    public function sincronizarFichasInstructor(string $instructorId, array $fichaIds): void {
        $pdo = self::obtenerConexion();
        $stmtDel = $pdo->prepare('DELETE FROM instructor_ficha WHERE instructor_id = ?');
        $stmtDel->execute([$instructorId]);

        if (empty($fichaIds)) {
            return;
        }

        $stmtIns = $pdo->prepare('INSERT IGNORE INTO instructor_ficha (instructor_id, ficha_id) VALUES (?, ?)');
        foreach ($fichaIds as $fId) {
            if (is_string($fId) && !empty($fId)) {
                $stmtIns->execute([$instructorId, $fId]);
            }
        }
    }
}
