/**
 * modulos/seguridad/modulos/js/modulos.js
 * Lógica del módulo "Módulos":
 * - Carga de usuario y su rol
 * - Modal de permisos por módulo
 * - Guardado masivo de permisos
 */

(function () {
  'use strict';

  const RUTA_MODULO = '/Peluqueria/modulos/seguridad/modulos';

  // =====================================================
  // ESTADO GLOBAL
  // =====================================================
  const estado = {
    idUsuario: null,        // ID del usuario actual
    nombreUsuario: '',      // Nombre para el SweetAlert
    idRol: null,            // ID del rol del usuario
    nombreRol: '',          // Nombre del rol
    permisos: {},           // { codigoAccion: { ver, crear, editar, eliminar, idAccion } }
    permisosOriginales: {}, // Snapshot para detectar cambios sin guardar
    tieneRol: false,        // Si el usuario tiene rol
    hayCambios: false,      // Si hay cambios sin guardar
    modalAbiertoPara: null, // Código de la acción que abrió el modal
  };

  // =====================================================
  // UTILIDADES
  // =====================================================

  /** Marca que hay cambios sin guardar */
  function marcarCambios() {
    estado.hayCambios = true;
  }

  /** Devuelve true si dos objetos de permisos son iguales */
  function permisosIguales(a, b) {
    const claves = new Set([...Object.keys(a), ...Object.keys(b)]);
    for (const k of claves) {
      const x = a[k] || { ver: 0, crear: 0, editar: 0, eliminar: 0 };
      const y = b[k] || { ver: 0, crear: 0, editar: 0, eliminar: 0 };
      if (x.ver !== y.ver || x.crear !== y.crear ||
          x.editar !== y.editar || x.eliminar !== y.eliminar) {
        return false;
      }
    }
    return true;
  }

  /** Actualiza el estado visual (switch + badge) de una tarjeta */
  function actualizarTarjeta(codigo) {
    const card = document.querySelector(`.card-modulo[data-modulo="${codigo}"]`);
    if (!card) return;

    const chk   = card.querySelector('.chk-modulo');
    const badge = card.querySelector('.estado-modulo');
    const p     = estado.permisos[codigo];

    const activo = !!(p && (p.ver || p.crear || p.editar || p.eliminar));

    chk.checked = activo;
    if (badge) {
      badge.textContent = activo ? 'Asignado' : 'No asignado';
      badge.classList.toggle('activo', activo);
      badge.classList.toggle('inactivo', !activo);
    }
  }

  /** Cuenta cuántos módulos están asignados */
  function contarAsignados() {
    let total = 0;
    Object.values(estado.permisos).forEach(p => {
      if (p.ver || p.crear || p.editar || p.eliminar) total++;
    });
    return total;
  }

  /** Actualiza las 3 métricas de arriba */
  function actualizarMetricas() {
    const numAsignados = document.getElementById('numAsignados');
    if (numAsignados) numAsignados.textContent = contarAsignados();
  }

  /** Habilita o deshabilita todos los switches */
  function habilitarSwitches(habilitar) {
    document.querySelectorAll('.chk-modulo').forEach(chk => {
      chk.disabled = !habilitar;
    });
  }

  // =====================================================
  // 1. CAMBIO DE USUARIO EN EL SELECT
  // =====================================================
  document.addEventListener('change', async (e) => {
    if (e.target.id !== 'selectEmpleadoModulo') return;

    const select = e.target;
    const nuevoId = select.value;

    // Si había usuario y cambios sin guardar → preguntar
    if (estado.idUsuario && estado.hayCambios) {
      const result = await Swal.fire({
        icon: 'warning',
        title: 'Cambios sin guardar',
        text: `Tienes cambios sin guardar para ${estado.nombreUsuario}. ¿Qué quieres hacer?`,
        showCancelButton: true,
        confirmButtonText: 'Descartar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        allowOutsideClick: false,
        allowEscapeKey: false,
      });

      if (!result.isConfirmed) {
        // Canceló → regresar el select al usuario anterior
        select.value = estado.idUsuario;
        return;
      }
      // Descartó → seguir con el nuevo
    }

    // Sin usuario seleccionado → resetear todo
    if (!nuevoId) {
      resetearModulo();
      return;
    }

    // Cargar el usuario
    await cargarUsuario(nuevoId);
  });

  // =====================================================
  // 2. CARGAR USUARIO (AJAX)
  // =====================================================
  async function cargarUsuario(idUsuario) {
    // Loading
    Swal.fire({
      title: 'Cargando...',
      allowOutsideClick: false,
      allowEscapeKey: false,
      didOpen: () => Swal.showLoading()
    });

    try {
      const resp = await fetch(`${RUTA_MODULO}/php/cargar_usuario.php?id_usuario=${idUsuario}`);
      const data = await resp.json();

      if (!data.success) {
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: data.message || 'No se pudo cargar el usuario.'
        });
        return;
      }

      // Guardar estado
      estado.idUsuario = data.usuario.id_usuario;
      estado.nombreUsuario = data.usuario.nombre;
      estado.idRol = data.rol ? data.rol.id_rol : null;
      estado.nombreRol = data.rol ? data.rol.nombreRol : '';
      estado.tieneRol = !!data.rol;

      // Cargar permisos actuales en el estado
      estado.permisos = {};
      data.permisos.forEach(p => {
        estado.permisos[p.accionCodigo] = {
          ver:      parseInt(p.puedeConsultar) || 0,
          crear:    parseInt(p.puedeCrear)     || 0,
          editar:   parseInt(p.puedeModificar) || 0,
          eliminar: parseInt(p.puedeEliminar)  || 0,
        };
      });

      // Snapshot para detectar cambios
      estado.permisosOriginales = JSON.parse(JSON.stringify(estado.permisos));
      estado.hayCambios = false;

      // Actualizar UI
      const rolEmpleado = document.getElementById('rolEmpleado');
      const rolActual   = document.getElementById('rolActual');
      if (rolEmpleado) rolEmpleado.textContent = estado.nombreRol || '—';
      if (rolActual)   rolActual.textContent   = estado.nombreRol || '—';

      // Actualizar tarjetas
      document.querySelectorAll('.card-modulo').forEach(card => {
        actualizarTarjeta(card.dataset.modulo);
      });

      // Habilitar switches solo si tiene rol
      habilitarSwitches(estado.tieneRol);

      actualizarMetricas();

      // Cerrar loading
      Swal.close();

      // Si no tiene rol → aviso
      if (!estado.tieneRol) {
        Swal.fire({
          icon: 'info',
          title: 'Sin rol asignado',
          text: `${estado.nombreUsuario} aún no tiene un rol asignado. Asígnalo primero en Ajustes → Accesos.`,
          confirmButtonText: 'Entendido'
        });
      }

    } catch (err) {
      Swal.fire({
        icon: 'error',
        title: 'Error de conexión',
        text: 'No se pudo conectar con el servidor.'
      });
    }
  }

  // =====================================================
  // 3. RESET (cuando no hay usuario seleccionado)
  // =====================================================
  function resetearModulo() {
    estado.idUsuario = null;
    estado.nombreUsuario = '';
    estado.idRol = null;
    estado.nombreRol = '';
    estado.permisos = {};
    estado.permisosOriginales = {};
    estado.tieneRol = false;
    estado.hayCambios = false;

    document.querySelectorAll('.card-modulo').forEach(card => {
      const chk   = card.querySelector('.chk-modulo');
      const badge = card.querySelector('.estado-modulo');
      chk.checked = false;
      badge.textContent = 'No asignado';
      badge.classList.add('inactivo');
      badge.classList.remove('activo');
    });

    habilitarSwitches(false);
    actualizarMetricas();

    const rolEmpleado = document.getElementById('rolEmpleado');
    const rolActual   = document.getElementById('rolActual');
    if (rolEmpleado) rolEmpleado.textContent = '—';
    if (rolActual)   rolActual.textContent   = '—';
  }

  // =====================================================
  // 4. CLIC EN SWITCH → abre modal de permisos
  // =====================================================
  document.addEventListener('change', (e) => {
    if (!e.target.classList.contains('chk-modulo')) return;

    const card = e.target.closest('.card-modulo');
    if (!card) return;

    const codigo = card.dataset.modulo;
    const idAccion = card.dataset.idAccion;

    // Guardar a qué acción se le está aplicando
    estado.modalAbiertoPara = codigo;

    // Precargar modal
    const inputCodigo = document.getElementById('inputCodigoModulo');
    const inputId     = document.getElementById('inputIdAccion');
    const titulo      = document.getElementById('modalPermisosTitulo');

    if (inputCodigo) inputCodigo.value = codigo;
    if (inputId)     inputId.value     = idAccion;
    if (titulo)      titulo.textContent = `Permisos: ${card.dataset.nombre}`;

    // Precargar checkboxes con los permisos actuales del módulo
    const p = estado.permisos[codigo] || { ver: 0, crear: 0, editar: 0, eliminar: 0 };
    const form = document.getElementById('formPermisos');
    form.querySelector('input[name="puedeConsultar"]').checked = !!p.ver;
    form.querySelector('input[name="puedeCrear"]').checked     = !!p.crear;
    form.querySelector('input[name="puedeModificar"]').checked = !!p.editar;
    form.querySelector('input[name="puedeEliminar"]').checked  = !!p.eliminar;

    // Marcar visualmente los labels activos
    form.querySelectorAll('.check-permiso').forEach(label => {
      const chk = label.querySelector('input');
      label.classList.toggle('activo', chk.checked);
    });

    // Abrir modal
    document.getElementById('modalPermisos').classList.add('abierto');
  });

  // Actualizar visual de check-permiso al marcar/desmarcar
  document.addEventListener('change', (e) => {
    if (!e.target.closest('.check-permiso')) return;
    const label = e.target.closest('.check-permiso');
    label.classList.toggle('activo', e.target.checked);
  });

  // =====================================================
  // 5. SUBMIT del modal de permisos → aplicar
  // =====================================================
  document.addEventListener('submit', (e) => {
    if (e.target.id !== 'formPermisos') return;
    e.preventDefault();

    const codigo = estado.modalAbiertoPara;
    if (!codigo) return;

    const form = e.target;
    const ver      = form.querySelector('input[name="puedeConsultar"]').checked ? 1 : 0;
    const crear    = form.querySelector('input[name="puedeCrear"]').checked     ? 1 : 0;
    const editar   = form.querySelector('input[name="puedeModificar"]').checked ? 1 : 0;
    const eliminar = form.querySelector('input[name="puedeEliminar"]').checked  ? 1 : 0;

    // Validar que al menos uno esté marcado
    if (!ver && !crear && !editar && !eliminar) {
      Swal.fire({
        icon: 'warning',
        title: 'Sin permisos',
        text: 'Debes seleccionar al menos un permiso para este módulo.',
        confirmButtonText: 'Entendido'
      });
      return;
    }

    // Guardar en el estado
    estado.permisos[codigo] = { ver, crear, editar, eliminar };

    // Actualizar tarjeta
    actualizarTarjeta(codigo);
    actualizarMetricas();

    // Marcar cambios
    marcarCambios();

    // Cerrar modal
    document.getElementById('modalPermisos').classList.remove('abierto');
    estado.modalAbiertoPara = null;
  });

  // =====================================================
  // 6. BOTÓN "GUARDAR ASIGNACIÓN"
  // =====================================================
  document.addEventListener('click', async (e) => {
    if (e.target.id !== 'btnGuardarAsignacion') return;

    // Validar usuario seleccionado
    if (!estado.idUsuario) {
      Swal.fire({
        icon: 'info',
        title: 'Sin usuario',
        text: 'Selecciona primero un usuario.',
        confirmButtonText: 'Entendido'
      });
      return;
    }

    // Validar que tenga rol
    if (!estado.tieneRol) {
      Swal.fire({
        icon: 'info',
        title: 'Sin rol',
        text: `${estado.nombreUsuario} no tiene un rol asignado. Asígnalo primero en Ajustes → Accesos.`,
        confirmButtonText: 'Entendido'
      });
      return;
    }

    // Confirmación
    const confirm = await Swal.fire({
      icon: 'question',
      title: '¿Guardar cambios?',
      text: `Se guardarán los permisos asignados al rol ${estado.nombreRol}.`,
      showCancelButton: true,
      confirmButtonText: 'Guardar',
      cancelButtonText: 'Cancelar',
      confirmButtonColor: '#c9a961',
      cancelButtonColor: '#6c757d',
    });

    if (!confirm.isConfirmed) return;

    // Enviar al servidor
    await guardarPermisos();
  });

  // =====================================================
  // 7. GUARDAR PERMISOS (AJAX)
  // =====================================================
  async function guardarPermisos() {
    Swal.fire({
      title: 'Guardando...',
      allowOutsideClick: false,
      allowEscapeKey: false,
      didOpen: () => Swal.showLoading()
    });

    try {
      const payload = {
        id_usuario: estado.idUsuario,
        id_rol: estado.idRol,
        permisos: estado.permisos
      };

      const resp = await fetch(`${RUTA_MODULO}/php/guardar_permisos.php`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify(payload)
      });

      const data = await resp.json();

      if (!data.success) {
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: data.message || 'No se pudieron guardar los permisos.'
        });
        return;
      }

      // Actualizar snapshot y quitar flag de cambios
      estado.permisosOriginales = JSON.parse(JSON.stringify(estado.permisos));
      estado.hayCambios = false;

      await Swal.fire({
        icon: 'success',
        title: 'Permisos guardados',
        text: `Los permisos del rol ${estado.nombreRol} se guardaron correctamente para ${estado.nombreUsuario}.`,
        timer: 2500,
        showConfirmButton: false
      });

    } catch (err) {
      Swal.fire({
        icon: 'error',
        title: 'Error de conexión',
        text: 'No se pudo conectar con el servidor.'
      });
    }
  }

  // =====================================================
  // 8. BUSCADOR DE MÓDULOS
  // =====================================================
  document.addEventListener('input', (e) => {
    if (e.target.id !== 'buscarModulo') return;

    const texto = e.target.value.trim().toLowerCase();
    document.querySelectorAll('.card-modulo').forEach(card => {
      const nombre = card.dataset.nombre.toLowerCase();
      card.style.display = (!texto || nombre.includes(texto)) ? '' : 'none';
    });
  });

})();