<?php
/**
 * Aviso de a qué aprendices está mirando el instructor (HU23).
 * Espera $sinPrograma y $programaNombre, que llegan desde InstructorController.
 */
?>
<?php if (!empty($sinPrograma)): ?>
  <div class="alerta-flash alerta-advertencia" role="status" style="margin-bottom:20px;">
    <i class="fas fa-circle-info"></i>
    No tienes un programa de formación asignado, así que ves a todos los aprendices de la plataforma. Pide a un administrador que te asigne tu programa para ver solo a los tuyos.
  </div>
<?php elseif (!empty($programaNombre)): ?>
  <p class="alcance-programa">
    <i class="fas fa-graduation-cap"></i>Aprendices del programa <strong><?= limpiar($programaNombre) ?></strong>
  </p>
<?php endif; ?>
