<?php
require_once __DIR__ . '/../core/Model.php';

class Notification extends Model
{
    protected string $table = 'notifications';

    public function notify(int $userId, string $title, string $message, ?string $link = null, ?string $category = null): void
    {
        $this->deliver($userId, $title, $message, $link, $category);
    }

    public function deliver(int $userId, string $title, string $message, ?string $link = null, ?string $category = null, array $data = []): bool
    {
        if (function_exists('settingBool') && !settingBool('notifications_enabled', true)) return false;
        $eventCategory = match ($category) {
            'request_created', 'direct_request', 'new_request', 'request_accepted', 'request_in_progress', 'provider_declined', 'quote_submitted', 'quote_decided', 'rework_requested', 'request_cancelled' => 'request',
            'request_completed', 'completion_requested' => 'completion',
            'invoice_paid' => 'payment',
            'new_complaint', 'complaint_reply' => 'complaint',
            default => $category,
        };
        $switch = match ($eventCategory) {
            'request' => 'notify_on_request',
            'completion' => 'notify_on_completion',
            'payment' => 'notify_on_payment',
            'complaint' => 'notify_on_complaint',
            default => null,
        };
        if ($switch !== null && function_exists('settingBool') && !settingBool($switch, true)) return false;

        $this->insert(['user_id' => $userId, 'title' => $title, 'message' => $message, 'link' => $link]);
        require_once __DIR__ . '/../services/FcmService.php';
        return FcmService::pushToUser($userId, $title, $message, array_merge($data, [
            'type' => $category ?? 'system', 'link' => $link ?? '',
        ]));
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
