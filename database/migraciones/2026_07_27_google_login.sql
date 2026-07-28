-- =============================================================
-- Migración: Login con Google (OAuth 2.0)
-- Fecha: 2026-07-27
-- Descripción: Agrega la columna google_id (UNIQUE) a usuarios y vuelve
--              la columna contrasena NULLABLE, porque las cuentas creadas
--              vía Google no tienen contraseña local.
--
-- INSTRUCCIONES: Ejecutar este script en tu base de datos local
--                smash_code antes de probar la rama feature/login-google.
--
-- SEGURO: No borra ni modifica datos existentes. La adición de google_id es
--         idempotente (se puede ejecutar varias veces sin error) y el MODIFY
--         de contrasena solo relaja la restricción NOT NULL.
--
-- NOTA: se usa information_schema + PREPARE en lugar de
--       "ADD COLUMN IF NOT EXISTS" porque esa sintaxis es de MariaDB (XAMPP)
--       y falla en MySQL 8.0, que es la imagen usada en docker-compose.yml.
-- =============================================================

USE smash_code;

-- 1. Agregar google_id solo si aún no existe
SET @existe_google_id := (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME   = 'usuarios'
      AND COLUMN_NAME  = 'google_id'
);

SET @sql_google_id := IF(
    @existe_google_id = 0,
    'ALTER TABLE usuarios ADD COLUMN google_id VARCHAR(255) NULL UNIQUE AFTER correo',
    'SELECT ''La columna google_id ya existe, no se hace nada.'' AS aviso'
);

PREPARE stmt_google_id FROM @sql_google_id;
EXECUTE stmt_google_id;
DEALLOCATE PREPARE stmt_google_id;

-- 2. Permitir contraseña nula (usuarios que solo entran con Google)
ALTER TABLE usuarios
    MODIFY COLUMN contrasena VARCHAR(255) NULL COMMENT 'hash bcrypt; NULL si la cuenta solo usa Google';

-- Verificación: contrasena debe aparecer con Null = YES y google_id con Key = UNI
DESCRIBE usuarios;
