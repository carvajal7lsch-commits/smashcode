-- =============================================================
-- MIGRACIÓN: Anotaciones de diálogos y borrado lógico de turnos (HU21)
-- Fecha: 2026-09-15
-- Descripción:
--   1. dialogo.anotaciones: notas pedagógicas del escenario para el panel.
--   2. turno_dialogo.activo: quitar un turno desde el panel lo desactiva en
--      lugar de borrarlo.
--   Algunas bases locales ya tenían estas columnas (se agregaron a mano y
--   nunca llegaron a smash_code.sql ni a una migración); aquí solo se agregan
--   donde faltan.
--
-- SEGURO: solo agrega columnas; todos los turnos quedan activos.
-- IDEMPOTENTE: information_schema + PREPARE en lugar de "ADD COLUMN IF NOT
--              EXISTS" (MariaDB), que falla en MySQL 8.0.
-- =============================================================

SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;
USE smash_code;

-- 1. dialogo.anotaciones
SET @existe_anotaciones := (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'dialogo' AND COLUMN_NAME = 'anotaciones'
);
SET @sql_anotaciones := IF(
    @existe_anotaciones = 0,
    'ALTER TABLE dialogo ADD COLUMN anotaciones VARCHAR(1000) NULL AFTER participantes',
    'SELECT ''dialogo ya tiene la columna anotaciones: no se hace nada.'' AS aviso'
);
PREPARE stmt_anotaciones FROM @sql_anotaciones;
EXECUTE stmt_anotaciones;
DEALLOCATE PREPARE stmt_anotaciones;

-- 2. turno_dialogo.activo
SET @existe_turno_activo := (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'turno_dialogo' AND COLUMN_NAME = 'activo'
);
SET @sql_turno_activo := IF(
    @existe_turno_activo = 0,
    'ALTER TABLE turno_dialogo ADD COLUMN activo TINYINT(1) NOT NULL DEFAULT 1',
    'SELECT ''turno_dialogo ya tiene la columna activo: no se hace nada.'' AS aviso'
);
PREPARE stmt_turno_activo FROM @sql_turno_activo;
EXECUTE stmt_turno_activo;
DEALLOCATE PREPARE stmt_turno_activo;

-- Verificación
SELECT TABLE_NAME AS tabla, COLUMN_NAME AS columna, COLUMN_TYPE AS tipo
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
  AND ((TABLE_NAME = 'dialogo' AND COLUMN_NAME = 'anotaciones')
    OR (TABLE_NAME = 'turno_dialogo' AND COLUMN_NAME = 'activo'));
