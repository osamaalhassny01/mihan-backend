<?php
/**
 * Decision Support Dashboard View - Administrative Decision Intelligence
 */
$kpis = $data['kpis'] ?? [];
$supplyDemand = $data['supply_demand_deficit'] ?? [];
$providerPerf = $data['provider_performance'] ?? [];
$serviceDemand = $data['service_demand'] ?? [];
$complaints = $data['complaints_hotspots'] ?? [];
$aiRecommendations = $data['ai_recommendations'] ?? [];
?>

<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <div>
        <h2 class="fw-bold mb-1" style="color: #0f172a;">
            <i class="fa-solid fa-brain text-teal me-2" style="color: #0f766e;"></i>
            الذكاء الاصطناعي ودعم اتخاذ القرار الإداري
        </h2>
        <p class="text-muted mb-0">
            تحليل ذكي متكامل لبيانات المنصة التشغيلية، واكتشاف فجوات العرض والطلب، ومؤشرات الأداء لتوجيه القرارات الإدارية.
        </p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= url('/admin/ai') ?>" class="btn btn-outline-secondary">
            <i class="fa-solid fa-gear me-1"></i> إعدادات الذكاء الاصطناعي
        </a>
        <a href="<?= url('/admin/decision-support') ?>" class="btn btn-primary" style="background-color: #0f766e; border-color: #0f766e;">
            <i class="fa-solid fa-arrows-rotate me-1"></i> تحديث التحليلات
        </a>
    </div>
</div>

<!-- 1. Executive KPI Grid -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card border-0 shadow-sm p-3 rounded-4 h-100 bg-white">
            <div class="text-muted small mb-1"><i class="fa-solid fa-clipboard-list text-primary me-1"></i> إجمالي الطلبات</div>
            <h3 class="fw-bold mb-0 text-dark"><?= number_format($kpis['total_requests'] ?? 0) ?></h3>
            <div class="small text-muted mt-1">مكتملة: <span class="text-success fw-bold"><?= $kpis['completed_requests'] ?? 0 ?></span></div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card border-0 shadow-sm p-3 rounded-4 h-100 bg-white">
            <div class="text-muted small mb-1"><i class="fa-solid fa-circle-check text-success me-1"></i> نسبة الإنجاز</div>
            <h3 class="fw-bold mb-0 text-success"><?= $kpis['completion_rate'] ?? 0 ?>%</h3>
            <div class="small text-muted mt-1">إلغاء: <span class="text-danger"><?= $kpis['cancellation_rate'] ?? 0 ?>%</span></div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card border-0 shadow-sm p-3 rounded-4 h-100 bg-white">
            <div class="text-muted small mb-1"><i class="fa-solid fa-user-gear text-info me-1"></i> مزودو الخدمة</div>
            <h3 class="fw-bold mb-0 text-dark"><?= number_format($kpis['total_providers'] ?? 0) ?></h3>
            <div class="small text-muted mt-1">موثقون: <span class="text-primary fw-bold"><?= $kpis['verified_providers'] ?? 0 ?></span></div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card border-0 shadow-sm p-3 rounded-4 h-100 bg-white">
            <div class="text-muted small mb-1"><i class="fa-solid fa-star text-warning me-1"></i> متوسط التقييم</div>
            <h3 class="fw-bold mb-0 text-warning"><?= number_format((float)($kpis['average_rating'] ?? 0), 2) ?> <span class="fs-6 text-muted">/ 5</span></h3>
            <div class="small text-muted mt-1">تقييمات العملاء</div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card border-0 shadow-sm p-3 rounded-4 h-100 bg-white">
            <div class="text-muted small mb-1"><i class="fa-solid fa-triangle-exclamation text-danger me-1"></i> الشكاوى المفتوحة</div>
            <h3 class="fw-bold mb-0 <?= ($kpis['open_complaints'] ?? 0) > 0 ? 'text-danger' : 'text-success' ?>">
                <?= number_format($kpis['open_complaints'] ?? 0) ?>
            </h3>
            <div class="small text-muted mt-1">نسبة الحل: <?= $kpis['complaint_resolution_rate'] ?? 100 ?>%</div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card border-0 shadow-sm p-3 rounded-4 h-100 bg-white">
            <div class="text-muted small mb-1"><i class="fa-solid fa-coins text-warning me-1"></i> الإيرادات / العمولات</div>
            <h4 class="fw-bold mb-0 text-dark"><?= number_format($kpis['platform_commission'] ?? 0) ?> <span class="fs-6 text-muted">ريال</span></h4>
            <div class="small text-muted mt-1">إجمالي: <?= number_format($kpis['total_revenue'] ?? 0) ?></div>
        </div>
    </div>
</div>

<!-- 2. AI Executive Action Recommendations -->
<div class="card border-0 shadow-sm rounded-4 p-4 mb-4" style="background: linear-gradient(135deg, #042f2e 0%, #0f766e 100%); color: white;">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div class="d-flex align-items-center gap-2">
            <span class="p-2 bg-white bg-opacity-25 rounded-3"><i class="fa-solid fa-wand-magic-sparkles fs-5"></i></span>
            <h4 class="fw-bold mb-0">توصيات الذكاء الاصطناعي الإدارية المباشرة</h4>
        </div>
        <span class="badge bg-white text-dark px-3 py-2 rounded-pill fw-normal">مبنية على البيانات التشغيلية الحية</span>
    </div>
    <p class="mb-4 text-white-50 small">
        يقوم محرك الذكاء الاصطناعي بربط المتغيرات المتعددة (الطلبات، التغطية، الأداء، الشكاوى) لاستخلاص أولويات القرار للمدير:
    </p>

    <div class="row g-3">
        <?php foreach ($aiRecommendations as $rec): ?>
            <?php
                $borderClass = match($rec['severity'] ?? 'info') {
                    'urgent' => 'border-danger border-start border-4',
                    'warning' => 'border-warning border-start border-4',
                    'opportunity' => 'border-success border-start border-4',
                    default => 'border-info border-start border-4',
                };
                $badgeBg = match($rec['severity'] ?? 'info') {
                    'urgent' => 'bg-danger',
                    'warning' => 'bg-warning text-dark',
                    'opportunity' => 'bg-success',
                    default => 'bg-info text-dark',
                };
                $severityLabel = match($rec['severity'] ?? 'info') {
                    'urgent' => 'عاجل وحرج',
                    'warning' => 'تنبيه تشغيلي',
                    'opportunity' => 'فرصة تحسين',
                    default => 'إشعار',
                };
            ?>
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 border-0 bg-white text-dark rounded-3 shadow-sm p-3 <?= $borderClass ?>">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <span class="badge <?= $badgeBg ?>"><?= $severityLabel ?></span>
                    </div>
                    <h6 class="fw-bold mb-2 text-dark"><?= e($rec['title']) ?></h6>
                    <p class="small text-muted mb-2" style="font-size: 0.85rem; line-height: 1.5;">
                        <i class="fa-solid fa-chart-simple text-secondary me-1"></i> <?= e($rec['explanation']) ?>
                    </p>
                    <div class="mt-auto pt-2 border-top">
                        <div class="small fw-bold text-teal mb-2" style="color: #0f766e;">
                            <i class="fa-solid fa-lightbulb me-1"></i> <?= e($rec['recommendation']) ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- 3. The 4 Practical Business Pillars -->
<div class="row g-4 mb-4">
    <!-- Pillar 1: Geographic Deficit (المثال الأول) -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-4 h-100 bg-white">
            <div class="card-header bg-transparent border-0 pt-4 px-4 pb-2 d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="fw-bold mb-1 text-dark">
                        <i class="fa-solid fa-map-location-dot text-danger me-2"></i>
                        المثال 1: توازن العرض والطلب واكتشاف العجز في المناطق
                    </h5>
                    <p class="text-muted small mb-0">كشف المناطق ذات الطلب العالي مع قلة أو انعدام المهنيين المتاحين</p>
                </div>
            </div>
            <div class="card-body p-4 pt-2">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>المنطقة / المدينة</th>
                                <th class="text-center">الطلبات</th>
                                <th class="text-center">المتاحين</th>
                                <th class="text-center">النسبة</th>
                                <th>الحالة والتوصية</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($supplyDemand)): ?>
                                <tr><td colspan="5" class="text-center text-muted py-3">لا توجد بيانات مناطق مسجلة</td></tr>
                            <?php else: ?>
                                <?php foreach (array_slice($supplyDemand, 0, 6) as $area): ?>
                                    <tr>
                                        <td>
                                            <div class="fw-bold"><?= e($area['name_ar']) ?></div>
                                            <small class="text-muted"><?= e($area['city']) ?></small>
                                        </td>
                                        <td class="text-center fw-bold"><?= $area['requests_count'] ?></td>
                                        <td class="text-center">
                                            <span class="badge <?= $area['available_providers'] > 0 ? 'bg-success' : 'bg-danger' ?>">
                                                <?= $area['available_providers'] ?> متاح
                                            </span>
                                        </td>
                                        <td class="text-center text-muted small">
                                            <?= $area['demand_to_supply_ratio'] ?> : 1
                                        </td>
                                        <td>
                                            <span class="badge bg-<?= $area['badge_color'] ?> mb-1"><?= $area['status_label'] ?></span>
                                            <div class="small text-muted" style="font-size: 0.75rem; max-width: 180px;">
                                                <?= e($area['recommendation']) ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Pillar 2: Provider Performance Matrix (المثال الثاني) -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-4 h-100 bg-white">
            <div class="card-header bg-transparent border-0 pt-4 px-4 pb-2 d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="fw-bold mb-1 text-dark">
                        <i class="fa-solid fa-users-gear text-primary me-2"></i>
                        المثال 2: تحليل أداء مقدمي الخدمات والمصفوفة الإدارية
                    </h5>
                    <p class="text-muted small mb-0">فرز أصحاب الأداء المرتفع مقارنة بمن يحتاجون إلى متابعة وتدقيق</p>
                </div>
            </div>
            <div class="card-body p-4 pt-2">
                <!-- Top Performers -->
                <h6 class="fw-bold text-success mb-2"><i class="fa-solid fa-medal me-1"></i> أصحاب الأداء المتميز (أولوية الإسناد)</h6>
                <div class="table-responsive mb-3">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>اسم المهني والتخصص</th>
                                <th class="text-center">التقييم</th>
                                <th class="text-center">الأعمال</th>
                                <th class="text-center">الإنجاز</th>
                                <th>الإجراء الإداري</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($providerPerf['top_performers'])): ?>
                                <tr><td colspan="5" class="text-center text-muted py-2">لا يوجد بيانات كافية</td></tr>
                            <?php else: ?>
                                <?php foreach (array_slice($providerPerf['top_performers'], 0, 3) as $tp): ?>
                                    <tr>
                                        <td>
                                            <div class="fw-bold"><?= e($tp['name']) ?></div>
                                            <small class="text-muted"><?= e($tp['profession']) ?></small>
                                        </td>
                                        <td class="text-center text-warning fw-bold"><i class="fa-solid fa-star"></i> <?= $tp['avg_rating'] ?></td>
                                        <td class="text-center"><?= $tp['completed_jobs'] ?></td>
                                        <td class="text-center text-success fw-bold"><?= $tp['completion_rate'] ?>%</td>
                                        <td class="small text-success"><?= e($tp['admin_action']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Needs Attention -->
                <h6 class="fw-bold text-danger mb-2"><i class="fa-solid fa-triangle-exclamation me-1"></i> يحتاجون إلى متابعة إدارية وتنبيه</h6>
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>اسم المهني</th>
                                <th class="text-center">الشكاوى</th>
                                <th class="text-center">الرفض/الإلغاء</th>
                                <th>سبب المتابعة</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($providerPerf['needs_attention'])): ?>
                                <tr><td colspan="4" class="text-center text-muted py-2">لا يوجد مهنيين بحاجة لمتابعة حرجة حالياً</td></tr>
                            <?php else: ?>
                                <?php foreach (array_slice($providerPerf['needs_attention'], 0, 3) as $na): ?>
                                    <tr>
                                        <td>
                                            <div class="fw-bold"><?= e($na['name']) ?></div>
                                            <small class="text-muted"><?= e($na['profession']) ?></small>
                                        </td>
                                        <td class="text-center"><span class="badge bg-danger"><?= $na['complaints_count'] ?></span></td>
                                        <td class="text-center text-danger fw-bold"><?= $na['rejection_rate'] ?>%</td>
                                        <td class="small text-danger"><?= e($na['admin_action']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Pillar 3: Most & Least Demanded Services (المثال الثالث) -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-4 h-100 bg-white">
            <div class="card-header bg-transparent border-0 pt-4 px-4 pb-2">
                <h5 class="fw-bold mb-1 text-dark">
                    <i class="fa-solid fa-chart-pie text-success me-2"></i>
                    المثال 3: الخدمات الأكثر والأقل طلباً والتخصصات المطلوبة
                </h5>
                <p class="text-muted small mb-0">معرفة التخصصات التي تحتاج إلى استقطاب مزيد من المهنيين</p>
            </div>
            <div class="card-body p-4 pt-2">
                <!-- Specialization Deficit Alert -->
                <?php if (!empty($serviceDemand['specialization_deficit'])): ?>
                    <div class="alert alert-warning border-0 rounded-3 p-3 mb-3">
                        <div class="fw-bold mb-1"><i class="fa-solid fa-bullhorn me-1"></i> تخصصات تعاني من نقص في مقدمي الخدمة:</div>
                        <ul class="mb-0 small ps-3">
                            <?php foreach ($serviceDemand['specialization_deficit'] as $sd): ?>
                                <li><strong><?= e($sd['name_ar']) ?>:</strong> <?= e($sd['recommendation']) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <div class="row g-3">
                    <div class="col-sm-6">
                        <h6 class="fw-bold text-success mb-2"><i class="fa-solid fa-arrow-trend-up me-1"></i> الأكثر طلباً</h6>
                        <ul class="list-group list-group-flush small">
                            <?php foreach ($serviceDemand['most_demanded'] as $md): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                                    <div>
                                        <div class="fw-bold"><?= e($md['name_ar']) ?></div>
                                        <span class="text-muted"><?= e($md['category_name']) ?></span>
                                    </div>
                                    <span class="badge bg-success rounded-pill"><?= $md['requests_count'] ?> طلب</span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <div class="col-sm-6">
                        <h6 class="fw-bold text-secondary mb-2"><i class="fa-solid fa-arrow-trend-down me-1"></i> الأقل طلباً (فرص تنشيط)</h6>
                        <ul class="list-group list-group-flush small">
                            <?php foreach ($serviceDemand['least_demanded'] as $ld): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                                    <div>
                                        <div class="fw-bold"><?= e($ld['name_ar']) ?></div>
                                        <span class="text-muted"><?= e($ld['category_name']) ?></span>
                                    </div>
                                    <span class="badge bg-light text-muted border"><?= $ld['requests_count'] ?> طلب</span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pillar 4: Complaints Hotspots (المثال الرابع) -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-4 h-100 bg-white">
            <div class="card-header bg-transparent border-0 pt-4 px-4 pb-2">
                <h5 class="fw-bold mb-1 text-dark">
                    <i class="fa-solid fa-shield-halved text-warning me-2"></i>
                    المثال 4: متابعة الشكاوى ومؤشرات الجودة ومناطق الاحتكاك
                </h5>
                <p class="text-muted small mb-0">اكتشاف بؤر النزاع والشكاوى للتدخل الإداري المسبق وحماية الجودة</p>
            </div>
            <div class="card-body p-4 pt-2">
                <div class="row g-3">
                    <div class="col-sm-6">
                        <h6 class="fw-bold text-dark mb-2"><i class="fa-solid fa-wrench me-1"></i> أعلى الخدمات تسجيلاً للشكاوى</h6>
                        <ul class="list-group list-group-flush small">
                            <?php if (empty($complaints['hotspots_by_service'])): ?>
                                <li class="list-group-item px-0 py-2 text-muted">لا توجد شكاوى مسجلة على الخدمات</li>
                            <?php else: ?>
                                <?php foreach ($complaints['hotspots_by_service'] as $hs): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                                        <span><?= e($hs['service_name']) ?></span>
                                        <span class="badge bg-danger rounded-pill"><?= $hs['complaints_count'] ?> شكاوى</span>
                                    </li>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </ul>
                    </div>

                    <div class="col-sm-6">
                        <h6 class="fw-bold text-dark mb-2"><i class="fa-solid fa-location-dot me-1"></i> أعلى المناطق تسجيلاً للشكاوى</h6>
                        <ul class="list-group list-group-flush small">
                            <?php if (empty($complaints['hotspots_by_location'])): ?>
                                <li class="list-group-item px-0 py-2 text-muted">لا توجد شكاوى مرتبطة بمناطق معينة</li>
                            <?php else: ?>
                                <?php foreach ($complaints['hotspots_by_location'] as $hl): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                                        <span><?= e($hl['location_name']) ?></span>
                                        <span class="badge bg-warning text-dark rounded-pill"><?= $hl['complaints_count'] ?> شكاوى</span>
                                    </li>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>

                <div class="mt-4 pt-3 border-top">
                    <a href="<?= url('/admin/complaints') ?>" class="btn btn-outline-danger btn-sm w-100">
                        <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> الانتقال لإدارة الشكاوى وحلها بالكامل
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 4. Interactive AI Decision Advisor -->
<div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white">
    <div class="d-flex align-items-center gap-2 mb-3">
        <span class="p-2 bg-light rounded-3 text-teal"><i class="fa-solid fa-comments fs-5"></i></span>
        <div>
            <h5 class="fw-bold mb-0 text-dark">استشارة المساعد الإداري الذكي</h5>
            <small class="text-muted">اطرح أي سؤال حول بيانات المنصة للحصول على تقرير واستشارة فورية</small>
        </div>
    </div>

    <form method="POST" action="<?= url('/admin/decision-support') ?>">
        <?= csrf_field() ?>
        <div class="mb-3">
            <textarea name="question" class="form-control rounded-3" rows="3" 
                      placeholder="مثال: ما هي أولوياتي التشغيلية لهذا الأسبوع؟ أو: أي المناطق تحتاج استقطاب فنيين مكيفات فوراً؟"><?= e($question ?? '') ?></textarea>
        </div>
        <div class="d-flex flex-wrap gap-2 mb-3">
            <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill" onclick="document.querySelector('textarea[name=question]').value = this.innerText;">
                ما هي أكثر المناطق التي تعاني من عجز في مقدمي الخدمات؟
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill" onclick="document.querySelector('textarea[name=question]').value = this.innerText;">
                حلل أداء المهنيين وحدد من يحتاج إلى متابعة أو إنذار.
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill" onclick="document.querySelector('textarea[name=question]').value = this.innerText;">
                ما الخدمات الأكثر طلباً والتي تحتاج إلى زيادة الاستقطاب؟
            </button>
        </div>
        <button class="btn btn-primary px-4 py-2" style="background-color: #0f766e; border-color: #0f766e;">
            <i class="fa-solid fa-paper-plane me-1"></i> تحليل واستشارة الذكاء الاصطناعي
        </button>
    </form>

    <?php if ($answer !== null): ?>
        <div class="mt-4 p-4 rounded-3 border bg-light">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="badge bg-secondary">
                    محرك الذكاء الاصطناعي: <?= e($meta['provider'] ?? 'local') ?>
                    <?= !empty($meta['fallback']) ? ' (احتياطي ذكي)' : '' ?>
                </span>
                <span class="small text-muted"><i class="fa-regular fa-clock me-1"></i> استجابة فورية</span>
            </div>
            <div style="white-space: pre-wrap; line-height: 1.8; color: #1e293b;" class="fs-6">
                <?= e($answer) ?>
            </div>
        </div>
    <?php endif; ?>
</div>
