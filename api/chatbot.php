<?php
/**
 * api/chatbot.php
 * Endpoint del Asistente Europa.
 * Recibe un POST JSON { message, history } y devuelve { ok, intent, reply }.
 *
 * Este archivo NO está dentro de public/ para no quedar expuesto
 * directamente como archivo navegable.
 */

// =====================================================
// CONFIGURACIÓN
// =====================================================
const DEBUG = true; // ← cambiar a false cuando pases a producción

// =====================================================
// HEADERS
// =====================================================
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

// =====================================================
// RESPUESTA ESTÁNDAR
// =====================================================
function responder(bool $ok, string $intent, string $reply, ?string $state = null, int $httpCode = 200): void {
    http_response_code($httpCode);
    echo json_encode([
        'ok'     => $ok,
        'intent' => $intent,
        'reply'  => $reply,
        'state'  => $state,
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

function responderError(string $intent, string $reply, int $httpCode = 200): void {
    responder(false, $intent, $reply, $httpCode);
}

// =====================================================
// VALIDAR MÉTODO
// =====================================================
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responderError('error', 'Método no permitido.', 405);
}

// =====================================================
// VALIDAR CONTENT-TYPE
// =====================================================
$contentType = $_SERVER['CONTENT_TYPE'] ?? '';
if (stripos($contentType, 'application/json') === false) {
    responderError('error', 'Content-Type debe ser application/json.', 415);
}

// =====================================================
// LEER Y VALIDAR ENTRADA
// =====================================================
$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

if (!is_array($data) || !isset($data['message'])) {
    responderError('error', 'Falta el campo "message".', 400);
}

$mensaje = trim((string) $data['message']);
if ($mensaje === '') {
    responderError('error', 'El mensaje está vacío.', 400);
}

// Límite de longitud (defensa anti-abuso)
if (mb_strlen($mensaje) > 300) {
    $mensaje = mb_substr($mensaje, 0, 300);
}

// =====================================================
// HELPERS
// =====================================================
function normalizar(string $texto): string {
    $texto = mb_strtolower($texto, 'UTF-8');
    // Quitar tildes
    $texto = str_replace(
        ['á','é','í','ó','ú','Á','É','Í','Ó','Ú','ñ','Ñ','ü','Ü'],
        ['a','e','i','o','u','A','E','I','O','U','n','N','u','U'],
        $texto
    );
    return trim($texto);
}

function escapar(string $texto): string {
    return htmlspecialchars($texto, ENT_QUOTES, 'UTF-8');
}

function elegirAleatoria(array $arr): string {
    return $arr[array_rand($arr)];
}

// =====================================================
// DETECCIÓN DE INTENCIÓN
// =====================================================
function detectarIntencion(string $mensaje): string {
    $t = normalizar($mensaje);

    // Saludos
    if (preg_match('/^(hola|buenas|buenos dias|buenas tardes|buenas noches|hey|que tal|holi|saludos)\b/u', $t)) {
        return 'saludo';
    }

    // Propósito
    if (preg_match('/(quien sos|quien eres|para que|proposito|que podes hacer|que sabes|en que me puedes ayudar|en que me podes ayudar|que informacion|ayuda|funcion|para que fuiste)/u', $t)) {
        return 'proposito';
    }

    // Gracias
    if (preg_match('/(gracias|mil gracias|te agradezco|muchas gracias)/u', $t)) {
        return 'gracias';
    }

    // Despedida
    if (preg_match('/(adios|chau|chao|hasta luego|nos vemos|bye|me voy|hasta pronto)/u', $t)) {
        return 'despedida';
    }

    // Duración específica (debe ir antes que precio/servicios)
    if (preg_match('/(duracion|dura|demora|cuanto tiempo|cuanto tarda|tiempo aproximado)/u', $t)) {
        return 'duracion_servicio';
    }

    // Precio específico
    if (preg_match('/(precio|precios|cuesta|cuestan|vale|valen|cuanto sale|cuanto cuesta)/u', $t)) {
        return 'precio_servicio';
    }

    // Lista completa / servicios en general
    if (preg_match('/(lista|completa|que servicios|servicios que|servicios ofrecen|ofrecen|servicios tienen|tienen servicios|dame los servicios|mostrar servicios|cuales son los servicios|servicios disponibles|que ofrecen|servicios|servicio)/u', $t)) {
        return 'lista_servicios';
    }

    // Servicio específico sin precio/duración (ej: "tienen corte?")
    if (preg_match('/(tienen|hacen|ofrecen|dan)\s+/u', $t) && preg_match('/(corte|color|barba|tratamiento|peinado|manicure|pedicure|depilacion|facial|maquillaje|tinte|mechas|alisado|keratina)/u', $t)) {
        return 'servicio_especifico';
    }

    // Lista de productos (Fase 1)
    if (preg_match('/(producto|productos|producto tienen|productos tienen|que productos|lista de productos|catalogo|catalogos|que venden|venden|que ofrecen en productos|tienen a la venta|a la venta|para la venta)/u', $t)) {
        return 'lista_productos';
    }

    // Horarios
    if (preg_match('/(horario|hora|abren|cierran|abierto|abiertos|atienden|cerrado)/u', $t)) {
        return 'horarios';
    }
    
    return 'noEntiende';
}

// =====================================================
// RESPUESTAS FIJAS (no dependen de BD)
// =====================================================
$RESPUESTAS = [
    'saludo' => [
        '¡Hola! 👋 Soy el Asistente de Europa. Estoy acá para ayudarte con información sobre la estética: horarios, servicios, productos, ubicación y cómo reservar tu cita. ¿Sobre qué te gustaría saber?',
        '¡Hola! 😊 ¿Cómo estás? Soy el Asistente de Europa. Puedo contarte sobre horarios, servicios, productos, ubicación o cómo reservar una cita. ¿Qué necesitás?',
        '¡Buenas! ✨ Soy el Asistente de Europa. ¿En qué te puedo ayudar hoy?',
    ],
    'proposito' => [
        "Mi propósito es ayudarte con información sobre Peluquería y Estética Europa. 😊 Puedo contarte sobre:\n\n• Horarios de atención\n• Servicios y precios\n• Productos disponibles\n• Ubicación y contacto\n• Cómo reservar una cita\n\n¿Sobre qué te gustaría saber?",
        "Fui creado para que puedas consultar de forma rápida información sobre la estética. 💫 Manejo estos temas:\n\n• Horarios\n• Servicios\n• Productos\n• Ubicación\n• Cómo reservar\n\n¿Qué te interesa?",
    ],
    'gracias' => [
        '¡Con gusto! 😊 Si necesitás algo más, aquí estoy.',
        '¡De nada! 🙌 Cualquier cosa, me escribís.',
        '¡Un placer ayudarte! ✨ ¿Algo más en lo que pueda colaborar?',
    ],
    'despedida' => [
        '¡Hasta luego! 👋 Que tengas un lindo día.',
        '¡Nos vemos! 😊 Gracias por escribirnos.',
        '¡Chau! ✨ Espero verte pronto por Europa.',
    ],
    'noEntiende' => [
        'Mmm, no estoy seguro de haber entendido. 😅 Por ahora puedo ayudarte con: horarios, servicios, productos, ubicación y cómo reservar una cita. ¿Sobre cuál te gustaría saber?',
        'Perdón, todavía estoy aprendiendo. 🙈 Puedo darte info sobre horarios, servicios, productos, ubicación o cómo reservar. ¿Qué te gustaría consultar?',
    ],
    'proximamente' => [
        '¡Muy pronto podré ayudarte con eso! 🚧 Por ahora estoy en modo de prueba. Mientras tanto, podés escribirme tu consulta y vemos qué puedo hacer. 😊',
    ],
    'error' => [
        'Lo siento, en este momento no puedo acceder a esa información. Por favor intentá más tarde. 🙏',
    ],
    'lista_productos' => [
    '¡Claro! ¿Querés ver la lista completa o preferís verla por categoría? 😊',
    ],
];

// =====================================================
// CONSULTA A BD: HORARIOS
// =====================================================
function obtenerHorarios(): string {
    $rutaConexion = __DIR__ . '/../config/conexion.php';

    if (!file_exists($rutaConexion)) {
        if (DEBUG) error_log('[Chatbot] No se encontró conexion.php');
        return elegirAleatoria([
            'No puedo acceder a los horarios en este momento. Por favor intentá más tarde.',
        ]);
    }

    try {
        require_once $rutaConexion;

        if (!function_exists('conexionBD')) {
            if (DEBUG) error_log('[Chatbot] conexionBD() no existe');
            return 'No puedo acceder a los horarios en este momento. Por favor intentá más tarde.';
        }

        $conn = conexionBD();

        $sql = "SELECT dias, horaApertura, horaCierre, estado
                FROM horarios_estetica
                ORDER BY FIELD(dias, 'Lunes','Martes','Miércoles','Jueves','Viernes','Sabado','Domingo')";

        $stmt = $conn->query($sql);
        $filas = $stmt->fetchAll();

        if (empty($filas)) {
            return 'Aún no hay horarios configurados. Por favor intentá más tarde.';
        }

        $lineas = [];
        foreach ($filas as $f) {
            $dia = escapar($f['dias']);
            if ((int) $f['estado'] === 1) {
                $apertura = substr($f['horaApertura'], 0, 5);
                $cierre   = substr($f['horaCierre'], 0, 5);
                $lineas[] = "• {$dia}: {$apertura} — {$cierre}";
            } else {
                $lineas[] = "• {$dia}: Cerrado";
            }
        }

        return "Nuestros horarios de atención son:\n\n" . implode("\n", $lineas);

    } catch (Throwable $e) {
        if (DEBUG) error_log('[Chatbot] Error BD: ' . $e->getMessage());
        return 'No puedo acceder a los horarios en este momento. Por favor intentá más tarde.';
    }
}

// =====================================================
// CONSULTA A BD: LISTA DE PRODUCTOS (solo nombres)
// =====================================================
function obtenerProductos(?int $idCategoria = null): string {
    $rutaConexion = __DIR__ . '/../config/conexion.php';

    if (!file_exists($rutaConexion)) {
        if (DEBUG) error_log('[Chatbot] No se encontró conexion.php');
        return 'No puedo acceder a la lista de productos en este momento. Por favor intentá más tarde.';
    }

    try {
        require_once $rutaConexion;
        $conn = conexionBD();

        // Solo productos que tengan al menos una presentación activa
        if ($idCategoria !== null) {
            $sql = "SELECT DISTINCT p.nombreProducto
                    FROM productos p
                    INNER JOIN presentacionprod pp ON pp.id_producto = p.id_producto
                    WHERE p.activo = 1
                      AND pp.activo = 1
                      AND p.id_categoria = :idCategoria
                    ORDER BY p.nombreProducto ASC";
            $stmt = $conn->prepare($sql);
            $stmt->bindValue(':idCategoria', $idCategoria, PDO::PARAM_INT);
            $stmt->execute();
        } else {
            $sql = "SELECT DISTINCT p.nombreProducto
                    FROM productos p
                    INNER JOIN presentacionprod pp ON pp.id_producto = p.id_producto
                    WHERE p.activo = 1
                      AND pp.activo = 1
                    ORDER BY p.nombreProducto ASC";
            $stmt = $conn->query($sql);
        }

        $filas = $stmt->fetchAll();

        if (empty($filas)) {
            return 'Aún no hay productos cargados. Por favor intentá más tarde.';
        }

        $lineas = [];
        foreach ($filas as $f) {
            $lineas[] = '• ' . escapar($f['nombreProducto']);
        }

        return "Estos son nuestros productos disponibles:\n\n" . implode("\n", $lineas);

    } catch (Throwable $e) {
        if (DEBUG) error_log('[Chatbot] Error BD productos: ' . $e->getMessage());
        return 'No puedo acceder a la lista de productos en este momento. Por favor intentá más tarde.';
    }
}

// =====================================================
// CONSULTA A BD: PRODUCTOS CON PRECIO (Fase 5)
// Devuelve nombre + presentación + precioVenta
// =====================================================
function obtenerProductosConPrecio(?int $idCategoria = null): string {
    $rutaConexion = __DIR__ . '/../config/conexion.php';

    if (!file_exists($rutaConexion)) {
        if (DEBUG) error_log('[Chatbot] No se encontró conexion.php');
        return 'No puedo acceder a los precios en este momento. Por favor intentá más tarde.';
    }

    try {
        require_once $rutaConexion;
        $conn = conexionBD();

        // Filtro por categoría (opcional)
        $whereCat = '';
        if ($idCategoria !== null) {
            $whereCat = ' AND p.id_categoria = :idCategoria';
        }

        $sql = "SELECT p.nombreProducto,
                       pr.nombrePresentacion,
                       pp.precioVenta
                FROM presentacionprod pp
                INNER JOIN productos p     ON p.id_producto      = pp.id_producto
                INNER JOIN presentacion pr ON pr.id_presentacion = pp.id_presentacion
                WHERE p.activo = 1
                  AND pp.activo = 1
                  {$whereCat}
                ORDER BY p.nombreProducto ASC, pr.nombrePresentacion ASC";

        if ($idCategoria !== null) {
            $stmt = $conn->prepare($sql);
            $stmt->bindValue(':idCategoria', $idCategoria, PDO::PARAM_INT);
            $stmt->execute();
        } else {
            $stmt = $conn->query($sql);
        }

        $filas = $stmt->fetchAll();

        if (empty($filas)) {
            return 'No hay productos con precios disponibles en este momento.';
        }

        $lineas = [];
        foreach ($filas as $f) {
            $nombre    = escapar($f['nombreProducto']);
            $present   = escapar($f['nombrePresentacion']);
            $precio    = 'Q ' . number_format((float) $f['precioVenta'], 2, '.', ',');
            $lineas[]  = "• {$nombre} · {$present}: {$precio}";
        }

        return "Estos son los precios:\n\n" . implode("\n", $lineas);

    } catch (Throwable $e) {
        if (DEBUG) error_log('[Chatbot] Error BD precios productos: ' . $e->getMessage());
        return 'No puedo acceder a los precios en este momento. Por favor intentá más tarde.';
    }
}

// =====================================================
// CONSULTA A BD: CATEGORÍAS CON PRODUCTOS ACTIVOS (Fase 3)
// =====================================================
function obtenerCategoriasConProductos(): string {
    $rutaConexion = __DIR__ . '/../config/conexion.php';

    if (!file_exists($rutaConexion)) {
        if (DEBUG) error_log('[Chatbot] No se encontró conexion.php');
        return 'No puedo acceder a las categorías en este momento. Por favor intentá más tarde.';
    }

    try {
        require_once $rutaConexion;
        $conn = conexionBD();

        // Solo categorías activas que tengan al menos un producto activo
        // con al menos una presentación activa.
        $sql = "SELECT DISTINCT c.id_categoria, c.nombreCategoria
                FROM categorias c
                INNER JOIN productos p        ON p.id_categoria = c.id_categoria
                INNER JOIN presentacionprod pp ON pp.id_producto = p.id_producto
                WHERE c.activo = 1
                  AND p.activo = 1
                  AND pp.activo = 1
                ORDER BY c.nombreCategoria ASC";

        $stmt = $conn->query($sql);
        $filas = $stmt->fetchAll();

        if (empty($filas)) {
            return 'Aún no hay categorías con productos disponibles. Por favor intentá más tarde.';
        }

        $lineas = [];
        foreach ($filas as $f) {
            $lineas[] = '• ' . escapar($f['nombreCategoria']);
        }

        return "Estas son las categorías disponibles:\n\n" . implode("\n", $lineas) . "\n\n¿Cuál te interesa?";

    } catch (Throwable $e) {
        if (DEBUG) error_log('[Chatbot] Error BD categorías: ' . $e->getMessage());
        return 'No puedo acceder a las categorías en este momento. Por favor intentá más tarde.';
    }
}

// =====================================================
// BUSCAR CATEGORÍA POR NOMBRE (Fase 4)
// Devuelve ['id_categoria' => X, 'nombreCategoria' => Y] o null
// =====================================================
function obtenerCategoriaPorNombre(string $texto): ?array {
    $rutaConexion = __DIR__ . '/../config/conexion.php';

    if (!file_exists($rutaConexion)) {
        if (DEBUG) error_log('[Chatbot] No se encontró conexion.php');
        return null;
    }

    try {
        require_once $rutaConexion;
        $conn = conexionBD();

        // Normalizar: quitar tildes, minúsculas, y quitar palabras comunes
        $t = normalizar($texto);
        $t = preg_replace('/[¿?¡!.,;:()\[\]{}"\'\/]/u', ' ', $t);

        // Quitar palabras comunes que no son categorías
        $stopwords = ['quiero', 'ver', 'la', 'el', 'los', 'las', 'de', 'del',
                      'para', 'por', 'favor', 'dame', 'muestrame', 'mostrar',
                      'productos', 'producto', 'categoria', 'categorias',
                      'si', 'claro', 'dale', 'ok', 'bueno', 'porfavor'];
        $t = preg_replace('/\b(' . implode('|', $stopwords) . ')\b/u', ' ', $t);
        $t = trim(preg_replace('/\s+/u', ' ', $t));

        if ($t === '') return null;

        // Buscar por LIKE (una sola palabra suele bastar)
        $sql = "SELECT id_categoria, nombreCategoria
                FROM categorias
                WHERE activo = 1
                  AND LOWER(nombreCategoria) LIKE LOWER(:filtro)
                ORDER BY nombreCategoria ASC
                LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':filtro', '%' . $t . '%', PDO::PARAM_STR);
        $stmt->execute();
        $fila = $stmt->fetch();

        return $fila ?: null;

    } catch (Throwable $e) {
        if (DEBUG) error_log('[Chatbot] Error BD categoría: ' . $e->getMessage());
        return null;
    }
}

// =====================================================
// LEER STATE (contexto conversacional)
// =====================================================
$state = $data['state'] ?? null;
if (!is_string($state)) $state = null;

// =====================================================
// RESPUESTAS DE SÍ / NO SEGÚN CONTEXTO
// =====================================================
$tNorm = normalizar($mensaje);
$esSi  = preg_match('/^(si|sip|sii|claro|dale|ok|okey|bueno|yes|obvio|porfa|por favor|vamos)\b/u', $tNorm);
$esNo  = preg_match('/^(no|nop|nel|para nada|no gracias|no por ahora|todavia no|aun no|aún no)\b/u', $tNorm);

// =====================================================
// RESPUESTAS POR ESTADO: PRODUCTOS (Fase 2)
// =====================================================
$esListaCompleta = preg_match('/\b(lista|completa|lista completa|todo|todos|ver todo|mostrar todo|mostrame todo|mostrarme todo|quiero ver todo|ver todos|todos los productos|la lista completa|lista de todo)\b/u', $tNorm);
$esPorCategoria  = preg_match('/\b(por categoria|categoria|categorias|por categorias|ver categorias|ver por categorias|mostrar categorias)/u', $tNorm);

// El cliente pidió lista completa
if ($state === 'esperando_tipo_lista_productos' && $esListaCompleta) {
    $reply = obtenerProductos(null);
    responder(true, 'lista_productos_completa', $reply . "\n\n¿Querés saber los precios?", 'esperando_precios_productos_all');
}

// El cliente pidió por categoría (Fase 3: mostrar categorías disponibles)
if ($state === 'esperando_tipo_lista_productos' && $esPorCategoria) {
    $reply = obtenerCategoriasConProductos();
    responder(true, 'lista_categorias', $reply, 'esperando_categoria_productos');
}

// El cliente no eligió ni lista ni categoría, pero está en ese estado
if ($state === 'esperando_tipo_lista_productos' && !$esListaCompleta && !$esPorCategoria) {
    responder(true, 'lista_productos_aclara', 'Perdón, no entendí. ¿Querés ver la lista completa o preferís verla por categoría? 😊', 'esperando_tipo_lista_productos');
}

// =====================================================
// RESPUESTAS POR ESTADO: CATEGORÍA ELEGIDA (Fase 3)
// =====================================================

// El cliente está en estado "esperando categoría" (Fase 4: búsqueda real)
if ($state === 'esperando_categoria_productos') {
    $categoria = obtenerCategoriaPorNombre($mensaje);

    if ($categoria === null) {
        // No encontró la categoría: mostrar de nuevo el listado de categorías disponibles
        $reply = obtenerCategoriasConProductos();
        responder(true, 'categoria_no_encontrada',
            "No encontré esa categoría. 😅 Estas son las que tenemos disponibles:\n\n" . $reply,
            'esperando_categoria_productos');
    }

        // Encontró la categoría: mostrar solo los productos de esa categoría
    $reply = obtenerProductos((int) $categoria['id_categoria']);
    responder(true, 'productos_de_categoria',
        $reply . "\n\n¿Querés saber los precios?",
        'esperando_precios_productos_cat_' . (int) $categoria['id_categoria']);
}

if ($esSi && $state === 'esperando_precios') {
    $reply = obtenerServicios(null, 'precios');
    responder(true, 'lista_servicios_precios', $reply . "\n\n¿También querés saber la duración aproximada de cada servicio?", 'esperando_duracion');
}

// Fase 5: el cliente pidió ver precios de productos
// Detectamos si el state guarda una categoría específica o es la lista completa.
if ($esSi && strpos($state ?? '', 'esperando_precios_productos') === 0) {
    // Extraer id_categoria si está presente en el state
    $idCategoria = null;
    if (preg_match('/esperando_precios_productos_cat_(\d+)/', $state, $m)) {
        $idCategoria = (int) $m[1];
    }

    $reply = obtenerProductosConPrecio($idCategoria);
    responder(true, 'productos_precios',
        $reply . "\n\n¿Querés pedir alguno o querés saber algo más?",
        'esperando_algo_mas_productos');
}

if ($esNo && strpos($state ?? '', 'esperando_precios_productos') === 0) {
    responder(true, 'productos_no_precios', '¡Perfecto! ¿Querés saber algo más?', 'esperando_algo_mas_productos');
}

// =====================================================
// FASE 6: DETECCIÓN DE PEDIDO (cliente quiere comprar)
// =====================================================
$esPedido = preg_match('/\b(quiero|dame|me llevo|me interesa|reservame|apartame|aparta|como compro|cómo compro|como lo compro|dónde lo compro|donde lo compro|como lo pido|cómo lo pido|como pido|quiero pedir|quiero comprar|quiero apartar|quiero reservar|me gustaria|me gustaría)\b/u', $tNorm);

// El cliente está en contexto de productos y expresa intención de pedido
if ($esPedido && (
    $state === 'esperando_algo_mas_productos' ||
    $state === 'esperando_precios_productos' ||
    (strpos($state ?? '', 'esperando_precios_productos') === 0)
)) {
    responder(true, 'pedido_producto',
        "Para tomar tu pedido, por favor llamá al 1111-1111. 📞\n\n¡Gracias por consultar! Si necesitás algo más, aquí estoy. 😊",
        null);
}

if ($state === 'esperando_algo_mas_productos') {
    if ($esNo) {
        responder(true, 'despedida_suave', '¡Gracias por consultar! Si necesitás algo más, aquí estoy. 😊', null);
    }
    responder(true, 'productos_algo_mas', '¡Dale! Contame, ¿sobre qué querés saber?', null);
}

if ($esNo && $state === 'esperando_precios') {
    responder(true, 'servicios_no_precios', '¡Perfecto! ¿Querés saber algo más?', 'esperando_algo_mas');
}

if ($esSi && $state === 'esperando_duracion') {
    $reply = obtenerServicios(null, 'duraciones');
    responder(true, 'lista_servicios_duraciones', $reply . "\n\n¿Querés saber algo más?", 'esperando_algo_mas');
}

if ($esNo && $state === 'esperando_duracion') {
    responder(true, 'servicios_no_duracion', '¡Perfecto! ¿Querés saber algo más?', 'esperando_algo_mas');
}

if (($esSi || $esNo) && $state === 'esperando_algo_mas') {
    if ($esNo) {
        responder(true, 'despedida_suave', '¡Gracias por consultar! Si necesitás algo más, aquí estoy. 😊', null);
    }
    responder(true, 'servicios_algo_mas', '¡Dale! Contame, ¿sobre qué querés saber?', null);
}

// Si el cliente dice "sí" o "no" pero no hay contexto
if ($esSi && $state === null) {
    responder(true, 'noEntiende', 'No estoy seguro de a qué te referís con "sí". ¿Podés contarme qué necesitás?', null);
}

if ($esNo && $state === null) {
    responder(true, 'noEntiende', 'No estoy seguro de a qué te referís con "no". ¿Podés contarme qué necesitás?', null);
}

// =====================================================
// PROCESAR SEGÚN INTENCIÓN
// =====================================================
$intent = detectarIntencion($mensaje);

// Horarios
if ($intent === 'horarios') {
    $reply = obtenerHorarios();
    responder(true, 'horarios', $reply, null);
}

// Lista de productos (Fase 1: solo pregunta lista o categoría)
if ($intent === 'lista_productos') {
    responder(true, 'lista_productos', elegirAleatoria($RESPUESTAS['lista_productos']), 'esperando_tipo_lista_productos');
}

// Lista completa de servicios (solo nombres)
if ($intent === 'lista_servicios') {
    $reply = obtenerServicios(null, 'nombres');
    responder(true, 'lista_servicios', $reply . "\n\n¿Querés saber los precios?", 'esperando_precios');
}

// Precio específico (busca por nombre)
if ($intent === 'precio_servicio') {
    $filtro = detectarServicioMencionado($mensaje);
    $reply  = obtenerServicios($filtro, 'precios');
    responder(true, 'precio_servicio', $reply, null);
}

// Duración específica
if ($intent === 'duracion_servicio') {
    $filtro = detectarServicioMencionado($mensaje);
    $reply  = obtenerServicios($filtro, 'duraciones');
    responder(true, 'duracion_servicio', $reply, null);
}

// Servicio específico sin precio ni duración
if ($intent === 'servicio_especifico') {
    $filtro = detectarServicioMencionado($mensaje);
    $reply  = obtenerServicios($filtro, 'precios');
    responder(true, 'servicio_especifico', $reply, null);
}

// Respuestas fijas
if (isset($RESPUESTAS[$intent])) {
    responder(true, $intent, elegirAleatoria($RESPUESTAS[$intent]), null);
}

// Fase 6: pedido sin contexto (cliente escribe directo "quiero X producto")
if ($esPedido && $state === null) {
    responder(true, 'pedido_producto_sin_contexto',
        "Para tomar tu pedido, por favor llamá al 1111-1111. 📞\n\n¡Gracias por consultar! Si necesitás algo más, aquí estoy. 😊",
        null);
}

// Fallback
responder(true, 'noEntiende', elegirAleatoria($RESPUESTAS['noEntiende']), null);

// =====================================================
// CONSULTA A BD: SERVICIOS
// =====================================================
function obtenerServicios(?string $filtro = null, string $modo = 'nombres'): string {
    $rutaConexion = __DIR__ . '/../config/conexion.php';

    if (!file_exists($rutaConexion)) {
        if (DEBUG) error_log('[Chatbot] No se encontró conexion.php');
        return 'No puedo acceder a la lista de servicios en este momento. Por favor intentá más tarde.';
    }

    try {
        require_once $rutaConexion;
        $conn = conexionBD();

        // Armar consulta con filtro opcional
        if ($filtro !== null && $filtro !== '') {
            $sql = "SELECT nombreServicio, costoServicio, duracion
                    FROM servicios
                    WHERE activo = 1
                      AND LOWER(nombreServicio) LIKE LOWER(:filtro)
                    ORDER BY nombreServicio ASC";
            $stmt = $conn->prepare($sql);
            $stmt->bindValue(':filtro', '%' . $filtro . '%', PDO::PARAM_STR);
            $stmt->execute();
        } else {
            $sql = "SELECT nombreServicio, costoServicio, duracion
                    FROM servicios
                    WHERE activo = 1
                    ORDER BY nombreServicio ASC";
            $stmt = $conn->query($sql);
        }

        $filas = $stmt->fetchAll();

        if (empty($filas)) {
            if ($filtro !== null && $filtro !== '') {
                return 'No encontré servicios relacionados con eso. ¿Querés ver la lista completa?';
            }
            return 'Aún no hay servicios cargados. Por favor intentá más tarde.';
        }

        $lineas = [];
        foreach ($filas as $f) {
            $nombre = escapar($f['nombreServicio']);
            $precio = 'Q ' . number_format((float) $f['costoServicio'], 2, '.', ',');

            if ($modo === 'nombres') {
                $lineas[] = "• {$nombre}";
            } elseif ($modo === 'precios') {
                $lineas[] = "• {$nombre}: {$precio}";
            } elseif ($modo === 'duraciones') {
                $minutos = (int) $f['duracion'];
                $textoDur = formatearDuracion($minutos);
                $lineas[] = "• {$nombre}: {$textoDur}";
            }
        }

        return implode("\n", $lineas);

    } catch (Throwable $e) {
        if (DEBUG) error_log('[Chatbot] Error BD servicios: ' . $e->getMessage());
        return 'No puedo acceder a la lista de servicios en este momento. Por favor intentá más tarde.';
    }
}

// =====================================================
// FORMATEAR DURACIÓN (minutos → texto humano)
// =====================================================
function formatearDuracion(int $minutos): string {
    if ($minutos <= 0) return 'Duración no especificada';
    if ($minutos < 60) return "{$minutos} minutos";
    $horas = intdiv($minutos, 60);
    $resto = $minutos % 60;
    if ($resto === 0) {
        return $horas === 1 ? '1 hora' : "{$horas} horas";
    }
    return "{$horas} hora" . ($horas > 1 ? 's' : '') . " y {$resto} minutos";
}

// =====================================================
// DETECTAR SERVICIO MENCIONADO EN EL MENSAJE
// =====================================================
function detectarServicioMencionado(string $mensaje): ?string {
    // Palabras ignoradas (no son servicios)
    $ignorar = ['corte', 'cortes', 'servicio', 'servicios', 'precio', 'precios',
                'cuesta', 'cuestan', 'vale', 'valen', 'cuanto', 'cuánto',
                'lista', 'completa', 'ofrecen', 'tienen', 'dan', 'dame',
                'duracion', 'duración', 'dura', 'demora', 'tiempo',
                'el', 'la', 'los', 'las', 'de', 'del', 'un', 'una', 'y', 'o',
                'que', 'qué', 'me', 'te', 'les', 'sus', 'mi', 'mis',
                'por', 'para', 'favor', 'gracias', 'hola'];

    // Normalizar
    $t = normalizar($mensaje);
    // Quitar signos
    $t = preg_replace('/[¿?¡!.,;:()\[\]{}"\'\/]/u', ' ', $t);
    $palabras = preg_split('/\s+/u', $t, -1, PREG_SPLIT_NO_EMPTY);

    // Filtrar palabras útiles (>= 4 letras y no en la lista ignorar)
    $utiles = array_filter($palabras, function ($p) use ($ignorar) {
        return mb_strlen($p) >= 4 && !in_array($p, $ignorar, true);
    });

    if (empty($utiles)) return null;

    // Devolver la palabra más larga (más probable que sea el servicio)
    usort($utiles, fn($a, $b) => mb_strlen($b) - mb_strlen($a));
    return $utiles[0];
}