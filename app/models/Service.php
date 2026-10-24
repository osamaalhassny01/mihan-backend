<?php
require_once __DIR__ . '/../core/Model.php';
class Service extends Model
{
    protected string $table = 'services';
    public function byCategory(int $categoryId): array { return $this->where(['category_id' => $categoryId, 'is_active' => 1]); }

    public function search(?string $keyword): array
    {
        $keyword=trim((string)$keyword);
        if($keyword==='') return $this->withCategory();
        $like='%'.$keyword.'%';
        return $this->query("SELECT s.*, c.name_ar as category_name_ar, c.name_en as category_name_en, c.icon as category_icon,
            (CASE WHEN s.name_ar = :exact OR s.name_en = :exact2 THEN 100
                  WHEN s.name_ar LIKE :start OR s.name_en LIKE :start2 THEN 70
                  WHEN c.name_ar LIKE :cat OR c.name_en LIKE :cat2 THEN 50
                  ELSE 20 END) AS relevance
            FROM services s JOIN categories c ON c.id=s.category_id
            WHERE s.is_active=1 AND (s.name_ar LIKE :q OR s.name_en LIKE :q2 OR s.description LIKE :q3 OR c.name_ar LIKE :q4 OR c.name_en LIKE :q5)
            ORDER BY relevance DESC, c.name_ar, s.name_ar",
            ['exact'=>$keyword,'exact2'=>$keyword,'start'=>$keyword.'%','start2'=>$keyword.'%','cat'=>$like,'cat2'=>$like,'q'=>$like,'q2'=>$like,'q3'=>$like,'q4'=>$like,'q5'=>$like]);
    }
    public function withCategory(): array
    {
        return $this->query("SELECT s.*, c.name_ar as category_name_ar, c.name_en as category_name_en, c.icon as category_icon
                              FROM services s JOIN categories c ON c.id = s.category_id
                              WHERE s.is_active = 1 ORDER BY c.name_ar, s.name_ar");
    }
}
