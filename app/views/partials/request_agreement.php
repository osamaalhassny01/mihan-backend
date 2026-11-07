<?php
$isAgreement = (int)($request['workflow_version'] ?? 1) >= 2;
$isProviderAgreement = ($agreementRole ?? 'customer') === 'provider';
$hasQuote = !empty($request['quote_id']);
$quoteStatus = $request['quote_status'] ?? '';
?>
<?php if ($isAgreement || $hasQuote): ?>
<div class="card p-4 mb-3 border-info">
  <h5>اتفاق الخدمة</h5>
  <p class="small text-muted">السعر ونطاق العمل يُعتمدان قبل التنفيذ، وتصدر الفاتورة بعد تأكيد العميل للاستلام.</p>
  <?php if ($hasQuote): ?>
    <div class="fs-4 fw-bold mb-2"><?= money($request['quoted_price']) ?></div>
    <p style="white-space:pre-wrap"><?= e($request['quote_details']) ?></p>
    <?php if (!empty($request['visit_at'])): ?><p><strong>موعد الزيارة:</strong> <?= e($request['visit_at']) ?></p><?php endif; ?>
    <p class="fw-bold"><?= e(['pending'=>'بانتظار موافقة العميل','approved'=>'عرض معتمد وسعر ثابت','declined'=>'طلب العميل عرضًا جديدًا'][$quoteStatus] ?? 'عرض الخدمة') ?></p>
    <?php if (!$isProviderAgreement && $request['status'] === 'accepted' && $quoteStatus === 'pending'): ?>
    <form method="post" action="<?= url('/customer/requests/'.$request['id'].'/quote') ?>" class="d-flex gap-2 flex-wrap">
      <?= csrf_field() ?><input type="hidden" name="quote_id" value="<?= (int)$request['quote_id'] ?>">
      <button name="decision" value="approve" class="btn btn-success">أوافق على العرض والسعر</button>
      <button name="decision" value="decline" class="btn btn-outline-secondary">أحتاج عرضًا جديدًا</button>
    </form>
    <?php endif; ?>
  <?php else: ?><p>بعد حجز الطلب، يرسل مقدم الخدمة عرضًا يوضح السعر وما يشمله العمل.</p><?php endif; ?>
  <?php if ($isProviderAgreement && $request['status'] === 'accepted' && $quoteStatus !== 'approved'): ?>
    <form method="post" action="<?= url('/provider/requests/'.$request['id'].'/quote') ?>" class="mt-3">
      <?= csrf_field() ?>
      <label class="form-label">إجمالي السعر شامل جميع بنود العرض</label>
      <input type="number" name="total_price" min="0.01" max="99999999.99" step="0.01" required class="form-control mb-2" value="<?= e($request['quoted_price'] ?? '') ?>">
      <label class="form-label">ما الذي يشمله السعر؟ وما البنود المستثناة؟</label>
      <textarea name="details" minlength="10" maxlength="3000" required class="form-control mb-2" rows="3"><?= e($request['quote_details'] ?? '') ?></textarea>
      <label class="form-label">موعد الزيارة المقترح (اختياري)</label>
      <input type="datetime-local" name="visit_at" class="form-control mb-3">
      <button class="btn btn-primary">إرسال العرض للعميل</button>
    </form>
  <?php endif; ?>
</div>
<?php endif; ?>
<?php if ($request['status'] === 'in_progress' && !empty($request['rework_notes'])): ?>
<div class="alert alert-warning"><strong>ملاحظات العميل للمعالجة</strong><p class="mb-0" style="white-space:pre-wrap"><?= e($request['rework_notes']) ?></p></div>
<?php endif; ?>
<?php if ($request['status'] === 'awaiting_confirmation'): ?>
<div class="card p-4 mb-3 border-warning">
  <h5>بانتظار تأكيد الاستلام</h5>
  <p style="white-space:pre-wrap"><?= e($request['completion_notes']) ?></p>
  <?php if (!$isProviderAgreement): ?>
    <form method="post" action="<?= url('/customer/requests/'.$request['id'].'/confirm-completion') ?>" class="mb-3">
      <?= csrf_field() ?><button class="btn btn-success">أؤكد استلام الخدمة وإصدار الفاتورة</button>
    </form>
    <form method="post" action="<?= url('/customer/requests/'.$request['id'].'/rework') ?>">
      <?= csrf_field() ?><label class="form-label">توجد ملاحظات قبل الاستلام؟</label>
      <textarea name="notes" required minlength="5" maxlength="3000" class="form-control mb-2" placeholder="صف ما يحتاج إلى معالجة"></textarea>
      <button class="btn btn-outline-warning">إرسال الملاحظات وإعادة الطلب للتنفيذ</button>
    </form>
  <?php else: ?><p class="mb-0">أرسلت ملخص الإنجاز. ستصدر الفاتورة بالسعر المعتمد بعد تأكيد العميل للاستلام.</p><?php endif; ?>
</div>
<?php endif; ?>
