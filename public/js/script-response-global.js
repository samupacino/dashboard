(function () {

    /**
     * Procesa la respuesta HTTP del fetch.
     *
     * - Si el servidor respondió JSON:
     *   - si es 2xx => devuelve objeto normalizado
     *   - si no es 2xx => lanza error normalizado
     *
     * - Si el servidor NO respondió JSON:
     *   - lee texto y lanza error normalizado
     */
    function manejarRespuesta(response) {
        const contentType = response.headers.get("content-type");

        // Caso 1: el servidor respondió JSON
        if (contentType && contentType.includes("application/json")) {
            return response.json().then(body => {

                // Si el código HTTP NO es éxito (fuera de 200-299)
                if (!response.ok) {
                    throw {
                        http: response.status,                 // Código HTTP real: 401, 404, 422, etc.
                        status: body.status || 'error',       // Estado lógico de tu backend: fail, unauthorized, etc.
                        mensaje: body.mensaje || 'Ocurrió un error',
                        data: body.data || {},
                        errors: body.errors || {}
                    };
                }

                // Si todo salió bien
                return {
                    http: response.status,
                    status: body.status || 'success',
                    mensaje: body.mensaje || '',
                    data: body.data || {},
                    errors: body.errors || {}
                };
            });
        }

        // Caso 2: el servidor respondió algo que NO es JSON
        // Por ejemplo: HTML de error 500 o texto plano
        return response.text().then(texto => {
            throw {
                http: response.status,
                status: 'error',
                mensaje: texto || 'Respuesta inválida del servidor',
                data: {},
                errors: {}
            };
        });
    }

    /**
     * Maneja todos los errores de forma global.
     *
     * Este método cubre:
     * 1. Errores HTTP lanzados por manejarRespuesta()
     * 2. Errores de red donde fetch nunca recibió response
     */
    function manejarError(err) {

        let titulo = 'Error';
        let mensaje = 'Ocurrió un error inesperado';

        // --------------------------------------------------
        // CASO A: ERROR DE RED / FETCH FALLÓ ANTES DEL RESPONSE
        // --------------------------------------------------
        // Aquí NO existe err.http porque nunca hubo respuesta HTTP
        // Ejemplo: Failed to fetch
        if (!err || typeof err !== 'object' || !('http' in err)) {
            titulo = 'Conexión';
            mensaje = 'No se pudo conectar con el servidor. Verifique su red o intente nuevamente.';

            // Aquí llamas a tu modal global si existe
			if (window.app?.ui?.mostrarErrorGENERAL) {
						
				window.app?.ui?.mostrarErrorGENERAL(mensaje, titulo);
			} else {
                alert(`${titulo}: ${mensaje}`);
            }

            console.error('Error de red o fetch:', err);
            return;
        }

        // --------------------------------------------------
        // CASO B: SÍ HUBO RESPONSE Y YA TENEMOS CÓDIGO HTTP
        // --------------------------------------------------
        switch (err.http) {

            // 400 - solicitud incorrecta
            case 400:
                titulo = 'Solicitud';
                mensaje = err.mensaje || 'La solicitud no es válida.';
                break;

            // 401 - puede ser unauthorized o session_expired
            case 401:
                if (err.status === 'session_expired') {
                    titulo = 'Sesión expirada';
                    window.actualizarBotonLogin(false);
                    mensaje = err.mensaje || 'La sesión expiró. Inicie sesión nuevamente.';
                } else if (err.status === 'unauthorized') {
					window.actualizarBotonLogin(false);
                    titulo = 'No autenticado';
                    mensaje = err.mensaje || 'Debe iniciar sesión para continuar.';
                } else {
                    titulo = 'Acceso';
                    mensaje = err.mensaje || 'No autorizado.';
                }
                break;

            // 403 - sin permisos
            case 403:
                titulo = 'Permisos';
                mensaje = err.mensaje || 'No tiene permisos para realizar esta acción.';
                break;

            // 404 - recurso no encontrado
            case 404:
                titulo = 'No encontrado';
                mensaje = err.mensaje || 'El recurso solicitado no existe.';
                break;

            // 409 - conflicto
            case 409:
                titulo = 'Conflicto';
                mensaje = err.mensaje || 'Existe un conflicto con el recurso.';
                break;

            // 422 - validación
            case 422:
                titulo = 'Validación';
                mensaje = err.mensaje || 'Revise los datos enviados.';

                // Si tu backend mandó errores por campo, los agregamos al mensaje
                if (err.errors && typeof err.errors === 'object' && Object.keys(err.errors).length > 0) {
                    mensaje += '\n\n' + Object.values(err.errors).join('\n');
                }
                break;

            // 500 - error interno
            case 500:
                titulo = 'Servidor';
                mensaje = err.mensaje || 'Ocurrió un error interno del servidor.';
               
                break;

            // Cualquier otro código no contemplado
            default:
                titulo = 'Error';
                mensaje = err.mensaje || `Error no controlado (${err.http}).`;
                break;
        }

        // Mostrar modal global si existe
        
		if (window.app?.ui?.mostrarErrorGENERAL) {
					
			window.app?.ui?.mostrarErrorGENERAL(mensaje, titulo);
		} else {
            alert(`${titulo}: ${mensaje}`);
        }

        console.log('Error capturado:', err);
    }

    // Registro global
    window.ajax = window.ajax || {};
    window.ajax.response = window.ajax.response || {};
    window.app.ui.mostrarSuccessGENERAL
    window.ajax.response.manejarRespuesta = manejarRespuesta;
    window.ajax.response.manejarError = manejarError;

})();
