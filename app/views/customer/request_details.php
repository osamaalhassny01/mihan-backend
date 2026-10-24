<div class="d-flex justify-content-between align-items-center mb-3">
  <div class="d-flex align-items-center gap-2">
    <h3 class="mb-0"><?= e($request['request_number']) ?></h3>
    <?= statusBadge($request['status']) ?>
  </div>
  <?php renderBackButton('/customer/requests', 'العودة لطلباتي'); ?>
</div>
<div class="row">
  <div class="col-md-7">
    <div class="card p-4 mb-3">
      <h6>تفاصيل الطلب</h6>
      <p><strong>الخدمة:</strong> <?= e(localized($request['service_name_ar'], $request['service_name_en'] ?? null)) ?></p>
      <p><strong>الوصف:</strong> <?= e($request['description']) ?></p>
      <p><strong>المنطقة:</strong> <?= e(localized($request['location_name_ar'] ?? '-', $request['location_name_en'] ?? null)) ?></p>
      <p><strong>مقدم الخدمة:</strong> <?= e($request['provider_name'] ?? 'لم يُحدد بعد') ?></p>
      <p><strong>الميزانية:</strong> <?= $request['budget'] ? number_format($request['budget']) . ' ' . e(setting('currency', 'ريال')) : '-' ?></p>
      <?php if (in_array($request['status'], ['pending','accepted'], true)): ?><a class="btn btn-warning btn-sm mt-2" href="<?= url('/customer/requests/' . $request['id'] . '/pre-arrival') ?>"><i class="fa-solid fa-shield-heart"></i> مساعد تقليل الضرر قبل وصول الفني</a><?php endif; ?>

      <?php if (in_array($request['status'], ['new','pending','accepted'])): ?>
        <form method="POST" action="<?= url('/customer/requests/' . $request['id'] . '/cancel') ?>" onsubmit="return confirm('هل أنت متأكد من إلغاء الطلب؟')">
          <?= csrf_field() ?>
          <button class="btn btn-outline-danger btn-sm mt-2"><i class="fa-solid fa-ban"></i> إلغاء الطلب</button>
        </form>
      <?php endif; ?>
    </div>

    <?php if ($request['status'] === 'completed'): ?>
      <div class="card p-4 mb-3">
        <h6><?= __('reviews') ?></h6>
        <?php if ($review): ?>
          <div class="rating-stars mb-2 d-flex align-items-center gap-1">
            <?php for ($s=1;$s<=5;$s++): ?><i class="fa-solid fa-star<?= $s <= (int)$review['rating'] ? ' text-warning' : ' text-secondary opacity-25' ?>"></i><?php endfor; ?>
            <span class="ms-2 fw-semibold text-dark"><?= (int)$review['rating'] ?> من 5</span>
          </div>
          <p class="mb-2 text-secondary"><?= !empty($review['comment']) ? nl2br(e($review['comment'])) : '<em class="text-muted">بدون تعليق نصي</em>' ?></p>
          <div class="small text-muted mb-2"><i class="fa-regular fa-clock"></i> <?= formatDate($review['created_at']) ?></div>

          <?php if (!empty($review['provider_reply'])): ?>
            <div class="alert alert-light border p-3 mt-3">
              <div class="fw-semibold text-primary mb-1"><i class="fa-solid fa-reply"></i> رد مقدم الخدمة:</div>
              <div class="small text-dark"><?= nl2br(e($review['provider_reply'])) ?></div>
            </div>
          <?php endif; ?>
        <?php elseif (!$invoice || $invoice['payment_status'] !== 'paid'): ?>
          <div class="alert alert-warning py-2 mb-0 small">
            <i class="fa-solid fa-lock me-1"></i> يرجى سداد فاتورة الطلب أولاً لتتمكن من إضافة تقييمك لمقدم الخدمة.
          </div>
        <?php else: ?>
          <form method="POST" action="<?= url('/customer/requests/' . $request['id'] . '/review') ?>" id="customerReviewForm">
            <?= csrf_field() ?>
            <div class="mb-2">
              <label class="form-label small fw-semibold">اختر التقييم (من 1 إلى 5 نجوم):</label>
              <div class="star-rating d-flex align-items-center gap-1" id="starContainer">
                <input type="hidden" name="rating" id="reviewRatingInput" value="5" required>
                <?php for ($s=1;$s<=5;$s++): ?>
                  <i class="fa-solid fa-star fa-xl star-glyph text-warning" data-val="<?= $s ?>" style="cursor:pointer; transition: transform .15s;"></i>
                <?php endfor; ?>
                <span class="ms-2 small fw-semibold text-muted" id="ratingLabel">5 من 5 (ممتاز)</span>
              </div>
            </div>
            <textarea name="comment" class="form-control mb-2" rows="3" placeholder="اكتب تقييمك ورأيك في الخدمة..."></textarea>
            <button type="submit" class="btn btn-primary btn-sm px-3"><i class="fa-solid fa-paper-plane"></i> إرسال التقييم</button>
          </form>
          <script>
          (function() {
            const container = document.getElementById('starContainer');
            if (!container) return;
            const input = document.getElementById('reviewRatingInput');
            const label = document.getElementById('ratingLabel');
            const stars = container.querySelectorAll('.star-glyph');
            const map = {1: '1 من 5 (سيئ جداً)', 2: '2 من 5 (ضعيف)', 3: '3 من 5 (جيد)', 4: '4 من 5 (جيد جداً)', 5: '5 من 5 (ممتاز)'};

            function paint(val) {
              val = parseInt(val, 10) || 5;
              stars.forEach(s => {
                const sVal = parseInt(s.getAttribute('data-val'), 10);
                if (sVal <= val) {
                  s.classList.remove('text-secondary', 'opacity-25');
                  s.classList.add('text-warning');
                } else {
                  s.classList.remove('text-warning');
                  s.classList.add('text-secondary', 'opacity-25');
                }
              });
              if (label) label.textContent = map[val] || (val + ' من 5');
            }

            stars.forEach(s => {
              s.addEventListener('click', function() {
                const v = this.getAttribute('data-val');
                input.value = v;
                paint(v);
              });
              s.addEventListener('mouseenter', function() {
                paint(this.getAttribute('data-val'));
              });
            });

            container.addEventListener('mouseleave', function() {
              paint(input.value);
            });
            paint(input.value || 5);
          })();
          </script>
        <?php endif; ?>
      </div>
    <?php endif; ?>

    <?php if (!empty($request['provider_id']) && settingBool('internal_messaging_enabled', true) && in_array($request['status'], ['pending','accepted','in_progress','completed'], true)): ?>
      <div class="card p-4 mb-3 border-info"><div class="d-flex justify-content-between align-items-center"><div><h6 class="mb-1">التواصل مع مقدم الخدمة</h6><div class="small text-muted">جميع المراسلات المتعلقة بهذا الطلب تتم داخل منصة مِهَن.</div></div><a class="btn btn-info" href="<?= url('/customer/requests/' . $request['id'] . '/conversation') ?>"><i class="fa-solid fa-comments"></i> فتح المحادثة</a></div></div>
    <?php endif; ?>

    <?php if ($invoice): ?>
      <div class="card p-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3"><h6 class="mb-0">الفاتورة <?= e($invoice['invoice_number']) ?></h6><?= statusBadge($invoice['payment_status']) ?></div>
        <div class="row g-2 small">
          <div class="col-6"><span class="text-muted">سعر الخدمة</span><div class="fw-semibold"><?= number_format($invoice['service_price'],2) ?> <?= e(setting('currency', 'ريال')) ?></div></div>
          <div class="col-6"><span class="text-muted">إجمالي الفاتورة</span><div class="fw-semibold"><?= number_format($invoice['total_amount'],2) ?> <?= e(setting('currency', 'ريال')) ?></div></div>
          <div class="col-6"><span class="text-muted">المدفوع</span><div class="fw-semibold text-success"><?= number_format($paidAmount,2) ?> <?= e(setting('currency', 'ريال')) ?></div></div>
          <div class="col-6"><span class="text-muted">المتبقي</span><div class="fw-semibold <?= $balanceAmount>0?'text-warning':'text-success' ?>"><?= number_format($balanceAmount,2) ?> <?= e(setting('currency', 'ريال')) ?></div></div>
        </div>
        <?php if ($invoice['payment_status']==='unpaid' && $balanceAmount>0): ?><hr><div class="alert alert-info small"><strong>الدفع عبر منصة مِهَن:</strong> اختر طريقة الدفع المحاكية لإتمام السداد داخل المنصة، وبعد نجاح الدفع يحتسب النظام عمولة المنصة ويثبت صافي مستحق مقدم الخدمة.</div><form method="post" action="<?= url('/customer/invoices/'.$invoice['id'].'/pay') ?>" class="d-flex gap-2 flex-wrap"><?= csrf_field() ?><select name="method" class="form-select" style="max-width:260px"><option value="simulated_wallet">محفظة إلكترونية (محاكاة)</option><option value="simulated_card">بطاقة (محاكاة)</option></select><button class="btn btn-success">دفع <?= number_format($balanceAmount,2) ?> <?= e(setting('currency','ريال')) ?></button></form><?php endif; ?>
        <?php if ($payments): ?><hr><div class="small fw-semibold mb-2">سجل الدفعات</div><?php foreach ($payments as $pay): ?><div class="d-flex justify-content-between border-bottom py-2 small"><span><?= e(['simulated_card'=>'بطاقة محاكاة','simulated_wallet'=>'محفظة محاكاة'][$pay['method']] ?? $pay['method']) ?> · <?= e($pay['paid_at']) ?></span><strong><?= number_format($pay['amount'],2) ?></strong></div><?php endforeach; ?><?php endif; ?>
      </div>
    <?php endif; ?>
  </div>

  <div class="col-md-5">
    <div class="card p-4">
      <h6>سجل تتبع الحالة</h6>
      <ul class="list-unstyled">
        <?php foreach ($history as $h): ?>
          <li class="mb-2 border-bottom pb-2">
            <?= statusBadge($h['new_status']) ?> <span class="text-muted small"><?= formatDate($h['changed_at']) ?></span>
            <div class="small text-muted">بواسطة: <?= e($h['changed_by_name'] ?? 'النظام') ?></div>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>
</div>
