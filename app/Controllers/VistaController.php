<?php

namespace app\Controllers;

use app\Core\Session;
use app\Core\Response;

class VistaController extends BaseWebController
{
    /**
     * Muestra la vista de login.
     *
     * Lógica:
     * 1. Inicia la sesión por si aún no existe.
     * 2. Si ya hay un usuario autenticado y la sesión sigue vigente,
     *    no tiene sentido mostrar login otra vez, así que se redirige al dashboard.
     * 3. Si la sesión existe pero ya expiró por inactividad,
     *    se destruye para limpiar completamente el estado anterior.
     * 4. Si no hay sesión válida, se carga la vista login.php.
     */
	public function loginView(): void
	{
		Session::start();

		$tieneUsuario = Session::has('usuario');
		$expirada = Session::isExpired();

		// Si ya existe una sesión válida, renovamos actividad y mandamos al dashboard
		if ($tieneUsuario && !$expirada) {
			Session::touch();
			$this->redirect('/dashboard');
		}

		// Si la sesión existe pero expiró, la destruimos para dejar limpio el acceso
		if ($expirada) {
			Session::destroy();
		}

		// Mostrar formulario de login
		$this->render(ROOT . '/views/login.php');
	}
    /**
     * Muestra el menú principal del sistema.
     *
     * Esta vista se considera protegida, por lo tanto:
     * - exige sesión válida
     * - si no hay sesión o expiró, el BaseWebController redirige a /login
     */
    public function menu(): void
    {
        //$this->verificarSesionWeb();
        $this->render(ROOT . '/views/menu/menu.php');
    }

    /**
     * Endpoint de validación previa para entrar al módulo inglés.
     *
     * Está pensado para peticiones fetch/AJAX desde frontend.
     *
     * Flujo:
     * 1. Verifica que realmente sea una petición AJAX/fetch.
     * 2. Verifica sesión válida y rol admin.
     * 3. Si todo está correcto, responde JSON con la ruta de redirección.
     *
     * Si falla:
     * - redirige a / si no es AJAX
     * - responde JSON si no hay sesión o no tiene permisos
     */
	public function inglesAccess(): void
	{
		// Esta ruta está pensada solo para fetch / AJAX.
		// Si alguien entra directo por navegador, respondemos error JSON claro.
		if (!$this->isAjaxRequest()) {
			Response::error('Esta ruta solo acepta peticiones AJAX.');
		}


		// Si todo está correcto, devolvemos la ruta que el frontend debe abrir.
		Response::success([
			'redirect' => '/ingles'
		], 'Acceso autorizado');
	}
	
	public function registroInstrumentoAccess(): void
	{
		// Esta ruta está pensada solo para fetch / AJAX.
		// Si alguien entra directo por navegador, respondemos error JSON claro.
		if (!$this->isAjaxRequest()) {
			Response::error('Esta ruta solo acepta peticiones AJAX.');
		}

		// Si todo está correcto, devolvemos la ruta que el frontend debe abrir.
		Response::success([
			'redirect' => '/instrumento/registro'
		], 'Acceso autorizado');
	}
	
	public function registroInstrumento(): void 
	{
		$this->render(ROOT . '/views/practica/form.php');
	}
    
	public function instrumentoAccess(): void
	{
		// Esta ruta está pensada solo para fetch / AJAX.
		// Si alguien entra directo por navegador, respondemos error JSON claro.
		if (!$this->isAjaxRequest()) {
			Response::error('Esta ruta solo acepta peticiones AJAX.');
		}


		// Si todo está correcto, devolvemos la ruta que el frontend debe abrir.
		Response::success([
			'redirect' => '/instrumento'
		], 'Acceso autorizado');
	}
	public function instrumento(): void
    {
       
        $this->render(ROOT . '/views/practica/index.php');
    }
	
	
    /**
     * Muestra la vista principal del módulo inglés.
     *
     * Esta vista solo puede verla un usuario admin con sesión válida.
     * Si no cumple:
     * - se redirige según la lógica de BaseWebController
     */
    public function ingles(): void
    {
       
        $this->render(ROOT . '/views/ingles/index.php');
    }

    /**
     * Muestra la vista principal del dashboard.
     *
     * Requiere:
     * - sesión válida
     *
     * Si la sesión es válida:
     * - carga head
     * - carga dashboard
     * - carga footer
     */
    public function dashboardView(): void
    {
        

        require ROOT . '/views/layouts/head.php';
        require ROOT . '/views/dashboard.php';
        require ROOT . '/views/layouts/footer.php';
    }

    /**
     * Carga vistas parciales según el módulo solicitado.
     *
     * Este método está pensado para peticiones AJAX/fetch que cargan
     * HTML parcial dinámicamente dentro de la interfaz.
     *
     * Flujo:
     * 1. Verifica que la petición sea AJAX/fetch.
     * 2. Verifica que exista una sesión válida.
     * 3. Según el módulo pedido, carga la vista parcial correspondiente.
     * 4. Si el módulo no existe, responde 404 en JSON.
     *
     * Nota:
     * - En el caso del módulo "usuario", además exige rol admin.
     */
    public function vistaParcial($modulo): void
    {
    
        // Verifica que el usuario tenga sesión activa
        //$this->verificarSesionAjax();

        switch ($modulo) {
            case 'usuario':
                // Este parcial solo lo puede ver un admin
                if (!Session::isAdmin()) {
                    Response::forbidden('No tienes permisos para ver usuarios.');
                }

                $this->renderPartial(ROOT . '/views/usuario/usuario.php');
                break;

            case 't155':
                $this->renderPartial(ROOT . '/views/instrumento_t155/index.php');
                break;

            case 'perfil':
                $this->renderPartial(ROOT . '/views/perfil/index.php');
                break;

            case 'pl3':
                $this->renderPartial(ROOT . '/views/instrumento_pl3/index.php');
                break;

            default:
                Response::notFound('Vista no encontrada.');
        }
    }
}
