<div class="page-heading">
  <div>
    <h2><i class="fa-solid fa-gauge-high text-primary me-2"></i><?= __('admin_panel') ?></h2>
    <p>نظرة عامة على مؤشرات الأداء الحيوية، إدارة العمليات والرقابة على المنصة</p>
  </div>
  <div class="d-flex gap-2">
    <a href="<?= url('/admin/reports') ?>" class="btn btn-outline-primary btn-sm"><i class="fa-solid fa-chart-pie me-1"></i> التقارير والتحليلات</a>
    <a href="<?= url('/admin/settings') ?>" class="btn btn-primary btn-sm"><i class="fa-solid fa-gear me-1"></i> إعدادات المنصة</a>
  </div>
</div>

<!-- Row 1: User Metrics -->
<div class="row g-3 mb-4">
  <div class="col-sm-6 col-xl-3">
    <div class="stat-card">
      <div class="d-flex align-items-center justify-content-between mb-2">
        <span class="stat-label"><?= __('total_users') ?></span>
        <div class="stat-icon"><i class="fa-solid fa-users"></i></div>
      </div>
      <div class="stat-value"><?= number_format($overview['total_users']) ?></div>
      <div class="small text-muted"><i class="fa-solid fa-circle-check text-success me-1"></i> إجمالي الحسابات المسجلة</div>
    </div>
  </div>
  <div class="col-sm-6 col-xl-3">
    <div class="stat-card">
      <div class="d-flex align-items-center justify-content-between mb-2">
        <span class="stat-label"><?= __('customers') ?></span>
        <div class="stat-icon"><i class="fa-solid fa-user-tag"></i></div>
      </div>
      <div class="stat-value text-primary"><?= number_format($overview['customers']) ?></div>
      <div class="small text-muted"><i class="fa-solid fa-arrow-trend-up text-primary me-1"></i> عملاء مستفيدون نشطون</div>
    </div>
  </div>
  <div class="col-sm-6 col-xl-3">
    <div class="stat-card">
      <div class="d-flex align-items-center justify-content-between mb-2">
        <span class="stat-label">مقدمو الخدمة</span>
        <div class="stat-icon"><i class="fa-solid fa-handshake-angle"></i></div>
      </div>
      <div class="stat-value text-info"><?= number_format($overview['providers']) ?></div>
      <div class="small text-muted"><i class="fa-solid fa-toolbox text-info me-1"></i> مهنيون وفنيون معتمدون</div>
    </div>
  </div>
  <div class="col-sm-6 col-xl-3">
    <div class="stat-card">
      <div class="d-flex align-items-center justify-content-between mb-2">
        <span class="stat-label">مقدمون موثقون</span>
        <div class="stat-icon"><i class="fa-solid fa-certificate"></i></div>
      </div>
      <div class="stat-value text-success"><?= number_format($overview['verified_providers']) ?></div>
      <div class="small text-muted"><i class="fa-solid fa-shield-halved text-success me-1"></i> هويات ووثائق مكتملة</div>
    </div>
  </div>
</div>

<!-- Row 2: Operation & Financial Metrics -->
<div class="row g-3 mb-4">
  <div class="col-sm-6 col-xl-3">
    <div class="stat-card">
      <div class="d-flex align-items-center justify-content-between mb-2">
        <span class="stat-label">طلبات قيد الانتظار</span>
        <div class="stat-icon" style="background:#fffbeb;color:#d97706"><i class="fa-solid fa-clock-rotate-left"></i></div>
      </div>
      <div class="stat-value" style="color:#d97706"><?= number_format($overview['pending_requests']) ?></div>
      <div class="small text-muted">تحتاج متابعة وإسناد سريع</div>
    </div>
  </div>
  <div class="col-sm-6 col-xl-3">
    <div class="stat-card">
      <div class="d-flex align-items-center justify-content-between mb-2">
        <span class="stat-label">طلبات مكتملة</span>
        <div class="stat-icon" style="background:#ecfdf5;color:#10b981"><i class="fa-solid fa-circle-check"></i></div>
      </div>
      <div class="stat-value text-success"><?= number_format($overview['completed_requests']) ?></div>
      <div class="small text-muted">تم تنفيذها وتقييمها بنجاح</div>
    </div>
  </div>
  <div class="col-sm-6 col-xl-3">
    <div class="stat-card">
      <div class="d-flex align-items-center justify-content-between mb-2">
        <span class="stat-label">شكاوى مفتوحة</span>
        <div class="stat-icon" style="background:#fef2f2;color:#ef4444"><i class="fa-solid fa-triangle-exclamation"></i></div>
      </div>
      <div class="stat-value text-danger"><?= number_format($overview['open_complaints']) ?></div>
      <div class="small text-muted">تتطلب تدخل فريق الدعم</div>
    </div>
  </div>
  <div class="col-sm-6 col-xl-3">
    <div class="stat-card">
      <div class="d-flex align-items-center justify-content-between mb-2">
        <span class="stat-label"><?= __('revenue') ?> (ريال)</span>
        <div class="stat-icon" style="background:#f0fdfa;color:#0f766e"><i class="fa-solid fa-vault"></i></div>
      </div>
      <div class="stat-value text-primary"><?= number_format($overview['revenue']) ?></div>
      <div class="small text-muted">إجمالي الرسوم والعمولات</div>
    </div>
  </div>
</div>

<!-- Charts Section -->
<div class="row g-3 mb-4">
  <div class="col-lg-7">
    <div class="card p-4 h-100">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h6><i class="fa-solid fa-chart-line text-primary me-2"></i>نمو الطلبات الشهرية</h6>
        <span class="badge badge-soft-primary">إحصائيات العام الحالي</span>
      </div>
      <canvas id="chartRequests" style="max-height: 280px;"></canvas>
    </div>
  </div>
  <div class="col-lg-5">
    <div class="card p-4 h-100">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h6><i class="fa-solid fa-chart-column text-warning me-2"></i>الطلبات حسب التخصص</h6>
        <span class="badge badge-soft-warning">الأعلى طلباً</span>
      </div>
      <canvas id="chartCategories" style="max-height: 280px;"></canvas>
    </div>
  </div>
</div>

<!-- Top Providers & Activity Section -->
<div class="row g-3">
  <div class="col-lg-6">
    <div class="card p-4 h-100">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h6><i class="fa-solid fa-medal text-warning me-2"></i>أفضل مقدمي الخدمة أداءً</h6>
        <a href="<?= url('/admin/providers') ?>" class="small text-decoration-none fw-bold">عرض الكل <i class="fa-solid fa-arrow-left"></i></a>
      </div>
      <ul class="list-group list-group-flush">
        <?php if (!empty($topProviders)): ?>
          <?php foreach ($topProviders as $p): ?>
            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
              <div class="d-flex align-items-center gap-2">
                <div class="dash-user-avatar" style="width:34px;height:34px;font-size:0.8rem;"><i class="fa-solid fa-user"></i></div>
                <div>
                  <div class="fw-bold text-dark"><?= e($p['name']) ?></div>
                  <div class="small text-muted"><?= $p['completed_jobs'] ?> خدمة مكتملة</div>
                </div>
              </div>
              <span class="badge badge-soft-warning font-monospace fs-6">
                <?= (float)$p['avg_rating'] ?> <i class="fa-solid fa-star text-warning"></i>
              </span>
            </li>
          <?php endforeach; ?>
        <?php else: ?>
          <li class="list-group-item text-muted text-center py-4">لا توجد بيانات متاحة حالياً</li>
        <?php endif; ?>
      </ul>
    </div>
  </div>

  <div class="col-lg-6">
    <div class="card p-4 h-100">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h6><i class="fa-solid fa-clock-rotate-left text-info me-2"></i>سجل العمليات الأخير (Activity Log)</h6>
        <span class="badge badge-soft-info">محدث لحظياً</span>
      </div>
      <ul class="list-group list-group-flush">
        <?php if (!empty($recentActivity)): ?>
          <?php foreach ($recentActivity as $a): ?>
            <li class="list-group-item d-flex justify-content-between align-items-center px-0 small">
              <div class="d-flex align-items-center gap-2">
                <span class="badge rounded-circle bg-light text-primary p-2"><i class="fa-solid fa-bolt"></i></span>
                <div>
                  <span class="fw-bold text-dark"><?= e($a['user_name'] ?? 'النظام') ?></span>
                  <span class="text-muted">— <?= e($a['action']) ?></span>
                </div>
              </div>
              <span class="text-muted font-monospace" style="font-size:0.75rem;"><?= formatDate($a['created_at']) ?></span>
            </li>
          <?php endforeach; ?>
        <?php else: ?>
          <li class="list-group-item text-muted text-center py-4">لا توجد حركات مسجلة مؤخراً</li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
  const reqCtx = document.getElementById('chartRequests');
  if (reqCtx) {
    new Chart(reqCtx, {
      type: 'line',
      data: {
        labels: <?= json_safe(array_column($requestsByMonth, 'ym')) ?>,
        datasets: [{
          label: 'إجمالي الطلبات',
          data: <?= json_safe(array_map('intval', array_column($requestsByMonth, 'c'))) ?>,
          borderColor: '#0f766e',
          backgroundColor: 'rgba(15, 118, 110, 0.08)',
          fill: true,
          tension: 0.35,
          borderWidth: 2.5,
          pointBackgroundColor: '#0f766e',
          pointRadius: 4
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: { display: false }
        },
        scales: {
          y: { grid: { color: '#f1f5f9' } },
          x: { grid: { display: false } }
        }
      }
    });
  }

  const catCtx = document.getElementById('chartCategories');
  if (catCtx) {
    new Chart(catCtx, {
      type: 'bar',
      data: {
        labels: <?= json_safe(array_column($servicesByCategory, 'name_ar')) ?>,
        datasets: [{
          label: 'عدد الطلبات',
          data: <?= json_safe(array_map('intval', array_column($servicesByCategory, 'c'))) ?>,
          backgroundColor: '#f59e0b',
          borderRadius: 8
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: { display: false }
        },
        scales: {
          y: { grid: { color: '#f1f5f9' } },
          x: { grid: { display: false } }
        }
      }
    });
  }
});
</script>
