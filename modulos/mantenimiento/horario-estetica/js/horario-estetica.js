(function () {
  'use strict';

  // Si el script se cargara dos veces, no duplicamos los listeners
  if (window.__horarioEsteticaJsCargado) return;
  window.__horarioEsteticaJsCargado = true;

  // =====================================================
  // URL base del módulo
  // =====================================================
  const RUTA_MODULO = '/Peluqueria/modulos/mantenimiento/horario-estetica';

  // Formularios del bloque de CIERRES (comparten los campos de fecha/horario)
  const IDS_FORM_CIERRE = ['formNuevoCierre', 'formEditarCierre'];

  // =====================================================
  // HELPERS GENERALES
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
  // BLOQUE 1: HORARIO SEMANAL
  // =====================================================

  const FORMULARIOS_HORARIO = {
    formNuevoHorarioEst: {
      archivo: 'ingresar_horario.php',
      cargando: 'Guardando...',
      titulo: 'Horario registrado'
    },
    formEditarHorarioEst: {
      archivo: 'editar_horario.php',
      cargando: 'Guardando cambios...',
      titulo: 'Horario actualizado'
    }
  };

  // 1.1 Submit de los formularios de horario semanal
  document.addEventListener('submit', async (e) => {
    const form = e.target;
    const config = FORMULARIOS_HORARIO[form.id];
    if (!config) return;

    e.preventDefault();

    const apertura = form.elements['horaApertura'].value;
    const cierre   = form.elements['horaCierre'].value;
    const estado   = form.elements['estado'].value;

    if (estado === '1' && apertura && cierre && cierre <= apertura) {
      Swal.fire({
        icon: 'warning',
        title: 'Revisa las horas',
        text: 'La hora de cierre debe ser mayor que la hora de apertura.'
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

  // 1.2 Editar: llenar el modal de horario semanal con los datos de la fila
  document.addEventListener('click', (e) => {
    const boton = e.target.closest('.btn-accion.editar[data-modal="modalEditarHorarioEst"]');
    if (!boton) return;

    const form = document.getElementById('formEditarHorarioEst');
    if (!form) return;

    form.elements['id_horario'].value    = boton.dataset.horario;
    form.elements['dias'].value          = boton.dataset.dia;
    form.elements['horaApertura'].value  = boton.dataset.apertura;
    form.elements['horaCierre'].value    = boton.dataset.cierre;
    form.elements['estado'].value        = boton.dataset.estado;
  });

  // =====================================================
  // BLOQUE 2: CIERRES PROGRAMADOS
  // =====================================================

  const FORMULARIOS_CIERRE = {
    formNuevoCierre: {
      archivo: 'ingresar_cierre.php',
      cargando: 'Guardando...',
      titulo: 'Cierre registrado'
    },
    formEditarCierre: {
      archivo: 'editar_cierre.php',
      cargando: 'Guardando cambios...',
      titulo: 'Cierre actualizado'
    }
  };

  // Devuelve el formulario de cierre al que pertenece un elemento (o null)
  function formularioCierre(elemento) {
    const form = elemento.closest ? elemento.closest('form') : null;
    return form && IDS_FORM_CIERRE.includes(form.id) ? form : null;
  }

  // 2.1 Fechas: mostrar/ocultar los campos de horario según coincidan o no
  function actualizarCamposHorario(form) {
    const inicio   = form.elements['fechaInicio'].value;
    const fin      = form.elements['fechaFin'].value;
    const mismoDia = inicio !== '' && inicio === fin;

    form.querySelectorAll('[data-campo-horario]').forEach((el) => {
      el.style.display = mismoDia ? '' : 'none';
    });

    if (!mismoDia) {
      form.elements['horaInicio'].value = '';
      form.elements['horaFin'].value = '';
    }
  }

  function ajustarFechas(form) {
    const inicio = form.elements['fechaInicio'];
    const fin    = form.elements['fechaFin'];

    fin.min = inicio.value || '';

    if (inicio.value && (!fin.value || fin.value < inicio.value)) {
      fin.value = inicio.value;
    }
  }

  document.addEventListener('change', (e) => {
    const form = formularioCierre(e.target);
    if (!form) return;

    const nombre = e.target.name;
    if (nombre !== 'fechaInicio' && nombre !== 'fechaFin') return;

    if (nombre === 'fechaInicio') ajustarFechas(form);
    actualizarCamposHorario(form);
  });

  // 2.2 Validación en el navegador antes de enviar
  function validarCierre(form) {
    const inicio     = form.elements['fechaInicio'].value;
    const fin        = form.elements['fechaFin'].value;
    const horaInicio = form.elements['horaInicio'].value;
    const horaFin    = form.elements['horaFin'].value;

    if (fin < inicio) {
      return 'La fecha de fin no puede ser menor que la fecha de inicio.';
    }
    if ((horaInicio && !horaFin) || (!horaInicio && horaFin)) {
      return 'Indica la hora de inicio y la hora de fin, o deja ambas vacías si es todo el día.';
    }
    if (horaInicio && horaFin && horaFin <= horaInicio) {
      return 'La hora de fin debe ser mayor que la hora de inicio.';
    }
    return '';
  }

  // 2.3 Submit de los formularios de cierre
  document.addEventListener('submit', async (e) => {
    const form = e.target;
    const config = FORMULARIOS_CIERRE[form.id];
    if (!config) return;

    e.preventDefault();

    const problema = validarCierre(form);
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
      actualizarCamposHorario(form);

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

  // 2.4 Editar: llenar el modal de cierre con los datos de la fila
  document.addEventListener('click', (e) => {
    const boton = e.target.closest('.btn-accion.editar[data-modal="modalEditarCierre"]');
    if (!boton) return;

    const form = document.getElementById('formEditarCierre');
    if (!form) return;

    form.elements['id_cierre'].value     = boton.dataset.cierre;
    form.elements['tipo'].value          = boton.dataset.tipo;
    form.elements['fechaInicio'].value   = boton.dataset.inicio;
    form.elements['fechaFin'].value      = boton.dataset.fin;
    form.elements['fechaFin'].min        = boton.dataset.inicio;
    form.elements['horaInicio'].value    = boton.dataset.horaInicio;
    form.elements['horaFin'].value       = boton.dataset.horaFin;
    form.elements['observaciones'].value = boton.dataset.observaciones;
    form.elements['estado'].value        = boton.dataset.estado;

    // Los valores puestos por código no disparan "change":
    // se muestra u oculta el bloque de horas según las fechas cargadas.
    actualizarCamposHorario(form);
  });

  // 2.5 Buscador de la tabla de cierres
  //     Busca en tipo, fechas, horario, motivo y estado.
  //     No busca en "Registrado por", "Fecha creación" ni en los botones.
  const COLUMNAS_SIN_BUSCAR = ['registrado por', 'fecha creacion'];

  function mostrarSinResultados(tabla, mostrar) {
    let aviso = document.getElementById('sinResultadosCierre');

    if (!aviso) {
      aviso = document.createElement('div');
      aviso.id = 'sinResultadosCierre';
      aviso.className = 'tabla-vacia';
      aviso.innerHTML =
        '<p>No se encontraron cierres.</p>' +
        '<small>Prueba con otro tipo, fecha o motivo.</small>';
      tabla.parentElement.appendChild(aviso);
    }

    aviso.style.display = mostrar ? '' : 'none';
  }

  document.addEventListener('input', (e) => {
    if (e.target.id !== 'buscarCierre') return;

    const tabla = document.getElementById('tablaCierresEstetica');
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