<?php
require_once __DIR__ . '/AIServiceInterface.php';
require_once __DIR__ . '/../../core/Model.php';

/**
 * RuleBasedAIService - keyword/rule-driven problem analyzer.
 * Works fully offline with no external API, satisfying the requirement
 * that the platform must function without any AI API key configured.
 */
class RuleBasedAIService implements AIServiceInterface
{
    /** category_key => [keywords...] mapped later to real category_id/service_id via DB name match */
    private array $rules = [
        'تكييف وتبريد' => ['مكيف', 'تكييف', 'يبرد', 'ما يبرد', 'حار', 'كمبروسر', 'فريون', 'تبريد'],
        'سباكة' => ['تسريب', 'تسرب', 'ماء', 'مياه', 'صنبور', 'خلاط', 'مغسلة', 'مرحاض', 'انسداد', 'حنفية', 'مواسير', 'ماسورة', 'سباك', 'مجاري'],
        'كهرباء' => ['كهرباء', 'قاطع', 'انقطاع التيار', 'شرارة', 'مصباح', 'كهربائي', 'تماس'],
        'نجارة' => ['باب', 'خشب', 'نافذة', 'خزانة', 'دولاب'],
        'صيانة أجهزة منزلية' => ['غسالة', 'ثلاجة', 'فرن', 'خلاط كهربائي', 'مايكرويف'],
        'دهان' => ['دهان', 'طلاء', 'صبغ الحائط'],
        'تنظيف' => ['تنظيف', 'نظافة', 'غبار'],
        'صيانة سيارات' => ['سيارة', 'محرك', 'إطار', 'زيت السيارة'],
        'صيانة هواتف وحواسيب' => ['موبايل', 'هاتف', 'شاشة الجوال', 'لابتوب', 'حاسوب'],
        'حدادة وألمنيوم' => ['حديد', 'بوابة', 'شباك ألمنيوم'],
        'بلاط وسيراميك' => ['بلاط', 'سيراميك', 'أرضية'],
    ];

    public function analyzeProblem(string $text): array
    {
        $normalized = $this->normalize($text);
        $bestCategory = null;
        $bestScore = 0;
        $matchedKeywords = [];

        foreach ($this->rules as $categoryName => $keywords) {
            $score = 0;
            $matched = [];
            foreach ($keywords as $kw) {
                if (mb_strpos($normalized, $this->normalize($kw)) !== false) {
                    $score++;
                    $matched[] = $kw;
                }
            }
            if ($score > $bestScore) {
                $bestScore = $score;
                $bestCategory = $categoryName;
                $matchedKeywords = $matched;
            }
        }

        if ($bestCategory === null) {
            return [
                'matched' => false,
                'category_name' => null,
                'confidence' => 0,
                'suggestion' => 'لم نتمكن من تحديد نوع المشكلة تلقائيًا. الرجاء اختيار التصنيف يدويًا من القائمة أدناه.',
                'keywords' => [],
            ];
        }

        $confidence = min(95, 45 + min($bestScore, 3) * 15);

        $catRow = null;
        $service = null;
        $alternatives = [];

        try {
            $model = new class extends Model { protected string $table = 'categories'; };
            $catRow = $model->first(['name_ar' => $bestCategory]);
            $svcModel = new class extends Model { protected string $table = 'services'; };
            if ($catRow) {
                // Prefer the service whose name best matches the customer's words,
                // instead of blindly taking the first service in the category.
                $services = $svcModel->where(['category_id' => $catRow['id']], null, 100);
                $bestServiceScore = -1;
                foreach ($services as $candidate) {
                    $serviceText = $this->normalize(($candidate['name_ar'] ?? '') . ' ' . ($candidate['name_en'] ?? ''));
                    $serviceScore = 0;
                    foreach (preg_split('/\s+/u', $serviceText, -1, PREG_SPLIT_NO_EMPTY) as $word) {
                        if (mb_strlen($word) >= 3 && mb_strpos($normalized, $word) !== false) $serviceScore++;
                    }
                    if ($serviceScore > $bestServiceScore) {
                        $bestServiceScore = $serviceScore;
                        $service = $candidate;
                    }
                }
                if (!$service) $service = $services[0] ?? null;

                if (!empty($services)) {
                    foreach ($services as $candidate) {
                        if ($service && (int)$candidate['id'] === (int)$service['id']) continue;
                        $alternatives[] = ['id' => $candidate['id'], 'name_ar' => $candidate['name_ar']];
                        if (count($alternatives) >= 3) break;
                    }
                }
            }
        } catch (Throwable $e) {
            // DB connection or query fallback: retain matched category even if database is offline
            $catRow = null;
            $service = null;
            $alternatives = [];
        }

        return [
            'matched' => true,
            'category_id' => $catRow['id'] ?? null,
            'category_name' => $bestCategory,
            'service_id' => $service['id'] ?? null,
            'service_name' => $service['name_ar'] ?? null,
            'confidence' => $confidence,
            'suggestion' => "يبدو أن مشكلتك مرتبطة بـ \"$bestCategory\". ننصحك باختيار خدمة" . ($service ? " \"{$service['name_ar']}\"" : '') . ' لمتابعة طلبك.',
            'keywords' => $matchedKeywords,
            'alternative_services' => $alternatives,
        ];
    }

    private function normalize(string $text): string
    {
        $text = mb_strtolower(trim($text));
        // Normalize common Arabic letter variants for more forgiving matching
        $text = str_replace(['أ', 'إ', 'آ'], 'ا', $text);
        $text = str_replace('ة', 'ه', $text);
        $text = str_replace('ى', 'ي', $text);
        return $text;
    }
}
