<?php

namespace app\Controllers;

use app\Core\Response;
use app\Core\Session;

abstract class BaseWebController
{
    /**
     * Verifica sesión para navegación web normal.
     *
     * Si no hay usuario o la sesión expiró:
     * - destruye sesión
     * - redirige a /login
     *
     * Si todo está bien:
     * - renueva actividad con touch()
     */
    protected function verificarSesionWeb(): void
    {
        Session::start();

        // Si no existe usuario autenticado
        if (!Session::has('usuario')) {
            Session::destroy();
            $this->redirect('/login');
        }

        // Si la sesión expiró por inactividad
        if (Session::isExpired()) {
            Session::destroy();
            $this->redirect('/login');
        }

        // Sesión válida, se renueva actividad
        Session::touch();
    }

    /**
     * Verifica acceso admin para navegación web normal.
     *
     * Primero valida sesión web.
     * Luego valida rol admin.
     *
     * Si no es admin:
     * - redirige a /
     */
    protected function verificarAdminWeb(): void
    {
        $this->verificarSesionWeb();

        if (!Session::isAdmin()) {
            $this->redirect('/');
        }
    }



    /**
     * Detecta si la petición viene por AJAX/fetch.
     *
     * Criterios:
     * - Accept contiene application/json
     * - X-Requested-With = XMLHttpRequest
     *
     * Esto te sirve para diferenciar entre:
     * - navegación web tradicional
     * - peticiones dinámicas del frontend
     */
  	protected function isAjaxRequest(): bool
    {
        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        $xhr    = $_SERVER['HTTP_X_REQUESTED_WITH'] ?? '';

        return str_contains($accept, 'application/json')
            || strtolower($xhr) === 'xmlhttprequest';
    }

    /**
     * Renderiza una vista normal.
     *
     * Si el archivo no existe:
     * - responde notFound
     */
    protected function render(string $ruta): void
    {
        if (!file_exists($ruta)) {
            Response::notFound('Vista no encontrada.');
        }

        require_once $ruta;
    }

    /**
     * Renderiza una vista parcial.
     *
     * Útil para HTML que será inyectado dinámicamente.
     *
     * Si el archivo no existe:
     * - responde serverError
     */
    protected function renderPartial(string $ruta): void
    {
        if (!file_exists($ruta)) {
            Response::serverError('Archivo de vista no encontrado.');
        }

        require $ruta;
    }

    /**
     * Redirección centralizada.
     *
     * Siempre finaliza ejecución.
     */
    protected function redirect(string $ruta): void
    {
        header("Location: {$ruta}");
        exit;
    }
}
