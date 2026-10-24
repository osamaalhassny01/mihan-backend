<?php
require_once __DIR__ . '/../core/Model.php';
class Category extends Model
{
    protected string $table = 'categories';
    public function active(): array { return $this->where(['is_active' => 1], 'name_ar ASC'); }

    public function allWithServicesCount(): array
    {
        return $this->query("SELECT c.*, COUNT(s.id) as services_count 
                              FROM categories c 
                              LEFT JOIN services s ON s.category_id = c.id AND s.is_active = 1 
                              WHERE c.is_active = 1 
                              GROUP BY c.id 
                              ORDER BY c.name_ar ASC");
    }
}
