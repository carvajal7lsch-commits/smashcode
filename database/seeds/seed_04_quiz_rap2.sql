-- =============================================================
-- SEED PARTE 4: Quiz de Evaluación del RAP 2 (Módulo 2)
-- =============================================================

SET NAMES utf8mb4;
USE smash_code;

SELECT id INTO @RAP2 FROM rap WHERE nivel_id IN (SELECT id FROM nivel WHERE orden = 2) LIMIT 1;

-- Limpieza previa
DELETE FROM respuesta_quiz WHERE pregunta_id IN (SELECT id FROM pregunta WHERE quiz_id IN (SELECT id FROM quiz WHERE rap_id = @RAP2));
DELETE FROM intento_quiz WHERE quiz_id IN (SELECT id FROM quiz WHERE rap_id = @RAP2);
DELETE FROM pregunta WHERE quiz_id IN (SELECT id FROM quiz WHERE rap_id = @RAP2);
DELETE FROM quiz WHERE rap_id = @RAP2;

-- Crear el Quiz del RAP 2
SET @quiz2 = UUID();
INSERT INTO quiz (id, rap_id, puntaje_minimo, limite_tiempo_seg, aleatorizar, max_intentos, activo)
VALUES (
    @quiz2,
    @RAP2,
    60.00,
    300,
    0,
    3,
    1
);

-- Pregunta 1: Entrega de turno
INSERT INTO pregunta (id, quiz_id, texto, opciones, respuesta_correcta, retroalimentacion) VALUES
(UUID(), @quiz2,
 'In the shift handover report, where did Mr. Thomas fall yesterday?',
 '["In the emergency room", "At his hotel", "In the hospital hallway", "In room 204"]',
 'At his hotel',
 'Correct! The handover report specifies he fell at the hotel before admission.');

-- Pregunta 2: Pasado simple
INSERT INTO pregunta (id, quiz_id, texto, opciones, respuesta_correcta, retroalimentacion) VALUES
(UUID(), @quiz2,
 'Choose the correct past tense verb: "Yesterday the patient ___ an accident."',
 '["has", "had", "is", "have"]',
 'had',
 'Correct! "Had" is the simple past form of "have".');

-- Pregunta 3: Adjetivo descriptivo
INSERT INTO pregunta (id, quiz_id, texto, opciones, respuesta_correcta, retroalimentacion) VALUES
(UUID(), @quiz2,
 'What descriptive adjective explains a patient with pale skin and low energy?',
 '["Strong", "Tired and pale", "Angry", "Noisy"]',
 'Tired and pale',
 'Correct! "Pale" and "tired" describe clinical appearance and low energy.');

-- Pregunta 4: Vocabulario de anatomía
INSERT INTO pregunta (id, quiz_id, texto, opciones, respuesta_correcta, retroalimentacion) VALUES
(UUID(), @quiz2,
 'Where is Mr. Thomas''s fracture located?',
 '["On his left knee", "In his right arm", "On his ankle", "On his forehead"]',
 'In his right arm',
 'Correct! The report states he suffered a fracture in his right arm.');

-- Pregunta 5: Entorno hospitalario
INSERT INTO pregunta (id, quiz_id, texto, opciones, respuesta_correcta, retroalimentacion) VALUES
(UUID(), @quiz2,
 'Where was Mr. Thomas transferred to after the emergency room?',
 '["Room 204", "The waiting room", "The cafeteria", "The pharmacy"]',
 'Room 204',
 'Correct! He was transferred to room 204.');