<div class="row justify-content-center py-4">
  <div class="col-lg-7 col-xl-6">
    <div class="card border-0 shadow-lg p-4 p-md-5" style="border-radius: 24px;">
      
      <div class="text-center mb-4">
        <img class="auth-logo mb-3" style="width: 72px; height: auto;" src="<?= asset('images/logo-mihan-mark.png') ?>" alt="<?= e(__('app_name')) ?>">
        <h3 class="fw-bold mb-1" style="color: #0f172a;">إنشاء حساب جديد في مِهَن</h3>
        <p class="text-muted small">انضم إلى مجتمع مِهَن الذكي واستمتع بخدمات موثوقة وفورية</p>
      </div>

      <form method="POST" action="<?= url('/register') ?>" data-guard>
        <?= csrf_field() ?>

        <div class="mb-3">
          <label class="form-label">الاسم الكامل</label>
          <div class="input-group">
            <span class="input-group-text bg-white border-end-0 text-muted"><i class="fa-solid fa-user"></i></span>
            <input type="text" name="name" class="form-control form-control-lg border-start-0 ps-0" value="<?= old('name') ?>" required placeholder="الاسم الثلاثي أو اسم المنشأة">
          </div>
          <?php if (!empty($errors['name'])): ?><small class="text-danger mt-1 d-block"><?= e($errors['name'][0]) ?></small><?php endif; ?>
        </div>

        <div class="row">
          <div class="col-md-6 mb-3">
            <label class="form-label">البريد الإلكتروني</label>
            <div class="input-group">
              <span class="input-group-text bg-white border-end-0 text-muted"><i class="fa-solid fa-envelope"></i></span>
              <input type="email" name="email" class="form-control form-control-lg border-start-0 ps-0" value="<?= old('email') ?>" required placeholder="name@example.com">
            </div>
            <?php if (!empty($errors['email'])): ?><small class="text-danger mt-1 d-block"><?= e($errors['email'][0]) ?></small><?php endif; ?>
          </div>

          <div class="col-md-6 mb-3">
            <label class="form-label">رقم الهاتف</label>
            <div class="input-group">
              <span class="input-group-text bg-white border-end-0 text-muted"><i class="fa-solid fa-phone"></i></span>
              <input type="text" name="phone" class="form-control form-control-lg border-start-0 ps-0" placeholder="777000000" value="<?= old('phone') ?>" required>
            </div>
            <?php if (!empty($errors['phone'])): ?><small class="text-danger mt-1 d-block"><?= e($errors['phone'][0]) ?></small><?php endif; ?>
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label">نوع الحساب</label>
          <div class="input-group">
            <span class="input-group-text bg-white border-end-0 text-muted"><i class="fa-solid fa-id-badge"></i></span>
            <select name="role" class="form-select form-select-lg border-start-0 ps-0" id="roleSelect" required>
              <?php if (settingBool('allow_customer_registration', true)): ?>
                <option value="customer" <?= (($_GET['role'] ?? '') !== 'provider' || !settingBool('allow_provider_registration', true)) ? 'selected' : '' ?>>👤 عميل مستفيد (طلب وحجز الخدمات)</option>
              <?php endif; ?>
              <?php if (settingBool('allow_provider_registration', true)): ?>
                <option value="provider" <?= (($_GET['role'] ?? '') === 'provider' || !settingBool('allow_customer_registration', true)) ? 'selected' : '' ?>>🛠️ مقدم خدمة فنية (استقبال وتنفيذ الطلبات)</option>
              <?php endif; ?>
            </select>
          </div>
        </div>

        <div class="mb-3" id="professionField" style="display:none">
          <label class="form-label">المهنة أو التخصص الفني</label>
          <div class="input-group">
            <span class="input-group-text bg-white border-end-0 text-muted"><i class="fa-solid fa-wrench"></i></span>
            <input type="text" name="profession" class="form-control form-control-lg border-start-0 ps-0" placeholder="مثال: فني تكييف وتبريد، كهربائي منازل، سباكة">
          </div>
        </div>

        <div class="row">
          <div class="col-md-6 mb-4">
            <label class="form-label">كلمة المرور</label>
            <div class="input-group">
              <span class="input-group-text bg-white border-end-0 text-muted"><i class="fa-solid fa-lock"></i></span>
              <input type="password" name="password" class="form-control form-control-lg border-start-0 ps-0" required minlength="6" placeholder="6 خانات فأكثر">
            </div>
            <?php if (!empty($errors['password'])): ?><small class="text-danger mt-1 d-block"><?= e($errors['password'][0]) ?></small><?php endif; ?>
          </div>

          <div class="col-md-6 mb-4">
            <label class="form-label">تأكيد كلمة المرور</label>
            <div class="input-group">
              <span class="input-group-text bg-white border-end-0 text-muted"><i class="fa-solid fa-lock-open"></i></span>
              <input type="password" name="password_confirmation" class="form-control form-control-lg border-start-0 ps-0" required minlength="6" placeholder="أعد إدخال كلمة المرور">
            </div>
          </div>
        </div>

        <button type="submit" class="btn btn-primary btn-lg w-100 py-3 mb-3">
          <i class="fa-solid fa-user-plus me-1"></i> إنشاء الحساب والانضمام للمنصة
        </button>
      </form>

      <div class="text-center mt-3">
        <span class="text-muted">لديك حساب بالفعل؟</span>
        <a href="<?= url('/login') ?>" class="fw-bold ms-1 text-primary">سجّل دخولك الآن</a>
      </div>

    </div>
  </div>
</div>

<script>
document.getElementById('roleSelect').addEventListener('change', function () {
  document.getElementById('professionField').style.display = this.value === 'provider' ? 'block' : 'none';
});
document.getElementById('roleSelect').dispatchEvent(new Event('change'));
</script>
