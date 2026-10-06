<?php
/**
 * modulos/mantenimiento/clientes/php/vista.php
 * Listado de clientes + modales para crear y editar.
 * Tabla: clientes
 *
 * - correo y telefono2 son opcionales.
 * - telefono se muestra formateado; si hay telefono2, se muestran los dos
 *   separados por "/" dentro de la misma columna.
 * - Sin botón Eliminar: el historial de citas/ventas/membresías depende
 *   del cliente, así que solo se activa/inactiva.
 */

require_once __DIR__ . '/../../../../dashboard/includes/auth.php';
require_once __DIR__ . '/../../../../dashboard/includes/permisos.php';
require_once __DIR__ . '/../../../../config/conexion.php';

requierePermiso('clientes', 'ver');

$conn = conexionBD();

// =====================================================
// GÉNEROS
// clave = valor exacto del ENUM en la BD | valor = texto visible
// =====================================================
$GENEROS = [
    'MASCULINO'       => 'Masculino',
    'FEMENINO'        => 'Femenino',
    'OTRO'            => 'Otro',
    'NO_ESPECIFICADO' => 'No especificado',
];

// =====================================================
// LISTADO DE CLIENTES
// =====================================================
$clientes = $conn->query(
    "SELECT
        id_cliente,
        nombreCliente,
        apellidoCliente,
        telefono,
        telefono2,
        correo,
        genero,
        fechaRegistro,
        fechaActualizacion,
        estado
     FROM clientes
     ORDER BY nombreCliente ASC, apellidoCliente ASC"
)->fetchAll();

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

// "55124478" -> "5512 4478" (solo para teléfonos de 8 dígitos; si no calza, se muestra tal cual)
if (!function_exists('formatoTelefono')) {
    function formatoTelefono(?string $telefono): string {
        if (!$telefono) return '';
        $digitos = preg_replace('/\D/', '', $telefono);
        if (strlen($digitos) === 8) {
            return substr($digitos, 0, 4) . ' ' . substr($digitos, 4);
        }
        return $telefono;
    }
}

$opcionesGenero = '';
foreach ($GENEROS as $valor => $texto) {
    $opcionesGenero .= '<option value="' . e($valor) . '">' . e($texto) . '</option>';
}

$puedeCrear  = tienePermiso('clientes', 'crear');
$puedeEditar = tienePermiso('clientes', 'editar');

// =====================================================
// CAMPOS DEL FORMULARIO (idénticos en "nuevo" y "editar")
// =====================================================
ob_start();
?>
        <div class="campo-panel">
          <label>Nombre cliente</label>
          <input type="text" name="nombreCliente" required maxlength="50" placeholder="María Fernanda" />
        </div>
        <div class="campo-panel">
          <label>Apellido cliente</label>
          <input type="text" name="apellidoCliente" required maxlength="50" placeholder="López" />
        </div>
        <div class="campo-panel">
          <label>Teléfono</label>
          <input type="tel" name="telefono" required placeholder="5512 4478" />
        </div>
        <div class="campo-panel">
          <label>Teléfono 2 (opcional)</label>
          <input type="tel" name="telefono2" placeholder="4478 9012" />
        </div>
        <div class="campo-panel ancho">
          <label>Correo (opcional)</label>
          <input type="email" name="correo" maxlength="75" placeholder="cliente@correo.com" />
        </div>
        <div class="campo-panel">
          <label>Género</label>
          <select name="genero">
            <?= $opcionesGenero ?>
          </select>
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
    <span class="eyebrow-dark">Cartera</span>
    <h1>Clientes <em>registrados</em></h1>
    <p>Datos de contacto y estado de los clientes de la estética.</p>
  </div>
  <span class="chip-rol"><?= e($nombreRol) ?></span>
</div>

<div class="bloque">
  <div class="bloque-top">
    <div>
      <h2>Listado de clientes</h2>
      <p class="sub"><?= count($clientes) ?> registrados</p>
    </div>
    <div class="acciones-top">
      <input type="search" class="buscador" id="buscarCliente" placeholder="Buscar cliente..." />
      <?php if ($puedeCrear): ?>
        <button class="btn-oro" data-modal="modalCliente">+ Nuevo cliente</button>
      <?php endif; ?>
    </div>
  </div>

  <div class="tabla-scroll">
    <?php if (empty($clientes)): ?>

      <div class="tabla-vacia">
        <p>No hay clientes registrados aún.</p>
        <?php if ($puedeCrear): ?>
          <small>Usa el botón "+ Nuevo cliente" para registrar el primero.</small>
        <?php endif; ?>
      </div>

    <?php else: ?>

      <table class="tabla-panel" id="tablaClientes">
        <thead>
          <tr>
            <th>Nombre cliente</th>
            <th>Apellido cliente</th>
            <th>Teléfono</th>
            <th>Correo</th>
            <th>Género</th>
            <th>Fecha registro</th>
            <th>Fecha actualización</th>
            <th>Estado</th>
            <?php if ($puedeEditar): ?>
              <th class="col-acc">Acciones</th>
            <?php endif; ?>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($clientes as $c): ?>
            <?php
              $activo      = ((int) $c['estado'] === 1);
              $claseEstado = $activo ? 'activo' : 'inactivo';
              $textoEstado = $activo ? 'Activo' : 'Inactivo';

              $tel1 = formatoTelefono($c['telefono']);
              $tel2 = formatoTelefono($c['telefono2']);
              $telefonos = $tel2 !== '' ? ($tel1 . ' / ' . $tel2) : $tel1;

              $textoGenero = $GENEROS[$c['genero']] ?? (string) $c['genero'];
            ?>
            <tr data-cliente="<?= (int) $c['id_cliente'] ?>">
              <td class="principal"><?= e($c['nombreCliente']) ?></td>
              <td><?= e($c['apellidoCliente']) ?></td>
              <td><?= e($telefonos) ?></td>
              <td><?= e($c['correo'] ?: '—') ?></td>
              <td><?= e($textoGenero) ?></td>
              <td><?= fechaHoraDMA($c['fechaRegistro']) ?></td>
              <td><?= fechaHoraDMA($c['fechaActualizacion']) ?></td>
              <td><span class="badge <?= $claseEstado ?>"><?= $textoEstado ?></span></td>
              <?php if ($puedeEditar): ?>
                <td class="col-acc">
                  <button class="btn-accion editar"
                          data-modal="modalEditarCliente"
                          data-cliente="<?= (int) $c['id_cliente'] ?>"
                          data-nombre="<?= e($c['nombreCliente']) ?>"
                          data-apellido="<?= e($c['apellidoCliente']) ?>"
                          data-telefono="<?= e($tel1) ?>"
                          data-telefono2="<?= e($tel2) ?>"
                          data-correo="<?= e($c['correo'] ?? '') ?>"
                          data-genero="<?= e($c['genero']) ?>"
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
<!-- ============ MODAL: NUEVO CLIENTE ============ -->
<div class="modal-panel" id="modalCliente">
  <div class="modal-caja">
    <h3>Nuevo cliente</h3>
    <p class="sub">Registra los datos de contacto del cliente.</p>

    <form id="formNuevoCliente" autocomplete="off" onsubmit="return false;">
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
<!-- ============ MODAL: EDITAR CLIENTE ============ -->
<div class="modal-panel" id="modalEditarCliente">
  <div class="modal-caja">
    <h3>Editar cliente</h3>
    <p class="sub">Modifica los datos de contacto o el estado del cliente.</p>

    <form id="formEditarCliente" autocomplete="off" onsubmit="return false;">
      <input type="hidden" name="id_cliente" value="" />

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