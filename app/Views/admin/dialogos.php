<!DOCTYPE html>
<html lang="es" data-theme="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Diálogos — Admin SmashCode</title>
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
        <span class="breadcrumb-link"><i class="fas fa-comments" style="color:var(--verde); margin-right:4px;"></i> Diálogos</span>
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

      <div class="encabezado-seccion-admin" style="margin-bottom: 24px;">
        <div style="display: flex !important; align-items: center; gap: 14px;">
          <a href="<?= PROYECTO_PATH ?>/admin/raps" class="btn btn-gris" style="width:40px; height:40px; border-radius:50%; padding:0; display:inline-flex; align-items:center; justify-content:center; flex-shrink:0;" title="Volver a RAPs">
            <i class="fas fa-arrow-left" style="font-size:1rem;"></i>
          </a>
          <div>
            <h1 class="pagina-titulo" style="margin:0; font-size:1.6rem; font-weight:800; color:var(--texto-principal); letter-spacing:-0.5px; display:flex; align-items:center; gap:10px;">
              <i class="fas fa-comments" style="color:var(--verde); font-size:1.3rem;"></i>
              Diálogos: <?= limpiar($tituloRap) ?>
            </h1>
            <p class="desc-seccion-admin" style="margin:4px 0 0 0; font-size:0.88rem; color:var(--texto-secundario); font-weight:500;">
              Gestiona las conversaciones clínicas interactivas (Momento 2: Absorption - Storybook Dialogue).
            </p>
          </div>
        </div>
        <div>
          <button type="button" onclick="abrirModalDialogo()" class="btn btn-verde" style="padding:10px 18px; font-weight:700;">
            <i class="fas fa-plus"></i> Añadir Diálogo
          </button>
        </div>
      </div>

      <!-- Barra de filtros -->
      <?php if (!empty($dialogos)): ?>
      <div class="barra-filtros" style="margin-bottom: 20px;">
        <div class="contenedor-input-search" style="max-width: 360px; margin: 0;">
          <i class="fas fa-search icono-search"></i>
          <input type="text" id="buscar-dialogo" class="input-busqueda" placeholder="Buscar por título o personaje..." onkeyup="filtrarTabla()">
        </div>
      </div>
      <?php endif; ?>

      <div class="tarjeta" style="padding:0; overflow:hidden;">
        <?php if (empty($dialogos)): ?>
          <div class="empty-state-card">
            <div class="empty-state-icon">
              <i class="fas fa-comments"></i>
            </div>
            <h3>No hay diálogos configurados</h3>
            <p>Aún no has agregado ningún diálogo clínico a este RAP.</p>
            <button type="button" onclick="abrirModalDialogo()" class="btn btn-verde empty-state-btn">
              <i class="fas fa-plus"></i> Añadir primer diálogo
            </button>
          </div>
        <?php else: ?>
          <table class="tabla-usuarios w-100" id="tabla-dialogos">
            <thead>
              <tr>
                <th class="w-60px text-center">#</th>
                <th style="width: 250px;">Título del Diálogo</th>
                <th>Contexto / Participantes</th>
                <th class="w-100px text-center">Turnos</th>
                <th class="w-100px text-center">Acciones</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($dialogos as $idx => $d): ?>
              <tr>
                <td class="text-center font-weight-700" style="color:var(--azul);"><?= $idx + 1 ?></td>
                <td>
                  <div style="font-weight:700; color:var(--texto-principal);">
                    <?= limpiar($d['titulo']) ?>
                  </div>
                </td>
                <td>
                  <div style="font-size:0.85rem; color:var(--texto-secundario);">
                    <?= limpiar($d['contexto']) ?>
                  </div>
                  <?php if ($d['participantes']): ?>
                    <span class="badge-area" style="margin-top:4px; display:inline-block;">
                      <i class="fas fa-users" style="font-size:0.65rem;"></i> <?= limpiar($d['participantes']) ?>
                    </span>
                  <?php endif; ?>
                </td>
                <td class="text-center">
                  <span class="badge-categoria">
                    <?= count($d['turnos']) ?> turnos
                  </span>
                </td>
                <td class="text-center">
                  <div class="btn-icono-accion">
                    <button type="button" class="btn-accion btn-editar" title="Editar Diálogo" onclick="editarDialogo(<?= htmlspecialchars(json_encode($d), ENT_QUOTES) ?>)">
                      <i class="fas fa-pencil"></i>
                    </button>
                    <button type="button" class="btn-accion btn-suspender" title="Eliminar Diálogo" onclick="eliminarDialogo('<?= $d['id'] ?>')">
                      <i class="fas fa-trash"></i>
                    </button>
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
</div>

<!-- Modal Formulario Diálogo -->
<div class="modal-fondo" id="modal-dialogo">
  <div class="modal-caja modal-caja-lg" style="position: relative; padding: 24px; max-width:900px; width:95%;">
    <button type="button" class="btn-cerrar-modal-top" onclick="cerrarModal('modal-dialogo')" aria-label="Cerrar modal">
      <i class="fas fa-times"></i>
    </button>

    <p class="modal-titulo-premium" id="modal-titulo-dialogo" style="margin-bottom: 20px;">Nuevo Diálogo Clínico</p>
    
    <form id="form-dialogo" class="formulario-perfil" onsubmit="guardarDialogo(event)">
      <input type="hidden" id="dial-id" name="id" value="">
      <input type="hidden" name="rap_id" value="<?= $rap['id'] ?>">
      <input type="hidden" name="csrf_token" value="<?= generarTokenCSRF() ?>">

      <div class="grupo-input">
        <label class="label-input">Título del Diálogo <span class="text-rojo">*</span></label>
        <input type="text" class="input-base" id="dial-titulo" name="titulo" required placeholder="Ej: Storybook Dialogue: Patient Reception">
      </div>

      <div class="grid-vocab-form" style="grid-template-columns: 1fr 1fr; gap:16px;">
        <div class="grupo-input">
          <label class="label-input">Contexto / Escenario</label>
          <input type="text" class="input-base" id="dial-contexto" name="contexto" placeholder="Ej: Nurse receiving patient in room 204">
        </div>
        <div class="grupo-input">
          <label class="label-input">Participantes (Personajes)</label>
          <input type="text" class="input-base" id="dial-participantes" name="participantes" placeholder="Ej: Nurse, Patient">
        </div>
      </div>

      <!-- Turnos de Conversación (Chat Builder) -->
      <div class="tarjeta" style="background:var(--bg-app); border:1px solid var(--border-color); margin-top:20px; padding:16px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
          <label class="label-input" style="margin:0; font-size:0.95rem;">Turnos de Conversación (Chat Builder)</label>
          <button type="button" class="btn btn-verde" style="padding:4px 10px; font-size:0.75rem;" onclick="agregarTurno()">
            <i class="fas fa-plus"></i> Añadir Intervención
          </button>
        </div>
        
        <div id="contenedor-turnos" style="display:flex; flex-direction:column; gap:12px;">
          <!-- Turnos dinámicos -->
        </div>
      </div>

      <div class="modal-acciones" style="margin-top: 24px; display:flex; justify-content:flex-end; gap:12px;">
        <button type="button" class="btn btn-gris" onclick="cerrarModal('modal-dialogo')">Cancelar</button>
        <button type="submit" class="btn btn-verde" id="btn-guardar-dialogo">Guardar Diálogo</button>
      </div>
    </form>
  </div>
</div>

<style>
  .turno-row {
    display: flex;
    gap: 12px;
    align-items: flex-start;
    background: var(--bg-card);
    padding: 12px;
    border-radius: 8px;
    border: 1px solid var(--border-color);
  }
</style>

<script>
  let turnoIndex = 0;

  function cerrarModal(id) {
    document.getElementById(id).classList.remove('visible');
  }

  function abrirModalDialogo() {
    document.getElementById('form-dialogo').reset();
    document.getElementById('dial-id').value = '';
    document.getElementById('modal-titulo-dialogo').textContent = 'Nuevo Diálogo Clínico';
    document.getElementById('contenedor-turnos').innerHTML = '';
    turnoIndex = 0;
    
    // 2 turnos por defecto (Nurse & Patient)
    agregarTurno({hablante: 'Nurse', texto_en: '', texto_es: ''});
    agregarTurno({hablante: 'Patient', texto_en: '', texto_es: ''});
    
    document.getElementById('modal-dialogo').classList.add('visible');
  }

  function editarDialogo(d) {
    document.getElementById('form-dialogo').reset();
    document.getElementById('dial-id').value = d.id;
    document.getElementById('modal-titulo-dialogo').textContent = 'Editar Diálogo Clínico';
    document.getElementById('dial-titulo').value = d.titulo;
    document.getElementById('dial-contexto').value = d.contexto || '';
    document.getElementById('dial-participantes').value = d.participantes || '';
    
    document.getElementById('contenedor-turnos').innerHTML = '';
    turnoIndex = 0;
    
    if (d.turnos && d.turnos.length > 0) {
      d.turnos.forEach(t => agregarTurno(t));
    } else {
      agregarTurno();
    }
    
    document.getElementById('modal-dialogo').classList.add('visible');
  }

  function agregarTurno(data = null) {
    const container = document.getElementById('contenedor-turnos');
    const idx = turnoIndex++;
    
    const hablante = data ? data.hablante : 'Nurse';
    const textoEn = data ? data.texto_en : '';
    const textoEs = data ? data.texto_es : '';
    
    const html = `
      <div class="turno-row" id="turno-row-${idx}">
        <div style="width:140px; flex-shrink:0;">
          <label style="font-size:0.75rem; font-weight:700; color:var(--texto-secundario); margin-bottom:4px; display:block;">Hablante</label>
          <input type="text" class="input-base" name="turnos[${idx}][hablante]" value="${hablante.replace(/"/g, '&quot;')}" placeholder="Ej: Nurse" required>
        </div>
        <div style="flex:1;">
          <label style="font-size:0.75rem; font-weight:700; color:var(--texto-secundario); margin-bottom:4px; display:block;">Texto en Inglés (EN)</label>
          <input type="text" class="input-base" name="turnos[${idx}][texto_en]" value="${textoEn.replace(/"/g, '&quot;')}" placeholder="Good morning, what is your name?" required>
          <input type="text" class="input-base" style="margin-top:6px; font-size:0.8rem; background:rgba(0,0,0,0.05);" name="turnos[${idx}][texto_es]" value="${textoEs.replace(/"/g, '&quot;')}" placeholder="Traducción en Español (ES)">
        </div>
        <div style="margin-top:24px;">
          <button type="button" class="btn-accion btn-suspender" style="width:32px; height:32px;" onclick="document.getElementById('turno-row-${idx}').remove()">
            <i class="fas fa-trash" style="font-size:0.8rem;"></i>
          </button>
        </div>
      </div>
    `;
    container.insertAdjacentHTML('beforeend', html);
  }

  function guardarDialogo(e) {
    e.preventDefault();
    const formData = new FormData(e.target);
    const btn = document.getElementById('btn-guardar-dialogo');
    btn.disabled = true;
    btn.textContent = 'Guardando...';

    fetch('<?= PROYECTO_PATH ?>/admin/dialogos/save', {
      method: 'POST',
      body: formData
    })
    .then(r => r.json())
    .then(data => {
      if (data.success) {
        window.location.reload();
      } else {
        alert(data.error || 'Ocurrió un error al guardar');
        btn.disabled = false;
        btn.textContent = 'Guardar Diálogo';
      }
    })
    .catch(err => {
      console.error(err);
      alert('Error de conexión');
      btn.disabled = false;
      btn.textContent = 'Guardar Diálogo';
    });
  }

  function eliminarDialogo(id) {
    if (!confirm('¿Estás seguro de eliminar este diálogo?')) return;
    
    const formData = new FormData();
    formData.append('id', id);
    formData.append('csrf_token', '<?= generarTokenCSRF() ?>');

    fetch('<?= PROYECTO_PATH ?>/admin/dialogos/delete', {
      method: 'POST',
      body: formData
    })
    .then(r => r.json())
    .then(data => {
      if (data.success) window.location.reload();
      else alert(data.error || 'Error al eliminar');
    });
  }

  function filtrarTabla() {
    const input = document.getElementById('buscar-dialogo');
    const filter = input.value.toLowerCase();
    const table = document.getElementById('tabla-dialogos');
    if (!table) return;
    const tr = table.getElementsByTagName('tr');

    for (let i = 1; i < tr.length; i++) {
      const textContent = tr[i].textContent || tr[i].innerText;
      tr[i].style.display = textContent.toLowerCase().indexOf(filter) > -1 ? '' : 'none';
    }
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
