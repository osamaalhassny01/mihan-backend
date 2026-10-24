<?php
require_once __DIR__ . '/../AIProviderInterface.php';

/**
 * Google Gemini provider.
 *
 * Uses Gemini's native generateContent REST API rather than the OpenAI
 * compatibility layer. This is more reliable for Google AI Studio Auth Keys
 * and follows Google's current REST authentication format.
 */
class GeminiProvider implements AIProviderInterface
{
    private string $key;
    private string $model;
    private string $baseUrl;
    private float $temperature;
    private int $maxTokens;
    private int $timeout;

    public function __construct(array $s)
    {
        $this->key = trim((string)($s['api_key'] ?? ''));
        $this->model = trim((string)($s['model'] ?? '')) ?: 'gemini-3.1-flash-lite';
        $configuredBaseUrl = trim((string)($s['base_url'] ?? ''));
        if ($configuredBaseUrl === '') {
            $configuredBaseUrl = 'https://generativelanguage.googleapis.com/v1beta';
        }
        // Accept the previous OpenAI-compatible URL too, so existing installations
        // keep working without requiring a manual edit before the migration runs.
        $configuredBaseUrl = str_replace(
            'https://generativelanguage.googleapis.com/v1beta/openai',
            'https://generativelanguage.googleapis.com/v1beta',
            $configuredBaseUrl
        );
        $this->baseUrl = rtrim($configuredBaseUrl, '/');
        $this->temperature = max(0, min(2, (float)($s['temperature'] ?? 0.2)));
        $this->maxTokens = max(100, min(65536, (int)($s['max_tokens'] ?? 700)));
        $this->timeout = max(5, min(60, (int)($s['timeout'] ?? 20)));

        if ($this->key === '') {
            throw new RuntimeException('API Key غير مُدخل.');
        }
        $this->validateBaseUrl();
    }

    public function name(): string
    {
        return 'gemini';
    }

    private function validateBaseUrl(): void
    {
        $p = parse_url($this->baseUrl);
        if (!$p || !in_array(strtolower($p['scheme'] ?? ''), ['https', 'http'], true) || empty($p['host'])) {
            throw new RuntimeException('Base URL غير صالح.');
        }
        if (in_array(strtolower($p['host']), ['localhost', '127.0.0.1', '0.0.0.0', '::1'], true)) {
            throw new RuntimeException('Base URL محلي غير مسموح به.');
        }
    }

    private function normalizeMessages(array $messages): array
    {
        $contents = [];
        $systemParts = [];

        foreach ($messages as $message) {
            $role = strtolower(trim((string)($message['role'] ?? 'user')));
            $content = $message['content'] ?? '';

            if (is_array($content)) {
                $parts = [];
                foreach ($content as $item) {
                    if (is_string($item) && trim($item) !== '') {
                        $parts[] = ['text' => $item];
                    } elseif (is_array($item) && isset($item['text']) && is_string($item['text'])) {
                        $parts[] = ['text' => $item['text']];
                    }
                }
            } else {
                $parts = [['text' => (string)$content]];
            }

            if (!$parts) {
                continue;
            }

            if ($role === 'system') {
                foreach ($parts as $part) {
                    if (isset($part['text'])) {
                        $systemParts[] = ['text' => $part['text']];
                    }
                }
                continue;
            }

            $nativeRole = $role === 'assistant' || $role === 'model' ? 'model' : 'user';
            $contents[] = [
                'role' => $nativeRole,
                'parts' => $parts
            ];
        }

        if (!$contents) {
            throw new RuntimeException('لا يوجد محتوى صالح لإرساله إلى Gemini.');
        }

        return [$contents, $systemParts];
    }

    private function request(array $messages, array $options = []): string
    {
        [$contents, $systemParts] = $this->normalizeMessages($messages);

        $generationConfig = [
            'temperature' => isset($options['temperature']) ? (float)$options['temperature'] : $this->temperature,
            'maxOutputTokens' => isset($options['max_tokens']) ? (int)$options['max_tokens'] : $this->maxTokens,
        ];

        if (!empty($options['response_mime_type'])) {
            $generationConfig['responseMimeType'] = (string)$options['response_mime_type'];
        }

        $payload = [
            'contents' => $contents,
            'generationConfig' => $generationConfig,
        ];

        if ($systemParts) {
            $payload['systemInstruction'] = ['parts' => $systemParts];
        }

        $url = $this->baseUrl . '/models/' . rawurlencode($this->model) . ':generateContent';

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'x-goog-api-key: ' . $this->key,
            ],
            CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_CONNECTTIMEOUT => min(10, $this->timeout),
        ]);

        $body = curl_exec($ch);
        $err = curl_error($ch);
        $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($err) {
            throw new RuntimeException('فشل الاتصال بـ Gemini: ' . $err);
        }
        if ($body === false || trim((string)$body) === '') {
            throw new RuntimeException('Gemini أعاد استجابة فارغة.');
        }

        $data = json_decode($body, true);
        if (!is_array($data)) {
            throw new RuntimeException('استجابة Gemini غير صالحة.');
        }

        if ($status < 200 || $status >= 300) {
            $providerMessage = '';
            if (isset($data['error']['message']) && is_string($data['error']['message'])) {
                $providerMessage = trim($data['error']['message']);
            }
            $providerStatus = isset($data['error']['status']) && is_string($data['error']['status'])
                ? trim($data['error']['status'])
                : '';

            $details = [];
            if ($providerStatus !== '') {
                $details[] = $providerStatus;
            }
            if ($providerMessage !== '') {
                $details[] = mb_substr($providerMessage, 0, 350);
            }

            throw new RuntimeException(
                'Gemini رفض الطلب (HTTP ' . $status . ')' .
                ($details ? ': ' . implode(' — ', $details) : '.')
            );
        }

        $parts = $data['candidates'][0]['content']['parts'] ?? [];
        $texts = [];
        if (is_array($parts)) {
            foreach ($parts as $part) {
                if (isset($part['text']) && is_string($part['text'])) {
                    $texts[] = $part['text'];
                }
            }
        }

        $text = trim(implode("\n", $texts));
        if ($text === '') {
            $blockReason = $data['promptFeedback']['blockReason'] ?? '';
            if (is_string($blockReason) && $blockReason !== '') {
                throw new RuntimeException('Gemini حظر الطلب بسبب سياسة السلامة: ' . $blockReason);
            }
            throw new RuntimeException('Gemini لم يُرجع محتوى نصيًا صالحًا.');
        }

        return $text;
    }

    public function chat(array $messages, array $options = []): string
    {
        return $this->request($messages, $options);
    }

    public function analyzeProblem(string $text): array
    {
        $system = 'أنت محلل مشاكل لمنصة خدمات مهنية محلية. أعد JSON فقط: {"category_name":"...","service_name":"...","confidence":0-100,"suggestion":"..."}. لا تخترع أرقام IDs ولا تدّعي معرفة خدمة غير مذكورة.';

        $content = $this->request(
            [
                ['role' => 'system', 'content' => $system],
                ['role' => 'user', 'content' => $text]
            ],
            [
                'temperature' => 0.1,
                'max_tokens' => 400,
                'response_mime_type' => 'application/json'
            ]
        );

        $content = preg_replace('/^```(?:json)?\s*|\s*```$/i', '', trim($content));
        $json = json_decode($content, true);

        if (!is_array($json)) {
            throw new RuntimeException('استجابة Gemini ليست JSON صالحًا.');
        }

        return [
            'matched' => true,
            'category_name' => $json['category_name'] ?? null,
            'service_name' => $json['service_name'] ?? null,
            'confidence' => max(0, min(100, (float)($json['confidence'] ?? 60))),
            'suggestion' => $json['suggestion'] ?? '',
            'keywords' => [],
        ];
    }

    public function testConnection(): array
    {
        $text = $this->request(
            [
                ['role' => 'user', 'content' => 'Reply with exactly one word: OK']
            ],
            [
                'temperature' => 0,
                'max_tokens' => 10
            ]
        );

        return [
            'ok' => stripos(trim($text), 'OK') !== false,
            'message' => trim($text)
        ];
    }
}
