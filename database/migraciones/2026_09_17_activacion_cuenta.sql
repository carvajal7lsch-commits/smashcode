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
-- IDEMPOTENTE: registra la aplicación inicial. Repetirla nunca activa cuentas
--              pendientes creadas después, ni instalaciones que ya tenían tokens.
-- =============================================================

SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;
USE smash_code;

SET @activacion_ya_instalada := (SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='token_activacion');

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
CREATE TABLE IF NOT EXISTS migracion_aplicada (
    nombre VARCHAR(191) NOT NULL PRIMARY KEY,
    aplicada_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
START TRANSACTION;
INSERT IGNORE INTO migracion_aplicada(nombre) VALUES ('2026_09_17_activacion_cuenta');
SET @primera_activacion := ROW_COUNT();
UPDATE usuarios SET correo_verificado = 1
WHERE correo_verificado = 0 AND @primera_activacion = 1 AND @activacion_ya_instalada = 0;
COMMIT;

-- Verificación
SELECT
    (SELECT COUNT(*) FROM information_schema.TABLES
      WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'token_activacion') AS tabla_creada,
    (SELECT COUNT(*) FROM usuarios WHERE correo_verificado = 0) AS cuentas_sin_verificar;
