<!DOCTYPE html>
<html lang="es" data-theme="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Editar Nivel <?= (int)$nivel['orden'] ?> — Admin SmashCode</title>
  <meta name="description" content="Formulario de edición del nivel <?= limpiar($nivel['nombre']) ?> en SmashCode.">
  <link rel="stylesheet" href="<?= PROYECTO_PATH ?>/assets/css/estilos.css?v=<?= time() ?>">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <script>(function(){var t=localStorage.getItem('smashcode_tema');if(t)document.documentElement.setAttribute('data-theme',t);})();</script>
  <link rel="stylesheet" href="<?= PROYECTO_PATH ?>/assets/css/cruds.css?v=<?= time() ?>">
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
        <a href="<?= PROYECTO_PATH ?>/admin/niveles" class="breadcrumb-current" style="text-decoration:none;">Niveles</a>
        <i class="fas fa-chevron-right breadcrumb-separator"></i>
        <span class="breadcrumb-link"><i class="fas fa-pen-to-square" style="color:var(--azul); margin-right:4px;"></i> Editar Nivel <?= (int)$nivel['orden'] ?></span>
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
        <div>
          <h1 class="titulo-seccion-admin">
            <i class="fas fa-pen-to-square icono-seccion-admin"></i>
            Editar Nivel
          </h1>
          <p class="desc-seccion-admin">
            Modifica los atributos del nivel. Los cambios se reflejan inmediatamente para los aprendices.
          </p>
        </div>
        <a href="<?= PROYECTO_PATH ?>/admin/niveles" class="btn btn-gris">
          <i class="fas fa-arrow-left"></i> Volver
        </a>
      </div>

      <div class="tarjeta glass-panel tarjeta-premium" style="max-width: 680px; margin: 0 auto; display: block; text-align: left;">
        <div class="cabecera-tarjeta-premium cabecera-tarjeta-verde">
          <div class="cabecera-icono-wrap">
            <i class="fas fa-layer-group"></i>
          </div>
          <div>
            <h2 class="modal-titulo-premium" style="margin: 0; color: #fff;">
              Editar Nivel <?= (int)$nivel['orden'] ?>
            </h2>
            <p class="modal-desc-premium" style="margin: 4px 0 0 0; color: rgba(255,255,255,0.85);">
              Modifica los atributos, umbrales y la portada del nivel MCER.
            </p>
          </div>
        </div>
        <form id="form-editar-nivel" method="POST" action="<?= PROYECTO_PATH ?>/admin/niveles/actualizar" novalidate>
          <input type="hidden" name="csrf_token" value="<?= generarTokenCSRF() ?>">
          <input type="hidden" name="id"         value="<?= limpiar($nivel['id']) ?>">

          <!-- Nombre -->
          <div class="grupo-campo">
            <label for="nombre-nivel" class="etiqueta-campo">Nombre del Nivel *</label>
            <div class="contenedor-input">
              <i class="fas fa-layer-group icono-input"></i>
              <input type="text" id="nombre-nivel" name="nombre"
                     class="campo-input"
                     value="<?= limpiar($nivel['nombre']) ?>"
                     maxlength="255" required
                     placeholder="Ej: Nivel 1 - A1 Básico">
            </div>
            <p class="form-hint ayuda-campo" style="margin-top: 6px;">Identificador visible para aprendices e instructores.</p>
          </div>

          <!-- Descripción -->
          <div class="grupo-campo">
            <label for="descripcion-nivel" class="etiqueta-campo">Descripción</label>
            <div class="contenedor-input">
              <i class="fas fa-align-left icono-input" style="top: 18px; transform: none; color: var(--texto-tenue);"></i>
              <textarea id="descripcion-nivel" name="descripcion"
                        class="campo-input"
                        maxlength="500"
                        placeholder="Describe el contenido y objetivos de este nivel..."
                        style="resize:vertical; min-height:100px; padding-top: 12px;"><?= limpiar($nivel['descripcion'] ?? '') ?></textarea>
            </div>
            <p class="form-hint ayuda-campo" style="margin-top: 6px;">Máximo 500 caracteres. Visible en el mapa de niveles del aprendiz.</p>
          </div>

          <!-- URL de imagen -->
          <div class="grupo-campo">
            <label for="imagen-url-nivel" class="etiqueta-campo">URL de imagen de portada</label>
            <div class="contenedor-input">
              <i class="fas fa-image icono-input"></i>
              <input type="url" id="imagen-url-nivel" name="imagen_url"
                     class="campo-input"
                     value="<?= limpiar($nivel['imagen_url'] ?? '') ?>"
                     placeholder="https://ejemplo.com/imagen.jpg">
            </div>
            <p class="form-hint ayuda-campo" style="margin-top: 6px;">Imagen de portada del nivel (PNG, JPG o WebP recomendado, mín. 600×300 px).</p>
            <!-- Preview -->
            <div class="preview-placeholder" id="preview-placeholder-<?= (int)$nivel['orden'] ?>">
              <i class="fas fa-image"></i> Vista previa de imagen
            </div>
            <img id="preview-img-nivel" class="preview-imagen"
                 src="<?= limpiar($nivel['imagen_url'] ?? '') ?>"
                 alt="Preview portada nivel">
          </div>

          <!-- Umbral + RAPs -->
          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 22px;">
            <div class="grupo-campo" style="margin-bottom:0;">
              <label for="umbral-nivel" class="etiqueta-campo">Umbral de desbloqueo (%)</label>
              <div class="contenedor-input">
                <i class="fas fa-percentage icono-input"></i>
                <input type="number" id="umbral-nivel" name="umbral_desbloqueo"
                       class="campo-input"
                       value="<?= number_format((float)$nivel['umbral_desbloqueo'], 2) ?>"
                       min="0" max="100" step="0.01"
                       <?= (int)$nivel['orden'] === 1 ? 'disabled title="El Nivel 1 siempre está disponible (0%)"' : '' ?>>
              </div>
              <p class="form-hint ayuda-campo" style="margin-top: 6px;">
                <?= (int)$nivel['orden'] === 1 ? '⚡ El Nivel 1 siempre es accesible sin requisito previo.' : '% mínimo del nivel anterior para desbloquear este.' ?>
              </p>
            </div>

            <div class="grupo-campo" style="margin-bottom:0;">
              <label class="etiqueta-campo">RAPs configurados</label>
              <div style="padding:12px 14px; background:rgba(31,47,54,0.4); border:2px solid var(--borde-sutil); border-radius:12px; font-size:0.9rem; color:var(--texto-secundario); display: flex; align-items: center; min-height: 48px; box-sizing: border-box;">
                <i class="fas fa-file-lines" style="color:#8B5CF6; margin-right:8px;"></i>
                <span><?= (int)$nivel['total_raps'] ?> RAP<?= (int)$nivel['total_raps'] !== 1 ? 's' : '' ?> asignado<?= (int)$nivel['total_raps'] !== 1 ? 's' : '' ?></span>
              </div>
              <p class="form-hint ayuda-campo" style="margin-top: 6px;">Gestiona los RAPs desde el módulo de RAPs.</p>
            </div>
          </div>

          <!-- Toggle activo -->
          <div class="grupo-campo" style="margin-top:22px;">
            <label class="etiqueta-campo">Estado del nivel</label>
            <div class="toggle-activo">
              <label class="toggle-switch" for="activo-nivel">
                <input type="checkbox" id="activo-nivel" name="activo" value="1"
                       <?= $nivel['activo'] ? 'checked' : '' ?>
                       <?= (int)$nivel['orden'] === 1 ? 'disabled title="El Nivel 1 no puede desactivarse."' : '' ?>>
                <span class="slider"></span>
              </label>
              <div>
                <span class="toggle-label-principal" id="estado-label-nivel">
                  <?= $nivel['activo'] ? 'Activo' : 'Inactivo' ?>
                </span>
                <p class="toggle-label-desc">
                  <?= (int)$nivel['orden'] === 1 ? 'El Nivel 1 siempre debe estar activo.' : 'Un nivel inactivo no es visible para los aprendices.' ?>
                </p>
              </div>
            </div>
          </div>

          <!-- Botones -->
          <div class="form-nivel-acciones">
            <button type="submit" id="btn-guardar-nivel" class="btn btn-verde">
              <i class="fas fa-floppy-disk"></i> Guardar cambios
            </button>
            <a href="<?= PROYECTO_PATH ?>/admin/niveles" class="btn btn-gris">
              <i class="fas fa-xmark"></i> Cancelar
            </a>
          </div>
        </form>
      </div>

    </div><!-- /pagina-contenido -->
  </main>
</div>

<script>
  // Preview de imagen
  const inputImg  = document.getElementById('imagen-url-nivel');
  const previewImg = document.getElementById('preview-img-nivel');
  const placeholder = document.getElementById('preview-placeholder-<?= (int)$nivel['orden'] ?>');

  function actualizarPreview() {
    const url = inputImg.value.trim();
    if (url) {
      previewImg.src = url;
      previewImg.style.display = 'block';
      placeholder.style.display = 'none';
    } else {
      previewImg.style.display = 'none';
      placeholder.style.display = 'flex';
    }
  }
  inputImg.addEventListener('input', actualizarPreview);
  // Mostrar si ya hay imagen
  if (inputImg.value.trim()) {
    previewImg.style.display = 'block';
    placeholder.style.display = 'none';
  }

  // Toggle label dinámico
  const toggleInput = document.getElementById('activo-nivel');
  const estadoLabel = document.getElementById('estado-label-nivel');
  if (toggleInput) {
    toggleInput.addEventListener('change', () => {
      estadoLabel.textContent = toggleInput.checked ? 'Activo' : 'Inactivo';
    });
  }

  // Validación básica del formulario
  document.getElementById('form-editar-nivel').addEventListener('submit', function(e) {
    const nombre = document.getElementById('nombre-nivel').value.trim();
    if (!nombre) {
      e.preventDefault();
      alert('El nombre del nivel es obligatorio.');
      document.getElementById('nombre-nivel').focus();
    }
  });
</script>
<script src="<?= PROYECTO_PATH ?>/assets/js/tema.js"></script>
</body>
</html>
