<!-- Modal Editar Usuario -->
<div id="modalEditarUsuario" class="modal-editar-usuario modalRegistro">
  <div class="modal-contenido-editar">
    <span class="cerrar-modal-editar" id="cerrarEditar">&times;</span>
    <h2>EDITAR USUARIO</h2>
    <form id="formEditarUsuario" class="form-editar-usuario" data-id="">
      <input type="text" id="usernameEditar" name="username" placeholder="Usuario" required>
      <input type="text" id="nameCompleteEditar" name="name_complete" placeholder="Nombre completo" required>
      <input type="password" id="passwordEditar" name="password" placeholder="Nueva contraseña (opcional)">
      <select id="rolEditar" name="rol">
        <option value="invitado">Invitado</option>
        <option value="admin">Admin</option>
      </select>
      <button type="submit">Actualizar</button>
      <div id="errorEditar" class="mensaje-error-editar"></div>
    </form>
  </div>
</div>

<!-- Modal Confirmar Edición -->
<div id="modalConfirmarEditar" class="modal-confirmar-editar modalRegistro">
  <div class="modal-contenido-confirmar">
    <p>¿Deseas guardar los cambios del usuario?</p>
    <button class="btn-si-editar" id="btnConfirmarEditarSi">Sí</button>
    <button class="btn-no-editar" id="btnConfirmarEditarNo">No</button>
  </div>
</div>
