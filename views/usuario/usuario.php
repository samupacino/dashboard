
  <?php

    include_once'modal-registro.php';
    include_once ROOT.'/views/modal/modal-mensaje-servidor.php';
    include_once ROOT.'/views/layouts/modal-requerido-login.php';
    include_once'modal-editar.php';
  ?>


  <!-- Botones superiores -->
<div class="container mt-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <button id="btnAbrirRegistro" class="btn btn-primary">➕ Registrar Usuario</button>
    <button id="recargarTabla" onclick="recargar_table_usuario()" class="btn btn-secondary">🔁 Recargar tabla</button>

  </div>

  <!-- Mensaje -->
  <div id="mensajeTabla" class="mb-3"></div>

  <!-- Tabla responsiva -->
  <div class="table-responsive">
    <table id="usuario" class="table table-striped table-bordered nowrap dt-responsive w-100">
      <thead class="table-dark text-center">
        <tr>
          <th>ID</th>
          <th>NOMBRE</th>
          <th>CORREO</th>
          <th>ROL</th>
        </tr>
      </thead>
      <tbody class="">
        <!-- Cargado dinámicamente -->
      </tbody>
    </table>
  </div>
</div>

