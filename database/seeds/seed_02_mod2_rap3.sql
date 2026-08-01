-- =============================================================
-- SEED MÓDULO 2 - RAP 3: Entorno Hospitalario y Estado Actual (Parte B)
-- =============================================================
SET NAMES utf8mb4;
USE smash_code;

-- Obtener ID del RAP 3 (Módulo 2, Orden 2)
SELECT r.id INTO @RAP3 FROM rap r JOIN nivel n ON r.nivel_id = n.id WHERE n.orden = 2 AND r.orden = 2 LIMIT 1;
SELECT id INTO @CAT_SUST FROM categoria_vocabulario WHERE nombre = 'Sustantivo' LIMIT 1;
SELECT id INTO @CAT_VERB FROM categoria_vocabulario WHERE nombre = 'Verbo' LIMIT 1;
SELECT id INTO @CAT_ADJ FROM categoria_vocabulario WHERE nombre = 'Adjetivo' LIMIT 1;
SELECT id INTO @AREA_GEN FROM area_clinica WHERE nombre LIKE '%General%' LIMIT 1;

-- Limpieza previa de RAP 3
DELETE FROM vocabulario WHERE rap_id = @RAP3;
DELETE FROM turno_dialogo WHERE dialogo_id IN (SELECT id FROM dialogo WHERE rap_id = @RAP3);
DELETE FROM dialogo WHERE rap_id = @RAP3;
DELETE FROM ejercicio_opcion WHERE ejercicio_id IN (SELECT id FROM ejercicio WHERE rap_id = @RAP3);
DELETE FROM ejercicio WHERE rap_id = @RAP3;
DELETE FROM respuesta_quiz WHERE pregunta_id IN (SELECT id FROM pregunta WHERE quiz_id IN (SELECT id FROM quiz WHERE rap_id = @RAP3));
DELETE FROM intento_quiz WHERE quiz_id IN (SELECT id FROM quiz WHERE rap_id = @RAP3);
DELETE FROM pregunta WHERE quiz_id IN (SELECT id FROM quiz WHERE rap_id = @RAP3);
DELETE FROM quiz WHERE rap_id = @RAP3;

-- 1. VOCABULARIO RAP 3
INSERT INTO vocabulario (id, rap_id, termino_en, termino_es, categoria_id, area_clinica_id, transcripcion_ipa, audio_url, imagen_url, oracion_ejemplo, nivel_dificultad, activo) VALUES
(UUID(), @RAP3, 'Waiting room', 'Sala de espera', @CAT_SUST, @AREA_GEN, '/ˈweɪtɪŋ ruːm/', NULL, NULL, 'The family is in the waiting room.', 'A1', 1),
(UUID(), @RAP3, 'Hospital bed', 'Cama de hospital', @CAT_SUST, @AREA_GEN, '/ˈhɒspɪtl bɛd/', NULL, NULL, 'Mr. Thomas is resting in the hospital bed.', 'A1', 1),
(UUID(), @RAP3, 'Stretcher', 'Camilla', @CAT_SUST, @AREA_GEN, '/ˈstrɛtʃər/', NULL, NULL, 'Paramedics transferred him on a stretcher.', 'A1', 1),
(UUID(), @RAP3, 'Room 204', 'Habitación 204', @CAT_SUST, @AREA_GEN, '/ruːm tuː oʊ fɔːr/', NULL, NULL, 'The patient was transferred to room 204.', 'A1', 1),
(UUID(), @RAP3, 'Cold', 'Frío/a', @CAT_ADJ, @AREA_GEN, '/koʊld/', NULL, NULL, 'The room feels cold, so we gave him a blanket.', 'A1', 1),
(UUID(), @RAP3, 'Stable', 'Estable', @CAT_ADJ, @AREA_GEN, '/ˈsteɪbl/', NULL, NULL, 'His vital signs are stable today.', 'A1', 1),
(UUID(), @RAP3, 'Vital signs', 'Signos vitales', @CAT_SUST, @AREA_GEN, '/ˈvaɪtl saɪnz/', NULL, NULL, 'Check vital signs every four hours.', 'A1', 1),
(UUID(), @RAP3, 'Swollen knee', 'Rodilla inflamada', @CAT_SUST, @AREA_GEN, '/ˈswoʊlən niː/', NULL, NULL, 'He has a swollen knee from the fall.', 'A1', 1);

-- 2. DIÁLOGO RAP 3
SET @dia3 = UUID();
INSERT INTO dialogo (id, rap_id, titulo, contexto, participantes, audio_completo_url, activo) VALUES
(@dia3, @RAP3, 'Room 204 Check: Current Status & Hospital Environment', 'Nurse A and Nurse B check Mr. Thomas in room 204 and evaluate room environment.', 'Nurse A, Nurse B', NULL, 1);

INSERT INTO turno_dialogo (id, dialogo_id, orden_turno, hablante, texto_en, texto_es, audio_url) VALUES
(UUID(), @dia3, 1, 'Nurse A', 'How is Mr. Thomas in room 204 doing right now?', '¿Cómo está Mr. Thomas en la habitación 204 en este momento?', NULL),
(UUID(), @dia3, 2, 'Nurse B', 'He is pale and tired, but his vital signs are stable.', 'Él está pálido y cansado, pero sus signos vitales están estables.', NULL),
(UUID(), @dia3, 3, 'Nurse A', 'Is the room comfortable for him?', '¿La habitación es cómoda para él?', NULL),
(UUID(), @dia3, 4, 'Nurse B', 'The room is cold, so he is resting under a warm blanket.', 'La habitación está fría, así que él está descansando bajo una cobija caliente.', NULL);

-- 3. EJERCICIOS RAP 3
SET @ej1 = UUID();
INSERT INTO ejercicio (id, rap_id, tipo, enunciado, max_intentos, puntos, activo) VALUES
(@ej1, @RAP3, 'completar_frase', 'Current Status: The patient ___ pale and tired today.', 3, 10, 1);
INSERT INTO ejercicio_opcion (id, ejercicio_id, texto, es_correcta, retroalimentacion) VALUES
(UUID(), @ej1, 'is', 1, 'Correct! Present tense "is" describes current status.'),
(UUID(), @ej1, 'was', 0, 'Incorrect. Use present tense for current status.');

SET @ej2 = UUID();
INSERT INTO ejercicio (id, rap_id, tipo, enunciado, max_intentos, puntos, activo) VALUES
(@ej2, @RAP3, 'completar_frase', 'Room Status: The room ___ cold, but signs are stable.', 3, 10, 1);
INSERT INTO ejercicio_opcion (id, ejercicio_id, texto, es_correcta, retroalimentacion) VALUES
(UUID(), @ej2, 'is', 1, 'Correct! Present tense describes room status.'),
(UUID(), @ej2, 'were', 0, 'Incorrect.');

SET @ej3 = UUID();
INSERT INTO ejercicio (id, rap_id, tipo, enunciado, max_intentos, puntos, activo) VALUES
(@ej3, @RAP3, 'arrastrar_soltar', 'Match the hospital environment terms with Spanish meanings:', 3, 10, 1);
INSERT INTO ejercicio_opcion (id, ejercicio_id, texto, es_correcta, retroalimentacion) VALUES
(UUID(), @ej3, 'Waiting room = Sala de espera', 1, 'Correct match!'),
(UUID(), @ej3, 'Hospital bed = Cama de hospital', 1, 'Correct match!'),
(UUID(), @ej3, 'Swollen knee = Rodilla inflamada', 1, 'Correct match!');

SET @ej4 = UUID();
INSERT INTO ejercicio (id, rap_id, tipo, enunciado, max_intentos, puntos, activo) VALUES
(@ej4, @RAP3, 'ordenar_dialogo', 'Order the room check conversation in correct sequence:', 3, 10, 1);
INSERT INTO ejercicio_opcion (id, ejercicio_id, texto, es_correcta, retroalimentacion) VALUES
(UUID(), @ej4, 'Nurse A: How is Mr. Thomas in room 204 doing right now? | Nurse B: He is pale and tired, but his vital signs are stable. | Nurse A: Is the room comfortable for him? | Nurse B: The room is cold, so he is resting under a blanket.', 1, '¡Perfecto!');

SET @ej5 = UUID();
INSERT INTO ejercicio (id, rap_id, tipo, enunciado, max_intentos, puntos, activo) VALUES
(@ej5, @RAP3, 'escucha_escribe', 'Listen to the clinical dictation and type the room number (Hint: 204):', 3, 10, 1);
INSERT INTO ejercicio_opcion (id, ejercicio_id, texto, es_correcta, retroalimentacion) VALUES
(UUID(), @ej5, '204', 1, 'Correct! Room 204.');

-- 4. QUIZ RAP 3
SET @quiz3 = UUID();
INSERT INTO quiz (id, rap_id, puntaje_minimo, limite_tiempo_seg, aleatorizar, max_intentos, activo) VALUES (@quiz3, @RAP3, 60.00, 300, 0, 3, 1);
INSERT INTO pregunta (id, quiz_id, texto, opciones, respuesta_correcta, retroalimentacion) VALUES
(UUID(), @quiz3, 'What is Mr. Thomas\'s current physical status in room 204?', '["Pale and tired, but stable", "Angry and noisy", "Running in the hallway", "Fully recovered"]', 'Pale and tired, but stable', 'Correct! He is pale and tired, but stable.'),
(UUID(), @quiz3, 'Where is the patient resting right now?', '["In room 204 hospital bed", "In the cafeteria", "In the parking lot", "In the elevator"]', 'In room 204 hospital bed', 'Correct! Resting in room 204.'),
(UUID(), @quiz3, 'Where is the patient\'s family waiting?', '["In the waiting room", "In the operating room", "In the pharmacy", "At the airport"]', 'In the waiting room', 'Correct! Family is in the waiting room.');
