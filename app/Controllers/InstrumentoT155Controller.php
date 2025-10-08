<?php

namespace app\Controllers;

use app\Models\InstrumentoT155Model;
use app\Core\Session;
use app\Core\Response;
use PDOException;
use Throwable;

class InstrumentoT155Controller
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

    public function listar()
    {
        $this->verificarSesion();
       
        try {
            $modelo = new InstrumentoT155Model();
            $resultado = $modelo->datatable();
                  
            return Response::json($resultado);
        } catch (Throwable $e) {
            return Response::error("Error al listar: " . $e->getMessage(), 500);
        }
    }

    public function guardar()
    {
        $this->verificarSesion();

        if (!Session::isAdmin()) {
            return Response::error('No autorizado', 403);
        }

        try {
            $data = json_decode(file_get_contents('php://input'), true);
            $tag = trim($data['tag'] ?? '');
            $plataforma = $data['plataforma'] ?? null;

            if (!$tag || !$plataforma) {
                return Response::error('Faltan campos requeridos', 400);
            }

            $modelo = new InstrumentoT155Model();

          

            /*if ($modelo->existeNombre($tag)) {
                return Response::json(['error' => 'El nombre ya existe'], 409);
            }*/

            $exito = $modelo->crear($tag, $plataforma);
            return $exito
                ? Response::success([],'Instrumento creado exitosamente')
                : Response::error('Error al crear instrumento', 500);
        } catch (PDOException $e) {
            return Response::error("Error de base de datos: " . $e->getMessage(), 500);
        } catch (Throwable $e) {
            return Response::error("Error inesperado: " . $e->getMessage(), 500);
        }
    }

    public function obtener($id)
    {
        $this->verificarSesion();

        try {
            $modelo = new InstrumentoT155Model();
            $registro = $modelo->obtenerPorId($id);

            return $registro
                ? Response::json($registro)
                : Response::json(['error' => 'Instrumento no encontrado'], 404);
        } catch (Throwable $e) {
            return Response::error("Error al obtener instrumento: " . $e->getMessage(), 500);
        }
    }

    public function actualizar($id)
    {
   
        $this->verificarSesion();

        if (!Session::isAdmin()) {
            return Response::error('No autorizado', 403);
        }

        try {
            $data = json_decode(file_get_contents('php://input'), true);
            $tag = trim($data['tag'] ?? '');
            $plataforma = $data['plataforma'] ?? null;

            if (!$tag || !$plataforma) {
                Response::error('Faltan campos requeridos');
                //return Response::json(['error' => 'Faltan campos requeridos'], 400);
            }

            $modelo = new InstrumentoT155Model();

            /*if ($modelo->existeNombre($tag, $id)) {
                return Response::json(['error' => 'El nombre ya está en uso por otro instrumento'], 409);
            }*/

            $exito = $modelo->actualizar($id, $tag, $plataforma);
            return $exito
                ? Response::success([], 'Tag actualizado correctamente')
                : Response::error('Error al actualizar TAG', 500);
        } catch (PDOException $e) {
            return Response::error("Error de base de datos: " . $e->getMessage(), 500);
        } catch (Throwable $e) {
            return Response::error("Error inesperado: " . $e->getMessage(), 500);
        }

/*
        return $success
                ? Response::success([], 'Usuario actualizado correctamente')
                : Response::error('Error al actualizar usuario', 500);
        } catch (Throwable $e) {
            return Response::error("Error al actualizar usuario: " . $e->getMessage(), 500);
        }*/


        
    }

    public function eliminar($id)
    {
        $this->verificarSesion();

        if (!Session::isAdmin()) {
            return Response::error('No autorizado', 403);
        }

        try {
            $modelo = new InstrumentoT155Model();
            $exito = $modelo->eliminar($id);

            return $exito
                ? Response::success([], 'Usuario eliminado correctamente' . $exit)
                : Response::error('Error al eliminar usuario', 500);
        } catch (Throwable $e) {
            return Response::error("Error al eliminar instrumento: " . $e->getMessage(), 500);
        }
    }


    public function existeNombre()
    {
        $this->verificarSesion();

        try {
            $data = json_decode(file_get_contents('php://input'), true);
            $tag = $data['tag'] ?? '';
            $idExcluir = $data['id_excluir'] ?? null;

            $modelo = new InstrumentoT155Model();
            $existe = $modelo->existeNombre($tag, $idExcluir);

            return Response::json(['existe' => $existe]);
        } catch (Throwable $e) {
            return Response::error("Error al verificar nombre: " . $e->getMessage(), 500);
        }
    }
}
