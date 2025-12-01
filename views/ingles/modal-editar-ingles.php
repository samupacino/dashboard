<!-- Modal EDITAR -->
<div class="modal fade" id="modalEnVocabEdit" tabindex="-1" aria-labelledby="modalEnVocabEditLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <form id="form-en-vocab-edit" class="needs-validation" novalidate method="post" action="#">

        <div class="modal-header">
          <h5 class="modal-title" id="modalEnVocabEditLabel">Editar palabra / expresión</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>

        <div class="modal-body">
          <div class="row g-3">

            <!-- ID (oculto) -->
            <input type="hidden" name="id" id="id_edit">

            <!-- English -->
            <div class="col-12 col-lg-6">
              <label for="english_edit" class="form-label">English <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="english_edit" name="english"
                     maxlength="120" required placeholder="Go up">
              <div class="invalid-feedback">Ingresa la palabra/frase en inglés.</div>
            </div>

            <!-- Pronunciation -->
            <div class="col-12 col-lg-6">
              <label for="pronunciation_edit" class="form-label">Pronunciation</label>
              <input type="text" class="form-control" id="pronunciation_edit" name="pronunciation"
                     maxlength="120" placeholder="gou ap">
              <div class="form-text">Fonética aproximada.</div>
            </div>

            <!-- Spanish -->
            <div class="col-12 col-lg-6">
              <label for="spanish_edit" class="form-label">Spanish <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="spanish_edit" name="spanish"
                     maxlength="180" required placeholder="Subir">
              <div class="invalid-feedback">Ingresa la traducción al español.</div>
            </div>

            <!-- POS -->
            <div class="col-12 col-lg-6">
              <label for="pos_edit" class="form-label">Part of Speech (POS) <span class="text-danger">*</span></label>
              <select class="form-select" id="pos_edit" name="pos" required>
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
              <label for="level_edit" class="form-label">Level (CEFR)</label>
              <select class="form-select" id="level_edit" name="level">
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
              <label for="example_en_edit" class="form-label">Example (EN)</label>
              <input type="text" class="form-control" id="example_en_edit" name="example_en"
                     maxlength="240" placeholder="Prices go up in summer.">
            </div>

            <!-- Example ES -->
            <div class="col-12 col-lg-6">
              <label for="example_es_edit" class="form-label">Ejemplo (ES)</label>
              <input type="text" class="form-control" id="example_es_edit" name="example_es"
                     maxlength="240" placeholder="Los precios suben en verano.">
            </div>

            <!-- Notes -->
            <div class="col-12">
              <label for="notes_edit" class="form-label">Notes</label>
              <textarea class="form-control" id="notes_edit" name="notes"
                        rows="2" maxlength="240" placeholder="directions, time, etc."></textarea>
            </div>

            <!-- Opposite: búsqueda + lista + hidden -->
            <div class="col-12">
              <label class="form-label d-block">Opposite (buscar y seleccionar)</label>
              <div class="row g-2 align-items-start">
                <div class="col-12 col-md-6">
                  <input type="text" class="form-control" id="opposite_query_edit"
                         placeholder="Escribe para buscar: Go down, Come out…"
                         aria-describedby="helpOppositeEdit">
                  <div id="helpOppositeEdit" class="form-text">
                    Escribe parte del inglés y luego elige.
                  </div>
                </div>
                <div class="col-12 col-md-6">
                  <select class="form-select" id="opposite_list_edit" size="6"
                          aria-describedby="oppositeInvalidEdit" title="Resultados">
                    <!-- opciones dinámicas -->
                  </select>
                  <div id="oppositeInvalidEdit" class="invalid-feedback">
                    Selecciona un opuesto válido o deja vacío.
                  </div>
                </div>
              </div>
              <!-- hidden con el id real -->
              <input type="hidden" id="opposite_id_edit" name="opposite_id">
            </div>

            <!-- Source -->
            <div class="col-12 col-lg-6">
              <label for="source_edit" class="form-label">Source</label>
              <input type="text" class="form-control" id="source_edit" name="source"
                     maxlength="120" placeholder="libreta, foto, app…">
            </div>

            <!-- Created/Updated -->
            <div class="col-12 col-lg-3">
              <label class="form-label" for="created_at_edit">Created At</label>
              <input type="text" class="form-control" id="created_at_edit" value="(auto)" disabled>
            </div>
            <div class="col-12 col-lg-3">
              <label class="form-label" for="updated_at_edit">Updated At</label>
              <input type="text" class="form-control" id="updated_at_edit" value="(auto)" disabled>
            </div>

          </div>
        </div>

        <div class="modal-footer">
          <button type="button" id="btnCancelEnVocabEdit" class="btn btn-outline-secondary">
            Cancelar
          </button>
          <button type="submit" class="btn btn-primary">ACTUALIZAR</button>
        </div>
      </form>
    </div>
  </div>
</div>
