<?php
function loadEnv($path) {
    if (!file_exists($path)) return;
    foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') === false) continue;
        list($key, $value) = explode('=', $line, 2);
        $key = trim($key); $value = trim($value);
        if (getenv($key) === false) {
            putenv("$key=$value");
            $_ENV[$key] = $value;
        }
    }
}
loadEnv(__DIR__ . '/../.env');

function env($key, $default = null) {
    $value = getenv($key);
    return $value === false ? $default : $value;
}

$defaultAppUrl = 'http://localhost/mihan';
if (getenv('RAILWAY_PUBLIC_DOMAIN')) {
    $defaultAppUrl = 'https://' . getenv('RAILWAY_PUBLIC_DOMAIN');
} elseif (getenv('RAILWAY_STATIC_URL')) {
    $defaultAppUrl = 'https://' . getenv('RAILWAY_STATIC_URL');
}

define('APP_NAME', env('APP_NAME', 'مِهَن - MIHAN'));
define('APP_URL', env('APP_URL', $defaultAppUrl));
define('APP_ENV', env('APP_ENV', env('RAILWAY_ENVIRONMENT') ? 'production' : 'development'));

// Base path is derived from APP_URL so local subfolders and production URLs remain consistent.
$appUrlPath = parse_url(APP_URL, PHP_URL_PATH) ?: '';
$appUrlPath = rtrim($appUrlPath, '/');
define('BASE_PATH', $appUrlPath);

// Database configuration supporting Railway MySQL, Docker, and standard env vars
$dbUrl = env('DATABASE_URL', env('MYSQL_URL'));
if ($dbUrl) {
    $dbParts = parse_url($dbUrl);
    define('DB_HOST', $dbParts['host'] ?? 'localhost');
    define('DB_PORT', (string)($dbParts['port'] ?? 3306));
    define('DB_USER', $dbParts['user'] ?? 'root');
    define('DB_PASS', $dbParts['pass'] ?? '');
    define('DB_NAME', ltrim($dbParts['path'] ?? 'mihan', '/'));
} else {
    define('DB_HOST', env('DB_HOST', env('MYSQLHOST', 'localhost')));
    define('DB_PORT', (string)env('DB_PORT', env('MYSQLPORT', '3306')));
    define('DB_NAME', env('DB_NAME', env('MYSQLDATABASE', 'mihan')));
    define('DB_USER', env('DB_USER', env('MYSQLUSER', 'root')));
    define('DB_PASS', env('DB_PASS', env('MYSQLPASSWORD', '')));
}
define('DB_CHARSET', 'utf8mb4');

define('AI_PROVIDER', env('AI_PROVIDER', 'local'));
define('AI_API_KEY', env('AI_API_KEY', ''));
define('AI_MODEL', env('AI_MODEL', ''));
define('AI_ENCRYPTION_KEY', env('AI_ENCRYPTION_KEY', ''));

define('UPLOAD_MAX_SIZE', 3 * 1024 * 1024);
define('UPLOAD_ALLOWED_EXT', ['jpg', 'jpeg', 'png', 'webp']);
define('UPLOAD_ALLOWED_MIME', ['image/jpeg', 'image/png', 'image/webp']);

define('PLATFORM_COMMISSION_RATE', 0.10);

session_name('mihan_session');
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'httponly' => true,
        'samesite' => 'Lax',
        'secure' => (APP_ENV === 'production'),
    ]);
    session_start();
    if (empty($_SESSION['__regenerated'])) {
        session_regenerate_id(true);
        $_SESSION['__regenerated'] = true;
    }
}

error_reporting(APP_ENV === 'development' ? E_ALL : 0);
ini_set('display_errors', APP_ENV === 'development' ? '1' : '0');
date_default_timezone_set('Asia/Aden');
