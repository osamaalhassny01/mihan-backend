<?php
require_once __DIR__ . '/../../models/Notification.php';
$notifModel = new Notification();
$unread = Auth::check() ? $notifModel->unreadCount(Auth::id()) : 0;
$userRole = Auth::role();
$roleBadge = [
    'admin' => ['label' => 'مدير النظام', 'color' => 'bg-dark text-white'],
    'employee' => ['label' => 'موظف المنصة', 'color' => 'bg-primary text-white'],
    'provider' => ['label' => 'مقدم خدمة', 'color' => 'bg-info text-dark'],
    'customer' => ['label' => 'عميل مستفيد', 'color' => 'bg-success text-white'],
][$userRole] ?? ['label' => $userRole, 'color' => 'bg-secondary text-white'];
?>
<header class="dash-topbar">
  <button class="icon-btn d-lg-none" data-sidebar-toggle type="button" aria-label="Toggle Navigation">
    <i class="fa-solid fa-bars"></i>
  </button>
  
  <div class="d-none d-md-flex align-items-center gap-2">
    <span class="badge <?= $roleBadge['color'] ?> px-3 py-2 fw-bold" style="font-size: 0.8rem; border-radius: 8px;">
      <i class="fa-solid fa-shield-halved me-1"></i> <?= $roleBadge['label'] ?>
    </span>
    <span class="text-muted small">| مرحبًا بك مجددًا في لوحة العمل</span>
  </div>

  <div class="ms-auto d-flex align-items-center gap-2 gap-md-3">
    <!-- Language Switcher -->
    <a href="<?= e(langUrl(currentLang() === 'ar' ? 'en' : 'ar')) ?>" class="icon-btn" title="تبديل اللغة">
      <span class="fw-bold small"><?= currentLang() === 'ar' ? 'EN' : 'ع' ?></span>
    </a>

    <!-- Notifications -->
    <a href="<?= url(Auth::role() . '/notifications') ?>" class="icon-btn position-relative" title="الإشعارات والتنبيهات">
      <i class="fa-solid fa-bell"></i>
      <?php if ($unread > 0): ?>
        <span class="badge rounded-pill bg-danger notif-badge"><?= $unread ?></span>
      <?php endif; ?>
    </a>

    <!-- User Profile Badge -->
    <div class="dash-user">
      <span class="dash-user-avatar">
        <i class="fa-solid fa-user"></i>
      </span>
      <span class="dash-user-name fw-bold"><?= e(Auth::name()) ?></span>
    </div>

    <!-- Logout Button -->
    <form method="POST" action="<?= url('/logout') ?>" class="d-inline">
      <?= csrf_field() ?>
      <button type="submit" class="icon-btn text-danger" title="<?= e(__('logout')) ?>">
        <i class="fa-solid fa-right-from-bracket"></i>
      </button>
    </form>
  </div>
</header>
