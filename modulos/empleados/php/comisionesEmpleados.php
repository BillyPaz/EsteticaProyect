<section class="seccion" id="sec-emp-comisiones">
      <div class="panel-head">
        <div>
          <span class="eyebrow-dark">Empleados</span>
          <h1>Comisiones <em>empleados</em></h1>
          <p>Tabla comisiones_configuracion — porcentaje por empleado y vigencia.</p>
        </div>
        <span class="chip-rol">Admin</span>
      </div>

      <div class="bloque">
        <div class="bloque-top">
          <div><h2>Configuraciones de comisión</h2></div>
          <div class="acciones-top">
            <input type="search" class="buscador" placeholder="Buscar empleado..." />
            <button class="btn-oro" data-modal="modalComision">+ Nueva configuración</button>
          </div>
        </div>

        <div class="tabla-scroll">
          <table class="tabla-panel">
            <thead>
              <tr>
                <th>id_empleado</th><th>Porcentaje</th>
                <th>Fecha inicio</th><th>Fecha fin</th><th>Estado</th><th>Fecha creación</th><th class="col-acc">Acciones</th>
              </tr>
            </thead>
            <tbody>
              <td class="principal">2 — Otto Mérida</td><td>15.00 %</td><td>01/01/2026</td><td><span class="vacio-nada">Nada</span></td>
                <td><span class="badge activo">Activo</span></td><td>01/01/2026 09:00</td>
                <td class="col-acc"><button class="btn-accion editar">Editar</button><button class="btn-accion eliminar">Eliminar</button></td></tr>
              <td class="principal">3 — Kevin Solís</td><td>12.50 %</td><td>01/03/2026</td><td>31/12/2026</td>
                <td><span class="badge activo">Activo</span></td><td>28/02/2026 18:30</td>
                <td class="col-acc"><button class="btn-accion editar">Editar</button><button class="btn-accion eliminar">Eliminar</button></td></tr>
              <td class="principal">4 — Alejandra Pineda</td><td>5.00 %</td><td>01/12/2025</td><td>28/02/2026</td>
                <td><span class="badge inactivo">Inactivo</span></td><td>25/11/2025 10:15</td>
                <td class="col-acc"><button class="btn-accion editar">Editar</button><button class="btn-accion eliminar">Eliminar</button></td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </section>
