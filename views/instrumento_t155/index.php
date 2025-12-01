
<?php
  include_once ROOT.'/views/modal/modal-mensaje-servidor.php';
  include_once ROOT.'/views/layouts/modal-requerido-login.php';
  include_once ROOT.'/views/instrumento_t155/modal-eliminar.php';
  include_once'modal-registro-t155.php';
  include_once'moda-editar-t155.php';




?>


<!-- Encabezado fijo -->
<header class="sticky-top bg-light text-center py-3 shadow-sm border-bottom z-0">
  <h2 class="fw-bold text-uppercase text-dark d-inline-block pb-1 mb-0 border-bottom border-3 border-primary">
    Instrumentos T155
  </h2>
</header>

<!-- Contenido principal -->
<main class="container my-4">
  <!-- Barra de acciones -->
  <section class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
    <div class="btn-group" role="group" aria-label="Acciones principales">
      <button id="btnAbrirRegistroT155" class="btn btn-primary" onclick="registro_tag()">
        <i class="bi bi-plus-circle"></i> Registrar TAG
      </button>
      <button id="recargarTablaT155" class="btn btn-outline-secondary" onclick="recargar_table_t155()">
        <i class="bi bi-arrow-clockwise"></i> Recargar
      </button>
    </div>
  </section>

  <!-- Mensaje dinámico -->
  <div id="mensajeTablaT155" class="alert alert-info d-none" role="status" aria-live="polite"></div>

  <!-- Tabla de datos -->
  <section class="table-responsive">
    <table id="t155" class="table table-striped table-bordered table-hover align-middle nowrap dt-responsive w-100">
      <caption class="caption-top fw-semibold text-secondary">
        Lista actualizada de instrumentos T155
      </caption>
      <thead class="table-dark text-center">
        <tr>
          <th scope="col">ID</th>
          <th scope="col">TAG</th>
          <th scope="col">Ubicación</th>
        </tr>
      </thead>
      <tbody>
        <!-- Cargado dinámicamente -->
      </tbody>
    </table>
  </section>
</main>

