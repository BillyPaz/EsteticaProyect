<?php
/**
 * modulos/seguridad/modulos/php/vista.php
 * Vista del módulo "Módulos":
 * - Selector de usuario
 * - Grid de acciones con switches
 * - Modal de permisos (ver/crear/editar/eliminar)
 */

require_once __DIR__ . '/../../../../dashboard/includes/auth.php';
require_once __DIR__ . '/../../../../dashboard/includes/permisos.php';
require_once __DIR__ . '/../../../../config/conexion.php';
require_once __DIR__ . '/../../../../dashboard/includes/iconos.php';

requierePermiso('modulos', 'ver');

$conn = conexionBD();

// =====================================================
// 1. LISTADO DE USUARIOS (con su rol actual)
// =====================================================

$sqlUsuarios = "SELECT
                    u.id_usuario,
                    u.nombres,
                    u.apellidos,
                    u.correo,
                    r.id_rol,
                    r.nombreRol
                FROM usuarios u
                LEFT JOIN rol_usuario ru ON ru.id_usuario = u.id_usuario AND ru.estado = 1
                LEFT JOIN rol r          ON r.id_rol = ru.id_rol
                WHERE u.estado = 1
                ORDER BY u.nombres, u.apellidos";

$usuarios = $conn->query($sqlUsuarios)->fetchAll();

// =====================================================
// 2. LISTADO DE TODAS LAS ACCIONES (con su grupo)
// =====================================================

$sqlAcciones = "SELECT
                    a.id_accion,
                    a.codigo,
                    a.nombre,
                    a.icono,
                    a.orden,
                    m.id_modulo,
                    m.codigo AS moduloCodigo,
                    m.nombre AS moduloNombre,
                    m.icono  AS moduloIcono,
                    m.orden  AS moduloOrden
                FROM acciones a
                INNER JOIN modulo m ON m.id_modulo = a.id_modulo
                WHERE a.estado = 1 AND m.estado = 1
                ORDER BY m.orden, a.orden";

$acciones = $conn->query($sqlAcciones)->fetchAll();

// Total de acciones disponibles
$totalAcciones = count($acciones);
?>

<div class="panel-head">
  <div>
    <span class="eyebrow-dark">Ajustes</span>
    <h1>Asignación de <em>módulos</em></h1>
    <p>Selecciona un usuario y define qué módulos puede utilizar.</p>
  </div>
  <span class="chip-rol"><?= htmlspecialchars($nombreRol) ?></span>
</div>

<div class="bloque">
  <div class="bloque-top">
    <div>
      <h2>Selecciona un usuario</h2>
      <p class="sub">Los módulos se activan según el trabajo que realiza.</p>
    </div>
    <div class="acciones-top">
      <select class="select-filtro" id="selectEmpleadoModulo">
        <option value="">Selecciona un usuario</option>
        <?php foreach ($usuarios as $u): ?>
          <option value="<?= (int) $u['id_usuario'] ?>"
                  data-rol="<?= htmlspecialchars($u['nombreRol'] ?? '') ?>">
            <?= (int) $u['id_usuario'] ?> — <?= htmlspecialchars($u['nombres'] . ' ' . $u['apellidos']) ?>
          </option>
        <?php endforeach; ?>
      </select>
      <span class="badge rol" id="rolEmpleado">—</span>
    </div>
  </div>
</div>

<div class="metricas">
  <div class="metrica">
    <strong id="numAsignados">0</strong>
    <span>Módulos asignados</span>
  </div>
  <div class="metrica">
    <strong id="numDisponibles"><?= $totalAcciones ?></strong>
    <span>Módulos disponibles</span>
  </div>
  <div class="metrica">
    <strong id="rolActual">—</strong>
    <span>Rol del usuario</span>
  </div>
</div>

<div class="bloque">
  <div class="bloque-top">
    <div>
      <h2>Módulos del sistema</h2>
      <p class="sub">Activa el switch para dar acceso al usuario seleccionado.</p>
    </div>
    <div class="acciones-top">
      <input type="search" class="buscador" id="buscarModulo" placeholder="Buscar módulo..." />
      <button class="btn-oro" id="btnGuardarAsignacion">Guardar asignación</button>
    </div>
  </div>

  <div class="grid-modulos" id="gridModulos">

    <?php foreach ($acciones as $acc): ?>
      <div class="card-modulo"
           data-modulo="<?= htmlspecialchars($acc['codigo']) ?>"
           data-id-accion="<?= (int) $acc['id_accion'] ?>"
           data-nombre="<?= htmlspecialchars($acc['nombre']) ?>">

        <div class="modulo-top">
          <span class="modulo-icon"><?= iconoSVG($acc['icono']) ?></span>
          <label class="switch">
            <input type="checkbox" class="chk-modulo" disabled />
            <span class="track"></span>
          </label>
        </div>
        <h4><?= htmlspecialchars($acc['nombre']) ?></h4>
        <span class="badge inactivo estado-modulo">No asignado</span>
      </div>
    <?php endforeach; ?>

  </div>
</div>

<!-- ============================================================= -->
<!-- MODAL: PERMISOS POR MÓDULO                                     -->
<!-- ============================================================= -->
<div class="modal-panel" id="modalPermisos">
  <div class="modal-caja">
    <h3 id="modalPermisosTitulo">Permisos del módulo</h3>
    <p class="sub">Marca las acciones que quieres otorgar.</p>

    <form id="formPermisos" autocomplete="off" onsubmit="return false;">
      <input type="hidden" name="codigoModulo" id="inputCodigoModulo" value="" />
      <input type="hidden" name="idAccion"     id="inputIdAccion"     value="" />

      <div class="grid-permisos">
        <label class="check-permiso">
          <input type="checkbox" name="puedeConsultar" value="1" />
          <span class="ico">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M2.5 12S6 5 12 5s9.5 7 9.5 7-3.5 7-9.5 7-9.5-7-9.5-7z"/><circle cx="12" cy="12" r="3"/></svg>
          </span>
          <span>Ver</span>
        </label>

        <label class="check-permiso">
          <input type="checkbox" name="puedeCrear" value="1" />
          <span class="ico">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
          </span>
          <span>Crear</span>
        </label>

        <label class="check-permiso">
          <input type="checkbox" name="puedeModificar" value="1" />
          <span class="ico">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4z"/></svg>
          </span>
          <span>Editar</span>
        </label>

        <label class="check-permiso">
          <input type="checkbox" name="puedeEliminar" value="1" />
          <span class="ico">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
          </span>
          <span>Eliminar</span>
        </label>
      </div>

      <div class="modal-acciones">
        <button type="button" class="btn-linea" data-cerrar>Cancelar</button>
        <button type="submit" class="btn-oro">Aplicar</button>
      </div>
    </form>
  </div>
</div>