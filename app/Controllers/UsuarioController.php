<?php

namespace app\Controllers;

use app\Models\Usuario;
use app\Core\Response;
use app\Core\Session;
use PDOException;
use Throwable;

class UsuarioController
{
    private function verificarSesion()
    {
        Session::start();

        if (!Session::has('usuario')) {
            Response::unauthorized('No autenticado');
        }

        if (Session::isExpired()) {
            Session::destroy();
            Response::sessionExpired('Sesión expirada');
        }

        Session::renovarTiempo();
    }

    public function login()
    {
        Session::start();

        try {
            $data = json_decode(file_get_contents('php://input'), true);
            $username = $data['username'] ?? '';
            $clave = $data['password'] ?? '';
          
         
            if (!$username || !$clave) {
                return Response::error('Usuario o clave faltante', 400);
            }

            $usuarioModel = new Usuario();
            $usuario = $usuarioModel->verificarCredenciales($username, $clave);

            if ($usuario) {
                Session::set('usuario', [
                    'id' => $usuario['id'],
                    'username' => $usuario['username'],
                    'name_complete' => $usuario['name_complete'],
                    'rol' => $usuario['rol']
                ]);
                Session::renovarTiempo();

                return Response::success(Session::get('usuario')['username'], 'Login correcto');
            }

            return Response::error('Credenciales inválidas', 401);
        } catch (Throwable $e) {
            return Response::error('Error inesperado al hacer login: ' . $e->getMessage(), 500);
        }
    }

    public function logout()
    {
        try {
            Session::start();
            Session::destroy();
            return Response::logout();
        } catch (Throwable $e) {
            return Response::error('Error al cerrar sesión: ' . $e->getMessage(), 500);
        }
    }

    public function sessionInfo()
    {
        Session::start();

        if (Session::has('usuario')) {
            return Response::data([
                'autenticado' => true,
                'usuario' => Session::get('usuario')
            ]);
        }

        return Response::data(['autenticado' => false]);
    }

    public function listar()
    {
        $this->verificarSesion();

        try {
            if (!Session::isAdmin()) {
                return Response::unauthorized('No autorizado');
            }

            $usuarioModel = new Usuario();
            $resultado = $usuarioModel->obtenerDataTable();

          
            return Response::json($resultado);

        } catch (Throwable $e) {
            return Response::error("Error al listar: " . $e->getMessage(), 500);
        }
    }

    public function guardar()
    {
        $this->verificarSesion();

        if (!Session::isAdmin()) {
            return Response::unauthorized('No autorizado');
        }

        try {
            $data = json_decode(file_get_contents('php://input'), true);
            $username = $data['username'] ?? '';
            $name_complete = $data['name_complete'] ?? '';
            $password = $data['password'] ?? '';
            $rol = $data['rol'] ?? 'invitado';

            
            if (!$username || !$name_complete || !$password) {
                return Response::error('Faltan campos requeridos', 400);
            }

            $usuarioModel = new Usuario();
            $success = $usuarioModel->guardar($username, $name_complete, $password, $rol);

            return $success
                ? Response::success([], 'Usuario creado exitosamente')
                : Response::error('Error al crear el usuario', 500);
        } catch (Throwable $e) {
            return Response::error('Error inesperado al guardar: ' . $e->getMessage(), 500);
        }
    }

    public function obtener($id)
    {
        $this->verificarSesion();

        if (!Session::isAdmin()) {
            return Response::unauthorized('No autorizado');
        }

        try {
            $usuarioModel = new Usuario();
            $usuario = $usuarioModel->obtener($id);

            return $usuario
                ? Response::data($usuario, 'Usuario obtenido correctamente')
                : Response::error('Usuario no encontrado', 404);
        } catch (Throwable $e) {
            return Response::error("Error al obtener usuario: " . $e->getMessage(), 500);
        }
    }

    public function actualizar($id)
    {
        $this->verificarSesion();

        if (!Session::isAdmin()) {
            return Response::unauthorized('No autorizado');
        }

        try {
            $data = json_decode(file_get_contents('php://input'), true);
            $usuarioModel = new Usuario();
            $success = $usuarioModel->actualizar($id, $data);

            return $success
                ? Response::success([], 'Usuario actualizado correctamente')
                : Response::error('Error al actualizar usuario', 500);
        } catch (Throwable $e) {
            return Response::error("Error al actualizar usuario: " . $e->getMessage(), 500);
        }
    }

    public function eliminar($id)
    {
          
        $this->verificarSesion();

        if (!Session::isAdmin()) {
            return Response::unauthorized('No autorizado');
        }

        try {
            $modelo = new Usuario();
            $exito = $modelo->eliminar($id);

            return $exito
                ? Response::success([], 'Usuario eliminado correctamente' . $exit)
                : Response::error('Error al eliminar usuario', 500);
        } catch (Throwable $e) {
            return Response::error("Error al eliminar usuario: " . $e->getMessage(), 500);
        }
    }
}

