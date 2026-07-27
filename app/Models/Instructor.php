<?php
namespace App\Models;

use App\Core\Model;

/**
 * Instructor.php
 * Modelo de negocio para la gestión y seguimiento del desempeño de los Aprendices por parte del Instructor.
 */
class Instructor extends Model {

    /**
     * Obtiene el número total de aprendices activos.
     */
    public function obtenerTotalAprendices(): int {
        $pdo = self::obtenerConexion();
        return (int) $pdo->query("SELECT COUNT(*) FROM usuarios WHERE rol = 'aprendiz' AND activo = 1")->fetchColumn();
    }

    /**
     * Obtiene el número de aprendices que han completado al menos un RAP.
     */
    public function obtenerCompletaronAlgo(): int {
        $pdo = self::obtenerConexion();
        return (int) $pdo->query("SELECT COUNT(DISTINCT usuario_id) FROM progreso WHERE completado = 1")->fetchColumn();
    }

    /**
     * Obtiene el promedio de puntaje en todos los quizzes intentados.
     */
    public function obtenerPromedioQuiz(): float {
        $pdo = self::obtenerConexion();
        return (float) $pdo->query("SELECT COALESCE(AVG(puntaje), 0) FROM intento_quiz")->fetchColumn();
    }

    /**
     * Obtiene el listado completo de aprendices activos con sus estadísticas de progreso y XP.
     */
    public function obtenerListadoAprendices(): array {
        $pdo = self::obtenerConexion();
        $stmt = $pdo->query(
            "SELECT u.id, u.nombre_completo, u.correo, u.xp_puntos,
                    COUNT(p.id) AS raps_iniciados,
                    COALESCE(SUM(p.completado), 0) AS raps_completados,
                    COALESCE(AVG(p.porcentaje), 0) AS avance_promedio
             FROM usuarios u
             LEFT JOIN progreso p ON p.usuario_id = u.id
             WHERE u.rol = 'aprendiz' AND u.activo = 1
             GROUP BY u.id
             ORDER BY avance_promedio DESC"
        );
        return $stmt->fetchAll();
    }
}
