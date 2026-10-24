<h3 class="mb-4"><?= __('profile') ?> المهني</h3>
<div class="row g-3">
  <div class="col-md-6">
    <div class="card p-4">
      <h6 class="mb-3">البيانات الأساسية</h6>
      <?php if (!empty($user['profile_photo'])): ?>
        <img src="<?= e($user['profile_photo']) ?>" class="rounded-circle mb-3" width="90" height="90" style="object-fit:cover">
      <?php endif; ?>
      <form method="POST" action="<?= url('/provider/profile/photo') ?>" enctype="multipart/form-data" class="mb-3 d-flex gap-2">
        <?= csrf_field() ?>
        <input type="file" name="photo" class="form-control form-control-sm" accept=".jpg,.jpeg,.png,.webp" required>
        <button class="btn btn-sm btn-outline-primary">رفع الصورة</button>
      </form>
      <form method="POST" action="<?= url('/provider/profile') ?>">
        <?= csrf_field() ?>
        <div class="mb-2"><label class="form-label small"><?= __('name') ?></label><input type="text" name="name" class="form-control" value="<?= e($user['name']) ?>" required></div>
        <div class="mb-2"><label class="form-label small"><?= __('phone') ?></label><input type="text" name="phone" class="form-control" value="<?= e($user['phone']) ?>"></div>
        <div class="mb-2"><label class="form-label small"><?= __('category') ?></label>
          <select name="category_id" class="form-select">
            <option value="">اختر</option>
            <?php foreach ($categories as $c): ?><option value="<?= $c['id'] ?>" <?= $profile['category_id']==$c['id']?'selected':'' ?>><?= e(localized($c['name_ar'], $c['name_en'] ?? null)) ?></option><?php endforeach; ?>
          </select>
        </div>
        <div class="mb-2"><label class="form-label small">المهنة</label><input type="text" name="profession" class="form-control" value="<?= e($profile['profession']) ?>"></div>
        <div class="mb-2"><label class="form-label small">التخصص</label><input type="text" name="specialization" class="form-control" value="<?= e($profile['specialization']) ?>"></div>
        <div class="mb-2"><label class="form-label small">نبذة</label><textarea name="bio" class="form-control" rows="3"><?= e($profile['bio']) ?></textarea></div>
        <div class="mb-2"><label class="form-label small"><?= __('experience_years') ?></label><input type="number" name="experience_years" class="form-control" value="<?= (int)$profile['experience_years'] ?>"></div>
        <div class="form-check mb-3">
          <input class="form-check-input" type="checkbox" name="is_available" value="1" <?= $profile['is_available'] ? 'checked' : '' ?>>
          <label class="form-check-label"><?= __('available') ?> لاستقبال الطلبات</label>
        </div>
        <button class="btn btn-primary w-100"><?= __('submit') ?></button>
      </form>
    </div>
  </div>

  <div class="col-md-6">
    <div class="card p-4 mb-3">
      <h6 class="mb-3">الخدمات والأسعار</h6>
      <ul class="list-group mb-3">
        <?php foreach ($services as $s): ?>
          <li class="list-group-item d-flex justify-content-between"><?= e(localized($s['name_ar'], $s['name_en'] ?? null)) ?> <span><?= number_format($s['price_from']) ?> - <?= number_format($s['price_to']) ?></span></li>
        <?php endforeach; ?>
      </ul>
      <form method="POST" action="<?= url('/provider/profile/services') ?>" class="row g-2">
        <?= csrf_field() ?>
        <div class="col-12">
          <select name="service_id" class="form-select form-select-sm" required>
            <?php foreach ($allServices as $s): ?><option value="<?= $s['id'] ?>"><?= e(localized($s['category_name_ar'], $s['category_name_en'] ?? null)) ?> - <?= e(localized($s['name_ar'], $s['name_en'] ?? null)) ?></option><?php endforeach; ?>
          </select>
        </div>
        <div class="col-6"><input type="number" name="price_from" class="form-control form-control-sm" placeholder="من"></div>
        <div class="col-6"><input type="number" name="price_to" class="form-control form-control-sm" placeholder="إلى"></div>
        <div class="col-12"><button class="btn btn-sm btn-outline-primary w-100">إضافة/تحديث خدمة</button></div>
      </form>
    </div>

    <div class="card p-4 mb-3">
      <h6 class="mb-3">مناطق العمل</h6>
      <p><?php foreach ($locations as $l): ?><span class="badge bg-light text-dark border me-1"><?= e(localized($l['name_ar'], $l['name_en'] ?? null)) ?></span><?php endforeach; ?></p>
      <form method="POST" action="<?= url('/provider/profile/locations') ?>" class="d-flex gap-2">
        <?= csrf_field() ?>
        <select name="location_id" class="form-select form-select-sm" required>
          <?php foreach ($allLocations as $l): ?><option value="<?= $l['id'] ?>"><?= e(localized($l['name_ar'], $l['name_en'] ?? null)) ?></option><?php endforeach; ?>
        </select>
        <button class="btn btn-sm btn-outline-primary">إضافة</button>
      </form>
    </div>

    <div class="card p-4 mb-3">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="mb-0">🕐 أوقات العمل والتوفر</h6>
        <span class="badge <?= $profile['is_available'] ? 'bg-success' : 'bg-secondary' ?>">
          <?= $profile['is_available'] ? 'يستقبل الطلبات' : 'متوقف مؤقتًا' ?>
        </span>
      </div>

      <?php
        $days = [0=>'الأحد',1=>'الاثنين',2=>'الثلاثاء',3=>'الأربعاء',4=>'الخميس',5=>'الجمعة',6=>'السبت'];
      ?>
      <?php if (!empty($availability)): ?>
        <div class="table-responsive mb-3">
          <table class="table table-sm align-middle mb-0">
            <thead><tr><th>اليوم</th><th>من</th><th>إلى</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($availability as $slot): ?>
              <tr>
                <td><?= e($days[(int)$slot['day_of_week']] ?? 'غير معروف') ?></td>
                <td><?= e(substr($slot['start_time'], 0, 5)) ?></td>
                <td><?= e(substr($slot['end_time'], 0, 5)) ?></td>
                <td class="text-end">
                  <form method="POST" action="<?= url('/provider/profile/availability/' . (int)$slot['id'] . '/delete') ?>" class="d-inline">
                    <?= csrf_field() ?>
                    <button class="btn btn-sm btn-outline-danger" onclick="return confirm('حذف فترة التوفر؟')">حذف</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php else: ?>
        <div class="alert alert-light border small">لم تحدد جدولًا أسبوعيًا بعد. سيستخدم النظام حالة «يستقبل الطلبات» كإشارة التوفر الأساسية.</div>
      <?php endif; ?>

      <form method="POST" action="<?= url('/provider/profile/availability') ?>" class="row g-2">
        <?= csrf_field() ?>
        <div class="col-12 col-md-4">
          <label class="form-label small">اليوم</label>
          <select name="day_of_week" class="form-select form-select-sm" required>
            <?php foreach ($days as $dayId => $dayName): ?>
              <option value="<?= $dayId ?>"><?= e($dayName) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-6 col-md-3">
          <label class="form-label small">من</label>
          <input type="time" name="start_time" class="form-control form-control-sm" required>
        </div>
        <div class="col-6 col-md-3">
          <label class="form-label small">إلى</label>
          <input type="time" name="end_time" class="form-control form-control-sm" required>
        </div>
        <div class="col-12 col-md-2 d-flex align-items-end">
          <button class="btn btn-sm btn-outline-primary w-100">إضافة</button>
        </div>
      </form>
      <div class="form-text mt-2">يمكنك إضافة أكثر من فترة في اليوم، مثل 08:00–12:00 و16:00–20:00.</div>
    </div>

    <div class="card p-4">
      <h6 class="mb-3">معرض الأعمال</h6>
      <div class="row g-2 mb-3">
        <?php foreach ($portfolio as $img): ?><div class="col-4"><img src="<?= e($img['image_path']) ?>" class="img-fluid rounded"></div><?php endforeach; ?>
      </div>
      <form method="POST" action="<?= url('/provider/profile/portfolio') ?>" enctype="multipart/form-data" class="d-flex gap-2">
        <?= csrf_field() ?>
        <input type="file" name="image" class="form-control form-control-sm" accept=".jpg,.jpeg,.png,.webp" required>
        <button class="btn btn-sm btn-outline-primary">رفع</button>
      </form>
    </div>
  </div>
</div>
