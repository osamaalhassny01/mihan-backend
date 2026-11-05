<?php
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Notification.php';

class FcmService
{
    /**
     * Send push notification to user by ID (and write to DB notifications)
     */
    public static function sendToUser(int $userId, string $title, string $body, array $data = []): bool
    {
        // 1. Insert in local notifications table
        try {
            (new Notification())->createNotification($userId, $title, $body, $data['type'] ?? 'system', $data['link'] ?? null);
        } catch (Throwable $e) {
            error_log("Failed to insert notification in DB: " . $e->getMessage());
        }

        // 2. Fetch user's FCM token
        $userModel = new User();
        $user = $userModel->find($userId);
        if (!$user || empty($user['fcm_token'])) {
            return false;
        }

        return self::sendRaw($user['fcm_token'], $title, $body, $data);
    }

    /**
     * Send notification strictly to verified active providers who cover BOTH the service and location
     */
    public static function notifyEligibleProviders(int $serviceId, ?int $locationId, string $title, string $body, array $data = []): int
    {
        try {
            $db = Database::getInstance();
            // Strict query: Provider must be verified (is_verified=1), active (is_active=1), provides the service, AND covers the location
            $sql = "SELECT DISTINCT u.id, u.fcm_token
                    FROM users u
                    JOIN provider_profiles pp ON pp.user_id = u.id
                    JOIN provider_services ps ON ps.provider_id = pp.id
                    WHERE u.role = 'provider'
                      AND u.is_active = 1
                      AND pp.is_verified = 1
                      AND ps.service_id = :service_id";
            
            $params = ['service_id' => $serviceId];

            if ($locationId !== null && $locationId > 0) {
                $sql .= " AND EXISTS (SELECT 1 FROM provider_locations pl WHERE pl.provider_id = pp.id AND pl.location_id = :loc_id)";
                $params['loc_id'] = $locationId;
            }

            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $providers = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $sentCount = 0;
            foreach ($providers as $p) {
                // Save DB notification
                try {
                    (new Notification())->createNotification((int)$p['id'], $title, $body, 'request', '/provider/requests');
                } catch (Throwable $e) {}

                if (!empty($p['fcm_token'])) {
                    self::sendRaw($p['fcm_token'], $title, $body, $data);
                    $sentCount++;
                }
            }

            return $sentCount;
        } catch (Throwable $e) {
            error_log("Error in notifyEligibleProviders: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Raw sending using FCM HTTP API or OAuth2 Service Account
     */
    public static function sendRaw(string $token, string $title, string $body, array $data = []): bool
    {
        $serverKey = getenv('FCM_SERVER_KEY') ?: ($_ENV['FCM_SERVER_KEY'] ?? '');
        if (!empty($serverKey)) {
            $payload = [
                'to' => $token,
                'notification' => [
                    'title' => $title,
                    'body'  => $body,
                    'sound' => 'default',
                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK'
                ],
                'data' => array_merge($data, [
                    'title' => $title,
                    'body' => $body,
                ]),
                'priority' => 'high'
            ];

            $ch = curl_init('https://fcm.googleapis.com/fcm/send');
            curl_setopt_array($ch, [
                CURLOPT_POST => true,
                CURLOPT_HTTPHEADER => [
                    'Authorization: key=' . $serverKey,
                    'Content-Type: application/json'
                ],
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POSTFIELDS => json_encode($payload),
                CURLOPT_TIMEOUT => 5
            ]);
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            return $httpCode === 200;
        }

        // If no server key configured, notification is still saved in DB notifications
        return true;
    }
}
