<?php
/**
 * modulos/seguridad/accesos/php/asignar_rol.php
 * Procesa el formulario "Asignar rol":
 * - Si el usuario NO tiene rol → INSERT en rol_usuario
 * - Si el usuario YA tiene rol → UPDATE en rol_usuario
 * Devuelve JSON.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../../../dashboard/includes/auth.php';
require_once __DIR__ . '/../../../../dashboard/includes/permisos.php';
require_once __DIR__ . '/../../../../config/conexion.php';

// 1. Validar permiso
if (!tienePermiso('accesos', 'editar')) {
    echo json_encode([
        'success' => false,
        'message' => 'No tienes permiso para asignar roles.'
    ]);
    exit;
}

// 2. Recoger datos
$idUsuario = (int) ($_POST['id_usuario'] ?? 0);
$idRol     = (int) ($_POST['id_rol']     ?? 0);
$estado    = (int) ($_POST['estado']     ?? 1);

// 3. Validaciones
$errores = [];

if ($idUsuario <= 0) $errores[] = 'Debes seleccionar un usuario.';
if ($idRol     <= 0) $errores[] = 'Debes seleccionar un rol.';
if (!in_array($estado, [0, 1], true)) $estado = 1;

if (!empty($errores)) {
    echo json_encode([
        'success' => false,
        'message' => implode(' ', $errores)
    ]);
    exit;
}

try {
    $conn = conexionBD();

    // 4. Verificar que el usuario exista
    $stmtU = $conn->prepare("SELECT id_usuario FROM usuarios WHERE id_usuario = :id LIMIT 1");
    $stmtU->execute([':id' => $idUsuario]);
    if (!$stmtU->fetch()) {
        echo json_encode([
            'success' => false,
            'message' => 'El usuario seleccionado no existe.'
        ]);
        exit;
    }

    // 5. Verificar que el rol exista y esté activo
    $stmtR = $conn->prepare("SELECT id_rol FROM rol WHERE id_rol = :id AND estado = 1 LIMIT 1");
    $stmtR->execute([':id' => $idRol]);
    if (!$stmtR->fetch()) {
        echo json_encode([
            'success' => false,
            'message' => 'El rol seleccionado no existe o está inactivo.'
        ]);
        exit;
    }

    // 6. Ver si el usuario ya tiene un rol asignado
    $stmtActual = $conn->prepare("SELECT id_rol_usuario FROM rol_usuario WHERE id_usuario = :id LIMIT 1");
    $stmtActual->execute([':id' => $idUsuario]);
    $rolActual = $stmtActual->fetch();

    if ($rolActual) {
        // --- UPDATE ---
        $sql = "UPDATE rol_usuario
                SET id_rol = :idRol, estado = :estado
                WHERE id_rol_usuario = :idRolUsuario";

        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':idRol'         => $idRol,
            ':estado'        => $estado,
            ':idRolUsuario'  => $rolActual['id_rol_usuario'],
        ]);

        echo json_encode([
            'success' => true,
            'message' => 'Rol actualizado correctamente.'
        ]);

    } else {
        // --- INSERT ---
        $sql = "INSERT INTO rol_usuario (id_rol, id_usuario, estado)
                VALUES (:idRol, :idUsuario, :estado)";

        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':idRol'     => $idRol,
            ':idUsuario' => $idUsuario,
            ':estado'    => $estado,
        ]);

        echo json_encode([
            'success' => true,
            'message' => 'Rol asignado correctamente.'
        ]);
    }

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al guardar en la base de datos.'
    ]);
}