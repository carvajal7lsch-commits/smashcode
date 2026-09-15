-- =============================================================
-- MIGRACIÓN: Traducción de los ejemplos del vocabulario existente (HU19)
-- Fecha: 2026-09-15
-- Descripción: HU19 vuelve obligatoria la traducción de la oración de
--              ejemplo. Aquí se completan las 65 palabras de los seeds y del
--              RAP 6.
--
-- Cada palabra se ubica por su término y su oración de ejemplo en inglés (en
-- el VPS los ids no coinciden con los de las bases locales). Si alguien editó
-- el ejemplo desde el panel, no coincide y la traducción queda para llenarla a
-- mano al editar la palabra.
--
-- SEGURO: solo escribe traduccion_ejemplo donde está vacía. No cambia nada más.
-- IDEMPOTENTE: la segunda vez ninguna fila tiene la traducción vacía.
-- PRUEBA EN SECO: solo usa UPDATE, se puede probar con
--   php database/probar_migracion.php database/migraciones/2026_09_15_vocabulario_traducciones_ejemplos.sql
-- REQUIERE: 2026_09_15_vocabulario_traduccion_ejemplo.sql (la columna).
-- =============================================================

SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;
USE smash_code;

-- Módulo 1 · RAP 1
UPDATE vocabulario SET traduccion_ejemplo = '¡Buenos días! Mi nombre es Sarah, hoy soy su enfermera.' WHERE termino_en = 'Good morning' AND oracion_ejemplo = 'Good morning! My name is Sarah, your nurse today.' AND (traduccion_ejemplo IS NULL OR traduccion_ejemplo = '');
UPDATE vocabulario SET traduccion_ejemplo = 'Buenas tardes, Mr. Thomas. ¿Cómo se siente?' WHERE termino_en = 'Good afternoon' AND oracion_ejemplo = 'Good afternoon, Mr. Thomas. How are you feeling?' AND (traduccion_ejemplo IS NULL OR traduccion_ejemplo = '');
UPDATE vocabulario SET traduccion_ejemplo = '¡Buenas noches! El turno de la noche acaba de empezar.' WHERE termino_en = 'Good evening' AND oracion_ejemplo = 'Good evening! The night shift has just started.' AND (traduccion_ejemplo IS NULL OR traduccion_ejemplo = '');
UPDATE vocabulario SET traduccion_ejemplo = 'Adiós, señora Rivera. Nos vemos mañana en la mañana.' WHERE termino_en = 'Goodbye' AND oracion_ejemplo = 'Goodbye, Mrs. Rivera. See you tomorrow morning.' AND (traduccion_ejemplo IS NULL OR traduccion_ejemplo = '');
UPDATE vocabulario SET traduccion_ejemplo = 'Mucho gusto. Soy la enfermera asignada a su habitación.' WHERE termino_en = 'Nice to meet you' AND oracion_ejemplo = 'Nice to meet you. I am the nurse assigned to your room.' AND (traduccion_ejemplo IS NULL OR traduccion_ejemplo = '');
UPDATE vocabulario SET traduccion_ejemplo = '¿Cómo se siente hoy, Mr. Thomas?' WHERE termino_en = 'How are you?' AND oracion_ejemplo = 'How are you feeling today, Mr. Thomas?' AND (traduccion_ejemplo IS NULL OR traduccion_ejemplo = '');
UPDATE vocabulario SET traduccion_ejemplo = 'Mi nombre es Carolina. ¿Cuál es su nombre?' WHERE termino_en = 'name' AND oracion_ejemplo = 'My name is Carolina. What is your name?' AND (traduccion_ejemplo IS NULL OR traduccion_ejemplo = '');
UPDATE vocabulario SET traduccion_ejemplo = 'Por favor, deletree su apellido para el registro del paciente.' WHERE termino_en = 'last name' AND oracion_ejemplo = 'Please spell your last name for the patient register.' AND (traduccion_ejemplo IS NULL OR traduccion_ejemplo = '');
UPDATE vocabulario SET traduccion_ejemplo = '¿Cuál es su edad? ¿Es mayor de dieciocho años?' WHERE termino_en = 'age' AND oracion_ejemplo = 'What is your age? Are you over eighteen?' AND (traduccion_ejemplo IS NULL OR traduccion_ejemplo = '');
UPDATE vocabulario SET traduccion_ejemplo = '¿Cuál es su nacionalidad? ¿Es estadounidense?' WHERE termino_en = 'nationality' AND oracion_ejemplo = 'What is your nationality? Are you American?' AND (traduccion_ejemplo IS NULL OR traduccion_ejemplo = '');
UPDATE vocabulario SET traduccion_ejemplo = 'Por favor, deme su número de teléfono en caso de emergencia.' WHERE termino_en = 'phone number' AND oracion_ejemplo = 'Please give me your phone number in case of emergency.' AND (traduccion_ejemplo IS NULL OR traduccion_ejemplo = '');
UPDATE vocabulario SET traduccion_ejemplo = 'Por favor, escriba su correo electrónico en este formulario.' WHERE termino_en = 'email address' AND oracion_ejemplo = 'Please type your email address in this form.' AND (traduccion_ejemplo IS NULL OR traduccion_ejemplo = '');
UPDATE vocabulario SET traduccion_ejemplo = 'Por favor, confirme su fecha de nacimiento para la historia clínica.' WHERE termino_en = 'date of birth' AND oracion_ejemplo = 'Please confirm your date of birth for the medical record.' AND (traduccion_ejemplo IS NULL OR traduccion_ejemplo = '');
UPDATE vocabulario SET traduccion_ejemplo = 'Soy Sarah, la enfermera a cargo de la habitación 204.' WHERE termino_en = 'I am' AND oracion_ejemplo = 'I am Sarah, the nurse in charge of Room 204.' AND (traduccion_ejemplo IS NULL OR traduccion_ejemplo = '');
UPDATE vocabulario SET traduccion_ejemplo = 'Usted es mi paciente en este turno.' WHERE termino_en = 'You are' AND oracion_ejemplo = 'You are my patient for this shift.' AND (traduccion_ejemplo IS NULL OR traduccion_ejemplo = '');
UPDATE vocabulario SET traduccion_ejemplo = 'Él es el médico de turno esta noche.' WHERE termino_en = 'He is' AND oracion_ejemplo = 'He is the doctor on duty tonight.' AND (traduccion_ejemplo IS NULL OR traduccion_ejemplo = '');
UPDATE vocabulario SET traduccion_ejemplo = 'Ella es una paciente de Estados Unidos.' WHERE termino_en = 'She is' AND oracion_ejemplo = 'She is a patient from the United States.' AND (traduccion_ejemplo IS NULL OR traduccion_ejemplo = '');

-- Módulo 2 · RAP 2
UPDATE vocabulario SET traduccion_ejemplo = 'Mr. Thomas se lesionó el brazo derecho en la caída.' WHERE termino_en = 'Right arm' AND oracion_ejemplo = 'Mr. Thomas injured his right arm in the fall.' AND (traduccion_ejemplo IS NULL OR traduccion_ejemplo = '');
UPDATE vocabulario SET traduccion_ejemplo = 'Verifique si el paciente se golpeó la cabeza.' WHERE termino_en = 'Head' AND oracion_ejemplo = 'Check if the patient hit his head.' AND (traduccion_ejemplo IS NULL OR traduccion_ejemplo = '');
UPDATE vocabulario SET traduccion_ejemplo = 'Puede mover la pierna izquierda sin dolor.' WHERE termino_en = 'Leg' AND oracion_ejemplo = 'He can move his left leg without pain.' AND (traduccion_ejemplo IS NULL OR traduccion_ejemplo = '');
UPDATE vocabulario SET traduccion_ejemplo = 'Las radiografías confirmaron una fractura en el brazo derecho.' WHERE termino_en = 'Fracture' AND oracion_ejemplo = 'X-rays confirmed a right arm fracture.' AND (traduccion_ejemplo IS NULL OR traduccion_ejemplo = '');
UPDATE vocabulario SET traduccion_ejemplo = 'Se cayó en el hotel ayer.' WHERE termino_en = 'Fell' AND oracion_ejemplo = 'He fell down at the hotel yesterday.' AND (traduccion_ejemplo IS NULL OR traduccion_ejemplo = '');
UPDATE vocabulario SET traduccion_ejemplo = 'El paciente tuvo un accidente antes del ingreso.' WHERE termino_en = 'Had an accident' AND oracion_ejemplo = 'The patient had an accident before admission.' AND (traduccion_ejemplo IS NULL OR traduccion_ejemplo = '');
UPDATE vocabulario SET traduccion_ejemplo = 'Su piel se ve pálida por el dolor.' WHERE termino_en = 'Pale' AND oracion_ejemplo = 'His skin looks pale due to pain.' AND (traduccion_ejemplo IS NULL OR traduccion_ejemplo = '');
UPDATE vocabulario SET traduccion_ejemplo = 'Mr. Thomas se siente cansado después del traslado de urgencias.' WHERE termino_en = 'Tired' AND oracion_ejemplo = 'Mr. Thomas feels tired after the emergency transfer.' AND (traduccion_ejemplo IS NULL OR traduccion_ejemplo = '');

-- Módulo 2 · RAP 3
UPDATE vocabulario SET traduccion_ejemplo = 'La familia está en la sala de espera.' WHERE termino_en = 'Waiting room' AND oracion_ejemplo = 'The family is in the waiting room.' AND (traduccion_ejemplo IS NULL OR traduccion_ejemplo = '');
UPDATE vocabulario SET traduccion_ejemplo = 'Mr. Thomas está descansando en la cama del hospital.' WHERE termino_en = 'Hospital bed' AND oracion_ejemplo = 'Mr. Thomas is resting in the hospital bed.' AND (traduccion_ejemplo IS NULL OR traduccion_ejemplo = '');
UPDATE vocabulario SET traduccion_ejemplo = 'Los paramédicos lo trasladaron en una camilla.' WHERE termino_en = 'Stretcher' AND oracion_ejemplo = 'Paramedics transferred him on a stretcher.' AND (traduccion_ejemplo IS NULL OR traduccion_ejemplo = '');
UPDATE vocabulario SET traduccion_ejemplo = 'El paciente fue trasladado a la habitación 204.' WHERE termino_en = 'Room 204' AND oracion_ejemplo = 'The patient was transferred to room 204.' AND (traduccion_ejemplo IS NULL OR traduccion_ejemplo = '');
UPDATE vocabulario SET traduccion_ejemplo = 'La habitación está fría, así que le dimos una cobija.' WHERE termino_en = 'Cold' AND oracion_ejemplo = 'The room feels cold, so we gave him a blanket.' AND (traduccion_ejemplo IS NULL OR traduccion_ejemplo = '');
UPDATE vocabulario SET traduccion_ejemplo = 'Sus signos vitales están estables hoy.' WHERE termino_en = 'Stable' AND oracion_ejemplo = 'His vital signs are stable today.' AND (traduccion_ejemplo IS NULL OR traduccion_ejemplo = '');
UPDATE vocabulario SET traduccion_ejemplo = 'Controle los signos vitales cada cuatro horas.' WHERE termino_en = 'Vital signs' AND oracion_ejemplo = 'Check vital signs every four hours.' AND (traduccion_ejemplo IS NULL OR traduccion_ejemplo = '');
UPDATE vocabulario SET traduccion_ejemplo = 'Tiene la rodilla inflamada por la caída.' WHERE termino_en = 'Swollen knee' AND oracion_ejemplo = 'He has a swollen knee from the fall.' AND (traduccion_ejemplo IS NULL OR traduccion_ejemplo = '');

-- Módulo 3 · RAP 4
UPDATE vocabulario SET traduccion_ejemplo = 'Usamos el termómetro para medir su temperatura.' WHERE termino_en = 'Thermometer' AND oracion_ejemplo = 'We use the thermometer to check his temperature.' AND (traduccion_ejemplo IS NULL OR traduccion_ejemplo = '');
UPDATE vocabulario SET traduccion_ejemplo = 'Ausculte su pecho con el estetoscopio.' WHERE termino_en = 'Stethoscope' AND oracion_ejemplo = 'Listen to his chest with the stethoscope.' AND (traduccion_ejemplo IS NULL OR traduccion_ejemplo = '');
UPDATE vocabulario SET traduccion_ejemplo = 'El monitor de presión arterial está activo en este momento.' WHERE termino_en = 'Blood pressure monitor' AND oracion_ejemplo = 'The blood pressure monitor is active right now.' AND (traduccion_ejemplo IS NULL OR traduccion_ejemplo = '');
UPDATE vocabulario SET traduccion_ejemplo = 'El pulsioxímetro muestra la saturación de oxígeno.' WHERE termino_en = 'Pulse oximeter' AND oracion_ejemplo = 'The pulse oximeter shows oxygen saturation.' AND (traduccion_ejemplo IS NULL OR traduccion_ejemplo = '');
UPDATE vocabulario SET traduccion_ejemplo = 'Salude con amabilidad al visitante en la habitación.' WHERE termino_en = 'Visitor' AND oracion_ejemplo = 'Greet the visitor politely in the room.' AND (traduccion_ejemplo IS NULL OR traduccion_ejemplo = '');
UPDATE vocabulario SET traduccion_ejemplo = 'Explique el procedimiento de rutina al visitante.' WHERE termino_en = 'Procedure' AND oracion_ejemplo = 'Explain the routine procedure to the visitor.' AND (traduccion_ejemplo IS NULL OR traduccion_ejemplo = '');
UPDATE vocabulario SET traduccion_ejemplo = 'La enfermera Sarah está midiendo la temperatura en este momento.' WHERE termino_en = 'Checking temperature' AND oracion_ejemplo = 'Nurse Sarah is checking temperature right now.' AND (traduccion_ejemplo IS NULL OR traduccion_ejemplo = '');
UPDATE vocabulario SET traduccion_ejemplo = 'Hablamos con los familiares sobre la evolución del paciente.' WHERE termino_en = 'Talk to family' AND oracion_ejemplo = 'We talk to family members about patient progress.' AND (traduccion_ejemplo IS NULL OR traduccion_ejemplo = '');

-- Módulo 3 · RAP 5
UPDATE vocabulario SET traduccion_ejemplo = 'Diligencie la lista de chequeo de enfermería de este turno.' WHERE termino_en = 'Checklist' AND oracion_ejemplo = 'Fill out the nursing checklist for this shift.' AND (traduccion_ejemplo IS NULL OR traduccion_ejemplo = '');
UPDATE vocabulario SET traduccion_ejemplo = 'Proponga una mejora al jefe de enfermería.' WHERE termino_en = 'Nurse Manager' AND oracion_ejemplo = 'Propose an improvement to the Nurse Manager.' AND (traduccion_ejemplo IS NULL OR traduccion_ejemplo = '');
UPDATE vocabulario SET traduccion_ejemplo = 'Administro medicamentos a las 8 AM como parte de nuestra rutina.' WHERE termino_en = 'Give medication' AND oracion_ejemplo = 'I give medication at 8 AM as part of our routine.' AND (traduccion_ejemplo IS NULL OR traduccion_ejemplo = '');
UPDATE vocabulario SET traduccion_ejemplo = 'La rutina diaria incluye las rondas de la mañana y la actualización de la lista de chequeo.' WHERE termino_en = 'Routine' AND oracion_ejemplo = 'Daily routine includes morning rounds and checklist update.' AND (traduccion_ejemplo IS NULL OR traduccion_ejemplo = '');
UPDATE vocabulario SET traduccion_ejemplo = 'Prepare una jeringa estéril para la medicación de rutina.' WHERE termino_en = 'Syringe' AND oracion_ejemplo = 'Prepare a sterile syringe for routine medication.' AND (traduccion_ejemplo IS NULL OR traduccion_ejemplo = '');
UPDATE vocabulario SET traduccion_ejemplo = 'Revise la velocidad del goteo intravenoso cada hora.' WHERE termino_en = 'IV Drip' AND oracion_ejemplo = 'Check the IV drip flow rate every hour.' AND (traduccion_ejemplo IS NULL OR traduccion_ejemplo = '');
UPDATE vocabulario SET traduccion_ejemplo = 'Use el gluciómetro para medir los niveles de glucosa.' WHERE termino_en = 'Glucometer' AND oracion_ejemplo = 'Use the glucometer to measure glucose levels.' AND (traduccion_ejemplo IS NULL OR traduccion_ejemplo = '');
UPDATE vocabulario SET traduccion_ejemplo = 'Deberíamos actualizar la lista de chequeo para ahorrar tiempo.' WHERE termino_en = 'Update' AND oracion_ejemplo = 'We should update the checklist to save time.' AND (traduccion_ejemplo IS NULL OR traduccion_ejemplo = '');

-- Módulo 4 · RAP 6
UPDATE vocabulario SET traduccion_ejemplo = 'Buenas noticias, Mr. Thomas: sus signos vitales están estables.' WHERE termino_en = 'Vital signs stable' AND oracion_ejemplo = 'Good news, Mr. Thomas: your vital signs are stable.' AND (traduccion_ejemplo IS NULL OR traduccion_ejemplo = '');
UPDATE vocabulario SET traduccion_ejemplo = 'Su dolor se resolvió, así que ahora no necesita un analgésico.' WHERE termino_en = 'Pain resolved' AND oracion_ejemplo = 'His pain is resolved, so he does not need a painkiller now.' AND (traduccion_ejemplo IS NULL OR traduccion_ejemplo = '');
UPDATE vocabulario SET traduccion_ejemplo = 'El paciente está listo para el alta esta tarde.' WHERE termino_en = 'Ready for discharge' AND oracion_ejemplo = 'The patient is ready for discharge this afternoon.' AND (traduccion_ejemplo IS NULL OR traduccion_ejemplo = '');
UPDATE vocabulario SET traduccion_ejemplo = 'El Dr. Smith firmó la orden de alta.' WHERE termino_en = 'Discharge' AND oracion_ejemplo = 'Dr. Smith signed the discharge order.' AND (traduccion_ejemplo IS NULL OR traduccion_ejemplo = '');
UPDATE vocabulario SET traduccion_ejemplo = 'Lleve esta receta médica a la farmacia.' WHERE termino_en = 'Prescription' AND oracion_ejemplo = 'Take this prescription to the pharmacy.' AND (traduccion_ejemplo IS NULL OR traduccion_ejemplo = '');
UPDATE vocabulario SET traduccion_ejemplo = 'Tome un analgésico cada ocho horas si tiene dolor.' WHERE termino_en = 'Painkiller' AND oracion_ejemplo = 'Take one painkiller every eight hours if you have pain.' AND (traduccion_ejemplo IS NULL OR traduccion_ejemplo = '');
UPDATE vocabulario SET traduccion_ejemplo = 'Debe tomar esta pastilla después del desayuno.' WHERE termino_en = 'Pill' AND oracion_ejemplo = 'You must take this pill after breakfast.' AND (traduccion_ejemplo IS NULL OR traduccion_ejemplo = '');
UPDATE vocabulario SET traduccion_ejemplo = 'No tome más de una dosis cada ocho horas.' WHERE termino_en = 'Dose' AND oracion_ejemplo = 'Do not take more than one dose every eight hours.' AND (traduccion_ejemplo IS NULL OR traduccion_ejemplo = '');
UPDATE vocabulario SET traduccion_ejemplo = 'Su cita de control es el lunes a las 9 AM.' WHERE termino_en = 'Follow-up appointment' AND oracion_ejemplo = 'Your follow-up appointment is on Monday at 9 AM.' AND (traduccion_ejemplo IS NULL OR traduccion_ejemplo = '');
UPDATE vocabulario SET traduccion_ejemplo = 'El descanso es importante para una recuperación rápida.' WHERE termino_en = 'Recovery' AND oracion_ejemplo = 'Rest is important for a fast recovery.' AND (traduccion_ejemplo IS NULL OR traduccion_ejemplo = '');
UPDATE vocabulario SET traduccion_ejemplo = 'La enfermera anota los resultados del paciente en la lista de chequeo.' WHERE termino_en = 'Outcomes' AND oracion_ejemplo = 'The nurse writes the patient outcomes in the checklist.' AND (traduccion_ejemplo IS NULL OR traduccion_ejemplo = '');
UPDATE vocabulario SET traduccion_ejemplo = 'Su hija lo ayudará con el cuidado en casa.' WHERE termino_en = 'Home care' AND oracion_ejemplo = 'His daughter will help him with home care.' AND (traduccion_ejemplo IS NULL OR traduccion_ejemplo = '');
UPDATE vocabulario SET traduccion_ejemplo = 'Debería tomar mucha agua.' WHERE termino_en = 'Should' AND oracion_ejemplo = 'You should drink plenty of water.' AND (traduccion_ejemplo IS NULL OR traduccion_ejemplo = '');
UPDATE vocabulario SET traduccion_ejemplo = 'Debe tomar su medicamento todos los días.' WHERE termino_en = 'Must' AND oracion_ejemplo = 'You must take your medicine every day.' AND (traduccion_ejemplo IS NULL OR traduccion_ejemplo = '');
UPDATE vocabulario SET traduccion_ejemplo = 'Debería descansar en casa durante cinco días.' WHERE termino_en = 'Rest' AND oracion_ejemplo = 'You should rest at home for five days.' AND (traduccion_ejemplo IS NULL OR traduccion_ejemplo = '');
UPDATE vocabulario SET traduccion_ejemplo = 'La temperatura está normal hoy.' WHERE termino_en = 'Normal' AND oracion_ejemplo = 'The temperature is normal today.' AND (traduccion_ejemplo IS NULL OR traduccion_ejemplo = '');

-- Verificación
SELECT COUNT(*) AS palabras,
       SUM(traduccion_ejemplo IS NOT NULL AND traduccion_ejemplo <> '') AS con_traduccion
FROM vocabulario;
