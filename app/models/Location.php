<?php
require_once __DIR__ . '/../core/Model.php';
class Location extends Model
{
    protected string $table = 'locations';
    public function active(): array { return $this->where(['is_active' => 1], 'name_ar ASC'); }
    public function governorates(): array { return $this->query("SELECT * FROM locations WHERE level='governorate' AND is_active=1"); }
    public function childrenOf(int $parentId): array { return $this->where(['parent_id' => $parentId, 'is_active' => 1]); }
}
