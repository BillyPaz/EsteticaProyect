<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Europa · Validación de citas</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      background: #f1f4f9;
      font-family: 'Segoe UI', Roboto, system-ui, sans-serif;
      padding: 30px 20px;
      display: flex;
      justify-content: center;
      min-height: 100vh;
    }

    .app-container {
      max-width: 1280px;
      width: 100%;
    }

    /* Card principal */
    .card {
      background: #ffffff;
      border-radius: 28px;
      box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.15);
      padding: 28px 30px 35px;
      margin-bottom: 30px;
    }

    /* Header */
    .header-europa {
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
      margin-bottom: 20px;
    }
    .header-europa h1 {
      font-size: 24px;
      font-weight: 700;
      color: #1e293b;
    }
    .header-europa h1 i {
      color: #b8860b;
      margin-right: 8px;
    }
    .admin-badge {
      background: #eef2ff;
      padding: 8px 18px;
      border-radius: 40px;
      font-size: 14px;
      font-weight: 600;
      color: #1e3a8a;
    }
    .admin-badge i {
      margin-right: 6px;
      color: #2563eb;
    }

    /* Navegación */
    .nav-grid {
      display: flex;
      flex-wrap: wrap;
      gap: 8px 25px;
      background: #f8fafc;
      padding: 10px 18px;
      border-radius: 60px;
      margin-bottom: 28px;
      align-items: center;
      border: 1px solid #e9edf2;
    }
    .nav-item {
      display: flex;
      align-items: center;
      gap: 7px;
      font-size: 15px;
      font-weight: 500;
      color: #334155;
      padding: 5px 0;
      cursor: default;
      border-bottom: 2px solid transparent;
    }
    .nav-item i {
      color: #64748b;
      font-size: 15px;
      width: 20px;
    }
    .nav-item.active {
      color: #0b3b5c;
      border-bottom-color: #b8860b;
      font-weight: 600;
    }
    .nav-item.active i {
      color: #b8860b;
    }
    .nav-item.ver-sitio {
      margin-left: auto;
      color: #2563eb;
      background: #e0e7ff;
      padding: 5px 18px;
      border-radius: 30px;
      font-weight: 500;
      border-bottom: none;
    }
    .nav-item.ver-sitio i {
      color: #2563eb;
    }

    /* Sección "Validar citas" */
    .section-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
      margin-bottom: 20px;
    }
    .section-header h2 {
      font-size: 20px;
      font-weight: 700;
      color: #0f172a;
      display: flex;
      align-items: center;
      gap: 10px;
    }
    .section-header h2 i {
      color: #b8860b;
    }
    .section-header .sub {
      font-weight: 400;
      font-size: 14px;
      color: #475569;
    }
    .search-box {
      display: flex;
      align-items: center;
      background: #f1f5f9;
      padding: 6px 14px 6px 18px;
      border-radius: 40px;
      gap: 10px;
    }
    .search-box i {
      color: #64748b;
    }
    .search-box input {
      border: none;
      background: transparent;
      padding: 6px 0;
      font-size: 14px;
      outline: none;
      width: 200px;
      color: #0f172a;
    }
    .search-box input::placeholder {
      color: #94a3b8;
    }

    /* Tabla de citas del día */
    .table-responsive {
      overflow-x: auto;
      border-radius: 18px;
      border: 1px solid #eef2f6;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      font-size: 14px;
      min-width: 900px;
    }
    th {
      text-align: left;
      padding: 14px 12px;
      background: #f9fbfd;
      color: #1e293b;
      font-weight: 600;
      border-bottom: 1px solid #e2e8f0;
      white-space: nowrap;
    }
    td {
      padding: 14px 12px;
      border-bottom: 1px solid #eef2f6;
      vertical-align: middle;
      background: white;
    }
    .cliente-info {
      display: flex;
      flex-direction: column;
    }
    .cliente-info .nombre {
      font-weight: 600;
      color: #0b1e33;
    }
    .cliente-info .email {
      font-size: 12px;
      color: #64748b;
      margin-top: 2px;
    }
    .badge-estado {
      display: inline-block;
      padding: 4px 14px;
      border-radius: 40px;
      font-size: 12px;
      font-weight: 600;
      background: #e6f0ff;
      color: #1e4f8a;
    }
    .badge-estado.confirmada {
      background: #d1fae5;
      color: #0b5e42;
    }
    .badge-estado.pendiente {
      background: #fef3c7;
      color: #9d6b0b;
    }
    .badge-pago {
      font-weight: 600;
      font-size: 12px;
      padding: 4px 12px;
      border-radius: 30px;
      background: #f1f5f9;
      color: #1e293b;
      display: inline-block;
    }
    .badge-pago.cancelado {
      background: #fee2e2;
      color: #991b1b;
    }
    .badge-pago.falta-pagar {
      background: #fef9c3;
      color: #854d0e;
    }
    .badge-pago.pagado {
      background: #d1fae5;
      color: #0b5e42;
    }

    .btn-validar {
      background: #0b3b5c;
      border: none;
      color: white;
      font-weight: 600;
      padding: 6px 18px;
      border-radius: 40px;
      font-size: 13px;
      display: inline-flex;
      align-items: center;
      gap: 6px;
      cursor: pointer;
      transition: 0.1s;
      border: 1px solid transparent;
    }
    .btn-validar:hover {
      background: #1e4f7a;
    }
    .btn-validar:active {
      transform: scale(0.95);
    }
    .btn-validar i {
      color: white;
    }
    .btn-validar.success {
      background: #0b5e42;
    }

    .btn-editar {
      background: #f1f5f9;
      border: none;
      padding: 6px 14px;
      border-radius: 30px;
      font-weight: 500;
      font-size: 13px;
      color: #1e293b;
      display: inline-flex;
      align-items: center;
      gap: 6px;
      cursor: default;
    }
    .btn-editar i {
      color: #64748b;
    }

    /* Modal */
    .modal-overlay {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(15, 23, 42, 0.5);
      backdrop-filter: blur(4px);
      z-index: 1000;
      justify-content: center;
      align-items: center;
      padding: 20px;
    }
    .modal-overlay.active {
      display: flex;
    }
    .modal {
      background: white;
      border-radius: 32px;
      max-width: 900px;
      width: 100%;
      max-height: 90vh;
      overflow-y: auto;
      padding: 32px 35px 35px;
      box-shadow: 0 30px 60px rgba(0, 0, 0, 0.3);
      animation: modalIn 0.25s ease-out;
    }
    @keyframes modalIn {
      from {
        opacity: 0;
        transform: scale(0.95) translateY(10px);
      }
      to {
        opacity: 1;
        transform: scale(1) translateY(0);
      }
    }
    .modal-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 22px;
      padding-bottom: 14px;
      border-bottom: 1px solid #eef2f6;
    }
    .modal-header h3 {
      font-size: 20px;
      font-weight: 700;
      color: #0f172a;
      display: flex;
      align-items: center;
      gap: 10px;
    }
    .modal-header h3 i {
      color: #b8860b;
    }
    .modal-close {
      background: #f1f5f9;
      border: none;
      width: 40px;
      height: 40px;
      border-radius: 40px;
      font-size: 18px;
      color: #475569;
      cursor: pointer;
      transition: 0.1s;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .modal-close:hover {
      background: #e2e8f0;
    }

    .modal-grid {
      display: grid;
      grid-template-columns: 1fr 1.6fr 0.8fr;
      gap: 20px;
      margin-bottom: 20px;
    }
    .modal-resumen {
      display: flex;
      flex-direction: column;
      gap: 6px;
    }
    .modal-resumen .cliente {
      font-weight: 700;
      font-size: 17px;
      color: #0b1e33;
    }
    .modal-resumen .detalle {
      font-size: 14px;
      color: #334155;
      display: flex;
      flex-wrap: wrap;
      gap: 6px 16px;
    }
    .modal-resumen .detalle i {
      color: #64748b;
      width: 18px;
    }
    .modal-resumen .detalle span {
      background: #eef2f6;
      padding: 2px 12px;
      border-radius: 30px;
      font-size: 12px;
      font-weight: 500;
    }

    .modal-servicios {
      display: flex;
      flex-direction: column;
      gap: 8px;
      border-left: 1px dashed #d4dfe9;
      padding-left: 20px;
    }
    .servicio-item {
      display: flex;
      align-items: center;
      gap: 12px;
      background: white;
      padding: 8px 14px 8px 12px;
      border-radius: 40px;
      border: 1px solid #e2eaf2;
      cursor: pointer;
      transition: 0.1s;
    }
    .servicio-item:hover {
      background: #f5f9ff;
      border-color: #b0c8e0;
    }
    .servicio-item .nombre-serv {
      font-weight: 500;
      font-size: 14px;
      flex: 1;
    }
    .servicio-item .estado-serv {
      font-size: 12px;
      font-weight: 600;
      padding: 2px 12px;
      border-radius: 30px;
      transition: 0.1s;
      white-space: nowrap;
    }
    .servicio-item .estado-serv.realizado {
      background: #d1fae5;
      color: #0b5e42;
    }
    .servicio-item .estado-serv.no-realizado {
      background: #fee2e2;
      color: #991b1b;
    }
    .servicio-item .estado-serv.pendiente {
      background: #fef3c7;
      color: #9d6b0b;
    }
    .servicio-item i {
      color: #94a3b8;
      font-size: 14px;
    }

    .modal-acciones {
      display: flex;
      flex-direction: column;
      gap: 10px;
      align-items: flex-end;
    }
    .modal-acciones .btn-validar-modal {
      background: #0b3b5c;
      border: none;
      color: white;
      font-weight: 600;
      padding: 12px 24px;
      border-radius: 40px;
      font-size: 15px;
      display: flex;
      align-items: center;
      gap: 8px;
      cursor: pointer;
      width: 100%;
      justify-content: center;
      transition: 0.1s;
    }
    .modal-acciones .btn-validar-modal:hover {
      background: #1e4f7a;
    }
    .modal-acciones .btn-validar-modal:active {
      transform: scale(0.97);
    }
    .modal-acciones .btn-validar-modal.success {
      background: #0b5e42;
    }
    .modal-acciones .btn-reiniciar {
      background: transparent;
      border: 1px solid #d0d9e3;
      color: #1e293b;
      font-weight: 500;
      padding: 8px 16px;
      border-radius: 40px;
      font-size: 13px;
      display: flex;
      align-items: center;
      gap: 6px;
      cursor: pointer;
      width: 100%;
      justify-content: center;
      transition: 0.1s;
    }
    .modal-acciones .btn-reiniciar:hover {
      background: #f1f5f9;
      border-color: #b0c0d0;
    }

    .modal-feedback {
      margin-top: 16px;
      padding: 12px 18px;
      border-radius: 40px;
      font-weight: 500;
      display: none;
    }
    .modal-feedback.show {
      display: block;
    }
    .modal-feedback.success {
      background: #d1fae5;
      color: #0b5e42;
      border-left: 4px solid #0b5e42;
    }
    .modal-feedback.warning {
      background: #fef3c7;
      color: #9d6b0b;
      border-left: 4px solid #9d6b0b;
    }
    .modal-feedback.error {
      background: #fee2e2;
      color: #991b1b;
      border-left: 4px solid #991b1b;
    }
    .modal-feedback i {
      margin-right: 8px;
    }

    .demo-footer {
      margin-top: 20px;
      font-size: 13px;
      color: #64748b;
      background: #f8fafc;
      padding: 12px 20px;
      border-radius: 40px;
      border: 1px solid #e9edf2;
      display: inline-block;
    }
    .demo-footer i {
      color: #b8860b;
      margin-right: 6px;
    }

    /* Responsive */
    @media (max-width: 850px) {
      .modal-grid {
        grid-template-columns: 1fr;
        gap: 16px;
      }
      .modal-servicios {
        border-left: none;
        padding-left: 0;
        border-top: 1px solid #e2eaf2;
        padding-top: 16px;
      }
      .modal-acciones {
        align-items: stretch;
      }
      .search-box input {
        width: 140px;
      }
    }
    @media (max-width: 600px) {
      .card {
        padding: 18px 16px;
      }
      .modal {
        padding: 20px 18px;
      }
    }
  </style>
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

<script>
  // ---- DATOS ----
  const citas = [
    { id: 1024, nombre: 'Andrés Batres', email: 'abatres@correo.com', telefono: '4478 9012', fecha: '16/08/2026', hora: '11:00', barbero: 'Nada', pago: 'FALTA PAGAR', estado: 'PENDIENTE', servicios: ['Corte normal', 'Afeitado clásico', 'Cepillado (extra)'] },
    { id: 1025, nombre: 'María Fernanda López', email: 'mfernanda@correo.com', telefono: '5512 4478', fecha: '16/08/2026', hora: '09:30', barbero: 'Otto Mérida', pago: 'CANCELADO', estado: 'CONFIRMADA', servicios: ['Corte completo'] },
    { id: 1026, nombre: 'Gabriela Morales', email: 'gmorales@correo.com', telefono: '3390 5521', fecha: '16/08/2026', hora: '14:00', barbero: 'Kevin Solís', pago: 'CANCELADO', estado: 'CONFIRMADA', servicios: ['Aislado permanente'] },
    { id: 1027, nombre: 'Lucia Herrera', email: 'lherrera@correo.com', telefono: '5678 1290', fecha: '17/08/2026', hora: '16:30', barbero: 'Otto Mérida', pago: 'CANCELADO', estado: 'CONFIRMADA', servicios: ['Maquillaje & peinado'] },
    { id: 1028, nombre: 'Josué Ramírez', email: 'jramirez@correo.com', telefono: '2201 7745', fecha: '17/08/2026', hora: '10:15', barbero: 'Kevin Solís', pago: 'FALTA PAGAR', estado: 'PENDIENTE', servicios: ['Planchado'] },
    { id: 1029, nombre: 'Diana Cruz', email: 'dcruz@correo.com', telefono: '7789 3320', fecha: '18/08/2026', hora: '12:45', barbero: 'Otto Mérida', pago: 'CANCELADO', estado: 'CONFIRMADA', servicios: ['Corte normal'] }
  ];

  // Filtramos solo citas de hoy (16/08/2026) para la tabla
  const citasHoy = citas.filter(c => c.fecha === '16/08/2026');

  // Estado de servicios para la cita seleccionada en el modal
  let modalServiciosEstado = {};
  let citaSeleccionada = null;

  // ---- RENDER TABLA ----
  function renderTabla(lista) {
    const tbody = document.getElementById('tablaCitasDia');
    const data = lista || citasHoy;
    if (data.length === 0) {
      tbody.innerHTML = `<tr><td colspan="8" style="text-align:center; padding:30px; color:#64748b;">No hay citas para hoy</td></tr>`;
      return;
    }
    let html = '';
    data.forEach(c => {
      const estadoClass = c.estado === 'CONFIRMADA' ? 'confirmada' : 'pendiente';
      const pagoClass = c.pago === 'CANCELADO' ? 'cancelado' : (c.pago === 'PAGADO' ? 'pagado' : 'falta-pagar');
      html += `<tr>
        <td><div class="cliente-info"><span class="nombre">${c.nombre}</span><span class="email">${c.email}</span></div></td>
        <td>${c.telefono}</td>
        <td>${c.servicios[0]}</td>
        <td>${c.barbero}</td>
        <td>${c.hora}</td>
        <td><span class="badge-pago ${pagoClass}">${c.pago}</span></td>
        <td><span class="badge-estado ${estadoClass}">${c.estado}</span></td>
        <td><button class="btn-validar" data-id="${c.id}"><i class="fas fa-check-circle"></i> Validar</button></td>
      </tr>`;
    });
    tbody.innerHTML = html;

    // Asignar eventos a botones "Validar"
    document.querySelectorAll('.btn-validar').forEach(btn => {
      btn.addEventListener('click', function() {
        const id = parseInt(this.dataset.id);
        abrirModal(id);
      });
    });
  }

  // ---- FILTRAR ----
  function filtrarTabla() {
    const query = document.getElementById('searchInput').value.toLowerCase().trim();
    if (!query) {
      renderTabla(citasHoy);
      return;
    }
    const filtradas = citasHoy.filter(c => 
      c.nombre.toLowerCase().includes(query) || 
      c.email.toLowerCase().includes(query) ||
      c.telefono.includes(query)
    );
    renderTabla(filtradas);
  }

  // ---- MODAL ----
  function abrirModal(id) {
    const cita = citas.find(c => c.id === id);
    if (!cita) return;
    citaSeleccionada = cita;

    // Inicializar estados de servicios: todos pendientes por defecto, pero podemos poner algunos ejemplos
    // Para demo, ponemos estados variados
    modalServiciosEstado = {};
    cita.servicios.forEach((s, idx) => {
      if (idx === 0) modalServiciosEstado[s] = 'realizado';
      else if (idx === 1) modalServiciosEstado[s] = 'no-realizado';
      else modalServiciosEstado[s] = 'pendiente';
    });

    // Llenar resumen
    document.getElementById('modalCitaId').textContent = '#' + cita.id;
    document.getElementById('modalCliente').textContent = cita.nombre;
    document.getElementById('modalTelefono').textContent = cita.telefono;
    document.getElementById('modalFecha').textContent = cita.fecha;
    document.getElementById('modalHora').textContent = cita.hora;
    document.getElementById('modalBarbero').textContent = cita.barbero;

    const estadoClass = cita.estado === 'CONFIRMADA' ? 'confirmada' : 'pendiente';
    const estadoBadge = document.getElementById('modalEstadoBadge');
    estadoBadge.className = `badge-estado ${estadoClass}`;
    estadoBadge.innerHTML = cita.estado === 'CONFIRMADA' ? '<i class="fas fa-check-circle"></i> CONFIRMADA' : '<i class="fas fa-hourglass-half"></i> PENDIENTE';

    const pagoClass = cita.pago === 'CANCELADO' ? 'cancelado' : (cita.pago === 'PAGADO' ? 'pagado' : 'falta-pagar');
    const pagoBadge = document.getElementById('modalPagoBadge');
    pagoBadge.className = `badge-pago ${pagoClass}`;
    pagoBadge.textContent = cita.pago;

    renderServiciosModal();
    document.getElementById('modalFeedback').className = 'modal-feedback';
    document.getElementById('modalFeedback').textContent = '';
    document.getElementById('modalOverlay').classList.add('active');
    document.body.style.overflow = 'hidden';
  }

  function cerrarModal() {
    document.getElementById('modalOverlay').classList.remove('active');
    document.body.style.overflow = '';
  }

  function renderServiciosModal() {
    const container = document.getElementById('modalServicios');
    if (!citaSeleccionada) return;
    let html = '';
    citaSeleccionada.servicios.forEach(s => {
      const estado = modalServiciosEstado[s] || 'pendiente';
      let icono = '';
      let label = '';
      if (estado === 'realizado') { icono = '<i class="fas fa-check-circle"></i>'; label = 'Realizado'; }
      else if (estado === 'no-realizado') { icono = '<i class="fas fa-times-circle"></i>'; label = 'No realizado'; }
      else { icono = '<i class="fas fa-clock"></i>'; label = 'Pendiente'; }
      html += `<div class="servicio-item" data-servicio="${s}">
        <i class="fas fa-cut"></i>
        <span class="nombre-serv">${s}</span>
        <span class="estado-serv ${estado}">${icono} ${label}</span>
      </div>`;
    });
    container.innerHTML = html;

    // Eventos click en servicios
    container.querySelectorAll('.servicio-item').forEach(item => {
      item.addEventListener('click', function() {
        const servicio = this.dataset.servicio;
        if (!servicio) return;
        const current = modalServiciosEstado[servicio] || 'pendiente';
        let next;
        if (current === 'realizado') next = 'no-realizado';
        else if (current === 'no-realizado') next = 'pendiente';
        else next = 'realizado';
        modalServiciosEstado[servicio] = next;
        renderServiciosModal();
        // limpiar feedback
        const fb = document.getElementById('modalFeedback');
        fb.className = 'modal-feedback';
        fb.textContent = '';
      });
    });
  }

  // ---- ACCIONES MODAL ----
  document.getElementById('modalBtnValidar').addEventListener('click', function() {
    if (!citaSeleccionada) return;
    const estados = Object.values(modalServiciosEstado);
    const todosRealizados = estados.every(v => v === 'realizado');
    const algunNoRealizado = estados.some(v => v === 'no-realizado');
    const fb = document.getElementById('modalFeedback');

    if (todosRealizados) {
      // Actualizar cita
      citaSeleccionada.pago = 'PAGADO';
      citaSeleccionada.estado = 'CONFIRMADA';
      // Actualizar tabla
      renderTabla(citasHoy);
      // Actualizar resumen modal
      const pagoBadge = document.getElementById('modalPagoBadge');
      pagoBadge.className = 'badge-pago pagado';
      pagoBadge.textContent = 'PAGADO';
      const estadoBadge = document.getElementById('modalEstadoBadge');
      estadoBadge.className = 'badge-estado confirmada';
      estadoBadge.innerHTML = '<i class="fas fa-check-circle"></i> CONFIRMADA';
      // Feedback
      fb.className = 'modal-feedback show success';
      fb.innerHTML = '<i class="fas fa-check-circle"></i> Todos los servicios realizados. ¡Cobro en caja habilitado!';
      this.classList.add('success');
      setTimeout(() => this.classList.remove('success'), 2000);
      // Refrescar tabla
      renderTabla(citasHoy);
    } else if (algunNoRealizado) {
      fb.className = 'modal-feedback show error';
      fb.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Hay servicios marcados como "No realizado". Confirma o cambia su estado.';
    } else {
      fb.className = 'modal-feedback show warning';
      fb.innerHTML = '<i class="fas fa-clock"></i> Hay servicios pendientes. Confirma su estado antes de validar.';
    }
  });

  document.getElementById('modalBtnReiniciar').addEventListener('click', function() {
    if (!citaSeleccionada) return;
    // Reiniciar estados: primero realizado, segundo no realizado, resto pendiente
    citaSeleccionada.servicios.forEach((s, idx) => {
      if (idx === 0) modalServiciosEstado[s] = 'realizado';
      else if (idx === 1) modalServiciosEstado[s] = 'no-realizado';
      else modalServiciosEstado[s] = 'pendiente';
    });
    // Resetear cita a estado original (para demo, lo dejamos PENDIENTE / FALTA PAGAR)
    citaSeleccionada.pago = 'FALTA PAGAR';
    citaSeleccionada.estado = 'PENDIENTE';
    renderServiciosModal();
    // Actualizar badges
    const pagoBadge = document.getElementById('modalPagoBadge');
    pagoBadge.className = 'badge-pago falta-pagar';
    pagoBadge.textContent = 'FALTA PAGAR';
    const estadoBadge = document.getElementById('modalEstadoBadge');
    estadoBadge.className = 'badge-estado pendiente';
    estadoBadge.innerHTML = '<i class="fas fa-hourglass-half"></i> PENDIENTE';
    const fb = document.getElementById('modalFeedback');
    fb.className = 'modal-feedback show success';
    fb.innerHTML = '<i class="fas fa-undo-alt"></i> Estados reiniciados. Cita vuelve a PENDIENTE / FALTA PAGAR.';
    renderTabla(citasHoy);
  });

  // Cerrar modal con click fuera
  document.getElementById('modalOverlay').addEventListener('click', function(e) {
    if (e.target === this) cerrarModal();
  });

  // ---- INICIALIZAR ----
  renderTabla(citasHoy);

  // Exponer filtro global
  window.filtrarTabla = filtrarTabla;
  window.cerrarModal = cerrarModal;
</script>

</body>
</html>