<footer class="footer-main mt-5 py-5">
  <div class="container">
    <div class="row g-4 mb-4 align-items-center">
      <div class="col-md-6 text-md-start text-center">
        <div class="d-flex align-items-center justify-content-md-start justify-content-center gap-3 mb-2">
          <img class="footer-logo mb-0" src="<?= asset('images/logo-mihan-mark.png') ?>" alt="<?= e(__('app_name')) ?>">
          <div>
            <h5 class="text-white fw-bold mb-0"><?= e(setting('site_name', __('app_name'))) ?></h5>
            <small style="color: #a7f3d0 !important; font-weight: 600;">المنصة الذكية الرائدة لإدارة وربط الخدمات المهنية المحلية</small>
          </div>
        </div>
      </div>
      <div class="col-md-6 text-md-end text-center">
        <div class="d-flex justify-content-md-end justify-content-center gap-3">
          <a href="<?= url('/services') ?>" class="small fw-bold" style="color: #f0fdfa !important;">دليل الخدمات</a>
          <span style="color: #2dd4bf;">&bull;</span>
          <a href="<?= url('/providers') ?>" class="small fw-bold" style="color: #f0fdfa !important;">شبكة المهنيين</a>
          <span style="color: #2dd4bf;">&bull;</span>
          <a href="<?= url('/register?role=provider') ?>" class="btn btn-warning btn-sm py-1 px-3 fw-bold">انضم كمهني</a>
        </div>
      </div>
    </div>
    <hr style="border-color: rgba(94, 234, 212, 0.25) !important;" class="my-3">
    <div class="text-center small" style="color: #cbd5e1;">
      <p class="mb-1 text-white">&copy; <?= date('Y') ?> <?= e(setting('site_name', __('app_name'))) ?>. جميع الحقوق محفوظة.</p>
      <p class="mb-0" style="color: #a7f3d0;">مشروع تخرج نوعي &mdash; نظم المعلومات الإدارية (MIS)</p>
    </div>
  </div>
</footer>
