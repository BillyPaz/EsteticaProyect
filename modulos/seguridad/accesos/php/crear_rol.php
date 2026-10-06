<?php
/**
 * modulos/seguridad/accesos/php/crear_rol.php
 * Procesa el formulario "Nuevo rol" y guarda en la tabla rol.
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
if (!tienePermiso('accesos', 'crear')) {
    echo json_encode([
        'success' => false,
        'message' => 'No tienes permiso para crear roles.'
    ]);
    exit;
}

// 2. Recoger datos
$nombreRol   = trim($_POST['nombreRol']   ?? '');
$descripcion = trim($_POST['descripcion'] ?? '');
$estado      = (int) ($_POST['estado']    ?? 1);

// 3. Validaciones
$errores = [];

if ($nombreRol === '') {
    $errores[] = 'El nombre del rol es obligatorio.';
}
if (mb_strlen($nombreRol) > 25) {
    $errores[] = 'El nombre del rol no puede tener más de 25 caracteres.';
}
if (!in_array($estado, [0, 1], true)) {
    $estado = 1;
}

if (!empty($errores)) {
    echo json_encode([
        'success' => false,
        'message' => implode(' ', $errores)
    ]);
    exit;
}

try {
    $conn = conexionBD();

    // 4. Verificar que no exista otro rol con el mismo nombre
    $stmtCheck = $conn->prepare("SELECT id_rol FROM rol WHERE nombreRol = :nombre LIMIT 1");
    $stmtCheck->execute([':nombre' => $nombreRol]);

    if ($stmtCheck->fetch()) {
        echo json_encode([
            'success' => false,
            'message' => 'Ya existe un rol con ese nombre.'
        ]);
        exit;
    }

    // 5. Insertar el rol
    $sql = "INSERT INTO rol (nombreRol, descripcion, estado, esSistema)
            VALUES (:nombre, :descripcion, :estado, 0)";

    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':nombre'      => $nombreRol,
        ':descripcion' => $descripcion ?: null,
        ':estado'      => $estado,
    ]);

    $idRol = (int) $conn->lastInsertId();

    echo json_encode([
        'success' => true,
        'message' => 'Rol creado correctamente.',
        'id_rol'  => $idRol
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al guardar en la base de datos.'
    ]);
}