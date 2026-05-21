<?php
// NOTA IMPORTANTE:
// Al enviar datos JSON desde el servidor al frontend,
// solo se reconocerán como un ARRAY en JavaScript
// si se devuelven directamente entre corchetes [] SIN clave externa.
//
// ✅ Ejemplo que SÍ es array:
// [
//   { "id": 1, "nombre": "Samuel" },
//   { "id": 2, "nombre": "Luján" }
// ]
// JS: Array.isArray(json) === true
//
// ❌ Ejemplo que NO es array (es un objeto que contiene un array):
// {
//   "datos": [
//     { "id": 1, "nombre": "Samuel" },
//     { "id": 2, "nombre": "Luján" }
//   ]
// }
// JS: Array.isArray(json) === false
//     Array.isArray(json.datos) === true
//
// En resumen:
// - Si el array está dentro de una clave (por ejemplo: "datos"), 
//   se debe acceder a esa clave y verificar con Array.isArray(json.datos).
// - Si el JSON completo comienza con { ... }, siempre será tratado como un objeto en JS.
// - Solo los datos iniciando directamente con [ ... ] serán detectados como un array en la raíz.

namespace app\Core;

class Response
{
    // =========================
    // Códigos HTTP
    // =========================
    public const HTTP_OK = 200;
    public const HTTP_CREATED = 201;
    public const HTTP_NO_CONTENT = 204;

    public const HTTP_BAD_REQUEST = 400;
    public const HTTP_UNAUTHORIZED = 401;
    public const HTTP_FORBIDDEN = 403;
    public const HTTP_NOT_FOUND = 404;
    public const HTTP_CONFLICT = 409;
    public const HTTP_UNPROCESSABLE_ENTITY = 422;
    public const HTTP_INTERNAL_SERVER_ERROR = 500;

    // =========================
    // Estados de respuesta
    // =========================
    public const STATUS_SUCCESS = 'success';
    public const STATUS_LOGOUT = 'logout';
    public const STATUS_ERROR = 'error';
    public const STATUS_FAIL = 'fail';
    public const STATUS_UNAUTHORIZED = 'unauthorized';
    public const STATUS_FORBIDDEN = 'forbidden';
    public const STATUS_SESSION_EXPIRED = 'session_expired';
    public const STATUS_NOT_FOUND = 'not_found';
    public const STATUS_CONFLICT = 'conflict';

    /**
     * Envía una respuesta JSON al cliente.
     *
     * Uso interno:
     * self::json(['status' => 'success', 'mensaje' => 'OK'], self::HTTP_OK);
     */
    private static function json(array $payload, int $status = self::HTTP_OK, bool $exit = true): void
    {
        http_response_code($status);

        if (!headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
        }

        $json = json_encode(
            $payload,
            JSON_UNESCAPED_UNICODE
            | JSON_UNESCAPED_SLASHES
            | JSON_INVALID_UTF8_SUBSTITUTE
        );

        if ($json === false) {
            http_response_code(self::HTTP_INTERNAL_SERVER_ERROR);

            echo json_encode([
                'status'  => self::STATUS_ERROR,
                'mensaje' => 'Error al codificar JSON',
                'data'    => [
                    'detalle' => json_last_error_msg()
                ],
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

            if ($exit) {
                exit;
            }

            return;
        }

        echo $json;

        if ($exit) {
            exit;
        }
    }
    
    public static function raw(array $data): void
	{
		self::json($data, self::HTTP_OK);
	}

    /**
     * Respuesta especial para DataTables.
     *
     * Cómo usar:
     * Response::datatable($resultadoDatatable);
     */
    public static function datatable(array $payload): void
    {
        self::json($payload, self::HTTP_OK);
    }

    /**
     * Respuesta exitosa estándar.
     *
     * Cómo usar:
     * Response::success();
     * Response::success(['id' => 1], 'Registro obtenido');
     * Response::success(['id' => 1], 'Actualizado correctamente', Response::HTTP_OK);
     */
    public static function success(
        array $data = [],
        string $mensaje = 'Operación realizada correctamente',
        int $status = self::HTTP_OK
    ): void {
        self::json([
            'status'  => self::STATUS_SUCCESS,
            'mensaje' => $mensaje,
            'data'    => $data
        ], $status);
    }

    /**
     * Recurso creado.
     *
     * Cómo usar:
     * Response::created();
     * Response::created(['id' => 10], 'Usuario creado correctamente');
     */
    public static function created(
        array $data = [],
        string $mensaje = 'Recurso creado correctamente'
    ): void {
        self::json([
            'status'  => self::STATUS_SUCCESS,
            'mensaje' => $mensaje,
            'data'    => $data
        ], self::HTTP_CREATED);
    }

    /**
     * Error general.
     *
     * Cómo usar:
     * Response::error();
     * Response::error('No se pudo guardar');
     * Response::error('No autorizado', Response::HTTP_UNAUTHORIZED);
     * Response::error('Error al registrar', Response::HTTP_BAD_REQUEST, ['detalle' => 'El correo ya existe']);
     */
    public static function error(
        string $mensaje = 'Ocurrió un error',
        int $status = self::HTTP_BAD_REQUEST,
        array $data = []
    ): void {
        self::json([
            'status'  => self::STATUS_ERROR,
            'mensaje' => $mensaje,
            'data'    => $data
        ], $status);
    }

    /**
     * Error de validación.
     *
     * Cómo usar:
     * Response::validation(['nombre' => 'El nombre es obligatorio']);
     * Response::validation(['email' => 'Correo inválido'], 'Revise los campos del formulario');
     */
    public static function validation(
        array $errors = [],
        string $mensaje = 'Errores de validación'
    ): void {
        self::json([
            'status'  => self::STATUS_FAIL,
            'mensaje' => $mensaje,
            'errors'  => $errors
        ], self::HTTP_UNPROCESSABLE_ENTITY);
    }

    /**
     * Usuario no autenticado.
     *
     * Cómo usar:
     * Response::unauthorized();
     * Response::unauthorized('Debe iniciar sesión primero');
     */
    public static function unauthorized(
        string $mensaje = 'Debe iniciar sesión para continuar'
    ): void {
        self::json([
            'status'  => self::STATUS_UNAUTHORIZED,
            'mensaje' => $mensaje
        ], self::HTTP_UNAUTHORIZED);
    }

    /**
     * Usuario autenticado pero sin permisos.
     *
     * Cómo usar:
     * Response::forbidden();
     * Response::forbidden('Solo el administrador puede realizar esta acción');
     */
    public static function forbidden(
        string $mensaje = 'No tiene permisos para realizar esta acción'
    ): void {
        self::json([
            'status'  => self::STATUS_FORBIDDEN,
            'mensaje' => $mensaje
        ], self::HTTP_FORBIDDEN);
    }

    /**
     * Sesión expirada.
     *
     * Cómo usar:
     * Response::sessionExpired();
     * Response::sessionExpired('La sesión venció, vuelva a ingresar');
     */
    public static function sessionExpired(
        string $mensaje = 'Sesión expirada. Por favor inicie sesión nuevamente'
    ): void {
        self::json([
            'status'  => self::STATUS_SESSION_EXPIRED,
            'mensaje' => $mensaje
        ], self::HTTP_UNAUTHORIZED);
    }

    /**
     * Recurso no encontrado.
     *
     * Cómo usar:
     * Response::notFound();
     * Response::notFound('Usuario no encontrado');
     */
    public static function notFound(
        string $mensaje = 'Recurso no encontrado'
    ): void {
        self::json([
            'status'  => self::STATUS_NOT_FOUND,
            'mensaje' => $mensaje
        ], self::HTTP_NOT_FOUND);
    }

    /**
     * Conflicto de datos.
     *
     * Cómo usar:
     * Response::conflict();
     * Response::conflict('El registro ya existe');
     * Response::conflict('Conflicto al guardar', ['campo' => 'email']);
     */
    public static function conflict(
        string $mensaje = 'Conflicto con el recurso',
        array $data = []
    ): void {
        self::json([
            'status'  => self::STATUS_CONFLICT,
            'mensaje' => $mensaje,
            'data'    => $data
        ], self::HTTP_CONFLICT);
    }

    /**
     * Error interno del servidor.
     *
     * Cómo usar:
     * Response::serverError();
     * Response::serverError('Error en la base de datos');
     * Response::serverError('Excepción no controlada', ['detalle' => $e->getMessage()]);
     */
    public static function serverError(
        string $mensaje = 'Error interno del servidor',
        array $data = []
    ): void {
        self::json([
            'status'  => self::STATUS_ERROR,
            'mensaje' => $mensaje,
            'data'    => $data
        ], self::HTTP_INTERNAL_SERVER_ERROR);
    }

    /**
     * Cierre de sesión correcto.
     *
     * Cómo usar:
     * Response::logout();
     * Response::logout('Sesión cerrada correctamente');
     */
    public static function logout(
        string $mensaje = 'Sesión cerrada correctamente'
    ): void {
        self::json([
            'status'  => self::STATUS_LOGOUT,
            'mensaje' => $mensaje,
            'data'    => []
        ], self::HTTP_OK);
    }
}
