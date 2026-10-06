<?php
/**
 * modulos/mantenimiento/clientes/php/ingresar.php
 * Registra un cliente. Responde SIEMPRE en JSON.
 *
 * - telefono2 y correo son opcionales.
 * - El correo debe ser único (si se proporciona).
 * - El teléfono repetido NO se bloquea: se guarda igual y se informa
 *   al frontend con "telefonoRepetido" para mostrar un aviso.
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

if (!tienePermiso('clientes', 'crear')) {
    responder(403, false, 'No tienes permiso para registrar clientes.');
}

// -----------------------------------------------------
// 4. Constantes y helpers
// -----------------------------------------------------
const GENEROS_VALIDOS = ['MASCULINO', 'FEMENINO', 'OTRO', 'NO_ESPECIFICADO'];
// Letras (con tildes y ñ), espacios, apóstrofos y guiones
const PATRON_NOMBRE = '/^[A-Za-zÁÉÍÓÚáéíóúÑñÜü\' -]+$/u';

// Deja solo dígitos. Si vienen menos u otros caracteres, no es un teléfono válido.
function soloDigitos(string $valor): string
{
    return preg_replace('/\D/', '', $valor);
}

// "55124478" -> "5512 4478"
function formatoTelefono(string $digitos): string
{
    return substr($digitos, 0, 4) . ' ' . substr($digitos, 4);
}

// -----------------------------------------------------
// 5. Validación de datos
// -----------------------------------------------------
$nombre    = trim((string) ($_POST['nombreCliente'] ?? ''));
$apellido  = trim((string) ($_POST['apellidoCliente'] ?? ''));
$telefono  = trim((string) ($_POST['telefono'] ?? ''));
$telefono2 = trim((string) ($_POST['telefono2'] ?? ''));
$correo    = trim((string) ($_POST['correo'] ?? ''));
$genero    = (string) ($_POST['genero'] ?? 'NO_ESPECIFICADO');
$estado    = (string) ($_POST['estado'] ?? '1');

if ($nombre === '' || mb_strlen($nombre, 'UTF-8') > 50 || !preg_match(PATRON_NOMBRE, $nombre)) {
    responder(422, false, 'El nombre no es válido. Solo se permiten letras y espacios, hasta 50 caracteres.');
}
if ($apellido === '' || mb_strlen($apellido, 'UTF-8') > 50 || !preg_match(PATRON_NOMBRE, $apellido)) {
    responder(422, false, 'El apellido no es válido. Solo se permiten letras y espacios, hasta 50 caracteres.');
}

$digitosTel1 = soloDigitos($telefono);
if (strlen($digitosTel1) !== 8) {
    responder(422, false, 'El teléfono debe tener exactamente 8 dígitos.');
}
$telefonoBD = formatoTelefono($digitosTel1);

$digitosTel2 = null;
$telefono2BD = null;
if ($telefono2 !== '') {
    $digitosTel2 = soloDigitos($telefono2);
    if (strlen($digitosTel2) !== 8) {
        responder(422, false, 'El teléfono 2 debe tener exactamente 8 dígitos, o déjalo vacío.');
    }
    $telefono2BD = formatoTelefono($digitosTel2);
}

$correoBD = null;
if ($correo !== '') {
    if (mb_strlen($correo, 'UTF-8') > 75 || !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        responder(422, false, 'El correo no es válido.');
    }
    $correoBD = $correo;
}

if (!in_array($genero, GENEROS_VALIDOS, true)) {
    responder(422, false, 'Selecciona un género válido.');
}
if ($estado !== '0' && $estado !== '1') {
    responder(422, false, 'El estado no es válido.');
}
$estado = (int) $estado;

// -----------------------------------------------------
// 6. Guardado (en transacción)
// -----------------------------------------------------
$conn = conexionBD();

try {
    $conn->beginTransaction();

    // Correo único (sin distinguir mayúsculas, igual que el índice de la BD)
    if ($correoBD !== null) {
        $st = $conn->prepare(
            "SELECT nombreCliente, apellidoCliente FROM clientes
             WHERE correo = :correo
             LIMIT 1
             FOR UPDATE"
        );
        $st->execute([':correo' => $correoBD]);
        $existente = $st->fetch();

        if ($existente) {
            $conn->rollBack();
            responder(
                409,
                false,
                'Ese correo ya está registrado con el cliente '
                . $existente['nombreCliente'] . ' ' . $existente['apellidoCliente'] . '.'
            );
        }
    }

    $st = $conn->prepare(
        "INSERT INTO clientes
            (nombreCliente, apellidoCliente, telefono, telefono2, correo, genero, estado)
         VALUES
            (:nombre, :apellido, :telefono, :telefono2, :correo, :genero, :estado)"
    );
    $st->execute([
        ':nombre'    => $nombre,
        ':apellido'  => $apellido,
        ':telefono'  => $telefonoBD,
        ':telefono2' => $telefono2BD,
        ':correo'    => $correoBD,
        ':genero'    => $genero,
        ':estado'    => $estado,
    ]);

    $idNuevo = (int) $conn->lastInsertId();

    // Aviso (no bloquea): ¿algún OTRO cliente ya tiene alguno de estos teléfonos?
    $telefonos = array_values(array_filter([$digitosTel1, $digitosTel2]));
    $placeholders = implode(',', array_fill(0, count($telefonos), '?'));

    $st = $conn->prepare(
        "SELECT nombreCliente, apellidoCliente, telefono, telefono2
         FROM clientes
         WHERE id_cliente <> ?
           AND (
             REPLACE(telefono, ' ', '')  IN ($placeholders)
             OR REPLACE(telefono2, ' ', '') IN ($placeholders)
           )
         LIMIT 1"
    );
    $st->execute(array_merge([$idNuevo], $telefonos, $telefonos));
    $coincidencia = $st->fetch();

    $conn->commit();

    $respuesta = ['id' => $idNuevo];
    if ($coincidencia) {
        $respuesta['telefonoRepetido'] = true;
        $respuesta['avisoTelefono'] = 'El teléfono ya está registrado con el cliente '
            . $coincidencia['nombreCliente'] . ' ' . $coincidencia['apellidoCliente'] . '.';
    }

    responder(200, true, 'El cliente se registró correctamente.', $respuesta);

} catch (PDOException $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }

    // 1062 = clave duplicada (por si dos peticiones simultáneas usan el mismo correo)
    if ((int) ($e->errorInfo[1] ?? 0) === 1062) {
        responder(409, false, 'Ese correo ya está registrado con otro cliente.');
    }

    error_log('[clientes/ingresar] ' . $e->getMessage());
    responder(500, false, 'No se pudo guardar el cliente. Intenta de nuevo.');
}