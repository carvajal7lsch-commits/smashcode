<?php
/**
 * diagnostico_raps.php
 * Diagnóstico de SOLO LECTURA de la tabla `rap`: no modifica nada.
 *
 * Existe porque en el VPS aparecieron RAPs duplicados, varias filas ocupando
 * el mismo módulo y la misma posición. El panel de administración las muestra
 * con el mismo título canónico, así que parecían copias exactas.
 *
 * Uso en terminal (XAMPP o contenedor web desde Dokploy):
 *   php database/diagnostico_raps.php
 *
 * La salida sirve para decidir con datos reales qué fila conservar antes de
 * escribir una migración de limpieza: cuánto contenido tiene cada una y cuánta
 * actividad de aprendices depende de ella (y se perdería al borrarla).
 */

require_once __DIR__ . '/../config/conexion.php';

$pdo = obtenerConexion();

$filas = $pdo->query(
    "SELECT r.id, n.orden AS modulo, r.orden AS posicion, r.activo, r.titulo,
            (SELECT COUNT(*) FROM vocabulario v WHERE v.rap_id = r.id) AS vocabulario,
            (SELECT COUNT(*) FROM ejercicio e WHERE e.rap_id = r.id) AS ejercicios,
            (SELECT COUNT(*) FROM dialogo d WHERE d.rap_id = r.id) AS dialogos,
            (SELECT COUNT(*) FROM pregunta p JOIN quiz q ON q.id = p.quiz_id WHERE q.rap_id = r.id) AS preguntas,
            (SELECT COUNT(*) FROM progreso pr WHERE pr.rap_id = r.id) AS progresos,
            (SELECT COUNT(*) FROM intento_quiz iq JOIN quiz q ON q.id = iq.quiz_id WHERE q.rap_id = r.id) AS intentos_quiz,
            (SELECT COUNT(*) FROM intento_ejercicio ie JOIN ejercicio e ON e.id = ie.ejercicio_id WHERE e.rap_id = r.id) AS intentos_ejercicio
     FROM rap r
     JOIN nivel n ON n.id = r.nivel_id
     ORDER BY n.orden, r.orden, r.activo DESC, r.titulo"
)->fetchAll();

$porPosicion = [];
foreach ($filas as $fila) {
    $porPosicion[$fila['modulo'] . '_' . $fila['posicion']][] = $fila;
}

echo "========================================================\n";
echo " SmashCode — Diagnóstico de RAPs (solo lectura)         \n";
echo "========================================================\n\n";

$posicionesDuplicadas = 0;

foreach ($porPosicion as $clave => $grupo) {
    [$modulo, $posicion] = explode('_', $clave);
    $duplicada = count($grupo) > 1;

    if ($duplicada) {
        $posicionesDuplicadas++;
    }

    echo "Módulo {$modulo} · posición {$posicion}"
       . ($duplicada ? '   <-- DUPLICADO (' . count($grupo) . ' filas)' : '')
       . "\n";

    foreach ($grupo as $f) {
        echo "   " . $f['id'] . '  ' . ($f['activo'] ? 'ACTIVO  ' : 'inactivo') . '  ' . $f['titulo'] . "\n";
        printf(
            "        contenido : vocabulario=%d  ejercicios=%d  dialogos=%d  preguntas=%d\n",
            $f['vocabulario'], $f['ejercicios'], $f['dialogos'], $f['preguntas']
        );
        printf(
            "        actividad : progresos=%d  intentos_quiz=%d  intentos_ejercicio=%d\n",
            $f['progresos'], $f['intentos_quiz'], $f['intentos_ejercicio']
        );
    }

    echo "\n";
}

$nivelesSobrantes = $pdo->query('SELECT orden, nombre FROM nivel WHERE orden > 4 ORDER BY orden')->fetchAll();

echo "--------------------------------------------------------\n";
echo " Filas en rap:            " . count($filas) . " (se esperan 6)\n";
echo " Posiciones duplicadas:   {$posicionesDuplicadas}\n";
echo " Niveles con orden > 4:   " . count($nivelesSobrantes) . "\n";
foreach ($nivelesSobrantes as $n) {
    echo "     orden {$n['orden']}: {$n['nombre']}\n";
}
echo "--------------------------------------------------------\n";
echo " No se modificó ningún dato.\n";
