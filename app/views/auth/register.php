<div class="row justify-content-center">
  <div class="col-md-6">
    <div class="card shadow-sm border-0 p-4 mt-4">
      <div class="text-center mb-2"><img class="auth-logo auth-logo-sm" src="<?= asset('images/logo-mihan-original.png') ?>" alt="<?= e(__('app_name')) ?>"></div>
      <h3 class="mb-3 text-center"><i class="fa-solid fa-user-plus"></i> <?= __('register') ?></h3>
      <form method="POST" action="<?= url('/register') ?>" data-guard>
        <?= csrf_field() ?>
        <div class="mb-3">
          <label class="form-label"><?= __('name') ?></label>
          <input type="text" name="name" class="form-control" value="<?= old('name') ?>" required>
          <?php if (!empty($errors['name'])): ?><small class="text-danger"><?= e($errors['name'][0]) ?></small><?php endif; ?>
        </div>
        <div class="mb-3">
          <label class="form-label"><?= __('email') ?></label>
          <input type="email" name="email" class="form-control" value="<?= old('email') ?>" required>
          <?php if (!empty($errors['email'])): ?><small class="text-danger"><?= e($errors['email'][0]) ?></small><?php endif; ?>
        </div>
        <div class="mb-3">
          <label class="form-label"><?= __('phone') ?></label>
          <input type="text" name="phone" class="form-control" placeholder="7XXXXXXXX" value="<?= old('phone') ?>" required>
          <?php if (!empty($errors['phone'])): ?><small class="text-danger"><?= e($errors['phone'][0]) ?></small><?php endif; ?>
        </div>
        <div class="mb-3">
          <label class="form-label">نوع الحساب</label>
          <select name="role" class="form-select" id="roleSelect" required>
            <?php if (settingBool('allow_customer_registration', true)): ?>
              <option value="customer" <?= (($_GET['role'] ?? '') !== 'provider' || !settingBool('allow_provider_registration', true)) ? 'selected' : '' ?>>عميل</option>
            <?php endif; ?>
            <?php if (settingBool('allow_provider_registration', true)): ?>
              <option value="provider" <?= (($_GET['role'] ?? '') === 'provider' || !settingBool('allow_customer_registration', true)) ? 'selected' : '' ?>>مقدم خدمة</option>
            <?php endif; ?>
          </select>
        </div>
        <div class="mb-3" id="professionField" style="display:none">
          <label class="form-label">المهنة</label>
          <input type="text" name="profession" class="form-control" placeholder="مثال: فني تكييف">
        </div>
        <div class="row">
          <div class="col-md-6 mb-3">
            <label class="form-label"><?= __('password') ?></label>
            <input type="password" name="password" class="form-control" required minlength="8">
            <?php if (!empty($errors['password'])): ?><small class="text-danger"><?= e($errors['password'][0]) ?></small><?php endif; ?>
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label"><?= __('confirm_password') ?></label>
            <input type="password" name="password_confirmation" class="form-control" required minlength="8">
          </div>
        </div>
        <button type="submit" class="btn btn-primary w-100"><?= __('register') ?></button>
      </form>
      <p class="text-center mt-3 mb-0">لديك حساب بالفعل؟ <a href="<?= url('/login') ?>"><?= __('login') ?></a></p>
    </div>
  </div>
</div>
<script>
document.getElementById('roleSelect').addEventListener('change', function () {
  document.getElementById('professionField').style.display = this.value === 'provider' ? 'block' : 'none';
});
document.getElementById('roleSelect').dispatchEvent(new Event('change'));
</script>
