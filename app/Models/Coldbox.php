<?php
    namespace app\Models;

    use app\Core\Database;
    use app\Core\DataTableT155;
    use PDO;

    class Coldbox{

        private $db;

        public function __construct(){
            $this->db = Database::getInstance();
        }

        public function obtenerDataTable() {
       

        /*
            con variable tabla y columnas se armara lo siguiente:

            SELECT i.id, i.tag, p.nombre FROM instrumento_t155 i J
            OIN plataformas p ON i.plataforma = p.id
            
        */
      
            $tabla = 'instrumento_t155 i JOIN plataformas p ON i.plataforma = p.id';
            $columnas = ['i.id', 'i.tag', 'p.nombre '];

            $dt = new DataTableT155($this->db,$tabla, $columnas);
            return $dt->procesar();
        }

        
    }

?>