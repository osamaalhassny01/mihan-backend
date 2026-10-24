<?php
/**
 * Database - PDO singleton wrapper with prepared-statement helpers.
 * All queries in the whole app go through this class using bound parameters
 * to prevent SQL injection.
 */
class Database
{
    private static ?PDO $instance = null;

    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            $cfg = require __DIR__ . '/../../config/database.php';
            $port = !empty($cfg['port']) ? ";port={$cfg['port']}" : '';
            $dsn = "mysql:host={$cfg['host']}{$port};dbname={$cfg['dbname']};charset={$cfg['charset']}";
            try {
                self::$instance = new PDO($dsn, $cfg['user'], $cfg['pass'], [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]);
            } catch (PDOException $e) {
                error_log('DB Connection Error: ' . $e->getMessage());
                throw new RuntimeException('Database connection failed: ' . $e->getMessage(), (int)$e->getCode(), $e);
            }
        }
        return self::$instance;
    }

    public static function setInstance(?PDO $instance): void
    {
        self::$instance = $instance;
    }
}
