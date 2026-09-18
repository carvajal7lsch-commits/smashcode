<!DOCTYPE html>
<html lang="es" data-theme="dark">
<head>
  <meta charset="UTF-8">
  <link rel="icon" type="image/svg+xml" href="<?= PROYECTO_PATH ?>/assets/img/favicon.svg">
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

    /* Path Container
       Geometría de los nodos en un solo sitio. El nodo es un círculo y el aro se
       deriva de él, así el aire es igual por los cuatro lados. Antes el nodo medía
       70x65 (una elipse) y el aro 98px, con 14px de holgura a los lados pero 16.5
       arriba y abajo: de ahí que se viera descentrado. */
    .path-container {
      display: flex; flex-direction: column; align-items: center;
      /* 28px de separación + los 30px que cada nodo reserva para su etiqueta */
      gap: 28px; padding-bottom: 100px;

      --nodo: 72px;          /* diámetro del círculo */
      --aro-grosor: 6px;     /* grosor del anillo */
      --aro-aire: 2px;       /* separación entre el círculo y el anillo */
      --aro: 88px;           /* 72 + 2·2 de aire + 2·6 de borde */
      --nodo-sombra: 6px;    /* borde 3D inferior del nodo */
    }
    .path-item {
      position: relative; width: 100%; display: flex; justify-content: center;
      overflow: visible; z-index: 1;
      /* Reserva el espacio de la etiqueta: sin esto el aro, que sobresale por
         debajo del nodo, se le montaba encima. */
      padding-bottom: 30px;
    }
    /* El nodo actual lleva el globo "EMPEZAR" a 45px por encima (más 5 del rebote),
       así que reserva ese alto. Antes el separador del módulo usaba margin-bottom:-20px
       y el globo terminaba encima de la píldora del título. */
    .path-item.current { z-index: 50; margin-top: 26px; }
    .path-item.offset-right { transform: translateX(40px); }
    .path-item.offset-left { transform: translateX(-40px); }

    .node-wrapper { position: relative; display: flex; justify-content: center; align-items: center; }
    .node-wrapper::before {
      content: ''; position: absolute; top: 50%; left: 50%;
      /* El nodo se ve desplazado hacia abajo por su sombra 3D, así que el aro
         acompaña ese desplazamiento en vez de quedar centrado sobre el círculo. */
      transform: translate(-50%, calc(-50% + var(--nodo-sombra) / 2));
      width: var(--aro); height: var(--aro);
      box-sizing: border-box;                        /* el borde ya no suma al tamaño */
      background: transparent; border: var(--aro-grosor) solid var(--gris-claro);
      border-radius: 50%; z-index: 0; pointer-events: none;
    }
    .path-item.current .node-wrapper::before { display: none; }

    @keyframes bounce {
      0%, 100% { transform: translateX(-50%) translateY(0); }
      50% { transform: translateX(-50%) translateY(-5px); }
    }
    .node-wrapper .tooltip {
      position: absolute; top: -45px; left: 50%; transform: translateX(-50%);
      background: var(--blanco); border: 2px solid var(--gris-claro); padding: 5px 15px;
      border-radius: 12px; font-weight: 800; font-size: 14px; color: var(--duo-green);
      box-shadow: 0 3px 0 var(--gris-claro); animation: bounce 2s infinite ease-in-out;
      white-space: nowrap; z-index: 10;
    }
    .node-wrapper .tooltip::after {
      content: ''; position: absolute; bottom: -8px; left: 50%;
      transform: translateX(-50%); border-left: 8px solid transparent;
      border-right: 8px solid transparent; border-top: 8px solid var(--blanco);
    }
    [data-theme="light"] .node-wrapper .tooltip {
      background: #FFFFFF;
      border-color: #E2E8F0;
      box-shadow: 0 3px 0 #E2E8F0;
    }
    [data-theme="light"] .node-wrapper .tooltip::after {
      border-top-color: #FFFFFF;
    }

    .node {
      position: relative; z-index: 1; width: var(--nodo); height: var(--nodo);
      border-radius: 50%; display: flex; align-items: center; justify-content: center;
      font-size: 30px; cursor: pointer; transition: all 0.1s ease;
    }
    .node.star { background: var(--duo-green); color: #fff; box-shadow: 0 6px 0 var(--duo-green-dark); }
    .node.star:hover { transform: translateY(2px); box-shadow: 0 4px 0 var(--duo-green-dark); }
    .node.star:active { transform: translateY(6px); box-shadow: 0 0 0 var(--duo-green-dark); }
    .node.star-locked { background: var(--gris-claro); color: var(--gris-medio); box-shadow: 0 4px 0 var(--borde-sutil); cursor: not-allowed; font-size: 25px; }
    /* Los momentos bloqueados pesan menos: círculo, aro y sombra más pequeños, para
       que la vista se vaya a lo que sí está disponible. El aro se encoge con ellos
       y conserva los mismos 2px de aire. */
    .path-item:has(.star-locked) { --nodo: 62px; --aro: 78px; --nodo-sombra: 4px; }
    .node.completed { background: var(--duo-green); color: #fff; box-shadow: 0 6px 0 var(--duo-green-dark); }
    .node.chest-complete { background: #ffd700; color: #fff; box-shadow: 0 6px 0 #cc9900; }
    .node.trophy-complete { background: var(--duo-green); color: #fff; box-shadow: 0 6px 0 var(--duo-green-dark); }

    /* Mismo tamaño y mismo desplazamiento que el aro gris, para que el anillo de
       progreso del momento actual caiga exactamente donde cae el de los demás. */
    .progress-ring {
      position: absolute; top: 50%; left: 50%;
      transform: translate(-50%, calc(-50% + var(--nodo-sombra) / 2));
      width: var(--aro); height: var(--aro);
      z-index: 0; pointer-events: none;
    }

    /* ── Panel lateral derecho ── */
    .rango-medalla {
      width: 44px; height: 44px; border-radius: 50%; flex-shrink: 0;
      background: var(--duo-green); color: #fff;
      display: flex; align-items: center; justify-content: center; font-size: 20px;
    }
    .insignia-chip {
      display: inline-flex; align-items: center; gap: 6px;
      font-size: 11px; font-weight: 800; padding: 5px 10px; border-radius: 999px;
      background: var(--gris-claro); color: var(--texto-principal);
    }
    .insignia-chip i { color: var(--naranja); }
    .ranking-fila {
      display: flex; align-items: center; gap: 10px;
      padding: 7px 0; font-size: 13px; font-weight: 700;
    }
    .ranking-fila + .ranking-fila { border-top: 1px solid var(--borde-sutil); }
    .ranking-puesto {
      width: 22px; height: 22px; border-radius: 50%; flex-shrink: 0;
      background: var(--gris-claro); color: var(--texto-tenue);
      display: flex; align-items: center; justify-content: center; font-size: 11px;
    }
    .ranking-yo .ranking-puesto { background: var(--duo-green); color: #fff; }
    .ranking-nombre { flex: 1; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .ranking-xp { color: var(--texto-tenue); font-size: 12px; }

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
        // Agrupar RAPs por Nivel
        $nivelesAgrupados = [];
        foreach ($niveles as $row) {
            $nId = $row['id'];
            if (!isset($nivelesAgrupados[$nId])) {
                $nivelesAgrupados[$nId] = [
                    'id' => $row['id'],
                    'nombre' => $row['nombre'],
                    'descripcion' => $row['descripcion'] ?? '',
                    'orden' => $row['orden'] ?? 1,
                    'umbral' => $row['umbral_desbloqueo'],
                    'raps' => []
                ];
            }
            $nivelesAgrupados[$nId]['raps'][] = [
                'id' => $row['rap_id'],
                'titulo' => $row['rap_titulo'],
                'orden' => $row['rap_orden'] ?? 1
            ];
        }

        // Calcular promedios de progreso por nivel
        foreach ($nivelesAgrupados as &$nivelData) {
            $totalPorcentaje = 0;
            foreach ($nivelData['raps'] as $rap) {
                if ($autenticado && isset($mapaProgreso[$rap['id']])) {
                    $totalPorcentaje += $mapaProgreso[$rap['id']]['porcentaje'];
                }
            }
            $nivelData['progreso_promedio'] = count($nivelData['raps']) > 0 ? ($totalPorcentaje / count($nivelData['raps'])) : 0;
        }
        unset($nivelData); // Importante: limpiar la referencia del foreach anterior
        reset($nivelesAgrupados); // Resetear el puntero interno del array

        $primerActivo = true; // Para el tooltip de EMPEZAR
        $OFFSETS = ['', '', 'offset-right', 'offset-left', '', 'offset-right'];
        $todosCompletadosGlobal = true;
        $promedioNivelAnterior = 100.00; // El nivel 1 siempre está desbloqueado
        $globalRapIndex = 0; // Para los offsets
        
        // Determinar la sección activa: primer nivel desbloqueado con progreso < 100%
        $seccionActiva = null;
        $promedioAnteriorParaHeader = 100.00;
        foreach ($nivelesAgrupados as $nivelData) {
            $nivelAlcanzable = ($nivelData['orden'] == 1) || (\App\Models\AccesoCurso::alcanzaUmbral($promedioAnteriorParaHeader,(float)$nivelData['umbral']));
            if ($nivelAlcanzable && $nivelData['progreso_promedio'] < 100) {
                $seccionActiva = $nivelData;
                break;
            }
            $promedioAnteriorParaHeader = $nivelData['progreso_promedio'];
        }
        if (!$seccionActiva) {
            $seccionActiva = end($nivelesAgrupados); // todos completados, mostrar el último
        }
        ?>
        
        <!-- Green Header Section -->
        <div class="unit-header">
            <div class="unit-info">
                <div class="back-btn">
                    <i class="fas fa-book-open"></i> MÓDULO <?= $seccionActiva['orden'] ?>
                </div>
                <h1 id="header-title"><?= limpiar($seccionActiva['nombre']) ?></h1>
            </div>
            <button class="guide-btn" type="button" onclick="verGuiaModulo()">
                <i class="fas fa-book-open"></i> GUÍA
            </button>
        </div>

        <div class="path-container" style="padding-bottom: 40px;">
            <?php
            // Definir los 4 Momentos pedagógicos (igual para todos los RAPs)
            $momentosDef = [
                1 => ['nombre' => 'Warm-Up',    'icono' => 'fa-gamepad',     'umbral' => 0],
                2 => ['nombre' => 'Absorption',  'icono' => 'fa-book-reader', 'umbral' => 25],
                3 => ['nombre' => 'Practice',    'icono' => 'fa-dumbbell',    'umbral' => 50],
                4 => ['nombre' => 'Quiz',        'icono' => 'fa-award',       'umbral' => 75],
            ];

            foreach ($nivelesAgrupados as $indexNivel => $nivelData):
                // Un nivel está desbloqueado si es el primero o si el nivel anterior alcanzó su umbral configurado
                $nivelDesbloqueado = (!$autenticado)
                    ? ($nivelData['orden'] == 1)
                    : (($nivelData['orden'] == 1) || (\App\Models\AccesoCurso::alcanzaUmbral($promedioNivelAnterior,(float)$nivelData['umbral'])));

                $rap = $nivelData['raps'][0] ?? null;
                if (!$rap) continue;

                $pctRap = (float)$nivelData['progreso_promedio'];
                if (!$nivelDesbloqueado) {
                    $estadoRap = 'bloqueado';
                } elseif ($pctRap >= 100) {
                    $estadoRap = 'completado';
                } elseif ($pctRap > 0) {
                    $estadoRap = 'en_progreso';
                } else {
                    $estadoRap = 'disponible';
                }

                if ($estadoRap !== 'completado') $todosCompletadosGlobal = false;
            ?>
                    <!-- ── Encabezado de sección del Módulo / Nivel ── -->
                    <div style="width:100%; text-align:center; margin-bottom:4px; margin-top:24px;">
                        <span style="
                            display: inline-block;
                            background: var(--blanco);
                            border: 2px solid var(--gris-claro);
                            border-radius: 10px;
                            padding: 6px 18px;
                            font-size: 13px;
                            font-weight: 800;
                            color: var(--duo-green);
                            letter-spacing: 0.08em;
                            text-transform: uppercase;
                        ">
                            <i class="fas fa-book-open" style="margin-right:6px; color:var(--duo-green);"></i>
                            <?= limpiar($nivelData['nombre']) ?>
                        </span>
                        <?php if ($autenticado && $estadoRap === 'completado'): ?>
                            <!-- HU14: repetir un módulo completado; el avance y el mejor puntaje se conservan -->
                            <a href="<?= htmlspecialchars(PROYECTO_PATH . '/aprendiz/rap?id=' . urlencode($rap['id']) . '&repetir=1', ENT_QUOTES) ?>"
                               class="btn-repetir-rap"
                               title="Repasa el módulo desde el Momento 1. Tu avance y tu mejor puntaje se conservan."
                               style="display:inline-block; vertical-align:middle; margin-left:8px; padding:6px 12px; border-radius:10px; background:var(--azul, #1cb0f6); color:#fff; font-size:12px; font-weight:800; letter-spacing:0.06em; text-transform:uppercase; text-decoration:none;">
                                <i class="fas fa-redo" style="margin-right:4px;"></i> Repetir RAP
                            </a>
                        <?php endif; ?>
                    </div>

            <?php foreach ($momentosDef as $mNum => $mDef):
                    // Estado de este momento según el porcentaje del RAP
                    if ($estadoRap === 'bloqueado') {
                        $estadoMomento = 'bloqueado';
                    } elseif ($pctRap >= 100 || $estadoRap === 'completado') {
                        $estadoMomento = 'completado';
                    } elseif ($pctRap >= $mDef['umbral']) {
                        // Si ya pasó el umbral del siguiente momento, este ya fue completado
                        $umbralSiguiente = $mNum < 4 ? $momentosDef[$mNum + 1]['umbral'] : 101;
                        if ($pctRap >= $umbralSiguiente) {
                            $estadoMomento = 'completado';
                        } elseif ($pctRap >= $mDef['umbral']) {
                            $estadoMomento = 'activo';
                        }
                    } else {
                        $estadoMomento = 'bloqueado';
                    }

                    // Si no hay usuario autenticado, solo el Momento 1 del RAP 1 está activo
                    if (!$autenticado) {
                        if ($nivelData['orden'] == 1 && $mNum == 1) {
                            $estadoMomento = 'activo';
                        } else {
                            $estadoMomento = 'bloqueado';
                        }
                    }

                    // Determinar si es la esfera activa principal (tooltip EMPEZAR)
                    $esPrincipal = ($estadoMomento === 'activo') && $primerActivo;
                    if ($esPrincipal) $primerActivo = false;

                    // Offset zigzag continuo
                    $offsetClase = $OFFSETS[$globalRapIndex % count($OFFSETS)];
                    $globalRapIndex++;

                    // URL del momento
                    $urlMomento = ($autenticado && $estadoMomento !== 'bloqueado')
                        ? PROYECTO_PATH . '/aprendiz/rap?id=' . urlencode($rap['id']) . '&momento=' . $mNum
                        : '#';

                    // Clases y HTML del nodo según estado
                    if ($estadoMomento === 'completado') {
                        $nodeClass = 'star completed';
                        $iconHtml  = '<i class="fas fa-check"></i>';
                        $isActive  = false;
                    } elseif ($estadoMomento === 'activo') {
                        $nodeClass = 'star';
                        $iconHtml  = '<i class="fas ' . $mDef['icono'] . '"></i>';
                        $isActive  = $esPrincipal;
                    } else {
                        $nodeClass = 'star-locked';
                        $iconHtml  = '<i class="fas ' . $mDef['icono'] . '"></i>';
                        $isActive  = false;
                    }

                    // Label debajo de la esfera
                    $momentoLabel = 'M' . $mNum . ': ' . $mDef['nombre'];
            ?>
                    <div class="path-item <?= $isActive ? 'current' : '' ?> <?= $offsetClase ?>"
                         style="<?= $isActive ? 'z-index:50;' : '' ?>">
                        <div class="node-wrapper"
                             onclick="<?= $estadoMomento !== 'bloqueado' ? "window.location='{$urlMomento}'" : "mostrarMensajeBloqueado(" . (float)$nivelData['umbral'] . "," . ($estadoRap === 'bloqueado' ? 'true' : 'false') . ")" ?>"
                             title="<?= limpiar($nivelData['nombre']) ?> — <?= $mDef['nombre'] ?>">

                            <?php if ($isActive): ?>
                                <span class="tooltip" id="start-tooltip-<?= $globalRapIndex ?>">EMPEZAR</span>
                            <?php endif; ?>

                            <div class="node <?= $nodeClass ?>" <?= $isActive ? 'id="star-node"' : '' ?>>
                                <?= $iconHtml ?>
                            </div>

                            <?php if ($isActive && $autenticado):
                                // 41 = (88 del aro − 6 de trazo) / 2, para que el anillo de
                                // progreso pise exactamente el mismo sitio que el aro gris.
                                $radius        = 41;
                                $circumference = 2 * pi() * $radius;
                                $dashoffset    = $circumference - ($pctRap / 100) * $circumference;
                            ?>
                            <svg class="progress-ring" width="88" height="88" viewBox="0 0 88 88">
                                <circle cx="44" cy="44" r="<?= $radius ?>" fill="none" stroke="var(--gris-claro)" stroke-width="6"/>
                                <circle cx="44" cy="44" r="<?= $radius ?>" fill="none" stroke="#58cc02" stroke-width="6"
                                        stroke-dasharray="<?= $circumference ?>" stroke-dashoffset="<?= $dashoffset ?>"
                                        stroke-linecap="round" transform="rotate(-90 44 44)"/>
                            </svg>
                            <?php endif; ?>
                        </div>

                        <!-- Label del momento: vive dentro del padding reservado por
                             .path-item, así el aro nunca se le monta encima -->
                        <div style="
                            position: absolute;
                            bottom: 2px;
                            left: 50%;
                            transform: translateX(-50%);
                            font-size: 10px;
                            font-weight: 800;
                            color: var(--gris-medio);
                            white-space: nowrap;
                            letter-spacing: 0.04em;
                            text-transform: uppercase;
                        "><?= $momentoLabel ?></div>
                    </div>
            <?php endforeach; // fin momentos ?>
            <?php
                $promedioNivelAnterior = $nivelData['progreso_promedio'];
            endforeach; // fin niveles
            ?>

            <!-- Cofre final -->
            <?php
              $chestOffset = $OFFSETS[$globalRapIndex % count($OFFSETS)];
              $globalRapIndex++;
            ?>
            <div class="path-item <?= $todosCompletadosGlobal ? 'completed' : 'locked' ?> <?= $chestOffset ?>">
                <div class="node-wrapper">
                    <div class="node <?= $todosCompletadosGlobal ? 'chest-complete' : 'chest' ?>">
                        <i class="fas fa-box-open"></i>
                    </div>
                </div>
            </div>

            <!-- Trofeo final -->
            <?php
              $trophyOffset = $OFFSETS[$globalRapIndex % count($OFFSETS)];
            ?>
            <div class="path-item <?= $todosCompletadosGlobal ? 'completed' : 'locked' ?> <?= $trophyOffset ?>">
                <div class="node-wrapper">
                    <div class="node <?= $todosCompletadosGlobal ? 'trophy-complete' : 'trophy' ?>">
                        <i class="fas fa-trophy"></i>
                    </div>
                </div>
            </div>
        </div>

      </div>



      <!-- PANEL LATERAL DERECHO -->
      <aside class="side-column" aria-label="Panel de gamificación">
          
        <!-- Solo indicadores con respaldo real: XP acumulado, racha de días y rango
             clínico. Antes había bandera, gemas y vidas fijas de una maqueta. -->
        <div class="right-stats-bar">
            <div class="stat xp" title="Puntos de experiencia acumulados">
                <i class="fas fa-bolt"></i> <span><?= $autenticado ? formatearXP($usuario['xp_puntos']) : '0' ?> XP</span>
            </div>
            <div class="stat fire" title="Días seguidos practicando">
                <i class="fas fa-fire"></i> <span><?= (int) $racha ?> <?= (int) $racha === 1 ? 'día' : 'días' ?></span>
            </div>
        </div>

        <?php if ($autenticado): ?>
        <div class="card">
            <div class="card-header">
                <h3>Tu rango clínico</h3>
                <a href="<?= PROYECTO_PATH ?>/aprendiz/perfil">VER PERFIL</a>
            </div>
            <div style="display:flex; align-items:center; gap:12px; margin-top:12px;">
                <div class="rango-medalla"><i class="fas fa-user-nurse"></i></div>
                <div style="flex:1;">
                    <p style="font-size:15px; font-weight:800; margin-bottom:2px;"><?= htmlspecialchars($rango['nombre']) ?></p>
                    <p style="font-size:12px; color:var(--texto-tenue);">Nivel <?= (int) $rango['nivel'] ?> · <?= formatearXP($usuario['xp_puntos']) ?> XP</p>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3>Insignias</h3>
                <a href="<?= PROYECTO_PATH ?>/aprendiz/perfil">VER TODAS</a>
            </div>
            <p style="font-size:13px; color:var(--texto-tenue); margin:10px 0 12px;">
                <?= count($insigniasGanadas) ?> de <?= (int) $totalInsignias ?> conseguidas
            </p>
            <?php if ($insigniasGanadas): ?>
                <div style="display:flex; flex-wrap:wrap; gap:8px;">
                    <?php foreach (array_slice($insigniasGanadas, 0, 4) as $insignia): ?>
                        <span class="insignia-chip" title="<?= htmlspecialchars($insignia['descripcion'] ?? '') ?>">
                            <i class="fas fa-medal"></i> <?= htmlspecialchars($insignia['nombre']) ?>
                        </span>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p style="font-size:13px; color:var(--texto-tenue);">Aprueba un quiz con 90% o más para conseguir la primera.</p>
            <?php endif; ?>
        </div>

        <?php if (count($leaderboard) > 1): ?>
        <div class="card">
            <div class="card-header">
                <h3>Ranking de la semana</h3>
                <a href="<?= PROYECTO_PATH ?>/aprendiz/leaderboard">VER TODO</a>
            </div>
            <p style="font-size:12px; color:var(--texto-tenue); margin:8px 0 12px;">
                Cuenta desde el lunes <?= htmlspecialchars($inicioSemana) ?>
            </p>
            <?php foreach (array_slice($leaderboard, 0, 3) as $i => $fila):
                    $esYo = $fila['id'] === ($usuario['id'] ?? '');
            ?>
                <div class="ranking-fila<?= $esYo ? ' ranking-yo' : '' ?>">
                    <span class="ranking-puesto"><?= $i + 1 ?></span>
                    <span class="ranking-nombre"><?= htmlspecialchars($fila['nombre_completo']) ?><?= $esYo ? ' (tú)' : '' ?></span>
                    <span class="ranking-xp"><?= (int) $fila['xp_semana'] ?> XP</span>
                </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
        <?php endif; ?>

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
  /* Ficha del módulo (botón GUÍA). Antes el botón existía pero no hacía nada:
     venía de la maqueta y nunca se le conectó contenido. Ahora resume de qué
     trata el módulo, qué RAPs cubre y qué se hace en cada momento. */
  const GUIA_MODULO = <?= json_encode([
      'nombre'      => $seccionActiva['nombre'],
      'descripcion' => $seccionActiva['descripcion'] ?? '',
      'progreso'    => round((float) $seccionActiva['progreso_promedio']),
      'raps'        => array_map(fn($r) => $r['titulo'], $seccionActiva['raps']),
  ], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;

  const MOMENTOS_GUIA = [
    ['fa-gamepad',     'Warm-Up',    'Repasas el vocabulario del módulo con un juego de emparejar.'],
    ['fa-book-reader', 'Absorption', 'Ves la explicación gramatical y el diálogo clínico en contexto.'],
    ['fa-dumbbell',    'Practice',   'Resuelves los ejercicios: completar, dictado, arrastrar y role-play.'],
    ['fa-flag-checkered', 'Quiz',    'Presentas la evaluación del RAP. Necesitas el puntaje mínimo para aprobarlo.']
  ];

  function verGuiaModulo() {
    const esc = (t) => String(t == null ? '' : t)
      .replace(/&/g, '&amp;').replace(/</g, '&lt;')
      .replace(/>/g, '&gt;').replace(/"/g, '&quot;');

    let html = '';

    if (GUIA_MODULO.descripcion) {
      html += `<p style="margin-bottom:16px;">${esc(GUIA_MODULO.descripcion)}</p>`;
    }

    if (GUIA_MODULO.raps && GUIA_MODULO.raps.length) {
      html += `<div style="font-size:.72rem;font-weight:900;text-transform:uppercase;letter-spacing:.05em;opacity:.7;margin-bottom:6px;">
                 Contenido del módulo</div>
               <ul style="margin:0 0 16px 18px;padding:0;list-style:disc;">` +
        GUIA_MODULO.raps.map(r => `<li style="margin-bottom:3px;">${esc(r)}</li>`).join('') +
        `</ul>`;
    }

    html += `<div style="font-size:.72rem;font-weight:900;text-transform:uppercase;letter-spacing:.05em;opacity:.7;margin-bottom:8px;">
               Cómo avanzas</div>`;
    html += MOMENTOS_GUIA.map(([icono, nombre, texto], i) => `
      <div style="display:flex;gap:11px;align-items:flex-start;margin-bottom:11px;">
        <div style="width:28px;height:28px;border-radius:50%;flex-shrink:0;background:var(--duo-green);
                    color:#fff;display:flex;align-items:center;justify-content:center;font-size:12px;">
          <i class="fas ${icono}"></i>
        </div>
        <div>
          <div style="font-weight:800;font-size:.86rem;">M${i + 1}: ${esc(nombre)}</div>
          <div style="font-size:.82rem;opacity:.85;">${esc(texto)}</div>
        </div>
      </div>`).join('');

    html += `<div style="margin-top:16px;padding-top:14px;border-top:1px solid var(--borde-sutil);
                         font-size:.84rem;font-weight:700;">
               Llevas ${GUIA_MODULO.progreso}% de este módulo
             </div>`;

    Avisos.dialogo({ titulo: GUIA_MODULO.nombre, html: html, boton: 'Cerrar' });
  }

  /* Mostrar mensaje cuando el aprendiz intenta acceder a un nivel bloqueado */
  function mostrarMensajeBloqueado(umbral, moduloBloqueado) {
    Avisos.dialogo({
      icono: 'fa-lock',
      color: 'var(--gris-medio)',
      titulo: moduloBloqueado ? 'Módulo bloqueado' : 'Aún no disponible',
      mensaje: moduloBloqueado
        ? `Completa el módulo anterior con al menos ${umbral}% de progreso para abrir este.`
        : 'Termina el momento anterior para continuar con este.',
      boton: 'Entendido'
    });
  }
</script>
<script src="<?= PROYECTO_PATH ?>/assets/js/tema.js"></script>
  <script src="<?= PROYECTO_PATH ?>/assets/js/avisos.js"></script>
</body>
</html>
