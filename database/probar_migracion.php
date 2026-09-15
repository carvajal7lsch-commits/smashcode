<?php
/**
 * probar_migracion.php
 * Prueba en seco una migración de datos: la ejecuta dentro de una transacción,
 * muestra qué cambiaría y la revierte. NO modifica la base.
 *
 * Uso (en el VPS: Dokploy → Open Terminal, dentro de /var/www/html):
 *   php database/probar_migracion.php database/migraciones/<archivo>.sql
 *
 * Solo acepta sentencias que se pueden revertir: UPDATE de una columna,
 * INSERT, SET @variable, SELECT, más SET NAMES y USE. Una sentencia de
 * estructura (ALTER, CREATE, DROP...) confirmaría la transacción en MySQL y
 * ya no se podría revertir, así que la prueba se niega a correr.
 *
 * Las sentencias se separan por un ";" al final de la línea: ninguna línea de
 * texto dentro de una sentencia puede terminar en ";".
 */

require_once __DIR__ . '/../config/conexion.php';

$archivo = $argv[1] ?? '';
if ($archivo === '' || !is_file($archivo)) {
    fwrite(STDERR, "Uso: php database/probar_migracion.php database/migraciones/<archivo>.sql\n");
    exit(1);
}

// 1. Separar y validar las sentencias
$sinComentarios = preg_replace('/^\s*--.*$/m', '', file_get_contents($archivo));
$sentencias = [];
$baseDestino = null;
foreach (preg_split('/;\s*$/m', $sinComentarios) as $sql) {
    $sql = trim($sql);
    if ($sql === '') {
        continue;
    }
    if (preg_match('/^USE\s+`?(\w+)`?$/i', $sql, $m)) {
        $baseDestino = $m[1];
        continue;
    }
    if (preg_match('/^SET\s+NAMES\b/i', $sql)) {
        continue;
    }
    if (preg_match('/^UPDATE\s+`?(\w+)`?\s+SET\s+`?(\w+)`?\s*=/i', $sql, $m)) {
        $sentencias[] = ['tipo' => 'update', 'tabla' => $m[1], 'columna' => $m[2], 'sql' => $sql];
    } elseif (preg_match('/^INSERT\s+INTO\s+`?(\w+)`?/i', $sql, $m)) {
        $sentencias[] = ['tipo' => 'insert', 'tabla' => $m[1], 'sql' => $sql];
    } elseif (preg_match('/^SET\s+@\w+\s*:?=/i', $sql) || preg_match('/^SELECT\b/i', $sql)) {
        $sentencias[] = ['tipo' => 'lectura', 'sql' => $sql];
    } else {
        fwrite(STDERR, "ABORTADO: esta sentencia no se puede revertir con seguridad:\n  " . mb_strimwidth($sql, 0, 120, '…', 'UTF-8') . "\n");
        exit(1);
    }
}
$modificaciones = array_filter($sentencias, fn ($s) => $s['tipo'] !== 'lectura');
if (!$modificaciones) {
    fwrite(STDERR, "ABORTADO: el archivo no tiene sentencias UPDATE ni INSERT.\n");
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

function filasDe(PDO $pdo, string $tabla): int {
    return (int) $pdo->query("SELECT COUNT(*) FROM `$tabla`")->fetchColumn();
}

function recortar(?string $texto): string {
    return $texto === null ? 'NULL' : mb_strimwidth($texto, 0, 60, '…', 'UTF-8');
}

$columnas = [];
$tablasInsert = [];
foreach ($modificaciones as $s) {
    if ($s['tipo'] === 'update') {
        $columnas[$s['tabla'] . '.' . $s['columna']] = [$s['tabla'], $s['columna']];
    } else {
        $tablasInsert[$s['tabla']] = true;
    }
}

echo "Prueba en seco de " . basename($archivo) . ': ' . count($modificaciones) . " sentencias que modifican datos\n\n";

$antes = [];
$filasAntes = [];
$pdo->beginTransaction();
try {
    foreach ($columnas as $nombre => [$tabla, $columna]) {
        $antes[$nombre] = valores($pdo, $tabla, $columna);
    }
    foreach (array_keys($tablasInsert) as $tabla) {
        $filasAntes[$tabla] = filasDe($pdo, $tabla);
    }

    $afectadas = 0;
    foreach ($sentencias as $s) {
        $stmt = $pdo->query($s['sql']);
        if ($s['tipo'] === 'lectura') {
            $stmt->fetchAll();
        } else {
            $afectadas += $stmt->rowCount();
        }
        $stmt->closeCursor();
    }

    if ($columnas) {
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
    }

    if ($tablasInsert) {
        printf("%-40s %8s %10s\n", 'tabla', 'antes', 'filas nuevas');
        foreach (array_keys($tablasInsert) as $tabla) {
            printf("%-40s %8d %10d\n", $tabla, $filasAntes[$tabla], filasDe($pdo, $tabla) - $filasAntes[$tabla]);
        }
    }
    echo "\nFilas que cambiaría o insertaría en total: $afectadas\n";
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
foreach (array_keys($tablasInsert) as $tabla) {
    if (filasDe($pdo, $tabla) !== $filasAntes[$tabla]) {
        $intactas = false;
    }
}
echo $intactas
    ? "ROLLBACK: la base quedó exactamente como estaba.\n"
    : "ATENCIÓN: la base no coincide con el estado inicial. Avisa antes de desplegar.\n";
exit($intactas ? 0 : 1);
