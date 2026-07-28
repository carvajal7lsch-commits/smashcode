<!-- Barra lateral compartida del panel admin -->
<nav class="barra-lateral" aria-label="Navegación administrador">
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
      <div class="logo-subtitulo">PANEL ADMIN</div>
    </div>
  </div>

  <ul class="nav-lateral">
    <li><span class="nav-grupo-titulo">General</span></li>
    <li>
      <a href="<?= PROYECTO_PATH ?>/admin" class="nav-enlace <?= (strpos($_SERVER['REQUEST_URI'] ?? '', '/admin/usuarios') === false && strpos($_SERVER['REQUEST_URI'] ?? '', '/admin') !== false) ? 'activo' : '' ?>">
        <i class="fas fa-gauge-high nav-icono"></i><span>Dashboard</span>
      </a>
    </li>

    <li><span class="nav-grupo-titulo">Plataforma</span></li>
    <li>
      <a href="<?= PROYECTO_PATH ?>/admin/usuarios" class="nav-enlace <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/admin/usuarios') !== false ? 'activo' : '' ?>">
        <i class="fas fa-users-gear nav-icono"></i><span>Gestión de Usuarios</span>
        <span class="nav-badge"><?= $totalUsuarios ?? '' ?></span>
      </a>
    </li>
    <li>
      <a href="<?= PROYECTO_PATH ?>/admin/niveles" class="nav-enlace <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/admin/niveles') !== false ? 'activo' : '' ?>">
        <i class="fas fa-layer-group nav-icono"></i><span>Niveles</span>
      </a>
    </li>
    <li>
      <a href="<?= PROYECTO_PATH ?>/admin/programas" class="nav-enlace <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/admin/programas') !== false ? 'activo' : '' ?>">
        <i class="fas fa-graduation-cap nav-icono"></i><span>Programas</span>
      </a>
    </li>
    <li>
      <a href="<?= PROYECTO_PATH ?>/admin/raps" class="nav-enlace <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/admin/raps') !== false ? 'activo' : '' ?>">
        <i class="fas fa-file-lines nav-icono"></i><span>RAPs</span>
      </a>
    </li>
    <li>
      <a href="<?= PROYECTO_PATH ?>/admin/catalogos" class="nav-enlace <?= strpos($_SERVER['REQUEST_URI'] ?? '', '/admin/catalogos') !== false ? 'activo' : '' ?>">
        <i class="fas fa-tags nav-icono"></i><span>Catálogos</span>
      </a>
    </li>

    <li><span class="nav-grupo-titulo">Reportes</span></li>
    <li>
      <a href="<?= PROYECTO_PATH ?>/admin/analytics" class="nav-enlace">
        <i class="fas fa-chart-line nav-icono"></i><span>Analytics</span>
      </a>
    </li>
    <li>
      <a href="<?= PROYECTO_PATH ?>/admin/configuracion" class="nav-enlace">
        <i class="fas fa-gear nav-icono"></i><span>Configuración</span>
      </a>
    </li>
    <li>
      <a href="<?= PROYECTO_PATH ?>/logout" class="nav-enlace nav-enlace-logout">
        <i class="fas fa-right-from-bracket nav-icono"></i><span>Cerrar Sesión</span>
      </a>
    </li>
  </ul>
</nav>
