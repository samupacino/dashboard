<?php

namespace app\Controllers;
use app\Models\Login;
use app\Models\Usuario;
use app\Core\Response;


class UsuarioController {

        public function login() {
            

        $data = json_decode(file_get_contents('php://input'), true);
        $correo = $data['correo'] ?? '';
        $clave  = $data['clave'] ?? '';

        if (!$correo || !$clave) {
            return Response::json(['error' => 'Correo o clave faltante'], 400);
        }

        $usuario = new Login();
  
        $resultado = $usuario->verificarCredenciales($correo, $clave);

       
        if ($resultado) {
          
            $_SESSION['usuario'] = [
                'id' => $resultado['id'],
                'nombre' => $resultado['nombre'],
                'correo' => $resultado['corre'],
            ];
            
            return Response::json(['mensaje' => 'Login correcto', 'usuario' =>$_SESSION['usuario']['correo']]);
        }

        return Response::json(['error' => 'Credenciales inválidas SAMUEL'], 401);
    }


    public function crear() {
        $data = json_decode(file_get_contents('php://input'), true);
        $nombre = $data['nombre'] ?? '';
        $email = $data['email'] ?? '';

        if (!$nombre || !$email) {
            return Response::json(['error' => 'Datos incompletos'], 400);
        }

        $usuario = new Usuario();
        if ($usuario->guardar($nombre, $email)) {
            return Response::json(['mensaje' => 'Usuario creado']);
        }
        return Response::json(['error' => 'Error al guardar'], 500);
    }

    public function listar() {
        $usuario = new Usuario();
        $usuarios = $usuario->todos();
        return Response::json($usuarios);
    }

    public function ver($id) {
        $usuario = new Usuario();
        $data = $usuario->obtener($id);
        if ($data) {
            return Response::json($data);
        }
        return Response::json(['error' => 'Usuario no encontrado'], 404);
    }

    public function actualizar($id) {
        $data = json_decode(file_get_contents('php://input'), true);
        $usuario = new Usuario();
        if ($usuario->actualizar($id, $data)) {
            return Response::json(['mensaje' => 'Usuario actualizado']);
        }
        return Response::json(['error' => 'No se pudo actualizar'], 400);
    }

    public function eliminar($id) {
        $usuario = new Usuario();
        if ($usuario->eliminar($id)) {
            return Response::json(['mensaje' => 'Usuario eliminado']);
        }
        return Response::json(['error' => 'No se pudo eliminar'], 400);
    }
   
}