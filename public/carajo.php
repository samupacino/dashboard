<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Registro Usuario</title>
  <style>
    /* Estilos base */
    body {
      font-family: Arial, sans-serif;
      margin: 0;
      padding: 0;
    }

    /* Modal principal de registro */
    .modal-registro-usuario {
      display: none;
      position: fixed;
      z-index: 1000;
      left: 0; top: 0;
      width: 100%; height: 100%;
      background: rgba(0,0,0,0.6);
      justify-content: center;
      align-items: center;
    }
    .modal-contenido-registro {
      background: #fff;
      padding: 20px;
      width: 90%;
      max-width: 400px;
      border-radius: 10px;
      position: relative;
    }
    .cerrar-modal-registro {
      position: absolute;
      top: 10px; right: 15px;
      font-size: 20px;
      cursor: pointer;
    }
    .modal-contenido-registro h2 {
      margin-top: 0;
      text-align: center;
    }
    .form-registro-usuario {
      display: flex;
      flex-direction: column;
      gap: 10px;
    }
    .form-registro-usuario input, 
    .form-registro-usuario select {
      padding: 8px;
      font-size: 14px;
      border: 1px solid #ccc;
      border-radius: 5px;
    }
    .form-registro-usuario button {
      padding: 10px;
      border: none;
      background: #007bff;
      color: white;
      border-radius: 5px;
      cursor: pointer;
    }
    .form-registro-usuario button:hover {
      background: #0056b3;
    }
    .mensaje-error-registro {
      color: red;
      font-size: 13px;
      margin-top: 5px;
    }

    /* Modal de confirmación */
    .modal-confirmar-registro {
      display: none;
      position: fixed;
      z-index: 2000;
      left: 0; top: 0;
      width: 100%; height: 100%;
      background: rgba(0,0,0,0.6);
      justify-content: center;
      align-items: center;
    }
    .modal-contenido-confirmar {
      background: #fff;
      padding: 20px;
      border-radius: 10px;
      width: 300px;
      text-align: center;
    }
    .modal-contenido-confirmar button {
      margin: 5px;
      padding: 8px 15px;
      border: none;
      border-radius: 5px;
      cursor: pointer;
    }
    .btn-si-registro {
      background: #28a745;
      color: white;
    }
    .btn-no-registro {
      background: #dc3545;
      color: white;
    }
  </style>
</head>
<body>

  <button onclick="abrirModalRegistro()">Abrir Registro Usuario</button>

  <!-- Modal Registro -->
  <div id="modalRegistroUsuario" class="modal-registro-usuario">
    <div class="modal-contenido-registro">
      <span class="cerrar-modal-registro" onclick="cerrarModalRegistro()">&times;</span>
      <h2>Registro Usuario</h2>
      <form id="formRegistroUsuario" class="form-registro-usuario">
        <input type="text" id="usernameRegistro" name="username" placeholder="Usuario" required>
        <input type="text" id="nameCompleteRegistro" name="name_complete" placeholder="Nombre completo" required>
        <input type="password" id="passwordRegistro" name="password" placeholder="Contraseña" required>
        <select id="rolRegistro" name="rol">
          <option value="invitado">Invitado</option>
          <option value="admin">Admin</option>
        </select>
        <button type="submit">Registrar</button>
        <div id="errorRegistro" class="mensaje-error-registro"></div>
      </form>
    </div>
  </div>

  <!-- Modal Confirmar -->
  <div id="modalConfirmarRegistro" class="modal-confirmar-registro">
    <div class="modal-contenido-confirmar">
      <p>¿Deseas registrar este usuario?</p>
      <button class="btn-si-registro" onclick="confirmarRegistro()">Sí</button>
      <button class="btn-no-registro" onclick="cerrarModalConfirmar()">No</button>
    </div>
  </div>

  <script>
    const modalRegistro = document.getElementById('modalRegistroUsuario');
    const modalConfirmar = document.getElementById('modalConfirmarRegistro');
    const formRegistro = document.getElementById('formRegistroUsuario');
    const errorRegistro = document.getElementById('errorRegistro');

    function abrirModalRegistro() {
      modalRegistro.style.display = 'flex';
    }
    function cerrarModalRegistro() {
      modalRegistro.style.display = 'none';
      formRegistro.reset();
      errorRegistro.textContent = '';
    }
    function cerrarModalConfirmar() {
      modalConfirmar.style.display = 'none';
    }

    // Abrir modal confirmación antes de enviar
    formRegistro.addEventListener('submit', function(e) {
      e.preventDefault();
      modalConfirmar.style.display = 'flex';
    });

    // Confirmar envío
    function confirmarRegistro() {
      cerrarModalConfirmar();

      const data = {
        username: document.getElementById('usernameRegistro').value,
        name_complete: document.getElementById('nameCompleteRegistro').value,
        password: document.getElementById('passwordRegistro').value,
        rol: document.getElementById('rolRegistro').value
      };

      // Simulación fetch al servidor
      fetch('/registrar_usuario', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
      })
      .then(res => {
        if (!res.ok) throw new Error("Error en el servidor");
        return res.json();
      })
      .then(respuesta => {
        alert("Usuario registrado con éxito");
        cerrarModalRegistro();
      })
      .catch(err => {
        errorRegistro.textContent = err.message;
      });
    }

    // Borrar error al escribir
    formRegistro.querySelectorAll("input, select").forEach(input => {
      input.addEventListener("input", () => {
        errorRegistro.textContent = "";
      });
    });
  </script>
</body>
</html>
