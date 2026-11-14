<!-- Executive Dashboard Header -->
<div class="page-heading mb-4 pb-3" style="border-bottom: 2px solid #d4e7e4;">
  <div class="d-flex align-items-center gap-3">
    <div style="width: 54px; height: 54px; border-radius: 18px; background: linear-gradient(135deg, #0b3734 0%, #0f766e 100%); color: #5eead4; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; box-shadow: 0 8px 20px rgba(15,118,110,0.2);">
      <i class="fa-solid fa-gauge-high"></i>
    </div>
    <div>
      <h2 class="fw-bold mb-1" style="color: #0b3734; font-size: 1.75rem;">لوحة قيادة إدارة المنصة</h2>
      <p class="mb-0 small" style="color: #527976; font-weight: 600;">مركز الرقابة الموحد &bull; رصد المؤشرات، العمليات، وحركة الإيرادات والطلبات لحظياً</p>
    </div>
  </div>
  <div class="d-flex gap-2 mt-3 mt-md-0">
    <a href="<?= url('/admin/reports') ?>" class="btn btn-outline-primary btn-sm px-3 py-2 fw-bold" style="border-radius: 12px;">
      <i class="fa-solid fa-chart-pie me-1"></i> التقارير المتقدمة
    </a>
    <a href="<?= url('/admin/settings') ?>" class="btn btn-primary btn-sm px-3 py-2 fw-bold" style="border-radius: 12px; background: linear-gradient(135deg, #0f766e 0%, #115e59 100%); border: none;">
      <i class="fa-solid fa-gear me-1"></i> إعدادات النظام
    </a>
  </div>
</div>

<!-- ============ ROW 1: USER & MEMBERSHIP KPIS ============ -->
<div class="row g-3 mb-4">
  <div class="col-sm-6 col-xl-3">
    <div class="stat-card" style="border-radius: 20px; border: 1.5px solid #d4e7e4; background: #ffffff; padding: 1.5rem; box-shadow: 0 6px 20px rgba(15,118,110,0.05); transition: all 0.3s ease;">
      <div class="d-flex align-items-center justify-content-between mb-2">
        <span class="stat-label" style="color: #527976; font-weight: 700;">إجمالي المستخدمين</span>
        <div class="stat-icon" style="background: #f0fdfa; color: #0f766e; border-radius: 14px; width: 48px; height: 48px; display: flex; align-items: center; justify-content: center; font-size: 1.3rem;">
          <i class="fa-solid fa-users"></i>
        </div>
      </div>
      <div class="stat-value" style="color: #0b3734; font-size: 2.2rem; font-weight: 900; line-height: 1.2;">
        <?= number_format($overview['total_users']) ?>
      </div>
      <div class="small fw-semibold mt-1" style="color: #059669;">
        <i class="fa-solid fa-circle-check me-1"></i> حسابات مسجلة ونشطة
      </div>
    </div>
  </div>

  <div class="col-sm-6 col-xl-3">
    <div class="stat-card" style="border-radius: 20px; border: 1.5px solid #d4e7e4; background: #ffffff; padding: 1.5rem; box-shadow: 0 6px 20px rgba(15,118,110,0.05); transition: all 0.3s ease;">
      <div class="d-flex align-items-center justify-content-between mb-2">
        <span class="stat-label" style="color: #527976; font-weight: 700;">العملاء المستفيدون</span>
        <div class="stat-icon" style="background: #e0f2fe; color: #0284c7; border-radius: 14px; width: 48px; height: 48px; display: flex; align-items: center; justify-content: center; font-size: 1.3rem;">
          <i class="fa-solid fa-user-tag"></i>
        </div>
      </div>
      <div class="stat-value" style="color: #0284c7; font-size: 2.2rem; font-weight: 900; line-height: 1.2;">
        <?= number_format($overview['customers']) ?>
      </div>
      <div class="small fw-semibold mt-1" style="color: #0284c7;">
        <i class="fa-solid fa-arrow-trend-up me-1"></i> عملاء نشطون في طلب الخدمة
      </div>
    </div>
  </div>

  <div class="col-sm-6 col-xl-3">
    <div class="stat-card" style="border-radius: 20px; border: 1.5px solid #d4e7e4; background: #ffffff; padding: 1.5rem; box-shadow: 0 6px 20px rgba(15,118,110,0.05); transition: all 0.3s ease;">
      <div class="d-flex align-items-center justify-content-between mb-2">
        <span class="stat-label" style="color: #527976; font-weight: 700;">مقدمو الخدمة</span>
        <div class="stat-icon" style="background: #ecfdf5; color: #059669; border-radius: 14px; width: 48px; height: 48px; display: flex; align-items: center; justify-content: center; font-size: 1.3rem;">
          <i class="fa-solid fa-handshake-angle"></i>
        </div>
      </div>
      <div class="stat-value" style="color: #059669; font-size: 2.2rem; font-weight: 900; line-height: 1.2;">
        <?= number_format($overview['providers']) ?>
      </div>
      <div class="small fw-semibold mt-1" style="color: #059669;">
        <i class="fa-solid fa-toolbox me-1"></i> فنيون ومهنيون مسجلون
      </div>
    </div>
  </div>

  <div class="col-sm-6 col-xl-3">
    <div class="stat-card" style="border-radius: 20px; border: 1.5px solid #d4e7e4; background: #ffffff; padding: 1.5rem; box-shadow: 0 6px 20px rgba(15,118,110,0.05); transition: all 0.3s ease;">
      <div class="d-flex align-items-center justify-content-between mb-2">
        <span class="stat-label" style="color: #527976; font-weight: 700;">المهنيون الموثقون</span>
        <div class="stat-icon" style="background: #fef3c7; color: #d97706; border-radius: 14px; width: 48px; height: 48px; display: flex; align-items: center; justify-content: center; font-size: 1.3rem;">
          <i class="fa-solid fa-certificate"></i>
        </div>
      </div>
      <div class="stat-value" style="color: #d97706; font-size: 2.2rem; font-weight: 900; line-height: 1.2;">
        <?= number_format($overview['verified_providers']) ?>
      </div>
      <div class="small fw-semibold mt-1" style="color: #d97706;">
        <i class="fa-solid fa-shield-halved me-1"></i> وثائق وهويات معتمدة
      </div>
    </div>
  </div>
</div>

<!-- ============ ROW 2: OPERATIONS & FINANCIAL KPIS ============ -->
<div class="row g-3 mb-4">
  <div class="col-sm-6 col-xl-3">
    <a href="<?= url('/admin/requests?status=pending') ?>" class="text-decoration-none">
      <div class="stat-card" style="border-radius: 20px; border: 1.5px solid #d4e7e4; background: #ffffff; padding: 1.5rem; box-shadow: 0 6px 20px rgba(15,118,110,0.05); position: relative; overflow: hidden; transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-3px)'" onmouseout="this.style.transform='translateY(0)'">
        <div style="position: absolute; top: 0; inset-inline: 0; height: 3.5px; background: #f59e0b;"></div>
        <div class="d-flex align-items-center justify-content-between mb-2">
          <span class="stat-label" style="color: #527976; font-weight: 700;">طلبات قيد الانتظار</span>
          <div class="stat-icon" style="background: #fffbeb; color: #d97706; border-radius: 14px; width: 48px; height: 48px; display: flex; align-items: center; justify-content: center; font-size: 1.3rem;">
            <i class="fa-solid fa-clock-rotate-left"></i>
          </div>
        </div>
        <div class="stat-value" style="color: #d97706; font-size: 2.2rem; font-weight: 900; line-height: 1.2;">
          <?= number_format($overview['pending_requests']) ?>
        </div>
        <div class="small text-muted d-flex justify-content-between align-items-center mt-1">
          <span>تحتاج متابعة وتوجيه</span>
          <i class="fa-solid fa-arrow-left text-warning"></i>
        </div>
      </div>
    </a>
  </div>

  <div class="col-sm-6 col-xl-3">
    <div class="stat-card" style="border-radius: 20px; border: 1.5px solid #d4e7e4; background: #ffffff; padding: 1.5rem; box-shadow: 0 6px 20px rgba(15,118,110,0.05); position: relative; overflow: hidden;">
      <div style="position: absolute; top: 0; inset-inline: 0; height: 3.5px; background: #10b981;"></div>
      <div class="d-flex align-items-center justify-content-between mb-2">
        <span class="stat-label" style="color: #527976; font-weight: 700;">طلبات منجزة بنجاح</span>
        <div class="stat-icon" style="background: #ecfdf5; color: #10b981; border-radius: 14px; width: 48px; height: 48px; display: flex; align-items: center; justify-content: center; font-size: 1.3rem;">
          <i class="fa-solid fa-circle-check"></i>
        </div>
      </div>
      <div class="stat-value text-success" style="font-size: 2.2rem; font-weight: 900; line-height: 1.2;">
        <?= number_format($overview['completed_requests']) ?>
      </div>
      <div class="small text-muted">تم تنفيذها وتقييمها بالكامل</div>
    </div>
  </div>

  <div class="col-sm-6 col-xl-3">
    <div class="stat-card" style="border-radius: 20px; border: 1.5px solid #d4e7e4; background: #ffffff; padding: 1.5rem; box-shadow: 0 6px 20px rgba(15,118,110,0.05); position: relative; overflow: hidden;">
      <div style="position: absolute; top: 0; inset-inline: 0; height: 3.5px; background: #ef4444;"></div>
      <div class="d-flex align-items-center justify-content-between mb-2">
        <span class="stat-label" style="color: #527976; font-weight: 700;">شكاوى قيد الحل</span>
        <div class="stat-icon" style="background: #fef2f2; color: #ef4444; border-radius: 14px; width: 48px; height: 48px; display: flex; align-items: center; justify-content: center; font-size: 1.3rem;">
          <i class="fa-solid fa-triangle-exclamation"></i>
        </div>
      </div>
      <div class="stat-value text-danger" style="font-size: 2.2rem; font-weight: 900; line-height: 1.2;">
        <?= number_format($overview['open_complaints']) ?>
      </div>
      <div class="small text-muted">تتطلب تدخلاً من فريق الإدارة</div>
    </div>
  </div>

  <div class="col-sm-6 col-xl-3">
    <div class="stat-card" style="border-radius: 20px; border: 1.5px solid #d4e7e4; background: #ffffff; padding: 1.5rem; box-shadow: 0 6px 20px rgba(15,118,110,0.05); position: relative; overflow: hidden;">
      <div style="position: absolute; top: 0; inset-inline: 0; height: 3.5px; background: #0f766e;"></div>
      <div class="d-flex align-items-center justify-content-between mb-2">
        <span class="stat-label" style="color: #527976; font-weight: 700;">الإيرادات المحققة (ريال)</span>
        <div class="stat-icon" style="background: #f0fdfa; color: #0f766e; border-radius: 14px; width: 48px; height: 48px; display: flex; align-items: center; justify-content: center; font-size: 1.3rem;">
          <i class="fa-solid fa-vault"></i>
        </div>
      </div>
      <div class="stat-value" style="color: #0b3734; font-size: 2.2rem; font-weight: 900; line-height: 1.2;">
        <?= number_format($overview['revenue']) ?>
      </div>
      <div class="small text-muted">عمولات ورسوم العمليات المكتملة</div>
    </div>
  </div>
</div>

<!-- ============ RECENT SERVICE REQUESTS TABLE ============ -->
<div class="card border-0 shadow-sm mb-4" style="border-radius: 22px; border: 1px solid #d4e7e4 !important; background: #ffffff;">
  <div class="card-header bg-white border-0 pt-4 px-4 pb-2 d-flex justify-content-between align-items-center">
    <div class="d-flex align-items-center gap-2">
      <div style="width: 42px; height: 42px; border-radius: 12px; background: #ecfdf5; color: #0f766e; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; border: 1.5px solid #a7f3d0;">
        <i class="fa-solid fa-clipboard-list"></i>
      </div>
      <div>
        <h5 class="fw-bold mb-0" style="color: #0b3734;">أحدث طلبات الخدمة</h5>
        <small class="text-muted">متابعة فورية للطلبات الواردة من التطبيق والويب وتوجيهها للمهنيين</small>
      </div>
    </div>
    <div class="d-flex align-items-center gap-2">
      <a href="<?= url('/admin/requests?status=pending') ?>" class="btn btn-sm btn-outline-warning fw-bold px-3 py-1" style="border-radius: 10px;">
        <i class="fa-solid fa-hourglass-start me-1"></i> بانتظار التوجيه
      </a>
      <a href="<?= url('/admin/requests') ?>" class="btn btn-sm btn-primary fw-bold px-3 py-1" style="border-radius: 10px; background: #0f766e; border-color: #0f766e;">
        عرض كل الطلبات <i class="fa-solid fa-arrow-left ms-1"></i>
      </a>
    </div>
  </div>
  <div class="card-body px-0 pb-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead style="background: #f8fafc; border-bottom: 2px solid #e2e8f0;">
          <tr>
            <th class="ps-4 py-3" style="color: #0b3734; font-weight: 800; font-size: 0.85rem;">رقم الطلب</th>
            <th class="py-3" style="color: #0b3734; font-weight: 800; font-size: 0.85rem;">الخدمة والعميل</th>
            <th class="py-3" style="color: #0b3734; font-weight: 800; font-size: 0.85rem;">مقدم الخدمة</th>
            <th class="py-3" style="color: #0b3734; font-weight: 800; font-size: 0.85rem;">السعر</th>
            <th class="py-3" style="color: #0b3734; font-weight: 800; font-size: 0.85rem;">الحالة</th>
            <th class="text-end pe-4 py-3" style="color: #0b3734; font-weight: 800; font-size: 0.85rem;">الإجراء</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($recentRequests)): ?>
            <tr>
              <td colspan="6" class="text-center py-4 text-muted">لا توجد طلبات مسجلة حتى الآن</td>
            </tr>
          <?php else: ?>
            <?php foreach ($recentRequests as $r): ?>
              <tr>
                <td class="ps-4">
                  <div class="fw-bold font-monospace fs-6" style="color: #0b3734;">#<?= e($r['request_number']) ?></div>
                  <div class="small text-muted font-monospace"><i class="fa-solid fa-calendar me-1"></i><?= formatDate($r['created_at']) ?></div>
                </td>
                <td>
                  <div class="fw-bold text-dark"><?= e($r['service_name_ar'] ?? 'خدمة فنية') ?></div>
                  <div class="small text-secondary"><i class="fa-solid fa-user me-1"></i><?= e($r['customer_name'] ?? 'عميل') ?></div>
                </td>
                <td>
                  <?php if (!empty($r['provider_name'])): ?>
                    <div class="fw-bold text-success"><i class="fa-solid fa-user-check me-1"></i><?= e($r['provider_name']) ?></div>
                    <div class="small text-muted"><?= e($r['provider_profession'] ?? 'فني مهني') ?></div>
                  <?php else: ?>
                    <span class="badge bg-warning text-dark"><i class="fa-solid fa-hourglass-start me-1"></i>بانتظار التوجيه</span>
                  <?php endif; ?>
                </td>
                <td>
                  <?php if (!empty($r['final_price'])): ?>
                    <span class="fw-bold text-success"><?= formatCurrency($r['final_price']) ?></span>
                  <?php elseif (!empty($r['display_price'])): ?>
                    <span class="fw-bold text-primary"><?= formatCurrency($r['display_price']) ?></span>
                  <?php else: ?>
                    <span class="text-muted small">غير محدد</span>
                  <?php endif; ?>
                </td>
                <td>
                  <?= statusBadge($r['status']) ?>
                </td>
                <td class="text-end pe-4">
                  <a href="<?= url('/admin/requests/' . $r['id']) ?>" class="btn btn-sm btn-outline-primary px-3 py-1 fw-bold" style="border-radius: 8px;">
                    <i class="fa-solid fa-route me-1"></i> متابعة وتوجيه
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php if (!empty($pendingApplications)): ?>
<!-- ============ PENDING PROVIDER APPLICATIONS BANNER ============ -->
<div class="card border-0 shadow-sm mb-4" style="border-radius: 22px; border: 1.5px solid #fde68a !important; background: #fffdf5;">
  <div class="card-header bg-transparent border-0 pt-4 px-4 pb-2 d-flex justify-content-between align-items-center">
    <div class="d-flex align-items-center gap-2">
      <div style="width: 42px; height: 42px; border-radius: 12px; background: #fffbeb; color: #d97706; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; border: 1.5px solid #fde68a;">
        <i class="fa-solid fa-user-check"></i>
      </div>
      <div>
        <h5 class="fw-bold mb-0" style="color: #92400e;">طلبات انضمام المهنيين الجدد (بانتظار الاعتماد)</h5>
        <small class="text-muted">مهنيون سجلوا رغبتهم بالانضمام لمنصة مِهَن وبانتظار موافقة الإدارة لتفعيل حساباتهم</small>
      </div>
    </div>
    <a href="<?= url('/admin/provider-applications?status=pending') ?>" class="btn btn-sm btn-warning fw-bold px-3 py-1" style="border-radius: 10px;">
      عرض جميع طلبات المهنيين (<?= count($pendingApplications) ?>)
    </a>
  </div>
  <div class="card-body px-0 pb-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead style="background: #fefce8; border-bottom: 2px solid #fef08a;">
          <tr>
            <th class="ps-4 py-3" style="color: #854d0e; font-weight: 800; font-size: 0.85rem;">اسم المهني</th>
            <th class="py-3" style="color: #854d0e; font-weight: 800; font-size: 0.85rem;">التخصص والخبرة</th>
            <th class="py-3" style="color: #854d0e; font-weight: 800; font-size: 0.85rem;">المدينة / المنطقة</th>
            <th class="py-3" style="color: #854d0e; font-weight: 800; font-size: 0.85rem;">رقم الهاتف</th>
            <th class="py-3" style="color: #854d0e; font-weight: 800; font-size: 0.85rem;">الشهادة / الوثيقة</th>
            <th class="text-end pe-4 py-3" style="color: #854d0e; font-weight: 800; font-size: 0.85rem;">قرار الإدارة</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($pendingApplications as $app): ?>
            <tr>
              <td class="ps-4">
                <div class="fw-bold fs-6" style="color: #0b3734;"><?= e($app['user_name']) ?></div>
                <div class="small text-muted font-monospace"><i class="fa-solid fa-clock me-1"></i><?= formatDate($app['created_at']) ?></div>
              </td>
              <td>
                <span class="badge" style="background: #ecfdf5; color: #065f46; font-size: 0.85rem;"><?= e($app['profession']) ?></span>
                <span class="small text-muted ms-1">(<?= (int)$app['years_exp'] ?> سنوات خبرة)</span>
              </td>
              <td>
                <span class="fw-semibold text-secondary"><i class="fa-solid fa-location-dot me-1 text-danger"></i><?= e($app['city'] ?: 'غير محدد') ?></span>
              </td>
              <td>
                <span class="font-monospace fw-bold text-dark"><i class="fa-solid fa-phone me-1 text-muted"></i><?= e($app['user_phone'] ?: '-') ?></span>
              </td>
              <td>
                <?php if (!empty($app['certificate_url'])): ?>
                  <a href="<?= e($app['certificate_url']) ?>" target="_blank" class="btn btn-xs btn-outline-info px-2 py-1" style="border-radius: 6px; font-size: 0.8rem;">
                    <i class="fa-solid fa-file-image me-1"></i> عرض الوثيقة
                  </a>
                <?php else: ?>
                  <span class="text-muted small">لا توجد وثيقة</span>
                <?php endif; ?>
              </td>
              <td class="text-end pe-4">
                <div class="d-inline-flex gap-2">
                  <form method="POST" action="<?= url('/admin/provider-applications/' . $app['id'] . '/approve') ?>" onsubmit="return confirm('هل أنت متأكد من قبول واعتماد المهني <?= e($app['user_name']) ?>؟')">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-sm btn-success fw-bold px-3 py-1" style="border-radius: 8px;">
                      <i class="fa-solid fa-check me-1"></i> قبول وتفعيل
                    </button>
                  </form>
                  <a href="<?= url('/admin/provider-applications/' . $app['id']) ?>" class="btn btn-sm btn-outline-secondary px-2 py-1" style="border-radius: 8px;">
                    تفاصيل
                  </a>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php endif; ?>

<div class="row g-4 mb-4">
  <div class="col-lg-7">
    <div class="card p-4 border-0 shadow-sm h-100" style="border-radius: 22px; border: 1px solid #d4e7e4 !important; background: #ffffff;">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
          <h5 class="fw-bold mb-1" style="color: #0b3734;"><i class="fa-solid fa-chart-line text-primary me-2"></i>منحنى نمو الطلبات الشهري</h5>
          <small class="text-muted">معدل تنفيذ وحجم الطلبات على مدار العام</small>
        </div>
        <span class="badge" style="background: #f0fdfa; color: #0f766e; border: 1px solid #ccfbf1; font-weight: 700; padding: 0.4rem 0.8rem; border-radius: 8px;">
          إحصائيات حية
        </span>
      </div>
      <canvas id="chartRequests" style="max-height: 290px;"></canvas>
    </div>
  </div>

  <div class="col-lg-5">
    <div class="card p-4 border-0 shadow-sm h-100" style="border-radius: 22px; border: 1px solid #d4e7e4 !important; background: #ffffff;">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
          <h5 class="fw-bold mb-1" style="color: #0b3734;"><i class="fa-solid fa-chart-column text-warning me-2"></i>الطلبات حسب التخصص</h5>
          <small class="text-muted">الخدمات الأكثر رواجاً واستهلاكاً</small>
        </div>
        <span class="badge" style="background: #fffbeb; color: #d97706; border: 1px solid #fef3c7; font-weight: 700; padding: 0.4rem 0.8rem; border-radius: 8px;">
          توزيع نسبي
        </span>
      </div>
      <canvas id="chartCategories" style="max-height: 290px;"></canvas>
    </div>
  </div>
</div>

<!-- ============ BOTTOM SECTION: TOP PERFORMERS & REALTIME AUDIT LOG ============ -->
<div class="row g-4">
  <!-- Top Providers -->
  <div class="col-lg-6">
    <div class="card p-4 border-0 shadow-sm h-100" style="border-radius: 22px; border: 1px solid #d4e7e4 !important; background: #ffffff;">
      <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
        <div>
          <h5 class="fw-bold mb-0" style="color: #0b3734;"><i class="fa-solid fa-award text-warning me-2"></i>أنشط وأفضل مقدمي الخدمة</h5>
          <small class="text-muted">ترتيب تلقائي بحسب أعلى التقييمات والإنجاز</small>
        </div>
        <a href="<?= url('/admin/providers') ?>" class="btn btn-outline-primary btn-sm px-3 py-1 fw-bold" style="border-radius: 10px;">
          عرض الكل <i class="fa-solid fa-arrow-left ms-1"></i>
        </a>
      </div>

      <ul class="list-group list-group-flush">
        <?php if (!empty($topProviders)): ?>
          <?php foreach ($topProviders as $p): ?>
            <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-3 border-bottom" style="border-color: #f1f5f9;">
              <div class="d-flex align-items-center gap-3">
                <div style="width: 44px; height: 44px; border-radius: 14px; background: #ecfdf5; color: #0f766e; display: flex; align-items: center; justify-content: center; font-size: 1.15rem; border: 1.5px solid #a7f3d0;">
                  <i class="fa-solid fa-user-tie"></i>
                </div>
                <div>
                  <div class="fw-bold fs-6" style="color: #0b3734;"><?= e($p['name']) ?></div>
                  <small style="color: #527976;"><i class="fa-solid fa-circle-check text-success me-1"></i> <?= $p['completed_jobs'] ?> خدمة مكتملة</small>
                </div>
              </div>
              <span class="badge" style="background: #fffbeb; color: #b45309; border: 1px solid #fde68a; font-size: 0.95rem; font-weight: 800; border-radius: 10px; padding: 0.4rem 0.8rem;">
                <?= (float)$p['avg_rating'] ?> <i class="fa-solid fa-star text-warning"></i>
              </span>
            </li>
          <?php endforeach; ?>
        <?php else: ?>
          <li class="list-group-item text-muted text-center py-4">لا توجد سجلات كافية حالياً</li>
        <?php endif; ?>
      </ul>
    </div>
  </div>

  <!-- Activity Audit Trail -->
  <div class="col-lg-6">
    <div class="card p-4 border-0 shadow-sm h-100" style="border-radius: 22px; border: 1px solid #d4e7e4 !important; background: #ffffff;">
      <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
        <div>
          <h5 class="fw-bold mb-0" style="color: #0b3734;"><i class="fa-solid fa-bolt-lightning text-primary me-2"></i>سجل العمليات والرقابة الحية</h5>
          <small class="text-muted">مراقبة الحركات والأنشطة الإدارية لحظة بلحظة</small>
        </div>
        <span class="badge" style="background: #ecfdf5; color: #059669; border-radius: 8px; padding: 0.35rem 0.7rem; font-weight: 700;">
          🟢 مباشر
        </span>
      </div>

      <ul class="list-group list-group-flush">
        <?php if (!empty($recentActivity)): ?>
          <?php foreach ($recentActivity as $a): ?>
            <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-3 border-bottom" style="border-color: #f1f5f9;">
              <div class="d-flex align-items-center gap-3">
                <span class="rounded-circle p-2" style="background: #f0fdfa; color: #0f766e; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; font-size: 0.9rem;">
                  <i class="fa-solid fa-clock-rotate-left"></i>
                </span>
                <div>
                  <span class="fw-bold" style="color: #0b3734;"><?= e($a['user_name'] ?? 'النظام') ?></span>
                  <span class="small d-block" style="color: #527976;"><?= e($a['action']) ?></span>
                </div>
              </div>
              <span class="text-muted font-monospace small" style="background: #f8fafc; padding: 0.2rem 0.5rem; border-radius: 6px;">
                <?= formatDate($a['created_at']) ?>
              </span>
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
          borderWidth: 3,
          pointBackgroundColor: '#0f766e',
          pointBorderColor: '#ffffff',
          pointBorderWidth: 2,
          pointRadius: 5
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
          backgroundColor: '#0f766e',
          borderRadius: 10
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
