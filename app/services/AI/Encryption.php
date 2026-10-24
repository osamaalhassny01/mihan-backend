<?php
class AIEncryption
{
    private static function key(): string
    {
        $raw = trim((string)env('AI_ENCRYPTION_KEY', ''));
        if ($raw === '') {
            $raw = self::createPersistentKey();
        }
        return hash('sha256', $raw, true);
    }

    /**
     * Creates a per-installation encryption key automatically when the project
     * is running locally and AI_ENCRYPTION_KEY has not yet been configured.
     * The generated key is persisted in .env so encrypted API keys remain
     * decryptable across requests and restarts.
     */
    private static function createPersistentKey(): string
    {
        $key = bin2hex(random_bytes(32));
        $envPath = dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . '.env';

        try {
            $contents = file_exists($envPath) ? (string)file_get_contents($envPath) : '';
            $line = 'AI_ENCRYPTION_KEY=' . $key;

            if ($contents !== '' && preg_match('/^\s*AI_ENCRYPTION_KEY\s*=.*$/mi', $contents)) {
                $contents = preg_replace('/^\s*AI_ENCRYPTION_KEY\s*=.*$/mi', $line, $contents, 1);
            } else {
                $contents = rtrim($contents) . ($contents !== '' ? PHP_EOL : '') . $line . PHP_EOL;
            }

            $written = file_put_contents($envPath, $contents, LOCK_EX);
            if ($written === false) {
                throw new RuntimeException('تعذر إنشاء مفتاح تشفير الذكاء الاصطناعي تلقائيًا. أنشئ ملف .env وأضف AI_ENCRYPTION_KEY بقيمة عشوائية طويلة.');
            }
            @chmod($envPath, 0600);

            putenv('AI_ENCRYPTION_KEY=' . $key);
            $_ENV['AI_ENCRYPTION_KEY'] = $key;
            return $key;
        } catch (Throwable $e) {
            if ($e instanceof RuntimeException) throw $e;
            throw new RuntimeException('تعذر إعداد مفتاح تشفير الذكاء الاصطناعي تلقائيًا: ' . $e->getMessage(), 0, $e);
        }
    }

    public static function encrypt(string $plain): string
    {
        if ($plain === '') return '';
        $iv = random_bytes(12);
        $tag = '';
        $cipher = openssl_encrypt($plain, 'aes-256-gcm', self::key(), OPENSSL_RAW_DATA, $iv, $tag);
        if ($cipher === false) throw new RuntimeException('Unable to encrypt AI secret.');
        return base64_encode($iv . $tag . $cipher);
    }

    public static function decrypt(?string $encoded): string
    {
        if (!$encoded) return '';
        $raw = base64_decode($encoded, true);
        if ($raw === false || strlen($raw) < 28) return '';

        // Decryption also creates the key if needed. Existing installations
        // with a missing .env key should therefore recover gracefully before
        // reporting a configuration error.
        $iv = substr($raw, 0, 12);
        $tag = substr($raw, 12, 16);
        $cipher = substr($raw, 28);
        $plain = openssl_decrypt($cipher, 'aes-256-gcm', self::key(), OPENSSL_RAW_DATA, $iv, $tag);
        return $plain === false ? '' : $plain;
    }
}
