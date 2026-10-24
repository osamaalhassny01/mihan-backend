<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
  <div>
    <h3 class="mb-1">موظفو المنصة</h3>
    <p class="text-muted mb-0">إدارة حسابات موظفي المنصة التشغيليين وتخصيص صلاحياتهم.</p>
  </div>
</div>

<div class="card shadow-sm border-0 mb-4">
  <div class="card-header bg-white py-3">
    <h5 class="card-title mb-0"><i class="fa-solid fa-user-plus me-2 text-primary"></i> إضافة موظف منصة جديد</h5>
  </div>
  <div class="card-body">
    <form method="POST" action="<?= url('/admin/employees') ?>" class="row g-3">
      <?= csrf_field() ?>
      <div class="col-md-3">
        <label class="form-label small fw-semibold">الاسم الكامل <span class="text-danger">*</span></label>
        <input name="name" class="form-control" placeholder="اسم الموظف" required minlength="3" maxlength="100">
      </div>
      <div class="col-md-3">
        <label class="form-label small fw-semibold">البريد الإلكتروني <span class="text-danger">*</span></label>
        <input type="email" name="email" class="form-control" placeholder="employee@mihan.test" required>
      </div>
      <div class="col-md-2">
        <label class="form-label small fw-semibold">رقم الهاتف</label>
        <input name="phone" class="form-control" placeholder="777000000">
      </div>
      <div class="col-md-2">
        <label class="form-label small fw-semibold">كلمة المرور <span class="text-danger">*</span></label>
        <input type="password" name="password" class="form-control" placeholder="لا تقل عن 8 أحرف" required minlength="8">
      </div>
      <div class="col-md-2 d-flex align-items-end">
        <button type="submit" class="btn btn-primary w-100"><i class="fa-solid fa-plus me-1"></i> إنشاء الموظف</button>
      </div>
    </form>
  </div>
</div>

<div class="card shadow-sm border-0">
  <div class="card-header bg-white py-3">
    <h5 class="card-title mb-0"><i class="fa-solid fa-users-gear me-2 text-secondary"></i> قائمة موظفي المنصة</h5>
  </div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th class="ps-3">#</th>
            <th>الاسم</th>
            <th>البريد الإلكتروني</th>
            <th>الهاتف</th>
            <th>الحالة</th>
            <th>تاريخ التعيين</th>
            <th class="text-end pe-3">الإجراءات</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($employees as $e): ?>
          <tr>
            <td class="ps-3 fw-bold text-muted"><?= (int)$e['id'] ?></td>
            <td><div class="fw-semibold text-dark"><?= e($e['name']) ?></div></td>
            <td><?= e($e['email']) ?></td>
            <td><?= e($e['phone'] ?: '—') ?></td>
            <td>
              <span class="badge bg-<?= (int)$e['is_active'] === 1 ? 'success' : 'danger' ?>">
                <?= (int)$e['is_active'] === 1 ? 'نشط ومفعّل' : 'معطل' ?>
              </span>
            </td>
            <td class="small text-muted"><?= formatDate($e['created_at']) ?></td>
            <td class="text-end pe-3">
              <div class="d-inline-flex gap-1">
                <a href="<?= url('/admin/employees/' . $e['id'] . '/edit') ?>" class="btn btn-sm btn-outline-primary" title="تعديل بيانات الموظف وصلاحياته">
                  <i class="fa-solid fa-user-pen me-1"></i> تعديل وصلاحيات
                </a>
                <form method="POST" action="<?= url('/admin/employees/' . $e['id'] . '/toggle') ?>" class="d-inline">
                  <?= csrf_field() ?>
                  <button type="submit" class="btn btn-sm btn-outline-warning">
                    <?= (int)$e['is_active'] === 1 ? 'تعطيل' : 'تفعيل' ?>
                  </button>
                </form>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (empty($employees)): ?>
          <tr><td colspan="7" class="text-center py-4 text-muted">لا يوجد موظفون مسجلون حالياً.</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>