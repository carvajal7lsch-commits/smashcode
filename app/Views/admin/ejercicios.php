<!DOCTYPE html>
<html lang="es" data-theme="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Ejercicios — Admin SmashCode</title>
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
        <span class="breadcrumb-link"><i class="fas fa-dumbbell" style="color:var(--verde); margin-right:4px;"></i> Ejercicios</span>
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
              <i class="fas fa-tasks" style="color:var(--verde); font-size:1.3rem;"></i>
              Ejercicios: <?= limpiar($tituloRap) ?>
            </h1>
            <p class="desc-seccion-admin" style="margin:4px 0 0 0; font-size:0.88rem; color:var(--texto-secundario); font-weight:500;">
              Gestiona las actividades interactivas (Momento 1: Warm-up & Momento 3: Practice).
            </p>
          </div>
        </div>
        <div>
          <button type="button" onclick="abrirModalEjercicio()" class="btn btn-verde" style="padding:10px 18px; font-weight:700;">
            <i class="fas fa-plus"></i> Añadir Ejercicio
          </button>
        </div>
      </div>

      <!-- Barra de filtros -->
      <?php if (!empty($ejercicios)): ?>
      <div class="barra-filtros" style="margin-bottom: 20px;">
        <div class="contenedor-input-search" style="max-width: 360px; margin: 0;">
          <i class="fas fa-search icono-search"></i>
          <input type="text" id="buscar-ejercicio" class="input-busqueda" placeholder="Buscar por enunciado o tipo..." onkeyup="filtrarTabla()">
        </div>
      </div>
      <?php endif; ?>

      <div class="tarjeta" style="padding:0; overflow:hidden;">
        <?php if (empty($ejercicios)): ?>
          <div class="empty-state-card">
            <div class="empty-state-icon">
              <i class="fas fa-dumbbell"></i>
            </div>
            <h3>No hay ejercicios configurados</h3>
            <p>Aún no has agregado ningún ejercicio interactivo a este RAP.</p>
            <button type="button" onclick="abrirModalEjercicio()" class="btn btn-verde empty-state-btn">
              <i class="fas fa-plus"></i> Añadir primer ejercicio
            </button>
          </div>
        <?php else: ?>
          <table class="tabla-usuarios w-100" id="tabla-ejercicios">
            <thead>
              <tr>
                <th class="w-60px text-center">#</th>
                <th style="width: 200px;">Tipo de Ejercicio</th>
                <th>Enunciado / Instrucción</th>
                <th class="w-100px text-center">Opciones</th>
                <th class="w-100px text-center">Acciones</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($ejercicios as $idx => $ej): ?>
              <tr>
                <td class="text-center font-weight-700" style="color:var(--azul);"><?= $idx + 1 ?></td>
                <td>
                  <span class="badge-categoria" style="text-transform: uppercase;">
                    <?= limpiar(str_replace('_', ' ', $ej['tipo'])) ?>
                  </span>
                </td>
                <td>
                  <div style="font-weight:600; color:var(--texto-principal);">
                    <?= limpiar($ej['enunciado']) ?>
                  </div>
                </td>
                <td class="text-center">
                  <span class="badge-area">
                    <?= count($ej['opciones']) ?> opt.
                  </span>
                </td>
                <td class="text-center">
                  <div class="btn-icono-accion">
                    <button type="button" class="btn-accion btn-editar" title="Editar Ejercicio" onclick="editarEjercicio(<?= htmlspecialchars(json_encode($ej), ENT_QUOTES) ?>)">
                      <i class="fas fa-pencil"></i>
                    </button>
                    <button type="button" class="btn-accion btn-suspender" title="Eliminar Ejercicio" onclick="eliminarEjercicio('<?= $ej['id'] ?>')">
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

<!-- Modal Ejercicio -->
<div class="modal-fondo" id="modal-ejercicio">
  <div class="modal-caja modal-caja-lg" style="position: relative; padding: 24px;">
    <button type="button" class="btn-cerrar-modal-top" onclick="cerrarModal('modal-ejercicio')" aria-label="Cerrar modal">
      <i class="fas fa-times"></i>
    </button>

    <p class="modal-titulo-premium" id="modal-titulo-ejercicio" style="margin-bottom: 20px;">Nuevo Ejercicio</p>
    
    <form id="form-ejercicio" class="formulario-perfil" onsubmit="guardarEjercicio(event)">
      <input type="hidden" id="ej-id" name="id" value="">
      <input type="hidden" name="rap_id" value="<?= $rap['id'] ?>">
      <input type="hidden" name="csrf_token" value="<?= generarTokenCSRF() ?>">

      <div class="grupo-input">
        <label class="label-input">Tipo de Ejercicio <span class="text-rojo">*</span></label>
        <select class="input-base" id="ej-tipo" name="tipo" required onchange="cambiarTipoEjercicio()">
          <option value="completar_frase">Completar Frase (Fill in blanks)</option>
          <option value="arrastrar_soltar">Arrastrar y Soltar (Matching)</option>
          <option value="escucha_escribe">Escucha y Escribe (Dictation)</option>
          <option value="role_play">Role Play (Diálogo)</option>
          <option value="seleccion_multiple">Selección Múltiple (Quiz)</option>
        </select>
      </div>

      <div class="grupo-input">
        <label class="label-input">Enunciado / Instrucción del Ejercicio <span class="text-rojo">*</span></label>
        <textarea class="input-base" id="ej-enunciado" name="enunciado" rows="2" required placeholder="Ej: Complete the sentence: I ___ a nurse."></textarea>
        <small class="form-hint" id="helper-enunciado" style="color:var(--verde); font-weight:600; margin-top:4px; display:block;">
          Para "Completar Frase", usa "___" (tres guiones bajos) donde irá el espacio en blanco.
        </small>
      </div>

      <!-- Contenedor dinámico de opciones -->
      <div class="tarjeta" style="background:var(--bg-app); border:1px solid var(--border-color); margin-top:20px; padding:16px;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
          <label class="label-input" style="margin:0; font-size:0.95rem;">Alternativas u Opciones del Ejercicio</label>
          <button type="button" class="btn btn-verde" style="padding:4px 10px; font-size:0.75rem;" onclick="agregarOpcion()">
            <i class="fas fa-plus"></i> Añadir Opción
          </button>
        </div>
        
        <div id="contenedor-opciones" style="display:flex; flex-direction:column; gap:12px;">
          <!-- Opciones dinámicas -->
        </div>
      </div>

      <div class="modal-acciones" style="margin-top: 24px; display:flex; justify-content:flex-end; gap:12px;">
        <button type="button" class="btn btn-gris" onclick="cerrarModal('modal-ejercicio')">Cancelar</button>
        <button type="submit" class="btn btn-verde" id="btn-guardar-ejercicio">Guardar Ejercicio</button>
      </div>
    </form>
  </div>
</div>

<style>
  .opcion-row {
    display: flex;
    gap: 12px;
    align-items: center;
    background: var(--bg-card);
    padding: 12px;
    border-radius: 8px;
    border: 1px solid var(--border-color);
  }
</style>

<script>
  let opcionIndex = 0;

  function cerrarModal(id) {
    document.getElementById(id).classList.remove('visible');
  }

  function abrirModalEjercicio() {
    document.getElementById('form-ejercicio').reset();
    document.getElementById('ej-id').value = '';
    document.getElementById('modal-titulo-ejercicio').textContent = 'Nuevo Ejercicio';
    document.getElementById('contenedor-opciones').innerHTML = '';
    opcionIndex = 0;
    
    for (let i = 0; i < 4; i++) agregarOpcion();
    
    cambiarTipoEjercicio();
    document.getElementById('modal-ejercicio').classList.add('visible');
  }

  function editarEjercicio(ej) {
    document.getElementById('form-ejercicio').reset();
    document.getElementById('ej-id').value = ej.id;
    document.getElementById('modal-titulo-ejercicio').textContent = 'Editar Ejercicio';
    document.getElementById('ej-tipo').value = ej.tipo;
    document.getElementById('ej-enunciado').value = ej.enunciado;
    
    document.getElementById('contenedor-opciones').innerHTML = '';
    opcionIndex = 0;
    
    if (ej.opciones && ej.opciones.length > 0) {
      ej.opciones.forEach(opc => agregarOpcion(opc));
    } else {
      agregarOpcion();
    }
    
    cambiarTipoEjercicio();
    document.getElementById('modal-ejercicio').classList.add('visible');
  }

  function agregarOpcion(data = null) {
    const container = document.getElementById('contenedor-opciones');
    const idx = opcionIndex++;
    
    const texto = data ? data.texto : '';
    const esCorrecta = data && data.es_correcta == 1 ? 'checked' : '';
    const retro = data && data.retroalimentacion ? data.retroalimentacion : '';
    
    const html = `
      <div class="opcion-row" id="opc-row-${idx}">
        <div style="flex:1;">
          <input type="text" class="input-base" name="opciones[${idx}][texto]" value="${texto.replace(/"/g, '&quot;')}" placeholder="Texto de la opción (Ej: Good morning = Buenos días)" required>
          <input type="text" class="input-base" style="margin-top:6px; font-size:0.8rem; background:rgba(0,0,0,0.05);" name="opciones[${idx}][retroalimentacion]" value="${retro.replace(/"/g, '&quot;')}" placeholder="Retroalimentación (opcional)">
        </div>
        <div style="display:flex; flex-direction:column; align-items:center; gap:4px; min-width:80px;">
          <label style="font-size:0.75rem; font-weight:700; cursor:pointer; color:var(--verde); display:flex; align-items:center; gap:4px;">
            <input type="checkbox" name="opciones[${idx}][es_correcta]" value="1" ${esCorrecta}> Correcta
          </label>
          <button type="button" class="btn-accion btn-suspender" style="width:28px; height:28px;" onclick="document.getElementById('opc-row-${idx}').remove()">
            <i class="fas fa-trash" style="font-size:0.75rem;"></i>
          </button>
        </div>
      </div>
    `;
    container.insertAdjacentHTML('beforeend', html);
  }

  function cambiarTipoEjercicio() {
    const tipo = document.getElementById('ej-tipo').value;
    const helper = document.getElementById('helper-enunciado');
    if (tipo === 'completar_frase') {
      helper.textContent = 'Para "Completar Frase", usa "___" (tres guiones bajos) en la posición donde va la respuesta en blanco.';
    } else if (tipo === 'arrastrar_soltar') {
      helper.textContent = 'Para "Arrastrar y Soltar" (Matching), escribe cada opción como "Inglés = Español" (Ej: Good morning = Buenos días).';
    } else if (tipo === 'escucha_escribe') {
      helper.textContent = 'Para "Escucha y Escribe" (Dictado), marca la opción correcta con el texto exacto que se debe escribir.';
    } else {
      helper.textContent = 'Indica el enunciado claro del ejercicio para el estudiante.';
    }
  }

  function guardarEjercicio(e) {
    e.preventDefault();
    const formData = new FormData(e.target);
    const btn = document.getElementById('btn-guardar-ejercicio');
    btn.disabled = true;
    btn.textContent = 'Guardando...';

    fetch('<?= PROYECTO_PATH ?>/admin/ejercicios/save', {
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
        btn.textContent = 'Guardar Ejercicio';
      }
    })
    .catch(err => {
      console.error(err);
      alert('Error de conexión');
      btn.disabled = false;
      btn.textContent = 'Guardar Ejercicio';
    });
  }

  function eliminarEjercicio(id) {
    if (!confirm('¿Estás seguro de eliminar este ejercicio?')) return;
    
    const formData = new FormData();
    formData.append('id', id);
    formData.append('csrf_token', '<?= generarTokenCSRF() ?>');

    fetch('<?= PROYECTO_PATH ?>/admin/ejercicios/delete', {
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
    const input = document.getElementById('buscar-ejercicio');
    const filter = input.value.toLowerCase();
    const table = document.getElementById('tabla-ejercicios');
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
