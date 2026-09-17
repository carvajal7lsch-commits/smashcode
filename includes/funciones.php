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
 * Normaliza y restaura tildes y caracteres en español que hayan sido truncados
 * o dañados por codificaciones no-UTF8 en servidores y bases de datos.
 * @param string $texto
 * @return string
 */
function normalizarTextoEspanol(string $texto): string {
    static $mapaFrases = [
        // Módulos y RAPs
        'Mdulo' => 'Módulo',
        'MDULO' => 'MÓDULO',
        'Modulo' => 'Módulo',
        'MODULO' => 'MÓDULO',
        'Informacin' => 'Información',
        'Interaccin' => 'Interacción',
        'Evaluacin' => 'Evaluación',
        'Planeacin' => 'Planeación',
        'Ejecucin' => 'Ejecución',
        'Atencin' => 'Atención',
        'Prctica' => 'Práctica',

        // Diálogos Módulo 1 - RAP 1 (First Day at the Clinic)
        'Buenos das! Bienvenida a la clnica. Eres la nueva enfermera?' => '¡Buenos días! Bienvenida a la clínica. ¿Eres la nueva enfermera?',
        'Buenos das! Bienvenida a la clinica. Eres la nueva enfermera?' => '¡Buenos días! Bienvenida a la clínica. ¿Eres la nueva enfermera?',
        'Buenos dias! Bienvenida a la clinica. Eres la nueva enfermera?' => '¡Buenos días! Bienvenida a la clínica. ¿Eres la nueva enfermera?',
        'Buenos das! S, lo soy. Mi nombre es Carolina Ramrez. Mucho gusto!' => '¡Buenos días! Sí, lo soy. Mi nombre es Carolina Ramírez. ¡Mucho gusto!',
        'Buenos das! Si, lo soy. Mi nombre es Carolina Ramrez. Mucho gusto!' => '¡Buenos días! Sí, lo soy. Mi nombre es Carolina Ramírez. ¡Mucho gusto!',
        'Buenos dias! Si, lo soy. Mi nombre es Carolina Ramirez. Mucho gusto!' => '¡Buenos días! Sí, lo soy. Mi nombre es Carolina Ramírez. ¡Mucho gusto!',
        'Igualmente, Carolina. Soy David Torres. Soy el enfermero encargado del turno de la maana.' => 'Igualmente, Carolina. Soy David Torres. Soy el enfermero encargado del turno de la mañana.',
        'Igualmente, Carolina. Soy David Torres. Soy el enfermero encargado del turno de la manana.' => 'Igualmente, Carolina. Soy David Torres. Soy el enfermero encargado del turno de la mañana.',
        'Excelente! Cul es tu apellido, David? Cmo se escribe?' => '¡Excelente! ¿Cuál es tu apellido, David? ¿Cómo se escribe?',
        'Excelente! Cual es tu apellido, David? Como se escribe?' => '¡Excelente! ¿Cuál es tu apellido, David? ¿Cómo se escribe?',
        'Mi apellido es Torres. T-O-R-R-E-S. Y tu apellido? Cmo se escribe Ramrez?' => 'Mi apellido es Torres. T-O-R-R-E-S. ¿Y tu apellido? ¿Cómo se escribe Ramírez?',
        'Mi apellido es Torres. T-O-R-R-E-S. Y tu apellido? Como se escribe Ramirez?' => 'Mi apellido es Torres. T-O-R-R-E-S. ¿Y tu apellido? ¿Cómo se escribe Ramírez?',
        'Es R-A-M-R-E-Z. Soy de Colombia. Cul es tu nacionalidad?' => 'Es R-A-M-Í-R-E-Z. Soy de Colombia. ¿Cuál es tu nacionalidad?',
        'Es R-A-M-I-R-E-Z. Soy de Colombia. Cual es tu nacionalidad?' => 'Es R-A-M-Í-R-E-Z. Soy de Colombia. ¿Cuál es tu nacionalidad?',
        'Soy mexicano. Soy de Guadalajara. Y cul es tu nmero de telfono para la lista de contactos de emergencia?' => 'Soy mexicano. Soy de Guadalajara. ¿Y cuál es tu número de teléfono para la lista de contactos de emergencia?',
        'Soy mexicano. Soy de Guadalajara. Y cual es tu numero de telefono para la lista de contactos de emergencia?' => 'Soy mexicano. Soy de Guadalajara. ¿Y cuál es tu número de teléfono para la lista de contactos de emergencia?',
        'Mi nmero de telfono es tres, uno, cero, cinco, cinco, cinco, dos, dos, siete, ocho. Y mi correo es carolina.ramirez@clinic.co' => 'Mi número de teléfono es tres, uno, cero, cinco, cinco, cinco, dos, dos, siete, ocho. Y mi correo es carolina.ramirez@clinic.co',
        'Mi numero de telefono es tres, uno, cero, cinco, cinco, cinco, dos, dos, siete, ocho. Y mi correo es carolina.ramirez@clinic.co' => 'Mi número de teléfono es tres, uno, cero, cinco, cinco, cinco, dos, dos, siete, ocho. Y mi correo es carolina.ramirez@clinic.co',
        'Perfecto, gracias! Buenas tardes, Carolina. Nos vemos en el pabelln.' => '¡Perfecto, gracias! Buenas tardes, Carolina. Nos vemos en el pabellón.',
        'Perfecto, gracias! Buenas tardes, Carolina. Nos vemos en el pabellon.' => '¡Perfecto, gracias! Buenas tardes, Carolina. Nos vemos en el pabellón.',
        'Buenas tardes, David. Hasta luego!' => 'Buenas tardes, David. ¡Hasta luego!',

        // Diálogos Módulo 2 - RAP 2
        'Qu le sucedi a Mr. Thomas ayer antes de llegar a urgencias?' => '¿Qué le sucedió a Mr. Thomas ayer antes de llegar a urgencias?',
        'Que le sucedio a Mr. Thomas ayer antes de llegar a urgencias?' => '¿Qué le sucedió a Mr. Thomas ayer antes de llegar a urgencias?',
        'l se cay en la habitacin de su hotel ayer por la tarde.' => 'Él se cayó en la habitación de su hotel ayer por la tarde.',
        'El se cayo en la habitacion de su hotel ayer por la tarde.' => 'Él se cayó en la habitación de su hotel ayer por la tarde.',
        'Los paramdicos lo trajeron a la clnica en una camilla?' => '¿Los paramédicos lo trajeron a la clínica en una camilla?',
        'Los paramedicos lo trajeron a la clinica en una camilla?' => '¿Los paramédicos lo trajeron a la clínica en una camilla?',
        'S, lo trajeron de inmediato y reportaron una fractura en el brazo derecho.' => 'Sí, lo trajeron de inmediato y reportaron una fractura en el brazo derecho.',
        'Si, lo trajeron de inmediato y reportaron una fractura en el brazo derecho.' => 'Sí, lo trajeron de inmediato y reportaron una fractura en el brazo derecho.',

        // Diálogos Módulo 2 - RAP 3
        'Cmo est Mr. Thomas en la habitacin 204 en este momento?' => '¿Cómo está Mr. Thomas en la habitación 204 en este momento?',
        'Como esta Mr. Thomas en la habitacion 204 en este momento?' => '¿Cómo está Mr. Thomas en la habitación 204 en este momento?',
        'l est plido y cansado, pero sus signos vitales estn estables.' => 'Él está pálido y cansado, pero sus signos vitales están estables.',
        'El esta palido y cansado, pero sus signos vitales estan estables.' => 'Él está pálido y cansado, pero sus signos vitales están estables.',
        'La habitacin es cmoda para l?' => '¿La habitación es cómoda para él?',
        'La habitacion es comoda para el?' => '¿La habitación es cómoda para él?',
        'La habitacin est fra, as que l est descansando bajo una cobija caliente.' => 'La habitación está fría, así que él está descansando bajo una cobija caliente.',
        'La habitacion esta fria, asi que el esta descansando bajo una cobija caliente.' => 'La habitación está fría, así que él está descansando bajo una cobija caliente.',

        // Diálogos Módulo 3 - RAP 4
        'Disculpe, enfermera, qu le est haciendo a mi padre en este momento?' => 'Disculpe, enfermera, ¿qué le está haciendo a mi padre en este momento?',
        'Disculpe, enfermera, que le esta haciendo a mi padre en este momento?' => 'Disculpe, enfermera, ¿qué le está haciendo a mi padre en este momento?',
        'Buenos das. Le estamos midiendo la temperatura y la presin arterial ahora mismo.' => 'Buenos días. Le estamos midiendo la temperatura y la presión arterial ahora mismo.',
        'Buenos dias. Le estamos midiendo la temperatura y la presion arterial ahora mismo.' => 'Buenos días. Le estamos midiendo la temperatura y la presión arterial ahora mismo.',
        'Gracias por explicarme. l se encuentra bien?' => 'Gracias por explicarme. ¿Él se encuentra bien?',
        'Gracias por explicarme. El se encuentra bien?' => 'Gracias por explicarme. ¿Él se encuentra bien?',
        'S, sus signos vitales estn estables y l est descansando cmodamente.' => 'Sí, sus signos vitales están estables y él está descansando cómodamente.',
        'Si, sus signos vitales estan estables y el esta descansando comodamente.' => 'Sí, sus signos vitales están estables y él está descansando cómodamente.',

        // Diálogos Módulo 3 - RAP 5
        'Enfermera Sarah, cmo va progresando nuestra rutina diaria de medicamentos?' => 'Enfermera Sarah, ¿cómo va progresando nuestra rutina diaria de medicamentos?',
        'Enfermera Sarah, como va progresando nuestra rutina diaria de medicamentos?' => 'Enfermera Sarah, ¿cómo va progresando nuestra rutina diaria de medicamentos?',
        'Todo va segn lo programado. Administro medicamentos a las 8 AM cada maana.' => 'Todo va según lo programado. Administro medicamentos a las 8 AM cada mañana.',
        'Todo va segun lo programado. Administro medicamentos a las 8 AM cada manana.' => 'Todo va según lo programado. Administro medicamentos a las 8 AM cada mañana.',
        'Tiene alguna sugerencia para nuestra lista de chequeo de entrega de turno?' => '¿Tiene alguna sugerencia para nuestra lista de chequeo de entrega de turno?',
        'S! Creo que deberamos actualizar la lista de chequeo para Mr. Thomas para registrar los signos vitales ms rpido.' => '¡Sí! Creo que deberíamos actualizar la lista de chequeo para Mr. Thomas para registrar los signos vitales más rápido.',
        'Si! Creo que deberiamos actualizar la lista de chequeo para Mr. Thomas para registrar los signos vitales mas rapido.' => '¡Sí! Creo que deberíamos actualizar la lista de chequeo para Mr. Thomas para registrar los signos vitales más rápido.',

        // Palabras clínicas individuales truncadas o sin tilde
        'habitacin' => 'habitación',
        'presin' => 'presión',
        'clnica' => 'clínica',
        'Clnica' => 'Clínica',
        'mdico' => 'médico',
        'Mdico' => 'Médico',
        'plido' => 'pálido',
        'Plido' => 'Pálido',
        'paramdicos' => 'paramédicos',
        'Paramdicos' => 'Paramédicos',
        'Pulsixmetro' => 'Pulsioxímetro',
        'Pulsioxmetro' => 'Pulsioxímetro',
        'Gluacmetro' => 'Gluciómetro',
        'Glucimetro' => 'Gluciómetro',
        'cmodamente' => 'cómodamente',
        'deberamos' => 'deberíamos',
        'maana' => 'mañana',
        'ms rpido' => 'más rápido',
        'se cay' => 'se cayó',
        'le sucedi' => 'le sucedió',
        'est fra' => 'está fría',
        'cmoda' => 'cómoda',
        'diagnstico' => 'diagnóstico',
        'Diagnstico' => 'Diagnóstico',
        'oxgeno' => 'oxígeno',
        'Oxgeno' => 'Oxígeno',
        'administracin' => 'administración',
        'Administracin' => 'Administración',
        'medicacin' => 'medicación',
        'Medicacin' => 'Medicación',
        'interaccin' => 'interacción',
        'Interaccin' => 'Interacción',
        'Ramrez' => 'Ramírez',
        'Ramirez' => 'Ramírez',
        'Buenos das' => 'Buenos días',
        'Buenos dias' => 'Buenos días',
        'pabelln' => 'pabellón',
        'pabellon' => 'pabellón',
        'nmero de telfono' => 'número de teléfono',
        'numero de telefono' => 'número de teléfono',
        'correo electrnico' => 'correo electrónico',
        'correo electronico' => 'correo electrónico',
        'Adis' => 'Adiós',
        'Cmo ests?' => '¿Cómo estás?',
        'Como estas?' => '¿Cómo estás?',
        'T eres / Usted es' => 'Tú eres / Usted es',
        'l es / l est' => 'Él es / Él está',
        'Ella es / Ella est' => 'Ella es / Ella está'
    ];

    return strtr($texto, $mapaFrases);
}

/**
 * Recorta y escapa una cadena para HTML sin modificar su ortografía.
 * @param string $valor Cadena de entrada
 * @return string Cadena escapada
 */
function limpiar(string $valor): string {
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
 * Formatea segundos como tiempo de estudio legible (ej: 3725 → 1 h 2 min).
 * Se usa en el panel de progreso del aprendiz (HU05).
 * @param int $segundos
 * @return string
 */
function formatearDuracion(int $segundos): string {
    if ($segundos <= 0) {
        return '0 min';
    }

    $horas   = intdiv($segundos, 3600);
    $minutos = intdiv($segundos % 3600, 60);

    if ($horas > 0) {
        return $minutos > 0 ? $horas . ' h ' . $minutos . ' min' : $horas . ' h';
    }

    // Por debajo del minuto se muestran los segundos para no mostrar siempre "0 min"
    return $minutos > 0 ? $minutos . ' min' : $segundos . ' s';
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

