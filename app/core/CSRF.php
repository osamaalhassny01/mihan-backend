<?php
/** CSRF - token generation and validation for all state-changing forms */
class CSRF
{
    public static function token(): string
    {
        if (empty($_SESSION['_csrf_token'])) {
            $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['_csrf_token'];
    }

    public static function rotateToken(): string
    {
        $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
        return $_SESSION['_csrf_token'];
    }

    public static function field(): string
    {
        return '<input type="hidden" name="_csrf" value="' . self::token() . '">';
    }

    public static function verify(?string $token): bool
    {
        if (empty($token) || empty($_SESSION['_csrf_token'])) return false;
        return hash_equals($_SESSION['_csrf_token'], $token);
    }

    public static function verifyRequest(): bool
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return true;
        return self::verify($_POST['_csrf'] ?? null);
    }
}
