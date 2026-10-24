<?php
/**
 * Migration runner - runs all migrations in database/migrations in sequential order.
 * Usage (from project root):
 *   php database/migrations/migrate.php
 */
require_once __DIR__ . '/../../config/config.php';

$cfg = require __DIR__ . '/../../config/database.php';
$dsn = "mysql:host={$cfg['host']};charset={$cfg['charset']}";
$pdo = new PDO($dsn, $cfg['user'], $cfg['pass'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

// Ensure database exists
$pdo->exec("CREATE DATABASE IF NOT EXISTS `{$cfg['dbname']}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
$pdo->exec("USE `{$cfg['dbname']}`");

// Create migrations tracking table
$pdo->exec("
    CREATE TABLE IF NOT EXISTS schema_migrations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        migration VARCHAR(255) NOT NULL UNIQUE,
        applied_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB;
");

$stmt = $pdo->query("SELECT migration FROM schema_migrations");
$applied = $stmt->fetchAll(PDO::FETCH_COLUMN) ?: [];
$appliedMap = array_flip($applied);

$files = glob(__DIR__ . '/*.sql');
sort($files);

echo "Running migrations for {$cfg['dbname']}...\n";

$appliedCount = 0;
foreach ($files as $file) {
    $filename = basename($file);
    if (isset($appliedMap[$filename])) {
        echo "  [SKIP] {$filename} (already applied)\n";
        continue;
    }

    echo "  [APPLY] {$filename}... ";
    $sql = file_get_contents($file);

    // Strip comments
    $cleanSql = preg_replace('/^\s*(--|#)[^\r\n]*/m', '', $sql);
    $cleanSql = preg_replace('!/\*.*?\*/!s', '', (string)$cleanSql);
    $statements = preg_split('/;\s*[\r\n]+/', (string)$cleanSql);
    $statements = array_filter(array_map('trim', (array)$statements));

    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    $inTx = false;
    try {
        $pdo->beginTransaction();
        $inTx = true;
        foreach ($statements as $stmtSql) {
            if ($stmtSql === '' || strpos($stmtSql, 'USE ') === 0) continue;
            $pdo->exec($stmtSql);
        }
        $record = $pdo->prepare("INSERT INTO schema_migrations (migration) VALUES (:m)");
        $record->execute(['m' => $filename]);
        if ($pdo->inTransaction()) {
            $pdo->commit();
        }
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
        echo "OK\n";
        $appliedCount++;
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
        echo "FAILED: " . $e->getMessage() . "\n";
    }
}

echo "Migration process finished. Applied {$appliedCount} new migrations.\n";
