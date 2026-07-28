<!DOCTYPE html>
<html lang="es" data-theme="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $modoEditar ? 'Editar' : 'Crear' ?> Usuario — Admin SmashCode</title>
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
        <span class="breadcrumb-link"><i class="fas fa-<?= $modoEditar ? 'user-pen' : 'user-plus' ?>" style="color:var(--azul); margin-right:4px;"></i> <?= $modoEditar ? 'Editar Usuario' : 'Nuevo Usuario' ?></span>
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
      <div style="max-width: 640px; margin: 0 auto;">

        <h1 class="pagina-titulo">
          <i class="fas fa-<?= $modoEditar ? 'user-pen' : 'user-plus' ?>" style="color:var(--verde-acento);"></i>
          <?= $modoEditar ? 'Editar Usuario' : 'Crear Nuevo Usuario' ?>
        </h1>

        <?php if ($error): ?>
          <div class="alerta alerta-error"><i class="fas fa-triangle-exclamation"></i> <?= $error ?></div>
        <?php endif; ?>

        <div class="tarjeta glass-panel" style="margin-top:20px; padding:0; overflow:hidden; border-radius: var(--radio);">
          <!-- Cabecera de la tarjeta con gradiente -->
          <div class="cabecera-tarjeta-premium cabecera-tarjeta-azul">
            <div class="cabecera-icono-wrap">
              <i class="fas fa-<?= $modoEditar ? 'user-pen' : 'user-plus' ?>"></i>
            </div>
            <div>
              <h2 style="font-size: 1.25rem; font-weight: 800; margin: 0; text-transform: uppercase; letter-spacing: 0.05em; color: #fff;">
                <?= $modoEditar ? 'Editar Usuario' : 'Nuevo Usuario' ?>
              </h2>
              <p style="font-size: 0.78rem; opacity: 0.85; margin: 4px 0 0 0; font-weight: 600;">
                <?= $modoEditar ? 'Modifica la información básica del usuario.' : 'Completa los campos para crear un nuevo usuario.' ?>
              </p>
            </div>
          </div>
          <form method="POST"
                action="<?= PROYECTO_PATH ?>/admin/usuarios/<?= $modoEditar ? 'actualizar' : 'guardar' ?>"
                novalidate style="padding: 32px;">
            <input type="hidden" name="csrf_token" value="<?= generarTokenCSRF() ?>">
            <?php if ($modoEditar): ?>
              <input type="hidden" name="id" value="<?= $usuario['id'] ?>">
            <?php endif; ?>

            <!-- Nombre completo -->
            <div class="grupo-campo">
              <label class="etiqueta-campo" for="nombre_completo">Nombre Completo *</label>
              <div class="contenedor-input">
                <i class="fas fa-user icono-input"></i>
                <input type="text" id="nombre_completo" name="nombre_completo" class="campo-input"
                       placeholder="Nombre y apellido" required
                       value="<?= limpiar($usuario['nombre_completo'] ?? '') ?>">
              </div>
            </div>

            <!-- Correo -->
            <div class="grupo-campo">
              <label class="etiqueta-campo" for="correo">Correo Electrónico *</label>
              <div class="contenedor-input">
                <i class="fas fa-envelope icono-input"></i>
                <input type="email" id="correo" name="correo" class="campo-input"
                       placeholder="usuario@dominio.com" required
                       value="<?= limpiar($usuario['correo'] ?? '') ?>">
              </div>
            </div>

            <!-- Rol -->
            <div class="grupo-campo">
              <label class="etiqueta-campo" for="rol">Rol *</label>
              <div class="contenedor-input">
                <i class="fas fa-shield-halved icono-input"></i>
                <select id="rol" name="rol" class="campo-input" style="cursor:pointer;">
                  <option value="aprendiz"   <?= ($usuario['rol'] ?? 'aprendiz') === 'aprendiz'   ? 'selected' : '' ?>>Aprendiz</option>
                  <option value="instructor" <?= ($usuario['rol'] ?? '') === 'instructor' ? 'selected' : '' ?>>Instructor</option>
                  <option value="admin"      <?= ($usuario['rol'] ?? '') === 'admin'      ? 'selected' : '' ?>>Administrador</option>
                </select>
              </div>
            </div>

            <!-- Ficha SENA -->
            <div class="grupo-campo">
              <label class="etiqueta-campo" for="ficha_sena">Ficha SENA <span style="color:var(--texto-tenue); font-weight:400;">(Opcional)</span></label>
              <div class="contenedor-input">
                <i class="fas fa-id-card icono-input"></i>
                <input type="text" id="ficha_sena" name="ficha_sena" class="campo-input"
                       placeholder="Ej: 2877650" maxlength="20"
                       value="<?= limpiar($usuario['ficha_sena'] ?? '') ?>">
              </div>
            </div>

            <!-- Programa de Formación -->
            <div class="grupo-campo">
              <label class="etiqueta-campo" for="programa_id">
                Programa de Formación <span style="color:var(--texto-tenue); font-weight:400;">(Opcional)</span>
              </label>
              <div class="contenedor-input">
                <i class="fas fa-graduation-cap icono-input"></i>
                <select id="programa_id" name="programa_id" class="campo-input" style="padding-left:38px; cursor:pointer;">
                  <option value="">— Sin programa asignado —</option>
                  <?php foreach ($programas as $p): ?>
                    <option value="<?= $p['id'] ?>"
                      <?= ($usuario['programa_id'] ?? '') === $p['id'] ? 'selected' : '' ?>>
                      <?= limpiar($p['nombre']) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
              <span class="ayuda-campo">Programa de formación SENA al que pertenece este usuario.</span>
            </div>

            <?php if (!$modoEditar): ?>
            <!-- Contraseña (solo al crear) -->
            <div class="grupo-campo">
              <label class="etiqueta-campo" for="contrasena">Contraseña *</label>
              <div class="contenedor-input">
                <i class="fas fa-lock icono-input"></i>
                <input type="password" id="contrasena" name="contrasena" class="campo-input"
                       placeholder="Mín. 8 caracteres, 1 mayúscula, 1 número" required>
              </div>
              <span class="ayuda-campo">Debe incluir al menos 1 mayúscula y 1 número.</span>
            </div>
            <?php else: ?>
            <div class="alerta" style="background: rgba(46,134,193,0.08); border:1px solid rgba(46,134,193,0.3); color: var(--azul-claro); font-size: var(--texto-xs); padding: 10px 14px; border-radius: var(--radio-sm); margin-bottom:16px;">
              <i class="fas fa-info-circle"></i>
              Al editar, la contraseña <strong>no se modifica</strong>. Para cambiarla, el usuario debe usar "Recuperar contraseña".
            </div>
            <?php endif; ?>

            <div style="display:flex; gap: 10px; margin-top: 8px;">
              <button type="submit" class="btn btn-verde">
                <i class="fas fa-<?= $modoEditar ? 'floppy-disk' : 'user-plus' ?>"></i>
                <?= $modoEditar ? 'Guardar Cambios' : 'Crear Usuario' ?>
              </button>
              <a href="<?= PROYECTO_PATH ?>/admin/usuarios" class="btn btn-gris">
                <i class="fas fa-arrow-left"></i> Cancelar
              </a>
            </div>
          </form>
        </div><!-- /tarjeta -->
      </div>
    </div><!-- /pagina-contenido -->
  </main>
</div>
</body>
<script src="<?= PROYECTO_PATH ?>/assets/js/tema.js"></script>
</html>
