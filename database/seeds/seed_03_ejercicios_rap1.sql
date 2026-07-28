-- =============================================================
-- SEED PARTE 3: Ejercicios del RAP 1 — Momento 3: Practice
-- Módulo 1 — "Getting to Know Other People"
-- RAP ID: be5df679-5166-11f1-b275-8c1645fa3d64
-- =============================================================

SET NAMES utf8mb4;
USE smash_code;

-- -------------------------------------------------------
-- IDs fijos para los ejercicios (para poder insertar opciones)
-- -------------------------------------------------------
SET @ej1 = 'ej-rap1-completar-01';
SET @ej2 = 'ej-rap1-completar-02';
SET @ej3 = 'ej-rap1-completar-03';
SET @ej4 = 'ej-rap1-arrastrar-01';
SET @ej5 = 'ej-rap1-escucha-01';
SET @ej6 = 'ej-rap1-roleplay-01';

SET @RAP1 = 'be5df679-5166-11f1-b275-8c1645fa3d64';

-- -------------------------------------------------------
-- EJERCICIO 1: Completar frase — Verbo To Be (ID Card)
-- Práctica Guiada 1: Sujeto + Verbo + Complemento
-- -------------------------------------------------------
INSERT INTO ejercicio (id, rap_id, tipo, enunciado, max_intentos, puntos, activo)
VALUES (@ej1, @RAP1, 'completar_frase',
    'Complete the patient form. Choose the correct verb: "I ___ Carolina Ramírez, the nurse in charge."',
    3, 10, 1);

INSERT INTO ejercicio_opcion (id, ejercicio_id, texto, es_correcta, retroalimentacion) VALUES
(UUID(), @ej1, 'am', 1, '✅ Correct! "I am" is the Subject + Verb To Be structure. I + am = first person singular.'),
(UUID(), @ej1, 'is', 0, '❌ "Is" is used with He / She / It. Use "am" with "I".'),
(UUID(), @ej1, 'are', 0, '❌ "Are" is used with You / We / They. Use "am" with "I".'),
(UUID(), @ej1, 'be', 0, '❌ "Be" is the base form, not used in simple sentences without an auxiliary.');

-- -------------------------------------------------------
-- EJERCICIO 2: Completar frase — Información personal
-- -------------------------------------------------------
INSERT INTO ejercicio (id, rap_id, tipo, enunciado, max_intentos, puntos, activo)
VALUES (@ej2, @RAP1, 'completar_frase',
    'Fill in the ID Card: "You ___ Mr. Thomas, the patient in Room 204."',
    3, 10, 1);

INSERT INTO ejercicio_opcion (id, ejercicio_id, texto, es_correcta, retroalimentacion) VALUES
(UUID(), @ej2, 'are', 1, '✅ Correct! "You are" is the Subject + Verb To Be for second person.'),
(UUID(), @ej2, 'am', 0, '❌ "Am" is only used with "I".'),
(UUID(), @ej2, 'is', 0, '❌ "Is" is for He / She / It, not for "You".'),
(UUID(), @ej2, 'were', 0, '❌ "Were" is past tense. Use present tense here.');

-- -------------------------------------------------------
-- EJERCICIO 3: Completar frase — Presentación clínica
-- -------------------------------------------------------
INSERT INTO ejercicio (id, rap_id, tipo, enunciado, max_intentos, puntos, activo)
VALUES (@ej3, @RAP1, 'completar_frase',
    'Complete the nurse introduction: "She ___ the head nurse of the morning shift."',
    3, 10, 1);

INSERT INTO ejercicio_opcion (id, ejercicio_id, texto, es_correcta, retroalimentacion) VALUES
(UUID(), @ej3, 'is', 1, '✅ Correct! "She is" — third person singular (She / He / It) uses "is".'),
(UUID(), @ej3, 'are', 0, '❌ "Are" is for You / We / They. Use "is" for She / He / It.'),
(UUID(), @ej3, 'am', 0, '❌ "Am" is only for "I".'),
(UUID(), @ej3, 'been', 0, '❌ "Been" is a past participle. This sentence needs present tense.');

-- -------------------------------------------------------
-- EJERCICIO 4: Arrastrar y soltar — Emparejar saludos con momento del día
-- -------------------------------------------------------
INSERT INTO ejercicio (id, rap_id, tipo, enunciado, max_intentos, puntos, activo)
VALUES (@ej4, @RAP1, 'arrastrar_soltar',
    'Match each English greeting with the correct time of day. Drag the greeting to its pair.',
    3, 10, 1);

-- Formato de opciones para arrastrar: "término EN = traducción ES"
INSERT INTO ejercicio_opcion (id, ejercicio_id, texto, es_correcta, retroalimentacion) VALUES
(UUID(), @ej4, 'Good morning = Buenos días (6am - 12pm)', 1, '✅ Correct! "Good morning" is used from sunrise until noon.'),
(UUID(), @ej4, 'Good afternoon = Buenas tardes (12pm - 6pm)', 1, '✅ Correct! "Good afternoon" is used from noon until evening.'),
(UUID(), @ej4, 'Good evening = Buenas noches / saludo (6pm - 12am)', 1, '✅ Correct! "Good evening" is a greeting used after 6pm.'),
(UUID(), @ej4, 'Goodbye = Adiós (despedida)', 1, '✅ Correct! "Goodbye" is used when leaving or ending a conversation.');

-- -------------------------------------------------------
-- EJERCICIO 5: Escucha y escribe — Deletreo de apellido
-- (Simula la práctica de escuchar y escribir información personal)
-- -------------------------------------------------------
INSERT INTO ejercicio (id, rap_id, tipo, enunciado, max_intentos, puntos, activo)
VALUES (@ej5, @RAP1, 'escucha_escribe',
    '🎧 Listening Exercise: Press the play button and listen carefully. The nurse is spelling her last name. Type exactly what you hear letter by letter. (Expected answer: RAMIREZ)',
    3, 10, 1);

INSERT INTO ejercicio_opcion (id, ejercicio_id, texto, es_correcta, retroalimentacion) VALUES
(UUID(), @ej5, 'RAMIREZ', 1, '✅ Excellent! R-A-M-I-R-E-Z. You correctly identified each letter of the last name.'),
(UUID(), @ej5, 'RAMIRÉZ', 0, '❌ Close! Remember, when spelling in English we do not use accent marks. The answer is RAMIREZ.'),
(UUID(), @ej5, 'RAMIRES', 0, '❌ Not quite. The last letter is Z, not S. Listen again: R-A-M-I-R-E-Z.'),
(UUID(), @ej5, 'REMIREZ', 0, '❌ The second letter is A, not E. Listen carefully: R-A-M-I-R-E-Z.');

-- -------------------------------------------------------
-- EJERCICIO 6: Role Play — El Desafío: Presentación clínica
-- (Evidencia de Aprendizaje: indagar info de un paciente extranjero)
-- -------------------------------------------------------
INSERT INTO ejercicio (id, rap_id, tipo, enunciado, max_intentos, puntos, activo)
VALUES (@ej6, @RAP1, 'role_play',
    '🎤 Clinical Challenge — El Desafío: A foreign patient (Mr. Thomas) has arrived at the clinic. You are the admissions nurse. Record yourself (max. 1 minute) greeting Mr. Thomas, introducing yourself, asking for his full name (and spelling it), his phone number, and his date of birth. Use the structure: Subject + Verb To Be + Complement.',
    1, 10, 1);

INSERT INTO ejercicio_opcion (id, ejercicio_id, texto, es_correcta, retroalimentacion) VALUES
(UUID(), @ej6, 'I used correct greetings (Good morning / Good afternoon / Good evening)', 1, 'Using the correct time-appropriate greeting shows clinical professionalism.'),
(UUID(), @ej6, 'I introduced myself using "I am [name], your nurse"', 1, 'The Subject + Verb To Be structure is the foundation of self-introduction.'),
(UUID(), @ej6, 'I asked for the patient name and spelled it back correctly', 1, 'Confirming spelling is a critical skill in clinical documentation.'),
(UUID(), @ej6, 'I asked for the phone number and date of birth', 1, 'Collecting personal data accurately is essential for patient registration.');
