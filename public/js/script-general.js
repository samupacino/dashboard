

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
				alert('Respuesta inesperada al cerrar sesión');
			}
			})
			.catch(err => {
			console.error('Error al cerrar sesión:', err);
			alert('Error inesperado al cerrar sesión');
			});
		});
	}
}
cerrar_sesion();






















