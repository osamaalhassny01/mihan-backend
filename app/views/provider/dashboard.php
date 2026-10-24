<h3 class="mb-4"><?= __('welcome') ?>، <?= e(Auth::name()) ?> 👋</h3>
<?php if (!$profile['is_verified']): ?>
<div class="alert alert-warning"><i class="fa-solid fa-triangle-exclamation"></i> حسابك غير موثّق بعد. أكمل ملفك المهني لزيادة فرصك في استقبال الطلبات.</div>
<?php endif; ?>
<div class="row g-3 mb-4">
  <div class="col-md-3"><div class="card stat-card p-3"><div class="text-muted small">إجمالي الطلبات</div><h4><?= $stats['total'] ?></h4></div></div>
  <div class="col-md-3"><div class="card stat-card p-3"><div class="text-muted small">قيد الانتظار</div><h4><?= $stats['pending'] ?></h4></div></div>
  <div class="col-md-3"><div class="card stat-card p-3"><div class="text-muted small">مقبولة/جارية</div><h4><?= $stats['accepted'] ?></h4></div></div>
  <div class="col-md-3"><div class="card stat-card p-3"><div class="text-muted small">مكتملة</div><h4><?= $stats['completed'] ?></h4></div></div>
</div>
<div class="row g-3 mb-4">
  <div class="col-md-3"><div class="card p-3"><div class="text-muted small">متوسط التقييم</div><h4><?= (float)$profile['avg_rating'] ?> / 5</h4></div></div>
  <div class="col-md-3"><div class="card p-3"><div class="text-muted small">قيمة الفواتير</div><h4><?= number_format((float)$finance['gross'],2) ?> <?= e(setting('currency','ريال')) ?></h4></div></div>
  <div class="col-md-3"><div class="card p-3"><div class="text-muted small">المبلغ المستلم فعليًا</div><h4 class="text-success"><?= number_format((float)$finance['received_amount'],2) ?> <?= e(setting('currency','ريال')) ?></h4></div></div>
  <div class="col-md-3"><div class="card p-3"><div class="text-muted small">الرصيد المتبقي من العملاء</div><h4 class="text-warning"><?= number_format((float)$finance['pending_net'],2) ?> <?= e(setting('currency','ريال')) ?></h4></div></div>
</div>
<div class="d-flex justify-content-between align-items-center mb-3"><h5 class="mb-0">أحدث الطلبات</h5><a href="<?= url('/provider/finance') ?>" class="btn btn-sm btn-outline-success"><i class="fa-solid fa-wallet"></i> فتح المالية</a></div>
<div class="table-responsive">
<table class="table table-hover bg-white rounded shadow-sm">
<thead><tr><th>رقم الطلب</th><th>العميل</th><th>الخدمة</th><th><?= __('status') ?></th><th></th></tr></thead>
<tbody>
<?php foreach ($requests as $r): ?>
<tr><td><?= e($r['request_number']) ?></td><td><?= e($r['customer_name']) ?></td><td><?= e(localized($r['service_name_ar'], $r['service_name_en'] ?? null)) ?></td>
<td><?= statusBadge($r['status']) ?></td><td><a href="<?= url('/provider/requests/' . $r['id']) ?>" class="btn btn-sm btn-outline-primary">عرض</a></td></tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
