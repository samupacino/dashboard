<?php
namespace app\Controllers;

use app\Core\Session;
use app\Core\Response;

class VistaController
{
    // Vista de login
    public function loginView()
    {
        Session::start();

        if (Session::has('usuario')) {
            header('Location: /dashboard');
            exit;
        }

        require_once ROOT . '/views/login.php';
    }

    public function menu(){
     
        require_once ROOT . '/views/menu/menu.php';
     
    }
    
	public function ingles(){
		Session::start();

		$esAjax = $this->isAjaxRequest();

		// ====== Validación de sesión / rol ======
		if (!Session::isAdmin()) {
			// Si viene por fetch → JSON
			if ($esAjax) {
				
				Response::error('No tienes permisos para ver esta sección.',403);
				//$this->jsonError(403, 'No tienes permisos para ver esta sección.');
				//return;
			}
		
			// Si viene por navegación normal → redirigir o mostrar vista 403
			header('Location: /'); // o /login o /error403
			exit;
		}

		// ====== Si ES admin ======
		if ($esAjax) {
			// Llamada hecha por fetch → NO renderizamos la vista aquí.
			// Solo avisamos al front que puede ir a la URL real.
			Response::success(['redirect' => '/ingles']);
			//http_response_code(200);
        	//header('Content-Type: application/json');
        	//echo json_encode(['redirect' => '/ingles']);
			
			return;
		}

		// Navegación normal (no AJAX): renderizar vista directamente
		
		require_once ROOT . '/views/ingles/index.php';
	}
	
	protected function isAjaxRequest(): bool
	{
		return isset($_SERVER['HTTP_X_REQUESTED_WITH'])
			&& strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
	}


    /*public function ingles(){
		
		Session::start();

       // Solo admin puede acceder
       if (!Session::isAdmin()) {
          $this->jsonError(403, 'No tienes permisos para ver usuarios.');
          return;
       }

     
        require_once ROOT . '/views/ingles/index.php';
     
    }*/
    // Vista principal del dashboard
    public function dashboardView()
    {
        Session::start();

     

        if (!Session::has('usuario')) {
            Session::destroy();
            header('Location: /login');
            exit;
        }

        if (Session::isExpired()) {
            Session::destroy();
            header('Location: /login');
            exit;
        }
        
      
        require ROOT . '/views/layouts/head.php';
        require ROOT . '/views/dashboard.php';
        require ROOT . '/views/layouts/footer.php';
    }

    // Método reutilizable para errores en formato JSON
    private function jsonError($code, $message)
    {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode(['error' => $message]);
    }
    


    // Vista parcial que se carga dinámicamente según el módulo
    public function vistaParcial($modulo)
    {
        Session::start();

        // 1. Verifica autenticación
        if (!Session::has('usuario')) {
            $this->jsonError(401, 'No autenticado.');
            return;
        }

        // 2. Switch de vistas
        switch ($modulo) {

            case 'usuario':
                // Solo admin puede acceder
                if (!Session::isAdmin()) {
                    $this->jsonError(403, 'No tienes permisos para ver usuarios.');
                    return;
                }

                $ruta = ROOT . '/views/usuario/usuario.php';
                if (!file_exists($ruta)) {
                    $this->jsonError(500, 'Archivo de vista usuario.php no encontrado.');
                    return;
                }

                require $ruta;
                break;

            case 't155':
                $ruta = ROOT . '/views/instrumento_t155/index.php';
                if (!file_exists($ruta)) {
                    $this->jsonError(500, 'Archivo de vista instrumento_t155/index.php no encontrado.');
                    return;
                }

                require $ruta;
                break;

                
            case 'perfil':
                $ruta = ROOT . '/views/perfil/index.php';
                if (!file_exists($ruta)) {
                    $this->jsonError(500, 'Archivo de vista perfil/index.php no encontrado.');
                    return;
                }

                require $ruta;
                break;

            case 'pl3':
                $ruta = ROOT . '/views/instrumento_pl3/index.php';
                if (!file_exists($ruta)) {
                    $this->jsonError(500, 'Archivo de vista perfil/index.php no encontrado.');
                    return;
                }

                require $ruta;
                break;
            default:
                $this->jsonError(404, 'Vista no encontrada.');
                break;
        }
    }
}
