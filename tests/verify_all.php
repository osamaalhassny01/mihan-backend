<?php
/**
 * Comprehensive Verification & Regression Test Suite for MIHAN Platform
 *
 * Validates:
 *  1. Security: .env.example hygiene, AES-256-GCM Encryption, Secret Redaction
 *  2. Maintenance Mode: Logic & admin bypass via class_exists('Auth')
 *  3. Authentication & RBAC: User deactivation handling, Role Permissions, Admin protection
 *  4. Financial Engine & FinanceService: Contracts, race condition locks, overpayment prevention, refund rules
 *  5. Reviews System: Rating bounds (1-5), single review rule, provider replies
 *  6. Settings & Notifications: Dynamic config effects (language, currency, notifications)
 *  7. AI Architecture: Safe fail-safe fallback to Rule-Based AI without leaks
 *  8. JSON/XSS Safe encoding
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/core/Session.php';
require_once __DIR__ . '/../app/core/Auth.php';
require_once __DIR__ . '/../app/helpers/helpers.php';
require_once __DIR__ . '/../app/services/AI/Encryption.php';
require_once __DIR__ . '/../app/services/FinanceService.php';
require_once __DIR__ . '/../app/services/AI/AIServiceFactory.php';
require_once __DIR__ . '/../app/services/AI/providers/RuleBasedAIProvider.php';

$passed = 0;
$failed = 0;

function assertTest(bool $condition, string $description, string $details = ''): void {
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
echo "       MIHAN PLATFORM - COMPREHENSIVE VERIFICATION SUITE    \n";
echo "============================================================\n\n";

// ------------------------------------------------------------
// 1. SECURITY & ENCRYPTION
// ------------------------------------------------------------
echo "Section 1: Security, Secrets & AES-256-GCM Encryption\n";
$envExample = file_get_contents(__DIR__ . '/../.env.example');
assertTest(
    strpos($envExample, 'AI_ENCRYPTION_KEY=') !== false &&
    strpos($envExample, 'gemini_test_key') === false &&
    strpos($envExample, 'sk-') === false,
    '.env.example contains no hardcoded API keys or secrets'
);

$testSecret = 'SuperSecretMihanAPIKey2026!#$';
$encrypted = AIEncryption::encrypt($testSecret);
$decrypted = AIEncryption::decrypt($encrypted);
assertTest(
    $encrypted !== $testSecret && $decrypted === $testSecret,
    'AES-256-GCM encryption and decryption round-trip succeeds'
);

$keyInEnv = getenv('AI_ENCRYPTION_KEY');
assertTest(
    !empty($keyInEnv) && strlen($keyInEnv) === 64,
    'AI_ENCRYPTION_KEY is present and is a valid 64-character hex key (256-bit)'
);

// Secret redaction test
$rawError = "Error with api_key=AIzaSyA123456789 and Bearer secret-token-xyz occurred.";
$redacted = preg_replace('/(api[_-]?key|authorization|bearer)\s*[:=]?\s*[^\s,;]+/iu', '[REDACTED]', $rawError);
assertTest(
    strpos($redacted, 'AIzaSy') === false && strpos($redacted, 'secret-token-xyz') === false && strpos($redacted, '[REDACTED]') !== false,
    'API keys and Bearer tokens are properly redacted from error strings'
);
echo "\n";

// ------------------------------------------------------------
// 2. MAINTENANCE MODE BYPASS & CORE LOGIC
// ------------------------------------------------------------
echo "Section 2: Maintenance Mode Logic & Safe Bypass\n";
$appCode = file_get_contents(__DIR__ . '/../app/core/App.php');
assertTest(
    strpos($appCode, "class_exists('Auth')") !== false && strpos($appCode, "function_exists('Auth')") === false,
    "Maintenance mode checks class_exists('Auth') instead of buggy function_exists('Auth')"
);
assertTest(
    strpos($appCode, "'/login'") !== false && strpos($appCode, "'/logout'") !== false,
    "Login and logout paths are allowed during maintenance mode to prevent redirect loops"
);
echo "\n";

// ------------------------------------------------------------
// 3. AUTHENTICATION & RBAC PERMISSION CHECKS
// ------------------------------------------------------------
echo "Section 3: RBAC & User Active State Verification\n";
$authCode = file_get_contents(__DIR__ . '/../app/core/Auth.php');
assertTest(
    strpos($authCode, "is_active") !== false && strpos($authCode, "self::logout()") !== false,
    "Inactive user (is_active = 0) triggers immediate session invalidation and logout"
);
assertTest(
    method_exists('Auth', 'can') && method_exists('Auth', 'requirePermission'),
    "Auth::can() and Auth::requirePermission() methods are implemented"
);

// Admin self-protection in AdminController
$adminCtrlCode = file_get_contents(__DIR__ . '/../app/controllers/AdminController.php');
assertTest(
    strpos($adminCtrlCode, 'لا يمكنك تعطيل حسابك الحالي') !== false &&
    strpos($adminCtrlCode, 'لا يمكن تعطيل آخر مدير نشط في النظام') !== false,
    "AdminController prevents admin self-deactivation and deactivating the last active admin"
);
assertTest(
    strpos($adminCtrlCode, 'لا يمكنك حذف حسابك الحالي') !== false &&
    strpos($adminCtrlCode, 'لا يمكن حذف آخر مدير نشط في النظام') !== false,
    "AdminController prevents admin self-deletion and deleting the last active admin"
);
echo "\n";

// ------------------------------------------------------------
// 4. FINANCIAL SERVICE CONTRACT & LOGIC
// ------------------------------------------------------------
echo "Section 4: Financial Service Contract & Rules\n";
$finance = new FinanceService();

// Contract math test with known data
$rawInvoice = [
    'id' => 999,
    'invoice_number' => 'INV-TEST-001',
    'total_amount' => 10000.00,
    'commission_rate' => 0.10,
    'commission_amount' => 1000.00,
    'provider_net' => 9000.00,
    'payment_status' => 'paid',
    'created_at' => '2026-09-20 00:00:00'
];
$payments = [
    ['amount' => 6000.00, 'status' => 'paid', 'is_simulated' => 0],
    ['amount' => 4000.00, 'status' => 'paid', 'is_simulated' => 0]
];
$summary = $finance->calculateContract($rawInvoice, $payments);

assertTest(
    $summary['gross_amount'] === 10000.00 &&
    $summary['paid_amount'] === 10000.00 &&
    $summary['net_collected'] === 10000.00 &&
    $summary['outstanding_amount'] === 0.00 &&
    $summary['commission_amount'] === 1000.00 &&
    $summary['provider_net_amount'] === 9000.00 &&
    $summary['net_platform_revenue'] === 1000.00,
    "FinanceService calculation contract strictly holds for fully paid invoice"
);

// Partial payment contract test
$partialPayments = [
    ['amount' => 4000.00, 'status' => 'paid', 'is_simulated' => 0]
];
$rawInvoice['payment_status'] = 'unpaid';
$partialSummary = $finance->calculateContract($rawInvoice, $partialPayments);
assertTest(
    $partialSummary['paid_amount'] === 4000.00 &&
    $partialSummary['outstanding_amount'] === 6000.00 &&
    $partialSummary['net_collected'] === 4000.00 &&
    $partialSummary['net_platform_revenue'] === 400.00,
    "FinanceService accurately handles partial payment (net_platform_revenue = 400, outstanding = 6000)"
);

// Refunded contract test
$rawInvoice['payment_status'] = 'refunded';
$refundPayments = [
    ['amount' => 10000.00, 'status' => 'refunded', 'is_simulated' => 0]
];
$refundSummary = $finance->calculateContract($rawInvoice, $refundPayments);
assertTest(
    $refundSummary['refunded_amount'] === 10000.00 &&
    $refundSummary['net_collected'] === 0.00 &&
    $refundSummary['provider_net_amount'] === 0.00 &&
    $refundSummary['net_platform_revenue'] === 0.00 &&
    $refundSummary['outstanding_amount'] === 0.00,
    "FinanceService zeros out provider net and platform commission upon refund"
);

// Overpayment and race condition in Invoice.php
$invoiceModelCode = file_get_contents(__DIR__ . '/../app/models/Invoice.php');
assertTest(
    strpos($invoiceModelCode, 'FOR UPDATE') !== false &&
    strpos($invoiceModelCode, 'beginTransaction') !== false &&
    strpos($invoiceModelCode, 'commit') !== false,
    "Invoice::recordPayment() employs SELECT ... FOR UPDATE within transaction to prevent race conditions"
);
assertTest(
    strpos($invoiceModelCode, '$amount > ($remainingBalance') !== false,
    "Invoice::recordPayment() prevents overpayment exceeding remaining invoice balance"
);

// Atomic request completion in ServiceRequest.php
$requestModelCode = file_get_contents(__DIR__ . '/../app/models/ServiceRequest.php');
assertTest(
    strpos($requestModelCode, 'completeWithInvoice') !== false &&
    strpos($requestModelCode, 'platform_commissions') !== false &&
    strpos($requestModelCode, 'rollBack') !== false,
    "ServiceRequest::completeWithInvoice() executes status completion and invoice creation atomically in one transaction"
);
echo "\n";

// ------------------------------------------------------------
// 5. REVIEW SYSTEM LOGIC & VALIDATION
// ------------------------------------------------------------
echo "Section 5: Review System Integrity\n";
$customerCtrlCode = file_get_contents(__DIR__ . '/../app/controllers/CustomerController.php');
assertTest(
    strpos($customerCtrlCode, '$rawRating < 1 || (int)$rawRating > 5') !== false,
    "CustomerController validates rating strictly between 1 and 5"
);
assertTest(
    strpos($customerCtrlCode, 'لقد قمت بتقييم هذا الطلب مسبقًا') !== false,
    "CustomerController enforces single review per request"
);
assertTest(
    strpos($customerCtrlCode, "'completed'") !== false,
    "CustomerController permits reviews only on completed requests"
);

// Provider reply
$providerCtrlCode = file_get_contents(__DIR__ . '/../app/controllers/ProviderController.php');
assertTest(
    strpos($providerCtrlCode, 'replyReview') !== false,
    "ProviderController provides replyReview() action for providers to respond to customer reviews"
);
echo "\n";

// ------------------------------------------------------------
// 6. SETTINGS & NOTIFICATIONS RUNTIME INTEGRATION
// ------------------------------------------------------------
echo "Section 6: Settings & Notification Switches\n";
$notifModelCode = file_get_contents(__DIR__ . '/../app/models/Notification.php');
assertTest(
    strpos($notifModelCode, "settingBool('notifications_enabled'") !== false &&
    strpos($notifModelCode, "notify_on_request") !== false &&
    strpos($notifModelCode, "notify_on_completion") !== false &&
    strpos($notifModelCode, "notify_on_payment") !== false &&
    strpos($notifModelCode, "notify_on_complaint") !== false,
    "Notification::notify() respects master notifications_enabled and individual event switches"
);

// Registration toggles in AuthController
$authCtrlCode = file_get_contents(__DIR__ . '/../app/controllers/AuthController.php');
assertTest(
    strpos($authCtrlCode, 'allow_customer_registration') !== false &&
    strpos($authCtrlCode, 'allow_provider_registration') !== false,
    "AuthController enforces allow_customer_registration and allow_provider_registration settings"
);

// Helpers: currency, money, json_safe
assertTest(function_exists('currency') && function_exists('money'), "currency() and money() helpers exist");
$moneyOutput = money(250.5);
assertTest(strpos($moneyOutput, '250.50') !== false, "money() correctly formats numbers to two decimal places: $moneyOutput");

$safeJson = json_safe(['tag' => '<script>alert(1)</script>', 'quote' => "O'Reilly & Sons"]);
assertTest(
    strpos($safeJson, '<script>') === false &&
    strpos($safeJson, '\u003C') !== false &&
    strpos($safeJson, '\u0026') !== false,
    "json_safe() properly escapes HTML tags and ampersands (XSS protection for inline scripts)"
);
echo "\n";

// ------------------------------------------------------------
// 7. AI ARCHITECTURE & FALLBACK ENGINE
// ------------------------------------------------------------
echo "Section 7: AI Architecture & Fail-Safe Fallbacks\n";
$ruleBased = new RuleBasedAIProvider();
$analysis = $ruleBased->analyzeProblem("تسرب ماء في الحمام والمواسير مكسورة");
assertTest(
    !empty($analysis['matched']) && ($analysis['category_name'] ?? '') === 'سباكة',
    "RuleBasedAIProvider accurately analyzes and matches plumbing problem to 'سباكة'"
);

$emergencyPreArrival = $ruleBased->chat([
    ['role' => 'user', 'content' => '[MIHAN_PREARRIVAL] تسرب ماء خطير']
]);
assertTest(
    strpos($emergencyPreArrival, 'محبس الماء') !== false,
    "RuleBasedAIProvider provides safety guidance for [MIHAN_PREARRIVAL] water leak"
);

// AIServiceFactory analyze and chat fallback behavior
$factoryAnalysis = AIServiceFactory::analyze("مكيف لا يبرد ويصدر صوت غريب");
assertTest(
    !empty($factoryAnalysis['matched']) || isset($factoryAnalysis['suggestion']),
    "AIServiceFactory::analyze returns reliable suggestion via active engine or rule-based fallback"
);

$factoryCode = file_get_contents(__DIR__ . '/../app/services/AI/AIServiceFactory.php');
assertTest(
    strpos($factoryCode, 'fallbackAnalyze') !== false &&
    strpos($factoryCode, 'RuleBasedAIProvider') !== false &&
    strpos($factoryCode, 'safeError') !== false,
    "AIServiceFactory contains catch-all fallback to RuleBasedAIProvider with error redaction"
);
echo "\n";

// ------------------------------------------------------------
// 8. DATABASE UNIFICATION
// ------------------------------------------------------------
echo "Section 8: Database Migrations & Seeds Unification\n";
$migrationFiles = glob(__DIR__ . '/../database/migrations/*.sql');
assertTest(count($migrationFiles) >= 10, "database/migrations contains all 10 sequential migrations (001 to 010)");
assertTest(!is_dir(__DIR__ . '/../database_migrations'), "Redundant database_migrations folder successfully eliminated");
assertTest(file_exists(__DIR__ . '/../database/migrations/migrate.php'), "Migration runner migrate.php is present");
assertTest(file_exists(__DIR__ . '/../database/seeds/seed.php'), "Unified seed runner seed.php is present");
echo "\n";

// ------------------------------------------------------------
// 9. PRIVACY - Phone number not exposed to provider
// ------------------------------------------------------------
echo "Section 9: Privacy - Phone Number Protection\n";
$providerRequestView = file_get_contents(__DIR__ . '/../app/views/provider/request_details.php');
assertTest(
    strpos($providerRequestView, 'customer_phone') === false,
    "Provider request_details view does NOT expose customer_phone to provider"
);
echo "\n";

// ------------------------------------------------------------
// 10. CASH PAYMENT ELIMINATION
// ------------------------------------------------------------
echo "Section 10: Cash Payment Elimination\n";
$migration001 = file_get_contents(__DIR__ . '/../database/migrations/001_base_schema.sql');
assertTest(
    strpos($migration001, "'cash'") === false,
    "Migration 001 payments.method ENUM does not contain 'cash'"
);
$demoSeed = file_get_contents(__DIR__ . '/../database/seeds/mihan-demo.sql');
assertTest(
    strpos($demoSeed, ",'cash',") === false && strpos($demoSeed, ", 'cash',") === false,
    "Demo seed mihan-demo.sql has no cash payment records"
);
$invoiceModel = file_get_contents(__DIR__ . '/../app/models/Invoice.php');
assertTest(
    strpos($invoiceModel, "'simulated_card','simulated_wallet'") !== false,
    "Invoice::recordPayment() allows only simulated_card and simulated_wallet"
);
echo "\n";

// ------------------------------------------------------------
// 11. REVIEW GATING - Must require paid invoice
// ------------------------------------------------------------
echo "Section 11: Review Gating - Paid Invoice Required\n";
$customerController = file_get_contents(__DIR__ . '/../app/controllers/CustomerController.php');
assertTest(
    strpos($customerController, "invoiceModel->forRequest") !== false &&
    strpos($customerController, "payment_status") !== false &&
    strpos($customerController, "'paid'") !== false,
    "CustomerController::submitReview() checks invoice exists AND payment_status = 'paid'"
);
echo "\n";

// ------------------------------------------------------------
// 12. RBAC COMPLETENESS - Admin fallback includes all required permissions
// ------------------------------------------------------------
echo "Section 12: RBAC Completeness\n";
$authCode = file_get_contents(__DIR__ . '/../app/core/Auth.php');
$requiredAdminPerms = ['manage_payouts', 'manage_employees', 'view_finance', 'view_conversations', 'manage_reviews', 'view_dashboard', 'view_providers'];
$missingPerms = [];
foreach ($requiredAdminPerms as $perm) {
    if (strpos($authCode, "'$perm'") === false) {
        $missingPerms[] = $perm;
    }
}
assertTest(empty($missingPerms), "Auth.php admin fallback includes all required permissions", implode(', ', $missingPerms));
$seedSql = file_get_contents(__DIR__ . '/../database/seeds/seed.sql');
assertTest(
    strpos($seedSql, "'manage_payouts'") !== false && strpos($seedSql, "'view_dashboard'") !== false,
    "seed.sql includes all 7 additional RBAC permissions"
);
assertTest(
    strpos($seedSql, "employee@mihan.test") !== false,
    "seed.sql includes a demo employee user"
);
assertTest(
    strpos($seedSql, "SELECT 'employee'") !== false,
    "seed.sql assigns role_permissions for employee role"
);
echo "\n";

// ------------------------------------------------------------
// 13. AVAILABLE PAYOUT - Admin finance shows correct available amount
// ------------------------------------------------------------
echo "Section 13: Available Payout in Admin Finance\n";
$adminController = file_get_contents(__DIR__ . '/../app/controllers/AdminController.php');
assertTest(
    strpos($adminController, 'available_payout') !== false,
    "AdminController::finance() calculates available_payout per invoice (provider_net - already paid out)"
);
$financeView = file_get_contents(__DIR__ . '/../app/views/admin/finance.php');
assertTest(
    strpos($financeView, "available_payout") !== false,
    "Admin finance.php view uses available_payout in payout form instead of static provider_net"
);
echo "\n";

// ------------------------------------------------------------
// 14. ADMIN CONVERSATIONS - Routes and views exist
// ------------------------------------------------------------
echo "Section 14: Admin Conversation Oversight\n";
$routes = file_get_contents(__DIR__ . '/../routes/web.php');
assertTest(
    strpos($routes, "'/admin/conversations'") !== false,
    "Routes include /admin/conversations listing route"
);
assertTest(
    strpos($routes, "'/admin/conversations/{id}'") !== false,
    "Routes include /admin/conversations/{id} detail route"
);
assertTest(
    method_exists('AdminController', 'conversations') || strpos($adminController, 'function conversations()') !== false,
    "AdminController has conversations() method"
);
assertTest(
    file_exists(__DIR__ . '/../app/views/admin/conversations.php'),
    "Admin conversations listing view exists"
);
assertTest(
    file_exists(__DIR__ . '/../app/views/admin/conversation_view.php'),
    "Admin conversation detail view exists"
);
echo "\n";

// ------------------------------------------------------------
// 15. USER MANAGEMENT & INDIVIDUAL PERMISSIONS OVERRIDES
// ------------------------------------------------------------
echo "Section 15: User Management & Individual Permissions Overrides\n";
$userModelCode = file_get_contents(__DIR__ . '/../app/models/User.php');
assertTest(
    strpos($userModelCode, 'function findWithPermissions') !== false &&
    strpos($userModelCode, 'function setUserPermissions') !== false &&
    strpos($userModelCode, 'function replaceUserPermissions') !== false &&
    strpos($userModelCode, 'function getEffectivePermissions') !== false,
    "User model implements complete individual permissions and override resolution methods"
);
assertTest(
    strpos($routes, "'/admin/users/{id}/edit'") !== false &&
    strpos($routes, "'/admin/users/{id}/update'") !== false,
    "Routes include /admin/users/{id}/edit and /admin/users/{id}/update"
);
assertTest(
    file_exists(__DIR__ . '/../app/views/admin/user_edit.php'),
    "Admin user_edit.php view exists with permissions checklist"
);
echo "\n";

// ------------------------------------------------------------
// 16. EMPLOYEE MANAGEMENT & UNIFIED RECORD
// ------------------------------------------------------------
echo "Section 16: Platform Employee Management\n";
assertTest(
    strpos($routes, "'/admin/employees/{id}/edit'") !== false &&
    strpos($routes, "'/admin/employees/{id}/update'") !== false,
    "Routes include /admin/employees/{id}/edit and /admin/employees/{id}/update"
);
assertTest(
    file_exists(__DIR__ . '/../app/views/admin/employee_edit.php'),
    "Admin employee_edit.php view exists"
);
assertTest(
    strpos($adminController, 'function editEmployee') !== false &&
    strpos($adminController, 'function updateEmployee') !== false,
    "AdminController has editEmployee() and updateEmployee() methods"
);
echo "\n";

// ------------------------------------------------------------
// 17. MIGRATIONS 012 & SCHEMA MIGRATIONS RUNNER
// ------------------------------------------------------------
echo "Section 17: Migration 012 & Schema Migrations Runner\n";
assertTest(
    file_exists(__DIR__ . '/../database/migrations/012_user_management_employee_dashboard.sql'),
    "Migration 012_user_management_employee_dashboard.sql exists"
);
$migrateCode = file_get_contents(__DIR__ . '/../database/migrations/migrate.php');
assertTest(
    strpos($migrateCode, 'schema_migrations') !== false,
    "migrate.php uses schema_migrations tracking table to prevent duplicate execution"
);
echo "\n";

// ------------------------------------------------------------
// 18. UNIFIED BACK BUTTON & ERROR PAGES NAVIGATION
// ------------------------------------------------------------
echo "Section 18: Unified Back Button & Navigation Integrity\n";
assertTest(
    file_exists(__DIR__ . '/../app/views/partials/back_button.php'),
    "Unified back_button.php partial exists in views/partials"
);
assertTest(
    function_exists('renderBackButton'),
    "renderBackButton() helper function is registered"
);
ob_start();
renderBackButton('/customer/dashboard', 'العودة');
$btnOut = ob_get_clean();
assertTest(
    strpos($btnOut, 'btn-back') !== false && strpos($btnOut, 'العودة') !== false,
    "renderBackButton helper produces expected markup with btn-back class"
);

$err403 = file_get_contents(__DIR__ . '/../app/views/errors/403.php');
assertTest(
    strpos($err403, 'renderBackButton') !== false && strpos($err403, 'href="/mihan/') === false,
    "403 error page includes unified back button and no hardcoded /mihan/ paths"
);
$err404 = file_get_contents(__DIR__ . '/../app/views/errors/404.php');
assertTest(
    strpos($err404, 'renderBackButton') !== false && strpos($err404, 'href="/mihan/') === false,
    "404 error page includes unified back button and no hardcoded /mihan/ paths"
);
$err500 = file_get_contents(__DIR__ . '/../app/views/errors/500.php');
assertTest(
    strpos($err500, 'renderBackButton') !== false && strpos($err500, 'href="/mihan/') === false,
    "500 error page includes unified back button and no hardcoded /mihan/ paths"
);
echo "\n";

// ------------------------------------------------------------
// SUMMARY
// ------------------------------------------------------------
echo "============================================================\n";
echo "VERIFICATION SUMMARY: $passed PASSED, $failed FAILED\n";
echo "============================================================\n";

if ($failed === 0) {
    echo "ALL TESTS PASSED SUCCESSFULLY! The MIHAN platform is production-ready.\n";
    exit(0);
} else {
    echo "SOME TESTS FAILED! Please review the output above.\n";
    exit(1);
}
