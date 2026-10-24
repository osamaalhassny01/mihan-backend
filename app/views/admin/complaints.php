<div class="d-flex justify-content-between mb-3">
<h3>الشكاوى</h3>
<form method="GET" class="d-flex gap-2">
<select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
<option value="">كل الحالات</option>
<?php foreach (['open','in_review','resolved','rejected','closed'] as $s): ?>
<option value="<?= $s ?>" <?= $status===$s?'selected':'' ?>><?= e($s) ?></option>
<?php endforeach; ?>
</select>
</form>
</div>
<div class="table-responsive">
<table class="table table-hover bg-white rounded shadow-sm">
<thead><tr><th>الرقم</th><th>الموضوع</th><th><?= __('status') ?></th><th>التاريخ</th><th></th></tr></thead>
<tbody>
<?php foreach ($complaints as $c): ?>
<tr><td><?= e($c['complaint_number']) ?></td><td><?= e($c['subject']) ?></td><td><?= statusBadge($c['status']) ?></td><td><?= formatDate($c['created_at']) ?></td>
<td><a href="<?= url('/admin/complaints/' . $c['id']) ?>" class="btn btn-sm btn-outline-primary">عرض</a></td></tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
