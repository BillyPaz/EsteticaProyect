<?php
/**
 * modulos/caja/cierre/php/vista.php
 * Cierre de caja con desglose de billetes y monedas.
 * Tablas: caja, caja_cierre, caja_cierre_denominacion
 *
 * Reglas de negocio:
 * - Solo puede cerrarse una caja que esté en estado 'abierta'.
 * - Solo existe una caja abierta a la vez.
 * - El monto contado se calcula sumando (cantidad × valor) de cada denominación.
 * - Los montos "esperado" y "diferencia" los calcula el backend al procesar el cierre.
 * - Solo el Administrador ve y usa este módulo (permiso caja-cierre).
 * - La fecha de cierre la asigna el backend (NOW()); aquí solo se muestra.
 */

require_once __DIR__ . '/../../../../dashboard/includes/auth.php';
require_once __DIR__ . '/../../../../dashboard/includes/permisos.php';
require_once __DIR__ . '/../../../../config/conexion.php';

requierePermiso('caja-cierre', 'ver');

$conn = conexionBD();

// =====================================================
// DENOMINACIONES DEL QUETZAL (mismas que en apertura)
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

$puedeCerrar = tienePermiso('caja-cierre', 'crear');

// =====================================================
// ¿HAY UNA CAJA ABIERTA PARA CERRAR?
// Se trae el turno activo + usuario que aperturó.
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
// VALORES POR DEFECTO (los reemplaza el backend al procesar)
// =====================================================
$ventasTurno   = 0.00;
$montoEsperado = $cajaAbierta ? (float) $cajaAbierta['montoInicial'] : 0.00;
$diferencia    = 0.00;

// Si tu backend ya calcula ventasTurno antes de renderizar, sobreescribí aquí:
// $ventasTurno   = $ventasCalculadas ?? 0.00;
// $montoEsperado = $montoInicial + $ventasTurno;
// $diferencia    = 0.00; // se recalcula al procesar
?>

<div class="panel-head">
  <div>
    <span class="eyebrow-dark">Caja</span>
    <h1>Cierre de <em>caja</em></h1>
    <p>Compara lo registrado en el sistema contra el efectivo contado.</p>
  </div>
  <span class="chip-rol"><?= e($nombreRol) ?></span>
</div>

<?php if (!$cajaAbierta): ?>

  <!-- ============ NO HAY CAJA ABIERTA ============ -->
  <div class="bloque form-agendar">
    <div class="aviso-caja aviso-caja-ok">
      <span><strong>No hay ninguna caja abierta.</strong> Debe aperturarse una caja antes de poder cerrarla.</span>
    </div>
    <p class="nota-panel">La apertura de caja se realiza desde el módulo "Aperturar caja".</p>
  </div>

<?php elseif (!$puedeCerrar): ?>

  <!-- ============ SIN PERMISO PARA CERRAR ============ -->
  <div class="bloque form-agendar">
    <div class="tabla-vacia">
      <p>No tienes permiso para cerrar la caja.</p>
    </div>
  </div>

<?php else: ?>

  <!-- ============ TARJETAS RESUMEN ============ -->
  <div class="tarjetas-cierre">
    <div class="tarjeta-cierre">
      <div class="tarjeta-valor" id="cardMontoInicial"><?= e(montoQ($cajaAbierta['montoInicial'])) ?></div>
      <div class="tarjeta-label">Monto inicial</div>
    </div>
    <div class="tarjeta-cierre">
      <div class="tarjeta-valor" id="cardVentasTurno"><?= e(montoQ($ventasTurno)) ?></div>
      <div class="tarjeta-label">Ventas del turno</div>
    </div>
    <div class="tarjeta-cierre">
      <div class="tarjeta-valor" id="cardTotalEsperado"><?= e(montoQ($montoEsperado)) ?></div>
      <div class="tarjeta-label">Total esperado</div>
    </div>
  </div>

  <!-- ============ FORMULARIO DE CIERRE ============ -->
  <div class="bloque form-agendar">
    <form id="formCierreCaja"
          autocomplete="off"
          onsubmit="return false;"
          data-id-caja="<?= e($cajaAbierta['id_caja']) ?>"
          data-monto-inicial="<?= e(number_format((float) $cajaAbierta['montoInicial'], 2, '.', '')) ?>"
          data-ventas-turno="<?= e(number_format((float) $ventasTurno, 2, '.', '')) ?>"
          data-monto-esperado="<?= e(number_format((float) $montoEsperado, 2, '.', '')) ?>">

      <div class="grid-form">
        <div class="campo-panel">
          <label>ID_Caja</label>
          <input type="text" value="<?= e($cajaAbierta['id_caja']) ?>" disabled />
        </div>
        <div class="campo-panel">
          <label>Fecha cierre</label>
          <input type="text" value="<?= e(fechaHoraDMA(date('Y-m-d H:i:s'))) ?>" disabled />
        </div>

        <div class="campo-panel">
          <label>Monto esperado</label>
          <input type="text" id="inputMontoEsperado"
                 value="<?= e(number_format((float) $montoEsperado, 2, '.', '')) ?>"
                 disabled />
        </div>
        <div class="campo-panel">
          <label>Monto contado</label>
          <input type="text" id="inputMontoContado"
                 name="montoContado"
                 value="0.00"
                 readonly
                 data-monto-contado="0.00" />
        </div>

        <div class="campo-panel">
          <label>Diferencia</label>
          <input type="text" id="inputDiferencia"
                 value="<?= e(number_format((float) $diferencia, 2, '.', '')) ?>"
                 disabled />
        </div>
        <div class="campo-panel">
          <label>Estado</label>
          <input type="text" value="Cerrada" disabled />
        </div>

        <div class="campo-panel ancho">
          <label>Observaciones</label>
          <input type="text" name="observaciones" maxlength="150" placeholder="Sin diferencias" />
        </div>
      </div>

      <h2 class="titulo-denominaciones">Conteo de efectivo</h2>
      <p class="sub">Indica cuántos billetes o monedas de cada valor hay en la caja al cierre.</p>

      <div class="tabla-scroll">
        <table class="tabla-panel" id="tablaDenominacionesCierre">
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
              <td class="principal" colspan="2">Monto contado (total contado)</td>
              <td id="totalDenominacionesCierre" class="total-denominacion">Q 0.00</td>
            </tr>
          </tfoot>
        </table>
      </div>

      <div class="form-acciones">
        <button type="button" class="btn-linea" id="btnCancelarCierre">Cancelar</button>
        <button type="submit" class="btn-oro" id="btnCerrarCaja">Cerrar caja</button>
      </div>
    </form>
  </div>

<?php endif; ?>