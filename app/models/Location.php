<?php
require_once __DIR__ . '/../core/Model.php';

class Location extends Model
{
    protected string $table = 'locations';

    public function active(): array { return $this->where(['is_active' => 1], 'name_ar ASC'); }
    public function allActive(): array { return $this->active(); }
    public function governorates(): array { return $this->query("SELECT * FROM locations WHERE level='governorate' AND is_active=1"); }
    public function childrenOf(int $parentId): array { return $this->where(['parent_id' => $parentId, 'is_active' => 1]); }

    /**
     * Return all location IDs that cover or are covered by the given location ID hierarchically.
     * Includes:
     * - The location itself.
     * - Parent/ancestor governorate (if location is a district/city).
     * - Child districts/areas (if location is a governorate/city).
     */
    public function getCoverageLocationIds(int $locationId): array
    {
        if ($locationId <= 0) return [];
        $ids = [$locationId];

        // 1. Ancestors (e.g. district -> governorate)
        $currId = $locationId;
        $safety = 0;
        while ($currId && $safety++ < 5) {
            $stmt = $this->db->prepare("SELECT parent_id FROM locations WHERE id = :id LIMIT 1");
            $stmt->execute(['id' => $currId]);
            $parent = $stmt->fetchColumn();
            if ($parent && !in_array((int)$parent, $ids, true)) {
                $ids[] = (int)$parent;
                $currId = (int)$parent;
            } else {
                break;
            }
        }

        // 2. Descendants (e.g. governorate -> all its districts)
        $children = $this->query("SELECT id FROM locations WHERE parent_id = :id AND is_active = 1", ['id' => $locationId]);
        foreach ($children as $c) {
            $cId = (int)$c['id'];
            if (!in_array($cId, $ids, true)) {
                $ids[] = $cId;
            }
        }

        return array_values(array_unique($ids));
    }

    /**
     * Check if a provider covers a given location directly or hierarchically.
     */
    public function providerCoversLocation(int $providerId, int $locationId): bool
    {
        if ($locationId <= 0) return true;
        $coverageIds = $this->getCoverageLocationIds($locationId);
        if (empty($coverageIds)) return false;

        $inList = implode(',', array_map('intval', $coverageIds));
        $sql = "SELECT 1 FROM provider_locations WHERE provider_id = :p_id AND location_id IN ({$inList}) LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['p_id' => $providerId]);
        return (bool)$stmt->fetchColumn();
    }
}

