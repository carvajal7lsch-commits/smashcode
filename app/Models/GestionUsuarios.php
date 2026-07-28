<?php
namespace App\Models;

use App\Core\Model;
use PDO;

/**
 * GestionUsuarios.php
 * Modelo para la gestión CRUD completa de usuarios por el administrador (HU04).
 */
class GestionUsuarios extends Model {

    /**
     * Lista usuarios con búsqueda, filtro de rol y paginación.
     * Excluye los eliminados (soft-delete) por defecto.
     */
    public function listar(string $busqueda, string $rol, int $pagina, int $porPagina): array {
        $pdo = self::obtenerConexion();
        $offset = ($pagina - 1) * $porPagina;

        $sql    = "SELECT id, nombre_completo, correo, rol, activo, bloqueado, ficha_sena,
                          xp_puntos, nivel_perfil, creado_en, eliminado
                   FROM usuarios
                   WHERE eliminado = 0";
        $params = [];

        if ($busqueda !== '') {
            $sql .= " AND (nombre_completo LIKE ? OR correo LIKE ?)";
            $params[] = '%' . $busqueda . '%';
            $params[] = '%' . $busqueda . '%';
        }
        if ($rol !== '') {
            $sql .= " AND rol = ?";
            $params[] = $rol;
        }

        $sql .= " ORDER BY creado_en DESC LIMIT ? OFFSET ?";
        $params[] = $porPagina;
        $params[] = $offset;

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Cuenta el total de usuarios que coinciden con los filtros (para la paginación).
     */
    public function contarTotal(string $busqueda, string $rol): int {
        $pdo    = self::obtenerConexion();
        $sql    = "SELECT COUNT(*) FROM usuarios WHERE eliminado = 0";
        $params = [];

        if ($busqueda !== '') {
            $sql .= " AND (nombre_completo LIKE ? OR correo LIKE ?)";
            $params[] = '%' . $busqueda . '%';
            $params[] = '%' . $busqueda . '%';
        }
        if ($rol !== '') {
            $sql .= " AND rol = ?";
            $params[] = $rol;
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Obtiene un usuario completo por su ID (para edición).
     */
    public function obtenerPorId(string $id): ?array {
        $pdo  = self::obtenerConexion();
        $stmt = $pdo->prepare(
            "SELECT id, nombre_completo, correo, rol, activo, bloqueado,
                    ficha_sena, programa_id, xp_puntos, nivel_perfil, creado_en
             FROM usuarios WHERE id = ? AND eliminado = 0 LIMIT 1"
        );
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Verifica si un correo ya existe en la base de datos.
     */
    public function existeCorreo(string $correo): bool {
        $pdo  = self::obtenerConexion();
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE correo = ? LIMIT 1");
        $stmt->execute([$correo]);
        return (bool) $stmt->fetch();
    }

    /**
     * Crea un nuevo usuario en el sistema.
     */
    public function crear(string $id, string $nombre, string $correo, string $hash, string $rol, ?string $ficha, ?string $programaId): bool {
        $pdo  = self::obtenerConexion();
        $stmt = $pdo->prepare(
            "INSERT INTO usuarios (id, nombre_completo, correo, contrasena, rol, ficha_sena, programa_id, activo, correo_verificado)
             VALUES (?, ?, ?, ?, ?, ?, ?, 1, 1)"
        );
        return $stmt->execute([$id, $nombre, $correo, $hash, $rol, $ficha, $programaId]);
    }

    /**
     * Crea una cuenta con credenciales temporales.
     * Establece debe_cambiar_clave = 1 para forzar el cambio en el primer login.
     */
    public function crearConClaveTemporal(string $id, string $nombre, string $correo, string $hash, string $rol, ?string $programaId, ?string $ficha): bool {
        $pdo  = self::obtenerConexion();
        $stmt = $pdo->prepare(
            "INSERT INTO usuarios
             (id, nombre_completo, correo, contrasena, rol, programa_id, ficha_sena, activo, correo_verificado, debe_cambiar_clave)
             VALUES (?, ?, ?, ?, ?, ?, ?, 1, 0, 1)"
        );
        return $stmt->execute([$id, $nombre, $correo, $hash, $rol, $programaId, $ficha]);
    }

    /**
     * Actualiza los datos de un usuario (sin tocar la contraseña).
     */
    public function actualizar(string $id, string $nombre, string $correo, string $rol, ?string $ficha, ?string $programaId): bool {
        $pdo  = self::obtenerConexion();
        $stmt = $pdo->prepare(
            "UPDATE usuarios SET nombre_completo = ?, correo = ?, rol = ?, ficha_sena = ?, programa_id = ? WHERE id = ?"
        );
        return $stmt->execute([$nombre, $correo, $rol, $ficha, $programaId, $id]);
    }

    /**
     * Cambia el estado activo/inactivo del usuario de forma inmediata.
     */
    public function cambiarEstado(string $id, int $nuevoEstado): bool {
        $pdo  = self::obtenerConexion();
        $stmt = $pdo->prepare("UPDATE usuarios SET activo = ?, bloqueado = 0 WHERE id = ?");
        return $stmt->execute([$nuevoEstado, $id]);
    }

    /**
     * Soft-delete: marca el campo 'eliminado = 1' sin borrar físicamente el registro.
     * Preserva todo el historial de interacciones del usuario.
     */
    public function softDelete(string $id): bool {
        $pdo  = self::obtenerConexion();
        $stmt = $pdo->prepare("UPDATE usuarios SET eliminado = 1, activo = 0, correo = CONCAT(correo, '__deleted_', UNIX_TIMESTAMP()) WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Obtiene el log de actividad de un usuario (intentos de quiz, ejercicios, inicio de sesión).
     */
    public function obtenerActividad(string $usuarioId): array {
        $pdo  = self::obtenerConexion();
        // Combina intentos de quiz + intentos de ejercicios como un log cronológico unificado
        $stmt = $pdo->prepare(
            "SELECT 'quiz' AS tipo,
                    iq.creado_en AS fecha,
                    CONCAT('Quiz del RAP: ', r.titulo) AS descripcion,
                    CONCAT(iq.puntaje, '%') AS detalle,
                    IF(iq.aprobado = 1, 'aprobado', 'reprobado') AS estado
             FROM intento_quiz iq
             JOIN quiz q ON q.id = iq.quiz_id
             JOIN rap r  ON r.id = q.rap_id
             WHERE iq.usuario_id = ?

             UNION ALL

             SELECT 'ejercicio' AS tipo,
                    ie.creado_en AS fecha,
                    CONCAT('Ejercicio: ', LEFT(e.enunciado, 60), '...') AS descripcion,
                    CONCAT(ie.numero_intento, ' intento(s)') AS detalle,
                    IF(ie.es_correcto = 1, 'correcto', 'incorrecto') AS estado
             FROM intento_ejercicio ie
             JOIN ejercicio e ON e.id = ie.ejercicio_id
             WHERE ie.usuario_id = ?

             ORDER BY fecha DESC
             LIMIT 50"
        );
        $stmt->execute([$usuarioId, $usuarioId]);
        return $stmt->fetchAll();
    }
}
