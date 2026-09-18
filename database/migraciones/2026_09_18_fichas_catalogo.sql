-- =============================================================
-- MIGRACIÓN: Catálogo de Fichas SENA y Relación Instructor-Ficha (W11 / W12)
-- Fecha: 2026-09-18
-- Descripción:
--   1. Crea la tabla `fichas` con relación a `programa_formacion`.
--   2. Registra la ficha activa de Enfermería '3142784' y fichas de prueba.
--   3. Crea la tabla `instructor_ficha` para soportar múltiples fichas por instructor.
--   4. Marca en `migracion_aplicada` de forma idempotente sin afectar el acceso
--      de usuarios preexistentes con fichas heredadas.
-- =============================================================

SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;
USE smash_code;

CREATE TABLE IF NOT EXISTS fichas (
    id          VARCHAR(36)  NOT NULL PRIMARY KEY,
    codigo      VARCHAR(20)  NOT NULL,
    programa_id VARCHAR(36)  NOT NULL,
    activo      TINYINT(1)   NOT NULL DEFAULT 1,
    creado_en   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_fichas_codigo (codigo),
    INDEX idx_fichas_programa (programa_id),
    CONSTRAINT fk_fichas_programa FOREIGN KEY (programa_id) REFERENCES programa_formacion(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS instructor_ficha (
    instructor_id VARCHAR(36) NOT NULL,
    ficha_id      VARCHAR(36) NOT NULL,
    asignado_en   DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (instructor_id, ficha_id),
    INDEX idx_if_ficha (ficha_id),
    CONSTRAINT fk_if_instructor FOREIGN KEY (instructor_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    CONSTRAINT fk_if_ficha FOREIGN KEY (ficha_id) REFERENCES fichas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Fichas iniciales del programa Técnico en Enfermería
-- Se vinculan al programa activo existente en la base de datos
INSERT IGNORE INTO fichas (id, codigo, programa_id, activo)
SELECT 'f1c4a001-3142-7840-0000-000000000001', '3142784', id, 1
FROM programa_formacion WHERE eliminado = 0 ORDER BY activo DESC, id ASC LIMIT 1;

INSERT IGNORE INTO fichas (id, codigo, programa_id, activo)
SELECT 'f1c4a002-2877-6500-0000-000000000002', '2877650', id, 1
FROM programa_formacion WHERE eliminado = 0 ORDER BY activo DESC, id ASC LIMIT 1;

INSERT IGNORE INTO fichas (id, codigo, programa_id, activo)
SELECT 'f1c4a003-2234-8910-0000-000000000003', '2234891', id, 1
FROM programa_formacion WHERE eliminado = 0 ORDER BY activo DESC, id ASC LIMIT 1;

CREATE TABLE IF NOT EXISTS migracion_aplicada (
    nombre VARCHAR(191) NOT NULL PRIMARY KEY,
    aplicada_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT IGNORE INTO migracion_aplicada(nombre) VALUES ('2026_09_18_fichas_catalogo');

-- Verificación
SELECT
    (SELECT COUNT(*) FROM information_schema.TABLES
      WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'fichas') AS tabla_fichas_creada,
    (SELECT COUNT(*) FROM information_schema.TABLES
      WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'instructor_ficha') AS tabla_instructor_ficha_creada,
    (SELECT COUNT(*) FROM fichas) AS total_fichas;
