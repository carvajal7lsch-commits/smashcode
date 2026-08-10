<?php
namespace App\Models;

use App\Core\Model;
use PDO;
use Exception;

/**
 * GamificacionConfig.php
 * Modelo para gestionar los parámetros configurables de gamificación y puntos XP.
 */
class GamificacionConfig extends Model {

    /**
     * Valores por defecto si la base de datos no tiene la tabla o los registros aún.
     */
    private static array $defaults = [
        'xp_ejercicio_correcto' => 10,
        'xp_quiz_aprobado'     => 50,
        'xp_quiz_perfecto'     => 100,
        'xp_por_nivel'         => 500,
        'dias_racha_insignia'  => 7
    ];

    /**
     * Obtiene todos los parámetros de gamificación como un array asociativo [clave => valor].
     */
    public function obtenerTodas(): array {
        $pdo = self::obtenerConexion();
        try {
            $stmt = $pdo->query('SELECT clave, valor, descripcion, actualizado_en FROM configuracion_gamificacion');
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if (empty($rows)) {
                return self::$defaults;
            }
            $config = self::$defaults;
            foreach ($rows as $r) {
                $config[$r['clave']] = (int)$r['valor'];
            }
            return $config;
        } catch (Exception $e) {
            return self::$defaults;
        }
    }

    /**
     * Obtiene el listado completo con metadatos (descripción y fecha).
     */
    public function obtenerListadoConMetadatos(): array {
        $pdo = self::obtenerConexion();
        try {
            $stmt = $pdo->query('SELECT clave, valor, descripcion, actualizado_en FROM configuracion_gamificacion ORDER BY clave ASC');
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Obtiene un valor de configuración individual.
     */
    public function obtenerValor(string $clave, ?int $defecto = null): int {
        $pdo = self::obtenerConexion();
        try {
            $stmt = $pdo->prepare('SELECT valor FROM configuracion_gamificacion WHERE clave = ? LIMIT 1');
            $stmt->execute([$clave]);
            $val = $stmt->fetchColumn();
            if ($val !== false) {
                return (int)$val;
            }
        } catch (Exception $e) {
            // fallback
        }
        return $defecto ?? self::$defaults[$clave] ?? 0;
    }

    /**
     * Guarda / actualiza múltiples configuraciones de gamificación en lote.
     */
    public function guardarConfiguracion(array $valores): bool {
        $pdo = self::obtenerConexion();
        $pdo->beginTransaction();
        try {
            $stmt = $pdo->prepare('INSERT INTO configuracion_gamificacion (clave, valor) VALUES (?, ?) ON DUPLICATE KEY UPDATE valor = VALUES(valor)');
            foreach ($valores as $clave => $val) {
                if (array_key_exists($clave, self::$defaults)) {
                    $intVal = max(0, (int)$val);
                    $stmt->execute([$clave, $intVal]);
                }
            }
            $pdo->commit();
            return true;
        } catch (Exception $e) {
            $pdo->rollBack();
            error_log('[GamificacionConfig] Error al guardar configuración: ' . $e->getMessage());
            return false;
        }
    }
}
