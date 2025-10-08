
      


/*
setInterval(() => {
  // 1. Hacemos la solicitud al servidor
  fetch('/login/check-session')
    .then(response => {
      // 2. Guardamos la respuesta HTTP antes de convertirla
      const resultado = {
        status: response.status,     // estado HTTP (200, 401, etc.)
        ok: response.ok,             // true si es 2xx, false si no
        headers: response.headers,   // cabeceras si quieres usar después
        url: response.url            // URL solicitada
      };

      // 3. Convertimos a JSON y añadimos el cuerpo a resultado
      return response.json().then(data => {
        resultado.body = data;      // guardamos el JSON en resultado.body
        return resultado;           // devolvemos el objeto completo
      });
    })
    .then(resultado => {
     
      // 4. Usamos el resultado completo
      console.log('Estado HTTP:', resultado.status);
      console.log('¿OK?', resultado.ok);
      console.log('Datos JSON del servidor:', resultado.body);

      // 5. Verificamos si la sesión expiró
      if (
        resultado.status === 401 &&
        resultado.body?.status === 'unauthorized' &&
        resultado.body?.mensaje === 'Sesión expirada'
      ) {
        // Mostrar modal de login
        loginModal.style.display = 'flex';
      }
    })
    .catch(error => {
      console.error('Error de red o servidor:', error);
    });
}, 70000);*/




//  CODIGO ANTERIOR Y SIRVE

    // Verificar cada 30 segundos si la sesión sigue activa
/* setInterval(() => {
  fetch('/login/check-session')
    .then(res => {

   
      if (res.status === 401) {
        loginModal.style.display = 'flex'; // Mostrar modal si la sesión expiró
      } else {
        res.json().then(data => {
          console.log(data); // Mostrar contenido si la sesión está activa
        });
      }
    })
    .catch(err => {
      console.error('Error al verificar la sesión:', err);
    });
}, 67000); // Cada 3 segundos

 */

    // Verificar cada 30 segundos si la sesión sigue activa
/* setInterval(() => {
  fetch('/login/check-session')
    .then(res => response.json())
    .then(response => {
        console.log(response.status);
    })
    .catch(err => {
      console.error('Error al verificar la sesión:', err);
    });
}, 2000); // Cada 3 segundos*/



function login_obligado(){

    const loginModal = document.getElementById('loginModal');
    const usuario = document.getElementById('username');
    const clave = document.getElementById('password');
    const mensaje = document.getElementById('mensajeModal');   
    document.getElementById('modalLoginForm').addEventListener('submit', function (e) {
      e.preventDefault();

      //alert(usuario.value);
      const formData = new FormData(this);
      const data = Object.fromEntries(formData.entries());
      //console.log(data);
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
                 
                 loginModal.style.display = 'none';
            
          
              } else {
                  document.getElementById('mensajeModal').textContent = json.error;
              }
            });

          })
    
      
        });
        usuario.addEventListener('input', () => mensaje.textContent = '');
        clave.addEventListener('input', () => mensaje.textContent = '');
}

