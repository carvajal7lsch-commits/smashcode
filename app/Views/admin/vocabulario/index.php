<!DOCTYPE html>
<html lang="es" data-theme="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Vocabulario — Admin SmashCode</title>
  <link rel="stylesheet" href="<?= PROYECTO_PATH ?>/assets/css/estilos.css">
  <link rel="stylesheet" href="<?= PROYECTO_PATH ?>/assets/css/cruds.css?v=<?= time() ?>">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <script>(function(){var t=localStorage.getItem('smashcode_tema');if(t)document.documentElement.setAttribute('data-theme',t);})();</script>
  <style>
  </style>
</head>
<body>
<div class="contenedor-app">

  <?php include dirname(__DIR__) . '/partials/sidebar.php'; ?>

  <main class="contenido-principal">
    <header class="barra-superior barra-superior-admin">
      <div class="breadcrumb-admin">
        <i class="fas fa-home breadcrumb-icon"></i>
        <a href="<?= PROYECTO_PATH ?>/admin" class="breadcrumb-current" style="text-decoration:none;">Dashboard</a>
        <i class="fas fa-chevron-right breadcrumb-separator"></i>
        <a href="<?= PROYECTO_PATH ?>/admin/raps" class="breadcrumb-current" style="text-decoration:none;">RAPs</a>
        <i class="fas fa-chevron-right breadcrumb-separator"></i>
        <span class="breadcrumb-link"><i class="fas fa-spell-check" style="color:var(--azul); margin-right:4px;"></i> Vocabulario</span>
      </div>
      <div class="admin-header-actions">
        <!-- Botón cambio de tema -->
        <button id="btn-cambiar-tema" class="btn-tema" aria-label="Cambiar a modo claro" title="Cambiar a modo claro">
          <i class="fas fa-sun tema-icono"></i>
          <span class="tema-label">Claro</span>
        </button>
        <div class="avatar-usuario" title="<?= limpiar($_SESSION['nombre']) ?>">
          <?= strtoupper(substr($_SESSION['nombre'], 0, 1)) ?>
        </div>
      </div>
    </header>

    <div class="pagina-contenido">
      <div class="encabezado-seccion-admin">
        <div class="d-flex align-items-center" style="gap:12px;">
          <a href="<?= PROYECTO_PATH ?>/admin/raps" class="btn btn-gris" style="padding:8px 12px;">
            <i class="fas fa-arrow-left"></i>
          </a>
          <div>
            <h1 class="pagina-titulo">
              <i class="fas fa-book-medical header-titulo-icono"></i>
              Vocabulario: <?= $rap ? limpiar($rap['titulo']) : 'RAP Desconocido' ?>
            </h1>
            <p class="desc-seccion-admin">
              Gestiona los términos médicos de este RAP.
            </p>
          </div>
        </div>
        <div>
          <button type="button" onclick="abrirModalVocabulario()" class="btn btn-verde">
            <i class="fas fa-plus"></i> Añadir Término
          </button>
        </div>
      </div>

      <?php if ($exito): ?>
        <div class="alerta alerta-exito alerta-margen">
          <i class="fas fa-circle-check"></i>
          <?php
            $msgs = [
              'creado'      => 'Término creado correctamente.',
              'actualizado' => 'Término actualizado correctamente.',
              'estado'      => 'Estado actualizado.',
            ];
            echo $msgs[$exito] ?? 'Operación realizada.';
          ?>
        </div>
      <?php endif; ?>
      <?php if ($error): ?>
        <div class="alerta alerta-error alerta-margen">
          <i class="fas fa-triangle-exclamation"></i> <?= $error ?>
        </div>
      <?php endif; ?>

      <!-- Barra de filtros -->
      <?php if (!empty($vocabulario)): ?>
      <div class="barra-filtros" style="margin-bottom: 20px;">
        <div class="contenedor-input-search" style="max-width: 360px; margin: 0;">
          <i class="fas fa-search icono-search"></i>
          <input type="text" id="buscar-vocab" class="input-busqueda" placeholder="Buscar por término...">
        </div>
      </div>
      <?php endif; ?>

      <div class="tarjeta" style="padding:0; overflow:hidden;">
        <?php if (empty($vocabulario)): ?>
          <div class="empty-state-card">
            <div class="empty-state-icon">
              <i class="fas fa-book-medical"></i>
            </div>
            <h3>El vocabulario está vacío</h3>
            <p>Aún no has agregado ningún término médico a este RAP. Empieza añadiendo el primer término de vocabulario.</p>
            <button type="button" onclick="abrirModalVocabulario()" class="btn btn-verde empty-state-btn">
              <i class="fas fa-plus"></i> Añadir primer término
            </button>
          </div>
        <?php else: ?>
          <table class="tabla-usuarios w-100">
            <thead>
              <tr>
                <th class="w-60px text-center">Media</th>
                <th>Término (EN)</th>
                <th>Traducción (ES)</th>
                <th>Clasificación</th>
                <th class="w-100px text-center">Dificultad</th>
                <th class="w-80px text-center">Estado</th>
                <th class="w-100px text-center">Acciones</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($vocabulario as $v): ?>
              <tr class="<?= !$v['activo'] ? 'disabled-toggle' : '' ?>">
                <td class="text-center">
                  <div class="btn-icono-accion">
                    <?php if ($v['imagen_url']): ?>
                      <img src="<?= PROYECTO_PATH ?><?= $v['imagen_url'] ?>" class="mini-img" alt="IMG">
                    <?php else: ?>
                      <div class="mini-img mini-img-placeholder">
                        <i class="fas fa-image" style="opacity:0.3;"></i>
                      </div>
                    <?php endif; ?>
                    <?php if ($v['audio_url']): ?>
                      <button type="button" class="audio-btn" onclick="new Audio('<?= PROYECTO_PATH ?><?= $v['audio_url'] ?>').play()" title="Escuchar audio">
                        <i class="fas fa-volume-up"></i>
                      </button>
                    <?php else: ?>
                      <button type="button" class="audio-btn" onclick="speakTerm('<?= addslashes(limpiar($v['termino_en'])) ?>')" title="Escuchar pronunciación (TTS)">
                        <i class="fas fa-volume-up" style="color:var(--naranja);"></i>
                      </button>
                    <?php endif; ?>
                  </div>
                </td>
                <td>
                  <div class="vocab-termino-en">
                    <?= limpiar($v['termino_en']) ?>
                  </div>
                  <?php if ($v['transcripcion_ipa']): ?>
                    <div class="vocab-ipa">
                      /<?= limpiar($v['transcripcion_ipa']) ?>/
                    </div>
                  <?php endif; ?>
                </td>
                <td class="vocab-termino-es">
                  <?= limpiar($v['termino_es']) ?>
                </td>
                <td>
                  <?php if ($v['categoria_nombre']): ?>
                    <span class="badge-categoria">
                      <?= limpiar($v['categoria_nombre']) ?>
                    </span>
                  <?php endif; ?>
                  <?php if ($v['area_clinica_nombre']): ?>
                    <span class="badge-area">
                      <i class="fas fa-hospital" style="font-size:0.65rem;"></i> <?= limpiar($v['area_clinica_nombre']) ?>
                    </span>
                  <?php endif; ?>
                </td>
                <td class="text-center">
                  <?php if ($v['nivel_dificultad']): ?>
                    <?php
                      $colorDif = match($v['nivel_dificultad']) {
                        'basico' => 'text-verde',
                        'intermedio' => 'text-naranja',
                        'avanzado' => 'text-rojo',
                        default => 'text-tenue'
                      };
                    ?>
                    <span class="<?= $colorDif ?> font-weight-700 text-uppercase text-sm">
                      <?= limpiar($v['nivel_dificultad']) ?>
                    </span>
                  <?php else: ?>
                    <span class="text-tenue text-sm">-</span>
                  <?php endif; ?>
                </td>
                <td class="text-center">
                  <?php if ($v['activo']): ?>
                    <span class="badge-activo">Activo</span>
                  <?php else: ?>
                    <span class="badge-inactivo">Inactivo</span>
                  <?php endif; ?>
                </td>
                <td class="text-center">
                  <div class="btn-icono-accion">
                    <button type="button" class="btn-accion btn-editar" title="Editar"
                            data-id="<?= $v['id'] ?>"
                            data-termino_en="<?= limpiar($v['termino_en']) ?>"
                            data-termino_es="<?= limpiar($v['termino_es']) ?>"
                            data-transcripcion_ipa="<?= limpiar($v['transcripcion_ipa']) ?>"
                            data-oracion_ejemplo="<?= limpiar($v['oracion_ejemplo']) ?>"
                            data-categoria_id="<?= $v['categoria_id'] ?>"
                            data-area_clinica_id="<?= $v['area_clinica_id'] ?>"
                            data-nivel_dificultad="<?= $v['nivel_dificultad'] ?>"
                            data-audio_url="<?= $v['audio_url'] ?>"
                            data-imagen_url="<?= $v['imagen_url'] ?>"
                            onclick="abrirModalVocabulario(this)">
                      <i class="fas fa-pen"></i>
                    </button>
                    <form method="POST" action="<?= PROYECTO_PATH ?>/admin/vocabulario/toggle" class="d-inline">
                      <input type="hidden" name="csrf_token" value="<?= generarTokenCSRF() ?>">
                      <input type="hidden" name="id" value="<?= $v['id'] ?>">
                      <input type="hidden" name="rap_id" value="<?= $rapId ?>">
                      <button type="submit" class="btn-accion <?= $v['activo'] ? 'btn-suspender' : 'btn-activar' ?>" title="<?= $v['activo'] ? 'Desactivar' : 'Activar' ?>">
                        <i class="fas fa-<?= $v['activo'] ? 'ban' : 'check' ?>"></i>
                      </button>
                    </form>
                  </div>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>
      </div>

    </div>
  </main>

  <!-- Modal Formulario de Vocabulario -->
  <div class="modal-fondo" id="modal-vocab">
    <div class="modal-caja modal-caja-lg" style="position: relative; padding: 24px;">
      <button type="button" class="btn-cerrar-modal-top" onclick="cerrarModal('modal-vocab')" aria-label="Cerrar modal">
        <i class="fas fa-times"></i>
      </button>

      <p class="modal-titulo-premium" id="modal-vocab-titulo" style="margin-bottom: 20px;">Añadir Nuevo Término</p>
      
      <form id="form-vocabulario" class="formulario-perfil" method="POST" action="<?= PROYECTO_PATH ?>/admin/vocabulario/guardar" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= generarTokenCSRF() ?>">
        <input type="hidden" name="rap_id" value="<?= $rapId ?>">
        <input type="hidden" name="id" id="vocab-id" value="">
        <input type="hidden" name="audio_url_actual" id="vocab-audio-actual" value="">
        <input type="hidden" name="imagen_url_actual" id="vocab-imagen-actual" value="">

        <div class="grid-vocab-form">
          <!-- Columna Izquierda: Textos y Clasificación -->
          <div class="tarjeta tarjeta-margen" style="padding: 20px;">
            <h2 class="form-seccion-titulo" style="font-size: 1rem; margin-bottom: 16px;">Información Principal</h2>

            <div class="grupo-input">
              <div class="d-flex" style="justify-content:space-between; align-items:center;">
                <label class="label-input" for="termino_en">Término en Inglés <span class="text-rojo">*</span></label>
                <button type="button" class="btn-premium-blanco" style="font-size:0.75rem; padding:4px 8px;" onclick="sugerirConIA()">
                  <i class="fas fa-magic" style="color:var(--naranja);"></i> Sugerir con IA
                </button>
              </div>
              <input type="text" name="termino_en" id="vocab-termino-en" class="input-base" required>
            </div>

            <div class="grupo-input">
              <label class="label-input" for="termino_es">Traducción al Español <span class="text-rojo">*</span></label>
              <input type="text" name="termino_es" id="vocab-termino-es" class="input-base" required>
            </div>

            <div class="grupo-input">
              <label class="label-input" for="transcripcion_ipa">Transcripción IPA</label>
              <input type="text" name="transcripcion_ipa" id="vocab-ipa" class="input-base" placeholder="Ej: kɑːrˈdiː.ə">
            </div>

            <div class="grupo-input mb-0">
              <label class="label-input" for="oracion_ejemplo">Oración de Ejemplo (Opcional)</label>
              <textarea name="oracion_ejemplo" id="vocab-oracion" class="input-base" rows="3"></textarea>
            </div>
          </div>

          <!-- Columna Derecha: Multimedia y Clasificación -->
          <div class="tarjeta tarjeta-margen" style="padding: 20px;">
            <h2 class="form-seccion-titulo" style="font-size: 1rem; margin-bottom: 16px;">Contenido y Clasificación</h2>

            <div class="grupo-input">
              <div class="d-flex" style="justify-content:space-between; align-items:center;">
                <label class="label-input" for="audio">Archivo de Audio (Máx 2MB)</label>
                <button type="button" class="btn-premium-blanco" style="font-size:0.75rem; padding:4px 8px;" onclick="probarTTS()">
                  <i class="fas fa-volume-up" style="color:var(--naranja);"></i> Probar TTS
                </button>
              </div>
              <div id="vocab-audio-preview-container" style="display: none; margin-bottom: 12px;">
                <div class="vocab-audio-preview" style="background: rgba(0,0,0,0.1); padding: 10px; border-radius: 8px;">
                  <audio controls class="vocab-audio-player" id="vocab-audio-player" style="height: 30px; width: 100%;">
                    <source src="" id="vocab-audio-source" type="audio/mpeg">
                  </audio>
                  <div class="text-tenue text-sm mt-1">Audio Actual</div>
                </div>
              </div>
              <input type="file" name="audio" id="audio" class="input-base" accept="audio/*">
              <small class="form-hint">Si subes uno nuevo, reemplazará al actual. (MP3, OGG, WAV)</small>
            </div>

            <hr style="border:none; border-top:1px solid rgba(255,255,255,0.05); margin:20px 0;">

            <!-- Imagen -->
            <div class="grupo-input mb-0">
              <label class="label-input" for="imagen">Imagen Ilustrativa</label>
              <div id="vocab-img-preview-container" style="display: none; margin-bottom: 12px; text-align: center;">
                <img src="" id="vocab-img-preview" alt="Imagen actual" style="max-height: 120px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.1);">
                <div class="text-tenue text-sm mt-1">Imagen Actual</div>
              </div>
              <input type="file" name="imagen" id="imagen" class="input-base" accept="image/*">
            </div>

            <hr style="border:none; border-top:1px solid rgba(255,255,255,0.05); margin:16px 0;">

            <div class="grid-2-col">
              <div class="grupo-input mb-0">
                <label class="label-input" for="categoria_id">Categoría</label>
                <select name="categoria_id" id="vocab-categoria" class="input-base">
                  <option value="">-- Ninguna --</option>
                  <?php foreach ($categorias as $cat): ?>
                    <option value="<?= $cat['id'] ?>"><?= limpiar($cat['nombre']) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div class="grupo-input mb-0">
                <label class="label-input" for="area_clinica_id">Área Clínica</label>
                <select name="area_clinica_id" id="vocab-area" class="input-base">
                  <option value="">-- Ninguna --</option>
                  <?php foreach ($areas as $area): ?>
                    <option value="<?= $area['id'] ?>"><?= limpiar($area['nombre']) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>

            <div class="grupo-input mb-0" style="margin-top: 16px;">
              <label class="label-input" for="nivel_dificultad">Nivel de Dificultad</label>
              <select name="nivel_dificultad" id="vocab-dificultad" class="input-base">
                <option value="basico">Básico</option>
                <option value="intermedio" selected>Intermedio</option>
                <option value="avanzado">Avanzado</option>
              </select>
            </div>
          </div>
        </div>

        <div class="modal-acciones modal-acciones-gap" style="margin-top: 24px; padding-top: 20px; border-top: 1px solid rgba(255,255,255,0.05);">
          <button type="button" class="btn btn-gris" onclick="cerrarModal('modal-vocab')">Cancelar</button>
          <button type="submit" class="btn btn-verde"><i class="fas fa-save"></i> Guardar Término</button>
        </div>
      </form>
    </div>
  </div>

</div>
<script src="<?= PROYECTO_PATH ?>/assets/js/tema.js"></script>
<script src="<?= PROYECTO_PATH ?>/assets/js/admin_cruds.js"></script>
<script>
  document.addEventListener('DOMContentLoaded', () => {
    inicializarBusqueda('buscar-vocab', '.tabla-usuarios tbody tr');
  });

  function abrirModalVocabulario(btn = null) {
    const form = document.getElementById('form-vocabulario');
    const actionBase = "<?= PROYECTO_PATH ?>/admin/vocabulario";
    
    // Reseteamos el formulario
    form.reset();
    document.getElementById('vocab-id').value = '';
    document.getElementById('vocab-audio-actual').value = '';
    document.getElementById('vocab-imagen-actual').value = '';
    
    document.getElementById('vocab-audio-preview-container').style.display = 'none';
    document.getElementById('vocab-img-preview-container').style.display = 'none';

    if (btn) {
      // Modo Edición
      document.getElementById('modal-vocab-titulo').textContent = 'Editar Término';
      form.action = actionBase + '/actualizar';

      document.getElementById('vocab-id').value = btn.dataset.id || '';
      document.getElementById('vocab-termino-en').value = btn.dataset.termino_en || '';
      document.getElementById('vocab-termino-es').value = btn.dataset.termino_es || '';
      document.getElementById('vocab-ipa').value = btn.dataset.transcripcion_ipa || '';
      document.getElementById('vocab-oracion').value = btn.dataset.oracion_ejemplo || '';
      document.getElementById('vocab-categoria').value = btn.dataset.categoria_id || '';
      document.getElementById('vocab-area').value = btn.dataset.area_clinica_id || '';
      document.getElementById('vocab-dificultad').value = btn.dataset.nivel_dificultad || 'intermedio';

      const audioUrl = btn.dataset.audio_url;
      if (audioUrl) {
        document.getElementById('vocab-audio-actual').value = audioUrl;
        document.getElementById('vocab-audio-source').src = "<?= PROYECTO_PATH ?>" + audioUrl;
        document.getElementById('vocab-audio-player').load();
        document.getElementById('vocab-audio-preview-container').style.display = 'block';
      }

      const imgUrl = btn.dataset.imagen_url;
      if (imgUrl) {
        document.getElementById('vocab-imagen-actual').value = imgUrl;
        document.getElementById('vocab-img-preview').src = "<?= PROYECTO_PATH ?>" + imgUrl;
        document.getElementById('vocab-img-preview-container').style.display = 'block';
      }
    } else {
      // Modo Creación
      document.getElementById('modal-vocab-titulo').textContent = 'Añadir Nuevo Término';
      form.action = actionBase + '/guardar';
    }

    document.getElementById('modal-vocab').classList.add('visible');
  }

  // --- Lógica de Sugerencia con IA ---
  async function sugerirConIA() {
    const terminoEn = document.getElementById('vocab-termino-en').value.trim();
    if (!terminoEn) {
      alert("Por favor, ingresa el término en inglés primero.");
      return;
    }

    const btn = event.currentTarget;
    const oldHtml = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sugiriendo...';
    btn.disabled = true;

    try {
      const formData = new FormData();
      formData.append('termino', terminoEn);

      const response = await fetch("<?= PROYECTO_PATH ?>/admin/vocabulario/sugerir", {
        method: 'POST',
        body: formData
      });

      const text = await response.text();
      let data;
      try {
        data = JSON.parse(text);
      } catch(e) {
        throw new Error("Respuesta inválida del servidor");
      }

      if (data.error) {
        throw new Error(data.error);
      }

      if (data.traduccion) document.getElementById('vocab-termino-es').value = data.traduccion;
      if (data.ipa) document.getElementById('vocab-ipa').value = data.ipa;
      if (data.oracion) document.getElementById('vocab-oracion').value = data.oracion;
      
    } catch (error) {
      alert("Error al sugerir con IA: " + error.message);
    } finally {
      btn.innerHTML = oldHtml;
      btn.disabled = false;
    }
  }

  // --- Lógica de TTS (Text-to-Speech) ---
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
    } else {
      alert("Tu navegador no soporta síntesis de voz (TTS).");
    }
  }

  function probarTTS() {
    const terminoEn = document.getElementById('vocab-termino-en').value.trim();
    if (!terminoEn) {
      alert("Por favor, ingresa un término en inglés para probar el TTS.");
      return;
    }
    speakTerm(terminoEn);
  }
</script>
</body>
</html>
