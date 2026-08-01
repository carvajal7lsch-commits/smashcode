-- =============================================================
-- SEED PARTE 3: Ejercicios del RAP 3 (Módulo 3: Work Place Communication)
-- =============================================================

SET NAMES utf8mb4;
USE smash_code;

SELECT id INTO @RAP3 FROM rap WHERE nivel_id IN (SELECT id FROM nivel WHERE orden = 3) LIMIT 1;

-- Limpieza previa
DELETE FROM ejercicio_opcion WHERE ejercicio_id IN (SELECT id FROM ejercicio WHERE rap_id = @RAP3);
DELETE FROM ejercicio WHERE rap_id = @RAP3;

-- Ejercicio 1: Selección múltiple rutinas
SET @ej1 = UUID();
INSERT INTO ejercicio (id, rap_id, tipo, enunciado, max_intentos, puntos, activo)
VALUES (@ej1, @RAP3, 'seleccion_multiple', 'What routine action does Nurse Sarah perform every morning at 8 AM?', 3, 10, 1);

INSERT INTO ejercicio_opcion (id, ejercicio_id, texto, es_correcta, retroalimentacion) VALUES
(UUID(), @ej1, 'She gives medication.', 1, 'Correct! Giving medication at 8 AM is part of the daily routine.'),
(UUID(), @ej1, 'She takes the patient home.', 0, 'Incorrect. Patient discharge occurs in Module 4.'),
(UUID(), @ej1, 'She calls an airplane.', 0, 'Incorrect. This action is unrelated to clinical routines.'),
(UUID(), @ej1, 'She performs surgical procedures.', 0, 'Incorrect. Surgical procedures are performed by doctors.');

-- Ejercicio 2: Completar frase - Acciones en el momento (Presente Continuo)
SET @ej2 = UUID();
INSERT INTO ejercicio (id, rap_id, tipo, enunciado, max_intentos, puntos, activo)
VALUES (@ej2, @RAP3, 'completar_frase', 'Happening Now: Right now, Nurse Sarah ___ checking the blood pressure monitor.', 3, 10, 1);

INSERT INTO ejercicio_opcion (id, ejercicio_id, texto, es_correcta, retroalimentacion) VALUES
(UUID(), @ej2, 'is', 1, 'Correct! Present continuous for singular subject uses "is checking".'),
(UUID(), @ej2, 'were', 0, 'Incorrect. "Were" is past plural.'),
(UUID(), @ej2, 'gave', 0, 'Incorrect. "Gave" is past tense.'),
(UUID(), @ej2, 'should', 0, 'Incorrect. "Should" is a modal verb for suggestions.');

-- Ejercicio 3: Completar frase - Sugerencias de mejora
SET @ej3 = UUID();
INSERT INTO ejercicio (id, rap_id, tipo, enunciado, max_intentos, puntos, activo)
VALUES (@ej3, @RAP3, 'completar_frase', 'Shift Suggestion: I think we ___ update the nursing checklist for Mr. Thomas.', 3, 10, 1);

INSERT INTO ejercicio_opcion (id, ejercicio_id, texto, es_correcta, retroalimentacion) VALUES
(UUID(), @ej3, 'should', 1, 'Correct! "Should" is used for polite workplace suggestions.'),
(UUID(), @ej3, 'was', 0, 'Incorrect. "Was" is past tense.'),
(UUID(), @ej3, 'checking', 0, 'Incorrect. A modal verb is required before the main verb.'),
(UUID(), @ej3, 'are', 0, 'Incorrect. "Are" does not fit before base verb update.');

-- Ejercicio 4: Arrastrar y soltar parejas de herramientas
SET @ej4 = UUID();
INSERT INTO ejercicio (id, rap_id, tipo, enunciado, max_intentos, puntos, activo)
VALUES (@ej4, @RAP3, 'arrastrar_soltar', 'Match the clinical tools and actions with their Spanish meanings:', 3, 10, 1);

INSERT INTO ejercicio_opcion (id, ejercicio_id, texto, es_correcta, retroalimentacion) VALUES
(UUID(), @ej4, 'Thermometer = Termómetro', 1, 'Correct match!'),
(UUID(), @ej4, 'Stethoscope = Estetoscopio', 1, 'Correct match!'),
(UUID(), @ej4, 'Blood pressure monitor = Monitor de presión arterial', 1, 'Correct match!'),
(UUID(), @ej4, 'Checklist = Lista de chequeo', 1, 'Correct match!');

-- Ejercicio 5: Ordenar diálogo de interacción
SET @ej5 = UUID();
INSERT INTO ejercicio (id, rap_id, tipo, enunciado, max_intentos, puntos, activo)
VALUES (@ej5, @RAP3, 'ordenar_dialogo', 'Order the workplace conversation in correct chronological sequence:', 3, 10, 1);

INSERT INTO ejercicio_opcion (id, ejercicio_id, texto, es_correcta, retroalimentacion) VALUES
(UUID(), @ej5, 'Visitor: Excuse me, what procedure are you doing right now? | Nurse Sarah: We are checking his vital signs with the monitor. | Visitor: When do you give his medication? | Nurse Sarah: I give medication at 8 AM every morning.', 1, '¡Perfecto!');

-- Ejercicio 6: Escucha y escribe (dictado)
SET @ej6 = UUID();
INSERT INTO ejercicio (id, rap_id, tipo, enunciado, max_intentos, puntos, activo)
VALUES (@ej6, @RAP3, 'escucha_escribe', 'Listen to the clinical dictation and type the name of the tool used to listen to heart and lungs:', 3, 10, 1);

INSERT INTO ejercicio_opcion (id, ejercicio_id, texto, es_correcta, retroalimentacion) VALUES
(UUID(), @ej6, 'stethoscope', 1, 'Correct! "stethoscope" is the clinical instrument.');
