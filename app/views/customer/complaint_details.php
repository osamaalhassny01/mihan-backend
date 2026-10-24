<div class="d-flex justify-content-between align-items-center mb-3">
  <div class="d-flex align-items-center gap-2">
    <h3 class="mb-0"><?= e($complaint['complaint_number']) ?></h3>
    <?= statusBadge($complaint['status']) ?>
  </div>
  <?php renderBackButton('/customer/complaints', 'العودة لقائمة الشكاوى'); ?>
</div>
<div class="card p-4 mb-3">
<p><strong>الموضوع:</strong> <?= e($complaint['subject']) ?></p>
<p><strong>التفاصيل:</strong> <?= e($complaint['description']) ?></p>
</div>
<div class="card p-4 mb-3">
<h6>المحادثة</h6>
<?php foreach ($messages as $m): ?>
  <div class="mb-2 p-2 rounded <?= $m['sender_role']==='admin' ? 'bg-light' : 'bg-primary-subtle' ?>">
    <strong><?= e($m['sender_name']) ?></strong> <span class="text-muted small"><?= formatDate($m['created_at']) ?></span>
    <p class="mb-0"><?= e($m['message']) ?></p>
  </div>
<?php endforeach; ?>
</div>
<?php if (!in_array($complaint['status'], ['resolved','closed','rejected'])): ?>
<form method="POST" action="<?= url('/customer/complaints/' . $complaint['id'] . '/reply') ?>">
  <?= csrf_field() ?>
  <div class="input-group">
    <input type="text" name="message" class="form-control" placeholder="اكتب رسالتك..." required>
    <button class="btn btn-primary">إرسال</button>
  </div>
</form>
<?php endif; ?>
