USE smash_code;

CREATE TABLE IF NOT EXISTS sesion_practica (
    id VARCHAR(36) NOT NULL PRIMARY KEY,
    usuario_id VARCHAR(36) NOT NULL,
    nivel_id VARCHAR(36) NOT NULL,
    etapa INT NOT NULL DEFAULT 0,
    iniciada_en DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
    INDEX idx_practica_usuario_modulo (usuario_id, nivel_id),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    FOREIGN KEY (nivel_id) REFERENCES nivel(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS sesion_quiz (
    id VARCHAR(36) NOT NULL PRIMARY KEY,
    usuario_id VARCHAR(36) NOT NULL,
    rap_id VARCHAR(36) NOT NULL,
    quiz_id VARCHAR(36) NOT NULL,
    iniciada_en DATETIME(6) NOT NULL DEFAULT CURRENT_TIMESTAMP(6),
    vence_en DATETIME(6) NOT NULL,
    preguntas_json JSON NOT NULL,
    puntaje_minimo DECIMAL(5,2) NOT NULL,
    resultado_json JSON NULL,
    INDEX idx_sesion_quiz_usuario (usuario_id),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    FOREIGN KEY (rap_id) REFERENCES rap(id),
    FOREIGN KEY (quiz_id) REFERENCES quiz(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET @sql = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='intento_ejercicio' AND COLUMN_NAME='solicitud_id')=0,
    'ALTER TABLE intento_ejercicio ADD COLUMN solicitud_id VARCHAR(36) NULL, ADD COLUMN resultado_json JSON NULL, ADD UNIQUE KEY uk_ejercicio_solicitud (usuario_id, solicitud_id)', 'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
