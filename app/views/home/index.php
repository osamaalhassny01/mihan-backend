<?php $isCustomer = Auth::check() && Auth::role() === 'customer'; ?>

<!-- ============ HERO SECTION (ELITE BRAND IDENTITY) ============ -->
<section class="hero-section mb-5">
  <div class="hero-content">
    <span class="hero-kicker">
      <i class="fa-solid fa-sparkles text-warning"></i> منصة ذكية لربط وإدارة الخدمات المهنية المحلية
    </span>
    <h1 class="fw-bold mb-3">
      ابحث عن <span class="text-warning">المهني المناسب</span> بكل ثقة وسهولة.
    </h1>
    <p class="lead mb-4">
      منصة مِهَن تربطك بأفضل الفنيين ومقدمي الخدمات المحليين، وتساعدك بخوارزميات الذكاء الاصطناعي على تحليل المشكلة واختيار الأنسب حسب التخصص، الموقع، والتقييم الحقيقي.
    </p>

    <form class="hero-search mb-4" action="<?= url('/providers') ?>" method="GET">
      <input name="keyword" type="search" placeholder="ما الخدمة التي تحتاجها اليوم؟ (مثال: صيانة مكيف، كهرباء، سباكة...)" aria-label="البحث عن خدمة">
      <button class="btn btn-primary" type="submit">
        <i class="fa-solid fa-magnifying-glass me-1"></i> ابحث الآن
      </button>
    </form>

    <div class="d-flex flex-wrap gap-2">
      <?php
        $aiCtaHref = $isCustomer
          ? url('/customer/requests/create')
          : url('/login?redirect=' . urlencode('/customer/requests/create'));
      ?>
      <a href="<?= $aiCtaHref ?>" class="btn btn-warning">
        <i class="fa-solid fa-wand-magic-sparkles me-1"></i> تحليل المشكلة بالذكاء الاصطناعي
      </a>
      <a href="<?= url('/providers') ?>" class="btn btn-outline-light">
        <i class="fa-solid fa-user-gear me-1"></i> تصفح قائمة المهنيين
      </a>
    </div>
  </div>
</section>

<!-- ============ TRUST STATS ============ -->
<div class="trust-stats mb-5">
  <div class="trust-stat">
    <div class="num"><?= (int)$stats['providers'] ?></div>
    <div class="stat-label">مقدم خدمة معتمد</div>
  </div>
  <div class="trust-stat">
    <div class="num"><?= (int)$stats['services'] ?></div>
    <div class="stat-label">خدمة مهنية متاحة</div>
  </div>
  <div class="trust-stat">
    <div class="num"><?= (int)$stats['requests'] ?></div>
    <div class="stat-label">طلب خدمة منجز</div>
  </div>
  <div class="trust-stat">
    <div class="num"><?= (int)$stats['locations'] ?></div>
    <div class="stat-label">منطقة تغطية نشطة</div>
  </div>
</div>

<!-- ============ CATEGORIES ============ -->
<div class="d-flex justify-content-between align-items-end mb-4">
  <div>
    <h3 class="section-title mb-1"><i class="fa-solid fa-layer-group text-primary me-2"></i>استكشف حسب التخصص</h3>
    <p class="section-subtitle mb-0">اختر المجال الذي تحتاجه وتواصل مع نخبة المهنيين</p>
  </div>
  <a href="<?= url('/services') ?>" class="btn btn-outline-primary btn-sm">
    عرض كافة الخدمات <i class="fa-solid fa-arrow-left ms-1"></i>
  </a>
</div>

<div class="row g-3 mb-5">
  <?php foreach($categories as $cat): ?>
    <div class="col-6 col-md-3">
      <a href="<?= url('/providers?category_id='.$cat['id']) ?>" class="text-decoration-none">
        <div class="category-card h-100">
          <div class="category-icon">
            <i class="fa-solid <?= e($cat['icon']) ?>"></i>
          </div>
          <div class="fw-bold text-dark fs-6">
            <?= currentLang()==='ar'?e($cat['name_ar']):e($cat['name_en']?:$cat['name_ar']) ?>
          </div>
          <div class="small text-muted mt-1">تصفح المهنيين المتاحين</div>
        </div>
      </a>
    </div>
  <?php endforeach; ?>
</div>

<!-- ============ AI WORKFLOW SECTION ============ -->
<div id="ai-section" class="mb-4">
  <h3 class="section-title mb-1"><i class="fa-solid fa-brain text-warning me-2"></i>مِهَن الذكية (AI Capabilities)</h3>
  <p class="section-subtitle mb-0">حلول رقمية متطورة تدعم اتخاذ القرار وتسهل سير العمل من البداية إلى النهاية</p>
</div>

<div class="row g-3 mb-5">
  <div class="col-md-6 col-lg-3">
    <div class="ai-card">
      <div class="category-icon mb-3" style="background:#fef3c7;color:#d97706">
        <i class="fa-solid fa-wand-magic-sparkles"></i>
      </div>
      <h6 class="fw-bold mb-2">تحليل المشكلة بالذكاء الاصطناعي</h6>
      <p class="section-subtitle small flex-grow-1">اكتب وصف المشكلة بلهجتك الطبيعية وسيقوم النظام بتحديد نوع العطل والخدمة المطابقة فوراً.</p>
      <?php if ($isCustomer): ?>
        <a href="<?= url('/customer/requests/create') ?>" class="btn btn-outline-primary btn-sm mt-3">ابدأ التحليل الذكي</a>
      <?php else: ?>
        <a href="<?= url('/login?redirect=' . urlencode('/customer/requests/create')) ?>" class="btn btn-outline-primary btn-sm mt-3">سجّل الدخول لتجربة AI</a>
      <?php endif; ?>
    </div>
  </div>
  <div class="col-md-6 col-lg-3">
    <div class="ai-card">
      <div class="category-icon mb-3" style="background:#e0f2fe;color:#0284c7">
        <i class="fa-solid fa-ranking-star"></i>
      </div>
      <h6 class="fw-bold mb-2">التوصية والفرز الذكي</h6>
      <p class="section-subtitle small flex-grow-1">ترشيح أفضل مقدمي الخدمة بحسب القرب الجغرافي، التقييم، وتوفر الفني لتوفير وقتك وجهدك.</p>
      <?php if ($isCustomer): ?>
        <a href="<?= url('/customer/requests/create') ?>" class="btn btn-outline-primary btn-sm mt-3">اكتشف التوصيات</a>
      <?php else: ?>
        <a href="<?= url('/login?redirect=' . urlencode('/customer/requests/create')) ?>" class="btn btn-outline-primary btn-sm mt-3">سجّل الدخول للبدء</a>
      <?php endif; ?>
    </div>
  </div>
  <div class="col-md-6 col-lg-3">
    <div class="ai-card">
      <div class="category-icon mb-3" style="background:#ecfdf5;color:#10b981">
        <i class="fa-solid fa-shield-halved"></i>
      </div>
      <h6 class="fw-bold mb-2">الضمان ومتابعة التنفيذ</h6>
      <p class="section-subtitle small flex-grow-1">نظام تتبع مباشر لمراحل الطلب، توثيق المدفوعات والشكاوى، مع تقييم موثوق وشفاف.</p>
      <?php if (Auth::check()): ?>
        <a href="<?= url(Auth::role() . ($isCustomer ? '/requests' : '/dashboard')) ?>" class="btn btn-outline-primary btn-sm mt-3">متابعة طلباتي</a>
      <?php else: ?>
        <a href="<?= url('/login') ?>" class="btn btn-outline-primary btn-sm mt-3">دخول الحساب</a>
      <?php endif; ?>
    </div>
  </div>
  <div class="col-md-6 col-lg-3">
    <div class="ai-card">
      <div class="category-icon mb-3" style="background:#f1f5f9;color:#334155">
        <i class="fa-solid fa-chart-line"></i>
      </div>
      <h6 class="fw-bold mb-2">تحليلات الأداء والتقارير</h6>
      <p class="section-subtitle small flex-grow-1">مؤشرات تشغيلية حية ترصد حركة السوق، الطلبات الأكثر رواجاً، وتقارير الإنجاز لحظياً.</p>
      <?php if (Auth::check() && Auth::role() === 'admin'): ?>
        <a href="<?= url('/admin/reports') ?>" class="btn btn-outline-primary btn-sm mt-3">عرض لوحة التحليلات</a>
      <?php else: ?>
        <span class="badge badge-soft-primary mt-3 align-self-start">متاح لإدارة المنصة</span>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- ============ FEATURED PROVIDERS ============ -->
<div class="d-flex justify-content-between align-items-end mb-4">
  <div>
    <h3 class="section-title mb-1"><i class="fa-solid fa-award text-primary me-2"></i>مهنيون مميزون</h3>
    <p class="section-subtitle mb-0">نخبة من الفنيين المعتمدين الحاصلين على أعلى تقييمات العملاء</p>
  </div>
  <a href="<?= url('/providers') ?>" class="btn btn-outline-primary btn-sm">
    استكشف جميع المهنيين <i class="fa-solid fa-arrow-left ms-1"></i>
  </a>
</div>

<div class="row g-3 mb-5">
  <?php if (empty($topProviders)): ?>
    <div class="col-12">
      <div class="card p-5 text-center text-muted">
        <i class="fa-regular fa-face-frown fa-3x mb-3 text-secondary"></i>
        <h5>لا يوجد مقدمو خدمات متاحون حالياً</h5>
        <p class="mb-0">سيتم إضافة المزيد من المهنيين قريباً.</p>
      </div>
    </div>
  <?php endif; ?>
  <?php foreach($topProviders as $p): ?>
    <div class="col-md-4">
      <div class="card provider-card p-4 h-100 d-flex flex-column">
        <div class="d-flex align-items-center gap-3 mb-3">
          <div class="provider-avatar">
            <i class="fa-solid fa-user"></i>
          </div>
          <div class="min-w-0 flex-grow-1">
            <div class="fw-bold text-truncate fs-6 text-dark"><?= e($p['name']) ?></div>
            <div class="text-muted small"><?= e($p['profession'] ?? 'فني خدمات معتمد') ?></div>
          </div>
          <?php if($p['is_verified']): ?>
            <span class="badge badge-soft-success">
              <i class="fa-solid fa-circle-check"></i> موثّق
            </span>
          <?php endif; ?>
        </div>
        <div class="rating-stars mb-3">
          <?php for($i=1;$i<=5;$i++): ?>
            <i class="fa-solid fa-star<?= $i<=round($p['avg_rating'])?' text-warning':' text-muted opacity-25' ?>"></i>
          <?php endfor; ?>
          <span class="text-muted small ms-1 fw-bold">(<?= (float)$p['avg_rating'] ?>)</span>
          <span class="text-muted small ms-2">• <?= $p['completed_jobs'] ?? 0 ?> إنجاز</span>
        </div>
        <a href="<?= url('/providers/'.$p['id']) ?>" class="btn btn-outline-primary mt-auto">
          عرض الملف المهني <i class="fa-solid fa-arrow-left ms-1"></i>
        </a>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<!-- ============ HOW IT WORKS ============ -->
<div id="how-it-works" class="mb-5">
  <div class="text-center mb-4">
    <h3 class="section-title mb-1">كيف تعمل منصة مِهَن؟</h3>
    <p class="section-subtitle mb-0">خطوات واضحة وسريعة لتحصل على خدمتك بأعلى جودة</p>
  </div>
  <div class="row g-4">
    <div class="col-md-4">
      <div class="card p-4 text-center h-100">
        <div class="step-num mb-3 mx-auto" style="width:50px;height:50px;border-radius:16px;background:#f0fdfa;color:#0f766e;display:flex;align-items:center;justify-content:center;font-size:1.4rem;font-weight:900;">1</div>
        <h5 class="fw-bold mb-2">حدد مشكلتك أو طلبك</h5>
        <p class="text-muted small mb-0">صف المشكلة أو اختر الخدمة المطلوبة من القائمة بسهولة وسرعة.</p>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card p-4 text-center h-100">
        <div class="step-num mb-3 mx-auto" style="width:50px;height:50px;border-radius:16px;background:#fef3c7;color:#d97706;display:flex;align-items:center;justify-content:center;font-size:1.4rem;font-weight:900;">2</div>
        <h5 class="fw-bold mb-2">اختر المهني الأنسب</h5>
        <p class="text-muted small mb-0">قارن العروض والتقييمات والقرب الجغرافي بفضل خوارزميات المنصة الذكية.</p>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card p-4 text-center h-100">
        <div class="step-num mb-3 mx-auto" style="width:50px;height:50px;border-radius:16px;background:#ecfdf5;color:#10b981;display:flex;align-items:center;justify-content:center;font-size:1.4rem;font-weight:900;">3</div>
        <h5 class="fw-bold mb-2">التنفيذ المباشر والتقييم</h5>
        <p class="text-muted small mb-0">تابع حالة طلبك لحظياً، ادفع بأمان، وشارك تقييمك لتعزيز جودة المجتمع المهني.</p>
      </div>
    </div>
  </div>
</div>

<!-- ============ PROVIDER CALL TO ACTION ============ -->
<div id="about" class="hero-section text-center mb-4 py-5" style="background: linear-gradient(135deg, #0b3734 0%, #0f766e 100%);">
  <div class="hero-content">
    <h2 class="fw-bold text-white mb-2">هل أنت مهني أو صاحب حرفة متميزة؟</h2>
    <p class="lead text-white opacity-90 mb-4 mx-auto" style="max-width:650px;">
      انضم إلى شبكة مِهَن واستقبل مئات طلبات الصيانة والخدمات يومياً في منطقتك الجغرافية مع نظام إدارة ومتابعة متكامل.
    </p>
    <a href="<?= url('/register?role=provider') ?>" class="btn btn-warning px-4 py-2 fs-6">
      <i class="fa-solid fa-user-plus me-1"></i> انضم الآن كمقدم خدمة معتمد
    </a>
  </div>
</div>
