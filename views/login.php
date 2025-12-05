<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login Hacker</title>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      background-color: #000;
      color: #00ff00;
      font-family: monospace;
      display: flex;
      align-items: center;
      justify-content: center;
      height: 100vh;
    }

    .login-container {
      width: 90%;
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
      padding: 10px;
      margin-bottom: 15px;
      border: 1px solid #00ff00;
      background-color: black;
      color: #00ff00;
      font-weight: bold;
    }

    input[type="submit"] {
      width: 100%;
      padding: 10px;
      background-color: black;
      border: 2px solid #00ff00;
      color: #00ff00;
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
      }
    @media (max-width: 400px) {
      .login-container {
        padding: 15px;
      }

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
      <label for="username">Usuario</label>
      <input type="text" id="username" name="username"  value="" required />

      <label for="password">Contraseña</label>
      <input type="password" id="password" name="password" value="" required />
    
      <input type="submit" value="Iniciar sesión" />
    </form>
     <div id="message" id="mensaje"></div>
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
            credentials: 'include', 
            body: JSON.stringify(data)
        
          })
          .then(function(response) {

            return response.json().then(function(json) {
              //console.log(json);
              if (response.ok && json.status === 'success') {
                //document.getElementById('message').textContent = json.usuario;
                window.location.href = '/dashboard';
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
