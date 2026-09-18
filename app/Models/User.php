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
        $stmt = $pdo->prepare('SELECT id, nombre_completo, contrasena, rol, activo, bloqueado, intentos_fallidos, debe_cambiar_clave, correo_verificado FROM usuarios WHERE correo = ? AND eliminado = 0 LIMIT 1');
        $stmt->execute([$correo]);
        $usuario = $stmt->fetch();
        return $usuario ?: null;
    }

    /**
     * Busca un usuario por su ID único (incluyendo programa SENA, ficha y metadatos).
     */
    public function obtenerPorId(string $id): ?array {
        $pdo = self::obtenerConexion();
        $stmt = $pdo->prepare(
            'SELECT u.id, u.nombre_completo, u.xp_puntos, u.nivel_perfil, u.rol, u.correo, 
                    u.ficha_sena, u.programa_id, u.debe_cambiar_clave, u.creado_en,
                    p.nombre AS programa_nombre
             FROM usuarios u
             LEFT JOIN programa_formacion p ON p.id = u.programa_id
             WHERE u.id = ? LIMIT 1'
        );
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

    /* --------------------------------------------------------
     * Activación de cuenta por correo (RF-01)
     * -------------------------------------------------------- */

    /**
     * Emite un token de activación y anula los anteriores del mismo usuario,
     * para que reenviar el correo invalide el enlace viejo.
     */
    public function crearTokenActivacion(string $usuarioId, string $token, string $expiraEn): bool {
        $pdo = self::obtenerConexion();
        $pdo->prepare('UPDATE token_activacion SET usado = 1 WHERE usuario_id = ?')->execute([$usuarioId]);
        $stmt = $pdo->prepare('INSERT INTO token_activacion (id, usuario_id, token, expira_en) VALUES (?, ?, ?, ?)');
        return $stmt->execute([generarUUID(), $usuarioId, $token, $expiraEn]);
    }

    /**
     * Marca la cuenta como verificada y consume el token, en una sola
     * transacción: un enlace que no habilita la cuenta no debe gastarse.
     * Devuelve false si el token no existe, ya se usó o venció.
     */
    public function activarCuenta(string $token): bool {
        $pdo = self::obtenerConexion();
        try {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare('SELECT usuario_id FROM token_activacion WHERE token = ? AND usado = 0 AND expira_en > NOW() LIMIT 1');
            $stmt->execute([$token]);
            $usuarioId = $stmt->fetchColumn();
            if (!$usuarioId) {
                $pdo->rollBack();
                return false;
            }
            $pdo->prepare('UPDATE usuarios SET correo_verificado = 1 WHERE id = ?')->execute([$usuarioId]);
            $pdo->prepare('UPDATE token_activacion SET usado = 1 WHERE token = ?')->execute([$token]);
            $pdo->commit();
            return true;
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            error_log('[Activacion] ' . $e->getMessage());
            return false;
        }
    }

    /** Deja la cuenta utilizable sin pasar por el correo (ver AuthController). */
    public function marcarCorreoVerificado(string $usuarioId): bool {
        $pdo = self::obtenerConexion();
        return $pdo->prepare('UPDATE usuarios SET correo_verificado = 1 WHERE id = ?')->execute([$usuarioId]);
    }

    /* --------------------------------------------------------
     * Inicio de sesión con Google (OAuth 2.0)
     * -------------------------------------------------------- */

    /**
     * Busca un usuario por su identificador de Google (el claim `sub`).
     */
    public function obtenerPorGoogleId(string $googleId): ?array {
        $pdo = self::obtenerConexion();
        $stmt = $pdo->prepare('SELECT id, nombre_completo, correo, rol, activo, bloqueado, eliminado, ficha_sena, programa_id, google_id, debe_cambiar_clave FROM usuarios WHERE google_id = ? LIMIT 1');
        $stmt->execute([$googleId]);
        $usuario = $stmt->fetch();
        return $usuario ?: null;
    }

    /**
     * Busca un usuario por correo trayendo los campos que necesita el flujo de Google
     * (ficha_sena y programa_id para HU16, eliminado para no revivir cuentas borradas).
     * Se mantiene aparte de obtenerPorCorreo() porque ese lo usa el login por contraseña.
     */
    public function obtenerPorCorreoParaGoogle(string $correo): ?array {
        $pdo = self::obtenerConexion();
        $stmt = $pdo->prepare('SELECT id, nombre_completo, correo, rol, activo, bloqueado, eliminado, ficha_sena, programa_id, google_id, debe_cambiar_clave FROM usuarios WHERE correo = ? LIMIT 1');
        $stmt->execute([$correo]);
        $usuario = $stmt->fetch();
        return $usuario ?: null;
    }

    /**
     * Vincula una cuenta ya existente con su identificador de Google.
     * El correo de Google llega verificado, así que también marca correo_verificado.
     */
    public function vincularGoogleId(string $id, string $googleId): bool {
        $pdo = self::obtenerConexion();
        $stmt = $pdo->prepare('UPDATE usuarios SET google_id = ?, correo_verificado = 1 WHERE id = ?');
        return $stmt->execute([$googleId, $id]);
    }

    /**
     * Registra un aprendiz autenticado con Google, sin contraseña local.
     * Requiere la migración 2026_07_27_google_login.sql (contrasena NULLABLE + google_id).
     */
    public function registrarConGoogle(string $id, string $nombre, string $correo, string $googleId): bool {
        $pdo = self::obtenerConexion();
        $stmt = $pdo->prepare('INSERT INTO usuarios (id, nombre_completo, correo, contrasena, google_id, rol, correo_verificado) VALUES (?, ?, ?, NULL, ?, "aprendiz", 1)');
        return $stmt->execute([$id, $nombre, $correo, $googleId]);
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
     * Actualiza la ficha SENA y el programa de formación del aprendiz (HU16).
     * Lo necesitan las cuentas creadas con Google, que nacen sin estos datos.
     */
    public function actualizarFichaYPrograma(string $id, string $fichaSena, string $programaId): bool {
        $pdo  = self::obtenerConexion();
        $stmt = $pdo->prepare('UPDATE usuarios SET ficha_sena = ?, programa_id = ? WHERE id = ?');
        return $stmt->execute([$fichaSena, $programaId, $id]);
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
     * Incrementa los puntos XP de un usuario y recalcula su nivel de perfil dinámicamente.
     */
    public function actualizarXP(string $id, int $puntos): bool {
        $pdo = self::obtenerConexion();
        $propia = !$pdo->inTransaction();
        if ($propia) $pdo->beginTransaction();
        try {
            // Obtener XP actuales
            $stmt = $pdo->prepare('SELECT xp_puntos, nivel_perfil FROM usuarios WHERE id = ? FOR UPDATE');
            $stmt->execute([$id]);
            $user = $stmt->fetch();
            if (!$user) {
                if ($propia) $pdo->rollBack();
                return false;
            }

            // Obtener valor configurable de XP por nivel
            $gamConfig = new GamificacionConfig();
            $xpPorNivel = $gamConfig->obtenerValor('xp_por_nivel', 500);
            if ($xpPorNivel <= 0) $xpPorNivel = 500;

            $nuevoXp = (int)$user['xp_puntos'] + $puntos;
            $nuevoNivel = (int)floor($nuevoXp / $xpPorNivel) + 1;

            $stmtUpdate = $pdo->prepare('UPDATE usuarios SET xp_puntos = ?, nivel_perfil = ? WHERE id = ?');
            $stmtUpdate->execute([$nuevoXp, $nuevoNivel, $id]);

            // HU15: el mismo XP alimenta el marcador de la semana en curso
            $this->sumarPuntajeSemanal($pdo, $id, $puntos);

            // HU15: quien llama necesita saber si hubo ascenso para poder avisarlo
            $nivelAnterior = (int)floor((int)$user['xp_puntos'] / $xpPorNivel) + 1;
            $this->ultimoAscensoNivel = ($nuevoNivel > $nivelAnterior) ? $nuevoNivel : 0;

            if ($propia) $pdo->commit();
            return true;
        } catch (Exception $e) {
            if (!$propia) throw $e;
            $pdo->rollBack();
            error_log('[User Model] Error en actualizarXP: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Nivel al que ascendió el usuario en la última llamada a actualizarXP(),
     * o 0 si no hubo ascenso. Lo consulta el controlador para avisar en pantalla.
     */
    private int $ultimoAscensoNivel = 0;

    /**
     * Devuelve el nivel recién alcanzado (0 si el último actualizarXP() no subió de nivel).
     */
    public function obtenerUltimoAscensoNivel(): int {
        return $this->ultimoAscensoNivel;
    }

    /**
     * Acumula puntos en la fila de la semana en curso (HU15).
     *
     * La semana se calcula con el calendario ISO (date('o') y date('W')), que
     * empieza en lunes: al cambiar de semana se crea una fila nueva y el
     * ranking arranca de cero sin necesidad de ninguna tarea programada.
     *
     * Requiere la migración 2026_08_21_leaderboard_semanal.sql, que agrega la
     * clave única (usuario_id, anio, numero_semana) sobre la que se hace upsert.
     */
    private function sumarPuntajeSemanal(PDO $pdo, string $usuarioId, int $puntos): void {
        if ($puntos === 0) {
            return;
        }

        $stmt = $pdo->prepare(
            'INSERT INTO puntaje_semanal (id, usuario_id, numero_semana, anio, puntaje_total)
             VALUES (?, ?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE puntaje_total = puntaje_total + VALUES(puntaje_total)'
        );
        $stmt->execute([generarUUID(), $usuarioId, (int)date('W'), (int)date('o'), $puntos]);
    }

    /**
     * Calcula la escala de rango clínico oficial (HU15) basada en los puntos XP acumulados.
     * Escala: Novato Clínico (Nivel 1) → Intermedio Clínico (Nivel 2) → Avanzado Clínico (Nivel 3) → Experto Clínico (Nivel 4+)
     */
    public function calcularRangoClinico(int $xp, int $xpPorNivel = 500): array {
        if ($xpPorNivel <= 0) $xpPorNivel = 500;
        
        $nivel = (int)floor($xp / $xpPorNivel) + 1;
        $xpSiguienteNivel = $nivel * $xpPorNivel;
        $xpBaseNivel = ($nivel - 1) * $xpPorNivel;
        $xpEnEsteNivel = $xp - $xpBaseNivel;
        $porcentaje = $xpPorNivel > 0 ? min(100, max(0, round(($xpEnEsteNivel / $xpPorNivel) * 100))) : 100;
        $xpFaltantes = max(0, $xpSiguienteNivel - $xp);

        switch ($nivel) {
            case 1:
                $rangoNombre = 'Novato Clínico';
                $rangoIcono = 'fa-shield-heart';
                $rangoColor = 'var(--azul)';
                break;
            case 2:
                $rangoNombre = 'Intermedio Clínico';
                $rangoIcono = 'fa-stethoscope';
                $rangoColor = 'var(--verde)';
                break;
            case 3:
                $rangoNombre = 'Avanzado Clínico';
                $rangoIcono = 'fa-user-doctor';
                $rangoColor = 'var(--naranja)';
                break;
            default:
                $rangoNombre = 'Experto Clínico';
                $rangoIcono = 'fa-crown';
                $rangoColor = 'var(--morado)';
                break;
        }

        return [
            'nivel' => $nivel,
            'rango_nombre' => $rangoNombre,
            'rango_icono' => $rangoIcono,
            'rango_color' => $rangoColor,
            'xp_actual' => $xp,
            'xp_base_nivel' => $xpBaseNivel,
            'xp_siguiente_nivel' => $xpSiguienteNivel,
            'xp_faltantes' => $xpFaltantes,
            'porcentaje' => $porcentaje
        ];
    }

    /**
     * Calcula la racha actual de días activos consecutivos de estudio (HU05/HU15).
     */
    public function calcularRachaDias(string $usuarioId): int {
        $pdo = self::obtenerConexion();
        $fechas = $this->obtenerHeatmapActividad($usuarioId);
        if (empty($fechas)) {
            return 0;
        }

        // Fechas únicas ordenadas descendentemente
        $fechas = array_unique($fechas);
        rsort($fechas);

        $hoy = date('Y-m-d');
        $ayer = date('Y-m-d', strtotime('-1 day'));

        // Si no practicó ni hoy ni ayer, la racha activa es 0
        if (!in_array($hoy, $fechas) && !in_array($ayer, $fechas)) {
            return 0;
        }

        $racha = 0;
        $fechaCursor = in_array($hoy, $fechas) ? $hoy : $ayer;

        while (in_array($fechaCursor, $fechas)) {
            $racha++;
            $fechaCursor = date('Y-m-d', strtotime($fechaCursor . ' -1 day'));
        }

        return $racha;
    }

    /**
     * Obtiene el historial de intentos de quizzes de un usuario.
     */
    public function obtenerHistorialQuizzes(string $usuarioId): array {
        $pdo = self::obtenerConexion();
        $stmt = $pdo->prepare(
            'SELECT i.id, i.puntaje, i.aprobado, i.numero_intento, i.duracion_seg, i.creado_en, 
                    q.puntaje_minimo,
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
     * Ranking de la SEMANA EN CURSO entre los aprendices del mismo programa (HU15).
     *
     * Antes esta consulta ordenaba por xp_puntos acumulado, así que el
     * "leaderboard semanal" en realidad era un histórico que nunca se
     * reiniciaba. Ahora lee puntaje_semanal, cuya fila se crea por semana
     * ISO: al llegar el lunes cambia la clave y el marcador arranca en cero.
     *
     * Se usa LEFT JOIN a propósito para que los compañeros de ficha que aún
     * no han sumado esta semana sigan apareciendo con 0 y el aprendiz vea a
     * todo su grupo, no solo a quienes ya practicaron.
     */
    public function obtenerLeaderboardSemanal(?string $programaId): array {
        if (!$programaId) return [];
        $pdo = self::obtenerConexion();
        $stmt = $pdo->prepare(
            'SELECT u.id, u.nombre_completo, u.xp_puntos, u.nivel_perfil,
                    COALESCE(ps.puntaje_total, 0) AS xp_semana
             FROM usuarios u
             LEFT JOIN puntaje_semanal ps
                    ON ps.usuario_id = u.id AND ps.anio = ? AND ps.numero_semana = ?
             WHERE u.programa_id = ? AND u.rol = "aprendiz" AND u.eliminado = 0 AND u.activo = 1
             ORDER BY xp_semana DESC, u.xp_puntos DESC
             LIMIT 15'
        );
        $stmt->execute([(int)date('o'), (int)date('W'), $programaId]);
        return $stmt->fetchAll();
    }

    /**
     * Fecha del lunes en que arrancó la semana en curso, para poder decirle al
     * aprendiz desde cuándo cuenta el marcador (HU15).
     */
    public function obtenerInicioSemana(): string {
        return date('Y-m-d', strtotime('monday this week'));
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

