-- =============================================================
-- MIGRACIÓN: Traducción de la oración de ejemplo del vocabulario (HU19)
-- Fecha: 2026-09-15
-- Descripción: HU19 pide registrar la oración de ejemplo clínico y su
--              traducción. Solo existía la oración.
--
-- SEGURO: solo agrega la columna donde falta, vacía. Las traducciones de las
--         palabras existentes las carga 2026_09_15_vocabulario_traducciones_ejemplos.sql.
-- IDEMPOTENTE: information_schema + PREPARE en lugar de "ADD COLUMN IF NOT
--              EXISTS" (MariaDB), que falla en MySQL 8.0.
-- =============================================================

SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;
USE smash_code;

SET @existe_traduccion := (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'vocabulario' AND COLUMN_NAME = 'traduccion_ejemplo'
);
SET @sql_traduccion := IF(
    @existe_traduccion = 0,
    'ALTER TABLE vocabulario ADD COLUMN traduccion_ejemplo VARCHAR(500) NULL AFTER oracion_ejemplo',
    'SELECT ''vocabulario ya tiene la columna traduccion_ejemplo: no se hace nada.'' AS aviso'
);
PREPARE stmt_traduccion FROM @sql_traduccion;
EXECUTE stmt_traduccion;
DEALLOCATE PREPARE stmt_traduccion;

-- Verificación
SELECT COLUMN_NAME AS columna, COLUMN_TYPE AS tipo
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'vocabulario' AND COLUMN_NAME = 'traduccion_ejemplo';
