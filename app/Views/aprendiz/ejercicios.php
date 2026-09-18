<!DOCTYPE html>
<html lang="es" data-theme="dark">
<head>
  <meta charset="UTF-8">
  <link rel="icon" type="image/svg+xml" href="<?= PROYECTO_PATH ?>/assets/img/favicon.svg">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Ejercicios Clínicos — SmashCode</title>
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
            <i class="fas fa-dumbbell header-icon"></i>
            <div>
                <h1>Banco de Ejercicios Clínicos</h1>
                <p>Practica las estructuras y conceptos clínicos con ejercicios de auto-evaluación interactivos.</p>
            </div>
        </div>

        <?php if (empty($ejercicios)): ?>
          <div class="vacio-container">
            <i class="fas fa-dumbbell vacio-icono"></i>
            <h3>No hay ejercicios cargados aún</h3>
            <p class="vacio-texto">Completa el mapa formativo para desbloquear desafíos.</p>
          </div>
        <?php else: ?>
          <?php foreach ($ejercicios as $idx => $ej): ?>
            <div class="ejercicio-card" id="ejercicio-card-<?= $ej['id'] ?>">
              <span class="ejercicio-tipo"><?= str_replace('_', ' ', htmlspecialchars($ej['tipo'])) ?></span>
              <small class="ejercicio-nivel"><?= htmlspecialchars($ej['nivel_nombre']) ?></small>
              <div class="ejercicio-titulo"><?= htmlspecialchars($ej['enunciado']) ?></div>
              
              <div class="opciones-list">
                <?php foreach ($ej['opciones'] as $opc): ?>
                  <div class="opcion-item" onclick="checkPracticeOption('<?= $ej['id'] ?>', '<?= $opc['id'] ?>', <?= $opc['es_correcta'] ? 'true' : 'false' ?>, this)">
                    <span><?= htmlspecialchars($opc['texto']) ?></span>
                    <span class="retro-text" style="display:none;"><?= htmlspecialchars($opc['retroalimentacion']) ?></span>
                  </div>
                <?php endforeach; ?>
              </div>

              <div class="retro-box" id="retro-box-<?= $ej['id'] ?>">
                <i class="fas fa-info-circle retro-icon"></i>
                <span class="retro-content">Retroalimentación del ejercicio.</span>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
    </div>
  </main>
</div>

<script>
  function checkPracticeOption(ejId, opcId, esCorrecta, node) {
    let card = document.getElementById('ejercicio-card-' + ejId);
    let options = card.querySelectorAll('.opcion-item');
    let retroBox = document.getElementById('retro-box-' + ejId);

    // Desactivar clicks adicionales
    options.forEach(n => {
      n.style.pointerEvents = 'none';
      n.classList.remove('correct', 'incorrect');
    });

    let retroMsg = node.querySelector('.retro-text').textContent;

    if (esCorrecta) {
      node.classList.add('correct');
      retroBox.className = 'retro-box correct';
      retroBox.querySelector('.retro-content').textContent = retroMsg || '¡Respuesta correcta! Excelente.';
    } else {
      node.classList.add('incorrect');
      retroBox.className = 'retro-box incorrect';
      retroBox.querySelector('.retro-content').textContent = retroMsg || 'Respuesta incorrecta. Sigue practicando.';

      // Resaltar la correcta en verde
      options.forEach(n => {
        let isOptionCorrect = n.getAttribute('onclick').includes('true');
        if (isOptionCorrect) {
          n.classList.add('correct');
        }
      });
    }

    retroBox.style.display = 'block';
  }
</script>
<script src="<?= PROYECTO_PATH ?>/assets/js/tema.js"></script>
</body>
</html>
