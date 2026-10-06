<?php
/**
 * index.php
 * Layout principal del dashboard.
 * El contenido de cada módulo se carga dinámicamente en #contenido.
 */

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/permisos.php';

$tituloPagina = 'Panel — Peluquería & Estética Europa';
?>
<?php require_once __DIR__ . '/includes/header.php'; ?>

<?php require_once __DIR__ . '/includes/sidebar.php'; ?>

<main class="panel-main">
  <div id="contenido" class="panel-contenido">
    <div class="cargando-inicial">
      <p>Cargando panel...</p>
    </div>
  </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>