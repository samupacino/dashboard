<?php

	namespace app\Controllers;
	
	
	use app\Core\Session;
    use app\Core\Response;
    use PDOException;
    use Throwable;
    
    
	class PracticaController{
		
		public function practica(){
			
			$headers = getallheaders();
			
			
			Response::json($headers['SAMUEL']);
			
		}
	
	}

	/*

$headers = getallheaders();
$type = $headers['Content-Type'] ?? '';

if (str_contains($type, 'application/json')) {
    $data = json_decode(file_get_contents('php://input'), true);
}


*/

?>
