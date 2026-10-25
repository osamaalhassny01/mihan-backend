<nav class="navbar navbar-expand-lg navbar-dark brand-nav">
  <div class="container">
    <a class="navbar-brand fw-bold d-flex align-items-center" href="<?= url('/') ?>">
      <img class="brand-logo-mark" src="<?= asset('images/logo-mihan-mark.png') ?>" alt="<?= e(__('app_name')) ?>"> 
      <span><?= e(setting('site_name', __('app_name'))) ?></span>
    </a>
    
    <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#navMain" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    
    <div class="collapse navbar-collapse" id="navMain">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item">
          <a class="nav-link" href="<?= url('/') ?>"><i class="fa-solid fa-house"></i> <?= __('home') ?></a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="<?= url('/services') ?>"><i class="fa-solid fa-layer-group"></i> <?= __('services') ?></a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="<?= url('/providers') ?>"><i class="fa-solid fa-user-gear"></i> <?= __('providers') ?></a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="<?= url('/') ?>#how-it-works"><i class="fa-solid fa-diagram-project"></i> كيف تعمل المنصة</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="<?= url('/') ?>#about"><i class="fa-solid fa-circle-info"></i> عن مِهَن</a>
        </li>
      </ul>
      
      <ul class="navbar-nav align-items-lg-center gap-1">
        <li class="nav-item me-lg-2">
          <a class="nav-link lang-switch" href="<?= e(langUrl(currentLang() === 'ar' ? 'en' : 'ar')) ?>">
            <i class="fa-solid fa-globe"></i> <?= currentLang() === 'ar' ? 'English' : 'العربية' ?>
          </a>
        </li>
        <?php if (Auth::check()): ?>
          <li class="nav-item">
            <a class="btn btn-outline-light btn-sm px-3" href="<?= url(Auth::role() . '/dashboard') ?>">
              <i class="fa-solid fa-gauge-high me-1"></i> <?= __('dashboard') ?>
            </a>
          </li>
          <li class="nav-item ms-lg-1">
            <form method="POST" action="<?= url('/logout') ?>" class="d-inline">
              <?= csrf_field() ?>
              <button type="submit" class="btn btn-link nav-link border-0 text-danger" title="<?= e(__('logout')) ?>">
                <i class="fa-solid fa-right-from-bracket"></i>
              </button>
            </form>
          </li>
        <?php else: ?>
          <li class="nav-item">
            <a class="nav-link px-3" href="<?= url('/login') ?>">
              <i class="fa-solid fa-arrow-right-to-bracket me-1"></i> <?= __('login') ?>
            </a>
          </li>
          <li class="nav-item ms-lg-2">
            <a class="btn btn-warning btn-sm px-3 fw-bold" href="<?= url('/register?role=provider') ?>">
              <i class="fa-solid fa-user-plus me-1"></i> انضم كمقدم خدمة
            </a>
          </li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>
