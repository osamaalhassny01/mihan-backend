<?php
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/MahirProfession.php';
require_once __DIR__ . '/../services/MahirAIService.php';

class AdminMahirController extends Controller
{
    public function __construct()
    {
        Auth::requireRole('admin');
        MahirProfession::ensureTablesExist();
    }

    // صفحة إدارة ماهر AI
    public function index(): void
    {
        try {
            $model = new MahirProfession();
            $professions = $model->allForAdmin();
            $stats = MahirAIService::getStats();
            $isConfigured = MahirAIService::isConfigured();

            $savedModel = 'gemini-3.6-flash';
            try {
                $db = Database::getInstance();
                $saved = $db->query("SELECT model FROM ai_provider_settings WHERE provider = 'gemini' LIMIT 1")->fetchColumn();
                $deprecated = ['gemini-1.5-flash', 'gemini-2.5-flash', 'gemini-2.0-flash'];
                if (!empty($saved) && !in_array($saved, $deprecated, true) && strpos($saved, '3.1') === false) {
                    $savedModel = trim((string)$saved);
                }
            } catch (Throwable $e) {}

            $this->view('admin/mahir/index', [
                'title'             => 'إدارة ماهر AI',
                'professions'       => $professions,
                'stats'             => $stats,
                'gemini_configured' => $isConfigured,
                'gemini_model'      => $savedModel,
            ], 'dashboard');
        } catch (Throwable $e) {
            Session::flash('error', 'حدث خطأ في تحميل بيانات ماهر AI: ' . $e->getMessage());
            $this->view('admin/mahir/index', [
                'title'             => 'إدارة ماهر AI',
                'professions'       => [],
                'stats'             => ['total_sessions' => 0, 'total_messages' => 0, 'today_messages' => 0, 'top_professions' => []],
                'gemini_configured' => false,
                'gemini_model'      => 'gemini-3.6-flash',
            ], 'dashboard');
        }
    }

    // حفظ وتفعيل مفتاح Google Gemini مباشرة
    public function saveGemini(): void
    {
        $this->verifyCsrfOrFail();
        $key = trim((string)$this->input('api_key', ''));
        if ($key === '') {
            Session::flash('error', 'يرجى إدخال مفتاح Gemini API صالح.');
            $this->redirect('/admin/mahir');
            return;
        }

        try {
            require_once __DIR__ . '/../models/AIProviderSetting.php';
            require_once __DIR__ . '/../services/AI/Encryption.php';
            require_once __DIR__ . '/../services/AI/providers/GeminiProvider.php';

            // كشف النموذج الفعال من حساب المستخدم في Google مباشرة
            $detectedModel = GeminiProvider::detectSupportedModel($key);

            $m = new AIProviderSetting();
            $data = [
                'model'             => $detectedModel ?: 'gemini-3.6-flash',
                'api_key_encrypted' => AIEncryption::encrypt($key),
                'base_url'          => 'https://generativelanguage.googleapis.com/v1beta',
                'temperature'       => 0.40,
                'max_tokens'        => 2048,
                'timeout'           => 30,
                'is_enabled'        => 1,
                'is_default'        => 1,
            ];
            $m->saveProvider('gemini', $data);
            $m->makeDefault('gemini');

            // اختبار فوري للمفتاح وتوليد محتوى حقيقي للتأكد 100%
            $provider = new GeminiProvider([
                'api_key'  => $key,
                'model'    => $detectedModel ?: 'gemini-3.6-flash',
                'base_url' => 'https://generativelanguage.googleapis.com/v1beta',
                'timeout'  => 12,
            ]);
            $test = $provider->testConnection();

            if (!empty($test['ok'])) {
                Session::flash('success', '✓ ' . ($test['message'] ?? 'تم التحقق من الاتصال بنجاح وتفعيل السيرفر.'));
            } else {
                Session::flash('warning', 'تم حفظ المفتاح، ولكن فحص التوليد أظهر: ' . ($test['message'] ?? 'فشل الاتصال'));
            }
        } catch (Throwable $e) {
            Session::flash('error', 'تعذر حفظ مفتاح Gemini: ' . $e->getMessage());
        }

        $this->redirect('/admin/mahir');
    }

    // اختبار الاتصال بـ Gemini مباشرة
    public function testGemini(): void
    {
        $this->verifyCsrfOrFail();
        $postedKey = trim((string)$this->input('api_key', ''));
        $key = $postedKey !== '' ? $postedKey : MahirAIService::getGeminiKey();

        if (empty($key)) {
            $this->json(['ok' => false, 'message' => 'لم يتم إدخال مفتاح API للاختبار.'], 400);
            return;
        }

        try {
            require_once __DIR__ . '/../services/AI/providers/GeminiProvider.php';
            $detected = GeminiProvider::detectSupportedModel($key);
            $provider = new GeminiProvider([
                'api_key'  => $key,
                'model'    => $detected ?: 'gemini-3.6-flash',
                'base_url' => 'https://generativelanguage.googleapis.com/v1beta',
                'timeout'  => 12,
            ]);
            $res = $provider->testConnection();
            $this->json($res);
        } catch (Throwable $e) {
            $this->json(['ok' => false, 'message' => 'فشل الاختبار: ' . $e->getMessage()], 422);
        }
    }

    // إضافة مهنة جديدة
    public function store(): void
    {
        $this->verifyCsrfOrFail();
        $model = new MahirProfession();
        try {
            $model->insert([
                'name'                => trim((string)$this->input('name', '')),
                'name_en'             => trim((string)$this->input('name_en', '')) ?: null,
                'category'            => trim((string)$this->input('category', '')),
                'icon'                => trim((string)$this->input('icon', '🔧')),
                'description'         => trim((string)$this->input('description', '')) ?: null,
                'system_prompt_extra' => trim((string)$this->input('system_prompt_extra', '')) ?: null,
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
                'name'                => trim((string)$this->input('name', '')),
                'name_en'             => trim((string)$this->input('name_en', '')) ?: null,
                'category'            => trim((string)$this->input('category', '')),
                'icon'                => trim((string)$this->input('icon', '🔧')),
                'description'         => trim((string)$this->input('description', '')) ?: null,
                'system_prompt_extra' => trim((string)$this->input('system_prompt_extra', '')) ?: null,
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
        try {
            $row = $model->find($id);
            if ($row) {
                $model->update($id, ['is_active' => $row['is_active'] ? 0 : 1]);
                Session::flash('success', 'تم تحديث حالة المهنة.');
            }
        } catch (Throwable $e) {
            Session::flash('error', 'فشل تغيير الحالة: ' . $e->getMessage());
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
            Session::flash('error', 'فشل الحذف: ' . $e->getMessage());
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
