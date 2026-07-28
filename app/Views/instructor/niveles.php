<!DOCTYPE html>
<html lang="es" data-theme="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Gestión de Niveles — Instructor SmashCode</title>
  <meta name="description" content="Panel del instructor: gestiona los 6 niveles del programa de inglés médico.">
  <link rel="stylesheet" href="<?= PROYECTO_PATH ?>/assets/css/estilos.css?v=<?= time() ?>">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <script>(function(){var t=localStorage.getItem('smashcode_tema');if(t)document.documentElement.setAttribute('data-theme',t);})();</script>
  <link rel="stylesheet" href="<?= PROYECTO_PATH ?>/assets/css/cruds.css?v=<?= time() ?>">
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
      <li><a href="<?= PROYECTO_PATH ?>/instructor/niveles" class="nav-enlace activo" aria-current="page"><i class="fas fa-layer-group nav-icono"></i><span>Niveles</span></a></li>
      <li><a href="<?= PROYECTO_PATH ?>/instructor/raps" class="nav-enlace"><i class="fas fa-file-lines nav-icono"></i><span>RAPs</span></a></li>
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

    <div class="pagina-contenido pagina-contenido-admin">
      <div class="encabezado-seccion-admin">
        <div>
          <h1 class="titulo-seccion-admin"><i class="fas fa-layer-group icono-seccion-admin"></i>Gestión de Niveles</h1>
          <p class="desc-seccion-admin">Edita los 6 niveles del programa · MCER A1 → B2</p>
        </div>
      </div>

      <!-- Alertas -->
      <?php if ($exito): ?>
        <div class="alerta-flash alerta-exito" role="alert">
          <i class="fas fa-check-circle"></i>
          <?= match($exito) { 'actualizado' => 'Nivel actualizado correctamente.', 'estado' => 'Estado del nivel actualizado.', default => 'Operación completada.' } ?>
        </div>
      <?php endif; ?>
      <?php if ($error): ?>
        <div class="alerta-flash alerta-error" role="alert"><i class="fas fa-triangle-exclamation"></i> <?= $error ?></div>
      <?php endif; ?>

      <!-- Grid de niveles -->
      <div class="grid-niveles">
        <?php
        $mcer = ['A1', 'A2', 'B1', 'B1+', 'B2-', 'B2'];
        $iconos = ['🩺','💊','🏥','📋','🚑','🩻'];
        foreach ($niveles as $n):
          $orden = (int)$n['orden'];
          $mcerLabel = $mcer[$orden - 1] ?? 'N/A';
          $icono     = $iconos[$orden - 1] ?? '📚';
        ?>
        <div class="card-nivel <?= $n['activo'] ? '' : 'inactivo' ?>">
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
              <span class="badge-<?= $n['activo'] ? 'activo' : 'inactivo' ?>">
                <i class="fas fa-circle icono-punto-<?= $n['activo'] ? 'activo' : 'inactivo' ?>"></i> <?= $n['activo'] ? 'Activo' : 'Inactivo' ?>
              </span>
            </div>
            <div class="card-nivel-acciones">
              <?php if ($n['rap_id']): ?>
                <a href="<?= PROYECTO_PATH ?>/aprendiz/rap?id=<?= urlencode($n['rap_id']) ?>" 
                   class="btn-prever-nivel btn-azul" 
                   title="Previsualizar el RAP como aprendiz">
                  <i class="fas fa-eye-low-vision"></i> Prever
                </a>
              <?php endif; ?>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>

      <div class="nota-informativa-admin">
        <i class="fas fa-circle-info nota-informativa-icono"></i>
        <p class="nota-informativa-texto">
          Este panel muestra los niveles activos en la plataforma.
          No tienes permisos para crear, editar ni eliminar contenido (solo consulta de RAPs).
        </p>
      </div>

    </div>
  </main>
</div>
<script src="<?= PROYECTO_PATH ?>/assets/js/tema.js"></script>
</body>
</html>
