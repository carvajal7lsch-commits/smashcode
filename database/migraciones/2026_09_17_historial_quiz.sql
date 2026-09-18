USE smash_code;

SET @sql = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='intento_quiz' AND COLUMN_NAME='puntaje_minimo_original')=0,
    'ALTER TABLE intento_quiz ADD COLUMN puntaje_minimo_original DECIMAL(5,2) NULL', 'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='respuesta_quiz' AND COLUMN_NAME='texto_pregunta_original')=0,
    'ALTER TABLE respuesta_quiz ADD COLUMN texto_pregunta_original VARCHAR(1000) NULL', 'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Los datos antiguos quedan NULL: no se puede reconstruir su criterio original con certeza.
