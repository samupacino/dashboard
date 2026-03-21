var practica = document.getElementById('ingresar_practica');
practica.addEventListener('click',function(){
	fetch("/api/practica",{
		method: 'GET',
		headers: {
			'Accept':'Aplication/json',
			'SAMUEL' : 'LUJAN'
		}
	})
	.then(response => {
		
		//const type = response.headers;
		const type = response.headers.get('content-type')??'';
		
		console.log(type);

		return response.json();
	})
	.then(contenido => {
		console.log(contenido);
	})

});

function cerrar_sesion(){
	if(document.getElementById('cerrar_sesion')!==null){

		var cerrar_sesion = document.getElementById('cerrar_sesion');
		cerrar_sesion.addEventListener('click',function(e){

			fetch('/login/logout', {
			method: 'POST',
			headers: { 'Content-Type': 'application/json' }
			})
			.then(response => response.json())
			.then(res => {
			if (res.status === 'logout') {
				//alert(res.mensaje || 'Sesión cerrada');
				window.location.href = '/'; //  Redirección automática
			} else {
				mostrarErrorGENERAL('Respuesta inesperada al cerrar sesión');
			}
			})
			.catch(err => {
			console.error('Error al cerrar sesión:', err);
			mostrarErrorGENERAL('Error inesperado al cerrar sesión');
			});
		});
	}
}
cerrar_sesion();


	var ingresar_ingles = document.getElementById('ingresar_ingles');
	
	ingresar_ingles.addEventListener('click',function(){
		
		fetch('/ingles/access',
			{
				method: 'GET',
				headers: {
					'Accept': 'application/json',
					'X-Requested-With': 'XMLHttpRequest' // MUY IMPORTANTE para detectar AJAX
				}
			}
		)
		.then(function(response){
			
			
			const contentType = response.headers.get("content-type")??'';

		
			
			
			// Si NO es JSON, igual devolvemos algo para no romper el chain
			if (!contentType.includes('application/json')) {
				
			  	if (!response.ok) {
			  		throw { 
							status: response.status, 
							body: { mensaje: 'Respuesta no válida del servidor.' } 
						};
		  		}
		  		
			  	return { status: response.status, body: { status: 'error', mensaje: 'Respuesta no válida del servidor.' } };
			}

			// Si es JSON, lo parseamos y armamos un objeto uniforme
			return response.json().then(body => {
				
			  if (!response.ok){ 
				  throw { status: response.status, body: body };
			  }
			  
			  return { status: response.status, body: body };
			});
			
			
		})
		.then(resultado=>{
		
			const b = resultado.body;
			console.log(b);
			  // Todo OK → redirigimos desde el navegador
			if (b.status === 'success' && b.data?.redirect) {

				window.location.href = b.data.redirect;
				
				return;
			}
			
			 mostrarErrorGENERAL(b.mensaje || 'Acción completada sin redirección.');
			
			
		})
		.catch(error=>{
			
		 	const body = error?.body || {};
			const status = error?.status;

			// Sesión expirada
			if (body.status === 'session_expired' || status === 401) {
			  mostrarErrorGENERAL(body.mensaje || 'Sesión expirada. Inicie sesión nuevamente.');
			  //window.location.assign('/login');
			  return;
			}

			// Sin permisos (rol)
			if (body.status === 'error' && status === 403) {
			  mostrarErrorGENERAL(body.mensaje || 'No tienes permisos.');
			  return;
			}

			// Cualquier otro error
			mostrarErrorGENERAL(body.mensaje || 'Error.');
    
    
		});
			

		
		
		
		
		
		
	});
	
	document.addEventListener('click',function(e){
		
		console.log(e.target);	
	});
	
let modalError = null;
let modalSuccess = null;

function mostrarErrorGENERAL(mensaje) {
    const modalElement = document.querySelector('#modal_error');

    if (!modalError) {
        modalError = bootstrap.Modal.getOrCreateInstance(modalElement);
    }

    document.querySelector('.body_mensaje_error').textContent = mensaje;
    modalError.show();
}

function mostrarSuccessGENERAL(mensaje) {
    const modalElement = document.querySelector('#modal_success');

    if (!modalSuccess) {
        modalSuccess = bootstrap.Modal.getOrCreateInstance(modalElement);
    }

    document.querySelector('.body_mensaje_success').textContent = mensaje;
    modalSuccess.show();
}



















