(function (){


/*
|--------------------------------------------------------------------------
| REGISTRO DE INSTRUMENTO
|--------------------------------------------------------------------------
| Este archivo controla:
| 1. La vista previa de la imagen.
| 2. La validación HTML5 del formulario.
| 3. El envío del formulario con fetch.
| 4. La limpieza del formulario después de registrar.
|--------------------------------------------------------------------------
*/


/*
|--------------------------------------------------------------------------
| 1. ESPERAR QUE EL HTML ESTÉ CARGADO
|--------------------------------------------------------------------------
| DOMContentLoaded asegura que los elementos del HTML ya existan
| antes de buscarlos con getElementById().
|--------------------------------------------------------------------------
*/
document.addEventListener('DOMContentLoaded', function () {

    /*
    |--------------------------------------------------------------------------
    | 2. CAPTURAR ELEMENTOS DEL DOM
    |--------------------------------------------------------------------------
    | Guardamos en constantes los elementos que vamos a usar.
    |--------------------------------------------------------------------------
    */

    const form = document.getElementById('formInstrumento');
    const inputFoto = document.getElementById('foto');
    const preview = document.getElementById('preview');
    const previewWrapper = document.getElementById('previewWrapper');
	const buttonReset = document.getElementById('btnLimpiarInstrumento');

    /*
    |--------------------------------------------------------------------------
    | 3. VALIDAR QUE EXISTA EL FORMULARIO
    |--------------------------------------------------------------------------
    | Si este JS se carga en una vista donde no existe este formulario,
    | evitamos errores en consola.
    |--------------------------------------------------------------------------
    */
    if (!form) return;


    /*
    |--------------------------------------------------------------------------
    | 4. EVENTO PARA MOSTRAR PREVIEW DE LA FOTO
    |--------------------------------------------------------------------------
    | Se ejecuta cuando el usuario selecciona una imagen.
    |--------------------------------------------------------------------------
    */
    inputFoto.addEventListener('change', function (e) {

        // Obtenemos el primer archivo seleccionado.
        const file = e.target.files[0];

        // Si el usuario no seleccionó archivo o lo canceló:
        if (!file) {
            preview.src = '';
            previewWrapper.classList.add('d-none');
            return;
        }

        // Creamos una URL temporal para mostrar la imagen en pantalla.
        preview.src = URL.createObjectURL(file);

        // Mostramos el contenedor de la vista previa.
        previewWrapper.classList.remove('d-none');
    });


    /*
    |--------------------------------------------------------------------------
    | 5. EVENTO RESET DEL FORMULARIO
    |--------------------------------------------------------------------------
    | Se ejecuta cuando el usuario presiona el botón "Limpiar".
    |--------------------------------------------------------------------------
    */
    buttonReset.addEventListener('click', function () {

        /*
        Usamos setTimeout porque el reset del navegador ocurre después
        del evento. Con esto esperamos un instante y luego limpiamos
        también la imagen y las clases Bootstrap.
        */
        setTimeout(limpiarFormularioRegistro, 1);
    });


    /*
    |--------------------------------------------------------------------------
    | 6. EVENTO SUBMIT DEL FORMULARIO
    |--------------------------------------------------------------------------
    | Se ejecuta cuando el usuario presiona "Guardar".
    |--------------------------------------------------------------------------
    */
    
    btnLimpiarInstrumento
    form.addEventListener('submit', function (e) {

        /*
        Evita que el navegador recargue la página.
        Nosotros enviaremos los datos con fetch.
        */
        e.preventDefault();


        /*
        checkValidity() es JavaScript nativo.
        Revisa todos los campos required, maxlength, type, etc.
        */
        const esValido = form.checkValidity();


        /*
        Si el formulario NO es válido:
        - agregamos was-validated
        - Bootstrap pinta los campos inválidos en rojo
        - detenemos el envío con return
        */
        if (!esValido) {
            form.classList.add('was-validated');
            return;
        }


        /*
        FormData toma automáticamente todos los campos del formulario.
        Es ideal porque también permite enviar archivos.
        */
        const formData = new FormData(form);


        /*
        Enviamos los datos al backend.
        No ponemos Content-Type porque FormData lo configura solo.
        */
        fetch('/api/instrumentos/registro', {
            method: 'POST',
            body: formData,
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })

        /*
        Usamos tu manejador global de respuestas.
        Ahí centralizas errores HTTP, JSON inválido, sesión expirada, etc.
        */
        .then(window.ajax.response.manejarRespuesta)

        /*
        Si todo salió bien:
        - mostramos modal de éxito
        - limpiamos formulario
        */
        .then(function (respuesta) {

            window.app.ui.mostrarSuccessGENERAL(respuesta.mensaje);

            limpiarFormularioRegistro();
        })

        /*
        Si algo falla:
        - usamos tu manejador global de errores
        - mostrará el modal de error general
        */
        .catch(window.ajax.response.manejarError);
    });

});


/*
|--------------------------------------------------------------------------
| 7. FUNCIÓN PARA LIMPIAR FORMULARIO
|--------------------------------------------------------------------------
| Esta función se usa:
| - después de registrar correctamente
| - cuando el usuario presiona Limpiar
|--------------------------------------------------------------------------
*/
function limpiarFormularioRegistro() {

    /*
    Volvemos a capturar los elementos aquí porque esta función está
    fuera del DOMContentLoaded.
    */
    const form = document.getElementById('formInstrumento');
    const preview = document.getElementById('preview');
    const previewWrapper = document.getElementById('previewWrapper');


    /*
    Si no existe el formulario, salimos para evitar errores.
    */
    if (!form) return;


    /*
    Limpia todos los campos normales:
    - inputs text
    - textarea
    - select
    - input file
    */
    form.reset();


    /*
    Quita la clase de Bootstrap que pinta validaciones.
    Así el formulario queda limpio visualmente.
    */
    form.classList.remove('was-validated');


    /*
    Limpia la imagen de preview.
    */
    if (preview) {
        preview.src = '';
    }


    /*
    Oculta nuevamente el contenedor del preview.
    */
    if (previewWrapper) {
        previewWrapper.classList.add('d-none');
    }
}
})()
