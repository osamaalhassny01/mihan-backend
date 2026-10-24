<div class="d-flex justify-content-between align-items-center mb-3">
  <h3><?= __('complaints') ?></h3>
  <a href="<?= url('/customer/complaints/create') ?>" class="btn btn-primary btn-sm"><i class="fa-solid fa-plus"></i> شكوى جديدة</a>
</div>
<div class="table-responsive">
<table class="table table-hover bg-white rounded shadow-sm">
<thead><tr><th>رقم الشكوى</th><th>الموضوع</th><th><?= __('status') ?></th><th>التاريخ</th><th></th></tr></thead>
<tbody>
<?php foreach ($complaints as $c): ?>
<tr>
<td><?= e($c['complaint_number']) ?></td>
<td><?= e($c['subject']) ?></td>
<td><?= statusBadge($c['status']) ?></td>
<td><?= formatDate($c['created_at']) ?></td>
<td><a href="<?= url('/customer/complaints/' . $c['id']) ?>" class="btn btn-sm btn-outline-primary">عرض</a></td>
</tr>
<?php endforeach; ?>
<?php if (empty($complaints)): ?><tr><td colspan="5" class="text-center text-muted py-4">لا توجد شكاوى</td></tr><?php endif; ?>
</tbody>
</table>
</div>
