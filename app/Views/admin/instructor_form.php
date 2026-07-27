
<!DOCTYPE html>
<html lang="es" data-theme="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Nuevo Instructor — Admin SmashCode</title>
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
        <span class="breadcrumb-link"><i class="fas fa-chalkboard-teacher" style="color:var(--azul); margin-right:4px;"></i> Nuevo Instructor</span>
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
      <div style="max-width: 620px; margin: 0 auto;">

        <?php if ($error): ?>
          <div class="alerta alerta-error" style="margin-top: 12px;"><i class="fas fa-triangle-exclamation"></i> <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <!-- Aviso del flujo -->
        <div class="alerta alerta-info" style="margin-bottom: 24px; margin-top: 12px;">
          <i class="fas fa-circle-info" style="font-size:1.1rem;"></i>
          <div>
            <strong>¿Cómo funciona?</strong><br>
            Al crear la cuenta, el sistema genera una contraseña aleatoria y segura, la guarda hasheada en la base de datos y
            envía un correo al instructor con sus credenciales. En su primer login se le pedirá que cambie la contraseña.
          </div>
        </div>

        <div class="tarjeta glass-panel" style="margin-top: 0; padding: 0; overflow: hidden; border-radius: var(--radio);">
          <!-- Cabecera de la tarjeta con gradiente -->
          <div class="cabecera-tarjeta-premium cabecera-tarjeta-azul">
            <div class="cabecera-icono-wrap">
              <i class="fas fa-chalkboard-teacher"></i>
            </div>
            <div>
              <h2 style="font-size: 1.25rem; font-weight: 800; margin: 0; text-transform: uppercase; letter-spacing: 0.05em; color: #fff;">
                Crear Instructor
              </h2>
              <p style="font-size: 0.78rem; opacity: 0.85; margin: 4px 0 0 0; font-weight: 600;">
                Se generará y enviará una contraseña temporal por correo electrónico.
              </p>
            </div>
          </div>
          <form method="POST" action="<?= PROYECTO_PATH ?>/admin/usuarios/instructor/guardar" novalidate style="padding: 32px;">
            <input type="hidden" name="csrf_token" value="<?= generarTokenCSRF() ?>">

            <!-- Nombre -->
            <div class="grupo-campo">
              <label class="etiqueta-campo" for="nombre_completo">Nombre Completo *</label>
              <div class="contenedor-input">
                <i class="fas fa-user icono-input"></i>
                <input type="text" id="nombre_completo" name="nombre_completo" class="campo-input"
                       placeholder="Ej: Carlos Andrés Gómez" required
                       value="<?= htmlspecialchars($datos['nombre'] ?? '') ?>">
              </div>
            </div>

            <!-- Correo -->
            <div class="grupo-campo">
              <label class="etiqueta-campo" for="correo">Correo Electrónico Institucional *</label>
              <div class="contenedor-input">
                <i class="fas fa-envelope icono-input"></i>
                <input type="email" id="correo" name="correo" class="campo-input"
                       placeholder="instructor@sena.edu.co" required
                       value="<?= htmlspecialchars($datos['correo'] ?? '') ?>">
              </div>
              <span class="ayuda-campo">Se enviará la contraseña temporal a este correo.</span>
            </div>

            <!-- Ficha SENA -->
            <div class="grupo-campo">
              <label class="etiqueta-campo" for="ficha_sena">
                Ficha SENA <span style="color:var(--gris-medio); font-weight:400;">(Opcional)</span>
              </label>
              <div class="contenedor-input">
                <i class="fas fa-id-card icono-input"></i>
                <input type="text" id="ficha_sena" name="ficha_sena" class="campo-input"
                       placeholder="Ej: 2877650" maxlength="20"
                       value="<?= htmlspecialchars($datos['ficha'] ?? '') ?>">
              </div>
            </div>

            <!-- Programa Asignado -->
            <div class="grupo-campo">
              <label class="etiqueta-campo" for="programa_id">
                Programa Asignado <span style="color:var(--gris-medio); font-weight:400;">(Opcional)</span>
              </label>
              <div class="contenedor-input">
                <i class="fas fa-graduation-cap icono-input"></i>
                <select id="programa_id" name="programa_id" class="campo-input" style="padding-left:38px; cursor:pointer;">
                  <option value="">— Sin programa asignado —</option>
                  <?php foreach ($programas as $p): ?>
                    <option value="<?= limpiar($p['id']) ?>"
                      <?= ($datos['programaId'] ?? '') === $p['id'] ? 'selected' : '' ?>>
                      <?= limpiar($p['nombre']) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
              <span class="ayuda-campo">Programa de formación SENA al que el instructor estará asignado.</span>
            </div>

            <!-- Info contraseña -->
            <div style="background: rgba(88,204,2,0.08); border: 2px solid rgba(88,204,2,0.25); border-radius: var(--radio-sm); padding: 14px 18px; margin-bottom: 20px; font-size: 0.82rem; color: var(--gris-texto);">
              <i class="fas fa-key" style="color:var(--verde); margin-right:6px;"></i>
              <strong>Contraseña temporal:</strong> Se generará automáticamente una contraseña segura de 12 caracteres
              (mayúsculas, números y símbolos) y se enviará al correo del instructor.
            </div>

            <div style="display: flex; gap: 10px; margin-top: 8px;">
              <button type="submit" class="btn btn-azul" style="flex:1;">
                <i class="fas fa-paper-plane"></i> Crear y Enviar Credenciales
              </button>
              <a href="<?= PROYECTO_PATH ?>/admin/usuarios" class="btn btn-blanco" style="flex-shrink:0; width:auto; padding: 13px 20px;">
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
