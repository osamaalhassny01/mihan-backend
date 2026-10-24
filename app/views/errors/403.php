<?php
$lang = function_exists('currentLang') ? currentLang() : 'ar';
$dir = $lang === 'ar' ? 'rtl' : 'ltr';

// Role-based dashboard link
$dashUrl = '/';
$dashTitle = 'الرئيسية';
if (class_exists('Auth') && Auth::check()) {
    switch (Auth::role()) {
        case 'admin': $dashUrl = '/admin/dashboard'; $dashTitle = 'لوحة الإدارة'; break;
        case 'employee': $dashUrl = '/employee/dashboard'; $dashTitle = 'لوحة الموظف'; break;
        case 'provider': $dashUrl = '/provider/dashboard'; $dashTitle = 'لوحة مقدم الخدمة'; break;
        case 'customer': $dashUrl = '/customer/dashboard'; $dashTitle = 'لوحة العميل'; break;
    }
}
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>" dir="<?= $dir ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>403 | غير مصرح | <?= function_exists('setting') ? e(setting('site_name', 'مِهَن')) : 'مِهَن' ?></title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap<?= $dir === 'rtl' ? '.rtl' : '' ?>.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
  body { background: #f8fafc; font-family: "Segoe UI", Tahoma, Arial, sans-serif; color: #1e293b; }
  .error-card { max-width: 540px; }
  .error-icon { font-size: 3.5rem; color: #dc2626; }
</style>
</head>
<body class="d-flex align-items-center justify-content-center min-vh-100 p-3">
<div class="card shadow-sm border-0 error-card text-center p-4 p-md-5 rounded-4 bg-white">
  <div class="error-icon mb-3">
    <i class="fa-solid fa-shield-halved"></i>
  </div>
  <h1 class="display-5 fw-bold text-danger mb-2">403</h1>
  <h3 class="fw-bold mb-3">غير مصرح بالوصول</h3>
  <p class="text-muted fs-6 mb-4">
    عذراً، ليس لديك الصلاحيات الكافية للوصول إلى هذه الصفحة أو تنفيذ هذا الإجراء.
  </p>

  <div class="d-flex flex-wrap justify-content-center gap-2">
    <?php if (function_exists('renderBackButton')): ?>
      <?php renderBackButton($dashUrl, 'العودة للصفحة السابقة', $dir === 'rtl' ? 'fa-arrow-right' : 'fa-arrow-left', 'btn btn-outline-secondary px-3'); ?>
    <?php endif; ?>

    <?php if ($dashUrl !== '/'): ?>
      <a href="<?= function_exists('url') ? url($dashUrl) : $dashUrl ?>" class="btn btn-primary px-3 mb-3">
        <i class="fa-solid fa-gauge-high me-1"></i> <?= e($dashTitle) ?>
      </a>
    <?php endif; ?>

    <a href="<?= function_exists('url') ? url('/') : '/' ?>" class="btn btn-outline-primary px-3 mb-3">
      <i class="fa-solid fa-house me-1"></i> الرئيسية
    </a>
  </div>
</div>
</body>
</html>
