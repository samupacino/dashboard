<?php
namespace app\Core;

class Session {

    public static function start() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function set($key, $value) {
        $_SESSION[$key] = $value;
    }

    public static function get($key) {
        return $_SESSION[$key] ?? null;
    }

    public static function has($key) {
        return isset($_SESSION[$key]);
    }

    public static function remove($key) {
        unset($_SESSION[$key]);
    }

    public static function destroy() {
        if (session_status() !== PHP_SESSION_NONE) {
            session_destroy();
            $_SESSION = []; // limpiar variables también
        }
    }

    /**
     * Verifica si la sesión ha expirado por inactividad
     * @param int $timeoutMinutes Tiempo máximo de inactividad (min)
     * @return bool true si la sesión expiró, false si aún es válida
     */
    public static function isExpired($timeoutMinutes = 1) {
        self::start();

        $now = time();

        if (!isset($_SESSION['_last_activity'])) {
            $_SESSION['_last_activity'] = $now;
            return false;
        }

        $elapsed = $now - $_SESSION['_last_activity'];

        if ($elapsed > ($timeoutMinutes * 60)) {//$timeoutMinutes * 60
            return true;
        }

        // No renovar tiempo aquí si no se desea renovar la sesión con cada request
        return false;
    }

    public static function renovarTiempo() {
        self::start();
        $_SESSION['_last_activity'] = time();
    }

    public static function isAdmin() {
        return self::has('usuario') && ($_SESSION['usuario']['rol'] ?? '') === 'admin';
    }
}
