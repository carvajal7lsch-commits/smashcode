-- =============================================================
-- MIGRACIÓN: Rondas de intentos del quiz y borrado lógico de preguntas (HU22)
-- Fecha: 2026-09-15
-- Descripción:
--   1. progreso.ronda_quiz_desde: cuándo empezó la ronda de intentos del quiz.
--      Una ronda empieza al terminar la práctica (Momento 3). Al agotar los
--      intentos reprobados de la ronda (quiz.max_intentos), el quiz se bloquea
--      hasta repasar el RAP. Quien ya aprobó no tiene límite (HU14).
--   2. pregunta.activo: borrar una pregunta desde el panel ya no la elimina de
--      la base; se oculta y se conserva el historial de respuestas.
--   3. Da una ronda nueva a quien ya podía presentar el quiz y no lo ha
--      aprobado, para que nadie quede bloqueado por intentos anteriores a este
--      cambio.
--
-- SEGURO: solo agrega columnas donde faltan (todas las preguntas quedan
--         activas) y solo escribe ronda_quiz_desde donde está vacía.
-- IDEMPOTENTE: information_schema + PREPARE en lugar de "ADD COLUMN IF NOT
--              EXISTS" (MariaDB), y el paso 3 solo toca filas con la ronda
--              vacía, así que no reinicia rondas en cada despliegue.
-- =============================================================

SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;
USE smash_code;

-- 1. progreso.ronda_quiz_desde
SET @existe_ronda := (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'progreso' AND COLUMN_NAME = 'ronda_quiz_desde'
);
SET @sql_ronda := IF(
    @existe_ronda = 0,
    'ALTER TABLE progreso ADD COLUMN ronda_quiz_desde DATETIME NULL',
    'SELECT ''progreso ya tiene ronda_quiz_desde: no se hace nada.'' AS aviso'
);
PREPARE stmt_ronda FROM @sql_ronda;
EXECUTE stmt_ronda;
DEALLOCATE PREPARE stmt_ronda;

-- 2. pregunta.activo
SET @existe_activo := (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'pregunta' AND COLUMN_NAME = 'activo'
);
SET @sql_activo := IF(
    @existe_activo = 0,
    'ALTER TABLE pregunta ADD COLUMN activo TINYINT(1) NOT NULL DEFAULT 1',
    'SELECT ''pregunta ya tiene la columna activo: no se hace nada.'' AS aviso'
);
PREPARE stmt_activo FROM @sql_activo;
EXECUTE stmt_activo;
DEALLOCATE PREPARE stmt_activo;

-- 3. Ronda inicial para quien ya llegó al quiz sin aprobarlo
UPDATE progreso
SET ronda_quiz_desde = NOW()
WHERE ronda_quiz_desde IS NULL
  AND completado = 0
  AND porcentaje >= 75;

-- Verificación
SELECT TABLE_NAME AS tabla, COLUMN_NAME AS columna, COLUMN_TYPE AS tipo
FROM information_schema.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
  AND ((TABLE_NAME = 'progreso' AND COLUMN_NAME = 'ronda_quiz_desde')
    OR (TABLE_NAME = 'pregunta' AND COLUMN_NAME = 'activo'));
