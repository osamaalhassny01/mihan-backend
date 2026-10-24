<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h3 class="mb-1">تعديل بيانات وصلاحيات الموظف</h3>
    <p class="text-muted mb-0">تعديل بيانات حساب الموظف <strong><?= e($employee['name']) ?></strong> (#<?= (int)$employee['id'] ?>) وتخصيص صلاحياته التشغيلية.</p>
  </div>
  <div class="d-flex gap-2">
    <a href="<?= url('/admin/employees') ?>" class="btn btn-outline-secondary btn-sm">
      <i class="fa-solid fa-arrow-right me-1"></i> العودة لقائمة الموظفين
    </a>
  </div>
</div>

<form method="POST" action="<?= url('/admin/employees/' . $employee['id'] . '/update') ?>" class="row g-4">
  <?= csrf_field() ?>

  <div class="col-lg-5">
    <div class="card shadow-sm border-0 mb-4">
      <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0"><i class="fa-solid fa-user-tie me-2 text-primary"></i> بيانات الموظف</h5>
        <span class="badge bg-primary bg-opacity-10 text-primary border-primary border">موظف منصة</span>
      </div>
      <div class="card-body">
        <div class="mb-3">
          <label class="form-label fw-semibold">الاسم الكامل <span class="text-danger">*</span></label>
          <input type="text" name="name" class="form-control" value="<?= e($employee['name']) ?>" required minlength="3" maxlength="150">
        </div>

        <div class="mb-3">
          <label class="form-label fw-semibold">البريد الإلكتروني <span class="text-danger">*</span></label>
          <input type="email" name="email" class="form-control" value="<?= e($employee['email']) ?>" required>
        </div>

        <div class="mb-3">
          <label class="form-label fw-semibold">رقم الهاتف</label>
          <input type="text" name="phone" class="form-control" value="<?= e($employee['phone'] ?? '') ?>" placeholder="مثال: 777000000">
        </div>

        <div class="mb-3">
          <label class="form-label fw-semibold">كلمة مرور جديدة (اختياري)</label>
          <input type="password" name="password" class="form-control" placeholder="اتركها فارغة إذا لم ترغب في تغييرها" minlength="8">
          <small class="text-muted">اترك هذا الحقل فارغاً للإبقاء على كلمة المرور الحالية دون تعديل.</small>
        </div>

        <hr class="my-3">

        <div class="mb-3 form-check form-switch">
          <input class="form-check-input" type="checkbox" name="is_active" value="1" id="isActiveCheck" <?= (int)$employee['is_active'] === 1 ? 'checked' : '' ?>>
          <label class="form-check-label fw-semibold" for="isActiveCheck">حالة الحساب (نشط ومفعّل)</label>
          <small class="text-muted d-block">تعطيل الحساب يمنع الموظف من تسجيل الدخول فوراً.</small>
        </div>
      </div>
    </div>
  </div>

  <div class="col-lg-7">
    <div class="card shadow-sm border-0">
      <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0"><i class="fa-solid fa-key me-2 text-warning"></i> صلاحيات الموظف (Employee Permissions)</h5>
        <span class="badge bg-light text-dark border">تخصيص مباشر</span>
      </div>
      <div class="card-body">
        <p class="small text-muted mb-3">
          حدد الصلاحيات الممنوحة لهذا الموظف. الصلاحيات الافتراضية محددة مسبقًا، ويمكنك منح أو حجب أي صلاحية فردية إضافية.
        </p>

        <div class="row g-2">
          <?php foreach ($employee['permissions_catalogue'] as $p): ?>
            <div class="col-12">
              <div class="border rounded p-2 d-flex justify-content-between align-items-center <?= $p['is_allowed'] ? 'bg-light' : '' ?>">
                <div class="form-check mb-0">
                  <input class="form-check-input perm-checkbox" type="checkbox" name="permissions[]" value="<?= e($p['key']) ?>" id="perm_<?= e($p['key']) ?>" <?= $p['is_allowed'] ? 'checked' : '' ?>>
                  <label class="form-check-label fw-semibold text-dark" for="perm_<?= e($p['key']) ?>">
                    <?= e($p['description'] ?: $p['key']) ?>
                  </label>
                  <div class="small text-muted font-monospace" style="font-size:0.75rem;"><?= e($p['key']) ?></div>
                </div>
                <div>
                  <?php if ($p['is_role_default']): ?>
                    <span class="badge bg-secondary bg-opacity-10 text-secondary" style="font-size:0.75rem;">افتراضي للموظف</span>
                  <?php endif; ?>
                  <?php if ($p['has_override']): ?>
                    <span class="badge <?= $p['override_value'] === 1 ? 'bg-success' : 'bg-danger' ?>" style="font-size:0.75rem;">
                      <?= $p['override_value'] === 1 ? 'ممنوح باستثناء' : 'محجوب باستثناء' ?>
                    </span>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>

        <div class="mt-4 pt-3 border-top d-flex gap-2">
          <button type="submit" class="btn btn-primary px-4">
            <i class="fa-solid fa-floppy-disk me-1"></i> حفظ بيانات وصلاحيات الموظف
          </button>
          <a href="<?= url('/admin/employees') ?>" class="btn btn-outline-secondary">إلغاء</a>
        </div>
      </div>
    </div>
  </div>
</form>
