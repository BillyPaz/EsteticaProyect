<?php
/**
 * modulos/seguridad/accesos/php/vista.php
 * Vista del módulo Accesos:
 * - Crear roles
 * - Asignar rol a un usuario
 */

require_once __DIR__ . '/../../../../dashboard/includes/auth.php';
require_once __DIR__ . '/../../../../dashboard/includes/permisos.php';
require_once __DIR__ . '/../../../../config/conexion.php';

requierePermiso('accesos', 'ver');

$conn = conexionBD();

// =====================================================
// 1. LISTADO DE USUARIOS CON SU ROL (o sin rol)
// =====================================================

$sqlUsuarios = "SELECT
                    u.id_usuario,
                    u.nombres,
                    u.apellidos,
                    u.correo,
                    u.estado AS estadoUsuario,
                    r.id_rol,
                    r.nombreRol,
                    ru.id_rol_usuario,
                    ru.estado AS estadoRolUsuario
                FROM usuarios u
                LEFT JOIN rol_usuario ru ON ru.id_usuario = u.id_usuario
                LEFT JOIN rol r          ON r.id_rol = ru.id_rol
                ORDER BY u.id_usuario ASC";

$usuarios = $conn->query($sqlUsuarios)->fetchAll();

// =====================================================
// 2. LISTADO DE ROLES DISPONIBLES (para el select del modal)
// =====================================================

$sqlRoles = "SELECT id_rol, nombreRol
             FROM rol
             WHERE estado = 1
             ORDER BY nombreRol";
$roles = $conn->query($sqlRoles)->fetchAll();

// =====================================================
// HELPERS
// =====================================================

function iniciales(string $nombres, string $apellidos): string {
    $i = mb_strtoupper(mb_substr($nombres, 0, 1))
       . mb_strtoupper(mb_substr($apellidos, 0, 1));
    return $i ?: 'US';
}
?>

<div class="panel-head">
  <div>
    <span class="eyebrow-dark">Seguridad</span>
    <h1>Accesos <em>y roles</em></h1>
    <p>Crea roles y asígnalos a los usuarios del sistema.</p>
  </div>
  <span class="chip-rol"><?= htmlspecialchars($nombreRol) ?></span>
</div>

<div class="bloque">
  <div class="bloque-top">
    <div>
      <h2>Usuarios y sus roles</h2>
      <p class="sub"><?= count($usuarios) ?> usuarios registrados</p>
    </div>
    <div class="acciones-top">
      <input type="search" class="buscador" id="buscarAcceso" placeholder="Buscar usuario..." />

      <?php if (tienePermiso('accesos', 'crear')): ?>
        <button class="btn-linea" data-modal="modalNuevoRol">+ Nuevo rol</button>
      <?php endif; ?>

      <?php if (tienePermiso('accesos', 'editar')): ?>
        <button class="btn-oro" data-modal="modalAsignarRol">+ Asignar rol</button>
      <?php endif; ?>
    </div>
  </div>

  <div class="tabla-scroll">
    <?php if (empty($usuarios)): ?>

      <div class="tabla-vacia">
        <p>No hay usuarios registrados aún.</p>
      </div>

    <?php else: ?>

      <table class="tabla-panel" id="tablaAccesos">
        <thead>
          <tr>
            <th>Usuario</th>
            <th>Correo</th>
            <th>Rol asignado</th>
            <th>Estado del rol</th>
            <th class="col-acc">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($usuarios as $u): ?>
            <?php
              $tieneRol = !empty($u['id_rol']);
              $rolActivo = $tieneRol && ((int) $u['estadoRolUsuario'] === 1);
            ?>
            <tr data-usuario="<?= (int) $u['id_usuario'] ?>">
              <td>
                <div class="usuario-celda">
                  <div class="mini-avatar">
                    <?= htmlspecialchars(iniciales($u['nombres'], $u['apellidos'])) ?>
                  </div>
                  <div>
                    <span class="principal"><?= htmlspecialchars($u['nombres'] . ' ' . $u['apellidos']) ?></span>
                  </div>
                </div>
              </td>
              <td><?= htmlspecialchars($u['correo']) ?></td>
              <td>
                <?php if ($tieneRol): ?>
                  <span class="badge rol"><?= htmlspecialchars($u['nombreRol']) ?></span>
                <?php else: ?>
                  <span class="vacio-nada">Sin rol</span>
                <?php endif; ?>
              </td>
              <td>
                <?php if (!$tieneRol): ?>
                  <span class="vacio-nada">—</span>
                <?php elseif ($rolActivo): ?>
                  <span class="badge activo">Activo</span>
                <?php else: ?>
                  <span class="badge inactivo">Inactivo</span>
                <?php endif; ?>
              </td>
              <td class="col-acc">
                <?php if (tienePermiso('accesos', 'editar')): ?>
                  <button class="btn-accion editar"
                          data-modal="modalAsignarRol"
                          data-usuario="<?= (int) $u['id_usuario'] ?>"
                          data-rol="<?= (int) ($u['id_rol'] ?? 0) ?>">
                    <?= $tieneRol ? 'Cambiar rol' : 'Asignar rol' ?>
                  </button>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>

    <?php endif; ?>
  </div>
</div>

<!-- ============================================================= -->
<!-- MODAL: NUEVO ROL                                              -->
<!-- ============================================================= -->
<div class="modal-panel" id="modalNuevoRol">
  <div class="modal-caja">
    <h3>Nuevo rol</h3>
    <p class="sub">Crea un rol para asignarlo a los usuarios.</p>

    <form id="formNuevoRol" autocomplete="off" onsubmit="return false;">
      <div class="grid-form">
        <div class="campo-panel ancho">
          <label>Nombre del rol *</label>
          <input type="text" name="nombreRol" required maxlength="25" placeholder="Recepcionista" />
        </div>
        <div class="campo-panel ancho">
          <label>Descripción</label>
          <input type="text" name="descripcion" maxlength="75" placeholder="Encargado de recibir clientes" />
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

<!-- ============================================================= -->
<!-- MODAL: ASIGNAR ROL                                            -->
<!-- ============================================================= -->
<div class="modal-panel" id="modalAsignarRol">
  <div class="modal-caja">
    <h3>Asignar rol</h3>
    <p class="sub">Asigna un rol existente a un usuario del sistema.</p>

    <form id="formAsignarRol" autocomplete="off" onsubmit="return false;">
      <input type="hidden" name="id_rol_usuario" id="inputIdRolUsuario" value="" />

      <div class="grid-form">
        <div class="campo-panel ancho">
          <label>Usuario *</label>
          <select name="id_usuario" id="selectUsuario" required>
            <option value="">Selecciona un usuario</option>
            <?php foreach ($usuarios as $u): ?>
              <option value="<?= (int) $u['id_usuario'] ?>">
                <?= htmlspecialchars($u['nombres'] . ' ' . $u['apellidos']) ?>
                — <?= htmlspecialchars($u['correo']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="campo-panel ancho">
          <label>Rol *</label>
          <select name="id_rol" id="selectRol" required>
            <option value="">Selecciona un rol</option>
            <?php foreach ($roles as $r): ?>
              <option value="<?= (int) $r['id_rol'] ?>">
                <?= htmlspecialchars($r['nombreRol']) ?>
              </option>
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
      </div>

      <div class="modal-acciones">
        <button type="button" class="btn-linea" data-cerrar>Cancelar</button>
        <button type="submit" class="btn-oro">Guardar</button>
      </div>
    </form>
  </div>
</div>