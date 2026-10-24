<?php
/** Session - thin wrapper for session data + flash messages */
class Session
{
    public static function set(string $key, $value): void { $_SESSION[$key] = $value; }
    public static function get(string $key, $default = null) { return $_SESSION[$key] ?? $default; }
    public static function has(string $key): bool { return isset($_SESSION[$key]); }
    public static function remove(string $key): void { unset($_SESSION[$key]); }

    public static function flash(string $key, string $message): void
    {
        $_SESSION['_flash'][$key] = $message;
    }

    public static function getFlash(string $key): ?string
    {
        if (!isset($_SESSION['_flash'][$key])) return null;
        $msg = $_SESSION['_flash'][$key];
        unset($_SESSION['_flash'][$key]);
        return $msg;
    }

    public static function destroy(): void
    {
        $_SESSION = [];
        if (!headers_sent() && ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            @setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        if (session_status() === PHP_SESSION_ACTIVE) {
            @session_destroy();
        }
    }
}
