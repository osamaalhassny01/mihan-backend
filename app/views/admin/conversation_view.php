<div class="d-flex justify-content-between align-items-center mb-3">
  <div>
    <h4 class="mb-1">محادثة: <?= e($conversation['request_number']) ?></h4>
    <p class="text-muted mb-0">
      <strong>العميل:</strong> <?= e($conversation['customer_name']) ?> &nbsp;|&nbsp;
      <strong>مقدم الخدمة:</strong> <?= e($conversation['provider_name'] ?? '—') ?>
    </p>
  </div>
  <div class="d-flex gap-2">
    <a href="<?= url('/admin/requests/' . $conversation['request_id']) ?>" class="btn btn-outline-secondary btn-sm">الطلب</a>
    <?php renderBackButton('/admin/conversations', 'العودة للمحادثات'); ?>
  </div>
</div>

<div class="alert alert-info py-2 mb-3"><i class="fa-solid fa-eye me-1"></i> وضع مراقبة — للاطلاع فقط. لا يمكن إرسال رسائل من هنا.</div>

<div class="card shadow-sm">
  <div class="card-body p-0">
    <?php if ($messages): ?>
    <div class="list-group list-group-flush">
      <?php foreach ($messages as $msg): ?>
      <?php $isCustomer = ($msg['sender_role'] === 'customer'); ?>
      <div class="list-group-item py-3 px-4">
        <div class="d-flex justify-content-between align-items-start">
          <div>
            <strong><?= e($msg['sender_name']) ?></strong>
            <span class="badge ms-1 <?= $isCustomer ? 'bg-primary' : 'bg-success' ?> bg-opacity-10 text-<?= $isCustomer ? 'primary' : 'success' ?>">
              <?= $isCustomer ? 'عميل' : 'مقدم خدمة' ?>
            </span>
          </div>
          <small class="text-muted"><?= e($msg['created_at']) ?></small>
        </div>
        <p class="mb-0 mt-1"><?= nl2br(e($msg['message'])) ?></p>
      </div>
      <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div class="p-4 text-muted text-center">لا توجد رسائل في هذه المحادثة بعد.</div>
    <?php endif; ?>
  </div>
</div>
