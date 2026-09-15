<?php
/**
 * probar_migracion.php
 * Prueba en seco una migración de datos: la ejecuta dentro de una transacción,
 * muestra qué filas cambiaría y la revierte. NO modifica la base.
 *
 * Uso (en el VPS: Dokploy → Open Terminal, dentro de /var/www/html):
 *   php database/probar_migracion.php database/migraciones/<archivo>.sql
 *
 * Solo acepta migraciones de UPDATE (más SET NAMES, USE y SELECT de
 * verificación): una sentencia de estructura (ALTER, CREATE, DROP...) confirma
 * la transacción en MySQL y ya no se podría revertir.
 */

require_once __DIR__ . '/../config/conexion.php';

$archivo = $argv[1] ?? '';
if ($archivo === '' || !is_file($archivo)) {
    fwrite(STDERR, "Uso: php database/probar_migracion.php database/migraciones/<archivo>.sql\n");
    exit(1);
}

// 1. Validar que todas las sentencias se pueden revertir
$sentencias = [];
$baseDestino = null;
foreach (preg_split('/\R/', file_get_contents($archivo)) as $n => $linea) {
    $linea = trim($linea);
    if ($linea === '' || str_starts_with($linea, '--')) {
        continue;
    }
    if (preg_match('/^USE `?(\w+)`?;$/i', $linea, $m)) {
        $baseDestino = $m[1];
        continue;
    }
    if (preg_match('/^(SET NAMES|SELECT)\b/i', $linea)) {
        continue;
    }
    if (!preg_match('/^UPDATE `?(\w+)`? SET `?(\w+)`? = .*;$/i', $linea, $m)) {
        fwrite(STDERR, "ABORTADO: la línea " . ($n + 1) . " no es un UPDATE de una sola línea; esta prueba no puede revertirla con seguridad.\n");
        exit(1);
    }
    $sentencias[] = ['tabla' => $m[1], 'columna' => $m[2], 'sql' => $linea];
}
if (!$sentencias) {
    fwrite(STDERR, "ABORTADO: el archivo no tiene sentencias UPDATE.\n");
    exit(1);
}

// La prueba corre sobre la base de la conexión: debe ser la misma que la
// migración selecciona con USE, o se estaría probando otra base
$pdo = obtenerConexion();
$baseConexion = $pdo->query('SELECT DATABASE()')->fetchColumn();
if ($baseDestino === null || $baseConexion !== $baseDestino) {
    fwrite(STDERR, "ABORTADO: la migración usa '" . ($baseDestino ?? '(sin USE)') . "' y la conexión apunta a '$baseConexion'.\n");
    exit(1);
}

/** Valores de una columna por clave primaria, comparables byte a byte. */
function valores(PDO $pdo, string $tabla, string $columna): array {
    $clave = $pdo->query(
        "SELECT COLUMN_NAME FROM information_schema.KEY_COLUMN_USAGE
         WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = " . $pdo->quote($tabla) . " AND CONSTRAINT_NAME = 'PRIMARY'
         ORDER BY ORDINAL_POSITION LIMIT 1"
    )->fetchColumn();
    return $pdo->query("SELECT `$clave`, CAST(`$columna` AS BINARY) FROM `$tabla`")->fetchAll(PDO::FETCH_KEY_PAIR);
}

function recortar(?string $texto): string {
    return $texto === null ? 'NULL' : mb_strimwidth($texto, 0, 60, '…', 'UTF-8');
}

$columnas = [];
foreach ($sentencias as $s) {
    $columnas[$s['tabla'] . '.' . $s['columna']] = [$s['tabla'], $s['columna']];
}

echo "Prueba en seco de " . basename($archivo) . ': ' . count($sentencias) . " sentencias\n\n";

$antes = [];
$pdo->beginTransaction();
try {
    foreach ($columnas as $nombre => [$tabla, $columna]) {
        $antes[$nombre] = valores($pdo, $tabla, $columna);
    }

    $afectadas = 0;
    foreach ($sentencias as $s) {
        $afectadas += $pdo->exec($s['sql']);
    }

    // "solo ASCII": filas sin ningún carácter con tilde, signo, emoji o IPA;
    // en columnas en español o de IPA suelen ser las que siguen dañadas
    printf("%-40s %8s %10s\n", 'columna', 'cambian', 'solo ASCII');
    foreach ($columnas as $nombre => [$tabla, $columna]) {
        $despues = valores($pdo, $tabla, $columna);
        $cambios = array_keys(array_filter($despues, fn ($v, $k) => $v !== $antes[$nombre][$k], ARRAY_FILTER_USE_BOTH));
        $soloAscii = count(array_filter($despues, fn ($v) => $v !== null && $v !== '' && !preg_match('/[^\x00-\x7F]/', $v)));
        printf("%-40s %8d %10d\n", $nombre, count($cambios), $soloAscii);
        foreach (array_slice($cambios, 0, 2) as $k) {
            echo '     antes:   ' . recortar($antes[$nombre][$k]) . "\n";
            echo '     después: ' . recortar($despues[$k]) . "\n";
        }
    }
    echo "\nFilas que cambiaría en total: $afectadas\n";
} finally {
    $pdo->rollBack();
}

// Comprobar que la reversión dejó todo como estaba
$intactas = true;
foreach ($columnas as $nombre => [$tabla, $columna]) {
    if (valores($pdo, $tabla, $columna) !== $antes[$nombre]) {
        $intactas = false;
    }
}
echo $intactas
    ? "ROLLBACK: la base quedó exactamente como estaba.\n"
    : "ATENCIÓN: la base no coincide con el estado inicial. Avisa antes de desplegar.\n";
exit($intactas ? 0 : 1);
