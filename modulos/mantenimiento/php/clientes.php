<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
        <section class="seccion" id="sec-clientes">
      <div class="panel-head">
        <div>
          <span class="eyebrow-dark">Cartera</span>
          <h1>Clientes <em>registrados</em></h1>
          <p>Tabla clientes — datos de contacto y estado.</p>
        </div>
        <span class="chip-rol">Admin</span>
      </div>

      <div class="bloque">
        <div class="bloque-top">
          <div><h2>Listado de clientes</h2></div>
          <div class="acciones-top">
            <input type="search" class="buscador" placeholder="Buscar cliente..." />
            <button class="btn-oro" data-modal="modalCliente">+ Nuevo cliente</button>
          </div>
        </div>
        <div class="tabla-scroll">
          <table class="tabla-panel">
            <thead>
              <tr><th>id_cliente</th><th>Nombre cliente</th><th>Apellido cliente</th><th>Teléfono</th><th>Correo</th><th>Género</th>
                <th>Fecha registro</th><th>Fecha actualización</th><th>Estado</th><th class="col-acc">Acciones</th></tr>
            </thead>
            <tbody>
              <tr><td>1</td><td class="principal">María Fernanda</td><td>López</td><td>5512 4478</td><td>mfernanda@correo.com</td><td>FEMENINO</td>
                <td>12/02/2026 10:40</td><td>16/08/2026 09:30</td><td><span class="badge activo">Activo</span></td>
                <td class="col-acc"><button class="btn-accion editar">Editar</button><button class="btn-accion eliminar">Eliminar</button></td></tr>
              <tr><td>2</td><td class="principal">Andrés</td><td>Batres</td><td>4478 9012</td><td>abatres@correo.com</td><td>MASCULINO</td>
                <td>02/03/2026 12:10</td><td>16/08/2026 11:00</td><td><span class="badge activo">Activo</span></td>
                <td class="col-acc"><button class="btn-accion editar">Editar</button><button class="btn-accion eliminar">Eliminar</button></td></tr>
              <tr><td>3</td><td class="principal">Gabriela</td><td>Morales</td><td>3390 5521</td><td>gmorales@correo.com</td><td>FEMENINO</td>
                <td>03/03/2026 15:20</td><td>16/08/2026 14:00</td><td><span class="badge activo">Activo</span></td>
                <td class="col-acc"><button class="btn-accion editar">Editar</button><button class="btn-accion eliminar">Eliminar</button></td></tr>
              <tr><td>4</td><td class="principal">Lucía</td><td>Herrera</td><td>5678 1290</td><td>lherrera@correo.com</td><td>NO_ESPECIFICADO</td>
                <td>20/04/2026 09:00</td><td>17/08/2026 16:30</td><td><span class="badge inactivo">Inactivo</span></td>
                <td class="col-acc"><button class="btn-accion editar">Editar</button><button class="btn-accion eliminar">Eliminar</button></td></tr>
              <tr><td>5</td><td class="principal">Josué</td><td>Ramírez</td><td>2201 7745</td><td>jramirez@correo.com</td><td>MASCULINO</td>
                <td>18/05/2026 11:02</td><td>17/08/2026 10:15</td><td><span class="badge activo">Activo</span></td>
                <td class="col-acc"><button class="btn-accion editar">Editar</button><button class="btn-accion eliminar">Eliminar</button></td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </section>
</body>
</html>