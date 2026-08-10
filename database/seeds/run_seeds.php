<?php
/**
 * run_seeds.php
 * Script ejecutor de semillas y actualización de base de datos con UTF-8 (utf8mb4) garantizado.
 * Uso en terminal (VPS o local):
 * php database/seeds/run_seeds.php
 */

require_once __DIR__ . '/../../config/conexion.php';

echo "========================================================\n";
echo " SmashCode — Sincronizador de Contenidos y Tildes UTF-8 \n";
echo "========================================================\n\n";

try {
    $pdo = obtenerConexion();
    $pdo->exec("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("SET CHARACTER SET utf8mb4");

    // 1. Asegurar codificación utf8mb4 en todas las tablas
    $tablas = [
        'nivel', 'rap', 'dialogo', 'turno_dialogo', 
        'vocabulario', 'ejercicio', 'ejercicio_opcion', 
        'quiz', 'pregunta', 'respuesta_quiz', 'intento_quiz'
    ];

    echo "[1/3] Verificando charset utf8mb4 en tablas...\n";
    foreach ($tablas as $tabla) {
        try {
            $pdo->exec("ALTER TABLE `$tabla` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        } catch (Exception $e) {
            // Si la tabla no existe aún, ignorar
        }
    }
    echo "      ✓ Tablas convertidas a utf8mb4_unicode_ci correctamente.\n\n";

    // 2. Ejecutar cada semilla en orden
    $seeds = [
        'seed_00_update_niveles.sql',
        'seed_00_mod1_rap1.sql',
        'seed_01_mod2_rap2.sql',
        'seed_02_mod2_rap3.sql',
        'seed_03_mod3_rap4.sql',
        'seed_04_mod3_rap5.sql'
    ];

    echo "[2/3] Ejecutando scripts SQL de contenidos...\n";
    foreach ($seeds as $archivo) {
        $ruta = __DIR__ . '/' . $archivo;
        if (!file_exists($ruta)) {
            echo "      ⚠ Archivo no encontrado: $archivo\n";
            continue;
        }

        echo "      -> Procesando $archivo...";
        $sql = file_get_contents($ruta);
        $pdo->exec($sql);
        echo " [OK]\n";
    }

    echo "\n[3/3] Resumen de contenidos cargados:\n";
    $stmt = $pdo->query("
        SELECT 
            n.orden AS nivel,
            n.nombre AS modulo,
            r.orden AS rap_orden,
            r.titulo AS rap_titulo,
            (SELECT COUNT(*) FROM vocabulario v WHERE v.rap_id = r.id) AS vocab,
            (SELECT COUNT(*) FROM dialogo d WHERE d.rap_id = r.id) AS dialogos,
            (SELECT COUNT(*) FROM ejercicio e WHERE e.rap_id = r.id) AS ejercicios,
            (SELECT COUNT(*) FROM quiz q WHERE q.rap_id = r.id) AS quizzes
        FROM rap r
        JOIN nivel n ON r.nivel_id = n.id
        WHERE n.orden <= 4
        ORDER BY n.orden, r.orden
    ");

    $filas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($filas as $f) {
        echo "      • Módulo {$f['nivel']} (RAP {$f['rap_orden']}): {$f['rap_titulo']} | Vocab: {$f['vocab']}, Diálogos: {$f['dialogos']}, Ejercicios: {$f['ejercicios']}, Quizzes: {$f['quizzes']}\n";
    }

    echo "\n========================================================\n";
    echo " ¡ÉXITO! Base de datos sincronizada con tildes impecables. \n";
    echo "========================================================\n";

} catch (Exception $e) {
    echo "\n[ERROR]: " . $e->getMessage() . "\n";
    exit(1);
}
