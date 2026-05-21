<?php

use app\Core\Response;
use app\Core\Session;

class Router {

    /**
     * Aquí guardamos todas las rutas
     * ahora cada ruta tendrá:
     * - handler
     * - middlewares
     */
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

        /**
         * 🔥 EJEMPLO CON MIDDLEWARE
         * SOLO ADMIN
         */
        $this->add('GET', '/instrumentosPL3', 'app\\Controllers\\InstrumentoPL3Controller@listar', ['auth','admin']);

        $this->add('DELETE', '/instrumentosPL3/{id}', 'app\\Controllers\\InstrumentoPL3Controller@eliminar', ['auth','admin']);
        $this->add('PUT', '/instrumentosPL3/{id}', 'app\\Controllers\\InstrumentoPL3Controller@actualizar', ['auth','admin']);
        $this->add('POST', '/instrumentosPL3', 'app\\Controllers\\InstrumentoPL3Controller@guardar', ['auth','admin']);


        /**
         * T155
         */
        $this->add('GET', '/instrumentos', 'app\\Controllers\\InstrumentoT155Controller@listar', ['auth', 'admin']);
        $this->add('DELETE', '/instrumentos/{id}', 'apps\\Controllers\\InstrumentoT155Controller@eliminar', ['auth','admin']);
        $this->add('PUT', '/instrumentos/{id}', 'app\\Controllers\\InstrumentoT155Controller@actualizar', ['auth','admin']);
        $this->add('POST', '/instrumentos', 'app\\Controllers\\InstrumentoT155Controller@guardar', ['auth','admin']);


        /**
         * Usuarios (admin)
         */
        $this->add('GET', '/usuarios', 'app\\Controllers\\UsuarioController@listar', ['auth','admin']);
        $this->add('POST', '/usuarios', 'app\\Controllers\\UsuarioController@guardar', ['auth','admin']);
        $this->add('GET', '/usuarios/{id}', 'app\\Controllers\\UsuarioController@obtener', ['auth','admin']);
        $this->add('PUT', '/usuarios/{id}', 'app\\Controllers\\UsuarioController@actualizar', ['auth','admin']);
        $this->add('DELETE', '/usuarios/{id}', 'app\\Controllers\\UsuarioController@eliminar', ['auth','admin']);


        /**
         * Login (público)
         */
        $this->add('GET', '/login', 'app\\Controllers\\VistaController@loginView',[]);
        $this->add('POST', '/login', 'app\\Controllers\\UsuarioController@login',[]);
        
             
       
        $this->add('POST', '/login/logout', 'app\\Controllers\\UsuarioController@logout');


        /**
         * Dashboard (requiere sesión)
         */
        $this->add('GET', '/dashboard', 'app\\Controllers\\VistaController@dashboardView', ['web_auth']);
        $this->add('GET', '/dashboard/{tipo}', 'app\\Controllers\\VistaController@vistaParcial', ['auth']);
		//$this->add('GET', '/dashboard/usuario', 'app\\Controllers\\VistaController@vistaParcial', ['auth']);

        /**
         * Ingles
         */
        $this->add('GET','/ingles', 'app\\Controllers\\VistaController@ingles', ['web_auth','web_admin']);
        $this->add('GET','/ingles/access', 'app\\Controllers\\VistaController@inglesAccess',['auth','admin']);


        /**
         * API ingles
         */
        $this->add('GET','/api/ingles/search', 'app\\Controllers\\InglesController@search'); // público

        $this->add('POST','/api/ingles/registro', 'app\\Controllers\\InglesController@guardar', ['auth']);
        $this->add('PUT','/api/ingles/actualizar', 'app\\Controllers\\InglesController@actualizar', ['auth']);
        $this->add('GET','/api/ingles/listar', 'app\\Controllers\\InglesController@listar', ['auth']);
        $this->add('DELETE','/api/ingles/{id}', 'app\\Controllers\\InglesController@eliminar', ['auth']);


        /**
         * HOME
         */
        $this->add('GET','/', 'app\\Controllers\\VistaController@menu', []);
        
        
        
        // =========================
		// INSTRUMENTOS   registroInstrumento
		// =========================

        $this->add('GET','/instrumento/access', 'app\\Controllers\\VistaController@instrumentoAccess',['web_auth']);
        $this->add('GET','/instrumento', 'app\\Controllers\\VistaController@instrumento',);
        
        $this->add('GET','/instrumento/registro/access', 'app\\Controllers\\VistaController@registroInstrumentoAccess',['web_auth','admin']);
        $this->add('GET','/instrumento/registro', 'app\\Controllers\\VistaController@registroInstrumento',['auth','admin']);
        
        
        $this->add('GET','/api/instrumentos/listar', 'app\\Controllers\\InstrumentoController@index',['auth']);
		$this->add('GET','/api/instrumentos/{id}', 'app\\Controllers\\InstrumentoController@show', ['auth','admin']);
		$this->add('POST','/api/instrumentos/registro', 'app\\Controllers\\InstrumentoController@store', ['auth','admin']);
		$this->add('POST','/api/instrumentos/actualizar', 'app\\Controllers\\InstrumentoController@update', ['auth','admin']);
		$this->add('DELETE','/api/instrumentos/{id}', 'app\\Controllers\\InstrumentoController@delete', ['auth','admin']);
				
        
         
         
         

        /*PLANTA PL3*/

        //$this->add('GET',       '/instrumentosPL3', 'app\\Controllers\\InstrumentoPL3Controller@listar');
        //$this->add('DELETE',    '/instrumentosPL3/{id}', 'app\\Controllers\\InstrumentoPL3Controller@eliminar');
        //$this->add('PUT',       '/instrumentosPL3/{id}', 'app\\Controllers\\InstrumentoPL3Controller@actualizar');
        //$this->add('POST',      '/instrumentosPL3', 'app\\Controllers\\InstrumentoPL3Controller@guardar');


        /*
        PLANTA T155
        */
        //$this->add('GET',       '/instrumentos', 'app\\Controllers\\InstrumentoT155Controller@listar');
        //$this->add('DELETE',    '/instrumentos/{id}', 'app\\Controllers\\InstrumentoT155Controller@eliminar');
        //$this->add('PUT',       '/instrumentos/{id}', 'app\\Controllers\\InstrumentoT155Controller@actualizar');
        //$this->add('POST',      '/instrumentos', 'app\\Controllers\\InstrumentoT155Controller@guardar');


        //$this->add('GET', '/dashboard/', 'app\\Controllers\\VistaController@vistaParcial');


        //$this->add('GET',    '/usuarios', 'app\\Controllers\\UsuarioController@listar');           // Para DataTable
        //$this->add('POST',   '/usuarios', 'app\\Controllers\\UsuarioController@guardar');          // Crear nuevo
        //$this->add('GET',    '/usuarios/{id}', 'app\\Controllers\\UsuarioController@obtener');          // Ver uno
        //$this->add('PUT',    '/usuarios/{id}', 'app\\Controllers\\UsuarioController@actualizar');       // Actualizar
        //$this->add('DELETE', '/usuarios/{id}', 'app\\Controllers\\UsuarioController@eliminar');         // Eliminar

        /*
        $this->add('POST', '/api/usuarios', 'app\\Controllers\\UsuarioController@crear');
        $this->add('GET',  '/api/usuarios', 'app\\Controllers\\UsuarioController@test');
        //$this->add('GET',  '/api/usuarios', 'app\\Controllers\\UsuarioController@listar');
        $this->add('GET',  '/api/usuarios/{id}', 'app\\Controllers\\UsuarioController@ver');
        $this->add('PUT',  '/api/usuarios/{id}', 'app\\Controllers\\UsuarioController@actualizar');
        $this->add('DELETE', '/api/usuarios/{id}', 'app\\Controllers\\UsuarioController@eliminar');
     */

     
        //$this->add('GET', '/login', 'app\\Controllers\\VistaController@loginView');
        //$this->add('POST', '/login', 'app\\Controllers\\UsuarioController@login');
        //$this->add('GET', '/login/check-session', 'app\\Controllers\\UsuarioController@verificarSesions');
        //$this->add('POST', '/login/logout', 'app\\Controllers\\UsuarioController@logout');



  
       
        //$this->add('GET','/dashboard/ingles', 'app\\Controllers\\VistaController@ingles');
        //$this->add('GET', '/dashboard', 'app\\Controllers\\VistaController@dashboardView');
       
        //$this->add('GET', '/dashboard/{tipo}', 'app\\Controllers\\VistaController@vistaParcial');
    
       

        /*
        $this->add('GET',    '/instrumentos-t155',        'app\\Controllers\\InstrumentoT155Controller@listar');
        $this->add('POST',   '/instrumentos-t155',        'app\\Controllers\\InstrumentoT155Controller@guardar');
        $this->add('GET',    '/instrumentos-t155/{id}',   'app\\Controllers\\InstrumentoT155Controller@obtener');
        $this->add('PUT',    '/instrumentos-t155/{id}',   'app\\Controllers\\InstrumentoT155Controller@actualizar');
        $this->add('DELETE', '/instrumentos-t155/{id}',   'app\\Controllers\\InstrumentoT155Controller@eliminar');

        // (opcional para validar nombre)
        $this->add('POST',   '/instrumentos-t155/existe-nombre', 'app\\Controllers\\InstrumentoT155Controller@existeNombre');

        */

        //$this->add('GET','/', 'app\\Controllers\\VistaController@menu');


        //$this->add('GET','/ingles/access', 'app\\Controllers\\VistaController@inglesAccess');
       	//$this->add('GET','/ingles', 'app\\Controllers\\VistaController@ingles');
						
        //$this->add('GET','/api/ingles/search', 'app\\Controllers\\InglesController@search');
        
        //$this->add('POST','/api/ingles/registro', 'app\\Controllers\\InglesController@guardar');
        //$this->add('PUT','/api/ingles/actualizar', 'app\\Controllers\\InglesController@actualizar');
 		
 		//$this->add('GET','/api/ingles/listar', 'app\\Controllers\\InglesController@listar');
 		//$this->add('POST','/api/ingles/listar', 'app\\Controllers\\InglesController@listar');
		
		//$this->add('DELETE','/api/ingles/{id}', 'app\\Controllers\\InglesController@eliminar');

     
     	//$this->add('GET','/api/practica', 'app\\Controllers\\PracticaController@practica'); 
         
    }

    /**
     * 🔥 MODIFICADO: ahora acepta middleware
     */
    private function add($method, $route, $handler, $middlewares = []) {
        $this->routes[$method][$route] = [
            'handler' => $handler,
            'middlewares' => $middlewares
        ];
    }

    public function handleRequest() {

        $method = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        foreach ($this->routes[$method] as $route => $data) {

            $pattern = preg_replace('#\{[a-zA-Z_]+\}#', '([a-zA-Z0-9_-]+)', $route);

            if (preg_match('#^' . $pattern . '$#', $uri, $matches)) {

                array_shift($matches);

                /**
                 * 🔥 ejecutamos middleware antes del controller
                 */
                $this->runMiddlewares($data['middlewares']);

                return $this->dispatch($data['handler'], $matches);
            }
        }

        Response::error('Ruta no encontrada');
    }

	/**
	 * Ejecuta los middlewares definidos para una ruta.
	 *
	 * Idea:
	 * - auth / admin      => pensados para API o respuestas JSON
	 * - web_auth / web_admin => pensados para navegación normal de vistas
	 *
	 * Ejemplos:
	 * []                        => ruta pública
	 * ['auth']                  => API con sesión
	 * ['auth', 'admin']         => API solo admin
	 * ['web_auth']              => vista protegida
	 * ['web_auth', 'web_admin'] => vista web solo admin
	 */
	private function runMiddlewares($middlewares): void
	{
		foreach ($middlewares as $middleware) {

			switch ($middleware) {

				/**
				 * AUTH para API / JSON
				 *
				 * Si no hay sesión:
				 * - responde unauthorized
				 *
				 * Si expiró:
				 * - destruye sesión
				 * - responde sessionExpired
				 *
				 * Si todo está bien:
				 * - renueva actividad
				 */
				case 'auth':
					
					if (!Session::has('usuario')) {
						Response::unauthorized('No autenticado');
					}

					if (Session::isExpired()) {
						Session::destroy();
						Response::sessionExpired('Sesión expirada');
					}

					Session::touch();
					break;

				/**
				 * ADMIN para API / JSON
				 *
				 * Requiere que antes haya pasado por auth.
				 * Si no es admin:
				 * - responde forbidden
				 */
				case 'admin':
					if (!Session::isAdmin()) {
						Response::forbidden('No tienes permisos');
					}
					break;

				/**
				 * AUTH para navegación WEB normal
				 *
				 * Si no hay sesión:
				 * - destruye sesión
				 * - redirige a /login
				 *
				 * Si expiró:
				 * - destruye sesión
				 * - redirige a /login
				 *
				 * Si todo está bien:
				 * - renueva actividad
				 */
				case 'web_auth':
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

					Session::touch();
					break;

				/**
				 * ADMIN para navegación WEB normal
				 *
				 * Requiere que antes haya pasado por web_auth.
				 * Si no es admin:
				 * - redirige a /
				 */
				case 'web_admin':
					if (!Session::isAdmin()) {
						header('Location: /');
						exit;
					}
					break;
			}
		}
	}
	 

    private function dispatch($handler, $params) {
        list($class, $method) = explode('@', $handler);

        $controller = new $class;
		
		
        call_user_func_array([$controller, $method], $params);
    }
}
