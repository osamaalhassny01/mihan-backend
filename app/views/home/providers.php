<?php $isCustomer = Auth::check() && Auth::role() === 'customer'; ?>

<!-- ============ SEARCH HEADER ============ -->
<div class="search-header mb-4">
  <?php if (!empty($filters['keyword'])): ?>
    <h3 class="fw-bold mb-1">نتائج البحث عن: "<?= e($filters['keyword']) ?>"</h3>
  <?php else: ?>
    <h3 class="fw-bold mb-1"><?= __('providers') ?></h3>
  <?php endif; ?>
  <p class="section-subtitle mb-0"><?= count($providers) ?> نتيجة مطابقة</p>
</div>

<div class="row g-4">
  <!-- ============ FILTERS ============ -->
  <div class="col-lg-3">
    <div class="surface-card p-3 mb-3">
      <h6 class="mb-3 fw-bold"><i class="fa-solid fa-filter text-primary"></i> تصفية النتائج</h6>
      <form method="GET" action="<?= url('/providers') ?>">
        <div class="mb-3">
          <label class="form-label small fw-semibold">كلمة بحث</label>
          <input type="text" name="keyword" class="form-control form-control-sm" placeholder="مثال: صيانة مكيف" value="<?= e($filters['keyword'] ?? '') ?>">
        </div>
        <div class="mb-3">
          <label class="form-label small fw-semibold"><?= __('category') ?></label>
          <select name="category_id" class="form-select form-select-sm">
            <option value="">الكل</option>
            <?php foreach ($categories as $c): ?>
              <option value="<?= $c['id'] ?>" <?= ($filters['category_id'] ?? '') == $c['id'] ? 'selected' : '' ?>><?= e(localized($c['name_ar'], $c['name_en'] ?? null)) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="mb-3">
          <label class="form-label small fw-semibold"><?= __('location') ?></label>
          <select name="location_id" class="form-select form-select-sm">
            <option value="">الكل</option>
            <?php foreach ($locations as $l): ?>
              <option value="<?= $l['id'] ?>" <?= ($filters['location_id'] ?? '') == $l['id'] ? 'selected' : '' ?>><?= e(localized($l['name_ar'], $l['name_en'] ?? null)) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="mb-3">
          <label class="form-label small fw-semibold"><?= __('rating') ?></label>
          <select name="min_rating" class="form-select form-select-sm">
            <option value="">الكل</option>
            <?php foreach ([4,3,2,1] as $r): ?><option value="<?= $r ?>" <?= ($filters['min_rating'] ?? '') == $r ? 'selected' : '' ?>><?= $r ?>+ نجوم</option><?php endforeach; ?>
          </select>
        </div>
        <div class="form-check mb-2">
          <input class="form-check-input" type="checkbox" id="verifiedOnly" name="verified_only" value="1" <?= !empty($filters['verified_only']) ? 'checked' : '' ?>>
          <label class="form-check-label small" for="verifiedOnly">موثّق فقط</label>
        </div>
        <div class="form-check mb-3">
          <input class="form-check-input" type="checkbox" id="availableOnly" name="available_only" value="1" <?= !empty($filters['available_only']) ? 'checked' : '' ?>>
          <label class="form-check-label small" for="availableOnly">متاح الآن فقط</label>
        </div>
        <button class="btn btn-primary btn-sm w-100 mb-2"><i class="fa-solid fa-magnifying-glass"></i> <?= __('search') ?></button>
        <?php if (array_filter($filters)): ?>
          <a href="<?= url('/providers') ?>" class="btn btn-outline-secondary btn-sm w-100"><i class="fa-solid fa-xmark"></i> إزالة الفلاتر</a>
        <?php endif; ?>
      </form>
    </div>
  </div>

  <!-- ============ RESULTS ============ -->
  <div class="col-lg-9">
    <?php if (empty($providers)): ?>
      <!-- ============ NO RESULTS ============ -->
      <div class="surface-card p-5 text-center">
        <i class="fa-solid fa-magnifying-glass-minus fa-3x text-secondary opacity-50 mb-3 d-block"></i>
        <h5 class="fw-bold mb-2">لم نجد خدمة مطابقة لبحثك</h5>
        <p class="text-muted mb-4">جرّب كلمة بحث مختلفة، أو استخدم تحليل المشكلة بالذكاء الاصطناعي لنساعدك في تحديد الخدمة المناسبة.</p>
        <div class="d-flex flex-wrap gap-2 justify-content-center mb-4">
          <a href="<?= $isCustomer ? url('/customer/requests/create') : url('/login?redirect=' . urlencode('/customer/requests/create')) ?>" class="btn btn-primary">
            <i class="fa-solid fa-wand-magic-sparkles"></i> استخدم التحليل الذكي
          </a>
          <a href="<?= url('/providers') ?>" class="btn btn-outline-primary"><i class="fa-solid fa-users"></i> عرض جميع المهنيين</a>
        </div>
        <?php if (!empty($suggestedCategories)): ?>
          <hr class="my-4">
          <p class="fw-semibold small mb-3">أو تصفح حسب التصنيف:</p>
          <div class="d-flex flex-wrap gap-2 justify-content-center">
            <?php foreach (array_slice($suggestedCategories, 0, 8) as $c): ?>
              <a href="<?= url('/providers?category_id=' . $c['id']) ?>" class="badge bg-light text-dark border px-3 py-2 text-decoration-none">
                <i class="fa-solid <?= e($c['icon']) ?>"></i> <?= e(localized($c['name_ar'], $c['name_en'] ?? null)) ?>
              </a>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    <?php else: ?>
      <div class="row g-3">
        <?php foreach ($providers as $p): ?>
          <div class="col-md-6">
            <div class="provider-card p-3 h-100 d-flex flex-column">
              <div class="d-flex align-items-center gap-3 mb-2">
                <div class="provider-avatar">
                  <?php if (!empty($p['profile_photo'])): ?><img src="<?= e($p['profile_photo']) ?>" alt="<?= e($p['name']) ?>"><?php else: ?><i class="fa-solid fa-user"></i><?php endif; ?>
                </div>
                <div class="min-w-0 flex-grow-1">
                  <div class="d-flex align-items-center gap-2">
                    <span class="fw-bold text-truncate"><?= e($p['name']) ?></span>
                    <?php if ($p['is_verified']): ?><span class="verified-badge"><i class="fa-solid fa-circle-check"></i> <?= __('verified') ?></span><?php endif; ?>
                  </div>
                  <div class="text-muted small text-truncate"><?= e($p['profession'] ?? '') ?><?php if (!empty($p['category_name_ar'])): ?> &middot; <?= e(localized($p['category_name_ar'], $p['category_name_en'] ?? null)) ?><?php endif; ?></div>
                </div>
              </div>
              <div class="rating-stars mb-2">
                <?php for ($i = 1; $i <= 5; $i++): ?><i class="fa-solid fa-star<?= $i <= round($p['avg_rating']) ? '' : ' text-secondary opacity-25' ?>"></i><?php endfor; ?>
                <span class="text-muted small ms-1">(<?= number_format((float)$p['avg_rating'], 1) ?> - <?= (int)$p['rating_count'] ?> تقييم)</span>
              </div>
              <p class="small mb-3">
                <i class="fa-solid fa-briefcase text-muted"></i> <?= (int)$p['experience_years'] ?> سنوات خبرة
                &middot;
                <span class="badge-status <?= $p['is_available'] ? 'is-available' : 'is-unavailable' ?>"><?= $p['is_available'] ? __('available') : __('unavailable') ?></span>
              </p>
              <div class="d-flex gap-2 mt-auto">
                <a href="<?= url('/providers/' . $p['id']) ?>" class="btn btn-outline-primary btn-sm flex-grow-1"><?= __('view_profile') ?></a>
                <?php if ($isCustomer): ?>
                  <a href="<?= url('/customer/requests/create?provider_id=' . $p['id']) ?>" class="btn btn-primary btn-sm flex-grow-1"><?= __('request_service') ?></a>
                <?php elseif (!Auth::check()): ?>
                  <a href="<?= url('/login?redirect=' . urlencode('/customer/requests/create?provider_id=' . $p['id'])) ?>" class="btn btn-primary btn-sm flex-grow-1"><?= __('request_service') ?></a>
                <?php endif; ?>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</div>
