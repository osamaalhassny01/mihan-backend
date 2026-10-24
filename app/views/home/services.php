<div class="mb-4">
  <h3 class="fw-bold mb-1"><?= __('services') ?></h3>
  <p class="section-subtitle mb-0">تصفح كل الخدمات المتاحة على المنصة مجمّعة حسب التصنيف.</p>
</div>

<?php if (empty($services)): ?>
  <div class="surface-card p-5 text-center">
    <i class="fa-solid fa-layer-group fa-3x text-secondary opacity-50 mb-3 d-block"></i>
    <h5 class="fw-bold mb-2">لا توجد خدمات متاحة حاليًا</h5>
    <p class="text-muted mb-0">يرجى المحاولة لاحقًا.</p>
  </div>
<?php else: ?>
  <?php $grouped = []; foreach ($services as $s) { $groupKey = currentLang() === 'en' ? ($s['category_name_en'] ?: $s['category_name_ar']) : $s['category_name_ar']; $grouped[$groupKey][] = $s; } ?>
  <?php foreach ($grouped as $catName => $items): ?>
    <div class="d-flex align-items-center gap-2 mt-4 mb-3">
      <div class="category-icon" style="width:42px;height:42px;font-size:1.05rem"><i class="fa-solid <?= e($items[0]['category_icon'] ?? 'fa-toolbox') ?>"></i></div>
      <h5 class="fw-bold mb-0"><?= e(localized($catName, $catName)) ?></h5>
    </div>
    <div class="row g-3 mb-2">
      <?php foreach ($items as $s): ?>
        <div class="col-sm-6 col-lg-4">
          <div class="surface-card p-3 h-100 d-flex flex-column">
            <div class="fw-semibold mb-1"><?= currentLang() === 'ar' ? e(localized($s['name_ar'], $s['name_en'] ?? null)) : e($s['name_en'] ?: $s['name_ar']) ?></div>
            <p class="text-muted small mb-3 flex-grow-1"><?= e($s['description'] ?? 'لا يوجد وصف لهذه الخدمة.') ?></p>
            <a href="<?= url('/providers?service_id=' . $s['id']) ?>" class="btn btn-sm btn-outline-primary mt-auto mb-2">
              <i class="fa-solid fa-user-gear"></i> تصفح مقدمي هذه الخدمة
            </a>
            <?php if (Auth::check() && Auth::role() === 'customer'): ?>
              <a href="<?= url('/customer/complaints/create?subject=' . urlencode('بلاغ عن خدمة: ' . ($s['name_ar'] ?? ''))) ?>" class="btn btn-sm btn-outline-danger">
                <i class="fa-solid fa-flag"></i> الإبلاغ عن الخدمة
              </a>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endforeach; ?>
<?php endif; ?>
