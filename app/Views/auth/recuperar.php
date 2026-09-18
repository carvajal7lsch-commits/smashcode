<!DOCTYPE html>
<html lang="es" data-theme="dark">
<head>
  <meta charset="UTF-8">
  <link rel="icon" type="image/svg+xml" href="<?= PROYECTO_PATH ?>/assets/img/favicon.svg">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Recuperar Contraseña — SmashCode</title>
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
    <div class="panel-auth-der auth-panel-rounded">
      <h2 class="titulo-formulario text-center">Recuperar Contraseña</h2>
      
      <?php if ($error): ?>
        <div class="alerta alerta-error"><i class="fas fa-circle-exclamation"></i><?= $error ?></div>
      <?php endif; ?>
      <?php if ($exito): ?>
        <div class="alerta alerta-exito"><i class="fas fa-circle-check"></i><?= $exito ?></div>
        <div class="text-center mt-20">
          <a href="<?= PROYECTO_PATH ?>/login" class="btn btn-verde btn-block"><i class="fas fa-arrow-left"></i> Volver a Iniciar Sesión</a>
        </div>
      <?php else: ?>
        <p class="subtitulo-formulario text-center">Ingresa tu correo institucional y te enviaremos un enlace seguro para restablecer tu contraseña.</p>
        <form method="POST" action="<?= PROYECTO_PATH ?>/recuperar/enviar" novalidate>
          <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
          
          <div class="grupo-campo">
            <label class="etiqueta-campo" for="correo-recuperacion">Correo Institucional</label>
            <div class="contenedor-input">
              <i class="fas fa-envelope icono-input"></i>
              <input type="email" id="correo-recuperacion" name="correo" class="campo-input" placeholder="nombre@sena.edu.co" required>
            </div>
          </div>

          <button type="submit" class="btn btn-verde btn-block mt-10">
            <i class="fas fa-paper-plane"></i> Enviar Enlace de Recuperación
          </button>
          
          <div class="text-center mt-20">
            <a href="<?= PROYECTO_PATH ?>/login" class="auth-link-recuperar"><i class="fas fa-arrow-left"></i> Volver a Iniciar Sesión</a>
          </div>
        </form>
      <?php endif; ?>
    </div>
  </div>
</main>
<script src="<?= PROYECTO_PATH ?>/assets/js/tema.js"></script>
</body>
</html>
