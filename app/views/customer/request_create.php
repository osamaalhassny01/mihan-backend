<div class="d-flex justify-content-between align-items-center mb-4">
  <h3 class="mb-0"><?= __('new_request') ?></h3>
  <?php renderBackButton('/customer/dashboard', 'العودة للوحة التحكم'); ?>
</div>
<?php if (!empty($targetProvider)): ?>
  <div class="alert alert-info d-flex align-items-center gap-2 mb-3">
    <i class="fa-solid fa-user-check fa-lg"></i>
    <div>سيتم إرسال هذا الطلب مباشرة إلى <strong><?= e($targetProvider['name']) ?></strong> (<?= e($targetProvider['profession'] ?? '') ?>) بدلًا من القائمة العامة لمقدمي الخدمة.</div>
  </div>
<?php endif; ?>
<div class="row">
  <div class="col-md-7">
    <div class="card p-4">
      <form method="POST" action="<?= url('/customer/requests') ?>" data-guard>
        <?= csrf_field() ?>
        <?php if (!empty($providerId)): ?><input type="hidden" name="provider_id" value="<?= (int)$providerId ?>"><?php endif; ?>
        <div class="mb-3">
          <label class="form-label">صف مشكلتك بكلماتك (اختياري - المساعد الذكي سيقترح الخدمة المناسبة)</label>
          <textarea id="problemText" class="form-control" rows="2" placeholder="مثال: المكيف يشتغل لكن ما يبرد"></textarea>
          <button type="button" id="analyzeBtn" class="btn btn-sm btn-outline-primary mt-2"><i class="fa-solid fa-wand-magic-sparkles"></i> تحليل المشكلة بالذكاء الاصطناعي</button>
          <div id="aiResult" class="ai-analysis-result mt-2 d-none"></div>
        </div>
        <div class="mb-3">
          <label class="form-label"><?= __('services') ?></label>
          <select name="service_id" id="serviceSelect" class="form-select" required>
            <option value="">اختر الخدمة</option>
            <?php foreach ($services as $s): ?>
              <option value="<?= $s['id'] ?>" <?= (string)($preselectedServiceId ?? '') === (string)$s['id'] ? 'selected' : '' ?>><?= e(localized($s['category_name_ar'], $s['category_name_en'] ?? null)) ?> — <?= e(localized($s['name_ar'], $s['name_en'] ?? null)) ?></option>
            <?php endforeach; ?>
          </select>
          <?php if (!empty($targetProvider) && empty($services)): ?>
            <div class="form-text text-danger">لم يقم هذا المهني بإضافة خدمات بعد، الرجاء اختيار مقدم خدمة آخر.</div>
          <?php endif; ?>
        </div>
        <div class="mb-3">
          <label class="form-label">وصف تفصيلي للطلب</label>
          <textarea name="description" class="form-control" rows="3" required></textarea>
        </div>
        <div class="mb-3">
          <label class="form-label"><?= __('location') ?></label>
          <select name="location_id" class="form-select">
            <option value="">اختر المنطقة</option>
            <?php foreach ($locations as $l): ?><option value="<?= $l['id'] ?>"><?= e(localized($l['name_ar'], $l['name_en'] ?? null)) ?></option><?php endforeach; ?>
          </select>
        </div>
        <div class="row">
          <div class="col-md-6 mb-3">
            <label class="form-label">التاريخ المفضل</label>
            <input type="date" name="preferred_date" class="form-control">
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label">الوقت المفضل</label>
            <input type="time" name="preferred_time" class="form-control">
          </div>
        </div>
        <div class="mb-3">
          <label class="form-label">الميزانية التقديرية (اختياري)</label>
          <input type="number" name="budget" class="form-control">
        </div>
        <div class="mb-3">
          <label class="form-label">ملاحظات إضافية</label>
          <textarea name="notes" class="form-control" rows="2"></textarea>
        </div>
        <button class="btn btn-primary w-100"><?= __('submit') ?></button>
      </form>
    </div>
  </div>
</div>
<script>
function escapeHtml(str) {
  if (str === null || str === undefined) return '';
  const div = document.createElement('div');
  div.textContent = String(str);
  return div.innerHTML;
}

document.getElementById('analyzeBtn').addEventListener('click', function () {
  const text = document.getElementById('problemText').value;
  if (!text.trim()) return;
  fetch('<?= url('/customer/requests/analyze') ?>', {
    method: 'POST', headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    body: 'problem_text=' + encodeURIComponent(text) + '&_csrf=' + encodeURIComponent('<?= CSRF::token() ?>')
  }).then(r => r.json()).then(data => {
    const box = document.getElementById('aiResult');
    box.classList.remove('d-none');
    if (!data.matched) {
      box.innerHTML = '<div class="alert alert-warning mb-0">' + escapeHtml(data.suggestion || 'تعذر تحليل النص، اختر الخدمة يدويًا من القائمة.') + '</div>';
      return;
    }
    let alt = '';
    if (data.alternative_services && data.alternative_services.length) {
      alt = '<div class="small text-muted mt-2">خدمات بديلة: ' +
        data.alternative_services.map(s => '<span class="badge bg-light text-dark border me-1">' + escapeHtml(s.name_ar) + '</span>').join('') +
        '</div>';
    }
    box.innerHTML =
      '<div class="alert alert-info mb-0">' +
      '<div class="fw-semibold mb-1"><i class="fa-solid fa-circle-check"></i> التصنيف المقترح: ' + escapeHtml(data.category_name || '-') + '</div>' +
      '<div class="mb-1">الخدمة المقترحة: <strong>' + escapeHtml(data.service_name || '-') + '</strong></div>' +
      '<div class="small text-muted mb-1">درجة الثقة: ' + Number(data.confidence || 0) + '%</div>' +
      '<div class="small">' + escapeHtml(data.suggestion || '') + '</div>' +
      alt +
      '</div>';
    if (data.matched && data.service_id) {
      document.getElementById('serviceSelect').value = data.service_id;
    }
  }).catch(() => {
    const box = document.getElementById('aiResult');
    box.classList.remove('d-none');
    box.innerHTML = '<div class="alert alert-warning mb-0">حدث خطأ أثناء الاتصال بخدمة التحليل الذكي.</div>';
  });
});
</script>
