

<?php
	include_once ROOT.'/views/practica/head/head.php';
	
?>	
</head>
<body>
	
	

<?php
	include_once ROOT.'/views/auth/auth-login.php';
	include_once 'modal-editar-instrumento.php';
	include_once 'modal-confirmar-instrumento.php';
	include_once ROOT.'/views/modal/modal-mensaje-servidor.php';
?>


	
	
<header class="sticky-top bg-light py-3 shadow-sm border-bottom position-relative">


  <div class="container text-center">
    <h2 class="fw-bold text-uppercase text-dark d-inline-block pb-1 mb-2 border-bottom border-3 border-primary">
     INSTRUMENTOS
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
      	<button class="btn btn-primary"  id="registro_instrumento">
        <i class="bi bi-plus-circle"></i> Registrar TAG
      </button>
      <button id="" class="btn btn-outline-secondary" onclick="window.app.instrumento.reload()">
        <i class="bi bi-arrow-clockwise"></i> Recargar
      </button>
    </div>
  </section>



  <!-- Mensaje dinámico -->


<div id="mensajeTablaINGLES"
     role="status"
     aria-live="polite"></div>
     
     
  <!-- Tabla de datos -->
  <section class="table-responsive"> 
    <table id="instrumento" width="100%" class="responsive table table-striped table-bordered table-hover align-middle nowrap dt-responsive w-100">
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



<?php

	include_once ROOT.'/views/practica/footer/footer.php';
?>

</body>
</html>
