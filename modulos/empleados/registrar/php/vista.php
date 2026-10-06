<?php
/**
 * modulos/empleados/registrar/php/vista.php
 * Listado de usuarios + modal para crear uno nuevo.
 * Solo datos básicos del empleado.
 * El rol se asigna en "Seguridad → Accesos".
 */

require_once __DIR__ . '/../../../../dashboard/includes/auth.php';
require_once __DIR__ . '/../../../../dashboard/includes/permisos.php';
require_once __DIR__ . '/../../../../config/conexion.php';

requierePermiso('emp-registrar', 'ver');

$conn = conexionBD();

// =====================================================
// LISTADO DE USUARIOS
// =====================================================

$sqlUsuarios = "SELECT
                    u.id_usuario,
                    u.nombres,
                    u.apellidos,
                    u.correo,
                    u.telefono,
                    u.direccion,
                    u.fechaRegistro,
                    u.fechaActualizacion,
                    u.ultimoAcceso,
                    u.estado
                FROM usuarios u
                ORDER BY u.id_usuario ASC";

$usuarios = $conn->query($sqlUsuarios)->fetchAll();

// =====================================================
// HELPERS
// =====================================================

function fechaCorta(?string $fecha): string {
    if (!$fecha) return '—';
    $dt = new DateTime($fecha);
    return $dt->format('d/m/Y H:i');
}
?>

<div class="panel-head">
  <div>
    <span class="eyebrow-dark">Empleados</span>
    <h1>Registrar <em>empleados</em></h1>
    <p>Tabla usuarios — datos personales, acceso y estado.</p>
  </div>
  <span class="chip-rol"><?= htmlspecialchars($nombreRol) ?></span>
</div>

<div class="bloque">
  <div class="bloque-top">
    <div>
      <h2>Usuarios registrados</h2>
      <p class="sub"><?= count($usuarios) ?> registrados</p>
    </div>
    <div class="acciones-top">
      <input type="search" class="buscador" id="buscarUsuario" placeholder="Buscar por nombres o correo..." />
      <?php if (tienePermiso('emp-registrar', 'crear')): ?>
        <button class="btn-oro" data-modal="modalUsuario">+ Nuevo usuario</button>
      <?php endif; ?>
    </div>
  </div>

  <div class="tabla-scroll">
    <?php if (empty($usuarios)): ?>

      <div class="tabla-vacia">
        <p>No hay usuarios registrados aún.</p>
        <small>Usa el botón "+ Nuevo usuario" para crear el primero.</small>
      </div>

    <?php else: ?>

      <table class="tabla-panel" id="tablaUsuarios">
        <thead>
          <tr>
            <th>Nombres</th>
            <th>Apellidos</th>
            <th>Correo</th>
            <th>Teléfono</th>
            <th>Dirección</th>
            <th>Fecha registro</th>
            <th>Último acceso</th>
            <th>Estado</th>
            <th class="col-acc">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($usuarios as $u): ?>
            <?php
              $activo      = ((int) $u['estado'] === 1);
              $claseEstado = $activo ? 'activo' : 'inactivo';
              $textoEstado = $activo ? 'Activo' : 'Inactivo';
              $textoAccion = $activo ? 'Inactivar' : 'Activar';
            ?>
            <tr data-usuario="<?= (int) $u['id_usuario'] ?>">
              <td class="principal"><?= htmlspecialchars($u['nombres']) ?></td>
              <td><?= htmlspecialchars($u['apellidos']) ?></td>
              <td><?= htmlspecialchars($u['correo']) ?></td>
              <td><?= htmlspecialchars($u['telefono']) ?></td>
              <td><?= htmlspecialchars($u['direccion'] ?? '—') ?></td>
              <td><?= fechaCorta($u['fechaRegistro']) ?></td>
              <td><?= fechaCorta($u['ultimoAcceso']) ?></td>
              <td><span class="badge <?= $claseEstado ?>"><?= $textoEstado ?></span></td>
              <td class="col-acc">
                <?php if (tienePermiso('emp-registrar', 'editar')): ?>
                  <button class="btn-accion editar"
                          data-modal="modalEditarUsuario"
                          data-usuario="<?= (int) $u['id_usuario'] ?>">Editar</button>
                <?php endif; ?>
                <?php if (tienePermiso('emp-registrar', 'editar')): ?>
                  <button class="btn-accion estado" data-usuario="<?= (int) $u['id_usuario'] ?>"><?= $textoAccion ?></button>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>

    <?php endif; ?>
  </div>
</div>

<!-- ============ MODAL: NUEVO USUARIO ============ -->
<div class="modal-panel" id="modalUsuario">
  <div class="modal-caja">
    <h3>Nuevo usuario</h3>
    <p class="sub">Tabla usuarios.</p>

    <form id="formNuevoUsuario" autocomplete="off" onsubmit="return false;">
      <div class="grid-form">
        <div class="campo-panel">
          <label>Nombres</label>
          <input type="text" name="nombres" required maxlength="75" placeholder="Otto" />
        </div>
        <div class="campo-panel">
          <label>Apellidos</label>
          <input type="text" name="apellidos" required maxlength="75" placeholder="Mérida" />
        </div>
        <div class="campo-panel">
          <label>Correo</label>
          <input type="email" name="correo" required maxlength="75" placeholder="correo@europa.com" />
        </div>
        <div class="campo-panel">
          <label>Teléfono</label>
          <input type="tel" name="telefono" required maxlength="20" placeholder="4412 8890" />
        </div>
        <div class="campo-panel ancho">
          <label>Dirección</label>
          <input type="text" name="direccion" maxlength="100" placeholder="3a Av. 4-15 Zona 1" />
        </div>
        <div class="campo-panel">
          <label>Contraseña</label>
          <input type="password" name="password" required minlength="6" placeholder="••••••••" />
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

