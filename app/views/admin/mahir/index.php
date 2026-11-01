<?php
$categories = [
    'صيانة_منزلية' => ['label' => 'صيانة منزلية', 'color' => 'blue'],
    'طعام'          => ['label' => 'طعام وطبخ',    'color' => 'orange'],
    'تقنية'         => ['label' => 'تقنية',          'color' => 'purple'],
    'زراعة'         => ['label' => 'زراعة',          'color' => 'green'],
    'سيارات'        => ['label' => 'سيارات',         'color' => 'red'],
    'يدوية'         => ['label' => 'حرف يدوية',     'color' => 'yellow'],
];
?>

<!-- Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <div class="bg-white rounded-xl p-5 shadow flex items-center gap-4">
        <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center text-2xl">💬</div>
        <div>
            <p class="text-gray-500 text-sm">إجمالي الجلسات</p>
            <p class="text-2xl font-bold text-gray-800"><?= number_format($stats['total_sessions']) ?></p>
        </div>
    </div>
    <div class="bg-white rounded-xl p-5 shadow flex items-center gap-4">
        <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center text-2xl">📨</div>
        <div>
            <p class="text-gray-500 text-sm">إجمالي الرسائل</p>
            <p class="text-2xl font-bold text-gray-800"><?= number_format($stats['total_messages']) ?></p>
        </div>
    </div>
    <div class="bg-white rounded-xl p-5 shadow flex items-center gap-4">
        <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center text-2xl">📅</div>
        <div>
            <p class="text-gray-500 text-sm">رسائل اليوم</p>
            <p class="text-2xl font-bold text-gray-800"><?= number_format($stats['today_messages']) ?></p>
        </div>
    </div>
</div>

<!-- Top Professions -->
<?php if (!empty($stats['top_professions'])): ?>
<div class="bg-white rounded-xl p-5 shadow mb-6">
    <h3 class="font-bold text-gray-700 mb-3">🏆 أكثر المهن استخداماً</h3>
    <div class="flex flex-wrap gap-3">
        <?php foreach ($stats['top_professions'] as $tp): ?>
        <span class="bg-blue-50 text-blue-700 px-3 py-1 rounded-full text-sm">
            <?= htmlspecialchars($tp['icon'] . ' ' . $tp['name']) ?>
            <strong>(<?= $tp['usage_count'] ?>)</strong>
        </span>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- Add Profession Form -->
<div class="bg-white rounded-xl shadow p-5 mb-6">
    <h3 class="font-bold text-gray-700 mb-4">➕ إضافة مهنة جديدة</h3>
    <form method="POST" action="<?= url('/admin/mahir/store') ?>">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(CSRF::token()) ?>">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
            <div>
                <label class="block text-sm text-gray-600 mb-1">اسم المهنة <span class="text-red-500">*</span></label>
                <input type="text" name="name" required placeholder="مثال: السباكة"
                    class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 outline-none">
            </div>
            <div>
                <label class="block text-sm text-gray-600 mb-1">الاسم بالإنجليزية</label>
                <input type="text" name="name_en" placeholder="Plumbing"
                    class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 outline-none">
            </div>
            <div>
                <label class="block text-sm text-gray-600 mb-1">الفئة <span class="text-red-500">*</span></label>
                <select name="category" required class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 outline-none">
                    <?php foreach ($categories as $key => $cat): ?>
                    <option value="<?= $key ?>"><?= $cat['label'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm text-gray-600 mb-1">الرمز التعبيري (Icon)</label>
                <input type="text" name="icon" value="🔧" placeholder="🔧"
                    class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 outline-none">
            </div>
            <div>
                <label class="block text-sm text-gray-600 mb-1">ترتيب العرض</label>
                <input type="number" name="sort_order" value="0"
                    class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 outline-none">
            </div>
            <div>
                <label class="block text-sm text-gray-600 mb-1">الحالة</label>
                <select name="is_active" class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 outline-none">
                    <option value="1">✅ مفعّل</option>
                    <option value="0">❌ معطّل</option>
                </select>
            </div>
        </div>
        <div class="mb-4">
            <label class="block text-sm text-gray-600 mb-1">وصف المهنة</label>
            <input type="text" name="description" placeholder="وصف مختصر للمهنة..."
                class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 outline-none">
        </div>
        <div class="mb-4">
            <label class="block text-sm text-gray-600 mb-1">تعليمات إضافية لـ ماهر AI (System Prompt Extra)</label>
            <textarea name="system_prompt_extra" rows="3" placeholder="تعليمات خاصة بهذه المهنة تُضاف لماهر AI..."
                class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-300 outline-none"></textarea>
            <p class="text-xs text-gray-400 mt-1">مثال: "ابدأ دائماً بذكر أدوات السلامة قبل الشرح"</p>
        </div>
        <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 text-sm font-medium">
            ➕ إضافة المهنة
        </button>
    </form>
</div>

<!-- Professions Table -->
<div class="bg-white rounded-xl shadow overflow-hidden">
    <div class="p-5 border-b flex items-center justify-between">
        <h3 class="font-bold text-gray-700">📋 المهن المتاحة (<?= count($professions) ?>)</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="text-right px-4 py-3 text-gray-500 font-medium">المهنة</th>
                    <th class="text-right px-4 py-3 text-gray-500 font-medium">الفئة</th>
                    <th class="text-right px-4 py-3 text-gray-500 font-medium">الحالة</th>
                    <th class="text-right px-4 py-3 text-gray-500 font-medium">الترتيب</th>
                    <th class="text-right px-4 py-3 text-gray-500 font-medium">إجراءات</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php if (empty($professions)): ?>
                <tr>
                    <td colspan="5" class="text-center py-8 text-gray-400">لا توجد مهن مضافة</td>
                </tr>
                <?php else: ?>
                <?php foreach ($professions as $p): ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-2">
                            <span class="text-xl"><?= htmlspecialchars($p['icon']) ?></span>
                            <div>
                                <p class="font-medium text-gray-800"><?= htmlspecialchars($p['name']) ?></p>
                                <?php if ($p['name_en']): ?>
                                <p class="text-xs text-gray-400"><?= htmlspecialchars($p['name_en']) ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3">
                        <span class="bg-gray-100 text-gray-600 px-2 py-1 rounded text-xs">
                            <?= htmlspecialchars($categories[$p['category']]['label'] ?? $p['category']) ?>
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        <?php if ($p['is_active']): ?>
                            <span class="bg-green-100 text-green-700 px-2 py-1 rounded-full text-xs">✅ مفعّل</span>
                        <?php else: ?>
                            <span class="bg-red-100 text-red-700 px-2 py-1 rounded-full text-xs">❌ معطّل</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-4 py-3 text-gray-500"><?= (int)$p['sort_order'] ?></td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-2">
                            <!-- تفعيل/تعطيل -->
                            <form method="POST" action="<?= url('/admin/mahir/' . $p['id'] . '/toggle') ?>" class="inline">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(CSRF::token()) ?>">
                                <button type="submit" class="text-xs px-2 py-1 rounded <?= $p['is_active'] ? 'bg-red-50 text-red-600 hover:bg-red-100' : 'bg-green-50 text-green-600 hover:bg-green-100' ?>">
                                    <?= $p['is_active'] ? 'تعطيل' : 'تفعيل' ?>
                                </button>
                            </form>
                            <!-- حذف -->
                            <form method="POST" action="<?= url('/admin/mahir/' . $p['id'] . '/delete') ?>" class="inline"
                                onsubmit="return confirm('هل أنت متأكد من حذف هذه المهنة؟')">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(CSRF::token()) ?>">
                                <button type="submit" class="text-xs px-2 py-1 rounded bg-gray-100 text-gray-600 hover:bg-red-100 hover:text-red-600">حذف</button>
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
