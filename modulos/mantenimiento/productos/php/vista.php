<?php
/**
 * modulos/mantenimiento/productos/php/vista.php
 * Vista del módulo Productos:
 * - Tarjetas de alertas (stock bajo, próximos a vencer, más/menos vendidos)
 * - Tabla de productos-presentación
 * - Modales: editar, stock, desactivar, nueva categoría, nueva presentación
 * - Vista embebida: crear producto (con modo nuevo/existente)
 */

require_once __DIR__ . '/../../../../dashboard/includes/auth.php';
require_once __DIR__ . '/../../../../dashboard/includes/permisos.php';
require_once __DIR__ . '/../../../../config/conexion.php';

requierePermiso('productos', 'ver');

$conn = conexionBD();

// =====================================================
// 1. LISTADO DE PRODUCTOS-PRESENTACIÓN
// =====================================================
$sqlProductos = "
    SELECT
        pp.id_presentacion_prod,
        pp.codigoBarra,
        pp.precioCompra,
        pp.precioVenta,
        pp.stock,
        pp.stockMinimo,
        pp.activo,
        pp.fechaRegistro,
        p.id_producto,
        p.nombreProducto,
        p.observaciones,
        p.activo        AS productoActivo,
        c.id_categoria,
        c.nombreCategoria,
        pr.id_presentacion,
        pr.nombrePresentacion
    FROM presentacionprod pp
    INNER JOIN productos p       ON p.id_producto     = pp.id_producto
    INNER JOIN presentacion pr   ON pr.id_presentacion = pp.id_presentacion
    LEFT  JOIN categorias c      ON c.id_categoria    = p.id_categoria
    ORDER BY pp.activo DESC, p.nombreProducto ASC, pr.nombrePresentacion ASC
";
$productos = $conn->query($sqlProductos)->fetchAll();

// =====================================================
// 2. CATEGORÍAS (filtro + selects)
// =====================================================
$categorias = $conn->query("
    SELECT id_categoria, nombreCategoria
    FROM categorias
    WHERE activo = 1
    ORDER BY nombreCategoria ASC
")->fetchAll();

// =====================================================
// 3. PRESENTACIONES (select crear producto)
// =====================================================
$presentaciones = $conn->query("
    SELECT id_presentacion, nombrePresentacion
    FROM presentacion
    ORDER BY nombrePresentacion ASC
")->fetchAll();

// =====================================================
// 4. PRODUCTOS BASE (para modo "existente")
// =====================================================
$productosBase = $conn->query("
    SELECT id_producto, nombreProducto
    FROM productos
    WHERE activo = 1
    ORDER BY nombreProducto ASC
")->fetchAll();

// =====================================================
// 5. MÉTRICAS PARA TARJETAS
// =====================================================

// 5.1 Stock bajo
$totalStockBajo = 0;
foreach ($productos as $p) {
    if ((int) $p['activo'] === 1 && (int) $p['stock'] <= (int) $p['stockMinimo']) {
        $totalStockBajo++;
    }
}

// 5.2 Próximos a vencer (30 días)
$hoy    = new DateTime();
$limite = (clone $hoy)->modify('+30 days');

$stmtVencer = $conn->prepare("
    SELECT COUNT(*) AS total
    FROM movimientos_inventario
    WHERE tipo = 'entrada'
      AND fechaVencimiento IS NOT NULL
      AND fechaVencimiento >= :hoy
      AND fechaVencimiento <= :limite
");
$stmtVencer->execute([
    ':hoy'    => $hoy->format('Y-m-d'),
    ':limite' => $limite->format('Y-m-d'),
]);
$totalPorVencer = (int) $stmtVencer->fetchColumn();

// 5.3 Más vendidos / menos vendidos
// TODO: integrar cuando ventas esté listo (otro compañero)
$totalMasVendidos   = 0;
$totalMenosVendidos = 0;

// =====================================================
// 6. HELPERS
// =====================================================
if (!function_exists('e')) {
    function e($valor): string {
        return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('formatearQ')) {
    function formatearQ($monto): string {
        return 'Q ' . number_format((float) $monto, 2);
    }
}

if (!function_exists('fechaCorta')) {
    function fechaCorta(?string $fecha): string {
        if (!$fecha) return '—';
        return (new DateTime($fecha))->format('d/m/Y H:i');
    }
}

if (!function_exists('fechaVencimientoCorta')) {
    function fechaVencimientoCorta(?string $fecha): string {
        if (!$fecha) return '—';
        return (new DateTime($fecha))->format('d/m/Y');
    }
}

// Próxima fecha de vencimiento por presentación-producto
$vencimientosPorPP = [];
$stmtVenc = $conn->query("
    SELECT id_presentacion_prod, MIN(fechaVencimiento) AS proximoVencimiento
    FROM movimientos_inventario
    WHERE tipo = 'entrada'
      AND fechaVencimiento IS NOT NULL
      AND fechaVencimiento >= CURDATE()
    GROUP BY id_presentacion_prod
");
foreach ($stmtVenc->fetchAll() as $v) {
    $vencimientosPorPP[(int) $v['id_presentacion_prod']] = $v['proximoVencimiento'];
}

// =====================================================
// 7. PERMISOS
// =====================================================
$puedeCrear    = tienePermiso('productos', 'crear');
$puedeEditar   = tienePermiso('productos', 'editar');
$puedeEliminar = tienePermiso('productos', 'eliminar');
?>

<div class="panel-head">
  <div>
    <span class="eyebrow-dark">Catálogo</span>
    <h1>Productos de <em>venta</em></h1>
    <p>Catálogo de productos con sus presentaciones, precios y stock.</p>
  </div>
  <span class="chip-rol"><?= e($nombreRol) ?></span>
</div>

<!-- ============================================================= -->
<!-- LISTADO PRINCIPAL                                             -->
<!-- ============================================================= -->
<div id="productosListado">

  <!-- TARJETAS DE ALERTA -->
  <div class="metricas metricas-alertas">
    <div class="metrica clicable" data-filtro="stock-bajo">
      <strong><?= (int) $totalStockBajo ?></strong>
      <span>Stock bajo</span>
    </div>
    <div class="metrica clicable" data-filtro="por-vencer">
      <strong><?= (int) $totalPorVencer ?></strong>
      <span>Próximos a vencer</span>
    </div>
    <div class="metrica clicable" data-filtro="mas-vendidos">
      <strong><?= (int) $totalMasVendidos ?></strong>
      <span>Más vendidos</span>
    </div>
    <div class="metrica clicable" data-filtro="menos-vendidos">
      <strong><?= (int) $totalMenosVendidos ?></strong>
      <span>Menos vendidos</span>
    </div>
  </div>

  <div class="bloque">
    <div class="bloque-top">
      <div>
        <h2>Productos registrados</h2>
        <p class="sub"><?= count($productos) ?> productos en el catálogo</p>
      </div>
      <div class="acciones-top">
        <select class="buscador filtro-categoria" id="filtroCategoria">
          <option value="">Todas las categorías</option>
          <?php foreach ($categorias as $c): ?>
            <option value="<?= (int) $c['id_categoria'] ?>"><?= e($c['nombreCategoria']) ?></option>
          <?php endforeach; ?>
        </select>
        <input type="search" class="buscador" id="buscarProducto"
               placeholder="Buscar por nombre o código..." />
        <?php if ($puedeCrear): ?>
          <button class="btn-oro" id="btnNuevoProducto">+ Nuevo producto</button>
        <?php endif; ?>
      </div>
    </div>

    <div class="tabla-scroll">
      <?php if (empty($productos)): ?>

        <div class="tabla-vacia">
          <p>No hay productos registrados aún.</p>
          <?php if ($puedeCrear): ?>
            <small>Usa el botón "+ Nuevo producto" para crear el primero.</small>
          <?php endif; ?>
        </div>

      <?php else: ?>

        <table class="tabla-panel" id="tablaProductos">
          <thead>
            <tr>
              <th>CÓDIGO</th>
              <th>PRODUCTO</th>
              <th>PRESENTACIÓN</th>
              <th>CATEGORÍA</th>              
              <th>STOCK</th>              
              <th>VENTA</th>
              <th>VENCE</th>
              <th>ESTADO</th>
              <th class="col-acc">ACCIONES</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($productos as $p): ?>
              <?php
                $activo      = ((int) $p['activo'] === 1);
                $claseActivo = $activo ? 'activo' : 'inactivo';
                $textoActivo = $activo ? 'Activo' : 'Inactivo';
                $stockActual = (int) $p['stock'];
                $stockMinimo = (int) $p['stockMinimo'];
                $claseStock  = ($activo && $stockActual <= $stockMinimo) ? 'stock-num bajo' : 'stock-num';
                $vencimiento = $vencimientosPorPP[(int) $p['id_presentacion_prod']] ?? null;
                $categoria   = $p['nombreCategoria'] ?? '—';
                $codigo      = $p['codigoBarra'] ?: '—';
              ?>
              <tr data-pp="<?= (int) $p['id_presentacion_prod'] ?>"
                  data-categoria="<?= (int) ($p['id_categoria'] ?? 0) ?>"
                  data-stock="<?= $stockActual ?>"
                  data-stockmin="<?= $stockMinimo ?>"
                  data-vencimiento="<?= e($vencimiento ?? '') ?>"
                  data-activo="<?= (int) $p['activo'] ?>">
                <td><?= e($codigo) ?></td>
                  <td class="principal"><?= e($p['nombreProducto']) ?></td>
                  <td><?= e($p['nombrePresentacion']) ?></td>
                  <td><?= e($categoria) ?></td>
                  <td><span class="<?= $claseStock ?>"><?= $stockActual ?></span></td>
                  <td><?= formatearQ($p['precioVenta']) ?></td>
                  <td><?= fechaVencimientoCorta($vencimiento) ?></td>
                  <td><span class="badge <?= $claseActivo ?>"><?= $textoActivo ?></span></td>
                <td class="col-acc">
                  <?php if ($puedeEditar && $activo): ?>
                    <button class="btn-accion editar"
                            data-pp="<?= (int) $p['id_presentacion_prod'] ?>">Editar</button>
                    <button class="btn-accion stock"
                            data-pp="<?= (int) $p['id_presentacion_prod'] ?>"
                            data-nombre="<?= e($p['nombreProducto'] . ' · ' . $p['nombrePresentacion']) ?>">Stock</button>
                  <?php endif; ?>
                  <?php if ($puedeEliminar): ?>
                    <?php if ($activo): ?>
                      <button class="btn-accion desactivar"
                              data-pp="<?= (int) $p['id_presentacion_prod'] ?>"
                              data-nombre="<?= e($p['nombreProducto'] . ' · ' . $p['nombrePresentacion']) ?>"
                              data-accion="desactivar">Desactivar</button>
                    <?php else: ?>
                      <button class="btn-accion activar"
                              data-pp="<?= (int) $p['id_presentacion_prod'] ?>"
                              data-nombre="<?= e($p['nombreProducto'] . ' · ' . $p['nombrePresentacion']) ?>"
                              data-accion="activar">Activar</button>
                    <?php endif; ?>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>

      <?php endif; ?>
    </div>
  </div>

</div><!-- /#productosListado -->

<!-- ============================================================= -->
<!-- VISTA EMBEBIDA: CREAR PRODUCTO                                -->
<!-- ============================================================= -->
<div class="vista-embebida" id="crearProductoWrap">

  <button type="button" class="btn-volver" id="btnVolverProductos">
    ← Volver a productos
  </button>

  <div class="panel-head">
    <div>
      <span class="eyebrow-dark">Nuevo producto</span>
      <h1>Registrar <em>producto</em></h1>
      <p>Completa los datos del producto, su presentación inicial y el stock con el que entra.</p>
    </div>
  </div>

  <form id="formProducto" autocomplete="off" onsubmit="return false;">

    <!-- ============ MODO: NUEVO / EXISTENTE ============ -->
    <div class="bloque">
      <div class="bloque-top">
        <div>
          <h2>¿Producto nuevo o presentación nueva?</h2>
          <p class="sub">Si el producto ya existe, agrega solo una nueva presentación.</p>
        </div>
      </div>

      <div class="grid-form">
        <div class="campo-panel ancho">
          <label>Tipo de registro *</label>
          <select name="modo" id="inputModo" required>
            <option value="nuevo">Producto nuevo (primera presentación)</option>
            <option value="existente">Agregar presentación a producto existente</option>
          </select>
        </div>
      </div>
    </div>

    <!-- ============ DATOS DEL PRODUCTO (modo nuevo) ============ -->
    <div class="bloque" id="bloqueDatosProducto">
      <div class="bloque-top">
        <div>
          <h2>Datos del producto</h2>
          <p class="sub">Información base del catálogo.</p>
        </div>
      </div>

      <div class="grid-form">
        <div class="campo-panel ancho">
          <label>Nombre del producto *</label>
          <input type="text" name="nombreProducto" id="inputNombreProducto"
                 maxlength="50" placeholder="Ej: Crema Nivea para cuerpo" />
        </div>

        <div class="campo-panel">
          <label>Categoría *</label>
          <div class="campo-con-boton">
            <select name="id_categoria" id="inputCategoria">
              <option value="">Selecciona una categoría</option>
              <?php foreach ($categorias as $c): ?>
                <option value="<?= (int) $c['id_categoria'] ?>"><?= e($c['nombreCategoria']) ?></option>
              <?php endforeach; ?>
            </select>
            <button type="button" class="btn-linea btn-mini" id="btnNuevaCategoria"
                    title="Crear nueva categoría">+</button>
          </div>
        </div>

        <div class="campo-panel">
          <label>Observaciones</label>
          <input type="text" name="observaciones" id="inputObservaciones"
                 maxlength="100" placeholder="Opcional" />
        </div>
      </div>
    </div>

    <!-- ============ SELECT PRODUCTO EXISTENTE ============ -->
    <div class="bloque" id="bloqueProductoExistente" style="display:none;">
      <div class="bloque-top">
        <div>
          <h2>Producto existente</h2>
          <p class="sub">Selecciona el producto al que le agregarás una nueva presentación.</p>
        </div>
      </div>

      <div class="grid-form">
        <div class="campo-panel ancho">
          <label>Producto *</label>
          <select name="id_producto" id="inputProductoExistente">
            <option value="">Selecciona un producto</option>
            <?php foreach ($productosBase as $pb): ?>
              <option value="<?= (int) $pb['id_producto'] ?>"><?= e($pb['nombreProducto']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
    </div>

    <!-- ============ PRESENTACIÓN, PRECIOS Y STOCK ============ -->
    <div class="bloque">
      <div class="bloque-top">
        <div>
          <h2>Presentación, precios y stock inicial</h2>
          <p class="sub">Esta es la presentación con la que entra el producto.</p>
        </div>
      </div>

      <div class="grid-form">
        <div class="campo-panel">
          <label>Presentación *</label>
          <div class="campo-con-boton">
            <select name="id_presentacion" id="inputPresentacion" required>
              <option value="">Selecciona una presentación</option>
              <?php foreach ($presentaciones as $pr): ?>
                <option value="<?= (int) $pr['id_presentacion'] ?>"><?= e($pr['nombrePresentacion']) ?></option>
              <?php endforeach; ?>
            </select>
            <button type="button" class="btn-linea btn-mini" id="btnNuevaPresentacion"
                    title="Crear nueva presentación">+</button>
          </div>
        </div>

        <div class="campo-panel">
          <label>Código de barras</label>
          <input type="text" name="codigoBarra" id="inputCodigoBarra"
                 maxlength="50" placeholder="Opcional" />
        </div>

        <div class="campo-panel">
          <label>Precio compra *</label>
          <input type="number" step="0.01" min="0" name="precioCompra"
                 id="inputPrecioCompra" required placeholder="30.00" />
        </div>

        <div class="campo-panel">
          <label>Precio venta *</label>
          <input type="number" step="0.01" min="0" name="precioVenta"
                 id="inputPrecioVenta" required placeholder="50.00" />
        </div>

        <div class="campo-panel">
          <label>Stock inicial *</label>
          <input type="number" min="0" name="stockInicial"
                 id="inputStockInicial" required placeholder="5" />
        </div>

        <div class="campo-panel">
          <label>Stock mínimo</label>
          <input type="number" min="0" name="stockMinimo"
                 id="inputStockMinimo" value="2" />
        </div>

        <div class="campo-panel">
          <label>Fecha de vencimiento</label>
          <input type="date" name="fechaVencimiento" id="inputFechaVencimiento" />
          <small>Solo si aplica.</small>
        </div>
      </div>

      <div class="form-acciones">
        <button type="button" class="btn-linea" id="btnCancelarProducto">Cancelar</button>
        <button type="submit" class="btn-oro">Guardar producto</button>
      </div>
    </div>
  </form>
</div>

<!-- ============================================================= -->
<!-- MODAL: EDITAR PRODUCTO                                        -->
<!-- ============================================================= -->
<div class="modal-panel" id="modalEditarProducto">
  <div class="modal-caja">
    <h3>Editar producto</h3>
    <p class="sub">Modifica los datos del catálogo. El stock se ajusta desde "Stock".</p>

    <form id="formEditarProducto" autocomplete="off" onsubmit="return false;">
      <input type="hidden" name="id_presentacion_prod" id="editIdPp" value="" />

      <div class="grid-form">
        <div class="campo-panel ancho">
          <label>Nombre del producto *</label>
          <input type="text" name="nombreProducto" id="editNombreProducto"
                 required maxlength="50" />
        </div>

        <div class="campo-panel">
          <label>Categoría</label>
          <select name="id_categoria" id="editCategoria">
            <option value="">Sin categoría</option>
            <?php foreach ($categorias as $c): ?>
              <option value="<?= (int) $c['id_categoria'] ?>"><?= e($c['nombreCategoria']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="campo-panel">
          <label>Observaciones</label>
          <input type="text" name="observaciones" id="editObservaciones" maxlength="100" />
        </div>

        <div class="campo-panel">
          <label>Precio compra *</label>
          <input type="number" step="0.01" min="0" name="precioCompra"
                 id="editPrecioCompra" required />
        </div>

        <div class="campo-panel">
          <label>Precio venta *</label>
          <input type="number" step="0.01" min="0" name="precioVenta"
                 id="editPrecioVenta" required />
        </div>

        <div class="campo-panel">
          <label>Stock mínimo</label>
          <input type="number" min="0" name="stockMinimo" id="editStockMinimo" />
        </div>

        <div class="campo-panel">
          <label>Código de barras</label>
          <input type="text" name="codigoBarra" id="editCodigoBarra" maxlength="50" />
        </div>
      </div>

      <div class="modal-acciones">
        <button type="button" class="btn-linea" data-cerrar>Cancelar</button>
        <button type="submit" class="btn-oro">Guardar cambios</button>
      </div>
    </form>
  </div>
</div>

<!-- ============================================================= -->
<!-- MODAL: MOVIMIENTO DE STOCK                                    -->
<!-- ============================================================= -->
<div class="modal-panel" id="modalStock">
  <div class="modal-caja">
    <h3>Movimiento de stock</h3>
    <p class="sub" id="stockSubtitulo">Producto:</p>

    <form id="formStock" autocomplete="off" onsubmit="return false;">
      <input type="hidden" name="id_presentacion_prod" id="stockIdPp" value="" />

      <div class="grid-form">
        <div class="campo-panel ancho">
          <label>Tipo de movimiento *</label>
          <select name="tipoMovimiento" id="stockTipo">
            <option value="entrada">Entrada (reabastecimiento)</option>
            <option value="ajuste">Ajuste (merma / corrección)</option>
          </select>
        </div>

        <div class="campo-panel">
          <label>Cantidad *</label>
          <input type="number" name="cantidad" id="stockCantidad" required />
          <small id="stockAyudaCantidad">Positivo para sumar, negativo para restar.</small>
        </div>

        <div class="campo-panel campo-stock-entrada">
          <label>Costo unitario</label>
          <input type="number" step="0.01" min="0" name="costoUnitario"
                 id="stockCosto" placeholder="Solo en entradas" />
        </div>

        <div class="campo-panel campo-stock-entrada">
          <label>Fecha de vencimiento</label>
          <input type="date" name="fechaVencimiento" id="stockVencimiento" />
        </div>

        <div class="campo-panel ancho">
          <label>Motivo *</label>
          <select name="motivoSelect" id="stockMotivoSelect" required>
            <option value="">— Selecciona —</option>
            <option value="reabastecimiento">Reabastecimiento</option>
            <option value="merma">Merma</option>
            <option value="vencido">Producto vencido</option>
            <option value="correccion_conteo">Corrección de conteo</option>
            <option value="otro">Otro</option>
          </select>
        </div>

        <div class="campo-panel ancho" id="stockMotivoOtroWrap" style="display:none;">
          <label>Especifica el motivo *</label>
          <input type="text" name="motivoOtro" id="stockMotivoOtro" maxlength="150" />
        </div>
      </div>

      <div class="modal-acciones">
        <button type="button" class="btn-linea" data-cerrar>Cancelar</button>
        <button type="submit" class="btn-oro">Registrar movimiento</button>
      </div>
    </form>
  </div>
</div>

<!-- ============================================================= -->
<!-- MODAL: NUEVA CATEGORÍA                                        -->
<!-- ============================================================= -->
<div class="modal-panel" id="modalCategoria">
  <div class="modal-caja">
    <h3>Nueva categoría</h3>
    <p class="sub">Ej: Cara, Cuerpo, Uñas, Cabello.</p>

    <form id="formCategoria" autocomplete="off" onsubmit="return false;">
      <div class="grid-form">
        <div class="campo-panel ancho">
          <label>Nombre de la categoría *</label>
          <input type="text" name="nombreCategoria" id="inputNuevaCategoria"
                 required maxlength="50" placeholder="Ej: Cabello" />
        </div>
      </div>

      <div class="modal-acciones">
        <button type="button" class="btn-linea" data-cerrar>Cancelar</button>
        <button type="submit" class="btn-oro">Guardar</button>
      </div>
    </form>
  </div>
</div>

<!-- ============================================================= -->
<!-- MODAL: NUEVA PRESENTACIÓN                                     -->
<!-- ============================================================= -->
<div class="modal-panel" id="modalPresentacion">
  <div class="modal-caja">
    <h3>Nueva presentación</h3>
    <p class="sub">Ej: 100ml, 250ml, Unidad, Par.</p>

    <form id="formPresentacion" autocomplete="off" onsubmit="return false;">
      <div class="grid-form">
        <div class="campo-panel ancho">
          <label>Nombre de la presentación *</label>
          <input type="text" name="nombrePresentacion" id="inputNuevaPresentacion"
                 required maxlength="50" placeholder="Ej: 250ml" />
        </div>
      </div>

      <div class="modal-acciones">
        <button type="button" class="btn-linea" data-cerrar>Cancelar</button>
        <button type="submit" class="btn-oro">Guardar</button>
      </div>
    </form>
  </div>
</div>