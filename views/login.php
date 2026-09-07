<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">

  <meta name="viewport"
        content="width=device-width, initial-scale=1.0">

  <title>Login</title>

  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    html,
    body {
      width: 100%;
      min-height: 100%;
    }

    body {
      background-color: #000;
      color: #00ff00;
      font-family: monospace;

      display: flex;
      align-items: center;
      justify-content: center;

      min-height: 100vh;
      padding: 20px;
    }

    .login-container {
      width: 100%;
      max-width: 400px;

      border: 2px solid #00ff00;
      padding: 20px;
    }

    h1 {
      text-align: center;
      margin-bottom: 20px;
      font-weight: bold;
    }

    label {
      display: block;
      margin-bottom: 5px;
      font-weight: bold;
    }

    input[type="text"],
    input[type="password"] {
      width: 100%;

      padding: 12px;
      margin-bottom: 15px;

      border: 1px solid #00ff00;
      background-color: #000;
      color: #00ff00;

      font-family: monospace;
      font-size: 16px;
      font-weight: bold;

      outline: none;
    }

    input[type="text"]:focus,
    input[type="password"]:focus {
      border: 2px solid #00ff00;
    }

    input[type="submit"] {
      width: 100%;

      padding: 12px;

      background-color: #000;
      border: 2px solid #00ff00;
      color: #00ff00;

      font-family: monospace;
      font-size: 16px;
      font-weight: bold;

      cursor: pointer;
    }

    input[type="submit"]:hover {
      background-color: #001100;
    }

    #message {
      margin-top: 15px;
      font-weight: bold;
      color: #00ff00;
      text-align: center;
      min-height: 20px;
    }

    /* Celular */
    @media (max-width: 480px) {

      body {
        padding: 15px;
        align-items: center;
      }

      .login-container {
        padding: 18px;
      }

      h1 {
        font-size: 1.6rem;
      }

      input[type="text"],
      input[type="password"],
      input[type="submit"] {
        font-size: 16px;
      }
    }
  </style>
</head>

<body>

  <div class="login-container">

    <h1>== LOGIN ==</h1>

    <form action="" method="post" id="loginForm">

      <label for="username">
        Usuario
      </label>

      <input
        type="text"
        id="username"
        name="username"
        required
      >

      <label for="password">
        Contraseña
      </label>

      <input
        type="password"
        id="password"
        name="password"
        required
      >

      <input
        type="submit"
        value="Iniciar sesión"
      >

    </form>

    <div id="message"></div>

  </div>


  <script>
           
    window.addEventListener('load',function(){

    const usuario = document.getElementById('username');
    const clave = document.getElementById('password');
    const mensaje = document.getElementById('message');    

    document.getElementById('loginForm').addEventListener('submit', function (e) {
      e.preventDefault();

      const formData = new FormData(this);
      const data = Object.fromEntries(formData.entries());


          fetch('/login', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'same-origin', 
            body: JSON.stringify(data)
        
          })
          .then(function(response) {

            return response.json().then(function(json) {
              console.log(json);
              if (response.ok && json.status === 'success') {
              
                document.getElementById('message').textContent = 'Acceso concedido. Redirigiendo...';
                setTimeout(() => {
					
					
                	window.location.href = '/dashboard';
				
				}, 1000);
               
              } else {
                document.getElementById('message').textContent = json.mensaje;
              }
            });

          })
    
      
        });
        
        usuario.addEventListener('input', () => mensaje.textContent = '');
        clave.addEventListener('input', () => mensaje.textContent = '');

      });


    </script>
</body>
</html>
