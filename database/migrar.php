<?php
/**
 * migrar.php
 * Aplica el esquema base (solo si la base está vacía) y las migraciones
 * vigentes, en orden. Todas son idempotentes: se puede ejecutar las veces
 * que haga falta sin romper ni duplicar nada.
 *
 * Uso en terminal (XAMPP o VPS):
 *   php database/migrar.php
 *
 * En Docker lo ejecuta el entrypoint antes de arrancar Apache, así que
 * levantar el stack deja la base al día sin pasos manuales.
 */

require_once __DIR__ . '/../config/conexion.php';

/**
 * Migraciones que se aplican, en orden cronológico.
 *
 * La lista es explícita y no un `glob()` del directorio a propósito: hay dos
 * archivos en database/migraciones/ que NO se deben ejecutar hoy.
 *
 *  - 2026_06_16_hu17_programa_formacion.sql
 *      Usa ADD COLUMN IF NOT EXISTS, sintaxis de MariaDB que falla en MySQL 8
 *      (la imagen del docker-compose). Ya es innecesaria: esas columnas vienen
 *      en el esquema base.
 *
 *  - 2026_07_27_actualizar_nombres_raps.sql
 *      Renombra TODOS los RAPs a "RAP <n>". Correrla ahora borraría los
 *      títulos reales de los seis RAPs.
 *
 *  - 2026_08_10_fix_vps_niveles_raps.sql
 *      Reemplazada por 2026_08_21_reubicar_raps_a_modulos.sql. Su DELETE
 *      FROM rap falla con ERROR 1451 en cuanto los RAPs tienen contenido.
 */
const MIGRACIONES = [
    '2026_07_27_google_login.sql',
    '2026_08_10_crear_tabla_configuracion_gamificacion.sql',
    '2026_08_21_reubicar_raps_a_modulos.sql',
    '2026_08_21_leaderboard_semanal.sql',
    '2026_09_14_progreso_por_modulo.sql',
    '2026_09_14_retirar_raps_duplicados.sql',
    '2026_09_14_activo_en_catalogos.sql',
    '2026_09_15_restaurar_tildes.sql',
    '2026_09_15_contenido_rap6.sql',
    '2026_09_15_quiz_rondas_y_preguntas_activas.sql',
    '2026_09_15_insignias_por_modulo.sql',
    '2026_09_15_ejercicio_instrucciones.sql',
    '2026_09_15_dialogos_anotaciones_y_turnos.sql',
];

const INTENTOS_CONEXION = 30;
const ESPERA_SEGUNDOS   = 2;

echo "========================================================\n";
echo " SmashCode — Migrador de base de datos                  \n";
echo "========================================================\n\n";

/**
 * Conecta al motor SIN seleccionar base de datos, porque el esquema base es
 * el que la crea. Reintenta porque en Docker el contenedor web suele estar
 * listo antes que MySQL.
 */
function conectarConReintentos(): PDO {
    $dsn = 'mysql:host=' . DB_HOST . ';charset=' . DB_CHARSET;
    $ultimoError = '';

    for ($intento = 1; $intento <= INTENTOS_CONEXION; $intento++) {
        try {
            return new PDO($dsn, DB_USUARIO, DB_CLAVE, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci',
            ]);
        } catch (PDOException $e) {
            $ultimoError = $e->getMessage();
            echo "      · Esperando a MySQL ($intento/" . INTENTOS_CONEXION . ")...\n";
            sleep(ESPERA_SEGUNDOS);
        }
    }

    fwrite(STDERR, "\n[ERROR] No se pudo conectar a MySQL: $ultimoError\n");
    exit(1);
}

/**
 * Ejecuta un archivo .sql completo.
 *
 * Se usa query() y no exec() porque estas migraciones terminan con un SELECT
 * o un DESCRIBE de verificación: hay que consumir todos los conjuntos de
 * resultados o la conexión queda ocupada y falla el archivo siguiente.
 */
function ejecutarArchivo(PDO $pdo, string $ruta): void {
    $sql = file_get_contents($ruta);
    if ($sql === false || trim($sql) === '') {
        throw new RuntimeException('El archivo está vacío o no se pudo leer');
    }

    $stmt = $pdo->query($sql);
    do {
        // Vaciar cada resultado que devuelvan las sentencias de verificación
        $stmt->fetchAll();
    } while ($stmt->nextRowset());
    $stmt->closeCursor();
}

try {
    $pdo = conectarConReintentos();
    $base = DB_NOMBRE;

    // 1. Esquema base, solo si la base todavía no existe o está vacía.
    //    No se re-aplica nunca: su INSERT del programa de formación no es
    //    idempotente y duplicaría "Técnico en Enfermería" en cada arranque.
    $stmt = $pdo->prepare(
        'SELECT COUNT(*) FROM information_schema.TABLES
         WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?'
    );
    $stmt->execute([$base, 'usuarios']);
    $baseInstalada = ((int) $stmt->fetchColumn()) > 0;

    if (!$baseInstalada) {
        echo "[1/2] Base vacía: instalando el esquema inicial...\n";
        ejecutarArchivo($pdo, __DIR__ . '/smash_code.sql');
        echo "      ✓ smash_code.sql aplicado.\n\n";
    } else {
        echo "[1/2] Esquema ya instalado, no se toca.\n\n";
    }

    // 2. Migraciones vigentes
    echo "[2/2] Aplicando migraciones...\n";
    $aplicadas = 0;

    foreach (MIGRACIONES as $archivo) {
        $ruta = __DIR__ . '/migraciones/' . $archivo;

        if (!file_exists($ruta)) {
            echo "      ⚠ No encontrada, se omite: $archivo\n";
            continue;
        }

        echo "      -> $archivo";
        ejecutarArchivo($pdo, $ruta);
        echo " [OK]\n";
        $aplicadas++;
    }

    echo "\n========================================================\n";
    echo " Listo: $aplicadas migraciones aplicadas sobre '$base'.\n";
    echo "========================================================\n";

} catch (Throwable $e) {
    fwrite(STDERR, "\n[ERROR] " . $e->getMessage() . "\n");
    exit(1);
}
