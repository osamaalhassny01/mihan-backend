<?php
/**
 * Unified Back Button Partial for MIHAN Platform
 *
 * Usage:
 *   <?php renderBackButton('/customer/requests', 'العودة إلى طلباتي'); ?>
 *   or:
 *   <?php $backUrl = '/customer/requests'; $backText = 'العودة إلى طلباتي'; include __DIR__ . '/../partials/back_button.php'; ?>
 */

$isRtl = (currentLang() === 'ar');
$defaultIcon = $isRtl ? 'fa-arrow-right' : 'fa-arrow-left';

// Determine safe fallback URL based on current role
$roleFallback = '/';
if (Auth::check()) {
    switch (Auth::role()) {
        case 'admin': $roleFallback = '/admin/dashboard'; break;
        case 'employee': $roleFallback = '/employee/dashboard'; break;
        case 'provider': $roleFallback = '/provider/dashboard'; break;
        case 'customer': $roleFallback = '/customer/dashboard'; break;
    }
}

// Compute target URL
$targetUrl = !empty($backUrl) ? $backUrl : null;

// Safe Referer validation (prevent loop to error pages or external domains)
if (!$targetUrl && !empty($_SERVER['HTTP_REFERER'])) {
    $referer = $_SERVER['HTTP_REFERER'];
    $refererPath = parse_url($referer, PHP_URL_PATH) ?? '';
    $currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '';

    $isErrorPage = preg_match('/\b(403|404|500|error)\b/i', $refererPath);
    $isSamePage = ($refererPath === $currentPath);

    // Verify it is local to our BASE_PATH and not an error page or same page
    if (!$isErrorPage && !$isSamePage && (BASE_PATH === '' || str_starts_with($refererPath, BASE_PATH))) {
        // Strip BASE_PATH to get route
        $relPath = BASE_PATH !== '' && str_starts_with($refererPath, BASE_PATH)
            ? substr($refererPath, strlen(BASE_PATH))
            : $refererPath;
        if ($relPath !== '' && $relPath !== false) {
            $targetUrl = $relPath;
        }
    }
}

if (!$targetUrl) {
    $targetUrl = $roleFallback;
}

$text = $backText ?? 'رجوع';
$icon = $backIcon ?? $defaultIcon;
$class = $backClass ?? 'btn btn-outline-secondary btn-sm';
?>
<div class="back-button-wrapper mb-3">
  <a href="<?= url($targetUrl) ?>" class="btn-back <?= e($class) ?> d-inline-flex align-items-center gap-1">
    <i class="fa-solid <?= e($icon) ?>"></i>
    <span><?= e($text) ?></span>
  </a>
</div>
