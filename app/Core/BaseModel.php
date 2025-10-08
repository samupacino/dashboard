<?php
namespace app\Core;

use app\Core\Database;

class BaseModel {

    protected $db;

    public function __construct() {
    
        $this->db = Database::getInstance();
    
    }
}
