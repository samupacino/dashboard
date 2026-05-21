<?php

namespace app\Controllers;

use app\Core\Response;
use app\Core\Session;

abstract class BaseApiController
{
    public function __construct()
    {
    }

    /**
     * Verifica sesión para endpoints API / AJAX / fetch.
     *
     * Lógica:
     * 1. Inicia sesión
     * 2. Verifica si existe usuario autenticado
     * 3. Verifica si expiró por inactividad
     * 4. Si todo está bien, renueva la actividad con touch()
     *
     * Si falla:
     * - responde JSON con Response
     * - no redirige
     */
    protected function verificarSesion(): void
    {
        Session::start();

        // No hay usuario autenticado
        if (!Session::has('usuario')) {
            Response::unauthorized('No autenticado.');
        }

        // La sesión existe pero expiró por inactividad
        if (Session::isExpired()) {
            Session::destroy();
            Response::sessionExpired('Sesión expirada. Inicie sesión nuevamente.');
        }

        // La sesión sigue activa, renovamos tiempo de actividad
        Session::touch();
    }

    /**
     * Verifica si el usuario autenticado además es admin.
     *
     * Primero valida sesión activa.
     * Luego valida rol.
     *
     * Si no es admin:
     * - responde 403 forbidden
     */
    protected function verificarAdmin(string $mensaje = 'No tiene permisos para esta acción.'): void
    {
        $this->verificarSesion();

        if (!Session::isAdmin()) {
            Response::forbidden($mensaje);
        }
    }

    /**
     * Lee y decodifica el body JSON de la petición.
     *
     * Si el JSON es inválido:
     * - responde con error 400
     *
     * Devuelve un array siempre que la entrada sea válida o vacía.
     */
    protected function obtenerJsonInput(): array
    {
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Response::error('JSON inválido.', 400, [
                'detalle' => json_last_error_msg()
            ]);
        }

        return is_array($data) ? $data : [];
    }
    
		/**
	 * Obtiene datos enviados como multipart/form-data (formularios con archivos).
	 *
	 * Retorna:
	 * [
	 *   'fields' => [...], // datos de $_POST
	 *   'files'  => [...]  // datos de $_FILES
	 * ]
	 *
	 * Uso:
	 * $input = $this->obtenerFormInput();
	 * $data  = $input['fields'];
	 * $files = $input['files'];
	 */
	protected function obtenerFormInput(): array
	{
		// Si no hay datos POST ni archivos, algo está mal
		if (empty($_POST) && empty($_FILES)) {
			Response::error('No se recibieron datos del formulario.', 400);
		}

		return [
			'fields' => $_POST,
			'files'  => $_FILES
		];
	}

    /**
     * Valida campos requeridos de forma genérica.
     *
     * Uso:
     * $this->validarRequeridos(
     *     ['tag' => $tag, 'plataforma' => $plataforma],
     *     ['tag' => 'tag', 'plataforma' => 'plataforma']
     * );
     *
     * Si encuentra errores:
     * - responde 422 validation
     *
     * Nota:
     * - Este método se mantiene por compatibilidad con controladores antiguos.
     * - Los controladores nuevos pueden usar Validator + validateOrFail().
     */
    protected function validarRequeridos(array $campos, array $labels = []): void
    {
        $errors = [];

        foreach ($campos as $campo => $valor) {
            $label = $labels[$campo] ?? $campo;

            if (is_string($valor)) {
                $valor = trim($valor);
            }

            if ($valor === null || $valor === '') {
                $errors[$campo] = ["El campo {$label} es obligatorio."];
            }
        }

        if (!empty($errors)) {
            Response::validation($errors, 'Faltan campos requeridos.');
        }
    }

    /**
     * Responde automáticamente con error de validación si el arreglo
     * de errores contiene elementos.
     *
     * Uso recomendado en controladores nuevos:
     *
     * $errors = [];
     * $id = Validator::requirePositiveInt($id, 'id', $errors);
     * $nombre = Validator::requireString($data['nombre'] ?? null, 'nombre', $errors);
     *
     * $this->validateOrFail($errors);
     *
     * Ventajas:
     * - evita repetir:
     *   if (!empty($errors)) { Response::validation(...); }
     * - deja los controladores más limpios
     * - funciona con el formato actual:
     *   ['campo' => ['mensaje1', 'mensaje2']]
     *
     * Nota:
     * - No reemplaza validarRequeridos(); ambos pueden convivir.
     * - Está pensado para el nuevo flujo con Validator.
     */
    protected function validateOrFail(array $errors, string $mensaje = 'Errores de validación'): void
    {
        if (!empty($errors)) {
            Response::validation($errors, $mensaje);
        }
    }
}
