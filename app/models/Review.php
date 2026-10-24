<?php
require_once __DIR__ . '/../core/Model.php';

class Review extends Model
{
    protected string $table = 'reviews';

    public function existsForRequest(int $requestId): bool
    {
        return $this->first(['request_id' => $requestId]) !== null;
    }

    public function allForAdmin(array $filters = []): array
    {
        $where=['1=1']; $params=[];
        if (!empty($filters['rating'])) { $where[]='r.rating=:rating'; $params['rating']=(int)$filters['rating']; }
        if (!empty($filters['provider_id'])) { $where[]='r.provider_id=:provider_id'; $params['provider_id']=(int)$filters['provider_id']; }
        return $this->query(
            "SELECT r.*, cu.name customer_name, pu.name provider_name, sr.request_number, s.name_ar service_name_ar, s.name_en service_name_en
             FROM reviews r
             JOIN users cu ON cu.id=r.customer_id
             JOIN provider_profiles pp ON pp.id=r.provider_id
             JOIN users pu ON pu.id=pp.user_id
             JOIN service_requests sr ON sr.id=r.request_id
             JOIN services s ON s.id=sr.service_id
             WHERE ".implode(' AND ',$where)." ORDER BY r.created_at DESC", $params
        );
    }

    public function forProvider(int $providerId): array
    {
        return $this->query(
            "SELECT r.*, u.name as customer_name FROM reviews r
             JOIN users u ON u.id = r.customer_id
             WHERE r.provider_id = :id ORDER BY r.created_at DESC",
            ['id' => $providerId]
        );
    }
}
