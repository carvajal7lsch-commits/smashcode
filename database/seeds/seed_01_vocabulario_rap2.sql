-- =============================================================
-- SEED PARTE 1: Vocabulario del RAP 2 (Módulo 2: Work Life Interaction)
-- Ámbito: Partes del cuerpo, Entorno Hospitalario, Pasado Simple y Adjetivos Descriptivos
-- =============================================================

SET NAMES utf8mb4;
USE smash_code;

-- Buscar IDs dinámicamente
SELECT id INTO @RAP2 FROM rap WHERE nivel_id IN (SELECT id FROM nivel WHERE orden = 2) LIMIT 1;
SELECT id INTO @CAT_SUSTANTIVO FROM categoria_vocabulario WHERE nombre = 'Sustantivo' LIMIT 1;
SELECT id INTO @CAT_VERBO FROM categoria_vocabulario WHERE nombre = 'Verbo' LIMIT 1;
SELECT id INTO @CAT_ADJETIVO FROM categoria_vocabulario WHERE nombre = 'Adjetivo' LIMIT 1;
SELECT id INTO @AREA_URGENCIAS FROM area_clinica WHERE nombre LIKE '%Urgencias%' LIMIT 1;
SELECT id INTO @AREA_GENERAL FROM area_clinica WHERE nombre LIKE '%General%' LIMIT 1;

-- Limpieza previa
DELETE FROM vocabulario WHERE rap_id = @RAP2;

INSERT INTO vocabulario
    (id, rap_id, termino_en, termino_es, categoria_id, area_clinica_id, transcripcion_ipa, audio_url, imagen_url, oracion_ejemplo, nivel_dificultad, activo)
VALUES
-- ANATOMÍA Y PARTES DEL CUERPO
(UUID(), @RAP2, 'head', 'cabeza', @CAT_SUSTANTIVO, @AREA_GENERAL, '/hed/', NULL, NULL, 'The patient has a minor injury on his head.', 'A2', 1),
(UUID(), @RAP2, 'arm', 'brazo', @CAT_SUSTANTIVO, @AREA_GENERAL, '/ɑːrm/', NULL, NULL, 'Mr. Thomas has a fracture in his right arm.', 'A2', 1),
(UUID(), @RAP2, 'leg', 'pierna', @CAT_SUSTANTIVO, @AREA_GENERAL, '/leɡ/', NULL, NULL, 'He has a bandage on his left leg.', 'A2', 1),
(UUID(), @RAP2, 'knee', 'rodilla', @CAT_SUSTANTIVO, @AREA_GENERAL, '/niː/', NULL, NULL, 'His knee is swollen after the fall.', 'A2', 1),
(UUID(), @RAP2, 'shoulder', 'hombro', @CAT_SUSTANTIVO, @AREA_GENERAL, '/ˈʃoʊl.dɚ/', NULL, NULL, 'Check the movement of his right shoulder.', 'A2', 1),
(UUID(), @RAP2, 'chest', 'pecho', @CAT_SUSTANTIVO, @AREA_GENERAL, '/tʃest/', NULL, NULL, 'The patient reports no chest pain today.', 'A2', 1),

-- ENTORNO HOSPITALARIO
(UUID(), @RAP2, 'waiting room', 'sala de espera', @CAT_SUSTANTIVO, @AREA_URGENCIAS, '/ˈweɪ.tɪŋ ˌruːm/', NULL, NULL, 'The family is waiting in the waiting room.', 'A2', 1),
(UUID(), @RAP2, 'hospital bed', 'cama de hospital', @CAT_SUSTANTIVO, @AREA_URGENCIAS, '/ˈhɑː.spɪ.təl bed/', NULL, NULL, 'Mr. Thomas is resting in his hospital bed.', 'A2', 1),
(UUID(), @RAP2, 'stretcher', 'camilla', @CAT_SUSTANTIVO, @AREA_URGENCIAS, '/ˈstretʃ.ɚ/', NULL, NULL, 'The paramedics brought the patient on a stretcher.', 'A2', 1),
(UUID(), @RAP2, 'emergency room', 'sala de urgencias', @CAT_SUSTANTIVO, @AREA_URGENCIAS, '/ɪˈmɝː.dʒən.si ˌruːm/', NULL, NULL, 'He arrived at the emergency room yesterday.', 'A2', 1),
(UUID(), @RAP2, 'room 204', 'habitación 204', @CAT_SUSTANTIVO, @AREA_GENERAL, '/ruːm tuː oʊ fɔːr/', NULL, NULL, 'Mr. Thomas was transferred to room 204.', 'A2', 1),

-- ESTADO CLÍNICO Y PASADO SIMPLE
(UUID(), @RAP2, 'pale', 'pálido', @CAT_ADJETIVO, @AREA_URGENCIAS, '/peɪl/', NULL, NULL, 'The patient looks pale and tired today.', 'A2', 1),
(UUID(), @RAP2, 'tired', 'cansado', @CAT_ADJETIVO, @AREA_GENERAL, '/taɪərd/', NULL, NULL, 'He feels tired after the medical examination.', 'A2', 1),
(UUID(), @RAP2, 'cold', 'frío', @CAT_ADJETIVO, @AREA_GENERAL, '/koʊld/', NULL, NULL, 'The patient said the room is cold.', 'A2', 1),
(UUID(), @RAP2, 'swollen', 'inflamado', @CAT_ADJETIVO, @AREA_URGENCIAS, '/ˈswoʊ.lən/', NULL, NULL, 'His knee is swollen and painful.', 'A2', 1),
(UUID(), @RAP2, 'dizzy', 'mareado', @CAT_ADJETIVO, @AREA_URGENCIAS, '/ˈdɪz.i/', NULL, NULL, 'He felt dizzy before he fell.', 'A2', 1),
(UUID(), @RAP2, 'fell', 'se cayó (pasado)', @CAT_VERBO, @AREA_URGENCIAS, '/fel/', NULL, NULL, 'Yesterday, Mr. Thomas fell at the hotel.', 'A2', 1),
(UUID(), @RAP2, 'fracture', 'fractura', @CAT_SUSTANTIVO, @AREA_URGENCIAS, '/ˈfræk.tʃɚ/', NULL, NULL, 'The X-ray confirmed an arm fracture.', 'A2', 1);