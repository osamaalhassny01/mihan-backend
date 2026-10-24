<h3 class="mb-4"><?= __('notifications') ?></h3>
<div class="list-group">
<?php foreach ($notifications as $n): ?>
  <a href="<?= url($n['link'] ?? '#') ?>" class="list-group-item list-group-item-action">
    <div class="d-flex justify-content-between">
      <strong><?= e($n['title']) ?></strong>
      <small class="text-muted"><?= formatDate($n['created_at']) ?></small>
    </div>
    <p class="mb-0 small"><?= e($n['message']) ?></p>
  </a>
<?php endforeach; ?>
<?php if (empty($notifications)): ?><div class="text-muted text-center py-4">لا توجد إشعارات</div><?php endif; ?>
</div>
