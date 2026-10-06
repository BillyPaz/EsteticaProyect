<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Europa · Ventas realizadas</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <link rel="stylesheet" href="../css/styleListadoVentas.css">
</head>
<body>

<!-- ====== HEADER ====== -->
<div class="page-header">
  <div class="left">
    <h1>Ventas <em>realizadas</em></h1>
    <p>Historial de ventas — comprobantes, clientes y totales.</p>
  </div>
  <div class="right">
    <span class="badge-admin">Caja · Turno mañana</span>
  </div>
</div>

<!-- ====== STATS ====== -->
<div class="stats-mini">
  <div class="stat-card">
    <div class="icono dorado"><i class="fas fa-receipt"></i></div>
    <div class="info">
      <span class="numero" id="statTotalVentas">0</span>
      <span class="label">Ventas totales</span>
    </div>
  </div>
  <div class="stat-card">
    <div class="icono verde"><i class="fas fa-money-bill-wave"></i></div>
    <div class="info">
      <span class="numero" id="statMontoTotal">Q 0.00</span>
      <span class="label">Monto total</span>
    </div>
  </div>
  <div class="stat-card">
    <div class="icono azul"><i class="fas fa-calendar-day"></i></div>
    <div class="info">
      <span class="numero" id="statVentasHoy">0</span>
      <span class="label">Ventas de hoy</span>
    </div>
  </div>
  <div class="stat-card">
    <div class="icono rojo"><i class="fas fa-chart-line"></i></div>
    <div class="info">
      <span class="numero" id="statPromedio">Q 0.00</span>
      <span class="label">Ticket promedio</span>
    </div>
  </div>
</div>

<!-- ====== CARD ====== -->
<div class="card">
  <div class="card-header">
    <h2>Listado de ventas</h2>
    <div class="actions">
      <select class="filter-select" id="filtroTipo" onchange="filtrarVentas()">
        <option value="">Todos los tipos</option>
        <option value="CF">Consumidor Final</option>
        <option value="NIT">NIT</option>
      </select>
      <select class="filter-select" id="filtroMetodo" onchange="filtrarVentas()">
        <option value="">Todos los métodos</option>
        <option value="Efectivo">Efectivo</option>
        <option value="Tarjeta">Tarjeta</option>
        <option value="Transferencia">Transferencia</option>
      </select>
      <div class="search-box">
        <i class="fas fa-search"></i>
        <input type="text" id="buscarVenta" placeholder="Buscar por documento, cliente o NIT..." oninput="filtrarVentas()">
      </div>
    </div>
  </div>

  <!-- Tabla -->
  <div class="table-responsive">
    <table>
      <thead>
        <tr>
          <th>DOCUMENTO</th>
          <th>CLIENTE</th>
          <th>TIPO</th>
          <th>MÉTODO</th>
          <th>ITEMS</th>
          <th>TOTAL</th>
          <th>ACCIONES</th>
        </tr>
      </thead>
      <tbody id="tablaVentas"></tbody>
    </table>
  </div>
</div>

<!-- ====== MODAL DETALLE ====== -->
<div class="modal-overlay" id="modalDetalle">
  <div class="modal">
    <div class="modal-header">
      <div class="titulo-wrapper">
        <h3><i class="fas fa-file-invoice"></i> Detalle de venta</h3>
        <span class="subtitulo" id="modalNumDocumento">FAC-0001</span>
      </div>
      <button class="modal-close" onclick="cerrarModalDetalle()"><i class="fas fa-times"></i></button>
    </div>

    <div class="modal-body">
      <!-- Información general -->
      <div class="info-grid">
        <div class="info-block">
          <div class="label"><i class="fas fa-user"></i> Cliente</div>
          <div class="value" id="modalCliente">Consumidor Final</div>
          <div class="value secundario" id="modalClienteDetalle">Sin datos fiscales</div>
        </div>
        <div class="info-block">
          <div class="label"><i class="fas fa-calendar-day"></i> Fecha y hora</div>
          <div class="value" id="modalFecha">—</div>
          <div class="value secundario" id="modalCajero">Cajero: Roberto Espinoza</div>
        </div>
        <div class="info-block">
          <div class="label"><i class="fas fa-credit-card"></i> Método de pago</div>
          <div class="value" id="modalMetodo">Efectivo</div>
        </div>
        <div class="info-block">
          <div class="label"><i class="fas fa-tag"></i> Tipo de documento</div>
          <div class="value" id="modalTipoDoc">Consumidor Final (CF)</div>
        </div>
      </div>

      <!-- Productos -->
      <div class="modal-subtitulo">
        <i class="fas fa-shopping-basket"></i> Productos vendidos
      </div>
      <div class="tabla-productos">
        <table>
          <thead>
            <tr>
              <th>PRODUCTO</th>
              <th>PRECIO</th>
              <th>CANT.</th>
              <th>SUBTOTAL</th>
            </tr>
          </thead>
          <tbody id="modalProductosBody"></tbody>
        </table>
      </div>

      <!-- Totales -->
      <div class="modal-subtitulo">
        <i class="fas fa-calculator"></i> Totales
      </div>
      <div class="totales-box">
        <div class="total-linea">
          <span class="label"><i class="fas fa-shopping-bag"></i> Subtotal</span>
          <span class="valor" id="modalSubtotal">Q 0.00</span>
        </div>
        <div class="total-linea">
          <span class="label"><i class="fas fa-tag"></i> Descuento</span>
          <span class="valor" id="modalDescuento">Q 0.00</span>
        </div>
        <div class="total-linea iva">
          <span class="label"><i class="fas fa-percent"></i> IVA (12%)</span>
          <span class="valor" id="modalIva">Q 0.00</span>
        </div>
        <div class="total-linea total-final">
          <span class="label">TOTAL</span>
          <span class="valor" id="modalTotal">Q 0.00</span>
        </div>
      </div>
    </div>

    <div class="modal-footer">
      <div class="footer-info">
        <i class="fas fa-info-circle"></i> Documento generado el <span id="modalFechaGeneracion">—</span>
      </div>
      <div class="footer-buttons">
        <button class="btn-cerrar-modal" onclick="cerrarModalDetalle()">
          <i class="fas fa-times"></i> Cerrar
        </button>
        <button class="btn-pdf" onclick="generarPDF()">
          <i class="fas fa-file-pdf"></i> Generar PDF
        </button>
      </div>
    </div>
  </div>
</div>

<!-- ====== TOAST ====== -->
<div class="toast" id="toast">
  <i class="fas fa-check-circle"></i>
  <span id="toastMsg">PDF generado correctamente</span>
</div>
<script src="../js/jsListadoVentas.js" ></script>

</body>
</html>