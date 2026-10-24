<?php
require_once __DIR__ . '/../core/Model.php';

class Conversation extends Model
{
    protected string $table = 'conversations';

    public function forRequest(int $requestId): ?array
    {
        $rows = $this->query("SELECT c.*, sr.request_number, sr.customer_id, sr.provider_id, cu.name customer_name, pu.name provider_name
            FROM conversations c
            JOIN service_requests sr ON sr.id=c.request_id
            JOIN users cu ON cu.id=c.customer_id
            JOIN provider_profiles pp ON pp.id=c.provider_id
            JOIN users pu ON pu.id=pp.user_id
            WHERE c.request_id=:id LIMIT 1", ['id'=>$requestId]);
        return $rows[0] ?? null;
    }

    public function ensureForRequest(array $request): ?array
    {
        if (empty($request['id']) || empty($request['customer_id']) || empty($request['provider_id'])) return null;
        $existing = $this->forRequest((int)$request['id']);
        if ($existing) return $existing;
        $this->insert([
            'request_id' => (int)$request['id'],
            'customer_id' => (int)$request['customer_id'],
            'provider_id' => (int)$request['provider_id'],
        ]);
        return $this->forRequest((int)$request['id']);
    }

    public function messages(int $conversationId): array
    {
        return $this->query("SELECT m.*, u.name sender_name, u.role sender_role
            FROM conversation_messages m JOIN users u ON u.id=m.sender_id
            WHERE m.conversation_id=:id ORDER BY m.created_at ASC, m.id ASC", ['id'=>$conversationId]);
    }

    public function send(int $conversationId, int $senderId, string $message): bool
    {
        $message=trim($message);
        if ($message==='' || mb_strlen($message)>4000) return false;
        $ok=$this->execute("INSERT INTO conversation_messages(conversation_id,sender_id,message) VALUES(:c,:s,:m)",['c'=>$conversationId,'s'=>$senderId,'m'=>$message]);
        if ($ok) $this->execute("UPDATE conversations SET last_message_at=NOW() WHERE id=:id",['id'=>$conversationId]);
        return $ok;
    }

    public function markRead(int $conversationId, int $userId): void
    {
        $this->execute("UPDATE conversation_messages SET is_read=1 WHERE conversation_id=:c AND sender_id<>:u",['c'=>$conversationId,'u'=>$userId]);
    }

    public function listForEmployee(): array
    {
        return $this->query("SELECT c.*, sr.request_number, sr.status request_status, cu.name customer_name, pu.name provider_name, c.last_message_at
            FROM conversations c
            JOIN service_requests sr ON sr.id=c.request_id
            JOIN users cu ON cu.id=c.customer_id
            JOIN provider_profiles pp ON pp.id=c.provider_id
            JOIN users pu ON pu.id=pp.user_id
            ORDER BY COALESCE(c.last_message_at,c.created_at) DESC");
    }
}
