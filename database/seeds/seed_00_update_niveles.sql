-- =============================================================
-- ACTUALIZAR NOMBRES DE NIVELES según contenidos.md
-- =============================================================
SET NAMES utf8mb4;
USE smash_code;

-- Módulo 1: Getting to Know Other People (Fase Análisis - RAP 1) → Nivel 1
UPDATE nivel SET nombre = 'Módulo 1: Getting to Know Other People', descripcion = 'Fase Análisis — RAP 1: Presentaciones, saludos e información personal en contexto clínico.' WHERE orden = 1;

-- Módulo 2: Work Life Interaction (Fase Planeación - RAP 2 y 3) → Nivel 2
UPDATE nivel SET nombre = 'Módulo 2: Work Life Interaction', descripcion = 'Fase Planeación — RAP 2 y 3: Describir pacientes, entornos hospitalarios y experiencias pasadas.' WHERE orden = 2;

-- Módulo 3: Work Place Communication (Fase Ejecución - RAP 4 y 5) → Nivel 3
UPDATE nivel SET nombre = 'Módulo 3: Work Place Communication', descripcion = 'Fase Ejecución — RAP 4 y 5: Comunicación con médicos, colegas y familiares de pacientes.' WHERE orden = 3;

-- Módulo 4: Professional Practice (Fase Evaluación - RAP 6) → Nivel 4
UPDATE nivel SET nombre = 'Módulo 4: Professional Practice', descripcion = 'Fase Evaluación — RAP 6: Práctica profesional integral del inglés clínico.' WHERE orden = 4;

-- Niveles 5 y 6 quedan como extensión (B2)
UPDATE nivel SET nombre = 'Nivel 5 - B2 Pre-avanzado', descripcion = 'Nivel avanzado de inglés clínico.' WHERE orden = 5;
UPDATE nivel SET nombre = 'Nivel 6 - B2 Avanzado', descripcion = 'Dominio avanzado del inglés en contextos sanitarios.' WHERE orden = 6;

-- Confirmar resultado
SELECT orden, nombre FROM nivel ORDER BY orden;
