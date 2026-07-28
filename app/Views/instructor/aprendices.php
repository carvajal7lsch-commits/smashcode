<?php
// Evitar variables no definidas
$filtroNivel  = $filtroNivel ?? '';
$filtroRap    = $filtroRap ?? '';
$filtroEstado = $filtroEstado ?? '';
?>
<!DOCTYPE html>
<html lang="es" data-theme="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Mis Aprendices — Instructor SmashCode</title>
  <link rel="stylesheet" href="<?= PROYECTO_PATH ?>/assets/css/estilos.css?v=<?= time() ?>">
  <link rel="stylesheet" href="<?= PROYECTO_PATH ?>/assets/css/dashboard.css?v=<?= time() ?>">
  <link rel="stylesheet" href="<?= PROYECTO_PATH ?>/assets/css/cruds.css?v=<?= time() ?>">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <script>(function(){var t=localStorage.getItem('smashcode_tema');if(t)document.documentElement.setAttribute('data-theme',t);})();</script>
</head>
<body>
<div class="contenedor-app">

  <!-- Barra lateral instructor -->
  <nav class="barra-lateral" aria-label="Navegación instructor">
    <div class="logo-app">
      <div class="logo-icono">
        <svg viewBox="0 0 100 100" width="40" height="40" xmlns="http://www.w3.org/2000/svg" class="svg-block">
          <ellipse cx="50" cy="85" rx="22" ry="5" fill="#000" opacity="0.3" />
          <ellipse cx="38" cy="82" rx="7" ry="4" fill="#FF9600" />
          <ellipse cx="62" cy="82" rx="7" ry="4" fill="#FF9600" />
          <rect x="26" y="20" width="48" height="58" rx="24" fill="#2B3E46" />
          <path d="M 26 38 C 17 42 17 56 26 62 Z" fill="#2B3E46" />
          <path d="M 74 38 C 83 42 83 56 74 62 Z" fill="#2B3E46" />
          <ellipse cx="50" cy="54" rx="17" ry="20" fill="#FFFFFF" />
          <ellipse cx="41" cy="38" rx="9" ry="9" fill="#FFFFFF" />
          <ellipse cx="59" cy="38" rx="9" ry="9" fill="#FFFFFF" />
          <circle cx="42" cy="38" r="5" fill="#111B1E" />
          <circle cx="40.5" cy="36.5" r="1.8" fill="#FFFFFF" />
          <circle cx="58" cy="38" r="5" fill="#111B1E" />
          <circle cx="56.5" cy="36.5" r="1.8" fill="#FFFFFF" />
          <path d="M 44 43 Q 50 51 56 43 Z" fill="#FF9600" stroke="#FF9600" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
      </div>
      <div>
        <div class="logo-nombre">Smash<span>Code</span></div>
        <div class="logo-rol-texto">INSTRUCTOR</div>
      </div>
    </div>
    <ul class="nav-lateral">
      <li><a href="<?= PROYECTO_PATH ?>/instructor" class="nav-enlace"><i class="fas fa-gauge-high nav-icono"></i><span>Dashboard</span></a></li>
      <li><a href="<?= PROYECTO_PATH ?>/instructor/aprendices" class="nav-enlace activo" aria-current="page"><i class="fas fa-users nav-icono"></i><span>Mis Aprendices</span></a></li>
      <li><a href="<?= PROYECTO_PATH ?>/instructor/resultados" class="nav-enlace"><i class="fas fa-clipboard-list nav-icono"></i><span>Resultados Quiz</span></a></li>
      <li><a href="<?= PROYECTO_PATH ?>/instructor/niveles" class="nav-enlace"><i class="fas fa-layer-group nav-icono"></i><span>Niveles</span></a></li>
      <li><a href="<?= PROYECTO_PATH ?>/instructor/raps" class="nav-enlace"><i class="fas fa-file-lines nav-icono"></i><span>RAPs</span></a></li>
      <li><a href="<?= PROYECTO_PATH ?>/instructor/exportar" class="nav-enlace"><i class="fas fa-file-csv nav-icono"></i><span>Exportar CSV</span></a></li>
      <li><a href="<?= PROYECTO_PATH ?>/logout" class="nav-enlace nav-enlace-salir"><i class="fas fa-right-from-bracket nav-icono"></i><span>Cerrar Sesión</span></a></li>
    </ul>
  </nav>

  <!-- Contenido principal -->
  <main class="contenido-principal">
    <header class="barra-superior">
      <!-- Botón cambio de tema -->
      <button id="btn-cambiar-tema" class="btn-tema" aria-label="Cambiar a modo claro" title="Cambiar a modo claro">
        <i class="fas fa-sun tema-icono"></i>
        <span class="tema-label">Claro</span>
      </button>
      <div class="avatar-usuario" title="<?= limpiar($_SESSION['nombre'] ?? 'Instructor') ?>">
        <?= strtoupper(substr($_SESSION['nombre'] ?? 'I', 0, 1)) ?>
      </div>
    </header>

    <div class="dashboard-page-content">
      <div class="dashboard-welcome-header">
        <div>
          <h1 class="dashboard-welcome-title">Filtro de Aprendices</h1>
          <p class="dashboard-welcome-subtitle">Consulta el progreso detallado aplicando filtros.</p>
        </div>
      </div>

      <!-- Filtros -->
      <div class="tarjeta" style="margin-bottom: 24px;">
        <form method="GET" action="<?= PROYECTO_PATH ?>/instructor/aprendices" style="display:flex; gap:16px; align-items:flex-end; flex-wrap:wrap;">
          
          <div class="form-grupo" style="flex:1; min-width:200px; display:flex; flex-direction:column; gap:8px;">
            <label style="font-weight:600; color:var(--texto-principal);">Nivel:</label>
            <select name="nivel_id" class="input-premium select-premium">
              <option value="">Todos los Niveles</option>
              <?php foreach ($nivelesConRaps as $n): ?>
                <option value="<?= $n['id'] ?>" <?= $filtroNivel == $n['id'] ? 'selected' : '' ?>>
                  <?= limpiar($n['nombre']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="form-grupo" style="flex:1; min-width:200px; display:flex; flex-direction:column; gap:8px;">
            <label style="font-weight:600; color:var(--texto-principal);">RAP:</label>
            <select name="rap_id" class="input-premium select-premium">
              <option value="">Todos los RAPs</option>
              <?php foreach ($nivelesConRaps as $n): ?>
                <?php if (!empty($n['rap_id'])): ?>
                  <option value="<?= $n['rap_id'] ?>" <?= $filtroRap == $n['rap_id'] ? 'selected' : '' ?>>
                    RAP <?= $n['orden'] ?>: <?= limpiar($n['rap_titulo']) ?>
                  </option>
                <?php endif; ?>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="form-grupo" style="flex:1; min-width:200px; display:flex; flex-direction:column; gap:8px;">
            <label style="font-weight:600; color:var(--texto-principal);">Estado:</label>
            <select name="estado" class="input-premium select-premium">
              <option value="">Todos los Estados</option>
              <option value="completado" <?= $filtroEstado == 'completado' ? 'selected' : '' ?>>Completado</option>
              <option value="en_progreso" <?= $filtroEstado == 'en_progreso' ? 'selected' : '' ?>>En Progreso</option>
              <option value="sin_iniciar" <?= $filtroEstado == 'sin_iniciar' ? 'selected' : '' ?>>Sin Iniciar</option>
            </select>
          </div>

          <div class="form-grupo" style="display:flex; gap:8px;">
            <button type="submit" class="btn btn-primario"><i class="fas fa-search"></i> Filtrar</button>
            <a href="<?= PROYECTO_PATH ?>/instructor/aprendices" class="btn btn-secundario"><i class="fas fa-eraser"></i></a>
          </div>

        </form>
      </div>

      <!-- Tabla de aprendices filtrados -->
      <div class="tarjeta">
        <div class="lista-aprendices-header">
          <span class="lista-aprendices-titulo">
            <i class="fas fa-list-ul"></i>
            Resultados (<?= count($aprendices) ?>)
          </span>
          <a href="<?= PROYECTO_PATH ?>/instructor/exportar" class="btn btn-primario btn-exportar-csv">
            <i class="fas fa-download"></i> Exportar Todo a CSV
          </a>
        </div>

        <?php if (empty($aprendices)): ?>
          <p class="mensaje-vacio-tabla">
            No hay aprendices que coincidan con los filtros.
          </p>
        <?php else: ?>
        <div class="tabla-container-scroll">
          <table class="tabla-aprendices tabla-premium" id="tabla-aprendices" style="width:100%;">
            <thead>
              <tr>
                <th>Nombre</th>
                <th>Correo</th>
                <th class="text-center">RAPs Iniciados</th>
                <th class="text-center">RAPs Completados</th>
                <th>Avance Promedio</th>
                <th>XP</th>
                <th>Estado</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($aprendices as $a):
                $avance = (float) $a['avance_promedio'];
                $nivelClase = $avance >= 70 ? 'nivel-alto' : ($avance >= 40 ? 'nivel-medio' : 'nivel-bajo');
                
                // Si filtramos por un estado, la lógica del chip puede adaptarse, 
                // pero por defecto mostramos el estado en base a avance.
                $chipTexto = 'En riesgo';
                $chipClase = 'chip-riesgo';
                
                if ($avance == 100) {
                    $chipTexto = 'Completado';
                    $chipClase = 'chip-activo';
                } elseif ($avance >= 40) {
                    $chipTexto = 'En progreso';
                    $chipClase = 'chip-activo';
                }
                
                if ($filtroEstado === 'sin_iniciar' || $a['raps_iniciados'] == 0) {
                    $chipTexto = 'Sin iniciar';
                    $chipClase = 'chip-riesgo';
                }
              ?>
              <tr>
                <td><?= limpiar($a['nombre_completo']) ?></td>
                <td><?= limpiar($a['correo']) ?></td>
                <td class="text-center"><?= $a['raps_iniciados'] ?></td>
                <td class="text-center"><?= $a['raps_completados'] ?></td>
                <td>
                  <div class="progreso-mini">
                    <div class="barra">
                      <div class="relleno <?= $nivelClase ?>" style="width:<?= min(100, $avance) ?>%"></div>
                    </div>
                    <span class="progreso-mini-porcentaje"><?= number_format($avance,0) ?>%</span>
                  </div>
                </td>
                <td><?= formatearXP((int)$a['xp_puntos']) ?></td>
                <td><span class="chip-estado <?= $chipClase ?>"><?= $chipTexto ?></span></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </main>
</div>
<script src="<?= PROYECTO_PATH ?>/assets/js/tema.js"></script>
</body>
</html>
