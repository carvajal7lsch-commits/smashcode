-- =============================================================
-- ACTUALIZAR NOMBRES DE NIVELES Y RAPs según contenidos.md
-- =============================================================
SET NAMES utf8mb4;
USE smash_code;

-- Módulo 1: Getting to Know Other People (Nivel 1)
UPDATE nivel SET nombre = 'Módulo 1: Getting to Know Other People', descripcion = 'Fase Análisis — RAP 1: Presentaciones, saludos e información personal en contexto clínico.' WHERE orden = 1;

-- Módulo 2: Work Life Interaction (Nivel 2)
UPDATE nivel SET nombre = 'Módulo 2: Work Life Interaction', descripcion = 'Fase Planeación — RAP 2 y 3: Describir pacientes, entornos hospitalarios y experiencias pasadas.' WHERE orden = 2;

-- Módulo 3: Work Place Communication (Nivel 3)
UPDATE nivel SET nombre = 'Módulo 3: Work Place Communication', descripcion = 'Fase Ejecución — RAP 4 y 5: Comunicación con médicos, colegas y familiares de pacientes.' WHERE orden = 3;

-- Módulo 4: Professional Practice (Nivel 4)
UPDATE nivel SET nombre = 'Módulo 4: Professional Practice', descripcion = 'Fase Evaluación — RAP 6: Práctica profesional e instrucciones de alta médica.' WHERE orden = 4;

-- Borrar niveles > 4 si existen
DELETE FROM rap WHERE nivel_id IN (SELECT id FROM nivel WHERE orden > 4);
DELETE FROM nivel WHERE orden > 4;

-- Obtener IDs de Niveles
SET @NIV1 = (SELECT id FROM nivel WHERE orden = 1 LIMIT 1);
SET @NIV2 = (SELECT id FROM nivel WHERE orden = 2 LIMIT 1);
SET @NIV3 = (SELECT id FROM nivel WHERE orden = 3 LIMIT 1);
SET @NIV4 = (SELECT id FROM nivel WHERE orden = 4 LIMIT 1);

-- 1. RAP 1 -> Módulo 1 (Nivel 1), Orden 1
INSERT INTO rap (id, nivel_id, titulo, orden, activo)
SELECT UUID(), @NIV1, 'RAP 1: Presentaciones e Información Personal', 1, 1
WHERE NOT EXISTS (SELECT 1 FROM rap WHERE nivel_id = @NIV1 AND orden = 1);
UPDATE rap SET titulo = 'RAP 1: Presentaciones e Información Personal', activo = 1 WHERE nivel_id = @NIV1 AND orden = 1;

-- 2. RAP 2 -> Módulo 2 (Nivel 2), Orden 1
INSERT INTO rap (id, nivel_id, titulo, orden, activo)
SELECT UUID(), @NIV2, 'RAP 2: Historia del Paciente y Pasado Simple', 1, 1
WHERE NOT EXISTS (SELECT 1 FROM rap WHERE nivel_id = @NIV2 AND orden = 1);
UPDATE rap SET titulo = 'RAP 2: Historia del Paciente y Pasado Simple', activo = 1 WHERE nivel_id = @NIV2 AND orden = 1;

-- 3. RAP 3 -> Módulo 2 (Nivel 2), Orden 2
INSERT INTO rap (id, nivel_id, titulo, orden, activo)
SELECT UUID(), @NIV2, 'RAP 3: Entorno Hospitalario y Estado Actual', 2, 1
WHERE NOT EXISTS (SELECT 1 FROM rap WHERE nivel_id = @NIV2 AND orden = 2);
UPDATE rap SET titulo = 'RAP 3: Entorno Hospitalario y Estado Actual', activo = 1 WHERE nivel_id = @NIV2 AND orden = 2;

-- 4. RAP 4 -> Módulo 3 (Nivel 3), Orden 1
INSERT INTO rap (id, nivel_id, titulo, orden, activo)
SELECT UUID(), @NIV3, 'RAP 4: Interacción con Visitantes y Presente Continuo', 1, 1
WHERE NOT EXISTS (SELECT 1 FROM rap WHERE nivel_id = @NIV3 AND orden = 1);
UPDATE rap SET titulo = 'RAP 4: Interacción con Visitantes y Presente Continuo', activo = 1 WHERE nivel_id = @NIV3 AND orden = 1;

-- 5. RAP 5 -> Módulo 3 (Nivel 3), Orden 2
INSERT INTO rap (id, nivel_id, titulo, orden, activo)
SELECT UUID(), @NIV3, 'RAP 5: Sugerencias de Mejora y Lista de Chequeo', 2, 1
WHERE NOT EXISTS (SELECT 1 FROM rap WHERE nivel_id = @NIV3 AND orden = 2);
UPDATE rap SET titulo = 'RAP 5: Sugerencias de Mejora y Lista de Chequeo', activo = 1 WHERE nivel_id = @NIV3 AND orden = 2;

-- 6. RAP 6 -> Módulo 4 (Nivel 4), Orden 1
INSERT INTO rap (id, nivel_id, titulo, orden, activo)
SELECT UUID(), @NIV4, 'RAP 6: Práctica Profesional e Instrucciones de Alta', 1, 1
WHERE NOT EXISTS (SELECT 1 FROM rap WHERE nivel_id = @NIV4 AND orden = 1);
UPDATE rap SET titulo = 'RAP 6: Práctica Profesional e Instrucciones de Alta', activo = 1 WHERE nivel_id = @NIV4 AND orden = 1;

SELECT r.id, r.titulo, r.orden, n.nombre AS nivel FROM rap r JOIN nivel n ON r.nivel_id = n.id ORDER BY n.orden, r.orden;
