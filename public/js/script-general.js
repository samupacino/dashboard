let modalError = null;
let modalSuccess = null;

function mostrarErrorGENERAL(mensaje) {
    const modalElement = document.querySelector('#modal_error');
    if (!modalElement) return;

    if (!modalError) {
        modalError = bootstrap.Modal.getOrCreateInstance(modalElement);
    }

    document.querySelector('.body_mensaje_error').textContent = mensaje;
    modalError.show();
}

function mostrarSuccessGENERAL(mensaje) {
    const modalElement = document.querySelector('#modal_success');
    if (!modalElement) return;

    if (!modalSuccess) {
        modalSuccess = bootstrap.Modal.getOrCreateInstance(modalElement);
    }

    document.querySelector('.body_mensaje_success').textContent = mensaje;
    modalSuccess.show();
}


// 1. PRIMERO creamos la función global
(function () {

    function accederVistaPorAjax(urlAccess) {
        fetch(urlAccess, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(function (response) {
            const contentType = response.headers.get('content-type') || '';

            if (!contentType.includes('application/json')) {
                throw {
                    status: response.status,
                    body: {
                        mensaje: 'Respuesta no válida del servidor.'
                    }
                };
            }

            return response.json().then(function (body) {
                if (!response.ok) {
                    throw {
                        status: response.status,
                        body: body
                    };
                }

                return {
                    status: response.status,
                    body: body
                };
            });
        })
        .then(function (resultado) {
            const body = resultado.body;

            if (body.status === 'success' && body.data && body.data.redirect) {
                window.location.href = body.data.redirect;
                return;
            }

            mostrarErrorGENERAL(body.mensaje || 'No se pudo redirigir.');
        })
        .catch(function (error) {
            const body = error && error.body ? error.body : {};
            const status = error && error.status ? error.status : 0;

            if (body.status === 'session_expired' || status === 401) {
                mostrarErrorGENERAL(body.mensaje || 'Sesión expirada. Inicie sesión nuevamente.');
                return;
            }

            if (body.status === 'forbidden' || status === 403) {
                mostrarErrorGENERAL(body.mensaje || 'No tienes permisos para acceder.');
                return;
            }

            if (body.status === 'unauthorized') {
                mostrarErrorGENERAL(body.mensaje || 'No autenticado.');
                return;
            }

            mostrarErrorGENERAL(body.mensaje || 'Ocurrió un error al intentar acceder.');
        });
    }

    window.ajax = window.ajax || {};
    window.ajax.url = window.ajax.url || {};
    window.ajax.url.accederVistaPorAjax = accederVistaPorAjax;
	
})();


// 2. DESPUÉS recién asignas eventos
document.addEventListener('DOMContentLoaded', function () {

    const practica = document.getElementById('ingresar_practica');

    if (practica) {
        practica.addEventListener('click', function () {
            window.ajax.url.accederVistaPorAjax('/instrumento/access');
        });
    }

    const ingresarIngles = document.getElementById('ingresar_ingles');

    if (ingresarIngles) {
        ingresarIngles.addEventListener('click', function () {
            window.ajax.url.accederVistaPorAjax('/ingles/access');
        });
    }

    const cerrarSesion = document.getElementById('cerrar_sesion');

    if (cerrarSesion) {
        cerrarSesion.addEventListener('click', function () {
            fetch('/login/logout', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(res => {
                if (res.status === 'logout') {
                    window.location.href = '/login';
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

});
