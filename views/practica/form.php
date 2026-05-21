


<?php
	include_once ROOT.'/views/practica/head/head.php';
	
?>	
</head>
<body>
	
	
<?php
	include_once ROOT.'/views/auth/auth-login.php';
	include_once 'modal-editar-instrumento.php';
	include_once ROOT.'/views/modal/modal-mensaje-servidor.php';
?>

<header class="sticky-top bg-light py-3 shadow-sm border-bottom position-relative">


  <div class="container text-center">
    <h2 class="fw-bold text-uppercase text-dark d-inline-block pb-1 mb-2 border-bottom border-3 border-primary">
     INSTRUMENTOS
    </h2>

    <div class="mt-2">
      <a href="/instrumento"
         class="btn btn-outline-success px-4 py-2 rounded-4 fw-bold text-uppercase shadow-sm border-2">
        <i class="fa-solid fa-circle-info me-2"></i> Retornar
      </a>
    </div>
  </div>

</header>





<div class="container py-4 py-md-5">
	
	
  <div class="row justify-content-center">
    <div class="col-12 col-lg-9 col-xl-8">

      <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-3 p-md-4 p-lg-5">

          <div class="mb-4 text-center text-md-start">
            <h2 class="fw-bold text-secondary border-bottom border-3 border-warning d-inline-block pb-2 mb-2">
              Registro de Instrumento
            </h2>
            <p class="text-muted mb-0">
              Complete los datos del instrumento y adjunte una foto de referencia.
            </p>
          </div>
          
          
          
          

          <form id="formInstrumento" enctype="multipart/form-data" novalidate>

            <div class="row g-3">

              <div class="col-12 col-md-6">
                <label for="tag" class="form-label fw-semibold">Tag</label>
                <input type="text" class="form-control" id="tag" name="tag" placeholder="Ej: PT-2034" required>
                <div class="invalid-feedback">Ingrese el tag del instrumento.</div>
              </div>

              <div class="col-12 col-md-6">
                <label for="tipo" class="form-label fw-semibold">Tipo</label>
                <input type="text" class="form-control" id="tipo" name="tipo" placeholder="Ej: Transmisor" required>
                <div class="invalid-feedback">Ingrese el tipo de instrumento.</div>
              </div>

              <div class="col-12">
                <label for="descripcion" class="form-label fw-semibold">Descripción</label>
                <input type="text" class="form-control" id="descripcion" name="descripcion" placeholder="Descripción corta del instrumento" required>
                <div class="invalid-feedback">Ingrese una descripción.</div>
              </div>

              <div class="col-12 col-md-6">
                <label for="planta" class="form-label fw-semibold">Planta</label>
                <input type="text" class="form-control" id="planta" name="planta" placeholder="Ej: T155" required>
                <div class="invalid-feedback">Ingrese la planta.</div>
              </div>

              <div class="col-12 col-md-6">
                <label for="area" class="form-label fw-semibold">Área</label>
                <input type="text" class="form-control" id="area" name="area" placeholder="Ej: Coldbox" required>
                <div class="invalid-feedback">Ingrese el área.</div>
              </div>

              <div class="col-12">
                <label for="ubicacion_exacta" class="form-label fw-semibold">Ubicación exacta</label>
                <textarea class="form-control" id="ubicacion_exacta" name="ubicacion_exacta" rows="3" placeholder="Ej: Plataforma nivel 2, lado norte, cerca de la válvula XV-2030" required></textarea>
                <div class="invalid-feedback">Ingrese la ubicación exacta.</div>
              </div>

              <div class="col-12 col-md-6">
                <label for="estado" class="form-label fw-semibold">Estado</label>
                <select class="form-select" id="estado" name="estado">
                  <option value="">(Por defecto activo)</option>
                  <option value="activo">Activo</option>
                  <option value="inactivo">Inactivo</option>
                </select>
              </div>

              <div class="col-12 col-md-6">
                <label for="foto" class="form-label fw-semibold">Foto</label>
                <input class="form-control" type="file" id="foto" name="foto" accept="image/*">
              </div>

              <div class="col-12">
                <div class="border rounded-3 bg-white p-2 text-center d-none" id="previewWrapper">
                  <img id="preview" src="" alt="Vista previa" class="img-fluid rounded-3" style="max-height: 220px;">
                </div>
              </div>

              <div class="col-12">
                <label for="observacion" class="form-label fw-semibold">Observación</label>
                <textarea class="form-control" id="observacion" name="observacion" rows="2" placeholder="Dato adicional opcional"></textarea>
              </div>

            </div>
            
         

            <div class="mt-4 d-flex flex-column flex-sm-row gap-2 justify-content-end">
				
				
              <button type="reset" class="btn btn-outline-secondary" id="btnLimpiarInstrumento">
                Limpiar
              </button>
              <button type="submit" class="btn btn-warning text-white fw-semibold px-4">
                Guardar
              </button>
            </div>
            
       

          </form>
          
       
    
        </div>
      </div>

    </div>
  </div>
</div>

<!--
Si solo usas componentes simples (modal, collapse, alert, etc.) podrías usar bootstrap.min.js, 
pero como casi siempre en un proyecto real aparece un dropdown o un tooltip, lo común es 
trabajar con bootstrap.bundle.min.js
-->
<script src="/bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>

<script src="/js/javascript-auth.js?v=<?php echo time();?>"></script>

<!-- JS GENERAL-->
<script src="/js/script-general.js?v=<?php echo time();?>"></script>
<script src="/js/script-modal-general-global.js?v=<?php echo time();?>"></script>
<script src="/js/script-response-global.js?v=<?php echo time();?>"></script>
<script src="/js/instrumento-script-registro.js?v=<?php echo time();?>"></script>



</body>
