<div class="row justify-content-center py-4">
  <div class="col-lg-10 col-xl-9">
    <div class="card border-0 shadow-lg overflow-hidden" style="border-radius: 28px; background: #ffffff;">
      <div class="row g-0">
        
        <!-- Left Visual Brand Showcase Column -->
        <div class="col-lg-5 d-none d-lg-flex flex-column justify-content-between p-5 text-white position-relative" style="background: linear-gradient(135deg, #072a27 0%, #0b3734 50%, #0f766e 100%);">
          <div style="position: absolute; top: -50px; inset-inline-end: -50px; width: 200px; height: 200px; border-radius: 50%; background: radial-gradient(circle, rgba(94, 234, 212, 0.3) 0%, transparent 70%);"></div>
          
          <div class="position-relative" style="z-index: 2;">
            <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill mb-4" style="background: rgba(255,255,255,0.12); border: 1px solid rgba(94, 234, 212, 0.3);">
              <i class="fa-solid fa-shield-halved text-warning"></i>
              <span class="small fw-bold">بوابة الدخول الآمن</span>
            </div>
            <h3 class="fw-bold mb-3" style="font-size: 1.85rem; line-height: 1.4; color: #ffffff;">
              منظومة <span style="color: #fbbf24; text-shadow: 0 0 16px rgba(251, 191, 36, 0.45);">مِهَن</span> الرقمية الذكية
            </h3>
            <p class="small" style="color: rgba(255, 255, 255, 0.88); line-height: 1.8; font-size: 0.92rem;">
              بوابتك الموحدة للوصول إلى إدارة الطلبات، متابعة الخدمات، التوجيه الذكي، والرقابة الفورية على العمليات.
            </p>
          </div>

          <div class="position-relative my-4" style="z-index: 2;">
            <div class="p-3 rounded-4 mb-3" style="background: rgba(255, 255, 255, 0.08); border: 1px solid rgba(255, 255, 255, 0.15); backdrop-filter: blur(8px);">
              <div class="d-flex align-items-center gap-3">
                <div style="width: 40px; height: 40px; border-radius: 12px; background: #5eead4; color: #0b3734; display: flex; align-items: center; justify-content: center; font-size: 1.2rem;">
                  <i class="fa-solid fa-users"></i>
                </div>
                <div>
                  <div class="fw-bold small text-white">4 بوابات تشغيلية</div>
                  <small style="color: #a7f3d0;">عميل، مقدم خدمة، موظف، ومدير</small>
                </div>
              </div>
            </div>

            <div class="p-3 rounded-4" style="background: rgba(255, 255, 255, 0.08); border: 1px solid rgba(255, 255, 255, 0.15); backdrop-filter: blur(8px);">
              <div class="d-flex align-items-center gap-3">
                <div style="width: 40px; height: 40px; border-radius: 12px; background: #fbbf24; color: #0b3734; display: flex; align-items: center; justify-content: center; font-size: 1.2rem;">
                  <i class="fa-solid fa-bolt"></i>
                </div>
                <div>
                  <div class="fw-bold small text-white">أداء فوري واستجابة حية</div>
                  <small style="color: #a7f3d0;">تشفير عالي وخصوصية كاملة للبيانات</small>
                </div>
              </div>
            </div>
          </div>

          <div class="position-relative" style="z-index: 2;">
            <small style="color: #e2e8f0; opacity: 0.8;">&copy; <?= date('Y') ?> منصة مِهَن &mdash; جميع الحقوق محفوظة</small>
          </div>
        </div>

        <!-- Right Login Form Column -->
        <div class="col-lg-7 p-4 p-md-5">
          <div class="d-flex align-items-center gap-3 mb-4">
            <img style="width: 62px; height: 62px; border-radius: 16px; background: #f0fdfa; padding: 4px; border: 1.5px solid #5eead4;" src="<?= asset('images/logo-mihan-mark.png') ?>" alt="<?= e(__('app_name')) ?>">
            <div>
              <h3 class="fw-bold mb-1" style="color: #0b3734;">تسجيل الدخول</h3>
              <p class="text-muted small mb-0">أدخل بياناتك للمتابعة إلى حسابك في المنصة</p>
            </div>
          </div>

          <?php if (!empty($redirect)): ?>
            <div class="alert alert-info small rounded-3 mb-4 border-0" style="background: #e0f2fe; color: #0369a1;">
              <i class="fa-solid fa-circle-info me-1"></i> يرجى تسجيل الدخول أولاً لإكمال الإجراء المطلوب.
            </div>
          <?php endif; ?>

          <!-- Role Demo Selector Pills -->
          <div class="card border-0 p-3 mb-4" style="background: #f8fafc; border: 1px solid #e2e8f0 !important; border-radius: 18px;">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <span class="small fw-bold text-secondary">
                <i class="fa-solid fa-key text-warning me-1"></i> تجربة سريعة بنقرة واحدة (كلمة المرور: <code>Passw0rd!</code>):
              </span>
            </div>
            <div class="row g-2">
              <div class="col-6 col-sm-3">
                <button type="button" class="btn btn-outline-dark btn-sm w-100 py-2 demo-fill" data-email="admin@mihan.test" data-pass="Passw0rd!" style="border-radius: 10px;">
                  <i class="fa-solid fa-user-shield me-1"></i> مدير النظام
                </button>
              </div>
              <div class="col-6 col-sm-3">
                <button type="button" class="btn btn-outline-secondary btn-sm w-100 py-2 demo-fill" data-email="employee@mihan.test" data-pass="Passw0rd!" style="border-radius: 10px;">
                  <i class="fa-solid fa-user-tie me-1"></i> موظف
                </button>
              </div>
              <div class="col-6 col-sm-3">
                <button type="button" class="btn btn-outline-primary btn-sm w-100 py-2 demo-fill" data-email="customer1@mihan.test" data-pass="Passw0rd!" style="border-radius: 10px;">
                  <i class="fa-solid fa-user me-1"></i> عميل
                </button>
              </div>
              <div class="col-6 col-sm-3">
                <button type="button" class="btn btn-outline-success btn-sm w-100 py-2 demo-fill" data-email="provider1@mihan.test" data-pass="Passw0rd!" style="border-radius: 10px;">
                  <i class="fa-solid fa-handyman me-1"></i> مقدم خدمة
                </button>
              </div>
            </div>
          </div>

          <form method="POST" action="<?= url('/login') ?>" data-guard id="loginForm">
            <?= csrf_field() ?>
            <?php if (!empty($redirect)): ?>
              <input type="hidden" name="redirect" value="<?= e($redirect) ?>">
            <?php endif; ?>

            <div class="mb-3">
              <label class="form-label fw-bold small" style="color: #0b3734;">البريد الإلكتروني</label>
              <div class="input-group">
                <span class="input-group-text border-end-0" style="background: #f8fafc; border-color: #d4e7e4; color: #0f766e;"><i class="fa-solid fa-envelope"></i></span>
                <input type="email" name="email" id="loginEmail" class="form-control form-control-lg border-start-0 ps-0" value="<?= old('email') ?>" autocomplete="email" required placeholder="name@example.com" style="border-color: #d4e7e4; font-size: 1rem;">
              </div>
            </div>

            <div class="mb-4">
              <div class="d-flex justify-content-between align-items-center mb-1">
                <label class="form-label fw-bold small mb-0" style="color: #0b3734;">كلمة المرور</label>
              </div>
              <div class="input-group">
                <span class="input-group-text border-end-0" style="background: #f8fafc; border-color: #d4e7e4; color: #0f766e;"><i class="fa-solid fa-lock"></i></span>
                <input type="password" name="password" id="loginPassword" class="form-control form-control-lg border-start-0 border-end-0 px-0" autocomplete="current-password" required placeholder="••••••••" style="border-color: #d4e7e4; font-size: 1rem;">
                <button class="btn border-start-0" type="button" id="togglePasswordBtn" title="إظهار/إخفاء كلمة المرور" style="background: #f8fafc; border-color: #d4e7e4; color: #64748b;">
                  <i class="fa-solid fa-eye" id="togglePasswordIcon"></i>
                </button>
              </div>
            </div>

            <button type="submit" class="btn btn-primary btn-lg w-100 py-3 mb-3 fw-bold" style="border-radius: 14px; background: linear-gradient(135deg, #0f766e 0%, #115e59 100%); border: none; box-shadow: 0 8px 25px rgba(15,118,110,0.3);">
              <i class="fa-solid fa-arrow-right-to-bracket me-1"></i> تسجيل الدخول إلى المنصة
            </button>
          </form>

          <div class="text-center mt-3 pt-3 border-top" style="border-color: #f1f5f9 !important;">
            <span class="text-muted small">ليس لديك حساب بعد؟</span>
            <a href="<?= url('/register') ?>" class="fw-bold ms-1 text-primary">أنشئ حسابك الجديد الآن</a>
          </div>

        </div>
      </div>
    </div>
  </div>
</div>

<script>
document.querySelectorAll('.demo-fill').forEach(function(btn) {
  btn.addEventListener('click', function() {
    document.getElementById('loginEmail').value = this.dataset.email;
    document.getElementById('loginPassword').value = this.dataset.pass;
    this.classList.add('active');
    setTimeout(() => this.classList.remove('active'), 300);
  });
});
document.getElementById('togglePasswordBtn').addEventListener('click', function() {
  const pwd = document.getElementById('loginPassword');
  const icon = document.getElementById('togglePasswordIcon');
  if (pwd.type === 'password') {
    pwd.type = 'text';
    icon.classList.remove('fa-eye');
    icon.classList.add('fa-eye-slash');
  } else {
    pwd.type = 'password';
    icon.classList.remove('fa-eye-slash');
    icon.classList.add('fa-eye');
  }
});
</script>
