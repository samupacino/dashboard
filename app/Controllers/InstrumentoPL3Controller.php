<?php
    namespace app\Controllers;

    use app\Models\InstrumentoPL3Model;
    use app\Core\Session;
    use app\Core\Response;
    use PDOException;
    use Throwable;

class InstrumentoPL3Controller{

    private function verificarSesion(){
        Session::start();

        if (!Session::has('usuario')) {
            Response::unauthorized('No autenticado');
        }

        if (Session::isExpired(1)) {
            Session::destroy();
            Response::sessionExpired('Sesión expirada');
        }

        Session::renovarTiempo();
    }

    public function listar(){

        $this->verificarSesion();
       
        try {
            $modelo = new InstrumentoPL3Model();
            $resultado = $modelo->datatable();
                    
            return Response::json($resultado);
        } catch (Throwable $e) {
            return Response::error("Error al listar: " . $e->getMessage(), 500);
        }


        
    }
    public function guardar(){

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

            $modelo = new InstrumentoPL3Model();

          

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

    public function eliminar($id){
	
        $this->verificarSesion();

        if (!Session::isAdmin()) {
            return Response::error('No autorizado', 403);
        }

        try {
            $modelo = new InstrumentoPL3Model();
            $exito = $modelo->eliminar($id);

            return $exito
                ? Response::success([], 'Instrumento eliminado correctamente' . $exit)
                : Response::error('Error al eliminar instrumento', 500);
        } catch (Throwable $e) {
            return Response::error("Error al eliminar instrumento: " . $e->getMessage(), 500);
        }
    }

    public function actualizar($id){
   
        $this->verificarSesion();

        if (!Session::isAdmin()) {
            return Response::unauthorized('No autorizado', 403);
        }

            
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            $tag = trim($data['tag'] ?? '');
            $plataforma = $data['plataforma'] ?? null;

            if (!$tag || !$plataforma) {
                Response::error('Faltan campos requeridos');
                //return Response::json(['error' => 'Faltan campos requeridos'], 400);
            }

            $modelo = new InstrumentoPL3Model();

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




        
    }
    
}


?>
