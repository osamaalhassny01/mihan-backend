<?php
require_once __DIR__ . '/AI/AIServiceFactory.php';
require_once __DIR__ . '/../models/MahirProfession.php';
require_once __DIR__ . '/../models/MahirSession.php';

class MahirAIService
{
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
أنت "ماهر"، مساعد ذكي متخصص في تعليم المهن العملية. مهمتك الوحيدة هي مساعدة الناس على تعلم المهن خطوة بخطوة.

قواعدك الصارمة:
1. تجيب فقط على الأسئلة المتعلقة بالمهن العملية والحرف.
2. المهن التي تُعلّمها حالياً هي: {$professionList}.
3. إذا سألك أحد عن موضوع خارج نطاق المهن، قل بلطف: "أنا ماهر، متخصص في تعليم المهن العملية فقط. يمكنني مساعدتك في تعلم: {$professionList}."
4. لا تجب على أسئلة سياسية أو دينية أو شخصية أو غير مهنية.
5. اشرح الخطوات بشكل مبسط ومتسلسل (خطوة 1، خطوة 2...).
6. ابدأ دائماً بذكر الأدوات أو المواد المطلوبة قبل الشرح.
7. أضف تنبيهات السلامة عند الحاجة.
8. استخدم اللغة العربية دائماً.
9. اسمك "ماهر" فقط، لا تذكر أنك Gemini أو AI من Google أو أي شيء آخر.

أسلوبك: مبسط، ودود، تعليمي، مثل معلم صبور يشرح لمبتدئ.
PROMPT;

        if ($profession && !empty($profession['system_prompt_extra'])) {
            $base .= "\n\nتعليمات إضافية لمهنة {$profession['name']}:\n" . $profession['system_prompt_extra'];
        }

        return $base;
    }

    /**
     * إرسال رسالة لـ ماهر AI والحصول على رد
     */
    public static function chat(
        string $userMessage,
        int $sessionId,
        ?array $profession = null,
        ?int $userId = null
    ): array {
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

        try {
            $result = AIServiceFactory::chat($messages, [
                'temperature' => 0.4,
                'max_tokens'  => 1000,
            ]);

            $aiText = $result['text'] ?? 'عذراً، لم أتمكن من الرد في الوقت الحالي.';
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
                'provider' => $result['provider'] ?? 'gemini',
            ];
        } catch (Throwable $e) {
            return [
                'success' => false,
                'message' => 'عذراً، حدث خطأ تقني. حاول مرة أخرى.',
                'error'   => $e->getMessage(),
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
            return ['total_sessions' => 0, 'total_messages' => 0, 'today_messages' => 0, 'top_professions' => []];
        }
    }
}
