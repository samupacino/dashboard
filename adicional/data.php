

<?php

    $datos = [
        [
        "id" => 1,
        "nombre" => "SAMUEL",
        "correo" => "samlsr064@gmail.com"
        ],
                [
        "id" => 2,
        "nombre" => "LUJAN",
        "correo" => "lujan064@gmail.com"
        ]
    ];

    $response = [
 
        
        "data" => $datos
    ];


    
    echo json_encode($response);

?>