<?php

namespace app\Controllers;

use app\Models\InstrumentoPL3Model;
use app\Core\Response;
use PDOException;
use Throwable;

class InstrumentoPL3Controller extends BaseApiController
{
    public function listar(): void
    {
       
        try {
            $modelo = new InstrumentoPL3Model();
            $resultado = $modelo->datatable();

            Response::datatable($resultado);

        } catch (Throwable $e) {
            Response::serverError(
                'Error al listar instrumentos',
                ['detalle' => $e->getMessage()]
            );
        }
    }

    public function guardar(): void
    {
        
        try {
            $data = $this->obtenerJsonInput();

            $tag = trim($data['tag'] ?? '');
            $plataforma = $data['plataforma'] ?? null;

            $this->validarRequeridos(
                [
                    'tag' => $tag,
                    'plataforma' => $plataforma
                ],
                [
                    'tag' => 'tag',
                    'plataforma' => 'plataforma'
                ]
            );

            $modelo = new InstrumentoPL3Model();
            $exito = $modelo->crear($tag, $plataforma);

            if (!$exito) {
                Response::serverError('Error al crear instrumento');
            }

            Response::created([], 'Instrumento creado exitosamente');

        } catch (PDOException $e) {
            Response::serverError(
                'Error de base de datos al crear instrumento',
                ['detalle' => $e->getMessage()]
            );
        } catch (Throwable $e) {
            Response::serverError(
                'Error inesperado al crear instrumento',
                ['detalle' => $e->getMessage()]
            );
        }
    }

    public function actualizar($id): void
    {
        
        try {
            $data = $this->obtenerJsonInput();

            $tag = trim($data['tag'] ?? '');
            $plataforma = $data['plataforma'] ?? null;

            $this->validarRequeridos(
                [
                    'tag' => $tag,
                    'plataforma' => $plataforma
                ],
                [
                    'tag' => 'tag',
                    'plataforma' => 'plataforma'
                ]
            );

            $modelo = new InstrumentoPL3Model();
            $exito = $modelo->actualizar($id, $tag, $plataforma);

            if (!$exito) {
                Response::serverError('Error al actualizar TAG');
            }

            Response::success([], 'Tag actualizado correctamente');

        } catch (PDOException $e) {
            Response::serverError(
                'Error de base de datos al actualizar instrumento',
                ['detalle' => $e->getMessage()]
            );
        } catch (Throwable $e) {
            Response::serverError(
                'Error inesperado al actualizar instrumento',
                ['detalle' => $e->getMessage()]
            );
        }
    }

    public function eliminar($id): void
    {
        
        try {
            $modelo = new InstrumentoPL3Model();
            $exito = $modelo->eliminar($id);

            if (!$exito) {
                Response::serverError('Error al eliminar instrumento');
            }

            Response::success([], 'Instrumento eliminado correctamente');

        } catch (Throwable $e) {
            Response::serverError(
                'Error al eliminar instrumento',
                ['detalle' => $e->getMessage()]
            );
        }
    }
}
