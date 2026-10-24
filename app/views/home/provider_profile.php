<?php
$dayNames = ['الأحد', 'الاثنين', 'الثلاثاء', 'الأربعاء', 'الخميس', 'الجمعة', 'السبت'];
$byDay = [];
foreach ($availability as $slot) { $byDay[(int)$slot['day_of_week']][] = $slot; }
?>

<!-- ============ HEADER ============ -->
<div class="provider-hero surface-card p-4 p-md-5 mb-4">
  <div class="row align-items-center g-4">
    <div class="col-md-auto text-center">
      <div class="provider-avatar-xl mx-auto">
        <?php if (!empty($provider['profile_photo'])): ?>
          <img src="<?= e($provider['profile_photo']) ?>" alt="<?= e($provider['name']) ?>">
        <?php else: ?>
          <i class="fa-solid fa-user"></i>
        <?php endif; ?>
      </div>
    </div>
    <div class="col-md">
      <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
        <h2 class="fw-bold mb-0"><?= e($provider['name']) ?></h2>
        <?php if ($provider['is_verified']): ?><span class="verified-badge"><i class="fa-solid fa-circle-check"></i> <?= __('verified') ?></span><?php endif; ?>
        <span class="badge-status <?= $provider['is_available'] ? 'is-available' : 'is-unavailable' ?>">
          <i class="fa-solid fa-circle" style="font-size:.5rem"></i> <?= $provider['is_available'] ? __('available') : __('unavailable') ?>
        </span>
      </div>
      <p class="text-muted mb-3 fs-6">
        <i class="fa-solid fa-briefcase"></i> <?= e($provider['profession'] ?? '') ?>
        <?php if (!empty($provider['specialization'])): ?> &middot; <?= e($provider['specialization']) ?><?php endif; ?>
        <?php if (!empty($provider['category_name_ar'])): ?>
          <span class="badge bg-light text-dark border ms-2"><?= e(localized($provider['category_name_ar'], $provider['category_name_en'] ?? null)) ?></span>
        <?php endif; ?>
      </p>
      <div class="d-flex flex-wrap gap-4">
        <div class="profile-stat">
          <div class="rating-stars mb-1">
            <?php for ($i = 1; $i <= 5; $i++): ?><i class="fa-solid fa-star<?= $i <= round($provider['avg_rating']) ? '' : ' text-secondary opacity-25' ?>"></i><?php endfor; ?>
          </div>
          <div class="small text-muted"><?= number_format((float)$provider['avg_rating'], 1) ?> &middot; <?= (int)$provider['rating_count'] ?> تقييم</div>
        </div>
        <div class="profile-stat">
          <div class="fw-bold fs-5"><?= (int)$provider['completed_jobs'] ?></div>
          <div class="small text-muted">عملية منجزة</div>
        </div>
        <div class="profile-stat">
          <div class="fw-bold fs-5"><?= (int)$provider['experience_years'] ?></div>
          <div class="small text-muted"><?= __('experience_years') ?></div>
        </div>
        <div class="profile-stat">
          <div class="fw-bold fs-5"><?= number_format((float)$provider['response_rate'], 0) ?>%</div>
          <div class="small text-muted">معدل الاستجابة</div>
        </div>
      </div>
    </div>
    <div class="col-md-auto">
      <?php if (Auth::check() && Auth::role() === 'customer'): ?>
        <a href="<?= url('/customer/requests/create?provider_id=' . $provider['id']) ?>" class="btn btn-primary btn-lg w-100 mb-2">
          <i class="fa-solid fa-paper-plane"></i> طلب هذه الخدمة
        </a>
        <a href="<?= url('/customer/complaints/create?against_user_id=' . $provider['user_id'] . '&subject=' . urlencode('بلاغ عن مقدم الخدمة: ' . $provider['name'])) ?>" class="btn btn-outline-danger btn-sm w-100">
          <i class="fa-solid fa-flag"></i> الإبلاغ عن مقدم الخدمة
        </a>
      <?php elseif (!Auth::check()): ?>
        <a href="<?= url('/login?redirect=' . urlencode('/customer/requests/create?provider_id=' . $provider['id'])) ?>" class="btn btn-primary btn-lg w-100">
          <i class="fa-solid fa-right-to-bracket"></i> سجّل الدخول لطلب الخدمة
        </a>
      <?php else: ?>
        <span class="text-muted small">تسجيل الدخول متاح للعملاء فقط لطلب الخدمة</span>
      <?php endif; ?>
    </div>
  </div>
</div>

<div class="row g-4">
  <div class="col-lg-8">

    <!-- ============ ABOUT ============ -->
    <div class="surface-card p-4 mb-4">
      <h5 class="fw-bold mb-3"><i class="fa-solid fa-circle-info text-primary"></i> نبذة عن مقدم الخدمة</h5>
      <p class="mb-0 text-muted"><?= nl2br(e($provider['bio'] ?? 'لا توجد نبذة مضافة بعد.')) ?></p>
    </div>

    <!-- ============ SERVICES ============ -->
    <div class="surface-card p-4 mb-4">
      <h5 class="fw-bold mb-3"><i class="fa-solid fa-layer-group text-primary"></i> الخدمات المقدمة</h5>
      <?php if (empty($services)): ?>
        <p class="text-muted mb-0">لم يقم مقدم الخدمة بإضافة خدمات بعد.</p>
      <?php else: ?>
        <div class="row g-3">
          <?php foreach ($services as $s): ?>
            <div class="col-sm-6">
              <div class="service-line-item h-100">
                <div class="fw-semibold mb-1"><?= e(currentLang() === 'ar' ? $s['name_ar'] : ($s['name_en'] ?: $s['name_ar'])) ?></div>
                <div class="text-muted small">
                  <i class="fa-solid fa-tag"></i>
                  <?= number_format((float)$s['price_from']) ?> — <?= number_format((float)$s['price_to']) ?> <?= e(currency()) ?>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

    <!-- ============ COVERAGE AREAS ============ -->
    <div class="surface-card p-4 mb-4">
      <h5 class="fw-bold mb-3"><i class="fa-solid fa-location-dot text-primary"></i> مناطق التغطية</h5>
      <?php if (empty($locations)): ?>
        <p class="text-muted mb-0">لم تتم إضافة مناطق عمل بعد.</p>
      <?php else: ?>
        <div class="d-flex flex-wrap gap-2">
          <?php foreach ($locations as $l): ?>
            <span class="badge bg-light text-dark border px-3 py-2"><i class="fa-solid fa-map-pin text-primary"></i> <?= e(localized($l['name_ar'], $l['name_en'] ?? null)) ?></span>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

    <!-- ============ AVAILABILITY ============ -->
    <div class="surface-card p-4 mb-4">
      <h5 class="fw-bold mb-3"><i class="fa-solid fa-clock text-primary"></i> أوقات العمل والتوفر</h5>
      <?php if (empty($byDay)): ?>
        <p class="text-muted mb-0">
          لم يتم تحديد جدول أسبوعي مفصل.
          <span class="badge-status <?= $provider['is_available'] ? 'is-available' : 'is-unavailable' ?> ms-1">
            <?= $provider['is_available'] ? 'متاح حاليًا بشكل عام' : 'غير متاح حاليًا' ?>
          </span>
        </p>
      <?php else: ?>
        <div class="availability-grid">
          <?php for ($d = 0; $d <= 6; $d++): ?>
            <div class="availability-day <?= empty($byDay[$d]) ? 'is-off' : '' ?>">
              <div class="fw-semibold small mb-1"><?= $dayNames[$d] ?></div>
              <?php if (empty($byDay[$d])): ?>
                <div class="text-muted small">غير متاح</div>
              <?php else: ?>
                <?php foreach ($byDay[$d] as $slot): ?>
                  <div class="small text-primary"><?= substr($slot['start_time'], 0, 5) ?> - <?= substr($slot['end_time'], 0, 5) ?></div>
                <?php endforeach; ?>
              <?php endif; ?>
            </div>
          <?php endfor; ?>
        </div>
      <?php endif; ?>
    </div>

    <!-- ============ PORTFOLIO ============ -->
    <div class="surface-card p-4 mb-4">
      <h5 class="fw-bold mb-3"><i class="fa-solid fa-images text-primary"></i> معرض الأعمال السابقة</h5>
      <?php if (empty($portfolio)): ?>
        <p class="text-muted mb-0">لا توجد أعمال سابقة مضافة بعد.</p>
      <?php else: ?>
        <div class="portfolio-gallery">
          <?php foreach ($portfolio as $img): ?>
            <a href="<?= e($img['image_path']) ?>" target="_blank" rel="noopener" class="portfolio-item" title="<?= e($img['caption'] ?? '') ?>">
              <img src="<?= e($img['image_path']) ?>" alt="<?= e($img['caption'] ?? 'عمل سابق') ?>" loading="lazy">
              <?php if (!empty($img['caption'])): ?><span class="portfolio-caption"><?= e($img['caption']) ?></span><?php endif; ?>
            </a>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

    <!-- ============ REVIEWS ============ -->
    <div class="surface-card p-4">
      <h5 class="fw-bold mb-3"><i class="fa-solid fa-star text-primary"></i> <?= __('reviews') ?> (<?= count($reviews) ?>)</h5>
      <?php if (empty($reviews)): ?>
        <div class="empty-state">
          <i class="fa-regular fa-comment-dots fa-2x mb-2 d-block"></i>
          لا توجد تقييمات بعد.
        </div>
      <?php endif; ?>
      <?php foreach ($reviews as $r): ?>
        <div class="review-item">
          <div class="d-flex justify-content-between align-items-start flex-wrap gap-1">
            <strong><?= e($r['customer_name']) ?></strong>
            <span class="rating-stars small"><?php for ($i = 1; $i <= 5; $i++): ?><i class="fa-solid fa-star<?= $i <= $r['rating'] ? '' : ' text-secondary opacity-25' ?>"></i><?php endfor; ?></span>
          </div>
          <p class="mb-1 mt-1"><?= e($r['comment'] ?? '') ?></p>
          <?php if (!empty($r['provider_reply'])): ?>
            <div class="bg-light p-2 rounded small mt-1"><i class="fa-solid fa-reply"></i> رد مقدم الخدمة: <?= e($r['provider_reply']) ?></div>
          <?php endif; ?>
          <div class="text-muted small mt-1"><?= formatDate($r['created_at']) ?></div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- ============ SIDEBAR CTA ============ -->
  <div class="col-lg-4">
    <div class="surface-card p-4 sticky-top" style="top:1rem">
      <h6 class="fw-bold mb-3">هل تحتاج هذه الخدمة؟</h6>
      <p class="text-muted small mb-3">تواصل مع <?= e($provider['name']) ?> مباشرة عبر طلب خدمة على المنصة، ويتم تنسيق كل التفاصيل من داخل حسابك.</p>
      <?php if (Auth::check() && Auth::role() === 'customer'): ?>
        <a href="<?= url('/customer/requests/create?provider_id=' . $provider['id']) ?>" class="btn btn-primary w-100 mb-2">
          <i class="fa-solid fa-paper-plane"></i> طلب خدمة من هذا المهني
        </a>
      <?php elseif (!Auth::check()): ?>
        <a href="<?= url('/login?redirect=' . urlencode('/customer/requests/create?provider_id=' . $provider['id'])) ?>" class="btn btn-primary w-100 mb-2">
          <i class="fa-solid fa-right-to-bracket"></i> سجّل الدخول للطلب
        </a>
        <a href="<?= url('/register') ?>" class="btn btn-outline-primary w-100">
          <i class="fa-solid fa-user-plus"></i> إنشاء حساب جديد
        </a>
      <?php endif; ?>
      <hr>
      <ul class="list-unstyled small text-muted mb-0">
        <li class="mb-2"><i class="fa-solid fa-shield-halved text-primary"></i> حسابات موثّقة ومراجَعة من الإدارة</li>
        <li class="mb-2"><i class="fa-solid fa-star text-primary"></i> تقييمات حقيقية من عملاء سابقين</li>
        <li><i class="fa-solid fa-headset text-primary"></i> دعم فني عند وجود أي مشكلة</li>
      </ul>
    </div>
  </div>
</div>
