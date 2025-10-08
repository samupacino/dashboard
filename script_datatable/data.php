<?php
include_once'conexion.php';


    $start = isset($_GET['start'])?$_GET['start']:0;
    $length = isset($_GET['length'])?$_GET['length']:0;

    $searchValue = isset($_GET['search']['value'])?$_GET['search']['value']:"";

    $orderColumnIndex = isset($_GET['order'][0]['column'])?(int)$_GET['order'][0]['column']:0;
    $orderDIr = isset($_GET['order'][0]['dir'])?$_GET['order'][0]['dir']:'asc';
    $draw = isset($_GET['draw'])?$_GET['draw']:1;

    $columns = ['id','nombre','correo'];
    $orderColumn = isset($columns[$orderColumnIndex])?$columns[$orderColumnIndex]:'id';

    /////////////////////////////////////////////////////////////////////////////////
    $cantidad = $conexion->prepare("select count(*) as total from usuario");
    $cantidad->execute();
    $resultado = $cantidad->fetch(PDO::FETCH_ASSOC);
    $total = $resultado['total'];
    /////////////////////////////////////////////////////////////////////////////////////

    $where = "";
    $params = [];


    if(!empty($searchValue)){
        $where = "where nombre LIKE :search OR correo like :search ";
        $params[':search'] = "%$searchValue%";
    }
//////////////////////////////////////////////////////////////////////////////////////////////
    $filteredQuery = "select count(*) as total from usuario $where";
    $filteredStmt = $conexion->prepare($filteredQuery);
    //$filteredStmt->bindValue(':search',$ctm[':search'], PDO::PARAM_STR);
    $filteredStmt->execute($params);
    $resultadofiltered = $filteredStmt->fetch(PDO::FETCH_ASSOC);
    $totalFiltered = $resultadofiltered['total'];
  
    
//////////////////////////////////////////////////////////////////
    $declaracion = $conexion->prepare("select * from usuario $where order by $orderColumn $orderDIr LIMIT :start, :length");

    foreach($params as $key=>$value){
        
       $declaracion->bindValue($key,$value);
    }

    $declaracion->bindValue(':start',$start,PDO::PARAM_INT);
    $declaracion->bindValue(':length',$length,PDO::PARAM_INT);

    $declaracion->execute();
    $resultado =  $declaracion->fetchAll(PDO::FETCH_ASSOC);
/////////////////////////////////////////////////////////////////
  
    $response = [
        "draw"=>$draw,
        "recordsTotal"=>$total,
        "recordsFiltered"=>$totalFiltered,
        "data"=>$resultado
    ];
    

    //echo json_encode($response['data'][0]['id']);
    
    echo json_encode($response);




?>