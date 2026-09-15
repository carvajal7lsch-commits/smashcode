<?php
namespace App\Models;

use App\Core\Model;

/**
 * Progreso.php
 * Modelo de negocio para la gestión del progreso de los usuarios.
 * Mapea el avance de los aprendices en cada Resultado de Aprendizaje (RAP).
 */
class Progreso extends Model {

    /**
     * Obtiene el progreso de un usuario por RAP, incluyendo información del nivel.
     */
    public function obtenerProgresoPorUsuario(string $usuarioId): array {
        $pdo = self::obtenerConexion();
        $stmt = $pdo->prepare(
            'SELECT p.rap_id, p.porcentaje, p.completado, p.mejor_puntaje_quiz, r.titulo, r.nivel_id, n.nombre AS nombre_nivel, n.orden AS orden_nivel
             FROM progreso p
             JOIN rap r ON r.id = p.rap_id
             JOIN nivel n ON n.id = r.nivel_id
             WHERE p.usuario_id = ?
             ORDER BY n.orden'
        );
        $stmt->execute([$usuarioId]);
        return $stmt->fetchAll();
    }

    /**
     * Actualiza o inserta el progreso de un usuario para un RAP específico.
     */
    public function actualizarProgreso(string $usuarioId, string $rapId, float $porcentaje, int $completado): bool {
        $pdo = self::obtenerConexion();
        $stmt = $pdo->prepare('SELECT id, porcentaje, completado FROM progreso WHERE usuario_id = ? AND rap_id = ? LIMIT 1');
        $stmt->execute([$usuarioId, $rapId]);
        $row = $stmt->fetch();

        if ($row) {
            $nuevoCompletado = $row['completado'] ? 1 : $completado;
            $nuevoPorcentaje = max((float)$row['porcentaje'], $porcentaje);
            $stmtUpdate = $pdo->prepare('UPDATE progreso SET porcentaje = ?, completado = ?, ultimo_acceso = NOW() WHERE usuario_id = ? AND rap_id = ?');
            return $stmtUpdate->execute([$nuevoPorcentaje, $nuevoCompletado, $usuarioId, $rapId]);
        } else {
            $stmtInsert = $pdo->prepare('INSERT INTO progreso (id, usuario_id, rap_id, porcentaje, completado, ultimo_acceso) VALUES (?, ?, ?, ?, ?, NOW())');
            return $stmtInsert->execute([generarUUID(), $usuarioId, $rapId, $porcentaje, $completado]);
        }
    }

    /**
     * Empieza una ronda nueva de intentos del quiz (HU22). Se llama al terminar la
     * práctica: los intentos reprobados anteriores dejan de contar para el límite.
     */
    public function iniciarRondaQuiz(string $usuarioId, string $rapId): bool {
        $pdo = self::obtenerConexion();
        $stmt = $pdo->prepare('UPDATE progreso SET ronda_quiz_desde = NOW() WHERE usuario_id = ? AND rap_id = ?');
        return $stmt->execute([$usuarioId, $rapId]);
    }

    /**
     * Guarda el mejor puntaje del quiz para un RAP, conservando el mejor histórico.
     */
    public function guardarMejorPuntaje(string $usuarioId, string $rapId, float $puntaje): bool {
        $pdo = self::obtenerConexion();
        $stmtCheck = $pdo->prepare('SELECT id, mejor_puntaje_quiz FROM progreso WHERE usuario_id = ? AND rap_id = ? LIMIT 1');
        $stmtCheck->execute([$usuarioId, $rapId]);
        $row = $stmtCheck->fetch();

        if ($row) {
            $mejorPuntaje = max((float)$row['mejor_puntaje_quiz'], $puntaje);
            $stmtUpdate = $pdo->prepare('UPDATE progreso SET mejor_puntaje_quiz = ? WHERE usuario_id = ? AND rap_id = ?');
            return $stmtUpdate->execute([$mejorPuntaje, $usuarioId, $rapId]);
        } else {
            $stmtInsert = $pdo->prepare('INSERT INTO progreso (id, usuario_id, rap_id, porcentaje, completado, mejor_puntaje_quiz, ultimo_acceso) VALUES (?, ?, ?, 0.00, 0, ?, NOW())');
            return $stmtInsert->execute([generarUUID(), $usuarioId, $rapId, $puntaje]);
        }
    }

    /**
     * Avance del aprendiz módulo por módulo y RAP por RAP (HU05).
     *
     * Parte de la lista completa de módulos/RAPs activos, no de la tabla
     * progreso, para que los RAPs que el aprendiz todavía no ha abierto
     * cuenten como 0% en lugar de desaparecer del cálculo.
     */
    public function obtenerAvancePorModulo(string $usuarioId): array {
        $pdo = self::obtenerConexion();
        $stmt = $pdo->prepare(
            'SELECT n.id AS nivel_id, n.orden AS nivel_orden, n.nombre AS nivel_nombre,
                    r.id AS rap_id, r.orden AS rap_orden, r.titulo AS rap_titulo,
                    COALESCE(p.porcentaje, 0) AS porcentaje,
                    COALESCE(p.completado, 0) AS completado,
                    COALESCE(p.mejor_puntaje_quiz, 0) AS mejor_puntaje_quiz,
                    COALESCE(p.tiempo_total_seg, 0) AS tiempo_total_seg,
                    p.ultimo_acceso
             FROM nivel n
             JOIN rap r ON r.nivel_id = n.id AND r.activo = 1
             LEFT JOIN progreso p ON p.rap_id = r.id AND p.usuario_id = ?
             WHERE n.activo = 1
             ORDER BY n.orden, r.orden'
        );
        $stmt->execute([$usuarioId]);

        $modulos = [];
        foreach ($stmt->fetchAll() as $fila) {
            $nivelId = $fila['nivel_id'];

            if (!isset($modulos[$nivelId])) {
                $modulos[$nivelId] = [
                    'nivel_id'         => $nivelId,
                    'orden'            => (int) $fila['nivel_orden'],
                    'nombre'           => $fila['nivel_nombre'],
                    'raps'             => [],
                    'raps_completados' => 0,
                    'tiempo_seg'       => 0
                ];
            }

            $modulos[$nivelId]['raps'][] = [
                'rap_id'             => $fila['rap_id'],
                'orden'              => (int) $fila['rap_orden'],
                'titulo'             => $fila['rap_titulo'],
                'porcentaje'         => (float) $fila['porcentaje'],
                'completado'         => (int) $fila['completado'] === 1,
                'mejor_puntaje_quiz' => (float) $fila['mejor_puntaje_quiz'],
                'tiempo_seg'         => (int) $fila['tiempo_total_seg'],
                'ultimo_acceso'      => $fila['ultimo_acceso']
            ];

            $modulos[$nivelId]['raps_completados'] += ((int) $fila['completado'] === 1) ? 1 : 0;
            $modulos[$nivelId]['tiempo_seg']       += (int) $fila['tiempo_total_seg'];
        }

        // El avance del módulo es el promedio de sus RAPs, contando en 0 los no iniciados
        foreach ($modulos as $id => $mod) {
            $totalRaps = count($mod['raps']);
            $suma = array_sum(array_column($mod['raps'], 'porcentaje'));
            $modulos[$id]['total_raps'] = $totalRaps;
            $modulos[$id]['porcentaje'] = $totalRaps > 0 ? round($suma / $totalRaps, 1) : 0.0;
        }

        return array_values($modulos);
    }

    /**
     * Suma tiempo de estudio al RAP (HU05: "tiempo total invertido").
     *
     * Se acumula, nunca se reemplaza: un RAP se puede repetir y cada pasada
     * suma. Crea la fila de progreso si el aprendiz aún no tenía una.
     */
    public function sumarTiempo(string $usuarioId, string $rapId, int $segundos): bool {
        if ($segundos <= 0) {
            return true;
        }

        // Un valor absurdo suele ser una pestaña olvidada abierta, no estudio real
        $segundos = min($segundos, 3600);

        $pdo = self::obtenerConexion();
        $stmt = $pdo->prepare('SELECT id FROM progreso WHERE usuario_id = ? AND rap_id = ? LIMIT 1');
        $stmt->execute([$usuarioId, $rapId]);

        if ($stmt->fetchColumn()) {
            $stmtUp = $pdo->prepare(
                'UPDATE progreso SET tiempo_total_seg = tiempo_total_seg + ?, ultimo_acceso = NOW()
                 WHERE usuario_id = ? AND rap_id = ?'
            );
            return $stmtUp->execute([$segundos, $usuarioId, $rapId]);
        }

        $stmtIns = $pdo->prepare(
            'INSERT INTO progreso (id, usuario_id, rap_id, porcentaje, completado, tiempo_total_seg, ultimo_acceso)
             VALUES (?, ?, ?, 0.00, 0, ?, NOW())'
        );
        return $stmtIns->execute([generarUUID(), $usuarioId, $rapId, $segundos]);
    }

    /**
     * Tiempo total de estudio del aprendiz en toda la plataforma, en segundos (HU05).
     */
    public function obtenerTiempoTotal(string $usuarioId): int {
        $pdo = self::obtenerConexion();
        $stmt = $pdo->prepare('SELECT COALESCE(SUM(tiempo_total_seg), 0) FROM progreso WHERE usuario_id = ?');
        $stmt->execute([$usuarioId]);
        return (int) $stmt->fetchColumn();
    }
}
