<?php
$areas      = $areas ?? [];
$categorias = $categorias ?? [];
$exito      = $_GET['exito'] ?? '';
$error      = $_GET['error'] ?? '';
?>
<!DOCTYPE html>
<html lang="es" data-theme="dark">
<head>
  <meta charset="UTF-8">
  <title>Catálogos — SmashCode</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="<?= PROYECTO_PATH ?>/assets/css/estilos.css?v=<?= time() ?>">
  <link rel="stylesheet" href="<?= PROYECTO_PATH ?>/assets/css/cruds.css?v=<?= time() ?>">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <script>(function(){var t=localStorage.getItem('smashcode_tema');if(t)document.documentElement.setAttribute('data-theme',t);})();</script>
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
        <span class="breadcrumb-link"><i class="fas fa-tags text-morado"></i> Catálogos</span>
      </div>
      <div class="admin-header-actions">
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
      <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px; margin-bottom:24px;">
        <div>
          <h1 class="pagina-titulo" style="margin-bottom:4px;">
            <i class="fas fa-tags text-morado"></i> Catálogos del Sistema
          </h1>
          <p style="color:var(--texto-tenue); font-size:var(--texto-sm); margin:0;">
            Administra las áreas clínicas y categorías gramaticales.
          </p>
        </div>
      </div>

      <?php if ($exito): ?>
        <div class="alerta alerta-exito alerta-margen">
          <i class="fas fa-circle-check"></i>
          <?php
            $msgs = [
              'creado'      => 'Elemento creado correctamente.',
              'actualizado' => 'Elemento actualizado correctamente.',
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

      <div class="grid-catalogos">
        <!-- Áreas Clínicas -->
        <div class="tarjeta" style="padding:0; overflow:hidden;">
          <div class="tarjeta-header" style="padding:16px 20px; border-bottom:1px solid var(--borde-sutil);">
            <h2 class="tarjeta-titulo" style="margin:0;">
              <i class="fas fa-hospital icono-seccion-admin"></i> Áreas Clínicas
            </h2>
            <button class="btn btn-verde btn-sm" onclick="abrirModalForm('area', '', '')">
              <i class="fas fa-plus"></i> Nueva Área
            </button>
          </div>
          
          <table class="tabla-usuarios w-100">
            <thead>
              <tr>
                <th>Nombre</th>
                <th class="w-100px text-center">Estado</th>
                <th class="w-100px text-center">Acciones</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($areas as $area): 
                $areaActiva = $area['activo'] ?? 1;
              ?>
              <tr class="<?= !$areaActiva ? 'disabled-toggle' : '' ?>">
                <td class="font-weight-600 text-sm"><?= limpiar($area['nombre']) ?></td>
                <td class="text-center">
                  <?php if ($areaActiva): ?>
                    <span class="badge-activo">Activa</span>
                  <?php else: ?>
                    <span class="badge-inactivo">Inactiva</span>
                  <?php endif; ?>
                </td>
                <td class="text-center">
                  <div class="btn-icono-accion">
                    <button class="btn-accion btn-editar" onclick="abrirModalForm('area', '<?= $area['id'] ?>', '<?= limpiar(addslashes($area['nombre'])) ?>')">
                      <i class="fas fa-pen"></i>
                    </button>
                    <?php if (isset($area['activo'])): ?>
                    <form method="POST" action="<?= PROYECTO_PATH ?>/admin/catalogos/toggle" class="d-inline">
                      <input type="hidden" name="csrf_token" value="<?= generarTokenCSRF() ?>">
                      <input type="hidden" name="tipo" value="area">
                      <input type="hidden" name="id" value="<?= $area['id'] ?>">
                      <button type="submit" class="btn-accion <?= $areaActiva ? 'btn-suspender' : 'btn-activar' ?>" title="<?= $areaActiva ? 'Desactivar' : 'Activar' ?>">
                        <i class="fas fa-<?= $areaActiva ? 'ban' : 'check' ?>"></i>
                      </button>
                    </form>
                    <?php endif; ?>
                  </div>
                </td>
              </tr>
              <?php endforeach; ?>
              <?php if (empty($areas)): ?>
                <tr><td colspan="3" style="text-align:center; padding:20px; color:var(--texto-tenue);">No hay áreas clínicas.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>

        <!-- Categorías Gramaticales -->
        <div class="tarjeta" style="padding:0; overflow:hidden;">
          <div class="tarjeta-header" style="padding:16px 20px; border-bottom:1px solid var(--borde-sutil);">
            <h2 class="tarjeta-titulo" style="margin:0;">
              <i class="fas fa-spell-check text-morado"></i> Categorías
            </h2>
            <button class="btn btn-verde btn-sm" onclick="abrirModalForm('categoria', '', '')">
              <i class="fas fa-plus"></i> Nueva Categoría
            </button>
          </div>
          
          <table class="tabla-usuarios w-100">
            <thead>
              <tr>
                <th>Nombre</th>
                <th class="w-100px text-center">Estado</th>
                <th class="w-100px text-center">Acciones</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($categorias as $cat): 
                $catActiva = $cat['activo'] ?? 1;
              ?>
              <tr class="<?= !$catActiva ? 'disabled-toggle' : '' ?>">
                <td class="font-weight-600 text-sm"><?= limpiar($cat['nombre']) ?></td>
                <td class="text-center">
                  <?php if ($catActiva): ?>
                    <span class="badge-activo">Activa</span>
                  <?php else: ?>
                    <span class="badge-inactivo">Inactiva</span>
                  <?php endif; ?>
                </td>
                <td class="text-center">
                  <div class="btn-icono-accion">
                    <button class="btn-accion btn-editar" onclick="abrirModalForm('categoria', '<?= $cat['id'] ?>', '<?= limpiar(addslashes($cat['nombre'])) ?>')">
                      <i class="fas fa-pen"></i>
                    </button>
                    <?php if (isset($cat['activo'])): ?>
                    <form method="POST" action="<?= PROYECTO_PATH ?>/admin/catalogos/toggle" class="d-inline">
                      <input type="hidden" name="csrf_token" value="<?= generarTokenCSRF() ?>">
                      <input type="hidden" name="tipo" value="categoria">
                      <input type="hidden" name="id" value="<?= $cat['id'] ?>">
                      <button type="submit" class="btn-accion <?= $catActiva ? 'btn-suspender' : 'btn-activar' ?>" title="<?= $catActiva ? 'Desactivar' : 'Activar' ?>">
                        <i class="fas fa-<?= $catActiva ? 'ban' : 'check' ?>"></i>
                      </button>
                    </form>
                    <?php endif; ?>
                  </div>
                </td>
              </tr>
              <?php endforeach; ?>
              <?php if (empty($categorias)): ?>
                <tr><td colspan="3" style="text-align:center; padding:20px; color:var(--texto-tenue);">No hay categorías gramaticales.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

    </div><!-- /pagina-contenido -->

    <!-- Modal: Formulario -->
    <div class="modal-fondo" id="modal-form">
      <div class="modal-caja">
        <p class="modal-titulo-premium" id="modal-form-titulo"></p>
        <form method="POST" action="<?= PROYECTO_PATH ?>/admin/catalogos/guardar">
          <input type="hidden" name="csrf_token" value="<?= generarTokenCSRF() ?>">
          <input type="hidden" name="tipo" id="form-tipo">
          <input type="hidden" name="id" id="form-id">
          
          <div class="grupo-input alerta-margen">
            <label class="label-input" for="form-nombre">Nombre <span class="text-rojo">*</span></label>
            <input type="text" name="nombre" id="form-nombre" class="input-base" required>
          </div>

          <div class="modal-acciones modal-acciones-gap">
            <button type="button" class="btn btn-gris" onclick="cerrarModal('modal-form')">Cancelar</button>
            <button type="submit" class="btn btn-verde">Guardar</button>
          </div>
        </form>
      </div>
    </div>

  </main>
</div>
<script src="<?= PROYECTO_PATH ?>/assets/js/tema.js"></script>
<script src="<?= PROYECTO_PATH ?>/assets/js/admin_cruds.js"></script>
<script>
  function abrirModalForm(tipo, id, nombre) {
    document.getElementById('form-tipo').value = tipo;
    document.getElementById('form-id').value = id;
    document.getElementById('form-nombre').value = nombre;
    
    const esArea = tipo === 'area';
    const accion = id ? 'Editar' : 'Nueva';
    const tipoLabel = esArea ? 'Área Clínica' : 'Categoría';
    
    document.getElementById('modal-form-titulo').textContent = `${accion} ${tipoLabel}`;
    document.getElementById('modal-form').classList.add('visible');
  }
</script>
</body>
</html>
