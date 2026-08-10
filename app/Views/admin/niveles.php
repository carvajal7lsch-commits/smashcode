<!DOCTYPE html>
<html lang="es" data-theme="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Gestión de Niveles — Admin SmashCode</title>
  <meta name="description" content="Panel de administración: gestiona los 6 niveles del programa de inglés médico SmashCode.">
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
        $iconosNivel = ['🩺','💊','🏥','📋'];

        foreach ($niveles as $n):
          $orden = (int)$n['orden'];
          $mcerLabel = $mcer[$orden - 1] ?? 'N/A';
          $icono     = $iconosNivel[$orden - 1] ?? '📚';
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
              <span><?= $icono ?></span>
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
              <a href="<?= PROYECTO_PATH ?>/admin/niveles/editar?id=<?= urlencode($n['id']) ?>" 
                 class="btn btn-verde btn-editar-nivel" 
                 id="btn-editar-nivel-<?= $orden ?>">
                <i class="fas fa-edit"></i> Editar
              </a>
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
  });
</script>
</body>
</html>
