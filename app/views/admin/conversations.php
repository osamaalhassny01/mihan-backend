<div class="d-flex justify-content-between align-items-center mb-4">
  <div><h3 class="mb-1">المحادثات</h3><p class="text-muted mb-0">مراقبة التواصل بين العملاء ومقدمي الخدمة (للاطلاع فقط)</p></div>
</div>

<div class="card shadow-sm">
  <div class="card-body">
    <?php if ($conversations): ?>
    <div class="table-responsive">
      <table class="table table-hover align-middle">
        <thead><tr><th>#</th><th>الطلب</th><th>العميل</th><th>مقدم الخدمة</th><th>الرسائل</th><th>آخر رسالة</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($conversations as $c): ?>
        <tr>
          <td><?= (int)$c['id'] ?></td>
          <td><a href="<?= url('/admin/requests/' . $c['request_id']) ?>"><?= e($c['request_number']) ?></a></td>
          <td><?= e($c['customer_name']) ?></td>
          <td><?= e($c['provider_name'] ?? '—') ?></td>
          <td><span class="badge bg-secondary"><?= (int)$c['message_count'] ?></span></td>
          <td class="small text-muted"><?= $c['last_message_at'] ? e($c['last_message_at']) : '—' ?></td>
          <td><a href="<?= url('/admin/conversations/' . $c['id']) ?>" class="btn btn-sm btn-outline-primary">عرض</a></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php else: ?>
    <div class="alert alert-info mb-0">لا توجد محادثات حتى الآن.</div>
    <?php endif; ?>
  </div>
</div>
