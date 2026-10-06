<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Europa · Punto de Venta</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <link rel="stylesheet" href="../js/jsRealizarVenta.js">
  <link rel="stylesheet" href="../css/styleRealizarVenta.css">
</head>
<body>

<!-- ====== HEADER ====== -->
<div class="page-header">
  <div class="left">
    <h1>Venta de <em>productos</em></h1>
    <p>Punto de venta — código de producto, PEPS y facturación CF/NIT.</p>
  </div>
  <div class="right">
    <span class="badge-admin">Caja · Turno mañana</span>
  </div>
</div>

<!-- ====== LAYOUT ====== -->
<div class="pos-layout">

  <!-- COLUMNA IZQUIERDA -->
  <div>
    <!-- CARD: DATOS DE FACTURA -->
    <div class="card">
      <div class="card-title">
        <div class="left">
          <i class="fas fa-file-invoice"></i> Datos de facturación
        </div>
        <!-- Toggle CF / NIT unificado -->
        <div class="tipo-toggle">
          <button class="toggle-btn active" data-tipo="CF" onclick="seleccionarTipo('CF')">
            <i class="fas fa-user"></i> CF
          </button>
          <button class="toggle-btn" data-tipo="NIT" onclick="seleccionarTipo('NIT')">
            <i class="fas fa-id-card"></i> NIT
          </button>
        </div>
      </div>

      <!-- Formulario unificado -->
      <div class="form-row">
        <div class="form-group" id="groupNombre">
          <label><i class="fas fa-user"></i> Nombre del cliente <span class="required" id="reqNombre" style="display:none;">*</span></label>
          <input type="text" id="nombreCliente" placeholder="Consumidor Final" value="Consumidor Final" disabled>
          <div class="error-msg" id="errorNombre">Ingresa el nombre del cliente</div>
        </div>
        <div class="form-group" id="groupNit">
          <label><i class="fas fa-id-card"></i> NIT <span class="required" id="reqNit" style="display:none;">*</span></label>
          <input type="text" id="nitCliente" placeholder="Ej: 1234567-8" maxlength="15" disabled>
          <div class="error-msg" id="errorNit">Ingresa un NIT válido</div>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label><i class="fas fa-building"></i> Razón social</label>
          <input type="text" id="razonSocial" placeholder="Nombre de la empresa (opcional)" disabled>
        </div>
        <div class="form-group">
          <label><i class="fas fa-map-marker-alt"></i> Dirección</label>
          <input type="text" id="direccionCliente" placeholder="Dirección (opcional)" disabled>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label><i class="fas fa-receipt"></i> No. Documento</label>
          <input type="text" id="numDocumento" value="FAC-0001" readonly style="background:#f5f0eb; cursor:not-allowed;">
        </div>
        <div class="form-group">
          <label><i class="fas fa-calendar-day"></i> Fecha</label>
          <input type="text" id="fechaVenta" readonly style="background:#f5f0eb; cursor:not-allowed;">
        </div>
      </div>
    </div>

    <!-- CARD: DETALLE -->
    <div class="card">
      <div class="card-title">
        <div class="left">
          <i class="fas fa-shopping-basket"></i> Detalle de la venta
        </div>
        <button class="btn-agregar-productos" onclick="abrirModalProductos()">
          <i class="fas fa-search"></i> Buscar productos
        </button>
      </div>

      <!-- Campo de código rápido -->
      <div class="form-row" style="margin-bottom: 20px;">
        <div class="form-group codigo-wrapper" id="groupCodigo" style="grid-column: 1 / -1;">
          <label><i class="fas fa-barcode"></i> Código de producto <span style="color:#999; font-weight:400; font-size:11px; margin-left:6px;">(escribe o escanea y presiona Enter)</span></label>
          <input type="text" id="codigoProducto" placeholder="Ej: P001, P002..." autocomplete="off" maxlength="20">
          <i class="fas fa-barcode scan-icon"></i>
          <div class="codigo-status" id="codigoStatus"></div>
        </div>
      </div>

      <!-- Estado vacío -->
      <div class="detalle-vacio" id="detalleVacio">
        <i class="fas fa-box-open"></i>
        <p>No hay productos en la venta</p>
        <small>Escribe un código o haz clic en "Buscar productos"</small>
      </div>

      <!-- Tabla de detalle -->
      <div class="detalle-tabla" id="detalleTabla" style="display:none;">
        <table>
          <thead>
            <tr>
              <th>PRODUCTO / LOTE</th>
              <th>PRECIO</th>
              <th>CANTIDAD</th>
              <th>SUBTOTAL</th>
              <th></th>
            </tr>
          </thead>
          <tbody id="cuerpoDetalle"></tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- COLUMNA DERECHA: RESUMEN -->
  <div class="resumen-panel">
    <div class="panel-title">
      <i class="fas fa-calculator"></i> Resumen de venta
    </div>

    <div class="info-cliente" id="infoCliente">
      <div class="tipo" id="infoTipo">Consumidor Final</div>
      <div class="nombre" id="infoNombre">Consumidor Final</div>
      <div class="detalle" id="infoDetalle">Sin datos fiscales</div>
    </div>

    <div class="resumen-linea">
      <span class="label"><i class="fas fa-shopping-bag"></i> Subtotal</span>
      <span class="valor" id="resumenSubtotal">Q 0.00</span>
    </div>
    <div class="resumen-linea iva">
      <span class="label"><i class="fas fa-percent"></i> IVA (12%)</span>
      <span class="valor" id="resumenIva">Q 0.00</span>
    </div>

    <div class="resumen-linea total">
      <span class="label">TOTAL</span>
      <span class="valor" id="resumenTotal">Q 0.00</span>
    </div>

    <button class="btn-cobrar" id="btnCobrar" disabled onclick="procesarVenta()">
      <i class="fas fa-cash-register"></i> Cobrar venta
    </button>
    <button class="btn-cancelar-venta" onclick="cancelarVenta()">
      <i class="fas fa-times"></i> Cancelar venta
    </button>

    <div style="margin-top:16px; padding-top:14px; border-top:1px solid #f0ebe5; font-size:11px; color:#999; text-align:center;">
      <i class="fas fa-info-circle"></i> Método PEPS: primero en entrar, primero en salir
    </div>
  </div>

</div>

<!-- ====== MODAL DE PRODUCTOS ====== -->
<div class="modal-overlay" id="modalProductos">
  <div class="modal">
    <div class="modal-header">
      <h3><i class="fas fa-boxes"></i> Seleccionar productos</h3>
      <button class="modal-close" onclick="cerrarModalProductos()"><i class="fas fa-times"></i></button>
    </div>

    <div class="modal-busqueda">
      <div class="input-wrapper">
        <input type="text" id="busquedaProducto" placeholder="Buscar por nombre, código, marca o lote..." autocomplete="off" oninput="filtrarProductos()">
      </div>
      <div class="cantidad-wrapper">
        <label>Cantidad:</label>
        <input type="number" id="cantidadProducto" value="1" min="1" max="99">
      </div>
    </div>

    <div class="modal-lista" id="listaProductos"></div>

    <div class="modal-footer">
      <span class="contador-items" id="contadorItems"><strong>0</strong> productos agregados</span>
      <button class="btn-cerrar-modal" onclick="cerrarModalProductos()">
        <i class="fas fa-check"></i> Terminar
      </button>
    </div>
  </div>
</div>

<!-- ====== TOAST ====== -->
<div class="toast" id="toast">
  <i class="fas fa-check-circle"></i>
  <span id="toastMsg">Venta procesada correctamente</span>
</div>

</body>
</html>