<!DOCTYPE html>
<html lang="es" data-theme="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Programas de Formación — Admin SmashCode</title>
  <meta name="description" content="Gestión de programas de formación SENA en la plataforma SmashCode.">
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
        <span class="breadcrumb-link"><i class="fas fa-graduation-cap" style="color:var(--azul); margin-right:4px;"></i> Programas de Formación</span>
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

      <!-- Encabezado con acción -->
      <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px; margin-bottom:24px;">
        <div>
          <h1 class="pagina-titulo" style="margin-bottom:4px;">
            <i class="fas fa-graduation-cap" style="color:var(--verde-acento);"></i>
            Programas de Formación
          </h1>
          <p style="color:var(--texto-tenue); font-size:var(--texto-sm); margin:0;">
            Gestiona los programas del SENA. Los programas inactivos no permiten nuevas asignaciones.
          </p>
        </div>
        <a href="<?= PROYECTO_PATH ?>/admin/programas/crear" class="btn btn-verde" id="btn-nuevo-programa">
          <i class="fas fa-plus"></i> Nuevo Programa
        </a>
      </div>

      <!-- Alertas -->
      <?php if ($exito): ?>
        <div class="alerta alerta-exito" style="margin-bottom:16px;">
          <i class="fas fa-circle-check"></i>
          <?php
            $msgs = [
              'creado'      => 'Programa creado correctamente.',
              'actualizado' => 'Programa actualizado correctamente.',
              'estado'      => 'Estado del programa actualizado.',
              'eliminado'   => 'Programa eliminado correctamente.',
            ];
            echo $msgs[$exito] ?? 'Operación realizada.';
          ?>
        </div>
      <?php endif; ?>
      <?php if ($error): ?>
        <div class="alerta alerta-error" style="margin-bottom:16px;">
          <i class="fas fa-triangle-exclamation"></i> <?= $error ?>
        </div>
      <?php endif; ?>

      <!-- Barra de filtros -->
      <?php if (!empty($programas)): ?>
      <div class="barra-filtros" style="margin-bottom: 20px;">
        <div class="contenedor-input-search" style="max-width: 360px; margin: 0;">
          <i class="fas fa-search icono-search"></i>
          <input type="text" id="buscar-programa" class="input-busqueda" placeholder="Buscar programa por nombre o descripción...">
        </div>
      </div>
      <?php endif; ?>

      <!-- Tabla de programas -->
      <div class="tarjeta" style="padding:0; overflow:hidden;">
        <?php if (empty($programas)): ?>
          <div style="text-align:center; padding:60px 20px; color:var(--texto-tenue);">
            <i class="fas fa-graduation-cap" style="font-size:3rem; margin-bottom:16px; display:block; opacity:.3;"></i>
            <p style="font-size:var(--texto-sm);">No hay programas registrados aún.</p>
            <a href="<?= PROYECTO_PATH ?>/admin/programas/crear" class="btn btn-verde" style="margin-top:12px;">
              <i class="fas fa-plus"></i> Crear primer programa
            </a>
          </div>
        <?php else: ?>
          <table class="tabla-usuarios" style="width:100%;">
            <thead>
              <tr>
                <th style="width:30%;">Nombre</th>
                <th>Descripción</th>
                <th style="width:100px; text-align:center;">Usuarios</th>
                <th style="width:100px; text-align:center;">Estado</th>
                <th style="width:140px; text-align:center;">Acciones</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($programas as $p): ?>
              <tr id="fila-programa-<?= $p['id'] ?>" style="<?= !$p['activo'] ? 'opacity:.6;' : '' ?>">
                <td>
                  <div style="display:flex; align-items:center; gap:10px;">
                    <div style="width:36px; height:36px; border-radius:10px; background:linear-gradient(135deg,var(--verde-oscuro),var(--verde-acento)); display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                      <i class="fas fa-graduation-cap" style="color:#fff; font-size:.8rem;"></i>
                    </div>
                    <div>
                      <div style="font-weight:600; font-size:var(--texto-sm);"><?= limpiar($p['nombre']) ?></div>
                    </div>
                  </div>
                </td>
                <td style="color:var(--texto-tenue); font-size:var(--texto-xs);">
                  <?= $p['descripcion'] ? limpiar($p['descripcion']) : '<span style="opacity:.4;">Sin descripción</span>' ?>
                </td>
                <td style="text-align:center;">
                  <span style="background:rgba(28, 176, 246, 0.12); color:var(--azul); border:1px solid rgba(28, 176, 246, 0.25); padding:4px 10px; border-radius:20px; font-size:.72rem; font-weight:700;">
                    <?= (int)$p['total_usuarios'] ?>
                  </span>
                </td>
                <td style="text-align:center;">
                  <?php if ($p['activo']): ?>
                    <span class="badge-activo">Activo</span>
                  <?php else: ?>
                    <span class="badge-inactivo">Inactivo</span>
                  <?php endif; ?>
                </td>
                <td style="text-align:center;">
                  <div style="display:flex; gap:6px; justify-content:center;">
                    <!-- Editar -->
                    <a href="<?= PROYECTO_PATH ?>/admin/programas/editar?id=<?= $p['id'] ?>"
                       class="btn-accion btn-editar" title="Editar" id="btn-editar-programa-<?= substr($p['id'],0,8) ?>">
                      <i class="fas fa-pen"></i>
                    </a>
                    <!-- Toggle activo/inactivo -->
                    <button type="button" class="btn-accion <?= $p['activo'] ? 'btn-suspender' : 'btn-activar' ?>"
                            title="<?= $p['activo'] ? 'Desactivar' : 'Activar' ?>"
                            id="btn-toggle-programa-<?= substr($p['id'],0,8) ?>"
                            onclick="abrirModalToggle('<?= $p['id'] ?>', <?= $p['activo'] ?>, '<?= limpiar(addslashes($p['nombre'])) ?>')">
                      <i class="fas fa-<?= $p['activo'] ? 'ban' : 'check' ?>"></i>
                    </button>
                    <!-- Eliminar (solo si no tiene usuarios) -->
                    <?php if ((int)$p['total_usuarios'] === 0): ?>
                    <button type="button" class="btn-accion btn-eliminar" title="Eliminar"
                            id="btn-eliminar-programa-<?= substr($p['id'],0,8) ?>"
                            onclick="abrirModalEliminar('<?= $p['id'] ?>', '<?= limpiar(addslashes($p['nombre'])) ?>')">
                      <i class="fas fa-trash"></i>
                    </button>
                    <?php else: ?>
                    <button class="btn-accion btn-eliminar" disabled title="Tiene usuarios vinculados" style="opacity:.3; cursor:not-allowed;">
                      <i class="fas fa-trash"></i>
                    </button>
                    <?php endif; ?>
                  </div>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>
      </div>

      <!-- Nota informativa -->
      <div style="margin-top:16px; padding:12px 16px; background:rgba(88,204,2,.06); border:1px solid rgba(88,204,2,.15); border-radius:var(--radio-sm); font-size:var(--texto-xs); color:var(--texto-tenue); display:flex; gap:10px; align-items:flex-start;">
        <i class="fas fa-circle-info" style="color:var(--verde-acento); margin-top:2px; flex-shrink:0;"></i>
        <div>
          Los programas <strong>inactivos</strong> no aparecen al crear nuevos usuarios, pero los usuarios ya vinculados conservan su asignación.
          Un programa solo puede <strong>eliminarse</strong> si no tiene usuarios asociados.
        </div>
      </div>

    </div><!-- /pagina-contenido -->

    <!-- Modal: Activar/Desactivar Programa -->
    <div class="modal-fondo" id="modal-toggle-estado">
      <div class="modal-caja">
        <p class="modal-titulo" id="modal-toggle-titulo" style="font-size:1.3rem; font-weight:800; color:var(--texto-principal); margin-bottom:12px;"></p>
        <p class="modal-desc" id="modal-toggle-desc" style="font-size:0.875rem; color:var(--texto-secundario); line-height:1.6; margin-bottom:24px;"></p>
        <form method="POST" action="<?= PROYECTO_PATH ?>/admin/programas/toggle">
          <input type="hidden" name="csrf_token" value="<?= generarTokenCSRF() ?>">
          <input type="hidden" name="id" id="toggle-id">
          <div class="modal-acciones" style="gap:12px;">
            <button type="button" class="btn btn-gris" onclick="cerrarModal('modal-toggle-estado')">Cancelar</button>
            <button type="submit" class="btn" id="toggle-btn-confirm">Confirmar</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal: Eliminar Programa -->
    <div class="modal-fondo" id="modal-eliminar">
      <div class="modal-caja">
        <p class="modal-titulo" style="font-size:1.3rem; font-weight:800; color:var(--rojo); margin-bottom:12px;">⚠️ Confirmar Eliminación</p>
        <p class="modal-desc" id="modal-eliminar-desc" style="font-size:0.875rem; color:var(--texto-secundario); line-height:1.6; margin-bottom:24px;"></p>
        <form method="POST" action="<?= PROYECTO_PATH ?>/admin/programas/eliminar">
          <input type="hidden" name="csrf_token" value="<?= generarTokenCSRF() ?>">
          <input type="hidden" name="id" id="eliminar-id">
          <div class="modal-acciones" style="gap:12px;">
            <button type="button" class="btn btn-gris" onclick="cerrarModal('modal-eliminar')">Cancelar</button>
            <button type="submit" class="btn btn-verde" style="background:linear-gradient(135deg, var(--rojo), #DC2626); box-shadow: 0 4px 0 #DC2626;" id="eliminar-btn-confirm">Sí, Eliminar</button>
          </div>
        </form>
      </div>
    </div>

  </main>
</div>
<script src="<?= PROYECTO_PATH ?>/assets/js/tema.js"></script>
<script src="<?= PROYECTO_PATH ?>/assets/js/admin_cruds.js"></script>
<script>
  function abrirModalToggle(id, activo, nombre) {
    document.getElementById('toggle-id').value = id;
    const accion = activo == 1 ? 'Desactivar' : 'Activar';
    document.getElementById('modal-toggle-titulo').textContent = accion + ' Programa';
    document.getElementById('modal-toggle-desc').textContent = 
      '¿Estás seguro de que deseas ' + accion.toLowerCase() + ' el programa «' + nombre + '»? ' +
      (activo == 1 ? 'Los usuarios vinculados conservarán su asignación, pero no se podrán realizar nuevas asignaciones a este programa.' : 'El programa volverá a estar disponible para nuevas asignaciones.');
    
    const btnConfirm = document.getElementById('toggle-btn-confirm');
    if (activo == 1) {
      btnConfirm.className = "btn btn-gris";
      btnConfirm.style.background = "linear-gradient(135deg, var(--naranja), #E08400)";
      btnConfirm.style.color = "#fff";
      btnConfirm.style.boxShadow = "0 4px 0 #E08400";
      btnConfirm.textContent = 'Desactivar';
    } else {
      btnConfirm.className = "btn btn-verde";
      btnConfirm.style.background = "";
      btnConfirm.style.color = "";
      btnConfirm.style.boxShadow = "";
      btnConfirm.textContent = 'Activar';
    }
    document.getElementById('modal-toggle-estado').classList.add('visible');
  }

  function abrirModalEliminar(id, nombre) {
    document.getElementById('eliminar-id').value = id;
    document.getElementById('modal-eliminar-desc').textContent = 
      '¿Estás seguro de que deseas eliminar permanentemente el programa «' + nombre + '»? Esta acción no se puede deshacer.';
    document.getElementById('modal-eliminar').classList.add('visible');
  }

  // Inicializar búsqueda
  document.addEventListener('DOMContentLoaded', () => {
    inicializarBusqueda('buscar-programa', '.tabla-usuarios tbody tr');
  });
</script>
</body>
</html>
