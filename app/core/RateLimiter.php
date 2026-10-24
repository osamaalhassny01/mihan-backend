<?php
/**
 * RateLimiter - Prevents brute-force attacks and abuse.
 * Tracks attempts with decay time window using atomic file storage in storage/cache/.
 */
class RateLimiter
{
    private static function storageDir(): string
    {
        $dir = __DIR__ . '/../../storage/cache/ratelimit';
        if (!is_dir($dir)) {
            @mkdir($dir, 0770, true);
        }
        return $dir;
    }

    private static function filePath(string $key): string
    {
        $hash = hash('sha256', $key);
        return self::storageDir() . '/' . $hash . '.json';
    }

    public static function tooManyAttempts(string $key, int $maxAttempts = 5): bool
    {
        $file = self::filePath($key);
        if (!file_exists($file)) return false;

        $data = json_decode((string)file_get_contents($file), true);
        if (!$data || !isset($data['attempts'], $data['reset_at'])) return false;

        if (time() >= $data['reset_at']) {
            @unlink($file);
            return false;
        }

        return $data['attempts'] >= $maxAttempts;
    }

    public static function hit(string $key, int $decaySeconds = 900): int
    {
        $file = self::filePath($key);
        $now = time();
        $data = ['attempts' => 0, 'reset_at' => $now + $decaySeconds];

        if (file_exists($file)) {
            $existing = json_decode((string)file_get_contents($file), true);
            if ($existing && isset($existing['reset_at']) && $now < $existing['reset_at']) {
                $data = $existing;
            }
        }

        $data['attempts']++;
        @file_put_contents($file, json_encode($data), LOCK_EX);
        return $data['attempts'];
    }

    public static function attempts(string $key): int
    {
        $file = self::filePath($key);
        if (!file_exists($file)) return 0;
        $data = json_decode((string)file_get_contents($file), true);
        if (!$data || time() >= ($data['reset_at'] ?? 0)) return 0;
        return (int)($data['attempts'] ?? 0);
    }

    public static function availableIn(string $key): int
    {
        $file = self::filePath($key);
        if (!file_exists($file)) return 0;
        $data = json_decode((string)file_get_contents($file), true);
        if (!$data || !isset($data['reset_at'])) return 0;
        return max(0, (int)$data['reset_at'] - time());
    }

    public static function clear(string $key): void
    {
        $file = self::filePath($key);
        if (file_exists($file)) {
            @unlink($file);
        }
    }
}
