<?php
/**
 * modulos/empleados/comisiones/php/vista.php
 * Listado de configuraciones de comisión por empleado + modales para crear y editar.
 * Tabla: asignacioncomision
 *
 * - Cada configuración tiene un porcentaje y una vigencia (fechaInicio → fechaFin).
 * - fechaFin vacía (NULL) = sin fecha de cierre.
 */

require_once __DIR__ . '/../../../../dashboard/includes/auth.php';
require_once __DIR__ . '/../../../../dashboard/includes/permisos.php';
require_once __DIR__ . '/../../../../config/conexion.php';

requierePermiso('emp-comisiones', 'ver');

$conn = conexionBD();

// Usuario interno del sistema: no aparece como empleado
const ID_USUARIO_SISTEMA = 1;

// =====================================================
// LISTADO DE CONFIGURACIONES
// Por empleado; dentro de cada uno, la más reciente primero
// =====================================================
$stmt = $conn->prepare(
    "SELECT
        c.id_asignacion_comision,
        c.id_usuario,
        c.porcentaje,
        c.fechaInicio,
        c.fechaFin,
        c.estado,
        c.fechaRegistro,
        u.nombres,
        u.apellidos
     FROM asignacioncomision c
     INNER JOIN usuarios u ON u.id_usuario = c.id_usuario
     WHERE c.id_usuario <> :sistema
     ORDER BY u.nombres ASC, u.apellidos ASC,
              c.fechaInicio DESC, c.id_asignacion_comision DESC"
);
$stmt->execute([':sistema' => ID_USUARIO_SISTEMA]);
$comisiones = $stmt->fetchAll();

// =====================================================
// EMPLEADOS ACTIVOS (para los select de los modales)
// =====================================================
$stmt = $conn->prepare(
    "SELECT id_usuario, nombres, apellidos
     FROM usuarios
     WHERE estado = 1 AND id_usuario <> :sistema
     ORDER BY nombres ASC, apellidos ASC"
);
$stmt->execute([':sistema' => ID_USUARIO_SISTEMA]);
$empleados = $stmt->fetchAll();

// =====================================================
// HELPERS
// =====================================================
if (!function_exists('e')) {
    function e($valor): string {
        return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('fechaDMA')) {
    function fechaDMA(?string $fecha): string {
        if (!$fecha) return '—';
        return (new DateTime($fecha))->format('d/m/Y');
    }
}

if (!function_exists('fechaHoraDMA')) {
    function fechaHoraDMA(?string $fecha): string {
        if (!$fecha) return '—';
        return (new DateTime($fecha))->format('d/m/Y H:i');
    }
}

// Opciones del select de empleados (se reutilizan en los dos modales)
$opcionesEmpleados = '<option value="">Selecciona un empleado</option>';
foreach ($empleados as $emp) {
    $opcionesEmpleados .= '<option value="' . (int) $emp['id_usuario'] . '">'
        . e($emp['nombres'] . ' ' . $emp['apellidos']) . '</option>';
}

$puedeCrear  = tienePermiso('emp-comisiones', 'crear');
$puedeEditar = tienePermiso('emp-comisiones', 'editar');

// =====================================================
// CAMPOS DEL FORMULARIO (idénticos en "nueva" y "editar")
// =====================================================
ob_start();
?>
        <div class="campo-panel ancho">
          <label>Empleado</label>
          <select name="id_usuario" required>
            <?= $opcionesEmpleados ?>
          </select>
        </div>
        <div class="campo-panel">
          <label>Porcentaje (%)</label>
          <input type="number" name="porcentaje" step="0.01" min="0.01" max="99" required placeholder="15.00" />
        </div>
        <div class="campo-panel">
          <label>Estado</label>
          <select name="estado">
            <option value="1">Activo</option>
            <option value="0">Inactivo</option>
          </select>
        </div>
        <div class="campo-panel">
          <label>Fecha inicio</label>
          <input type="date" name="fechaInicio" required />
        </div>
        <div class="campo-panel">
          <label>Fecha fin (opcional)</label>
          <input type="date" name="fechaFin" />
        </div>
        <div class="campo-panel ancho">
          <small>El porcentaje puede ser de 0.01 a 99. Deja la fecha fin vacía si la configuración no tiene fecha de cierre.</small>
        </div>
<?php
$camposFormulario = ob_get_clean();
?>

<div class="panel-head">
  <div>
    <span class="eyebrow-dark">Empleados</span>
    <h1>Comisiones <em>empleados</em></h1>
    <p>Porcentaje de comisión de cada empleado y el período en que está vigente.</p>
  </div>
  <span class="chip-rol"><?= e($nombreRol) ?></span>
</div>

<div class="bloque">
  <div class="bloque-top">
    <div>
      <h2>Configuraciones de comisión</h2>
      <p class="sub"><?= count($comisiones) ?> registradas</p>
    </div>
    <div class="acciones-top">
      <input type="search" class="buscador" id="buscarComision" placeholder="Buscar empleado..." />
      <?php if ($puedeCrear): ?>
        <button class="btn-oro" data-modal="modalComision">+ Nueva configuración</button>
      <?php endif; ?>
    </div>
  </div>

  <div class="tabla-scroll">
    <?php if (empty($comisiones)): ?>

      <div class="tabla-vacia">
        <p>No hay configuraciones de comisión aún.</p>
        <?php if ($puedeCrear): ?>
          <small>Usa el botón "+ Nueva configuración" para crear la primera.</small>
        <?php endif; ?>
      </div>

    <?php else: ?>

      <table class="tabla-panel" id="tablaComisiones">
        <thead>
          <tr>
            <th>Empleado</th>
            <th>Porcentaje</th>
            <th>Fecha inicio</th>
            <th>Fecha fin</th>
            <th>Estado</th>
            <th>Fecha creación</th>
            <?php if ($puedeEditar): ?>
              <th class="col-acc">Acciones</th>
            <?php endif; ?>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($comisiones as $c): ?>
            <?php
              $activo      = ((int) $c['estado'] === 1);
              $claseEstado = $activo ? 'activo' : 'inactivo';
              $textoEstado = $activo ? 'Activo' : 'Inactivo';
              $nombreEmp   = $c['nombres'] . ' ' . $c['apellidos'];
              $porcentaje  = number_format((float) $c['porcentaje'], 2, '.', '');
            ?>
            <tr data-comision="<?= (int) $c['id_asignacion_comision'] ?>">
              <td class="principal"><?= e($nombreEmp) ?></td>
              <td><?= e($porcentaje) ?> %</td>
              <td><?= fechaDMA($c['fechaInicio']) ?></td>
              <td>
                <?php if ($c['fechaFin']): ?>
                  <?= fechaDMA($c['fechaFin']) ?>
                <?php else: ?>
                  <span class="vacio-nada">Nada</span>
                <?php endif; ?>
              </td>
              <td><span class="badge <?= $claseEstado ?>"><?= $textoEstado ?></span></td>
              <td><?= fechaHoraDMA($c['fechaRegistro']) ?></td>
              <?php if ($puedeEditar): ?>
                <td class="col-acc">
                  <button class="btn-accion editar"
                          data-modal="modalEditarComision"
                          data-comision="<?= (int) $c['id_asignacion_comision'] ?>"
                          data-usuario="<?= (int) $c['id_usuario'] ?>"
                          data-porcentaje="<?= e($porcentaje) ?>"
                          data-inicio="<?= e($c['fechaInicio']) ?>"
                          data-fin="<?= e($c['fechaFin'] ?? '') ?>"
                          data-estado="<?= (int) $c['estado'] ?>">Editar</button>
                </td>
              <?php endif; ?>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>

    <?php endif; ?>
  </div>
</div>

<?php if ($puedeCrear): ?>
<!-- ============ MODAL: NUEVA CONFIGURACIÓN ============ -->
<div class="modal-panel" id="modalComision">
  <div class="modal-caja">
    <h3>Nueva configuración de comisión</h3>
    <p class="sub">Define el porcentaje que gana un empleado y desde cuándo aplica.</p>

    <form id="formNuevaComision" autocomplete="off" onsubmit="return false;">
      <div class="grid-form">
<?= $camposFormulario ?>
      </div>

      <div class="modal-acciones">
        <button type="button" class="btn-linea" data-cerrar>Cancelar</button>
        <button type="submit" class="btn-oro">Guardar</button>
      </div>
    </form>
  </div>
</div>
<?php endif; ?>

<?php if ($puedeEditar): ?>
<!-- ============ MODAL: EDITAR CONFIGURACIÓN ============ -->
<div class="modal-panel" id="modalEditarComision">
  <div class="modal-caja">
    <h3>Editar configuración de comisión</h3>
    <p class="sub">Modifica el porcentaje, la vigencia o el estado de esta configuración.</p>

    <form id="formEditarComision" autocomplete="off" onsubmit="return false;">
      <input type="hidden" name="id_comision" value="" />

      <div class="grid-form">
<?= $camposFormulario ?>
      </div>

      <div class="modal-acciones">
        <button type="button" class="btn-linea" data-cerrar>Cancelar</button>
        <button type="submit" class="btn-oro">Guardar cambios</button>
      </div>
    </form>
  </div>
</div>
<?php endif; ?>