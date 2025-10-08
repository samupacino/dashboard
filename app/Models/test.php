<?php


include 'app/Core/Database';

use app\Core\Database;

$this->db = Database::getInstance();
$consultas = $this->db->prepare("select * from usuario");
$consultas->execute();
$respuesta = $consultas->fetchAll();
echo json_encode($respuesta);
?>