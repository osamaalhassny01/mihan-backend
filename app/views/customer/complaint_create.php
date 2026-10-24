<div class="d-flex justify-content-between align-items-center mb-4">
  <h3 class="mb-0">شكوى جديدة</h3>
  <?php renderBackButton('/customer/complaints', 'العودة لقائمة الشكاوى'); ?>
</div>
<div class="card p-4">
  <form method="POST" action="<?= url('/customer/complaints') ?>">
    <?= csrf_field() ?>
    <?php if (!empty($againstProvider)): ?>
      <input type="hidden" name="against_user_id" value="<?= (int)$againstProvider['user_id'] ?>">
      <div class="alert alert-warning py-2"><i class="fa-solid fa-flag"></i> البلاغ موجه إلى مقدم الخدمة: <strong><?= e($againstProvider['name']) ?></strong></div>
    <?php endif; ?>
    <div class="mb-3">
      <label class="form-label">مرتبطة بطلب (اختياري)</label>
      <select name="request_id" class="form-select">
        <option value="">بدون طلب محدد</option>
        <?php foreach ($requests as $r): ?><option value="<?= $r['id'] ?>"><?= e($r['request_number']) ?> - <?= e(localized($r['service_name_ar'], $r['service_name_en'] ?? null)) ?></option><?php endforeach; ?>
      </select>
    </div>
    <div class="mb-3">
      <label class="form-label">الموضوع</label>
      <input type="text" name="subject" class="form-control" value="<?= e($prefillSubject ?? '') ?>" required>
    </div>
    <div class="mb-3">
      <label class="form-label">التفاصيل</label>
      <textarea name="description" class="form-control" rows="4" required></textarea>
    </div>
    <button class="btn btn-primary"><?= __('submit') ?></button>
  </form>
</div>
