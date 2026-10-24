<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h3 class="mb-1"><?= __('reviews') ?></h3>
    <div class="text-muted small">يمكنك الرد على كل تقييم من العملاء مباشرة.</div>
  </div>
  <?php renderBackButton('/provider/dashboard', 'العودة للوحة التحكم'); ?>
</div>
<?php foreach ($reviews as $r): ?>
<div class="card p-3 mb-3">
  <div class="d-flex justify-content-between">
    <strong><?= e($r['customer_name']) ?></strong>
    <span class="rating-stars"><?php for ($s=1;$s<=5;$s++): ?><i class="fa-solid fa-star<?= $s <= $r['rating'] ? '' : ' text-secondary opacity-25' ?>"></i><?php endfor; ?></span>
  </div>
  <p class="mb-2"><?= e($r['comment']) ?></p>
  <?php if ($r['provider_reply']): ?>
    <div class="bg-light p-2 rounded small mb-2"><i class="fa-solid fa-reply"></i> ردك: <?= e($r['provider_reply']) ?></div>
  <?php else: ?>
    <form method="POST" action="<?= url('/provider/reviews/' . (int)$r['id'] . '/reply') ?>" class="review-reply-form">
      <div class="input-group">
      <?= csrf_field() ?>
      <input type="text" name="reply" class="form-control form-control-sm" placeholder="اكتب ردك على هذا التقييم">
      <button type="submit" class="btn btn-primary"><i class="fa-solid fa-paper-plane"></i> رد على التقييم</button>
      </div>
    </form>
  <?php endif; ?>
</div>
<?php endforeach; ?>
<?php if (empty($reviews)): ?><p class="text-muted text-center py-4">لا توجد تقييمات بعد</p><?php endif; ?>
