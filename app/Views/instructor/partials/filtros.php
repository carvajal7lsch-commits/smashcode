<?php
/**
 * Filtros de nivel, RAP y estado compartidos por "Mis Aprendices" y
 * "Resultados Quiz" (HU23).
 *
 * Espera: $accionFiltros, $nivelesConRaps, $filtroNivel, $filtroRap,
 * $filtroEstado y $opcionesEstado (valor => etiqueta).
 *
 * $nivelesConRaps trae una fila por RAP. Aquí se agrupa por módulo para que
 * cada módulo salga una sola vez en el selector de nivel —un módulo con dos
 * RAPs aparecía repetido— y los RAPs queden bajo el módulo al que pertenecen.
 */
$modulosFiltro = [];
foreach ($nivelesConRaps ?? [] as $fila) {
    $modulosFiltro[$fila['id']] ??= ['nombre' => $fila['nombre'], 'raps' => []];

    if (!empty($fila['rap_id'])) {
        $modulosFiltro[$fila['id']]['raps'][$fila['rap_id']] = $fila['rap_titulo'];
    }
}
?>
<div class="tarjeta" style="margin-bottom: 24px;">
  <form method="GET" action="<?= $accionFiltros ?>" style="display:flex; gap:16px; align-items:flex-end; flex-wrap:wrap;">

    <div class="form-grupo" style="flex:1; min-width:200px; display:flex; flex-direction:column; gap:8px;">
      <label for="filtro-nivel" style="font-weight:600; color:var(--texto-principal);">Nivel:</label>
      <select id="filtro-nivel" name="nivel_id" class="input-premium select-premium">
        <option value="">Todos los Niveles</option>
        <?php foreach ($modulosFiltro as $idModulo => $modulo): ?>
          <option value="<?= limpiar($idModulo) ?>" <?= $filtroNivel === $idModulo ? 'selected' : '' ?>>
            <?= limpiar($modulo['nombre']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="form-grupo" style="flex:1; min-width:200px; display:flex; flex-direction:column; gap:8px;">
      <label for="filtro-rap" style="font-weight:600; color:var(--texto-principal);">RAP:</label>
      <select id="filtro-rap" name="rap_id" class="input-premium select-premium">
        <option value="">Todos los RAPs</option>
        <?php foreach ($modulosFiltro as $modulo): ?>
          <?php if (empty($modulo['raps'])) continue; ?>
          <optgroup label="<?= limpiar($modulo['nombre']) ?>">
            <?php
              // Dos RAPs activos con el mismo título en un módulo solo ocurre con
              // filas duplicadas: el id corto permite distinguirlos al elegir.
              $vecesTitulo = array_count_values($modulo['raps']);
            ?>
            <?php foreach ($modulo['raps'] as $idRap => $tituloRap): ?>
              <option value="<?= limpiar($idRap) ?>" <?= $filtroRap === $idRap ? 'selected' : '' ?>>
                <?= limpiar($tituloRap) ?><?= $vecesTitulo[$tituloRap] > 1 ? ' · ' . limpiar(substr($idRap, 0, 8)) : '' ?>
              </option>
            <?php endforeach; ?>
          </optgroup>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="form-grupo" style="flex:1; min-width:200px; display:flex; flex-direction:column; gap:8px;">
      <label for="filtro-estado" style="font-weight:600; color:var(--texto-principal);">Estado:</label>
      <select id="filtro-estado" name="estado" class="input-premium select-premium">
        <option value="">Todos los Estados</option>
        <?php foreach ($opcionesEstado as $valorEstado => $etiquetaEstado): ?>
          <option value="<?= $valorEstado ?>" <?= $filtroEstado === $valorEstado ? 'selected' : '' ?>><?= $etiquetaEstado ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="form-grupo" style="display:flex; gap:8px;">
      <button type="submit" class="btn btn-primario"><i class="fas fa-search"></i> Filtrar</button>
      <a href="<?= $accionFiltros ?>" class="btn btn-secundario" title="Quitar filtros" aria-label="Quitar filtros"><i class="fas fa-eraser"></i></a>
    </div>

  </form>
</div>
