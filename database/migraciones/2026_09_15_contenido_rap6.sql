-- =============================================================
-- MIGRACIÓN: Contenido del RAP 6 (Módulo 4: Professional Practice)
-- Fecha: 2026-09-15
-- Descripción: el RAP 6 existía sin contenido. Carga su vocabulario, el
--              diálogo de alta de Mr. Thomas, los ejercicios del Momento 3
--              y el quiz, según el guion de contenidos.md (Módulo 4).
--
-- Por qué migración y no seed: los seeds borran el contenido y los intentos
-- de quiz de su RAP antes de insertar, así que no se pueden correr en
-- producción. Esta migración nunca borra nada.
--
-- SEGURO: solo inserta si el RAP 6 existe y está completamente vacío (sin
--         vocabulario, diálogos, ejercicios ni quiz) y si ninguno de los ids
--         de abajo existe ya. Si un administrador ya cargó algo en el RAP 6,
--         no hace nada.
-- IDEMPOTENTE: después de la primera vez el RAP 6 ya no está vacío.
-- IDS FIJOS: la página ordena el vocabulario, los ejercicios, las opciones y
--            las preguntas por id. Con ids fijos el Warm-Up usa las tres
--            primeras palabras del guion y los ejercicios salen en orden.
-- TEXTOS: sin apóstrofos a propósito. Las fichas de completar frase, ordenar
--         diálogo y dictado van dentro de onclick='...' en la vista.
-- NO PEGAR EN LA TERMINAL: la terminal borra las tildes. Se aplica sola
--         con migrar.php.
-- =============================================================

SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;
USE smash_code;

SET @NIV4 := (SELECT id FROM nivel WHERE orden = 4 LIMIT 1);
SET @RAP6 := (SELECT id FROM rap WHERE nivel_id = @NIV4 AND orden = 1 LIMIT 1);

SET @insertar := COALESCE((
    @RAP6 IS NOT NULL
    AND NOT EXISTS (SELECT 1 FROM vocabulario WHERE rap_id = @RAP6)
    AND NOT EXISTS (SELECT 1 FROM dialogo     WHERE rap_id = @RAP6)
    AND NOT EXISTS (SELECT 1 FROM ejercicio   WHERE rap_id = @RAP6)
    AND NOT EXISTS (SELECT 1 FROM quiz        WHERE rap_id = @RAP6)
    AND NOT EXISTS (SELECT 1 FROM vocabulario      WHERE id LIKE '00000006-0000-4000-8000-%')
    AND NOT EXISTS (SELECT 1 FROM dialogo          WHERE id LIKE '00000006-0000-4000-8000-%')
    AND NOT EXISTS (SELECT 1 FROM turno_dialogo    WHERE id LIKE '00000006-0000-4000-8000-%')
    AND NOT EXISTS (SELECT 1 FROM ejercicio        WHERE id LIKE '00000006-0000-4000-8000-%')
    AND NOT EXISTS (SELECT 1 FROM ejercicio_opcion WHERE id LIKE '00000006-0000-4000-8000-%')
    AND NOT EXISTS (SELECT 1 FROM quiz             WHERE id LIKE '00000006-0000-4000-8000-%')
    AND NOT EXISTS (SELECT 1 FROM pregunta         WHERE id LIKE '00000006-0000-4000-8000-%')
), 0);

-- Si el nombre no existe en la base, la palabra queda sin categoría o área
SET @CAT_SUST  := (SELECT id FROM categoria_vocabulario WHERE nombre = 'Sustantivo' LIMIT 1);
SET @CAT_VERB  := (SELECT id FROM categoria_vocabulario WHERE nombre = 'Verbo' LIMIT 1);
SET @CAT_ADJ   := (SELECT id FROM categoria_vocabulario WHERE nombre = 'Adjetivo' LIMIT 1);
SET @CAT_FRASE := (SELECT id FROM categoria_vocabulario WHERE nombre = 'Frase' LIMIT 1);
SET @AREA_GEN  := (SELECT id FROM area_clinica WHERE nombre LIKE '%General%' LIMIT 1);

-- 1. VOCABULARIO (las tres primeras son las del Warm-Up del guion)
INSERT INTO vocabulario (id, rap_id, termino_en, termino_es, categoria_id, area_clinica_id, transcripcion_ipa, audio_url, imagen_url, oracion_ejemplo, nivel_dificultad, activo)
SELECT f.id, @RAP6, f.en, f.es, f.cat, @AREA_GEN, f.ipa, NULL, NULL, f.ejemplo, 'A1', 1
FROM (
              SELECT '00000006-0000-4000-8000-100000000001' AS id, 'Vital signs stable' AS en, 'Signos vitales estables' AS es, @CAT_FRASE AS cat, '/ˈvaɪtəl saɪnz ˈsteɪbəl/' AS ipa, 'Good news, Mr. Thomas: your vital signs are stable.' AS ejemplo
    UNION ALL SELECT '00000006-0000-4000-8000-100000000002', 'Pain resolved', 'Dolor resuelto', @CAT_FRASE, '/peɪn rɪˈzɑːlvd/', 'His pain is resolved, so he does not need a painkiller now.'
    UNION ALL SELECT '00000006-0000-4000-8000-100000000003', 'Ready for discharge', 'Listo para el alta', @CAT_FRASE, '/ˈrɛdi fɔːr dɪsˈtʃɑːrdʒ/', 'The patient is ready for discharge this afternoon.'
    UNION ALL SELECT '00000006-0000-4000-8000-100000000004', 'Discharge', 'Alta médica', @CAT_SUST, '/ˈdɪstʃɑːrdʒ/', 'Dr. Smith signed the discharge order.'
    UNION ALL SELECT '00000006-0000-4000-8000-100000000005', 'Prescription', 'Receta médica', @CAT_SUST, '/prɪˈskrɪpʃən/', 'Take this prescription to the pharmacy.'
    UNION ALL SELECT '00000006-0000-4000-8000-100000000006', 'Painkiller', 'Analgésico', @CAT_SUST, '/ˈpeɪnˌkɪlər/', 'Take one painkiller every eight hours if you have pain.'
    UNION ALL SELECT '00000006-0000-4000-8000-100000000007', 'Pill', 'Pastilla', @CAT_SUST, '/pɪl/', 'You must take this pill after breakfast.'
    UNION ALL SELECT '00000006-0000-4000-8000-100000000008', 'Dose', 'Dosis', @CAT_SUST, '/doʊs/', 'Do not take more than one dose every eight hours.'
    UNION ALL SELECT '00000006-0000-4000-8000-100000000009', 'Follow-up appointment', 'Cita de control', @CAT_SUST, '/ˈfɑːloʊ ʌp əˈpɔɪntmənt/', 'Your follow-up appointment is on Monday at 9 AM.'
    UNION ALL SELECT '00000006-0000-4000-8000-100000000010', 'Recovery', 'Recuperación', @CAT_SUST, '/rɪˈkʌvəri/', 'Rest is important for a fast recovery.'
    UNION ALL SELECT '00000006-0000-4000-8000-100000000011', 'Outcomes', 'Resultados (del cuidado)', @CAT_SUST, '/ˈaʊtˌkʌmz/', 'The nurse writes the patient outcomes in the checklist.'
    UNION ALL SELECT '00000006-0000-4000-8000-100000000012', 'Home care', 'Cuidado en casa', @CAT_SUST, '/hoʊm kɛr/', 'His daughter will help him with home care.'
    UNION ALL SELECT '00000006-0000-4000-8000-100000000013', 'Should', 'Debería (consejo)', @CAT_VERB, '/ʃʊd/', 'You should drink plenty of water.'
    UNION ALL SELECT '00000006-0000-4000-8000-100000000014', 'Must', 'Debe (obligación)', @CAT_VERB, '/mʌst/', 'You must take your medicine every day.'
    UNION ALL SELECT '00000006-0000-4000-8000-100000000015', 'Rest', 'Descansar', @CAT_VERB, '/rɛst/', 'You should rest at home for five days.'
    UNION ALL SELECT '00000006-0000-4000-8000-100000000016', 'Normal', 'Normal', @CAT_ADJ, '/ˈnɔːrməl/', 'The temperature is normal today.'
) AS f
WHERE @insertar = 1;

-- 2. DIÁLOGO: el alta de Mr. Thomas
INSERT INTO dialogo (id, rap_id, titulo, contexto, participantes, audio_completo_url, activo)
SELECT '00000006-0000-4000-8000-200000000001', @RAP6, 'Going Home: Discharge Instructions for Mr. Thomas', 'Nurse Sarah gives Mr. Thomas his final health advice, his prescription and his follow-up appointment before he goes home.', 'Nurse Sarah, Mr. Thomas', NULL, 1
FROM DUAL
WHERE @insertar = 1;

INSERT INTO turno_dialogo (id, dialogo_id, orden_turno, hablante, texto_en, texto_es, audio_url)
SELECT f.id, '00000006-0000-4000-8000-200000000001', f.orden, f.hablante, f.en, f.es, NULL
FROM (
              SELECT '00000006-0000-4000-8000-300000000001' AS id, 1 AS orden, 'Nurse Sarah' AS hablante, 'Good morning, Mr. Thomas! I have good news. Your vital signs are stable and you are ready for discharge.' AS en, '¡Buenos días, Mr. Thomas! Tengo buenas noticias. Sus signos vitales están estables y usted está listo para el alta.' AS es
    UNION ALL SELECT '00000006-0000-4000-8000-300000000002', 2, 'Mr. Thomas', 'That is great news! What should I do at home?', '¡Qué buena noticia! ¿Qué debo hacer en casa?'
    UNION ALL SELECT '00000006-0000-4000-8000-300000000003', 3, 'Nurse Sarah', 'You should rest at home for five days. You must not lift heavy objects with your right arm.', 'Debería descansar en casa durante cinco días. No debe levantar objetos pesados con el brazo derecho.'
    UNION ALL SELECT '00000006-0000-4000-8000-300000000004', 4, 'Mr. Thomas', 'OK. And what about the pain in my arm?', 'De acuerdo. ¿Y el dolor del brazo?'
    UNION ALL SELECT '00000006-0000-4000-8000-300000000005', 5, 'Nurse Sarah', 'Here is your prescription. You must take one painkiller every eight hours, after meals.', 'Aquí está su receta médica. Debe tomar un analgésico cada ocho horas, después de las comidas.'
    UNION ALL SELECT '00000006-0000-4000-8000-300000000006', 6, 'Mr. Thomas', 'One painkiller every eight hours, after meals. Do I need to come back?', 'Un analgésico cada ocho horas, después de las comidas. ¿Necesito volver?'
    UNION ALL SELECT '00000006-0000-4000-8000-300000000007', 7, 'Nurse Sarah', 'Yes. Your follow-up appointment is on Monday at 9 AM with Dr. Smith.', 'Sí. Su cita de control es el lunes a las 9 AM con el Dr. Smith.'
    UNION ALL SELECT '00000006-0000-4000-8000-300000000008', 8, 'Mr. Thomas', 'Thank you, Nurse Sarah. You took great care of me.', 'Gracias, enfermera Sarah. Usted me cuidó muy bien.'
    UNION ALL SELECT '00000006-0000-4000-8000-300000000009', 9, 'Nurse Sarah', 'You are welcome! I checked your discharge checklist: the temperature is normal and the pain level is low. Have a good recovery!', '¡Con mucho gusto! Revisé su lista de verificación de alta: la temperatura es normal y el nivel de dolor es bajo. ¡Que tenga una buena recuperación!'
) AS f
WHERE @insertar = 1;

-- 3. EJERCICIOS (Momento 3, en el orden del guion)
INSERT INTO ejercicio (id, rap_id, tipo, enunciado, max_intentos, puntos, activo)
SELECT f.id, @RAP6, f.tipo, f.enunciado, 3, 10, 1
FROM (
              SELECT '00000006-0000-4000-8000-400000000001' AS id, 'escucha_escribe' AS tipo, '🎧 Discharge Summary: Press play and listen to Dr. Smith dictating the discharge order. Type the name of the medicine Mr. Thomas must take every eight hours.' AS enunciado
    UNION ALL SELECT '00000006-0000-4000-8000-400000000002', 'escucha_escribe', '🎧 Discharge Summary: Listen again. How long must Mr. Thomas rest at home? Type the time in words (example: two weeks).'
    UNION ALL SELECT '00000006-0000-4000-8000-400000000003', 'seleccion_multiple', '📋 Nursing Checklist of Mr. Thomas: Temperature 36.8 °C ✔ · Pain level 1/10 ✔ · Vital signs stable ✔ · Discharge order signed ✔. Which sentence describes the result of this checklist?'
    UNION ALL SELECT '00000006-0000-4000-8000-400000000004', 'completar_frase', 'Medical Advice: "You ___ rest at home for five days."'
    UNION ALL SELECT '00000006-0000-4000-8000-400000000005', 'completar_frase', 'Discharge Order: "You ___ take one pill every eight hours."'
    UNION ALL SELECT '00000006-0000-4000-8000-400000000006', 'completar_frase', 'Reporting Results: "The temperature ___ normal and the pain level is low."'
    UNION ALL SELECT '00000006-0000-4000-8000-400000000007', 'arrastrar_soltar', 'Match the discharge terms with their Spanish meanings:'
    UNION ALL SELECT '00000006-0000-4000-8000-400000000008', 'ordenar_dialogo', 'Order the discharge conversation in the correct sequence:'
    UNION ALL SELECT '00000006-0000-4000-8000-400000000009', 'role_play', '🎤 Clinical Challenge — El Desafío: Mr. Thomas is going home. Record yourself (max. 1 minute) giving him 2 final health recommendations with modal verbs (should / must), and report in one sentence that his checklist was analyzed and completed successfully.'
) AS f
WHERE @insertar = 1;

-- Opciones. El dictado reproduce la PRIMERA opción: la correcta va primero.
INSERT INTO ejercicio_opcion (id, ejercicio_id, texto, es_correcta, retroalimentacion)
SELECT f.id, f.ejercicio, f.texto, f.correcta, f.retro
FROM (
              SELECT '00000006-0000-4000-8000-500000000101' AS id, '00000006-0000-4000-8000-400000000001' AS ejercicio, 'painkiller' AS texto, 1 AS correcta, '✅ Correct! A painkiller is the medicine for his pain.' AS retro
    UNION ALL SELECT '00000006-0000-4000-8000-500000000201', '00000006-0000-4000-8000-400000000002', 'five days', 1, '✅ Correct! Mr. Thomas must rest at home for five days.'
    UNION ALL SELECT '00000006-0000-4000-8000-500000000301', '00000006-0000-4000-8000-400000000003', 'The temperature is very high.', 0, '❌ The checklist shows 36.8 °C: the temperature is normal.'
    UNION ALL SELECT '00000006-0000-4000-8000-500000000302', '00000006-0000-4000-8000-400000000003', 'The pain level is high.', 0, '❌ The pain level is 1/10: it is low.'
    UNION ALL SELECT '00000006-0000-4000-8000-500000000303', '00000006-0000-4000-8000-400000000003', 'The patient is ready for discharge.', 1, '✅ Correct! Every item is checked, so the patient can go home.'
    UNION ALL SELECT '00000006-0000-4000-8000-500000000304', '00000006-0000-4000-8000-400000000003', 'The patient must stay in the hospital.', 0, '❌ Every item is checked: he does not need to stay.'
    UNION ALL SELECT '00000006-0000-4000-8000-500000000401', '00000006-0000-4000-8000-400000000004', 'should', 1, '✅ Correct! Use should to give advice.'
    UNION ALL SELECT '00000006-0000-4000-8000-500000000402', '00000006-0000-4000-8000-400000000004', 'is', 0, '❌ After You, a modal verb gives the advice: You should rest.'
    UNION ALL SELECT '00000006-0000-4000-8000-500000000403', '00000006-0000-4000-8000-400000000004', 'was', 0, '❌ Was is past tense. Advice uses should.'
    UNION ALL SELECT '00000006-0000-4000-8000-500000000404', '00000006-0000-4000-8000-400000000004', 'are', 0, '❌ You are rest is not correct. Use should.'
    UNION ALL SELECT '00000006-0000-4000-8000-500000000501', '00000006-0000-4000-8000-400000000005', 'must', 1, '✅ Correct! Use must for an obligation.'
    UNION ALL SELECT '00000006-0000-4000-8000-500000000502', '00000006-0000-4000-8000-400000000005', 'is', 0, '❌ An instruction uses a modal verb: You must take.'
    UNION ALL SELECT '00000006-0000-4000-8000-500000000503', '00000006-0000-4000-8000-400000000005', 'was', 0, '❌ Was is past tense. The instruction is for now.'
    UNION ALL SELECT '00000006-0000-4000-8000-500000000504', '00000006-0000-4000-8000-400000000005', 'does', 0, '❌ You does take is not correct. Use must.'
    UNION ALL SELECT '00000006-0000-4000-8000-500000000601', '00000006-0000-4000-8000-400000000006', 'is', 1, '✅ Correct! The temperature is normal.'
    UNION ALL SELECT '00000006-0000-4000-8000-500000000602', '00000006-0000-4000-8000-400000000006', 'are', 0, '❌ The temperature is singular: use is.'
    UNION ALL SELECT '00000006-0000-4000-8000-500000000603', '00000006-0000-4000-8000-400000000006', 'am', 0, '❌ Am is only used with I.'
    UNION ALL SELECT '00000006-0000-4000-8000-500000000604', '00000006-0000-4000-8000-400000000006', 'be', 0, '❌ Be is the base form. Use is to report a result.'
    UNION ALL SELECT '00000006-0000-4000-8000-500000000701', '00000006-0000-4000-8000-400000000007', 'Discharge = Alta médica', 1, '✅ Correct match!'
    UNION ALL SELECT '00000006-0000-4000-8000-500000000702', '00000006-0000-4000-8000-400000000007', 'Prescription = Receta médica', 1, '✅ Correct match!'
    UNION ALL SELECT '00000006-0000-4000-8000-500000000703', '00000006-0000-4000-8000-400000000007', 'Painkiller = Analgésico', 1, '✅ Correct match!'
    UNION ALL SELECT '00000006-0000-4000-8000-500000000704', '00000006-0000-4000-8000-400000000007', 'Follow-up appointment = Cita de control', 1, '✅ Correct match!'
    UNION ALL SELECT '00000006-0000-4000-8000-500000000801', '00000006-0000-4000-8000-400000000008', 'Nurse Sarah: Your vital signs are stable. You are ready for discharge. | Mr. Thomas: What should I do at home? | Nurse Sarah: You should rest and you must take one painkiller every eight hours. | Mr. Thomas: When is my follow-up appointment? | Nurse Sarah: It is on Monday at 9 AM with Dr. Smith.', 1, '¡Perfecto!'
    UNION ALL SELECT '00000006-0000-4000-8000-500000000901', '00000006-0000-4000-8000-400000000009', 'I gave advice with should (for example: You should rest at home).', 1, 'Should is the modal verb for friendly medical advice.'
    UNION ALL SELECT '00000006-0000-4000-8000-500000000902', '00000006-0000-4000-8000-400000000009', 'I gave an instruction with must (for example: You must take this pill every eight hours).', 1, 'Must expresses an obligation the patient has to follow.'
    UNION ALL SELECT '00000006-0000-4000-8000-500000000903', '00000006-0000-4000-8000-400000000009', 'I reported the checklist result in one sentence (for example: The checklist is complete and the patient is ready).', 1, 'Reporting results clearly closes the nursing care process.'
) AS f
WHERE @insertar = 1;

-- 4. QUIZ (Momento 4)
INSERT INTO quiz (id, rap_id, puntaje_minimo, limite_tiempo_seg, aleatorizar, max_intentos, activo)
SELECT '00000006-0000-4000-8000-600000000001', @RAP6, 60.00, 300, 0, 3, 1
FROM DUAL
WHERE @insertar = 1;

INSERT INTO pregunta (id, quiz_id, texto, opciones, respuesta_correcta, retroalimentacion)
SELECT f.id, '00000006-0000-4000-8000-600000000001', f.texto, f.opciones, f.correcta, f.retro
FROM (
              SELECT '00000006-0000-4000-8000-700000000001' AS id, 'Which sentence gives medical advice to Mr. Thomas?' AS texto, '["He fell down yesterday","You should rest at home","I am checking his temperature now","The room is cold"]' AS opciones, 'You should rest at home' AS correcta, 'Should is used to give advice: You should rest at home.' AS retro
    UNION ALL SELECT '00000006-0000-4000-8000-700000000002', 'Choose the modal verb for an obligation: "You ___ take this pill every eight hours."', '["are","was","must","has"]', 'must', 'Must expresses an obligation: You must take this pill.'
    UNION ALL SELECT '00000006-0000-4000-8000-700000000003', 'The checklist shows: Temperature 36.8 °C. Which sentence reports this result?', '["The temperature is normal","The temperature is very high","The patient has a high fever","The temperature was not checked"]', 'The temperature is normal', '36.8 °C is a normal body temperature.'
    UNION ALL SELECT '00000006-0000-4000-8000-700000000004', 'What is a painkiller?', '["A follow-up appointment","A nursing checklist","A type of wound","A medicine for pain"]', 'A medicine for pain', 'A painkiller (analgésico) is a medicine for pain.'
    UNION ALL SELECT '00000006-0000-4000-8000-700000000005', 'Mr. Thomas must come back to see Dr. Smith next Monday. What is this visit called?', '["Discharge","Follow-up appointment","Prescription","Recovery"]', 'Follow-up appointment', 'A follow-up appointment (cita de control) is a visit after discharge.'
    UNION ALL SELECT '00000006-0000-4000-8000-700000000006', 'Every item on the discharge checklist is complete. What do you report?', '["The pain level is high","The prescription is missing","The patient is ready for discharge","The patient must stay in the ICU"]', 'The patient is ready for discharge', 'A complete discharge checklist means the patient is ready for discharge.'
) AS f
WHERE @insertar = 1;

-- Verificación
SELECT @insertar AS se_inserto,
       (SELECT COUNT(*) FROM vocabulario WHERE rap_id = @RAP6) AS vocabulario,
       (SELECT COUNT(*) FROM turno_dialogo t JOIN dialogo d ON d.id = t.dialogo_id WHERE d.rap_id = @RAP6) AS turnos,
       (SELECT COUNT(*) FROM ejercicio WHERE rap_id = @RAP6) AS ejercicios,
       (SELECT COUNT(*) FROM pregunta p JOIN quiz q ON q.id = p.quiz_id WHERE q.rap_id = @RAP6) AS preguntas;
