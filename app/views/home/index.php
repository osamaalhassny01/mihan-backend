<?php $isCustomer = Auth::check() && Auth::role() === 'customer'; ?>

<!-- ============ HERO SECTION (MODERN LUXURY HERO) ============ -->
<section class="hero-section mb-5 position-relative" style="background: linear-gradient(135deg, #072a27 0%, #0d544e 50%, #134e4a 100%); border: 1px solid rgba(94, 234, 212, 0.25); border-radius: 28px; padding: 4.5rem 3.5rem; box-shadow: 0 25px 60px rgba(7, 42, 39, 0.35); overflow: hidden;">
  <!-- Ambient Glow Elements -->
  <div style="position: absolute; top: -120px; inset-inline-end: -80px; width: 420px; height: 420px; border-radius: 50%; background: radial-gradient(circle, rgba(20, 184, 166, 0.35) 0%, rgba(20, 184, 166, 0) 70%); pointer-events: none;"></div>
  <div style="position: absolute; bottom: -80px; inset-inline-start: -60px; width: 350px; height: 350px; border-radius: 50%; background: radial-gradient(circle, rgba(245, 158, 11, 0.2) 0%, rgba(245, 158, 11, 0) 70%); pointer-events: none;"></div>

  <div class="row align-items-center position-relative" style="z-index: 3;">
    <div class="col-lg-8">
      <div class="d-inline-flex align-items-center gap-2 px-3 py-2 rounded-pill mb-3" style="background: rgba(255, 255, 255, 0.12); border: 1px solid rgba(94, 234, 212, 0.35); backdrop-filter: blur(10px);">
        <span class="badge bg-warning text-dark fw-bold px-2 py-1"><i class="fa-solid fa-bolt"></i> إصدار 3.0</span>
        <span class="text-white fw-bold small">منصة ذكية متكاملة لإدارة وربط الخدمات المهنية المحلية</span>
      </div>

      <h1 class="fw-bold mb-3" style="color: #ffffff; font-size: clamp(2.2rem, 5vw, 3.6rem); line-height: 1.25; letter-spacing: -0.02em;">
        ابحث عن <span style="background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; text-shadow: none;">المهني المعتمد</span> بكل ثقة وضمان.
      </h1>

      <p class="lead mb-4" style="color: #e6f7f5; font-size: 1.15rem; line-height: 1.8; max-width: 680px;">
        تربطك منصة مِهَن بنخبة الفنيين المحليين الموثقين، وتوظف خوارزميات الذكاء الاصطناعي لفحص عطلتك واختيار مقدم الخدمة الأنسب والأقرب لموقعك.
      </p>

      <!-- Search Input Container -->
      <form class="hero-search mb-4" action="<?= url('/providers') ?>" method="GET" style="background: #ffffff; border-radius: 18px; padding: 0.5rem 0.65rem; display: flex; gap: 0.5rem; max-width: 720px; box-shadow: 0 15px 35px rgba(0, 0, 0, 0.25); border: 2px solid rgba(255, 255, 255, 0.4);">
        <div class="d-flex align-items-center flex-grow-1 ps-2">
          <i class="fa-solid fa-magnifying-glass text-muted me-2 fs-5"></i>
          <input name="keyword" type="search" placeholder="ما الخدمة التي تحتاجها؟ مثال: صيانة تكييف، سباكة، كهرباء..." aria-label="البحث عن خدمة" style="border: 0; outline: 0; width: 100%; font-family: 'Cairo', sans-serif; font-size: 1.05rem; color: #0b3734;">
        </div>
        <button class="btn btn-primary px-4 py-2 fw-bold" type="submit" style="border-radius: 14px; background: linear-gradient(135deg, #0f766e 0%, #115e59 100%); border: none;">
          ابحث الآن
        </button>
      </form>

      <div class="d-flex flex-wrap align-items-center gap-3">
        <?php
          $aiCtaHref = $isCustomer
            ? url('/customer/requests/create')
            : url('/login?redirect=' . urlencode('/customer/requests/create'));
        ?>
        <a href="<?= $aiCtaHref ?>" class="btn btn-warning px-4 py-2 fw-bold" style="border-radius: 14px; box-shadow: 0 6px 20px rgba(245, 158, 11, 0.35);">
          <i class="fa-solid fa-wand-magic-sparkles me-1"></i> تحليل المشكلة بالذكاء الاصطناعي
        </a>
        <a href="<?= url('/providers') ?>" class="btn btn-outline-light px-4 py-2 fw-bold" style="border-radius: 14px; border: 1.5px solid rgba(255, 255, 255, 0.35); background: rgba(255, 255, 255, 0.08);">
          <i class="fa-solid fa-users-gear me-1"></i> استكشف المهنيين المتاحين
        </a>
      </div>
    </div>

    <!-- Visual Stats / Floating Badge column -->
    <div class="col-lg-4 d-none d-lg-block text-center position-relative">
      <div class="p-4 rounded-4 text-start" style="background: rgba(255, 255, 255, 0.08); border: 1px solid rgba(94, 234, 212, 0.3); backdrop-filter: blur(14px); box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);">
        <div class="d-flex align-items-center gap-3 mb-3 pb-3 border-bottom border-light border-opacity-25">
          <div style="width: 50px; height: 50px; border-radius: 14px; background: #5eead4; color: #0b3734; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
            <i class="fa-solid fa-shield-halved"></i>
          </div>
          <div>
            <div class="fw-bold text-white fs-6">ضمان وجودة الخدمة</div>
            <small style="color: #a7f3d0;">فنيون معتمدون وموثقون رسميًا</small>
          </div>
        </div>
        <div class="d-flex align-items-center gap-3 mb-3 pb-3 border-bottom border-light border-opacity-25">
          <div style="width: 50px; height: 50px; border-radius: 14px; background: #fbbf24; color: #0b3734; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
            <i class="fa-solid fa-bolt-lightning"></i>
          </div>
          <div>
            <div class="fw-bold text-white fs-6">استجابة فورية 24/7</div>
            <small style="color: #a7f3d0;">تتبع مسار الطلب لحظة بلحظة</small>
          </div>
        </div>
        <div class="d-flex align-items-center gap-3">
          <div style="width: 50px; height: 50px; border-radius: 14px; background: #38bdf8; color: #0b3734; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
            <i class="fa-solid fa-brain"></i>
          </div>
          <div>
            <div class="fw-bold text-white fs-6">خوارزميات AI ذكية</div>
            <small style="color: #a7f3d0;">توجيه آلي ومطابقة جغرافية</small>
          </div>
        </div>
      </div>
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
    <h3 class="section-title mb-1" style="color: #0b3734 !important;"><i class="fa-solid fa-layer-group me-2" style="color: #0f766e;"></i>استكشف حسب التخصص</h3>
    <p class="section-subtitle mb-0" style="color: #527976;">اختر المجال الذي تحتاجه وتواصل مع نخبة المهنيين</p>
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

<!-- ============ PROVIDER CALL TO ACTION (MODERN LUXURY CTA) ============ -->
<div id="about" class="mb-5 position-relative" style="background: linear-gradient(135deg, #072a27 0%, #0d544e 60%, #115e59 100%); border: 1px solid rgba(94, 234, 212, 0.25); border-radius: 28px; padding: 4rem 3rem; box-shadow: 0 25px 50px rgba(7, 42, 39, 0.3); overflow: hidden;">
  <div style="position: absolute; top: -60px; inset-inline-end: -60px; width: 300px; height: 300px; border-radius: 50%; background: radial-gradient(circle, rgba(94, 234, 212, 0.25) 0%, transparent 70%); pointer-events: none;"></div>
  
  <div class="row align-items-center position-relative" style="z-index: 3;">
    <div class="col-lg-8 text-lg-start text-center mb-4 mb-lg-0">
      <span class="badge px-3 py-2 rounded-pill mb-3" style="background: rgba(251, 191, 36, 0.15); color: #fbbf24; border: 1px solid rgba(251, 191, 36, 0.35); font-size: 0.88rem; font-weight: 700;">
        <i class="fa-solid fa-handshake-angle me-1"></i> فرصة لزيادة دخلك وعملائك
      </span>
      <h2 class="fw-bold text-white mb-2" style="font-size: clamp(1.8rem, 4vw, 2.8rem);">هل أنت مهني أو صاحب حرفة متميزة؟</h2>
      <p class="lead mb-0" style="color: #e6f7f5; font-size: 1.1rem; line-height: 1.8; max-width: 650px;">
        انضم اليوم إلى شبكة مِهَن واستقبل طلبات الصيانة من آلاف العملاء في منطقتك الجغرافية مع نظام إدارة فواتير ومحادثات ومتابعة متكامل.
      </p>
    </div>
    <div class="col-lg-4 text-lg-end text-center">
      <a href="<?= url('/register?role=provider') ?>" class="btn btn-warning px-4 py-3 fs-6 fw-bold" style="border-radius: 16px; box-shadow: 0 8px 25px rgba(245, 158, 11, 0.4); color: #0b3734 !important; background: linear-gradient(135deg, #f59e0b 0%, #fbbf24 100%); border: none;">
        <i class="fa-solid fa-user-plus me-1"></i> انضم الآن كمقدم خدمة معتمد
      </a>
    </div>
  </div>
</div>
