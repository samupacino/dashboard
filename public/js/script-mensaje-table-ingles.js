(function () {
  "use strict";

  /**********************************************
   * ESTADO PRIVADO
   **********************************************/

  let mensajeTimeout = null;

  /**********************************************
   * FUNCIÓN PRIVADA
   **********************************************/

  function mostrarMensajeDATATABLE(
    texto = "",
    tipo = "success",
    tiempo = 3000
  ) {
    const mensaje = document.getElementById("mensajeDATATABLE");
    if (!mensaje) return;

    // Cancelar ocultado previo
    if (mensajeTimeout) {
      clearTimeout(mensajeTimeout);
    }

    // Limpiar estados
    mensaje.classList.remove("success", "error");

    // Nuevo estado
    mensaje.classList.add(tipo);

    // Texto
    mensaje.textContent = texto;

    // Mostrar
    mensaje.style.display = "block";

    // Ocultar luego
    mensajeTimeout = setTimeout(() => {
      mensaje.style.display = "none";
    }, tiempo);
  }

  /**********************************************
   * FUNCIÓN PRIVADA
   **********************************************/

  function mostrarMensaje_REGISTRO_MODAL(
    texto = "",
    tipo = "success",
    tiempo = 3000
  ) {
    const mensaje = document.getElementById("mensajeREGISTRO_INGLES");
    if (!mensaje) return;

    // Cancelar ocultado previo
    if (mensajeTimeout) {
      clearTimeout(mensajeTimeout);
    }

    // Limpiar estados
    mensaje.classList.remove("success", "error");

    // Nuevo estado
    mensaje.classList.add(tipo);

    // Texto
    mensaje.textContent = texto;

    // Mostrar
    mensaje.style.display = "block";

    // Ocultar luego
    mensajeTimeout = setTimeout(() => {
      mensaje.style.display = "none";
    }, tiempo);
  }

  /**********************************************
   * EXPORTACIÓN GLOBAL CONTROLADA
   **********************************************/

  window.App = window.App || {};

  App.ui = App.ui || {};

  App.ui.mostrarMensajeEnDataTableINGLES = mostrarMensajeDATATABLE;
  App.ui.mostrarMensajeEnDataTable = mostrarMensajeDATATABLE;
  App.ui.mensaje_modal_registro = mostrarMensaje_REGISTRO_MODAL;
  

})();
