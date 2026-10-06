<?php
/**
 * sidebar.php
 * Genera el menú lateral dinámicamente a partir de $_SESSION['permisos'].
 *
 * Agrupa las acciones por módulo (grupo) y las ordena por moduloOrden / accionOrden.
 * Si un grupo tiene una sola acción visible, se renderiza como botón suelto
 * (igual que "Citas" en el diseño original).
 */

require_once __DIR__ . '/iconos.php';

// Si no vienen definidas, las tomamos de la sesión (por si acaso)
if (!isset($permisos)) {
    $permisos = $_SESSION['permisos'] ?? [];
}
if (!isset($nombreUsuario)) {
    $nombreUsuario = $_SESSION['nombre'] ?? 'Usuario';
}
if (!isset($nombreRol)) {
    $nombreRol = $_SESSION['nombre_rol'] ?? 'Usuario';
}

// 1. Agrupar acciones por módulo
$grupos = [];
foreach ($permisos as $p) {
    $key = $p['moduloCodigo'];

    if (!isset($grupos[$key])) {
        $grupos[$key] = [
            'codigo' => $p['moduloCodigo'],
            'nombre' => $p['moduloNombre'],
            'icono'  => $p['moduloIcono'],
            'orden'  => (int) $p['moduloOrden'],
            'acciones' => [],
        ];
    }

    $grupos[$key]['acciones'][] = [
        'codigo' => $p['accionCodigo'],
        'nombre' => $p['accionNombre'],
        'icono'  => $p['accionIcono'],
        'ruta'   => $p['accionRuta'],
        'orden'  => (int) $p['accionOrden'],
    ];
}

// 2. Ordenar grupos por su orden
uasort($grupos, fn($a, $b) => $a['orden'] <=> $b['orden']);

// 3. Ordenar acciones dentro de cada grupo
foreach ($grupos as &$g) {
    usort($g['acciones'], fn($a, $b) => $a['orden'] <=> $b['orden']);
}
unset($g);

// 4. Iniciales del avatar (2 primeras letras del nombre)
$partes    = preg_split('/\s+/', trim($nombreUsuario));
$iniciales = '';
foreach ($partes as $p) {
    if ($p !== '') {
        $iniciales .= mb_strtoupper(mb_substr($p, 0, 1));
    }
    if (mb_strlen($iniciales) >= 2) break;
}
if ($iniciales === '') $iniciales = 'US';
?>
<aside class="panel-side" id="panelSide">

  <button type="button" class="side-collapse-btn" id="btnColapsar" aria-label="Contraer menú">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="16" rx="2"/><path d="M9 4v16"/></svg>
  </button>

  <div class="side-brand"><span class="side-label">Peluquería y Estética<em>Europa</em></span></div>

  <div class="side-user">
    <div class="side-avatar"><?= htmlspecialchars($iniciales) ?></div>
    <div class="side-label">
      <strong><?= htmlspecialchars($nombreUsuario) ?></strong>
      <span><?= htmlspecialchars($nombreRol) ?></span>
    </div>
  </div>

  <nav class="side-nav">

    <?php foreach ($grupos as $grupo): ?>

      <?php if (count($grupo['acciones']) === 1): ?>
        <?php $acc = $grupo['acciones'][0]; ?>
        <button data-seccion="<?= htmlspecialchars($acc['codigo']) ?>"
                data-ruta="<?= htmlspecialchars($acc['ruta']) ?>"
                title="<?= htmlspecialchars($acc['nombre']) ?>">
          <span class="side-icon"><?= iconoSVG($acc['icono'] ?: $grupo['icono']) ?></span>
          <span class="side-label"><?= htmlspecialchars($acc['nombre']) ?></span>
        </button>

      <?php else: ?>
        <div class="side-grupo">
          <button type="button" class="side-toggle" title="<?= htmlspecialchars($grupo['nombre']) ?>">
            <span class="side-icon"><?= iconoSVG($grupo['icono']) ?></span>
            <span class="side-label"><?= htmlspecialchars($grupo['nombre']) ?></span>
            <svg class="side-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 6 15 12 9 18"/></svg>
          </button>
          <div class="side-sub">
            <?php foreach ($grupo['acciones'] as $acc): ?>
              <button data-seccion="<?= htmlspecialchars($acc['codigo']) ?>"
                      data-ruta="<?= htmlspecialchars($acc['ruta']) ?>">
                <span class="side-icon"><?= iconoSVG($acc['icono']) ?></span>
                <span><?= htmlspecialchars($acc['nombre']) ?></span>
              </button>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endif; ?>

    <?php endforeach; ?>

    <a href="/Peluqueria/public/" target="_blank" title="Ver sitio">
      <span class="side-icon"><?= iconoSVG('ojo') ?></span>
      <span class="side-label">Ver sitio</span>
    </a>

  </nav>

  <a href="/Peluqueria/dashboard/logout.php" class="side-salir">
    <span class="side-label">Cerrar sesión</span>
  </a>

</aside>