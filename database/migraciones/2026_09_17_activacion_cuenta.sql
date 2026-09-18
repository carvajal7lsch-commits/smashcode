-- =============================================================
-- MIGRACIÓN: Activación de cuenta por correo (RF-01)
-- Fecha: 2026-09-17
-- Descripción: RF-01 pide que el auto-registro envíe un correo de confirmación
--              que debe activarse para habilitar la cuenta. La columna
--              usuarios.correo_verificado ya existía pero nunca se consultaba
--              y no había tokens de activación.
--
-- CRÍTICO: el UPDATE final marca como verificadas TODAS las cuentas que ya
--          existen. Sin él, activar la comprobación en el login dejaría fuera
--          a todo el mundo: correo_verificado nace en 0 y hasta hoy solo lo
--          subían a 1 el alta con Google y el restablecimiento de contraseña.
--          La exigencia aplica únicamente de aquí en adelante.
--
-- IDEMPOTENTE: CREATE TABLE IF NOT EXISTS y un UPDATE que solo toca las filas
--              que siguen en 0.
-- =============================================================

SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;
USE smash_code;

CREATE TABLE IF NOT EXISTS token_activacion (
    id         VARCHAR(36)  NOT NULL PRIMARY KEY DEFAULT (UUID()),
    usuario_id VARCHAR(36)  NOT NULL,
    token      VARCHAR(255) NOT NULL UNIQUE,
    expira_en  DATETIME     NOT NULL,
    usado      TINYINT(1)   NOT NULL DEFAULT 0,
    creado_en  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_activacion_usuario (usuario_id),
    CONSTRAINT fk_activacion_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Ninguna cuenta anterior a esta migración debe quedar bloqueada por algo que
-- todavía no se le podía pedir.
UPDATE usuarios SET correo_verificado = 1 WHERE correo_verificado = 0;

-- Verificación
SELECT
    (SELECT COUNT(*) FROM information_schema.TABLES
      WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'token_activacion') AS tabla_creada,
    (SELECT COUNT(*) FROM usuarios WHERE correo_verificado = 0) AS cuentas_sin_verificar;
