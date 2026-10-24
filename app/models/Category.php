<?php
require_once __DIR__ . '/../core/Model.php';
class Category extends Model
{
    protected string $table = 'categories';
    public function active(): array { return $this->where(['is_active' => 1], 'name_ar ASC'); }
}
