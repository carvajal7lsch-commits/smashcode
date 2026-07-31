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
</head>
<body>

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
      <div class="progreso-header-fill" id="header-progress-fill" style="width: <?= $progreso['porcentaje'] ?>%;"></div>
    </div>
    <div class="progreso-texto" id="header-progress-text"><?= (int)$progreso['porcentaje'] ?>%</div>
    
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
    <div class="moment-tab <?= (isset($esPreview) && $esPreview) || $progreso['porcentaje'] >= 25 || $progreso['completado'] ? '' : 'locked' ?>" id="tab-moment-2" onclick="switchTab(2)">
      <i class="fas fa-book-reader"></i> Moment 2: Absorption
    </div>
    <div class="moment-tab <?= (isset($esPreview) && $esPreview) || $progreso['porcentaje'] >= 50 || $progreso['completado'] ? '' : 'locked' ?>" id="tab-moment-3" onclick="switchTab(3)">
      <i class="fas fa-dumbbell"></i> Moment 3: Practice
    </div>
    <div class="moment-tab <?= (isset($esPreview) && $esPreview) || $progreso['porcentaje'] >= 75 || $progreso['completado'] ? '' : 'locked' ?>" id="tab-moment-4" onclick="switchTab(4)">
      <i class="fas fa-award"></i> Moment 4: Quiz
    </div>
  </nav>

  <!-- CONTENEDOR DE PANELES -->
  <main class="moments-container">

    <!-- ==================== MOMENTO 1: WARM-UP ==================== -->
    <section class="moment-pane active" id="pane-moment-1" aria-labelledby="tab-moment-1">
      <div class="card-moment">
        <h2>Warm-Up Mini-Game</h2>
        <p style="color:var(--texto-tenue); margin-top:8px;">Match English terms to their Spanish meanings to activate your prior knowledge!</p>
        
        <div class="matching-grid" id="warmup-matching-grid">
          <div class="matching-col" id="warmup-col-en"></div>
          <div class="matching-col" id="warmup-col-es"></div>
        </div>

        <div id="warmup-success-msg" style="display:none; text-align:center; margin-top:32px; animation: fadeInUp 0.3s ease;">
          <h3 style="color:var(--verde); font-size:1.4rem; font-weight:800; margin-bottom:12px;">
            <i class="fas fa-star" style="margin-right:8px;"></i>Warm-Up Completed!
          </h3>
          <p style="color:var(--texto-tenue); margin-bottom:24px;">Moment 2 (Absorption) is now unlocked. Let's start studying!</p>
          <button class="btn-verde" style="margin: 0 auto; display: block;" onclick="switchTab(2)">
            Continue <i class="fas fa-arrow-right"></i>
          </button>
        </div>
      </div>
    </section>

    <!-- ==================== MOMENTO 2: ABSORPTION ==================== -->
    <section class="moment-pane" id="pane-moment-2" aria-labelledby="tab-moment-2">
      <!-- 2.1 Grammar Pill -->
      <div class="card-moment">
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
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:14px;">
              <h3 style="font-size:1.15rem; font-weight:800; color:var(--gris-texto);"><i class="fas fa-hospital-user" style="margin-right:8px; color:var(--azul);"></i><?= limpiar($d['titulo']) ?></h3>
              <button class="btn-play-full-dialogue" onclick="playFullDialogue('dialogue-<?= $d['id'] ?>')">
                <i class="fas fa-play-circle"></i> Play Full Dialog
              </button>
            </div>
            
            <div class="dialogue-chat" id="dialogue-<?= $d['id'] ?>">
              <?php foreach ($d['turnos'] as $t): ?>
                <?php 
                  $isNurse = strpos(strtolower($t['hablante']), 'nurse') !== false || strpos(strtolower($t['hablante']), 'enfermer') !== false;
                ?>
                <div class="chat-bubble <?= $isNurse ? 'right' : 'left' ?>" 
                     id="turno-<?= $t['id'] ?>" 
                     data-text-en="<?= limpiar($t['texto_en']) ?>"
                     data-speaker="<?= $isNurse ? 'female' : 'male' ?>">
                  <div class="chat-sender"><?= limpiar($t['hablante']) ?></div>
                  <div class="chat-text-en"><?= limpiar($t['texto_en']) ?></div>
                  <div class="chat-text-es"><?= limpiar($t['texto_es']) ?></div>
                  <button class="chat-bubble-play" onclick="speakSingleTurn('turno-<?= $t['id'] ?>')">
                    <i class="fas fa-volume-up"></i>
                  </button>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>

        <div style="text-align:center; margin-top:32px;">
          <button class="btn-verde" id="btn-unlock-moment-3" onclick="unlockMoment3()">
            I am ready for Output Practice <i class="fas fa-arrow-right"></i>
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
                    <div style="margin-top:20px; font-weight:800; color:var(--azul);">Organized Conversation:</div>
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

                <div style="display:flex; justify-content:flex-end; margin-top:24px;">
                  <button class="btn-verde" id="btn-validate-<?= $idx ?>" onclick="validateExercise('<?= $idx ?>')">Verificar</button>
                  <button class="btn-azul" id="btn-next-exercise-<?= $idx ?>" onclick="nextExercise('<?= $idx ?>')" style="display:none;">Continuar</button>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </section>

    <!-- ==================== MOMENTO 4: QUIZ ==================== -->
    <section class="moment-pane" id="pane-moment-4" aria-labelledby="tab-moment-4">
      <div class="card-moment" id="quiz-intro-box">
        <h2 style="color:var(--morado);"><i class="fas fa-graduation-cap" style="margin-right:8px;"></i>Quiz Closure: Prueba tus conocimientos</h2>
        <p style="color:var(--texto-tenue); margin-top:10px; line-height:1.6;">
          Has completado todos los momentos pedagógicos de este Resultado de Aprendizaje (RAP). Toma este quiz final de <strong>5 preguntas</strong> para evaluar tu nivel y desbloquear oficialmente el siguiente nivel en tu mapa de aprendizaje.
        </p>
        <div style="background:var(--fondo); border:2px solid var(--gris-claro); border-radius:12px; padding:20px; margin:24px 0; display:grid; grid-template-columns:1fr 1fr; gap:16px;">
          <div>
            <div style="font-size:0.75rem; text-transform:uppercase; font-weight:800; color:var(--gris-medio);">Umbral de Aprobación</div>
            <div style="font-size:1.4rem; font-weight:900; color:var(--morado);"><?= (int)($quiz['puntaje_minimo'] ?? 60) ?>%</div>
          </div>
          <div>
            <div style="font-size:0.75rem; text-transform:uppercase; font-weight:800; color:var(--gris-medio);">Límite de tiempo</div>
            <div style="font-size:1.4rem; font-weight:900; color:var(--morado);">5:00 min</div>
          </div>
        </div>
        <button class="btn-morado" style="display:block; width:100%; font-size:1.1rem; padding:14px;" onclick="startQuiz()">
          Comenzar Evaluación
        </button>
      </div>

      <!-- Quiz Player -->
      <div class="card-moment" id="quiz-player-box" style="display:none;">
        <div class="exercise-header">
          <span style="color:var(--morado);" id="quiz-question-indicator">PREGUNTA 1 DE 5</span>
          <span style="color:var(--rojo);" id="quiz-timer"><i class="fas fa-clock" style="margin-right:4px;"></i>05:00</span>
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

        <button class="btn-verde" style="margin:24px auto 0 auto; display:block;" onclick="window.location='<?= PROYECTO_PATH ?>/'">
          Volver al Mapa de Aprendizaje
        </button>
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
  const totalQuizPreguntas = <?= count($preguntas) ?>;
  const quizMinPct = <?= (float)($quiz['puntaje_minimo'] ?? 60.00) ?>;

  // Variables de estado
  let activeTab = 1;
  let maxTabUnlocked = <?= (isset($esPreview) && $esPreview) ? 4 : ($progreso['porcentaje'] >= 75 || $progreso['completado'] ? 4 : ($progreso['porcentaje'] >= 50 ? 3 : ($progreso['porcentaje'] >= 25 ? 2 : 1))) ?>;
  let vocabIndex = 0;
  let sessionXp = 0;

  // 1. Warm-Up State
  let selectedEn = null;
  let selectedEs = null;
  let matchedCount = 0;

  // 2. Exercises State
  let currentExerciseIdx = 0;
  let exercisePoints = 0;
  let answersObj = {};
  let selectedColumnText = { en: '', es: '', enNode: null, esNode: null };
  let selectedOrderSeq = [];

  // 3. Quiz State
  let currentQuizPregIdx = 0;
  let quizAnswers = {};
  let quizTimerInterval = null;
  let quizTimeRemaining = 300; // 5 mins

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

    // Actualizar progreso
    let pct = (num - 1) * 25;
    let fill = document.getElementById('header-progress-fill');
    let txt = document.getElementById('header-progress-text');
    if (fill && txt) {
      fill.style.width = pct + '%';
      txt.textContent = pct + '%';
    }

    // Reportar progreso al servidor vía AJAX
    saveProgress(pct);
  }

  function saveProgress(pct) {
    let formData = new FormData();
    formData.append('rap_id', rapId);
    formData.append('porcentaje', pct);

    fetch('<?= PROYECTO_PATH ?>/aprendiz/rap/guardar-progreso', {
      method: 'POST',
      body: formData
    });
  }

  // --- AUDIO / SPEECH SYNTHESIS ---
  function speakText(text, gender = 'female') {
    if ('speechSynthesis' in window) {
      window.speechSynthesis.cancel(); // Parar audios anteriores
      let utterance = new SpeechSynthesisUtterance(text);
      utterance.lang = 'en-US';
      utterance.rate = 0.9; // Hablar un poco más lento
      
      // Buscar voces en inglés
      let voices = window.speechSynthesis.getVoices();
      let enVoices = voices.filter(v => v.lang.startsWith('en'));
      
      if (enVoices.length > 0) {
        if (gender === 'female') {
          // Intentar obtener voz femenina
          let fVoice = enVoices.find(v => v.name.toLowerCase().includes('zira') || v.name.toLowerCase().includes('female') || v.name.toLowerCase().includes('google'));
          utterance.voice = fVoice || enVoices[0];
        } else {
          // Intentar obtener voz masculina
          let mVoice = enVoices.find(v => v.name.toLowerCase().includes('david') || v.name.toLowerCase().includes('male') || v.name.toLowerCase().includes('microsoft'));
          utterance.voice = mVoice || enVoices[0];
        }
      }
      window.speechSynthesis.speak(utterance);
      return utterance;
    } else {
      console.log("Speech synthesis not supported in this browser.");
    }
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
        
        speakText(selectedEn.textContent);

        selectedEn = null;
        selectedEs = null;

        if (matchedCount === 3) {
          document.getElementById('warmup-success-msg').style.display = 'block';
          unlockMoment(2);
        }
      } else {
        // MATCH incorrecto
        let nodeEn = selectedEn;
        let nodeEs = selectedEs;
        nodeEn.classList.add('incorrect');
        nodeEs.classList.add('incorrect');
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

    fetch('<?= PROYECTO_PATH ?>/aprendiz/rap/marcar-vocabulario', {
      method: 'POST',
      body: formData
    })
    .then(r => r.json())
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

  // --- STORYBOOK DIALOGUE PLAYBACK & HIGHLIGHT ---
  let dialogTimeoutList = [];

  function playFullDialogue(diaElementId) {
    window.speechSynthesis.cancel();
    dialogTimeoutList.forEach(t => clearTimeout(t));
    dialogTimeoutList = [];
    
    let container = document.getElementById(diaElementId);
    let bubbles = Array.from(container.querySelectorAll('.chat-bubble'));
    
    bubbles.forEach(b => b.classList.remove('active-highlight'));

    function playTurn(idx) {
      if (idx >= bubbles.length) return;
      let bubble = bubbles[idx];
      let text = bubble.getAttribute('data-text-en');
      let speaker = bubble.getAttribute('data-speaker') || 'female';

      bubble.classList.add('active-highlight');
      bubble.scrollIntoView({ behavior: 'smooth', block: 'nearest' });

      let utterance = speakText(text, speaker);
      
      utterance.onend = () => {
        bubble.classList.remove('active-highlight');
        // Continuar al siguiente turno tras 0.5s de pausa natural
        let timeout = setTimeout(() => {
          playTurn(idx + 1);
        }, 500);
        dialogTimeoutList.push(timeout);
      };
    }
    
    playTurn(0);
  }

  function speakSingleTurn(turnId) {
    window.speechSynthesis.cancel();
    dialogTimeoutList.forEach(t => clearTimeout(t));
    dialogTimeoutList = [];

    document.querySelectorAll('.chat-bubble').forEach(b => b.classList.remove('active-highlight'));

    let bubble = document.getElementById(turnId);
    let text = bubble.getAttribute('data-text-en');
    let speaker = bubble.getAttribute('data-speaker') || 'female';

    bubble.classList.add('active-highlight');
    let utterance = speakText(text, speaker);
    utterance.onend = () => {
      bubble.classList.remove('active-highlight');
    };
  }

  function unlockMoment3() {
    unlockMoment(3);
    switchTab(3);
  }

  // --- MOMENTO 3: EJERCICIOS PRACTICOS PLAYER ---
  function initExercises() {
    if (totalEjercicios === 0) return;
    currentExerciseIdx = 0;
    exercisePoints = 0;
    answersObj = {};
    
    // Ocultar todos
    document.querySelectorAll('.exercise-box').forEach(b => {
      b.style.display = 'none';
      b.classList.remove('active');
    });

    let firstBox = document.getElementById('exercise-box-0');
    if (firstBox) {
      firstBox.style.display = 'block';
      firstBox.classList.add('active');
    }
    
    updateExerciseHeader();
  }

  function updateExerciseHeader() {
    let ind = document.getElementById('exercise-number-indicator');
    let sc = document.getElementById('exercise-score-indicator');
    if (ind && sc) {
      ind.textContent = `EJERCICIO ${currentExerciseIdx + 1} DE ${totalEjercicios}`;
      sc.textContent = `Total: ${exercisePoints} / ${totalEjercicios * 10} Pts`;
    }
  }

  // MC & Role-play Selection handler
  function selectOption(exIdx, opcId, node) {
    let box = document.getElementById('exercise-box-' + exIdx);
    box.querySelectorAll('.option-item').forEach(n => n.classList.remove('selected'));
    node.classList.add('selected');
    
    answersObj[exIdx] = {
      isCorrect: parseInt(node.dataset.correct) === 1,
      retro: node.dataset.retro,
      text: node.querySelector('span:last-child').textContent
    };
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

    answersObj[exIdx] = {
      isCorrect: text.toLowerCase().trim() === correctVal.toLowerCase().trim(),
      retro: 'Frase completada.',
      text: text
    };
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
    bubble.innerHTML = `<div>${itemText}</div>`;
    displayBox.appendChild(bubble);

    selectedOrderSeq.push(itemText);

    // Comparar longitud para validar
    let correctSeqText = document.getElementById('ordered-seq-list-' + exIdx).dataset.correctSeq;
    let correctArr = correctSeqText.split('|');

    if (selectedOrderSeq.length === correctArr.length) {
      let isCorrect = selectedOrderSeq.every((val, i) => val === correctArr[i]);
      answersObj[exIdx] = {
        isCorrect: isCorrect,
        retro: isCorrect ? '¡Has ordenado perfectamente la conversación!' : 'El orden no es el correcto.',
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
      ans = {
        isCorrect: text === correct,
        retro: text === correct ? '¡Correcto!' : `Incorrecto. Se escribe: "${correct}".`,
        text: text
      };
      answersObj[exIdx] = ans;
    }

    if (!ans) {
      alert("Por favor selecciona o ingresa una respuesta primero.");
      return;
    }

    // Ocultar botón validar
    document.getElementById('btn-validate-' + exIdx).style.display = 'none';

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
      
      // Dar puntos XP en caliente para la UI
      exercisePoints += 10;
      sessionXp += 10;
      document.getElementById('session-xp').textContent = `${sessionXp} XP`;
    } else {
      banner.className = 'validation-banner incorrect';
      icon.className = 'fas fa-times-circle';
      title.textContent = 'Respuesta incorrecta';
      expl.textContent = ans.retro || 'Inténtalo de nuevo en la siguiente sesión.';
    }

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

  function nextExercise(exIdx) {
    let currentBox = document.getElementById('exercise-box-' + exIdx);
    currentBox.style.display = 'none';
    currentBox.classList.remove('active');

    let nextIdx = parseInt(exIdx) + 1;
    
    if (nextIdx < totalEjercicios) {
      currentExerciseIdx = nextIdx;
      // Reset selected column variables
      selectedColumnText = { en: '', es: '', enNode: null, esNode: null };
      selectedOrderSeq = [];

      let nextBox = document.getElementById('exercise-box-' + nextIdx);
      nextBox.style.display = 'block';
      nextBox.classList.add('active');
      updateExerciseHeader();
    } else {
      // Completó todos los ejercicios!
      // Otorgar XP en el servidor al final de los ejercicios
      let userXpFormData = new FormData();
      userXpFormData.append('rap_id', rapId);
      userXpFormData.append('porcentaje', 75); // 75% progress
      fetch('<?= PROYECTO_PATH ?>/aprendiz/rap/guardar-progreso', {
        method: 'POST',
        body: userXpFormData
      }).then(() => {
        unlockMoment(4);
        switchTab(4);
      });
    }
  }

  // --- MOMENTO 4: QUIZ EVALUATION CLOSURE ---
  function startQuiz() {
    document.getElementById('quiz-intro-box').style.display = 'none';
    document.getElementById('quiz-player-box').style.display = 'block';
    currentQuizPregIdx = 0;
    quizAnswers = {};
    quizTimeRemaining = 300;
    
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

    let duracion = 300 - quizTimeRemaining;

    let formData = new FormData();
    formData.append('rap_id', rapId);
    formData.append('duracion_seg', duracion);
    
    for (let pId in answersData) {
      formData.append(`respuestas[${pId}]`, answersData[pId]);
    }

    fetch('<?= PROYECTO_PATH ?>/aprendiz/rap/guardar-quiz', {
      method: 'POST',
      body: formData
    })
    .then(r => r.json())
    .then(data => {
      if (data.exito) {
        showQuizResults(data);
      } else {
        alert("Ocurrió un error al procesar el Quiz: " + data.error);
      }
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

      // Confetti!
      triggerConfetti();
      
      // Actualizar XP en la barra superior en caliente
      sessionXp += data.xp_ganados;
      document.getElementById('session-xp').textContent = `${sessionXp} XP`;
      
      // Actualizar barra del encabezado al 100%
      let fill = document.getElementById('header-progress-fill');
      let txt = document.getElementById('header-progress-text');
      if (fill && txt) {
        fill.style.width = '100%';
        txt.textContent = '100%';
      }
    } else {
      ring.style.borderColor = 'var(--rojo)';
      title.textContent = 'Sigue practicando';
      title.style.color = 'var(--rojo)';
      msg.textContent = `Has obtenido ${pct}%. Necesitas un mínimo de ${quizMinPct}% para aprobar la lección.`;
      xpBox.textContent = '+0 XP';
      badgeBox.textContent = 'Ninguna';
    }
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
