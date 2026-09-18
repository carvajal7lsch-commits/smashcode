<!DOCTYPE html>
<html lang="es" data-theme="dark">
<head>
  <meta charset="UTF-8">
  <link rel="icon" type="image/svg+xml" href="<?= PROYECTO_PATH ?>/assets/img/favicon.svg">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Diálogos Clínicos — SmashCode</title>
  <link rel="stylesheet" href="<?= PROYECTO_PATH ?>/assets/css/estilos.css?v=<?= time() ?>">
  <link rel="stylesheet" href="<?= PROYECTO_PATH ?>/assets/css/layout.css?v=<?= time() ?>">
  <link rel="stylesheet" href="<?= PROYECTO_PATH ?>/assets/css/aprendiz.css?v=<?= time() ?>">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <script>
    (function(){var t=localStorage.getItem('smashcode_tema');if(t)document.documentElement.setAttribute('data-theme',t);})();
  </script>
</head>
<body>
<div class="contenedor-app">
  <?php include dirname(__DIR__) . '/layouts/aprendiz_sidebar.php'; ?>

  <main class="contenido-principal">
    <div class="module-view">
        <div class="module-header">
            <i class="fas fa-comments header-icon"></i>
            <div>
                <h1>Diálogos Clínicos</h1>
                <p>Escucha y practica conversaciones del entorno médico real con voces nativas.</p>
            </div>
        </div>

        <?php if (empty($dialogos)): ?>
          <div class="vacio-container">
            <i class="fas fa-comments vacio-icono"></i>
            <h3>No hay diálogos disponibles aún</h3>
            <p class="vacio-texto">Completa lecciones para habilitar prácticas de conversación.</p>
          </div>
        <?php else: ?>
          <?php foreach ($dialogos as $d): ?>
            <div class="dialogue-card">
              <div class="dialogue-header-flex">
                <div>
                  <h3 class="dialogue-title-main"><i class="fas fa-notes-medical"></i><?= htmlspecialchars($d['titulo']) ?></h3>
                  <small class="dialogue-subtitle"><?= htmlspecialchars($d['nivel_nombre']) ?> • <?= htmlspecialchars($d['participantes']) ?></small>
                </div>
                <div style="display:flex; gap:10px; align-items:center;">
                  <button class="btn-play-full-dialogue" onclick="playFullDialogue('dialogue-<?= $d['id'] ?>')">
                    <i class="fas fa-play-circle"></i> Play Full Dialog
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
                       data-text-en="<?= htmlspecialchars($t['texto_en']) ?>"
                       data-speaker="<?= $speakerGender ?>">
                    <div class="chat-sender">
                      <i class="fas fa-<?= $speakerGender === 'male' ? 'mars' : 'venus' ?>" style="margin-right:4px; font-size:0.8rem; color:<?= $speakerGender === 'male' ? 'var(--azul)' : 'var(--naranja)' ?>;"></i><?= htmlspecialchars($t['hablante']) ?>
                    </div>
                    <div class="chat-text-en"><?= htmlspecialchars($t['texto_en']) ?></div>
                    <div class="chat-text-es"><?= htmlspecialchars($t['texto_es']) ?></div>
                    <button class="chat-bubble-play" onclick="speakSingleTurn('turno-<?= $t['id'] ?>')" title="Escuchar este turno (Voz <?= $speakerGender === 'male' ? 'Masculina' : 'Femenina' ?>)">
                      <i class="fas fa-volume-up"></i>
                    </button>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
    </div>
  </main>
</div>

<script>
  let dialogTimeoutList = [];

  function speakText(text, gender = 'female') {
    if (!('speechSynthesis' in window)) {
      return null;
    }
    window.speechSynthesis.cancel();
    let utterance = new SpeechSynthesisUtterance(text);
    utterance.lang = 'en-US';
    
    let voices = window.speechSynthesis.getVoices();
    let enVoices = voices.filter(v => v.lang && v.lang.toLowerCase().startsWith('en'));
    
    let isMale = (gender === 'male' || gender === 'hombre');
    
    if (isMale) {
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

  function stopAudioPlayback() {
    if ('speechSynthesis' in window) {
      window.speechSynthesis.cancel();
    }
    dialogTimeoutList.forEach(t => clearTimeout(t));
    dialogTimeoutList = [];
    document.querySelectorAll('.chat-bubble').forEach(b => b.classList.remove('active-highlight'));
    document.querySelectorAll('.btn-stop-dialogue').forEach(b => b.style.display = 'none');
  }

  function playFullDialogue(diaElementId) {
    stopAudioPlayback();
    
    let container = document.getElementById(diaElementId);
    let diaId = diaElementId.replace('dialogue-', '');
    let stopBtn = document.getElementById('btn-stop-audio-' + diaId);
    if (stopBtn) stopBtn.style.display = 'inline-flex';

    let bubbles = Array.from(container.querySelectorAll('.chat-bubble'));
    bubbles.forEach(b => b.classList.remove('active-highlight'));

    function playTurn(idx) {
      if (idx >= bubbles.length) {
        if (stopBtn) stopBtn.style.display = 'none';
        return;
      }
      let bubble = bubbles[idx];
      let text = bubble.getAttribute('data-text-en');
      let speaker = bubble.getAttribute('data-speaker') || 'female';

      bubble.classList.add('active-highlight');
      bubble.scrollIntoView({ behavior: 'smooth', block: 'nearest' });

      let utterance = speakText(text, speaker);
      
      utterance.onend = () => {
        bubble.classList.remove('active-highlight');
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
</script>
<script src="<?= PROYECTO_PATH ?>/assets/js/tema.js"></script>
</body>
</html>
