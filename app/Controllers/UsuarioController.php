<?php

namespace app\Controllers;

use app\Models\Usuario;
use app\Core\Response;
use app\Core\Session;
use Throwable;

class UsuarioController extends BaseApiController
{
    public function login(): void
    {
        try {
            $data = $this->obtenerJsonInput();

            $username = trim($data['username'] ?? '');
            $clave    = $data['password'] ?? '';

            $errors = [];

            if ($username === '') {
                $errors['username'] = ['El usuario es obligatorio'];
            }

            if ($clave === '') {
                $errors['password'] = ['La contraseña es obligatoria'];
            }

            $this->validateOrFail($errors);

            $usuarioModel = new Usuario();
            $usuario = $usuarioModel->verificarCredenciales($username, $clave);

            if (!$usuario) {
                Response::unauthorized('Credenciales inválidas');
            }

            Session::start();

            Session::set('usuario', [
                'id'            => $usuario['id'],
                'username'      => $usuario['username'],
                'name_complete' => $usuario['name_complete'],
                'rol'           => $usuario['rol']
            ]);

            Session::regenerate();
			Session::touch();

            Response::success(
                [
                    'usuario' => Session::get('usuario')
                ],
                'Login exitoso'
            );
        } catch (Throwable $e) {
            Response::serverError(
                'Error inesperado al hacer login',
                ['detalle' => $e->getMessage()]
            );
        }
    }

    public function logout(): void
    {
        try {
            Session::start();
            Session::destroy();

            Response::logout();
        } catch (Throwable $e) {
            Response::serverError(
                'Error al cerrar sesión',
                ['detalle' => $e->getMessage()]
            );
        }
    }

    public function sessionInfo(): void
    {
        try {
            Session::start();

            if (Session::has('usuario')) {
                Response::success([
                    'autenticado' => true,
                    'usuario'     => Session::get('usuario')
                ], 'Sesión activa');
            }

            Response::success([
                'autenticado' => false
            ], 'No hay sesión activa');
        } catch (Throwable $e) {
            Response::serverError(
                'Error al obtener información de sesión',
                ['detalle' => $e->getMessage()]
            );
        }
    }

    public function listar(): void
    {
        $this->verificarAdmin('No autorizado para listar usuarios');

        try {
            $usuarioModel = new Usuario();
            $resultado = $usuarioModel->obtenerDataTable();

            Response::datatable($resultado);
        } catch (Throwable $e) {
            Response::serverError(
                'Error al listar usuarios',
                ['detalle' => $e->getMessage()]
            );
        }
    }

    public function guardar(): void
    {
        $this->verificarAdmin('No autorizado para registrar usuarios');

        try {
            $data = $this->obtenerJsonInput();

            $username      = trim($data['username'] ?? '');
            $name_complete = trim($data['name_complete'] ?? '');
            $password      = $data['password'] ?? '';
            $rol           = trim($data['rol'] ?? 'invitado');

            $errors = [];

            if ($username === '') {
                $errors['username'] = ['El usuario es obligatorio'];
            }

            if ($name_complete === '') {
                $errors['name_complete'] = ['El nombre completo es obligatorio'];
            }

            if ($password === '') {
                $errors['password'] = ['La contraseña es obligatoria'];
            }

            if ($rol === '') {
                $errors['rol'] = ['El rol es obligatorio'];
            }

            $this->validateOrFail($errors);

            $usuarioModel = new Usuario();

            $success = $usuarioModel->guardar($username, $name_complete, $password, $rol);

            if (!$success) {
                Response::serverError('Error al crear el usuario');
            }

            Response::created([], 'Usuario creado exitosamente');
        } catch (Throwable $e) {
            Response::serverError(
                'Error inesperado al guardar usuario',
                ['detalle' => $e->getMessage()]
            );
        }
    }

    public function obtener($id): void
    {
        $this->verificarAdmin('No autorizado para obtener usuarios');

        try {
            $usuarioModel = new Usuario();
            $usuario = $usuarioModel->obtener($id);

            if (!$usuario) {
                Response::notFound('Usuario no encontrado');
            }

            Response::success($usuario, 'Usuario obtenido correctamente');
        } catch (Throwable $e) {
            Response::serverError(
                'Error al obtener usuario',
                ['detalle' => $e->getMessage()]
            );
        }
    }

    public function actualizar($id): void
    {
        $this->verificarAdmin('No autorizado para actualizar usuarios');

        try {
            $data = $this->obtenerJsonInput();

            $errors = [];

            if (isset($data['username']) && trim((string)$data['username']) === '') {
                $errors['username'] = ['El usuario no puede estar vacío'];
            }

            if (isset($data['name_complete']) && trim((string)$data['name_complete']) === '') {
                $errors['name_complete'] = ['El nombre completo no puede estar vacío'];
            }

            if (isset($data['rol']) && trim((string)$data['rol']) === '') {
                $errors['rol'] = ['El rol no puede estar vacío'];
            }

            $this->validateOrFail($errors);

            $usuarioModel = new Usuario();
            $success = $usuarioModel->actualizar($id, $data);

            if (!$success) {
                Response::serverError('Error al actualizar usuario');
            }

            Response::success([], 'Usuario actualizado correctamente');
        } catch (Throwable $e) {
            Response::serverError(
                'Error al actualizar usuario',
                ['detalle' => $e->getMessage()]
            );
        }
    }

    public function eliminar($id): void
    {
        $this->verificarAdmin('No autorizado para eliminar usuarios');

        try {
            $modelo = new Usuario();
            $exito = $modelo->eliminar($id);

            if (!$exito) {
                Response::serverError('Error al eliminar usuario');
            }

            Response::success([], 'Usuario eliminado correctamente');
        } catch (Throwable $e) {
            Response::serverError(
                'Error al eliminar usuario',
                ['detalle' => $e->getMessage()]
            );
        }
    }
}
