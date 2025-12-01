

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
				window.location.href = '/'; // 🔁 Redirección automática
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
		
		fetch('/ingles',
			{
				method: 'GET',
				headers: {
					'X-Requested-With': 'XMLHttpRequest' // MUY IMPORTANTE para detectar AJAX
				}
			}
		)
		.then(function(response){
			
			
			const contentType = response.headers.get("content-type");

			if (contentType && contentType.includes("application/json")) {
				return response.json().then(body => {

					if (!response.ok) throw { status: response.status, body: body };
					return { status: response.status, body: body };

				});
			}
		})
		.then(resultado=>{
			console.log(resultado.body.data);
			  // Todo OK → redirigimos desde el navegador
			if (resultado.body.status === 'success' && resultado.body.data && resultado.body.data.redirect) {
				window.location.href = resultado.body.data.redirect
			}
		})
		.catch(error=>{
			
		
			if (error.body.status === 'error' && error.status === 403) {
				mostrarErrorGENERAL(error.body.mensaje || 'No tienes permisos.');
				return;
			}
		})
			

		
		
		
		
		
		
	});
	
	document.addEventListener('click',function(e){
		
		//console.log(e.target);	
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
























