

  // ====== CATÁLOGO CON CÓDIGOS Y LOTES PEPS ======
  const catalogo = [
    { id: 1, codigo: 'P001', nombre: 'Shampoo Keratina', marca: 'Europa Pro', precio: 180,
      lotes: [
        { lote: 'L-2024-001', stock: 15, fechaIngreso: '2026-06-01' },
        { lote: 'L-2024-015', stock: 20, fechaIngreso: '2026-07-15' }
      ]
    },
    { id: 2, codigo: 'P002', nombre: 'Shampoo Anticaspa', marca: 'Europa Pro', precio: 160,
      lotes: [
        { lote: 'L-2024-003', stock: 8, fechaIngreso: '2026-06-05' },
        { lote: 'L-2024-018', stock: 25, fechaIngreso: '2026-07-20' }
      ]
    },
    { id: 3, codigo: 'P003', nombre: 'Acondicionador Premium', marca: 'Europa Pro', precio: 200,
      lotes: [
        { lote: 'L-2024-007', stock: 12, fechaIngreso: '2026-06-10' }
      ]
    },
    { id: 4, codigo: 'P004', nombre: 'Mascarilla Capilar', marca: 'Europa Pro', precio: 250,
      lotes: [
        { lote: 'L-2024-009', stock: 5, fechaIngreso: '2026-06-15' },
        { lote: 'L-2024-022', stock: 18, fechaIngreso: '2026-08-01' }
      ]
    },
    { id: 5, codigo: 'P005', nombre: 'Aceite Reparador', marca: 'Europa Pro', precio: 120,
      lotes: [
        { lote: 'L-2024-011', stock: 30, fechaIngreso: '2026-06-20' }
      ]
    },
    { id: 6, codigo: 'P006', nombre: 'Gel Fijador', marca: 'Europa Pro', precio: 90,
      lotes: [
        { lote: 'L-2024-013', stock: 3, fechaIngreso: '2026-06-25' },
        { lote: 'L-2024-025', stock: 22, fechaIngreso: '2026-08-05' }
      ]
    },
    { id: 7, codigo: 'P007', nombre: 'Cera Modeladora', marca: 'Europa Pro', precio: 95,
      lotes: [
        { lote: 'L-2024-014', stock: 14, fechaIngreso: '2026-06-28' }
      ]
    },
    { id: 8, codigo: 'P008', nombre: 'Tinte Permanente', marca: 'ColorPro', precio: 320,
      lotes: [
        { lote: 'L-2024-016', stock: 6, fechaIngreso: '2026-07-01' },
        { lote: 'L-2024-028', stock: 10, fechaIngreso: '2026-08-10' }
      ]
    },
    { id: 9, codigo: 'P009', nombre: 'Spray Protector', marca: 'Europa Pro', precio: 110,
      lotes: [
        { lote: 'L-2024-020', stock: 9, fechaIngreso: '2026-07-25' }
      ]
    },
    { id: 10, codigo: 'P010', nombre: 'Kit de Peinado', marca: 'Europa Pro', precio: 350,
      lotes: [
        { lote: 'L-2024-024', stock: 4, fechaIngreso: '2026-08-03' }
      ]
    }
  ];

  // ====== ESTADO ======
  let tipoFactura = 'CF';
  let carrito = [];
  let documentoActual = 1;

  // ====== HELPERS ======
  function formatQ(n) {
    return 'Q ' + n.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
  }

  function getStockTotal(producto) {
    return producto.lotes.reduce((sum, l) => sum + l.stock, 0);
  }

  function getLotePEPS(producto) {
    const lotesOrdenados = [...producto.lotes].sort((a, b) => 
      new Date(a.fechaIngreso) - new Date(b.fechaIngreso)
    );
    for (const lote of lotesOrdenados) {
      if (lote.stock > 0) return lote;
    }
    return null;
  }

  function buscarPorCodigo(codigo) {
    const codigoUpper = codigo.trim().toUpperCase();
    return catalogo.find(p => p.codigo.toUpperCase() === codigoUpper);
  }

  // ====== FECHA ACTUAL ======
  function setFechaActual() {
    const hoy = new Date();
    const dia = String(hoy.getDate()).padStart(2, '0');
    const mes = String(hoy.getMonth() + 1).padStart(2, '0');
    const anio = hoy.getFullYear();
    document.getElementById('fechaVenta').value = `${dia}/${mes}/${anio}`;
  }

  // ====== TIPO FACTURA ======
  function seleccionarTipo(tipo) {
    tipoFactura = tipo;
    document.querySelectorAll('.toggle-btn').forEach(el => {
      el.classList.toggle('active', el.dataset.tipo === tipo);
    });

    const nombreInput = document.getElementById('nombreCliente');
    const nitInput = document.getElementById('nitCliente');
    const razonInput = document.getElementById('razonSocial');
    const direccionInput = document.getElementById('direccionCliente');
    const reqNombre = document.getElementById('reqNombre');
    const reqNit = document.getElementById('reqNit');

    if (tipo === 'CF') {
      nombreInput.value = 'Consumidor Final';
      nombreInput.disabled = true;
      nitInput.value = '';
      nitInput.disabled = true;
      razonInput.value = '';
      razonInput.disabled = true;
      direccionInput.value = '';
      direccionInput.disabled = true;
      reqNombre.style.display = 'none';
      reqNit.style.display = 'none';
      document.getElementById('groupNombre').classList.remove('error');
      document.getElementById('errorNombre').classList.remove('show');
      document.getElementById('groupNit').classList.remove('error');
      document.getElementById('errorNit').classList.remove('show');
    } else {
      nombreInput.value = '';
      nombreInput.disabled = false;
      nombreInput.placeholder = 'Nombre del cliente';
      nitInput.value = '';
      nitInput.disabled = false;
      razonInput.value = '';
      razonInput.disabled = false;
      direccionInput.value = '';
      direccionInput.disabled = false;
      reqNombre.style.display = 'inline';
      reqNit.style.display = 'inline';
      nombreInput.focus();
    }

    actualizarInfoCliente();
  }

  function actualizarInfoCliente() {
    const infoTipo = document.getElementById('infoTipo');
    const infoNombre = document.getElementById('infoNombre');
    const infoDetalle = document.getElementById('infoDetalle');

    if (tipoFactura === 'CF') {
      infoTipo.textContent = 'Consumidor Final';
      infoNombre.textContent = 'Consumidor Final';
      infoDetalle.textContent = 'Sin datos fiscales';
    } else {
      const nombre = document.getElementById('nombreCliente').value.trim() || 'Sin nombre';
      const nit = document.getElementById('nitCliente').value.trim();
      const razon = document.getElementById('razonSocial').value.trim();
      infoTipo.textContent = 'NIT';
      infoNombre.textContent = nombre;
      infoDetalle.textContent = 
        (nit ? 'NIT: ' + nit : '') + (razon ? ' · ' + razon : '');
    }
  }

  ['nombreCliente', 'nitCliente', 'razonSocial'].forEach(id => {
    document.getElementById(id).addEventListener('input', actualizarInfoCliente);
  });

  // ====== CÓDIGO DE PRODUCTO ======
  document.getElementById('codigoProducto').addEventListener('keydown', function(e) {
    if (e.key === 'Enter') {
      e.preventDefault();
      buscarYAgregarPorCodigo();
    }
  });

  function buscarYAgregarPorCodigo() {
    const input = document.getElementById('codigoProducto');
    const codigo = input.value.trim();
    const status = document.getElementById('codigoStatus');

    if (!codigo) return;

    const producto = buscarPorCodigo(codigo);

    if (!producto) {
      status.className = 'codigo-status show error';
      status.innerHTML = `<i class="fas fa-times-circle"></i> Código "${codigo}" no encontrado`;
      input.style.borderColor = '#d94a4a';
      setTimeout(() => {
        status.classList.remove('show');
        input.style.borderColor = '';
      }, 2500);
      mostrarToast(`❌ Producto con código "${codigo}" no encontrado`, true);
      return;
    }

    // Verificar stock
    const stockTotal = getStockTotal(producto);
    if (stockTotal === 0) {
      status.className = 'codigo-status show error';
      status.innerHTML = `<i class="fas fa-times-circle"></i> "${producto.nombre}" sin stock`;
      mostrarToast(`❌ "${producto.nombre}" no tiene stock disponible`, true);
      return;
    }

    // Obtener lote PEPS
    const lotePEPS = getLotePEPS(producto);
    if (!lotePEPS) {
      mostrarToast('❌ No hay lotes disponibles', true);
      return;
    }

    // Verificar si ya está en el carrito (mismo producto + mismo lote)
    const existenteIndex = carrito.findIndex(item => 
      item.productoId === producto.id && item.lote === lotePEPS.lote
    );

    if (existenteIndex !== -1) {
      const existente = carrito[existenteIndex];
      if (existente.cantidad + 1 > lotePEPS.stock) {
        mostrarToast(`❌ Solo hay ${lotePEPS.stock} unidades en el lote ${lotePEPS.lote}`, true);
        return;
      }
      existente.cantidad += 1;
    } else {
      carrito.push({
        productoId: producto.id,
        codigo: producto.codigo,
        nombre: producto.nombre,
        marca: producto.marca,
        precio: producto.precio,
        cantidad: 1,
        lote: lotePEPS.lote,
        fechaIngreso: lotePEPS.fechaIngreso,
        stockMax: lotePEPS.stock
      });
    }

    // Feedback visual
    status.className = 'codigo-status show success';
    status.innerHTML = `<i class="fas fa-check-circle"></i> ${producto.nombre} agregado (Lote ${lotePEPS.lote})`;

    input.value = '';
    input.style.borderColor = '';
    input.focus();

    setTimeout(() => status.classList.remove('show'), 2500);

    renderDetalle();
    mostrarToast(`✅ ${producto.nombre} agregado`);
  }

  // ====== MODAL PRODUCTOS ======
  function abrirModalProductos() {
    document.getElementById('modalProductos').classList.add('active');
    document.body.style.overflow = 'hidden';
    document.getElementById('busquedaProducto').value = '';
    document.getElementById('cantidadProducto').value = 1;
    renderListaProductos(catalogo);
    setTimeout(() => document.getElementById('busquedaProducto').focus(), 200);
  }

  function cerrarModalProductos() {
    document.getElementById('modalProductos').classList.remove('active');
    document.body.style.overflow = '';
    renderDetalle();
  }

  function filtrarProductos() {
    const query = document.getElementById('busquedaProducto').value.toLowerCase().trim();
    if (!query) {
      renderListaProductos(catalogo);
      return;
    }
    const filtrados = catalogo.filter(p =>
      p.nombre.toLowerCase().includes(query) ||
      p.codigo.toLowerCase().includes(query) ||
      p.marca.toLowerCase().includes(query) ||
      p.lotes.some(l => l.lote.toLowerCase().includes(query))
    );
    renderListaProductos(filtrados);
  }

  function renderListaProductos(lista) {
    const container = document.getElementById('listaProductos');
    
    if (lista.length === 0) {
      container.innerHTML = `<div class="sin-resultados"><i class="fas fa-search"></i>No se encontraron productos</div>`;
      return;
    }

    let html = '';
    lista.forEach(p => {
      const stockTotal = getStockTotal(p);
      const lotePEPS = getLotePEPS(p);
      const stockBajo = stockTotal <= 5;
      const sinStock = stockTotal === 0;

      html += `
        <div class="producto-item" data-producto-id="${p.id}">
          <div class="info">
            <span class="nombre">${p.nombre}</span>
            <div class="meta">
              <span class="codigo"><i class="fas fa-barcode"></i> ${p.codigo}</span>
              <span>${p.marca}</span>
              <span class="lote"><i class="fas fa-box"></i> Lote PEPS: ${lotePEPS ? lotePEPS.lote : '—'}</span>
              <span class="stock ${stockBajo ? 'bajo' : 'ok'}">
                <i class="fas fa-cubes"></i> ${stockTotal} disponibles
              </span>
            </div>
          </div>
          <div class="precio">${formatQ(p.precio)}</div>
          <button class="btn-agregar-item" 
                  ${sinStock ? 'disabled' : ''}
                  onclick="agregarProducto(${p.id}, event)">
            ${sinStock ? '<i class="fas fa-ban"></i> Sin stock' : '<i class="fas fa-plus"></i> Agregar'}
          </button>
        </div>
      `;
    });

    container.innerHTML = html;
  }

  // ====== AGREGAR PRODUCTO ======
  function agregarProducto(productoId, event) {
    if (event) event.stopPropagation();

    const producto = catalogo.find(p => p.id === productoId);
    if (!producto) return;

    const cantidad = parseInt(document.getElementById('cantidadProducto').value) || 1;
    const stockTotal = getStockTotal(producto);

    if (cantidad > stockTotal) {
      mostrarToast(`❌ Solo hay ${stockTotal} unidades disponibles`, true);
      return;
    }

    const lotePEPS = getLotePEPS(producto);
    if (!lotePEPS) {
      mostrarToast('❌ No hay lotes disponibles', true);
      return;
    }

    const existenteIndex = carrito.findIndex(item => 
      item.productoId === productoId && item.lote === lotePEPS.lote
    );

    if (existenteIndex !== -1) {
      const existente = carrito[existenteIndex];
      const nuevoTotal = existente.cantidad + cantidad;
      if (nuevoTotal > lotePEPS.stock) {
        mostrarToast(`❌ Solo hay ${lotePEPS.stock} unidades en el lote ${lotePEPS.lote}`, true);
        return;
      }
      existente.cantidad = nuevoTotal;
    } else {
      if (cantidad > lotePEPS.stock) {
        mostrarToast(`❌ Solo hay ${lotePEPS.stock} unidades en el lote ${lotePEPS.lote}`, true);
        return;
      }
      carrito.push({
        productoId: producto.id,
        codigo: producto.codigo,
        nombre: producto.nombre,
        marca: producto.marca,
        precio: producto.precio,
        cantidad: cantidad,
        lote: lotePEPS.lote,
        fechaIngreso: lotePEPS.fechaIngreso,
        stockMax: lotePEPS.stock
      });
    }

    mostrarToast(`✅ ${producto.nombre} agregado (Lote ${lotePEPS.lote})`);
    actualizarContadorItems();
    renderDetalle();
  }

  // ====== RENDER DETALLE ======
  function renderDetalle() {
    const vacio = document.getElementById('detalleVacio');
    const tabla = document.getElementById('detalleTabla');
    const tbody = document.getElementById('cuerpoDetalle');

    if (carrito.length === 0) {
      vacio.style.display = 'block';
      tabla.style.display = 'none';
      actualizarResumen();
      actualizarContadorItems();
      return;
    }

    vacio.style.display = 'none';
    tabla.style.display = 'block';

    let html = '';
    carrito.forEach((item, idx) => {
      const subtotal = item.precio * item.cantidad;
      html += `
        <tr>
          <td>
            <div class="producto-cell">
              <span class="nombre">${item.nombre}</span>
              <div class="meta">
                <span class="codigo"><i class="fas fa-barcode"></i> ${item.codigo}</span>
                <span><i class="fas fa-box"></i> Lote: ${item.lote}</span>
                <span>${item.marca}</span>
              </div>
            </div>
          </td>
          <td class="precio-cell">${formatQ(item.precio)}</td>
          <td>
            <div class="cantidad-control">
              <button onclick="cambiarCantidad(${idx}, -1)" ${item.cantidad <= 1 ? 'disabled' : ''}>
                <i class="fas fa-minus"></i>
              </button>
              <span class="valor">${item.cantidad}</span>
              <button onclick="cambiarCantidad(${idx}, 1)" ${item.cantidad >= item.stockMax ? 'disabled' : ''}>
                <i class="fas fa-plus"></i>
              </button>
            </div>
          </td>
          <td class="subtotal-cell">${formatQ(subtotal)}</td>
          <td>
            <button class="btn-eliminar-item" onclick="eliminarItem(${idx})" title="Eliminar">
              <i class="fas fa-trash-alt"></i>
            </button>
          </td>
        </tr>
      `;
    });

    tbody.innerHTML = html;
    actualizarResumen();
    actualizarContadorItems();
  }

  // ====== CAMBIAR CANTIDAD ======
  function cambiarCantidad(idx, delta) {
    const item = carrito[idx];
    if (!item) return;

    const nuevaCantidad = item.cantidad + delta;

    if (nuevaCantidad < 1) return;
    if (nuevaCantidad > item.stockMax) {
      mostrarToast(`❌ Solo hay ${item.stockMax} unidades en el lote ${item.lote}`, true);
      return;
    }

    item.cantidad = nuevaCantidad;
    renderDetalle();
  }

  // ====== ELIMINAR ======
  function eliminarItem(idx) {
    carrito.splice(idx, 1);
    renderDetalle();
    mostrarToast('🗑️ Producto eliminado');
  }

  // ====== CONTADOR ======
  function actualizarContadorItems() {
    const total = carrito.reduce((sum, item) => sum + item.cantidad, 0);
    document.getElementById('contadorItems').innerHTML = `<strong>${total}</strong> producto${total !== 1 ? 's' : ''} agregado${total !== 1 ? 's' : ''}`;
  }

  // ====== RESUMEN ======
  function actualizarResumen() {
    const subtotal = carrito.reduce((sum, item) => sum + (item.precio * item.cantidad), 0);
    const iva = subtotal * 0.12;
    const total = subtotal + iva;

    document.getElementById('resumenSubtotal').textContent = formatQ(subtotal);
    document.getElementById('resumenIva').textContent = formatQ(iva);
    document.getElementById('resumenTotal').textContent = formatQ(total);

    document.getElementById('btnCobrar').disabled = carrito.length === 0;
  }

  // ====== PROCESAR VENTA ======
  function procesarVenta() {
    if (carrito.length === 0) {
      mostrarToast('❌ No hay productos en la venta', true);
      return;
    }

    // Validaciones si es NIT
    if (tipoFactura === 'NIT') {
      const nit = document.getElementById('nitCliente').value.trim();
      const nombre = document.getElementById('nombreCliente').value.trim();
      
      let valido = true;
      
      if (!nombre) {
        document.getElementById('groupNombre').classList.add('error');
        document.getElementById('errorNombre').classList.add('show');
        valido = false;
      } else {
        document.getElementById('groupNombre').classList.remove('error');
        document.getElementById('errorNombre').classList.remove('show');
      }

      if (!nit) {
        document.getElementById('groupNit').classList.add('error');
        document.getElementById('errorNit').classList.add('show');
        valido = false;
      } else {
        document.getElementById('groupNit').classList.remove('error');
        document.getElementById('errorNit').classList.remove('show');
      }

      if (!valido) {
        mostrarToast('❌ Completa los datos de facturación', true);
        return;
      }
    }

    const subtotal = carrito.reduce((sum, item) => sum + (item.precio * item.cantidad), 0);
    const iva = subtotal * 0.12;
    const total = subtotal + iva;

    // Descontar stock PEPS
    carrito.forEach(item => {
      const producto = catalogo.find(p => p.id === item.productoId);
      if (producto) {
        let restante = item.cantidad;
        const lotesOrdenados = [...producto.lotes].sort((a, b) => 
          new Date(a.fechaIngreso) - new Date(b.fechaIngreso)
        );
        for (const lote of lotesOrdenados) {
          if (restante <= 0) break;
          const descontar = Math.min(lote.stock, restante);
          lote.stock -= descontar;
          restante -= descontar;
        }
      }
    });

    mostrarToast(`✅ Venta FAC-${String(documentoActual).padStart(4, '0')} procesada · Total: ${formatQ(total)}`);
    documentoActual++;

    // Reset
    carrito = [];
    renderDetalle();
    renderListaProductos(catalogo);
    document.getElementById('numDocumento').value = 'FAC-' + String(documentoActual).padStart(4, '0');
    document.getElementById('codigoProducto').value = '';
    document.getElementById('codigoProducto').focus();

    // Reset cliente a CF
    seleccionarTipo('CF');
  }

  // ====== CANCELAR VENTA ======
  function cancelarVenta() {
    if (carrito.length === 0) {
      mostrarToast('ℹ️ No hay venta en curso');
      return;
    }
    if (confirm('¿Cancelar la venta actual? Se perderán todos los productos agregados.')) {
      carrito = [];
      renderDetalle();
      seleccionarTipo('CF');
      document.getElementById('codigoProducto').value = '';
      mostrarToast('🔄 Venta cancelada');
    }
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
    }, 3000);
  }

  // ====== EVENTOS ======
  document.getElementById('modalProductos').addEventListener('click', function(e) {
    if (e.target === this) cerrarModalProductos();
  });

  document.getElementById('busquedaProducto').addEventListener('keydown', function(e) {
    if (e.key === 'Enter') {
      e.preventDefault();
      const primerBtn = document.querySelector('#listaProductos .btn-agregar-item:not([disabled])');
      if (primerBtn) primerBtn.click();
    }
  });

  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
      if (document.getElementById('modalProductos').classList.contains('active')) {
        cerrarModalProductos();
      }
    }
  });

  // ====== INICIALIZAR ======
  setFechaActual();
  renderDetalle();
  actualizarResumen();
  seleccionarTipo('CF');
  document.getElementById('codigoProducto').focus();
