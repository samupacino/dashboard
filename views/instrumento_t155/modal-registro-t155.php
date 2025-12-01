<div class="modal fade modal-dinamico" id="modal_registro_t155" tabindex="-1" aria-labelledby="modalRegistroT155Label" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content border-0 shadow-lg rounded-3">
      
      <!-- Encabezado gris -->
      <div class="modal-header bg-secondary-subtle border-bottom">
        <h5 class="modal-title fw-bold text-dark" id="modalRegistroT155Label">
          <i class="bi bi-pencil-square me-2 text-secondary"></i> Registro de Instrumento T155
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>

      <!-- Cuerpo -->
      <div class="modal-body bg-light">
        <form id="formInstrumentoT155_registro" class="needs-validation" novalidate>
          
          <div class="mb-3">
            <label for="tag_t155" class="form-label fw-semibold text-secondary">Tag del instrumento</label>
            <input 
              type="text"
              class="form-control"
              id="tag_t155"
              name="tag"
              maxlength="100"
              required
              placeholder="Ingrese el Tag del instrumento">
            <div class="invalid-feedback">Por favor ingrese el TAG.</div>
          </div>

          <div class="mb-3">
            <label for="plataforma_t155" class="form-label fw-semibold text-secondary">Plataforma</label>
            <select class="form-select" id="plataforma_t155" name="plataforma" required>
              <option value="">Seleccione una plataforma...</option>
              <!-- Opciones dinámicas se llenan con fetch -->
              <option value="1">Plataforma 1</option>
              <option value="2">Plataforma 2</option>
              <option value="3">Plataforma 3</option>
              <option value="4">Plataforma 4</option>
              <option value="5">Plataforma 5</option>
              <option value="6">Plataforma 6</option>
              <option value="7">Plataforma 7</option>
              <option value="8">Base</option>
            </select>
            <div class="invalid-feedback">Seleccione una plataforma válida.</div>
          </div>

        </form>

        <!-- Mensaje dinámico -->
        <div id="mensajeModalT155" class="mt-2"></div>
      </div>

      <!-- Pie -->
      <div class="modal-footer bg-body-tertiary border-top">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
          <i class="bi bi-x-circle"></i> Cerrar
        </button>
        <button type="button" class="btn btn-dark" id="modal_registro_t155_confirmar">
          <i class="bi bi-save"></i> Guardar Registro
        </button>
      </div>
    </div>
  </div>
</div>


