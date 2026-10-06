<?php
/**
 * modulos/seguridad/modulos/php/cargar_usuario.php
 * Devuelve el rol y los permisos actuales del rol de un usuario.
 * Se llama por AJAX al seleccionar un usuario en el <select>.
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
if (!tienePermiso('modulos', 'ver')) {
    echo json_encode([
        'success' => false,
        'message' => 'No tienes permiso para ver esta información.'
    ]);
    exit;
}

// 2. Recoger datos
$idUsuario = (int) ($_GET['id_usuario'] ?? 0);

if ($idUsuario <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'ID de usuario inválido.'
    ]);
    exit;
}

try {
    $conn = conexionBD();

    // 3. Verificar que el usuario exista y esté activo
    $stmtU = $conn->prepare("SELECT id_usuario, nombres, apellidos
                             FROM usuarios
                             WHERE id_usuario = :id AND estado = 1
                             LIMIT 1");
    $stmtU->execute([':id' => $idUsuario]);
    $usuario = $stmtU->fetch();

    if (!$usuario) {
        echo json_encode([
            'success' => false,
            'message' => 'El usuario no existe o está inactivo.'
        ]);
        exit;
    }

    // 4. Traer el rol del usuario (puede no tener)
    $stmtRol = $conn->prepare("SELECT r.id_rol, r.nombreRol, r.descripcion
                               FROM rol_usuario ru
                               INNER JOIN rol r ON r.id_rol = ru.id_rol
                               WHERE ru.id_usuario = :id AND ru.estado = 1
                               LIMIT 1");
    $stmtRol->execute([':id' => $idUsuario]);
    $rol = $stmtRol->fetch();

    // 5. Si tiene rol, traer sus permisos actuales
    $permisos = [];
    if ($rol) {
        $stmtPerm = $conn->prepare("SELECT
                                        a.codigo        AS accionCodigo,
                                        ar.puedeConsultar,
                                        ar.puedeCrear,
                                        ar.puedeModificar,
                                        ar.puedeEliminar
                                    FROM accion_rol ar
                                    INNER JOIN acciones a ON a.id_accion = ar.id_accion
                                    WHERE ar.id_rol = :idRol AND ar.estado = 1");
        $stmtPerm->execute([':idRol' => (int) $rol['id_rol']]);
        $permisos = $stmtPerm->fetchAll();
    }

    // 6. Devolver respuesta
    echo json_encode([
        'success'     => true,
        'usuario' => [
            'id_usuario' => (int) $usuario['id_usuario'],
            'nombre'     => $usuario['nombres'] . ' ' . $usuario['apellidos'],
        ],
        'rol' => $rol ? [
            'id_rol'    => (int) $rol['id_rol'],
            'nombreRol' => $rol['nombreRol'],
            'descripcion' => $rol['descripcion'],
        ] : null,
        'permisos' => $permisos,
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error en el servidor.'
    ]);
}