<!DOCTYPE html>
<html lang="es" data-theme="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Gestión de RAPs — Admin SmashCode</title>
  <meta name="description" content="Verificación de componentes, publicación y previsualización de RAPs en SmashCode.">
  <link rel="stylesheet" href="<?= PROYECTO_PATH ?>/assets/css/estilos.css?v=<?= time() ?>">
  <link rel="stylesheet" href="<?= PROYECTO_PATH ?>/assets/css/cruds.css?v=<?= time() ?>">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <script>(function(){var t=localStorage.getItem('smashcode_tema');if(t)document.documentElement.setAttribute('data-theme',t);})();</script>
    <!-- Estilos locales removidos: movidos a cruds.css -->
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
        <span class="breadcrumb-link"><i class="fas fa-file-lines" style="color:var(--azul); margin-right:4px;"></i> RAPs</span>
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

    <div class="pagina-contenido" style="padding:10px 24px 32px;">

      <!-- Encabezado -->
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;">
        <div>
          <h1 style="font-size:1.7rem;font-weight:800;letter-spacing:-0.5px;margin:0 0 4px 0;">
            <i class="fas fa-file-lines" style="color:var(--azul);margin-right:10px;"></i>Previsualizar y Publicar RAPs
          </h1>
          <p style="color:var(--texto-secundario);font-size:0.85rem;margin:0;">
            HU03: Controla la completitud de los 5 componentes requeridos y la visibilidad de los RAPs para los aprendices.
          </p>
        </div>
      </div>

      <!-- Alertas flash -->
      <?php if ($exito): ?>
        <div class="alerta-flash alerta-exito" role="alert">
          <i class="fas fa-check-circle"></i> Estado del RAP actualizado correctamente.
        </div>
      <?php endif; ?>

      <!-- Barra de filtros -->
      <div class="barra-filtros" style="margin-bottom: 24px;">
        <div class="contenedor-input-search" style="max-width: 450px; flex: 1; margin: 0;">
          <i class="fas fa-search icono-search"></i>
          <input type="text" id="buscar-rap" class="input-busqueda" placeholder="Buscar RAP o nivel...">
        </div>
      </div>

      <!-- Contenedor Grid -->
      <div class="grid-raps">
        <?php
        $descRaps = [
            1 => 'Getting to Know Other People (Fase Analisis)',
            2 => 'Work Life Interaction - Parte 1 (Fase Planeacion)',
            3 => 'Work Life Interaction - Parte 2 (Fase Planeacion)',
            4 => 'Work Place Communication - Parte 1 (Fase Ejecucion)',
            5 => 'Work Place Communication - Parte 2 (Fase Ejecucion)',
            6 => 'Professional Practice (Fase Evaluacion)',
        ];
        ?>
        <?php foreach ($raps as $r): 
          // Validar completitud de cada uno de los 5 componentes
          $cVocab = $r['total_vocabulario'] > 0;
          $cPron  = $r['total_vocabulario'] > 0 && ($r['total_pronunciacion'] == $r['total_vocabulario']);
          $cEjerc = $r['total_ejercicios'] > 0;
          $cDial  = $r['total_dialogos'] > 0;
          $cQuiz  = $r['tiene_quiz'] > 0 && $r['total_preguntas_quiz'] > 0;

          $esCompleto = ($cVocab && $cPron && $cEjerc && $cDial && $cQuiz);
        ?>
        <div id="fila-rap-<?= $r['id'] ?>" class="card-rap fila-rap <?= !$r['rap_activo'] ? 'inactiva' : '' ?>" data-nombre="<?= limpiar(mb_strtolower($r['titulo'])) ?>" data-nivel="<?= limpiar(mb_strtolower($r['nivel_nombre'])) ?>">
          
          <div class="card-rap-header">
            <div>
              <div style="font-weight:800; font-size:1.1rem; color:var(--texto-principal); letter-spacing:-0.3px; margin-bottom:4px;"><?= limpiar($r['titulo']) ?></div>
              <div style="font-size:0.8rem; color:var(--texto-tenue); font-weight:600; line-height:1.4;">
                <i class="fas fa-graduation-cap" style="margin-right:4px;"></i><?= $descRaps[$r['nivel_orden']] ?? limpiar($r['nivel_nombre']) ?>
              </div>
            </div>
            <div>
              <?php if ($esCompleto): ?>
                <span class="badge-completitud si" style="padding:4px 8px; font-size:0.7rem;">Completo</span>
              <?php else: ?>
                <span class="badge-completitud no" style="padding:4px 8px; font-size:0.7rem;">Incompleto</span>
              <?php endif; ?>
            </div>
          </div>

          <div class="card-rap-body">
            <div style="font-size:0.75rem; color:var(--texto-tenue); text-transform:uppercase; font-weight:700; margin-bottom:12px; letter-spacing:0.5px;">Componentes</div>
            
            <!-- Botón de Vocabulario (Es el único interactivo por ahora) -->
            <a href="<?= PROYECTO_PATH ?>/admin/vocabulario?rap_id=<?= urlencode($r['id']) ?>" class="btn-componente <?= $cVocab ? 'completado' : 'incompleto' ?>" title="Gestionar Vocabulario">
              <div class="btn-componente-icono-izq">
                <i class="fas fa-<?= $cVocab ? 'circle-check' : 'circle-xmark' ?>" style="color: <?= $cVocab ? 'var(--verde)' : 'var(--rojo)' ?>;"></i>
                <span>Vocabulario</span>
              </div>
              <div class="btn-componente-icono-der">
                <?= (int)$r['total_vocabulario'] ?> <i class="fas fa-pen"></i>
              </div>
            </a>

            <!-- Resto de botones (informativos por ahora, podrían ser interactivos luego) -->
            <div class="btn-componente <?= $cPron ? 'completado' : 'incompleto' ?>" title="Requerido: Todos los vocablos con IPA configurado">
              <div class="btn-componente-icono-izq">
                <i class="fas fa-<?= $cPron ? 'circle-check' : 'circle-xmark' ?>" style="color: <?= $cPron ? 'var(--verde)' : 'var(--rojo)' ?>;"></i>
                <span>IPA (Pronunciación)</span>
              </div>
              <div class="btn-componente-icono-der">
                <?= (int)$r['total_pronunciacion'] ?>/<?= (int)$r['total_vocabulario'] ?>
              </div>
            </div>

            <div class="btn-componente <?= $cEjerc ? 'completado' : 'incompleto' ?>" title="Requerido: >= 1 ejercicio activo">
              <div class="btn-componente-icono-izq">
                <i class="fas fa-<?= $cEjerc ? 'circle-check' : 'circle-xmark' ?>" style="color: <?= $cEjerc ? 'var(--verde)' : 'var(--rojo)' ?>;"></i>
                <span>Ejercicios</span>
              </div>
              <div class="btn-componente-icono-der">
                <?= (int)$r['total_ejercicios'] ?>
              </div>
            </div>

            <div class="btn-componente <?= $cDial ? 'completado' : 'incompleto' ?>" title="Requerido: >= 1 diálogo clínico activo">
              <div class="btn-componente-icono-izq">
                <i class="fas fa-<?= $cDial ? 'circle-check' : 'circle-xmark' ?>" style="color: <?= $cDial ? 'var(--verde)' : 'var(--rojo)' ?>;"></i>
                <span>Diálogos</span>
              </div>
              <div class="btn-componente-icono-der">
                <?= (int)$r['total_dialogos'] ?>
              </div>
            </div>

            <div class="btn-componente <?= $cQuiz ? 'completado' : 'incompleto' ?>" title="Requerido: Quiz con >= 1 pregunta">
              <div class="btn-componente-icono-izq">
                <i class="fas fa-<?= $cQuiz ? 'circle-check' : 'circle-xmark' ?>" style="color: <?= $cQuiz ? 'var(--verde)' : 'var(--rojo)' ?>;"></i>
                <span>Quiz</span>
              </div>
              <div class="btn-componente-icono-der">
                <?= (int)$r['total_preguntas_quiz'] ?> preg.
              </div>
            </div>
          </div>

          <div class="card-rap-footer">
            <div style="display:flex; align-items:center; gap:8px;">
              <?php if ($r['rap_activo']): ?>
                <span class="badge-activo" style="padding:4px 8px; font-size:0.7rem;"><i class="fas fa-eye"></i> Publicado</span>
              <?php else: ?>
                <span class="badge-inactivo" style="padding:4px 8px; font-size:0.7rem;"><i class="fas fa-eye-slash"></i> Inactivo</span>
              <?php endif; ?>
            </div>
            
            <div style="display:flex; gap:8px;">
              <a href="<?= PROYECTO_PATH ?>/aprendiz/rap?id=<?= urlencode($r['id']) ?>" 
                 class="btn btn-azul" 
                 style="padding:6px 12px; font-size:0.75rem; border:none; display:inline-flex; align-items:center; gap:6px; text-decoration:none;"
                 id="btn-preview-rap-<?= $r['nivel_orden'] ?>"
                 title="Previsualizar la vista exacta del aprendiz">
                <i class="fas fa-desktop"></i> Prever
              </a>

              <button type="button" 
                      class="btn-accion <?= $r['rap_activo'] ? 'btn-suspender' : 'btn-activar' ?>"
                      style="width: 32px; height: 32px; display:inline-flex; align-items:center; justify-content:center; border-radius:8px;"
                      title="<?= $r['rap_activo'] ? 'Desactivar RAP' : 'Publicar RAP' ?>"
                      id="btn-toggle-rap-<?= $r['nivel_orden'] ?>"
                      onclick="abrirModalToggle('<?= $r['id'] ?>', <?= $r['rap_activo'] ?>, '<?= limpiar(addslashes($r['titulo'])) ?>', <?= $esCompleto ? 'true' : 'false' ?>)">
                <i class="fas fa-<?= $r['rap_activo'] ? 'ban' : 'circle-check' ?>"></i>
              </button>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>

    </div>

    <!-- Modal: Publicar / Desactivar RAP -->
    <div class="modal-fondo" id="modal-toggle-rap">
      <div class="modal-caja">
        <p class="modal-titulo" id="modal-rap-title" style="font-size:1.3rem; font-weight:800; color:var(--texto-principal); margin-bottom:12px;"></p>
        <p class="modal-desc" id="modal-rap-desc" style="font-size:0.875rem; color:var(--texto-secundario); line-height:1.6; margin-bottom:24px;"></p>
        
        <div id="modal-warning-completo" style="display:none; margin-bottom:20px; padding:12px 16px; background:rgba(255,150,0,0.08); border:1px solid rgba(255,150,0,0.3); border-radius:12px; display:flex; gap:10px; align-items:center;">
          <i class="fas fa-triangle-exclamation" style="color:#FF9600; font-size:1.2rem;"></i>
          <p style="margin:0; font-size:0.75rem; color:var(--texto-secundario); text-align:left;">
            ⚠️ <strong>Atención:</strong> Este RAP tiene componentes incompletos. Se recomienda completar todos los componentes antes de publicarlo.
          </p>
        </div>

        <form method="POST" action="<?= PROYECTO_PATH ?>/admin/raps/toggle">
          <input type="hidden" name="csrf_token" value="<?= generarTokenCSRF() ?>">
          <input type="hidden" name="id" id="toggle-rap-id">
          <div class="modal-acciones" style="gap:12px;">
            <button type="button" class="btn btn-gris" onclick="cerrarModal('modal-toggle-rap')">Cancelar</button>
            <button type="submit" class="btn" id="toggle-rap-btn-confirm">Confirmar</button>
          </div>
        </form>
      </div>
    </div>

  </main>
</div>

<script src="<?= PROYECTO_PATH ?>/assets/js/tema.js"></script>
<script src="<?= PROYECTO_PATH ?>/assets/js/admin_cruds.js"></script>
<script>
  function abrirModalToggle(id, activo, titulo, esCompleto) {
    document.getElementById('toggle-rap-id').value = id;
    const accion = activo === 1 ? 'Desactivar' : 'Publicar';
    document.getElementById('modal-rap-title').textContent = accion + ' RAP';
    
    let desc = '¿Estás seguro de que deseas ' + accion.toLowerCase() + ' el RAP «' + titulo + '»? ';
    if (activo === 1) {
      desc += 'Los aprendices ya no podrán ver este RAP en su mapa de aprendizaje.';
      document.getElementById('modal-warning-completo').style.display = 'none';
    } else {
      desc += 'El RAP se hará inmediatamente visible en el mapa de aprendizaje de los aprendices que tengan el nivel correspondiente desbloqueado.';
      if (!esCompleto) {
        document.getElementById('modal-warning-completo').style.display = 'flex';
      } else {
        document.getElementById('modal-warning-completo').style.display = 'none';
      }
    }
    
    document.getElementById('modal-rap-desc').textContent = desc;
    
    const btnConfirm = document.getElementById('toggle-rap-btn-confirm');
    if (activo === 1) {
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
      btnConfirm.textContent = 'Publicar';
    }
    
    document.getElementById('modal-toggle-rap').classList.add('visible');
  }

  document.addEventListener('DOMContentLoaded', () => {
    inicializarBusqueda('buscar-rap', '.fila-rap');
  });
</script>
</body>
</html>
