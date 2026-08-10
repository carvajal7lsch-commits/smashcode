-- =============================================================
-- SEED MÓDULO 2 - RAP 2: Historia del Paciente y Pasado Simple (Parte A)
-- =============================================================
SET NAMES utf8mb4;
USE smash_code;

-- Obtener ID del RAP 2 (Módulo 2, Orden 1)
SELECT r.id INTO @RAP2 FROM rap r JOIN nivel n ON r.nivel_id = n.id WHERE n.orden = 2 AND r.orden = 1 LIMIT 1;
SELECT id INTO @CAT_SUST FROM categoria_vocabulario WHERE nombre = 'Sustantivo' LIMIT 1;
SELECT id INTO @CAT_VERB FROM categoria_vocabulario WHERE nombre = 'Verbo' LIMIT 1;
SELECT id INTO @CAT_ADJ FROM categoria_vocabulario WHERE nombre = 'Adjetivo' LIMIT 1;
SELECT id INTO @AREA_URG FROM area_clinica WHERE nombre LIKE '%Urgencias%' LIMIT 1;

-- Limpieza previa de RAP 2
DELETE FROM vocabulario WHERE rap_id = @RAP2;
DELETE FROM turno_dialogo WHERE dialogo_id IN (SELECT id FROM dialogo WHERE rap_id = @RAP2);
DELETE FROM dialogo WHERE rap_id = @RAP2;
DELETE FROM ejercicio_opcion WHERE ejercicio_id IN (SELECT id FROM ejercicio WHERE rap_id = @RAP2);
DELETE FROM ejercicio WHERE rap_id = @RAP2;
DELETE FROM respuesta_quiz WHERE pregunta_id IN (SELECT id FROM pregunta WHERE quiz_id IN (SELECT id FROM quiz WHERE rap_id = @RAP2));
DELETE FROM intento_quiz WHERE quiz_id IN (SELECT id FROM quiz WHERE rap_id = @RAP2);
DELETE FROM pregunta WHERE quiz_id IN (SELECT id FROM quiz WHERE rap_id = @RAP2);
DELETE FROM quiz WHERE rap_id = @RAP2;

-- 1. VOCABULARIO RAP 2
INSERT INTO vocabulario (id, rap_id, termino_en, termino_es, categoria_id, area_clinica_id, transcripcion_ipa, audio_url, imagen_url, oracion_ejemplo, nivel_dificultad, activo) VALUES
(UUID(), @RAP2, 'Right arm', 'Brazo derecho', @CAT_SUST, @AREA_URG, '/raɪt ɑːrm/', NULL, NULL, 'Mr. Thomas injured his right arm in the fall.', 'A1', 1),
(UUID(), @RAP2, 'Head', 'Cabeza', @CAT_SUST, @AREA_URG, '/hɛd/', NULL, NULL, 'Check if the patient hit his head.', 'A1', 1),
(UUID(), @RAP2, 'Leg', 'Pierna', @CAT_SUST, @AREA_URG, '/lɛɡ/', NULL, NULL, 'He can move his left leg without pain.', 'A1', 1),
(UUID(), @RAP2, 'Fracture', 'Fractura', @CAT_SUST, @AREA_URG, '/ˈfræktʃər/', NULL, NULL, 'X-rays confirmed a right arm fracture.', 'A1', 1),
(UUID(), @RAP2, 'Fell', 'Se cayó (pasado de fall)', @CAT_VERB, @AREA_URG, '/fɛl/', NULL, NULL, 'He fell down at the hotel yesterday.', 'A1', 1),
(UUID(), @RAP2, 'Had an accident', 'Tuvo un accidente', @CAT_VERB, @AREA_URG, '/hæd ən ˈæksɪdənt/', NULL, NULL, 'The patient had an accident before admission.', 'A1', 1),
(UUID(), @RAP2, 'Pale', 'Pálido/a', @CAT_ADJ, @AREA_URG, '/peɪl/', NULL, NULL, 'His skin looks pale due to pain.', 'A1', 1),
(UUID(), @RAP2, 'Tired', 'Cansado/a', @CAT_ADJ, @AREA_URG, '/ˈtaɪərd/', NULL, NULL, 'Mr. Thomas feels tired after the emergency transfer.', 'A1', 1);

-- 2. DIÁLOGO RAP 2
SET @dia2 = UUID();
INSERT INTO dialogo (id, rap_id, titulo, contexto, participantes, audio_completo_url, activo) VALUES
(@dia2, @RAP2, 'Shift Handover: Patient Admission History', 'Nurse A and Nurse B discuss Mr. Thomas\'s medical history and arrival background.', 'Nurse A, Nurse B', NULL, 1);

INSERT INTO turno_dialogo (id, dialogo_id, orden_turno, hablante, texto_en, texto_es, audio_url) VALUES
(UUID(), @dia2, 1, 'Nurse A', 'What happened to Mr. Thomas yesterday before arriving at emergency?', '¿Qué le sucedió a Mr. Thomas ayer antes de llegar a urgencias?', NULL),
(UUID(), @dia2, 2, 'Nurse B', 'He fell at his hotel room yesterday afternoon.', 'Él se cayó en la habitación de su hotel ayer por la tarde.', NULL),
(UUID(), @dia2, 3, 'Nurse A', 'Did paramedics bring him to the clinic on a stretcher?', '¿Los paramédicos lo trajeron a la clínica en una camilla?', NULL),
(UUID(), @dia2, 4, 'Nurse B', 'Yes, they brought him immediately and reported a right arm fracture.', 'Sí, lo trajeron de inmediato y reportaron una fractura en el brazo derecho.', NULL);

-- 3. EJERCICIOS RAP 2
SET @ej1 = UUID();
INSERT INTO ejercicio (id, rap_id, tipo, enunciado, max_intentos, puntos, activo) VALUES
(@ej1, @RAP2, 'seleccion_multiple', 'What happened to Mr. Thomas yesterday before arriving at the hospital?', 3, 10, 1);
INSERT INTO ejercicio_opcion (id, ejercicio_id, texto, es_correcta, retroalimentacion) VALUES
(UUID(), @ej1, 'He fell at the hotel.', 1, 'Correct! Mr. Thomas fell at the hotel yesterday.'),
(UUID(), @ej1, 'He was working at the office.', 0, 'Incorrect. He fell at the hotel.'),
(UUID(), @ej1, 'He traveled by airplane.', 0, 'Incorrect. He was at his hotel.');

SET @ej2 = UUID();
INSERT INTO ejercicio (id, rap_id, tipo, enunciado, max_intentos, puntos, activo) VALUES
(@ej2, @RAP2, 'completar_frase', 'Patient History: Yesterday, Mr. Thomas ___ down in his room.', 3, 10, 1);
INSERT INTO ejercicio_opcion (id, ejercicio_id, texto, es_correcta, retroalimentacion) VALUES
(UUID(), @ej2, 'fell', 1, 'Correct! "Fell" is simple past.'),
(UUID(), @ej2, 'falls', 0, 'Incorrect. Yesterday requires past tense "fell".');

SET @ej3 = UUID();
INSERT INTO ejercicio (id, rap_id, tipo, enunciado, max_intentos, puntos, activo) VALUES
(@ej3, @RAP2, 'arrastrar_soltar', 'Match the anatomical terms with their Spanish meanings:', 3, 10, 1);
INSERT INTO ejercicio_opcion (id, ejercicio_id, texto, es_correcta, retroalimentacion) VALUES
(UUID(), @ej3, 'Right arm = Brazo derecho', 1, 'Correct match!'),
(UUID(), @ej3, 'Head = Cabeza', 1, 'Correct match!'),
(UUID(), @ej3, 'Fracture = Fractura', 1, 'Correct match!');

SET @ej4 = UUID();
INSERT INTO ejercicio (id, rap_id, tipo, enunciado, max_intentos, puntos, activo) VALUES
(@ej4, @RAP2, 'ordenar_dialogo', 'Order the shift handover lines in correct sequence:', 3, 10, 1);
INSERT INTO ejercicio_opcion (id, ejercicio_id, texto, es_correcta, retroalimentacion) VALUES
(UUID(), @ej4, 'Nurse A: What happened to Mr. Thomas yesterday? | Nurse B: He fell at his hotel room yesterday afternoon. | Nurse A: Did paramedics bring him on a stretcher? | Nurse B: Yes, and they reported a right arm fracture.', 1, '¡Perfecto!');

SET @ej5 = UUID();
INSERT INTO ejercicio (id, rap_id, tipo, enunciado, max_intentos, puntos, activo) VALUES
(@ej5, @RAP2, 'escucha_escribe', 'Listen to the clinical dictation and type the diagnosis (Hint: broken bone):', 3, 10, 1);
INSERT INTO ejercicio_opcion (id, ejercicio_id, texto, es_correcta, retroalimentacion) VALUES
(UUID(), @ej5, 'fracture', 1, 'Correct! "fracture" is the diagnosis.');

-- 4. QUIZ RAP 2
SET @quiz2 = UUID();
INSERT INTO quiz (id, rap_id, puntaje_minimo, limite_tiempo_seg, aleatorizar, max_intentos, activo) VALUES (@quiz2, @RAP2, 60.00, 300, 0, 3, 1);
INSERT INTO pregunta (id, quiz_id, texto, opciones, respuesta_correcta, retroalimentacion) VALUES
(UUID(), @quiz2, 'Where did Mr. Thomas fall yesterday?', '["At his hotel", "In the hospital hallway", "At work", "In the street"]', 'At his hotel', 'Correct! He fell at his hotel.'),
(UUID(), @quiz2, 'Choose the simple past verb: "He ___ an accident yesterday."', '["had", "has", "is", "have"]', 'had', 'Correct! "Had" is simple past of have.'),
(UUID(), @quiz2, 'Where is Mr. Thomas\'s fracture located?', '["In his right arm", "In his left knee", "On his ankle", "On his forehead"]', 'In his right arm', 'Correct! Fracture is in his right arm.');
