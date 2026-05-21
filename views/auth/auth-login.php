
<button id="btnAbrirLogin"
			class="btn btn-outline-dark fw-bold px-4 py-2"
			style="
			  position: fixed;
			  top: 20px;
			  right: 20px;
			  z-index: 9999;
			  font-size: 1.5rem;
			"
			data-bs-toggle="modal"
			data-bs-target="#modalLogin">
	  Login
	</button>

<!-- Modal login oculto -->
<div class="modalLogin" id="loginModal">
  <div class="modal-content-login">
    <button type="button" class="btn-close-modal" id="btnCerrarLogin" aria-label="Cerrar">×</button>

    <h2>== LOGIN REQUERIDO AUTH ==</h2>

    <form id="modalLoginForm" method="POST">
      <label for="username">Usuario</label>
      <input type="text" id="username" name="username" value="" required />

      <label for="password">Contraseña</label>
      <input type="password" id="password" name="password" value="" required />

      <input type="submit" value="Iniciar sesión" />
    </form>

    <div id="mensajeModal"></div>
  </div>
</div>
