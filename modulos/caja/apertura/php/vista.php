<?php
/**
 * modulos/caja/apertura/php/vista.php
 * Apertura de caja con desglose de billetes y monedas.
 * Tablas: caja, caja_detalle_denominacion
 *
 * - Solo puede haber una caja con estado 'abierta' a la vez.
 * - El monto inicial se calcula sumando (cantidad × valor) de cada
 *   denominación; no se escribe a mano.
 * - Solo el Administrador ve y usa este módulo (permiso caja-apertura).
 */

require_once __DIR__ . '/../../../../dashboard/includes/auth.php';
require_once __DIR__ . '/../../../../dashboard/includes/permisos.php';
require_once __DIR__ . '/../../../../config/conexion.php';

requierePermiso('caja-apertura', 'ver');

$conn = conexionBD();

// =====================================================
// DENOMINACIONES DEL QUETZAL
// valor => texto visible
// =====================================================
$DENOMINACIONES = [
    '200.00' => 'Billete de Q200',
    '100.00' => 'Billete de Q100',
    '50.00'  => 'Billete de Q50',
    '20.00'  => 'Billete de Q20',
    '10.00'  => 'Billete de Q10',
    '5.00'   => 'Billete de Q5',
    '1.00'   => 'Moneda de Q1',
    '0.50'   => 'Moneda de Q0.50',
    '0.25'   => 'Moneda de Q0.25',
    '0.10'   => 'Moneda de Q0.10',
    '0.05'   => 'Moneda de Q0.05',
];

// =====================================================
// ¿YA HAY UNA CAJA ABIERTA?
// Mientras exista una, no se puede aperturar otra.
// =====================================================
$stmt = $conn->prepare(
    "SELECT c.id_caja, c.fecha_apertura, c.montoInicial, c.observaciones,
            u.nombres, u.apellidos
     FROM caja c
     INNER JOIN usuarios u ON u.id_usuario = c.id_usuario
     WHERE c.estado = 'abierta'
     ORDER BY c.id_caja DESC
     LIMIT 1"
);
$stmt->execute();
$cajaAbierta = $stmt->fetch();

// =====================================================
// HELPERS
// =====================================================
if (!function_exists('e')) {
    function e($valor): string {
        return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('fechaHoraDMA')) {
    function fechaHoraDMA(?string $fecha): string {
        if (!$fecha) return '—';
        return (new DateTime($fecha))->format('d/m/Y H:i');
    }
}

if (!function_exists('montoQ')) {
    function montoQ($valor): string {
        return 'Q ' . number_format((float) $valor, 2, '.', ',');
    }
}

$puedeCrear = tienePermiso('caja-apertura', 'crear');
?>

<div class="panel-head">
  <div>
    <span class="eyebrow-dark">Caja</span>
    <h1>Aperturar <em>caja</em></h1>
    <p>Cuenta el efectivo con el que empieza el turno, billete por billete.</p>
  </div>
  <span class="chip-rol"><?= e($nombreRol) ?></span>
</div>

<?php if ($cajaAbierta): ?>

  <!-- ============ YA HAY UNA CAJA ABIERTA ============ -->
  <div class="bloque form-agendar">
    <div class="aviso-caja aviso-caja-ok">
      <span><strong>La caja ya está abierta.</strong> Debe cerrarse antes de poder aperturar una nueva.</span>
    </div>

    <div class="grid-form">
      <div class="campo-panel">
        <label>Abierta por</label>
        <input type="text" value="<?= e($cajaAbierta['nombres'] . ' ' . $cajaAbierta['apellidos']) ?>" disabled />
      </div>
      <div class="campo-panel">
        <label>Fecha de apertura</label>
        <input type="text" value="<?= e(fechaHoraDMA($cajaAbierta['fecha_apertura'])) ?>" disabled />
      </div>
      <div class="campo-panel">
        <label>Monto inicial</label>
        <input type="text" value="<?= e(montoQ($cajaAbierta['montoInicial'])) ?>" disabled />
      </div>
      <div class="campo-panel ancho">
        <label>Observaciones</label>
        <input type="text" value="<?= e($cajaAbierta['observaciones'] ?: 'Sin observaciones') ?>" disabled />
      </div>
    </div>

    <p class="nota-panel">El cierre de caja se realiza desde el módulo "Cierre de caja".</p>
  </div>

<?php elseif ($puedeCrear): ?>

  <!-- ============ FORMULARIO DE APERTURA ============ -->
  <div class="bloque form-agendar">
    <div class="aviso-caja">
      <span><strong>Importante:</strong> la caja debe aperturarse antes de registrar cualquier venta del día.</span>
    </div>

    <form id="formAperturaCaja" autocomplete="off" onsubmit="return false;">
      <div class="grid-form">
        <div class="campo-panel">
          <label>Abierta por</label>
          <input type="text" value="<?= e($nombreUsuario) ?>" disabled />
        </div>
        <div class="campo-panel">
          <label>Fecha apertura</label>
          <input type="text" value="Se registra al guardar" disabled />
        </div>
        <div class="campo-panel ancho">
          <label>Observaciones</label>
          <input type="text" name="observaciones" maxlength="75" placeholder="Turno matutino" />
        </div>
      </div>

      <h2 class="titulo-denominaciones">Conteo de efectivo</h2>
      <p class="sub">Indica cuántos billetes o monedas de cada valor hay en la caja.</p>

      <div class="tabla-scroll">
        <table class="tabla-panel" id="tablaDenominaciones">
          <thead>
            <tr>
              <th>Denominación</th>
              <th>Cantidad</th>
              <th>Subtotal</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($DENOMINACIONES as $valor => $texto): ?>
              <tr data-valor="<?= e($valor) ?>">
                <td class="principal"><?= e($texto) ?></td>
                <td>
                  <input type="number"
                         class="input-cantidad"
                         name="cantidad_<?= e($valor) ?>"
                         min="0"
                         step="1"
                         value="0"
                         inputmode="numeric" />
                </td>
                <td class="subtotal-denominacion">Q 0.00</td>
              </tr>
            <?php endforeach; ?>
          </tbody>
          <tfoot>
            <tr>
              <td class="principal" colspan="2">Monto inicial (total contado)</td>
              <td id="totalDenominaciones" class="total-denominacion">Q 0.00</td>
            </tr>
          </tfoot>
        </table>
      </div>

      <div class="form-acciones">
        <button type="button" class="btn-linea" id="btnLimpiarApertura">Limpiar</button>
        <button type="submit" class="btn-oro">Aperturar caja</button>
      </div>
    </form>
  </div>

<?php else: ?>

  <!-- ============ SIN PERMISO PARA APERTURAR ============ -->
  <div class="bloque form-agendar">
    <div class="tabla-vacia">
      <p>No tienes permiso para aperturar la caja.</p>
    </div>
  </div>

<?php endif; ?>