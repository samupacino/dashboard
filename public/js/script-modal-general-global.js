(function () {

    let modalError = null;
    let modalSuccess = null;

    /**
     * Muestra el modal de error.
     *
     * @param {string} mensaje - Texto del cuerpo del modal.
     * @param {string|null} titulo - Título opcional. Si no se pasa, se mantiene el del HTML.
     */
    function mostrarErrorGENERAL(mensaje, titulo = null) {
        const modalElement = document.querySelector('#modal_error');

        if (!modalElement) {
            console.error('No existe el modal con id #modal_error');
            return;
        }

        if (!modalError) {
            modalError = bootstrap.Modal.getOrCreateInstance(modalElement);
        }

        const bodyMensaje = modalElement.querySelector('.body_mensaje_error');
        if (bodyMensaje) {
            bodyMensaje.textContent = mensaje;
        }

        // Solo cambia el título si se envía uno
        if (titulo) {
            const tituloEl = modalElement.querySelector('.modal-title');
            if (tituloEl) {
                tituloEl.textContent = titulo;
            }
        }

        modalError.show();
    }

    /**
     * Muestra el modal de éxito.
     *
     * @param {string} mensaje - Texto del cuerpo del modal.
     * @param {string|null} titulo - Título opcional. Si no se pasa, se mantiene el del HTML.
     */
    function mostrarSuccessGENERAL(mensaje, titulo = null) {
        const modalElement = document.querySelector('#modal_success');

        if (!modalElement) {
            console.error('No existe el modal con id #modal_success');
            return;
        }

        if (!modalSuccess) {
            modalSuccess = bootstrap.Modal.getOrCreateInstance(modalElement);
        }

        const bodyMensaje = modalElement.querySelector('.body_mensaje_success');
        if (bodyMensaje) {
            bodyMensaje.textContent = mensaje;
        }

        // Solo cambia el título si se envía uno
        if (titulo) {
            const tituloEl = modalElement.querySelector('.modal-title');
            if (tituloEl) {
                tituloEl.textContent = titulo;
            }
        }

        modalSuccess.show();
    }

    /**
     * Cierra el modal de error si está abierto.
     */
    function cerrarErrorGENERAL() {
        const modalElement = document.querySelector('#modal_error');

        if (!modalElement) {
            return;
        }

        if (!modalError) {
            modalError = bootstrap.Modal.getOrCreateInstance(modalElement);
        }

        modalError.hide();
    }

    /**
     * Cierra el modal de éxito si está abierto.
     */
    function cerrarSuccessGENERAL() {
        const modalElement = document.querySelector('#modal_success');

        if (!modalElement) {
            return;
        }

        if (!modalSuccess) {
            modalSuccess = bootstrap.Modal.getOrCreateInstance(modalElement);
        }

        modalSuccess.hide();
    }

    // Namespace global
    window.app = window.app || {};
    window.app.ui = window.app.ui || {};

    // Registrar funciones globales
    window.app.ui.mostrarErrorGENERAL = mostrarErrorGENERAL;
    window.app.ui.mostrarSuccessGENERAL = mostrarSuccessGENERAL;
    window.app.ui.cerrarErrorGENERAL = cerrarErrorGENERAL;
    window.app.ui.cerrarSuccessGENERAL = cerrarSuccessGENERAL;

})();
