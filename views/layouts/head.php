<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />

  <!-- Esta linea es para instalar bootstrap -->
  <link rel="stylesheet" href="bootstrap-5.3.3-dist/css/bootstrap.min.css">


  <script src="https://code.jquery.com/jquery-3.7.1.js"></script>

  <!-- Estas dos lineas es para usar datatable-->
  <link rel="stylesheet" href="https://cdn.datatables.net/2.3.2/css/dataTables.dataTables.css" />
  <script src="https://cdn.datatables.net/2.3.2/js/dataTables.js"></script> 

 <!-- DataTables CSS AGREUE ESTO ULTIMO ACUERDATE CTM-->
  <link rel="stylesheet" href="https://cdn.datatables.net/responsive/3.0.7/css/responsive.dataTables.min.css">
  <script src="https://cdn.datatables.net/responsive/3.0.7/js/dataTables.responsive.min.js"></script> 

  
  <!-- ------------------------------------------------------------------------- -->
  <!-- Estas lineas son para uso exclusivo para barra menu-->
   
  <link rel="stylesheet" href="barra-menu/estilos/fonts.css?t=<?php echo mt_rand(); ?>">
	<link rel="stylesheet" href="barra-menu/estilos/prism.css?t=<?php echo mt_rand(); ?>">
	<link rel="stylesheet" href="barra-menu/estilos/custom.css?t=<?php echo mt_rand(); ?>">
	<link rel="stylesheet" href="barra-menu/estilos/extra.css?t=<?php echo mt_rand(); ?>">

  <!-- ------------------------------------------------------------------------- -->
   
  <link rel="stylesheet" href="css/estilos-login-modal.css?v=<?php echo time();?>">
  <link rel="stylesheet" href="css/estilos-registro-modal.css?v=<?php echo time();?>">
  <link rel="stylesheet" href="css/estilos-editar-modal.css?v=<?php echo time();?>">
  <link rel="stylesheet" href="css/estilos-mensaje-tabla.css?v=<?php echo time();?>">
  <link rel="stylesheet" href="css/estilos-datatable.css?v=<?php echo time();?>">
  <link rel="stylesheet" href="css/estilos-iconos.css?v=<?php echo time();?>">
  <link rel="stylesheet" href="css/estilos-perfil.css?v=<?php echo time();?>">

  <link rel="stylesheet" href="css/estilos-pl3.css?v=<?php echo time();?>">



  <!-- our project is using icons from Solid, Sharp Thin, Sharp Duotone Thin + Brands -->
  <link href="fontawesome-free-7.1.0-web/css/fontawesome.css" rel="stylesheet" />
  <link href="fontawesome-free-7.1.0-web/css/brands.css" rel="stylesheet" />
  <link href="fontawesome-free-7.1.0-web/css/solid.css" rel="stylesheet" />


  <title>Dashboard</title>
 
</head>
<body>

  <?php
  	
 	include_once ROOT.'/views/modal/modal-mensaje-servidor.php';
    
  ?>
  
  <?php
  include_once'barra-menu.php';
  include_once'modal-requerido-login.php';
   
  ?>

  <?php
    include_once'modal-registro.php';
    
  ?>

  
