<div class="d-flex justify-content-between align-items-center mb-3">
  <div class="d-flex align-items-center gap-2">
    <h3 class="mb-0"><?= e($complaint['complaint_number']) ?></h3>
    <form method="POST" action="<?= url('/admin/complaints/' . $complaint['id'] . '/status') ?>" class="d-inline-flex gap-2">
      <?= csrf_field() ?>
      <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
        <?php foreach (['open','in_review','resolved','rejected','closed'] as $s): ?>
          <option value="<?= $s ?>" <?= $complaint['status']===$s?'selected':'' ?>><?= e($s) ?></option>
        <?php endforeach; ?>
      </select>
    </form>
  </div>
  <?php renderBackButton('/admin/complaints', 'العودة للشكاوى'); ?>
</div>
<div class="card p-4 mb-3">
<p><strong>الموضوع:</strong> <?= e($complaint['subject']) ?></p>
<p><strong>التفاصيل:</strong> <?= e($complaint['description']) ?></p>
</div>
<div class="card p-4 mb-3">
<h6>المحادثة</h6>
<?php foreach ($messages as $m): ?>
<div class="mb-2 p-2 rounded <?= $m['sender_role']==='admin'?'bg-light':'bg-primary-subtle' ?>">
<strong><?= e($m['sender_name']) ?></strong> <span class="text-muted small"><?= formatDate($m['created_at']) ?></span>
<p class="mb-0"><?= e($m['message']) ?></p>
</div>
<?php endforeach; ?>
</div>
<form method="POST" action="<?= url('/admin/complaints/' . $complaint['id'] . '/reply') ?>" class="input-group">
<?= csrf_field() ?>
<input type="text" name="message" class="form-control" placeholder="اكتب ردك..." required>
<button class="btn btn-primary">إرسال</button>
</form>
