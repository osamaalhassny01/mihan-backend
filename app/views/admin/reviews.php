<div class="d-flex justify-content-between align-items-center mb-4">
  <div><h3 class="mb-1">إدارة تقييمات العملاء</h3><div class="text-muted small">مراجعة التقييمات المنشورة وحذف التقييمات المخالفة مع إعادة حساب متوسط مقدم الخدمة.</div></div>
</div>
<div class="card p-3 mb-4">
<form method="GET" class="row g-2">
  <div class="col-md-3"><label class="form-label small">مقدم الخدمة</label><select name="provider_id" class="form-select form-select-sm"><option value="">كل مقدمي الخدمة</option><?php foreach($providers as $p): ?><option value="<?= (int)$p['id'] ?>" <?= ($filters['provider_id']??'')==$p['id']?'selected':'' ?>><?= e($p['name']) ?></option><?php endforeach; ?></select></div>
  <div class="col-md-2"><label class="form-label small">التقييم</label><select name="rating" class="form-select form-select-sm"><option value="">كل التقييمات</option><?php for($i=5;$i>=1;$i--): ?><option value="<?= $i ?>" <?= ($filters['rating']??'')==$i?'selected':'' ?>><?= $i ?> ★</option><?php endfor; ?></select></div>
  <div class="col-md-2 d-flex align-items-end"><button class="btn btn-primary btn-sm w-100">تطبيق</button></div>
</form>
</div>
<div class="card p-3"><div class="table-responsive"><table class="table table-hover align-middle"><thead><tr><th>العميل</th><th>مقدم الخدمة</th><th>الخدمة</th><th>التقييم</th><th>التعليق</th><th>التاريخ</th><th></th></tr></thead><tbody>
<?php foreach($reviews as $r): ?><tr>
<td><?= e($r['customer_name']) ?></td><td><?= e($r['provider_name']) ?></td><td><?= e(localized($r['service_name_ar'],$r['service_name_en']??null)) ?></td>
<td><span class="rating-stars"><?php for($i=1;$i<=5;$i++): ?><i class="fa-solid fa-star<?= $i <= (int)$r['rating'] ? '' : ' text-secondary opacity-25' ?>"></i><?php endfor; ?></span></td>
<td style="max-width:360px"><?= e($r['comment'] ?? '') ?><?php if(!empty($r['provider_reply'])): ?><div class="small text-muted mt-1">رد المقدم: <?= e($r['provider_reply']) ?></div><?php endif; ?></td>
<td><?= formatDate($r['created_at']) ?></td>
<td><form method="POST" action="<?= url('/admin/reviews/'.$r['id'].'/delete') ?>" onsubmit="return confirm('هل أنت متأكد من حذف هذا التقييم؟')"><?= csrf_field() ?><button class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-trash"></i> حذف</button></form></td>
</tr><?php endforeach; ?>
<?php if(empty($reviews)): ?><tr><td colspan="7" class="text-center text-muted py-4">لا توجد تقييمات مطابقة.</td></tr><?php endif; ?>
</tbody></table></div></div>
