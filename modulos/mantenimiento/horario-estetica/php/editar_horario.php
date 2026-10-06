<?php
/**
 * modulos/mantenimiento/horario-estetica/php/editar_horario.php
 * Modifica el horario de un día de la semana (apertura, cierre y estado).
 * El día en sí también se puede cambiar (por si se registró uno equivocado).
 * Responde SIEMPRE en JSON.
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

if (!tienePermiso('horario-estetica', 'editar')) {
    responder(403, false, 'No tienes permiso para modificar el horario de la estética.');
}

// -----------------------------------------------------
// 4. Validación de datos
// -----------------------------------------------------
const DIAS_VALIDOS = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sabado', 'Domingo'];
const PATRON_HORA  = '/^([01]\d|2[0-3]):[0-5]\d$/';

$idHorario    = filter_var($_POST['id_horario'] ?? '', FILTER_VALIDATE_INT);
$dia          = (string) ($_POST['dias'] ?? '');
$horaApertura = trim((string) ($_POST['horaApertura'] ?? ''));
$horaCierre   = trim((string) ($_POST['horaCierre'] ?? ''));
$estado       = (string) ($_POST['estado'] ?? '');

if ($idHorario === false || $idHorario < 1) {
    responder(422, false, 'No se pudo identificar el horario que quieres editar.');
}
if (!in_array($dia, DIAS_VALIDOS, true)) {
    responder(422, false, 'Selecciona un día válido.');
}
if (!preg_match(PATRON_HORA, $horaApertura)) {
    responder(422, false, 'La hora de apertura no es válida.');
}
if (!preg_match(PATRON_HORA, $horaCierre)) {
    responder(422, false, 'La hora de cierre no es válida.');
}
if ($estado !== '0' && $estado !== '1') {
    responder(422, false, 'El estado no es válido.');
}
$estado = (int) $estado;

if ($estado === 1 && $horaCierre <= $horaApertura) {
    responder(422, false, 'La hora de cierre debe ser mayor que la hora de apertura.');
}

// -----------------------------------------------------
// 5. Actualización
// -----------------------------------------------------
$conn = conexionBD();

try {
    $st = $conn->prepare(
        "SELECT id_horario_estetica FROM horarios_estetica
         WHERE id_horario_estetica = :id"
    );
    $st->execute([':id' => $idHorario]);
    if (!$st->fetch()) {
        responder(404, false, 'Este horario ya no existe. Recarga el módulo para ver la lista actualizada.');
    }

    $st = $conn->prepare(
        "UPDATE horarios_estetica
         SET dias         = :dia,
             horaApertura = :apertura,
             horaCierre   = :cierre,
             estado       = :estado
         WHERE id_horario_estetica = :id"
    );
    $st->execute([
        ':dia'      => $dia,
        ':apertura' => $horaApertura . ':00',
        ':cierre'   => $horaCierre . ':00',
        ':estado'   => $estado,
        ':id'       => $idHorario,
    ]);

    responder(200, true, 'El horario se actualizó correctamente.');

} catch (PDOException $e) {
    // 1062 = clave duplicada: ya existe un horario para el día que se eligió
    if ((int) ($e->errorInfo[1] ?? 0) === 1062) {
        responder(409, false, 'Ya existe un horario configurado para ese día.');
    }

    error_log('[horario-estetica/editar_horario] ' . $e->getMessage());
    responder(500, false, 'No se pudo actualizar el horario. Intenta de nuevo.');
}