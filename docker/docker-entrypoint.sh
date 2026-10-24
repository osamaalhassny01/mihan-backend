#!/bin/bash
set -e

PORT="${PORT:-80}"
export PORT

# Configure Apache port dynamically for Railway ($PORT)
sed -i "s/Listen 80/Listen ${PORT}/g" /etc/apache2/ports.conf 2>/dev/null || true
sed -i "s/<VirtualHost \*:80>/<VirtualHost \*:${PORT}>/g" /etc/apache2/sites-available/000-default.conf 2>/dev/null || true

# Ensure storage directories exist and are writable
mkdir -p /var/www/html/storage/cache /var/www/html/storage/logs /var/www/html/storage/uploads
chown -R www-data:www-data /var/www/html/storage
chmod -R 775 /var/www/html/storage

# Run database auto-migration if MySQL environment variables are available
if [ -n "$DB_HOST" ] || [ -n "$MYSQLHOST" ] || [ -n "$DATABASE_URL" ] || [ -n "$MYSQL_URL" ]; then
    echo "Checking database connection..."
    php -r '
        require_once "/var/www/html/config/config.php";
        try {
            $dsn = "mysql:host=" . DB_HOST . (defined("DB_PORT") && DB_PORT ? ";port=" . DB_PORT : "") . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
            $check = $pdo->query("SHOW TABLES LIKE '\''users'\''")->fetch();
            if (!$check && file_exists("/var/www/html/database.sql")) {
                echo "Initializing database schema from database.sql...\n";
                $sql = file_get_contents("/var/www/html/database.sql");
                $pdo->exec($sql);
                echo "Database schema initialized successfully!\n";
            } else {
                echo "Database already initialized.\n";
            }
        } catch (Exception $e) {
            echo "Notice: DB check deferred (" . $e->getMessage() . ")\n";
        }
    ' || true
fi

exec "$@"
