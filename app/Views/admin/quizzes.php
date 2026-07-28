<!DOCTYPE html>
<html lang="es" data-theme="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Quiz — Admin SmashCode</title>
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
        <a href="<?= PROYECTO_PATH ?>/admin/raps" class="breadcrumb-current" style="text-decoration:none;">RAPs</a>
        <i class="fas fa-chevron-right breadcrumb-separator"></i>
        <span class="breadcrumb-link"><i class="fas fa-circle-question" style="color:var(--verde); margin-right:4px;"></i> Quiz</span>
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
      <?php
      $ordenRap = $rap['orden'] ?? 1;
      $tituloRap = isset($rap['titulo']) ? str_replace(['ÔÇö', 'Â', 'Basico'], ['—', '', 'Básico'], $rap['titulo']) : "RAP $ordenRap";
      ?>

      <form id="form-quiz" onsubmit="guardarQuiz(event)">
        <input type="hidden" name="rap_id" value="<?= $rap['id'] ?>">
        <input type="hidden" name="csrf_token" value="<?= generarTokenCSRF() ?>">

        <div class="encabezado-seccion-admin" style="margin-bottom: 24px;">
          <div style="display: flex !important; align-items: center; gap: 14px;">
            <a href="<?= PROYECTO_PATH ?>/admin/raps" class="btn btn-gris" style="width:40px; height:40px; border-radius:50%; padding:0; display:inline-flex; align-items:center; justify-content:center; flex-shrink:0;" title="Volver a RAPs">
              <i class="fas fa-arrow-left" style="font-size:1rem;"></i>
            </a>
            <div>
              <h1 class="pagina-titulo" style="margin:0; font-size:1.6rem; font-weight:800; color:var(--texto-principal); letter-spacing:-0.5px; display:flex; align-items:center; gap:10px;">
                <i class="fas fa-circle-question" style="color:var(--verde); font-size:1.3rem;"></i>
                Quiz: <?= limpiar($tituloRap) ?>
              </h1>
              <p class="desc-seccion-admin" style="margin:4px 0 0 0; font-size:0.88rem; color:var(--texto-secundario); font-weight:500;">
                Configura los parámetros del examen y las preguntas de evaluación (Momento 4: Closure).
              </p>
            </div>
          </div>
          <div>
            <button type="submit" class="btn btn-verde" id="btn-guardar-quiz" style="padding:10px 20px; font-weight:700;">
              <i class="fas fa-save"></i> Guardar Cambios
            </button>
          </div>
        </div>

        <!-- Parámetros del Quiz -->
        <div class="tarjeta" style="margin-bottom: 24px; padding: 20px;">
          <h2 class="form-seccion-titulo" style="font-size: 1.05rem; margin-bottom: 16px; font-weight:800; color:var(--texto-principal);">
            <i class="fas fa-sliders" style="color:var(--azul); margin-right:8px;"></i> Parámetros de Evaluación
          </h2>

          <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap:16px;">
            <div class="grupo-input" style="margin:0;">
              <label class="label-input">Puntaje Mínimo de Aprobación (%)</label>
              <input type="number" step="0.01" class="input-base" name="puntaje_minimo" value="<?= $quiz ? (float)$quiz['puntaje_minimo'] : 60.00 ?>" required min="0" max="100">
            </div>

            <div class="grupo-input" style="margin:0;">
              <label class="label-input">Límite de Tiempo (Segundos)</label>
              <input type="number" class="input-base" name="limite_tiempo_seg" value="<?= $quiz ? (int)$quiz['limite_tiempo_seg'] : 300 ?>" required min="30" step="10">
              <small class="form-hint">300 seg = 5 minutos.</small>
            </div>

            <div class="grupo-input" style="margin:0;">
              <label class="label-input">Máximo de Intentos Permitidos</label>
              <input type="number" class="input-base" name="max_intentos" value="<?= $quiz ? (int)$quiz['max_intentos'] : 3 ?>" required min="1" max="10">
            </div>
          </div>
        </div>

        <!-- Creador de Preguntas -->
        <div class="tarjeta" style="padding:20px;">
          <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
            <h2 class="form-seccion-titulo" style="font-size: 1.05rem; margin:0; font-weight:800; color:var(--texto-principal);">
              <i class="fas fa-list-check" style="color:var(--verde); margin-right:8px;"></i> Preguntas del Quiz
            </h2>
            <button type="button" class="btn btn-verde" style="padding:6px 14px; font-size:0.8rem; font-weight:700;" onclick="agregarPregunta()">
              <i class="fas fa-plus"></i> Añadir Pregunta
            </button>
          </div>

          <div id="contenedor-preguntas" style="display:flex; flex-direction:column; gap:20px;">
            <!-- Preguntas dinámicas -->
          </div>
        </div>
      </form>

    </div>
  </main>
</div>

<style>
  .pregunta-card {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.04);
  }
  .pregunta-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 14px;
    padding-bottom: 10px;
    border-bottom: 1px solid var(--border-color);
  }
</style>

<script>
  let preguntaIndex = 0;
  const preguntasExistentes = <?= json_encode($preguntas) ?>;

  document.addEventListener('DOMContentLoaded', () => {
    if (preguntasExistentes && preguntasExistentes.length > 0) {
      preguntasExistentes.forEach(p => agregarPregunta(p));
    } else {
      // 5 preguntas por defecto
      for (let i = 0; i < 5; i++) agregarPregunta();
    }
  });

  function agregarPregunta(data = null) {
    const container = document.getElementById('contenedor-preguntas');
    const idx = preguntaIndex++;

    const id = data && data.id ? data.id : '';
    const texto = data ? data.texto : '';
    const respCorrecta = data ? data.respuesta_correcta : '';
    const retro = data && data.retroalimentacion ? data.retroalimentacion : '';
    const opciones = data && data.opciones_arr ? data.opciones_arr : ['', '', '', ''];

    let opcionesHtml = '';
    opciones.forEach((optText, optIdx) => {
      const isSelected = (optText !== '' && optText === respCorrecta) ? 'checked' : (optIdx === 0 && !respCorrecta ? 'checked' : '');
      opcionesHtml += `
        <div style="display:flex; align-items:center; gap:8px; margin-bottom:8px;">
          <input type="radio" name="preguntas[${idx}][respuesta_correcta_idx]" value="${optIdx}" ${isSelected} required style="cursor:pointer;" title="Marcar como respuesta correcta">
          <input type="text" class="input-base" name="preguntas[${idx}][opciones][${optIdx}]" value="${optText.replace(/"/g, '&quot;')}" placeholder="Opción ${optIdx + 1}" required onkeyup="sincronizarRespuestaCorrecta(${idx})">
        </div>
      `;
    });

    const html = `
      <div class="pregunta-card" id="preg-card-${idx}">
        <input type="hidden" name="preguntas[${idx}][id]" value="${id}">
        <div class="pregunta-header">
          <span style="font-weight:800; color:var(--azul); font-size:1rem;">Pregunta #${idx + 1}</span>
          <button type="button" class="btn-accion btn-suspender" style="width:32px; height:32px;" onclick="document.getElementById('preg-card-${idx}').remove()">
            <i class="fas fa-trash" style="font-size:0.85rem;"></i>
          </button>
        </div>

        <input type="hidden" name="preguntas[${idx}][respuesta_correcta]" id="preg-resp-correcta-${idx}" value="${respCorrecta.replace(/"/g, '&quot;')}">

        <div class="grupo-input">
          <label class="label-input">Enunciado de la Pregunta <span class="text-rojo">*</span></label>
          <input type="text" class="input-base" name="preguntas[${idx}][texto]" value="${texto.replace(/"/g, '&quot;')}" placeholder="Ej: Which greeting is appropriate for 8:00 AM?" required>
        </div>

        <div class="grupo-input">
          <label class="label-input" style="margin-bottom:8px; display:block;">Opciones de Respuesta (Selecciona el botón de la opción correcta) <span class="text-rojo">*</span></label>
          <div id="opts-container-${idx}">
            ${opcionesHtml}
          </div>
        </div>

        <div class="grupo-input" style="margin-bottom:0;">
          <label class="label-input">Retroalimentación / Explicación (Opcional)</label>
          <input type="text" class="input-base" style="font-size:0.85rem;" name="preguntas[${idx}][retroalimentacion]" value="${retro.replace(/"/g, '&quot;')}" placeholder="Ej: Good morning is used between 6am and 12pm.">
        </div>
      </div>
    `;
    container.insertAdjacentHTML('beforeend', html);
    sincronizarRespuestaCorrecta(idx);
  }

  function sincronizarRespuestaCorrecta(idx) {
    const card = document.getElementById(`preg-card-${idx}`);
    if (!card) return;

    const selectedRadio = card.querySelector(`input[name="preguntas[${idx}][respuesta_correcta_idx]"]:checked`);
    if (selectedRadio) {
      const optIdx = selectedRadio.value;
      const optInput = card.querySelector(`input[name="preguntas[${idx}][opciones][${optIdx}]"]`);
      const targetHidden = document.getElementById(`preg-resp-correcta-${idx}`);
      if (optInput && targetHidden) {
        targetHidden.value = optInput.value;
      }
    }
  }

  document.addEventListener('change', (e) => {
    if (e.target && e.target.name && e.target.name.includes('[respuesta_correcta_idx]')) {
      const match = e.target.name.match(/preguntas\[(\d+)\]/);
      if (match) {
        sincronizarRespuestaCorrecta(match[1]);
      }
    }
  });

  function guardarQuiz(e) {
    e.preventDefault();

    // Sincronizar todas las respuestas correctas antes de enviar
    const cards = document.querySelectorAll('.pregunta-card');
    cards.forEach(card => {
      const match = card.id.match(/preg-card-(\d+)/);
      if (match) sincronizarRespuestaCorrecta(match[1]);
    });

    const formData = new FormData(e.target);
    const btn = document.getElementById('btn-guardar-quiz');
    btn.disabled = true;
    btn.textContent = 'Guardando...';

    fetch('<?= PROYECTO_PATH ?>/admin/quizzes/save', {
      method: 'POST',
      body: formData
    })
    .then(r => r.json())
    .then(data => {
      if (data.success) {
        alert('Quiz actualizado correctamente.');
        window.location.reload();
      } else {
        alert(data.error || 'Ocurrió un error al guardar');
        btn.disabled = false;
        btn.textContent = 'Guardar Cambios';
      }
    })
    .catch(err => {
      console.error(err);
      alert('Error de conexión');
      btn.disabled = false;
      btn.textContent = 'Guardar Cambios';
    });
  }

  // Cambio de tema JS
  const btnTema = document.getElementById('btn-cambiar-tema');
  const html = document.documentElement;
  const icono = btnTema.querySelector('i');
  const label = btnTema.querySelector('span');

  function updateThemeButton(theme) {
    if (theme === 'light') {
      icono.className = 'fas fa-moon';
      label.textContent = 'Oscuro';
    } else {
      icono.className = 'fas fa-sun';
      label.textContent = 'Claro';
    }
  }

  if (btnTema) {
    updateThemeButton(html.getAttribute('data-theme'));
    btnTema.addEventListener('click', () => {
      const currentTheme = html.getAttribute('data-theme');
      const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
      html.setAttribute('data-theme', newTheme);
      localStorage.setItem('smashcode_tema', newTheme);
      updateThemeButton(newTheme);
    });
  }
</script>
</body>
</html>
