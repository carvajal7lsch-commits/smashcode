-- =============================================================
-- SEED PARTE 1: Vocabulario del RAP 1
-- Módulo 1 — "Getting to Know Other People"
-- Ámbito: Saludos, Información Personal, Alfabeto y Números
-- RAP ID: be5df679-5166-11f1-b275-8c1645fa3d64
-- Categoria IDs:
--   Frase      = be5f697b-5166-11f1-b275-8c1645fa3d64
--   Sustantivo = be5f5792-5166-11f1-b275-8c1645fa3d64
--   Verbo      = be5f6831-5166-11f1-b275-8c1645fa3d64
-- Area ID:
--   General    = be5e529c-5166-11f1-b275-8c1645fa3d64
-- =============================================================

SET NAMES utf8mb4;
USE smash_code;

-- Buscar IDs de forma dinamica para evitar errores de Foreign Key (entornos con diferentes UUIDs)
SELECT id INTO @RAP1 FROM rap ORDER BY orden ASC LIMIT 1;
SELECT id INTO @CAT_FRASE FROM categoria_vocabulario WHERE nombre = 'Frase' LIMIT 1;
SELECT id INTO @CAT_SUSTANTIVO FROM categoria_vocabulario WHERE nombre = 'Sustantivo' LIMIT 1;
SELECT id INTO @CAT_VERBO FROM categoria_vocabulario WHERE nombre = 'Verbo' LIMIT 1;
SELECT id INTO @AREA_GENERAL FROM area_clinica WHERE nombre = 'General' LIMIT 1;


INSERT INTO vocabulario
    (id, rap_id, termino_en, termino_es, categoria_id, area_clinica_id, transcripcion_ipa, audio_url, imagen_url, oracion_ejemplo, nivel_dificultad, activo)
VALUES
-- SALUDOS Y DESPEDIDAS
(UUID(), @RAP1, 'Good morning', 'Buenos días', @CAT_FRASE, @AREA_GENERAL, '/ɡʊd ˈmɔːr.nɪŋ/', NULL, NULL, 'Good morning! My name is Sarah, your nurse today.', 'A1', 1),
(UUID(), @RAP1, 'Good afternoon', 'Buenas tardes', @CAT_FRASE, @AREA_GENERAL, '/ɡʊd ˌæf.tɚˈnuːn/', NULL, NULL, 'Good afternoon, Mr. Thomas. How are you feeling?', 'A1', 1),
(UUID(), @RAP1, 'Good evening', 'Buenas noches (saludo)', @CAT_FRASE, @AREA_GENERAL, '/ɡʊd ˈiːv.nɪŋ/', NULL, NULL, 'Good evening! The night shift has just started.', 'A1', 1),
(UUID(), @RAP1, 'Goodbye', 'Adiós', @CAT_FRASE, @AREA_GENERAL, '/ˌɡʊdˈbaɪ/', NULL, NULL, 'Goodbye, Mrs. Rivera. See you tomorrow morning.', 'A1', 1),
(UUID(), @RAP1, 'Nice to meet you', 'Mucho gusto', @CAT_FRASE, @AREA_GENERAL, '/naɪs tə miːt juː/', NULL, NULL, 'Nice to meet you. I am the nurse assigned to your room.', 'A1', 1),
(UUID(), @RAP1, 'How are you?', '¿Cómo estás?', @CAT_FRASE, @AREA_GENERAL, '/haʊ ɑːr juː/', NULL, NULL, 'How are you feeling today, Mr. Thomas?', 'A1', 1),

-- INFORMACIÓN PERSONAL
(UUID(), @RAP1, 'name', 'nombre', @CAT_SUSTANTIVO, @AREA_GENERAL, '/neɪm/', NULL, NULL, 'My name is Carolina. What is your name?', 'A1', 1),
(UUID(), @RAP1, 'last name', 'apellido', @CAT_SUSTANTIVO, @AREA_GENERAL, '/læst neɪm/', NULL, NULL, 'Please spell your last name for the patient register.', 'A1', 1),
(UUID(), @RAP1, 'age', 'edad', @CAT_SUSTANTIVO, @AREA_GENERAL, '/eɪdʒ/', NULL, NULL, 'What is your age? Are you over eighteen?', 'A1', 1),
(UUID(), @RAP1, 'nationality', 'nacionalidad', @CAT_SUSTANTIVO, @AREA_GENERAL, '/ˌnæʃ.əˈnæl.ɪ.ti/', NULL, NULL, 'What is your nationality? Are you American?', 'A1', 1),
(UUID(), @RAP1, 'phone number', 'número de teléfono', @CAT_SUSTANTIVO, @AREA_GENERAL, '/foʊn ˈnʌm.bɚ/', NULL, NULL, 'Please give me your phone number in case of emergency.', 'A1', 1),
(UUID(), @RAP1, 'email address', 'correo electrónico', @CAT_SUSTANTIVO, @AREA_GENERAL, '/ˈiː.meɪl ˈæd.res/', NULL, NULL, 'Please type your email address in this form.', 'A1', 1),
(UUID(), @RAP1, 'date of birth', 'fecha de nacimiento', @CAT_SUSTANTIVO, @AREA_GENERAL, '/deɪt əv bɜːrθ/', NULL, NULL, 'Please confirm your date of birth for the medical record.', 'A1', 1),

-- VERBO TO BE (Estructuras)
(UUID(), @RAP1, 'I am', 'Yo soy / Yo estoy', @CAT_VERBO, @AREA_GENERAL, '/aɪ æm/', NULL, NULL, 'I am Sarah, the nurse in charge of Room 204.', 'A1', 1),
(UUID(), @RAP1, 'You are', 'Tú eres / Usted es', @CAT_VERBO, @AREA_GENERAL, '/juː ɑːr/', NULL, NULL, 'You are my patient for this shift.', 'A1', 1),
(UUID(), @RAP1, 'He is', 'Él es / Él está', @CAT_VERBO, @AREA_GENERAL, '/hiː ɪz/', NULL, NULL, 'He is the doctor on duty tonight.', 'A1', 1),
(UUID(), @RAP1, 'She is', 'Ella es / Ella está', @CAT_VERBO, @AREA_GENERAL, '/ʃiː ɪz/', NULL, NULL, 'She is a patient from the United States.', 'A1', 1);

