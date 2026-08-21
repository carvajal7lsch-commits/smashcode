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

    /* ========================================================
     * HU06 / HU23 — Seguimiento pedagógico y reportes
     * ======================================================== */

    /**
     * Devuelve los intentos de quiz de todos los aprendices, con los mismos
     * filtros de nivel/RAP/estado que usa el listado de aprendices.
     * Es la fuente única de la vista de resultados y del CSV, para que el
     * instructor exporte exactamente lo que está viendo en pantalla.
     *
     * El estado se interpreta sobre el intento: 'completado' = aprobado,
     * 'en_progreso' = presentado sin aprobar. 'sin_iniciar' no aplica aquí
     * (un aprendiz sin intentos no tiene filas que mostrar).
     */
    public function obtenerResultadosQuiz(string $nivelId = '', string $rapId = '', string $estado = ''): array {
        $pdo = self::obtenerConexion();

        // El detalle de respuestas de un quiz largo supera los 1024 bytes que
        // GROUP_CONCAT trae por defecto y se cortaría a mitad de frase.
        $pdo->exec('SET SESSION group_concat_max_len = 100000');

        $sql = "SELECT i.id AS intento_id,
                       u.id AS aprendiz_id,
                       u.nombre_completo,
                       u.correo,
                       u.ficha_sena,
                       n.orden AS modulo_orden,
                       n.nombre AS modulo_nombre,
                       r.orden AS rap_orden,
                       r.titulo AS rap_titulo,
                       q.puntaje_minimo,
                       i.puntaje,
                       i.aprobado,
                       i.numero_intento,
                       i.duracion_seg,
                       i.creado_en,
                       (SELECT GROUP_CONCAT(
                                   CONCAT(p.texto, ' -> ',
                                          COALESCE(NULLIF(rq.respuesta_elegida, ''), '(sin responder)'),
                                          ' [', IF(rq.es_correcto = 1, 'correcta', 'incorrecta'), ']')
                                   ORDER BY p.texto SEPARATOR ' | ')
                        FROM respuesta_quiz rq
                        JOIN pregunta p ON p.id = rq.pregunta_id
                        WHERE rq.intento_quiz_id = i.id) AS detalle_respuestas
                FROM intento_quiz i
                JOIN usuarios u ON u.id = i.usuario_id
                JOIN quiz q ON q.id = i.quiz_id
                JOIN rap r ON r.id = q.rap_id
                JOIN nivel n ON n.id = r.nivel_id
                WHERE u.rol = 'aprendiz' AND u.eliminado = 0";

        $params = [];

        if (!empty($nivelId)) {
            $sql .= " AND n.id = :nivel_id";
            $params['nivel_id'] = $nivelId;
        }

        if (!empty($rapId)) {
            $sql .= " AND r.id = :rap_id";
            $params['rap_id'] = $rapId;
        }

        if ($estado === 'completado') {
            $sql .= " AND i.aprobado = 1";
        } elseif ($estado === 'en_progreso') {
            $sql .= " AND i.aprobado = 0";
        } elseif ($estado === 'sin_iniciar') {
            // Un intento registrado nunca está "sin iniciar": no hay filas posibles.
            $sql .= " AND 1 = 0";
        }

        $sql .= " ORDER BY i.creado_en DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Avance de cada aprendiz en cada módulo, con el detalle RAP por RAP (HU06/HU23).
     *
     * Devuelve una matriz indexada por usuario y por orden de módulo, para que
     * la tabla de aprendices resuelva cada celda sin lanzar una consulta por
     * fila. El CROSS JOIN contra nivel es intencional: garantiza que todo
     * aprendiz tenga una celda por cada módulo, incluso los que no ha tocado,
     * que es justo lo que el instructor necesita ver.
     */
    public function obtenerAvancePorModuloDeAprendices(): array {
        $pdo = self::obtenerConexion();

        // El detalle por RAP de un módulo supera los 1024 bytes por defecto
        $pdo->exec('SET SESSION group_concat_max_len = 8192');

        $stmt = $pdo->query(
            "SELECT u.id AS usuario_id,
                    n.orden AS modulo_orden,
                    ROUND(AVG(COALESCE(p.porcentaje, 0)), 0) AS avance,
                    SUM(CASE WHEN p.completado = 1 THEN 1 ELSE 0 END) AS raps_completados,
                    COUNT(r.id) AS total_raps,
                    GROUP_CONCAT(
                        CONCAT(r.titulo, ': ', ROUND(COALESCE(p.porcentaje, 0), 0), '%')
                        ORDER BY r.orden SEPARATOR ' | '
                    ) AS detalle
             FROM usuarios u
             CROSS JOIN nivel n
             JOIN rap r ON r.nivel_id = n.id AND r.activo = 1
             LEFT JOIN progreso p ON p.rap_id = r.id AND p.usuario_id = u.id
             WHERE u.rol = 'aprendiz' AND u.activo = 1 AND u.eliminado = 0 AND n.activo = 1
             GROUP BY u.id, n.id
             ORDER BY u.id, n.orden"
        );

        $matriz = [];
        foreach ($stmt->fetchAll() as $fila) {
            $matriz[$fila['usuario_id']][(int) $fila['modulo_orden']] = [
                'avance'           => (float) $fila['avance'],
                'raps_completados' => (int) $fila['raps_completados'],
                'total_raps'       => (int) $fila['total_raps'],
                'detalle'          => (string) $fila['detalle']
            ];
        }

        return $matriz;
    }

    /**
     * Ejercicios con mayor tasa de error del grupo (HU06/HU23).
     * Solo entran ejercicios que alguien haya intentado; ordena por tasa de
     * error y desempata por número de fallos, para que un ejercicio con un
     * único intento fallido no desplace a uno que falla el curso entero.
     */
    public function obtenerEjerciciosConMasErrores(string $nivelId = '', string $rapId = '', int $limite = 10): array {
        $pdo = self::obtenerConexion();

        $sql = "SELECT e.id,
                       e.enunciado,
                       e.tipo,
                       n.orden AS modulo_orden,
                       n.nombre AS modulo_nombre,
                       r.orden AS rap_orden,
                       r.titulo AS rap_titulo,
                       COUNT(ie.id) AS total_intentos,
                       SUM(CASE WHEN ie.es_correcto = 0 THEN 1 ELSE 0 END) AS total_fallos,
                       COUNT(DISTINCT ie.usuario_id) AS aprendices_afectados,
                       ROUND(100 * SUM(CASE WHEN ie.es_correcto = 0 THEN 1 ELSE 0 END) / COUNT(ie.id), 1) AS tasa_error
                FROM intento_ejercicio ie
                JOIN ejercicio e ON e.id = ie.ejercicio_id
                JOIN rap r ON r.id = e.rap_id
                JOIN nivel n ON n.id = r.nivel_id
                JOIN usuarios u ON u.id = ie.usuario_id
                WHERE u.rol = 'aprendiz' AND u.eliminado = 0";

        $params = [];

        if (!empty($nivelId)) {
            $sql .= " AND n.id = :nivel_id";
            $params['nivel_id'] = $nivelId;
        }

        if (!empty($rapId)) {
            $sql .= " AND r.id = :rap_id";
            $params['rap_id'] = $rapId;
        }

        $sql .= " GROUP BY e.id
                  HAVING total_fallos > 0
                  ORDER BY tasa_error DESC, total_fallos DESC
                  LIMIT " . max(1, $limite);

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
