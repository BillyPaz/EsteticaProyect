<?php
/**
 * modulos/mantenimiento/servicios/php/vista.php
 * Vista del módulo Servicios:
 * - Pestaña "Servicios individuales" (tabla)
 * - Pestaña "Combos" (tarjetas)
 * - Formulario embebido para crear/editar combos
 */

require_once __DIR__ . '/../../../../dashboard/includes/auth.php';
require_once __DIR__ . '/../../../../dashboard/includes/permisos.php';
require_once __DIR__ . '/../../../../config/conexion.php';

requierePermiso('servicios', 'ver');

$conn = conexionBD();

// =====================================================
// 1. LISTADO DE SERVICIOS
// =====================================================
$sqlServicios = "SELECT     
                    id_servicio,               
                    nombreServicio,
                    costoServicio,
                    duracion,
                    activo,
                    fechaRegistro
                 FROM servicios
                 ORDER BY id_servicio DESC";
$servicios = $conn->query($sqlServicios)->fetchAll();

// =====================================================
// 2. LISTADO DE COMBOS CON SUS SERVICIOS
// =====================================================
$sqlCombos = "SELECT
                c.idCombo,
                c.nombre,
                c.descripcion,
                c.precioCombo,
                c.incluyeBebida,
                c.activo,
                c.fechaRegistro
              FROM combos c
              WHERE c.activo = 1
              ORDER BY c.idCombo DESC";
$combos = $conn->query($sqlCombos)->fetchAll();

// Para cada combo, traer sus servicios
$sqlDetalle = "SELECT
                    cd.idCombo,
                    s.id_servicio,
                    s.nombreServicio,
                    s.costoServicio,
                    s.duracion
               FROM combo_detalle cd
               INNER JOIN servicios s ON s.id_servicio = cd.idServicio
               ORDER BY cd.id_combo_detalle ASC";
$detalle = $conn->query($sqlDetalle)->fetchAll();

// Agrupar detalle por idCombo
$detallePorCombo = [];
foreach ($detalle as $d) {
    $detallePorCombo[$d['idCombo']][] = $d;
}

// =====================================================
// 3. HELPERS
// =====================================================

function formatearQ(float $monto): string {
    return 'Q ' . number_format($monto, 2);
}

function fechaCorta(?string $fecha): string {
    if (!$fecha) return '—';
    $dt = new DateTime($fecha);
    return $dt->format('d/m/Y H:i');
}

/** Calcula el precio original de un combo (suma de servicios) */
function calcularPrecioOriginal(array $serviciosCombo): float {
    $total = 0;
    foreach ($serviciosCombo as $s) {
        $total += (float) $s['costoServicio'];
    }
    return $total;
}

/** Calcula la duración total de un combo (suma de duraciones) */
function calcularDuracionCombo(array $serviciosCombo): int {
    $total = 0;
    foreach ($serviciosCombo as $s) {
        $total += (int) $s['duracion'];
    }
    return $total;
}
?>

<div class="panel-head">
  <div>
    <span class="eyebrow-dark">Catálogo</span>
    <h1>Servicios del <em>salón</em></h1>
    <p>Gestiona los servicios individuales y los combos del salón.</p>
  </div>
  <span class="chip-rol"><?= htmlspecialchars($nombreRol) ?></span>
</div>

<!-- ============================================================= -->
<!-- TOGGLE DE PESTAÑAS                                            -->
<!-- ============================================================= -->
<div class="toggle-catalogo">
  <button type="button" class="toggle-btn" data-tab="combos">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 7l9-4 9 4-9 4-9-4z"/><path d="M3 7v10l9 4 9-4V7"/><path d="M12 11v10"/></svg>
    <span>Combos</span>
  </button>
  <button type="button" class="toggle-btn activo" data-tab="servicios">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="6" cy="6" r="2.4"/><circle cx="6" cy="18" r="2.4"/><path d="M8.5 8 20 19M20 5 8.5 16"/></svg>
    <span>Servicios individuales</span>
  </button>
</div>

<!-- ============================================================= -->
<!-- PESTAÑA: SERVICIOS INDIVIDUALES                               -->
<!-- ============================================================= -->
<div class="tab-catalogo activo" id="tab-servicios">

  <div class="bloque">
    <div class="bloque-top">
      <div>
        <h2>Servicios registrados</h2>
        <p class="sub"><?= count($servicios) ?> servicios en el catálogo</p>
      </div>
      <div class="acciones-top">
        <input type="search" class="buscador" id="buscarServicio" placeholder="Buscar servicio..." />
        <?php if (tienePermiso('servicios', 'crear')): ?>
          <button class="btn-oro" data-modal="modalServicio" id="btnNuevoServicio">+ Nuevo servicio</button>
        <?php endif; ?>
      </div>
    </div>

    <div class="tabla-scroll">
      <?php if (empty($servicios)): ?>

        <div class="tabla-vacia">
          <p>No hay servicios registrados aún.</p>
          <small>Usa el botón "+ Nuevo servicio" para crear el primero.</small>
        </div>

      <?php else: ?>

        <table class="tabla-panel" id="tablaServicios">
          <thead>
            <tr>         
              <th>NOMBRE SERVICIO</th>
              <th>COSTO SERVICIO</th>
              <th>DURACIÓN (MIN)</th>
              <th>ACTIVO</th>
              <th>FECHA REGISTRO</th>
              <th class="col-acc">ACCIONES</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($servicios as $s): ?>
              <?php
                $activo = ((int) $s['activo'] === 1);
                $claseActivo = $activo ? 'activo' : 'inactivo';
                $textoActivo = $activo ? 'Activo' : 'Inactivo';
              ?>
              <tr data-servicio="<?= (int) $s['id_servicio'] ?>">
                <td class="principal"><?= htmlspecialchars($s['nombreServicio']) ?></td>
                <td><?= formatearQ((float) $s['costoServicio']) ?></td>
                <td><?= (int) $s['duracion'] ?></td>
                <td><span class="badge <?= $claseActivo ?>"><?= $textoActivo ?></span></td>
                <td><?= fechaCorta($s['fechaRegistro']) ?></td>
                <td class="col-acc">
                  <?php if (tienePermiso('servicios', 'editar')): ?>
                    <button class="btn-accion editar"
                            data-modal="modalServicio"
                            data-servicio="<?= (int) $s['id_servicio'] ?>"
                            data-nombre="<?= htmlspecialchars($s['nombreServicio']) ?>"
                            data-costo="<?= (float) $s['costoServicio'] ?>"
                            data-duracion="<?= (int) $s['duracion'] ?>"
                            data-activo="<?= (int) $s['activo'] ?>">Editar</button>
                  <?php endif; ?>
                  <?php if (tienePermiso('servicios', 'eliminar')): ?>
                    <button class="btn-accion eliminar" data-servicio="<?= (int) $s['id_servicio'] ?>">Eliminar</button>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>

      <?php endif; ?>
    </div>
  </div>

</div>

<!-- ============================================================= -->
<!-- PESTAÑA: COMBOS                                               -->
<!-- ============================================================= -->
<div class="tab-catalogo" id="tab-combos">

  <div class="bloque">
    <div class="bloque-top">
      <div>
        <h2>Combos registrados</h2>
        <p class="sub"><?= count($combos) ?> combos en el catálogo</p>
      </div>
      <div class="acciones-top">
        <input type="search" class="buscador" id="buscarCombo" placeholder="Buscar combo..." />
        <?php if (tienePermiso('servicios', 'crear')): ?>
          <button class="btn-oro" id="btnNuevoCombo">+ Nuevo combo</button>
        <?php endif; ?>
      </div>
    </div>

    <div class="grid-combos" id="gridCombos">
      <?php if (empty($combos)): ?>

        <div class="tabla-vacia">
          <p>No hay combos registrados aún.</p>
          <small>Usa el botón "+ Nuevo combo" para crear el primero.</small>
        </div>

      <?php else: ?>

        <?php foreach ($combos as $c): ?>
          <?php
            $serviciosCombo = $detallePorCombo[$c['idCombo']] ?? [];
            $precioOriginal = calcularPrecioOriginal($serviciosCombo);
            $precioCombo    = (float) $c['precioCombo'];
            $ahorro         = $precioOriginal - $precioCombo;
            $duracionTotal  = calcularDuracionCombo($serviciosCombo);
          ?>

          <div class="card-combo" data-combo="<?= (int) $c['idCombo'] ?>">

            <?php if ($ahorro > 0): ?>
              <span class="badge-popular">POPULAR</span>
            <?php endif; ?>

            <div class="combo-top">
              <span class="combo-icono">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M9 12l2 2 4-4"/></svg>
              </span>
            </div>

            <h3><?= htmlspecialchars($c['nombre']) ?></h3>
            <p class="combo-desc"><?= htmlspecialchars($c['descripcion'] ?? '') ?></p>

            <ul class="combo-lista">
              <?php foreach ($serviciosCombo as $s): ?>
                <li>
                  <span class="bullet">•</span>
                  <?= htmlspecialchars($s['nombreServicio']) ?>
                </li>
              <?php endforeach; ?>
              <?php if ((int) $c['incluyeBebida'] === 1): ?>
                <li class="bebida">
                  <span class="bullet">🥤</span>
                  Bebida incluida
                </li>
              <?php endif; ?>
            </ul>

            <div class="combo-precios">
              <?php if ($ahorro > 0): ?>
                <span class="precio-original"><?= formatearQ($precioOriginal) ?></span>
              <?php endif; ?>
              <strong class="precio-combo"><?= formatearQ($precioCombo) ?></strong>
              <?php if ($ahorro > 0): ?>
                <span class="ahorro">Ahorras <?= formatearQ($ahorro) ?></span>
              <?php endif; ?>
            </div>

            <div class="combo-footer">
              <span class="duracion">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3.5 2"/></svg>
                <?= $duracionTotal ?> min
              </span>
              <div class="combo-acciones">
                <?php if (tienePermiso('servicios', 'editar')): ?>
                  <button class="btn-accion editar" data-combo="<?= (int) $c['idCombo'] ?>">Editar</button>
                <?php endif; ?>
                <?php if (tienePermiso('servicios', 'eliminar')): ?>
                  <button class="btn-accion eliminar" data-combo="<?= (int) $c['idCombo'] ?>">Eliminar</button>
                <?php endif; ?>
              </div>
            </div>

          </div>
        <?php endforeach; ?>

      <?php endif; ?>
    </div>
  </div>

</div>

<!-- ============================================================= -->
<!-- VISTA EMBEBIDA: FORMULARIO DE COMBO                           -->
<!-- ============================================================= -->
<div class="vista-embebida" id="formComboWrap">

  <button type="button" class="btn-volver" id="btnVolverCombos">
    ← Volver a combos
  </button>

  <div class="panel-head">
    <div>
      <span class="eyebrow-dark">Nuevo combo</span>
      <h1 id="formComboTitulo">Crear <em>combo</em></h1>
      <p>Selecciona los servicios que incluye y define el precio del combo.</p>
    </div>
  </div>

  <form id="formCombo" autocomplete="off" onsubmit="return false;">
    <input type="hidden" name="idCombo" id="inputIdCombo" value="" />

    <div class="bloque">
      <div class="grid-form">
        <div class="campo-panel ancho">
          <label>Nombre del combo *</label>
          <input type="text" name="nombre" id="inputNombreCombo" required maxlength="100" placeholder="Combo Caballero 5" />
        </div>
        <div class="campo-panel ancho">
          <label>Descripción</label>
          <input type="text" name="descripcion" id="inputDescripcionCombo" maxlength="150" placeholder="Corte + barba + bebida" />
        </div>
      </div>
    </div>

    <div class="bloque">
      <div class="bloque-top">
        <div>
          <h2>Servicios incluidos</h2>
          <p class="sub">Busca servicios y agrégalos al combo.</p>
        </div>
      </div>

      <div class="buscador-combo-wrap">
        <input type="search" class="buscador" id="buscarServicioCombo" placeholder="Buscar servicio por nombre..." autocomplete="off" />
        <button type="button" class="btn-linea" id="btnMostrarTodos">Mostrar todos</button>
      </div>

      <div class="resultados-servicios" id="resultadosServicios"></div>

      <div class="cuadro-combo" id="cuadroCombo">
        <p class="cuadro-vacio" id="cuadroVacio">Aún no has agregado servicios al combo.</p>
      </div>
    </div>

    <div class="bloque">
      <div class="grid-form">
        <div class="campo-panel">
          <label>Precio original (calculado)</label>
          <input type="text" id="inputPrecioOriginal" value="Q 0.00" readonly />
        </div>
        <div class="campo-panel">
          <label>Precio del combo *</label>
          <input type="number" step="0.01" min="0" name="precioCombo" id="inputPrecioCombo" required placeholder="125.00" />
        </div>
        <div class="campo-panel">
          <label>Ahorro del cliente</label>
          <input type="text" id="inputAhorro" value="Q 0.00" readonly />
        </div>
        <div class="campo-panel">
          <label>Duración total</label>
          <input type="text" id="inputDuracionTotal" value="0 min" readonly />
        </div>
        <div class="campo-panel">
          <label>¿Incluye bebida?</label>
          <select name="incluyeBebida" id="inputIncluyeBebida">
            <option value="0">No</option>
            <option value="1">Sí</option>
          </select>
        </div>
        <div class="campo-panel">
          <label>Estado</label>
          <select name="activo" id="inputActivoCombo">
            <option value="1">Activo</option>
            <option value="0">Inactivo</option>
          </select>
        </div>
      </div>

      <div class="form-acciones">
        <button type="button" class="btn-linea" id="btnCancelarCombo">Cancelar</button>
        <button type="submit" class="btn-oro">Guardar combo</button>
      </div>
    </div>
  </form>
</div>

<!-- ============================================================= -->
<!-- MODAL: NUEVO / EDITAR SERVICIO                                -->
<!-- ============================================================= -->
<div class="modal-panel" id="modalServicio">
  <div class="modal-caja">
    <h3 id="modalServicioTitulo">Nuevo servicio</h3>
    <p class="sub">Tabla servicios.</p>

    <form id="formServicio" autocomplete="off" onsubmit="return false;">
      <input type="hidden" name="id_servicio" id="inputIdServicio" value="" />

      <div class="grid-form">
        <div class="campo-panel ancho">
          <label>Nombre servicio *</label>
          <input type="text" name="nombreServicio" id="inputNombreServicio" required maxlength="50" placeholder="Corte completo" />
        </div>
        <div class="campo-panel">
          <label>Costo servicio *</label>
          <input type="number" step="0.01" min="0" name="costoServicio" id="inputCostoServicio" required placeholder="180.00" />
        </div>
        <div class="campo-panel">
          <label>Duración (min) *</label>
          <input type="number" min="1" name="duracion" id="inputDuracionServicio" required placeholder="60" />
        </div>
        <div class="campo-panel">
          <label>Estado</label>
          <select name="activo" id="inputActivoServicio">
            <option value="1">Activo</option>
            <option value="0">Inactivo</option>
          </select>
        </div>
      </div>

      <div class="modal-acciones">
        <button type="button" class="btn-linea" data-cerrar>Cancelar</button>
        <button type="submit" class="btn-oro">Guardar</button>
      </div>
    </form>
  </div>
</div>