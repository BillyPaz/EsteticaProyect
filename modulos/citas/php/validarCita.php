<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Europa · Validación de citas</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <link rel="stylesheet" href="../css/styleValidarCita.css">
</head>
<body>

<div class="app-container">
  <!-- Card principal -->
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
      <div class="nav-item"><i class="fas fa-cash-register"></i> Caja</div>
      <div class="nav-item"><i class="fas fa-sliders-h"></i> Ajustes</div>
      <div class="nav-item active"><i class="fas fa-check-double"></i> Validar citas</div>
      <div class="nav-item ver-sitio"><i class="fas fa-external-link-alt"></i> Ver sitio</div>
    </div>

    <!-- Sección: Validar citas -->
    <div class="section-header">
      <h2><i class="fas fa-check-double"></i> Validar citas <span class="sub">— Citas de hoy</span></h2>
      <div class="search-box">
        <i class="fas fa-search"></i>
        <input type="text" id="searchInput" placeholder="Buscar cliente..." oninput="filtrarTabla()">
      </div>
    </div>

    <!-- Tabla de citas del día -->
    <div class="table-responsive">
      <table>
        <thead>
          <tr>
            <th>CLIENTE</th>
            <th>TELÉFONO</th>
            <th>SERVICIO</th>
            <th>BARBERO</th>
            <th>HORA</th>
            <th>PAGO</th>
            <th>ESTADO</th>
            <th>ACCIÓN</th>
          </tr>
        </thead>
        <tbody id="tablaCitasDia">
          <!-- Generado por JS -->
        </tbody>
      </table>
    </div>
    <div style="margin-top: 12px; font-size: 13px; color: #64748b;">
      <i class="fas fa-info-circle"></i> Haz clic en <strong>"Validar"</strong> para gestionar los servicios de la cita.
    </div>
  </div>

  <div class="demo-footer">
    <i class="fas fa-credit-card"></i> Vista de demostración — el pago con tarjeta confirma la cita automáticamente.
  </div>
</div>

<!-- ===== MODAL ===== -->
<div class="modal-overlay" id="modalOverlay">
  <div class="modal">
    <div class="modal-header">
      <h3><i class="fas fa-check-circle"></i> Validar servicios <span style="font-size:14px; font-weight:400; color:#475569; margin-left:8px;" id="modalCitaId">#1024</span></h3>
      <button class="modal-close" onclick="cerrarModal()"><i class="fas fa-times"></i></button>
    </div>

    <div class="modal-grid">
      <!-- Resumen cita -->
      <div class="modal-resumen" id="modalResumen">
        <div class="cliente"><i class="fas fa-user" style="color:#b8860b; width:20px;"></i> <span id="modalCliente">Andrés Batres</span></div>
        <div class="detalle">
          <span><i class="fas fa-phone"></i> <span id="modalTelefono">4478 9012</span></span>
          <span><i class="fas fa-calendar-alt"></i> <span id="modalFecha">16/08/2026</span></span>
          <span><i class="fas fa-clock"></i> <span id="modalHora">11:00</span></span>
          <span><i class="fas fa-user-tie"></i> <span id="modalBarbero">Nada</span></span>
        </div>
        <div style="margin-top: 6px; display: flex; gap: 8px; flex-wrap: wrap;">
          <span class="badge-estado pendiente" id="modalEstadoBadge"><i class="fas fa-hourglass-half"></i> PENDIENTE</span>
          <span class="badge-pago falta-pagar" id="modalPagoBadge">FALTA PAGAR</span>
        </div>
      </div>

      <!-- Servicios (click para cambiar estado) -->
      <div class="modal-servicios" id="modalServicios">
        <!-- generado por JS -->
      </div>

      <!-- Acciones -->
      <div class="modal-acciones">
        <button class="btn-validar-modal" id="modalBtnValidar"><i class="fas fa-check"></i> Validar servicios</button>
        <button class="btn-reiniciar" id="modalBtnReiniciar"><i class="fas fa-undo-alt"></i> Reiniciar estados</button>
        <div style="font-size:12px; color:#64748b; text-align:center; width:100%;">
          <i class="fas fa-info-circle"></i> Click en servicio para cambiar
        </div>
      </div>
    </div>

    <div class="modal-feedback" id="modalFeedback"></div>
  </div>
</div>

<script src="../js/jsValidarCita.js" ></script>

</body>
</html>