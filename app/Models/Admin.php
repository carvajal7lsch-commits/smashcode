<?php
namespace App\Models;

use App\Core\Model;

/**
 * Admin.php
 * Modelo de negocio para la gestión administrativa y estadísticas globales (KPIs).
 */
class Admin extends Model {

    /**
     * Obtiene el número total de usuarios registrados con el rol de aprendiz.
     */
    public function obtenerTotalUsuarios(): int {
        $pdo = self::obtenerConexion();
        return (int) $pdo->query("SELECT COUNT(*) FROM usuarios WHERE rol = 'aprendiz'")->fetchColumn();
    }

    /**
     * Obtiene el número de aprendices activos.
     */
    public function obtenerAprendicesActivos(): int {
        $pdo = self::obtenerConexion();
        return (int) $pdo->query("SELECT COUNT(*) FROM usuarios WHERE rol = 'aprendiz' AND activo = 1")->fetchColumn();
    }

    /**
     * Obtiene la suma total de puntos XP acumulados por todos los usuarios.
     */
    public function obtenerTotalXP(): int {
        $pdo = self::obtenerConexion();
        return (int) $pdo->query("SELECT COALESCE(SUM(xp_puntos), 0) FROM usuarios")->fetchColumn();
    }

    /**
     * Obtiene el total de quizzes aprobados (completados).
     */
    public function obtenerQuizzesCompletados(): int {
        $pdo = self::obtenerConexion();
        return (int) $pdo->query("SELECT COUNT(*) FROM intento_quiz WHERE aprobado = 1")->fetchColumn();
    }

    /**
     * Obtiene la lista de los últimos 5 intentos de quizzes aprobados o reprobados.
     */
    public function obtenerActividadReciente(): array {
        $pdo = self::obtenerConexion();
        $stmt = $pdo->query(
            "SELECT u.nombre_completo, iq.puntaje, iq.aprobado, iq.creado_en, r.titulo AS rap_titulo
             FROM intento_quiz iq
             JOIN usuarios u ON u.id = iq.usuario_id
             JOIN quiz q     ON q.id = iq.quiz_id
             JOIN rap r      ON r.id = q.rap_id
             ORDER BY iq.creado_en DESC LIMIT 5"
        );
        return $stmt->fetchAll();
    }

    /**
     * Obtiene el rendimiento semanal (últimos 7 días) de quizzes completados.
     */
    public function obtenerRendimientoSemanal(): array {
        $pdo = self::obtenerConexion();
        // MySQL query to get counts grouped by date for the last 7 days
        $stmt = $pdo->query("
            SELECT DATE(creado_en) as fecha, COUNT(*) as total 
            FROM intento_quiz 
            WHERE aprobado = 1 AND creado_en >= DATE(NOW() - INTERVAL 7 DAY)
            GROUP BY DATE(creado_en)
            ORDER BY DATE(creado_en) ASC
        ");
        $resultados = $stmt->fetchAll();
        
        $datos = [];
        $dias = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];
        
        // Fill last 7 days including today with 0 if no data
        for ($i = 6; $i >= 0; $i--) {
            $fecha = date('Y-m-d', strtotime("-$i days"));
            $diaSemana = $dias[date('w', strtotime("-$i days"))];
            $total = 0;
            
            foreach ($resultados as $r) {
                if ($r['fecha'] === $fecha) {
                    $total = (int)$r['total'];
                    break;
                }
            }
            
            $datos[] = [
                'dia' => $diaSemana,
                'val' => $total
            ];
        }
        
        return $datos;
    }
}
