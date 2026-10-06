<?php
/**
 * modulos/empleados/horario/php/editar.php
 * Actualiza un horario existente. Responde SIEMPRE en JSON.
 *
 * No incluye auth.php a propósito (redirige al login con header Location
 * y aquí el navegador necesita un JSON).
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

if (!tienePermiso('emp-horario', 'editar')) {
    responder(403, false, 'No tienes permiso para modificar horarios.');
}

// -----------------------------------------------------
// 4. Validación de datos
// -----------------------------------------------------
const ID_USUARIO_SISTEMA = 1;
const DIAS_VALIDOS = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sabado', 'Domingo'];
const PATRON_HORA  = '/^([01]\d|2[0-3]):[0-5]\d$/';

$idHorario  = filter_var($_POST['id_horario'] ?? '', FILTER_VALIDATE_INT);
$idUsuario  = filter_var($_POST['id_usuario'] ?? '', FILTER_VALIDATE_INT);
$dia        = (string) ($_POST['dias'] ?? '');
$horaInicio = trim((string) ($_POST['horaInicio'] ?? ''));
$horaFin    = trim((string) ($_POST['horaFin'] ?? ''));
$estado     = (string) ($_POST['estado'] ?? '');

if ($idHorario === false || $idHorario < 1) {
    responder(422, false, 'No se pudo identificar el horario a modificar.');
}
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
// 5. Actualización (en transacción)
// -----------------------------------------------------
$conn = conexionBD();

try {
    $conn->beginTransaction();

    // El horario debe existir (y queda bloqueado mientras se edita)
    $st = $conn->prepare(
        "SELECT id_usuario FROM horarios_empleados
         WHERE id_horario_usuario = :id
         FOR UPDATE"
    );
    $st->execute([':id' => $idHorario]);
    $actual = $st->fetch();

    if (!$actual) {
        $conn->rollBack();
        responder(404, false, 'Este horario ya no existe. Actualiza la lista e intenta de nuevo.');
    }

    // Bloquea la fila del empleado destino para serializar la revisión de duplicados
    $st = $conn->prepare(
        "SELECT estado FROM usuarios
         WHERE id_usuario = :id
         FOR UPDATE"
    );
    $st->execute([':id' => $idUsuario]);
    $empleado = $st->fetch();

    if (!$empleado) {
        $conn->rollBack();
        responder(422, false, 'El empleado seleccionado no existe.');
    }

    // Si se cambia de empleado, el nuevo debe estar activo.
    // Si sigue siendo el mismo, se permite editar aunque ya esté inactivo.
    $cambioEmpleado = ((int) $actual['id_usuario'] !== $idUsuario);
    if ($cambioEmpleado && (int) $empleado['estado'] !== 1) {
        $conn->rollBack();
        responder(422, false, 'El empleado seleccionado está inactivo.');
    }

    // Un solo horario por empleado y día (sin contar el que se está editando)
    $st = $conn->prepare(
        "SELECT 1 FROM horarios_empleados
         WHERE id_usuario = :usuario
           AND dias = :dia
           AND id_horario_usuario <> :id
         LIMIT 1"
    );
    $st->execute([':usuario' => $idUsuario, ':dia' => $dia, ':id' => $idHorario]);
    if ($st->fetch()) {
        $conn->rollBack();
        responder(409, false, 'Este empleado ya tiene otro horario para ese día.');
    }

    $st = $conn->prepare(
        "UPDATE horarios_empleados
         SET id_usuario = :usuario,
             dias       = :dia,
             horaInicio = :inicio,
             horaFin    = :fin,
             estado     = :estado
         WHERE id_horario_usuario = :id"
    );
    $st->execute([
        ':usuario' => $idUsuario,
        ':dia'     => $dia,
        ':inicio'  => $horaInicio . ':00',
        ':fin'     => $horaFin . ':00',
        ':estado'  => $estado,
        ':id'      => $idHorario,
    ]);

    $conn->commit();

    responder(200, true, 'El horario se actualizó correctamente.');

} catch (PDOException $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }

    // 1062 = clave duplicada (por si se agrega el índice único empleado + día)
    if ((int) ($e->errorInfo[1] ?? 0) === 1062) {
        responder(409, false, 'Este empleado ya tiene otro horario para ese día.');
    }

    error_log('[horario/editar] ' . $e->getMessage());
    responder(500, false, 'No se pudo actualizar el horario. Intenta de nuevo.');
}