(function () {
  'use strict';

  // Si el script se cargara dos veces, no duplicamos los listeners
  if (window.__comisionesJsCargado) return;
  window.__comisionesJsCargado = true;

  // =====================================================
  // URL base del módulo
  // =====================================================
  const RUTA_MODULO = '/Peluqueria/modulos/empleados/comisiones';

  // Formularios que comparten los campos de porcentaje y fechas
  const IDS_FORMULARIOS = ['formNuevaComision', 'formEditarComision'];

  const PORCENTAJE_MAXIMO = 99;

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

  // Minúsculas y sin tildes, para que "miercoles" encuentre "Miércoles"
  function normalizar(texto) {
    return (texto || '')
      .toString()
      .toLowerCase()
      .normalize('NFD')
      .replace(/[\u0300-\u036f]/g, '')
      .trim();
  }

  // Devuelve el formulario de comisión al que pertenece un elemento (o null)
  function formularioComision(elemento) {
    const form = elemento.closest ? elemento.closest('form') : null;
    return form && IDS_FORMULARIOS.includes(form.id) ? form : null;
  }

  // =====================================================
  // 1. FECHAS DEL MODAL
  //    La fecha fin es opcional, pero si se llena no puede ser
  //    anterior a la de inicio.
  // =====================================================
  document.addEventListener('change', (e) => {
    const form = formularioComision(e.target);
    if (!form || e.target.name !== 'fechaInicio') return;

    const inicio = form.elements['fechaInicio'];
    const fin    = form.elements['fechaFin'];

    fin.min = inicio.value || '';

    // Si la fecha fin ya quedó antes del nuevo inicio, se limpia
    // (vacía significa "sin fecha de cierre", que sigue siendo válido)
    if (inicio.value && fin.value && fin.value < inicio.value) {
      fin.value = '';
    }
  });

  // =====================================================
  // 2. VALIDACIÓN EN EL NAVEGADOR (el servidor valida de nuevo)
  //    Devuelve el texto del problema, o '' si todo está bien.
  // =====================================================
  function validar(form) {
    const porcentaje = parseFloat(form.elements['porcentaje'].value);
    const inicio     = form.elements['fechaInicio'].value;
    const fin        = form.elements['fechaFin'].value;

    if (isNaN(porcentaje) || porcentaje <= 0 || porcentaje > PORCENTAJE_MAXIMO) {
      return 'El porcentaje debe ser mayor que 0 y como máximo 99.';
    }
    if (fin && fin < inicio) {
      return 'La fecha de fin no puede ser menor que la fecha de inicio.';
    }
    return '';
  }

  // =====================================================
  // 3. SUBMIT: Nueva configuración
  // =====================================================
    const FORMULARIOS = {
    formNuevaComision: {
      archivo: 'ingresar.php',
      cargando: 'Guardando...',
      titulo: 'Configuración registrada'
    },
    formEditarComision: {
      archivo: 'editar.php',
      cargando: 'Guardando cambios...',
      titulo: 'Configuración actualizada'
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
      form.elements['fechaFin'].min = '';

      await Swal.fire({
        icon: 'success',
        title: config.titulo,
        text: data.message,
        timer: 1800,
        showConfirmButton: false
      });

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
  // 4. EDITAR: llenar el modal con los datos de la fila
  //    (el modal se abre por data-modal desde el JS del dashboard;
  //     aquí solo se cargan los valores, sin petición al servidor)
  // =====================================================

  // Si el empleado de la fila no está en el select (porque está inactivo),
  // se agrega una opción temporal para que el valor no se pierda.
  function asegurarOpcion(select, valor, texto) {
    select.querySelectorAll('option[data-temporal]').forEach((o) => o.remove());

    const existe = [...select.options].some((o) => o.value === valor);
    if (existe) return;

    const opcion = document.createElement('option');
    opcion.value = valor;
    opcion.textContent = texto + ' (inactivo)';
    opcion.dataset.temporal = '1';
    select.appendChild(opcion);
  }

  document.addEventListener('click', (e) => {
    const boton = e.target.closest('.btn-accion.editar[data-modal="modalEditarComision"]');
    if (!boton) return;

    const form = document.getElementById('formEditarComision');
    if (!form) return;

    const fila = boton.closest('tr');
    const nombreEmpleado = fila ? fila.children[0].textContent.trim() : '';
    const selectEmpleado = form.elements['id_usuario'];

    asegurarOpcion(selectEmpleado, boton.dataset.usuario, nombreEmpleado);

    form.elements['id_comision'].value = boton.dataset.comision;
    selectEmpleado.value               = boton.dataset.usuario;
    form.elements['porcentaje'].value  = boton.dataset.porcentaje;
    form.elements['fechaInicio'].value = boton.dataset.inicio;
    form.elements['fechaFin'].value    = boton.dataset.fin;
    form.elements['fechaFin'].min      = boton.dataset.inicio;
    form.elements['estado'].value      = boton.dataset.estado;
  });

  // =====================================================
  // 5. BUSCADOR de la tabla de comisiones
  //    Busca en empleado, porcentaje, fechas y estado.
  //    No busca en "Fecha creación" ni en los botones.
  //    Acepta varias palabras: "otto activo" exige ambas.
  // =====================================================
  const COLUMNAS_SIN_BUSCAR = ['fecha creacion'];

  function mostrarSinResultados(tabla, mostrar) {
    let aviso = document.getElementById('sinResultadosComision');

    if (!aviso) {
      aviso = document.createElement('div');
      aviso.id = 'sinResultadosComision';
      aviso.className = 'tabla-vacia';
      aviso.innerHTML =
        '<p>No se encontraron configuraciones.</p>' +
        '<small>Prueba con otro nombre, porcentaje o fecha.</small>';
      tabla.parentElement.appendChild(aviso);
    }

    aviso.style.display = mostrar ? '' : 'none';
  }

  document.addEventListener('input', (e) => {
    if (e.target.id !== 'buscarComision') return;

    const tabla = document.getElementById('tablaComisiones');
    if (!tabla) return;

    // Posición de las columnas que no participan en la búsqueda
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