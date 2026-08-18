<?php

namespace app\Core;

use PDO;

/**
 * DatatableIngles (motor genérico para DataTables Server-Side)
 *
 * ✅ Soporta:
 * - Paginación (start, length)
 * - Ordenamiento (order[column], order[dir])
 * - Búsqueda global (search[value])
 * - JOINs (INNER / LEFT / RIGHT) y relaciones 1-1, 1-N, N-1
 * - Columnas con alias en SELECT (ej: "b.english AS opposite")
 *
 * ⚠️ Nota clave:
 * - En SQL NO se puede usar el alias del SELECT dentro del WHERE del mismo nivel.
 *   Por eso, si detectamos "b.english AS opposite", en el WHERE usamos "b.english".
 *
 * ✅ Conteos robustos:
 * - recordsTotal y recordsFiltered se calculan con COUNT(DISTINCT pk)
 *   para evitar conteos inflados cuando un JOIN multiplica filas (caso 1-N).
 */
class DatatableIngles
{
    /** Conexión PDO (se inyecta desde tu App/Controller) */
    private PDO $pdo;

    /**
     * "tabla" en realidad es el FROM completo (puede incluir JOINs y alias)
     * Ej: 'en_vocab a LEFT JOIN en_vocab b ON a.opposite_id = b.id'
     */
    private string $tabla;

    /**
     * Columnas que se seleccionan (SELECT).
     * Pueden incluir alias: 'b.english AS opposite'
     */
    private array $columnas;

    /**
     * PK (clave principal) de la entidad que representa UNA fila del DataTable.
     * Ej:
     * - Si listamos palabras: 'a.id'
     * - Si listamos calibraciones (hijos): 'c.id'
     */
    private string $pk;
    
    
    
    
     /**
     * Opcional: columnas sobre las que se hará la búsqueda global.
     * Si es null, se usa $columnas (comportamiento actual).
     *
     * Importante: aquí se recomienda poner columnas "reales" (sin alias),
     * por ejemplo: 'b.english' en vez de 'b.english AS opposite'
     */
    private ?array $searchCols;
    
    
		/**
	 * Opcional: columnas válidas para ORDER BY.
	 * Si es null, se usa la lógica basada en $columnas.
	 *
	 * Importante:
	 * - Debe respetar el mismo índice que DataTables envía desde el frontend.
	 * - Se recomienda usar columnas reales, no alias.
	 */

    private ?array $orderCols;
/**
 * Constructor del DataTable genérico.
 *
 * @param PDO $pdo
 * Conexión activa a base de datos.
 *
 * @param string $tabla
 * Cláusula FROM completa. Puede incluir alias y JOINs.
 * Ejemplo: 'instrumentos i LEFT JOIN plataformas p ON i.plataforma_id = p.id'
 *
 * @param array $columnas
 * Lista de columnas que formarán el SELECT.
 * Puede incluir alias y expresiones SQL.
 *
 * @param string $pk
 * Clave primaria real de la entidad principal listada.
 * Se usa para conteos robustos con COUNT(DISTINCT ...).
 *
 * @param array|null $searchCols
 * Columnas reales permitidas para búsqueda global.
 * No se recomienda usar alias aquí.
 *
 * @param array|null $orderCols
 * Columnas reales permitidas para ORDER BY.
 * Deben corresponder al orden de columnas enviado por DataTables.
 */
    public function __construct(PDO $pdo, string $tabla, array $columnas, string $pk, ?array $searchCols = null, ?array $orderCols = null)
    {
        // Guardamos la conexión a BD
        $this->pdo = $pdo;

        // Guardamos el FROM completo (con JOINs si aplica)
        $this->tabla = $tabla;

        // Guardamos la lista de columnas del SELECT
        $this->columnas = $columnas;

        // Guardamos la PK para conteos robustos
        $this->pk = $pk;
        
        
        $this->searchCols = $searchCols; // puede ser null
        
        
        $this->orderCols = $orderCols;
        
        
/*
===============================================================================
📘 CONFIGURACIÓN DEL DATATABLE — PARÁMETROS DEL CONSTRUCTOR
===============================================================================

pdo → Conexión a base de datos (PDO)
------------------------------------
Definición:
    Objeto PDO activo que se usará para ejecutar todas las consultas SQL.

Ejemplo:
    $pdo = new PDO('mysql:host=localhost;dbname=test', 'user', 'pass');

Notas:
    • Debe ser una conexión válida y abierta.
    • Se recomienda usar modo de errores por excepción.
    • Toda la clase depende de esta conexión.


tabla → FROM completo (con JOINs)
---------------------------------
Definición:
    Cadena SQL EXACTA que irá después de FROM.
    Puede contener alias y cualquier tipo de JOIN.

Ejemplo simple:
    $tabla = 'usuarios u';

Ejemplo con JOIN:
    $tabla = '
        instrumentos i
        LEFT JOIN plataformas p ON i.plataforma_id = p.id
        LEFT JOIN areas a ON i.area_id = a.id
    ';

Notas:
    • Aquí se definen TODAS las relaciones.
    • La clase NO construye JOINs automáticamente.
    • Funciona con INNER, LEFT, RIGHT, etc.
    • Puede incluir consultas complejas.


columnas → Columnas del SELECT
------------------------------
Definición:
    Lista de columnas que se mostrarán en el DataTable.

Ejemplo:
    $columnas = [
        'i.id',
        'i.tag',
        'i.descripcion',
        'p.nombre AS plataforma',
        'a.nombre AS area'
    ];

Notas:
    • Se usan para construir el SELECT.
    • Pueden incluir alias (AS).
    • Pueden incluir funciones SQL.
    • Deben corresponder a las columnas del frontend.


pk → Clave primaria de la entidad listada
-----------------------------------------
Definición:
    Columna que identifica UNA FILA del DataTable.
    Se usa para conteos robustos con COUNT(DISTINCT).

Ejemplo:
    $pk = 'i.id';

Por qué es importante:
    Los JOINs pueden multiplicar filas (relaciones 1-N).

Ejemplo problema:
    Instrumento con 3 calibraciones → 3 filas en JOIN.

Solución:
    COUNT(DISTINCT i.id) cuenta solo instrumentos únicos.

Regla de oro:
    pk = la entidad principal que estás listando.


searchCols → Columnas donde se aplica la búsqueda (opcional)
-----------------------------------------------------------
Definición:
    Lista de columnas REALES donde se permitirá buscar texto.

Ejemplo:
    $searchCols = [
        'i.tag',
        'i.descripcion',
        'p.nombre'
    ];

Notas:
    • No usar alias aquí.
    • Se usan en el WHERE con LIKE.
    • Si es null, la clase puede usar columnas por defecto.
    • Mejora rendimiento y evita errores con alias.


orderCols → Columnas válidas para ORDER BY (opcional)
----------------------------------------------------
Definición:
    Lista de columnas REALES que se pueden usar para ordenar.

Ejemplo:
    $orderCols = [
        'i.id',
        'i.tag',
        'i.descripcion',
        'p.nombre'
    ];

Notas:
    • Debe coincidir con el orden de columnas del DataTable.
    • Evita ordenar por alias o expresiones.
    • Previene SQL inválido o inseguro.
    • Recomendado siempre definirlo.


===============================================================================
🧠 RESUMEN RÁPIDO
===============================================================================

pdo        → conexión a BD
tabla      → FROM completo con JOINs
columnas   → columnas del SELECT
pk         → identificador único de la fila listada
searchCols → columnas donde buscar texto
orderCols  → columnas permitidas para ordenar

===============================================================================
*/
    }

    /**
     * Método principal:
     * - Lee parámetros de DataTables (GET)
     * - Construye SQL dinámico (WHERE/ORDER/LIMIT)
     * - Retorna array con formato que DataTables espera
     */
    public function procesar(?array $request = null): array
    {
        // ============================================================
        // 0) MINI APUNTE (GENÉRICO, PARA CUALQUIER JOIN / RELACIÓN)
        // ============================================================
        /*
          ✅ Nota de diseño genérico:
          - recordsTotal/recordsFiltered se calculan con COUNT(DISTINCT pk) para que funcione con:
            1-1, 1-N, N-1 y cualquier JOIN (INNER/LEFT/RIGHT), evitando conteos inflados cuando el JOIN multiplica filas.

          - El valor de $pk debe ser la PK de la entidad que representa UNA fila en el DataTable:
            * Si listamos "padres" -> pk = padre.id
            * Si listamos "hijos/eventos" (padre repetido) -> pk = hijo.id
        */

        // ============================================================
        // 1) Leer parámetros enviados por DataTables (GET o request manual)
        // ============================================================
        
        /*
			Desacoplamiento del entorno HTTP:

			- Si procesar() recibe un arreglo, se usa como request manual.
			- Si no, se usa $_GET (modo normal con DataTables).

			Esto permite:
			✔ probar el backend sin frontend
			✔ usar la clase en scripts o tests
			✔ evitar dependencia directa del navegador
		*/
		
		/*
    Fuente de datos del request:
    - Si se pasa un array manual, se usa ese array.
    - Si no, se intenta usar $_POST (modo DataTables con ajax POST).
    - Si $_POST está vacío, se usa $_GET como respaldo.
		*/

        // Si no se pasa un arreglo manual, usamos $_GET por defecto
		$request = $request ?? ($_POST ?: $_GET);

        // draw: contador que DataTables usa para sincronizar solicitudes/respuestas
        $draw = (int)($request['draw'] ?? 1);

        // start: offset inicial (desde qué registro empezar)
        $start = max(0, (int)($request['start'] ?? 0));

        // length: cuántos registros devolver por página
		$length = (int)($request['length'] ?? 5);

		if ($length < 1) {
			$length = 5;
		}

		if ($length > 100) {
			$length = 100;
		}

        // search[value]: búsqueda global (un solo input en DataTables)
        // trim() para evitar espacios fantasma ("   ")
        $search = trim($request['search']['value'] ?? '');
		
	
        // order[0][column]: índice de columna a ordenar (DataTables manda índice, no nombre)
        $orderColumnIndex = (int)($request['order'][0]['column'] ?? 0);
	  
		
				
				
        // order[0][dir]: dirección (asc/desc)
        // Blindaje: solo permitimos ASC o DESC
        $orderDir = strtolower($request['order'][0]['dir'] ?? 'asc');
        $orderDir = ($orderDir === 'desc') ? 'DESC' : 'ASC';

        // ============================================================
        // 2) Determinar la columna para ORDER BY (soporta alias)
        // ============================================================


		/*
		 * ============================================================
		 * DETERMINACIÓN DE LA COLUMNA PARA ORDER BY
		 * ============================================================
		 *
		 * DataTables envía el índice de la columna que el usuario
		 * seleccionó para ordenar mediante:
		 *
		 * order[0][column]
		 *
		 * Ese índice se encuentra almacenado en:
		 *
		 * $orderColumnIndex
		 *
		 * Ejemplo:
		 *
		 * DataTables:
		 * 0 = id
		 * 1 = tag
		 * 2 = descripcion
		 * 3 = tipo
		 * 4 = planta
		 *
		 * Si existe $this->orderCols, este arreglo funciona como una
		 * whitelist de las columnas que están permitidas para ordenar.
		 *
		 * Ejemplo:
		 *
		 * $this->orderCols = [
		 *     0 => 'i.id',
		 *     1 => 'i.tag',
		 *     4 => 'p.nombre AS planta'
		 * ];
		 *
		 * Las claves (0, 1, 4) deben coincidir con los índices reales
		 * de las columnas mostradas en DataTables.
		 */


		/*
		 * Inicializamos la columna de ordenamiento en null.
		 *
		 * Si finalmente permanece en null significa que la columna
		 * seleccionada por el usuario NO está permitida para ordenar
		 * y, por lo tanto, no se deberá generar ORDER BY.
		 */
		/*
		 * ============================================================
		 * DETERMINAR LA COLUMNA PARA EL ORDER BY
		 * ============================================================
		 *
		 * DataTables envía el índice de la columna que el usuario
		 * seleccionó para ordenar.
		 *
		 * Ejemplo:
		 * order[0][column] = 4
		 *
		 * Ese índice se encuentra guardado en:
		 * $orderColumnIndex
		 */


		/*
		 * Guarda la columna tal como viene desde $orderCols o $columnas.
		 *
		 * Se inicializa en null porque todavía no sabemos si el índice
		 * enviado por DataTables corresponde a una columna válida.
		 *
		 * Ejemplo de un valor que podría recibir:
		 * "p.nombre AS planta"
		 */
		$orderColumnRaw = null;


		/*
		 * Guarda la columna final que se utilizará en ORDER BY.
		 *
		 * También comienza en null.
		 *
		 * Si al terminar esta lógica continúa siendo null,
		 * significa que no existe una columna válida para ordenar
		 * y posteriormente NO se agregará ORDER BY al SQL.
		 *
		 * Ejemplo de valor final:
		 * "planta"
		 */
		$orderColumn = null;


		/*
		 * Verificamos si $orderCols contiene elementos.
		 *
		 * !empty() será true cuando se haya definido una lista
		 * específica de columnas permitidas para ordenar.
		 *
		 * Ejemplo:
		 *
		 * $this->orderCols = [
		 *     0 => 'i.id',
		 *     1 => 'i.tag',
		 *     4 => 'p.nombre AS planta'
		 * ];
		 */
		if (!empty($this->orderCols)) {

			/*
			 * Verificamos si el índice enviado por DataTables
			 * existe como clave dentro de $orderCols.
			 *
			 * Ejemplo:
			 *
			 * $orderColumnIndex = 4;
			 *
			 * array_key_exists(4, $this->orderCols)
			 *
			 * devuelve true si existe la clave 4.
			 *
			 * Esto permite que $orderCols funcione como una lista
			 * de columnas autorizadas para ordenar.
			 */
			if (array_key_exists($orderColumnIndex, $this->orderCols)) {

				/*
				 * Obtenemos la columna correspondiente al índice
				 * enviado por DataTables.
				 *
				 * Ejemplo:
				 *
				 * $this->orderCols[4] = 'p.nombre AS planta';
				 *
				 * entonces:
				 *
				 * $orderColumnRaw = 'p.nombre AS planta';
				 */
				$orderColumnRaw = $this->orderCols[$orderColumnIndex];
			}

		} else {

			/*
			 * Si $orderCols está vacío o es null, significa que
			 * no existe una lista específica de columnas permitidas.
			 *
			 * En ese caso usamos directamente $this->columnas,
			 * que contiene las columnas utilizadas en el SELECT.
			 */


			/*
			 * Verificamos si el índice enviado por DataTables
			 * existe dentro de $this->columnas.
			 *
			 * isset() evita intentar acceder a una posición
			 * inexistente del arreglo.
			 */
			if (isset($this->columnas[$orderColumnIndex])) {

				/*
				 * Obtenemos la columna del SELECT correspondiente
				 * al índice enviado por DataTables.
				 *
				 * Ejemplo:
				 *
				 * $this->columnas[2] = 'i.descripcion';
				 *
				 * entonces:
				 *
				 * $orderColumnRaw = 'i.descripcion';
				 */
				$orderColumnRaw = $this->columnas[$orderColumnIndex];
			}
		}


		/*
		 * Si $orderColumnRaw continúa siendo null significa que:
		 *
		 * - El índice no estaba permitido en $orderCols, o
		 * - El índice no existía en $this->columnas.
		 *
		 * Por eso solamente continuamos si encontramos
		 * una columna válida.
		 */
		if ($orderColumnRaw !== null) {

			/*
			 * Verificamos si la columna contiene un alias mediante AS.
			 *
			 * stripos() busca texto sin diferenciar
			 * mayúsculas y minúsculas.
			 *
			 * Ejemplo:
			 *
			 * "p.nombre AS planta"
			 *
			 * contiene " AS ", por lo tanto entra al if.
			 */
			if (stripos($orderColumnRaw, ' AS ') !== false) {

				/*
				 * Separamos la columna real de su alias.
				 *
				 * Ejemplo:
				 *
				 * "p.nombre AS planta"
				 *
				 * se convierte en:
				 *
				 * $partes[0] = "p.nombre"
				 * $partes[1] = "planta"
				 *
				 * La "i" de la expresión regular hace que
				 * no importe si viene AS, as, As, etc.
				 */
				$partes = preg_split('/\s+AS\s+/i', $orderColumnRaw);


				/*
				 * Para ORDER BY utilizamos el alias.
				 *
				 * Ejemplo:
				 *
				 * SELECT p.nombre AS planta
				 *
				 * entonces podemos utilizar:
				 *
				 * ORDER BY planta
				 *
				 * trim() elimina posibles espacios sobrantes.
				 */
				$orderColumn = trim($partes[1]);

			} else {

				/*
				 * Si la columna NO tiene alias, no necesitamos
				 * realizar ninguna transformación.
				 *
				 * Ejemplo:
				 *
				 * $orderColumnRaw = "i.tag"
				 *
				 * entonces:
				 *
				 * $orderColumn = "i.tag"
				 *
				 * y posteriormente:
				 *
				 * ORDER BY i.tag ASC
				 */
				$orderColumn = $orderColumnRaw;
			}
		}


		/*
		 * ============================================================
		 * PROCESAMIENTO DEL ALIAS DE LA COLUMNA
		 * ============================================================
		 *
		 * Esta parte se ejecuta independientemente de si la columna
		 * provino de:
		 *
		 *     $this->orderCols
		 *
		 * o de:
		 *
		 *     $this->columnas
		 *
		 * De esta manera ambos casos soportan columnas con alias.
		 */
		if ($orderColumnRaw !== null) {

			/*
			 * Verificamos si la expresión contiene " AS ".
			 *
			 * stripos() realiza una búsqueda sin distinguir
			 * mayúsculas y minúsculas.
			 *
			 * Por lo tanto reconoce:
			 *
			 * AS
			 * as
			 * As
			 * aS
			 *
			 * Ejemplo:
			 *
			 * 'p.nombre AS planta'
			 */
			if (stripos($orderColumnRaw, ' AS ') !== false) {

				/*
				 * Separamos la expresión utilizando AS.
				 *
				 * Ejemplo:
				 *
				 * 'p.nombre AS planta'
				 *
				 * se convierte en:
				 *
				 * [
				 *     0 => 'p.nombre',
				 *     1 => 'planta'
				 * ]
				 *
				 * La expresión regular permite reconocer AS
				 * independientemente de mayúsculas/minúsculas
				 * y de la cantidad de espacios alrededor.
				 */
				$partes = preg_split('/\s+AS\s+/i', $orderColumnRaw);

				/*
				 * Para ORDER BY utilizamos el alias.
				 *
				 * Ejemplo:
				 *
				 * SELECT p.nombre AS planta
				 *
				 * permite posteriormente:
				 *
				 * ORDER BY planta
				 *
				 * trim() elimina posibles espacios adicionales.
				 *
				 * Si por alguna razón no existiera $partes[1],
				 * utilizamos $partes[0] como respaldo.
				 */
				$orderColumn = trim($partes[1] ?? $partes[0]);

			} else {

				/*
				 * Si la columna no tiene alias, se utiliza
				 * directamente la expresión original.
				 *
				 * Ejemplo:
				 *
				 * 'i.tag'
				 *
				 * produce:
				 *
				 * ORDER BY i.tag
				 */
				$orderColumn = $orderColumnRaw;
			}
		}
        
        //echo json_encode(["samuel" => $orderColumn, "dir" => $orderDir, "TEST"=>$probando]);
   

        // ============================================================
        // 3) recordsTotal (conteo sin filtros)
        // ============================================================

        /**
         * Antes lo hacías con subconsulta COUNT(*) FROM (SELECT ... ) sub
         * Ahora usamos COUNT(DISTINCT pk), que es:
         * ✅ Correcto para 1-1, 1-N, N-1
         * ✅ Correcto con cualquier JOIN
         * ✅ Evita conteos inflados por multiplicación de filas en JOIN 1-N
         */
        $sqlTotal = "SELECT COUNT(DISTINCT {$this->pk}) AS total FROM {$this->tabla}";
        $stmtTotal = $this->pdo->query($sqlTotal);
        $recordsTotal = (int)$stmtTotal->fetch(PDO::FETCH_ASSOC)['total'];

        // ============================================================
        // 4) Construcción dinámica del WHERE + parámetros
        // ============================================================

        // WHERE vacío por defecto
        $where = '';

        // Parámetros para PDO (bind)
        $params = [];

        // IMPORTANTE: usamos ($search !== '') y no empty()
        // porque empty("0") es true y no debería ignorarse una búsqueda "0".
        if ($search !== '') {

            /*
                SI EXISTE VALOR A BUSCAR ENTRA AQUI PARA ARMAR SQL Y PARAMETROS:
                ASI LO ARMA PARA MI TABLA: ->SI TIENES ALIAS LO ELIMINA PARA USAR EN WHERE
                Lo partes en:
                    p.nombre → para usar en WHERE
                    escalon  → para mostrar en SELECT

                Ejemplo conditions:
                [
                  "a.english LIKE :search",
                  "a.spanish LIKE :search",
                  "b.english LIKE :search"
                ]
            */

			/*
			 * ============================================================
			 * CONSTRUCCIÓN DE LAS CONDICIONES PARA LA BÚSQUEDA GLOBAL
			 * ============================================================
			 *
			 * DataTables envía el texto ingresado en el buscador global.
			 *
			 * Para realizar la búsqueda necesitamos construir condiciones
			 * SQL con LIKE para cada columna donde se permita buscar.
			 *
			 * Ejemplo del resultado que queremos construir:
			 *
			 * WHERE i.tag LIKE :search
			 *    OR i.descripcion LIKE :search
			 *    OR p.nombre LIKE :search
			 */


			/*
			 * Creamos un arreglo vacío donde iremos guardando
			 * cada condición LIKE de la búsqueda.
			 *
			 * Ejemplo final:
			 *
			 * $conditions = [
			 *     'i.tag LIKE :search',
			 *     'i.descripcion LIKE :search',
			 *     'p.nombre LIKE :search'
			 * ];
			 */
			$conditions = [];


			/*
			 * Determinamos qué columnas se utilizarán para buscar.
			 *
			 * Si $this->searchCols tiene columnas definidas,
			 * utilizamos solamente esas columnas.
			 *
			 * Si searchCols es null o está vacío [],
			 * utilizamos todas las columnas de $this->columnas.
			 *
			 * Ejemplo:
			 *
			 * $this->searchCols = [
			 *     'i.tag',
			 *     'i.descripcion',
			 *     'p.nombre'
			 * ];
			 *
			 * En ese caso $colsParaBuscar tendrá esas tres columnas.
			 */
			$colsParaBuscar = !empty($this->searchCols)
				? $this->searchCols
				: $this->columnas;


			/*
			 * Recorremos una por una las columnas donde
			 * se realizará la búsqueda.
			 *
			 * En cada vuelta, $col contiene una columna.
			 *
			 * Ejemplo:
			 *
			 * Primera vuelta:
			 * $col = 'i.tag'
			 *
			 * Segunda vuelta:
			 * $col = 'i.descripcion'
			 *
			 * Tercera vuelta:
			 * $col = 'p.nombre'
			 */
			foreach ($colsParaBuscar as $col) {

				/*
				 * Por defecto consideramos que la columna recibida
				 * puede utilizarse directamente en el WHERE.
				 *
				 * Ejemplo:
				 *
				 * $col = 'i.tag'
				 *
				 * entonces:
				 *
				 * $colReal = 'i.tag'
				 */
				$colReal = $col;


				/*
				 * Verificamos si la columna contiene un alias mediante AS.
				 *
				 * Esto es necesario porque un alias definido en SELECT
				 * no debe utilizarse directamente en el WHERE del mismo nivel.
				 *
				 * Ejemplo:
				 *
				 * p.nombre AS planta
				 *
				 * Para el SELECT podemos utilizar:
				 *
				 * SELECT p.nombre AS planta
				 *
				 * Pero para buscar debemos utilizar la columna real:
				 *
				 * WHERE p.nombre LIKE :search
				 *
				 * stripos() busca " AS " sin diferenciar entre
				 * mayúsculas y minúsculas.
				 */
				if (stripos($col, ' AS ') !== false) {

					/*
					 * Separamos la columna real de su alias.
					 *
					 * Ejemplo:
					 *
					 * p.nombre AS planta
					 *
					 * se convierte en:
					 *
					 * $partes[0] = 'p.nombre'
					 * $partes[1] = 'planta'
					 *
					 * La expresión regular también reconoce:
					 *
					 * AS
					 * as
					 * As
					 *
					 * y permite uno o más espacios alrededor de AS.
					 */
					$partes = preg_split('/\s+AS\s+/i', $col);


					/*
					 * Para el WHERE necesitamos únicamente
					 * la columna real, que está en la posición 0.
					 *
					 * Ejemplo:
					 *
					 * $partes[0] = 'p.nombre'
					 *
					 * entonces:
					 *
					 * $colReal = 'p.nombre'
					 *
					 * trim() elimina posibles espacios sobrantes.
					 */
					$colReal = trim($partes[0]);
				}


				/*
				 * Verificamos que después de procesar la columna
				 * exista realmente un nombre de columna.
				 *
				 * Esto evita agregar una condición vacía.
				 */
				if ($colReal !== '') {

					/*
					 * Agregamos la condición LIKE al arreglo.
					 *
					 * Ejemplo:
					 *
					 * Si:
					 *
					 * $colReal = 'p.nombre'
					 *
					 * se agrega:
					 *
					 * 'p.nombre LIKE :search'
					 *
					 * Después todas estas condiciones serán unidas
					 * mediante OR para construir el WHERE global.
					 */
					$conditions[] = "$colReal LIKE :search";
				}
			}

			/*
			 * ============================================================
			 * IMPORTANTE: USO DE ALIAS EN LA CLÁUSULA WHERE
			 * ============================================================
			 *
			 * Un alias creado en el SELECT no puede utilizarse en el WHERE
			 * de la misma consulta.
			 *
			 * El motivo está en el ORDEN LÓGICO en que SQL procesa las
			 * diferentes partes de una consulta.
			 *
			 * Aunque nosotros escribimos normalmente:
			 *
			 * SELECT ...
			 * FROM ...
			 * WHERE ...
			 * ORDER BY ...
			 *
			 * SQL no procesa estas cláusulas exactamente en ese orden.
			 *
			 * De forma simplificada, el orden lógico es:
			 *
			 * 1. FROM       -> obtiene las tablas y realiza los JOIN.
			 * 2. WHERE      -> filtra las filas.
			 * 3. SELECT     -> selecciona las columnas y crea sus alias.
			 * 4. ORDER BY   -> ordena el resultado.
			 *
			 * Por lo tanto, cuando SQL está procesando el WHERE,
			 * el SELECT todavía no ha sido evaluado y el alias todavía
			 * NO EXISTE.
			 *
			 * ------------------------------------------------------------
			 * EJEMPLO INCORRECTO:
			 * ------------------------------------------------------------
			 *
			 * SELECT p.nombre AS planta
			 * FROM plantas p
			 * WHERE planta LIKE '%VPSA%';
			 *
			 * Esto es incorrecto porque cuando se procesa:
			 *
			 * WHERE planta LIKE '%VPSA%'
			 *
			 * el alias "planta" todavía no ha sido creado.
			 *
			 * El alias recién se crea posteriormente cuando SQL procesa:
			 *
			 * SELECT p.nombre AS planta
			 *
			 * ------------------------------------------------------------
			 * FORMA CORRECTA:
			 * ------------------------------------------------------------
			 *
			 * En el WHERE debemos utilizar la columna REAL:
			 *
			 * SELECT p.nombre AS planta
			 * FROM plantas p
			 * WHERE p.nombre LIKE '%VPSA%';
			 *
			 * Por esta razón, cuando una columna llega con alias:
			 *
			 *     p.nombre AS planta
			 *
			 * esta clase elimina el alias antes de construir el WHERE
			 * y utiliza solamente:
			 *
			 *     p.nombre
			 *
			 * generando finalmente:
			 *
			 *     WHERE p.nombre LIKE :search
			 *
			 * ------------------------------------------------------------
			 * ¿POR QUÉ EL ALIAS SÍ PUEDE USARSE EN ORDER BY?
			 * ------------------------------------------------------------
			 *
			 * ORDER BY se procesa después del SELECT.
			 *
			 * Para ese momento el alias ya fue creado, por lo que:
			 *
			 * SELECT p.nombre AS planta
			 * FROM plantas p
			 * ORDER BY planta;
			 *
			 * sí es válido.
			 *
			 * ------------------------------------------------------------
			 * ¿LIKE PUEDE UTILIZARSE CON UN ALIAS?
			 * ------------------------------------------------------------
			 *
			 * Sí. LIKE no tiene ninguna restricción especial con los alias.
			 *
			 * El problema anterior no es LIKE, sino intentar utilizar en
			 * el WHERE un alias creado por el SELECT DEL MISMO NIVEL de
			 * consulta.
			 *
			 * Un alias sí puede utilizarse con WHERE ... LIKE cuando dicho
			 * alias ya existe para ese nivel de consulta.
			 *
			 * Por ejemplo, utilizando una subconsulta:
			 *
			 * SELECT *
			 * FROM (
			 *     SELECT
			 *         p.id,
			 *         p.nombre AS planta
			 *     FROM plantas p
			 * ) AS resultado
			 * WHERE planta LIKE '%VPSA%';
			 *
			 * En este caso sí funciona.
			 *
			 * Primero, la consulta interna:
			 *
			 *     SELECT p.id, p.nombre AS planta
			 *     FROM plantas p
			 *
			 * genera un resultado que ya contiene una columna llamada:
			 *
			 *     planta
			 *
			 * Luego la consulta externa trabaja sobre ese resultado.
			 * Para la consulta externa, "planta" ya existe como columna,
			 * por lo que puede utilizar:
			 *
			 *     WHERE planta LIKE '%VPSA%'
			 *
			 * Por lo tanto:
			 *
			 * MISMO NIVEL:
			 *
			 * SELECT p.nombre AS planta
			 * FROM plantas p
			 * WHERE planta LIKE '%VPSA%';
			 *
			 *     -> INCORRECTO: WHERE se procesa antes de que SELECT
			 *        cree el alias "planta".
			 *
			 *
			 * DIFERENTE NIVEL (SUBCONSULTA):
			 *
			 * SELECT *
			 * FROM (
			 *     SELECT p.nombre AS planta
			 *     FROM plantas p
			 * ) AS resultado
			 * WHERE planta LIKE '%VPSA%';
			 *
			 *     -> CORRECTO: para la consulta externa, "planta"
			 *        ya existe como una columna.
			 *
			 * ============================================================
			 * RESUMEN
			 * ============================================================
			 *
			 * WHERE    -> se procesa antes del SELECT -> usar columna real.
			 * SELECT   -> aquí se crea el alias.
			 * ORDER BY -> se procesa después          -> puede usar el alias.
			 *
			 * LIKE no prohíbe utilizar alias.
			 *
			 * La restricción ocurre cuando intentamos utilizar en el WHERE
			 * un alias creado por el SELECT del MISMO NIVEL de consulta.
			 *
			 * Si el alias fue generado previamente por una subconsulta,
			 * la consulta externa puede utilizarlo normalmente en:
			 *
			 *     WHERE alias LIKE ...
			 */

            // Armamos el WHERE global con OR (búsqueda global)
            $where = "WHERE " . implode(' OR ', $conditions);

            // Parámetro único (un solo cuadro de búsqueda)
            $params[':search'] = "%$search%";
        }


		//return [$where];
        // ============================================================
        // 5) recordsFiltered (conteo con filtros)
        // ============================================================

        /**
         * Si existe WHERE, contamos cuántos registros quedan luego del filtro.
         * Usamos COUNT(DISTINCT pk) para que sea robusto con JOINs que multiplican filas.
         */
        if ($where !== '') {
			
			// Guardamos la PK de la entidad que representa UNA fila del DataTable.
			// Se usa para COUNT(DISTINCT pk), lo que garantiza conteos correctos
			// incluso cuando los JOINs multiplican filas (relaciones 1-N).
			

            $sqlFiltrado = "SELECT COUNT(DISTINCT {$this->pk}) AS total
                            FROM {$this->tabla} $where";
              
                
/*
  Antes:
  COUNT(*) FROM (SELECT ... FROM tabla JOIN otra_tabla WHERE ...) sub

  Ahora:
  COUNT(DISTINCT pk) FROM tabla JOIN otra_tabla WHERE ...

  ✅ El JOIN sigue existiendo porque {$this->tabla} ya contiene el FROM + JOIN.
  ✅ Solo se eliminó la subconsulta.
  ✅ COUNT(DISTINCT pk) es más robusto para relaciones 1-N y cualquier JOIN.
*/      
                            
            /*
             * CODIGO ANTIGUO:
             * 
             * $sqlFiltrado = "SELECT COUNT(*) AS total FROM ({$this->buildSelectQuery()} $where) 
            AS subconsulta_filtrada";
            $stmtFiltered = $this->pdo->prepare($sqlFiltrado);*/
            
            

            $stmtFiltered = $this->pdo->prepare($sqlFiltrado);
            $stmtFiltered->execute($params);

            $recordsFiltered = (int)$stmtFiltered->fetch(PDO::FETCH_ASSOC)['total'];

        } else {
            // Sin búsqueda, filtrados = total
            $recordsFiltered = $recordsTotal;
        }

        // ============================================================
        // 6) Query final (data): SELECT + WHERE + ORDER + LIMIT
        // ============================================================

        // Convertimos el array de columnas en un string: "a.id, a.english, b.english AS opposite, ..."
        $selectColumns = implode(', ', $this->columnas);

     
		/*
		 * Construimos el ORDER BY únicamente si existe
		 * una columna válida para ordenar.
		 *
		 * Si $orderColumn es null, $orderSql queda vacío
		 * y la consulta se ejecuta sin ORDER BY.
		 */
		$orderSql = '';

		if ($orderColumn !== null) {
			$orderSql = "ORDER BY $orderColumn $orderDir";
		}


		/*
		 * Construcción final de la consulta.
		 *
		 * $orderSql puede contener:
		 *
		 * ORDER BY i.tag ASC
		 *
		 * o simplemente quedar vacío.
		 */
		$sql = "SELECT $selectColumns
				FROM {$this->tabla}
				$where
				$orderSql
				LIMIT :start, :length";

        $stmt = $this->pdo->prepare($sql);

        // Bind del parámetro de búsqueda (si existe)
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val, PDO::PARAM_STR);
        }

        // Bind paginación (INT obligatorio)
        $stmt->bindValue(':start', $start, PDO::PARAM_INT);
        $stmt->bindValue(':length', $length, PDO::PARAM_INT);

        // Ejecutar query
        $stmt->execute();

        // Traer resultados como array asociativo
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // ============================================================
        // 7) Respuesta final para DataTables
        // ============================================================

        return [
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data
        ];
    }
}
