<div class="mb-5 text-center text-md-start">
  <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill mb-3" style="background: rgba(15, 118, 110, 0.1); border: 1px solid rgba(15, 118, 110, 0.25);">
    <i class="fa-solid fa-layer-group text-primary"></i>
    <span class="small fw-bold text-primary">دليل الخدمات والتخصصات المعتمدة</span>
  </div>
  <h2 class="fw-bold mb-2" style="color: #0b3734; font-size: clamp(1.8rem, 4vw, 2.6rem);">
    استكشف خدمات <span style="background: linear-gradient(135deg, #0f766e 0%, #14b8a6 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">مِهَن</span> المتنوعة
  </h2>
  <p class="section-subtitle mb-0" style="color: #527976; font-size: 1.05rem;">
    اختر الخدمة التي تحتاجها وتواصل مع أمهر الفنيين والمهنيين الموثقين بأعلى معايير الجودة والضمان.
  </p>
</div>

<?php if (empty($services)): ?>
  <div class="card p-5 text-center border-0 shadow-sm" style="border-radius: 24px;">
    <div style="width: 80px; height: 80px; border-radius: 24px; background: #f0fdfa; color: #0f766e; display: inline-flex; align-items: center; justify-content: center; font-size: 2.2rem; margin: 0 auto 1.5rem;">
      <i class="fa-solid fa-layer-group"></i>
    </div>
    <h4 class="fw-bold mb-2" style="color: #0b3734;">لا توجد خدمات متاحة حاليًا</h4>
    <p class="text-muted mb-0">يتم تحديث قائمة الخدمات باستمرار، يرجى المحاولة لاحقًا.</p>
  </div>
<?php else: ?>
  <?php 
    $grouped = []; 
    foreach ($services as $s) { 
      $groupKey = currentLang() === 'en' ? ($s['category_name_en'] ?: $s['category_name_ar']) : $s['category_name_ar']; 
      $grouped[$groupKey][] = $s; 
    } 
    $animDelay = 0;
  ?>

  <?php foreach ($grouped as $catName => $items): ?>
    <div class="category-block mb-5 reveal-on-scroll">
      
      <!-- Category Header Pill -->
      <div class="d-flex align-items-center justify-content-between mb-4 pb-2 border-bottom" style="border-color: #d4e7e4 !important;">
        <div class="d-flex align-items-center gap-3">
          <div class="category-icon" style="width: 52px; height: 52px; font-size: 1.35rem; border-radius: 16px; background: #ecfdf5; color: #0f766e; box-shadow: 0 4px 14px rgba(15,118,110,0.15);">
            <i class="fa-solid <?= e($items[0]['category_icon'] ?? 'fa-toolbox') ?>"></i>
          </div>
          <div>
            <h4 class="fw-bold mb-0" style="color: #0b3734;"><?= e(localized($catName, $catName)) ?></h4>
            <small style="color: #527976; font-weight: 600;"><?= count($items) ?> خدمة متاحة في هذا المجال</small>
          </div>
        </div>
        <a href="<?= url('/providers?category_id=' . ($items[0]['category_id'] ?? '')) ?>" class="btn btn-outline-primary btn-sm px-3 py-2 fw-bold" style="border-radius: 12px;">
          عرض المهنيين <i class="fa-solid fa-arrow-left ms-1"></i>
        </a>
      </div>

      <!-- Services Cards Grid -->
      <div class="row g-4">
        <?php foreach ($items as $s): ?>
          <div class="col-sm-6 col-lg-4">
            <div class="service-interactive-card h-100 d-flex flex-column p-4" style="background: #ffffff; border: 1.5px solid #d4e7e4; border-radius: 20px; box-shadow: 0 6px 20px rgba(15,118,110,0.06); transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1); position: relative; overflow: hidden;">
              
              <!-- Subtle accent top strip -->
              <div style="position: absolute; top: 0; inset-inline: 0; height: 4px; background: linear-gradient(90deg, #0f766e, #14b8a6); opacity: 0.8;"></div>

              <div class="d-flex align-items-center justify-content-between mb-3">
                <div class="service-icon-box" style="width: 44px; height: 44px; border-radius: 12px; background: #f0fdfa; color: #0f766e; display: flex; align-items: center; justify-content: center; font-size: 1.2rem;">
                  <i class="fa-solid fa-wrench"></i>
                </div>
                <span class="badge" style="background: #ecfdf5; color: #059669; font-weight: 700; border-radius: 8px; padding: 0.35rem 0.65rem;">
                  <i class="fa-solid fa-circle-check me-1"></i> معتمدة
                </span>
              </div>

              <h5 class="fw-bold mb-2" style="color: #0b3734; font-size: 1.15rem;">
                <?= currentLang() === 'ar' ? e(localized($s['name_ar'], $s['name_en'] ?? null)) : e($s['name_en'] ?: $s['name_ar']) ?>
              </h5>

              <p class="small mb-4 flex-grow-1" style="color: #527976; line-height: 1.7;">
                <?= e($s['description'] ?? 'خدمة مهنية متخصصة ينفذها فنيون معتمدون ومجهزون بكافة الأدوات.') ?>
              </p>

              <div class="d-flex gap-2 mt-auto">
                <a href="<?= url('/providers?service_id=' . $s['id']) ?>" class="btn btn-primary btn-sm flex-grow-1 py-2 fw-bold" style="border-radius: 12px; background: linear-gradient(135deg, #0f766e 0%, #115e59 100%); border: none;">
                  <i class="fa-solid fa-users me-1"></i> طلب الخدمة
                </a>
                <?php if (Auth::check() && Auth::role() === 'customer'): ?>
                  <a href="<?= url('/customer/complaints/create?subject=' . urlencode('بلاغ عن خدمة: ' . ($s['name_ar'] ?? ''))) ?>" class="btn btn-outline-danger btn-sm px-2 py-2" style="border-radius: 12px;" title="الإبلاغ عن الخدمة">
                    <i class="fa-solid fa-flag"></i>
                  </a>
                <?php endif; ?>
              </div>

            </div>
          </div>
        <?php endforeach; ?>
      </div>

    </div>
  <?php endforeach; ?>
<?php endif; ?>

<style>
/* Smooth Scroll Reveal and Hover Dynamics */
.reveal-on-scroll {
  animation: fadeInUp 0.65s cubic-bezier(0.16, 1, 0.3, 1) both;
}

@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(28px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.service-interactive-card:hover {
  transform: translateY(-8px) scale(1.015);
  box-shadow: 0 18px 40px rgba(15, 118, 110, 0.16) !important;
  border-color: #5eead4 !important;
}

.service-interactive-card:hover .service-icon-box {
  background: #0f766e !important;
  color: #ffffff !important;
  transform: rotate(-6deg) scale(1.1);
  transition: all 0.3s ease;
}
</style>

<script>
// Dynamic stagger animation on scroll
document.addEventListener('DOMContentLoaded', function() {
  const blocks = document.querySelectorAll('.category-block');
  if ('IntersectionObserver' in window) {
    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('reveal-on-scroll');
          observer.unobserve(entry.target);
        }
      });
    }, { threshold: 0.15 });

    blocks.forEach(block => observer.observe(block));
  }
});
</script>
