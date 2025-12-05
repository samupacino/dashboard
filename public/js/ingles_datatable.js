
(function () {
  'use strict';
  /******************************************************
   * HELPERS DE FORMATO
   ******************************************************/

const formatDate = (s) => {
  if (!s) return '';
  const d = new Date(s);
  return new Intl.DateTimeFormat('es-PE', {
    year:'numeric', month:'2-digit', day:'2-digit',
    hour:'2-digit', minute:'2-digit'
  }).format(d);
};

const posLabel = (p) => ({
  verb: 'Verbo',
  phrasal_verb: 'Phrasal verb',
  noun: 'Sustantivo',
  adjective: 'Adjetivo',
  adverb: 'Adverbio',
  expression: 'Expresión'
}[p] || p);


function levelBadge(lvl) {
  if (lvl) {
    return `<span class="dt-level">${lvl}</span>`;
  } else {
    return '';
  }
}


  const TABLE_KEY = 'datatable_ingles';  // clave para window.dataTables

  // Aseguramos el “registro global” de DataTables
  window.dataTables = window.dataTables || {};

  /******************************************************
   * FUNCIÓN PRINCIPAL: INICIALIZAR DATATABLE
   ******************************************************/
	
function load_ingles_init() {                                   // [APP] Tu función para inicializar la tabla
  
  	const tabla = document.getElementById('ingles');
	
	if (!tabla) {                                                  // [DOM] Verifica si existe
		console.warn('Tabla #ingles no encontrada en el DOM');      // [DOM] Mensaje en consola si no existe
		
		return;                                                      // [DOM] Sale de la función
	}

	if (window.dataTables[TABLE_KEY]?.destroy instanceof Function) {
		console.log('[INGLES] Destruyendo instancia previa de DataTable');
    	window.dataTables[TABLE_KEY].destroy();
    	delete window.dataTables[TABLE_KEY];
	}

	// === CREAR INSTANCIA DATATABLE ===
	 let datatable_ingles = new DataTable(tabla, {                       // [DT] Constructor principal de DataTables
		 
		 
		responsive: {
			details: {
			  type: 'inline',              // muestra “detalles” debajo
			  target: 'dtr-control',         // clic en la celda de control
			  
			  renderer: function (api, rowIdx, columns) {
				   console.log("Renderer ejecutado para la fila:", columns);
				  		  
				  
					// Construye una tabla 2-columnas: TÍTULO | VALOR
					const rows = $.map(columns, col => {
					  if (!col.hidden) return '';  // solo mostrar las ocultas en el detalle
					  return `
						<tr>
						  <th class="dt-key">${col.title}</th>
						  <td class="dt-val">${col.data ?? ''}</td>
						</tr>`;
					}).join('');
					return rows ? $(`<table class="table table-sm m-0"><tbody>${rows}</tbody></table>`) : false;
				  
			  }      
			}
		},
		columnDefs: [
			
			
			
			{ targets: 0, visible: false, searchable: false },
			{ targets: 1, className: 'dtr-control' }, // NOMBRE

			// Columnas que van al “detalle” al expandir la fila
			{ targets: 2, className: 'all' }, 
			{ targets: 3, className: 'all' },
			{ targets: 4, className: 'none' },
			{ targets: 5, className: 'none' },
			{ targets: 6, className: 'none' },
			{ targets: 7, className: 'none' },
			{ targets: 8, className: 'none' },
			{ targets: 9, visible: false, className: 'none' , searchable: true},
			{ targets: 10, className: 'none' },
			{ targets: 11, className: 'none' },
			{ targets: 12, className: 'none' },
			{ targets: 13, className: 'none' },
			

		],
		
		
		
		
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
		url: '/api/ingles/listar',                                          // [DT] URL del backend para cargar datos


		dataSrc: function (json) {  // [DT] Función que transforma la respuesta JSON
			// Solo entra aquí si el servidor respondió 200 OK
			
			 
			 
			if (Array.isArray(json.data)) { // [APP] Validación de tu contrato de API
				//alert(json.data);
				return json.data;                                      // [DT] Devuelve array de datos para pintar filas
			}
			mostrarMensajeEnDataTableINGLES('Respuesta inesperada del servidor. No se puede construir la tabla.','error',7000);          // [APP] Si llega 200 pero no es success → alerta
			return [];                                               // [DT] Devuelve vacío para no romper la tabla
		},

		error: function (response) {                               // [DT] Manejo de errores HTTP ≠ 200
			
			try {                                                    // [APP] Intentamos parsear respuesta
				const resultado = {
					status: response.status,                             // [APP] Código HTTP (400, 401, 500, etc.)
					body: JSON.parse(response.responseText)              // [APP] Convertimos body en objeto JS
				};
				
				console.log(resultado);
	
				if (resultado.status === 401 && resultado.body?.status === 'session_expired') {
					
					actualizarBotonLogin(false);
					
				} else if (resultado.status === 403 && resultado.body?.status === 'unauthorized'){
					
					
				}
				
				
				
				mostrarMensajeEnDataTableINGLES(resultado.body?.mensaje || 'Error desconocido','error',7000);
				
			
			} catch (e) {
				console.error(e);    // [APP] Log de error si JSON no es válido
				mostrarMensajeEnDataTableINGLES('Error parseando JSON de error: ', e,'error',7000);
				
			}
		}
		},


		// === DEFINICIÓN DE COLUMNAS ===
		columns: [  
		
			{data: 'id', title: 'ID', render: (v)=> v || ''},
			{data: 'english', title: 'ENGLISH', render: (v)=> v || ''},
			{data: 'pronunciation', title: 'PRONUNCIATION', render: (v)=> v || ''},
			{data: 'spanish', title: 'SPANISH', render: (v)=> v || ''},
			{data: 'pos', title: 'TIPO', render: (v)=> posLabel(v)},
			{data: 'level', title: 'LEVEL', render: (v)=> posLabel(v)},
			{data: 'example_en', title: 'EXAMPLE EN', render: (v)=> v || ''},
			{data: 'example_es', title: 'EXAMPLE ES', render: (v)=> v || ''},
			{data: 'notes', title: 'NOTES', render: (v)=> v || ''},
			{data: 'opposite_id', title: 'OPPOSITE ID', render: (v)=> v || ''},
			{data: 'opposite', title: 'OPPOSITE', render: (v)=> v || ''},
			{data: 'source', title: 'SOURCE', render: (v)=> v || ''},
			{data: 'created_at', title: 'CREATED', render: (v)=> formatDate(v)},
			{data: 'updated_at', title: 'UPDATED', render: (v)=> formatDate(v)},
		
			{                                                          
			data: null,                                              // [DT] No usa un campo → fabricamos contenido
			title: 'EDITAR',
			orderable: false,                                        // [DT] No ordenable
			searchable: false,                                       // [DT] No filtrable
			/*
			🟡 ¿class="dt-center" es de Bootstrap?
				No. dt-center no es una clase de Bootstrap.
				Es una clase de DataTables, no de Bootstrap.
			*/
			className: 'dt-center none',                                  // [DT] Centrado
			render: function (data, type, row, meta) {               // [DT] Firma reglamentaria (data, type, row, meta)
				// data = valor de la celda (null aquí)
				// type = 'display' | 'sort' | 'filter'
				// row  = objeto completo de la fila (ej: {id:1,...})
				// meta = info de índice de fila/columna
				//console.log(data);
				return (`<i class="fas fa-edit fa-2x btn-editar btn-icon edit btnEditar"  data-id="${row.id}"></i>`);
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
			title: 'ELIMINAR',
			orderable: false,                                        // [DT]
			searchable: false,                                       // [DT]
			className: 'dt-center none',                                  // [DT]
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
		
		
		

		
		
		
		createdRow: function (row, data, dataIndex) {                // [DT] Callback al crear cada fila
			//console.log(data);
			row.dataset.rowId = data.id;                               // [DOM] Insertamos atributo data-row-id en el <tr>
		}
	});

	//Guardar esta instancia bajo el nombre del módulo 'datatable_t155'
  	window.dataTables[TABLE_KEY] = datatable_ingles;
  	
	const tbody = tabla.querySelector('tbody');                    // [DOM] Seleccionamos <tbody> de la tabla
	
	if(tbody){
	tbody.addEventListener('click', function (e) {                 // [DOM] Escuchamos clicks en todo el tbody
		
		const boton = e.target.closest('i');                    // [DOM] Detectamos si clic fue en botón
		if (!boton) return;                                          // [DOM] Si no es botón → salir

		const tr = e.target.closest('tr');                           // [DOM]td.control:before {

		if (!tr) return;                                             // [DOM] Seguridad
	
		const rowApi = datatable_ingles.row(tr);                            // [DT] Obtenemos instancia row() de DataTables

		const fila = rowApi.data();                                  // [DT] Obtenemos datos JSON de la fila
	
		//console.log(fila);
		if (boton.classList.contains('btn-editar')) {                // [DOM] Si el botón tiene clase editar
				
			
			onClickEditarINGLES(fila, boton, tr, rowApi);                    // [APP] Llamamos a función de negocio editar
		
		} else if (boton.classList.contains('btn-eliminar')) {       // [DOM] Si es eliminar

			onClickEliminarINGLES(fila, boton, tr, rowApi);                  // [APP] Llamamos a función de negocio eliminar
		}


		});
	}
	onClickEliminar_confirmar_ingles();
}

function onClickEditarINGLES(fila, boton, tr, rowApi){
	
	// abrirModalEditar viene de tu módulo del modal EnVocab
    if (typeof abrirModalEditar === 'function') {
      abrirModalEditar(fila);
    } else {
      console.warn('[INGLES] abrirModalEditar no está definido');
    }
}



function recargar_table_ingles(){
  // 1) window.dataTables existe?
  // 2) existe la tabla datatable_ingles dentro?
  const dt = window.dataTables?.['datatable_ingles'];

  // 3) existe datatable_ingles y tiene ajax.reload()?
  if (dt && typeof dt.ajax?.reload === 'function') {
    dt.ajax.reload(null, false); // Recargar sin resetear página
  }
}

  function destroyTable() {
    const dt = window.dataTables?.[TABLE_KEY];
    if (dt?.destroy instanceof Function) {
      dt.destroy();
      delete window.dataTables[TABLE_KEY];
      console.log('[INGLES] DataTable destruido manualmente');
    }
  }

function onClickEliminarINGLES(fila, boton, tr, rowApi){

	var eliminar = document.querySelector('#modal_eliminar_ingles');
	
	if (!eliminar) {
      console.warn('[INGLES] Modal #modal_eliminar_ingles no encontrado');
      return;
    }
    
    
	var modal_eliminar = bootstrap.Modal.getOrCreateInstance(eliminar,{
		backdrop: 'static'
	});

	console.log(fila);
	eliminar.querySelector('.modal-body').dataset.idDelete = fila.id;
	eliminar.querySelector('.modal-body').textContent = `¿Seguro que deseas eliminar la palabra ${fila.english}?`;
	modal_eliminar.show();


}


function onClickEliminar_confirmar_ingles() {
//modal_eliminar_ingles_confirmar
	var eliminar = document.querySelector('#modal_eliminar_ingles');
	if (!eliminar) return;
	eliminar.querySelector('#modal_eliminar_ingles_confirmar').addEventListener('click',function(){
		
	
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

		fetch(`/api/ingles/${id}`, {
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
			recargar_table_ingles();
			modal_eliminar.hide();
			//mostrarMensajeEnDataTableINGLES(resultado.body.mensaje || "Operación realizada correctamente");
			mostrarMensajeEnDataTableINGLES(resultado.body.mensaje || "Operación realizada correctamente");
			//mostrarMensajeEnDataTable(resultado.body.mensaje || "Operación realizada correctamente");

		})
		.catch(err => {

			//console.log(err.status);
			console.log(err);
				
			modal_eliminar.hide();
			
		
			
			if (err.status === 401 && err.body?.status === 'session_expired') {
				console.log("desde linea 437");
				//loginModal.style.display = 'flex';                   // [DOM] Mostramos modal de login si expiró sesión
					actualizarBotonLogin(false);
				mostrarMensajeEnDataTableINGLES(err.body?.mensaje || "No se pudo conectar con el servidor",'error',7000);
				
		
			} else if (err.status === 403 && err.body?.status === 'unauthorized'){
				console.log("desde linea 443");
				//mostrarMensajeEnDataTableINGLES(err.body?.mensaje || "Operación realizada correctamente",'error',7000);
				mostrarMensajeEnDataTableINGLES(err.body?.mensaje || "No se pudo conectar con el servidor",'error',7000);

				
			} else {

				//console.error("❌ Error de red:", err);
				//mostrarErrorPL3(err.body?.mensaje || "No se pudo conectar con el servidor");
				mostrarMensajeEnDataTableINGLES(err.body?.mensaje || "No se pudo conectar con el servidor",'error',7000);

			}
			
			// Aquí llegan:
			//   a) Errores HTTP (400, 401, 500...) lanzados con throw
			//   b) Errores de red reales (servidor caído, CORS, etc.)
			if (err.body) {
			
				//mostrarMensajeEnDataTableINGLES(err.body?.mensaje || "Error en la operación",'error',7000);

				//console.error(`❌ Error HTTP ${err.status}:`, err.body);
				//loginModal.style.display = 'flex'; 
				//alert(err.body.mensaje || "Error en la operación");
			} 	
		});

	});

}
		

function mostrarMensajeEnDataTableINGLES(texto, tipo = "success",time = 2000) {
  const mensaje = document.getElementById("mensajeTablaINGLES");

  mensaje.className = tipo;  // success o error
  mensaje.textContent = texto;
  mensaje.style.display = "block";

  setTimeout(() => {
    mensaje.style.display = "none";
  }, time);
}


  /******************************************************
   * EXPONER MÓDULO GLOBAL
   **/
  window.App = window.App || {};
  App.ingles = {
    init:   load_ingles_init,
    reload: recargar_table_ingles,
   	destroy: destroyTable
  };
  
  	  // Inicializar cuando el DOM esté listo
  document.addEventListener('DOMContentLoaded', () => {
    App.ingles.init();
  });
  
  
  
})();  // fin IIFE

/*******************************************************
 * NAMESPACE GLOBAL: window.App
 * -----------------------------------------------------
 * JavaScript en navegador NO tiene módulos nativos
 * cuando se usa <script> tradicional. Para evitar:
 * 
 *   - variables globales sueltas,
 *   - colisiones de nombres,
 *   - dificultad para mantener código,
 * 
 * se crea un único objeto global “App” dentro de
 * window. Dentro colocamos módulos organizados como:
 * 
 *   App.ingles.init()
 *   App.ingles.reload()
 *   App.dashboard.init()
 *
 * Esto es una práctica profesional conocida como
 * “namespace pattern”, permite:
 *
 *  ✔ evitar contaminar window con mil variables sueltas
 *  ✔ agrupar todo por funcionalidad
 *  ✔ mantener orden y escalabilidad
 *  ✔ exponer solo lo necesario al ámbito global
 *
 * Si App ya existe, se reutiliza; si no, se crea:
 *     window.App = window.App || {};
 *******************************************************/


/* ==========================================================================
   GUÍA RÁPIDA DE CALLBACKS Y PARÁMETROS EN DATATABLES
   (Para recordar cómo funciona cada función)
   ==========================================================================

   1) Formatear cómo se muestra una celda
      columns.render
      -----------------------------
      render: function(data, type, row, meta) { ... }

      data → valor bruto de la celda
      type → modo: 'display', 'filter', 'sort', etc.
      row  → objeto completo de la fila (toda la data)
      meta → info de posición {row, col, settings}

      Uso típico:
      render: (data) => data || ''     // evitar mostrar null

   --------------------------------------------------------------------------

   2) Controlar cómo se muestra el detalle en Responsive (child row)
      responsive.details.renderer
      -----------------------------
      renderer: function(api, rowIdx, columns) { ... }

      api     → instancia API de DataTables
      rowIdx  → índice de fila expandida
      columns → Array de columnas con:
                col.title  → título de la columna
                col.data   → valor que se mostrará
                col.hidden → true si la columna está oculta en la tabla principal

      Uso típico:
      Mostrar solo columnas ocultas (detalle):
      if (!col.hidden) return '';

   --------------------------------------------------------------------------

   3) Ejecutar código cuando se crea la fila (útil para agregar clases)
      createdRow
      -----------------------------
      createdRow: function(row, data, dataIndex) { ... }

      row       → <tr> ya creado
      data      → objeto completo de la fila
      dataIndex → posición

   --------------------------------------------------------------------------

   4) Modificar o filtrar datos después del AJAX y antes de pintar la tabla
      ajax.dataSrc
      -----------------------------
      dataSrc: function(json) { ... return json.data or array; }

      json → respuesta cruda del servidor (JSON)
      Se usa para:
        - ajustar formato
        - filtrar
        - renombrar campos
        - retornar solo el array que DataTables necesita

   --------------------------------------------------------------------------

   5) Clase para el botón de apertura de detalle responsive
      -----------------------------
      className: 'dtr-control', orderable:false

      IMPORTANTE:
      - Usar *dtr-control*
      - NO usar "control" solo

   --------------------------------------------------------------------------

   6) Columnas visibles / ocultas para responsive
      -----------------------------
      visible: true/false

      visible: false → columna NO aparece en la fila principal,
                        pero sí estará disponible en "columns" dentro del renderer.

   --------------------------------------------------------------------------

   CONSEJO GENERAL:
   - No inventes parámetros.
   - Cada callback tiene su firma específica.
   - Buscar siempre en la doc oficial:
       https://datatables.net/reference/index
   ========================================================================== */
