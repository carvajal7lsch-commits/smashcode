-- =============================================================
-- SEED PARTE 2: Diálogo RAP 1 - Storybook Chat de Presentación
-- Módulo 1 — "Getting to Know Other People"
-- Escenario: Dos colegas de enfermería se presentan al inicio del turno
-- RAP ID: be5df679-5166-11f1-b275-8c1645fa3d64
-- =============================================================

SET NAMES utf8mb4;
USE smash_code;

SELECT id INTO @RAP1 FROM rap ORDER BY orden ASC LIMIT 1;


-- Insertar el diálogo principal del RAP 1
INSERT INTO dialogo (id, rap_id, titulo, contexto, participantes, audio_completo_url, activo)
VALUES (
    'dia-rap1-presentacion',
    @RAP1,
    'First Day at the Clinic',
    'Nurse Carolina arrives at the clinic for her first shift and meets her colleague David, who has already been working there. They introduce themselves using formal and informal greetings.',
    'Nurse Carolina, Nurse David',
    NULL,
    1
);

-- Insertar los turnos del diálogo (estilo chat WhatsApp)
INSERT INTO turno_dialogo (id, dialogo_id, orden_turno, hablante, texto_en, texto_es, audio_url)
VALUES
(UUID(), 'dia-rap1-presentacion', 1, 'Nurse David', 'Good morning! Welcome to the clinic. Are you the new nurse?', '¡Buenos días! Bienvenida a la clínica. ¿Eres la nueva enfermera?', NULL),
(UUID(), 'dia-rap1-presentacion', 2, 'Nurse Carolina', 'Good morning! Yes, I am. My name is Carolina Ramírez. Nice to meet you!', '¡Buenos días! Sí, lo soy. Mi nombre es Carolina Ramírez. ¡Mucho gusto!', NULL),
(UUID(), 'dia-rap1-presentacion', 3, 'Nurse David', 'Nice to meet you too, Carolina. I am David Torres. I am the nurse in charge of the morning shift.', 'Igualmente, Carolina. Soy David Torres. Soy el enfermero encargado del turno de la mañana.', NULL),
(UUID(), 'dia-rap1-presentacion', 4, 'Nurse Carolina', 'Great! What is your last name, David? How do you spell it?', '¡Excelente! ¿Cuál es tu apellido, David? ¿Cómo se escribe?', NULL),
(UUID(), 'dia-rap1-presentacion', 5, 'Nurse David', 'My last name is Torres. T-O-R-R-E-S. And your last name? How do you spell Ramírez?', 'Mi apellido es Torres. T-O-R-R-E-S. ¿Y tu apellido? ¿Cómo se escribe Ramírez?', NULL),
(UUID(), 'dia-rap1-presentacion', 6, 'Nurse Carolina', 'It is R-A-M-Í-R-E-Z. I am from Colombia. What is your nationality?', 'Es R-A-M-Í-R-E-Z. Soy de Colombia. ¿Cuál es tu nacionalidad?', NULL),
(UUID(), 'dia-rap1-presentacion', 7, 'Nurse David', 'I am Mexican. I am from Guadalajara. And what is your phone number for the emergency contact list?', 'Soy mexicano. Soy de Guadalajara. ¿Y cuál es tu número de teléfono para la lista de contactos de emergencia?', NULL),
(UUID(), 'dia-rap1-presentacion', 8, 'Nurse Carolina', 'My phone number is three, one, zero, five, five, five, two, two, seven, eight. And my email is carolina.ramirez@clinic.co', 'Mi número de teléfono es tres, uno, cero, cinco, cinco, cinco, dos, dos, siete, ocho. Y mi correo es carolina.ramirez@clinic.co', NULL),
(UUID(), 'dia-rap1-presentacion', 9, 'Nurse David', 'Perfect, thank you! Good afternoon, Carolina. See you at the ward.', '¡Perfecto, gracias! Buenas tardes, Carolina. Nos vemos en el pabellón.', NULL),
(UUID(), 'dia-rap1-presentacion', 10, 'Nurse Carolina', 'Good afternoon, David. Goodbye for now!', 'Buenas tardes, David. ¡Hasta luego!', NULL);
