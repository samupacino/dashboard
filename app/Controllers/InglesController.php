<?php
	//declare(strict_types=1);

	
    namespace app\Controllers;

    use app\Models\InglesModel;
    
    use app\Core\Session;
    use app\Core\Validator;
    use app\Core\Response;
    use PDOException;
    use Throwable;
    


    class InglesController{
			
		private function verificarSesion(){
			Session::start();
			
			if (!Session::has('usuario')) {
				Response::unauthorized('No autenticado INGLES');
			}

			if (Session::isExpired(1)) {
				Session::destroy();
				Response::sessionExpired('Sesión expirada INGLES');
			}

			Session::renovarTiempo();
    	}
		

        public function search(){
			
			//echo json_encode(['samuel'=>1]);
			//return;
			
            $ingles = new InglesModel();
            //echo json_encode(['samuel'=>1]);
           
            echo json_encode($ingles->search(),JSON_UNESCAPED_UNICODE);

        }

		public function listar(){
			
			$this->verificarSesion();
			
			try{
				
				$ingles = new InglesModel();
				
				//Response::error("SIMULANDO DESDE BACKEND SAMUEL",200);
				
				
				return Response::json($ingles->datatable());
				
			
			
			}catch (PDOException $e) {
				return Response::error("Error de base de datos: " . $e->getMessage(), 500);
			} catch (Throwable $e) {
				return Response::error("Error inesperado: " . $e->getMessage(), 500);
			}
		}
        public function guardar(){
		
			$this->verificarSesion();
			
			try{
			
			
				// Supón que ya recibiste $in desde JSON o POST:
				$isJson = isset($_SERVER['CONTENT_TYPE']) && str_contains($_SERVER['CONTENT_TYPE'], 'application/json');
				$in     = $isJson ? (json_decode(file_get_contents('php://input'), true) ?? []) : $_POST;

				// Arrays de enums permitidos según tu schema
				$POS_ALLOWED   = ['verb','phrasal_verb','noun','adjective','adverb','expression'];
				$LEVEL_ALLOWED = ['A1','A2','B1','B2','C1','C2'];

				

			
				// Acumulador de errores
				$errors = [];


				// ========= VALIDACIONES =========

				// Requeridos (devuelven string o agregan error)
				$english = Validator::requireString($in['english'] ?? null, 'english', $errors);
				$spanish = Validator::requireString($in['spanish'] ?? null, 'spanish', $errors);

				// Opcionales (trimOrNull)
				$pronunciation = Validator::trimOrNull($in['pronunciation'] ?? null);
				$pos           = Validator::trimOrNull($in['pos'] ?? null);     // si queda null, aplicará DEFAULT en DB
				$level         = Validator::trimOrNull($in['level'] ?? null);
				$example_en    = Validator::trimOrNull($in['example_en'] ?? null);
				$example_es    = Validator::trimOrNull($in['example_es'] ?? null);
				$notes         = Validator::trimOrNull($in['notes'] ?? null);
				$source        = Validator::trimOrNull($in['source'] ?? null);

				// FK opcional (entero positivo o null)
				$opposite_id   = Validator::positiveIntOrNull($in['opposite_id'] ?? null, 'opposite_id', $errors);

				// Longitudes según columnas SQL
				
				
				$english       = Validator::maxLength($english, 120);
				$pronunciation = Validator::maxLength($pronunciation, 120);
				$spanish       = Validator::maxLength($spanish, 180);
				$example_en    = Validator::maxLength($example_en, 240);
				$example_es    = Validator::maxLength($example_es, 240);
				$notes         = Validator::maxLength($notes, 240);
				$source        = Validator::maxLength($source, 120);

				// Enums
				$pos   = Validator::enum($pos,   'pos',   $POS_ALLOWED,   $errors);
				$level = Validator::enum($level, 'level', $LEVEL_ALLOWED, $errors);

				// (Opcional) sanitizar texto para almacenamiento simple
				
				$english       = Validator::sanitize($english);
				$pronunciation = Validator::sanitize($pronunciation);
				$spanish       = Validator::sanitize($spanish);
				$example_en    = Validator::sanitize($example_en);
				$example_es    = Validator::sanitize($example_es);
				$notes         = Validator::sanitize($notes);
				$source        = Validator::sanitize($source);
				
			
				// ========= SI HAY ERRORES, RESPONDE 422 =========
				if (!empty($errors)) {
					
					Response::error("Error inesperado: ", 400, $errors);
					
					
					//http_response_code(422);
					//echo json_encode(['ok' => false, 'errors' => $errors], JSON_UNESCAPED_UNICODE);
					exit;
				}


				// ========= CONSTRUIR PAYLOAD LIMPIO PARA EL MODELO =========
				$clean = [
					'english'       => $english,        // requerido
					'pronunciation' => $pronunciation,  // opcional null
					'spanish'       => $spanish,        // requerido
					'pos'           => $pos,            // null → DEFAULT 'expression' en SQL
					'level'         => $level,          // opcional null
					'example_en'    => $example_en,     // opcional null
					'example_es'    => $example_es,     // opcional null
					'notes'         => $notes,          // opcional null
					'opposite_id'   => $opposite_id,    // null o int positivo
					'source'        => $source,         // opcional null
				];
				
		
				$ingles = new InglesModel();
				//Response::success([],'Palabra guardado LINEA TEST');
				//echo json_encode($ingles->crear($clean),JSON_UNESCAPED_UNICODE);
				$exito = $ingles->crear($clean);
				
				return $exito
                ? Response::success([],'Palabra guardado exitosamente')
                : Response::error('Error al guardar palabra', 500);

			} catch (PDOException $e) {
				return Response::error("Error de base de datos: " . $e->getMessage(), 500);
			} catch (Throwable $e) {
				return Response::error("Error inesperado: " . $e->getMessage(), 500);
			}

		}
		

		public function actualizar(){
			
			
			$this->verificarSesion();

			/*if (!Session::isAdmin()) {
				return Response::unauthorized('No autorizado', 403);
			}*/

			try{
			
			$isJson = isset($_SERVER['CONTENT_TYPE']) && str_contains($_SERVER['CONTENT_TYPE'], 'application/json');
			$in     = $isJson ? (json_decode(file_get_contents('php://input'), true) ?? []) : $_POST;

	
			
			$POS_ALLOWED   = ['verb','phrasal_verb','noun','adjective','adverb','expression'];
			$LEVEL_ALLOWED = ['A1','A2','B1','B2','C1','C2'];
			$errors = [];
			
		
			
			
			
			// ======= ID del registro a editar =======
			$id = Validator::positiveIntOrNull($in['id'] ?? null, 'id', $errors);
			if ($id === null) {
				$errors['id'] = 'ID es obligatorio y debe ser entero positivo';
			}


				// ========= VALIDACIONES =========

			// Requeridos (devuelven string o agregan error)
			$english = Validator::requireString($in['english'] ?? null, 'english', $errors);
			$spanish = Validator::requireString($in['spanish'] ?? null, 'spanish', $errors);

			// Opcionales (trimOrNull)
			$pronunciation = Validator::trimOrNull($in['pronunciation'] ?? null);
			$pos           = Validator::trimOrNull($in['pos'] ?? null);     // si queda null, aplicará DEFAULT en DB
			$level         = Validator::trimOrNull($in['level'] ?? null);
			$example_en    = Validator::trimOrNull($in['example_en'] ?? null);
			$example_es    = Validator::trimOrNull($in['example_es'] ?? null);
			$notes         = Validator::trimOrNull($in['notes'] ?? null);
			$source        = Validator::trimOrNull($in['source'] ?? null);

			// FK opcional (entero positivo o null)
			$opposite_id   = Validator::positiveIntOrNull($in['opposite_id'] ?? null, 'opposite_id', $errors);

			// Longitudes según columnas SQL
				
				
			$english       = Validator::maxLength($english, 120);
			$pronunciation = Validator::maxLength($pronunciation, 120);
			$spanish       = Validator::maxLength($spanish, 180);
			$example_en    = Validator::maxLength($example_en, 240);
			$example_es    = Validator::maxLength($example_es, 240);
			$notes         = Validator::maxLength($notes, 240);
			$source        = Validator::maxLength($source, 120);

			// Enums
			$pos   = Validator::enum($pos,   'pos',   $POS_ALLOWED,   $errors);
			$level = Validator::enum($level, 'level', $LEVEL_ALLOWED, $errors);
			// (Opcional) sanitizar texto para almacenamiento simple
				
			$english       = Validator::sanitize($english);
			$pronunciation = Validator::sanitize($pronunciation);
			$spanish       = Validator::sanitize($spanish);
			$example_en    = Validator::sanitize($example_en);
			$example_es    = Validator::sanitize($example_es);
			$notes         = Validator::sanitize($notes);
			$source        = Validator::sanitize($source);
				
			
			// ========= SI HAY ERRORES, RESPONDE 422 =========
			if (!empty($errors)) {
					
				return Response::error("Error inesperado: ", 400, $errors);
						
					
				exit;
			}


			// ========= CONSTRUIR PAYLOAD LIMPIO PARA EL MODELO =========
			$clean = [
				'id'			=>$id,				 // requerido
				'english'       => $english,        // requerido
				'pronunciation' => $pronunciation,  // opcional null
				'spanish'       => $spanish,        // requerido
				'pos'           => $pos,            // null → DEFAULT 'expression' en SQL
				'level'         => $level,          // opcional null
				'example_en'    => $example_en,     // opcional null
				'example_es'    => $example_es,     // opcional null
				'notes'         => $notes,          // opcional null
				'opposite_id'   => $opposite_id,    // null o int positivo
				'source'        => $source,         // opcional null
			];
			
			

			$modelo = new InglesModel();
			$exito = $modelo->actualizar($clean);
			
			if ($exito > 0) {
				// Se actualizó algo
				Response::success([], 'Actualizado correctamente');
			} else {
				// No hubo cambios PERO la operación fue correcta
				Response::success([],'No se modificó ningún campo');
			}
                
		
				
				
			} catch (PDOException $e) {
				return Response::error("Error de base de datos: " . $e->getMessage(), 500);
			} catch (Throwable $e) {
				return Response::error("Error inesperado: " . $e->getMessage(), 500);
			}
			
		}

		
    
   		public function eliminar($id){
			
			$this->verificarSesion();

			/*if (!Session::isAdmin()) {
				return Response::unauthorized('No autorizado', 403);
			}*/

			try {
				
			
				$modelo = new InglesModel();
				$exito = $modelo->eliminar($id);

				return $exito
					? Response::success([], 'Palabra eliminado correctamente')
					: Response::error('Error al eliminar usuario', 500);
			} catch (Throwable $e) {
				return Response::error("Error al eliminar instrumento: " . $e->getMessage(), 500);
			}
    	}

    }
?>
