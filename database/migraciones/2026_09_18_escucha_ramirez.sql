-- Corrige el enunciado del ejercicio escucha_escribe de deletreo (W05).
-- Retira el spoiler visible "(Expected answer: RAMIREZ)" manteniendo
-- intactos el ID, opciones, intentos y progreso existentes.
UPDATE ejercicio
SET enunciado = '🎧 Listening Exercise: Press the play button and listen carefully. The nurse is spelling her last name. Type exactly what you hear letter by letter.'
WHERE id = 'df459ef3-b2e4-11f1-a733-d843ae078a11'
   OR enunciado LIKE '%The nurse is spelling her last name%Expected answer: RAMIREZ%';
