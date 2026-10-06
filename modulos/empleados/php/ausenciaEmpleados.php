<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Europa · Ausencia de empleado</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <style>
    /* ====== RESET Y BASE ====== */
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
      background: #f5f0eb;
      font-family: 'Inter', 'Segoe UI', Roboto, system-ui, sans-serif;
      min-height: 100vh;
      color: #1e1e1e;
      padding: 30px 40px 50px 40px;
    }

    /* ====== HEADER DE PÁGINA ====== */
    .page-header {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      margin-bottom: 28px;
      flex-wrap: wrap;
      gap: 16px;
      max-width: 1400px;
      margin-left: auto;
      margin-right: auto;
    }
    .page-header .left h1 {
      font-size: 32px;
      font-weight: 800;
      color: #1a1a1a;
      letter-spacing: -0.5px;
      line-height: 1.2;
    }
    .page-header .left h1 em {
      font-style: italic;
      color: #b8860b;
      font-weight: 800;
    }
    .page-header .left p {
      font-size: 14px;
      color: #777;
      margin-top: 4px;
      font-weight: 400;
    }
    .badge-admin {
      background: #e8d5a3;
      color: #8a6d1f;
      padding: 6px 20px;
      border-radius: 40px;
      font-size: 11px;
      font-weight: 700;
      letter-spacing: 0.8px;
      text-transform: uppercase;
    }

    /* ====== STATS MINI ====== */
    .stats-mini {
      display: flex;
      gap: 16px;
      flex-wrap: wrap;
      max-width: 1400px;
      margin: 0 auto 20px auto;
    }
    .stat-card {
      background: #ffffff;
      border-radius: 16px;
      padding: 16px 22px;
      border: 1px solid #efe8e0;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);
      display: flex;
      align-items: center;
      gap: 14px;
      flex: 1;
      min-width: 180px;
    }
    .stat-card .icono {
      width: 42px;
      height: 42px;
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 18px;
      flex-shrink: 0;
    }
    .stat-card .icono.amarillo { background: #fef3c7; color: #9d6b0b; }
    .stat-card .icono.rojo { background: #fce8e8; color: #b33a3a; }
    .stat-card .icono.verde { background: #e3f2e9; color: #2e7d4f; }
    .stat-card .icono.azul { background: #e0e7ff; color: #1e3a8a; }
    .stat-card .info {
      display: flex;
      flex-direction: column;
    }
    .stat-card .info .numero {
      font-size: 22px;
      font-weight: 800;
      color: #1a1a1a;
      line-height: 1;
    }
    .stat-card .info .label {
      font-size: 12px;
      color: #888;
      font-weight: 500;
      margin-top: 2px;
    }

    /* ====== CARD PRINCIPAL ====== */
    .card-ausencias {
      background: #ffffff;
      border-radius: 20px;
      box-shadow: 0 4px 24px rgba(0, 0, 0, 0.06);
      padding: 28px 32px 32px 32px;
      border: 1px solid #efe8e0;
      max-width: 1400px;
      margin: 0 auto;
    }

    .card-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
      gap: 16px;
      margin-bottom: 24px;
    }
    .card-header h2 {
      font-size: 20px;
      font-weight: 700;
      color: #1a1a1a;
    }
    .card-header .actions {
      display: flex;
      align-items: center;
      gap: 12px;
      flex-wrap: wrap;
    }
    .search-box {
      display: flex;
      align-items: center;
      background: #f8f5f2;
      border: 1px solid #e8e0d8;
      border-radius: 40px;
      padding: 8px 18px;
      gap: 10px;
      transition: 0.2s;
    }
    .search-box:focus-within {
      border-color: #b8860b;
      background: #ffffff;
    }
    .search-box i { color: #aaa; font-size: 14px; }
    .search-box input {
      border: none;
      background: transparent;
      outline: none;
      font-size: 14px;
      width: 180px;
      color: #1a1a1a;
      font-family: inherit;
    }
    .search-box input::placeholder { color: #bbb; }

    .filter-select {
      background: #f8f5f2;
      border: 1px solid #e8e0d8;
      border-radius: 40px;
      padding: 8px 16px;
      font-size: 13px;
      font-family: inherit;
      color: #1a1a1a;
      outline: none;
      cursor: pointer;
      transition: 0.2s;
      appearance: none;
      -webkit-appearance: none;
      background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='10' viewBox='0 0 12 12'%3E%3Cpath fill='%23888' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
      background-repeat: no-repeat;
      background-position: right 14px center;
      padding-right: 32px;
    }
    .filter-select:focus {
      border-color: #b8860b;
      background-color: #fff;
    }

    .btn-nuevo {
      background: #1a1a1a;
      color: #ffffff;
      border: none;
      padding: 10px 24px;
      border-radius: 40px;
      font-size: 13px;
      font-weight: 600;
      display: inline-flex;
      align-items: center;
      gap: 8px;
      cursor: pointer;
      transition: 0.2s;
      font-family: inherit;
      letter-spacing: 0.3px;
    }
    .btn-nuevo:hover {
      background: #333;
      transform: translateY(-1px);
      box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    .btn-nuevo i { font-size: 12px; }

    /* ====== TABLA ====== */
    .table-responsive {
      overflow-x: auto;
      border-radius: 12px;
      border: 1px solid #f0ebe5;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      font-size: 14px;
      min-width: 1000px;
    }
    thead th {
      text-align: left;
      padding: 14px 16px;
      background: #faf8f6;
      color: #888;
      font-weight: 600;
      font-size: 11px;
      letter-spacing: 0.8px;
      text-transform: uppercase;
      border-bottom: 1px solid #efe8e0;
      white-space: nowrap;
    }
    tbody td {
      padding: 16px 16px;
      border-bottom: 1px solid #f5f0eb;
      vertical-align: middle;
      color: #1a1a1a;
      font-size: 14px;
    }
    tbody tr:last-child td { border-bottom: none; }
    tbody tr:hover { background: #fdfcfa; }

    .empleado-info {
      display: flex;
      align-items: center;
      gap: 12px;
    }
    .empleado-info .avatar-sm {
      width: 36px;
      height: 36px;
      border-radius: 50%;
      background: #f0e8d8;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 700;
      font-size: 13px;
      color: #8a6d1f;
      flex-shrink: 0;
    }
    .empleado-info .info .nombre {
      font-weight: 600;
      color: #1a1a1a;
      font-size: 14px;
    }

    .badge-tipo {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 5px 14px;
      border-radius: 40px;
      font-size: 11px;
      font-weight: 700;
      letter-spacing: 0.3px;
      text-transform: uppercase;
    }
    .badge-tipo.enfermedad { background: #fce8e8; color: #b33a3a; }
    .badge-tipo.personal { background: #e0e7ff; color: #1e3a8a; }
    .badge-tipo.vacaciones { background: #e3f2e9; color: #2e7d4f; }
    .badge-tipo.permiso { background: #fef3c7; color: #9d6b0b; }
    .badge-tipo.otro { background: #f1f5f9; color: #475569; }

    .badge-estado {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 5px 16px;
      border-radius: 40px;
      font-size: 11px;
      font-weight: 700;
      letter-spacing: 0.5px;
      text-transform: uppercase;
    }
    .badge-estado.justificada { background: #e3f2e9; color: #2e7d4f; }
    .badge-estado.pendiente { background: #fef3c7; color: #9d6b0b; }
    .badge-estado.no-justificada { background: #fce8e8; color: #b33a3a; }
    .badge-estado .dot {
      width: 6px;
      height: 6px;
      border-radius: 50%;
      background: currentColor;
      display: inline-block;
    }

    .btn-accion {
      background: transparent;
      border: 1px solid #e0d8d0;
      padding: 6px 18px;
      border-radius: 40px;
      font-size: 12px;
      font-weight: 600;
      cursor: pointer;
      transition: 0.15s;
      font-family: inherit;
      display: inline-flex;
      align-items: center;
      gap: 6px;
      color: #555;
    }
    .btn-accion:hover {
      border-color: #b8860b;
      color: #b8860b;
      background: #fdf9f3;
    }
    .btn-accion.eliminar:hover {
      border-color: #d94a4a;
      color: #d94a4a;
      background: #fdf5f5;
    }
    .btn-accion.justificar:hover {
      border-color: #2e7d4f;
      color: #2e7d4f;
      background: #f0faf4;
    }

    /* ====== MODAL ====== */
    .modal-overlay {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(26, 26, 26, 0.5);
      backdrop-filter: blur(4px);
      z-index: 1000;
      justify-content: center;
      align-items: center;
      padding: 20px;
    }
    .modal-overlay.active { display: flex; }
    .modal {
      background: #ffffff;
      border-radius: 24px;
      max-width: 620px;
      width: 100%;
      max-height: 90vh;
      overflow-y: auto;
      padding: 32px 36px 36px 36px;
      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
      animation: modalIn 0.25s ease-out;
      border: 1px solid #efe8e0;
    }
    @keyframes modalIn {
      from { opacity: 0; transform: scale(0.96) translateY(10px); }
      to { opacity: 1; transform: scale(1) translateY(0); }
    }
    .modal-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 24px;
      padding-bottom: 16px;
      border-bottom: 1px solid #f0ebe5;
    }
    .modal-header h3 {
      font-size: 20px;
      font-weight: 700;
      color: #1a1a1a;
      display: flex;
      align-items: center;
      gap: 10px;
    }
    .modal-header h3 i { color: #b8860b; }
    .modal-close {
      background: #f5f0eb;
      border: none;
      width: 38px;
      height: 38px;
      border-radius: 50%;
      font-size: 16px;
      color: #666;
      cursor: pointer;
      transition: 0.15s;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .modal-close:hover { background: #e8e0d8; color: #1a1a1a; }

    /* Formulario */
    .form-group {
      margin-bottom: 20px;
      position: relative;
    }
    .form-group label {
      display: block;
      font-size: 13px;
      font-weight: 600;
      color: #444;
      margin-bottom: 6px;
      letter-spacing: 0.2px;
    }
    .form-group label i {
      color: #b8860b;
      margin-right: 6px;
      width: 16px;
    }
    .form-group label .required { color: #d94a4a; margin-left: 4px; }
    .form-group label .opcional {
      color: #999;
      font-weight: 400;
      font-size: 11px;
      margin-left: 6px;
      text-transform: lowercase;
    }

    .form-group select,
    .form-group input[type="date"],
    .form-group input[type="time"],
    .form-group input[type="text"],
    .form-group textarea {
      width: 100%;
      padding: 12px 16px;
      border: 1px solid #e0d8d0;
      border-radius: 12px;
      font-size: 14px;
      font-family: inherit;
      color: #1a1a1a;
      background: #fdfcfa;
      outline: none;
      transition: 0.2s;
      appearance: none;
      -webkit-appearance: none;
    }
    .form-group select {
      background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23888' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
      background-repeat: no-repeat;
      background-position: right 16px center;
      cursor: pointer;
    }
    .form-group textarea {
      resize: vertical;
      min-height: 80px;
      line-height: 1.5;
    }
    .form-group select:focus,
    .form-group input:focus,
    .form-group textarea:focus {
      border-color: #b8860b;
      background: #ffffff;
      box-shadow: 0 0 0 3px rgba(184, 134, 11, 0.08);
    }
    .form-group select:disabled {
      background: #f5f0eb;
      color: #999;
      cursor: not-allowed;
      opacity: 0.7;
    }

    /* Campo con error */
    .form-group.error select,
    .form-group.error input,
    .form-group.error textarea {
      border-color: #d94a4a;
      background: #fdf5f5;
    }
    .form-group.error select:focus,
    .form-group.error input:focus,
    .form-group.error textarea:focus {
      box-shadow: 0 0 0 3px rgba(217, 74, 74, 0.08);
    }

    .error-msg {
      display: none;
      font-size: 12px;
      color: #d94a4a;
      margin-top: 6px;
      align-items: center;
      gap: 6px;
    }
    .error-msg.show { display: flex; }
    .error-msg i { font-size: 12px; }

    .form-row {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 16px;
    }

    /* Info del horario del empleado */
    .info-horario {
      background: #f8f5f2;
      border-radius: 12px;
      padding: 12px 16px;
      border: 1px solid #efe8e0;
      font-size: 13px;
      color: #555;
      display: flex;
      align-items: center;
      gap: 10px;
      margin-top: 8px;
    }
    .info-horario i {
      color: #b8860b;
      font-size: 16px;
    }
    .info-horario strong {
      color: #1a1a1a;
    }
    .info-horario.sin-horario {
      background: #fef3c7;
      border-color: #fde68a;
      color: #9d6b0b;
    }
    .info-horario.sin-horario i { color: #9d6b0b; }

    /* Alert de advertencia */
    .alert-warning {
      background: #fef3c7;
      color: #9d6b0b;
      padding: 12px 16px;
      border-radius: 12px;
      font-size: 13px;
      display: flex;
      align-items: flex-start;
      gap: 10px;
      margin-bottom: 20px;
      border-left: 4px solid #b8860b;
    }
    .alert-warning i {
      font-size: 16px;
      margin-top: 1px;
      flex-shrink: 0;
    }
    .alert-warning.error {
      background: #fce8e8;
      color: #b33a3a;
      border-left-color: #d94a4a;
    }
    .alert-warning.success {
      background: #e3f2e9;
      color: #2e7d4f;
      border-left-color: #2e7d4f;
    }

    /* Botones del modal */
    .modal-actions {
      display: flex;
      justify-content: flex-end;
      gap: 12px;
      margin-top: 28px;
      padding-top: 20px;
      border-top: 1px solid #f0ebe5;
    }
    .btn-cancelar {
      background: transparent;
      border: 1px solid #e0d8d0;
      color: #555;
      padding: 10px 24px;
      border-radius: 40px;
      font-size: 14px;
      font-weight: 600;
      cursor: pointer;
      transition: 0.15s;
      font-family: inherit;
    }
    .btn-cancelar:hover { background: #f5f0eb; border-color: #ccc; }
    .btn-guardar {
      background: #1a1a1a;
      border: none;
      color: #ffffff;
      padding: 10px 28px;
      border-radius: 40px;
      font-size: 14px;
      font-weight: 600;
      cursor: pointer;
      transition: 0.15s;
      font-family: inherit;
      display: inline-flex;
      align-items: center;
      gap: 8px;
    }
    .btn-guardar:hover {
      background: #333;
      transform: translateY(-1px);
      box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    .btn-guardar:disabled {
      background: #ccc;
      cursor: not-allowed;
      transform: none;
      box-shadow: none;
    }

    /* Toast */
    .toast {
      position: fixed;
      bottom: 30px;
      right: 30px;
      background: #2e7d4f;
      color: #ffffff;
      padding: 14px 28px;
      border-radius: 40px;
      font-weight: 600;
      font-size: 14px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.15);
      display: none;
      align-items: center;
      gap: 10px;
      z-index: 1100;
      animation: toastIn 0.3s ease-out;
      max-width: 400px;
    }
    .toast.show { display: flex; }
    .toast.error { background: #b33a3a; }
    @keyframes toastIn {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
    }

    /* Responsive */
    @media (max-width: 768px) {
      body { padding: 20px 16px 40px 16px; }
      .page-header .left h1 { font-size: 24px; }
      .card-ausencias { padding: 20px 16px; }
      .form-row { grid-template-columns: 1fr; }
      .card-header { flex-direction: column; align-items: stretch; }
      .card-header .actions { flex-direction: column; }
      .search-box input { width: 100%; }
      .stats-mini { flex-direction: column; }
    }
  </style>
</head>
<body>

<!-- ====== HEADER DE PÁGINA ====== -->
<div class="page-header">
  <div class="left">
    <h1>Ausencia de <em>empleado</em></h1>
    <p>Registra y gestiona las ausencias del personal — justificación y seguimiento.</p>
  </div>
  <div class="right">
    <span class="badge-admin">Admin</span>
  </div>
</div>

<!-- ====== STATS MINI ====== -->
<div class="stats-mini">
  <div class="stat-card">
    <div class="icono amarillo"><i class="fas fa-calendar-xmark"></i></div>
    <div class="info">
      <span class="numero" id="statTotal">0</span>
      <span class="label">Total ausencias</span>
    </div>
  </div>
  <div class="stat-card">
    <div class="icono rojo"><i class="fas fa-clock"></i></div>
    <div class="info">
      <span class="numero" id="statPendientes">0</span>
      <span class="label">Pendientes</span>
    </div>
  </div>
  <div class="stat-card">
    <div class="icono verde"><i class="fas fa-check-circle"></i></div>
    <div class="info">
      <span class="numero" id="statJustificadas">0</span>
      <span class="label">Justificadas</span>
    </div>
  </div>
  <div class="stat-card">
    <div class="icono azul"><i class="fas fa-calendar-day"></i></div>
    <div class="info">
      <span class="numero" id="statEsteMes">0</span>
      <span class="label">Este mes</span>
    </div>
  </div>
</div>

<!-- ====== CARD ====== -->
<div class="card-ausencias">
  <div class="card-header">
    <h2>Registro de ausencias</h2>
    <div class="actions">
      <select class="filter-select" id="filtroEstado" onchange="filtrarAusencias()">
        <option value="">Todos los estados</option>
        <option value="Pendiente">Pendientes</option>
        <option value="Justificada">Justificadas</option>
        <option value="No justificada">No justificadas</option>
      </select>
      <div class="search-box">
        <i class="fas fa-search"></i>
        <input type="text" id="buscarAusencia" placeholder="Buscar empleado..." oninput="filtrarAusencias()">
      </div>
      <button class="btn-nuevo" onclick="abrirModal()">
        <i class="fas fa-plus"></i> Nueva ausencia
      </button>
    </div>
  </div>

  <!-- Tabla -->
  <div class="table-responsive">
    <table>
      <thead>
        <tr>
          <th>EMPLEADO</th>
          <th>FECHA</th>
          <th>TIPO</th>
          <th>MOTIVO</th>
          <th>ESTADO</th>
          <th>ACCIONES</th>
        </tr>
      </thead>
      <tbody id="tablaAusencias">
        <!-- Generado por JS -->
      </tbody>
    </table>
  </div>
</div>

<!-- ====== MODAL ====== -->
<div class="modal-overlay" id="modalAusencia">
  <div class="modal">
    <div class="modal-header">
      <h3><i class="fas fa-calendar-xmark"></i> <span id="modalTitulo">Registrar ausencia</span></h3>
      <button class="modal-close" onclick="cerrarModal()"><i class="fas fa-times"></i></button>
    </div>

    <!-- Alert informativo -->
    <div class="alert-warning" id="alertInfo" style="display:none;">
      <i class="fas fa-info-circle"></i>
      <div>
        <strong id="alertInfoTitulo">Información</strong>
        <div id="alertInfoTexto" style="margin-top:4px; font-weight:400;"></div>
      </div>
    </div>

    <form id="formAusencia" onsubmit="guardarAusencia(event)" novalidate>
      <div class="form-group" id="groupEmpleado">
        <label><i class="fas fa-user"></i> Empleado <span class="required">*</span></label>
        <select id="empleadoSelect" required onchange="onEmpleadoChange()">
          <option value="">Seleccionar empleado...</option>
          <option value="2">2 — Otto Mérida</option>
          <option value="3">3 — Kevin Solís</option>
          <option value="4">4 — María Fernanda López</option>
          <option value="5">5 — Lucia Herrera</option>
          <option value="6">6 — Carlos Gutiérrez</option>
        </select>
        <div class="error-msg" id="errorEmpleado">
          <i class="fas fa-exclamation-circle"></i> Debes seleccionar un empleado
        </div>
        <div class="info-horario" id="infoHorario" style="display:none;">
          <!-- Info del horario del empleado -->
        </div>
      </div>

      <div class="form-row">
        <div class="form-group" id="groupFecha">
          <label><i class="fas fa-calendar-day"></i> Fecha de ausencia <span class="required">*</span></label>
          <input type="date" id="fechaAusencia" required onchange="onFechaChange()">
          <div class="error-msg" id="errorFecha">
            <i class="fas fa-exclamation-circle"></i> Debes seleccionar una fecha
          </div>
        </div>
        <div class="form-group" id="groupTipo">
          <label><i class="fas fa-tag"></i> Tipo de ausencia <span class="required">*</span></label>
          <select id="tipoAusencia" required>
            <option value="">Seleccionar tipo...</option>
            <option value="Enfermedad">Enfermedad</option>
            <option value="Personal">Personal</option>
            <option value="Vacaciones">Vacaciones</option>
            <option value="Permiso">Permiso</option>
            <option value="Otro">Otro</option>
          </select>
          <div class="error-msg" id="errorTipo">
            <i class="fas fa-exclamation-circle"></i> Debes seleccionar un tipo
          </div>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group" id="groupHoraInicio">
          <label><i class="fas fa-sign-in-alt"></i> Hora entrada <span class="opcional">(opcional)</span></label>
          <input type="time" id="horaInicio" onchange="validarHoras()">
          <div class="error-msg" id="errorHoraInicio">
            <i class="fas fa-exclamation-circle"></i> Hora inválida
          </div>
        </div>
        <div class="form-group" id="groupHoraFin">
          <label><i class="fas fa-sign-out-alt"></i> Hora salida <span class="opcional">(opcional)</span></label>
          <input type="time" id="horaFin" onchange="validarHoras()">
          <div class="error-msg" id="errorHoraFin">
            <i class="fas fa-exclamation-circle"></i> Hora inválida
          </div>
        </div>
      </div>

      <!-- Mensaje de error de horas -->
      <div class="error-msg" id="errorHoras" style="margin-top:-12px; margin-bottom:16px;">
        <i class="fas fa-exclamation-circle"></i> <span id="errorHorasTexto">La hora de salida debe ser mayor a la de entrada</span>
      </div>

      <div class="form-group" id="groupMotivo">
        <label><i class="fas fa-comment-alt"></i> Motivo <span class="opcional">(opcional)</span></label>
        <textarea id="motivoAusencia" placeholder="Describe el motivo de la ausencia..." maxlength="300"></textarea>
        <div style="font-size:11px; color:#999; margin-top:4px; text-align:right;">
          <span id="contadorMotivo">0</span>/300
        </div>
      </div>

      <div class="form-group" id="groupEstado">
        <label><i class="fas fa-clipboard-check"></i> Estado de la ausencia</label>
        <select id="estadoAusencia">
          <option value="Pendiente" selected>Pendiente</option>
          <option value="Justificada">Justificada</option>
          <option value="No justificada">No justificada</option>
        </select>
      </div>

      <div class="modal-actions">
        <button type="button" class="btn-cancelar" onclick="cerrarModal()">Cancelar</button>
        <button type="submit" class="btn-guardar" id="btnGuardar">
          <i class="fas fa-save"></i> Guardar ausencia
        </button>
      </div>
    </form>
  </div>
</div>

<!-- ====== TOAST ====== -->
<div class="toast" id="toast">
  <i class="fas fa-check-circle"></i>
  <span id="toastMsg">Ausencia registrada correctamente</span>
</div>

<script>
  // ====== DATOS ======
  const EMPLEADOS = {
    2: 'Otto Mérida',
    3: 'Kevin Solís',
    4: 'María Fernanda López',
    5: 'Lucia Herrera',
    6: 'Carlos Gutiérrez'
  };

  // Horarios asignados (para validar que el empleado trabaja ese día)
  const HORARIOS = {
    2: { Lunes: ['08:00', '17:00'], Martes: ['08:00', '17:00'], Domingo: ['10:00', '14:00'] },
    3: { Miércoles: ['10:00', '19:00'], Sabado: ['09:00', '15:00'] },
    4: { Jueves: ['09:00', '18:00'] },
    5: { Viernes: ['11:00', '20:00'] },
    6: {} // Carlos no tiene horario
  };

  const TIPOS_AUSENCIA = ['Enfermedad', 'Personal', 'Vacaciones', 'Permiso', 'Otro'];

  let ausencias = [
    { id: 1, id_usuario: 3, nombreEmpleado: 'Kevin Solís', fecha: '2026-08-10', tipo: 'Enfermedad', motivo: 'Gripe fuerte, reposo médico', estado: 'Justificada', horaInicio: '', horaFin: '' },
    { id: 2, id_usuario: 2, nombreEmpleado: 'Otto Mérida', fecha: '2026-08-12', tipo: 'Personal', motivo: 'Trámite personal en el banco', estado: 'Pendiente', horaInicio: '08:00', horaFin: '12:00' },
    { id: 3, id_usuario: 5, nombreEmpleado: 'Lucia Herrera', fecha: '2026-08-05', tipo: 'Permiso', motivo: 'Cita médica', estado: 'Justificada', horaInicio: '', horaFin: '' },
    { id: 4, id_usuario: 4, nombreEmpleado: 'María Fernanda López', fecha: '2026-08-15', tipo: 'Vacaciones', motivo: 'Día de descanso programado', estado: 'Justificada', horaInicio: '', horaFin: '' },
    { id: 5, id_usuario: 3, nombreEmpleado: 'Kevin Solís', fecha: '2026-08-20', tipo: 'Otro', motivo: '', estado: 'No justificada', horaInicio: '', horaFin: '' },
  ];

  let editandoId = null;

  // ====== UTILIDADES ======
  function getDiaSemana(fechaStr) {
    if (!fechaStr) return '';
    const fecha = new Date(fechaStr + 'T00:00:00');
    const dias = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sabado'];
    return dias[fecha.getDay()];
  }

  function getHorarioEmpleado(idUsuario, dia) {
    return HORARIOS[idUsuario]?.[dia] || null;
  }

  function horaAMinutos(hora) {
    if (!hora) return 0;
    const [h, m] = hora.split(':').map(Number);
    return h * 60 + m;
  }

  function formatearFecha(fechaStr) {
    if (!fechaStr) return '—';
    const fecha = new Date(fechaStr + 'T00:00:00');
    const dia = String(fecha.getDate()).padStart(2, '0');
    const mes = String(fecha.getMonth() + 1).padStart(2, '0');
    const anio = fecha.getFullYear();
    return `${dia}/${mes}/${anio}`;
  }

  function getHoy() {
    const hoy = new Date();
    const anio = hoy.getFullYear();
    const mes = String(hoy.getMonth() + 1).padStart(2, '0');
    const dia = String(hoy.getDate()).padStart(2, '0');
    return `${anio}-${mes}-${dia}`;
  }

  // ====== RENDER TABLA ======
  function renderTabla(lista) {
    const tbody = document.getElementById('tablaAusencias');
    const data = lista || ausencias;

    if (data.length === 0) {
      tbody.innerHTML = `<tr><td colspan="6" style="text-align:center; padding:40px; color:#aaa;">No hay ausencias registradas</td></tr>`;
      actualizarStats([]);
      return;
    }

    let html = '';
    data.forEach(a => {
      const iniciales = a.nombreEmpleado.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
      const tipoClass = a.tipo.toLowerCase();
      const estadoClass = a.estado.toLowerCase().replace(' ', '-');

      const iconoTipo = a.tipo === 'Enfermedad' ? 'fa-notes-medical' :
                       a.tipo === 'Personal' ? 'fa-user' :
                       a.tipo === 'Vacaciones' ? 'fa-umbrella-beach' :
                       a.tipo === 'Permiso' ? 'fa-clock' : 'fa-tag';

      const iconoEstado = a.estado === 'Justificada' ? 'fa-check-circle' :
                          a.estado === 'Pendiente' ? 'fa-hourglass-half' : 'fa-times-circle';

      html += `<tr>
        <td>
          <div class="empleado-info">
            <div class="avatar-sm">${iniciales}</div>
            <div class="info">
              <span class="nombre">${a.id_usuario} — ${a.nombreEmpleado}</span>
            </div>
          </div>
        </td>
        <td>${formatearFecha(a.fecha)}</td>
        <td><span class="badge-tipo ${tipoClass}"><i class="fas ${iconoTipo}"></i> ${a.tipo}</span></td>
        <td style="max-width:220px; color:#555; font-size:13px;">${a.motivo || '—'}</td>
        <td>
          <span class="badge-estado ${estadoClass}">
            <i class="fas ${iconoEstado}" style="font-size:10px;"></i> ${a.estado}
          </span>
        </td>
        <td>
          ${a.estado === 'Pendiente' ? 
            `<button class="btn-accion justificar" onclick="justificarAusencia(${a.id})" title="Marcar como justificada">
              <i class="fas fa-check"></i> Justificar
            </button>` : ''
          }
          <button class="btn-accion editar" onclick="editarAusencia(${a.id})" style="margin-left:6px;">
            Editar
          </button>
          <button class="btn-accion eliminar" onclick="eliminarAusencia(${a.id})" style="margin-left:6px;">
            Eliminar
          </button>
        </td>
      </tr>`;
    });

    tbody.innerHTML = html;
    actualizarStats(data);
  }

  // ====== ACTUALIZAR STATS ======
  function actualizarStats(lista) {
    const data = lista || ausencias;
    document.getElementById('statTotal').textContent = data.length;
    document.getElementById('statPendientes').textContent = data.filter(a => a.estado === 'Pendiente').length;
    document.getElementById('statJustificadas').textContent = data.filter(a => a.estado === 'Justificada').length;

    // Este mes
    const hoy = new Date();
    const mesActual = hoy.getMonth();
    const anioActual = hoy.getFullYear();
    const esteMes = data.filter(a => {
      const f = new Date(a.fecha + 'T00:00:00');
      return f.getMonth() === mesActual && f.getFullYear() === anioActual;
    }).length;
    document.getElementById('statEsteMes').textContent = esteMes;
  }

  // ====== FILTRAR ======
  function filtrarAusencias() {
    const query = document.getElementById('buscarAusencia').value.toLowerCase().trim();
    const estado = document.getElementById('filtroEstado').value;

    let filtrados = ausencias;

    if (query) {
      filtrados = filtrados.filter(a =>
        a.nombreEmpleado.toLowerCase().includes(query) ||
        a.tipo.toLowerCase().includes(query) ||
        a.motivo.toLowerCase().includes(query) ||
        String(a.id_usuario).includes(query)
      );
    }

    if (estado) {
      filtrados = filtrados.filter(a => a.estado === estado);
    }

    renderTabla(filtrados);
  }

  // ====== MODAL ======
  function abrirModal(id) {
    const modal = document.getElementById('modalAusencia');
    const form = document.getElementById('formAusencia');
    form.reset();
    limpiarErrores();

    // Resetear contador
    document.getElementById('contadorMotivo').textContent = '0';

    // Resetear alert
    document.getElementById('alertInfo').style.display = 'none';
    document.getElementById('infoHorario').style.display = 'none';

    if (id) {
      // Editar
      const a = ausencias.find(x => x.id === id);
      if (!a) return;
      editandoId = id;
      document.getElementById('modalTitulo').textContent = 'Editar ausencia';
      document.getElementById('empleadoSelect').value = a.id_usuario;
      document.getElementById('fechaAusencia').value = a.fecha;
      document.getElementById('tipoAusencia').value = a.tipo;
      document.getElementById('horaInicio').value = a.horaInicio || '';
      document.getElementById('horaFin').value = a.horaFin || '';
      document.getElementById('motivoAusencia').value = a.motivo || '';
      document.getElementById('estadoAusencia').value = a.estado;
      document.getElementById('contadorMotivo').textContent = (a.motivo || '').length;

      // Cargar info del horario
      onEmpleadoChange();
    } else {
      // Nuevo
      editandoId = null;
      document.getElementById('modalTitulo').textContent = 'Registrar ausencia';
      document.getElementById('estadoAusencia').value = 'Pendiente';
      // Poner fecha de hoy por defecto
      document.getElementById('fechaAusencia').value = getHoy();
    }

    modal.classList.add('active');
    document.body.style.overflow = 'hidden';
  }

  function cerrarModal() {
    document.getElementById('modalAusencia').classList.remove('active');
    document.body.style.overflow = '';
    editandoId = null;
    limpiarErrores();
  }

  // ====== EVENTOS ======
  function onEmpleadoChange() {
    const idUsuario = parseInt(document.getElementById('empleadoSelect').value);
    document.getElementById('groupEmpleado').classList.remove('error');
    document.getElementById('errorEmpleado').classList.remove('show');

    const infoHorario = document.getElementById('infoHorario');

    if (!idUsuario) {
      infoHorario.style.display = 'none';
      return;
    }

    // Mostrar horarios del empleado
    const horariosEmpleado = HORARIOS[idUsuario] || {};
    const diasConHorario = Object.keys(horariosEmpleado);

    if (diasConHorario.length === 0) {
      infoHorario.className = 'info-horario sin-horario';
      infoHorario.innerHTML = `<i class="fas fa-exclamation-triangle"></i> <span>Este empleado <strong>no tiene horario asignado</strong>. Puedes registrar la ausencia igualmente.</span>`;
      infoHorario.style.display = 'flex';
    } else {
      infoHorario.className = 'info-horario';
      const detalles = diasConHorario.map(d => `${d}: ${horariosEmpleado[d][0]}–${horariosEmpleado[d][1]}`).join(' · ');
      infoHorario.innerHTML = `<i class="fas fa-clock"></i> <span><strong>Horario:</strong> ${detalles}</span>`;
      infoHorario.style.display = 'flex';
    }

    // Re-validar fecha si ya hay una seleccionada
    onFechaChange();
  }

  function onFechaChange() {
    const idUsuario = parseInt(document.getElementById('empleadoSelect').value);
    const fecha = document.getElementById('fechaAusencia').value;
    const alertInfo = document.getElementById('alertInfo');

    document.getElementById('groupFecha').classList.remove('error');
    document.getElementById('errorFecha').classList.remove('show');

    if (!idUsuario || !fecha) {
      alertInfo.style.display = 'none';
      return;
    }

    const diaSemana = getDiaSemana(fecha);
    const horario = getHorarioEmpleado(idUsuario, diaSemana);

    // Verificar si ya existe una ausencia ese día
    const ausenciaExistente = ausencias.find(a => 
      a.id_usuario === idUsuario && a.fecha === fecha && a.id !== editandoId
    );

    if (ausenciaExistente) {
      alertInfo.style.display = 'flex';
      alertInfo.className = 'alert-warning error';
      document.getElementById('alertInfoTitulo').textContent = 'Ausencia duplicada';
      document.getElementById('alertInfoTexto').textContent = `Ya existe una ausencia registrada para este empleado el ${formatearFecha(fecha)} (${ausenciaExistente.tipo}).`;
      return;
    }

    if (horario) {
      // El empleado SÍ trabaja ese día
      alertInfo.style.display = 'flex';
      alertInfo.className = 'alert-warning';
      document.getElementById('alertInfoTitulo').textContent = `Ausencia en día laboral (${diaSemana})`;
      document.getElementById('alertInfoTexto').textContent = `El empleado tiene horario asignado de ${horario[0]} a ${horario[1]}. Esta ausencia afectará su jornada.`;
    } else {
      // El empleado NO trabaja ese día
      alertInfo.style.display = 'flex';
      alertInfo.className = 'alert-warning';
      document.getElementById('alertInfoTitulo').textContent = `Día no laboral (${diaSemana})`;
      document.getElementById('alertInfoTexto').textContent = `El empleado no tiene horario asignado para este día. ¿Seguro que quieres registrar la ausencia?`;
    }
  }

  function validarHoras() {
    const horaInicio = document.getElementById('horaInicio').value;
    const horaFin = document.getElementById('horaFin').value;
    const errorHoras = document.getElementById('errorHoras');

    if (horaInicio && horaFin) {
      if (horaAMinutos(horaFin) <= horaAMinutos(horaInicio)) {
        errorHoras.classList.add('show');
        document.getElementById('errorHorasTexto').textContent = 'La hora de salida debe ser mayor a la de entrada';
        document.getElementById('groupHoraFin').classList.add('error');
        return false;
      } else {
        errorHoras.classList.remove('show');
        document.getElementById('groupHoraFin').classList.remove('error');
      }
    } else {
      errorHoras.classList.remove('show');
    }
    return true;
  }

  function limpiarErrores() {
    document.querySelectorAll('.form-group').forEach(g => g.classList.remove('error'));
    document.querySelectorAll('.error-msg').forEach(e => e.classList.remove('show'));
    document.getElementById('errorHoras').classList.remove('show');
  }

  // ====== VALIDACIÓN COMPLETA ======
  function validarFormulario() {
    let valido = true;
    limpiarErrores();

    const idUsuario = parseInt(document.getElementById('empleadoSelect').value);
    const fecha = document.getElementById('fechaAusencia').value;
    const tipo = document.getElementById('tipoAusencia').value;
    const horaInicio = document.getElementById('horaInicio').value;
    const horaFin = document.getElementById('horaFin').value;

    // 1. Validar empleado
    if (!idUsuario) {
      document.getElementById('groupEmpleado').classList.add('error');
      document.getElementById('errorEmpleado').classList.add('show');
      valido = false;
    }

    // 2. Validar fecha
    if (!fecha) {
      document.getElementById('groupFecha').classList.add('error');
      document.getElementById('errorFecha').classList.add('show');
      valido = false;
    }

    // 3. Validar tipo
    if (!tipo) {
      document.getElementById('groupTipo').classList.add('error');
      document.getElementById('errorTipo').classList.add('show');
      valido = false;
    }

    // 4. Validar duplicado
    if (idUsuario && fecha) {
      const duplicado = ausencias.find(a => 
        a.id_usuario === idUsuario && a.fecha === fecha && a.id !== editandoId
      );
      if (duplicado) {
        document.getElementById('groupFecha').classList.add('error');
        document.getElementById('errorFecha').innerHTML = '<i class="fas fa-exclamation-circle"></i> Ya existe una ausencia para este empleado en esta fecha';
        document.getElementById('errorFecha').classList.add('show');
        valido = false;
      }
    }

    // 5. Validar horas
    if (horaInicio && horaFin) {
      if (horaAMinutos(horaFin) <= horaAMinutos(horaInicio)) {
        document.getElementById('errorHoras').classList.add('show');
        document.getElementById('groupHoraFin').classList.add('error');
        valido = false;
      }
    }

    // 6. Validar que las horas estén dentro del horario del empleado
    if (idUsuario && fecha && horaInicio && horaFin) {
      const diaSemana = getDiaSemana(fecha);
      const horario = getHorarioEmpleado(idUsuario, diaSemana);
      if (horario) {
        const hInicioHorario = horaAMinutos(horario[0]);
        const hFinHorario = horaAMinutos(horario[1]);
        const hInicioAus = horaAMinutos(horaInicio);
        const hFinAus = horaAMinutos(horaFin);

        if (hInicioAus < hInicioHorario || hFinAus > hFinHorario) {
          document.getElementById('errorHoras').classList.add('show');
          document.getElementById('errorHorasTexto').textContent = `Las horas deben estar dentro del horario laboral (${horario[0]} a ${horario[1]})`;
          document.getElementById('groupHoraInicio').classList.add('error');
          document.getElementById('groupHoraFin').classList.add('error');
          valido = false;
        }
      }
    }

    return valido;
  }

  // ====== GUARDAR ======
  function guardarAusencia(e) {
    e.preventDefault();

    if (!validarFormulario()) {
      mostrarToast('❌ Corrige los errores del formulario', true);
      return;
    }

    const id_usuario = parseInt(document.getElementById('empleadoSelect').value);
    const fecha = document.getElementById('fechaAusencia').value;
    const tipo = document.getElementById('tipoAusencia').value;
    const horaInicio = document.getElementById('horaInicio').value;
    const horaFin = document.getElementById('horaFin').value;
    const motivo = document.getElementById('motivoAusencia').value.trim();
    const estado = document.getElementById('estadoAusencia').value;
    const nombreEmpleado = EMPLEADOS[id_usuario] || 'Empleado';

    if (editandoId) {
      const idx = ausencias.findIndex(x => x.id === editandoId);
      if (idx !== -1) {
        ausencias[idx] = {
          ...ausencias[idx],
          id_usuario,
          nombreEmpleado,
          fecha,
          tipo,
          motivo,
          estado,
          horaInicio,
          horaFin
        };
      }
      mostrarToast('✅ Ausencia actualizada correctamente');
    } else {
      const nuevoId = Math.max(...ausencias.map(a => a.id), 0) + 1;
      ausencias.push({
        id: nuevoId,
        id_usuario,
        nombreEmpleado,
        fecha,
        tipo,
        motivo,
        estado,
        horaInicio,
        horaFin
      });
      mostrarToast('✅ Ausencia registrada correctamente');
    }

    cerrarModal();
    renderTabla(ausencias);
  }

  // ====== JUSTIFICAR ======
  function justificarAusencia(id) {
    const a = ausencias.find(x => x.id === id);
    if (!a) return;

    if (confirm(`¿Marcar la ausencia de ${a.nombreEmpleado} del ${formatearFecha(a.fecha)} como JUSTIFICADA?`)) {
      a.estado = 'Justificada';
      renderTabla(ausencias);
      mostrarToast('✅ Ausencia justificada correctamente');
    }
  }

  // ====== ELIMINAR ======
  function eliminarAusencia(id) {
    const a = ausencias.find(x => x.id === id);
    if (!a) return;

    if (confirm(`¿Eliminar la ausencia de ${a.nombreEmpleado} del ${formatearFecha(a.fecha)}?`)) {
      ausencias = ausencias.filter(x => x.id !== id);
      renderTabla(ausencias);
      mostrarToast('🗑️ Ausencia eliminada correctamente');
    }
  }

  function editarAusencia(id) {
    abrirModal(id);
  }

  // ====== CONTADOR DE CARACTERES ======
  document.getElementById('motivoAusencia').addEventListener('input', function() {
    document.getElementById('contadorMotivo').textContent = this.value.length;
  });

  // ====== TOAST ======
  function mostrarToast(msg, esError = false) {
    const toast = document.getElementById('toast');
    const msgEl = document.getElementById('toastMsg');
    msgEl.textContent = msg;
    toast.classList.toggle('error', esError);
    toast.classList.add('show');
    clearTimeout(toast._timeout);
    toast._timeout = setTimeout(() => {
      toast.classList.remove('show');
    }, 3500);
  }

  // ====== EVENTOS ======
  document.getElementById('modalAusencia').addEventListener('click', function(e) {
    if (e.target === this) cerrarModal();
  });

  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') cerrarModal();
  });

  // ====== INICIALIZAR ======
  renderTabla(ausencias);
</script>

</body>
</html>