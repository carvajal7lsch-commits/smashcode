<!DOCTYPE html>
<html lang="es" data-theme="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Log de Actividad — <?= limpiar($usuario['nombre_completo']) ?> — SmashCode</title>
  <link rel="stylesheet" href="<?= PROYECTO_PATH ?>/assets/css/estilos.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <script>/* Aplicar tema guardado antes del paint */
  (function(){var t=localStorage.getItem('smashcode_tema');if(t)document.documentElement.setAttribute('data-theme',t);})();
  </script>
</head>
<body>
<div class="contenedor-app">

  <!-- Barra lateral -->
  <?php include __DIR__ . '/partials/sidebar.php'; ?>

  <main class="contenido-principal">
    <header class="barra-superior barra-superior-admin">
      <div class="breadcrumb-admin">
        <i class="fas fa-home breadcrumb-icon"></i>
        <a href="<?= PROYECTO_PATH ?>/admin" class="breadcrumb-current" style="text-decoration:none;">Dashboard</a>
        <i class="fas fa-chevron-right breadcrumb-separator"></i>
        <a href="<?= PROYECTO_PATH ?>/admin/usuarios" class="breadcrumb-current" style="text-decoration:none;">Gestión de Usuarios</a>
        <i class="fas fa-chevron-right breadcrumb-separator"></i>
        <span class="breadcrumb-link"><i class="fas fa-clock-rotate-left" style="color:var(--azul); margin-right:4px;"></i> Log de Actividad</span>
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

    <div class="pagina-contenido">

      <h1 class="pagina-titulo"><i class="fas fa-clock-rotate-left" style="color:var(--acento-oro);"></i> Historial de Actividad</h1>

      <!-- Perfil resumido -->
      <div class="perfil-card">
        <div class="perfil-avatar"><?= strtoupper(substr($usuario['nombre_completo'], 0, 1)) ?></div>
        <div>
          <div style="font-weight: 800; font-size: 1rem;"><?= limpiar($usuario['nombre_completo']) ?></div>
          <div style="font-size: 0.8rem; color: var(--texto-secundario);"><?= limpiar($usuario['correo']) ?></div>
          <div style="margin-top: 6px; display:flex; gap:8px; flex-wrap:wrap;">
            <span class="badge-rol badge-<?= $usuario['rol'] ?>"><?= ucfirst($usuario['rol']) ?></span>
            <span style="font-size:0.75rem; color:var(--acento-oro);"><i class="fas fa-bolt"></i> <?= number_format($usuario['xp_puntos']) ?> XP</span>
            <span style="font-size:0.75rem; color:var(--texto-tenue);"><i class="fas fa-calendar"></i> Desde <?= date('d/m/Y', strtotime($usuario['creado_en'])) ?></span>
          </div>
        </div>
        <div style="margin-left:auto;">
          <a href="<?= PROYECTO_PATH ?>/admin/usuarios/editar?id=<?= $usuario['id'] ?>" class="btn btn-verde">
            <i class="fas fa-pen-to-square"></i> Editar perfil
          </a>
        </div>
      </div>

      <!-- Línea de tiempo -->
      <div class="tarjeta">
        <div style="margin-bottom:16px;">
          <span style="font-size: var(--texto-sm); font-weight:600;">
            <i class="fas fa-stream" style="color:var(--verde-acento);"></i>
            Últimas <?= count($log) ?> interacciones
          </span>
        </div>

        <?php if (empty($log)): ?>
          <div style="text-align:center; padding: 40px 0; color: var(--texto-tenue);">
            <i class="fas fa-ghost" style="font-size:2.5rem; display:block; margin-bottom:10px;"></i>
            Este usuario aún no tiene actividad registrada.
          </div>
        <?php else: ?>
          <ul class="linea-tiempo">
            <?php foreach ($log as $item): ?>
            <li class="lt-item">
              <div class="lt-icono lt-<?= $item['tipo'] ?>">
                <i class="fas fa-<?= $item['tipo'] === 'quiz' ? 'clipboard-check' : 'dumbbell' ?>"></i>
              </div>
              <div class="lt-cuerpo">
                <div class="lt-titulo">
                  <?= limpiar($item['descripcion']) ?>
                  <span class="badge-<?= $item['estado'] ?>"><?= ucfirst($item['estado']) ?></span>
                </div>
                <div class="lt-detalle">
                  <i class="fas fa-info-circle" style="font-size:0.7rem;"></i>
                  <?= limpiar($item['detalle']) ?>
                </div>
                <div class="lt-fecha">
                  <i class="fas fa-clock" style="font-size:0.65rem;"></i>
                  <?= date('d/m/Y \a \l\a\s H:i', strtotime($item['fecha'])) ?>
                </div>
              </div>
            </li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </div><!-- /tarjeta -->

      <div style="margin-top: 20px;">
        <a href="<?= PROYECTO_PATH ?>/admin/usuarios" class="btn btn-gris">
          <i class="fas fa-arrow-left"></i> Volver a Usuarios
        </a>
      </div>
    </div><!-- /pagina-contenido -->
  </main>
</div>
</body>
<script src="<?= PROYECTO_PATH ?>/assets/js/tema.js"></script>
</html>
