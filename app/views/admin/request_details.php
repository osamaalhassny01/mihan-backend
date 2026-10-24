<div class="d-flex justify-content-between align-items-center mb-3">
  <div class="d-flex align-items-center gap-2">
    <h3 class="mb-0"><?= e($request['request_number']) ?></h3>
    <?= statusBadge($request['status']) ?>
  </div>
  <?php renderBackButton('/admin/requests', 'العودة للطلبات'); ?>
</div>
<div class="card p-4 mb-3">
<p><strong>العميل:</strong> <?= e($request['customer_name']) ?> — <?= e($request['customer_phone']) ?></p>
<p><strong>مقدم الخدمة:</strong> <?= e($request['provider_name'] ?? '-') ?></p>
<p><strong>الخدمة:</strong> <?= e(localized($request['service_name_ar'], $request['service_name_en'] ?? null)) ?></p>
<p><strong>الوصف:</strong> <?= e($request['description']) ?></p>
</div>
<div class="card p-4">
<h6>سجل تتبع الحالة</h6>
<ul class="list-unstyled">
<?php foreach ($history as $h): ?>
<li class="mb-2 border-bottom pb-2">
من <?= statusBadge($h['old_status'] ?? 'new') ?> إلى <?= statusBadge($h['new_status']) ?>
<span class="text-muted small"> بواسطة <?= e($h['changed_by_name'] ?? 'النظام') ?> في <?= formatDate($h['changed_at']) ?></span>
</li>
<?php endforeach; ?>
</ul>
</div>
