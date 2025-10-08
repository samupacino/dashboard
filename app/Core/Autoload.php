<?php

spl_autoload_register(function ($class) {
    $baseDir = ROOT . '/';
    $classPath = str_replace('\\', '/', $class);
    $file = $baseDir . $classPath . '.php';


    //echo $file;
        //echo "</br>";

    if (file_exists($file)) {
        //echo  $file;
        //echo "</br>";
     
        require_once $file;
    }
});