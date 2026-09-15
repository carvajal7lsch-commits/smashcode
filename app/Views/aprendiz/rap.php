<!DOCTYPE html>
<html lang="es" data-theme="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= limpiar($rap['titulo']) ?> — SmashCode</title>
  <link rel="stylesheet" href="<?= PROYECTO_PATH ?>/assets/css/estilos.css?v=<?= time() ?>">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <script>
    (function(){var t=localStorage.getItem('smashcode_tema');if(t)document.documentElement.setAttribute('data-theme',t);})();
  </script>
  <link rel="stylesheet" href="<?= PROYECTO_PATH ?>/assets/css/layout.css?v=<?= time() ?>">
  <link rel="stylesheet" href="<?= PROYECTO_PATH ?>/assets/css/aprendiz.css?v=<?= time() ?>">
  <script src="<?= PROYECTO_PATH ?>/assets/js/sonidos.js"></script>
</head>
<body>
<?php
  // HU14: en modo repaso la visita empieza de cero; la base conserva el avance real
  $modoRepaso    = $modoRepaso ?? false;
  $rapCompletado = $rapCompletado ?? false;
  $pctSesion     = $modoRepaso ? 0.0 : (float) $progreso['porcentaje'];
  $urlRepetir    = PROYECTO_PATH . '/aprendiz/rap?id=' . urlencode($rap['id']) . '&repetir=1';
  // HU22: intentos del quiz en la ronda actual (null en vista previa o sin quiz)
  $intentosQuiz  = $intentosQuiz ?? null;
  $quizBloqueado = !empty($intentosQuiz['bloqueado']);
?>

<?php if (isset($esPreview) && $esPreview): ?>
  <div style="background: linear-gradient(90deg, #1cb0f6, #1899d6); color: white; text-align: center; padding: 12px; font-weight: 800; font-size: 0.88rem; letter-spacing: 0.05em; text-transform: uppercase; display: flex; align-items: center; justify-content: center; gap: 8px; box-shadow: 0 4px 12px rgba(28, 176, 246, 0.25); z-index: 1000; position: relative;">
    <i class="fas fa-eye"></i> Modo Vista Previa: Tareas y Progreso no se guardarán en la base de datos.
  </div>
<?php endif; ?>

<div class="learning-view">
  <!-- HEADER CON PROGRESO -->
  <header class="learning-header">
    <button class="btn-exit" onclick="window.location='<?= PROYECTO_PATH ?>/'" title="Volver al Mapa">
      <i class="fas fa-times"></i>
    </button>
    <div class="progreso-header-bar">
      <div class="progreso-header-fill" id="header-progress-fill" style="width: <?= $pctSesion ?>%;"></div>
    </div>
    <div class="progreso-texto" id="header-progress-text"><?= (int) $pctSesion ?>%</div>
    
    <div class="header-xp-badge">
      <i class="fas fa-bolt"></i>
      <span id="session-xp">0 XP</span>
    </div>
  </header>

  <!-- PESTAÑAS DE MOMENTOS -->
  <nav class="moments-tabs" aria-label="Momentos Pedagógicos">
    <div class="moment-tab active" id="tab-moment-1" onclick="switchTab(1)">
      <i class="fas fa-gamepad"></i> Moment 1: Warm-Up
    </div>
    <div class="moment-tab <?= (isset($esPreview) && $esPreview) || (!$modoRepaso && ($progreso['porcentaje'] >= 25 || $progreso['completado'])) ? '' : 'locked' ?>" id="tab-moment-2" onclick="switchTab(2)">
      <i class="fas fa-book-reader"></i> Moment 2: Absorption
    </div>
    <div class="moment-tab <?= (isset($esPreview) && $esPreview) || (!$modoRepaso && ($progreso['porcentaje'] >= 50 || $progreso['completado'])) ? '' : 'locked' ?>" id="tab-moment-3" onclick="switchTab(3)">
      <i class="fas fa-dumbbell"></i> Moment 3: Practice
    </div>
    <div class="moment-tab <?= (isset($esPreview) && $esPreview) || (!$modoRepaso && ($progreso['porcentaje'] >= 75 || $progreso['completado'])) ? '' : 'locked' ?>" id="tab-moment-4" onclick="switchTab(4)">
      <i class="fas fa-award"></i> Moment 4: Quiz
    </div>
  </nav>

  <?php if ($modoRepaso || $rapCompletado): ?>
    <!-- HU14: repetir un RAP completado -->
    <div id="aviso-repaso" style="max-width:960px; margin:12px auto 0 auto; padding:12px 18px; border-radius:14px; border:2px solid var(--gris-claro); background:var(--fondo); display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap; font-weight:700; color:var(--gris-texto);">
      <?php if ($modoRepaso): ?>
        <span><i class="fas fa-redo" style="color:var(--azul); margin-right:8px;"></i>Modo repaso: empiezas de nuevo desde el Momento 1. Tu avance y tu mejor puntaje (<?= number_format((float) $progreso['mejor_puntaje_quiz'], 0) ?>%) se conservan aunque saques menos.<?php if ($quizBloqueado): ?> Al terminar la práctica (Momento 3) recibirás <?= (int) $intentosQuiz['limite'] ?> intentos nuevos para el quiz.<?php endif; ?></span>
      <?php else: ?>
        <span><i class="fas fa-circle-check" style="color:var(--verde); margin-right:8px;"></i>Ya completaste este RAP. Mejor puntaje en el quiz: <?= number_format((float) $progreso['mejor_puntaje_quiz'], 0) ?>%.</span>
        <a class="btn btn-azul" href="<?= htmlspecialchars($urlRepetir, ENT_QUOTES) ?>" style="padding:8px 16px; font-weight:800; text-decoration:none;">
          <i class="fas fa-redo" style="margin-right:6px;"></i> Repetir RAP
        </a>
      <?php endif; ?>
    </div>
  <?php endif; ?>

  <!-- CONTENEDOR DE PANELES -->
  <main class="moments-container">

    <!-- ==================== MOMENTO 1: WARM-UP ==================== -->
    <section class="moment-pane active" id="pane-moment-1" aria-labelledby="tab-moment-1">
      
      <!-- 1.1 Intro & Objetivos (Contenidos.md) -->
      <div class="card-moment" style="margin-bottom: 24px;">
        <div style="display:flex; align-items:center; gap:16px; margin-bottom:12px;">
          <div style="width:48px; height:48px; border-radius:14px; background:rgba(28,176,246,0.12); color:var(--azul); display:flex; align-items:center; justify-content:center; font-size:1.4rem;">
            <i class="fas fa-bullseye"></i>
          </div>
          <div>
            <h2 style="font-size:1.25rem; margin:0; font-weight:800; color:var(--gris-texto);">Momento 1: Preparación (Intro & Objetivos)</h2>
            <div style="font-size:0.85rem; font-weight:800; color:var(--azul); text-transform:uppercase; margin-top:2px;">
              <?= limpiar($rap['nivel_nombre']) ?>
            </div>
          </div>
        </div>

        <?php
          $introTextos = [
              1 => '¡Bienvenido! En este primer módulo aprenderás a presentarte, saludar a tus pacientes y colegas, e intercambiar información personal básica en el entorno de enfermería.',
              2 => 'En este módulo acompañarás a Mr. Thomas en su hospitalización. Al final, serás capaz de describir el estado físico de tus pacientes, detallar su entorno hospitalario y comprender qué les sucedió antes de llegar a urgencias.',
              3 => '¡Tu turno ha comenzado! En este módulo aprenderás a comunicarte con médicos, colegas y familiares de pacientes. Al final, podrás explicar procedimientos de rutina, interactuar con visitantes y proponer mejoras en tu entorno laboral.',
              4 => '¡Mr. Thomas se va a casa! En este último módulo, aprenderás a dar instrucciones de alta médica, recomendaciones de cuidado en casa y a evaluar los resultados de tu trabajo analizando las listas de verificación en inglés.'
          ];
          $introNarrativa = $introTextos[$rap['nivel_orden']] ?? 'En este módulo aprenderás las competencias clave de comunicación clínica en inglés.';
        ?>

        <div style="background:var(--fondo); border:2px solid var(--gris-claro); border-radius:14px; padding:18px 22px; margin-top:14px;">
          <h3 style="font-size:1rem; font-weight:800; color:var(--azul); margin-bottom:8px; display:flex; align-items:center; gap:8px;">
            <i class="fas fa-info-circle"></i> Introducción del Módulo
          </h3>
          <p style="color:var(--gris-texto); font-size:1.02rem; line-height:1.6; margin:0 0 16px 0; font-weight:600;">
            "<?= $introNarrativa ?>"
          </p>

          <h3 style="font-size:0.95rem; font-weight:800; color:var(--verde); margin-bottom:6px; display:flex; align-items:center; gap:8px;">
            <i class="fas fa-compass"></i> Objetivo Pedagógico (<?= limpiar($rap['titulo']) ?>)
          </h3>
          <p style="color:var(--texto-tenue); font-size:0.92rem; line-height:1.5; margin:0; font-weight:600;">
            <?= !empty($rap['nivel_descripcion']) ? limpiar($rap['nivel_descripcion']) : '' ?>
          </p>
        </div>
      </div>

      <div class="card-moment">
        <h2>Warm-Up Mini-Game</h2>
        <p style="color:var(--texto-tenue); margin-top:8px;">Match English terms to their Spanish meanings to activate your prior knowledge!</p>
        
        <div class="matching-grid" id="warmup-matching-grid">
          <div class="matching-col" id="warmup-col-en"></div>
          <div class="matching-col" id="warmup-col-es"></div>
        </div>

        <div id="warmup-success-msg" style="display:none; text-align:center; margin-top:32px; animation: fadeInUp 0.3s ease;">
          <h3 style="color:var(--verde); font-size:1.4rem; font-weight:800; margin-bottom:12px;">
            <i class="fas fa-star" style="margin-right:8px;"></i>¡Warm-Up Completado!
          </h3>
          <p style="color:var(--texto-tenue); margin-bottom:24px;">¡Has completado el Momento 1! El Momento 2 (Absorption) está desbloqueado.</p>
          <div style="display:flex; justify-content:center; gap:16px; flex-wrap:wrap; margin-top:20px;">
            <button class="btn btn-verde" onclick="finishMomentAndReturn(25)" style="padding:12px 24px; font-weight:800;">
              <i class="fas fa-map-marker-alt" style="margin-right:8px;"></i> Volver al Mapa
            </button>
            <button class="btn btn-azul" onclick="switchTab(2)" style="padding:12px 24px; font-weight:800;">
              Siguiente Momento <i class="fas fa-arrow-right" style="margin-left:8px;"></i>
            </button>
          </div>
        </div>
      </div>
    </section>

    <!-- ==================== MOMENTO 2: ABSORPTION ==================== -->
    <section class="moment-pane" id="pane-moment-2" aria-labelledby="tab-moment-2">
      <!-- 2.1 Grammar Pill -->
      <div class="card-moment">
        <?php if ($rap['nivel_orden'] == 2): ?>
          <h2 style="display:flex; align-items:center; gap:8px;"><i class="fas fa-pills" style="color:var(--naranja);"></i> Grammar Pill: Clinical Status vs. Patient History</h2>
          <p style="color:var(--texto-tenue); margin-top:8px; margin-bottom:20px;">
            Aprende a diferenciar las acciones en <strong>Pasado Simple</strong> (lo que le ocurrió al paciente antes de su ingreso) de los <strong>Adjetivos Descriptivos y Estado Actual</strong> (cómo se encuentra hoy Mr. Thomas).
          </p>

          <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap:16px; margin-bottom:12px;">
            <!-- Columna 1: Patient's History (Simple Past) -->
            <div style="background:var(--fondo); border:2px solid var(--gris-claro); border-radius:14px; padding:18px;">
              <h3 style="font-size:0.9rem; font-weight:800; color:var(--morado); text-transform:uppercase; margin-bottom:14px; display:flex; align-items:center; gap:8px;">
                <i class="fas fa-history"></i> Patient's History (Pasado Simple)
              </h3>
              <div style="display:flex; flex-direction:column; gap:10px;">
                <div style="background:var(--blanco); padding:12px 14px; border-radius:10px; border:1px solid var(--gris-claro); font-size:0.95rem; font-weight:700;">
                  He <span style="color:var(--naranja); font-weight:900;">fell</span> at the hotel yesterday.
                  <div style="font-size:0.8rem; color:var(--texto-tenue); font-weight:600; margin-top:2px;">(Él se cayó en el hotel ayer)</div>
                </div>
                <div style="background:var(--blanco); padding:12px 14px; border-radius:10px; border:1px solid var(--gris-claro); font-size:0.95rem; font-weight:700;">
                  He <span style="color:var(--naranja); font-weight:900;">had</span> an accident before admission.
                  <div style="font-size:0.8rem; color:var(--texto-tenue); font-weight:600; margin-top:2px;">(Él tuvo un accidente antes del ingreso)</div>
                </div>
                <div style="background:var(--blanco); padding:12px 14px; border-radius:10px; border:1px solid var(--gris-claro); font-size:0.95rem; font-weight:700;">
                  The paramedics <span style="color:var(--naranja); font-weight:900;">brought</span> him on a stretcher.
                  <div style="font-size:0.8rem; color:var(--texto-tenue); font-weight:600; margin-top:2px;">(Los paramédicos lo trajeron en camilla)</div>
                </div>
              </div>
            </div>

            <!-- Columna 2: Current Status (Present & Descriptives) -->
            <div style="background:var(--fondo); border:2px solid var(--gris-claro); border-radius:14px; padding:18px;">
              <h3 style="font-size:0.9rem; font-weight:800; color:var(--azul); text-transform:uppercase; margin-bottom:14px; display:flex; align-items:center; gap:8px;">
                <i class="fas fa-stethoscope"></i> Current Status (Estado Actual)
              </h3>
              <div style="display:flex; flex-direction:column; gap:10px;">
                <div style="background:var(--blanco); padding:12px 14px; border-radius:10px; border:1px solid var(--gris-claro); font-size:0.95rem; font-weight:700;">
                  Mr. Thomas <span style="color:var(--azul); font-weight:900;">is</span> <span style="color:var(--verde); font-weight:900;">pale and tired</span> today.
                  <div style="font-size:0.8rem; color:var(--texto-tenue); font-weight:600; margin-top:2px;">(El paciente está pálido y cansado hoy)</div>
                </div>
                <div style="background:var(--blanco); padding:12px 14px; border-radius:10px; border:1px solid var(--gris-claro); font-size:0.95rem; font-weight:700;">
                  His right arm <span style="color:var(--azul); font-weight:900;">has</span> a <span style="color:var(--verde); font-weight:900;">fracture</span>.
                  <div style="font-size:0.8rem; color:var(--texto-tenue); font-weight:600; margin-top:2px;">(Su brazo derecho tiene una fractura)</div>
                </div>
                <div style="background:var(--blanco); padding:12px 14px; border-radius:10px; border:1px solid var(--gris-claro); font-size:0.95rem; font-weight:700;">
                  The room <span style="color:var(--azul); font-weight:900;">is</span> <span style="color:var(--verde); font-weight:900;">cold</span>, but signs are stable.
                  <div style="font-size:0.8rem; color:var(--texto-tenue); font-weight:600; margin-top:2px;">(La habitación está fría, pero está estable)</div>
                </div>
              </div>
            </div>
          </div>
        <?php elseif ($rap['nivel_orden'] == 3): ?>
          <h2 style="display:flex; align-items:center; gap:8px;"><i class="fas fa-pills" style="color:var(--naranja);"></i> Grammar Pill: Daily Routines vs. Happening Now</h2>
          <p style="color:var(--texto-tenue); margin-top:8px; margin-bottom:20px;">
            Aprende a diferenciar el <strong>Presente Simple</strong> (para rutinas laborales diarias) del <strong>Presente Continuo</strong> (para acciones que ocurren en el momento) y fórmulas de cortesía para sugerir mejoras (<em>We should...</em>).
          </p>

          <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap:16px; margin-bottom:12px;">
            <!-- Columna 1: Daily Routine (Present Simple) -->
            <div style="background:var(--fondo); border:2px solid var(--gris-claro); border-radius:14px; padding:18px;">
              <h3 style="font-size:0.9rem; font-weight:800; color:var(--morado); text-transform:uppercase; margin-bottom:14px; display:flex; align-items:center; gap:8px;">
                <i class="fas fa-calendar-check"></i> Daily Routine (Presente Simple)
              </h3>
              <div style="display:flex; flex-direction:column; gap:10px;">
                <div style="background:var(--blanco); padding:12px 14px; border-radius:10px; border:1px solid var(--gris-claro); font-size:0.95rem; font-weight:700;">
                  I <span style="color:var(--naranja); font-weight:900;">give</span> medication at 8 AM every day.
                  <div style="font-size:0.8rem; color:var(--texto-tenue); font-weight:600; margin-top:2px;">(Administro medicamentos a las 8 AM todos los días)</div>
                </div>
                <div style="background:var(--blanco); padding:12px 14px; border-radius:10px; border:1px solid var(--gris-claro); font-size:0.95rem; font-weight:700;">
                  Nurse Sarah <span style="color:var(--naranja); font-weight:900;">checks</span> vital signs during rounds.
                  <div style="font-size:0.8rem; color:var(--texto-tenue); font-weight:600; margin-top:2px;">(La enfermera verifica signos vitales en las rondas)</div>
                </div>
              </div>
            </div>

            <!-- Columna 2: Happening Now (Present Continuous) -->
            <div style="background:var(--fondo); border:2px solid var(--gris-claro); border-radius:14px; padding:18px;">
              <h3 style="font-size:0.9rem; font-weight:800; color:var(--azul); text-transform:uppercase; margin-bottom:14px; display:flex; align-items:center; gap:8px;">
                <i class="fas fa-clock"></i> Happening Now (Presente Continuo)
              </h3>
              <div style="display:flex; flex-direction:column; gap:10px;">
                <div style="background:var(--blanco); padding:12px 14px; border-radius:10px; border:1px solid var(--gris-claro); font-size:0.95rem; font-weight:700;">
                  We <span style="color:var(--azul); font-weight:900;">are checking</span> his temperature right now.
                  <div style="font-size:0.8rem; color:var(--texto-tenue); font-weight:600; margin-top:2px;">(Estamos midiendo su temperatura justo ahora)</div>
                </div>
                <div style="background:var(--blanco); padding:12px 14px; border-radius:10px; border:1px solid var(--gris-claro); font-size:0.95rem; font-weight:700;">
                  I think we <span style="color:var(--verde); font-weight:900;">should update</span> the checklist.
                  <div style="font-size:0.8rem; color:var(--texto-tenue); font-weight:600; margin-top:2px;">(Creo que deberíamos actualizar la lista de chequeo)</div>
                </div>
              </div>
            </div>
          </div>
        <?php elseif ($rap['nivel_orden'] == 4): ?>
          <h2 style="display:flex; align-items:center; gap:8px;"><i class="fas fa-pills" style="color:var(--naranja);"></i> Grammar Pill: Medical Advice vs. Reporting Results</h2>
          <p style="color:var(--texto-tenue); margin-top:8px; margin-bottom:20px;">
            Aprende a usar los <strong>verbos modales</strong> para dar consejos e instrucciones de alta (<em>should</em> para aconsejar, <em>must</em> para una obligación) y frases sencillas para <strong>reportar resultados</strong> de la lista de verificación.
          </p>

          <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap:16px; margin-bottom:12px;">
            <!-- Columna 1: Medical Advice (Modal Verbs) -->
            <div style="background:var(--fondo); border:2px solid var(--gris-claro); border-radius:14px; padding:18px;">
              <h3 style="font-size:0.9rem; font-weight:800; color:var(--morado); text-transform:uppercase; margin-bottom:14px; display:flex; align-items:center; gap:8px;">
                <i class="fas fa-user-md"></i> Medical Advice (Verbos Modales)
              </h3>
              <div style="display:flex; flex-direction:column; gap:10px;">
                <div style="background:var(--blanco); padding:12px 14px; border-radius:10px; border:1px solid var(--gris-claro); font-size:0.95rem; font-weight:700;">
                  You <span style="color:var(--naranja); font-weight:900;">should rest</span> at home for five days.
                  <div style="font-size:0.8rem; color:var(--texto-tenue); font-weight:600; margin-top:2px;">(Debería descansar en casa cinco días)</div>
                </div>
                <div style="background:var(--blanco); padding:12px 14px; border-radius:10px; border:1px solid var(--gris-claro); font-size:0.95rem; font-weight:700;">
                  You <span style="color:var(--naranja); font-weight:900;">must take</span> this pill every eight hours.
                  <div style="font-size:0.8rem; color:var(--texto-tenue); font-weight:600; margin-top:2px;">(Debe tomar esta pastilla cada ocho horas)</div>
                </div>
                <div style="background:var(--blanco); padding:12px 14px; border-radius:10px; border:1px solid var(--gris-claro); font-size:0.95rem; font-weight:700;">
                  You <span style="color:var(--naranja); font-weight:900;">must not lift</span> heavy objects.
                  <div style="font-size:0.8rem; color:var(--texto-tenue); font-weight:600; margin-top:2px;">(No debe levantar objetos pesados)</div>
                </div>
              </div>
            </div>

            <!-- Columna 2: Reporting Results -->
            <div style="background:var(--fondo); border:2px solid var(--gris-claro); border-radius:14px; padding:18px;">
              <h3 style="font-size:0.9rem; font-weight:800; color:var(--azul); text-transform:uppercase; margin-bottom:14px; display:flex; align-items:center; gap:8px;">
                <i class="fas fa-clipboard-check"></i> Reporting Results (Reporte de Resultados)
              </h3>
              <div style="display:flex; flex-direction:column; gap:10px;">
                <div style="background:var(--blanco); padding:12px 14px; border-radius:10px; border:1px solid var(--gris-claro); font-size:0.95rem; font-weight:700;">
                  The temperature <span style="color:var(--azul); font-weight:900;">is</span> <span style="color:var(--verde); font-weight:900;">normal</span>.
                  <div style="font-size:0.8rem; color:var(--texto-tenue); font-weight:600; margin-top:2px;">(La temperatura es normal)</div>
                </div>
                <div style="background:var(--blanco); padding:12px 14px; border-radius:10px; border:1px solid var(--gris-claro); font-size:0.95rem; font-weight:700;">
                  The pain level <span style="color:var(--azul); font-weight:900;">is</span> <span style="color:var(--verde); font-weight:900;">low</span>.
                  <div style="font-size:0.8rem; color:var(--texto-tenue); font-weight:600; margin-top:2px;">(El nivel de dolor es bajo)</div>
                </div>
                <div style="background:var(--blanco); padding:12px 14px; border-radius:10px; border:1px solid var(--gris-claro); font-size:0.95rem; font-weight:700;">
                  The patient <span style="color:var(--azul); font-weight:900;">is</span> <span style="color:var(--verde); font-weight:900;">ready for discharge</span>.
                  <div style="font-size:0.8rem; color:var(--texto-tenue); font-weight:600; margin-top:2px;">(El paciente está listo para el alta)</div>
                </div>
              </div>
            </div>
          </div>
        <?php else: ?>
          <h2>Grammar Pill</h2>
          <p style="color:var(--texto-tenue); margin-top:8px; margin-bottom:16px;">Analyze the grammatical structure of clinical interactions: Subject + Verb + Complement.</p>
          <div class="grammar-pill">
            <h3>Forming Patient Registrations & Sentences</h3>
            <div class="grammar-table">
              <span class="gt-sujeto" title="Subject">I</span>
              <span class="gt-verbo" title="Verb To Be">am</span>
              <span class="gt-complemento" title="Complement">Sarah, your nurse</span>
            </div>
            <p style="font-size:0.9rem; text-align:center; color:var(--texto-tenue);">
              Sujeto (<span style="color:#1cb0f6; font-weight:700;">I</span>) + Verbo To Be (<span style="color:#ff9600; font-weight:700;">am</span>) + Complemento Clínico (<span style="color:#58cc02; font-weight:700;">Sarah, su enfermera</span>).
            </p>
          </div>
        <?php endif; ?>
      </div>

      <!-- 2.2 Vocabulary Lab Slider -->
      <div class="card-moment">
        <h2>Vocabulary Laboratory</h2>
        <p style="color:var(--texto-tenue); margin-top:8px; margin-bottom:20px;">Review clinical terminology. Click a card to flip and reveal the translation. Mark terms you find hard.</p>
        
        <?php if (empty($vocabulario)): ?>
          <p style="color:var(--texto-tenue); text-align:center;">No vocabulary terms loaded for this RAP.</p>
        <?php else: ?>
          <div class="slider-wrap">
            <button class="btn-gris" onclick="prevVocab()" id="btn-prev-vocab" style="padding:10px 16px;"><i class="fas fa-chevron-left"></i></button>
            
            <div class="flashcard" id="current-flashcard" onclick="flipCard()">
              <div class="flashcard-inner">
                <!-- FRONT -->
                <div class="flashcard-front">
                  <button class="btn-star-mark" id="btn-star-vocab" onclick="toggleStar(event)" title="Marcar como difícil">
                    <i class="far fa-star"></i>
                  </button>
                  <div class="fc-title" id="vocab-word-en">Word</div>
                  <div class="fc-ipa" id="vocab-word-ipa">/ipa/</div>
                  <div class="fc-example" id="vocab-word-ex">Example Sentence</div>
                  <button class="fc-audio-btn" onclick="speakVocab(event)" title="Escuchar pronunciación">
                    <i class="fas fa-volume-up"></i>
                  </button>
                </div>
                <!-- BACK -->
                <div class="flashcard-back">
                  <div class="fc-translation" id="vocab-word-es">Traducción</div>
                </div>
              </div>
            </div>

            <button class="btn-gris" onclick="nextVocab()" id="btn-next-vocab" style="padding:10px 16px;"><i class="fas fa-chevron-right"></i></button>
          </div>
          <div style="text-align:center; margin-top:16px; font-weight:800; color:var(--gris-medio);" id="vocab-counter">1 / 5</div>
        <?php endif; ?>
      </div>

      <!-- 2.3 Storybook Dialogue Highlight -->
      <div class="card-moment">
        <h2>Storybook Dialogue</h2>
        <p style="color:var(--texto-tenue); margin-top:8px; margin-bottom:20px;">Play the dialogue below. The active speech line will be automatically highlighted.</p>
        
        <?php if (empty($dialogos)): ?>
          <p style="color:var(--texto-tenue); text-align:center;">No clinical dialogues loaded for this RAP.</p>
        <?php else: ?>
          <?php foreach ($dialogos as $d): ?>
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px; flex-wrap:wrap; gap:8px;">
              <h3 style="font-size:1.15rem; font-weight:800; color:var(--gris-texto);"><i class="fas fa-hospital-user" style="margin-right:8px; color:var(--azul);"></i><?= limpiar($d['titulo']) ?></h3>
              <div style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
                <button class="btn-play-full-dialogue" onclick="playFullDialogue('dialogue-<?= $d['id'] ?>')">
                  <i class="fas fa-play-circle"></i> Play Full Dialog
                </button>
                <!-- HU13: controles que aparecen mientras el diálogo está en curso -->
                <button class="btn-control-dialogo" id="btn-prev-turn-<?= $d['id'] ?>" onclick="previousTurn()" style="display:none;" title="Volver a la línea anterior">
                  <i class="fas fa-backward-step"></i> Previous Line
                </button>
                <button class="btn-control-dialogo" id="btn-pause-audio-<?= $d['id'] ?>" onclick="pauseDialogue()" style="display:none;" title="Pausar el diálogo">
                  <i class="fas fa-pause-circle"></i> Pause
                </button>
                <button class="btn-control-dialogo" id="btn-resume-audio-<?= $d['id'] ?>" onclick="resumeDialogue()" style="display:none;" title="Reanudar desde la línea en pausa">
                  <i class="fas fa-play-circle"></i> Resume
                </button>
                <button class="btn-stop-dialogue" id="btn-stop-audio-<?= $d['id'] ?>" onclick="stopAudioPlayback()" style="display:none;" title="Stop audio playback">
                  <i class="fas fa-stop-circle"></i> Stop Dialog
                </button>
              </div>
            </div>
            
            <div class="dialogue-chat" id="dialogue-<?= $d['id'] ?>">
              <?php foreach ($d['turnos'] as $t): ?>
                <?php 
                  $speakerGender = detectarGeneroHablante($t['hablante'] ?? '', (int)($t['orden_turno'] ?? 1));
                  $isRightBubble = ($speakerGender === 'female');
                ?>
                <div class="chat-bubble <?= $isRightBubble ? 'right' : 'left' ?>" 
                     id="turno-<?= $t['id'] ?>" 
                     data-text-en="<?= limpiar($t['texto_en']) ?>"
                     data-speaker="<?= $speakerGender ?>">
                  <div class="chat-sender">
                    <i class="fas fa-<?= $speakerGender === 'male' ? 'mars' : 'venus' ?>" style="margin-right:4px; font-size:0.8rem; color:<?= $speakerGender === 'male' ? 'var(--azul)' : 'var(--naranja)' ?>;"></i><?= limpiar($t['hablante']) ?>
                  </div>
                  <div class="chat-text-en"><?= limpiar($t['texto_en']) ?></div>
                  <div class="chat-text-es"><?= limpiar($t['texto_es']) ?></div>
                  <button class="chat-bubble-play" onclick="speakSingleTurn('turno-<?= $t['id'] ?>')" title="Escuchar este turno (Voz <?= $speakerGender === 'male' ? 'Masculina' : 'Femenina' ?>)">
                    <i class="fas fa-volume-up"></i>
                  </button>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>

        <div style="text-align:center; margin-top:32px; display:flex; justify-content:center; gap:16px; flex-wrap:wrap;">
          <button class="btn btn-verde" id="btn-unlock-moment-3" onclick="finishMomentAndReturn(50)" style="padding:12px 24px; font-weight:800;">
            <i class="fas fa-map-marker-alt" style="margin-right:8px;"></i> Volver al Mapa
          </button>
          <button class="btn btn-azul" onclick="unlockMoment3()" style="padding:12px 24px; font-weight:800;">
            Ir a Practicar (Momento 3) <i class="fas fa-arrow-right" style="margin-left:8px;"></i>
          </button>
        </div>
      </div>
    </section>

    <!-- ==================== MOMENTO 3: PRACTICE ==================== -->
    <section class="moment-pane" id="pane-moment-3" aria-labelledby="tab-moment-3">
      <div class="card-moment">
        <div class="exercise-header">
          <span style="color:var(--azul);" id="exercise-number-indicator">EJERCICIO 1 DE 6</span>
          <span style="color:var(--gris-medio);" id="exercise-score-indicator">Total: 0 / 60 Pts</span>
        </div>

        <?php if (empty($ejercicios)): ?>
          <p style="color:var(--texto-tenue); text-align:center;">No exercises configured for this RAP.</p>
        <?php else: ?>
          <div id="exercises-carousel">
            <?php foreach ($ejercicios as $idx => $ej): ?>
              <div class="exercise-box" id="exercise-box-<?= $idx ?>" data-type="<?= $ej['tipo'] ?>" data-id="<?= $ej['id'] ?>">
                <?php if ($ej['tipo'] !== 'completar_frase'): ?>
                  <div class="exercise-title"><?= limpiar($ej['enunciado']) ?></div>
                <?php endif; ?>
                
                <div class="exercise-content">
                  <?php if ($ej['tipo'] === 'seleccion_multiple' || $ej['tipo'] === 'role_play'): ?>
                    <div class="options-list">
                      <?php foreach ($ej['opciones'] as $opcIdx => $opc): 
                        $badgeLetter = chr(65 + $opcIdx);
                      ?>
                        <div class="option-item" onclick="selectOption('<?= $idx ?>', '<?= $opc['id'] ?>', this)" data-correct="<?= $opc['es_correcta'] ?>" data-retro="<?= limpiar($opc['retroalimentacion']) ?>">
                          <span class="option-badge"><?= $badgeLetter ?></span>
                          <span><?= limpiar($opc['texto']) ?></span>
                        </div>
                      <?php endforeach; ?>
                    </div>

                  <?php elseif ($ej['tipo'] === 'completar_frase'): ?>
                    <?php
                      // Vamos a buscar la palabra correcta en base a opciones
                      $correctWord = '';
                      $wrongWords = [];
                      foreach ($ej['opciones'] as $opc) {
                          if ($opc['es_correcta']) $correctWord = $opc['texto'];
                          else $wrongWords[] = $opc['texto'];
                      }
                      
                      // Reemplazar el marcador "___" por el contenedor en blanco. 
                      // Si no hay "___", buscar la palabra correcta exacta (palabra completa)
                      $enunciadoFormateado = $ej['enunciado'];
                      if (strpos($enunciadoFormateado, '___') !== false) {
                          $enunciadoFormateado = preg_replace('/_{3,}/', '<span class="blank-drop" id="blank-drop-'.$idx.'">???</span>', $enunciadoFormateado, 1);
                      } else {
                          $enunciadoFormateado = preg_replace('/\b' . preg_quote($correctWord, '/') . '\b/i', '<span class="blank-drop" id="blank-drop-'.$idx.'">???</span>', $enunciadoFormateado, 1);
                      }
                      
                      // Unir chips y mezclar
                      $chips = array_merge([$correctWord], $wrongWords);
                      shuffle($chips);
                    ?>
                    <div class="blank-sentence" id="blank-sentence-<?= $idx ?>" data-correct="<?= htmlspecialchars($correctWord, ENT_QUOTES) ?>" data-retro="¡Excelente! Frase completada correctamente.">
                      <?= $enunciadoFormateado ?>
                    </div>
                    <div class="word-bank">
                      <?php foreach ($chips as $c): ?>
                        <button class="word-chip" onclick="fillBlank('<?= $idx ?>', '<?= limpiar($c) ?>', this)"><?= limpiar($c) ?></button>
                      <?php endforeach; ?>
                    </div>

                  <?php elseif ($ej['tipo'] === 'arrastrar_soltar'): ?>
                    <?php 
                      // Parsear parejas separadas por "="
                      $pairs = [];
                      foreach ($ej['opciones'] as $opc) {
                          $parts = explode('=', $opc['texto']);
                          if (count($parts) >= 2) {
                              $enPart = trim($parts[0]);
                              $esPart = trim(implode('=', array_slice($parts, 1))); // soporta '=' en el texto español
                              $pairs[] = ['en' => $enPart, 'es' => $esPart];
                          }
                      }
                      $shuffledEn = array_column($pairs, 'en');
                      shuffle($shuffledEn);
                      $shuffledEs = array_column($pairs, 'es');
                      shuffle($shuffledEs);
                      // Serializar los pares correctos como JSON para el JS
                      $pairsJson = htmlspecialchars(json_encode($pairs), ENT_QUOTES);
                    ?>
                    <!-- Relacionar términos en columnas -->
                    <div class="columns-grid" data-pairs="<?= $pairsJson ?>" data-total-pairs="<?= count($pairs) ?>">
                      <div class="matching-col">
                        <?php foreach ($shuffledEn as $eText): ?>
                          <div class="matching-card" data-col="en" data-value="<?= htmlspecialchars($eText, ENT_QUOTES) ?>" data-exidx="<?= $idx ?>" onclick="selectColumnMatch(this.dataset.exidx, this.dataset.col, this.dataset.value, this)"><?= limpiar($eText) ?></div>
                        <?php endforeach; ?>
                      </div>
                      <div class="matching-col">
                        <?php foreach ($shuffledEs as $sText): ?>
                          <div class="matching-card" data-col="es" data-value="<?= htmlspecialchars($sText, ENT_QUOTES) ?>" data-exidx="<?= $idx ?>" onclick="selectColumnMatch(this.dataset.exidx, this.dataset.col, this.dataset.value, this)"><?= limpiar($sText) ?></div>
                        <?php endforeach; ?>
                      </div>
                    </div>

                  <?php elseif ($ej['tipo'] === 'ordenar_dialogo'): ?>
                    <?php
                      // Opciones contienen el diálogo separado por |
                      $sequence = [];
                      foreach ($ej['opciones'] as $opc) {
                          $parts = explode('|', $opc['texto']);
                          foreach ($parts as $p) {
                              $sequence[] = trim($p);
                          }
                      }
                      $shuffledSeq = $sequence;
                      shuffle($shuffledSeq);
                    ?>
                    <p style="color:var(--texto-tenue); font-size:0.85rem; margin-bottom:12px;">Click cards in chronological order to organize the conversation:</p>
                    <div class="options-list" id="ordered-seq-list-<?= $idx ?>" data-correct-seq="<?= implode('|', $sequence) ?>">
                      <?php foreach ($shuffledSeq as $seqItem): ?>
                        <div class="option-item" onclick="addDialogueOrder('<?= $idx ?>', '<?= limpiar($seqItem) ?>', this)">
                          <span><?= limpiar($seqItem) ?></span>
                        </div>
                      <?php endforeach; ?>
                    </div>
                    <div style="margin-top:20px; font-weight:800; color:var(--azul); display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px;">
                      <span>Organized Conversation:</span>
                      <small style="font-size:0.78rem; color:var(--texto-tenue); font-weight:600;"><i class="fas fa-info-circle" style="margin-right:4px; color:var(--azul);"></i>Haz clic en una opción elegida para quitarla si te equivocaste</small>
                    </div>
                    <div class="dialogue-chat" id="ordered-chat-display-<?= $idx ?>" style="min-height:80px; padding:12px; margin-top:10px;">
                      <div style="color:var(--gris-medio); text-align:center; font-style:italic;" id="ordered-placeholder-<?= $idx ?>">Empty. Click options above to order.</div>
                    </div>

                  <?php elseif ($ej['tipo'] === 'escucha_escribe'): ?>
                    <?php
                      // Buscar respuesta correcta en las opciones
                      $correctWord = $ej['opciones'][0]['texto'] ?? '';
                    ?>
                    <button class="dictation-play-btn" onclick="speakText('<?= limpiar($correctWord) ?>')" title="Escuchar Dictado">
                      <i class="fas fa-volume-up"></i>
                    </button>
                    <input type="text" class="dictation-input" id="dictation-input-<?= $idx ?>" placeholder="Type what you hear..." data-correct="<?= limpiar($correctWord) ?>" autocomplete="off">
                  <?php endif; ?>
                </div>

                <!-- Banner de Validacion -->
                <div class="validation-banner" id="val-banner-<?= $idx ?>">
                  <div class="vb-msg">
                    <i class="fas" id="val-icon-<?= $idx ?>"></i>
                    <div>
                      <div style="font-size:1.15rem; font-weight:800;" id="val-title-<?= $idx ?>">¡Correcto!</div>
                      <div class="vb-expl" id="val-expl-<?= $idx ?>">Explicación corta aquí.</div>
                    </div>
                  </div>
                </div>

                <div style="display:flex; justify-content:flex-end; margin-top:24px; gap:12px;">
                  <?php if ($ej['tipo'] === 'escucha_escribe'): ?>
                    <button class="btn btn-verde" id="btn-validate-<?= $idx ?>" onclick="validateExercise('<?= $idx ?>')" style="padding:12px 28px; font-size:1rem; font-weight:800;">
                      <i class="fas fa-check-circle" style="margin-right:6px;"></i> Verificar
                    </button>
                  <?php endif; ?>
                  <button class="btn btn-azul" id="btn-next-exercise-<?= $idx ?>" onclick="nextExercise('<?= $idx ?>')" style="display:none; padding:12px 28px; font-size:1rem; font-weight:800;">
                    Continuar <i class="fas fa-arrow-right" style="margin-left:6px;"></i>
                  </button>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </section>

    <!-- ==================== MOMENTO 4: QUIZ ==================== -->
    <?php
      // Límite configurado por el administrador para este quiz (HU22); 5 minutos si no tiene
      $limiteQuizSeg = (int) ($quiz['limite_tiempo_seg'] ?? 0);
      if ($limiteQuizSeg <= 0) {
          $limiteQuizSeg = 300;
      }
      $limiteQuizReloj = sprintf('%02d:%02d', intdiv($limiteQuizSeg, 60), $limiteQuizSeg % 60);
    ?>
    <section class="moment-pane" id="pane-moment-4" aria-labelledby="tab-moment-4">
      <div class="card-moment" id="quiz-intro-box">
        <h2 style="color:var(--morado);"><i class="fas fa-graduation-cap" style="margin-right:8px;"></i>Quiz Closure: Prueba tus conocimientos</h2>
        <p style="color:var(--texto-tenue); margin-top:10px; line-height:1.6;">
          Has completado todos los momentos pedagógicos de este Resultado de Aprendizaje (RAP). Toma este quiz final de <strong><?= count($preguntas) ?> preguntas</strong> para evaluar tu nivel y desbloquear oficialmente el siguiente nivel en tu mapa de aprendizaje.
        </p>
        <div style="background:var(--fondo); border:2px solid var(--gris-claro); border-radius:12px; padding:20px; margin:24px 0; display:grid; grid-template-columns:1fr 1fr; gap:16px;">
          <div>
            <div style="font-size:0.75rem; text-transform:uppercase; font-weight:800; color:var(--gris-medio);">Umbral de Aprobación</div>
            <div style="font-size:1.4rem; font-weight:900; color:var(--morado);"><?= (int)($quiz['puntaje_minimo'] ?? 60) ?>%</div>
          </div>
          <div>
            <div style="font-size:0.75rem; text-transform:uppercase; font-weight:800; color:var(--gris-medio);">Límite de tiempo</div>
            <div style="font-size:1.4rem; font-weight:900; color:var(--morado);"><?= intdiv($limiteQuizSeg, 60) ?>:<?= sprintf('%02d', $limiteQuizSeg % 60) ?> min</div>
          </div>
        </div>
        <?php if ($intentosQuiz !== null): ?>
          <!-- HU22: intentos por ronda -->
          <p id="quiz-intentos-info" style="margin:-8px 0 16px 0; font-weight:700; color:var(--texto-tenue); text-align:center;">
            <?php if ($intentosQuiz['sin_limite']): ?>
              <i class="fas fa-infinity" style="margin-right:6px; color:var(--verde);"></i>Intentos sin límite: ya aprobaste este quiz.
            <?php else: ?>
              <i class="fas fa-rotate-right" style="margin-right:6px; color:var(--morado);"></i>Intentos en esta ronda: <span id="quiz-intentos-usados"><?= (int) $intentosQuiz['usados'] ?></span> de <?= (int) $intentosQuiz['limite'] ?>
            <?php endif; ?>
          </p>
        <?php endif; ?>
        <div id="quiz-bloqueado-box" style="display:<?= $quizBloqueado ? 'block' : 'none' ?>; background:var(--fondo); border:2px solid var(--rojo); border-radius:12px; padding:18px; text-align:center; font-weight:700; color:var(--gris-texto);">
          <p style="margin:0 0 12px 0;">
            <i class="fas fa-lock" style="color:var(--rojo); margin-right:6px;"></i>
            Usaste tus <?= (int) ($intentosQuiz['limite'] ?? 0) ?> intentos de esta ronda. Repasa el RAP y termina la práctica (Momento 3) para recibir intentos nuevos. Tu avance y tu mejor puntaje se conservan.
          </p>
          <?php if (!$modoRepaso): ?>
            <a class="btn btn-azul" href="<?= htmlspecialchars($urlRepetir, ENT_QUOTES) ?>" style="text-decoration:none; font-weight:800;">
              <i class="fas fa-redo" style="margin-right:6px;"></i> Repasar el RAP
            </a>
          <?php endif; ?>
        </div>
        <button id="btn-comenzar-quiz" class="btn btn-morado" style="display:<?= $quizBloqueado ? 'none' : 'block' ?>; width:100%; font-size:1.1rem; padding:14px 24px; font-weight:800; text-align:center; box-shadow: 0 4px 0 #a855f7;" onclick="startQuiz()">
          <i class="fas fa-play-circle" style="margin-right:8px;"></i> Comenzar Evaluación
        </button>
      </div>

      <!-- Quiz Player -->
      <div class="card-moment" id="quiz-player-box" style="display:none;">
        <div class="exercise-header">
          <span style="color:var(--morado);" id="quiz-question-indicator">PREGUNTA 1 DE <?= count($preguntas) ?></span>
          <span style="color:var(--rojo);" id="quiz-timer"><i class="fas fa-clock" style="margin-right:4px;"></i><?= $limiteQuizReloj ?></span>
        </div>

        <div id="quiz-questions-wrap">
          <?php foreach ($preguntas as $pIdx => $preg): ?>
            <div class="quiz-question-box" id="quiz-question-box-<?= $pIdx ?>" style="display: <?= $pIdx === 0 ? 'block' : 'none' ?>;" data-id="<?= $preg['id'] ?>">
              <div class="exercise-title"><?= limpiar($preg['texto']) ?></div>
              <div class="options-list">
                <?php foreach ($preg['opciones'] as $optIdx => $optText): 
                  $optLetter = chr(65 + $optIdx);
                ?>
                  <div class="option-item" onclick="selectQuizAnswer('<?= $pIdx ?>', '<?= limpiar($optText) ?>', this)">
                    <span class="option-badge"><?= $optLetter ?></span>
                    <span><?= limpiar($optText) ?></span>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>

        <div style="display:flex; justify-content:space-between; margin-top:28px; align-items:center;">
          <small style="color:var(--gris-medio); font-weight:700;">Recuerda responder todas las preguntas.</small>
          <button class="btn-morado" id="btn-next-quiz-question" onclick="nextQuizQuestion()" disabled>Continuar</button>
        </div>
      </div>

      <!-- Quiz Results -->
      <div class="card-moment" id="quiz-results-box" style="display:none; text-align:center; animation:fadeInUp 0.4s ease;">
        <h2 style="font-size:2rem; font-weight:900;" id="quiz-result-title">Resultados del Quiz</h2>
        <div style="margin:24px auto; width:120px; height:120px; border-radius:50%; display:flex; flex-direction:column; align-items:center; justify-content:center; border:8px solid var(--morado);" id="quiz-result-ring">
          <span style="font-size:2.2rem; font-weight:900; color:var(--gris-texto);" id="quiz-result-score">0%</span>
        </div>
        <p style="font-size:1.1rem; font-weight:700; margin-bottom:16px;" id="quiz-result-msg">¡Has aprobado la lección!</p>
        
        <div style="background:var(--fondo); border:2px solid var(--gris-claro); border-radius:16px; padding:20px; max-width:400px; margin:20px auto; display:grid; grid-template-columns:1fr 1fr; gap:12px;">
          <div>
            <div style="font-size:0.7rem; font-weight:800; color:var(--gris-medio); text-transform:uppercase;">XP Ganados</div>
            <div style="font-size:1.4rem; font-weight:900; color:var(--naranja);" id="quiz-xp-ganados">+0 XP</div>
          </div>
          <div>
            <div style="font-size:0.7rem; font-weight:800; color:var(--gris-medio); text-transform:uppercase;">Insignias ganadas</div>
            <div style="font-size:0.95rem; font-weight:800; color:var(--verde-oscuro); height:33px; display:flex; align-items:center; justify-content:center;" id="quiz-insignia-ganada">Ninguna</div>
          </div>
        </div>

        <!-- Avisos de ascenso de rango (HU15) y de modulo desbloqueado (HU05) -->
        <div id="quiz-avisos" style="display:none; max-width:640px; margin:16px auto 0 auto;"></div>

        <!-- Resumen de cierre del RAP (HU07): fortalezas, mejoras y recomendaciones -->
        <div id="quiz-resumen" style="display:none; text-align:left; max-width:640px; margin:8px auto 0 auto;"></div>

        <div style="display:flex; gap:12px; justify-content:center; flex-wrap:wrap; margin-top:24px;">
          <?php if (!(isset($esPreview) && $esPreview)): ?>
            <!-- HU14: se muestra al aprobar (el RAP queda completado) o si ya lo estaba -->
            <a class="btn btn-azul" id="btn-repetir-rap-resultados" href="<?= htmlspecialchars($urlRepetir, ENT_QUOTES) ?>" style="display:none; text-decoration:none; font-weight:800;">
              <i class="fas fa-redo" style="margin-right:6px;"></i> Repetir RAP
            </a>
          <?php endif; ?>
          <button class="btn-verde" onclick="window.location='<?= PROYECTO_PATH ?>/'">
            Volver al Mapa de Aprendizaje
          </button>
        </div>
      </div>
    </section>

  </main>
</div>

<canvas id="confetti-canvas"></canvas>

<script>
  // Datos inyectados desde PHP
  const vocabulario = <?= json_encode($vocabulario) ?>;
  const marcados = <?= json_encode($marcados) ?>;
  const rapId = <?= json_encode($rap['id']) ?>;
  const totalEjercicios = <?= count($ejercicios) ?>;
  // Ejercicios del módulo respondidos en sesiones anteriores (id => acertó), para
  // retomar el Momento 3 donde quedó el aprendiz en lugar de empezar de cero
  const ejerciciosRespondidos = <?= json_encode((object) ($ejerciciosRespondidos ?? [])) ?>;
  const totalQuizPreguntas = <?= count($preguntas) ?>;
  const quizMinPct = <?= (float)($quiz['puntaje_minimo'] ?? 60.00) ?>;

  // Variables de estado
  let activeTab = 1;
  // HU14: en modo repaso solo el Momento 1 está abierto; los demás se desbloquean al avanzar
  const rapCompletado = <?= $rapCompletado ? 'true' : 'false' ?>;
  // HU22: intentos del quiz en la ronda actual (null en vista previa)
  let intentosQuiz = <?= json_encode($intentosQuiz) ?>;
  let maxTabUnlocked = <?= (isset($esPreview) && $esPreview) ? 4 : ($modoRepaso ? 1 : ($progreso['porcentaje'] >= 75 || $progreso['completado'] ? 4 : ($progreso['porcentaje'] >= 50 ? 3 : ($progreso['porcentaje'] >= 25 ? 2 : 1)))) ?>;
  let vocabIndex = 0;
  let sessionXp = 0;

  // 1. Warm-Up State
  let selectedEn = null;
  let selectedEs = null;
  let matchedCount = 0;

  // 2. Exercises State
  let inicioEjercicioMs = Date.now(); // HU05: mide el tiempo dedicado a cada ejercicio
  let currentExerciseIdx = 0;
  let exercisePoints = 0;
  let answersObj = {};
  let selectedColumnText = { en: '', es: '', enNode: null, esNode: null };
  let selectedOrderSeq = [];

  // 3. Quiz State
  let currentQuizPregIdx = 0;
  let quizAnswers = {};
  let quizTimerInterval = null;
  const quizLimiteSeg = <?= $limiteQuizSeg ?>; // HU22: límite configurado para este quiz
  let quizTimeRemaining = quizLimiteSeg;

  // --- NAVEGACIÓN ENTRE TABS ---
  function switchTab(num) {
    if (num > maxTabUnlocked) {
      alert("🔒 Este momento está bloqueado. Completa el momento actual para desbloquear el siguiente.");
      return;
    }
    document.querySelectorAll('.moment-tab').forEach(el => el.classList.remove('active'));
    document.querySelectorAll('.moment-pane').forEach(el => el.classList.remove('active'));

    document.getElementById('tab-moment-' + num).classList.add('active');
    document.getElementById('pane-moment-' + num).classList.add('active');
    activeTab = num;
  }

  // --- COMUNICACIÓN CON EL SERVIDOR ---
  // Todo lo que el RAP guarda pasa por aquí. Si la sesión caducó, el servidor responde
  // 401: la petición queda en espera, se avisa al aprendiz y se reenvía cuando vuelve a
  // iniciar sesión. Sin esto, el navegador tomaría la página de login como respuesta y
  // el avance se perdería sin aviso.
  let peticionesEnEspera = [];

  function enviarAlServidor(ruta, datos) {
    return new Promise((resolve, reject) => {
      const intentar = () => fetch('<?= PROYECTO_PATH ?>' + ruta, {
        method: 'POST',
        body: datos,
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
      })
      .then(res => {
        if (res.status === 401) {
          peticionesEnEspera.push(intentar);
          avisarSesionExpirada();
          return;
        }
        return res.json().then(resolve);
      })
      .catch(reject);

      intentar();
    });
  }

  function avisarSesionExpirada() {
    let aviso = document.getElementById('aviso-sesion-expirada');
    if (!aviso) {
      aviso = document.createElement('div');
      aviso.id = 'aviso-sesion-expirada';
      aviso.setAttribute('role', 'alert');
      aviso.style.cssText = 'position:fixed; left:50%; bottom:24px; transform:translateX(-50%); z-index:2000; width:min(560px, calc(100% - 32px)); background:var(--blanco); color:var(--gris-texto); border:2px solid var(--rojo); border-radius:16px; padding:18px 20px; box-shadow:0 12px 32px rgba(0,0,0,0.25); flex-direction:column; gap:14px;';
      aviso.innerHTML = `
        <div style="display:flex; gap:10px; align-items:flex-start;">
          <i class="fas fa-lock" style="color:var(--rojo); margin-top:3px;"></i>
          <div>
            <strong>Tu sesión expiró por inactividad.</strong>
            <div style="font-size:0.9rem; margin-top:4px; color:var(--texto-tenue);">Tu avance está en pausa: se guardará en cuanto vuelvas a iniciar sesión.</div>
          </div>
        </div>
        <div style="display:flex; gap:10px; flex-wrap:wrap; justify-content:flex-end;">
          <a class="btn btn-azul" href="<?= PROYECTO_PATH ?>/login" target="_blank" rel="noopener" style="padding:10px 18px; font-weight:800;">Iniciar sesión en otra pestaña</a>
          <button type="button" class="btn btn-verde" onclick="reintentarEnEspera()" style="padding:10px 18px; font-weight:800;">Ya inicié sesión: guardar</button>
        </div>`;
      document.body.appendChild(aviso);
    }
    aviso.style.display = 'flex';
  }

  function reintentarEnEspera() {
    let pendientes = peticionesEnEspera;
    peticionesEnEspera = [];
    let aviso = document.getElementById('aviso-sesion-expirada');
    if (aviso) aviso.style.display = 'none';
    // Si la sesión sigue inactiva, cada petición vuelve a la espera y el aviso reaparece
    pendientes.forEach(intentar => intentar());
  }

  // Avance ya alcanzado en este RAP: la barra nunca retrocede, igual que en el servidor.
  // En modo repaso (HU14) la barra mide solo esta visita y arranca en 0.
  let progresoActual = <?= (float) $pctSesion ?>;

  function pintarProgreso(pct) {
    progresoActual = Math.max(progresoActual, pct);
    let fill = document.getElementById('header-progress-fill');
    let txt = document.getElementById('header-progress-text');
    if (fill) fill.style.width = progresoActual + '%';
    if (txt) txt.textContent = Math.round(progresoActual) + '%';
  }

  // Guarda el avance y lo refleja en la barra del encabezado. Devuelve la
  // petición para poder esperarla antes de salir de la página.
  function saveProgress(pct) {
    pintarProgreso(pct);

    let formData = new FormData();
    formData.append('rap_id', rapId);
    formData.append('porcentaje', pct);

    return enviarAlServidor('/aprendiz/rap/guardar-progreso', formData)
      .catch(() => { /* un fallo de red no debe cortar la lección */ });
  }

  // --- AUDIO / SPEECH SYNTHESIS CON DIFERENCIACIÓN CLARA HOMBRE / MUJER ---
  function speakText(text, gender = 'female') {
    if (!('speechSynthesis' in window)) {
      console.log("Speech synthesis not supported in this browser.");
      return null;
    }

    window.speechSynthesis.cancel(); // Parar audios anteriores
    let utterance = new SpeechSynthesisUtterance(text);
    utterance.lang = 'en-US';
    
    let voices = window.speechSynthesis.getVoices();
    let enVoices = voices.filter(v => v.lang && v.lang.toLowerCase().startsWith('en'));
    
    let isMale = (gender === 'male' || gender === 'hombre');
    
    if (isMale) {
      // Configuración acústica masculina (tono más grave y firme)
      utterance.pitch = 0.78; 
      utterance.rate  = 0.88; 

      if (enVoices.length > 0) {
        let mVoice = enVoices.find(v => {
          let n = v.name.toLowerCase();
          return (n.includes('david') || n.includes('male') || n.includes('guy') || n.includes('george') || 
                  n.includes('mark') || n.includes('james') || n.includes('daniel') || n.includes('alex') || 
                  n.includes('christopher') || n.includes('richard')) && 
                 !n.includes('female') && !n.includes('zira') && !n.includes('jenny');
        });
        if (mVoice) {
          utterance.voice = mVoice;
        } else {
          let altVoice = enVoices.find(v => !v.name.toLowerCase().includes('zira') && !v.name.toLowerCase().includes('jenny'));
          if (altVoice) utterance.voice = altVoice;
        }
      }
    } else {
      // Configuración acústica femenina (tono más agudo, suave y melodioso)
      utterance.pitch = 1.20; 
      utterance.rate  = 0.94; 

      if (enVoices.length > 0) {
        let fVoice = enVoices.find(v => {
          let n = v.name.toLowerCase();
          return n.includes('zira') || n.includes('female') || n.includes('jenny') || 
                 n.includes('aria') || n.includes('samantha') || n.includes('victoria') || 
                 n.includes('karen') || n.includes('catherine') || n.includes('google');
        });
        if (fVoice) {
          utterance.voice = fVoice;
        }
      }
    }

    window.speechSynthesis.speak(utterance);
    return utterance;
  }

  // Cargar voces al iniciar para evitar problemas de sincronía
  if ('speechSynthesis' in window) {
    window.speechSynthesis.onvoiceschanged = () => {};
  }

  // --- MOMENTO 1: WARM-UP MATCHING GAME ---
  function initWarmupMatching() {
    if (vocabulario.length === 0) return;
    
    // Elegir hasta 3 vocablos
    let items = vocabulario.slice(0, 3);
    matchedCount = 0;

    let colEn = document.getElementById('warmup-col-en');
    let colEs = document.getElementById('warmup-col-es');
    if (!colEn || !colEs) return;

    colEn.innerHTML = '';
    colEs.innerHTML = '';

    let itemsEn = [...items];
    let itemsEs = [...items];

    // Mezclar
    itemsEn.sort(() => Math.random() - 0.5);
    itemsEs.sort(() => Math.random() - 0.5);

    itemsEn.forEach(it => {
      let card = document.createElement('div');
      card.className = 'matching-card';
      card.textContent = it.termino_en;
      card.dataset.id = it.id;
      card.onclick = () => selectWarmupCard('en', card);
      colEn.appendChild(card);
    });

    itemsEs.forEach(it => {
      let card = document.createElement('div');
      card.className = 'matching-card';
      card.textContent = it.termino_es;
      card.dataset.id = it.id;
      card.onclick = () => selectWarmupCard('es', card);
      colEs.appendChild(card);
    });
  }

  function selectWarmupCard(lang, cardNode) {
    if (cardNode.classList.contains('correct')) return;

    if (lang === 'en') {
      document.querySelectorAll('#warmup-col-en .matching-card').forEach(n => n.classList.remove('selected', 'incorrect'));
      selectedEn = cardNode;
      selectedEn.classList.add('selected');
    } else {
      document.querySelectorAll('#warmup-col-es .matching-card').forEach(n => n.classList.remove('selected', 'incorrect'));
      selectedEs = cardNode;
      selectedEs.classList.add('selected');
    }

    if (selectedEn && selectedEs) {
      let idEn = selectedEn.dataset.id;
      let idEs = selectedEs.dataset.id;

      if (idEn === idEs) {
        // MATCH correcto!
        selectedEn.className = 'matching-card correct';
        selectedEs.className = 'matching-card correct';
        matchedCount++;
        
        if (window.SonidosApp) SonidosApp.playCorrect();
        speakText(selectedEn.textContent);

        selectedEn = null;
        selectedEs = null;

        if (matchedCount === 3) {
          document.getElementById('warmup-success-msg').style.display = 'block';
          unlockMoment(2);
          saveProgress(25);
        }
      } else {
        // MATCH incorrecto
        let nodeEn = selectedEn;
        let nodeEs = selectedEs;
        nodeEn.classList.add('incorrect');
        nodeEs.classList.add('incorrect');
        if (window.SonidosApp) SonidosApp.playIncorrect();
        setTimeout(() => {
          nodeEn.classList.remove('selected', 'incorrect');
          nodeEs.classList.remove('selected', 'incorrect');
        }, 1000);
        selectedEn = null;
        selectedEs = null;
      }
    }
  }

  function unlockMoment(num) {
    if (num > maxTabUnlocked) {
      maxTabUnlocked = num;
      let tab = document.getElementById('tab-moment-' + num);
      if (tab) tab.classList.remove('locked');
    }
  }

  // --- MOMENTO 2: VOCABULARIO SLIDER ---
  function showVocabItem() {
    if (vocabulario.length === 0) return;
    let item = vocabulario[vocabIndex];

    document.getElementById('vocab-word-en').textContent = item.termino_en;
    document.getElementById('vocab-word-ipa').textContent = item.transcripcion_ipa || '';
    document.getElementById('vocab-word-ex').textContent = item.oracion_ejemplo || '';
    document.getElementById('vocab-word-es').textContent = item.termino_es;
    
    // Counter
    document.getElementById('vocab-counter').textContent = (vocabIndex + 1) + ' / ' + vocabulario.length;

    // Reset flipped
    document.getElementById('current-flashcard').classList.remove('flipped');

    // Star icon
    let isMarcado = marcados.includes(item.id);
    let star = document.getElementById('btn-star-vocab');
    if (star) {
      star.className = isMarcado ? 'btn-star-mark active' : 'btn-star-mark';
      star.innerHTML = isMarcado ? '<i class="fas fa-star"></i>' : '<i class="far fa-star"></i>';
    }
  }

  function flipCard() {
    document.getElementById('current-flashcard').classList.toggle('flipped');
  }

  function toggleStar(event) {
    event.stopPropagation(); // Evitar voltear la tarjeta
    let item = vocabulario[vocabIndex];
    let star = document.getElementById('btn-star-vocab');
    
    let formData = new FormData();
    formData.append('vocabulario_id', item.id);

    enviarAlServidor('/aprendiz/rap/marcar-vocabulario', formData)
    .then(d => {
      if (d.exito) {
        if (d.marcado) {
          if (!marcados.includes(item.id)) marcados.push(item.id);
          star.className = 'btn-star-mark active';
          star.innerHTML = '<i class="fas fa-star"></i>';
        } else {
          let idx = marcados.indexOf(item.id);
          if (idx !== -1) marcados.splice(idx, 1);
          star.className = 'btn-star-mark';
          star.innerHTML = '<i class="far fa-star"></i>';
        }
      }
    });
  }

  function speakVocab(event) {
    event.stopPropagation();
    let word = vocabulario[vocabIndex].termino_en;
    speakText(word, 'female');
  }

  function prevVocab() {
    if (vocabIndex > 0) {
      vocabIndex--;
      showVocabItem();
    }
  }

  function nextVocab() {
    if (vocabIndex < vocabulario.length - 1) {
      vocabIndex++;
      showVocabItem();
    }
  }

  // --- STORYBOOK DIALOGUE PLAYBACK & HIGHLIGHT (HU13) ---
  // Un solo reproductor para la página: la síntesis de voz tiene un único canal.
  // Detener, pausar, retroceder o tocar otro turno abren una sesión nueva y los
  // eventos de la voz anterior se ignoran. Antes, al cancelar, Chrome disparaba
  // onend y el diálogo seguía avanzando aunque se hubiera pulsado Stop.
  let dialogTimeoutList = [];
  const reproductorDialogo = { diaId: null, burbujas: [], idx: 0, estado: 'detenido', sesion: 0 };

  function cortarVozDialogo() {
    reproductorDialogo.sesion++;
    if ('speechSynthesis' in window) {
      window.speechSynthesis.cancel();
    }
    dialogTimeoutList.forEach(t => clearTimeout(t));
    dialogTimeoutList = [];
  }

  function pintarControlesDialogo() {
    let r = reproductorDialogo;
    document.querySelectorAll('.btn-stop-dialogue, .btn-control-dialogo').forEach(b => b.style.display = 'none');
    if (r.estado === 'detenido' || !r.diaId) return;

    let mostrar = (prefijo) => {
      let boton = document.getElementById(prefijo + r.diaId);
      if (boton) boton.style.display = 'inline-flex';
    };
    mostrar('btn-stop-audio-');
    mostrar('btn-prev-turn-');
    mostrar(r.estado === 'pausado' ? 'btn-resume-audio-' : 'btn-pause-audio-');
  }

  function resaltarTurno(idx) {
    reproductorDialogo.burbujas.forEach((b, i) => b.classList.toggle('active-highlight', i === idx));
  }

  function stopAudioPlayback() {
    cortarVozDialogo();
    document.querySelectorAll('.chat-bubble').forEach(b => b.classList.remove('active-highlight'));
    reproductorDialogo.estado = 'detenido';
    pintarControlesDialogo();
  }

  function reproducirTurnoDialogo(idx) {
    let r = reproductorDialogo;
    if (idx >= r.burbujas.length) {
      stopAudioPlayback();
      return;
    }

    r.idx = idx;
    r.estado = 'reproduciendo';
    let sesion = r.sesion;
    let bubble = r.burbujas[idx];
    resaltarTurno(idx);
    if (bubble.scrollIntoView) bubble.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    pintarControlesDialogo();

    let utterance = speakText(bubble.getAttribute('data-text-en'), bubble.getAttribute('data-speaker') || 'female');
    if (!utterance) {
      // Navegador sin síntesis de voz
      stopAudioPlayback();
      return;
    }
    utterance.onend = () => {
      if (sesion !== r.sesion) return; // se detuvo, pausó o cambió de turno
      let timeout = setTimeout(() => {
        if (sesion === r.sesion) reproducirTurnoDialogo(idx + 1);
      }, 500);
      dialogTimeoutList.push(timeout);
    };
    utterance.onerror = () => {
      if (sesion === r.sesion) stopAudioPlayback();
    };
  }

  function playFullDialogue(diaElementId, desde = 0) {
    stopAudioPlayback();
    let container = document.getElementById(diaElementId);
    if (!container) return;

    reproductorDialogo.diaId = diaElementId.replace('dialogue-', '');
    reproductorDialogo.burbujas = Array.from(container.querySelectorAll('.chat-bubble'));
    reproducirTurnoDialogo(desde);
  }

  function pauseDialogue() {
    let r = reproductorDialogo;
    if (r.estado !== 'reproduciendo') return;
    cortarVozDialogo();
    r.estado = 'pausado';
    resaltarTurno(r.idx); // la línea en pausa sigue resaltada
    pintarControlesDialogo();
  }

  // Reanuda repitiendo desde el principio la línea en pausa: pause() y resume() de la
  // síntesis de voz no funcionan igual en todos los navegadores
  function resumeDialogue() {
    let r = reproductorDialogo;
    if (r.estado !== 'pausado') return;
    reproducirTurnoDialogo(r.idx);
  }

  function previousTurn() {
    let r = reproductorDialogo;
    if (r.estado === 'detenido') return;

    let anterior = Math.max(0, r.idx - 1);
    if (r.estado === 'pausado') {
      r.idx = anterior;
      resaltarTurno(anterior);
      return;
    }
    cortarVozDialogo();
    reproducirTurnoDialogo(anterior);
  }

  function speakSingleTurn(turnId) {
    stopAudioPlayback();
    let bubble = document.getElementById(turnId);
    if (!bubble) return;

    let sesion = reproductorDialogo.sesion;
    bubble.classList.add('active-highlight');
    let utterance = speakText(bubble.getAttribute('data-text-en'), bubble.getAttribute('data-speaker') || 'female');
    if (!utterance) {
      bubble.classList.remove('active-highlight');
      return;
    }
    utterance.onend = () => {
      if (sesion === reproductorDialogo.sesion) bubble.classList.remove('active-highlight');
    };
  }

  function unlockMoment3() {
    unlockMoment(3);
    saveProgress(50);
    switchTab(3);
  }

  // --- MOMENTO 3: EJERCICIOS PRACTICOS PLAYER ---
  function esRespondido(idx) {
    let box = document.getElementById('exercise-box-' + idx);
    return !!box && Object.prototype.hasOwnProperty.call(ejerciciosRespondidos, box.dataset.id);
  }

  // Primer ejercicio sin responder desde "desde", o totalEjercicios si no queda ninguno
  function siguientePendiente(desde) {
    let idx = desde;
    while (idx < totalEjercicios && esRespondido(idx)) idx++;
    return idx;
  }

  function mostrarEjercicio(idx) {
    currentExerciseIdx = idx;
    selectedColumnText = { en: '', es: '', enNode: null, esNode: null };
    selectedOrderSeq = [];

    let box = document.getElementById('exercise-box-' + idx);
    box.style.display = 'block';
    box.classList.add('active');
    inicioEjercicioMs = Date.now();
    updateExerciseHeader();
  }

  function initExercises() {
    if (totalEjercicios === 0) return;
    answersObj = {};

    // Ocultar todos
    document.querySelectorAll('.exercise-box').forEach(b => {
      b.style.display = 'none';
      b.classList.remove('active');
    });

    // Recuperar los puntos de lo ya respondido y seguir en el primer ejercicio pendiente
    exercisePoints = 0;
    for (let i = 0; i < totalEjercicios; i++) {
      let box = document.getElementById('exercise-box-' + i);
      if (esRespondido(i) && ejerciciosRespondidos[box.dataset.id]) exercisePoints += 10;
    }

    let inicio = siguientePendiente(0);
    if (inicio >= totalEjercicios) {
      // Ya respondió todo en sesiones anteriores, pero el 75% no llegó a guardarse
      currentExerciseIdx = totalEjercicios - 1;
      updateExerciseHeader();
      completarMomento3();
      return;
    }

    mostrarEjercicio(inicio);
  }

  function updateExerciseHeader() {
    let ind = document.getElementById('exercise-number-indicator');
    let sc = document.getElementById('exercise-score-indicator');
    if (ind && sc) {
      ind.textContent = `EJERCICIO ${Math.min(currentExerciseIdx + 1, totalEjercicios)} DE ${totalEjercicios}`;
      sc.textContent = `Total: ${exercisePoints} / ${totalEjercicios * 10} Pts`;
    }
  }

  // MC & Role-play Selection handler
  function selectOption(exIdx, opcId, node) {
    let box = document.getElementById('exercise-box-' + exIdx);
    box.querySelectorAll('.option-item').forEach(n => n.classList.remove('selected'));
    node.classList.add('selected');
    
    let isCorrect = parseInt(node.dataset.correct) === 1;
    let retroText = node.dataset.retro;
    if (!isCorrect) {
      let correctNode = box.querySelector('.option-item[data-correct="1"] span:last-child');
      let correctAns = correctNode ? correctNode.textContent : '';
      retroText = 'La respuesta correcta es: ' + correctAns;
    }
    
    answersObj[exIdx] = {
      isCorrect: isCorrect,
      retro: retroText,
      text: node.querySelector('span:last-child').textContent,
      opcionId: opcId
    };
    validateExercise(exIdx);
  }

  // Fill in blanks handler
  function fillBlank(exIdx, text, chipNode) {
    let drop = document.getElementById('blank-drop-' + exIdx);
    if (!drop) return;

    // Revert previous chip if used
    let box = document.getElementById('exercise-box-' + exIdx);
    box.querySelectorAll('.word-chip').forEach(c => {
      if (c.textContent === drop.textContent) {
        c.classList.remove('used');
      }
    });

    drop.textContent = text;
    chipNode.classList.add('used');

    let correctVal = document.getElementById('blank-sentence-' + exIdx).dataset.correct;

    let isCorrect = text.toLowerCase().trim() === correctVal.toLowerCase().trim();
    answersObj[exIdx] = {
      isCorrect: isCorrect,
      retro: isCorrect ? '¡Frase completada correctamente!' : ('La respuesta correcta es: ' + correctVal),
      text: text
    };
    validateExercise(exIdx);
  }

  function selectColumnMatch(exIdx, column, text, node) {
    let box = document.getElementById('exercise-box-' + exIdx);

    // No permitir seleccionar tarjetas ya emparejadas
    if (node.classList.contains('correct')) return;

    if (column === 'en') {
      box.querySelectorAll('.matching-col:first-child .matching-card').forEach(n => n.classList.remove('selected'));
      selectedColumnText.en = text;
      selectedColumnText.enNode = node;
      node.classList.add('selected');
    } else {
      box.querySelectorAll('.matching-col:last-child .matching-card').forEach(n => n.classList.remove('selected'));
      selectedColumnText.es = text;
      selectedColumnText.esNode = node;
      node.classList.add('selected');
    }

    // Si ambos seleccionados, verificar contra los pares guardados en data-pairs
    if (selectedColumnText.en && selectedColumnText.es) {
      let grid = box.querySelector('.columns-grid');
      let pairs = JSON.parse(grid.dataset.pairs || '[]');
      let totalPairs = parseInt(grid.dataset.totalPairs || '0');

      // Comparar ignorando mayúsculas/minúsculas y espacios extras
      let selEn = selectedColumnText.en.trim().toLowerCase();
      let selEs = selectedColumnText.es.trim().toLowerCase();
      let correctMatch = pairs.some(p =>
        p.en.trim().toLowerCase() === selEn && p.es.trim().toLowerCase() === selEs
      );

      let nEn = selectedColumnText.enNode;
      let nEs = selectedColumnText.esNode;

      if (correctMatch) {
        nEn.className = 'matching-card correct';
        nEs.className = 'matching-card correct';
        speakText(selectedColumnText.en);
      } else {
        nEn.classList.add('incorrect');
        nEs.classList.add('incorrect');
        setTimeout(() => {
          nEn.classList.remove('selected', 'incorrect');
          nEs.classList.remove('selected', 'incorrect');
        }, 800);
      }

      // Limpiar selección
      selectedColumnText = { en: '', es: '', enNode: null, esNode: null };

      // Comprobar si se completaron TODOS los pares
      let totalCorrects = box.querySelectorAll('.matching-card.correct').length;
      if (totalCorrects === totalPairs * 2) {
        answersObj[exIdx] = {
          isCorrect: true,
          retro: '¡Excelente! Emparejaste todos los términos correctamente.',
          text: 'All matches completed'
        };
        validateExercise(exIdx);
      }
    }
  }

  // Order Dialogue handler
  function addDialogueOrder(exIdx, itemText, node) {
    if (node.style.opacity === '0.3') return;

    node.style.opacity = '0.3';
    node.style.pointerEvents = 'none';

    let displayBox = document.getElementById('ordered-chat-display-' + exIdx);
    let placeholder = document.getElementById('ordered-placeholder-' + exIdx);
    if (placeholder) placeholder.style.display = 'none';

    let bubble = document.createElement('div');
    bubble.className = 'chat-bubble left';
    bubble.style.width = '100%';
    bubble.style.margin = '4px 0';
    bubble.style.cursor = 'pointer';
    bubble.title = 'Haz clic para remover esta opción si te equivocaste';
    bubble.style.transition = 'all 0.2s ease';
    bubble.innerHTML = `<div>${itemText}</div>`;

    bubble.onclick = function() {
      if (displayBox.contains(bubble)) {
        displayBox.removeChild(bubble);
      }
      node.style.opacity = '1';
      node.style.pointerEvents = 'auto';

      let seqIndex = selectedOrderSeq.indexOf(itemText);
      if (seqIndex !== -1) {
        selectedOrderSeq.splice(seqIndex, 1);
      }

      let remaining = displayBox.querySelectorAll('.chat-bubble');
      if (remaining.length === 0 && placeholder) {
        placeholder.style.display = 'block';
      }
    };

    displayBox.appendChild(bubble);
    selectedOrderSeq.push(itemText);

    // Comparar longitud para validar
    let correctSeqText = document.getElementById('ordered-seq-list-' + exIdx).dataset.correctSeq;
    let correctArr = correctSeqText.split('|');

    if (selectedOrderSeq.length === correctArr.length) {
      let isCorrect = selectedOrderSeq.every((val, i) => val.trim() === correctArr[i].trim());
      let formattedCorrect = correctArr.map((line, idx) => `${idx + 1}. ${line.trim()}`).join(' ');
      let retroMsg = isCorrect ? '¡Has ordenado perfectamente la conversación!' : ('La respuesta correcta es el orden cronológico: ' + formattedCorrect);
      
      answersObj[exIdx] = {
        isCorrect: isCorrect,
        retro: retroMsg,
        text: selectedOrderSeq.join(' | ')
      };
      validateExercise(exIdx);
    }
  }

  // Validador de ejercicio
  function validateExercise(exIdx) {
    let box = document.getElementById('exercise-box-' + exIdx);
    let type = box.dataset.type;
    let ans = answersObj[exIdx];

    // Para dictado, recolectar la respuesta de la caja
    if (type === 'escucha_escribe') {
      let input = document.getElementById('dictation-input-' + exIdx);
      let text = input.value.trim().toLowerCase();
      let correct = input.dataset.correct.toLowerCase().trim();
      let isCorrect = text === correct;
      ans = {
        isCorrect: isCorrect,
        retro: isCorrect ? '¡Correcto! Has registrado la palabra en las Notas de Enfermería.' : `La respuesta correcta es: "${correct}".`,
        text: text
      };
      answersObj[exIdx] = ans;
    }

    if (!ans) {
      alert("Por favor selecciona o ingresa una respuesta primero.");
      return;
    }

    // Ocultar botón validar si existe
    let btnVal = document.getElementById('btn-validate-' + exIdx);
    if (btnVal) btnVal.style.display = 'none';

    // Mostrar Banner
    let banner = document.getElementById('val-banner-' + exIdx);
    let icon = document.getElementById('val-icon-' + exIdx);
    let title = document.getElementById('val-title-' + exIdx);
    let expl = document.getElementById('val-expl-' + exIdx);

    if (ans.isCorrect) {
      banner.className = 'validation-banner correct';
      icon.className = 'fas fa-check-circle';
      title.textContent = '¡Excelente trabajo!';
      expl.textContent = ans.retro || '¡Respuesta correcta!';
      if (window.SonidosApp) SonidosApp.playCorrect();
      
      // Dar puntos XP en caliente para la UI
      exercisePoints += 10;
      sessionXp += 10;
      document.getElementById('session-xp').textContent = `${sessionXp} XP`;
    } else {
      banner.className = 'validation-banner incorrect';
      icon.className = 'fas fa-times-circle';
      title.textContent = 'Respuesta incorrecta';
      expl.textContent = ans.retro || 'Inténtalo de nuevo en la siguiente sesión.';
      if (window.SonidosApp) SonidosApp.playIncorrect();
    }

    // Dejar rastro del intento en la base de datos (HU07)
    registrarIntentoEjercicio(box.dataset.id, ans);

    // Mostrar continuar
    document.getElementById('btn-next-exercise-' + exIdx).style.display = 'inline-block';
    
    // Bloquear inputs para que no editen
    box.querySelectorAll('.option-item, .word-chip, .matching-card').forEach(n => {
      n.style.pointerEvents = 'none';
    });
    let inp = box.querySelector('.dictation-input');
    if (inp) inp.disabled = true;

    updateExerciseHeader();
  }

  // Envia el intento del ejercicio sin bloquear la interfaz: el aprendiz ya vio
  // su retroalimentacion y no debe esperar a la red para continuar.
  function registrarIntentoEjercicio(ejercicioId, ans) {
    if (!ejercicioId || !ans) return;

    let datos = new FormData();
    datos.append('ejercicio_id', ejercicioId);
    datos.append('es_correcto', ans.isCorrect ? 1 : 0);
    datos.append('respuesta', ans.text || '');
    datos.append('tiempo_respuesta_ms', Math.max(0, Date.now() - inicioEjercicioMs));
    if (ans.opcionId) datos.append('opcion_id', ans.opcionId);

    enviarAlServidor('/aprendiz/rap/guardar-ejercicio', datos)
      .catch(() => { /* un fallo de red no debe cortar la leccion */ });
  }

  function nextExercise(exIdx) {
    let currentBox = document.getElementById('exercise-box-' + exIdx);
    currentBox.style.display = 'none';
    currentBox.classList.remove('active');

    // Salta los que ya estaban respondidos de una sesión anterior
    let nextIdx = siguientePendiente(parseInt(exIdx) + 1);

    if (nextIdx < totalEjercicios) {
      mostrarEjercicio(nextIdx);
    } else {
      // Completó todos los ejercicios del Momento 3!
      completarMomento3();
    }
  }

  let momento3Completado = false;

  function completarMomento3() {
    // Se llama al terminar la práctica o al cargar si ya estaba toda respondida
    if (momento3Completado) return;
    momento3Completado = true;

    saveProgress(75).then(() => {
      unlockMoment(4);
      abrirRondaQuiz();
      let carousel = document.getElementById('exercises-carousel');
      let finishBox = document.createElement('div');
      finishBox.className = 'card-moment';
      finishBox.style.cssText = 'text-align:center; padding:32px 20px; animation:fadeInUp 0.3s ease;';
      finishBox.innerHTML = `
        <h3 style="color:var(--verde); font-size:1.5rem; font-weight:800; margin-bottom:12px;">
          <i class="fas fa-check-circle" style="margin-right:8px;"></i>¡Momento 3 (Práctica) Completado!
        </h3>
        <p style="color:var(--texto-tenue); margin-bottom:24px;">Has realizado los ejercicios prácticos. El Momento 4 (Quiz) ya está desbloqueado.</p>
        <div style="display:flex; justify-content:center; gap:16px; flex-wrap:wrap; margin-top:20px;">
          <button class="btn btn-verde" onclick="finishMomentAndReturn(75)" style="padding:12px 24px; font-weight:800;">
            <i class="fas fa-map-marker-alt" style="margin-right:8px;"></i> Volver al Mapa
          </button>
          <button class="btn btn-morado" onclick="switchTab(4)" style="padding:12px 24px; font-weight:800;">
            Comenzar Quiz <i class="fas fa-award" style="margin-left:8px;"></i>
          </button>
        </div>
      `;
      carousel.appendChild(finishBox);
    });
  }

  // HU22: al terminar la práctica el servidor abre una ronda nueva de intentos;
  // se refleja en el Momento 4 sin recargar la página
  function abrirRondaQuiz() {
    if (!intentosQuiz || intentosQuiz.sin_limite) return;
    intentosQuiz.usados = 0;
    intentosQuiz.restantes = intentosQuiz.limite;
    intentosQuiz.bloqueado = false;

    let usados = document.getElementById('quiz-intentos-usados');
    if (usados) usados.textContent = '0';
    let bloqueado = document.getElementById('quiz-bloqueado-box');
    if (bloqueado) bloqueado.style.display = 'none';
    let comenzar = document.getElementById('btn-comenzar-quiz');
    if (comenzar) comenzar.style.display = 'block';
  }

  // --- MOMENTO 4: QUIZ EVALUATION CLOSURE ---
  function startQuiz() {
    document.getElementById('quiz-intro-box').style.display = 'none';
    document.getElementById('quiz-player-box').style.display = 'block';
    currentQuizPregIdx = 0;
    quizAnswers = {};
    quizTimeRemaining = quizLimiteSeg;
    
    // Mostrar primera pregunta
    document.querySelectorAll('.quiz-question-box').forEach(b => b.style.display = 'none');
    document.getElementById('quiz-question-box-0').style.display = 'block';

    updateQuizHeader();

    // Iniciar Temporizador
    if (quizTimerInterval) clearInterval(quizTimerInterval);
    quizTimerInterval = setInterval(() => {
      quizTimeRemaining--;
      updateQuizTimer();
      if (quizTimeRemaining <= 0) {
        clearInterval(quizTimerInterval);
        submitQuiz();
      }
    }, 1000);
  }

  function updateQuizHeader() {
    let ind = document.getElementById('quiz-question-indicator');
    if (ind) {
      ind.textContent = `PREGUNTA ${currentQuizPregIdx + 1} DE ${totalQuizPreguntas}`;
    }
    let btn = document.getElementById('btn-next-quiz-question');
    if (btn) {
      btn.disabled = !quizAnswers[currentQuizPregIdx];
      btn.textContent = (currentQuizPregIdx === totalQuizPreguntas - 1) ? 'Enviar Respuestas' : 'Continuar';
    }
  }

  function updateQuizTimer() {
    let timerSpan = document.getElementById('quiz-timer');
    if (!timerSpan) return;
    
    let mins = Math.floor(quizTimeRemaining / 60);
    let secs = quizTimeRemaining % 60;
    timerSpan.innerHTML = `<i class="fas fa-clock" style="margin-right:4px;"></i>${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
  }

  function selectQuizAnswer(pregIdx, answerText, node) {
    let box = document.getElementById('quiz-question-box-' + pregIdx);
    box.querySelectorAll('.option-item').forEach(n => n.classList.remove('selected'));
    node.classList.add('selected');

    quizAnswers[pregIdx] = {
      pregunta_id: box.dataset.id,
      respuesta: answerText
    };

    updateQuizHeader();
  }

  function nextQuizQuestion() {
    if (!quizAnswers[currentQuizPregIdx]) return;

    let currentBox = document.getElementById('quiz-question-box-' + currentQuizPregIdx);
    currentBox.style.display = 'none';

    let nextIdx = currentQuizPregIdx + 1;
    if (nextIdx < totalQuizPreguntas) {
      currentQuizPregIdx = nextIdx;
      let nextBox = document.getElementById('quiz-question-box-' + nextIdx);
      nextBox.style.display = 'block';
      updateQuizHeader();
    } else {
      clearInterval(quizTimerInterval);
      submitQuiz();
    }
  }

  function submitQuiz() {
    // Preparar respuestas
    let answersData = {};
    for (let key in quizAnswers) {
      answersData[quizAnswers[key].pregunta_id] = quizAnswers[key].respuesta;
    }

    let duracion = quizLimiteSeg - quizTimeRemaining;

    let formData = new FormData();
    formData.append('rap_id', rapId);
    formData.append('duracion_seg', duracion);
    
    for (let pId in answersData) {
      formData.append(`respuestas[${pId}]`, answersData[pId]);
    }

    enviarAlServidor('/aprendiz/rap/guardar-quiz', formData)
    .then(data => {
      if (data.exito) {
        showQuizResults(data);
      } else if (data.bloqueado) {
        // HU22: el servidor rechazó el intento; la página vuelve con el aviso de bloqueo
        alert(data.error);
        window.location.reload();
      } else {
        alert("Ocurrió un error al procesar el Quiz: " + data.error);
      }
    })
    .catch(() => {
      alert("No se pudo enviar el quiz. Revisa tu conexión y vuelve a presentarlo.");
    });
  }

  function showQuizResults(data) {
    document.getElementById('quiz-player-box').style.display = 'none';
    document.getElementById('quiz-results-box').style.display = 'block';

    let pct = Math.round(data.puntaje);
    document.getElementById('quiz-result-score').textContent = pct + '%';
    
    let ring = document.getElementById('quiz-result-ring');
    let title = document.getElementById('quiz-result-title');
    let msg = document.getElementById('quiz-result-msg');
    let xpBox = document.getElementById('quiz-xp-ganados');
    let badgeBox = document.getElementById('quiz-insignia-ganada');

    if (data.aprobado) {
      ring.style.borderColor = 'var(--verde)';
      title.textContent = '¡Felicidades!';
      title.style.color = 'var(--verde)';
      msg.textContent = 'Has aprobado la lección y desbloqueado nuevos contenidos.';
      xpBox.textContent = `+${data.xp_ganados} XP`;
      badgeBox.textContent = data.insignia_ganada || 'Quiz Completado';

      if (window.SonidosApp) SonidosApp.playVictory();

      // Confetti!
      triggerConfetti();
      
      // Actualizar XP en la barra superior en caliente
      sessionXp += data.xp_ganados;
      document.getElementById('session-xp').textContent = `${sessionXp} XP`;
      
      // Actualizar barra del encabezado al 100%
      pintarProgreso(100);
    } else {
      ring.style.borderColor = 'var(--rojo)';
      title.textContent = 'Sigue practicando';
      title.style.color = 'var(--rojo)';
      msg.textContent = `Has obtenido ${pct}%. Necesitas un mínimo de ${quizMinPct}% para aprobar la lección.`;
      // HU22: intentos que quedan en la ronda
      if (data.intentos && !data.intentos.sin_limite) {
        msg.textContent += data.intentos.bloqueado
          ? ` Usaste tus ${data.intentos.limite} intentos de esta ronda: repasa el RAP y termina la práctica para recibir intentos nuevos.`
          : ` Te quedan ${data.intentos.restantes} de ${data.intentos.limite} intentos en esta ronda.`;
      }
      xpBox.textContent = '+0 XP';
      badgeBox.textContent = 'Ninguna';
      if (window.SonidosApp) SonidosApp.playIncorrect();
    }

    pintarAvisos(data);
    pintarResumenRap(data.resumen);

    // HU14: aprobar deja el RAP completado, así que ya se puede repetir
    let btnRepetir = document.getElementById('btn-repetir-rap-resultados');
    let quizAgotado = !!(data.intentos && data.intentos.bloqueado);
    if (btnRepetir && (data.aprobado || rapCompletado || quizAgotado)) {
      // HU22: con los intentos agotados el mismo botón lleva a repasar el RAP
      if (quizAgotado && !data.aprobado) {
        btnRepetir.innerHTML = '<i class="fas fa-redo" style="margin-right:6px;"></i> Repasar el RAP';
      }
      btnRepetir.style.display = 'inline-block';
    }
  }

  // Avisos de logro: subida de rango de perfil (HU15) y modulo desbloqueado (HU05).
  // El servidor solo los manda en el momento exacto en que ocurren, no en cada intento.
  function pintarAvisos(data) {
    const caja = document.getElementById('quiz-avisos');
    if (!caja) return;

    const avisos = [];

    if (data.subio_nivel > 0) {
      avisos.push({
        icono: 'fa-arrow-up-right-dots',
        color: 'var(--morado)',
        titulo: '¡Subiste de rango!',
        texto: 'Alcanzaste el Nivel ' + data.subio_nivel + ' de tu perfil clínico. Míralo en tu perfil.'
      });
    }

    if (data.modulo_desbloqueado) {
      avisos.push({
        icono: 'fa-lock-open',
        color: 'var(--verde)',
        titulo: '¡Nuevo módulo desbloqueado!',
        texto: 'Ya puedes entrar a ' + data.modulo_desbloqueado + ' desde tu mapa de aprendizaje.'
      });
    }

    if (!avisos.length) {
      caja.style.display = 'none';
      return;
    }

    caja.innerHTML = avisos.map(a => `
      <div style="display:flex; gap:12px; align-items:center; text-align:left; border:2px solid ${a.color};
                  border-radius:16px; padding:14px 18px; margin-bottom:10px; background:var(--fondo);">
        <i class="fas ${a.icono}" style="color:${a.color}; font-size:1.5rem;"></i>
        <div>
          <div style="font-weight:900; color:${a.color};">${a.titulo}</div>
          <div style="font-weight:600; font-size:0.85rem;">${a.texto}</div>
        </div>
      </div>`).join('');
    caja.style.display = 'block';

    if (window.SonidosApp) SonidosApp.playVictory();
  }

  // Resumen de cierre del RAP (HU07). El servidor manda fortalezas, areas de
  // mejora y recomendaciones ya calculadas con el estado real del aprendiz.
  function pintarResumenRap(resumen) {
    const caja = document.getElementById('quiz-resumen');
    if (!caja) return;

    // En vista previa (admin/instructor) el servidor manda resumen = null
    if (!resumen) {
      caja.style.display = 'none';
      return;
    }

    const esc = (t) => String(t == null ? '' : t)
      .replace(/&/g, '&amp;').replace(/</g, '&lt;')
      .replace(/>/g, '&gt;').replace(/"/g, '&quot;');

    const bloque = (titulo, icono, color, cuerpo) => `
      <div style="background:var(--fondo); border:2px solid var(--gris-claro); border-radius:16px; padding:16px 20px; margin-top:12px;">
        <div style="font-size:0.72rem; font-weight:900; color:${color}; text-transform:uppercase; letter-spacing:0.05em; margin-bottom:10px;">
          <i class="fas ${icono}"></i> ${titulo}
        </div>
        ${cuerpo}
      </div>`;

    let html = `<div style="text-align:center; font-weight:800; color:var(--gris-medio); font-size:0.85rem; margin-top:8px;">
        Acertaste ${resumen.correctas} de ${resumen.total} preguntas
      </div>`;

    if (resumen.fortalezas && resumen.fortalezas.length) {
      html += bloque('Lo que dominas', 'fa-circle-check', 'var(--verde)',
        '<ul style="margin:0; padding-left:18px; list-style:disc;">' +
        resumen.fortalezas.map(f => `<li style="font-size:0.85rem; font-weight:600; margin-bottom:4px;">${esc(f)}</li>`).join('') +
        '</ul>');
    }

    if (resumen.mejoras && resumen.mejoras.length) {
      html += bloque('Para reforzar', 'fa-triangle-exclamation', 'var(--naranja)',
        resumen.mejoras.map(m => `
          <div style="font-size:0.85rem; margin-bottom:12px;">
            <div style="font-weight:700;">${esc(m.pregunta)}</div>
            <div style="font-weight:600; color:var(--rojo);">Tu respuesta: ${esc(m.tu_respuesta)}</div>
            <div style="font-weight:600; color:var(--verde);">Correcta: ${esc(m.correcta)}</div>
            ${m.retroalimentacion ? `<div style="font-weight:600; opacity:0.8; margin-top:2px;">${esc(m.retroalimentacion)}</div>` : ''}
          </div>`).join(''));
    }

    if (resumen.recomendaciones && resumen.recomendaciones.length) {
      html += bloque('Qué hacer ahora', 'fa-lightbulb', 'var(--azul)',
        resumen.recomendaciones.map(r => `
          <div style="font-size:0.85rem; font-weight:600; margin-bottom:8px; display:flex; gap:8px; align-items:flex-start;">
            <i class="fas ${esc(r.icono)}" style="color:var(--azul); margin-top:3px;"></i>
            <span>${esc(r.texto)}</span>
          </div>`).join(''));
    }

    caja.innerHTML = html;
    caja.style.display = 'block';
  }

  // --- CONFETTI ANIMATION (PURE JS/CANVAS) ---
  function triggerConfetti() {
    const canvas = document.getElementById('confetti-canvas');
    if (!canvas) return;
    canvas.style.display = 'block';
    const ctx = canvas.getContext('2d');
    canvas.width = window.innerWidth;
    canvas.height = window.innerHeight;

    let particles = [];
    const colors = ['#58cc02', '#1cb0f6', '#ff9600', '#ff4b4b', '#a855f7', '#ffd700'];

    for (let i = 0; i < 150; i++) {
      particles.push({
        x: Math.random() * canvas.width,
        y: Math.random() * canvas.height - canvas.height,
        r: Math.random() * 6 + 4,
        d: Math.random() * canvas.height,
        color: colors[Math.floor(Math.random() * colors.length)],
        tilt: Math.random() * 10 - 5,
        tiltAngleIncremental: Math.random() * 0.07 + 0.02,
        tiltAngle: 0
      });
    }

    function draw() {
      ctx.clearRect(0, 0, canvas.width, canvas.height);
      particles.forEach((p, index) => {
        p.tiltAngle += p.tiltAngleIncremental;
        p.y += (Math.cos(p.d) + 3 + p.r / 2) / 2;
        p.x += Math.sin(p.tiltAngle);
        p.tilt = Math.sin(p.tiltAngle - index / 3) * 15;

        ctx.beginPath();
        ctx.lineWidth = p.r;
        ctx.strokeStyle = p.color;
        ctx.moveTo(p.x + p.tilt + p.r / 2, p.y);
        ctx.lineTo(p.x + p.tilt, p.y + p.tilt + p.r / 2);
        ctx.stroke();
      });

      // Si caen abajo, reset
      particles.forEach(p => {
        if (p.y > canvas.height) {
          p.x = Math.random() * canvas.width;
          p.y = -20;
        }
      });
    }

    let animInterval = setInterval(draw, 20);
    // Parar después de 6 segundos
    setTimeout(() => {
      clearInterval(animInterval);
      ctx.clearRect(0,0,canvas.width,canvas.height);
      canvas.style.display = 'none';
    }, 6000);
  }

  function finishMomentAndReturn(pct) {
    // Espera a que el avance quede guardado: con un tiempo fijo, una red lenta
    // cortaba la petición al cambiar de página
    saveProgress(pct).finally(() => {
      window.location.href = '<?= PROYECTO_PATH ?>/';
    });
  }

  // --- AL CARGAR ---
  document.addEventListener('DOMContentLoaded', () => {
    initWarmupMatching();
    showVocabItem();
    initExercises();

    // Si la URL tiene ?momento=N, navegar directamente a ese tab
    const urlParams = new URLSearchParams(window.location.search);
    const momentoParam = parseInt(urlParams.get('momento'));
    if (momentoParam && momentoParam >= 1 && momentoParam <= 4 && momentoParam <= maxTabUnlocked) {
      switchTab(momentoParam);
    }
  });
</script>

<script src="<?= PROYECTO_PATH ?>/assets/js/tema.js"></script>
</body>
</html>
