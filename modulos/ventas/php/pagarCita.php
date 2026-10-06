<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Europa · Caja con modal de productos</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <link rel="stylesheet" href="../css/stylePagarCita.css">
</head>
<body>

<div class="app-container">
  <div class="card">
    <!-- Header -->
    <div class="header-europa">
      <h1><i class="fas fa-cut"></i> Peluquería y Estética Europa</h1>
      <div class="admin-badge"><i class="fas fa-user-cog"></i> Roberto Espinoza · ADMINISTRADOR</div>
    </div>

    <!-- Navegación -->
    <div class="nav-grid">
      <div class="nav-item"><i class="fas fa-calendar-check"></i> Citas</div>
      <div class="nav-item"><i class="fas fa-chart-simple"></i> Ventas</div>
      <div class="nav-item"><i class="fas fa-users"></i> Empleados</div>
      <div class="nav-item"><i class="fas fa-wrench"></i> Mantenimientos</div>
      <div class="nav-item active"><i class="fas fa-cash-register"></i> Caja</div>
      <div class="nav-item"><i class="fas fa-sliders-h"></i> Ajustes</div>
      <div class="nav-item ver-sitio"><i class="fas fa-external-link-alt"></i> Ver sitio</div>
    </div>

    <!-- Sección: Caja -->
    <div class="section-header">
      <h2><i class="fas fa-cash-register"></i> Cobros pendientes <span class="sub">— Citas listas para cobrar</span></h2>
      <div class="search-box">
        <i class="fas fa-search"></i>
        <input type="text" id="searchCaja" placeholder="Buscar cliente..." oninput="filtrarCaja()">
      </div>
    </div>

    <!-- Tabla -->
    <div class="table-responsive">
      <table>
        <thead>
          <tr>
            <th>CLIENTE</th>
            <th>TELÉFONO</th>
            <th>SERVICIOS</th>
            <th>BARBERO</th>
            <th>FECHA</th>
            <th>HORA</th>
            <th>ESTADO</th>
            <th>ACCIÓN</th>
          </tr>
        </thead>
        <tbody id="tablaCaja"></tbody>
      </table>
    </div>
    <div style="margin-top: 12px; font-size: 13px; color: #64748b;">
      <i class="fas fa-info-circle"></i> Haz clic en <strong>"Cobrar"</strong> para gestionar el pago.
    </div>

    <!-- Panel de cobro -->
    <div class="panel-cobro" id="panelCobro">
      <div class="panel-header">
        <h3><i class="fas fa-hand-holding-usd"></i> Cobrar servicio <span class="sub" id="panelCitaId">#1024</span></h3>
        <span style="font-size:14px; color:#475569;"><i class="far fa-clock"></i> Caja · Turno mañana</span>
      </div>

      <div class="panel-grid">
        <!-- Resumen -->
        <div class="panel-resumen" id="panelResumen">
          <div class="cliente"><i class="fas fa-user" style="color:#b8860b; width:20px;"></i> <span id="panelCliente">Andrés Batres</span></div>
          <div class="detalle">
            <span><i class="fas fa-phone"></i> <span id="panelTelefono">4478 9012</span></span>
            <span><i class="fas fa-calendar-alt"></i> <span id="panelFecha">16/08/2026</span></span>
            <span><i class="fas fa-clock"></i> <span id="panelHora">11:00</span></span>
            <span><i class="fas fa-user-tie"></i> <span id="panelBarbero">Nada</span></span>
          </div>
          <div class="total">$<span id="panelTotal">0.00</span> <small>MXN</small></div>
          <div id="panelBadgeProductos"></div>
        </div>

        <!-- Items -->
        <div class="panel-items" id="panelItems">
          <!-- Generado por JS -->
        </div>

        <!-- Acciones -->
        <div class="panel-acciones">
          <button class="btn-cobrar-ahora" id="btnCobrarAhora"><i class="fas fa-check-circle"></i> Cobrar ahora</button>
          <button class="btn-cancelar" id="btnCancelarCobro"><i class="fas fa-times"></i> Cancelar</button>
          <div style="font-size:12px; color:#64748b; text-align:center; width:100%;">
            <i class="fas fa-info-circle"></i> El pago confirma la cita
          </div>
        </div>
      </div>

      <div class="panel-feedback" id="panelFeedback"></div>
    </div>
  </div>

  <div class="demo-footer">
    <i class="fas fa-credit-card"></i> Vista de demostración — el pago con tarjeta confirma la cita automáticamente.
  </div>
</div>

<!-- ===== MODAL DE PRODUCTOS ===== -->
<div class="modal-overlay" id="modalProductos">
  <div class="modal-productos">
    <div class="modal-header">
      <h3><i class="fas fa-boxes"></i> Agregar productos <small>— Selecciona los que desees</small></h3>
      <button class="modal-close" id="btnCerrarModalProductos"><i class="fas fa-times"></i></button>
    </div>

    <div class="modal-busqueda">
      <div class="input-wrapper">
        <input type="text" id="modalBusquedaProducto" placeholder="Buscar producto..." autocomplete="off">
      </div>
      <div class="cantidad-wrapper">
        <label>Cantidad:</label>
        <input type="number" id="modalCantidadProducto" value="1" min="1" max="99">
      </div>
    </div>

    <div class="modal-lista" id="modalListaProductos">
      <!-- Generado por JS -->
    </div>

    <div class="modal-footer">
      <button class="btn-cerrar-modal" id="btnCerrarModal"><i class="fas fa-check"></i> Terminar y cerrar</button>
    </div>
  </div>
</div>

<script src="../js/jsPagarCita.js">
  // ---- DATOS ----
 </script>

</body>
</html>