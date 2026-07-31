-- =============================================================
-- SEED PARTE 3: Ejercicios del RAP 2 (Módulo 2: Work Life Interaction)
-- =============================================================

SET NAMES utf8mb4;
USE smash_code;

SELECT id INTO @RAP2 FROM rap WHERE nivel_id IN (SELECT id FROM nivel WHERE orden = 2) LIMIT 1;

-- Limpieza previa
DELETE FROM ejercicio_opcion WHERE ejercicio_id IN (SELECT id FROM ejercicio WHERE rap_id = @RAP2);
DELETE FROM ejercicio WHERE rap_id = @RAP2;

-- Ejercicio 1: Selección múltiple antecedente
SET @ej1 = UUID();
INSERT INTO ejercicio (id, rap_id, tipo, enunciado, max_intentos, puntos, activo)
VALUES (@ej1, @RAP2, 'seleccion_multiple', 'What happened to Mr. Thomas yesterday before arriving at the hospital?', 3, 10, 1);

INSERT INTO ejercicio_opcion (id, ejercicio_id, texto, es_correcta, retroalimentacion) VALUES
(UUID(), @ej1, 'He fell at the hotel.', 1, 'Correct! Mr. Thomas fell yesterday at his hotel before admission.'),
(UUID(), @ej1, 'He was working at the office.', 0, 'Incorrect. The handover report states he fell at the hotel.'),
(UUID(), @ej1, 'He traveled by airplane.', 0, 'Incorrect. He was staying at the hotel.'),
(UUID(), @ej1, 'He had surgery last week.', 0, 'Incorrect. His admission was due to a fall yesterday.');

-- Ejercicio 2: Completar frase - Estado actual
SET @ej2 = UUID();
INSERT INTO ejercicio (id, rap_id, tipo, enunciado, max_intentos, puntos, activo)
VALUES (@ej2, @RAP2, 'completar_frase', 'Current Status: The patient ___ pale and tired today.', 3, 10, 1);

INSERT INTO ejercicio_opcion (id, ejercicio_id, texto, es_correcta, retroalimentacion) VALUES
(UUID(), @ej2, 'is', 1, 'Correct! Present tense "is" describes current patient status.'),
(UUID(), @ej2, 'was', 0, '"Was" is past tense. Use present tense for current status.'),
(UUID(), @ej2, 'fell', 0, '"Fell" is the action that happened yesterday, not the present state.'),
(UUID(), @ej2, 'are', 0, '"Are" is for plural subjects. "The patient" is singular (is).');

-- Ejercicio 3: Completar frase - Antecedente pasado
SET @ej3 = UUID();
INSERT INTO ejercicio (id, rap_id, tipo, enunciado, max_intentos, puntos, activo)
VALUES (@ej3, @RAP2, 'completar_frase', 'Patient History: Yesterday, Mr. Thomas ___ down in his room.', 3, 10, 1);

INSERT INTO ejercicio_opcion (id, ejercicio_id, texto, es_correcta, retroalimentacion) VALUES
(UUID(), @ej3, 'fell', 1, 'Correct! "Fell" is the simple past of "fall".'),
(UUID(), @ej3, 'falls', 0, 'Use past tense "fell" for an action completed yesterday.'),
(UUID(), @ej3, 'is', 0, '"Is" is present tense. Yesterday requires past tense.'),
(UUID(), @ej3, 'feeling', 0, '"Feeling" is a participle, not a past verb.');

-- Ejercicio 4: Arrastrar y soltar parejas clínicas
SET @ej4 = UUID();
INSERT INTO ejercicio (id, rap_id, tipo, enunciado, max_intentos, puntos, activo)
VALUES (@ej4, @RAP2, 'arrastrar_soltar', 'Match the clinical terms with their Spanish meanings:', 3, 10, 1);

INSERT INTO ejercicio_opcion (id, ejercicio_id, texto, es_correcta, retroalimentacion) VALUES
(UUID(), @ej4, 'Waiting room = Sala de espera', 1, 'Correct match!'),
(UUID(), @ej4, 'Hospital bed = Cama de hospital', 1, 'Correct match!'),
(UUID(), @ej4, 'Right arm = Brazo derecho', 1, 'Correct match!'),
(UUID(), @ej4, 'Swollen knee = Rodilla inflamada', 1, 'Correct match!');

-- Ejercicio 5: Ordenar diálogo de entrega de turno
SET @ej5 = UUID();
INSERT INTO ejercicio (id, rap_id, tipo, enunciado, max_intentos, puntos, activo)
VALUES (@ej5, @RAP2, 'ordenar_dialogo', 'Order the shift handover lines in correct chronological sequence:', 3, 10, 1);

INSERT INTO ejercicio_opcion (id, ejercicio_id, texto, es_correcta, retroalimentacion) VALUES
(UUID(), @ej5, 'Nurse A: How is Mr. Thomas in room 204? | Nurse B: He feels tired. Yesterday he fell at the hotel. | Nurse A: Did he suffer any fracture? | Nurse B: Yes, he has a fracture in his right arm.', 1, '¡Perfecto!');

-- Ejercicio 6: Escucha y escribe (dictado)
SET @ej6 = UUID();
INSERT INTO ejercicio (id, rap_id, tipo, enunciado, max_intentos, puntos, activo)
VALUES (@ej6, @RAP2, 'escucha_escribe', 'Listen to the diagnosis and type the key medical term (Hint: broken bone):', 3, 10, 1);

INSERT INTO ejercicio_opcion (id, ejercicio_id, texto, es_correcta, retroalimentacion) VALUES
(UUID(), @ej6, 'fracture', 1, 'Correct! "fracture" is the diagnosis.');