<footer class="footer-main mt-5 py-5">
  <div class="container">
    <div class="row g-4 mb-4 align-items-center">
      <div class="col-md-6 text-md-start text-center">
        <div class="d-flex align-items-center justify-content-md-start justify-content-center gap-3 mb-2">
          <img class="footer-logo mb-0" src="<?= asset('images/logo-mihan-mark.png') ?>" alt="<?= e(__('app_name')) ?>">
          <div>
            <h5 class="text-white fw-bold mb-0"><?= e(setting('site_name', __('app_name'))) ?></h5>
            <small class="text-muted">المنصة الذكية الرائدة لإدارة وربط الخدمات المهنية المحلية</small>
          </div>
        </div>
      </div>
      <div class="col-md-6 text-md-end text-center">
        <div class="d-flex justify-content-md-end justify-content-center gap-3">
          <a href="<?= url('/services') ?>" class="text-light small">دليل الخدمات</a>
          <span class="text-muted">&bull;</span>
          <a href="<?= url('/providers') ?>" class="text-light small">شبكة المهنيين</a>
          <span class="text-muted">&bull;</span>
          <a href="<?= url('/register?role=provider') ?>" class="text-warning small fw-bold">انضم كمهني</a>
        </div>
      </div>
    </div>
    <hr class="border-secondary opacity-25 my-3">
    <div class="text-center text-muted small">
      <p class="mb-1">&copy; <?= date('Y') ?> <?= e(setting('site_name', __('app_name'))) ?>. جميع الحقوق محفوظة.</p>
      <p class="mb-0 opacity-75">مشروع تخرج نوعي &mdash; نظم المعلومات الإدارية (MIS)</p>
    </div>
  </div>
</footer>
