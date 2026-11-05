<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <div>
        <h2 class="h4 mb-1">إدارة مقدمي الخدمة</h2>
        <p class="text-muted mb-0">عرض وتصفية المهنيين ومتابعة حالة التوثيق والاعتماد</p>
    </div>
    <div class="d-flex gap-2">
        <a href="/admin/provider-applications" class="btn btn-outline-primary btn-sm">
            <i class="fa-solid fa-user-check me-1"></i> طلبات الانضمام الجديدة
        </a>
    </div>
</div>

<!-- Stats Counter Cards -->
<div class="row g-3 mb-4">
    <div class="col-sm-4">
        <div class="card border-0 shadow-sm p-3 bg-white rounded-3 border-start border-4 border-primary">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted small">إجمالي مقدمي الخدمة</div>
                    <div class="fs-4 fw-bold text-dark mt-1"><?= number_format($stats['total'] ?? 0) ?></div>
                </div>
                <div class="p-3 bg-primary-subtle text-primary rounded-circle">
                    <i class="fa-solid fa-users-gear fs-5"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="card border-0 shadow-sm p-3 bg-white rounded-3 border-start border-4 border-success">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted small">الموثقين والمعتمدين</div>
                    <div class="fs-4 fw-bold text-success mt-1"><?= number_format($stats['verified'] ?? 0) ?></div>
                </div>
                <div class="p-3 bg-success-subtle text-success rounded-circle">
                    <i class="fa-solid fa-certificate fs-5"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="card border-0 shadow-sm p-3 bg-white rounded-3 border-start border-4 border-warning">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="text-muted small">غير الموثقين / بانتظار الاعتماد</div>
                    <div class="fs-4 fw-bold text-warning mt-1"><?= number_format($stats['unverified'] ?? 0) ?></div>
                </div>
                <div class="p-3 bg-warning-subtle text-warning rounded-circle">
                    <i class="fa-solid fa-user-clock fs-5"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Advanced Filter Card -->
<div class="card border-0 shadow-sm mb-4 bg-white rounded-3">
    <div class="card-body p-3">
        <form method="GET" action="/admin/providers" class="row g-2 align-items-center">
            <!-- Search Keyword -->
            <div class="col-md-3">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-search text-muted"></i></span>
                    <input type="text" name="q" class="form-control border-start-0" placeholder="بحث بالاسم أو التخصص..." value="<?= htmlspecialchars($search ?? '') ?>">
                </div>
            </div>

            <!-- Verification Status Filter -->
            <div class="col-md-3">
                <select name="status" class="form-select form-select-sm">
                    <option value="">جميع الحالات (الموثق وغير الموثق)</option>
                    <option value="verified" <?= ($status ?? '') === 'verified' ? 'selected' : '' ?>>الموثقين فقط ✅</option>
                    <option value="unverified" <?= ($status ?? '') === 'unverified' ? 'selected' : '' ?>>غير الموثقين فقط ⏳</option>
                </select>
            </div>

            <!-- Service/Profession Filter -->
            <div class="col-md-2">
                <select name="service_id" class="form-select form-select-sm">
                    <option value="0">كل المهن والخدمات</option>
                    <?php foreach ($services ?? [] as $s): ?>
                        <option value="<?= $s['id'] ?>" <?= ((int)($serviceId ?? 0) === (int)$s['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($s['name_ar']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Location Filter -->
            <div class="col-md-2">
                <select name="location_id" class="form-select form-select-sm">
                    <option value="0">كل المناطق</option>
                    <?php foreach ($locations ?? [] as $l): ?>
                        <option value="<?= $l['id'] ?>" <?= ((int)($locationId ?? 0) === (int)$l['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($l['name_ar']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Buttons -->
            <div class="col-md-2 d-flex gap-1">
                <button type="submit" class="btn btn-primary btn-sm flex-fill">
                    <i class="fa-solid fa-filter me-1"></i> تصفية
                </button>
                <a href="/admin/providers" class="btn btn-light btn-sm" title="إعادة الضبط">
                    <i class="fa-solid fa-rotate-right"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Providers Table Card -->
<div class="card border-0 shadow-sm bg-white rounded-3">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>مقدم الخدمة</th>
                        <th>المهنة / التخصص</th>
                        <th>التقييم</th>
                        <th>الطلبات المنجزة</th>
                        <th>حالة التوثيق</th>
                        <th>التوافر</th>
                        <th class="text-end">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($providers)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="fa-solid fa-user-slash fs-1 d-block mb-2 text-secondary"></i>
                                لا يوجد مقدمو خدمة مطابقين لمعايير التصفية المحددة
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($providers as $p): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="rounded-circle bg-light d-flex align-items-center justify-content-center text-primary fw-bold" style="width: 38px; height: 38px;">
                                            <?= mb_substr($p['name'] ?? 'م', 0, 1) ?>
                                        </div>
                                        <div>
                                            <div class="fw-bold"><?= htmlspecialchars($p['name']) ?></div>
                                            <small class="text-muted">#<?= $p['id'] ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border"><?= htmlspecialchars($p['profession'] ?? '-') ?></span>
                                    <?php if (!empty($p['specialization'])): ?>
                                        <div class="small text-muted"><?= htmlspecialchars($p['specialization']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="text-warning fw-bold small">
                                        <i class="fa-solid fa-star"></i> <?= (float)$p['avg_rating'] ?>
                                        <span class="text-muted fw-normal">(<?= (int)$p['rating_count'] ?>)</span>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-secondary-subtle text-secondary"><?= (int)$p['completed_jobs'] ?> طلب</span>
                                </td>
                                <td>
                                    <?php if (!empty($p['is_verified'])): ?>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                            <i class="fa-solid fa-circle-check me-1"></i> موثق ومعتمد
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-1">
                                            <i class="fa-solid fa-clock me-1"></i> غير موثق
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($p['is_available'])): ?>
                                        <span class="badge bg-success-subtle text-success">متاح للعمل</span>
                                    <?php else: ?>
                                        <span class="badge bg-light text-muted border">غير متاح</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        <?php if (!empty($p['is_verified'])): ?>
                                            <form method="POST" action="<?= url('/admin/providers/' . $p['id'] . '/unverify') ?>" class="d-inline" onsubmit="return confirm('هل أنت متأكد من إلغاء توثيق هذا الحساب؟')">
                                                <?= csrf_field() ?>
                                                <button class="btn btn-outline-secondary" title="إلغاء التوثيق">
                                                    <i class="fa-solid fa-ban"></i>
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <form method="POST" action="<?= url('/admin/providers/' . $p['id'] . '/verify') ?>" class="d-inline" onsubmit="return confirm('هل أنت متأكد من توثيق واعتماد هذا الحساب؟')">
                                                <?= csrf_field() ?>
                                                <button class="btn btn-outline-success" title="اعتماد وتوثيق">
                                                    <i class="fa-solid fa-check"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>

                                        <a href="<?= url('/providers/' . $p['id']) ?>" target="_blank" class="btn btn-outline-primary" title="معاينة الملف">
                                            <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                        </a>
                                        <a href="<?= url('/admin/providers/' . $p['id'] . '/evaluation') ?>" class="btn btn-outline-warning" title="التقييم الداخلي">
                                            <i class="fa-solid fa-award"></i>
                                        </a>
                                        <a href="<?= url('/admin/ai/provider/' . $p['id']) ?>" class="btn btn-outline-info" title="تحليل الذكاء الاصطناعي">
                                            <i class="fa-solid fa-brain"></i>
                                        </a>
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
