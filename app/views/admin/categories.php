<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 pb-3 border-bottom" style="border-color: #d4e7e4 !important;">
  <div class="d-flex align-items-center gap-3">
    <div style="width: 50px; height: 50px; border-radius: 16px; background: #ecfdf5; color: #0f766e; display: flex; align-items: center; justify-content: center; font-size: 1.4rem; border: 1.5px solid #a7f3d0;">
      <i class="fa-solid fa-layer-group"></i>
    </div>
    <div>
      <h3 class="fw-bold mb-1" style="color: #0b3734;">التصنيفات والخدمات المهنية</h3>
      <p class="mb-0 small" style="color: #527976;">هيكلة مجالات العمل، ربط الأيقونات، وإضافة وتعديل الخدمات وتوصيفاتها</p>
    </div>
  </div>
</div>

<div class="row g-4">
  <!-- Categories Column -->
  <div class="col-lg-6">
    <!-- Add Category Form -->
    <div class="card border-0 shadow-sm p-4 mb-4" style="border-radius: 20px; border: 1px solid #d4e7e4 !important; background: #ffffff;">
      <h5 class="fw-bold mb-3" style="color: #0b3734;"><i class="fa-solid fa-plus-circle text-primary me-2"></i>إضافة تصنيف جديد</h5>
      <form method="POST" action="<?= url('/admin/categories') ?>" class="row g-3">
        <?= csrf_field() ?>
        <div class="col-md-6">
          <label class="form-label small fw-bold text-dark">الاسم بالعربية</label>
          <input type="text" name="name_ar" class="form-control" placeholder="مثال: خدمات كهربائية" required style="border-radius: 10px;">
        </div>
        <div class="col-md-6">
          <label class="form-label small fw-bold text-dark">الاسم بالإنجليزية</label>
          <input type="text" name="name_en" class="form-control" placeholder="e.g. Electrical Services" style="border-radius: 10px;">
        </div>
        <div class="col-12">
          <label class="form-label small fw-bold text-dark">أيقونة FontAwesome</label>
          <div class="input-group">
            <span class="input-group-text bg-light text-muted border-end-0"><i class="fa-solid fa-icons"></i></span>
            <input type="text" name="icon" class="form-control border-start-0" placeholder="fa-bolt أو fa-toolbox" value="fa-toolbox" style="border-radius: 0 10px 10px 0;">
          </div>
        </div>
        <div class="col-12">
          <button class="btn btn-primary w-100 py-2 fw-bold" style="border-radius: 12px; background: linear-gradient(135deg, #0f766e 0%, #115e59 100%); border: none;">
            <i class="fa-solid fa-folder-plus me-1"></i> حفظ وإضافة التصنيف
          </button>
        </div>
      </form>
    </div>

    <!-- Existing Categories List -->
    <div class="card border-0 shadow-sm p-4" style="border-radius: 20px; border: 1px solid #d4e7e4 !important; background: #ffffff;">
      <h5 class="fw-bold mb-3" style="color: #0b3734;"><i class="fa-solid fa-list-check text-primary me-2"></i>التصنيفات الحالية (<?= count($categories) ?>)</h5>
      <ul class="list-group list-group-flush" style="max-height: 480px; overflow-y: auto;">
        <?php foreach ($categories as $c): ?>
          <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-3 border-bottom" style="border-color: #f1f5f9;">
            <div class="d-flex align-items-center gap-3">
              <div style="width: 42px; height: 42px; border-radius: 12px; background: #ecfdf5; color: #0f766e; display: flex; align-items: center; justify-content: center; font-size: 1.2rem;">
                <i class="fa-solid <?= e($c['icon']) ?>"></i>
              </div>
              <div>
                <div class="fw-bold fs-6" style="color: #0b3734;"><?= e(localized($c['name_ar'], $c['name_en'] ?? null)) ?></div>
                <small class="text-muted font-monospace"><?= e($c['icon']) ?></small>
              </div>
            </div>
            <form method="POST" action="<?= url('/admin/categories/' . $c['id'] . '/delete') ?>" onsubmit="return confirm('هل تريد حذف هذا التصنيف وكافة خدماته؟')">
              <?= csrf_field() ?>
              <button class="btn btn-sm btn-outline-danger px-3 py-1 fw-bold" style="border-radius: 8px;">
                <i class="fa-solid fa-trash-can me-1"></i> حذف
              </button>
            </form>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>

  <!-- Services Column -->
  <div class="col-lg-6">
    <!-- Add Service Form -->
    <div class="card border-0 shadow-sm p-4 mb-4" style="border-radius: 20px; border: 1px solid #d4e7e4 !important; background: #ffffff;">
      <h5 class="fw-bold mb-3" style="color: #0b3734;"><i class="fa-solid fa-plus-circle text-primary me-2"></i>إضافة خدمة فرعية</h5>
      <form method="POST" action="<?= url('/admin/services') ?>" class="row g-3">
        <?= csrf_field() ?>
        <div class="col-12">
          <label class="form-label small fw-bold text-dark">التصنيف الرئيسي</label>
          <select name="category_id" class="form-select" required style="border-radius: 10px;">
            <?php foreach ($categories as $c): ?>
              <option value="<?= $c['id'] ?>"><?= e(localized($c['name_ar'], $c['name_en'] ?? null)) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-6">
          <label class="form-label small fw-bold text-dark">اسم الخدمة (عربي)</label>
          <input type="text" name="name_ar" class="form-control" placeholder="مثال: فحص وتمديد أسلاك" required style="border-radius: 10px;">
        </div>
        <div class="col-md-6">
          <label class="form-label small fw-bold text-dark">اسم الخدمة (إنجليزي)</label>
          <input type="text" name="name_en" class="form-control" placeholder="e.g. Wire Inspection" style="border-radius: 10px;">
        </div>
        <div class="col-12">
          <label class="form-label small fw-bold text-dark">وصف تفصيلي للخدمة</label>
          <textarea name="description" class="form-control" rows="2" placeholder="اكتب نبذة عن متطلبات وطريقة تنفيذ الخدمة..." style="border-radius: 10px;"></textarea>
        </div>
        <div class="col-12">
          <button class="btn btn-primary w-100 py-2 fw-bold" style="border-radius: 12px; background: linear-gradient(135deg, #0f766e 0%, #115e59 100%); border: none;">
            <i class="fa-solid fa-wrench me-1"></i> حفظ وإضافة الخدمة
          </button>
        </div>
      </form>
    </div>

    <!-- Existing Services List -->
    <div class="card border-0 shadow-sm p-4" style="border-radius: 20px; border: 1px solid #d4e7e4 !important; background: #ffffff;">
      <h5 class="fw-bold mb-3" style="color: #0b3734;"><i class="fa-solid fa-toolbox text-primary me-2"></i>الخدمات المسجلة (<?= count($services) ?>)</h5>
      <ul class="list-group list-group-flush" style="max-height: 480px; overflow-y: auto;">
        <?php foreach ($services as $s): ?>
          <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-3 border-bottom" style="border-color: #f1f5f9;">
            <div class="min-w-0 pe-2">
              <span class="badge" style="background: #f0fdfa; color: #0f766e; border: 1px solid #ccfbf1; border-radius: 6px; font-size: 0.75rem;">
                <?= e(localized($s['category_name_ar'], $s['category_name_en'] ?? null)) ?>
              </span>
              <div class="fw-bold text-dark mt-1 fs-6"><?= e(localized($s['name_ar'], $s['name_en'] ?? null)) ?></div>
              <?php if (!empty($s['description'])): ?>
                <small class="text-muted d-block text-truncate" style="max-width: 320px;"><?= e($s['description']) ?></small>
              <?php endif; ?>
            </div>
            <form method="POST" action="<?= url('/admin/services/' . $s['id'] . '/delete') ?>" onsubmit="return confirm('هل تريد حذف هذه الخدمة؟')">
              <?= csrf_field() ?>
              <button class="btn btn-sm btn-outline-danger px-3 py-1 fw-bold" style="border-radius: 8px;">
                <i class="fa-solid fa-trash-can"></i>
              </button>
            </form>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>
</div>
