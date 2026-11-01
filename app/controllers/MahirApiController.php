<?php
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/MahirProfession.php';
require_once __DIR__ . '/../models/MahirSession.php';
require_once __DIR__ . '/../services/MahirAIService.php';

/**
 * MahirApiController — REST API لتطبيق Flutter
 * Base URL: /api/mahir/
 */
class MahirApiController extends Controller
{
    // ============================
    // Helper: JSON Responses
    // ============================

    private function ok(mixed $data, string $message = 'تمت العملية بنجاح'): void
    {
        if (ob_get_level() > 0) @ob_end_clean();
        http_response_code(200);
        header('Content-Type: application/json; charset=utf-8');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Session-Token');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        echo json_encode(['success' => true, 'message' => $message, 'data' => $data], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    private function fail(string $message, int $code = 400): void
    {
        if (ob_get_level() > 0) @ob_end_clean();
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Session-Token');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        echo json_encode(['success' => false, 'message' => $message, 'data' => null], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    private function payload(): array
    {
        $raw = file_get_contents('php://input');
        $json = json_decode((string)$raw, true);
        return is_array($json) ? $json : $_REQUEST;
    }

    private function handleOptions(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(204);
            header('Access-Control-Allow-Origin: *');
            header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Session-Token');
            header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
            exit;
        }
    }

    // ============================
    // GET /api/mahir/professions
    // إرجاع قائمة المهن المتاحة مجمّعة حسب التصنيف
    // ============================
    public function professions(): void
    {
        $this->handleOptions();
        $model = new MahirProfession();
        $grouped = $model->byCategory();
        $this->ok(['categories' => $grouped]);
    }

    // ============================
    // POST /api/mahir/session/start
    // Body: { "profession_id": 1 }  (اختياري)
    // إنشاء جلسة محادثة جديدة مع ماهر
    // ============================
    public function startSession(): void
    {
        $this->handleOptions();
        $payload = $this->payload();
        $professionId = isset($payload['profession_id']) ? (int)$payload['profession_id'] : null;
        $userId = null; // يمكن ربطه بـ Auth لاحقاً

        // التحقق من المهنة إذا أُرسلت
        $profession = null;
        if ($professionId) {
            $pm = new MahirProfession();
            $profession = $pm->findById($professionId);
            if (!$profession) {
                $this->fail('المهنة غير موجودة أو غير متاحة.');
            }
        }

        $sm = new MahirSession();
        $session = $sm->createSession($userId, $professionId);

        // رسالة ترحيب من ماهر
        $welcome = $profession
            ? "أهلاً! أنا ماهر 👋 سأكون معلمك في {$profession['icon']} {$profession['name']}.\n\nأخبرني ما الذي تريد تعلمه، وسأشرح لك خطوة بخطوة!"
            : "أهلاً! أنا ماهر 👋 معلمك الذكي للمهن العملية.\n\nاختر المهنة التي تريد تعلمها، أو أخبرني مباشرة ما الذي يستفسر عنه!";

        $sm->appendMessage((int)$session['id'], [
            ['role' => 'assistant', 'content' => $welcome]
        ]);

        $this->ok([
            'session_token'   => $session['session_token'],
            'session_id'      => $session['id'],
            'profession'      => $profession,
            'welcome_message' => $welcome,
        ]);
    }

    // ============================
    // POST /api/mahir/chat
    // Body: { "session_token": "...", "message": "كيف أصلح الحنفية؟" }
    // إرسال رسالة والحصول على رد ماهر
    // ============================
    public function chat(): void
    {
        $this->handleOptions();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->fail('يجب استخدام POST.', 405);
        }

        $payload = $this->payload();
        $token   = trim((string)($payload['session_token'] ?? ''));
        $message = trim((string)($payload['message'] ?? ''));

        if (empty($token)) {
            $this->fail('session_token مطلوب.');
        }
        if (empty($message)) {
            $this->fail('الرسالة لا يمكن أن تكون فارغة.');
        }
        if (mb_strlen($message) > 1000) {
            $this->fail('الرسالة طويلة جداً (الحد الأقصى 1000 حرف).');
        }

        // جلب الجلسة
        $sm = new MahirSession();
        $session = $sm->findByToken($token);
        if (!$session) {
            $this->fail('الجلسة غير موجودة أو انتهت صلاحيتها.', 404);
        }

        // جلب المهنة إن وجدت
        $profession = null;
        if ($session['profession_id']) {
            $pm = new MahirProfession();
            $profession = $pm->findById((int)$session['profession_id']);
        }

        // إرسال للذكاء الاصطناعي
        $result = MahirAIService::chat(
            $message,
            (int)$session['id'],
            $profession,
            $session['user_id'] ? (int)$session['user_id'] : null
        );

        if (!$result['success']) {
            $this->fail($result['message'], 500);
        }

        $this->ok([
            'reply'    => $result['message'],
            'provider' => $result['provider'] ?? 'gemini',
        ]);
    }

    // ============================
    // GET /api/mahir/session/{token}/history
    // إرجاع تاريخ المحادثة
    // ============================
    public function history(string $token): void
    {
        $this->handleOptions();
        $sm = new MahirSession();
        $session = $sm->findByToken($token);
        if (!$session) {
            $this->fail('الجلسة غير موجودة.', 404);
        }
        $messages = $sm->getMessages((int)$session['id']);
        $this->ok(['messages' => $messages, 'count' => count($messages)]);
    }

    // ============================
    // GET /api/mahir/professions/{id}
    // تفاصيل مهنة محددة
    // ============================
    public function professionDetail(int $id): void
    {
        $this->handleOptions();
        $pm = new MahirProfession();
        $profession = $pm->findById($id);
        if (!$profession) {
            $this->fail('المهنة غير موجودة.', 404);
        }
        $this->ok(['profession' => $profession]);
    }
}
