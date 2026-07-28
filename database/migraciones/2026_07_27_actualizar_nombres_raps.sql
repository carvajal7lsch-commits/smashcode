-- =============================================================
-- Migración: Actualizar nombres de RAPs según contenidos.md
-- Fecha: 2026-07-27
-- Descripción: Cambia los títulos genéricos de los RAPs existentes
--              a los nombres definidos en la estructura de contenidos,
--              evitando tildes y guiones largos (em-dash) para
--              evitar problemas de encoding en el servidor.
-- =============================================================

USE smash_code;

UPDATE rap r
JOIN nivel n ON r.nivel_id = n.id
SET r.titulo = CONCAT('RAP ', n.orden);

-- Verificación rápida
SELECT r.id, n.orden, r.titulo 
FROM rap r 
JOIN nivel n ON r.nivel_id = n.id 
ORDER BY n.orden;
