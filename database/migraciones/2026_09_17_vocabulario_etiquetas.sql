-- =============================================================
-- MIGRACIÓN: Etiquetas de búsqueda del vocabulario (RF-16)
-- Fecha: 2026-09-17
-- Descripción: RF-16 exige registrar cada palabra con etiquetas de búsqueda
--              además de término, categoría, área clínica y dificultad. El
--              campo no existía.
--
-- Se guarda como lista separada por comas en una sola columna, no en tabla
-- aparte: las etiquetas solo se usan para filtrar el glosario y el listado del
-- administrador, nunca se agregan ni se cuentan, así que normalizarlas no
-- aportaría nada y sí obligaría a un JOIN en cada búsqueda.
--
-- SEGURO: solo agrega la columna donde falta, vacía. Las palabras existentes
--         quedan sin etiquetas y se siguen encontrando por término en inglés,
--         español y área clínica, como hasta ahora.
-- IDEMPOTENTE: information_schema + PREPARE en lugar de "ADD COLUMN IF NOT
--              EXISTS" (MariaDB), que falla en MySQL 8.0.
-- =============================================================

SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;
USE smash_code;

SET @existe_etiquetas := (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'vocabulario' AND COLUMN_NAME = 'etiquetas'
);
SET @sql_etiquetas := IF(
    @existe_etiquetas = 0,
    'ALTER TABLE vocabulario ADD COLUMN etiquetas VARCHAR(255) NULL AFTER nivel_dificultad',
    'SELECT ''vocabulario ya tiene la columna etiquetas: no se hace nada.'' AS aviso'
);
PREPARE stmt_etiquetas FROM @sql_etiquetas;
EXECUTE stmt_etiquetas;
DEALLOCATE PREPARE stmt_etiquetas;

-- Verificación
SELECT COLUMN_NAME AS columna, COLUMN_TYPE AS tipo
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'vocabulario' AND COLUMN_NAME = 'etiquetas';
