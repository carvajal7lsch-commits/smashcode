<?php
// Evitar variables no definidas
$filtroNivel  = $filtroNivel ?? '';
$filtroRap    = $filtroRap ?? '';
$filtroEstado = $filtroEstado ?? '';
$resultados      = $resultados ?? [];
$ejerciciosError = $ejerciciosError ?? [];

// Los filtros activos viajan al CSV para que el archivo traiga lo mismo que la pantalla
$queryFiltros = http_build_query(array_filter([
    'nivel_id' => $filtroNivel,
    'rap_id'   => $filtroRap,
    'estado'   => $filtroEstado
]));
$enlaceCsv = PROYECTO_PATH . '/instructor/exportar' . ($queryFiltros ? '?' . $queryFiltros : '');

$accionFiltros  = PROYECTO_PATH . '/instructor/resultados';
$opcionesEstado = [
    'completado'  => 'Aprobado',
    'en_progreso' => 'No aprobado'
];
?>
<!DOCTYPE html>
<html lang="es" data-theme="dark">
<head>
  <meta charset="UTF-8">
  <link rel="icon" type="image/svg+xml" href="<?= PROYECTO_PATH ?>/assets/img/favicon.svg">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Resultados de Quizzes — Instructor SmashCode</title>
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
      <li><a href="<?= PROYECTO_PATH ?>/instructor/aprendices" class="nav-enlace"><i class="fas fa-users nav-icono"></i><span>Mis Aprendices</span></a></li>
      <li><a href="<?= PROYECTO_PATH ?>/instructor/resultados" class="nav-enlace activo" aria-current="page"><i class="fas fa-clipboard-list nav-icono"></i><span>Resultados Quiz</span></a></li>
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
          <h1 class="dashboard-welcome-title">Resultados de Quizzes</h1>
          <p class="dashboard-welcome-subtitle">Consulta el desempeño del grupo, detecta los ejercicios más difíciles y exporta el reporte.</p>
        </div>
      </div>

      <?php require __DIR__ . '/partials/alcance.php'; ?>

      <!-- Filtros (mismos criterios que el panel de aprendices) -->
      <?php require __DIR__ . '/partials/filtros.php'; ?>

      <!-- Ejercicios con mayor tasa de error del grupo -->
      <div class="tarjeta" style="margin-bottom: 24px;">
        <div class="lista-aprendices-header">
          <span class="lista-aprendices-titulo">
            <i class="fas fa-triangle-exclamation"></i>
            Ejercicios con mayor tasa de error
          </span>
        </div>

        <?php if (empty($ejerciciosError)): ?>
          <p class="mensaje-vacio-tabla">
            Todavía no hay intentos de ejercicios fallidos con estos filtros.
          </p>
        <?php else: ?>
        <div class="tabla-container-scroll">
          <table class="tabla-aprendices tabla-premium" style="width:100%;">
            <thead>
              <tr>
                <th>Ejercicio</th>
                <th>Módulo / RAP</th>
                <th class="text-center">Aprendices</th>
                <th class="text-center">Fallos / Intentos</th>
                <th>Tasa de error</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($ejerciciosError as $e):
                $tasa = (float) $e['tasa_error'];
                // Se reutiliza la escala de color de la barra de avance, pero invertida:
                // aquí un porcentaje alto es una señal de alarma, no de logro.
                $nivelClase = $tasa >= 60 ? 'nivel-bajo' : ($tasa >= 30 ? 'nivel-medio' : 'nivel-alto');
              ?>
              <tr>
                <td>
                  <?= limpiar(mb_strimwidth($e['enunciado'], 0, 90, '…')) ?>
                  <div style="font-size:0.72rem; opacity:0.7; margin-top:2px;"><?= limpiar(str_replace('_', ' ', $e['tipo'])) ?></div>
                </td>
                <td>Módulo <?= (int) $e['modulo_orden'] ?> · <?= limpiar($e['rap_titulo']) ?></td>
                <td class="text-center"><?= (int) $e['aprendices_afectados'] ?></td>
                <td class="text-center"><?= (int) $e['total_fallos'] ?> / <?= (int) $e['total_intentos'] ?></td>
                <td>
                  <div class="progreso-mini">
                    <div class="barra">
                      <div class="relleno <?= $nivelClase ?>" style="width:<?= min(100, $tasa) ?>%"></div>
                    </div>
                    <span class="progreso-mini-porcentaje"><?= number_format($tasa, 0) ?>%</span>
                  </div>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <?php endif; ?>
      </div>

      <!-- Tabla de intentos de quiz -->
      <div class="tarjeta">
        <div class="lista-aprendices-header">
          <span class="lista-aprendices-titulo">
            <i class="fas fa-clipboard-list"></i>
            Intentos de quiz (<?= count($resultados) ?>)
          </span>
          <a href="<?= $enlaceCsv ?>" class="btn btn-primario btn-exportar-csv">
            <i class="fas fa-download"></i> Exportar a CSV
          </a>
        </div>

        <?php if (empty($resultados)): ?>
          <p class="mensaje-vacio-tabla">
            No hay intentos de quiz que coincidan con los filtros.
          </p>
        <?php else: ?>
        <div class="tabla-container-scroll">
          <table class="tabla-aprendices tabla-premium" style="width:100%;">
            <thead>
              <tr>
                <th>Aprendiz</th>
                <th>Módulo / RAP</th>
                <th class="text-center">Intento</th>
                <th>Puntaje</th>
                <th class="text-center">Duración</th>
                <th>Fecha</th>
                <th>Estado</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($resultados as $r):
                $puntaje    = (float) $r['puntaje'];
                $nivelClase = $puntaje >= 70 ? 'nivel-alto' : ($puntaje >= 40 ? 'nivel-medio' : 'nivel-bajo');
                $segundos   = (int) ($r['duracion_seg'] ?? 0);
                $aprobado   = ((int) $r['aprobado'] === 1);
              ?>
              <tr>
                <td>
                  <?= limpiar($r['nombre_completo']) ?>
                  <div style="font-size:0.72rem; opacity:0.7; margin-top:2px;"><?= limpiar($r['correo']) ?></div>
                </td>
                <td>Módulo <?= (int) $r['modulo_orden'] ?> · <?= limpiar($r['rap_titulo']) ?></td>
                <td class="text-center"><?= (int) $r['numero_intento'] ?></td>
                <td>
                  <div class="progreso-mini">
                    <div class="barra">
                      <div class="relleno <?= $nivelClase ?>" style="width:<?= min(100, $puntaje) ?>%"></div>
                    </div>
                    <span class="progreso-mini-porcentaje"><?= number_format($puntaje, 0) ?>%</span>
                  </div>
                </td>
                <td class="text-center"><?= sprintf('%02d:%02d', intdiv($segundos, 60), $segundos % 60) ?></td>
                <td><?= date('d/m/Y H:i', strtotime($r['creado_en'])) ?></td>
                <td>
                  <span class="chip-estado <?= $aprobado ? 'chip-activo' : 'chip-riesgo' ?>">
                    <?= $aprobado ? 'Aprobado' : 'No aprobado' ?>
                  </span>
                </td>
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
