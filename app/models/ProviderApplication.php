<?php
require_once __DIR__ . '/../core/Model.php';
require_once __DIR__ . '/User.php';

class ProviderApplication extends Model
{
    protected string $table = 'provider_applications';

    public static function ensureTable(): void
    {
        try {
            $db = Database::getInstance();
            $db->exec("
                CREATE TABLE IF NOT EXISTS provider_applications (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    user_id INT NOT NULL,
                    national_id VARCHAR(50) NULL,
                    profession VARCHAR(100) NOT NULL,
                    years_exp INT DEFAULT 0,
                    city VARCHAR(100) NULL,
                    bio TEXT NULL,
                    certificate_url VARCHAR(500) NULL,
                    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
                    ai_score INT DEFAULT 0,
                    ai_recommendation VARCHAR(50) NULL,
                    ai_notes TEXT NULL,
                    admin_notes TEXT NULL,
                    reviewed_by INT NULL,
                    reviewed_at DATETIME NULL,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    INDEX idx_user_id (user_id),
                    INDEX idx_status (status)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");
        } catch (Throwable $e) {
            error_log('Error ensuring provider_applications table: ' . $e->getMessage());
        }
    }

    public function findByUserId(int $userId): ?array
    {
        self::ensureTable();
        return $this->findWhere(['user_id' => $userId]);
    }

    public function allWithUserDetails(?string $status = null): array
    {
        self::ensureTable();
        $sql = "SELECT pa.*, u.name as user_name, u.email as user_email, u.phone as user_phone, u.is_active as user_is_active
                FROM provider_applications pa
                JOIN users u ON u.id = pa.user_id";
        $params = [];
        if ($status) {
            $sql .= " WHERE pa.status = :status";
            $params['status'] = $status;
        }
        $sql .= " ORDER BY pa.created_at DESC";
        return $this->query($sql, $params);
    }

    public function findWithDetails(int $id): ?array
    {
        self::ensureTable();
        $sql = "SELECT pa.*, u.name as user_name, u.email as user_email, u.phone as user_phone, u.is_active as user_is_active
                FROM provider_applications pa
                JOIN users u ON u.id = pa.user_id
                WHERE pa.id = :id
                LIMIT 1";
        $res = $this->query($sql, ['id' => $id]);
        return $res[0] ?? null;
    }
}
