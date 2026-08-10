-- =============================================================
-- SEED MÓDULO 3 - RAP 5: Sugerencias de Mejora y Lista de Chequeo (Parte B)
-- =============================================================
SET NAMES utf8mb4;
USE smash_code;

-- Obtener ID del RAP 5 (Módulo 3, Orden 2)
SELECT r.id INTO @RAP5 FROM rap r JOIN nivel n ON r.nivel_id = n.id WHERE n.orden = 3 AND r.orden = 2 LIMIT 1;
SELECT id INTO @CAT_SUST FROM categoria_vocabulario WHERE nombre = 'Sustantivo' LIMIT 1;
SELECT id INTO @CAT_VERB FROM categoria_vocabulario WHERE nombre = 'Verbo' LIMIT 1;
SELECT id INTO @CAT_ADJ FROM categoria_vocabulario WHERE nombre = 'Adjetivo' LIMIT 1;
SELECT id INTO @AREA_GEN FROM area_clinica WHERE nombre LIKE '%General%' LIMIT 1;

-- Limpieza previa de RAP 5
DELETE FROM vocabulario WHERE rap_id = @RAP5;
DELETE FROM turno_dialogo WHERE dialogo_id IN (SELECT id FROM dialogo WHERE rap_id = @RAP5);
DELETE FROM dialogo WHERE rap_id = @RAP5;
DELETE FROM ejercicio_opcion WHERE ejercicio_id IN (SELECT id FROM ejercicio WHERE rap_id = @RAP5);
DELETE FROM ejercicio WHERE rap_id = @RAP5;
DELETE FROM respuesta_quiz WHERE pregunta_id IN (SELECT id FROM pregunta WHERE quiz_id IN (SELECT id FROM quiz WHERE rap_id = @RAP5));
DELETE FROM intento_quiz WHERE quiz_id IN (SELECT id FROM quiz WHERE rap_id = @RAP5);
DELETE FROM pregunta WHERE quiz_id IN (SELECT id FROM quiz WHERE rap_id = @RAP5);
DELETE FROM quiz WHERE rap_id = @RAP5;

-- 1. VOCABULARIO RAP 5
INSERT INTO vocabulario (id, rap_id, termino_en, termino_es, categoria_id, area_clinica_id, transcripcion_ipa, audio_url, imagen_url, oracion_ejemplo, nivel_dificultad, activo) VALUES
(UUID(), @RAP5, 'Checklist', 'Lista de chequeo', @CAT_SUST, @AREA_GEN, '/ˈtʃɛklɪst/', NULL, NULL, 'Fill out the nursing checklist for this shift.', 'A1', 1),
(UUID(), @RAP5, 'Nurse Manager', 'Jefe de enfermería', @CAT_SUST, @AREA_GEN, '/nɜːrs ˈmænɪdʒər/', NULL, NULL, 'Propose an improvement to the Nurse Manager.', 'A1', 1),
(UUID(), @RAP5, 'Give medication', 'Administrar medicamento', @CAT_VERBO, @AREA_GEN, '/ɡɪv ˌmɛdɪˈkeɪʃən/', NULL, NULL, 'I give medication at 8 AM as part of our routine.', 'A1', 1),
(UUID(), @RAP5, 'Routine', 'Rutina', @CAT_SUST, @AREA_GEN, '/ruːˈtiːn/', NULL, NULL, 'Daily routine includes morning rounds and checklist update.', 'A1', 1),
(UUID(), @RAP5, 'Syringe', 'Jeringa', @CAT_SUST, @AREA_GEN, '/sɪˈrɪndʒ/', NULL, NULL, 'Prepare a sterile syringe for routine medication.', 'A1', 1),
(UUID(), @RAP5, 'IV Drip', 'Goteo intravenoso', @CAT_SUST, @AREA_GEN, '/aɪ viː drɪp/', NULL, NULL, 'Check the IV drip flow rate every hour.', 'A1', 1),
(UUID(), @RAP5, 'Glucometer', 'Gluciómetro', @CAT_SUST, @AREA_GEN, '/ɡluːˈkɒmɪtər/', NULL, NULL, 'Use the glucometer to measure glucose levels.', 'A1', 1),
(UUID(), @RAP5, 'Update', 'Actualizar / Mejorar', @CAT_VERB, @AREA_GEN, '/ʌpˈdeɪt/', NULL, NULL, 'We should update the checklist to save time.', 'A1', 1);

-- 2. DIÁLOGO RAP 5
SET @dia5 = UUID();
INSERT INTO dialogo (id, rap_id, titulo, contexto, participantes, audio_completo_url, activo) VALUES
(@dia5, @RAP5, 'Team Shift Update: Proposing Workplace Improvements', 'Nurse Sarah discusses shift routines and proposes a checklist update to the Nurse Manager.', 'Nurse Manager, Nurse Sarah', NULL, 1);

INSERT INTO turno_dialogo (id, dialogo_id, orden_turno, hablante, texto_en, texto_es, audio_url) VALUES
(UUID(), @dia5, 1, 'Nurse Manager', 'Nurse Sarah, how is our daily medication routine progressing?', 'Enfermera Sarah, ¿cómo va progresando nuestra rutina diaria de medicamentos?', NULL),
(UUID(), @dia5, 2, 'Nurse Sarah', 'Everything is on schedule. I give medication at 8 AM every morning.', 'Todo va según lo programado. Administro medicamentos a las 8 AM cada mañana.', NULL),
(UUID(), @dia5, 3, 'Nurse Manager', 'Do you have any suggestions for our shift handover checklist?', '¿Tiene alguna sugerencia para nuestra lista de chequeo de entrega de turno?', NULL),
(UUID(), @dia5, 4, 'Nurse Sarah', 'Yes! I think we should update the checklist for Mr. Thomas to track vital signs faster.', '¡Sí! Creo que deberíamos actualizar la lista de chequeo para Mr. Thomas para registrar los signos vitales más rápido.', NULL);

-- 3. EJERCICIOS RAP 5
SET @ej1 = UUID();
INSERT INTO ejercicio (id, rap_id, tipo, enunciado, max_intentos, puntos, activo) VALUES
(@ej1, @RAP5, 'completar_frase', 'Shift Suggestion: I think we ___ update the nursing checklist.', 3, 10, 1);
INSERT INTO ejercicio_opcion (id, ejercicio_id, texto, es_correcta, retroalimentacion) VALUES
(UUID(), @ej1, 'should', 1, 'Correct! "Should" is used for workplace suggestions.'),
(UUID(), @ej1, 'was', 0, 'Incorrect.'),
(UUID(), @ej1, 'are', 0, 'Incorrect.');

SET @ej2 = UUID();
INSERT INTO ejercicio (id, rap_id, tipo, enunciado, max_intentos, puntos, activo) VALUES
(@ej2, @RAP5, 'seleccion_multiple', 'What routine action does Nurse Sarah perform at 8 AM?', 3, 10, 1);
INSERT INTO ejercicio_opcion (id, ejercicio_id, texto, es_correcta, retroalimentacion) VALUES
(UUID(), @ej2, 'I give medication at 8 AM.', 1, 'Correct! Giving medication at 8 AM is the daily routine.'),
(UUID(), @ej2, 'I sleep in room 204.', 0, 'Incorrect.'),
(UUID(), @ej2, 'I leave the hospital.', 0, 'Incorrect.');

SET @ej3 = UUID();
INSERT INTO ejercicio (id, rap_id, tipo, enunciado, max_intentos, puntos, activo) VALUES
(@ej3, @RAP5, 'arrastrar_soltar', 'Match the nursing tools and actions with Spanish meanings:', 3, 10, 1);
INSERT INTO ejercicio_opcion (id, ejercicio_id, texto, es_correcta, retroalimentacion) VALUES
(UUID(), @ej3, 'Checklist = Lista de chequeo', 1, 'Correct match!'),
(UUID(), @ej3, 'Give medication = Administrar medicamento', 1, 'Correct match!'),
(UUID(), @ej3, 'Glucometer = Gluciómetro', 1, 'Correct match!');

SET @ej4 = UUID();
INSERT INTO ejercicio (id, rap_id, tipo, enunciado, max_intentos, puntos, activo) VALUES
(@ej4, @RAP5, 'ordenar_dialogo', 'Order the workplace suggestion conversation in correct sequence:', 3, 10, 1);
INSERT INTO ejercicio_opcion (id, ejercicio_id, texto, es_correcta, retroalimentacion) VALUES
(UUID(), @ej4, 'Nurse Manager: How is our daily medication routine progressing? | Nurse Sarah: Everything is on schedule. I give medication at 8 AM. | Nurse Manager: Do you have suggestions for our shift handover checklist? | Nurse Sarah: Yes! I think we should update the checklist to track vital signs faster.', 1, '¡Perfecto!');

SET @ej5 = UUID();
INSERT INTO ejercicio (id, rap_id, tipo, enunciado, max_intentos, puntos, activo) VALUES
(@ej5, @RAP5, 'escucha_escribe', 'Listen to the clinical dictation and type the format name (Hint: evaluation form):', 3, 10, 1);
INSERT INTO ejercicio_opcion (id, ejercicio_id, texto, es_correcta, retroalimentacion) VALUES
(UUID(), @ej5, 'checklist', 1, 'Correct! "checklist" is the format.');

-- 4. QUIZ RAP 5
SET @quiz5 = UUID();
INSERT INTO quiz (id, rap_id, puntaje_minimo, limite_tiempo_seg, aleatorizar, max_intentos, activo) VALUES (@quiz5, @RAP5, 60.00, 300, 0, 3, 1);
INSERT INTO pregunta (id, quiz_id, texto, opciones, respuesta_correcta, retroalimentacion) VALUES
(UUID(), @quiz5, 'How do you propose a polite workplace suggestion to your Nurse Manager?', '["We should update the checklist", "I am standing here", "He fell down yesterday", "Give me the syringe"]', 'We should update the checklist', 'Correct! "We should..." is used for suggestions.'),
(UUID(), @quiz5, 'Which sentence expresses a daily nursing routine in Present Simple?', '["I give medication at 8 AM", "I am checking temperature now", "He was pale yesterday", "We should go home"]', 'I give medication at 8 AM', 'Correct! "I give medication at 8 AM" is a daily routine.'),
(UUID(), @quiz5, 'What clinical tool is used to record daily shift procedures and performance?', '["Checklist", "Wheelchair", "Stretcher", "Bandage"]', 'Checklist', 'Correct! A checklist records shift procedures.');
