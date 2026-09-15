-- =============================================================
-- MIGRACIÓN: Retirar RAPs duplicados sin actividad
-- Fecha: 2026-09-14
-- Descripción: en producción quedaron filas de `rap` repetidas en la misma
--              posición (módulo y orden): una copia del RAP 3 y el RAP 5
--              original vacío, ambas inactivas. Los aprendices no las ven,
--              pero confunden el panel de administración y los seeds, e
--              impiden proteger la tabla con una restricción única.
--
--              Una fila se retira solo si cumple TODO esto:
--                · está inactiva,
--                · hay una fila ACTIVA en su misma posición, que es la que se
--                  conserva,
--                · ningún aprendiz depende de ella: sin progreso, sin intentos
--                  de ejercicio ni de quiz, sin respuestas de quiz y sin
--                  vocabulario marcado como difícil.
--              Se retira con su contenido (vocabulario, diálogos, ejercicios y
--              quiz), que nadie ve. Si ya no quedan posiciones repetidas, se
--              añade la restricción única (nivel_id, orden) para que no vuelva
--              a pasar.
--
-- SEGURO: una fila con cualquier actividad de aprendices no se toca, y una
--         posición sin fila activa tampoco.
-- IDEMPOTENTE: ejecutarla de nuevo no cambia nada.
-- =============================================================

SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;
USE smash_code;

DROP TEMPORARY TABLE IF EXISTS tmp_raps_a_retirar;

CREATE TEMPORARY TABLE tmp_raps_a_retirar AS
SELECT r.id
FROM rap r
WHERE r.activo = 0
  AND EXISTS (
      SELECT 1 FROM rap a
      WHERE a.nivel_id = r.nivel_id AND a.orden = r.orden AND a.activo = 1 AND a.id <> r.id
  )
  AND NOT EXISTS (SELECT 1 FROM progreso p WHERE p.rap_id = r.id)
  AND NOT EXISTS (
      SELECT 1 FROM intento_ejercicio ie JOIN ejercicio e ON e.id = ie.ejercicio_id
      WHERE e.rap_id = r.id
  )
  AND NOT EXISTS (
      SELECT 1 FROM intento_quiz iq JOIN quiz q ON q.id = iq.quiz_id
      WHERE q.rap_id = r.id
  )
  AND NOT EXISTS (
      SELECT 1 FROM respuesta_quiz rq
      JOIN pregunta pr ON pr.id = rq.pregunta_id
      JOIN quiz q ON q.id = pr.quiz_id
      WHERE q.rap_id = r.id
  )
  AND NOT EXISTS (
      SELECT 1 FROM vocabulario_marcado vm JOIN vocabulario v ON v.id = vm.vocabulario_id
      WHERE v.rap_id = r.id
  );

-- Los turnos, las opciones y las preguntas se van en cascada con su diálogo,
-- su ejercicio y su quiz
DELETE FROM dialogo     WHERE rap_id IN (SELECT id FROM tmp_raps_a_retirar);
DELETE FROM ejercicio   WHERE rap_id IN (SELECT id FROM tmp_raps_a_retirar);
DELETE FROM quiz        WHERE rap_id IN (SELECT id FROM tmp_raps_a_retirar);
DELETE FROM vocabulario WHERE rap_id IN (SELECT id FROM tmp_raps_a_retirar);
DELETE FROM rap         WHERE id     IN (SELECT id FROM tmp_raps_a_retirar);

DROP TEMPORARY TABLE IF EXISTS tmp_raps_a_retirar;

-- Restricción única, solo si ya no queda ninguna posición repetida
SET @posiciones_repetidas := (
    SELECT COUNT(*) FROM (
        SELECT nivel_id, orden FROM rap GROUP BY nivel_id, orden HAVING COUNT(*) > 1
    ) repetidas
);

SET @existe_uk := (
    SELECT COUNT(*)
    FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME   = 'rap'
      AND INDEX_NAME   = 'uk_rap_posicion'
);

SET @sql_uk := IF(
    @existe_uk = 0 AND @posiciones_repetidas = 0,
    'ALTER TABLE rap ADD UNIQUE KEY uk_rap_posicion (nivel_id, orden)',
    'SELECT ''La restricción uk_rap_posicion ya existe o aún hay posiciones repetidas: no se hace nada.'' AS aviso'
);

PREPARE stmt_uk FROM @sql_uk;
EXECUTE stmt_uk;
DEALLOCATE PREPARE stmt_uk;

-- Verificación: filas por posición (deberían ser 1)
SELECT n.orden AS modulo, r.orden AS posicion, COUNT(*) AS filas
FROM rap r
JOIN nivel n ON n.id = r.nivel_id
GROUP BY n.orden, r.orden
ORDER BY n.orden, r.orden;
