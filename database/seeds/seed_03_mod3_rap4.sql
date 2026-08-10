-- =============================================================
-- SEED MÓDULO 3 - RAP 4: Interacción con Visitantes y Presente Continuo (Parte A)
-- =============================================================
SET NAMES utf8mb4;
USE smash_code;

-- Obtener ID del RAP 4 (Módulo 3, Orden 1)
SELECT r.id INTO @RAP4 FROM rap r JOIN nivel n ON r.nivel_id = n.id WHERE n.orden = 3 AND r.orden = 1 LIMIT 1;
SELECT id INTO @CAT_SUST FROM categoria_vocabulario WHERE nombre = 'Sustantivo' LIMIT 1;
SELECT id INTO @CAT_VERB FROM categoria_vocabulario WHERE nombre = 'Verbo' LIMIT 1;
SELECT id INTO @CAT_ADJ FROM categoria_vocabulario WHERE nombre = 'Adjetivo' LIMIT 1;
SELECT id INTO @AREA_GEN FROM area_clinica WHERE nombre LIKE '%General%' LIMIT 1;

-- Limpieza previa de RAP 4
DELETE FROM vocabulario WHERE rap_id = @RAP4;
DELETE FROM turno_dialogo WHERE dialogo_id IN (SELECT id FROM dialogo WHERE rap_id = @RAP4);
DELETE FROM dialogo WHERE rap_id = @RAP4;
DELETE FROM ejercicio_opcion WHERE ejercicio_id IN (SELECT id FROM ejercicio WHERE rap_id = @RAP4);
DELETE FROM ejercicio WHERE rap_id = @RAP4;
DELETE FROM respuesta_quiz WHERE pregunta_id IN (SELECT id FROM pregunta WHERE quiz_id IN (SELECT id FROM quiz WHERE rap_id = @RAP4));
DELETE FROM intento_quiz WHERE quiz_id IN (SELECT id FROM quiz WHERE rap_id = @RAP4);
DELETE FROM pregunta WHERE quiz_id IN (SELECT id FROM quiz WHERE rap_id = @RAP4);
DELETE FROM quiz WHERE rap_id = @RAP4;

-- 1. VOCABULARIO RAP 4
INSERT INTO vocabulario (id, rap_id, termino_en, termino_es, categoria_id, area_clinica_id, transcripcion_ipa, audio_url, imagen_url, oracion_ejemplo, nivel_dificultad, activo) VALUES
(UUID(), @RAP4, 'Thermometer', 'Termómetro', @CAT_SUST, @AREA_GEN, '/θərˈmɒmɪtər/', NULL, NULL, 'We use the thermometer to check his temperature.', 'A1', 1),
(UUID(), @RAP4, 'Stethoscope', 'Estetoscopio', @CAT_SUST, @AREA_GEN, '/ˈstɛθəskoʊp/', NULL, NULL, 'Listen to his chest with the stethoscope.', 'A1', 1),
(UUID(), @RAP4, 'Blood pressure monitor', 'Monitor de presión arterial', @CAT_SUST, @AREA_GEN, '/blʌd ˈprɛʃər ˈmɒnɪtər/', NULL, NULL, 'The blood pressure monitor is active right now.', 'A1', 1),
(UUID(), @RAP4, 'Pulse oximeter', 'Pulsioxímetro', @CAT_SUST, @AREA_GEN, '/pʌls ɒkˈsɪmɪtər/', NULL, NULL, 'The pulse oximeter shows oxygen saturation.', 'A1', 1),
(UUID(), @RAP4, 'Visitor', 'Visitante / Familiar', @CAT_SUST, @AREA_GEN, '/ˈvɪzɪtər/', NULL, NULL, 'Greet the visitor politely in the room.', 'A1', 1),
(UUID(), @RAP4, 'Procedure', 'Procedimiento', @CAT_SUST, @AREA_GEN, '/prəˈsiːdʒər/', NULL, NULL, 'Explain the routine procedure to the visitor.', 'A1', 1),
(UUID(), @RAP4, 'Checking temperature', 'Midiendo la temperatura', @CAT_VERB, @AREA_GEN, '/ˈtʃɛkɪŋ ˈtɛmprətʃər/', NULL, NULL, 'Nurse Sarah is checking temperature right now.', 'A1', 1),
(UUID(), @RAP4, 'Talk to family', 'Hablar con la familia', @CAT_VERB, @AREA_GEN, '/tɔːk tuː ˈfæmɪli/', NULL, NULL, 'We talk to family members about patient progress.', 'A1', 1);

-- 2. DIÁLOGO RAP 4
SET @dia4 = UUID();
INSERT INTO dialogo (id, rap_id, titulo, contexto, participantes, audio_completo_url, activo) VALUES
(@dia4, @RAP4, 'Visitor Explanation: Explaining Current Care', 'Nurse Sarah politely explains current procedures to Mr. Thomas\'s daughter.', 'Nurse Sarah, Visitor', NULL, 1);

INSERT INTO turno_dialogo (id, dialogo_id, orden_turno, hablante, texto_en, texto_es, audio_url) VALUES
(UUID(), @dia4, 1, 'Visitor', 'Excuse me, nurse, what are you doing with my father right now?', 'Disculpe, enfermera, ¿qué le está haciendo a mi padre en este momento?', NULL),
(UUID(), @dia4, 2, 'Nurse Sarah', 'Good morning. We are checking his temperature and blood pressure right now.', 'Buenos días. Le estamos midiendo la temperatura y la presión arterial ahora mismo.', NULL),
(UUID(), @dia4, 3, 'Visitor', 'Thank you for explaining. Is he doing well?', 'Gracias por explicarme. ¿Él se encuentra bien?', NULL),
(UUID(), @dia4, 4, 'Nurse Sarah', 'Yes, his vital signs are stable and he is resting comfortably.', 'Sí, sus signos vitales están estables y él está descansando cómodamente.', NULL);

-- 3. EJERCICIOS RAP 4
SET @ej1 = UUID();
INSERT INTO ejercicio (id, rap_id, tipo, enunciado, max_intentos, puntos, activo) VALUES
(@ej1, @RAP4, 'completar_frase', 'Happening Now: Right now, Nurse Sarah ___ checking his blood pressure.', 3, 10, 1);
INSERT INTO ejercicio_opcion (id, ejercicio_id, texto, es_correcta, retroalimentacion) VALUES
(UUID(), @ej1, 'is', 1, 'Correct! Present continuous uses "is checking".'),
(UUID(), @ej1, 'was', 0, 'Incorrect. Use present continuous for actions happening now.');

SET @ej2 = UUID();
INSERT INTO ejercicio (id, rap_id, tipo, enunciado, max_intentos, puntos, activo) VALUES
(@ej2, @RAP4, 'seleccion_multiple', 'What is Nurse Sarah doing when the visitor enters?', 3, 10, 1);
INSERT INTO ejercicio_opcion (id, ejercicio_id, texto, es_correcta, retroalimentacion) VALUES
(UUID(), @ej2, 'She is checking his temperature and blood pressure.', 1, 'Correct! She is checking vital signs.'),
(UUID(), @ej2, 'She is sleeping in the bed.', 0, 'Incorrect.'),
(UUID(), @ej2, 'She is leaving the hospital.', 0, 'Incorrect.');

SET @ej3 = UUID();
INSERT INTO ejercicio (id, rap_id, tipo, enunciado, max_intentos, puntos, activo) VALUES
(@ej3, @RAP4, 'arrastrar_soltar', 'Match the clinical instruments with their Spanish meanings:', 3, 10, 1);
INSERT INTO ejercicio_opcion (id, ejercicio_id, texto, es_correcta, retroalimentacion) VALUES
(UUID(), @ej3, 'Thermometer = Termómetro', 1, 'Correct match!'),
(UUID(), @ej3, 'Stethoscope = Estetoscopio', 1, 'Correct match!'),
(UUID(), @ej3, 'Pulse oximeter = Pulsioxímetro', 1, 'Correct match!');

SET @ej4 = UUID();
INSERT INTO ejercicio (id, rap_id, tipo, enunciado, max_intentos, puntos, activo) VALUES
(@ej4, @RAP4, 'ordenar_dialogo', 'Order the visitor conversation in correct sequence:', 3, 10, 1);
INSERT INTO ejercicio_opcion (id, ejercicio_id, texto, es_correcta, retroalimentacion) VALUES
(UUID(), @ej4, 'Visitor: Excuse me, nurse, what procedure are you doing right now? | Nurse Sarah: We are checking his temperature and blood pressure right now. | Visitor: Thank you for explaining. Is he doing well? | Nurse Sarah: Yes, his vital signs are stable and he is resting.', 1, '¡Perfecto!');

SET @ej5 = UUID();
INSERT INTO ejercicio (id, rap_id, tipo, enunciado, max_intentos, puntos, activo) VALUES
(@ej5, @RAP4, 'escucha_escribe', 'Listen to the clinical dictation and type the instrument name (Hint: temperature):', 3, 10, 1);
INSERT INTO ejercicio_opcion (id, ejercicio_id, texto, es_correcta, retroalimentacion) VALUES
(UUID(), @ej5, 'thermometer', 1, 'Correct! "thermometer" is the instrument.');

-- 4. QUIZ RAP 4
SET @quiz4 = UUID();
INSERT INTO quiz (id, rap_id, puntaje_minimo, limite_tiempo_seg, aleatorizar, max_intentos, activo) VALUES (@quiz4, @RAP4, 60.00, 300, 0, 3, 1);
INSERT INTO pregunta (id, quiz_id, texto, opciones, respuesta_correcta, retroalimentacion) VALUES
(UUID(), @quiz4, 'Which sentence expresses an action happening right now (Present Continuous)?', '["We are checking his temperature now", "I give medication at 8 AM", "He fell yesterday", "We should update checklists"]', 'We are checking his temperature now', 'Correct! "are checking... now" is present continuous.'),
(UUID(), @quiz4, 'What clinical tool measures oxygen saturation on a finger?', '["Pulse oximeter", "Stethoscope", "Wheelchair", "Bandage"]', 'Pulse oximeter', 'Correct! Pulse oximeter.'),
(UUID(), @quiz4, 'How do you politely answer a visitor asking about care in progress?', '["We are checking his vital signs right now.", "Go away.", "He fell down yesterday.", "Room 204 is busy."]', 'We are checking his vital signs right now.', 'Correct! Politely explain using present continuous.');
