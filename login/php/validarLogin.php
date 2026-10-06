<?php
// login/php/validarLogin.php
// Valida las credenciales del login y crea la sesión del usuario.

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json');

// Ruta al conexion.php que vive en la raíz del proyecto
require __DIR__ . '/../../config/conexion.php';

$conn = conexionBD();

$username = trim($_POST['user']     ?? '');
$password = trim($_POST['password'] ?? '');

// --- Validación básica ---
if ($username === '' || $password === '') {
    echo json_encode([
        'success' => false,
        'message' => 'Usuario y contraseña son obligatorios'
    ]);
    exit;
}

try {
    // 1) Buscar usuario activo por correo
    $query = "SELECT * FROM usuarios
              WHERE correo = :username AND estado = 1
              LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':username', $username);
    $stmt->execute();
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        echo json_encode([
            'success' => false,
            'message' => 'Usuario no existente o inactivo'
        ]);
        exit;
    }

    // 2) Verificar contraseña (soporta hash bcrypt/argon2 y texto plano)
    $hashAlmacenado = $usuario['passwordHash'];
    $esHashReal = (
        str_starts_with($hashAlmacenado, '$2y$') ||
        str_starts_with($hashAlmacenado, '$2a$') ||
        str_starts_with($hashAlmacenado, '$argon2')
    );

    if ($esHashReal) {
        // Contraseña hasheada → usar password_verify
        if (!password_verify($password, $hashAlmacenado)) {
            echo json_encode([
                'success' => false,
                'message' => 'Usuario o contraseña incorrecta'
            ]);
            exit;
        }
    } else {
        // Contraseña en texto plano (usuarios viejos) → comparación directa
        if ($password !== $hashAlmacenado) {
            echo json_encode([
                'success' => false,
                'message' => 'Usuario o contraseña incorrecta'
            ]);
            exit;
        }
    }

    $idUsuario = (int) $usuario['id_usuario'];

    // 3) Traer permisos desde la vista
    $query2 = "SELECT
                    id_rol, nombreRol,
                    moduloCodigo, moduloNombre, moduloIcono, moduloOrden,
                    accionCodigo, accionNombre, accionIcono, accionRuta, accionOrden,
                    puedeCrear, puedeModificar, puedeConsultar, puedeEliminar
               FROM view_usuario_modulos
               WHERE id_usuario = :idUsuario
                 AND puedeConsultar = 1
               ORDER BY moduloOrden, accionOrden";
    $stmt2 = $conn->prepare($query2);
    $stmt2->bindParam(':idUsuario', $idUsuario, PDO::PARAM_INT);
    $stmt2->execute();
    $modulos = $stmt2->fetchAll(PDO::FETCH_ASSOC);

    if (empty($modulos)) {
        echo json_encode([
            'success' => false,
            'message' => 'Este usuario no tiene permisos de acceso. Contacte al departamento de informática.'
        ]);
        exit;
    }

    // 4) Limpiar sesión previa y regenerar el ID por seguridad
    session_unset();
    session_regenerate_id(true);

    $_SESSION['id_usuario'] = $idUsuario;
    $_SESSION['nombre']     = $usuario['nombres'] . ' ' . $usuario['apellidos'];
    $_SESSION['correo']     = $usuario['correo'];
    $_SESSION['id_rol']     = $modulos[0]['id_rol']    ?? null;
    $_SESSION['nombre_rol'] = $modulos[0]['nombreRol']  ?? null;
    $_SESSION['permisos']   = $modulos;

    // 5) Actualizar último acceso
    $upd = $conn->prepare("UPDATE usuarios SET ultimoAcceso = NOW() WHERE id_usuario = :id");
    $upd->execute([':id' => $idUsuario]);

    echo json_encode([
        'success'   => true,
        'idUsuario' => $idUsuario,
        'nombre'    => $_SESSION['nombre'],
        'modulos'   => $modulos
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error en el servidor'
    ]);
}