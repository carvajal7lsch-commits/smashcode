<!DOCTYPE html>
<html lang="es" data-theme="dark">
<head>
  <meta charset="UTF-8">
  <link rel="icon" type="image/svg+xml" href="<?= PROYECTO_PATH ?>/assets/img/favicon.svg">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Mi Vocabulario — SmashCode</title>
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
            <i class="fas fa-spell-check header-icon"></i>
            <div>
                <h1>Vocabulario Clínico</h1>
                <p>Repasa los términos técnicos y marca palabras difíciles para reforzarlas.</p>
            </div>
        </div>

        <?php if (empty($vocabulario)): ?>
          <div class="vacio-container">
            <i class="fas fa-book-open vacio-icono"></i>
            <h3>No hay términos clínicos guardados aún</h3>
            <p class="vacio-texto">Completa lecciones del mapa para poblar tu vocabulario.</p>
          </div>
        <?php else: ?>
          <div class="vocab-list" id="vocab-container">
              <?php foreach ($vocabulario as $v): ?>
                <div class="vocab-card" id="card-<?= $v['id'] ?>">
                    <button class="btn-play-audio" onclick="speakTerm('<?= addslashes($v['termino_en']) ?>')"><i class="fas fa-volume-up"></i></button>
                    <div class="vocab-details">
                      <h3 class="vocab-english"><?= htmlspecialchars($v['termino_en']) ?></h3>
                      <p class="vocab-spanish"><?= htmlspecialchars($v['termino_es']) ?></p>
                      <small class="vocab-tag">
                        <?= htmlspecialchars($v['nivel_nombre']) ?> • <?= htmlspecialchars($v['categoria_nombre'] ?? 'Sustantivo') ?>
                      </small>
                    </div>
                    <button class="vocab-card-star <?= $v['es_dificil'] ? 'active' : 'inactive' ?>" onclick="toggleVocabStar('<?= $v['id'] ?>', this)">
                      <i class="<?= $v['es_dificil'] ? 'fas' : 'far' ?> fa-star"></i>
                    </button>
                </div>
              <?php endforeach; ?>
          </div>
        <?php endif; ?>
    </div>
  </main>
</div>

<script>
  function speakTerm(text) {
    if ('speechSynthesis' in window) {
      window.speechSynthesis.cancel();
      let utterance = new SpeechSynthesisUtterance(text);
      utterance.lang = 'en-US';
      utterance.rate = 0.9;
      
      let voices = window.speechSynthesis.getVoices();
      let enVoice = voices.find(v => v.lang.startsWith('en'));
      if (enVoice) utterance.voice = enVoice;
      
      window.speechSynthesis.speak(utterance);
    }
  }

  function toggleVocabStar(vocabId, node) {
    let formData = new FormData();
    formData.append('vocabulario_id', vocabId);
    formData.append('csrf_token',<?= json_encode(generarTokenCSRF()) ?>);

    fetch('<?= PROYECTO_PATH ?>/aprendiz/rap/marcar-vocabulario', {
      method: 'POST',
      body: formData,
      headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(d => {
      if (d.sesion_expirada) {
        // Aquí no hay avance que perder: basta con volver a iniciar sesión
        Avisos.error(d.error);
        window.location.href = '<?= PROYECTO_PATH ?>/login';
        return;
      }
      if (!d.exito) { Avisos.error(d.error || 'No se pudo guardar.'); return; }
      if (d.exito) {
        if (d.marcado) {
          node.className = 'vocab-card-star active';
          node.innerHTML = '<i class="fas fa-star"></i>';
        } else {
          node.className = 'vocab-card-star inactive';
          node.innerHTML = '<i class="far fa-star"></i>';
        }
      }
    });
  }
</script>
<script src="<?= PROYECTO_PATH ?>/assets/js/tema.js"></script>
  <script src="<?= PROYECTO_PATH ?>/assets/js/avisos.js"></script>
</body>
</html>
