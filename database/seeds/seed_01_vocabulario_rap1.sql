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

INSERT INTO vocabulario
    (id, rap_id, termino_en, termino_es, categoria_id, area_clinica_id, transcripcion_ipa, audio_url, imagen_url, oracion_ejemplo, nivel_dificultad, activo)
VALUES
-- SALUDOS Y DESPEDIDAS
(UUID(), 'be5df679-5166-11f1-b275-8c1645fa3d64', 'Good morning', 'Buenos días', 'be5f697b-5166-11f1-b275-8c1645fa3d64', 'be5e529c-5166-11f1-b275-8c1645fa3d64', '/ɡʊd ˈmɔːr.nɪŋ/', NULL, NULL, 'Good morning! My name is Sarah, your nurse today.', 'A1', 1),
(UUID(), 'be5df679-5166-11f1-b275-8c1645fa3d64', 'Good afternoon', 'Buenas tardes', 'be5f697b-5166-11f1-b275-8c1645fa3d64', 'be5e529c-5166-11f1-b275-8c1645fa3d64', '/ɡʊd ˌæf.tɚˈnuːn/', NULL, NULL, 'Good afternoon, Mr. Thomas. How are you feeling?', 'A1', 1),
(UUID(), 'be5df679-5166-11f1-b275-8c1645fa3d64', 'Good evening', 'Buenas noches (saludo)', 'be5f697b-5166-11f1-b275-8c1645fa3d64', 'be5e529c-5166-11f1-b275-8c1645fa3d64', '/ɡʊd ˈiːv.nɪŋ/', NULL, NULL, 'Good evening! The night shift has just started.', 'A1', 1),
(UUID(), 'be5df679-5166-11f1-b275-8c1645fa3d64', 'Goodbye', 'Adiós', 'be5f697b-5166-11f1-b275-8c1645fa3d64', 'be5e529c-5166-11f1-b275-8c1645fa3d64', '/ˌɡʊdˈbaɪ/', NULL, NULL, 'Goodbye, Mrs. Rivera. See you tomorrow morning.', 'A1', 1),
(UUID(), 'be5df679-5166-11f1-b275-8c1645fa3d64', 'Nice to meet you', 'Mucho gusto', 'be5f697b-5166-11f1-b275-8c1645fa3d64', 'be5e529c-5166-11f1-b275-8c1645fa3d64', '/naɪs tə miːt juː/', NULL, NULL, 'Nice to meet you. I am the nurse assigned to your room.', 'A1', 1),
(UUID(), 'be5df679-5166-11f1-b275-8c1645fa3d64', 'How are you?', '¿Cómo estás?', 'be5f697b-5166-11f1-b275-8c1645fa3d64', 'be5e529c-5166-11f1-b275-8c1645fa3d64', '/haʊ ɑːr juː/', NULL, NULL, 'How are you feeling today, Mr. Thomas?', 'A1', 1),

-- INFORMACIÓN PERSONAL
(UUID(), 'be5df679-5166-11f1-b275-8c1645fa3d64', 'name', 'nombre', 'be5f5792-5166-11f1-b275-8c1645fa3d64', 'be5e529c-5166-11f1-b275-8c1645fa3d64', '/neɪm/', NULL, NULL, 'My name is Carolina. What is your name?', 'A1', 1),
(UUID(), 'be5df679-5166-11f1-b275-8c1645fa3d64', 'last name', 'apellido', 'be5f5792-5166-11f1-b275-8c1645fa3d64', 'be5e529c-5166-11f1-b275-8c1645fa3d64', '/læst neɪm/', NULL, NULL, 'Please spell your last name for the patient register.', 'A1', 1),
(UUID(), 'be5df679-5166-11f1-b275-8c1645fa3d64', 'age', 'edad', 'be5f5792-5166-11f1-b275-8c1645fa3d64', 'be5e529c-5166-11f1-b275-8c1645fa3d64', '/eɪdʒ/', NULL, NULL, 'What is your age? Are you over eighteen?', 'A1', 1),
(UUID(), 'be5df679-5166-11f1-b275-8c1645fa3d64', 'nationality', 'nacionalidad', 'be5f5792-5166-11f1-b275-8c1645fa3d64', 'be5e529c-5166-11f1-b275-8c1645fa3d64', '/ˌnæʃ.əˈnæl.ɪ.ti/', NULL, NULL, 'What is your nationality? Are you American?', 'A1', 1),
(UUID(), 'be5df679-5166-11f1-b275-8c1645fa3d64', 'phone number', 'número de teléfono', 'be5f5792-5166-11f1-b275-8c1645fa3d64', 'be5e529c-5166-11f1-b275-8c1645fa3d64', '/foʊn ˈnʌm.bɚ/', NULL, NULL, 'Please give me your phone number in case of emergency.', 'A1', 1),
(UUID(), 'be5df679-5166-11f1-b275-8c1645fa3d64', 'email address', 'correo electrónico', 'be5f5792-5166-11f1-b275-8c1645fa3d64', 'be5e529c-5166-11f1-b275-8c1645fa3d64', '/ˈiː.meɪl ˈæd.res/', NULL, NULL, 'Please type your email address in this form.', 'A1', 1),
(UUID(), 'be5df679-5166-11f1-b275-8c1645fa3d64', 'date of birth', 'fecha de nacimiento', 'be5f5792-5166-11f1-b275-8c1645fa3d64', 'be5e529c-5166-11f1-b275-8c1645fa3d64', '/deɪt əv bɜːrθ/', NULL, NULL, 'Please confirm your date of birth for the medical record.', 'A1', 1),

-- VERBO TO BE (Estructuras)
(UUID(), 'be5df679-5166-11f1-b275-8c1645fa3d64', 'I am', 'Yo soy / Yo estoy', 'be5f6831-5166-11f1-b275-8c1645fa3d64', 'be5e529c-5166-11f1-b275-8c1645fa3d64', '/aɪ æm/', NULL, NULL, 'I am Sarah, the nurse in charge of Room 204.', 'A1', 1),
(UUID(), 'be5df679-5166-11f1-b275-8c1645fa3d64', 'You are', 'Tú eres / Usted es', 'be5f6831-5166-11f1-b275-8c1645fa3d64', 'be5e529c-5166-11f1-b275-8c1645fa3d64', '/juː ɑːr/', NULL, NULL, 'You are my patient for this shift.', 'A1', 1),
(UUID(), 'be5df679-5166-11f1-b275-8c1645fa3d64', 'He is', 'Él es / Él está', 'be5f6831-5166-11f1-b275-8c1645fa3d64', 'be5e529c-5166-11f1-b275-8c1645fa3d64', '/hiː ɪz/', NULL, NULL, 'He is the doctor on duty tonight.', 'A1', 1),
(UUID(), 'be5df679-5166-11f1-b275-8c1645fa3d64', 'She is', 'Ella es / Ella está', 'be5f6831-5166-11f1-b275-8c1645fa3d64', 'be5e529c-5166-11f1-b275-8c1645fa3d64', '/ʃiː ɪz/', NULL, NULL, 'She is a patient from the United States.', 'A1', 1);

