<?php
require_once __DIR__ . '/../core/Model.php';

class MahirProfession extends Model
{
    protected string $table = 'mahir_professions';

    public function allActive(): array
    {
        $db = Database::getInstance();
        return $db->query(
            "SELECT * FROM mahir_professions WHERE is_active = 1 ORDER BY sort_order ASC, name ASC"
        )->fetchAll(PDO::FETCH_ASSOC);
    }

    public function byCategory(): array
    {
        $all = $this->allActive();
        $grouped = [];
        foreach ($all as $p) {
            $grouped[$p['category']][] = $p;
        }
        return $grouped;
    }

    public function findById(int $id): ?array
    {
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM mahir_professions WHERE id = :id AND is_active = 1 LIMIT 1");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function allForAdmin(): array
    {
        $db = Database::getInstance();
        return $db->query(
            "SELECT * FROM mahir_professions ORDER BY sort_order ASC, name ASC"
        )->fetchAll(PDO::FETCH_ASSOC);
    }
}
