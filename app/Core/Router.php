<?php

//namespace app\Core;
use app\Core\Response;


class Router {

    private $routes = [
        'GET' => [],
        'POST' => [],
        'PUT' => [],
        'DELETE' => [],
    ];

    public function __construct() {
        $this->registerRoutes();
    }

    private function registerRoutes() {

     
        /*PLANTA PL3*/

        $this->add('GET',       '/instrumentosPL3', 'app\\Controllers\\InstrumentoPL3Controller@listar');
        $this->add('DELETE',    '/instrumentosPL3/{id}', 'app\\Controllers\\InstrumentoPL3Controller@eliminar');
        $this->add('PUT',       '/instrumentosPL3/{id}', 'app\\Controllers\\InstrumentoPL3Controller@actualizar');
        $this->add('POST',      '/instrumentosPL3', 'app\\Controllers\\InstrumentoPL3Controller@guardar');


        /*
        PLANTA T155
        */
        $this->add('GET',       '/instrumentos', 'app\\Controllers\\InstrumentoT155Controller@listar');
        $this->add('DELETE',    '/instrumentos/{id}', 'app\\Controllers\\InstrumentoT155Controller@eliminar');
        $this->add('PUT',       '/instrumentos/{id}', 'app\\Controllers\\InstrumentoT155Controller@actualizar');
        $this->add('POST',      '/instrumentos', 'app\\Controllers\\InstrumentoT155Controller@guardar');


        //$this->add('GET', '/dashboard/', 'app\\Controllers\\VistaController@vistaParcial');


        $this->add('GET',    '/usuarios', 'app\\Controllers\\UsuarioController@listar');           // Para DataTable
        $this->add('POST',   '/usuarios', 'app\\Controllers\\UsuarioController@guardar');          // Crear nuevo
        $this->add('GET',    '/usuarios/{id}', 'app\\Controllers\\UsuarioController@obtener');          // Ver uno
        $this->add('PUT',    '/usuarios/{id}', 'app\\Controllers\\UsuarioController@actualizar');       // Actualizar
        $this->add('DELETE', '/usuarios/{id}', 'app\\Controllers\\UsuarioController@eliminar');         // Eliminar

        /*
        $this->add('POST', '/api/usuarios', 'app\\Controllers\\UsuarioController@crear');
        $this->add('GET',  '/api/usuarios', 'app\\Controllers\\UsuarioController@test');
        //$this->add('GET',  '/api/usuarios', 'app\\Controllers\\UsuarioController@listar');
        $this->add('GET',  '/api/usuarios/{id}', 'app\\Controllers\\UsuarioController@ver');
        $this->add('PUT',  '/api/usuarios/{id}', 'app\\Controllers\\UsuarioController@actualizar');
        $this->add('DELETE', '/api/usuarios/{id}', 'app\\Controllers\\UsuarioController@eliminar');
     */

     
        $this->add('GET', '/login', 'app\\Controllers\\VistaController@loginView');
        $this->add('POST', '/login', 'app\\Controllers\\UsuarioController@login');
        $this->add('GET', '/login/check-session', 'app\\Controllers\\UsuarioController@verificarSesions');
        $this->add('POST', '/login/logout', 'app\\Controllers\\UsuarioController@logout');



  
       
        $this->add('GET','/dashboard/ingles', 'app\\Controllers\\VistaController@ingles');
        $this->add('GET', '/dashboard', 'app\\Controllers\\VistaController@dashboardView');
       
        $this->add('GET', '/dashboard/{tipo}', 'app\\Controllers\\VistaController@vistaParcial');
    
       

        /*
        $this->add('GET',    '/instrumentos-t155',        'app\\Controllers\\InstrumentoT155Controller@listar');
        $this->add('POST',   '/instrumentos-t155',        'app\\Controllers\\InstrumentoT155Controller@guardar');
        $this->add('GET',    '/instrumentos-t155/{id}',   'app\\Controllers\\InstrumentoT155Controller@obtener');
        $this->add('PUT',    '/instrumentos-t155/{id}',   'app\\Controllers\\InstrumentoT155Controller@actualizar');
        $this->add('DELETE', '/instrumentos-t155/{id}',   'app\\Controllers\\InstrumentoT155Controller@eliminar');

        // (opcional para validar nombre)
        $this->add('POST',   '/instrumentos-t155/existe-nombre', 'app\\Controllers\\InstrumentoT155Controller@existeNombre');

        */

        $this->add('GET','/', 'app\\Controllers\\VistaController@menu');


        $this->add('GET','/ingles', 'app\\Controllers\\VistaController@ingles');
        $this->add('GET','/ingles/search', 'app\\Controllers\\InglesController@test');



     
    }

    private function add($method, $route, $handler) {
        $this->routes[$method][$route] = $handler;
    }

    public function handleRequest() {
     
      
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

    
        foreach ($this->routes[$method] as $route => $handler) {
         
            
            $pattern = preg_replace('#\{[a-zA-Z_]+\}#', '([a-zA-Z0-9_-]+)', $route);
          


            if (preg_match('#^' . $pattern . '$#', $uri, $matches)) {

             

                array_shift($matches);

    
                return $this->dispatch($handler, $matches);
            }
        } 
        
   
        Response::json(['error' => 'Ruta no encontrada'], 404);
    }

    private function dispatch($handler, $params) {
        list($class, $method) = explode('@', $handler);
      
        
       
        $controller = new $class;
      
        call_user_func_array([$controller, $method], $params);
    }
    public function samuel_lujan(){
        echo "que raro";
    }
}