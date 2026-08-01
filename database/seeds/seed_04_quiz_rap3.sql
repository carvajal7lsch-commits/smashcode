-- =============================================================
-- SEED PARTE 4: Quiz de Evaluación del RAP 3 (Módulo 3: Work Place Communication)
-- =============================================================

SET NAMES utf8mb4;
USE smash_code;

SELECT id INTO @RAP3 FROM rap WHERE nivel_id IN (SELECT id FROM nivel WHERE orden = 3) LIMIT 1;

-- Limpieza previa
DELETE FROM respuesta_quiz WHERE pregunta_id IN (SELECT id FROM pregunta WHERE quiz_id IN (SELECT id FROM quiz WHERE rap_id = @RAP3));
DELETE FROM intento_quiz WHERE quiz_id IN (SELECT id FROM quiz WHERE rap_id = @RAP3);
DELETE FROM pregunta WHERE quiz_id IN (SELECT id FROM quiz WHERE rap_id = @RAP3);
DELETE FROM quiz WHERE rap_id = @RAP3;

-- Crear el Quiz del RAP 3
SET @quiz3 = UUID();
INSERT INTO quiz (id, rap_id, puntaje_minimo, limite_tiempo_seg, aleatorizar, max_intentos, activo)
VALUES (
    @quiz3,
    @RAP3,
    60.00,
    300,
    0,
    3,
    1
);

-- Pregunta 1: Rutinas en Presente Simple
INSERT INTO pregunta (id, quiz_id, texto, opciones, respuesta_correcta, retroalimentacion) VALUES
(UUID(), @quiz3,
 'Which sentence describes a daily nursing routine in Present Simple?',
 '["I give medication at 8 AM", "I am checking blood pressure right now", "He fell at the hotel yesterday", "We should go home"]',
 'I give medication at 8 AM',
 'Correct! "I give medication at 8 AM" expresses a daily routine.');

-- Pregunta 2: Acciones en el momento (Presente Continuo)
INSERT INTO pregunta (id, quiz_id, texto, opciones, respuesta_correcta, retroalimentacion) VALUES
(UUID(), @quiz3,
 'Which sentence describes an action happening right now (Present Continuous)?',
 '["Nurse Sarah is checking vital signs right now", "I give medication at 8 AM", "He had an accident", "We update checklists daily"]',
 'Nurse Sarah is checking vital signs right now',
 'Correct! "is checking... right now" indicates an action happening at the current moment.');

-- Pregunta 3: Fórmulas de sugerencia clínica
INSERT INTO pregunta (id, quiz_id, texto, opciones, respuesta_correcta, retroalimentacion) VALUES
(UUID(), @quiz3,
 'How do you propose a polite workplace suggestion to improve a checklist?',
 '["We should update the checklist for Mr. Thomas", "I am standing near the bed", "He was pale yesterday", "Give me the stethoscope immediately"]',
 'We should update the checklist for Mr. Thomas',
 'Correct! "We should..." is the appropriate phrase for proposing workplace improvements.');

-- Pregunta 4: Vocabulario de herramientas médicas
INSERT INTO pregunta (id, quiz_id, texto, opciones, respuesta_correcta, retroalimentacion) VALUES
(UUID(), @quiz3,
 'What clinical instrument is used to measure body temperature?',
 '["Thermometer", "Wheelchair", "Bandage", "Syringe"]',
 'Thermometer',
 'Correct! A thermometer measures temperature.');

-- Pregunta 5: Interacción con visitantes
INSERT INTO pregunta (id, quiz_id, texto, opciones, respuesta_correcta, retroalimentacion) VALUES
(UUID(), @quiz3,
 'When explaining a current procedure to a patient\'s family member, what is a polite response?',
 '["We are checking his temperature now.", "Do not speak to me.", "He fell down yesterday.", "Room 204 is closed."]',
 'We are checking his temperature now.',
 'Correct! Explaining current actions using Present Continuous informs visitors politely.');
