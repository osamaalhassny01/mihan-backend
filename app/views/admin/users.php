<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 pb-3 border-bottom" style="border-color: #d4e7e4 !important;">
  <div class="d-flex align-items-center gap-3">
    <div style="width: 50px; height: 50px; border-radius: 16px; background: #ecfdf5; color: #0f766e; display: flex; align-items: center; justify-content: center; font-size: 1.4rem; border: 1.5px solid #a7f3d0;">
      <i class="fa-solid fa-users-gear"></i>
    </div>
    <div>
      <h3 class="fw-bold mb-1" style="color: #0b3734;">إدارة المستخدمين والصلاحيات</h3>
      <p class="mb-0 small" style="color: #527976;">التحكم الشامل في حسابات المنصة، تفعيل وتعطيل الحسابات، وإسناد أدوار RBAC</p>
    </div>
  </div>

  <form method="GET" class="d-flex align-items-center gap-2">
    <div class="input-group input-group-sm" style="min-width: 260px;">
      <span class="input-group-text bg-white border-end-0" style="border-color: #d4e7e4; color: #0f766e;"><i class="fa-solid fa-filter"></i></span>
      <select name="role" class="form-select border-start-0 ps-0" style="border-color: #d4e7e4; border-radius: 0 10px 10px 0; font-weight: 600;" onchange="this.form.submit()">
        <option value="">جميع الفئات (<?= count($users) ?> مستخدم)</option>
        <option value="admin" <?= $role==='admin'?'selected':'' ?>>مديرو النظام (Admin)</option>
        <option value="employee" <?= $role==='employee'?'selected':'' ?>>موظفو المنصة (Employee)</option>
        <option value="provider" <?= $role==='provider'?'selected':'' ?>>مقدمو الخدمة (Provider)</option>
        <option value="customer" <?= $role==='customer'?'selected':'' ?>>العملاء المستفيدون (Customer)</option>
      </select>
    </div>
  </form>
</div>

<div class="card border-0 shadow-sm overflow-hidden" style="border-radius: 20px; border: 1px solid #d4e7e4 !important; background: #ffffff;">
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead style="background: #f8fafc; border-bottom: 2px solid #e2e8f0;">
        <tr>
          <th class="ps-4 py-3" style="color: #0b3734; font-weight: 800; font-size: 0.85rem;"># ID</th>
          <th class="py-3" style="color: #0b3734; font-weight: 800; font-size: 0.85rem;">المستخدم</th>
          <th class="py-3" style="color: #0b3734; font-weight: 800; font-size: 0.85rem;">بيانات التواصل</th>
          <th class="py-3" style="color: #0b3734; font-weight: 800; font-size: 0.85rem;">الدور الوظيفي</th>
          <th class="py-3" style="color: #0b3734; font-weight: 800; font-size: 0.85rem;">الحالة</th>
          <th class="py-3" style="color: #0b3734; font-weight: 800; font-size: 0.85rem;">تاريخ التسجيل</th>
          <th class="text-end pe-4 py-3" style="color: #0b3734; font-weight: 800; font-size: 0.85rem;">الإجراءات والتحكم</th>
        </tr>
      </thead>
      <tbody>
      <?php
      $roleBadges = [
        'admin' => ['bg-dark text-white', 'مدير النظام', 'fa-shield-halved'],
        'employee' => ['bg-primary text-white', 'موظف منصة', 'fa-user-tie'],
        'provider' => ['bg-success text-white', 'مقدم خدمة', 'fa-wrench'],
        'customer' => ['bg-info text-white', 'عميل مستفيد', 'fa-user'],
      ];
      foreach ($users as $u):
        $badge = $roleBadges[$u['role']] ?? ['bg-secondary text-white', $u['role'], 'fa-user'];
        $isActive = (int)$u['is_active'] === 1;
      ?>
        <tr style="transition: background 0.2s;">
          <td class="ps-4 font-monospace fw-bold" style="color: #64748b;">#<?= (int)$u['id'] ?></td>
          <td>
            <div class="d-flex align-items-center gap-2">
              <div style="width: 36px; height: 36px; border-radius: 10px; background: #ecfdf5; color: #0f766e; display: flex; align-items: center; justify-content: center; font-size: 0.95rem; font-weight: bold;">
                <?= mb_substr($u['name'], 0, 1) ?>
              </div>
              <div class="fw-bold fs-6" style="color: #0b3734;"><?= e($u['name']) ?></div>
            </div>
          </td>
          <td>
            <div class="small fw-semibold text-dark"><i class="fa-solid fa-envelope text-muted me-1"></i><?= e($u['email']) ?></div>
            <div class="small text-muted font-monospace"><i class="fa-solid fa-phone text-muted me-1"></i><?= e($u['phone'] ?: '—') ?></div>
          </td>
          <td>
            <span class="badge <?= $badge[0] ?> px-3 py-1" style="border-radius: 8px; font-size: 0.8rem; font-weight: 700;">
              <i class="fa-solid <?= $badge[2] ?> me-1"></i> <?= $badge[1] ?>
            </span>
          </td>
          <td>
            <span class="badge <?= $isActive ? 'bg-success' : 'bg-danger' ?> px-2 py-1" style="border-radius: 6px; font-size: 0.78rem;">
              <?= $isActive ? '🟢 نشط' : '🔴 معطل' ?>
            </span>
          </td>
          <td class="small text-muted font-monospace"><?= formatDate($u['created_at']) ?></td>
          <td class="text-end pe-4">
            <div class="d-inline-flex gap-1">
              <a href="<?= url('/admin/users/' . $u['id'] . '/edit') ?>" class="btn btn-sm btn-outline-primary fw-bold" style="border-radius: 8px;" title="تعديل وتخصيص الصلاحيات">
                <i class="fa-solid fa-user-pen"></i> تعديل
              </a>
              <form method="POST" action="<?= url('/admin/users/' . $u['id'] . '/toggle') ?>" class="d-inline">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-sm <?= $isActive ? 'btn-outline-warning' : 'btn-outline-success' ?> fw-bold" style="border-radius: 8px;" title="<?= $isActive ? 'تعطيل الحساب' : 'تفعيل الحساب' ?>">
                  <?= $isActive ? '<i class="fa-solid fa-ban"></i> تعطيل' : '<i class="fa-solid fa-check"></i> تفعيل' ?>
                </button>
              </form>
              <form method="POST" action="<?= url('/admin/users/' . $u['id'] . '/delete') ?>" class="d-inline" onsubmit="return confirm('هل أنت متأكد من حذف هذا المستخدم نهائيًا؟');">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-sm btn-outline-danger" style="border-radius: 8px;" title="حذف نهائي">
                  <i class="fa-solid fa-trash-can"></i>
                </button>
              </form>
            </div>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
