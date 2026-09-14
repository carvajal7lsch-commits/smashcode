<?php
namespace App\Models;

use App\Core\Model;

/**
 * Instructor.php
 * Modelo de negocio para la gestión y seguimiento del desempeño de los Aprendices por parte del Instructor.
 *
 * Alcance de "mis aprendices" (HU23): los métodos de consulta reciben un
 * $programaId opcional como último parámetro. Con un programa solo cuentan los
 * aprendices de ese programa de formación; con null —instructor sin programa
 * asignado— se ve a todos, que era el comportamiento anterior. Ir al final y
 * ser opcional mantiene compatibles las llamadas que ya existían.
 */
class Instructor extends Model {

    /** Estados del filtro de aprendices (HU23). */
    public const ESTADOS_APRENDIZ = ['completado', 'en_progreso', 'sin_iniciar'];

    /** Estados del filtro de resultados de quiz: aprobado o no aprobado. */
    public const ESTADOS_RESULTADO = ['completado', 'en_progreso'];

    /**
     * Fragmento SQL que limita la consulta a los aprendices de un programa.
     * Sin programa devuelve cadena vacía y no filtra nada.
     */
    private function condicionPrograma(?string $programaId, string $aliasUsuario, array &$params): string {
        if ($programaId === null || $programaId === '') {
            return '';
        }

        $params['programa_id'] = $programaId;
        return " AND {$aliasUsuario}.programa_id = :programa_id";
    }

    /**
     * Obtiene el número total de aprendices activos.
     */
    public function obtenerTotalAprendices(?string $programaId = null): int {
        $pdo = self::obtenerConexion();
        $params = [];
        $sql = "SELECT COUNT(*)
                FROM usuarios u
                WHERE u.rol = 'aprendiz' AND u.activo = 1 AND u.eliminado = 0"
             . $this->condicionPrograma($programaId, 'u', $params);

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Obtiene el número de aprendices que han completado al menos un RAP.
     */
    public function obtenerCompletaronAlgo(?string $programaId = null): int {
        $pdo = self::obtenerConexion();
        $params = [];
        $sql = "SELECT COUNT(DISTINCT p.usuario_id)
                FROM progreso p
                JOIN usuarios u ON u.id = p.usuario_id
                JOIN rap r ON r.id = p.rap_id AND r.activo = 1
                WHERE p.completado = 1
                  AND u.rol = 'aprendiz' AND u.activo = 1 AND u.eliminado = 0"
             . $this->condicionPrograma($programaId, 'u', $params);

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Obtiene el promedio de puntaje en todos los quizzes intentados.
     */
    public function obtenerPromedioQuiz(?string $programaId = null): float {
        $pdo = self::obtenerConexion();
        $params = [];
        $sql = "SELECT COALESCE(AVG(i.puntaje), 0)
                FROM intento_quiz i
                JOIN usuarios u ON u.id = i.usuario_id
                WHERE u.rol = 'aprendiz' AND u.eliminado = 0"
             . $this->condicionPrograma($programaId, 'u', $params);

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return (float) $stmt->fetchColumn();
    }

    /**
     * Obtiene el listado completo de aprendices activos con sus estadísticas de progreso y XP.
     * Es el listado filtrado sin filtros, para que el dashboard y "Mis Aprendices"
     * calculen el avance exactamente de la misma forma.
     */
    public function obtenerListadoAprendices(?string $programaId = null): array {
        return $this->obtenerListadoAprendicesFiltrado('', '', '', $programaId);
    }

    /**
     * Obtiene el listado de aprendices con filtros por Nivel, RAP y Estado (HU23).
     *
     * Cada aprendiz se cruza con todos los RAPs activos del alcance elegido
     * —el curso completo, un módulo o un RAP—, no solo con los que ya abrió.
     * Así quien no ha empezado sigue apareciendo como "sin iniciar", y el avance
     * promedio cuenta en 0% los RAPs sin tocar, igual que el panel del aprendiz
     * (HU05). Cruzar solo contra su progreso escondía del filtro por nivel o RAP
     * a quien no había empezado, y mostraba 100% a quien terminó un único RAP.
     *
     * El estado se evalúa sobre ese mismo alcance:
     *  - completado:  terminó todos los RAPs del alcance
     *  - en_progreso: tiene actividad en alguno pero no los terminó todos
     *  - sin_iniciar: no tiene actividad en ninguno
     */
    public function obtenerListadoAprendicesFiltrado($nivel_id = '', $rap_id = '', $estado = '', ?string $programaId = null): array {
        $pdo = self::obtenerConexion();
        $params = [];

        $sql = "SELECT u.id, u.nombre_completo, u.correo, u.xp_puntos,
                       COUNT(r.id) AS total_raps,
                       COUNT(p.id) AS raps_iniciados,
                       SUM(CASE WHEN p.completado = 1 THEN 1 ELSE 0 END) AS raps_completados,
                       AVG(COALESCE(p.porcentaje, 0)) AS avance_promedio
                FROM usuarios u
                JOIN rap r ON r.activo = 1
                JOIN nivel n ON n.id = r.nivel_id AND n.activo = 1
                LEFT JOIN progreso p ON p.rap_id = r.id AND p.usuario_id = u.id
                WHERE u.rol = 'aprendiz' AND u.activo = 1 AND u.eliminado = 0"
             . $this->condicionPrograma($programaId, 'u', $params);

        if (!empty($nivel_id)) {
            $sql .= " AND n.id = :nivel_id";
            $params['nivel_id'] = $nivel_id;
        }

        if (!empty($rap_id)) {
            $sql .= " AND r.id = :rap_id";
            $params['rap_id'] = $rap_id;
        }

        $sql .= " GROUP BY u.id, u.nombre_completo, u.correo, u.xp_puntos";

        // El estado se filtra sobre los totales ya agrupados, así que no cambia los
        // parámetros de la consulta: combinar nivel, RAP y estado siempre es válido.
        if ($estado === 'completado') {
            $sql .= " HAVING raps_completados = total_raps";
        } elseif ($estado === 'en_progreso') {
            $sql .= " HAVING raps_iniciados > 0 AND raps_completados < total_raps";
        } elseif ($estado === 'sin_iniciar') {
            $sql .= " HAVING raps_iniciados = 0";
        }

        $sql .= " ORDER BY avance_promedio DESC, u.nombre_completo ASC";

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
    public function obtenerResultadosQuiz(string $nivelId = '', string $rapId = '', string $estado = '', ?string $programaId = null): array {
        $pdo = self::obtenerConexion();

        // El detalle de respuestas de un quiz largo supera los 1024 bytes que
        // GROUP_CONCAT trae por defecto y se cortaría a mitad de frase.
        $pdo->exec('SET SESSION group_concat_max_len = 100000');

        $params = [];

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
                WHERE u.rol = 'aprendiz' AND u.eliminado = 0"
             . $this->condicionPrograma($programaId, 'u', $params);

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
    public function obtenerAvancePorModuloDeAprendices(?string $programaId = null): array {
        $pdo = self::obtenerConexion();

        // El detalle por RAP de un módulo supera los 1024 bytes por defecto
        $pdo->exec('SET SESSION group_concat_max_len = 8192');

        $params = [];
        $sql = "SELECT u.id AS usuario_id,
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
                WHERE u.rol = 'aprendiz' AND u.activo = 1 AND u.eliminado = 0 AND n.activo = 1"
             . $this->condicionPrograma($programaId, 'u', $params)
             . " GROUP BY u.id, n.id
                 ORDER BY u.id, n.orden";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

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
     * Avance de cada aprendiz en cada RAP activo (HU23: "% de avance por RAP").
     *
     * Solo devuelve los RAPs con actividad; la vista toma como 0% los que no
     * aparecen. Se indexa por usuario y por id de RAP para que, al filtrar por
     * un módulo, la tabla muestre una columna por cada RAP de ese módulo.
     */
    public function obtenerAvancePorRapDeAprendices(?string $programaId = null): array {
        $pdo = self::obtenerConexion();
        $params = [];

        $sql = "SELECT p.usuario_id, p.rap_id, p.porcentaje, p.completado
                FROM progreso p
                JOIN usuarios u ON u.id = p.usuario_id
                JOIN rap r ON r.id = p.rap_id AND r.activo = 1
                WHERE u.rol = 'aprendiz' AND u.activo = 1 AND u.eliminado = 0"
             . $this->condicionPrograma($programaId, 'u', $params);

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        $matriz = [];
        foreach ($stmt->fetchAll() as $fila) {
            $matriz[$fila['usuario_id']][$fila['rap_id']] = [
                'porcentaje' => (float) $fila['porcentaje'],
                'completado' => (int) $fila['completado'] === 1
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
    public function obtenerEjerciciosConMasErrores(string $nivelId = '', string $rapId = '', int $limite = 10, ?string $programaId = null): array {
        $pdo = self::obtenerConexion();
        $params = [];

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
                WHERE u.rol = 'aprendiz' AND u.eliminado = 0"
             . $this->condicionPrograma($programaId, 'u', $params);

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
