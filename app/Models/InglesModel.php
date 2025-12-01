<?php

   
    namespace app\Models;
	use app\Core\DatatableIngles;

 	use app\Core\Database;
    use PDO;


    class InglesModel{


        private PDO $pdo;
       
        private $tabla = 'en_vocab'; // Tabla asociada a este modelo
   
        public function __construct()
        {
            //$this->pdo = Database::getInstance("PlataformaDB");
            $this->pdo = Database::getInstance("english_notes");
  
        }
        public function search(){

     
                    // Obtener parámetro q (texto buscado)
            $q = isset($_GET['q']) ? trim($_GET['q']) : '';
            $q = trim($q);
            // Si no hay texto, devolver las primeras 20 palabras
            if ($q === '') {
                $stmt = $this->pdo->prepare("SELECT id, english FROM en_vocab ORDER BY english ASC LIMIT 5");
                $stmt->execute();
            } else {
                $stmt = $this->pdo->prepare("SELECT id, english 
                                    FROM en_vocab 
                                    WHERE english LIKE :search 
                                    ORDER BY english ASC 
                                    LIMIT 10");
                $stmt->execute([':search' => "%{$q}%"]);
            }

            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } 

            
        public function crear($datos){
            // 4) SQL fijo y fácil de leer
            $sql = "INSERT INTO {$this->tabla} 
            (english, pronunciation, spanish, pos, level, example_en, example_es, 
            notes, opposite_id, source) 
            VALUES 
            (:english, :pronunciation, :spanish, :pos, :level, :example_en, :example_es, 
            :notes, :opposite_id, :source)";
            
            $stmt = $this->pdo->prepare($sql);
            
         
         
            
			$stmt->bindValue(':english',       $datos['english']);
			
			
			$stmt->bindValue(':pronunciation', $datos['pronunciation']);
			$stmt->bindValue(':spanish',       $datos['spanish']);
			$stmt->bindValue(':pos',           $datos['pos']);
			$stmt->bindValue(':level',         $datos['level']);
			$stmt->bindValue(':example_en',    $datos['example_en']);
			$stmt->bindValue(':example_es',    $datos['example_es']);
			$stmt->bindValue(':notes',         $datos['notes']);
			$stmt->bindValue(':opposite_id',   $datos['opposite_id']);
			$stmt->bindValue(':source',        $datos['source']);

		
            return $stmt->execute();
                    
      
        } 
        public function datatable(){
			
			
			
            //$tabla = 'instrumento_pl3 i JOIN plataformas p ON i.plataforma = p.id';
            //$columnas = ['i.id', 'i.tag', 'i.plataforma','p.ubicacion '];


            /*
                con variable tabla y columnas se armara lo siguiente:

                SELECT i.id, i.tag, p.nombre FROM instrumento_t155 i J
                OIN plataformas p ON i.plataforma = p.id
                
                
                SELECT a.english AS palabra, b.english AS opuesto
FROM en_vocab a
LEFT JOIN en_vocab b ON a.opposite_id = b.id;

select a.id, a.english , a.opposite_id , b.english from en_vocab a 
left join en_vocab b on a.opposite_id = b.id;

            */
        
            $tabla = 'en_vocab a left join en_vocab b on a.opposite_id = b.id' ;
         
            
            
            $columnas = [
            'a.id',
            'a.english',
            'a.pronunciation',
            'a.spanish',
            'a.pos',
            'a.level',
            'a.example_en',
            'a.example_es',
            'a.notes',
            'a.opposite_id',
            'b.english as opposite',
            'a.source',
            'a.created_at',
            'a.updated_at'
            
            ];
            
            $dt = new DatatableIngles($this->pdo, $tabla, $columnas);
		
          
            return $dt->procesar();
/*
	
             
           $stmt = $this->pdo->prepare("select id from en_vocab");
           $stmt->execute();
           return $stmt->fetchAll();*/
           
           
            /*$tabla = 'instrumento_pl3 i JOIN plataformas p ON i.plataforma = p.id';
            $columnas = ['i.id', 'i.tag', 'i.plataforma','p.ubicacion '];


             
           
            $dt = new DataTablePL3($this->pdo,$tabla, $columnas);
            
            return $dt->procesar();*/
      
 

        }
    	// Eliminar por ID
        public function eliminar($id){
            $stmt = $this->pdo->prepare("DELETE FROM {$this->tabla} WHERE id = ?");
            return $stmt->execute([$id]);
        }
        
        
        
       
    /**
     * Actualiza un registro en la tabla en_vocab usando SQL FIJO.
     *
     * Espera un array $data con estas claves:
     *   - id            (int)    → id del registro a actualizar (PK)
     *   - english       (string) → requerido
     *   - pronunciation (?string)
     *   - spanish       (string) → requerido
     *   - pos           (?string)
     *   - level         (?string)
     *   - example_en    (?string)
     *   - example_es    (?string)
     *   - notes         (?string)
     *   - opposite_id   (?int)
     *   - source        (?string)
     *
     * IMPORTANTE:
     *   - La validación y sanitizado ya se hizo en el controlador (Validator).
     *   - Aquí asumimos que los tipos son correctos y que no hay campos faltantes.
     *
     * @param array $data  Datos limpios (clean) validados en el controlador.
     * @return bool        true si se actualizó al menos 1 fila, false si no.
     */
    public function actualizar(array $data): bool
    {
        // 1) SQL FIJO: se actualizan SIEMPRE las mismas columnas.
        //
        // NOTA: updated_at se actualiza solo gracias a:
        //   updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        //              ON UPDATE CURRENT_TIMESTAMP
        // en tu definición de tabla, así que no hace falta incluirlo en el SET.
        $sql = "
            UPDATE en_vocab
               SET english       = :english,
                   pronunciation = :pronunciation,
                   spanish       = :spanish,
                   pos           = :pos,
                   level         = :level,
                   example_en    = :example_en,
                   example_es    = :example_es,
                   notes         = :notes,
                   opposite_id   = :opposite_id,
                   source        = :source
             WHERE id            = :id
        ";

        // 2) Preparamos el statement
        $st = $this->pdo->prepare($sql);

        // 3) Bindeamos uno por uno, respetando tipos y posibles NULLs.
        //
        // ID (PK, requerido, entero)
        $st->bindValue(':id', (int)$data['id'], PDO::PARAM_INT);

        // english: requerido, siempre string
        $st->bindValue(':english', $data['english'], PDO::PARAM_STR);

        // pronunciation: puede ser string o null
        if ($data['pronunciation'] === null) {
            $st->bindValue(':pronunciation', null, PDO::PARAM_NULL);
        } else {
            $st->bindValue(':pronunciation', $data['pronunciation'], PDO::PARAM_STR);
        }

        // spanish: requerido, siempre string
        $st->bindValue(':spanish', $data['spanish'], PDO::PARAM_STR);

        // pos: enum o null
        if ($data['pos'] === null) {
            $st->bindValue(':pos', null, PDO::PARAM_NULL);
        } else {
            $st->bindValue(':pos', $data['pos'], PDO::PARAM_STR);
        }

        // level: enum o null
        if ($data['level'] === null) {
            $st->bindValue(':level', null, PDO::PARAM_NULL);
        } else {
            $st->bindValue(':level', $data['level'], PDO::PARAM_STR);
        }

        // example_en: texto opcional
        if ($data['example_en'] === null) {
            $st->bindValue(':example_en', null, PDO::PARAM_NULL);
        } else {
            $st->bindValue(':example_en', $data['example_en'], PDO::PARAM_STR);
        }

        // example_es: texto opcional
        if ($data['example_es'] === null) {
            $st->bindValue(':example_es', null, PDO::PARAM_NULL);
        } else {
            $st->bindValue(':example_es', $data['example_es'], PDO::PARAM_STR);
        }

        // notes: texto opcional
        if ($data['notes'] === null) {
            $st->bindValue(':notes', null, PDO::PARAM_NULL);
        } else {
            $st->bindValue(':notes', $data['notes'], PDO::PARAM_STR);
        }

        // opposite_id: FK opcional (int o null)
        if ($data['opposite_id'] === null) {
            $st->bindValue(':opposite_id', null, PDO::PARAM_NULL);
        } else {
            $st->bindValue(':opposite_id', (int)$data['opposite_id'], PDO::PARAM_INT);
        }

        // source: texto opcional
        if ($data['source'] === null) {
            $st->bindValue(':source', null, PDO::PARAM_NULL);
        } else {
            $st->bindValue(':source', $data['source'], PDO::PARAM_STR);
        }

        // 4) Ejecutamos el UPDATE
        $st->execute();

        // 5) rowCount() indica cuántas filas fueron afectadas:
        //    - 0 → no encontró el id o los valores eran exactamente iguales.
        //    - >=1 → se actualizó algo.
        //
        // Tú decides cómo interpretarlo. Aquí devolvemos true solo si hay al menos 1 fila afectada.
        return $st->rowCount() > 0;
    }
        
        
        
        
        
        
        
        
        
        
    }
?>





