<!DOCTYPE html>
<html lang="es" data-theme="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Gestión de Usuarios — Admin SmashCode</title>
  <link rel="stylesheet" href="<?= PROYECTO_PATH ?>/assets/css/estilos.css?v=<?= time() ?>">
  <link rel="stylesheet" href="<?= PROYECTO_PATH ?>/assets/css/cruds.css?v=<?= time() ?>">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <script>/* Aplicar tema guardado antes del paint */
  (function(){var t=localStorage.getItem('smashcode_tema');if(t)document.documentElement.setAttribute('data-theme',t);})();
  </script>
    <!-- Estilos locales removidos: movidos a cruds.css -->
</head>
<body class="bg-mesh">
<div class="contenedor-app">

  <!-- Barra lateral -->
  <?php include __DIR__ . '/partials/sidebar.php'; ?>

  <!-- Contenido principal -->
  <main class="contenido-principal">
    <header class="barra-superior barra-superior-admin">
      <div class="breadcrumb-admin">
        <i class="fas fa-home breadcrumb-icon"></i>
        <a href="<?= PROYECTO_PATH ?>/admin" class="breadcrumb-current" style="text-decoration:none;">Dashboard</a>
        <i class="fas fa-chevron-right breadcrumb-separator"></i>
        <span class="breadcrumb-link"><i class="fas fa-users-gear" style="color:var(--azul); margin-right:4px;"></i> Gestión de Usuarios</span>
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
      <!-- Encabezado -->
      <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:28px; flex-wrap:wrap; gap:16px;">
        <div>
          <h1 class="pagina-titulo" style="font-size:1.8rem; font-weight:800; letter-spacing:-0.5px;">
            <i class="fas fa-users" style="color:var(--verde); margin-right:10px;"></i>Gestión de Usuarios
          </h1>
          <p class="pagina-subtitulo" style="margin-bottom:0; color:var(--texto-secundario);">
            Hay <strong style="color:var(--texto-principal);"><?= $total ?></strong> usuario(s) registrado(s) en la plataforma
          </p>
        </div>
        <div style="display:flex; gap:12px; flex-wrap:wrap;">
          <button type="button" class="btn-premium btn-premium-verde" onclick="abrirModalCrearUsuario()">
            <i class="fas fa-user-plus"></i> Nuevo Usuario
          </button>
        </div>
      </div>

      <!-- Alertas de éxito / error -->
      <?php $exito = $_GET['exito'] ?? ''; ?>
      <?php $errorMsg = $_GET['error'] ?? ''; ?>
      <?php if ($errorMsg): ?>
        <div class="alerta alerta-error" style="border-radius:12px; background:#451212; border:1px solid #7F1D1D; color:#FCA5A5; margin-bottom:20px; font-weight:600;"><i class="fas fa-triangle-exclamation"></i> <?= htmlspecialchars($errorMsg) ?></div>
      <?php endif; ?>
      <?php if ($exito === 'creado'): ?>
        <div class="alerta alerta-exito" style="border-radius:12px;"><i class="fas fa-circle-check"></i> Usuario creado correctamente.</div>
      <?php elseif ($exito === 'instructor_creado'): ?>
        <div class="alerta alerta-exito" style="border-radius:12px;"><i class="fas fa-chalkboard-teacher"></i> Cuenta de instructor creada. Se enviaron las credenciales temporales a su correo.</div>
      <?php elseif ($exito === 'actualizado'): ?>
        <div class="alerta alerta-exito" style="border-radius:12px;"><i class="fas fa-circle-check"></i> Usuario actualizado correctamente.</div>
      <?php elseif ($exito === 'estado'): ?>
        <div class="alerta alerta-exito" style="border-radius:12px;"><i class="fas fa-circle-check"></i> Estado del usuario actualizado.</div>
      <?php elseif ($exito === 'eliminado'): ?>
        <div class="alerta alerta-exito" style="border-radius:12px;"><i class="fas fa-circle-check"></i> Usuario eliminado del sistema (soft-delete).</div>
      <?php endif; ?>

      <!-- Tabla Premium Wrap (Con Toolbar de Filtros Integrado) -->
      <div class="tabla-premium-wrap" style="background:var(--blanco); border-radius:16px; border:1px solid var(--borde-sutil); box-shadow: var(--sombra-premium); overflow:hidden;">
        
        <!-- Toolbar de Filtros Integrado -->
        <div style="padding: 16px 20px; border-bottom: 1px solid var(--borde-sutil); background: var(--blanco);">
          <form method="GET" action="<?= PROYECTO_PATH ?>/admin/usuarios" style="display:flex; align-items:center; gap:12px; flex-wrap:wrap; width:100%; margin:0;">
            <!-- Campo de búsqueda -->
            <div style="position:relative; width: 100%; max-width: 320px; margin: 0;">
              <i class="fas fa-search" style="position:absolute; left:14px; top:50%; transform:translateY(-50%); color:var(--texto-tenue); font-size:0.9rem;"></i>
              <input type="text" name="busqueda" class="input-premium" 
                     style="width:100%; padding: 10px 14px 10px 38px; border:2px solid var(--borde-sutil); background:var(--blanco); color:var(--texto-principal); border-radius:12px; font-size:0.875rem;"
                     placeholder="Buscar por nombre o correo..."
                     value="<?= limpiar($busqueda) ?>">
            </div>
            
            <!-- Selector de Rol -->
            <div style="position:relative; min-width: 160px; margin:0;">
              <i class="fas fa-shield-halved" style="position:absolute; left:14px; top:50%; transform:translateY(-50%); color:var(--texto-tenue); font-size:0.9rem; pointer-events:none; z-index:5;"></i>
              <select name="rol" class="select-premium" onchange="this.form.submit()" style="width:100%; padding: 10px 14px 10px 38px; border:2px solid var(--borde-sutil); background:var(--blanco); color:var(--texto-principal); border-radius:12px; font-size:0.875rem; -webkit-appearance:none; -moz-appearance:none; appearance:none; background-image: url('data:image/svg+xml;charset=UTF-8,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%2212%22 height=%2212%22 viewBox=%220 0 24 24%22 fill=%22none%22 stroke=%22%239CA3AF%22 stroke-width=%223%22 stroke-linecap=%22round%22 stroke-linejoin=%22round%22%3E%3Cpolyline points=%226 9 12 15 18 9%22%3E%3C/polyline%3E%3C/svg%3E'); background-repeat: no-repeat; background-position: right 14px center; cursor:pointer;">
                <option value="">Todos los roles</option>
                <option value="aprendiz"   <?= $rol === 'aprendiz'   ? 'selected' : '' ?>>Aprendiz</option>
                <option value="instructor" <?= $rol === 'instructor' ? 'selected' : '' ?>>Instructor</option>
                <option value="admin"      <?= $rol === 'admin'      ? 'selected' : '' ?>>Administrador</option>
              </select>
            </div>
            
            <!-- Botones de Acción (Solo Limpiar si está activo) -->
            <?php if ($busqueda || $rol): ?>
              <div style="display:flex; gap:8px; margin:0;">
                <a href="<?= PROYECTO_PATH ?>/admin/usuarios" class="btn-premium btn-premium-blanco" style="padding: 10px 18px; font-weight:700; border-radius:12px; display:inline-flex; align-items:center; gap:6px; height:42px; border:2px solid var(--borde-sutil); background:var(--blanco); box-shadow: 0 4px 0 var(--borde-sutil); color:var(--texto-principal);">
                  <i class="fas fa-xmark"></i> Limpiar
                </a>
              </div>
            <?php endif; ?>

            <!-- Selector de Vista (Lista / Cuadrícula) -->
            <div class="toggle-vista-wrapper" style="border: 2px solid var(--borde-sutil); background: var(--fondo); border-radius: 12px; display: inline-flex; padding: 3px; gap: 3px; height: 42px; align-items: center; margin-left: auto;">
              <button type="button" id="btn-vista-lista" class="btn-toggle-vista activa" onclick="cambiarVista('list')" style="background: var(--blanco); border: 2px solid var(--borde-sutil); border-radius: 8px; color: var(--azul); font-size: 1rem; width: 36px; height: 32px; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s;" title="Vista de Lista">
                <i class="fas fa-list-ul"></i>
              </button>
              <button type="button" id="btn-vista-cuadricula" class="btn-toggle-vista" onclick="cambiarVista('grid')" style="background: transparent; border: 2px solid transparent; border-radius: 8px; color: var(--gris-medio); font-size: 1rem; width: 36px; height: 32px; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.2s;" title="Vista de Tarjetas (Cuadrícula)">
                <i class="fas fa-th-large"></i>
              </button>
            </div>
          </form>
        </div>
        <div id="vista-lista" class="vista-contenedor">
        <table class="tabla-premium">
          <thead>
            <tr>
              <th style="width: 32%;">Usuario</th>
              <th style="width: 14%;">Rol de Cuenta</th>
              <th style="width: 14%;">Ficha SENA</th>
              <th style="width: 10%;">Puntos XP</th>
              <th style="width: 12%;">Estado</th>
              <th style="width: 10%;">F. Registro</th>
              <th style="text-align:right; padding-right:24px; width: 8%;">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($lista)): ?>
              <tr>
                <td colspan="7" style="text-align:center; padding: 50px; color: var(--texto-tenue);">
                  <i class="fas fa-ghost" style="font-size:2.5rem; display:block; margin-bottom:12px; color:var(--gris-medio);"></i>
                  No se encontraron usuarios que coincidan con los filtros aplicados.
                </td>
              </tr>
            <?php else: ?>
              <?php foreach ($lista as $u): ?>
              <tr>
                <td>
                  <div style="display:flex; align-items:center; gap:12px;">
                    <div class="avatar-usuario" style="width:38px; height:38px; font-size:0.9rem; flex-shrink:0; border:none; margin:0; background:linear-gradient(135deg, #60A5FA, #2563EB); font-weight:800; color:#fff;">
                      <?= strtoupper(substr($u['nombre_completo'], 0, 1)) ?>
                    </div>
                    <div>
                      <span style="font-weight:700; color:var(--texto-principal); font-size:0.9rem; display:block;"><?= limpiar($u['nombre_completo']) ?></span>
                      <span style="color:var(--texto-tenue); font-family:monospace; font-size:0.75rem; display:block; margin-top:2px;"><?= limpiar($u['correo']) ?></span>
                    </div>
                  </div>
                </td>
                <td>
                  <span class="badge-rol badge-<?= $u['rol'] ?>" style="font-size: 0.68rem; font-weight:800; letter-spacing:0.04em;">
                    <?= ucfirst($u['rol']) ?>
                  </span>
                </td>
                <td style="font-weight: 600; color:var(--texto-secundario); font-size:0.85rem;">
                  <?php if ($u['rol'] === 'admin'): ?>
                    <span style="color:var(--texto-tenue); font-weight:400;">—</span>
                  <?php else: ?>
                    <?= $u['ficha_sena'] ? limpiar($u['ficha_sena']) : '<span style="color:var(--texto-tenue); font-weight:400;">—</span>' ?>
                  <?php endif; ?>
                </td>
                <td style="font-weight:800; color:#D97706; font-size:0.9rem;">
                  <?php if ($u['rol'] === 'aprendiz'): ?>
                    <?= number_format($u['xp_puntos']) ?>
                  <?php else: ?>
                    <span style="color:var(--texto-tenue); font-weight:400;">—</span>
                  <?php endif; ?>
                </td>
                <td>
                  <?php if ($u['bloqueado']): ?>
                    <span class="status-pill status-pill-bloqueado"><i class="fas fa-lock"></i> Bloqueado</span>
                  <?php elseif ($u['activo']): ?>
                    <span class="status-pill status-pill-activo"><i class="fas fa-check-circle"></i> Activo</span>
                  <?php else: ?>
                    <span class="status-pill status-pill-inactivo"><i class="fas fa-minus-circle"></i> Suspendido</span>
                  <?php endif; ?>
                </td>
                <td style="color:var(--texto-tenue); font-size:0.8rem; font-weight: 500;">
                  <?= date('d/m/Y', strtotime($u['creado_en'])) ?>
                </td>
                <td>
                  <div style="display:flex; gap:6px; justify-content:flex-end; padding-right:6px;">
                    <button type="button" 
                       class="btn-accion-premium btn-premium-edit" 
                       style="padding:0; border-radius:10px; width:34px; height:34px; display:inline-flex; align-items:center; justify-content:center;" 
                       title="Editar Datos"
                     onclick="abrirModalEditar('<?= $u['id'] ?>', '<?= limpiar(addslashes($u['nombre_completo'])) ?>', '<?= limpiar(addslashes($u['correo'])) ?>', '<?= limpiar($u['rol']) ?>', '<?= limpiar(addslashes($u['ficha_sena'] ?? '')) ?>', '<?= limpiar($u['programa_id'] ?? '') ?>')">
                      <i class="fas fa-pen" style="font-size:0.8rem;"></i>
                    </button>
                    
                    <button type="button" 
                       class="btn-accion-premium btn-premium-log" 
                       style="padding:0; border-radius:10px; width:34px; height:34px; display:inline-flex; align-items:center; justify-content:center;" 
                       title="Log de Actividad"
                       onclick="abrirModalLogs('<?= $u['id'] ?>', '<?= limpiar(addslashes($u['nombre_completo'])) ?>')">
                      <i class="fas fa-history" style="font-size:0.8rem;"></i>
                    </button>
                    
                    <button type="button"
                      class="btn-accion-premium <?= $u['activo'] ? 'btn-premium-susp' : 'btn-premium-react' ?>"
                      style="padding:0; border-radius:10px; width:34px; height:34px; display:inline-flex; align-items:center; justify-content:center;"
                      title="<?= $u['activo'] ? 'Suspender Acceso' : 'Reactivar Acceso' ?>"
                      onclick="abrirModalSuspender('<?= $u['id'] ?>', <?= $u['activo'] ?>, '<?= limpiar($u['nombre_completo']) ?>')">
                      <i class="fas <?= $u['activo'] ? 'fa-user-slash' : 'fa-user-check' ?>" style="font-size:0.8rem;"></i>
                    </button>
                    
                    <button type="button" class="btn-accion-premium btn-premium-del"
                      style="padding:0; border-radius:10px; width:34px; height:34px; display:inline-flex; align-items:center; justify-content:center;"
                      title="Eliminar (Soft-Delete)"
                      onclick="abrirModalEliminar('<?= $u['id'] ?>', '<?= limpiar($u['nombre_completo']) ?>')">
                      <i class="fas fa-trash" style="font-size:0.8rem;"></i>
                    </button>
                  </div>
                </td>
              </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
        </div> <!-- /#vista-lista -->
      </div> <!-- /.tabla-premium-wrap -->

      <!-- Vista de Cuadrícula (Cards) -->
      <div id="vista-cuadricula" class="vista-contenedor" style="display:none; margin-bottom: 24px; margin-top: 16px;">
        <?php if (empty($lista)): ?>
          <div class="tabla-premium-wrap" style="background:var(--blanco); border-radius:16px; border:2px solid #354952; padding:50px; text-align:center;">
            <i class="fas fa-ghost" style="font-size:2.5rem; display:block; margin-bottom:12px; color:var(--gris-medio);"></i>
            <span style="color:var(--texto-tenue);">No se encontraron usuarios con los filtros aplicados.</span>
          </div>
        <?php else: ?>
          <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 24px; padding: 24px;">
            <?php foreach ($lista as $u): ?>
              <div class="tarjeta-premium" style="background: var(--blanco) !important; border: 2px solid #354952 !important; border-radius: 16px; padding: 24px; display: flex; flex-direction: column; align-items: center; text-align: center; gap: 16px; transition: border-color 0.2s;">
                
                <!-- Avatar Circular Grande -->
                <div style="position: relative;">
                  <div class="avatar-usuario" style="width: 72px; height: 72px; font-size: 1.8rem; background: linear-gradient(135deg, #60A5FA, #2563EB); font-weight: 900; color: #fff; border: 3px solid #354952; margin: 0; box-shadow: 0 4px 10px rgba(0,0,0,0.15);">
                    <?= strtoupper(substr($u['nombre_completo'], 0, 1)) ?>
                  </div>
                  <!-- Indicador de Activo (Green dot) -->
                  <?php if ($u['activo'] && !$u['bloqueado']): ?>
                    <span style="position: absolute; right: 2px; bottom: 2px; width: 14px; height: 14px; background: #58CC02; border: 3px solid var(--blanco); border-radius: 50%; display: block;" title="Usuario Activo"></span>
                  <?php endif; ?>
                </div>

                <!-- Info de Identificación -->
                <div>
                  <span style="font-weight: 800; font-size: 1.05rem; color: #fff; display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 180px;" title="<?= limpiar($u['nombre_completo']) ?>">
                    <?= limpiar($u['nombre_completo']) ?>
                  </span>
                  <span style="color: var(--texto-tenue); font-family: monospace; font-size: 0.78rem; display: block; margin-top: 4px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 180px;" title="<?= limpiar($u['correo']) ?>">
                    <?= limpiar($u['correo']) ?>
                  </span>
                </div>

                <!-- Role Pill y Status Pill -->
                <div style="display: flex; flex-wrap: wrap; gap: 8px; justify-content: center; align-items: center;">
                  <span class="badge-rol badge-<?= $u['rol'] ?>" style="font-size: 0.65rem; font-weight: 800; letter-spacing: 0.04em;">
                    <?= ucfirst($u['rol']) ?>
                  </span>
                  <?php if ($u['bloqueado']): ?>
                    <span class="status-pill status-pill-bloqueado" style="font-size:0.65rem; padding: 3px 8px;"><i class="fas fa-lock" style="font-size:0.6rem;"></i> Bloqueado</span>
                  <?php elseif ($u['activo']): ?>
                    <span class="status-pill status-pill-activo" style="font-size:0.65rem; padding: 3px 8px;"><i class="fas fa-check-circle" style="font-size:0.6rem;"></i> Activo</span>
                  <?php else: ?>
                    <span class="status-pill status-pill-inactivo" style="font-size:0.65rem; padding: 3px 8px;"><i class="fas fa-minus-circle" style="font-size:0.6rem;"></i> Susp.</span>
                  <?php endif; ?>
                </div>

                <!-- Detalles Extras (Ficha / XP / Registro) -->
                <div style="width: 100%; border-top: 2px solid #2B3E46; padding-top: 14px; margin-top: 4px; display: grid; grid-template-columns: 1fr 1fr; gap: 8px; font-size: 0.8rem; text-align: left;">
                  <div>
                    <span style="color: var(--texto-tenue); display: block; font-size: 0.7rem; text-transform: uppercase; font-weight: 700; letter-spacing: 0.02em;">Ficha SENA</span>
                    <strong style="color: var(--texto-secundario); font-size: 0.82rem; font-weight: 700;">
                      <?php if ($u['rol'] === 'admin'): ?>
                        <span style="color:var(--texto-tenue); font-weight:400;">—</span>
                      <?php else: ?>
                        <?= $u['ficha_sena'] ? limpiar($u['ficha_sena']) : '<span style="color:var(--texto-tenue); font-weight:400;">—</span>' ?>
                      <?php endif; ?>
                    </strong>
                  </div>
                  <div>
                    <span style="color: var(--texto-tenue); display: block; font-size: 0.7rem; text-transform: uppercase; font-weight: 700; letter-spacing: 0.02em;">Puntos XP</span>
                    <strong style="color: #FF9600; font-size: 0.82rem; font-weight: 800; display: flex; align-items: center; gap: 4px;">
                      <?php if ($u['rol'] === 'aprendiz'): ?>
                        🔥 <?= number_format($u['xp_puntos']) ?>
                      <?php else: ?>
                        <span style="color:var(--texto-tenue); font-weight:400;">—</span>
                      <?php endif; ?>
                    </strong>
                  </div>
                  <div style="grid-column: span 2; margin-top: 4px;">
                    <span style="color: var(--texto-tenue); font-size: 0.7rem; font-weight: 500;">
                      Registrado el: <strong style="color: var(--texto-secundario); font-weight: 600;"><?= date('d/m/Y', strtotime($u['creado_en'])) ?></strong>
                    </span>
                  </div>
                </div>

                <!-- Botones de Acción Horizontal -->
                <div style="width: 100%; border-top: 2px solid #2B3E46; padding-top: 14px; display: flex; gap: 8px; justify-content: center; margin-top: auto;">
                  <button type="button" 
                     class="btn-accion-premium btn-premium-edit" 
                     style="padding:0; border-radius:10px; width:36px; height:36px; display:inline-flex; align-items:center; justify-content:center;" 
                     title="Editar Datos"
                     onclick="abrirModalEditar('<?= $u['id'] ?>', '<?= limpiar(addslashes($u['nombre_completo'])) ?>', '<?= limpiar(addslashes($u['correo'])) ?>', '<?= limpiar($u['rol']) ?>', '<?= limpiar(addslashes($u['ficha_sena'] ?? '')) ?>', '<?= limpiar($u['programa_id'] ?? '') ?>')">
                    <i class="fas fa-pen" style="font-size:0.8rem;"></i>
                  </button>
                  
                  <button type="button" 
                     class="btn-accion-premium btn-premium-log" 
                     style="padding:0; border-radius:10px; width:36px; height:36px; display:inline-flex; align-items:center; justify-content:center;" 
                     title="Log de Actividad"
                     onclick="abrirModalLogs('<?= $u['id'] ?>', '<?= limpiar(addslashes($u['nombre_completo'])) ?>')">
                    <i class="fas fa-history" style="font-size:0.8rem;"></i>
                  </button>
                  
                  <button type="button"
                     class="btn-accion-premium <?= $u['activo'] ? 'btn-premium-susp' : 'btn-premium-react' ?>"
                     style="padding:0; border-radius:10px; width:36px; height:36px; display:inline-flex; align-items:center; justify-content:center;"
                     title="<?= $u['activo'] ? 'Suspender Acceso' : 'Reactivar Acceso' ?>"
                     onclick="abrirModalSuspender('<?= $u['id'] ?>', <?= $u['activo'] ?>, '<?= limpiar($u['nombre_completo']) ?>')">
                    <i class="fas <?= $u['activo'] ? 'fa-user-slash' : 'fa-user-check' ?>" style="font-size:0.8rem;"></i>
                  </button>
                  
                  <button type="button" class="btn-accion-premium btn-premium-del"
                     style="padding:0; border-radius:10px; width:36px; height:36px; display:inline-flex; align-items:center; justify-content:center;"
                     title="Eliminar (Soft-Delete)"
                     onclick="abrirModalEliminar('<?= $u['id'] ?>', '<?= limpiar($u['nombre_completo']) ?>')">
                    <i class="fas fa-trash" style="font-size:0.8rem;"></i>
                  </button>
                </div>

              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>

      <!-- Paginación Premium -->
      <?php if ($paginas > 1): ?>
        <nav class="paginacion" aria-label="Paginación de usuarios">
          <?php if ($pagina > 1): ?>
            <a href="?busqueda=<?= urlencode($busqueda) ?>&rol=<?= $rol ?>&pagina=<?= $pagina - 1 ?>" class="pag-btn" style="border-radius: 8px;">
              <i class="fas fa-chevron-left"></i>
            </a>
          <?php endif; ?>
          <?php for ($p = 1; $p <= $paginas; $p++): ?>
            <a href="?busqueda=<?= urlencode($busqueda) ?>&rol=<?= $rol ?>&pagina=<?= $p ?>"
               class="pag-btn <?= $p === $pagina ? 'activa' : '' ?>" style="border-radius: 8px;">
              <?= $p ?>
            </a>
          <?php endfor; ?>
          <?php if ($pagina < $paginas): ?>
            <a href="?busqueda=<?= urlencode($busqueda) ?>&rol=<?= $rol ?>&pagina=<?= $pagina + 1 ?>" class="pag-btn" style="border-radius: 8px;">
              <i class="fas fa-chevron-right"></i>
            </a>
          <?php endif; ?>
        </nav>
      <?php endif; ?>
    </div><!-- /pagina-contenido -->
  </main>
</div>

<!-- Modal: Suspender/Reactivar -->
<div class="modal-fondo" id="modal-suspender">
  <div class="modal-caja-premium">
    <p class="modal-titulo" id="modal-suspender-titulo" style="font-size:1.3rem; font-weight:800; color:var(--texto-principal); margin-bottom:12px;"></p>
    <p class="modal-desc" id="modal-suspender-desc" style="font-size:0.875rem; color:var(--texto-secundario); line-height:1.6; margin-bottom:24px;"></p>
    <form method="POST" action="<?= PROYECTO_PATH ?>/admin/usuarios/suspender">
      <input type="hidden" name="csrf_token" value="<?= generarTokenCSRF() ?>">
      <input type="hidden" name="id" id="suspender-id">
      <input type="hidden" name="activo" id="suspender-activo">
      <div class="modal-acciones" style="gap:12px;">
        <button type="button" class="btn-premium btn-premium-blanco" onclick="cerrarModal('modal-suspender')">Cancelar</button>
        <button type="submit" class="btn-premium btn-premium-azul" id="suspender-btn-text">Confirmar</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal: Eliminar -->
<div class="modal-fondo" id="modal-eliminar">
  <div class="modal-caja-premium">
    <p class="modal-titulo" style="font-size:1.3rem; font-weight:800; color:#E11D48; margin-bottom:12px;">⚠️ Confirmar Eliminación</p>
    <p class="modal-desc" id="modal-eliminar-desc" style="font-size:0.875rem; color:var(--texto-secundario); line-height:1.6; margin-bottom:24px;"></p>
    <form method="POST" action="<?= PROYECTO_PATH ?>/admin/usuarios/eliminar">
      <input type="hidden" name="csrf_token" value="<?= generarTokenCSRF() ?>">
      <input type="hidden" name="id" id="eliminar-id">
      <div class="modal-acciones" style="gap:12px;">
        <button type="button" class="btn-premium btn-premium-blanco" onclick="cerrarModal('modal-eliminar')">Cancelar</button>
        <button type="submit" class="btn-premium btn-premium-verde" style="background:linear-gradient(135deg, #E11D48, #BE123C); box-shadow:0 4px 12px rgba(225, 29, 72, 0.2);"><i class="fas fa-trash-can"></i> Sí, Eliminar</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal: Crear Usuario (Unificado Inteligente) -->
<div class="modal-fondo" id="modal-crear-usuario">
  <div class="modal-caja-premium" style="max-width: 540px;">
    <p class="modal-titulo" style="font-size:1.3rem; font-weight:800; color:var(--texto-principal); margin-bottom:4px; display:flex; align-items:center; gap:8px;">
      <i class="fas fa-user-plus" style="color:var(--verde);"></i>
      <span id="crear-modal-titulo">Crear Nuevo Usuario</span>
    </p>
    <p class="modal-desc" id="crear-modal-desc" style="font-size:0.875rem; color:var(--texto-secundario); margin-bottom:20px;">Selecciona un rol para adaptar el formulario automáticamente.</p>

    <form method="POST" action="<?= PROYECTO_PATH ?>/admin/usuarios/guardar" novalidate id="form-crear-usuario">
      <input type="hidden" name="csrf_token" value="<?= generarTokenCSRF() ?>">

      <!-- Rol (primer campo: define qué se muestra) -->
      <div class="grupo-campo" style="margin-bottom:16px;">
        <label class="etiqueta-campo" for="crear_rol" style="font-size:0.78rem; font-weight:700; color:var(--texto-secundario); display:block; margin-bottom:6px;">Rol de Cuenta *</label>
        <div class="contenedor-input" style="margin:0; position:relative;">
          <i class="fas fa-shield-halved icono-input" style="position:absolute; left:14px; top:50%; transform:translateY(-50%); color:var(--texto-tenue); z-index:10;"></i>
          <select id="crear_rol" name="rol" class="select-premium" style="width:100%; padding-left:38px; cursor:pointer;" onchange="adaptarModalCrear(this.value)">
            <option value="aprendiz" selected>Aprendiz</option>
            <option value="instructor">Instructor</option>
            <option value="admin">Administrador</option>
          </select>
        </div>
      </div>

      <!-- Nombre completo -->
      <div class="grupo-campo" style="margin-bottom:16px;">
        <label class="etiqueta-campo" for="crear_nombre" style="font-size:0.78rem; font-weight:700; color:var(--texto-secundario); display:block; margin-bottom:6px;">Nombre Completo *</label>
        <div class="contenedor-input" style="margin:0; position:relative;">
          <i class="fas fa-user icono-input" style="position:absolute; left:14px; top:50%; transform:translateY(-50%); color:var(--texto-tenue);"></i>
          <input type="text" id="crear_nombre" name="nombre_completo" class="input-premium" style="width:100%; padding-left:38px;" placeholder="Nombre y apellido" required>
        </div>
      </div>

      <!-- Correo -->
      <div class="grupo-campo" style="margin-bottom:16px;">
        <label class="etiqueta-campo" for="crear_correo" style="font-size:0.78rem; font-weight:700; color:var(--texto-secundario); display:block; margin-bottom:6px;">Correo Electrónico *</label>
        <div class="contenedor-input" style="margin:0; position:relative;">
          <i class="fas fa-envelope icono-input" style="position:absolute; left:14px; top:50%; transform:translateY(-50%); color:var(--texto-tenue);"></i>
          <input type="email" id="crear_correo" name="correo" class="input-premium" style="width:100%; padding-left:38px;" placeholder="usuario@dominio.com" required>
        </div>
      </div>

      <!-- Contraseña (oculta para instructor y aprendiz) -->
      <div class="grupo-campo" id="crear-grupo-contrasena" style="margin-bottom:16px;">
        <label class="etiqueta-campo" for="crear_contrasena" style="font-size:0.78rem; font-weight:700; color:var(--texto-secundario); display:block; margin-bottom:6px;">Contraseña *</label>
        <div class="contenedor-input" style="margin:0; position:relative;">
          <i class="fas fa-lock icono-input" style="position:absolute; left:14px; top:50%; transform:translateY(-50%); color:var(--texto-tenue);"></i>
          <input type="password" id="crear_contrasena" name="contrasena" class="input-premium" style="width:100%; padding-left:38px;" placeholder="Mín. 8 caracteres, 1 mayúscula, 1 número">
        </div>
      </div>

      <!-- Aviso clave temporal (para instructor y aprendiz) -->
      <div id="crear-aviso-instructor" style="display:none; margin-bottom:16px; padding:12px 16px; background:rgba(28,176,246,0.08); border:1px solid rgba(28,176,246,0.25); border-radius:12px;">
        <p style="margin:0; font-size:0.82rem; color:var(--azul); font-weight:600; display:flex; align-items:center; gap:8px;">
          <i class="fas fa-paper-plane"></i>
          <span id="crear-texto-aviso">Se generará una contraseña temporal segura y se enviará automáticamente al correo del usuario.</span>
        </p>
      </div>

      <!-- Ficha SENA (oculta para admin) -->
      <div class="grupo-campo" id="crear-grupo-ficha" style="margin-bottom:16px;">
        <label class="etiqueta-campo" for="crear_ficha" style="font-size:0.78rem; font-weight:700; color:var(--texto-secundario); display:block; margin-bottom:6px;">Ficha SENA <span style="color:var(--texto-tenue); font-weight:400;">(Opcional)</span></label>
        <div class="contenedor-input" style="margin:0; position:relative;">
          <i class="fas fa-id-card icono-input" style="position:absolute; left:14px; top:50%; transform:translateY(-50%); color:var(--texto-tenue);"></i>
          <input type="text" id="crear_ficha" name="ficha_sena" class="input-premium" style="width:100%; padding-left:38px;" placeholder="Ej: 2877650" maxlength="20">
        </div>
      </div>

      <!-- Programa de Formación (oculto para admin) -->
      <div class="grupo-campo" id="crear-grupo-programa" style="margin-bottom:24px;">
        <label class="etiqueta-campo" for="crear_programa" style="font-size:0.78rem; font-weight:700; color:var(--texto-secundario); display:block; margin-bottom:6px;">Programa de Formación <span style="color:var(--texto-tenue); font-weight:400;">(Opcional)</span></label>
        <div class="contenedor-input" style="margin:0; position:relative;">
          <i class="fas fa-graduation-cap icono-input" style="position:absolute; left:14px; top:50%; transform:translateY(-50%); color:var(--texto-tenue); z-index:10;"></i>
          <select id="crear_programa" name="programa_id" class="select-premium" style="width:100%; padding:11px 14px 11px 38px; cursor:pointer; background:var(--fondo-input); color:var(--texto-principal); border:1px solid var(--borde-sutil); border-radius:10px;">
            <option value="">— Sin programa asignado —</option>
            <?php foreach ($programas as $p): ?>
              <option value="<?= limpiar($p['id']) ?>"><?= limpiar($p['nombre']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <div class="modal-acciones" style="gap:12px;">
        <button type="button" class="btn btn-gris" onclick="cerrarModal('modal-crear-usuario')">Cancelar</button>
        <button type="submit" class="btn btn-verde" id="crear-btn-submit"><i class="fas fa-user-plus"></i> Crear Cuenta</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal: Editar Usuario -->
<div class="modal-fondo" id="modal-editar-usuario">
  <div class="modal-caja-premium" style="max-width: 540px;">
    <p class="modal-titulo" style="font-size:1.3rem; font-weight:800; color:var(--texto-principal); margin-bottom:12px; display:flex; align-items:center; gap:8px;">
      <i class="fas fa-user-pen" style="color:var(--azul);"></i> Editar Datos del Usuario
    </p>
    <p class="modal-desc" style="font-size:0.875rem; color:var(--texto-secundario); margin-bottom:20px;">Modifica la información básica de la cuenta. El historial de interacciones se conservará.</p>

    <form method="POST" action="<?= PROYECTO_PATH ?>/admin/usuarios/actualizar" novalidate>
      <input type="hidden" name="csrf_token" value="<?= generarTokenCSRF() ?>">
      <input type="hidden" name="id" id="editar-id">

      <!-- Nombre completo -->
      <div class="grupo-campo" style="margin-bottom:16px;">
        <label class="etiqueta-campo" for="editar-nombre" style="font-size:0.78rem; font-weight:700; color:var(--texto-secundario); display:block; margin-bottom:6px;">Nombre Completo *</label>
        <div class="contenedor-input" style="margin:0; position:relative;">
          <i class="fas fa-user icono-input" style="position:absolute; left:14px; top:50%; transform:translateY(-50%); color:var(--texto-tenue);"></i>
          <input type="text" id="editar-nombre" name="nombre_completo" class="input-premium" style="width:100%; padding-left:38px;" placeholder="Nombre y apellido" required>
        </div>
      </div>

      <!-- Correo -->
      <div class="grupo-campo" style="margin-bottom:16px;">
        <label class="etiqueta-campo" for="editar-correo" style="font-size:0.78rem; font-weight:700; color:var(--texto-secundario); display:block; margin-bottom:6px;">Correo Electrónico *</label>
        <div class="contenedor-input" style="margin:0; position:relative;">
          <i class="fas fa-envelope icono-input" style="position:absolute; left:14px; top:50%; transform:translateY(-50%); color:var(--texto-tenue);"></i>
          <input type="email" id="editar-correo" name="correo" class="input-premium" style="width:100%; padding-left:38px;" placeholder="usuario@dominio.com" required>
        </div>
      </div>

      <!-- Rol -->
      <div class="grupo-campo" style="margin-bottom:16px;">
        <label class="etiqueta-campo" for="editar-rol" style="font-size:0.78rem; font-weight:700; color:var(--texto-secundario); display:block; margin-bottom:6px;">Rol de Cuenta *</label>
        <div class="contenedor-input" style="margin:0; position:relative;">
          <i class="fas fa-shield-halved icono-input" style="position:absolute; left:14px; top:50%; transform:translateY(-50%); color:var(--texto-tenue); z-index:10;"></i>
          <select id="editar-rol" name="rol" class="select-premium" style="width:100%; padding:11px 14px 11px 38px; cursor:pointer; background:var(--fondo-input); color:var(--texto-principal); border:1px solid var(--borde-sutil); border-radius:10px;" onchange="adaptarModalEditar(this.value)">
            <option value="aprendiz">Aprendiz</option>
            <option value="instructor">Instructor</option>
            <option value="admin">Administrador</option>
          </select>
        </div>
      </div>

      <!-- Ficha SENA -->
      <div class="grupo-campo" id="editar-grupo-ficha" style="margin-bottom:16px;">
        <label class="etiqueta-campo" for="editar-ficha" style="font-size:0.78rem; font-weight:700; color:var(--texto-secundario); display:block; margin-bottom:6px;">Ficha SENA <span style="color:var(--texto-tenue); font-weight:400;">(Opcional)</span></label>
        <div class="contenedor-input" style="margin:0; position:relative;">
          <i class="fas fa-id-card icono-input" style="position:absolute; left:14px; top:50%; transform:translateY(-50%); color:var(--texto-tenue);"></i>
          <input type="text" id="editar-ficha" name="ficha_sena" class="input-premium" style="width:100%; padding-left:38px;" placeholder="Ej: 2877650" maxlength="20">
        </div>
      </div>

      <!-- Programa de Formación -->
      <div class="grupo-campo" id="editar-grupo-programa" style="margin-bottom:24px;">
        <label class="etiqueta-campo" for="editar-programa" style="font-size:0.78rem; font-weight:700; color:var(--texto-secundario); display:block; margin-bottom:6px;">Programa de Formación <span style="color:var(--texto-tenue); font-weight:400;">(Opcional)</span></label>
        <div class="contenedor-input" style="margin:0; position:relative;">
          <i class="fas fa-graduation-cap icono-input" style="position:absolute; left:14px; top:50%; transform:translateY(-50%); color:var(--texto-tenue); z-index:10;"></i>
          <select id="editar-programa" name="programa_id" class="select-premium" style="width:100%; padding:11px 14px 11px 38px; cursor:pointer; background:var(--fondo-input); color:var(--texto-principal); border:1px solid var(--borde-sutil); border-radius:10px;">
            <option value="">— Sin programa asignado —</option>
            <?php foreach ($programas as $p): ?>
              <option value="<?= limpiar($p['id']) ?>"><?= limpiar($p['nombre']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <div class="modal-acciones" style="gap:12px;">
        <button type="button" class="btn-premium btn-premium-blanco" onclick="cerrarModal('modal-editar-usuario')">Cancelar</button>
        <button type="submit" class="btn-premium btn-premium-azul"><i class="fas fa-save"></i> Guardar Cambios</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal: Log de Actividad (AJAX) -->
<div class="modal-fondo" id="modal-logs-usuario">
  <div class="modal-caja-premium" style="max-width: 680px; width:95%; max-height:85vh; display:flex; flex-direction:column; padding:28px;">
    <!-- Cabecera -->
    <div style="flex-shrink: 0; margin-bottom:20px;">
      <p class="modal-titulo" style="font-size:1.35rem; font-weight:800; color:var(--texto-principal); margin-bottom:6px; display:flex; align-items:center; gap:8px;">
        <i class="fas fa-history" style="color:var(--naranja);"></i> Historial de Actividad
      </p>
      <p class="modal-desc" style="font-size:0.875rem; color:var(--texto-secundario);" id="logs-nombre-usuario">Historial de interacciones y progreso.</p>
    </div>
    
    <!-- Lista de Logs (Scrollable) -->
    <div id="logs-contenedor-lista" style="flex:1; overflow-y:auto; padding-right:8px; margin-bottom:20px; min-height:220px;">
      <!-- Cargador -->
      <div id="logs-cargando" style="display:flex; flex-direction:column; align-items:center; justify-content:center; gap:12px; height:180px; color:var(--texto-tenue);">
        <i class="fas fa-circle-notch fa-spin" style="font-size:2rem; color:var(--azul);"></i>
        <span style="font-weight:600; font-size:0.9rem;">Cargando historial de actividad...</span>
      </div>
      
      <!-- Timeline list -->
      <div id="logs-timeline" style="display:none; flex-direction:column; gap:14px; position:relative; padding-left:22px; border-left:2px solid #E5E7EB; margin-left:14px;">
        <!-- Items inyectados dinámicamente -->
      </div>
      
      <!-- Empty state -->
      <div id="logs-vacio" style="display:none; flex-direction:column; align-items:center; justify-content:center; gap:12px; height:180px; color:var(--texto-tenue); text-align:center;">
        <i class="fas fa-folder-open" style="font-size:2rem; color:var(--texto-tenue);"></i>
        <span style="font-weight:600; font-size:0.9rem;">No se encontraron registros de actividad para este usuario.</span>
      </div>
    </div>
    
    <!-- Pie -->
    <div style="flex-shrink: 0; display:flex; justify-content:flex-end; border-top: 1px solid var(--borde-sutil); padding-top:16px;">
      <button type="button" class="btn-premium btn-premium-blanco" onclick="cerrarModal('modal-logs-usuario')" style="padding:10px 24px;">Cerrar Historial</button>
    </div>
  </div>
</div>

<script>
  // ── MODAL CREAR: lógica adaptativa por rol ──
  function abrirModalCrearUsuario() {
    // Resetear formulario
    document.getElementById('form-crear-usuario').reset();
    adaptarModalCrear('aprendiz');
    document.getElementById('modal-crear-usuario').classList.add('visible');
  }

  function adaptarModalCrear(rol) {
    const grupoContrasena = document.getElementById('crear-grupo-contrasena');
    const avisoinst       = document.getElementById('crear-aviso-instructor');
    const grupoFicha      = document.getElementById('crear-grupo-ficha');
    const grupoPrograma   = document.getElementById('crear-grupo-programa');
    const btnSubmit       = document.getElementById('crear-btn-submit');
    const titulo          = document.getElementById('crear-modal-titulo');
    const desc            = document.getElementById('crear-modal-desc');
    const contrasena      = document.getElementById('crear_contrasena');

    if (rol === 'instructor' || rol === 'aprendiz') {
      grupoContrasena.style.display = 'none';
      avisoinst.style.display       = 'block';
      grupoFicha.style.display      = 'block';
      grupoPrograma.style.display   = 'block';
      contrasena.removeAttribute('required');
      btnSubmit.innerHTML  = '<i class="fas fa-paper-plane"></i> Crear y Enviar Credenciales';
      btnSubmit.className  = 'btn-premium btn-premium-azul';
      
      if (rol === 'instructor') {
        titulo.textContent = 'Crear Cuenta de Instructor';
        desc.textContent   = 'Se generará una contraseña temporal y se enviará automáticamente al correo del instructor.';
        document.getElementById('crear-texto-aviso').textContent = 'Se generará una contraseña temporal segura y se enviará automáticamente al correo del instructor.';
      } else {
        titulo.textContent = 'Crear Nuevo Aprendiz';
        desc.textContent   = 'Se generará una contraseña temporal y se enviará automáticamente al correo del aprendiz.';
        document.getElementById('crear-texto-aviso').textContent = 'Se generará una contraseña temporal segura y se enviará automáticamente al correo del aprendiz.';
      }
    } else if (rol === 'admin') {
      grupoContrasena.style.display = 'block';
      avisoinst.style.display       = 'none';
      grupoFicha.style.display      = 'none';
      grupoPrograma.style.display   = 'none';
      contrasena.setAttribute('required', 'required');
      btnSubmit.innerHTML  = '<i class="fas fa-user-shield"></i> Crear Admin';
      btnSubmit.className  = 'btn-premium btn-premium-verde';
      titulo.textContent   = 'Crear Administrador';
      desc.textContent     = 'Crea una cuenta con acceso total al panel de administración.';
    }
  }

  // ── MODAL EDITAR ──
  function abrirModalEditar(id, nombre, correo, rol, ficha, programaId) {
    document.getElementById('editar-id').value     = id;
    document.getElementById('editar-nombre').value = nombre;
    document.getElementById('editar-correo').value = correo;
    document.getElementById('editar-rol').value    = rol;
    document.getElementById('editar-ficha').value  = ficha;

    // Preseleccionar programa
    const selPrograma = document.getElementById('editar-programa');
    if (selPrograma) selPrograma.value = programaId || '';

    adaptarModalEditar(rol);
    document.getElementById('modal-editar-usuario').classList.add('visible');
  }

  function adaptarModalEditar(rol) {
    const grupoFicha    = document.getElementById('editar-grupo-ficha');
    const grupoPrograma = document.getElementById('editar-grupo-programa');
    if (grupoFicha)    grupoFicha.style.display    = (rol === 'admin') ? 'none' : 'block';
    if (grupoPrograma) grupoPrograma.style.display = (rol === 'admin') ? 'none' : 'block';
  }

  function abrirModalLogs(id, nombre) {
    document.getElementById('logs-nombre-usuario').innerHTML = 'Historial de interacciones de <strong style="color:var(--texto-principal);">' + nombre + '</strong>';
    
    const loading = document.getElementById('logs-cargando');
    const timeline = document.getElementById('logs-timeline');
    const empty = document.getElementById('logs-vacio');
    
    loading.style.display = 'flex';
    timeline.style.display = 'none';
    empty.style.display = 'none';
    timeline.innerHTML = '';
    
    document.getElementById('modal-logs-usuario').classList.add('visible');
    
    // Petición AJAX limpia
    fetch('<?= PROYECTO_PATH ?>/admin/usuarios/actividad?id=' + id + '&ajax=1')
      .then(r => r.json())
      .then(data => {
        loading.style.display = 'none';
        
        if (data.error || !data.logs || data.logs.length === 0) {
          empty.style.display = 'flex';
          return;
        }
        
        data.logs.forEach(log => {
          const item = document.createElement('div');
          item.style.position = 'relative';
          item.style.marginBottom = '4px';
          
          const isSuccess = log.estado === 'aprobado' || log.estado === 'correcto';
          const color = isSuccess ? '#10B981' : '#EF4444';
          const bg = isSuccess ? '#ECFDF5' : '#FEF2F2';
          const icon = log.tipo === 'quiz' ? 'fa-puzzle-piece' : 'fa-code';
          
          // Bullet en el timeline
          const bullet = document.createElement('div');
          bullet.style.position = 'absolute';
          bullet.style.left = '-28px';
          bullet.style.top = '16px';
          bullet.style.width = '10px';
          bullet.style.height = '10px';
          bullet.style.borderRadius = '50%';
          bullet.style.background = color;
          bullet.style.border = '2px solid #fff';
          bullet.style.boxShadow = '0 0 0 2px ' + color;
          item.appendChild(bullet);
          
          // Caja contenedora
          const container = document.createElement('div');
          container.style.background = '#F8FAFC';
          container.style.padding = '12px 16px';
          container.style.borderRadius = '12px';
          container.style.border = '1px solid #E2E8F0';
          container.style.display = 'flex';
          container.style.alignItems = 'center';
          container.style.justifyContent = 'space-between';
          container.style.gap = '16px';
          container.style.flexWrap = 'wrap';
          
          const info = document.createElement('div');
          info.innerHTML = `
            <div style="font-weight:700; font-size:0.875rem; color:var(--texto-principal); display:flex; align-items:center; gap:8px; margin-bottom:4px;">
              <i class="fas ${icon}" style="color:${color};"></i> ${log.descripcion}
            </div>
            <div style="font-size:0.75rem; color:var(--texto-tenue);">
              <i class="far fa-clock"></i> ${log.fecha}
            </div>
          `;
          container.appendChild(info);
          
          const badge = document.createElement('span');
          badge.style.padding = '4px 10px';
          badge.style.borderRadius = '8px';
          badge.style.fontSize = '0.75rem';
          badge.style.fontWeight = '700';
          badge.style.textTransform = 'uppercase';
          badge.style.letterSpacing = '0.04em';
          badge.style.background = bg;
          badge.style.color = color;
          badge.textContent = log.detalle + ' • ' + log.estado;
          container.appendChild(badge);
          
          item.appendChild(container);
          timeline.appendChild(item);
        });
        
        timeline.style.display = 'flex';
      })
      .catch(err => {
        loading.style.display = 'none';
        empty.style.display = 'flex';
        empty.querySelector('span').textContent = 'Error al cargar el historial.';
      });
  }

  function abrirModalSuspender(id, activo, nombre) {
    document.getElementById('suspender-id').value     = id;
    document.getElementById('suspender-activo').value = activo;
    const accion = activo == 1 ? 'Suspender' : 'Reactivar';
    document.getElementById('modal-suspender-titulo').textContent = accion + ' Cuenta de Usuario';
    document.getElementById('modal-suspender-desc').textContent   =
      '¿Estás seguro de que deseas ' + accion.toLowerCase() + ' la cuenta de "' + nombre + '"? ' +
      (activo == 1 ? 'El usuario perderá el acceso a la plataforma inmediatamente de forma temporal.' : 'El usuario recuperará el acceso completo a la plataforma al instante.');
    document.getElementById('suspender-btn-text').textContent = accion;
    
    // Si es suspender, poner botón rojo
    const btnConfirmar = document.getElementById('suspender-btn-text');
    if (activo == 1) {
      btnConfirmar.className = "btn-premium";
      btnConfirmar.style.background = "linear-gradient(135deg, #DC2626, #B91C1C)";
      btnConfirmar.style.color = "#fff";
      btnConfirmar.style.boxShadow = "0 4px 12px rgba(220, 38, 38, 0.2)";
    } else {
      btnConfirmar.className = "btn-premium btn-premium-azul";
      btnConfirmar.style.background = "";
      btnConfirmar.style.color = "";
      btnConfirmar.style.boxShadow = "";
    }
    
    document.getElementById('modal-suspender').classList.add('visible');
  }

  function abrirModalEliminar(id, nombre) {
    document.getElementById('eliminar-id').value      = id;
    document.getElementById('modal-eliminar-desc').textContent =
      'Estás a punto de eliminar al usuario "' + nombre + '". Su cuenta se marcará como inactiva (Soft-Delete) y no aparecerá en el listado, pero todo su progreso académico, XP acumulado y logs históricos se preservarán para auditoría.';
    document.getElementById('modal-eliminar').classList.add('visible');
  }

  // Funciones cerrarModal y eventos modal-fondo se manejan en admin_cruds.js

  // Sistema de Selección de Vista (Lista / Cuadrícula)
  function cambiarVista(modo) {
    const vistaLista = document.getElementById('vista-lista');
    const vistaCuadricula = document.getElementById('vista-cuadricula');
    const btnLista = document.getElementById('btn-vista-lista');
    const btnCuadricula = document.getElementById('btn-vista-cuadricula');
    
    if (!vistaLista || !vistaCuadricula || !btnLista || !btnCuadricula) return;

    if (modo === 'grid') {
      vistaLista.style.display = 'none';
      vistaCuadricula.style.display = 'block';
      
      // Actualizar botones del toggle
      btnLista.classList.remove('activa');
      btnLista.style.background = 'transparent';
      btnLista.style.borderColor = 'transparent';
      btnLista.style.color = '#84929C';
      
      btnCuadricula.classList.add('activa');
      btnCuadricula.style.background = '#1F2F36';
      btnCuadricula.style.borderColor = '#354952';
      btnCuadricula.style.color = '#1CB0F6';
    } else {
      vistaLista.style.display = 'block';
      vistaCuadricula.style.display = 'none';
      
      // Actualizar botones del toggle
      btnLista.classList.add('activa');
      btnLista.style.background = '#1F2F36';
      btnLista.style.borderColor = '#354952';
      btnLista.style.color = '#1CB0F6';
      
      btnCuadricula.classList.remove('activa');
      btnCuadricula.style.background = 'transparent';
      btnCuadricula.style.borderColor = 'transparent';
      btnCuadricula.style.color = '#84929C';
    }
    
    // Guardar preferencia del usuario en localStorage
    localStorage.setItem('smashcode-view-mode', modo);
  }

  // Inicializar vista guardada al cargar la página e implementar búsqueda en tiempo real debounced
  document.addEventListener('DOMContentLoaded', () => {
    const vistaGuardada = localStorage.getItem('smashcode-view-mode') || 'list';
    cambiarVista(vistaGuardada);

    // Búsqueda en tiempo real debounced (500ms) sin perder el foco
    const searchInput = document.querySelector('input[name="busqueda"]');
    if (searchInput) {
      let debounceTimeout = null;
      
      searchInput.addEventListener('input', (e) => {
        clearTimeout(debounceTimeout);
        debounceTimeout = setTimeout(() => {
          // Guardar el estado de foco y selección del cursor antes de enviar
          localStorage.setItem('smashcode-search-focus', 'true');
          localStorage.setItem('smashcode-search-selection', e.target.selectionStart);
          searchInput.form.submit();
        }, 500);
      });

      // Restaurar foco y posición exacta del cursor tras la recarga por búsqueda
      const shouldFocus = localStorage.getItem('smashcode-search-focus');
      if (shouldFocus === 'true') {
        localStorage.removeItem('smashcode-search-focus');
        const savedPos = localStorage.getItem('smashcode-search-selection') || searchInput.value.length;
        localStorage.removeItem('smashcode-search-selection');
        
        searchInput.focus();
        // Colocar cursor en la última posición registrada
        searchInput.setSelectionRange(savedPos, savedPos);
      }
    }
  });
</script>
<script src="<?= PROYECTO_PATH ?>/assets/js/tema.js"></script>
<script src="<?= PROYECTO_PATH ?>/assets/js/admin_cruds.js"></script>
</body>
</html>
