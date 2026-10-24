<div class="d-flex justify-content-between align-items-center mb-3">
  <div class="d-flex align-items-center gap-2">
    <h3 class="mb-0"><?= e($request['request_number']) ?></h3>
    <?= statusBadge($request['status']) ?>
  </div>
  <?php renderBackButton('/provider/requests', 'العودة للطلبات'); ?>
</div>
<div class="row">
<div class="col-md-7">
  <div class="card p-4 mb-3">
    <p><strong>العميل:</strong> <?= e($request['customer_name']) ?></p>
    <p><strong>الخدمة:</strong> <?= e(localized($request['service_name_ar'], $request['service_name_en'] ?? null)) ?></p>
    <p><strong>الوصف:</strong> <?= e($request['description']) ?></p>
    <p><strong>المنطقة:</strong> <?= e(localized($request['location_name_ar'] ?? '-', $request['location_name_en'] ?? null)) ?></p>
    <p><strong>الميزانية:</strong> <?= $request['budget'] ? number_format($request['budget']) . ' ريال' : '-' ?></p>
    <?php if (in_array($request['status'], ['accepted','in_progress'], true)): ?><a class="btn btn-info btn-sm mt-2" href="<?= url('/provider/requests/' . $request['id'] . '/assistant') ?>"><i class="fa-solid fa-screwdriver-wrench"></i> المساعد المهني</a><?php endif; ?>

    <div class="d-flex gap-2 mt-3">
      <?php if ($request['status'] === 'pending'): ?>
        <form method="POST" action="<?= url('/provider/requests/' . $request['id'] . '/accept') ?>"><?= csrf_field() ?><button class="btn btn-success btn-sm">قبول الطلب</button></form>
        <form method="POST" action="<?= url('/provider/requests/' . $request['id'] . '/reject') ?>"><?= csrf_field() ?><button class="btn btn-outline-danger btn-sm">رفض الطلب</button></form>
      <?php elseif ($request['status'] === 'accepted'): ?>
        <form method="POST" action="<?= url('/provider/requests/' . $request['id'] . '/start') ?>"><?= csrf_field() ?><button class="btn btn-primary btn-sm">بدء التنفيذ</button></form>
      <?php elseif ($request['status'] === 'in_progress'): ?>
        <form method="POST" action="<?= url('/provider/requests/' . $request['id'] . '/complete') ?>" class="d-flex gap-2">
          <?= csrf_field() ?>
          <input type="number" name="final_price" class="form-control form-control-sm" placeholder="السعر النهائي" value="<?= $request['budget'] ?>" style="max-width:150px">
          <button class="btn btn-success btn-sm">إكمال الخدمة</button>
        </form>
      <?php endif; ?>
    </div>
  </div>
</div>
<div class="col-md-12">
<?php if (settingBool('internal_messaging_enabled', true) && in_array($request['status'], ['accepted','in_progress','completed'], true)): ?>
<div class="card p-4 mb-3 border-info"><div class="d-flex justify-content-between align-items-center"><div><h6 class="mb-1">التواصل مع العميل</h6><div class="small text-muted">التواصل يتم من خلال منصة مِهَن فقط.</div></div><a class="btn btn-info" href="<?= url('/provider/requests/' . $request['id'] . '/conversation') ?>"><i class="fa-solid fa-comments"></i> فتح المحادثة</a></div></div>
<?php endif; ?>

<?php if ($invoice): ?>
<div class="card p-4 mb-3">
  <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3"><h6 class="mb-0">الحساب والفاتورة</h6><span><?= statusBadge($invoice['payment_status']) ?></span></div>
  <?php $paidAmount = max(0, (float)$invoice['total_amount'] - (new Invoice())->balance((int)$invoice['id'])); $balanceAmount = (new Invoice())->balance((int)$invoice['id']); ?>
  <div class="row g-2 small">
    <div class="col-6"><span class="text-muted">رقم الفاتورة</span><div class="fw-semibold"><?= e($invoice['invoice_number']) ?></div></div>
    <div class="col-6"><span class="text-muted">سعر الخدمة</span><div class="fw-semibold"><?= number_format($invoice['service_price'],2) ?> <?= e(setting('currency','ريال')) ?></div></div>
    <div class="col-6"><span class="text-muted">عمولة المنصة</span><div class="fw-semibold"><?= number_format($invoice['commission_amount'],2) ?> <?= e(setting('currency','ريال')) ?></div></div>
    <div class="col-6"><span class="text-muted">صافي مستحقك</span><div class="fw-semibold"><?= number_format($invoice['provider_net'],2) ?> <?= e(setting('currency','ريال')) ?></div></div>
    <div class="col-6"><span class="text-muted">المحصّل</span><div class="fw-semibold text-success"><?= number_format($paidAmount,2) ?> <?= e(setting('currency','ريال')) ?></div></div>
    <div class="col-6"><span class="text-muted">المتبقي على العميل</span><div class="fw-semibold <?= $balanceAmount>0?'text-warning':'text-success' ?>"><?= number_format($balanceAmount,2) ?> <?= e(setting('currency','ريال')) ?></div></div>
  </div>
  <?php if ($payments): ?><hr><div class="small text-muted mb-2">سجل الدفعات</div><?php foreach ($payments as $pay): ?><div class="d-flex justify-content-between border-bottom py-2 small"><span><?= e(['simulated_card'=>'بطاقة محاكاة','simulated_wallet'=>'محفظة محاكاة'][$pay['method']] ?? $pay['method']) ?> · <?= e($pay['paid_at']) ?></span><strong><?= number_format($pay['amount'],2) ?> <?= e(setting('currency','ريال')) ?></strong></div><?php endforeach; ?><?php else: ?><div class="alert alert-light border mt-3 mb-0 small">لم تُسجل دفعة بعد.</div><?php endif; ?>

  <div class="alert alert-success border mt-3 mb-0 small"><i class="fa-solid fa-shield-halved"></i> الدفع يتم عبر منصة مِهَن، ولا يتم تسجيل أي مبلغ نقدي مباشر لمقدم الخدمة.</div>
</div>
<?php endif; ?>
</div>

<div class="col-md-5">
  <div class="card p-4">
    <h6>سجل تتبع الحالة</h6>
    <ul class="list-unstyled">
      <?php foreach ($history as $h): ?>
        <li class="mb-2 border-bottom pb-2"><?= statusBadge($h['new_status']) ?> <span class="text-muted small"><?= formatDate($h['changed_at']) ?></span></li>
      <?php endforeach; ?>
    </ul>
  </div>
</div>
</div>
