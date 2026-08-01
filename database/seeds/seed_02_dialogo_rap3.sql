-- =============================================================
-- SEED PARTE 2: Diálogo RAP 3 (Módulo 3: Work Place Communication)
-- Escenario: Explicación a visitante y propuesta de mejora al Nurse Manager
-- =============================================================

SET NAMES utf8mb4;
USE smash_code;

SELECT id INTO @RAP3 FROM rap WHERE nivel_id IN (SELECT id FROM nivel WHERE orden = 3) LIMIT 1;
SET @dia3 = UUID();

-- Limpieza previa
DELETE FROM turno_dialogo WHERE dialogo_id IN (SELECT id FROM dialogo WHERE rap_id = @RAP3);
DELETE FROM dialogo WHERE rap_id = @RAP3;

INSERT INTO dialogo (id, rap_id, titulo, contexto, participantes, audio_completo_url, activo)
VALUES (
    @dia3,
    @RAP3,
    'Workplace Interaction: Visitor Explanation & Team Shift Update',
    'Nurse Sarah explains a routine procedure to Mr. Thomas\'s daughter (visitor) and proposes a checklist improvement to the Nurse Manager.',
    'Nurse Sarah, Visitor, Nurse Manager',
    NULL,
    1
);

INSERT INTO turno_dialogo (id, dialogo_id, orden_turno, hablante, texto_en, texto_es, audio_url) VALUES
(UUID(), @dia3, 1, 'Visitor', 'Excuse me, nurse, what are you doing with my father right now?', 'Disculpe, enfermera, ¿qué le está haciendo a mi padre en este momento?', NULL),
(UUID(), @dia3, 2, 'Nurse Sarah', 'Good morning. We are checking his temperature and blood pressure right now.', 'Buenos días. Le estamos midiendo la temperatura y la presión arterial ahora mismo.', NULL),
(UUID(), @dia3, 3, 'Visitor', 'Thank you for the update. Does he take medication at this hour?', 'Gracias por la información. ¿Él toma medicamentos a esta hora?', NULL),
(UUID(), @dia3, 4, 'Nurse Sarah', 'Yes, I give medication at 8 AM as part of our daily routine.', 'Sí, administro medicamentos a las 8 AM como parte de nuestra rutina diaria.', NULL),
(UUID(), @dia3, 5, 'Nurse Manager', 'Nurse Sarah, do you have any suggestions for our shift handover?', 'Enfermera Sarah, ¿tiene alguna sugerencia para nuestra entrega de turno?', NULL),
(UUID(), @dia3, 6, 'Nurse Sarah', 'Yes! I think we should update the checklist for Mr. Thomas to track vital signs faster.', '¡Sí! Creo que deberíamos actualizar la lista de chequeo para Mr. Thomas para registrar los signos vitales más rápido.', NULL);
