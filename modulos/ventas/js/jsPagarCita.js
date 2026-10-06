 const preciosServicios = {
    'Corte normal': 150,
    'Corte completo': 250,
    'Afeitado clásico': 120,
    'Aislado permanente': 380,
    'Maquillaje & peinado': 320,
    'Planchado': 180,
    'Cepillado (extra)': 80
  };

  const catalogoProductos = [
    { nombre: 'Shampoo Keratina', precio: 180 },
    { nombre: 'Shampoo Anticaspa', precio: 160 },
    { nombre: 'Shampoo Volumen', precio: 170 },
    { nombre: 'Shampoo Nutritivo', precio: 190 },
    { nombre: 'Acondicionador Premium', precio: 200 },
    { nombre: 'Acondicionador Reparador', precio: 185 },
    { nombre: 'Acondicionador Rizos', precio: 195 },
    { nombre: 'Mascarilla Capilar', precio: 250 },
    { nombre: 'Mascarilla Hidratante', precio: 230 },
    { nombre: 'Mascarilla Reparadora', precio: 270 },
    { nombre: 'Aceite Reparador', precio: 120 },
    { nombre: 'Aceite Nutritivo', precio: 130 },
    { nombre: 'Gel Fijador', precio: 90 },
    { nombre: 'Gel Extra Fuerte', precio: 100 },
    { nombre: 'Spray Protector', precio: 110 },
    { nombre: 'Spray Brillo', precio: 115 },
    { nombre: 'Cera Modeladora', precio: 95 },
    { nombre: 'Pomada Capilar', precio: 85 },
    { nombre: 'Tinte Permanente', precio: 320 },
    { nombre: 'Tinte Semi-permanente', precio: 280 },
    { nombre: 'Decolorante', precio: 200 },
    { nombre: 'Kit de Peinado', precio: 350 }
  ];

  let citas = [
    { id: 1024, nombre: 'Andrés Batres', email: 'abatres@correo.com', telefono: '4478 9012', fecha: '16/08/2026', hora: '11:00', barbero: 'Nada', pago: 'FALTA PAGAR', estado: 'CONFIRMADA', servicios: ['Corte normal', 'Afeitado clásico', 'Cepillado (extra)'] },
    { id: 1025, nombre: 'María Fernanda López', email: 'mfernanda@correo.com', telefono: '5512 4478', fecha: '16/08/2026', hora: '09:30', barbero: 'Otto Mérida', pago: 'FALTA PAGAR', estado: 'CONFIRMADA', servicios: ['Corte completo'] },
    { id: 1026, nombre: 'Gabriela Morales', email: 'gmorales@correo.com', telefono: '3390 5521', fecha: '16/08/2026', hora: '14:00', barbero: 'Kevin Solís', pago: 'CANCELADO', estado: 'CONFIRMADA', servicios: ['Aislado permanente'] },
    { id: 1028, nombre: 'Josué Ramírez', email: 'jramirez@correo.com', telefono: '2201 7745', fecha: '17/08/2026', hora: '10:15', barbero: 'Kevin Solís', pago: 'FALTA PAGAR', estado: 'CONFIRMADA', servicios: ['Planchado'] }
  ];

  let carritoProductos = {};
  let citaSeleccionada = null;

  function getCitasParaCobrar() {
    return citas.filter(c => c.estado === 'CONFIRMADA' && c.pago === 'FALTA PAGAR');
  }

  // ---- RENDER TABLA ----
  function renderTablaCaja(lista) {
    const tbody = document.getElementById('tablaCaja');
    const data = lista || getCitasParaCobrar();
    if (data.length === 0) {
      tbody.innerHTML = `<tr><td colspan="8" style="text-align:center; padding:30px; color:#64748b;">No hay citas pendientes de cobro</td></tr>`;
      return;
    }
    let html = '';
    data.forEach(c => {
      const serviciosStr = c.servicios.join(', ');
      html += `<tr>
        <td><div class="cliente-info"><span class="nombre">${c.nombre}</span><span class="email">${c.email}</span></div></td>
        <td>${c.telefono}</td>
        <td>${serviciosStr}</td>
        <td>${c.barbero}</td>
        <td>${c.fecha}</td>
        <td>${c.hora}</td>
        <td><span class="badge-estado lista-cobrar"><i class="fas fa-clock"></i> LISTA PARA COBRAR</span></td>
        <td><button class="btn-cobrar" data-id="${c.id}"><i class="fas fa-cash-register"></i> Cobrar</button></td>
      </tr>`;
    });
    tbody.innerHTML = html;

    document.querySelectorAll('.btn-cobrar').forEach(btn => {
      btn.addEventListener('click', function() {
        const id = parseInt(this.dataset.id);
        abrirPanelCobro(id);
      });
    });
  }

  function filtrarCaja() {
    const query = document.getElementById('searchCaja').value.toLowerCase().trim();
    const base = getCitasParaCobrar();
    if (!query) { renderTablaCaja(base); return; }
    const filtradas = base.filter(c =>
      c.nombre.toLowerCase().includes(query) ||
      c.email.toLowerCase().includes(query) ||
      c.telefono.includes(query)
    );
    renderTablaCaja(filtradas);
  }

  // ---- CALCULAR TOTAL ----
  function calcularTotal(cita, productos) {
    let total = 0;
    cita.servicios.forEach(s => { total += preciosServicios[s] || 0; });
    if (productos) {
      productos.forEach(p => { total += p.precio * p.cantidad; });
    }
    return total;
  }

  // ---- RENDER PANEL ITEMS CON CONTROLES DE CANTIDAD ----
  function renderPanelItems(cita) {
    const container = document.getElementById('panelItems');
    const productos = carritoProductos[cita.id] || [];

    let html = '';

    // Servicios
    cita.servicios.forEach(s => {
      const precio = preciosServicios[s] || 0;
      html += `<div class="item">
        <i class="fas fa-cut"></i>
        <span class="nombre">${s}</span>
        <span class="precio">$${precio.toFixed(2)}</span>
      </div>`;
    });

    if (productos.length > 0) {
      html += `<div class="divider"></div>`;
    }

    // Productos agregados con controles de cantidad
    productos.forEach((p, idx) => {
      const subtotal = p.precio * p.cantidad;
      html += `
        <div class="item" data-idx="${idx}">
          <i class="fas fa-shopping-bag" style="color:#b8860b;"></i>
          <span class="nombre">${p.nombre}</span>
          <span class="precio">$${p.precio.toFixed(2)}</span>
          <div class="cantidad-control">
            <button class="btn-cantidad-menos" data-idx="${idx}">−</button>
            <span class="cantidad-valor">${p.cantidad}</span>
            <button class="btn-cantidad-mas" data-idx="${idx}">+</button>
          </div>
          <span class="subtotal">$${subtotal.toFixed(2)}</span>
          <button class="btn-eliminar" data-idx="${idx}"><i class="fas fa-times"></i></button>
        </div>
      `;
    });

    // Botón para abrir modal de productos
    html += `
      <button class="btn-agregar-producto-panel" id="btnAbrirModalProductos">
        <i class="fas fa-plus-circle"></i> Agregar productos
        <span style="font-size:12px; font-weight:400; color:#64748b;">(${productos.length} agregados)</span>
      </button>
    `;

    container.innerHTML = html;

    // Evento para abrir modal
    document.getElementById('btnAbrirModalProductos').addEventListener('click', function() {
      abrirModalProductos(cita);
    });

    // Eventos de cantidad: más
    container.querySelectorAll('.btn-cantidad-mas').forEach(btn => {
      btn.addEventListener('click', function() {
        const idx = parseInt(this.dataset.idx);
        if (carritoProductos[cita.id] && carritoProductos[cita.id][idx]) {
          carritoProductos[cita.id][idx].cantidad += 1;
          actualizarPanel(cita);
        }
      });
    });

    // Eventos de cantidad: menos
    container.querySelectorAll('.btn-cantidad-menos').forEach(btn => {
      btn.addEventListener('click', function() {
        const idx = parseInt(this.dataset.idx);
        if (carritoProductos[cita.id] && carritoProductos[cita.id][idx]) {
          if (carritoProductos[cita.id][idx].cantidad > 1) {
            carritoProductos[cita.id][idx].cantidad -= 1;
          } else {
            // Si llega a 1 y el usuario le da a menos, eliminamos el producto
            carritoProductos[cita.id].splice(idx, 1);
            if (carritoProductos[cita.id].length === 0) delete carritoProductos[cita.id];
          }
          actualizarPanel(cita);
        }
      });
    });

    // Eventos eliminar producto
    container.querySelectorAll('.btn-eliminar').forEach(btn => {
      btn.addEventListener('click', function() {
        const idx = parseInt(this.dataset.idx);
        if (carritoProductos[cita.id]) {
          carritoProductos[cita.id].splice(idx, 1);
          if (carritoProductos[cita.id].length === 0) delete carritoProductos[cita.id];
        }
        actualizarPanel(cita);
      });
    });

    // Actualizar badge de productos
    const badgeContainer = document.getElementById('panelBadgeProductos');
    if (productos.length > 0) {
      const totalItems = productos.reduce((sum, p) => sum + p.cantidad, 0);
      badgeContainer.innerHTML = `<span class="badge-productos"><i class="fas fa-shopping-bag"></i> ${totalItems} producto(s) agregado(s)</span>`;
    } else {
      badgeContainer.innerHTML = '';
    }

    // Actualizar total
    const total = calcularTotal(cita, carritoProductos[cita.id] || []);
    document.getElementById('panelTotal').textContent = total.toFixed(2);
  }

  function actualizarPanel(cita) {
    renderPanelItems(cita);
    const total = calcularTotal(cita, carritoProductos[cita.id] || []);
    document.getElementById('panelTotal').textContent = total.toFixed(2);
  }

  // ---- MODAL DE PRODUCTOS ----
  let modalCitaActual = null;

  function abrirModalProductos(cita) {
    modalCitaActual = cita;
    document.getElementById('modalProductos').classList.add('active');
    document.body.style.overflow = 'hidden';
    document.getElementById('modalBusquedaProducto').value = '';
    renderModalLista('');
  }

  function cerrarModalProductos() {
    document.getElementById('modalProductos').classList.remove('active');
    document.body.style.overflow = '';
    if (modalCitaActual) {
      actualizarPanel(modalCitaActual);
      modalCitaActual = null;
    }
  }

  function renderModalLista(filtro) {
    const container = document.getElementById('modalListaProductos');
    const query = filtro.toLowerCase().trim();
    let productos = catalogoProductos;

    if (query) {
      productos = productos.filter(p => p.nombre.toLowerCase().includes(query));
    }

    if (productos.length === 0) {
      container.innerHTML = `<div class="sin-resultados"><i class="fas fa-search" style="font-size:24px; display:block; margin-bottom:12px;"></i>No se encontraron productos</div>`;
      return;
    }

    let html = '';
    productos.forEach(p => {
      // Verificar si ya está en el carrito
      const carrito = carritoProductos[modalCitaActual?.id] || [];
      const existente = carrito.find(item => item.nombre === p.nombre);
      const agregado = existente ? true : false;

      html += `
        <div class="producto-item" data-nombre="${p.nombre}" data-precio="${p.precio}">
          <div class="info">
            <span class="nombre">${p.nombre}</span>
            <span class="categoria">Producto capilar</span>
          </div>
          <div style="display:flex; align-items:center; gap:8px;">
            <span class="precio">$${p.precio.toFixed(2)}</span>
            <button class="btn-agregar-item ${agregado ? 'agregado' : ''}" 
                    data-nombre="${p.nombre}" 
                    data-precio="${p.precio}"
                    ${agregado ? 'disabled' : ''}>
              ${agregado ? '<i class="fas fa-check"></i> Agregado' : '<i class="fas fa-plus"></i> Agregar'}
            </button>
          </div>
        </div>
      `;
    });

    container.innerHTML = html;

    // Eventos de los botones "Agregar"
    container.querySelectorAll('.btn-agregar-item:not([disabled])').forEach(btn => {
      btn.addEventListener('click', function(e) {
        e.stopPropagation();
        const nombre = this.dataset.nombre;
        const precio = parseFloat(this.dataset.precio);
        const cantidad = parseInt(document.getElementById('modalCantidadProducto').value) || 1;

        if (!modalCitaActual) return;

        if (!carritoProductos[modalCitaActual.id]) {
          carritoProductos[modalCitaActual.id] = [];
        }

        const existente = carritoProductos[modalCitaActual.id].find(p => p.nombre === nombre);
        if (existente) {
          existente.cantidad += cantidad;
        } else {
          carritoProductos[modalCitaActual.id].push({ nombre, precio, cantidad });
        }

        // Actualizar el modal (refrescar lista para mostrar el estado "Agregado")
        const filtroActual = document.getElementById('modalBusquedaProducto').value;
        renderModalLista(filtroActual);

        // Actualizar el panel en segundo plano
        if (modalCitaActual) {
          const total = calcularTotal(modalCitaActual, carritoProductos[modalCitaActual.id] || []);
          document.getElementById('panelTotal').textContent = total.toFixed(2);
          const productos = carritoProductos[modalCitaActual.id] || [];
          const badgeContainer = document.getElementById('panelBadgeProductos');
          if (productos.length > 0) {
            const totalItems = productos.reduce((sum, p) => sum + p.cantidad, 0);
            badgeContainer.innerHTML = `<span class="badge-productos"><i class="fas fa-shopping-bag"></i> ${totalItems} producto(s) agregado(s)</span>`;
          } else {
            badgeContainer.innerHTML = '';
          }
        }
      });
    });

    // Click en el item para agregar (también)
    container.querySelectorAll('.producto-item').forEach(item => {
      item.addEventListener('click', function() {
        const btn = this.querySelector('.btn-agregar-item:not([disabled])');
        if (btn) btn.click();
      });
    });
  }

  // ---- ABRIR PANEL DE COBRO ----
  function abrirPanelCobro(id) {
    const cita = citas.find(c => c.id === id);
    if (!cita) return;
    citaSeleccionada = cita;

    if (!carritoProductos[cita.id]) carritoProductos[cita.id] = [];

    document.getElementById('panelCitaId').textContent = '#' + cita.id;
    document.getElementById('panelCliente').textContent = cita.nombre;
    document.getElementById('panelTelefono').textContent = cita.telefono;
    document.getElementById('panelFecha').textContent = cita.fecha;
    document.getElementById('panelHora').textContent = cita.hora;
    document.getElementById('panelBarbero').textContent = cita.barbero;

    renderPanelItems(cita);

    document.getElementById('panelCobro').classList.add('active');
    document.getElementById('panelCobro').scrollIntoView({ behavior: 'smooth', block: 'start' });

    const fb = document.getElementById('panelFeedback');
    fb.className = 'panel-feedback';
    fb.textContent = '';

    const btn = document.getElementById('btnCobrarAhora');
    btn.className = 'btn-cobrar-ahora';
    btn.innerHTML = '<i class="fas fa-check-circle"></i> Cobrar ahora';
  }

  function cerrarPanelCobro() {
    document.getElementById('panelCobro').classList.remove('active');
    if (citaSeleccionada) {
      delete carritoProductos[citaSeleccionada.id];
    }
    citaSeleccionada = null;
  }

  // ---- COBRAR ----
  document.getElementById('btnCobrarAhora').addEventListener('click', function() {
    if (!citaSeleccionada) return;
    const fb = document.getElementById('panelFeedback');
    const productos = carritoProductos[citaSeleccionada.id] || [];
    const total = calcularTotal(citaSeleccionada, productos);

    citaSeleccionada.pago = 'PAGADO';
    const idx = citas.findIndex(c => c.id === citaSeleccionada.id);
    if (idx !== -1) citas[idx] = citaSeleccionada;

    delete carritoProductos[citaSeleccionada.id];

    let detalle = `Servicios: ${citaSeleccionada.servicios.join(', ')}`;
    if (productos.length > 0) {
      detalle += ` | Productos: ${productos.map(p => `${p.nombre} ×${p.cantidad}`).join(', ')}`;
    }
    fb.className = 'panel-feedback show success';
    fb.innerHTML = `<i class="fas fa-check-circle"></i> Pago realizado correctamente. Total: $${total.toFixed(2)} MXN<br><small>${detalle}</small>`;

    this.className = 'btn-cobrar-ahora success';
    this.innerHTML = '<i class="fas fa-check"></i> Cobrado';

    renderTablaCaja(getCitasParaCobrar());
  });

  document.getElementById('btnCancelarCobro').addEventListener('click', function() {
    cerrarPanelCobro();
  });

  // ---- EVENTOS MODAL ----
  document.getElementById('btnCerrarModal').addEventListener('click', cerrarModalProductos);
  document.getElementById('btnCerrarModalProductos').addEventListener('click', cerrarModalProductos);
  document.getElementById('modalProductos').addEventListener('click', function(e) {
    if (e.target === this) cerrarModalProductos();
  });

  document.getElementById('modalBusquedaProducto').addEventListener('input', function() {
    renderModalLista(this.value);
  });

  document.getElementById('modalBusquedaProducto').addEventListener('keydown', function(e) {
    if (e.key === 'Enter') {
      e.preventDefault();
      const primerBoton = document.querySelector('#modalListaProductos .btn-agregar-item:not([disabled])');
      if (primerBoton) primerBoton.click();
    }
  });

  // ---- INICIALIZAR ----
  renderTablaCaja(getCitasParaCobrar());

  window.filtrarCaja = filtrarCaja;
  window.cerrarPanelCobro = cerrarPanelCobro;
  window.abrirPanelCobro = abrirPanelCobro;
