<h3 class="mb-4"><?= __('welcome') ?>، <?= e(Auth::name()) ?> 👋</h3>
<div class="row g-3 mb-4">
  <div class="col-md-4">
    <div class="card stat-card p-3 d-flex flex-row align-items-center">
      <div class="stat-icon bg-warning-subtle text-warning me-3"><i class="fa-solid fa-hourglass-half"></i></div>
      <div><div class="text-muted small">الطلبات النشطة</div><h4 class="mb-0"><?= $stats['active'] ?></h4></div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card stat-card p-3 d-flex flex-row align-items-center">
      <div class="stat-icon bg-success-subtle text-success me-3"><i class="fa-solid fa-check"></i></div>
      <div><div class="text-muted small">مكتملة</div><h4 class="mb-0"><?= $stats['completed'] ?></h4></div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card stat-card p-3 d-flex flex-row align-items-center">
      <div class="stat-icon bg-secondary-subtle text-secondary me-3"><i class="fa-solid fa-ban"></i></div>
      <div><div class="text-muted small">ملغاة</div><h4 class="mb-0"><?= $stats['cancelled'] ?></h4></div>
    </div>
  </div>
</div>

<div class="d-flex justify-content-between align-items-center mb-3">
  <h5 class="mb-0">آخر الطلبات</h5>
  <a href="<?= url('/customer/finance') ?>" class="btn btn-outline-success btn-sm me-2"><i class="fa-solid fa-file-invoice-dollar"></i> المالية والفواتير</a>
  <a href="<?= url('/customer/requests/create') ?>" class="btn btn-primary btn-sm"><i class="fa-solid fa-plus"></i> <?= __('new_request') ?></a>
</div>
<div class="table-responsive">
<table class="table table-hover bg-white rounded shadow-sm">
<thead><tr><th>رقم الطلب</th><th><?= __('services') ?></th><th>مقدم الخدمة</th><th><?= __('status') ?></th><th></th></tr></thead>
<tbody>
<?php foreach ($requests as $r): ?>
<tr>
<td><?= e($r['request_number']) ?></td>
<td><?= e(localized($r['service_name_ar'], $r['service_name_en'] ?? null)) ?></td>
<td><?= e($r['provider_name'] ?? '—') ?></td>
<td><?= statusBadge($r['status']) ?></td>
<td><a href="<?= url('/customer/requests/' . $r['id']) ?>" class="btn btn-sm btn-outline-primary">عرض</a></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
