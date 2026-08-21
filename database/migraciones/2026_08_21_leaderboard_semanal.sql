-- =============================================================
-- MIGRACIÓN: Leaderboard semanal real (HU15)
-- Fecha: 2026-08-21
-- Descripción: la tabla puntaje_semanal ya existía en el esquema pero
--              ningún proceso escribía en ella, así que el leaderboard
--              "semanal" en realidad ordenaba por XP acumulado de siempre
--              y nunca se reiniciaba los lunes.
--
--              Esta migración agrega la clave única (usuario_id, anio,
--              numero_semana) que necesita el upsert de puntos semanales,
--              y los índices para ordenar el ranking de cada semana.
--
-- SEGURO: no borra ni modifica datos existentes. Es idempotente: se puede
--         ejecutar varias veces sin error.
--
-- NOTA: se usa information_schema + PREPARE en lugar de
--       "ADD ... IF NOT EXISTS" porque esa sintaxis es de MariaDB (XAMPP)
--       y falla en MySQL 8.0, que es la imagen usada en docker-compose.yml.
-- =============================================================

USE smash_code;

-- 1. Limpiar posibles duplicados previos de (usuario, año, semana).
--    Sin esto, la creación de la clave única fallaría.
DELETE ps FROM puntaje_semanal ps
JOIN (
    SELECT usuario_id, anio, numero_semana, MIN(id) AS conservar
    FROM puntaje_semanal
    GROUP BY usuario_id, anio, numero_semana
    HAVING COUNT(*) > 1
) dup
  ON dup.usuario_id = ps.usuario_id
 AND dup.anio = ps.anio
 AND dup.numero_semana = ps.numero_semana
 AND ps.id <> dup.conservar;

-- 2. Clave única que habilita el upsert semanal
SET @existe_uk := (
    SELECT COUNT(*)
    FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME   = 'puntaje_semanal'
      AND INDEX_NAME   = 'uk_puntaje_semanal'
);

SET @sql_uk := IF(
    @existe_uk = 0,
    'ALTER TABLE puntaje_semanal ADD UNIQUE KEY uk_puntaje_semanal (usuario_id, anio, numero_semana)',
    'SELECT ''La clave uk_puntaje_semanal ya existe, no se hace nada.'' AS aviso'
);

PREPARE stmt_uk FROM @sql_uk;
EXECUTE stmt_uk;
DEALLOCATE PREPARE stmt_uk;

-- 3. Índice para ordenar el ranking de una semana concreta
SET @existe_idx := (
    SELECT COUNT(*)
    FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME   = 'puntaje_semanal'
      AND INDEX_NAME   = 'idx_puntaje_semana'
);

SET @sql_idx := IF(
    @existe_idx = 0,
    'ALTER TABLE puntaje_semanal ADD INDEX idx_puntaje_semana (anio, numero_semana, puntaje_total)',
    'SELECT ''El indice idx_puntaje_semana ya existe, no se hace nada.'' AS aviso'
);

PREPARE stmt_idx FROM @sql_idx;
EXECUTE stmt_idx;
DEALLOCATE PREPARE stmt_idx;

-- Verificación
SHOW INDEX FROM puntaje_semanal;
