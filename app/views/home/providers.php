<?php $isCustomer = Auth::check() && Auth::role() === 'customer'; ?>

<!-- ============ MODERN TOP FILTER BAR ============ -->
<div class="mb-4 p-4 rounded-4 shadow-sm" style="background: linear-gradient(135deg, #072a27 0%, #0d544e 60%, #115e59 100%); border: 1px solid rgba(94, 234, 212, 0.3); color: #ffffff;">
  <div class="row align-items-center g-3">
    <div class="col-lg-6">
      <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill mb-2" style="background: rgba(255, 255, 255, 0.12); border: 1px solid rgba(94, 234, 212, 0.35);">
        <i class="fa-solid fa-users-gear text-warning"></i>
        <span class="small fw-bold text-white">دليل المهنيين والفنيين المعتمدين</span>
      </div>
      <h3 class="fw-bold mb-1" style="color: #ffffff;">
        <?php if (!empty($filters['keyword'])): ?>
          نتائج البحث عن: "<span class="text-warning"><?= e($filters['keyword']) ?></span>"
        <?php else: ?>
          نخبة المهنيين الموثقين
        <?php endif; ?>
      </h3>
      <p class="mb-0 small" style="color: #a7f3d0; font-weight: 600;">
        تم العثور على <span class="badge bg-warning text-dark font-monospace"><?= count($providers) ?></span> مهني جاهز لخدمتك
      </p>
    </div>

    <!-- Quick Search Field in Top Bar -->
    <div class="col-lg-6">
      <form method="GET" action="<?= url('/providers') ?>" class="d-flex gap-2">
        <div class="position-relative flex-grow-1">
          <input type="text" name="keyword" class="form-control form-control-lg border-0 ps-4" style="border-radius: 14px; font-size: 0.95rem; font-family: 'Cairo', sans-serif;" placeholder="ابحث باسم الفني أو الخدمة..." value="<?= e($filters['keyword'] ?? '') ?>">
        </div>
        <button type="submit" class="btn btn-warning px-4 fw-bold" style="border-radius: 14px; color: #0b3734 !important;">
          <i class="fa-solid fa-magnifying-glass me-1"></i> بحث
        </button>
      </form>
    </div>
  </div>
</div>

<div class="row g-4">
  <!-- ============ SIDEBAR FILTERS (FLOATING CARD) ============ -->
  <div class="col-lg-3">
    <div class="card p-4 border-0 shadow-sm" style="border-radius: 20px; background: #ffffff; border: 1px solid #d4e7e4 !important; position: sticky; top: 90px; z-index: 10;">
      <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
        <h6 class="mb-0 fw-bold" style="color: #0b3734;"><i class="fa-solid fa-sliders text-primary me-2"></i>تصفية متقدمة</h6>
        <?php if (array_filter($filters)): ?>
          <a href="<?= url('/providers') ?>" class="small text-danger text-decoration-none fw-bold"><i class="fa-solid fa-rotate-left"></i> تصفير</a>
        <?php endif; ?>
      </div>

      <form method="GET" action="<?= url('/providers') ?>" id="filterForm">
        <?php if (!empty($filters['keyword'])): ?>
          <input type="hidden" name="keyword" value="<?= e($filters['keyword']) ?>">
        <?php endif; ?>

        <!-- Category -->
        <div class="mb-3">
          <label class="form-label small fw-bold" style="color: #0b3734;">التخصص المهني</label>
          <select name="category_id" class="form-select" style="border-radius: 10px;" onchange="this.form.submit()">
            <option value="">جميع التخصصات</option>
            <?php foreach ($categories as $c): ?>
              <option value="<?= $c['id'] ?>" <?= ($filters['category_id'] ?? '') == $c['id'] ? 'selected' : '' ?>><?= e(localized($c['name_ar'], $c['name_en'] ?? null)) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Location -->
        <div class="mb-3">
          <label class="form-label small fw-bold" style="color: #0b3734;">المنطقة الجغرافية</label>
          <select name="location_id" class="form-select" style="border-radius: 10px;" onchange="this.form.submit()">
            <option value="">جميع المناطق</option>
            <?php foreach ($locations as $l): ?>
              <option value="<?= $l['id'] ?>" <?= ($filters['location_id'] ?? '') == $l['id'] ? 'selected' : '' ?>><?= e(localized($l['name_ar'], $l['name_en'] ?? null)) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Rating -->
        <div class="mb-3">
          <label class="form-label small fw-bold" style="color: #0b3734;">الحد الأدنى للتقييم</label>
          <select name="min_rating" class="form-select" style="border-radius: 10px;" onchange="this.form.submit()">
            <option value="">أي تقييم</option>
            <?php foreach ([4,3,2,1] as $r): ?>
              <option value="<?= $r ?>" <?= ($filters['min_rating'] ?? '') == $r ? 'selected' : '' ?>><?= $r ?>+ نجوم فأعلى ★</option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Checkboxes -->
        <div class="p-3 rounded-3 mb-3" style="background: #f0fdfa; border: 1px solid #ccfbf1;">
          <div class="form-check mb-2">
            <input class="form-check-input" type="checkbox" id="verifiedOnly" name="verified_only" value="1" <?= !empty($filters['verified_only']) ? 'checked' : '' ?> onchange="this.form.submit()">
            <label class="form-check-label small fw-bold text-dark" for="verifiedOnly">
              <i class="fa-solid fa-circle-check text-success me-1"></i> مهنيون موثقون فقط
            </label>
          </div>
          <div class="form-check">
            <input class="form-check-input" type="checkbox" id="availableOnly" name="available_only" value="1" <?= !empty($filters['available_only']) ? 'checked' : '' ?> onchange="this.form.submit()">
            <label class="form-check-label small fw-bold text-dark" for="availableOnly">
              <span class="badge rounded-circle bg-success p-1 me-1 d-inline-block" style="width: 8px; height: 8px;"></span> متاحون للعمل الآن
            </label>
          </div>
        </div>

        <button type="submit" class="btn btn-primary w-100 py-2 fw-bold" style="border-radius: 12px; background: linear-gradient(135deg, #0f766e 0%, #115e59 100%); border: none;">
          <i class="fa-solid fa-filter me-1"></i> تطبيق الفلترة
        </button>
      </form>
    </div>
  </div>

  <!-- ============ PROVIDERS CARDS GRID ============ -->
  <div class="col-lg-9">
    <?php if (empty($providers)): ?>
      <div class="card p-5 text-center border-0 shadow-sm" style="border-radius: 24px; background: #ffffff;">
        <div style="width: 80px; height: 80px; border-radius: 24px; background: #fef2f2; color: #ef4444; display: inline-flex; align-items: center; justify-content: center; font-size: 2.2rem; margin: 0 auto 1.5rem;">
          <i class="fa-solid fa-user-slash"></i>
        </div>
        <h4 class="fw-bold mb-2" style="color: #0b3734;">لم نجد مقدم خدمة يطابق شروط الفرز</h4>
        <p class="text-muted mb-4">جرّب تقليل شروط الفلترة أو استخدم محرك الذكاء الاصطناعي لاقتراح البديل.</p>
        <div class="d-flex justify-content-center gap-2">
          <a href="<?= url('/providers') ?>" class="btn btn-primary px-4 py-2" style="border-radius: 12px;">عرض جميع المهنيين</a>
          <?php if (!empty($isCustomer)): ?>
            <a href="<?= url('/customer/requests/create') ?>" class="btn btn-warning px-4 py-2" style="border-radius: 12px; color: #0b3734 !important;">
              <i class="fa-solid fa-wand-magic-sparkles me-1"></i> التحليل الذكي للطلب
            </a>
          <?php endif; ?>
        </div>
      </div>
    <?php else: ?>
      <div class="row g-4">
        <?php foreach ($providers as $idx => $p): ?>
          <div class="col-md-6 provider-stagger-card" style="animation-delay: <?= $idx * 0.08 ?>s;">
            <div class="provider-card p-4 h-100 d-flex flex-column" style="background: #ffffff; border: 1.5px solid #d4e7e4; border-radius: 22px; box-shadow: 0 8px 24px rgba(15,118,110,0.06); transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);">
              
              <!-- Header info with avatar -->
              <div class="d-flex align-items-center gap-3 mb-3">
                <div class="provider-avatar" style="width: 62px; height: 62px; border-radius: 18px; background: #ecfdf5; color: #0f766e; display: flex; align-items: center; justify-content: center; font-size: 1.6rem; border: 2px solid #5eead4;">
                  <?php if (!empty($p['profile_photo'])): ?>
                    <img src="<?= e($p['profile_photo']) ?>" alt="<?= e($p['name']) ?>" style="width:100%;height:100%;object-fit:cover;border-radius:16px;">
                  <?php else: ?>
                    <i class="fa-solid fa-user-tie"></i>
                  <?php endif; ?>
                </div>

                <div class="min-w-0 flex-grow-1">
                  <div class="d-flex align-items-center gap-2 mb-1">
                    <span class="fw-bold text-truncate fs-5" style="color: #0b3734;"><?= e($p['name']) ?></span>
                    <?php if ($p['is_verified']): ?>
                      <span class="badge" style="background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0; border-radius: 8px;">
                        <i class="fa-solid fa-circle-check"></i> موثّق
                      </span>
                    <?php endif; ?>
                  </div>
                  <div class="small fw-semibold" style="color: #0f766e;">
                    <?= e($p['profession'] ?? 'فني خدمات معتمد') ?>
                    <?php if (!empty($p['category_name_ar'])): ?>
                      <span class="text-muted fw-normal">&bull; <?= e(localized($p['category_name_ar'], $p['category_name_en'] ?? null)) ?></span>
                    <?php endif; ?>
                  </div>
                </div>
              </div>

              <!-- Ratings & Availability Badges -->
              <div class="d-flex align-items-center justify-content-between mb-3 p-2 rounded-3" style="background: #f8fafc;">
                <div class="rating-stars">
                  <?php for ($i = 1; $i <= 5; $i++): ?>
                    <i class="fa-solid fa-star<?= $i <= round($p['avg_rating']) ? ' text-warning' : ' text-muted opacity-25' ?>"></i>
                  <?php endfor; ?>
                  <span class="fw-bold small ms-1" style="color: #0b3734;">(<?= number_format((float)$p['avg_rating'], 1) ?>)</span>
                  <small class="text-muted ms-1">(<?= (int)$p['rating_count'] ?> تقييم)</small>
                </div>

                <span class="badge <?= $p['is_available'] ? 'bg-success' : 'bg-secondary' ?> px-2 py-1" style="border-radius: 6px; font-size: 0.76rem;">
                  <?= $p['is_available'] ? '🟢 متاح الآن' : '⚪ غير متاح' ?>
                </span>
              </div>

              <!-- Experience & Bio -->
              <p class="small mb-4" style="color: #527976; line-height: 1.6;">
                <i class="fa-solid fa-briefcase text-primary me-1"></i> خبرة عملية: <strong><?= (int)$p['experience_years'] ?> سنوات</strong>
                <?php if (!empty($p['completed_jobs'])): ?>
                  &bull; <i class="fa-solid fa-check-double text-success me-1"></i> أنجز <strong><?= (int)$p['completed_jobs'] ?> طلباً</strong> بنجاح
                <?php endif; ?>
              </p>

              <!-- Action Buttons -->
              <div class="d-flex gap-2 mt-auto">
                <a href="<?= url('/providers/' . $p['id']) ?>" class="btn btn-outline-primary btn-sm flex-grow-1 py-2 fw-bold" style="border-radius: 12px;">
                  الملف المهني <i class="fa-solid fa-arrow-left ms-1"></i>
                </a>
                <?php if ($isCustomer): ?>
                  <a href="<?= url('/customer/requests/create?provider_id=' . $p['id']) ?>" class="btn btn-primary btn-sm flex-grow-1 py-2 fw-bold" style="border-radius: 12px; background: linear-gradient(135deg, #0f766e 0%, #115e59 100%); border: none;">
                    طلب الخدمة الآن
                  </a>
                <?php elseif (!Auth::check()): ?>
                  <a href="<?= url('/login?redirect=' . urlencode('/customer/requests/create?provider_id=' . $p['id'])) ?>" class="btn btn-primary btn-sm flex-grow-1 py-2 fw-bold" style="border-radius: 12px; background: linear-gradient(135deg, #0f766e 0%, #115e59 100%); border: none;">
                    طلب الخدمة
                  </a>
                <?php endif; ?>
              </div>

            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</div>

<style>
/* Stagger entrance animation */
.provider-stagger-card {
  animation: cardFadeIn 0.55s cubic-bezier(0.16, 1, 0.3, 1) both;
}

@keyframes cardFadeIn {
  from {
    opacity: 0;
    transform: translateY(24px) scale(0.97);
  }
  to {
    opacity: 1;
    transform: translateY(0) scale(1);
  }
}

.provider-card:hover {
  transform: translateY(-7px) !important;
  box-shadow: 0 18px 40px rgba(15, 118, 110, 0.16) !important;
  border-color: #5eead4 !important;
}

.provider-card:hover .provider-avatar {
  transform: scale(1.08) rotate(3deg);
  transition: all 0.3s ease;
}
</style>
