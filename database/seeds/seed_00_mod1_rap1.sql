-- =============================================================
-- SEED MÓDULO 1 - RAP 1: Presentaciones e Información Personal
-- =============================================================
SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;
SET CHARACTER SET utf8mb4;
USE smash_code;

-- Obtener ID del RAP 1 (Módulo 1, Orden 1)
SET @NIV1 = (SELECT id FROM nivel WHERE orden = 1 LIMIT 1);
INSERT INTO rap (id, nivel_id, titulo, orden, activo)
SELECT UUID(), @NIV1, 'RAP 1: Presentaciones e Información Personal', 1, 1
WHERE NOT EXISTS (SELECT 1 FROM rap WHERE nivel_id = @NIV1 AND orden = 1);

SELECT r.id INTO @RAP1 FROM rap r WHERE r.nivel_id = @NIV1 AND r.orden = 1 LIMIT 1;
SELECT id INTO @CAT_SUST FROM categoria_vocabulario WHERE nombre = 'Sustantivo' LIMIT 1;
SELECT id INTO @CAT_VERB FROM categoria_vocabulario WHERE nombre = 'Verbo' LIMIT 1;
SELECT id INTO @CAT_ADJ FROM categoria_vocabulario WHERE nombre = 'Adjetivo' LIMIT 1;
SELECT id INTO @AREA_GEN FROM area_clinica WHERE nombre LIKE '%General%' LIMIT 1;

-- Limpieza previa de RAP 1
DELETE FROM vocabulario WHERE rap_id = @RAP1;
DELETE FROM turno_dialogo WHERE dialogo_id IN (SELECT id FROM dialogo WHERE rap_id = @RAP1);
DELETE FROM dialogo WHERE rap_id = @RAP1;
DELETE FROM ejercicio_opcion WHERE ejercicio_id IN (SELECT id FROM ejercicio WHERE rap_id = @RAP1);
DELETE FROM ejercicio WHERE rap_id = @RAP1;
DELETE FROM respuesta_quiz WHERE pregunta_id IN (SELECT id FROM pregunta WHERE quiz_id IN (SELECT id FROM quiz WHERE rap_id = @RAP1));
DELETE FROM intento_quiz WHERE quiz_id IN (SELECT id FROM quiz WHERE rap_id = @RAP1);
DELETE FROM pregunta WHERE quiz_id IN (SELECT id FROM quiz WHERE rap_id = @RAP1);
DELETE FROM quiz WHERE rap_id = @RAP1;

-- 1. VOCABULARIO RAP 1
INSERT INTO vocabulario (id, rap_id, termino_en, termino_es, categoria_id, area_clinica_id, transcripcion_ipa, audio_url, imagen_url, oracion_ejemplo, nivel_dificultad, activo) VALUES
(UUID(), @RAP1, 'Good morning', 'Buenos días', @CAT_SUST, @AREA_GEN, '/ɡʊd ˈmɔːr.nɪŋ/', NULL, NULL, 'Good morning! My name is Sarah, your nurse today.', 'A1', 1),
(UUID(), @RAP1, 'Good afternoon', 'Buenas tardes', @CAT_SUST, @AREA_GEN, '/ɡʊd ˌæf.tɚˈnuːn/', NULL, NULL, 'Good afternoon, Mr. Thomas. How are you feeling?', 'A1', 1),
(UUID(), @RAP1, 'Good evening', 'Buenas noches (saludo)', @CAT_SUST, @AREA_GEN, '/ɡʊd ˈiːv.nɪŋ/', NULL, NULL, 'Good evening! The night shift has just started.', 'A1', 1),
(UUID(), @RAP1, 'Goodbye', 'Adiós', @CAT_SUST, @AREA_GEN, '/ˌɡʊdˈbaɪ/', NULL, NULL, 'Goodbye, Mrs. Rivera. See you tomorrow morning.', 'A1', 1),
(UUID(), @RAP1, 'Nice to meet you', 'Mucho gusto', @CAT_SUST, @AREA_GEN, '/naɪs tə miːt juː/', NULL, NULL, 'Nice to meet you. I am the nurse assigned to your room.', 'A1', 1),
(UUID(), @RAP1, 'How are you?', '¿Cómo estás?', @CAT_SUST, @AREA_GEN, '/haʊ ɑːr juː/', NULL, NULL, 'How are you feeling today, Mr. Thomas?', 'A1', 1),
(UUID(), @RAP1, 'name', 'nombre', @CAT_SUST, @AREA_GEN, '/neɪm/', NULL, NULL, 'My name is Carolina. What is your name?', 'A1', 1),
(UUID(), @RAP1, 'last name', 'apellido', @CAT_SUST, @AREA_GEN, '/læst neɪm/', NULL, NULL, 'Please spell your last name for the patient register.', 'A1', 1),
(UUID(), @RAP1, 'age', 'edad', @CAT_SUST, @AREA_GEN, '/eɪdʒ/', NULL, NULL, 'What is your age? Are you over eighteen?', 'A1', 1),
(UUID(), @RAP1, 'nationality', 'nacionalidad', @CAT_SUST, @AREA_GEN, '/ˌnæʃ.əˈnæl.ɪ.ti/', NULL, NULL, 'What is your nationality? Are you American?', 'A1', 1),
(UUID(), @RAP1, 'phone number', 'número de teléfono', @CAT_SUST, @AREA_GEN, '/foʊn ˈnʌm.bɚ/', NULL, NULL, 'Please give me your phone number in case of emergency.', 'A1', 1),
(UUID(), @RAP1, 'email address', 'correo electrónico', @CAT_SUST, @AREA_GEN, '/ˈiː.meɪl ˈæd.res/', NULL, NULL, 'Please type your email address in this form.', 'A1', 1),
(UUID(), @RAP1, 'date of birth', 'fecha de nacimiento', @CAT_SUST, @AREA_GEN, '/deɪt əv bɜːrθ/', NULL, NULL, 'Please confirm your date of birth for the medical record.', 'A1', 1),
(UUID(), @RAP1, 'I am', 'Yo soy / Yo estoy', @CAT_SUST, @AREA_GEN, '/aɪ æm/', NULL, NULL, 'I am Sarah, the nurse in charge of Room 204.', 'A1', 1),
(UUID(), @RAP1, 'You are', 'Tú eres / Usted es', @CAT_SUST, @AREA_GEN, '/juː ɑːr/', NULL, NULL, 'You are my patient for this shift.', 'A1', 1),
(UUID(), @RAP1, 'He is', 'Él es / Él está', @CAT_SUST, @AREA_GEN, '/hiː ɪz/', NULL, NULL, 'He is the doctor on duty tonight.', 'A1', 1),
(UUID(), @RAP1, 'She is', 'Ella es / Ella está', @CAT_SUST, @AREA_GEN, '/ʃiː ɪz/', NULL, NULL, 'She is a patient from the United States.', 'A1', 1);

-- 2. DIÁLOGO RAP 1
SET @dia1 = UUID();
INSERT INTO dialogo (id, rap_id, titulo, contexto, participantes, audio_completo_url, activo) VALUES
(@dia1, @RAP1, 'First Day at the Clinic', 'Nurse David and Nurse Carolina introduce themselves on the first day at the clinic.', 'Nurse David, Nurse Carolina', NULL, 1);

INSERT INTO turno_dialogo (id, dialogo_id, orden_turno, hablante, texto_en, texto_es, audio_url) VALUES
(UUID(), @dia1, 1, 'Nurse David', 'Good morning! Welcome to the clinic. Are you the new nurse?', '¡Buenos días! Bienvenida a la clínica. ¿Eres la nueva enfermera?', NULL),
(UUID(), @dia1, 2, 'Nurse Carolina', 'Good morning! Yes, I am. My name is Carolina Ramírez. Nice to meet you!', '¡Buenos días! Sí, lo soy. Mi nombre es Carolina Ramírez. ¡Mucho gusto!', NULL),
(UUID(), @dia1, 3, 'Nurse David', 'Nice to meet you too, Carolina. I am David Torres. I am the nurse in charge of the morning shift.', 'Igualmente, Carolina. Soy David Torres. Soy el enfermero encargado del turno de la mañana.', NULL),
(UUID(), @dia1, 4, 'Nurse Carolina', 'Great! What is your last name, David? How do you spell it?', '¡Excelente! ¿Cuál es tu apellido, David? ¿Cómo se escribe?', NULL),
(UUID(), @dia1, 5, 'Nurse David', 'My last name is Torres. T-O-R-R-E-S. And your last name? How do you spell Ramírez?', 'Mi apellido es Torres. T-O-R-R-E-S. ¿Y tu apellido? ¿Cómo se escribe Ramírez?', NULL),
(UUID(), @dia1, 6, 'Nurse Carolina', 'It is R-A-M-Í-R-E-Z. I am from Colombia. What is your nationality?', 'Es R-A-M-Í-R-E-Z. Soy de Colombia. ¿Cuál es tu nacionalidad?', NULL),
(UUID(), @dia1, 7, 'Nurse David', 'I am Mexican. I am from Guadalajara. And what is your phone number for the emergency contact list?', 'Soy mexicano. Soy de Guadalajara. ¿Y cuál es tu número de teléfono para la lista de contactos de emergencia?', NULL),
(UUID(), @dia1, 8, 'Nurse Carolina', 'My phone number is three, one, zero, five, five, five, two, two, seven, eight. And my email is carolina.ramirez@clinic.co', 'Mi número de teléfono es tres, uno, cero, cinco, cinco, cinco, dos, dos, siete, ocho. Y mi correo es carolina.ramirez@clinic.co', NULL),
(UUID(), @dia1, 9, 'Nurse David', 'Perfect, thank you! Good afternoon, Carolina. See you at the ward.', '¡Perfecto, gracias! Buenas tardes, Carolina. Nos vemos en el pabellón.', NULL),
(UUID(), @dia1, 10, 'Nurse Carolina', 'Good afternoon, David. Goodbye for now!', 'Buenas tardes, David. ¡Hasta luego!', NULL);

-- 3. EJERCICIOS RAP 1
SET @ej1 = UUID();
INSERT INTO ejercicio (id, rap_id, tipo, enunciado, max_intentos, puntos, activo) VALUES
(@ej1, @RAP1, 'arrastrar_soltar', 'Match each English greeting with the correct time of day. Drag the greeting to its pair.', 3, 10, 1);
INSERT INTO ejercicio_opcion (id, ejercicio_id, texto, es_correcta, retroalimentacion) VALUES
(UUID(), @ej1, 'Good morning = Buenos días (6am - 12pm)', 1, '✅ Correct! \"Good morning\" is used from sunrise until noon.'),
(UUID(), @ej1, 'Good afternoon = Buenas tardes (12pm - 6pm)', 1, '✅ Correct! \"Good afternoon\" is used from noon until evening.'),
(UUID(), @ej1, 'Good evening = Buenas noches / saludo (6pm - 12am)', 1, '✅ Correct! \"Good evening\" is a greeting used after 6pm.'),
(UUID(), @ej1, 'Goodbye = Adiós (despedida)', 1, '✅ Correct! \"Goodbye\" is used when leaving or ending a conversation.');

SET @ej2 = UUID();
INSERT INTO ejercicio (id, rap_id, tipo, enunciado, max_intentos, puntos, activo) VALUES
(@ej2, @RAP1, 'completar_frase', 'Complete the patient form. Choose the correct verb: \"I ___ Carolina Ramírez, the nurse in charge.\"', 3, 10, 1);
INSERT INTO ejercicio_opcion (id, ejercicio_id, texto, es_correcta, retroalimentacion) VALUES
(UUID(), @ej2, 'am', 1, '✅ Correct! \"I am\" is the Subject + Verb To Be structure. I + am = first person singular.'),
(UUID(), @ej2, 'is', 0, '❌ \"Is\" is used with He / She / It. Use \"am\" with \"I\".'),
(UUID(), @ej2, 'are', 0, '❌ \"Are\" is used with You / We / They. Use \"am\" with \"I\".'),
(UUID(), @ej2, 'be', 0, '❌ \"Be\" is the base form, not used in simple sentences without an auxiliary.');

SET @ej3 = UUID();
INSERT INTO ejercicio (id, rap_id, tipo, enunciado, max_intentos, puntos, activo) VALUES
(@ej3, @RAP1, 'completar_frase', 'Fill in the ID Card: \"You ___ Mr. Thomas, the patient in Room 204.\"', 3, 10, 1);
INSERT INTO ejercicio_opcion (id, ejercicio_id, texto, es_correcta, retroalimentacion) VALUES
(UUID(), @ej3, 'are', 1, '✅ Correct! \"You are\" is the Subject + Verb To Be for second person.'),
(UUID(), @ej3, 'am', 0, '❌ \"Am\" is only used with \"I\".'),
(UUID(), @ej3, 'is', 0, '❌ \"Is\" is for He / She / It, not for \"You\".'),
(UUID(), @ej3, 'were', 0, '❌ \"Were\" is past tense. Use present tense here.');

SET @ej4 = UUID();
INSERT INTO ejercicio (id, rap_id, tipo, enunciado, max_intentos, puntos, activo) VALUES
(@ej4, @RAP1, 'completar_frase', 'Complete the nurse introduction: \"She ___ the head nurse of the morning shift.\"', 3, 10, 1);
INSERT INTO ejercicio_opcion (id, ejercicio_id, texto, es_correcta, retroalimentacion) VALUES
(UUID(), @ej4, 'is', 1, '✅ Correct! \"She is\" — third person singular (She / He / It) uses \"is\".'),
(UUID(), @ej4, 'are', 0, '❌ \"Are\" is for You / We / They. Use \"is\" for She / He / It.'),
(UUID(), @ej4, 'am', 0, '❌ \"Am\" is only for \"I\".'),
(UUID(), @ej4, 'been', 0, '❌ \"Been\" is a past participle. This sentence needs present tense.');

SET @ej5 = UUID();
INSERT INTO ejercicio (id, rap_id, tipo, enunciado, max_intentos, puntos, activo) VALUES
(@ej5, @RAP1, 'escucha_escribe', '🎧 Listening Exercise: Press the play button and listen carefully. The nurse is spelling her last name. Type exactly what you hear letter by letter.', 3, 10, 1);
INSERT INTO ejercicio_opcion (id, ejercicio_id, texto, es_correcta, retroalimentacion) VALUES
(UUID(), @ej5, 'RAMIREZ', 1, '✅ Excellent! R-A-M-I-R-E-Z. You correctly identified each letter of the last name.'),
(UUID(), @ej5, 'RAMIRÉZ', 0, '❌ Close! Remember, when spelling in English we do not use accent marks. The answer is RAMIREZ.'),
(UUID(), @ej5, 'RAMIRES', 0, '❌ Not quite. The last letter is Z, not S. Listen again: R-A-M-I-R-E-Z.'),
(UUID(), @ej5, 'REMIREZ', 0, '❌ The second letter is A, not E. Listen carefully: R-A-M-I-R-E-Z.');

SET @ej6 = UUID();
INSERT INTO ejercicio (id, rap_id, tipo, enunciado, max_intentos, puntos, activo) VALUES
(@ej6, @RAP1, 'role_play', '🎤 Clinical Challenge — El Desafío: A foreign patient (Mr. Thomas) has arrived at the clinic. You are the admissions nurse. Record yourself (max. 1 minute) greeting Mr. Thomas, introducing yourself, asking for his full name (and spelling it), his phone number, and his date of birth. Use the structure: Subject + Verb To Be + Complement.', 3, 10, 1);
INSERT INTO ejercicio_opcion (id, ejercicio_id, texto, es_correcta, retroalimentacion) VALUES
(UUID(), @ej6, 'I used correct greetings (Good morning / Good afternoon / Good evening)', 1, 'Using the correct time-appropriate greeting shows clinical professionalism.'),
(UUID(), @ej6, 'I introduced myself using \"I am [name], your nurse\"', 1, 'The Subject + Verb To Be structure is the foundation of self-introduction.'),
(UUID(), @ej6, 'I asked for the patient name and spelled it back correctly', 1, 'Confirming spelling is a critical skill in clinical documentation.'),
(UUID(), @ej6, 'I asked for the phone number and date of birth', 1, 'Collecting personal data accurately is essential for patient registration.');

-- 4. QUIZ RAP 1
SET @quiz1 = UUID();
INSERT INTO quiz (id, rap_id, puntaje_minimo, limite_tiempo_seg, aleatorizar, max_intentos, activo) VALUES (@quiz1, @RAP1, 60.00, 300, 0, 3, 1);
INSERT INTO pregunta (id, quiz_id, texto, opciones, respuesta_correcta, retroalimentacion) VALUES
(UUID(), @quiz1, 'What is the correct greeting for 8:00 AM when you arrive at the clinic?', '[\"Good evening\",\"Good afternoon\",\"Good morning\",\"Good night\"]', 'Good morning', '\"Good morning\" is used from sunrise until noon (12:00 PM).'),
(UUID(), @quiz1, 'Choose the correct verb to complete the sentence: \"I ___ a nurse at this hospital.\"', '[\"am\",\"is\",\"are\",\"be\"]', 'am', 'The first person singular \"I\" always uses the verb \"am\".'),
(UUID(), @quiz1, 'Choose the correct verb to complete the sentence: \"Dr. Smith ___ the head of the department.\"', '[\"are\",\"am\",\"is\",\"were\"]', 'is', 'Third person singular (He/She/It) uses the verb \"is\".'),
(UUID(), @quiz1, 'A patient gives you their phone number. How do you say the number \"8\"?', '[\"Three\",\"Five\",\"Eight\",\"Nine\"]', 'Eight', 'The correct English word for the number 8 is \"Eight\".'),
(UUID(), @quiz1, 'How do you correctly spell the word \"Nurse\"?', '[\"N-U-R-C-E\",\"N-U-R-S-E\",\"N-O-R-S-E\",\"M-U-R-S-E\"]', 'N-U-R-S-E', 'The correct spelling is N-U-R-S-E.');
