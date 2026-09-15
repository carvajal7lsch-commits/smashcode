-- =============================================================
-- MIGRACIÓN: Insignias por módulo, Vocabulario Pro y Estudiante Élite (HU07)
-- Fecha: 2026-09-15
-- Descripción:
--   HU07 pide una insignia al aprobar un quiz con 90% o más, pero solo el
--   Módulo 1 la daba ("Primer Nivel"). "Vocabulario Pro" y "Estudiante Élite"
--   existían y ningún código las otorgaba; "Élite" además pedía 6 niveles
--   cuando la ruta tiene 4 módulos.
--
--   1. Criterios estables que lee el código (columna insignia.criterio).
--   2. Insignias nuevas, con los nombres de contenidos.md:
--      M2 Handover Specialist, M3 Clinical Communicator, M4 Care Evaluator.
--   3. Entrega las insignias que los aprendices ya se habían ganado.
--
-- SEGURO: los UPDATE solo cambian las filas que aún tienen el criterio
--         original; los INSERT no duplican nada. No borra datos.
-- IDEMPOTENTE: la segunda vez ningún UPDATE coincide y ningún INSERT agrega.
-- PRUEBA EN SECO: solo usa UPDATE e INSERT, así que se puede probar con
--   php database/probar_migracion.php database/migraciones/2026_09_15_insignias_por_modulo.sql
-- =============================================================

SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;
USE smash_code;

-- 1. Criterios estables
UPDATE insignia SET criterio = 'quiz_modulo_1 >= 90', descripcion = 'Aprobaste el Módulo 1 con 90% o más' WHERE criterio = 'Completar Nivel 1';
UPDATE insignia SET criterio = 'vocabulario_aprendido >= 30', descripcion = 'Aprendiste 30 términos médicos' WHERE criterio = 'vocabulario_aprendido >= 50';
UPDATE insignia SET criterio = 'modulos_completados = todos', descripcion = 'Completaste todos los módulos del curso' WHERE criterio = 'niveles_completados = 6';

-- 2. Insignias nuevas por módulo
INSERT INTO insignia (id, nombre, descripcion, criterio)
SELECT UUID(), 'Handover Specialist', 'Aprobaste el Módulo 2 con 90% o más', 'quiz_modulo_2 >= 90' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM insignia WHERE criterio = 'quiz_modulo_2 >= 90');

INSERT INTO insignia (id, nombre, descripcion, criterio)
SELECT UUID(), 'Clinical Communicator', 'Aprobaste el Módulo 3 con 90% o más', 'quiz_modulo_3 >= 90' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM insignia WHERE criterio = 'quiz_modulo_3 >= 90');

INSERT INTO insignia (id, nombre, descripcion, criterio)
SELECT UUID(), 'Care Evaluator', 'Aprobaste el Módulo 4 con 90% o más', 'quiz_modulo_4 >= 90' FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM insignia WHERE criterio = 'quiz_modulo_4 >= 90');

-- 3a. Ya ganadas: un quiz aprobado con 90% o más en el módulo
INSERT INTO insignia_usuario (usuario_id, insignia_id)
SELECT DISTINCT iq.usuario_id, i.id
FROM intento_quiz iq
JOIN quiz q ON q.id = iq.quiz_id
JOIN rap r ON r.id = q.rap_id
JOIN nivel n ON n.id = r.nivel_id
JOIN insignia i ON i.criterio = CONCAT('quiz_modulo_', n.orden, ' >= 90')
WHERE iq.aprobado = 1
  AND iq.puntaje >= 90
  AND NOT EXISTS (SELECT 1 FROM insignia_usuario iu WHERE iu.usuario_id = iq.usuario_id AND iu.insignia_id = i.id);

-- 3b. Ya ganada: 30 palabras o más en RAPs completados
INSERT INTO insignia_usuario (usuario_id, insignia_id)
SELECT t.usuario_id, i.id
FROM (
    SELECT p.usuario_id, COUNT(v.id) AS palabras
    FROM progreso p
    JOIN rap r ON r.id = p.rap_id AND r.activo = 1
    JOIN nivel n ON n.id = r.nivel_id AND n.activo = 1
    JOIN vocabulario v ON v.rap_id = r.id AND v.activo = 1
    WHERE p.completado = 1
    GROUP BY p.usuario_id
) AS t
JOIN insignia i ON i.criterio = 'vocabulario_aprendido >= 30'
WHERE t.palabras >= 30
  AND NOT EXISTS (SELECT 1 FROM insignia_usuario iu WHERE iu.usuario_id = t.usuario_id AND iu.insignia_id = i.id);

-- 3c. Ya ganada: todos los RAPs activos completados
INSERT INTO insignia_usuario (usuario_id, insignia_id)
SELECT t.usuario_id, i.id
FROM (
    SELECT p.usuario_id, COUNT(DISTINCT p.rap_id) AS completados
    FROM progreso p
    JOIN rap r ON r.id = p.rap_id AND r.activo = 1
    JOIN nivel n ON n.id = r.nivel_id AND n.activo = 1
    WHERE p.completado = 1
    GROUP BY p.usuario_id
) AS t
JOIN insignia i ON i.criterio = 'modulos_completados = todos'
WHERE t.completados > 0
  AND t.completados = (SELECT COUNT(*) FROM rap r JOIN nivel n ON n.id = r.nivel_id AND n.activo = 1 WHERE r.activo = 1)
  AND NOT EXISTS (SELECT 1 FROM insignia_usuario iu WHERE iu.usuario_id = t.usuario_id AND iu.insignia_id = i.id);

-- Verificación
SELECT nombre, criterio, (SELECT COUNT(*) FROM insignia_usuario iu WHERE iu.insignia_id = insignia.id) AS aprendices
FROM insignia
ORDER BY criterio;
