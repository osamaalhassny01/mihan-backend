<?php
/**
 * Seed runner - run this AFTER importing database.sql (schema only).
 * Usage (from project root):
 *   php database/seeds/seed.php
 *
 * It generates a real bcrypt hash for the demo password and executes
 * seed.sql against the configured database.
 */
require_once __DIR__ . '/../../config/config.php';

$demoPassword = 'Passw0rd!';
$hash = password_hash($demoPassword, PASSWORD_BCRYPT);

$sql = file_get_contents(__DIR__ . '/seed.sql');
$sql = str_replace('__DEMO_HASH__', $hash, $sql);

$cfg = require __DIR__ . '/../../config/database.php';
$dsn = "mysql:host={$cfg['host']};dbname={$cfg['dbname']};charset={$cfg['charset']}";
$pdo = new PDO($dsn, $cfg['user'], $cfg['pass'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

// Strip comment lines and blank lines
$cleanSql = preg_replace('/^\s*(--|#)[^\r\n]*/m', '', $sql);
$cleanSql = preg_replace('!/\*.*?\*/!s', '', (string)$cleanSql);

// Split on semicolons
$statements = preg_split('/;\s*[\r\n]+/', (string)$cleanSql);
$statements = array_filter(array_map('trim', (array)$statements));

$tables = [
    'activity_logs', 'ai_interactions', 'ai_recommendations', 'ai_provider_settings',
    'complaint_messages', 'complaints', 'conversation_messages', 'conversations',
    'provider_payouts', 'platform_commissions', 'payments', 'invoices', 'reviews',
    'request_status_history', 'service_requests', 'provider_evaluations',
    'provider_portfolio', 'provider_availability', 'provider_locations',
    'provider_services', 'provider_profiles', 'notifications', 'services',
    'categories', 'locations', 'role_permissions', 'permissions', 'users'
];

$pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
foreach ($tables as $tbl) {
    try { $pdo->exec("TRUNCATE TABLE `$tbl`"); } catch (Exception $e) {}
}

$pdo->beginTransaction();
try {
    foreach ($statements as $stmt) {
        if ($stmt === '' || strpos($stmt, '--') === 0 || strpos($stmt, 'USE ') === 0) continue;
        $pdo->exec($stmt);
    }
    $pdo->commit();
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    echo "Seed completed successfully.\n";
    echo "Demo login password for ALL seeded accounts: $demoPassword\n";
    echo "Admin: admin@mihan.test\n";
    echo "Employee: employee@mihan.test\n";
    echo "Customers: customer1@mihan.test, customer2@mihan.test\n";
    echo "Providers: provider1@mihan.test, provider2@mihan.test, provider3@mihan.test\n";
} catch (Exception $e) {
    $pdo->rollBack();
    echo "Seed failed: " . $e->getMessage() . "\n";
}
