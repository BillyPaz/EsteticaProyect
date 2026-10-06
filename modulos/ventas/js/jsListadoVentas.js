
  // ====== DATOS DE VENTAS REALIZADAS ======
  const ventas = [
    {
      id: 1,
      numero: 'FAC-0001',
      fecha: '2026-08-15',
      hora: '09:45',
      tipo: 'CF',
      metodo: 'Efectivo',
      cajero: 'Roberto Espinoza',
      cliente: { nombre: 'Consumidor Final', nit: '', razon: '', direccion: '' },
      items: [
        { codigo: 'P001', nombre: 'Shampoo Keratina', marca: 'Europa Pro', precio: 180, cantidad: 2, lote: 'L-2024-001' },
        { codigo: 'P005', nombre: 'Aceite Reparador', marca: 'Europa Pro', precio: 120, cantidad: 1, lote: 'L-2024-011' }
      ]
    },
    {
      id: 2,
      numero: 'FAC-0002',
      fecha: '2026-08-15',
      hora: '10:30',
      tipo: 'NIT',
      metodo: 'Tarjeta',
      cajero: 'Roberto Espinoza',
      cliente: { nombre: 'María Fernández', nit: '1234567-8', razon: 'Salón Bella', direccion: '5ta Ave 12-34 Zona 1' },
      items: [
        { codigo: 'P004', nombre: 'Mascarilla Capilar', marca: 'Europa Pro', precio: 250, cantidad: 1, lote: 'L-2024-009' },
        { codigo: 'P008', nombre: 'Tinte Permanente', marca: 'ColorPro', precio: 320, cantidad: 2, lote: 'L-2024-016' }
      ]
    },
    {
      id: 3,
      numero: 'FAC-0003',
      fecha: '2026-08-15',
      hora: '11:15',
      tipo: 'CF',
      metodo: 'Efectivo',
      cajero: 'Roberto Espinoza',
      cliente: { nombre: 'Consumidor Final', nit: '', razon: '', direccion: '' },
      items: [
        { codigo: 'P006', nombre: 'Gel Fijador', marca: 'Europa Pro', precio: 90, cantidad: 3, lote: 'L-2024-013' },
        { codigo: 'P007', nombre: 'Cera Modeladora', marca: 'Europa Pro', precio: 95, cantidad: 2, lote: 'L-2024-014' }
      ]
    },
    {
      id: 4,
      numero: 'FAC-0004',
      fecha: '2026-08-14',
      hora: '16:20',
      tipo: 'NIT',
      metodo: 'Transferencia',
      cajero: 'Roberto Espinoza',
      cliente: { nombre: 'Carlos Méndez', nit: '9876543-2', razon: '', direccion: '' },
      items: [
        { codigo: 'P010', nombre: 'Kit de Peinado', marca: 'Europa Pro', precio: 350, cantidad: 1, lote: 'L-2024-024' },
        { codigo: 'P009', nombre: 'Spray Protector', marca: 'Europa Pro', precio: 110, cantidad: 2, lote: 'L-2024-020' }
      ]
    },
    {
      id: 5,
      numero: 'FAC-0005',
      fecha: '2026-08-14',
      hora: '12:05',
      tipo: 'CF',
      metodo: 'Tarjeta',
      cajero: 'Roberto Espinoza',
      cliente: { nombre: 'Consumidor Final', nit: '', razon: '', direccion: '' },
      items: [
        { codigo: 'P002', nombre: 'Shampoo Anticaspa', marca: 'Europa Pro', precio: 160, cantidad: 1, lote: 'L-2024-003' },
        { codigo: 'P003', nombre: 'Acondicionador Premium', marca: 'Europa Pro', precio: 200, cantidad: 1, lote: 'L-2024-007' }
      ]
    },
    {
      id: 6,
      numero: 'FAC-0006',
      fecha: '2026-08-13',
      hora: '17:40',
      tipo: 'NIT',
      metodo: 'Efectivo',
      cajero: 'Roberto Espinoza',
      cliente: { nombre: 'Ana López', nit: '5551234-9', razon: 'Estética Glamour', direccion: 'Av. Las Américas 45' },
      items: [
        { codigo: 'P001', nombre: 'Shampoo Keratina', marca: 'Europa Pro', precio: 180, cantidad: 3, lote: 'L-2024-001' },
        { codigo: 'P004', nombre: 'Mascarilla Capilar', marca: 'Europa Pro', precio: 250, cantidad: 1, lote: 'L-2024-009' },
        { codigo: 'P005', nombre: 'Aceite Reparador', marca: 'Europa Pro', precio: 120, cantidad: 2, lote: 'L-2024-011' }
      ]
    }
  ];

  let ventaSeleccionada = null;

  // ====== HELPERS ======
  function formatQ(n) {
    return 'Q ' + n.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
  }

  function formatearFecha(fechaStr) {
    if (!fechaStr) return '—';
    const [anio, mes, dia] = fechaStr.split('-');
    return `${dia}/${mes}/${anio}`;
  }

  function getFechaHoy() {
    const hoy = new Date();
    const anio = hoy.getFullYear();
    const mes = String(hoy.getMonth() + 1).padStart(2, '0');
    const dia = String(hoy.getDate()).padStart(2, '0');
    return `${anio}-${mes}-${dia}`;
  }

  function calcularTotales(items) {
    const subtotal = items.reduce((sum, item) => sum + (item.precio * item.cantidad), 0);
    const descuento = 0;
    const base = subtotal - descuento;
    const iva = base * 0.12;
    const total = base + iva;
    return { subtotal, descuento, iva, total };
  }

  // ====== RENDER TABLA ======
  function renderTabla(lista) {
    const tbody = document.getElementById('tablaVentas');
    const data = lista || ventas;

    if (data.length === 0) {
      tbody.innerHTML = `
        <tr>
          <td colspan="7">
            <div class="tabla-vacia">
              <i class="fas fa-receipt"></i>
              <p>No hay ventas registradas</p>
              <small>Las ventas procesadas aparecerán aquí</small>
            </div>
          </td>
        </tr>`;
      actualizarStats([]);
      return;
    }

    let html = '';
    data.forEach(v => {
      const totales = calcularTotales(v.items);
      const totalItems = v.items.reduce((sum, i) => sum + i.cantidad, 0);
      const tipoClass = v.tipo.toLowerCase();
      const metodoClass = v.metodo.toLowerCase();

      const iconoMetodo = v.metodo === 'Efectivo' ? 'fa-money-bill-wave' :
                         v.metodo === 'Tarjeta' ? 'fa-credit-card' : 'fa-exchange-alt';

      html += `
        <tr>
          <td>
            <div class="doc-cell">
              <span class="num">${v.numero}</span>
              <span class="fecha">${formatearFecha(v.fecha)} · ${v.hora}</span>
            </div>
          </td>
          <td>
            <div class="cliente-cell">
              <span class="nombre">${v.cliente.nombre}</span>
              ${v.cliente.nit ? `<span class="nit">NIT: ${v.cliente.nit}</span>` : ''}
            </div>
          </td>
          <td>
            <span class="badge-tipo ${tipoClass}">
              <i class="fas ${v.tipo === 'CF' ? 'fa-user' : 'fa-id-card'}"></i> ${v.tipo}
            </span>
          </td>
          <td>
            <span class="badge-metodo ${metodoClass}">
              <i class="fas ${iconoMetodo}"></i> ${v.metodo}
            </span>
          </td>
          <td>
            <span class="cantidad-badge">${totalItems}</span>
          </td>
          <td class="total-cell">${formatQ(totales.total)}</td>
          <td>
            <button class="btn-ver" onclick="verDetalle(${v.id})">
              <i class="fas fa-eye"></i> Ver detalle
            </button>
          </td>
        </tr>
      `;
    });

    tbody.innerHTML = html;
    actualizarStats(data);
  }

  // ====== STATS ======
  function actualizarStats(lista) {
    const data = lista || ventas;
    const hoy = getFechaHoy();
    
    document.getElementById('statTotalVentas').textContent = data.length;

    const montoTotal = data.reduce((sum, v) => {
      const t = calcularTotales(v.items);
      return sum + t.total;
    }, 0);
    document.getElementById('statMontoTotal').textContent = formatQ(montoTotal);

    const ventasHoy = data.filter(v => v.fecha === hoy).length;
    document.getElementById('statVentasHoy').textContent = ventasHoy;

    const promedio = data.length > 0 ? montoTotal / data.length : 0;
    document.getElementById('statPromedio').textContent = formatQ(promedio);
  }

  // ====== FILTRAR ======
  function filtrarVentas() {
    const query = document.getElementById('buscarVenta').value.toLowerCase().trim();
    const tipo = document.getElementById('filtroTipo').value;
    const metodo = document.getElementById('filtroMetodo').value;

    let filtrados = ventas;

    if (query) {
      filtrados = filtrados.filter(v =>
        v.numero.toLowerCase().includes(query) ||
        v.cliente.nombre.toLowerCase().includes(query) ||
        (v.cliente.nit && v.cliente.nit.toLowerCase().includes(query))
      );
    }

    if (tipo) {
      filtrados = filtrados.filter(v => v.tipo === tipo);
    }

    if (metodo) {
      filtrados = filtrados.filter(v => v.metodo === metodo);
    }

    renderTabla(filtrados);
  }

  // ====== VER DETALLE ======
  function verDetalle(id) {
    const venta = ventas.find(v => v.id === id);
    if (!venta) return;
    ventaSeleccionada = venta;

    const totales = calcularTotales(venta.items);

    // Header del modal
    document.getElementById('modalNumDocumento').textContent = venta.numero;

    // Info general
    document.getElementById('modalCliente').textContent = venta.cliente.nombre;
    let detalleCliente = '';
    if (venta.tipo === 'CF') {
      detalleCliente = 'Sin datos fiscales';
    } else {
      if (venta.cliente.nit) detalleCliente += `NIT: ${venta.cliente.nit}`;
      if (venta.cliente.razon) detalleCliente += (detalleCliente ? ' · ' : '') + venta.cliente.razon;
      if (venta.cliente.direccion) detalleCliente += (detalleCliente ? ' · ' : '') + venta.cliente.direccion;
    }
    document.getElementById('modalClienteDetalle').textContent = detalleCliente;

    document.getElementById('modalFecha').textContent = `${formatearFecha(venta.fecha)} · ${venta.hora}`;
    document.getElementById('modalCajero').textContent = `Cajero: ${venta.cajero}`;
    document.getElementById('modalMetodo').textContent = venta.metodo;
    document.getElementById('modalTipoDoc').textContent = venta.tipo === 'CF' ? 'Consumidor Final (CF)' : 'NIT';

    // Productos
    const tbody = document.getElementById('modalProductosBody');
    let html = '';
    venta.items.forEach(item => {
      const subtotal = item.precio * item.cantidad;
      html += `
        <tr>
          <td>
            <div class="prod-cell">
              <span class="nombre">${item.nombre}</span>
              <div class="meta">
                <span class="codigo"><i class="fas fa-barcode"></i> ${item.codigo}</span>
                <span><i class="fas fa-box"></i> ${item.lote}</span>
                <span>${item.marca}</span>
              </div>
            </div>
          </td>
          <td>${formatQ(item.precio)}</td>
          <td><span class="cantidad-badge">${item.cantidad}</span></td>
          <td style="font-weight:700;">${formatQ(subtotal)}</td>
        </tr>
      `;
    });
    tbody.innerHTML = html;

    // Totales
    document.getElementById('modalSubtotal').textContent = formatQ(totales.subtotal);
    document.getElementById('modalDescuento').textContent = formatQ(totales.descuento);
    document.getElementById('modalIva').textContent = formatQ(totales.iva);
    document.getElementById('modalTotal').textContent = formatQ(totales.total);

    // Fecha de generación
    const ahora = new Date();
    const fechaGen = `${String(ahora.getDate()).padStart(2, '0')}/${String(ahora.getMonth() + 1).padStart(2, '0')}/${ahora.getFullYear()} ${String(ahora.getHours()).padStart(2, '0')}:${String(ahora.getMinutes()).padStart(2, '0')}`;
    document.getElementById('modalFechaGeneracion').textContent = fechaGen;

    // Abrir modal
    document.getElementById('modalDetalle').classList.add('active');
    document.body.style.overflow = 'hidden';
  }

  function cerrarModalDetalle() {
    document.getElementById('modalDetalle').classList.remove('active');
    document.body.style.overflow = '';
    ventaSeleccionada = null;
  }

  // ====== GENERAR PDF (SIMULADO) ======
  function generarPDF() {
    if (!ventaSeleccionada) return;

    const venta = ventaSeleccionada;
    const totales = calcularTotales(venta.items);

    // Construir el contenido del PDF (simulado con HTML)
    const contenido = `
========================================
      PELUQUERÍA Y ESTÉTICA EUROPA
========================================
         COMPROBANTE DE VENTA
========================================

Documento: ${venta.numero}
Fecha: ${formatearFecha(venta.fecha)} ${venta.hora}
Cajero: ${venta.cajero}

----------------------------------------
DATOS DEL CLIENTE
----------------------------------------
Nombre: ${venta.cliente.nombre}
${venta.tipo === 'NIT' ? `NIT: ${venta.cliente.nit}` : 'Tipo: Consumidor Final'}
${venta.cliente.razon ? `Razón social: ${venta.cliente.razon}` : ''}
${venta.cliente.direccion ? `Dirección: ${venta.cliente.direccion}` : ''}

----------------------------------------
PRODUCTOS
----------------------------------------
${venta.items.map(item => 
  `${item.codigo}  ${item.nombre}
   ${item.cantidad} x ${formatQ(item.precio)} = ${formatQ(item.precio * item.cantidad)}
   Lote: ${item.lote}
`).join('\n')}

----------------------------------------
TOTALES
----------------------------------------
Subtotal:      ${formatQ(totales.subtotal)}
Descuento:     ${formatQ(totales.descuento)}
IVA (12%):     ${formatQ(totales.iva)}
----------------------------------------
TOTAL:         ${formatQ(totales.total)}
========================================

Método de pago: ${venta.metodo}

¡Gracias por su compra!
    `;

    console.log('%c📄 PDF GENERADO:', 'color:#b33a3a; font-weight:bold; font-size:14px;');
    console.log(contenido);

    mostrarToast(`📄 PDF del documento ${venta.numero} generado correctamente`);
  }

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
  document.getElementById('modalDetalle').addEventListener('click', function(e) {
    if (e.target === this) cerrarModalDetalle();
  });

  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
      if (document.getElementById('modalDetalle').classList.contains('active')) {
        cerrarModalDetalle();
      }
    }
  });

  // ====== INICIALIZAR ======
  renderTabla(ventas);
