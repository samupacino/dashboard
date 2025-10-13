
  <!-- Modal Registro -->
  <div id="modalRegistroUsuario" class="modal-registro-usuario modalRegistro">
    <div class="modal-contenido-registro">
      <span class="cerrar-modal-registro" id="cerrarRegistro">&times;</span>
      <h2>REGISTRO USUARIO</h2>
      <form id="formRegistroUsuario" class="form-registro-usuario">
        <input type="text" id="usernameRegistro" name="username" placeholder="Usuario"  required>
        <input type="text" id="nameCompleteRegistro" name="name_complete" placeholder="Nombre completo"  required>
        <input type="password" id="passwordRegistro" name="password" placeholder="Contraseña"  required>
        <select id="rolRegistro" name="rol">
          <option value="invitado">Invitado</option>
          <option value="admin">Admin</option>
        </select>
        <button type="submit">Save Record</button>
        <div id="mensajeTablaMessage"></div>
        <div id="errorRegistro" class="mensaje-error-registro"></div>
      </form>
    </div>
  </div>

  <!-- Modal Confirmar -->
  <div id="modalConfirmarRegistro" class="modal-confirmar-registro modalRegistro">
    <div class="modal-contenido-confirmar">
      <p>¿Deseas registrar este usuario?</p>
      <button class="btn-si-registro" id="btnConfirmarSi">Sí</button>
      <button class="btn-no-registro" id="btnConfirmarNo">No</button>
    </div>
  </div>



