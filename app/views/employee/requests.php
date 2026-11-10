<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 pb-3 border-bottom" style="border-color: #d4e7e4 !important;">
  <div class="d-flex align-items-center gap-3">
    <div style="width: 50px; height: 50px; border-radius: 16px; background: #e0e7ff; color: #4338ca; display: flex; align-items: center; justify-content: center; font-size: 1.4rem; border: 1.5px solid #c7d2fe;">
      <i class="fa-solid fa-clipboard-check"></i>
    </div>
    <div>
      <h3 class="fw-bold mb-1" style="color: #1e1b4b;">متابعة وإدارة طلبات الخدمة</h3>
      <p class="mb-0 small text-muted">استقبال طلبات العملاء وتوجيهها للمهنيين وتحديد الأسعار المعتمدة</p>
    </div>
  </div>

  <form method="GET" class="d-flex align-items-center gap-2">
    <div class="input-group input-group-sm" style="min-width: 240px;">
      <span class="input-group-text bg-white border-end-0" style="color: #4338ca;"><i class="fa-solid fa-filter"></i></span>
      <select name="status" class="form-select border-start-0 ps-0" style="border-radius: 0 10px 10px 0; font-weight: 600;" onchange="this.form.submit()">
        <option value="">جميع الحالات (<?= count($requests) ?>)</option>
        <?php foreach (['new'=>'جديد','pending'=>'قيد الانتظار','accepted'=>'مقبول وموجه','in_progress'=>'قيد التنفيذ','awaiting_confirmation'=>'بانتظار الاستلام','completed'=>'مكتمل','cancelled'=>'ملغي','rejected'=>'مرفوض'] as $sKey => $sLabel): ?>
          <option value="<?= $sKey ?>" <?= $status===$sKey?'selected':'' ?>><?= $sLabel ?></option>
        <?php endforeach; ?>
      </select>
    </div>
  </form>
</div>

<div class="card border-0 shadow-sm overflow-hidden" style="border-radius: 20px; border: 1px solid #d4e7e4 !important; background: #ffffff;">
  <div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
      <thead style="background: #f8fafc; border-bottom: 2px solid #e2e8f0;">
        <tr>
          <th class="ps-4 py-3" style="color: #1e1b4b; font-weight: 800; font-size: 0.85rem;">رقم الطلب</th>
          <th class="py-3" style="color: #1e1b4b; font-weight: 800; font-size: 0.85rem;">الخدمة والعميل</th>
          <th class="py-3" style="color: #1e1b4b; font-weight: 800; font-size: 0.85rem;">مقدم الخدمة</th>
          <th class="py-3" style="color: #1e1b4b; font-weight: 800; font-size: 0.85rem;">السعر</th>
          <th class="py-3" style="color: #1e1b4b; font-weight: 800; font-size: 0.85rem;">حالة الطلب</th>
          <th class="text-end pe-4 py-3" style="color: #1e1b4b; font-weight: 800; font-size: 0.85rem;">الإجراء والمتابعة</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($requests)): ?>
          <tr>
            <td colspan="6" class="text-center py-5 text-muted">
              <i class="fa-solid fa-inbox fa-3x text-secondary opacity-50 mb-3 d-block"></i>
              لا توجد طلبات مسجلة بهذه الحالة
            </td>
          </tr>
        <?php endif; ?>
        <?php foreach ($requests as $r): ?>
          <tr>
            <td class="ps-4">
              <div class="fw-bold font-monospace fs-6" style="color: #1e1b4b;">#<?= e($r['request_number']) ?></div>
              <div class="small text-muted font-monospace"><i class="fa-solid fa-calendar text-muted me-1"></i><?= formatDate($r['created_at']) ?></div>
            </td>
            <td>
              <div class="fw-bold text-dark"><?= e($r['service_name_ar'] ?? 'خدمة فنية') ?></div>
              <div class="small text-secondary"><i class="fa-solid fa-user me-1"></i><?= e($r['customer_name'] ?? 'عميل') ?></div>
            </td>
            <td>
              <?php if (!empty($r['provider_name'])): ?>
                <div class="fw-bold text-success"><i class="fa-solid fa-user-check me-1"></i><?= e($r['provider_name']) ?></div>
                <div class="small text-muted"><?= e($r['provider_profession'] ?? 'فني مهني') ?></div>
              <?php else: ?>
                <span class="badge bg-warning text-dark"><i class="fa-solid fa-hourglass-start me-1"></i>بانتظار التوجيه</span>
              <?php endif; ?>
            </td>
            <td>
              <?php if (!empty($r['final_price'])): ?>
                <span class="fw-bold text-success"><?= formatCurrency($r['final_price']) ?></span>
              <?php elseif (!empty($r['display_price'])): ?>
                <span class="fw-bold text-primary"><?= formatCurrency($r['display_price']) ?></span>
              <?php else: ?>
                <span class="text-muted small">غير محدد</span>
              <?php endif; ?>
            </td>
            <td>
              <?= statusBadge($r['status']) ?>
            </td>
            <td class="text-end pe-4">
              <a href="<?= url('/employee/requests/' . $r['id']) ?>" class="btn btn-sm btn-outline-primary px-3 py-1 fw-bold" style="border-radius: 8px;">
                <i class="fa-solid fa-route me-1"></i> التفاصيل والمتابعة
              </a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>