

function recargar_table_pl3(){
	if (window.dataTables['datatable_pl3']?.destroy instanceof Function){
		window.dataTables['datatable_pl3'].ajax.reload();
	}
}


function cargar_pl3_init(){
	//login_obligado();
    load_pl3();
    registro_pl3_init();
    onClickEliminar_confirmar_pl3();
	editar_pl3_init();
}

function load_pl3(){

	const tabla = document.getElementById('pl3'); // ID de la tabla


	if (!tabla) {                                                  // [DOM] Verifica si existe
		console.warn('Tabla #usuario no encontrada en el DOM');      // [DOM] Mensaje en consola si no existe
		console.log("de fuera en init");
		return;                                                      // [DOM] Sale de la función
	}


	window.dataTables = window.dataTables || {};

	if (window.dataTables['datatable_pl3']?.destroy instanceof Function) {
		console.log("nuevo ingreso datatable");
    	window.dataTables['datatable_pl3'].destroy();
    	delete window.dataTables['datatable_pl3'];
	}


	// Crear nueva instancia y guardarla globalmente
   
	let datatable_pl3 = new DataTable(tabla,{
		responsive:true,
 		//responsive: true,
		destroy:true ,
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
			url: '/instrumentosPL3',
			dataSrc: function (json) {  // [DT] Función que transforma la respuesta JSON
				// Solo entra aquí si el servidor respondió 200 OK
				
				if (Array.isArray(json.data)) { // [APP] Validación de tu contrato de API
			
					return json.data;                                      // [DT] Devuelve array de datos para pintar filas
				}
				alert('Respuesta inesperada del servidor. No se puede construir la tabla.');          // [APP] Si llega 200 pero no es success → alerta
				return [];                                               // [DT] Devuelve vacío para no romper la tabla
			},
			
/*

			if (err.status === 401 && err.body?.status === 'session_expired') {
				loginModal.style.display = 'flex';
				//login_modal_relogin();
			} else if (err.status === 401 && err.body?.status === 'unauthorized') {
					mostrarErrorPL3(err.body.mensaje);
			} else {
					mostrarErrorPL3(err.body?.mensaje || "No se pudo conectar con el servidor");
			}

			*/
			error: function (response) {                               // [DT] Manejo de errores HTTP ≠ 200
				
				try {                                                    // [APP] Intentamos parsear respuesta
					const resultado = {
						status: response.status,                             // [APP] Código HTTP (400, 401, 500, etc.)
						body: JSON.parse(response.responseText)              // [APP] Convertimos body en objeto JS
					};
		
				//console.log(response);
				if (resultado.status === 401 && resultado.body?.status === 'session_expired') {
					console.log("entre");
					loginModal.style.display = 'flex';                   // [DOM] Mostramos modal de login si expiró sesión
					
					return;                                              // [APP] Evitamos ejecutar alert después
				}

				mostrarErrorGENERAL(resultado.body?.mensaje || 'Error desconocido'); // [APP] Mostramos mensaje de error
				} catch (e) {
					//console.error('Error parseando JSON de error:', e);    // [APP] Log de error si JSON no es válido
					mostrarErrorGENERAL('Error crítico de servidor:' , e);                    // [APP]
				}
			}


		},
        columns:[

					{
						data:'id',
						visible: false
					},
					{data:'tag'},
					{data:'ubicacion'},
					{
						data: 'plataforma',
						visible: false
					},

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
							return (`<i class="fas fa-edit fa-2x btn-editar btn-icon edit" data-id="${row.id}"></i>`);
							//return (`<button type="button" class="btn-editar btn btn-secondary" data-id="${row.id}">Editar</button>`);
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
							return (`<i class="fa fa-trash fa-2x btn-eliminar btn-icon delete" aria-hidden="true" data-id="${row.id}"></i>`);
							//return (`<button type="button" class="btn-eliminar btn btn-danger" data-id="${row.id}">Eliminar</button>`);
						/*
										// [DOM] Botón eliminar
										// [DOM] Guardamos id en data-id
										// [DOM]
						*/
						
						}
					}

				],

				  // 🔹 Aquí sí van los priorities
		
			
     		
		createdRow: function (row, data, dataIndex) {                // [DT] Callback al crear cada fila
			//console.log(data);
			row.dataset.rowId = data.id;                               // [DOM] Insertamos atributo data-row-id en el <tr>
		}	
    });
	
	//Guardar esta instancia bajo el nombre del módulo 'datatable_t155'
  	window.dataTables['datatable_pl3'] = datatable_pl3;
	const tbody = tabla.querySelector('tbody');                    // [DOM] Seleccionamos <tbody> de la tabla

	tbody.addEventListener('click', function (e) {                 // [DOM] Escuchamos clicks en todo el tbody
		
		


		const boton = e.target.closest('i');                    // [DOM] Detectamos si clic fue en botón
		if (!boton) return;                                          // [DOM] Si no es botón → salir

		const tr = e.target.closest('tr');                           // [DOM]td.control:before {

		if (!tr) return;                                             // [DOM] Seguridad
	
		const rowApi = datatable_pl3.row(tr);                            // [DT] Obtenemos instancia row() de DataTables

		const fila = rowApi.data();                                  // [DT] Obtenemos datos JSON de la fila
	
		//console.log(fila);
		if (boton.classList.contains('btn-editar')) {                // [DOM] Si el botón tiene clase editar
				
			onClickEditarPL3(fila, boton, tr, rowApi);                    // [APP] Llamamos a función de negocio editar
		
		} else if (boton.classList.contains('btn-eliminar')) {       // [DOM] Si es eliminar

			onClickEliminarPL3(fila, boton, tr, rowApi);                  // [APP] Llamamos a función de negocio eliminar
		}


	});


};
function onClickEditarPL3(fila, boton, tr, rowApi) {              // [APP] Tu función para editar
	
	var myModalEl = document.querySelector('#modal_editar_pl3');
	var modalEditar = bootstrap.Modal.getOrCreateInstance(myModalEl,{
		backdrop: 'static'
	
	}); // Returns a Bootstrap modal instance

	const data = rowApi.data(); // Obtenemos los datos de esa fila

	const formEditar = document.getElementById('formInstrumentoPL3_edit');
	
	// Seteamos los valores en los campos
	document.getElementById('tag_pl3_edit').value = data.tag;
	document.getElementById('plataforma_pl3_edit').value = data.plataforma;

	
	formEditar.dataset.idEditar = data.id; // Guardamos el ID en un atributo personalizado

	
  	modalEditar.show();
	
}
function editar_pl3_init(){

	const modalConfirmar = document.getElementById('modal_editar_Pl3_confirmar');
	modalConfirmar.addEventListener('click',function(){

		var myModalEl = document.querySelector('#modal_editar_pl3');
		var modalEditar = bootstrap.Modal.getOrCreateInstance(myModalEl,{
			backdrop: 'static'
		
		}); 
		const formEditarPL3 =  document.getElementById('formInstrumentoPL3_edit');
		const formData = new FormData(formEditarPL3);
		const data = Object.fromEntries(formData.entries());

		const tagId = formEditarPL3.dataset.idEditar;

		//console.log(data);

			
		fetch(`/instrumentosPL3/${tagId}`, {
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
	
			modalEditar.hide();
			recargar_table_pl3();
			//mostrarMensajeEnModalRegistro(resultado.body.mensaje);
			mostrarMensajeEnDataTablePL3(resultado.body.mensaje);
			formEditarPL3.reset();
			

		})
		.catch(err => {
			console.log(err);
			modalEditar.hide();
			formEditarPL3.reset();
			
			if (err.status === 401 && err.body?.status === 'session_expired') {
				loginModal.style.display = 'flex';
				//login_modal_relogin();
			} else if (err.status === 403 && err.body?.status === 'unauthorized') {
					console.log("ahora si funciona ");
					mostrarErrorGENERAL(err.body.mensaje);
			} else {
				
					mostrarErrorGENERAL(err.body?.mensaje || "No se pudo conectar con el servidor");
			}
		});		
		
	});
}
function onClickEliminarPL3(fila, boton, tr, rowApi){

	var eliminar = document.querySelector('#modal_eliminar_pl3');
	var modal_eliminar = bootstrap.Modal.getOrCreateInstance(eliminar,{
		backdrop: 'static'
	});

	console.log(fila);
	eliminar.querySelector('.modal-body').dataset.idDelete = fila.id;
	eliminar.querySelector('.modal-body').textContent = `¿Seguro que deseas eliminar el tag ${fila.tag}?`;
	modal_eliminar.show();


}
function onClickEliminar_confirmar_pl3() {

	var eliminar = document.querySelector('#modal_eliminar_pl3');
	eliminar.querySelector('#modal_eliminar_pl3_confirmar').addEventListener('click',function(){
		
		
		var modal_eliminar = bootstrap.Modal.getOrCreateInstance(eliminar,{
			backdrop: 'static'
		});

		
		var id = eliminar.querySelector('.modal-body').dataset.idDelete;

		
	

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

		fetch(`/instrumentosPL3/${id}`, {
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
			recargar_table_pl3();
			modal_eliminar.hide();
			mostrarMensajeEnDataTablePL3(resultado.body.mensaje || "Operación realizada correctamente");
			//mostrarMensajeEnDataTable(resultado.body.mensaje || "Operación realizada correctamente");

		})
		.catch(err => {

			//console.log(err.status);
			console.log(err);
				
			modal_eliminar.hide();
			
			if (err.status === 401 && err.body?.status === 'session_expired') {
				
				loginModal.style.display = 'flex';                   // [DOM] Mostramos modal de login si expiró sesión
				console.log(err);
				
			} else if (err.status === 403 && err.body?.status === 'unauthorized'){
console.log("entre unathorized");
				mostrarErrorGENERAL(err.body.mensaje);
			} else {

				//console.error("❌ Error de red:", err);
				mostrarErrorGENERAL(err.body?.mensaje || "No se pudo conectar con el servidor");

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

	});

}

function mostrarMensajeEnDataTablePL3(texto, tipo = "success") {
  const mensaje = document.getElementById("mensajeTablaPL3");

  mensaje.className = tipo;  // success o error
  mensaje.textContent = texto;
  mensaje.style.display = "block";

  setTimeout(() => {
    mensaje.style.display = "none";
  }, 2000);
}


function registro_tag_pl3(){

	
       

		var myModalEl = document.querySelector('#modal_registro_pl3');
		var modalRegistro = bootstrap.Modal.getOrCreateInstance(myModalEl,{
			backdrop: 'static'
		
		}); // Returns a Bootstrap modal instance


		modalRegistro.show();
	
}

function mostrarMensajeEnModalRegistroPL3(texto, tipo = "success") {
  const mensaje = document.getElementById("mensajeModalPL3");

  mensaje.className = tipo;  // success o error
  mensaje.textContent = texto;
  mensaje.style.display = "block";

  setTimeout(() => {
    mensaje.style.display = "none";
  }, 2000);
}

function registro_pl3_init(){

	const modalConfirmar = document.getElementById('modal_registro_pl3_confirmar');
	modalConfirmar.addEventListener('click',function(){

		var myModalEl = document.querySelector('#modal_registro_pl3');
		var modalRegistro = bootstrap.Modal.getOrCreateInstance(myModalEl,{
			backdrop: 'static'
		
		});
		
		
		const formRegistroPL3 =  document.getElementById('formInstrumentoPL3_registro');
		const formData = new FormData(formRegistroPL3);
		const data = Object.fromEntries(formData.entries());

		fetch(`/instrumentosPL3`, {
			method: 'POST',
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
	
			//modalRegistro.hide();
			recargar_table_pl3();
			mostrarMensajeEnModalRegistroPL3(resultado.body.mensaje);
			//mostrarMensajeEnDataTableInstrumento(resultado.body.mensaje);
			formRegistroPL3.reset();
			

		})
		.catch(err => {
			console.log(err);
			modalRegistro.hide();
			formRegistroPL3.reset();

			if (err.status === 401 && err.bmensajeTablaody?.status === 'session_expired') {
				loginModal.style.display = 'flex';
			} else if (err.status === 401 && err.body?.status === 'unauthorized') {
					mostrarErrorGENERAL(err.body.mensaje);
			} else {
					mostrarErrorGENERAL(err.body?.mensaje || "No se pudo conectar con el servidor");
			}
		});		
		
	});

}



// Creamos el "manejador" de error
//const mostrarError = crearModalError();


// Función que devuelve un manejador de modal "privado"
function mostrarSuccessPL3(mensaje) {
  
    const modalElement = document.getElementById('modal_success');
    const modalSuccess = new bootstrap.Modal.getOrCreateInstance(modalElement);
    
    document.querySelector('.body_mensaje_success').textContent = mensaje;
    modalSuccess.show();
 };
