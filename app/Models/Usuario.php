<?php
namespace app\Models;

use app\Core\DataTableServer;
use app\Core\Database;
use PDO;

class Usuario {

    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }
    public function test(){
        echo "samuel desde probando";
    }
    public function verificarCredenciales($username, $clave) {
        
        $stmt = $this->db->prepare("SELECT * FROM usuario WHERE username = :username LIMIT 1");
        $stmt->execute([':username' => $username]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuario && password_verify($clave, $usuario['password'])) {
            unset($usuario['password']);
            return $usuario;
        }

        return false;
    }

    public function guardar($username, $name_complete, $password, $rol = 'invitado') {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare("INSERT INTO usuario (username, name_complete, password, rol) VALUES (?, ?, ?, ?)");
        return $stmt->execute([$username, $name_complete, $hash, $rol]);
    }

    public function obtenerDataTable() {
        $tabla = 'usuario';
        $columnas = ['id', 'username', 'name_complete', 'rol'];
  
        $tablaServer = new DataTableServer($this->db, $tabla, $columnas);
        return $tablaServer->procesar();
    }

    public function obtener($id) {
        $stmt = $this->db->prepare("SELECT id, username, name_complete, rol FROM usuario WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function actualizar($id, $data) {
        $stmt = $this->db->prepare("UPDATE usuario SET username = ?, name_complete = ?, rol = ? WHERE id = ?");
        return $stmt->execute([
            $data['username'],
            $data['name_complete'],
            $data['rol'],
            $id
        ]);
    }

    public function eliminar($id) {
        $stmt = $this->db->prepare("DELETE FROM usuario WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
