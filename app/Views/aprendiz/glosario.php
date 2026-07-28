<!DOCTYPE html>
<html lang="es" data-theme="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Glosario Médico — SmashCode</title>
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
            <i class="fas fa-book-medical header-icon"></i>
            <div>
                <h1>Glosario Clínico Bilingüe</h1>
                <p>Consulta términos técnicos, categorías gramaticales, áreas de enfermería y escucha pronunciaciones IPA.</p>
            </div>
        </div>

        <!-- FORMULARIO DE FILTROS -->
        <form class="filtros-card" method="GET" action="<?= PROYECTO_PATH ?>/aprendiz/glosario" style="background: var(--bg-fondo); padding: 20px; border-radius: 12px; margin-bottom: 24px; border: 1px solid rgba(0,0,0,0.05);">
          <div class="filtros-row" style="display:flex; gap:16px; margin-bottom: 16px; flex-wrap:wrap;">
            
            <div style="flex: 2; position: relative;">
              <i class="fas fa-search" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--gris-medio);"></i>
              <input type="text" name="q" placeholder="Buscar término..." value="<?= htmlspecialchars($busqueda) ?>" style="width: 100%; padding: 12px 12px 12px 40px; border-radius: 8px; border: 1px solid var(--gris-claro); background: var(--bg-tarjeta); color: var(--texto-principal);">
            </div>

            <div style="flex: 1;">
              <select name="area" style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid var(--gris-claro); background: var(--bg-tarjeta); color: var(--texto-principal);">
                <option value="">Todas las Áreas</option>
                <?php foreach ($areas as $a): ?>
                  <option value="<?= $a['id'] ?>" <?= $areaId === $a['id'] ? 'selected' : '' ?>><?= htmlspecialchars($a['nombre']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <div style="flex: 1;">
              <select name="categoria" style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid var(--gris-claro); background: var(--bg-tarjeta); color: var(--texto-principal);">
                <option value="">Todas las Categorías</option>
                <?php foreach ($categorias as $c): ?>
                  <option value="<?= $c['id'] ?>" <?= $categoriaId === $c['id'] ? 'selected' : '' ?>><?= htmlspecialchars($c['nombre']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <div style="flex: 1;">
              <select name="nivel" style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid var(--gris-claro); background: var(--bg-tarjeta); color: var(--texto-principal);">
                <option value="">Todos los Niveles</option>
                <?php foreach ($niveles as $n): ?>
                  <option value="<?= $n['id'] ?>" <?= $nivelId === $n['id'] ? 'selected' : '' ?>><?= htmlspecialchars($n['nombre']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div style="display:flex; justify-content:space-between; align-items:center;">
            <small style="color:var(--gris-medio); font-weight:bold;">Resultados encontrados: <?= count($vocabulario) ?></small>
            <div style="display:flex; gap:12px;">
              <?php if ($busqueda || $areaId || $categoriaId || $nivelId): ?>
                <a href="<?= PROYECTO_PATH ?>/aprendiz/glosario" class="btn-gris" style="text-decoration:none; padding:10px 20px; display:inline-flex; align-items:center; border-radius:8px; font-weight:bold;">Limpiar</a>
              <?php endif; ?>
              <button type="submit" class="btn-verde" style="padding:10px 20px; border-radius:8px; font-weight:bold; cursor:pointer; border: none; color: white; background: #58cc02;">
                <i class="fas fa-filter"></i> Filtrar
              </button>
            </div>
          </div>
        </form>

        <?php if (empty($vocabulario)): ?>
          <div class="vacio-container">
            <i class="fas fa-book-open vacio-icono"></i>
            <h3>No se encontraron términos clínicos</h3>
            <p class="vacio-texto">Intenta ajustando los criterios de búsqueda o filtros.</p>
          </div>
        <?php else: ?>
          <div class="vocab-list" id="vocab-container">
              <?php foreach ($vocabulario as $v): ?>
                <div class="vocab-card">
                    <button class="btn-play-audio" onclick="speakWord('<?= addslashes($v['termino_en']) ?>')"><i class="fas fa-volume-up"></i></button>
                    <div class="vocab-details">
                      <h3 class="vocab-english">
                          <?= htmlspecialchars($v['termino_en']) ?>
                          <?php if ($v['transcripcion_ipa']): ?>
                              <span style="font-size:0.85rem; color:var(--texto-tenue); font-family:monospace; margin-left:8px;">/<?= htmlspecialchars($v['transcripcion_ipa']) ?>/</span>
                          <?php endif; ?>
                      </h3>
                      <p class="vocab-spanish"><?= htmlspecialchars($v['termino_es']) ?></p>
                      
                      <div style="margin-top:8px; display:flex; flex-direction:column; gap:4px;">
                        <small class="vocab-tag">
                          <?= htmlspecialchars($v['nivel_nombre']) ?> • <?= htmlspecialchars($v['categoria_nombre'] ?? 'Sustantivo') ?> • <?= htmlspecialchars($v['area_nombre'] ?? 'General') ?>
                        </small>
                        <?php if ($v['oracion_ejemplo']): ?>
                          <small style="color:var(--texto-secundario); font-style:italic;">"<?= htmlspecialchars($v['oracion_ejemplo']) ?>"</small>
                        <?php endif; ?>
                      </div>
                    </div>
                </div>
              <?php endforeach; ?>
          </div>
        <?php endif; ?>
    </div>
  </main>
</div>

<script>
  function speakWord(text) {
    if ('speechSynthesis' in window) {
      window.speechSynthesis.cancel();
      let utterance = new SpeechSynthesisUtterance(text);
      utterance.lang = 'en-US';
      utterance.rate = 0.9;
      
      let voices = window.speechSynthesis.getVoices();
      let enVoice = voices.find(v => v.lang.startsWith('en'));
      if (enVoice) utterance.voice = enVoice;
      
      window.speechSynthesis.speak(utterance);
    } else {
      console.log("Audio Speech synthesis not supported.");
    }
  }

  // Pre-cargar voces al inicio
  if ('speechSynthesis' in window) {
    window.speechSynthesis.onvoiceschanged = () => {};
  }
</script>
<script src="<?= PROYECTO_PATH ?>/assets/js/tema.js"></script>
</body>
</html>
