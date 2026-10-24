<?php
require_once __DIR__ . '/../core/Model.php';

class Notification extends Model
{
    protected string $table = 'notifications';

    public function notify(int $userId, string $title, string $message, ?string $link = null, ?string $category = null): void
    {
        if (function_exists('settingBool') && !settingBool('notifications_enabled', true)) return;
        if ($category === 'request' && function_exists('settingBool') && !settingBool('notify_on_request', true)) return;
        if ($category === 'completion' && function_exists('settingBool') && !settingBool('notify_on_completion', true)) return;
        if ($category === 'payment' && function_exists('settingBool') && !settingBool('notify_on_payment', true)) return;
        if ($category === 'complaint' && function_exists('settingBool') && !settingBool('notify_on_complaint', true)) return;

        $this->insert(['user_id' => $userId, 'title' => $title, 'message' => $message, 'link' => $link]);
    }

    public function forUser(int $userId, int $limit = 20): array
    {
        return $this->where(['user_id' => $userId], 'created_at DESC', $limit);
    }

    public function unreadCount(int $userId): int
    {
        return $this->count(['user_id' => $userId, 'is_read' => 0]);
    }

    public function markRead(int $id, int $userId): void
    {
        $this->execute("UPDATE notifications SET is_read = 1 WHERE id = :id AND user_id = :u", ['id' => $id, 'u' => $userId]);
    }

    public function markAllRead(int $userId): void
    {
        $this->execute("UPDATE notifications SET is_read = 1 WHERE user_id = :u", ['u' => $userId]);
    }
}
