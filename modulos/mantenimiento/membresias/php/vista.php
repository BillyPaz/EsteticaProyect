<?php
/**
 * modulos/mantenimiento/membresias/php/vista.php
 * Vista del módulo Membresías:
 * - Pestaña "Membresías" (tabla)
 * - Pestaña "Membresías por clientes" (tabla)
 */

require_once __DIR__ . '/../../../../dashboard/includes/auth.php';
require_once __DIR__ . '/../../../../dashboard/includes/permisos.php';
require_once __DIR__ . '/../../../../config/conexion.php';

requierePermiso('membresias', 'ver');

$conn = conexionBD();

// =====================================================
// 1. LISTADO DE MEMBRESÍAS
// =====================================================
$sqlMembresias = "SELECT id_membresia, codigo, nombre, porcentajeDescuento, estado, fechaRegistro
                  FROM membresias
                  ORDER BY id_membresia DESC";
$membresias = $conn->query($sqlMembresias)->fetchAll();

// =====================================================
// 2. LISTADO DE MEMBRESÍAS ASIGNADAS A CLIENTES (solo activas)
// =====================================================
$sqlAsignaciones = "SELECT
                        mc.id_membresia_cliente,
                        mc.id_cliente,
                        mc.id_membresia,
                        mc.id_usuario,
                        mc.fechaRegistro,
                        c.nombreCliente,
                        c.apellidoCliente,
                        c.telefono,
                        c.correo,
                        m.codigo        AS codigoMembresia,
                        m.nombre        AS nombreMembresia,
                        m.porcentajeDescuento,
                        CONCAT(u.nombres, ' ', u.apellidos) AS asignadoPor
                    FROM membresiacliente mc
                    INNER JOIN clientes c   ON c.id_cliente  = mc.id_cliente
                    INNER JOIN membresias m ON m.id_membresia = mc.id_membresia
                    INNER JOIN usuarios u   ON u.id_usuario   = mc.id_usuario
                    WHERE mc.estado = 1
                    ORDER BY mc.id_membresia_cliente DESC";
$asignaciones = $conn->query($sqlAsignaciones)->fetchAll();

// =====================================================
// 3. HELPERS
// =====================================================

function fechaCorta(?string $fecha): string {
    if (!$fecha) return '—';
    $dt = new DateTime($fecha);
    return $dt->format('d/m/Y H:i');
}
?>

<div class="panel-head">
  <div>
    <span class="eyebrow-dark">Programa de fidelidad</span>
    <h1>Membresías y <em>descuentos</em></h1>
    <p>Gestiona las membresías disponibles y su asignación a clientes.</p>
  </div>
  <span class="chip-rol"><?= htmlspecialchars($nombreRol) ?></span>
</div>

<!-- ============================================================= -->
<!-- TOGGLE DE PESTAÑAS                                            -->
<!-- ============================================================= -->
<div class="toggle-catalogo">
  <button type="button" class="toggle-btn activo" data-tab="membresias">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="2.5" y="5" width="19" height="14" rx="2.2"/><path d="M2.5 10h19"/><path d="M6 15h5"/></svg>
    <span>Membresías</span>
  </button>
  <button type="button" class="toggle-btn" data-tab="asignadas">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="8" r="3.2"/><path d="M3.5 19c0-3.3 2.6-5.5 5.5-5.5s5.5 2.2 5.5 5.5"/><path d="M17 8h4M19 6v4"/></svg>
    <span>Membresías por cliente</span>
  </button>
</div>

<!-- ============================================================= -->
<!-- PESTAÑA: MEMBRESÍAS                                           -->
<!-- ============================================================= -->
<div class="tab-catalogo activo" id="tab-membresias">

  <div class="bloque">
    <div class="bloque-top">
      <div>
        <h2>Membresías registradas</h2>
        <p class="sub"><?= count($membresias) ?> membresías en el catálogo</p>
      </div>
      <div class="acciones-top">
        <input type="search" class="buscador" id="buscarMembresia" placeholder="Buscar membresía..." />
        <?php if (tienePermiso('membresias', 'crear')): ?>
          <button class="btn-oro" id="btnNuevaMembresia">+ Nueva membresía</button>
        <?php endif; ?>
      </div>
    </div>

    <div class="tabla-scroll">
      <?php if (empty($membresias)): ?>

        <div class="tabla-vacia">
          <p>No hay membresías registradas aún.</p>
          <small>Usa el botón "+ Nueva membresía" para crear la primera.</small>
        </div>

      <?php else: ?>

        <table class="tabla-panel" id="tablaMembresias">
          <thead>
            <tr>
              <th>CÓDIGO</th>
              <th>NOMBRE</th>
              <th>% DESCUENTO</th>
              <th>ESTADO</th>
              <th>FECHA REGISTRO</th>
              <th class="col-acc">ACCIONES</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($membresias as $m): ?>
              <?php
                $activo = ((int) $m['estado'] === 1);
                $claseActivo = $activo ? 'activo' : 'inactivo';
                $textoActivo = $activo ? 'Activo' : 'Inactivo';
              ?>
              <tr data-membresia="<?= (int) $m['id_membresia'] ?>">
                <td class="principal"><?= htmlspecialchars($m['codigo']) ?></td>
                <td><?= htmlspecialchars($m['nombre']) ?></td>
                <td><?= number_format((float) $m['porcentajeDescuento'], 2) ?> %</td>
                <td><span class="badge <?= $claseActivo ?>"><?= $textoActivo ?></span></td>
                <td><?= fechaCorta($m['fechaRegistro']) ?></td>
                <td class="col-acc">
                  <?php if (tienePermiso('membresias', 'editar')): ?>
                    <button class="btn-accion editar"
                            data-membresia="<?= (int) $m['id_membresia'] ?>"
                            data-codigo="<?= htmlspecialchars($m['codigo']) ?>"
                            data-nombre="<?= htmlspecialchars($m['nombre']) ?>"
                            data-porcentaje="<?= (float) $m['porcentajeDescuento'] ?>"
                            data-estado="<?= (int) $m['estado'] ?>">Editar</button>
                  <?php endif; ?>
                  <?php if (tienePermiso('membresias', 'eliminar')): ?>
                    <button class="btn-accion eliminar" data-membresia="<?= (int) $m['id_membresia'] ?>">Eliminar</button>
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
<!-- PESTAÑA: MEMBRESÍAS POR CLIENTE                               -->
<!-- ============================================================= -->
<div class="tab-catalogo" id="tab-asignadas">

  <div class="bloque">
    <div class="bloque-top">
      <div>
        <h2>Membresías asignadas</h2>
        <p class="sub"><?= count($asignaciones) ?> asignaciones activas</p>
      </div>
      <div class="acciones-top">
        <input type="search" class="buscador" id="buscarAsignada" placeholder="Buscar cliente o membresía..." />
        <?php if (tienePermiso('membresias', 'crear')): ?>
          <button class="btn-oro" id="btnAsignarMembresia">+ Asignar membresía</button>
        <?php endif; ?>
      </div>
    </div>

    <div class="tabla-scroll">
      <?php if (empty($asignaciones)): ?>

        <div class="tabla-vacia">
          <p>No hay membresías asignadas aún.</p>
          <small>Usa el botón "+ Asignar membresía" para asignar una.</small>
        </div>

      <?php else: ?>

        <table class="tabla-panel" id="tablaAsignadas">
          <thead>
            <tr>
              <th>CLIENTE</th>
              <th>MEMBRESÍA</th>
              <th>ASIGNADO POR</th>
              <th>ESTADO</th>
              <th>FECHA REGISTRO</th>
              <th class="col-acc">ACCIONES</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($asignaciones as $a): ?>
              <tr data-asignacion="<?= (int) $a['id_membresia_cliente'] ?>">
                <td>
                  <span class="principal"><?= htmlspecialchars($a['nombreCliente'] . ' ' . $a['apellidoCliente']) ?></span>
                  <small><?= htmlspecialchars($a['correo']) ?></small>
                </td>
                <td>
                  <span class="badge rol"><?= htmlspecialchars($a['nombreMembresia']) ?></span>
                  <small><?= number_format((float) $a['porcentajeDescuento'], 2) ?> % descuento</small>
                </td>
                <td><?= htmlspecialchars($a['asignadoPor']) ?></td>
                <td><span class="badge activo">Activo</span></td>
                <td><?= fechaCorta($a['fechaRegistro']) ?></td>
                <td class="col-acc">
                  <?php if (tienePermiso('membresias', 'editar')): ?>
                    <button class="btn-accion editar"
                            data-asignacion="<?= (int) $a['id_membresia_cliente'] ?>"
                            data-cliente="<?= (int) $a['id_cliente'] ?>"
                            data-cliente-nombre="<?= htmlspecialchars($a['nombreCliente'] . ' ' . $a['apellidoCliente']) ?>"
                            
                  <?php endif; ?>
                  <?php if (tienePermiso('membresias', 'eliminar')): ?>
                    <button class="btn-accion eliminar" data-asignacion="<?= (int) $a['id_membresia_cliente'] ?>">Quitar</button>
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
<!-- MODAL: NUEVA / EDITAR MEMBRESÍA                               -->
<!-- ============================================================= -->
<div class="modal-panel" id="modalMembresia">
  <div class="modal-caja">
    <h3 id="modalMembresiaTitulo">Nueva membresía</h3>
    <p class="sub">Tabla membresias.</p>

    <form id="formMembresia" autocomplete="off" onsubmit="return false;">
      <input type="hidden" name="id_membresia" id="inputIdMembresia" value="" />

      <div class="grid-form">
        <div class="campo-panel">
          <label>Código *</label>
          <input type="text" name="codigo" id="inputCodigo" required maxlength="20" placeholder="2002" />
        </div>
        <div class="campo-panel">
          <label>Nombre *</label>
          <input type="text" name="nombre" id="inputNombreMembresia" required maxlength="50" placeholder="Platino" />
        </div>
        <div class="campo-panel">
          <label>% Descuento *</label>
          <input type="number" step="0.01" min="0" max="100" name="porcentajeDescuento" id="inputPorcentaje" required placeholder="5.00" />
        </div>
        <div class="campo-panel">
          <label>Estado</label>
          <select name="estado" id="inputEstadoMembresia">
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

<!-- ============================================================= -->
<!-- MODAL: ASIGNAR MEMBRESÍA A CLIENTE                            -->
<!-- ============================================================= -->
<div class="modal-panel" id="modalAsignarMembresia">
  <div class="modal-caja">
    <h3>Asignar membresía</h3>
    <p class="sub">Tabla membresiaCliente.</p>

    <form id="formAsignarMembresia" autocomplete="off" onsubmit="return false;">
      <input type="hidden" name="id_asignacion" id="inputIdAsignacion" value="" />

      <div class="grid-form">
        <div class="campo-panel ancho">
          <label>Cliente *</label>
          <input type="search" class="buscador" id="buscarClienteAsignar"
                 placeholder="Escribe para buscar cliente..."
                 autocomplete="off" />
          <input type="hidden" name="id_cliente" id="inputIdCliente" value="" />
          <div class="resultados-busqueda" id="resultadosCliente"></div>
          <div class="cliente-seleccionado" id="clienteSeleccionado"></div>
        </div>

        <div class="campo-panel ancho">
          <label>Membresía *</label>
          <select name="id_membresia" id="selectMembresiaAsignar" required>
            <option value="">Selecciona una membresía</option>
            <?php foreach ($membresias as $m): ?>
              <?php if ((int) $m['estado'] === 1): ?>
                <option value="<?= (int) $m['id_membresia'] ?>">
                  <?= htmlspecialchars($m['codigo'] . ' — ' . $m['nombre']) ?>
                  (<?= number_format((float) $m['porcentajeDescuento'], 2) ?>%)
                </option>
              <?php endif; ?>
            <?php endforeach; ?>
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