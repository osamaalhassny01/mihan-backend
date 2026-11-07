<?php
/** FCM HTTP v1 transport. Credentials stay on the server; no legacy server key. */
class FcmClient
{
    private array $credentials;
    private $transport;
    private ?string $accessToken = null;
    private int $expiresAt = 0;

    public function __construct(array $credentials, ?callable $transport = null)
    {
        foreach (['project_id', 'client_email', 'private_key'] as $field) {
            if (empty($credentials[$field]) || !is_string($credentials[$field])) {
                throw new RuntimeException('FCM service account is missing ' . $field);
            }
        }
        $this->credentials = $credentials;
        $this->transport = $transport ?? [self::class, 'request'];
    }

    public static function fromEnvironment(): self
    {
        $json = getenv('FCM_SERVICE_ACCOUNT_JSON') ?: '';
        if ($json === '') {
            $path = getenv('GOOGLE_APPLICATION_CREDENTIALS') ?: '';
            if ($path === '' || !is_readable($path)) {
                throw new RuntimeException('Configure FCM_SERVICE_ACCOUNT_JSON or GOOGLE_APPLICATION_CREDENTIALS');
            }
            $json = file_get_contents($path);
        }
        return new self(json_decode($json, true, 512, JSON_THROW_ON_ERROR));
    }

    public function send(string $token, string $title, string $body, array $data = []): bool
    {
        if (trim($token) === '') return false;
        // HTTP v1 requires every data value to be a string.
        $payloadData = [];
        foreach (array_merge($data, ['title' => $title, 'body' => $body]) as $key => $value) {
            $payloadData[$key] = is_scalar($value) || $value === null
                ? (string)$value : json_encode($value, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        }
        $payload = ['message' => [
            'token' => $token,
            'notification' => ['title' => $title, 'body' => $body],
            'data' => $payloadData,
            'android' => [
                'priority' => 'high',
                'notification' => ['channel_id' => 'mihan_channel', 'sound' => 'default'],
            ],
            'apns' => ['payload' => ['aps' => ['sound' => 'default']]],
        ]];
        $url = 'https://fcm.googleapis.com/v1/projects/'
            . rawurlencode($this->credentials['project_id']) . '/messages:send';
        for ($attempt = 0; $attempt < 2; $attempt++) {
            [$status, $response] = ($this->transport)($url, [
                'Authorization: Bearer ' . $this->getAccessToken(), 'Content-Type: application/json',
            ], json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
            if ($status === 401 && $attempt === 0) {
                $this->accessToken = null;
                continue;
            }
            $decoded = json_decode($response, true);
            if ($status >= 200 && $status < 300 && !empty($decoded['name'])) return true;
            // Never log response bodies: they may contain registration tokens or message text.
            $code = (string)($decoded['error']['status'] ?? 'UNKNOWN');
            $code = preg_replace('/[^A-Z_]/', '', $code);
            error_log('FCM send failed: HTTP ' . $status . ' ' . $code);
            return false;
        }
        return false;
    }

    private function getAccessToken(): string
    {
        if ($this->accessToken !== null && time() < $this->expiresAt - 60) return $this->accessToken;
        $encode = static fn(string $value): string => rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
        $now = time();
        $unsigned = $encode(json_encode(['alg' => 'RS256', 'typ' => 'JWT'])) . '.' . $encode(json_encode([
            'iss' => $this->credentials['client_email'],
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
            'aud' => 'https://oauth2.googleapis.com/token',
            'iat' => $now, 'exp' => $now + 3600,
        ]));
        if (!openssl_sign($unsigned, $signature, $this->credentials['private_key'], OPENSSL_ALGO_SHA256)) {
            throw new RuntimeException('Cannot sign FCM service account assertion');
        }
        [$status, $response] = ($this->transport)('https://oauth2.googleapis.com/token', [
            'Content-Type: application/x-www-form-urlencoded',
        ], http_build_query([
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $unsigned . '.' . $encode($signature),
        ]));
        $decoded = json_decode($response, true);
        if ($status !== 200 || empty($decoded['access_token'])) {
            throw new RuntimeException('FCM OAuth authentication failed: HTTP ' . $status);
        }
        $this->accessToken = $decoded['access_token'];
        $this->expiresAt = $now + (int)($decoded['expires_in'] ?? 3600);
        return $this->accessToken;
    }

    private static function request(string $url, array $headers, string $body): array
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true, CURLOPT_HTTPHEADER => $headers,
            CURLOPT_POSTFIELDS => $body, CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 5, CURLOPT_TIMEOUT => 10,
        ]);
        $response = curl_exec($ch);
        $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($response === false) throw new RuntimeException('FCM network request failed');
        return [$status, $response];
    }
}
