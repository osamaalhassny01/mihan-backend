<div class="d-flex justify-content-between mb-3">
<h3>الطلبات</h3>
<form method="GET" class="d-flex gap-2">
<select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
<option value="">كل الحالات</option>
<?php foreach (['new','pending','accepted','in_progress','completed','cancelled','rejected'] as $s): ?>
<option value="<?= $s ?>" <?= $status===$s?'selected':'' ?>><?= e($s) ?></option>
<?php endforeach; ?>
</select>
</form>
</div>
<div class="table-responsive">
<table class="table table-hover bg-white rounded shadow-sm">
<thead><tr><th>رقم الطلب</th><th><?= __('status') ?></th><th>التاريخ</th><th></th></tr></thead>
<tbody>
<?php foreach ($requests as $r): ?>
<tr><td><?= e($r['request_number']) ?></td><td><?= statusBadge($r['status']) ?></td><td><?= formatDate($r['created_at']) ?></td>
<td><a href="<?= url('/admin/requests/' . $r['id']) ?>" class="btn btn-sm btn-outline-primary">عرض</a></td></tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
