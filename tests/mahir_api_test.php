<?php
/**
 * اختبارات ماهر AI API
 * التشغيل: php tests/mahir_api_test.php
 * أو عبر curl بعد تشغيل السيرفر
 */

$BASE_URL = getenv('API_BASE_URL') ?: 'http://localhost:8000/api/mahir';
$passed = 0;
$failed = 0;

function test(string $name, callable $fn): void {
    global $passed, $failed;
    try {
        $result = $fn();
        if ($result === true) {
            echo "✅ PASS: {$name}\n";
            $passed++;
        } else {
            echo "❌ FAIL: {$name} → {$result}\n";
            $failed++;
        }
    } catch (Throwable $e) {
        echo "❌ ERROR: {$name} → {$e->getMessage()}\n";
        $failed++;
    }
}

function apiCall(string $method, string $url, array $body = []): array {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
    ]);
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
    }
    $responseBody = curl_exec($ch);
    $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ['status' => $status, 'body' => json_decode($responseBody, true)];
}

echo "\n🤖 بدء اختبارات ماهر AI\n";
echo str_repeat('─', 50) . "\n";

// Test 1: جلب قائمة المهن
test('GET /api/mahir/professions - يجب إرجاع قائمة المهن', function() use ($BASE_URL) {
    $r = apiCall('GET', "$BASE_URL/professions");
    if ($r['status'] !== 200) return "HTTP {$r['status']}";
    if (empty($r['body']['success'])) return "success=false";
    if (empty($r['body']['data']['categories'])) return "لا توجد فئات";
    return true;
});

// Test 2: بدء جلسة بدون مهنة
$sessionToken = null;
test('POST /api/mahir/session/start - بدون مهنة', function() use ($BASE_URL, &$sessionToken) {
    $r = apiCall('POST', "$BASE_URL/session/start", []);
    if ($r['status'] !== 200) return "HTTP {$r['status']}";
    if (empty($r['body']['success'])) return "success=false";
    if (empty($r['body']['data']['session_token'])) return "لا يوجد session_token";
    $sessionToken = $r['body']['data']['session_token'];
    return true;
});

// Test 3: بدء جلسة مع مهنة (id=1 السباكة)
$sessionWithProfession = null;
test('POST /api/mahir/session/start - مع مهنة السباكة', function() use ($BASE_URL, &$sessionWithProfession) {
    $r = apiCall('POST', "$BASE_URL/session/start", ['profession_id' => 1]);
    if ($r['status'] !== 200) return "HTTP {$r['status']}";
    if (empty($r['body']['data']['session_token'])) return "لا يوجد session_token";
    $sessionWithProfession = $r['body']['data']['session_token'];
    // يجب أن تكون هناك رسالة ترحيب
    if (empty($r['body']['data']['welcome_message'])) return "لا توجد رسالة ترحيب";
    return true;
});

// Test 4: إرسال رسالة لماهر
test('POST /api/mahir/chat - سؤال عن السباكة', function() use ($BASE_URL, $sessionWithProfession) {
    if (!$sessionWithProfession) return "لا يوجد session";
    $r = apiCall('POST', "$BASE_URL/chat", [
        'session_token' => $sessionWithProfession,
        'message'       => 'كيف أصلح حنفية تقطر ماء؟',
    ]);
    if ($r['status'] !== 200) return "HTTP {$r['status']} — " . json_encode($r['body']);
    if (empty($r['body']['success'])) return "success=false: " . ($r['body']['message'] ?? '');
    if (empty($r['body']['data']['reply'])) return "لا يوجد رد من ماهر";
    $reply = $r['body']['data']['reply'];
    echo "   💬 رد ماهر: " . mb_substr($reply, 0, 100) . "...\n";
    return true;
});

// Test 5: session token غير صالح
test('POST /api/mahir/chat - token خاطئ يجب أن يعطي 404', function() use ($BASE_URL) {
    $r = apiCall('POST', "$BASE_URL/chat", [
        'session_token' => 'invalid_token_xyz_12345',
        'message'       => 'مرحبا',
    ]);
    if ($r['status'] !== 404) return "يجب HTTP 404 وليس {$r['status']}";
    return true;
});

// Test 6: رسالة فارغة
test('POST /api/mahir/chat - رسالة فارغة يجب أن تُرفض', function() use ($BASE_URL, $sessionToken) {
    if (!$sessionToken) return "لا يوجد session";
    $r = apiCall('POST', "$BASE_URL/chat", [
        'session_token' => $sessionToken,
        'message'       => '',
    ]);
    if ($r['status'] !== 400) return "يجب HTTP 400 وليس {$r['status']}";
    return true;
});

// Test 7: جلب تاريخ المحادثة
test('GET /api/mahir/session/{token}/history - تاريخ المحادثة', function() use ($BASE_URL, $sessionWithProfession) {
    if (!$sessionWithProfession) return "لا يوجد session";
    $r = apiCall('GET', "$BASE_URL/session/{$sessionWithProfession}/history");
    if ($r['status'] !== 200) return "HTTP {$r['status']}";
    if (!isset($r['body']['data']['messages'])) return "لا توجد رسائل";
    return true;
});

// Test 8: مهنة غير موجودة
test('POST /api/mahir/session/start - مهنة غير موجودة يجب أن تُرفض', function() use ($BASE_URL) {
    $r = apiCall('POST', "$BASE_URL/session/start", ['profession_id' => 99999]);
    if ($r['status'] !== 400) return "يجب HTTP 400 وليس {$r['status']}";
    return true;
});

echo str_repeat('─', 50) . "\n";
echo "📊 النتائج: ✅ {$passed} ناجح | ❌ {$failed} فاشل\n";
echo ($failed === 0 ? "🎉 جميع الاختبارات نجحت!\n" : "⚠️  يوجد " . $failed . " اختبار(ات) فاشلة.\n") . "\n";
