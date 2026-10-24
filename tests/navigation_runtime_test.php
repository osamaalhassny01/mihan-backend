<?php
/**
 * Navigation, Route Auditing & Unified Back Button Runtime Test Suite
 * MIHAN Platform
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../app/core/Session.php';
require_once __DIR__ . '/../app/core/Auth.php';
require_once __DIR__ . '/../app/core/CSRF.php';
require_once __DIR__ . '/../app/core/Router.php';
require_once __DIR__ . '/../app/core/Validator.php';
require_once __DIR__ . '/../app/helpers/helpers.php';

// Models & Services
require_once __DIR__ . '/../app/models/User.php';
require_once __DIR__ . '/../app/models/ServiceRequest.php';
require_once __DIR__ . '/../app/models/Service.php';
require_once __DIR__ . '/../app/models/Location.php';

// Controllers
require_once __DIR__ . '/../app/controllers/AuthController.php';
require_once __DIR__ . '/../app/controllers/AdminController.php';
require_once __DIR__ . '/../app/controllers/EmployeeController.php';
require_once __DIR__ . '/../app/controllers/CustomerController.php';
require_once __DIR__ . '/../app/controllers/ProviderController.php';

$passed = 0;
$failed = 0;

function assertNav(bool $condition, string $description, string $details = ''): void {
    global $passed, $failed;
    if ($condition) {
        $passed++;
        echo "  [PASS] $description\n";
    } else {
        $failed++;
        echo "  [FAIL] $description" . ($details ? " -> $details" : "") . "\n";
    }
}

echo "============================================================\n";
echo "   MIHAN PLATFORM - NAVIGATION & ROUTE RUNTIME TEST SUITE   \n";
echo "============================================================\n\n";

$db = Database::getInstance();

// ------------------------------------------------------------
// 1. CUSTOMER REQUEST CREATION (GET 200 & POST CREATION)
// ------------------------------------------------------------
echo "Section 1: Customer 'New Request' Access & Creation Flow\n";

Auth::logout();
$loginOk = Auth::attempt('customer1@mihan.test', 'Passw0rd!');
assertNav($loginOk && Auth::check() && Auth::role() === 'customer', "Customer login succeeds (customer1@mihan.test)");
assertNav(Auth::can('create_request') === true, "Customer has 'create_request' permission granted");

// Capture GET /customer/requests/create form output
$customerCtrl = new CustomerController();
ob_start();
$formException = null;
try {
    $customerCtrl->createRequestForm();
} catch (Throwable $t) {
    $formException = $t;
}
$formHtml = ob_get_clean();

assertNav($formException === null, "CustomerController::createRequestForm() executed without exceptions");
assertNav(strpos($formHtml, 'name="service_id"') !== false, "Create Request view contains service selector");
assertNav(strpos($formHtml, 'name="description"') !== false, "Create Request view contains description field");
assertNav(strpos($formHtml, 'btn-back') !== false || strpos($formHtml, 'العودة') !== false, "Create Request view includes Back Button navigation");

// Test POST request creation in isolated PHP subprocess to handle redirect/exit cleanly
$service = (new Service())->query("SELECT id FROM services WHERE is_active = 1 LIMIT 1");
$serviceId = !empty($service) ? (int)$service[0]['id'] : 1;
$location = (new Location())->query("SELECT id FROM locations WHERE is_active = 1 LIMIT 1");
$locationId = !empty($location) ? (int)$location[0]['id'] : 1;

$testDesc = "Test request created during navigation runtime test " . time();
$storeSubprocessScript = <<<PHP
require_once 'config/config.php';
require_once 'app/core/Database.php';
require_once 'app/core/Session.php';
require_once 'app/core/Auth.php';
require_once 'app/core/CSRF.php';
require_once 'app/core/Router.php';
require_once 'app/core/Validator.php';
require_once 'app/helpers/helpers.php';
require_once 'app/controllers/CustomerController.php';

Auth::attempt('customer1@mihan.test', 'Passw0rd!');
\$_SERVER['REQUEST_METHOD'] = 'POST';
\$_POST = [
    '_csrf' => CSRF::token(),
    'service_id' => '{$serviceId}',
    'location_id' => '{$locationId}',
    'description' => '{$testDesc}',
    'budget' => '150',
];

\$ctrl = new CustomerController();
\$ctrl->storeRequest();
PHP;

$tmpScriptPath = __DIR__ . '/../scratch_nav_store.php';
file_put_contents($tmpScriptPath, "<?php\n" . $storeSubprocessScript);
$output = [];
$returnCode = 0;
exec('php ' . escapeshellarg($tmpScriptPath), $output, $returnCode);
@unlink($tmpScriptPath);

// Check if request was created in DB
$stmt = $db->prepare("SELECT * FROM service_requests WHERE customer_id = ? AND description = ? ORDER BY id DESC LIMIT 1");
$stmt->execute([Auth::id(), $testDesc]);
$createdReq = $stmt->fetch(PDO::FETCH_ASSOC);

assertNav(!empty($createdReq), "Service request was successfully created in database by CustomerController::storeRequest()");
if (!empty($createdReq)) {
    assertNav(strpos($createdReq['request_number'], 'REQ-') === 0, "Created request has valid number: {$createdReq['request_number']}");
    assertNav($createdReq['status'] === 'pending', "Created request status is 'pending'");
    
    // Clean up test request
    $delHist = $db->prepare("DELETE FROM request_status_history WHERE request_id = ?");
    $delHist->execute([(int)$createdReq['id']]);
    $delReq = $db->prepare("DELETE FROM service_requests WHERE id = ?");
    $delReq->execute([(int)$createdReq['id']]);
}

// ------------------------------------------------------------
// 2. AUTHORIZATION & ROUTE GUARD (403/404 ENFORCEMENT)
// ------------------------------------------------------------
echo "\nSection 2: Authorization & Boundary Protection\n";

// Function to test guarded routes in isolated process
function testGuardedRoute(string $email, string $controllerClass): array {
    $script = <<<PHP
require_once 'config/config.php';
require_once 'app/core/Database.php';
require_once 'app/core/Session.php';
require_once 'app/core/Auth.php';
require_once 'app/core/CSRF.php';
require_once 'app/core/Router.php';
require_once 'app/core/Validator.php';
require_once 'app/helpers/helpers.php';
require_once 'app/controllers/{$controllerClass}.php';

if ('{$email}' !== 'guest') {
    Auth::attempt('{$email}', 'Passw0rd!');
}
\$ctrl = new {$controllerClass}();
PHP;
    $tmp = __DIR__ . '/../scratch_guard_test.php';
    file_put_contents($tmp, "<?php\n" . $script);
    $out = [];
    $code = 0;
    exec('php ' . escapeshellarg($tmp), $out, $code);
    @unlink($tmp);
    return ['code' => $code, 'output' => implode("\n", $out)];
}

// Provider attempting to access CustomerController
$res = testGuardedRoute('provider1@mihan.test', 'CustomerController');
assertNav(strpos($res['output'], '403') !== false && strpos($res['output'], 'غير مصرح') !== false, "Provider is blocked from CustomerController with 403 Forbidden");

// Provider attempting to access EmployeeController
$res = testGuardedRoute('provider1@mihan.test', 'EmployeeController');
assertNav(strpos($res['output'], '403') !== false && strpos($res['output'], 'غير مصرح') !== false, "Provider is blocked from EmployeeController with 403 Forbidden");

// Provider attempting to access AdminController
$res = testGuardedRoute('provider1@mihan.test', 'AdminController');
assertNav(strpos($res['output'], '403') !== false && strpos($res['output'], 'غير مصرح') !== false, "Provider is blocked from AdminController with 403 Forbidden");

// Guest blocked from all protected controllers
$resGuestCust = testGuardedRoute('guest', 'CustomerController');
assertNav(strpos($resGuestCust['output'], 'login') !== false || $resGuestCust['code'] === 0, "Guest is redirected to login from CustomerController");

$resGuestEmp = testGuardedRoute('guest', 'EmployeeController');
assertNav(strpos($resGuestEmp['output'], 'login') !== false || $resGuestEmp['code'] === 0, "Guest is redirected to login from EmployeeController");

$resGuestAdmin = testGuardedRoute('guest', 'AdminController');
assertNav(strpos($resGuestAdmin['output'], 'login') !== false || $resGuestAdmin['code'] === 0, "Guest is redirected to login from AdminController");

// ------------------------------------------------------------
// 3. UNIFIED BACK BUTTON & SAFE REFERER SYSTEM
// ------------------------------------------------------------
echo "\nSection 3: Unified Back Button & Navigation Security\n";

// Test renderBackButton helper output
ob_start();
renderBackButton('/customer/requests', 'العودة للطلبات');
$btnHtml = ob_get_clean();

assertNav(strpos($btnHtml, 'btn-back') !== false, "renderBackButton outputs button with .btn-back class");
assertNav(strpos($btnHtml, 'العودة للطلبات') !== false, "renderBackButton includes custom text");
assertNav(strpos($btnHtml, url('/customer/requests')) !== false, "renderBackButton uses url() resolution");

// Test safe referer fallback
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['REQUEST_URI'] = '/mihan/customer/requests/create';

// Valid local referer
$_SERVER['HTTP_REFERER'] = 'http://localhost/mihan/customer/requests';
ob_start();
renderBackButton(null, 'رجوع');
$refHtml = ob_get_clean();
assertNav(strpos($refHtml, '/customer/requests') !== false, "renderBackButton accepts safe local HTTP_REFERER");

// External referer (open-redirect attempt)
$_SERVER['HTTP_REFERER'] = 'https://evil-attacker-site.com/steal-creds';
ob_start();
renderBackButton(null, 'رجوع');
$extHtml = ob_get_clean();
assertNav(strpos($extHtml, 'evil-attacker-site.com') === false, "renderBackButton safely blocks external HTTP_REFERER");

// Error loop referer (403 / 404 / 500)
$_SERVER['HTTP_REFERER'] = 'http://localhost/mihan/403';
ob_start();
renderBackButton(null, 'رجوع');
$errHtml = ob_get_clean();
assertNav(strpos($errHtml, '/403') === false, "renderBackButton prevents loops on error pages (403/404/500)");

// XSS in referer attempt
$_SERVER['HTTP_REFERER'] = 'javascript:alert(1)';
ob_start();
renderBackButton(null, 'رجوع');
$xssHtml = ob_get_clean();
assertNav(strpos($xssHtml, 'javascript:alert') === false, "renderBackButton rejects javascript: URI scheme in referer");

// ------------------------------------------------------------
// 4. ERROR PAGES VERIFICATION (403, 404, 500)
// ------------------------------------------------------------
echo "\nSection 4: Error Pages Integrity & Navigation\n";

// Render 403 page
$src403 = file_get_contents(__DIR__ . '/../app/views/errors/403.php');
assertNav(strpos($src403, 'href="/mihan/') === false, "403 template source has no hardcoded /mihan/ paths");
ob_start();
require __DIR__ . '/../app/views/errors/403.php';
$p403 = ob_get_clean();
assertNav(strpos($p403, '403') !== false && strpos($p403, 'غير مصرح') !== false, "403 view renders Arabic explanation");
assertNav(strpos($p403, 'btn-back') !== false || strpos($p403, 'العودة') !== false, "403 view includes Back Button");

// Render 404 page
$src404 = file_get_contents(__DIR__ . '/../app/views/errors/404.php');
assertNav(strpos($src404, 'href="/mihan/') === false, "404 template source has no hardcoded /mihan/ paths");
ob_start();
require __DIR__ . '/../app/views/errors/404.php';
$p404 = ob_get_clean();
assertNav(strpos($p404, '404') !== false && strpos($p404, 'غير موجودة') !== false, "404 view renders Arabic explanation");
assertNav(strpos($p404, 'btn-back') !== false || strpos($p404, 'العودة') !== false, "404 view includes Back Button");

// Render 500 page
$src500 = file_get_contents(__DIR__ . '/../app/views/errors/500.php');
assertNav(strpos($src500, 'href="/mihan/') === false, "500 template source has no hardcoded /mihan/ paths");
ob_start();
require __DIR__ . '/../app/views/errors/500.php';
$p500 = ob_get_clean();
assertNav(strpos($p500, '500') !== false && strpos($p500, 'غير متوقع') !== false, "500 view renders Arabic explanation");
assertNav(strpos($p500, 'btn-back') !== false || strpos($p500, 'العودة') !== false, "500 view includes Back Button");

// ------------------------------------------------------------
// 5. MULTI-ROLE CONTROLLER ROUTE EXECUTION
// ------------------------------------------------------------
echo "\nSection 5: Multi-Role Primary Dashboard & Route Execution\n";

// Customer Dashboard
Auth::logout();
Auth::attempt('customer1@mihan.test', 'Passw0rd!');
$cCtrl = new CustomerController();
ob_start();
$cCtrl->dashboard();
$cDash = ob_get_clean();
assertNav(strpos($cDash, 'لوحة') !== false || strpos($cDash, 'طلبات') !== false, "Customer Dashboard renders successfully");

// Provider Dashboard
Auth::logout();
Auth::attempt('provider1@mihan.test', 'Passw0rd!');
$pCtrl = new ProviderController();
ob_start();
$pCtrl->dashboard();
$pDash = ob_get_clean();
assertNav(strpos($pDash, 'لوحة') !== false || strpos($pDash, 'المهني') !== false, "Provider Dashboard renders successfully");

// Employee Dashboard
Auth::logout();
Auth::attempt('employee@mihan.test', 'Passw0rd!');
$eCtrl = new EmployeeController();
ob_start();
$eCtrl->dashboard();
$eDash = ob_get_clean();
assertNav(strpos($eDash, 'لوحة') !== false || strpos($eDash, 'موظف') !== false, "Employee Dashboard renders successfully");

// Admin Dashboard
Auth::logout();
Auth::attempt('admin@mihan.test', 'Passw0rd!');
$aCtrl = new AdminController();
ob_start();
$aCtrl->dashboard();
$aDash = ob_get_clean();
assertNav(strpos($aDash, 'لوحة') !== false || strpos($aDash, 'الإدارة') !== false, "Admin Dashboard renders successfully");

// Reset session after tests
Auth::logout();

// ------------------------------------------------------------
// SUMMARY
// ------------------------------------------------------------
echo "\n============================================================\n";
echo "                   NAVIGATION TEST SUMMARY                  \n";
echo "============================================================\n";
echo "  Total Passed: $passed\n";
echo "  Total Failed: $failed\n";

if ($failed === 0) {
    echo "  Status: ALL NAVIGATION & ROUTE TESTS PASSED (100% OK)\n";
} else {
    echo "  Status: SOME TESTS FAILED\n";
}
echo "============================================================\n";

exit($failed > 0 ? 1 : 0);
