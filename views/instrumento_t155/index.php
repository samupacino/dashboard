
<?php
  include_once ROOT.'/views/modal/modal-mensaje-servidor.php';
  include_once ROOT.'/views/layouts/modal-requerido-login.php';
  include_once'modal-registro-t155.php';
  include_once'moda-editar-t155.php';




?>



  <!-- Botones superiores -->
<div class="container mt-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <button id="btnAbrirRegistroT155" class="btn btn-primary" onclick='registro_tag()'>➕ Registrar TAG</button>
    <button id="recargarTablaT155" onclick="recargar_table_t155()" class="btn btn-secondary">🔁 Recargar tabla</button>

  </div>

  <!-- Mensaje -->
  <div id="mensajeTablaT155" class="mb-3"></div>

  <!-- Tabla responsiva -->
  <div class="table-responsive">
    <table id="t155" class="table table-striped table-bordered nowrap dt-responsive w-100">
      <thead class="table-dark text-center">
        <tr>
        
            <th>ID</th>
		        <th>TAG</th>
			      <th>UBICACION</th>
          
        </tr>
      </thead>
      <tbody class="">
        <!-- Cargado dinámicamente -->
      </tbody>
    </table>
  </div>
</div>
