<?php
namespace app\Core;

use PDO;
use PDOException;
use Exception;

class Database {
    private static $instance = null;

    public static function getInstance() {
       
        if (!self::$instance) {
            try {
                self::$instance = new PDO(
                    'mysql:host=localhost;dbname=PlataformaDB',
                    'root',
                    'samuellujan1989'
                );
                self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                // Lanzamos la excepción para que el controlador pueda capturarla
                throw new Exception('Error al conectar con la base de datos: ' . $e->getMessage(), 500);
            }
        }
        return self::$instance;
    }
}
