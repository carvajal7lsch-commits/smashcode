<?php
namespace App\Models;

use App\Core\Model;

class ValidacionUsuario extends Model {
    public static function entrada($valor): string {
        return limpiar(is_string($valor) ? $valor : '');
    }

    /**
     * Valida los campos comunes de usuario (nombre, correo, rol, ficha SENA).
     * Soporta preservación de fichas históricas para cuentas preexistentes.
     */
    public static function errores(string $nombre, string $correo, string $ficha, string $rol, array $datos = [], ?string $fichaActual = null, ?string $programaId = null): array {
        $errores = [];
        foreach (['id', 'nombre_completo', 'correo', 'ficha_sena', 'rol', 'programa_id', 'contrasena'] as $campo) {
            if (isset($datos[$campo]) && !is_string($datos[$campo])) {
                $errores[] = 'El campo ' . $campo . ' no es válido.';
            }
        }
        if ($nombre === '' || mb_strlen($nombre) > 255) {
            $errores[] = 'El nombre es obligatorio y admite hasta 255 caracteres.';
        }
        if (!filter_var($correo, FILTER_VALIDATE_EMAIL) || mb_strlen($correo) > 255) {
            $errores[] = 'Ingresa un correo válido de hasta 255 caracteres.';
        }
        if (!in_array($rol, ['aprendiz', 'instructor', 'admin'], true)) {
            $errores[] = 'Selecciona un rol válido.';
        }

        // Validación de Ficha SENA (W11 / W12)
        $fichaLimpia = trim($ficha);
        $esFichaHistorica = ($fichaActual !== null && $fichaLimpia !== '' && $fichaLimpia === trim($fichaActual));

        if (!$esFichaHistorica) {
            $progId = $programaId ?: ($datos['programa_id'] ?? null);
            $progIdStr = is_string($progId) && $progId !== '' ? $progId : null;

            if ($rol === 'aprendiz') {
                if ($fichaLimpia === '') {
                    $errores[] = 'La ficha SENA es obligatoria para aprendices.';
                } elseif (!preg_match('/^[0-9]+$/', $fichaLimpia)) {
                    $errores[] = 'La ficha SENA debe contener únicamente dígitos numéricos (sin letras ni espacios).';
                } elseif (mb_strlen($fichaLimpia) > 20) {
                    $errores[] = 'La ficha SENA admite hasta 20 dígitos.';
                } elseif (!self::fichaPermitida($fichaLimpia, $progIdStr, $fichaActual)) {
                    $errores[] = 'La ficha SENA no existe en el catálogo activo para el programa seleccionado.';
                }
            } elseif ($fichaLimpia !== '') {
                // Instructor o admin con ficha opcional
                if (!preg_match('/^[0-9]+$/', $fichaLimpia)) {
                    $errores[] = 'La ficha SENA debe contener únicamente dígitos numéricos (sin letras ni espacios).';
                } elseif (mb_strlen($fichaLimpia) > 20) {
                    $errores[] = 'La ficha SENA admite hasta 20 dígitos.';
                } elseif (!self::fichaPermitida($fichaLimpia, $progIdStr, $fichaActual)) {
                    $errores[] = 'La ficha SENA no existe en el catálogo activo.';
                }
            }
        }

        return $errores;
    }

    /**
     * Comprueba si la ficha existe activa en el catálogo y corresponde al programa.
     */
    public static function fichaPermitida(string $codigo, ?string $programaId = null, ?string $actual = null): bool {
        if ($actual !== null && $codigo === $actual) {
            return true;
        }
        $pdo = self::obtenerConexion();
        $sql = 'SELECT f.activo, f.programa_id, p.activo AS prog_activo
                FROM fichas f
                INNER JOIN programa_formacion p ON f.programa_id = p.id
                WHERE f.codigo = ? AND p.eliminado = 0';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$codigo]);
        $ficha = $stmt->fetch();
        if (!$ficha || (int)$ficha['activo'] !== 1 || (int)$ficha['prog_activo'] !== 1) {
            return false;
        }
        if (!empty($programaId) && $ficha['programa_id'] !== $programaId) {
            return false;
        }
        return true;
    }

    /**
     * Comprueba si el programa está activo y no eliminado (o es el actualmente asignado).
     */
    public static function programaPermitido(string $id, ?string $actual = null): bool {
        if ($id === '') return true;
        $stmt = self::obtenerConexion()->prepare('SELECT activo FROM programa_formacion WHERE id=? AND eliminado=0');
        $stmt->execute([$id]);
        $activo = $stmt->fetchColumn();
        return $activo !== false && ((int)$activo === 1 || $id === $actual);
    }
}
