<?php
    
    use app\Core\Session;

    Session::start();

    $usuario = Session::get('usuario');
    echo $usuario['correo'];
    //echo json_encode(['samuel'=>'desde dashboard']);
?>