<?php
/** Global helper functions available everywhere */

function currentLang(): string
{
    if (isset($_GET['lang']) && in_array($_GET['lang'], ['ar', 'en'], true)) {
        Session::set('lang', $_GET['lang']);
    }
    $default = function_exists('setting') ? (string)setting('default_language', 'ar') : 'ar';
    if (!in_array($default, ['ar', 'en'], true)) {
        $default = 'ar';
    }
    return Session::get('lang', $default);
}

function can(string $permission): bool
{
    return class_exists('Auth') && Auth::can($permission);
}

function json_safe($data): string
{
    return (string)json_encode($data, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
}

function __(string $key): string
{
    static $translations = null;
    if ($translations === null) {
        $lang = currentLang();
        $file = __DIR__ . "/../../lang/{$lang}.php";
        $translations = file_exists($file) ? require $file : [];
    }
    return $translations[$key] ?? $key;
}


function localized(?string $ar, ?string $en = null): string
{
    if (currentLang() === 'en' && $en !== null && trim($en) !== '') return $en;
    return $ar ?? '';
}

function translateRendered(string $html): string
{
    if (currentLang() !== 'en') return $html;
    $file = __DIR__ . '/../../lang/en.php';
    $translations = file_exists($file) ? require $file : [];
    $map = $translations['_text'] ?? [];
    if (!$map) return $html;

    // Translate only complete visible text nodes. Translating the entire HTML
    // with strtr() caused partial phrases such as "Rating المنصة" and could
    // accidentally alter Arabic user/provider data.
    return preg_replace_callback(
        '/>([^<>]*[\x{0600}-\x{06FF}][^<>]*)</u',
        static function ($m) use ($map) {
            $text = $m[1];
            $leading = '';
            $trailing = '';
            if (preg_match('/^\s+/u', $text, $x)) $leading = $x[0];
            if (preg_match('/\s+$/u', $text, $x)) $trailing = $x[0];
            $core = trim($text);
            if ($core === '' || !array_key_exists($core, $map)) return $m[0];
            return '>' . $leading . $map[$core] . $trailing . '<';
        },
        $html
    );
}

function langUrl(string $lang): string
{
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    $query = $_GET;
    $query['lang'] = $lang;
    return $path . '?' . http_build_query($query);
}

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function asset(string $path): string
{
    $cleanPath = ltrim($path, '/');
    $realFile = dirname(__DIR__, 2) . '/public/assets/' . $cleanPath;
    $v = file_exists($realFile) ? filemtime($realFile) : time();
    return BASE_PATH . '/public/assets/' . $cleanPath . '?v=' . $v;
}

function url(string $path = ''): string
{
    return BASE_PATH . '/' . ltrim($path, '/');
}

function old(string $key, $default = '')
{
    $old = Session::get('_old_input', []);
    return e($old[$key] ?? $default);
}

function flash(string $key): ?string
{
    return Session::getFlash($key);
}

function formatDate(?string $date): string
{
    if (!$date) return '-';
    return date('Y-m-d H:i', strtotime($date));
}

function statusBadge(string $status): string
{
    $map = [
        'new' => 'secondary', 'pending' => 'warning', 'accepted' => 'info',
        'in_progress' => 'primary', 'completed' => 'success',
        'cancelled' => 'dark', 'rejected' => 'danger',
        'open' => 'warning', 'in_review' => 'info', 'resolved' => 'success', 'closed' => 'secondary',
        'unpaid' => 'danger', 'paid' => 'success', 'refunded' => 'secondary',
    ];
    $color = $map[$status] ?? 'secondary';
    $labels = [
        'new' => ['جديد', 'New'], 'pending' => ['قيد الانتظار', 'Pending'], 'accepted' => ['مقبول', 'Accepted'],
        'in_progress' => ['قيد التنفيذ', 'In Progress'], 'completed' => ['مكتمل', 'Completed'], 'cancelled' => ['ملغى', 'Cancelled'],
        'rejected' => ['مرفوض', 'Rejected'], 'open' => ['مفتوح', 'Open'], 'in_review' => ['قيد المراجعة', 'In Review'],
        'resolved' => ['تم الحل', 'Resolved'], 'closed' => ['مغلق', 'Closed'], 'unpaid' => ['غير مدفوع', 'Unpaid'],
        'paid' => ['مدفوع', 'Paid'], 'refunded' => ['مسترد', 'Refunded'],
    ];
    $label = $labels[$status] ?? [ucfirst(str_replace('_', ' ', $status)), ucfirst(str_replace('_', ' ', $status))];
    return "<span class=\"badge bg-$color\">" . e($label[currentLang() === 'en' ? 1 : 0]) . "</span>";
}

function generateRequestNumber(int $id): string
{
    return 'REQ-' . date('Y') . '-' . str_pad($id, 6, '0', STR_PAD_LEFT);
}

function csrf_field(): string { return CSRF::field(); }

function setting(string $key, $default = null)
{
    static $cache = null;
    if ($cache === null) {
        $cache = [];
        try {
            require_once __DIR__ . '/../models/SystemSetting.php';
            $cache = (new SystemSetting())->allKeyed();
        } catch (Throwable $e) {
            $cache = [];
        }
    }
    return array_key_exists($key, $cache) ? $cache[$key] : $default;
}

function settingBool(string $key, bool $default = false): bool
{
    return in_array(strtolower((string)setting($key, $default ? '1' : '0')), ['1','true','yes','on'], true);
}

function currency(): string
{
    return (string)setting('currency', 'ر.ي');
}

function money($amount): string
{
    return number_format((float)$amount, 2) . ' ' . currency();
}

function renderBackButton(?string $url = null, ?string $text = null, ?string $icon = null, ?string $class = null): void
{
    $backUrl = $url;
    $backText = $text;
    $backIcon = $icon;
    $backClass = $class;
    include __DIR__ . '/../views/partials/back_button.php';
}

