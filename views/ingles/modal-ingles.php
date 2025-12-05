

<!-- Modal login oculto -->
<div class="modalLogin" id="loginModal">
  <div class="modal-content-login">
    <button type="button" class="btn-close-modal" id="btnCerrarLogin" aria-label="Cerrar">×</button>

    <h2>== LOGIN REQUERIDO ==</h2>

    <form id="modalLoginForm" method="POST">
      <label for="username">Usuario</label>
      <input type="text" id="username" name="username" value="samuel" required />

      <label for="password">Contraseña</label>
      <input type="password" id="password" name="password" value="12345" required />

      <input type="submit" value="Iniciar sesión" />
    </form>

    <div id="mensajeModal"></div>
  </div>
</div>
