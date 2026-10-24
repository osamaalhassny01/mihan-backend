<?php
require_once __DIR__ . '/AIServiceFactory.php';
require_once __DIR__ . '/../../core/Model.php';
class ProblemAnalyzer
{
    public static function analyze(string $text, ?int $userId = null): array
    {
        $result=AIServiceFactory::analyze($text);
        $categoryModel=new class extends Model{protected string $table='categories';};
        $serviceModel=new class extends Model{protected string $table='services';};
        $category=self::matchCategory($categoryModel,$result['category_name']??'');
        if($category){$result['category_id']=$category['id'];$service=self::matchService($serviceModel,(int)$category['id'],$result['service_name']??'');if($service){$result['service_id']=$service['id'];$result['service_name']=$service['name_ar'];}}
        else {$result['category_id']=null;$result['service_id']=null;}
        if(!$result['category_id']){$result['matched']=false;$result['suggestion']=$result['suggestion']??'اختر التصنيف والخدمة يدويًا إذا لم يكن الاقتراح مطابقًا.';}
        $model=new class extends Model{protected string $table='ai_interactions';};
        $model->insert(['user_id'=>$userId,'input_text'=>$text,'detected_category_id'=>$result['category_id']??null,'detected_service_id'=>$result['service_id']??null,'confidence'=>$result['confidence']??0,'engine_used'=>$result['engine']??'local']);
        return $result;
    }
    private static function normalize(string $s): string { $s=mb_strtolower(trim($s));return str_replace(['أ','إ','آ','ة','ى'],['ا','ا','ا','ه','ي'],$s); }
    private static function matchCategory(Model $m,string $name): ?array
    { $n=self::normalize($name);if($n==='')return null;foreach($m->all() as $c){$cn=self::normalize(($c['name_ar']??'').' '.($c['name_en']??''));if($cn===$n||mb_strpos($cn,$n)!==false||mb_strpos($n,$cn)!==false)return $c;}return null; }
    private static function matchService(Model $m,int $cat,string $name): ?array
    { $n=self::normalize($name);$rows=$m->where(['category_id'=>$cat,'is_active'=>1]);if($n==='')return $rows[0]??null;$best=null;$score=0;foreach($rows as $r){$x=self::normalize(($r['name_ar']??'').' '.($r['name_en']??''));$s=0;foreach(preg_split('/\s+/u',$n,-1,PREG_SPLIT_NO_EMPTY) as $w)if(mb_strlen($w)>=2&&mb_strpos($x,$w)!==false)$s++;if($s>$score){$score=$s;$best=$r;}}return $best?:($rows[0]??null); }
}
