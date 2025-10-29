<?php

   
    namespace app\Models;

    use app\Core\BaseModel;
    use app\Core\DataTablePL3;
    use PDO;

    class InstrumentoPL3Model extends BaseModel{

     
        private $tabla = 'instrumento_pl3'; // Tabla asociada a este modelo

        public function datatable(){
          

            /*
                con variable tabla y columnas se armara lo siguiente:

                SELECT i.id, i.tag, p.nombre FROM instrumento_t155 i J
                OIN plataformas p ON i.plataforma = p.id
                
            */
        
            $tabla = 'instrumento_pl3 i JOIN plataformas p ON i.plataforma = p.id';
            $columnas = ['i.id', 'i.tag', 'i.plataforma','p.ubicacion '];


             
           
            $dt = new DataTablePL3($this->db,$tabla, $columnas);

            
            return $dt->procesar();

        }

        // Crear nuevo instrumento
        public function crear($tag, $plataforma)
        {
            $stmt = $this->db->prepare("INSERT INTO {$this->tabla} (tag, plataforma) VALUES (?, ?)");
            return $stmt->execute([$tag, $plataforma]);
        }    
        
            // Eliminar por ID
        public function eliminar($id)
        {
            $stmt = $this->db->prepare("DELETE FROM {$this->tabla} WHERE id = ?");
            return $stmt->execute([$id]);
        }

            // Actualizar un instrumento por ID
        public function actualizar($id, $tag, $plataforma)
        {
            $stmt = $this->db->prepare("UPDATE {$this->tabla} SET tag = ?, plataforma = ? WHERE id = ?");
            return $stmt->execute([$tag, $plataforma, $id]);
        }


     
        
    }


?>