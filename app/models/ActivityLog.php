<?php
require_once __DIR__ . '/../core/Model.php';

class ActivityLog extends Model
{
    protected string $table = 'activity_logs';

    public static function log(?int $userId, string $action, string $description = ''): void
    {
        if (function_exists('settingBool') && !settingBool('activity_log_enabled', true)) return;
        $model = new self();
        $model->insert([
            'user_id'     => $userId,
            'action'      => $action,
            'description' => $description,
            'ip_address'  => $_SERVER['REMOTE_ADDR'] ?? null,
        ]);
    }

    public function recent(int $limit = 20): array
    {
        return $this->query(
            "SELECT al.*, u.name as user_name FROM activity_logs al
             LEFT JOIN users u ON u.id = al.user_id ORDER BY al.created_at DESC LIMIT $limit"
        );
    }
}
