<?php
/**
 * modulos/citas/vista.php
 * Vista del módulo Citas.
 * Se carga vía AJAX dentro del dashboard.
 */

// Cargar auth y permisos
require_once __DIR__ . '/../../../dashboard/includes/auth.php';
require_once __DIR__ . '/../../../dashboard/includes/permisos.php';

// Validar que el usuario tenga permiso de ver este módulo
requierePermiso('citas', 'ver');

// Conexión a BD
require_once __DIR__ . '/../../../config/conexion.php';
$conn = conexionBD();

// =====================================================
// 1. CONSULTAS DE MÉTRICAS
// =====================================================

// Citas activas (no canceladas)
$sqlActivas = "SELECT COUNT(*) AS total FROM citas 
               WHERE estado IN ('reservada', 'confirmada', 'completada')";
$metricas = [];
$metricas['activas'] = (int) $conn->query($sqlActivas)->fetch()['total'];

// Citas para hoy
$sqlHoy = "SELECT COUNT(*) AS total FROM citas 
           WHERE fecha = CURDATE() 
             AND estado IN ('reservada', 'confirmada', 'completada')";
$metricas['hoy'] = (int) $conn->query($sqlHoy)->fetch()['total'];

// Citas sin pagar (aquellas que no tienen un registro en serviciocitavalidacion)
$sqlPendientes = "SELECT COUNT(*) AS total FROM citas c
                  WHERE c.estado IN ('reservada', 'confirmada')
                    AND NOT EXISTS (
                      SELECT 1 FROM serviciocitavalidacion scv WHERE scv.id_cita = c.id_cita
                    )";
$metricas['falta_pagar'] = (int) $conn->query($sqlPendientes)->fetch()['total'];

// Cobrado hoy (suma de ventasDetalleServicio de ventas creadas hoy)
$sqlCobrado = "SELECT COALESCE(SUM(vds.subtotalConDescuento), 0) AS total
               FROM ventasDetalleServicio vds
               INNER JOIN ventas v ON v.id_venta = vds.id_venta
               WHERE DATE(v.fechaVenta) = CURDATE()";
$metricas['cobrado_hoy'] = (float) $conn->query($sqlCobrado)->fetch()['total'];

// =====================================================
// 2. CONSULTA DEL LISTADO DE CITAS (con JOINs)
// =====================================================

$sqlCitas = "SELECT
                c.id_cita,
                c.fecha,
                c.hora,
                c.estado,
                cl.nombreCliente,
                cl.apellidoCliente,
                cl.telefono AS telefonoCliente,
                cl.correo    AS correoCliente,
                CONCAT(u.nombres, ' ', u.apellidos) AS barbero,
                s.nombreServicio,
                s.costoServicio,
                CASE WHEN scv.id_servicio_cita_validacion IS NOT NULL 
                     THEN 'Cancelado' 
                     ELSE 'Falta pagar' 
                END AS estadoPago
             FROM citas c
             INNER JOIN clientes  cl ON cl.id_cliente  = c.id_cliente
             LEFT  JOIN usuarios  u  ON u.id_usuario   = c.id_usuario
             LEFT  JOIN cita_servicio cs ON cs.id_cita = c.id_cita
             LEFT  JOIN servicios s  ON s.id_servicio  = cs.id_servicio
             LEFT  JOIN serviciocitavalidacion scv ON scv.id_cita = c.id_cita
             ORDER BY c.fecha DESC, c.hora DESC";

$citas = $conn->query($sqlCitas)->fetchAll();

// =====================================================
// 3. DATOS AUXILIARES PARA FILTROS
// =====================================================

// Lista de barberos (usuarios con rol activo)
$sqlBarberos = "SELECT DISTINCT u.id_usuario, CONCAT(u.nombres, ' ', u.apellidos) AS nombre
                FROM usuarios u
                INNER JOIN rol_usuario ru ON ru.id_usuario = u.id_usuario AND ru.estado = 1
                WHERE u.estado = 1
                ORDER BY u.nombres";
$barberos = $conn->query($sqlBarberos)->fetchAll();

// =====================================================
// 4. HELPERS
// =====================================================

/** Formatea fecha de YYYY-MM-DD a DD / MM / YYYY */
function formatearFecha(?string $fecha): string {
    if (!$fecha) return '—';
    $dt = DateTime::createFromFormat('Y-m-d', $fecha);
    return $dt ? $dt->format('d / m / Y') : $fecha;
}

/** Formatea hora de HH:MM:SS a HH:MM */
function formatearHora(?string $hora): string {
    if (!$hora) return '—';
    return substr($hora, 0, 5);
}

/** Formatea un número como quetzales */
function formatearQ(float $monto): string {
    return 'Q ' . number_format($monto, 2);
}
?>

<!-- ============ CABECERA ============ -->
<div class="panel-head">
  <div>
    
    <h1>Citas <em>reservadas</em></h1>
    <p>Control de reservas, barbero asignado y estado de pago.</p>
  </div>
  <span class="chip-rol"><?= htmlspecialchars($nombreRol) ?></span>
</div>

<!-- ============ MÉTRICAS ============ -->
<div class="metricas">
  <div class="metrica">
    <strong><?= $metricas['activas'] ?></strong>
    <span>Citas activas</span>
  </div>
  <div class="metrica">
    <strong><?= $metricas['hoy'] ?></strong>
    <span>Para hoy</span>
  </div>
  <div class="metrica">
    <strong><?= $metricas['falta_pagar'] ?></strong>
    <span>Falta pagar</span>
  </div>
  <div class="metrica">
    <strong><?= formatearQ($metricas['cobrado_hoy']) ?></strong>
    <span>Cobrado hoy</span>
  </div>
</div>

<!-- ============ BLOQUE PRINCIPAL ============ -->
<div class="bloque">
  <div class="bloque-top">
    <div>
      <h2>Listado de citas</h2>
      <p class="sub">Filtra por barbero para ver cuántos cortes realizó en el día.</p>
    </div>
    <div class="acciones-top">
      <input type="search" class="buscador" id="buscarCita" placeholder="Buscar cliente..." />
      <select class="select-filtro" id="filtroBarbero">
        <option value="">Todos los barberos</option>
        <?php foreach ($barberos as $b): ?>
          <option value="<?= htmlspecialchars($b['nombre']) ?>">
            <?= htmlspecialchars($b['nombre']) ?>
          </option>
        <?php endforeach; ?>
        <option value="Nada">Sin barbero asignado</option>
      </select>
    </div>
  </div>

  <div class="resumen-barberos">
    <div class="resumen-barbero"><strong id="cortesOtto">0</strong><span>Cortes — Otto</span></div>
    <div class="resumen-barbero"><strong id="cortesKevin">0</strong><span>Cortes — Kevin</span></div>
    <div class="resumen-barbero"><strong id="cortesSin">0</strong><span>Sin preferencia</span></div>
  </div>

  <div class="tabla-scroll">
    <?php if (empty($citas)): ?>

      <div class="tabla-vacia">
        <p>No hay citas registradas aún.</p>
        <small>Las citas reservadas desde el sitio público aparecerán aquí.</small>
      </div>

    <?php else: ?>

      <table class="tabla-panel" id="tablaCitas">
        <thead>
          <tr>
            <th>Cliente</th>
            <th>Teléfono</th>
            <th>Servicio</th>
            <th>Barbero</th>
            <th>Fecha</th>
            <th>Hora</th>
            <th>Pago</th>
            <th>Estado</th>
            <th class="col-acc">Acción</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($citas as $cita): ?>
            <?php
              $barbero     = $cita['barbero'] ?? 'Nada';
              $estadoPago  = $cita['estadoPago'] ?? 'Falta pagar';
              $clasePago   = ($estadoPago === 'Cancelado') ? 'pagado' : 'debe';
              $claseEstado = match($cita['estado']) {
                  'confirmada' => 'conf',
                  'completada' => 'conf',
                  'reservada'  => 'pend',
                  'cancelada'  => 'debe',
                  default      => 'pend',
              };
              $textoEstado = ucfirst($cita['estado'] ?? 'pendiente');
            ?>
            <tr data-barbero="<?= htmlspecialchars($barbero) ?>">
              <td>
                <span class="principal">
                  <?= htmlspecialchars($cita['nombreCliente'] . ' ' . $cita['apellidoCliente']) ?>
                </span>
                <small><?= htmlspecialchars($cita['correoCliente']) ?></small>
              </td>
              <td><?= htmlspecialchars($cita['telefonoCliente']) ?></td>
              <td><?= htmlspecialchars($cita['nombreServicio'] ?? 'Sin servicio') ?></td>
              <td>
                <?php if ($barbero === 'Nada'): ?>
                  <span class="vacio-nada">Nada</span>
                <?php else: ?>
                  <?= htmlspecialchars($barbero) ?>
                <?php endif; ?>
              </td>
              <td><?= formatearFecha($cita['fecha']) ?></td>
              <td><?= formatearHora($cita['hora']) ?></td>
              <td><span class="badge <?= $clasePago ?>"><?= htmlspecialchars($estadoPago) ?></span></td>
              <td><span class="badge <?= $claseEstado ?>"><?= htmlspecialchars($textoEstado) ?></span></td>
              <td class="col-acc">
                <button class="btn-accion editar">Editar</button>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>

    <?php endif; ?>
  </div>

  <p class="nota-panel">Las citas se reservan desde el sitio público. La confirmación de pago se gestiona en el módulo "Citas pagas".</p>
</div>