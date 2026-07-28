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
        $nivel = (int)($usuario['nivel_perfil'] ?? 1);
        $xpSiguiente = $nivel * 500;
        $xpPorcentaje = $xpSiguiente > 0 ? min(100, round(($xp / $xpSiguiente) * 100)) : 100;
        $inicialAvatar = strtoupper(substr($usuario['nombre_completo'] ?? 'A', 0, 1));
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

      <!-- ── HÉROE DEL PERFIL ── -->
      <div class="hero-perfil">
        <div class="avatar-hero"><?= $inicialAvatar ?></div>
        <div class="hero-info">
          <h1><?= limpiar($usuario['nombre_completo'] ?? '') ?></h1>
          <p class="hero-correo">
            <i class="fas fa-envelope"></i>
            <?= limpiar($usuario['correo'] ?? '') ?>
          </p>
          <div class="hero-badges">
            <span class="hero-badge badge-xp"><i class="fas fa-bolt"></i><?= number_format($xp) ?> XP</span>
            <span class="hero-badge badge-nivel"><i class="fas fa-star"></i>Nivel <?= $nivel ?></span>
            <span class="hero-badge badge-rol"><i class="fas fa-user-graduate"></i>Aprendiz</span>
          </div>
          <div class="xp-section">
            <div class="xp-barra-wrap">
              <div class="xp-label">
                <span>Progreso al siguiente nivel</span>
                <span><?= $xp ?> / <?= $xpSiguiente ?> XP</span>
              </div>
              <div class="xp-barra">
                <div class="xp-fill" style="width: <?= $xpPorcentaje ?>%"></div>
              </div>
            </div>
            <span style="font-size:1.6rem; font-weight:900; color:var(--verde); min-width:48px; text-align:right;"><?= $xpPorcentaje ?>%</span>
          </div>
        </div>
      </div>

      <!-- ── ESTADÍSTICAS RÁPIDAS ── -->
      <div class="config-card" style="padding: 20px 28px;">
        <div class="config-card-titulo"><i class="fas fa-chart-bar" style="color:var(--azul);"></i>Mis Estadísticas</div>
        <div class="stats-quick">
          <div class="stat-q">
            <div class="stat-q-val" style="color:var(--naranja);"><?= number_format($xp) ?></div>
            <div class="stat-q-lbl">XP Total</div>
          </div>
          <div class="stat-q">
            <div class="stat-q-val" style="color:var(--verde);"><?= $nivel ?></div>
            <div class="stat-q-lbl">Nivel Actual</div>
          </div>
          <div class="stat-q">
            <div class="stat-q-val" style="color:var(--azul);"><?= $xpPorcentaje ?>%</div>
            <div class="stat-q-lbl">Avance Nivel</div>
          </div>
        </div>
      </div>

      <!-- ── GRID DE CONFIGURACIÓN ── -->
      <div class="config-grid">

        <!-- Leaderboard -->
        <div class="config-card">
          <div class="config-card-titulo"><i class="fas fa-ranking-star" style="color:var(--azul);"></i>Leaderboard de Ficha</div>
          <?php if (empty($leaderboard)): ?>
            <p style="color:var(--texto-tenue); font-size:0.8rem; text-align:center; padding:12px;">Completa lecciones para entrar al ranking.</p>
          <?php else: ?>
            <div style="display:flex; flex-direction:column; gap:8px;">
              <?php foreach ($leaderboard as $pos => $uRank): 
                $isMe = $uRank['id'] === $_SESSION['usuario_id'];
              ?>
                <div style="display:flex; align-items:center; justify-content:space-between; padding:8px 12px; border-radius:10px; border:2px solid <?= $isMe ? 'var(--verde)' : 'var(--gris-claro)' ?>; background: <?= $isMe ? 'rgba(88,204,2,0.08)' : 'var(--fondo)' ?>;">
                  <div style="display:flex; align-items:center; gap:8px;">
                    <span style="font-weight:900; width:20px; color:var(--gris-medio);">#<?= $pos + 1 ?></span>
                    <span style="font-weight:<?= $isMe ? '800' : '700' ?>; font-size:0.85rem; color:var(--gris-texto);"><?= htmlspecialchars($uRank['nombre_completo']) ?></span>
                  </div>
                  <span style="font-weight:800; font-size:0.85rem; color:var(--naranja);"><?= number_format($uRank['xp_puntos']) ?> XP</span>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>

        <!-- Heatmap de Actividad -->
        <div class="config-card">
          <div class="config-card-titulo"><i class="fas fa-calendar-check" style="color:var(--verde);"></i>Actividad (Últimos 90 Días)</div>
          <p style="font-size:0.75rem; color:var(--texto-tenue); margin-bottom:12px;">Visualiza tu constancia y días activos de estudio clínico.</p>
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
                  $isActiveDay = in_array($dVal, $heatmapActivo);
            ?>
              <div style="aspect-ratio:1; border-radius:3px; background: <?= $isActiveDay ? 'var(--verde)' : 'var(--gris-claro)' ?>; border: 1px solid <?= $isActiveDay ? 'var(--verde-oscuro)' : 'transparent' ?>; box-shadow: <?= $isActiveDay ? '0 0 6px rgba(88,204,2,0.4)' : 'none' ?>;" 
                   title="<?= date('d/m/Y', strtotime($dVal)) . ($isActiveDay ? ' - Día Activo' : ' - Sin actividad') ?>">
              </div>
            <?php endforeach; ?>
          </div>
          <div style="display:flex; align-items:center; gap:8px; margin-top:12px; font-size:0.68rem; color:var(--gris-medio); justify-content:flex-end;">
            <span>Menos</span>
            <div style="width:10px; height:10px; border-radius:2px; background:var(--gris-claro);"></div>
            <div style="width:10px; height:10px; border-radius:2px; background:var(--verde);"></div>
            <span>Más</span>
          </div>
        </div>

        <!-- Colección de Insignias -->
        <div class="config-card" style="grid-column: span 2;">
          <div class="config-card-titulo"><i class="fas fa-medal" style="color:var(--naranja);"></i>Colección de Insignias</div>
          <div style="display:flex; gap:24px; flex-wrap:wrap; margin-top:10px; justify-content:center;">
            <?php 
              $earnedIds = array_column($insigniasGanadas, 'id');
              foreach ($todasInsignias as $insig):
                  $hasEarned = in_array($insig['id'], $earnedIds);
            ?>
              <div style="display:flex; flex-direction:column; align-items:center; width:110px; text-align:center; opacity: <?= $hasEarned ? '1' : '0.4' ?>; filter: <?= $hasEarned ? 'none' : 'grayscale(100%)' ?>;">
                <div style="width:64px; height:64px; border-radius:50%; background:var(--fondo); border:3px solid <?= $hasEarned ? 'var(--naranja)' : 'var(--gris-claro)' ?>; display:flex; align-items:center; justify-content:center; font-size:1.8rem; color:var(--naranja); box-shadow: <?= $hasEarned ? '0 4px 10px rgba(255,150,0,0.2)' : 'none' ?>; transition:all 0.2s;">
                  <?php if ($insig['nombre'] === 'Quiz Perfecto'): ?>
                    <i class="fas fa-trophy"></i>
                  <?php elseif ($insig['nombre'] === 'Primer Nivel'): ?>
                    <i class="fas fa-star"></i>
                  <?php elseif ($insig['nombre'] === 'Racha 7 Días'): ?>
                    <i class="fas fa-fire"></i>
                  <?php elseif ($insig['nombre'] === 'Vocabulario Pro'): ?>
                    <i class="fas fa-book-medical"></i>
                  <?php else: ?>
                    <i class="fas fa-award"></i>
                  <?php endif; ?>
                </div>
                <div style="font-size:0.78rem; font-weight:800; margin-top:8px; color:var(--gris-texto);"><?= htmlspecialchars($insig['nombre']) ?></div>
                <div style="font-size:0.65rem; color:var(--texto-tenue); margin-top:2px; line-height:1.2;"><?= htmlspecialchars($insig['descripcion']) ?></div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- Historial de Quizzes -->
        <div class="config-card" style="grid-column: span 2;">
          <div class="config-card-titulo"><i class="fas fa-clock-rotate-left" style="color:var(--morado);"></i>Historial de Evaluaciones</div>
          <?php if (empty($historialQuizzes)): ?>
            <p style="color:var(--texto-tenue); text-align:center; padding: 20px;">No has completado ninguna evaluación final de RAP todavía.</p>
          <?php else: ?>
            <div style="overflow-x:auto;">
              <table class="vocab-table" style="width:100%; border:none;">
                <thead>
                  <tr>
                    <th style="padding:10px 12px;">Quiz / Resultado de Aprendizaje</th>
                    <th style="padding:10px 12px; text-align:center;">Puntaje</th>
                    <th style="padding:10px 12px; text-align:center;">Resultado</th>
                    <th style="padding:10px 12px; text-align:center;">Intento</th>
                    <th style="padding:10px 12px; text-align:right;">Fecha</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($historialQuizzes as $q): ?>
                    <tr>
                      <td style="padding:12px; font-weight:700;"><?= htmlspecialchars($q['rap_titulo']) ?></td>
                      <td style="padding:12px; text-align:center; font-weight:800; color:<?= $q['aprobado'] ? 'var(--verde)' : 'var(--rojo)' ?>;"><?= (int)$q['puntaje'] ?>%</td>
                      <td style="padding:12px; text-align:center;">
                        <span class="tag-badge <?= $q['aprobado'] ? 'nivel' : 'cat' ?>" style="font-size:0.65rem; display:inline-block; padding:3px 8px;">
                          <?= $q['aprobado'] ? 'APROBADO' : 'REPROBADO' ?>
                        </span>
                      </td>
                      <td style="padding:12px; text-align:center; font-weight:700;">#<?= $q['numero_intento'] ?></td>
                      <td style="padding:12px; text-align:right; font-size:0.8rem; color:var(--texto-tenue);"><?= date('d/m/Y H:i', strtotime($q['creado_en'])) ?></td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php endif; ?>
        </div>

        <!-- Datos Personales -->
        <div class="config-card">
          <div class="config-card-titulo"><i class="fas fa-user-pen" style="color:var(--verde);"></i>Datos Personales</div>

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
              <label class="campo-label">Correo electrónico</label>
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

        <!-- Seguridad -->
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

        <!-- Preferencias -->
        <div class="config-card">
          <div class="config-card-titulo"><i class="fas fa-palette" style="color:var(--morado);"></i>Preferencias de Apariencia</div>

          <div class="campo-grupo">
            <label class="campo-label">Tema de la interfaz</label>
            <div style="display:flex; gap:12px; margin-top:4px;">
              <button onclick="cambiarTema('dark')" id="btn-tema-oscuro"
                style="flex:1; padding:10px; border-radius:10px; border:2px solid var(--gris-claro); background:var(--fondo); color:var(--gris-texto); font-family:var(--fuente); font-weight:800; cursor:pointer; font-size:0.82rem; transition:all 0.2s;">
                <i class="fas fa-moon" style="margin-right:6px;"></i>Oscuro
              </button>
              <button onclick="cambiarTema('light')" id="btn-tema-claro"
                style="flex:1; padding:10px; border-radius:10px; border:2px solid var(--gris-claro); background:var(--fondo); color:var(--gris-texto); font-family:var(--fuente); font-weight:800; cursor:pointer; font-size:0.82rem; transition:all 0.2s;">
                <i class="fas fa-sun" style="margin-right:6px;"></i>Claro
              </button>
            </div>
          </div>

          <div class="divider"></div>

          <div style="display:flex; align-items:center; justify-content:space-between; padding:12px 0;">
            <div>
              <div style="font-size:0.85rem; font-weight:700; color:var(--gris-texto);">Sonidos de la app</div>
              <div style="font-size:0.72rem; color:var(--gris-medio); margin-top:2px;">Efectos de sonido al completar lecciones</div>
            </div>
            <label class="toggle-switch" style="position:relative; display:inline-block; width:44px; height:24px;">
              <input type="checkbox" id="toggle-sonido" style="opacity:0; width:0; height:0;" onchange="toggleSonido(this)">
              <span style="position:absolute; cursor:pointer; top:0; left:0; right:0; bottom:0; background:var(--gris-claro); border-radius:34px; transition:.3s;"
                    id="toggle-sonido-track">
                <span style="position:absolute; height:18px; width:18px; left:3px; bottom:3px; background:#fff; border-radius:50%; transition:.3s;" id="toggle-sonido-thumb"></span>
              </span>
            </label>
          </div>
        </div>

        <!-- Cerrar sesión -->
        <div class="config-card danger-zone">
          <div class="config-card-titulo"><i class="fas fa-triangle-exclamation"></i>Zona de Riesgo</div>
          <p style="font-size:0.85rem; color:var(--gris-medio); margin-bottom:20px; line-height:1.6;">
            Al cerrar sesión perderás acceso hasta que inicies sesión nuevamente. Tu progreso y XP siempre se guardan automáticamente.
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

<script src="<?= PROYECTO_PATH ?>/assets/js/tema.js"></script>
<script>
  // Resaltar el botón del tema activo
  function actualizarBotonesTema() {
    const tema = document.documentElement.getAttribute('data-theme') || 'dark';
    const btnOscuro = document.getElementById('btn-tema-oscuro');
    const btnClaro  = document.getElementById('btn-tema-claro');
    if (!btnOscuro || !btnClaro) return;
    const estiloActivo   = 'border-color: var(--verde); color: var(--verde); background: rgba(88,204,2,0.1);';
    const estiloInactivo = '';
    btnOscuro.style.cssText += tema === 'dark' ? estiloActivo : estiloInactivo;
    btnClaro.style.cssText  += tema === 'light' ? estiloActivo : estiloInactivo;
  }

  function cambiarTema(nuevoTema) {
    document.documentElement.setAttribute('data-theme', nuevoTema);
    localStorage.setItem('smashcode_tema', nuevoTema);
    // Actualizar el botón en la sidebar también
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
    if (cb.checked) {
      track.style.background = 'var(--verde)';
      thumb.style.transform = 'translateX(20px)';
      localStorage.setItem('smashcode_sonido', '1');
    } else {
      track.style.background = 'var(--gris-claro)';
      thumb.style.transform = 'translateX(0)';
      localStorage.setItem('smashcode_sonido', '0');
    }
  }

  // Al cargar: restaurar preferencias
  document.addEventListener('DOMContentLoaded', function() {
    actualizarBotonesTema();
    const sonido = localStorage.getItem('smashcode_sonido') === '1';
    const cbSonido = document.getElementById('toggle-sonido');
    if (cbSonido && sonido) {
      cbSonido.checked = true;
      toggleSonido(cbSonido);
    }
  });
</script>
</body>
</html>
