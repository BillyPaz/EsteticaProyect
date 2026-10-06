<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
        <section class="seccion" id="sec-servicios">
      <div class="panel-head">
        <div>
          <span class="eyebrow-dark">Catálogo</span>
          <h1>Servicios del <em>salón</em></h1>
          <p>Tabla servicios — costo, duración y disponibilidad.</p>
        </div>
        <span class="chip-rol">Admin</span>
      </div>

      <div class="bloque">
        <div class="bloque-top">
          <div><h2>Servicios registrados</h2></div>
          <div class="acciones-top">
            <input type="search" class="buscador" placeholder="Buscar servicio..." />
            <button class="btn-oro" data-modal="modalServicio">+ Nuevo servicio</button>
          </div>
        </div>
        <div class="tabla-scroll">
          <table class="tabla-panel">
            <thead>
              <tr><th>id_servicio</th><th>Nombre servicio</th><th>Costo servicio</th><th>Duración (min)</th><th>Activo</th><th>Fecha registro</th><th class="col-acc">Acciones</th></tr>
            </thead>
            <tbody>
              <tr><td>1</td><td class="principal">Corte normal</td><td>Q 80.00</td><td>30</td><td><span class="badge activo">Activo</span></td><td>05/01/2026 09:00</td>
                <td class="col-acc"><button class="btn-accion editar">Editar</button><button class="btn-accion eliminar">Eliminar</button></td></tr>
              <tr><td>2</td><td class="principal">Corte completo</td><td>Q 180.00</td><td>60</td><td><span class="badge activo">Activo</span></td><td>05/01/2026 09:05</td>
                <td class="col-acc"><button class="btn-accion editar">Editar</button><button class="btn-accion eliminar">Eliminar</button></td></tr>
              <tr><td>3</td><td class="principal">Alisado permanente</td><td>Q 450.00</td><td>120</td><td><span class="badge activo">Activo</span></td><td>05/01/2026 09:08</td>
                <td class="col-acc"><button class="btn-accion editar">Editar</button><button class="btn-accion eliminar">Eliminar</button></td></tr>
              <tr><td>4</td><td class="principal">Planchado</td><td>Q 150.00</td><td>45</td><td><span class="badge activo">Activo</span></td><td>05/01/2026 09:12</td>
                <td class="col-acc"><button class="btn-accion editar">Editar</button><button class="btn-accion eliminar">Eliminar</button></td></tr>
              <tr><td>5</td><td class="principal">Maquillaje</td><td>Q 250.00</td><td>50</td><td><span class="badge inactivo">Inactivo</span></td><td>05/01/2026 09:15</td>
                <td class="col-acc"><button class="btn-accion editar">Editar</button><button class="btn-accion eliminar">Eliminar</button></td></tr>
              <tr><td>6</td><td class="principal">Peinado</td><td>Q 120.00</td><td>40</td><td><span class="badge activo">Activo</span></td><td>05/01/2026 09:18</td>
                <td class="col-acc"><button class="btn-accion editar">Editar</button><button class="btn-accion eliminar">Eliminar</button></td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </section>  
</body>
</html>