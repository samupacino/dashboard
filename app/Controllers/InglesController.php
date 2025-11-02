<?php

    namespace app\Controllers;

    use app\Models\InglesModel;
    use app\Core\Session;
    use app\Core\Response;
    use PDOException;
    use Throwable;


    class InglesController{

        public function search(){

            $ingles = new InglesModel();
            echo json_encode($ingles->search(),JSON_UNESCAPED_UNICODE);

        }

    }
?>