<?php

namespace app\Core;
/*
 start()

Inicia la sesión si todavía no está iniciada.

set() / get() / has() / remove()

Operaciones básicas sobre $_SESSION.

destroy()

Cierra la sesión completamente, incluyendo cookie.

regenerate()

Cambia el ID de sesión. Úsalo después de login.

touch()

Actualiza el tiempo de última actividad.

isExpired()

Solo consulta si venció por inactividad.

isAdmin()

Revisa si el usuario autenticado tiene rol admin.

 * */
class Session
{
    /**
     * Tiempo máximo de inactividad permitido.
     *
     * 3600 segundos = 60 minutos
     * Si el usuario pasa más de este tiempo sin actividad,
     * consideraremos la sesión como expirada.
     */
    private const INACTIVITY_TIMEOUT = 3600;

    /**
     * Inicia la sesión solo si aún no fue iniciada.
     *
     * Esto evita errores por intentar iniciar la sesión múltiples veces.
     */
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Guarda un valor en la sesión.
     *
     * Ejemplo:
     * Session::set('usuario', [...]);
     */
    public static function set(string $key, mixed $value): void
    {
        self::start();
        $_SESSION[$key] = $value;
    }

    /**
     * Obtiene un valor de la sesión.
     *
     * Devuelve null si no existe.
     */
    public static function get(string $key): mixed
    {
        self::start();
        return $_SESSION[$key] ?? null;
    }

    /**
     * Verifica si existe una clave en la sesión.
     */
    public static function has(string $key): bool
    {
        self::start();
        return isset($_SESSION[$key]);
    }

    /**
     * Elimina una clave específica de la sesión.
     */
    public static function remove(string $key): void
    {
        self::start();
        unset($_SESSION[$key]);
    }

    /**
     * Destruye completamente la sesión.
     *
     * Qué hace:
     * 1. Inicia la sesión si aún no está iniciada.
     * 2. Limpia el arreglo $_SESSION.
     * 3. Elimina la cookie de sesión del navegador.
     * 4. Llama a session_unset() y session_destroy().
     *
     * Esto deja la sesión realmente cerrada de forma limpia.
     */
    public static function destroy(): void
    {
        self::start();

        // Limpiar variables de sesión en memoria
        $_SESSION = [];

        // Si PHP usa cookies para la sesión, eliminar la cookie del navegador
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();

            setcookie(
                session_name(), // nombre de la cookie de sesión
                '',             // valor vacío
                time() - 42000, // fecha en el pasado para invalidarla
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        // Limpiar y destruir la sesión a nivel interno de PHP
        session_unset();
        session_destroy();
    }

    /**
     * Regenera el ID de sesión.
     *
     * Muy recomendable después del login para evitar session fixation.
     *
     * true = elimina el ID anterior
     */
    public static function regenerate(bool $deleteOldSession = true): void
    {
        self::start();
        session_regenerate_id($deleteOldSession);
    }

    /**
     * Marca el momento actual como última actividad del usuario.
     *
     * Debe llamarse cuando una petición válida mantiene viva la sesión.
     */
    public static function touch(): void
    {
        self::start();
        $_SESSION['_last_activity'] = time();
    }

    /**
     * Devuelve el timestamp de la última actividad registrada.
     *
     * Si no existe aún, devuelve null.
     */
    public static function lastActivity(): ?int
    {
        self::start();
        return $_SESSION['_last_activity'] ?? null;
    }

    /**
     * Inicializa la marca de actividad solo si todavía no existe.
     *
     * Esto es útil después del login o en un primer acceso autenticado.
     */
    public static function initActivity(): void
    {
        self::start();

        if (!isset($_SESSION['_last_activity'])) {
            $_SESSION['_last_activity'] = time();
        }
    }

    /**
     * Verifica si la sesión ha expirado por inactividad.
     *
     * Importante:
     * - Este método SOLO consulta.
     * - No destruye la sesión.
     * - No renueva el tiempo.
     *
     * Eso permite que la lógica sea más clara:
     * 1. Preguntas si expiró
     * 2. Si expiró, destruyes
     * 3. Si no expiró, haces touch()
     */
    public static function isExpired(): bool
    {
        self::start();

        $lastActivity = $_SESSION['_last_activity'] ?? null;

        // Si no hay actividad registrada, aún no la consideramos expirada
        if ($lastActivity === null) {
            return false;
        }

        return (time() - $lastActivity) > self::INACTIVITY_TIMEOUT;
    }

    /**
     * Devuelve true si el usuario autenticado tiene rol admin.
     */
    public static function isAdmin(): bool
    {
        self::start();

        return self::has('usuario')
            && (($_SESSION['usuario']['rol'] ?? '') === 'admin');
    }
}
