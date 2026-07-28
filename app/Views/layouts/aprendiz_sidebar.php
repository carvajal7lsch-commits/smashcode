  <!-- ============ BARRA LATERAL ============ -->
  <nav class="barra-lateral" aria-label="Navegación principal">
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
        <div class="logo-rol-texto">APRENDIZ</div>
      </div>
    </div>

    <?php $current_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH); ?>
    
    <ul class="nav-lateral">
      <li>
        <a href="<?= PROYECTO_PATH ?>/" class="nav-enlace <?= ($current_uri == PROYECTO_PATH.'/') ? 'activo' : '' ?>">
          <i class="fas fa-book-open nav-icono"></i>
          <span>Aprender</span>
        </a>
      </li>

      <li>
        <a href="<?= PROYECTO_PATH ?>/aprendiz/glosario" class="nav-enlace <?= strpos($current_uri, '/aprendiz/glosario') !== false ? 'activo' : '' ?>">
          <i class="fas fa-book-medical nav-icono"></i>
          <span>Glosario</span>
        </a>
      </li>
      <?php if (isset($autenticado) && $autenticado || estaAutenticado()): ?>
      <li>
        <a href="<?= PROYECTO_PATH ?>/aprendiz/perfil" class="nav-enlace <?= strpos($current_uri, '/aprendiz/perfil') !== false ? 'activo' : '' ?>">
          <i class="fas fa-user nav-icono"></i>
          <span>Perfil</span>
        </a>
      </li>
      <li>
        <a href="<?= PROYECTO_PATH ?>/logout" class="nav-enlace nav-enlace-salir">
          <i class="fas fa-right-from-bracket nav-icono"></i>
          <span>Cerrar Sesión</span>
        </a>
      </li>
      <?php endif; ?>
    </ul>

    <!-- Botón cambio de tema al fondo de la barra lateral -->
    <div class="sidebar-footer">
      <button id="btn-cambiar-tema" class="btn-tema btn-tema-sidebar" aria-label="Cambiar a modo claro" title="Cambiar a modo claro">
        <i class="fas fa-sun tema-icono"></i>
        <span class="tema-label">Claro</span>
      </button>
    </div>
  </nav>
