<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 pb-3 border-bottom" style="border-color: #d4e7e4 !important;">
  <div class="d-flex align-items-center gap-3">
    <div style="width: 50px; height: 50px; border-radius: 16px; background: #e0e7ff; color: #4338ca; display: flex; align-items: center; justify-content: center; font-size: 1.4rem; border: 1.5px solid #c7d2fe;">
      <i class="fa-solid fa-clipboard-check"></i>
    </div>
    <div>
      <div class="d-flex align-items-center gap-2">
        <h3 class="fw-bold mb-0" style="color: #1e1b4b;">طلب رقم #<?= e($request['request_number']) ?></h3>
        <?= statusBadge($request['status']) ?>
      </div>
      <p class="mb-0 small text-muted">تاريخ الإنشاء: <?= formatDate($request['created_at']) ?></p>
    </div>
  </div>

  <div class="d-flex align-items-center gap-2">
    <?php if (!in_array($request['status'], ['completed', 'cancelled'])): ?>
      <button type="button" class="btn btn-success fw-bold px-3 py-2 shadow-sm d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#forwardModal" style="border-radius: 12px; background: #059669; border-color: #059669;">
        <i class="fa-solid fa-user-check"></i>
        <span>قبول وتحويل لمقدم الخدمة مع تحديد السعر</span>
      </button>

      <button type="button" class="btn btn-outline-danger fw-bold px-3 py-2 shadow-sm d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#cancelModal" style="border-radius: 12px;">
        <i class="fa-solid fa-ban"></i>
        <span>إلغاء الطلب</span>
      </button>
    <?php endif; ?>
    <?php renderBackButton('/employee/requests', 'العودة للطلبات'); ?>
  </div>
</div>

<!-- Tracking Stepper Header -->
<div class="card border-0 shadow-sm mb-4" style="border-radius: 20px; border: 1px solid #d4e7e4 !important; background: #ffffff;">
  <div class="card-body p-4">
    <h6 class="fw-bold mb-3" style="color: #1e1b4b;"><i class="fa-solid fa-route text-primary me-2"></i>مسار متابعة الطلب</h6>
    <?php
      $currentStatus = $request['status'];
      $steps = [
        ['key' => 'new', 'label' => 'إنشاء الطلب', 'icon' => 'fa-plus'],
        ['key' => 'accepted', 'label' => 'اعتماد وتوجيه', 'icon' => 'fa-user-check'],
        ['key' => 'in_progress', 'label' => 'قيد التنفيذ', 'icon' => 'fa-screwdriver-wrench'],
        ['key' => 'awaiting_confirmation', 'label' => 'بانتظار الاستلام', 'icon' => 'fa-clock'],
        ['key' => 'completed', 'label' => 'مكتمل بنجاح', 'icon' => 'fa-check-double'],
      ];
      $statusOrder = ['new' => 0, 'pending' => 0, 'accepted' => 1, 'in_progress' => 2, 'awaiting_confirmation' => 3, 'completed' => 4, 'cancelled' => -1, 'rejected' => -1];
      $currentStepIdx = $statusOrder[$currentStatus] ?? 0;
    ?>
    <?php if ($currentStatus === 'cancelled'): ?>
      <div class="alert alert-danger d-flex align-items-center gap-2 mb-0" style="border-radius: 14px;">
        <i class="fa-solid fa-triangle-exclamation fs-5"></i>
        <div><strong>هذا الطلب ملغي.</strong> <?= !empty($request['notes']) ? e($request['notes']) : '' ?></div>
      </div>
    <?php else: ?>
      <div class="d-flex justify-content-between position-relative px-2">
        <?php foreach ($steps as $idx => $step): 
          $isDone = $currentStepIdx >= $idx;
          $isActive = $currentStepIdx === $idx;
        ?>
          <div class="text-center position-relative" style="z-index: 1; flex: 1;">
            <div style="width: 44px; height: 44px; border-radius: 50%; margin: 0 auto; display: flex; align-items: center; justify-content: center; font-size: 1.1rem;
                 background: <?= $isDone ? '#4338ca' : '#f1f5f9' ?>; color: <?= $isDone ? '#ffffff' : '#94a3b8' ?>; border: 3px solid <?= $isActive ? '#6366f1' : ($isDone ? '#4338ca' : '#e2e8f0') ?>;">
              <i class="fa-solid <?= $step['icon'] ?>"></i>
            </div>
            <div class="mt-2 small fw-bold" style="color: <?= $isDone ? '#1e1b4b' : '#94a3b8' ?>;"><?= $step['label'] ?></div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</div>

<div class="row g-4 mb-4">
  <!-- Request Info -->
  <div class="col-lg-7">
    <div class="card border-0 shadow-sm h-100" style="border-radius: 20px; border: 1px solid #d4e7e4 !important; background: #ffffff;">
      <div class="card-body p-4">
        <h5 class="fw-bold mb-3" style="color: #1e1b4b;"><i class="fa-solid fa-circle-info text-primary me-2"></i>تفاصيل الخدمة والطلب</h5>
        
        <div class="table-responsive">
          <table class="table table-borderless align-middle mb-0">
            <tr>
              <td class="text-muted" style="width: 140px;">الخدمة المطلوبة:</td>
              <td class="fw-bold fs-6 text-primary"><?= e(localized($request['service_name_ar'], $request['service_name_en'] ?? null)) ?></td>
            </tr>
            <tr>
              <td class="text-muted">المنطقة / العنوان:</td>
              <td class="fw-bold"><?= e(localized($request['location_name_ar'] ?? 'غير محدد', $request['location_name_en'] ?? null)) ?></td>
            </tr>
            <tr>
              <td class="text-muted">تاريخ / موعد الزيارة:</td>
              <td><?= !empty($request['preferred_date']) ? e($request['preferred_date']) . ' ' . e($request['preferred_time'] ?? '') : 'في أقرب وقت ممكن' ?></td>
            </tr>
            <tr>
              <td class="text-muted">الميزانية المقترحة:</td>
              <td><?= !empty($request['budget']) ? formatCurrency($request['budget']) : 'غير محددة' ?></td>
            </tr>
            <tr>
              <td class="text-muted">السعر المعتمد:</td>
              <td class="fw-bold fs-5 text-success">
                <?= !empty($request['final_price']) ? formatCurrency($request['final_price']) : (!empty($request['quoted_price']) ? formatCurrency($request['quoted_price']) : '<span class="text-muted fs-6">بانتظار تحديد الموظف/الإدارة</span>') ?>
              </td>
            </tr>
            <tr>
              <td class="text-muted align-top">وصف المشكلة:</td>
              <td class="bg-light p-3" style="border-radius: 12px; line-height: 1.6;"><?= nl2br(e($request['description'])) ?></td>
            </tr>
            <?php if (!empty($request['notes'])): ?>
              <tr>
                <td class="text-muted align-top">ملاحظات إضافية:</td>
                <td class="p-3" style="background: #fffbeb; border-radius: 12px; color: #92400e;"><?= nl2br(e($request['notes'])) ?></td>
              </tr>
            <?php endif; ?>
          </table>
        </div>
      </div>
    </div>
  </div>

  <!-- Customer & Provider Info -->
  <div class="col-lg-5">
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 20px; border: 1px solid #d4e7e4 !important; background: #ffffff;">
      <div class="card-body p-4">
        <h6 class="fw-bold mb-3" style="color: #1e1b4b;"><i class="fa-solid fa-user text-secondary me-2"></i>بيانات العميل المستفيد</h6>
        <div class="d-flex align-items-center gap-3">
          <div style="width: 48px; height: 48px; border-radius: 14px; background: #e0f2fe; color: #0284c7; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; font-weight: bold;">
            <?= mb_substr($request['customer_name'] ?? 'ع', 0, 1) ?>
          </div>
          <div>
            <div class="fw-bold text-dark"><?= e($request['customer_name']) ?></div>
            <div class="small text-muted font-monospace"><i class="fa-solid fa-phone me-1"></i><?= e($request['customer_phone']) ?></div>
          </div>
        </div>
      </div>
    </div>

    <div class="card border-0 shadow-sm" style="border-radius: 20px; border: 1px solid #d4e7e4 !important; background: #ffffff;">
      <div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h6 class="fw-bold mb-0" style="color: #1e1b4b;"><i class="fa-solid fa-handyman text-warning me-2"></i>مقدم الخدمة المكلف</h6>
          <?php if (!empty($request['provider_name'])): ?>
            <span class="badge bg-success" style="border-radius: 8px;">معين ومكلف</span>
          <?php else: ?>
            <span class="badge bg-warning text-dark" style="border-radius: 8px;">غير محدد بعد</span>
          <?php endif; ?>
        </div>

        <?php if (!empty($request['provider_name'])): ?>
          <div class="d-flex align-items-center gap-3">
            <div style="width: 48px; height: 48px; border-radius: 14px; background: #fef3c7; color: #d97706; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; font-weight: bold;">
              <?= mb_substr($request['provider_name'], 0, 1) ?>
            </div>
            <div>
              <div class="fw-bold text-dark"><?= e($request['provider_name']) ?></div>
              <div class="small text-muted"><?= e($request['provider_profession'] ?? 'فني مهني معتمد') ?></div>
              <?php if (!empty($request['provider_avg_rating'])): ?>
                <div class="small text-warning"><i class="fa-solid fa-star"></i> <?= number_format((float)$request['provider_avg_rating'], 1) ?></div>
              <?php endif; ?>
            </div>
          </div>
        <?php else: ?>
          <div class="text-center py-3 text-muted">
            <i class="fa-solid fa-user-clock fa-2x mb-2 text-secondary opacity-50"></i>
            <div>الطلب بانتظار توجيه الموظف لمقدم الخدمة وتحديد السعر</div>
            <?php if (!in_array($request['status'], ['completed', 'cancelled'])): ?>
              <button class="btn btn-sm btn-outline-success mt-2 fw-bold" data-bs-toggle="modal" data-bs-target="#forwardModal" style="border-radius: 8px;">
                <i class="fa-solid fa-plus me-1"></i> تعيين مقدم الخدمة الآن
              </button>
            <?php endif; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<!-- History & Tracking Logs -->
<div class="card border-0 shadow-sm" style="border-radius: 20px; border: 1px solid #d4e7e4 !important; background: #ffffff;">
  <div class="card-body p-4">
    <h5 class="fw-bold mb-3" style="color: #1e1b4b;"><i class="fa-solid fa-clock-rotate-left text-info me-2"></i>سجل المتابعة والتتبع الزمني للطلب</h5>
    
    <?php if (empty($history)): ?>
      <p class="text-muted mb-0">لا توجد سجلات تتبع إضافية.</p>
    <?php else: ?>
      <div class="timeline ps-2">
        <?php foreach ($history as $h): ?>
          <div class="d-flex gap-3 mb-3 pb-3 border-bottom position-relative">
            <div style="width: 36px; height: 36px; border-radius: 50%; background: #e0e7ff; color: #4338ca; display: flex; align-items: center; justify-content: center; font-size: 0.9rem; flex-shrink: 0;">
              <i class="fa-solid fa-arrow-left"></i>
            </div>
            <div class="flex-grow-1">
              <div class="d-flex justify-content-between align-items-center">
                <span class="fw-bold">
                  تغيير الحالة إلى <?= statusBadge($h['new_status']) ?>
                  <?php if (!empty($h['old_status'])): ?>
                    <span class="small text-muted ms-1">(من <?= statusBadge($h['old_status']) ?>)</span>
                  <?php endif; ?>
                </span>
                <span class="small text-muted font-monospace"><i class="fa-solid fa-calendar me-1"></i><?= formatDate($h['changed_at']) ?></span>
              </div>
              <div class="small text-secondary mt-1">
                بواسطة: <strong><?= e($h['changed_by_name'] ?? 'النظام الآلي') ?></strong>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</div>

<!-- Modal: Forward & Set Price -->
<div class="modal fade" id="forwardModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow" style="border-radius: 20px;">
      <form action="<?= url('/employee/requests/' . $request['id'] . '/forward') ?>" method="POST">
        <?= csrfField() ?>
        <div class="modal-header border-0 pb-0 pt-4 px-4">
          <h5 class="modal-title fw-bold" style="color: #1e1b4b;">
            <i class="fa-solid fa-user-check text-success me-2"></i>قبول الطلب وتوجيهه لمقدم الخدمة
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
        </div>
        <div class="modal-body p-4">
          <p class="text-muted small mb-3">اختر مقدم الخدمة المعتمد وحدد السعر المتفق عليه لهذا الطلب. سيتم إشعار كل من العميل ومقدم الخدمة فوراً.</p>

          <div class="mb-3">
            <label class="form-label fw-bold small text-dark">مقدم الخدمة المكلف: <span class="text-danger">*</span></label>
            <select name="provider_id" class="form-select" required style="border-radius: 12px; padding: 10px 14px;">
              <option value="">-- اختر مقدم الخدمة المعتمد --</option>
              <?php foreach (($providers ?? []) as $p): ?>
                <option value="<?= $p['id'] ?>" <?= (!empty($request['provider_id']) && $request['provider_id'] == $p['id']) ? 'selected' : '' ?>>
                  <?= e($p['name']) ?> (<?= e($p['profession'] ?? 'فني مهني') ?>) - تقييم: <?= number_format((float)($p['avg_rating'] ?? 5), 1) ?> ⭐
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label fw-bold small text-dark">السعر المعتمد للخدمة (بالريال): <span class="text-danger">*</span></label>
            <div class="input-group">
              <input type="number" step="0.01" min="1" name="price" class="form-control" placeholder="مثال: 5000" value="<?= e($request['final_price'] ?? $request['budget'] ?? '') ?>" required style="border-radius: 0 12px 12px 0; padding: 10px 14px;">
              <span class="input-group-text bg-light text-muted fw-bold" style="border-radius: 12px 0 0 12px;">ر.ي</span>
            </div>
            <div class="form-text small text-muted">الميزانية المقترحة من العميل: <?= !empty($request['budget']) ? formatCurrency($request['budget']) : 'غير محددة' ?></div>
          </div>

          <div class="mb-3">
            <label class="form-label fw-bold small text-dark">ملاحظات وتوجيهات لمقدم الخدمة (اختياري):</label>
            <textarea name="notes" rows="3" class="form-control" placeholder="تعليمات نطاق العمل، شروط الإنجاز، وملاحظات التنسيق مع العميل..." style="border-radius: 12px;"><?= e($request['notes'] ?? '') ?></textarea>
          </div>
        </div>
        <div class="modal-footer border-0 pt-0 pb-4 px-4">
          <button type="button" class="btn btn-light fw-bold px-3 py-2" data-bs-dismiss="modal" style="border-radius: 10px;">إلغاء</button>
          <button type="submit" class="btn btn-success fw-bold px-4 py-2" style="border-radius: 10px; background: #059669; border-color: #059669;">
            <i class="fa-solid fa-check me-1"></i> تأكيد التحويل والاعتماد
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal: Cancel Request -->
<div class="modal fade" id="cancelModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow" style="border-radius: 20px;">
      <form action="<?= url('/employee/requests/' . $request['id'] . '/cancel') ?>" method="POST">
        <?= csrfField() ?>
        <div class="modal-header border-0 pb-0 pt-4 px-4">
          <h5 class="modal-title fw-bold text-danger">
            <i class="fa-solid fa-ban me-2"></i>إلغاء طلب الخدمة
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
        </div>
        <div class="modal-body p-4">
          <p class="text-muted small mb-3">هل أنت متأكد من رغبتك في إلغاء هذا الطلب؟ سيتم إشعار العميل بسبب الإلغاء.</p>

          <div class="mb-3">
            <label class="form-label fw-bold small text-dark">سبب الإلغاء: <span class="text-danger">*</span></label>
            <textarea name="reason" rows="3" class="form-control" placeholder="اكتب سبب إلغاء الطلب (مثلاً: عدم توفر فني بالمنطقة، طلب غير واضح، أو بطلب من العميل)..." required style="border-radius: 12px;"></textarea>
          </div>
        </div>
        <div class="modal-footer border-0 pt-0 pb-4 px-4">
          <button type="button" class="btn btn-light fw-bold px-3 py-2" data-bs-dismiss="modal" style="border-radius: 10px;">تراجع</button>
          <button type="submit" class="btn btn-danger fw-bold px-4 py-2" style="border-radius: 10px;">
            <i class="fa-solid fa-trash me-1"></i> نعم، تأكيد إلغاء الطلب
          </button>
        </div>
      </form>
    </div>
  </div>
</div>