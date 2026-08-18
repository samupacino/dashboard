(function () {
  'use strict';
  
  
const formatDate = (s) => {
  if (!s) return '';
  const d = new Date(s);
  return new Intl.DateTimeFormat('es-PE', {
    year:'numeric', month:'2-digit', day:'2-digit',
    hour:'2-digit', minute:'2-digit'
  }).format(d);
};

  /******************************************************
   * HELPERS DE FORMATO
   ******************************************************/

  const TABLE_KEY = 'instrumento';  // clave para window.dataTables

  // Aseguramos el “registro global” de DataTables
  window.dataTables = window.dataTables || {};

  /******************************************************
   * FUNCIÓN PRINCIPAL: INICIALIZAR DATATABLE
   ******************************************************/


					
function load_instrumento_init() {                                   // [APP] Tu función para inicializar la tabla
 	
  	const tabla = document.getElementById('instrumento');
	
	if (!tabla) {                                                  // [DOM] Verifica si existe
		console.warn('Tabla #INSTRUMENTO no encontrada en el DOM');      // [DOM] Mensaje en consola si no existe
		
		return;                                                      // [DOM] Sale de la función
	}

	if (window.dataTables[TABLE_KEY]?.destroy instanceof Function) {
		console.log('[INSTRUMENTO] Destruyendo instancia previa de DataTable');
    	window.dataTables[TABLE_KEY].destroy();
    	delete window.dataTables[TABLE_KEY];
	}

	// === CREAR INSTANCIA DATATABLE ===
	 let datatable_instrumento = new DataTable(tabla, {                       // [DT] Constructor principal de DataTables
		 
		 
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
			
			/*
			 ⚠️ Diferencia importante
				className: 'none'
					Oculta en fila principal
					Visible en detalle responsive
					hidden = true
					
				visible: false
					Oculta completamente
					NO aparece en detalle
					hidden = false (no participa)
			 * */
			
			{ targets: 0, visible: false, searchable: false },
			{ targets: 1, className: 'dtr-control all' }, // NOMBRE
			{ targets: 2, className: 'all', orderable: true }, 
			{ targets: 3, visible: false, className: 'all' },
			{ targets: 4, className: 'all' },
			// Columnas que van al “detalle” al expandir la fila
			
			{ targets: 5, visible: true, className: 'none' },
			{ targets: 6, className: 'none' },
			{ targets: 7, className: 'none' },
			{ targets: 8, className: 'none' },
			{ targets: 9, className: 'none'},
			{ targets: 10, className: 'none' },
			{ targets: 11, className: 'none'},
			{ targets: 12, className: 'none'},

			

		],
		
		
		
		
		//destroy:true ,
		processing: true,                                            // [DT] Muestra texto "Procesando..." durante AJAX
		serverSide: true,                                           // // [DT] Búsqueda, orden y paginación procesados en servidor
		pageLength: 5,                                               // [DT] Número de filas por página
		paging: true,                                                // [DT] Activa paginación
		lengthMenu: [5, 10, 25, 50],                                 // [DT] Opciones para elegir filas por página
		language: {                                                  // [DT] Traducciones de UI
			url: 'https://cdn.datatables.net/plug-ins/2.3.2/i18n/es-ES.json'
		},

		ajax: {                                                      // [DT] Bloque AJAX de DataTables
			url: '/api/instrumentos/listar',                                          // [DT] URL del backend para cargar datos

			type: 'GET',
			dataSrc: function (json) {  // [DT] Función que transforma la respuesta JSON
			// Solo entra aquí si el servidor respondió 200 OK
			
	
			// Validación completa para DataTables server-side
			if (json && Array.isArray(json.data) && typeof json.draw !== 'undefined') {
				return json.data;
			}
			window.app.ui.mostrarErrorGENERAL('Respuesta inesperada del servidor. No se puede construir la tabla.');
			
			return [];                                               // [DT] Devuelve vacío para no romper la tabla
		},

		error: function (xhr, textStatus, errorThrown) {


			let err = {
				http: xhr?.status || 0,
				status: 'error',
				mensaje: 'Error al cargar la tabla de instrumentos',
				data: {},
				errors: {}
			};

			if (xhr?.responseJSON) {
				err.status = xhr.responseJSON.status || 'error';
				err.mensaje = xhr.responseJSON.mensaje || err.mensaje;
				err.data = xhr.responseJSON.data || {};
				err.errors = xhr.responseJSON.errors || {};
			} 
			else if (xhr?.responseText) {
				err.mensaje = 'El servidor devolvió una respuesta no válida';
			} 
			else if (errorThrown) {
				err.mensaje = errorThrown;
			}

			window.ajax.response.manejarError(err);
        }
        
        
		},


		// === DEFINICIÓN DE COLUMNAS ===
		columns: [  
		
			{data: 'id', title: 'ID', render: (v)=> v || ''},
			{data: 'tag', title: 'TAG', render: (v)=> v || ''},
			{data: 'planta', title: 'PLANTA', render: (v)=> v || ''},
			{data: 'planta_id', title: 'PLANTA ID', render: (v)=> v || ''},
			{data: 'descripcion', title: 'DESCRIPCION', render: (v)=> v || ''},
			{data: 'tipo', title: 'TIPO', render: (v)=> v || ''},
			
			
			{data: 'area', title: 'AREA', render: (v)=> v || ''},
			{data: 'ubicacion_exacta', title: 'UBICACION', render: (v)=> v || ''},
			//{data: 'foto', title: 'FOTO', render: (v)=> v || ''},
			
			{
			  data: 'foto',
			  title: 'FOTO',
			  render: function(v, type) {

				if (type === 'display') {

				  if (!v || v.trim() === '') {
					return '<span class="text-muted">Sin foto</span>';
				  }

				  return `
					<a href="/${v}" target="_blank">
					  <img src="/${v}" 
						   loading="lazy"
						   style="width:60px; height:60px; object-fit:cover; border-radius:6px;">
					</a>
				  `;
				}

				return v;
			  }
			},		
			
			
			
			{data: 'observacion', title: 'OBSERVACION', render: (v)=> v || ''},
			{data: 'estado', title: 'ESTADO', render: (v)=> v || ''},
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


  	window.dataTables[TABLE_KEY] = datatable_instrumento;


	bindTableEvents(tabla);

}
function recargar_table_instrumento(){
  // 1) window.dataTables existe?
  // 2) existe la tabla datatable_ingles dentro?
  const dt = window.dataTables?.[TABLE_KEY];

  // 3) existe datatable_ingles y tiene ajax.reload()?
  if (dt && typeof dt.ajax?.reload === 'function') {
    dt.ajax.reload(null, false); // Recargar sin resetear página
  }
}
function bindTableEvents(tabla) {

    const tbody = tabla.querySelector('tbody');
    if (!tbody) return;

    // 🧠 evitar duplicados, pero ligado al DOM real
    if (tbody.dataset.eventsBound === 'true') return;

    tbody.addEventListener('click', function (e) {

 		const boton = e.target.closest('.btn-editar, .btn-eliminar');
        if (!boton) return;
       
        /*const boton = e.target.closest('i');
        if (!boton) return;*/

        const tr = boton.closest('tr');
        if (!tr) return;

        const dt = window.dataTables?.[TABLE_KEY];
        if (!dt) return;

        const rowApi = dt.row(tr);
        const fila = rowApi.data();
        if (!fila) return;

        if (boton.classList.contains('btn-editar')) {
			console.log("funciona editar");
            onClickEditarINSTRUMENTO(fila, boton, tr, rowApi);

        } else if (boton.classList.contains('btn-eliminar')) {
            onClickEliminarINSTRUMENTO(fila, boton, tr, rowApi);
          
        }

    });

    // 🔒 marcamos este tbody como ya enlazado
    tbody.dataset.eventsBound = 'true';
}
function onClickEliminarINSTRUMENTO(fila, boton, tr, rowApi){
	console.log(fila.id);
	var eliminar = document.querySelector('#modal_eliminar_instrumento');
	
	if (!eliminar) {
      console.warn('[INSTRUMENTO] Modal #modal_eliminar_ingles no encontrado');
      return;
    }
    
    
	var modal_eliminar = bootstrap.Modal.getOrCreateInstance(eliminar,{
		backdrop: 'static'
	});

	//console.log(fila);
	eliminar.querySelector('.modal-body').dataset.idDelete = fila.id;
	eliminar.querySelector('.modal-body').textContent = `¿Seguro que deseas eliminar la palabra ${fila.tag}?`;
	modal_eliminar.show();

}
function bindEliminarInstrumento() {
	var eliminar = document.querySelector('#modal_eliminar_instrumento');
	if (!eliminar) return;
	
	const btnConfirmar = eliminar.querySelector('#modal_eliminar_instrumento_confirmar');
	 if (!btnConfirmar) {
		return;
	 }
	btnConfirmar.addEventListener('click', function () {
		
		
		
		var modal_eliminar = bootstrap.Modal.getOrCreateInstance(eliminar,{
				backdrop: 'static'
		});

			
		var id = eliminar.querySelector('.modal-body').dataset.idDelete;
		if (!id) {
			window.app.ui.mostrarErrorGENERAL('No se encontró el ID del instrumento.');
			return;
		}
 

        fetch('/api/instrumentos/' + id, {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(window.ajax.response.manejarRespuesta)
        .then(function(respuesta) {

            if (window.app.instrumento) {
                 window.app.instrumento.reload();
            }
			modal_eliminar.hide();
			window.app.ui.mostrarSuccessGENERAL(
                respuesta.body?.mensaje || 'Instrumento eliminado correctamente.'
            );
        })
        .catch(function(error) {
			modal_eliminar.hide();
            window.ajax.response.manejarError(error);
        })
        .finally(function() {
            btn.disabled = false;
            btn.textContent = 'Eliminar';
        });

    });
}

function onClickEditarINSTRUMENTO(fila, boton, tr, rowApi){


    const modal = document.querySelector('#modal_editar_instrumento');
    const instancia = bootstrap.Modal.getOrCreateInstance(modal);

	document.getElementById('edit_id').value = fila.id;
	document.getElementById('edit_tag').value = fila.tag;
	document.getElementById('edit_estado').value = fila.estado || 'activo';
	document.getElementById('edit_descripcion').value = fila.descripcion;
	document.getElementById('edit_tipo').value = fila.tipo;
	
	document.getElementById('edit_planta_id').value = fila.planta_id;
	
	
	document.getElementById('edit_area').value = fila.area;
	document.getElementById('edit_ubicacion_exacta').value = fila.ubicacion_exacta;
	document.getElementById('edit_observacion').value = fila.observacion || '';

	document.getElementById('edit_quitar_foto').value = '0';
	document.getElementById('check_quitar_foto').checked = false;
	document.getElementById('edit_foto').value = '';
	document.getElementById('edit_preview_nueva').src = '';
	document.getElementById('contenedor_preview_nueva').classList.add('d-none');

	const previewActual = document.getElementById('edit_preview_actual');
	const sinFoto = document.getElementById('edit_sin_foto_actual');

	if (fila.foto) {
		previewActual.src = '/' + fila.foto;
		previewActual.classList.remove('d-none');
		sinFoto.classList.add('d-none');
	} else {
		previewActual.src = '';
		previewActual.classList.add('d-none');
		sinFoto.classList.remove('d-none');
	}


    instancia.show();

}

function estadoFoto(){
	const inputNuevaFoto = document.getElementById('edit_foto');
	const previewNueva = document.getElementById('edit_preview_nueva');
	const contenedorPreviewNueva = document.getElementById('contenedor_preview_nueva');
	const checkQuitarFoto = document.getElementById('check_quitar_foto');
	const hiddenQuitarFoto = document.getElementById('edit_quitar_foto');


	if (inputNuevaFoto.dataset.bound === 'true') return;
	
	inputNuevaFoto.addEventListener('change', function () {
		const file = this.files[0];

		if (!file) {
			previewNueva.src = '';
			contenedorPreviewNueva.classList.add('d-none');
			return;
		}

		previewNueva.src = URL.createObjectURL(file);
		contenedorPreviewNueva.classList.remove('d-none');

		// si sube nueva foto, ya no tiene sentido quitar la actual manualmente
		checkQuitarFoto.checked = false;
		hiddenQuitarFoto.value = '0';
	});

	checkQuitarFoto.addEventListener('change', function () {
		hiddenQuitarFoto.value = this.checked ? '1' : '0';

		if (this.checked) {
			inputNuevaFoto.value = '';
			previewNueva.src = '';
			contenedorPreviewNueva.classList.add('d-none');
		}
	});	
}


function bindModalEditar() {

    const modal = document.querySelector('#modal_editar_instrumento');
    if (!modal) return;

    if (modal.dataset.bound === 'true') {
        console.log('YA estaba enlazado');
        return;
    }

    console.log('ENLAZANDO evento');

    modal.addEventListener('hidden.bs.modal', function () {
        limpiarFormularioEditar();
    });

    modal.dataset.bound = 'true';
}



function bindModalEditarCONFIRMAR() {

    const modal = document.querySelector('#modal_editar_instrumento');
    if (!modal) return;
    
  

    modal.addEventListener('click', function (e) {

        const btn = e.target.closest('#modal_editar_confirmar');
        if (!btn) return;

        // 🔽 obtenemos el formulario
        const form = modal.querySelector('#form_editar_instrumento');
        if (!form) return;
        
        
        
        var esValidoHTML5 = form.checkValidity();
		  if (!esValidoHTML5) {
			form.classList.add('was-validated');
		
			return; // no seguimos si hay campos requeridos vacíos o inválidos
		}	
        
         

        // 🔽 ahora usamos FormData directo porque puede venir archivo
        const data = new FormData(form);

        // 🔽 sincronizamos quitar_foto con el checkbox
        const checkQuitarFoto = modal.querySelector('#check_quitar_foto');
        const hiddenQuitarFoto = modal.querySelector('#edit_quitar_foto');

        if (checkQuitarFoto && hiddenQuitarFoto) {
            hiddenQuitarFoto.value = checkQuitarFoto.checked ? '1' : '0';
        }

        fetch('/api/instrumentos/actualizar', {
            method: 'POST', // o POST con override si tu router no acepta PUT multipart
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: data
        })
        .then(window.ajax.response.manejarRespuesta)
        .then(respuesta => {
		           	
		   
           	window.app.ui.mostrarSuccessGENERAL(respuesta.mensaje);

			const instancia = bootstrap.Modal.getInstance(modal);
			if (instancia) instancia.hide();

			recargar_table_instrumento();
           	
        })
        .catch(window.ajax.response.manejarError);

    });
   
}

function limpiarFormularioEditar() {

    const form = document.querySelector('#form_editar_instrumento');
    if (!form) return;

    // 1. Reset básico (inputs, textarea, select)
    form.reset();

    // 2. Quitar estilos de validación Bootstrap
    form.classList.remove('was-validated');

    // 3. Limpiar preview imagen actual
    const imgActual = document.querySelector('#edit_preview_actual');
    const sinFoto = document.querySelector('#edit_sin_foto_actual');

    if (imgActual) {
        imgActual.src = '';
        imgActual.classList.add('d-none');
    }

    if (sinFoto) {
        sinFoto.classList.remove('d-none');
    }

    // 4. Limpiar preview nueva imagen
    const contPreview = document.querySelector('#contenedor_preview_nueva');
    const imgNueva = document.querySelector('#edit_preview_nueva');

    if (contPreview) contPreview.classList.add('d-none');
    if (imgNueva) imgNueva.src = '';

    // 5. Reset checkbox quitar foto
    const check = document.querySelector('#check_quitar_foto');
    const hidden = document.querySelector('#edit_quitar_foto');

    if (check) check.checked = false;
    if (hidden) hidden.value = '0';
}





  /******************************************************
   * EXPONER MÓDULO GLOBAL
   **/
   
  
  window.app = window.app || {};
  app.instrumento = {
    init:   load_instrumento_init,
    reload: recargar_table_instrumento,
  };
  
  	  // Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', () => {
    
    
    var REGISTRO_INSTRUMENTO = document.getElementById('registro_instrumento');
	REGISTRO_INSTRUMENTO.addEventListener('click',function(e){
	
	 window.ajax.url.accederVistaPorAjax('/instrumento/registro/access');
	
	});
	
	window.onLoginSuccess = function (){
		window.app.instrumento.reload();
	}
	console.log("entre reload cargar instrumento");
	
	load_instrumento_init();
    bindModalEditar();
    estadoFoto();
    bindModalEditarCONFIRMAR();
    bindEliminarInstrumento();
    
});
  
  
  
})();  // fin IIFE
