<?php
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Notification.php';
require_once __DIR__ . '/FcmClient.php';

class FcmService
{
    private static ?FcmClient $client = null;

    /** Save once and attempt push. False means push was not accepted by FCM. */
    public static function sendToUser(int $userId, string $title, string $body, array $data = []): bool
    {
        try {
            if (empty($data['link']) && !empty($data['request_id'])) {
                $user = (new User())->find($userId);
                if ($user && in_array($user['role'], ['customer', 'provider'], true)) {
                    $data['link'] = '/' . $user['role'] . '/requests/' . (int)$data['request_id'];
                }
            }
            return (new Notification())->deliver($userId, $title, $body, $data['link'] ?? null, $data['type'] ?? null, $data);
        } catch (Throwable $e) {
            error_log('Notification delivery failed (' . get_class($e) . ')');
            return false;
        }
    }

    /** Push only; callers that already saved a notification must use this method. */
    public static function pushToUser(int $userId, string $title, string $body, array $data = []): bool
    {
        try {
            $user = (new User())->find($userId);
            if (!$user || !(int)$user['is_active'] || empty($user['fcm_token'])) return false;
            return self::sendRaw($user['fcm_token'], $title, $body, $data);
        } catch (Throwable $e) {
            error_log('FCM user delivery failed (' . get_class($e) . ')');
            return false;
        }
    }

    public static function notifyEligibleProviders(int $serviceId, ?int $locationId, string $title, string $body, array $data = []): int
    {
        $sql = "SELECT DISTINCT u.id FROM users u
                JOIN provider_profiles pp ON pp.user_id = u.id
                JOIN provider_services ps ON ps.provider_id = pp.id
                WHERE u.role = 'provider' AND u.is_active = 1 AND pp.is_verified = 1
                  AND ps.service_id = :service_id";
        $params = ['service_id' => $serviceId];
        if ($locationId !== null && $locationId > 0) {
            $sql .= " AND EXISTS (SELECT 1 FROM provider_locations pl WHERE pl.provider_id = pp.id AND pl.location_id = :location_id)";
            $params['location_id'] = $locationId;
        }
        if (!empty($data['request_id'])) {
            $sql .= " AND NOT EXISTS (SELECT 1 FROM provider_rejections pr WHERE pr.provider_id = pp.id AND pr.request_id = :request_id)";
            $params['request_id'] = (int)$data['request_id'];
        }
        $stmt = Database::getInstance()->prepare($sql);
        $stmt->execute($params);
        $sent = 0;
        foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $userId) {
            if (self::sendToUser((int)$userId, $title, $body, array_merge([
                'type' => 'request', 'link' => !empty($data['request_id']) ? '/provider/requests/' . (int)$data['request_id'] : '/provider/requests',
            ], $data))) $sent++;
        }
        return $sent;
    }

    public static function sendRaw(string $token, string $title, string $body, array $data = []): bool
    {
        try {
            self::$client ??= FcmClient::fromEnvironment();
            return self::$client->send($token, $title, $body, $data);
        } catch (Throwable $e) {
            // Configuration/authentication messages are deliberately free of credential contents.
            error_log('FCM unavailable (' . get_class($e) . '): check service account configuration and connectivity');
            return false;
        }
    }
}
