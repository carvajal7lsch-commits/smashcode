<!DOCTYPE html>
<html lang="es" data-theme="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Panel Instructor — SmashCode</title>
  <link rel="stylesheet" href="<?= PROYECTO_PATH ?>/assets/css/estilos.css?v=<?= time() ?>">
  <link rel="stylesheet" href="<?= PROYECTO_PATH ?>/assets/css/dashboard.css?v=<?= time() ?>">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <script>/* Aplicar tema guardado antes del paint */
  (function(){var t=localStorage.getItem('smashcode_tema');if(t)document.documentElement.setAttribute('data-theme',t);})();
  </script>
</head>
<body>
<div class="contenedor-app">

  <!-- Barra lateral instructor -->
  <nav class="barra-lateral" aria-label="Navegación instructor">
    <div class="logo-app">
      <div class="logo-icono">
        <svg viewBox="0 0 100 100" width="40" height="40" xmlns="http://www.w3.org/2000/svg" class="svg-block">
          <!-- Sombra sutil -->
          <ellipse cx="50" cy="85" rx="22" ry="5" fill="#000" opacity="0.3" />
          
          <!-- Patitas (Naranja Duolingo) -->
          <ellipse cx="38" cy="82" rx="7" ry="4" fill="#FF9600" />
          <ellipse cx="62" cy="82" rx="7" ry="4" fill="#FF9600" />
          
          <!-- Cuerpo Principal (Azul oscuro mate Duolingo) -->
          <rect x="26" y="20" width="48" height="58" rx="24" fill="#2B3E46" />
          
          <!-- Aletas laterales -->
          <!-- Izquierda -->
          <path d="M 26 38 C 17 42 17 56 26 62 Z" fill="#2B3E46" />
          <!-- Derecha -->
          <path d="M 74 38 C 83 42 83 56 74 62 Z" fill="#2B3E46" />
          
          <!-- Barriga (Blanca redonda) -->
          <ellipse cx="50" cy="54" rx="17" ry="20" fill="#FFFFFF" />
          
          <!-- Cara (Parches blancos de los ojos) -->
          <ellipse cx="41" cy="38" rx="9" ry="9" fill="#FFFFFF" />
          <ellipse cx="59" cy="38" rx="9" ry="9" fill="#FFFFFF" />
          
          <!-- Ojos Grandes Lindos -->
          <!-- Ojo Izquierdo -->
          <circle cx="42" cy="38" r="5" fill="#111B1E" />
          <circle cx="40.5" cy="36.5" r="1.8" fill="#FFFFFF" />
          <!-- Ojo Derecho -->
          <circle cx="58" cy="38" r="5" fill="#111B1E" />
          <circle cx="56.5" cy="36.5" r="1.8" fill="#FFFFFF" />
          
          <!-- Pico Naranja Lindo -->
          <path d="M 44 43 Q 50 51 56 43 Z" fill="#FF9600" stroke="#FF9600" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
      </div>
      <div>
        <div class="logo-nombre">Smash<span>Code</span></div>
        <div class="logo-rol-texto">INSTRUCTOR</div>
      </div>
    </div>
    <ul class="nav-lateral">
      <li>
        <a href="<?= PROYECTO_PATH ?>/instructor" class="nav-enlace activo" aria-current="page">
          <i class="fas fa-gauge-high nav-icono"></i><span>Dashboard</span>
        </a>
      </li>
      <li>
        <a href="<?= PROYECTO_PATH ?>/instructor/niveles" class="nav-enlace">
          <i class="fas fa-layer-group nav-icono"></i><span>Niveles</span>
        </a>
      </li>
      <li>
        <a href="<?= PROYECTO_PATH ?>/instructor/raps" class="nav-enlace">
          <i class="fas fa-file-lines nav-icono"></i><span>RAPs</span>
        </a>
      </li>
      <li>
        <a href="<?= PROYECTO_PATH ?>/logout" class="nav-enlace nav-enlace-salir">
          <i class="fas fa-right-from-bracket nav-icono"></i><span>Cerrar Sesión</span>
        </a>
      </li>
    </ul>
  </nav>

  <main class="contenido-principal">
    <header class="barra-superior">
      <!-- Botón cambio de tema -->
      <button id="btn-cambiar-tema" class="btn-tema" aria-label="Cambiar a modo claro" title="Cambiar a modo claro">
        <i class="fas fa-sun tema-icono"></i>
        <span class="tema-label">Claro</span>
      </button>
      <div class="avatar-usuario" title="<?= limpiar($_SESSION['nombre']) ?>">
        <?= strtoupper(substr($_SESSION['nombre'], 0, 1)) ?>
      </div>
    </header>

    <div class="pagina-contenido">
      <h1 class="pagina-titulo">Panel del Instructor</h1>
      <p class="pagina-subtitulo">Seguimiento de aprendices — <?= limpiar($_SESSION['nombre']) ?></p>

      <!-- KPIs -->
      <div class="grid-estadisticas">
        <div class="tarjeta tarjeta-stat">
          <div class="stat-icono stat-icono-inst-azul">
            <i class="fas fa-users"></i>
          </div>
          <span class="stat-valor"><?= $totalAprendices ?></span>
          <span class="stat-etiqueta">Total Aprendices</span>
        </div>
        <div class="tarjeta tarjeta-stat">
          <div class="stat-icono stat-icono-inst-verde">
            <i class="fas fa-trophy"></i>
          </div>
          <span class="stat-valor"><?= $completaronAlgo ?></span>
          <span class="stat-etiqueta">Completaron RAP</span>
        </div>
        <div class="tarjeta tarjeta-stat">
          <div class="stat-icono stat-icono-inst-naranja">
            <i class="fas fa-star"></i>
          </div>
          <span class="stat-valor"><?= number_format($promedioQuiz, 1) ?>%</span>
          <span class="stat-etiqueta">Promedio Quiz</span>
        </div>
      </div>

      <!-- Tabla de aprendices -->
      <div class="tarjeta">
        <div class="lista-aprendices-header">
          <span class="lista-aprendices-titulo">
            <i class="fas fa-list-ul"></i>
            Lista de Aprendices
          </span>
          <a href="<?= PROYECTO_PATH ?>/instructor/exportar" class="btn btn-primario btn-exportar-csv">
            <i class="fas fa-download"></i> Exportar CSV
          </a>
        </div>

        <?php if (empty($aprendices)): ?>
          <p class="mensaje-vacio-tabla">
            No hay aprendices registrados aún.
          </p>
        <?php else: ?>
        <div class="tabla-container-scroll">
          <table class="tabla-aprendices" id="tabla-aprendices">
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
                $chipClase  = $avance >= 40 ? 'chip-activo' : 'chip-riesgo';
                $chipTexto  = $avance >= 40 ? 'En progreso' : 'En riesgo';
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
