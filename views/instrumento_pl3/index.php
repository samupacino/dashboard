<?php

   include_once'modal-registro-pl3.php';
   include_once'modal-eliminar-PL3.php';
   include_once'modal-editar-pl3.php';
   include_once ROOT.'/views/modal/modal-mensaje-servidor.php';

   include_once ROOT.'/views/layouts/modal-requerido-login.php';
   
?>
<!-- Encabezado fijo -->
<header class="sticky-top bg-light text-center py-3 shadow-sm border-bottom z-0">
  <h2 class="fw-bold text-uppercase text-dark d-inline-block pb-1 mb-0 border-bottom border-3 border-primary">
    Instrumentos PL3
  </h2>
</header>

<!-- Contenido principal -->
<main class="container my-4">
  <!-- Barra de acciones -->
  <section class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
    <div class="btn-group" role="group" aria-label="Acciones principales">
      <button id="btnAbrirRegistroPL3" class="btn btn-primary" onclick="registro_tag_pl3()">
        <i class="bi bi-plus-circle"></i> Registrar TAG
      </button>
      <button id="recargarTablaPL3" class="btn btn-outline-secondary" onclick="recargar_table_pl3()">
        <i class="bi bi-arrow-clockwise"></i> Recargar
      </button>
    </div>
  </section>

  <!-- Mensaje dinámico -->
  <div id="mensajeTablaPL3" class="alert alert-info d-none" role="status" aria-live="polite"></div>

  <!-- Tabla de datos -->
  <section class="table-responsive">
    <table id="pl3" class="table table-striped table-bordered table-hover align-middle nowrap dt-responsive w-100">
      <caption class="caption-top fw-semibold text-secondary">
        Lista actualizada de instrumentos PL3
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

