<?php
/**
 * modulos/empleados/horario/php/vista.php
 * Listado de horarios por empleado y día + modales para crear y editar.
 * Tabla: horarios_empleados
 */

require_once __DIR__ . '/../../../../dashboard/includes/auth.php';
require_once __DIR__ . '/../../../../dashboard/includes/permisos.php';
require_once __DIR__ . '/../../../../config/conexion.php';

requierePermiso('emp-horario', 'ver');

$conn = conexionBD();

// Usuario interno del sistema: no aparece como empleado
const ID_USUARIO_SISTEMA = 1;

// =====================================================
// DÍAS DE LA SEMANA
// clave = valor exacto del ENUM en la BD | valor = texto visible
// =====================================================
$DIAS = [
    'Lunes'     => 'Lunes',
    'Martes'    => 'Martes',
    'Miércoles' => 'Miércoles',
    'Jueves'    => 'Jueves',
    'Viernes'   => 'Viernes',
    'Sabado'    => 'Sábado',
    'Domingo'   => 'Domingo',
];

// =====================================================
// LISTADO DE HORARIOS
// El ORDER BY sobre un ENUM ordena por su posición (Lunes → Domingo)
// =====================================================
$stmt = $conn->prepare(
    "SELECT
        h.id_horario_usuario,
        h.id_usuario,
        h.dias,
        h.horaInicio,
        h.horaFin,
        h.estado,
        u.nombres,
        u.apellidos
     FROM horarios_empleados h
     INNER JOIN usuarios u ON u.id_usuario = h.id_usuario
     WHERE h.id_usuario <> :sistema
     ORDER BY u.nombres ASC, u.apellidos ASC, h.dias ASC, h.horaInicio ASC"
);
$stmt->execute([':sistema' => ID_USUARIO_SISTEMA]);
$horarios = $stmt->fetchAll();

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
if (!function_exists('horaCorta')) {
    function horaCorta(?string $hora): string {
        return $hora ? substr($hora, 0, 5) : '—';
    }
}

if (!function_exists('e')) {
    function e($valor): string {
        return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
    }
}

// Opciones de los select (se reutilizan en los dos modales)
$opcionesEmpleados = '<option value="">Selecciona un empleado</option>';
foreach ($empleados as $emp) {
    $opcionesEmpleados .= '<option value="' . (int) $emp['id_usuario'] . '">'
        . e($emp['nombres'] . ' ' . $emp['apellidos']) . '</option>';
}

$opcionesDias = '';
foreach ($DIAS as $valor => $texto) {
    $opcionesDias .= '<option value="' . e($valor) . '">' . e($texto) . '</option>';
}

$puedeCrear    = tienePermiso('emp-horario', 'crear');
$puedeEditar   = tienePermiso('emp-horario', 'editar');
$puedeEliminar = tienePermiso('emp-horario', 'eliminar');
?>

<div class="panel-head">
  <div>
    <span class="eyebrow-dark">Empleados</span>
    <h1>Horario de <em>empleado</em></h1>
    <p>Días de trabajo con hora de entrada y de salida de cada empleado.</p>
  </div>
  <span class="chip-rol"><?= e($nombreRol) ?></span>
</div>

<div class="bloque">
  <div class="bloque-top">
    <div>
      <h2>Horarios asignados</h2>
      <p class="sub"><?= count($horarios) ?> registrados</p>
    </div>
    <div class="acciones-top">
      <input type="search" class="buscador" id="buscarHorario" placeholder="Buscar empleado..." />
      <?php if ($puedeCrear): ?>
        <button class="btn-oro" data-modal="modalHorarioEmp">+ Nuevo horario</button>
      <?php endif; ?>
    </div>
  </div>

  <div class="tabla-scroll">
    <?php if (empty($horarios)): ?>

      <div class="tabla-vacia">
        <p>No hay horarios asignados aún.</p>
        <?php if ($puedeCrear): ?>
          <small>Usa el botón "+ Nuevo horario" para asignar el primero.</small>
        <?php endif; ?>
      </div>

    <?php else: ?>

      <table class="tabla-panel" id="tablaHorarios">
        <thead>
          <tr>
            <th>Empleado</th>
            <th>Día</th>
            <th>Hora entrada</th>
            <th>Hora salida</th>
            <th>Estado</th>
            <?php if ($puedeEditar || $puedeEliminar): ?>
              <th class="col-acc">Acciones</th>
            <?php endif; ?>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($horarios as $h): ?>
            <?php
              $activo      = ((int) $h['estado'] === 1);
              $claseEstado = $activo ? 'activo' : 'inactivo';
              $textoEstado = $activo ? 'Activo' : 'Inactivo';
              $nombreEmp   = $h['nombres'] . ' ' . $h['apellidos'];
              $textoDia    = $DIAS[$h['dias']] ?? (string) $h['dias'];
            ?>
            <tr data-horario="<?= (int) $h['id_horario_usuario'] ?>">
              <td class="principal"><?= e($nombreEmp) ?></td>
              <td><?= e($textoDia) ?></td>
              <td><?= horaCorta($h['horaInicio']) ?></td>
              <td><?= horaCorta($h['horaFin']) ?></td>
              <td><span class="badge <?= $claseEstado ?>"><?= $textoEstado ?></span></td>
              <?php if ($puedeEditar || $puedeEliminar): ?>
                <td class="col-acc">
                  <?php if ($puedeEditar): ?>
                    <button class="btn-accion editar"
                            data-modal="modalEditarHorario"
                            data-horario="<?= (int) $h['id_horario_usuario'] ?>"
                            data-usuario="<?= (int) $h['id_usuario'] ?>"
                            data-dia="<?= e($h['dias']) ?>"
                            data-inicio="<?= e(horaCorta($h['horaInicio'])) ?>"
                            data-fin="<?= e(horaCorta($h['horaFin'])) ?>"
                            data-estado="<?= (int) $h['estado'] ?>">Editar</button>
                  <?php endif; ?>
                  <?php if ($puedeEliminar): ?>
                    <button class="btn-accion eliminar"
                            data-horario="<?= (int) $h['id_horario_usuario'] ?>"
                            data-nombre="<?= e($nombreEmp . ' — ' . $textoDia) ?>">Eliminar</button>
                  <?php endif; ?>
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
<!-- ============ MODAL: NUEVO HORARIO ============ -->
<div class="modal-panel" id="modalHorarioEmp">
  <div class="modal-caja">
    <h3>Nuevo horario de empleado</h3>
    <p class="sub">Asigna un día de trabajo con su hora de entrada y de salida.</p>

    <form id="formNuevoHorario" autocomplete="off" onsubmit="return false;">
      <div class="grid-form">
        <div class="campo-panel">
          <label>Empleado</label>
          <select name="id_usuario" required>
            <?= $opcionesEmpleados ?>
          </select>
        </div>
        <div class="campo-panel">
          <label>Día</label>
          <select name="dias" required>
            <?= $opcionesDias ?>
          </select>
        </div>
        <div class="campo-panel">
          <label>Hora entrada</label>
          <input type="time" name="horaInicio" value="08:00" required />
        </div>
        <div class="campo-panel">
          <label>Hora salida</label>
          <input type="time" name="horaFin" value="17:00" required />
        </div>
        <div class="campo-panel">
          <label>Estado</label>
          <select name="estado">
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
<?php endif; ?>

<?php if ($puedeEditar): ?>
<!-- ============ MODAL: EDITAR HORARIO ============ -->
<div class="modal-panel" id="modalEditarHorario">
  <div class="modal-caja">
    <h3>Editar horario de empleado</h3>
    <p class="sub">Modifica el día, las horas o el estado de este horario.</p>

    <form id="formEditarHorario" autocomplete="off" onsubmit="return false;">
      <input type="hidden" name="id_horario" value="" />

      <div class="grid-form">
        <div class="campo-panel">
          <label>Empleado</label>
          <select name="id_usuario" required>
            <?= $opcionesEmpleados ?>
          </select>
        </div>
        <div class="campo-panel">
          <label>Día</label>
          <select name="dias" required>
            <?= $opcionesDias ?>
          </select>
        </div>
        <div class="campo-panel">
          <label>Hora entrada</label>
          <input type="time" name="horaInicio" required />
        </div>
        <div class="campo-panel">
          <label>Hora salida</label>
          <input type="time" name="horaFin" required />
        </div>
        <div class="campo-panel">
          <label>Estado</label>
          <select name="estado">
            <option value="1">Activo</option>
            <option value="0">Inactivo</option>
          </select>
        </div>
      </div>

      <div class="modal-acciones">
        <button type="button" class="btn-linea" data-cerrar>Cancelar</button>
        <button type="submit" class="btn-oro">Guardar cambios</button>
      </div>
    </form>
  </div>
</div>
<?php endif; ?>