<?php
/**
 * modulos/mantenimiento/horario-estetica/php/vista.php
 * Horario semanal de atención + cierres programados (vacaciones, remodelación, etc.).
 * Tablas: horarios_estetica, cierres_estetica
 *
 * - horarios_estetica: un registro por día de la semana (apertura/cierre).
 * - cierres_estetica: rangos de fechas en los que la estética no abre o
 *   abre parcialmente. Sin horas (NULL) = día(s) completo(s) cerrado(s).
 *   Con horas = cierre parcial, solo cuando fechaInicio = fechaFin.
 */

require_once __DIR__ . '/../../../../dashboard/includes/auth.php';
require_once __DIR__ . '/../../../../dashboard/includes/permisos.php';
require_once __DIR__ . '/../../../../config/conexion.php';

requierePermiso('horario-estetica', 'ver');

$conn = conexionBD();

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
// TIPOS DE CIERRE
// =====================================================
$TIPOS_CIERRE = [
    'vacaciones'   => 'Vacaciones',
    'remodelacion' => 'Remodelación',
    'imprevisto'   => 'Imprevisto',
    'feriado'      => 'Feriado',
    'otro'         => 'Otro',
];

// =====================================================
// HORARIO SEMANAL
// =====================================================
$stmt = $conn->query(
    "SELECT id_horario_estetica, dias, horaApertura, horaCierre, estado
     FROM horarios_estetica
     ORDER BY FIELD(dias, 'Lunes','Martes','Miércoles','Jueves','Viernes','Sabado','Domingo')"
);
$horarioSemanal = $stmt->fetchAll();

// Días que ya tienen horario configurado (para que el select de "nuevo" no los repita)
$diasConfigurados = array_column($horarioSemanal, 'dias');
$diasDisponibles  = array_diff(array_keys($DIAS), $diasConfigurados);

// =====================================================
// CIERRES PROGRAMADOS (los más recientes primero)
// =====================================================
$stmt = $conn->query(
    "SELECT
        c.id_cierre_estetica,
        c.fechaInicio,
        c.fechaFin,
        c.horaInicio,
        c.horaFin,
        c.tipo,
        c.observaciones,
        c.estado,
        c.fechaRegistro,
        u.nombres,
        u.apellidos
     FROM cierres_estetica c
     LEFT JOIN usuarios u ON u.id_usuario = c.id_usuario_registro
     ORDER BY c.fechaInicio DESC, c.id_cierre_estetica DESC"
);
$cierres = $stmt->fetchAll();

// =====================================================
// HELPERS
// =====================================================
if (!function_exists('e')) {
    function e($valor): string {
        return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('horaCorta')) {
    function horaCorta(?string $hora): string {
        return $hora ? substr($hora, 0, 5) : '';
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

$opcionesTiposCierre = '';
foreach ($TIPOS_CIERRE as $valor => $texto) {
    $opcionesTiposCierre .= '<option value="' . e($valor) . '">' . e($texto) . '</option>';
}

$puedeCrear  = tienePermiso('horario-estetica', 'crear');
$puedeEditar = tienePermiso('horario-estetica', 'editar');

// =====================================================
// CAMPOS DEL FORMULARIO DE CIERRE (idénticos en "nuevo" y "editar")
// Los campos de hora empiezan ocultos: el JS los muestra solo cuando
// la fecha de inicio y la de fin son la misma.
// =====================================================
ob_start();
?>
        <div class="campo-panel">
          <label>Tipo de cierre</label>
          <select name="tipo" required>
            <?= $opcionesTiposCierre ?>
          </select>
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
          <small>Si el cierre es de unas horas, indica desde cuándo y hasta cuándo. Déjalo vacío si es todo el día.</small>
        </div>
        <div class="campo-panel ancho">
          <label>Motivo</label>
          <input type="text" name="observaciones" maxlength="100" placeholder="Ej: Remodelación del local" />
        </div>
<?php
$camposCierre = ob_get_clean();
?>

<div class="panel-head">
  <div>
    <span class="eyebrow-dark">Configuración</span>
    <h1>Horario de la <em>estética</em></h1>
    <p>Días y horas de atención, y los cierres programados del negocio.</p>
  </div>
  <span class="chip-rol"><?= e($nombreRol) ?></span>
</div>

<!-- ============ BLOQUE 1: HORARIO SEMANAL ============ -->
<div class="bloque">
  <div class="bloque-top">
    <div>
      <h2>Horarios de atención</h2>
      <p class="sub"><?= count($horarioSemanal) ?> de 7 días configurados</p>
    </div>
    <?php if ($puedeCrear && !empty($diasDisponibles)): ?>
      <button class="btn-oro" data-modal="modalHorarioEst">+ Nuevo horario</button>
    <?php endif; ?>
  </div>

  <div class="tabla-scroll">
    <?php if (empty($horarioSemanal)): ?>

      <div class="tabla-vacia">
        <p>Aún no se ha configurado el horario de la estética.</p>
        <?php if ($puedeCrear): ?>
          <small>Usa el botón "+ Nuevo horario" para agregar el primer día.</small>
        <?php endif; ?>
      </div>

    <?php else: ?>

      <table class="tabla-panel" id="tablaHorarioEstetica">
        <thead>
          <tr>
            <th>Día</th>
            <th>Hora apertura</th>
            <th>Hora cierre</th>
            <th>Estado</th>
            <?php if ($puedeEditar): ?>
              <th class="col-acc">Acciones</th>
            <?php endif; ?>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($horarioSemanal as $h): ?>
            <?php
              $activo      = ((int) $h['estado'] === 1);
              $claseEstado = $activo ? 'activo' : 'inactivo';
              $textoEstado = $activo ? 'Activo' : 'Inactivo';
              $textoDia    = $DIAS[$h['dias']] ?? (string) $h['dias'];
            ?>
            <tr data-horario="<?= (int) $h['id_horario_estetica'] ?>">
              <td class="principal"><?= e($textoDia) ?></td>
              <td><?= horaCorta($h['horaApertura']) ?></td>
              <td><?= horaCorta($h['horaCierre']) ?></td>
              <td><span class="badge <?= $claseEstado ?>"><?= $textoEstado ?></span></td>
              <?php if ($puedeEditar): ?>
                <td class="col-acc">
                  <button class="btn-accion editar"
                          data-modal="modalEditarHorarioEst"
                          data-horario="<?= (int) $h['id_horario_estetica'] ?>"
                          data-dia="<?= e($h['dias']) ?>"
                          data-apertura="<?= e(horaCorta($h['horaApertura'])) ?>"
                          data-cierre="<?= e(horaCorta($h['horaCierre'])) ?>"
                          data-estado="<?= (int) $h['estado'] ?>">Editar</button>
                </td>
              <?php endif; ?>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>

    <?php endif; ?>
  </div>
</div>

<!-- ============ BLOQUE 2: CIERRES PROGRAMADOS ============ -->
<div class="bloque">
  <div class="bloque-top">
    <div>
      <h2>Cierres programados</h2>
      <p class="sub">Vacaciones, remodelaciones u otros días sin atención — <?= count($cierres) ?> registrados</p>
    </div>
    <div class="acciones-top">
      <input type="search" class="buscador" id="buscarCierre" placeholder="Buscar tipo o motivo..." />
      <?php if ($puedeCrear): ?>
        <button class="btn-oro" data-modal="modalCierre">+ Nuevo cierre</button>
      <?php endif; ?>
    </div>
  </div>

  <div class="tabla-scroll">
    <?php if (empty($cierres)): ?>

      <div class="tabla-vacia">
        <p>No hay cierres programados.</p>
        <?php if ($puedeCrear): ?>
          <small>Usa el botón "+ Nuevo cierre" para registrar vacaciones o una remodelación.</small>
        <?php endif; ?>
      </div>

    <?php else: ?>

      <table class="tabla-panel" id="tablaCierresEstetica">
        <thead>
          <tr>
            <th>Tipo</th>
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
          <?php foreach ($cierres as $c): ?>
            <?php
              $activo      = ((int) $c['estado'] === 1);
              $claseEstado = $activo ? 'activo' : 'inactivo';
              $textoEstado = $activo ? 'Activo' : 'Inactivo';

              $tipoTexto = mb_strtoupper($TIPOS_CIERRE[$c['tipo']] ?? (string) $c['tipo'], 'UTF-8');

              $nombreReg = $c['nombres'] !== null
                  ? $c['nombres'] . ' ' . $c['apellidos']
                  : '—';

              $horaIni = horaCorta($c['horaInicio']);
              $horaFin = horaCorta($c['horaFin']);
              $horario = ($horaIni !== '' && $horaFin !== '')
                  ? $horaIni . ' – ' . $horaFin
                  : 'Todo el día';
            ?>
            <tr data-cierre="<?= (int) $c['id_cierre_estetica'] ?>">
              <td class="principal"><?= e($tipoTexto) ?></td>
              <td><?= fechaDMA($c['fechaInicio']) ?></td>
              <td><?= fechaDMA($c['fechaFin']) ?></td>
              <td><?= e($horario) ?></td>
              <td><?= e($c['observaciones'] ?: '—') ?></td>
              <td><span class="badge <?= $claseEstado ?>"><?= $textoEstado ?></span></td>
              <td><?= e($nombreReg) ?></td>
              <td><?= fechaHoraDMA($c['fechaRegistro']) ?></td>
              <?php if ($puedeEditar): ?>
                <td class="col-acc">
                  <button class="btn-accion editar"
                          data-modal="modalEditarCierre"
                          data-cierre="<?= (int) $c['id_cierre_estetica'] ?>"
                          data-tipo="<?= e($c['tipo']) ?>"
                          data-inicio="<?= e($c['fechaInicio']) ?>"
                          data-fin="<?= e($c['fechaFin']) ?>"
                          data-hora-inicio="<?= e($horaIni) ?>"
                          data-hora-fin="<?= e($horaFin) ?>"
                          data-observaciones="<?= e($c['observaciones'] ?? '') ?>"
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

<?php if ($puedeCrear && !empty($diasDisponibles)): ?>
<!-- ============ MODAL: NUEVO HORARIO SEMANAL ============ -->
<div class="modal-panel" id="modalHorarioEst">
  <div class="modal-caja">
    <h3>Nuevo horario de la estética</h3>
    <p class="sub">Define la hora de apertura y de cierre para un día de la semana.</p>

    <form id="formNuevoHorarioEst" autocomplete="off" onsubmit="return false;">
      <div class="grid-form">
        <div class="campo-panel">
          <label>Día</label>
          <select name="dias" required>
            <?php foreach ($diasDisponibles as $valor): ?>
              <option value="<?= e($valor) ?>"><?= e($DIAS[$valor]) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="campo-panel">
          <label>Estado</label>
          <select name="estado">
            <option value="1">Activo</option>
            <option value="0">Inactivo</option>
          </select>
        </div>
        <div class="campo-panel">
          <label>Hora apertura</label>
          <input type="time" name="horaApertura" value="08:00" required />
        </div>
        <div class="campo-panel">
          <label>Hora cierre</label>
          <input type="time" name="horaCierre" value="18:00" required />
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
<!-- ============ MODAL: EDITAR HORARIO SEMANAL ============ -->
<div class="modal-panel" id="modalEditarHorarioEst">
  <div class="modal-caja">
    <h3>Editar horario de la estética</h3>
    <p class="sub">Modifica la hora de apertura, de cierre o el estado de este día.</p>

    <form id="formEditarHorarioEst" autocomplete="off" onsubmit="return false;">
      <input type="hidden" name="id_horario" value="" />

      <div class="grid-form">
        <div class="campo-panel">
          <label>Día</label>
          <select name="dias" required>
            <?php foreach ($DIAS as $valor => $texto): ?>
              <option value="<?= e($valor) ?>"><?= e($texto) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="campo-panel">
          <label>Estado</label>
          <select name="estado">
            <option value="1">Activo</option>
            <option value="0">Inactivo</option>
          </select>
        </div>
        <div class="campo-panel">
          <label>Hora apertura</label>
          <input type="time" name="horaApertura" required />
        </div>
        <div class="campo-panel">
          <label>Hora cierre</label>
          <input type="time" name="horaCierre" required />
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

<?php if ($puedeCrear): ?>
<!-- ============ MODAL: NUEVO CIERRE ============ -->
<div class="modal-panel" id="modalCierre">
  <div class="modal-caja">
    <h3>Nuevo cierre programado</h3>
    <p class="sub">Registra vacaciones, una remodelación u otro cierre.</p>

    <form id="formNuevoCierre" autocomplete="off" onsubmit="return false;">
      <div class="grid-form">
<?= $camposCierre ?>
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
<!-- ============ MODAL: EDITAR CIERRE ============ -->
<div class="modal-panel" id="modalEditarCierre">
  <div class="modal-caja">
    <h3>Editar cierre programado</h3>
    <p class="sub">Modifica el tipo, las fechas, el horario o el estado de este cierre.</p>

    <form id="formEditarCierre" autocomplete="off" onsubmit="return false;">
      <input type="hidden" name="id_cierre" value="" />

      <div class="grid-form">
<?= $camposCierre ?>
      </div>

      <div class="modal-acciones">
        <button type="button" class="btn-linea" data-cerrar>Cancelar</button>
        <button type="submit" class="btn-oro">Guardar cambios</button>
      </div>
    </form>
  </div>
</div>
<?php endif; ?>