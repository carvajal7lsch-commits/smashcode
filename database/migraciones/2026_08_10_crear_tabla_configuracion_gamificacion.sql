-- =============================================================
-- MIGRACIÓN: Tabla de Configuración de Gamificación y Puntos XP
-- =============================================================
SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;
USE smash_code;

CREATE TABLE IF NOT EXISTS configuracion_gamificacion (
    clave VARCHAR(50) PRIMARY KEY,
    valor INT NOT NULL,
    descripcion VARCHAR(255) NOT NULL,
    actualizado_en DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertar configuraciones base por defecto
INSERT INTO configuracion_gamificacion (clave, valor, descripcion) VALUES
('xp_ejercicio_correcto', 10, 'Puntos XP otorgados por cada ejercicio interactivo resuelto correctamente'),
('xp_quiz_aprobado', 50, 'Puntos XP otorgados por aprobar una evaluación / quiz de RAP (>= 60%)'),
('xp_quiz_perfecto', 100, 'Bonus extra de puntos XP por completar un quiz con puntaje perfecto (100%)'),
('xp_por_nivel', 500, 'Cantidad de puntos XP requeridos por cada nivel de rango clínico'),
('dias_racha_insignia', 7, 'Días consecutivos de estudio requeridos para desbloquear la insignia de Racha')
ON DUPLICATE KEY UPDATE 
    descripcion = VALUES(descripcion);
