<?php
/**
 * modulos/empleados/registrar/php/ingresar.php
 * Procesa el formulario "Nuevo usuario" y guarda el empleado en BD.
 * Devuelve JSON.
 */

session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../../../dashboard/includes/auth.php';
require_once __DIR__ . '/../../../../dashboard/includes/permisos.php';
require_once __DIR__ . '/../../../../config/conexion.php';

// 1. Validar permiso
if (!tienePermiso('emp-registrar', 'crear')) {
    echo json_encode([
        'success' => false,
        'message' => 'No tienes permiso para crear usuarios.'
    ]);
    exit;
}

// 2. Recoger datos del POST
$nombres    = trim($_POST['nombres']    ?? '');
$apellidos  = trim($_POST['apellidos']  ?? '');
$correo     = trim($_POST['correo']     ?? '');
$telefono   = trim($_POST['telefono']   ?? '');
$direccion  = trim($_POST['direccion']  ?? '');
$password   = $_POST['password']        ?? '';
$estado     = (int) ($_POST['estado']   ?? 1);

// 3. Validaciones básicas
$errores = [];

if ($nombres === '')    $errores[] = 'El nombre es obligatorio.';
if ($apellidos === '')  $errores[] = 'El apellido es obligatorio.';
if ($correo === '')     $errores[] = 'El correo es obligatorio.';
if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) $errores[] = 'El correo no tiene formato válido.';
if ($telefono === '')   $errores[] = 'El teléfono es obligatorio.';
if ($password === '')   $errores[] = 'La contraseña es obligatoria.';
if (strlen($password) < 6) $errores[] = 'La contraseña debe tener al menos 6 caracteres.';
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

    // 4. Verificar que el correo no exista
    $stmtCheck = $conn->prepare("SELECT id_usuario FROM usuarios WHERE correo = :correo LIMIT 1");
    $stmtCheck->execute([':correo' => $correo]);

    if ($stmtCheck->fetch()) {
        echo json_encode([
            'success' => false,
            'message' => 'Ya existe un usuario con ese correo.'
        ]);
        exit;
    }

    // 5. Hashear la contraseña
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    // 6. Insertar el usuario
    $sql = "INSERT INTO usuarios
                (nombres, apellidos, correo, telefono, direccion, passwordHash, estado, ultimoAcceso)
            VALUES
                (:nombres, :apellidos, :correo, :telefono, :direccion, :passwordHash, :estado, NOW())";

    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':nombres'      => $nombres,
        ':apellidos'    => $apellidos,
        ':correo'       => $correo,
        ':telefono'     => $telefono,
        ':direccion'    => $direccion ?: null,
        ':passwordHash' => $passwordHash,
        ':estado'       => $estado,
    ]);

    $idNuevo = (int) $conn->lastInsertId();

    echo json_encode([
        'success'    => true,
        'message'    => 'Usuario creado correctamente.',
        'id_usuario' => $idNuevo
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al guardar en la base de datos.'
    ]);
}