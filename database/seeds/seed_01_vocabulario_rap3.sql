-- =============================================================
-- SEED PARTE 1: Vocabulario del RAP 3 (Módulo 3: Work Place Communication)
-- Ámbito: Herramientas clínicas, acciones rutinarias y verbos de trabajo
-- =============================================================

SET NAMES utf8mb4;
USE smash_code;

-- Buscar IDs dinámicamente
SELECT id INTO @RAP3 FROM rap WHERE nivel_id IN (SELECT id FROM nivel WHERE orden = 3) LIMIT 1;
SELECT id INTO @CAT_SUSTANTIVO FROM categoria_vocabulario WHERE nombre = 'Sustantivo' LIMIT 1;
SELECT id INTO @CAT_VERBO FROM categoria_vocabulario WHERE nombre = 'Verbo' LIMIT 1;
SELECT id INTO @CAT_ADJETIVO FROM categoria_vocabulario WHERE nombre = 'Adjetivo' LIMIT 1;
SELECT id INTO @AREA_URGENCIAS FROM area_clinica WHERE nombre LIKE '%Urgencias%' LIMIT 1;
SELECT id INTO @AREA_GENERAL FROM area_clinica WHERE nombre LIKE '%General%' LIMIT 1;

-- Limpieza previa
DELETE FROM vocabulario WHERE rap_id = @RAP3;

INSERT INTO vocabulario
    (id, rap_id, termino_en, termino_es, categoria_id, area_clinica_id, transcripcion_ipa, audio_url, imagen_url, oracion_ejemplo, nivel_dificultad, activo)
VALUES
-- HERRAMIENTAS E INSTRUMENTOS CLÍNICOS
(UUID(), @RAP3, 'Thermometer', 'Termómetro', @CAT_SUSTANTIVO, @AREA_GENERAL, '/θərˈmɒmɪtər/', NULL, NULL, 'Use the thermometer to check Mr. Thomas\'s temperature.', 'A1', 1),
(UUID(), @RAP3, 'Stethoscope', 'Estetoscopio', @CAT_SUSTANTIVO, @AREA_GENERAL, '/ˈstɛθəskoʊp/', NULL, NULL, 'Listen to his chest using the stethoscope.', 'A1', 1),
(UUID(), @RAP3, 'Blood pressure monitor', 'Monitor de presión arterial', @CAT_SUSTANTIVO, @AREA_URGENCIAS, '/blʌd ˈprɛʃər ˈmɒnɪtər/', NULL, NULL, 'The blood pressure monitor is on the bedside table.', 'A1', 1),
(UUID(), @RAP3, 'Checklist', 'Lista de chequeo', @CAT_SUSTANTIVO, @AREA_GENERAL, '/ˈtʃɛklɪst/', NULL, NULL, 'Fill out the nursing checklist for this shift.', 'A1', 1),
(UUID(), @RAP3, 'Syringe', 'Jeringa', @CAT_SUSTANTIVO, @AREA_URGENCIAS, '/sɪˈrɪndʒ/', NULL, NULL, 'Prepare a sterile syringe for the injection.', 'A1', 1),
(UUID(), @RAP3, 'IV Drip', 'Goteo intravenoso', @CAT_SUSTANTIVO, @AREA_URGENCIAS, '/aɪ viː drɪp/', NULL, NULL, 'The IV drip is infusing saline solution properly.', 'A1', 1),
(UUID(), @RAP3, 'Pulse oximeter', 'Pulsioxímetro', @CAT_SUSTANTIVO, @AREA_URGENCIAS, '/pʌls ɒkˈsɪmɪtər/', NULL, NULL, 'Attach the pulse oximeter to measure oxygen saturation.', 'A1', 1),
(UUID(), @RAP3, 'Bandage', 'Vendaje', @CAT_SUSTANTIVO, @AREA_GENERAL, '/ˈbændɪdʒ/', NULL, NULL, 'Change the bandage on his right arm every morning.', 'A1', 1),
(UUID(), @RAP3, 'Wheelchair', 'Silla de ruedas', @CAT_SUSTANTIVO, @AREA_GENERAL, '/ˈwiːltʃɛər/', NULL, NULL, 'Transport the patient using a comfortable wheelchair.', 'A1', 1),
(UUID(), @RAP3, 'Glucometer', 'Gluciómetro', @CAT_SUSTANTIVO, @AREA_GENERAL, '/ɡluːˈkɒmɪtər/', NULL, NULL, 'Use the glucometer to measure blood glucose level.', 'A1', 1),

-- ACCIONES RUTINARIAS Y VERBOS
(UUID(), @RAP3, 'Give medication', 'Administrar medicamento', @CAT_VERBO, @AREA_GENERAL, '/ɡɪv ˌmɛdɪˈkeɪʃən/', NULL, NULL, 'I give medication at 8 AM as part of my daily routine.', 'A1', 1),
(UUID(), @RAP3, 'Check vital signs', 'Verificar signos vitales', @CAT_VERBO, @AREA_GENERAL, '/tʃɛk ˈvaɪtəl saɪnz/', NULL, NULL, 'Nurse Sarah is checking vital signs right now.', 'A1', 1),
(UUID(), @RAP3, 'Talk to family', 'Hablar con la familia', @CAT_VERBO, @AREA_GENERAL, '/tɔːk tuː ˈfæmɪli/', NULL, NULL, 'We talk to family members to explain routine care.', 'A1', 1),
(UUID(), @RAP3, 'Routine', 'Rutina', @CAT_SUSTANTIVO, @AREA_GENERAL, '/ruːˈtiːn/', NULL, NULL, 'Daily routine includes morning rounds and medication.', 'A1', 1),
(UUID(), @RAP3, 'Procedure', 'Procedimiento', @CAT_SUSTANTIVO, @AREA_GENERAL, '/prəˈsiːdʒər/', NULL, NULL, 'Explain the routine procedure to the visitor.', 'A1', 1),
(UUID(), @RAP3, 'Nurse Manager', 'Jefe de enfermería', @CAT_SUSTANTIVO, @AREA_GENERAL, '/nɜːrs ˈmænɪdʒər/', NULL, NULL, 'Propose an improvement to the Nurse Manager.', 'A1', 1);
