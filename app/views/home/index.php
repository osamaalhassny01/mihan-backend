<?php $isCustomer = Auth::check() && Auth::role() === 'customer'; ?>
<section class="hero-section mb-5">
 <div class="hero-content">
  <span class="hero-kicker"><i class="fa-solid fa-sparkles"></i> منصة ذكية للخدمات المهنية المحلية</span>
  <h1 class="fw-bold mb-3">ابحث عن <span class="text-warning">المهني المناسب</span> بثقة.</h1>
  <p class="lead fs-5 mb-4">مِهَن يربطك بمقدمي الخدمات المحليين، ويساعدك بالذكاء الاصطناعي على فهم احتياجك واختيار مقدم الخدمة الأنسب حسب التخصص والموقع والتوفر والتقييم.</p>
  <form class="hero-search mb-3" action="<?= url('/providers') ?>" method="GET">
   <input name="keyword" type="search" placeholder="ما الخدمة التي تحتاجها؟ مثال: صيانة مكيف" aria-label="البحث عن خدمة">
   <button class="btn btn-primary px-4" type="submit"><i class="fa-solid fa-magnifying-glass"></i> بحث</button>
  </form>
  <div class="d-flex flex-wrap gap-2 mt-3">
    <?php
      // Guests get sent to login first (with the AI analysis page preserved as
      // the return target); logged-in customers go straight to it.
      $aiCtaHref = $isCustomer
        ? url('/customer/requests/create')
        : url('/login?redirect=' . urlencode('/customer/requests/create'));
    ?>
    <a href="<?= $aiCtaHref ?>" class="btn btn-light">
      <i class="fa-solid fa-wand-magic-sparkles"></i> تحليل المشكلة بالذكاء الاصطناعي
    </a>
    <a href="<?= url('/providers') ?>" class="btn btn-outline-light"><i class="fa-solid fa-user-gear"></i> استكشف المهنيين</a>
  </div>
 </div>
</section>

<!-- ============ TRUST STATS (real numbers from the database) ============ -->
<div class="trust-stats mb-5">
  <div class="trust-stat"><div class="num"><?= (int)$stats['providers'] ?></div><div class="small text-muted">مقدم خدمة</div></div>
  <div class="trust-stat"><div class="num"><?= (int)$stats['services'] ?></div><div class="small text-muted">خدمة متاحة</div></div>
  <div class="trust-stat"><div class="num"><?= (int)$stats['requests'] ?></div><div class="small text-muted">طلب خدمة</div></div>
  <div class="trust-stat"><div class="num"><?= (int)$stats['locations'] ?></div><div class="small text-muted">منطقة تغطية</div></div>
</div>

<!-- ============ CATEGORIES ============ -->
<div class="d-flex justify-content-between align-items-end mb-3"><div><h3 class="section-title mb-1">استكشف حسب التخصص</h3><p class="section-subtitle mb-0">اختر المجال الذي تحتاج إليه.</p></div><a href="<?= url('/services') ?>" class="text-decoration-none fw-semibold">عرض الكل <i class="fa-solid fa-arrow-left"></i></a></div>
<div class="row g-3 mb-5">
 <?php foreach($categories as $cat): ?><div class="col-6 col-md-3"><a href="<?= url('/providers?category_id='.$cat['id']) ?>" class="text-decoration-none"><div class="category-card text-center h-100"><div class="category-icon mb-3"><i class="fa-solid <?= e($cat['icon']) ?>"></i></div><div class="fw-bold text-dark"><?= currentLang()==='ar'?e($cat['name_ar']):e($cat['name_en']?:$cat['name_ar']) ?></div></div></a></div><?php endforeach; ?>
</div>

<!-- ============ AI SECTION (each card links to a real, working feature) ============ -->
<div id="ai-section" class="mb-3"><h3 class="section-title mb-1">مِهَن أكثر ذكاءً</h3><p class="section-subtitle mb-0">أدوات فعلية تعمل على بياناتك، وليست مجرد وصف.</p></div>
<div class="row g-3 mb-5">
  <div class="col-md-6 col-lg-3">
    <div class="ai-card">
      <div class="category-icon mb-3"><i class="fa-solid fa-wand-magic-sparkles"></i></div>
      <h6 class="fw-bold">تحليل المشكلة بالذكاء الاصطناعي</h6>
      <p class="section-subtitle small flex-grow-1">اكتب مشكلتك بلغتك الطبيعية وسيحاول النظام تحديد الخدمة المناسبة تلقائيًا.</p>
      <?php if ($isCustomer): ?>
        <a href="<?= url('/customer/requests/create') ?>" class="btn btn-outline-primary btn-sm mt-2">ابدأ التحليل الذكي</a>
      <?php else: ?>
        <a href="<?= url('/login?redirect=' . urlencode('/customer/requests/create')) ?>" class="btn btn-outline-primary btn-sm mt-2">سجّل الدخول لتبدأ</a>
      <?php endif; ?>
    </div>
  </div>
  <div class="col-md-6 col-lg-3">
    <div class="ai-card">
      <div class="category-icon mb-3"><i class="fa-solid fa-ranking-star"></i></div>
      <h6 class="fw-bold">التوصيات الذكية</h6>
      <p class="section-subtitle small flex-grow-1">يرتّب النظام أفضل المهنيين حسب الخدمة والموقع والتقييم والخبرة والتوفر، مع توضيح سبب كل ترشيح.</p>
      <?php if ($isCustomer): ?>
        <a href="<?= url('/customer/requests/create') ?>" class="btn btn-outline-primary btn-sm mt-2">اكتشف المهني المناسب</a>
      <?php else: ?>
        <a href="<?= url('/login?redirect=' . urlencode('/customer/requests/create')) ?>" class="btn btn-outline-primary btn-sm mt-2">سجّل الدخول لتبدأ</a>
      <?php endif; ?>
    </div>
  </div>
  <div class="col-md-6 col-lg-3">
    <div class="ai-card">
      <div class="category-icon mb-3"><i class="fa-solid fa-shield-halved"></i></div>
      <h6 class="fw-bold">ثقة ومتابعة</h6>
      <p class="section-subtitle small flex-grow-1">تابع طلبك من الإنشاء حتى الإنجاز، ثم قيّم تجربتك وتواصل مع الدعم عند الحاجة.</p>
      <?php if (Auth::check()): ?>
        <a href="<?= url(Auth::role() . ($isCustomer ? '/requests' : '/dashboard')) ?>" class="btn btn-outline-primary btn-sm mt-2">عرض طلباتي</a>
      <?php else: ?>
        <a href="<?= url('/login') ?>" class="btn btn-outline-primary btn-sm mt-2">سجّل الدخول</a>
      <?php endif; ?>
    </div>
  </div>
  <div class="col-md-6 col-lg-3">
    <div class="ai-card">
      <div class="category-icon mb-3"><i class="fa-solid fa-chart-line"></i></div>
      <h6 class="fw-bold">تحليلات ذكية</h6>
      <p class="section-subtitle small flex-grow-1">أكثر الخدمات والمناطق طلبًا، متوسط التقييم، ومؤشرات أداء المنصة لحظة بلحظة.</p>
      <?php if (Auth::check() && Auth::role() === 'admin'): ?>
        <a href="<?= url('/admin/reports') ?>" class="btn btn-outline-primary btn-sm mt-2">عرض التحليلات</a>
      <?php else: ?>
        <span class="badge bg-light text-dark border mt-2 align-self-start">متاح لفريق الإدارة</span>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- ============ FEATURED PROVIDERS ============ -->
<div class="d-flex justify-content-between align-items-end mb-3"><div><h3 class="section-title mb-1">مهنيون مميزون</h3><p class="section-subtitle mb-0">نماذج من مقدمي الخدمات المتاحين على المنصة.</p></div><a href="<?= url('/providers') ?>" class="text-decoration-none fw-semibold">استكشف الجميع</a></div>
<div class="row g-3 mb-5">
 <?php if (empty($topProviders)): ?>
   <div class="empty-state"><i class="fa-regular fa-face-frown fa-2x mb-2 d-block"></i>لا يوجد مقدمو خدمات متاحون حاليًا.</div>
 <?php endif; ?>
 <?php foreach($topProviders as $p): ?><div class="col-md-4"><div class="provider-card p-4 h-100 d-flex flex-column"><div class="d-flex align-items-center gap-3 mb-3"><div class="provider-avatar"><i class="fa-solid fa-user"></i></div><div class="min-w-0"><div class="fw-bold text-truncate"><?= e($p['name']) ?></div><div class="text-muted small"><?= e($p['profession']??'') ?></div></div><?php if($p['is_verified']): ?><span class="verified-badge ms-auto"><i class="fa-solid fa-circle-check"></i> موثّق</span><?php endif; ?></div><div class="rating-stars mb-3"><?php for($i=1;$i<=5;$i++): ?><i class="fa-solid fa-star<?= $i<=round($p['avg_rating'])?'':' text-secondary opacity-25' ?>"></i><?php endfor; ?> <span class="text-muted small ms-1">(<?= (float)$p['avg_rating'] ?>)</span></div><a href="<?= url('/providers/'.$p['id']) ?>" class="btn btn-outline-primary mt-auto">عرض الملف <i class="fa-solid fa-arrow-left"></i></a></div></div><?php endforeach; ?>
</div>

<!-- ============ HOW IT WORKS ============ -->
<div id="how-it-works" class="mb-3 text-center"><h3 class="section-title mb-1">كيف تعمل المنصة</h3><p class="section-subtitle mb-0">ثلاث خطوات بسيطة من المشكلة إلى الحل.</p></div>
<div class="row g-3 mb-5">
  <div class="col-md-4"><div class="how-step"><div class="step-num">1</div><h6 class="fw-bold">حدد الخدمة</h6><p class="section-subtitle small mb-0">اكتب مشكلتك أو اختر الخدمة مباشرة من القائمة.</p></div></div>
  <div class="col-md-4"><div class="how-step"><div class="step-num">2</div><h6 class="fw-bold">اختر المهني المناسب</h6><p class="section-subtitle small mb-0">قارن التوصيات المرتّبة حسب التقييم والموقع والتوفر.</p></div></div>
  <div class="col-md-4"><div class="how-step"><div class="step-num">3</div><h6 class="fw-bold">تابع طلبك وقيّم الخدمة</h6><p class="section-subtitle small mb-0">تابع حالة الطلب لحظة بلحظة، ثم شارك تقييمك بعد الإنجاز.</p></div></div>
</div>

<!-- ============ PROVIDER CTA ============ -->
<div id="about" class="provider-cta mb-4">
  <h3 class="fw-bold mb-2">هل أنت مهني؟</h3>
  <p class="mb-4 opacity-75">انضم إلى مِهَن واعرض خدماتك لآلاف العملاء في منطقتك.</p>
  <a href="<?= url('/register?role=provider') ?>" class="btn btn-light px-4"><i class="fa-solid fa-user-plus"></i> انضم كمقدم خدمة</a>
</div>
