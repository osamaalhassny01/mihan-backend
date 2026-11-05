<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="h4 mb-1">طلبات انضمام مقدمي الخدمة</h2>
        <p class="text-muted mb-0">مراجعة وتدقيق بيانات وشهادات المهنيين الراغبين بالانضمام للمنصة والبت فيها إدارياً</p>
    </div>
    <div class="btn-group">
        <a href="/admin/provider-applications" class="btn btn-sm <?= empty($currentStatus) ? 'btn-primary' : 'btn-outline-primary' ?>">الكل</a>
        <a href="/admin/provider-applications?status=pending" class="btn btn-sm <?= $currentStatus === 'pending' ? 'btn-primary' : 'btn-outline-primary' ?>">قيد الانتظار</a>
        <a href="/admin/provider-applications?status=approved" class="btn btn-sm <?= $currentStatus === 'approved' ? 'btn-primary' : 'btn-outline-primary' ?>">المقبولين</a>
        <a href="/admin/provider-applications?status=rejected" class="btn btn-sm <?= $currentStatus === 'rejected' ? 'btn-primary' : 'btn-outline-primary' ?>">المرفوضين</a>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>مقدم الخدمة</th>
                        <th>التخصص والمهنة</th>
                        <th>سنوات الخبرة</th>
                        <th>المدينة</th>
                        <th>الحالة</th>
                        <th>تاريخ التقديم</th>
                        <th>الإجراء الإداري</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($applications)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary"></i>
                                لا توجد طلبات انضمام حالياً
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($applications as $app): ?>
                            <tr>
                                <td><?= $app['id'] ?></td>
                                <td>
                                    <div class="fw-bold"><?= htmlspecialchars($app['user_name']) ?></div>
                                    <small class="text-muted"><?= htmlspecialchars($app['user_phone'] ?: $app['user_email']) ?></small>
                                </td>
                                <td>
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle"><?= htmlspecialchars($app['profession']) ?></span>
                                </td>
                                <td><?= (int)$app['years_exp'] ?> سنوات</td>
                                <td><?= htmlspecialchars($app['city'] ?: 'غير محدد') ?></td>
                                <td>
                                    <?php if ($app['status'] === 'pending'): ?>
                                        <span class="badge bg-warning text-dark">قيد المراجعة</span>
                                    <?php elseif ($app['status'] === 'approved'): ?>
                                        <span class="badge bg-success">معتمد ومفعل</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">مرفوض</span>
                                    <?php endif; ?>
                                </td>
                                <td class="small text-muted"><?= date('Y/m/d H:i', strtotime($app['created_at'])) ?></td>
                                <td>
                                    <a href="/admin/provider-applications/<?= $app['id'] ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-card-checklist"></i> تدقيق وقرار
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
