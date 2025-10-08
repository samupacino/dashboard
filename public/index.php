<?php




define('ROOT', dirname(__DIR__));

require_once '../app/Core/Autoload.php';

require_once '../app/Core/Router.php';




$router = new Router();

$router->handleRequest();


?>


