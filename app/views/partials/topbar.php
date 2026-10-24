<?php require_once __DIR__.'/../../models/Notification.php'; $notifModel=new Notification(); $unread=Auth::check()?$notifModel->unreadCount(Auth::id()):0; ?>
<header class="dash-topbar">
 <button class="icon-btn d-lg-none" data-sidebar-toggle type="button"><i class="fa-solid fa-bars"></i></button>
 <div class="ms-auto d-flex align-items-center gap-2 gap-md-3">
  <a href="<?= e(langUrl(currentLang()==='ar'?'en':'ar')) ?>" class="text-decoration-none small fw-semibold"><?= currentLang()==='ar'?'EN':'AR' ?></a>
  <a href="<?= url(Auth::role().'/notifications') ?>" class="icon-btn position-relative"><i class="fa-solid fa-bell"></i><?php if($unread>0): ?><span class="badge rounded-pill bg-danger notif-badge"><?= $unread ?></span><?php endif; ?></a>
  <div class="dash-user"><span class="dash-user-avatar"><i class="fa-solid fa-user"></i></span><span class="dash-user-name fw-semibold"><?= e(Auth::name()) ?></span></div>
  <form method="POST" action="<?= url('/logout') ?>" class="d-inline"><?= csrf_field() ?><button type="submit" class="icon-btn text-danger" title="<?= e(__('logout')) ?>"><i class="fa-solid fa-right-from-bracket"></i></button></form>
 </div>
</header>
