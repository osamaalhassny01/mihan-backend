<div class="row justify-content-center"><div class="col-lg-7 col-xl-6"><div class="card shadow-sm border-0 p-4 p-md-5 mt-3">
<div class="text-center mb-4"><img class="auth-logo mb-2" src="<?= asset('images/logo-mihan-mark.png') ?>" alt="<?= e(__('app_name')) ?>"><h2 class="fw-bold mb-1">تسجيل الدخول إلى مِهَن</h2></div>
<?php if (!empty($redirect)): ?><div class="alert alert-info small"><i class="fa-solid fa-circle-info"></i> سجّل الدخول لإكمال العملية التي بدأت بها.</div><?php endif; ?>

<div class="card bg-light border-0 p-3 mb-4 rounded-3">
  <div class="d-flex justify-content-between align-items-center mb-2">
    <span class="small fw-bold text-secondary"><i class="fa-solid fa-key me-1"></i> حسابات تجريبية سريعة (كلمة المرور: <code>Passw0rd!</code>):</span>
  </div>
  <div class="d-flex flex-wrap gap-2">
    <button type="button" class="btn btn-outline-dark btn-sm py-1 px-2 demo-fill" data-email="admin@mihan.test" data-pass="Passw0rd!"><i class="fa-solid fa-user-shield me-1"></i> مدير النظام</button>
    <button type="button" class="btn btn-outline-secondary btn-sm py-1 px-2 demo-fill" data-email="employee@mihan.test" data-pass="Passw0rd!"><i class="fa-solid fa-user-tie me-1"></i> موظف</button>
    <button type="button" class="btn btn-outline-primary btn-sm py-1 px-2 demo-fill" data-email="customer1@mihan.test" data-pass="Passw0rd!">عميل</button>
    <button type="button" class="btn btn-outline-success btn-sm py-1 px-2 demo-fill" data-email="provider1@mihan.test" data-pass="Passw0rd!">مقدم خدمة</button>
  </div>
</div>

<form method="POST" action="<?= url('/login') ?>" data-guard id="loginForm"><?= csrf_field() ?><?php if (!empty($redirect)): ?><input type="hidden" name="redirect" value="<?= e($redirect) ?>"><?php endif; ?>
<div class="mb-3"><label class="form-label">البريد الإلكتروني</label><input type="email" name="email" id="loginEmail" class="form-control form-control-lg" value="<?= old('email') ?>" autocomplete="email" required placeholder="example@mihan.test"></div>
<div class="mb-3">
  <label class="form-label">كلمة المرور</label>
  <div class="input-group">
    <input type="password" name="password" id="loginPassword" class="form-control form-control-lg" autocomplete="current-password" required placeholder="••••••••">
    <button class="btn btn-outline-secondary" type="button" id="togglePasswordBtn" title="إظهار/إخفاء كلمة المرور"><i class="fa-solid fa-eye" id="togglePasswordIcon"></i></button>
  </div>
</div>
<button type="submit" class="btn btn-primary btn-lg w-100"><i class="fa-solid fa-right-to-bracket"></i> دخول</button></form>
<div class="text-center mt-4">ليس لديك حساب؟ <a href="<?= url('/register') ?>">إنشاء حساب</a></div>
</div></div></div>

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
