-- =============================================================
-- MIGRACIÓN: Reubicar los 6 RAPs existentes a los 4 Módulos
-- Fecha: 2026-08-21
-- Motivo: la migración 2026_08_10_fix_vps_niveles_raps.sql hace
--         DELETE FROM rap sobre los niveles 5 y 6, pero las FKs de
--         dialogo, ejercicio, vocabulario, quiz y progreso son
--         RESTRICT: si esos RAPs ya tienen contenido, el DELETE
--         falla (ERROR 1451) y deja la migración a medias.
--         Esta versión MUEVE los RAPs conservando su contenido.
-- Idempotente: solo actúa si aún existen niveles con orden > 4.
-- =============================================================
SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;
USE smash_code;

-- ¿La base aún está en la estructura vieja de 6 niveles?
SET @ESTRUCTURA_VIEJA = (SELECT COUNT(*) FROM nivel WHERE orden > 4);

-- IDs de los 4 módulos destino
SET @NIV1 = (SELECT id FROM nivel WHERE orden = 1 LIMIT 1);
SET @NIV2 = (SELECT id FROM nivel WHERE orden = 2 LIMIT 1);
SET @NIV3 = (SELECT id FROM nivel WHERE orden = 3 LIMIT 1);
SET @NIV4 = (SELECT id FROM nivel WHERE orden = 4 LIMIT 1);

-- Capturar los RAPs por su nivel ACTUAL antes de mover nada
SET @RAP1 = (SELECT r.id FROM rap r JOIN nivel n ON r.nivel_id = n.id WHERE n.orden = 1 ORDER BY r.orden LIMIT 1);
SET @RAP2 = (SELECT r.id FROM rap r JOIN nivel n ON r.nivel_id = n.id WHERE n.orden = 2 ORDER BY r.orden LIMIT 1);
SET @RAP3 = (SELECT r.id FROM rap r JOIN nivel n ON r.nivel_id = n.id WHERE n.orden = 3 ORDER BY r.orden LIMIT 1);
SET @RAP4 = (SELECT r.id FROM rap r JOIN nivel n ON r.nivel_id = n.id WHERE n.orden = 4 ORDER BY r.orden LIMIT 1);
SET @RAP5 = (SELECT r.id FROM rap r JOIN nivel n ON r.nivel_id = n.id WHERE n.orden = 5 ORDER BY r.orden LIMIT 1);
SET @RAP6 = (SELECT r.id FROM rap r JOIN nivel n ON r.nivel_id = n.id WHERE n.orden = 6 ORDER BY r.orden LIMIT 1);

-- Reubicar conservando el contenido asociado (dialogo, ejercicio, vocabulario, quiz, progreso)
UPDATE rap SET nivel_id = @NIV1, orden = 1, activo = 1,
    titulo = 'RAP 1: Presentaciones e Información Personal'
    WHERE @ESTRUCTURA_VIEJA > 0 AND id = @RAP1;
UPDATE rap SET nivel_id = @NIV2, orden = 1, activo = 1,
    titulo = 'RAP 2: Historia del Paciente y Pasado Simple'
    WHERE @ESTRUCTURA_VIEJA > 0 AND id = @RAP2;
UPDATE rap SET nivel_id = @NIV2, orden = 2, activo = 1,
    titulo = 'RAP 3: Entorno Hospitalario y Estado Actual'
    WHERE @ESTRUCTURA_VIEJA > 0 AND id = @RAP3;
UPDATE rap SET nivel_id = @NIV3, orden = 1, activo = 1,
    titulo = 'RAP 4: Interacción con Visitantes y Presente Continuo'
    WHERE @ESTRUCTURA_VIEJA > 0 AND id = @RAP4;
UPDATE rap SET nivel_id = @NIV3, orden = 2, activo = 1,
    titulo = 'RAP 5: Sugerencias de Mejora y Lista de Chequeo'
    WHERE @ESTRUCTURA_VIEJA > 0 AND id = @RAP5;
UPDATE rap SET nivel_id = @NIV4, orden = 1, activo = 1,
    titulo = 'RAP 6: Práctica Profesional e Instrucciones de Alta'
    WHERE @ESTRUCTURA_VIEJA > 0 AND id = @RAP6;

-- Los niveles 5 y 6 ya no tienen RAPs: ahora sí se pueden eliminar
DELETE FROM nivel WHERE orden > 4;

-- Verificación final
SELECT n.orden AS modulo, n.nombre, r.orden AS rap_orden, r.titulo
FROM rap r JOIN nivel n ON r.nivel_id = n.id
ORDER BY n.orden, r.orden;
