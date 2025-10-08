
function limpiarModalesAnteriores() {
  // Buscar todos los modales existentes en el DOM
  document.querySelectorAll(".modal").forEach(modalEl => {
    // Si había una instancia activa, destruirla
    const instance = bootstrap.Modal.getInstance(modalEl);
    if (instance) {
      console.log(modalEl);
      instance.dispose();
    }
    // Quitar el HTML del modal
    modalEl.remove();
  });
}
function cargarVistaModulo(nombreModulo) {

  limpiarModalesAnteriores();
  

  // Paso 0: Obtenemos el contenedor principal donde se insertará la vista
  const contenedor = document.getElementById('contenido-principal');
  
// Paso 1: Mostrar un mensaje o spinner mientras se carga la vista
  contenedor.innerHTML = `
    <div class="vista-cargando" style="text-align:center; padding:2rem;">
      <div class="spinner" style="
        margin: 1rem auto;
        width: 40px;
        height: 40px;
        border: 5px solid #ccc;
        border-top-color: #333;
        border-radius: 50%;
        animation: spin 0.8s linear infinite;">
      </div>
      <p style="font-weight:bold;">Cargando módulo <em>${nombreModulo}</em>...</p>
    </div>
  `;

  setTimeout(()=>{

  
  // Paso 2: Construimos la ruta del backend para obtener la vista
  const rutaVista = `/dashboard/${nombreModulo}`;

  // Paso 3: Enviamos petición AJAX (fetch) para cargar el HTML de la vista
    fetch(rutaVista)
    .then(response => {
      // Paso 4: Si la respuesta del servidor no fue exitosa (401, 403, 404, etc)
      if (!response.ok) {
        // Convertimos la respuesta en JSON (esperamos que tenga la propiedad "error")
        return response.json().then(errorData => {
          // Lanzamos un error personalizado para el catch
          throw new Error(errorData.error || 'Error desconocido al cargar la vista');
        });
      }

      // Paso 5: Si todo bien, extraemos el HTML como texto plano
      return response.text();
    })
    .then(html => {


       //destruirTodasLasDataTables();

      // Paso 6: Reemplazamos el contenido anterior por el nuevo HTML cargado
      contenedor.innerHTML = html;
    

      // Paso 7: Generamos el ID del <script> JS que se encargará del módulo
      // Ej: para usuario → script-usuario
      const scriptId = `script-${nombreModulo}`;

      // Paso 8: Verificamos si el script de este módulo ya fue cargado antes
      if (!document.getElementById(scriptId)) {
      
        // Paso 8.1: Si no fue cargado, creamos dinámicamente una nueva etiqueta <script>

        // Añadimos un timestamp para evitar que el navegador use la versión cacheada
        const timestamp = new Date().getTime();

        const script = document.createElement('script');
        script.src = `/js/script-${nombreModulo}.js?v=${timestamp}`; // nombre y cache busting
        script.id = scriptId;
    
        // Paso 8.2: Esperamos que el script JS se cargue completamente antes de usarlo
        script.onload = () => {
          // Buscamos la función de inicialización en el objeto global `window`
          const funcionInit = window[`cargar_${nombreModulo}_init`];

          // Paso 8.3: Ejecutamos la función si existe
          if (typeof funcionInit === 'function') {
            funcionInit();
            //inicializarVista();
         
         
          } else {
            console.error(`No se encontró la función cargar_${nombreModulo}_init`);
          }
        };

        // Paso 8.4: Insertamos el script dinámicamente en el <body> del documento
       
        document.body.appendChild(script);
      } else {


        // Paso 9: El script ya estaba cargado antes. Solo ejecutamos su función de init
        const funcionInit = window[`cargar_${nombreModulo}_init`];
        const funcionrecarga = window[`recargar_tabla`];
        if (typeof funcionInit === 'function') {
          funcionInit();
          login_obligado();

        } else {
          console.error(`No se encontró la función cargar_${nombreModulo}_init`);
        }
      }
    })
    .catch(error => {
      // Paso 10: Si hubo algún error, lo mostramos dentro del contenedor principal
      contenedor.innerHTML = `
        <div class="vista-error" style="color: red; text-align: center; padding: 2rem;">
          <h1>PTM</h1>
          <strong>Error:</strong> ${error.message}
        </div>
      `;
    });
  },10); 
}

function destruirTodasLasDataTables() {
  // Paso 1: Verifica si existe el objeto global
  if (!window.dataTables) return;

  // Paso 2: Iterar sobre cada clave (nombre del módulo)
  for (const nombreModulo in window.dataTables) {
    const instancia = window.dataTables[nombreModulo];

    // Paso 3: Si es instancia válida, destruirla
    if (instancia instanceof DataTable) {
      instancia.destroy();
    }

    // Paso 4: Eliminar la referencia
    delete window.dataTables[nombreModulo];
  }

  // (Opcional) Limpieza extra
  // window.dataTables = {};
}
