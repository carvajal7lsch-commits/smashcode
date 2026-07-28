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
  <style>
    /* Premium Design Overrides para el Glosario */
    .premium-header {
      background: linear-gradient(135deg, rgba(28, 176, 246, 0.1) 0%, rgba(88, 204, 2, 0.05) 100%);
      border: 1px solid rgba(255,255,255,0.05);
      border-radius: 20px;
      padding: 30px;
      margin-bottom: 24px;
      display: flex;
      align-items: center;
      gap: 20px;
      position: relative;
      overflow: hidden;
      backdrop-filter: blur(10px);
    }
    .premium-header::before {
      content: '';
      position: absolute;
      top: -50px; right: -50px;
      width: 150px; height: 150px;
      background: radial-gradient(circle, rgba(28,176,246,0.2) 0%, transparent 70%);
      border-radius: 50%;
    }
    .premium-header-icon {
      background: linear-gradient(135deg, #1cb0f6, #1899d6);
      color: white;
      width: 60px; height: 60px;
      border-radius: 16px;
      display: flex; align-items: center; justify-content: center;
      font-size: 1.8rem;
      box-shadow: 0 10px 20px rgba(28,176,246,0.3);
    }
    .premium-header-content h1 {
      margin: 0; font-size: 1.8rem; font-weight: 800; color: var(--texto-principal);
    }
    .premium-header-content p {
      margin: 8px 0 0 0; color: var(--texto-tenue); font-size: 0.95rem;
    }

    .premium-filters {
      background: var(--bg-tarjeta);
      border-radius: 20px;
      padding: 24px;
      box-shadow: 0 8px 30px rgba(0,0,0,0.08);
      margin-bottom: 30px;
      border: 1px solid rgba(255,255,255,0.03);
    }
    
    .glass-input-group {
      position: relative;
      flex: 1;
      min-width: 200px;
    }
    .glass-input-group i {
      position: absolute;
      left: 16px; top: 50%;
      transform: translateY(-50%);
      color: #1cb0f6;
      font-size: 1.1rem;
      pointer-events: none;
    }
    .glass-input {
      width: 100%;
      background: var(--bg-fondo);
      border: 2px solid transparent;
      border-radius: 14px;
      padding: 14px 14px 14px 44px;
      font-size: 0.95rem;
      color: var(--texto-principal);
      transition: all 0.3s ease;
      box-shadow: inset 0 2px 4px rgba(0,0,0,0.02);
    }
    .glass-input:focus {
      border-color: #1cb0f6;
      outline: none;
      background: var(--bg-tarjeta);
      box-shadow: 0 0 0 4px rgba(28,176,246,0.15);
    }
    .glass-input:hover {
      background: var(--hover-tarjeta);
    }
    select.glass-input {
      appearance: none;
      cursor: pointer;
      background-image: url('data:image/svg+xml;utf8,<svg fill="%231cb0f6" height="24" viewBox="0 0 24 24" width="24" xmlns="http://www.w3.org/2000/svg"><path d="M7 10l5 5 5-5z"/></svg>');
      background-repeat: no-repeat;
      background-position: right 12px center;
    }

    .premium-btn-filter {
      background: linear-gradient(to bottom, #58cc02, #46a302);
      color: white;
      border: none;
      border-radius: 14px;
      padding: 14px 28px;
      font-size: 1rem;
      font-weight: 800;
      text-transform: uppercase;
      letter-spacing: 0.05em;
      cursor: pointer;
      box-shadow: 0 4px 0 #3a8701, 0 8px 15px rgba(88,204,2,0.3);
      transition: all 0.1s ease;
      display: inline-flex; align-items: center; gap: 8px;
    }
    .premium-btn-filter:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 0 #3a8701, 0 10px 20px rgba(88,204,2,0.4);
    }
    .premium-btn-filter:active {
      transform: translateY(4px);
      box-shadow: 0 0 0 #3a8701;
    }

    .vocab-list-premium {
      display: flex;
      flex-direction: column;
      gap: 16px;
    }
    .vocab-item {
      background: var(--bg-tarjeta);
      border-radius: 16px;
      padding: 20px;
      display: flex;
      align-items: center;
      gap: 24px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.05);
      border: 1px solid rgba(255,255,255,0.02);
      transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
    }
    .vocab-item:hover {
      transform: translateY(-4px) scale(1.01);
      box-shadow: 0 12px 24px rgba(0,0,0,0.1);
      border-color: rgba(28,176,246,0.3);
    }
    
    .vocab-item-audio {
      flex-shrink: 0;
    }
    .btn-audio-huge {
      width: 50px; height: 50px;
      border-radius: 50%;
      background: rgba(28,176,246,0.1);
      color: #1cb0f6;
      border: none;
      font-size: 1.2rem;
      cursor: pointer;
      transition: all 0.3s ease;
      display: flex; align-items: center; justify-content: center;
    }
    .btn-audio-huge:hover {
      background: #1cb0f6;
      color: white;
      transform: scale(1.1);
      box-shadow: 0 4px 12px rgba(28,176,246,0.4);
    }

    .vocab-item-content {
      flex: 1;
      display: grid;
      grid-template-columns: 2fr 1fr 2fr;
      gap: 20px;
      align-items: center;
    }

    .v-term h3 {
      margin: 0 0 4px 0;
      font-size: 1.3rem;
      font-weight: 800;
      color: var(--texto-principal);
      display: flex; align-items: center; gap: 10px;
    }
    .v-ipa {
      font-size: 0.9rem;
      color: var(--texto-tenue);
      font-weight: 500;
      font-family: monospace;
      background: var(--bg-fondo);
      padding: 2px 8px;
      border-radius: 8px;
    }
    .v-trans {
      margin: 0;
      font-size: 1rem;
      color: #ff9600;
      font-weight: 700;
    }

    .v-tags {
      display: flex;
      flex-direction: column;
      gap: 6px;
      align-items: flex-start;
    }
    .badge-modern {
      font-size: 0.75rem;
      font-weight: 700;
      padding: 4px 10px;
      border-radius: 20px;
      display: inline-flex;
      align-items: center;
      gap: 6px;
    }
    .badge-modern.area { background: rgba(28,176,246,0.1); color: #1cb0f6; }
    .badge-modern.cat { background: rgba(206,130,255,0.1); color: #ce82ff; }
    .badge-modern.level { background: rgba(88,204,2,0.1); color: #58cc02; }

    .v-example {
      font-size: 0.9rem;
      color: var(--texto-secundario);
      font-style: italic;
      line-height: 1.5;
      border-left: 3px solid rgba(255,150,0,0.3);
      padding-left: 12px;
    }

    .empty-state-premium {
      text-align: center;
      padding: 60px 20px;
      background: var(--bg-tarjeta);
      border-radius: 24px;
      border: 2px dashed rgba(255,255,255,0.1);
    }
    .empty-state-premium img {
      width: 150px;
      opacity: 0.8;
      margin-bottom: 20px;
      filter: drop-shadow(0 10px 20px rgba(0,0,0,0.1));
    }
    .empty-state-premium h3 {
      font-size: 1.5rem;
      color: var(--texto-principal);
      margin-bottom: 8px;
    }
    .empty-state-premium p {
      color: var(--texto-tenue);
      font-size: 1rem;
    }

    /* Responsivo */
    @media (max-width: 900px) {
      .vocab-item-content {
        grid-template-columns: 1fr;
      }
      .filtros-row {
        flex-direction: column;
      }
    }
  </style>
</head>
<body>
<div class="contenedor-app">
  <?php include dirname(__DIR__) . '/layouts/aprendiz_sidebar.php'; ?>

  <main class="contenido-principal">
    <div class="glosario-container" style="max-width: 1100px; margin: 0 auto;">
      
      <!-- HEADER PREMIUM -->
      <div class="premium-header">
        <div class="premium-header-icon">
          <i class="fas fa-book-medical"></i>
        </div>
        <div class="premium-header-content">
          <h1>Glosario Clínico Bilingüe</h1>
          <p>Explora el diccionario médico completo de SmashCode. Filtra por áreas, categorías y domina la pronunciación exacta.</p>
        </div>
      </div>

      <!-- FORMULARIO DE FILTROS -->
      <form class="premium-filters" method="GET" action="<?= PROYECTO_PATH ?>/aprendiz/glosario">
        <div class="filtros-row" style="display:flex; gap:16px; margin-bottom: 20px; flex-wrap:wrap;">
          
          <div class="glass-input-group" style="flex: 2;">
            <i class="fas fa-search"></i>
            <input type="text" name="q" class="glass-input" placeholder="Buscar un término médico..." value="<?= htmlspecialchars($busqueda) ?>">
          </div>

          <div class="glass-input-group">
            <i class="fas fa-hospital-user"></i>
            <select name="area" class="glass-input">
              <option value="">Cualquier Área Clínica</option>
              <?php foreach ($areas as $a): ?>
                <option value="<?= $a['id'] ?>" <?= $areaId === $a['id'] ? 'selected' : '' ?>><?= htmlspecialchars($a['nombre']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="glass-input-group">
            <i class="fas fa-tags"></i>
            <select name="categoria" class="glass-input">
              <option value="">Tipo de Palabra</option>
              <?php foreach ($categorias as $c): ?>
                <option value="<?= $c['id'] ?>" <?= $categoriaId === $c['id'] ? 'selected' : '' ?>><?= htmlspecialchars($c['nombre']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="glass-input-group">
            <i class="fas fa-layer-group"></i>
            <select name="nivel" class="glass-input">
              <option value="">Todos los Niveles</option>
              <?php foreach ($niveles as $n): ?>
                <option value="<?= $n['id'] ?>" <?= $nivelId === $n['id'] ? 'selected' : '' ?>><?= htmlspecialchars($n['nombre']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <div style="display:flex; justify-content:space-between; align-items:center; border-top: 1px solid rgba(255,255,255,0.05); padding-top:20px;">
          <div style="font-size: 0.9rem; font-weight: 700; color: var(--gris-medio);">
            <i class="fas fa-poll-h" style="margin-right:6px;"></i> Resultados: <span style="color:var(--texto-principal);"><?= count($vocabulario) ?></span> términos
          </div>
          <div style="display:flex; gap:16px; align-items:center;">
            <?php if ($busqueda || $areaId || $categoriaId || $nivelId): ?>
              <a href="<?= PROYECTO_PATH ?>/aprendiz/glosario" style="color:var(--texto-tenue); font-weight:700; text-decoration:none; font-size:0.9rem; transition: color 0.2s;">
                <i class="fas fa-times-circle"></i> Limpiar Filtros
              </a>
            <?php endif; ?>
            <button type="submit" class="premium-btn-filter">
              <i class="fas fa-filter"></i> Aplicar Filtros
            </button>
          </div>
        </div>
      </form>

      <!-- LISTA DE RESULTADOS PREMIUM -->
      <div class="vocab-list-premium">
        <?php if (empty($vocabulario)): ?>
          <div class="empty-state-premium">
            <i class="fas fa-box-open" style="font-size: 5rem; color: rgba(28,176,246,0.2); margin-bottom: 20px; display:block;"></i>
            <h3>No se encontraron términos</h3>
            <p>No hay resultados que coincidan con tu búsqueda o filtros actuales.</p>
            <?php if ($busqueda || $areaId || $categoriaId || $nivelId): ?>
              <a href="<?= PROYECTO_PATH ?>/aprendiz/glosario" class="btn-azul" style="display:inline-block; margin-top:20px; padding: 10px 24px; text-decoration:none;">Ver Todo el Glosario</a>
            <?php endif; ?>
          </div>
        <?php else: ?>
          <?php foreach ($vocabulario as $v): ?>
            <div class="vocab-item">
              <div class="vocab-item-audio">
                <button class="btn-audio-huge" onclick="speakWord('<?= addslashes($v['termino_en']) ?>')" title="Escuchar pronunciación">
                  <i class="fas fa-volume-up"></i>
                </button>
              </div>
              <div class="vocab-item-content">
                <div class="v-term">
                  <h3>
                    <?= htmlspecialchars($v['termino_en']) ?>
                    <?php if ($v['transcripcion_ipa']): ?>
                      <span class="v-ipa">/<?= htmlspecialchars($v['transcripcion_ipa']) ?>/</span>
                    <?php endif; ?>
                  </h3>
                  <p class="v-trans"><?= htmlspecialchars($v['termino_es']) ?></p>
                </div>
                
                <div class="v-tags">
                  <?php if ($v['area_nombre']): ?>
                    <span class="badge-modern area"><i class="fas fa-hospital-alt"></i> <?= htmlspecialchars($v['area_nombre']) ?></span>
                  <?php endif; ?>
                  <?php if ($v['categoria_nombre']): ?>
                    <span class="badge-modern cat"><i class="fas fa-tag"></i> <?= htmlspecialchars($v['categoria_nombre']) ?></span>
                  <?php endif; ?>
                  <?php if ($v['nivel_nombre']): ?>
                    <span class="badge-modern level"><i class="fas fa-layer-group"></i> <?= htmlspecialchars($v['nivel_nombre']) ?></span>
                  <?php endif; ?>
                </div>

                <div class="v-example">
                  <?php if ($v['oracion_ejemplo']): ?>
                    "<?= htmlspecialchars($v['oracion_ejemplo']) ?>"
                  <?php else: ?>
                    <span style="opacity: 0.5;">Sin ejemplo registrado.</span>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>

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

  if ('speechSynthesis' in window) {
    window.speechSynthesis.onvoiceschanged = () => {};
  }
</script>
<script src="<?= PROYECTO_PATH ?>/assets/js/tema.js"></script>
</body>
</html>
