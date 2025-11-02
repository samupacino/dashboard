<?php

   
    namespace app\Models;

    use app\Core\Database;
    use app\Core\DataTablePL3;
    use PDO;


    class InglesModel{


        private PDO $pdo;


   
        public function __construct()
        {
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

            return $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        } 
    }
?>