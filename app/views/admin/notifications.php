<div class="row g-4">
<div class="col-lg-7">
<h3 class="mb-4">الإشعارات</h3>
<div class="list-group">
<?php foreach ($notifications as $n): ?>
<a href="<?= url($n['link'] ?? '#') ?>" class="list-group-item list-group-item-action"><div class="d-flex justify-content-between"><strong><?= e($n['title']) ?></strong><small class="text-muted"><?= formatDate($n['created_at']) ?></small></div><p class="mb-0 small"><?= e($n['message']) ?></p></a>
<?php endforeach; ?>
<?php if (empty($notifications)): ?><div class="text-muted text-center py-4">لا توجد إشعارات.</div><?php endif; ?>
</div></div>
<div class="col-lg-5">
<div class="card p-4">
<h5 class="mb-3"><i class="fa-solid fa-paper-plane text-primary"></i> إرسال إشعار</h5>
<form method="POST" action="<?= url('/admin/notifications/send') ?>">
<?= csrf_field() ?>
<div class="mb-3"><label class="form-label">المستلمون</label><select name="target" class="form-select"><option value="all">جميع المستخدمين النشطين</option><option value="customers">العملاء</option><option value="providers">مقدمو الخدمة</option><option value="admins">المديرون</option><?php foreach($users as $u): ?><option value="user:<?= (int)$u['id'] ?>"><?= e($u['name']) ?> — <?= e($u['role']) ?></option><?php endforeach; ?></select></div>
<div class="mb-3"><label class="form-label">العنوان</label><input name="title" class="form-control" maxlength="200" required></div>
<div class="mb-3"><label class="form-label">الرسالة</label><textarea name="message" class="form-control" rows="4" maxlength="500" required></textarea></div>
<div class="mb-3"><label class="form-label">الرابط (اختياري)</label><input name="link" class="form-control" maxlength="255" placeholder="/customer/requests"></div>
<button class="btn btn-primary w-100"><i class="fa-solid fa-paper-plane"></i> إرسال الإشعار</button>
</form></div></div>
</div>
