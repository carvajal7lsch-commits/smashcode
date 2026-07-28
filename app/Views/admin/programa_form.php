<!DOCTYPE html>
<html lang="es" data-theme="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $modoEditar ? 'Editar' : 'Nuevo' ?> Programa — Admin SmashCode</title>
  <link rel="stylesheet" href="<?= PROYECTO_PATH ?>/assets/css/estilos.css">
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
        <a href="<?= PROYECTO_PATH ?>/admin/programas" class="breadcrumb-current" style="text-decoration:none;">Programas</a>
        <i class="fas fa-chevron-right breadcrumb-separator"></i>
        <span class="breadcrumb-link"><i class="fas fa-<?= $modoEditar ? 'pen-to-square' : 'plus-circle' ?>" style="color:var(--azul); margin-right:4px;"></i> <?= $modoEditar ? 'Editar' : 'Nuevo' ?></span>
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
      <div style="max-width:600px; margin:0 auto;">

        <h1 class="pagina-titulo">
          <i class="fas fa-graduation-cap" style="color:var(--verde-acento);"></i>
          <?= $modoEditar ? 'Editar Programa de Formación' : 'Nuevo Programa de Formación' ?>
        </h1>

        <?php if ($error): ?>
          <div class="alerta alerta-error" style="margin-top:12px;">
            <i class="fas fa-triangle-exclamation"></i> <?= $error ?>
          </div>
        <?php endif; ?>

        <div class="tarjeta glass-panel" style="margin-top:20px; padding:0; overflow:hidden; border-radius: var(--radio);">
          <!-- Cabecera de la tarjeta con gradiente -->
          <div class="cabecera-tarjeta-premium cabecera-tarjeta-verde">
            <div class="cabecera-icono-wrap">
              <i class="fas fa-graduation-cap"></i>
            </div>
            <div>
              <h2 style="font-size: 1.25rem; font-weight: 800; margin: 0; text-transform: uppercase; letter-spacing: 0.05em; color: #fff;">
                <?= $modoEditar ? 'Editar Programa' : 'Nuevo Programa' ?>
              </h2>
              <p style="font-size: 0.78rem; opacity: 0.85; margin: 4px 0 0 0; font-weight: 600;">
                <?= $modoEditar ? 'Modifica los campos del programa de formación.' : 'Completa los campos para crear un nuevo programa.' ?>
              </p>
            </div>
          </div>
          <form method="POST"
                action="<?= PROYECTO_PATH ?>/admin/programas/<?= $modoEditar ? 'actualizar' : 'guardar' ?>"
                novalidate id="form-programa" style="padding: 32px;">
            <input type="hidden" name="csrf_token" value="<?= generarTokenCSRF() ?>">
            <?php if ($modoEditar): ?>
              <input type="hidden" name="id" value="<?= $programa['id'] ?>">
            <?php endif; ?>

            <!-- Nombre -->
            <div class="grupo-campo">
              <label class="etiqueta-campo" for="nombre">Nombre del Programa *</label>
              <div class="contenedor-input">
                <i class="fas fa-graduation-cap icono-input"></i>
                <input type="text" id="nombre" name="nombre" class="campo-input"
                       placeholder="Ej: Técnico en Enfermería"
                       maxlength="255" required
                       value="<?= limpiar($programa['nombre'] ?? '') ?>">
              </div>
              <span class="ayuda-campo">Nombre oficial del programa de formación SENA.</span>
            </div>

            <!-- Descripción -->
            <div class="grupo-campo">
              <label class="etiqueta-campo" for="descripcion">
                Descripción <span style="color:var(--texto-tenue); font-weight:400;">(Opcional)</span>
              </label>
              <div class="contenedor-input">
                <i class="fas fa-align-left icono-input" style="top: 18px; transform: none; color: var(--texto-tenue);"></i>
                <textarea id="descripcion" name="descripcion" class="campo-input"
                          placeholder="Descripción breve del programa y su contexto..."
                          rows="4" maxlength="500"
                          style="resize:vertical; padding: 12px 14px 12px 38px; min-height: 120px;"><?= limpiar($programa['descripcion'] ?? '') ?></textarea>
              </div>
              <div style="display:flex; justify-content:space-between; margin-top:4px;">
                <span class="ayuda-campo">Máximo 500 caracteres.</span>
                <span id="contador-desc" style="font-size:.72rem; color:var(--texto-tenue);">
                  <?= strlen($programa['descripcion'] ?? '') ?>/500
                </span>
              </div>
            </div>

            <?php if ($modoEditar && !$programa['activo']): ?>
              <div class="alerta" style="background:rgba(255,150,0,.08); border:1px solid rgba(255,150,0,.3); color:#FF9600; font-size:var(--texto-xs); padding:10px 14px; border-radius:var(--radio-sm); margin-bottom:16px;">
                <i class="fas fa-triangle-exclamation"></i>
                Este programa está <strong>inactivo</strong>. No aparecerá en los selectores hasta que lo actives desde la lista.
              </div>
            <?php endif; ?>

            <div style="display:flex; gap:10px; margin-top:8px;">
              <button type="submit" class="btn btn-verde" id="btn-guardar-programa">
                <i class="fas fa-<?= $modoEditar ? 'floppy-disk' : 'plus' ?>"></i>
                <?= $modoEditar ? 'Guardar Cambios' : 'Crear Programa' ?>
              </button>
              <a href="<?= PROYECTO_PATH ?>/admin/programas" class="btn btn-gris">
                <i class="fas fa-arrow-left"></i> Cancelar
              </a>
            </div>
          </form>
        </div>

      </div>
    </div><!-- /pagina-contenido -->
  </main>
</div>

<script src="<?= PROYECTO_PATH ?>/assets/js/tema.js"></script>
<script>
// Contador de caracteres para la descripción
(function() {
  var textarea = document.getElementById('descripcion');
  var contador = document.getElementById('contador-desc');
  if (textarea && contador) {
    textarea.addEventListener('input', function() {
      var len = this.value.length;
      contador.textContent = len + '/500';
      contador.style.color = len > 450 ? 'var(--rojo)' : 'var(--texto-tenue)';
    });
  }
})();
</script>
</body>
</html>
