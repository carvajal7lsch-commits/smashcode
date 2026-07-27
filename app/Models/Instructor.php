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

    /**
     * Obtiene el listado de aprendices con filtros por Nivel, RAP y Estado (HU23).
     */
    public function obtenerListadoAprendicesFiltrado($nivel_id = '', $rap_id = '', $estado = ''): array {
        $pdo = self::obtenerConexion();
        
        $sql = "SELECT u.id, u.nombre_completo, u.correo, u.xp_puntos,
                       COUNT(p.id) AS raps_iniciados,
                       COALESCE(SUM(p.completado), 0) AS raps_completados,
                       COALESCE(AVG(p.porcentaje), 0) AS avance_promedio
                FROM usuarios u
                LEFT JOIN progreso p ON p.usuario_id = u.id
                LEFT JOIN rap r ON p.rap_id = r.id
                WHERE u.rol = 'aprendiz' AND u.activo = 1";
        
        $params = [];
        
        // Filtro por Nivel
        if (!empty($nivel_id)) {
            $sql .= " AND r.nivel_id = :nivel_id";
            $params['nivel_id'] = $nivel_id;
        }
        
        // Filtro por RAP
        if (!empty($rap_id)) {
            $sql .= " AND p.rap_id = :rap_id";
            $params['rap_id'] = $rap_id;
        }

        // Filtro por Estado (completado, en_progreso, sin_iniciar)
        if (!empty($estado)) {
            if ($estado === 'completado') {
                $sql .= " AND p.completado = 1";
            } elseif ($estado === 'en_progreso') {
                $sql .= " AND p.porcentaje > 0 AND p.completado = 0";
            } elseif ($estado === 'sin_iniciar') {
                // Si buscan sin iniciar pero pasaron un RAP, significa que no existe registro en progreso para ese RAP.
                if (!empty($rap_id)) {
                    // Quitamos la condicion del AND normal y cambiamos la logica.
                    // Esto es complejo si lo unimos directamente. 
                    // Es mejor reescribir la query para 'sin_iniciar' si hay un RAP específico:
                    $sql = "SELECT u.id, u.nombre_completo, u.correo, u.xp_puntos,
                                   0 AS raps_iniciados, 0 AS raps_completados, 0 AS avance_promedio
                            FROM usuarios u
                            WHERE u.rol = 'aprendiz' AND u.activo = 1
                            AND NOT EXISTS (
                                SELECT 1 FROM progreso p2 
                                WHERE p2.usuario_id = u.id AND p2.rap_id = :rap_id
                            )";
                } else {
                    // Sin iniciar general (0 progreso total)
                    $sql .= " AND p.id IS NULL";
                }
            }
        }
        
        // Si no se sobreescribió la query por el caso 'sin_iniciar' especifico
        if (strpos($sql, 'GROUP BY') === false) {
            $sql .= " GROUP BY u.id ORDER BY avance_promedio DESC";
        }
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
