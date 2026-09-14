<?php
// Evitar variables no definidas
$filtroNivel  = $filtroNivel ?? '';
$filtroRap    = $filtroRap ?? '';
$filtroEstado = $filtroEstado ?? '';
$avanceModulos = $avanceModulos ?? [];
$avanceRaps    = $avanceRaps ?? [];

// El CSV exporta intentos de quiz, así que solo hereda el alcance (nivel y RAP).
// El estado de esta pantalla habla del avance del aprendiz, no de aprobar un quiz:
// pasarlo tal cual dejaba el archivo vacío al filtrar por "sin iniciar".
$queryFiltros = http_build_query(array_filter([
    'nivel_id' => $filtroNivel,
    'rap_id'   => $filtroRap
]));
$enlaceCsv = PROYECTO_PATH . '/instructor/exportar' . ($queryFiltros ? '?' . $queryFiltros : '');

// Columnas de avance (HU23: "% de avance por nivel y por RAP").
// Sin filtro de alcance hay una columna por módulo, con el detalle de sus RAPs en
// el tooltip. Al filtrar por módulo o RAP hay una columna por cada RAP del alcance,
// porque en ese momento el instructor ya está mirando RAP a RAP.
$modulos     = [];
$columnasRap = [];
foreach ($nivelesConRaps ?? [] as $n) {
    $modulos[(int) $n['orden']] = $n['nombre'];

    $enAlcance = $filtroRap !== ''
        ? $n['rap_id'] === $filtroRap
        : ($filtroNivel !== '' && $n['id'] === $filtroNivel);

    if ($enAlcance) {
        $columnasRap[$n['rap_id']] = [
            'titulo' => $n['rap_titulo'],
            'corto'  => preg_match('/^RAP\s*\d+/i', $n['rap_titulo'], $m) ? $m[0] : 'RAP'
        ];
    }
}
ksort($modulos);
$verPorRap = ($filtroNivel !== '' || $filtroRap !== '');
$hayFiltros = ($filtroNivel !== '' || $filtroRap !== '' || $filtroEstado !== '');

$accionFiltros  = PROYECTO_PATH . '/instructor/aprendices';
$opcionesEstado = [
    'completado'  => 'Completado',
    'en_progreso' => 'En Progreso',
    'sin_iniciar' => 'Sin Iniciar'
];

// Mismo semáforo para barras y celdas de avance
$colorAvance = static fn(float $pct): string =>
    $pct >= 70 ? 'var(--verde)' : ($pct >= 40 ? 'var(--naranja)' : 'var(--gris-medio)');
?>
<!DOCTYPE html>
<html lang="es" data-theme="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Mis Aprendices — Instructor SmashCode</title>
  <link rel="stylesheet" href="<?= PROYECTO_PATH ?>/assets/css/estilos.css?v=<?= time() ?>">
  <link rel="stylesheet" href="<?= PROYECTO_PATH ?>/assets/css/dashboard.css?v=<?= time() ?>">
  <link rel="stylesheet" href="<?= PROYECTO_PATH ?>/assets/css/cruds.css?v=<?= time() ?>">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <script>(function(){var t=localStorage.getItem('smashcode_tema');if(t)document.documentElement.setAttribute('data-theme',t);})();</script>
</head>
<body>
<div class="contenedor-app">

  <!-- Barra lateral instructor -->
  <nav class="barra-lateral" aria-label="Navegación instructor">
    <div class="logo-app">
      <div class="logo-icono">
        <svg viewBox="0 0 100 100" width="40" height="40" xmlns="http://www.w3.org/2000/svg" class="svg-block">
          <ellipse cx="50" cy="85" rx="22" ry="5" fill="#000" opacity="0.3" />
          <ellipse cx="38" cy="82" rx="7" ry="4" fill="#FF9600" />
          <ellipse cx="62" cy="82" rx="7" ry="4" fill="#FF9600" />
          <rect x="26" y="20" width="48" height="58" rx="24" fill="#2B3E46" />
          <path d="M 26 38 C 17 42 17 56 26 62 Z" fill="#2B3E46" />
          <path d="M 74 38 C 83 42 83 56 74 62 Z" fill="#2B3E46" />
          <ellipse cx="50" cy="54" rx="17" ry="20" fill="#FFFFFF" />
          <ellipse cx="41" cy="38" rx="9" ry="9" fill="#FFFFFF" />
          <ellipse cx="59" cy="38" rx="9" ry="9" fill="#FFFFFF" />
          <circle cx="42" cy="38" r="5" fill="#111B1E" />
          <circle cx="40.5" cy="36.5" r="1.8" fill="#FFFFFF" />
          <circle cx="58" cy="38" r="5" fill="#111B1E" />
          <circle cx="56.5" cy="36.5" r="1.8" fill="#FFFFFF" />
          <path d="M 44 43 Q 50 51 56 43 Z" fill="#FF9600" stroke="#FF9600" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
      </div>
      <div>
        <div class="logo-nombre">Smash<span>Code</span></div>
        <div class="logo-rol-texto">INSTRUCTOR</div>
      </div>
    </div>
    <ul class="nav-lateral">
      <li><a href="<?= PROYECTO_PATH ?>/instructor" class="nav-enlace"><i class="fas fa-gauge-high nav-icono"></i><span>Dashboard</span></a></li>
      <li><a href="<?= PROYECTO_PATH ?>/instructor/aprendices" class="nav-enlace activo" aria-current="page"><i class="fas fa-users nav-icono"></i><span>Mis Aprendices</span></a></li>
      <li><a href="<?= PROYECTO_PATH ?>/instructor/resultados" class="nav-enlace"><i class="fas fa-clipboard-list nav-icono"></i><span>Resultados Quiz</span></a></li>
      <li><a href="<?= PROYECTO_PATH ?>/instructor/niveles" class="nav-enlace"><i class="fas fa-layer-group nav-icono"></i><span>Niveles</span></a></li>
      <li><a href="<?= PROYECTO_PATH ?>/instructor/raps" class="nav-enlace"><i class="fas fa-file-lines nav-icono"></i><span>RAPs</span></a></li>
      <li><a href="<?= PROYECTO_PATH ?>/instructor/exportar" class="nav-enlace"><i class="fas fa-file-csv nav-icono"></i><span>Exportar CSV</span></a></li>
      <li><a href="<?= PROYECTO_PATH ?>/logout" class="nav-enlace nav-enlace-salir"><i class="fas fa-right-from-bracket nav-icono"></i><span>Cerrar Sesión</span></a></li>
    </ul>
  </nav>

  <!-- Contenido principal -->
  <main class="contenido-principal">
    <header class="barra-superior">
      <!-- Botón cambio de tema -->
      <button id="btn-cambiar-tema" class="btn-tema" aria-label="Cambiar a modo claro" title="Cambiar a modo claro">
        <i class="fas fa-sun tema-icono"></i>
        <span class="tema-label">Claro</span>
      </button>
      <div class="avatar-usuario" title="<?= limpiar($_SESSION['nombre'] ?? 'Instructor') ?>">
        <?= strtoupper(substr($_SESSION['nombre'] ?? 'I', 0, 1)) ?>
      </div>
    </header>

    <div class="dashboard-page-content">
      <div class="dashboard-welcome-header">
        <div>
          <h1 class="dashboard-welcome-title">Mis Aprendices</h1>
          <p class="dashboard-welcome-subtitle">Consulta el avance de cada aprendiz por módulo y por RAP, y filtra por estado.</p>
        </div>
      </div>

      <?php require __DIR__ . '/partials/alcance.php'; ?>

      <?php require __DIR__ . '/partials/filtros.php'; ?>

      <!-- Tabla de aprendices filtrados -->
      <div class="tarjeta">
        <div class="lista-aprendices-header">
          <span class="lista-aprendices-titulo">
            <i class="fas fa-list-ul"></i>
            Resultados (<?= count($aprendices) ?>)
          </span>
          <a href="<?= $enlaceCsv ?>" class="btn btn-primario btn-exportar-csv" title="Descarga los intentos de quiz del módulo o RAP filtrado">
            <i class="fas fa-download"></i> Exportar resultados de quiz (CSV)
          </a>
        </div>

        <?php if (empty($aprendices)): ?>
          <p class="mensaje-vacio-tabla">
            <?= $hayFiltros ? 'No hay aprendices que coincidan con los filtros.' : 'Todavía no hay aprendices para mostrar.' ?>
          </p>
        <?php else: ?>
        <div class="tabla-container-scroll">
          <table class="tabla-aprendices tabla-premium" id="tabla-aprendices" style="width:100%;">
            <thead>
              <tr>
                <th>Nombre</th>
                <th>Correo</th>
                <?php if ($verPorRap): ?>
                  <?php foreach ($columnasRap as $columna): ?>
                    <th class="text-center" title="<?= limpiar($columna['titulo']) ?>"><?= limpiar($columna['corto']) ?></th>
                  <?php endforeach; ?>
                <?php else: ?>
                  <?php foreach ($modulos as $ordenMod => $nombreMod): ?>
                    <th class="text-center" title="<?= limpiar($nombreMod) ?>">M<?= $ordenMod ?></th>
                  <?php endforeach; ?>
                <?php endif; ?>
                <th class="text-center">RAPs Iniciados</th>
                <th class="text-center">RAPs Completados</th>
                <th title="Promedio sobre todos los RAPs del alcance filtrado; los que no ha empezado cuentan como 0%">Avance Promedio</th>
                <th>XP</th>
                <th>Estado</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($aprendices as $a):
                $avance      = (float) $a['avance_promedio'];
                $nivelClase  = $avance >= 70 ? 'nivel-alto' : ($avance >= 40 ? 'nivel-medio' : 'nivel-bajo');
                $totalRaps   = (int) $a['total_raps'];
                $iniciados   = (int) $a['raps_iniciados'];
                $completados = (int) $a['raps_completados'];

                // El chip usa la misma regla que el filtro de estado del modelo
                if ($totalRaps > 0 && $completados === $totalRaps) {
                    $chipTexto = 'Completado';
                    $chipClase = 'chip-activo';
                } elseif ($iniciados === 0) {
                    $chipTexto = 'Sin iniciar';
                    $chipClase = 'chip-riesgo';
                } else {
                    $chipTexto = 'En progreso';
                    $chipClase = 'chip-activo';
                }
              ?>
              <tr>
                <td><?= limpiar($a['nombre_completo']) ?></td>
                <td><?= limpiar($a['correo']) ?></td>
                <?php if ($verPorRap): ?>
                  <?php foreach ($columnasRap as $idRap => $columna):
                    $celdaRap = $avanceRaps[$a['id']][$idRap] ?? null;
                    $pctRap   = $celdaRap ? $celdaRap['porcentaje'] : 0.0;
                  ?>
                    <td class="text-center" title="<?= limpiar($columna['titulo']) ?>">
                      <span style="font-weight:800; color:<?= $colorAvance($pctRap) ?>;"><?= number_format($pctRap, 0) ?>%</span>
                      <?php if ($celdaRap && $celdaRap['completado']): ?>
                        <i class="fas fa-circle-check" style="color:var(--verde); margin-left:4px;" title="RAP completado"></i>
                      <?php endif; ?>
                    </td>
                  <?php endforeach; ?>
                <?php else: ?>
                  <?php foreach ($modulos as $ordenMod => $nombreMod):
                    $celda  = $avanceModulos[$a['id']][$ordenMod] ?? null;
                    $pctMod = $celda ? (float) $celda['avance'] : 0.0;
                    // El detalle RAP por RAP va en el tooltip para no multiplicar columnas
                    $tituloCelda = $celda
                        ? $nombreMod . ' — ' . str_replace(' | ', ' · ', $celda['detalle'])
                        : $nombreMod;
                  ?>
                    <td class="text-center" title="<?= limpiar($tituloCelda) ?>">
                      <span style="font-weight:800; color:<?= $colorAvance($pctMod) ?>;"><?= number_format($pctMod, 0) ?>%</span>
                    </td>
                  <?php endforeach; ?>
                <?php endif; ?>
                <td class="text-center"><?= $iniciados ?></td>
                <td class="text-center"><?= $completados ?> / <?= $totalRaps ?></td>
                <td>
                  <div class="progreso-mini">
                    <div class="barra">
                      <div class="relleno <?= $nivelClase ?>" style="width:<?= min(100, $avance) ?>%"></div>
                    </div>
                    <span class="progreso-mini-porcentaje"><?= number_format($avance, 0) ?>%</span>
                  </div>
                </td>
                <td><?= formatearXP((int) $a['xp_puntos']) ?></td>
                <td><span class="chip-estado <?= $chipClase ?>"><?= $chipTexto ?></span></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </main>
</div>
<script src="<?= PROYECTO_PATH ?>/assets/js/tema.js"></script>
</body>
</html>
