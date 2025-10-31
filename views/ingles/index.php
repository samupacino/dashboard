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


 <!-- Botón para abrir modal -->
<div class="container my-4">
  <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalEnVocab" onclick="openEnVocabModal()">Nueva palabra</button>
</div>

<!-- Modal -->
<div class="modal fade" id="modalEnVocab" tabindex="-1" aria-labelledby="modalEnVocabLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <form id="form-en-vocab" class="needs-validation" novalidate method="post" action="/en_vocab/save">
        <div class="modal-header">
          <h5 class="modal-title" id="modalEnVocabLabel">Registrar palabra / expresión</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>

        <div class="modal-body">
          <div class="row g-3">

            <input type="hidden" name="id" id="id">

            <!-- English -->
            <div class="col-12 col-lg-6">
              <label for="english" class="form-label">English <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="english" name="english" maxlength="120" required placeholder="Go up">
              <div class="invalid-feedback">Ingresa la palabra/frase en inglés.</div>
            </div>

            <!-- Pronunciation -->
            <div class="col-12 col-lg-6">
              <label for="pronunciation" class="form-label">Pronunciation</label>
              <input type="text" class="form-control" id="pronunciation" name="pronunciation" maxlength="120" placeholder="gou ap">
              <div class="form-text">Fonética aproximada.</div>
            </div>

            <!-- Spanish -->
            <div class="col-12 col-lg-6">
              <label for="spanish" class="form-label">Spanish <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="spanish" name="spanish" maxlength="180" required placeholder="Subir">
              <div class="invalid-feedback">Ingresa la traducción al español.</div>
            </div>

            <!-- POS -->
            <div class="col-12 col-lg-6">
              <label for="pos" class="form-label">Part of Speech (POS) <span class="text-danger">*</span></label>
              <select class="form-select" id="pos" name="pos" required>
                <option value="" selected disabled>Selecciona un tipo…</option>
                <option value="verb">verb</option>
                <option value="phrasal_verb">phrasal_verb</option>
                <option value="noun">noun</option>
                <option value="adjective">adjective</option>
                <option value="adverb">adverb</option>
                <option value="expression">expression</option>
              </select>
              <div class="invalid-feedback">Selecciona el tipo gramatical.</div>
            </div>

            <!-- Level -->
            <div class="col-12 col-lg-6">
              <label for="level" class="form-label">Level (CEFR)</label>
              <select class="form-select" id="level" name="level">
                <option value="" selected>— sin nivel —</option>
                <option value="A1">A1</option>
                <option value="A2">A2</option>
                <option value="B1">B1</option>
                <option value="B2">B2</option>
                <option value="C1">C1</option>
                <option value="C2">C2</option>
              </select>
              <div class="form-text">Opcional: nivel de dificultad.</div>
            </div>

            <!-- Example EN -->
            <div class="col-12 col-lg-6">
              <label for="example_en" class="form-label">Example (EN)</label>
              <input type="text" class="form-control" id="example_en" name="example_en" maxlength="240" placeholder="Prices go up in summer.">
            </div>

            <!-- Example ES -->
            <div class="col-12 col-lg-6">
              <label for="example_es" class="form-label">Ejemplo (ES)</label>
              <input type="text" class="form-control" id="example_es" name="example_es" maxlength="240" placeholder="Los precios suben en verano.">
            </div>

            <!-- Notes -->
            <div class="col-12">
              <label for="notes" class="form-label">Notes</label>
              <textarea class="form-control" id="notes" name="notes" rows="2" maxlength="240" placeholder="directions, time, etc."></textarea>
            </div>

            <!-- Opposite: búsqueda + lista + hidden -->
            <div class="col-12">
              <label class="form-label d-block">Opposite (buscar y seleccionar)</label>
              <div class="row g-2 align-items-start">
                <div class="col-12 col-md-6">
                  <input type="text" class="form-control" id="opposite_query"
                         placeholder="Escribe para buscar: Go down, Come out…"
                         aria-describedby="helpOpposite">
                  <div id="helpOpposite" class="form-text">Escribe parte del inglés y luego elige.</div>
                </div>
                <div class="col-12 col-md-6">
                  <select class="form-select" id="opposite_list" size="6" aria-describedby="oppositeInvalid" title="Resultados">
                    <!-- opciones dinámicas -->
                  </select>
                  <div id="oppositeInvalid" class="invalid-feedback">Selecciona un opuesto válido o deja vacío.</div>
                </div>
              </div>
              <input type="hidden" id="opposite_id" name="opposite_id">
            </div>

            <!-- Source -->
            <div class="col-12 col-lg-6">
              <label for="source" class="form-label">Source</label>
              <input type="text" class="form-control" id="source" name="source" maxlength="120" placeholder="libreta, foto, app…">
            </div>

            <!-- Created/Updated -->
            <div class="col-12 col-lg-3">
              <label class="form-label">Created At</label>
              <input type="text" class="form-control" id="created_at" value="(auto)" disabled>
            </div>
            <div class="col-12 col-lg-3">
              <label class="form-label">Updated At</label>
              <input type="text" class="form-control" id="updated_at" value="(auto)" disabled>
            </div>

          </div>
        </div>

        <div class="modal-footer">
          <button type="reset" class="btn btn-outline-secondary">Limpiar</button>
          <button type="submit" class="btn btn-primary">Guardar</button>
        </div>
      </form>
    </div>
  </div>
</div>


<script src="bootstrap-5.3.3-dist/js/bootstrap.bundle.min.js"></script>
<script src="/js/ingles.js?v=<?php echo time();?>"></script>


<script>
// Ejemplo básico de inicialización de modal
function openEnVocabModal(){
  const form = document.getElementById('form-en-vocab');
  if(form){
    form.reset();
    form.classList.remove('was-validated');
  }
}
</script>


</body>