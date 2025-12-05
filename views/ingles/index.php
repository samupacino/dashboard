<!DOCTYPE html>
<html lang="en">
	

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">



    <!-- Esta linea es para instalar bootstrap -->
    <link rel="stylesheet" href="bootstrap-5.3.3-dist/css/bootstrap.min.css">


    <!-- our project is using icons from Solid, Sharp Thin, Sharp Duotone Thin + Brands -->
    <link href="fontawesome-free-7.1.0-web/css/fontawesome.css" rel="stylesheet" />
    <link href="fontawesome-free-7.1.0-web/css/brands.css" rel="stylesheet" />
    <link href="fontawesome-free-7.1.0-web/css/solid.css" rel="stylesheet" />
    
    
     <script src="https://code.jquery.com/jquery-3.7.1.js"></script>

  	<!-- Estas dos lineas es para usar datatable-->
  	<link rel="stylesheet" href="https://cdn.datatables.net/2.3.2/css/dataTables.dataTables.css" />
  	<script src="https://cdn.datatables.net/2.3.2/js/dataTables.js"></script> 

 	<!-- DataTables CSS AGREUE ESTO ULTIMO ACUERDATE CTM-->
  	<link rel="stylesheet" href="https://cdn.datatables.net/responsive/3.0.7/css/responsive.dataTables.min.css">
  	<script src="https://cdn.datatables.net/responsive/3.0.7/js/dataTables.responsive.min.js"></script> 




	<!-- ESTILOS GENERAL-->
	<link rel="stylesheet" href="css/estilos-iconos-ingles.css?v=<?php echo time();?>">
	<link rel="stylesheet" href="css/estilos-mensaje-ingles.css?v=<?php echo time();?>">
	
	<link rel="stylesheet" href="css/estilos-login-modal-ingles.css?v=<?php echo time();?>">
	
	
<style>
/* ===== Scroll interno del modal ===== */
.modal-dialog-scrollable .modal-content {
  max-height: 90vh;       /* ocupa 90% de la altura de la ventana */
  overflow-y: auto;       /* permite desplazamiento vertical */
  
  
  
  
  
}
</style>
        
    <title>Document</title>
</head>
<body>

<?php

	include_once'modal-eliminar-ingles.php';
	
?>

<?php

	include_once'modal-registro-ingles.php';
	include_once 'modal-ingles.php';
?>







<header class="sticky-top bg-light py-3 shadow-sm border-bottom position-relative">

  <!-- Botón Login simple, negro, visible y tamaño mediano -->
  <button id="btnAbrirLogin"
          class="btn btn-outline-dark fw-bold px-5 py-2 position-absolute top-0 end-0 m-4"
          style="z-index: 1200; font-size: 1.5rem;">
    Login
  </button>

  <div class="container text-center">
    <h2 class="fw-bold text-uppercase text-dark d-inline-block pb-1 mb-2 border-bottom border-3 border-primary">
      Palabras en Inglés
    </h2>

    <div class="mt-2">
      <a href="/dashboard"
         class="btn btn-outline-success px-4 py-2 rounded-4 fw-bold text-uppercase shadow-sm border-2">
        <i class="fa-solid fa-circle-info me-2"></i> Retornar al Dashboard
      </a>
    </div>
  </div>

</header>




<!-- Contenido principal -->
<main class="container my-4">
	
	

	
  <!-- Barra de acciones -->
  <section class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
    <div class="btn-group" role="group" aria-label="Acciones principales">
      	<button class="btn btn-primary"  id="abrirModal" onclick="">
        <i class="bi bi-plus-circle"></i> Registrar TAG
      </button>
      <button id="recargarTablaINGLES" class="btn btn-outline-secondary" onclick="App.ingles.reload()">
        <i class="bi bi-arrow-clockwise"></i> Recargar
      </button>
    </div>
  </section>



  <!-- Mensaje dinámico -->
  <div id="mensajeTablaINGLES" class="alert alert-info d-none" role="status" aria-live="polite"></div>

  <!-- Tabla de datos -->
  <section class="table-responsive"> 
    <table id="ingles" width="100%" class="responsive nowrap table table-striped table-bordered table-hover align-middle nowrap dt-responsive w-100">
      <caption class="caption-top fw-semibold text-secondary">
        Lista actualizada: 
      </caption>
      <thead class="table-dark text-center">
        <tr>

        </tr>
      </thead>
      <tbody>
        <!-- Cargado dinámicamente -->
      </tbody>
    </table>
  </section>
</main>



<!--
Si solo usas componentes simples (modal, collapse, alert, etc.) podrías usar bootstrap.min.js, 
pero como casi siempre en un proyecto real aparece un dropdown o un tooltip, lo común es 
trabajar con bootstrap.bundle.min.js
-->
<script src="bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>

<script src="/js/javascript-login-modal-ingles.js?v=<?php echo time();?>"></script>
<script src="/js/ingles_datatable.js?v=<?php echo time();?>"></script>
<script src="/js/script-ingles-registro-editar.js?v=<?php echo time();?>"></script>
<script src="/js/ingles_fill_list.js?v=<?php echo time();?>"></script> 
	
</body>
