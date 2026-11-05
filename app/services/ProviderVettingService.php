<?php
require_once __DIR__ . '/MahirAIService.php';
require_once __DIR__ . '/AI/providers/GeminiProvider.php';

class ProviderVettingService
{
    /**
     * Use Gemini AI to analyze a provider application and give recommendation
     */
    public static function evaluate(array $application): array
    {
        $apiKey = MahirAIService::getGeminiKey();
        if (empty($apiKey)) {
            return [
                'score' => 70,
                'recommendation' => 'review',
                'notes' => 'تم استلام الطلب بنجاح. يرجى المراجعة اليدوية (مفتاح الذكاء الاصطناعي غير متوفر حالياً).'
            ];
        }

        $prompt = "أنت مستشار توظيف وخبير تقييم مهني في منصة 'مِهَن' للخدمات المنزلية والمهنية.
المطلوب منك تقييم طلب انضمام مهني/مقدم خدمة جديد للمنصة وتقديم توصية دقيقة للمدير لمساعدته في اتخاذ القرار.

بيانات المتقدم:
- الاسم: {$application['user_name']}
- البريد: {$application['user_email']}
- رقم الهاتف: {$application['user_phone']}
- رقم الهوية الوطنية: {$application['national_id']}
- التخصص / المهنة: {$application['profession']}
- سنوات الخبرة: {$application['years_exp']} سنة
- المدينة: {$application['city']}
- نبذة عن الخبرات والأعمال: {$application['bio']}

المطلوب:
حلل هذه البيانات وقدم نتيجتك بتنسيق JSON حصراً بالشكل التالي دون أي نصوص إضافية:
{
  \"score\": (رقم صحيح من 0 إلى 100 يعبر عن مدى ملاءمة المتقدم واكتمال بياناته),
  \"recommendation\": (\"approve\" أو \"reject\" أو \"review\"),
  \"notes\": \"ملاحظات موجزة باللغة العربية توضح أسباب التوصية وتوجيهات للمدير\"
}";

        try {
            $provider = new GeminiProvider($apiKey);
            $response = $provider->chat([
                ['role' => 'user', 'content' => $prompt]
            ], [
                'temperature' => 0.2,
                'max_tokens' => 500
            ]);

            $text = trim($response['text'] ?? '');
            // Strip markdown json formatting if any
            $text = preg_replace('/^```(?:json)?\s*/i', '', $text);
            $text = preg_replace('/\s*```$/', '', $text);

            $decoded = json_decode($text, true);
            if (is_array($decoded) && isset($decoded['score'])) {
                return [
                    'score' => (int)$decoded['score'],
                    'recommendation' => in_array($decoded['recommendation'] ?? '', ['approve', 'reject', 'review']) ? $decoded['recommendation'] : 'review',
                    'notes' => (string)($decoded['notes'] ?? '')
                ];
            }
        } catch (Throwable $e) {
            error_log('Provider vetting AI error: ' . $e->getMessage());
        }

        // Fallback rule-based evaluation if API fails
        $score = 50;
        $exp = (int)($application['years_exp'] ?? 0);
        if ($exp >= 5) $score += 30;
        elseif ($exp >= 2) $score += 20;
        elseif ($exp >= 1) $score += 10;

        if (!empty($application['national_id'])) $score += 10;
        if (mb_strlen($application['bio'] ?? '') > 20) $score += 10;

        $rec = $score >= 70 ? 'approve' : ($score >= 40 ? 'review' : 'reject');

        return [
            'score' => $score,
            'recommendation' => $rec,
            'notes' => "تقييم آلي أولي: سنوات الخبرة ({$exp})، اكتمال البيانات الأساسية."
        ];
    }
}
