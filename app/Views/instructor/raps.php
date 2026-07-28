<!DOCTYPE html>
<html lang="es" data-theme="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Mapeo de RAPs — Instructor SmashCode</title>
  <meta name="description" content="Monitoreo de componentes, completitud y previsualización de RAPs para instructores.">
  <link rel="stylesheet" href="<?= PROYECTO_PATH ?>/assets/css/estilos.css?v=<?= time() ?>">
  <link rel="stylesheet" href="<?= PROYECTO_PATH ?>/assets/css/cruds.css?v=<?= time() ?>">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <script>(function(){var t=localStorage.getItem('smashcode_tema');if(t)document.documentElement.setAttribute('data-theme',t);})();</script>
  <style>
    .grid-niveles {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
      gap: 20px;
    }
    .comp-badge {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 6px 12px;
      border-radius: 12px;
      font-size: 0.75rem;
      font-weight: 700;
      border: 1px solid transparent;
      transition: all 0.2s;
    }
    .comp-badge.complete {
      background: rgba(16, 185, 129, 0.08);
      color: #10B981;
      border-color: rgba(16, 185, 129, 0.2);
    }
    .comp-badge.incomplete {
      background: rgba(239, 68, 68, 0.08);
      color: #EF4444;
      border-color: rgba(239, 68, 68, 0.2);
    }
    .badge-completitud {
      font-size: 0.72rem;
      font-weight: 800;
      text-transform: uppercase;
      padding: 4px 10px;
      border-radius: 99px;
      letter-spacing: 0.05em;
    }
    .badge-completitud.si {
      background: #10B981;
      color: #fff;
      box-shadow: 0 4px 0 #059669;
    }
    .badge-completitud.no {
      background: #EF4444;
      color: #fff;
      box-shadow: 0 4px 0 #DC2626;
    }
  </style>
</head>
<body>
<div class="contenedor-app">

  <!-- Sidebar Instructor -->
  <nav class="barra-lateral" aria-label="Navegación instructor">
    <div class="logo-app">
      <div class="logo-icono">
        <svg viewBox="0 0 100 100" width="40" height="40" xmlns="http://www.w3.org/2000/svg" style="display:block;">
          <ellipse cx="50" cy="85" rx="22" ry="5" fill="#000" opacity="0.3"/>
          <ellipse cx="38" cy="82" rx="7" ry="4" fill="#FF9600"/>
          <ellipse cx="62" cy="82" rx="7" ry="4" fill="#FF9600"/>
          <rect x="26" y="20" width="48" height="58" rx="24" fill="#2B3E46"/>
          <path d="M 26 38 C 17 42 17 56 26 62 Z" fill="#2B3E46"/>
          <path d="M 74 38 C 83 42 83 56 74 62 Z" fill="#2B3E46"/>
          <ellipse cx="50" cy="54" rx="17" ry="20" fill="#FFFFFF"/>
          <ellipse cx="41" cy="38" rx="9" ry="9" fill="#FFFFFF"/>
          <ellipse cx="59" cy="38" rx="9" ry="9" fill="#FFFFFF"/>
          <circle cx="42" cy="38" r="5" fill="#111B1E"/>
          <circle cx="40.5" cy="36.5" r="1.8" fill="#FFFFFF"/>
          <circle cx="58" cy="38" r="5" fill="#111B1E"/>
          <circle cx="56.5" cy="36.5" r="1.8" fill="#FFFFFF"/>
          <path d="M 44 43 Q 50 51 56 43 Z" fill="#FF9600" stroke="#FF9600" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </div>
      <div>
        <div class="logo-nombre">Smash<span>Code</span></div>
        <div style="font-size:0.62rem;color:#52656D;letter-spacing:1.5px;font-weight:800;padding-left:2px;margin-top:2px;">INSTRUCTOR</div>
      </div>
    </div>
    <ul class="nav-lateral">
      <li><a href="<?= PROYECTO_PATH ?>/instructor" class="nav-enlace"><i class="fas fa-gauge-high nav-icono"></i><span>Dashboard</span></a></li>
      <li><a href="<?= PROYECTO_PATH ?>/instructor/aprendices" class="nav-enlace"><i class="fas fa-users nav-icono"></i><span>Mis Aprendices</span></a></li>
      <li><a href="<?= PROYECTO_PATH ?>/instructor/resultados" class="nav-enlace"><i class="fas fa-clipboard-list nav-icono"></i><span>Resultados Quiz</span></a></li>
      <li><a href="<?= PROYECTO_PATH ?>/instructor/niveles" class="nav-enlace"><i class="fas fa-layer-group nav-icono"></i><span>Niveles</span></a></li>
      <li><a href="<?= PROYECTO_PATH ?>/instructor/raps" class="nav-enlace activo" aria-current="page"><i class="fas fa-file-lines nav-icono"></i><span>RAPs</span></a></li>
      <li><a href="<?= PROYECTO_PATH ?>/instructor/exportar" class="nav-enlace"><i class="fas fa-file-csv nav-icono"></i><span>Exportar CSV</span></a></li>
      <li><a href="<?= PROYECTO_PATH ?>/logout" class="nav-enlace" style="color:var(--rojo);"><i class="fas fa-right-from-bracket nav-icono"></i><span>Cerrar Sesión</span></a></li>
    </ul>
  </nav>

  <main class="contenido-principal">
    <header class="barra-superior">
      <button id="btn-cambiar-tema" class="btn-tema" aria-label="Cambiar tema">
        <i class="fas fa-sun tema-icono"></i><span class="tema-label">Claro</span>
      </button>
      <div class="avatar-usuario" title="<?= limpiar($_SESSION['nombre']) ?>">
        <?= strtoupper(substr($_SESSION['nombre'], 0, 1)) ?>
      </div>
    </header>

    <div class="pagina-contenido">
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;">
        <div>
          <h1 class="pagina-titulo"><i class="fas fa-file-lines" style="color:var(--azul);margin-right:10px;"></i>Mapeo de RAPs</h1>
          <p class="pagina-subtitulo">Visualiza el estado de los componentes de cada RAP y previsualiza la experiencia del aprendiz.</p>
        </div>
      </div>

      <!-- Barra de filtros -->
      <div class="barra-filtros" style="margin-bottom: 24px;">
        <div class="contenedor-input-search" style="max-width: 450px; flex: 1; margin: 0;">
          <i class="fas fa-search icono-search"></i>
          <input type="text" id="buscar-rap" class="input-busqueda" placeholder="Buscar RAP o nivel...">
        </div>
      </div>

      <!-- Contenedor Tabla -->
      <!-- Contenedor Grid RAPs (Modo Lectura) -->
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
              <div style="font-weight:800; font-size:1.1rem; color:var(--texto-principal); letter-spacing:-0.3px; margin-bottom:4px;">RAP <?= $r['nivel_orden'] ?></div>
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
            
            <div class="btn-componente <?= $cVocab ? 'completado' : 'incompleto' ?>">
              <div class="btn-componente-icono-izq">
                <i class="fas fa-<?= $cVocab ? 'circle-check' : 'circle-xmark' ?>" style="color: <?= $cVocab ? 'var(--verde)' : 'var(--rojo)' ?>;"></i>
                <span>Vocabulario</span>
              </div>
              <div class="btn-componente-icono-der">
                <?= (int)$r['total_vocabulario'] ?>
              </div>
            </div>

            <div class="btn-componente <?= $cPron ? 'completado' : 'incompleto' ?>">
              <div class="btn-componente-icono-izq">
                <i class="fas fa-<?= $cPron ? 'circle-check' : 'circle-xmark' ?>" style="color: <?= $cPron ? 'var(--verde)' : 'var(--rojo)' ?>;"></i>
                <span>IPA (Pronunciación)</span>
              </div>
              <div class="btn-componente-icono-der">
                <?= (int)$r['total_pronunciacion'] ?>/<?= (int)$r['total_vocabulario'] ?>
              </div>
            </div>

            <div class="btn-componente <?= $cEjerc ? 'completado' : 'incompleto' ?>">
              <div class="btn-componente-icono-izq">
                <i class="fas fa-<?= $cEjerc ? 'circle-check' : 'circle-xmark' ?>" style="color: <?= $cEjerc ? 'var(--verde)' : 'var(--rojo)' ?>;"></i>
                <span>Ejercicios</span>
              </div>
              <div class="btn-componente-icono-der">
                <?= (int)$r['total_ejercicios'] ?>
              </div>
            </div>

            <div class="btn-componente <?= $cDial ? 'completado' : 'incompleto' ?>">
              <div class="btn-componente-icono-izq">
                <i class="fas fa-<?= $cDial ? 'circle-check' : 'circle-xmark' ?>" style="color: <?= $cDial ? 'var(--verde)' : 'var(--rojo)' ?>;"></i>
                <span>Diálogos</span>
              </div>
              <div class="btn-componente-icono-der">
                <?= (int)$r['total_dialogos'] ?>
              </div>
            </div>

            <div class="btn-componente <?= $cQuiz ? 'completado' : 'incompleto' ?>">
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
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>

    </div>
  </main>
</div>

<script src="<?= PROYECTO_PATH ?>/assets/js/tema.js"></script>
<script>
  // Client-side search for RAPs
  document.getElementById('buscar-rap')?.addEventListener('input', function(e) {
    const query = e.target.value.toLowerCase().trim();
    const rows = document.querySelectorAll('.fila-rap');
    
    rows.forEach(row => {
      const nombre = row.getAttribute('data-nombre') || '';
      const nivel = row.getAttribute('data-nivel') || '';
      
      if (nombre.includes(query) || nivel.includes(query)) {
        row.style.display = '';
      } else {
        row.style.display = 'none';
      }
    });
  });
</script>
</body>
</html>
