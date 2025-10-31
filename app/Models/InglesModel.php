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
        public function test(){


                    // Obtener parámetro q (texto buscado)
            //$q = isset($_GET['q']) ? trim($_GET['q']) : '';
            $q = trim('  ');
            // Si no hay texto, devolver las primeras 20 palabras
            if ($q === '') {
                $stmt = $this->pdo->query("SELECT id, english FROM en_vocab ORDER BY english ASC LIMIT 5");
                $stmt->execute();
            } else {
                $stmt = $this->pdo->prepare("SELECT id, english 
                                    FROM en_vocab 
                                    WHERE english LIKE :search 
                                    ORDER BY english ASC 
                                    LIMIT 20");
                $stmt->execute([':search' => "%{$q}%"]);
            }

            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (!headers_sent()) {
                header('Content-Type: application/json; charset=utf-8');
            }
            echo json_encode($results, JSON_UNESCAPED_UNICODE);
        } 
    }
?>