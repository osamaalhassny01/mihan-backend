<?php
/**
 * App - bootstraps the router and wraps the whole request in centralized
 * error handling so the visitor never sees a raw PHP/PDO stack trace.
 *
 * In development (APP_ENV=development) the real exception is logged AND
 * shown, to make debugging fast. In production, only a generic Arabic
 * message is shown and full details go to storage/logs/app.log.
 */
class App
{
    public static function run(): void
    {
        set_error_handler([self::class, 'handleError']);
        set_exception_handler([self::class, 'handleException']);
        register_shutdown_function([self::class, 'handleShutdown']);

        try {
            $timezone = function_exists('setting') ? (string)setting('timezone', 'Asia/Aden') : 'Asia/Aden';
            try { new DateTimeZone($timezone); date_default_timezone_set($timezone); } catch (Throwable $ignore) { date_default_timezone_set('Asia/Aden'); }
            $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
            if (function_exists('settingBool') && settingBool('force_https') && (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== 'on')) {
                $host = $_SERVER['HTTP_HOST'] ?? '';
                if ($host !== '') { header('Location: https://' . $host . $path); exit; }
            }
            if (function_exists('settingBool') && settingBool('show_system_errors')) {
                ini_set('display_errors', '1'); error_reporting(E_ALL);
            }
            // Maintenance mode blocks the public area while keeping login and admin access available.
            $adminSession = (class_exists('Auth') && Auth::check() && Auth::role() === 'admin');
            $allowed = in_array($path, [BASE_PATH.'/login', BASE_PATH.'/logout'], true) || str_starts_with($path, BASE_PATH.'/public/');
            if (function_exists('settingBool') && settingBool('maintenance_mode') && !$adminSession && !$allowed) {
                http_response_code(503);
                $message = function_exists('setting') ? setting('maintenance_message', 'المنصة تحت الصيانة المؤقتة، نعود إليكم قريبًا.') : 'المنصة تحت الصيانة المؤقتة، نعود إليكم قريبًا.';
                echo '<!doctype html><html lang="ar" dir="rtl"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>وضع الصيانة</title><style>body{font-family:Arial,sans-serif;background:#f6f8fb;display:grid;place-items:center;min-height:100vh;margin:0}.box{background:#fff;max-width:620px;padding:40px;border-radius:20px;box-shadow:0 10px 40px #0001;text-align:center}.icon{font-size:54px;margin-bottom:12px}h1{margin:0 0 12px;color:#172033}p{color:#667085;line-height:1.8}</style></head><body><div class="box"><div class="icon">🛠️</div><h1>المنصة تحت الصيانة</h1><p>' . htmlspecialchars((string)$message, ENT_QUOTES, 'UTF-8') . '</p></div></body></html>';
                return;
            }
            $router = require __DIR__ . '/../../routes/web.php';
            $router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
        } catch (Throwable $e) {
            self::handleException($e);
        }
    }

    /** Convert classic PHP warnings/notices into loggable, non-fatal entries */
    public static function handleError(int $severity, string $message, string $file, int $line): bool
    {
        if (!(error_reporting() & $severity)) return false;
        self::log("PHP Error [$severity]: $message in $file:$line");
        // Let normal PHP error handling continue for fatal severities.
        return in_array($severity, [E_WARNING, E_NOTICE, E_DEPRECATED, E_USER_WARNING, E_USER_NOTICE, E_USER_DEPRECATED], true);
    }

    /** Catches any uncaught Exception/Error/PDOException, e.g. schema/query mistakes */
    public static function handleException(Throwable $e): void
    {
        self::log(get_class($e) . ': ' . $e->getMessage() . "\n" . $e->getTraceAsString());

        if (ob_get_level() > 0) { @ob_end_clean(); }
        http_response_code(500);

        $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
        $base = defined('BASE_PATH') ? BASE_PATH : '';
        $relative = ($base !== '' && str_starts_with($path, $base)) ? substr($path, strlen($base)) : $path;

        if (str_starts_with($relative, '/api/') || str_starts_with($path, '/api/')) {
            header('Content-Type: application/json; charset=utf-8');
            header('Access-Control-Allow-Origin: *');
            echo json_encode([
                'success' => false,
                'message' => 'حدث خطأ في معالجة طلب API بالخادم: ' . $e->getMessage(),
                'errors'  => (defined('APP_ENV') && APP_ENV === 'development') ? [
                    'exception' => get_class($e),
                    'file'      => $e->getFile(),
                    'line'      => $e->getLine(),
                ] : null,
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            return;
        }

        if (defined('APP_ENV') && APP_ENV === 'development') {
            echo '<div style="direction:ltr;text-align:left;font-family:monospace;background:#1e1e1e;color:#f88;padding:24px;white-space:pre-wrap">';
            echo '<h2 style="color:#f66">Uncaught ' . htmlspecialchars(get_class($e)) . '</h2>';
            echo htmlspecialchars($e->getMessage()) . "\n\n" . htmlspecialchars($e->getFile()) . ':' . $e->getLine();
            echo "\n\n" . htmlspecialchars($e->getTraceAsString());
            echo '</div>';
            return;
        }

        $file = __DIR__ . '/../views/errors/500.php';
        if (file_exists($file)) {
            require $file;
        } else {
            echo 'حدث خطأ غير متوقع. حاول مرة أخرى.';
        }
    }

    /** Catches true fatal errors (out of memory, syntax-level, etc.) that skip exceptions entirely */
    public static function handleShutdown(): void
    {
        $error = error_get_last();
        if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
            self::log("Fatal shutdown error: {$error['message']} in {$error['file']}:{$error['line']}");
            if (!headers_sent()) http_response_code(500);
            if (!(defined('APP_ENV') && APP_ENV === 'development')) {
                $file = __DIR__ . '/../views/errors/500.php';
                if (file_exists($file)) require $file;
            }
        }
    }

    private static function log(string $message): void
    {
        $dir = __DIR__ . '/../../storage/logs';
        if (!is_dir($dir)) @mkdir($dir, 0775, true);
        @file_put_contents(
            $dir . '/app.log',
            '[' . date('Y-m-d H:i:s') . '] ' . $message . PHP_EOL,
            FILE_APPEND
        );
    }
}
