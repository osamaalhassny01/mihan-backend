<?php
require_once __DIR__ . '/../core/Model.php';

class MahirSession extends Model
{
    protected string $table = 'mahir_sessions';

    public function findByToken(string $token): ?array
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM mahir_sessions WHERE session_token = :t LIMIT 1");
        $stmt->execute(['t' => $token]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function createSession(?int $userId, ?int $professionId): array
    {
        $token = bin2hex(random_bytes(32));
        $id = $this->insert([
            'session_token'  => $token,
            'user_id'        => $userId,
            'profession_id'  => $professionId,
            'messages'       => json_encode([]),
        ]);
        return ['id' => $id, 'session_token' => $token];
    }

    public function appendMessage(int $sessionId, array $newMessages): void
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT messages FROM mahir_sessions WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $sessionId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $existing = json_decode($row['messages'] ?? '[]', true) ?: [];
        $merged = array_merge($existing, $newMessages);
        // Keep only last 20 messages to avoid huge payloads
        if (count($merged) > 20) {
            $merged = array_slice($merged, -20);
        }
        $upd = $db->prepare("UPDATE mahir_sessions SET messages = :m, updated_at = NOW() WHERE id = :id");
        $upd->execute(['m' => json_encode($merged, JSON_UNESCAPED_UNICODE), 'id' => $sessionId]);
    }

    public function getMessages(int $sessionId): array
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT messages FROM mahir_sessions WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $sessionId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return json_decode($row['messages'] ?? '[]', true) ?: [];
    }
}
