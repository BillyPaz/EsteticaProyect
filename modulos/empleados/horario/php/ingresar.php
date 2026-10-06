<?php
/**
 * modulos/empleados/horario/php/ingresar.php
 * Registra un horario (empleado + día). Responde SIEMPRE en JSON.
 *
 * No incluye auth.php a propósito: auth.php redirige al login con un
 * header Location y aquí el navegador necesita un JSON, no una página.
 */

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

// -----------------------------------------------------
// Respuesta JSON estándar (termina la ejecución)
// -----------------------------------------------------
function responder(int $codigo, bool $ok, string $mensaje, array $extra = []): void
{
    http_response_code($codigo);
    echo json_encode(
        array_merge(['success' => $ok, 'message' => $mensaje], $extra),
        JSON_UNESCAPED_UNICODE
    );
    exit;
}

// -----------------------------------------------------
// 1. Solo POST y solo peticiones AJAX del propio sistema
// -----------------------------------------------------
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    responder(405, false, 'Método no permitido.');
}
if (($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') !== 'XMLHttpRequest') {
    responder(400, false, 'Petición no válida.');
}

// -----------------------------------------------------
// 2. Sesión
// -----------------------------------------------------
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (empty($_SESSION['id_usuario']) || empty($_SESSION['permisos'])) {
    responder(401, false, 'Tu sesión expiró. Inicia sesión de nuevo.', ['sesionExpirada' => true]);
}

// -----------------------------------------------------
// 3. Permiso
// -----------------------------------------------------
require_once __DIR__ . '/../../../../dashboard/includes/permisos.php';
require_once __DIR__ . '/../../../../config/conexion.php';

if (!tienePermiso('emp-horario', 'crear')) {
    responder(403, false, 'No tienes permiso para registrar horarios.');
}

// -----------------------------------------------------
// 4. Validación de datos
// -----------------------------------------------------
const ID_USUARIO_SISTEMA = 1;
const DIAS_VALIDOS = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sabado', 'Domingo'];
const PATRON_HORA  = '/^([01]\d|2[0-3]):[0-5]\d$/';

$idUsuario  = filter_var($_POST['id_usuario'] ?? '', FILTER_VALIDATE_INT);
$dia        = (string) ($_POST['dias'] ?? '');
$horaInicio = trim((string) ($_POST['horaInicio'] ?? ''));
$horaFin    = trim((string) ($_POST['horaFin'] ?? ''));
$estado     = (string) ($_POST['estado'] ?? '1');

if ($idUsuario === false || $idUsuario < 1 || $idUsuario === ID_USUARIO_SISTEMA) {
    responder(422, false, 'Selecciona un empleado.');
}
if (!in_array($dia, DIAS_VALIDOS, true)) {
    responder(422, false, 'Selecciona un día válido.');
}
if (!preg_match(PATRON_HORA, $horaInicio)) {
    responder(422, false, 'La hora de entrada no es válida.');
}
if (!preg_match(PATRON_HORA, $horaFin)) {
    responder(422, false, 'La hora de salida no es válida.');
}
// Formato HH:MM con ceros a la izquierda: la comparación de texto equivale a la de horas
if ($horaFin <= $horaInicio) {
    responder(422, false, 'La hora de salida debe ser mayor que la hora de entrada.');
}
if ($estado !== '0' && $estado !== '1') {
    responder(422, false, 'El estado no es válido.');
}
$estado = (int) $estado;

// -----------------------------------------------------
// 5. Guardado (en transacción)
// -----------------------------------------------------
$conn = conexionBD();

try {
    $conn->beginTransaction();

    // Bloquea la fila del empleado: si llegan dos peticiones iguales a la vez,
    // la segunda espera y luego ve el duplicado que dejó la primera.
    $st = $conn->prepare(
        "SELECT id_usuario FROM usuarios
         WHERE id_usuario = :id AND estado = 1
         FOR UPDATE"
    );
    $st->execute([':id' => $idUsuario]);
    if (!$st->fetch()) {
        $conn->rollBack();
        responder(422, false, 'El empleado seleccionado no existe o está inactivo.');
    }

    // Un solo horario por empleado y día
    $st = $conn->prepare(
        "SELECT 1 FROM horarios_empleados
         WHERE id_usuario = :id AND dias = :dia
         LIMIT 1"
    );
    $st->execute([':id' => $idUsuario, ':dia' => $dia]);
    if ($st->fetch()) {
        $conn->rollBack();
        responder(409, false, 'Este empleado ya tiene un horario para ese día. Usa "Editar" para modificarlo.');
    }

    $st = $conn->prepare(
        "INSERT INTO horarios_empleados (id_usuario, dias, horaInicio, horaFin, estado)
         VALUES (:id, :dia, :inicio, :fin, :estado)"
    );
    $st->execute([
        ':id'     => $idUsuario,
        ':dia'    => $dia,
        ':inicio' => $horaInicio . ':00',
        ':fin'    => $horaFin . ':00',
        ':estado' => $estado,
    ]);

    $idNuevo = (int) $conn->lastInsertId();
    $conn->commit();

    responder(200, true, 'El horario se registró correctamente.', ['id' => $idNuevo]);

} catch (PDOException $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }

    // 1062 = clave duplicada (por si más adelante se agrega un índice único)
    if ((int) ($e->errorInfo[1] ?? 0) === 1062) {
        responder(409, false, 'Este empleado ya tiene un horario para ese día. Usa "Editar" para modificarlo.');
    }

    error_log('[horario/ingresar] ' . $e->getMessage());
    responder(500, false, 'No se pudo guardar el horario. Intenta de nuevo.');
}