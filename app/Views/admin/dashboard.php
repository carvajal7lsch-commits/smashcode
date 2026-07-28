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
      <div class="dashboard-charts-grid" style="display:grid; grid-template-columns: 1fr 1fr; gap:24px; margin-top:24px;">

        <!-- Gráfico de barras premium -->
        <div class="tarjeta-premium tarjeta-chart" style="background:var(--bg-card); border:1px solid var(--border-color); border-radius:16px; padding:24px;">
          <div class="chart-header" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;">
            <span class="chart-title" style="font-size:1rem; font-weight:800; color:var(--texto-principal); display:flex; align-items:center; gap:8px;">
              <i class="fas fa-chart-column" style="color:var(--azul);"></i>
              Rendimiento Semanal (Quizzes Completados)
            </span>
            <span class="chart-badge" style="font-size:0.75rem; background:rgba(43,108,176,0.1); color:var(--azul); font-weight:700; padding:4px 10px; border-radius:6px;">
              Últimos 7 días
            </span>
          </div>
          
          <?php
          // Calcular valor máximo para el gráfico
          $maxVal = 1; // Mínimo 1 para evitar división por cero
          foreach ($datosSemana as $d) {
              if ($d['val'] > $maxVal) {
                  $maxVal = $d['val'];
              }
          }
          ?>

          <div class="barra-chart-container" style="display:flex; align-items:flex-end; justify-content:space-between; gap:16px; height:180px; padding:30px 16px 10px 16px; border-bottom:1px solid var(--border-color); box-sizing:border-box;">
            <?php foreach ($datosSemana as $idx => $d): 
              $pct = round(($d['val'] / $maxVal) * 100);
              $esTop = ($idx === 3); // Jueves destacado
            ?>
              <div class="columna-bar-wrapper" style="flex:1; height:100%; display:flex; flex-direction:column; justify-content:flex-end; align-items:center; position:relative;">
                <!-- Valor flotante arriba de la barra -->
                <span style="font-size:0.75rem; font-weight:800; color:<?= $esTop ? 'var(--verde)' : 'var(--texto-principal)' ?>; margin-bottom:6px;">
                  <?= $d['val'] ?>
                </span>
                <!-- Barra vertical -->
                <div class="barra-vertical" 
                     style="width:100%; height:<?= $pct ?>%; background: <?= $esTop ? 'linear-gradient(180deg, #10B981 0%, #059669 100%)' : 'linear-gradient(180deg, #3B82F6 0%, #1D4ED8 100%)' ?>; border-radius:8px 8px 0 0; cursor:pointer; transition:all 0.2s ease; min-height:12px;"
                     title="<?= $d['dia'] ?>: <?= $d['val'] ?> quizzes completados"
                     onmouseover="this.style.filter='brightness(1.25)'; this.style.transform='scaleY(1.04)';"
                     onmouseout="this.style.filter='brightness(1)'; this.style.transform='scaleY(1)';"
                ></div>
              </div>
            <?php endforeach; ?>
          </div>
          
          <div class="etiquetas-dias-flex" style="display:flex; justify-content:space-between; margin-top:12px; padding:0 16px;">
            <?php foreach ($datosSemana as $d): ?>
              <span style="flex:1; text-align:center; font-size:0.78rem; font-weight:700; color:var(--texto-secundario);">
                <?= $d['dia'] ?>
              </span>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- Actividad reciente Premium -->
        <div class="tarjeta-premium actividad-reciente-container" style="background:var(--bg-card); border:1px solid var(--border-color); border-radius:16px; padding:24px; display:flex; flex-direction:column;">
          <div class="chart-header" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:18px;">
            <span class="chart-title" style="font-size:1rem; font-weight:800; color:var(--texto-principal); display:flex; align-items:center; gap:8px;">
              <i class="fas fa-history" style="color:var(--naranja);"></i>
              Actividad Académica Reciente
            </span>
            <span class="chart-badge" style="font-size:0.75rem; background:rgba(245,158,11,0.1); color:var(--naranja); font-weight:700; padding:4px 10px; border-radius:6px;">
              En tiempo real
            </span>
          </div>
          
          <div class="actividad-reciente-lista" style="flex:1; display:flex; flex-direction:column; gap:12px; justify-content:flex-start;">
            <?php if (!empty($actividad)): ?>
              <?php foreach ($actividad as $a): ?>
              <div class="item-actividad-card" style="display:flex; align-items:center; justify-content:space-between; background:var(--bg-app); border:1px solid var(--border-color); padding:14px 16px; border-radius:12px;">
                <div style="display:flex; align-items:center; gap:12px;">
                  <div class="avatar-mini" style="width:36px; height:36px; border-radius:50%; background:linear-gradient(135deg, var(--azul), var(--verde)); color:#fff; display:flex; align-items:center; justify-content:center; font-weight:800; font-size:0.85rem; flex-shrink:0;">
                    <?= strtoupper(substr($a['nombre_completo'], 0, 1)) ?>
                  </div>
                  <div>
                    <div style="font-weight:700; font-size:0.88rem; color:var(--texto-principal);">
                      <?= limpiar($a['nombre_completo']) ?>
                    </div>
                    <div style="font-size:0.78rem; color:var(--texto-secundario);">
                      Completó <?= limpiar(substr($a['rap_titulo'], 0, 32)) ?>...
                    </div>
                  </div>
                </div>

                <div style="text-align:right; flex-shrink:0;">
                  <span class="badge-completitud <?= $a['aprobado'] ? 'si' : 'no' ?>" style="font-size:0.8rem; font-weight:800; padding:4px 10px;">
                    <?= number_format($a['puntaje'], 0) ?>%
                  </span>
                  <div style="font-size:0.7rem; color:var(--texto-tenue); margin-top:4px;">
                    <?= date('h:i a', strtotime($a['creado_en'])) ?>
                  </div>
                </div>
              </div>
              <?php endforeach; ?>
            <?php else: ?>
              <div style="text-align:center; padding:30px; color:var(--texto-tenue); font-size:0.9rem;">
                <i class="fas fa-history" style="font-size:2rem; margin-bottom:12px; opacity:0.3; display:block;"></i>
                No hay actividad académica reciente.
              </div>
            <?php endif; ?>
          </div>
        </div>

      </div><!-- /grid -->
    </div><!-- /pagina-contenido -->
  </main>
</div>

<script src="<?= PROYECTO_PATH ?>/assets/js/tema.js"></script>
</body>
</html>
