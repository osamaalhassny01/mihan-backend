<?php
require_once __DIR__ . '/AI/AIServiceFactory.php';
require_once __DIR__ . '/AI/Encryption.php';
require_once __DIR__ . '/AI/providers/GeminiProvider.php';
require_once __DIR__ . '/../models/MahirProfession.php';
require_once __DIR__ . '/../models/MahirSession.php';

class MahirAIService
{
    /**
     * الحصول على مفتاح Gemini API من قاعدة البيانات أو متغيرات البيئة
     */
    public static function getGeminiKey(): string
    {
        // 1. البحث في إعدادات المزود بقاعدة البيانات
        try {
            $db = Database::getInstance();
            $stmt = $db->query("SELECT api_key_encrypted FROM ai_provider_settings WHERE provider = 'gemini' LIMIT 1");
            $enc = $stmt->fetchColumn();
            if ($enc) {
                $dec = AIEncryption::decrypt((string)$enc);
                if (!empty($dec)) {
                    return trim($dec);
                }
            }
        } catch (Throwable $e) {}

        // 2. البحث في متغيرات البيئة
        $envKey = trim((string)(
            getenv('GEMINI_API_KEY') ?: 
            (getenv('AI_API_KEY') ?: 
            ($_ENV['GEMINI_API_KEY'] ?? 
            ($_ENV['AI_API_KEY'] ?? '')))
        ));

        return $envKey;
    }

    /**
     * التحقق مما إذا كان اتصال Gemini مهيأ
     */
    public static function isConfigured(): bool
    {
        return self::getGeminiKey() !== '';
    }

    /**
     * System prompt الأساسي لـ ماهر AI - محصور على المهن فقط
     */
    private static function buildSystemPrompt(?array $profession = null): string
    {
        $model = new MahirProfession();
        $professions = $model->allActive();
        $professionNames = array_column($professions, 'name');
        $professionList = implode('، ', $professionNames);

        $base = <<<PROMPT
أنت "ماهر"، مساعد ذكي ومعلم خبير للمهن والحرف العملية في منصة مهن.
مهمتك الأساسية هي تدريب المستخدمين وشرح خطوات المهن خطوة بخطوة بطريقة عملية ومبسطة.

قواعدك الصارمة:
1. تجيب فقط على الأسئلة المتعلقة بالمهن الحرفية والعملية (مثل: {$professionList}).
2. إذا سألك أحد عن موضوع خارج نطاق المهن والحرف، اعتذر بلطف وقل: "أنا ماهر، مدربك المهني المتخصص في تعليم الحرف والمهن العملية فقط. يمكنني تدريبك على: {$professionList}."
3. لا تجب على أي أسئلة سياسية أو دينية أو شخصية أو خارج نطاق الحرف والمهن.
4. رتّب إجاباتك بخطوات مرقمة وواضحة (خطوة 1، خطوة 2...).
5. ابدأ دائماً بذكر الأدوات ومعدات السلامة المطلوبة قبل البدء في الخطوات.
6. استخدم لغة عربية سليمة وواضحة وسهلة الفهم.
7. اسمك "ماهر" فقط، ولا تذكر مطلقاً اسم النموذج أو الشركة المطورة.

أسلوبك: مدرب حرفي محترف، صبور، يشجع المتعلم، ويقدم نصائح عملية من واقع الميدان.
PROMPT;

        if ($profession && !empty($profession['system_prompt_extra'])) {
            $base .= "\n\nتعليمات إضافية وتخصصية لمهنة {$profession['name']}:\n" . $profession['system_prompt_extra'];
        }

        return $base;
    }

    /**
     * إرسال رسالة لـ ماهر AI والحصول على رد حصرياً عبر سيرفر Google Gemini
     * (بدون أي ردود محلية معلبة)
     */
    public static function chat(
        string $userMessage,
        int $sessionId,
        ?array $profession = null,
        ?int $userId = null
    ): array {
        $geminiKey = self::getGeminiKey();

        // لا يوجد رد محلي معلب - إشعار مباشر بضرورة ربط السيرفر
        if (empty($geminiKey)) {
            return [
                'success'  => false,
                'message'  => '⚠️ لم يتم تفعيل مفتاح Google Gemini في السيرفر بعد. يُرجى حفظ المفتاح من لوحة التحكم > إدارة ماهر AI لتفعيل الإجابات الحية عبر السيرفر.',
                'provider' => 'gemini_unconfigured',
            ];
        }

        $sessionModel = new MahirSession();
        $history = $sessionModel->getMessages($sessionId);

        // بناء قائمة الرسائل مع السياق
        $messages = [
            ['role' => 'system', 'content' => self::buildSystemPrompt($profession)]
        ];

        // إضافة تاريخ المحادثة (آخر 10 رسائل)
        $recentHistory = array_slice($history, -10);
        foreach ($recentHistory as $msg) {
            $messages[] = $msg;
        }

        // إضافة رسالة المستخدم الحالية
        $messages[] = ['role' => 'user', 'content' => trim($userMessage)];

        $startTime = microtime(true);

        // جلب النموذج المحفوظ أو استخدام النموذج المعتمد
        $model = 'gemini-2.5-flash';
        try {
            $db = Database::getInstance();
            $stmt = $db->query("SELECT model FROM ai_provider_settings WHERE provider = 'gemini' LIMIT 1");
            $saved = $stmt->fetchColumn();
            if (!empty($saved) && $saved !== 'gemini-1.5-flash' && strpos($saved, '3.1') === false) {
                $model = trim((string)$saved);
            }
        } catch (Throwable $e) {}

        try {
            // اتصال حصري ومباشر بسيرفر Google Gemini مع الكشف التلقائي الذكي للنموذج
            $provider = new GeminiProvider([
                'api_key'     => $geminiKey,
                'model'       => $model,
                'base_url'    => 'https://generativelanguage.googleapis.com/v1beta',
                'temperature' => 0.4,
                'max_tokens'  => 1500,
                'timeout'     => 30,
            ]);

            $aiText = $provider->chat($messages, [
                'temperature' => 0.4,
                'max_tokens'  => 1500,
            ]);

            $durationMs = (int)round((microtime(true) - $startTime) * 1000);

            // حفظ الرسالتين في سجل الجلسة
            $sessionModel->appendMessage($sessionId, [
                ['role' => 'user',      'content' => $userMessage],
                ['role' => 'assistant', 'content' => $aiText],
            ]);

            // تسجيل الاستخدام
            self::logUsage($sessionId, $userId, $profession['id'] ?? null, $userMessage, $aiText, $durationMs);

            return [
                'success'  => true,
                'message'  => $aiText,
                'provider' => 'gemini (gemini-1.5-flash)',
            ];
        } catch (Throwable $e) {
            $safeError = preg_replace('/(api[_-]?key|authorization|bearer)\s*[:=]?\s*[^\s,;]+/iu', '[REDACTED]', $e->getMessage());
            return [
                'success' => false,
                'message' => '⚠️ فشل الاتصال بسيرفر Google Gemini: ' . $safeError,
                'error'   => $safeError,
            ];
        }
    }

    /**
     * تسجيل استخدام ماهر AI
     */
    private static function logUsage(
        int $sessionId,
        ?int $userId,
        ?int $professionId,
        string $userMsg,
        string $aiResponse,
        int $durationMs
    ): void {
        try {
            $db = Database::getInstance();
            $stmt = $db->prepare("
                INSERT INTO mahir_usage_logs 
                (session_id, user_id, profession_id, user_message, ai_response, duration_ms, created_at)
                VALUES (:s, :u, :p, :um, :ar, :d, NOW())
            ");
            $stmt->execute([
                's'  => $sessionId,
                'u'  => $userId,
                'p'  => $professionId,
                'um' => mb_substr($userMsg, 0, 1000),
                'ar' => mb_substr($aiResponse, 0, 2000),
                'd'  => $durationMs,
            ]);
        } catch (Throwable $ignore) {}
    }

    /**
     * إحصائيات ماهر AI للوحة التحكم
     */
    public static function getStats(): array
    {
        try {
            $db = Database::getInstance();
            return [
                'total_sessions'  => (int)$db->query("SELECT COUNT(*) FROM mahir_sessions")->fetchColumn(),
                'total_messages'  => (int)$db->query("SELECT COUNT(*) FROM mahir_usage_logs")->fetchColumn(),
                'today_messages'  => (int)$db->query("SELECT COUNT(*) FROM mahir_usage_logs WHERE DATE(created_at) = CURDATE()")->fetchColumn(),
                'top_professions' => $db->query("
                    SELECT p.name, p.icon, COUNT(l.id) as usage_count
                    FROM mahir_usage_logs l
                    JOIN mahir_professions p ON p.id = l.profession_id
                    GROUP BY l.profession_id
                    ORDER BY usage_count DESC
                    LIMIT 5
                ")->fetchAll(PDO::FETCH_ASSOC),
            ];
        } catch (Throwable $e) {
            return [
                'total_sessions'  => 0,
                'total_messages'  => 0,
                'today_messages'  => 0,
                'top_professions' => [],
            ];
        }
    }
}
