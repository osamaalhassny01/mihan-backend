<?php
require_once __DIR__ . '/../AIProviderInterface.php';
require_once __DIR__ . '/../RuleBasedAIService.php';
class RuleBasedAIProvider implements AIProviderInterface
{
    public function name(): string { return 'local'; }
    public function analyzeProblem(string $text): array
    {
        return (new RuleBasedAIService())->analyzeProblem($text);
    }
    public function chat(array $messages, array $options = []): string
    {
        $last = '';
        foreach (array_reverse($messages) as $m) if (($m['role'] ?? '') === 'user') { $last = (string)$m['content']; break; }
        if (str_contains($last, '[MIHAN_PREARRIVAL]')) {
            $t=mb_strtolower($last);
            if (str_contains($t,'ماء') || str_contains($t,'سباك') || str_contains($t,'تسرب') || str_contains($t,'ماسورة') || str_contains($t,'قصبة') || str_contains($t,'pipe')) {
                return 'حل مؤقت وآمن إلى أن يصل السباك:
1) إذا كان الوصول آمنًا، أغلق محبس الماء الأقرب للخط المتضرر أو المحبس الرئيسي.\n2) أبعد الأجهزة والمقابس الكهربائية عن الماء، ولا تلمس الكهرباء وأنت واقف في الماء.\n3) ضع دلوًا/مناشف لاحتواء الماء وتقليل انتشاره.\n4) لا تحاول فك أو قص الأنبوب أو استخدام أدوات قد تزيد الكسر.\n5) أخبر السباك بمكان الكسر، وهل تم إغلاق الماء، وحجم التسرب.\nإذا كان الماء قريبًا من الكهرباء أو الفيضان شديدًا، ابتعد عن المكان واطلب مساعدة مختص فورًا.';
            }
            return 'حتى يصل الفني: أوقف مصدر الضرر إن كان ذلك آمنًا، أبعد الأشخاص عن الخطر، واحتوِ المشكلة مؤقتًا دون تفكيك أو إصلاحات خطرة. صف لي نوع المشكلة وماذا يحدث الآن لأعطيك خطوات أدق.';
        }
        if (str_contains($last, '[MIHAN_FIELD]')) {
            return 'ابدأ بفحص السلامة ومصدر المشكلة قبل فك أي جزء. ثبّت الحالة، اجمع الأعراض والقياسات المتاحة، ثم نفّذ أبسط اختبار غير خطِر. لا تتجاوز حدود خبرتك، خصوصًا مع الكهرباء والغاز وضغط المياه. إذا وصفت لي ما وجدته في الموقع سأرتب لك خطوات الفحص والإصلاح والأدوات المحتملة.';
        }
        $r = $this->analyzeProblem($last);
        if (!empty($r['matched'])) return ($r['suggestion'] ?? 'تم تحليل المشكلة.') . ' يمكنك أيضًا فتح قائمة مقدمي الخدمة المناسبين من النتيجة.';
        return 'المساعد المحلي يعمل بدون نموذج لغوي خارجي. صف المشكلة بتفصيل أكبر، مثل نوع الجهاز والأعراض ومتى بدأت المشكلة.';
    }
    public function testConnection(): array { return ['ok' => true, 'message' => 'المحرك المحلي جاهز ولا يحتاج إلى API Key.']; }
}
