<h3 class="mb-4">المناطق</h3>
<div class="row g-3">
  <div class="col-md-5">
    <div class="card p-3">
      <h6>إضافة منطقة</h6>
      <form method="POST" action="<?= url('/admin/locations') ?>" class="row g-2">
        <?= csrf_field() ?>
        <div class="col-12"><input type="text" name="name_ar" class="form-control form-control-sm" placeholder="الاسم بالعربي" required></div>
        <div class="col-12"><input type="text" name="name_en" class="form-control form-control-sm" placeholder="Name in English"></div>
        <div class="col-12">
          <select name="level" class="form-select form-select-sm" required>
            <option value="governorate">محافظة</option>
            <option value="city">مدينة</option>
            <option value="district">مديرية</option>
            <option value="area">منطقة</option>
          </select>
        </div>
        <div class="col-12">
          <select name="parent_id" class="form-select form-select-sm">
            <option value="">بدون أب (مستوى أعلى)</option>
            <?php foreach ($locations as $l): ?><option value="<?= $l['id'] ?>"><?= e(localized($l['name_ar'], $l['name_en'] ?? null)) ?></option><?php endforeach; ?>
          </select>
        </div>
        <div class="col-12"><button class="btn btn-sm btn-primary w-100">إضافة</button></div>
      </form>
    </div>
  </div>
  <div class="col-md-7">
    <div class="card p-3">
      <h6>المناطق الحالية</h6>
      <table class="table table-sm">
        <thead><tr><th>الاسم</th><th>المستوى</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($locations as $l): ?>
          <tr><td><?= e(localized($l['name_ar'], $l['name_en'] ?? null)) ?></td><td><?= e($l['level']) ?></td>
          <td><form method="POST" action="<?= url('/admin/locations/' . $l['id'] . '/delete') ?>" onsubmit="return confirm('حذف؟')"><?= csrf_field() ?><button class="btn btn-sm btn-outline-danger">حذف</button></form></td></tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
