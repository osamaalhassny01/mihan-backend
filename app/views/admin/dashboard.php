<h3 class="mb-4"><?= __('admin_panel') ?></h3>
<div class="row g-3 mb-4">
  <div class="col-md-3"><div class="card stat-card p-3"><div class="text-muted small"><?= __('total_users') ?></div><h4><?= $overview['total_users'] ?></h4></div></div>
  <div class="col-md-3"><div class="card stat-card p-3"><div class="text-muted small"><?= __('customers') ?></div><h4><?= $overview['customers'] ?></h4></div></div>
  <div class="col-md-3"><div class="card stat-card p-3"><div class="text-muted small">مقدمو الخدمة</div><h4><?= $overview['providers'] ?></h4></div></div>
  <div class="col-md-3"><div class="card stat-card p-3"><div class="text-muted small">موثّقون</div><h4><?= $overview['verified_providers'] ?></h4></div></div>
</div>
<div class="row g-3 mb-4">
  <div class="col-md-3"><div class="card stat-card p-3"><div class="text-muted small">طلبات قيد الانتظار</div><h4><?= $overview['pending_requests'] ?></h4></div></div>
  <div class="col-md-3"><div class="card stat-card p-3"><div class="text-muted small">طلبات مكتملة</div><h4><?= $overview['completed_requests'] ?></h4></div></div>
  <div class="col-md-3"><div class="card stat-card p-3"><div class="text-muted small">شكاوى مفتوحة</div><h4><?= $overview['open_complaints'] ?></h4></div></div>
  <div class="col-md-3"><div class="card stat-card p-3"><div class="text-muted small"><?= __('revenue') ?></div><h4><?= number_format($overview['revenue']) ?></h4></div></div>
</div>

<div class="row g-3 mb-4">
  <div class="col-md-6"><div class="card p-3"><h6>الطلبات حسب الشهر</h6><canvas id="chartRequests"></canvas></div></div>
  <div class="col-md-6"><div class="card p-3"><h6>الخدمات حسب التصنيف</h6><canvas id="chartCategories"></canvas></div></div>
</div>

<div class="row g-3">
  <div class="col-md-6">
    <div class="card p-3">
      <h6>أفضل مقدمي الخدمة</h6>
      <ul class="list-group list-group-flush">
        <?php foreach ($topProviders as $p): ?>
          <li class="list-group-item d-flex justify-content-between"><?= e($p['name']) ?> <span><?= (float)$p['avg_rating'] ?> ★ / <?= $p['completed_jobs'] ?> عملية</span></li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>
  <div class="col-md-6">
    <div class="card p-3">
      <h6>آخر العمليات (Activity Log)</h6>
      <ul class="list-group list-group-flush">
        <?php foreach ($recentActivity as $a): ?>
          <li class="list-group-item d-flex justify-content-between small">
            <span><?= e($a['user_name'] ?? 'النظام') ?> — <?= e($a['action']) ?></span>
            <span class="text-muted"><?= formatDate($a['created_at']) ?></span>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
new Chart(document.getElementById('chartRequests'), {
  type: 'line',
  data: {
    labels: <?= json_safe(array_column($requestsByMonth, 'ym')) ?>,
    datasets: [{ label: 'الطلبات', data: <?= json_safe(array_map('intval', array_column($requestsByMonth, 'c'))) ?>, borderColor: '#0f766e', tension: .3 }]
  }
});
new Chart(document.getElementById('chartCategories'), {
  type: 'bar',
  data: {
    labels: <?= json_safe(array_column($servicesByCategory, 'name_ar')) ?>,
    datasets: [{ label: 'عدد الطلبات', data: <?= json_safe(array_map('intval', array_column($servicesByCategory, 'c'))) ?>, backgroundColor: '#f59e0b' }]
  }
});
</script>
