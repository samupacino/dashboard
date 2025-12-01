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



<!-- Fondo claro de pantalla completa -->
<div class="bg-light d-flex flex-column justify-content-center align-items-center min-vh-100 text-center">



  <!-- Botones apilados -->
  <div class="d-flex flex-column gap-3" style="width: 260px;">

    <a href="/dashboard" class="btn btn-outline-success py-3 rounded-4 fw-bold text-uppercase shadow-sm border-2">
      <i class="fa-solid fa-gauge-high me-2"></i> Dashboard
    </a>

    <a href="#" class="btn btn-outline-success py-3 rounded-4 fw-bold text-uppercase shadow-sm border-2">
      <i class="fa-solid fa-circle-info me-2"></i> 
    </a>

    <a href="#" class="btn btn-outline-success py-3 rounded-4 fw-bold text-uppercase shadow-sm border-2">
      <i class="fa-solid fa-diagram-project me-2"></i> 
    </a>

    <a href="#" class="btn btn-outline-success py-3 rounded-4 fw-bold text-uppercase shadow-sm border-2">
      <i class="fa-solid fa-microchip me-2"></i> 
    </a>

    <a href="#" class="btn btn-outline-success py-3 rounded-4 fw-bold text-uppercase shadow-sm border-2">
      <i class="fa-solid fa-industry me-2"></i> 
    </a>

  </div>
</div>


</body>
</html>
