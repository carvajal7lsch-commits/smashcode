<?php
namespace App\Models;

use App\Core\Model;
use PDO;

/**
 * Vocabulario.php
 * Modelo para la gestión de vocabulario médico dentro de cada RAP (HU19).
 */
class Vocabulario extends Model {

    public function obtenerPorRap(string $rapId): array {
        $pdo  = self::obtenerConexion();
        $stmt = $pdo->prepare(
            'SELECT v.*, c.nombre AS categoria_nombre, a.nombre AS area_clinica_nombre
             FROM vocabulario v
             LEFT JOIN categoria_vocabulario c ON v.categoria_id = c.id
             LEFT JOIN area_clinica a ON v.area_clinica_id = a.id
             WHERE v.rap_id = ?
             ORDER BY v.termino_en'
        );
        $stmt->execute([$rapId]);
        return $stmt->fetchAll();
    }

    public function obtenerTituloRap(string $rapId): ?string {
        $pdo  = self::obtenerConexion();
        $stmt = $pdo->prepare('SELECT titulo FROM rap WHERE id = ?');
        $stmt->execute([$rapId]);
        return $stmt->fetchColumn() ?: null;
    }

    public function obtenerPorId(string $id): ?array {
        $pdo  = self::obtenerConexion();
        $stmt = $pdo->prepare('SELECT * FROM vocabulario WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Normaliza las etiquetas de búsqueda (RF-16): separa por comas, recorta,
     * descarta vacías y repetidas sin distinguir mayúsculas, y limita el total
     * para que quepan en la columna. Devuelve null si no queda ninguna.
     */
    public static function normalizarEtiquetas(?string $crudas): ?string {
        $partes = array_filter(array_map('trim', explode(',', (string) $crudas)), fn($e) => $e !== '');

        $unicas = [];
        foreach ($partes as $etiqueta) {
            $etiqueta = mb_substr($etiqueta, 0, 40);
            $clave = mb_strtolower($etiqueta);
            if (!isset($unicas[$clave])) {
                $unicas[$clave] = $etiqueta;
            }
        }

        $lista = implode(', ', array_slice(array_values($unicas), 0, 15));
        return $lista === '' ? null : mb_substr($lista, 0, 255);
    }

    public function crear(array $datos): bool {
        $pdo  = self::obtenerConexion();
        $stmt = $pdo->prepare(
            'INSERT INTO vocabulario (id, rap_id, termino_en, termino_es, categoria_id, area_clinica_id, transcripcion_ipa, audio_url, imagen_url, oracion_ejemplo, traduccion_ejemplo, nivel_dificultad, etiquetas, activo)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)'
        );
        return $stmt->execute([
            $datos['id'],
            $datos['rap_id'],
            $datos['termino_en'],
            $datos['termino_es'],
            $datos['categoria_id'] ?: null,
            $datos['area_clinica_id'] ?: null,
            $datos['transcripcion_ipa'] ?: null,
            $datos['audio_url'] ?: null,
            $datos['imagen_url'] ?: null,
            $datos['oracion_ejemplo'] ?: null,
            ($datos['traduccion_ejemplo'] ?? '') ?: null,
            $datos['nivel_dificultad'] ?: null,
            self::normalizarEtiquetas($datos['etiquetas'] ?? null)
        ]);
    }

    public function actualizar(string $id, array $datos): bool {
        $pdo  = self::obtenerConexion();
        
        // Si no se envían URLs nuevas, no sobreescribimos las existentes
        // Esto se manejará desde el controlador actualizando el array con la url actual si no hay nueva
        $stmt = $pdo->prepare(
            'UPDATE vocabulario SET 
                termino_en = ?, 
                termino_es = ?, 
                categoria_id = ?, 
                area_clinica_id = ?, 
                transcripcion_ipa = ?, 
                audio_url = ?, 
                imagen_url = ?, 
                oracion_ejemplo = ?,
                traduccion_ejemplo = ?,
                nivel_dificultad = ?,
                etiquetas = ?
             WHERE id = ?'
        );
        return $stmt->execute([
            $datos['termino_en'],
            $datos['termino_es'],
            $datos['categoria_id'] ?: null,
            $datos['area_clinica_id'] ?: null,
            $datos['transcripcion_ipa'] ?: null,
            $datos['audio_url'] ?: null,
            $datos['imagen_url'] ?: null,
            $datos['oracion_ejemplo'] ?: null,
            ($datos['traduccion_ejemplo'] ?? '') ?: null,
            $datos['nivel_dificultad'] ?: null,
            self::normalizarEtiquetas($datos['etiquetas'] ?? null),
            $id
        ]);
    }

    public function toggleActivo(string $id): bool {
        $pdo  = self::obtenerConexion();
        $stmt = $pdo->prepare('UPDATE vocabulario SET activo = IF(activo = 1, 0, 1) WHERE id = ?');
        return $stmt->execute([$id]);
    }
}
