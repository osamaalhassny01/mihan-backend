<?php
/** Offline regression tests: no real credentials, devices, or network calls. */
require_once __DIR__ . '/../app/services/FcmClient.php';
require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../app/services/FcmService.php';

function check(bool $ok, string $label): void {
    if (!$ok) throw new RuntimeException($label);
    echo "PASS: $label\n";
}
$key = openssl_pkey_new(['private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA]);
openssl_pkey_export($key, $pem);
$credentials = ['project_id' => 'test-project', 'client_email' => 'test@example.invalid', 'private_key' => $pem];
$requests = [];
$transport = function ($url, $headers, $body) use (&$requests, $key) {
    $requests[] = [$url, $headers, $body];
    if ($url === 'https://oauth2.googleapis.com/token') {
        parse_str($body, $fields);
        [$header, $claims, $signature] = explode('.', $fields['assertion']);
        $publicKey = openssl_pkey_get_details($key)['key'];
        check(openssl_verify($header . '.' . $claims, base64_decode(strtr($signature, '-_', '+/')), $publicKey, OPENSSL_ALGO_SHA256) === 1, 'OAuth assertion has a valid RSA signature');
        $claims = json_decode(base64_decode(strtr($claims, '-_', '+/')), true);
        check($claims['scope'] === 'https://www.googleapis.com/auth/firebase.messaging', 'OAuth scope is Firebase messaging');
        return [200, '{"access_token":"test-oauth-token","expires_in":3600}'];
    }
    return [200, '{"name":"projects/test-project/messages/test-message"}'];
};
$client = new FcmClient($credentials, $transport);
check($client->send('device-token', 'عنوان', 'رسالة', ['request_id' => 12]), 'HTTP v1 accepts a notification');
$message = json_decode($requests[1][2], true)['message'];
check($requests[1][0] === 'https://fcm.googleapis.com/v1/projects/test-project/messages:send', 'Uses the project-specific HTTP v1 endpoint');
check($message['data']['request_id'] === '12', 'Data values are strings');
check($message['android']['notification']['channel_id'] === 'mihan_channel', 'Background channel matches the app');
check($message['notification']['title'] === 'عنوان', 'Arabic text survives encoding');
$client->send('another-token', 'title', 'body');
check(count($requests) === 3, 'OAuth token reused within its lifetime');

$oauthCalls = 0;
$sendCalls = 0;
$retry = new FcmClient($credentials, function ($url) use (&$oauthCalls, &$sendCalls) {
    if ($url === 'https://oauth2.googleapis.com/token') {
        $oauthCalls++;
        return [200, '{"access_token":"refreshed-token","expires_in":3600}'];
    }
    $sendCalls++;
    return $sendCalls === 1 ? [401, '{"error":{"status":"UNAUTHENTICATED"}}'] : [200, '{"name":"projects/test/messages/1"}'];
});
check($retry->send('token', 'title', 'body') && $oauthCalls === 2 && $sendCalls === 2, '401 refreshes OAuth and retries once');

$reject = new FcmClient($credentials, fn($url) => $url === 'https://oauth2.googleapis.com/token'
    ? [200, '{"access_token":"test","expires_in":3600}']
    : [404, '{"error":{"status":"NOT_FOUND"}}']);
check(!$reject->send('expired-token', 'title', 'body'), 'Rejected delivery reports failure');
$malformed = new FcmClient($credentials, fn($url) => $url === 'https://oauth2.googleapis.com/token'
    ? [200, '{"access_token":"test","expires_in":3600}'] : [200, '{}']);
check(!$malformed->send('token', 'title', 'body'), 'An empty 200 response is not treated as delivery');

// Exercise the real Notification -> FcmService -> FcmClient path with an isolated database.
$db = new PDO('sqlite::memory:', null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
Database::setInstance($db);
$db->exec('CREATE TABLE users (id INTEGER PRIMARY KEY, is_active INTEGER, fcm_token TEXT)');
$db->exec('CREATE TABLE notifications (id INTEGER PRIMARY KEY, user_id INTEGER, title TEXT, message TEXT, link TEXT, is_read INTEGER DEFAULT 0, created_at TEXT DEFAULT CURRENT_TIMESTAMP)');
$db->exec("INSERT INTO users VALUES (1, 1, 'device-token'), (2, 1, NULL), (3, 0, 'inactive-token')");
$settings = [];
function settingBool($name, $default = true) { global $settings; return $settings[$name] ?? $default; }
$prop = new ReflectionProperty(FcmService::class, 'client');
$prop->setValue(null, $client);
check(FcmService::sendToUser(1, 'title', 'body'), 'Unified delivery pushes to the device');
check((int)$db->query('SELECT COUNT(*) FROM notifications')->fetchColumn() === 1, 'Unified delivery saves exactly one notification');
$before = count($requests);
(new Notification())->notify(1, 'web event', 'body');
check(count($requests) === $before + 1, 'Web model notifications also dispatch push');
check(!FcmService::sendToUser(2, 'no device', 'body'), 'A missing device reports no push');
check((int)$db->query('SELECT COUNT(*) FROM notifications')->fetchColumn() === 3, 'Notifications persist even without a device');
$before = count($requests);
check(!FcmService::pushToUser(3, 'inactive', 'body') && count($requests) === $before, 'Inactive accounts do not receive push');
$settings['notify_on_request'] = false;
check(!FcmService::sendToUser(1, 'muted', 'body', ['type' => 'request_accepted']), 'Event-specific settings apply to API notifications');
check((int)$db->query('SELECT COUNT(*) FROM notifications')->fetchColumn() === 3, 'Disabled events are not stored');
$settings = [];
$prop->setValue(null, $reject);
check(!FcmService::sendToUser(1, 'failed push', 'body'), 'Push failure is reported');
check((int)$db->query('SELECT COUNT(*) FROM notifications')->fetchColumn() === 4, 'Push failure preserves the inbox record');
$model = new Notification();
$model->markRead(1, 2);
check($model->unreadCount(1) === 3, 'A user cannot mark another user notification as read');
$model->markRead(1, 1);
check($model->unreadCount(1) === 2, 'The owner can mark a notification as read');
$model->markAllRead(1);
check($model->unreadCount(1) === 0 && $model->unreadCount(2) === 1, 'Read-all only changes the authenticated user inbox');
check(count($model->forUser(1)) === 3, 'Inbox queries exclude other users');
putenv('FCM_SERVICE_ACCOUNT_JSON');
putenv('GOOGLE_APPLICATION_CREDENTIALS');
$prop->setValue(null, null);
check(!FcmService::sendRaw('token', 'title', 'body'), 'Missing credentials never report success');
echo "All notification regression tests passed.\n";
