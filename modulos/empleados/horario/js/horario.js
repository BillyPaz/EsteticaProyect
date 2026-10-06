(function () {
  'use strict';

  // Si el script se cargara dos veces, no duplicamos los listeners
  if (window.__horarioJsCargado) return;
  window.__horarioJsCargado = true;

  // =====================================================
  // URL base del módulo
  // =====================================================
  const RUTA_MODULO = '/Peluqueria/modulos/empleados/horario';

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

  // =====================================================
  // 1. SUBMIT: Nuevo horario y Editar horario
  // =====================================================
  const FORMULARIOS = {
    formNuevoHorario: {
      archivo: 'ingresar.php',
      cargando: 'Guardando...',
      titulo: 'Horario registrado'
    },
    formEditarHorario: {
      archivo: 'editar.php',
      cargando: 'Guardando cambios...',
      titulo: 'Horario actualizado'
    }
  };

  document.addEventListener('submit', async (e) => {
    const form = e.target;
    const config = FORMULARIOS[form.id];
    if (!config) return;

    e.preventDefault();

    // Validación rápida en el navegador (el servidor valida de nuevo)
    const inicio = form.elements['horaInicio'].value;
    const fin    = form.elements['horaFin'].value;

    if (inicio && fin && fin <= inicio) {
      Swal.fire({
        icon: 'warning',
        title: 'Revisa las horas',
        text: 'La hora de salida debe ser mayor que la hora de entrada.'
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
  // 2. EDITAR: llenar el modal con los datos de la fila
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
    const boton = e.target.closest('.btn-accion.editar[data-modal="modalEditarHorario"]');
    if (!boton) return;

    const form = document.getElementById('formEditarHorario');
    if (!form) return;

    const fila = boton.closest('tr');
    const nombreEmpleado = fila ? fila.children[0].textContent.trim() : '';
    const selectEmpleado = form.elements['id_usuario'];

    asegurarOpcion(selectEmpleado, boton.dataset.usuario, nombreEmpleado);

    form.elements['id_horario'].value = boton.dataset.horario;
    selectEmpleado.value              = boton.dataset.usuario;
    form.elements['dias'].value       = boton.dataset.dia;
    form.elements['horaInicio'].value = boton.dataset.inicio;
    form.elements['horaFin'].value    = boton.dataset.fin;
    form.elements['estado'].value     = boton.dataset.estado;
  });

  // =====================================================
  // 3. BUSCADOR de la tabla de horarios
  //    Busca en empleado, día, horas y estado (no en los botones).
  //    Acepta varias palabras: "otto lunes" muestra solo lo que cumpla ambas.
  // =====================================================
  function mostrarSinResultados(tabla, mostrar) {
    let aviso = document.getElementById('sinResultadosHorario');

    if (!aviso) {
      aviso = document.createElement('div');
      aviso.id = 'sinResultadosHorario';
      aviso.className = 'tabla-vacia';
      aviso.innerHTML =
        '<p>No se encontraron horarios.</p>' +
        '<small>Prueba con otro nombre, día u hora.</small>';
      tabla.parentElement.appendChild(aviso);
    }

    aviso.style.display = mostrar ? '' : 'none';
  }

  document.addEventListener('input', (e) => {
    if (e.target.id !== 'buscarHorario') return;

    const tabla = document.getElementById('tablaHorarios');
    if (!tabla) return;

    const terminos = normalizar(e.target.value).split(/\s+/).filter(Boolean);
    let visibles = 0;

    tabla.querySelectorAll('tbody tr').forEach((fila) => {
      const texto = normalizar(
        [...fila.children]
          .filter((celda) => !celda.classList.contains('col-acc'))
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