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
        $start = (int)($request['start'] ?? 0);

        // length: cuántos registros devolver por página
        $length = (int)($request['length'] ?? 5);

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


		// ✅ Si hay whitelist de order, úsala. Si no, usa el modo antiguo.
		
		
		/*
    Validación correcta del índice de orden:
    - Si usamos orderCols, el índice debe existir en orderCols.
    - Si no usamos orderCols, el índice debe existir en columnas.
    - Así el ORDER BY siempre se construye usando una fuente válida y coherente.
*/
		if ($this->orderCols !== null && !empty($this->orderCols)) {
			/*
    Validación del índice de orden:
    - Si existe whitelist de orderCols, validamos contra ese arreglo.
    - Si no existe, validamos contra $columnas.
    - Esto asegura que el índice siempre se evalúe sobre la fuente real
      que se usará para construir el ORDER BY.
*/
			if (!array_key_exists($orderColumnIndex, $this->orderCols)) {
        		$orderColumnIndex = array_key_first($this->orderCols);
    		}
    		$orderColumn = $this->orderCols[$orderColumnIndex];
			//return [$orderColumn];
			
		}else{
			
			 // Blindaje: si el índice no existe, lo forzamos a 0
			if (!isset($this->columnas[$orderColumnIndex])) {
				$orderColumnIndex = 0;
			}
		 	// tu lógica antigua basada en $this->columnas (con alias)
		 	
		 	
			// Obtenemos la columna del SELECT según el índice de DataTables
			$orderColumnRaw = $this->columnas[$orderColumnIndex] ?? $this->columnas[0];

			// Si viene con alias (AS), para ORDER BY usamos el alias (MySQL lo permite)
			// Ej: "b.english AS opposite" -> ORDER BY opposite
			if (stripos($orderColumnRaw, ' AS ') !== false) {

				// Divide por "AS" (case-insensitive): ["b.english", "opposite"]
				$partes = preg_split('/\s+AS\s+/i', $orderColumnRaw);

				// Si existe la parte del alias, se usa; si no, se cae a la parte original
				$orderColumn = trim($partes[1] ?? $partes[0]);

			} else {
				// Sin alias: ORDER BY "a.english" / "i.id" etc.
				$orderColumn = $orderColumnRaw;
        	}
        
        }

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

            $conditions = [];
            
            // Si searchCols es null, usamos $columnas (comportamiento original)
            
            //Así si viene null o [], usa $columnas.
            $colsParaBuscar = !empty($this->searchCols) ? $this->searchCols : $this->columnas;

            foreach ($colsParaBuscar as $col) {
				
		/*"WHERE a.english LIKE :search OR a.spanish LIKE :search OR 
		a.pronunciation LIKE :search OR a.pos LIKE :search OR a.level LIKE :search 
		OR b.english LIKE :search OR a.source LIKE :search"*/
		
                // Si la columna tiene alias, NO usamos el alias en WHERE
                // porque en SQL el WHERE se evalúa antes del SELECT.
                
                 // Si accidentalmente alguien puso alias en searchCols, lo limpiamos igual
                if (stripos($col, ' AS ') !== false) {

                    // Divide: "b.english AS opposite" -> ["b.english", "opposite"]
                    $partes = preg_split('/\s+AS\s+/i', $col);

                    // Tomamos la parte real: "b.english"
                    $colReal = trim($partes[0]);

                    if ($colReal !== '') {
                        $conditions[] = "$colReal LIKE :search";
                    }

                } else {
                    // Columna sin alias: "a.english"
                    $conditions[] = "$col LIKE :search";
                }
            }

            /*       nota del porque se reemplaza el alias por normal:
            📌 ¿Se puede usar un alias en WHERE?
            ❌ No, en MySQL y en la mayoría de SGBD no puedes usar un alias definido en el SELECT dentro del mismo WHERE.
            Esto es porque el orden en que se ejecutan las cláusulas SQL es distinto al orden en que lo escribes.

            ⚠️ Restricción del uso de alias en cláusula WHERE
            En SQL, no es posible utilizar un alias definido en el SELECT dentro de la cláusula WHERE, ya que los alias son
            evaluados recién en una etapa posterior del procesamiento de la consulta. El orden lógico de ejecución de una
            consulta comienza con FROM, luego WHERE, y solo después se evalúa el SELECT, por lo tanto el alias aún no existe al
            momento de evaluar condiciones.
            Por ejemplo, en una consulta como:
              SELECT p.nombre AS plataforma FROM plataformas p WHERE plataforma LIKE '%A%';
            produce error porque "plataforma" aún no existe cuando se evalúa el WHERE.

            ✅ Para solucionar esto:
            - Usar la columna real en WHERE (p.nombre), o
            - Encapsular como subconsulta y filtrar afuera:
              SELECT * FROM (SELECT p.nombre AS plataforma FROM plataformas p) sub WHERE plataforma LIKE '%A%';
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
            Query final:
            - Devuelve data paginada y ordenada.
            - Aquí sí usamos ORDER BY con alias si existe (MySQL OK).
        */
        $sql = "SELECT $selectColumns
                FROM {$this->tabla}
                $where
                ORDER BY $orderColumn $orderDir
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
