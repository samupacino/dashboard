

<div class="modal fade" id="modal_editar_instrumento" tabindex="-1" aria-labelledby="modalEditarInstrumentoLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">

      <div class="modal-header bg-warning-subtle">
        <h5 class="modal-title fw-bold" id="modalEditarInstrumentoLabel">Editar instrumento</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <form id="form_editar_instrumento" enctype="multipart/form-data">

          <!-- ID oculto -->
          <input type="hidden" id="edit_id" name="id">

          <!-- Campo oculto para quitar foto -->
          <input type="hidden" id="edit_quitar_foto" name="quitar_foto" value="0">

          <div class="row g-3">
            
            <div class="col-md-6">
				<label for="edit_tag" class="form-label semibold">Tag</label>
				<input 
					type="text" 
					class="form-control" 
					id="edit_tag" 
					maxlength="50"
					name="tag"
					required>
				</input>
			</div>
            
        

            <!-- ESTADO -->
            <div class="col-md-6">
              <label for="edit_estado" class="form-label fw-semibold">Estado</label>
              <select class="form-select" id="edit_estado" name="estado" required>
                <option value="">Seleccione</option>
                <option value="activo">Activo</option>
                <option value="inactivo">Inactivo</option>
              </select>
            </div>

            <!-- DESCRIPCION -->
            <div class="col-12">
              <label for="edit_descripcion" class="form-label fw-semibold">Descripción</label>
              <input
                type="text"
                class="form-control"
                id="edit_descripcion"
                name="descripcion"
                maxlength="150"
                required
              >
            </div>

            <!-- TIPO -->
            <div class="col-md-4">
              <label for="edit_tipo" class="form-label fw-semibold">Tipo</label>
              <input
                type="text"
                class="form-control"
                id="edit_tipo"
                name="tipo"
                maxlength="50"
                required
              >
            </div>

            <!-- PLANTA -->
            <div class="col-md-4">
              <label for="edit_planta" class="form-label fw-semibold">Planta</label>
              <input
                type="text"
                class="form-control"
                id="edit_planta"
                name="planta"
                maxlength="50"
                required
              >
            </div>

            <!-- AREA -->
            <div class="col-md-4">
              <label for="edit_area" class="form-label fw-semibold">Área</label>
              <input
                type="text"
                class="form-control"
                id="edit_area"
                name="area"
                maxlength="100"
                required
              >
            </div>

            <!-- UBICACION EXACTA -->
            <div class="col-12">
              <label for="edit_ubicacion_exacta" class="form-label fw-semibold">Ubicación exacta</label>
              <textarea
                class="form-control"
                id="edit_ubicacion_exacta"
                name="ubicacion_exacta"
                rows="3"
                required
              ></textarea>
            </div>

            <!-- FOTO ACTUAL -->
            <div class="col-12">
              <label class="form-label fw-semibold">Foto actual</label>

              <div class="border rounded p-2 text-center bg-light" id="contenedor_foto_actual">
                <img
                  id="edit_preview_actual"
                  src=""
                  alt="Foto actual"
                  class="img-fluid rounded d-none"
                  style="max-height: 220px;"
                >
                <div id="edit_sin_foto_actual" class="text-muted">
                  Sin foto registrada
                </div>
              </div>
            </div>

            <!-- NUEVA FOTO -->
            <div class="col-12">
              <label for="edit_foto" class="form-label fw-semibold">Nueva foto</label>
              <input
                type="file"
                class="form-control"
                id="edit_foto"
                name="foto"
                accept="image/*"
              >
              <div class="form-text">
                Si selecciona una nueva imagen, reemplazará la foto actual.
              </div>
            </div>

            <!-- PREVIEW NUEVA FOTO -->
            <div class="col-12">
              <div class="border rounded p-2 text-center bg-light d-none" id="contenedor_preview_nueva">
                <img
                  id="edit_preview_nueva"
                  src=""
                  alt="Nueva foto"
                  class="img-fluid rounded"
                  style="max-height: 220px;"
                >
              </div>
            </div>

            <!-- QUITAR FOTO -->
            <div class="col-12">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="check_quitar_foto">
                <label class="form-check-label" for="check_quitar_foto">
                  Quitar foto actual
                </label>
              </div>
            </div>

            <!-- OBSERVACION -->
            <div class="col-12">
              <label for="edit_observacion" class="form-label fw-semibold">Observación</label>
              <textarea
                class="form-control"
                id="edit_observacion"
                name="observacion"
                rows="3"
              ></textarea>
            </div>

          </div>
        </form>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
          Cerrar
        </button>
        <button type="button" class="btn btn-primary" id="modal_editar_confirmar">
          Guardar cambios
        </button>
      </div>

    </div>
  </div>
</div>


