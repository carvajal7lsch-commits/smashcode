-- =============================================================
-- MIGRACIÓN: El módulo es la unidad de progreso
-- Fecha: 2026-09-14
-- Descripción: el mapa muestra un solo camino de 4 momentos por módulo y
--              la página del RAP une el contenido de todos sus RAPs, pero el
--              avance se guardaba solo en el RAP abierto (el primero). En los
--              módulos de dos RAPs el segundo nunca avanzaba: el módulo se
--              quedaba en 50% y el siguiente no se desbloqueaba (umbral 80%).
--
--              AprendizController ahora guarda el avance en todos los RAPs
--              activos del módulo. Esta migración pone al día a los aprendices
--              que ya quedaron atascados:
--
--              1. Quien tiene intentos de ejercicios en un módulo ya pasó los
--                 Momentos 1 y 2, así que sus RAPs de ese módulo quedan al menos
--                 en 50%. Ese avance antes solo se guardaba al salir por
--                 "Volver al Mapa" y muchos lo perdieron.
--              2. Cada RAP activo del módulo toma el mayor avance, el estado de
--                 completado y el mejor puntaje que el aprendiz tenga en el módulo.
--
-- SEGURO: nunca baja un avance (GREATEST) y no borra nada. El tiempo invertido
--         no se copia, para no contarlo dos veces en el perfil (HU05).
-- IDEMPOTENTE: ejecutarla de nuevo no cambia nada.
-- =============================================================

SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;
USE smash_code;

-- -------------------------------------------------------------
-- 1. Aprendices con intentos de ejercicios en un módulo: al menos 50%
-- -------------------------------------------------------------
DROP TEMPORARY TABLE IF EXISTS tmp_modulo_con_ejercicios;

CREATE TEMPORARY TABLE tmp_modulo_con_ejercicios AS
SELECT DISTINCT ie.usuario_id, r.nivel_id
FROM intento_ejercicio ie
JOIN usuarios u  ON u.id = ie.usuario_id AND u.rol = 'aprendiz'
JOIN ejercicio e ON e.id = ie.ejercicio_id
JOIN rap r       ON r.id = e.rap_id;

UPDATE progreso p
JOIN rap r ON r.id = p.rap_id AND r.activo = 1
JOIN tmp_modulo_con_ejercicios t ON t.usuario_id = p.usuario_id AND t.nivel_id = r.nivel_id
SET p.porcentaje = 50.00
WHERE p.porcentaje < 50.00;

INSERT INTO progreso (id, usuario_id, rap_id, porcentaje, completado, ultimo_acceso)
SELECT UUID(), t.usuario_id, r.id, 50.00, 0, NOW()
FROM tmp_modulo_con_ejercicios t
JOIN rap r ON r.nivel_id = t.nivel_id AND r.activo = 1
WHERE NOT EXISTS (
    SELECT 1 FROM progreso p WHERE p.usuario_id = t.usuario_id AND p.rap_id = r.id
);

-- -------------------------------------------------------------
-- 2. Igualar el avance de todos los RAPs activos de cada módulo
-- -------------------------------------------------------------
DROP TEMPORARY TABLE IF EXISTS tmp_avance_modulo;

CREATE TEMPORARY TABLE tmp_avance_modulo AS
SELECT p.usuario_id,
       r.nivel_id,
       MAX(p.porcentaje)         AS porcentaje,
       MAX(p.completado)         AS completado,
       MAX(p.mejor_puntaje_quiz) AS mejor_puntaje_quiz
FROM progreso p
JOIN rap r ON r.id = p.rap_id AND r.activo = 1
GROUP BY p.usuario_id, r.nivel_id;

UPDATE progreso p
JOIN rap r ON r.id = p.rap_id AND r.activo = 1
JOIN tmp_avance_modulo t ON t.usuario_id = p.usuario_id AND t.nivel_id = r.nivel_id
SET p.porcentaje         = GREATEST(p.porcentaje, t.porcentaje),
    p.completado         = GREATEST(p.completado, t.completado),
    p.mejor_puntaje_quiz = GREATEST(p.mejor_puntaje_quiz, t.mejor_puntaje_quiz);

INSERT INTO progreso (id, usuario_id, rap_id, porcentaje, completado, mejor_puntaje_quiz, ultimo_acceso)
SELECT UUID(), t.usuario_id, r.id, t.porcentaje, t.completado, t.mejor_puntaje_quiz, NOW()
FROM tmp_avance_modulo t
JOIN rap r ON r.nivel_id = t.nivel_id AND r.activo = 1
WHERE NOT EXISTS (
    SELECT 1 FROM progreso p WHERE p.usuario_id = t.usuario_id AND p.rap_id = r.id
);

DROP TEMPORARY TABLE IF EXISTS tmp_modulo_con_ejercicios;
DROP TEMPORARY TABLE IF EXISTS tmp_avance_modulo;

-- Verificación: aprendices con un RAP completado y otro del mismo módulo sin completar
SELECT COUNT(DISTINCT p1.usuario_id) AS aprendices_atascados_restantes
FROM progreso p1
JOIN rap r1 ON r1.id = p1.rap_id AND r1.activo = 1
JOIN rap r2 ON r2.nivel_id = r1.nivel_id AND r2.activo = 1 AND r2.id <> r1.id
LEFT JOIN progreso p2 ON p2.rap_id = r2.id AND p2.usuario_id = p1.usuario_id
WHERE p1.completado = 1 AND COALESCE(p2.completado, 0) = 0;
