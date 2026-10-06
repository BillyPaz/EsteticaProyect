<?php
/**
 * modulos/empleados/ausencia/php/vista.php
 * Listado de ausencias de empleados + modales para registrar y editar.
 * Tabla: ausencia_usuario
 *
 * - Sin horas (NULL)  → ausencia de día completo (o de varios días).
 * - Con horas         → ausencia parcial, solo cuando fechaInicio = fechaFin.
 */

require_once __DIR__ . '/../../../../dashboard/includes/auth.php';
require_once __DIR__ . '/../../../../dashboard/includes/permisos.php';
require_once __DIR__ . '/../../../../config/conexion.php';

requierePermiso('emp-ausencia', 'ver');

$conn = conexionBD();

// Usuario interno del sistema: no aparece como empleado
const ID_USUARIO_SISTEMA = 1;

// =====================================================
// TIPOS DE AUSENCIA
// clave = valor exacto del ENUM en la BD | valor = texto visible
// =====================================================
$TIPOS = [
    'vacaciones' => 'Vacaciones',
    'enfermedad' => 'Enfermedad',
    'permiso'    => 'Permiso',
    'otro'       => 'Otro',
];

// =====================================================
// LISTADO DE AUSENCIAS (las más recientes primero)
// =====================================================
$stmt = $conn->prepare(
    "SELECT
        a.id_ausencia_usuario,
        a.id_usuario,
        a.fechaInicio,
        a.fechaFin,
        a.horaInicio,
        a.horaFin,
        a.motivo,
        a.observaciones,
        a.estado,
        a.fechaRegistro,
        u.nombres  AS emp_nombres,
        u.apellidos AS emp_apellidos,
        r.nombres  AS reg_nombres,
        r.apellidos AS reg_apellidos
     FROM ausencia_usuario a
     INNER JOIN usuarios u ON u.id_usuario = a.id_usuario
     LEFT JOIN  usuarios r ON r.id_usuario = a.id_usuario_registro
     WHERE a.id_usuario <> :sistema
     ORDER BY a.fechaInicio DESC, a.id_ausencia_usuario DESC"
);
$stmt->execute([':sistema' => ID_USUARIO_SISTEMA]);
$ausencias = $stmt->fetchAll();

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

if (!function_exists('horaCorta')) {
    function horaCorta(?string $hora): string {
        return $hora ? substr($hora, 0, 5) : '';
    }
}

// Opciones de los select (se reutilizan en los dos modales)
$opcionesEmpleados = '<option value="">Selecciona un empleado</option>';
foreach ($empleados as $emp) {
    $opcionesEmpleados .= '<option value="' . (int) $emp['id_usuario'] . '">'
        . e($emp['nombres'] . ' ' . $emp['apellidos']) . '</option>';
}

$opcionesTipos = '';
foreach ($TIPOS as $valor => $texto) {
    $opcionesTipos .= '<option value="' . e($valor) . '">' . e($texto) . '</option>';
}

$puedeCrear  = tienePermiso('emp-ausencia', 'crear');
$puedeEditar = tienePermiso('emp-ausencia', 'editar');

// =====================================================
// CAMPOS DEL FORMULARIO (idénticos en "nueva" y "editar")
// Los campos de hora empiezan ocultos: el JS los muestra solo cuando
// la fecha de inicio y la de fin son la misma.
// =====================================================
ob_start();
?>
        <div class="campo-panel">
          <label>Empleado</label>
          <select name="id_usuario" required>
            <?= $opcionesEmpleados ?>
          </select>
        </div>
        <div class="campo-panel">
          <label>Tipo de ausencia</label>
          <select name="motivo" required>
            <?= $opcionesTipos ?>
          </select>
        </div>
        <div class="campo-panel">
          <label>Fecha inicio</label>
          <input type="date" name="fechaInicio" required />
        </div>
        <div class="campo-panel">
          <label>Fecha fin</label>
          <input type="date" name="fechaFin" required />
        </div>
        <div class="campo-panel" data-campo-horario style="display:none;">
          <label>Hora inicio (opcional)</label>
          <input type="time" name="horaInicio" />
        </div>
        <div class="campo-panel" data-campo-horario style="display:none;">
          <label>Hora fin (opcional)</label>
          <input type="time" name="horaFin" />
        </div>
        <div class="campo-panel ancho" data-campo-horario style="display:none;">
          <small>Si la ausencia es de unas horas, indica desde cuándo y hasta cuándo. Déjalo vacío si es todo el día.</small>
        </div>
        <div class="campo-panel ancho">
          <label>Motivo</label>
          <input type="text" name="observaciones" maxlength="100" placeholder="Ej: Trámite personal" />
        </div>
        <div class="campo-panel">
          <label>Estado</label>
          <select name="estado">
            <option value="1">Activo</option>
            <option value="0">Inactivo</option>
          </select>
        </div>
<?php
$camposFormulario = ob_get_clean();
?>

<div class="panel-head">
  <div>
    <span class="eyebrow-dark">Empleados</span>
    <h1>Ausencia <em>empleado</em></h1>
    <p>Vacaciones, enfermedad, permisos u otras ausencias de los empleados.</p>
  </div>
  <span class="chip-rol"><?= e($nombreRol) ?></span>
</div>

<div class="bloque">
  <div class="bloque-top">
    <div>
      <h2>Ausencias registradas</h2>
      <p class="sub"><?= count($ausencias) ?> registradas</p>
    </div>
    <div class="acciones-top">
      <input type="search" class="buscador" id="buscarAusencia" placeholder="Buscar empleado..." />
      <?php if ($puedeCrear): ?>
        <button class="btn-oro" data-modal="modalAusencia">+ Nueva ausencia</button>
      <?php endif; ?>
    </div>
  </div>

  <div class="tabla-scroll">
    <?php if (empty($ausencias)): ?>

      <div class="tabla-vacia">
        <p>No hay ausencias registradas aún.</p>
        <?php if ($puedeCrear): ?>
          <small>Usa el botón "+ Nueva ausencia" para registrar la primera.</small>
        <?php endif; ?>
      </div>

    <?php else: ?>

      <table class="tabla-panel" id="tablaAusencias">
        <thead>
          <tr>
            <th>Empleado</th>
            <th>Tipo ausencia</th>
            <th>Fecha inicio</th>
            <th>Fecha fin</th>
            <th>Horario</th>
            <th>Motivo</th>
            <th>Estado</th>
            <th>Registrado por</th>
            <th>Fecha creación</th>
            <?php if ($puedeEditar): ?>
              <th class="col-acc">Acciones</th>
            <?php endif; ?>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($ausencias as $a): ?>
            <?php
              $activo      = ((int) $a['estado'] === 1);
              $claseEstado = $activo ? 'activo' : 'inactivo';
              $textoEstado = $activo ? 'Activo' : 'Inactivo';

              $nombreEmp = $a['emp_nombres'] . ' ' . $a['emp_apellidos'];
              $nombreReg = $a['reg_nombres'] !== null
                  ? $a['reg_nombres'] . ' ' . $a['reg_apellidos']
                  : '—';

              $tipoTexto = mb_strtoupper($TIPOS[$a['motivo']] ?? (string) $a['motivo'], 'UTF-8');

              $horaIni = horaCorta($a['horaInicio']);
              $horaFin = horaCorta($a['horaFin']);
              $horario = ($horaIni !== '' && $horaFin !== '')
                  ? $horaIni . ' – ' . $horaFin
                  : 'Todo el día';
            ?>
            <tr data-ausencia="<?= (int) $a['id_ausencia_usuario'] ?>">
              <td class="principal"><?= e($nombreEmp) ?></td>
              <td><?= e($tipoTexto) ?></td>
              <td><?= fechaDMA($a['fechaInicio']) ?></td>
              <td><?= fechaDMA($a['fechaFin']) ?></td>
              <td><?= e($horario) ?></td>
              <td><?= e($a['observaciones'] ?: '—') ?></td>
              <td><span class="badge <?= $claseEstado ?>"><?= $textoEstado ?></span></td>
              <td><?= e($nombreReg) ?></td>
              <td><?= fechaHoraDMA($a['fechaRegistro']) ?></td>
              <?php if ($puedeEditar): ?>
                <td class="col-acc">
                  <button class="btn-accion editar"
                          data-modal="modalEditarAusencia"
                          data-ausencia="<?= (int) $a['id_ausencia_usuario'] ?>"
                          data-usuario="<?= (int) $a['id_usuario'] ?>"
                          data-motivo="<?= e($a['motivo']) ?>"
                          data-inicio="<?= e($a['fechaInicio']) ?>"
                          data-fin="<?= e($a['fechaFin']) ?>"
                          data-hora-inicio="<?= e($horaIni) ?>"
                          data-hora-fin="<?= e($horaFin) ?>"
                          data-observaciones="<?= e($a['observaciones'] ?? '') ?>"
                          data-estado="<?= (int) $a['estado'] ?>">Editar</button>
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
<!-- ============ MODAL: NUEVA AUSENCIA ============ -->
<div class="modal-panel" id="modalAusencia">
  <div class="modal-caja">
    <h3>Nueva ausencia de empleado</h3>
    <p class="sub">Registra vacaciones, enfermedad, un permiso u otra ausencia.</p>

    <form id="formNuevaAusencia" autocomplete="off" onsubmit="return false;">
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
<!-- ============ MODAL: EDITAR AUSENCIA ============ -->
<div class="modal-panel" id="modalEditarAusencia">
  <div class="modal-caja">
    <h3>Editar ausencia de empleado</h3>
    <p class="sub">Modifica el tipo, las fechas, el horario o el estado de esta ausencia.</p>

    <form id="formEditarAusencia" autocomplete="off" onsubmit="return false;">
      <input type="hidden" name="id_ausencia" value="" />

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