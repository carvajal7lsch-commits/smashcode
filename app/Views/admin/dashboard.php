<!DOCTYPE html>
<html lang="es" data-theme="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard — Admin SmashCode</title>
  <link rel="stylesheet" href="<?= PROYECTO_PATH ?>/assets/css/estilos.css?v=<?= time() ?>">
  <link rel="stylesheet" href="<?= PROYECTO_PATH ?>/assets/css/dashboard.css?v=<?= time() ?>">
  <link rel="stylesheet" href="<?= PROYECTO_PATH ?>/assets/css/cruds.css?v=<?= time() ?>">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <script>/* Aplicar tema guardado antes del paint */
  (function(){var t=localStorage.getItem('smashcode_tema');if(t)document.documentElement.setAttribute('data-theme',t);})();
  </script>
</head>
<body class="bg-mesh">
<div class="contenedor-app">

  <!-- Barra lateral admin -->
  <?php include __DIR__ . '/partials/sidebar.php'; ?>

  <!-- Contenido principal -->
  <main class="contenido-principal">
    <header class="barra-superior barra-superior-admin">
      <div class="breadcrumb-admin">
        <i class="fas fa-home breadcrumb-icon"></i>
        <span class="breadcrumb-link">Dashboard</span>
      </div>
      <div class="admin-header-actions">
        <div class="stat-xp-indicator">
          <i class="fas fa-bolt"></i> <?= formatearXP((int)$totalXP) ?> XP Total
        </div>
        <!-- Botón cambio de tema -->
        <button id="btn-cambiar-tema" class="btn-tema" aria-label="Cambiar a modo claro" title="Cambiar a modo claro">
          <i class="fas fa-sun tema-icono"></i>
          <span class="tema-label">Claro</span>
        </button>
        <div class="avatar-usuario" title="<?= limpiar($_SESSION['nombre']) ?>">
          <?= strtoupper(substr($_SESSION['nombre'], 0, 1)) ?>
        </div>
      </div>
    </header>

    <div class="dashboard-page-content">
      <!-- Encabezado -->
      <div class="dashboard-welcome-header">
        <div>
          <h1 class="pagina-titulo dashboard-welcome-title">Dashboard</h1>
          <p class="pagina-subtitulo dashboard-welcome-subtitle">
            ¡Bienvenido, <strong><?= limpiar(explode(' ', $_SESSION['nombre'])[0]) ?></strong>! 👋
            &nbsp;— Resumen de control de Smash Code
          </p>
        </div>
      </div>

      <!-- KPIs Premium Grid -->
      <div class="grid-estadisticas dashboard-grid-stats">
        
        <!-- CARD 1 -->
        <div class="tarjeta-premium card-azul">
          <div class="tarjeta-premium-header">
            <span class="stat-etiqueta-azul">Total Usuarios</span>
            <div class="stat-icono stat-icono-azul"><i class="fas fa-users"></i></div>
          </div>
          <div class="stat-valor-premium"><?= $totalUsuarios ?></div>
          <div class="stat-cambio-azul">
            <i class="fas fa-arrow-trend-up"></i> +12 este mes
          </div>
        </div>

        <!-- CARD 2 -->
        <div class="tarjeta-premium card-verde">
          <div class="tarjeta-premium-header">
            <span class="stat-etiqueta-verde">Aprendices Activos</span>
            <div class="stat-icono stat-icono-verde"><i class="fas fa-running"></i></div>
          </div>
          <div class="stat-valor-premium"><?= $aprendicesActivos ?></div>
          <div class="stat-cambio-verde">
            <i class="fas fa-arrow-trend-up"></i> +8 esta semana
          </div>
        </div>

        <!-- CARD 3 -->
        <div class="tarjeta-premium card-naranja">
          <div class="tarjeta-premium-header">
            <span class="stat-etiqueta-naranja">XP Generado</span>
            <div class="stat-icono stat-icono-naranja"><i class="fas fa-bolt"></i></div>
          </div>
          <div class="stat-valor-premium"><?= $totalXP >= 1000 ? round($totalXP/1000,1).'K' : $totalXP ?></div>
          <div class="stat-cambio-naranja">
            <i class="fas fa-fire" style="color:#EF4444;"></i> Racha global activa
          </div>
        </div>

        <!-- CARD 4 -->
        <div class="tarjeta-premium card-lila">
          <div class="tarjeta-premium-header">
            <span class="stat-etiqueta-lila">Quizzes Listos</span>
            <div class="stat-icono stat-icono-lila"><i class="fas fa-file-invoice"></i></div>
          </div>
          <div class="stat-valor-premium"><?= $quizzesCompletos ?></div>
          <div class="stat-cambio-lila">
            <i class="fas fa-check-double"></i> 100% de integridad
          </div>
        </div>

      </div>

      <!-- Gráfico + Actividad reciente -->
      <div class="dashboard-charts-grid">

        <!-- Gráfico de barras premium -->
        <div class="tarjeta-premium tarjeta-chart">
          <div class="chart-header">
            <span class="chart-title">
              <i class="fas fa-chart-line"></i>
              Rendimiento Semanal (Quizzes Completados)
            </span>
            <span class="chart-badge">Últimos 7 días</span>
          </div>
          
          <div class="barra-chart-premium" id="grafico-quizzes">
            <!-- Barras generadas por JS dinámicamente -->
          </div>
          
          <div class="etiquetas-dias etiquetas-dias-container">
            <span>Lun</span>
            <span>Mar</span>
            <span>Mié</span>
            <span>Jue</span>
            <span>Vie</span>
            <span>Sáb</span>
            <span>Dom</span>
          </div>
        </div>

        <!-- Actividad reciente Premium -->
        <div class="tarjeta-premium actividad-reciente-container">
          <div class="chart-header">
            <span class="chart-title">
              <i class="fas fa-history chart-title-icon-naranja"></i>
              Actividad Académica Reciente
            </span>
          </div>
          
          <div class="actividad-reciente-lista">
            <?php if (empty($actividad)): ?>
              <div class="actividad-vacia">
                <i class="fas fa-face-meh"></i>
                Aún no hay actividad registrada hoy.
              </div>
            <?php else: ?>
              <?php foreach ($actividad as $a): ?>
              <div class="item-actividad-card">
                <div class="actividad-item-flex">
                  <span class="punto-actividad-glow <?= $a['aprobado'] ? 'punto-verde-glow' : 'punto-oro-glow' ?>"></span>
                  <div>
                    <strong class="actividad-item-nombre"><?= limpiar($a['nombre_completo']) ?></strong>
                    <span class="actividad-item-rap">Completó <?= limpiar(substr($a['rap_titulo'],0,28)) ?>...</span>
                  </div>
                </div>
                <div class="actividad-item-derecha">
                  <span class="<?= $a['aprobado'] ? 'actividad-puntaje-verde' : 'actividad-puntaje-naranja' ?>"><?= number_format($a['puntaje'], 0) ?>%</span>
                  <span class="actividad-hora">
                    <?= date('H:i a', strtotime($a['creado_en'])) ?>
                  </span>
                </div>
              </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </div>

      </div><!-- /grid -->
    </div><!-- /pagina-contenido -->
  </main>
</div>

<script>
  /* Generación dinámica de barras premium con Tooltip nativo */
  const datos = [12, 8, 15, 22, 18, 5, 11];
  const max   = Math.max(...datos);
  const cont  = document.getElementById('grafico-quizzes');
  datos.forEach((v, i) => {
    const col = document.createElement('div');
    col.className = 'columna' + (i === 3 ? ' destacada' : '');
    col.style.height = (v / max * 100) + '%';
    col.setAttribute('title', `Quizzes: ${v}`);
    cont.appendChild(col);
  });
</script>
<script src="<?= PROYECTO_PATH ?>/assets/js/tema.js"></script>
</body>
</html>
