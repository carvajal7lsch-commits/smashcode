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

-- Niveles 5 y 6
UPDATE nivel SET nombre = 'Nivel 5 - B2 Pre-avanzado', descripcion = 'Nivel avanzado de inglés clínico.' WHERE orden = 5;
UPDATE nivel SET nombre = 'Nivel 6 - B2 Avanzado', descripcion = 'Dominio avanzado del inglés en contextos sanitarios.' WHERE orden = 6;

-- Obtener IDs de Niveles
SELECT id INTO @NIV1 FROM nivel WHERE orden = 1 LIMIT 1;
SELECT id INTO @NIV2 FROM nivel WHERE orden = 2 LIMIT 1;
SELECT id INTO @NIV3 FROM nivel WHERE orden = 3 LIMIT 1;
SELECT id INTO @NIV4 FROM nivel WHERE orden = 4 LIMIT 1;

-- Asegurar que la tabla RAP contenga la estructura exacta de 6 RAPs asignados a sus respectivos módulos
-- RAP 1 -> Módulo 1 (Nivel 1)
-- RAP 2 y RAP 3 -> Módulo 2 (Nivel 2)
-- RAP 4 y RAP 5 -> Módulo 3 (Nivel 3)
-- RAP 6 -> Módulo 4 (Nivel 4)

-- Limpieza o actualización de RAPs
UPDATE rap SET nivel_id = @NIV1, titulo = 'RAP 1: Presentaciones e Información Personal', orden = 1, activo = 1 WHERE id = 'be5df679-5166-11f1-b275-8c1645fa3d64';
UPDATE rap SET nivel_id = @NIV2, titulo = 'RAP 2: Historia del Paciente y Pasado Simple', orden = 1, activo = 1 WHERE id = 'be5e0200-5166-11f1-b275-8c1645fa3d64';
UPDATE rap SET nivel_id = @NIV2, titulo = 'RAP 3: Entorno Hospitalario y Estado Actual', orden = 2, activo = 1 WHERE id = 'be5e030b-5166-11f1-b275-8c1645fa3d64';
UPDATE rap SET nivel_id = @NIV3, titulo = 'RAP 4: Interacción con Visitantes y Presente Continuo', orden = 1, activo = 1 WHERE id = 'be5e03c2-5166-11f1-b275-8c1645fa3d64';
UPDATE rap SET nivel_id = @NIV3, titulo = 'RAP 5: Sugerencias de Mejora y Lista de Chequeo', orden = 2, activo = 1 WHERE id = 'be5e0474-5166-11f1-b275-8c1645fa3d64';
UPDATE rap SET nivel_id = @NIV4, titulo = 'RAP 6: Práctica Profesional e Instrucciones de Alta', orden = 1, activo = 1 WHERE id = 'be5e056f-5166-11f1-b275-8c1645fa3d64';

SELECT r.id, r.titulo, r.orden, n.nombre AS nivel FROM rap r JOIN nivel n ON r.nivel_id = n.id ORDER BY n.orden, r.orden;
