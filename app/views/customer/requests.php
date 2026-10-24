<div class="d-flex justify-content-between align-items-center mb-3">
  <h3><?= __('my_requests') ?></h3>
  <a href="<?= url('/customer/requests/create') ?>" class="btn btn-primary btn-sm"><i class="fa-solid fa-plus"></i> <?= __('new_request') ?></a>
</div>
<div class="table-responsive">
<table class="table table-hover bg-white rounded shadow-sm">
<thead><tr><th>رقم الطلب</th><th>الخدمة</th><th>مقدم الخدمة</th><th><?= __('status') ?></th><th>التاريخ</th><th></th></tr></thead>
<tbody>
<?php foreach ($requests as $r): ?>
<tr>
<td><?= e($r['request_number']) ?></td>
<td><?= e(localized($r['service_name_ar'], $r['service_name_en'] ?? null)) ?></td>
<td><?= e($r['provider_name'] ?? '—') ?></td>
<td><?= statusBadge($r['status']) ?></td>
<td><?= formatDate($r['created_at']) ?></td>
<td><a href="<?= url('/customer/requests/' . $r['id']) ?>" class="btn btn-sm btn-outline-primary">عرض</a></td>
</tr>
<?php endforeach; ?>
<?php if (empty($requests)): ?><tr><td colspan="6" class="text-center text-muted py-4">لا توجد طلبات بعد</td></tr><?php endif; ?>
</tbody>
</table>
</div>
