
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
