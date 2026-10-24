<?php
require_once __DIR__ . '/../AIProviderInterface.php';
class OpenAICompatibleProvider implements AIProviderInterface
{
    protected string $provider; protected string $apiKey; protected string $model; protected string $baseUrl; protected float $temperature; protected int $maxTokens; protected int $timeout;
    public function __construct(array $settings)
    {
        $this->provider = $settings['provider']; $this->apiKey = $settings['api_key']; $this->model = $settings['model'] ?: 'gpt-4o-mini';
        $this->baseUrl = rtrim($settings['base_url'] ?: 'https://api.openai.com/v1', '/');
        $this->temperature = (float)($settings['temperature'] ?? 0.2); $this->maxTokens = (int)($settings['max_tokens'] ?? 700); $this->timeout = max(5, min(60, (int)($settings['timeout'] ?? 20)));
        $this->validateBaseUrl();
        if ($this->apiKey === '') throw new RuntimeException('API Key غير مُدخل.');
    }
    public function name(): string { return $this->provider; }
    protected function validateBaseUrl(): void
    {
        $p = parse_url($this->baseUrl);
        if (!$p || !in_array(strtolower($p['scheme'] ?? ''), ['https','http'], true) || empty($p['host'])) throw new RuntimeException('Base URL غير صالح.');
        if (in_array(strtolower($p['host']), ['localhost','127.0.0.1','0.0.0.0','::1'], true)) throw new RuntimeException('Base URL محلي غير مسموح به.');
    }
    protected function request(array $messages, array $options = []): string
    {
        $payload = ['model'=>$this->model,'messages'=>$messages,'temperature'=>$options['temperature'] ?? $this->temperature,'max_tokens'=>$options['max_tokens'] ?? $this->maxTokens];
        $ch = curl_init($this->baseUrl . '/chat/completions');
        curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_POST=>true,CURLOPT_HTTPHEADER=>['Content-Type: application/json','Authorization: Bearer '.$this->apiKey],CURLOPT_POSTFIELDS=>json_encode($payload,JSON_UNESCAPED_UNICODE),CURLOPT_TIMEOUT=>$this->timeout]);
        $body=curl_exec($ch); $err=curl_error($ch); $status=(int)curl_getinfo($ch,CURLINFO_HTTP_CODE); curl_close($ch);
        if ($err || !$body) throw new RuntimeException('فشل الاتصال بمزود الذكاء الاصطناعي.');
        $data=json_decode($body,true); if ($status<200 || $status>=300) throw new RuntimeException('المزود رفض الطلب (HTTP '.$status.').');
        $content=$data['choices'][0]['message']['content'] ?? null; if (!is_string($content) || trim($content)==='') throw new RuntimeException('لم يُرجع المزود محتوى صالحًا.');
        return trim($content);
    }
    public function chat(array $messages,array $options=[]): string { return $this->request($messages,$options); }
    public function analyzeProblem(string $text): array
    {
        $system='أنت محلل مشاكل لمنصة خدمات مهنية محلية. أعد JSON فقط: {"category_name":"...","service_name":"...","confidence":0-100,"suggestion":"..."}. لا تخترع أرقام IDs ولا تدّعي معرفة خدمة غير مذكورة.';
        $content=$this->request([['role'=>'system','content'=>$system],['role'=>'user','content'=>$text]],['temperature'=>0.1,'max_tokens'=>400]);
        $content=preg_replace('/^```(?:json)?\s*|\s*```$/i','',trim($content)); $json=json_decode($content,true);
        if (!is_array($json)) throw new RuntimeException('استجابة التحليل ليست JSON صالحًا.');
        return ['matched'=>true,'category_name'=>$json['category_name']??null,'service_name'=>$json['service_name']??null,'confidence'=>max(0,min(100,(float)($json['confidence']??60))),'suggestion'=>$json['suggestion']??'','keywords'=>[]];
    }
    public function testConnection(): array { $text=$this->request([['role'=>'system','content'=>'أجب بكلمة واحدة فقط: OK'],['role'=>'user','content'=>'اختبار اتصال']]); return ['ok'=>stripos($text,'OK')!==false,'message'=>$text]; }
}
