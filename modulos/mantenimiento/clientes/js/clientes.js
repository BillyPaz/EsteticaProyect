(function () {
  'use strict';

  // Si el script se cargara dos veces, no duplicamos los listeners
  if (window.__clientesJsCargado) return;
  window.__clientesJsCargado = true;

  // =====================================================
  // URL base del módulo
  // =====================================================
  const RUTA_MODULO = '/Peluqueria/modulos/mantenimiento/clientes';

  // =====================================================
  // HELPERS
  // =====================================================

  // Envía un FormData y siempre devuelve un objeto { success, message, ... }
  async function enviar(url, formData) {
    try {
      const resp = await fetch(url, {
        method: 'POST',
        body: formData,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      });

      try {
        return await resp.json();
      } catch (_) {
        return {
          success: false,
          message: 'El servidor respondió de forma inesperada. Intenta de nuevo.'
        };
      }
    } catch (_) {
      return {
        success: false,
        conexion: true,
        message: 'No se pudo conectar con el servidor. Intenta de nuevo.'
      };
    }
  }

  // Si la sesión expiró, avisa y recarga (auth.php mandará al login)
  function sesionExpirada(data) {
    if (!data || !data.sesionExpirada) return false;

    Swal.fire({
      icon: 'warning',
      title: 'Sesión expirada',
      text: data.message,
      confirmButtonText: 'Iniciar sesión',
      allowOutsideClick: false
    }).then(() => window.location.reload());

    return true;
  }

  function mostrarCargando(titulo) {
    Swal.fire({
      title: titulo,
      html: 'Por favor espera',
      allowOutsideClick: false,
      allowEscapeKey: false,
      didOpen: () => Swal.showLoading()
    });
  }

  // Recarga el módulo actual (reenvía el clic al botón activo del sidebar)
  function recargarModulo() {
    const botonActivo = document.querySelector('.side-nav button.activo[data-seccion]');
    if (botonActivo) {
      botonActivo.click();
    } else {
      window.location.reload();
    }
  }

  // Minúsculas y sin tildes, para que "jose" encuentre "José"
  function normalizar(texto) {
    return (texto || '')
      .toString()
      .toLowerCase()
      .normalize('NFD')
      .replace(/[\u0300-\u036f]/g, '')
      .trim();
  }

  // Solo dígitos, para validar el teléfono en el navegador
  function soloDigitos(texto) {
    return (texto || '').replace(/\D/g, '');
  }

  // =====================================================
  // 1. VALIDACIÓN EN EL NAVEGADOR (el servidor valida de nuevo)
  //    Devuelve el texto del problema, o '' si todo está bien.
  // =====================================================
  function validar(form) {
    const telefono  = soloDigitos(form.elements['telefono'].value);
    const telefono2 = soloDigitos(form.elements['telefono2'].value);
    const correo    = form.elements['correo'].value.trim();

    if (telefono.length !== 8) {
      return 'El teléfono debe tener exactamente 8 dígitos.';
    }
    if (form.elements['telefono2'].value.trim() !== '' && telefono2.length !== 8) {
      return 'El teléfono 2 debe tener exactamente 8 dígitos, o déjalo vacío.';
    }
    if (correo !== '' && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(correo)) {
      return 'El correo no es válido.';
    }
    return '';
  }

  // Muestra el aviso de teléfono repetido, sin bloquear el flujo
  function avisarTelefonoRepetido(data) {
    if (!data.telefonoRepetido) return;

    Swal.fire({
      icon: 'info',
      title: 'Teléfono ya registrado',
      text: data.avisoTelefono || 'Ese teléfono ya está registrado con otro cliente.'
    });
  }

  // =====================================================
  // 2. SUBMIT: Nuevo cliente
  //    (cuando hagamos "Editar" se agrega su formulario aquí)
  // =====================================================
   const FORMULARIOS = {
    formNuevoCliente: {
      archivo: 'ingresar.php',
      cargando: 'Guardando...',
      titulo: 'Cliente registrado'
    },
    formEditarCliente: {
      archivo: 'editar.php',
      cargando: 'Guardando cambios...',
      titulo: 'Cliente actualizado'
    }
  };

  document.addEventListener('submit', async (e) => {
    const form = e.target;
    const config = FORMULARIOS[form.id];
    if (!config) return;

    e.preventDefault();

    const problema = validar(form);
    if (problema) {
      Swal.fire({
        icon: 'warning',
        title: 'Revisa los datos',
        text: problema
      });
      return;
    }

    mostrarCargando(config.cargando);

    const data = await enviar(`${RUTA_MODULO}/php/${config.archivo}`, new FormData(form));

    if (sesionExpirada(data)) return;

    if (data.success) {
      const modal = form.closest('.modal-panel');
      if (modal) modal.classList.remove('abierto');
      form.reset();

      await Swal.fire({
        icon: 'success',
        title: config.titulo,
        text: data.message,
        timer: 1800,
        showConfirmButton: false
      });

      avisarTelefonoRepetido(data);
      recargarModulo();
      return;
    }

    Swal.fire({
      icon: data.conexion ? 'error' : 'warning',
      title: data.conexion ? 'Error de conexión' : 'No se pudo guardar',
      text: data.message || 'Ocurrió un error al guardar.'
    });
  });

    // =====================================================
  // 3. EDITAR: llenar el modal con los datos de la fila
  //    (el modal se abre por data-modal desde el JS del dashboard;
  //     aquí solo se cargan los valores, sin petición al servidor)
  // =====================================================
  document.addEventListener('click', (e) => {
    const boton = e.target.closest('.btn-accion.editar[data-modal="modalEditarCliente"]');
    if (!boton) return;

    const form = document.getElementById('formEditarCliente');
    if (!form) return;

    form.elements['id_cliente'].value      = boton.dataset.cliente;
    form.elements['nombreCliente'].value   = boton.dataset.nombre;
    form.elements['apellidoCliente'].value = boton.dataset.apellido;
    form.elements['telefono'].value        = boton.dataset.telefono;
    form.elements['telefono2'].value       = boton.dataset.telefono2;
    form.elements['correo'].value          = boton.dataset.correo;
    form.elements['genero'].value          = boton.dataset.genero;
    form.elements['estado'].value          = boton.dataset.estado;
  });

  // =====================================================
  // 4. BUSCADOR de la tabla de clientes
  //    Busca en nombre, apellido, teléfono, correo, género y estado.
  //    No busca en "Fecha registro", "Fecha actualización" ni en los botones.
  //    Acepta varias palabras: "maria activo" exige ambas.
  // =====================================================
  const COLUMNAS_SIN_BUSCAR = ['fecha registro', 'fecha actualizacion'];

  function mostrarSinResultados(tabla, mostrar) {
    let aviso = document.getElementById('sinResultadosCliente');

    if (!aviso) {
      aviso = document.createElement('div');
      aviso.id = 'sinResultadosCliente';
      aviso.className = 'tabla-vacia';
      aviso.innerHTML =
        '<p>No se encontraron clientes.</p>' +
        '<small>Prueba con otro nombre, teléfono o correo.</small>';
      tabla.parentElement.appendChild(aviso);
    }

    aviso.style.display = mostrar ? '' : 'none';
  }

  document.addEventListener('input', (e) => {
    if (e.target.id !== 'buscarCliente') return;

    const tabla = document.getElementById('tablaClientes');
    if (!tabla) return;

    const excluidas = [...tabla.querySelectorAll('thead th')]
      .map((th, i) =>
        th.classList.contains('col-acc') ||
        COLUMNAS_SIN_BUSCAR.includes(normalizar(th.textContent))
          ? i
          : -1
      )
      .filter((i) => i >= 0);

    const terminos = normalizar(e.target.value).split(/\s+/).filter(Boolean);
    let visibles = 0;

    tabla.querySelectorAll('tbody tr').forEach((fila) => {
      const texto = normalizar(
        [...fila.children]
          .filter((_, i) => !excluidas.includes(i))
          .map((celda) => celda.textContent)
          .join(' ')
      );

      const coincide = terminos.every((t) => texto.includes(t));
      fila.style.display = coincide ? '' : 'none';
      if (coincide) visibles++;
    });

    mostrarSinResultados(tabla, terminos.length > 0 && visibles === 0);
  });

})();