-- =============================================================
-- MIGRACIÓN: Instrucciones de los ejercicios (HU20)
-- Fecha: 2026-09-15
-- Descripción: HU20 pide que cada ejercicio tenga enunciado, instrucciones,
--              máximo de intentos y puntaje. max_intentos y puntos ya existían;
--              faltaba la columna de instrucciones.
--
-- SEGURO: solo agrega la columna donde falta, vacía. No cambia datos.
-- IDEMPOTENTE: information_schema + PREPARE en lugar de "ADD COLUMN IF NOT
--              EXISTS" (MariaDB), que falla en MySQL 8.0.
-- =============================================================

SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;
USE smash_code;

SET @existe_instrucciones := (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'ejercicio' AND COLUMN_NAME = 'instrucciones'
);
SET @sql_instrucciones := IF(
    @existe_instrucciones = 0,
    'ALTER TABLE ejercicio ADD COLUMN instrucciones VARCHAR(500) NULL AFTER enunciado',
    'SELECT ''ejercicio ya tiene la columna instrucciones: no se hace nada.'' AS aviso'
);
PREPARE stmt_instrucciones FROM @sql_instrucciones;
EXECUTE stmt_instrucciones;
DEALLOCATE PREPARE stmt_instrucciones;

-- Verificación
SELECT COLUMN_NAME AS columna, COLUMN_TYPE AS tipo
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'ejercicio' AND COLUMN_NAME = 'instrucciones';
