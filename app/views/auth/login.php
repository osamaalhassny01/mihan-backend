<div class="row justify-content-center py-4">
  <div class="col-lg-6 col-xl-5">
    <div class="card p-4 p-md-5 border-0 shadow-lg" style="border-radius: 24px;">
      
      <div class="text-center mb-4">
        <img class="auth-logo mb-3" style="width: 72px; height: auto;" src="<?= asset('images/logo-mihan-mark.png') ?>" alt="<?= e(__('app_name')) ?>">
        <h3 class="fw-bold mb-1" style="color: #0f172a;">مرحبًا بك في مِهَن</h3>
        <p class="text-muted small">سجّل دخولك للوصول إلى لوحة التحكم والخدمات الذكية</p>
      </div>

      <?php if (!empty($redirect)): ?>
        <div class="alert alert-info small rounded-3 mb-4">
          <i class="fa-solid fa-circle-info me-1"></i> يرجى تسجيل الدخول أولاً لإكمال الإجراء المطلوب.
        </div>
      <?php endif; ?>

      <!-- Quick Demo Accounts -->
      <div class="card bg-light border-0 p-3 mb-4 rounded-3">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <span class="small fw-bold text-secondary">
            <i class="fa-solid fa-key text-warning me-1"></i> حسابات تجريبية سريعة (الباسورد: <code>Passw0rd!</code>):
          </span>
        </div>
        <div class="d-flex flex-wrap gap-2">
          <button type="button" class="btn btn-outline-dark btn-sm py-1 px-2 demo-fill" data-email="admin@mihan.test" data-pass="Passw0rd!">
            <i class="fa-solid fa-user-shield me-1"></i> مدير النظام
          </button>
          <button type="button" class="btn btn-outline-secondary btn-sm py-1 px-2 demo-fill" data-email="employee@mihan.test" data-pass="Passw0rd!">
            <i class="fa-solid fa-user-tie me-1"></i> موظف المنصة
          </button>
          <button type="button" class="btn btn-outline-primary btn-sm py-1 px-2 demo-fill" data-email="customer1@mihan.test" data-pass="Passw0rd!">
            <i class="fa-solid fa-user me-1"></i> عميل
          </button>
          <button type="button" class="btn btn-outline-success btn-sm py-1 px-2 demo-fill" data-email="provider1@mihan.test" data-pass="Passw0rd!">
            <i class="fa-solid fa-handyman me-1"></i> مقدم خدمة
          </button>
        </div>
      </div>

      <form method="POST" action="<?= url('/login') ?>" data-guard id="loginForm">
        <?= csrf_field() ?>
        <?php if (!empty($redirect)): ?>
          <input type="hidden" name="redirect" value="<?= e($redirect) ?>">
        <?php endif; ?>

        <div class="mb-3">
          <label class="form-label">البريد الإلكتروني</label>
          <div class="input-group">
            <span class="input-group-text bg-white border-end-0 text-muted"><i class="fa-solid fa-envelope"></i></span>
            <input type="email" name="email" id="loginEmail" class="form-control form-control-lg border-start-0 ps-0" value="<?= old('email') ?>" autocomplete="email" required placeholder="name@example.com">
          </div>
        </div>

        <div class="mb-4">
          <div class="d-flex justify-content-between align-items-center mb-1">
            <label class="form-label mb-0">كلمة المرور</label>
          </div>
          <div class="input-group">
            <span class="input-group-text bg-white border-end-0 text-muted"><i class="fa-solid fa-lock"></i></span>
            <input type="password" name="password" id="loginPassword" class="form-control form-control-lg border-start-0 border-end-0 px-0" autocomplete="current-password" required placeholder="••••••••">
            <button class="btn btn-outline-secondary bg-white border-start-0" type="button" id="togglePasswordBtn" title="إظهار/إخفاء كلمة المرور">
              <i class="fa-solid fa-eye" id="togglePasswordIcon"></i>
            </button>
          </div>
        </div>

        <button type="submit" class="btn btn-primary btn-lg w-100 py-3 mb-3">
          <i class="fa-solid fa-right-to-bracket me-1"></i> دخول إلى المنصة
        </button>
      </form>

      <div class="text-center mt-3">
        <span class="text-muted">ليس لديك حساب بعد؟</span>
        <a href="<?= url('/register') ?>" class="fw-bold ms-1 text-primary">أنشئ حسابك الآن</a>
      </div>

    </div>
  </div>
</div>

<script>
document.querySelectorAll('.demo-fill').forEach(function(btn) {
  btn.addEventListener('click', function() {
    document.getElementById('loginEmail').value = this.dataset.email;
    document.getElementById('loginPassword').value = this.dataset.pass;
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
