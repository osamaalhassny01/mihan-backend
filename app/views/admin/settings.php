<?php
$values = $values ?? [];
$commission = isset($values['platform_commission_rate']) ? ((float)$values['platform_commission_rate'] * 100) : 10;
$values['platform_commission_rate'] = $commission;
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 pb-3 border-bottom" style="border-color: #d4e7e4 !important;">
  <div class="d-flex align-items-center gap-3">
    <div style="width: 50px; height: 50px; border-radius: 16px; background: #ecfdf5; color: #0f766e; display: flex; align-items: center; justify-content: center; font-size: 1.4rem; border: 1.5px solid #a7f3d0;">
      <i class="fa-solid fa-sliders"></i>
    </div>
    <div>
      <h3 class="fw-bold mb-1" style="color: #0b3734;">إعدادات وتكوين المنصة</h3>
      <p class="mb-0 small" style="color: #527976;">مركز موحد لضبط الهوية، بوابات الدفع والعمولات، وسياسات التشغيل والتسجيل</p>
    </div>
  </div>

  <form method="POST" action="<?= url('/admin/settings/reset') ?>" onsubmit="return confirm('هل أنت متأكد من استعادة كافة الإعدادات إلى القيم الافتراضية؟')">
    <?= csrf_field() ?>
    <button class="btn btn-outline-secondary btn-sm px-3 py-2 fw-bold" style="border-radius: 10px;">
      <i class="fa-solid fa-rotate-left me-1"></i> استعادة الافتراضيات
    </button>
  </form>
</div>

<!-- System Health Cards -->
<div class="row g-3 mb-4">
  <div class="col-6 col-xl-3">
    <div class="card border-0 shadow-sm p-3 h-100" style="border-radius: 16px; border: 1px solid #d4e7e4 !important;">
      <div class="text-muted small fw-bold">إجمالي المستخدمين</div>
      <div class="fs-3 fw-bold" style="color: #0b3734;"><?= number_format($health['users']) ?></div>
    </div>
  </div>
  <div class="col-6 col-xl-3">
    <div class="card border-0 shadow-sm p-3 h-100" style="border-radius: 16px; border: 1px solid #d4e7e4 !important;">
      <div class="text-muted small fw-bold">مقدمو الخدمة</div>
      <div class="fs-3 fw-bold text-success"><?= number_format($health['providers']) ?></div>
    </div>
  </div>
  <div class="col-6 col-xl-3">
    <div class="card border-0 shadow-sm p-3 h-100" style="border-radius: 16px; border: 1px solid #d4e7e4 !important;">
      <div class="text-muted small fw-bold">طلبات الخدمة</div>
      <div class="fs-3 fw-bold text-primary"><?= number_format($health['requests']) ?></div>
    </div>
  </div>
  <div class="col-6 col-xl-3">
    <div class="card border-0 shadow-sm p-3 h-100" style="border-radius: 16px; border: 1px solid #d4e7e4 !important;">
      <div class="text-muted small fw-bold">اتصال قاعدة البيانات</div>
      <div class="fw-bold text-success fs-5"><i class="fa-solid fa-circle-check me-1"></i> <?= e($health['database']) ?></div>
      <small class="text-muted font-monospace">PHP <?= e($health['php']) ?> &bull; <?= e($health['environment']) ?></small>
    </div>
  </div>
</div>

<form method="POST" action="<?= url('/admin/settings') ?>">
<?= csrf_field() ?>
<div class="row g-4">
  <div class="col-lg-8">
    <?php foreach ($groups as $group): ?>
      <section class="card border-0 shadow-sm mb-4" id="<?= e($group['id']) ?>" style="border-radius: 20px; border: 1px solid #d4e7e4 !important; background: #ffffff;">
        <div class="card-header bg-transparent border-0 p-4 pb-2">
          <div class="d-flex gap-3 align-items-start">
            <div style="width: 44px; height: 44px; border-radius: 12px; background: #ecfdf5; color: #0f766e; display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">
              <i class="fa-solid <?= e($group['icon']) ?>"></i>
            </div>
            <div>
              <h5 class="fw-bold mb-1" style="color: #0b3734;"><?= e($group['title']) ?></h5>
              <p class="text-muted small mb-0"><?= e($group['description']) ?></p>
            </div>
          </div>
        </div>
        <div class="card-body p-4 pt-3">
          <div class="row g-3">
          <?php foreach ($group['fields'] as $f): $key=$f['key']; $value=$values[$key] ?? $f['default']; ?>
            <div class="col-md-<?= in_array($f['type'],['textarea'],true) ? '12' : '6' ?>">
              <?php if ($f['type'] === 'toggle'): ?>
                <div class="form-check form-switch border rounded-3 p-3 pe-5 h-100" style="background: #f8fafc; border-color: #e2e8f0 !important;">
                  <input class="form-check-input" type="checkbox" role="switch" id="<?= e($key) ?>" name="<?= e($key) ?>" value="1" <?= in_array(strtolower((string)$value),['1','true','yes','on'],true)?'checked':'' ?>>
                  <label class="form-check-label fw-bold text-dark" for="<?= e($key) ?>"><?= e($f['label']) ?></label>
                  <div class="small text-muted mt-1"><?= e($f['help']) ?></div>
                </div>
              <?php else: ?>
                <label class="form-label small fw-bold text-dark" for="<?= e($key) ?>"><?= e($f['label']) ?></label>
                <?php if ($f['type'] === 'select'): ?>
                  <select class="form-select" id="<?= e($key) ?>" name="<?= e($key) ?>" style="border-radius: 10px;">
                    <?php foreach ($f['options'] as $ov=>$ol): ?>
                      <option value="<?= e($ov) ?>" <?= (string)$value===(string)$ov?'selected':'' ?>><?= e($ol) ?></option>
                    <?php endforeach; ?>
                  </select>
                <?php elseif ($f['type'] === 'textarea'): ?>
                  <textarea class="form-control" id="<?= e($key) ?>" name="<?= e($key) ?>" rows="3" style="border-radius: 10px;"><?= e((string)$value) ?></textarea>
                <?php else: ?>
                  <input class="form-control" type="<?= e($f['type']) ?>" id="<?= e($key) ?>" name="<?= e($key) ?>" value="<?= e((string)$value) ?>" <?= $f['type']==='number'?'min="0" max="100" step="0.01"':'' ?> style="border-radius: 10px;">
                <?php endif; ?>
                <div class="form-text small" style="color: #64748b;"><?= e($f['help']) ?></div>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
          </div>
        </div>
      </section>
    <?php endforeach; ?>
  </div>

  <div class="col-lg-4">
    <div class="card border-0 shadow-sm sticky-top" style="top: 90px; border-radius: 20px; border: 1px solid #d4e7e4 !important; background: #ffffff;">
      <div class="card-body p-4">
        <h5 class="fw-bold mb-3" style="color: #0b3734;"><i class="fa-solid fa-list-check text-primary me-2"></i>أقسام الإعدادات</h5>
        <div class="list-group list-group-flush mb-4">
          <?php foreach ($groups as $group): ?>
            <a class="list-group-item list-group-item-action px-0 border-bottom d-flex align-items-center gap-2" href="#<?= e($group['id']) ?>" style="border-color: #f1f5f9; color: #0b3734; font-weight: 600;">
              <i class="fa-solid <?= e($group['icon']) ?> text-primary"></i>
              <?= e($group['title']) ?>
            </a>
          <?php endforeach; ?>
        </div>
        <button class="btn btn-primary w-100 py-3 fw-bold" style="border-radius: 14px; background: linear-gradient(135deg, #0f766e 0%, #115e59 100%); border: none; box-shadow: 0 6px 20px rgba(15,118,110,0.3);">
          <i class="fa-solid fa-floppy-disk me-1"></i> حفظ جميع التغييرات
        </button>
      </div>
    </div>
  </div>
</div>
</form>
