<div class="d-flex justify-content-between align-items-center mb-3">
  <div>
    <h3 class="mb-1">أفضل مقدمي الخدمة لك</h3>
    <p class="text-muted mb-0">تحليل ذكي محلي يعتمد على الخدمة والموقع والتقييم والخبرة والتوفر والموثوقية لطلبك <?= e($request['request_number']) ?></p>
  </div>
  <?php renderBackButton('/customer/requests/create', 'العودة لإنشاء طلب'); ?>
</div>

<div class="row g-3">
<?php if (empty($ranked)): ?>
  <div class="alert alert-warning">لا يوجد حاليًا مقدمو خدمة متاحون لهذه الخدمة. سيتم إشعارك عند توفر أحدهم.</div>
<?php endif; ?>
<?php foreach ($ranked as $i => $p): ?>
  <div class="col-md-6">
    <div class="card provider-card p-3 h-100">
      <div class="d-flex justify-content-between">
        <div class="d-flex align-items-center mb-2">
          <i class="fa-solid fa-circle-user fa-2x me-2"></i>
          <div>
            <div class="fw-bold"><?= $i+1 ?>. <?= e($p['name']) ?></div>
            <div class="text-muted small"><?= e($p['profession'] ?? '') ?></div>
          </div>
        </div>
        <span class="badge bg-primary align-self-start">التوافق: <?= $p['score'] ?>%</span>
      </div>
      <div class="rating-stars mb-2">
        <?php for ($s=1;$s<=5;$s++): ?><i class="fa-solid fa-star<?= $s <= round($p['avg_rating']) ? '' : ' text-secondary opacity-25' ?>"></i><?php endfor; ?>
        <span class="text-muted small">(<?= (float)$p['avg_rating'] ?>)</span>
      </div>
      <div class="small text-muted mb-2">
        خبرة <?= (int)$p['experience_years'] ?> سنوات &middot;
        <?= (int)$p['completed_jobs'] ?> عملية مكتملة &middot;
        <span class="badge bg-<?= $p['is_available'] ? 'success' : 'secondary' ?>"><?= $p['is_available'] ? __('available') : __('unavailable') ?></span>
      </div>
      <?php if (!empty($p['recommendation_reasons'])): ?>
      <div class="mb-2">
        <?php foreach ($p['recommendation_reasons'] as $reason): ?>
          <span class="badge bg-light text-dark border me-1 mb-1">✓ <?= e($reason) ?></span>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
      <details class="small mb-2">
        <summary class="text-primary" style="cursor:pointer">تفاصيل درجة التوصية</summary>
        <ul class="mb-0 mt-1">
          <li>مطابقة الخدمة: <?= $p['score_breakdown']['service_match'] ?></li>
          <li>مطابقة الموقع: <?= $p['score_breakdown']['location_match'] ?></li>
          <li>التقييم: <?= $p['score_breakdown']['rating_score'] ?></li>
          <li>الخبرة: <?= $p['score_breakdown']['experience_score'] ?></li>
          <li>التوفر: <?= $p['score_breakdown']['availability_score'] ?></li>
          <li>الموثوقية: <?= $p['score_breakdown']['reliability_score'] ?></li>
          <li>التوثيق: <?= !empty($p['score_breakdown']['verified']) ? 'موثّق' : 'غير موثّق' ?></li>
        </ul>
      </details>
      <div class="d-flex gap-2 mt-auto">
        <a href="<?= url('/providers/' . $p['id']) ?>" class="btn btn-sm btn-outline-primary flex-fill"><?= __('view_profile') ?></a>
        <form method="POST" action="<?= url('/customer/requests/' . $request['id'] . '/assign') ?>" class="flex-fill">
          <?= csrf_field() ?>
          <input type="hidden" name="provider_id" value="<?= $p['id'] ?>">
          <button class="btn btn-sm btn-primary w-100">اختر هذا المقدم</button>
        </form>
      </div>
    </div>
  </div>
<?php endforeach; ?>
</div>
