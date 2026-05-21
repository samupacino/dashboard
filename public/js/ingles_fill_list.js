

(function () {
  'use strict';

/******************************************************
 * CONFIGURACIÓN Y REFERENCIAS A ELEMENTOS DEL DOM
 ******************************************************/


// Endpoint del backend para buscar por texto (GET ?q=...)
var API_SEARCH_URL = '/api/ingles/search';

// Referencias a los elementos del formulario (ajusta los IDs si cambias tu HTML)
var inputBusquedaOpuesto   = document.getElementById('opposite_query');  // Input donde escribes el inglés a buscar

var selectResultadosOpuesto= document.getElementById('opposite_list');   // Select donde se muestran los resultados
var inputOcultoOppositeId  = document.getElementById('opposite_id');     // Hidden donde guardamos el ID elegido
var divMensajeInvalido     = document.getElementById('oppositeInvalid'); // Mensaje de error/invalidación
var formularioEnVocab      = document.getElementById('form-en-vocab');   // Formulario principal

const modalEl = document.getElementById('modalEnVocab');


// Para ver mejor el flujo en la consola
var MODO_DEBUG = true;

function debugLog(){
  if (!MODO_DEBUG) return;
  var args = Array.prototype.slice.call(arguments);
  console.log.apply(console, args);
}

/******************************************************
 * DEBOUNCE (PARA NO LLAMAR AL SERVIDOR EN CADA TECLA)
 ******************************************************/

// Temporizador global usado por la función debounce
var temporizadorDebounce = null;

/**
 * Crea una función "envuelta" que espera un tiempo antes de ejecutar la original.
 * Si se vuelve a llamar dentro del tiempo de espera, se reinicia el temporizador.
 * @param {Function} funcionOriginal - la función que quieres ejecutar con retraso
 * @param {number} milisegundosEspera - tiempo de espera (por defecto 250ms)
 * @returns {Function}
 */

/************************************************************************************
 * PATRÓN DE "DEBOUNCE" EN JS — NOTAS COMPLETAS PARA EL PROYECTO
 * ----------------------------------------------------------------------------------
 * ¿QUÉ ES "DEBOUNCE"?
 *  - Técnica para retrasar la ejecución de una función hasta que el usuario termine
 *    de disparar eventos de forma repetida (teclear, scroll, resize, etc.).
 *  - Si un nuevo evento ocurre antes de que venza el tiempo, se cancela el anterior
 *    y se reinicia el conteo. Solo se ejecuta la función cuando hay "silencio".
 *
 * ¿POR QUÉ SIRVE?
 *  - Evita saturar el servidor/CPU: ej. no hacer 100 requests mientras el usuario escribe.
 *  - Mejora UX y rendimiento.
 *
 * CONCEPTOS CLAVE (breve glosario):
 *  - Callback: función que se pasa como argumento para ser ejecutada más tarde
 *    (p.ej., un manejador de eventos).
 *  - Event loop: el "motor" de JS que gestiona la cola de tareas, timers y callbacks.
 *  - setTimeout(fn, t): agenda (NO ejecuta ahora) la función "fn" para dentro de "t" ms.
 *  - this (en handlers): en eventos del DOM, el navegador invoca el callback con
 *    "this = elemento que disparó el evento".
 *  - arguments: objeto similar a array con los argumentos con los que se invocó la función.
 *  - .apply(ctx, args): ejecuta una función fijando "this = ctx" y pasando "args" (array-like).
 *  - Closure (clausura): una función "recuerda" (tiene acceso a) variables del entorno
 *    donde fue creada, incluso después de que ese entorno haya terminado de ejecutarse.
 *    Aquí lo usamos para:
 *      (1) recordar el "timer" (para poder cancelarlo entre llamadas),
 *      (2) recordar el "tiempo de espera",
 *      (3) recordar la "función original" que queremos demorar.
 *
 * ORDEN DE EJECUCIÓN (timeline):
 *  1) En arranque/configuración:
 *      var manejador = crearDebounce(funciónOriginal, espera);
 *     → Se ejecuta "crearDebounce" UNA sola vez:
 *          - calcula "espera"
 *          - crea variables internas (timer)
 *          - devuelve "funcionDebounced" (que cierra/recuerda todo lo anterior)
 *
 *  2) Registro del evento:
 *      input.addEventListener('input', manejador);
 *     → El navegador guarda la FUNCIÓN DEVUELTA ("funcionDebounced") como callback.
 *
 *  3) En cada tecla (evento 'input'):
 *      navegador llama: funcionDebounced.call(input, event)
 *     → dentro de funcionDebounced:
 *          - guarda "this" (contexto = input) y "arguments" ([event])
 *          - clearTimeout(timer previo) para cancelar ejecuciones viejas
 *          - setTimeout( ... , espera ) para agendar la ejecución REAL
 *
 *  4) Si pasan "espera" ms sin nuevas teclas:
 *          - se ejecuta la función agendada
 *          - dentro: funcionOriginal.apply(contexto, argumentos)
 *            (preserva "this = input" y pasa el "event" original si lo necesitas)
 *
 * NOTA SOBRE "timer" (temporizador):
 *  - Versión RECOMENDADA: tener un "timer" por instancia de debounce (encapsulado
 *    en el closure). Evita colisiones si usas el debounce en varios inputs.
 *  - Versión con "timer" global: funciona si solo hay un uso, pero puede mezclar
 *    estados si hay varios campos usando el mismo patrón.
 ************************************************************************************/


/* ================================================================================
 * VERSIÓN RECOMENDADA (ENCAPSULADA): cada debounce tiene su propio temporizador
 * ================================================================================ */
function crearDebounce(funcionOriginal, milisegundosEspera) {
  // "espera" se calcula UNA VEZ cuando se crea el debounce (capa de creación)
  var espera = (typeof milisegundosEspera === 'number') ? milisegundosEspera : 250;

  // "temporizadorDebounce" vive dentro del closure: cada instancia tiene el suyo
  var temporizadorDebounce = null;

  // Devolvemos la "función envuelta": esto es lo que el navegador ejecutará en cada evento
  return function funcionDebounced() {
    // CAPTURA DINÁMICA (capa de ejecución por evento):
    // - "this": el elemento que disparó el evento (el input)
    // - "arguments": por lo general [event], pero podrías pasar más cosas
    var contexto   = this;
    var argumentos = arguments;
   

    // Cancelamos un timeout anterior (si el usuario tecleó de nuevo antes de tiempo)
    clearTimeout(temporizadorDebounce);

    // Programamos la ejecución de la función ORIGINAL para dentro de "espera" ms
    temporizadorDebounce = setTimeout(function ejecutar() {
      // Usamos .apply para:
      //  - mantener "this = contexto" (el input)
      //  - pasar los "argumentos" tal cual llegaron (ej. event)

      // 🔹 "args" es un array con todos los parámetros recibidos (ej. [event])
      // 🔹 "apply" mantiene el this correcto y pasa todos los parámetros
      funcionOriginal.apply(contexto,argumentos);
          /************************************************************************************
     * 🔹 MÉTODO .apply() — RESUMEN TÉCNICO
     * ----------------------------------------------------------------------------------
     * Sintaxis:
     *    funcion.apply(thisArg, argsArray)
     *
     * 📘 Qué hace:
     *    - Ejecuta inmediatamente la función indicada.
     *    - Fija manualmente el valor de "this" dentro de la función (primer parámetro).
     *    - Pasa los argumentos de la función en forma de array (segundo parámetro).
     *
     * 🧠 En otras palabras:
     *    .apply() sirve para llamar a una función manteniendo un "this" específico,
     *    incluso si se ejecuta en otro contexto (por ejemplo, dentro de un setTimeout).
     *
     * 📌 Ejemplo:
     *    funcionOriginal.apply(contexto, argumentos);
     *    → Ejecuta la función "funcionOriginal" usando:
     *        this = contexto
     *        parámetros = valores del array "argumentos"
     *
     * 🧩 Uso típico:
     *    - Preservar el "this" correcto al ejecutar funciones asincrónicas.
     *    - Reutilizar funciones con diferentes objetos como contexto.
     ************************************************************************************/

    }, espera);
  };
}

//FIN SAMUEL

/******************************************************
 * FUNCIÓN QUE CONSULTA AL SERVIDOR (PROMESAS)
 ******************************************************/

/**
 * Llama al backend para buscar palabras por coincidencia parcial en "english".
 * Devuelve una promesa que resuelve con un arreglo de objetos: [{id, english}, ...].
 * @param {string} textoBuscado
 * @returns {Promise<Array<{id:number, english:string}>>}
 */
function buscarOpuestosEnServidor(textoBuscado) {
  // Construimos la URL absoluta a partir del origen actual y el path del API
  var urlCompleta = new URL(API_SEARCH_URL, window.location.origin);

  // Si hay texto, lo añadimos como query string: ?q=algo
  if (textoBuscado && textoBuscado.trim() !== '') {
    urlCompleta.searchParams.set('q', textoBuscado.trim());
  }

  debugLog('[buscarOpuestosEnServidor] GET', urlCompleta.toString());

  // Hacemos fetch y devolvemos la promesa
  return fetch(urlCompleta.toString(), {
    method: 'GET',
    headers: { 'Accept': 'application/json' }
  })
  .then(function manejarRespuesta(respuesta) {
    // Si el servidor respondió OK (2xx), intentamos parsear JSON
    if (respuesta.ok) {
      return respuesta.json();
    }

    // Si no fue OK, devolvemos lista vacía para no romper el flujo
    debugLog('[buscarOpuestosEnServidor] Respuesta NO OK:', respuesta.status);
    return [];
  })
  .then(function normalizarDatos(datos) {
	  console.log(datos);
    // Aseguramos que sea un arreglo. Tu API idealmente devuelve un array directamente
    // pero si usas { items: [...] } lo convertimos aquí.
    var lista = Array.isArray(datos) ? datos : (datos.items || []);
    debugLog('[buscarOpuestosEnServidor] Resultados:', lista);
    return lista;
  })
  .catch(function manejarError(error) {
    // En caso de error de red o parseo de JSON
    console.log(error);
    //console.error('[buscarOpuestosEnServidor] Error:', error);
    return [];
  });
}

/******************************************************
 * PINTAR LA LISTA EN EL <SELECT>
 ******************************************************/

/**
 * Limpia el <select> y agrega opciones con los resultados recibidos.
 * @param {Array<{id:number, english:string}>} items
 */
function dibujarResultadosEnSelect(items) {
  // Borramos cualquier opción previa
  selectResultadosOpuesto.innerHTML = '';

  // Si no hay resultados, no agregamos nada (el select queda vacío)
  if (!items || !items.length) {
    return;
  }

  // Por cada ítem, creamos un <option>
  items.forEach(function (item) {
    var opcion = document.createElement('option');

    // El value del <option> será el ID real del registro
    opcion.value = String(item.id);

    // Lo que se muestra al usuario es el texto en inglés
    opcion.textContent = item.english;

    // Agregamos la opción al select
    selectResultadosOpuesto.appendChild(opcion);
  });
}

/******************************************************
 * MANEJADORES DE EVENTOS: ESCRIBIR Y SELECCIONAR
 ******************************************************/

/**
 * Manejador cuando el usuario escribe en el input de búsqueda.
 * Hace debounce para no saturar el servidor, llama al backend
 * y pinta los resultados en el select.
 */


/* ================================================================================
 * EJEMPLO DE USO: buscar mientras el usuario escribe (con "debounce")
 *   - Esta es la función ORIGINAL que queremos ejecutar "con calma".
 *   - OJO: aquí NO se declara parámetros porque leemos del DOM directamente.
 *     Si prefieres, puedes declararla "function (event) { ... }" y usar event.target.value.
 * ================================================================================ */
var manejadorAlTeclear = crearDebounce(function () {
  // Este cuerpo SOLO se ejecuta si el usuario deja de teclear durante "espera" ms.

  // Leemos el texto (vía variable global de referencia al input)
  var texto = '';
  if (inputBusquedaOpuesto && typeof inputBusquedaOpuesto.value === 'string') {
    texto = inputBusquedaOpuesto.value.trim();
  }

    // ====================================================
    // ✨ SI ESTÁ VACÍO → limpiar otro campo automáticamente
    // ====================================================

		inputOcultoOppositeId.value = '';
        // También puedes limpiar el <select>, si quieres:
        // selectOpuestos.innerHTML = '';

        debugLog('Campo vacío → limpiando campo dependiente.');
        //return; // 👈 detenemos aquí (no buscar en el servidor)
 
    
  debugLog('[manejadorAlTeclear] Texto digitado (post-debounce):', texto);

  // Hacemos la búsqueda al servidor y pintamos el select cuando llegue la respuesta
  buscarOpuestosEnServidor(texto)
    .then(function (lista) {
      dibujarResultadosEnSelect(lista);
    })
    .catch(function (err) {
      console.error('Error buscando opposites:', err);
    });

}, 300); // ← "espera" (ms). Se fija UNA VEZ al crear el debounce, luego se recuerda por closure.


/* ================================================================================
 * REGISTRO DEL EVENTO:
 *  - El navegador guardará "manejadorAlTeclear" (función devuelta) como callback.
 *  - En cada tecla, SOLO se ejecuta lo que hay dentro de "funcionDebounced" (la envuelta).
 * ================================================================================ */
// inputBusquedaOpuesto.addEventListener('input', manejadorAlTeclear);


/* ================================================================================
 * (OPCIONAL) VERSIÓN CON TIMER GLOBAL — NO RECOMENDADA SI HAY VARIOS CAMPOS
 *  - Úsala solo si TENDRÁS UN ÚNICO debounce en la página.
 *  - Se deja como referencia para entender la diferencia.
 * ================================================================================
 */
// var temporizadorGlobal = null;
// function manejarInputConDebounceGlobal() {
//   clearTimeout(temporizadorGlobal);
//   temporizadorGlobal = setTimeout(function () {
//     // ... lógica original ...
//   }, 300);
// }
// inputBusquedaOpuesto.addEventListener('input', manejarInputConDebounceGlobal);


/* ================================================================================
 * RESUMEN DIDÁCTICO (por qué funciona):
 *  - "crearDebounce" se ejecuta UNA vez → calcula "espera" y crea "temporizadorDebounce".
//  - Devuelve "funcionDebounced" que CIERRA sobre esas variables (closure).
 *  - "addEventListener" registra ESA función envuelta.
 *  - En cada evento:
 *      * se cancela el timeout anterior,
 *      * se programa uno nuevo,
 *      * si no hay más eventos en "espera" ms → se ejecuta la función original.
 *  - .apply(contexto, argumentos) preserva:
 *      * this = elemento del evento (input),
 *      * los parámetros que llegaron (ej. event).
 * ================================================================================ */



/**
 * Manejador cuando el usuario elige una opción del <select>.
 * Guarda el ID seleccionado en el input hidden y limpia el mensaje de inválido.
 */
function manejadorSeleccionarEnLista() {
  // Si no hay ninguna opción seleccionada (caso extraño), marcamos inválido
  if (!selectResultadosOpuesto || selectResultadosOpuesto.selectedIndex === -1) {
	  
	 
    inputOcultoOppositeId.value = '';
    if (divMensajeInvalido) divMensajeInvalido.style.display = 'block';
    return;
  }

  // Obtenemos la opción seleccionada y extraemos el ID
  var opcionElegida = selectResultadosOpuesto.options[selectResultadosOpuesto.selectedIndex];
  var idElegido = opcionElegida ? opcionElegida.value : '';

  // Guardamos el ID en el hidden
  inputOcultoOppositeId.value = idElegido;

  // Ocultamos el mensaje de inválido si estaba visible
  if (divMensajeInvalido) {
    divMensajeInvalido.style.display = '';
  }

  debugLog('[manejadorSeleccionarEnLista] ID elegido:', idElegido);
}







/******************************************************
 * INICIALIZACIÓN: EVENTOS Y PREFETCH
 ******************************************************/

	
  debugLog('[init] Iniciando wiring de eventos');

  // Si existe el input de búsqueda, conectamos el evento input
  if (inputBusquedaOpuesto) {
    inputBusquedaOpuesto.addEventListener('input', manejadorAlTeclear);
  }

  // Si existe el select de resultados, conectamos el evento change
  if (selectResultadosOpuesto) {
    selectResultadosOpuesto.addEventListener('change', manejadorSeleccionarEnLista);
  }
	
	
	
	
	
		
	modalEl.addEventListener('shown.bs.modal', () => {
				
				
				// Prefetch inicial (opcional)
				buscarOpuestosEnServidor('')
				.then(function (listaInicial) {
				
					dibujarResultadosEnSelect(listaInicial);

				});
			
	});

 

	  // --- Limpieza visual del error custom al escribir o elegir ---
	  if (inputBusquedaOpuesto && divMensajeInvalido) {
		inputBusquedaOpuesto.addEventListener('input', function () {
		  // si borra el texto, ocultamos el feedback custom
		  if (inputBusquedaOpuesto.value.trim() === '') {
			divMensajeInvalido.style.display = 'none';
			// también quitamos estado inválido visual si lo usaste
			selectResultadosOpuesto?.classList.remove('is-invalid');
		  }
		});
	  }
	  
	  if (selectResultadosOpuesto && divMensajeInvalido) {
		selectResultadosOpuesto.addEventListener('change', function () {
		  // si selecciona algo, ocultamos el feedback custom
		  divMensajeInvalido.style.display = 'none';
		  selectResultadosOpuesto.classList.remove('is-invalid');
		});
	  }
	  
	 

	  // Integración con el submit del formulario principal
	  if (formularioEnVocab) {
		  
		formularioEnVocab.addEventListener('submit', function manejarSubmit(evento) {
		  // SIEMPRE: detener envío para validar primero
		  evento.preventDefault();
		  evento.stopPropagation();
		  
		 
		  
	  

		  // 1) Validación nativa Bootstrap 5 (required, pattern, etc.)
		  //    Requiere: <form class="needs-validation" novalidate>
		  var esValidoHTML5 = formularioEnVocab.checkValidity();
		  if (!esValidoHTML5) {
			formularioEnVocab.classList.add('was-validated');
			debugLog('[submit] Validación HTML5/Bootstrap falló');
			return; // no seguimos si hay campos requeridos vacíos o inválidos
		  }	

		  // 2) Regla especial para “opposite”: si escribió algo en el buscador
		  //    pero NO eligió del select, marcamos error custom.
		  var textoDigitado = inputBusquedaOpuesto ? inputBusquedaOpuesto.value.trim() : '';
		  var idOculto      = inputOcultoOppositeId ? inputOcultoOppositeId.value : '';
		  var escribioPeroNoEligio = (textoDigitado !== '' && !idOculto);

		  if (escribioPeroNoEligio) {
			// feedback custom (tu DIV)
			if (divMensajeInvalido) divMensajeInvalido.style.display = 'block';
			// opcional: estilo Bootstrap de inválido al <select>
			if (selectResultadosOpuesto) {
			  selectResultadosOpuesto.classList.add('is-invalid');
			  selectResultadosOpuesto.focus();
			}
			debugLog('[submit] Texto escrito pero sin selección. Validación detenida.');
			return;
		  }

		  // 3) Si todo OK -> recolectar datos y proceder (enviar o lo que prefieras)
		  var datosFormulario = new FormData(formularioEnVocab);
		  var objetoPlano = Object.fromEntries(datosFormulario.entries());
		  //const url = formularioEnVocab.action; // ✅ obtiene la ruta actual (create o update)
		  debugLog('[submit] Datos listos para enviar ✅', objetoPlano);
		  
	   
		  var url = formularioEnVocab.getAttribute('action');
		  
		  
		  var method = formularioEnVocab.dataset.method;
		  
		  
			
		  envio_data(url,objetoPlano,method);
		 
  
		});
	  
	  
	  
	  }
	  


function envio_data(url,data,metodo){
	
	   
      console.log(url);
      console.log(metodo);
    
		fetch(url, {
			method: metodo,
			headers: {
				'Content-Type': 'application/json'
			},
			body: JSON.stringify(data)
		})
		.then(response => {
			const contentType = response.headers.get("content-type");

			if (contentType && contentType.includes("application/json")) {
				return response.json().then(body => {

					if (!response.ok) throw { status: response.status, body: body };
						return { status: response.status, body: body };

				});
			} else {
				return response.text().then(texto => {
						if (!response.ok) throw { status: response.status, body: { mensaje: texto } };
						return { status: response.status, body: { mensaje: texto } };
					});
				}
		})
		.then(resultado => {
	
			App.ingles.reload();
					
			App.ui.mensaje_modal_registro(resultado.body.mensaje);
			
			if(url.includes('registro')){
				App.modal.abrirModalREPEAT();
			}else{
				App.modal.resetFormularioEnVocab();
			}
			
		})
		.catch(err => {
			
			//resetFormularioEnVocab();
			
			if (err.status === 401 && err.body?.status === 'session_expired') {
				actualizarBotonLogin(false);
				//loginModal.style.display = 'flex';                   // [DOM] Mostramos modal de login si expiró sesión
				App.ui.mensaje_modal_registro(err.body.mensaje,'error');
		
			} else if (err.status === 403 && err.body?.status === 'unauthorized'){
			
				//mostrarMensajeEnDataTableINGLES(err.body?.mensaje || "Operación realizada correctamente",'error',7000);
				App.ui.mensaje_modal_registro(err.body.mensaje,'error');
				
			} else {

		
				App.ui.mensaje_modal_registro(err.body?.mensaje || "No se pudo conectar con el servidor",'error');
			}
			
		});		
		
	

}




})();
