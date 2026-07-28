<?php
// Asegurar variables básicas y de sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$rol = $_SESSION['rol'] ?? null;
$nombre = $_SESSION['nombre'] ?? 'Usuario';
$nombreCorto = explode(' ', $nombre)[0];
$avatar = strtoupper(substr($nombre, 0, 1));
$proyectoPath = defined('PROYECTO_PATH') ? PROYECTO_PATH : '';

if (!function_exists('limpiar')) {
    function limpiar($str) {
        return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
    }
}
?>
<!DOCTYPE html>
<html lang="es" data-theme="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>404 Página No Encontrada — SmashCode</title>
  <link rel="stylesheet" href="<?= $proyectoPath ?>/assets/css/estilos.css?v=<?= time() ?>">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <script>/* Aplicar tema guardado antes del paint */
  (function(){var t=localStorage.getItem('smashcode_tema');if(t)document.documentElement.setAttribute('data-theme',t);})();
  </script>
  <style>
    /* Estilos del área de error 404 premium */
    .caja-error-404 {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      text-align: center;
      padding: 60px 24px;
      background: var(--blanco);
      border: var(--borde);
      border-radius: 20px;
      max-width: 600px;
      margin: 40px auto 0;
    }
    .titulo-404 {
      font-size: 5rem;
      font-weight: 900;
      color: #FF4B4B;
      line-height: 1;
      margin: 0 0 10px 0;
      letter-spacing: -2px;
    }
    .subtitulo-404 {
      font-size: 1.5rem;
      font-weight: 800;
      color: var(--texto-principal);
      margin: 0 0 12px 0;
    }
    .texto-404 {
      font-size: 0.95rem;
      color: var(--texto-secundario);
      max-width: 420px;
      margin: 0 0 24px 0;
      line-height: 1.6;
    }
  </style>
</head>
<body class="bg-mesh">

<?php if ($rol === 'admin'): ?>
<div class="contenedor-app">
  <!-- Sidebar Admin -->
  <?php 
  $sidebarPath = dirname(__DIR__) . '/admin/partials/sidebar.php';
  if (file_exists($sidebarPath)) {
      include $sidebarPath;
  }
  ?>

  <!-- Contenido principal -->
  <main class="contenido-principal">
    <header class="barra-superior" style="background: transparent; border: none; box-shadow: none; margin: 0; padding: 24px 24px 10px; z-index: 90; position:relative; min-height:60px;">
      <div style="display:flex; align-items:center; gap:8px; font-size:0.82rem; font-weight:600; color:var(--texto-tenue);">
        <i class="fas fa-home" style="font-size:0.75rem;"></i>
        <span style="color:var(--texto-secundario); font-weight:700;">Dashboard</span>
        <i class="fas fa-chevron-right" style="font-size:0.65rem;"></i>
        <span style="color:var(--texto-tenue);">Error 404</span>
      </div>
      <div style="margin-left: auto; display:flex; align-items:center; gap:16px;">
        <!-- Botón cambio de tema -->
        <button id="btn-cambiar-tema" class="btn-tema" aria-label="Cambiar a modo claro" title="Cambiar a modo claro">
          <i class="fas fa-sun tema-icono"></i>
          <span class="tema-label">Claro</span>
        </button>
        <div class="avatar-usuario" style="border: 2px solid var(--verde); background: linear-gradient(135deg, var(--verde), var(--azul)); font-weight: 800; cursor: default;" title="<?= limpiar($nombre) ?>">
          <?= $avatar ?>
        </div>
      </div>
    </header>

    <div class="pagina-contenido" style="padding: 10px 24px 32px;">
      <div class="caja-error-404">
        <!-- SVG Mascota Confundida -->
        <svg viewBox="0 0 100 100" width="120" height="120" xmlns="http://www.w3.org/2000/svg" style="margin-bottom:16px;">
          <ellipse cx="50" cy="82" rx="22" ry="5" fill="#000" opacity="0.3" />
          <ellipse cx="38" cy="80" rx="7" ry="4" fill="#FF9600" />
          <ellipse cx="62" cy="80" rx="7" ry="4" fill="#FF9600" />
          <rect x="26" y="18" width="48" height="58" rx="24" fill="#2B3E46" />
          <path d="M 26 36 C 17 40 17 54 26 60 Z" fill="#2B3E46" />
          <path d="M 74 36 C 83 40 83 54 74 60 Z" fill="#2B3E46" />
          <ellipse cx="50" cy="52" rx="17" ry="20" fill="#FFFFFF" />
          
          <ellipse cx="41" cy="36" rx="9" ry="9" fill="#FFFFFF" />
          <text x="37" y="41" font-size="13" font-family="sans-serif" font-weight="900" fill="#111B1E">?</text>
          
          <ellipse cx="59" cy="36" rx="9" ry="9" fill="#FFFFFF" />
          <text x="55" y="41" font-size="13" font-family="sans-serif" font-weight="900" fill="#111B1E">?</text>
          
          <path d="M 46 47 Q 50 42 54 47 Z" fill="#FF9600" stroke="#FF9600" stroke-width="1.5" />
          
          <rect x="36" y="10" width="28" height="8" rx="3" fill="#FFFFFF" />
          <rect x="47" y="6" width="6" height="14" rx="2" fill="#FF4B4B" />
          <rect x="36" y="12" width="28" height="3" rx="1.5" fill="#E5E5E5" />
        </svg>

        <h1 class="titulo-404">404</h1>
        <h2 class="subtitulo-404">¿Te has perdido, Administrador?</h2>
        <p class="texto-404">
          La ruta a la que intentas acceder no existe o aún no ha sido creada en el panel de administración.
        </p>
        <a href="<?= $proyectoPath ?>/admin" class="btn-premium btn-premium-verde" style="padding: 12px 24px; font-weight:700; border-radius:12px; display:inline-flex; align-items:center; gap:8px; text-decoration:none; cursor:pointer;">
          <i class="fas fa-house"></i> Volver al Dashboard
        </a>
      </div>
    </div>
  </main>
</div>

<?php elseif ($rol === 'instructor'): ?>
<div class="contenedor-app">
  <!-- Sidebar Instructor -->
  <nav class="barra-lateral" aria-label="Navegación instructor">
    <div class="logo-app">
      <div class="logo-icono">
        <svg viewBox="0 0 100 100" width="40" height="40" xmlns="http://www.w3.org/2000/svg" style="display: block;">
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
        <div style="font-size: 0.62rem; color: #52656D; letter-spacing: 1.5px; font-weight: 800; padding-left: 2px; margin-top: 2px;">INSTRUCTOR</div>
      </div>
    </div>
    <ul class="nav-lateral">
      <li>
        <a href="<?= $proyectoPath ?>/instructor" class="nav-enlace">
          <i class="fas fa-gauge-high nav-icono"></i><span>Dashboard</span>
        </a>
      </li>
      <li>
        <a href="<?= $proyectoPath ?>/instructor/aprendices" class="nav-enlace">
          <i class="fas fa-users nav-icono"></i><span>Mis Aprendices</span>
        </a>
      </li>
      <li>
        <a href="<?= $proyectoPath ?>/instructor/resultados" class="nav-enlace">
          <i class="fas fa-clipboard-list nav-icono"></i><span>Resultados Quiz</span>
        </a>
      </li>
      <li>
        <a href="<?= $proyectoPath ?>/instructor/niveles" class="nav-enlace">
          <i class="fas fa-layer-group nav-icono"></i><span>Niveles</span>
        </a>
      </li>
      <li>
        <a href="<?= $proyectoPath ?>/instructor/raps" class="nav-enlace">
          <i class="fas fa-file-lines nav-icono"></i><span>RAPs</span>
        </a>
      </li>
      <li>
        <a href="<?= $proyectoPath ?>/instructor/exportar" class="nav-enlace">
          <i class="fas fa-file-csv nav-icono"></i><span>Exportar CSV</span>
        </a>
      </li>
      <li>
        <a href="<?= $proyectoPath ?>/logout" class="nav-enlace" style="color:var(--rojo);">
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
      <div class="avatar-usuario" title="<?= limpiar($nombre) ?>">
        <?= $avatar ?>
      </div>
    </header>

    <div class="pagina-contenido" style="padding: 10px 24px 32px;">
      <div class="caja-error-404">
        <!-- SVG Mascota Confundida -->
        <svg viewBox="0 0 100 100" width="120" height="120" xmlns="http://www.w3.org/2000/svg" style="margin-bottom:16px;">
          <ellipse cx="50" cy="82" rx="22" ry="5" fill="#000" opacity="0.3" />
          <ellipse cx="38" cy="80" rx="7" ry="4" fill="#FF9600" />
          <ellipse cx="62" cy="80" rx="7" ry="4" fill="#FF9600" />
          <rect x="26" y="18" width="48" height="58" rx="24" fill="#2B3E46" />
          <path d="M 26 36 C 17 40 17 54 26 60 Z" fill="#2B3E46" />
          <path d="M 74 36 C 83 40 83 54 74 60 Z" fill="#2B3E46" />
          <ellipse cx="50" cy="52" rx="17" ry="20" fill="#FFFFFF" />
          
          <ellipse cx="41" cy="36" rx="9" ry="9" fill="#FFFFFF" />
          <text x="37" y="41" font-size="13" font-family="sans-serif" font-weight="900" fill="#111B1E">?</text>
          
          <ellipse cx="59" cy="36" rx="9" ry="9" fill="#FFFFFF" />
          <text x="55" y="41" font-size="13" font-family="sans-serif" font-weight="900" fill="#111B1E">?</text>
          
          <path d="M 46 47 Q 50 42 54 47 Z" fill="#FF9600" stroke="#FF9600" stroke-width="1.5" />
          
          <rect x="36" y="10" width="28" height="8" rx="3" fill="#FFFFFF" />
          <rect x="47" y="6" width="6" height="14" rx="2" fill="#FF4B4B" />
          <rect x="36" y="12" width="28" height="3" rx="1.5" fill="#E5E5E5" />
        </svg>

        <h1 class="titulo-404">404</h1>
        <h2 class="subtitulo-404">Ruta no disponible</h2>
        <p class="texto-404">
          Estimado Instructor, la sección que está intentando cargar no existe o no se encuentra habilitada temporalmente.
        </p>
        <a href="<?= $proyectoPath ?>/instructor" class="btn-premium btn-premium-verde" style="padding: 12px 24px; font-weight:700; border-radius:12px; display:inline-flex; align-items:center; gap:8px; text-decoration:none; cursor:pointer;">
          <i class="fas fa-house"></i> Volver al Dashboard
        </a>
      </div>
    </div>
  </main>
</div>

<?php elseif ($rol === 'aprendiz'): ?>
<div class="contenedor-app">
  <!-- Sidebar Aprendiz -->
  <nav class="barra-lateral" aria-label="Navegación principal">
    <div class="logo-app">
      <div class="logo-icono">
        <svg viewBox="0 0 100 100" width="40" height="40" xmlns="http://www.w3.org/2000/svg" style="display: block;">
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
        <div style="font-size: 0.62rem; color: #52656D; letter-spacing: 1.5px; font-weight: 800; padding-left: 2px; margin-top: 2px;">APRENDIZ</div>
      </div>
    </div>
    <ul class="nav-lateral">
      <li>
        <a href="<?= $proyectoPath ?>/" class="nav-enlace">
          <i class="fas fa-book-open nav-icono"></i><span>Aprender</span>
        </a>
      </li>
      <li>
        <a href="<?= $proyectoPath ?>/aprendiz/vocabulario" class="nav-enlace">
          <i class="fas fa-spell-check nav-icono"></i><span>Vocabulario</span>
        </a>
      </li>
      <li>
        <a href="<?= $proyectoPath ?>/logout" class="nav-enlace" style="color:var(--rojo);">
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
      <div class="avatar-usuario" title="<?= limpiar($nombre) ?>">
        <?= $avatar ?>
      </div>
    </header>

    <div class="pagina-contenido" style="padding: 10px 24px 32px;">
      <div class="caja-error-404">
        <!-- SVG Mascota Confundida -->
        <svg viewBox="0 0 100 100" width="120" height="120" xmlns="http://www.w3.org/2000/svg" style="margin-bottom:16px;">
          <ellipse cx="50" cy="82" rx="22" ry="5" fill="#000" opacity="0.3" />
          <ellipse cx="38" cy="80" rx="7" ry="4" fill="#FF9600" />
          <ellipse cx="62" cy="80" rx="7" ry="4" fill="#FF9600" />
          <rect x="26" y="18" width="48" height="58" rx="24" fill="#2B3E46" />
          <path d="M 26 36 C 17 40 17 54 26 60 Z" fill="#2B3E46" />
          <path d="M 74 36 C 83 40 83 54 74 60 Z" fill="#2B3E46" />
          <ellipse cx="50" cy="52" rx="17" ry="20" fill="#FFFFFF" />
          
          <ellipse cx="41" cy="36" rx="9" ry="9" fill="#FFFFFF" />
          <text x="37" y="41" font-size="13" font-family="sans-serif" font-weight="900" fill="#111B1E">?</text>
          
          <ellipse cx="59" cy="36" rx="9" ry="9" fill="#FFFFFF" />
          <text x="55" y="41" font-size="13" font-family="sans-serif" font-weight="900" fill="#111B1E">?</text>
          
          <path d="M 46 47 Q 50 42 54 47 Z" fill="#FF9600" stroke="#FF9600" stroke-width="1.5" />
          
          <rect x="36" y="10" width="28" height="8" rx="3" fill="#FFFFFF" />
          <rect x="47" y="6" width="6" height="14" rx="2" fill="#FF4B4B" />
          <rect x="36" y="12" width="28" height="3" rx="1.5" fill="#E5E5E5" />
        </svg>

        <h1 class="titulo-404">404</h1>
        <h2 class="subtitulo-404">¡Ups! Ruta equivocada</h2>
        <p class="texto-404">
          Hola Aprendiz, la lección o sección a la que intentas ingresar no existe todavía en tu ruta de aprendizaje.
        </p>
        <a href="<?= $proyectoPath ?>/" class="btn-premium btn-premium-verde" style="padding: 12px 24px; font-weight:700; border-radius:12px; display:inline-flex; align-items:center; gap:8px; text-decoration:none; cursor:pointer;">
          <i class="fas fa-house"></i> Volver al Inicio
        </a>
      </div>
    </div>
  </main>
</div>

<?php else: ?>
<!-- Botón flotante de tema -->
<button id="btn-cambiar-tema" class="btn-tema" aria-label="Cambiar a modo claro" title="Cambiar a modo claro"
  style="position:fixed; top:16px; right:20px; z-index:9999;">
  <i class="fas fa-sun tema-icono"></i>
  <span class="tema-label">Claro</span>
</button>
<!-- Standalone premium 404 para usuarios no autenticados -->
<main class="pagina-auth" style="display:flex; align-items:center; justify-content:center; min-height:100vh;">
  <div class="caja-error-404" style="background: var(--blanco); border-color: var(--borde-sutil);">
    <!-- SVG Mascota Confundida -->
    <svg viewBox="0 0 100 100" width="120" height="120" xmlns="http://www.w3.org/2000/svg" style="margin-bottom:16px;">
      <ellipse cx="50" cy="82" rx="22" ry="5" fill="#000" opacity="0.3" />
      <ellipse cx="38" cy="80" rx="7" ry="4" fill="#FF9600" />
      <ellipse cx="62" cy="80" rx="7" ry="4" fill="#FF9600" />
      <rect x="26" y="18" width="48" height="58" rx="24" fill="#2B3E46" />
      <path d="M 26 36 C 17 40 17 54 26 60 Z" fill="#2B3E46" />
      <path d="M 74 36 C 83 40 83 54 74 60 Z" fill="#2B3E46" />
      <ellipse cx="50" cy="52" rx="17" ry="20" fill="#FFFFFF" />
      
      <ellipse cx="41" cy="36" rx="9" ry="9" fill="#FFFFFF" />
      <text x="37" y="41" font-size="13" font-family="sans-serif" font-weight="900" fill="#111B1E">?</text>
      
      <ellipse cx="59" cy="36" rx="9" ry="9" fill="#FFFFFF" />
      <text x="55" y="41" font-size="13" font-family="sans-serif" font-weight="900" fill="#111B1E">?</text>
      
      <path d="M 46 47 Q 50 42 54 47 Z" fill="#FF9600" stroke="#FF9600" stroke-width="1.5" />
      
      <rect x="36" y="10" width="28" height="8" rx="3" fill="#FFFFFF" />
      <rect x="47" y="6" width="6" height="14" rx="2" fill="#FF4B4B" />
      <rect x="36" y="12" width="28" height="3" rx="1.5" fill="#E5E5E5" />
    </svg>

    <h1 class="titulo-404">404</h1>
    <h2 class="subtitulo-404">Página No Encontrada</h2>
    <p class="texto-404">
      La dirección que has ingresado no existe en SmashCode.
    </p>
    <a href="<?= $proyectoPath ?>/" class="btn-premium btn-premium-verde" style="padding: 12px 24px; font-weight:700; border-radius:12px; display:inline-flex; align-items:center; gap:8px; text-decoration:none; cursor:pointer;">
      <i class="fas fa-right-to-bracket"></i> Volver a Ingresar
    </a>
  </div>
</main>
<?php endif; ?>
<script src="<?= $proyectoPath ?>/assets/js/tema.js"></script>
</body>
</html>
