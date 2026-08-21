<!DOCTYPE html>
<html lang="es" data-theme="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Mi Perfil — SmashCode Enfermería SENA</title>
  <link rel="stylesheet" href="<?= PROYECTO_PATH ?>/assets/css/estilos.css?v=<?= time() ?>">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <script>(function(){var t=localStorage.getItem('smashcode_tema');if(t)document.documentElement.setAttribute('data-theme',t);})();</script>
  <link rel="stylesheet" href="<?= PROYECTO_PATH ?>/assets/css/layout.css?v=<?= time() ?>">
  <link rel="stylesheet" href="<?= PROYECTO_PATH ?>/assets/css/aprendiz.css?v=<?= time() ?>">
</head>
<body>
<div class="contenedor-app">
  <?php include dirname(__DIR__) . '/layouts/aprendiz_sidebar.php'; ?>

  <main class="contenido-principal">
    <div class="perfil-page">

      <?php
        $exito = limpiar($_GET['exito'] ?? '');
        $error = limpiar($_GET['error'] ?? '');
        $xp    = (int)($usuario['xp_puntos'] ?? 0);
        $inicialAvatar = strtoupper(substr($usuario['nombre_completo'] ?? 'A', 0, 1));
        
        $rango = $rangoClinico ?? [
            'nivel' => 1,
            'rango_nombre' => 'Novato Clínico',
            'rango_icono' => 'fa-shield-heart',
            'rango_color' => 'var(--azul)',
            'xp_actual' => $xp,
            'xp_siguiente_nivel' => 500,
            'xp_faltantes' => max(0, 500 - $xp),
            'porcentaje' => min(100, round(($xp / 500) * 100))
        ];

        $racha = (int)($rachaDias ?? 0);
        $quizzesAprobados = count(array_filter($historialQuizzes ?? [], fn($q) => (int)$q['aprobado'] === 1));
        // HU16: el callback de Google manda aquí con ?completar=1 a los aprendices sin ficha/programa
        $completar = ($_GET['completar'] ?? '') === '1';
      ?>

      <?php if ($completar): ?>
        <!-- ── AVISO: PERFIL INCOMPLETO (HU16) ── -->
        <div class="alerta-perfil" style="background: rgba(255,150,0,0.1); border-color: var(--naranja); color: var(--naranja); align-items: flex-start;">
          <i class="fas fa-triangle-exclamation" style="margin-top:2px;"></i>
          <div style="flex:1;">
            <div style="font-weight:800;">Completa tu ficha SENA y programa para continuar</div>
            <div style="font-weight:600; font-size:0.78rem; margin-top:4px; opacity:0.85;">
              Los necesitamos para ubicarte en tu grupo y en el leaderboard de tu ficha.
            </div>
          </div>
          <a href="#datos-formacion" style="flex-shrink:0; padding:8px 14px; border-radius:10px; background:var(--naranja); color:#fff; font-weight:800; font-size:0.75rem; text-transform:uppercase; letter-spacing:0.05em;">
            Completar
          </a>
        </div>
      <?php endif; ?>

      <!-- ── HÉROE DEL PERFIL (HU16 / HU15) ── -->
      <div class="hero-perfil">
        <div class="avatar-hero"><?= $inicialAvatar ?></div>
        <div class="hero-info">
          <h1><?= limpiar($usuario['nombre_completo'] ?? '') ?></h1>
          <p class="hero-correo">
            <i class="fas fa-envelope"></i>
            <?= limpiar($usuario['correo'] ?? '') ?>
          </p>

          <div class="hero-badges">
            <span class="hero-badge badge-rol"><i class="fas fa-user-graduate"></i>Aprendiz SENA</span>
            <?php if (!empty($usuario['ficha_sena'])): ?>
              <span class="hero-badge" style="background:rgba(28,176,246,0.12); color:var(--azul); border:1px solid rgba(28,176,246,0.3);">
                <i class="fas fa-id-card"></i> Ficha: <?= limpiar($usuario['ficha_sena']) ?>
              </span>
            <?php endif; ?>
            <?php if (!empty($usuario['programa_nombre'])): ?>
              <span class="hero-badge" style="background:rgba(155,89,182,0.12); color:var(--morado); border:1px solid rgba(155,89,182,0.3);">
                <i class="fas fa-book-medical"></i> <?= limpiar($usuario['programa_nombre']) ?>
              </span>
            <?php endif; ?>
            <span class="hero-badge badge-nivel" style="background:rgba(255,150,0,0.12); color:var(--naranja); border:1px solid rgba(255,150,0,0.3);">
              <i class="fas <?= $rango['rango_icono'] ?>"></i> <?= $rango['rango_nombre'] ?> (Nivel <?= $rango['nivel'] ?>)
            </span>
          </div>

          <div class="xp-section">
            <div class="xp-barra-wrap">
              <div class="xp-label">
                <span>Progreso al siguiente rango (<?= $rango['xp_faltantes'] ?> XP faltantes)</span>
                <span><?= number_format($rango['xp_actual']) ?> / <?= number_format($rango['xp_siguiente_nivel']) ?> XP</span>
              </div>
              <div class="xp-barra">
                <div class="xp-fill" style="width: <?= $rango['porcentaje'] ?>%"></div>
              </div>
            </div>
            <span style="font-size:1.6rem; font-weight:900; color:var(--verde); min-width:48px; text-align:right;"><?= $rango['porcentaje'] ?>%</span>
          </div>
        </div>
      </div>

      <!-- ── ESTADÍSTICAS RÁPIDAS (HU05 / HU15) ── -->
      <div class="config-card" style="padding: 20px 28px;">
        <div class="config-card-titulo"><i class="fas fa-chart-line" style="color:var(--azul);"></i>Mis Métricas Clínicas</div>
        <div class="stats-quick">
          <div class="stat-q">
            <div class="stat-q-val" style="color:var(--naranja);"><i class="fas fa-bolt" style="font-size:1.2rem; margin-right:4px;"></i><?= number_format($xp) ?></div>
            <div class="stat-q-lbl">Puntos XP Totales</div>
          </div>
          <div class="stat-q">
            <div class="stat-q-val" style="color:var(--rojo);"><i class="fas fa-fire" style="font-size:1.2rem; margin-right:4px;"></i><?= $racha ?> Días</div>
            <div class="stat-q-lbl">Racha Activa</div>
          </div>
          <div class="stat-q">
            <div class="stat-q-val" style="color:var(--verde);"><i class="fas fa-check-circle" style="font-size:1.2rem; margin-right:4px;"></i><?= $quizzesAprobados ?></div>
            <div class="stat-q-lbl">Quizzes Aprobados</div>
          </div>
          <div class="stat-q">
            <div class="stat-q-val" style="color:var(--azul);"><i class="fas <?= $rango['rango_icono'] ?>" style="font-size:1.2rem; margin-right:4px;"></i>Nivel <?= $rango['nivel'] ?></div>
            <div class="stat-q-lbl"><?= $rango['rango_nombre'] ?></div>
          </div>
          <div class="stat-q">
            <div class="stat-q-val" style="color:var(--morado);"><i class="fas fa-hourglass-half" style="font-size:1.2rem; margin-right:4px;"></i><?= formatearDuracion((int)($tiempoTotalSeg ?? 0)) ?></div>
            <div class="stat-q-lbl">Tiempo Total Invertido</div>
          </div>
        </div>
      </div>

      <!-- ── GRID DE INFORMACIÓN Y GAMIFICACIÓN ── -->
      <div class="config-grid">

        <!-- Avance por Módulo y por RAP (HU05) -->
        <div class="config-card" style="grid-column: span 2;">
          <div class="config-card-titulo"><i class="fas fa-diagram-project" style="color:var(--verde);"></i>Avance por Módulo y RAP</div>

          <?php if (empty($avanceModulos ?? [])): ?>
            <p style="font-size:0.85rem; color:var(--gris-medio); font-weight:600;">
              Todavía no hay módulos activos para mostrar.
            </p>
          <?php else: ?>
            <?php foreach ($avanceModulos as $mod):
              $pctMod = (float) $mod['porcentaje'];
              $colorMod = $pctMod >= 100 ? 'var(--verde)' : ($pctMod > 0 ? 'var(--azul)' : 'var(--gris-medio)');
            ?>
              <div style="margin-bottom:18px;">
                <div style="display:flex; justify-content:space-between; align-items:baseline; gap:12px; margin-bottom:6px;">
                  <span style="font-weight:800; font-size:0.9rem;"><?= limpiar($mod['nombre']) ?></span>
                  <span style="font-weight:900; font-size:0.9rem; color:<?= $colorMod ?>;">
                    <?= number_format($pctMod, 0) ?>%
                    <span style="font-weight:700; font-size:0.72rem; color:var(--gris-medio);">
                      (<?= (int) $mod['raps_completados'] ?>/<?= (int) $mod['total_raps'] ?> RAPs<?= $mod['tiempo_seg'] > 0 ? ' · ' . formatearDuracion((int) $mod['tiempo_seg']) : '' ?>)
                    </span>
                  </span>
                </div>

                <div class="xp-barra" style="height:12px;">
                  <div class="xp-fill" style="width: <?= min(100, $pctMod) ?>%; background: <?= $colorMod ?>;"></div>
                </div>

                <div style="margin-top:8px; display:flex; flex-direction:column; gap:6px;">
                  <?php foreach ($mod['raps'] as $rap):
                    $pctRap = (float) $rap['porcentaje'];
                    $colorRap = $rap['completado'] ? 'var(--verde)' : ($pctRap > 0 ? 'var(--naranja)' : 'var(--gris-claro)');
                  ?>
                    <div style="display:flex; align-items:center; gap:10px; font-size:0.78rem;">
                      <i class="fas <?= $rap['completado'] ? 'fa-circle-check' : ($pctRap > 0 ? 'fa-circle-half-stroke' : 'fa-circle') ?>"
                         style="color:<?= $colorRap ?>; font-size:0.85rem;"></i>
                      <span style="flex:1; font-weight:600;"><?= limpiar($rap['titulo']) ?></span>
                      <?php if ($rap['mejor_puntaje_quiz'] > 0): ?>
                        <span style="font-weight:700; color:var(--morado);" title="Mejor puntaje de quiz">
                          <?= number_format((float) $rap['mejor_puntaje_quiz'], 0) ?>% quiz
                        </span>
                      <?php endif; ?>
                      <span style="font-weight:800; color:<?= $colorRap ?>; min-width:38px; text-align:right;">
                        <?= number_format($pctRap, 0) ?>%
                      </span>
                    </div>
                  <?php endforeach; ?>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>

        <!-- Leaderboard Semanal de Ficha (HU15) -->
        <div class="config-card">
          <div class="config-card-titulo"><i class="fas fa-ranking-star" style="color:var(--azul);"></i>Leaderboard Semanal de Programa / Ficha</div>
          <?php
            // HU15: el marcador cuenta desde el lunes; el ranking se reinicia solo al cambiar de semana ISO
            $miPosicion = 0;
            foreach ($leaderboard as $i => $fila) {
                if ($fila['id'] === $_SESSION['usuario_id']) { $miPosicion = $i + 1; break; }
            }
          ?>
          <p style="font-size:0.75rem; color:var(--texto-tenue); margin-bottom:12px;">
            Cuenta desde el lunes <?= date('d/m/Y', strtotime($inicioSemana ?? 'monday this week')) ?>.
            <?php if ($miPosicion > 0): ?>
              Vas en la posición <strong style="color:var(--verde);">#<?= $miPosicion ?></strong> de tu ficha.
            <?php endif; ?>
          </p>
          <?php if (empty($leaderboard)): ?>
            <p style="color:var(--texto-tenue); font-size:0.85rem; text-align:center; padding:20px;">Aún no hay actividad registrada en tu programa esta semana.</p>
          <?php else: ?>
            <div style="display:flex; flex-direction:column; gap:8px;">
              <?php foreach ($leaderboard as $pos => $uRank): 
                $isMe = ($uRank['id'] === $_SESSION['usuario_id']);
                $medalla = '';
                if ($pos === 0) $medalla = '🥇';
                elseif ($pos === 1) $medalla = '🥈';
                elseif ($pos === 2) $medalla = '🥉';
              ?>
                <div style="display:flex; align-items:center; justify-content:space-between; padding:10px 14px; border-radius:12px; border:2px solid <?= $isMe ? 'var(--verde)' : 'var(--gris-claro)' ?>; background: <?= $isMe ? 'rgba(88,204,2,0.1)' : 'var(--fondo)' ?>; transition:all 0.2s;">
                  <div style="display:flex; align-items:center; gap:10px;">
                    <span style="font-weight:900; width:24px; text-align:center; font-size:0.95rem; color:var(--gris-medio);"><?= $medalla ?: '#' . ($pos + 1) ?></span>
                    <div>
                      <span style="font-weight:<?= $isMe ? '800' : '700' ?>; font-size:0.88rem; color:var(--gris-texto); display:block;">
                        <?= htmlspecialchars($uRank['nombre_completo']) ?> <?= $isMe ? '<small style="color:var(--verde); font-weight:800;">(Tú)</small>' : '' ?>
                      </span>
                      <small style="font-size:0.72rem; color:var(--texto-tenue);">Nivel <?= (int)$uRank['nivel_perfil'] ?></small>
                    </div>
                  </div>
                  <span style="font-weight:800; font-size:0.9rem; color:var(--naranja); text-align:right;">
                    <i class="fas fa-bolt" style="font-size:0.75rem;"></i> <?= number_format((int)($uRank['xp_semana'] ?? 0)) ?> XP
                    <small style="display:block; font-size:0.68rem; font-weight:700; color:var(--texto-tenue);">
                      esta semana · <?= number_format($uRank['xp_puntos']) ?> total
                    </small>
                  </span>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>

        <!-- Heatmap de Actividad (HU05 / HU15) -->
        <div class="config-card">
          <div class="config-card-titulo"><i class="fas fa-calendar-check" style="color:var(--verde);"></i>Constancia de Estudio (Últimos 90 Días)</div>
          <p style="font-size:0.78rem; color:var(--texto-tenue); margin-bottom:12px;">Visualiza tu constancia y días activos de práctica en SmashCode.</p>
          
          <div style="display:grid; grid-template-columns: repeat(13, 1fr); gap:6px;">
            <?php 
              $today = new DateTime();
              $startDate = (clone $today)->modify('-90 days');
              $dateList = [];
              for ($i = 0; $i <= 90; $i++) {
                  $dateStr = $startDate->format('Y-m-d');
                  $dateList[] = $dateStr;
                  $startDate->modify('+1 day');
              }
              foreach ($dateList as $dVal):
                  $isActiveDay = in_array($dVal, $heatmapActivo ?? []);
            ?>
              <div style="aspect-ratio:1; border-radius:4px; background: <?= $isActiveDay ? 'var(--verde)' : 'var(--gris-claro)' ?>; border: 1px solid <?= $isActiveDay ? 'var(--verde-oscuro)' : 'transparent' ?>; box-shadow: <?= $isActiveDay ? '0 0 6px rgba(88,204,2,0.5)' : 'none' ?>;" 
                   title="<?= date('d/m/Y', strtotime($dVal)) . ($isActiveDay ? ' • ¡Día de práctica activo!' : ' • Sin actividad') ?>">
              </div>
            <?php endforeach; ?>
          </div>

          <div style="display:flex; align-items:center; justify-content:space-between; margin-top:14px; font-size:0.75rem; color:var(--gris-medio);">
            <span>🔥 Racha actual: <strong style="color:var(--rojo);"><?= $racha ?> días consecutivos</strong></span>
            <div style="display:flex; align-items:center; gap:6px;">
              <span>Menos</span>
              <div style="width:10px; height:10px; border-radius:2px; background:var(--gris-claro);"></div>
              <div style="width:10px; height:10px; border-radius:2px; background:var(--verde);"></div>
              <span>Más</span>
            </div>
          </div>
        </div>

        <!-- Colección de Insignias y Logros (HU07) -->
        <div class="config-card" style="grid-column: span 2;">
          <div class="config-card-titulo"><i class="fas fa-medal" style="color:var(--naranja);"></i>Colección de Insignias y Logros Clínicos</div>
          <div style="display:flex; gap:24px; flex-wrap:wrap; margin-top:14px; justify-content:center;">
            <?php 
              $descInsigniasMap = [
                'Primer Nivel' => 'Completaste con éxito tu primer nivel',
                'Racha 7 Días' => '7 días consecutivos de práctica clínica',
                'Quiz Perfecto' => 'Obtuviste 100% de aciertos en un quiz',
                'Vocabulario Pro' => 'Aprendiste 50 términos médicos',
                'Estudiante Élite' => 'Completaste todos los módulos del curso'
              ];

              $earnedIds = array_column($insigniasGanadas ?? [], 'id');
              foreach ($todasInsignias as $insig):
                  $hasEarned = in_array($insig['id'], $earnedIds);
                  $rawNombre = $insig['nombre'];
                  
                  if (mb_stripos($rawNombre, 'Racha') !== false || mb_stripos($rawNombre, '7') !== false) {
                      $cleanNombre = 'Racha 7 Días';
                  } elseif (mb_stripos($rawNombre, 'Primer') !== false) {
                      $cleanNombre = 'Primer Nivel';
                  } elseif (mb_stripos($rawNombre, 'Perfecto') !== false) {
                      $cleanNombre = 'Quiz Perfecto';
                  } elseif (mb_stripos($rawNombre, 'Vocabulario') !== false) {
                      $cleanNombre = 'Vocabulario Pro';
                  } elseif (mb_stripos($rawNombre, 'lite') !== false || mb_stripos($rawNombre, 'Élite') !== false || mb_stripos($rawNombre, 'Elite') !== false) {
                      $cleanNombre = 'Estudiante Élite';
                  } else {
                      $cleanNombre = htmlspecialchars($rawNombre);
                  }

                  $cleanDesc = $descInsigniasMap[$cleanNombre] ?? htmlspecialchars($insig['descripcion']);
            ?>
              <div style="display:flex; flex-direction:column; align-items:center; width:120px; text-align:center; opacity: <?= $hasEarned ? '1' : '0.35' ?>; filter: <?= $hasEarned ? 'none' : 'grayscale(100%)' ?>; transition:all 0.2s;">
                <div style="width:68px; height:68px; border-radius:50%; background:var(--fondo); border:3px solid <?= $hasEarned ? 'var(--naranja)' : 'var(--gris-claro)' ?>; display:flex; align-items:center; justify-content:center; font-size:1.8rem; color:var(--naranja); box-shadow: <?= $hasEarned ? '0 4px 12px rgba(255,150,0,0.25)' : 'none' ?>;">
                  <?php if ($cleanNombre === 'Quiz Perfecto'): ?>
                    <i class="fas fa-trophy"></i>
                  <?php elseif ($cleanNombre === 'Primer Nivel'): ?>
                    <i class="fas fa-star"></i>
                  <?php elseif ($cleanNombre === 'Racha 7 Días'): ?>
                    <i class="fas fa-fire"></i>
                  <?php elseif ($cleanNombre === 'Vocabulario Pro'): ?>
                    <i class="fas fa-book-medical"></i>
                  <?php else: ?>
                    <i class="fas fa-award"></i>
                  <?php endif; ?>
                </div>
                <div style="font-size:0.82rem; font-weight:800; margin-top:8px; color:var(--gris-texto);"><?= $cleanNombre ?></div>
                <div style="font-size:0.68rem; color:var(--texto-tenue); margin-top:2px; line-height:1.2;"><?= $cleanDesc ?></div>
                <?php if ($hasEarned): ?>
                  <span style="display:inline-block; margin-top:4px; font-size:0.65rem; font-weight:800; color:var(--verde);"><i class="fas fa-check"></i> Desbloqueada</span>
                <?php else: ?>
                  <span style="display:inline-block; margin-top:4px; font-size:0.65rem; color:var(--texto-tenue);"><i class="fas fa-lock"></i> Bloqueada</span>
                <?php endif; ?>
              </div>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- Evolución de puntajes entre sesiones (HU15) -->
        <div class="config-card" style="grid-column: span 2;">
          <div class="config-card-titulo"><i class="fas fa-chart-line" style="color:var(--morado);"></i>Evolución de mis Puntajes</div>

          <?php
            // Del más antiguo al más reciente: el historial llega ordenado al revés
            $serie = array_reverse($historialQuizzes ?? []);
            // Con un solo intento no hay evolución que comparar todavía
            $hayGrafica = count($serie) >= 2;
          ?>

          <?php if (!$hayGrafica): ?>
            <p style="font-size:0.85rem; color:var(--gris-medio); font-weight:600;">
              Presenta al menos dos quizzes para ver cómo evolucionan tus puntajes entre sesiones.
            </p>
          <?php else: ?>
            <?php
              // Lienzo en coordenadas fijas; el SVG escala solo con viewBox
              $ancho = 640; $alto = 220;
              $margenIzq = 38; $margenDer = 12; $margenSup = 14; $margenInf = 34;
              $areaAncho = $ancho - $margenIzq - $margenDer;
              $areaAlto  = $alto - $margenSup - $margenInf;
              $n = count($serie);

              $puntos = [];
              foreach ($serie as $i => $intento) {
                  $valor = (float) $intento['puntaje'];
                  $x = $margenIzq + ($n === 1 ? $areaAncho / 2 : ($areaAncho * $i / ($n - 1)));
                  $y = $margenSup + $areaAlto * (1 - min(100, max(0, $valor)) / 100);
                  $puntos[] = ['x' => round($x, 1), 'y' => round($y, 1), 'valor' => $valor, 'intento' => $intento];
              }

              $polilinea = implode(' ', array_map(fn($p) => $p['x'] . ',' . $p['y'], $puntos));
              $promedio  = array_sum(array_column($puntos, 'valor')) / $n;
              $mejor     = max(array_column($puntos, 'valor'));
              $ultimo    = end($puntos)['valor'];
              $primero   = $puntos[0]['valor'];
              $delta     = $ultimo - $primero;
            ?>

            <div style="display:flex; gap:18px; flex-wrap:wrap; margin-bottom:10px; font-size:0.78rem; font-weight:700;">
              <span style="color:var(--gris-medio);">Sesiones: <strong style="color:var(--gris-texto);"><?= $n ?></strong></span>
              <span style="color:var(--gris-medio);">Promedio: <strong style="color:var(--azul);"><?= number_format($promedio, 0) ?>%</strong></span>
              <span style="color:var(--gris-medio);">Mejor: <strong style="color:var(--verde);"><?= number_format($mejor, 0) ?>%</strong></span>
              <span style="color:var(--gris-medio);">
                Desde la primera:
                <strong style="color:<?= $delta >= 0 ? 'var(--verde)' : 'var(--rojo)' ?>;">
                  <?= $delta >= 0 ? '+' : '' ?><?= number_format($delta, 0) ?> pts
                </strong>
              </span>
            </div>

            <div style="overflow-x:auto;">
              <svg viewBox="0 0 <?= $ancho ?> <?= $alto ?>" width="100%" height="220" role="img"
                   aria-label="Gráfica de la evolución de los puntajes de quiz entre sesiones">
                <!-- Rejilla horizontal cada 25% -->
                <?php foreach ([0, 25, 50, 75, 100] as $marca):
                  $y = round($margenSup + $areaAlto * (1 - $marca / 100), 1);
                ?>
                  <line x1="<?= $margenIzq ?>" y1="<?= $y ?>" x2="<?= $ancho - $margenDer ?>" y2="<?= $y ?>"
                        stroke="var(--gris-claro)" stroke-width="1" />
                  <text x="<?= $margenIzq - 6 ?>" y="<?= $y + 4 ?>" text-anchor="end"
                        font-size="10" font-weight="700" fill="var(--texto-tenue)"><?= $marca ?>%</text>
                <?php endforeach; ?>

                <!-- Puntaje mínimo para aprobar, tomado del quiz más reciente -->
                <?php
                  $minimoAprobar = (float) ($serie[$n - 1]['puntaje_minimo'] ?? 60);
                  $yMinimo = round($margenSup + $areaAlto * (1 - min(100, max(0, $minimoAprobar)) / 100), 1);
                ?>
                <line x1="<?= $margenIzq ?>" y1="<?= $yMinimo ?>" x2="<?= $ancho - $margenDer ?>" y2="<?= $yMinimo ?>"
                      stroke="var(--naranja)" stroke-width="1" stroke-dasharray="4 4" opacity="0.8" />

                <polyline points="<?= $polilinea ?>" fill="none" stroke="var(--morado)" stroke-width="3"
                          stroke-linejoin="round" stroke-linecap="round" />

                <?php foreach ($puntos as $p):
                  $aprobado = (int) $p['intento']['aprobado'] === 1;
                ?>
                  <circle cx="<?= $p['x'] ?>" cy="<?= $p['y'] ?>" r="5"
                          fill="<?= $aprobado ? 'var(--verde)' : 'var(--rojo)' ?>"
                          stroke="var(--fondo)" stroke-width="2">
                    <title><?= limpiar($p['intento']['rap_titulo']) ?> — <?= number_format($p['valor'], 0) ?>% — <?= date('d/m/Y', strtotime($p['intento']['creado_en'])) ?></title>
                  </circle>
                <?php endforeach; ?>

                <!-- Fechas del primer y del último intento -->
                <text x="<?= $margenIzq ?>" y="<?= $alto - 10 ?>" font-size="10" font-weight="700" fill="var(--texto-tenue)">
                  <?= date('d/m/y', strtotime($serie[0]['creado_en'])) ?>
                </text>
                <text x="<?= $ancho - $margenDer ?>" y="<?= $alto - 10 ?>" text-anchor="end"
                      font-size="10" font-weight="700" fill="var(--texto-tenue)">
                  <?= date('d/m/y', strtotime($serie[$n - 1]['creado_en'])) ?>
                </text>
              </svg>
            </div>

            <p style="font-size:0.72rem; color:var(--texto-tenue); margin-top:6px;">
              Cada punto es un intento de quiz: verde si lo aprobaste, rojo si no. La línea naranja punteada es el mínimo para aprobar (<?= number_format($minimoAprobar, 0) ?>%).
            </p>
          <?php endif; ?>
        </div>

        <!-- Historial de Evaluaciones y Quizzes (HU05) -->
        <div class="config-card" style="grid-column: span 2;">
          <div class="config-card-titulo"><i class="fas fa-clock-rotate-left" style="color:var(--morado);"></i>Historial de Evaluaciones y Quizzes</div>
          <?php if (empty($historialQuizzes)): ?>
            <p style="color:var(--texto-tenue); text-align:center; padding: 24px; font-size:0.88rem;">No has completado ninguna evaluación final de RAP todavía.</p>
          <?php else: ?>
            <div style="overflow-x:auto;">
              <table class="vocab-table" style="width:100%; border:none;">
                <thead>
                  <tr>
                    <th style="padding:12px;">Módulo / Resultado de Aprendizaje</th>
                    <th style="padding:12px; text-align:center;">Puntaje</th>
                    <th style="padding:12px; text-align:center;">Resultado</th>
                    <th style="padding:12px; text-align:center;">Intento</th>
                    <th style="padding:12px; text-align:right;">Fecha</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($historialQuizzes as $q): 
                      $moduloOrden = (int)($q['modulo_orden'] ?? 1);
                      $rapsSubtextMap = [
                          1 => 'RAP 1: Presentaciones e Información Personal',
                          2 => 'RAP 2 y RAP 3: Historia del Paciente y Entorno Hospitalario',
                          3 => 'RAP 4 y RAP 5: Interacción con Visitantes y Lista de Chequeo',
                          4 => 'RAP 6: Práctica Profesional e Instrucciones de Alta'
                      ];
                      $subtexto = $rapsSubtextMap[$moduloOrden] ?? limpiar($q['rap_titulo']);
                  ?>
                    <tr>
                      <td style="padding:12px; font-weight:700;">
                        <div><?= !empty($q['modulo_nombre']) ? limpiar($q['modulo_nombre']) : limpiar($q['rap_titulo']) ?></div>
                        <div style="font-size:0.75rem; font-weight:600; color:var(--texto-tenue); margin-top:2px;"><?= $subtexto ?></div>
                      </td>
                      <td style="padding:12px; text-align:center; font-weight:800; font-size:1rem; color:<?= $q['aprobado'] ? 'var(--verde)' : 'var(--rojo)' ?>;"><?= (int)$q['puntaje'] ?>%</td>
                      <td style="padding:12px; text-align:center;">
                        <span class="tag-badge <?= $q['aprobado'] ? 'nivel' : 'cat' ?>" style="font-size:0.7rem; display:inline-block; padding:4px 10px; font-weight:800;">
                          <?= $q['aprobado'] ? 'APROBADO' : 'REPROBADO' ?>
                        </span>
                      </td>
                      <td style="padding:12px; text-align:center; font-weight:700;">#<?= $q['numero_intento'] ?></td>
                      <td style="padding:12px; text-align:right; font-size:0.82rem; color:var(--texto-tenue);"><?= date('d/m/Y H:i', strtotime($q['creado_en'])) ?></td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php endif; ?>
        </div>

        <!-- Datos Personales (HU16) -->
        <div class="config-card">
          <div class="config-card-titulo"><i class="fas fa-user-pen" style="color:var(--verde);"></i>Datos del Aprendiz</div>

          <?php if ($exito === 'nombre'): ?>
            <div class="alerta-perfil alerta-ok"><i class="fas fa-check-circle"></i>Nombre actualizado correctamente.</div>
          <?php endif; ?>
          <?php if ($error === 'nombre'): ?>
            <div class="alerta-perfil alerta-err"><i class="fas fa-exclamation-circle"></i>El nombre no puede estar vacío.</div>
          <?php endif; ?>

          <form method="POST" action="<?= PROYECTO_PATH ?>/aprendiz/perfil/actualizar">
            <input type="hidden" name="csrf_token" value="<?= generarTokenCSRF() ?>">
            <input type="hidden" name="accion" value="nombre">

            <div class="campo-grupo">
              <label class="campo-label" for="nombre_completo">Nombre completo</label>
              <div class="campo-wrap">
                <i class="fas fa-user campo-ico"></i>
                <input type="text" id="nombre_completo" name="nombre_completo" class="campo-input-perfil"
                       value="<?= limpiar($usuario['nombre_completo'] ?? '') ?>" required maxlength="100">
              </div>
            </div>

            <div class="campo-grupo">
              <label class="campo-label">Correo institucional</label>
              <div class="campo-wrap">
                <i class="fas fa-envelope campo-ico"></i>
                <input type="email" class="campo-input-perfil"
                       value="<?= limpiar($usuario['correo'] ?? '') ?>" disabled>
              </div>
              <small style="color:var(--gris-medio); font-size:0.72rem; margin-top:4px; display:block;">
                <i class="fas fa-lock" style="margin-right:4px;"></i>El correo solo puede ser modificado por un administrador.
              </small>
            </div>

            <button type="submit" class="btn-guardar">
              <i class="fas fa-save"></i> Guardar Nombre
            </button>
          </form>
        </div>

        <!-- Datos de Formación (HU16) -->
        <div class="config-card" id="datos-formacion"
             <?php if ($completar): ?>style="order:-1; grid-column: span 2; border-color: var(--naranja); box-shadow: 0 0 0 4px rgba(255,150,0,0.15);"<?php endif; ?>>
          <div class="config-card-titulo"><i class="fas fa-id-card" style="color:var(--naranja);"></i>Datos de Formación</div>

          <?php if ($exito === 'ficha'): ?>
            <div class="alerta-perfil alerta-ok"><i class="fas fa-check-circle"></i>Datos de formación actualizados correctamente.</div>
          <?php endif; ?>
          <?php if ($error === 'ficha'): ?>
            <div class="alerta-perfil alerta-err"><i class="fas fa-exclamation-circle"></i>La ficha SENA no puede estar vacía.</div>
          <?php endif; ?>
          <?php if ($error === 'programa'): ?>
            <div class="alerta-perfil alerta-err"><i class="fas fa-exclamation-circle"></i>Selecciona un programa de formación válido.</div>
          <?php endif; ?>
          <?php if ($error === 'ficha_guardar'): ?>
            <div class="alerta-perfil alerta-err"><i class="fas fa-exclamation-circle"></i>No pudimos guardar tus datos de formación. Intenta de nuevo.</div>
          <?php endif; ?>

          <form method="POST" action="<?= PROYECTO_PATH ?>/aprendiz/perfil/actualizar">
            <input type="hidden" name="csrf_token" value="<?= generarTokenCSRF() ?>">
            <input type="hidden" name="accion" value="ficha">
            <?php if ($completar): ?>
              <input type="hidden" name="completar" value="1">
            <?php endif; ?>

            <div class="campo-grupo">
              <label class="campo-label" for="ficha_sena">Ficha SENA</label>
              <div class="campo-wrap">
                <i class="fas fa-id-card campo-ico"></i>
                <input type="text" id="ficha_sena" name="ficha_sena" class="campo-input-perfil"
                       value="<?= limpiar($fichaSena ?? '') ?>" placeholder="p. ej. 2234891" required maxlength="50">
              </div>
              <small style="color:var(--gris-medio); font-size:0.72rem; margin-top:4px; display:block;">
                Número de la ficha en la que estás matriculado.
              </small>
            </div>

            <div class="campo-grupo">
              <label class="campo-label" for="programa_id">Programa de formación</label>
              <div class="campo-wrap">
                <i class="fas fa-graduation-cap campo-ico"></i>
                <select id="programa_id" name="programa_id" class="campo-input-perfil" required>
                  <option value="">Selecciona tu programa</option>
                  <?php foreach (($programas ?? []) as $p): ?>
                    <option value="<?= limpiar($p['id']) ?>" <?= ($programaId ?? '') === $p['id'] ? 'selected' : '' ?>>
                      <?= limpiar($p['nombre']) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>

            <button type="submit" class="btn-guardar" style="background:var(--naranja); box-shadow:0 4px 0 #C87700;">
              <i class="fas fa-save"></i> Guardar Datos de Formación
            </button>
          </form>
        </div>

        <!-- Seguridad y Contraseña (HU08) -->
        <div class="config-card">
          <div class="config-card-titulo"><i class="fas fa-shield-halved" style="color:var(--azul);"></i>Seguridad y Contraseña</div>

          <?php if ($exito === 'clave'): ?>
            <div class="alerta-perfil alerta-ok"><i class="fas fa-check-circle"></i>Contraseña actualizada correctamente.</div>
          <?php endif; ?>
          <?php if ($error === 'clave_actual'): ?>
            <div class="alerta-perfil alerta-err"><i class="fas fa-exclamation-circle"></i>La contraseña actual es incorrecta.</div>
          <?php endif; ?>
          <?php if ($error === 'clave_corta'): ?>
            <div class="alerta-perfil alerta-err"><i class="fas fa-exclamation-circle"></i>La nueva contraseña debe tener mínimo 8 caracteres.</div>
          <?php endif; ?>
          <?php if ($error === 'clave_no_coincide'): ?>
            <div class="alerta-perfil alerta-err"><i class="fas fa-exclamation-circle"></i>Las contraseñas nuevas no coinciden.</div>
          <?php endif; ?>

          <form method="POST" action="<?= PROYECTO_PATH ?>/aprendiz/perfil/actualizar">
            <input type="hidden" name="csrf_token" value="<?= generarTokenCSRF() ?>">
            <input type="hidden" name="accion" value="clave">

            <div class="campo-grupo">
              <label class="campo-label" for="clave_actual">Contraseña actual</label>
              <div class="campo-wrap">
                <i class="fas fa-lock campo-ico"></i>
                <input type="password" id="clave_actual" name="clave_actual" class="campo-input-perfil" placeholder="••••••••" required autocomplete="current-password">
              </div>
            </div>

            <div class="campo-grupo">
              <label class="campo-label" for="clave_nueva">Nueva contraseña</label>
              <div class="campo-wrap">
                <i class="fas fa-key campo-ico"></i>
                <input type="password" id="clave_nueva" name="clave_nueva" class="campo-input-perfil" placeholder="Mín. 8 caracteres" required minlength="8" autocomplete="new-password">
              </div>
            </div>

            <div class="campo-grupo">
              <label class="campo-label" for="clave_confirmar">Confirmar nueva contraseña</label>
              <div class="campo-wrap">
                <i class="fas fa-check-double campo-ico"></i>
                <input type="password" id="clave_confirmar" name="clave_confirmar" class="campo-input-perfil" placeholder="Repite la nueva contraseña" required minlength="8" autocomplete="new-password">
              </div>
            </div>

            <button type="submit" class="btn-guardar" style="background:var(--azul); box-shadow:0 4px 0 var(--azul-oscuro);">
              <i class="fas fa-lock"></i> Actualizar Contraseña
            </button>
          </form>
        </div>

        <!-- Preferencias de Apariencia -->
        <div class="config-card">
          <div class="config-card-titulo"><i class="fas fa-palette" style="color:var(--morado);"></i>Preferencias de Apariencia</div>

          <div class="campo-grupo">
            <label class="campo-label">Tema de la interfaz</label>
            <div style="display:flex; gap:12px; margin-top:6px;">
              <button type="button" onclick="cambiarTema('dark')" id="btn-tema-oscuro" class="btn-tema-opcion">
                <i class="fas fa-moon"></i> <span>Oscuro</span>
              </button>
              <button type="button" onclick="cambiarTema('light')" id="btn-tema-claro" class="btn-tema-opcion">
                <i class="fas fa-sun"></i> <span>Claro</span>
              </button>
            </div>
          </div>

          <div class="divider"></div>

          <div style="display:flex; align-items:center; justify-content:space-between; padding:12px 0;">
            <div>
              <div style="font-size:0.85rem; font-weight:700; color:var(--gris-texto);">Sonidos de la app</div>
              <div style="font-size:0.72rem; color:var(--gris-medio); margin-top:2px;">Efectos de sonido al completar lecciones y quizzes</div>
            </div>
            <label class="toggle-switch" style="position:relative; display:inline-block; width:46px; height:26px; cursor:pointer;">
              <input type="checkbox" id="toggle-sonido" style="opacity:0; width:0; height:0;" onchange="toggleSonido(this)">
              <span style="position:absolute; top:0; left:0; right:0; bottom:0; background:var(--gris-claro); border-radius:34px; transition:all 0.3s ease;"
                    id="toggle-sonido-track">
                <span style="position:absolute; height:20px; width:20px; left:3px; bottom:3px; background:#fff; border-radius:50%; box-shadow:0 2px 5px rgba(0,0,0,0.2); transition:all 0.3s cubic-bezier(0.4, 0, 0.2, 1);" id="toggle-sonido-thumb"></span>
              </span>
            </label>
          </div>
        </div>

        <!-- Zona de Riesgo -->
        <div class="config-card danger-zone">
          <div class="config-card-titulo"><i class="fas fa-triangle-exclamation"></i>Zona de Riesgo</div>
          <p style="font-size:0.85rem; color:var(--gris-medio); margin-bottom:20px; line-height:1.6;">
            Al cerrar sesión perderás acceso temporal hasta que ingreses tus credenciales nuevamente. Tu progreso y XP siempre se guardan automáticamente.
          </p>
          <a href="<?= PROYECTO_PATH ?>/logout"
             style="display:flex; align-items:center; justify-content:center; gap:10px; width:100%; padding:12px; border-radius:12px;
                    background: rgba(255,75,75,0.1); color:var(--rojo); border:2px solid rgba(255,75,75,0.3);
                    font-weight:800; font-size:0.9rem; text-decoration:none; text-transform:uppercase; letter-spacing:0.05em;
                    transition:all 0.2s;"
             onmouseover="this.style.background='rgba(255,75,75,0.2)'"
             onmouseout="this.style.background='rgba(255,75,75,0.1)'">
            <i class="fas fa-right-from-bracket"></i> Cerrar Sesión
          </a>
        </div>

      </div><!-- /config-grid -->
    </div><!-- /perfil-page -->
  </main>
</div>

<style>
  .btn-tema-opcion {
    flex: 1;
    padding: 10px 14px;
    border-radius: 10px;
    border: 2px solid var(--gris-claro);
    background: var(--fondo);
    color: var(--gris-texto);
    font-family: var(--fuente);
    font-weight: 800;
    cursor: pointer;
    font-size: 0.84rem;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
  }
  .btn-tema-opcion:hover {
    border-color: var(--verde);
  }
  .btn-tema-opcion.activo {
    border-color: var(--verde) !important;
    color: var(--verde) !important;
    background: rgba(88, 204, 2, 0.12) !important;
    box-shadow: 0 0 10px rgba(88, 204, 2, 0.2);
  }
</style>

<script src="<?= PROYECTO_PATH ?>/assets/js/tema.js"></script>
<script src="<?= PROYECTO_PATH ?>/assets/js/sonidos.js"></script>
<script>
  function actualizarBotonesTema() {
    const tema = document.documentElement.getAttribute('data-theme') || 'dark';
    const btnOscuro = document.getElementById('btn-tema-oscuro');
    const btnClaro  = document.getElementById('btn-tema-claro');
    if (!btnOscuro || !btnClaro) return;

    if (tema === 'dark') {
      btnOscuro.classList.add('activo');
      btnClaro.classList.remove('activo');
    } else {
      btnClaro.classList.add('activo');
      btnOscuro.classList.remove('activo');
    }
  }

  function cambiarTema(nuevoTema) {
    document.documentElement.setAttribute('data-theme', nuevoTema);
    localStorage.setItem('smashcode_tema', nuevoTema);
    const btnSidebar = document.getElementById('btn-cambiar-tema');
    if (btnSidebar) {
      const ico = btnSidebar.querySelector('.tema-icono');
      const lbl = btnSidebar.querySelector('.tema-label');
      if (nuevoTema === 'light') {
        if (ico) ico.className = 'fas fa-moon tema-icono';
        if (lbl) lbl.textContent = 'Oscuro';
      } else {
        if (ico) ico.className = 'fas fa-sun tema-icono';
        if (lbl) lbl.textContent = 'Claro';
      }
    }
    actualizarBotonesTema();
  }

  function toggleSonido(cb) {
    const track = document.getElementById('toggle-sonido-track');
    const thumb = document.getElementById('toggle-sonido-thumb');
    const habilitado = cb.checked;
    
    if (habilitado) {
      track.style.background = 'var(--verde)';
      thumb.style.transform = 'translateX(20px)';
      SonidosApp.setHabilitado(true);
      SonidosApp.playToggle(true);
    } else {
      track.style.background = 'var(--gris-claro)';
      thumb.style.transform = 'translateX(0)';
      SonidosApp.playToggle(false);
      SonidosApp.setHabilitado(false);
    }
  }

  document.addEventListener('DOMContentLoaded', function() {
    actualizarBotonesTema();
    const habilitado = SonidosApp.estaHabilitado();
    const cbSonido = document.getElementById('toggle-sonido');
    const track = document.getElementById('toggle-sonido-track');
    const thumb = document.getElementById('toggle-sonido-thumb');
    
    if (cbSonido) {
      cbSonido.checked = habilitado;
      if (habilitado) {
        track.style.background = 'var(--verde)';
        thumb.style.transform = 'translateX(20px)';
      } else {
        track.style.background = 'var(--gris-claro)';
        thumb.style.transform = 'translateX(0)';
      }
    }
  });
</script>
</body>
</html>
