// ====================================================================
//  Modal EnVocab – Manejo de registro y edición en un solo modal
//  Samuel Luján – Código modular, limpio y mantenible
// ====================================================================

(function () {

  'use strict';

  document.addEventListener('DOMContentLoaded', () => {

    // ----------------------------------------------------------------
    // Referencias al DOM
    // ----------------------------------------------------------------
    const modalEl   = document.getElementById('modalEnVocab');
    const form      = document.getElementById('form-en-vocab');
    const btnCancel = document.getElementById('btnCancelEnVocab');
    const btnAbrir  = document.getElementById('abrirModal'); // botón “NUEVO” (si existe)

    if (!modalEl || !form) {
      // Si esta vista no tiene el modal, salimos sin romper nada
      return;
    }

    // Instancia única del modal Bootstrap
    const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);

    // Campo de búsqueda de opuestos (solo se usará en edición)
    const inputBusquedaOpuesto = document.getElementById('opposite_query');

    // ----------------------------------------------------------------
    // Función auxiliar: resetear estado del formulario (versión PRO)
    // ----------------------------------------------------------------
    function resetFormState() {
      // 1) Reset de valores
      form.reset();

      // 2) Quitar estado de validación de Bootstrap
      form.classList.remove('was-validated');

      // 3) Quitar clases is-valid / is-invalid de todos los controles
      form
        .querySelectorAll('.is-valid, .is-invalid')
        .forEach(el => {
          el.classList.remove('is-valid', 'is-invalid');
        });

      // 4) Acción y metadatos del formulario
      form.action = '#';
      form.id.value = '';

      delete form.dataset.mode;
      delete form.dataset.method;

      // 5) (Opcional) devolver textos de created/updated
      const createdAt = form.elements['created_at'];
      const updatedAt = form.elements['updated_at'];

      if (createdAt) createdAt.value = '(auto)';
      if (updatedAt) updatedAt.value = '(auto)';

      // 6) Limpiar campo de búsqueda de opuestos
      if (inputBusquedaOpuesto) {
        inputBusquedaOpuesto.value = '';
      }
    }

    // ----------------------------------------------------------------
    // Función: Abrir modal para registrar nuevo vocabulario
    // ----------------------------------------------------------------
    function abrirModalNuevo() {
      resetFormState();

      // Modo creación
      form.dataset.mode   = 'create';
      form.action         = '/api/ingles/registro';
      form.dataset.method = 'POST';

      // Título y botón
      const label = document.getElementById('modalEnVocabLabel');
      if (label) {
        label.textContent = 'Registrar palabra / expresión';
      }

      const botonSubmit = form.querySelector('button[type="submit"]');
      if (botonSubmit) {
        botonSubmit.textContent = 'GUARDAR';
      }

      // Mostrar modal
      modalInstance.show();
    }
    
    function abrirModalNuevoREPEAT() {
      resetFormState();

      // Modo creación
      form.dataset.mode   = 'create';
      form.action         = '/api/ingles/registro';
      form.dataset.method = 'POST';

      // Título y botón
      const label = document.getElementById('modalEnVocabLabel');
      if (label) {
        label.textContent = 'Registrar palabra / expresión';
      }

      const botonSubmit = form.querySelector('button[type="submit"]');
      if (botonSubmit) {
        botonSubmit.textContent = 'GUARDAR';
      }
    }

    // ----------------------------------------------------------------
    // Función: Abrir modal para editar un registro existente
    //   data = objeto con los campos de la fila seleccionada (JSON)
    // ----------------------------------------------------------------
    function abrirModalEditar(data) {
      if (!data || typeof data !== 'object') {
        console.warn('[ModalEnVocab] Datos inválidos para edición:', data);
        return;
      }

      resetFormState();

      // Modo edición
      form.dataset.mode   = 'edit';
      form.action         = '/api/ingles/actualizar';
      form.dataset.method = 'PUT';

      // Cargar datos en los campos (name = campo DB)
      form.id.value            = data.id;
      form.english.value       = data.english ?? '';
      form.pronunciation.value = data.pronunciation ?? '';
      form.spanish.value       = data.spanish ?? '';
      form.pos.value           = data.pos ?? '';
      form.level.value         = data.level ?? '';
      form.example_en.value    = data.example_en ?? '';
      form.example_es.value    = data.example_es ?? '';
      form.notes.value         = data.notes ?? '';
      form.opposite_id.value   = data.opposite_id ?? '';
      form.source.value        = data.source ?? '';
      form.created_at.value    = data.created_at ?? '(auto)';
      form.updated_at.value    = data.updated_at ?? '(auto)';

      if (inputBusquedaOpuesto) {
        inputBusquedaOpuesto.value = data.opposite ?? '';
      }

      // Título y botón
      const label = document.getElementById('modalEnVocabLabel');
      if (label) {
        label.textContent = 'Editar palabra / expresión';
      }

      const botonSubmit = form.querySelector('button[type="submit"]');
      if (botonSubmit) {
        botonSubmit.textContent = 'ACTUALIZAR';
      }

      // Mostrar modal
      modalInstance.show();
    }

    // ----------------------------------------------------------------
    // Eventos de botones y modal
    // ----------------------------------------------------------------

    // Botón “Nuevo” (si existe en esta vista)
    if (btnAbrir) {
      btnAbrir.addEventListener('click', abrirModalNuevo);
    }

    // Botón Cancelar
    if (btnCancel) {
      btnCancel.addEventListener('click', () => {
        resetFormState();
        modalInstance.hide();
      });
    }

    // Limpieza automática al cerrar el modal
    modalEl.addEventListener('hidden.bs.modal', () => {
      resetFormState();
    });

    // ----------------------------------------------------------------
    // Exponer funciones globalmente para otros scripts (DataTable, etc.)
    // ----------------------------------------------------------------
    
    window.App = window.App || {};

  	App.modal = App.modal || {};
  	
    App.modal.abrirModalNuevo        = abrirModalNuevo;
    App.modal.abrirModalREPEAT		  = abrirModalNuevoREPEAT;
    App.modal.abrirModalEditar       = abrirModalEditar;
    App.modal.resetFormularioEnVocab = resetFormState; // ← AQUÍ tu función global “pro”

  });

})();









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
	



	
