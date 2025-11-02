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
    /**
     * Envía una respuesta JSON al cliente
     */
    public static function json($data, int $status = 200, bool $exit = true): void
    {
        http_response_code($status);

        if (!headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
        }


        

        $json = json_encode($data, JSON_UNESCAPED_UNICODE);

        if ($json === false) {
            http_response_code(500);
            echo json_encode([
                'error' => 'Error al codificar JSON',
                'detalle' => json_last_error_msg()
            ]);
            if ($exit) exit;
        }

        echo $json;

        if ($exit) exit;
    }

    /**
     * Envía una respuesta exitosa estándar
     */
    public static function success($data = [], string $mensaje = '', int $status = 200): void
    {
        self::json([
            'status' => 'success',
            'mensaje' => $mensaje,
            'data' => $data
        ], $status);
    }

    /**
     * Envía una respuesta de error estandarizada
     */
    public static function error(string $mensaje = 'Ocurrió un error', int $status = 400, $data = []): void
    {
        self::json([
            'status' => 'error',
            'mensaje' => $mensaje,
            'data' => $data
        ], $status);
    }

    /**
     * Envía una respuesta cuando la sesión está expirada o no hay autenticación
     */
    public static function unauthorized(string $mensaje = 'No autorizado'): void
    {
        self::json([
            'status' => 'unauthorized',
            'mensaje' => $mensaje
        ], 401);
    }

    /**
     * Respuesta cuando la sesión del usuario ya expiró / debe reautenticarse.
     *
     * Nota: usamos el código 440 (Login Time-out) aquí por claridad semántica,
     * pero puedes cambiarlo a 419 (Laravel uses) o a 401 si prefieres.
    */
    public static function sessionExpired(string $mensaje = 'Sesión expirada. Por favor inicie sesión de nuevo'): void
    {
        self::json([
            'status'  => 'session_expired',
            'mensaje' => $mensaje
        ], 401);
    }

    /**
     * Finaliza sesión de forma controlada y notifica al frontend
     */
    public static function logout(string $mensaje = 'Sesión cerrada correctamente'): void
    {
        self::json([
            'status' => 'logout',
            'mensaje' => $mensaje
        ], 200); //  HTTP 200: operación válida
    }


    /**
     * Envía una respuesta de tipo "data" para retornar contenido sin indicar éxito/error explícito
     */
    public static function data($data = [], string $mensaje = 'Datos enviados correctamente', int $status = 200): void
    {
        self::json([
            'status' => 'ok',
            'mensaje' => $mensaje,
            'datos' => $data
        ], $status);
    }
}


/*
namespace app\Core;

class Response {
    public static function json($data, $status = 200) {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
    }
    public static function response_test(){
        echo "samuel desde response";
    }
}*/
?>