<?php

namespace app\Core;
use PDO;

class DataTableT155
{
    private PDO $pdo;
    private string $tabla;
    private array $columnas;

   
    public function __construct(PDO $pdo, string $tabla, array $columnas)
    {
        $this->pdo = Database::getInstance("PlataformaDB");
        $this->tabla = $tabla;
        $this->columnas = $columnas;
    }

    /**
     * Método principal para procesar la petición y devolver JSON para DataTables
     */
    
    public function procesar(): array
    {
        
       
        // Extraer parámetros enviados por DataTables (GET o POST)
        $draw = $_GET['draw'] ?? 1;
        $start = $_GET['start'] ?? 0;
        $length = $_GET['length'] ?? 10;
        $search = $_GET['search']['value'] ?? '';
        $orderColumnIndex = $_GET['order'][0]['column'] ?? 0;
        $orderDir = $_GET['order'][0]['dir'] ?? 'asc';


        
        // 🔧 MODIFICADO: Extraer alias limpio para el ordenamiento
        // Si tienes columnas tipo "p.nombre AS plataforma", se extrae el alias "plataforma"

        $orderColumnRaw = $this->columnas[$orderColumnIndex] ?? $this->columnas[0];

        if (stripos($orderColumnRaw, ' AS ') !== false) {
            // Convertir "p.nombre AS plataforma" → "plataforma"
            
            $orderColumn = trim(preg_replace('/.*\s+AS\s+/i', '', $orderColumnRaw));
           
        } else {
            
            // Convertir "p.nombre" → "p.nombre"
            $orderColumn = $orderColumnRaw;
           
        }
      
        // 🔧 MODIFICADO: Usar subconsulta para contar correctamente con JOINs

        /*
        SELECT COUNT(*) AS total FROM (SELECT i.id, i.nombre, p.nombre AS plataforma FROM instrumento_t155 i JOIN plataformas p ON i.plataforma = p.id) AS subconsulta_total
        */
         /*
        SELECT COUNT(*) AS total FROM (SELECT i.id, i.nombre, p.nombre FROM instrumento_t155 i JOIN plataformas p ON i.plataforma = p.id) AS subconsulta_total;
        */
       
        //AQUI DEVUELVE EL TOTAL DE INSTRUMENTOS EXISTENTE EN LA TABLA 
        $stmtTotal = $this->pdo->query("SELECT COUNT(*) AS total FROM ({$this->buildSelectQuery()}) AS subconsulta_total");
        $recordsTotal = (int)$stmtTotal->fetch(PDO::FETCH_ASSOC)['total'];
   
        // Preparar cláusula WHERE si hay búsqueda
        $where = '';
        $params = [];

        if (!empty($search)) {

            /*
                SI EXISTE VALOR A BUSCAR ENTRA AQUI PARA ARMAR SQL Y PARAMETROS:
                ASI LO ARMA PARA MI TABLA: ->SI TIENES ALIAS LO ELIMINA PARA USAR DENTRO DE UNA SUBCONSULTA
                Lo partes en:
                    p.nombre → para usar en WHERE
                    escalon → para mostrar en SELECT

                [
                "i.id LIKE :search",
                "i.tag LIKE :search",
                "p.nombre LIKE :search"
                ]
            */
                
            $conditions = [];
            foreach ($this->columnas as $col) {
                if (stripos($col, ' AS ') !== false) {
                    // Divide en 'p.nombre AS escalon'
                    preg_match('/(.+)\s+AS\s+(\w+)/i', $col, $matches);
                    $colReal = trim($matches[1] ?? '');
                    if ($colReal !== '') {
                        //nota del porque se reemplaza el alias por nombre real de columna solo para los parametros
                        //ya que se usaran dentro de un where
                        $conditions[] = "$colReal LIKE :search";
                    }
                } else {
                    $conditions[] = "$col LIKE :search";
                }
            }
/*       nota del porque se reemplaza el alias por normal:
📌 ¿Se puede usar un alias en WHERE?
❌ No, en MySQL y en la mayoría de SGBD no puedes usar un alias definido en el SELECT dentro del mismo WHERE.
Esto es porque el orden en que se ejecutan las cláusulas SQL es distinto al orden en que lo escribes.

⚠️ Restricción del uso de alias en cláusula WHERE
En SQL, no es posible utilizar un alias definido en el SELECT dentro de la cláusula WHERE, ya que los alias son 
evaluados recién en una etapa posterior del procesamiento de la consulta. El orden lógico de ejecución de una consulta 
comienza con FROM, luego WHERE, y solo después se evalúa el SELECT, por lo tanto el alias aún no existe al momento de 
evaluar condiciones.
Por ejemplo, en una consulta como SELECT p.nombre AS plataforma FROM plataformas p WHERE plataforma LIKE '%A%', se 
producirá un error porque plataforma aún no está definido cuando se evalúa el WHERE.
✅ Para solucionar esto, se debe usar directamente la columna original (p.nombre) en el WHERE, o encapsular la 
consulta como una subconsulta y aplicar el WHERE sobre el alias desde fuera:

SELECT * FROM (
  SELECT p.nombre AS plataforma FROM plataformas p
) AS sub WHERE plataforma LIKE '%A%';

*/
        

           
            /*
            foreach ($this->columnas as $col) {
                // 🔧 MODIFICADO: aplicar alias si existe
                if (stripos($col, ' AS ') !== false) {
                    preg_match('/AS\s+(\w+)/i', $col, $matches);
                    $alias = $matches[1] ?? '';
                    if ($alias !== '') {
                        $conditions[] = "$alias LIKE :search";
                    }
                } else {
                    $conditions[] = "$col LIKE :search";
                }
            }*/
           
            /*
            De esa manera lo arma:
                "WHERE i.id LIKE :search OR i.tag LIKE :search OR p.nombre LIKE :search"
            */
            $where = "WHERE " . implode(' OR ', $conditions);
      
            /*
                Como hay solo un cuadro de busqueda de frontend entonces se arma asi:
                    :search: "%A5F-989%"
            */
            $params[':search'] = "%$search%";
         
           
        }
     
    
        // 🔧 MODIFICADO: Contar con filtro aplicado también usando subconsulta
        if ($where) {//SOLO ENTRA AQUI SI EXISTE DATOS A BUSCAR MEDIANTE FILTRADO PARA CONTAR EL TOTAL DE LO BUSCADO
/*
VERSION UNO CON p.nombre:
SELECT COUNT(*) AS total FROM (SELECT i.id, i.tag, p.nombre FROM instrumento_t155 i JOIN 
plataformas p ON i.plataforma = p.id WHERE i.id LIKE :search OR i.tag LIKE :search OR p.nombre LIKE :search) 
AS subconsulta_filtrada

VERSION UNO CON p.nombre as escalon:
SELECT COUNT(*) AS total FROM (SELECT i.id, i.tag, p.nombre as escalon FROM instrumento_t155 i JOIN
plataformas p ON i.plataforma = p.id WHERE i.id LIKE :search OR i.tag LIKE :search OR p.nombre LIKE :search) 
AS subconsulta_filtrada
*/
            //AQUI DEVUELVE LA CANTIDAD DE INSTRUMENTOS POR BUSQUEDA FILTRADO
            $sqlFiltrado = "SELECT COUNT(*) AS total FROM ({$this->buildSelectQuery()} $where) AS subconsulta_filtrada";
            $stmtFiltered = $this->pdo->prepare($sqlFiltrado);

           
            $stmtFiltered->execute($params);
            $recordsFiltered = (int)$stmtFiltered->fetch(PDO::FETCH_ASSOC)['total'];


           
           
    
        } else {
            //SI NO HAY VALOR A BUSCAR ENTONCES DEVUELVE EL TOTAL
            $recordsFiltered = $recordsTotal;
        }

        // 🔧 MODIFICADO: Usar lista de columnas explícita
        /*
            antes:      ["i.id","i.tag","p.nombre as plataforma"]
            despues:    i.id, i.tag, p.nombre as plataforma
        */
        $selectColumns = implode(', ', $this->columnas);
 
        // 🔧 MODIFICADO: Usar consulta base con búsqueda, orden y paginación

/*
1.-CON BUSQUEDA:
SELECT i.id, i.tag, p.nombre as plataforma FROM instrumento_t155 i JOIN 
plataformas p ON i.plataforma = p.id WHERE i.id LIKE :search OR i.tag LIKE :search OR p.nombre LIKE :search 
ORDER BY i.id asc LIMIT :start, :length

2.-SIN BUSQUEDA:
SELECT i.id, i.tag, p.nombre as plataforma FROM instrumento_t155 i JOIN plataformas p ON i.plataforma = p.id 
ORDER BY i.id asc LIMIT :start, :length
*/        

        //ESTA CONSULTA YA DEVUELVE EL TOTAL APLICANDO ORDEN , BUSQUEDA Y PAGINACION
        $sql = "SELECT $selectColumns FROM {$this->tabla} $where ORDER BY $orderColumn $orderDir LIMIT :start, :length";
        
        $stmt = $this->pdo->prepare($sql);

        // Vincular parámetros de busquedas , si en caso no aplica busqueda este foreach no recorre nada
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val, PDO::PARAM_STR);
        }
       
        $stmt->bindValue(':start', (int)$start, PDO::PARAM_INT);
        $stmt->bindValue(':length', (int)$length, PDO::PARAM_INT);

        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Formato que espera DataTables


        return [
            'draw' => (int)$draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data
        ];
    }

    /**
     * 🔧 AGREGADO: Función auxiliar para construir SELECT base sin LIMIT/ORDER
     */
    private function buildSelectQuery(): string
    {
        $selectColumns = implode(', ', $this->columnas);
        return "SELECT $selectColumns FROM {$this->tabla}";

        /*
            devuelve:
            SELECT i.id, i.tag, p.nombre as plataforma FROM instrumento_t155 i 
            JOIN plataformas p ON i.plataforma = p.id
        */
    }
}
