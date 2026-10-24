<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h3 class="mb-1">تعديل المستخدم والصلاحيات</h3>
    <p class="text-muted mb-0">تعديل بيانات الحساب <strong>#<?= (int)$user['id'] ?></strong> وتعيين الصلاحيات الفردية (User Permissions Override).</p>
  </div>
  <a href="<?= url('/admin/users') ?>" class="btn btn-outline-secondary btn-sm">
    <i class="fa-solid fa-arrow-right me-1"></i> العودة لقائمة المستخدمين
  </a>
</div>

<form method="POST" action="<?= url('/admin/users/' . $user['id'] . '/update') ?>" class="row g-4">
  <?= csrf_field() ?>

  <div class="col-lg-5">
    <div class="card shadow-sm border-0 mb-4">
      <div class="card-header bg-white py-3">
        <h5 class="card-title mb-0"><i class="fa-solid fa-id-card me-2 text-primary"></i> البيانات الأساسية للحساب</h5>
      </div>
      <div class="card-body">
        <div class="mb-3">
          <label class="form-label fw-semibold">الاسم الكامل <span class="text-danger">*</span></label>
          <input type="text" name="name" class="form-control" value="<?= e($user['name']) ?>" required minlength="3" maxlength="150">
        </div>

        <div class="mb-3">
          <label class="form-label fw-semibold">البريد الإلكتروني <span class="text-danger">*</span></label>
          <input type="email" name="email" class="form-control" value="<?= e($user['email']) ?>" required>
        </div>

        <div class="mb-3">
          <label class="form-label fw-semibold">رقم الهاتف</label>
          <input type="text" name="phone" class="form-control" value="<?= e($user['phone'] ?? '') ?>" placeholder="مثال: 777000000">
        </div>

        <div class="mb-3">
          <label class="form-label fw-semibold">دور المستخدم (Role) <span class="text-danger">*</span></label>
          <select name="role" class="form-select" id="roleSelect" required>
            <option value="customer" <?= $user['role'] === 'customer' ? 'selected' : '' ?>>عميل (Customer)</option>
            <option value="provider" <?= $user['role'] === 'provider' ? 'selected' : '' ?>>مقدم خدمة (Provider)</option>
            <option value="employee" <?= $user['role'] === 'employee' ? 'selected' : '' ?>>موظف منصة (Employee)</option>
            <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>مدير النظام (Admin)</option>
          </select>
          <small class="text-muted d-block mt-1">تغيير الدور يمنح الصلاحيات الافتراضية للدور الجديد تلقائيًا.</small>
        </div>

        <div class="mb-3">
          <label class="form-label fw-semibold">كلمة مرور جديدة (اختياري)</label>
          <input type="password" name="password" class="form-control" placeholder="اتركها فارغة إذا لم ترغب في تغييرها" minlength="6">
          <small class="text-muted">اترك هذا الحقل فارغاً للإبقاء على كلمة المرور الحالية دون تعديل.</small>
        </div>

        <hr class="my-3">

        <div class="mb-3 form-check form-switch">
          <input class="form-check-input" type="checkbox" name="is_active" value="1" id="isActiveCheck" <?= (int)$user['is_active'] === 1 ? 'checked' : '' ?>>
          <label class="form-check-label fw-semibold" for="isActiveCheck">حالة الحساب (نشط ومفعّل)</label>
          <small class="text-muted d-block">تعطيل الحساب يمنع المستخدم من تسجيل الدخول فوراً.</small>
        </div>

        <div class="mb-2 form-check form-switch">
          <input class="form-check-input" type="checkbox" name="is_verified_email" value="1" id="isVerifiedCheck" <?= (int)$user['is_verified_email'] === 1 ? 'checked' : '' ?>>
          <label class="form-check-label fw-semibold" for="isVerifiedCheck">حالة توثيق البريد الإلكتروني</label>
        </div>
      </div>
    </div>
  </div>

  <div class="col-lg-7">
    <div class="card shadow-sm border-0">
      <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0"><i class="fa-solid fa-key me-2 text-warning"></i> الصلاحيات الفردية والمخصصة (Effective Permissions)</h5>
        <span class="badge bg-light text-dark border">نظام الاستثناءات الفردية</span>
      </div>
      <div class="card-body">
        <p class="small text-muted mb-3">
          يمكنك تحديد الصلاحيات الفعلية لهذا المستخدم مباشرة. الصلاحيات المحددة تمثل الصلاحيات النافذة (Effective Permissions)؛ وعند الحفظ يتم احتساب الاستثناءات (Overrides) نسبةً للدور تلقائيًا.
        </p>

        <div class="row g-2">
          <?php foreach ($user['permissions_catalogue'] as $p): ?>
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
                    <span class="badge bg-secondary bg-opacity-10 text-secondary" style="font-size:0.75rem;">افتراضي بالدور</span>
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
            <i class="fa-solid fa-floppy-disk me-1"></i> حفظ كافة التعديلات والصلاحيات
          </button>
          <a href="<?= url('/admin/users') ?>" class="btn btn-outline-secondary">إلغاء</a>
        </div>
      </div>
    </div>
  </div>
</form>
