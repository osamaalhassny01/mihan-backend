<?php
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/MahirProfession.php';
require_once __DIR__ . '/../services/MahirAIService.php';

class AdminMahirController extends Controller
{
    public function __construct()
    {
        Auth::requireRole('admin');
    }

    // صفحة إدارة ماهر AI
    public function index(): void
    {
        $model = new MahirProfession();
        $professions = $model->allForAdmin();
        $stats = MahirAIService::getStats();

        $this->view('admin/mahir/index', [
            'title'       => 'إدارة ماهر AI',
            'professions' => $professions,
            'stats'       => $stats,
        ], 'dashboard');
    }

    // إضافة مهنة جديدة
    public function store(): void
    {
        $this->verifyCsrfOrFail();
        $model = new MahirProfession();
        try {
            $model->insert([
                'name'                => trim($this->input('name', '')),
                'name_en'             => trim($this->input('name_en', '')) ?: null,
                'category'            => trim($this->input('category', '')),
                'icon'                => trim($this->input('icon', '🔧')),
                'description'         => trim($this->input('description', '')) ?: null,
                'system_prompt_extra' => trim($this->input('system_prompt_extra', '')) ?: null,
                'is_active'           => $this->input('is_active') === '1' ? 1 : 0,
                'sort_order'          => (int)$this->input('sort_order', 0),
            ]);
            Session::flash('success', 'تمت إضافة المهنة بنجاح.');
        } catch (Throwable $e) {
            Session::flash('error', 'فشل إضافة المهنة: ' . $e->getMessage());
        }
        $this->redirect('/admin/mahir');
    }

    // تعديل مهنة
    public function update(int $id): void
    {
        $this->verifyCsrfOrFail();
        $model = new MahirProfession();
        try {
            $model->update($id, [
                'name'                => trim($this->input('name', '')),
                'name_en'             => trim($this->input('name_en', '')) ?: null,
                'category'            => trim($this->input('category', '')),
                'icon'                => trim($this->input('icon', '🔧')),
                'description'         => trim($this->input('description', '')) ?: null,
                'system_prompt_extra' => trim($this->input('system_prompt_extra', '')) ?: null,
                'is_active'           => $this->input('is_active') === '1' ? 1 : 0,
                'sort_order'          => (int)$this->input('sort_order', 0),
            ]);
            Session::flash('success', 'تم تحديث المهنة بنجاح.');
        } catch (Throwable $e) {
            Session::flash('error', 'فشل تحديث المهنة: ' . $e->getMessage());
        }
        $this->redirect('/admin/mahir');
    }

    // تفعيل/تعطيل مهنة
    public function toggle(int $id): void
    {
        $this->verifyCsrfOrFail();
        $model = new MahirProfession();
        $row = $model->find($id);
        if ($row) {
            $model->update($id, ['is_active' => $row['is_active'] ? 0 : 1]);
        }
        $this->redirect('/admin/mahir');
    }

    // حذف مهنة
    public function delete(int $id): void
    {
        $this->verifyCsrfOrFail();
        $model = new MahirProfession();
        try {
            $model->delete($id);
            Session::flash('success', 'تم حذف المهنة.');
        } catch (Throwable $e) {
            Session::flash('error', 'فشل الحذف.');
        }
        $this->redirect('/admin/mahir');
    }

    // عرض إحصائيات الاستخدام
    public function stats(): void
    {
        $stats = MahirAIService::getStats();
        $this->view('admin/mahir/stats', [
            'title' => 'إحصائيات ماهر AI',
            'stats' => $stats,
        ], 'dashboard');
    }
}
