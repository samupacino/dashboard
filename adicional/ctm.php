<?php

function saludar($nombre, $pais) {
    echo "Hola $nombre de $pais";
}

$args = ['Samuel', 'Perú'];
call_user_func_array('saludar', $args);



?>