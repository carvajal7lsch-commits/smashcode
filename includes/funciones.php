<?php
/**
 * funciones.php
 * Funciones utilitarias globales del sistema.
 * Incluye sanitización, redirección y generación de tokens.
 */

// Definir la ruta base del proyecto de manera dinámica (funciona en raíz del dominio/puerto o en subcarpetas)
if (!defined('PROYECTO_PATH')) {
    $folder_name = basename(dirname(__DIR__));
    $script_name = $_SERVER['SCRIPT_NAME'] ?? '';
    if (strpos($script_name, '/' . $folder_name) === 0) {
        define('PROYECTO_PATH', '/' . $folder_name);
    } else {
        define('PROYECTO_PATH', '');
    }
}


/**
 * Sanitiza una cadena para prevenir XSS.
 * @param string $valor Cadena de entrada
 * @return string Cadena segura
 */
function limpiar(string $valor): string {
    if (strpos($valor, 'Mdulo') !== false) {
        $valor = str_replace('Mdulo', 'Módulo', $valor);
    }
    if (strpos($valor, 'MDULO') !== false) {
        $valor = str_replace('MDULO', 'MÓDULO', $valor);
    }
    return htmlspecialchars(strip_tags(trim($valor)), ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

/**
 * Genera un token CSRF y lo guarda en sesión.
 * @return string Token generado
 */
function generarTokenCSRF(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Valida el token CSRF enviado por el formulario.
 * @param string $token Token recibido del formulario
 * @return bool
 */
function validarTokenCSRF(string $token): bool {
    return isset($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Genera un UUID v4 compatible con PHP 7+.
 * @return string UUID
 */
function generarUUID(): string {
    $datos = random_bytes(16);
    $datos[6] = chr(ord($datos[6]) & 0x0f | 0x40);
    $datos[8] = chr(ord($datos[8]) & 0x3f | 0x80);
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($datos), 4));
}

/**
 * Redirige a una URL relativa del proyecto.
 * @param string $ruta Ruta relativa
 */
function redirigir(string $ruta): void {
    header('Location: ' . PROYECTO_PATH . '/' . ltrim($ruta, '/'));
    exit;
}

/**
 * Formatea un número para mostrar puntos XP (ej: 1240 → 1.240).
 * @param int $puntos
 * @return string
 */
function formatearXP(int $puntos): string {
    return number_format($puntos, 0, ',', '.');
}

/**
 * Detecta si el hablante de un diálogo es masculino o femenino para síntesis de voz (TTS).
 * @param string $hablante Nombre o rol del hablante
 * @param int $ordenTurno Orden secuencial del turno
 * @return string 'male' o 'female'
 */
function detectarGeneroHablante(string $hablante, int $ordenTurno = 1): string {
    $h = mb_strtolower(trim($hablante));
    
    // Nombres o roles masculinos
    if (
        strpos($h, 'david') !== false ||
        strpos($h, 'mr.') !== false ||
        strpos($h, 'carlos') !== false ||
        strpos($h, 'doctor') !== false ||
        strpos($h, 'manager') !== false ||
        strpos($h, 'enfermero') !== false ||
        strpos($h, 'male') !== false ||
        strpos($h, 'nurse a') !== false
    ) {
        return 'male';
    }

    // Nombres o roles femeninos
    if (
        strpos($h, 'carolina') !== false ||
        strpos($h, 'sarah') !== false ||
        strpos($h, 'mrs.') !== false ||
        strpos($h, 'ms.') !== false ||
        strpos($h, 'miss') !== false ||
        strpos($h, 'enfermera') !== false ||
        strpos($h, 'female') !== false ||
        strpos($h, 'daughter') !== false ||
        strpos($h, 'nurse b') !== false
    ) {
        return 'female';
    }

    // Por defecto alternar por orden de turno (impares masculino / interlocutor 1, pares femenino / interlocutor 2)
    return ($ordenTurno % 2 === 1) ? 'male' : 'female';
}

