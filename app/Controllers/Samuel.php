<?php
namespace app\controllers;


use app\Models\InstrumentoT155Model;
use app\Core\Response;


class Samuel{

 

    public function samuel(){

        $dato = new InstrumentoT155Model();
        return Response::json($dato->datatable());
      
  
       
    }
}
?>

