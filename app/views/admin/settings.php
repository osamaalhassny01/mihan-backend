<?php
$values = $values ?? [];
$commission = isset($values['platform_commission_rate']) ? ((float)$values['platform_commission_rate'] * 100) : 10;
$values['platform_commission_rate'] = $commission;
?>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
  <div>
    <div class="text-muted small mb-1"><i class="fa-solid fa-gear"></i> مركز التحكم</div>
    <h2 class="fw-bold mb-1">إعدادات المنصة</h2>
    <p class="text-muted mb-0">مركز موحد لإدارة الهوية، الوصول، التشغيل، المالية، الإشعارات والأمان.</p>
  </div>
  <form method="POST" action="<?= url('/admin/settings/reset') ?>" onsubmit="return confirm('إعادة جميع الإعدادات إلى القيم الافتراضية؟')">
    <?= csrf_field() ?>
    <button class="btn btn-outline-secondary"><i class="fa-solid fa-rotate-left"></i> استعادة الافتراضيات</button>
  </form>
</div>

<div class="row g-3 mb-4">
  <div class="col-6 col-xl-3"><div class="card border-0 shadow-sm h-100 p-3"><div class="text-muted small">المستخدمون</div><div class="fs-3 fw-bold"><?= number_format($health['users']) ?></div></div></div>
  <div class="col-6 col-xl-3"><div class="card border-0 shadow-sm h-100 p-3"><div class="text-muted small">مقدمو الخدمة</div><div class="fs-3 fw-bold"><?= number_format($health['providers']) ?></div></div></div>
  <div class="col-6 col-xl-3"><div class="card border-0 shadow-sm h-100 p-3"><div class="text-muted small">طلبات الخدمة</div><div class="fs-3 fw-bold"><?= number_format($health['requests']) ?></div></div></div>
  <div class="col-6 col-xl-3"><div class="card border-0 shadow-sm h-100 p-3"><div class="text-muted small">حالة النظام</div><div class="fw-bold text-success"><i class="fa-solid fa-circle-check"></i> <?= e($health['database']) ?></div><small class="text-muted">PHP <?= e($health['php']) ?> · <?= e($health['environment']) ?></small></div></div>
</div>

<form method="POST" action="<?= url('/admin/settings') ?>">
<?= csrf_field() ?>
<div class="row g-4">
  <div class="col-lg-8">
    <?php foreach ($groups as $group): ?>
      <section class="card border-0 shadow-sm mb-4" id="<?= e($group['id']) ?>">
        <div class="card-header bg-transparent border-0 p-4 pb-2">
          <div class="d-flex gap-3 align-items-start"><div class="rounded-circle bg-primary-subtle text-primary p-2"><i class="fa-solid <?= e($group['icon']) ?>"></i></div><div><h5 class="fw-bold mb-1"><?= e($group['title']) ?></h5><p class="text-muted small mb-0"><?= e($group['description']) ?></p></div></div>
        </div>
        <div class="card-body p-4 pt-3">
          <div class="row g-3">
          <?php foreach ($group['fields'] as $f): $key=$f['key']; $value=$values[$key] ?? $f['default']; ?>
            <div class="col-md-<?= in_array($f['type'],['textarea'],true) ? '12' : '6' ?>">
              <?php if ($f['type'] === 'toggle'): ?>
                <div class="form-check form-switch border rounded-3 p-3 pe-5 h-100">
                  <input class="form-check-input" type="checkbox" role="switch" id="<?= e($key) ?>" name="<?= e($key) ?>" value="1" <?= in_array(strtolower((string)$value),['1','true','yes','on'],true)?'checked':'' ?>>
                  <label class="form-check-label fw-semibold" for="<?= e($key) ?>"><?= e($f['label']) ?></label>
                  <div class="small text-muted mt-1"><?= e($f['help']) ?></div>
                </div>
              <?php else: ?>
                <label class="form-label fw-semibold" for="<?= e($key) ?>"><?= e($f['label']) ?></label>
                <?php if ($f['type'] === 'select'): ?>
                  <select class="form-select" id="<?= e($key) ?>" name="<?= e($key) ?>"><?php foreach ($f['options'] as $ov=>$ol): ?><option value="<?= e($ov) ?>" <?= (string)$value===(string)$ov?'selected':'' ?>><?= e($ol) ?></option><?php endforeach; ?></select>
                <?php elseif ($f['type'] === 'textarea'): ?>
                  <textarea class="form-control" id="<?= e($key) ?>" name="<?= e($key) ?>" rows="3"><?= e((string)$value) ?></textarea>
                <?php else: ?>
                  <input class="form-control" type="<?= e($f['type']) ?>" id="<?= e($key) ?>" name="<?= e($key) ?>" value="<?= e((string)$value) ?>" <?= $f['type']==='number'?'min="0" max="100" step="0.01"':'' ?>>
                <?php endif; ?>
                <div class="form-text"><?= e($f['help']) ?></div>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
          </div>
        </div>
      </section>
    <?php endforeach; ?>
  </div>
  <div class="col-lg-4">
    <div class="card border-0 shadow-sm sticky-top" style="top:1rem">
      <div class="card-body p-4">
        <h5 class="fw-bold mb-3"><i class="fa-solid fa-list-check text-primary"></i> أقسام الإعدادات</h5>
        <div class="list-group list-group-flush mb-4">
          <?php foreach ($groups as $group): ?><a class="list-group-item list-group-item-action px-0" href="#<?= e($group['id']) ?>"><i class="fa-solid <?= e($group['icon']) ?> fa-fw me-2"></i><?= e($group['title']) ?></a><?php endforeach; ?>
        </div>
        <div class="alert alert-warning small mb-3"><i class="fa-solid fa-triangle-exclamation"></i> التغييرات التشغيلية تؤثر مباشرة على سلوك المنصة. لا تفعل وضع الصيانة إلا عند الحاجة.</div>
        <button class="btn btn-primary w-100 btn-lg"><i class="fa-solid fa-floppy-disk"></i> حفظ كل الإعدادات</button>
      </div>
    </div>
  </div>
</div>
</form>
