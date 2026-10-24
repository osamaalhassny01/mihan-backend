<?php
require_once __DIR__ . '/../core/Model.php';

class Complaint extends Model
{
    protected string $table = 'complaints';

    public function createComplaint(array $data): int
    {
        $id = (int) $this->insert([
            'complaint_number' => 'TEMP',
            'created_by'       => $data['created_by'],
            'against_user_id'  => $data['against_user_id'] ?? null,
            'request_id'       => $data['request_id'] ?? null,
            'subject'          => $data['subject'],
            'description'      => $data['description'],
            'status'           => 'open',
        ]);
        $this->update($id, ['complaint_number' => 'CMP-' . date('Y') . '-' . str_pad($id, 6, '0', STR_PAD_LEFT)]);
        return $id;
    }

    public function forUser(int $userId): array
    {
        return $this->where(['created_by' => $userId], 'created_at DESC');
    }

    public function messages(int $complaintId): array
    {
        return $this->query(
            "SELECT cm.*, u.name as sender_name, u.role as sender_role FROM complaint_messages cm
             JOIN users u ON u.id = cm.sender_id WHERE cm.complaint_id = :id ORDER BY cm.created_at ASC",
            ['id' => $complaintId]
        );
    }

    public function addMessage(int $complaintId, int $senderId, string $message): void
    {
        $this->execute(
            "INSERT INTO complaint_messages (complaint_id, sender_id, message) VALUES (:c, :s, :m)",
            ['c' => $complaintId, 's' => $senderId, 'm' => $message]
        );
    }
}
