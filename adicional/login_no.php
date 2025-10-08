
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <form method="POST" action="" id="loginForm">
        <input name="correo" value="admin@example.com" required>
        <input name="clave" type="password" value="12345"required>
        <button type="submit">Ingresar</button>
    </form>

<?php
/*
    $clave = '12345'; // Puedes cambiarla
$hash = password_hash($clave, PASSWORD_DEFAULT);
echo $hash;

INSERT INTO usuario (nombre, corre, clave)
VALUES ('Admin', 'admin@example.com', '$2y$10$Li.emtkj5vQArD5x/uzIkuoCq4xyMWPB4cf9qQUMm/GbmCbOfgIda');
*/
?>
    <script>
       
        window.addEventListener('load',function(){
          document.getElementById('loginForm').addEventListener('submit', function (e) {
          e.preventDefault();

          const formData = new FormData(this);
          const data = Object.fromEntries(formData.entries());

          fetch('api/login', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'include', 
            body: JSON.stringify(data)
          })
          .then(function(response) {

            return response.json().then(function(json) {
              console.log(json);
              if (response.ok && json.usuario) {
                
              window.location.href = '/dashboard';
              } else {
              //document.getElementById('error').textContent = json.error || 'Credenciales inválidas';
              }
            });

          })
    
      
        });

      });
    </script>
</body>
</html>