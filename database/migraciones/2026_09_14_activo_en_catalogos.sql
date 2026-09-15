-- =============================================================
-- MIGRACIÓN: Columna activo en los catálogos del vocabulario (HU18)
-- Fecha: 2026-09-14
-- Descripción: smash_code.sql define activo en area_clinica y en
--              categoria_vocabulario, pero las bases creadas antes de ese
--              cambio no la tienen: CREATE TABLE IF NOT EXISTS nunca altera
--              una tabla que ya existe, y ninguna migración la añadió.
--
--              Sin la columna, el panel de Catálogos mostraba todo como
--              activo y ocultaba el botón de desactivar, y desactivar un área
--              o una categoría no era posible (HU18).
--
-- SEGURO: solo añade la columna donde falta, con valor 1: todo lo existente
--         queda activo, como se veía hasta ahora. No borra ni cambia datos.
-- IDEMPOTENTE: se usa information_schema + PREPARE en lugar de
--              "ADD COLUMN IF NOT EXISTS", que es de MariaDB y falla en
--              MySQL 8.0 (la imagen de docker-compose.yml).
-- =============================================================

SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;
USE smash_code;

-- 1. area_clinica
SET @existe_activo_area := (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME   = 'area_clinica'
      AND COLUMN_NAME  = 'activo'
);

SET @sql_area := IF(
    @existe_activo_area = 0,
    'ALTER TABLE area_clinica ADD COLUMN activo TINYINT(1) NOT NULL DEFAULT 1',
    'SELECT ''area_clinica ya tiene la columna activo: no se hace nada.'' AS aviso'
);

PREPARE stmt_area FROM @sql_area;
EXECUTE stmt_area;
DEALLOCATE PREPARE stmt_area;

-- 2. categoria_vocabulario
SET @existe_activo_categoria := (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME   = 'categoria_vocabulario'
      AND COLUMN_NAME  = 'activo'
);

SET @sql_categoria := IF(
    @existe_activo_categoria = 0,
    'ALTER TABLE categoria_vocabulario ADD COLUMN activo TINYINT(1) NOT NULL DEFAULT 1',
    'SELECT ''categoria_vocabulario ya tiene la columna activo: no se hace nada.'' AS aviso'
);

PREPARE stmt_categoria FROM @sql_categoria;
EXECUTE stmt_categoria;
DEALLOCATE PREPARE stmt_categoria;

-- Verificación
SELECT TABLE_NAME AS tabla, COLUMN_NAME AS columna, COLUMN_DEFAULT AS valor_por_defecto
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME IN ('area_clinica', 'categoria_vocabulario')
  AND COLUMN_NAME = 'activo';
