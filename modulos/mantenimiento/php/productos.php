<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
        <section class="seccion" id="sec-productos">
      <div class="panel-head">
        <div>
          <span class="eyebrow-dark">Catálogo</span>
          <h1>Productos de <em>venta</em></h1>
          <p>Tabla productos — catálogo general, independiente del stock.</p>
        </div>
        <span class="chip-rol">Admin</span>
      </div>

      <div class="bloque">
        <div class="bloque-top">
          <div><h2>Productos registrados</h2><p class="sub">5 productos en catálogo</p></div>
          <div class="acciones-top">
            <input type="search" class="buscador" placeholder="Buscar producto..." />
            <button class="btn-oro" data-modal="modalProducto">+ Nuevo producto</button>
          </div>
        </div>
        <div class="tabla-scroll">
          <table class="tabla-panel">
            <thead>
              <th>Nombre producto</th><th>Observaciones</th><th>Activo</th><th>Fecha registro</th><th class="col-acc">Acciones</th></tr>
            </thead>
            <tbody>
              <td class="principal">Shampoo Keratina</td><td>Cuidado capilar</td><td><span class="badge activo">Activo</span></td><td>05/01/2026 10:00</td>
                <td class="col-acc"><button class="btn-accion editar">Editar</button></td></tr>
              <td class="principal">Tinte profesional</td><td>Coloración castaño</td><td><span class="badge activo">Activo</span></td><td>05/01/2026 10:05</td>
                <td class="col-acc"><button class="btn-accion editar">Editar</button></td></tr>
              <td class="principal">Cera moldeadora</td><td>Acabado mate</td><td><span class="badge activo">Activo</span></td><td>05/01/2026 10:08</td>
                <td class="col-acc"><button class="btn-accion editar">Editar</button></td></tr>
              <td class="principal">Mascarilla facial</td><td>Arcilla</td><td><span class="badge activo">Activo</span></td><td>05/01/2026 10:12</td>
                <td class="col-acc"><button class="btn-accion editar">Editar</button></td></tr>
              <td class="principal">Aceite de argán</td><td><span class="vacio-nada">Nada</span></td><td><span class="badge inactivo">Inactivo</span></td><td>05/01/2026 10:15</td>
                <td class="col-acc"><button class="btn-accion editar">Editar</button></td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </section>

</body>
</html>