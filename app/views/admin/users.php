<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
  <div>
    <h3 class="mb-1">إدارة المستخدمين</h3>
    <p class="text-muted mb-0">عرض وتعديل وتعيين أدوار وصلاحيات جميع حسابات المنصة.</p>
  </div>
  <form method="GET" class="d-flex gap-2">
    <select name="role" class="form-select form-select-sm" onchange="this.form.submit()">
      <option value="">جميع الأدوار (الكل)</option>
      <option value="admin" <?= $role==='admin'?'selected':'' ?>>مديرو النظام (Admin)</option>
      <option value="employee" <?= $role==='employee'?'selected':'' ?>>موظفو المنصة (Employee)</option>
      <option value="provider" <?= $role==='provider'?'selected':'' ?>>مقدمو الخدمة (Provider)</option>
      <option value="customer" <?= $role==='customer'?'selected':'' ?>>العملاء (Customer)</option>
    </select>
  </form>
</div>

<div class="card shadow-sm border-0">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th class="ps-3">#</th>
            <th><?= __('name') ?></th>
            <th><?= __('email') ?></th>
            <th>الهاتف</th>
            <th>الدور</th>
            <th>الحالة</th>
            <th>تاريخ التسجيل</th>
            <th class="text-end pe-3">الإجراءات</th>
          </tr>
        </thead>
        <tbody>
        <?php
        $roleBadges = [
          'admin' => ['bg-danger bg-opacity-10 text-danger border-danger border', 'مدير النظام', 'fa-shield-halved'],
          'employee' => ['bg-primary bg-opacity-10 text-primary border-primary border', 'موظف منصة', 'fa-user-tie'],
          'provider' => ['bg-success bg-opacity-10 text-success border-success border', 'مقدم خدمة', 'fa-wrench'],
          'customer' => ['bg-info bg-opacity-10 text-info border-info border', 'عميل', 'fa-user'],
        ];
        foreach ($users as $u):
          $badge = $roleBadges[$u['role']] ?? ['bg-secondary', $u['role'], 'fa-user'];
        ?>
          <tr>
            <td class="ps-3 fw-bold text-muted"><?= (int)$u['id'] ?></td>
            <td>
              <div class="fw-semibold text-dark"><?= e($u['name']) ?></div>
            </td>
            <td>
              <div><?= e($u['email']) ?></div>
              <?php if (!empty($u['is_verified_email'])): ?>
                <span class="badge bg-success bg-opacity-10 text-success" style="font-size:0.7rem;"><i class="fa-solid fa-check"></i> موثق</span>
              <?php endif; ?>
            </td>
            <td><?= e($u['phone'] ?: '—') ?></td>
            <td>
              <span class="badge <?= $badge[0] ?> px-2 py-1"><i class="fa-solid <?= $badge[2] ?> me-1"></i> <?= $badge[1] ?></span>
            </td>
            <td>
              <span class="badge bg-<?= (int)$u['is_active'] === 1 ? 'success' : 'danger' ?>"><?= (int)$u['is_active'] === 1 ? 'نشط' : 'معطل' ?></span>
            </td>
            <td class="small text-muted"><?= formatDate($u['created_at']) ?></td>
            <td class="text-end pe-3">
              <div class="d-inline-flex gap-1">
                <a href="<?= url('/admin/users/' . $u['id'] . '/edit') ?>" class="btn btn-sm btn-outline-primary" title="تعديل الحساب وتخصيص الصلاحيات">
                  <i class="fa-solid fa-user-pen"></i> تعديل وصلاحيات
                </a>
                <form method="POST" action="<?= url('/admin/users/' . $u['id'] . '/toggle') ?>" class="d-inline">
                  <?= csrf_field() ?>
                  <button type="submit" class="btn btn-sm btn-outline-warning" title="<?= (int)$u['is_active'] === 1 ? 'تعطيل الحساب' : 'تفعيل الحساب' ?>">
                    <?= (int)$u['is_active'] === 1 ? 'تعطيل' : 'تفعيل' ?>
                  </button>
                </form>
                <form method="POST" action="<?= url('/admin/users/' . $u['id'] . '/delete') ?>" class="d-inline" onsubmit="return confirm('هل أنت متأكد من حذف هذا المستخدم نهائيًا؟');">
                  <?= csrf_field() ?>
                  <button type="submit" class="btn btn-sm btn-outline-danger" title="حذف المستخدم"><i class="fa-solid fa-trash-can"></i></button>
                </form>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (empty($users)): ?>
          <tr><td colspan="8" class="text-center py-4 text-muted">لا يوجد مستخدمون مطابقون للمرشح المحدد.</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
