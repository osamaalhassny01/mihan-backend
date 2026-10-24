<h3 class="mb-4">الطلبات الواردة</h3>
<div class="table-responsive">
<table class="table table-hover bg-white rounded shadow-sm">
<thead><tr><th>رقم الطلب</th><th>العميل</th><th>الخدمة</th><th><?= __('status') ?></th><th>التاريخ</th><th></th></tr></thead>
<tbody>
<?php foreach ($requests as $r): ?>
<tr>
<td><?= e($r['request_number']) ?></td><td><?= e($r['customer_name']) ?></td><td><?= e(localized($r['service_name_ar'], $r['service_name_en'] ?? null)) ?></td>
<td><?= statusBadge($r['status']) ?></td><td><?= formatDate($r['created_at']) ?></td>
<td><a href="<?= url('/provider/requests/' . $r['id']) ?>" class="btn btn-sm btn-outline-primary">عرض</a></td>
</tr>
<?php endforeach; ?>
<?php if (empty($requests)): ?><tr><td colspan="6" class="text-center text-muted py-4">لا توجد طلبات حاليًا</td></tr><?php endif; ?>
</tbody>
</table>
</div>
