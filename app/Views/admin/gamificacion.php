<!DOCTYPE html>
<html lang="es" data-theme="dark">
<head>
  <meta charset="UTF-8">
  <link rel="icon" type="image/svg+xml" href="<?= PROYECTO_PATH ?>/assets/img/favicon.svg">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Configuración de Puntos y Gamificación — Admin SmashCode</title>
  <link rel="stylesheet" href="<?= PROYECTO_PATH ?>/assets/css/estilos.css">
  <link rel="stylesheet" href="<?= PROYECTO_PATH ?>/assets/css/cruds.css?v=<?= time() ?>">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <script>(function(){var t=localStorage.getItem('smashcode_tema');if(t)document.documentElement.setAttribute('data-theme',t);})();</script>
</head>
<body>
<div class="contenedor-app">

  <?php include __DIR__ . '/partials/sidebar.php'; ?>

  <main class="contenido-principal">
    <header class="barra-superior barra-superior-admin">
      <div class="breadcrumb-admin">
        <i class="fas fa-home breadcrumb-icon"></i>
        <a href="<?= PROYECTO_PATH ?>/admin" class="breadcrumb-current" style="text-decoration:none;">Dashboard</a>
        <i class="fas fa-chevron-right breadcrumb-separator"></i>
        <span class="breadcrumb-link"><i class="fas fa-trophy" style="color:var(--naranja); margin-right:4px;"></i> Puntos y Gamificación</span>
      </div>
      <div class="admin-header-actions">
        <button id="btn-cambiar-tema" class="btn-tema" aria-label="Cambiar a modo claro" title="Cambiar a modo claro">
          <i class="fas fa-sun tema-icono"></i>
          <span class="tema-label">Claro</span>
        </button>
        <div class="avatar-usuario" title="<?= limpiar($_SESSION['nombre'] ?? 'Administrador') ?>">
          <?= strtoupper(substr($_SESSION['nombre'] ?? 'A', 0, 1)) ?>
        </div>
      </div>
    </header>

    <div class="pagina-contenido">
      <div class="encabezado-seccion-admin" style="margin-bottom: 24px;">
        <div>
          <h1 class="pagina-titulo" style="margin:0; font-size:1.6rem; font-weight:800; color:var(--texto-principal); letter-spacing:-0.5px; display:flex; align-items:center; gap:10px;">
            <i class="fas fa-trophy" style="color:var(--naranja); font-size:1.4rem;"></i>
            Configuración de Puntos (XP) y Gamificación
          </h1>
          <p class="desc-seccion-admin" style="margin:4px 0 0 0; font-size:0.88rem; color:var(--texto-secundario); font-weight:500;">
            Define las recompensas de experiencia, rangos clínicos y metas de racha para los aprendices.
          </p>
        </div>
      </div>

      <?php if ($exito === 'guardado'): ?>
        <div class="alerta-perfil alerta-ok" style="margin-bottom:20px; display:flex; align-items:center; gap:10px; background:rgba(88,204,2,0.15); border:1.5px solid var(--verde); color:var(--verde); padding:14px 20px; border-radius:12px; font-weight:700;">
          <i class="fas fa-check-circle" style="font-size:1.2rem;"></i>
          Configuración de puntos y gamificación actualizada con éxito.
        </div>
      <?php endif; ?>

      <?php if ($error): ?>
        <div class="alerta-perfil alerta-err" style="margin-bottom:20px; display:flex; align-items:center; gap:10px; background:rgba(255,75,75,0.15); border:1.5px solid var(--rojo); color:var(--rojo); padding:14px 20px; border-radius:12px; font-weight:700;">
          <i class="fas fa-exclamation-circle" style="font-size:1.2rem;"></i>
          Ocurrió un error al guardar los cambios. Intenta nuevamente.
        </div>
      <?php endif; ?>

      <div style="display:grid; grid-template-columns: 1.2fr 0.8fr; gap:24px; align-items:start;">

        <!-- Formulario de Configuración -->
        <div class="tarjeta" style="padding:28px;">
          <h2 style="font-size:1.15rem; font-weight:800; color:var(--texto-principal); margin-bottom:20px; display:flex; align-items:center; gap:10px;">
            <i class="fas fa-sliders" style="color:var(--azul);"></i> Parámetros de Recompensa
          </h2>

          <form action="<?= PROYECTO_PATH ?>/admin/gamificacion/guardar" method="POST">
            <input type="hidden" name="csrf_token" value="<?= generarTokenCSRF() ?>">

            <div class="grupo-input" style="margin-bottom:20px;">
              <label class="label-input" for="xp_ejercicio_correcto" style="font-weight:700;">
                <i class="fas fa-dumbbell" style="color:var(--verde); margin-right:6px;"></i> XP por Ejercicio Correcto
              </label>
              <input type="number" min="0" max="1000" id="xp_ejercicio_correcto" name="xp_ejercicio_correcto" 
                     class="input-base" value="<?= (int)($config['xp_ejercicio_correcto'] ?? 10) ?>" required>
              <small class="form-hint" style="color:var(--texto-secundario); font-size:0.75rem; margin-top:4px; display:block;">
                Puntos otorgados al aprendiz al resolver correctamente una actividad del Momento 3.
              </small>
            </div>

            <div class="grupo-input" style="margin-bottom:20px;">
              <label class="label-input" for="xp_quiz_aprobado" style="font-weight:700;">
                <i class="fas fa-check-double" style="color:var(--azul); margin-right:6px;"></i> XP por Aprobar Quiz (≥ 60%)
              </label>
              <input type="number" min="0" max="5000" id="xp_quiz_aprobado" name="xp_quiz_aprobado" 
                     class="input-base" value="<?= (int)($config['xp_quiz_aprobado'] ?? 50) ?>" required>
              <small class="form-hint" style="color:var(--texto-secundario); font-size:0.75rem; margin-top:4px; display:block;">
                Puntos otorgados por aprobar la evaluación final de cualquier RAP.
              </small>
            </div>

            <div class="grupo-input" style="margin-bottom:20px;">
              <label class="label-input" for="xp_quiz_perfecto" style="font-weight:700;">
                <i class="fas fa-star" style="color:var(--naranja); margin-right:6px;"></i> Bonus por Quiz Perfecto (100%)
              </label>
              <input type="number" min="0" max="5000" id="xp_quiz_perfecto" name="xp_quiz_perfecto" 
                     class="input-base" value="<?= (int)($config['xp_quiz_perfecto'] ?? 100) ?>" required>
              <small class="form-hint" style="color:var(--texto-secundario); font-size:0.75rem; margin-top:4px; display:block;">
                Puntos de bonificación extra si el aprendiz obtiene 100% de aciertos en el quiz.
              </small>
            </div>

            <div class="grupo-input" style="margin-bottom:20px;">
              <label class="label-input" for="xp_por_nivel" style="font-weight:700;">
                <i class="fas fa-layer-group" style="color:var(--morado); margin-right:6px;"></i> XP requerido por Nivel de Perfil
              </label>
              <input type="number" min="100" max="10000" id="xp_por_nivel" name="xp_por_nivel" 
                     class="input-base" value="<?= (int)($config['xp_por_nivel'] ?? 500) ?>" required>
              <small class="form-hint" style="color:var(--texto-secundario); font-size:0.75rem; margin-top:4px; display:block;">
                Cantidad de XP necesaria para subir de rango clínico (ej: 500 XP = 1 nivel).
              </small>
            </div>

            <div class="grupo-input" style="margin-bottom:28px;">
              <label class="label-input" for="dias_racha_insignia" style="font-weight:700;">
                <i class="fas fa-fire" style="color:var(--rojo); margin-right:6px;"></i> Días de Racha para Insignia
              </label>
              <input type="number" min="1" max="365" id="dias_racha_insignia" name="dias_racha_insignia" 
                     class="input-base" value="<?= (int)($config['dias_racha_insignia'] ?? 7) ?>" required>
              <small class="form-hint" style="color:var(--texto-secundario); font-size:0.75rem; margin-top:4px; display:block;">
                Días consecutivos de práctica requeridos para otorgar la insignia "Racha 7 Días".
              </small>
            </div>

            <div style="display:flex; justify-content:flex-end;">
              <button type="submit" class="btn btn-verde" style="padding:12px 24px; font-size:0.95rem; font-weight:800; display:inline-flex; align-items:center; gap:8px;">
                <i class="fas fa-save"></i> Guardar Cambios
              </button>
            </div>
          </form>
        </div>

        <!-- Escala de Rangos Clínicos (Vista Previa) -->
        <div class="tarjeta" style="padding:28px; background:var(--fondo-tarjeta);">
          <h2 style="font-size:1.15rem; font-weight:800; color:var(--texto-principal); margin-bottom:14px; display:flex; align-items:center; gap:10px;">
            <i class="fas fa-user-graduate" style="color:var(--verde);"></i> Escala de Rangos Clínicos (HU15)
          </h2>
          <p style="font-size:0.8rem; color:var(--texto-secundario); margin-bottom:18px; line-height:1.5;">
            Los aprendices avanzan automáticamente en esta jerarquía clínica conforme acumulan XP en ejercicios y evaluaciones:
          </p>

          <?php 
            $base = (int)($config['xp_por_nivel'] ?? 500);
            if ($base <= 0) $base = 500;
          ?>

          <div style="display:flex; flex-direction:column; gap:12px;">
            <!-- Nivel 1 -->
            <div style="display:flex; align-items:center; gap:14px; padding:12px 16px; border-radius:12px; background:rgba(28,176,246,0.08); border:1.5px solid rgba(28,176,246,0.3);">
              <div style="width:40px; height:40px; border-radius:50%; background:var(--azul); color:#fff; display:flex; align-items:center; justify-content:center; font-size:1.1rem; flex-shrink:0;">
                <i class="fas fa-shield-heart"></i>
              </div>
              <div style="flex:1;">
                <div style="font-weight:800; color:var(--azul); font-size:0.92rem;">Novato Clínico (Nivel 1)</div>
                <div style="font-size:0.75rem; color:var(--texto-secundario);">0 – <?= number_format($base - 1) ?> XP</div>
              </div>
            </div>

            <!-- Nivel 2 -->
            <div style="display:flex; align-items:center; gap:14px; padding:12px 16px; border-radius:12px; background:rgba(88,204,2,0.08); border:1.5px solid rgba(88,204,2,0.3);">
              <div style="width:40px; height:40px; border-radius:50%; background:var(--verde); color:#fff; display:flex; align-items:center; justify-content:center; font-size:1.1rem; flex-shrink:0;">
                <i class="fas fa-stethoscope"></i>
              </div>
              <div style="flex:1;">
                <div style="font-weight:800; color:var(--verde); font-size:0.92rem;">Intermedio Clínico (Nivel 2)</div>
                <div style="font-size:0.75rem; color:var(--texto-secundario);"><?= number_format($base) ?> – <?= number_format(($base * 3) - 1) ?> XP</div>
              </div>
            </div>

            <!-- Nivel 3 -->
            <div style="display:flex; align-items:center; gap:14px; padding:12px 16px; border-radius:12px; background:rgba(255,150,0,0.08); border:1.5px solid rgba(255,150,0,0.3);">
              <div style="width:40px; height:40px; border-radius:50%; background:var(--naranja); color:#fff; display:flex; align-items:center; justify-content:center; font-size:1.1rem; flex-shrink:0;">
                <i class="fas fa-user-doctor"></i>
              </div>
              <div style="flex:1;">
                <div style="font-weight:800; color:var(--naranja); font-size:0.92rem;">Avanzado Clínico (Nivel 3)</div>
                <div style="font-size:0.75rem; color:var(--texto-secundario);"><?= number_format($base * 3) ?> – <?= number_format(($base * 6) - 1) ?> XP</div>
              </div>
            </div>

            <!-- Nivel 4+ -->
            <div style="display:flex; align-items:center; gap:14px; padding:12px 16px; border-radius:12px; background:rgba(155,89,182,0.08); border:1.5px solid rgba(155,89,182,0.3);">
              <div style="width:40px; height:40px; border-radius:50%; background:var(--morado); color:#fff; display:flex; align-items:center; justify-content:center; font-size:1.1rem; flex-shrink:0;">
                <i class="fas fa-crown"></i>
              </div>
              <div style="flex:1;">
                <div style="font-weight:800; color:var(--morado); font-size:0.92rem;">Experto Clínico (Nivel 4+)</div>
                <div style="font-size:0.75rem; color:var(--texto-secundario);"><?= number_format($base * 6) ?>+ XP</div>
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>
  </main>
</div>

<script src="<?= PROYECTO_PATH ?>/assets/js/tema.js"></script>
</body>
</html>
