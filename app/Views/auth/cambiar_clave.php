<!DOCTYPE html>
<html lang="es" data-theme="dark">
<head>
  <meta charset="UTF-8">
  <link rel="icon" type="image/svg+xml" href="<?= PROYECTO_PATH ?>/assets/img/favicon.svg">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Cambiar Contraseña — SmashCode</title>
  <link rel="stylesheet" href="<?= PROYECTO_PATH ?>/assets/css/estilos.css?v=<?= time() ?>">
  <link rel="stylesheet" href="<?= PROYECTO_PATH ?>/assets/css/auth.css?v=<?= time() ?>">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <script>/* Aplicar tema guardado antes del paint */
  (function(){var t=localStorage.getItem('smashcode_tema');if(t)document.documentElement.setAttribute('data-theme',t);})();
  </script>
</head>
<body>
<!-- Botón flotante de tema -->
<button id="btn-cambiar-tema" class="btn-tema btn-tema-flotante" aria-label="Cambiar a modo claro" title="Cambiar a modo claro">
  <i class="fas fa-sun tema-icono"></i>
  <span class="tema-label">Claro</span>
</button>
<main class="pagina-auth">
  <div class="contenedor-auth animar-entrada auth-container-narrow">
    <div class="panel-auth-der auth-panel-rounded auth-panel-padded">

      <div class="icono-clave"><i class="fas fa-key"></i></div>

      <h1 class="auth-title-large text-center">Cambiar Contraseña</h1>
      <p class="auth-desc-text text-center">
        Por seguridad, debes establecer una <strong>nueva contraseña personal</strong> antes de continuar.
        Esta contraseña temporal no podrás volver a usarla.
      </p>

      <?php if ($error): ?>
        <div class="alerta alerta-error"><i class="fas fa-triangle-exclamation"></i> <?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

    <form method="POST" action="<?= PROYECTO_PATH ?>/cambiar-clave/guardar" novalidate id="form-clave">
      <input type="hidden" name="csrf_token" value="<?= $csrf ?>">

      <div class="grupo-campo">
        <label class="etiqueta-campo" for="contrasena">Nueva Contraseña *</label>
        <div class="contenedor-input">
          <i class="fas fa-lock icono-input"></i>
          <input type="password" id="contrasena" name="contrasena" class="campo-input"
                 placeholder="Mín. 8 chars, 1 mayúscula, 1 número" required
                 oninput="evaluarFuerza(this.value)">
        </div>
        <div class="indicador-fuerza" id="indicador-fuerza"></div>
        <span class="ayuda-campo" id="ayuda-fuerza">Ingresa tu nueva contraseña.</span>
      </div>

      <div class="grupo-campo">
        <label class="etiqueta-campo" for="contrasena_confirmar">Confirmar Contraseña *</label>
        <div class="contenedor-input">
          <i class="fas fa-lock icono-input"></i>
          <input type="password" id="contrasena_confirmar" name="contrasena_confirmar" class="campo-input"
                 placeholder="Repite la contraseña" required>
        </div>
      </div>

      <ul class="auth-req-list">
        <li>Mínimo 8 caracteres</li>
        <li>Al menos 1 letra mayúscula</li>
        <li>Al menos 1 número</li>
        <li>Las contraseñas deben coincidir</li>
      </ul>

      <button type="submit" class="btn btn-verde btn-block">
        <i class="fas fa-floppy-disk"></i> Guardar y Continuar
      </button>
    </form>

    </div>
  </div>
</main>

<script>
function evaluarFuerza(clave) {
  const indicador = document.getElementById('indicador-fuerza');
  const ayuda     = document.getElementById('ayuda-fuerza');
  let puntos = 0;
  if (clave.length >= 8)          puntos++;
  if (/[A-Z]/.test(clave))        puntos++;
  if (/[0-9]/.test(clave))        puntos++;
  if (/[^A-Za-z0-9]/.test(clave)) puntos++;

  indicador.className = 'indicador-fuerza';
  if (puntos <= 1) {
    indicador.classList.add('fuerza-debil');
    ayuda.textContent = 'Contraseña débil — agrega mayúsculas y números.';
  } else if (puntos === 2 || puntos === 3) {
    indicador.classList.add('fuerza-media');
    ayuda.textContent = 'Contraseña aceptable — agrega un símbolo para mejorarla.';
  } else {
    indicador.classList.add('fuerza-fuerte');
    ayuda.textContent = 'Contraseña fuerte — ¡excelente!';
  }
}
</script>
<script src="<?= PROYECTO_PATH ?>/assets/js/tema.js"></script>
</body>
</html>
