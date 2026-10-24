<h3 class="mb-4">التصنيفات والخدمات</h3>
<div class="row g-3">
  <div class="col-md-6">
    <div class="card p-3 mb-3">
      <h6>إضافة تصنيف</h6>
      <form method="POST" action="<?= url('/admin/categories') ?>" class="row g-2">
        <?= csrf_field() ?>
        <div class="col-6"><input type="text" name="name_ar" class="form-control form-control-sm" placeholder="الاسم بالعربي" required></div>
        <div class="col-6"><input type="text" name="name_en" class="form-control form-control-sm" placeholder="Name in English"></div>
        <div class="col-12"><input type="text" name="icon" class="form-control form-control-sm" placeholder="fa-toolbox (Font Awesome icon)"></div>
        <div class="col-12"><button class="btn btn-sm btn-primary w-100">إضافة</button></div>
      </form>
    </div>
    <div class="card p-3">
      <h6>التصنيفات الحالية</h6>
      <ul class="list-group list-group-flush">
        <?php foreach ($categories as $c): ?>
          <li class="list-group-item d-flex justify-content-between align-items-center">
            <span><i class="fa-solid <?= e($c['icon']) ?>"></i> <?= e(localized($c['name_ar'], $c['name_en'] ?? null)) ?></span>
            <form method="POST" action="<?= url('/admin/categories/' . $c['id'] . '/delete') ?>" onsubmit="return confirm('حذف التصنيف؟')"><?= csrf_field() ?><button class="btn btn-sm btn-outline-danger">حذف</button></form>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>
  <div class="col-md-6">
    <div class="card p-3 mb-3">
      <h6>إضافة خدمة</h6>
      <form method="POST" action="<?= url('/admin/services') ?>" class="row g-2">
        <?= csrf_field() ?>
        <div class="col-12">
          <select name="category_id" class="form-select form-select-sm" required>
            <?php foreach ($categories as $c): ?><option value="<?= $c['id'] ?>"><?= e(localized($c['name_ar'], $c['name_en'] ?? null)) ?></option><?php endforeach; ?>
          </select>
        </div>
        <div class="col-6"><input type="text" name="name_ar" class="form-control form-control-sm" placeholder="الاسم بالعربي" required></div>
        <div class="col-6"><input type="text" name="name_en" class="form-control form-control-sm" placeholder="Name in English"></div>
        <div class="col-12"><textarea name="description" class="form-control form-control-sm" placeholder="الوصف"></textarea></div>
        <div class="col-12"><button class="btn btn-sm btn-primary w-100">إضافة</button></div>
      </form>
    </div>
    <div class="card p-3">
      <h6>الخدمات الحالية</h6>
      <ul class="list-group list-group-flush" style="max-height:400px; overflow-y:auto">
        <?php foreach ($services as $s): ?>
          <li class="list-group-item d-flex justify-content-between align-items-center small">
            <span><?= e(localized($s['category_name_ar'], $s['category_name_en'] ?? null)) ?> — <?= e(localized($s['name_ar'], $s['name_en'] ?? null)) ?></span>
            <form method="POST" action="<?= url('/admin/services/' . $s['id'] . '/delete') ?>" onsubmit="return confirm('حذف الخدمة؟')"><?= csrf_field() ?><button class="btn btn-sm btn-outline-danger">حذف</button></form>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>
</div>
