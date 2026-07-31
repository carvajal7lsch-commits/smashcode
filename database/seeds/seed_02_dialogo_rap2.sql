-- =============================================================
-- SEED PARTE 2: Diálogo RAP 2 (Módulo 2: Work Life Interaction)
-- Escenario: Entregando y recibiendo el turno de Mr. Thomas (Room 204)
-- =============================================================

SET NAMES utf8mb4;
USE smash_code;

SELECT id INTO @RAP2 FROM rap WHERE nivel_id IN (SELECT id FROM nivel WHERE orden = 2) LIMIT 1;

-- Limpieza previa
DELETE FROM turno_dialogo WHERE dialogo_id IN (SELECT id FROM dialogo WHERE rap_id = @RAP2);
DELETE FROM dialogo WHERE rap_id = @RAP2;

SET @dia2 = UUID();

INSERT INTO dialogo (id, rap_id, titulo, contexto, participantes, audio_completo_url, activo)
VALUES (
    @dia2,
    @RAP2,
    'Shift Handover: Mr. Thomas Case',
    'Nurse Carolina arrives for the shift handover. Nurse David explains the condition, history, and current status of Mr. Thomas in Room 204.',
    'Nurse Carolina, Nurse David',
    NULL,
    1
);

INSERT INTO turno_dialogo (id, dialogo_id, orden_turno, hablante, texto_en, texto_es, audio_url)
VALUES
(UUID(), @dia2, 1, 'Nurse Carolina', 'How is Mr. Thomas in room 204?', '¿Cómo está el Sr. Thomas en la habitación 204?', NULL),
(UUID(), @dia2, 2, 'Nurse David', 'He is an older man. He feels tired today. Yesterday, he fell at the hotel.', 'Es un hombre mayor. Se siente cansado hoy. Ayer se cayó en el hotel.', NULL),
(UUID(), @dia2, 3, 'Nurse Carolina', 'Did he suffer any fracture?', '¿Sufrió alguna fractura?', NULL),
(UUID(), @dia2, 4, 'Nurse David', 'Yes, he has a fracture in his right arm and a bandage on his leg.', 'Sí, tiene una fractura en su brazo derecho y un vendaje en su pierna.', NULL),
(UUID(), @dia2, 5, 'Nurse Carolina', 'Is he stable now?', '¿Está estable ahora?', NULL),
(UUID(), @dia2, 6, 'Nurse David', 'His vital signs are stable, but the room is cold and he needs rest.', 'Sus signos vitales están estables, pero la habitación está fría y necesita descansar.', NULL);