<footer class="footer-main mt-5 py-5" style="background: linear-gradient(180deg, #072725 0%, #031514 100%) !important; border-top: 3px solid #0f766e; color: #ffffff !important;">
  <div class="container">
    <div class="row g-4 mb-4 align-items-center">
      <div class="col-md-6 text-md-start text-center">
        <div class="d-flex align-items-center justify-content-md-start justify-content-center gap-3 mb-2">
          <img class="footer-logo mb-0" style="width: 58px; height: 58px; background: #ffffff; border-radius: 16px; padding: 5px; box-shadow: 0 4px 15px rgba(0,0,0,0.3);" src="<?= asset('images/logo-mihan-mark.png') ?>" alt="<?= e(__('app_name')) ?>">
          <div>
            <h4 class="fw-bold mb-1" style="color: #ffffff !important; letter-spacing: -0.02em;"><?= e(setting('site_name', __('app_name'))) ?></h4>
            <p class="mb-0 small" style="color: #6ee7b7 !important; font-weight: 700; font-size: 0.95rem;">المنصة الذكية الرائدة لإدارة وربط الخدمات المهنية المحلية</p>
          </div>
        </div>
      </div>
      <div class="col-md-6 text-md-end text-center">
        <div class="d-flex justify-content-md-end justify-content-center align-items-center gap-3 flex-wrap">
          <a href="<?= url('/services') ?>" class="px-3 py-2 rounded-3 text-decoration-none fw-bold" style="color: #ffffff !important; background: rgba(255,255,255,0.08); transition: all 0.2s;">
            <i class="fa-solid fa-layer-group text-warning me-1"></i> دليل الخدمات
          </a>
          <a href="<?= url('/providers') ?>" class="px-3 py-2 rounded-3 text-decoration-none fw-bold" style="color: #ffffff !important; background: rgba(255,255,255,0.08); transition: all 0.2s;">
            <i class="fa-solid fa-users-gear text-info me-1"></i> شبكة المهنيين
          </a>
          <a href="<?= url('/register?role=provider') ?>" class="btn btn-warning btn-sm py-2 px-3 fw-bold" style="color: #0b3734 !important; background: linear-gradient(135deg, #f59e0b 0%, #fbbf24 100%); border: none; box-shadow: 0 4px 15px rgba(245,158,11,0.4);">
            <i class="fa-solid fa-user-plus me-1"></i> انضم كمقدم خدمة
          </a>
        </div>
      </div>
    </div>
    
    <hr style="border-color: rgba(94, 234, 212, 0.25) !important;" class="my-4">
    
    <div class="row align-items-center text-center text-md-start">
      <div class="col-md-6 mb-2 mb-md-0">
        <p class="mb-0" style="color: #e2e8f0 !important; font-size: 0.92rem; font-weight: 600;">
          &copy; <?= date('Y') ?> <strong style="color: #5eead4 !important;"><?= e(setting('site_name', __('app_name'))) ?></strong>. جميع الحقوق محفوظة.
        </p>
      </div>
      <div class="col-md-6 text-md-end text-center">
        <span class="badge px-3 py-2" style="background: rgba(20, 184, 166, 0.15); color: #5eead4 !important; border: 1px solid rgba(94, 234, 212, 0.3); font-size: 0.85rem; font-weight: 700;">
          <i class="fa-solid fa-graduation-cap me-1"></i> مشروع تخرج نوعي &mdash; نظم المعلومات الإدارية (MIS)
        </span>
      </div>
    </div>
  </div>
</footer>
