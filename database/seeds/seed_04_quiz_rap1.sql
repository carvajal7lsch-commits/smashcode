-- =============================================================
-- SEED PARTE 4: Quiz del RAP 1 — Momento 4: Closure
-- Módulo 1 — "Getting to Know Other People"
-- RAP ID: be5df679-5166-11f1-b275-8c1645fa3d64
-- =============================================================

SET NAMES utf8mb4;
USE smash_code;

SELECT id INTO @RAP1 FROM rap ORDER BY orden ASC LIMIT 1;
SET @QUIZ1 = 'quiz-rap1-0001';

-- 1. Insertar la configuración del Quiz
INSERT INTO quiz (id, rap_id, puntaje_minimo, limite_tiempo_seg, aleatorizar, max_intentos, activo)
VALUES (@QUIZ1, @RAP1, 60.00, 300, 1, 3, 1);

-- 2. Insertar las 5 preguntas (Saludos, Estructura To Be y Números)

-- Pregunta 1: Saludos
INSERT INTO pregunta (id, quiz_id, texto, opciones, respuesta_correcta, retroalimentacion) VALUES
(UUID(), @QUIZ1, 
 'What is the correct greeting for 8:00 AM when you arrive at the clinic?', 
 '["Good evening", "Good afternoon", "Good morning", "Good night"]', 
 'Good morning', 
 '"Good morning" is used from sunrise until noon (12:00 PM).');

-- Pregunta 2: Estructura del Verbo To Be (1ra persona)
INSERT INTO pregunta (id, quiz_id, texto, opciones, respuesta_correcta, retroalimentacion) VALUES
(UUID(), @QUIZ1, 
 'Choose the correct verb to complete the sentence: "I ___ a nurse at this hospital."', 
 '["am", "is", "are", "be"]', 
 'am', 
 'The first person singular "I" always uses the verb "am".');

-- Pregunta 3: Estructura del Verbo To Be (3ra persona)
INSERT INTO pregunta (id, quiz_id, texto, opciones, respuesta_correcta, retroalimentacion) VALUES
(UUID(), @QUIZ1, 
 'Choose the correct verb to complete the sentence: "Dr. Smith ___ the head of the department."', 
 '["are", "am", "is", "were"]', 
 'is', 
 'Third person singular (He/She/It) uses the verb "is".');

-- Pregunta 4: Números (Teléfonos en emergencias)
INSERT INTO pregunta (id, quiz_id, texto, opciones, respuesta_correcta, retroalimentacion) VALUES
(UUID(), @QUIZ1, 
 'A patient gives you their phone number. How do you say the number "8"?', 
 '["Three", "Five", "Eight", "Nine"]', 
 'Eight', 
 'The correct English word for the number 8 is "Eight".');

-- Pregunta 5: Deletreo (Spelling)
INSERT INTO pregunta (id, quiz_id, texto, opciones, respuesta_correcta, retroalimentacion) VALUES
(UUID(), @QUIZ1, 
 'How do you correctly spell the word "Nurse"?', 
 '["N-U-R-C-E", "N-U-R-S-E", "N-O-R-S-E", "M-U-R-S-E"]', 
 'N-U-R-S-E', 
 'The correct spelling is N-U-R-S-E.');
