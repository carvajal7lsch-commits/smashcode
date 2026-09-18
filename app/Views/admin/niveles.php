<!DOCTYPE html>
<html lang="es" data-theme="dark">
<head>
  <meta charset="UTF-8">
  <link rel="icon" type="image/svg+xml" href="<?= PROYECTO_PATH ?>/assets/img/favicon.svg">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Gestión de Niveles — Admin SmashCode</title>
  <meta name="description" content="Panel de administración: gestiona los niveles del programa de inglés médico SmashCode.">
  <link rel="stylesheet" href="<?= PROYECTO_PATH ?>/assets/css/estilos.css?v=<?= time() ?>">
  <link rel="stylesheet" href="<?= PROYECTO_PATH ?>/assets/css/cruds.css?v=<?= time() ?>">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <script>(function(){var t=localStorage.getItem('smashcode_tema');if(t)document.documentElement.setAttribute('data-theme',t);})();</script>
</head>
<body>
<div class="contenedor-app">

  <!-- Barra lateral admin -->
   
  <?php include __DIR__ . '/partials/sidebar.php'; ?>

  <main class="contenido-principal">
    <header class="barra-superior barra-superior-admin">
      <div class="breadcrumb-admin">
        <i class="fas fa-home breadcrumb-icon"></i>
        <a href="<?= PROYECTO_PATH ?>/admin" class="breadcrumb-current" style="text-decoration:none;">Dashboard</a>
        <i class="fas fa-chevron-right breadcrumb-separator"></i>
        <span class="breadcrumb-link"><i class="fas fa-layer-group" style="color:var(--azul); margin-right:4px;"></i> Niveles</span>
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

      <!-- Encabezado de sección -->
      <div class="encabezado-seccion-admin">
        <div>
          <h1 class="titulo-seccion-admin">
            <i class="fas fa-layer-group icono-seccion-admin"></i>Gestión de Niveles
          </h1>
          <p class="desc-seccion-admin">
            4 módulos/niveles fijos alineados al MCER (A1 → B1+) · Solo se pueden editar.
          </p>
        </div>
      </div>

      <!-- Alertas flash -->
      <?php if ($exito): ?>
        <div class="alerta-flash alerta-exito" role="alert">
          <i class="fas fa-check-circle"></i>
          <?php
            echo match($exito) {
              'actualizado' => 'Nivel actualizado correctamente.',
              'estado'      => 'Estado del nivel actualizado.',
              default       => 'Operación completada.',
            };
          ?>
        </div>
      <?php endif; ?>
      <?php if ($error): ?>
        <div class="alerta-flash alerta-error" role="alert">
          <i class="fas fa-triangle-exclamation"></i> <?= $error ?>
        </div>
      <?php endif; ?>

      <!-- Resumen rápido -->
      <div class="resumen-rapido-admin">
        <?php
          $totalActivos   = count(array_filter($niveles, fn($n) => $n['activo'] == 1));
          $totalInactivos = count($niveles) - $totalActivos;
        ?>
        <div class="tarjeta glass-panel tarjeta-stat-admin">
          <div class="icono-stat-admin stat-azul"><i class="fas fa-layer-group"></i></div>
          <div>
            <div class="valor-stat-admin"><?= count($niveles) ?></div>
            <div class="label-stat-admin">Total Niveles</div>
          </div>
        </div>
        <div class="tarjeta glass-panel tarjeta-stat-admin">
          <div class="icono-stat-admin stat-verde"><i class="fas fa-circle-check"></i></div>
          <div>
            <div class="valor-stat-admin"><?= $totalActivos ?></div>
            <div class="label-stat-admin">Activos</div>
          </div>
        </div>
        <div class="tarjeta glass-panel tarjeta-stat-admin">
          <div class="icono-stat-admin stat-rojo"><i class="fas fa-ban"></i></div>
          <div>
            <div class="valor-stat-admin"><?= $totalInactivos ?></div>
            <div class="label-stat-admin">Inactivos</div>
          </div>
        </div>
      </div>

      <!-- Barra de filtros -->
      <div class="barra-filtros barra-filtros-admin">
        <div class="contenedor-input-search contenedor-search-admin" style="width: 480px !important; min-width: 360px !important; max-width: 500px !important;">
          <i class="fas fa-search icono-search"></i>
          <input type="text" id="buscar-nivel" class="input-busqueda" placeholder="Buscar nivel por nombre o descripción..." style="width: 100% !important; box-sizing: border-box !important;">
        </div>
        <select id="filtrar-estado" class="select-filtro select-filtro-admin">
          <option value="todos">Todos los estados</option>
          <option value="activos">Activos</option>
          <option value="inactivos">Inactivos</option>
        </select>
      </div>

      <!-- Grid de niveles -->
      <div class="grid-niveles">
        <?php
        $mcer = ['A1', 'A2', 'B1', 'B1+'];
        $iconosNivel = ['stethoscope','pill','hospital','clipboard'];

        foreach ($niveles as $n):
          $orden = (int)$n['orden'];
          $mcerLabel = $mcer[$orden - 1] ?? 'N/A';
          $icono     = $iconosNivel[$orden - 1] ?? 'book';
        ?>
        <div class="card-nivel <?= $n['activo'] ? '' : 'inactivo' ?>" 
             id="nivel-<?= limpiar($n['id']) ?>" 
             data-nombre="<?= limpiar(mb_strtolower($n['nombre'])) ?>"
             data-desc="<?= limpiar(mb_strtolower($n['descripcion'] ?? '')) ?>"
             data-activo="<?= $n['activo'] ? '1' : '0' ?>">
          <!-- Imagen / portada -->
          <div class="card-nivel-imagen">
            <?php if (!empty($n['imagen_url'])): ?>
              <img src="<?= limpiar($n['imagen_url']) ?>" alt="Portada Nivel <?= $orden ?>">
            <?php else: ?>
              <span><?= icono_svg($icono,'icono-portada') ?></span>
            <?php endif; ?>
          </div>

          <div class="card-nivel-body">
            <div class="card-nivel-orden">Nivel <?= $orden ?> · MCER <?= $mcerLabel ?></div>
            <h2 class="card-nivel-nombre"><?= limpiar($n['nombre']) ?></h2>
            <p class="card-nivel-desc"><?= limpiar($n['descripcion'] ?? 'Sin descripción configurada.') ?></p>

            <div class="card-nivel-meta">
              <span class="badge-mcer"><i class="fas fa-graduation-cap"></i> <?= $mcerLabel ?></span>
              <span class="badge-raps"><i class="fas fa-file-lines"></i> <?= (int)$n['total_raps'] ?> RAP<?= (int)$n['total_raps'] !== 1 ? 's' : '' ?></span>
              <?php if ($orden > 1): ?>
                <span class="badge-umbral"><i class="fas fa-lock"></i> ≥<?= number_format((float)$n['umbral_desbloqueo'], 0) ?>%</span>
              <?php else: ?>
                <span class="badge-umbral"><i class="fas fa-lock-open"></i> Libre</span>
              <?php endif; ?>
              <?php if ($n['activo']): ?>
                <span class="badge-activo"><i class="fas fa-circle icono-punto-activo"></i> Activo</span>
              <?php else: ?>
                <span class="badge-inactivo"><i class="fas fa-circle icono-punto-inactivo"></i> Inactivo</span>
              <?php endif; ?>
            </div>

            <div class="card-nivel-acciones">
              <button type="button" 
                 class="btn btn-verde btn-editar-nivel" 
                 id="btn-editar-nivel-<?= $orden ?>"
                 data-id="<?= htmlspecialchars($n['id'],ENT_QUOTES,'UTF-8') ?>"
                 data-orden="<?= $orden ?>"
                 data-nombre="<?= htmlspecialchars($n['nombre'],ENT_QUOTES,'UTF-8') ?>"
                 data-descripcion="<?= htmlspecialchars($n['descripcion'] ?? '',ENT_QUOTES,'UTF-8') ?>"
                 data-imagen="<?= htmlspecialchars($n['imagen_url'] ?? '',ENT_QUOTES,'UTF-8') ?>"
                 data-umbral="<?= number_format((float)$n['umbral_desbloqueo'], 2, '.', '') ?>"
                 data-activo="<?= $n['activo'] ? '1' : '0' ?>"
                 data-raps="<?= (int)$n['total_raps'] ?>"
                 onclick="abrirModalEditarNivel(this)">
                <i class="fas fa-edit"></i> Editar
              </button>
              <?php if ($n['rap_id']): ?>
                <a href="<?= PROYECTO_PATH ?>/aprendiz/rap?id=<?= urlencode($n['rap_id']) ?>" 
                   class="btn-azul btn-prever-nivel" 
                   id="btn-preview-nivel-<?= $orden ?>"
                   title="Previsualizar el RAP como aprendiz">
                  <i class="fas fa-eye-low-vision"></i> Prever
                </a>
              <?php endif; ?>
              <?php if ($orden > 1): ?>
                <button type="button" class="btn-accion <?= $n['activo'] ? 'btn-suspender' : 'btn-activar' ?> btn-toggle-nivel"
                        title="<?= $n['activo'] ? 'Desactivar' : 'Activar' ?>"
                        id="btn-toggle-nivel-<?= $orden ?>"
                        onclick="abrirModalToggleNivel('<?= $n['id'] ?>', <?= $n['activo'] ?>, '<?= $orden ?>', '<?= limpiar(addslashes($n['nombre'])) ?>')">
                  <i class="fas fa-<?= $n['activo'] ? 'eye-slash' : 'eye' ?>"></i>
                </button>
              <?php else: ?>
                <button type="button" class="btn-accion btn-toggle-nivel disabled-toggle" disabled 
                        title="El Nivel 1 siempre debe estar activo">
                  <i class="fas fa-eye"></i>
                </button>
              <?php endif; ?>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>

      <!-- Nota informativa -->
      <div class="nota-informativa-admin">
        <i class="fas fa-circle-info nota-informativa-icono"></i>
        <p class="nota-informativa-texto">
          <strong class="nota-informativa-titulo">Nota pedagógica (HU10):</strong>
          Los 6 niveles están precargados y alineados al Marco Común Europeo de Referencia (MCER).
          No es posible crear ni eliminar niveles. Solo se puede editar su nombre, descripción, imagen de portada,
          umbral de desbloqueo y estado activo/inactivo.
        </p>
      </div>

    </div><!-- /pagina-contenido -->

    <!-- Modal: Activar/Desactivar Nivel -->
    <div class="modal-fondo" id="modal-toggle-nivel">
      <div class="modal-caja">
        <p class="modal-titulo modal-titulo-premium" id="modal-level-title"></p>
        <p class="modal-desc modal-desc-premium" id="modal-level-desc"></p>
        <form method="POST" action="<?= PROYECTO_PATH ?>/admin/niveles/toggle">
          <input type="hidden" name="csrf_token" value="<?= generarTokenCSRF() ?>">
          <input type="hidden" name="id" id="toggle-nivel-id">
          <div class="modal-acciones modal-acciones-gap">
            <button type="button" class="btn btn-gris" onclick="cerrarModal('modal-toggle-nivel')">Cancelar</button>
            <button type="submit" class="btn" id="toggle-level-btn-confirm">Confirmar</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal: Editar Nivel -->
    <div class="modal-fondo" id="modal-editar-nivel" role="dialog" aria-modal="true" aria-labelledby="editar-nivel-titulo">
      <div class="modal-caja modal-caja-lg" style="max-width: 640px; text-align: left; padding: 28px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 2px solid var(--borde-sutil); padding-bottom: 14px;">
          <div>
            <h2 class="modal-titulo" id="editar-nivel-titulo" style="margin: 0; font-size: 1.3rem; font-weight: 800; color: var(--texto-principal); display: flex; align-items: center; gap: 8px;">
              <i class="fas fa-layer-group" style="color: var(--verde);"></i> Editar Nivel
            </h2>
            <p class="modal-desc" id="editar-nivel-subtitulo" style="margin: 4px 0 0 0; font-size: 0.85rem; color: var(--texto-secundario);">
              Modifica los atributos, umbrales y la portada del nivel MCER.
            </p>
          </div>
          <button type="button" class="btn-cerrar-modal-top" style="background: none; border: none; color: var(--texto-tenue); font-size: 1.5rem; cursor: pointer; padding: 4px 8px;" onclick="cerrarModal('modal-editar-nivel')" aria-label="Cerrar modal">&times;</button>
        </div>

        <div id="editar-nivel-error" class="alerta-flash alerta-error" style="display: none; margin-bottom: 16px;" role="alert">
          <i class="fas fa-triangle-exclamation"></i> <span id="editar-nivel-error-texto"></span>
        </div>

        <form id="form-modal-editar-nivel" method="POST" action="<?= PROYECTO_PATH ?>/admin/niveles/actualizar" novalidate onsubmit="return validarFormEditarNivel(this)">
          <input type="hidden" name="csrf_token" value="<?= generarTokenCSRF() ?>">
          <input type="hidden" name="id" id="editar-nivel-id">

          <!-- Nombre -->
          <div class="grupo-campo" style="margin-bottom: 16px;">
            <label for="editar-nombre-nivel" class="etiqueta-campo" style="font-size: 0.82rem; font-weight: 700; color: var(--texto-principal); margin-bottom: 6px; display: block;">Nombre del Nivel *</label>
            <div class="contenedor-input" style="position: relative;">
              <i class="fas fa-layer-group icono-input" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--texto-tenue);"></i>
              <input type="text" id="editar-nombre-nivel" name="nombre" class="campo-input" maxlength="255" required placeholder="Ej: Nivel 1 - A1 Básico" style="width: 100%; box-sizing: border-box; padding: 11px 14px 11px 40px; border-radius: 10px; border: 1px solid var(--borde-sutil); background: var(--fondo-input); color: var(--texto-principal);">
            </div>
            <p class="form-hint ayuda-campo" style="margin-top: 4px; font-size: 0.78rem; color: var(--texto-tenue);">Identificador visible para aprendices e instructores.</p>
          </div>

          <!-- Descripción -->
          <div class="grupo-campo" style="margin-bottom: 16px;">
            <label for="editar-descripcion-nivel" class="etiqueta-campo" style="font-size: 0.82rem; font-weight: 700; color: var(--texto-principal); margin-bottom: 6px; display: block;">Descripción</label>
            <div class="contenedor-input" style="position: relative;">
              <i class="fas fa-align-left icono-input" style="position: absolute; left: 14px; top: 14px; color: var(--texto-tenue);"></i>
              <textarea id="editar-descripcion-nivel" name="descripcion" class="campo-input" maxlength="500" placeholder="Describe el contenido y objetivos de este nivel..." style="width: 100%; box-sizing: border-box; resize: vertical; min-height: 80px; padding: 11px 14px 11px 40px; border-radius: 10px; border: 1px solid var(--borde-sutil); background: var(--fondo-input); color: var(--texto-principal);"></textarea>
            </div>
            <p class="form-hint ayuda-campo" style="margin-top: 4px; font-size: 0.78rem; color: var(--texto-tenue);">Máximo 500 caracteres. Visible en el mapa de niveles del aprendiz.</p>
          </div>

          <!-- URL de imagen -->
          <div class="grupo-campo" style="margin-bottom: 16px;">
            <label for="editar-imagen-url-nivel" class="etiqueta-campo" style="font-size: 0.82rem; font-weight: 700; color: var(--texto-principal); margin-bottom: 6px; display: block;">URL de imagen de portada</label>
            <div class="contenedor-input" style="position: relative;">
              <i class="fas fa-image icono-input" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--texto-tenue);"></i>
              <input type="url" id="editar-imagen-url-nivel" name="imagen_url" class="campo-input" placeholder="https://ejemplo.com/imagen.jpg" style="width: 100%; box-sizing: border-box; padding: 11px 14px 11px 40px; border-radius: 10px; border: 1px solid var(--borde-sutil); background: var(--fondo-input); color: var(--texto-principal);">
            </div>
            <div style="margin-top: 8px; display: flex; align-items: center; gap: 12px;">
              <div id="editar-preview-placeholder" style="width: 80px; height: 50px; border-radius: 8px; background: rgba(0,0,0,0.1); border: 1px dashed var(--borde-sutil); display: flex; align-items: center; justify-content: center; font-size: 0.75rem; color: var(--texto-tenue);">
                <i class="fas fa-image"></i>
              </div>
              <img id="editar-preview-img" src="" alt="Preview portada" style="display: none; width: 80px; height: 50px; object-fit: cover; border-radius: 8px; border: 1px solid var(--borde-sutil);">
              <span style="font-size: 0.78rem; color: var(--texto-tenue);">Previsualización de portada. Formatos: PNG, JPG, WebP.</span>
            </div>
          </div>

          <!-- Umbral + RAPs -->
          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
            <div class="grupo-campo" style="margin-bottom: 0;">
              <label for="editar-umbral-nivel" class="etiqueta-campo" style="font-size: 0.82rem; font-weight: 700; color: var(--texto-principal); margin-bottom: 6px; display: block;">Umbral de desbloqueo (%)</label>
              <div class="contenedor-input" style="position: relative;">
                <i class="fas fa-percentage icono-input" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--texto-tenue);"></i>
                <input type="number" id="editar-umbral-nivel" name="umbral_desbloqueo" class="campo-input" min="0" max="100" step="0.01" style="width: 100%; box-sizing: border-box; padding: 11px 14px 11px 40px; border-radius: 10px; border: 1px solid var(--borde-sutil); background: var(--fondo-input); color: var(--texto-principal);">
              </div>
              <p class="form-hint ayuda-campo" id="editar-umbral-hint" style="margin-top: 4px; font-size: 0.78rem; color: var(--texto-tenue);"></p>
            </div>

            <div class="grupo-campo" style="margin-bottom: 0;">
              <label class="etiqueta-campo" style="font-size: 0.82rem; font-weight: 700; color: var(--texto-principal); margin-bottom: 6px; display: block;">RAPs configurados</label>
              <div style="padding: 10px 14px; background: rgba(31,47,54,0.3); border: 1px solid var(--borde-sutil); border-radius: 10px; font-size: 0.85rem; color: var(--texto-secundario); display: flex; align-items: center; min-height: 44px; box-sizing: border-box;">
                <i class="fas fa-file-lines" style="color: #8B5CF6; margin-right: 8px;"></i>
                <span id="editar-raps-badge">0 RAPs asignados</span>
              </div>
              <p class="form-hint ayuda-campo" style="margin-top: 4px; font-size: 0.78rem; color: var(--texto-tenue);">Gestiona los RAPs desde el módulo de RAPs.</p>
            </div>
          </div>

          <!-- Toggle activo -->
          <div class="grupo-campo" style="margin-bottom: 20px;">
            <label class="etiqueta-campo" style="font-size: 0.82rem; font-weight: 700; color: var(--texto-principal); margin-bottom: 6px; display: block;">Estado del nivel</label>
            <div class="toggle-activo" style="display: flex; align-items: center; gap: 12px;">
              <label class="toggle-switch" for="editar-activo-nivel">
                <input type="checkbox" id="editar-activo-nivel" name="activo" value="1">
                <span class="slider"></span>
              </label>
              <div>
                <span class="toggle-label-principal" id="editar-estado-label-nivel" style="font-weight: 700; font-size: 0.9rem; color: var(--texto-principal);">Activo</span>
                <p class="toggle-label-desc" id="editar-activo-hint" style="margin: 2px 0 0 0; font-size: 0.78rem; color: var(--texto-tenue);"></p>
              </div>
            </div>
          </div>

          <!-- Botones del modal -->
          <div class="modal-acciones" style="gap: 12px;">
            <button type="button" class="btn btn-gris" onclick="cerrarModal('modal-editar-nivel')">Cancelar</button>
            <button type="submit" id="btn-guardar-nivel-modal" class="btn btn-verde">
              <i class="fas fa-floppy-disk"></i> Guardar cambios
            </button>
          </div>
        </form>
      </div>
    </div>

  </main>
</div>

<script src="<?= PROYECTO_PATH ?>/assets/js/tema.js"></script>
<script src="<?= PROYECTO_PATH ?>/assets/js/admin_cruds.js"></script>
<script>
  function abrirModalToggleNivel(id, activo, orden, nombre) {
    document.getElementById('toggle-nivel-id').value = id;
    const accion = activo == 1 ? 'Desactivar' : 'Activar';
    document.getElementById('modal-level-title').textContent = accion + ' Nivel ' + orden;
    document.getElementById('modal-level-desc').textContent = 
      '¿Estás seguro de que deseas ' + accion.toLowerCase() + ' el nivel «' + nombre + '»? ' +
      (activo == 1 ? 'Los aprendices no podrán visualizar este nivel ni realizar los ejercicios correspondientes.' : 'El nivel volverá a estar visible para todos los aprendices y habilitado para el aprendizaje.');
    
    const btnConfirm = document.getElementById('toggle-level-btn-confirm');
    if (activo == 1) {
      btnConfirm.className = "btn btn-gris";
      btnConfirm.style.background = "linear-gradient(135deg, var(--rojo), #DC2626)";
      btnConfirm.style.color = "#fff";
      btnConfirm.style.boxShadow = "0 4px 0 #DC2626";
      btnConfirm.textContent = 'Desactivar';
    } else {
      btnConfirm.className = "btn btn-verde";
      btnConfirm.style.background = "";
      btnConfirm.style.color = "";
      btnConfirm.style.boxShadow = "";
      btnConfirm.textContent = 'Activar';
    }
    document.getElementById('modal-toggle-nivel').classList.add('visible');
  }

  let botonActivadorEditarNivel = null;

  function abrirModalEditarNivel(btn) {
    botonActivadorEditarNivel = btn;
    const d = btn.dataset;
    const orden = parseInt(d.orden, 10);

    document.getElementById('editar-nivel-id').value = d.id;
    document.getElementById('editar-nivel-titulo').innerHTML = '<i class="fas fa-layer-group" style="color: var(--verde);"></i> Editar Nivel ' + orden;
    document.getElementById('editar-nivel-subtitulo').textContent = 'Nivel ' + orden + ' · Modifica los atributos, umbrales y la portada del nivel MCER.';

    const inputNombre = document.getElementById('editar-nombre-nivel');
    inputNombre.value = d.nombre;

    document.getElementById('editar-descripcion-nivel').value = d.descripcion;

    const inputImg = document.getElementById('editar-imagen-url-nivel');
    inputImg.value = d.imagen;
    actualizarPreviewModalNivel();

    const inputUmbral = document.getElementById('editar-umbral-nivel');
    const umbralHint = document.getElementById('editar-umbral-hint');
    inputUmbral.value = d.umbral;

    const toggleActivo = document.getElementById('editar-activo-nivel');
    const labelActivo = document.getElementById('editar-estado-label-nivel');
    const activoHint = document.getElementById('editar-activo-hint');
    toggleActivo.checked = (d.activo === '1');
    labelActivo.textContent = toggleActivo.checked ? 'Activo' : 'Inactivo';

    document.getElementById('editar-raps-badge').textContent = d.raps + ' RAP' + (d.raps !== '1' ? 's' : '') + ' asignado' + (d.raps !== '1' ? 's' : '');

    if (orden === 1) {
      inputUmbral.value = '0.00';
      inputUmbral.disabled = true;
      inputUmbral.title = 'El Nivel 1 siempre está disponible (0%)';
      umbralHint.textContent = 'El Nivel 1 siempre es accesible sin requisito previo (0%).';

      toggleActivo.checked = true;
      toggleActivo.disabled = true;
      toggleActivo.title = 'El Nivel 1 no puede desactivarse.';
      labelActivo.textContent = 'Activo';
      activoHint.textContent = 'El Nivel 1 siempre debe estar activo.';
    } else {
      inputUmbral.disabled = false;
      inputUmbral.removeAttribute('title');
      umbralHint.textContent = '% mínimo del nivel anterior para desbloquear este.';

      toggleActivo.disabled = false;
      toggleActivo.removeAttribute('title');
      activoHint.textContent = 'Un nivel inactivo no es visible para los aprendices.';
    }

    document.getElementById('editar-nivel-error').style.display = 'none';

    const btnSubmit = document.getElementById('btn-guardar-nivel-modal');
    if (btnSubmit) {
      btnSubmit.removeAttribute('disabled');
      btnSubmit.innerHTML = '<i class="fas fa-floppy-disk"></i> Guardar cambios';
    }

    const modal = document.getElementById('modal-editar-nivel');
    modal.classList.add('visible');
    inputNombre.focus();
  }

  function actualizarPreviewModalNivel() {
    const inputImg = document.getElementById('editar-imagen-url-nivel');
    const previewImg = document.getElementById('editar-preview-img');
    const placeholder = document.getElementById('editar-preview-placeholder');
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

  function validarFormEditarNivel(form) {
    const nombre = document.getElementById('editar-nombre-nivel').value.trim();
    const errorBox = document.getElementById('editar-nivel-error');
    const errorTexto = document.getElementById('editar-nivel-error-texto');

    if (!nombre) {
      errorTexto.textContent = 'El nombre del nivel es obligatorio.';
      errorBox.style.display = 'block';
      document.getElementById('editar-nombre-nivel').focus();
      return false;
    }

    const umbralInput = document.getElementById('editar-umbral-nivel');
    if (!umbralInput.disabled) {
      const umbral = parseFloat(umbralInput.value);
      if (isNaN(umbral) || umbral < 0 || umbral > 100) {
        errorTexto.textContent = 'El umbral de desbloqueo debe estar entre 0% y 100%.';
        errorBox.style.display = 'block';
        umbralInput.focus();
        return false;
      }
    }

    errorBox.style.display = 'none';
    const btnSubmit = document.getElementById('btn-guardar-nivel-modal');
    if (btnSubmit) {
      btnSubmit.setAttribute('disabled', 'true');
      btnSubmit.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';
    }
    return true;
  }

  // Client-side search and status filter for Levels
  document.addEventListener('DOMContentLoaded', () => {
    const filtrarSelect = document.getElementById('filtrar-estado');
    
    inicializarBusqueda('buscar-nivel', '.card-nivel', function(el) {
      const estado = filtrarSelect ? filtrarSelect.value : 'todos';
      const activo = el.getAttribute('data-activo');
      
      if (estado === 'activos') return activo === '1';
      if (estado === 'inactivos') return activo === '0';
      return true;
    });

    if (filtrarSelect) {
      filtrarSelect.addEventListener('change', () => {
        // Trigger input event to re-filter
        document.getElementById('buscar-nivel').dispatchEvent(new Event('input'));
      });
    }

    const inputImgNivel = document.getElementById('editar-imagen-url-nivel');
    if (inputImgNivel) {
      inputImgNivel.addEventListener('input', actualizarPreviewModalNivel);
    }

    const toggleActivoNivel = document.getElementById('editar-activo-nivel');
    if (toggleActivoNivel) {
      toggleActivoNivel.addEventListener('change', (e) => {
        document.getElementById('editar-estado-label-nivel').textContent = e.target.checked ? 'Activo' : 'Inactivo';
      });
    }
  });
</script>
</body>
</html>
