
<div class="modal" id="modal_editar_t155" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">EDITAR</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">

        <form id="formInstrumentoT155_edit">
        
          <div class="mb-3">
            <input type="text" 
                  class="form-control" modal
                  id="tag_editar" 
                  name="tag" 
                  maxlength="100" 
                  required
                  placeholder="Ingrese el Tag del instrumento">
          </div>

          <div class="mb-3">
            <select class="form-select" id="plataforma_editar" name="plataforma" required>

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
          </div>

        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" id="modal_editar_t155_confirmar">Save Changes</button>
      </div>
    </div>
  </div>
</div>

