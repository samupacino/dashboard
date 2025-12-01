<?php
namespace app\Core;

use PDO;
use PDOException;
use Exception;

class Database {
    private static $instance = null;//PlataformaDB

    public static function getInstance($database) {
       
        if (!self::$instance) {
            try {
                self::$instance = new PDO(
                    "mysql:host=localhost;dbname={$database}",
                    'root',
                    'samuellujan1989'
                );
                self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
             
        		//self::$instance->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        		
            } catch (PDOException $e) {
                // Lanzamos la excepción para que el controlador pueda capturarla
                throw new Exception('Error al conectar con la base de datos: ' . $e->getMessage(), 500);
            }
        }
        return self::$instance;
    }
}
