<?php

    namespace app\Controllers;

    use app\Models\InglesModel;
    use app\Core\Session;
    use app\Core\Response;
    use PDOException;
    use Throwable;


    class InglesController{

        public function test(){
            $ingles = new InglesModel();
            $ingles->test();
        }

    }
?>