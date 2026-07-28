<!DOCTYPE html>
<html lang="es" data-theme="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Aprender — SmashCode Enfermería SENA</title>
  <meta name="description" content="Aprende inglés clínico con SmashCode, plataforma gamificada para enfermería SENA.">
  <link rel="stylesheet" href="<?= PROYECTO_PATH ?>/assets/css/estilos.css?v=<?= time() ?>">
  <link rel="stylesheet" href="<?= PROYECTO_PATH ?>/assets/css/layout.css?v=<?= time() ?>">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <script>/* Aplicar tema guardado antes del paint para evitar parpadeo */
  (function(){var t=localStorage.getItem('smashcode_tema');if(t)document.documentElement.setAttribute('data-theme',t);})();
  </script>
  <style>
    /* Variables from reference */
    :root {
      --duo-green: #58cc02;
      --duo-green-dark: #46a302;
      --duo-blue: #1cb0f6;
      --duo-blue-dark: #1899d6;
      --duo-gray: var(--gris-claro);
      --duo-gray-dark: var(--gris-medio);
      --duo-text: var(--gris-texto);
    }

    /* Main Content Area */
    .learning-path-view {
      display: flex;
      padding: 20px 40px;
      gap: 40px;
      background: var(--fondo);
      flex: 1;
      height: 100vh;
      overflow-y: auto;
    }

    .main-column {
      flex: 1;
      max-width: 600px;
      margin: 0 auto;
    }

    /* Unit Header */
    .unit-header {
      background: var(--duo-green);
      border-radius: 15px;
      padding: 20px;
      color: white;
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 40px;
      box-shadow: 0 4px 0 var(--duo-green-dark);
    }
    .unit-info .back-btn { font-size: 14px; font-weight: 800; opacity: 0.8; margin-bottom: 5px; }
    .unit-info h1 { font-size: 24px; font-weight: 800; margin: 0; }
    .guide-btn {
      background: transparent; border: 2px solid rgba(255,255,255,0.5);
      color: white; padding: 10px 15px; border-radius: 12px;
      font-weight: 800; cursor: pointer; display: flex; align-items: center; gap: 10px;
    }

    /* Path Container */
    .path-container {
      display: flex; flex-direction: column; align-items: center;
      gap: 60px; padding-bottom: 100px;
    }
    .path-item { position: relative; width: 100%; display: flex; justify-content: center; overflow: visible; z-index: 1; }
    .path-item.current { z-index: 50; }
    .path-item.offset-right { transform: translateX(40px); }
    .path-item.offset-left { transform: translateX(-40px); }

    .node-wrapper { position: relative; display: flex; justify-content: center; align-items: center; }
    .node-wrapper::before {
      content: ''; position: absolute; top: 50%; left: 50%;
      transform: translate(-50%, -50%); width: 82px; height: 82px;
      background: transparent; border: 8px solid var(--gris-claro); border-radius: 50%;
      z-index: 0; pointer-events: none;
    }
    .path-item.current .node-wrapper::before { display: none; }

    @keyframes bounce {
      0%, 100% { transform: translateX(-50%) translateY(0); }
      50% { transform: translateX(-50%) translateY(-5px); }
    }
    .node-wrapper .tooltip {
      position: absolute; top: -45px; left: 50%; transform: translateX(-50%);
      background: var(--blanco); border: 2px solid var(--duo-gray); padding: 5px 15px;
      border-radius: 12px; font-weight: 800; font-size: 14px; color: var(--duo-green);
      box-shadow: 0 2px 0 var(--duo-gray); animation: bounce 2s infinite ease-in-out;
      white-space: nowrap; z-index: 10;
    }
    .node-wrapper .tooltip::after {
      content: ''; position: absolute; bottom: -10px; left: 50%;
      transform: translateX(-50%); border-left: 10px solid transparent;
      border-right: 10px solid transparent; border-top: 10px solid var(--blanco);
    }

    .node {
      position: relative; z-index: 1; width: 70px; height: 65px;
      border-radius: 50%; display: flex; align-items: center; justify-content: center;
      font-size: 30px; cursor: pointer; transition: all 0.1s ease;
    }
    .node.star { background: var(--duo-green); color: #fff; box-shadow: 0 6px 0 var(--duo-green-dark); }
    .node.star:hover { transform: translateY(2px); box-shadow: 0 4px 0 var(--duo-green-dark); }
    .node.star:active { transform: translateY(6px); box-shadow: 0 0 0 var(--duo-green-dark); }
    .node.star-locked { background: var(--gris-claro); color: var(--gris-medio); box-shadow: 0 6px 0 var(--borde-sutil); cursor: not-allowed; }
    .node.completed { background: var(--duo-green); color: #fff; box-shadow: 0 6px 0 var(--duo-green-dark); }
    .node.chest-complete { background: #ffd700; color: #fff; box-shadow: 0 6px 0 #cc9900; }
    .node.trophy-complete { background: var(--duo-green); color: #fff; box-shadow: 0 6px 0 var(--duo-green-dark); }

    .progress-ring { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 0; pointer-events: none; }

    /* Side Column */
    .side-column { width: 350px; flex-shrink: 0; }
    .right-stats-bar { display: flex; justify-content: space-between; padding: 10px 0; margin-bottom: 20px; }
    .stat { display: flex; align-items: center; gap: 8px; font-weight: 800; font-size: 16px; color: var(--duo-text); }
    .stat.fire { color: #ff9600; }
    .stat.gem { color: var(--duo-blue); }
    .stat.heart { color: #ff4b4b; }

    .card { border: 2px solid var(--gris-claro); border-radius: 15px; padding: 15px; margin-bottom: 20px; background: var(--blanco); }
    .card h3 { font-size: 18px; font-weight: 800; margin-bottom: 15px; color: var(--gris-texto); }
    .promo-content { display: flex; align-items: center; gap: 15px; }
    .lock-icon { width: 50px; height: 50px; background: var(--gris-claro); border-radius: 10px; display: flex; align-items: center; justify-content: center; color: var(--gris-medio); font-size: 20px; }

    .daily-card .card-header { display: flex; justify-content: space-between; align-items: center; }
    .daily-card a { color: var(--duo-blue); text-decoration: none; font-weight: 800; font-size: 12px; }
    .challenge-item { display: flex; align-items: center; gap: 15px; margin-top: 10px; }
    .challenge-item i { color: #ff9600; font-size: 24px; }
    .mini-progress-bar { width: 200px; height: 12px; background: var(--gris-claro); border-radius: 10px; position: relative; margin-top: 5px; }
    .mini-progress-fill { height: 100%; background: var(--naranja); border-radius: 10px; }
    .mini-progress-bar i { position: absolute; right: -10px; top: -5px; font-size: 14px; color: #cd7f32; }
    
    .signup-card { text-align: center; }
    .signup-card button { width: 100%; padding: 12px; border-radius: 12px; font-weight: 800; margin-top: 10px; cursor: pointer; border: none; }
    .btn-create { background: var(--verde); color: #fff; box-shadow: 0 4px 0 var(--verde-oscuro); }
    .btn-login { background: var(--azul); color: #fff; box-shadow: 0 4px 0 var(--azul-oscuro); }
  </style>
</head>
<body>

<div class="contenedor-app">

  <?php include dirname(__DIR__) . '/Views/layouts/aprendiz_sidebar.php'; ?>

  <!-- ============ CONTENIDO PRINCIPAL ============ -->
  <main class="contenido-principal">

    <!-- Barra superior -->
    <?php if ($autenticado && $usuario): ?>
    <header class="barra-superior">
      <div class="stat-xp">
        <i class="fas fa-bolt"></i>
        <?= formatearXP($usuario['xp_puntos']) ?> XP
      </div>
      <div class="stat-racha">
        <i class="fas fa-fire"></i>
        Racha: 0 días
      </div>

      <div style="margin-left: auto; display:flex; align-items:center; gap:16px;">
        <div class="avatar-usuario" title="<?= limpiar($usuario['nombre_completo']) ?>">
          <?= strtoupper(substr($usuario['nombre_completo'], 0, 1)) ?>
        </div>
      </div>
    </header>
    <?php endif; ?>

    <!-- Zona del mapa + panel derecho -->
    <div class="learning-path-view">

      <!-- MAPA DE PROGRESO -->
      <div class="main-column">
        
        <?php
        // Encontrar la sección (nivel) activa actual
        $seccionActiva = null;
        if ($autenticado) {
            foreach ($niveles as $nivel) {
                if (!isset($mapaProgreso[$nivel['rap_id']]) || !$mapaProgreso[$nivel['rap_id']]['completado']) {
                    $seccionActiva = $nivel;
                    break;
                }
            }
        }
        if (!$seccionActiva) {
            $seccionActiva = $niveles[0] ?? ['orden' => 1, 'nombre' => 'Conceptos Básicos'];
        }
        ?>
        
        <!-- Green Header Section -->
        <div class="unit-header">
            <div class="unit-info">
                <div class="back-btn" id="header-competencia">
                    <i class="fas fa-arrow-left"></i> ETAPA 1, SECCIÓN <?= $seccionActiva['orden'] ?>
                </div>
                <h1 id="header-title"><?= limpiar($seccionActiva['nombre']) ?></h1>
            </div>
            <button class="guide-btn"><i class="fas fa-book-open"></i> GUÍA</button>
        </div>

        <!-- Dynamic Path -->
        <div class="path-container" id="path-container">
            <?php
            $primerActivo = true; // Para saber cuál es el nodo actual (para el tooltip EMPEZAR y el progreso)
            $OFFSETS = ['', '', 'offset-right', 'offset-left', '', 'offset-right'];
            $todosCompletados = true;

            foreach ($niveles as $i => $nivel):
              $estado = $autenticado
                  ? (isset($mapaProgreso[$nivel['rap_id']])
                      ? ($mapaProgreso[$nivel['rap_id']]['completado'] ? 'completado' : ($mapaProgreso[$nivel['rap_id']]['porcentaje'] > 0 ? 'en_progreso' : 'disponible'))
                      : ($nivel['orden'] === 1 ? 'disponible' : 'bloqueado')) // Nivel 1 siempre disponible
                  : ($nivel['orden'] === 1 ? 'disponible' : 'bloqueado');

              // Lógica de desbloqueo avanzado
              if ($autenticado && $estado === 'bloqueado' && $nivel['orden'] > 1) {
                  $anteriorOrden = $nivel['orden'] - 1;
                  $nivelAnterior = array_filter($niveles, fn($n) => $n['orden'] === $anteriorOrden);
                  $nivelAnterior = reset($nivelAnterior);
                  if ($nivelAnterior && isset($mapaProgreso[$nivelAnterior['rap_id']])) {
                      if ($mapaProgreso[$nivelAnterior['rap_id']]['porcentaje'] >= 80) {
                          $estado = 'disponible';
                      }
                  }
              }

              $offsetClase = $OFFSETS[$i % count($OFFSETS)];
              $esPrincipal = ($estado === 'disponible' || $estado === 'en_progreso') && $primerActivo;
              if ($esPrincipal) $primerActivo = false;
              if ($estado !== 'completado') $todosCompletados = false;

              $urlRap = $autenticado && $estado !== 'bloqueado'
                  ? PROYECTO_PATH . '/aprendiz/rap?id=' . urlencode($nivel['rap_id'])
                  : '#';

              // Clases e íconos exactos de la referencia
              $nodeClass = '';
              $iconHtml = '';
              $isActive = false;

              $iconosRefs = ['fa-star', 'fa-book', 'fa-star', 'fa-star', 'fa-heart', 'fa-star'];
              $iconoAct = $iconosRefs[$i % count($iconosRefs)];

              if ($estado === 'completado') {
                  $nodeClass = 'star completed';
                  $iconHtml = '<i class="fas fa-check"></i>';
              } else if ($esPrincipal) {
                  $nodeClass = 'star';
                  $iconHtml = '<i class="fas '.$iconoAct.'"></i>';
                  $isActive = true;
              } else {
                  $nodeClass = 'star-locked';
                  $iconHtml = '<i class="fas '.$iconoAct.'"></i>';
              }
            ?>
            <div class="path-item <?= $isActive ? 'current' : 'locked' ?> <?= $offsetClase ?>">
                <div class="node-wrapper" 
                     onclick="<?= $estado !== 'bloqueado' ? "window.location='{$urlRap}'" : "mostrarMensajeBloqueado()" ?>" 
                     title="<?= limpiar($nivel['nombre']) ?>">
                    
                    <?php if ($isActive): ?>
                        <span class="tooltip" id="start-tooltip">EMPEZAR</span>
                    <?php endif; ?>
                    
                    <div class="node <?= $nodeClass ?>" <?= $isActive ? 'id="star-node"' : '' ?>>
                        <?= $iconHtml ?>
                    </div>
                    
                    <?php if ($isActive): 
                        // Progress ring SVG
                        $pct = isset($mapaProgreso[$nivel['rap_id']]) ? $mapaProgreso[$nivel['rap_id']]['porcentaje'] : 0;
                        $radius = 45;
                        $circumference = 2 * pi() * $radius;
                        $dashoffset = $circumference - ($pct / 100) * $circumference;
                    ?>
                    <svg class="progress-ring" width="100" height="100" viewBox="0 0 100 100">
                        <circle cx="50" cy="50" r="<?= $radius ?>" fill="none" stroke="#e5e5e5" stroke-width="8"/>
                        <circle cx="50" cy="50" r="<?= $radius ?>" fill="none" stroke="#58cc02" stroke-width="8"
                                stroke-dasharray="<?= $circumference ?>" stroke-dashoffset="<?= $dashoffset ?>"
                                stroke-linecap="round" transform="rotate(-90 50 50)"/>
                    </svg>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
            
            <!-- Cofre final -->
            <?php
              $chestIndex = count($niveles);
              $chestOffset = $OFFSETS[$chestIndex % count($OFFSETS)];
            ?>
            <div class="path-item <?= $todosCompletados ? 'completed' : 'locked' ?> <?= $chestOffset ?>">
                <div class="node-wrapper">
                    <div class="node <?= $todosCompletados ? 'chest-complete' : 'chest' ?>">
                        <i class="fas fa-box-open"></i>
                    </div>
                </div>
            </div>
            
            <!-- Trofeo final -->
            <?php
              $trophyIndex = count($niveles) + 1;
              $trophyOffset = $OFFSETS[$trophyIndex % count($OFFSETS)];
            ?>
            <div class="path-item <?= $todosCompletados ? 'completed' : 'locked' ?> <?= $trophyOffset ?>">
                <div class="node-wrapper">
                    <div class="node <?= $todosCompletados ? 'trophy-complete' : 'trophy' ?>">
                        <i class="fas fa-trophy"></i>
                    </div>
                </div>
            </div>

        </div>
      </div>

      <!-- PANEL LATERAL DERECHO -->
      <aside class="side-column" aria-label="Panel de gamificación">
          
        <div class="right-stats-bar">
            <div class="stat"><img src="https://flagcdn.com/us.svg" width="25" alt="EN" style="border-radius:4px;"></div>
            <div class="stat fire"><i class="fas fa-fire"></i> <span>0</span></div>
            <div class="stat gem"><i class="fas fa-gem"></i> <span>0</span></div>
            <div class="stat xp" style="color:var(--duo-blue);"><i class="fas fa-bolt"></i> <span><?= $autenticado ? formatearXP($usuario['xp_puntos']) : '0' ?> XP</span></div>
            <div class="stat heart"><i class="fas fa-heart"></i> <span>5</span></div>
        </div>

        <div class="card promo-card">
            <h3>¡Compite en las Ligas!</h3>
            <div class="promo-content">
                <div class="lock-icon"><i class="fas fa-lock"></i></div>
                <p style="font-size:14px;color:var(--texto-tenue);">Completa lecciones para empezar a competir</p>
            </div>
        </div>

        <div class="card daily-card">
            <div class="card-header">
                <h3>Desafíos del día</h3>
                <a href="#">VER TODOS</a>
            </div>
            <div class="challenge-item">
                <i class="fas fa-bolt"></i>
                <div style="flex:1;">
                    <p style="font-size:14px;font-weight:600;margin-bottom:4px;">Gana 10 XP</p>
                    <div class="mini-progress-bar">
                        <div class="mini-progress-fill" style="width: <?= $autenticado ? min(100,($usuario['xp_puntos']??0)/10*100) : 0 ?>%;"></div>
                        <i class="fas fa-box"></i>
                    </div>
                    <div style="font-size:11px;color:var(--texto-tenue);margin-top:4px;"><?= $autenticado ? min(10,$usuario['xp_puntos']??0) : 0 ?> / 10</div>
                </div>
            </div>
        </div>

        <?php if (!$autenticado): ?>
        <div class="card signup-card">
            <h3>¡Crea un perfil para guardar tu progreso!</h3>
            <a href="<?= PROYECTO_PATH ?>/login" style="text-decoration:none;"><button class="btn-create">INGRESAR</button></a>
        </div>
        <?php endif; ?>

      </aside>

    </div><!-- /learning-path-view -->
  </main>
</div><!-- /contenedor-app -->

<script>
  /* Mostrar mensaje cuando el aprendiz intenta acceder a un nivel bloqueado */
  function mostrarMensajeBloqueado() {
    alert('🔒 Este nivel está bloqueado. Completa el nivel anterior con al menos 80% de progreso.');
  }
</script>
<script src="<?= PROYECTO_PATH ?>/assets/js/tema.js"></script>
</body>
</html>
