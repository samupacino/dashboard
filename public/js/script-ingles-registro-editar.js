// ====================================================================
//  Modal EnVocab – Manejo de registro y edición en un solo modal
//  Samuel Luján – Código profesional, limpio y mantenible
// ====================================================================

// Referencias directas a elementos del DOM (se cargan cuando el DOM está listo)
//const form      = document.getElementById('form-en-vocab');
document.addEventListener('DOMContentLoaded', () => {
	
    // ----------------------------------------------------------------
    // Referencias al formulario y al modal
    // ----------------------------------------------------------------
    const modalEl   = document.getElementById('modalEnVocab');
    const form      = document.getElementById('form-en-vocab');
    const btnCancel = document.getElementById('btnCancelEnVocab');

	
	
    // Instancia del modal Bootstrap
    const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);


    // ----------------------------------------------------------------
    // Función: Abrir modal para registrar nuevo vocabulario
    // ----------------------------------------------------------------
    window.abrirModalNuevo = 	function abrirModalNuevo() {

        // Limpia completamente el formulario
        form.reset();

        // Se asegura de que el campo ID esté vacío
        form.id.value = "";

        // Define el modo del formulario
        form.dataset.mode = "create";

        // Define al endpoint para registro
        form.action = "/api/ingles/registro";
        form.dataset.method = "POST";

        // Ajusta el título y el texto del botón
        document.getElementById('modalEnVocabLabel').textContent = "Registrar palabra / expresión";
        form.querySelector('button[type="submit"]').textContent = "GUARDAR";

        // Abre el modal
        modalInstance.show();
    };
    document.getElementById('abrirModal').addEventListener('click', abrirModalNuevo);

    // ----------------------------------------------------------------
    // Botón Cancelar – Limpia el formulario y cierra el modal
    // ----------------------------------------------------------------
    btnCancel.addEventListener('click', () => {
        form.reset();                    // limpiar campos
        form.classList.remove('was-validated'); // limpiar estado visual
        modalInstance.hide();            // cerrar modal
    });



    // ----------------------------------------------------------------
    // Limpieza automática al cerrar el modal (opcional, buena práctica)
    // ----------------------------------------------------------------
    modalEl.addEventListener('hidden.bs.modal', () => {
		
	
        form.reset();
        form.classList.remove('was-validated');
        form.action = "#";
        form.id.value = "";
      
        delete form.dataset.mode;
        delete form.dataset.method;
 
    });

});


   // ----------------------------------------------------------------
    // Función: Abrir modal en modo edición
    //  data → objeto con los valores de la fila (id, english, spanish…)
    // ----------------------------------------------------------------


//Después de inicializar DataTable: OTRA FORMA DE LLENAR TABLA
const tbody = document.querySelector('#ingles tbody');

tbody.addEventListener('click', function (e) {
    const btn = e.target.closest('.btnEditar');
    if (btn) {
		
        const fila = window.dataTables['datatable_ingles'].row(btn.closest('tr')).data();
        //abrirModalEditar(fila);
    }
    
    /*
  EVENT DELEGATION (Delegación de eventos)

  DataTables genera las filas y botones dinámicamente, por lo que 
  NO existen en el DOM cuando cargamos el script.

  Para capturar eventos en elementos creados después, no debemos
  escuchar al elemento en sí (ej. '.btn-edit'), sino a un padre
  que SÍ existe desde el inicio (ej. <tbody>).

  Cuando ocurre un clic:

    1. El clic sucede en el botón dinámico
    2. El evento burbujea hacia sus elementos padres
    3. Llega al <tbody>, que sí tiene el listener
    4. Detectamos si el clic proviene de un .btn-edit usando closest()
    5. Ejecutamos la acción (abrir modal de edición)

  Por eso funciona incluso cuando DataTables reemplaza las filas.
*/

});


function abrirModalEditar(data) {
	
	const form      = document.getElementById('form-en-vocab');
	const modalEl   = document.getElementById('modalEnVocab');
	const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
	
	var inputBusquedaOpuesto   = document.getElementById('opposite_query');  // Input donde escribes el inglés a buscar

		
    // Limpia el formulario antes de cargar datos
    form.reset();

		
    // Modo edición
    form.dataset.mode = "edit";

    // Endpoint para actualizar
    form.action = "/api/ingles/actualizar";
    form.dataset.method = "PUT";
		
     // Cargar valores en los campos del formulario
        //  NOTA: todos los name coinciden con la base de datos
    form.id.value            = data.id;
    form.english.value       = data.english;
    form.pronunciation.value = data.pronunciation ?? "";
    form.spanish.value       = data.spanish;
    form.pos.value           = data.pos;
    form.level.value         = data.level ?? "";
    form.example_en.value    = data.example_en ?? "";
    form.example_es.value    = data.example_es ?? "";
    form.notes.value         = data.notes ?? "";
    form.opposite_id.value   = data.opposite_id ?? "";
    form.source.value        = data.source ?? "";
    form.created_at.value    = data.created_at ?? "(auto)";
    form.updated_at.value    = data.updated_at ?? "(auto)";
    
	inputBusquedaOpuesto.value = data.opposite ?? "";
	
	
        // Ajustar título y botón
    document.getElementById('modalEnVocabLabel').textContent = "Editar palabra / expresión";
    form.querySelector('button[type="submit"]').textContent = "ACTUALIZAR";

        // Abre el modal
    modalInstance.show();
};


/* ============================================================================
   NOTA TÉCNICA: Delegación de eventos en elementos dinámicos
   ----------------------------------------------------------------------------
   Cuando se trabaja con DataTables, modales o elementos creados dinámicamente,
   es fundamental entender cómo funcionan los eventos y la relación DOM–JS.

   📌 1. LOS EVENTOS SÓLO FUNCIONAN SI EL ELEMENTO EXISTE EN EL DOM
   Si llamas:
      document.getElementById("btnEditar").addEventListener(...)
   esto SOLO funcionará si #btnEditar EXISTE al momento de ejecutar el JS.

   Si el elemento NO existe todavía (por ejemplo, porque DataTable lo crea
   dinámicamente), entonces la asignación del evento FALLA.

   Este fue el problema con:
      - El botón de EDITAR dentro del DataTable  ❌ NO funcionaba
      - El modal de Registro SÍ funcionaba (porque ya está en el DOM) ✔️

   ----------------------------------------------------------------------------
   📌 2. ¿POR QUÉ “EDITAR” FALLABA EN TU CASO?
   El botón "Editar" está dentro del DataTable → se genera dinámicamente.
   Tu JS se ejecutaba ANTES de que DataTable insertara ese botón en el DOM.
   Entonces no existía, y por eso el evento no se asignaba.

   Solución profesional:
      - Usar delegación de eventos con document.addEventListener()
      - O con el contenedor de la tabla

   Ejemplo aplicado:
      document.addEventListener("click", (e) => {
         if (e.target.matches(".btn-editar")) {
            abrirModalEditar(data);
         }
      });

   Ahora sí funciona porque el listener está en "document", que ya existe
   desde el inicio, y solo revisa si el click viene desde un .btn-editar,
   aunque se haya creado dinámicamente.

   ----------------------------------------------------------------------------
   📌 3. ¿POR QUÉ EL BUSCADOR “OPPOSITE” SÍ FUNCIONABA?
   Porque el modal (registro/editar) YA EXISTE EN EL DOM al cargar la página.
   El JS que maneja la búsqueda de opposite:
      - Se conecta al input #opposite_query
      - Y este elemento SÍ EXISTE desde el inicio

   Entonces no necesitó delegación de eventos.

   ----------------------------------------------------------------------------
   📌 4. EJEMPLO CLARO BASADO EN TU CASO

      // ❌ Esto falla para botones generados en DataTable
      document.getElementById("btnEditar").addEventListener("click", ...);

      // ✔️ Delegación correcta
      document.addEventListener("click", (e) => {
         if (e.target.closest(".btn-editar")) {
            const data = tabla.row(e.target.closest("tr")).data();
            abrirModalEditar(data);
         }
      });

   ----------------------------------------------------------------------------
   📌 5. IDEA CENTRAL PARA RECORDAR

      ✔ Siempre usar addEventListener directo cuando el elemento YA existe.
      ✔ Usar delegación cuando el elemento se crea dinámicamente:
          - Botones dentro de DataTables
          - Elementos cargados por AJAX
          - Fila expandida (responsive details)
          - Contenido creado por innerHTML
      ✔ Los modales de Bootstrap normalmente están en el DOM desde el inicio,
        por eso sus inputs funcionan sin delegación.

   ----------------------------------------------------------------------------
   Este principio te ayudará a evitar errores cuando mezcles:
      - DataTables dinámicos
      - Modales compartidos (registrar / editar)
      - Formularios con autocompletado dinámico (Opposite)
   ============================================================================ */
	



	
