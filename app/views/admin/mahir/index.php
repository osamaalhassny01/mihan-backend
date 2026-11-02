<?php
$categories = [
    'صيانة_منزلية' => ['label' => 'صيانة منزلية', 'color' => 'primary'],
    'طعام'          => ['label' => 'طعام وطبخ',    'color' => 'warning'],
    'تقنية'         => ['label' => 'تقنية وإلكترونيات', 'color' => 'info'],
    'زراعة'         => ['label' => 'زراعة وحدائق', 'color' => 'success'],
    'سيارات'        => ['label' => 'صيانة سيارات', 'color' => 'danger'],
    'يدوية'         => ['label' => 'حرف وخياطة',   'color' => 'secondary'],
];
?>

<div class="container-fluid px-0">
    <!-- Header Title -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h4 font-weight-bold mb-1">🛠️ إدارة ماهر AI — المعلم والمدرب الذكي للمهن</h2>
            <p class="text-muted small mb-0">التحكم في المهن المتاحة للمستخدمين، وتوجيهات الذكاء الاصطناعي، ومراقبة الجلسات.</p>
        </div>
    </div>

    <!-- Gemini AI Server Setup Card (Direct, 1-Click, Minimal) -->
    <div class="card shadow-sm border-0 rounded-3 mb-4 <?= !empty($gemini_configured) ? 'border-start border-success border-4' : 'border-start border-warning border-4' ?>">
        <div class="card-body p-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
                <div>
                    <h5 class="fw-bold mb-1 d-flex align-items-center gap-2">
                        <span>⚡</span>
                        <span>ربط سيرفر الذكاء الاصطناعي (Google Gemini)</span>
                    </h5>
                    <div class="text-muted small">
                        مودل التشغيل المعتمد: <code class="fw-bold text-primary">gemini-1.5-flash</code> (رسمي وسريع، ومضبوط تلقائياً بدون الحاجة لأي إعدادات توكن أو مودل).
                    </div>
                </div>
                <div>
                    <?php if (!empty($gemini_configured)): ?>
                        <span class="badge bg-success bg-opacity-10 text-success border border-success px-3 py-2 fs-7 rounded-pill">
                            <i class="fa-solid fa-circle-check me-1"></i> الاتصال الخارجي بالسيرفر مفعّل ونشط
                        </span>
                    <?php else: ?>
                        <span class="badge bg-warning bg-opacity-10 text-warning border border-warning px-3 py-2 fs-7 rounded-pill">
                            <i class="fa-solid fa-triangle-exclamation me-1"></i> بانتظار إضافة مفتاح Gemini API
                        </span>
                    <?php endif; ?>
                </div>
            </div>

            <form method="POST" action="<?= url('/admin/mahir/save-gemini') ?>" id="geminiMahirForm">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(CSRF::token()) ?>">
                <div class="row g-2 align-items-center">
                    <div class="col-md-7">
                        <label class="form-label small fw-bold mb-1">مفتاح API الخاص بك من Google AI Studio (Gemini API Key)</label>
                        <input type="password" name="api_key" id="mahirGeminiApiKey" class="form-control" placeholder="<?= !empty($gemini_configured) ? '•••••••••••••••••••••••• (المفتاح محفوظ — اتركه فارغاً للاحتفاظ به أو أدخل جديداً)' : 'أدخل مفتاح AIzaSy... هنا' ?>" autocomplete="new-password">
                    </div>
                    <div class="col-md-5 d-flex gap-2 pt-md-4">
                        <button type="submit" class="btn btn-primary px-3 flex-grow-1">
                            <i class="fa-solid fa-floppy-disk me-1"></i> حفظ وتفعيل
                        </button>
                        <button type="button" class="btn btn-outline-success px-3" id="btnTestMahirGemini">
                            <i class="fa-solid fa-plug me-1"></i> فحص الاتصال
                        </button>
                    </div>
                </div>
                <div id="mahirTestResult" class="small mt-2" aria-live="polite"></div>
            </form>
        </div>
    </div>

    <!-- Stats Row -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-primary bg-opacity-10 text-primary p-3 fs-3 d-flex align-items-center justify-center" style="width: 54px; height: 54px;">
                        💬
                    </div>
                    <div>
                        <div class="text-muted small">إجمالي الجلسات</div>
                        <div class="fs-4 fw-bold text-dark"><?= number_format((int)($stats['total_sessions'] ?? 0)) ?></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-success bg-opacity-10 text-success p-3 fs-3 d-flex align-items-center justify-center" style="width: 54px; height: 54px;">
                        📨
                    </div>
                    <div>
                        <div class="text-muted small">إجمالي الرسائل التعليمية</div>
                        <div class="fs-4 fw-bold text-dark"><?= number_format((int)($stats['total_messages'] ?? 0)) ?></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-warning bg-opacity-10 text-warning p-3 fs-3 d-flex align-items-center justify-center" style="width: 54px; height: 54px;">
                        📅
                    </div>
                    <div>
                        <div class="text-muted small">رسائل اليوم</div>
                        <div class="fs-4 fw-bold text-dark"><?= number_format((int)($stats['today_messages'] ?? 0)) ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Professions -->
    <?php if (!empty($stats['top_professions'])): ?>
    <div class="card shadow-sm border-0 rounded-3 mb-4">
        <div class="card-body">
            <h6 class="fw-bold text-dark mb-3">🏆 أكثر المهن طلباً وتعلماً</h6>
            <div class="d-flex flex-wrap gap-2">
                <?php foreach ($stats['top_professions'] as $tp): ?>
                <span class="badge bg-light text-dark border px-3 py-2 rounded-pill fs-7">
                    <?= htmlspecialchars(($tp['icon'] ?? '🛠️') . ' ' . ($tp['name'] ?? '')) ?>
                    <strong class="text-primary ms-1">(<?= (int)($tp['usage_count'] ?? 0) ?> سؤال)</strong>
                </span>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Add Profession Card -->
    <div class="card shadow-sm border-0 rounded-3 mb-4">
        <div class="card-header bg-white border-bottom py-3">
            <h6 class="fw-bold mb-0 text-dark">➕ إضافة مهنة جديدة إلى ماهر AI</h6>
        </div>
        <div class="card-body">
            <form method="POST" action="<?= url('/admin/mahir/store') ?>">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(CSRF::token()) ?>">
                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">اسم المهنة بالعربية <span class="text-danger">*</span></label>
                        <input type="text" name="name" required placeholder="مثال: السباكة" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">الاسم بالإنجليزية</label>
                        <input type="text" name="name_en" placeholder="Plumbing" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">الفئة <span class="text-danger">*</span></label>
                        <select name="category" required class="form-select form-select-sm">
                            <?php foreach ($categories as $key => $cat): ?>
                            <option value="<?= $key ?>"><?= $cat['label'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">الرمز التعبيري (Emoji/Icon)</label>
                        <input type="text" name="icon" value="🔧" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">ترتيب الظهور</label>
                        <input type="number" name="sort_order" value="0" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">الحالة</label>
                        <select name="is_active" class="form-select form-select-sm">
                            <option value="1">✅ مفعّل ونشط</option>
                            <option value="0">❌ معطّل مؤقتاً</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">وصف المهنة</label>
                        <input type="text" name="description" placeholder="وصف مختصر لمجال المهنة..." class="form-control form-control-sm">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold">توجيهات إضافية خاصة بهذه المهنة (System Prompt Extra)</label>
                    <textarea name="system_prompt_extra" rows="2" placeholder="توجيهات محددة لماهر عند الإجابة عن هذه المهنة (مثل: ركز على أدوات السلامة أولاً...)" class="form-control form-control-sm"></textarea>
                </div>
                <button type="submit" class="btn btn-primary btn-sm px-4">
                    <i class="fa fa-plus me-1"></i> حفظ وإضافة المهنة
                </button>
            </form>
        </div>
    </div>

    <!-- Professions Table Card -->
    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0 text-dark">📋 مسارات المهن الحالية (<?= count($professions) ?>)</h6>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="border-0">المهنة</th>
                        <th class="border-0">الفئة</th>
                        <th class="border-0">الحالة</th>
                        <th class="border-0">الترتيب</th>
                        <th class="border-0 text-center">إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($professions)): ?>
                    <tr>
                        <td colspan="5" class="text-center py-4 text-muted">لا توجد مهن مسجلة حالياً.</td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($professions as $p): ?>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <span class="fs-4"><?= htmlspecialchars((string)($p['icon'] ?? '🛠️')) ?></span>
                                <div>
                                    <div class="fw-bold text-dark"><?= htmlspecialchars((string)($p['name'] ?? '')) ?></div>
                                    <?php if (!empty($p['name_en'])): ?>
                                    <small class="text-muted"><?= htmlspecialchars((string)$p['name_en']) ?></small>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-<?= $categories[$p['category']]['color'] ?? 'secondary' ?> bg-opacity-10 text-<?= $categories[$p['category']]['color'] ?? 'secondary' ?>">
                                <?= htmlspecialchars($categories[$p['category']]['label'] ?? ($p['category'] ?? 'عام')) ?>
                            </span>
                        </td>
                        <td>
                            <?php if (!empty($p['is_active'])): ?>
                                <span class="badge bg-success bg-opacity-10 text-success">مفعّل</span>
                            <?php else: ?>
                                <span class="badge bg-danger bg-opacity-10 text-danger">معطّل</span>
                            <?php endif; ?>
                        </td>
                        <td><?= (int)($p['sort_order'] ?? 0) ?></td>
                        <td class="text-center">
                            <div class="btn-group btn-group-sm">
                                <form method="POST" action="<?= url('/admin/mahir/' . $p['id'] . '/toggle') ?>" class="d-inline">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(CSRF::token()) ?>">
                                    <button type="submit" class="btn btn-outline-secondary btn-sm" title="تبديل الحالة">
                                        <?= !empty($p['is_active']) ? 'تعطيل' : 'تفعيل' ?>
                                    </button>
                                </form>
                                <form method="POST" action="<?= url('/admin/mahir/' . $p['id'] . '/delete') ?>" class="d-inline ms-1" onsubmit="return confirm('هل أنت متأكد من حذف هذه المهنة؟');">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(CSRF::token()) ?>">
                                    <button type="submit" class="btn btn-outline-danger btn-sm" title="حذف">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.getElementById('btnTestMahirGemini')?.addEventListener('click', async () => {
    const box = document.getElementById('mahirTestResult');
    const keyInput = document.getElementById('mahirGeminiApiKey');
    box.textContent = 'جارٍ فحص الاتصال بسيرفر Google Gemini...';
    box.className = 'small mt-2 text-muted';
    
    const body = new URLSearchParams();
    body.append('csrf_token', '<?= htmlspecialchars(CSRF::token()) ?>');
    if (keyInput && keyInput.value.trim() !== '') {
        body.append('api_key', keyInput.value.trim());
    }

    try {
        const res = await fetch('<?= url('/admin/mahir/test-gemini') ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: body
        });
        const data = await res.json();
        box.textContent = (data.ok ? '✓ ' : '✕ ') + (data.message || '');
        box.className = 'small mt-2 ' + (data.ok ? 'text-success fw-bold' : 'text-danger fw-bold');
    } catch (e) {
        box.textContent = '✕ تعذر إجراء الفحص: ' + e.message;
        box.className = 'small mt-2 text-danger fw-bold';
    }
});
</script>
