<div class="mb-4">
    <a href="/admin/provider-applications" class="btn btn-outline-secondary btn-sm mb-2">
        <i class="bi bi-arrow-right"></i> العودة لقائمة الطلبات
    </a>
    <h2 class="h4 mb-1">مراجعة طلب انضمام: <?= htmlspecialchars($application['user_name']) ?></h2>
    <p class="text-muted mb-0">تم التقديم في <?= date('Y/m/d H:i', strtotime($application['created_at'])) ?></p>
</div>

<div class="row g-4">
    <!-- Main Info & Credentials -->
    <div class="col-lg-7">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="card-title mb-0">📋 بيانات المتقدم والشهادات</h5>
            </div>
            <div class="card-body">
                <div class="row g-3 mb-3">
                    <div class="col-sm-6">
                        <label class="text-muted small">الاسم الكامل</label>
                        <div class="fw-bold fs-6"><?= htmlspecialchars($application['user_name']) ?></div>
                    </div>
                    <div class="col-sm-6">
                        <label class="text-muted small">رقم الهاتف</label>
                        <div class="fw-bold fs-6"><?= htmlspecialchars($application['user_phone'] ?: 'غير متوفر') ?></div>
                    </div>
                    <div class="col-sm-6">
                        <label class="text-muted small">البريد الإلكتروني</label>
                        <div class="fw-bold"><?= htmlspecialchars($application['user_email']) ?></div>
                    </div>
                    <div class="col-sm-6">
                        <label class="text-muted small">رقم الهوية الوطنية / الإقامة</label>
                        <div class="fw-bold"><?= htmlspecialchars($application['national_id'] ?: 'غير مرفق') ?></div>
                    </div>
                    <div class="col-sm-6">
                        <label class="text-muted small">المهنة / التخصص</label>
                        <div class="badge bg-primary fs-6"><?= htmlspecialchars($application['profession']) ?></div>
                    </div>
                    <div class="col-sm-6">
                        <label class="text-muted small">سنوات الخبرة</label>
                        <div class="fw-bold"><?= (int)$application['years_exp'] ?> سنوات</div>
                    </div>
                    <div class="col-12">
                        <label class="text-muted small">المدينة / المنطقة</label>
                        <div class="fw-bold"><?= htmlspecialchars($application['city'] ?: 'غير محدد') ?></div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="text-muted small">نبذة عن الخبرة والمهارات</label>
                    <div class="p-3 bg-light rounded text-dark">
                        <?= nl2br(htmlspecialchars($application['bio'] ?: 'لا توجد نبذة مرفقة')) ?>
                    </div>
                </div>

                <?php if (!empty($application['certificate_url'])): ?>
                    <div class="mb-3">
                        <label class="text-muted small d-block">وثيقة / شهادة إثبات المهنة</label>
                        <a href="<?= htmlspecialchars($application['certificate_url']) ?>" target="_blank" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-file-earmark-text"></i> عرض الوثيقة المرفقة
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Administrative Decision Box -->
    <div class="col-lg-5">
        <div class="card shadow-sm border-0 border-top border-4 border-primary">
            <div class="card-header bg-white py-3">
                <h5 class="card-title mb-0">⚖️ اتخاذ القرار الإداري (الإدارة فقط)</h5>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-3">
                    بناءً على فحص البيانات والشهادات المرفقة أعلاه، يمكنك قبول الطلب واعتماد المهني وتفعيل حسابه فوراً أو رفضه.
                </p>

                <?php if ($application['status'] === 'approved'): ?>
                    <div class="alert alert-success mb-0">
                        <i class="bi bi-check-circle-fill"></i> هذا الحساب <strong>مقبول ومفعل</strong> كمقدم خدمة رسمي في المنصة.
                    </div>
                <?php elseif ($application['status'] === 'rejected'): ?>
                    <div class="alert alert-secondary mb-3">
                        <i class="bi bi-x-circle-fill"></i> هذا الطلب <strong>مرفوض</strong> حالياً.
                    </div>
                <?php endif; ?>

                <?php if ($application['status'] !== 'approved'): ?>
                    <form method="POST" action="/admin/provider-applications/<?= $application['id'] ?>/approve" class="mb-3">
                        <?= csrf_field() ?>
                        <div class="mb-3">
                            <label class="form-label small">ملاحظات إدارية (اختياري)</label>
                            <input type="text" name="admin_notes" class="form-control" placeholder="مثال: تم تدقيق الهوية والشهادة">
                        </div>
                        <button type="submit" class="btn btn-success w-100 fw-bold py-2 mb-2" onclick="return confirm('هل أنت متأكد من قبول مقدم الخدمة وتفعيل حسابه؟')">
                            <i class="bi bi-check-circle"></i> قبول وتفعيل الحساب وإرسال إشعار
                        </button>
                    </form>

                    <form method="POST" action="/admin/provider-applications/<?= $application['id'] ?>/reject">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-outline-danger w-100" onclick="return confirm('هل أنت متأكد من رفض هذا الطلب؟')">
                            <i class="bi bi-x-circle"></i> رفض الطلب
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
