/*
2. Delegación de eventos (más elegante)
document.addEventListener('click', function (e) {
  if (e.target && e.target.id === 'btnNuevo') {
    alert('Botón detectado por delegación');
  }
});

*/


function cargar_usuario_init(){
	load_usuario_init();
	registro_usuario();
	editar_usuario_init();
	
}

function editar_usuario_init() {
	const modalEditar = document.getElementById('modalEditarUsuario');
	const modalConfirmar = document.getElementById('modalConfirmarEditar');
	const formEditar = document.getElementById('formEditarUsuario');
	const errorEditar = document.getElementById('errorEditar');

	const btnCerrar = document.getElementById('cerrarEditar');
	const btnSi = document.getElementById('btnConfirmarEditarSi');
	const btnNo = document.getElementById('btnConfirmarEditarNo');

	// Cerrar modal edición
	btnCerrar.addEventListener("click", () => {
		modalEditar.style.display = "none";
		formEditar.reset();
		errorEditar.textContent = "";
	});

	// Prevenir cierre haciendo clic fuera
	modalEditar.addEventListener("click", (e) => {
		if (e.target === modalEditar) {
			e.stopPropagation(); // evitar cierre
		}
	});

	// Confirmar edición → abre modal de confirmación
	formEditar.addEventListener("submit", (e) => {
		e.preventDefault();
		modalConfirmar.style.display = "flex";
	});

	// Confirmar "Sí" → enviar PUT
	btnSi.addEventListener("click", () => {
		modalConfirmar.style.display = "none";

		const formData = new FormData(formEditar);
		const data = Object.fromEntries(formData.entries());

		if (data.password === "") delete data.password;

		const userId = formEditar.dataset.idEditar; // Extraemos ID desde data-id

		fetch(`/usuarios/${userId}`, {
			method: 'PUT',
			headers: {
				'Content-Type': 'application/json'
			},
			body: JSON.stringify(data)
		})
		.then(response => {
			const contentType = response.headers.get("content-type");

			if (contentType && contentType.includes("application/json")) {
				return response.json().then(body => {

					if (!response.ok) throw { status: response.status, body: body };
						return { status: response.status, body: body };

				});
			} else {
				return response.text().then(texto => {
						if (!response.ok) throw { status: response.status, body: { mensaje: texto } };
						return { status: response.status, body: { mensaje: texto } };
					});
				}
		})
		.then(resultado => {
			
			recargar_table_usuario();
			mostrarMensajeEnDataTable(resultado.body.mensaje);
			formEditar.reset();
			modalEditar.style.display = "none";
			

		})
		.catch(err => {

			if (err.status === 401 && err.body?.status === 'session_expired') {
				loginModal.style.display = 'flex';
			} else if (err.status === 401 && err.body?.status === 'unauthorized') {
					mostrarErrorUSUARIO(err.body.mensaje);
			} else {
					mostrarErrorUSUARIO(err.body?.mensaje || "No se pudo conectar con el servidor");
			}
		});
	});

	// Confirmar "No"
	btnNo.addEventListener("click", () => {
		//formEditar.reset();
		errorEditar.textContent = "";
		modalConfirmar.style.display = "none";

		//modalEditar.style.display = "none";
	
		

	});

	// Limpiar errores al escribir
	formEditar.querySelectorAll("input, select").forEach(input => {
		input.addEventListener("input", () => {
			errorEditar.textContent = "";
		});
	});
}

function registro_usuario() {

		const modalRegistro = document.getElementById('modalRegistroUsuario');
		const modalConfirmar = document.getElementById('modalConfirmarRegistro');
		const formRegistro = document.getElementById('formRegistroUsuario');
		const errorRegistro = document.getElementById('errorRegistro');

		const btnAbrir = document.getElementById('btnAbrirRegistro');
		const btnCerrar = document.getElementById('cerrarRegistro');
		const btnSi = document.getElementById('btnConfirmarSi');
		const btnNo = document.getElementById('btnConfirmarNo');
	

		// Abrir modal registro
		btnAbrir.addEventListener("click", () => {
			
			modalRegistro.style.display = "flex";
		});

		// Cerrar modal registro
		btnCerrar.addEventListener("click", () => {
			modalRegistro.style.display = "none";
			formRegistro.reset();
			errorRegistro.textContent = "";
		});

		// Prevenir cierre haciendo clic fuera (se cierra solo con la X)
		modalRegistro.addEventListener("click", (e) => {
			if (e.target === modalRegistro) {
			e.stopPropagation(); // no cerrar
			}
		});

		// Abrir modal de confirmación antes de enviar
		formRegistro.addEventListener("submit", (e) => {
			e.preventDefault();
			modalConfirmar.style.display = "flex";
		});

		// Confirmar "Sí"
		btnSi.addEventListener("click", () => {

			modalConfirmar.style.display = "none";

			// Capturamos los datos del formulario HTML
			// FormData es una CLASE ESPECIAL del navegador que guarda pares clave → valor
			// (no es un objeto plano de JS, por eso no podemos acceder con formData.usuario)
			const formData = new FormData(formRegistro);

			// formData.entries() devuelve un ITERADOR con pares [clave, valor]
			// Ejemplo: [["username", "samuel"], ["password", "12345"]]
			// Este formato sí es compatible con Object.fromEntries()
			const data = Object.fromEntries(formData.entries());

			// Ahora 'data' es un OBJETO PLANO de JS (ej: { username: "samuel", password: "12345" })
			// Al ser objeto, ya podemos acceder a sus propiedades directo con data.username
			// y además convertirlo a JSON fácilmente con JSON.stringify

			fetch('/usuarios', {
				method: 'POST',
				headers: {
					// Indicamos al servidor que el cuerpo se envía como JSON
					'Content-Type': 'application/json'
				},
				// Convertimos el objeto JS a JSON para enviar en la petición
				body: JSON.stringify(data)
				})
			// ========================================
			// 📌 REGISTRAR USUARIO CON FETCH + MANEJO DE ERRORES
			// ========================================

			.then(response => {
				const contentType = response.headers.get("content-type");

				if (contentType && contentType.includes("application/json")) {

					return response.json().then(body => {
						if (!response.ok) {
							throw { status: response.status, body: body };
						}
						return { status: response.status, body: body };
					});
				} else {
					return response.text().then(texto => {
						if (!response.ok) {
							throw { status: response.status, body: { mensaje: texto } };
						}
						return { status: response.status, body: { mensaje: texto } };
					});
				}
			})
			.then(resultado => {
				//console.log("✅ Usuario registrado:", resultado.body);
				//alert(resultado.body.mensaje || "Usuario registrado correctamente");
				//mostrarSuccess(resultado.body.mensaje || "Usuario registrado correctamente");
				//mostrarMensajeTabla(resultado.body.mensaje || "Usuario registrado correctamente");
				mostrarMensajeEnModal(resultado.body.mensaje);
				formRegistro.reset();
				recargar_table_usuario();
			})
			.catch(err => {
				// ===============================================
				// 📌 NOTA SOBRE MANEJO DE ERRORES CON FETCH
				// ===============================================
				//
				// 1. Usamos `err.body?.status === 'unauthorized'` para detectar si el servidor respondió con un mensaje 
				//    estructurado indicando que la sesión ha expirado.
				//
				// 2. El operador `?.` (encadenamiento opcional) permite verificar `err.body.status` sin lanzar error 
				//    si `body` está undefined.
				//
				// 3. Si el error fue `unauthorized`, mostramos el modal de login.
				//    Si no, mostramos un `alert()` genérico con el mensaje del servidor.
				//
				// ===============================================
				if (err.status === 401 && err.body?.status === 'session_expired') {
				
					loginModal.style.display = 'flex';                   // [DOM] Mostramos modal de login si expiró sesión
					
		
				} else if (err.status === 401 && err.body?.status === 'unauthorized'){
					mostrarErrorUSUARIO(err.body.mensaje);

				} else {

					//console.error("❌ Error de red:", err);
					mostrarErrorUSUARIO(err.body?.mensaje ||"No se pudo conectar con el servidor");

				}
		
			});


		});

		// Confirmar "No"
		btnNo.addEventListener("click", () => {
				// Borrar error al escribir
			formRegistro.reset();
			modalConfirmar.style.display = "none";

			
		});

		// Borrar error al escribir
		formRegistro.querySelectorAll("input, select").forEach(input => {
			
			input.addEventListener("input", () => {
			errorRegistro.textContent = " ";
			});
		});
}

function recargar_table_usuario(){


	if (window.dataTables['usuario']?.destroy instanceof Function){
	
		window.dataTables['usuario'].ajax.reload();
	}
}

function load_usuario_init() {                                   // [APP] Tu función para inicializar la tabla

  	const tabla = document.getElementById('usuario')

	if (!tabla) {                                                  // [DOM] Verifica si existe
		console.warn('Tabla #usuario no encontrada en el DOM');      // [DOM] Mensaje en consola si no existe
		console.log("de fuera en init");
		return;                                                      // [DOM] Sale de la función
	}
	window.dataTables = window.dataTables || {};

	if (window.dataTables['usuario']?.destroy instanceof Function) {
		console.log("nuevo ingreso datatable");
    	window.dataTables['usuario'].destroy();
    	delete window.dataTables['usuario'];
	}
	
	//Si ya existe una instancia anterior, destruirla

	// === CREAR INSTANCIA DATATABLE ===
	 	const datatable_usuario = new DataTable(tabla, {                       // [DT] Constructor principal de DataTables
		//responsive: true,
		destroy:true ,
		processing: true,                                            // [DT] Muestra texto "Procesando..." durante AJAX
		serverSide: true,                                           // [DT] Paginación y filtros hechos en cliente
		pageLength: 5,                                               // [DT] Número de filas por página
		paging: true,                                                // [DT] Activa paginación
		lengthMenu: [5, 10, 25, 50],                                 // [DT] Opciones para elegir filas por página
		language: {                                                  // [DT] Traducciones de UI
			url: 'https://cdn.datatables.net/plug-ins/2.3.2/i18n/es-ES.json'
		},

		ajax: {                                                      // [DT] Bloque AJAX de DataTables
		url: '/usuarios',                                          // [DT] URL del backend para cargar datos


		dataSrc: function (json) {  // [DT] Función que transforma la respuesta JSON
			// Solo entra aquí si el servidor respondió 200 OK
			
		
			if (Array.isArray(json.data)) { // [APP] Validación de tu contrato de API
				return json.data;                                      // [DT] Devuelve array de datos para pintar filas
			}
			alert('Respuesta inesperada del servidor. No se puede construir la tabla.');          // [APP] Si llega 200 pero no es success → alerta
			return [];                                               // [DT] Devuelve vacío para no romper la tabla
		},

		error: function (response) {                               // [DT] Manejo de errores HTTP ≠ 200
			try {                                                    // [APP] Intentamos parsear respuesta
			const resultado = {
				status: response.status,                             // [APP] Código HTTP (400, 401, 500, etc.)
				body: JSON.parse(response.responseText)              // [APP] Convertimos body en objeto JS
			};
	
			if (resultado.status === 401 && resultado.body?.status === 'unauthorized') {
			
				loginModal.style.display = 'flex';                   // [DOM] Mostramos modal de login si expiró sesión
				
				return;                                              // [APP] Evitamos ejecutar alert después
			}

			alert(resultado.body?.mensaje || 'Error desconocido'); // [APP] Mostramos mensaje de error
			} catch (e) {
			console.error('Error parseando JSON de error:', e);    // [APP] Log de error si JSON no es válido
			alert('Error crítico de servidor');                    // [APP]
			}
		}
		},


		// === DEFINICIÓN DE COLUMNAS ===
		columns: [                                                   // [DT] Configuración de columnas de la tabla
			{ data: 'id', visible: false },                                            // [DT] Columna id (mapea campo id del JSON)
			{ data: 'username'},                                      // [DT] Columna username
			{ data: 'name_complete' },                                 // [DT] Columna nombre completo
			{ data: 'rol' },                                           // [DT] Columna rol

			{                                                          
			data: null,                                              // [DT] No usa un campo → fabricamos contenido
			orderable: false,                                        // [DT] No ordenable
			searchable: false,                                       // [DT] No filtrable
			/*
			🟡 ¿class="dt-center" es de Bootstrap?
				No. dt-center no es una clase de Bootstrap.
				Es una clase de DataTables, no de Bootstrap.
			*/
			className: 'dt-center',                                  // [DT] Centrado
			render: function (data, type, row, meta) {               // [DT] Firma reglamentaria (data, type, row, meta)
				// data = valor de la celda (null aquí)
				// type = 'display' | 'sort' | 'filter'
				// row  = objeto completo de la fila (ej: {id:1,...})
				// meta = info de índice de fila/columna
				//console.log(data);
				return (`<button type="button" class="btn-editar btn btn-secondary" data-id="${row.id}">Editar</button>`);
					/*
						// [DOM] Creamos botón HTML
						// [DOM] Atributo data-id con el id de la fila
						// [DOM] Texto visible del botón
					*/
				}
			},
			{
			data: null,                                              // [DT] Columna personalizada para botón eliminar
			orderable: false,                                        // [DT]
			searchable: false,                                       // [DT]
			className: 'dt-center',                                  // [DT]
			render: function (data, type, row, meta) {               // [DT]
			return (`<button type="button" class="btn-eliminar btn btn-danger" data-id="${row.id}">Eliminar</button>`);
				/*
								// [DOM] Botón eliminar
								// [DOM] Guardamos id en data-id
				            	// [DOM]
				*/
				
				}
			}
		],

		createdRow: function (row, data, dataIndex) {                // [DT] Callback al crear cada fila
			//console.log(data);
			row.dataset.rowId = data.id;                               // [DOM] Insertamos atributo data-row-id en el <tr>
		}
	});

  	// === DELEGACIÓN DE EVENTOS ===
  	const tbody = tabla.querySelector('tbody');                    // [DOM] Seleccionamos <tbody> de la tabla

	tbody.addEventListener('click', function (e) {                 // [DOM] Escuchamos clicks en todo el tbody
		const boton = e.target.closest('button');                    // [DOM] Detectamos si clic fue en botón
		if (!boton) return;                                          // [DOM] Si no es botón → salir

		const tr = e.target.closest('tr');                           // [DOM] Encontramos la fila <tr>
		if (!tr) return;                                             // [DOM] Seguridad
	
		const rowApi = datatable_usuario.row(tr);                            // [DT] Obtenemos instancia row() de DataTables

		const fila = rowApi.data();                                  // [DT] Obtenemos datos JSON de la fila
	
		//console.log(fila);
		if (boton.classList.contains('btn-editar')) {                // [DOM] Si el botón tiene clase editar
				
			onClickEditar(fila, boton, tr, rowApi);                    // [APP] Llamamos a función de negocio editar
		
		} else if (boton.classList.contains('btn-eliminar')) {       // [DOM] Si es eliminar

			onClickEliminar(fila, boton, tr, rowApi);                  // [APP] Llamamos a función de negocio eliminar
		}
	});

	// === HANDLERS PERSONALIZADOS ===
	function onClickEditar(fila, boton, tr, rowApi) {              // [APP] Tu función para editar
		
		//const idDesdeData = fila.id;                                 // [APP] ID obtenido del objeto fila
		//const idDesdeAtributo = boton.dataset.id;                    // [DOM] ID leído del atributo data-id del botón
		//console.log('[EDITAR] fila:', fila);                         // [APP]
		//alert('Editar usuario ID: ' + idDesdeData);                  // [APP] Aquí abrirías modal de edición

		const data = rowApi.data(); // Obtenemos los datos de esa fila

		// Accedemos a los elementos del DOM
		const modalEditar = document.getElementById('modalEditarUsuario');
		const formEditar = document.getElementById('formEditarUsuario');
		const errorEditar = document.getElementById('errorEditar');

		// Seteamos los valores en los campos
		document.getElementById('usernameEditar').value = data.username;
		document.getElementById('nameCompleteEditar').value = data.name_complete;
		document.getElementById('rolEditar').value = data.rol;
		//document.getElementById('passwordEditar').value = ""; // campo en blanco
		formEditar.dataset.idEditar = data.id; // Guardamos el ID en un atributo personalizado

		errorEditar.textContent = "";
		modalEditar.style.display = "flex"; // Mostrar modal
		

	}

	function onClickEliminar(fila, boton, tr, rowApi) {

		const id = fila.id;
		
		if (!confirm(`¿Seguro que deseas eliminar el usuario con ID ${id}?`)) {
			return;
		}

		/*
		===============================================
		📌 APUNTE: Manejo de fetch + validación de JSON
		===============================================

		1) fetch() devuelve una PROMESA que se resuelve en un objeto Response.
		- Este objeto Response contiene status, headers, url y el body en stream.
		- El body todavía no está leído ni convertido.

		2) Para leer el body se usan métodos como response.json() o response.text(),
		los cuales también devuelven PROMESAS porque la lectura/parseo es asíncrono.

		3) Si hacemos directamente `return response.json()`, en el siguiente .then
		ya no tendremos acceso a `response.status` o headers (scope perdido).
		Por eso anidamos otra promesa dentro del mismo .then y devolvemos un objeto
		combinado con { status, body } → así conservamos todo en un solo ámbito.

		4) Además, validamos el Content-Type para no romper si el servidor devuelve HTML
		en lugar de JSON (ej. errores 500).

		===============================================
		*/

		fetch(`/usuarios/${id}`, {
		method: 'DELETE',
		headers: { 'Content-Type': 'application/json' }
		})
		.then(response => {
			
			
			// Revisamos cabecera Content-Type para saber si es JSON
			const contentType = response.headers.get("content-type");
			//console.log(contentType.includes("application/json"));
			if (contentType && contentType.includes("application/json")) {
				// ✅ Caso: el servidor dice que devolvió JSON
				return response.json().then(body => {
				// Aquí tenemos acceso tanto al response como al body parseado
				if (!response.ok) {
					// Lanzamos error con ambos datos unidos

					throw { status: response.status, body: body };
				}
				// Devolvemos objeto combinado { status, body } al siguiente then
				return { status: response.status, body: body };
				});
			} else {
				// ⚠️ Caso: no es JSON (ej. servidor devolvió HTML en error 500)
				return response.text().then(texto => {
				if (!response.ok) {
					// Lanzamos error con texto plano dentro de un body simulado
					throw { status: response.status, body: { mensaje: texto } };
				}
				// Devolvemos también como objeto combinado
				return { status: response.status, body: { mensaje: texto } };
				});
			}
		})
		.then(resultado => {
			// Solo entra aquí si el status fue 200–299
			//window.dataTables['usuario'].ajax.reload();
			//console.log("✅ OK:", resultado.body);
			//console.log(resultado);
			//mostrarSuccess(resultado.body.mensaje || "Operación realizada correctamente");
			//alert("samuel");
			recargar_table_usuario();
			mostrarMensajeEnDataTable(resultado.body.mensaje || "Operación realizada correctamente");

		})
		.catch(err => {

			console.log(err.status);
			console.log(err.body.status);
			
		
		
			if (err.status === 401 && err.body?.status === 'session_expired') {
			
				loginModal.style.display = 'flex';                   // [DOM] Mostramos modal de login si expiró sesión
				
	
			} else if (err.status === 401 && err.body?.status === 'unauthorized'){
				mostrarErrorUSUARIO(err.body.mensaje);
			} else {

				//console.errormostrarError("❌ Error de red:", err);
				mostrarErrorUSUARIO("No se pudo conectar con el servidor");

			}
		
		// Aquí llegan:
		//   a) Errores HTTP (400, 401, 500...) lanzados con throw
		//   b) Errores de red reales (servidor caído, CORS, etc.)
		if (err.body) {
			//console.error(`❌ Error HTTP ${err.status}:`, err.body);
			//loginModal.style.display = 'flex'; 
			//alert(err.body.mensaje || "Error en la operación");
		} 	
		});
	}


  	window.dataTables['usuario'] = datatable_usuario;                      // [APP] Guardamos la instancia globalmente
if(window.dataTables['usuario'] instanceof DataTable){
		alert("existe tabla usuario ");
		
	}
}



// Creamos el "manejador" de error
//const mostrarSuccessUSUARIO = crearModalSuccess();

function mostrarMensajeEnDataTable(texto, tipo = "success") {
  const mensaje = document.getElementById("mensajeTabla");

  mensaje.className = tipo;  // success o error
  mensaje.textContent = texto;
  mensaje.style.display = "block";

  setTimeout(() => {
    mensaje.style.display = "none";
  }, 2000);
}


function mostrarMensajeEnModal(texto, tipo = "success") {
  const mensaje = document.getElementById("mensajeTablaMessage");

  mensaje.className = tipo;  // success o error
  mensaje.textContent = texto;
  mensaje.style.display = "block";

  setTimeout(() => {
    mensaje.style.display = "none";
  }, 2000);
}


// Función que devuelve un manejador de modal "privado"
function mostrarErrorUSUARIO(mensaje) {
  
    const modalElement = document.querySelector('#modal_error');
    modalError = bootstrap.Modal.getOrCreateInstance(modalElement);
    
    document.querySelector('.body_mensaje_error').textContent = mensaje;
    modalError.show();
  
}

// Creamos el "manejador" de error
//const mostrarError = crearModalError();


// Función que devuelve un manejador de modal "privado"
function mostrarSuccessUSUARIO(mensaje) {
  
    const modalElement = document.getElementById('modal_success');
    const modalSuccess = new bootstrap.Modal.getOrCreateInstance(modalElement);
    
    document.querySelector('.body_mensaje_success').textContent = mensaje;
    modalSuccess.show();
 };
/*
========================================================
📌 FLUJO DE PROMESAS EN FETCH
========================================================

1) fetch(url)
   │
   │  (devuelve PROMESA 1)
   ▼
2) Response (status, headers, body como STREAM)
   │
   │  ┌─────────────────────────────────────────────┐
   │  │  body aún no está listo, es un stream       │
   │  │  necesitas consumirlo con json()/text()     │
   │  └─────────────────────────────────────────────┘
   │
   ├──> response.json()   → PROMESA 2 → objeto JS
   │
   ├──> response.text()   → PROMESA 2 → string
   │
   └──> response.blob()   → PROMESA 2 → binario

3) Unimos datos en un solo objeto:
   return response.json().then(body => {
       return { status: response.status, body: body };
   });

   ┌───────────────────────────────────────────────┐
   │  Ahora en el SIGUIENTE .then tendrás:         │
   │    resultado.status → código HTTP             │
   │    resultado.body   → datos ya parseados      │
   └───────────────────────────────────────────────┘

4) Flujo completo con then/catch:
   fetch(url)
     .then(response => response.json().then(body => {
         if (!response.ok) throw {status: response.status, body};
         return {status: response.status, body};
     }))
     .then(resultado => {
         // Éxito (200–299)
         console.log(resultado.status, resultado.body);
     })
     .catch(err => {
         // Error HTTP o de red
         console.error(err.status, err.body);
     });

========================================================


===============================================
📌 APUNTE: Flujo de promesas en fetch()
===============================================

1) fetch(url) devuelve una PROMESA.
   - Esa promesa representa la petición HTTP.
   - Cuando se resuelve, entrega un objeto Response.
   - En este punto puedes leer:
       response.status
       response.ok
       response.headers
       response.url
   - ⚠️ El body todavía es un stream, NO los datos.

2) Para consumir el body necesitas otro proceso asíncrono:
   - response.json()  -> devuelve OTRA PROMESA
   - response.text()  -> devuelve OTRA PROMESA
   - response.blob()  -> devuelve OTRA PROMESA
   Cada una representa "leer el stream y convertirlo".

3) El .then() abre el siguiente proceso en la cadena:
   fetch('/api')
     .then(response => {
        // Aquí ya se resolvió la 1ª promesa (Response)
        return response.json(); // 2ª promesa (leer + parsear)
     })
     .then(data => {
        // Aquí se resolvió la 2ª promesa
        // Ya tienes los datos listos como objeto JS
        console.log(data);
     });

👉 Cada promesa se resuelve en orden, y cada .then depende de la anterior.
   Por eso necesitas varios .then o usar async/await:
     const response = await fetch('/api');
     const data = await response.json();

===============================================
*/


/*
==========================================
📌 APUNTE: Uso de data-* y dataset en JS/HTML
==========================================

1) En HTML siempre se declaran como atributos que empiezan con "data-".
   Ejemplo:
     <button data-id="5"></button>
     <tr data-row-id="10"></tr>
     <div data-user-name="samuel"></div>

2) En JavaScript se acceden a través de la propiedad .dataset
   El navegador convierte automáticamente los nombres de kebab-case (con guiones)
   a camelCase (segunda palabra con mayúscula).

   Ejemplos:
     element.dataset.id          // lee data-id
     element.dataset.rowId       // lee data-row-id
     element.dataset.userName    // lee data-user-name

3) Si el atributo no existe en el HTML → devuelve undefined.

4) También puedes crearlos o modificarlos desde JS:
     element.dataset.id = 20;        // agrega/modifica data-id="20"
     element.dataset.userName = "Ana"; // agrega/modifica data-user-name="Ana"

5) 🔑 Regla de conversión:
   - HTML: data-mi-atributo-especial
   - JS:   element.dataset.miAtributoEspecial

6) Uso típico en DataTables:
   - Generar botones con un identificador:
       render: (data,row) => `<button class="btn-editar" data-id="${row.id}">Editar</button>`
   - Luego en el click:
       boton.dataset.id   // devuelve el id asignado en el botón
*/





function destruirTablaSiExiste() {
/*
===========================================
📌 NOTA: Verificación segura de instancia DataTable
===========================================

En lugar de usar:

    (window.dataTables['usuario'] instanceof DataTable)

… lo cual puede fallar si la clase DataTable no está accesible globalmente,
es mejor usar:

    window.dataTables['usuario']?.destroy instanceof Function

✅ ¿Por qué?
-------------------------------------------
- `instanceof DataTable` depende de que `DataTable` esté en el scope global.
  Si fue importado como módulo o por CDN, puede no estar disponible.
- `destroy instanceof Function` verifica directamente que el método exista.
  Es más confiable y evita errores incluso si la clase no está globalmente visible.

✅ ¿Qué hace el operador `?.`?
-------------------------------------------
- Es el operador de encadenamiento opcional (`optional chaining`).
- Permite acceder a propiedades sin lanzar error si el objeto es `null` o `undefined`.
- Ejemplo: `obj?.propiedad` no rompe aunque `obj` no exista.

✅ Recomendación
-------------------------------------------
Usar este patrón robusto para destruir una tabla si existe:

    if (window.dataTables['usuario']?.destroy instanceof Function) {
        window.dataTables['usuario'].destroy();
        delete window.dataTables['usuario'];
    }

Así evitas errores de tipo, garantizas compatibilidad y haces tu código más seguro.
*/


	if (window.dataTables['usuario']?.destroy instanceof Function) {
		console.log("estoy desde otro boton");
    	window.dataTables['usuario'].destroy();
    	delete window.dataTables['usuario'];
		document.getElementById('contenido-principal').innerHTML = "";
	}
}

function recargar_tablas() {
	destruirTablaSiExiste();
	
	const tablaUsuarioHTML = `
	  <table id="usuario">
	    <thead>
	      <tr>
	        <th>ID</th>
	        <th>NOMBRE</th>
	        <th>CORREO</th>
	        <th>ROL</th>
	        <th>EDITAR</th>
	      </tr>
	    </thead>
	    <tbody></tbody>
	  </table>
	`;

	document.getElementById('contenido-principal').innerHTML = tablaUsuarioHTML;
	cargar_usuario_init();
}






function cargar_usuario_init_original(){

	
	const tabla = document.getElementById('usuario'); // ID de la tabla
	if (!tabla) {	
    	console.warn('Tabla #usuario no encontrada en el DOM');
		return;
	}

	window.dataTables = window.dataTables || {};

	// Si ya existe una instancia anterior, destruirla
	if (window.dataTables['usuario']  instanceof DataTable) {
		window.dataTables['usuario'].destroy();
		delete window.dataTables['usuario'];  // Elimina la referencia del objeto global
	}


	// Crear nueva instancia y guardarla globalmente
    let datatable = new DataTable(tabla,{
 
		processing:true, /*
						1.-Muestra un indicador de "Procesando..." mientras se realiza una petición AJAX.
						2.-Solo es visual, no cambia el comportamiento funcional de la tabla.
				*/

		serverSide:true, /*
						Le dice a DataTables que no cargue todos los datos de golpe, sino que:
							-Pida los datos en bloques (paginación),
							-Aplique el filtro y ordenamiento en el servidor, y
							-Use AJAX para comunicarse con tu backend (PHP, Python, etc).
				*/

		pageLength:2,
		paging:true,
		lengthMenu:[2,5,10,25,30],

		language:{
			url: 'https://cdn.datatables.net/plug-ins/2.3.2/i18n/es-ES.json'
					//url:'//cdn.datatables.net/plug-ins/2.3.2/i18n/es-ES.json'
			
			},
        
		ajax:{
			url:'/usuarios',
			dataSrc: function(json){
				console.log(json);
				if (json?.status === 'success' && Array.isArray(json.data)) {
					return json.data; // DataTables dibuja normalmente
				}

				// En caso raro que llegue con status distinto pero 200
				alert(json?.mensaje || "Respuesta inesperada del servidor");
				return [];
			},	
			error: function(response){
			

				try {
			
					const resultado = {
					status: response.status,     
					body: JSON.parse(response.responseText)
					};

					if (
						resultado.status === 401 &&
						resultado.body?.status === 'unauthorized' &&
						resultado.body?.mensaje === 'Sesión expirada'
					) {
						// Mostrar modal de login
						loginModal.style.display = 'flex';
						return;
					}
			
					alert(resultado.body.mensaje || "Error desconocido");
					
				} catch (e) {
					console.error("Error parseando respuesta de servidor:", e, response.responseText);
					alert("Error crítico de servidor");

				}
				
			},
		},
		
        columns:[
					{data:'id'},
					{data:'username'},
					{data:'name_complete'},
					{data:'rol'},
					{data: null,
						render: function(data, type, row, meta){
							console.log(row.id);
							return `<button value=${row.id}>editar</button>`;
						}
					},
				]
			
    });
	
	//Guardar esta instancia bajo el nombre del módulo 'usuario'
  	//window.dataTables['usuario'] = datatable;

	var table_user = document.getElementById('usuario');
	table_user.addEventListener('click',function(e){
		
		console.log(e.target);
	});
	
};
function error_show(){

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
}

/*

function cargar_usuario_init() {
  const tabla = document.querySelector('#usuario'); // ID de la tabla

  if (!tabla) {
    console.warn('Tabla #usuario no encontrada en el DOM');
    return;
  }

  // Si ya existe una instancia anterior, destruirla
  if (window.dataTableUsuario instanceof DataTable) {
    window.dataTableUsuario.destroy();
    delete window.dataTableUsuario;
  }

  // Crear nueva instancia y guardarla globalmente
  const dt = new DataTable(tabla, {
    searchable: true,
    sortable: true,
    perPage: 10,
  });

  window.dataTableUsuario = dt;
}

*/


/*

🧠 ¿Qué hace realmente dataSrc en DataTables?

La opción dataSrc sirve para indicar a DataTables cuál parte del JSON es el array de datos que debe usar para pintar las filas.

🧩 Estructura esperada por defecto:
{
  "draw": 1,
  "recordsTotal": 100,
  "recordsFiltered": 50,
  "data": [ ... ]  // <- aquí están las filas
}

✅ ¿Por qué solo se retorna json.data?

Cuando haces esto:

dataSrc: function (json) {
  if (Array.isArray(json.data)) {
    return json.data;
  }
  return [];
}


Estás indicando a DataTables:

“Toma solamente json.data como contenido para las filas de la tabla.”

💡 Pero no estás eliminando el resto del JSON (como draw, recordsTotal, etc.), porque dataSrc no sustituye todo el JSON — solo especifica qué usar como “datos de fila”.

📌 ¿Y qué pasa con draw, recordsTotal, recordsFiltered?

Esos siguen presentes en json al nivel raíz, y DataTables los usa automáticamente antes de pasar el JSON a dataSrc.

Primero, DataTables usa draw, recordsTotal, recordsFiltered para controlar la paginación.

Luego, pasa el JSON completo a dataSrc, para que tú indiques solo dónde están los datos (filas).

⚙️ Secuencia de flujo interna (simplificada)

DataTables hace el fetch().

Recibe el JSON del servidor.

Lee automáticamente las claves draw, recordsTotal, recordsFiltered desde el JSON.

Llama a dataSrc(json) y espera que le devuelvas solo el array de datos para pintar filas.

Pinta las filas con lo que devuelves desde dataSrc.

📌 Conclusión

✅ Puedes devolver solo json.data desde dataSrc sin problema.

❌ No debes devolver el JSON completo desde dataSrc, porque eso rompería la tabla (espera solo un array).

✅ Mientras draw, recordsTotal, recordsFiltered estén en la raíz del JSON, DataTables los procesará automáticamente antes de aplicar dataSrc.
*/