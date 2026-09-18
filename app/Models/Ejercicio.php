<?php
namespace App\Models;

use App\Core\Model;
use PDO;

class Ejercicio extends Model {
    /**
     * Ejercicios activos del RAP. Los eliminados desde el panel se conservan
     * desactivados para no perder el historial de intentos (HU20).
     */
    public function obtenerPorRap(string $rapId): array {
        $pdo = self::obtenerConexion();
        $stmt = $pdo->prepare('SELECT * FROM ejercicio WHERE rap_id = ? AND activo = 1 ORDER BY id ASC');
        $stmt->execute([$rapId]);
        return $stmt->fetchAll();
    }

    public function obtenerPorId(string $id): ?array {
        $pdo = self::obtenerConexion();
        $stmt = $pdo->prepare('SELECT * FROM ejercicio WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function crear(array $datos): string {
        $pdo = self::obtenerConexion();
        // El id se genera automáticamente por uuid() en BD si no se envía, pero es mejor generarlo en PHP para usarlo
        $id = isset($datos['id']) ? $datos['id'] : generarUUID();
        $stmt = $pdo->prepare('INSERT INTO ejercicio (id, rap_id, tipo, enunciado, instrucciones, max_intentos, puntos, vocab_ayuda_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([
            $id,
            $datos['rap_id'],
            $datos['tipo'],
            $datos['enunciado'],
            $datos['instrucciones'] ?? null,
            $datos['max_intentos'] ?? 3,
            $datos['puntos'] ?? 10,
            $datos['vocab_ayuda_id'] ?? null
        ]);
        return $id;
    }

    public function actualizar(string $id, array $datos): bool {
        $pdo = self::obtenerConexion();
        $stmt = $pdo->prepare('UPDATE ejercicio SET tipo = ?, enunciado = ?, instrucciones = ?, max_intentos = ?, puntos = ?, vocab_ayuda_id = ? WHERE id = ?');
        return $stmt->execute([
            $datos['tipo'],
            $datos['enunciado'],
            $datos['instrucciones'] ?? null,
            $datos['max_intentos'] ?? 3,
            $datos['puntos'] ?? 10,
            $datos['vocab_ayuda_id'] ?? null,
            $id
        ]);
    }

    /**
     * Vocabulario que se puede enlazar como recurso de ayuda de un ejercicio (RF-34).
     * Se ofrece el del módulo completo, no solo el del RAP, porque la página del RAP
     * ya presenta las actividades de todos sus RAPs hermanos.
     */
    public function vocabularioDelModulo(string $rapId): array {
        $pdo = self::obtenerConexion();
        $stmt = $pdo->prepare(
            'SELECT v.id, v.termino_en, v.termino_es
             FROM vocabulario v
             JOIN rap r ON r.id = v.rap_id
             WHERE r.nivel_id = (SELECT nivel_id FROM rap WHERE id = ?)
               AND v.activo = 1
             ORDER BY v.termino_en'
        );
        $stmt->execute([$rapId]);
        return $stmt->fetchAll();
    }

    /**
     * Comprueba que la palabra de ayuda exista, esté activa y pertenezca al mismo
     * módulo que el ejercicio. Devuelve null si no sirve, para no guardar un enlace roto.
     */
    public function ayudaValida(?string $vocabId, string $rapId): ?string {
        if (empty($vocabId)) return null;
        $pdo = self::obtenerConexion();
        $stmt = $pdo->prepare(
            'SELECT v.id
             FROM vocabulario v
             JOIN rap r ON r.id = v.rap_id
             WHERE v.id = ? AND v.activo = 1
               AND r.nivel_id = (SELECT nivel_id FROM rap WHERE id = ?)
             LIMIT 1'
        );
        $stmt->execute([$vocabId, $rapId]);
        return $stmt->fetchColumn() ?: null;
    }

    /**
     * Borrado lógico (HU20): intento_ejercicio apunta al ejercicio con RESTRICT, así
     * que un DELETE fallaba en cuanto un aprendiz lo había respondido.
     */
    public function eliminar(string $id): bool {
        $pdo = self::obtenerConexion();
        $stmt = $pdo->prepare('UPDATE ejercicio SET activo = 0 WHERE id = ?');
        return $stmt->execute([$id]);
    }
}
