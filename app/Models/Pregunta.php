<?php
namespace App\Models;

use App\Core\Model;
use PDO;

class Pregunta extends Model {
    public function obtenerPorQuiz(string $quizId): array {
        $pdo = self::obtenerConexion();
        $stmt = $pdo->prepare('SELECT * FROM pregunta WHERE quiz_id = ? ORDER BY id ASC');
        $stmt->execute([$quizId]);
        return $stmt->fetchAll();
    }

    public function crear(array $datos): string {
        $pdo = self::obtenerConexion();
        $id = !empty($datos['id']) ? $datos['id'] : generarUUID();
        $stmt = $pdo->prepare('INSERT INTO pregunta (id, quiz_id, texto, opciones, respuesta_correcta, retroalimentacion) VALUES (?, ?, ?, ?, ?, ?)');
        $stmt->execute([
            $id,
            $datos['quiz_id'],
            $datos['texto'],
            is_array($datos['opciones']) ? json_encode($datos['opciones']) : $datos['opciones'],
            $datos['respuesta_correcta'],
            $datos['retroalimentacion'] ?? null
        ]);
        return $id;
    }

    public function actualizar(string $id, array $datos): bool {
        $pdo = self::obtenerConexion();
        $stmt = $pdo->prepare('UPDATE pregunta SET texto = ?, opciones = ?, respuesta_correcta = ?, retroalimentacion = ? WHERE id = ?');
        return $stmt->execute([
            $datos['texto'],
            is_array($datos['opciones']) ? json_encode($datos['opciones']) : $datos['opciones'],
            $datos['respuesta_correcta'],
            $datos['retroalimentacion'] ?? null,
            $id
        ]);
    }

    public function eliminarPorQuiz(string $quizId): bool {
        $pdo = self::obtenerConexion();
        $stmt = $pdo->prepare('DELETE FROM pregunta WHERE quiz_id = ?');
        return $stmt->execute([$quizId]);
    }

    public function eliminar(string $id): bool {
        $pdo = self::obtenerConexion();
        $stmt = $pdo->prepare('DELETE FROM pregunta WHERE id = ?');
        return $stmt->execute([$id]);
    }
}
