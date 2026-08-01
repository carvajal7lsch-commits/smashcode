<?php
namespace App\Models;

use App\Core\Model;
use PDO;
use Exception;

/**
 * User.php
 * Modelo de negocio para la gestión de Usuarios y Tokens de Recuperación.
 * Agrupa toda la lógica e interacciones seguras con la base de datos para la entidad de usuarios.
 */
class User extends Model {

    /**
     * Busca un usuario por su correo electrónico.
     */
    public function obtenerPorCorreo(string $correo): ?array {
        $pdo = self::obtenerConexion();
        $stmt = $pdo->prepare('SELECT id, nombre_completo, contrasena, rol, activo, bloqueado, intentos_fallidos, debe_cambiar_clave FROM usuarios WHERE correo = ? LIMIT 1');
        $stmt->execute([$correo]);
        $usuario = $stmt->fetch();
        return $usuario ?: null;
    }

    /**
     * Busca un usuario por su ID único (incluyendo el flag de primer login).
     */
    public function obtenerPorId(string $id): ?array {
        $pdo = self::obtenerConexion();
        $stmt = $pdo->prepare('SELECT nombre_completo, xp_puntos, nivel_perfil, rol, correo, debe_cambiar_clave FROM usuarios WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $usuario = $stmt->fetch();
        return $usuario ?: null;
    }

    /**
     * Verifica si un correo electrónico ya está registrado en la base de datos.
     */
    public function existeCorreo(string $correo): bool {
        $pdo = self::obtenerConexion();
        $stmt = $pdo->prepare('SELECT id FROM usuarios WHERE correo = ? LIMIT 1');
        $stmt->execute([$correo]);
        return (bool) $stmt->fetch();
    }

    /**
     * Registra un nuevo aprendiz.
     */
    public function registrar(string $id, string $nombre, string $correo, string $hashContrasena, ?string $fichaSena, ?string $programaId): bool {
        $pdo = self::obtenerConexion();
        $stmt = $pdo->prepare('INSERT INTO usuarios (id, nombre_completo, correo, contrasena, ficha_sena, programa_id, rol) VALUES (?, ?, ?, ?, ?, ?, "aprendiz")');
        return $stmt->execute([$id, $nombre, $correo, $hashContrasena, $fichaSena ?: null, $programaId ?: null]);
    }

    /**
     * Actualiza la cantidad de intentos fallidos y el estado de bloqueo de una cuenta.
     */
    public function actualizarIntentosFallidos(string $id, int $intentos, int $bloqueado): bool {
        $pdo = self::obtenerConexion();
        $stmt = $pdo->prepare('UPDATE usuarios SET intentos_fallidos = ?, bloqueado = ? WHERE id = ?');
        return $stmt->execute([$intentos, $bloqueado, $id]);
    }

    /**
     * Resetea el contador de intentos fallidos a cero.
     */
    public function resetearIntentosFallidos(string $id): bool {
        $pdo = self::obtenerConexion();
        $stmt = $pdo->prepare('UPDATE usuarios SET intentos_fallidos = 0 WHERE id = ?');
        return $stmt->execute([$id]);
    }

    /**
     * Invalida (marca como usados) todos los tokens de recuperación de un usuario.
     */
    public function invalidarTokensRecuperacion(string $usuarioId): bool {
        $pdo = self::obtenerConexion();
        $stmt = $pdo->prepare('UPDATE token_recuperacion SET usado = 1 WHERE usuario_id = ?');
        return $stmt->execute([$usuarioId]);
    }

    /**
     * Inserta un nuevo token de recuperación de contraseña.
     */
    public function crearTokenRecuperacion(string $usuarioId, string $token, string $expiraEn): bool {
        $pdo = self::obtenerConexion();
        $stmt = $pdo->prepare('INSERT INTO token_recuperacion (usuario_id, token, expira_en) VALUES (?, ?, ?)');
        return $stmt->execute([$usuarioId, $token, $expiraEn]);
    }

    /**
     * Valida si un token existe, no ha sido usado y no ha expirado.
     */
    public function obtenerTokenValido(string $token): ?array {
        $pdo = self::obtenerConexion();
        $stmt = $pdo->prepare('SELECT usuario_id FROM token_recuperacion WHERE token = ? AND usado = 0 AND expira_en > NOW() LIMIT 1');
        $stmt->execute([$token]);
        $tokenRow = $stmt->fetch();
        return $tokenRow ?: null;
    }

    /**
     * Restablece la contraseña de un usuario, desbloquea la cuenta,
     * reinicia sus intentos fallidos y consume el token de seguridad.
     * Se realiza dentro de una transacción para garantizar integridad.
     */
    public function restablecerContrasena(string $usuarioId, string $hashContrasena, string $token): bool {
        $pdo = self::obtenerConexion();
        $pdo->beginTransaction();
        try {
            // 1. Actualizar contraseña, desbloquear cuenta y verificar correo si era invitación
            $stmt1 = $pdo->prepare('UPDATE usuarios SET contrasena = ?, intentos_fallidos = 0, bloqueado = 0, debe_cambiar_clave = 0, correo_verificado = 1 WHERE id = ?');
            $stmt1->execute([$hashContrasena, $usuarioId]);

            // 2. Marcar token de recuperación como usado
            $stmt2 = $pdo->prepare('UPDATE token_recuperacion SET usado = 1 WHERE token = ?');
            $stmt2->execute([$token]);

            $pdo->commit();
            return true;
        } catch (Exception $e) {
            $pdo->rollBack();
            error_log('[User Model] Error en restablecerContrasena: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * HU09: Actualiza la contraseña de un instructor y limpia el flag debe_cambiar_clave.
     * Se usa después del primer inicio de sesión con credenciales temporales.
     */
    public function actualizarContrasenaYLimpiarFlag(string $usuarioId, string $hashContrasena): bool {
        $pdo  = self::obtenerConexion();
        $stmt = $pdo->prepare(
            'UPDATE usuarios SET contrasena = ?, debe_cambiar_clave = 0, intentos_fallidos = 0, bloqueado = 0 WHERE id = ?'
        );
        return $stmt->execute([$hashContrasena, $usuarioId]);
    }

    /**
     * Actualiza el nombre completo del usuario.
     */
    public function actualizarNombre(string $id, string $nombre): bool {
        $pdo  = self::obtenerConexion();
        $stmt = $pdo->prepare('UPDATE usuarios SET nombre_completo = ? WHERE id = ?');
        return $stmt->execute([$nombre, $id]);
    }

    /**
     * Obtiene solo el hash de la contraseña de un usuario.
     */
    public function obtenerHashContrasena(string $id): string {
        $pdo  = self::obtenerConexion();
        $stmt = $pdo->prepare('SELECT contrasena FROM usuarios WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        return (string)($stmt->fetchColumn() ?: '');
    }

    /**
     * Actualiza únicamente la contraseña del usuario.
     */
    public function actualizarContrasena(string $id, string $hashContrasena): bool {
        $pdo  = self::obtenerConexion();
        $stmt = $pdo->prepare('UPDATE usuarios SET contrasena = ?, intentos_fallidos = 0, bloqueado = 0 WHERE id = ?');
        return $stmt->execute([$hashContrasena, $id]);
    }

    /**
     * Incrementa los puntos XP de un usuario y recalcula su nivel de perfil.
     */
    public function actualizarXP(string $id, int $puntos): bool {
        $pdo = self::obtenerConexion();
        $pdo->beginTransaction();
        try {
            // Obtener XP actuales
            $stmt = $pdo->prepare('SELECT xp_puntos, nivel_perfil FROM usuarios WHERE id = ? FOR UPDATE');
            $stmt->execute([$id]);
            $user = $stmt->fetch();
            if (!$user) {
                $pdo->rollBack();
                return false;
            }

            $nuevoXp = (int)$user['xp_puntos'] + $puntos;
            // Nivel = floor(XP / 500) + 1
            $nuevoNivel = (int)floor($nuevoXp / 500) + 1;

            $stmtUpdate = $pdo->prepare('UPDATE usuarios SET xp_puntos = ?, nivel_perfil = ? WHERE id = ?');
            $stmtUpdate->execute([$nuevoXp, $nuevoNivel, $id]);

            $pdo->commit();
            return true;
        } catch (Exception $e) {
            $pdo->rollBack();
            error_log('[User Model] Error en actualizarXP: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene el historial de intentos de quizzes de un usuario.
     */
    public function obtenerHistorialQuizzes(string $usuarioId): array {
        $pdo = self::obtenerConexion();
        $stmt = $pdo->prepare(
            'SELECT i.id, i.puntaje, i.aprobado, i.numero_intento, i.duracion_seg, i.creado_en, 
                    r.titulo AS rap_titulo, n.nombre AS modulo_nombre, n.orden AS modulo_orden
             FROM intento_quiz i
             JOIN quiz q ON q.id = i.quiz_id
             JOIN rap r ON r.id = q.rap_id
             JOIN nivel n ON n.id = r.nivel_id
             WHERE i.usuario_id = ?
             ORDER BY i.creado_en DESC'
        );
        $stmt->execute([$usuarioId]);
        return $stmt->fetchAll();
    }

    /**
     * Obtiene las insignias ganadas por el usuario.
     */
    public function obtenerInsigniasGanadas(string $usuarioId): array {
        $pdo = self::obtenerConexion();
        $stmt = $pdo->prepare(
            'SELECT i.id, i.nombre, i.descripcion, i.icono_url, iu.ganada_en
             FROM insignia_usuario iu
             JOIN insignia i ON i.id = iu.insignia_id
             WHERE iu.usuario_id = ?
             ORDER BY iu.ganada_en DESC'
        );
        $stmt->execute([$usuarioId]);
        return $stmt->fetchAll();
    }

    /**
     * Obtiene todas las insignias registradas en el sistema.
     */
    public function obtenerTodasInsignias(): array {
        $pdo = self::obtenerConexion();
        $stmt = $pdo->query('SELECT id, nombre, descripcion, icono_url FROM insignia');
        return $stmt->fetchAll();
    }

    /**
     * Otorga una insignia a un usuario (si no la tiene ya).
     */
    public function otorgarInsignia(string $usuarioId, string $insigniaId): bool {
        $pdo = self::obtenerConexion();
        // Verificar si ya la tiene
        $stmtCheck = $pdo->prepare('SELECT 1 FROM insignia_usuario WHERE usuario_id = ? AND insignia_id = ? LIMIT 1');
        $stmtCheck->execute([$usuarioId, $insigniaId]);
        if ($stmtCheck->fetchColumn()) {
            return true;
        }
        $stmtInsert = $pdo->prepare('INSERT INTO insignia_usuario (usuario_id, insignia_id) VALUES (?, ?)');
        return $stmtInsert->execute([$usuarioId, $insigniaId]);
    }

    /**
     * Obtiene el ranking de usuarios del mismo programa para el leaderboard.
     */
    public function obtenerLeaderboardSemanal(?string $programaId): array {
        if (!$programaId) return [];
        $pdo = self::obtenerConexion();
        $stmt = $pdo->prepare(
            'SELECT id, nombre_completo, xp_puntos, nivel_perfil
             FROM usuarios
             WHERE programa_id = ? AND rol = "aprendiz" AND eliminado = 0 AND activo = 1
             ORDER BY xp_puntos DESC
             LIMIT 15'
        );
        $stmt->execute([$programaId]);
        return $stmt->fetchAll();
    }

    /**
     * Obtiene las fechas únicas en las que el usuario ha realizado actividades (quizzes o ejercicios).
     */
    public function obtenerHeatmapActividad(string $usuarioId): array {
        $pdo = self::obtenerConexion();
        $stmt = $pdo->prepare(
            'SELECT DISTINCT DATE(creado_en) AS fecha FROM intento_quiz WHERE usuario_id = ?
             UNION
             SELECT DISTINCT DATE(creado_en) AS fecha FROM intento_ejercicio WHERE usuario_id = ?
             ORDER BY fecha ASC'
        );
        $stmt->execute([$usuarioId, $usuarioId]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}

